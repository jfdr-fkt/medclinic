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
            <button onclick="toggleAddMenu()" class="btn-primary">
                <i class="fa-solid fa-plus"></i> Add Medicine
                <i class="fa-solid fa-chevron-down text-xs ml-1"></i>
            </button>
            <div id="addMenu" class="hidden absolute right-0 mt-2 w-56 bg-white rounded-xl shadow-lg border border-gray-100 py-2 z-30">
                <button onclick="openAddModal(); toggleAddMenu();" class="w-full text-left px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50 flex items-center gap-3">
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
                <button onclick="openLocationModal(); toggleAddMenu();" class="w-full text-left px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50 flex items-center gap-3">
                    <i class="fa-solid fa-location-dot text-amber-500 w-4"></i>
                    <div>
                        <p class="font-semibold">Add Location</p>
                        <p class="text-xs text-gray-400">New cabinet/shelf</p>
                    </div>
                </button>
            </div>
        </div>
    </div>

    <!-- Stat cards -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="card p-5">
            <div class="flex items-center justify-between mb-3">
                <div class="w-10 h-10 rounded-xl bg-brand-50 flex items-center justify-center">
                    <i class="fa-solid fa-pills text-brand-600"></i>
                </div>
            </div>
            <p class="text-3xl font-extrabold text-gray-900">{{ $totalMedicines }}</p>
            <p class="text-sm font-medium text-gray-500 mt-1">Total Medicines</p>
        </div>
        <div class="card p-5">
            <div class="flex items-center justify-between mb-3">
                <div class="w-10 h-10 rounded-xl bg-red-50 flex items-center justify-center">
                    <i class="fa-solid fa-triangle-exclamation text-red-500"></i>
                </div>
            </div>
            <p class="text-3xl font-extrabold {{ $criticalStock > 0 ? 'text-red-600' : 'text-gray-900' }}">{{ $criticalStock }}</p>
            <p class="text-sm font-medium text-gray-500 mt-1">Critical Stock</p>
        </div>
        <div class="card p-5">
            <div class="flex items-center justify-between mb-3">
                <div class="w-10 h-10 rounded-xl bg-amber-50 flex items-center justify-center">
                    <i class="fa-solid fa-circle-exclamation text-amber-500"></i>
                </div>
            </div>
            <p class="text-3xl font-extrabold {{ $lowStock > 0 ? 'text-amber-600' : 'text-gray-900' }}">{{ $lowStock }}</p>
            <p class="text-sm font-medium text-gray-500 mt-1">Low Stock</p>
        </div>
        <div class="card p-5">
            <div class="flex items-center justify-between mb-3">
                <div class="w-10 h-10 rounded-xl bg-orange-50 flex items-center justify-center">
                    <i class="fa-solid fa-calendar-xmark text-orange-500"></i>
                </div>
            </div>
            <p class="text-3xl font-extrabold {{ $expiringSoon > 0 ? 'text-orange-600' : 'text-gray-900' }}">{{ $expiringSoon }}</p>
            <p class="text-sm font-medium text-gray-500 mt-1">Expiring ≤30d</p>
        </div>
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

    <!-- Active medicines table -->
    <div class="card overflow-hidden">
        <div class="px-5 py-3 border-b border-gray-100 bg-gray-50/60">
            <h3 class="font-bold text-gray-800 text-sm flex items-center gap-2">
                <i class="fa-solid fa-circle-check text-emerald-500"></i> Active Inventory
                <span class="text-xs text-gray-500 font-normal">({{ $medicines->total() }} items)</span>
            </h3>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full">
                <thead>
                    <tr class="border-b border-gray-100 bg-gray-50/40">
                        <th class="th">Medicine</th>
                        <th class="th">Type</th>
                        <th class="th">Stock</th>
                        <th class="th">Location</th>
                        <th class="th">Expiry</th>
                        <th class="th">Status</th>
                        <th class="th text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($medicines as $m)
                    @php
                        $qty = $m->latestInventory?->quantity ?? 0;
                        $min = $m->latestInventory?->min_stock_level ?? 10;
                        $exp = $m->latestInventory?->expiration_date;
                        $daysLeft = $exp ? now()->diffInDays($exp, false) : null;
                    @endphp
                    <tr onclick="window.location='{{ route('medicines.show', $m) }}'" class="hover:bg-brand-50/30 transition-colors group cursor-pointer">
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
                        <td class="td">
                            @if($m->type === 'prescription')
                                <span class="badge-rx"><i class="fa-solid fa-prescription-bottle text-[10px]"></i> Rx</span>
                            @else
                                <span class="badge-otc"><i class="fa-solid fa-capsules text-[10px]"></i> OTC</span>
                            @endif
                        </td>
                        <td class="td">
                            <p class="font-bold {{ $qty <= 5 ? 'text-red-600' : ($qty <= $min ? 'text-amber-600' : 'text-gray-900') }}">{{ $qty }}</p>
                            <p class="text-xs text-gray-400">min {{ $min }}</p>
                        </td>
                        <td class="td">
                            <p class="text-xs text-gray-600">{{ $m->location?->full_location ?? '—' }}</p>
                        </td>
                        <td class="td">
                            @if($exp)
                                <p class="text-xs {{ $daysLeft <= 30 ? 'text-orange-600 font-semibold' : 'text-gray-600' }}">{{ $exp->format('M j, Y') }}</p>
                                @if($daysLeft <= 30)
                                <p class="text-xs text-orange-500">{{ $daysLeft }}d left</p>
                                @endif
                            @else
                                <span class="text-gray-300 text-xs">—</span>
                            @endif
                        </td>
                        <td class="td">
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
                        <td class="td text-right" onclick="event.stopPropagation()">
                            <div class="flex items-center justify-end gap-1">
                                <button onclick="openDispenseModal({{ $m->id }}, '{{ addslashes($m->name) }}', {{ $qty }})"
                                        class="w-8 h-8 rounded-lg flex items-center justify-center hover:bg-emerald-50 text-gray-400 hover:text-emerald-600 transition-colors"
                                        title="Dispense">
                                    <i class="fa-solid fa-hand-holding-medical text-sm"></i>
                                </button>
                                <form method="POST" action="{{ route('medicines.destroy', $m) }}" class="inline" onsubmit="event.stopPropagation(); return confirm('Delete {{ addslashes($m->name) }}?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="w-8 h-8 rounded-lg flex items-center justify-center hover:bg-red-50 text-gray-400 hover:text-red-500 transition-colors">
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
    <div class="card overflow-hidden border-red-100">
        <button onclick="toggleArchive()" class="w-full px-5 py-3 border-b border-red-100 bg-red-50/40 hover:bg-red-50 flex items-center justify-between transition-colors">
            <h3 class="font-bold text-gray-800 text-sm flex items-center gap-2">
                <i class="fa-solid fa-box-archive text-red-500"></i> Expired Archive
                <span class="text-xs text-red-600 font-normal bg-red-100 px-2 py-0.5 rounded-full">{{ $expiredCount }} items need disposal</span>
            </h3>
            <i id="archiveCaret" class="fa-solid fa-chevron-down text-gray-400 transition-transform"></i>
        </button>
        <div id="archiveContent" class="hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead>
                        <tr class="border-b border-gray-100 bg-gray-50/40">
                            <th class="th">Medicine</th>
                            <th class="th">Stock</th>
                            <th class="th">Location</th>
                            <th class="th">Expired On</th>
                            <th class="th text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @foreach($expiredMedicines as $m)
                        @php
                            $exp = $m->latestInventory?->expiration_date;
                            $daysExpired = $exp ? abs(now()->diffInDays($exp, false)) : 0;
                        @endphp
                        <tr class="hover:bg-red-50/30 transition-colors">
                            <td class="td">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-lg bg-gray-200 text-gray-500 flex items-center justify-center font-bold text-xs flex-shrink-0">
                                        {{ strtoupper(substr($m->name, 0, 1)) }}
                                    </div>
                                    <div>
                                        <p class="font-semibold text-gray-700">{{ $m->name }}</p>
                                        <p class="text-xs text-gray-400">{{ $m->generic_name ?? '—' }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="td text-gray-500">{{ $m->latestInventory?->quantity ?? 0 }}u</td>
                            <td class="td text-xs text-gray-500">{{ $m->location?->full_location ?? '—' }}</td>
                            <td class="td">
                                <p class="text-xs text-red-600 font-semibold">{{ $exp?->format('M j, Y') }}</p>
                                <p class="text-xs text-gray-400">{{ $daysExpired }}d ago</p>
                            </td>
                            <td class="td text-right">
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
                <button onclick="closeAddModal()" class="w-8 h-8 rounded-xl flex items-center justify-center text-white/80 hover:text-white hover:bg-white/20">
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
                <button onclick="closeDispenseModal()" class="w-8 h-8 rounded-xl flex items-center justify-center text-gray-400 hover:text-gray-700 hover:bg-gray-100">
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
                <div>
                    <label class="label">Cabinet <span class="text-red-500">*</span></label>
                    <input type="text" name="cabinet" required class="input" placeholder="A">
                </div>
                <div>
                    <label class="label">Shelf <span class="text-red-500">*</span></label>
                    <input type="text" name="shelf" required class="input" placeholder="2">
                </div>
                <div>
                    <label class="label">Level <span class="text-red-500">*</span></label>
                    <input type="text" name="level" required class="input" placeholder="Top">
                </div>
                <div>
                    <label class="label">Section</label>
                    <input type="text" name="section" class="input" placeholder="Left">
                </div>
            </div>
            <div>
                <label class="label">Notes</label>
                <textarea name="notes" rows="2" class="input resize-none" placeholder="Optional notes…"></textarea>
            </div>
            <div class="flex justify-between gap-3 pt-2 border-t border-gray-100">
                <button type="button" onclick="closeLocationModal()" class="btn-secondary"><i class="fa-solid fa-xmark"></i> Cancel</button>
                <button type="submit" class="btn-primary"><i class="fa-solid fa-plus"></i> Add Location</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
function toggleAddMenu() { document.getElementById('addMenu').classList.toggle('hidden'); }
document.addEventListener('click', e => {
    if (!e.target.closest('[onclick*="toggleAddMenu"]') && !e.target.closest('#addMenu')) {
        document.getElementById('addMenu').classList.add('hidden');
    }
});

function openAddModal()      { document.getElementById('addMedicineModal').classList.remove('hidden'); document.body.style.overflow='hidden'; }
function closeAddModal()     { document.getElementById('addMedicineModal').classList.add('hidden'); document.body.style.overflow=''; }
function openLocationModal() { document.getElementById('locationModal').classList.remove('hidden'); document.body.style.overflow='hidden'; }
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
