@extends('layouts.app')

@section('content')
<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Smart Scan</h1>
        <p class="mt-1 text-sm text-gray-500">Scan medicine barcodes, QR codes, or NFC tags for quick inventory management</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Scanner Section -->
        <div class="bg-white rounded-2xl shadow-lg overflow-hidden">
            <div class="bg-gradient-to-r from-brand-600 to-brand-700 px-6 py-4">
                <h2 class="text-xl font-bold text-white flex items-center">
                    <i class="fa-solid fa-camera mr-2"></i> Scanner
                </h2>
            </div>
            
            <div class="p-6">
                <!-- Tab Buttons -->
                <div class="flex gap-2 mb-6">
                    <button onclick="switchTab('barcode')" id="tab-barcode" 
                            class="flex-1 px-4 py-2 bg-brand-100 text-brand-700 rounded-lg font-medium text-sm transition-colors">
                        <i class="fa-solid fa-barcode mr-2"></i>Barcode/QR
                    </button>
                    <button onclick="switchTab('nfc')" id="tab-nfc"
                            class="flex-1 px-4 py-2 bg-gray-100 text-gray-700 rounded-lg font-medium text-sm transition-colors">
                        <i class="fa-solid fa-nfc-symbol mr-2"></i>NFC Tag
                    </button>
                    <button onclick="switchTab('manual')" id="tab-manual"
                            class="flex-1 px-4 py-2 bg-gray-100 text-gray-700 rounded-lg font-medium text-sm transition-colors">
                        <i class="fa-solid fa-keyboard mr-2"></i>Manual
                    </button>
                </div>

                <!-- Barcode/QR Scanner -->
                <div id="scanner-barcode" class="scan-section">
                    <div id="reader" class="rounded-xl overflow-hidden shadow-inner mb-4"></div>
                    <button onclick="startScanner()" id="startBtn"
                            class="w-full px-4 py-3 bg-brand-600 text-white rounded-lg font-medium hover:bg-brand-700 transition-colors shadow-lg">
                        <i class="fa-solid fa-play mr-2"></i>Start Camera
                    </button>
                    <button onclick="stopScanner()" id="stopBtn"
                            class="w-full px-4 py-3 bg-red-600 text-white rounded-lg font-medium hover:bg-red-700 transition-colors shadow-lg hidden">
                        <i class="fa-solid fa-stop mr-2"></i>Stop Camera
                    </button>
                </div>

                <!-- NFC Scanner -->
                <div id="scanner-nfc" class="scan-section hidden">
                    <div class="text-center py-12 bg-gray-50 rounded-xl border-2 border-dashed border-gray-300">
                        <i class="fa-solid fa-nfc-symbol text-6xl text-gray-400 mb-4"></i>
                        <p class="text-gray-600 font-medium mb-2">Tap NFC Tag to Scan</p>
                        <p class="text-sm text-gray-500">Make sure NFC is enabled on your device</p>
                        <button onclick="scanNFC()" 
                                class="mt-4 px-6 py-2 bg-purple-600 text-white rounded-lg font-medium hover:bg-purple-700 transition-colors">
                            <i class="fa-solid fa-wifi mr-2"></i>Scan NFC
                        </button>
                    </div>
                </div>

                <!-- Manual Input -->
                <div id="scanner-manual" class="scan-section hidden">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Medicine Code</label>
                            <input type="text" id="manualCode" 
                                   class="block w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:ring-brand-500 focus:border-brand-500 text-lg"
                                   placeholder="Enter barcode or QR code">
                        </div>
                        <button onclick="processManualCode()"
                                class="w-full px-4 py-3 bg-brand-600 text-white rounded-lg font-medium hover:bg-brand-700 transition-colors shadow-lg">
                            <i class="fa-solid fa-search mr-2"></i>Lookup Code
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Medicine Details Section -->
        <div class="bg-white rounded-2xl shadow-lg overflow-hidden">
            <div class="bg-gradient-to-r from-green-600 to-green-700 px-6 py-4">
                <h2 class="text-xl font-bold text-white flex items-center">
                    <i class="fa-solid fa-pills mr-2"></i> Medicine Details
                </h2>
            </div>
            
            <div class="p-6">
                <form id="medicineForm" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Scanned Code</label>
                        <div class="flex gap-2">
                            <input type="text" id="scannedCode" readonly
                                   class="flex-1 block w-full px-4 py-2 bg-gray-50 border border-gray-300 rounded-lg text-gray-600"
                                   placeholder="Waiting for scan...">
                            <button type="button" onclick="clearCode()" 
                                    class="px-3 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300">
                                <i class="fa-solid fa-trash"></i>
                            </button>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Medicine Name <span class="text-red-500">*</span></label>
                        <input type="text" name="name" required
                               class="block w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-brand-500 focus:border-brand-500">
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Type <span class="text-red-500">*</span></label>
                            <select name="type" required
                                    class="block w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-brand-500 focus:border-brand-500">
                                <option value="">Select Type</option>
                                <option value="tablet">Tablet</option>
                                <option value="syrup">Syrup</option>
                                <option value="injection">Injection</option>
                                <option value="cream">Cream/Ointment</option>
                                <option value="capsule">Capsule</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Category</label>
                            <select name="category"
                                    class="block w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-brand-500 focus:border-brand-500">
                                <option value="normal">Normal</option>
                                <option value="prescription">Prescription Only</option>
                                <option value="controlled">Controlled Substance</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Quantity <span class="text-red-500">*</span></label>
                            <input type="number" name="quantity" required min="1"
                                   class="block w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-brand-500 focus:border-brand-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Unit</label>
                            <select name="unit"
                                    class="block w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-brand-500 focus:border-brand-500">
                                <option value="pieces">Pieces</option>
                                <option value="bottles">Bottles</option>
                                <option value="boxes">Boxes</option>
                                <option value="ml">Milliliters</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Storage Location <span class="text-red-500">*</span></label>
                        <div class="space-y-2">
                            <input type="text" name="location_cabinet" placeholder="Cabinet/Shelf (e.g., Cabinet A)"
                                   class="block w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-brand-500 focus:border-brand-500">
                            <input type="text" name="location_level" placeholder="Level/Row (e.g., Level 3, Row B)"
                                   class="block w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-brand-500 focus:border-brand-500">
                        </div>
                        <p class="mt-1 text-xs text-gray-500">Example: Cabinet A, Level 2, Row C - Makes finding medicines easy!</p>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Batch Number</label>
                            <input type="text" name="batch_number"
                                   class="block w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-brand-500 focus:border-brand-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Expiry Date <span class="text-red-500">*</span></label>
                            <input type="date" name="expiry_date" required
                                   class="block w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-brand-500 focus:border-brand-500">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                        <textarea name="notes" rows="2"
                                  class="block w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-brand-500 focus:border-brand-500"
                                  placeholder="Additional information..."></textarea>
                    </div>

                    <div class="pt-4 flex gap-3">
                        <button type="reset"
                                class="flex-1 px-4 py-3 bg-white border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 font-medium transition-colors">
                            <i class="fa-solid fa-rotate-left mr-2"></i>Clear
                        </button>
                        <button type="submit"
                                class="flex-1 px-4 py-3 bg-green-600 text-white rounded-lg font-medium hover:bg-green-700 transition-colors shadow-lg">
                            <i class="fa-solid fa-save mr-2"></i>Save to Inventory
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
<script>
let html5QrcodeScanner;
let isScanning = false;

