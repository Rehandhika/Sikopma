<?php

namespace App\Services;

use App\Models\User;
use App\Models\LoginHistory;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Request;

class AuthService
{
    /**
     * Authenticate user with NIM and password
     * 
     * @deprecated Use Auth::attempt() instead for better security
     */
    public function authenticate(string $nim, string $password, bool $remember = false): array
    {
        try {
            // Use Laravel's built-in Auth::attempt for better security
            $credentials = [
                'nim' => $nim,
                'password' => $password,
                'status' => 'active',
            ];

            if (Auth::attempt($credentials, $remember)) {
                $user = Auth::user();
                
                // Record successful login
                $this->recordLoginHistory($user, 'success');
                
                Log::info('User logged in successfully', [
                    'user_id' => $user->id,
                    'nim' => $user->nim,
                    'name' => $user->name,
                ]);

                // Regenerate session
                session()->regenerate();

                return [
                    'success' => true,
                    'message' => 'Login berhasil.',
                    'user' => $user,
                ];
            }

            // Try to find user for failed login recording
            $user = User::where('nim', $nim)->first();
            if ($user) {
                $reason = !$user->isActive() ? 'Account inactive' : 'Invalid password';
                $this->recordLoginHistory($user, 'failed', $reason);
            }

            Log::warning('Login attempt failed', ['nim' => $nim]);
            
            return [
                'success' => false,
                'message' => 'NIM atau password salah, atau akun tidak aktif.',
            ];

        } catch (\Exception $e) {
            Log::error('Authentication error', [
                'nim' => $nim,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Terjadi kesalahan saat login. Silakan coba lagi.',
            ];
        }
    }

    /**
     * Logout current user
     */
    public function logout(): void
    {
        $user = Auth::user();
        
        if ($user) {
            // Record logout
            $this->recordLogout($user);
            
            Log::info('User logged out', [
                'user_id' => $user->id,
                'nim' => $user->nim,
            ]);
        }

        Auth::logout();
    }

    /**
     * Check if user can login
     */
    public function canLogin(User $user): array
    {
        if (!$user->isActive()) {
            return [
                'can_login' => false,
                'reason' => 'Akun tidak aktif',
            ];
        }

        if ($user->isSuspended()) {
            return [
                'can_login' => false,
                'reason' => 'Akun ditangguhkan',
            ];
        }

        return [
            'can_login' => true,
            'reason' => null,
        ];
    }

    /**
     * Record login history
     */
    protected function recordLoginHistory(User $user, string $status, ?string $failureReason = null): void
    {
        try {
            LoginHistory::create([
                'user_id' => $user->id,
                'ip_address' => Request::ip(),
                'user_agent' => Request::userAgent(),
                'status' => $status,
                'failure_reason' => $failureReason,
                'logged_in_at' => now(),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to record login history', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Record logout
     */
    public function recordLogout(User $user): void
    {
        try {
            $lastLogin = LoginHistory::where('user_id', $user->id)
                ->whereNull('logged_out_at')
                ->latest('logged_in_at')
                ->first();

            if ($lastLogin) {
                $lastLogin->update(['logged_out_at' => now()]);
            }
        } catch (\Exception $e) {
            Log::error('Failed to record logout', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
