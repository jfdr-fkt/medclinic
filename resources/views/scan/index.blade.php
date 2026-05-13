@extends('layouts.app')
@section('title', 'Smart Scan')
@section('page-title', 'Smart Scan')

@section('content')
<div class="space-y-5">

    <!-- Header -->
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Smart Scan</h1>
        <p class="text-sm text-gray-500 mt-0.5">Scan a barcode or QR code to instantly look up or add medicines to inventory</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">

        <!-- ── Scanner Panel ── -->
        <div class="card overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100 bg-gray-50/60 flex items-center gap-2">
                <i class="fa-solid fa-camera text-brand-500"></i>
                <h2 class="font-bold text-gray-800 text-sm">Scanner</h2>
            </div>

            <div class="p-5 space-y-4">
                <!-- Mode tabs -->
                <div class="flex rounded-xl bg-gray-100 p-1 gap-1">
                    <button onclick="switchTab('barcode')" id="tab-barcode" type="button"
                            class="flex-1 py-2 text-xs font-semibold rounded-lg transition-all bg-white text-brand-600 shadow-sm">
                        <i class="fa-solid fa-camera mr-1"></i> Camera
                    </button>
                    <button onclick="switchTab('upload')" id="tab-upload" type="button"
                            class="flex-1 py-2 text-xs font-semibold rounded-lg transition-all text-gray-500 hover:text-gray-700">
                        <i class="fa-solid fa-image mr-1"></i> Upload Image
                    </button>
                    <button onclick="switchTab('manual')" id="tab-manual" type="button"
                            class="flex-1 py-2 text-xs font-semibold rounded-lg transition-all text-gray-500 hover:text-gray-700">
                        <i class="fa-solid fa-keyboard mr-1"></i> Manual
                    </button>
                </div>

                <!-- Camera scanner (full-frame auto-grab — no crop box) -->
                <div id="scanner-barcode" class="scan-section space-y-3">
                    <div id="reader" class="rounded-xl overflow-hidden bg-gray-900 min-h-[260px] flex items-center justify-center">
                        <div class="text-center" id="cameraPlaceholder">
                            <i class="fa-solid fa-camera text-gray-600 text-4xl mb-2"></i>
                            <p class="text-gray-500 text-xs">Camera preview will appear here</p>
                            <p class="text-gray-600 text-[10px] mt-1">Click "Start Camera" to begin scanning</p>
                        </div>
                    </div>
                    <button onclick="startScanner()" id="startBtn" class="btn-primary w-full justify-center">
                        <i class="fa-solid fa-play"></i> Start Camera
                    </button>
                    <button onclick="stopScanner()" id="stopBtn" class="hidden w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-red-600 text-white text-sm font-semibold rounded-xl hover:bg-red-700 transition-colors shadow-sm">
                        <i class="fa-solid fa-stop"></i> Stop Camera
                    </button>
                </div>

                <!-- Image upload scanner -->
                <div id="scanner-upload" class="scan-section hidden space-y-3">
                    <label for="qrFile" class="block cursor-pointer rounded-xl border-2 border-dashed border-gray-300 hover:border-brand-400 hover:bg-brand-50/30 transition-all p-6 text-center">
                        <i class="fa-solid fa-cloud-arrow-up text-gray-400 text-3xl mb-2"></i>
                        <p class="text-sm font-semibold text-gray-700">Click to upload a QR or barcode image</p>
                        <p class="text-[11px] text-gray-500 mt-1">PNG, JPG, or any image file — the system will detect the code automatically</p>
                        <input type="file" id="qrFile" accept="image/*" class="hidden">
                    </label>
                    <div id="fileScanStatus" class="hidden text-xs text-gray-600 text-center"></div>
                    <!-- Hidden reader for file scan -->
                    <div id="file-reader" class="hidden"></div>
                </div>

                <!-- Manual input -->
                <div id="scanner-manual" class="scan-section hidden space-y-3">
                    <div>
                        <label class="label">Enter Code Manually</label>
                        <input type="text" id="manualCode" class="input text-base tracking-wider" placeholder="Barcode, QR text, or JSON">
                    </div>
                    <button onclick="processManualCode()" class="btn-primary w-full justify-center">
                        <i class="fa-solid fa-magnifying-glass"></i> Lookup Code
                    </button>
                </div>

                <!-- Scan result indicator -->
                <div id="scanResult" class="hidden p-3 bg-emerald-50 border border-emerald-200 rounded-xl text-sm text-emerald-800 flex items-center gap-2">
                    <i class="fa-solid fa-circle-check text-emerald-500 flex-shrink-0"></i>
                    <span id="scanResultText">Code scanned!</span>
                </div>

                <!-- Format helper card -->
                <details class="text-xs bg-blue-50/40 border border-blue-100 rounded-xl">
                    <summary class="cursor-pointer px-3 py-2.5 font-semibold text-blue-700 select-none">
                        <i class="fa-solid fa-circle-info mr-1"></i> How to generate codes that auto-fill the form
                    </summary>
                    <div class="px-3 pb-3 pt-1 space-y-2 text-gray-700 leading-relaxed">
                        <p>Paste either of these formats into any QR/barcode generator (e.g. <span class="font-mono">qr-code-generator.com</span>). The scanner will auto-fill all fields.</p>
                        <div>
                            <p class="font-semibold text-gray-800 mb-1">JSON (recommended — fields in any order):</p>
                            <pre class="bg-white border border-gray-200 rounded-lg p-2 text-[10.5px] font-mono overflow-x-auto whitespace-pre">{"name":"Amoxicillin 500mg","form":"tablet","category":"otc","quantity":100,"unit":"pieces","batch":"BTH-2024-001","expiry":"2026-12-31"}</pre>
                        </div>
                        <div>
                            <p class="font-semibold text-gray-800 mb-1">Pipe-separated (shorter — order matters):</p>
                            <pre class="bg-white border border-gray-200 rounded-lg p-2 text-[10.5px] font-mono overflow-x-auto">name|form|category|qty|unit|batch|expiry</pre>
                            <p class="text-[11px] text-gray-500 mt-1">Example: <span class="font-mono">Amoxicillin 500mg|tablet|otc|100|pieces|BTH-2024-001|2026-12-31</span></p>
                        </div>
                        <p class="text-[11px] text-gray-500">Plain barcodes (just a number) still work — they look up existing medicines by barcode.</p>
                    </div>
                </details>
            </div>
        </div>

        <!-- ── Medicine Form Panel ── -->
        <div class="card overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100 bg-gray-50/60 flex items-center gap-2">
                <i class="fa-solid fa-pills text-brand-500"></i>
                <h2 class="font-bold text-gray-800 text-sm">Medicine Details</h2>
            </div>

            <div class="p-5">
                <form id="medicineForm" class="space-y-4">
                    @csrf
                    <!-- Scanned Code -->
                    <div>
                        <label class="label">Scanned Code</label>
                        <div class="flex gap-2">
                            <input type="text" id="scannedCode" readonly class="input flex-1 bg-gray-50 text-gray-500 font-mono" placeholder="Waiting for scan…">
                            <button type="button" onclick="clearCode()"
                                    class="w-11 h-11 flex items-center justify-center text-gray-400 hover:text-red-500 hover:bg-red-50 rounded-xl transition-colors border-2 border-gray-200">
                                <i class="fa-solid fa-xmark text-sm"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Name + Form -->
                    <div class="grid grid-cols-2 gap-3">
                        <div class="col-span-2">
                            <label class="label">Medicine Name <span class="text-red-500">*</span></label>
                            <input type="text" name="name" required class="input" placeholder="e.g. Amoxicillin 500mg">
                        </div>
                        <div>
                            <label class="label">Form <span class="text-red-500">*</span></label>
                            <select name="type" required class="input">
                                <option value="">Select…</option>
                                <option value="tablet">Tablet</option>
                                <option value="capsule">Capsule</option>
                                <option value="syrup">Syrup</option>
                                <option value="injection">Injection</option>
                                <option value="cream">Cream / Ointment</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        <div>
                            <label class="label">Legal Category</label>
                            <select name="category" class="input">
                                <option value="otc">OTC (Over-the-Counter)</option>
                                <option value="prescription">Rx (Prescription)</option>
                                <option value="controlled">Controlled Substance</option>
                            </select>
                        </div>
                    </div>

                    <!-- Category info banner -->
                    <div class="text-xs text-gray-500 bg-blue-50/50 border border-blue-100 rounded-xl p-3 leading-relaxed">
                        <p class="font-semibold text-blue-700 mb-1"><i class="fa-solid fa-circle-info"></i> Legal categories explained:</p>
                        <ul class="space-y-0.5 ml-4 list-disc">
                            <li><strong>OTC:</strong> No prescription required (paracetamol, ibuprofen)</li>
                            <li><strong>Rx:</strong> Needs a doctor's prescription (antibiotics, blood pressure meds)</li>
                            <li><strong>Controlled:</strong> Strictly regulated narcotics/opioids — extra tracking required (morphine, diazepam)</li>
                        </ul>
                    </div>

                    <!-- Qty + Unit -->
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="label">Quantity <span class="text-red-500">*</span></label>
                            <input type="number" name="quantity" required min="1" class="input" placeholder="100">
                        </div>
                        <div>
                            <label class="label">Unit</label>
                            <select name="unit" class="input">
                                <option value="pieces">Pieces</option>
                                <option value="bottles">Bottles</option>
                                <option value="boxes">Boxes</option>
                                <option value="ml">Milliliters (mL)</option>
                            </select>
                        </div>
                    </div>

                    <!-- Location -->
                    <div>
                        <label class="label">Storage Location</label>
                        <div class="grid grid-cols-2 gap-2">
                            <input type="text" name="location_cabinet" class="input" placeholder="Cabinet (e.g. A)">
                            <input type="text" name="location_level" class="input" placeholder="Level / Shelf">
                        </div>
                    </div>

                    <!-- Batch + Expiry -->
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="label">Batch Number</label>
                            <input type="text" name="batch_number" class="input" placeholder="BTH-2024-001">
                        </div>
                        <div>
                            <label class="label">Expiry Date <span class="text-red-500">*</span></label>
                            <input type="date" name="expiry_date" required class="input">
                        </div>
                    </div>

                    <!-- Notes -->
                    <div>
                        <label class="label">Notes</label>
                        <textarea name="notes" rows="2" class="input resize-none" placeholder="Additional info…"></textarea>
                    </div>

                    <!-- Actions -->
                    <div class="flex justify-between gap-3 pt-3 border-t border-gray-100">
                        <button type="reset" onclick="clearCode()" class="btn-secondary">
                            <i class="fa-solid fa-rotate-left"></i> Clear
                        </button>
                        <button type="submit" class="btn-primary">
                            <i class="fa-solid fa-floppy-disk"></i> Save to Inventory
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
<script>
let html5QrcodeScanner = null;
let isScanning = false;

