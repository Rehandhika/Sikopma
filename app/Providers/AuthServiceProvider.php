<?php

namespace App\Providers;

use App\Models\Attendance;
use App\Models\LeaveRequest;
use App\Models\Penalty;
use App\Models\Product;
use App\Models\Sale;
use App\Models\Schedule;
use App\Models\ScheduleChangeRequest;
use App\Models\SwapRequest;
use App\Models\User;
use App\Policies\AttendancePolicy;
use App\Policies\LeaveRequestPolicy;
use App\Policies\PenaltyPolicy;
use App\Policies\ProductPolicy;
use App\Policies\SalePolicy;
use App\Policies\ScheduleChangeRequestPolicy;
use App\Policies\SchedulePolicy;
use App\Policies\SwapRequestPolicy;
use App\Policies\UserPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        User::class => UserPolicy::class,
        Schedule::class => SchedulePolicy::class,
        SwapRequest::class => SwapRequestPolicy::class,
        ScheduleChangeRequest::class => ScheduleChangeRequestPolicy::class,
        LeaveRequest::class => LeaveRequestPolicy::class,
        Penalty::class => PenaltyPolicy::class,
        Product::class => ProductPolicy::class,
        Sale::class => SalePolicy::class,
        Attendance::class => AttendancePolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        // Super Admin bypass - runs before any other authorization checks
        // This ensures Super Admin has access to everything without explicit permission assignment
        Gate::before(function ($user, $ability) {
            $superAdminRole = config('roles.super_admin_role', 'Super Admin');

            if ($user->hasRole($superAdminRole)) {
                return true;
            }

            // Return null to let other policies/permissions handle the check
            return null;
        });
    }
}
