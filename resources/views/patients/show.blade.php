@extends('layouts.app')
@section('title', $patient->name)
@section('page-title', 'Patient Record')

@section('content')
@php
    $me        = Auth::user();
    $canMed    = $me->can_('patients.view_medical');
    $age       = $patient->date_of_birth?->age;
    $bmi       = $patient->bmi();
    $bmiCat    = $patient->bmiCategory();
    $sexLabel  = $patient->sex ? ucfirst($patient->sex) : null;
    $isNew     = $patient->visits->count() <= 1;
    $totalVisits = $patient->visits->count();

    $bmiHue = match(true) {
        $bmi === null    => 'gray',
        $bmi < 18.5      => 'amber',
        $bmi < 25        => 'emerald',
        $bmi < 30        => 'orange',
        default          => 'red',
    };

    $visitStatusOpts = [
        'waiting'     => ['Waiting',     'amber',   'fa-hourglass-half'],
        'with_nurse'  => ['With Nurse',  'cyan',    'fa-user-nurse'],
        'with_doctor' => ['With Doctor', 'blue',    'fa-user-doctor'],
        'pharmacy'    => ['Pharmacy',    'purple',  'fa-prescription-bottle-medical'],
        'completed'   => ['Completed',   'emerald', 'fa-circle-check'],
        'cancelled'   => ['Cancelled',   'gray',    'fa-circle-xmark'],
    ];
@endphp

