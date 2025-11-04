<?php

namespace App\Livewire\Settings;

use Livewire\Component;
use App\Models\Setting;

class General extends Component
{
    public $app_name;
    public $app_description;
    public $contact_email;
    public $contact_phone;
    public $address;

    public function mount()
    {
        $this->app_name = Setting::get('app_name', config('app.name'));
        $this->app_description = Setting::get('app_description', '');
        $this->contact_email = Setting::get('contact_email', '');
        $this->contact_phone = Setting::get('contact_phone', '');
        $this->address = Setting::get('address', '');
    }

    public function save()
    {
        $this->validate([
            'app_name' => 'required|string|max:255',
            'contact_email' => 'nullable|email',
            'contact_phone' => 'nullable|string|max:20',
        ]);

        Setting::set('app_name', $this->app_name);
        Setting::set('app_description', $this->app_description);
        Setting::set('contact_email', $this->contact_email);
        Setting::set('contact_phone', $this->contact_phone);
        Setting::set('address', $this->address);

        $this->dispatch('alert', type: 'success', message: 'Pengaturan berhasil disimpan');
    }

    public function render()
    {
        return view('livewire.settings.general')
            ->layout('layouts.app')
            ->title('Pengaturan Umum');
    }
}
