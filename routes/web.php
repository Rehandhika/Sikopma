<?php

use App\Http\Controllers\FileDownloadController;
use App\Http\Controllers\LogoutController;
use App\Http\Controllers\PublicApi\HomeController as PublicHomeApiController;
use App\Http\Controllers\PublicPageController;
use App\Livewire\Dashboard\Index as DashboardIndex;
use Illuminate\Support\Facades\Route;

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

// Test route for debugging
Route::get('/test-react', function () {
    return view('public.test');
})->name('test.react');

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

/*
|--------------------------------------------------------------------------
| Guest Routes (Login)
|--------------------------------------------------------------------------
*/

Route::middleware('guest')->group(function () {
    Route::get('/admin/masuk', \App\Livewire\Auth\LoginForm::class)->name('login');
});

/*
|--------------------------------------------------------------------------
| Backward Compatibility Redirects
|--------------------------------------------------------------------------
*/

Route::redirect('/login', '/admin/masuk');
Route::redirect('/admin/login', '/admin/masuk');
Route::redirect('/dashboard', '/admin/beranda');
Route::redirect('/attendance', '/admin/absensi');
Route::redirect('/schedule', '/admin/jadwal');
Route::redirect('/cashier', '/admin/kasir');
Route::redirect('/stock', '/admin/stok');
Route::redirect('/purchase', '/admin/pembelian');
Route::redirect('/leave', '/admin/cuti');
Route::redirect('/swap', '/admin/tukar-jadwal');
Route::redirect('/penalties', '/admin/penalti');
Route::redirect('/reports', '/admin/laporan');
Route::redirect('/users', '/admin/pengguna');
Route::redirect('/roles', '/admin/peran');
Route::redirect('/poin-shu', '/admin/poin-shu/monitoring');
Route::redirect('/admin/poin-shu', '/admin/poin-shu/monitoring');
Route::redirect('/admin/poin-shu/redemptions', '/admin/poin-shu/monitoring');
Route::redirect('/admin/poin-shu/students', '/admin/poin-shu/monitoring');
Route::redirect('/admin/poin-shu/settings', '/admin/pengaturan/toko');

/*
|--------------------------------------------------------------------------
| Authenticated Routes (Requires Login)
|--------------------------------------------------------------------------
|
| SELF-SERVICE ROUTES: Accessible to ALL authenticated users
| - Check-in/out absensi
| - View own schedule, attendance, penalties
| - Access POS/Kasir
| - Submit leave/swap requests
| - Edit own profile
|
| MANAGEMENT ROUTES: Require specific permissions
| - kelola_* permissions for CRUD operations
| - lihat_semua_* permissions for viewing all users' data
| - setujui_* permissions for approval workflows
|
*/

