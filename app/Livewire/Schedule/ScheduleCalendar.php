<?php

namespace App\Livewire\Schedule;

use Livewire\Component;

class ScheduleCalendar extends Component
{
    public function render()
    {
        return view('livewire.schedule.schedule-calendar')
            ->layout('layouts.app');
    }
}
