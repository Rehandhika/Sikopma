<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

/**
 * Log Request Middleware
 * 
 * Logs all incoming requests for monitoring and debugging
 */
class LogRequestMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $startTime = microtime(true);

        // Process request
        $response = $next($request);

        // Calculate execution time
        $executionTime = round((microtime(true) - $startTime) * 1000, 2);

        // Log request details (only in non-production or for errors)
        if (app()->environment('local') || $response->getStatusCode() >= 400) {
            Log::channel('daily')->info('Request processed', [
                'method' => $request->method(),
                'url' => $request->fullUrl(),
                'ip' => $request->ip(),
                'user_id' => auth()->id(),
                'status' => $response->getStatusCode(),
                'execution_time_ms' => $executionTime,
            ]);
        }

        // Add execution time header
        $response->headers->set('X-Execution-Time', $executionTime . 'ms');

        return $response;
    }
}
