@extends('layouts.app')
@section('title', 'Staff')
@section('page-title', 'Staff Directory')

@section('content')
@php
    $isAdmin = Auth::user()->role === 'admin';
    $roleColors = [
        'admin'     => ['bg'=>'bg-brand-100',  'text'=>'text-brand-700',  'grad'=>'from-brand-400 to-brand-600',  'icon'=>'fa-user-shield'],
        'doctor'    => ['bg'=>'bg-purple-100', 'text'=>'text-purple-700', 'grad'=>'from-purple-400 to-purple-600','icon'=>'fa-user-doctor'],
        'nurse'     => ['bg'=>'bg-pink-100',   'text'=>'text-pink-700',   'grad'=>'from-pink-400 to-pink-600',    'icon'=>'fa-user-nurse'],
        'assistant' => ['bg'=>'bg-amber-100',  'text'=>'text-amber-700',  'grad'=>'from-amber-400 to-amber-600',  'icon'=>'fa-user'],
    ];
@endphp

<div class="space-y-5">

    <!-- Header -->
    <div class="flex items-center justify-between flex-wrap gap-3">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Staff Directory</h1>
            <p class="text-sm text-gray-500 mt-0.5">{{ $staff->total() }} members &bull; {{ $isAdmin ? 'Manage staff and assign shifts' : 'View clinic staff and shifts' }}</p>
        </div>
        @if($isAdmin)
        <button onclick="openAddStaffModal()" class="btn-primary">
            <i class="fa-solid fa-user-plus"></i> Add Staff
        </button>
        @endif
    </div>

    <!-- Filters -->
    <form method="GET" action="{{ route('staff.index') }}" class="card p-4">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
            <div class="md:col-span-2 relative">
                <i class="fa-solid fa-search absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 text-sm pointer-events-none"></i>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by name, email, specialization…" class="input pl-10">
            </div>
            <select name="role" class="input">
                <option value="">All Roles</option>
                <option value="admin"     {{ request('role')==='admin'?'selected':'' }}>Administrator</option>
                <option value="doctor"    {{ request('role')==='doctor'?'selected':'' }}>Doctor</option>
                <option value="nurse"     {{ request('role')==='nurse'?'selected':'' }}>Nurse</option>
                <option value="assistant" {{ request('role')==='assistant'?'selected':'' }}>Assistant</option>
            </select>
            <select name="status" class="input">
                <option value="">All Statuses</option>
                <option value="online"  {{ request('status')==='online'?'selected':'' }}>Online</option>
                <option value="offline" {{ request('status')==='offline'?'selected':'' }}>Offline</option>
            </select>
        </div>
        <div class="flex items-center justify-between mt-3">
            <div class="flex items-center gap-1 text-sm text-gray-500">
                <span>Sort:</span>
                @foreach(['name'=>'Name','role'=>'Role','last_seen_at'=>'Last Seen'] as $f=>$label)
                <a href="{{ request()->fullUrlWithQuery(['sort'=>$f,'direction'=>($sortField===$f&&$sortDir==='asc')?'desc':'asc']) }}"
                   class="px-2.5 py-1 rounded-lg font-medium transition-colors {{ $sortField===$f ? 'bg-brand-600 text-white' : 'text-gray-500 hover:bg-gray-100' }}">
                    {{ $label }}@if($sortField===$f) <i class="fa-solid fa-arrow-{{ $sortDir==='asc'?'up':'down' }} text-xs"></i>@endif
                </a>
                @endforeach
            </div>
            <div class="flex gap-2">
                <button type="submit" class="btn-primary py-1.5 text-xs">Apply</button>
                <a href="{{ route('staff.index') }}" class="btn-secondary py-1.5 text-xs">Clear</a>
            </div>
        </div>
    </form>

    <!-- Staff table -->
    <div class="card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full">
                <thead>
                    <tr class="bg-gradient-to-r from-gray-50 to-slate-50 border-b-2 border-gray-200 divide-x divide-gray-200">
                        <th class="th">Staff Member</th>
                        <th class="th text-center">Role</th>
                        <th class="th text-center">Contact</th>
                        <th class="th text-center">Today's Shift</th>
                        <th class="th text-center">Status</th>
                        <th class="th text-center">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($staff as $member)
                    @php
                        $isOnline = $member->isOnline();
                        $todayShift = $shifts->where('user_id', $member->id)->where('shift_date', today()->toDateString())->first();
                        $cfg = $roleColors[$member->role] ?? $roleColors['assistant'];
                    @endphp
                    <tr class="hover:bg-brand-50/30 transition-colors divide-x divide-gray-100">
                        <td class="td">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-gradient-to-br {{ $cfg['grad'] }} flex items-center justify-center text-white text-sm font-bold flex-shrink-0">
                                    {{ strtoupper(substr($member->name, 0, 2)) }}
                                </div>
                                <div>
                                    <p class="font-semibold text-gray-900">{{ $member->name }}</p>
                                    <p class="text-xs text-gray-400">{{ $member->specialization ?? '—' }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="td text-center">
                            <span class="inline-flex items-center gap-1.5 {{ $cfg['bg'] }} {{ $cfg['text'] }} px-2.5 py-1 rounded-full text-xs font-semibold capitalize">
                                <i class="fa-solid {{ $cfg['icon'] }} text-[10px]"></i> {{ $member->role }}
                            </span>
                        </td>
                        <td class="td text-center">
                            <p class="text-xs text-gray-700">{{ $member->email }}</p>
                            <p class="text-xs text-gray-400">{{ $member->phone ?? 'No phone' }}</p>
                        </td>
                        <td class="td text-center">
                            @if($todayShift)
                            <p class="text-xs font-semibold text-gray-800 capitalize">{{ $todayShift->shift_type }}</p>
                            <p class="text-xs text-gray-500">
                                {{ \Carbon\Carbon::parse($todayShift->start_time)->format('g:i A') }} —
                                {{ \Carbon\Carbon::parse($todayShift->end_time)->format('g:i A') }}
                            </p>
                            @else
                            <span class="text-xs text-gray-300">No shift today</span>
                            @endif
                        </td>
                        <td class="td text-center">
                            <span class="inline-flex items-center gap-1.5 px-2 py-1 rounded-full text-xs font-medium {{ $isOnline ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-100 text-gray-500' }}">
                                <span class="w-1.5 h-1.5 rounded-full {{ $isOnline ? 'bg-emerald-400 animate-pulse' : 'bg-gray-300' }}"></span>
                                {{ $isOnline ? 'Online' : 'Offline' }}
                            </span>
                            @if(!$isOnline && $member->last_seen_at)
                            <p class="text-[10px] text-gray-400 mt-0.5">{{ $member->last_seen_at->diffForHumans() }}</p>
                            @endif
                        </td>
                        <td class="td text-center">
                            <div class="flex items-center justify-center gap-1">
                                @if($isAdmin)
                                <button onclick="openShiftModal({{ $member->id }}, '{{ addslashes($member->name) }}')"
                                        class="w-8 h-8 rounded-lg flex items-center justify-center hover:bg-brand-100 text-gray-400 hover:text-brand-600 transition-colors"
                                        title="{{ $todayShift ? 'Revise Shift' : 'Assign Shift' }}">
                                    <i class="fa-solid fa-calendar-plus text-sm"></i>
                                </button>
                                @endif
                                <a href="{{ route('chat.index', ['with' => $member->id]) }}"
                                   class="w-8 h-8 rounded-lg flex items-center justify-center hover:bg-purple-100 text-gray-400 hover:text-purple-600 transition-colors"
                                   title="Message">
                                    <i class="fa-solid fa-comment text-sm"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="py-16 text-center">
                            <div class="w-16 h-16 bg-gray-100 rounded-2xl flex items-center justify-center mx-auto mb-3">
                                <i class="fa-solid fa-users text-gray-400 text-2xl"></i>
                            </div>
                            <p class="text-gray-500 font-medium">No staff match your filters</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($staff->hasPages())
        <div class="px-6 py-3 border-t border-gray-100 bg-gray-50/50">
            {{ $staff->links() }}
        </div>
        @endif
    </div>
</div>

@if($isAdmin)
<!-- ── Assign Shift Modal ── -->
<div id="shiftModal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4 backdrop-blur-sm">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md">
        <div class="bg-gradient-to-r from-brand-600 to-brand-700 px-6 py-5 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-bold">Assign Shift</h3>
                    <p class="text-xs text-white/80 mt-0.5">Schedule a work shift for this staff member</p>
                </div>
                <button type="button" onclick="closeShiftModal()" class="w-8 h-8 rounded-xl flex items-center justify-center text-white/80 hover:text-white hover:bg-white/20">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
        </div>
        <form id="shiftForm" action="{{ route('staff.shifts.store') }}" method="POST" class="px-6 py-5 space-y-4">
            @csrf
            <input type="hidden" name="user_id" id="shiftUserId">
            <div>
                <label class="label">Staff Member</label>
                <input type="text" id="shiftStaffName" readonly class="input bg-gray-50 text-gray-500">
            </div>
            <div>
                <label class="label">Shift Type <span class="text-red-500">*</span></label>
                <select name="shift_type" id="shiftTypeSelect" required class="input">
                    <option value="morning">Day Shift (7:00 AM – 3:00 PM)</option>
                    <option value="afternoon">Evening Shift (3:00 PM – 11:00 PM)</option>
                    <option value="night">Night Shift (11:00 PM – 7:00 AM)</option>
                    <option value="on_call">On Call (Custom hours)</option>
                </select>
            </div>
            <div>
                <label class="label">Date <span class="text-red-500">*</span></label>
                <input type="date" name="shift_date" required value="{{ date('Y-m-d') }}" class="input">
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="label">Start <span class="text-red-500">*</span></label>
                    <input type="time" name="start_time" id="startTime" required value="07:00" class="input">
                </div>
                <div>
                    <label class="label">End <span class="text-red-500">*</span></label>
                    <input type="time" name="end_time" id="endTime" required value="15:00" class="input">
                </div>
            </div>
            <div class="flex justify-between gap-3 pt-3 border-t border-gray-100">
                <button type="button" onclick="closeShiftModal()" class="btn-secondary"><i class="fa-solid fa-xmark"></i> Cancel</button>
                <button type="submit" class="btn-primary"><i class="fa-solid fa-calendar-check"></i> Save Shift</button>
            </div>
        </form>
    </div>
</div>

<!-- ── Add Staff Modal ── -->
<div id="addStaffModal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4 backdrop-blur-sm">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md max-h-[90vh] overflow-y-auto">
        <div class="bg-gradient-to-r from-brand-600 to-brand-700 px-6 py-5 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-bold">Add Staff Member</h3>
                    <p class="text-xs text-white/80 mt-0.5">Create an account for a new clinic staff member</p>
                </div>
                <button type="button" onclick="closeAddStaffModal()" class="w-8 h-8 rounded-xl flex items-center justify-center text-white/80 hover:text-white hover:bg-white/20">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
        </div>
        <form method="POST" action="{{ route('staff.store') }}" class="px-6 py-5 space-y-4">
            @csrf
            <div><label class="label">Full Name <span class="text-red-500">*</span></label><input type="text" name="name" required class="input" placeholder="e.g. Dr. Maria Santos"></div>
            <div><label class="label">Email <span class="text-red-500">*</span></label><input type="email" name="email" required class="input" placeholder="staff@clinic.com"></div>
            <div><label class="label">Password <span class="text-red-500">*</span></label><input type="password" name="password" required minlength="6" class="input" placeholder="Minimum 6 characters"></div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="label">Role <span class="text-red-500">*</span></label>
                    <select name="role" required class="input">
                        <option value="nurse">Nurse</option>
                        <option value="doctor">Doctor</option>
                        <option value="assistant">Assistant</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>
                <div><label class="label">Phone</label><input type="tel" name="phone" class="input" placeholder="09XX-XXX-XXXX"></div>
            </div>
            <div><label class="label">Specialization</label><input type="text" name="specialization" class="input" placeholder="e.g. Cardiology"></div>
            <div class="flex justify-between gap-3 pt-3 border-t border-gray-100">
                <button type="button" onclick="closeAddStaffModal()" class="btn-secondary"><i class="fa-solid fa-xmark"></i> Cancel</button>
                <button type="submit" class="btn-primary"><i class="fa-solid fa-user-plus"></i> Add Staff</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
function openShiftModal(userId, userName) {
    document.getElementById('shiftUserId').value = userId;
    document.getElementById('shiftStaffName').value = userName;
    document.getElementById('shiftModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}
function closeShiftModal()    { document.getElementById('shiftModal').classList.add('hidden'); document.body.style.overflow = ''; }
function openAddStaffModal()  { document.getElementById('addStaffModal').classList.remove('hidden'); document.body.style.overflow = 'hidden'; }
function closeAddStaffModal() { document.getElementById('addStaffModal').classList.add('hidden'); document.body.style.overflow = ''; }

document.addEventListener('keydown', e => {
    if (e.key === 'Escape') { closeShiftModal(); closeAddStaffModal(); }
});

document.getElementById('shiftTypeSelect').addEventListener('change', function () {
    const times = { morning:['07:00','15:00'], afternoon:['15:00','23:00'], night:['23:00','07:00'], on_call:['09:00','17:00'] };
    const [s, e] = times[this.value] || ['',''];
    document.getElementById('startTime').value = s;
    document.getElementById('endTime').value   = e;
});
</script>
@endpush
@endif
@endsection
