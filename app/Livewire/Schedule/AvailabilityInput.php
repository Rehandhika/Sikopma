<?php

namespace App\Livewire\Schedule;

use Livewire\Component;

class AvailabilityInput extends Component
{
    public function render()
    {
        return view('livewire.schedule.availability-input')
            ->layout('layouts.app');
    }
}
