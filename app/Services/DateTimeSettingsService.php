<?php

namespace App\Services;

use App\Models\SystemSetting;
use Carbon\Carbon;
use DateTimeZone;
use Illuminate\Support\Facades\Cache;

class DateTimeSettingsService
{
    /**
     * Cache key prefix for datetime settings
     */
    protected const CACHE_PREFIX = 'datetime_settings';
    protected const CACHE_TTL = 3600; // 1 hour

    /**
     * Default settings
     */
    protected array $defaults = [
        'timezone' => 'Asia/Jakarta',
        'date_format' => 'd/m/Y',
        'time_format' => 'H:i',
        'datetime_format' => 'd/m/Y H:i',
        'use_24_hour' => true,
        'first_day_of_week' => 1, // Monday
        'locale' => 'id',
        // Custom datetime for audit/development
        'use_custom_datetime' => false,
        'custom_date' => null,
        'custom_time' => null,
    ];

    /**
     * Available timezone options for Indonesia
     */
    public function getTimezoneOptions(): array
    {
        return [
            'Asia/Jakarta' => 'WIB - Waktu Indonesia Barat (Jakarta, Bandung, Surabaya)',
            'Asia/Makassar' => 'WITA - Waktu Indonesia Tengah (Makassar, Bali, Balikpapan)',
            'Asia/Jayapura' => 'WIT - Waktu Indonesia Timur (Jayapura, Ambon, Manokwari)',
        ];
    }

    /**
     * Available date format options
     */
    public function getDateFormatOptions(): array
    {
        $now = Carbon::now();
        return [
            'd/m/Y' => 'd/m/Y - ' . $now->format('d/m/Y'),
            'd-m-Y' => 'd-m-Y - ' . $now->format('d-m-Y'),
            'd M Y' => 'd M Y - ' . $now->format('d M Y'),
            'd F Y' => 'd F Y - ' . $now->format('d F Y'),
            'Y-m-d' => 'Y-m-d - ' . $now->format('Y-m-d'),
            'm/d/Y' => 'm/d/Y - ' . $now->format('m/d/Y'),
        ];
    }

    /**
     * Available time format options
     */
    public function getTimeFormatOptions(): array
    {
        $now = Carbon::now();
        return [
            'H:i' => '24 jam - ' . $now->format('H:i'),
            'H:i:s' => '24 jam dengan detik - ' . $now->format('H:i:s'),
            'h:i A' => '12 jam - ' . $now->format('h:i A'),
            'h:i:s A' => '12 jam dengan detik - ' . $now->format('h:i:s A'),
        ];
    }

    /**
     * Available datetime format options
     */
    public function getDateTimeFormatOptions(): array
    {
        $now = Carbon::now();
        return [
            'd/m/Y H:i' => 'd/m/Y H:i - ' . $now->format('d/m/Y H:i'),
            'd-m-Y H:i' => 'd-m-Y H:i - ' . $now->format('d-m-Y H:i'),
            'd M Y H:i' => 'd M Y H:i - ' . $now->format('d M Y H:i'),
            'd F Y H:i' => 'd F Y H:i - ' . $now->format('d F Y H:i'),
            'Y-m-d H:i' => 'Y-m-d H:i - ' . $now->format('Y-m-d H:i'),
            'd/m/Y h:i A' => 'd/m/Y h:i A - ' . $now->format('d/m/Y h:i A'),
        ];
    }

    /**
     * Available first day of week options
     */
    public function getFirstDayOfWeekOptions(): array
    {
        return [
            0 => 'Minggu',
            1 => 'Senin',
            6 => 'Sabtu',
        ];
    }

    /**
     * Available locale options
     */
    public function getLocaleOptions(): array
    {
        return [
            'id' => 'Indonesia',
            'en' => 'English',
        ];
    }

    /**
     * Get a specific setting
     */
    public function get(string $key, $default = null)
    {
        $default = $default ?? ($this->defaults[$key] ?? null);
        
        return Cache::remember(
            self::CACHE_PREFIX . '.' . $key,
            self::CACHE_TTL,
            fn() => SystemSetting::get($key, $default)
        );
    }

