@extends('layouts.app')
@section('content')
<div x-data="scannerApp()" x-init="initScanner()">
    <div class="max-w-4xl mx-auto">
        <h1 class="text-2xl font-bold text-gray-900 mb-6">📷 Smart Scan</h1>
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <h3 class="font-semibold mb-4">Scan Barcode or QR</h3>
                <div id="reader" class="rounded-lg overflow-hidden"></div>
                <div class="mt-4 flex space-x-2">
                    <button @click="startScan()" x-show="!isScanning" class="flex-1 bg-medical-600 text-white py-2 rounded-lg hover:bg-medical-700 transition">Start Camera</button>
                    <button @click="stopScan()" x-show="isScanning" class="flex-1 bg-red-600 text-white py-2 rounded-lg hover:bg-red-700 transition">Stop Camera</button>
                </div>
                <p class="text-sm text-gray-500 mt-3 text-center">Point camera at medicine barcode or QR</p>
            </div>
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <h3 class="font-semibold mb-4">Medicine Details</h3>
                <div x-show="scannedData" class="mb-4 p-4 bg-green-50 border border-green-200 rounded-lg"><p class="text-sm text-green-800 font-medium">✅ Code: <span x-text="scannedData"></span></p></div>
                <form method="POST" action="{{ route('medicines.store') }}" class="space-y-4">
                    @csrf
                    <div><label class="block text-sm font-medium text-gray-700 mb-1">Barcode / QR</label><input type="text" name="barcode" x-model="scannedData" placeholder="Scan or type..." class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-medical-500 outline-none"></div>
                    <div><label class="block text-sm font-medium text-gray-700 mb-1">Name *</label><input type="text" name="name" required class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-medical-500 outline-none"></div>
                    <div class="grid grid-cols-2 gap-4">
                        <div><label class="block text-sm font-medium text-gray-700 mb-1">Type</label><select name="type" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-medical-500 outline-none"><option value="normal">Normal</option><option value="prescription">Prescription</option></select></div>
                        <div><label class="block text-sm font-medium text-gray-700 mb-1">Qty</label><input type="number" name="quantity" required min="0" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-medical-500 outline-none"></div>
                    </div>
                    <div><label class="block text-sm font-medium text-gray-700 mb-1">Location *</label><select name="location_id" required class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-medical-500 outline-none"><option value="">Select</option>@foreach($locations as $loc)<option value="{{ $loc->id }}">{{ $loc->full_location }}</option>@endforeach</select></div>
                    <div><label class="block text-sm font-medium text-gray-700 mb-1">Expires *</label><input type="date" name="expiration_date" required class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-medical-500 outline-none"></div>
                    <button type="submit" class="w-full bg-medical-600 text-white py-3 rounded-lg hover:bg-medical-700 transition font-medium">Add to Inventory</button>
                </form>
            </div>
        </div>
    </div>
</div>
@push('scripts')
<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
<script>
function scannerApp() {
    return {
        scanner: null, isScanning: false, scannedData: '',
        initScanner() {},
        startScan() {
            this.scanner = new Html5Qrcode('reader');
            this.scanner.start({facingMode:"environment"},{fps:10,qrbox:{width:250,height:250}},
                (decodedText)=>{ this.scannedData=decodedText; this.stopScan(); },
                (error)=>{}
            ).then(()=>{ this.isScanning=true; });
        },
        stopScan() {
            if(this.scanner){ this.scanner.stop().then(()=>{ this.isScanning=false; }); }
        }
    }
}
</script>
@endpush
@endsection