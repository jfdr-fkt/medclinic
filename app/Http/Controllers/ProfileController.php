<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    public function edit()
    {
        return view('profile.edit', ['user' => Auth::user()]);
    }

    public function update(Request $request)
    {
        $user = Auth::user();
        $validated = $request->validate([
            'name'           => 'required|string|max:255',
            'email'          => ['required', 'email', Rule::unique('users')->ignore($user->id)],
            'phone'          => 'nullable|string|max:50',
            'specialization' => 'nullable|string|max:255',
            'bio'            => 'nullable|string|max:500',
        ]);
        $user->update($validated);
        return back()->with('success', 'Profile updated!');
    }

    public function password(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'password'         => 'required|min:6|confirmed',
        ]);

        if (!Hash::check($request->current_password, Auth::user()->password)) {
            return back()->withErrors(['current_password' => 'Current password is incorrect.']);
        }

        Auth::user()->update(['password' => Hash::make($request->password)]);
        return back()->with('success', 'Password changed!');
    }

    public function status(Request $request)
    {
        $request->validate(['status' => 'required|in:available,busy,away,offline']);
        Auth::user()->update(['status' => $request->status]);
        return response()->json([
            'success' => true,
            'status'  => Auth::user()->status,
            'label'   => Auth::user()->statusLabel(),
            'color'   => Auth::user()->statusColor(),
        ]);
    }
}
