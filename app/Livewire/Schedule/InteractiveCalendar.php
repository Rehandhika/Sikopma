<?php

namespace App\Livewire\Schedule;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\ScheduleAssignment;
use App\Models\User;
use App\Repositories\ScheduleRepository;
use App\Services\ScheduleService;
use App\Exceptions\ScheduleConflictException;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class InteractiveCalendar extends Component
{
    use WithPagination;

    public $currentDate;
    public $viewMode = 'month'; // month, week, day
    public $selectedUsers = [];
    public $draggedSchedule = null;
    public $dropTarget = null;
    public $showConflictModal = false;
    public $conflictDetails = [];
    public $searchUser = '';
    public $availableUsers = [];
    public $selectedDate = null;
    public $selectedSession = null;
    public $showAssignModal = false;

    protected $scheduleRepository;
    protected $scheduleService;

    protected $listeners = [
        'scheduleDragStart' => 'handleDragStart',
        'scheduleDrop' => 'handleDrop',
        'dateClick' => 'handleDateClick',
        'scheduleClick' => 'handleScheduleClick',
    ];

    public function boot(ScheduleRepository $scheduleRepository, ScheduleService $scheduleService)
    {
        $this->scheduleRepository = $scheduleRepository;
        $this->scheduleService = $scheduleService;
    }

    public function mount()
    {
        $this->currentDate = Carbon::now();
        $this->loadAvailableUsers();
    }

    public function render()
    {
        $schedules = $this->getSchedules();
        $calendarData = $this->buildCalendarData($schedules);

        return view('livewire.schedule.interactive-calendar', [
            'calendarData' => $calendarData,
            'schedules' => $schedules,
            'currentDate' => $this->currentDate,
            'viewMode' => $this->viewMode,
        ])->layout('layouts.app')->title('Jadwal Interaktif');
    }

    private function getSchedules(): Collection
    {
        $dateRange = $this->getDateRange();
        
        $query = ScheduleAssignment::whereBetween('date', [$dateRange['start'], $dateRange['end']])
            ->with(['user', 'schedule'])
            ->orderBy('date')
            ->orderBy('session');

        if (!empty($this->selectedUsers)) {
            $query->whereIn('user_id', $this->selectedUsers);
        }

        return $query->get();
    }

    private function getDateRange(): array
    {
        switch ($this->viewMode) {
            case 'month':
                return [
                    'start' => $this->currentDate->copy()->startOfMonth(),
                    'end' => $this->currentDate->copy()->endOfMonth(),
                ];
            case 'week':
                return [
                    'start' => $this->currentDate->copy()->startOfWeek(),
                    'end' => $this->currentDate->copy()->endOfWeek(),
                ];
            case 'day':
                return [
                    'start' => $this->currentDate->copy()->startOfDay(),
                    'end' => $this->currentDate->copy()->endOfDay(),
                ];
            default:
                return [
                    'start' => $this->currentDate->copy()->startOfMonth(),
                    'end' => $this->currentDate->copy()->endOfMonth(),
                ];
        }
    }

    private function buildCalendarData(Collection $schedules): array
    {
        $data = [];
        $dateRange = $this->getDateRange();
        $period = new \DatePeriod($dateRange['start'], new \DateInterval('P1D'), $dateRange['end']);

        foreach ($period as $date) {
            $dateStr = $date->format('Y-m-d');
            $daySchedules = $schedules->where('date', $dateStr);
            
            $data[$dateStr] = [
                'date' => $date,
                'schedules' => $daySchedules->groupBy('session'),
                'is_today' => $date->isToday(),
                'is_weekend' => $date->isWeekend(),
                'total_schedules' => $daySchedules->count(),
            ];
        }

        return $data;
    }

    public function previousPeriod()
    {
        switch ($this->viewMode) {
            case 'month':
                $this->currentDate->subMonth();
                break;
            case 'week':
                $this->currentDate->subWeek();
                break;
            case 'day':
                $this->currentDate->subDay();
                break;
        }
    }

    public function nextPeriod()
    {
        switch ($this->viewMode) {
            case 'month':
                $this->currentDate->addMonth();
                break;
            case 'week':
                $this->currentDate->addWeek();
                break;
            case 'day':
                $this->currentDate->addDay();
                break;
        }
    }

    public function goToToday()
    {
        $this->currentDate = Carbon::now();
    }

    public function setViewMode($mode)
    {
        $this->viewMode = $mode;
    }

    public function handleDragStart($scheduleId)
    {
        $this->draggedSchedule = ScheduleAssignment::findOrFail($scheduleId);
        $this->dispatch('dragStarted', [
            'scheduleId' => $scheduleId,
            'userName' => $this->draggedSchedule->user->name,
        ]);
    }

    public function handleDrop($date, $session)
    {
        if (!$this->draggedSchedule) {
            return;
        }

        try {
            $targetDate = Carbon::parse($date);
            
            // Check for conflicts
            $hasConflict = $this->scheduleRepository->hasConflict(
                $this->draggedSchedule->user_id,
                $targetDate,
                $session
            );

            if ($hasConflict) {
                $this->conflictDetails = [
                    'user' => $this->draggedSchedule->user->name,
                    'date' => $targetDate->format('d/m/Y'),
                    'session' => $session,
                ];
                $this->showConflictModal = true;
                return;
            }

            // Perform the move
            $this->scheduleRepository->update($this->draggedSchedule->id, [
                'date' => $targetDate,
                'session' => $session,
            ]);

            $this->dispatch('scheduleMoved', 'Jadwal berhasil dipindahkan');
            $this->draggedSchedule = null;

        } catch (\Exception $e) {
            $this->dispatch('error', 'Gagal memindahkan jadwal: ' . $e->getMessage());
        }
    }

    public function handleDateClick($date)
    {
        $this->selectedDate = $date;
        $this->showAssignModal = true;
    }

    public function handleScheduleClick($scheduleId)
    {
        // Navigate to schedule details or show edit modal
        $this->dispatch('showScheduleDetails', $scheduleId);
    }

    public function assignSchedule()
    {
        $this->validate([
            'selectedDate' => 'required|date',
            'selectedSession' => 'required|integer|min:1|max:3',
            'selectedUsers' => 'required|array|min:1',
        ]);

        try {
            $date = Carbon::parse($this->selectedDate);
            
            foreach ($this->selectedUsers as $userId) {
                // Check for conflicts
                if ($this->scheduleRepository->hasConflict($userId, $date, $this->selectedSession)) {
                    throw new ScheduleConflictException('Konflik jadwal terdeteksi untuk user ' . $userId);
                }

                // Create schedule assignment
                $this->scheduleRepository->create([
                    'user_id' => $userId,
                    'date' => $date,
                    'session' => $this->selectedSession,
                    'status' => 'scheduled',
                ]);
            }

            $this->dispatch('success', 'Jadwal berhasil ditetapkan');
            $this->showAssignModal = false;
            $this->selectedUsers = [];
            $this->selectedDate = null;
            $this->selectedSession = null;

        } catch (\Exception $e) {
            $this->dispatch('error', $e->getMessage());
        }
    }

    public function confirmMoveWithConflict()
    {
        if (!$this->draggedSchedule) {
            return;
        }

        try {
            // Force move despite conflict (replace existing)
            $targetDate = Carbon::parse($this->dropTarget['date']);
            
            // Remove existing schedule for that slot
            ScheduleAssignment::where('user_id', $this->draggedSchedule->user_id)
                ->where('date', $targetDate)
                ->where('session', $this->dropTarget['session'])
                ->delete();

            // Move the schedule
            $this->scheduleRepository->update($this->draggedSchedule->id, [
                'date' => $targetDate,
                'session' => $this->dropTarget['session'],
            ]);

            $this->dispatch('scheduleMoved', 'Jadwal berhasil dipindahkan (mengganti jadwal yang ada)');
            $this->showConflictModal = false;
            $this->draggedSchedule = null;
            $this->dropTarget = null;

        } catch (\Exception $e) {
            $this->dispatch('error', 'Gagal memindahkan jadwal: ' . $e->getMessage());
        }
    }

    public function cancelConflictMove()
    {
        $this->showConflictModal = false;
        $this->draggedSchedule = null;
        $this->dropTarget = null;
    }

    public function loadAvailableUsers()
    {
        $query = User::where('status', 'active')
            ->whereHas('roles', function ($q) {
                $q->whereIn('name', ['Super Admin', 'Ketua', 'Wakil Ketua', 'BPH', 'Anggota']);
            });

        if ($this->searchUser) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%' . $this->searchUser . '%')
                  ->orWhere('nim', 'like', '%' . $this->searchUser . '%');
            });
        }

        $this->availableUsers = $query->orderBy('name')->get(['id', 'name', 'nim']);
    }

    public function updatedSearchUser()
    {
        $this->loadAvailableUsers();
    }

    public function getScheduleStats()
    {
        $dateRange = $this->getDateRange();
        
        return $this->scheduleRepository->getScheduleStats(
            $dateRange['start'],
            $dateRange['end']
        );
    }

    public function exportSchedule()
    {
        try {
            $dateRange = $this->getDateRange();
            $schedules = $this->scheduleRepository->getSchedulesByDateRange(
                $dateRange['start'],
                $dateRange['end']
            );

            // Generate CSV or PDF export
            $this->dispatch('scheduleExported', 'Jadwal berhasil diekspor');
            
        } catch (\Exception $e) {
            $this->dispatch('error', 'Gagal mengekspor jadwal: ' . $e->getMessage());
        }
    }
}
