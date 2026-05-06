<?php
namespace App\Http\Controllers;

use App\Models\Patient;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
        $validated = $request->validate([
            'name'                => 'required|string|max:255',
            'patient_id'          => 'required|string|unique:patients',
            'date_of_birth'       => 'nullable|date',
            'phone'               => 'nullable|string',
            'address'             => 'nullable|string',
            'medical_history'     => 'nullable|string',
            'assigned_nurse_id'   => 'nullable|exists:users,id',
            'assigned_doctor_id'  => 'nullable|exists:users,id',
        ]);
        $validated['last_visit'] = now();
        Patient::create($validated);
        return redirect()->route('patients.index')->with('success', 'Patient added successfully!');
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
        $patient->delete();
        return redirect()->route('patients.index')->with('success', 'Patient record deleted.');
    }

    public function pin(Patient $patient)
    {
        $user = Auth::user();
        if ($user->pinnedPatients()->where('patient_id', $patient->id)->exists()) {
            $user->pinnedPatients()->detach($patient);
            $msg = 'Patient unpinned.';
        } else {
            $user->pinnedPatients()->attach($patient);
            $msg = 'Patient pinned to dashboard!';
        }

        if (request()->wantsJson()) {
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
