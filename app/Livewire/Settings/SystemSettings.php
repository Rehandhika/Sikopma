<?php

namespace App\Livewire\Settings;

use Livewire\Component;
use App\Models\Setting;
use Illuminate\Support\Facades\Cache;

class SystemSettings extends Component
{
    public $maintenance_mode;
    public $debug_mode;
    public $max_upload_size;
    public $session_lifetime;
    public $backup_enabled;
    public $backup_frequency;
    public $email_notifications;
    public $sms_notifications;

    public function mount()
    {
        $this->maintenance_mode = Setting::get('maintenance_mode', false);
        $this->debug_mode = Setting::get('debug_mode', false);
        $this->max_upload_size = Setting::get('max_upload_size', 10240);
        $this->session_lifetime = Setting::get('session_lifetime', 120);
        $this->backup_enabled = Setting::get('backup_enabled', true);
        $this->backup_frequency = Setting::get('backup_frequency', 'daily');
        $this->email_notifications = Setting::get('email_notifications', true);
        $this->sms_notifications = Setting::get('sms_notifications', false);
    }

    public function save()
    {
        $this->validate([
            'max_upload_size' => 'required|integer|min:1024|max:51200',
            'session_lifetime' => 'required|integer|min:15|max:1440',
            'backup_frequency' => 'required|in:hourly,daily,weekly,monthly',
        ]);

        Setting::set('maintenance_mode', $this->maintenance_mode);
        Setting::set('debug_mode', $this->debug_mode);
        Setting::set('max_upload_size', $this->max_upload_size);
        Setting::set('session_lifetime', $this->session_lifetime);
        Setting::set('backup_enabled', $this->backup_enabled);
        Setting::set('backup_frequency', $this->backup_frequency);
        Setting::set('email_notifications', $this->email_notifications);
        Setting::set('sms_notifications', $this->sms_notifications);

        // Clear cache after settings update
        Cache::flush();

        $this->dispatch('alert', type: 'success', message: 'Pengaturan sistem berhasil disimpan');
    }

    public function clearCache()
    {
        Cache::flush();
        $this->dispatch('alert', type: 'success', message: 'Cache berhasil dibersihkan');
    }

    public function render()
    {
        $systemInfo = [
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
            'database_version' => \DB::connection()->getPdo()->getAttribute(\PDO::ATTR_SERVER_VERSION),
            'server_os' => PHP_OS,
            'server_time' => now()->format('Y-m-d H:i:s'),
            'timezone' => config('app.timezone'),
            'cache_driver' => config('cache.default'),
            'session_driver' => config('session.driver'),
            'queue_driver' => config('queue.default'),
        ];

        return view('livewire.settings.system-settings', [
            'systemInfo' => $systemInfo,
        ])->layout('layouts.app')->title('Pengaturan Sistem');
    }
}