Route::middleware(['auth'])->group(function () {
    // Logout
    Route::post('/keluar', [LogoutController::class, 'logout'])->name('logout');
    Route::post('/admin/keluar', [LogoutController::class, 'logout'])->name('admin.logout');

    // Admin Routes (Prefix: /admin)
    Route::prefix('admin')->name('admin.')->group(function () {
        
        // ============================================================
        // SELF-SERVICE ROUTES (All authenticated users)
        // ============================================================
        
        // Dashboard (Beranda) - All authenticated users
        Route::get('/beranda', DashboardIndex::class)->name('dashboard');

        // Profile (Profil) - All authenticated users (ubah_profil)
        Route::prefix('profil')->name('profile.')->group(function () {
            Route::get('/ubah', \App\Livewire\Profile\Edit::class)->name('edit');
        });

        // Notifications (Notifikasi) - All authenticated users
        Route::prefix('notifikasi')->name('notifications.')->group(function () {
            Route::get('/', \App\Livewire\Notification\Index::class)->name('index');
            Route::get('/notifikasi-saya', \App\Livewire\Notification\MyNotifications::class)->name('my-notifications');
        });

        // Attendance Self-Service (check_in_out, lihat_absensi_sendiri)
        Route::prefix('absensi')->name('attendance.')->group(function () {
            // Self-service - check-in/out for own attendance
            Route::get('/check-in-out', \App\Livewire\Attendance\CheckInOut::class)->name('check-in-out');
            // Self-service - view own attendance history
            Route::get('/riwayat', \App\Livewire\Attendance\History::class)->name('history');
            
            // Management - requires kelola_absensi permission
            Route::middleware('can:kelola_absensi')->group(function () {
                Route::get('/', \App\Livewire\Admin\AttendanceManagement::class)->name('index');
            });
        });

        // Schedule Self-Service (lihat_jadwal_sendiri, input_ketersediaan)
        Route::prefix('jadwal')->name('schedule.')->group(function () {
            // Self-service - view own schedule
            Route::get('/jadwal-saya', \App\Livewire\Schedule\MySchedule::class)->name('my-schedule');
            // Self-service - input availability
            Route::get('/ketersediaan', \App\Livewire\Schedule\AvailabilityManager::class)->name('availability');
            
            // Management - requires lihat_semua_jadwal or kelola_jadwal permission
            Route::middleware('can:lihat_semua_jadwal')->group(function () {
                Route::get('/', \App\Livewire\Schedule\Index::class)->name('index');
                Route::get('/kalender', \App\Livewire\Schedule\ScheduleCalendar::class)->name('calendar');
                Route::get('/statistik', \App\Livewire\Schedule\ScheduleStatistics::class)->name('statistics');
            });
            
            // Create/Edit schedule - requires kelola_jadwal permission
            Route::middleware('can:kelola_jadwal')->group(function () {
                Route::get('/buat', \App\Livewire\Schedule\CreateSchedule::class)->name('create');
                Route::get('/generator', \App\Livewire\Schedule\ScheduleGenerator::class)->name('generator');
                Route::get('/template', \App\Livewire\Schedule\ScheduleTemplates::class)->name('templates');
                Route::get('/{schedule}/edit', \App\Livewire\Schedule\EditSchedule::class)->name('edit');
                Route::get('/{schedule}/riwayat', \App\Livewire\Schedule\EditHistory::class)->name('history');
                Route::get('/perubahan', \App\Livewire\Schedule\ScheduleChangeManager::class)->name('changes');
            });
        });

        // Cashier/POS (akses_kasir) - All authenticated users can access POS
        Route::prefix('kasir')->name('cashier.')->group(function () {
            Route::get('/pos', \App\Livewire\Cashier\Pos::class)->name('pos');
            Route::get('/entry', \App\Livewire\Cashier\PosEntry::class)->name('pos-entry');
        });

        // Leave Requests Self-Service (ajukan_cuti)
        Route::prefix('cuti')->name('leave.')->group(function () {
            // Self-service - submit and view own leave requests
            Route::get('/', \App\Livewire\Leave\Index::class)->name('index');
            Route::get('/pengajuan-baru', \App\Livewire\Leave\CreateRequest::class)->name('create');
            Route::get('/pengajuan-saya', \App\Livewire\Leave\MyRequests::class)->name('my-requests');
            
            // Approval routes - requires setujui_cuti permission
            Route::middleware('can:setujui_cuti')->group(function () {
                Route::get('/persetujuan', \App\Livewire\Leave\PendingApprovals::class)->name('approvals');
            });
            
            // Management - requires kelola_cuti permission
            Route::middleware('can:kelola_cuti')->group(function () {
                Route::get('/manajemen', \App\Livewire\Leave\LeaveManager::class)->name('manager');
            });
        });

        // Swap Requests Self-Service (ajukan_tukar_jadwal)
        Route::prefix('tukar-jadwal')->name('swap.')->group(function () {
            // Self-service - submit and view own swap requests
            Route::get('/', \App\Livewire\Swap\Index::class)->name('index');
            Route::get('/dashboard', \App\Livewire\Swap\SwapDashboard::class)->name('dashboard');
            Route::get('/pengajuan-baru', \App\Livewire\Swap\CreateRequest::class)->name('create');
            Route::get('/pengajuan-saya', \App\Livewire\Swap\MyRequests::class)->name('my-requests');
            
            // Approval routes - requires setujui_tukar_jadwal permission
            Route::middleware('can:setujui_tukar_jadwal')->group(function () {
                Route::get('/persetujuan', \App\Livewire\Swap\PendingApprovals::class)->name('approvals');
            });
            
            // Management - requires kelola_tukar_jadwal permission
            Route::middleware('can:kelola_tukar_jadwal')->group(function () {
                Route::get('/manajemen', \App\Livewire\Swap\SwapManager::class)->name('manager');
            });
        });

        // Penalties Self-Service (lihat_penalti_sendiri)
        Route::prefix('penalti')->name('penalties.')->group(function () {
            // Self-service - view own penalties
            Route::get('/penalti-saya', \App\Livewire\Penalty\MyPenalties::class)->name('my-penalties');
            
            // View all penalties - requires lihat_semua_penalti permission
            Route::middleware('can:lihat_semua_penalti')->group(function () {
                Route::get('/', \App\Livewire\Penalty\Index::class)->name('index');
            });
            
            // Manage penalties - requires kelola_penalti permission
            Route::get('/kelola', \App\Livewire\Penalty\ManagePenalties::class)
                ->middleware('can:kelola_penalti')
                ->name('manage');
        });

        // ============================================================
        // MANAGEMENT ROUTES (Require specific permissions)
        // ============================================================

        // Stock Management (lihat_stok, kelola_stok)
        Route::prefix('stok')->name('stock.')->middleware('can:lihat_stok')->group(function () {
            Route::get('/', \App\Livewire\Stock\Index::class)->name('index');
        });

        // Products (lihat_produk, kelola_produk)
        Route::prefix('produk')->name('products.')->middleware('can:lihat_produk')->group(function () {
            Route::get('/', \App\Livewire\Product\Index::class)->name('index');
            Route::get('/daftar', \App\Livewire\Product\ProductList::class)->name('list');
            
            // Create/Edit products - requires kelola_produk permission
            Route::middleware('can:kelola_produk')->group(function () {
                Route::get('/buat', \App\Livewire\Product\CreateProduct::class)->name('create');
                Route::get('/{product}/edit', \App\Livewire\Product\EditProduct::class)->name('edit');
            });
        });

        // Purchases (lihat_pembelian, kelola_pembelian)
        Route::prefix('pembelian')->name('purchase.')->middleware('can:lihat_pembelian')->group(function () {
            Route::get('/', \App\Livewire\Purchase\Index::class)->name('index');
            Route::get('/daftar', \App\Livewire\Purchase\PurchaseList::class)->name('list');
        });

        // Reports (lihat_laporan)
        Route::prefix('laporan')->name('reports.')->middleware('can:lihat_laporan')->group(function () {
            Route::get('/absensi', \App\Livewire\Report\AttendanceReport::class)->name('attendance');
            Route::get('/penjualan', \App\Livewire\Report\SalesReport::class)->name('sales');
            Route::get('/penalti', \App\Livewire\Report\PenaltyReport::class)->name('penalties');
        });

        // Users Management (lihat_pengguna, kelola_pengguna)
        Route::prefix('pengguna')->name('users.')->middleware('can:lihat_pengguna')->group(function () {
            Route::get('/', \App\Livewire\User\Index::class)->name('index');
            
            // User management - requires kelola_pengguna permission
            Route::get('/manajemen', \App\Livewire\User\UserManagement::class)
                ->middleware('can:kelola_pengguna')
                ->name('management');
        });

        // Roles & Permissions (lihat_peran, kelola_peran)
        Route::prefix('peran')->name('roles.')->middleware('can:lihat_peran')->group(function () {
            Route::get('/', \App\Livewire\Role\Index::class)->name('index');
        });

        // Activity Log (lihat_log_aktivitas) - Admin level
        Route::get('/log-aktivitas', \App\Livewire\Admin\ActivityLogViewer::class)
            ->middleware('can:lihat_log_aktivitas')
            ->name('activity-log');

        // Settings (kelola_pengaturan)
        Route::prefix('pengaturan')->name('settings.')->middleware('can:kelola_pengaturan')->group(function () {
            Route::get('/sistem', \App\Livewire\Settings\SystemSettings::class)->name('system');
            Route::get('/toko', \App\Livewire\Admin\Settings\StoreSettings::class)->name('store');
            Route::get('/banner', \App\Livewire\Admin\BannerNewsManagement::class)->name('banners');
            Route::get('/pembayaran', \App\Livewire\Settings\PaymentSettings::class)->name('payment');
        });

        // Poin SHU (lihat_poin_shu, kelola_poin_shu)
        Route::prefix('poin-shu')->name('poin-shu.')->middleware('can:lihat_poin_shu')->group(function () {
            Route::get('/monitoring', \App\Livewire\ShuPoint\Monitoring::class)->name('monitoring');
            Route::get('/mahasiswa/{student}', \App\Livewire\ShuPoint\StudentDetail::class)->name('student');
        });
    });
});
