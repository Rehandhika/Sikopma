<?php

/**
 * Global Helper Functions for SIKOPMA
 * 
 * This file contains utility functions that can be used throughout the application.
 */

if (!function_exists('format_currency')) {
    /**
     * Format number as Indonesian Rupiah currency
     *
     * @param float|int $amount
     * @param bool $showSymbol
     * @return string
     */
    function format_currency($amount, bool $showSymbol = true): string
    {
        $formatted = number_format($amount, 0, ',', '.');
        return $showSymbol ? 'Rp ' . $formatted : $formatted;
    }
}

if (!function_exists('format_date')) {
    /**
     * Format date to Indonesian format
     *
     * @param mixed $date
     * @param string $format
     * @return string
     */
    function format_date($date, string $format = 'd/m/Y'): string
    {
        if (!$date) {
            return '-';
        }
        
        if (is_string($date)) {
            $date = \Carbon\Carbon::parse($date);
        }
        
        return $date->format($format);
    }
}

if (!function_exists('format_datetime')) {
    /**
     * Format datetime to Indonesian format
     *
     * @param mixed $datetime
     * @return string
     */
    function format_datetime($datetime): string
    {
        return format_date($datetime, 'd/m/Y H:i');
    }
}

if (!function_exists('format_time')) {
    /**
     * Format time
     *
     * @param mixed $time
     * @return string
     */
    function format_time($time): string
    {
        if (!$time) {
            return '-';
        }
        
        if (is_string($time)) {
            $time = \Carbon\Carbon::parse($time);
        }
        
        return $time->format('H:i');
    }
}

if (!function_exists('get_session_label')) {
    /**
     * Get session time label
     *
     * @param int $session
     * @return string
     */
    function get_session_label(int $session): string
    {
        $sessions = config('sikopma.sessions');
        return $sessions[$session] ?? '-';
    }
}

if (!function_exists('calculate_late_minutes')) {
    /**
     * Calculate minutes late based on check-in time and session
     *
     * @param \Carbon\Carbon $checkIn
     * @param int $session
     * @return int
     */
    function calculate_late_minutes(\Carbon\Carbon $checkIn, int $session): int
    {
        $sessionTimes = config('sikopma.session_times');
        
        if (!isset($sessionTimes[$session])) {
            return 0;
        }
        
        $startTime = \Carbon\Carbon::parse($sessionTimes[$session]['start']);
        $lateThreshold = config('sikopma.late_threshold_minutes', 15);
        
        if ($checkIn->greaterThan($startTime->addMinutes($lateThreshold))) {
            return $checkIn->diffInMinutes($startTime);
        }
        
        return 0;
    }
}

if (!function_exists('is_within_geofence')) {
    /**
     * Check if coordinates are within allowed geofence
     *
     * @param float $latitude
     * @param float $longitude
     * @return bool
     */
    function is_within_geofence(float $latitude, float $longitude): bool
    {
        $allowedLat = config('sikopma.geofence.latitude');
        $allowedLng = config('sikopma.geofence.longitude');
        $radius = config('sikopma.geofence.radius_meters', 100);
        
        // Haversine formula to calculate distance
        $earthRadius = 6371000; // meters
        
        $latFrom = deg2rad($allowedLat);
        $lonFrom = deg2rad($allowedLng);
        $latTo = deg2rad($latitude);
        $lonTo = deg2rad($longitude);
        
        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;
        
        $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) +
            cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));
        
        $distance = $angle * $earthRadius;
        
        return $distance <= $radius;
    }
}

if (!function_exists('get_status_badge_class')) {
    /**
     * Get Tailwind CSS badge class for status
     *
     * @param string $status
     * @return string
     */
    function get_status_badge_class(string $status): string
    {
        return match (strtolower($status)) {
            'active', 'present', 'approved', 'paid', 'completed' => 'bg-green-100 text-green-800',
            'pending', 'partial' => 'bg-yellow-100 text-yellow-800',
            'late' => 'bg-orange-100 text-orange-800',
            'rejected', 'cancelled', 'failed', 'inactive' => 'bg-red-100 text-red-800',
            'absent' => 'bg-gray-100 text-gray-800',
            default => 'bg-blue-100 text-blue-800',
        };
    }
}

if (!function_exists('generate_invoice_number')) {
    /**
     * Generate invoice number with prefix
     *
     * @param string $prefix
     * @param string $modelClass
     * @return string
     */
    function generate_invoice_number(string $prefix, string $modelClass): string
    {
        $date = now()->format('Ymd');
        $lastRecord = $modelClass::whereDate('created_at', today())->latest('id')->first();
        $sequence = $lastRecord ? (int) substr($lastRecord->invoice_number, -4) + 1 : 1;
        
        return $prefix . '-' . $date . '-' . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }
}

if (!function_exists('log_audit')) {
    /**
     * Create audit log entry
     *
     * @param string $action
     * @param \Illuminate\Database\Eloquent\Model $model
     * @param array|null $oldValues
     * @param array|null $newValues
     * @return \App\Models\AuditLog
     */
    function log_audit(string $action, \Illuminate\Database\Eloquent\Model $model, ?array $oldValues = null, ?array $newValues = null): \App\Models\AuditLog
    {
        return \App\Models\AuditLog::log($action, $model, $oldValues, $newValues);
    }
}

if (!function_exists('notify_user')) {
    /**
     * Create notification for user
     *
     * @param int $userId
     * @param string $title
     * @param string $message
     * @param string $type
     * @return \App\Models\Notification
     */
    function notify_user(int $userId, string $title, string $message, string $type = 'info'): \App\Models\Notification
    {
        return \App\Models\Notification::create([
            'user_id' => $userId,
            'title' => $title,
            'message' => $message,
            'type' => $type,
        ]);
    }
}

if (!function_exists('get_user_role_names')) {
    /**
     * Get user's role names as comma-separated string
     *
     * @param \App\Models\User $user
     * @return string
     */
    function get_user_role_names(\App\Models\User $user): string
    {
        return $user->roles->pluck('name')->join(', ');
    }
}

if (!function_exists('can_access_admin_features')) {
    /**
     * Check if user can access admin features
     *
     * @param \App\Models\User|null $user
     * @return bool
     */
    function can_access_admin_features(?\App\Models\User $user = null): bool
    {
        $user = $user ?? auth()->user();
        
        if (!$user) {
            return false;
        }
        
        return $user->hasAnyRole(['Super Admin', 'Ketua', 'Wakil Ketua', 'BPH']);
    }
}
