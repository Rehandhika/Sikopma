<?php

namespace App\Livewire\Schedule;

use Livewire\Component;

class TestAvailability extends Component
{
    public function render()
    {
        return view('livewire.schedule.test-availability')
            ->layout('layouts.app')
            ->title('Test Availability');
    }
}
