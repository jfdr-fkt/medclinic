<?php
namespace App\Http\Controllers;
use App\Models\Medicine;
use App\Models\MedicineLocation;
use Illuminate\Http\Request;
class ScanController extends Controller {
    public function index() {
        $locations = MedicineLocation::all();
        return view('scan.index',compact('locations'));
    }
    public function lookup(Request $request) {
        $medicine = Medicine::with(['location','latestInventory'])
            ->where('barcode',$request->code)->orWhere('qr_code',$request->code)->first();
        if($medicine) {
            return response()->json(['found'=>true,'medicine'=>$medicine,'location'=>$medicine->location->full_location,'stock'=>$medicine->totalStock()]);
        }
        return response()->json(['found'=>false]);
    }
}