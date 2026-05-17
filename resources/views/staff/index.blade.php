@extends('layouts.app')
@section('title', 'Staff')
@section('page-title', 'Staff Directory')

@section('content')
@php
    $isAdmin = Auth::user()->role === 'admin';
    // Role styling — light + dark variants used inline across the page
    $roleColors = [
        'admin'       => ['bg'=>'bg-slate-100',   'darkBg'=>'dark:bg-slate-800/60',  'text'=>'text-slate-700',   'darkText'=>'dark:text-slate-200',   'border'=>'border-slate-200',   'darkBorder'=>'dark:border-slate-700',     'grad'=>'from-slate-500 to-slate-700',    'icon'=>'fa-user-shield',                'label'=>'Admin'],
        'clinic_head' => ['bg'=>'bg-purple-100',  'darkBg'=>'dark:bg-purple-900/35', 'text'=>'text-purple-700',  'darkText'=>'dark:text-purple-300',  'border'=>'border-purple-200', 'darkBorder'=>'dark:border-purple-800/60',  'grad'=>'from-purple-500 to-purple-700',  'icon'=>'fa-user-tie',                   'label'=>'Clinic Head'],
        'doctor'      => ['bg'=>'bg-blue-100',    'darkBg'=>'dark:bg-blue-900/35',   'text'=>'text-blue-700',    'darkText'=>'dark:text-blue-300',    'border'=>'border-blue-200',   'darkBorder'=>'dark:border-blue-800/60',    'grad'=>'from-blue-500 to-blue-700',      'icon'=>'fa-user-doctor',                'label'=>'Doctor'],
        'pharmacist'  => ['bg'=>'bg-green-100',   'darkBg'=>'dark:bg-green-900/35',  'text'=>'text-green-700',   'darkText'=>'dark:text-green-300',   'border'=>'border-green-200',  'darkBorder'=>'dark:border-green-800/60',   'grad'=>'from-green-500 to-green-700',    'icon'=>'fa-prescription-bottle-medical','label'=>'Pharmacist'],
        'nurse'       => ['bg'=>'bg-cyan-100',    'darkBg'=>'dark:bg-cyan-900/35',   'text'=>'text-teal-700',    'darkText'=>'dark:text-teal-300',    'border'=>'border-cyan-200',   'darkBorder'=>'dark:border-cyan-800/60',    'grad'=>'from-cyan-500 to-teal-600',      'icon'=>'fa-user-nurse',                 'label'=>'Nurse'],
        'secretary'   => ['bg'=>'bg-amber-100',   'darkBg'=>'dark:bg-amber-900/35',  'text'=>'text-amber-700',   'darkText'=>'dark:text-amber-300',   'border'=>'border-amber-200',  'darkBorder'=>'dark:border-amber-800/60',   'grad'=>'from-amber-400 to-amber-600',    'icon'=>'fa-id-badge',                   'label'=>'Secretary'],
        'assistant'   => ['bg'=>'bg-emerald-100', 'darkBg'=>'dark:bg-emerald-900/35','text'=>'text-emerald-700', 'darkText'=>'dark:text-emerald-300', 'border'=>'border-emerald-200','darkBorder'=>'dark:border-emerald-800/60', 'grad'=>'from-emerald-400 to-emerald-600','icon'=>'fa-user',                       'label'=>'Assistant'],
    ];
@endphp

