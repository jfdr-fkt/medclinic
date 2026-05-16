<?php
namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Shift;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
        // MySQL ENUM columns sort by declaration order by default. Cast to CHAR so "sort by Role"
        // produces an actual alphabetical ordering (Admin → Assistant → Clinic Head → …).
        if ($sortField === 'role') {
            $query->orderByRaw('CAST(role AS CHAR) ' . $sortDir)->orderBy('name');
        } else {
            $query->orderBy($sortField, $sortDir);
        }

        $staff  = $query->paginate(15)->withQueryString();
        // Just today's shifts, keyed by user_id for O(1) lookup in the view.
        $shifts = Shift::whereDate('shift_date', today())->get()->keyBy('user_id');

        return view('staff.index', compact('staff', 'shifts', 'sortField', 'sortDir'));
    }

    public function store(Request $request)
    {
        if (!\Illuminate\Support\Facades\Auth::user()->can_('staff.create')) abort(403, 'Only admins can add staff.');
        $validated = $request->validate([
            'name'                      => 'required|string|max:255',
            'email'                     => 'required|email|unique:users',
            'password'                  => 'required|string|min:6',
            'role'                      => 'required|in:admin,clinic_head,doctor,pharmacist,nurse,secretary,assistant',
            'phone'                     => 'nullable|string|max:60',
            'specialization'            => 'nullable|string|max:120',
            'date_of_birth'             => 'nullable|date|before:today',
            'hire_date'                 => 'nullable|date',
            // Address comes in as separate Philippine-style parts and is joined into one string.
            'address_street'            => 'nullable|string|max:120',
            'address_barangay'          => 'nullable|string|max:80',
            'address_city'              => 'nullable|string|max:80',
            'address_province'          => 'nullable|string|max:80',
            'address_zip'               => 'nullable|string|max:12',
            'emergency_contact_name'    => 'nullable|string|max:120',
            'emergency_contact_phone'   => 'nullable|string|max:60',
            'emergency_contact_2_name'  => 'nullable|string|max:120',
            'emergency_contact_2_phone' => 'nullable|string|max:60',
            'license_number'            => 'nullable|string|max:80',
        ]);

        // Join the address parts so the existing single `address` column stays the source of truth.
        $parts = array_filter([
            $validated['address_street']   ?? null,
            $validated['address_barangay'] ?? null,
            $validated['address_city']     ?? null,
            $validated['address_province'] ?? null,
            $validated['address_zip']      ?? null,
        ], fn($p) => trim((string)$p) !== '');
        $validated['address'] = $parts ? implode(', ', $parts) : null;

        foreach (['address_street','address_barangay','address_city','address_province','address_zip'] as $k) {
            unset($validated[$k]);
        }

        $validated['password']             = Hash::make($validated['password']);
        $validated['is_active']            = true;
        $validated['last_seen_at']         = now();
        // Admin/clinic-head handed out a temporary password — force the new staff member
        // to pick their own on first login.
        $validated['must_change_password'] = true;
        $newStaff = User::create($validated);

        ActivityLog::create([
            'user_id'     => Auth::id(),
            'action'      => 'staff.create',
            'entity_type' => User::class,
            'entity_id'   => $newStaff->id,
            'details'     => "Added staff {$newStaff->name} ({$newStaff->roleLabel()})",
        ]);

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

    public function shiftOn(User $user, Request $request)
    {
        if (Auth::user()->role !== 'admin') abort(403);
        $date  = $request->validate(['date' => 'required|date'])['date'];
        $shift = Shift::where('user_id', $user->id)->whereDate('shift_date', $date)->first();
        if (!$shift) return response()->json(['exists' => false]);

        return response()->json([
            'exists'     => true,
            'shift_type' => $shift->shift_type,
            'start_time' => \Carbon\Carbon::parse($shift->start_time)->format('g:i A'),
            'end_time'   => \Carbon\Carbon::parse($shift->end_time)->format('g:i A'),
        ]);
    }

    public function destroy(User $user)
    {
        $me = Auth::user();
        if (!$me->can_('staff.delete')) abort(403, 'Only admins can remove staff.');
        if ($user->id === $me->id) abort(403, 'You cannot delete your own account.');

        $name  = $user->name;
        $label = $user->roleLabel();
        $uid   = $user->id;

        $user->delete();

        ActivityLog::create([
            'user_id'     => $me->id,
            'action'      => 'staff.delete',
            'entity_type' => User::class,
            'entity_id'   => $uid,
            'details'     => "Removed staff {$name} ({$label})",
        ]);

        return redirect()->route('staff.index')->with('success', "Staff member {$name} removed.");
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

        $shift = Shift::updateOrCreate(
            ['user_id' => $request->user_id, 'shift_date' => $request->shift_date],
            $request->only('shift_type', 'start_time', 'end_time')
        );

        $target = User::find($request->user_id);
        ActivityLog::create([
            'user_id'     => Auth::id(),
            'action'      => 'staff.shift.assign',
            'entity_type' => Shift::class,
            'entity_id'   => $shift->id,
            'details'     => "Assigned {$request->shift_type} shift to {$target?->name} on {$request->shift_date}",
        ]);

        return back()->with('success', 'Shift assigned!');
    }
}
