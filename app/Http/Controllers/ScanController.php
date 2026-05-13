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
                'found'        => true,
                'name'         => $medicine->name,
                'generic_name' => $medicine->generic_name,
                'brand_names'  => $medicine->brand_names,
                'type'         => $medicine->type === 'prescription' ? 'prescription' : 'otc',
                'category'     => $medicine->type,
                'dosage_form'  => $medicine->dosage_form,
                'location'     => $medicine->location?->full_location,
                'stock'        => $medicine->totalStock(),
            ]);
        }
        return response()->json(['found' => false]);
    }

    public function storeScan(Request $request)
    {
        $validated = $request->validate([
            'name'             => 'required|string|max:255',
            'generic_name'     => 'nullable|string|max:255',
            'brand_names'      => 'nullable|string|max:500',
            'dosage_form'      => 'nullable|string',
            'form_other_note'  => 'nullable|string|max:255',
            'category'         => 'nullable|string',
            'quantity'         => 'required|integer|min:1',
            'unit'             => 'nullable|string',
            'expiry_date'      => 'required|date|after:today',
            'batch_number'     => 'nullable|string',
            'location_cabinet' => 'nullable|string',
            'location_level'   => 'nullable|string',
            'notes'            => 'nullable|string',
            'scanned_raw_code' => 'nullable|string',
            'image'            => 'nullable|image|max:4096',
        ]);

        // "Other" form selected → note is mandatory.
        if (($validated['dosage_form'] ?? '') === 'other' && empty(trim($validated['form_other_note'] ?? ''))) {
            return response()->json([
                'success' => false,
                'errors'  => ['form_other_note' => 'A note is required when form is "Other".'],
            ], 422);
        }

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

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('medicines', 'public');
        }

        $medicine = Medicine::create([
            'name'            => $validated['name'],
            'generic_name'    => $validated['generic_name'] ?? null,
            'brand_names'     => $validated['brand_names'] ?? null,
            'type'            => $isRx ? 'prescription' : 'normal',
            'dosage_form'     => $validated['dosage_form'] ?? null,
            'barcode'         => $validated['scanned_raw_code'] ?? null,
            'location_id'     => $locationId,
            'description'     => $validated['notes'] ?? null,
            'image_path'      => $imagePath,
            'form_other_note' => $validated['form_other_note'] ?? null,
        ]);

        Inventory::create([
            'medicine_id'     => $medicine->id,
            'quantity'        => $validated['quantity'],
            'min_stock_level' => 10,
            'expiration_date' => $validated['expiry_date'],
            'batch_number'    => $validated['batch_number'] ?? null,
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'success'       => true,
                'message'       => "'{$medicine->name}' added to inventory!",
                'medicine_id'   => $medicine->id,
                'medicine_name' => $medicine->name,
                'view_url'      => route('medicines.index', ['highlight' => $medicine->id]),
            ]);
        }
        return redirect()
            ->route('medicines.index', ['highlight' => $medicine->id])
            ->with('success', "'{$medicine->name}' added via Smart Scan!");
    }
}
