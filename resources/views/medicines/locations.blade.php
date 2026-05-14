@extends('layouts.app')
@section('title', 'Storage Locations')
@section('page-title', 'Storage Locations')

@push('head')
<style>
.loc-input {
    display: block; width: 100%;
    padding: 0.75rem 0.95rem;
    border: 2px solid #d1d5db;
    border-radius: 0.875rem;
    font-size: 0.9375rem;
    color: #111827;
    background: #fff;
    outline: none;
    transition: border-color .15s, box-shadow .15s;
}
.loc-input:focus { border-color: #0d9488; box-shadow: 0 0 0 3px rgba(13,148,136,.18); }
.loc-input::placeholder { color: #9ca3af; }
.dark .loc-input { background:#0f1a2e !important; border-color:#3f4d6b !important; color:#f1f5f9 !important; }
.dark .loc-input:focus { border-color:#14b8a6 !important; box-shadow: 0 0 0 3px rgba(20,184,166,.2) !important; }
.dark .loc-input::placeholder { color: #64748b !important; }

select.loc-input {
    cursor: pointer; appearance: none; -webkit-appearance: none;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='20' height='20' viewBox='0 0 20 20' fill='none'%3E%3Cpath d='M5 7.5L10 12.5L15 7.5' stroke='%236b7280' stroke-width='1.75' stroke-linecap='round' stroke-linejoin='round'/%3E%3C/svg%3E");
    background-repeat: no-repeat; background-position: right 0.85rem center; background-size: 1.05rem;
    padding-right: 2.4rem;
}

.loc-card {
    background:#fff; border:2px solid #e5e7eb; border-radius:1.25rem;
    box-shadow: 0 1px 3px rgba(0,0,0,.05);
}
.dark .loc-card { background:#1a2438 !important; border-color:#2d3a52 !important; }

.type-badge {
    display:inline-flex; align-items:center; gap:.35rem;
    padding:.25rem .65rem; border-radius:9999px;
    font-size:.7rem; font-weight:700; letter-spacing:.02em;
}
.badge-cabinet      { background:#dbeafe; color:#1d4ed8; }
.badge-freezer      { background:#e0f2fe; color:#0369a1; }
.badge-refrigerator { background:#cffafe; color:#0e7490; }
.badge-drawer       { background:#fef3c7; color:#92400e; }
.badge-room         { background:#f3e8ff; color:#7e22ce; }
.badge-other        { background:#f1f5f9; color:#475569; }
.dark .badge-cabinet      { background:rgba(59,130,246,.2); color:#93c5fd; }
.dark .badge-freezer      { background:rgba(14,165,233,.2); color:#7dd3fc; }
.dark .badge-refrigerator { background:rgba(6,182,212,.2);  color:#67e8f9; }
.dark .badge-drawer       { background:rgba(245,158,11,.2); color:#fcd34d; }
.dark .badge-room         { background:rgba(168,85,247,.2); color:#d8b4fe; }
.dark .badge-other        { background:rgba(148,163,184,.2); color:#cbd5e1; }
</style>
@endpush

@section('content')
<div class="space-y-6" style="max-width:980px; margin:0 auto;">

    <div class="flex items-start justify-between flex-wrap gap-3">
        <div>
            <h1 class="text-2xl font-extrabold text-gray-900 dark:text-white">Storage Locations</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">
                Manage the physical storage units used to organize medicines: cabinets, freezers, refrigerators, drawers and rooms.
            </p>
        </div>
        <a href="{{ route('medicines.index') }}"
           class="inline-flex items-center gap-2 px-4 py-2.5 rounded-2xl border-2 border-gray-200 dark:border-slate-600
                  text-sm font-semibold text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-slate-700 transition-colors">
            <i class="fa-solid fa-arrow-left text-gray-400"></i> Medicines
        </a>
    </div>

    @if(session('success'))
        <div class="rounded-2xl bg-emerald-50 dark:bg-emerald-900/20 border-2 border-emerald-200 dark:border-emerald-700 px-4 py-3 text-sm text-emerald-800 dark:text-emerald-200 flex items-center gap-2">
            <i class="fa-solid fa-circle-check text-emerald-500"></i> {{ session('success') }}
        </div>
    @endif
    @if($errors->any())
        <div class="rounded-2xl bg-red-50 dark:bg-red-900/20 border-2 border-red-200 dark:border-red-700 px-4 py-3 text-sm text-red-800 dark:text-red-200 flex items-center gap-2">
            <i class="fa-solid fa-circle-exclamation text-red-500"></i> {{ $errors->first() }}
        </div>
    @endif

    {{-- Add new location --}}
    <div class="loc-card p-5">
        <div class="flex items-center gap-2 mb-4">
            <div class="w-8 h-8 rounded-xl bg-brand-100 dark:bg-brand-900/40 flex items-center justify-center">
                <i class="fa-solid fa-plus text-brand-600 text-sm"></i>
            </div>
            <h2 class="font-bold text-gray-800 dark:text-white text-sm">Add a new storage location</h2>
        </div>

        <form method="POST" action="{{ route('medicines.locations.store') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            @csrf
            <div>
                <label class="block text-[11px] font-bold uppercase tracking-wide text-gray-600 dark:text-gray-300 mb-1.5">Storage Type</label>
                <select name="storage_type" required class="loc-input">
                    <option value="Cabinet">Cabinet</option>
                    <option value="Freezer">Freezer</option>
                    <option value="Refrigerator">Refrigerator</option>
                    <option value="Drawer">Drawer</option>
                    <option value="Room">Room</option>
                    <option value="Other">Other</option>
                </select>
            </div>
            <div>
                <label class="block text-[11px] font-bold uppercase tracking-wide text-gray-600 dark:text-gray-300 mb-1.5">Unit Name / Label</label>
                <input type="text" name="cabinet" required class="loc-input" placeholder="e.g. A, 1, Main">
            </div>
            <div>
                <label class="block text-[11px] font-bold uppercase tracking-wide text-gray-600 dark:text-gray-300 mb-1.5">Shelf</label>
                <input type="text" name="shelf" required class="loc-input" placeholder="e.g. 1">
            </div>
            <div>
                <label class="block text-[11px] font-bold uppercase tracking-wide text-gray-600 dark:text-gray-300 mb-1.5">Level</label>
                <select name="level" required class="loc-input">
                    <option value="Top">Top</option>
                    <option value="Middle">Middle</option>
                    <option value="Bottom">Bottom</option>
                </select>
            </div>
            <div>
                <label class="block text-[11px] font-bold uppercase tracking-wide text-gray-600 dark:text-gray-300 mb-1.5">Section <span class="font-normal text-gray-400 normal-case">(optional)</span></label>
                <select name="section" class="loc-input">
                    <option value="">—</option>
                    <option value="Left">Left</option>
                    <option value="Center">Center</option>
                    <option value="Right">Right</option>
                </select>
            </div>
            <div>
                <label class="block text-[11px] font-bold uppercase tracking-wide text-gray-600 dark:text-gray-300 mb-1.5">Notes <span class="font-normal text-gray-400 normal-case">(optional)</span></label>
                <input type="text" name="notes" class="loc-input" placeholder="e.g. Refrigerated stock">
            </div>
            <div class="sm:col-span-2 lg:col-span-3 flex justify-end">
                <button type="submit"
                        class="inline-flex items-center gap-2 px-5 py-3 rounded-2xl bg-brand-600 hover:bg-brand-700 text-white text-sm font-bold transition-colors shadow-sm">
                    <i class="fa-solid fa-plus"></i> Add Location
                </button>
            </div>
        </form>
    </div>

    {{-- Existing locations --}}
    <div class="loc-card overflow-hidden">
        <div class="px-5 py-3.5 border-b-2 border-gray-100 dark:border-slate-700 flex items-center gap-2">
            <div class="w-8 h-8 rounded-xl bg-emerald-100 dark:bg-emerald-900/40 flex items-center justify-center">
                <i class="fa-solid fa-list text-emerald-600 text-sm"></i>
            </div>
            <h2 class="font-bold text-gray-800 dark:text-white text-sm">Existing Locations ({{ $locations->count() }})</h2>
        </div>

        @if($locations->isEmpty())
            <div class="p-10 text-center text-gray-500 dark:text-gray-400">
                <i class="fa-solid fa-box-archive text-3xl text-gray-300 dark:text-slate-600 mb-3"></i>
                <p class="font-semibold">No storage locations yet</p>
                <p class="text-xs mt-1">Add one above to start organising your medicines.</p>
            </div>
        @else
        <ul class="divide-y divide-gray-100 dark:divide-slate-700">
            @foreach($locations as $loc)
                @php
                    $badgeClass = match(strtolower($loc->storage_type ?: 'cabinet')) {
                        'freezer'      => 'badge-freezer',
                        'refrigerator' => 'badge-refrigerator',
                        'drawer'       => 'badge-drawer',
                        'room'         => 'badge-room',
                        'other'        => 'badge-other',
                        default        => 'badge-cabinet',
                    };
                    $icon = match(strtolower($loc->storage_type ?: 'cabinet')) {
                        'freezer'      => 'fa-snowflake',
                        'refrigerator' => 'fa-temperature-low',
                        'drawer'       => 'fa-inbox',
                        'room'         => 'fa-door-open',
                        'other'        => 'fa-box',
                        default        => 'fa-warehouse',
                    };
                @endphp
                <li class="p-4 sm:p-5">
                    <form method="POST" action="{{ route('medicines.locations.update', $loc) }}"
                          class="grid grid-cols-2 sm:grid-cols-6 gap-3 items-end">
                        @csrf @method('PUT')
                        <div class="col-span-2 sm:col-span-1">
                            <label class="block text-[10px] font-bold uppercase tracking-wide text-gray-500 dark:text-gray-400 mb-1">Type</label>
                            <select name="storage_type" class="loc-input">
                                @foreach(['Cabinet','Freezer','Refrigerator','Drawer','Room','Other'] as $opt)
                                    <option value="{{ $opt }}" @selected($loc->storage_type === $opt)>{{ $opt }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold uppercase tracking-wide text-gray-500 dark:text-gray-400 mb-1">Unit</label>
                            <input type="text" name="cabinet" value="{{ $loc->cabinet }}" class="loc-input">
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold uppercase tracking-wide text-gray-500 dark:text-gray-400 mb-1">Shelf</label>
                            <input type="text" name="shelf" value="{{ $loc->shelf }}" class="loc-input">
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold uppercase tracking-wide text-gray-500 dark:text-gray-400 mb-1">Level</label>
                            <select name="level" class="loc-input">
                                @foreach(['Top','Middle','Bottom'] as $lvl)
                                    <option value="{{ $lvl }}" @selected($loc->level === $lvl)>{{ $lvl }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold uppercase tracking-wide text-gray-500 dark:text-gray-400 mb-1">Section</label>
                            <select name="section" class="loc-input">
                                <option value="">—</option>
                                @foreach(['Left','Center','Right'] as $sec)
                                    <option value="{{ $sec }}" @selected($loc->section === $sec)>{{ $sec }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-span-2 sm:col-span-6 flex flex-wrap items-center justify-between gap-3 pt-1">
                            <div class="flex items-center gap-2 text-xs">
                                <span class="type-badge {{ $badgeClass }}">
                                    <i class="fa-solid {{ $icon }}"></i> {{ $loc->storage_type }}
                                </span>
                                <span class="text-gray-500 dark:text-gray-400">
                                    {{ $loc->medicines_count }} medicine{{ $loc->medicines_count === 1 ? '' : 's' }} assigned
                                </span>
                            </div>
                            <div class="flex items-center gap-2">
                                <button type="submit"
                                        class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-brand-600 hover:bg-brand-700 text-white text-xs font-bold transition-colors">
                                    <i class="fa-solid fa-floppy-disk"></i> Save
                                </button>
                            </div>
                        </div>
                    </form>
                    <form method="POST" action="{{ route('medicines.locations.destroy', $loc) }}"
                          onsubmit="return confirm('Delete this location? This cannot be undone.');"
                          class="mt-2 flex justify-end">
                        @csrf @method('DELETE')
                        <button type="submit"
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-semibold text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors"
                                @if($loc->medicines_count > 0) disabled title="Cannot delete — medicines are assigned here" style="opacity:.4;cursor:not-allowed" @endif>
                            <i class="fa-solid fa-trash"></i> Delete
                        </button>
                    </form>
                </li>
            @endforeach
        </ul>
        @endif
    </div>
</div>
@endsection
