@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Header -->
    <div class="flex justify-between items-center mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Medicines & Inventory</h1>
            <p class="mt-1 text-sm text-gray-500">Manage your medicine stock and locations</p>
        </div>
        <div class="flex gap-3">
            <!-- Smart Scan Button -->
            <button onclick="openScanModal()" class="inline-flex items-center px-4 py-2 bg-purple-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-purple-700 shadow-lg transition-all hover:scale-105">
                <i class="fa-solid fa-barcode mr-2"></i> Smart Scan
            </button>
            <!-- Add Medicine Button -->
            <button onclick="openMedicineModal()" class="inline-flex items-center px-4 py-2 bg-brand-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-brand-700 shadow-lg transition-all hover:scale-105">
                <i class="fa-solid fa-plus mr-2"></i> Add Medicine
            </button>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-brand-500">
            <div class="flex justify-between items-center">
                <div>
                    <p class="text-sm text-gray-500">Total Medicines</p>
                    <p class="text-2xl font-bold text-gray-900">156</p>
                </div>
                <div class="bg-brand-100 p-3 rounded-full">
                    <i class="fa-solid fa-pills text-brand-600 text-xl"></i>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-red-500">
            <div class="flex justify-between items-center">
                <div>
                    <p class="text-sm text-gray-500">Critical Stock</p>
                    <p class="text-2xl font-bold text-red-600">3</p>
                </div>
                <div class="bg-red-100 p-3 rounded-full">
                    <i class="fa-solid fa-triangle-exclamation text-red-600 text-xl"></i>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-yellow-500">
            <div class="flex justify-between items-center">
                <div>
                    <p class="text-sm text-gray-500">Low Stock</p>
                    <p class="text-2xl font-bold text-yellow-600">8</p>
                </div>
                <div class="bg-yellow-100 p-3 rounded-full">
                    <i class="fa-solid fa-circle-exclamation text-yellow-600 text-xl"></i>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-green-500">
            <div class="flex justify-between items-center">
                <div>
                    <p class="text-sm text-gray-500">Expiring Soon</p>
                    <p class="text-2xl font-bold text-green-600">5</p>
                </div>
                <div class="bg-green-100 p-3 rounded-full">
                    <i class="fa-solid fa-calendar-xmark text-green-600 text-xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-xl shadow-sm p-4 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="md:col-span-2">
                <input type="text" placeholder="Search medicines..." 
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
            </div>
            <div>
                <select class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500">
                    <option>All Categories</option>
                    <option>Prescription</option>
                    <option>Non-Prescription</option>
                </select>
            </div>
            <div>
                <select class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500">
                    <option>All Stock Levels</option>
                    <option>Critical (< 6)</option>
                    <option>Low (6-10)</option>
                    <option>Good (> 10)</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Medicines Table -->
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Medicine</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Stock</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Location</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Expiry</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                <!-- Funny Data: 6 7 (six seven) -->
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4">
                        <div class="flex items-center">
                            <div class="h-10 w-10 bg-gradient-to-br from-blue-400 to-blue-600 rounded-full flex items-center justify-center text-white font-bold mr-3">
                                P
                            </div>
                            <div>
                                <div class="font-semibold text-gray-900">Paracetamol 500mg</div>
                                <div class="text-sm text-gray-500">Batch: B-2024-420</div>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        <span class="px-2 py-1 text-xs rounded-full bg-red-100 text-red-700 font-medium">
                            <i class="fa-solid fa-prescription-bottle mr-1"></i>Prescription
                        </span>
                    </td>
                    <td class="px-6 py-4">
                        <div class="text-sm font-medium text-gray-900">420 tablets</div>
                        <div class="text-xs text-gray-500">Blaze it!</div>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-500">
                        Cabinet A, Level 2, Row C
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-500">Dec 15, 2025</td>
                    <td class="px-6 py-4">
                        <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-700 font-medium">Good</span>
                    </td>
                    <td class="px-6 py-4 text-right">
                        <button class="text-brand-600 hover:text-brand-900 mr-2"><i class="fa-solid fa-edit"></i></button>
                        <button class="text-red-600 hover:text-red-900"><i class="fa-solid fa-trash"></i></button>
                    </td>
                </tr>
                
                <!-- Funny Data: 6 7 -->
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4">
                        <div class="flex items-center">
                            <div class="h-10 w-10 bg-gradient-to-br from-purple-400 to-purple-600 rounded-full flex items-center justify-center text-white font-bold mr-3">
                                A
                            </div>
                            <div>
                                <div class="font-semibold text-gray-900">Amoxicillin 250mg</div>
                                <div class="text-sm text-gray-500">Batch: B-2024-067</div>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        <span class="px-2 py-1 text-xs rounded-full bg-red-100 text-red-700 font-medium">
                            <i class="fa-solid fa-prescription-bottle mr-1"></i>Prescription
                        </span>
                    </td>
                    <td class="px-6 py-4">
                        <div class="text-sm font-medium text-red-600">6 units</div>
                        <div class="text-xs text-gray-500">Ate si 6, seven!</div>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-500">
                        Cabinet B, Level 1, Row A
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-500">Mar 20, 2025</td>
                    <td class="px-6 py-4">
                        <span class="px-2 py-1 text-xs rounded-full bg-yellow-100 text-yellow-700 font-medium">Low</span>
                    </td>
                    <td class="px-6 py-4 text-right">
                        <button class="text-brand-600 hover:text-brand-900 mr-2"><i class="fa-solid fa-edit"></i></button>
                        <button class="text-red-600 hover:text-red-900"><i class="fa-solid fa-trash"></i></button>
                    </td>
                </tr>

                <!-- Filipino Medicine Names -->
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4">
                        <div class="flex items-center">
                            <div class="h-10 w-10 bg-gradient-to-br from-green-400 to-green-600 rounded-full flex items-center justify-center text-white font-bold mr-3">
                                B
                            </div>
                            <div>
                                <div class="font-semibold text-gray-900">Biogesic 500mg</div>
                                <div class="text-sm text-gray-500">Batch: B-2024-001</div>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        <span class="px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-700 font-medium">
                            <i class="fa-solid fa-capsules mr-1"></i>Non-Prescription
                        </span>
                    </td>
                    <td class="px-6 py-4">
                        <div class="text-sm font-medium text-red-600">3 boxes</div>
                        <div class="text-xs text-gray-500">Critical na 'to!</div>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-500">
                        Cabinet A, Level 3, Row B
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-500">Jun 10, 2025</td>
                    <td class="px-6 py-4">
                        <span class="px-2 py-1 text-xs rounded-full bg-red-100 text-red-700 font-medium">Critical</span>
                    </td>
                    <td class="px-6 py-4 text-right">
                        <button class="text-brand-600 hover:text-brand-900 mr-2"><i class="fa-solid fa-edit"></i></button>
                        <button class="text-red-600 hover:text-red-900"><i class="fa-solid fa-trash"></i></button>
                    </td>
                </tr>

                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4">
                        <div class="flex items-center">
                            <div class="h-10 w-10 bg-gradient-to-br from-yellow-400 to-yellow-600 rounded-full flex items-center justify-center text-white font-bold mr-3">
                                N
                            </div>
                            <div>
                                <div class="font-semibold text-gray-900">Neozep Forte</div>
                                <div class="text-sm text-gray-500">Batch: B-2024-089</div>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        <span class="px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-700 font-medium">
                            <i class="fa-solid fa-capsules mr-1"></i>Non-Prescription
                        </span>
                    </td>
                    <td class="px-6 py-4">
                        <div class="text-sm font-medium text-gray-900">45 capsules</div>
                        <div class="text-xs text-gray-500">Okay pa!</div>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-500">
                        Cabinet C, Level 1, Row D
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-500">Aug 30, 2025</td>
                    <td class="px-6 py-4">
                        <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-700 font-medium">Good</span>
                    </td>
                    <td class="px-6 py-4 text-right">
                        <button class="text-brand-600 hover:text-brand-900 mr-2"><i class="fa-solid fa-edit"></i></button>
                        <button class="text-red-600 hover:text-red-900"><i class="fa-solid fa-trash"></i></button>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<!-- Smart Scan Modal -->