function switchTab(tab) {
    document.querySelectorAll('.scan-section').forEach(el => el.classList.add('hidden'));
    document.querySelectorAll('[id^="tab-"]').forEach(btn => {
        btn.classList.remove('bg-white','text-brand-600','shadow-sm');
        btn.classList.add('text-gray-500');
    });

    document.getElementById(`scanner-${tab}`).classList.remove('hidden');
    const activeBtn = document.getElementById(`tab-${tab}`);
    activeBtn.classList.add('bg-white','text-brand-600','shadow-sm');
    activeBtn.classList.remove('text-gray-500');

    if (tab !== 'barcode' && isScanning) stopScanner();
}

function startScanner() {
    document.getElementById('cameraPlaceholder')?.remove();
    html5QrcodeScanner = new Html5Qrcode('reader');
    // No qrbox → scans the full camera frame (works for QR codes AND wide 1D barcodes).
    html5QrcodeScanner.start(
        { facingMode: 'environment' },
        { fps: 10 },
        onScanSuccess,
        () => {}
    ).then(() => {
        isScanning = true;
        document.getElementById('startBtn').classList.add('hidden');
        document.getElementById('stopBtn').classList.remove('hidden');
    }).catch(() => {
        alert('Cannot access camera. Please grant camera permission and use HTTPS.');
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

// Image-file scanning (no camera needed) — uses html5-qrcode's scanFile.
document.getElementById('qrFile')?.addEventListener('change', function (e) {
    const file = e.target.files[0];
    if (!file) return;
    const status = document.getElementById('fileScanStatus');
    status.classList.remove('hidden');
    status.textContent = `Scanning "${file.name}"…`;

    const fileScanner = new Html5Qrcode('file-reader', /* verbose */ false);
    fileScanner.scanFile(file, /* showImage */ false)
        .then(decodedText => {
            status.innerHTML = `<span class="text-emerald-600 font-semibold">Detected!</span> ${escapeHtml(decodedText.slice(0,80))}${decodedText.length>80?'…':''}`;
            onScanSuccess(decodedText);
        })
        .catch(err => {
            status.innerHTML = `<span class="text-red-600 font-semibold">No code found.</span> Try a clearer image or use the camera.`;
            console.warn('scanFile error:', err);
        })
        .finally(() => { e.target.value = ''; });
});

// ── Structured-data parsing ─────────────────────────────────────────────
// Accepts JSON ({"name":..., "form":...}), pipe-separated (a|b|c|...), or plain.
const FIELD_ALIASES = {
    name:     ['name','title','medicine','medicine_name','medicineName'],
    form:     ['form','type','dosage_form','dosageForm'],
    category: ['category','legal','legal_category','legalCategory','class'],
    quantity: ['quantity','qty','count','amount'],
    unit:     ['unit','units','uom'],
    batch:    ['batch','batch_number','batchNumber','batchNo','lot'],
    expiry:   ['expiry','expiry_date','expiryDate','exp','expiration','expiration_date'],
};

function pickField(obj, key) {
    for (const alias of FIELD_ALIASES[key]) {
        if (obj[alias] !== undefined && obj[alias] !== null && obj[alias] !== '') return String(obj[alias]);
    }
    return '';
}

function parseScannedText(raw) {
    const text = (raw || '').trim();
    if (!text) return { format: 'empty', data: {} };

    // 1) JSON
    if (text.startsWith('{')) {
        try {
            const obj = JSON.parse(text);
            return {
                format: 'json',
                data: {
                    name:     pickField(obj, 'name'),
                    form:     pickField(obj, 'form'),
                    category: pickField(obj, 'category'),
                    quantity: pickField(obj, 'quantity'),
                    unit:     pickField(obj, 'unit'),
                    batch:    pickField(obj, 'batch'),
                    expiry:   pickField(obj, 'expiry'),
                },
            };
        } catch (e) { /* fall through */ }
    }

    // 2) Pipe-separated (need at least 3 pipes to count as structured)
    if ((text.match(/\|/g) || []).length >= 3) {
        const p = text.split('|').map(s => s.trim());
        return {
            format: 'pipe',
            data: {
                name:     p[0] || '',
                form:     p[1] || '',
                category: p[2] || '',
                quantity: p[3] || '',
                unit:     p[4] || '',
                batch:    p[5] || '',
                expiry:   p[6] || '',
            },
        };
    }

    // 3) Plain code
    return { format: 'plain', data: { code: text } };
}

// Normalize free-text values into the dropdown's allowed values.
function normalizeForm(v) {
    const s = (v || '').toLowerCase().trim();
    const allowed = ['tablet','capsule','syrup','injection','cream'];
    if (allowed.includes(s)) return s;
    if (s.includes('ointment')) return 'cream';
    return s ? 'other' : '';
}
function normalizeCategory(v) {
    const s = (v || '').toLowerCase().trim();
    if (['otc','over-the-counter','over the counter'].includes(s)) return 'otc';
    if (['rx','prescription'].includes(s)) return 'prescription';
    if (['controlled','narcotic','controlled substance'].includes(s)) return 'controlled';
    return '';
}
function normalizeUnit(v) {
    const s = (v || '').toLowerCase().trim();
    const allowed = ['pieces','bottles','boxes','ml'];
    if (allowed.includes(s)) return s;
    if (s === 'pcs' || s === 'piece') return 'pieces';
    if (s === 'bottle') return 'bottles';
    if (s === 'box')    return 'boxes';
    if (s === 'milliliters' || s === 'millilitres') return 'ml';
    return '';
}
function normalizeDate(v) {
    if (!v) return '';
    // Already YYYY-MM-DD
    if (/^\d{4}-\d{2}-\d{2}$/.test(v)) return v;
    const d = new Date(v);
    if (isNaN(d.getTime())) return '';
    const y = d.getFullYear(), m = String(d.getMonth()+1).padStart(2,'0'), day = String(d.getDate()).padStart(2,'0');
    return `${y}-${m}-${day}`;
}

function setIfPresent(selector, value) {
    if (!value) return false;
    const el = document.querySelector(selector);
    if (!el) return false;
    el.value = value;
    return true;
}

function autofillFromParsed(data) {
    const filled = [];
    if (setIfPresent('input[name="name"]', data.name)) filled.push('name');
    const formVal = normalizeForm(data.form);
    if (setIfPresent('select[name="type"]', formVal)) filled.push('form');
    const catVal = normalizeCategory(data.category);
    if (setIfPresent('select[name="category"]', catVal)) filled.push('category');
    if (setIfPresent('input[name="quantity"]', data.quantity)) filled.push('quantity');
    const unitVal = normalizeUnit(data.unit);
    if (setIfPresent('select[name="unit"]', unitVal)) filled.push('unit');
    if (setIfPresent('input[name="batch_number"]', data.batch)) filled.push('batch');
    const expiry = normalizeDate(data.expiry);
    if (setIfPresent('input[name="expiry_date"]', expiry)) filled.push('expiry');
    return filled;
}

function onScanSuccess(decodedText) {
    document.getElementById('scannedCode').value = decodedText;
    const parsed = parseScannedText(decodedText);

    if (parsed.format === 'json' || parsed.format === 'pipe') {
        const filled = autofillFromParsed(parsed.data);
        const label = parsed.format === 'json' ? 'JSON' : 'pipe-separated';
        showScanResult(`Parsed ${label} (${filled.length} fields filled) — review and Save.`);
    } else if (parsed.format === 'plain') {
        showScanResult(`Scanned: ${decodedText}`);
        lookupMedicine(decodedText);
    }
}

function showScanResult(msg) {
    const el = document.getElementById('scanResult');
    document.getElementById('scanResultText').textContent = msg;
    el.classList.remove('hidden');
    setTimeout(() => el.classList.add('hidden'), 5000);
}

function lookupMedicine(code) {
    fetch(`/api/medicines/lookup/${encodeURIComponent(code)}`, {
        headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
    })
    .then(r => r.json())
    .then(data => {
        if (data.found) {
            const m = data.medicine || data;
            document.querySelector('input[name="name"]').value     = m.name    || '';
            document.querySelector('select[name="category"]').value = (m.type === 'prescription' ? 'prescription' : 'otc');
            showScanResult(`Found existing medicine: ${m.name} — fields pre-filled`);
        }
    })
    .catch(() => {});
}

function processManualCode() {
    const code = document.getElementById('manualCode').value.trim();
    if (!code) { alert('Please enter a code first.'); return; }
    onScanSuccess(code);
}

function clearCode() {
    document.getElementById('scannedCode').value = '';
    document.getElementById('medicineForm').reset();
}

function escapeHtml(s) {
    return s.replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
}

document.getElementById('medicineForm').addEventListener('submit', function (e) {
    e.preventDefault();
    const code = document.getElementById('scannedCode').value;
    if (!code) { alert('Please scan or enter a medicine code first.'); return; }

    const formData = new FormData(this);
    formData.append('code', code);

    fetch('{{ route("scan.save") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json',
        },
        body: formData,
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            showScanResult(data.message || 'Medicine saved to inventory!');
            this.reset();
            clearCode();
        }
    })
    .catch(() => alert('Error saving medicine. Please try again.'));
});

window.addEventListener('beforeunload', () => { if (isScanning) stopScanner(); });
</script>
@endsection
