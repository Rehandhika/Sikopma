<?php

namespace App\Http\Middleware;

use App\Models\Setting;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;

class MaintenanceMiddleware
{
    /**
     * All authenticated users can bypass maintenance mode for admin area.
     * Only public pages (katalog) will show maintenance page.
     */

    /**
     * Routes that should always be accessible during maintenance.
     */
    protected array $excludedRoutes = ['login', 'admin.logout'];

    /**
     * Route prefixes that should always be accessible during maintenance.
     */
    protected array $excludedPrefixes = ['password', 'verification'];

    /**
     * URI patterns that should always be accessible during maintenance.
     */
    protected array $excludedUriPatterns = [
        'admin/masuk',      // Login page
        'admin/keluar',     // Logout
        'livewire/*',       // Livewire requests (needed for login form)
    ];

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Skip if route is excluded (login, logout)
        if ($this->isExcludedRoute($request)) {
            return $next($request);
        }

        // Skip if maintenance is not active
        if (! $this->isMaintenanceActive()) {
            return $next($request);
        }

        // All authenticated users can bypass maintenance (for admin area)
        if ($this->canBypass($request->user())) {
            // Share maintenance status with views for banner
            View::share('maintenanceActive', true);
            View::share('maintenanceData', $this->getMaintenanceData());

            return $next($request);
        }

        // Return appropriate response based on request type
        return $this->maintenanceResponse($request);
    }

    /**
     * Check if maintenance mode is active.
     */
    protected function isMaintenanceActive(): bool
    {
        // Short cache (5 seconds) for faster response to changes
        return Cache::remember('maintenance_mode', 5, function () {
            return (bool) Setting::get('maintenance_mode', false);
        });
    }

    /**
     * Check if user can bypass maintenance mode.
     * All authenticated users can bypass (access admin area during maintenance).
     */
    protected function canBypass(?User $user): bool
    {
        // Any logged-in user can bypass maintenance
        return $user !== null;
    }

    /**
     * Check if the current route is excluded from maintenance.
     */
    protected function isExcludedRoute(Request $request): bool
    {
        // Check URI patterns first (for routes without names or livewire)
        $uri = $request->path();
        foreach ($this->excludedUriPatterns as $pattern) {
            if ($request->is($pattern)) {
                return true;
            }
        }

        $routeName = $request->route()?->getName();

        if (! $routeName) {
            return false;
        }

        // Check exact route names
        if (in_array($routeName, $this->excludedRoutes)) {
            return true;
        }

        // Check route prefixes (for password reset, email verification, etc.)
        foreach ($this->excludedPrefixes as $prefix) {
            if (str_starts_with($routeName, $prefix)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Return appropriate maintenance response based on request type.
     */
    protected function maintenanceResponse(Request $request): Response
    {
        $data = $this->getMaintenanceData();

        // Return JSON for API requests or AJAX requests expecting JSON
        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'success' => false,
                'message' => $data['message'],
                'maintenance' => true,
                'estimated_end' => $data['estimated_end'],
            ], 503);
        }

        // Return HTML maintenance page for web requests
        return response()->view('maintenance', $data, 503);
    }

    /**
     * Get maintenance page data.
     */
    protected function getMaintenanceData(): array
    {
        return [
            'message' => Setting::get('maintenance_message', 'Sistem sedang dalam pemeliharaan. Silakan coba beberapa saat lagi.'),
            'estimated_end' => Setting::get('maintenance_estimated_end'),
            'started_at' => Setting::get('maintenance_started_at'),
        ];
    }
}
