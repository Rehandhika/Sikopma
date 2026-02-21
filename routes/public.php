<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FileDownloadController;
use App\Http\Controllers\PublicPageController;
use App\Http\Controllers\PublicApi\HomeController as PublicHomeApiController;

/*
|--------------------------------------------------------------------------
| File Download Routes (Signed URLs for Private Files)
|--------------------------------------------------------------------------
*/

Route::middleware(['signed'])->group(function () {
    Route::get('/berkas/unduh/{path}/{disk?}', [FileDownloadController::class, 'download'])
        ->name('file.download')
        ->where('path', '.*');

    Route::get('/berkas/lihat/{path}/{disk?}', [FileDownloadController::class, 'view'])
        ->name('file.view')
        ->where('path', '.*');
});

// Authenticated file access (for private files that require login)
Route::middleware(['auth', 'signed'])->group(function () {
    Route::get('/berkas/aman/{path}/{disk?}', [FileDownloadController::class, 'download'])
        ->name('file.secure.download')
        ->where('path', '.*');
});

/*
|--------------------------------------------------------------------------
| Public Routes (No Authentication Required)
|--------------------------------------------------------------------------
*/

// Public Catalog (Home)
Route::get('/', [PublicPageController::class, 'home'])->name('home');

// Test route for debugging (REMOVED as per Phase 0)
// Route::get('/test-react', ...)->name('test.react');

// Public Products
Route::get('/produk', [PublicPageController::class, 'home'])->name('public.products');

// Public Product Detail
Route::get('/produk/{slug}', [PublicPageController::class, 'product'])->name('public.products.show');

// Public About page
Route::get('/tentang', [PublicPageController::class, 'about'])->name('public.about');

// Public JSON API (for React public pages)
Route::prefix('api/publik')
    ->middleware('throttle-api')
    ->name('api.public.')
    ->group(function () {
        Route::get('/tentang', [PublicHomeApiController::class, 'about'])->name('about');
        Route::get('/banner', [PublicHomeApiController::class, 'banners'])->name('banners');
        Route::get('/berita', [PublicHomeApiController::class, 'news'])->name('news');
        Route::get('/kategori', [PublicHomeApiController::class, 'categories'])->name('categories');
        Route::get('/produk', [PublicHomeApiController::class, 'products'])->name('products');
        Route::get('/produk/{slug}', [PublicHomeApiController::class, 'product'])->name('products.show');
        Route::get('/status-toko', [PublicHomeApiController::class, 'storeStatus'])->name('store-status');
        Route::get('/pengaturan-waktu', [PublicHomeApiController::class, 'dateTimeSettings'])->name('datetime-settings');
    });
