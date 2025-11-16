<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\Auth\Login;
use App\Livewire\Dashboard\Index as DashboardIndex;
use App\Livewire\Kopma\Index as KopmaIndex;
use App\Livewire\Schedule\AvailabilityManager;
use App\Livewire\Schedule\ScheduleGenerator;
use App\Livewire\Schedule\ScheduleCalendar;
use App\Livewire\Schedule\MySchedule;
use App\Livewire\Schedule\ScheduleIndex;

// Attendance
use App\Livewire\Attendance\{Index as AttendanceIndex, History as AttendanceHistory, CheckInOut};

// Schedule

// Cashier
use App\Livewire\Cashier\{Pos, SalesList, TransactionForm};

// Product
use App\Livewire\Product\{Index as ProductIndex, ProductList};

// Stock
use App\Livewire\Stock\StockAdjustment;

// Purchase
use App\Livewire\Purchase\PurchaseList;

// Swap
use App\Livewire\Swap\{CreateRequest as SwapCreateRequest, MyRequests as SwapMyRequests, PendingApprovals as SwapPendingApprovals};

// Leave
use App\Livewire\Leave\{CreateRequest as LeaveCreateRequest, MyRequests as LeaveMyRequests, PendingApprovals as LeavePendingApprovals};

// Penalty
use App\Livewire\Penalty\{MyPenalties, ManagePenalties};

// Reports
use App\Livewire\Report\{AttendanceReport, SalesReport, PenaltyReport};

// Settings & Others
use App\Livewire\Notification\MyNotifications;
use App\Livewire\User\UserManagement;
use App\Livewire\Settings\SystemSettings;

// Public routes
Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }
    return redirect()->route('login');
});

Route::get('/login', Login::class)->name('login')->middleware('guest');

Route::post('/logout', function () {
    auth()->logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();
    return redirect()->route('login');
})->name('logout')->middleware('auth');

// Protected routes
Route::middleware(['auth'])->group(function () {
    // Dashboard
    Route::get('/dashboard', DashboardIndex::class)->name('dashboard');

    // Kopma
    Route::get('/kopma', KopmaIndex::class)->name('kopma.index');

    // Attendance Module
    Route::prefix('attendance')->name('attendance.')->middleware(['throttle-sensitive'])->group(function () {
        Route::get('/', AttendanceIndex::class)->name('index');
        Route::get('/check-in-out', CheckInOut::class)->name('check-in-out')->middleware('throttle:10,1'); // 10 requests per minute
        Route::get('/history', AttendanceHistory::class)->name('history');
    });

    // Schedule Module
    Route::prefix('schedule')->name('schedule.')->group(function () {
        Route::get('/', function () {
            return app(ScheduleIndex::class).__invoke();
        })->name('index');
        Route::get('/my-schedule', function () {
            return app(MySchedule::class).__invoke();
        })->name('my-schedule');
        Route::get('/calendar', function () {
            return app(ScheduleCalendar::class).__invoke();
        })->name('calendar');
        Route::get('/availability', function () {
            return app(AvailabilityManager::class).__invoke();
        })->name('availability');
        
        Route::middleware(['role:Super Admin|Ketua|Wakil Ketua'])->group(function () {
            Route::get('/generator', function () {
                return app(ScheduleGenerator::class).__invoke();
            })->name('generator');
        });
    });

    // Cashier/POS Module (Super Admin has full access)
    Route::prefix('cashier')->name('cashier.')->middleware(['role:Super Admin|Ketua|Wakil Ketua|BPH|Anggota'])->group(function () {
        Route::get('/', Pos::class)->name('index');
        Route::get('/pos', Pos::class)->name('pos');
        Route::get('/sales', SalesList::class)->name('sales');
        Route::get('/transactions', TransactionForm::class)->name('transactions');
    });

    // Product Module (Super Admin has full access)
    Route::prefix('products')->name('products.')->middleware(['role:Super Admin|Ketua|Wakil Ketua|BPH'])->group(function () {
        Route::get('/', ProductIndex::class)->name('index');
        Route::get('/list', ProductList::class)->name('list');
        Route::get('/create', ProductList::class)->name('create');
        Route::get('/{id}/edit', ProductList::class)->name('edit');
    });

    // Stock Module (Super Admin has full access)
    Route::prefix('stock')->name('stock.')->middleware(['role:Super Admin|Ketua|Wakil Ketua|BPH'])->group(function () {
        Route::get('/', \App\Livewire\Stock\Index::class)->name('index');
        Route::get('/adjustment', StockAdjustment::class)->name('adjustment');
    });

    // Purchase Module (Super Admin has full access)
    Route::prefix('purchase')->name('purchase.')->middleware(['role:Super Admin|Ketua|Wakil Ketua|BPH'])->group(function () {
        Route::get('/', \App\Livewire\Purchase\Index::class)->name('index');
        Route::get('/list', PurchaseList::class)->name('list');
        Route::get('/create', \App\Livewire\Purchase\Index::class)->name('create');
        Route::get('/{id}', \App\Livewire\Purchase\Index::class)->name('show');
        Route::get('/{id}/receive', \App\Livewire\Purchase\Index::class)->name('receive');
    });

    // Swap Module
    Route::prefix('swap')->name('swap.')->group(function () {
        Route::get('/', \App\Livewire\Swap\Index::class)->name('index');
        Route::get('/create', SwapCreateRequest::class)->name('create');
        Route::get('/my-requests', SwapMyRequests::class)->name('my-requests');
        // Approval routes require admin roles per RBAC spec
        Route::middleware(['role:Super Admin|Ketua|Wakil Ketua|BPH'])->group(function () {
            Route::get('/approval', \App\Livewire\Swap\Approval::class)->name('approval');
            Route::get('/pending', SwapPendingApprovals::class)->name('pending');
        });
    });

    // Leave Module (Super Admin has full access)
    Route::prefix('leave')->name('leave.')->group(function () {
        Route::get('/', \App\Livewire\Leave\Index::class)->name('index');
        Route::get('/create', LeaveCreateRequest::class)->name('create');
        Route::get('/my-requests', LeaveMyRequests::class)->name('my-requests');
        Route::get('/approval', \App\Livewire\Leave\Approval::class)->name('approval');
        Route::get('/pending', LeavePendingApprovals::class)->name('pending');
    });

    // Penalty Module (Super Admin has full access)
    Route::prefix('penalty')->name('penalty.')->group(function () {
        Route::get('/', \App\Livewire\Penalty\Index::class)->name('index');
        Route::get('/my-penalties', MyPenalties::class)->name('my-penalties');
        Route::get('/manage', ManagePenalties::class)->name('manage');
    });

    // Reports Module (Super Admin has full access)
    Route::prefix('reports')->name('reports.')->middleware(['role:Super Admin|Ketua|Wakil Ketua|BPH'])->group(function () {
        Route::get('/attendance', \App\Livewire\Report\AttendanceReport::class)->name('attendance');
        Route::get('/sales', \App\Livewire\Report\SalesReport::class)->name('sales');
        Route::get('/penalty', \App\Livewire\Report\PenaltyReport::class)->name('penalty');
    });

    // Notifications (All authenticated users)
    Route::get('/notifications', \App\Livewire\Notification\Index::class)->name('notifications');

    // User & Role Management (Super Admin has full access)
    Route::prefix('users')->name('users.')->middleware(['role:Super Admin|Ketua|Wakil Ketua'])->group(function () {
        Route::get('/', \App\Livewire\User\Index::class)->name('index');
        Route::get('/manage', UserManagement::class)->name('manage');
    });

    Route::prefix('roles')->name('roles.')->middleware(['role:Super Admin|Ketua'])->group(function () {
        Route::get('/', \App\Livewire\Role\Index::class)->name('index');
    });

    // Settings (Super Admin has full access)
    Route::prefix('settings')->name('settings.')->middleware(['role:Super Admin|Ketua'])->group(function () {
        Route::get('/', \App\Livewire\Settings\General::class)->name('index');
        Route::get('/general', \App\Livewire\Settings\General::class)->name('general');
        Route::get('/system', \App\Livewire\Settings\SystemSettings::class)->name('system');
    });

    // Profile
    Route::get('/profile/edit', \App\Livewire\Profile\Edit::class)->name('profile.edit');
});
