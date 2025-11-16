<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\AttendanceRepository;
use App\Repositories\ScheduleRepository;
use App\Repositories\SwapRepository;
use App\Repositories\SalesRepository;
use App\Services\AttendanceService;
use App\Services\NotificationService;
use App\Services\SwapService;
use App\Services\LeaveService;
use App\Services\ScheduleService;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Repository bindings
        $this->app->bind(AttendanceRepository::class, function ($app) {
            return new AttendanceRepository();
        });

        $this->app->bind(ScheduleRepository::class, function ($app) {
            return new ScheduleRepository();
        });

        $this->app->bind(SwapRepository::class, function ($app) {
            return new SwapRepository();
        });

        $this->app->bind(SalesRepository::class, function ($app) {
            return new SalesRepository();
        });

        // Service bindings
        $this->app->bind(AttendanceService::class, function ($app) {
            return new AttendanceService($app->make(AttendanceRepository::class));
        });

        $this->app->singleton(NotificationService::class, function ($app) {
            return new NotificationService();
        });

        $this->app->bind(SwapService::class, function ($app) {
            return new SwapService($app->make(SwapRepository::class));
        });

        $this->app->bind(LeaveService::class, function ($app) {
            return new LeaveService();
        });

        $this->app->bind(ScheduleService::class, function ($app) {
            return new ScheduleService($app->make(ScheduleRepository::class));
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
