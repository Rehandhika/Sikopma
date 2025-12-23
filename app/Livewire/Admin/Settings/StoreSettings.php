<?php

namespace App\Livewire\Admin\Settings;

use App\Services\StoreStatusService;
use Carbon\Carbon;
use Livewire\Component;

class StoreSettings extends Component
{
    public $currentStatus = [];
    public $statusLoaded = false;
    public $operatingHours = [];
    
    // Contact information properties
    public $contactPhone = '';
    public $contactEmail = '';
    public $contactWhatsapp = '';
    public $contactAddress = '';
    public $aboutText = '';

    protected StoreStatusService $storeStatusService;

    public function boot(StoreStatusService $storeStatusService)
    {
        $this->storeStatusService = $storeStatusService;
    }

    public function mount()
    {
        // Check authorization
        if (!auth()->user()->hasAnyRole(['Super Admin', 'Ketua', 'Wakil Ketua'])) {
            abort(403, 'Anda tidak memiliki akses ke halaman ini.');
        }

        $this->refreshStatus();
        $this->loadOperatingHours();
        $this->loadContactInfo();
    }

    public function loadOperatingHours()
    {
        $setting = \App\Models\StoreSetting::first();
        
        if ($setting && $setting->operating_hours) {
            $this->operatingHours = $setting->operating_hours;
        } else {
            // Default operating hours
            $this->operatingHours = [
                'monday' => ['open' => '07:30', 'close' => '16:00', 'is_open' => true],
                'tuesday' => ['open' => '07:30', 'close' => '16:00', 'is_open' => true],
                'wednesday' => ['open' => '07:30', 'close' => '16:00', 'is_open' => true],
                'thursday' => ['open' => '07:30', 'close' => '16:00', 'is_open' => true],
                'friday' => ['open' => null, 'close' => null, 'is_open' => false],
                'saturday' => ['open' => null, 'close' => null, 'is_open' => false],
                'sunday' => ['open' => null, 'close' => null, 'is_open' => false],
            ];
        }
    }

    public function refreshStatus()
    {
        $this->currentStatus = $this->storeStatusService->getStatus();
        $this->statusLoaded = true;
    }

    public function closeFor(int $hours)
    {
        $reason = "Tutup sementara selama {$hours} jam";
        $until = Carbon::now()->addHours($hours);
        
        $this->storeStatusService->manualClose($reason, $until);
        $this->refreshStatus();
        
        $this->dispatch('alert', type: 'success', message: "Koperasi ditutup sementara selama {$hours} jam");
    }

    public function closeUntilTomorrow()
    {
        $tomorrow = Carbon::tomorrow()->setTime(8, 0);
        $reason = "Tutup hingga besok pagi";
        
        $this->storeStatusService->manualClose($reason, $tomorrow);
        $this->refreshStatus();
        
        $this->dispatch('alert', type: 'success', message: 'Koperasi ditutup hingga besok pagi (07:30)');
    }

    public function enableOpenOverride()
    {
        $this->storeStatusService->manualOpenOverride(true);
        $this->refreshStatus();
        
        $this->dispatch('alert', type: 'success', message: 'Override buka diaktifkan - koperasi dapat buka di luar jadwal jika ada pengurus');
    }

    public function disableOpenOverride()
    {
        $this->storeStatusService->manualOpenOverride(false);
        $this->refreshStatus();
        
        $this->dispatch('alert', type: 'success', message: 'Override buka dinonaktifkan - kembali ke jadwal normal');
    }

    public function enableManualMode()
    {
        $reason = 'Mode manual diaktifkan oleh admin';
        $this->storeStatusService->toggleManualMode(false, $reason);
        $this->refreshStatus();
        
        $this->dispatch('alert', type: 'info', message: 'Mode manual diaktifkan - Anda memiliki kontrol penuh terhadap status');
    }

    public function setManualStatus(bool $isOpen)
    {
        $reason = $isOpen ? 'Dibuka manual oleh admin' : 'Ditutup manual oleh admin';
        $this->storeStatusService->toggleManualMode($isOpen, $reason);
        $this->refreshStatus();
        
        $statusText = $isOpen ? 'BUKA' : 'TUTUP';
        $this->dispatch('alert', type: 'success', message: "Status diubah menjadi {$statusText} (mode manual)");
    }

    public function disableManualMode()
    {
        $this->storeStatusService->backToAutoMode();
        $this->refreshStatus();
        
        $this->dispatch('alert', type: 'success', message: 'Mode manual dinonaktifkan - kembali ke mode otomatis');
    }

    public function resetToAuto()
    {
        $this->storeStatusService->backToAutoMode();
        $this->refreshStatus();
        
        $this->dispatch('alert', type: 'success', message: 'Semua pengaturan manual direset - kembali ke mode otomatis');
    }

