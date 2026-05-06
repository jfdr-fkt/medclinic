@extends('layouts.app')
@section('title', 'Staff')
@section('page-title', 'Staff Directory')

@section('content')
<div class="space-y-5">

    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Staff Directory</h1>
            <p class="text-sm text-gray-500 mt-0.5">Manage nurses, doctors, and their shifts</p>
        </div>
        <button onclick="openAddStaffModal()" class="btn-primary">
            <i class="fa-solid fa-user-plus"></i> Add Staff
        </button>
    </div>

    <!-- Stat Cards -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        @php
            $roleIcons = ['admin'=>['fa-user-shield','from-brand-500 to-brand-700','bg-brand-100 text-brand-700'],
                          'doctor'=>['fa-user-doctor','from-purple-500 to-purple-700','bg-purple-100 text-purple-700'],
                          'nurse'=>['fa-user-nurse','from-pink-500 to-pink-700','bg-pink-100 text-pink-700'],
                          'assistant'=>['fa-user','from-amber-500 to-amber-700','bg-amber-100 text-amber-700']];
            $onlineCount = $staff->filter(fn($s)=>$s->isOnline())->count();
        @endphp
        <div class="card p-5 relative overflow-hidden">
            <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-brand-400 to-brand-600 rounded-t-2xl"></div>
            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Total Staff</p>
            <p class="text-3xl font-bold text-gray-900 mt-1">{{ $staff->count() }}</p>
            <div class="mt-3 flex items-center gap-1.5 text-xs text-gray-500">
                <i class="fa-solid fa-users text-brand-400"></i> All roles
            </div>
        </div>
        <div class="card p-5 relative overflow-hidden">
            <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-emerald-400 to-emerald-600 rounded-t-2xl"></div>
            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Online Now</p>
            <p class="text-3xl font-bold text-emerald-600 mt-1">{{ $onlineCount }}</p>
            <div class="mt-3 flex items-center gap-1.5 text-xs text-gray-500">
                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span> Active
            </div>
        </div>
        <div class="card p-5 relative overflow-hidden">
            <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-purple-400 to-purple-600 rounded-t-2xl"></div>
            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Doctors</p>
            <p class="text-3xl font-bold text-purple-700 mt-1">{{ $staff->where('role','doctor')->count() }}</p>
            <div class="mt-3 flex items-center gap-1.5 text-xs text-gray-500">
                <i class="fa-solid fa-user-doctor text-purple-400"></i> Medical staff
            </div>
        </div>
        <div class="card p-5 relative overflow-hidden">
            <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-pink-400 to-pink-600 rounded-t-2xl"></div>
            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Nurses</p>
            <p class="text-3xl font-bold text-pink-700 mt-1">{{ $staff->where('role','nurse')->count() }}</p>
            <div class="mt-3 flex items-center gap-1.5 text-xs text-gray-500">
                <i class="fa-solid fa-user-nurse text-pink-400"></i> Nursing staff
            </div>
        </div>
    </div>

    <!-- Staff Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
        @foreach($staff as $member)
        @php
            $isOnline  = $member->isOnline();
            $todayShift = $shifts->where('user_id', $member->id)->where('shift_date', today()->toDateString())->first();
            $gradients  = ['admin'=>'from-brand-400 to-brand-700','doctor'=>'from-purple-400 to-purple-600','nurse'=>'from-pink-400 to-pink-600','assistant'=>'from-amber-400 to-amber-600'];
            $grad = $gradients[$member->role] ?? 'from-gray-400 to-gray-600';
        @endphp
        <div class="card overflow-hidden hover:shadow-md transition-shadow">
            <!-- Top bar accent -->
            <div class="h-1 bg-gradient-to-r {{ $grad }}"></div>
            <div class="p-5">
                <!-- Identity -->
                <div class="flex items-start justify-between mb-4">
                    <div class="flex items-center gap-3">
                        <div class="h-14 w-14 rounded-2xl bg-gradient-to-br {{ $grad }} flex items-center justify-center text-white text-xl font-bold flex-shrink-0">
                            {{ strtoupper(substr($member->name, 0, 2)) }}
                        </div>
                        <div>
                            <h3 class="font-bold text-gray-900 leading-snug">{{ $member->name }}</h3>
                            <p class="text-sm text-gray-500 capitalize">{{ $member->role }}</p>
                            @if($member->specialization)
                            <p class="text-xs text-gray-400">{{ $member->specialization }}</p>
                            @endif
                        </div>
                    </div>
                    <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-xs font-medium flex-shrink-0
                        {{ $isOnline ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-100 text-gray-500' }}">
                        <span class="w-1.5 h-1.5 rounded-full {{ $isOnline ? 'bg-emerald-400 animate-pulse' : 'bg-gray-300' }}"></span>
                        {{ $isOnline ? 'Online' : 'Offline' }}
                    </span>
                </div>

                <!-- Contact info -->
                <div class="space-y-1.5 mb-4 text-sm text-gray-600">
                    <div class="flex items-center gap-2">
                        <i class="fa-solid fa-envelope w-4 text-gray-400 text-xs"></i>
                        <span class="truncate text-xs">{{ $member->email }}</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <i class="fa-solid fa-phone w-4 text-gray-400 text-xs"></i>
                        <span class="text-xs">{{ $member->phone ?? 'No phone' }}</span>
                    </div>
                    @if($member->last_seen_at)
                    <div class="flex items-center gap-2">
                        <i class="fa-solid fa-clock w-4 text-gray-400 text-xs"></i>
                        <span class="text-xs text-gray-400">Last seen {{ $member->last_seen_at->diffForHumans() }}</span>
                    </div>
                    @endif
                </div>

                <!-- Today's Shift -->
                @if($todayShift)
                <div class="bg-brand-50 border border-brand-100 rounded-xl p-3 mb-4">
                    <p class="text-xs text-gray-500 mb-0.5">Today's Shift</p>
                    <p class="text-sm font-semibold text-brand-800 capitalize">{{ $todayShift->shift_type }}</p>
                    <p class="text-xs text-brand-600">
                        {{ \Carbon\Carbon::parse($todayShift->start_time)->format('g:i A') }}
                        — {{ \Carbon\Carbon::parse($todayShift->end_time)->format('g:i A') }}
                    </p>
                </div>
                @else
                <div class="bg-gray-50 border border-gray-100 rounded-xl p-3 mb-4 text-center">
                    <p class="text-xs text-gray-400">No shift assigned today</p>
                </div>
                @endif

                <!-- Actions -->
                <div class="flex gap-2">
                    <button onclick="openShiftModal({{ $member->id }}, '{{ addslashes($member->name) }}')"
                            class="flex-1 btn-primary py-2 text-xs justify-center">
                        <i class="fa-solid fa-calendar-plus"></i> Assign Shift
                    </button>
                    <button onclick="toggleStatus({{ $member->id }}, this)"
                            class="w-9 h-9 flex items-center justify-center rounded-xl text-sm transition-colors flex-shrink-0
                                {{ $isOnline ? 'bg-red-100 text-red-600 hover:bg-red-200' : 'bg-emerald-100 text-emerald-600 hover:bg-emerald-200' }}"
                            title="{{ $isOnline ? 'Mark Offline' : 'Mark Online' }}">
                        <i class="fa-solid fa-power-off"></i>
                    </button>
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

<!-- ── Assign Shift Modal ── -->
<div id="shiftModal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4 backdrop-blur-sm">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md">
        <div class="flex items-center justify-between px-6 py-5 border-b border-gray-100">
            <div>
                <h3 class="text-lg font-bold text-gray-900">Assign Shift</h3>
                <p class="text-xs text-gray-500 mt-0.5">Schedule a work shift for this staff member</p>
            </div>
            <button onclick="closeShiftModal()" class="w-8 h-8 rounded-xl flex items-center justify-center text-gray-400 hover:text-gray-600 hover:bg-gray-100">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
        <form id="shiftForm" action="{{ route('staff.shifts.store') }}" method="POST" class="px-6 py-5 space-y-4">
            @csrf
            <input type="hidden" name="user_id" id="shiftUserId">
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5 uppercase tracking-wide">Staff Member</label>
                <input type="text" id="shiftStaffName" readonly class="input bg-gray-50 text-gray-500">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5 uppercase tracking-wide">Shift Type <span class="text-red-500">*</span></label>
                <select name="shift_type" id="shiftTypeSelect" required class="input">
                    <option value="morning">Morning (8:00 AM – 4:00 PM)</option>
                    <option value="afternoon">Afternoon (12:00 PM – 8:00 PM)</option>
                    <option value="night">Night (8:00 PM – 8:00 AM)</option>
                    <option value="on_call">On Call (Custom hours)</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5 uppercase tracking-wide">Date <span class="text-red-500">*</span></label>
                <input type="date" name="shift_date" required value="{{ date('Y-m-d') }}" class="input">
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5 uppercase tracking-wide">Start <span class="text-red-500">*</span></label>
                    <input type="time" name="start_time" id="startTime" required value="08:00" class="input">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5 uppercase tracking-wide">End <span class="text-red-500">*</span></label>
                    <input type="time" name="end_time" id="endTime" required value="16:00" class="input">
                </div>
            </div>
            <div class="flex gap-2 pt-2 border-t border-gray-100">
                <button type="button" onclick="closeShiftModal()" class="btn-secondary flex-1 justify-center">Cancel</button>
                <button type="submit" class="btn-primary flex-1 justify-center">
                    <i class="fa-solid fa-calendar-check"></i> Assign
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ── Add Staff Modal ── -->
<div id="addStaffModal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4 backdrop-blur-sm">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between px-6 py-5 border-b border-gray-100">
            <div>
                <h3 class="text-lg font-bold text-gray-900">Add Staff Member</h3>
                <p class="text-xs text-gray-500 mt-0.5">Create a new account for a clinic staff member</p>
            </div>
            <button onclick="closeAddStaffModal()" class="w-8 h-8 rounded-xl flex items-center justify-center text-gray-400 hover:text-gray-600 hover:bg-gray-100">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
        <form method="POST" action="{{ route('staff.store') }}" class="px-6 py-5 space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5 uppercase tracking-wide">Full Name <span class="text-red-500">*</span></label>
                <input type="text" name="name" required class="input" placeholder="e.g. Dr. Maria Santos">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5 uppercase tracking-wide">Email <span class="text-red-500">*</span></label>
                <input type="email" name="email" required class="input" placeholder="staff@clinic.com">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5 uppercase tracking-wide">Password <span class="text-red-500">*</span></label>
                <input type="password" name="password" required minlength="6" class="input" placeholder="Minimum 6 characters">
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5 uppercase tracking-wide">Role <span class="text-red-500">*</span></label>
                    <select name="role" required class="input">
                        <option value="nurse">Nurse</option>
                        <option value="doctor">Doctor</option>
                        <option value="assistant">Assistant</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5 uppercase tracking-wide">Phone</label>
                    <input type="tel" name="phone" class="input" placeholder="09XX-XXX-XXXX">
                </div>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5 uppercase tracking-wide">Specialization</label>
                <input type="text" name="specialization" class="input" placeholder="e.g. Cardiology, Pediatrics">
            </div>
            <div class="flex gap-2 pt-2 border-t border-gray-100">
                <button type="button" onclick="closeAddStaffModal()" class="btn-secondary flex-1 justify-center">Cancel</button>
                <button type="submit" class="btn-primary flex-1 justify-center">
                    <i class="fa-solid fa-user-plus"></i> Add Staff
                </button>
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
    document.getElementById(id).addEventListener('click', e => { if (e.target === e.currentTarget) { closeShiftModal(); closeAddStaffModal(); } });
});

document.getElementById('shiftTypeSelect').addEventListener('change', function () {
    const times = { morning:['08:00','16:00'], afternoon:['12:00','20:00'], night:['20:00','08:00'], on_call:['',''] };
    const [s, e] = times[this.value] || ['',''];
    document.getElementById('startTime').value = s;
    document.getElementById('endTime').value   = e;
});

function toggleStatus(userId, btn) {
    fetch(`/staff/${userId}/toggle-status`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json',
        }
    })
    .then(r => r.json())
    .then(() => location.reload());
}
</script>
@endpush
@endsection
