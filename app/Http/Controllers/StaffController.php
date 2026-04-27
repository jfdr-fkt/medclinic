<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Shift;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class StaffController extends Controller
{
    public function index()
    {
        $staff = User::where('role', '!=', 'patient')->get();
        $shifts = Shift::with('user')->get();
        return view('staff.index', compact('staff', 'shifts'));
    }

    public function storeShift(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'shift_type' => 'required|in:morning,afternoon,night',
            'shift_date' => 'required|date',
            'start_time' => 'required',
            'end_time' => 'required|after:start_time',
        ]);

        Shift::create($request->all());

        return redirect()->back()->with('success', 'Shift assigned successfully!');
    }

    public function toggleStatus(User $user)
    {
        $user->update([
            'is_online' => !$user->is_online
        ]);

        return redirect()->back()->with('success', 'Status updated!');
    }
}