@extends('layouts.app')
@section('title', 'Staff')
@section('page-title', 'Staff Directory')

@section('content')
@php
    $isAdmin = Auth::user()->role === 'admin';
    $grouped = $staff->groupBy('role');
    $roleConfig = [
        'admin'     => ['label'=>'Administrators','plural'=>'Admin','icon'=>'fa-user-shield','grad'=>'from-brand-400 to-brand-600',  'bg'=>'bg-brand-50',  'border'=>'border-brand-200',  'text'=>'text-brand-700',  'iconBg'=>'bg-brand-500',   'shadow'=>'shadow-brand-200', 'softBg'=>'from-brand-50 to-brand-100/50'],
        'doctor'    => ['label'=>'Doctors',       'plural'=>'Doctor','icon'=>'fa-user-doctor','grad'=>'from-purple-400 to-purple-600','bg'=>'bg-purple-50','border'=>'border-purple-200','text'=>'text-purple-700','iconBg'=>'bg-purple-500',  'shadow'=>'shadow-purple-200','softBg'=>'from-purple-50 to-purple-100/50'],
        'nurse'     => ['label'=>'Nurses',        'plural'=>'Nurse','icon'=>'fa-user-nurse', 'grad'=>'from-pink-400 to-pink-600',   'bg'=>'bg-pink-50',  'border'=>'border-pink-200',  'text'=>'text-pink-700',  'iconBg'=>'bg-pink-500',    'shadow'=>'shadow-pink-200',  'softBg'=>'from-pink-50 to-pink-100/50'],
        'assistant' => ['label'=>'Assistants',    'plural'=>'Assistant','icon'=>'fa-user',   'grad'=>'from-amber-400 to-amber-600',  'bg'=>'bg-amber-50', 'border'=>'border-amber-200', 'text'=>'text-amber-700', 'iconBg'=>'bg-amber-500',   'shadow'=>'shadow-amber-200', 'softBg'=>'from-amber-50 to-amber-100/50'],
    ];
    $onlineCount = $staff->filter(fn($s) => $s->isOnline())->count();
@endphp

