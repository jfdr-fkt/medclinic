@extends('layouts.app')
@section('title', 'Add Medicine')
@section('page-title', 'Add Medicine')

@push('head')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr@4/dist/flatpickr.min.css">
<style>
/* ── Flatpickr brand theme ── */
.flatpickr-calendar {
    border-radius: 1rem !important;
    border: 2px solid #e5e7eb !important;
    box-shadow: 0 10px 30px rgba(0,0,0,.14) !important;
    font-family: inherit !important;
    padding: 6px !important;
}
.flatpickr-calendar.arrowTop:before, .flatpickr-calendar.arrowTop:after { border-bottom-color: #e5e7eb !important; }
.flatpickr-months { padding-top: 4px !important; }
.flatpickr-month { color: #0f172a !important; height: 38px !important; }
.flatpickr-current-month { font-weight: 700 !important; font-size: .95rem !important; padding-top: 6px !important; }
.flatpickr-current-month .flatpickr-monthDropdown-months,
.flatpickr-current-month input.cur-year { color: #0f172a !important; font-weight: 700 !important; }
.flatpickr-weekday { color: #6b7280 !important; font-weight: 700 !important; font-size: .72rem !important; text-transform: uppercase; letter-spacing: .04em; }
.flatpickr-day {
    border-radius: .65rem !important;
    color: #1f2937 !important;
    font-weight: 500 !important;
    transition: background .12s, color .12s;
}
.flatpickr-day:hover, .flatpickr-day.prevMonthDay:hover, .flatpickr-day.nextMonthDay:hover {
    background: #ecfdf5 !important; border-color: transparent !important; color: #065f46 !important;
}
.flatpickr-day.today {
    border-color: #0d9488 !important; color: #0d9488 !important; font-weight: 700 !important;
}
.flatpickr-day.selected, .flatpickr-day.selected:hover, .flatpickr-day.selected.today {
    background: #0d9488 !important; border-color: #0d9488 !important; color: #fff !important;
    box-shadow: 0 2px 8px rgba(13,148,136,.35) !important;
}
.flatpickr-day.flatpickr-disabled, .flatpickr-day.flatpickr-disabled:hover { color: #cbd5e1 !important; background: transparent !important; }
.flatpickr-day.prevMonthDay, .flatpickr-day.nextMonthDay { color: #cbd5e1 !important; }
.flatpickr-prev-month, .flatpickr-next-month { color: #6b7280 !important; fill: #6b7280 !important; padding: 8px !important; }
.flatpickr-prev-month:hover svg, .flatpickr-next-month:hover svg { fill: #0d9488 !important; }
/* Dark mode for flatpickr */
.dark .flatpickr-calendar { background: #1a2438 !important; border-color: #2d3a52 !important; box-shadow: 0 10px 30px rgba(0,0,0,.5) !important; }
.dark .flatpickr-calendar.arrowTop:before, .dark .flatpickr-calendar.arrowTop:after { border-bottom-color: #2d3a52 !important; }
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

/* ── Page-scoped input style: bigger, more rounded, high-contrast ── */
.add-input {
    display: block; width: 100%;
    padding: 0.875rem 1rem;           /* py-3.5 */
    border: 2px solid #d1d5db;       /* gray-300 */
    border-radius: 1rem;             /* rounded-2xl */
    font-size: 0.9375rem;            /* ~15px */
    color: #111827;
    background: #ffffff;
    outline: none;
    transition: border-color .15s, box-shadow .15s;
    line-height: 1.5;
}
.add-input:focus {
    border-color: #0d9488;
    box-shadow: 0 0 0 3px rgba(13,148,136,.18);
}
.add-input::placeholder { color: #9ca3af; }

/* Dark mode inputs */
.dark .add-input {
    background: #0f1a2e !important;
    border-color: #3f4d6b !important;
    color: #f1f5f9 !important;
}
.dark .add-input:focus { border-color: #14b8a6 !important; box-shadow: 0 0 0 3px rgba(20,184,166,.2) !important; }
.dark .add-input::placeholder { color: #64748b !important; }

/* Custom select: remove browser arrow, replace with centred chevron */
select.add-input {
    cursor: pointer;
    appearance: none;
    -webkit-appearance: none;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='20' height='20' viewBox='0 0 20 20' fill='none'%3E%3Cpath d='M5 7.5L10 12.5L15 7.5' stroke='%236b7280' stroke-width='1.75' stroke-linecap='round' stroke-linejoin='round'/%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 0.9rem center;
    background-size: 1.1rem;
    padding-right: 2.5rem;
    transition: border-color .2s, box-shadow .2s, background-color .15s;
}
select.add-input:focus {
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='20' height='20' viewBox='0 0 20 20' fill='none'%3E%3Cpath d='M5 7.5L10 12.5L15 7.5' stroke='%230d9488' stroke-width='1.75' stroke-linecap='round' stroke-linejoin='round'/%3E%3C/svg%3E");
}
.dark select.add-input {
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='20' height='20' viewBox='0 0 20 20' fill='none'%3E%3Cpath d='M5 7.5L10 12.5L15 7.5' stroke='%2394a3b8' stroke-width='1.75' stroke-linecap='round' stroke-linejoin='round'/%3E%3C/svg%3E") !important;
}

/* ── Section containers ── */
.form-section {
    border-radius: 1.25rem;
    border-width: 2px;
    border-style: solid;
    overflow: visible;    /* allows custom dropdowns to overflow the section boundary */
}
.form-section-header {
    padding: 0.875rem 1.25rem;
    display: flex;
    align-items: center;
    gap: 0.625rem;
    border-bottom-width: 2px;
    border-bottom-style: solid;
    border-radius: calc(1.25rem - 2px) calc(1.25rem - 2px) 0 0;  /* clips header bg to rounded top corners */
}
.form-section-body { padding: 1.25rem; }

/* Section colours */
.section-blue  { border-color: #bfdbfe; }
.section-blue  .form-section-header { background: #eff6ff; border-color: #bfdbfe; }
.section-green { border-color: #a7f3d0; }
.section-green .form-section-header { background: #ecfdf5; border-color: #a7f3d0; }
.section-amber { border-color: #fde68a; }
.section-amber .form-section-header { background: #fffbeb; border-color: #fde68a; }

.dark .section-blue  { border-color: rgba(59,130,246,.3) !important; }
.dark .section-blue  .form-section-header { background: rgba(59,130,246,.1) !important; border-color: rgba(59,130,246,.3) !important; }
.dark .section-green { border-color: rgba(16,185,129,.3) !important; }
.dark .section-green .form-section-header { background: rgba(16,185,129,.1) !important; border-color: rgba(16,185,129,.3) !important; }
.dark .section-amber { border-color: rgba(245,158,11,.3) !important; }
.dark .section-amber .form-section-header { background: rgba(245,158,11,.1) !important; border-color: rgba(245,158,11,.3) !important; }

/* ── Card ── */
.add-card {
    background: #ffffff;
    border: 2px solid #e5e7eb;
    border-radius: 1.25rem;
    overflow: hidden;
    box-shadow: 0 1px 3px rgba(0,0,0,.06);
}
.dark .add-card {
    background: #1a2438 !important;
    border-color: #2d3a52 !important;
}

/* ── Drop zone ── */
.drop-zone {
    border: 2.5px dashed #d1d5db;
    border-radius: 1rem;
    cursor: pointer;
    transition: all .2s;
}
.drop-zone:hover, .drop-zone.dragover {
    border-color: #0d9488;
    background: rgba(13,148,136,.04);
}
.dark .drop-zone { border-color: #3f4d6b !important; }
.dark .drop-zone:hover, .dark .drop-zone.dragover { border-color: #14b8a6 !important; background: rgba(20,184,166,.08) !important; }

/* ── Scan label ── */
.add-label {
    display: block;
    font-size: 0.75rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .06em;
    color: #374151;
    margin-bottom: 0.5rem;
}
.dark .add-label { color: #94a3b8 !important; }

/* ── Big save button ── */
.save-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.625rem;
    width: 100%;
    padding: 1.1rem 2rem;
    background: linear-gradient(135deg, #0d9488 0%, #0f766e 100%);
    color: #ffffff;
    font-size: 1.0625rem;
    font-weight: 700;
    border-radius: 1rem;
    border: none;
    cursor: pointer;
    box-shadow: 0 4px 14px rgba(13,148,136,.35);
    transition: all .2s;
    letter-spacing: .01em;
}
.save-btn:hover { background: linear-gradient(135deg, #0f766e 0%, #115e59 100%); box-shadow: 0 6px 20px rgba(13,148,136,.45); transform: translateY(-1px); }
.save-btn:active { transform: translateY(0); }

/* ── Camera/upload scan btn ── */
.scan-action-btn {
    display: flex; align-items: center; justify-content: center; gap: .5rem;
    width: 100%;
    padding: .9rem 1.25rem;
    font-size: .9375rem;
    font-weight: 700;
    border-radius: 1rem;
    border: none;
    cursor: pointer;
    transition: all .15s;
    letter-spacing: .01em;
}
.scan-action-btn.green  { background: #059669; color: #fff; box-shadow: 0 3px 10px rgba(5,150,105,.3); }
.scan-action-btn.green:hover  { background: #047857; }
.scan-action-btn.red    { background: #dc2626; color: #fff; box-shadow: 0 3px 10px rgba(220,38,38,.3); }
.scan-action-btn.red:hover    { background: #b91c1c; }

/* ── Success banner ── */
.success-banner {
    display: flex; align-items: center; justify-content: space-between; gap: 1rem;
    padding: 1rem 1.25rem;
    background: #ecfdf5;
    border: 2px solid #6ee7b7;
    border-radius: 1.25rem;
    cursor: pointer;
    transition: background .2s;
}
.success-banner:hover { background: #d1fae5; }
.dark .success-banner { background: rgba(16,185,129,.12) !important; border-color: rgba(16,185,129,.45) !important; }
.dark .success-banner:hover { background: rgba(16,185,129,.2) !important; }

/* ── Custom dropdown (replaces native select) ── */
.custom-select { position: relative; }
.custom-select-trigger {
    display: flex !important;
    align-items: center;
    justify-content: space-between;
    gap: .5rem;
    cursor: pointer;
    text-align: left;
    user-select: none;
    background-image: none !important;
}
.custom-select-trigger .cs-value { flex: 1; min-width: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.custom-select-trigger .cs-chevron {
    flex-shrink: 0; width: 1.125rem; height: 1.125rem;
    color: #6b7280; transition: transform .2s, color .2s;
}
.custom-select.open .cs-chevron { transform: rotate(180deg); color: #0d9488; }
.custom-select.open .custom-select-trigger { border-color: #0d9488; box-shadow: 0 0 0 3px rgba(13,148,136,.18); }
.dark .custom-select.open .custom-select-trigger { border-color: #14b8a6 !important; box-shadow: 0 0 0 3px rgba(20,184,166,.2) !important; }

.custom-select-panel {
    position: absolute; top: calc(100% + 6px); left: 0; right: 0; z-index: 200;
    background: #fff;
    border: 2px solid #e5e7eb;
    border-radius: 1rem;
    box-shadow: 0 10px 30px rgba(0,0,0,.14);
    overflow: hidden;
    animation: csDropIn .15s ease;
}
@keyframes csDropIn {
    from { opacity: 0; transform: translateY(-6px); }
    to   { opacity: 1; transform: translateY(0); }
}
.dark .custom-select-panel {
    background: #1a2438 !important;
    border-color: #2d3a52 !important;
    box-shadow: 0 10px 30px rgba(0,0,0,.5) !important;
}
.custom-select-option {
    padding: .75rem 1rem;
    cursor: pointer;
    font-size: .9375rem;
    color: #111827;
    transition: background .1s;
}
.custom-select-option:hover { background: #f3f4f6; }
.custom-select-option.selected { background: #ecfdf5; color: #065f46; font-weight: 700; }
.custom-select-option.placeholder { color: #9ca3af; font-style: italic; }
.dark .custom-select-option { color: #e2e8f0; }
.dark .custom-select-option:hover { background: #243050; }
.dark .custom-select-option.selected { background: rgba(16,185,129,.18) !important; color: #6ee7b7 !important; }
.dark .custom-select-option.placeholder { color: #64748b; }

</style>
@endpush

@section('content')
<div class="space-y-6" style="max-width:860px; margin:0 auto;">

    {{-- Page header --}}
    <div class="flex items-start justify-between flex-wrap gap-3">
        <div>
            <h1 class="text-2xl font-extrabold text-gray-900 dark:text-white">Add Medicine</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">
                Scan a barcode/QR code, upload or paste an image, type a code, or fill in the form manually
            </p>
        </div>
        <a href="{{ route('medicines.index') }}"
           class="inline-flex items-center gap-2 px-4 py-2.5 rounded-2xl border-2 border-gray-200 dark:border-slate-600
                  text-sm font-semibold text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-slate-700 transition-colors">
            <i class="fa-solid fa-arrow-left text-gray-400"></i> Medicines
        </a>
    </div>

    {{-- ── Success banner (shown after save) ── --}}
    <a id="successBanner" href="#" class="success-banner hidden" onclick="return true;">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-2xl bg-emerald-500 flex items-center justify-center flex-shrink-0">
                <i class="fa-solid fa-circle-check text-white text-lg"></i>
            </div>
            <div>
                <p class="font-bold text-emerald-800 dark:text-emerald-300 text-sm" id="successTitle">Saved!</p>
                <p class="text-xs text-emerald-600 dark:text-emerald-400" id="successSub">Click to view and highlight in Medicines</p>
            </div>
        </div>
        <i class="fa-solid fa-arrow-right text-emerald-500 text-lg"></i>
    </a>

    {{-- ── (1) Quick barcode / text lookup bar ── --}}
    <div class="add-card p-5">
        <p class="add-label mb-3"><i class="fa-solid fa-keyboard text-brand-500 mr-1.5"></i> Type a barcode or code manually</p>
        <div class="flex flex-col sm:flex-row gap-3">
            <div class="relative flex-1">
                <i class="fa-solid fa-barcode absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 text-lg pointer-events-none"></i>
                <input type="text" id="manualCode"
                       placeholder="Type barcode number, or paste JSON / pipe-separated text…"
                       class="add-input pl-11"
                       style="font-family:ui-monospace,monospace; letter-spacing:.04em;"
                       onkeydown="if(event.key==='Enter'){processManualCode();}">
            </div>
            <button type="button" onclick="processManualCode()"
                    class="sm:w-40 inline-flex items-center justify-center gap-2 px-5 py-3.5
                           bg-brand-600 hover:bg-brand-700 text-white text-sm font-bold
                           rounded-2xl transition-colors shadow-sm flex-shrink-0">
                <i class="fa-solid fa-magnifying-glass"></i> Lookup
            </button>
        </div>
    </div>

    {{-- ── (2) Scanner + Upload ── --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">

        {{-- Camera --}}
        <div class="add-card overflow-hidden">
            <div class="px-5 py-3.5 border-b-2 border-gray-100 dark:border-slate-700 flex items-center gap-2">
                <div class="w-8 h-8 rounded-xl bg-brand-100 dark:bg-brand-900/40 flex items-center justify-center">
                    <i class="fa-solid fa-camera text-brand-600 text-sm"></i>
                </div>
                <h2 class="font-bold text-gray-800 dark:text-white text-sm">Scanner</h2>
                <span class="ml-auto text-[11px] text-gray-400">auto-detects QR & barcodes</span>
            </div>
            <div class="p-5 space-y-4">
                <div id="reader"
                     class="rounded-2xl overflow-hidden bg-gray-900 dark:bg-black border-2 border-gray-200 dark:border-slate-700"
                     style="min-height:240px; display:flex; align-items:center; justify-content:center;">
                    <div id="cameraPlaceholder" class="text-center p-6">
                        <div class="w-16 h-16 rounded-2xl bg-gray-800 flex items-center justify-center mx-auto mb-3">
                            <i class="fa-solid fa-camera text-gray-500 text-2xl"></i>
                        </div>
                        <p class="text-gray-400 text-sm font-medium">Camera preview will appear here</p>
                        <p class="text-gray-600 text-xs mt-1">Press Start Scanner below</p>
                    </div>
                </div>
                <div class="flex flex-col gap-2">
                    <button onclick="startScanner()" id="startBtn" class="scan-action-btn green">
                        <i class="fa-solid fa-play"></i> Start Scanner
                    </button>
                    <button onclick="stopScanner()" id="stopBtn" class="scan-action-btn red hidden">
                        <i class="fa-solid fa-stop"></i> Stop Scanner
                    </button>
                </div>
            </div>
        </div>

        {{-- Upload / Paste --}}
        <div class="add-card overflow-hidden flex flex-col">
            <div class="px-5 py-3.5 border-b-2 border-gray-100 dark:border-slate-700 flex items-center gap-2">
                <div class="w-8 h-8 rounded-xl bg-purple-100 dark:bg-purple-900/40 flex items-center justify-center">
                    <i class="fa-solid fa-image text-purple-600 text-sm"></i>
                </div>
                <h2 class="font-bold text-gray-800 dark:text-white text-sm">Upload / Paste Image</h2>
            </div>
            <div class="p-5 flex flex-col flex-1 gap-3">
                <label id="qrDropZone" for="qrFile" class="drop-zone flex flex-col items-center justify-center p-8 text-center flex-1"
                       style="min-height:160px;" ondragover="onDragOver(event)" ondrop="onDrop(event)" ondragleave="onDragLeave(event)">
                    <div class="w-14 h-14 rounded-2xl bg-purple-50 dark:bg-purple-900/30 flex items-center justify-center mb-3">
                        <i class="fa-solid fa-cloud-arrow-up text-purple-400 text-2xl"></i>
                    </div>
                    <p class="text-sm font-semibold text-gray-700 dark:text-white">Click, drag & drop, or paste</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">a QR code or barcode image</p>
                    <input type="file" id="qrFile" accept="image/*" class="hidden">
                </label>
                <div id="fileScanStatus" class="hidden rounded-xl px-4 py-2.5 text-xs font-medium text-center"></div>
                <div id="file-reader" class="hidden"></div>
            </div>
        </div>
    </div>

    {{-- ── (3) Format helper ── --}}
    <details class="add-card" id="helperDetails">
        <summary class="cursor-pointer px-5 py-4 font-bold text-sm text-brand-700 dark:text-brand-300 flex items-center gap-2 select-none list-none">
            <i class="fa-solid fa-circle-info"></i>
            How to generate codes that auto-fill the form
            <i class="fa-solid fa-chevron-down ml-auto text-xs text-gray-400"></i>
        </summary>
        <div class="px-5 pb-5 pt-0 space-y-4 text-sm text-gray-700 dark:text-gray-300 border-t-2 border-gray-100 dark:border-slate-700">
            <p class="pt-4">Generate a QR code with any app using one of these text formats, then scan or upload it here — the system auto-detects the format and fills in the form automatically.</p>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="rounded-2xl border-2 border-blue-100 dark:border-blue-900/40 overflow-hidden">
                    <div class="px-4 py-2.5 bg-blue-50 dark:bg-blue-900/20 border-b-2 border-blue-100 dark:border-blue-900/40">
                        <p class="font-bold text-blue-700 dark:text-blue-300 text-xs flex items-center gap-1.5"><i class="fa-solid fa-code"></i> JSON (recommended)</p>
                    </div>
                    <pre class="p-3 text-[11px] font-mono overflow-x-auto text-gray-800 dark:text-gray-100 bg-white dark:bg-slate-900" style="white-space:pre-wrap;">{
  "name": "Amoxicillin 500mg",
  "generic_name": "Amoxicillin",
  "brand_names": "Amoxil, Trimox",
  "form": "tablet",
  "category": "otc",
  "quantity": 100,
  "unit": "pieces",
  "batch": "BTH-2024-001",
  "expiry": "2026-12-31"
}</pre>
                </div>
                <div class="rounded-2xl border-2 border-purple-100 dark:border-purple-900/40 overflow-hidden">
                    <div class="px-4 py-2.5 bg-purple-50 dark:bg-purple-900/20 border-b-2 border-purple-100 dark:border-purple-900/40">
                        <p class="font-bold text-purple-700 dark:text-purple-300 text-xs flex items-center gap-1.5"><i class="fa-solid fa-bars-staggered"></i> Pipe-separated (shorter)</p>
                    </div>
                    <div class="p-3 bg-white dark:bg-slate-900 space-y-2">
                        <pre class="text-[11px] font-mono text-gray-500 dark:text-gray-400">name | form | category | qty | unit | batch | expiry</pre>
                        <pre class="text-[11px] font-mono text-gray-800 dark:text-gray-100 bg-gray-50 dark:bg-slate-800 rounded-xl p-2 overflow-x-auto">Amoxicillin 500mg|tablet|otc|100|pieces|BTH-2024-001|2026-12-31</pre>
                    </div>
                </div>
            </div>
            <div class="rounded-2xl border-2 border-gray-100 dark:border-slate-700 p-4 text-xs space-y-1.5">
                <p class="font-bold text-gray-700 dark:text-white text-sm mb-2">Allowed values per field</p>
                <p><span class="font-semibold text-gray-800 dark:text-gray-200">form:</span> <span class="text-gray-500 dark:text-gray-400">tablet · capsule · syrup · injection · cream · other</span></p>
                <p><span class="font-semibold text-gray-800 dark:text-gray-200">category:</span> <span class="text-gray-500 dark:text-gray-400">otc · prescription (or rx) · controlled</span></p>
                <p><span class="font-semibold text-gray-800 dark:text-gray-200">unit:</span> <span class="text-gray-500 dark:text-gray-400">pieces · bottles · boxes · ml</span></p>
                <p><span class="font-semibold text-gray-800 dark:text-gray-200">expiry:</span> <span class="text-gray-500 dark:text-gray-400">YYYY-MM-DD</span></p>
                <p class="pt-1 text-gray-400 dark:text-gray-500">Plain barcode numbers still work — they look up an existing medicine by barcode.</p>
                <p class="text-gray-400 dark:text-gray-500">A plain medicine name (not numeric) gets placed in the Name field automatically so you just fill in the rest.</p>
            </div>
        </div>
    </details>

    {{-- ── (4) Medicine Details Form ── --}}
    <form id="medicineForm" enctype="multipart/form-data" class="space-y-5">
        @csrf

        {{-- Section: Identity --}}
        <div class="form-section section-blue">
            <div class="form-section-header">
                <div class="w-8 h-8 rounded-xl bg-blue-500 flex items-center justify-center flex-shrink-0">
                    <i class="fa-solid fa-tag text-white text-sm"></i>
                </div>
                <div>
                    <p class="font-bold text-blue-800 dark:text-blue-200 text-sm">Identity</p>
                    <p class="text-[11px] text-blue-600 dark:text-blue-400">Name, form, and category</p>
                </div>
                <button type="button" onclick="clearCode()" class="ml-auto text-gray-400 hover:text-red-500 transition-colors" title="Clear all">
                    <i class="fa-solid fa-rotate-left text-sm"></i>
                </button>
            </div>
            <div class="form-section-body space-y-4">

                {{-- Medicine picture --}}
                <div>
                    <label class="add-label">Medicine Picture <span class="normal-case font-normal text-gray-400">(optional)</span></label>
                    <div class="flex items-center gap-4">
                        <div id="imgPreview"
                             class="w-24 h-24 rounded-2xl border-2 border-dashed border-gray-300 dark:border-slate-600
                                    bg-gray-50 dark:bg-slate-800 flex items-center justify-center overflow-hidden flex-shrink-0
                                    cursor-pointer hover:border-brand-400 transition-colors"
                             onclick="document.getElementById('medImage').click()"
                             title="Click or paste an image here">
                            <div class="text-center">
                                <i class="fa-solid fa-image text-gray-300 dark:text-gray-600 text-2xl"></i>
                            </div>
                        </div>
                        <div class="flex-1 space-y-2">
                            <label for="medImage"
                                   class="inline-flex items-center gap-2 px-4 py-2.5 rounded-2xl border-2 border-gray-200 dark:border-slate-600
                                          text-sm font-semibold text-gray-700 dark:text-gray-200 bg-white dark:bg-slate-800
                                          hover:bg-gray-50 dark:hover:bg-slate-700 cursor-pointer transition-colors">
                                <i class="fa-solid fa-upload text-gray-400"></i> Choose Photo
                            </label>
                            <input type="file" id="medImage" name="image" accept="image/*" class="hidden" onchange="setMedImage(this.files[0])">
                            <button type="button" id="removeImgBtn" onclick="removeMedImage()"
                                    class="hidden ml-2 inline-flex items-center gap-1.5 px-3 py-2 rounded-xl text-xs font-semibold text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20">
                                <i class="fa-solid fa-trash"></i> Remove
                            </button>
                            <p class="text-xs text-gray-400 dark:text-gray-500">JPG / PNG · max 4 MB</p>
                        </div>
                    </div>
                </div>

                {{-- Scanned code display (shown after scanning) --}}
                <div id="scannedCodeRow" class="hidden rounded-2xl bg-blue-50 dark:bg-blue-900/20 border-2 border-blue-100 dark:border-blue-900/40 px-4 py-3 flex items-center gap-3">
                    <i class="fa-solid fa-qrcode text-blue-500 flex-shrink-0"></i>
                    <div class="flex-1 min-w-0">
                        <p class="text-[10px] font-bold text-blue-600 dark:text-blue-400 uppercase tracking-wide">Scanned code</p>
                        <p id="scannedCodeDisplay" class="text-xs font-mono text-blue-800 dark:text-blue-200 truncate">—</p>
                    </div>
                </div>
                <input type="hidden" id="scannedCode" name="scanned_raw_code">

                {{-- Medicine Name --}}
                <div>
                    <label class="add-label" for="medName">Medicine Name <span class="text-red-500">*</span></label>
                    <input type="text" id="medName" name="name" required class="add-input" placeholder="e.g. Amoxicillin 500mg">
                </div>

                {{-- Generic Name --}}
                <div>
                    <label class="add-label" for="genericName">
                        Generic Name
                        <span class="normal-case font-normal text-gray-400">(optional)</span>
                    </label>
                    <input type="text" id="genericName" name="generic_name" class="add-input"
                           placeholder="e.g. Amoxicillin">
                    <p class="text-xs text-gray-400 dark:text-gray-500 mt-1.5">
                        <strong>INN</strong> = International Nonproprietary Name — the official scientific name shared worldwide regardless of brand.
                        Example: "Paracetamol" is the INN; "Biogesic" and "Tylenol" are brand names.
                    </p>
                </div>

                {{-- Brand Names --}}
                <div>
                    <label class="add-label" for="brandNames">
                        Brand Names / Aliases
                        <span class="normal-case font-normal text-gray-400">(optional)</span>
                    </label>
                    <input type="text" id="brandNames" name="brand_names" class="add-input"
                           placeholder="e.g. Amoxil, Trimox, Biomox — separate with commas">
                    <p class="text-xs text-gray-400 dark:text-gray-500 mt-1.5">Trade or brand names for this medicine.</p>
                </div>

                {{-- Dosage Form + Legal Category --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="add-label">Dosage Form <span class="text-red-500">*</span></label>
                        <select name="dosage_form" id="formSelect" required class="add-input" onchange="onFormChange()">
                            <option value="">Select a form…</option>
                            <option value="tablet">🔵 Tablet</option>
                            <option value="capsule">💊 Capsule</option>
                            <option value="syrup">🧴 Syrup / Liquid</option>
                            <option value="injection">💉 Injection</option>
                            <option value="cream">🧴 Cream / Ointment</option>
                            <option value="other">✏️ Other — describe below</option>
                        </select>
                    </div>
                    <div>
                        <label class="add-label">Legal Category</label>
                        <select name="category" class="add-input">
                            <option value="otc">OTC — Over-the-Counter</option>
                            <option value="prescription">Rx — Prescription</option>
                            <option value="controlled">Controlled Substance</option>
                        </select>
                    </div>
                </div>

                {{-- Other form note --}}
                <div id="otherNoteRow" class="hidden">
                    <label class="add-label">Describe the form <span class="text-red-500">*</span></label>
                    <input type="text" name="form_other_note" id="formOtherNote" class="add-input"
                           placeholder="e.g. Suppository, Inhaler, Lozenge, Transdermal Patch…">
                </div>

            </div>
        </div>

        {{-- Section: Stock & Storage --}}
        <div class="form-section section-green">
            <div class="form-section-header">
                <div class="w-8 h-8 rounded-xl bg-emerald-500 flex items-center justify-center flex-shrink-0">
                    <i class="fa-solid fa-warehouse text-white text-sm"></i>
                </div>
                <div>
                    <p class="font-bold text-emerald-800 dark:text-emerald-200 text-sm">Stock & Storage</p>
                    <p class="text-[11px] text-emerald-600 dark:text-emerald-400">Quantity, location, batch, expiry</p>
                </div>
            </div>
            <div class="form-section-body space-y-4">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="add-label">Quantity <span class="text-red-500">*</span></label>
                        <input type="number" name="quantity" required min="1" class="add-input" placeholder="e.g. 100">
                    </div>
                    <div>
                        <label class="add-label">Unit</label>
                        <select name="unit" class="add-input">
                            <option value="pieces">Pieces / pcs</option>
                            <option value="bottles">Bottles</option>
                            <option value="boxes">Boxes</option>
                            <option value="ml">Milliliters / mL</option>
                        </select>
                    </div>
                    <div class="sm:col-span-2">
                        <label class="add-label">Storage Location <span class="text-red-500">*</span></label>
                        @if($locations->isEmpty())
                            <div class="add-input flex items-center justify-between gap-3" style="background:#fef3c7;border-color:#fde68a;color:#92400e;">
                                <span class="text-sm">No storage locations defined yet.</span>
                                @if(auth()->user()->can_('medicines.locations'))
                                <a href="{{ route('medicines.locations.index') }}" class="text-xs font-bold underline">Add one →</a>
                                @endif
                            </div>
                        @else
                        <select name="location_id" required class="add-input">
                            <option value="">Select where to store this medicine…</option>
                            @foreach($locations as $loc)
                                <option value="{{ $loc->id }}">{{ $loc->full_location }}</option>
                            @endforeach
                        </select>
                        @endif
                    </div>
                    <div>
                        <label class="add-label">Batch Number</label>
                        <input type="text" name="batch_number" class="add-input" placeholder="e.g. BTH-2024-001">
                    </div>
                    <div>
                        <label class="add-label">Expiry Date <span class="text-red-500">*</span></label>
                        <input type="text" name="expiry_date" id="expiryDate" required class="add-input" placeholder="Pick a date…" autocomplete="off">
                    </div>
                </div>
            </div>
        </div>

        {{-- Section: Notes --}}
        <div class="form-section section-amber">
            <div class="form-section-header">
                <div class="w-8 h-8 rounded-xl bg-amber-500 flex items-center justify-center flex-shrink-0">
                    <i class="fa-solid fa-note-sticky text-white text-sm"></i>
                </div>
                <div>
                    <p class="font-bold text-amber-800 dark:text-amber-200 text-sm">Notes & Description</p>
                    <p class="text-[11px] text-amber-600 dark:text-amber-400">Indications, side effects, special instructions</p>
                </div>
            </div>
            <div class="form-section-body">
                <textarea name="notes" rows="6" class="add-input resize-y"
                          placeholder="Indications, contraindications, side effects, dosage instructions, special storage requirements, interactions to watch out for…"
                          style="resize:vertical; min-height:120px;"></textarea>
            </div>
        </div>

        {{-- Actions --}}
        <div class="add-card p-5 flex flex-col sm:flex-row items-center gap-3">
            <button type="button" onclick="clearCode()"
                    class="sm:w-40 inline-flex items-center justify-center gap-2 px-5 py-3.5
                           rounded-2xl border-2 border-gray-200 dark:border-slate-600
                           text-sm font-semibold text-gray-600 dark:text-gray-300
                           bg-white dark:bg-slate-800 hover:bg-gray-50 dark:hover:bg-slate-700 transition-colors flex-shrink-0">
                <i class="fa-solid fa-rotate-left"></i> Clear All
            </button>
            <button type="submit" class="save-btn flex-1">
                <i class="fa-solid fa-floppy-disk text-xl"></i>
                Save to Inventory
            </button>
        </div>

        {{-- In-page toast (transient, non-navigating) --}}
        <div id="pageToast" class="hidden rounded-2xl border-2 px-5 py-3.5 flex items-center gap-3 text-sm font-medium">
            <i id="toastIcon" class="text-lg flex-shrink-0"></i>
            <span id="toastMsg"></span>
        </div>

    </form>

</div>

<script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr@4"></script>
<script>
// ═══════════════════════════════════════════════════════
// Globals
// ═══════════════════════════════════════════════════════
let html5QrcodeScanner = null;
let isScanning = false;
let pastedMedImageFile = null;  // holds paste-to-medicine-image file

// ═══════════════════════════════════════════════════════
// Camera
// ═══════════════════════════════════════════════════════
function startScanner() {
    document.getElementById('cameraPlaceholder')?.remove();
    html5QrcodeScanner = new Html5Qrcode('reader');
    html5QrcodeScanner.start(
        { facingMode: 'environment' },
        { fps: 10 },           // full frame — works for wide barcodes AND QR codes
        onScanSuccess,
        () => {}
    ).then(() => {
        isScanning = true;
        document.getElementById('startBtn').classList.add('hidden');
        document.getElementById('stopBtn').classList.remove('hidden');
    }).catch(err => {
        console.error(err);
        showToast('error', 'Camera access denied. Please grant permission and use HTTPS or localhost.');
    });
}

function stopScanner() {
    if (html5QrcodeScanner) {
        html5QrcodeScanner.stop().then(() => {
            isScanning = false;
            document.getElementById('startBtn').classList.remove('hidden');
            document.getElementById('stopBtn').classList.add('hidden');
            html5QrcodeScanner.clear();
        }).catch(() => {});
    }
}

// ═══════════════════════════════════════════════════════
// File upload scan (click or drop)
// ═══════════════════════════════════════════════════════
document.getElementById('qrFile').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) scanImageFile(file);
    e.target.value = '';
});

function onDragOver(e) {
    e.preventDefault();
    document.getElementById('qrDropZone').classList.add('dragover');
}
function onDragLeave(e) {
    document.getElementById('qrDropZone').classList.remove('dragover');
}
function onDrop(e) {
    e.preventDefault();
    document.getElementById('qrDropZone').classList.remove('dragover');
    const file = [...e.dataTransfer.files].find(f => f.type.startsWith('image/'));
    if (file) scanImageFile(file);
}

function scanImageFile(file) {
    const status = document.getElementById('fileScanStatus');
    status.className = 'rounded-xl px-4 py-2.5 text-xs font-medium text-center bg-gray-50 dark:bg-slate-800 text-gray-600 dark:text-gray-300 border-2 border-gray-200 dark:border-slate-700';
    status.classList.remove('hidden');
    status.textContent = `Scanning "${file.name}"…`;

    new Html5Qrcode('file-reader', false).scanFile(file, false)
        .then(decoded => {
            status.className = 'rounded-xl px-4 py-2.5 text-xs font-medium text-center bg-emerald-50 dark:bg-emerald-900/20 text-emerald-700 dark:text-emerald-300 border-2 border-emerald-200 dark:border-emerald-700';
            status.textContent = `✓ Code detected: ${decoded.length > 60 ? decoded.slice(0,60)+'…' : decoded}`;
            onScanSuccess(decoded);
        })
        .catch(() => {
            status.className = 'rounded-xl px-4 py-2.5 text-xs font-medium text-center bg-red-50 dark:bg-red-900/20 text-red-700 dark:text-red-300 border-2 border-red-200 dark:border-red-700';
            status.textContent = '✗ No QR or barcode found in the image. Try a clearer photo.';
        });
}

// ═══════════════════════════════════════════════════════
// Global paste handler
//   • Image with a scannable code  → scan it
//   • Image without a code         → use as medicine photo
//   • Text paste in the manual box → handled natively
// ═══════════════════════════════════════════════════════
document.addEventListener('paste', function(e) {
    const imgItem = [...(e.clipboardData?.items || [])].find(i => i.type.startsWith('image/'));
    if (!imgItem) return;

    // Don't hijack if user is focused on the medicine image preview area
    const active = document.activeElement;
    if (active && (active.id === 'medImage' || active.id === 'imgPreview')) return;

    e.preventDefault();
    const file = imgItem.getAsFile();
    if (!file) return;

    // Try to decode as QR/barcode first
    new Html5Qrcode('file-reader', false).scanFile(file, false)
        .then(decoded => {
            showToast('success', `Pasted image scanned!`);
            onScanSuccess(decoded);
        })
        .catch(() => {
            // No code in it — treat as medicine photo
            setMedImage(file);
            showToast('info', 'Image pasted as medicine photo. No QR/barcode detected in it.');
        });
});

// ═══════════════════════════════════════════════════════
// Structured-text parsing (JSON / pipe / plain)
// ═══════════════════════════════════════════════════════
const FIELD_ALIASES = {
    name:        ['name','title','medicine','medicine_name','medicineName'],
    genericName: ['generic_name','genericName','generic','inn','active_ingredient','activeIngredient'],
    brandNames:  ['brand_names','brandNames','brands','trade_names','tradeNames','aliases','brand'],
    form:        ['form','dosage_form','dosageForm','type'],
    category:    ['category','legal','legal_category','legalCategory','class'],
    quantity:    ['quantity','qty','count','amount'],
    unit:        ['unit','units','uom'],
    batch:       ['batch','batch_number','batchNumber','batchNo','lot'],
    expiry:      ['expiry','expiry_date','expiryDate','exp','expiration','expiration_date'],
};

function pickField(obj, key) {
    for (const alias of FIELD_ALIASES[key]) {
        if (obj[alias] !== undefined && obj[alias] !== null && String(obj[alias]).trim() !== '') return String(obj[alias]).trim();
    }
    return '';
}

function parseScannedText(raw) {
    const text = (raw || '').trim();
    if (!text) return { format: 'empty', data: {} };

    // JSON
    if (text.startsWith('{')) {
        try {
            const obj = JSON.parse(text);
            return {
                format: 'json',
                data: {
                    name:        pickField(obj,'name'),
                    genericName: pickField(obj,'genericName'),
                    brandNames:  pickField(obj,'brandNames'),
                    form:        pickField(obj,'form'),
                    category:    pickField(obj,'category'),
                    quantity:    pickField(obj,'quantity'),
                    unit:        pickField(obj,'unit'),
                    batch:       pickField(obj,'batch'),
                    expiry:      pickField(obj,'expiry'),
                },
            };
        } catch (_) {}
    }

    // Pipe-separated (≥ 3 pipes required to count as structured)
    if ((text.match(/\|/g) || []).length >= 3) {
        const p = text.split('|').map(s => s.trim());
        return {
            format: 'pipe',
            data: { name:p[0]||'', form:p[1]||'', category:p[2]||'', quantity:p[3]||'', unit:p[4]||'', batch:p[5]||'', expiry:p[6]||'' },
        };
    }

    // Plain text
    return { format: 'plain', data: { code: text } };
}

function normalizeForm(v) {
    const s = (v||'').toLowerCase().trim().replace(/[-_]/g,' ');
    const map = { tablet:'tablet', capsule:'capsule', syrup:'syrup', injection:'injection', cream:'cream', ointment:'cream' };
    if (map[s]) return map[s];
    for (const [k,val] of Object.entries(map)) { if (s.includes(k)) return val; }
    return v ? 'other' : '';
}
function normalizeCategory(v) {
    const s = (v||'').toLowerCase().trim();
    if (['otc','over-the-counter','over the counter'].includes(s)) return 'otc';
    if (['rx','prescription','rx only'].includes(s)) return 'prescription';
    if (['controlled','narcotic','controlled substance'].includes(s)) return 'controlled';
    return '';
}
function normalizeUnit(v) {
    const s = (v||'').toLowerCase().trim();
    const map = { pieces:'pieces', pcs:'pieces', piece:'pieces', tablets:'pieces',
                  bottles:'bottles', bottle:'bottles', boxes:'boxes', box:'boxes',
                  ml:'ml', milliliters:'ml', millilitres:'ml' };
    return map[s] || '';
}
function normalizeDate(v) {
    if (!v) return '';
    if (/^\d{4}-\d{2}-\d{2}$/.test(v)) return v;
    const d = new Date(v);
    if (isNaN(d)) return '';
    return `${d.getFullYear()}-${String(d.getMonth()+1).padStart(2,'0')}-${String(d.getDate()).padStart(2,'0')}`;
}

function setField(selector, value) {
    if (!value && value !== 0) return false;
    const el = document.querySelector(selector);
    if (!el) return false;
    el.value = value;
    if (el.tagName === 'SELECT') el.dispatchEvent(new Event('change', { bubbles: true }));
    return true;
}

function autofillFromParsed(data) {
    const filled = [];
    if (setField('input[name="name"]', data.name)) filled.push('Name');
    if (setField('input[name="generic_name"]', data.genericName)) filled.push('Generic name');
    if (setField('input[name="brand_names"]', data.brandNames)) filled.push('Brand names');
    const fv = normalizeForm(data.form);
    if (setField('select[name="dosage_form"]', fv)) { filled.push('Form'); onFormChange(); }
    if (setField('select[name="category"]', normalizeCategory(data.category))) filled.push('Category');
    if (setField('input[name="quantity"]', data.quantity)) filled.push('Quantity');
    if (setField('select[name="unit"]', normalizeUnit(data.unit))) filled.push('Unit');
    if (setField('input[name="batch_number"]', data.batch)) filled.push('Batch');
    const exp = normalizeDate(data.expiry);
    if (exp) {
        if (window.expiryPicker) window.expiryPicker.setDate(exp, true);
        else setField('input[name="expiry_date"]', exp);
        filled.push('Expiry');
    }
    return filled;
}

// ═══════════════════════════════════════════════════════
// Main scan dispatch
// ═══════════════════════════════════════════════════════
function onScanSuccess(decodedText) {
    const display = document.getElementById('scannedCodeDisplay');
    const input   = document.getElementById('scannedCode');
    const row     = document.getElementById('scannedCodeRow');
    const short   = decodedText.length > 60 ? decodedText.slice(0,60)+'…' : decodedText;
    display.textContent = short;
    input.value = decodedText;
    row.classList.remove('hidden');

    const parsed = parseScannedText(decodedText);

    if (parsed.format === 'json' || parsed.format === 'pipe') {
        // ✅ Structured data — auto-fill the form directly
        const filled = autofillFromParsed(parsed.data);
        if (filled.length > 0) {
            showToast('success', `${parsed.format === 'json' ? 'JSON' : 'Pipe'} detected — filled: ${filled.join(', ')}. Review then save.`);
        } else {
            showToast('warning', 'Parsed the code but no recognisable fields. Check the format in the helper above.');
        }
        scrollToForm();
    } else if (parsed.format === 'plain') {
        // Plain text: look up in inventory first.
        // If not found, pre-fill name if it looks like a name (not a bare barcode number).
        lookupMedicine(decodedText);
    }
}

function lookupMedicine(code) {
    fetch(`/api/medicines/lookup/${encodeURIComponent(code)}`, {
        headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(r => r.json())
    .then(data => {
        if (data.found) {
            const m = data.medicine || data;
            setField('input[name="name"]', m.name);
            setField('input[name="generic_name"]', m.generic_name);
            setField('input[name="brand_names"]', m.brand_names);
            setField('select[name="dosage_form"]', m.dosage_form || '');
            setField('select[name="category"]', m.type === 'prescription' ? 'prescription' : 'otc');
            showToast('success', `Found existing medicine: ${m.name}. Fields pre-filled — adjust stock and save.`);
            scrollToForm();
        } else {
            // Not in inventory — check if it looks like a name or a barcode
            const looksLikeName = !/^\d+$/.test(code) && code.length > 3 && code.length < 120;
            if (looksLikeName) {
                // Pre-fill the name field so the user doesn't have to retype it
                setField('input[name="name"]', code);
                showToast('info', `"${code}" pre-filled as medicine name. Complete the rest of the form and save.`);
            } else {
                showToast('info', `Barcode "${code}" isn't in inventory yet. Fill in the form below to add it.`);
            }
            scrollToForm();
        }
    })
    .catch(() => {
        // Offline / server error — still pre-fill name if it looks like text
        const looksLikeName = !/^\d+$/.test(code) && code.length > 3 && code.length < 120;
        if (looksLikeName) setField('input[name="name"]', code);
        showToast('warning', 'Could not reach the server to check the code. Fill in the form manually.');
        scrollToForm();
    });
}

function processManualCode() {
    const code = document.getElementById('manualCode').value.trim();
    if (!code) { showToast('error', 'Please type a code or barcode first.'); return; }
    onScanSuccess(code);
    document.getElementById('manualCode').value = '';
}

// ═══════════════════════════════════════════════════════
// "Other" form reveal / require
// ═══════════════════════════════════════════════════════
function onFormChange() {
    const sel  = document.getElementById('formSelect');
    const row  = document.getElementById('otherNoteRow');
    const note = document.getElementById('formOtherNote');
    const isOther = sel.value === 'other';
    row.classList.toggle('hidden', !isOther);
    note.required = isOther;
    if (!isOther) note.value = '';
}

// ═══════════════════════════════════════════════════════
// Medicine image helpers
// ═══════════════════════════════════════════════════════
function setMedImage(file) {
    if (!file) return;
    pastedMedImageFile = file;                 // stored for form submit
    const r = new FileReader();
    r.onload = e => {
        document.getElementById('imgPreview').innerHTML =
            `<img src="${e.target.result}" class="w-full h-full object-cover" alt="preview">`;
        document.getElementById('removeImgBtn').classList.remove('hidden');
    };
    r.readAsDataURL(file);
    // Also update the actual file input for normal submit path
    const dt = new DataTransfer();
    dt.items.add(file);
    document.getElementById('medImage').files = dt.files;
}

function removeMedImage() {
    pastedMedImageFile = null;
    document.getElementById('medImage').value = '';
    document.getElementById('imgPreview').innerHTML =
        '<div class="text-center"><i class="fa-solid fa-image text-gray-300 dark:text-gray-600 text-2xl"></i></div>';
    document.getElementById('removeImgBtn').classList.add('hidden');
}

// ═══════════════════════════════════════════════════════
// Clear form
// ═══════════════════════════════════════════════════════
function clearCode() {
    document.getElementById('scannedCode').value = '';
    document.getElementById('scannedCodeDisplay').textContent = '—';
    document.getElementById('scannedCodeRow').classList.add('hidden');
    document.getElementById('medicineForm').reset();
    syncAllCustomSelects();
    removeMedImage();
    onFormChange();
    if (window.expiryPicker) window.expiryPicker.clear();
    document.getElementById('successBanner').classList.add('hidden');
    document.getElementById('pageToast').classList.add('hidden');
}

// ═══════════════════════════════════════════════════════
// Toast helpers
// ═══════════════════════════════════════════════════════
const TOAST_STYLES = {
    success: 'bg-emerald-50 dark:bg-emerald-900/20 border-emerald-200 dark:border-emerald-700 text-emerald-800 dark:text-emerald-200',
    error:   'bg-red-50 dark:bg-red-900/20 border-red-200 dark:border-red-700 text-red-800 dark:text-red-200',
    info:    'bg-blue-50 dark:bg-blue-900/20 border-blue-200 dark:border-blue-700 text-blue-800 dark:text-blue-200',
    warning: 'bg-amber-50 dark:bg-amber-900/20 border-amber-200 dark:border-amber-700 text-amber-800 dark:text-amber-200',
};
const TOAST_ICONS = {
    success: 'fa-solid fa-circle-check text-emerald-500',
    error:   'fa-solid fa-circle-xmark text-red-500',
    info:    'fa-solid fa-circle-info text-blue-500',
    warning: 'fa-solid fa-triangle-exclamation text-amber-500',
};
let toastTimer = null;

function showToast(type, msg) {
    const el  = document.getElementById('pageToast');
    const ico = document.getElementById('toastIcon');
    const txt = document.getElementById('toastMsg');
    el.className = `rounded-2xl border-2 px-5 py-3.5 flex items-center gap-3 text-sm font-medium ${TOAST_STYLES[type]||TOAST_STYLES.info}`;
    ico.className = `text-lg flex-shrink-0 ${TOAST_ICONS[type]||TOAST_ICONS.info}`;
    txt.textContent = msg;
    el.scrollIntoView({ behavior:'smooth', block:'nearest' });
    clearTimeout(toastTimer);
    toastTimer = setTimeout(() => el.classList.add('hidden'), 8000);
}

function showSuccessBanner(msg, url) {
    const b = document.getElementById('successBanner');
    document.getElementById('successTitle').textContent = msg;
    document.getElementById('successSub').textContent   = 'Click to view in Medicines (highlights the row)';
    b.href = url;
    b.classList.remove('hidden');
    b.scrollIntoView({ behavior: 'smooth', block: 'start' });
}

function scrollToForm() {
    setTimeout(() => {
        document.getElementById('medicineForm').scrollIntoView({ behavior:'smooth', block:'start' });
    }, 200);
}

// ═══════════════════════════════════════════════════════
// Form submit
// ═══════════════════════════════════════════════════════
document.getElementById('medicineForm').addEventListener('submit', function(e) {
    e.preventDefault();

    // Guard: "Other" dosage form note is mandatory
    if (document.getElementById('formSelect').value === 'other') {
        const note = document.getElementById('formOtherNote').value.trim();
        if (!note) {
            showToast('error', 'Please describe the dosage form in the "Other" field.');
            document.getElementById('formOtherNote').focus();
            return;
        }
    }

    const btn = this.querySelector('button[type="submit"]');
    btn.disabled = true;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin text-xl"></i> Saving…';

    const formData = new FormData(this);

    // If image was pasted rather than selected via file input, attach it
    if (pastedMedImageFile && !formData.get('image')?.size) {
        formData.set('image', pastedMedImageFile, pastedMedImageFile.name || 'pasted.jpg');
    }

    fetch('{{ route("scan.save") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json',
        },
        body: formData,
    })
    .then(async r => ({ ok: r.ok, data: await r.json() }))
    .then(({ ok, data }) => {
        if (ok && data.success) {
            showSuccessBanner(`"${data.medicine_name}" added to inventory!`, data.view_url);
            this.reset();
            syncAllCustomSelects();
            removeMedImage();
            if (window.expiryPicker) window.expiryPicker.clear();
            onFormChange();
            document.getElementById('scannedCode').value = '';
            document.getElementById('scannedCodeDisplay').textContent = '—';
            document.getElementById('scannedCodeRow').classList.add('hidden');
        } else if (data.errors) {
            const msg = Object.values(data.errors).flat()[0];
            showToast('error', msg);
        } else {
            showToast('error', data.message || 'Error saving. Check the form and try again.');
        }
    })
    .catch(() => showToast('error', 'Network error. Please check your connection and try again.'))
    .finally(() => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fa-solid fa-floppy-disk text-xl"></i> Save to Inventory';
    });
});

// ═══════════════════════════════════════════════════════
// Custom dropdown — replaces all select.add-input elements
// ═══════════════════════════════════════════════════════
const CS_CHEVRON = `<svg class="cs-chevron" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="none"><path d="M5 7.5L10 12.5L15 7.5" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"/></svg>`;

function initCustomSelects() {
    document.querySelectorAll('select.add-input').forEach(initOneSelect);
}

function initOneSelect(nativeSel) {
    const wrapper = document.createElement('div');
    wrapper.className = 'custom-select';
    nativeSel.parentNode.insertBefore(wrapper, nativeSel);
    wrapper.appendChild(nativeSel);

    nativeSel.style.cssText = 'position:absolute;opacity:0;width:1px;height:1px;pointer-events:none;';

    const trigger = document.createElement('button');
    trigger.type = 'button';
    trigger.className = 'custom-select-trigger add-input';
    trigger.innerHTML = `<span class="cs-value"></span>${CS_CHEVRON}`;
    wrapper.insertBefore(trigger, nativeSel);

    const panel = document.createElement('div');
    panel.className = 'custom-select-panel hidden';
    Array.from(nativeSel.options).forEach(opt => {
        const item = document.createElement('div');
        item.className = 'custom-select-option' + (!opt.value ? ' placeholder' : '');
        item.dataset.value = opt.value;
        item.textContent = opt.textContent;
        item.addEventListener('click', () => {
            nativeSel.value = opt.value;
            nativeSel.dispatchEvent(new Event('change', { bubbles: true }));
            closeDropdown(wrapper);
        });
        panel.appendChild(item);
    });
    wrapper.appendChild(panel);

    updateTrigger(wrapper);
    nativeSel.addEventListener('change', () => updateTrigger(wrapper));

    trigger.addEventListener('click', e => {
        e.stopPropagation();
        wrapper.classList.contains('open') ? closeDropdown(wrapper) : openDropdown(wrapper);
    });
    trigger.addEventListener('keydown', e => {
        if (e.key === 'Escape') closeDropdown(wrapper);
        if ((e.key === 'Enter' || e.key === ' ') && !wrapper.classList.contains('open')) {
            e.preventDefault(); openDropdown(wrapper);
        }
    });
}

function updateTrigger(wrapper) {
    const sel = wrapper.querySelector('select');
    const valueSpan = wrapper.querySelector('.cs-value');
    if (!sel || !valueSpan) return;
    const opt = sel.options[sel.selectedIndex];
    const isEmpty = !opt || !opt.value;
    valueSpan.textContent = opt ? opt.textContent : '';
    valueSpan.style.color = isEmpty ? '#9ca3af' : '';
    wrapper.querySelectorAll('.custom-select-option').forEach(item => {
        item.classList.toggle('selected', !isEmpty && item.dataset.value === sel.value);
    });
}

function openDropdown(wrapper) {
    document.querySelectorAll('.custom-select.open').forEach(w => { if (w !== wrapper) closeDropdown(w); });
    wrapper.classList.add('open');
    wrapper.querySelector('.custom-select-panel').classList.remove('hidden');
}

function closeDropdown(wrapper) {
    wrapper.classList.remove('open');
    wrapper.querySelector('.custom-select-panel').classList.add('hidden');
}

function syncAllCustomSelects() {
    document.querySelectorAll('.custom-select').forEach(w => updateTrigger(w));
}

document.addEventListener('click', () => {
    document.querySelectorAll('.custom-select.open').forEach(w => closeDropdown(w));
});
document.addEventListener('keydown', e => {
    if (e.key === 'Escape') document.querySelectorAll('.custom-select.open').forEach(w => closeDropdown(w));
});

initCustomSelects();

// ═══════════════════════════════════════════════════════
// Flatpickr — branded date picker for Expiry Date
// ═══════════════════════════════════════════════════════
if (window.flatpickr) {
    window.expiryPicker = flatpickr('#expiryDate', {
        dateFormat: 'Y-m-d',
        altInput: true,
        altFormat: 'F j, Y',
        minDate: new Date(Date.now() + 24*60*60*1000),
        disableMobile: true,
    });
}

window.addEventListener('beforeunload', () => { if (isScanning) stopScanner(); });
</script>
@endsection
