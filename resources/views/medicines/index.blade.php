@extends('layouts.app')
@section('title', 'Medicines')
@section('page-title', 'Medicines & Inventory')

@section('content')
<div class="space-y-5">

    @php
        $me           = Auth::user();
        $canAddMed    = $me->can_('medicines.create');
        $canDeleteMed = $me->can_('medicines.delete');
        $canLocations = $me->can_('medicines.locations');
    @endphp
    <!-- Header with combined action -->
    <div class="flex items-center justify-between flex-wrap gap-3">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Medicines & Inventory</h1>
            <p class="text-sm text-gray-500 mt-0.5">Track stock, locations, and expiry dates</p>
        </div>
        @if($canAddMed)
        <div class="relative">
            <button type="button" onclick="toggleDropdown('addMedicineMenu')" class="btn-primary">
                <i class="fa-solid fa-plus"></i> Add Medicine
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
        @endif
    </div>

    <!-- Clickable filter cards -->
    @php
        $activeFilter = request('view') ?: 'all';
        $cardLink = function($view) {
            return request()->fullUrlWithQuery(['view' => $view, 'expiring' => null, 'low_stock' => null, 'status' => null]);
        };
    @endphp
    <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
        <a href="{{ $cardLink('all') }}"
           class="rounded-2xl p-5 bg-gradient-to-br from-brand-50 to-brand-100/50 border-2 {{ $activeFilter==='all' ? 'border-brand-600 ring-2 ring-brand-200' : 'border-brand-200 hover:border-brand-400' }} transition-all">
            <div class="flex items-center justify-between mb-3">
                <div class="w-10 h-10 rounded-xl bg-brand-500 flex items-center justify-center shadow-md shadow-brand-200">
                    <i class="fa-solid fa-pills text-white text-sm"></i>
                </div>
                @if($activeFilter==='all')<i class="fa-solid fa-circle-check text-brand-600"></i>@endif
            </div>
            <p class="text-3xl font-extrabold text-brand-900">{{ $totalMedicines }}</p>
            <p class="text-xs font-semibold text-brand-700/70 mt-1">Total Active</p>
        </a>
        <a href="{{ $cardLink('critical') }}"
           class="rounded-2xl p-5 bg-gradient-to-br from-red-50 to-red-100/50 border-2 {{ $activeFilter==='critical' ? 'border-red-600 ring-2 ring-red-200' : 'border-red-200 hover:border-red-400' }} transition-all">
            <div class="flex items-center justify-between mb-3">
                <div class="w-10 h-10 rounded-xl bg-red-500 flex items-center justify-center shadow-md shadow-red-200">
                    <i class="fa-solid fa-triangle-exclamation text-white text-sm"></i>
                </div>
                @if($activeFilter==='critical')<i class="fa-solid fa-circle-check text-red-600"></i>@endif
            </div>
            <p class="text-3xl font-extrabold text-red-900">{{ $criticalStock }}</p>
            <p class="text-xs font-semibold text-red-700/70 mt-1">Critical (≤5)</p>
        </a>
        <a href="{{ $cardLink('low') }}"
           class="rounded-2xl p-5 bg-gradient-to-br from-amber-50 to-amber-100/50 border-2 {{ $activeFilter==='low' ? 'border-amber-600 ring-2 ring-amber-200' : 'border-amber-200 hover:border-amber-400' }} transition-all">
            <div class="flex items-center justify-between mb-3">
                <div class="w-10 h-10 rounded-xl bg-amber-500 flex items-center justify-center shadow-md shadow-amber-200">
                    <i class="fa-solid fa-circle-exclamation text-white text-sm"></i>
                </div>
                @if($activeFilter==='low')<i class="fa-solid fa-circle-check text-amber-600"></i>@endif
            </div>
            <p class="text-3xl font-extrabold text-amber-900">{{ $lowStock }}</p>
            <p class="text-xs font-semibold text-amber-700/70 mt-1">Low Stock</p>
        </a>
        <a href="{{ $cardLink('expiring') }}"
           class="rounded-2xl p-5 bg-gradient-to-br from-orange-50 to-orange-100/50 border-2 {{ $activeFilter==='expiring' ? 'border-orange-600 ring-2 ring-orange-200' : 'border-orange-200 hover:border-orange-400' }} transition-all">
            <div class="flex items-center justify-between mb-3">
                <div class="w-10 h-10 rounded-xl bg-orange-500 flex items-center justify-center shadow-md shadow-orange-200">
                    <i class="fa-solid fa-calendar-xmark text-white text-sm"></i>
                </div>
                @if($activeFilter==='expiring')<i class="fa-solid fa-circle-check text-orange-600"></i>@endif
            </div>
            <p class="text-3xl font-extrabold text-orange-900">{{ $expiringSoon }}</p>
            <p class="text-xs font-semibold text-orange-700/70 mt-1">Expiring ≤30d</p>
        </a>
        <a href="{{ $cardLink('expired') }}"
           class="rounded-2xl p-5 bg-gradient-to-br from-gray-50 to-gray-200/50 border-2 {{ $activeFilter==='expired' ? 'border-gray-700 ring-2 ring-gray-300' : 'border-gray-300 hover:border-gray-500' }} transition-all">
            <div class="flex items-center justify-between mb-3">
                <div class="w-10 h-10 rounded-xl bg-gray-600 flex items-center justify-center shadow-md shadow-gray-300">
                    <i class="fa-solid fa-box-archive text-white text-sm"></i>
                </div>
                @if($activeFilter==='expired')<i class="fa-solid fa-circle-check text-gray-700"></i>@endif
            </div>
            <p class="text-3xl font-extrabold text-gray-700">{{ $expiredCount }}</p>
            <p class="text-xs font-semibold text-gray-600 mt-1">Expired Archive</p>
        </a>
    </div>

    <!-- Search + filter (consistent layout with patients page) -->
    <form method="GET" action="{{ route('medicines.index') }}" class="card p-3">
        @if(request('view'))<input type="hidden" name="view" value="{{ request('view') }}">@endif
        <div class="flex items-center gap-2">
            <!-- Filter icon -->
            <div class="relative">
                @php $hasFilters = request('type') || request('location_id'); @endphp
                <button type="button" onclick="toggleDropdown('medicineFilterMenu')"
                        class="h-12 px-4 bg-white border-2 border-gray-200 rounded-xl hover:border-brand-400 transition-colors flex items-center gap-2 text-sm text-gray-600 font-medium {{ $hasFilters ? 'border-brand-500 text-brand-700' : '' }}">
                    <i class="fa-solid fa-filter text-sm"></i>
                    <span class="hidden sm:inline">Filter</span>
                    @if($hasFilters)
                    <span class="bg-brand-500 text-white text-[10px] font-bold rounded-full w-4 h-4 flex items-center justify-center">{{ (int)!!request('type') + (int)!!request('location_id') }}</span>
                    @endif
                </button>
                <div id="medicineFilterMenu" class="hidden absolute left-0 top-full mt-2 w-72 bg-white border border-gray-100 rounded-xl shadow-xl p-4 space-y-3 z-30">
                    <div>
                        <label class="label">Type</label>
                        <select name="type" class="input">
                            <option value="">All Types</option>
                            <option value="prescription" {{ request('type')==='prescription'?'selected':'' }}>Prescription (Rx)</option>
                            <option value="normal"       {{ request('type')==='normal'?'selected':'' }}>Over-the-Counter</option>
                        </select>
                    </div>
                    <div>
                        <label class="label">Location</label>
                        <select name="location_id" class="input">
                            <option value="">All Locations</option>
                            @foreach($locations as $loc)
                            <option value="{{ $loc->id }}" {{ request('location_id')==$loc->id?'selected':'' }}>{{ $loc->full_location }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex gap-2 pt-2 border-t border-gray-100">
                        <button type="submit" class="btn-primary flex-1 justify-center text-xs py-2">Apply</button>
                        <a href="{{ route('medicines.index') }}" class="btn-secondary flex-1 justify-center text-xs py-2">Reset</a>
                    </div>
                </div>
            </div>

            <!-- Big consistent search bar -->
            <div class="relative flex-1">
                <i class="fa-solid fa-search absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 text-base pointer-events-none"></i>
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Search by name, generic name, or barcode…"
                       class="block w-full h-12 pl-12 pr-4 border-2 border-gray-200 rounded-xl text-base text-gray-800 placeholder-gray-400 focus:ring-2 focus:ring-brand-500 focus:border-brand-500 outline-none transition-all bg-white">
            </div>

            <button type="submit" class="hidden md:inline-flex btn-primary h-12">
                <i class="fa-solid fa-magnifying-glass"></i> Search
            </button>
        </div>

        @if(request('view') && request('view') !== 'all')
        <div class="mt-3 flex items-center gap-2 text-xs">
            <span class="text-gray-500">Card filter:</span>
            <span class="inline-flex items-center gap-1 bg-brand-100 text-brand-700 px-2.5 py-1 rounded-full font-semibold">
                {{ ucfirst(request('view')) }}
                <a href="{{ $cardLink('all') }}" class="hover:text-brand-900"><i class="fa-solid fa-xmark"></i></a>
            </span>
        </div>
        @endif
    </form>

    <!-- Inventory table (label changes with filter) -->
    @php
        $tableHeader = match($activeFilter) {
            'critical' => ['icon'=>'fa-triangle-exclamation','color'=>'red',    'title'=>'Critical Stock'],
            'low'      => ['icon'=>'fa-circle-exclamation', 'color'=>'amber',   'title'=>'Low Stock'],
            'expiring' => ['icon'=>'fa-calendar-xmark',     'color'=>'orange',  'title'=>'Expiring Soon'],
            'expired'  => ['icon'=>'fa-box-archive',        'color'=>'gray',    'title'=>'Expired Archive'],
            default    => ['icon'=>'fa-circle-check',       'color'=>'emerald', 'title'=>'Active Inventory'],
        };
    @endphp
    <div class="card overflow-hidden">
        <div class="px-5 py-3 border-b border-gray-100 bg-gradient-to-r from-{{ $tableHeader['color'] }}-50 to-{{ $tableHeader['color'] }}-100/40">
            <h3 class="font-bold text-gray-800 text-sm flex items-center gap-2">
                <i class="fa-solid {{ $tableHeader['icon'] }} text-{{ $tableHeader['color'] }}-500"></i> {{ $tableHeader['title'] }}
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
                        <td class="td">
                            <div class="flex items-center justify-center gap-3 row-action">
                                <!-- Dispense (larger, colored, primary action) -->
                                <button type="button"
                                        onclick="event.stopPropagation(); openDispenseModal({{ $m->id }}, '{{ addslashes($m->name) }}', {{ $qty }})"
                                        class="row-action inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg text-xs font-semibold bg-emerald-500 text-white hover:bg-emerald-600 transition-colors shadow-sm"
                                        title="Dispense">
                                    <i class="fa-solid fa-hand-holding-medical"></i>
                                    <span class="hidden lg:inline">Dispense</span>
                                </button>
                                @if($canDeleteMed)
                                <!-- Delete (smaller, separated, colored) -->
                                <form method="POST" action="{{ route('medicines.destroy', $m) }}" class="inline row-action"
                                      onsubmit="event.stopPropagation(); return confirm('Delete {{ addslashes($m->name) }}?')"
                                      onclick="event.stopPropagation()">
                                    @csrf @method('DELETE')
                                    <button type="submit" onclick="event.stopPropagation()"
                                            class="row-action inline-flex items-center gap-1.5 px-3 py-2 rounded-lg text-xs font-semibold bg-red-100 text-red-700 hover:bg-red-200 transition-colors">
                                        <i class="fa-solid fa-trash"></i>
                                        <span class="hidden lg:inline">Delete</span>
                                    </button>
                                </form>
                                @endif
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
