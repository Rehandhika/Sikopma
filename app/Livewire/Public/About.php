<?php

namespace App\Livewire\Public;

use App\Models\StoreSetting;
use Livewire\Component;

class About extends Component
{
    public $storeSetting;
    public $operatingDays = [];

    public function mount()
    {
        // Load store settings
        $this->storeSetting = StoreSetting::first();
        
        // If no settings exist, create default
        if (!$this->storeSetting) {
            $this->storeSetting = new StoreSetting([
                'contact_phone' => '-',
                'contact_email' => '-',
                'contact_whatsapp' => '-',
                'contact_address' => '-',
                'about_text' => 'Informasi tentang koperasi akan segera tersedia.',
                'operating_hours' => $this->getDefaultOperatingHours(),
            ]);
        }
        
        // Format operating hours for display
        $this->formatOperatingHours();
    }

    protected function getDefaultOperatingHours()
    {
        return [
            'monday' => ['open' => '07:30', 'close' => '16:00', 'is_open' => true],
            'tuesday' => ['open' => '07:30', 'close' => '16:00', 'is_open' => true],
            'wednesday' => ['open' => '07:30', 'close' => '16:00', 'is_open' => true],
            'thursday' => ['open' => '07:30', 'close' => '16:00', 'is_open' => true],
            'friday' => ['open' => null, 'close' => null, 'is_open' => false],
            'saturday' => ['open' => null, 'close' => null, 'is_open' => false],
            'sunday' => ['open' => null, 'close' => null, 'is_open' => false],
        ];
    }

    protected function formatOperatingHours()
    {
        $dayNames = [
            'monday' => 'Senin',
            'tuesday' => 'Selasa',
            'wednesday' => 'Rabu',
            'thursday' => 'Kamis',
            'friday' => 'Jumat',
            'saturday' => 'Sabtu',
            'sunday' => 'Minggu',
        ];

        $operatingHours = $this->storeSetting->operating_hours ?? $this->getDefaultOperatingHours();

        foreach ($dayNames as $key => $name) {
            $hours = $operatingHours[$key] ?? ['is_open' => false];
            
            $this->operatingDays[] = [
                'name' => $name,
                'is_open' => $hours['is_open'] ?? false,
                'open' => $hours['open'] ?? null,
                'close' => $hours['close'] ?? null,
            ];
        }
    }

    public function render()
    {
        $storeSetting = \Illuminate\Support\Facades\Cache::remember('store_settings:about', 3600, function () {
            return \App\Models\StoreSetting::first();
        });

        // Static operating days array (could also be from config, but this is fast enough)
        $operatingDays = [
            ['name' => 'Senin', 'open' => '07:30', 'close' => '16:00', 'is_open' => true],
            ['name' => 'Selasa', 'open' => '07:30', 'close' => '16:00', 'is_open' => true],
            ['name' => 'Rabu', 'open' => '07:30', 'close' => '16:00', 'is_open' => true],
            ['name' => 'Kamis', 'open' => '07:30', 'close' => '16:00', 'is_open' => true],
            ['name' => 'Jumat', 'open' => '-', 'close' => '-', 'is_open' => false],
            ['name' => 'Sabtu', 'open' => '-', 'close' => '-', 'is_open' => false],
            ['name' => 'Minggu', 'open' => '-', 'close' => '-', 'is_open' => false],
        ];

        return view('livewire.public.about', [
            'storeSetting' => $storeSetting,
            'operatingDays' => $operatingDays
        ])->layout('layouts.public');
    }
}
