<?php

namespace App\Http\Middleware;

use App\Services\MenuAccessService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class CheckMenuAccess
{
    public function __construct(
        private MenuAccessService $menuAccessService
    ) {}

    /**
     * Handle an incoming request.
     * Check if user has permission to access the menu/route.
     */
    public function handle(Request $request, Closure $next, string $menuKey): Response
    {
        // Check if user can access the menu
        if (! $this->menuAccessService->canAccess($menuKey)) {
            // Log unauthorized access attempt for security auditing
            Log::warning('Unauthorized menu access attempt', [
                'event' => 'unauthorized_menu_access',
                'user_id' => auth()->id(),
                'user_email' => auth()->user()?->email,
                'menu_key' => $menuKey,
                'route' => $request->route()?->getName(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'timestamp' => now()->toIso8601String(),
            ]);

            // Return JSON response for API requests
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Anda tidak memiliki akses ke halaman ini.',
                    'error' => 'access_denied',
                ], 403);
            }

            // Redirect to access denied page for web requests
            return redirect()->route('admin.access-denied')
                ->with('error', 'Anda tidak memiliki akses ke halaman ini.')
                ->with('menu_key', $menuKey);
        }

        return $next($request);
    }
}
