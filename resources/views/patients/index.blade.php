@extends('layouts.app')
@section('title', 'Patients')
@section('page-title', 'Patients')

@section('content')
@php
    $me          = Auth::user();
    $isAdmin     = $me->role === 'admin';
    $isDoctor    = $me->role === 'doctor';
    $canPinAll   = $me->can_('patients.pin_all');
    $canCreate   = $me->can_('patients.create');
    $canDelete   = $me->can_('patients.delete');
@endphp

@push('head')
<style>
.patient-card {
    background: #fff;
    border: 2px solid #e5e7eb;
    border-radius: 1.25rem;
    box-shadow: 0 1px 3px rgba(0,0,0,.05);
}
.dark .patient-card { background:#1a2438 !important; border-color:#2d3a52 !important; }

.patient-table thead th {
    padding: 0.95rem 1.5rem;
    font-size: 0.78rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .05em;
    color: #475569;
    background: #f8fafc;
    border-bottom: 2px solid #e2e8f0;
    text-align: center;
    border-right: 1px solid #e2e8f0;
}
.patient-table thead th:last-child { border-right: none; }
.dark .patient-table thead th {
    background: #0f1a2e !important;
    color: #cbd5e1 !important;
    border-bottom-color: #2d3a52 !important;
    border-right-color: #1f2c45 !important;
}

.patient-table tbody td {
    padding: 1rem 1.5rem;
    vertical-align: middle;
    border-right: 1px solid #f1f5f9;
}
.patient-table tbody td:last-child { border-right: none; }
.dark .patient-table tbody td { border-right-color: #1f2c45; }

.patient-table tbody tr {
    transition: background-color .12s;
    border-bottom: 1px solid #f1f5f9;
    cursor: pointer;
}
.dark .patient-table tbody tr { border-bottom-color:#1f2c45; }
.patient-table tbody tr:hover { background: #eff6ff; }
.dark .patient-table tbody tr:hover { background: #1a2438 !important; }
.patient-table tbody tr:last-child { border-bottom: none; }

/* Inline meta — equal weight with primary text so vision-impaired staff aren't squinting. */
.patient-meta { color: #64748b; font-weight: 500; }
.dark .patient-meta { color: #94a3b8; }

/* Plain mono patient ID — no chip, just clear monospace text */
.patient-id-text {
    font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
    font-size: 0.92rem;
    font-weight: 700;
    letter-spacing: .02em;
    color: #1e293b;
}
.dark .patient-id-text { color: #e2e8f0; }

.assign-line {
    display: inline-flex; align-items: center; gap: .5rem;
    font-size: .95rem;
    line-height: 1.25rem;
}
.assign-line .role-tag {
    font-size: .65rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: .04em;
    padding: .15rem .5rem;
    border-radius: 9999px;
    flex-shrink: 0;
}
.role-tag.doctor { background:#dbeafe; color:#1d4ed8; }
.role-tag.nurse  { background:#cffafe; color:#0e7490; }
.dark .role-tag.doctor { background: rgba(59,130,246,0.2) !important; color:#93c5fd !important; }
.dark .role-tag.nurse  { background: rgba(6,182,212,0.2)  !important; color:#67e8f9 !important; }

.btn-row-pin {
    display:inline-flex; align-items:center; justify-content:center; gap:.5rem;
    padding: 0.625rem 0.75rem;
    border-radius: 0.75rem;
    font-size: 0.875rem;
    font-weight: 600;
    flex: 1;
    transition: background .12s, box-shadow .12s;
}
.btn-row-pin.pinned       { background:#f59e0b; color:#fff; box-shadow: 0 2px 6px rgba(245,158,11,.35); }
.btn-row-pin.pinned:hover { background:#d97706; }
.btn-row-pin.unpinned     { background:#fef3c7; color:#92400e; }
.btn-row-pin.unpinned:hover { background:#fde68a; }
.dark .btn-row-pin.unpinned       { background: rgba(245,158,11,.18); color:#fcd34d; }
.dark .btn-row-pin.unpinned:hover { background: rgba(245,158,11,.28); }

.btn-row-delete {
    display:inline-flex; align-items:center; justify-content:center; gap:.5rem;
    padding: 0.625rem 0.75rem;
    border-radius: 0.75rem;
    font-size: 0.875rem;
    font-weight: 600;
    flex: 1;
    background:#fee2e2; color:#b91c1c;
    transition:background .12s;
}
.btn-row-delete:hover { background:#fecaca; }
.dark .btn-row-delete       { background: rgba(239,68,68,.18); color:#fca5a5; }
.dark .btn-row-delete:hover { background: rgba(239,68,68,.28); }
</style>
@endpush

<div class="space-y-5">

    <!-- Header -->
    <div class="flex items-center justify-between flex-wrap gap-3">
        <div>
            <h1 class="text-2xl font-extrabold text-gray-900 dark:text-white">Patients</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">
                {{ $patients->total() }} records total &bull; {{ $canCreate ? 'Add and manage patient records' : 'View patient records' }}
            </p>
        </div>
        @if($canCreate)
        <button onclick="openModal()" class="btn-primary">
            <i class="fa-solid fa-user-plus"></i> Add Patient
        </button>
        @endif
    </div>

    <!-- Search + filter -->
    <form method="GET" action="{{ route('patients.index') }}" class="patient-card p-3">
        @php $hasFilters = request('nurse_id') || request('doctor_id') || request('sort') || request('direction'); @endphp
        <div class="flex items-center gap-2">
            <div class="relative">
                <button type="button" onclick="toggleDropdown('patientFilterMenu')"
                        class="h-12 px-4 bg-white dark:bg-slate-800 border-2 border-gray-200 dark:border-slate-600 rounded-xl hover:border-brand-400 dark:hover:border-brand-500 transition-colors flex items-center gap-2 text-sm text-gray-600 dark:text-gray-200 font-medium {{ $hasFilters ? 'border-brand-500 text-brand-700 dark:text-brand-300' : '' }}">
                    <i class="fa-solid fa-sliders text-sm"></i>
                    <span class="hidden sm:inline">Filter & Sort</span>
                    @if($hasFilters)
                    <span class="bg-brand-500 text-white text-[10px] font-bold rounded-full w-4 h-4 flex items-center justify-center">!</span>
                    @endif
                </button>
                <div id="patientFilterMenu" class="hidden absolute left-0 top-full mt-2 w-80 bg-white dark:bg-slate-800 border-2 border-gray-100 dark:border-slate-700 rounded-2xl shadow-xl p-4 space-y-4 z-30">
                    <div>
                        <p class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-2 flex items-center gap-1.5"><i class="fa-solid fa-filter"></i> Filter by</p>
                        <div class="space-y-2">
                            <select name="nurse_id" class="input cs-select">
                                <option value="">All Nurses</option>
                                @foreach($nurses as $n)
                                <option value="{{ $n->id }}" {{ request('nurse_id')==$n->id ? 'selected' : '' }}>{{ $n->name }}</option>
                                @endforeach
                            </select>
                            <select name="doctor_id" class="input cs-select">
                                <option value="">All Doctors</option>
                                @foreach($doctors as $d)
                                <option value="{{ $d->id }}" {{ request('doctor_id')==$d->id ? 'selected' : '' }}>{{ $d->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="pt-2 border-t border-gray-100 dark:border-slate-700">
                        <p class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-2 flex items-center gap-1.5"><i class="fa-solid fa-arrow-down-wide-short"></i> Sort by</p>
                        <div class="grid grid-cols-2 gap-2">
                            <select name="sort" class="input cs-select">
                                @foreach(['name'=>'Name','last_visit'=>'Last Visit','patient_id'=>'Patient ID'] as $f=>$label)
                                <option value="{{ $f }}" {{ $sortField===$f ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                            <select name="direction" class="input cs-select">
                                <option value="asc"  {{ $sortDir==='asc' ? 'selected' : '' }}>↑ Asc</option>
                                <option value="desc" {{ $sortDir==='desc' ? 'selected' : '' }}>↓ Desc</option>
                            </select>
                        </div>
                    </div>
                    <div class="flex gap-2 pt-3 border-t border-gray-100 dark:border-slate-700">
                        <a href="{{ route('patients.index') }}" class="inline-flex flex-1 items-center justify-center gap-2 px-4 py-2.5 rounded-xl border-2 border-gray-200 dark:border-slate-600 bg-white dark:bg-slate-800 text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-slate-700 text-sm font-semibold transition-colors">
                            <i class="fa-solid fa-rotate-left"></i> Reset
                        </a>
                        <button type="submit" class="inline-flex flex-1 items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-brand-600 hover:bg-brand-700 text-white text-sm font-bold transition-colors shadow-sm">
                            <i class="fa-solid fa-check"></i> Apply
                        </button>
                    </div>
                </div>
            </div>

            <div class="relative flex-1">
                <i class="fa-solid fa-search absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 text-base pointer-events-none"></i>
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Search by name, ID, nurse or doctor"
                       class="block w-full h-12 pl-12 pr-4 border-2 border-gray-200 dark:border-slate-600 rounded-xl text-base text-gray-800 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500 focus:ring-2 focus:ring-brand-500 focus:border-brand-500 outline-none transition-all bg-white dark:bg-slate-800">
            </div>

            <button type="submit" class="hidden md:inline-flex h-12 px-5 items-center justify-center gap-2 bg-brand-600 hover:bg-brand-700 text-white rounded-xl transition-colors shadow-sm flex-shrink-0 text-sm font-semibold" title="Search">
                <i class="fa-solid fa-magnifying-glass"></i>
                <span class="hidden lg:inline">Search</span>
            </button>
        </div>
    </form>

    <!-- Patient table -->
    <div class="patient-card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full patient-table">
                <thead>
                    <tr>
                        <th>Patient</th>
                        <th>Patient ID</th>
                        <th>Age</th>
                        <th>Assigned Care Team</th>
                        <th>Last Visit</th>
                        <th style="min-width: 280px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($patients as $patient)
                    @php
                        $isPinned = in_array($patient->id, $pinnedIds);
                        // Deterministic initials-based gradient picker so the same patient always
                        // gets the same blue/indigo/cyan tone — keeps the directory feeling
                        // distinct row-to-row without random reshuffling on every page load.
                        $gradients = [
                            'from-blue-400 to-indigo-600',
                            'from-sky-400 to-blue-600',
                            'from-indigo-400 to-blue-600',
                            'from-cyan-400 to-blue-600',
                            'from-blue-500 to-indigo-700',
                        ];
                        $grad = $gradients[crc32($patient->patient_id) % count($gradients)];
                    @endphp
                    <tr data-href="{{ route('patients.show', $patient) }}"
                        onclick="if(!event.target.closest('.row-action')) window.location=this.dataset.href"
                        class="group">
                        <td>
                            <div class="flex items-center gap-3">
                                <div class="h-10 w-10 rounded-full bg-gradient-to-br {{ $grad }} flex items-center justify-center text-white text-sm font-bold flex-shrink-0">
                                    {{ strtoupper(substr($patient->name, 0, 2)) }}
                                </div>
                                <div class="min-w-0">
                                    <p class="font-semibold text-sm text-gray-900 dark:text-white group-hover:text-blue-700 dark:group-hover:text-blue-300 transition-colors truncate flex items-center gap-1.5">
                                        {{ $patient->name }}
                                        @if($isPinned)
                                        <i class="fa-solid fa-thumbtack text-amber-500 text-xs flex-shrink-0" title="Pinned"></i>
                                        @endif
                                    </p>
                                    <p class="text-sm patient-meta truncate">{{ $patient->phone ?? 'No phone on file' }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="text-center">
                            <span class="patient-id-text">{{ $patient->patient_id }}</span>
                        </td>
                        <td class="text-center">
                            @if($patient->date_of_birth)
                                <p class="text-sm font-semibold text-gray-800 dark:text-gray-100">{{ $patient->date_of_birth->age }} years</p>
                                <p class="text-sm patient-meta">{{ $patient->date_of_birth->format('M j, Y') }}</p>
                            @else
                                <span class="text-sm patient-meta italic">Not on file</span>
                            @endif
                        </td>
                        <td>
                            <div class="flex flex-col items-center gap-2">
                                <span class="assign-line">
                                    <span class="role-tag doctor">DR</span>
                                    <span class="text-gray-900 dark:text-white font-semibold">{{ $patient->doctor?->name ?? 'Unassigned' }}</span>
                                </span>
                                <span class="assign-line">
                                    <span class="role-tag nurse">RN</span>
                                    <span class="text-gray-700 dark:text-gray-200 font-medium">{{ $patient->nurse?->name ?? 'Unassigned' }}</span>
                                </span>
                            </div>
                        </td>
                        <td class="text-center">
                            @if($patient->last_visit)
                                <p class="text-sm font-semibold text-gray-800 dark:text-gray-100">{{ $patient->last_visit->format('M j, Y') }}</p>
                                <p class="text-sm patient-meta">{{ $patient->last_visit->diffForHumans() }}</p>
                            @else
                                <span class="text-sm patient-meta italic">Never visited</span>
                            @endif
                        </td>
                        <td>
                            <div class="flex items-center gap-3 row-action w-full">
                                <!-- Pin dropdown -->
                                <div class="relative flex-1">
                                    <button type="button"
                                            onclick="event.stopPropagation(); toggleDropdown('pinMenu-{{ $patient->id }}')"
                                            class="row-action btn-row-pin {{ $isPinned ? 'pinned' : 'unpinned' }} w-full"
                                            title="Pin options">
                                        <i class="fa-solid fa-thumbtack"></i>
                                        {{ $isPinned ? 'Pinned' : 'Pin' }}
                                        <i class="fa-solid fa-chevron-down text-[10px]"></i>
                                    </button>
                                    <div id="pinMenu-{{ $patient->id }}" class="hidden absolute right-0 mt-2 w-52 bg-white dark:bg-slate-800 rounded-xl shadow-lg border-2 border-gray-100 dark:border-slate-700 py-2 z-20">
                                        @if($isPinned)
                                        <button type="button" onclick="event.stopPropagation(); pinPatient({{ $patient->id }}, 'self')"
                                                class="w-full text-left px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-slate-700 flex items-center gap-2">
                                            <i class="fa-solid fa-thumbtack-slash text-rose-500 w-4"></i> Unpin from myself
                                        </button>
                                        <div class="my-1 border-t border-gray-100 dark:border-slate-700"></div>
                                        @else
                                        <button type="button" onclick="event.stopPropagation(); pinPatient({{ $patient->id }}, 'self')"
                                                class="w-full text-left px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-slate-700 flex items-center gap-2">
                                            <i class="fa-solid fa-user text-amber-500 w-4"></i> Pin to myself
                                        </button>
                                        @endif
                                        @if($canPinAll)
                                        <button type="button" onclick="event.stopPropagation(); pinPatient({{ $patient->id }}, 'all')"
                                                class="w-full text-left px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-slate-700 flex items-center gap-2">
                                            <i class="fa-solid fa-users text-brand-500 w-4"></i> Pin to everyone
                                        </button>
                                        @endif
                                        <button type="button" onclick="event.stopPropagation(); openPinSomeoneModal({{ $patient->id }}, '{{ addslashes($patient->name) }}')"
                                                class="w-full text-left px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-slate-700 flex items-center gap-2">
                                            <i class="fa-solid fa-user-plus text-purple-500 w-4"></i> Pin to someone
                                        </button>
                                    </div>
                                </div>
                                @if($canDelete)
                                <form method="POST" action="{{ route('patients.destroy', $patient) }}" class="inline row-action flex-1"
                                      onsubmit="event.stopPropagation(); return confirm('Delete {{ addslashes($patient->name) }}\'s record?')"
                                      onclick="event.stopPropagation()">
                                    @csrf @method('DELETE')
                                    <button type="submit" onclick="event.stopPropagation()" class="row-action btn-row-delete w-full">
                                        <i class="fa-solid fa-trash"></i> Delete
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="py-16 text-center">
                            <div class="w-16 h-16 bg-blue-50 dark:bg-slate-800 rounded-2xl flex items-center justify-center mx-auto mb-3">
                                <i class="fa-solid fa-user-injured text-blue-400 dark:text-gray-500 text-2xl"></i>
                            </div>
                            <p class="text-gray-500 dark:text-gray-400 font-medium">No patients match your filters</p>
                            @if($canCreate)
                            <button onclick="openModal()" class="text-blue-600 dark:text-blue-300 hover:underline text-sm mt-1 font-semibold">Add the first one</button>
                            @endif
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($patients->hasPages())
        <div class="px-6 py-3 border-t border-gray-100 dark:border-slate-700 bg-gray-50/50 dark:bg-slate-900/40">{{ $patients->links() }}</div>
        @endif
    </div>
</div>

<!-- ── Add Patient Modal ── -->
<div id="addPatientModal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4 backdrop-blur-sm">
    <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-2xl w-full max-w-2xl max-h-[90vh] overflow-y-auto">
        <div class="bg-gradient-to-r from-brand-600 to-brand-700 px-6 py-5 text-white">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center">
                        <i class="fa-solid fa-user-plus text-white"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold">Add New Patient</h3>
                        <p class="text-xs text-white/80">A unique Patient ID will be assigned automatically</p>
                    </div>
                </div>
                <button onclick="closeModal()" class="w-8 h-8 rounded-xl flex items-center justify-center text-white/80 hover:text-white hover:bg-white/20">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
        </div>

        <form method="POST" action="{{ route('patients.store') }}" class="px-6 py-5 space-y-5">
            @csrf

            <!-- Identity -->
            <div class="border-l-4 border-blue-400 bg-blue-50/30 rounded-r-xl p-4 space-y-3">
                <p class="text-xs font-bold text-blue-700 dark:text-blue-300 uppercase tracking-wider flex items-center gap-2">
                    <i class="fa-solid fa-id-card"></i> Identity
                </p>
                <div>
                    <label class="label">Full Name <span class="text-red-500">*</span></label>
                    <input type="text" name="name" required value="{{ old('name') }}" class="input" placeholder="Juan Dela Cruz">
                </div>
            </div>

            <!-- Personal -->
            <div class="border-l-4 border-purple-400 bg-purple-50/30 rounded-r-xl p-4 space-y-3">
                <p class="text-xs font-bold text-purple-700 dark:text-purple-300 uppercase tracking-wider flex items-center gap-2">
                    <i class="fa-solid fa-user"></i> Personal Details
                </p>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="label">Date of Birth</label>
                        <input type="date" name="date_of_birth" value="{{ old('date_of_birth') }}" class="input">
                    </div>
                    <div>
                        <label class="label">Phone</label>
                        <input type="tel" name="phone" value="{{ old('phone') }}" placeholder="09XX-XXX-XXXX" class="input">
                    </div>
                </div>
                <div>
                    <label class="label">Address</label>
                    <textarea name="address" rows="2" class="input resize-none" placeholder="Street, Barangay, City, Province, Zip">{{ old('address') }}</textarea>
                </div>
            </div>

            <!-- Care Team -->
            <div class="border-l-4 border-emerald-400 bg-emerald-50/30 rounded-r-xl p-4 space-y-3">
                <p class="text-xs font-bold text-emerald-700 dark:text-emerald-300 uppercase tracking-wider flex items-center gap-2">
                    <i class="fa-solid fa-stethoscope"></i> Care Team
                </p>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="label">Assigned Nurse</label>
                        <select name="assigned_nurse_id" class="input">
                            <option value="">Select Nurse</option>
                            @foreach($nurses as $n)
                            <option value="{{ $n->id }}" {{ old('assigned_nurse_id')==$n->id ? 'selected':'' }}>{{ $n->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="label">Assigned Doctor</label>
                        <select name="assigned_doctor_id" class="input">
                            <option value="">Select Doctor</option>
                            @foreach($doctors as $d)
                            <option value="{{ $d->id }}" {{ old('assigned_doctor_id')==$d->id ? 'selected':'' }}>{{ $d->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <!-- Medical Notes -->
            <div class="border-l-4 border-amber-400 bg-amber-50/30 rounded-r-xl p-4 space-y-3">
                <p class="text-xs font-bold text-amber-700 dark:text-amber-300 uppercase tracking-wider flex items-center gap-2">
                    <i class="fa-solid fa-notes-medical"></i> Medical Notes
                </p>
                <textarea name="medical_history" rows="6" class="input resize-y" placeholder="Allergies, conditions, medications, family history, special instructions">{{ old('medical_history') }}</textarea>
            </div>

            <div class="flex justify-between items-center gap-3 pt-3 border-t border-gray-100 dark:border-slate-700">
                <button type="button" onclick="closeModal()"
                        class="px-6 py-2.5 bg-red-100 hover:bg-red-200 text-red-700 text-sm font-semibold rounded-xl transition-colors">
                    Cancel
                </button>
                <button type="submit"
                        class="px-6 py-2.5 bg-brand-600 hover:bg-brand-700 text-white text-sm font-semibold rounded-xl transition-colors shadow-sm">
                    Save Patient
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ── Pin to Someone Modal ── -->
<div id="pinSomeoneModal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4 backdrop-blur-sm">
    <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-2xl w-full max-w-md border-2 border-gray-100 dark:border-slate-700">
        <div class="px-6 py-5 border-b border-gray-100 dark:border-slate-700">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white">Pin to Staff Member</h3>
                <button type="button" onclick="closePinSomeoneModal()" class="w-8 h-8 rounded-xl flex items-center justify-center text-gray-400 hover:text-gray-700 hover:bg-gray-100 dark:hover:bg-slate-800"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1" id="pinSomeoneTarget">—</p>
        </div>
        <div class="px-6 py-5 space-y-3">
            <label class="label">Pick a staff member</label>
            <select id="pinSomeoneUser" class="input">
                <option value="">Select</option>
                @foreach(\App\Models\User::orderBy('name')->get() as $u)
                <option value="{{ $u->id }}">{{ $u->name }} ({{ $u->roleLabel() }})</option>
                @endforeach
            </select>
            <div class="flex justify-between gap-3 pt-3 border-t border-gray-100 dark:border-slate-700">
                <button type="button" onclick="closePinSomeoneModal()" class="px-5 py-2 bg-red-100 hover:bg-red-200 text-red-700 text-sm font-semibold rounded-xl">Cancel</button>
                <button type="button" id="pinSomeoneSubmit" class="px-5 py-2 bg-brand-600 hover:bg-brand-700 text-white text-sm font-semibold rounded-xl">Pin Now</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
let pinSomeonePatientId = null;

function openModal()  { document.getElementById('addPatientModal').classList.remove('hidden'); document.body.style.overflow='hidden'; }
function closeModal() { document.getElementById('addPatientModal').classList.add('hidden'); document.body.style.overflow=''; }
document.getElementById('addPatientModal').addEventListener('click', e => { if(e.target===e.currentTarget) closeModal(); });

function openPinSomeoneModal(id, name) {
    pinSomeonePatientId = id;
    document.getElementById('pinSomeoneTarget').textContent = `Patient: ${name}`;
    document.getElementById('pinSomeoneModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}
function closePinSomeoneModal() {
    document.getElementById('pinSomeoneModal').classList.add('hidden');
    document.body.style.overflow = '';
}
document.getElementById('pinSomeoneSubmit').addEventListener('click', () => {
    const userId = document.getElementById('pinSomeoneUser').value;
    if (!userId) { alert('Pick a staff member.'); return; }
    pinPatient(pinSomeonePatientId, userId);
    closePinSomeoneModal();
});

document.addEventListener('keydown', e => { if(e.key==='Escape'){ closeModal(); closePinSomeoneModal(); } });

async function pinPatient(id, target) {
    try {
        const r = await fetch(`/patients/${id}/pin`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ target })
        });
        if (!r.ok) throw new Error('HTTP ' + r.status);
        await r.json();
        location.reload();
    } catch (err) {
        alert('Pin failed: ' + err.message);
    }
}

@if($errors->any()) openModal(); @endif
</script>
@endpush
@endsection
