<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class SimpleLoginController extends Controller
{
    public function showLoginForm()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }
        
        return view('auth.simple-login');
    }
    
    public function login(Request $request)
    {
        $nim = $request->input('nim');
        $password = $request->input('password');
        
        // Validate
        if (empty($nim) || empty($password)) {
            return back()->with('error', 'NIM dan Password wajib diisi');
        }
        
        // Attempt login
        if (Auth::attempt(['nim' => $nim, 'password' => $password], $request->boolean('remember'))) {
            session()->regenerate();
            
            return redirect()->intended(route('dashboard'));
        }
        
        return back()->with('error', 'NIM atau password salah');
    }
    
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect()->route('login')->with('success', 'Anda telah berhasil logout.');
    }
}
