<?php

namespace App\Livewire\Settings;

use Livewire\Component;
use App\Models\Setting;
use App\Services\DateTimeSettingsService;
use Illuminate\Support\Facades\Cache;

class SystemSettings extends Component
{
    // DateTime Settings
    public $timezone;
    public $date_format;
    public $time_format;
    public $datetime_format;
    public $use_24_hour;
    public $first_day_of_week;
    public $locale;

    // Custom DateTime for Audit/Development
    public $use_custom_datetime = false;
    public $custom_date;
    public $custom_time;

    // Security Settings
    public $session_lifetime;
    public $max_login_attempts;
    public $email_verification;
    public $two_factor_auth;

    // Notification Settings
    public $email_notifications;
    public $browser_notifications;
    public $sms_notifications;

    // Maintenance Settings
    public $maintenance_mode;
    public $auto_backup;
    public $backup_frequency;

    protected DateTimeSettingsService $dateTimeService;

    public function boot(DateTimeSettingsService $dateTimeService)
    {
        $this->dateTimeService = $dateTimeService;
    }

    public function mount()
    {
        // Check authorization
        if (!auth()->user()->hasAnyRole(['Super Admin', 'Ketua', 'Wakil Ketua'])) {
            abort(403, 'Anda tidak memiliki akses ke halaman ini.');
        }

        // Load DateTime Settings
        $dtSettings = $this->dateTimeService->getAll();
        $this->timezone = $dtSettings['timezone'];
        $this->date_format = $dtSettings['date_format'];
        $this->time_format = $dtSettings['time_format'];
        $this->datetime_format = $dtSettings['datetime_format'];
        $this->use_24_hour = (bool) $dtSettings['use_24_hour'];
        $this->first_day_of_week = (int) $dtSettings['first_day_of_week'];
        $this->locale = $dtSettings['locale'];

        // Load Custom DateTime Settings
        $customDt = $this->dateTimeService->getCustomDateTimeSettings();
        $this->use_custom_datetime = $customDt['enabled'];
        $this->custom_date = $customDt['date'];
        $this->custom_time = $customDt['time'] ?? '00:00';

        // Load Security Settings
        $this->session_lifetime = Setting::get('session_lifetime', 120);
        $this->max_login_attempts = Setting::get('max_login_attempts', 5);
        $this->email_verification = (bool) Setting::get('email_verification', false);
        $this->two_factor_auth = (bool) Setting::get('two_factor_auth', false);

        // Load Notification Settings
        $this->email_notifications = (bool) Setting::get('email_notifications', true);
        $this->browser_notifications = (bool) Setting::get('browser_notifications', true);
        $this->sms_notifications = (bool) Setting::get('sms_notifications', false);

        // Load Maintenance Settings
        $this->maintenance_mode = (bool) Setting::get('maintenance_mode', false);
        $this->auto_backup = (bool) Setting::get('auto_backup', true);
        $this->backup_frequency = Setting::get('backup_frequency', 'daily');
    }

    public function updatedUseCustomDatetime($value)
    {
        if (!$value) {
            // Reset to real time when disabled
            $this->custom_date = null;
            $this->custom_time = '00:00';
        } else {
            // Set default to current date/time when enabled
            if (!$this->custom_date) {
                $this->custom_date = $this->dateTimeService->realNow()->format('Y-m-d');
            }
            if (!$this->custom_time || $this->custom_time === '00:00') {
                $this->custom_time = $this->dateTimeService->realNow()->format('H:i');
            }
        }
    }

    public function resetToRealTime()
    {
        $this->use_custom_datetime = false;
        $this->custom_date = null;
        $this->custom_time = '00:00';
        
        $this->dateTimeService->disableCustomDateTime();
        Cache::flush();
        
        $this->dispatch('alert', type: 'success', message: 'Waktu sistem dikembalikan ke waktu nyata');
    }

