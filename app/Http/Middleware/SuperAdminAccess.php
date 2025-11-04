<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SuperAdminAccess
{
    /**
     * Handle an incoming request.
     * Super Admin can access everything without restriction
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        // Super Admin bypasses all role checks
        if (auth()->check() && auth()->user()->hasRole('super-admin')) {
            return $next($request);
        }

        // For other users, check if they have the required role
        if (auth()->check() && auth()->user()->hasAnyRole($roles)) {
            return $next($request);
        }

        // Unauthorized access
        abort(403, 'Unauthorized action.');
    }
}
