@extends('layouts.app')
@section('title', $medicine->name)
@section('page-title', 'Medicine Details')

@section('content')
<div class="space-y-5 max-w-4xl">

    <!-- Back + Header -->
    <div class="flex items-center gap-3">
        <a href="{{ route('medicines.index') }}"
           class="w-9 h-9 flex items-center justify-center rounded-xl text-gray-400 hover:text-gray-700 hover:bg-gray-100 transition-colors">
            <i class="fa-solid fa-arrow-left"></i>
        </a>
        <div>
            <h1 class="text-2xl font-bold text-gray-900">{{ $medicine->name }}</h1>
            <p class="text-sm text-gray-400 mt-0.5">
                {{ $medicine->generic_name ?? 'No generic name' }}
                @if($medicine->dosage) &bull; {{ $medicine->dosage }} @endif
            </p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

        <!-- ── Main Details ── -->
        <div class="lg:col-span-2 space-y-5">
            <div class="card p-6 space-y-5">
                <!-- Badges row -->
                <div class="flex items-center gap-2 flex-wrap">
                    @if($medicine->type === 'prescription')
                    <span class="badge-rx"><i class="fa-solid fa-prescription-bottle"></i> Prescription Only</span>
                    @else
                    <span class="badge-otc"><i class="fa-solid fa-capsules"></i> Over-the-Counter</span>
                    @endif

                    @php $qty = $medicine->latestInventory?->quantity ?? 0; @endphp
                    @if($qty <= 0)
                    <span class="badge-crit"><i class="fa-solid fa-circle-xmark"></i> Out of Stock</span>
                    @elseif($qty <= ($medicine->latestInventory?->min_stock_level ?? 10))
                    <span class="badge-low"><i class="fa-solid fa-triangle-exclamation"></i> Low: {{ $qty }} units</span>
                    @else
                    <span class="badge-ok"><i class="fa-solid fa-circle-check"></i> In Stock: {{ $qty }} units</span>
                    @endif

                    @if($medicine->barcode)
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-semibold bg-slate-100 text-slate-700">
                        <i class="fa-solid fa-barcode text-xs"></i> {{ $medicine->barcode }}
                    </span>
                    @endif
                </div>

                <!-- Details grid -->
                <div class="grid grid-cols-2 gap-4 text-sm">
                    <div>
                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Storage Location</p>
                        <p class="font-medium text-gray-900 flex items-center gap-1.5">
                            <i class="fa-solid fa-location-dot text-brand-500 text-xs"></i>
                            {{ $medicine->location?->full_location ?? 'No location set' }}
                        </p>
                    </div>
                    <div>
                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Batch Number</p>
                        <p class="font-mono font-medium text-gray-900">{{ $medicine->latestInventory?->batch_number ?? '—' }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Expiration Date</p>
                        @php
                            $expiry   = $medicine->latestInventory?->expiration_date;
                            $daysLeft = $expiry ? now()->diffInDays($expiry, false) : null;
                        @endphp
                        <p class="font-medium {{ $daysLeft !== null && $daysLeft < 0 ? 'text-red-600' : ($daysLeft !== null && $daysLeft <= 30 ? 'text-amber-600' : 'text-gray-900') }}">
                            {{ $expiry ? $expiry->format('F j, Y') : '—' }}
                            @if($daysLeft !== null)
                                @if($daysLeft < 0) <span class="text-xs">(EXPIRED)</span>
                                @elseif($daysLeft <= 30) <span class="text-xs">({{ $daysLeft }}d left)</span>
                                @endif
                            @endif
                        </p>
                    </div>
                    <div>
                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Min Stock Level</p>
                        <p class="font-medium text-gray-900">{{ $medicine->latestInventory?->min_stock_level ?? '—' }} units</p>
                    </div>
                </div>

                @if($medicine->description)
                <div class="pt-4 border-t border-gray-100">
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Description</p>
                    <p class="text-sm text-gray-800 leading-relaxed">{{ $medicine->description }}</p>
                </div>
                @endif

                <!-- Quick Dispense -->
                <div class="pt-4 border-t border-gray-100">
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-3">Quick Dispense</p>
                    <form method="POST" action="{{ route('medicines.dispense', $medicine) }}" class="flex gap-2">
                        @csrf
                        <input type="number" name="quantity" min="1" max="{{ $qty }}" placeholder="Qty" required
                               class="input w-24">
                        <input type="text" name="notes" placeholder="Notes (patient name, reason…)"
                               class="input flex-1">
                        <button type="submit" class="btn-primary flex-shrink-0">
                            <i class="fa-solid fa-hand-holding-medical"></i> Dispense
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- ── All Batches ── -->
        <div class="card p-5">
            <h3 class="font-bold text-gray-800 text-sm mb-4">All Batches</h3>
            @if($medicine->inventories->count() > 0)
            <div class="space-y-3">
                @foreach($medicine->inventories as $inv)
                <div class="p-3 bg-gray-50 rounded-xl border border-gray-100 text-sm">
                    <div class="flex justify-between items-center mb-1">
                        <p class="font-semibold text-gray-800 text-xs">{{ $inv->batch_number ?? 'No batch #' }}</p>
                        <span class="font-bold text-xs {{ $inv->quantity <= ($inv->min_stock_level ?? 10) ? 'text-red-600' : 'text-gray-900' }}">{{ $inv->quantity }}u</span>
                    </div>
                    <p class="text-xs text-gray-400">Exp: {{ $inv->expiration_date?->format('M j, Y') ?? '—' }}</p>
                    <p class="text-xs text-gray-400">Min: {{ $inv->min_stock_level ?? '—' }}</p>
                </div>
                @endforeach
            </div>
            @else
            <p class="text-sm text-gray-400 text-center py-6">No inventory records</p>
            @endif
        </div>
    </div>

    <!-- ── Dispense History ── -->
    @if($dispenseLogs->count() > 0)
    <div class="card overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100 bg-gray-50/60">
            <h3 class="font-bold text-gray-800 text-sm">Dispense History</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full">
                <thead>
                    <tr class="border-b border-gray-100 bg-gray-50/40">
                        <th class="th">Date &amp; Time</th>
                        <th class="th">Qty</th>
                        <th class="th">Dispensed By</th>
                        <th class="th">Notes</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach($dispenseLogs as $log)
                    <tr class="hover:bg-gray-50/60">
                        <td class="td text-gray-600">{{ $log->created_at->format('M j, Y g:i A') }}</td>
                        <td class="td font-semibold text-gray-900">{{ $log->quantity }}</td>
                        <td class="td text-gray-700">{{ $log->dispensedBy?->name ?? '—' }}</td>
                        <td class="td text-gray-500">{{ $log->notes ?? '—' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif
</div>
@endsection
