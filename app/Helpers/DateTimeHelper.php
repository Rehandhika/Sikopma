<?php

namespace App\Helpers;

use Carbon\Carbon;

class DateTimeHelper
{
    /**
     * Format date
     * Format: 13 Februari 2026
     */
    public static function formatDate($date): string
    {
        return static::parse($date)->translatedFormat('d F Y');
    }

    /**
     * Format time
     * Format: 13:45
     */
    public static function formatTime($time): string
    {
        return static::parse($time)->format('H:i');
    }

    /**
     * Format datetime
     * Format: 13 Februari 2026 13:45
     */
    public static function formatDateTime($datetime): string
    {
        return static::parse($datetime)->translatedFormat('d F Y H:i');
    }

    /**
     * Format date in human readable format
     * Same as formatDate for now, but can be customized
     */
    public static function formatDateHuman($date): string
    {
        return static::parse($date)->translatedFormat('l, d F Y');
    }

    /**
     * Format datetime in human readable format
     */
    public static function formatDateTimeHuman($datetime): string
    {
        return static::parse($datetime)->translatedFormat('l, d F Y H:i');
    }

    /**
     * Get relative time (e.g., "2 jam yang lalu")
     */
    public static function diffForHumans($datetime): string
    {
        return static::parse($datetime)->diffForHumans();
    }

    /**
     * Get current time in Jakarta timezone
     */
    public static function now(): Carbon
    {
        return Carbon::now('Asia/Jakarta');
    }

    /**
     * Parse a date string with Jakarta timezone
     */
    public static function parse($date): Carbon
    {
        if ($date instanceof Carbon) {
            return $date->setTimezone('Asia/Jakarta');
        }

        return Carbon::parse($date)->setTimezone('Asia/Jakarta');
    }

    /**
     * Get current timezone
     */
    public static function getTimezone(): string
    {
        return 'Asia/Jakarta';
    }

    /**
     * Get current date format (Standard)
     */
    public static function getDateFormat(): string
    {
        return 'd F Y';
    }

    /**
     * Get current time format (Standard)
     */
    public static function getTimeFormat(): string
    {
        return 'H:i';
    }

    /**
     * Get current datetime format (Standard)
     */
    public static function getDateTimeFormat(): string
    {
        return 'd F Y H:i';
    }

    /**
     * Get current locale
     */
    public static function getLocale(): string
    {
        return 'id';
    }
}
