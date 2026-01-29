<?php

namespace App\Providers;

use App\Listeners\InvalidatePermissionCacheOnPermissionChange;
use App\Listeners\InvalidatePermissionCacheOnRoleChange;
use App\Models\Attendance;
use App\Observers\AttendanceObserver;
use App\Services\DateTimeSettingsService;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Spatie\Permission\Events\PermissionAttached;
use Spatie\Permission\Events\PermissionDetached;
use Spatie\Permission\Events\RoleAttached;
use Spatie\Permission\Events\RoleDetached;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Register DateTimeSettingsService as singleton
        $this->app->singleton(DateTimeSettingsService::class, function ($app) {
            return new DateTimeSettingsService;
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Attendance::observe(AttendanceObserver::class);

        // Register event listeners for permission cache invalidation
        // These handle Spatie Permission events when roles/permissions change
        // @see Requirements 2.3, 8.1, 8.2
        Event::listen(RoleAttached::class, InvalidatePermissionCacheOnRoleChange::class);
        Event::listen(RoleDetached::class, InvalidatePermissionCacheOnRoleChange::class);
        Event::listen(PermissionAttached::class, InvalidatePermissionCacheOnPermissionChange::class);
        Event::listen(PermissionDetached::class, InvalidatePermissionCacheOnPermissionChange::class);

        // Register User observer for additional cache invalidation coverage
        \App\Models\User::observe(\App\Observers\UserRoleObserver::class);

        // Register Blade directives for datetime formatting
        $this->registerDateTimeDirectives();
    }

    /**
     * Register Blade directives for datetime formatting
     */
    protected function registerDateTimeDirectives(): void
    {
        // @formatDate($date) - Format date using system settings
        Blade::directive('formatDate', function ($expression) {
            return "<?php echo app(\App\Services\DateTimeSettingsService::class)->formatDate($expression); ?>";
        });

        // @formatTime($time) - Format time using system settings
        Blade::directive('formatTime', function ($expression) {
            return "<?php echo app(\App\Services\DateTimeSettingsService::class)->formatTime($expression); ?>";
        });

        // @formatDateTime($datetime) - Format datetime using system settings
        Blade::directive('formatDateTime', function ($expression) {
            return "<?php echo app(\App\Services\DateTimeSettingsService::class)->formatDateTime($expression); ?>";
        });

        // @formatDateHuman($date) - Format date in human readable format
        Blade::directive('formatDateHuman', function ($expression) {
            return "<?php echo app(\App\Services\DateTimeSettingsService::class)->formatDateHuman($expression); ?>";
        });

        // @formatDateTimeHuman($datetime) - Format datetime in human readable format
        Blade::directive('formatDateTimeHuman', function ($expression) {
            return "<?php echo app(\App\Services\DateTimeSettingsService::class)->formatDateTimeHuman($expression); ?>";
        });

        // @diffForHumans($datetime) - Get relative time
        Blade::directive('diffForHumans', function ($expression) {
            return "<?php echo app(\App\Services\DateTimeSettingsService::class)->diffForHumans($expression); ?>";
        });

        // @systemTimezone - Get current system timezone
        Blade::directive('systemTimezone', function () {
            return "<?php echo app(\App\Services\DateTimeSettingsService::class)->getTimezone(); ?>";
        });

        // @systemNow - Get current time in system timezone
        Blade::directive('systemNow', function ($expression) {
            $format = $expression ?: "'Y-m-d H:i:s'";

            return "<?php echo app(\App\Services\DateTimeSettingsService::class)->now()->format($format); ?>";
        });
    }
}
