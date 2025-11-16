<?php

namespace App\Services;

use App\Models\{Schedule, ScheduleAssignment, User, Availability, AvailabilityDetail};
use Illuminate\Support\Collection;

class ConflictDetectionService
{
    /**
     * Detect all conflicts in a schedule
     */
    public function detectConflicts(Schedule $schedule): array
    {
        $conflicts = [
            'critical' => [],
            'warning' => [],
            'info' => [],
        ];

        // Level 1: Critical Conflicts
        $conflicts['critical'] = array_merge(
            $conflicts['critical'],
            $this->checkDoubleAssignments($schedule),
            $this->checkInactiveUsers($schedule),
            $this->checkDeletedUsers($schedule)
        );

        // Level 2: Warning Conflicts
        $conflicts['warning'] = array_merge(
            $conflicts['warning'],
            $this->checkAvailabilityMismatches($schedule),
            $this->checkOverloadedUsers($schedule),
            $this->checkUnderloadedUsers($schedule)
        );

        // Level 3: Info
        $conflicts['info'] = array_merge(
            $conflicts['info'],
            $this->checkUnbalancedDistribution($schedule),
            $this->checkLowCoverage($schedule)
        );

        return $conflicts;
    }

    /**
     * Check for double assignments (same user, same time)
     */
    private function checkDoubleAssignments(Schedule $schedule): array
    {
        $conflicts = [];

        $doubleAssignments = ScheduleAssignment::where('schedule_id', $schedule->id)
            ->select('user_id', 'date', 'session')
            ->selectRaw('COUNT(*) as count')
            ->groupBy('user_id', 'date', 'session')
            ->havingRaw('COUNT(*) > 1')
            ->with('user:id,name')
            ->get();

        foreach ($doubleAssignments as $double) {
            $conflicts[] = [
                'type' => 'double_assignment',
                'severity' => 'critical',
                'message' => "User {$double->user->name} memiliki lebih dari 1 assignment pada {$double->date} Sesi {$double->session}",
                'data' => [
                    'user_id' => $double->user_id,
                    'date' => $double->date,
                    'session' => $double->session,
                    'count' => $double->count,
                ],
            ];
        }

        return $conflicts;
    }

    /**
     * Check for inactive users
     */
    private function checkInactiveUsers(Schedule $schedule): array
    {
        $conflicts = [];

        $inactiveAssignments = ScheduleAssignment::where('schedule_id', $schedule->id)
            ->whereHas('user', function($query) {
                $query->where('status', '!=', 'active');
            })
            ->with('user:id,name,status')
            ->get();

        foreach ($inactiveAssignments as $assignment) {
            $conflicts[] = [
                'type' => 'inactive_user',
                'severity' => 'critical',
                'message' => "User {$assignment->user->name} tidak aktif (status: {$assignment->user->status})",
                'data' => [
                    'assignment_id' => $assignment->id,
                    'user_id' => $assignment->user_id,
                    'user_status' => $assignment->user->status,
                    'date' => $assignment->date,
                    'session' => $assignment->session,
                ],
            ];
        }

        return $conflicts;
    }

    /**
     * Check for deleted users
     */
    private function checkDeletedUsers(Schedule $schedule): array
    {
        $conflicts = [];

        $deletedUserAssignments = ScheduleAssignment::where('schedule_id', $schedule->id)
            ->whereDoesntHave('user')
            ->get();

        foreach ($deletedUserAssignments as $assignment) {
            $conflicts[] = [
                'type' => 'deleted_user',
                'severity' => 'critical',
                'message' => "Assignment untuk user yang sudah dihapus (ID: {$assignment->user_id})",
                'data' => [
                    'assignment_id' => $assignment->id,
                    'user_id' => $assignment->user_id,
                    'date' => $assignment->date,
                    'session' => $assignment->session,
                ],
            ];
        }

        return $conflicts;
    }

