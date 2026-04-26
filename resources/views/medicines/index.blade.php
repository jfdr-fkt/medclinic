@extends('layouts.app')
@section('content')
<div x-data="{ showAddModal: false, showLocationModal: false }">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Inventory</h1>
        <div class="space-x-2">
            <button @click="showLocationModal = true" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg transition">+ Add Location</button>
            <button @click="showAddModal = true" class="bg-medical-600 hover:bg-medical-700 text-white px-4 py-2 rounded-lg transition">+ Add Medicine</button>
        </div>
    </div>

    <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-100 mb-6">
        <form method="GET" class="grid grid-cols-1 md:grid-cols-6 gap-4">
            <div class="md:col-span-2"><input type="text" name="search" value="{{ request('search') }}" placeholder="Search..." class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-medical-500 outline-none"></div>
            <div>
                <select name="type" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-medical-500 outline-none">
                    <option value="">All Types</option><option value="prescription" {{ request('type')=='prescription'?'selected':'' }}>Prescription</option><option value="normal" {{ request('type')=='normal'?'selected':'' }}>Normal</option>
                </select>
            </div>
            <div>
                <select name="location_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-medical-500 outline-none">
                    <option value="">All Locations</option>@foreach($locations as $loc)<option value="{{ $loc->id }}" {{ request('location_id')==$loc->id?'selected':'' }}>{{ $loc->cabinet }} - {{ $loc->shelf }}</option>@endforeach
                </select>
            </div>
            <div class="flex items-center space-x-4">
                <label class="flex items-center space-x-2 cursor-pointer"><input type="checkbox" name="low_stock" value="1" {{ request('low_stock')?'checked':'' }} class="rounded text-medical-600"><span class="text-sm text-gray-700">Low Stock</span></label>
                <label class="flex items-center space-x-2 cursor-pointer"><input type="checkbox" name="expiring" value="1" {{ request('expiring')?'checked':'' }} class="rounded text-medical-600"><span class="text-sm text-gray-700">Expiring</span></label>
            </div>
            <div class="flex space-x-2"><button type="submit" class="flex-1 bg-gray-800 text-white px-4 py-2 rounded-lg">Filter</button><a href="{{ route('medicines.index') }}" class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">Clear</a></div>
        </form>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach($medicines as $medicine)
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 hover:shadow-md transition">
            <div class="flex justify-between items-start mb-3">
                <div><h3 class="font-semibold text-gray-900">{{ $medicine->name }}</h3><p class="text-sm text-gray-500">{{ $medicine->generic_name ?? 'No generic name' }}</p></div>
                <span class="px-2 py-1 text-xs rounded-full {{ $medicine->type=='prescription'?'bg-purple-100 text-purple-800':'bg-blue-100 text-blue-800' }}">{{ ucfirst($medicine->type) }}</span>
            </div>
            <div class="space-y-2 text-sm">
                <div class="flex"><span class="text-gray-500 w-20">Location:</span><span class="font-medium text-medical-700">{{ $medicine->location->full_location }}</span></div>
                <div class="flex"><span class="text-gray-500 w-20">Stock:</span><span class="font-medium {{ ($medicine->latestInventory?->quantity??0)<=($medicine->latestInventory?->min_stock_level??0)?'text-red-600':'text-green-600' }}">{{ $medicine->latestInventory?->quantity ?? 0 }} units</span></div>
                <div class="flex"><span class="text-gray-500 w-20">Expires:</span><span class="font-medium {{ ($medicine->latestInventory?->daysUntilExpiry()??999)<=30?'text-orange-600':'text-gray-700' }}">{{ $medicine->latestInventory?->expiration_date?->format('M d, Y') ?? 'N/A' }}</span></div>
                @if($medicine->barcode)<div class="flex"><span class="text-gray-500 w-20">Barcode:</span><span class="font-mono text-xs bg-gray-100 px-2 py-1 rounded">{{ $medicine->barcode }}</span></div>@endif
            </div>
        </div>
        @endforeach
    </div>
    <div class="mt-6">{{ $medicines->links() }}</div>

    <div x-show="showAddModal" x-cloak class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-xl shadow-xl w-full max-w-2xl mx-4 max-h-[90vh] overflow-y-auto" @click.away="showAddModal = false">
            <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center"><h3 class="text-lg font-semibold">Add New Medicine</h3><button @click="showAddModal = false" class="text-gray-400 hover:text-gray-600">✕</button></div>
            <form method="POST" action="{{ route('medicines.store') }}" class="p-6 space-y-4">
                @csrf
                <div class="grid grid-cols-2 gap-4">
                    <div><label class="block text-sm font-medium text-gray-700 mb-1">Name *</label><input type="text" name="name" required class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-medical-500 outline-none"></div>
                    <div><label class="block text-sm font-medium text-gray-700 mb-1">Generic Name</label><input type="text" name="generic_name" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-medical-500 outline-none"></div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div><label class="block text-sm font-medium text-gray-700 mb-1">Barcode</label><input type="text" name="barcode" placeholder="Scan or type..." class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-medical-500 outline-none"></div>
                    <div><label class="block text-sm font-medium text-gray-700 mb-1">QR Code</label><input type="text" name="qr_code" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-medical-500 outline-none"></div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div><label class="block text-sm font-medium text-gray-700 mb-1">Location *</label><select name="location_id" required class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-medical-500 outline-none"><option value="">Select</option>@foreach($locations as $loc)<option value="{{ $loc->id }}">{{ $loc->full_location }}</option>@endforeach</select></div>
                    <div><label class="block text-sm font-medium text-gray-700 mb-1">Type *</label><select name="type" required class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-medical-500 outline-none"><option value="normal">Normal / OTC</option><option value="prescription">Prescription</option></select></div>
                </div>
                <div class="grid grid-cols-3 gap-4">
                    <div><label class="block text-sm font-medium text-gray-700 mb-1">Qty *</label><input type="number" name="quantity" required min="0" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-medical-500 outline-none"></div>
                    <div><label class="block text-sm font-medium text-gray-700 mb-1">Min Stock *</label><input type="number" name="min_stock_level" required min="1" value="10" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-medical-500 outline-none"></div>
                    <div><label class="block text-sm font-medium text-gray-700 mb-1">Expires *</label><input type="date" name="expiration_date" required class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-medical-500 outline-none"></div>
                </div>
                <div><label class="block text-sm font-medium text-gray-700 mb-1">Batch #</label><input type="text" name="batch_number" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-medical-500 outline-none"></div>
                <div class="flex justify-end space-x-3 pt-4">
                    <button type="button" @click="showAddModal = false" class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-medical-600 text-white rounded-lg hover:bg-medical-700">Add Medicine</button>
                </div>
            </form>
        </div>
    </div>

    <div x-show="showLocationModal" x-cloak class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-xl shadow-xl w-full max-w-md mx-4" @click.away="showLocationModal = false">
            <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center"><h3 class="text-lg font-semibold">Add Storage Location</h3><button @click="showLocationModal = false" class="text-gray-400 hover:text-gray-600">✕</button></div>
            <form method="POST" action="{{ route('medicines.locations.store') }}" class="p-6 space-y-4">
                @csrf
                <div class="grid grid-cols-2 gap-4">
                    <div><label class="block text-sm font-medium text-gray-700 mb-1">Cabinet *</label><input type="text" name="cabinet" required class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-medical-500 outline-none"></div>
                    <div><label class="block text-sm font-medium text-gray-700 mb-1">Shelf *</label><input type="text" name="shelf" required class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-medical-500 outline-none"></div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div><label class="block text-sm font-medium text-gray-700 mb-1">Level *</label><input type="text" name="level" required class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-medical-500 outline-none"></div>
                    <div><label class="block text-sm font-medium text-gray-700 mb-1">Section</label><input type="text" name="section" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-medical-500 outline-none"></div>
                </div>
                <div class="flex justify-end space-x-3 pt-4">
                    <button type="button" @click="showLocationModal = false" class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700">Add Location</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection