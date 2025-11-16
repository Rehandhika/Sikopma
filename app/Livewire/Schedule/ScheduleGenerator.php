<?php

namespace App\Livewire\Schedule;

use Livewire\Component;
use App\Models\{Schedule, ScheduleAssignment, Availability, AvailabilityDetail, User};
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ScheduleGenerator extends Component
{
    public $startDate;
    public $endDate;
    public $sessionId = 1;
    public $autoAssign = true;
    public $generateStatus = '';
    public $generatedCount = 0;

    public function mount()
    {
        $this->startDate = now()->startOfWeek()->format('Y-m-d');
        $this->endDate = now()->addWeeks(2)->endOfWeek()->format('Y-m-d');
    }

    public function generateSchedule()
    {
        $this->validate([
            'startDate' => 'required|date|before_or_equal:endDate',
            'endDate' => 'required|date|after_or_equal:startDate|within_date_range:90',
        ], [
            'startDate.before_or_equal' => 'Tanggal mulai harus sebelum atau sama dengan tanggal selesai.',
            'endDate.after_or_equal' => 'Tanggal selesai harus setelah atau sama dengan tanggal mulai.',
        ]);

        if ($this->scheduleTemplates->isEmpty()) {
            $this->dispatch('alert', type: 'error', message: 'Tidak ada template jadwal yang aktif. Silakan buat template terlebih dahulu.');
            return;
        }

        $this->isGenerating = true;

        try {
            DB::beginTransaction();

            // Clear existing assignments for the period
            ScheduleAssignment::whereBetween('date', [
                $this->startDate,
                $this->endDate
            ])->delete();

            // Generate new assignments
            $assignments = $this->generateScheduleAssignments(false);

            if (!empty($assignments)) {
                // Batch insert for performance
                ScheduleAssignment::insert($assignments);
                
                // Create notifications for assigned users
                $this->createScheduleNotifications($assignments);
            }

            DB::commit();

            $this->generatedCount = count($assignments);
            $this->generationStatus = 'success';
            $this->showPreview = false;
            $this->previewAssignments = [];

            $this->dispatch('alert', type: 'success', message: "Berhasil generate {$this->generatedCount} jadwal!");
            $this->dispatch('schedule-generated');

        } catch (\Exception $e) {
            DB::rollBack();
            $this->generationStatus = 'error';
            $this->dispatch('alert', type: 'error', message: 'Gagal generate jadwal: ' . $e->getMessage());
        } finally {
            $this->isGenerating = false;
        }
    }

    private function generateScheduleAssignments($isPreview = false)
    {
        $assignments = [];
        $startDate = Carbon::parse($this->startDate);
        $endDate = Carbon::parse($this->endDate);
        $current = $startDate->copy();

        // Get user availability for the week
        $weekStart = $startDate->copy()->startOfWeek(Carbon::MONDAY);
        $userAvailabilities = $this->getUserAvailabilities($weekStart);

        // Track assignments per user for balancing
        $userAssignmentCounts = [];

        while ($current <= $endDate) {
            $dayName = strtolower($current->englishName);
            
            // Get templates for this day
            $dayTemplates = $this->scheduleTemplates->where('day', $dayName);
            
            foreach ($dayTemplates as $template) {
                $user = $this->selectOptimalUser($template, $current, $userAvailabilities, $userAssignmentCounts);
                
                if ($user) {
                    $assignments[] = [
                        'user_id' => $user->id,
                        'date' => $current->format('Y-m-d'),
                        'schedule_id' => $template->id,
                        'session' => $template->session,
                        'time_start' => $template->time_start,
                        'time_end' => $template->time_end,
                        'status' => 'scheduled',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];

                    // Track assignment count
                    if (!isset($userAssignmentCounts[$user->id])) {
                        $userAssignmentCounts[$user->id] = 0;
                    }
                    $userAssignmentCounts[$user->id]++;
                }
            }

            $current->addDay();
        }

        return $assignments;
    }

    private function getUserAvailabilities($weekStart)
    {
        return AvailabilityDetail::whereHas('availability', function ($query) use ($weekStart) {
            $query->where('week_start', $weekStart->format('Y-m-d'))
                  ->where('status', 'active');
        })
        ->with('availability.user')
        ->get()
        ->groupBy(function ($detail) {
            return $detail->availability->user_id;
        })
        ->map(function ($userDetails) {
            return [
                'user_id' => $userDetails->first()->availability->user_id,
                'user' => $userDetails->first()->availability->user,
                'available_days' => $userDetails->pluck('day')->unique()->toArray(),
                'time_slots' => $userDetails->map(function ($detail) {
                    return [
                        'day' => $detail->day,
                        'start_time' => $detail->start_time,
                        'end_time' => $detail->end_time,
                    ];
                })->groupBy('day'),
            ];
        });
    }

    private function selectOptimalUser($template, $date, $userAvailabilities, $userAssignmentCounts)
    {
        $dayName = strtolower($date->englishName);
        $availableUsers = [];

        foreach ($userAvailabilities as $userId => $availability) {
            // Check if user is available on this day
            if (!in_array($dayName, $availability['available_days'])) {
                continue;
            }

            // Check if user is available during the template time
            $dayTimeSlots = $availability['time_slots']->get($dayName, collect());
            $isTimeAvailable = $dayTimeSlots->contains(function ($slot) use ($template) {
                $templateStart = Carbon::parse($template->time_start);
                $templateEnd = Carbon::parse($template->time_end);
                $slotStart = Carbon::parse($slot['start_time']);
                $slotEnd = Carbon::parse($slot['end_time']);

                return $templateStart >= $slotStart && $templateEnd <= $slotEnd;
            });

            if (!$isTimeAvailable) {
                continue;
            }

            // Check if user already has assignment for this date and session
            $hasConflict = ScheduleAssignment::where('user_id', $userId)
                ->where('date', $date->format('Y-m-d'))
                ->where('session', $template->session)
                ->exists();

            if ($hasConflict) {
                continue;
            }

            $availableUsers[] = [
                'user' => $availability['user'],
                'assignment_count' => $userAssignmentCounts[$userId] ?? 0,
            ];
        }

        if (empty($availableUsers)) {
            return null;
        }

        // Sort by assignment count (fewer assignments first)
        usort($availableUsers, function ($a, $b) {
            return $a['assignment_count'] - $b['assignment_count'];
        });

        return $availableUsers[0]['user'];
    }

    private function createScheduleNotifications($assignments)
    {
        $userAssignments = collect($assignments)->groupBy('user_id');
        
        foreach ($userAssignments as $userId => $userSchedules) {
            $user = User::find($userId);
            if ($user) {
                // Create notification for user
                // This would integrate with your notification system
                // For now, we'll just log it
                \Log::info("Schedule notification created for user {$user->name}", [
                    'schedules_count' => count($userSchedules),
                    'week_start' => $this->startDate,
                    'week_end' => $this->endDate,
                ]);
            }
        }
    }

    public function clearGeneratedSchedules()
    {
        try {
            $deletedCount = ScheduleAssignment::whereBetween('date', [
                $this->startDate,
                $this->endDate
            ])->delete();

            $this->dispatch('alert', type: 'success', message: "Berhasil menghapus {$deletedCount} jadwal.");
            $this->generatedCount = 0;
            $this->generationStatus = '';
            $this->showPreview = false;
            $this->previewAssignments = [];

        } catch (\Exception $e) {
            $this->dispatch('alert', type: 'error', message: 'Gagal menghapus jadwal: ' . $e->getMessage());
        }
    }

    public function getDayName($day)
    {
        $days = [
            'monday' => 'Senin',
            'tuesday' => 'Selasa',
            'wednesday' => 'Rabu',
            'thursday' => 'Kamis',
            'friday' => 'Jumat',
            'saturday' => 'Sabtu',
            'sunday' => 'Minggu',
        ];

        return $days[$day] ?? $day;
    }

    public function getPreviewStats()
    {
        if (empty($this->previewAssignments)) {
            return [
                'total_assignments' => 0,
                'unique_users' => 0,
                'assignments_per_user' => 0,
                'coverage_rate' => 0,
            ];
        }

        $assignments = collect($this->previewAssignments);
        $totalPossible = $this->scheduleTemplates->count() * 7; // templates * days
        $uniqueUsers = $assignments->pluck('user_id')->unique()->count();

        return [
            'total_assignments' => $assignments->count(),
            'unique_users' => $uniqueUsers,
            'assignments_per_user' => $uniqueUsers > 0 ? round($assignments->count() / $uniqueUsers, 1) : 0,
            'coverage_rate' => $totalPossible > 0 ? round(($assignments->count() / $totalPossible) * 100, 1) : 0,
        ];
    }

    public function __invoke()
    {
        return $this->render();
    }

    public function render()
    {
        return view('livewire.schedule.schedule-generator', [
            'weekRange' => $this->weekRange,
            'previewStats' => $this->getPreviewStats(),
            'isGenerating' => $this->isGenerating,
        ])->layout('layouts.app')->title('Generator Jadwal');
    }
}