    /**
     * Check for availability mismatches
     */
    private function checkAvailabilityMismatches(Schedule $schedule): array
    {
        $conflicts = [];

        $assignments = ScheduleAssignment::where('schedule_id', $schedule->id)
            ->with('user:id,name')
            ->get();

        foreach ($assignments as $assignment) {
            $dayName = strtolower($assignment->date->englishDayOfWeek);
            
            // Check if user marked as NOT available for this slot
            $isNotAvailable = AvailabilityDetail::whereHas('availability', function($query) use ($assignment, $schedule) {
                $query->where('user_id', $assignment->user_id)
                      ->whereBetween('submitted_at', [
                          $schedule->week_start_date->startOfDay(),
                          $schedule->week_end_date->endOfDay()
                      ])
                      ->where('status', 'submitted');
            })
            ->where('day', $dayName)
            ->where('session', $assignment->session)
            ->where('is_available', false)
            ->exists();

            if ($isNotAvailable) {
                $conflicts[] = [
                    'type' => 'availability_mismatch',
                    'severity' => 'warning',
                    'message' => "User {$assignment->user->name} tidak tersedia pada {$assignment->date->format('d M')} Sesi {$assignment->session}",
                    'data' => [
                        'assignment_id' => $assignment->id,
                        'user_id' => $assignment->user_id,
                        'date' => $assignment->date,
                        'session' => $assignment->session,
                    ],
                ];
            }
        }

        return $conflicts;
    }

    /**
     * Check for overloaded users (too many shifts)
     */
    private function checkOverloadedUsers(Schedule $schedule, int $maxShifts = 4): array
    {
        $conflicts = [];

        $userCounts = ScheduleAssignment::where('schedule_id', $schedule->id)
            ->select('user_id')
            ->selectRaw('COUNT(*) as count')
            ->groupBy('user_id')
            ->having('count', '>', $maxShifts)
            ->with('user:id,name')
            ->get();

        foreach ($userCounts as $userCount) {
            $conflicts[] = [
                'type' => 'overloaded_user',
                'severity' => 'warning',
                'message' => "User {$userCount->user->name} memiliki terlalu banyak shift ({$userCount->count} shift, max: {$maxShifts})",
                'data' => [
                    'user_id' => $userCount->user_id,
                    'count' => $userCount->count,
                    'max_shifts' => $maxShifts,
                ],
            ];
        }

        return $conflicts;
    }

    /**
     * Check for underloaded users (too few shifts)
     */
    private function checkUnderloadedUsers(Schedule $schedule, int $minShifts = 2): array
    {
        $conflicts = [];

        $userCounts = ScheduleAssignment::where('schedule_id', $schedule->id)
            ->select('user_id')
            ->selectRaw('COUNT(*) as count')
            ->groupBy('user_id')
            ->having('count', '<', $minShifts)
            ->with('user:id,name')
            ->get();

        foreach ($userCounts as $userCount) {
            $conflicts[] = [
                'type' => 'underloaded_user',
                'severity' => 'warning',
                'message' => "User {$userCount->user->name} memiliki terlalu sedikit shift ({$userCount->count} shift, min: {$minShifts})",
                'data' => [
                    'user_id' => $userCount->user_id,
                    'count' => $userCount->count,
                    'min_shifts' => $minShifts,
                ],
            ];
        }

        return $conflicts;
    }

    /**
     * Check for unbalanced distribution
     */
    private function checkUnbalancedDistribution(Schedule $schedule): array
    {
        $conflicts = [];

        $userCounts = ScheduleAssignment::where('schedule_id', $schedule->id)
            ->select('user_id')
            ->selectRaw('COUNT(*) as count')
            ->groupBy('user_id')
            ->pluck('count')
            ->toArray();

        if (empty($userCounts)) {
            return $conflicts;
        }

        $avg = array_sum($userCounts) / count($userCounts);
        $variance = array_sum(array_map(function($x) use ($avg) {
            return pow($x - $avg, 2);
        }, $userCounts)) / count($userCounts);
        $stdDev = sqrt($variance);

        // If standard deviation > 1, distribution is unbalanced
        if ($stdDev > 1) {
            $conflicts[] = [
                'type' => 'unbalanced_distribution',
                'severity' => 'info',
                'message' => "Distribusi shift tidak seimbang (std dev: " . round($stdDev, 2) . ")",
                'data' => [
                    'average' => round($avg, 2),
                    'std_dev' => round($stdDev, 2),
                    'min' => min($userCounts),
                    'max' => max($userCounts),
                ],
            ];
        }

        return $conflicts;
    }

