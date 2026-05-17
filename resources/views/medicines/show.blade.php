@extends('layouts.app')
@section('title', $medicine->name)
@section('page-title', 'Medicine Details')

@section('content')
@php
    $me      = Auth::user();
    $canDisp = $me->can_('medicines.dispense');
    $canEdit = $me->can_('medicines.create');
    $qty     = $medicine->latestInventory?->quantity ?? 0;
    $min     = $medicine->latestInventory?->min_stock_level ?? 10;
    $expiry  = $medicine->latestInventory?->expiration_date
                ? \Carbon\Carbon::parse($medicine->latestInventory->expiration_date) : null;
    $daysLeft = $expiry ? now()->startOfDay()->diffInDays($expiry->startOfDay(), false) : null;
    $isExpired = $daysLeft !== null && $daysLeft < 0;
    $gallery   = $medicine->galleryUrls();
@endphp

<div class="space-y-5 max-w-5xl mx-auto">

    {{-- Back link --}}
    <div class="flex items-center gap-3">
        <a href="{{ route('medicines.index') }}"
           class="w-9 h-9 flex items-center justify-center rounded-xl text-gray-400 dark:text-gray-500 hover:text-gray-700 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-slate-800 transition-colors">
            <i class="fa-solid fa-arrow-left"></i>
        </a>
        <span class="text-sm text-gray-400 dark:text-gray-500">Back to Medicines</span>
    </div>

    {{-- Hero banner — flat in-gradient, centered identity --}}
    <div class="rounded-2xl overflow-hidden bg-gradient-to-r from-emerald-600 via-emerald-700 to-teal-700 text-white shadow-md relative">
        <div class="absolute inset-0 opacity-20 pointer-events-none" style="background-image: radial-gradient(circle at 18% 30%, rgba(255,255,255,.55) 0, transparent 35%), radial-gradient(circle at 80% 75%, rgba(255,255,255,.35) 0, transparent 32%);"></div>
        <div class="relative px-5 sm:px-8 py-6 flex flex-col items-center text-center gap-3">
            @if($medicine->image_path)
                <img src="{{ $medicine->imageUrl() }}" alt="{{ $medicine->name }}"
                     class="w-24 h-24 sm:w-28 sm:h-28 rounded-2xl object-cover ring-2 ring-white/40 shadow-md bg-white">
            @else
                <div class="w-24 h-24 sm:w-28 sm:h-28 rounded-2xl bg-white/15 backdrop-blur-sm ring-1 ring-white/25 flex items-center justify-center">
                    <i class="fa-solid fa-pills text-3xl sm:text-4xl"></i>
                </div>
            @endif
            <div>
                <h1 class="text-2xl sm:text-3xl font-extrabold leading-tight">{{ $medicine->name }}</h1>
                <p class="text-white/85 text-sm sm:text-base mt-1">
                    {{ $medicine->generic_name ?? 'No generic name' }}
                    @if($medicine->dosage) &bull; {{ $medicine->dosage }} @endif
                    @if($medicine->dosage_form) &bull; {{ ucfirst($medicine->dosage_form) }} @endif
                </p>
            </div>
            <div class="flex items-center justify-center gap-2 flex-wrap mt-1">
                @if($medicine->type === 'prescription')
                <span class="inline-flex items-center gap-1.5 bg-white text-red-700 px-2.5 py-1 rounded-full text-xs font-extrabold shadow-sm">
                    <i class="fa-solid fa-prescription-bottle text-[10px]"></i> Rx Only
                </span>
                @else
                <span class="inline-flex items-center gap-1.5 bg-white text-emerald-700 px-2.5 py-1 rounded-full text-xs font-extrabold shadow-sm">
                    <i class="fa-solid fa-capsules text-[10px]"></i> Over-the-Counter
                </span>
                @endif

                @if($medicine->isArchivedManually())
                <span class="inline-flex items-center gap-1.5 bg-white/20 ring-1 ring-white/30 text-white px-2.5 py-1 rounded-full text-xs font-bold">
                    <i class="fa-solid fa-box-archive text-[10px]"></i> Archived
                </span>
                @elseif($isExpired)
                <span class="inline-flex items-center gap-1.5 bg-white text-orange-700 px-2.5 py-1 rounded-full text-xs font-extrabold shadow-sm">
                    <i class="fa-solid fa-calendar-xmark text-[10px]"></i> Expired
                </span>
                @elseif($qty <= 0)
                <span class="inline-flex items-center gap-1.5 bg-white text-red-700 px-2.5 py-1 rounded-full text-xs font-extrabold shadow-sm">
                    <i class="fa-solid fa-circle-xmark text-[10px]"></i> Out of Stock
                </span>
                @elseif($qty <= $min)
                <span class="inline-flex items-center gap-1.5 bg-white text-amber-700 px-2.5 py-1 rounded-full text-xs font-extrabold shadow-sm">
                    <i class="fa-solid fa-triangle-exclamation text-[10px]"></i> Low: {{ $qty }} units
                </span>
                @else
                <span class="inline-flex items-center gap-1.5 bg-white text-emerald-700 px-2.5 py-1 rounded-full text-xs font-extrabold shadow-sm">
                    <i class="fa-solid fa-circle-check text-[10px]"></i> In Stock: {{ $qty }} units
                </span>
                @endif

                @if($medicine->barcode)
                <span class="inline-flex items-center gap-1.5 bg-white/20 ring-1 ring-white/30 text-white px-2.5 py-1 rounded-full text-xs font-bold">
                    <i class="fa-solid fa-barcode text-[10px]"></i> {{ $medicine->barcode }}
                </span>
                @endif
            </div>
        </div>
    </div>

    {{-- Vitals strip: stock / location / expiry / min --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
        <div class="card p-4 text-center">
            <p class="text-[11px] font-bold uppercase tracking-wider text-gray-400 dark:text-gray-500 mb-1">Stock</p>
            <p class="text-2xl font-extrabold {{ $qty <= 0 ? 'text-red-600' : ($qty <= $min ? 'text-amber-600' : 'text-gray-900 dark:text-white') }}">{{ $qty }}</p>
            <p class="text-[11px] text-gray-500 dark:text-gray-400 mt-0.5">min {{ $min }}</p>
        </div>
        <div class="card p-4 text-center">
            <p class="text-[11px] font-bold uppercase tracking-wider text-gray-400 dark:text-gray-500 mb-1">Location</p>
            <p class="text-sm font-bold text-gray-900 dark:text-white leading-tight">{{ $medicine->location?->full_location ?? '—' }}</p>
        </div>
        <div class="card p-4 text-center">
            <p class="text-[11px] font-bold uppercase tracking-wider text-gray-400 dark:text-gray-500 mb-1">Expiry</p>
            <p class="text-sm font-bold {{ $isExpired ? 'text-red-600' : ($daysLeft !== null && $daysLeft <= 30 ? 'text-amber-600' : 'text-gray-900 dark:text-white') }}">{{ $expiry ? $expiry->format('M j, Y') : '—' }}</p>
            @if($daysLeft !== null)
                @if($isExpired)
                <p class="text-[11px] text-red-500 dark:text-red-300 mt-0.5">Expired {{ abs($daysLeft) }}d ago</p>
                @elseif($daysLeft <= 30)
                <p class="text-[11px] text-amber-600 dark:text-amber-300 mt-0.5">{{ $daysLeft }}d left</p>
                @else
                <p class="text-[11px] text-gray-500 dark:text-gray-400 mt-0.5">{{ $expiry->diffForHumans() }}</p>
                @endif
            @endif
        </div>
        <div class="card p-4 text-center">
            <p class="text-[11px] font-bold uppercase tracking-wider text-gray-400 dark:text-gray-500 mb-1">Batch</p>
            <p class="text-sm font-bold text-gray-900 dark:text-white font-mono">{{ $medicine->latestInventory?->batch_number ?? '—' }}</p>
        </div>
    </div>

    {{-- ── Image Gallery (up to 5 extra images) ── --}}
    @if($canEdit || !empty($gallery))
    <div class="card p-5">
        <div class="flex items-center justify-between mb-4 flex-wrap gap-2">
            <h3 class="font-bold text-gray-900 dark:text-white text-base flex items-center gap-2">
                <span class="w-7 h-7 rounded-lg bg-emerald-100 dark:bg-emerald-900/40 text-emerald-600 dark:text-emerald-300 inline-flex items-center justify-center">
                    <i class="fa-solid fa-images text-xs"></i>
                </span>
                Photo Gallery
                <span class="text-xs font-medium text-gray-500 dark:text-gray-400">{{ count($gallery) }}/5</span>
            </h3>
            @if($canEdit && count($gallery) < 5)
            <form method="POST" action="{{ route('medicines.gallery.upload', $medicine) }}" enctype="multipart/form-data" id="galleryUploadForm" class="inline">
                @csrf
                <input type="file" name="images[]" id="galleryInput" accept="image/jpeg,image/png,image/webp" multiple
                       class="hidden" onchange="document.getElementById('galleryUploadForm').submit()">
                <button type="button" onclick="document.getElementById('galleryInput').click()"
                        class="inline-flex items-center gap-2 px-3 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold transition-colors shadow-sm">
                    <i class="fa-solid fa-camera"></i> Add Photos
                </button>
            </form>
            @endif
        </div>

        @if(!empty($gallery))
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-5 gap-3">
            @foreach($gallery as $i => $url)
            <div class="relative group aspect-square rounded-xl overflow-hidden border-2 border-gray-100 dark:border-slate-700 bg-gray-50 dark:bg-slate-800/60">
                <img src="{{ $url }}" alt="{{ $medicine->name }} photo {{ $i+1 }}"
                     class="w-full h-full object-cover cursor-zoom-in"
                     onclick="openGalleryLightbox('{{ $url }}')">
                @if($canEdit)
                <form method="POST" action="{{ route('medicines.gallery.delete', $medicine) }}" class="absolute top-1.5 right-1.5"
                      onsubmit="return confirm('Remove this photo?')">
                    @csrf @method('DELETE')
                    <input type="hidden" name="index" value="{{ $i }}">
                    <button type="submit" class="w-7 h-7 rounded-lg bg-red-600/90 hover:bg-red-700 text-white flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity shadow-md" title="Remove photo">
                        <i class="fa-solid fa-trash text-[10px]"></i>
                    </button>
                </form>
                @endif
            </div>
            @endforeach
        </div>
        @else
        <div class="text-center py-10 border-2 border-dashed border-gray-200 dark:border-slate-700 rounded-xl">
            <div class="w-14 h-14 bg-emerald-50 dark:bg-slate-800 rounded-2xl flex items-center justify-center mx-auto mb-2">
                <i class="fa-solid fa-images text-emerald-400 dark:text-gray-500 text-xl"></i>
            </div>
            <p class="text-sm text-gray-500 dark:text-gray-400 font-medium">No photos yet</p>
            <p class="text-xs text-gray-400 dark:text-gray-500 mt-0.5">Up to 5 extra photos: box front, back label, pill close-up, etc.</p>
        </div>
        @endif
    </div>
    @endif

    {{-- ── Clinical detail sections ── --}}
    @if($canEdit)
    <div class="flex justify-end">
        <button type="button" onclick="openMedDetailsModal()" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-brand-100 dark:bg-brand-900/40 text-brand-700 dark:text-brand-300 hover:bg-brand-200 dark:hover:bg-brand-900/60 text-sm font-bold transition-colors">
            <i class="fa-solid fa-pen-to-square"></i> Edit Clinical Details
        </button>
    </div>
    @endif
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">

        @if($medicine->description)
        <div class="card p-5 lg:col-span-2">
            <h3 class="font-bold text-gray-900 dark:text-white text-base mb-3 flex items-center gap-2">
                <span class="w-7 h-7 rounded-lg bg-brand-100 dark:bg-brand-900/40 text-brand-600 dark:text-brand-300 inline-flex items-center justify-center">
                    <i class="fa-solid fa-circle-info text-xs"></i>
                </span>
                Description
            </h3>
            <p class="text-base text-gray-800 dark:text-gray-100 leading-relaxed whitespace-pre-line">{{ $medicine->description }}</p>
        </div>
        @endif

        @if($medicine->indications)
        <div class="card p-5">
            <h3 class="font-bold text-gray-900 dark:text-white text-base mb-3 flex items-center gap-2">
                <span class="w-7 h-7 rounded-lg bg-blue-100 dark:bg-blue-900/40 text-blue-600 dark:text-blue-300 inline-flex items-center justify-center">
                    <i class="fa-solid fa-stethoscope text-xs"></i>
                </span>
                Indications
            </h3>
            <p class="text-base text-gray-800 dark:text-gray-100 leading-relaxed whitespace-pre-line">{{ $medicine->indications }}</p>
        </div>
        @endif

        @if($medicine->dosage_instructions)
        <div class="card p-5">
            <h3 class="font-bold text-gray-900 dark:text-white text-base mb-3 flex items-center gap-2">
                <span class="w-7 h-7 rounded-lg bg-cyan-100 dark:bg-cyan-900/40 text-cyan-600 dark:text-cyan-300 inline-flex items-center justify-center">
                    <i class="fa-solid fa-prescription text-xs"></i>
                </span>
                Dosage Instructions
            </h3>
            <p class="text-base text-gray-800 dark:text-gray-100 leading-relaxed whitespace-pre-line">{{ $medicine->dosage_instructions }}</p>
        </div>
        @endif

        @if($medicine->side_effects)
        <div class="card p-5">
            <h3 class="font-bold text-gray-900 dark:text-white text-base mb-3 flex items-center gap-2">
                <span class="w-7 h-7 rounded-lg bg-orange-100 dark:bg-orange-900/40 text-orange-600 dark:text-orange-300 inline-flex items-center justify-center">
                    <i class="fa-solid fa-triangle-exclamation text-xs"></i>
                </span>
                Side Effects
            </h3>
            <p class="text-base text-gray-800 dark:text-gray-100 leading-relaxed whitespace-pre-line">{{ $medicine->side_effects }}</p>
        </div>
        @endif

        @if($medicine->warnings)
        <div class="card p-5 border-l-4 border-red-500 dark:border-red-700">
            <h3 class="font-bold text-gray-900 dark:text-white text-base mb-3 flex items-center gap-2">
                <span class="w-7 h-7 rounded-lg bg-red-100 dark:bg-red-900/40 text-red-600 dark:text-red-300 inline-flex items-center justify-center">
                    <i class="fa-solid fa-ban text-xs"></i>
                </span>
                Warnings &amp; Contraindications
            </h3>
            <p class="text-base text-gray-800 dark:text-gray-100 leading-relaxed whitespace-pre-line">{{ $medicine->warnings }}</p>
        </div>
        @endif

        @if($medicine->storage_instructions)
        <div class="card p-5">
            <h3 class="font-bold text-gray-900 dark:text-white text-base mb-3 flex items-center gap-2">
                <span class="w-7 h-7 rounded-lg bg-purple-100 dark:bg-purple-900/40 text-purple-600 dark:text-purple-300 inline-flex items-center justify-center">
                    <i class="fa-solid fa-box text-xs"></i>
                </span>
                Storage Instructions
            </h3>
            <p class="text-base text-gray-800 dark:text-gray-100 leading-relaxed whitespace-pre-line">{{ $medicine->storage_instructions }}</p>
        </div>
        @endif

        @if(!$medicine->description && !$medicine->indications && !$medicine->dosage_instructions && !$medicine->side_effects && !$medicine->warnings && !$medicine->storage_instructions)
        <div class="card p-5 lg:col-span-2 border-2 border-dashed border-gray-200 dark:border-slate-700 text-center">
            <i class="fa-solid fa-file-circle-question text-gray-300 dark:text-gray-600 text-2xl mb-2"></i>
            <p class="text-sm text-gray-500 dark:text-gray-400 font-medium">No clinical details recorded yet</p>
            @if($canEdit)
            <button type="button" onclick="openMedDetailsModal()" class="mt-3 inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-brand-600 hover:bg-brand-700 text-white text-sm font-bold transition-colors shadow-sm">
                <i class="fa-solid fa-pen-to-square"></i> Add Details Now
            </button>
            @endif
        </div>
        @endif
    </div>

    {{-- ── Quick Dispense (only if active + has stock + permission) ── --}}
    @if($canDisp && !$medicine->isArchivedManually() && !$isExpired && $qty > 0)
    <div class="card p-5">
        <h3 class="font-bold text-gray-900 dark:text-white text-base mb-4 flex items-center gap-2">
            <span class="w-7 h-7 rounded-lg bg-emerald-100 dark:bg-emerald-900/40 text-emerald-600 dark:text-emerald-300 inline-flex items-center justify-center">
                <i class="fa-solid fa-hand-holding-medical text-xs"></i>
            </span>
            Quick Dispense
        </h3>
        <form method="POST" action="{{ route('medicines.dispense', $medicine) }}" class="flex flex-col sm:flex-row gap-2">
            @csrf
            <input type="number" name="quantity" min="1" max="{{ $qty }}" placeholder="Quantity" required
                   class="input sm:w-32">
            <input type="text" name="notes" placeholder="Notes (patient name, reason for dispense)"
                   class="input flex-1">
            <button type="submit" class="btn-primary justify-center">
                <i class="fa-solid fa-hand-holding-medical"></i> Dispense
            </button>
        </form>
    </div>
    @endif

    {{-- ── All Batches + Dispense History ── --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
        <div class="card p-5">
            <h3 class="font-bold text-gray-900 dark:text-white text-base mb-4 flex items-center gap-2">
                <span class="w-7 h-7 rounded-lg bg-indigo-100 dark:bg-indigo-900/40 text-indigo-600 dark:text-indigo-300 inline-flex items-center justify-center">
                    <i class="fa-solid fa-layer-group text-xs"></i>
                </span>
                All Batches
                <span class="text-xs font-medium text-gray-500 dark:text-gray-400">({{ $medicine->inventories->count() }})</span>
            </h3>
            @if($medicine->inventories->count() > 0)
            <div class="space-y-2">
                @foreach($medicine->inventories as $inv)
                <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-slate-800/60 rounded-xl">
                    <div>
                        <p class="font-mono text-sm font-bold text-gray-900 dark:text-white">{{ $inv->batch_number ?? 'No batch #' }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Exp: {{ $inv->expiration_date ? \Carbon\Carbon::parse($inv->expiration_date)->format('M j, Y') : '—' }} &bull; Min: {{ $inv->min_stock_level ?? '—' }}</p>
                    </div>
                    <span class="text-base font-extrabold {{ $inv->quantity <= ($inv->min_stock_level ?? 10) ? 'text-red-600 dark:text-red-300' : 'text-gray-900 dark:text-white' }}">{{ $inv->quantity }}u</span>
                </div>
                @endforeach
            </div>
            @else
            <p class="text-sm text-gray-400 dark:text-gray-500 text-center py-6 italic">No inventory records</p>
            @endif
        </div>

        <div class="card p-5">
            <h3 class="font-bold text-gray-900 dark:text-white text-base mb-4 flex items-center gap-2">
                <span class="w-7 h-7 rounded-lg bg-amber-100 dark:bg-amber-900/40 text-amber-600 dark:text-amber-300 inline-flex items-center justify-center">
                    <i class="fa-solid fa-clock-rotate-left text-xs"></i>
                </span>
                Recent Dispenses
            </h3>
            @if($dispenseLogs->count() > 0)
            <div class="space-y-2 max-h-72 overflow-y-auto pr-1">
                @foreach($dispenseLogs as $log)
                <div class="flex items-start justify-between gap-2 p-3 bg-gray-50 dark:bg-slate-800/60 rounded-xl">
                    <div class="min-w-0">
                        <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ $log->quantity }} units</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400 truncate">{{ $log->dispensedBy?->name ?? '—' }} @if($log->notes) &bull; {{ $log->notes }} @endif</p>
                    </div>
                    <p class="text-xs text-gray-400 dark:text-gray-500 flex-shrink-0 whitespace-nowrap">{{ $log->created_at->diffForHumans(null, true) }} ago</p>
                </div>
                @endforeach
            </div>
            @else
            <p class="text-sm text-gray-400 dark:text-gray-500 text-center py-6 italic">No dispense history yet</p>
            @endif
        </div>
    </div>
</div>

{{-- ── Edit Clinical Details Modal ── --}}
@if($canEdit)
<div id="medDetailsModal" class="hidden fixed inset-0 bg-black/60 z-50 flex items-center justify-center p-4 backdrop-blur-sm">
    <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-2xl w-full max-w-2xl max-h-[92vh] flex flex-col overflow-hidden border-2 border-gray-100 dark:border-slate-700">
        <div class="bg-gradient-to-r from-brand-600 to-brand-700 px-6 py-5 text-white flex items-center justify-between flex-shrink-0">
            <div class="flex items-center gap-3">
                <div class="w-11 h-11 rounded-2xl bg-white/15 flex items-center justify-center backdrop-blur-sm">
                    <i class="fa-solid fa-pen-to-square text-xl"></i>
                </div>
                <div>
                    <h3 class="text-lg font-bold">Edit Clinical Details</h3>
                    <p class="text-xs text-white/80 mt-0.5">{{ $medicine->name }}</p>
                </div>
            </div>
            <button type="button" onclick="closeMedDetailsModal()" class="w-9 h-9 rounded-xl flex items-center justify-center text-white/80 hover:text-white hover:bg-white/20 transition-colors">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <form method="POST" action="{{ route('medicines.update', $medicine) }}" class="px-6 py-5 space-y-4 overflow-y-auto flex-1">
            @csrf @method('PUT')
            {{-- Resend identity so the update validator doesn't fail (it still requires name/type/location_id). --}}
            <input type="hidden" name="name" value="{{ $medicine->name }}">
            <input type="hidden" name="type" value="{{ $medicine->type }}">
            <input type="hidden" name="location_id" value="{{ $medicine->location_id }}">
            <input type="hidden" name="generic_name" value="{{ $medicine->generic_name }}">
            <input type="hidden" name="dosage" value="{{ $medicine->dosage }}">

            <div>
                <label class="label">Description</label>
                <textarea name="description" rows="3" class="input resize-y leading-relaxed" placeholder="Short summary of what this medicine is">{{ $medicine->description }}</textarea>
            </div>
            <div>
                <label class="label flex items-center gap-1.5"><i class="fa-solid fa-stethoscope text-blue-500"></i> Indications</label>
                <textarea name="indications" rows="3" class="input resize-y leading-relaxed" placeholder="What conditions this medicine treats">{{ $medicine->indications }}</textarea>
            </div>
            <div>
                <label class="label flex items-center gap-1.5"><i class="fa-solid fa-prescription text-cyan-500"></i> Dosage Instructions</label>
                <textarea name="dosage_instructions" rows="3" class="input resize-y leading-relaxed" placeholder="How and when to take it">{{ $medicine->dosage_instructions }}</textarea>
            </div>
            <div>
                <label class="label flex items-center gap-1.5"><i class="fa-solid fa-triangle-exclamation text-orange-500"></i> Side Effects</label>
                <textarea name="side_effects" rows="3" class="input resize-y leading-relaxed" placeholder="Common and serious adverse reactions">{{ $medicine->side_effects }}</textarea>
            </div>
            <div>
                <label class="label flex items-center gap-1.5"><i class="fa-solid fa-ban text-red-500"></i> Warnings &amp; Contraindications</label>
                <textarea name="warnings" rows="3" class="input resize-y leading-relaxed" placeholder="Allergies, pregnancy class, interactions, etc.">{{ $medicine->warnings }}</textarea>
            </div>
            <div>
                <label class="label flex items-center gap-1.5"><i class="fa-solid fa-box text-purple-500"></i> Storage Instructions</label>
                <textarea name="storage_instructions" rows="2" class="input resize-y leading-relaxed" placeholder="Temperature, light, dryness, etc.">{{ $medicine->storage_instructions }}</textarea>
            </div>

            <div class="flex justify-between gap-3 pt-3 border-t border-gray-100 dark:border-slate-700">
                <button type="button" onclick="closeMedDetailsModal()" class="btn-secondary">Cancel</button>
                <button type="submit" class="btn-primary"><i class="fa-solid fa-floppy-disk"></i> Save Details</button>
            </div>
        </form>
    </div>
</div>
@endif

{{-- Lightbox for gallery photos --}}
<div id="galleryLightbox" class="hidden fixed inset-0 bg-black/85 z-50 flex items-center justify-center p-4 backdrop-blur-sm" onclick="closeGalleryLightbox()">
    <img id="galleryLightboxImg" src="" alt="" class="max-w-full max-h-full rounded-2xl shadow-2xl">
    <button type="button" onclick="closeGalleryLightbox()"
            class="absolute top-4 right-4 w-10 h-10 rounded-full bg-white/15 hover:bg-white/25 text-white flex items-center justify-center backdrop-blur-sm transition-colors">
        <i class="fa-solid fa-xmark text-lg"></i>
    </button>
</div>

@push('scripts')
<script>
function openGalleryLightbox(url) {
    document.getElementById('galleryLightboxImg').src = url;
    document.getElementById('galleryLightbox').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}
function closeGalleryLightbox() {
    document.getElementById('galleryLightbox').classList.add('hidden');
    document.body.style.overflow = '';
}
function openMedDetailsModal()  { const m = document.getElementById('medDetailsModal'); if (m) { m.classList.remove('hidden'); document.body.style.overflow='hidden'; } }
function closeMedDetailsModal() { const m = document.getElementById('medDetailsModal'); if (m) { m.classList.add('hidden'); document.body.style.overflow=''; } }
document.addEventListener('keydown', e => { if (e.key === 'Escape') { closeGalleryLightbox(); closeMedDetailsModal(); } });
</script>
@endpush
@endsection
