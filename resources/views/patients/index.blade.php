@extends('layouts.app')
@section('content')
<div x-data="{ showAddModal: false }">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Patients</h1>
        <button @click="showAddModal = true" class="bg-medical-600 hover:bg-medical-700 text-white px-4 py-2 rounded-lg transition">+ Add Patient</button>
    </div>

    <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-100 mb-6">
        <form method="GET" action="{{ route('patients.index') }}" class="grid grid-cols-1 md:grid-cols-5 gap-4">
            <div class="md:col-span-2">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by name, ID, nurse, or doctor..." class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-medical-500 outline-none">
            </div>
            <div>
                <select name="nurse_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-medical-500 outline-none">
                    <option value="">All Nurses</option>
                    @foreach($nurses as $nurse)<option value="{{ $nurse->id }}" {{ request('nurse_id')==$nurse->id?'selected':'' }}>{{ $nurse->name }}</option>@endforeach
                </select>
            </div>
            <div>
                <select name="doctor_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-medical-500 outline-none">
                    <option value="">All Doctors</option>
                    @foreach($doctors as $doctor)<option value="{{ $doctor->id }}" {{ request('doctor_id')==$doctor->id?'selected':'' }}>{{ $doctor->name }}</option>@endforeach
                </select>
            </div>
            <div class="flex space-x-2">
                <button type="submit" class="flex-1 bg-gray-800 text-white px-4 py-2 rounded-lg">Filter</button>
                <a href="{{ route('patients.index') }}" class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">Clear</a>
            </div>
        </form>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase"><a href="{{ route('patients.index',array_merge(request()->all(),['sort'=>'name','direction'=>request('direction')=='asc'?'desc':'asc'])) }}" class="flex items-center space-x-1">Name @if(request('sort')=='name')<span>{{ request('direction')=='asc'?'↑':'↓' }}</span>@endif</a></th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ID</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Assigned To</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Last Visit</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @foreach($patients as $patient)
                <tr class="hover:bg-gray-50 transition">
                    <td class="px-6 py-4">
                        <div class="flex items-center">
                            <form method="POST" action="{{ route('patients.pin',$patient) }}" class="mr-3">@csrf<button type="submit" class="text-lg hover:scale-110 transition">{{ Auth::user()->pinnedPatients->contains($patient->id)?'📌':'📍' }}</button></form>
                            <div><p class="font-medium text-gray-900">{{ $patient->name }}</p><p class="text-sm text-gray-500">{{ $patient->phone }}</p></div>
                        </div>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-600 font-mono">{{ $patient->patient_id }}</td>
                    <td class="px-6 py-4 text-sm"><p><span class="text-gray-500">Nurse:</span> {{ $patient->nurse?->name ?? '—' }}</p><p><span class="text-gray-500">Doctor:</span> {{ $patient->doctor?->name ?? '—' }}</p></td>
                    <td class="px-6 py-4 text-sm text-gray-600">{{ $patient->last_visit?->diffForHumans() ?? 'Never' }}</td>
                    <td class="px-6 py-4"><a href="mailto:{{ $patient->doctor?->email }}" class="text-medical-600 hover:text-medical-800 text-sm font-medium">✉️ Email Doctor</a></td>
                </tr>
                @endforeach
            </tbody>
        </table>
        <div class="px-6 py-4 border-t border-gray-100">{{ $patients->links() }}</div>
    </div>

    <div x-show="showAddModal" x-cloak class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-xl shadow-xl w-full max-w-lg mx-4" @click.away="showAddModal = false">
            <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center"><h3 class="text-lg font-semibold">Add New Patient</h3><button @click="showAddModal = false" class="text-gray-400 hover:text-gray-600">✕</button></div>
            <form method="POST" action="{{ route('patients.store') }}" class="p-6 space-y-4">
                @csrf
                <div class="grid grid-cols-2 gap-4">
                    <div><label class="block text-sm font-medium text-gray-700 mb-1">Name *</label><input type="text" name="name" required class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-medical-500 outline-none"></div>
                    <div><label class="block text-sm font-medium text-gray-700 mb-1">Patient ID *</label><input type="text" name="patient_id" required class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-medical-500 outline-none"></div>
                </div>
                <div><label class="block text-sm font-medium text-gray-700 mb-1">Date of Birth</label><input type="date" name="date_of_birth" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-medical-500 outline-none"></div>
                <div class="grid grid-cols-2 gap-4">
                    <div><label class="block text-sm font-medium text-gray-700 mb-1">Nurse</label><select name="assigned_nurse_id" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-medical-500 outline-none"><option value="">Select</option>@foreach($nurses as $nurse)<option value="{{ $nurse->id }}">{{ $nurse->name }}</option>@endforeach</select></div>
                    <div><label class="block text-sm font-medium text-gray-700 mb-1">Doctor</label><select name="assigned_doctor_id" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-medical-500 outline-none"><option value="">Select</option>@foreach($doctors as $doctor)<option value="{{ $doctor->id }}">{{ $doctor->name }}</option>@endforeach</select></div>
                </div>
                <div class="flex justify-end space-x-3 pt-4">
                    <button type="button" @click="showAddModal = false" class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-medical-600 text-white rounded-lg hover:bg-medical-700">Add Patient</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection