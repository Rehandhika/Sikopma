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
    
    // Attendance
    Route::prefix('attendance')->name('attendance.')->group(function () {
        Route::get('/check-in-out', \App\Livewire\Attendance\CheckInOut::class)->name('check-in-out');
        Route::get('/', \App\Livewire\Attendance\Index::class)->name('index');
        Route::get('/history', \App\Livewire\Attendance\History::class)->name('history');
    });
    
    // Schedule
    Route::prefix('schedule')->name('schedule.')->group(function () {
        Route::get('/', \App\Livewire\Schedule\Index::class)->name('index');
        Route::get('/my-schedule', \App\Livewire\Schedule\MySchedule::class)->name('my-schedule');
        Route::get('/availability', \App\Livewire\Schedule\AvailabilityManager::class)->name('availability');
        Route::get('/calendar', \App\Livewire\Schedule\ScheduleCalendar::class)->name('calendar');
        Route::get('/generator', \App\Livewire\Schedule\ScheduleGenerator::class)->name('generator');
    });
    
    // Cashier / POS
    Route::prefix('cashier')->name('cashier.')->group(function () {
        Route::get('/pos', \App\Livewire\Cashier\Pos::class)->name('pos');
        Route::get('/sales', \App\Livewire\Cashier\SalesList::class)->name('sales');
    });
    
    // Products
    Route::prefix('products')->name('products.')->group(function () {
        Route::get('/', \App\Livewire\Product\Index::class)->name('index');
        Route::get('/list', \App\Livewire\Product\ProductList::class)->name('list');
    });
    
    // Stock
    Route::prefix('stock')->name('stock.')->group(function () {
        Route::get('/', \App\Livewire\Stock\Index::class)->name('index');
        Route::get('/adjustment', \App\Livewire\Stock\StockAdjustment::class)->name('adjustment');
    });
    
    // Purchase
    Route::prefix('purchase')->name('purchase.')->group(function () {
        Route::get('/', \App\Livewire\Purchase\Index::class)->name('index');
        Route::get('/list', \App\Livewire\Purchase\PurchaseList::class)->name('list');
    });
    
    // Leave Requests
    Route::prefix('leave')->name('leave.')->group(function () {
        Route::get('/', \App\Livewire\Leave\Index::class)->name('index');
        Route::get('/my-requests', \App\Livewire\Leave\MyRequests::class)->name('my-requests');
        Route::get('/create', \App\Livewire\Leave\CreateRequest::class)->name('create');
        Route::get('/approvals', \App\Livewire\Leave\PendingApprovals::class)->name('approvals');
    });
    
    // Swap Requests
    Route::prefix('swap')->name('swap.')->group(function () {
        Route::get('/', \App\Livewire\Swap\Index::class)->name('index');
        Route::get('/my-requests', \App\Livewire\Swap\MyRequests::class)->name('my-requests');
        Route::get('/create', \App\Livewire\Swap\CreateRequest::class)->name('create');
        Route::get('/approvals', \App\Livewire\Swap\PendingApprovals::class)->name('approvals');
    });
    
    // Penalties
    Route::prefix('penalties')->name('penalties.')->group(function () {
        Route::get('/', \App\Livewire\Penalty\Index::class)->name('index');
        Route::get('/my-penalties', \App\Livewire\Penalty\MyPenalties::class)->name('my-penalties');
        Route::get('/manage', \App\Livewire\Penalty\ManagePenalties::class)->name('manage');
    });
    
    // Reports
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/attendance', \App\Livewire\Report\AttendanceReport::class)->name('attendance');
        Route::get('/sales', \App\Livewire\Report\SalesReport::class)->name('sales');
        Route::get('/penalties', \App\Livewire\Report\PenaltyReport::class)->name('penalties');
    });
    
    // Analytics
    Route::prefix('analytics')->name('analytics.')->group(function () {
        Route::get('/dashboard', \App\Livewire\Analytics\BIDashboard::class)->name('dashboard');
    });
    
    // Users Management
    Route::prefix('users')->name('users.')->group(function () {
        Route::get('/', \App\Livewire\User\Index::class)->name('index');
        Route::get('/management', \App\Livewire\User\UserManagement::class)->name('management');
    });
    
    // Roles & Permissions
    Route::prefix('roles')->name('roles.')->group(function () {
        Route::get('/', \App\Livewire\Role\Index::class)->name('index');
    });
    
    // Settings
    Route::prefix('settings')->name('settings.')->group(function () {
        Route::get('/general', \App\Livewire\Settings\General::class)->name('general');
        Route::get('/system', \App\Livewire\Settings\SystemSettings::class)->name('system');
    });
    
    // Profile
    Route::prefix('profile')->name('profile.')->group(function () {
        Route::get('/edit', \App\Livewire\Profile\Edit::class)->name('edit');
    });
    
    // Notifications
    Route::prefix('notifications')->name('notifications.')->group(function () {
        Route::get('/', \App\Livewire\Notification\Index::class)->name('index');
        Route::get('/my-notifications', \App\Livewire\Notification\MyNotifications::class)->name('my-notifications');
    });
});
