<?php
namespace App\Http\Controllers;

use App\Models\Shift;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class StaffController extends Controller
{
    public function index(Request $request)
    {
        $query = User::query();

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'LIKE', "%{$request->search}%")
                  ->orWhere('email', 'LIKE', "%{$request->search}%")
                  ->orWhere('specialization', 'LIKE', "%{$request->search}%");
            });
        }
        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }
        if ($request->filled('status') && $request->status === 'online') {
            $query->where('last_seen_at', '>=', now()->subMinutes(5))
                  ->where('status', '!=', 'offline');
        }
        if ($request->filled('status') && $request->status === 'offline') {
            $query->where(function ($q) {
                $q->where('last_seen_at', '<', now()->subMinutes(5))
                  ->orWhereNull('last_seen_at')
                  ->orWhere('status', 'offline');
            });
        }

        $sortField = in_array($request->get('sort'), ['name', 'role', 'last_seen_at']) ? $request->get('sort') : 'name';
        $sortDir   = $request->get('direction') === 'desc' ? 'desc' : 'asc';
        $query->orderBy($sortField, $sortDir);

        $staff  = $query->paginate(15)->withQueryString();
        // Just today's shifts, keyed by user_id for O(1) lookup in the view.
        $shifts = Shift::whereDate('shift_date', today())->get()->keyBy('user_id');

        return view('staff.index', compact('staff', 'shifts', 'sortField', 'sortDir'));
    }

    public function store(Request $request)
    {
        if (!\Illuminate\Support\Facades\Auth::user()->can_('staff.create')) abort(403, 'Only admins can add staff.');
        $validated = $request->validate([
            'name'                    => 'required|string|max:255',
            'email'                   => 'required|email|unique:users',
            'password'                => 'required|string|min:6',
            'role'                    => 'required|in:admin,clinic_head,doctor,pharmacist,nurse,secretary,assistant',
            'phone'                   => 'nullable|string|max:60',
            'specialization'          => 'nullable|string|max:120',
            'date_of_birth'           => 'nullable|date|before:today',
            'hire_date'               => 'nullable|date',
            'address'                 => 'nullable|string|max:255',
            'emergency_contact_name'  => 'nullable|string|max:120',
            'emergency_contact_phone' => 'nullable|string|max:60',
            'license_number'          => 'nullable|string|max:80',
            'bio'                     => 'nullable|string|max:500',
        ]);
        $validated['password']     = Hash::make($validated['password']);
        $validated['is_active']    = true;
        $validated['last_seen_at'] = now();
        User::create($validated);
        return back()->with('success', 'Staff member added successfully!');
    }

    public function show(User $user)
    {
        $monthStart = now()->startOfMonth();
        $monthEnd   = now()->endOfMonth();

        $shifts = $user->shifts()
            ->whereBetween('shift_date', [$monthStart, $monthEnd])
            ->orderBy('shift_date')
            ->get();

        $upcoming = $user->shifts()
            ->where('shift_date', '>', $monthEnd)
            ->orderBy('shift_date')
            ->take(5)->get();

        return view('staff.show', compact('user', 'shifts', 'upcoming', 'monthStart', 'monthEnd'));
    }

    public function storeShift(Request $request)
    {
        if (\Illuminate\Support\Facades\Auth::user()->role !== 'admin') {
            abort(403, 'Only administrators can manage shifts.');
        }

        $request->validate([
            'user_id'    => 'required|exists:users,id',
            'shift_type' => 'required|in:morning,afternoon,night,on_call',
            'shift_date' => 'required|date',
            'start_time' => 'required',
            'end_time'   => 'required',
        ]);

        Shift::updateOrCreate(
            ['user_id' => $request->user_id, 'shift_date' => $request->shift_date],
            $request->only('shift_type', 'start_time', 'end_time')
        );

        return back()->with('success', 'Shift assigned!');
    }
}
