<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
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
            'avatar'         => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        // HR-controlled fields: only admin / clinic_head can change name and specialization.
        // For everyone else we silently strip those from the payload so a tampered form
        // can't bypass the UI's readonly attribute.
        if (!in_array($user->role, ['admin', 'clinic_head'])) {
            unset($validated['name'], $validated['specialization']);
        }

        if ($request->hasFile('avatar')) {
            if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
                Storage::disk('public')->delete($user->avatar);
            }
            $validated['avatar'] = $request->file('avatar')->store('avatars', 'public');
        } else {
            unset($validated['avatar']);
        }

        $user->update($validated);
        return back()->with('success', 'Profile updated!');
    }

    public function removeAvatar()
    {
        $user = Auth::user();
        if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
            Storage::disk('public')->delete($user->avatar);
        }
        $user->update(['avatar' => null]);
        return back()->with('success', 'Avatar removed.');
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

    public function theme(Request $request)
    {
        $request->validate(['theme' => 'required|in:light,dark']);
        Auth::user()->update(['theme' => $request->theme]);
        return response()->json(['success' => true, 'theme' => $request->theme]);
    }

    public function appearance(Request $request)
    {
        $validated = $request->validate([
            'font_size'       => 'nullable|in:sm,md,lg,xl',
            'colorblind_mode' => 'nullable|boolean',
        ]);
        Auth::user()->update(array_filter($validated, fn($v) => $v !== null));
        return back()->with('success', 'Display preferences saved.');
    }
}
