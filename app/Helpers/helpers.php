<?php

use App\Services\DateTimeSettingsService;
use Carbon\Carbon;

if (! function_exists('format_currency')) {
    /**
     * Format number as Indonesian Rupiah currency
     *
     * @param  float|int|null  $amount
     * @param  bool  $withSymbol  Include "Rp" symbol
     * @param  int  $decimals  Number of decimal places
     */
    function format_currency($amount, bool $withSymbol = true, int $decimals = 0): string
    {
        if ($amount === null) {
            $amount = 0;
        }

        $formatted = number_format((float) $amount, $decimals, ',', '.');

        return $withSymbol ? 'Rp '.$formatted : $formatted;
    }
}

if (! function_exists('format_rupiah')) {
    /**
     * Alias for format_currency
     */
    function format_rupiah($amount, bool $withSymbol = true, int $decimals = 0): string
    {
        return format_currency($amount, $withSymbol, $decimals);
    }
}

if (! function_exists('format_date')) {
    /**
     * Format date using system settings
     */
    function format_date($date): string
    {
        return app(DateTimeSettingsService::class)->formatDate($date);
    }
}

if (! function_exists('format_time')) {
    /**
     * Format time using system settings
     */
    function format_time($time): string
    {
        return app(DateTimeSettingsService::class)->formatTime($time);
    }
}

if (! function_exists('format_datetime')) {
    /**
     * Format datetime using system settings
     */
    function format_datetime($datetime): string
    {
        return app(DateTimeSettingsService::class)->formatDateTime($datetime);
    }
}

if (! function_exists('format_date_human')) {
    /**
     * Format date in human readable format
     */
    function format_date_human($date): string
    {
        return app(DateTimeSettingsService::class)->formatDateHuman($date);
    }
}

if (! function_exists('format_datetime_human')) {
    /**
     * Format datetime in human readable format
     */
    function format_datetime_human($datetime): string
    {
        return app(DateTimeSettingsService::class)->formatDateTimeHuman($datetime);
    }
}

if (! function_exists('diff_for_humans')) {
    /**
     * Get relative time (e.g., "2 jam yang lalu")
     */
    function diff_for_humans($datetime): string
    {
        return app(DateTimeSettingsService::class)->diffForHumans($datetime);
    }
}

if (! function_exists('system_now')) {
    /**
     * Get current time in system timezone
     * Returns custom time if custom datetime is enabled
     */
    function system_now(): Carbon
    {
        return app(DateTimeSettingsService::class)->now();
    }
}

if (! function_exists('real_now')) {
    /**
     * Get real current time (ignores custom datetime setting)
     */
    function real_now(): Carbon
    {
        return app(DateTimeSettingsService::class)->realNow();
    }
}

if (! function_exists('is_custom_datetime_enabled')) {
    /**
     * Check if custom datetime mode is enabled
     */
    function is_custom_datetime_enabled(): bool
    {
        return app(DateTimeSettingsService::class)->isCustomDateTimeEnabled();
    }
}

if (! function_exists('system_timezone')) {
    /**
     * Get current system timezone
     */
    function system_timezone(): string
    {
        return app(DateTimeSettingsService::class)->getTimezone();
    }
}

if (! function_exists('system_locale')) {
    /**
     * Get current system locale
     */
    function system_locale(): string
    {
        return app(DateTimeSettingsService::class)->getLocale();
    }
}

if (! function_exists('parse_date')) {
    /**
     * Parse a date string with system timezone
     */
    function parse_date(string $date): Carbon
    {
        return app(DateTimeSettingsService::class)->parse($date);
    }
}

if (! function_exists('log_audit')) {
    /**
     * Log an audit entry
     *
     * @param  string  $action  The action being performed
     * @param  \Illuminate\Database\Eloquent\Model  $model  The model being audited
     * @param  array|null  $oldValues  Previous values (for updates)
     * @param  array|null  $newValues  New values (for updates)
     */
    function log_audit(string $action, \Illuminate\Database\Eloquent\Model $model, ?array $oldValues = null, ?array $newValues = null): ?\App\Models\AuditLog
    {
        return \App\Models\AuditLog::log($action, $model, $oldValues, $newValues);
    }
}
