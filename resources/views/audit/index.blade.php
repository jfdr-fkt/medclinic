@extends('layouts.app')
@section('title', 'Audit Log')
@section('page-title', 'Audit Log')

@section('content')
<style>
.audit-card {
    background: #fff;
    border: 2px solid #e5e7eb;
    border-radius: 1.25rem;
    box-shadow: 0 1px 3px rgba(0,0,0,.05);
}
.dark .audit-card { background:#1a2438 !important; border-color:#2d3a52 !important; }

.audit-table thead th {
    padding: 0.85rem 1.1rem;
    font-size: 0.75rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .05em;
    color: #475569;
    background: #f8fafc;
    border-bottom: 2px solid #e2e8f0;
    text-align: center;
    border-right: 1px solid #e2e8f0;
}
.audit-table thead th:last-child { border-right: none; }
.dark .audit-table thead th {
    background: #0f1a2e !important; color: #cbd5e1 !important;
    border-bottom-color: #2d3a52 !important; border-right-color: #1f2c45 !important;
}
.audit-table tbody td {
    padding: 0.9rem 1.1rem;
    vertical-align: middle;
    border-right: 1px solid #f1f5f9;
}
.audit-table tbody td:last-child { border-right: none; }
.dark .audit-table tbody td { border-right-color: #1f2c45; }
.audit-table tbody tr {
    transition: background-color .12s;
    border-bottom: 1px solid #f1f5f9;
}
.dark .audit-table tbody tr { border-bottom-color:#1f2c45; }
.audit-table tbody tr:hover { background: #f8fafc; }
.dark .audit-table tbody tr:hover { background: #1a2438 !important; }
.audit-table tbody tr:last-child { border-bottom: none; }

.action-chip {
    display: inline-flex; align-items: center; gap: .4rem;
    padding: .3rem .75rem;
    border-radius: 9999px;
    font-size: .72rem;
    font-weight: 700;
    border: 1.5px solid transparent;
}
</style>

<div class="space-y-5">

    <!-- Hero banner — amber/orange (sensitive access trail) -->
    <div class="rounded-2xl overflow-hidden bg-gradient-to-r from-amber-600 via-orange-600 to-rose-600 text-white shadow-md relative">
        <div class="absolute inset-0 opacity-20 pointer-events-none" style="background-image: radial-gradient(circle at 18% 30%, rgba(255,255,255,.55) 0, transparent 35%), radial-gradient(circle at 80% 75%, rgba(255,255,255,.35) 0, transparent 32%);"></div>
        <div class="relative px-5 sm:px-6 py-5 flex items-center gap-4 flex-wrap">
            <div class="w-12 h-12 sm:w-14 sm:h-14 rounded-2xl bg-white/15 backdrop-blur-sm ring-1 ring-white/25 flex items-center justify-center flex-shrink-0">
                <i class="fa-solid fa-shield-halved text-xl sm:text-2xl"></i>
            </div>
            <div class="flex-1 min-w-0">
                <h1 class="text-xl sm:text-2xl font-extrabold leading-tight">Audit Log</h1>
                <p class="text-white/85 text-sm mt-0.5">
                    <span class="font-bold">{{ $logs->total() }}</span> entries &bull; Tracks sensitive access to patient records, medicine dispenses, and staff changes
                </p>
            </div>
            <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-white text-amber-700 text-xs font-extrabold shadow-sm w-full sm:w-auto justify-center sm:justify-start">
                <i class="fa-solid fa-lock"></i> Sensitive trail
            </span>
        </div>
    </div>

    <!-- Filters -->
    <form method="GET" action="{{ route('audit.index') }}" class="audit-card p-3">
        @php $hasFilters = request('user_id') || request('action') || request('range'); @endphp
        <div class="flex items-center gap-2">
            <div class="relative">
                <button type="button" onclick="toggleDropdown('auditFilterMenu')"
                        class="h-12 px-4 bg-white dark:bg-slate-800 border-2 border-gray-200 dark:border-slate-600 rounded-xl hover:border-brand-400 dark:hover:border-brand-500 transition-colors flex items-center gap-2 text-sm text-gray-600 dark:text-gray-200 font-medium {{ $hasFilters ? 'border-brand-500 text-brand-700 dark:text-brand-300' : '' }}">
                    <i class="fa-solid fa-sliders text-sm"></i>
                    <span class="hidden sm:inline">Filter</span>
                    @if($hasFilters)
                    <span class="bg-brand-500 text-white text-[10px] font-bold rounded-full w-4 h-4 flex items-center justify-center">!</span>
                    @endif
                </button>
                <div id="auditFilterMenu" class="hidden absolute left-0 top-full mt-2 w-80 bg-white dark:bg-slate-800 border-2 border-gray-100 dark:border-slate-700 rounded-2xl shadow-xl p-4 space-y-4 z-30">
                    <div>
                        <p class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-2 flex items-center gap-1.5"><i class="fa-solid fa-filter"></i> Filter by</p>
                        <div class="space-y-2">
                            <select name="user_id" class="input cs-select">
                                <option value="">All Users</option>
                                @foreach($users as $u)
                                <option value="{{ $u->id }}" {{ request('user_id')==$u->id ? 'selected':'' }}>{{ $u->name }} ({{ $u->roleLabel() }})</option>
                                @endforeach
                            </select>
                            <select name="action" class="input cs-select">
                                <option value="">All Actions</option>
                                @foreach($actions as $a)
                                <option value="{{ $a }}" {{ request('action')===$a ? 'selected':'' }}>{{ $a }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div>
                        <p class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-2 flex items-center gap-1.5"><i class="fa-solid fa-calendar-days"></i> Date range</p>
                        <select name="range" id="auditRangeSel" class="input cs-select" onchange="document.getElementById('auditCustomRange').classList.toggle('hidden', this.value !== 'custom')">
                            <option value="" {{ !request('range') ? 'selected' : '' }}>Any time</option>
                            <option value="today" {{ request('range')==='today' ? 'selected' : '' }}>Today</option>
                            <option value="yesterday" {{ request('range')==='yesterday' ? 'selected' : '' }}>Yesterday</option>
                            <option value="7d" {{ request('range')==='7d' ? 'selected' : '' }}>Last 7 days</option>
                            <option value="30d" {{ request('range')==='30d' ? 'selected' : '' }}>Last 30 days</option>
                            <option value="custom" {{ request('range')==='custom' ? 'selected' : '' }}>Custom range…</option>
                        </select>
                        <div id="auditCustomRange" class="grid grid-cols-2 gap-2 mt-2 {{ request('range')==='custom' ? '' : 'hidden' }}">
                            <div>
                                <label class="text-[10px] font-bold text-gray-400 dark:text-gray-500 uppercase">From</label>
                                <input type="date" name="from" value="{{ request('from') }}" class="input text-sm py-2">
                            </div>
                            <div>
                                <label class="text-[10px] font-bold text-gray-400 dark:text-gray-500 uppercase">To</label>
                                <input type="date" name="to" value="{{ request('to') }}" class="input text-sm py-2">
                            </div>
                        </div>
                    </div>
                    <div class="flex gap-2 pt-3 border-t border-gray-100 dark:border-slate-700">
                        <a href="{{ route('audit.index') }}" class="inline-flex flex-1 items-center justify-center gap-2 px-4 py-2.5 rounded-xl border-2 border-gray-200 dark:border-slate-600 bg-white dark:bg-slate-800 text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-slate-700 text-sm font-semibold transition-colors">
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
                       placeholder="Search by user name or record details"
                       class="block w-full h-12 pl-12 pr-4 border-2 border-gray-200 dark:border-slate-600 rounded-xl text-base text-gray-800 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500 focus:ring-2 focus:ring-brand-500 focus:border-brand-500 outline-none transition-all bg-white dark:bg-slate-800">
            </div>

            <button type="submit" class="hidden md:inline-flex h-12 px-5 items-center justify-center gap-2 bg-brand-600 hover:bg-brand-700 text-white rounded-xl transition-colors shadow-sm flex-shrink-0 text-sm font-semibold">
                <i class="fa-solid fa-magnifying-glass"></i>
                <span class="hidden lg:inline">Search</span>
            </button>
        </div>
    </form>

    <!-- Audit table -->
    <div class="audit-card overflow-hidden -mx-4 md:mx-0 rounded-none md:rounded-2xl border-x-0 md:border-x-2">
        <div class="overflow-x-auto">
            <table class="min-w-full audit-table responsive-table">
                <thead>
                    <tr>
                        <th>User</th>
                        <th style="width: 180px;">Action</th>
                        <th>Details</th>
                        <th style="width: 200px;">When</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $log)
                    <tr>
                        <td class="cell-primary">
                            @if($log->user)
                            <div class="flex items-center gap-3">
                                <x-avatar :user="$log->user" size="lg" />
                                <div class="min-w-0">
                                    <p class="font-semibold text-sm text-gray-900 dark:text-white truncate">{{ $log->user->name }}</p>
                                    <p class="text-sm text-gray-500 dark:text-gray-400 truncate">{{ $log->user->roleLabel() }}</p>
                                </div>
                            </div>
                            @else
                            <span class="text-sm text-gray-400 italic">System</span>
                            @endif
                        </td>
                        <td class="text-center" data-label="Action">
                            @php
                                // Color + icon by action category so the long list scans visually.
                                $a = $log->action;
                                $chip = match(true) {
                                    str_starts_with($a, 'patient.view')     => ['amber',   'fa-eye'],
                                    str_starts_with($a, 'patient.create')   => ['emerald', 'fa-user-plus'],
                                    str_starts_with($a, 'patient.update')   => ['blue',    'fa-pen'],
                                    str_starts_with($a, 'patient.delete')   => ['red',     'fa-trash'],
                                    str_starts_with($a, 'medicine.dispense')=> ['purple',  'fa-hand-holding-medical'],
                                    str_starts_with($a, 'medicine.create')  => ['emerald', 'fa-plus'],
                                    str_starts_with($a, 'medicine.update')  => ['blue',    'fa-pen'],
                                    str_starts_with($a, 'medicine.delete')  => ['red',     'fa-trash'],
                                    str_starts_with($a, 'staff.create')     => ['emerald', 'fa-user-plus'],
                                    str_starts_with($a, 'staff.shift')      => ['cyan',    'fa-calendar-plus'],
                                    default                                  => ['slate',   'fa-circle-info'],
                                };
                                [$hue, $icon] = $chip;
                            @endphp
                            <span class="action-chip bg-{{ $hue }}-100 dark:bg-{{ $hue }}-900/35 text-{{ $hue }}-700 dark:text-{{ $hue }}-300 border-{{ $hue }}-200 dark:border-{{ $hue }}-800/60">
                                <i class="fa-solid {{ $icon }} text-xs"></i> {{ $log->action }}
                            </span>
                        </td>
                        <td data-label="Details">
                            <p class="text-sm text-gray-700 dark:text-gray-200">{{ $log->details ?? '—' }}</p>
                            @php
                                // Delete actions point at rows that no longer exist — the deep link would 404 on click.
                                // Surface a static "Record removed" label instead so the audit row still reads clean.
                                $isDelete = str_ends_with($log->action, '.delete');
                                $deepLink = $isDelete ? null : match($log->entity_type) {
                                    \App\Models\Patient::class  => route('patients.show',  $log->entity_id),
                                    \App\Models\Medicine::class => route('medicines.show', $log->entity_id),
                                    \App\Models\User::class     => route('staff.show',     $log->entity_id),
                                    default                     => null,
                                };
                            @endphp
                            @if($deepLink)
                            <a href="{{ $deepLink }}" class="text-xs font-semibold text-brand-600 dark:text-brand-300 hover:underline inline-flex items-center gap-1 mt-1">
                                <i class="fa-solid fa-arrow-up-right-from-square text-[10px]"></i> Open record
                            </a>
                            @elseif($isDelete)
                            <span class="text-xs font-semibold text-gray-400 dark:text-gray-500 inline-flex items-center gap-1 mt-1 italic">
                                <i class="fa-solid fa-circle-minus text-[10px]"></i> Record removed
                            </span>
                            @endif
                        </td>
                        <td class="text-center" data-label="When">
                            <p class="text-sm text-gray-700 dark:text-gray-200">{{ $log->created_at->format('M j, Y g:i A') }}</p>
                            <p class="text-sm text-gray-500 dark:text-gray-400">{{ $log->created_at->diffForHumans() }}</p>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="py-16 text-center">
                            <div class="w-16 h-16 bg-gray-100 dark:bg-slate-800 rounded-2xl flex items-center justify-center mx-auto mb-3">
                                <i class="fa-solid fa-shield-halved text-gray-400 dark:text-gray-500 text-2xl"></i>
                            </div>
                            <p class="text-gray-500 dark:text-gray-400 font-medium">No audit entries yet</p>
                            <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">Entries appear when admins or clinic heads open patient records they aren't assigned to.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($logs->hasPages())
        <div class="px-6 py-3 border-t border-gray-100 dark:border-slate-700 bg-gray-50/50 dark:bg-slate-900/40">{{ $logs->links() }}</div>
        @endif
    </div>
</div>
@endsection
