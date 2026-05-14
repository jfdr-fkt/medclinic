@extends('layouts.app')
@section('title', $patient->name)
@section('page-title', 'Patient Details')

@section('content')
<div class="space-y-5 max-w-3xl">

    <!-- Back + Header -->
    <div class="flex items-center gap-3">
        <a href="{{ route('patients.index') }}"
           class="w-9 h-9 flex items-center justify-center rounded-xl text-gray-400 hover:text-gray-700 hover:bg-gray-100 transition-colors">
            <i class="fa-solid fa-arrow-left"></i>
        </a>
        <div class="flex items-center gap-3 flex-1">
            <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-brand-400 to-brand-700 flex items-center justify-center text-white font-bold text-lg flex-shrink-0">
                {{ strtoupper(substr($patient->name, 0, 2)) }}
            </div>
            <div>
                <h1 class="text-2xl font-bold text-gray-900">{{ $patient->name }}</h1>
                <p class="text-sm text-gray-400 font-mono">{{ $patient->patient_id }}</p>
            </div>
        </div>
        <a href="{{ route('patients.index') }}" class="btn-secondary text-xs py-1.5">
            <i class="fa-solid fa-arrow-left"></i> Back to List
        </a>
    </div>

    <!-- Details Card -->
    <div class="card p-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-5">

            <!-- Left column -->
            <div class="space-y-5">
                <div>
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Date of Birth</p>
                    <p class="text-gray-900 font-medium">
                        @if($patient->date_of_birth)
                            {{ $patient->date_of_birth->format('F j, Y') }}
                            <span class="text-gray-400 font-normal">({{ $patient->date_of_birth->age }} yrs old)</span>
                        @else
                            <span class="text-gray-300">—</span>
                        @endif
                    </p>
                </div>
                <div>
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Contact Number</p>
                    <p class="text-gray-900 font-medium">{{ $patient->phone ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Address</p>
                    <p class="text-gray-900">{{ $patient->address ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Last Visit</p>
                    <p class="text-gray-900 font-medium">
                        @if($patient->last_visit)
                            {{ $patient->last_visit->format('F j, Y') }}
                            <span class="text-xs text-gray-400">({{ $patient->last_visit->diffForHumans() }})</span>
                        @else
                            <span class="text-gray-300">—</span>
                        @endif
                    </p>
                </div>
            </div>

            <!-- Right column -->
            <div class="space-y-5">
                <div>
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Assigned Doctor</p>
                    <p class="text-gray-900 font-medium flex items-center gap-2">
                        <i class="fa-solid fa-user-doctor text-purple-400 text-xs"></i>
                        {{ $patient->doctor?->name ?? 'Unassigned' }}
                    </p>
                </div>
                <div>
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Assigned Nurse</p>
                    <p class="text-gray-900 font-medium flex items-center gap-2">
                        <i class="fa-solid fa-user-nurse text-pink-400 text-xs"></i>
                        {{ $patient->nurse?->name ?? 'Unassigned' }}
                    </p>
                </div>
                <div>
                    <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-1 flex items-center gap-1.5">
                        Medical History / Notes
                        @if(!Auth::user()->can_('patients.view_medical'))
                            <i class="fa-solid fa-lock text-amber-500 text-[10px]" title="Restricted to clinical staff"></i>
                        @endif
                    </p>
                    @if(Auth::user()->can_('patients.view_medical'))
                        <p class="text-gray-800 dark:text-gray-100 text-sm leading-relaxed bg-gray-50 dark:bg-slate-800 rounded-xl p-3 border border-gray-100 dark:border-slate-700">
                            {{ $patient->medical_history ?? 'No medical history recorded.' }}
                        </p>
                    @else
                        <div class="rounded-xl bg-amber-50 dark:bg-amber-900/25 border-2 border-dashed border-amber-300 dark:border-amber-700 p-4 flex items-start gap-3">
                            <i class="fa-solid fa-shield-halved text-amber-600 dark:text-amber-400 text-base mt-0.5"></i>
                            <div>
                                <p class="text-sm font-bold text-amber-800 dark:text-amber-200">Restricted</p>
                                <p class="text-xs text-amber-700 dark:text-amber-300 mt-0.5">
                                    Medical history is available to clinical staff only (Admin, Clinic Head, Doctor, Nurse). Your role can see patient contact and assignment details but not medical records.
                                </p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
