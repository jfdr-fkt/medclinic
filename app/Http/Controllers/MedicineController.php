<?php
namespace App\Http\Controllers;

use App\Models\ActivityLog;
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
        // Common predicates used across the active vs archive split.
        //   active     = no manual archive AND latest expiration is in the future (or unknown)
        //   expired    = no manual archive AND latest expiration is in the past
        //   archivedXX = archived_at is set, scoped by archive_location_type
        $isActive = function ($q) {
            $q->whereNull('archived_at')->whereDoesntHave('latestInventory', fn($s) => $s->where('expiration_date', '<', now()));
        };
        $isExpiredOnly = function ($q) {
            $q->whereNull('archived_at')->whereHas('latestInventory', fn($s) => $s->where('expiration_date', '<', now()));
        };

        $query = Medicine::with(['location', 'latestInventory', 'archivedBy']);

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

        // Top-level filter card: all | critical | low | expiring | archive
        $view = $request->get('view', 'all');
        // Sub-tab within archive view: expired | manual_med_room | manual_storage
        $archiveTab = $request->get('archive_tab', 'expired');

        switch ($view) {
            case 'critical':
                $isActive($query);
                $query->whereHas('inventories', fn($q) => $q->where('quantity', '<=', 5)->where('quantity', '>', 0));
                break;
            case 'low':
                $isActive($query);
                $query->whereHas('inventories', fn($q) =>
                    $q->whereColumn('quantity', '<=', 'min_stock_level')->where('quantity', '>', 5)
                );
                break;
            case 'expiring':
                $isActive($query);
                $query->whereHas('inventories', fn($q) =>
                    $q->where('expiration_date', '<=', now()->addDays(30))
                      ->where('expiration_date', '>=', now())
                );
                break;
            case 'archive':
                // Archive view splits into three sub-tabs.
                if ($archiveTab === 'manual_med_room') {
                    $query->whereNotNull('archived_at')->where('archive_location_type', 'med_room');
                } elseif ($archiveTab === 'manual_storage') {
                    $query->whereNotNull('archived_at')->where('archive_location_type', 'storage');
                } else {
                    $archiveTab = 'expired';
                    $isExpiredOnly($query);
                }
                break;
            default:
                $isActive($query);
        }

        $sortField = in_array($request->get('sort'), ['name', 'updated_at', 'stock', 'expiry', 'archived_at']) ? $request->get('sort') : 'name';
        $sortDir   = $request->get('direction') === 'desc' ? 'desc' : 'asc';

        if ($sortField === 'stock') {
            $query->leftJoin('inventories', 'medicines.id', '=', 'inventories.medicine_id')
                  ->select('medicines.*')
                  ->orderBy('inventories.quantity', $sortDir)
                  ->groupBy('medicines.id');
        } elseif ($sortField === 'expiry') {
            $query->leftJoin('inventories', 'medicines.id', '=', 'inventories.medicine_id')
                  ->select('medicines.*')
                  ->orderBy('inventories.expiration_date', $sortDir)
                  ->groupBy('medicines.id');
        } else {
            $query->orderBy($sortField, $sortDir);
        }

        $medicines = $query->paginate(15)->withQueryString();
        $locations = MedicineLocation::all();

        // Stat counts (active-only — archive has its own count surfaced separately)
        $activeBase = Medicine::query()->tap($isActive);

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
        // One Archive count = expired + all manually-archived (regardless of location).
        $archiveExpiredCount      = Medicine::query()->tap($isExpiredOnly)->count();
        $archiveManualMedCount    = Medicine::whereNotNull('archived_at')->where('archive_location_type', 'med_room')->count();
        $archiveManualStoreCount  = Medicine::whereNotNull('archived_at')->where('archive_location_type', 'storage')->count();
        $archiveTotal = $archiveExpiredCount + $archiveManualMedCount + $archiveManualStoreCount;

        return view('medicines.index', compact(
            'medicines', 'locations',
            'totalMedicines', 'criticalStock', 'lowStock', 'expiringSoon',
            'archiveTotal', 'archiveExpiredCount', 'archiveManualMedCount', 'archiveManualStoreCount',
            'sortField', 'sortDir', 'view', 'archiveTab'
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
            'name'            => 'required|string|max:255',
            'generic_name'    => 'nullable|string',
            'barcode'         => 'nullable|string|unique:medicines',
            'qr_code'         => 'nullable|string|unique:medicines',
            'location_id'     => 'required|exists:medicine_locations,id',
            'type'            => 'required|in:prescription,normal',
            'description'     => 'nullable|string',
            'dosage'          => 'nullable|string',
            'form'            => 'nullable|string',
            'form_other_note' => 'nullable|string|max:255',
            'image'           => 'nullable|image|max:4096',
            'quantity'        => 'required|integer|min:0',
            'min_stock_level' => 'required|integer|min:1',
            'expiration_date' => 'required|date|after:today',
            'batch_number'    => 'nullable|string',
        ]);

        // "Other" form selected → note is mandatory.
        if (($validated['form'] ?? '') === 'other' && empty(trim($validated['form_other_note'] ?? ''))) {
            return back()
                ->withErrors(['form_other_note' => 'A note is required when form is "Other".'])
                ->withInput();
        }

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('medicines', 'public');
        }

        $medicine = Medicine::create([
            'name'            => $validated['name'],
            'generic_name'    => $validated['generic_name'] ?? null,
            'barcode'         => $validated['barcode'] ?? null,
            'qr_code'         => $validated['qr_code'] ?? null,
            'location_id'     => $validated['location_id'],
            'type'            => $validated['type'],
            'description'     => $validated['description'] ?? null,
            'dosage'          => $validated['dosage'] ?? null,
            'image_path'      => $imagePath,
            'form_other_note' => $validated['form_other_note'] ?? null,
        ]);

        Inventory::create([
            'medicine_id'     => $medicine->id,
            'quantity'        => $validated['quantity'],
            'min_stock_level' => $validated['min_stock_level'],
            'expiration_date' => $validated['expiration_date'],
            'batch_number'    => $validated['batch_number'] ?? null,
        ]);

        ActivityLog::create([
            'user_id'     => Auth::id(),
            'action'      => 'medicine.create',
            'entity_type' => Medicine::class,
            'entity_id'   => $medicine->id,
            'details'     => "Added medicine {$medicine->name} (qty {$validated['quantity']}, batch {$validated['batch_number']})",
        ]);

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'medicine' => $medicine->load('location', 'latestInventory')]);
        }
        return redirect()
            ->route('medicines.index', ['highlight' => $medicine->id])
            ->with('success', "Medicine '{$medicine->name}' added successfully!");
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

        ActivityLog::create([
            'user_id'     => Auth::id(),
            'action'      => 'medicine.update',
            'entity_type' => Medicine::class,
            'entity_id'   => $medicine->id,
            'details'     => "Updated medicine {$medicine->name}",
        ]);

        return back()->with('success', 'Medicine updated!');
    }

    public function destroy(Medicine $medicine)
    {
        if (!Auth::user()->can_('medicines.delete')) abort(403, 'Only admins can delete medicine records.');

        ActivityLog::create([
            'user_id'     => Auth::id(),
            'action'      => 'medicine.delete',
            'entity_type' => Medicine::class,
            'entity_id'   => $medicine->id,
            'details'     => "Removed medicine {$medicine->name}",
        ]);

        $medicine->delete();
        return redirect()->route('medicines.index')->with('success', 'Medicine removed.');
    }

    public function archive(Request $request, Medicine $medicine)
    {
        if (!Auth::user()->can_('medicines.delete')) abort(403, 'Only admins and clinic heads can archive medicines.');
        $validated = $request->validate([
            'reason'                 => 'required|string|max:255',
            'archive_location_type'  => 'required|in:med_room,storage',
        ]);

        $medicine->update([
            'archived_at'            => now(),
            'archived_by'            => Auth::id(),
            'archive_reason'         => $validated['reason'],
            'archive_location_type'  => $validated['archive_location_type'],
        ]);

        ActivityLog::create([
            'user_id'     => Auth::id(),
            'action'      => 'medicine.archive',
            'entity_type' => Medicine::class,
            'entity_id'   => $medicine->id,
            'details'     => "Archived {$medicine->name} ({$validated['archive_location_type']}) — {$validated['reason']}",
        ]);

        return back()->with('success', "{$medicine->name} archived.");
    }

    public function unarchive(Medicine $medicine)
    {
        if (!Auth::user()->can_('medicines.delete')) abort(403, 'Only admins and clinic heads can unarchive medicines.');

        $medicine->update([
            'archived_at'            => null,
            'archived_by'            => null,
            'archive_reason'         => null,
            'archive_location_type'  => 'med_room',
        ]);

        ActivityLog::create([
            'user_id'     => Auth::id(),
            'action'      => 'medicine.unarchive',
            'entity_type' => Medicine::class,
            'entity_id'   => $medicine->id,
            'details'     => "Restored {$medicine->name} to active inventory",
        ]);

        return back()->with('success', "{$medicine->name} restored to active inventory.");
    }

    public function dispense(Request $request, Medicine $medicine)
    {
        $request->validate([
            'quantity'   => 'required|integer|min:1',
            'patient_id' => 'nullable|exists:patients,id',
            'notes'      => 'nullable|string',
        ]);

        if ($medicine->isArchivedManually()) {
            return back()->withErrors(['quantity' => 'This medicine is archived and cannot be dispensed. Restore it first.']);
        }
        if ($medicine->isExpired()) {
            return back()->withErrors(['quantity' => 'This medicine is expired and cannot be dispensed.']);
        }

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

        ActivityLog::create([
            'user_id'     => Auth::id(),
            'action'      => 'medicine.dispense',
            'entity_type' => Medicine::class,
            'entity_id'   => $medicine->id,
            'details'     => "Dispensed {$request->quantity} unit(s) of {$medicine->name}" . ($request->notes ? " — {$request->notes}" : ''),
        ]);

        return back()->with('success', "{$request->quantity} unit(s) of {$medicine->name} dispensed.");
    }

    public function locationsIndex()
    {
        if (!Auth::user()->can_('medicines.locations')) abort(403, 'Only admins can manage locations.');
        $locations = MedicineLocation::withCount('medicines')->orderBy('storage_type')->orderBy('cabinet')->orderBy('shelf')->get();
        return view('medicines.locations', compact('locations'));
    }

    public function storeLocation(Request $request)
    {
        if (!Auth::user()->can_('medicines.locations')) abort(403, 'Only admins can manage locations.');
        $validated = $request->validate([
            'storage_type' => 'required|string|max:50',
            'cabinet'      => 'required|string|max:100',
            'shelf'        => 'required|string|max:50',
            'level'        => 'required|string|max:50',
            'section'      => 'nullable|string|max:50',
            'notes'        => 'nullable|string|max:255',
        ]);
        MedicineLocation::create($validated);
        return back()->with('success', 'Location added!');
    }

    public function updateLocation(Request $request, MedicineLocation $location)
    {
        if (!Auth::user()->can_('medicines.locations')) abort(403, 'Only admins can manage locations.');
        $validated = $request->validate([
            'storage_type' => 'required|string|max:50',
            'cabinet'      => 'required|string|max:100',
            'shelf'        => 'required|string|max:50',
            'level'        => 'required|string|max:50',
            'section'      => 'nullable|string|max:50',
            'notes'        => 'nullable|string|max:255',
        ]);
        $location->update($validated);
        return back()->with('success', 'Location updated!');
    }

    public function destroyLocation(MedicineLocation $location)
    {
        if (!Auth::user()->can_('medicines.locations')) abort(403, 'Only admins can manage locations.');
        if ($location->medicines()->exists()) {
            return back()->withErrors(['location' => 'Cannot delete a location that still has medicines assigned. Move or remove those medicines first.']);
        }
        $location->delete();
        return back()->with('success', 'Location removed.');
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
