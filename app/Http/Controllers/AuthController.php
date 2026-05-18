<?php
namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) return redirect()->route('dashboard');
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();
            Auth::user()->update(['last_seen_at' => now(), 'status' => 'available']);
            // Flash so the dashboard can install a one-time back-button trap.
            $request->session()->flash('just_logged_in', true);
            return redirect()->intended(route('dashboard'));
        }

        return back()
            ->withErrors(['email' => 'Invalid email or password.'])
            ->withInput($request->only('email'));
    }

    public function showRegister()
    {
        if (Auth::check()) return redirect()->route('dashboard');
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6|confirmed',
            'role' => 'required|in:nurse,doctor,assistant,admin',
            'phone' => 'nullable|string|max:50',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
            'phone' => $validated['phone'] ?? null,
            'is_active' => true,
            'status' => 'available',
            'last_seen_at' => now(),
        ]);

        Auth::login($user);
        $request->session()->flash('just_logged_in', true);
        return redirect()->route('dashboard')->with('success', "Welcome to ClinicMS, {$user->name}!");
    }

    public function logout(Request $request)
    {
        if (Auth::check()) {
            Auth::user()->update(['status' => 'offline']);
        }
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }

    /**
     * Force-change-password screen. Only reachable when must_change_password = true on
     * the logged-in user (the ForcePasswordChange middleware redirects every other
     * route here until the flag clears).
     */
    public function showForcePassword()
    {
        // If somehow the flag was cleared already, send them on to the dashboard.
        if (! Auth::user()?->must_change_password) {
            return redirect()->route('dashboard');
        }
        return view('auth.force-password');
    }

    public function updateForcePassword(Request $request)
    {
        $request->validate([
            'password' => 'required|string|min:6|confirmed',
        ]);

        $user = Auth::user();
        // Reject reusing the same temporary password — they're supposed to pick a new one.
        if (Hash::check($request->password, $user->password)) {
            return back()->withErrors(['password' => 'Please pick a password different from the temporary one.']);
        }

        $user->update([
            'password' => Hash::make($request->password),
            'must_change_password' => false,
        ]);

        return redirect()->route('dashboard')->with('success', 'Password updated. Welcome to ClinicMS!');
    }
}
