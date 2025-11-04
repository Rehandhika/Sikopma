<?php

namespace App\Livewire\Schedule;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Title;
use App\Models\{Schedule, ScheduleAssignment};
use Carbon\Carbon;

#[Title('Jadwal Saya')]
class MySchedule extends Component
{
    use WithPagination;

    public $weekOffset = 0;
    public $currentWeekStart;
    public $currentWeekEnd;

    public function mount()
    {
        $this->updateWeekDates();
    }

    public function previousWeek()
    {
        $this->weekOffset--;
        $this->updateWeekDates();
        $this->resetPage();
    }

    public function nextWeek()
    {
        $this->weekOffset++;
        $this->updateWeekDates();
        $this->resetPage();
    }

    public function currentWeek()
    {
        $this->weekOffset = 0;
        $this->updateWeekDates();
        $this->resetPage();
    }

    private function updateWeekDates()
    {
        $baseDate = Carbon::now()->addWeeks($this->weekOffset);
        $this->currentWeekStart = $baseDate->copy()->startOfWeek();
        $this->currentWeekEnd = $baseDate->copy()->endOfWeek();
    }

    public function getMySchedulesProperty()
    {
        return ScheduleAssignment::query()
            ->where('user_id', auth()->id())
            ->whereBetween('date', [
                $this->currentWeekStart->toDateString(),
                $this->currentWeekEnd->toDateString()
            ])
            ->with(['schedule', 'user'])
            ->orderBy('date', 'asc')
            ->orderBy('session', 'asc')
            ->get()
            ->groupBy(function($assignment) {
                return $assignment->date->format('Y-m-d');
            });
    }

    public function getWeekDaysProperty()
    {
        $days = [];
        for ($i = 0; $i < 7; $i++) {
            $date = $this->currentWeekStart->copy()->addDays($i);
            $days[] = [
                'date' => $date,
                'dayName' => $date->locale('id')->dayName,
                'isToday' => $date->isToday(),
                'isPast' => $date->isPast() && !$date->isToday(),
            ];
        }
        return $days;
    }

    public function getUpcomingSchedulesProperty()
    {
        return ScheduleAssignment::query()
            ->where('user_id', auth()->id())
            ->where('date', '>=', Carbon::today())
            ->where('date', '<=', Carbon::today()->addDays(7))
            ->with('schedule')
            ->orderBy('date', 'asc')
            ->orderBy('session', 'asc')
            ->limit(5)
            ->get();
    }

    public function render()
    {
        return view('livewire.schedule.my-schedule', [
            'mySchedules' => $this->mySchedules,
            'weekDays' => $this->weekDays,
            'upcomingSchedules' => $this->upcomingSchedules,
        ])->layout('layouts.app');
    }
}
