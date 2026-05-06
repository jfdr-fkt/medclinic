<?php
namespace App\Http\Controllers;

use App\Models\DispenseLog;
use App\Models\Inventory;
use App\Models\Medicine;
use App\Models\MedicineLocation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MedicineController extends Controller
{
    public function index(Request $request)
    {
        $query = Medicine::with(['location', 'latestInventory']);

        // Active query excludes expired medicines (latest inventory still valid)
        $activeFilter = function ($q) {
            $q->where(function ($sub) {
                $sub->whereNull('expiration_date')
                    ->orWhere('expiration_date', '>=', now());
            });
        };

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'LIKE', "%{$request->search}%")
                  ->orWhere('generic_name', 'LIKE', "%{$request->search}%")
                  ->orWhere('barcode', $request->search)
                  ->orWhere('qr_code', $request->search);
            });
        }
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }
        if ($request->filled('location_id')) {
            $query->where('location_id', $request->location_id);
        }
        if ($request->filled('status')) {
            switch ($request->status) {
                case 'good':
                    $query->whereHas('inventories', fn($q) => $q->whereColumn('quantity', '>', 'min_stock_level'));
                    break;
                case 'low':
                    $query->whereHas('inventories', fn($q) =>
                        $q->whereColumn('quantity', '<=', 'min_stock_level')->where('quantity', '>', 5)
                    );
                    break;
                case 'critical':
                    $query->whereHas('inventories', fn($q) => $q->where('quantity', '<=', 5)->where('quantity', '>', 0));
                    break;
                case 'out':
                    $query->whereHas('inventories', fn($q) => $q->where('quantity', '<=', 0));
                    break;
            }
        }
        if ($request->boolean('low_stock')) {
            $query->whereHas('inventories', fn($q) => $q->whereColumn('quantity', '<=', 'min_stock_level'));
        }
        if ($request->boolean('expiring')) {
            $query->whereHas('inventories', fn($q) =>
                $q->where('expiration_date', '<=', now()->addDays(30))
                  ->where('expiration_date', '>=', now())
            );
        }

        // Active medicines (non-expired)
        $medicines = (clone $query)
            ->whereDoesntHave('latestInventory', fn($q) => $q->where('expiration_date', '<', now()))
            ->orderBy('name')->paginate(15)->withQueryString();

        // Expired archive (separate)
        $expiredMedicines = Medicine::with(['location', 'latestInventory'])
            ->whereHas('latestInventory', fn($q) => $q->where('expiration_date', '<', now()))
            ->orderBy('name')->get();

        $locations = MedicineLocation::all();

        $totalMedicines = Medicine::count();
        $criticalStock  = Medicine::whereHas('inventories', fn($q) => $q->where('quantity', '<=', 5))->count();
        $lowStock       = Medicine::whereHas('inventories', fn($q) =>
            $q->whereColumn('quantity', '<=', 'min_stock_level')->where('quantity', '>', 5)
        )->count();
        $expiringSoon   = Medicine::whereHas('inventories', fn($q) =>
            $q->where('expiration_date', '<=', now()->addDays(30))
              ->where('expiration_date', '>=', now())
        )->count();
        $expiredCount = $expiredMedicines->count();

        return view('medicines.index', compact(
            'medicines', 'expiredMedicines', 'locations',
            'totalMedicines', 'criticalStock', 'lowStock', 'expiringSoon', 'expiredCount'
        ));
    }

    public function create()
    {
        $locations = MedicineLocation::all();
        return view('medicines.create', compact('locations'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'           => 'required|string|max:255',
            'generic_name'   => 'nullable|string',
            'barcode'        => 'nullable|string|unique:medicines',
            'qr_code'        => 'nullable|string|unique:medicines',
            'location_id'    => 'required|exists:medicine_locations,id',
            'type'           => 'required|in:prescription,normal',
            'description'    => 'nullable|string',
            'dosage'         => 'nullable|string',
            'quantity'       => 'required|integer|min:0',
            'min_stock_level'=> 'required|integer|min:1',
            'expiration_date'=> 'required|date|after:today',
            'batch_number'   => 'nullable|string',
        ]);

        $medicine = Medicine::create([
            'name'         => $validated['name'],
            'generic_name' => $validated['generic_name'] ?? null,
            'barcode'      => $validated['barcode'] ?? null,
            'qr_code'      => $validated['qr_code'] ?? null,
            'location_id'  => $validated['location_id'],
            'type'         => $validated['type'],
            'description'  => $validated['description'] ?? null,
            'dosage'       => $validated['dosage'] ?? null,
        ]);

        Inventory::create([
            'medicine_id'     => $medicine->id,
            'quantity'        => $validated['quantity'],
            'min_stock_level' => $validated['min_stock_level'],
            'expiration_date' => $validated['expiration_date'],
            'batch_number'    => $validated['batch_number'] ?? null,
        ]);

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'medicine' => $medicine->load('location', 'latestInventory')]);
        }
        return redirect()->route('medicines.index')->with('success', "Medicine '{$medicine->name}' added successfully!");
    }

    public function show(Medicine $medicine)
    {
        $medicine->load(['location', 'inventories']);
        $dispenseLogs = DispenseLog::where('medicine_id', $medicine->id)
            ->with('dispensedBy')
            ->orderBy('created_at', 'desc')
            ->take(20)->get();
        return view('medicines.show', compact('medicine', 'dispenseLogs'));
    }

    public function update(Request $request, Medicine $medicine)
    {
        $validated = $request->validate([
            'name'         => 'required|string|max:255',
            'generic_name' => 'nullable|string',
            'location_id'  => 'required|exists:medicine_locations,id',
            'type'         => 'required|in:prescription,normal',
            'description'  => 'nullable|string',
            'dosage'       => 'nullable|string',
        ]);
        $medicine->update($validated);
        return back()->with('success', 'Medicine updated!');
    }

    public function destroy(Medicine $medicine)
    {
        $medicine->delete();
        return redirect()->route('medicines.index')->with('success', 'Medicine removed.');
    }

    public function dispense(Request $request, Medicine $medicine)
    {
        $request->validate([
            'quantity'   => 'required|integer|min:1',
            'patient_id' => 'nullable|exists:patients,id',
            'notes'      => 'nullable|string',
        ]);

        $inventory = $medicine->latestInventory;
        if (!$inventory || $inventory->quantity < $request->quantity) {
            return back()->withErrors(['quantity' => 'Not enough stock.']);
        }

        $inventory->decrement('quantity', $request->quantity);

        DispenseLog::create([
            'medicine_id'    => $medicine->id,
            'patient_id'     => $request->patient_id,
            'dispensed_by'   => Auth::id(),
            'quantity'       => $request->quantity,
            'notes'          => $request->notes,
        ]);

        return back()->with('success', "{$request->quantity} unit(s) of {$medicine->name} dispensed.");
    }

    public function storeLocation(Request $request)
    {
        $validated = $request->validate([
            'cabinet' => 'required|string',
            'shelf'   => 'required|string',
            'level'   => 'required|string',
            'section' => 'nullable|string',
            'notes'   => 'nullable|string',
        ]);
        MedicineLocation::create($validated);
        return back()->with('success', 'Location added!');
    }

    public function lookupByCode($code)
    {
        $medicine = Medicine::with(['location', 'latestInventory'])
            ->where('barcode', $code)
            ->orWhere('qr_code', $code)
            ->first();

        if ($medicine) {
            return response()->json([
                'found'       => true,
                'medicine'    => $medicine,
                'location'    => $medicine->location?->full_location,
                'stock'       => $medicine->totalStock(),
            ]);
        }
        return response()->json(['found' => false]);
    }
}
