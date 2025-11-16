<?php

namespace App\Services;

use App\Models\{ScheduleAssignment, User, SwapRequest};
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;

class ShiftManagementService
{
    /**
     * Auto-assign users to shifts using intelligent algorithm
     */
    public function autoAssignShifts(Carbon $startDate, Carbon $endDate, int $sessionId = null): array
    {
        return DB::transaction(function () use ($startDate, $endDate, $sessionId) {
            $results = [
                'assigned' => 0,
                'skipped' => 0,
                'errors' => [],
            ];

            // Get available users with their current assignment counts
            $availableUsers = $this->getAvailableUsersWithStats($sessionId);
            
            if ($availableUsers->isEmpty()) {
                $results['errors'][] = 'Tidak ada pengguna yang tersedia untuk penugasan';
                return $results;
            }

            // Get schedule templates
            $scheduleTemplates = $this->getScheduleTemplates($sessionId);
            
            if ($scheduleTemplates->isEmpty()) {
                $results['errors'][] = 'Tidak ada template jadwal yang ditemukan';
                return $results;
            }

            // Process each day in the range
            $current = $startDate->copy();
            $assignments = [];
            
            while ($current <= $endDate) {
                if ($current->isWeekend()) {
                    $current->addDay();
                    continue;
                }

                $dayName = strtolower($current->englishName);
                $template = $scheduleTemplates->get($dayName);

                if ($template) {
                    $assignedUser = $this->selectOptimalUser($availableUsers, $current, $sessionId);
                    
                    if ($assignedUser) {
                        $assignments[] = [
                            'user_id' => $assignedUser->id,
                            'date' => $current->format('Y-m-d'),
                            'schedule_id' => $template->id,
                            'session' => $sessionId ?? $template->session,
                            'status' => 'scheduled',
                            'time_start' => $template->time_start,
                            'time_end' => $template->time_end,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];
                        
                        // Update user's assignment count
                        $assignedUser->current_assignments++;
                        $results['assigned']++;
                    } else {
                        $results['skipped']++;
                    }
                }

                $current->addDay();
            }

            // Batch insert assignments
            if (!empty($assignments)) {
                ScheduleAssignment::insert($assignments);
            }

            return $results;
        });
    }

    /**
     * Get available users with their assignment statistics
     */
    private function getAvailableUsersWithStats(?int $sessionId): Collection
    {
        $query = User::select(['id', 'name', 'nim', 'status'])
            ->where('status', 'active')
            ->whereHas('availabilities', function ($query) {
                $query->whereHas('details', function ($subQuery) {
                    $subQuery->where('day', 'all')
                        ->orWhere('day', strtolower(now()->englishName));
                });
            });

        // Add assignment counts
        $query->withCount([
            'assignments as current_assignments' => function ($query) use ($sessionId) {
                $query->whereBetween('date', [now()->startOfMonth(), now()->endOfMonth()]);
                if ($sessionId) {
                    $query->where('session', $sessionId);
                }
            }
        ]);

        return $query->orderBy('current_assignments', 'asc')->get();
    }

    /**
     * Get schedule templates grouped by day
     */
    private function getScheduleTemplates(?int $sessionId): Collection
    {
        $query = \App\Models\Schedule::select(['id', 'day', 'session', 'time_start', 'time_end']);
        
        if ($sessionId) {
            $query->where('session', $sessionId);
        }

        return $query->get()->keyBy('day');
    }

    /**
     * Select optimal user for assignment using multiple criteria
     */
    private function selectOptimalUser(Collection $users, Carbon $date, ?int $sessionId): ?User
    {
        // Filter users who are available on this date
        $availableUsers = $users->filter(function ($user) use ($date) {
            return !$this->hasConflict($user, $date, $sessionId);
        });

        if ($availableUsers->isEmpty()) {
            return null;
        }

        // Score users based on multiple factors
        $scoredUsers = $availableUsers->map(function ($user) use ($date) {
            $score = 0;
            
            // Base score: fewer current assignments = higher score
            $score += max(0, 10 - $user->current_assignments);
            
            // Prefer users with recent activity (optional)
            $score += $this->getActivityScore($user);
            
            // Balance workload across users
            $score += $this->getBalanceScore($user, $date);
            
            return [
                'user' => $user,
                'score' => $score,
            ];
        });

        // Select user with highest score
        return $scoredUsers->sortByDesc('score')->first()['user'];
    }

    /**
     * Check if user has assignment conflict
     */
    private function hasConflict(User $user, Carbon $date, ?int $sessionId): bool
    {
        return ScheduleAssignment::where('user_id', $user->id)
            ->where('date', $date->format('Y-m-d'))
            ->when($sessionId, function ($query) use ($sessionId) {
                return $query->where('session', $sessionId);
            })
            ->exists();
    }