function switchTab(tab) {
    // Hide all sections
    document.querySelectorAll('.scan-section').forEach(el => el.classList.add('hidden'));
    document.querySelectorAll('[id^="tab-"]').forEach(el => {
        el.classList.remove('bg-brand-100', 'text-brand-700');
        el.classList.add('bg-gray-100', 'text-gray-700');
    });

    // Show selected section
    document.getElementById(`scanner-${tab}`).classList.remove('hidden');
    document.getElementById(`tab-${tab}`).classList.remove('bg-gray-100', 'text-gray-700');
    document.getElementById(`tab-${tab}`).classList.add('bg-brand-100', 'text-brand-700');

    // Stop scanner if switching away from barcode
    if (tab !== 'barcode' && isScanning) {
        stopScanner();
    }
}

function startScanner() {
    html5QrcodeScanner = new Html5Qrcode("reader");
    
    const config = { 
        fps: 10, 
        qrbox: { width: 250, height: 250 },
        aspectRatio: 1.0
    };

    html5QrcodeScanner.start(
        { facingMode: "environment" }, 
        config, 
        onScanSuccess,
        onScanFailure
    ).then(() => {
        isScanning = true;
        document.getElementById('startBtn').classList.add('hidden');
        document.getElementById('stopBtn').classList.remove('hidden');
    }).catch(err => {
        console.error("Unable to start scanning", err);
        alert("Unable to access camera. Please make sure you've granted camera permissions.");
    });
}

