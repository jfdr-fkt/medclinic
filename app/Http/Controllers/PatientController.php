<?php
namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Patient;
use App\Models\PatientImage;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class PatientController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        if (!$user->can_('patients.view')) abort(403, 'You do not have access to patient records.');

        $query = Patient::with(['nurse', 'doctor']);

        // Clinical scope: doctors and nurses only see patients THEY are personally assigned to.
        // Oversight roles (admin / clinic_head) see the full directory — every view of an
        // unassigned record gets audited downstream in show().
        if (!$user->can_('patients.view_all')) {
            $query->where(function ($q) use ($user) {
                $q->where('assigned_doctor_id', $user->id)
                  ->orWhere('assigned_nurse_id', $user->id);
            });
        }

        if ($request->filled('search')) {
            $query->search($request->search);
        }
        if ($request->filled('nurse_id')) {
            $query->where('assigned_nurse_id', $request->nurse_id);
        }
        if ($request->filled('doctor_id')) {
            $query->where('assigned_doctor_id', $request->doctor_id);
        }

        $sortField = in_array($request->get('sort'), ['name', 'last_visit', 'patient_id', 'date_of_birth'])
            ? $request->get('sort') : 'last_visit';
        $sortDir = $request->get('direction') === 'asc' ? 'asc' : 'desc';
        $query->orderBy($sortField, $sortDir);

        $patients = $query->paginate(15)->withQueryString();
        $nurses = User::where('role', 'nurse')->orderBy('name')->get();
        $doctors = User::where('role', 'doctor')->orderBy('name')->get();
        $pinnedIds = $user->pinnedPatients()->pluck('patients.id')->toArray();

        $pinnedByOthers = [];
        if ($user->can_('patients.pin_all')) {
            $rows = DB::table('pinned_patients')
                ->whereIn('patient_id', $patients->pluck('id'))
                ->join('users', 'users.id', '=', 'pinned_patients.user_id')
                ->select('pinned_patients.patient_id', 'users.id as user_id', 'users.name')
                ->get();
            foreach ($rows as $r) {
                $pinnedByOthers[$r->patient_id][] = ['id' => $r->user_id, 'name' => $r->name];
            }
        }

        return view('patients.index', compact('patients', 'nurses', 'doctors', 'pinnedIds', 'pinnedByOthers', 'sortField', 'sortDir'));
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        if (!$user->can_('patients.create')) abort(403, 'Not authorized to add patients.');
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'date_of_birth' => 'nullable|date',
            'sex' => 'nullable|in:male,female,other',
            'blood_type' => 'nullable|in:A+,A-,B+,B-,AB+,AB-,O+,O-',
            'height_cm' => 'nullable|numeric|min:20|max:260',
            'weight_kg' => 'nullable|numeric|min:0.5|max:500',
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string',
            'allergies' => 'nullable|string|max:1000',
            'chronic_conditions' => 'nullable|string|max:1000',
            'medical_history' => 'nullable|string',
            'assigned_nurse_id' => 'nullable|exists:users,id',
            'assigned_doctor_id' => 'nullable|exists:users,id',
            'emergency_contact_name' => 'nullable|string|max:255',
            'emergency_contact_phone' => 'nullable|string|max:50',
            'emergency_contact_2_name' => 'nullable|string|max:255',
            'emergency_contact_2_phone' => 'nullable|string|max:50',
        ]);

        do {
            $candidate = 'P-' . now()->year . '-' . str_pad((string) random_int(1, 9999), 4, '0', STR_PAD_LEFT);
        } while (Patient::where('patient_id', $candidate)->exists());
        $validated['patient_id'] = $candidate;
        $validated['last_visit'] = now();

        $patient = Patient::create($validated);

        ActivityLog::create([
            'user_id' => $user->id,
            'action' => 'patient.create',
            'entity_type' => Patient::class,
            'entity_id' => $patient->id,
            'details' => "Created record for {$patient->name} ({$patient->patient_id})",
        ]);

        return redirect()->route('patients.index')->with('success', "Patient added — ID assigned: {$patient->patient_id}");
    }

    public function show(Patient $patient)
    {
        $user = Auth::user();
        if (!$user->can_('patients.view')) abort(403, 'You do not have access to patient records.');

        // Clinical staff (doctor / nurse) can only open records they are personally assigned to.
        // patients.view_all roles (admin / clinic_head / secretary) can open any record but
        // every cross-team open is audit-logged below.
        $isAssigned = $patient->assigned_doctor_id === $user->id
                   || $patient->assigned_nurse_id  === $user->id;
        if (!$user->can_('patients.view_all') && !$isAssigned) {
            abort(403, 'This patient is not assigned to you.');
        }

        $patient->load(['nurse', 'doctor', 'visits.currentStaff', 'images.uploadedBy']);
        $visits = $patient->visits()->with('currentStaff')->take(15)->get();

        // Audit trail: oversight role opening someone they're not clinically assigned to.
        // HIPAA / PH Data Privacy Act pattern of "minimum necessary access + full audit trail".
        if ($user->can_('patients.view_all') && !$isAssigned) {
            ActivityLog::create([
                'user_id' => $user->id,
                'action' => 'patient.view',
                'entity_type' => Patient::class,
                'entity_id' => $patient->id,
                'details' => "Viewed record of {$patient->name} ({$patient->patient_id})",
            ]);
        }

        // For the inline Edit Patient modal on the show page.
        $nurses  = User::where('role', 'nurse')->orderBy('name')->get();
        $doctors = User::where('role', 'doctor')->orderBy('name')->get();
        return view('patients.show', compact('patient', 'visits', 'nurses', 'doctors'));
    }

    public function update(Request $request, Patient $patient)
    {
        $user = Auth::user();
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'date_of_birth' => 'nullable|date',
            'sex' => 'nullable|in:male,female,other',
            'blood_type' => 'nullable|in:A+,A-,B+,B-,AB+,AB-,O+,O-',
            'height_cm' => 'nullable|numeric|min:20|max:260',
            'weight_kg' => 'nullable|numeric|min:0.5|max:500',
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string',
            'allergies' => 'nullable|string|max:1000',
            'chronic_conditions' => 'nullable|string|max:1000',
            'medical_history' => 'nullable|string',
            'assigned_nurse_id' => 'nullable|exists:users,id',
            'assigned_doctor_id' => 'nullable|exists:users,id',
            'emergency_contact_name' => 'nullable|string|max:255',
            'emergency_contact_phone' => 'nullable|string|max:50',
            'emergency_contact_2_name' => 'nullable|string|max:255',
            'emergency_contact_2_phone' => 'nullable|string|max:50',
        ]);
        $patient->update($validated);

        ActivityLog::create([
            'user_id' => $user->id,
            'action' => 'patient.update',
            'entity_type' => Patient::class,
            'entity_id' => $patient->id,
            'details' => "Updated record for {$patient->name} ({$patient->patient_id})",
        ]);

        return back()->with('success', 'Patient updated!');
    }

    /**
     * Upload one or more clinical photos (skin condition, x-ray, etc.) for a patient.
     * Each image records who uploaded it so the audit trail is intact.
     */
    public function uploadImages(Request $request, Patient $patient)
    {
        $user = Auth::user();
        if (!$user->can_('patients.create')) abort(403, 'You cannot edit patient records.');
        $request->validate([
            'images' => 'required|array|min:1|max:8',
            'images.*' => 'image|mimes:jpeg,png,webp|max:6144',
            'caption' => 'nullable|string|max:255',
        ]);

        $created = 0;
        foreach ($request->file('images') as $file) {
            $path = $file->store('patients/photos', 'public');
            PatientImage::create([
                'patient_id' => $patient->id,
                'uploaded_by' => $user->id,
                'path' => $path,
                'caption' => $request->input('caption'),
            ]);
            $created++;
        }

        ActivityLog::create([
            'user_id' => $user->id,
            'action' => 'patient.image.add',
            'entity_type' => Patient::class,
            'entity_id' => $patient->id,
            'details' => "Added {$created} photo(s) to {$patient->name} ({$patient->patient_id})",
        ]);

        return back()->with('success', "{$created} photo(s) uploaded.");
    }

    /**
     * Delete a single patient photo. Only the uploader, admin, or clinic_head can remove.
     */
    public function deleteImage(Patient $patient, PatientImage $image)
    {
        $user = Auth::user();
        $isOwn       = $image->uploaded_by === $user->id;
        $isOversight = in_array($user->role, ['admin', 'clinic_head']);
        if (!$isOwn && !$isOversight) abort(403, 'You can only remove photos you uploaded.');
        if ($image->patient_id !== $patient->id) abort(404);

        try { Storage::disk('public')->delete($image->path); } catch (\Throwable $e) { /* ignore */ }
        $image->delete();

        ActivityLog::create([
            'user_id' => $user->id,
            'action' => 'patient.image.remove',
            'entity_type' => Patient::class,
            'entity_id' => $patient->id,
            'details' => "Removed a photo from {$patient->name} ({$patient->patient_id})",
        ]);

        return back()->with('success', 'Photo removed.');
    }

    public function destroy(Patient $patient)
    {
        $user = Auth::user();
        if (!$user->can_('patients.delete')) abort(403, 'Only admins and doctors can delete patient records.');

        ActivityLog::create([
            'user_id' => $user->id,
            'action' => 'patient.delete',
            'entity_type' => Patient::class,
            'entity_id' => $patient->id,
            'details' => "Deleted record for {$patient->name} ({$patient->patient_id})",
        ]);

        $patient->delete();
        return redirect()->route('patients.index')->with('success', 'Patient record deleted.');
    }

    public function pin(Request $request, Patient $patient)
    {
        $user = Auth::user();
        $target = $request->input('target', 'self');
        $canPinAll = $user->can_('patients.pin_all');

        if ($target === 'all') {
            if (!$canPinAll) return response()->json(['success' => false, 'error' => 'Not authorized'], 403);
            $userIds = User::pluck('id')->all();
            DB::table('pinned_patients')->where('patient_id', $patient->id)->delete();
            $rows = array_map(fn($uid) => ['user_id' => $uid, 'patient_id' => $patient->id], $userIds);
            DB::table('pinned_patients')->insert($rows);
            $msg = "Pinned for everyone ({$patient->name}).";
        } elseif ($target === 'unpin_all') {
            if (!$canPinAll) return response()->json(['success' => false, 'error' => 'Not authorized'], 403);
            DB::table('pinned_patients')->where('patient_id', $patient->id)->delete();
            $msg = "Unpinned from everyone ({$patient->name}).";
        } elseif ($target === 'self') {
            $result = $user->pinnedPatients()->toggle($patient->id);
            $msg = !empty($result['attached']) ? 'Patient pinned to your dashboard!' : 'Patient unpinned.';
        } elseif (str_starts_with((string)$target, 'unpin_')) {
            if (!$canPinAll) return response()->json(['success' => false, 'error' => 'Not authorized'], 403);
            $targetUserId = (int) substr($target, 6);
            $targetUser = User::findOrFail($targetUserId);
            $targetUser->pinnedPatients()->detach($patient->id);
            $msg = "Unpinned from {$targetUser->name}.";
        } else {
            $targetUser = User::findOrFail($target);
            $targetUser->pinnedPatients()->syncWithoutDetaching([$patient->id]);
            $msg = "Pinned for {$targetUser->name}.";
        }

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => $msg]);
        }
        return back()->with('success', $msg);
    }

    public function search(Request $request)
    {
        $patients = Patient::with(['nurse', 'doctor'])
            ->search($request->q)
            ->take(10)
            ->get()
            ->map(fn($p) => [
                'id' => $p->id,
                'patient_id' => $p->patient_id,
                'name' => $p->name,
                'nurse' => $p->nurse?->name,
                'doctor' => $p->doctor?->name,
            ]);
        return response()->json($patients);
    }
}