    /**
     * Get activity score for user
     */
    private function getActivityScore(User $user): int
    {
        // Simple implementation: check recent swap activity
        $recentSwaps = SwapRequest::where('requester_id', $user->id)
            ->where('created_at', '>=', now()->subDays(30))
            ->count();

        return min($recentSwaps, 3); // Max 3 points for activity
    }

    /**
     * Get balance score to distribute workload evenly
     */
    private function getBalanceScore(User $user, Carbon $date): int
    {
        // Check assignments in the current week
        $weekStart = $date->copy()->startOfWeek();
        $weekEnd = $date->copy()->endOfWeek();
        
        $weekAssignments = ScheduleAssignment::where('user_id', $user->id)
            ->whereBetween('date', [$weekStart, $weekEnd])
            ->count();

        // Prefer users with fewer assignments this week
        return max(0, 5 - $weekAssignments);
    }

    /**
     * Optimize existing assignments by balancing workload
     */
    public function optimizeAssignments(Carbon $startDate, Carbon $endDate): array
    {
        return DB::transaction(function () use ($startDate, $endDate) {
            $results = [
                'optimized' => 0,
                'unchanged' => 0,
                'errors' => [],
            ];

            // Get all assignments in the period
            $assignments = ScheduleAssignment::whereBetween('date', [$startDate, $endDate])
                ->with(['user', 'schedule'])
                ->get()
                ->groupBy('date');

            foreach ($assignments as $date => $dayAssignments) {
                $optimized = $this->optimizeDayAssignments($dayAssignments);
                
                if ($optimized > 0) {
                    $results['optimized'] += $optimized;
                } else {
                    $results['unchanged'] += $dayAssignments->count();
                }
            }

            return $results;
        });
    }

    /**
     * Optimize assignments for a specific day
     */
    private function optimizeDayAssignments(Collection $assignments): int
    {
        $optimized = 0;
        $userWorkloads = $this->calculateUserWorkloads($assignments);

        // Find users with excessive workload
        $overloadedUsers = $userWorkloads->filter(function ($workload) {
            return $workload['assignments'] > 5; // Threshold for "overloaded"
        });

        if ($overloadedUsers->isNotEmpty()) {
            // Try to reassign some shifts to underutilized users
            $underutilizedUsers = $userWorkloads->filter(function ($workload) {
                return $workload['assignments'] < 3; // Threshold for "underutilized"
            });

            $optimized = $this->rebalanceAssignments($assignments, $overloadedUsers, $underutilizedUsers);
        }

        return $optimized;
    }

    /**
     * Calculate current workloads for all users
     */
    private function calculateUserWorkloads(Collection $assignments): Collection
    {
        return $assignments->groupBy('user_id')->map(function ($userAssignments) {
            $user = $userAssignments->first()->user;
            
            return [
                'user' => $user,
                'assignments' => $userAssignments->count(),
                'total_hours' => $userAssignments->sum(function ($assignment) {
                    $start = Carbon::parse($assignment->time_start);
                    $end = Carbon::parse($assignment->time_end);
                    return $start->diffInHours($end);
                }),
            ];
        });
    }

    /**
     * Rebalance assignments between overloaded and underutilized users
     */
    private function rebalanceAssignments(
        Collection $assignments, 
        Collection $overloadedUsers, 
        Collection $underutilizedUsers
    ): int {
        $rebalanced = 0;

        foreach ($overloadedUsers as $overloaded) {
            if ($underutilizedUsers->isEmpty()) {
                break;
            }

            $userAssignments = $assignments->where('user_id', $overloaded['user']->id);
            $assignmentsToMove = min(2, $userAssignments->count()); // Move max 2 assignments

            for ($i = 0; $i < $assignmentsToMove && $underutilizedUsers->isNotEmpty(); $i++) {
                $assignment = $userAssignments->first();
                $targetUser = $underutilizedUsers->first()['user'];

                // Check if target user can take this assignment
                if (!$this->hasConflict($targetUser, Carbon::parse($assignment->date), $assignment->session)) {
                    $assignment->update(['user_id' => $targetUser->id]);
                    $rebalanced++;

                    // Update workloads
                    $overloaded['assignments']--;
                    $underutilizedUsers->first()['assignments']++;

                    // Remove from underutilized if now balanced
                    if ($underutilizedUsers->first()['assignments'] >= 3) {
                        $underutilizedUsers->shift();
                    }
                }
            }
        }

        return $rebalanced;
    }

