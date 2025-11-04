<?php

namespace App\Livewire\Schedule;

use Livewire\Component;

class ScheduleGenerator extends Component
{
    public function render()
    {
        return view('livewire.schedule.schedule-generator')
            ->layout('layouts.app');
    }
}
