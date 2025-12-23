<?php

namespace App\Livewire\Schedule;

use Livewire\Component;
use App\Models\Schedule;
use Carbon\Carbon;

class PreviewSchedule extends Component
{
    // Props
    public $scheduleId = null;
    public $schedule = null;
    public $assignments = [];
    public $show = false;
    public $isEditable = false;
    
    // Statistics
    public $totalAssignments = 0;
    public $coverageRate = 0;
    public $assignmentsPerUser = [];
    
    // View mode
    public $viewMode = 'calendar'; // calendar, list
    
    protected $listeners = [
        'open-preview' => 'openPreview',
        'close-preview' => 'closePreview',
    ];

    /**
     * Open preview modal
     */
    public function openPreview($scheduleId = null, $assignments = [], $isEditable = false): void
    {
        $this->scheduleId = $scheduleId;
        $this->assignments = $assignments;
        $this->isEditable = $isEditable;
        $this->show = true;
        
        if ($scheduleId) {
            $this->loadSchedule();
        } else {
            $this->calculateStatistics();
        }
    }

    /**
     * Close preview modal
     */
    public function closePreview(): void
    {
        $this->show = false;
        $this->reset(['scheduleId', 'schedule', 'assignments', 'isEditable']);
    }

    /**
     * Load schedule from database
     */
    private function loadSchedule(): void
    {
        $this->schedule = Schedule::with(['assignments.user'])->find($this->scheduleId);
        
        if ($this->schedule) {
            // Convert assignments to array format
            $this->assignments = [];
            $startDate = Carbon::parse($this->schedule->week_start_date);
            
            for ($day = 0; $day < 4; $day++) {
                $date = $startDate->copy()->addDays($day);
                $dateStr = $date->format('Y-m-d');
                $this->assignments[$dateStr] = [];
                
                for ($session = 1; $session <= 3; $session++) {
                    $assignment = $this->schedule->assignments->first(function($a) use ($dateStr, $session) {
                        return $a->date === $dateStr && $a->session == $session;
                    });
                    
                    if ($assignment) {
                        $this->assignments[$dateStr][$session] = [
                            'user_id' => $assignment->user_id,
                            'user_name' => $assignment->user->name,
                            'user_nim' => $assignment->user->nim,
                            'user_photo' => $assignment->user->photo,
                            'date' => $dateStr,
                            'session' => $session,
                        ];
                    } else {
                        $this->assignments[$dateStr][$session] = null;
                    }
                }
            }
            
            $this->calculateStatistics();
        }
    }

    /**
     * Calculate statistics
     */
    private function calculateStatistics(): void
    {
        $allAssignments = collect($this->assignments)->flatten(1)->filter();
        
        $this->totalAssignments = $allAssignments->count();
        $this->coverageRate = round(($this->totalAssignments / 12) * 100, 2);
        
        // Count per user
        $this->assignmentsPerUser = $allAssignments->groupBy('user_id')
            ->map(function($group) {
                return [
                    'user_name' => $group->first()['user_name'],
                    'count' => $group->count(),
                ];
            })
            ->sortByDesc('count')
            ->values()
            ->toArray();
    }

    /**
     * Switch view mode
     */
    public function setViewMode(string $mode): void
    {
        $this->viewMode = $mode;
    }

    /**
     * Get formatted date range
     */
    public function getDateRange(): string
    {
        if (empty($this->assignments)) {
            return '';
        }
        
        $dates = array_keys($this->assignments);
        $startDate = Carbon::parse(min($dates))->locale('id');
        $endDate = Carbon::parse(max($dates))->locale('id');
        
        return $startDate->isoFormat('D MMMM YYYY') . ' - ' . $endDate->isoFormat('D MMMM YYYY');
    }

    /**
     * Get session time label
     */
    public function getSessionTime(int $session): string
    {
        $times = [
            1 => '07:30 - 10:00',
            2 => '10:20 - 12:50',
            3 => '13:30 - 16:00',
        ];
        return $times[$session] ?? '';
    }

    /**
     * Get user initials for avatar fallback
     */
    public function getUserInitials(string $name): string
    {
        $words = explode(' ', $name);
        
        if (count($words) >= 2) {
            return strtoupper(substr($words[0], 0, 1) . substr($words[1], 0, 1));
        }
        
        return strtoupper(substr($name, 0, 2));
    }

    /**
     * Print schedule
     */
    public function printSchedule(): void
    {
        $this->dispatch('print-schedule');
    }

    /**
     * Edit assignment (if editable)
     */
    public function editAssignment(string $date, int $session): void
    {
        if (!$this->isEditable) {
            $this->dispatch('alert', type: 'warning', message: 'Preview ini tidak dapat diedit.');
            return;
        }
        
        // Dispatch to parent to open user selector
        $this->dispatch('edit-from-preview', date: $date, session: $session);
    }

    public function render()
    {
        return view('livewire.schedule.preview-schedule');
    }
}
