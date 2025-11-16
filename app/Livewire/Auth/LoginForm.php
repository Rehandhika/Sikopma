<?php

namespace App\Livewire\Auth;

use Livewire\Component;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use App\Models\LoginHistory;

#[Title('Login - SIKOPMA')]
#[Layout('layouts.guest')]
class LoginForm extends Component
{
    public $nim = '';
    public $password = '';
    public $remember = false;

    protected $rules = [
        'nim' => 'required|string|min:8|max:20',
        'password' => 'required|string|min:6',
    ];

    protected $messages = [
        'nim.required' => 'NIM wajib diisi',
        'nim.min' => 'NIM minimal 8 karakter',
        'nim.max' => 'NIM maksimal 20 karakter',
        'password.required' => 'Password wajib diisi',
        'password.min' => 'Password minimal 6 karakter',
    ];

    public function login()
    {
        $this->validate();

        // Rate limiting
        $key = $this->throttleKey();
        
        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);
            
            $this->addError('nim', "Terlalu banyak percobaan login. Coba lagi dalam {$seconds} detik.");
            return;
        }

        // Attempt login
        $credentials = [
            'nim' => $this->nim,
            'password' => $this->password,
            'status' => 'active',
        ];

        if (Auth::attempt($credentials, $this->remember)) {
            // Clear rate limiter
            RateLimiter::clear($key);
            
            // Regenerate session
            session()->regenerate();
            
            // Log successful login
            LoginHistory::create([
                'user_id' => Auth::id(),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'logged_in_at' => now(),
                'status' => 'success',
            ]);
            
            // Redirect to dashboard
            return redirect()->intended(route('dashboard'));
        }

        // Increment rate limiter
        RateLimiter::hit($key, 60);
        
        // Log failed login
        LoginHistory::create([
            'user_id' => null,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'logged_in_at' => now(),
            'status' => 'failed',
            'failure_reason' => 'Invalid credentials or inactive account',
        ]);

        $this->addError('nim', 'NIM atau password salah, atau akun tidak aktif.');
    }

    protected function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->nim) . '|' . request()->ip());
    }

    public function render()
    {
        return view('livewire.auth.login-form');
    }
}