    /**
     * Generate shift conflict report
     */
    public function generateConflictReport(Carbon $startDate, Carbon $endDate): array
    {
        $conflicts = [];
        
        // Check for double assignments
        $doubleAssignments = ScheduleAssignment::select('user_id', 'date', 'session')
            ->selectRaw('COUNT(*) as count')
            ->whereBetween('date', [$startDate, $endDate])
            ->groupBy('user_id', 'date', 'session')
            ->having('count', '>', 1)
            ->with('user:id,name')
            ->get();

        foreach ($doubleAssignments as $conflict) {
            $conflicts[] = [
                'type' => 'double_assignment',
                'user' => $conflict->user->name,
                'date' => $conflict->date,
                'session' => $conflict->session,
                'count' => $conflict->count,
            ];
        }

        // Check for users with excessive workload
        $excessiveWorkloads = ScheduleAssignment::select('user_id')
            ->selectRaw('DATE(date) as day, COUNT(*) as daily_assignments')
            ->whereBetween('date', [$startDate, $endDate])
            ->groupBy('user_id', 'day')
            ->having('daily_assignments', '>', 2)
            ->with('user:id,name')
            ->get();

        foreach ($excessiveWorkloads as $workload) {
            $conflicts[] = [
                'type' => 'excessive_workload',
                'user' => $workload->user->name,
                'date' => $workload->day,
                'assignments' => $workload->daily_assignments,
            ];
        }

        return [
            'conflicts' => $conflicts,
            'total_conflicts' => count($conflicts),
            'period' => $startDate->format('Y-m-d') . ' to ' . $endDate->format('Y-m-d'),
        ];
    }

    /**
     * Get shift statistics and insights
     */
    public function getShiftInsights(Carbon $startDate, Carbon $endDate): array
    {
        $assignments = ScheduleAssignment::whereBetween('date', [$startDate, $endDate])
            ->with(['user', 'schedule'])
            ->get();

        return [
            'total_assignments' => $assignments->count(),
            'unique_users' => $assignments->pluck('user_id')->unique()->count(),
            'avg_assignments_per_user' => $assignments->count() / max(1, $assignments->pluck('user_id')->unique()->count()),
            'busiest_days' => $this->getBusiestDays($assignments),
            'most_active_users' => $this->getMostActiveUsers($assignments),
            'session_distribution' => $this->getSessionDistribution($assignments),
            'coverage_rate' => $this->calculateCoverageRate($startDate, $endDate),
        ];
    }

    /**
     * Get busiest days in the period
     */
    private function getBusiestDays(Collection $assignments): Collection
    {
        return $assignments->groupBy(function ($assignment) {
            return Carbon::parse($assignment->date)->format('l');
        })->map(function ($dayAssignments) {
            return [
                'day' => $dayAssignments->first()->date,
                'assignments' => $dayAssignments->count(),
                'users' => $dayAssignments->pluck('user_id')->unique()->count(),
            ];
        })->sortByDesc('assignments')->take(5);
    }

    /**
     * Get most active users
     */
    private function getMostActiveUsers(Collection $assignments): Collection
    {
        return $assignments->groupBy('user_id')->map(function ($userAssignments) {
            $user = $userAssignments->first()->user;
            return [
                'user' => $user->name,
                'assignments' => $userAssignments->count(),
                'total_hours' => $userAssignments->sum(function ($assignment) {
                    $start = Carbon::parse($assignment->time_start);
                    $end = Carbon::parse($assignment->time_end);
                    return $start->diffInHours($end);
                }),
            ];
        })->sortByDesc('assignments')->take(10);
    }

    /**
     * Get distribution by session
     */
    private function getSessionDistribution(Collection $assignments): Collection
    {
        return $assignments->groupBy('session')->map(function ($sessionAssignments) {
            return [
                'session' => $sessionAssignments->first()->session,
                'assignments' => $sessionAssignments->count(),
                'percentage' => round(($sessionAssignments->count() / $assignments->count()) * 100, 1),
            ];
        });
    }

    /**
     * Calculate shift coverage rate
     */
    private function calculateCoverageRate(Carbon $startDate, Carbon $endDate): float
    {
        $totalDays = $startDate->diffInDays($endDate) + 1;
        $workingDays = $totalDays; // Adjust for weekends if needed
        
        $totalRequiredShifts = $workingDays * 3; // Assuming 3 sessions per day
        $actualAssignments = ScheduleAssignment::whereBetween('date', [$startDate, $endDate])->count();
        
        return round(($actualAssignments / $totalRequiredShifts) * 100, 1);
    }
}
