@extends('layouts.app')
@section('content')
<div x-data="{ showShiftModal: false, selectedStaff: null }">
    <h1 class="text-2xl font-bold text-gray-900 mb-6">Staff Directory</h1>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach($staff as $member)
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 hover:shadow-md transition">
            <div class="flex items-start justify-between">
                <div class="flex items-center space-x-4">
                    <div class="w-12 h-12 rounded-full {{ $member->isOnline()?'bg-green-100':'bg-gray-100' }} flex items-center justify-center text-2xl">{{ $member->isOnline()?'🟢':'⚪' }}</div>
                    <div><h3 class="font-semibold text-gray-900">{{ $member->name }}</h3><p class="text-sm text-gray-500 capitalize">{{ $member->role }}</p>@if($member->specialization)<p class="text-xs text-medical-600">{{ $member->specialization }}</p>@endif</div>
                </div>
                <span class="px-2 py-1 text-xs rounded-full {{ $member->isOnline()?'bg-green-100 text-green-800':'bg-gray-100 text-gray-600' }}">{{ $member->isOnline()?'Online':'Offline' }}</span>
            </div>
            @php $shift = $member->currentShift(); @endphp
            <div class="mt-4 pt-4 border-t border-gray-100">
                @if($shift)<div class="flex justify-between text-sm"><span class="text-gray-500">Current Shift:</span><span class="font-medium text-medical-700 capitalize">{{ $shift->shift_type }} ({{ $shift->start_time }} - {{ $shift->end_time }})</span></div>@else<p class="text-sm text-gray-400 italic">No active shift</p>@endif
                @if($member->shifts->count()>0)<div class="mt-2"><p class="text-xs text-gray-500 mb-1">Today's Schedule:</p>@foreach($member->shifts as $s)<span class="inline-block text-xs bg-medical-50 text-medical-700 px-2 py-1 rounded mr-1 mb-1">{{ ucfirst($s->shift_type) }}: {{ $s->start_time }}-{{ $s->end_time }}</span>@endforeach</div>@endif
            </div>
            <div class="mt-4 flex space-x-2">
                <a href="mailto:{{ $member->email }}" class="flex-1 text-center bg-medical-50 hover:bg-medical-100 text-medical-700 px-3 py-2 rounded-lg text-sm font-medium transition">✉️ Email</a>
                <button @click="selectedStaff={{ $member->id }}; showShiftModal=true" class="flex-1 bg-gray-50 hover:bg-gray-100 text-gray-700 px-3 py-2 rounded-lg text-sm font-medium transition">+ Shift</button>
            </div>
        </div>
        @endforeach
    </div>

    <div x-show="showShiftModal" x-cloak class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-xl shadow-xl w-full max-w-md mx-4" @click.away="showShiftModal = false">
            <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center"><h3 class="text-lg font-semibold">Assign Shift</h3><button @click="showShiftModal = false" class="text-gray-400 hover:text-gray-600">✕</button></div>
            <form method="POST" action="{{ route('staff.shifts.store') }}" class="p-6 space-y-4">
                @csrf
                <input type="hidden" name="user_id" :value="selectedStaff">
                <div><label class="block text-sm font-medium text-gray-700 mb-1">Shift Type</label><select name="shift_type" required class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-medical-500 outline-none"><option value="morning">Morning</option><option value="afternoon">Afternoon</option><option value="night">Night</option><option value="on_call">On Call</option></select></div>
                <div><label class="block text-sm font-medium text-gray-700 mb-1">Date</label><input type="date" name="shift_date" required value="{{ today()->format('Y-m-d') }}" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-medical-500 outline-none"></div>
                <div class="grid grid-cols-2 gap-4">
                    <div><label class="block text-sm font-medium text-gray-700 mb-1">Start</label><input type="time" name="start_time" required class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-medical-500 outline-none"></div>
                    <div><label class="block text-sm font-medium text-gray-700 mb-1">End</label><input type="time" name="end_time" required class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-medical-500 outline-none"></div>
                </div>
                <div class="flex justify-end space-x-3 pt-4">
                    <button type="button" @click="showShiftModal = false" class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-medical-600 text-white rounded-lg hover:bg-medical-700">Assign</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection