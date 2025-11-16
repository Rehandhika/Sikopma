<?php

namespace App\Livewire\Auth;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class Login extends Component
{
    public $nim = '';
    public $password = '';
    public $remember = false;
    public $errorMessage = '';

    public function login()
    {
        // Reset error
        $this->errorMessage = '';

        // Simple validation
        if (empty($this->nim)) {
            $this->errorMessage = 'NIM wajib diisi.';
            return;
        }

        if (empty($this->password)) {
            $this->errorMessage = 'Password wajib diisi.';
            return;
        }

        // Simple attempt - NO STATUS CHECK FIRST
        if (Auth::attempt(['nim' => $this->nim, 'password' => $this->password], $this->remember)) {
            // Success - regenerate session
            session()->regenerate();
            
            // Redirect
            return redirect()->route('dashboard');
        }

        // Failed
        $this->errorMessage = 'NIM atau password salah.';
    }

    public function render()
    {
        return view('livewire.auth.login')
            ->layout('layouts.guest')
            ->title('Login - SIKOPMA');
    }
}
