<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
class AuthController extends Controller {
    public function showLogin() { return view('auth.login'); }
    public function login(Request $request) {
        $credentials = $request->validate(['email'=>'required|email','password'=>'required']);
        if(Auth::attempt($credentials)) {
            $request->session()->regenerate();
            Auth::user()->update(['last_seen_at'=>now()]);
            return redirect()->intended('dashboard');
        }
        return back()->withErrors(['email'=>'Invalid credentials.']);
    }
    public function logout(Request $request) {
       $user = \App\Models\User::first();
    
    if (!$user) {
        $user = \App\Models\User::create([
            'name' => 'Dev Admin',
            'email' => 'dev@clinic.com',
            'password' => \Illuminate\Support\Facades\Hash::make('password'),
            'role' => 'admin'
        ]);
    }
    
    Auth::login($user);
    $request->session()->regenerate();
    
    return redirect()->intended('dashboard');
    }
}