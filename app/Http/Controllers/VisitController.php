<?php
namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Patient;
use App\Models\Visit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VisitController extends Controller
{
    private const STATUSES = ['waiting','with_nurse','with_doctor','pharmacy','completed','cancelled'];

    public function index(Request $request)
    {
        $me = Auth::user();
        if (!$me->can_('visits.view')) abort(403, 'You do not have access to the queue.');

        // Default to today; let admin / secretary back-scroll via ?date=YYYY-MM-DD.
        $date = $request->date('date') ?? today();

        $query = Visit::with(['patient', 'currentStaff', 'recordedBy'])
            ->whereDate('checked_in_at', $date);

        // Optional status filter via tab pill.
        $statusFilter = in_array($request->get('status'), self::STATUSES) ? $request->get('status') : null;
        if ($statusFilter) $query->where('status', $statusFilter);

        $visits = $query->orderByRaw("
            CASE status
                WHEN 'waiting'      THEN 1
                WHEN 'with_nurse'   THEN 2
                WHEN 'with_doctor'  THEN 3
                WHEN 'pharmacy'     THEN 4
                WHEN 'completed'    THEN 5
                WHEN 'cancelled'    THEN 6
                ELSE 7
            END
        ")->orderBy('checked_in_at')->get();

        // Counts per status for the filter chips (always reflects the full day, not filtered).
        $counts = Visit::whereDate('checked_in_at', $date)
            ->selectRaw('status, COUNT(*) as c')
            ->groupBy('status')
            ->pluck('c', 'status')
            ->toArray();

        $patients = $me->can_('visits.checkin')
            ? Patient::orderBy('name')->get(['id', 'name', 'patient_id'])
            : collect();

        $clinicalStaff = \App\Models\User::whereIn('role', ['doctor','nurse','pharmacist','clinic_head'])
            ->where('is_active', true)
            ->orderBy('role')
            ->orderBy('name')
            ->get(['id','name','role']);

        return view('visits.index', compact('visits', 'counts', 'date', 'statusFilter', 'patients', 'clinicalStaff'));
    }

    public function store(Request $request)
    {
        $me = Auth::user();
        if (!$me->can_('visits.checkin')) abort(403, 'Only the front desk can check patients in.');

        $mode = $request->input('patient_mode', 'existing');

        if ($mode === 'new') {
            // Quick-register the walk-in inline. Only minimum fields; the full
            // record can be completed later from /patients.
            $validated = $request->validate([
                'new_patient_name' => 'required|string|max:255',
                'new_patient_date_of_birth' => 'nullable|date',
                'new_patient_sex' => 'nullable|in:male,female,other',
                'new_patient_phone' => 'nullable|string|max:50',
                'visit_type' => 'required|in:appointment,walk_in',
                'reason' => 'nullable|string|max:255',
            ]);

            // Assign a unique patient ID like the regular Add Patient flow does.
            do {
                $candidate = 'P-' . now()->year . '-' . str_pad((string) random_int(1, 9999), 4, '0', STR_PAD_LEFT);
            } while (Patient::where('patient_id', $candidate)->exists());

            $patient = Patient::create([
                'patient_id' => $candidate,
                'name' => $validated['new_patient_name'],
                'date_of_birth' => $validated['new_patient_date_of_birth'] ?? null,
                'sex' => $validated['new_patient_sex'] ?? null,
                'phone' => $validated['new_patient_phone'] ?? null,
                'last_visit' => now(),
            ]);

            ActivityLog::create([
                'user_id' => $me->id,
                'action' => 'patient.create',
                'entity_type' => Patient::class,
                'entity_id' => $patient->id,
                'details' => "Quick-registered {$patient->name} ({$patient->patient_id}) at check-in",
            ]);

            $patientId = $patient->id;
        } else {
            $validated = $request->validate([
                'patient_id' => 'required|exists:patients,id',
                'visit_type' => 'required|in:appointment,walk_in',
                'reason' => 'nullable|string|max:255',
            ]);
            $patientId = $validated['patient_id'];
        }

        // Guard against double-checkin: if the patient already has an active visit today, bounce.
        $existing = Visit::where('patient_id', $patientId)
            ->whereDate('checked_in_at', today())
            ->whereNotIn('status', ['completed', 'cancelled'])
            ->first();
        if ($existing) {
            return back()->with('error', 'This patient already has an active visit today.');
        }

        $visit = Visit::create([
            'patient_id' => $patientId,
            'recorded_by' => $me->id,
            'checked_in_at' => now(),
            'status' => 'waiting',
            'visit_type' => $validated['visit_type'],
            'reason' => $validated['reason'] ?? null,
        ]);

        $patient = Patient::find($patientId);
        ActivityLog::create([
            'user_id' => $me->id,
            'action' => 'visit.checkin',
            'entity_type' => Visit::class,
            'entity_id' => $visit->id,
            'details' => "Checked in {$patient->name} ({$patient->patient_id}) as {$visit->typeLabel()}",
        ]);

        return back()->with('success', "{$patient->name} checked in.");
    }

    public function updateStatus(Request $request, Visit $visit)
    {
        $me = Auth::user();
        if (!$me->can_('visits.status')) abort(403, 'You cannot change visit status.');

        $validated = $request->validate([
            'status' => 'required|in:' . implode(',', self::STATUSES),
        ]);

        $prev = $visit->status;
        $visit->status = $validated['status'];

        // When a clinical staff "takes" the patient, tag them as current owner.
        if (in_array($validated['status'], ['with_nurse', 'with_doctor', 'pharmacy'])) {
            $visit->current_staff_id = $me->id;
        }
        if ($validated['status'] === 'completed') {
            $visit->checked_out_at  = now();
            $visit->current_staff_id = null;
        }
        $visit->save();

        ActivityLog::create([
            'user_id' => $me->id,
            'action' => 'visit.status',
            'entity_type' => Visit::class,
            'entity_id' => $visit->id,
            'details' => "Status: {$prev} → {$validated['status']} for {$visit->patient->name}",
        ]);

        return back()->with('success', 'Status updated.');
    }

    public function assign(Request $request, Visit $visit)
    {
        $me = Auth::user();
        $isClaim = $request->boolean('claim');
        if ($isClaim) {
            if (!$me->can_('visits.claim')) abort(403, 'You cannot claim visits.');
            $staffId = $me->id;
        } else {
            if (!$me->can_('visits.assign_any')) abort(403, 'You cannot reassign visits.');
            $validated = $request->validate([
                'staff_id' => 'nullable|exists:users,id',
            ]);
            $staffId = $validated['staff_id'] ?? null;
        }
        if (!$visit->isActive()) {
            return back()->with('error', 'Cannot reassign a finished visit.');
        }
        $prev = $visit->currentStaff?->name ?? 'Unassigned';
        $visit->current_staff_id = $staffId;
        $visit->save();
        $newName = $staffId ? (\App\Models\User::find($staffId)?->name ?? '—') : 'Unassigned';
        ActivityLog::create([
            'user_id' => $me->id,
            'action' => 'visit.assign',
            'entity_type' => Visit::class,
            'entity_id' => $visit->id,
            'details' => "Reassigned from {$prev} to {$newName} for {$visit->patient->name}",
        ]);
        return back()->with('success', $isClaim ? 'Claimed.' : 'Reassigned.');
    }

    public function destroy(Visit $visit)
    {
        $me = Auth::user();
        if (!$me->can_('visits.cancel')) abort(403, 'Only the front desk can cancel a visit.');

        // Already-finished visits get hard-removed from the queue (the "Remove" button).
        // Still-active visits get soft-cancelled so the patient's history shows the cancellation.
        $alreadyFinished = in_array($visit->status, ['completed', 'cancelled']);
        $patientName     = $visit->patient->name ?? 'unknown patient';

        if ($alreadyFinished) {
            ActivityLog::create([
                'user_id' => $me->id,
                'action' => 'visit.remove',
                'entity_type' => Visit::class,
                'entity_id' => $visit->id,
                'details' => "Removed {$visit->status} visit for {$patientName}",
            ]);
            $visit->delete();
            return back()->with('success', 'Visit removed from queue.');
        }

        $visit->status         = 'cancelled';
        $visit->checked_out_at = now();
        $visit->save();

        ActivityLog::create([
            'user_id' => $me->id,
            'action' => 'visit.cancel',
            'entity_type' => Visit::class,
            'entity_id' => $visit->id,
            'details' => "Cancelled visit for {$patientName}",
        ]);

        return back()->with('success', 'Visit cancelled.');
    }
}
