<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LogoutController;

// Login Route (Guest)
Route::middleware('guest')->group(function () {
    Route::get('/admin/masuk', \App\Livewire\Auth\LoginForm::class)->name('login');
});

// Authenticated Auth Routes
Route::middleware(['auth'])->group(function () {
    Route::post('/keluar', [LogoutController::class, 'logout'])->name('logout');
    Route::post('/admin/keluar', [LogoutController::class, 'logout'])->name('admin.logout');
});
