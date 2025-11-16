<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SimpleLoginController;
use App\Livewire\Dashboard\Index as DashboardIndex;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return auth()->check() 
        ? redirect()->route('dashboard') 
        : redirect()->route('login');
})->name('home');

/*
|--------------------------------------------------------------------------
| Guest Routes (Login)
|--------------------------------------------------------------------------
*/

Route::middleware('guest')->group(function () {
    Route::get('/login', [SimpleLoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [SimpleLoginController::class, 'login'])->name('login.post');
});

/*
|--------------------------------------------------------------------------
| Protected Routes (Requires Authentication)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth'])->group(function () {
    // Dashboard
    Route::get('/dashboard', DashboardIndex::class)->name('dashboard');
    
    // Logout
    Route::post('/logout', [SimpleLoginController::class, 'logout'])->name('logout');
});
