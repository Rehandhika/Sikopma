<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;

/*
|--------------------------------------------------------------------------
| Authentication Routes
|--------------------------------------------------------------------------
*/

Route::middleware('guest')->group(function () {
    // Login form submission (for AJAX/API)
    Route::post('/auth/login', [AuthController::class, 'login'])
        ->name('auth.login')
        ->middleware('throttle:5,1'); // 5 attempts per minute
});

Route::middleware('auth')->group(function () {
    // Logout
    Route::post('/auth/logout', [AuthController::class, 'logout'])
        ->name('auth.logout');
});
