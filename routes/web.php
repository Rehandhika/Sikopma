<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LogoutController;
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

// Component Demo Routes (for testing - remove in production)
Route::get('/demo/button', function () {
    return view('components.ui.button-demo');
})->name('demo.button');

Route::get('/demo/input', function () {
    return view('components.ui.input-demo');
})->name('demo.input');

Route::get('/demo/input-livewire', \App\Livewire\TestInputComponent::class)->name('demo.input-livewire');

Route::get('/demo/form-components', function () {
    return view('components.ui.form-components-demo');
})->name('demo.form-components');

Route::get('/demo/form-validation', \App\Livewire\TestFormComponents::class)->name('demo.form-validation');

Route::get('/demo/card', function () {
    return view('components.ui.card-demo');
})->name('demo.card');

Route::get('/demo/badge', function () {
    return view('components.ui.badge-demo');
})->name('demo.badge');

Route::get('/demo/alert', function () {
    return view('components.ui.alert-demo');
})->name('demo.alert');

Route::get('/demo/modal', function () {
    return view('components.ui.modal-test');
})->name('demo.modal');

Route::get('/demo/modal-example', function () {
    return view('components.ui.modal-example');
})->name('demo.modal-example');

Route::get('/demo/feedback', function () {
    return view('components.ui.feedback-components-test');
})->name('demo.feedback');

Route::get('/demo/dropdown', function () {
    return view('components.ui.dropdown-test');
})->name('demo.dropdown');

Route::get('/demo/page-header', function () {
    return view('components.layout.page-header-test');
})->name('demo.page-header');

Route::get('/demo/stat-card', function () {
    return view('components.layout.stat-card-test');
})->name('demo.stat-card');

Route::get('/demo/empty-state', function () {
    return view('components.layout.empty-state-test');
})->name('demo.empty-state');

Route::get('/demo/table', function () {
    return view('components.data.table-test');
})->name('demo.table');

Route::get('/demo/breadcrumb', function () {
    return view('components.data.breadcrumb-test');
})->name('demo.breadcrumb');

/*
|--------------------------------------------------------------------------
| Guest Routes (Login)
|--------------------------------------------------------------------------
*/

Route::middleware('guest')->group(function () {
    Route::get('/login', \App\Livewire\Auth\LoginForm::class)->name('login');
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
    Route::post('/logout', [LogoutController::class, 'logout'])->name('logout');
    
    // Attendance
    Route::prefix('attendance')->name('attendance.')->group(function () {
        Route::get('/check-in-out', \App\Livewire\Attendance\CheckInOut::class)->name('check-in-out');
        Route::get('/', \App\Livewire\Attendance\Index::class)->name('index');
        Route::get('/history', \App\Livewire\Attendance\History::class)->name('history');
    });
    
    // Schedule
    Route::prefix('schedule')->name('schedule.')->group(function () {
        Route::get('/', \App\Livewire\Schedule\Index::class)->name('index');
        Route::get('/create', \App\Livewire\Schedule\CreateSchedule::class)->name('create');
        Route::get('/my-schedule', \App\Livewire\Schedule\MySchedule::class)->name('my-schedule');
        Route::get('/availability', \App\Livewire\Schedule\AvailabilityManager::class)->name('availability');
        Route::get('/test-availability', \App\Livewire\Schedule\TestAvailability::class)->name('test-availability');
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
        Route::get('/create', \App\Livewire\Product\CreateProduct::class)->name('create');
        Route::get('/{product}/edit', \App\Livewire\Product\EditProduct::class)->name('edit');
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
