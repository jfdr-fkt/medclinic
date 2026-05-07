<?php
namespace App\Http\Controllers;

use App\Models\Patient;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PatientController extends Controller
{
    public function index(Request $request)
    {
        $query = Patient::with(['nurse', 'doctor']);

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
        $nurses   = User::where('role', 'nurse')->orderBy('name')->get();
        $doctors  = User::where('role', 'doctor')->orderBy('name')->get();
        $pinnedIds = Auth::user()->pinnedPatients()->pluck('patients.id')->toArray();

        return view('patients.index', compact('patients', 'nurses', 'doctors', 'pinnedIds', 'sortField', 'sortDir'));
    }

    public function store(Request $request)
    {
        if (!Auth::user()->can_('patients.create')) abort(403, 'Not authorized to add patients.');
        $validated = $request->validate([
            'name'                => 'required|string|max:255',
            'date_of_birth'       => 'nullable|date',
            'phone'               => 'nullable|string',
            'address'             => 'nullable|string',
            'medical_history'     => 'nullable|string',
            'assigned_nurse_id'   => 'nullable|exists:users,id',
            'assigned_doctor_id'  => 'nullable|exists:users,id',
        ]);

        // Auto-generate a unique random patient ID
        do {
            $candidate = 'P-' . now()->year . '-' . str_pad((string) random_int(1, 9999), 4, '0', STR_PAD_LEFT);
        } while (Patient::where('patient_id', $candidate)->exists());
        $validated['patient_id'] = $candidate;
        $validated['last_visit'] = now();

        $patient = Patient::create($validated);
        return redirect()->route('patients.index')->with('success', "Patient added — ID assigned: {$patient->patient_id}");
    }

    public function show(Patient $patient)
    {
        $patient->load(['nurse', 'doctor']);
        return view('patients.show', compact('patient'));
    }

    public function update(Request $request, Patient $patient)
    {
        $validated = $request->validate([
            'name'                => 'required|string|max:255',
            'date_of_birth'       => 'nullable|date',
            'phone'               => 'nullable|string',
            'address'             => 'nullable|string',
            'medical_history'     => 'nullable|string',
            'assigned_nurse_id'   => 'nullable|exists:users,id',
            'assigned_doctor_id'  => 'nullable|exists:users,id',
        ]);
        $patient->update($validated);
        return back()->with('success', 'Patient updated!');
    }

    public function destroy(Patient $patient)
    {
        if (!Auth::user()->can_('patients.delete')) abort(403, 'Only admins and doctors can delete patient records.');
        $patient->delete();
        return redirect()->route('patients.index')->with('success', 'Patient record deleted.');
    }

    public function pin(Request $request, Patient $patient)
    {
        $user = Auth::user();
        $target = $request->input('target', 'self');

        if ($target === 'all') {
            // Only doctor or admin
            if (!in_array($user->role, ['admin', 'doctor'])) {
                return response()->json(['success' => false, 'error' => 'Not authorized'], 403);
            }
            $userIds = User::pluck('id')->all();
            DB::table('pinned_patients')->where('patient_id', $patient->id)->delete();
            $rows = array_map(fn($uid) => ['user_id' => $uid, 'patient_id' => $patient->id], $userIds);
            DB::table('pinned_patients')->insert($rows);
            $msg = "Pinned for everyone ({$patient->name}).";
        } elseif ($target === 'self') {
            $result = $user->pinnedPatients()->toggle($patient->id);
            $msg = !empty($result['attached']) ? 'Patient pinned to your dashboard!' : 'Patient unpinned.';
        } else {
            // target is a user ID
            $targetUser = User::findOrFail($target);
            $result = $targetUser->pinnedPatients()->syncWithoutDetaching([$patient->id]);
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
                'id'         => $p->id,
                'patient_id' => $p->patient_id,
                'name'       => $p->name,
                'nurse'      => $p->nurse?->name,
                'doctor'     => $p->doctor?->name,
            ]);
        return response()->json($patients);
    }
}
