<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use App\Models\User;
use App\Models\LoginHistory;

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
        // Validate input
        $validated = $request->validate([
            'nim' => 'required|string|min:8|max:20',
            'password' => 'required|string|min:6',
        ]);
        
        $nim = $validated['nim'];
        $password = $validated['password'];
        
        // Rate limiting
        $key = $this->throttleKey($nim, $request);
        
        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);
            
            return back()->with('error', "Terlalu banyak percobaan login. Coba lagi dalam {$seconds} detik.");
        }
        
        // Attempt login with status check
        $credentials = [
            'nim' => $nim,
            'password' => $password,
            'status' => 'active',
        ];
        
        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            // Clear rate limiter on success
            RateLimiter::clear($key);
            
            // Regenerate session
            session()->regenerate();
            
            // Log successful login
            LoginHistory::create([
                'user_id' => Auth::id(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'logged_in_at' => now(),
                'status' => 'success',
            ]);
            
            return redirect()->intended(route('dashboard'));
        }
        
        // Increment rate limiter on failure
        RateLimiter::hit($key, 60);
        
        // Log failed login
        LoginHistory::create([
            'user_id' => null,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'logged_in_at' => now(),
            'status' => 'failed',
            'failure_reason' => 'Invalid credentials or inactive account',
        ]);
        
        return back()->with('error', 'NIM atau password salah, atau akun tidak aktif');
    }
    
    /**
     * Get the rate limiting throttle key for the request.
     */
    protected function throttleKey(string $nim, Request $request): string
    {
        return Str::transliterate(Str::lower($nim) . '|' . $request->ip());
    }
    
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect()->route('login')->with('success', 'Anda telah berhasil logout.');
    }
}
