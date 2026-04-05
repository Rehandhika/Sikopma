<?php

namespace App\Livewire\Settings;

use App\Models\Setting;
use App\Services\ActivityLogService;
use Illuminate\Support\Facades\Cache;
use Livewire\Component;

class SystemSettings extends Component
{
    // Maintenance
    public $maintenance_mode;

    public $maintenance_message;

    public $maintenance_estimated_end;

    public function mount()
    {
        abort_unless(auth()->user()->can('kelola_pengaturan'), 403, 'Anda tidak memiliki akses ke halaman ini.');

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
        $newState = ! $this->maintenance_mode;
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

    public function save()
    {
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
        $this->dispatch('toast', message: 'Cache berhasil dibersihkan', type: 'success');
    }

    public function render()
    {
        $systemInfo = [
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
            'server_time' => now()->format('Y-m-d H:i:s'),
            'real_time' => now()->setTimezone('Asia/Jakarta')->format('Y-m-d H:i:s'),
            'timezone' => 'Asia/Jakarta',
        ];

        return view('livewire.settings.system-settings', [
            'systemInfo' => $systemInfo,
        ])->layout('layouts.app')->title('Pengaturan Sistem');
    }
}