    public function loadContactInfo()
    {
        $setting = \App\Models\StoreSetting::first();
        
        if ($setting) {
            $this->contactPhone = $setting->contact_phone ?? '';
            $this->contactEmail = $setting->contact_email ?? '';
            $this->contactWhatsapp = $setting->contact_whatsapp ?? '';
            $this->contactAddress = $setting->contact_address ?? '';
            $this->aboutText = $setting->about_text ?? '';
        }
    }

    public function saveOperatingHours()
    {
        // Validate operating hours
        $errors = [];
        
        foreach (['monday', 'tuesday', 'wednesday', 'thursday'] as $day) {
            if (!isset($this->operatingHours[$day])) {
                continue;
            }
            
            $dayData = $this->operatingHours[$day];
            
            // Skip validation if day is not open
            if (!($dayData['is_open'] ?? false)) {
                continue;
            }
            
            $open = $dayData['open'] ?? null;
            $close = $dayData['close'] ?? null;
            
            // Validate time format (HH:MM)
            if ($open && !preg_match('/^([01]?[0-9]|2[0-3]):[0-5][0-9]$/', $open)) {
                $errors[$day . '_open'] = 'Format waktu buka tidak valid (gunakan format HH:MM)';
            }
            
            if ($close && !preg_match('/^([01]?[0-9]|2[0-3]):[0-5][0-9]$/', $close)) {
                $errors[$day . '_close'] = 'Format waktu tutup tidak valid (gunakan format HH:MM)';
            }
            
            // Validate close time is after open time
            if ($open && $close) {
                $openTime = strtotime($open);
                $closeTime = strtotime($close);
                
                if ($closeTime <= $openTime) {
                    $errors[$day . '_time'] = 'Waktu tutup harus setelah waktu buka';
                }
            }
        }
        
        if (!empty($errors)) {
            foreach ($errors as $error) {
                $this->dispatch('alert', type: 'error', message: $error);
            }
            return;
        }
        
        // Save operating hours
        $setting = \App\Models\StoreSetting::first();
        
        if (!$setting) {
            $setting = \App\Models\StoreSetting::create([
                'operating_hours' => $this->operatingHours,
            ]);
        } else {
            $setting->update([
                'operating_hours' => $this->operatingHours,
            ]);
        }
        
        // Trigger status update to apply new operating hours
        $this->storeStatusService->forceUpdate();
        $this->refreshStatus();
        
        $this->dispatch('alert', type: 'success', message: 'Jam operasional berhasil disimpan');
    }

    public function saveContactInfo()
    {
        // Validate email format
        if (!empty($this->contactEmail) && !filter_var($this->contactEmail, FILTER_VALIDATE_EMAIL)) {
            $this->dispatch('alert', type: 'error', message: 'Format email tidak valid');
            return;
        }
        
        // Validate phone number format (Indonesian format)
        if (!empty($this->contactPhone)) {
            $phone = preg_replace('/[^0-9+]/', '', $this->contactPhone);
            if (!preg_match('/^(\+62|62|0)[0-9]{8,12}$/', $phone)) {
                $this->dispatch('alert', type: 'error', message: 'Format nomor telepon tidak valid (gunakan format Indonesia)');
                return;
            }
        }
        
        // Validate WhatsApp number format (Indonesian format)
        if (!empty($this->contactWhatsapp)) {
            $whatsapp = preg_replace('/[^0-9+]/', '', $this->contactWhatsapp);
            if (!preg_match('/^(\+62|62|0)[0-9]{8,12}$/', $whatsapp)) {
                $this->dispatch('alert', type: 'error', message: 'Format nomor WhatsApp tidak valid (gunakan format Indonesia)');
                return;
            }
        }
        
        // Save contact information
        $setting = \App\Models\StoreSetting::first();
        
        if (!$setting) {
            $setting = \App\Models\StoreSetting::create([
                'contact_phone' => $this->contactPhone,
                'contact_email' => $this->contactEmail,
                'contact_whatsapp' => $this->contactWhatsapp,
                'contact_address' => $this->contactAddress,
                'about_text' => $this->aboutText,
            ]);
        } else {
            $setting->update([
                'contact_phone' => $this->contactPhone,
                'contact_email' => $this->contactEmail,
                'contact_whatsapp' => $this->contactWhatsapp,
                'contact_address' => $this->contactAddress,
                'about_text' => $this->aboutText,
            ]);
        }
        
        $this->dispatch('alert', type: 'success', message: 'Informasi kontak berhasil disimpan');
    }

    public function render()
    {
        return view('livewire.admin.settings.store-settings')
            ->layout('layouts.app')
            ->title('Pengaturan Toko');
    }
}
