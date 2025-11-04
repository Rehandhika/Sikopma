<?php

namespace App\Livewire\Settings;

use Livewire\Component;

class SystemSettings extends Component
{
    public function render()
    {
        return view('livewire.settings.system-settings')
            ->layout('layouts.app');
    }
}
