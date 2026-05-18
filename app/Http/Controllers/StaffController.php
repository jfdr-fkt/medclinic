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
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:6',
            'role' => 'required|in:admin,clinic_head,doctor,pharmacist,nurse,secretary,assistant',
            'phone' => 'nullable|string|max:60',
            'specialization' => 'nullable|string|max:120',
            'date_of_birth' => 'nullable|date|before:today',
            'hire_date' => 'nullable|date',
            // Address comes in as separate Philippine-style parts and is joined into one string.
            'address_street' => 'nullable|string|max:120',
            'address_barangay' => 'nullable|string|max:80',
            'address_city' => 'nullable|string|max:80',
            'address_province' => 'nullable|string|max:80',
            'address_zip' => 'nullable|string|max:12',
            'emergency_contact_name' => 'nullable|string|max:120',
            'emergency_contact_phone' => 'nullable|string|max:60',
            'emergency_contact_2_name' => 'nullable|string|max:120',
            'emergency_contact_2_phone' => 'nullable|string|max:60',
            'license_number' => 'nullable|string|max:80',
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
            'user_id' => Auth::id(),
            'action' => 'staff.create',
            'entity_type' => User::class,
            'entity_id' => $newStaff->id,
            'details' => "Added staff {$newStaff->name} ({$newStaff->roleLabel()})",
        ]);

        return back()->with('success', 'Staff member added successfully!');
    }

    public function show(Request $request, User $user)
    {
        $year = (int) ($request->query('year') ?: now()->year);
        if ($year < 2000 || $year > 2100) $year = now()->year;
        $month = (int) ($request->query('month') ?: now()->month);
        if ($month < 1 || $month > 12) $month = now()->month;

        $monthStart = \Carbon\Carbon::create($year, $month, 1)->startOfMonth();
        $monthEnd = (clone $monthStart)->endOfMonth();
        $yearStart = \Carbon\Carbon::create($year, 1, 1)->startOfDay();
        $yearEnd = \Carbon\Carbon::create($year, 12, 31)->endOfDay();

        $shifts = $user->shifts()
            ->whereBetween('shift_date', [$monthStart, $monthEnd])
            ->orderBy('shift_date')
            ->get();

        $yearShifts = $user->shifts()
            ->whereBetween('shift_date', [$yearStart, $yearEnd])
            ->get(['shift_date', 'shift_type']);

        $events = \App\Models\CalendarEvent::with('creator')
            ->where(function ($q) use ($user) {
                $q->where('user_id', $user->id)->orWhereNull('user_id');
            })
            ->whereBetween('event_date', [$monthStart, $monthEnd])
            ->orderBy('event_date')
            ->get();

        $yearEvents = \App\Models\CalendarEvent::where(function ($q) use ($user) {
                $q->where('user_id', $user->id)->orWhereNull('user_id');
            })
            ->whereBetween('event_date', [$yearStart, $yearEnd])
            ->get(['event_date', 'color']);

        $upcoming = $user->shifts()
            ->where('shift_date', '>', $monthEnd)
            ->orderBy('shift_date')
            ->take(5)->get();

        $allStaff = collect();
        if (Auth::user()->can_('staff.shifts.manage')) {
            $allStaff = User::where('is_active', true)
                ->whereNotIn('role', ['admin'])
                ->orderBy('role')
                ->orderBy('name')
                ->get(['id', 'name', 'role']);
        }

        return view('staff.show', compact('user', 'shifts', 'events', 'upcoming', 'monthStart', 'monthEnd', 'year', 'month', 'yearShifts', 'yearEvents', 'allStaff'));
    }

    public function storeEvent(Request $request, User $user)
    {
        $me = Auth::user();
        $isOwner = $me->id === $user->id;
        $isPrivileged = $me->can_('staff.shifts.manage');
        if (!$isOwner && !$isPrivileged) abort(403, 'You cannot add events to this calendar.');

        $validated = $request->validate([
            'event_date' => 'required|date',
            'title' => 'required|string|max:120',
            'description' => 'nullable|string|max:500',
            'color' => 'required|in:info,note,festive,important,holiday',
            'scope' => 'required|in:personal,global,custom',
            'custom_user_ids' => 'array',
            'custom_user_ids.*' => 'exists:users,id',
        ]);

        if (in_array($validated['scope'], ['global','custom']) && !$isPrivileged) {
            abort(403, 'Only admins can add events to other staff.');
        }

        $created = collect();

        if ($validated['scope'] === 'global') {
            $created->push(\App\Models\CalendarEvent::create([
                'user_id' => null,
                'created_by' => $me->id,
                'event_date' => $validated['event_date'],
                'title' => $validated['title'],
                'description' => $validated['description'] ?? null,
                'color' => $validated['color'],
            ]));
        } elseif ($validated['scope'] === 'custom') {
            $targetIds = collect($validated['custom_user_ids'] ?? [])->unique()->push($user->id)->unique()->values();
            foreach ($targetIds as $uid) {
                $created->push(\App\Models\CalendarEvent::create([
                    'user_id' => $uid,
                    'created_by' => $me->id,
                    'event_date' => $validated['event_date'],
                    'title' => $validated['title'],
                    'description' => $validated['description'] ?? null,
                    'color' => $validated['color'],
                ]));
            }
        } else {
            $created->push(\App\Models\CalendarEvent::create([
                'user_id' => $user->id,
                'created_by' => $me->id,
                'event_date' => $validated['event_date'],
                'title' => $validated['title'],
                'description' => $validated['description'] ?? null,
                'color' => $validated['color'],
            ]));
        }

        ActivityLog::create([
            'user_id' => $me->id,
            'action' => 'calendar.event.create',
            'entity_type' => \App\Models\CalendarEvent::class,
            'entity_id' => $created->first()?->id,
            'details' => "Added {$validated['color']} event '{$validated['title']}' on {$validated['event_date']} (scope: {$validated['scope']}, " . $created->count() . ' rows)',
        ]);

        if ($validated['scope'] === 'custom') {
            foreach ($created as $ev) {
                if (!$ev->user_id || $ev->user_id === $me->id) continue;
                \App\Models\UserNotification::notify(
                    $ev->user_id,
                    'calendar.event',
                    "Event: {$ev->title}",
                    \Carbon\Carbon::parse($ev->event_date)->format('l, F j') . ($ev->description ? ' · ' . \Illuminate\Support\Str::limit($ev->description, 80) : ''),
                    route('staff.show', ['user' => $ev->user_id, 'year' => $ev->event_date->year, 'month' => $ev->event_date->month]),
                    'fa-calendar-plus',
                    'pink'
                );
            }
        }

        return back()->with('success', 'Event added.');
    }

    public function bulkStoreEvents(Request $request, User $user)
    {
        $me = Auth::user();
        $isOwner = $me->id === $user->id;
        $isPrivileged = $me->can_('staff.shifts.manage');
        if (!$isOwner && !$isPrivileged) abort(403, 'You cannot add events to this calendar.');

        $validated = $request->validate([
            'title' => 'required|string|max:120',
            'description' => 'nullable|string|max:500',
            'color' => 'required|in:info,note,festive,important,holiday',
            'scope' => 'required|in:personal,global,custom',
            'dates' => 'required|array|min:1',
            'dates.*' => 'required|date',
            'user_ids' => 'array',
            'user_ids.*' => 'exists:users,id',
        ]);

        if (in_array($validated['scope'], ['global','custom']) && !$isPrivileged) {
            abort(403, 'Only admins can add events to other staff or clinic-wide.');
        }

        $targetIds = [];
        if ($validated['scope'] === 'global') {
            $targetIds = [null];
        } elseif ($validated['scope'] === 'custom') {
            $targetIds = collect($validated['user_ids'] ?? [])->unique()->push($user->id)->unique()->values()->all();
        } else {
            $targetIds = [$user->id];
        }

        $count = 0;
        foreach ($validated['dates'] as $date) {
            foreach ($targetIds as $uid) {
                \App\Models\CalendarEvent::create([
                    'user_id' => $uid,
                    'created_by' => $me->id,
                    'event_date' => $date,
                    'title' => $validated['title'],
                    'description' => $validated['description'] ?? null,
                    'color' => $validated['color'],
                ]);
                $count++;
            }
        }

        ActivityLog::create([
            'user_id' => $me->id,
            'action' => 'calendar.event.bulk_create',
            'entity_type' => \App\Models\CalendarEvent::class,
            'entity_id' => null,
            'details' => "Bulk-added {$count} '{$validated['title']}' events (scope: {$validated['scope']})",
        ]);

        if ($validated['scope'] === 'custom') {
            $notifyIds = array_filter($targetIds, fn($id) => $id && $id !== $me->id);
            foreach ($notifyIds as $uid) {
                $target = User::find($uid);
                if (!$target) continue;
                $firstDate = \Carbon\Carbon::parse($validated['dates'][0]);
                $body = count($validated['dates']) === 1
                    ? $firstDate->format('l, F j')
                    : count($validated['dates']) . ' dates · first on ' . $firstDate->format('M j');
                \App\Models\UserNotification::notify(
                    $uid,
                    'calendar.event.bulk',
                    "Event: {$validated['title']}",
                    $body,
                    route('staff.show', ['user' => $uid, 'year' => $firstDate->year, 'month' => $firstDate->month]),
                    'fa-calendar-plus',
                    'pink'
                );
            }
        }

        return response()->json(['ok' => true, 'count' => $count]);
    }

    public function destroyEvent(\App\Models\CalendarEvent $event)
    {
        $me = Auth::user();
        $isOwner = $event->user_id && $event->user_id === $me->id;
        $isCreator = $event->created_by === $me->id;
        $isPrivileged = $me->can_('staff.shifts.manage');
        if (!$isOwner && !$isCreator && !$isPrivileged) abort(403, 'You cannot remove this event.');

        $title = $event->title;
        $date = $event->event_date->toDateString();
        $event->delete();

        ActivityLog::create([
            'user_id' => $me->id,
            'action' => 'calendar.event.delete',
            'entity_type' => \App\Models\CalendarEvent::class,
            'entity_id' => null,
            'details' => "Removed event '{$title}' on {$date}",
        ]);

        return back()->with('success', 'Event removed.');
    }

    public function destroyShift(Shift $shift)
    {
        $me = Auth::user();
        if (!$me->can_('staff.shifts.manage')) abort(403, 'Only admins/clinic heads can remove shifts.');

        $userId = $shift->user_id;
        $date = $shift->shift_date;
        $shift->delete();

        $target = User::find($userId);
        ActivityLog::create([
            'user_id' => $me->id,
            'action' => 'staff.shift.remove',
            'entity_type' => Shift::class,
            'entity_id' => null,
            'details' => "Removed shift from {$target?->name} on {$date}",
        ]);

        return back()->with('success', 'Shift removed.');
    }

    public function shiftOn(User $user, Request $request)
    {
        if (!Auth::user()->can_('staff.shifts.manage')) abort(403);
        $date = $request->validate(['date' => 'required|date'])['date'];
        $shift = Shift::where('user_id', $user->id)->whereDate('shift_date', $date)->first();
        if (!$shift) return response()->json(['exists' => false]);

        return response()->json([
            'exists' => true,
            'shift_type' => $shift->shift_type,
            'start_time' => \Carbon\Carbon::parse($shift->start_time)->format('g:i A'),
            'end_time' => \Carbon\Carbon::parse($shift->end_time)->format('g:i A'),
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
            'user_id' => $me->id,
            'action' => 'staff.delete',
            'entity_type' => User::class,
            'entity_id' => $uid,
            'details' => "Removed staff {$name} ({$label})",
        ]);

        return redirect()->route('staff.index')->with('success', "Staff member {$name} removed.");
    }

    public function storeShift(Request $request)
    {
        if (!Auth::user()->can_('staff.shifts.manage')) {
            abort(403, 'Only admins or clinic heads can manage shifts.');
        }

        $request->validate([
            'user_id' => 'required|exists:users,id',
            'shift_type' => 'required|in:morning,afternoon,night,on_call,custom',
            'shift_date' => 'required|date',
            'start_time' => 'required',
            'end_time' => 'required',
        ]);

        $shift = Shift::updateOrCreate(
            ['user_id' => $request->user_id, 'shift_date' => $request->shift_date],
            $request->only('shift_type', 'start_time', 'end_time')
        );

        $target = User::find($request->user_id);
        ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => 'staff.shift.assign',
            'entity_type' => Shift::class,
            'entity_id' => $shift->id,
            'details' => "Assigned {$request->shift_type} shift to {$target?->name} on {$request->shift_date}",
        ]);

        if ($target && $target->id !== Auth::id()) {
            $label = $request->shift_type === 'on_call' ? 'On Call' : ucfirst($request->shift_type);
            \App\Models\UserNotification::notify(
                $target->id,
                'shift.assign',
                "Shift assigned: {$label}",
                \Carbon\Carbon::parse($request->shift_date)->format('l, F j') . ' · ' . \Carbon\Carbon::parse($request->start_time)->format('g:i A') . '–' . \Carbon\Carbon::parse($request->end_time)->format('g:i A'),
                route('staff.show', ['user' => $target->id, 'year' => \Carbon\Carbon::parse($request->shift_date)->year, 'month' => \Carbon\Carbon::parse($request->shift_date)->month]),
                'fa-briefcase-medical',
                'amber'
            );
        }

        return back()->with('success', 'Shift assigned!');
    }

    public function bulkStoreShifts(Request $request)
    {
        if (!Auth::user()->can_('staff.shifts.manage')) {
            abort(403, 'Only admins or clinic heads can manage shifts.');
        }

        $validated = $request->validate([
            'user_ids' => 'required|array|min:1',
            'user_ids.*' => 'exists:users,id',
            'entries' => 'required|array|min:1',
            'entries.*.shift_date' => 'required|date',
            'entries.*.shift_type' => 'required|in:morning,afternoon,night,on_call',
            'entries.*.start_time' => 'required',
            'entries.*.end_time' => 'required',
        ]);

        $count = 0;
        $perUser = [];

        foreach ($validated['user_ids'] as $uid) {
            foreach ($validated['entries'] as $entry) {
                $shift = Shift::updateOrCreate(
                    ['user_id' => $uid, 'shift_date' => $entry['shift_date']],
                    [
                        'shift_type' => $entry['shift_type'],
                        'start_time' => $entry['start_time'],
                        'end_time' => $entry['end_time'],
                    ]
                );
                $count++;
                $perUser[$uid][] = $entry;
            }
        }

        $me = Auth::user();
        foreach ($perUser as $uid => $entries) {
            if ($uid === $me->id) continue;
            $target = User::find($uid);
            if (!$target) continue;
            $shiftCount = count($entries);
            $first = $entries[0];
            $firstLabel = $first['shift_type'] === 'on_call' ? 'On Call' : ucfirst($first['shift_type']);
            $body = $shiftCount === 1
                ? \Carbon\Carbon::parse($first['shift_date'])->format('l, F j') . ' · ' . $firstLabel
                : "{$shiftCount} new shifts added · first on " . \Carbon\Carbon::parse($first['shift_date'])->format('M j');
            \App\Models\UserNotification::notify(
                $target->id,
                'shift.bulk',
                $shiftCount === 1 ? "Shift assigned: {$firstLabel}" : "{$shiftCount} shifts assigned",
                $body,
                route('staff.show', ['user' => $target->id, 'year' => \Carbon\Carbon::parse($first['shift_date'])->year, 'month' => \Carbon\Carbon::parse($first['shift_date'])->month]),
                'fa-calendar-plus',
                'amber'
            );
        }

        ActivityLog::create([
            'user_id' => $me->id,
            'action' => 'staff.shift.bulk_assign',
            'entity_type' => Shift::class,
            'entity_id' => null,
            'details' => "Bulk-assigned {$count} shifts across " . count($validated['user_ids']) . ' staff',
        ]);

        return response()->json(['ok' => true, 'count' => $count, 'staff' => count($validated['user_ids'])]);
    }
}
