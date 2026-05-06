<?php
namespace App\Http\Controllers;

use App\Models\Inventory;
use App\Models\Medicine;
use App\Models\MedicineLocation;
use Illuminate\Http\Request;

class ScanController extends Controller
{
    public function index()
    {
        $locations = MedicineLocation::all();
        return view('scan.index', compact('locations'));
    }

    public function lookup(Request $request)
    {
        $medicine = Medicine::with(['location', 'latestInventory'])
            ->where('barcode', $request->code)
            ->orWhere('qr_code', $request->code)
            ->first();

        if ($medicine) {
            return response()->json([
                'found'    => true,
                'name'     => $medicine->name,
                'type'     => $medicine->type === 'prescription' ? 'prescription' : 'otc',
                'category' => $medicine->type,
                'location' => $medicine->location?->full_location,
                'stock'    => $medicine->totalStock(),
            ]);
        }
        return response()->json(['found' => false]);
    }

    public function storeScan(Request $request)
    {
        $validated = $request->validate([
            'name'             => 'required|string|max:255',
            'type'             => 'nullable|string',
            'category'         => 'nullable|string',
            'quantity'         => 'required|integer|min:1',
            'unit'             => 'nullable|string',
            'expiry_date'      => 'required|date|after:today',
            'batch_number'     => 'nullable|string',
            'location_cabinet' => 'nullable|string',
            'location_level'   => 'nullable|string',
            'notes'            => 'nullable|string',
            'code'             => 'nullable|string',
        ]);

        // Find or create a location if provided
        $locationId = null;
        if (!empty($validated['location_cabinet'])) {
            $loc = MedicineLocation::firstOrCreate(
                ['cabinet' => $validated['location_cabinet'], 'shelf' => $validated['location_level'] ?? 'General'],
                ['level' => '1', 'section' => null, 'notes' => null]
            );
            $locationId = $loc->id;
        } else {
            $locationId = MedicineLocation::first()?->id;
        }

        $isRx = in_array($validated['category'] ?? '', ['prescription', 'controlled']);

        $medicine = Medicine::create([
            'name'        => $validated['name'],
            'type'        => $isRx ? 'prescription' : 'normal',
            'barcode'     => $validated['code'] ?? null,
            'location_id' => $locationId,
        ]);

        Inventory::create([
            'medicine_id'     => $medicine->id,
            'quantity'        => $validated['quantity'],
            'min_stock_level' => 10,
            'expiration_date' => $validated['expiry_date'],
            'batch_number'    => $validated['batch_number'] ?? null,
        ]);

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => "'{$medicine->name}' added to inventory!"]);
        }
        return redirect()->route('medicines.index')->with('success', "'{$medicine->name}' added via Smart Scan!");
    }
}