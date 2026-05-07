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
        // Filter card view (drives which subset to show)
        $view = $request->get('view', 'all');
        switch ($view) {
            case 'critical':
                $query->whereHas('inventories', fn($q) => $q->where('quantity', '<=', 5)->where('quantity', '>', 0));
                break;
            case 'low':
                $query->whereHas('inventories', fn($q) =>
                    $q->whereColumn('quantity', '<=', 'min_stock_level')->where('quantity', '>', 5)
                );
                break;
            case 'expiring':
                $query->whereHas('inventories', fn($q) =>
                    $q->where('expiration_date', '<=', now()->addDays(30))
                      ->where('expiration_date', '>=', now())
                );
                break;
        }

        // If user clicked "Expired Archive" card, show only expired in the main list
        if ($view === 'expired') {
            $medicines = (clone $query)
                ->whereHas('latestInventory', fn($q) => $q->where('expiration_date', '<', now()))
                ->orderBy('name')->paginate(15)->withQueryString();
            $expiredMedicines = collect();
        } else {
            // Active medicines (non-expired only)
            $medicines = (clone $query)
                ->whereDoesntHave('latestInventory', fn($q) => $q->where('expiration_date', '<', now()))
                ->orderBy('name')->paginate(15)->withQueryString();
            $expiredMedicines = collect(); // archive collapsed; users access it via the Expired card
        }

        $locations = MedicineLocation::all();

        // Stats — only count non-expired medicines (expired is a separate stat)
        $activeBase = Medicine::whereDoesntHave('latestInventory', fn($q) => $q->where('expiration_date', '<', now()));

        $totalMedicines = (clone $activeBase)->count();
        $criticalStock  = (clone $activeBase)->whereHas('inventories', fn($q) =>
            $q->where('quantity', '<=', 5)->where('quantity', '>', 0)
        )->count();
        $lowStock       = (clone $activeBase)->whereHas('inventories', fn($q) =>
            $q->whereColumn('quantity', '<=', 'min_stock_level')->where('quantity', '>', 5)
        )->count();
        $expiringSoon   = (clone $activeBase)->whereHas('inventories', fn($q) =>
            $q->where('expiration_date', '<=', now()->addDays(30))
              ->where('expiration_date', '>=', now())
        )->count();
        $expiredCount = Medicine::whereHas('latestInventory', fn($q) => $q->where('expiration_date', '<', now()))->count();

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
        if (!Auth::user()->can_('medicines.create')) abort(403, 'Only admins can add medicines.');
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
        if (!Auth::user()->can_('medicines.delete')) abort(403, 'Only admins can delete medicine records.');
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
        if (!Auth::user()->can_('medicines.locations')) abort(403, 'Only admins can manage locations.');
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