    /**
     * Check for low coverage
     */
    private function checkLowCoverage(Schedule $schedule): array
    {
        $conflicts = [];

        if ($schedule->coverage_rate < 80) {
            $conflicts[] = [
                'type' => 'low_coverage',
                'severity' => 'info',
                'message' => "Coverage rendah: {$schedule->coverage_rate}% (target: 80%+)",
                'data' => [
                    'coverage_rate' => $schedule->coverage_rate,
                    'filled_slots' => $schedule->filled_slots,
                    'total_slots' => $schedule->total_slots,
                    'unassigned_slots' => $schedule->total_slots - $schedule->filled_slots,
                ],
            ];
        }

        return $conflicts;
    }

    /**
     * Suggest alternative users for a conflicting assignment
     */
    public function suggestAlternatives(ScheduleAssignment $conflictingAssignment, int $limit = 5): Collection
    {
        $dayName = strtolower($conflictingAssignment->date->englishDayOfWeek);
        $schedule = $conflictingAssignment->schedule;

        // Get users who are:
        // 1. Active
        // 2. Available for this slot
        // 3. Don't have assignment at this time
        // 4. Not overloaded
        $alternatives = User::where('status', 'active')
            ->whereDoesntHave('scheduleAssignments', function($query) use ($conflictingAssignment) {
                $query->where('date', $conflictingAssignment->date)
                      ->where('session', $conflictingAssignment->session);
            })
            ->whereHas('availabilities.details', function($query) use ($dayName, $conflictingAssignment, $schedule) {
                $query->where('day', $dayName)
                      ->where('session', $conflictingAssignment->session)
                      ->where('is_available', true)
                      ->whereHas('availability', function($q) use ($schedule) {
                          $q->whereBetween('submitted_at', [
                              $schedule->week_start_date->startOfDay(),
                              $schedule->week_end_date->endOfDay()
                          ]);
                      });
            })
            ->withCount(['scheduleAssignments' => function($query) use ($schedule) {
                $query->where('schedule_id', $schedule->id);
            }])
            ->orderBy('schedule_assignments_count', 'asc')
            ->limit($limit)
            ->get();

        return $alternatives->map(function($user) {
            return [
                'user' => $user,
                'current_shifts' => $user->schedule_assignments_count,
                'score' => 100 - ($user->schedule_assignments_count * 10), // Lower shifts = higher score
            ];
        });
    }

    /**
     * Resolve conflict automatically if possible
     */
    public function resolveConflict(string $conflictType, array $conflictData): bool
    {
        switch ($conflictType) {
            case 'double_assignment':
                // Keep the first assignment, delete others
                return $this->resolveDoubleAssignment($conflictData);
                
            case 'inactive_user':
            case 'deleted_user':
                // Remove assignment
                return $this->removeConflictingAssignment($conflictData);
                
            default:
                // Cannot auto-resolve
                return false;
        }
    }

    /**
     * Resolve double assignment
     */
    private function resolveDoubleAssignment(array $conflictData): bool
    {
        $assignments = ScheduleAssignment::where('user_id', $conflictData['user_id'])
            ->where('date', $conflictData['date'])
            ->where('session', $conflictData['session'])
            ->orderBy('created_at', 'asc')
            ->get();

        // Keep first, delete others
        $assignments->skip(1)->each(function($assignment) {
            $assignment->delete();
        });

        return true;
    }

    /**
     * Remove conflicting assignment
     */
    private function removeConflictingAssignment(array $conflictData): bool
    {
        if (isset($conflictData['assignment_id'])) {
            ScheduleAssignment::find($conflictData['assignment_id'])?->delete();
            return true;
        }

        return false;
    }
}
