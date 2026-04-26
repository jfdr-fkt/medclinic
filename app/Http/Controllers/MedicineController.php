<?php
namespace App\Http\Controllers;
use App\Models\Medicine;
use App\Models\MedicineLocation;
use App\Models\Inventory;
use Illuminate\Http\Request;
class MedicineController extends Controller {
    public function index(Request $request) {
        $query = Medicine::with(['location','latestInventory']);
        if($request->filled('search')) {
            $query->where(function($q)use($request){
                $q->where('name','LIKE',"%{$request->search}%")->orWhere('generic_name','LIKE',"%{$request->search}%")->orWhere('barcode',$request->search);
            });
        }
        if($request->filled('type')) $query->where('type',$request->type);
        if($request->filled('location_id')) $query->where('location_id',$request->location_id);
        if($request->boolean('low_stock')) $query->whereHas('inventories',fn($q)=>$q->whereColumn('quantity','<=','min_stock_level'));
        if($request->boolean('expiring')) $query->whereHas('inventories',fn($q)=>$q->where('expiration_date','<=',now()->addDays(30)));
        $medicines = $query->paginate(15)->withQueryString();
        $locations = MedicineLocation::all();
        return view('medicines.index',compact('medicines','locations'));
    }
    public function store(Request $request) {
        $validated = $request->validate([
            'name'=>'required|string|max:255','generic_name'=>'nullable|string',
            'barcode'=>'nullable|string|unique:medicines','qr_code'=>'nullable|string|unique:medicines',
            'location_id'=>'required|exists:medicine_locations,id','type'=>'required|in:prescription,normal',
            'description'=>'nullable|string','dosage'=>'nullable|string','quantity'=>'required|integer|min:0',
            'min_stock_level'=>'required|integer|min:1','expiration_date'=>'required|date|after:today','batch_number'=>'nullable|string'
        ]);
        $medicine = Medicine::create([
            'name'=>$validated['name'],'generic_name'=>$validated['generic_name'],'barcode'=>$validated['barcode'],
            'qr_code'=>$validated['qr_code'],'location_id'=>$validated['location_id'],'type'=>$validated['type'],
            'description'=>$validated['description'],'dosage'=>$validated['dosage']
        ]);
        Inventory::create([
            'medicine_id'=>$medicine->id,'quantity'=>$validated['quantity'],'min_stock_level'=>$validated['min_stock_level'],
            'expiration_date'=>$validated['expiration_date'],'batch_number'=>$validated['batch_number']
        ]);
        return redirect()->route('medicines.index')->with('success','Medicine added');
    }
    public function storeLocation(Request $request) {
        $validated = $request->validate(['cabinet'=>'required|string','shelf'=>'required|string','level'=>'required|string','section'=>'nullable|string','notes'=>'nullable|string']);
        MedicineLocation::create($validated);
        return back()->with('success','Location added');
    }
}