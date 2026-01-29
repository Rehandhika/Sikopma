<?php

namespace App\Providers;

use App\Models\LeaveRequest;
use App\Models\Penalty;
use App\Models\Schedule;
use App\Models\ScheduleChangeRequest;
use App\Models\SwapRequest;
use App\Policies\LeaveRequestPolicy;
use App\Policies\PenaltyPolicy;
use App\Policies\ScheduleChangeRequestPolicy;
use App\Policies\SchedulePolicy;
use App\Policies\SwapRequestPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Schedule::class => SchedulePolicy::class,
        SwapRequest::class => SwapRequestPolicy::class,
        ScheduleChangeRequest::class => ScheduleChangeRequestPolicy::class,
        LeaveRequest::class => LeaveRequestPolicy::class,
        Penalty::class => PenaltyPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();
    }
}