<div id="smartScanModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl max-w-4xl w-full max-h-[90vh] overflow-y-auto">
        <div class="p-6 border-b border-gray-200 flex justify-between items-center">
            <h3 class="text-2xl font-bold text-gray-900">
                <i class="fa-solid fa-barcode text-purple-600 mr-2"></i>Smart Scan Medicine
            </h3>
            <button onclick="closeScanModal()" class="text-gray-400 hover:text-gray-600 text-2xl">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
        
        <div class="p-6">
            <!-- Scanner Section -->
            <div class="mb-6">
                <div id="reader" class="rounded-xl overflow-hidden shadow-lg mb-4"></div>
                <div class="flex gap-3">
                    <button onclick="startScanner()" class="flex-1 bg-purple-600 text-white px-4 py-3 rounded-lg font-semibold hover:bg-purple-700 transition-colors">
                        <i class="fa-solid fa-camera mr-2"></i>Start Camera
                    </button>
                    <button onclick="stopScanner()" class="flex-1 bg-red-600 text-white px-4 py-3 rounded-lg font-semibold hover:bg-red-700 transition-colors hidden">
                        <i class="fa-solid fa-stop mr-2"></i>Stop Camera
                    </button>
                </div>
            </div>

            <!-- Medicine Form -->
            <form id="medicineScanForm" class="space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Scanned Code</label>
                        <input type="text" id="scannedCode" readonly 
                               class="w-full px-3 py-2 bg-gray-100 border border-gray-300 rounded-lg text-gray-600">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Medicine Name *</label>
                        <input type="text" name="name" required 
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Type *</label>
                        <select name="type" required 
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500">
                            <option value="">Select Type</option>
                            <option value="tablet">Tablet</option>
                            <option value="capsule">Capsule</option>
                            <option value="syrup">Syrup</option>
                            <option value="injection">Injection</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Category *</label>
                        <select name="category" required 
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500">
                            <option value="non-prescription">Non-Prescription (OTC)</option>
                            <option value="prescription">Prescription Only</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Quantity *</label>
                        <input type="number" name="quantity" required min="1"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Low Threshold *</label>
                        <input type="number" name="low_threshold" required min="1" value="10"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500">
                        <p class="text-xs text-gray-500 mt-1">Below 10 = Low</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Critical *</label>
                        <input type="number" name="critical_threshold" required min="1" value="6"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500">
                        <p class="text-xs text-gray-500 mt-1">Below 6 = Critical</p>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Location *</label>
                    <div class="grid grid-cols-3 gap-2">
                        <input type="text" name="location_cabinet" placeholder="Cabinet (e.g., Cabinet A)" required
                               class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500">
                        <input type="text" name="location_level" placeholder="Level (e.g., Level 2)" required
                               class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500">
                        <input type="text" name="location_row" placeholder="Row (e.g., Row C)" required
                               class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Batch Number</label>
                        <input type="text" name="batch_number"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Expiry Date *</label>
                        <input type="date" name="expiry_date" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500">
                    </div>
                </div>

                <div class="flex gap-3 pt-4">
                    <button type="button" onclick="closeScanModal()" 
                            class="flex-1 px-4 py-3 bg-gray-200 text-gray-700 rounded-lg font-semibold hover:bg-gray-300 transition-colors">
                        Cancel
                    </button>
                    <button type="submit" 
                            class="flex-1 px-4 py-3 bg-brand-600 text-white rounded-lg font-semibold hover:bg-brand-700 transition-colors shadow-lg">
                        <i class="fa-solid fa-save mr-2"></i>Save Medicine
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://unpkg.com/html5-qrcode"></script>
<script>
let html5QrcodeScanner;
let isScanning = false;