<div class="space-y-5">

    <!-- Header -->
    <div class="flex items-center justify-between flex-wrap gap-3">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Staff Directory</h1>
            <p class="text-sm text-gray-500 mt-0.5">
                {{ $isAdmin ? 'Manage staff and assign shifts' : 'View clinic staff and shift schedules' }}
            </p>
        </div>
        @if($isAdmin)
        <button onclick="openAddStaffModal()" class="btn-primary">
            <i class="fa-solid fa-user-plus"></i> Add Staff
        </button>
        @endif
    </div>

    <!-- Colored stat cards -->
    <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
        <div class="rounded-2xl p-5 bg-gradient-to-br from-slate-50 to-slate-100/50 border-2 border-slate-200">
            <div class="flex items-center justify-between mb-3">
                <div class="w-10 h-10 rounded-xl bg-slate-700 flex items-center justify-center shadow-md shadow-slate-200">
                    <i class="fa-solid fa-users text-white text-sm"></i>
                </div>
            </div>
            <p class="text-3xl font-extrabold text-slate-900">{{ $staff->count() }}</p>
            <p class="text-xs font-semibold text-slate-700/70 mt-1">Total Staff</p>
        </div>
        <div class="rounded-2xl p-5 bg-gradient-to-br from-emerald-50 to-emerald-100/50 border-2 border-emerald-200">
            <div class="flex items-center justify-between mb-3">
                <div class="w-10 h-10 rounded-xl bg-emerald-500 flex items-center justify-center shadow-md shadow-emerald-200">
                    <i class="fa-solid fa-circle-check text-white text-sm"></i>
                </div>
            </div>
            <p class="text-3xl font-extrabold text-emerald-900">{{ $onlineCount }}</p>
            <p class="text-xs font-semibold text-emerald-700/70 mt-1">Online Now</p>
        </div>
        @foreach(['admin','doctor','nurse','assistant'] as $role)
            @php $cfg = $roleConfig[$role]; $count = $grouped[$role]?->count() ?? 0; @endphp
            <div class="rounded-2xl p-5 bg-gradient-to-br {{ $cfg['softBg'] }} border-2 {{ $cfg['border'] }}">
                <div class="flex items-center justify-between mb-3">
                    <div class="w-10 h-10 rounded-xl {{ $cfg['iconBg'] }} flex items-center justify-center shadow-md {{ $cfg['shadow'] }}">
                        <i class="fa-solid {{ $cfg['icon'] }} text-white text-sm"></i>
                    </div>
                </div>
                <p class="text-3xl font-extrabold {{ $cfg['text'] }}">{{ $count }}</p>
                <p class="text-xs font-semibold {{ $cfg['text'] }}/70 mt-1">{{ $cfg['label'] }}</p>
            </div>
        @endforeach
    </div>

    <!-- Staff grouped by role with section headers -->
    @foreach(['admin','doctor','nurse','assistant'] as $role)
        @if(isset($grouped[$role]) && $grouped[$role]->count() > 0)
        @php $cfg = $roleConfig[$role]; @endphp

        <div class="space-y-3">
            <!-- Section header bar -->
            <div class="flex items-center gap-3 {{ $cfg['bg'] }} border-2 {{ $cfg['border'] }} rounded-xl px-4 py-3">
                <div class="w-9 h-9 {{ $cfg['iconBg'] }} rounded-lg flex items-center justify-center shadow-sm {{ $cfg['shadow'] }}">
                    <i class="fa-solid {{ $cfg['icon'] }} text-white text-sm"></i>
                </div>
                <div class="flex-1">
                    <h2 class="font-bold {{ $cfg['text'] }} text-base">{{ $cfg['label'] }}</h2>
                    <p class="text-xs {{ $cfg['text'] }}/70">{{ $grouped[$role]->count() }} {{ Str::lower($cfg['label']) }} on the team</p>
                </div>
            </div>

            <!-- Cards in this group -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($grouped[$role] as $member)
                @php
                    $isOnline = $member->isOnline();
                    $todayShift = $shifts->where('user_id', $member->id)->where('shift_date', today()->toDateString())->first();
                    $upcoming = $shifts->where('user_id', $member->id)
                        ->where('shift_date', '>', today()->toDateString())
                        ->sortBy('shift_date')->first();
                @endphp
                <div class="card overflow-hidden hover:shadow-lg hover:border-{{ $role === 'admin' ? 'brand' : ($role === 'doctor' ? 'purple' : ($role === 'nurse' ? 'pink' : 'amber')) }}-300 transition-all border-2 {{ $cfg['border'] }}">
                    <!-- Top accent strip -->
                    <div class="h-1.5 bg-gradient-to-r {{ $cfg['grad'] }}"></div>

                    <div class="p-5">
                        <!-- Identity -->
                        <div class="flex items-start justify-between mb-4">
                            <div class="flex items-center gap-3">
                                <div class="h-14 w-14 rounded-2xl bg-gradient-to-br {{ $cfg['grad'] }} flex items-center justify-center text-white text-lg font-bold flex-shrink-0">
                                    {{ strtoupper(substr($member->name, 0, 2)) }}
                                </div>
                                <div>
                                    <h3 class="font-bold text-gray-900 leading-snug">{{ $member->name }}</h3>
                                    <p class="text-xs text-gray-400 capitalize">{{ $member->role }}</p>
                                    @if($member->specialization)
                                    <p class="text-xs {{ $cfg['text'] }} font-medium">{{ $member->specialization }}</p>
                                    @endif
                                </div>
                            </div>
                            <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-xs font-medium flex-shrink-0
                                {{ $isOnline ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-100 text-gray-500' }}">
                                <span class="w-1.5 h-1.5 rounded-full {{ $isOnline ? 'bg-emerald-400 animate-pulse' : 'bg-gray-300' }}"></span>
                                {{ $isOnline ? 'Online' : 'Offline' }}
                            </span>
                        </div>

                        <!-- Contact -->
                        <div class="space-y-1.5 mb-4">
                            <div class="flex items-center gap-2 text-xs text-gray-600">
                                <i class="fa-solid fa-envelope w-4 text-gray-400"></i>
                                <span class="truncate">{{ $member->email }}</span>
                            </div>
                            <div class="flex items-center gap-2 text-xs text-gray-600">
                                <i class="fa-solid fa-phone w-4 text-gray-400"></i>
                                <span>{{ $member->phone ?? 'No phone' }}</span>
                            </div>
                        </div>

                        <!-- Today's shift -->
                        @if($todayShift)
                        <div class="{{ $cfg['bg'] }} border {{ $cfg['border'] }} rounded-xl p-3 mb-3">
                            <p class="text-[10px] font-bold {{ $cfg['text'] }} uppercase tracking-wider mb-1">Today &bull; {{ ucfirst($todayShift->shift_type) }}</p>
                            <p class="text-sm font-bold text-gray-800">
                                {{ \Carbon\Carbon::parse($todayShift->start_time)->format('g:i A') }}
                                — {{ \Carbon\Carbon::parse($todayShift->end_time)->format('g:i A') }}
                            </p>
                        </div>
                        @else
                        <div class="bg-gray-50 border border-gray-100 rounded-xl p-3 mb-3 text-center">
                            <p class="text-xs text-gray-400">No shift today</p>
                        </div>
                        @endif

                        <!-- Upcoming shift preview -->
                        @if($upcoming)
                        <div class="text-xs text-gray-500 mb-3 flex items-center gap-2">
                            <i class="fa-solid fa-calendar-day text-gray-400"></i>
                            Next: <span class="font-medium">{{ \Carbon\Carbon::parse($upcoming->shift_date)->format('M j') }}, {{ ucfirst($upcoming->shift_type) }}</span>
                        </div>
                        @endif

                        <!-- Actions -->
                        <div class="flex gap-2">
                            @if($isAdmin)
                            <button onclick="openShiftModal({{ $member->id }}, '{{ addslashes($member->name) }}')"
                                    class="flex-1 btn-primary py-2 text-xs justify-center">
                                <i class="fa-solid fa-calendar-plus"></i> {{ $todayShift ? 'Revise Shift' : 'Assign Shift' }}
                            </button>
                            @else
                            <span class="flex-1 inline-flex items-center justify-center gap-2 px-3 py-2 bg-gray-50 border border-gray-100 rounded-xl text-xs text-gray-400 cursor-not-allowed" title="Only admins can manage shifts">
                                <i class="fa-solid fa-lock"></i> Admin only
                            </span>
                            @endif
                            <a href="{{ route('chat.index', ['with' => $member->id]) }}"
                               class="w-9 h-9 flex items-center justify-center rounded-xl bg-gray-100 text-gray-600 hover:bg-brand-100 hover:text-brand-600 text-sm transition-colors flex-shrink-0"
                               title="Message {{ $member->name }}">
                                <i class="fa-solid fa-comment"></i>
                            </a>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif
    @endforeach
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
['shiftModal','addStaffModal'].forEach(id => {
    document.getElementById(id)?.addEventListener('click', e => { if (e.target === e.currentTarget) { closeShiftModal(); closeAddStaffModal(); } });
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
