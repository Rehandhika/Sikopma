<?php

namespace App\Livewire\Schedule;

use App\Models\Schedule;
use App\Models\ScheduleAssignment;
use Carbon\Carbon;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Jadwal Saya')]
class MySchedule extends Component
{
    use WithPagination;

    public $weekOffset = 0;

    public $currentWeekStart;

    public $currentWeekEnd;

    /**
     * Listen for schedule-updated event to refresh data
     */
    #[On('schedule-updated')]
    public function onScheduleUpdated(): void
    {
        // Reset pagination and refresh data
        $this->resetPage();
    }

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
        if (! $this->currentWeekStart || ! $this->currentWeekEnd) {
            $this->updateWeekDates();
        }

        return ScheduleAssignment::query()
            ->where('user_id', auth()->id())
            ->whereBetween('date', [
                $this->currentWeekStart->toDateString(),
                $this->currentWeekEnd->toDateString(),
            ])
            ->with(['schedule', 'user'])
            ->orderBy('date', 'asc')
            ->orderBy('session', 'asc')
            ->get()
            ->groupBy(function ($assignment) {
                return $assignment->date->format('Y-m-d');
            });
    }

    public function getWeekDaysProperty()
    {
        if (! $this->currentWeekStart) {
            $this->updateWeekDates();
        }

        $days = [];
        for ($i = 0; $i < 7; $i++) {
            $date = $this->currentWeekStart->copy()->addDays($i);
            $days[] = [
                'date' => $date,
                'dayName' => $date->locale('id')->dayName,
                'isToday' => $date->isToday(),
                'isPast' => $date->isPast() && ! $date->isToday(),
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
        if (! $this->currentWeekStart || ! $this->currentWeekEnd) {
            $this->updateWeekDates();
        }

        return view('livewire.schedule.my-schedule', [
            'mySchedules' => $this->mySchedules,
            'weekDays' => $this->weekDays,
            'upcomingSchedules' => $this->upcomingSchedules,
            'currentWeekStart' => $this->currentWeekStart,
            'currentWeekEnd' => $this->currentWeekEnd,
            'weekOffset' => $this->weekOffset,
        ])->layout('layouts.app');
    }
}