<div class="space-y-5 max-w-5xl mx-auto">

    {{-- Back link --}}
    <div class="flex items-center gap-3">
        <a href="{{ route('patients.index') }}"
           class="w-9 h-9 flex items-center justify-center rounded-xl text-gray-400 dark:text-gray-500 hover:text-gray-700 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-slate-800 transition-colors">
            <i class="fa-solid fa-arrow-left"></i>
        </a>
        <span class="text-sm text-gray-400 dark:text-gray-500">Back to Patients</span>
    </div>

    {{-- Header card — flat in-gradient layout (no overlap, predictable on all viewports) --}}
    <div class="rounded-2xl overflow-hidden bg-gradient-to-r from-blue-600 via-indigo-600 to-blue-700 text-white shadow-md relative">
        <div class="absolute inset-0 opacity-20 pointer-events-none" style="background-image: radial-gradient(circle at 18% 30%, rgba(255,255,255,.55) 0, transparent 35%), radial-gradient(circle at 80% 75%, rgba(255,255,255,.35) 0, transparent 32%), radial-gradient(circle at 95% 20%, rgba(255,255,255,.25) 0, transparent 25%);"></div>
        <div class="relative px-5 sm:px-6 py-5 flex items-center gap-4 flex-wrap">
            <div class="w-16 h-16 sm:w-20 sm:h-20 rounded-2xl bg-white/15 backdrop-blur-sm ring-1 ring-white/25 flex items-center justify-center text-white text-xl sm:text-2xl font-extrabold flex-shrink-0">
                {{ strtoupper(substr($patient->name, 0, 2)) }}
            </div>
            <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2 flex-wrap">
                    <h2 class="text-xl sm:text-2xl font-extrabold leading-tight truncate">{{ $patient->name }}</h2>
                    @if($isNew)
                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-emerald-400/25 text-emerald-50 ring-1 ring-emerald-300/50 uppercase tracking-wider">New</span>
                    @else
                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-white/20 text-white ring-1 ring-white/30 uppercase tracking-wider">Returning</span>
                    @endif
                </div>
                <div class="flex items-center gap-3 flex-wrap text-sm text-white/85 mt-1.5">
                    <span class="patient-id-text text-white">{{ $patient->patient_id }}</span>
                    @if($age !== null)
                        <span class="text-white/40">|</span>
                        <span>{{ $age }} yrs</span>
                    @endif
                    @if($sexLabel)
                        <span class="text-white/40">|</span>
                        <span>{{ $sexLabel }}</span>
                    @endif
                    @if($patient->blood_type && $patient->blood_type !== 'unknown')
                        <span class="text-white/40">|</span>
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-white text-red-700 text-xs font-extrabold shadow-sm">
                            <i class="fa-solid fa-droplet text-[10px]"></i> {{ $patient->blood_type }}
                        </span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Edit toggle (anyone with patients.create can update demographics + clinical) --}}
    @if(auth()->user()->can_('patients.create'))
    <div class="flex justify-end gap-2 flex-wrap">
        <button type="button" onclick="document.getElementById('patientPhotoInput').click()" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-purple-100 dark:bg-purple-900/40 text-purple-700 dark:text-purple-300 hover:bg-purple-200 dark:hover:bg-purple-900/60 text-sm font-bold transition-colors">
            <i class="fa-solid fa-camera"></i> Add Photos
        </button>
        <form method="POST" action="{{ route('patients.images.upload', $patient) }}" enctype="multipart/form-data" id="patientPhotoForm" class="hidden">
            @csrf
            <input type="file" id="patientPhotoInput" name="images[]" accept="image/jpeg,image/png,image/webp" multiple
                   onchange="document.getElementById('patientPhotoForm').submit()">
        </form>
        <button type="button" onclick="openPatientEditModal()" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-blue-100 dark:bg-blue-900/40 text-blue-700 dark:text-blue-300 hover:bg-blue-200 dark:hover:bg-blue-900/60 text-sm font-bold transition-colors">
            <i class="fa-solid fa-pen-to-square"></i> Edit Patient Record
        </button>
    </div>
    @endif

    {{-- ── Patient photos ── --}}
    @if($patient->images->count() > 0 || auth()->user()->can_('patients.create'))
    <div class="card p-5">
        <h3 class="font-bold text-gray-900 dark:text-white text-base mb-3 flex items-center gap-2">
            <span class="w-7 h-7 rounded-lg bg-purple-100 dark:bg-purple-900/40 text-purple-600 dark:text-purple-300 inline-flex items-center justify-center">
                <i class="fa-solid fa-images text-xs"></i>
            </span>
            Photos
            <span class="text-sm text-gray-500 dark:text-gray-400 font-medium">({{ $patient->images->count() }})</span>
        </h3>
        @if($patient->images->count() > 0)
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3">
            @foreach($patient->images as $img)
            <div class="relative group rounded-xl overflow-hidden border-2 border-gray-100 dark:border-slate-700 bg-gray-50 dark:bg-slate-800/60">
                <div class="aspect-square">
                    <img src="{{ $img->url() }}" alt="{{ $img->caption ?? 'Patient photo' }}"
                         class="w-full h-full object-cover cursor-zoom-in"
                         onclick="openPatientLightbox('{{ $img->url() }}')">
                </div>
                <div class="px-2 py-1.5 text-[10px] text-gray-500 dark:text-gray-400 bg-white dark:bg-slate-900 border-t border-gray-100 dark:border-slate-700">
                    <p class="font-bold text-gray-800 dark:text-gray-200 truncate">{{ $img->uploadedBy?->name ?? 'System' }}</p>
                    <p class="truncate">{{ $img->created_at->diffForHumans() }}</p>
                </div>
                @if(auth()->user()->id === $img->uploaded_by || in_array(auth()->user()->role, ['admin','clinic_head']))
                <form method="POST" action="{{ route('patients.images.delete', [$patient, $img]) }}"
                      class="absolute top-1.5 right-1.5"
                      onsubmit="return confirm('Remove this photo?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="w-7 h-7 rounded-lg bg-red-600/90 hover:bg-red-700 text-white flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity shadow-md" title="Remove photo">
                        <i class="fa-solid fa-trash text-[10px]"></i>
                    </button>
                </form>
                @endif
            </div>
            @endforeach
        </div>
        @else
        <div class="text-center py-8 border-2 border-dashed border-gray-200 dark:border-slate-700 rounded-xl">
            <i class="fa-solid fa-images text-gray-300 dark:text-gray-600 text-2xl mb-2"></i>
            <p class="text-sm text-gray-500 dark:text-gray-400 font-medium">No photos uploaded yet</p>
            <p class="text-xs text-gray-400 dark:text-gray-500 mt-0.5">Use the "Add Photos" button above to upload clinical photos (rash, x-ray, etc.).</p>
        </div>
        @endif
    </div>
    @endif

    {{-- Vitals strip --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
        <div class="card p-4">
            <p class="text-[11px] font-bold uppercase tracking-wider text-gray-400 dark:text-gray-500 mb-1">Height</p>
            <p class="text-xl font-extrabold text-gray-900 dark:text-white">{{ $patient->height_cm ? $patient->height_cm . ' cm' : '—' }}</p>
        </div>
        <div class="card p-4">
            <p class="text-[11px] font-bold uppercase tracking-wider text-gray-400 dark:text-gray-500 mb-1">Weight</p>
            <p class="text-xl font-extrabold text-gray-900 dark:text-white">{{ $patient->weight_kg ? rtrim(rtrim((string)$patient->weight_kg, '0'), '.') . ' kg' : '—' }}</p>
        </div>
        <div class="card p-4">
            <p class="text-[11px] font-bold uppercase tracking-wider text-gray-400 dark:text-gray-500 mb-1">BMI</p>
            @if($bmi !== null)
                <p class="text-xl font-extrabold text-gray-900 dark:text-white">{{ $bmi }}</p>
                <p class="text-[11px] font-bold text-{{ $bmiHue }}-600 dark:text-{{ $bmiHue }}-300 mt-0.5">{{ $bmiCat }}</p>
            @else
                <p class="text-xl font-extrabold text-gray-300 dark:text-gray-600">—</p>
            @endif
        </div>
        <div class="card p-4">
            <p class="text-[11px] font-bold uppercase tracking-wider text-gray-400 dark:text-gray-500 mb-1">Visits Total</p>
            <p class="text-xl font-extrabold text-gray-900 dark:text-white">{{ $totalVisits }}</p>
            @if($patient->last_visit)
            <p class="text-[11px] text-gray-400 dark:text-gray-500 mt-0.5">Last: {{ $patient->last_visit->diffForHumans() }}</p>
            @endif
        </div>
    </div>

    {{-- Two-column body --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

        {{-- Left: Demographics + Care Team + Emergency --}}
        <div class="space-y-5 lg:col-span-1">
            <div class="card p-5">
                <h3 class="font-bold text-gray-800 dark:text-white text-sm mb-3 flex items-center gap-2">
                    <i class="fa-solid fa-address-card text-brand-500"></i> Demographics
                </h3>
                <dl class="space-y-3 text-sm">
                    <div>
                        <dt class="text-[11px] font-bold uppercase tracking-wider text-gray-400 dark:text-gray-500">Date of Birth</dt>
                        <dd class="text-gray-800 dark:text-gray-100 mt-0.5">
                            @if($patient->date_of_birth)
                                {{ $patient->date_of_birth->format('F j, Y') }}
                                <span class="text-gray-400 dark:text-gray-500 text-xs">({{ $age }} yrs)</span>
                            @else
                                <span class="text-gray-300 dark:text-gray-600">—</span>
                            @endif
                        </dd>
                    </div>
                    <div>
                        <dt class="text-[11px] font-bold uppercase tracking-wider text-gray-400 dark:text-gray-500">Phone</dt>
                        <dd class="text-gray-800 dark:text-gray-100 mt-0.5">{{ $patient->phone ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-[11px] font-bold uppercase tracking-wider text-gray-400 dark:text-gray-500">Address</dt>
                        <dd class="text-gray-800 dark:text-gray-100 mt-0.5">{{ $patient->address ?? '—' }}</dd>
                    </div>
                </dl>
            </div>

            <div class="card p-5">
                <h3 class="font-bold text-gray-800 dark:text-white text-sm mb-3 flex items-center gap-2">
                    <i class="fa-solid fa-people-group text-brand-500"></i> Care Team
                </h3>
                <div class="space-y-2.5 text-sm">
                    <div class="flex items-center gap-3 p-2.5 rounded-xl bg-purple-50 dark:bg-purple-900/20 border border-purple-100 dark:border-purple-800/40">
                        <div class="w-9 h-9 rounded-full bg-purple-200 dark:bg-purple-900/60 text-purple-700 dark:text-purple-300 flex items-center justify-center flex-shrink-0">
                            <i class="fa-solid fa-user-doctor"></i>
                        </div>
                        <div class="min-w-0">
                            <p class="text-[11px] font-bold uppercase tracking-wider text-purple-600 dark:text-purple-400">Doctor</p>
                            <p class="font-semibold text-gray-900 dark:text-white truncate">{{ $patient->doctor?->name ?? 'Unassigned' }}</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-3 p-2.5 rounded-xl bg-pink-50 dark:bg-pink-900/20 border border-pink-100 dark:border-pink-800/40">
                        <div class="w-9 h-9 rounded-full bg-pink-200 dark:bg-pink-900/60 text-pink-700 dark:text-pink-300 flex items-center justify-center flex-shrink-0">
                            <i class="fa-solid fa-user-nurse"></i>
                        </div>
                        <div class="min-w-0">
                            <p class="text-[11px] font-bold uppercase tracking-wider text-pink-600 dark:text-pink-400">Nurse</p>
                            <p class="font-semibold text-gray-900 dark:text-white truncate">{{ $patient->nurse?->name ?? 'Unassigned' }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card p-5">
                <h3 class="font-bold text-gray-900 dark:text-white text-base mb-3 flex items-center gap-2">
                    <span class="w-7 h-7 rounded-lg bg-rose-100 dark:bg-rose-900/40 text-rose-600 dark:text-rose-300 inline-flex items-center justify-center">
                        <i class="fa-solid fa-phone-flip text-xs"></i>
                    </span>
                    Emergency Contacts
                </h3>
                @if($patient->emergency_contact_name || $patient->emergency_contact_phone || $patient->emergency_contact_2_name || $patient->emergency_contact_2_phone)
                <div class="space-y-3 text-sm">
                    @if($patient->emergency_contact_name || $patient->emergency_contact_phone)
                    <div>
                        <p class="text-[11px] font-bold uppercase tracking-wider text-rose-600 dark:text-rose-400 mb-0.5">Primary</p>
                        <p class="font-semibold text-gray-900 dark:text-white">{{ $patient->emergency_contact_name ?? '—' }}</p>
                        @if($patient->emergency_contact_phone)
                        <p class="text-gray-600 dark:text-gray-300 mt-0.5 flex items-center gap-1.5">
                            <i class="fa-solid fa-phone text-xs text-gray-400"></i>
                            {{ $patient->emergency_contact_phone }}
                        </p>
                        @endif
                    </div>
                    @endif
                    @if($patient->emergency_contact_2_name || $patient->emergency_contact_2_phone)
                    <div class="pt-3 border-t border-gray-100 dark:border-slate-700">
                        <p class="text-[11px] font-bold uppercase tracking-wider text-rose-600 dark:text-rose-400 mb-0.5">Secondary</p>
                        <p class="font-semibold text-gray-900 dark:text-white">{{ $patient->emergency_contact_2_name ?? '—' }}</p>
                        @if($patient->emergency_contact_2_phone)
                        <p class="text-gray-600 dark:text-gray-300 mt-0.5 flex items-center gap-1.5">
                            <i class="fa-solid fa-phone text-xs text-gray-400"></i>
                            {{ $patient->emergency_contact_2_phone }}
                        </p>
                        @endif
                    </div>
                    @endif
                </div>
                @else
                <p class="text-sm text-gray-400 dark:text-gray-500 italic">Not on file</p>
                @endif
            </div>
        </div>

        {{-- Right: Medical sections --}}
        <div class="space-y-5 lg:col-span-2">
            @if($canMed)
                <div class="card p-5">
                    <h3 class="font-bold text-gray-900 dark:text-white text-base mb-3 flex items-center gap-2">
                        <span class="w-7 h-7 rounded-lg bg-red-100 dark:bg-red-900/40 text-red-600 dark:text-red-300 inline-flex items-center justify-center">
                            <i class="fa-solid fa-triangle-exclamation text-xs"></i>
                        </span>
                        Allergies
                    </h3>
                    @if($patient->allergies)
                    <div class="rounded-xl bg-red-50 dark:bg-red-900/20 border-2 border-red-200 dark:border-red-800/50 p-4 text-base text-red-800 dark:text-red-200 leading-relaxed font-medium">
                        {{ $patient->allergies }}
                    </div>
                    @else
                    <p class="text-sm text-gray-400 dark:text-gray-500 italic">No known allergies</p>
                    @endif
                </div>

                <div class="card p-5">
                    <h3 class="font-bold text-gray-900 dark:text-white text-base mb-3 flex items-center gap-2">
                        <span class="w-7 h-7 rounded-lg bg-orange-100 dark:bg-orange-900/40 text-orange-600 dark:text-orange-300 inline-flex items-center justify-center">
                            <i class="fa-solid fa-heart-pulse text-xs"></i>
                        </span>
                        Chronic Conditions
                    </h3>
                    @if($patient->chronic_conditions)
                    <div class="rounded-xl bg-orange-50 dark:bg-orange-900/20 border-2 border-orange-200 dark:border-orange-800/50 p-4 text-base text-orange-800 dark:text-orange-200 leading-relaxed font-medium">
                        {{ $patient->chronic_conditions }}
                    </div>
                    @else
                    <p class="text-sm text-gray-400 dark:text-gray-500 italic">None recorded</p>
                    @endif
                </div>

                {{-- Medical history is the largest section because clinicians read and add
                     to it the most. Bigger padding, bigger text, min-height so it never
                     looks empty even on new patients. --}}
                <div class="card p-5">
                    <h3 class="font-bold text-gray-900 dark:text-white text-base mb-3 flex items-center gap-2">
                        <span class="w-7 h-7 rounded-lg bg-brand-100 dark:bg-brand-900/40 text-brand-600 dark:text-brand-300 inline-flex items-center justify-center">
                            <i class="fa-solid fa-notes-medical text-xs"></i>
                        </span>
                        Medical History &amp; Notes
                    </h3>
                    <div class="rounded-xl bg-gray-50 dark:bg-slate-800/60 border-2 border-gray-100 dark:border-slate-700 p-5 text-base text-gray-800 dark:text-gray-100 leading-relaxed whitespace-pre-line min-h-[10rem]">
                        @if($patient->medical_history)
                            {{ $patient->medical_history }}
                        @else
                            <span class="text-gray-400 dark:text-gray-500 italic text-sm">No medical history recorded yet. Past surgeries, immunizations, family history, social history and ongoing medications belong here.</span>
                        @endif
                    </div>
                </div>
            @else
                <div class="card p-5">
                    <div class="rounded-xl bg-amber-50 dark:bg-amber-900/25 border-2 border-dashed border-amber-300 dark:border-amber-700 p-4 flex items-start gap-3">
                        <i class="fa-solid fa-shield-halved text-amber-600 dark:text-amber-400 text-base mt-0.5"></i>
                        <div>
                            <p class="text-sm font-bold text-amber-800 dark:text-amber-200">Medical Records Restricted</p>
                            <p class="text-xs text-amber-700 dark:text-amber-300 mt-0.5">
                                Allergies, conditions, and medical history are visible to clinical staff only (Admin, Clinic Head, Doctor, Nurse). Your role can see demographics, care team, and visit history.
                            </p>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Visit history --}}
            <div class="card p-5">
                <div class="flex items-center justify-between mb-3 flex-wrap gap-2">
                    <h3 class="font-bold text-gray-800 dark:text-white text-sm flex items-center gap-2">
                        <i class="fa-solid fa-clock-rotate-left text-brand-500"></i> Visit History
                        <span class="text-xs text-gray-400 dark:text-gray-500 font-normal">({{ $totalVisits }} total{{ $totalVisits > 15 ? ', showing latest 15' : '' }})</span>
                    </h3>
                    @if($me->can_('visits.view'))
                    <a href="{{ route('visits.index') }}" class="text-xs text-brand-600 dark:text-brand-300 hover:underline font-semibold">Today's queue →</a>
                    @endif
                </div>
                @if($visits->count() > 0)
                <ol class="relative border-l-2 border-gray-200 dark:border-slate-700 ml-2 space-y-3">
                    @foreach($visits as $v)
                    @php [$sLabel, $sHue, $sIcon] = $visitStatusOpts[$v->status] ?? ['Unknown', 'slate', 'fa-circle-question']; @endphp
                    <li class="ml-4">
                        <span class="absolute -left-[7px] w-3 h-3 rounded-full bg-{{ $sHue }}-400 ring-4 ring-white dark:ring-slate-900"></span>
                        <div class="flex items-start justify-between gap-3 flex-wrap">
                            <div class="min-w-0">
                                <p class="text-sm font-bold text-gray-900 dark:text-white">
                                    {{ $v->checked_in_at->format('M j, Y') }}
                                    <span class="text-xs text-gray-400 dark:text-gray-500 font-normal">{{ $v->checked_in_at->format('g:i A') }}</span>
                                </p>
                                @if($v->reason)
                                <p class="text-sm text-gray-700 dark:text-gray-200 mt-0.5">{{ $v->reason }}</p>
                                @endif
                                @if($v->currentStaff)
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Seen by {{ $v->currentStaff->name }}</p>
                                @endif
                            </div>
                            <span class="inline-flex items-center gap-1.5 px-2 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider flex-shrink-0
                                         bg-{{ $sHue }}-100 dark:bg-{{ $sHue }}-900/35 text-{{ $sHue }}-700 dark:text-{{ $sHue }}-300 border border-{{ $sHue }}-200 dark:border-{{ $sHue }}-800/60">
                                <i class="fa-solid {{ $sIcon }}"></i> {{ $sLabel }}
                            </span>
                        </div>
                    </li>
                    @endforeach
                </ol>
                @else
                <p class="text-sm text-gray-400 dark:text-gray-500 italic">No visits recorded yet.</p>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- ── Edit Patient Modal ── --}}
@if(auth()->user()->can_('patients.create'))
<div id="patientEditModal" class="hidden fixed inset-0 bg-black/60 z-50 flex items-center justify-center p-4 backdrop-blur-sm">
    <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-2xl w-full max-w-2xl max-h-[92vh] flex flex-col overflow-hidden border-2 border-gray-100 dark:border-slate-700">
        <div class="bg-gradient-to-r from-blue-600 to-indigo-700 px-6 py-5 text-white flex items-center justify-between flex-shrink-0">
            <div class="flex items-center gap-3">
                <div class="w-11 h-11 rounded-2xl bg-white/15 flex items-center justify-center backdrop-blur-sm">
                    <i class="fa-solid fa-pen-to-square text-xl"></i>
                </div>
                <div>
                    <h3 class="text-lg font-bold">Edit Patient Record</h3>
                    <p class="text-xs text-white/80 mt-0.5">{{ $patient->name }} — {{ $patient->patient_id }}</p>
                </div>
            </div>
            <button type="button" onclick="closePatientEditModal()" class="w-9 h-9 rounded-xl flex items-center justify-center text-white/80 hover:text-white hover:bg-white/20 transition-colors">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <form method="POST" action="{{ route('patients.update', $patient) }}" class="px-6 py-5 space-y-5 overflow-y-auto flex-1">
            @csrf @method('PUT')
            {{-- Identity passthrough — required by validator but unchanged from this modal. --}}
            <input type="hidden" name="name"          value="{{ $patient->name }}">
            <input type="hidden" name="date_of_birth" value="{{ $patient->date_of_birth?->format('Y-m-d') }}">
            <input type="hidden" name="sex"           value="{{ $patient->sex }}">
            <input type="hidden" name="blood_type"    value="{{ $patient->blood_type }}">
            <input type="hidden" name="height_cm"     value="{{ $patient->height_cm }}">
            <input type="hidden" name="weight_kg"     value="{{ $patient->weight_kg }}">

            {{-- Contact --}}
            <div class="border-l-4 border-purple-400 bg-purple-50/30 dark:bg-purple-900/15 rounded-r-xl p-4 space-y-3">
                <p class="text-xs font-bold text-purple-700 dark:text-purple-300 uppercase tracking-wider flex items-center gap-2">
                    <i class="fa-solid fa-address-book"></i> Contact
                </p>
                <div>
                    <label class="label">Phone</label>
                    <input type="tel" name="phone" value="{{ $patient->phone }}" class="input" placeholder="09XX-XXX-XXXX">
                </div>
                <div>
                    <label class="label">Address</label>
                    <textarea name="address" rows="2" class="input resize-none" placeholder="Street, Barangay, City, Province">{{ $patient->address }}</textarea>
                </div>
            </div>

            {{-- Care Team --}}
            <div class="border-l-4 border-emerald-400 bg-emerald-50/30 dark:bg-emerald-900/15 rounded-r-xl p-4 space-y-3">
                <p class="text-xs font-bold text-emerald-700 dark:text-emerald-300 uppercase tracking-wider flex items-center gap-2">
                    <i class="fa-solid fa-stethoscope"></i> Care Team
                </p>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="label">Nurse</label>
                        <select name="assigned_nurse_id" class="input cs-select" data-searchable="true">
                            <option value="">Unassigned</option>
                            @foreach($nurses as $n)
                            <option value="{{ $n->id }}" {{ $patient->assigned_nurse_id == $n->id ? 'selected':'' }}>{{ $n->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="label">Doctor</label>
                        <select name="assigned_doctor_id" class="input cs-select" data-searchable="true">
                            <option value="">Unassigned</option>
                            @foreach($doctors as $d)
                            <option value="{{ $d->id }}" {{ $patient->assigned_doctor_id == $d->id ? 'selected':'' }}>{{ $d->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            {{-- Clinical (only editable if user has medical-view permission) --}}
            @if(auth()->user()->can_('patients.view_medical'))
            <div class="border-l-4 border-amber-400 bg-amber-50/30 dark:bg-amber-900/15 rounded-r-xl p-4 space-y-3">
                <p class="text-xs font-bold text-amber-700 dark:text-amber-300 uppercase tracking-wider flex items-center gap-2">
                    <i class="fa-solid fa-notes-medical"></i> Clinical
                </p>
                <div>
                    <label class="label flex items-center gap-1.5"><i class="fa-solid fa-triangle-exclamation text-red-500"></i> Allergies</label>
                    <input type="text" name="allergies" value="{{ $patient->allergies }}" class="input" placeholder="Penicillin, Sulfa drugs, NKDA">
                </div>
                <div>
                    <label class="label flex items-center gap-1.5"><i class="fa-solid fa-heart text-orange-500"></i> Chronic Conditions</label>
                    <input type="text" name="chronic_conditions" value="{{ $patient->chronic_conditions }}" class="input" placeholder="Hypertension, Type 2 Diabetes">
                </div>
                <div>
                    <label class="label">Medical History &amp; Notes</label>
                    <textarea name="medical_history" rows="6" class="input resize-y leading-relaxed" placeholder="Past surgeries, immunizations, family history, ongoing medications, special instructions">{{ $patient->medical_history }}</textarea>
                </div>
            </div>
            @endif

            {{-- Emergency Contacts --}}
            <div class="border-l-4 border-rose-400 bg-rose-50/30 dark:bg-rose-900/15 rounded-r-xl p-4 space-y-4">
                <p class="text-xs font-bold text-rose-700 dark:text-rose-300 uppercase tracking-wider flex items-center gap-2">
                    <i class="fa-solid fa-phone-volume"></i> Emergency Contacts
                </p>
                <div class="space-y-2">
                    <p class="text-[11px] font-bold uppercase tracking-wider text-rose-600 dark:text-rose-400">Primary</p>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <input type="text" name="emergency_contact_name" value="{{ $patient->emergency_contact_name }}" class="input" placeholder="Contact name">
                        <input type="tel"  name="emergency_contact_phone" value="{{ $patient->emergency_contact_phone }}" class="input" placeholder="09XX-XXX-XXXX">
                    </div>
                </div>
                <div class="space-y-2 pt-2 border-t border-rose-200/60 dark:border-rose-800/50">
                    <p class="text-[11px] font-bold uppercase tracking-wider text-rose-600 dark:text-rose-400">Secondary</p>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <input type="text" name="emergency_contact_2_name" value="{{ $patient->emergency_contact_2_name }}" class="input" placeholder="Backup contact">
                        <input type="tel"  name="emergency_contact_2_phone" value="{{ $patient->emergency_contact_2_phone }}" class="input" placeholder="09XX-XXX-XXXX">
                    </div>
                </div>
            </div>

            <div class="flex justify-between gap-3 pt-3 border-t border-gray-100 dark:border-slate-700">
                <button type="button" onclick="closePatientEditModal()" class="btn-secondary">Cancel</button>
                <button type="submit" class="btn-primary"><i class="fa-solid fa-floppy-disk"></i> Save Changes</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
function openPatientEditModal()  { document.getElementById('patientEditModal').classList.remove('hidden'); document.body.style.overflow='hidden'; }
function closePatientEditModal() { document.getElementById('patientEditModal').classList.add('hidden'); document.body.style.overflow=''; }
function openPatientLightbox(url) {
    const lb = document.getElementById('patientLightbox');
    document.getElementById('patientLightboxImg').src = url;
    lb.classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}
function closePatientLightbox() {
    document.getElementById('patientLightbox').classList.add('hidden');
    document.body.style.overflow = '';
}
document.addEventListener('keydown', e => { if (e.key === 'Escape') { closePatientEditModal(); closePatientLightbox(); } });
</script>
@endpush
@endif

{{-- Patient photo lightbox --}}
<div id="patientLightbox" class="hidden fixed inset-0 bg-black/85 z-50 flex items-center justify-center p-4 backdrop-blur-sm" onclick="closePatientLightbox()">
    <img id="patientLightboxImg" src="" alt="" class="max-w-full max-h-full rounded-2xl shadow-2xl">
    <button type="button" onclick="closePatientLightbox()"
            class="absolute top-4 right-4 w-10 h-10 rounded-full bg-white/15 hover:bg-white/25 text-white flex items-center justify-center backdrop-blur-sm transition-colors">
        <i class="fa-solid fa-xmark text-lg"></i>
    </button>
</div>
@endsection