    public function save()
    {
        $rules = [
            'timezone' => 'required|string|timezone',
            'date_format' => 'required|string|max:20',
            'time_format' => 'required|string|max:20',
            'datetime_format' => 'required|string|max:40',
            'first_day_of_week' => 'required|integer|in:0,1,6',
            'locale' => 'required|string|in:id,en',
            'session_lifetime' => 'required|integer|min:15|max:1440',
            'max_login_attempts' => 'required|integer|min:3|max:10',
            'backup_frequency' => 'required|in:daily,weekly,monthly',
        ];

        // Add validation for custom datetime if enabled
        if ($this->use_custom_datetime) {
            $rules['custom_date'] = 'required|date_format:Y-m-d';
            $rules['custom_time'] = 'required|date_format:H:i';
        }

        $this->validate($rules);

        // Save DateTime Settings
        $this->dateTimeService->saveAll([
            'timezone' => $this->timezone,
            'date_format' => $this->date_format,
            'time_format' => $this->time_format,
            'datetime_format' => $this->datetime_format,
            'use_24_hour' => $this->use_24_hour,
            'first_day_of_week' => $this->first_day_of_week,
            'locale' => $this->locale,
            'use_custom_datetime' => $this->use_custom_datetime,
            'custom_date' => $this->use_custom_datetime ? $this->custom_date : null,
            'custom_time' => $this->use_custom_datetime ? $this->custom_time : null,
        ]);

        // Save Security Settings
        Setting::set('session_lifetime', $this->session_lifetime);
        Setting::set('max_login_attempts', $this->max_login_attempts);
        Setting::set('email_verification', $this->email_verification ? '1' : '0');
        Setting::set('two_factor_auth', $this->two_factor_auth ? '1' : '0');

        // Save Notification Settings
        Setting::set('email_notifications', $this->email_notifications ? '1' : '0');
        Setting::set('browser_notifications', $this->browser_notifications ? '1' : '0');
        Setting::set('sms_notifications', $this->sms_notifications ? '1' : '0');

        // Save Maintenance Settings
        Setting::set('maintenance_mode', $this->maintenance_mode ? '1' : '0');
        Setting::set('auto_backup', $this->auto_backup ? '1' : '0');
        Setting::set('backup_frequency', $this->backup_frequency);

        // Clear all cache
        Cache::flush();

        $this->dispatch('alert', type: 'success', message: 'Pengaturan sistem berhasil disimpan');
    }

    public function clearCache()
    {
        Cache::flush();
        $this->dateTimeService->clearAllCache();
        $this->dispatch('alert', type: 'success', message: 'Cache berhasil dibersihkan');
    }

    public function getTimezoneOptionsProperty(): array
    {
        return $this->dateTimeService->getTimezoneOptions();
    }

    public function getDateFormatOptionsProperty(): array
    {
        return $this->dateTimeService->getDateFormatOptions();
    }

    public function getTimeFormatOptionsProperty(): array
    {
        return $this->dateTimeService->getTimeFormatOptions();
    }

    public function getDateTimeFormatOptionsProperty(): array
    {
        return $this->dateTimeService->getDateTimeFormatOptions();
    }

    public function getFirstDayOfWeekOptionsProperty(): array
    {
        return $this->dateTimeService->getFirstDayOfWeekOptions();
    }

    public function getLocaleOptionsProperty(): array
    {
        return $this->dateTimeService->getLocaleOptions();
    }

    public function render()
    {
        $systemInfo = [
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
            'database_version' => \DB::connection()->getPdo()->getAttribute(\PDO::ATTR_SERVER_VERSION),
            'server_os' => PHP_OS,
            'server_time' => $this->dateTimeService->now()->format('Y-m-d H:i:s'),
            'real_time' => $this->dateTimeService->realNow()->format('Y-m-d H:i:s'),
            'timezone' => $this->dateTimeService->getTimezone(),
            'cache_driver' => config('cache.default'),
            'session_driver' => config('session.driver'),
            'queue_driver' => config('queue.default'),
            'custom_datetime_active' => $this->dateTimeService->isCustomDateTimeEnabled(),
        ];

        return view('livewire.settings.system-settings', [
            'systemInfo' => $systemInfo,
            'timezoneOptions' => $this->timezoneOptions,
            'dateFormatOptions' => $this->dateFormatOptions,
            'timeFormatOptions' => $this->timeFormatOptions,
            'datetimeFormatOptions' => $this->dateTimeFormatOptions,
            'firstDayOfWeekOptions' => $this->firstDayOfWeekOptions,
            'localeOptions' => $this->localeOptions,
        ])->layout('layouts.app')->title('Pengaturan Sistem');
    }
}
