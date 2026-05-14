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

<div class="space-y-5">

    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Patients</h1>
            <p class="text-sm text-gray-500 mt-0.5">{{ $patients->total() }} records total</p>
        </div>
        @if($canCreate)
        <button onclick="openModal()" class="btn-primary">
            <i class="fa-solid fa-plus"></i> Add Patient
        </button>
        @endif
    </div>

    <!-- Search + filter (sort merged into filter popover) -->
    <form method="GET" action="{{ route('patients.index') }}" class="card p-3">
        @php $hasFilters = request('nurse_id') || request('doctor_id') || request('sort') || request('direction'); @endphp
        <div class="flex items-center gap-2">
            <!-- Filter + sort popover -->
            <div class="relative">
                <button type="button" onclick="toggleDropdown('patientFilterMenu')"
                        class="h-12 px-4 bg-white border-2 border-gray-200 rounded-xl hover:border-brand-400 transition-colors flex items-center gap-2 text-sm text-gray-600 font-medium {{ $hasFilters ? 'border-brand-500 text-brand-700' : '' }}">
                    <i class="fa-solid fa-sliders text-sm"></i>
                    <span class="hidden sm:inline">Filter & Sort</span>
                    @if($hasFilters)
                    <span class="bg-brand-500 text-white text-[10px] font-bold rounded-full w-4 h-4 flex items-center justify-center">!</span>
                    @endif
                </button>
                <div id="patientFilterMenu" class="hidden absolute left-0 top-full mt-2 w-80 bg-white border border-gray-100 rounded-xl shadow-xl p-4 space-y-4 z-30">
                    <!-- Filter section -->
                    <div>
                        <p class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-2 flex items-center gap-1.5"><i class="fa-solid fa-filter"></i> Filter by</p>
                        <div class="space-y-2">
                            <select name="nurse_id" class="input">
                                <option value="">All Nurses</option>
                                @foreach($nurses as $n)
                                <option value="{{ $n->id }}" {{ request('nurse_id')==$n->id ? 'selected' : '' }}>{{ $n->name }}</option>
                                @endforeach
                            </select>
                            <select name="doctor_id" class="input">
                                <option value="">All Doctors</option>
                                @foreach($doctors as $d)
                                <option value="{{ $d->id }}" {{ request('doctor_id')==$d->id ? 'selected' : '' }}>{{ $d->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <!-- Sort section -->
                    <div class="pt-2 border-t border-gray-100">
                        <p class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-2 flex items-center gap-1.5"><i class="fa-solid fa-arrow-down-wide-short"></i> Sort by</p>
                        <div class="grid grid-cols-2 gap-2">
                            <select name="sort" class="input">
                                @foreach(['name'=>'Name','last_visit'=>'Last Visit','patient_id'=>'Patient ID'] as $f=>$label)
                                <option value="{{ $f }}" {{ $sortField===$f ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                            <select name="direction" class="input">
                                <option value="asc"  {{ $sortDir==='asc' ? 'selected' : '' }}>↑ Ascending</option>
                                <option value="desc" {{ $sortDir==='desc' ? 'selected' : '' }}>↓ Descending</option>
                            </select>
                        </div>
                    </div>
                    <div class="flex gap-2 pt-2 border-t border-gray-100">
                        <button type="submit" class="btn-primary flex-1 justify-center text-xs py-2">Apply</button>
                        <a href="{{ route('patients.index') }}" class="btn-secondary flex-1 justify-center text-xs py-2">Reset</a>
                    </div>
                </div>
            </div>

            <!-- Big search bar -->
            <div class="relative flex-1">
                <i class="fa-solid fa-search absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 text-base pointer-events-none"></i>
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Search by name, ID, nurse or doctor…"
                       class="block w-full h-12 pl-12 pr-4 border-2 border-gray-200 rounded-xl text-base text-gray-800 placeholder-gray-400 focus:ring-2 focus:ring-brand-500 focus:border-brand-500 outline-none transition-all bg-white">
                {{-- Sort/direction are preserved by the filter dropdown's <select> elements (selected attribute), so no hidden inputs are needed. --}}
            </div>

            <button type="submit" class="hidden md:inline-flex h-12 px-5 items-center justify-center gap-2 bg-brand-600 hover:bg-brand-700 text-white rounded-xl transition-colors shadow-sm flex-shrink-0 text-sm font-semibold" title="Search">
                <i class="fa-solid fa-magnifying-glass"></i>
                <span class="hidden lg:inline">Search</span>
            </button>
        </div>
    </form>

    <!-- Table -->
    <div class="card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full">
                <thead>
                    <tr class="bg-gradient-to-r from-gray-50 to-slate-50 border-b-2 border-gray-200 divide-x divide-gray-200">
                        <th class="th">Patient</th>
                        <th class="th text-center">ID</th>
                        <th class="th text-center">Age</th>
                        <th class="th text-center">Assigned To</th>
                        <th class="th text-center">Last Visit</th>
                        <th class="th text-center" style="width: 280px;">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($patients as $patient)
                    @php $isPinned = in_array($patient->id, $pinnedIds); @endphp
                    <tr data-href="{{ route('patients.show', $patient) }}"
                        onclick="if(!event.target.closest('.row-action')) window.location=this.dataset.href"
                        class="hover:bg-blue-50/40 transition-colors group cursor-pointer divide-x divide-gray-100">
                        <td class="td">
                            <p class="font-semibold text-gray-900 dark:text-white group-hover:text-blue-700 transition-colors flex items-center gap-1.5">
                                {{ $patient->name }}
                                @if($isPinned)
                                <i class="fa-solid fa-thumbtack text-amber-500 text-xs"></i>
                                @endif
                            </p>
                            <p class="text-xs text-gray-400">{{ $patient->phone ?? 'No phone' }}</p>
                        </td>
                        <td class="td text-center">
                            <span class="font-mono text-sm font-bold text-gray-700 dark:text-gray-200">{{ $patient->patient_id }}</span>
                        </td>
                        <td class="td text-center text-gray-600">
                            @if($patient->date_of_birth)
                                <p class="font-semibold">{{ $patient->date_of_birth->age }} yrs</p>
                                <p class="text-xs text-gray-400">{{ $patient->date_of_birth->format('M j, Y') }}</p>
                            @else
                                <span class="text-gray-300">—</span>
                            @endif
                        </td>
                        <td class="td text-center">
                            <p class="text-sm font-medium text-gray-800">{{ $patient->doctor?->name ?? 'Unassigned' }}</p>
                            <p class="text-xs text-gray-400">Nurse: {{ $patient->nurse?->name ?? '—' }}</p>
                        </td>
                        <td class="td text-center">
                            @if($patient->last_visit)
                                <p class="text-sm text-gray-700">{{ $patient->last_visit->format('M j, Y') }}</p>
                                <p class="text-xs text-gray-400">{{ $patient->last_visit->diffForHumans() }}</p>
                            @else
                                <span class="text-gray-300">—</span>
                            @endif
                        </td>
                        <td class="td px-2">
                            <div class="flex items-center justify-stretch gap-3 row-action w-full">
                                <!-- Pin dropdown -->
                                <div class="relative flex-1">
                                    <button type="button"
                                            onclick="event.stopPropagation(); toggleDropdown('pinMenu-{{ $patient->id }}')"
                                            class="row-action inline-flex w-full items-center gap-2 px-3 py-2.5 rounded-xl text-sm font-semibold transition-colors justify-center
                                                {{ $isPinned ? 'bg-amber-500 text-white hover:bg-amber-600 shadow-sm' : 'bg-amber-100 text-amber-700 hover:bg-amber-200' }}"
                                            title="Pin options">
                                        <i class="fa-solid fa-thumbtack"></i>
                                        {{ $isPinned ? 'Pinned' : 'Pin' }}
                                        <i class="fa-solid fa-chevron-down text-[10px]"></i>
                                    </button>
                                    <div id="pinMenu-{{ $patient->id }}" class="hidden absolute right-0 mt-2 w-48 bg-white rounded-xl shadow-lg border border-gray-100 py-2 z-20">
                                        <button type="button" onclick="event.stopPropagation(); pinPatient({{ $patient->id }}, 'self')"
                                                class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 flex items-center gap-2">
                                            <i class="fa-solid fa-user text-amber-500 w-4"></i> Pin to myself
                                        </button>
                                        @if($canPinAll)
                                        <button type="button" onclick="event.stopPropagation(); pinPatient({{ $patient->id }}, 'all')"
                                                class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 flex items-center gap-2">
                                            <i class="fa-solid fa-users text-brand-500 w-4"></i> Pin to everyone
                                        </button>
                                        @endif
                                        <button type="button" onclick="event.stopPropagation(); openPinSomeoneModal({{ $patient->id }}, '{{ addslashes($patient->name) }}')"
                                                class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 flex items-center gap-2">
                                            <i class="fa-solid fa-user-plus text-purple-500 w-4"></i> Pin to someone
                                        </button>
                                    </div>
                                </div>
                                @if($canDelete)
                                <form method="POST" action="{{ route('patients.destroy', $patient) }}" class="inline row-action flex-1"
                                      onsubmit="event.stopPropagation(); return confirm('Delete {{ addslashes($patient->name) }}\'s record?')"
                                      onclick="event.stopPropagation()">
                                    @csrf @method('DELETE')
                                    <button type="submit" onclick="event.stopPropagation()"
                                            class="row-action inline-flex w-full items-center gap-2 px-3 py-2.5 rounded-xl text-sm font-semibold bg-red-100 text-red-700 hover:bg-red-200 transition-colors justify-center">
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
                            <div class="w-16 h-16 bg-gray-100 rounded-2xl flex items-center justify-center mx-auto mb-3">
                                <i class="fa-solid fa-users text-gray-400 text-2xl"></i>
                            </div>
                            <p class="text-gray-500 font-medium">No patients found</p>
                            <button onclick="openModal()" class="text-brand-600 hover:underline text-sm mt-1">Add the first one</button>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($patients->hasPages())
        <div class="px-6 py-3 border-t border-gray-100 bg-gray-50/50">{{ $patients->links() }}</div>
        @endif
    </div>
</div>

<!-- ── Add Patient Modal (cleaner, bigger boxes, no patient_id field) ── -->
<div id="addPatientModal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4 backdrop-blur-sm">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl max-h-[90vh] overflow-y-auto">
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
                <p class="text-xs font-bold text-blue-700 uppercase tracking-wider flex items-center gap-2">
                    <i class="fa-solid fa-id-card"></i> Identity
                </p>
                <div>
                    <label class="label">Full Name <span class="text-red-500">*</span></label>
                    <input type="text" name="name" required value="{{ old('name') }}" class="input" placeholder="Juan Dela Cruz">
                </div>
            </div>

            <!-- Personal -->
            <div class="border-l-4 border-purple-400 bg-purple-50/30 rounded-r-xl p-4 space-y-3">
                <p class="text-xs font-bold text-purple-700 uppercase tracking-wider flex items-center gap-2">
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
                    <textarea name="address" rows="2" class="input resize-none" placeholder="Street, Barangay, City, Province, Zip…">{{ old('address') }}</textarea>
                </div>
            </div>

            <!-- Care Team -->
            <div class="border-l-4 border-emerald-400 bg-emerald-50/30 rounded-r-xl p-4 space-y-3">
                <p class="text-xs font-bold text-emerald-700 uppercase tracking-wider flex items-center gap-2">
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
                <p class="text-xs font-bold text-amber-700 uppercase tracking-wider flex items-center gap-2">
                    <i class="fa-solid fa-notes-medical"></i> Medical Notes
                </p>
                <textarea name="medical_history" rows="6" class="input resize-y" placeholder="Allergies, conditions, medications, family history, special instructions…">{{ old('medical_history') }}</textarea>
            </div>

            <!-- Buttons (colored, no icons, separated) -->
            <div class="flex justify-between items-center gap-3 pt-3 border-t border-gray-100">
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
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md">
        <div class="px-6 py-5 border-b border-gray-100">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-bold text-gray-900">Pin to Staff Member</h3>
                <button type="button" onclick="closePinSomeoneModal()" class="w-8 h-8 rounded-xl flex items-center justify-center text-gray-400 hover:text-gray-700 hover:bg-gray-100"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <p class="text-sm text-gray-500 mt-1" id="pinSomeoneTarget">—</p>
        </div>
        <div class="px-6 py-5 space-y-3">
            <label class="label">Pick a staff member</label>
            <select id="pinSomeoneUser" class="input">
                <option value="">Select…</option>
                @foreach(\App\Models\User::orderBy('name')->get() as $u)
                <option value="{{ $u->id }}">{{ $u->name }} ({{ $u->roleLabel() }})</option>
                @endforeach
            </select>
            <div class="flex justify-between gap-3 pt-3 border-t border-gray-100">
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