    /**
     * Set a specific setting
     */
    public function set(string $key, $value): void
    {
        SystemSetting::set($key, $value, 'string');
        Cache::forget(self::CACHE_PREFIX . '.' . $key);
        $this->clearAllCache();
    }

    /**
     * Get all datetime settings
     */
    public function getAll(): array
    {
        return Cache::remember(
            self::CACHE_PREFIX . '.all',
            self::CACHE_TTL,
            function () {
                $settings = [];
                foreach ($this->defaults as $key => $default) {
                    $settings[$key] = SystemSetting::get($key, $default);
                }
                return $settings;
            }
        );
    }

    /**
     * Save all datetime settings
     */
    public function saveAll(array $settings): void
    {
        foreach ($settings as $key => $value) {
            if (array_key_exists($key, $this->defaults)) {
                SystemSetting::set($key, $value, $this->getSettingType($key));
            }
        }
        $this->clearAllCache();
    }

    /**
     * Get setting type for proper casting
     */
    protected function getSettingType(string $key): string
    {
        return match($key) {
            'use_24_hour' => 'boolean',
            'first_day_of_week' => 'integer',
            default => 'string',
        };
    }

    /**
     * Clear all datetime settings cache
     */
    public function clearAllCache(): void
    {
        Cache::forget(self::CACHE_PREFIX . '.all');
        foreach (array_keys($this->defaults) as $key) {
            Cache::forget(self::CACHE_PREFIX . '.' . $key);
        }
    }

    /**
     * Get current timezone
     */
    public function getTimezone(): string
    {
        return $this->get('timezone', 'Asia/Jakarta');
    }

    /**
     * Get current date format
     */
    public function getDateFormat(): string
    {
        return $this->get('date_format', 'd/m/Y');
    }

    /**
     * Get current time format
     */
    public function getTimeFormat(): string
    {
        return $this->get('time_format', 'H:i');
    }

    /**
     * Get current datetime format
     */
    public function getDateTimeFormat(): string
    {
        return $this->get('datetime_format', 'd/m/Y H:i');
    }

    /**
     * Get current locale
     */
    public function getLocale(): string
    {
        return $this->get('locale', 'id');
    }

    /**
     * Format a date using current settings
     */
    public function formatDate($date): string
    {
        if (!$date) return '-';
        
        $carbon = $date instanceof Carbon ? $date : Carbon::parse($date);
        $carbon->setTimezone($this->getTimezone());
        
        if ($this->getLocale() === 'id') {
            $carbon->locale('id');
        }
        
        return $carbon->format($this->getDateFormat());
    }

    /**
     * Format a time using current settings
     */
    public function formatTime($time): string
    {
        if (!$time) return '-';
        
        $carbon = $time instanceof Carbon ? $time : Carbon::parse($time);
        $carbon->setTimezone($this->getTimezone());
        
        return $carbon->format($this->getTimeFormat());
    }

    /**
     * Format a datetime using current settings
     */
    public function formatDateTime($datetime): string
    {
        if (!$datetime) return '-';
        
        $carbon = $datetime instanceof Carbon ? $datetime : Carbon::parse($datetime);
        $carbon->setTimezone($this->getTimezone());
        
        if ($this->getLocale() === 'id') {
            $carbon->locale('id');
        }
        
        return $carbon->format($this->getDateTimeFormat());
    }

    /**
     * Format date in Indonesian human readable format
     */
    public function formatDateHuman($date): string
    {
        if (!$date) return '-';
        
        $carbon = $date instanceof Carbon ? $date : Carbon::parse($date);
        $carbon->setTimezone($this->getTimezone());
        $carbon->locale($this->getLocale());
        
        return $carbon->isoFormat('dddd, D MMMM YYYY');
    }

    /**
     * Format datetime in Indonesian human readable format
     */
    public function formatDateTimeHuman($datetime): string
    {
        if (!$datetime) return '-';
        
        $carbon = $datetime instanceof Carbon ? $datetime : Carbon::parse($datetime);
        $carbon->setTimezone($this->getTimezone());
        $carbon->locale($this->getLocale());
        
        return $carbon->isoFormat('dddd, D MMMM YYYY HH:mm');
    }

