@extends('layouts.app')
@section('title', 'Patients')
@section('page-title', 'Patients')

@section('content')
<div class="space-y-5">

    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Patients</h1>
            <p class="text-sm text-gray-500 mt-0.5">{{ $patients->total() }} records total</p>
        </div>
        <button onclick="openModal()" class="btn-primary">
            <i class="fa-solid fa-plus"></i> Add Patient
        </button>
    </div>

    <!-- Search & Filters -->
    <form method="GET" action="{{ route('patients.index') }}" class="card p-4">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
            <div class="md:col-span-2 relative">
                <i class="fa-solid fa-search absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 text-sm pointer-events-none"></i>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by name, ID, nurse or doctor..." class="input pl-10">
            </div>
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
        <div class="flex items-center justify-between mt-3">
            <div class="flex items-center gap-1 text-sm text-gray-500">
                <span>Sort:</span>
                @foreach(['name'=>'Name','last_visit'=>'Last Visit','patient_id'=>'Patient ID'] as $f=>$label)
                <a href="{{ request()->fullUrlWithQuery(['sort'=>$f,'direction'=>($sortField===$f&&$sortDir==='asc')?'desc':'asc']) }}"
                   class="px-2.5 py-1 rounded-lg font-medium transition-colors {{ $sortField===$f ? 'bg-brand-600 text-white' : 'text-gray-500 hover:bg-gray-100' }}">
                    {{ $label }}@if($sortField===$f) <i class="fa-solid fa-arrow-{{ $sortDir==='asc'?'up':'down' }} text-xs"></i>@endif
                </a>
                @endforeach
            </div>
            <div class="flex gap-2">
                <button type="submit" class="btn-primary py-1.5 text-xs">Apply</button>
                <a href="{{ route('patients.index') }}" class="btn-secondary py-1.5 text-xs">Clear</a>
            </div>
        </div>
    </form>

    <!-- Table with vertical separators + centered columns -->
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
                        <th class="th text-center">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($patients as $patient)
                    @php $isPinned = in_array($patient->id, $pinnedIds); @endphp
                    <tr data-href="{{ route('patients.show', $patient) }}"
                        onclick="if(!event.target.closest('.row-action')) window.location=this.dataset.href"
                        class="hover:bg-brand-50/40 transition-colors group cursor-pointer divide-x divide-gray-100">
                        <td class="td">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-full bg-gradient-to-br from-brand-400 to-brand-700 flex items-center justify-center text-white text-xs font-bold flex-shrink-0">
                                    {{ strtoupper(substr($patient->name,0,2)) }}
                                </div>
                                <div>
                                    <p class="font-semibold text-gray-900 group-hover:text-brand-700 transition-colors flex items-center gap-1.5">
                                        {{ $patient->name }}
                                        @if($isPinned)
                                        <i class="fa-solid fa-thumbtack text-amber-500 text-xs"></i>
                                        @endif
                                    </p>
                                    <p class="text-xs text-gray-400">{{ $patient->phone ?? 'No phone' }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="td text-center">
                            <span class="font-mono text-xs bg-slate-100 text-slate-700 px-2 py-1 rounded-lg inline-block">{{ $patient->patient_id }}</span>
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
                        <td class="td text-center">
                            <div class="flex items-center justify-center gap-1 row-action">
                                <button type="button"
                                        onclick="event.stopPropagation(); event.preventDefault(); togglePin({{ $patient->id }}, this);"
                                        class="row-action w-8 h-8 rounded-lg flex items-center justify-center transition-colors
                                            {{ $isPinned ? 'bg-amber-100 text-amber-600 hover:bg-amber-200' : 'hover:bg-amber-50 text-gray-400 hover:text-amber-500' }}"
                                        title="{{ $isPinned ? 'Unpin' : 'Pin to dashboard' }}">
                                    <i class="fa-solid fa-thumbtack text-sm"></i>
                                </button>
                                <form method="POST" action="{{ route('patients.destroy', $patient) }}" class="inline row-action"
                                      onsubmit="event.stopPropagation(); return confirm('Delete {{ addslashes($patient->name) }}\'s record?')"
                                      onclick="event.stopPropagation()">
                                    @csrf @method('DELETE')
                                    <button type="submit" onclick="event.stopPropagation()"
                                            class="row-action w-8 h-8 rounded-lg flex items-center justify-center hover:bg-red-50 text-gray-400 hover:text-red-500 transition-colors">
                                        <i class="fa-solid fa-trash text-sm"></i>
                                    </button>
                                </form>
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
        <div class="px-6 py-3 border-t border-gray-100 bg-gray-50/50">
            {{ $patients->links() }}
        </div>
        @endif
    </div>
</div>

<!-- ── Add Patient Modal ── -->
<div id="addPatientModal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4 backdrop-blur-sm">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl max-h-[90vh] overflow-y-auto">
        <div class="relative bg-gradient-to-r from-brand-600 to-brand-700 px-6 py-5 text-white">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center">
                        <i class="fa-solid fa-user-plus text-white"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold">Add New Patient</h3>
                        <p class="text-xs text-white/80">Fill in the patient's information</p>
                    </div>
                </div>
                <button onclick="closeModal()" class="w-8 h-8 rounded-xl flex items-center justify-center text-white/80 hover:text-white hover:bg-white/20">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
        </div>

        <form method="POST" action="{{ route('patients.store') }}" class="px-6 py-5 space-y-5">
            @csrf

            <div class="border-l-4 border-blue-400 bg-blue-50/30 rounded-r-xl p-4 space-y-3">
                <p class="text-xs font-bold text-blue-700 uppercase tracking-wider flex items-center gap-2">
                    <i class="fa-solid fa-id-card"></i> Identity
                </p>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="label">Full Name <span class="text-red-500">*</span></label>
                        <input type="text" name="name" required value="{{ old('name') }}" class="input" placeholder="Juan Dela Cruz">
                    </div>
                    <div>
                        <label class="label">Patient ID <span class="text-red-500">*</span></label>
                        <input type="text" name="patient_id" required value="{{ old('patient_id') }}" placeholder="P-2024-009" class="input">
                    </div>
                </div>
            </div>

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
                    <input type="text" name="address" value="{{ old('address') }}" class="input" placeholder="Street, Barangay, City">
                </div>
            </div>

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

            <div class="border-l-4 border-amber-400 bg-amber-50/30 rounded-r-xl p-4 space-y-3">
                <p class="text-xs font-bold text-amber-700 uppercase tracking-wider flex items-center gap-2">
                    <i class="fa-solid fa-notes-medical"></i> Medical Notes
                </p>
                <textarea name="medical_history" rows="3" class="input resize-none" placeholder="Allergies, conditions, medications, family history…">{{ old('medical_history') }}</textarea>
            </div>

            <div class="flex justify-between items-center gap-3 pt-3 border-t border-gray-100">
                <button type="button" onclick="closeModal()" class="btn-secondary">
                    <i class="fa-solid fa-xmark"></i> Cancel
                </button>
                <button type="submit" class="btn-primary">
                    <i class="fa-solid fa-floppy-disk"></i> Save Patient
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
function openModal()  { document.getElementById('addPatientModal').classList.remove('hidden'); document.body.style.overflow='hidden'; }
function closeModal() { document.getElementById('addPatientModal').classList.add('hidden'); document.body.style.overflow=''; }
document.getElementById('addPatientModal').addEventListener('click', e => { if(e.target===e.currentTarget) closeModal(); });
document.addEventListener('keydown', e => { if(e.key==='Escape') closeModal(); });

async function togglePin(id, btn) {
    const originalHTML = btn.innerHTML;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin text-sm"></i>';
    btn.disabled = true;
    try {
        const r = await fetch(`/patients/${id}/pin`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            }
        });
        if (!r.ok) throw new Error('HTTP ' + r.status);
        await r.json();
        location.reload();
    } catch (err) {
        btn.innerHTML = originalHTML;
        btn.disabled = false;
        alert('Could not toggle pin: ' + err.message);
    }
}

@if($errors->any()) openModal(); @endif
</script>
@endpush
@endsection
