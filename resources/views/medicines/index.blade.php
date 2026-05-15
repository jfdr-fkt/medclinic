@extends('layouts.app')
@section('title', 'Medicines')
@section('page-title', 'Medicines & Inventory')

@push('head')
<style>
    @keyframes highlightFadeInOut {
        0%   { background-color: transparent;            box-shadow: inset 0 0 0 0px rgba(16,185,129,0); }
        12%  { background-color: rgba(16,185,129,0.22); box-shadow: inset 0 0 0 2px rgba(16,185,129,0.8); }
        65%  { background-color: rgba(16,185,129,0.12); box-shadow: inset 0 0 0 2px rgba(16,185,129,0.5); }
        100% { background-color: transparent;            box-shadow: inset 0 0 0 0px rgba(16,185,129,0); }
    }
    .rowHighlight { animation: highlightFadeInOut 3s ease-in-out 1 forwards; }

    /* ── Stat filter cards — larger, accessible, dark/light parity ── */
    .stat-card {
        display: flex; flex-direction: column; gap: .35rem;
        padding: 1.25rem 1.1rem;
        border-radius: 1rem;
        border: 2px solid transparent;
        transition: transform .12s, box-shadow .12s, border-color .12s;
        cursor: pointer;
    }
    .stat-card:hover { transform: translateY(-1px); box-shadow: 0 6px 16px rgba(0,0,0,.08); }
    .stat-card .stat-icon {
        width: 2.5rem; height: 2.5rem; border-radius: .85rem;
        display: inline-flex; align-items: center; justify-content: center;
        color: #fff; font-size: 0.95rem;
        flex-shrink: 0;
    }
    .stat-card .stat-number {
        font-size: 2.25rem;
        line-height: 1.1;
        font-weight: 800;
        letter-spacing: -.02em;
    }
    .stat-card .stat-label {
        font-size: 0.8rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .04em;
    }
    .stat-card.active {
        border-width: 2.5px;
        box-shadow: 0 6px 18px rgba(0,0,0,.08);
    }
    /* Per-hue tinting */
    .stat-all      { background: #ecfdf5; border-color: #a7f3d0; color: #065f46; }
    .stat-all      .stat-icon { background: #10b981; }
    .stat-all.active { border-color: #059669; }
    .stat-crit     { background: #fef2f2; border-color: #fecaca; color: #991b1b; }
    .stat-crit     .stat-icon { background: #ef4444; }
    .stat-crit.active { border-color: #dc2626; }
    .stat-low      { background: #fffbeb; border-color: #fde68a; color: #92400e; }
    .stat-low      .stat-icon { background: #f59e0b; }
    .stat-low.active { border-color: #d97706; }
    .stat-exp      { background: #fff7ed; border-color: #fed7aa; color: #9a3412; }
    .stat-exp      .stat-icon { background: #f97316; }
    .stat-exp.active { border-color: #ea580c; }
    .stat-arch     { background: #f1f5f9; border-color: #cbd5e1; color: #334155; }
    .stat-arch     .stat-icon { background: #64748b; }
    .stat-arch.active { border-color: #475569; }

    /* Dark mode: keep colored tint but darker surface */
    .dark .stat-all      { background: rgba(16,185,129,.12); border-color: rgba(16,185,129,.35); color: #6ee7b7; }
    .dark .stat-crit     { background: rgba(239,68,68,.12);  border-color: rgba(239,68,68,.35);  color: #fca5a5; }
    .dark .stat-low      { background: rgba(245,158,11,.12); border-color: rgba(245,158,11,.35); color: #fcd34d; }
    .dark .stat-exp      { background: rgba(249,115,22,.12); border-color: rgba(249,115,22,.35); color: #fdba74; }
    .dark .stat-arch     { background: rgba(100,116,139,.15);border-color: rgba(100,116,139,.4); color: #cbd5e1; }
    .dark .stat-all.active   { border-color: #34d399 !important; box-shadow: 0 6px 18px rgba(0,0,0,.4); }
    .dark .stat-crit.active  { border-color: #f87171 !important; box-shadow: 0 6px 18px rgba(0,0,0,.4); }
    .dark .stat-low.active   { border-color: #fbbf24 !important; box-shadow: 0 6px 18px rgba(0,0,0,.4); }
    .dark .stat-exp.active   { border-color: #fb923c !important; box-shadow: 0 6px 18px rgba(0,0,0,.4); }
    .dark .stat-arch.active  { border-color: #94a3b8 !important; box-shadow: 0 6px 18px rgba(0,0,0,.4); }

    /* ── Medicine table — same breathing-room pattern as staff/patient ── */
    .medicine-card {
        background: #fff;
        border: 2px solid #e5e7eb;
        border-radius: 1.25rem;
        box-shadow: 0 1px 3px rgba(0,0,0,.05);
    }
    .dark .medicine-card { background:#1a2438 !important; border-color:#2d3a52 !important; }

    .medicine-table thead th {
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
    .medicine-table thead th:last-child { border-right: none; }
    .dark .medicine-table thead th {
        background: #0f1a2e !important;
        color: #cbd5e1 !important;
        border-bottom-color: #2d3a52 !important;
        border-right-color: #1f2c45 !important;
    }
    .medicine-table tbody td {
        padding: 1rem 1.5rem;
        vertical-align: middle;
        border-right: 1px solid #f1f5f9;
    }
    .medicine-table tbody td:last-child { border-right: none; }
    .dark .medicine-table tbody td { border-right-color: #1f2c45; }
    .medicine-table tbody tr {
        transition: background-color .12s;
        border-bottom: 1px solid #f1f5f9;
        cursor: pointer;
    }
    .dark .medicine-table tbody tr { border-bottom-color:#1f2c45; }
    .medicine-table tbody tr:hover { background: #ecfdf5; }
    .dark .medicine-table tbody tr:hover { background: #1a2438 !important; }
    .medicine-table tbody tr:last-child { border-bottom: none; }
    .med-meta { color:#64748b; font-weight:500; }
    .dark .med-meta { color:#94a3b8; }

    .stock-num {
        font-size: 1.15rem;
        font-weight: 800;
        letter-spacing: -.02em;
    }

    /* Big readable status pill — colored by category */
    .status-pill {
        display: inline-flex; align-items: center; gap: .4rem;
        padding: .35rem .8rem;
        border-radius: 9999px;
        font-size: .78rem;
        font-weight: 700;
        border: 1.5px solid transparent;
        letter-spacing: .02em;
        white-space: nowrap;
    }
    .pill-good { background:#d1fae5; color:#065f46; border-color:#a7f3d0; }
    .pill-low  { background:#fef3c7; color:#92400e; border-color:#fde68a; }
    .pill-crit { background:#fee2e2; color:#991b1b; border-color:#fecaca; }
    .pill-out  { background:#fecaca; color:#7f1d1d; border-color:#fca5a5; }
    .pill-exp  { background:#fed7aa; color:#7c2d12; border-color:#fdba74; }
    .pill-arch { background:#e2e8f0; color:#334155; border-color:#cbd5e1; }
    .dark .pill-good { background: rgba(16,185,129,.18) !important; color:#6ee7b7 !important; border-color: rgba(16,185,129,.4) !important; }
    .dark .pill-low  { background: rgba(245,158,11,.18) !important; color:#fcd34d !important; border-color: rgba(245,158,11,.4) !important; }
    .dark .pill-crit { background: rgba(239,68,68,.18) !important; color:#fca5a5 !important; border-color: rgba(239,68,68,.4) !important; }
    .dark .pill-out  { background: rgba(239,68,68,.28) !important; color:#fca5a5 !important; border-color: rgba(239,68,68,.5) !important; }
    .dark .pill-exp  { background: rgba(249,115,22,.18) !important; color:#fdba74 !important; border-color: rgba(249,115,22,.4) !important; }
    .dark .pill-arch { background: rgba(100,116,139,.22) !important; color:#cbd5e1 !important; border-color: rgba(100,116,139,.45) !important; }

    .badge-rx-lg  { display: inline-flex; align-items: center; gap: .35rem; padding: .3rem .7rem; border-radius: 9999px; font-size: .72rem; font-weight: 700; background: #fee2e2; color: #b91c1c; border: 1.5px solid #fecaca; }
    .badge-otc-lg { display: inline-flex; align-items: center; gap: .35rem; padding: .3rem .7rem; border-radius: 9999px; font-size: .72rem; font-weight: 700; background: #d1fae5; color: #065f46; border: 1.5px solid #a7f3d0; }
    .dark .badge-rx-lg  { background: rgba(239,68,68,.2) !important; color:#fca5a5 !important; border-color: rgba(239,68,68,.4) !important; }
    .dark .badge-otc-lg { background: rgba(16,185,129,.2) !important; color:#6ee7b7 !important; border-color: rgba(16,185,129,.4) !important; }

    .btn-row-dispense {
        display:inline-flex; align-items:center; justify-content:center; gap:.5rem;
        padding: 0.625rem 0.75rem;
        border-radius: 0.75rem;
        font-size: 0.875rem;
        font-weight: 600;
        flex: 1;
        background: #10b981; color: #fff;
        transition: background .12s, box-shadow .12s;
        box-shadow: 0 2px 6px rgba(16,185,129,.35);
    }
    .btn-row-dispense:hover { background: #059669; box-shadow: 0 4px 10px rgba(16,185,129,.45); }
    .btn-row-archive {
        display:inline-flex; align-items:center; justify-content:center; gap:.5rem;
        padding: 0.625rem 0.75rem;
        border-radius: 0.75rem;
        font-size: 0.875rem;
        font-weight: 600;
        flex: 1;
        background: #f1f5f9; color: #334155;
        transition: background .12s;
    }
    .btn-row-archive:hover { background: #e2e8f0; }
    .dark .btn-row-archive { background: rgba(100,116,139,.22); color:#cbd5e1; }
    .dark .btn-row-archive:hover { background: rgba(100,116,139,.35); }
    .btn-row-unarchive {
        display:inline-flex; align-items:center; justify-content:center; gap:.5rem;
        padding: 0.625rem 0.75rem;
        border-radius: 0.75rem;
        font-size: 0.875rem;
        font-weight: 600;
        flex: 1;
        background: #fef3c7; color: #92400e;
        transition: background .12s;
    }
    .btn-row-unarchive:hover { background: #fde68a; }
    .dark .btn-row-unarchive { background: rgba(245,158,11,.2); color:#fcd34d; }
    .dark .btn-row-unarchive:hover { background: rgba(245,158,11,.3); }
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

    /* Archive sub-tabs */
    .archive-tabs {
        display: flex; gap: .5rem;
        padding: 0 0 .9rem 0;
        border-bottom: 2px solid #e2e8f0;
        margin-bottom: 0;
    }
    .dark .archive-tabs { border-bottom-color: #2d3a52; }
    .archive-tab {
        display: inline-flex; align-items: center; gap: .5rem;
        padding: .55rem 1rem;
        border-radius: .75rem;
        font-size: .82rem;
        font-weight: 700;
        background: transparent;
        color: #64748b;
        border: 2px solid transparent;
        cursor: pointer;
        transition: background .12s, color .12s, border-color .12s;
    }
    .archive-tab:hover { background: #f1f5f9; color: #334155; }
    .archive-tab.active { background: #fff; color: #0d9488; border-color: #5eead4; box-shadow: 0 1px 3px rgba(0,0,0,.06); }
    .dark .archive-tab { color: #94a3b8; }
    .dark .archive-tab:hover { background: #243049; color: #cbd5e1; }
    .dark .archive-tab.active { background: #1a2438 !important; color: #5eead4 !important; border-color: #14b8a6 !important; }
    .archive-tab .count-chip {
        font-size: .68rem; font-weight: 800;
        padding: .1rem .45rem; border-radius: 9999px;
        background: #e2e8f0; color: #475569;
    }
    .archive-tab.active .count-chip { background: #ccfbf1; color: #0f766e; }
    .dark .archive-tab .count-chip { background:#2d3a52; color:#cbd5e1; }
    .dark .archive-tab.active .count-chip { background: rgba(20,184,166,.25) !important; color: #5eead4 !important; }
</style>
@endpush

@section('content')
<div class="space-y-5">

    @php
        $me           = Auth::user();
        $canAddMed    = $me->can_('medicines.create');
        $canDeleteMed = $me->can_('medicines.delete');
        $canLocations = $me->can_('medicines.locations');
    @endphp

    <!-- Header -->
    <div class="flex items-center justify-between flex-wrap gap-3">
        <div>
            <h1 class="text-2xl font-extrabold text-gray-900 dark:text-white">Medicines & Inventory</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">Track stock, locations, and expiry dates</p>
        </div>
        <div class="flex items-center gap-2 flex-wrap">
            @if($canLocations)
            <a href="{{ route('medicines.locations.index') }}"
               class="inline-flex items-center gap-2 px-3 py-2.5 bg-amber-100 text-amber-700 hover:bg-amber-200 text-sm font-semibold rounded-xl transition-colors">
                <i class="fa-solid fa-location-dot"></i> Locations
            </a>
            @endif
            @if($canAddMed)
            <a href="{{ route('scan.index') }}" class="btn-primary">
                <i class="fa-solid fa-plus"></i> Add Medicine
            </a>
            @endif
        </div>
    </div>

    <!-- Filter stat cards -->
    @php
        $activeFilter = $view ?? 'all';
        $cardLink = fn($v) => request()->fullUrlWithQuery(['view' => $v, 'archive_tab' => null]);
    @endphp
    <div class="grid grid-cols-2 md:grid-cols-5 gap-3">
        <a href="{{ $cardLink('all') }}" class="stat-card stat-all {{ $activeFilter==='all' ? 'active' : '' }}">
            <div class="flex items-center justify-between">
                <span class="stat-icon"><i class="fa-solid fa-pills"></i></span>
                @if($activeFilter==='all')<i class="fa-solid fa-circle-check"></i>@endif
            </div>
            <p class="stat-number">{{ $totalMedicines }}</p>
            <p class="stat-label">Active</p>
        </a>
        <a href="{{ $cardLink('critical') }}" class="stat-card stat-crit {{ $activeFilter==='critical' ? 'active' : '' }}">
            <div class="flex items-center justify-between">
                <span class="stat-icon"><i class="fa-solid fa-triangle-exclamation"></i></span>
                @if($activeFilter==='critical')<i class="fa-solid fa-circle-check"></i>@endif
            </div>
            <p class="stat-number">{{ $criticalStock }}</p>
            <p class="stat-label">Critical (≤5)</p>
        </a>
        <a href="{{ $cardLink('low') }}" class="stat-card stat-low {{ $activeFilter==='low' ? 'active' : '' }}">
            <div class="flex items-center justify-between">
                <span class="stat-icon"><i class="fa-solid fa-circle-exclamation"></i></span>
                @if($activeFilter==='low')<i class="fa-solid fa-circle-check"></i>@endif
            </div>
            <p class="stat-number">{{ $lowStock }}</p>
            <p class="stat-label">Low Stock</p>
        </a>
        <a href="{{ $cardLink('expiring') }}" class="stat-card stat-exp {{ $activeFilter==='expiring' ? 'active' : '' }}">
            <div class="flex items-center justify-between">
                <span class="stat-icon"><i class="fa-solid fa-calendar-xmark"></i></span>
                @if($activeFilter==='expiring')<i class="fa-solid fa-circle-check"></i>@endif
            </div>
            <p class="stat-number">{{ $expiringSoon }}</p>
            <p class="stat-label">Expiring ≤30d</p>
        </a>
        <a href="{{ $cardLink('archive') }}" class="stat-card stat-arch {{ $activeFilter==='archive' ? 'active' : '' }}">
            <div class="flex items-center justify-between">
                <span class="stat-icon"><i class="fa-solid fa-box-archive"></i></span>
                @if($activeFilter==='archive')<i class="fa-solid fa-circle-check"></i>@endif
            </div>
            <p class="stat-number">{{ $archiveTotal }}</p>
            <p class="stat-label">Archive</p>
        </a>
    </div>

    <!-- Search + filter -->
    <form method="GET" action="{{ route('medicines.index') }}" class="medicine-card p-3">
        @if($activeFilter !== 'all')<input type="hidden" name="view" value="{{ $activeFilter }}">@endif
        @if($activeFilter === 'archive')<input type="hidden" name="archive_tab" value="{{ $archiveTab }}">@endif
        @php $hasFilters = request('type') || request('location_id') || request('sort') || request('direction'); @endphp
        <div class="flex items-center gap-2">
            <div class="relative">
                <button type="button" onclick="toggleDropdown('medicineFilterMenu')"
                        class="h-12 px-4 bg-white dark:bg-slate-800 border-2 border-gray-200 dark:border-slate-600 rounded-xl hover:border-brand-400 dark:hover:border-brand-500 transition-colors flex items-center gap-2 text-sm text-gray-600 dark:text-gray-200 font-medium {{ $hasFilters ? 'border-brand-500 text-brand-700 dark:text-brand-300' : '' }}">
                    <i class="fa-solid fa-sliders text-sm"></i>
                    <span class="hidden sm:inline">Filter & Sort</span>
                    @if($hasFilters)
                    <span class="bg-brand-500 text-white text-[10px] font-bold rounded-full w-4 h-4 flex items-center justify-center">!</span>
                    @endif
                </button>
                <div id="medicineFilterMenu" class="hidden absolute left-0 top-full mt-2 w-80 bg-white dark:bg-slate-800 border-2 border-gray-100 dark:border-slate-700 rounded-2xl shadow-xl p-4 space-y-4 z-30">
                    <div>
                        <p class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-2 flex items-center gap-1.5"><i class="fa-solid fa-filter"></i> Filter by</p>
                        <div class="space-y-2">
                            <select name="type" class="input cs-select">
                                <option value="">All Types</option>
                                <option value="prescription" {{ request('type')==='prescription'?'selected':'' }}>Prescription (Rx)</option>
                                <option value="normal"       {{ request('type')==='normal'?'selected':'' }}>Over-the-Counter</option>
                            </select>
                            <select name="location_id" class="input cs-select">
                                <option value="">All Locations</option>
                                @foreach($locations as $loc)
                                <option value="{{ $loc->id }}" {{ request('location_id')==$loc->id?'selected':'' }}>{{ $loc->full_location }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="pt-2 border-t border-gray-100 dark:border-slate-700">
                        <p class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-2 flex items-center gap-1.5"><i class="fa-solid fa-arrow-down-wide-short"></i> Sort by</p>
                        <div class="grid grid-cols-2 gap-2">
                            <select name="sort" class="input cs-select">
                                @foreach([
                                    'name'        => 'Name',
                                    'stock'       => 'Stock',
                                    'expiry'      => 'Expiry',
                                    'updated_at'  => 'Last Updated',
                                    'archived_at' => 'Archived On',
                                ] as $f=>$label)
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
                        <a href="{{ route('medicines.index') }}" class="inline-flex flex-1 items-center justify-center gap-2 px-4 py-2.5 rounded-xl border-2 border-gray-200 dark:border-slate-600 bg-white dark:bg-slate-800 text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-slate-700 text-sm font-semibold transition-colors">
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
                       placeholder="Search by name, generic name, or barcode"
                       class="block w-full h-12 pl-12 pr-4 border-2 border-gray-200 dark:border-slate-600 rounded-xl text-base text-gray-800 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500 focus:ring-2 focus:ring-brand-500 focus:border-brand-500 outline-none transition-all bg-white dark:bg-slate-800">
            </div>

            <button type="submit" class="hidden md:inline-flex h-12 px-5 items-center justify-center gap-2 bg-brand-600 hover:bg-brand-700 text-white rounded-xl transition-colors shadow-sm flex-shrink-0 text-sm font-semibold">
                <i class="fa-solid fa-magnifying-glass"></i>
                <span class="hidden lg:inline">Search</span>
            </button>
        </div>
    </form>

    <!-- Archive sub-tabs (only when on the Archive view) -->
    @if($activeFilter === 'archive')
    @php
        $tabLink = fn($t) => request()->fullUrlWithQuery(['view' => 'archive', 'archive_tab' => $t]);
    @endphp
    <div class="medicine-card px-5 pt-4">
        <div class="archive-tabs">
            <a href="{{ $tabLink('expired') }}" class="archive-tab {{ $archiveTab === 'expired' ? 'active' : '' }}">
                <i class="fa-solid fa-calendar-xmark"></i> Expired
                <span class="count-chip">{{ $archiveExpiredCount }}</span>
            </a>
            <a href="{{ $tabLink('manual_med_room') }}" class="archive-tab {{ $archiveTab === 'manual_med_room' ? 'active' : '' }}">
                <i class="fa-solid fa-shield-halved"></i> Quarantine
                <span class="count-chip">{{ $archiveManualMedCount }}</span>
            </a>
            <a href="{{ $tabLink('manual_storage') }}" class="archive-tab {{ $archiveTab === 'manual_storage' ? 'active' : '' }}">
                <i class="fa-solid fa-warehouse"></i> Storage Room
                <span class="count-chip">{{ $archiveManualStoreCount }}</span>
            </a>
        </div>
    </div>
    @endif

    <!-- Medicine table -->
    <div class="medicine-card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full medicine-table">
                <thead>
                    <tr>
                        <th class="!text-left">Medicine</th>
                        <th>Type</th>
                        <th>Stock</th>
                        <th>Location</th>
                        <th>{{ $activeFilter === 'archive' && $archiveTab !== 'expired' ? 'Archived' : 'Expiry' }}</th>
                        <th>Status</th>
                        <th style="min-width: 280px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($medicines as $m)
                    @php
                        $qty = $m->latestInventory?->quantity ?? 0;
                        $min = $m->latestInventory?->min_stock_level ?? 10;
                        $exp = $m->latestInventory?->expiration_date
                                ? \Carbon\Carbon::parse($m->latestInventory->expiration_date)
                                : null;
                        $isExpired      = $exp && $exp->lt(now()->startOfDay());
                        $isArchivedMan  = $m->archived_at !== null;
                        // Calendar-day diff (no negatives, no fractional hours flipping the count).
                        $daysToExp = $exp ? now()->startOfDay()->diffInDays($exp->startOfDay(), false) : null;

                        // Pick the row's status pill — archive states win over stock states.
                        if ($isArchivedMan) {
                            $pill = ['arch', 'fa-box-archive', $m->archive_location_type === 'storage' ? 'ARCHIVED · STORAGE' : 'ARCHIVED · QUARANTINE'];
                        } elseif ($isExpired) {
                            $pill = ['exp', 'fa-calendar-xmark', 'EXPIRED'];
                        } elseif ($qty <= 0) {
                            $pill = ['out', 'fa-circle-xmark', 'OUT OF STOCK'];
                        } elseif ($qty <= 5) {
                            $pill = ['crit', 'fa-triangle-exclamation', 'CRITICAL'];
                        } elseif ($qty <= $min) {
                            $pill = ['low', 'fa-circle-exclamation', 'LOW STOCK'];
                        } else {
                            $pill = ['good', 'fa-check-circle', 'IN STOCK'];
                        }
                    @endphp
                    <tr data-href="{{ route('medicines.show', $m) }}"
                        data-medicine-id="{{ $m->id }}"
                        onclick="if(!event.target.closest('.row-action')) window.location=this.dataset.href"
                        class="group">
                        <td>
                            <div class="flex items-center gap-3">
                                @if($m->image_path)
                                <img src="{{ $m->imageUrl() }}" alt="{{ $m->name }}"
                                     class="w-11 h-11 rounded-xl object-cover border-2 border-gray-200 dark:border-slate-600 flex-shrink-0">
                                @else
                                <div class="w-11 h-11 rounded-xl bg-gradient-to-br from-emerald-100 to-emerald-200 dark:from-emerald-900/40 dark:to-emerald-800/40 flex items-center justify-center flex-shrink-0">
                                    <i class="fa-solid fa-pills text-emerald-600 dark:text-emerald-300 text-base"></i>
                                </div>
                                @endif
                                <div class="min-w-0">
                                    <p class="font-semibold text-sm text-gray-900 dark:text-white group-hover:text-emerald-700 dark:group-hover:text-emerald-300 transition-colors truncate">{{ $m->name }}</p>
                                    <p class="text-sm med-meta truncate">{{ $m->generic_name ?? '—' }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="text-center">
                            @if($m->type === 'prescription')
                                <span class="badge-rx-lg"><i class="fa-solid fa-prescription-bottle text-[10px]"></i> Rx</span>
                            @else
                                <span class="badge-otc-lg"><i class="fa-solid fa-capsules text-[10px]"></i> OTC</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <p class="stock-num {{ $isArchivedMan || $isExpired ? 'text-gray-400 dark:text-gray-500 line-through' : ($qty <= 5 ? 'text-red-600 dark:text-red-300' : ($qty <= $min ? 'text-amber-600 dark:text-amber-300' : 'text-gray-900 dark:text-gray-100')) }}">{{ $qty }}</p>
                            <p class="text-sm med-meta">min {{ $min }}</p>
                        </td>
                        <td class="text-center">
                            <p class="text-sm text-gray-700 dark:text-gray-200">{{ $m->location?->full_location ?? '—' }}</p>
                        </td>
                        <td class="text-center">
                            @if($isArchivedMan && $activeFilter === 'archive' && $archiveTab !== 'expired')
                                <p class="text-sm font-semibold text-gray-800 dark:text-gray-100">{{ $m->archived_at->format('M j, Y') }}</p>
                                <p class="text-sm med-meta">{{ $m->archived_at->diffForHumans() }}</p>
                            @elseif($exp)
                                <p class="text-sm font-semibold {{ $isExpired ? 'text-orange-600 dark:text-orange-300' : ($daysToExp <= 30 ? 'text-orange-600 dark:text-orange-300' : 'text-gray-800 dark:text-gray-100') }}">{{ $exp->format('M j, Y') }}</p>
                                @if($isExpired)
                                    <p class="text-sm med-meta">Expired {{ now()->startOfDay()->diffInDays($exp->startOfDay()) }}d ago</p>
                                @elseif($daysToExp <= 30)
                                    <p class="text-sm med-meta">{{ $daysToExp }}d left</p>
                                @else
                                    <p class="text-sm med-meta">{{ $exp->diffForHumans() }}</p>
                                @endif
                            @else
                                <span class="text-sm med-meta italic">—</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <span class="status-pill pill-{{ $pill[0] }}">
                                <i class="fa-solid {{ $pill[1] }} text-xs"></i> {{ $pill[2] }}
                            </span>
                            @if($isArchivedMan && $m->archive_reason)
                            <p class="text-xs med-meta mt-1 italic truncate max-w-[14rem] mx-auto" title="{{ $m->archive_reason }}">{{ $m->archive_reason }}</p>
                            @endif
                        </td>
                        <td>
                            <div class="flex items-center gap-3 row-action w-full">
                                @if($isArchivedMan)
                                    {{-- Archived: no dispense; offer restore + delete --}}
                                    @if($canDeleteMed)
                                    <form method="POST" action="{{ route('medicines.unarchive', $m) }}" class="inline row-action flex-1" onclick="event.stopPropagation()">
                                        @csrf
                                        <button type="submit" onclick="event.stopPropagation()" class="row-action btn-row-unarchive w-full" title="Restore to active inventory">
                                            <i class="fa-solid fa-rotate-left"></i> Restore
                                        </button>
                                    </form>
                                    @endif
                                @elseif($isExpired)
                                    {{-- Expired (auto): no dispense; offer delete --}}
                                    <span class="text-xs med-meta italic flex-1 text-center">Cannot dispense</span>
                                @else
                                    {{-- Active: dispense + archive --}}
                                    <button type="button"
                                            onclick="event.stopPropagation(); openDispenseModal({{ $m->id }}, '{{ addslashes($m->name) }}', {{ $qty }})"
                                            class="row-action btn-row-dispense" title="Dispense">
                                        <i class="fa-solid fa-hand-holding-medical"></i> Dispense
                                    </button>
                                    @if($canDeleteMed)
                                    <button type="button"
                                            onclick="event.stopPropagation(); openArchiveModal({{ $m->id }}, '{{ addslashes($m->name) }}')"
                                            class="row-action btn-row-archive" title="Archive">
                                        <i class="fa-solid fa-box-archive"></i> Archive
                                    </button>
                                    @endif
                                @endif
                                @if($canDeleteMed)
                                <form method="POST" action="{{ route('medicines.destroy', $m) }}" class="inline row-action flex-1"
                                      onsubmit="event.stopPropagation(); return confirm('Delete {{ addslashes($m->name) }}?')"
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
                        <td colspan="7" class="py-16 text-center">
                            <div class="w-16 h-16 bg-emerald-50 dark:bg-slate-800 rounded-2xl flex items-center justify-center mx-auto mb-3">
                                <i class="fa-solid fa-pills text-emerald-400 dark:text-gray-500 text-2xl"></i>
                            </div>
                            <p class="text-gray-500 dark:text-gray-400 font-medium">No medicines match your filters</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($medicines->hasPages())
        <div class="px-6 py-3 border-t border-gray-100 dark:border-slate-700 bg-gray-50/50 dark:bg-slate-900/40">
            {{ $medicines->links() }}
        </div>
        @endif
    </div>

</div>

<!-- ── Dispense Modal ── -->
<div id="dispenseModal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4 backdrop-blur-sm">
    <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-2xl w-full max-w-md border-2 border-gray-100 dark:border-slate-700">
        <div class="px-6 py-5 border-b border-gray-100 dark:border-slate-700">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white">Dispense Medicine</h3>
                <button type="button" onclick="closeDispenseModal()" class="w-8 h-8 rounded-xl flex items-center justify-center text-gray-400 hover:text-gray-700 hover:bg-gray-100 dark:hover:bg-slate-800">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
            <p class="text-sm text-brand-700 dark:text-brand-300 mt-2 font-semibold" id="dispenseMedName">—</p>
            <p class="text-xs text-gray-500 dark:text-gray-400" id="dispenseMedStock">In stock: —</p>
        </div>
        <form id="dispenseForm" method="POST" class="px-6 py-5 space-y-4">
            @csrf
            <div>
                <label class="label">Quantity to Dispense <span class="text-red-500">*</span></label>
                <input type="number" name="quantity" required min="1" id="dispenseQty" class="input" placeholder="1">
            </div>
            <div>
                <label class="label">Notes</label>
                <input type="text" name="notes" class="input" placeholder="Patient name or reason">
            </div>
            <div class="flex justify-between gap-3 pt-2 border-t border-gray-100 dark:border-slate-700">
                <button type="button" onclick="closeDispenseModal()" class="btn-secondary"><i class="fa-solid fa-xmark"></i> Cancel</button>
                <button type="submit" class="btn-primary"><i class="fa-solid fa-hand-holding-medical"></i> Dispense</button>
            </div>
        </form>
    </div>
</div>

<!-- ── Archive Modal ── -->
<div id="archiveModal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4 backdrop-blur-sm">
    <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-2xl w-full max-w-md border-2 border-gray-100 dark:border-slate-700">
        <div class="px-6 py-5 border-b border-gray-100 dark:border-slate-700">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white">Archive Medicine</h3>
                <button type="button" onclick="closeArchiveModal()" class="w-8 h-8 rounded-xl flex items-center justify-center text-gray-400 hover:text-gray-700 hover:bg-gray-100 dark:hover:bg-slate-800">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
            <p class="text-sm text-slate-700 dark:text-slate-300 mt-2 font-semibold" id="archiveMedName">—</p>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Pull this medicine from active circulation. You can restore it any time.</p>
        </div>
        <form id="archiveForm" method="POST" class="px-6 py-5 space-y-4">
            @csrf
            <div>
                <label class="label">Reason <span class="text-red-500">*</span></label>
                <input type="text" name="reason" required maxlength="255" class="input" placeholder="Recalled by manufacturer, damaged batch, etc.">
            </div>
            <div>
                <label class="label">Storage After Archive <span class="text-red-500">*</span></label>
                <select name="archive_location_type" required class="input cs-select">
                    <option value="med_room">Quarantine — still in the med room, pulled from rotation</option>
                    <option value="storage">Storage Room — moved to back-of-house storage</option>
                </select>
            </div>
            <div class="flex justify-between gap-3 pt-2 border-t border-gray-100 dark:border-slate-700">
                <button type="button" onclick="closeArchiveModal()" class="btn-secondary"><i class="fa-solid fa-xmark"></i> Cancel</button>
                <button type="submit" class="inline-flex items-center gap-2 px-4 py-2.5 bg-slate-700 hover:bg-slate-800 text-white text-sm font-semibold rounded-xl transition-colors"><i class="fa-solid fa-box-archive"></i> Archive</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
function closeDispenseModal(){ document.getElementById('dispenseModal').classList.add('hidden'); document.body.style.overflow=''; }
function openDispenseModal(id, name, stock) {
    document.getElementById('dispenseMedName').textContent  = name;
    document.getElementById('dispenseMedStock').textContent = `In stock: ${stock} units`;
    document.getElementById('dispenseQty').max = stock;
    document.getElementById('dispenseForm').action = `/medicines/${id}/dispense`;
    document.getElementById('dispenseModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}
function openArchiveModal(id, name) {
    document.getElementById('archiveMedName').textContent = name;
    document.getElementById('archiveForm').action = `/medicines/${id}/archive`;
    document.getElementById('archiveModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}
function closeArchiveModal(){ document.getElementById('archiveModal').classList.add('hidden'); document.body.style.overflow=''; }

document.addEventListener('keydown', e => { if(e.key==='Escape'){ closeDispenseModal(); closeArchiveModal(); } });

// Highlight & scroll to a newly-added medicine (?highlight=ID)
(function () {
    const params = new URLSearchParams(window.location.search);
    const id = params.get('highlight');
    if (!id) return;
    const row = document.querySelector(`tr[data-medicine-id="${id}"]`);
    if (!row) return;
    setTimeout(() => {
        row.scrollIntoView({ behavior: 'smooth', block: 'center' });
        row.classList.add('rowHighlight');
        row.addEventListener('animationend', () => row.classList.remove('rowHighlight'), { once: true });
        const url = new URL(window.location);
        url.searchParams.delete('highlight');
        window.history.replaceState({}, '', url);
    }, 150);
})();
</script>
@endpush
@endsection
