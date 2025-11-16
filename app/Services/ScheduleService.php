<?php

namespace App\Services;

use App\Models\Schedule;
use App\Models\Availability;
use App\Models\ScheduleAssignment;
use App\Models\User;
use App\Repositories\ScheduleRepository;
use App\Exceptions\BusinessException;
use App\Exceptions\ScheduleConflictException;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ScheduleService
{
    protected ScheduleRepository $repository;

    public function __construct(ScheduleRepository $repository = null)
    {
        $this->repository = $repository ?: new ScheduleRepository();
    }

    /**
     * Move schedule to new date/time
     */
    public function moveSchedule(int $scheduleId, Carbon $newDate, int $newSession, bool $force = false): bool
    {
        try {
            return DB::transaction(function () use ($scheduleId, $newDate, $newSession, $force) {
                $schedule = ScheduleAssignment::findOrFail($scheduleId);
                
                // Check for conflicts
                if (!$force && $this->repository->hasConflict($schedule->user_id, $newDate, $newSession)) {
                    throw new ScheduleConflictException('Schedule conflict detected');
                }

                // If force move, remove existing schedule
                if ($force) {
                    ScheduleAssignment::where('user_id', $schedule->user_id)
                        ->where('date', $newDate)
                        ->where('session', $newSession)
                        ->delete();
                }

                // Update the schedule
                $updated = $this->repository->update($scheduleId, [
                    'date' => $newDate,
                    'session' => $newSession,
                ]);

                if ($updated) {
                    Log::info('Schedule moved', [
                        'schedule_id' => $scheduleId,
                        'user_id' => $schedule->user_id,
                        'old_date' => $schedule->date->toDateString(),
                        'new_date' => $newDate->toDateString(),
                        'old_session' => $schedule->session,
                        'new_session' => $newSession,
                        'force' => $force,
                    ]);

                    // Send notification
                    NotificationService::createSwapNotification(
                        $schedule->user,
                        'schedule_changed',
                        [
                            'schedule_id' => $scheduleId,
                            'new_date' => $newDate->toDateString(),
                            'new_session' => $newSession,
                        ]
                    );
                }

                return $updated;
            });

        } catch (\Exception $e) {
            Log::error('Failed to move schedule', [
                'schedule_id' => $scheduleId,
                'new_date' => $newDate->toDateString(),
                'new_session' => $newSession,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }
    public function generateSchedule(Schedule $schedule): array
    {
        try {
            DB::beginTransaction();

            // 1. Validate all users have submitted availability
            if (!$this->allUsersSubmitted($schedule)) {
                throw new \Exception('Not all users have submitted their availability');
            }

            // 2. Get all availabilities
            $availabilities = $this->getAvailabilities($schedule);

            // 3. Initialize assignment tracker
            $userAssignments = [];
            $scheduleSlots = $this->initializeSlots($schedule);

            // 4. Get active users
            $users = User::active()->get();
            $usersPerSession = $this->calculateUsersPerSession($users->count());

            // 5. Generate assignments
            foreach (['monday', 'tuesday', 'wednesday', 'thursday'] as $day) {
                foreach (['1', '2', '3'] as $session) {
                    $eligibleUsers = $this->getEligibleUsers(
                        $day,
                        $session,
                        $availabilities,
                        $userAssignments
                    );

                    // Sort by least assignments
                    $eligibleUsers = $eligibleUsers->sortBy(function($user) use ($userAssignments) {
                        return count($userAssignments[$user->id] ?? []);
                    });

                    // Assign users to this slot
                    $assigned = 0;
                    foreach ($eligibleUsers as $user) {
                        if ($assigned >= $usersPerSession) break;

                        // Check no same-day conflict
                        if ($this->hasSameDayAssignment($user->id, $day, $userAssignments)) {
                            continue;
                        }

                        // Create assignment
                        $assignment = $this->createAssignment($schedule, $user, $day, $session);

                        // Track assignment
                        if (!isset($userAssignments[$user->id])) {
                            $userAssignments[$user->id] = [];
                        }
                        $userAssignments[$user->id][] = ['day' => $day, 'session' => $session];

                        $assigned++;
                    }
                }
            }

            // 6. Validate all users have exactly 2 assignments
            if (!$this->validateAssignments($userAssignments, $users->count())) {
                throw new \Exception('Unable to generate balanced schedule. Manual adjustment needed.');
            }

            // 7. Update schedule status
            $schedule->update([
                'status' => 'draft',
                'generated_by' => auth()->id(),
                'generated_at' => now(),
            ]);

            DB::commit();

            return [
                'success' => true,
                'message' => 'Schedule generated successfully',
                'assignments' => $userAssignments,
            ];

        } catch (\Exception $e) {
            DB::rollBack();

            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Check if all users have submitted availability
     */
    private function allUsersSubmitted(Schedule $schedule): bool
    {
        $totalUsers = User::active()->count();
        $submittedCount = Availability::where('schedule_id', $schedule->id)
            ->where('status', 'submitted')
            ->count();

        return $totalUsers === $submittedCount;
    }

    /**
     * Get availabilities grouped by user
     */
    private function getAvailabilities(Schedule $schedule): Collection
    {
        return Availability::with(['details', 'user'])
            ->where('schedule_id', $schedule->id)
            ->where('status', 'submitted')
            ->get();
    }

    /**
     * Initialize empty schedule slots
     */
    private function initializeSlots(Schedule $schedule): array
    {
        $slots = [];
        foreach (['monday', 'tuesday', 'wednesday', 'thursday'] as $day) {
            $slots[$day] = [];
            foreach (['1', '2', '3'] as $session) {
                $slots[$day][$session] = [];
            }
        }
        return $slots;
    }

    /**
     * Calculate how many users per session
     */
    private function calculateUsersPerSession(int $totalUsers): int
    {
        // Each user gets 2 sessions per week
        // 12 total sessions (4 days x 3 sessions)
        return (int) ceil(($totalUsers * 2) / 12);
    }

    /**
     * Get users eligible for specific day/session
     */
    private function getEligibleUsers(
        string $day,
        string $session,
        Collection $availabilities,
        array $userAssignments
    ): Collection {
        return $availabilities->filter(function($availability) use ($day, $session, $userAssignments) {
            // Check if user already has 2 assignments
            if (isset($userAssignments[$availability->user_id]) &&
                count($userAssignments[$availability->user_id]) >= 2) {
                return false;
            }

            // Check if user is available for this slot
            $detail = $availability->details
                ->where('day', $day)
                ->where('session', $session)
                ->where('is_available', true)
                ->first();

            return $detail !== null;
        })->map(function($availability) {
            return $availability->user;
        });
    }

    /**
     * Check if user already has assignment on same day
     */
    private function hasSameDayAssignment(int $userId, string $day, array $userAssignments): bool
    {
        if (!isset($userAssignments[$userId])) {
            return false;
        }

        foreach ($userAssignments[$userId] as $assignment) {
            if ($assignment['day'] === $day) {
                return true;
            }
        }

        return false;
    }

    /**
     * Create schedule assignment
     */
    private function createAssignment(
        Schedule $schedule,
        User $user,
        string $day,
        string $session
    ): ScheduleAssignment {
        $date = $this->getDateForDay($schedule->week_start_date, $day);
        $times = $this->getSessionTimes($session);

        return ScheduleAssignment::create([
            'schedule_id' => $schedule->id,
            'user_id' => $user->id,
            'day' => $day,
            'session' => $session,
            'date' => $date,
            'time_start' => $times['start'],
            'time_end' => $times['end'],
            'status' => 'scheduled',
        ]);
    }

    /**
     * Get actual date for day name
     */
    private function getDateForDay(Carbon $weekStart, string $day): Carbon
    {
        $days = [
            'monday' => 0,
            'tuesday' => 1,
            'wednesday' => 2,
            'thursday' => 3,
        ];

        return $weekStart->copy()->addDays($days[$day]);
    }

    /**
     * Get session time range
     */
    private function getSessionTimes(string $session): array
    {
        $times = [
            '1' => ['start' => '08:00', 'end' => '12:00'],
            '2' => ['start' => '12:00', 'end' => '16:00'],
            '3' => ['start' => '16:00', 'end' => '20:00'],
        ];

        return $times[$session];
    }

    /**
     * Validate all users have exactly 2 assignments
     */
    private function validateAssignments(array $userAssignments, int $totalUsers): bool
    {
        // Check count matches
        if (count($userAssignments) !== $totalUsers) {
            return false;
        }

        // Check each user has exactly 2
        foreach ($userAssignments as $assignments) {
            if (count($assignments) !== 2) {
                return false;
            }
        }

        return true;
    }

    /**
     * Publish schedule and notify users
     */
    public function publishSchedule(Schedule $schedule): bool
    {
        try {
            DB::beginTransaction();

            $schedule->update([
                'status' => 'published',
                'published_at' => now(),
            ]);

            // Notify all users
            $this->notifyUsersAboutSchedule($schedule);

            DB::commit();
            return true;

        } catch (\Exception $e) {
            DB::rollBack();
            return false;
        }
    }

    /**
     * Send notifications to all users about new schedule
     */
    private function notifyUsersAboutSchedule(Schedule $schedule): void
    {
        $assignments = $schedule->assignments()->with('user')->get();

        foreach ($assignments->groupBy('user_id') as $userId => $userAssignments) {
            $user = $userAssignments->first()->user;

            $message = "Jadwal jaga minggu {$schedule->week_start_date->format('d M')} - {$schedule->week_end_date->format('d M')} telah dipublikasi. ";
            $message .= "Anda dijadwalkan pada: ";

            $scheduleText = $userAssignments->map(function($assignment) {
                return "{$assignment->day_label}, {$assignment->date->format('d M')} - Sesi {$assignment->session} ({$assignment->session_label})";
            })->join(' dan ');

            $message .= $scheduleText;

            NotificationService::send($user, 'schedule_published', 'Jadwal Jaga Baru', $message);
        }
    }
}
