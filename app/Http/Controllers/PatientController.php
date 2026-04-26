<?php
namespace App\Http\Controllers;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
class PatientController extends Controller {
    public function index(Request $request) {
        $query = Patient::with(['nurse','doctor']);
        if($request->filled('search')) $query->search($request->search);
        if($request->filled('nurse_id')) $query->where('assigned_nurse_id',$request->nurse_id);
        if($request->filled('doctor_id')) $query->where('assigned_doctor_id',$request->doctor_id);
        $sortField = $request->get('sort','name');
        $sortDirection = $request->get('direction','asc');
        $query->orderBy($sortField,$sortDirection);
        $patients = $query->paginate(15)->withQueryString();
        $nurses = User::where('role','nurse')->get();
        $doctors = User::where('role','doctor')->get();
        return view('patients.index',compact('patients','nurses','doctors'));
    }
    public function togglePin(Patient $patient) {
        $user = Auth::user();
        if($user->pinnedPatients()->where('patient_id',$patient->id)->exists()){
            $user->pinnedPatients()->detach($patient);
            return back()->with('success','Patient unpinned');
        }
        $user->pinnedPatients()->attach($patient);
        return back()->with('success','Patient pinned');
    }
    public function store(Request $request) {
        $validated = $request->validate([
            'name'=>'required|string|max:255','patient_id'=>'required|string|unique:patients',
            'date_of_birth'=>'nullable|date','phone'=>'nullable|string','address'=>'nullable|string',
            'medical_history'=>'nullable|string','assigned_nurse_id'=>'nullable|exists:users,id',
            'assigned_doctor_id'=>'nullable|exists:users,id'
        ]);
        $validated['last_visit'] = now();
        Patient::create($validated);
        return redirect()->route('patients.index')->with('success','Patient added');
    }
}