    /**
     * Get current time in configured timezone
     * If custom datetime is enabled, returns the custom time
     */
    public function now(): Carbon
    {
        if ($this->isCustomDateTimeEnabled()) {
            return $this->getCustomDateTime();
        }
        
        return Carbon::now($this->getTimezone());
    }

    /**
     * Check if custom datetime is enabled
     */
    public function isCustomDateTimeEnabled(): bool
    {
        return (bool) $this->get('use_custom_datetime', false);
    }

    /**
     * Get custom datetime as Carbon instance
     */
    public function getCustomDateTime(): Carbon
    {
        $customDate = $this->get('custom_date');
        $customTime = $this->get('custom_time', '00:00');
        
        if (!$customDate) {
            return Carbon::now($this->getTimezone());
        }
        
        $dateTimeString = $customDate . ' ' . $customTime;
        
        return Carbon::createFromFormat('Y-m-d H:i', $dateTimeString, $this->getTimezone());
    }

    /**
     * Get real current time (ignores custom datetime setting)
     */
    public function realNow(): Carbon
    {
        return Carbon::now($this->getTimezone());
    }

    /**
     * Enable custom datetime mode
     */
    public function enableCustomDateTime(string $date, string $time): void
    {
        $this->set('use_custom_datetime', true);
        $this->set('custom_date', $date);
        $this->set('custom_time', $time);
    }

    /**
     * Disable custom datetime mode (return to real time)
     */
    public function disableCustomDateTime(): void
    {
        $this->set('use_custom_datetime', false);
        $this->set('custom_date', '');
        $this->set('custom_time', '');
    }

    /**
     * Get custom datetime settings
     */
    public function getCustomDateTimeSettings(): array
    {
        return [
            'enabled' => $this->isCustomDateTimeEnabled(),
            'date' => $this->get('custom_date'),
            'time' => $this->get('custom_time', '00:00'),
        ];
    }

    /**
     * Parse a date string to Carbon with configured timezone
     */
    public function parse(string $date): Carbon
    {
        return Carbon::parse($date, $this->getTimezone());
    }

    /**
     * Get relative time (e.g., "2 jam yang lalu")
     */
    public function diffForHumans($datetime): string
    {
        if (!$datetime) return '-';
        
        $carbon = $datetime instanceof Carbon ? $datetime : Carbon::parse($datetime);
        $carbon->setTimezone($this->getTimezone());
        $carbon->locale($this->getLocale());
        
        return $carbon->diffForHumans();
    }

    /**
     * Get settings for frontend/API
     */
    public function getForFrontend(): array
    {
        $settings = $this->getAll();
        $customSettings = $this->getCustomDateTimeSettings();
        
        return [
            'timezone' => $settings['timezone'],
            'timezone_offset' => $this->getTimezoneOffset($settings['timezone']),
            'timezone_name' => $this->getTimezoneOptions()[$settings['timezone']] ?? $settings['timezone'],
            'date_format' => $settings['date_format'],
            'time_format' => $settings['time_format'],
            'datetime_format' => $settings['datetime_format'],
            'use_24_hour' => (bool) $settings['use_24_hour'],
            'first_day_of_week' => (int) $settings['first_day_of_week'],
            'locale' => $settings['locale'],
            'current_time' => $this->now()->format('Y-m-d\TH:i:sP'),
            'current_time_formatted' => $this->formatDateTime($this->now()),
            'real_time' => $this->realNow()->format('Y-m-d\TH:i:sP'),
            'real_time_formatted' => $this->formatDateTime($this->realNow()),
            'custom_datetime' => [
                'enabled' => $customSettings['enabled'],
                'date' => $customSettings['date'],
                'time' => $customSettings['time'],
            ],
        ];
    }

    /**
     * Get timezone offset in hours
     */
    protected function getTimezoneOffset(string $timezone): int
    {
        $tz = new DateTimeZone($timezone);
        $now = new \DateTime('now', $tz);
        return $tz->getOffset($now) / 3600;
    }
}
