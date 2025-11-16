<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    /**
     * Handle login request
     */
    public function login(Request $request)
    {
        // Rate limiting
        $this->ensureIsNotRateLimited($request);

        // Validate credentials
        $credentials = $request->validate([
            'nim' => 'required|string|min:8|max:20',
            'password' => 'required|string|min:6',
        ], [
            'nim.required' => 'NIM wajib diisi.',
            'nim.min' => 'NIM minimal 8 karakter.',
            'password.required' => 'Password wajib diisi.',
            'password.min' => 'Password minimal 6 karakter.',
        ]);

        // Add status check to credentials
        $credentials['status'] = 'active';

        // Attempt authentication
        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            // Clear rate limiter
            RateLimiter::clear($this->throttleKey($request));
            
            // Regenerate session
            session()->regenerate();
            
            // Return success response
            return response()->json([
                'success' => true,
                'message' => 'Login berhasil',
                'redirect' => route('dashboard'),
            ]);
        }

        // Increment rate limiter
        RateLimiter::hit($this->throttleKey($request));

        // Return error
        throw ValidationException::withMessages([
            'nim' => 'NIM atau password salah, atau akun tidak aktif.',
        ]);
    }

    /**
     * Handle logout request
     */
    public function logout(Request $request)
    {
        Auth::logout();
        
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect()->route('login')->with('success', 'Anda telah berhasil logout.');
    }

    /**
     * Ensure the login request is not rate limited
     */
    protected function ensureIsNotRateLimited(Request $request)
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey($request), 5)) {
            return;
        }

        $seconds = RateLimiter::availableIn($this->throttleKey($request));

        throw ValidationException::withMessages([
            'nim' => "Terlalu banyak percobaan login. Silakan coba lagi dalam {$seconds} detik.",
        ]);
    }

    /**
     * Get the rate limiting throttle key
     */
    protected function throttleKey(Request $request)
    {
        return Str::transliterate(Str::lower($request->input('nim')).'|'.$request->ip());
    }
}
