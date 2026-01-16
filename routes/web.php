<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LogoutController;
use App\Http\Controllers\PublicApi\HomeController as PublicHomeApiController;
use App\Livewire\Dashboard\Index as DashboardIndex;

/*
|--------------------------------------------------------------------------
| Public Routes (No Authentication Required)
|--------------------------------------------------------------------------
*/

// Public Catalog (Home)
Route::get('/', function () {
    return view('public.react', ['page' => 'home']);
})->name('home');

// Public Products
Route::get('/products', function () {
    return view('public.react', ['page' => 'home']);
})->name('public.products');

// Public Product Detail
Route::get('/products/{slug}', function (string $slug) {
    return view('public.react', ['page' => 'product', 'slug' => $slug]);
})->name('public.products.show');

// Public About page
Route::get('/about', function () {
    return view('public.react', ['page' => 'about']);
})->name('public.about');

// Public JSON API (for React public pages)
Route::prefix('api/public')
    ->middleware('throttle-api')
    ->name('api.public.')
    ->group(function () {
        Route::get('/about', [PublicHomeApiController::class, 'about'])->name('about');
        Route::get('/banners', [PublicHomeApiController::class, 'banners'])->name('banners');
        Route::get('/categories', [PublicHomeApiController::class, 'categories'])->name('categories');
        Route::get('/products', [PublicHomeApiController::class, 'products'])->name('products');
        Route::get('/products/{slug}', [PublicHomeApiController::class, 'product'])->name('products.show');
        Route::get('/store-status', [PublicHomeApiController::class, 'storeStatus'])->name('store-status');
    });

// Temporary test route for public layout
Route::get('/public-test', function () {
    return view('public.test-layout');
})->name('public.test');

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

Route::get('/demo/modal-debug', function () {
    return view('modal-debug');
})->name('demo.modal.debug');

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

// Alpine.js Test Page
Route::get('/alpine-test', function () {
    return view('alpine-test');
})->name('alpine.test');

/*
|--------------------------------------------------------------------------
| Guest Routes (Login)
|--------------------------------------------------------------------------
*/

Route::middleware('guest')->group(function () {
    Route::get('/admin/login', \App\Livewire\Auth\LoginForm::class)->name('login');
});

/*
|--------------------------------------------------------------------------
| Backward Compatibility Redirects
|--------------------------------------------------------------------------
*/

Route::redirect('/login', '/admin/login');
Route::redirect('/dashboard', '/admin/dashboard');
Route::redirect('/attendance', '/admin/attendance');
Route::redirect('/schedule', '/admin/schedule');
Route::redirect('/cashier', '/admin/cashier');
Route::redirect('/stock', '/admin/stock');
Route::redirect('/purchase', '/admin/purchase');
Route::redirect('/leave', '/admin/leave');
Route::redirect('/swap', '/admin/swap');
Route::redirect('/penalties', '/admin/penalties');
Route::redirect('/reports', '/admin/reports');
Route::redirect('/users', '/admin/users');
Route::redirect('/roles', '/admin/roles');
Route::redirect('/settings', '/admin/settings');
Route::redirect('/profile', '/admin/profile');
Route::redirect('/notifications', '/admin/notifications');

/*
|--------------------------------------------------------------------------
| Admin Routes (Requires Authentication)
|--------------------------------------------------------------------------
*/

Route::prefix('admin')
    ->middleware(['auth'])
    ->name('admin.')
    ->group(function () {
        // Dashboard
        Route::get('/dashboard', DashboardIndex::class)->name('dashboard');
        
        // Logout
        Route::post('/logout', [LogoutController::class, 'logout'])->name('logout');
        
        // Attendance
        Route::prefix('attendance')->name('attendance.')->group(function () {
            Route::get('/check-in-out', \App\Livewire\Attendance\CheckInOut::class)->name('check-in-out');
            Route::get('/', \App\Livewire\Admin\AttendanceManagement::class)->name('index');
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
            Route::get('/{schedule}/edit', \App\Livewire\Schedule\EditSchedule::class)->name('edit');
            Route::get('/{schedule}/history', \App\Livewire\Schedule\EditHistory::class)->name('history');
        });
        
        // Cashier / POS
        Route::prefix('cashier')->name('cashier.')->group(function () {
            Route::get('/pos', \App\Livewire\Cashier\Pos::class)->name('pos');
            
            // POS Entry - restricted to admin roles only (Requirements 10.1, 10.2)
            Route::get('/pos-entry', \App\Livewire\Cashier\PosEntry::class)
                ->middleware('role:Super Admin|Ketua|Wakil Ketua')
                ->name('pos-entry');
        });
        
        // Products
        Route::prefix('products')->name('products.')->group(function () {
            Route::get('/', \App\Livewire\Product\Index::class)->name('index');
            Route::get('/list', \App\Livewire\Product\ProductList::class)->name('list');
            Route::get('/create', \App\Livewire\Product\CreateProduct::class)->name('create');
            Route::get('/{product}/edit', \App\Livewire\Product\EditProduct::class)->name('edit');
        });
        
        // Stock - Unified single page management
        Route::prefix('stock')->name('stock.')->group(function () {
            Route::get('/', \App\Livewire\Stock\StockManager::class)->name('index');
            // Legacy routes redirect to new unified page
            Route::redirect('/adjustment', '/admin/stock?activeTab=history')->name('adjustment');
        });
        
        // Purchase
        Route::prefix('purchase')->name('purchase.')->group(function () {
            Route::get('/', \App\Livewire\Purchase\Index::class)->name('index');
            Route::get('/list', \App\Livewire\Purchase\PurchaseList::class)->name('list');
        });
        
        // Leave Requests - Redesigned Single Page
        Route::prefix('leave')->name('leave.')->group(function () {
            Route::get('/', \App\Livewire\Leave\LeaveManager::class)->name('index');
            // Legacy routes redirect to new unified page
            Route::redirect('/my-requests', '/admin/leave')->name('my-requests');
            Route::redirect('/create', '/admin/leave')->name('create');
            Route::redirect('/approvals', '/admin/leave?tab=approvals')->name('approvals');
        });
        
        // Schedule Change Requests (formerly Swap) - Unified Single Page
        Route::prefix('swap')->name('swap.')->group(function () {
            Route::get('/', \App\Livewire\Schedule\ScheduleChangeManager::class)->name('index');
            // Legacy routes redirect
            Route::redirect('/my-requests', '/admin/swap')->name('my-requests');
            Route::redirect('/create', '/admin/swap')->name('create');
            Route::redirect('/approvals', '/admin/swap?tab=admin')->name('approvals');
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
            Route::get('/store', \App\Livewire\Admin\Settings\StoreSettings::class)
                ->middleware('role:Super Admin|Ketua|Wakil Ketua')
                ->name('store');
            Route::get('/banners', \App\Livewire\Admin\BannerManagement::class)
                ->middleware('role:Super Admin|Ketua')
                ->name('banners');
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