@push('head')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr@4/dist/flatpickr.min.css">
<style>
/* ── Flatpickr brand theme (same look as Add Medicine) ── */
.flatpickr-calendar {
    border-radius: 1rem !important;
    border: 2px solid #e5e7eb !important;
    box-shadow: 0 10px 30px rgba(0,0,0,.14) !important;
    font-family: inherit !important;
    padding: 8px !important;
    width: 22rem !important;
}
.flatpickr-innerContainer, .flatpickr-rContainer, .dayContainer { width: 100% !important; }
.dayContainer { min-width: 100% !important; max-width: 100% !important; }
.flatpickr-days { width: 100% !important; }
.flatpickr-monthDropdown-months {
    appearance: none !important; -webkit-appearance: none !important;
    background: #f1f5f9 !important; color: #0f172a !important;
    border: 1.5px solid #e5e7eb !important; border-radius: .55rem !important;
    padding: .25rem .6rem !important; font-weight: 700 !important; font-size: .9rem !important;
    cursor: pointer; transition: background .12s, border-color .12s;
}
.flatpickr-monthDropdown-months:hover { background: #ecfdf5 !important; border-color: #14b8a6 !important; }
.numInputWrapper input.cur-year {
    background: #f1f5f9 !important; border: 1.5px solid #e5e7eb !important;
    border-radius: .55rem !important; padding: .25rem .35rem !important;
    font-weight: 700 !important; font-size: .9rem !important; color: #0f172a !important;
}
.dark .flatpickr-monthDropdown-months, .dark .numInputWrapper input.cur-year {
    background: #243050 !important; border-color: #3f4d6b !important; color: #f1f5f9 !important;
}
.dark .flatpickr-monthDropdown-months:hover { background: rgba(20,184,166,.15) !important; border-color: #14b8a6 !important; }
.flatpickr-months { padding-top: 4px !important; }
.flatpickr-month { color: #0f172a !important; height: 38px !important; }
.flatpickr-current-month { font-weight: 700 !important; font-size: .95rem !important; padding-top: 6px !important; }
.flatpickr-weekday { color: #6b7280 !important; font-weight: 700 !important; font-size: .72rem !important; text-transform: uppercase; letter-spacing: .04em; }
.flatpickr-day { border-radius: .65rem !important; color: #1f2937 !important; font-weight: 500 !important; transition: background .12s, color .12s; }
.flatpickr-day:hover, .flatpickr-day.prevMonthDay:hover, .flatpickr-day.nextMonthDay:hover {
    background: #ecfdf5 !important; border-color: transparent !important; color: #065f46 !important;
}
.flatpickr-day.today { border-color: #0d9488 !important; color: #0d9488 !important; font-weight: 700 !important; }
.flatpickr-day.selected, .flatpickr-day.selected:hover, .flatpickr-day.selected.today {
    background: #0d9488 !important; border-color: #0d9488 !important; color: #fff !important;
    box-shadow: 0 2px 8px rgba(13,148,136,.35) !important;
}
.flatpickr-day.flatpickr-disabled, .flatpickr-day.flatpickr-disabled:hover { color: #cbd5e1 !important; background: transparent !important; }
.flatpickr-day.prevMonthDay, .flatpickr-day.nextMonthDay { color: #cbd5e1 !important; }
.flatpickr-prev-month, .flatpickr-next-month { color: #6b7280 !important; fill: #6b7280 !important; padding: 8px !important; }
.flatpickr-prev-month:hover svg, .flatpickr-next-month:hover svg { fill: #0d9488 !important; }
.dark .flatpickr-calendar { background: #1a2438 !important; border-color: #2d3a52 !important; box-shadow: 0 10px 30px rgba(0,0,0,.5) !important; }
.dark .flatpickr-month, .dark .flatpickr-current-month,
.dark .flatpickr-current-month .flatpickr-monthDropdown-months,
.dark .flatpickr-current-month input.cur-year { color: #f1f5f9 !important; }
.dark .flatpickr-weekday { color: #94a3b8 !important; }
.dark .flatpickr-day { color: #e2e8f0 !important; }
.dark .flatpickr-day:hover { background: rgba(20,184,166,.15) !important; color: #6ee7b7 !important; }
.dark .flatpickr-day.today { border-color: #14b8a6 !important; color: #6ee7b7 !important; }
.dark .flatpickr-day.selected, .dark .flatpickr-day.selected:hover {
    background: #14b8a6 !important; border-color: #14b8a6 !important; color: #042f2e !important;
}
.dark .flatpickr-day.prevMonthDay, .dark .flatpickr-day.nextMonthDay,
.dark .flatpickr-day.flatpickr-disabled { color: #475569 !important; }
.dark .flatpickr-prev-month, .dark .flatpickr-next-month { color: #94a3b8 !important; fill: #94a3b8 !important; }

.staff-card {
    background: #fff;
    border: 2px solid #e5e7eb;
    border-radius: 1.25rem;
    box-shadow: 0 1px 3px rgba(0,0,0,.05);
    /* No overflow:hidden here — apply explicitly on the table card so dropdowns from the filter form aren't clipped */
}
.dark .staff-card { background:#1a2438 !important; border-color:#2d3a52 !important; }

.staff-table thead th {
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
.staff-table thead th:last-child { border-right: none; }
.staff-table thead th:first-child { text-align: center; }
.dark .staff-table thead th {
    background: #0f1a2e !important;
    color: #cbd5e1 !important;
    border-bottom-color: #2d3a52 !important;
    border-right-color: #1f2c45 !important;
}

.staff-table tbody td {
    padding: 1rem 1.5rem;
    vertical-align: middle;
    border-right: 1px solid #f1f5f9;
}
.staff-table tbody td:last-child { border-right: none; }
.dark .staff-table tbody td { border-right-color: #1f2c45; }

.staff-table tbody tr {
    transition: background-color .12s;
    border-bottom: 1px solid #f1f5f9;
    cursor: pointer;
}
.dark .staff-table tbody tr { border-bottom-color:#1f2c45; }
.staff-table tbody tr:hover { background: #f8fafc; }
.dark .staff-table tbody tr:hover { background: #1a2438 !important; }
.staff-table tbody tr:last-child { border-bottom: none; }

/* Inline meta — same size & weight as the main text so vision-impaired staff
   aren't squinting at a tiny secondary line. Sits beside the main label, not below. */
.staff-meta { color: #64748b; font-weight: 500; }
.dark .staff-meta { color: #94a3b8; }

.shift-chip {
    display: inline-flex; align-items: center; gap: .35rem;
    padding: .3rem .65rem;
    border-radius: 9999px;
    font-size: .7rem;
    font-weight: 700;
    letter-spacing: .02em;
}
.shift-morning   { background: #fef3c7; color: #92400e; }
.shift-afternoon { background: #ffedd5; color: #9a3412; }
.shift-night     { background: #e0e7ff; color: #3730a3; }
.shift-on_call   { background: #f3e8ff; color: #6b21a8; }
.dark .shift-morning   { background: rgba(245,158,11,.2); color: #fcd34d; }
.dark .shift-afternoon { background: rgba(249,115,22,.2); color: #fdba74; }
.dark .shift-night     { background: rgba(79,70,229,.25); color: #a5b4fc; }
.dark .shift-on_call   { background: rgba(168,85,247,.2); color: #d8b4fe; }

.role-pill {
    display: inline-flex; align-items: center; gap: .4rem;
    padding: .3rem .75rem;
    border-radius: 9999px;
    font-size: .72rem;
    font-weight: 700;
    border: 1.5px solid transparent;
}

.btn-row-primary {
    display:inline-flex; align-items:center; justify-content:center; gap:.5rem;
    padding: 0.625rem 0.75rem;       /* py-2.5 px-3 */
    border-radius: 0.75rem;          /* rounded-xl */
    font-size: 0.875rem;             /* text-sm */
    font-weight: 600;
    flex: 1;
    background:#6366f1; color:#fff;
    transition:background .12s, box-shadow .12s;
    box-shadow: 0 2px 6px rgba(99,102,241,.3);
}
.btn-row-primary:hover { background:#4f46e5; box-shadow: 0 4px 10px rgba(99,102,241,.45); }
.btn-row-secondary {
    display:inline-flex; align-items:center; justify-content:center; gap:.5rem;
    padding: 0.625rem 0.75rem;
    border-radius: 0.75rem;
    font-size: 0.875rem;
    font-weight: 600;
    flex: 1;
    background:#ede9fe; color:#6b21a8;
    transition: background .12s;
}
.btn-row-secondary:hover { background:#ddd6fe; }
.dark .btn-row-secondary { background: rgba(168,85,247,.18); color:#d8b4fe; }
.dark .btn-row-secondary:hover { background: rgba(168,85,247,.28); }

.btn-row-delete {
    display:inline-flex; align-items:center; justify-content:center; gap:.5rem;
    padding: 0.7rem 1.1rem;
    border-radius: 0.75rem;
    font-size: 0.95rem;
    font-weight: 700;
    background:#fee2e2; color:#b91c1c;
    transition: background .12s;
}
.btn-row-delete:hover { background:#fecaca; }
.dark .btn-row-delete       { background: rgba(239,68,68,.18); color:#fca5a5; }
.dark .btn-row-delete:hover { background: rgba(239,68,68,.28); }
</style>
@endpush

<div class="space-y-5">

    <!-- Hero banner — gradient header card matching Staff Profile -->
    <div class="rounded-2xl overflow-hidden bg-gradient-to-r from-brand-600 via-teal-600 to-emerald-700 text-white shadow-md relative">
        <div class="absolute inset-0 opacity-20 pointer-events-none" style="background-image: radial-gradient(circle at 18% 30%, rgba(255,255,255,.55) 0, transparent 35%), radial-gradient(circle at 78% 75%, rgba(255,255,255,.35) 0, transparent 32%), radial-gradient(circle at 95% 20%, rgba(255,255,255,.25) 0, transparent 25%);"></div>
        <div class="relative px-5 sm:px-6 py-5 flex items-center justify-between gap-4 flex-wrap">
            <div class="flex items-center gap-4 min-w-0 flex-1">
                <div class="w-12 h-12 sm:w-14 sm:h-14 rounded-2xl bg-white/15 backdrop-blur-sm flex items-center justify-center flex-shrink-0 ring-1 ring-white/25">
                    <i class="fa-solid fa-user-group text-xl sm:text-2xl"></i>
                </div>
                <div class="min-w-0">
                    <h1 class="text-xl sm:text-2xl font-extrabold leading-tight">Staff Directory</h1>
                    <p class="text-white/80 text-sm mt-0.5">
                        <span class="font-bold">{{ $staff->total() }}</span> members &bull; {{ $isAdmin ? 'Manage staff and assign shifts' : 'View clinic staff and shifts' }}
                    </p>
                </div>
            </div>
            @if($isAdmin)
            <button onclick="openAddStaffModal()" class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-white text-teal-700 hover:bg-teal-50 text-sm font-bold transition-colors shadow-sm w-full sm:w-auto flex-shrink-0">
                <i class="fa-solid fa-user-plus"></i> Add Staff
            </button>
            @endif
        </div>
    </div>

    <!-- Search + filter -->
    <form method="GET" action="{{ route('staff.index') }}" class="staff-card p-3">
        @php $hasFilters = request('role') || request('status') || request('sort') || request('direction'); @endphp
        <div class="flex items-center gap-2">
            <div class="relative">
                <button type="button" onclick="toggleDropdown('staffFilterMenu')"
                        class="h-12 px-4 bg-white dark:bg-slate-800 border-2 border-gray-200 dark:border-slate-600 rounded-xl hover:border-brand-400 dark:hover:border-brand-500 transition-colors flex items-center gap-2 text-sm text-gray-600 dark:text-gray-200 font-medium {{ $hasFilters ? 'border-brand-500 text-brand-700 dark:text-brand-300' : '' }}">
                    <i class="fa-solid fa-sliders text-sm"></i>
                    <span class="hidden sm:inline">Filter & Sort</span>
                    @if($hasFilters)
                    <span class="bg-brand-500 text-white text-[10px] font-bold rounded-full w-4 h-4 flex items-center justify-center">!</span>
                    @endif
                </button>
                <div id="staffFilterMenu" class="hidden absolute left-0 top-full mt-2 w-80 bg-white dark:bg-slate-800 border-2 border-gray-100 dark:border-slate-700 rounded-2xl shadow-xl p-4 space-y-4 z-30">
                    <div>
                        <p class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-2 flex items-center gap-1.5"><i class="fa-solid fa-filter"></i> Filter by</p>
                        <div class="space-y-2">
                            <select name="role" class="input cs-select">
                                <option value="">All Roles</option>
                                <option value="admin"       {{ request('role')==='admin'?'selected':'' }}>Admin</option>
                                <option value="clinic_head" {{ request('role')==='clinic_head'?'selected':'' }}>Clinic Head</option>
                                <option value="doctor"      {{ request('role')==='doctor'?'selected':'' }}>Doctor</option>
                                <option value="pharmacist"  {{ request('role')==='pharmacist'?'selected':'' }}>Pharmacist</option>
                                <option value="nurse"       {{ request('role')==='nurse'?'selected':'' }}>Nurse</option>
                                <option value="secretary"   {{ request('role')==='secretary'?'selected':'' }}>Secretary</option>
                                <option value="assistant"   {{ request('role')==='assistant'?'selected':'' }}>Assistant</option>
                            </select>
                            <select name="status" class="input cs-select">
                                <option value="">All Statuses</option>
                                <option value="online"  {{ request('status')==='online'?'selected':'' }}>Online</option>
                                <option value="offline" {{ request('status')==='offline'?'selected':'' }}>Offline</option>
                            </select>
                        </div>
                    </div>
                    <div class="pt-2 border-t border-gray-100 dark:border-slate-700">
                        <p class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-2 flex items-center gap-1.5"><i class="fa-solid fa-arrow-down-wide-short"></i> Sort by</p>
                        <div class="grid grid-cols-2 gap-2">
                            <select name="sort" class="input cs-select">
                                @foreach(['name'=>'Name','role'=>'Role (A–Z)','last_seen_at'=>'Last Seen'] as $f=>$label)
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
                        <a href="{{ route('staff.index') }}" class="inline-flex flex-1 items-center justify-center gap-2 px-4 py-2.5 rounded-xl border-2 border-gray-200 dark:border-slate-600 bg-white dark:bg-slate-800 text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-slate-700 text-sm font-semibold transition-colors">
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
                       placeholder="Search by name, email, specialization"
                       class="block w-full h-12 pl-12 pr-4 border-2 border-gray-200 dark:border-slate-600 rounded-xl text-base text-gray-800 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500 focus:ring-2 focus:ring-brand-500 focus:border-brand-500 outline-none transition-all bg-white dark:bg-slate-800">
                {{-- Sort/direction are preserved by the dropdown's <select> selected attributes — no hidden inputs needed (they used to override the new selection). --}}
            </div>

            <button type="submit" class="hidden md:inline-flex h-12 px-5 items-center justify-center gap-2 bg-brand-600 hover:bg-brand-700 text-white rounded-xl transition-colors shadow-sm flex-shrink-0 text-sm font-semibold" title="Search">
                <i class="fa-solid fa-magnifying-glass"></i>
                <span class="hidden lg:inline">Search</span>
            </button>
        </div>
    </form>

    <!-- Staff table -->
    <div class="staff-card overflow-hidden -mx-4 md:mx-0 rounded-none md:rounded-2xl border-x-0 md:border-x-2">
        <div class="overflow-x-auto">
            <table class="min-w-full staff-table responsive-table">
                <thead>
                    <tr>
                        <th>Staff Member</th>
                        <th>Role</th>
                        <th>Contact</th>
                        <th>Today's Shift</th>
                        <th>Status</th>
                        <th style="min-width: 280px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($staff as $member)
                    @php
                        $isOnline   = $member->isOnline();
                        $todayShift = $shifts->get($member->id);
                        $cfg        = $roleColors[$member->role] ?? $roleColors['assistant'];
                        // Show the actual status (Available/Busy/Away/Offline) instead of a binary online/offline.
                        // statusColor() returns gray for offline, amber for away, red for busy, emerald otherwise.
                        $statusLabel = $member->statusLabel();
                        $statusHue   = $member->statusColor();
                        $statusDotClass = match($statusHue) {
                            'red'     => 'bg-red-500 animate-pulse',
                            'amber'   => 'bg-amber-500',
                            'emerald' => 'bg-emerald-500 animate-pulse',
                            default   => 'bg-gray-300 dark:bg-slate-600',
                        };
                        $statusTextClass = match($statusHue) {
                            'red'     => 'text-red-700 dark:text-red-300',
                            'amber'   => 'text-amber-700 dark:text-amber-300',
                            'emerald' => 'text-emerald-700 dark:text-emerald-300',
                            default   => 'text-gray-500 dark:text-gray-400',
                        };
                    @endphp
                    <tr data-href="{{ route('staff.show', $member) }}"
                        data-role-accent="{{ $member->role }}"
                        onclick="if(!event.target.closest('.row-action')) window.location=this.dataset.href"
                        class="group">
                        <td class="cell-primary">
                            <div class="flex items-center gap-3">
                                <x-avatar :user="$member" size="lg" :gradient="$cfg['grad']" />
                                <div class="min-w-0">
                                    <p class="font-semibold text-sm text-gray-900 dark:text-white group-hover:text-brand-700 dark:group-hover:text-brand-300 transition-colors truncate">{{ $member->name }}</p>
                                    <p class="text-sm staff-meta truncate">{{ $member->specialization ?? '—' }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="text-center" data-label="Role">
                            <span class="role-pill {{ $cfg['bg'] }} {{ $cfg['darkBg'] }} {{ $cfg['text'] }} {{ $cfg['darkText'] }} {{ $cfg['border'] }} {{ $cfg['darkBorder'] }}">
                                <i class="fa-solid {{ $cfg['icon'] }} text-xs"></i> {{ $cfg['label'] }}
                            </span>
                        </td>
                        <td data-label="Contact">
                            <div class="text-sm text-gray-700 dark:text-gray-200 text-center">
                                <p class="truncate">{{ $member->email }}</p>
                                <p class="staff-meta">{{ $member->phone ?? '—' }}</p>
                            </div>
                        </td>
                        <td data-label="Shift">
                            @if($todayShift)
                                <div class="flex items-center justify-center gap-2">
                                    <span class="shift-chip shift-{{ $todayShift->shift_type }}">
                                        <i class="fa-solid {{ match($todayShift->shift_type) {
                                            'morning'   => 'fa-sun',
                                            'afternoon' => 'fa-cloud-sun',
                                            'night'     => 'fa-moon',
                                            'on_call'   => 'fa-phone',
                                            default     => 'fa-clock',
                                        } }} text-[10px]"></i>
                                        {{ ucfirst(str_replace('_', ' ', $todayShift->shift_type)) }}
                                    </span>
                                    <span class="text-sm staff-meta whitespace-nowrap">
                                        {{ \Carbon\Carbon::parse($todayShift->start_time)->format('g:i A') }} – {{ \Carbon\Carbon::parse($todayShift->end_time)->format('g:i A') }}
                                    </span>
                                </div>
                            @else
                                <p class="text-sm staff-meta italic text-center">Rest day</p>
                            @endif
                        </td>
                        <td data-label="Status">
                            <div class="flex items-center justify-center gap-2 whitespace-nowrap">
                                <span class="inline-flex items-center gap-1.5 text-sm font-semibold {{ $statusTextClass }}">
                                    <span class="w-2 h-2 rounded-full {{ $statusDotClass }}"></span>
                                    {{ $statusLabel }}
                                </span>
                                @if(!$isOnline && $member->last_seen_at)
                                <span class="text-sm staff-meta">· {{ $member->last_seen_at->diffForHumans(null, true) }} ago</span>
                                @endif
                            </div>
                        </td>
                        <td class="cell-actions">
                            <div class="flex items-center gap-3 row-action w-full">
                                @if($isAdmin)
                                <button type="button" onclick="event.stopPropagation(); openShiftModal({{ $member->id }}, '{{ addslashes($member->name) }}')"
                                        class="row-action btn-row-primary"
                                        title="{{ $todayShift ? 'Revise Shift' : 'Assign Shift' }}">
                                    <i class="fa-solid fa-calendar-plus"></i> Shift
                                </button>
                                @endif
                                <a href="{{ route('chat.index', ['with' => $member->id]) }}" onclick="event.stopPropagation()"
                                   class="row-action btn-row-secondary"
                                   title="Message">
                                    <i class="fa-solid fa-comment"></i> Chat
                                </a>
                                @if(auth()->user()->can_('staff.delete') && $member->id !== auth()->id())
                                <form method="POST" action="{{ route('staff.destroy', $member) }}" class="inline row-action"
                                      onsubmit="event.stopPropagation(); return confirm('Remove {{ addslashes($member->name) }} from staff? This cannot be undone.');"
                                      onclick="event.stopPropagation()">
                                    @csrf @method('DELETE')
                                    <button type="submit" onclick="event.stopPropagation()" class="row-action btn-row-delete" title="Delete">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="py-16 text-center">
                            <div class="w-16 h-16 bg-gray-100 dark:bg-slate-800 rounded-2xl flex items-center justify-center mx-auto mb-3">
                                <i class="fa-solid fa-users text-gray-400 dark:text-gray-500 text-2xl"></i>
                            </div>
                            <p class="text-gray-500 dark:text-gray-400 font-medium">No staff match your filters</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($staff->hasPages())
        <div class="px-6 py-3 border-t border-gray-100 dark:border-slate-700 bg-gray-50/50 dark:bg-slate-900/40">{{ $staff->links() }}</div>
        @endif
    </div>
</div>

@if($isAdmin)
<!-- ── Assign Shift Modal ── -->
<div id="shiftModal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4 backdrop-blur-sm">
    <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-2xl w-full max-w-md border-2 border-gray-100 dark:border-slate-700">
        <div class="bg-gradient-to-r from-brand-600 to-brand-700 px-6 py-5 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-bold">Assign Shift</h3>
                    <p class="text-xs text-white/80 mt-0.5">Schedule a work shift for this staff member</p>
                </div>
                <button type="button" onclick="closeShiftModal()" class="w-9 h-9 rounded-xl flex items-center justify-center text-white/80 hover:text-white hover:bg-white/20 transition-colors"><i class="fa-solid fa-xmark"></i></button>
            </div>
        </div>
        <form id="shiftForm" action="{{ route('staff.shifts.store') }}" method="POST" class="px-6 py-5 space-y-4">
            @csrf
            <input type="hidden" name="user_id" id="shiftUserId">
            <div><label class="label">Staff Member</label><input type="text" id="shiftStaffName" readonly class="input bg-gray-50 dark:bg-slate-800 text-gray-500 dark:text-gray-400"></div>
            <div>
                <label class="label">Shift Type <span class="text-red-500">*</span></label>
                <select name="shift_type" id="shiftTypeSelect" required class="input">
                    <option value="morning">Day Shift (7:00 AM – 3:00 PM)</option>
                    <option value="afternoon">Evening Shift (3:00 PM – 11:00 PM)</option>
                    <option value="night">Night Shift (11:00 PM – 7:00 AM)</option>
                    <option value="on_call">On Call (Custom hours)</option>
                </select>
            </div>
            <div><label class="label">Date <span class="text-red-500">*</span></label><input type="date" name="shift_date" id="shiftDate" required value="{{ date('Y-m-d') }}" class="input"></div>
            <div id="shiftConflictWarning" class="hidden rounded-xl border-2 border-amber-300 dark:border-amber-700/60 bg-amber-50 dark:bg-amber-900/25 text-amber-800 dark:text-amber-200 px-3 py-2.5 text-xs flex items-start gap-2">
                <i class="fa-solid fa-triangle-exclamation mt-0.5"></i>
                <div>
                    <p class="font-bold">Existing shift on this date</p>
                    <p id="shiftConflictDetail" class="mt-0.5"></p>
                    <p class="mt-1 text-[11px] italic">Saving will replace the existing shift.</p>
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div><label class="label">Start <span class="text-red-500">*</span></label><input type="time" name="start_time" id="startTime" required value="07:00" class="input"></div>
                <div><label class="label">End <span class="text-red-500">*</span></label><input type="time" name="end_time" id="endTime" required value="15:00" class="input"></div>
            </div>
            <div class="flex justify-between gap-3 pt-3 border-t border-gray-100 dark:border-slate-700">
                <button type="button" onclick="closeShiftModal()" class="btn-secondary">Cancel</button>
                <button type="submit" class="btn-primary"><i class="fa-solid fa-floppy-disk"></i> Save Shift</button>
            </div>
        </form>
    </div>
</div>

<!-- ── Add Staff Modal ── -->
{{-- The outer container clips with rounded-2xl + overflow-hidden, then a flex column
     stacks a sticky header on top of an overflow-y-auto scroll area — so the rounded
     corners stay clean even at the top/bottom of the scroll. --}}
<div id="addStaffModal" class="hidden fixed inset-0 bg-black/60 z-50 flex items-center justify-center p-4 backdrop-blur-sm">
    <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-2xl w-full max-w-2xl max-h-[92vh] flex flex-col overflow-hidden border-2 border-gray-100 dark:border-slate-700">
        <div class="bg-gradient-to-r from-brand-600 to-brand-700 px-6 py-5 text-white flex items-center justify-between flex-shrink-0">
            <div class="flex items-center gap-3">
                <div class="w-11 h-11 rounded-2xl bg-white/15 flex items-center justify-center backdrop-blur-sm">
                    <i class="fa-solid fa-user-plus text-xl"></i>
                </div>
                <h3 class="text-lg font-bold">Add Staff Member</h3>
            </div>
            <button type="button" onclick="closeAddStaffModal()" class="w-9 h-9 rounded-xl flex items-center justify-center text-white/80 hover:text-white hover:bg-white/20 transition-colors">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <form method="POST" action="{{ route('staff.store') }}" class="px-6 py-5 space-y-5 overflow-y-auto flex-1">
            @csrf

            {{-- Section: Account & Identity --}}
            <div class="rounded-2xl border-2 border-blue-200 dark:border-blue-800/50">
                <div class="px-4 py-3 bg-blue-50 dark:bg-blue-900/25 border-b-2 border-blue-200 dark:border-blue-800/50 flex items-center gap-2 rounded-t-[14px]">
                    <div class="w-7 h-7 rounded-lg bg-blue-500 flex items-center justify-center">
                        <i class="fa-solid fa-id-card text-white text-xs"></i>
                    </div>
                    <p class="text-sm font-bold text-blue-800 dark:text-blue-200">Account & Identity</p>
                </div>
                <div class="p-4 space-y-3">
                    <div>
                        <label class="label">Full Name <span class="text-red-500">*</span></label>
                        <input type="text" name="name" required class="input">
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="label">Email <span class="text-red-500">*</span></label>
                            <input type="email" name="email" required class="input">
                        </div>
                        <div>
                            <label class="label">Temporary Password <span class="text-red-500">*</span></label>
                            <input type="password" name="password" required minlength="6" class="input" placeholder="min. 6 characters">
                        </div>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="label">Role <span class="text-red-500">*</span></label>
                            <select name="role" required class="input cs-select">
                                <option value="nurse">Nurse</option>
                                <option value="doctor">Doctor</option>
                                <option value="pharmacist">Pharmacist</option>
                                <option value="secretary">Secretary</option>
                                <option value="assistant">Assistant</option>
                                <option value="clinic_head">Clinic Head</option>
                                <option value="admin">Admin</option>
                            </select>
                        </div>
                        <div>
                            <label class="label">Date of Birth</label>
                            <input type="text" name="date_of_birth" class="input staff-date" placeholder="Pick a date" readonly>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Section: Contact --}}
            <div class="rounded-2xl border-2 border-emerald-200 dark:border-emerald-800/50">
                <div class="px-4 py-3 bg-emerald-50 dark:bg-emerald-900/25 border-b-2 border-emerald-200 dark:border-emerald-800/50 flex items-center gap-2 rounded-t-[14px]">
                    <div class="w-7 h-7 rounded-lg bg-emerald-500 flex items-center justify-center">
                        <i class="fa-solid fa-address-book text-white text-xs"></i>
                    </div>
                    <p class="text-sm font-bold text-emerald-800 dark:text-emerald-200">Contact</p>
                </div>
                <div class="p-4 space-y-3">
                    <div>
                        <label class="label">Phone</label>
                        <input type="tel" name="phone" class="input">
                    </div>
                    <div>
                        <label class="label">Address</label>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <input type="text" name="address_street" class="input" placeholder="House No. / Street">
                            <input type="text" name="address_barangay" class="input" placeholder="Barangay">
                            <input type="text" name="address_city" class="input" placeholder="City / Municipality">
                            <input type="text" name="address_province" class="input" placeholder="Province">
                            <input type="text" name="address_zip" class="input sm:col-span-2" placeholder="Postal / ZIP code">
                        </div>
                    </div>
                </div>
            </div>

            {{-- Section: Employment --}}
            <div class="rounded-2xl border-2 border-amber-200 dark:border-amber-800/50">
                <div class="px-4 py-3 bg-amber-50 dark:bg-amber-900/25 border-b-2 border-amber-200 dark:border-amber-800/50 flex items-center gap-2 rounded-t-[14px]">
                    <div class="w-7 h-7 rounded-lg bg-amber-500 flex items-center justify-center">
                        <i class="fa-solid fa-briefcase-medical text-white text-xs"></i>
                    </div>
                    <p class="text-sm font-bold text-amber-800 dark:text-amber-200">Employment</p>
                </div>
                <div class="p-4 space-y-3">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="label">Hire Date</label>
                            <input type="text" name="hire_date" class="input staff-date" value="{{ date('Y-m-d') }}" placeholder="Pick a date" readonly>
                        </div>
                        <div>
                            <label class="label">License No.</label>
                            <input type="text" name="license_number" class="input">
                        </div>
                    </div>
                    <div>
                        <label class="label">Specialization</label>
                        <input type="text" name="specialization" class="input">
                    </div>
                </div>
            </div>

            {{-- Section: Emergency Contact --}}
            <div class="rounded-2xl border-2 border-rose-200 dark:border-rose-800/50">
                <div class="px-4 py-3 bg-rose-50 dark:bg-rose-900/25 border-b-2 border-rose-200 dark:border-rose-800/50 flex items-center gap-2 rounded-t-[14px]">
                    <div class="w-7 h-7 rounded-lg bg-rose-500 flex items-center justify-center">
                        <i class="fa-solid fa-heart-pulse text-white text-xs"></i>
                    </div>
                    <p class="text-sm font-bold text-rose-800 dark:text-rose-200">Emergency Contact</p>
                </div>
                <div class="p-4 space-y-3">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="label">Primary Contact Name</label>
                            <input type="text" name="emergency_contact_name" class="input">
                        </div>
                        <div>
                            <label class="label">Primary Contact Phone</label>
                            <input type="tel" name="emergency_contact_phone" class="input">
                        </div>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="label">Secondary Contact Name</label>
                            <input type="text" name="emergency_contact_2_name" class="input">
                        </div>
                        <div>
                            <label class="label">Secondary Contact Phone</label>
                            <input type="tel" name="emergency_contact_2_phone" class="input">
                        </div>
                    </div>
                </div>
            </div>

            {{-- Actions --}}
            <div class="flex flex-col sm:flex-row gap-3 pt-3 border-t border-gray-100 dark:border-slate-700">
                <button type="button" onclick="closeAddStaffModal()"
                        class="inline-flex flex-1 items-center justify-center gap-2 px-5 py-3 rounded-xl border-2 border-gray-200 dark:border-slate-600 bg-white dark:bg-slate-800 text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-slate-700 text-sm font-semibold transition-colors">
                    <i class="fa-solid fa-xmark"></i> Cancel
                </button>
                <button type="submit"
                        class="inline-flex flex-1 items-center justify-center gap-2 px-5 py-3 rounded-xl bg-gradient-to-r from-brand-600 to-brand-700 hover:from-brand-700 hover:to-brand-800 text-white text-sm font-bold transition-all shadow-md shadow-brand-500/30">
                    <i class="fa-solid fa-user-plus"></i> Add Staff Member
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/flatpickr@4"></script>
<script>
function openShiftModal(userId, userName) {
    document.getElementById('shiftUserId').value = userId;
    document.getElementById('shiftStaffName').value = userName;
    document.getElementById('shiftModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
    checkShiftConflict();
}
function closeShiftModal()    { document.getElementById('shiftModal').classList.add('hidden'); document.body.style.overflow = ''; }

// Ping the server to see if this user already has a shift on the chosen date.
// Surfaces an amber warning so the admin knows save will overwrite (updateOrCreate behavior).
async function checkShiftConflict() {
    const userId = document.getElementById('shiftUserId').value;
    const date   = document.getElementById('shiftDate').value;
    const warn   = document.getElementById('shiftConflictWarning');
    const detail = document.getElementById('shiftConflictDetail');
    if (!userId || !date) { warn.classList.add('hidden'); return; }
    try {
        const r = await fetch(`/staff/${userId}/shift-on?date=${encodeURIComponent(date)}`, { headers: { 'Accept': 'application/json' } });
        if (!r.ok) { warn.classList.add('hidden'); return; }
        const j = await r.json();
        if (j.exists) {
            const label = j.shift_type === 'on_call' ? 'On Call'
                        : j.shift_type.charAt(0).toUpperCase() + j.shift_type.slice(1) + ' shift';
            detail.textContent = `${label} (${j.start_time} – ${j.end_time})`;
            warn.classList.remove('hidden');
        } else {
            warn.classList.add('hidden');
        }
    } catch (e) {
        warn.classList.add('hidden');
    }
}
document.getElementById('shiftDate').addEventListener('change', checkShiftConflict);
function openAddStaffModal()  { document.getElementById('addStaffModal').classList.remove('hidden'); document.body.style.overflow = 'hidden'; }
function closeAddStaffModal() { document.getElementById('addStaffModal').classList.add('hidden'); document.body.style.overflow = ''; }

document.addEventListener('keydown', e => { if (e.key === 'Escape') { closeShiftModal(); closeAddStaffModal(); } });

document.getElementById('shiftTypeSelect').addEventListener('change', function () {
    const times = { morning:['07:00','15:00'], afternoon:['15:00','23:00'], night:['23:00','07:00'], on_call:['09:00','17:00'] };
    const [s, e] = times[this.value] || ['',''];
    document.getElementById('startTime').value = s;
    document.getElementById('endTime').value   = e;
});

// Branded date picker for Add Staff dates — uses static month nav so the OS-styled
// dropdown never appears. Same look as the Add Medicine page.
document.querySelectorAll('.staff-date').forEach(el => {
    flatpickr(el, {
        dateFormat: 'Y-m-d',
        allowInput: false,
        monthSelectorType: 'static',
        defaultDate: el.value || null,
    });
});
</script>
@endpush
@endif
@endsection
