@extends('layouts.app')
@section('title', 'Medicines')
@section('page-title', 'Medicines & Inventory')

@section('content')
<div class="space-y-5">

    <!-- Header with combined action -->
    <div class="flex items-center justify-between flex-wrap gap-3">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Medicines & Inventory</h1>
            <p class="text-sm text-gray-500 mt-0.5">Track stock, locations, and expiry dates</p>
        </div>
        <div class="relative">
            <button type="button" onclick="toggleDropdown('addMedicineMenu')" class="btn-primary">
                <i class="fa-solid fa-plus"></i> Add Medicine
                <i class="fa-solid fa-chevron-down text-xs ml-1"></i>
            </button>
            <div id="addMedicineMenu" class="hidden absolute right-0 mt-2 w-56 bg-white rounded-xl shadow-lg border border-gray-100 py-2 z-40">
                <button type="button" onclick="openAddModal();" class="w-full text-left px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50 flex items-center gap-3">
                    <i class="fa-solid fa-keyboard text-brand-500 w-4"></i>
                    <div>
                        <p class="font-semibold">Manual Entry</p>
                        <p class="text-xs text-gray-400">Type details by hand</p>
                    </div>
                </button>
                <a href="{{ route('scan.index') }}" class="w-full text-left px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50 flex items-center gap-3">
                    <i class="fa-solid fa-barcode text-purple-500 w-4"></i>
                    <div>
                        <p class="font-semibold">Smart Scan</p>
                        <p class="text-xs text-gray-400">Scan barcode/QR</p>
                    </div>
                </a>
                <div class="border-t border-gray-100 my-1"></div>
                <button type="button" onclick="openLocationModal();" class="w-full text-left px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50 flex items-center gap-3">
                    <i class="fa-solid fa-location-dot text-amber-500 w-4"></i>
                    <div>
                        <p class="font-semibold">Add Location</p>
                        <p class="text-xs text-gray-400">New cabinet/shelf</p>
                    </div>
                </button>
            </div>
        </div>
    </div>

    <!-- Colorful Stat cards (5: Total / Critical / Low / Expiring / Expired) -->
    <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
        <!-- Total Active -->
        <div class="rounded-2xl p-5 bg-gradient-to-br from-brand-50 to-brand-100/50 border-2 border-brand-200">
            <div class="flex items-center justify-between mb-3">
                <div class="w-10 h-10 rounded-xl bg-brand-500 flex items-center justify-center shadow-md shadow-brand-200">
                    <i class="fa-solid fa-pills text-white text-sm"></i>
                </div>
            </div>
            <p class="text-3xl font-extrabold text-brand-900">{{ $totalMedicines }}</p>
            <p class="text-xs font-semibold text-brand-700/70 mt-1">Total Active</p>
        </div>
        <!-- Critical -->
        <div class="rounded-2xl p-5 bg-gradient-to-br from-red-50 to-red-100/50 border-2 border-red-200">
            <div class="flex items-center justify-between mb-3">
                <div class="w-10 h-10 rounded-xl bg-red-500 flex items-center justify-center shadow-md shadow-red-200">
                    <i class="fa-solid fa-triangle-exclamation text-white text-sm"></i>
                </div>
            </div>
            <p class="text-3xl font-extrabold text-red-900">{{ $criticalStock }}</p>
            <p class="text-xs font-semibold text-red-700/70 mt-1">Critical (≤5)</p>
        </div>
        <!-- Low -->
        <div class="rounded-2xl p-5 bg-gradient-to-br from-amber-50 to-amber-100/50 border-2 border-amber-200">
            <div class="flex items-center justify-between mb-3">
                <div class="w-10 h-10 rounded-xl bg-amber-500 flex items-center justify-center shadow-md shadow-amber-200">
                    <i class="fa-solid fa-circle-exclamation text-white text-sm"></i>
                </div>
            </div>
            <p class="text-3xl font-extrabold text-amber-900">{{ $lowStock }}</p>
            <p class="text-xs font-semibold text-amber-700/70 mt-1">Low Stock</p>
        </div>
        <!-- Expiring Soon -->
        <div class="rounded-2xl p-5 bg-gradient-to-br from-orange-50 to-orange-100/50 border-2 border-orange-200">
            <div class="flex items-center justify-between mb-3">
                <div class="w-10 h-10 rounded-xl bg-orange-500 flex items-center justify-center shadow-md shadow-orange-200">
                    <i class="fa-solid fa-calendar-xmark text-white text-sm"></i>
                </div>
            </div>
            <p class="text-3xl font-extrabold text-orange-900">{{ $expiringSoon }}</p>
            <p class="text-xs font-semibold text-orange-700/70 mt-1">Expiring ≤30d</p>
        </div>
        <!-- Expired (separate count) -->
        <button type="button" onclick="document.getElementById('expiredArchive')?.scrollIntoView({behavior:'smooth'})"
                class="text-left rounded-2xl p-5 bg-gradient-to-br from-gray-50 to-gray-200/50 border-2 border-gray-300 hover:border-gray-500 transition-colors">
            <div class="flex items-center justify-between mb-3">
                <div class="w-10 h-10 rounded-xl bg-gray-600 flex items-center justify-center shadow-md shadow-gray-300">
                    <i class="fa-solid fa-box-archive text-white text-sm"></i>
                </div>
            </div>
            <p class="text-3xl font-extrabold text-gray-700">{{ $expiredCount }}</p>
            <p class="text-xs font-semibold text-gray-600 mt-1">Expired Archive</p>
        </button>
    </div>

    <!-- Advanced filters -->
    <form method="GET" action="{{ route('medicines.index') }}" class="card p-4">
        <div class="grid grid-cols-1 md:grid-cols-5 gap-3">
            <div class="md:col-span-2 relative">
                <i class="fa-solid fa-search absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 text-sm pointer-events-none"></i>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search name, generic, barcode…" class="input pl-10">
            </div>
            <select name="type" class="input">
                <option value="">All Types</option>
                <option value="prescription" {{ request('type')==='prescription'?'selected':'' }}>Prescription (Rx)</option>
                <option value="normal"       {{ request('type')==='normal'?'selected':'' }}>Over-the-Counter</option>
            </select>
            <select name="status" class="input">
                <option value="">All Statuses</option>
                <option value="good"     {{ request('status')==='good'?'selected':'' }}>Good Stock</option>
                <option value="low"      {{ request('status')==='low'?'selected':'' }}>Low Stock</option>
                <option value="critical" {{ request('status')==='critical'?'selected':'' }}>Critical (≤5)</option>
                <option value="out"      {{ request('status')==='out'?'selected':'' }}>Out of Stock</option>
            </select>
            <select name="location_id" class="input">
                <option value="">All Locations</option>
                @foreach($locations as $loc)
                <option value="{{ $loc->id }}" {{ request('location_id')==$loc->id?'selected':'' }}>{{ $loc->full_location }}</option>
                @endforeach
            </select>
        </div>
        <div class="flex items-center justify-between mt-3 flex-wrap gap-3">
            <div class="flex items-center gap-4">
                <label class="flex items-center gap-2 text-sm text-gray-600 cursor-pointer">
                    <input type="checkbox" name="low_stock" value="1" {{ request('low_stock')?'checked':'' }} class="w-4 h-4 rounded border-gray-300 text-brand-600 focus:ring-brand-500">
                    Low stock only
                </label>
                <label class="flex items-center gap-2 text-sm text-gray-600 cursor-pointer">
                    <input type="checkbox" name="expiring" value="1" {{ request('expiring')?'checked':'' }} class="w-4 h-4 rounded border-gray-300 text-brand-600 focus:ring-brand-500">
                    Expiring soon
                </label>
            </div>
            <div class="flex gap-2">
                <button type="submit" class="btn-primary py-1.5 text-xs">Filter</button>
                <a href="{{ route('medicines.index') }}" class="btn-secondary py-1.5 text-xs">Clear</a>
            </div>
        </div>
    </form>

    <!-- Active inventory table -->
    <div class="card overflow-hidden">
        <div class="px-5 py-3 border-b border-gray-100 bg-gradient-to-r from-emerald-50 to-emerald-100/40">
            <h3 class="font-bold text-gray-800 text-sm flex items-center gap-2">
                <i class="fa-solid fa-circle-check text-emerald-500"></i> Active Inventory
                <span class="text-xs text-gray-500 font-normal">({{ $medicines->total() }} items)</span>
            </h3>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full">
                <thead>
                    <tr class="bg-gradient-to-r from-gray-50 to-slate-50 border-b-2 border-gray-200 divide-x divide-gray-200">
                        <th class="th">Medicine</th>
                        <th class="th text-center">Type</th>
                        <th class="th text-center">Stock</th>
                        <th class="th text-center">Location</th>
                        <th class="th text-center">Expiry</th>
                        <th class="th text-center">Status</th>
                        <th class="th text-center">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($medicines as $m)
                    @php
                        $qty = $m->latestInventory?->quantity ?? 0;
                        $min = $m->latestInventory?->min_stock_level ?? 10;
                        $exp = $m->latestInventory?->expiration_date;
                        $daysLeft = $exp ? now()->diffInDays($exp, false) : null;
                        $rowBg = $qty <= 0 ? 'bg-red-50/60 hover:bg-red-100/60'
                            : ($qty <= 5 ? 'bg-red-50/40 hover:bg-red-100/50'
                            : ($qty <= $min ? 'bg-amber-50/40 hover:bg-amber-100/50'
                            : 'hover:bg-brand-50/40'));
                    @endphp
                    <tr data-href="{{ route('medicines.show', $m) }}"
                        onclick="if(!event.target.closest('.row-action')) window.location=this.dataset.href"
                        class="transition-colors group cursor-pointer divide-x divide-gray-100 {{ $rowBg }}">
                        <td class="td">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-lg bg-gradient-to-br from-brand-400 to-brand-600 flex items-center justify-center text-white font-bold text-xs flex-shrink-0">
                                    {{ strtoupper(substr($m->name, 0, 1)) }}
                                </div>
                                <div>
                                    <p class="font-semibold text-gray-900 group-hover:text-brand-700">{{ $m->name }}</p>
                                    <p class="text-xs text-gray-400">{{ $m->generic_name ?? '—' }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="td text-center">
                            @if($m->type === 'prescription')
                                <span class="badge-rx"><i class="fa-solid fa-prescription-bottle text-[10px]"></i> Rx</span>
                            @else
                                <span class="badge-otc"><i class="fa-solid fa-capsules text-[10px]"></i> OTC</span>
                            @endif
                        </td>
                        <td class="td text-center">
                            <p class="font-extrabold text-base {{ $qty <= 5 ? 'text-red-600' : ($qty <= $min ? 'text-amber-600' : 'text-gray-900') }}">{{ $qty }}</p>
                            <p class="text-xs text-gray-400">min {{ $min }}</p>
                        </td>
                        <td class="td text-center">
                            <p class="text-xs text-gray-600">{{ $m->location?->full_location ?? '—' }}</p>
                        </td>
                        <td class="td text-center">
                            @if($exp)
                                <p class="text-xs {{ $daysLeft <= 30 ? 'text-orange-600 font-semibold' : 'text-gray-600' }}">{{ $exp->format('M j, Y') }}</p>
                                @if($daysLeft <= 30)
                                <p class="text-xs text-orange-500">{{ $daysLeft }}d left</p>
                                @endif
                            @else
                                <span class="text-gray-300 text-xs">—</span>
                            @endif
                        </td>
                        <td class="td text-center">
                            @if($qty <= 0)
                                <span class="badge-crit"><i class="fa-solid fa-circle-xmark"></i> Out</span>
                            @elseif($qty <= 5)
                                <span class="badge-crit"><i class="fa-solid fa-triangle-exclamation"></i> Critical</span>
                            @elseif($qty <= $min)
                                <span class="badge-low"><i class="fa-solid fa-circle-exclamation"></i> Low</span>
                            @else
                                <span class="badge-ok"><i class="fa-solid fa-check"></i> Good</span>
                            @endif
                        </td>
                        <td class="td text-center">
                            <div class="flex items-center justify-center gap-1 row-action">
                                <button type="button"
                                        onclick="event.stopPropagation(); openDispenseModal({{ $m->id }}, '{{ addslashes($m->name) }}', {{ $qty }})"
                                        class="row-action w-8 h-8 rounded-lg flex items-center justify-center hover:bg-emerald-100 text-emerald-500 hover:text-emerald-700 transition-colors"
                                        title="Dispense">
                                    <i class="fa-solid fa-hand-holding-medical text-sm"></i>
                                </button>
                                <form method="POST" action="{{ route('medicines.destroy', $m) }}" class="inline row-action"
                                      onsubmit="event.stopPropagation(); return confirm('Delete {{ addslashes($m->name) }}?')"
                                      onclick="event.stopPropagation()">
                                    @csrf @method('DELETE')
                                    <button type="submit" onclick="event.stopPropagation()"
                                            class="row-action w-8 h-8 rounded-lg flex items-center justify-center hover:bg-red-100 text-gray-400 hover:text-red-500 transition-colors">
                                        <i class="fa-solid fa-trash text-sm"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-16 text-center">
                            <div class="w-16 h-16 bg-gray-100 rounded-2xl flex items-center justify-center mx-auto mb-3">
                                <i class="fa-solid fa-pills text-gray-400 text-2xl"></i>
                            </div>
                            <p class="text-gray-500 font-medium">No medicines match your filters</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($medicines->hasPages())
        <div class="px-6 py-3 border-t border-gray-100 bg-gray-50/50">
            {{ $medicines->links() }}
        </div>
        @endif
    </div>

    <!-- ── Expired Archive ── -->
    @if($expiredCount > 0)
    <div id="expiredArchive" class="card overflow-hidden border-2 border-gray-200">
        <button onclick="toggleArchive()" class="w-full px-5 py-3 border-b border-gray-200 bg-gradient-to-r from-gray-100 to-gray-50 hover:from-gray-200 hover:to-gray-100 flex items-center justify-between transition-colors">
            <h3 class="font-bold text-gray-800 text-sm flex items-center gap-2">
                <i class="fa-solid fa-box-archive text-gray-600"></i> Expired Archive
                <span class="text-xs text-gray-700 font-semibold bg-white px-2 py-0.5 rounded-full border border-gray-300">{{ $expiredCount }} items need disposal</span>
            </h3>
            <i id="archiveCaret" class="fa-solid fa-chevron-down text-gray-500 transition-transform"></i>
        </button>
        <div id="archiveContent" class="hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead>
                        <tr class="border-b-2 border-gray-200 bg-gray-50/40 divide-x divide-gray-200">
                            <th class="th">Medicine</th>
                            <th class="th text-center">Stock</th>
                            <th class="th text-center">Location</th>
                            <th class="th text-center">Expired On</th>
                            <th class="th text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($expiredMedicines as $m)
                        @php
                            $exp = $m->latestInventory?->expiration_date;
                            $daysExpired = $exp ? abs(now()->diffInDays($exp, false)) : 0;
                        @endphp
                        <tr class="hover:bg-gray-50 transition-colors divide-x divide-gray-100">
                            <td class="td">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-lg bg-gray-300 text-gray-600 flex items-center justify-center font-bold text-xs flex-shrink-0">
                                        {{ strtoupper(substr($m->name, 0, 1)) }}
                                    </div>
                                    <div>
                                        <p class="font-semibold text-gray-700">{{ $m->name }}</p>
                                        <p class="text-xs text-gray-400">{{ $m->generic_name ?? '—' }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="td text-center text-gray-500">{{ $m->latestInventory?->quantity ?? 0 }}u</td>
                            <td class="td text-center text-xs text-gray-500">{{ $m->location?->full_location ?? '—' }}</td>
                            <td class="td text-center">
                                <p class="text-xs text-red-600 font-semibold">{{ $exp?->format('M j, Y') }}</p>
                                <p class="text-xs text-gray-400">{{ $daysExpired }}d ago</p>
                            </td>
                            <td class="td text-center">
                                <span class="badge-expired"><i class="fa-solid fa-ban"></i> Expired</span>
                                <form method="POST" action="{{ route('medicines.destroy', $m) }}" class="inline ml-2" onsubmit="return confirm('Permanently dispose {{ addslashes($m->name) }}?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-red-500 hover:text-red-700 text-xs font-semibold">
                                        <i class="fa-solid fa-trash-can"></i> Dispose
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif
</div>

<!-- ── Add Medicine Modal ── -->
<div id="addMedicineModal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4 backdrop-blur-sm">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl max-h-[90vh] overflow-y-auto">
        <div class="bg-gradient-to-r from-brand-600 to-brand-700 px-6 py-5 text-white">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center">
                        <i class="fa-solid fa-pills"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold">Add New Medicine</h3>
                        <p class="text-xs text-white/80">Manual inventory entry</p>
                    </div>
                </div>
                <button type="button" onclick="closeAddModal()" class="w-8 h-8 rounded-xl flex items-center justify-center text-white/80 hover:text-white hover:bg-white/20">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
        </div>
        <form method="POST" action="{{ route('medicines.store') }}" class="px-6 py-5 space-y-5">
            @csrf

            <div class="border-l-4 border-blue-400 bg-blue-50/30 rounded-r-xl p-4 space-y-3">
                <p class="text-xs font-bold text-blue-700 uppercase tracking-wider flex items-center gap-2"><i class="fa-solid fa-tag"></i> Identity</p>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="label">Name <span class="text-red-500">*</span></label>
                        <input type="text" name="name" required class="input" placeholder="Amoxicillin 500mg">
                    </div>
                    <div>
                        <label class="label">Generic Name</label>
                        <input type="text" name="generic_name" class="input" placeholder="Amoxicillin">
                    </div>
                    <div>
                        <label class="label">Type <span class="text-red-500">*</span></label>
                        <select name="type" required class="input">
                            <option value="normal">Over-the-Counter</option>
                            <option value="prescription">Prescription (Rx)</option>
                        </select>
                    </div>
                    <div>
                        <label class="label">Dosage</label>
                        <input type="text" name="dosage" class="input" placeholder="500mg">
                    </div>
                    <div>
                        <label class="label">Barcode</label>
                        <input type="text" name="barcode" class="input" placeholder="1234567890123">
                    </div>
                    <div>
                        <label class="label">QR Code</label>
                        <input type="text" name="qr_code" class="input" placeholder="optional">
                    </div>
                </div>
            </div>

            <div class="border-l-4 border-emerald-400 bg-emerald-50/30 rounded-r-xl p-4 space-y-3">
                <p class="text-xs font-bold text-emerald-700 uppercase tracking-wider flex items-center gap-2"><i class="fa-solid fa-warehouse"></i> Stock & Location</p>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="label">Quantity <span class="text-red-500">*</span></label>
                        <input type="number" name="quantity" required min="0" class="input" placeholder="100">
                    </div>
                    <div>
                        <label class="label">Min Stock Level <span class="text-red-500">*</span></label>
                        <input type="number" name="min_stock_level" required min="1" value="10" class="input">
                    </div>
                    <div class="col-span-2">
                        <label class="label">Storage Location <span class="text-red-500">*</span></label>
                        <select name="location_id" required class="input">
                            <option value="">Select location…</option>
                            @foreach($locations as $loc)
                            <option value="{{ $loc->id }}">{{ $loc->full_location }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="label">Expiry Date <span class="text-red-500">*</span></label>
                        <input type="date" name="expiration_date" required class="input">
                    </div>
                    <div>
                        <label class="label">Batch Number</label>
                        <input type="text" name="batch_number" class="input" placeholder="BTH-2024-001">
                    </div>
                </div>
            </div>

            <div class="border-l-4 border-amber-400 bg-amber-50/30 rounded-r-xl p-4 space-y-3">
                <p class="text-xs font-bold text-amber-700 uppercase tracking-wider flex items-center gap-2"><i class="fa-solid fa-circle-info"></i> Description</p>
                <textarea name="description" rows="2" class="input resize-none" placeholder="Indications, side effects, special instructions…"></textarea>
            </div>

            <div class="flex justify-between items-center pt-3 border-t border-gray-100">
                <button type="button" onclick="closeAddModal()" class="btn-secondary"><i class="fa-solid fa-xmark"></i> Cancel</button>
                <button type="submit" class="btn-primary"><i class="fa-solid fa-floppy-disk"></i> Save Medicine</button>
            </div>
        </form>
    </div>
</div>

<!-- ── Dispense Modal ── -->
<div id="dispenseModal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4 backdrop-blur-sm">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md">
        <div class="px-6 py-5 border-b border-gray-100">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-bold text-gray-900">Dispense Medicine</h3>
                <button type="button" onclick="closeDispenseModal()" class="w-8 h-8 rounded-xl flex items-center justify-center text-gray-400 hover:text-gray-700 hover:bg-gray-100">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
            <p class="text-sm text-brand-700 mt-2 font-semibold" id="dispenseMedName">—</p>
            <p class="text-xs text-gray-500" id="dispenseMedStock">In stock: —</p>
        </div>
        <form id="dispenseForm" method="POST" class="px-6 py-5 space-y-4">
            @csrf
            <div>
                <label class="label">Quantity to Dispense <span class="text-red-500">*</span></label>
                <input type="number" name="quantity" required min="1" id="dispenseQty" class="input" placeholder="1">
            </div>
            <div>
                <label class="label">Notes</label>
                <input type="text" name="notes" class="input" placeholder="Patient name or reason…">
            </div>
            <div class="flex justify-between gap-3 pt-2 border-t border-gray-100">
                <button type="button" onclick="closeDispenseModal()" class="btn-secondary"><i class="fa-solid fa-xmark"></i> Cancel</button>
                <button type="submit" class="btn-primary"><i class="fa-solid fa-hand-holding-medical"></i> Dispense</button>
            </div>
        </form>
    </div>
</div>

<!-- ── Add Location Modal ── -->
<div id="locationModal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4 backdrop-blur-sm">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md">
        <div class="px-6 py-5 border-b border-gray-100">
            <h3 class="text-lg font-bold text-gray-900">Add Storage Location</h3>
            <p class="text-xs text-gray-500 mt-0.5">Define a new cabinet, shelf, or section</p>
        </div>
        <form method="POST" action="{{ route('medicines.locations.store') }}" class="px-6 py-5 space-y-3">
            @csrf
            <div class="grid grid-cols-2 gap-3">
                <div><label class="label">Cabinet <span class="text-red-500">*</span></label><input type="text" name="cabinet" required class="input" placeholder="A"></div>
                <div><label class="label">Shelf <span class="text-red-500">*</span></label><input type="text" name="shelf" required class="input" placeholder="2"></div>
                <div><label class="label">Level <span class="text-red-500">*</span></label><input type="text" name="level" required class="input" placeholder="Top"></div>
                <div><label class="label">Section</label><input type="text" name="section" class="input" placeholder="Left"></div>
            </div>
            <div><label class="label">Notes</label><textarea name="notes" rows="2" class="input resize-none"></textarea></div>
            <div class="flex justify-between gap-3 pt-2 border-t border-gray-100">
                <button type="button" onclick="closeLocationModal()" class="btn-secondary"><i class="fa-solid fa-xmark"></i> Cancel</button>
                <button type="submit" class="btn-primary"><i class="fa-solid fa-plus"></i> Add Location</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
function openAddModal()      { document.getElementById('addMedicineMenu').classList.add('hidden'); document.getElementById('addMedicineModal').classList.remove('hidden'); document.body.style.overflow='hidden'; }
function closeAddModal()     { document.getElementById('addMedicineModal').classList.add('hidden'); document.body.style.overflow=''; }
function openLocationModal() { document.getElementById('addMedicineMenu').classList.add('hidden'); document.getElementById('locationModal').classList.remove('hidden'); document.body.style.overflow='hidden'; }
function closeLocationModal(){ document.getElementById('locationModal').classList.add('hidden'); document.body.style.overflow=''; }
function closeDispenseModal(){ document.getElementById('dispenseModal').classList.add('hidden'); document.body.style.overflow=''; }

function openDispenseModal(id, name, stock) {
    document.getElementById('dispenseMedName').textContent  = name;
    document.getElementById('dispenseMedStock').textContent = `In stock: ${stock} units`;
    document.getElementById('dispenseQty').max = stock;
    document.getElementById('dispenseForm').action = `/medicines/${id}/dispense`;
    document.getElementById('dispenseModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function toggleArchive() {
    const c = document.getElementById('archiveContent');
    const caret = document.getElementById('archiveCaret');
    c.classList.toggle('hidden');
    caret.classList.toggle('rotate-180');
}

document.addEventListener('keydown', e => { if(e.key==='Escape'){ closeAddModal(); closeLocationModal(); closeDispenseModal(); } });

@if($errors->any()) openAddModal(); @endif
</script>
@endpush
@endsection
