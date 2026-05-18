@extends('layouts.app')
@section('title', "Today's Queue")
@section('page-title', "Today's Queue")

@section('content')
@php
    $me = Auth::user();
    $canCheckIn = $me->can_('visits.checkin');
    $canStatus = $me->can_('visits.status');
    $canCancel = $me->can_('visits.cancel');
    $canClaim = $me->can_('visits.claim');
    $canAssignAny = $me->can_('visits.assign_any');
    $isToday = $date->isToday();

    // Visible status pills + their counts (always all 6 so the pill row stays consistent).
    $statusOptions = [
        'waiting'     => ['Waiting',     'amber',   'fa-hourglass-half'],
        'with_nurse'  => ['With Nurse',  'cyan',    'fa-user-nurse'],
        'with_doctor' => ['With Doctor', 'blue',    'fa-user-doctor'],
        'pharmacy'    => ['Pharmacy',    'purple',  'fa-prescription-bottle-medical'],
        'completed'   => ['Completed',   'emerald', 'fa-circle-check'],
        'cancelled'   => ['Cancelled',   'gray',    'fa-circle-xmark'],
    ];
@endphp

<style>
.queue-card {
    background: #fff;
    border: 2px solid #e5e7eb;
    border-radius: 1.25rem;
    box-shadow: 0 1px 3px rgba(0,0,0,.05);
}
.dark .queue-card { background:#1a2438 !important; border-color:#2d3a52 !important; }

/* ── Queue table (same look-and-feel as Patient / Staff / Medicine tables) ── */
.queue-table thead th {
    padding: 0.95rem 1.25rem;
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
.queue-table thead th:last-child { border-right: none; }
.queue-table thead th:first-child { text-align: left; }
.dark .queue-table thead th {
    background: #0f1a2e !important; color: #cbd5e1 !important;
    border-bottom-color: #2d3a52 !important; border-right-color: #1f2c45 !important;
}
.queue-table tbody td {
    padding: 1rem 1.25rem;
    vertical-align: middle;
    border-right: 1px solid #f1f5f9;
}
.queue-table tbody td:last-child { border-right: none; }
.dark .queue-table tbody td { border-right-color: #1f2c45; }
.queue-table tbody tr {
    transition: background-color .12s;
    border-bottom: 1px solid #f1f5f9;
    cursor: pointer;
}
.dark .queue-table tbody tr { border-bottom-color:#1f2c45; }
.queue-table tbody tr:hover { background: #f0fdfa; }
.dark .queue-table tbody tr:hover { background: #1a2438 !important; }
.queue-table tbody tr:last-child { border-bottom: none; }

.queue-status-pill {
    display: inline-flex; align-items: center; gap: .45rem;
    padding: .4rem .9rem;
    border-radius: 9999px;
    font-size: .78rem;
    font-weight: 800;
    border: 1.5px solid transparent;
    text-transform: uppercase;
    letter-spacing: .03em;
}

.q-btn {
    display: inline-flex; align-items: center; justify-content: center; gap: .45rem;
    padding: .6rem .9rem;
    border-radius: .75rem;
    font-size: .82rem;
    font-weight: 700;
    transition: background-color .12s, box-shadow .12s;
    white-space: nowrap;
}
.q-btn-next          { background:#0d9488; color:#fff; box-shadow: 0 2px 8px rgba(13,148,136,.3); }
.q-btn-next:hover    { background:#0f766e; }
.q-btn-complete      { background:#d1fae5; color:#065f46; }
.q-btn-complete:hover { background:#a7f3d0; }
.dark .q-btn-complete      { background: rgba(16,185,129,.2); color:#6ee7b7; }
.dark .q-btn-complete:hover{ background: rgba(16,185,129,.3); }
.q-btn-cancel        { background:#fee2e2; color:#b91c1c; }
.q-btn-cancel:hover  { background:#fecaca; }
.dark .q-btn-cancel        { background: rgba(239,68,68,.18); color:#fca5a5; }
.dark .q-btn-cancel:hover  { background: rgba(239,68,68,.28); }

.filter-pill {
    display: inline-flex; align-items: center; gap: .5rem;
    padding: .55rem 1rem;
    border-radius: .75rem;
    font-size: .85rem;
    font-weight: 700;
    border: 2px solid #e5e7eb;
    background: #fff;
    color: #475569;
    transition: all .12s;
}
.filter-pill:hover { border-color: #cbd5e1; }
.filter-pill.is-active {
    background: #0d9488; color: #fff; border-color: #0d9488;
    box-shadow: 0 4px 12px rgba(13,148,136,.3);
}
.dark .filter-pill { background:#1a2438; border-color:#2d3a52; color:#cbd5e1; }
.dark .filter-pill:hover { border-color:#3a4a66; }
.dark .filter-pill.is-active { background:#0d9488; color:#fff; border-color:#0d9488; }
</style>

<div class="space-y-5">

    {{-- Hero banner — same in-gradient design as Patients/Staff index --}}
    <div class="rounded-2xl overflow-hidden bg-gradient-to-r from-brand-600 via-teal-600 to-emerald-700 text-white shadow-md relative">
        <div class="absolute inset-0 opacity-20 pointer-events-none" style="background-image: radial-gradient(circle at 18% 30%, rgba(255,255,255,.55) 0, transparent 35%), radial-gradient(circle at 80% 75%, rgba(255,255,255,.35) 0, transparent 32%);"></div>
        <div class="relative px-5 sm:px-6 py-5 flex items-center justify-between gap-4 flex-wrap">
            <div class="flex items-center gap-4 min-w-0 flex-1">
                <div class="w-12 h-12 sm:w-14 sm:h-14 rounded-2xl bg-white/15 backdrop-blur-sm ring-1 ring-white/25 flex items-center justify-center flex-shrink-0">
                    <i class="fa-solid fa-clipboard-list text-xl sm:text-2xl"></i>
                </div>
                <div class="min-w-0">
                    <h1 class="text-xl sm:text-2xl font-extrabold leading-tight">Today's Queue</h1>
                    <p class="text-white/85 text-sm mt-0.5">
                        {{ $date->format('l, F j, Y') }}
                        @if(!$isToday)
                            &mdash; <a href="{{ route('visits.index') }}" class="underline font-semibold">Back to today</a>
                        @endif
                        &bull; <span class="font-bold">{{ $visits->count() }}</span> {{ $statusFilter ? Str::title(str_replace('_', ' ', $statusFilter)) : 'visit'.($visits->count() === 1 ? '' : 's') }}
                    </p>
                </div>
            </div>
            @if($canCheckIn)
            <button onclick="openCheckInModal()"
                    class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-white text-teal-700 hover:bg-teal-50 text-sm font-bold transition-colors shadow-sm w-full sm:w-auto flex-shrink-0">
                <i class="fa-solid fa-user-plus"></i> Check In Patient
            </button>
            @endif
        </div>
    </div>

    {{-- Status filter pills --}}
    <div class="flex flex-wrap gap-2">
        <a href="{{ route('visits.index', ['date' => $date->toDateString()]) }}"
           class="filter-pill {{ !$statusFilter ? 'is-active' : '' }}">
            <i class="fa-solid fa-list"></i>
            All
            <span class="text-[10px] font-bold {{ !$statusFilter ? 'text-white/80' : 'text-gray-400 dark:text-gray-500' }}">
                {{ array_sum($counts) }}
            </span>
        </a>
        @foreach($statusOptions as $key => [$label, $hue, $icon])
        <a href="{{ route('visits.index', ['date' => $date->toDateString(), 'status' => $key]) }}"
           class="filter-pill {{ $statusFilter === $key ? 'is-active' : '' }}">
            <i class="fa-solid {{ $icon }}"></i>
            {{ $label }}
            <span class="text-[10px] font-bold {{ $statusFilter === $key ? 'text-white/80' : 'text-gray-400 dark:text-gray-500' }}">
                {{ $counts[$key] ?? 0 }}
            </span>
        </a>
        @endforeach
    </div>

    {{-- Queue table — whole row opens the patient record; Advance/Cancel use
         .row-action to stop the click from bubbling. On phones the responsive-table
         CSS stacks each row as a card with labels. --}}
    <div class="queue-card overflow-hidden -mx-4 md:mx-0 rounded-none md:rounded-2xl border-x-0 md:border-x-2">
        <div class="overflow-x-auto">
            <table class="min-w-full queue-table responsive-table">
                <thead>
                    <tr>
                        <th>Patient</th>
                        <th>Patient ID</th>
                        <th>Reason</th>
                        <th>Checked In</th>
                        <th>Status</th>
                        <th style="min-width: 260px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($visits as $v)
                @php
                    [$statusLabel, $statusHue, $statusIcon] = $statusOptions[$v->status] ?? ['Unknown', 'slate', 'fa-circle-question'];
                    // New patient = this is their only visit ever (counting today's).
                    $isNew = $v->patient && $v->patient->visits()->count() <= 1;
                    $canOpenPatient = $me->can_('patients.view') && $v->patient_id;
                    $isFinished = in_array($v->status, ['completed', 'cancelled']);

                    // Linear queue flow. "Next" advances to the next logical stage so
                    // the front desk doesn't have to think about which stage to pick.
                    $linearNext = match($v->status) {
                        'waiting'     => ['with_nurse',  'With Nurse',  'fa-user-nurse'],
                        'with_nurse'  => ['with_doctor', 'With Doctor', 'fa-user-doctor'],
                        'with_doctor' => ['pharmacy',    'Pharmacy',    'fa-prescription-bottle-medical'],
                        'pharmacy'    => ['completed',   'Complete',    'fa-circle-check'],
                        default       => null,
                    };
                    // Show "Skip to Complete" alongside Next when the patient could go
                    // straight from doctor → done (no prescription needed).
                    $showCompleteShortcut = $v->status === 'with_doctor';
                @endphp
                <tr class="group"
                    @if($canOpenPatient)
                        data-href="{{ route('patients.show', $v->patient_id) }}"
                        onclick="if(!event.target.closest('.row-action')) window.location=this.dataset.href"
                    @endif>
                    <td class="cell-primary">
                        <div class="flex items-center gap-3">
                            <div class="h-12 w-12 rounded-full bg-gradient-to-br from-blue-400 to-indigo-600 flex items-center justify-center text-white text-base font-bold flex-shrink-0 shadow-sm">
                                {{ strtoupper(substr($v->patient->name ?? '?', 0, 2)) }}
                            </div>
                            <div class="min-w-0">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <p class="font-bold text-gray-900 dark:text-white text-base group-hover:text-brand-700 dark:group-hover:text-brand-300 transition-colors truncate">{{ $v->patient->name ?? 'Unknown patient' }}</p>
                                    @if($isNew)
                                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-300 uppercase tracking-wider">New</span>
                                    @else
                                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 uppercase tracking-wider">Returning</span>
                                    @endif
                                    @if($v->visit_type === 'appointment')
                                    <span class="inline-flex items-center gap-1 text-[10px] font-bold px-2 py-0.5 rounded-full bg-indigo-100 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300 uppercase tracking-wider" title="Scheduled appointment">
                                        <i class="fa-solid fa-calendar-check text-[9px]"></i> Appt
                                    </span>
                                    @else
                                    <span class="inline-flex items-center gap-1 text-[10px] font-bold px-2 py-0.5 rounded-full bg-orange-100 dark:bg-orange-900/30 text-orange-700 dark:text-orange-300 uppercase tracking-wider" title="Walk-in (no appointment)">
                                        <i class="fa-solid fa-person-walking text-[9px]"></i> Walk-in
                                    </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </td>
                    <td class="text-center" data-label="Patient ID">
                        <span class="patient-id-text">{{ $v->patient->patient_id ?? '—' }}</span>
                    </td>
                    <td class="text-center" data-label="Reason">
                        @if($v->reason)
                            <span class="text-sm text-gray-700 dark:text-gray-200">{{ $v->reason }}</span>
                        @else
                            <span class="text-sm text-gray-400 dark:text-gray-500 italic">—</span>
                        @endif
                    </td>
                    <td class="text-center" data-label="Checked In">
                        <p class="text-sm font-semibold text-gray-800 dark:text-gray-100">{{ $v->checked_in_at->format('g:i A') }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ $v->checked_in_at->diffForHumans() }}</p>
                        @if($v->currentStaff)
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">with {{ $v->currentStaff->name }}</p>
                        @endif
                    </td>
                    <td class="text-center" data-label="Status">
                        <span class="queue-status-pill bg-{{ $statusHue }}-100 dark:bg-{{ $statusHue }}-900/35 text-{{ $statusHue }}-700 dark:text-{{ $statusHue }}-300 border-{{ $statusHue }}-200 dark:border-{{ $statusHue }}-800/60">
                            <i class="fa-solid {{ $statusIcon }}"></i> {{ $statusLabel }}
                        </span>
                    </td>
                    <td class="cell-actions">
                        <div class="flex flex-col gap-2 row-action w-full">
                            <div class="flex items-center gap-2 flex-wrap">
                                @if($canStatus && $v->isActive() && $isToday)
                                <form method="POST" action="{{ route('visits.status', $v) }}" class="row-action flex-1 min-w-0"
                                      id="statusForm-{{ $v->id }}"
                                      onclick="event.stopPropagation()">
                                    @csrf @method('PUT')
                                    <select name="status"
                                            onchange="if(confirm('Move {{ addslashes($v->patient->name ?? 'this patient') }} to '+this.options[this.selectedIndex].text+'?')) this.form.submit(); else this.value='{{ $v->status }}';"
                                            onclick="event.stopPropagation()"
                                            class="input cs-select font-bold text-sm py-2.5 w-full">
                                        <option value="{{ $v->status }}" selected disabled>Move to…</option>
                                        @foreach(['waiting','with_nurse','with_doctor','pharmacy','completed','cancelled'] as $opt)
                                            @if($opt !== $v->status)
                                            <option value="{{ $opt }}">{{ $statusOptions[$opt][0] }}</option>
                                            @endif
                                        @endforeach
                                    </select>
                                </form>
                                @endif

                                @if($canCancel && $isFinished && $isToday)
                                <form method="POST" action="{{ route('visits.destroy', $v) }}" class="inline row-action"
                                      onsubmit="event.stopPropagation(); return confirm('Remove this visit from the queue?');"
                                      onclick="event.stopPropagation()">
                                    @csrf @method('DELETE')
                                    <button type="submit" onclick="event.stopPropagation()" class="q-btn q-btn-cancel" title="Remove visit">
                                        <i class="fa-solid fa-trash"></i> Remove
                                    </button>
                                </form>
                                @elseif($canCancel && $v->isActive() && $isToday)
                                <form method="POST" action="{{ route('visits.destroy', $v) }}" class="inline row-action"
                                      onsubmit="event.stopPropagation(); return confirm('Cancel this visit?');"
                                      onclick="event.stopPropagation()">
                                    @csrf @method('DELETE')
                                    <button type="submit" onclick="event.stopPropagation()" class="q-btn q-btn-cancel" title="Cancel visit">
                                        <i class="fa-solid fa-xmark"></i> Cancel
                                    </button>
                                </form>
                                @endif
                            </div>

                            @if($v->isActive() && $isToday && ($canClaim || $canAssignAny))
                            <div class="flex items-center gap-2 flex-wrap text-xs">
                                @if($canClaim && (int)$v->current_staff_id !== (int)$me->id)
                                <form method="POST" action="{{ route('visits.assign', $v) }}" class="inline row-action"
                                      onclick="event.stopPropagation()"
                                      onsubmit="event.stopPropagation()">
                                    @csrf @method('PUT')
                                    <input type="hidden" name="claim" value="1">
                                    <button type="submit" class="q-btn" style="background:#ecfeff;color:#0e7490;" title="Take this patient">
                                        <i class="fa-solid fa-hand-pointer"></i> Take
                                    </button>
                                </form>
                                @elseif($canClaim && (int)$v->current_staff_id === (int)$me->id)
                                <span class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg bg-teal-50 text-teal-700 font-semibold">
                                    <i class="fa-solid fa-circle-check"></i> Yours
                                </span>
                                @endif

                                @if($canAssignAny)
                                <form method="POST" action="{{ route('visits.assign', $v) }}" class="row-action flex-1 min-w-[180px]"
                                      onclick="event.stopPropagation()"
                                      onsubmit="event.stopPropagation()">
                                    @csrf @method('PUT')
                                    <select name="staff_id"
                                            onchange="if(confirm('Assign {{ addslashes($v->patient->name ?? 'this patient') }} to '+(this.options[this.selectedIndex].text)+'?')) this.form.submit(); else this.value='{{ $v->current_staff_id }}';"
                                            onclick="event.stopPropagation()"
                                            class="input cs-select text-sm py-2 w-full">
                                        <option value="">Unassigned</option>
                                        @foreach($clinicalStaff as $s)
                                        <option value="{{ $s->id }}" @selected((int)$v->current_staff_id === (int)$s->id)>
                                            {{ $s->name }} ({{ Str::title(str_replace('_',' ',$s->role)) }})
                                        </option>
                                        @endforeach
                                    </select>
                                </form>
                                @endif
                            </div>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="py-16 text-center">
                        <div class="w-16 h-16 bg-blue-50 dark:bg-slate-800 rounded-2xl flex items-center justify-center mx-auto mb-3">
                            <i class="fa-solid fa-clipboard-list text-blue-400 dark:text-gray-500 text-2xl"></i>
                        </div>
                        <p class="text-gray-500 dark:text-gray-400 font-medium">
                            @if($statusFilter)
                                No {{ str_replace('_', ' ', $statusFilter) }} visits {{ $isToday ? 'right now' : 'on this day' }}
                            @else
                                No visits {{ $isToday ? 'yet today' : 'on this day' }}
                            @endif
                        </p>
                        @if($canCheckIn && $isToday)
                        <button onclick="openCheckInModal()" class="text-blue-600 dark:text-blue-300 hover:underline text-sm mt-1 font-semibold">
                            Check in the first patient
                        </button>
                        @endif
                    </td>
                </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Check-In Modal --}}
@if($canCheckIn)
<div id="checkInModal" class="hidden fixed inset-0 bg-black/60 z-50 flex items-center justify-center p-4 backdrop-blur-sm">
    <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-2xl w-full max-w-md border-2 border-gray-100 dark:border-slate-700 overflow-hidden">
        <div class="bg-gradient-to-r from-brand-600 to-brand-700 px-6 py-5 text-white flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-11 h-11 rounded-2xl bg-white/15 flex items-center justify-center backdrop-blur-sm">
                    <i class="fa-solid fa-user-plus text-xl"></i>
                </div>
                <div>
                    <h3 class="text-lg font-bold">Check In Patient</h3>
                    <p class="text-xs text-white/80 mt-0.5">Add to today's queue</p>
                </div>
            </div>
            <button type="button" onclick="closeCheckInModal()" class="w-9 h-9 rounded-xl flex items-center justify-center text-white/80 hover:text-white hover:bg-white/20 transition-colors">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
        <form method="POST" action="{{ route('visits.store') }}" class="px-6 py-5 space-y-4">
            @csrf

            {{-- Mode toggle: pick existing patient or register a new walk-in inline.
                 The "new" mode captures only the minimum the front desk needs to seat
                 them in the queue — full record can be filled later from /patients. --}}
            <div>
                <label class="label">Patient</label>
                <div class="grid grid-cols-2 gap-2">
                    <label class="relative flex items-center justify-center gap-2 p-3 rounded-xl border-2 border-gray-200 dark:border-slate-700 cursor-pointer has-[:checked]:border-brand-500 has-[:checked]:bg-brand-50 dark:has-[:checked]:bg-brand-900/20 transition-colors">
                        <input type="radio" name="patient_mode" value="existing" checked class="sr-only" onchange="setCheckInMode('existing')">
                        <i class="fa-solid fa-user-check text-brand-500"></i>
                        <span class="text-sm font-bold text-gray-900 dark:text-white">Existing</span>
                    </label>
                    <label class="relative flex items-center justify-center gap-2 p-3 rounded-xl border-2 border-gray-200 dark:border-slate-700 cursor-pointer has-[:checked]:border-brand-500 has-[:checked]:bg-brand-50 dark:has-[:checked]:bg-brand-900/20 transition-colors">
                        <input type="radio" name="patient_mode" value="new" class="sr-only" onchange="setCheckInMode('new')">
                        <i class="fa-solid fa-user-plus text-brand-500"></i>
                        <span class="text-sm font-bold text-gray-900 dark:text-white">New walk-in</span>
                    </label>
                </div>
            </div>

            {{-- Existing-patient picker (searchable by name OR patient ID) --}}
            <div id="checkInExistingPane">
                <label class="label">Select patient <span class="text-red-500">*</span></label>
                <select name="patient_id" id="checkInPatientId" class="input cs-select"
                        data-searchable="true" data-search-placeholder="Search by name or patient ID…">
                    <option value="">Select a patient…</option>
                    @foreach($patients as $p)
                        <option value="{{ $p->id }}">{{ $p->name }} — {{ $p->patient_id }}</option>
                    @endforeach
                </select>
            </div>

            {{-- New-patient quick capture (hidden by default) --}}
            <div id="checkInNewPane" class="hidden space-y-3 p-3 rounded-xl bg-brand-50/40 dark:bg-brand-900/15 border-2 border-brand-200/60 dark:border-brand-800/40">
                <p class="text-xs font-bold text-brand-700 dark:text-brand-300 uppercase tracking-wider flex items-center gap-1.5">
                    <i class="fa-solid fa-bolt"></i> Quick register
                    <span class="text-gray-400 dark:text-gray-500 normal-case font-normal">— full record can be completed later</span>
                </p>
                <div>
                    <label class="label">Full Name <span class="text-red-500">*</span></label>
                    <input type="text" name="new_patient_name" id="newPatientName" class="input" placeholder="e.g. Juan Dela Cruz">
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="label">Date of Birth</label>
                        <input type="date" name="new_patient_date_of_birth" class="input">
                    </div>
                    <div>
                        <label class="label">Sex</label>
                        <select name="new_patient_sex" class="input cs-select">
                            <option value="">—</option>
                            <option value="male">Male</option>
                            <option value="female">Female</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                </div>
                <div>
                    <label class="label">Phone <span class="text-gray-400 text-xs font-normal">(optional)</span></label>
                    <input type="tel" name="new_patient_phone" class="input" placeholder="09XX-XXX-XXXX">
                </div>
            </div>

            <div>
                <label class="label">Visit Type <span class="text-red-500">*</span></label>
                <div class="grid grid-cols-2 gap-2">
                    <label class="relative flex flex-col items-start gap-1 p-3 rounded-xl border-2 border-gray-200 dark:border-slate-700 cursor-pointer has-[:checked]:border-brand-500 has-[:checked]:bg-brand-50 dark:has-[:checked]:bg-brand-900/20 transition-colors">
                        <input type="radio" name="visit_type" value="walk_in" checked class="sr-only">
                        <span class="flex items-center gap-2 text-sm font-bold text-gray-900 dark:text-white">
                            <i class="fa-solid fa-person-walking text-brand-500"></i> Walk-in
                        </span>
                        <span class="text-xs text-gray-500 dark:text-gray-400">No appointment</span>
                    </label>
                    <label class="relative flex flex-col items-start gap-1 p-3 rounded-xl border-2 border-gray-200 dark:border-slate-700 cursor-pointer has-[:checked]:border-brand-500 has-[:checked]:bg-brand-50 dark:has-[:checked]:bg-brand-900/20 transition-colors">
                        <input type="radio" name="visit_type" value="appointment" class="sr-only">
                        <span class="flex items-center gap-2 text-sm font-bold text-gray-900 dark:text-white">
                            <i class="fa-solid fa-calendar-check text-brand-500"></i> Appointment
                        </span>
                        <span class="text-xs text-gray-500 dark:text-gray-400">Scheduled visit</span>
                    </label>
                </div>
            </div>
            <div>
                <label class="label">Reason for visit <span class="text-gray-400 text-xs font-normal">(optional)</span></label>
                <input type="text" name="reason" maxlength="255" placeholder="e.g. Follow-up, Fever, Rash check" class="input">
            </div>
            <div class="flex justify-between gap-3 pt-3 border-t border-gray-100 dark:border-slate-700">
                <button type="button" onclick="closeCheckInModal()" class="btn-secondary">Cancel</button>
                <button type="submit" class="btn-primary"><i class="fa-solid fa-check"></i> Check In</button>
            </div>
        </form>
    </div>
</div>
@endif

@push('scripts')
<script>
function openCheckInModal()  { document.getElementById('checkInModal').classList.remove('hidden'); document.body.style.overflow = 'hidden'; }
function closeCheckInModal() { document.getElementById('checkInModal').classList.add('hidden'); document.body.style.overflow = ''; }
document.addEventListener('keydown', e => { if (e.key === 'Escape') closeCheckInModal(); });

// Toggle between picking an existing patient and quick-registering a brand new
// walk-in. Required-ness flips so the form only validates the visible side.
function setCheckInMode(mode) {
    const exPane  = document.getElementById('checkInExistingPane');
    const newPane = document.getElementById('checkInNewPane');
    const exId    = document.getElementById('checkInPatientId');
    const newName = document.getElementById('newPatientName');
    if (mode === 'new') {
        exPane.classList.add('hidden');
        newPane.classList.remove('hidden');
        exId.required  = false; exId.value = '';
        newName.required = true;
    } else {
        exPane.classList.remove('hidden');
        newPane.classList.add('hidden');
        exId.required  = true;
        newName.required = false; newName.value = '';
    }
}

// Dashboard quick action / Today's Queue header links use #checkin to deep-link
// the modal open. Strip the hash after so refresh doesn't keep re-opening it.
if (location.hash === '#checkin') {
    openCheckInModal();
    history.replaceState(null, '', location.pathname + location.search);
}
</script>
@endpush
@endsection
