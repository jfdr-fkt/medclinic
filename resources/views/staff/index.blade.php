@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Header -->
    <div class="flex justify-between items-center mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Staff Directory</h1>
            <p class="mt-1 text-sm text-gray-500">Manage your clinic staff and shifts</p>
        </div>
        <button onclick="openAddStaffModal()" class="inline-flex items-center px-4 py-2 bg-brand-600 text-white rounded-lg font-semibold hover:bg-brand-700 shadow-lg transition-all">
            <i class="fa-solid fa-user-plus mr-2"></i> Add Staff
        </button>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-xl shadow-sm p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Total Staff</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $staff->count() }}</p>
                </div>
                <div class="bg-blue-100 p-3 rounded-full">
                    <i class="fa-solid fa-users text-blue-600 text-xl"></i>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Online Now</p>
                    <p class="text-2xl font-bold text-green-600">{{ $staff->where('is_online', true)->count() }}</p>
                </div>
                <div class="bg-green-100 p-3 rounded-full">
                    <i class="fa-solid fa-circle text-green-600 text-xl"></i>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Doctors</p>
                    <p class="text-2xl font-bold text-purple-600">{{ $staff->where('role', 'doctor')->count() }}</p>
                </div>
                <div class="bg-purple-100 p-3 rounded-full">
                    <i class="fa-solid fa-user-doctor text-purple-600 text-xl"></i>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Nurses</p>
                    <p class="text-2xl font-bold text-pink-600">{{ $staff->where('role', 'nurse')->count() }}</p>
                </div>
                <div class="bg-pink-100 p-3 rounded-full">
                    <i class="fa-solid fa-user-nurse text-pink-600 text-xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Staff Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach($staff as $member)
        <div class="bg-white rounded-xl shadow-sm overflow-hidden hover:shadow-lg transition-shadow">
            <div class="p-6">
                <div class="flex items-start justify-between mb-4">
                    <div class="flex items-center space-x-4">
                        <div class="h-16 w-16 rounded-full bg-gradient-to-br from-brand-400 to-brand-600 flex items-center justify-center text-white text-2xl font-bold">
                            {{ strtoupper(substr($member->name, 0, 2)) }}
                        </div>
                        <div>
                            <h3 class="font-bold text-gray-900 text-lg">{{ $member->name }}</h3>
                            <p class="text-sm text-gray-500 capitalize">{{ $member->role }}</p>
                        </div>
                    </div>
                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium {{ $member->is_online ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                        {{ $member->is_online ? 'Online' : 'Offline' }}
                    </span>
                </div>

                <div class="space-y-2 mb-4">
                    <div class="flex items-center text-sm text-gray-600">
                        <i class="fa-solid fa-envelope w-5 mr-2"></i>
                        {{ $member->email }}
                    </div>
                    <div class="flex items-center text-sm text-gray-600">
                        <i class="fa-solid fa-phone w-5 mr-2"></i>
                        {{ $member->phone ?? 'N/A' }}
                    </div>
                </div>

                <!-- Today's Shift -->
                @php
                    $todayShift = $shifts->where('user_id', $member->id)
                        ->where('shift_date', date('Y-m-d'))
                        ->first();
                @endphp
                @if($todayShift)
                <div class="bg-brand-50 rounded-lg p-3 mb-4">
                    <p class="text-xs text-gray-600 mb-1">Today's Shift</p>
                    <p class="text-sm font-semibold text-brand-900 capitalize">{{ $todayShift->shift_type }}</p>
                    <p class="text-xs text-brand-700">{{ $todayShift->start_time }} - {{ $todayShift->end_time }}</p>
                </div>
                @endif

                <div class="flex gap-2">
                    <button onclick="openShiftModal({{ $member->id }}, '{{ $member->name }}')" 
                            class="flex-1 px-3 py-2 bg-brand-600 text-white text-sm rounded-lg hover:bg-brand-700 transition-colors">
                        <i class="fa-solid fa-calendar-plus mr-1"></i> Assign Shift
                    </button>
                    <button onclick="toggleStatus({{ $member->id }})" 
                            class="px-3 py-2 {{ $member->is_online ? 'bg-red-100 text-red-700 hover:bg-red-200' : 'bg-green-100 text-green-700 hover:bg-green-200' }} text-sm rounded-lg transition-colors">
                        <i class="fa-solid fa-power-off"></i>
                    </button>
                    <button class="px-3 py-2 bg-gray-100 text-gray-700 text-sm rounded-lg hover:bg-gray-200 transition-colors">
                        <i class="fa-solid fa-envelope"></i>
                    </button>
                </div>
            </div>
        </div>
        @endforeach

        <!-- Sample Staff with Filipino Names -->
        @if($staff->count() == 0)
        <!-- Dr. Maria Santos -->
        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <div class="p-6">
                <div class="flex items-start justify-between mb-4">
                    <div class="flex items-center space-x-4">
                        <div class="h-16 w-16 rounded-full bg-gradient-to-br from-purple-400 to-purple-600 flex items-center justify-center text-white text-2xl font-bold">
                            MS
                        </div>
                        <div>
                            <h3 class="font-bold text-gray-900 text-lg">Dr. Maria Santos</h3>
                            <p class="text-sm text-gray-500 capitalize">Doctor</p>
                        </div>
                    </div>
                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                        Online
                    </span>
                </div>
                <div class="space-y-2 mb-4">
                    <div class="flex items-center text-sm text-gray-600">
                        <i class="fa-solid fa-envelope w-5 mr-2"></i>
                        maria.santos@clinic.com
                    </div>
                    <div class="flex items-center text-sm text-gray-600">
                        <i class="fa-solid fa-phone w-5 mr-2"></i>
                        0917-123-4567
                    </div>
                </div>
                <div class="bg-brand-50 rounded-lg p-3 mb-4">
                    <p class="text-xs text-gray-600 mb-1">Today's Shift</p>
                    <p class="text-sm font-semibold text-brand-900 capitalize">Morning</p>
                    <p class="text-xs text-brand-700">08:00 AM - 04:00 PM</p>
                </div>
                <div class="flex gap-2">
                    <button onclick="openShiftModal(1, 'Dr. Maria Santos')" 
                            class="flex-1 px-3 py-2 bg-brand-600 text-white text-sm rounded-lg hover:bg-brand-700">
                        <i class="fa-solid fa-calendar-plus mr-1"></i> Assign Shift
                    </button>
                    <button class="px-3 py-2 bg-red-100 text-red-700 text-sm rounded-lg hover:bg-red-200">
                        <i class="fa-solid fa-power-off"></i>
                    </button>
                    <button class="px-3 py-2 bg-gray-100 text-gray-700 text-sm rounded-lg hover:bg-gray-200">
                        <i class="fa-solid fa-envelope"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- Nurse Juan Dela Cruz -->
        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <div class="p-6">
                <div class="flex items-start justify-between mb-4">
                    <div class="flex items-center space-x-4">
                        <div class="h-16 w-16 rounded-full bg-gradient-to-br from-pink-400 to-pink-600 flex items-center justify-center text-white text-2xl font-bold">
                            JD
                        </div>
                        <div>
                            <h3 class="font-bold text-gray-900 text-lg">Juan Dela Cruz</h3>
                            <p class="text-sm text-gray-500 capitalize">Nurse</p>
                        </div>
                    </div>
                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                        Online
                    </span>
                </div>
                <div class="space-y-2 mb-4">
                    <div class="flex items-center text-sm text-gray-600">
                        <i class="fa-solid fa-envelope w-5 mr-2"></i>
                        juan.delacruz@clinic.com
                    </div>
                    <div class="flex items-center text-sm text-gray-600">
                        <i class="fa-solid fa-phone w-5 mr-2"></i>
                        0918-765-4321
                    </div>
                </div>
                <div class="bg-brand-50 rounded-lg p-3 mb-4">
                    <p class="text-xs text-gray-600 mb-1">Today's Shift</p>
                    <p class="text-sm font-semibold text-brand-900 capitalize">Night</p>
                    <p class="text-xs text-brand-700">08:00 PM - 08:00 AM</p>
                </div>
                <div class="flex gap-2">
                    <button onclick="openShiftModal(2, 'Juan Dela Cruz')" 
                            class="flex-1 px-3 py-2 bg-brand-600 text-white text-sm rounded-lg hover:bg-brand-700">
                        <i class="fa-solid fa-calendar-plus mr-1"></i> Assign Shift
                    </button>
                    <button class="px-3 py-2 bg-red-100 text-red-700 text-sm rounded-lg hover:bg-red-200">
                        <i class="fa-solid fa-power-off"></i>
                    </button>
                    <button class="px-3 py-2 bg-gray-100 text-gray-700 text-sm rounded-lg hover:bg-gray-200">
                        <i class="fa-solid fa-envelope"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- Dr. Jose Rizal -->
        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <div class="p-6">
                <div class="flex items-start justify-between mb-4">
                    <div class="flex items-center space-x-4">
                        <div class="h-16 w-16 rounded-full bg-gradient-to-br from-blue-400 to-blue-600 flex items-center justify-center text-white text-2xl font-bold">
                            JR
                        </div>
                        <div>
                            <h3 class="font-bold text-gray-900 text-lg">Dr. Jose Rizal</h3>
                            <p class="text-sm text-gray-500 capitalize">Doctor</p>
                        </div>
                    </div>
                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                        Offline
                    </span>
                </div>
                <div class="space-y-2 mb-4">
                    <div class="flex items-center text-sm text-gray-600">
                        <i class="fa-solid fa-envelope w-5 mr-2"></i>
                        jose.rizal@clinic.com
                    </div>
                    <div class="flex items-center text-sm text-gray-600">
                        <i class="fa-solid fa-phone w-5 mr-2"></i>
                        0919-111-2222
                    </div>
                </div>
                <div class="flex gap-2">
                    <button onclick="openShiftModal(3, 'Dr. Jose Rizal')" 
                            class="flex-1 px-3 py-2 bg-brand-600 text-white text-sm rounded-lg hover:bg-brand-700">
                        <i class="fa-solid fa-calendar-plus mr-1"></i> Assign Shift
                    </button>
                    <button class="px-3 py-2 bg-green-100 text-green-700 text-sm rounded-lg hover:bg-green-200">
                        <i class="fa-solid fa-power-off"></i>
                    </button>
                    <button class="px-3 py-2 bg-gray-100 text-gray-700 text-sm rounded-lg hover:bg-gray-200">
                        <i class="fa-solid fa-envelope"></i>
                    </button>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>

<!-- Assign Shift Modal -->
<div id="shiftModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full">
        <div class="p-6 border-b border-gray-200 flex justify-between items-center">
            <h3 class="text-xl font-bold text-gray-900">Assign Shift</h3>
            <button onclick="closeShiftModal()" class="text-gray-400 hover:text-gray-600 text-2xl">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
        
        <form id="shiftForm" action="{{ route('staff.shifts.store') }}" method="POST" class="p-6 space-y-4">
            @csrf
            <input type="hidden" name="user_id" id="shiftUserId">
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Staff Member</label>
                <input type="text" id="shiftStaffName" readonly 
                       class="w-full px-3 py-2 bg-gray-100 border border-gray-300 rounded-lg text-gray-600">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Shift Type *</label>
                <select name="shift_type" required 
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500">
                    <option value="morning">Morning (8AM - 4PM)</option>
                    <option value="afternoon">Afternoon (12PM - 8PM)</option>
                    <option value="night">Night (8PM - 8AM)</option>
                    <option value="custom">Custom</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Date *</label>
                <input type="date" name="shift_date" required value="{{ date('Y-m-d') }}"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500">
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Start Time *</label>
                    <input type="time" name="start_time" id="startTime" required 
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">End Time *</label>
                    <input type="time" name="end_time" id="endTime" required 
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500">
                </div>
            </div>

            <div class="flex gap-3 pt-4">
                <button type="button" onclick="closeShiftModal()" 
                        class="flex-1 px-4 py-2 bg-gray-200 text-gray-700 rounded-lg font-semibold hover:bg-gray-300">
                    Cancel
                </button>
                <button type="submit" 
                        class="flex-1 px-4 py-2 bg-brand-600 text-white rounded-lg font-semibold hover:bg-brand-700">
                    Assign Shift
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openShiftModal(userId, userName) {
    document.getElementById('shiftUserId').value = userId;
    document.getElementById('shiftStaffName').value = userName;
    document.getElementById('shiftModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function closeShiftModal() {
    document.getElementById('shiftModal').classList.add('hidden');
    document.body.style.overflow = 'auto';
    document.getElementById('shiftForm').reset();
}

function toggleStatus(userId) {
    fetch(`/staff/${userId}/toggle-status`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(response => response.json())
    .then(data => {
        location.reload();
    });
}

function openAddStaffModal() {
    alert('Add staff functionality - Create registration form');
}

// Auto-fill times based on shift type
document.querySelector('select[name="shift_type"]').addEventListener('change', function(e) {
    const startInput = document.getElementById('startTime');
    const endInput = document.getElementById('endTime');
    
    switch(e.target.value) {
        case 'morning':
            startInput.value = '08:00';
            endInput.value = '16:00';
            break;
        case 'afternoon':
            startInput.value = '12:00';
            endInput.value = '20:00';
            break;
        case 'night':
            startInput.value = '20:00';
            endInput.value = '08:00';
            break;
    }
});

// Close modal on Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeShiftModal();
    }
});
</script>
@endsection