<?php
namespace App\Http\Controllers;
use App\Models\User;
use App\Models\Shift;
use Illuminate\Http\Request;
class StaffController extends Controller {
    public function index() {
        $staff = User::with(['shifts'=>fn($q)=>$q->whereDate('shift_date',today())])
            ->whereIn('role',['doctor','nurse','assistant'])->get();
        return view('staff.index',compact('staff'));
    }
    public function storeShift(Request $request) {
        $validated = $request->validate([
            'user_id'=>'required|exists:users,id','shift_type'=>'required|in:morning,afternoon,night,on_call',
            'shift_date'=>'required|date','start_time'=>'required','end_time'=>'required|after:start_time'
        ]);
        Shift::create($validated);
        return back()->with('success','Shift assigned');
    }
}