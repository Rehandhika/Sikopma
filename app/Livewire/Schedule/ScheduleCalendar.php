<?php

namespace App\Livewire\Schedule;

use App\Models\ScheduleAssignment;
use App\Services\ActivityLogService;
use Carbon\Carbon;
use Livewire\Component;

class ScheduleCalendar extends Component
{
    public $currentMonth;

    public $currentYear;

    public $selectedDate;

    public $viewMode = 'month'; // month, week, day

    public $showDetails = false;

    public $selectedAssignment = null;

    public $filterUser = '';

    public $filterSession = '';

    public $search = '';

    protected $queryString = [
        'currentMonth' => ['except' => ''],
        'currentYear' => ['except' => ''],
        'viewMode' => ['except' => 'month'],
        'filterUser' => ['except' => ''],
        'filterSession' => ['except' => ''],
    ];

    public function mount()
    {
        $this->currentMonth = now()->format('m');
        $this->currentYear = now()->format('Y');
        $this->selectedDate = now()->format('Y-m-d');
    }

    public function previousMonth()
    {
        $date = Carbon::create($this->currentYear, $this->currentMonth, 1)->subMonth();
        $this->currentMonth = $date->format('m');
        $this->currentYear = $date->format('Y');
    }

    public function nextMonth()
    {
        $date = Carbon::create($this->currentYear, $this->currentMonth, 1)->addMonth();
        $this->currentMonth = $date->format('m');
        $this->currentYear = $date->format('Y');
    }

    public function goToToday()
    {
        $this->currentMonth = now()->format('m');
        $this->currentYear = now()->format('Y');
        $this->selectedDate = now()->format('Y-m-d');
    }

    public function selectDate($date)
    {
        $this->selectedDate = $date;
        $this->showDetails = true;
    }

    public function closeDetails()
    {
        $this->showDetails = false;
        $this->selectedAssignment = null;
    }

    public function resetFilters()
    {
        $this->filterUser = '';
        $this->filterSession = '';
        $this->search = '';
    }

    public function viewAssignment($assignmentId)
    {
        $this->selectedAssignment = ScheduleAssignment::with(['user', 'schedule'])
            ->find($assignmentId);
    }

    public function getCalendarDays()
    {
        $startDate = Carbon::create($this->currentYear, $this->currentMonth, 1)
            ->startOfWeek(Carbon::MONDAY);
        $endDate = Carbon::create($this->currentYear, $this->currentMonth, 1)
            ->endOfMonth()
            ->endOfWeek(Carbon::SUNDAY);

        $days = [];
        $current = $startDate->copy();

        while ($current <= $endDate) {
            $days[] = [
                'date' => $current->format('Y-m-d'),
                'day' => $current->format('d'),
                'is_current_month' => $current->format('m') === $this->currentMonth,
                'is_today' => $current->isToday(),
                'is_weekend' => $current->isWeekend(),
                'assignments' => $this->getAssignmentsForDate($current->format('Y-m-d')),
            ];
            $current->addDay();
        }

        return $days;
    }

    private function getAssignmentsForDate($date)
    {
        $query = ScheduleAssignment::with(['user', 'schedule'])
            ->where('date', $date);

        if ($this->filterUser) {
            $query->where('user_id', $this->filterUser);
        }

        if ($this->filterSession) {
            $query->where('session', $this->filterSession);
        }

        if ($this->search) {
            $query->whereHas('user', function ($q) {
                $q->where('name', 'like', '%'.$this->search.'%')
                    ->orWhere('nim', 'like', '%'.$this->search.'%');
            });
        }

        return $query->orderBy('session')->orderBy('time_start')->get();
    }

    public function getSelectedDateAssignments()
    {
        return $this->getAssignmentsForDate($this->selectedDate);
    }

    public function getMonthName()
    {
        return Carbon::create($this->currentYear, $this->currentMonth, 1)
            ->locale('id')->format('F Y');
    }

    public function getAvailableUsers()
    {
        return \App\Models\User::where('status', 'active')
            ->orderBy('name')
            ->pluck('name', 'id');
    }

    public function getSessionOptions()
    {
        return [
            '' => 'Semua Sesi',
            1 => 'Sesi 1 (Pagi)',
            2 => 'Sesi 2 (Siang)',
            3 => 'Sesi 3 (Sore)',
        ];
    }

    public function getMonthStats()
    {
        $startDate = Carbon::create($this->currentYear, $this->currentMonth, 1)->startOfMonth();
        $endDate = Carbon::create($this->currentYear, $this->currentMonth, 1)->endOfMonth();

        $query = ScheduleAssignment::whereBetween('date', [$startDate, $endDate]);

        if ($this->filterUser) {
            $query->where('user_id', $this->filterUser);
        }

        if ($this->filterSession) {
            $query->where('session', $this->filterSession);
        }

        if ($this->search) {
            $query->whereHas('user', function ($q) {
                $q->where('name', 'like', '%'.$this->search.'%')
                    ->orWhere('nim', 'like', '%'.$this->search.'%');
            });
        }

        return [
            'total_assignments' => $query->count(),
            'unique_users' => $query->distinct('user_id')->count('user_id'),
            'total_hours' => $query->get()->sum(function ($assignment) {
                $start = Carbon::parse($assignment->time_start);
                $end = Carbon::parse($assignment->time_end);

                return $start->diffInHours($end);
            }),
            'coverage_days' => $query->distinct('date')->count('date'),
        ];
    }

    public function exportCalendar()
    {
        // Log activity
        $monthName = Carbon::create($this->currentYear, $this->currentMonth, 1)->locale('id')->format('F Y');
        ActivityLogService::logReportExported('Kalender Jadwal', $monthName);

        // Implementation for calendar export
        $this->dispatch('export-calendar', [
            'month' => $this->currentMonth,
            'year' => $this->currentYear,
            'filters' => [
                'user' => $this->filterUser,
                'session' => $this->filterSession,
                'search' => $this->search,
            ],
        ]);
    }

    public function render()
    {
        return view('livewire.schedule.schedule-calendar', [
            'calendarDays' => $this->getCalendarDays(),
            'selectedDateAssignments' => $this->getSelectedDateAssignments(),
            'monthName' => $this->getMonthName(),
            'availableUsers' => $this->getAvailableUsers(),
            'sessionOptions' => $this->getSessionOptions(),
            'monthStats' => $this->getMonthStats(),
        ])->layout('layouts.app')->title('Kalender Jadwal');
    }
}
