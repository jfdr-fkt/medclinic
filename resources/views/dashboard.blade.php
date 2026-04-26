@extends('layouts.app')
@section('content')
<div class="space-y-6">
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
            <div class="flex items-center justify-between">
                <div><p class="text-sm text-gray-500">Patients Today</p><p class="text-3xl font-bold text-medical-700">{{ $todayPatients }}</p></div>
                <div class="bg-medical-100 p-3 rounded-lg text-2xl">👥</div>
            </div>
        </div>
        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
            <div class="flex items-center justify-between">
                <div><p class="text-sm text-gray-500">Low Stock</p><p class="text-3xl font-bold text-red-600">{{ $lowStockMedicines->count() }}</p></div>
                <div class="bg-red-100 p-3 rounded-lg text-2xl">⚠️</div>
            </div>
        </div>
        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
            <div class="flex items-center justify-between">
                <div><p class="text-sm text-gray-500">Expiring Soon</p><p class="text-3xl font-bold text-orange-600">{{ $expiringSoon->count() }}</p></div>
                <div class="bg-orange-100 p-3 rounded-lg text-2xl">📅</div>
            </div>
        </div>
        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
            <div class="flex items-center justify-between">
                <div><p class="text-sm text-gray-500">Staff Online</p><p class="text-3xl font-bold text-green-600">{{ $onlineStaff->count() }}</p></div>
                <div class="bg-green-100 p-3 rounded-lg text-2xl">🟢</div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 bg-red-50"><h3 class="font-semibold text-red-800">⚠️ Low Stock Medicines</h3></div>
            <div class="divide-y divide-gray-100">
                @forelse($lowStockMedicines as $med)
                <div class="px-6 py-4 flex justify-between items-center hover:bg-gray-50">
                    <div><p class="font-medium text-gray-900">{{ $med->name }}</p><p class="text-sm text-gray-500">{{ $med->location->full_location }}</p></div>
                    <span class="bg-red-100 text-red-800 text-xs px-2 py-1 rounded-full font-medium">{{ $med->latestInventory?->quantity ?? 0 }} left</span>
                </div>
                @empty
                <div class="px-6 py-4 text-gray-500 text-sm">No low stock items.</div>
                @endforelse
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 bg-green-50"><h3 class="font-semibold text-green-800">🟢 Online Staff</h3></div>
            <div class="divide-y divide-gray-100">
                @forelse($onlineStaff as $staff)
                <div class="px-6 py-4 flex justify-between items-center hover:bg-gray-50">
                    <div class="flex items-center space-x-3">
                        <div class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></div>
                        <div><p class="font-medium text-gray-900">{{ $staff->name }}</p><p class="text-sm text-gray-500 capitalize">{{ $staff->role }}</p></div>
                    </div>
                    <a href="mailto:{{ $staff->email }}" class="text-medical-600 hover:text-medical-800 text-sm font-medium">✉️ Email</a>
                </div>
                @empty
                <div class="px-6 py-4 text-gray-500 text-sm">No staff online.</div>
                @endforelse
            </div>
        </div>
    </div>

    @if($myPinnedPatients->count() > 0)
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 bg-medical-50"><h3 class="font-semibold text-medical-800">📌 Your Pinned Patients</h3></div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 p-6">
            @foreach($myPinnedPatients as $patient)
            <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition">
                <div class="flex justify-between items-start">
                    <h4 class="font-semibold text-gray-900">{{ $patient->name }}</h4>
                    <span class="text-xs bg-medical-100 text-medical-800 px-2 py-1 rounded">{{ $patient->patient_id }}</span>
                </div>
                <p class="text-sm text-gray-500 mt-1">Last visit: {{ $patient->last_visit?->diffForHumans() ?? 'Never' }}</p>
            </div>
            @endforeach
        </div>
    </div>
    @endif
</div>
@endsection