function stopScanner() {
    if (html5QrcodeScanner) {
        html5QrcodeScanner.stop().then(() => {
            isScanning = false;
            document.getElementById('startBtn').classList.remove('hidden');
            document.getElementById('stopBtn').classList.add('hidden');
            html5QrcodeScanner.clear();
        }).catch(err => {
            console.error("Unable to stop scanning", err);
        });
    }
}

function onScanSuccess(decodedText, decodedResult) {
    // Play success sound
    const audio = new Audio('data:audio/wav;base64,UklGRnoGAABXQVZFZm10IBAAAAABAAEAQB8AAEAfAAABAAgAZGF0YQoGAACBhYqFbF1fdJivrJBhNjVgodDbq2EcBj+a2/LDciUFLIHO8tiJNwgZaLvt559NEAxQp+PwtmMcBjiR1/LMeSwFJHfH8N2QQAoUXrTp66hVFApGn+DyvmwhBTGH0fPTgjMGHm7A7+OZUQ4PVqzn8K1hGgU7k9r0z3kpBS2A0PLaizsIGGS57OihURALTqXh8bllHAU2jdXzyn0tBSp+zfDdkUEKFWCy6+qnVRQLSKDh8r5uIQU0h9Hy04IzBh1rv+/mnVIOD1Wr5O+rYRoFOpHZ88p6KgUte87w15Y9CRZhtuvqp1QWCkef4PK9bSIFM4XP8tWDMwYfbsPv5p1SDg9Uq+Tvq2EbBTmP2PPKfC0FKn7N8NqSOwoYY7bt6qdUFgpHn+Dyvmwi');
    audio.play().catch(() => {}); // Ignore play errors

    document.getElementById('scannedCode').value = decodedText;
    
    // Auto-lookup medicine if code exists
    lookupMedicine(decodedText);
    
    // Optionally stop scanner after successful scan
    // stopScanner();
}

function onScanFailure(error) {
    // Handle scan failure silently
    console.warn(`Code scan error = ${error}`);
}

function lookupMedicine(code) {
    // Simulate API call - Replace with actual fetch
    fetch(`/api/medicines/lookup/${code}`)
        .then(response => response.json())
        .then(data => {
            if (data.found) {
                // Pre-fill form with existing medicine data
                document.querySelector('input[name="name"]').value = data.name || '';
                document.querySelector('select[name="type"]').value = data.type || '';
                document.querySelector('select[name="category"]').value = data.category || 'normal';
                // Fill other fields...
            }
        })
        .catch(error => {
            console.log('Medicine not found in database, will create new entry');
        });
}

function processManualCode() {
    const code = document.getElementById('manualCode').value.trim();
    if (code) {
        document.getElementById('scannedCode').value = code;
        lookupMedicine(code);
    } else {
        alert('Please enter a code');
    }
}

function clearCode() {
    document.getElementById('scannedCode').value = '';
    document.getElementById('medicineForm').reset();
}

async function scanNFC() {
    if ('NDEFReader' in window) {
        try {
            const ndef = new NDEFReader();
            await ndef.scan();
            
            ndef.addEventListener("reading", ({ message }) => {
                for (const record of message.records) {
                    const text = new TextDecoder().decode(record.data);
                    document.getElementById('scannedCode').value = text;
                    lookupMedicine(text);
                }
            });
            
            alert('NFC scanning started. Tap an NFC tag.');
        } catch (error) {
            alert('NFC is not supported or not enabled on your device.');
        }
    } else {
        alert('NFC is not supported in your browser. Use Chrome on Android.');
    }
}

// Form submission
document.getElementById('medicineForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const scannedCode = document.getElementById('scannedCode').value;
    
    if (!scannedCode) {
        alert('Please scan or enter a medicine code first');
        return;
    }
    
    formData.append('code', scannedCode);
    
    // Simulate form submission - Replace with actual fetch
    fetch('/api/medicines', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify(Object.fromEntries(formData))
    })
    .then(response => response.json())
    .then(data => {
        alert('Medicine saved successfully!');
        this.reset();
        document.getElementById('scannedCode').value = '';
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error saving medicine. Please try again.');
    });
});

// Cleanup on page unload
window.addEventListener('beforeunload', () => {
    if (isScanning) {
        stopScanner();
    }
});
</script>
@endsection