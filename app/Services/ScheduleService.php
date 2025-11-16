<?php

namespace App\Services;

use App\Models\{Schedule, ScheduleAssignment, User, Notification};
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class ScheduleService
{
    /**
     * Create a new schedule
     */
    public function createSchedule(array $data): Schedule
    {
        // Validate dates
        $weekStart = Carbon::parse($data['week_start_date']);
        $weekEnd = Carbon::parse($data['week_end_date']);

        if (!$weekStart->isMonday()) {
            throw ValidationException::withMessages([
                'week_start_date' => 'Tanggal mulai harus hari Senin.'
            ]);
        }

        if (!$weekStart->copy()->addDays(3)->isSameDay($weekEnd)) {
            throw ValidationException::withMessages([
                'week_end_date' => 'Periode jadwal harus 4 hari (Senin-Kamis).'
            ]);
        }

        // Check for duplicate schedule
        if (Schedule::where('week_start_date', $weekStart->toDateString())->exists()) {
            throw ValidationException::withMessages([
                'week_start_date' => 'Jadwal untuk minggu ini sudah ada.'
            ]);
        }

        // Create schedule
        $schedule = Schedule::create([
            'week_start_date' => $weekStart,
            'week_end_date' => $weekEnd,
            'status' => 'draft',
            'generated_by' => auth()->id(),
            'generated_at' => now(),
            'total_slots' => 12, // 4 days × 3 sessions
            'filled_slots' => 0,
            'coverage_rate' => 0,
            'notes' => $data['notes'] ?? null,
        ]);

        Log::info('Schedule created', [
            'schedule_id' => $schedule->id,
            'week_start' => $weekStart->toDateString(),
            'created_by' => auth()->id(),
        ]);

        return $schedule;
    }

    /**
     * Add assignment to schedule
     */
    public function addAssignment(Schedule $schedule, array $data): ScheduleAssignment
    {
        // Validate schedule is editable
        if (!$schedule->canEdit()) {
            throw new \Exception('Jadwal tidak dapat diubah karena sudah dipublikasikan.');
        }

        // Validate user exists and is active
        $user = User::find($data['user_id']);
        if (!$user || $user->status !== 'active') {
            throw ValidationException::withMessages([
                'user_id' => 'User tidak valid atau tidak aktif.'
            ]);
        }

        // Check for conflicts
        $conflict = $this->checkConflict($data['user_id'], $data['date'], $data['session']);
        if ($conflict) {
            throw ValidationException::withMessages([
                'user_id' => 'User sudah memiliki assignment pada waktu yang sama.'
            ]);
        }

        // Create assignment
        DB::beginTransaction();
        try {
            $assignment = ScheduleAssignment::create([
                'schedule_id' => $schedule->id,
                'user_id' => $data['user_id'],
                'date' => $data['date'],
                'day' => strtolower(Carbon::parse($data['date'])->englishDayOfWeek),
                'session' => $data['session'],
                'time_start' => $this->getSessionTime($data['session'], 'start'),
                'time_end' => $this->getSessionTime($data['session'], 'end'),
                'status' => 'scheduled',
                'notes' => $data['notes'] ?? null,
            ]);

            // Update schedule statistics
            $schedule->calculateCoverage();

            DB::commit();

            Log::info('Assignment added', [
                'assignment_id' => $assignment->id,
                'schedule_id' => $schedule->id,
                'user_id' => $data['user_id'],
            ]);

            return $assignment;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Remove assignment from schedule
     */
    public function removeAssignment(ScheduleAssignment $assignment): bool
    {
        $schedule = $assignment->schedule;

        // Validate schedule is editable
        if (!$schedule->canEdit()) {
            throw new \Exception('Jadwal tidak dapat diubah karena sudah dipublikasikan.');
        }

        DB::beginTransaction();
        try {
            $assignment->delete();

            // Update schedule statistics
            $schedule->calculateCoverage();

            DB::commit();

            Log::info('Assignment removed', [
                'assignment_id' => $assignment->id,
                'schedule_id' => $schedule->id,
            ]);

            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Publish schedule
     */
    public function publishSchedule(Schedule $schedule): bool
    {
        // Validate can publish
        if (!$schedule->canPublish()) {
            $conflicts = $schedule->detectConflicts();
            
            if (!empty($conflicts['critical'])) {
                throw new \Exception('Jadwal tidak dapat dipublikasikan karena masih ada konflik kritis.');
            }

            if ($schedule->coverage_rate < 50) {
                throw new \Exception('Jadwal tidak dapat dipublikasikan. Coverage minimal 50%.');
            }
        }

        DB::beginTransaction();
        try {
            $schedule->update([
                'status' => 'published',
                'published_at' => now(),
                'published_by' => auth()->id(),
            ]);

            // Send notifications to assigned users
            $this->sendScheduleNotifications($schedule);

            DB::commit();

            Log::info('Schedule published', [
                'schedule_id' => $schedule->id,
                'published_by' => auth()->id(),
            ]);

            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Calculate schedule statistics
     */
    public function calculateStatistics(Schedule $schedule): array
    {
        return $schedule->getStatistics();
    }

    /**
     * Copy from previous week
     */
    public function copyFromPreviousWeek(Schedule $sourceSchedule, Schedule $targetSchedule): void
    {
        if (!$targetSchedule->canEdit()) {
            throw new \Exception('Jadwal target tidak dapat diubah.');
        }

        DB::beginTransaction();
        try {
            $sourceAssignments = $sourceSchedule->assignments;
            $daysDiff = $targetSchedule->week_start_date->diffInDays($sourceSchedule->week_start_date);

            foreach ($sourceAssignments as $sourceAssignment) {
                // Adjust date
                $newDate = Carbon::parse($sourceAssignment->date)->addDays($daysDiff);

                // Validate user still active
                $user = User::find($sourceAssignment->user_id);
                if (!$user || $user->status !== 'active') {
                    Log::warning('Skipping assignment - user inactive', [
                        'user_id' => $sourceAssignment->user_id,
                        'date' => $newDate->toDateString(),
                    ]);
                    continue;
                }

                // Check for conflicts
                if ($this->checkConflict($sourceAssignment->user_id, $newDate, $sourceAssignment->session)) {
                    Log::warning('Skipping assignment - conflict detected', [
                        'user_id' => $sourceAssignment->user_id,
                        'date' => $newDate->toDateString(),
                        'session' => $sourceAssignment->session,
                    ]);
                    continue;
                }

                // Create new assignment
                ScheduleAssignment::create([
                    'schedule_id' => $targetSchedule->id,
                    'user_id' => $sourceAssignment->user_id,
                    'date' => $newDate,
                    'day' => strtolower($newDate->englishDayOfWeek),
                    'session' => $sourceAssignment->session,
                    'time_start' => $sourceAssignment->time_start,
                    'time_end' => $sourceAssignment->time_end,
                    'status' => 'scheduled',
                ]);
            }

            // Update statistics
            $targetSchedule->calculateCoverage();

            DB::commit();

            Log::info('Schedule copied', [
                'source_schedule_id' => $sourceSchedule->id,
                'target_schedule_id' => $targetSchedule->id,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Check for assignment conflict
     */
    private function checkConflict(int $userId, $date, int $session): bool
    {
        return ScheduleAssignment::where('user_id', $userId)
            ->where('date', $date)
            ->where('session', $session)
            ->exists();
    }

    /**
     * Get session time
     */
    private function getSessionTime(int $session, string $type): string
    {
        $times = [
            1 => ['start' => '08:00:00', 'end' => '12:00:00'],
            2 => ['start' => '13:00:00', 'end' => '17:00:00'],
            3 => ['start' => '17:00:00', 'end' => '21:00:00'],
        ];

        return $times[$session][$type] ?? '00:00:00';
    }

    /**
     * Send notifications to assigned users
     */
    private function sendScheduleNotifications(Schedule $schedule): void
    {
        $assignments = $schedule->assignments()->with('user')->get();
        $groupedByUser = $assignments->groupBy('user_id');

        foreach ($groupedByUser as $userId => $userAssignments) {
            $user = $userAssignments->first()->user;
            
            if (!$user) {
                continue;
            }

            $message = "Jadwal shift Anda untuk minggu {$schedule->week_start_date->format('d M')} - {$schedule->week_end_date->format('d M Y')} telah dipublikasikan. ";
            $message .= "Anda mendapat {$userAssignments->count()} shift.";

            Notification::create([
                'user_id' => $userId,
                'type' => 'schedule_published',
                'title' => 'Jadwal Shift Dipublikasikan',
                'message' => $message,
                'data' => json_encode([
                    'schedule_id' => $schedule->id,
                    'assignments_count' => $userAssignments->count(),
                ]),
                'read_at' => null,
            ]);
        }
    }
}
