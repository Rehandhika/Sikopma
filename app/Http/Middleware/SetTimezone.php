<?php

namespace App\Http\Middleware;

use App\Services\DateTimeSettingsService;
use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetTimezone
{
    protected DateTimeSettingsService $dateTimeService;

    public function __construct(DateTimeSettingsService $dateTimeService)
    {
        $this->dateTimeService = $dateTimeService;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Get timezone from system settings
        $timezone = $this->dateTimeService->getTimezone();

        // Set PHP default timezone
        date_default_timezone_set($timezone);

        // Set Carbon default timezone
        Carbon::setLocale($this->dateTimeService->getLocale());

        // Update config at runtime
        config(['app.timezone' => $timezone]);

        return $next($request);
    }
}
