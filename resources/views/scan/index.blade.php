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
                        <i class="fa-solid fa-barcode mr-1"></i> Camera
                    </button>
                    <button onclick="switchTab('manual')" id="tab-manual" type="button"
                            class="flex-1 py-2 text-xs font-semibold rounded-lg transition-all text-gray-500 hover:text-gray-700">
                        <i class="fa-solid fa-keyboard mr-1"></i> Manual Entry
                    </button>
                </div>

                <!-- Camera scanner -->
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

                <!-- Manual input -->
                <div id="scanner-manual" class="scan-section hidden space-y-3">
                    <div>
                        <label class="label">Enter Code Manually</label>
                        <input type="text" id="manualCode" class="input text-base tracking-wider" placeholder="Barcode or QR code value">
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
    html5QrcodeScanner.start(
        { facingMode: 'environment' },
        { fps: 10, qrbox: { width: 240, height: 240 } },
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

function onScanSuccess(decodedText) {
    document.getElementById('scannedCode').value = decodedText;
    showScanResult(`Scanned: ${decodedText}`);
    lookupMedicine(decodedText);
}

function showScanResult(msg) {
    const el = document.getElementById('scanResult');
    document.getElementById('scanResultText').textContent = msg;
    el.classList.remove('hidden');
    setTimeout(() => el.classList.add('hidden'), 4000);
}

function lookupMedicine(code) {
    fetch(`/api/medicines/lookup/${encodeURIComponent(code)}`, {
        headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
    })
    .then(r => r.json())
    .then(data => {
        if (data.found) {
            document.querySelector('input[name="name"]').value     = data.name    || '';
            document.querySelector('select[name="category"]').value = data.category || 'otc';
            showScanResult(`Found: ${data.name} — fields pre-filled`);
        }
    })
    .catch(() => {});
}

function processManualCode() {
    const code = document.getElementById('manualCode').value.trim();
    if (!code) { alert('Please enter a code first.'); return; }
    document.getElementById('scannedCode').value = code;
    showScanResult(`Code entered: ${code}`);
    lookupMedicine(code);
}

function clearCode() {
    document.getElementById('scannedCode').value = '';
    document.getElementById('medicineForm').reset();
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
