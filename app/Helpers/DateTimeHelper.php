<?php

namespace App\Helpers;

use App\Services\DateTimeSettingsService;
use Carbon\Carbon;

class DateTimeHelper
{
    protected static ?DateTimeSettingsService $service = null;

    /**
     * Get the DateTimeSettingsService instance
     */
    protected static function service(): DateTimeSettingsService
    {
        if (static::$service === null) {
            static::$service = app(DateTimeSettingsService::class);
        }

        return static::$service;
    }

    /**
     * Format date using system settings
     */
    public static function formatDate($date): string
    {
        return static::service()->formatDate($date);
    }

    /**
     * Format time using system settings
     */
    public static function formatTime($time): string
    {
        return static::service()->formatTime($time);
    }

    /**
     * Format datetime using system settings
     */
    public static function formatDateTime($datetime): string
    {
        return static::service()->formatDateTime($datetime);
    }

    /**
     * Format date in human readable format
     */
    public static function formatDateHuman($date): string
    {
        return static::service()->formatDateHuman($date);
    }

    /**
     * Format datetime in human readable format
     */
    public static function formatDateTimeHuman($datetime): string
    {
        return static::service()->formatDateTimeHuman($datetime);
    }

    /**
     * Get relative time (e.g., "2 jam yang lalu")
     */
    public static function diffForHumans($datetime): string
    {
        return static::service()->diffForHumans($datetime);
    }

    /**
     * Get current time in configured timezone
     */
    public static function now(): Carbon
    {
        return static::service()->now();
    }

    /**
     * Parse a date string with configured timezone
     */
    public static function parse(string $date): Carbon
    {
        return static::service()->parse($date);
    }

    /**
     * Get current timezone
     */
    public static function getTimezone(): string
    {
        return static::service()->getTimezone();
    }

    /**
     * Get current date format
     */
    public static function getDateFormat(): string
    {
        return static::service()->getDateFormat();
    }

    /**
     * Get current time format
     */
    public static function getTimeFormat(): string
    {
        return static::service()->getTimeFormat();
    }

    /**
     * Get current datetime format
     */
    public static function getDateTimeFormat(): string
    {
        return static::service()->getDateTimeFormat();
    }

    /**
     * Get current locale
     */
    public static function getLocale(): string
    {
        return static::service()->getLocale();
    }
}
