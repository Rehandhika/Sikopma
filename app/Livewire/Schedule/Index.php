<?php

namespace App\Livewire\Schedule;

use Livewire\Component;
use App\Models\ScheduleAssignment;
use Carbon\Carbon;

class Index extends Component
{
    public $currentDate;
    public $viewMode = 'week'; // week, month
    public $weekStart;
    public $weekEnd;

    public function mount()
    {
        $this->currentDate = Carbon::now();
        $this->calculateWeekRange();
    }

    public function calculateWeekRange()
    {
        $this->weekStart = $this->currentDate->copy()->startOfWeek();
        $this->weekEnd = $this->currentDate->copy()->endOfWeek();
    }

    public function previousWeek()
    {
        $this->currentDate->subWeek();
        $this->calculateWeekRange();
    }

    public function nextWeek()
    {
        $this->currentDate->addWeek();
        $this->calculateWeekRange();
    }

    public function today()
    {
        $this->currentDate = Carbon::now();
        $this->calculateWeekRange();
    }

    public function __invoke()
    {
        return $this->render();
    }

    public function render()
    {
        // Ensure dates are initialized
        if (!$this->currentDate) {
            $this->currentDate = Carbon::now();
        }
        if (!$this->weekStart || !$this->weekEnd) {
            $this->calculateWeekRange();
        }

        $schedules = ScheduleAssignment::query()
            ->whereBetween('date', [$this->weekStart, $this->weekEnd])
            ->with(['user', 'schedule'])
            ->orderBy('date')
            ->orderBy('session')
            ->get()
            ->groupBy('date');

        $days = [];
        for ($i = 0; $i < 7; $i++) {
            $date = $this->weekStart->copy()->addDays($i);
            $days[] = [
                'date' => $date,
                'schedules' => $schedules->get($date->format('Y-m-d'), collect())
            ];
        }

        return view('livewire.schedule.index', [
            'days' => $days
        ])->layout('layouts.app')->title('Kalender Jadwal');
    }
}
