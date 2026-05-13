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
            'email'    => 'required|email',
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
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users',
            'password' => 'required|min:6|confirmed',
            'role'     => 'required|in:nurse,doctor,assistant,admin',
            'phone'    => 'nullable|string|max:50',
        ]);

        $user = User::create([
            'name'     => $validated['name'],
            'email'    => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role'     => $validated['role'],
            'phone'    => $validated['phone'] ?? null,
            'is_active' => true,
            'status'    => 'available',
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
}
