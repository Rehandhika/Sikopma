<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SanitizeInput
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        // Skip sanitization for login forms to prevent credential issues
        if ($request->route() && in_array($request->route()->getName(), ['login', 'logout'])) {
            return $next($request);
        }

        // Skip sanitization for Livewire requests (component payloads break when altered)
        if ($request->header('X-Livewire') || Str::startsWith($request->path(), 'livewire')) {
            return $next($request);
        }
        
        // Get all input data
        $input = $request->all();
        
        // Sanitize string inputs (but preserve passwords exactly)
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
                // Skip sanitizing password fields
                if (in_array($key, ['password', 'password_confirmation', 'current_password'])) {
                    continue;
                }
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
