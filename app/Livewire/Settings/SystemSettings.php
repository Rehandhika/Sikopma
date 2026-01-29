<?php

namespace App\Livewire\Settings;

use Livewire\Component;
use App\Models\Setting;
use App\Services\DateTimeSettingsService;
use App\Services\ActivityLogService;
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

    // Custom DateTime
    public $use_custom_datetime = false;
    public $custom_date;
    public $custom_time;

    // Maintenance
    public $maintenance_mode;
    public $maintenance_message;
    public $maintenance_estimated_end;

    protected DateTimeSettingsService $dateTimeService;

    public function boot(DateTimeSettingsService $dateTimeService)
    {
        $this->dateTimeService = $dateTimeService;
    }

    public function mount()
    {
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

        // Load Custom DateTime
        $customDt = $this->dateTimeService->getCustomDateTimeSettings();
        $this->use_custom_datetime = $customDt['enabled'];
        $this->custom_date = $customDt['date'];
        $this->custom_time = $customDt['time'] ?? '00:00';

        // Load Maintenance
        $this->maintenance_mode = (bool) Setting::get('maintenance_mode', false);
        $this->maintenance_message = Setting::get('maintenance_message', '');
        $this->maintenance_estimated_end = Setting::get('maintenance_estimated_end', '');
    }

    /**
     * Toggle maintenance mode
     */
    public function toggleMaintenance(): void
    {
        $newState = !$this->maintenance_mode;
        $this->maintenance_mode = $newState;
        
        // Save to settings
        Setting::set('maintenance_mode', $newState ? '1' : '0');
        
        if ($newState) {
            // Activating maintenance
            Setting::set('maintenance_message', $this->maintenance_message ?? '');
            Setting::set('maintenance_estimated_end', $this->maintenance_estimated_end ?? '');
            Setting::set('maintenance_started_at', now()->toDateTimeString());
            Setting::set('maintenance_started_by', (string) auth()->id());
            
            // Log activation
            \Log::info('Maintenance mode activated', [
                'user_id' => auth()->id(),
                'user_name' => auth()->user()->name,
                'message' => $this->maintenance_message,
                'estimated_end' => $this->maintenance_estimated_end,
            ]);
        } else {
            // Deactivating maintenance - clear metadata
            $startedAt = Setting::get('maintenance_started_at');
            Setting::set('maintenance_started_at', '');
            Setting::set('maintenance_started_by', '');
            
            // Log deactivation
            \Log::info('Maintenance mode deactivated', [
                'user_id' => auth()->id(),
                'user_name' => auth()->user()->name,
                'was_started_at' => $startedAt,
            ]);
        }
        
        // Clear cache immediately
        Cache::forget('maintenance_mode');
        
        // Log activity
        ActivityLogService::logMaintenanceModeChanged($newState);
        
        $message = $newState ? 'Mode maintenance diaktifkan' : 'Mode maintenance dinonaktifkan';
        $this->dispatch('alert', type: $newState ? 'warning' : 'success', message: $message);
    }

    public function updatedUseCustomDatetime($value)
    {
        if (!$value) {
            $this->custom_date = null;
            $this->custom_time = '00:00';
        } else {
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
        
        $this->dispatch('toast', message: 'Waktu dikembalikan ke waktu nyata', type: 'success');
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
        ];

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

        // Save Maintenance
        Setting::set('maintenance_mode', $this->maintenance_mode ? '1' : '0');
        Setting::set('maintenance_message', $this->maintenance_message ?? '');
        Setting::set('maintenance_estimated_end', $this->maintenance_estimated_end ?? '');

        Cache::flush();

        // Log activity
        ActivityLogService::logSettingsUpdated('Sistem');

        $this->dispatch('toast', message: 'Pengaturan berhasil disimpan', type: 'success');
    }

    public function clearCache()
    {
        Cache::flush();
        $this->dateTimeService->clearAllCache();
        $this->dispatch('toast', message: 'Cache berhasil dibersihkan', type: 'success');
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
            'server_time' => $this->dateTimeService->now()->format('Y-m-d H:i:s'),
            'real_time' => $this->dateTimeService->realNow()->format('Y-m-d H:i:s'),
            'timezone' => $this->dateTimeService->getTimezone(),
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
