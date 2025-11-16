<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SanitizeInput
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        // Get all input data
        $input = $request->all();
        
        // Sanitize string inputs
        $sanitized = $this->sanitizeArray($input);
        
        // Replace request input with sanitized data
        $request->merge($sanitized);

        return $next($request);
    }

    /**
     * Recursively sanitize array values
     */
    private function sanitizeArray(array $array): array
    {
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $array[$key] = $this->sanitizeArray($value);
            } elseif (is_string($value)) {
                $array[$key] = $this->sanitizeString($value);
            }
        }

        return $array;
    }

    /**
     * Sanitize string value
     */
    private function sanitizeString(string $value): string
    {
        // Remove potential XSS threats
        $value = strip_tags($value);
        
        // Remove control characters
        $value = preg_replace('/[\x00-\x1F\x7F]/', '', $value);
        
        // Normalize whitespace
        $value = preg_replace('/\s+/', ' ', $value);
        
        // Trim
        $value = trim($value);
        
        return $value;
    }
}