function openScanModal() {
    document.getElementById('smartScanModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function closeScanModal() {
    document.getElementById('smartScanModal').classList.add('hidden');
    document.body.style.overflow = 'auto';
    if (isScanning) stopScanner();
}

function startScanner() {
    html5QrcodeScanner = new Html5Qrcode("reader");
    const config = { fps: 10, qrbox: { width: 300, height: 250 } };
    
    html5QrcodeScanner.start({ facingMode: "environment" }, config, onScanSuccess)
        .then(() => {
            isScanning = true;
            document.querySelector('button[onclick="startScanner()"]').classList.add('hidden');
            document.querySelector('button[onclick="stopScanner()"]').classList.remove('hidden');
        })
        .catch(err => {
            alert("Unable to access camera. Please grant camera permissions.");
        });
}

function stopScanner() {
    if (html5QrcodeScanner) {
        html5QrcodeScanner.stop().then(() => {
            isScanning = false;
            document.querySelector('button[onclick="startScanner()"]').classList.remove('hidden');
            document.querySelector('button[onclick="stopScanner()"]').classList.add('hidden');
            html5QrcodeScanner.clear();
        });
    }
}

function onScanSuccess(decodedText, decodedResult) {
    document.getElementById('scannedCode').value = decodedText;
    // Auto-fill form if medicine exists
    lookupMedicine(decodedText);
}

function lookupMedicine(code) {
    fetch(`/api/medicines/lookup/${code}`)
        .then(response => response.json())
        .then(data => {
            if (data.found) {
                // Pre-fill form
                document.querySelector('input[name="name"]').value = data.name || '';
            }
        })
        .catch(() => console.log('New medicine'));
}

document.getElementById('medicineScanForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    formData.append('code', document.getElementById('scannedCode').value);
    
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
        closeScanModal();
        location.reload();
    })
    .catch(error => {
        alert('Error saving medicine');
    });
});

function openMedicineModal() {
    // Open regular add medicine modal
    alert('Open regular medicine form');
}
</script>
@endsection