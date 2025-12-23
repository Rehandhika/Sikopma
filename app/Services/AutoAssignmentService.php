<?php

namespace App\Services;

use App\Models\{Schedule, User, Availability, AvailabilityDetail, ScheduleAssignment};
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class AutoAssignmentService
{
    private float $fairnessWeight = 0.7;
    private float $availabilityWeight = 0.3;

    /**
     * Generate assignments automatically
     */
    public function generateAssignments(Schedule $schedule, array $options = []): array
    {
        // Override weights if provided
        if (isset($options['fairness_weight'])) {
            $this->fairnessWeight = $options['fairness_weight'];
        }
        if (isset($options['availability_weight'])) {
            $this->availabilityWeight = $options['availability_weight'];
        }

        // Get all slots (4 days × 3 sessions = 12 slots)
        $slots = $this->generateSlots($schedule);

        // Get available users with their availability data
        $availableUsers = $this->getUserAvailability($schedule);

        if ($availableUsers->isEmpty()) {
            throw new \Exception('Tidak ada user dengan availability yang tersedia.');
        }

        // Calculate optimal distribution
        $distribution = $this->calculateOptimalDistribution(
            count($slots),
            $availableUsers->count()
        );

        // Track current assignments per user
        $currentDistribution = [];
        foreach ($availableUsers as $userId => $userData) {
            $currentDistribution[$userId] = 0;
        }

        // Assign users to slots
        $assignments = [];
        foreach ($slots as $slot) {
            $user = $this->selectBestUser($slot, $availableUsers, $currentDistribution, $distribution);
            
            if ($user) {
                $assignments[] = [
                    'schedule_id' => $schedule->id,
                    'user_id' => $user->id,
                    'date' => $slot['date'],
                    'day' => $slot['day'],
                    'session' => $slot['session'],
                    'time_start' => $slot['time_start'],
                    'time_end' => $slot['time_end'],
                    'status' => 'scheduled',
                    'created_at' => now(),
                    'updated_at' => now(),
                ];

                $currentDistribution[$user->id]++;
            }
        }

        Log::info('Auto-assignment generated', [
            'schedule_id' => $schedule->id,
            'total_slots' => count($slots),
            'assigned_slots' => count($assignments),
            'coverage' => count($assignments) / count($slots) * 100,
            'fairness_score' => $this->calculateFairnessScore(array_values($currentDistribution)),
        ]);

        return $assignments;
    }

    /**
     * Generate all slots for the schedule
     */
    private function generateSlots(Schedule $schedule): array
    {
        $slots = [];
        $startDate = Carbon::parse($schedule->week_start_date);

        $sessionTimes = [
            1 => ['start' => '07:30:00', 'end' => '10:00:00'],
            2 => ['start' => '10:20:00', 'end' => '12:50:00'],
            3 => ['start' => '13:30:00', 'end' => '16:00:00'],
        ];

        for ($day = 0; $day < 4; $day++) {
            $date = $startDate->copy()->addDays($day);
            $dayName = strtolower($date->englishDayOfWeek);

            for ($session = 1; $session <= 3; $session++) {
                $slots[] = [
                    'date' => $date->toDateString(),
                    'day' => $dayName,
                    'session' => $session,
                    'time_start' => $sessionTimes[$session]['start'],
                    'time_end' => $sessionTimes[$session]['end'],
                ];
            }
        }

        return $slots;
    }

    /**
     * Get user availability data
     */
    private function getUserAvailability(Schedule $schedule): Collection
    {
        $weekStart = Carbon::parse($schedule->week_start_date);
        $weekEnd = Carbon::parse($schedule->week_end_date);

        $availabilities = Availability::where('status', 'submitted')
            ->whereBetween('submitted_at', [
                $weekStart->startOfDay(),
                $weekEnd->endOfDay()
            ])
            ->with(['user', 'details'])
            ->get();

        $userData = collect();

        foreach ($availabilities as $availability) {
            if (!$availability->user || $availability->user->status !== 'active') {
                continue;
            }

            $availableSlots = [];
            foreach ($availability->details as $detail) {
                if ($detail->is_available) {
                    $key = $detail->day . '_' . $detail->session;
                    $availableSlots[$key] = true;
                }
            }

            $userData->put($availability->user_id, [
                'user' => $availability->user,
                'available_slots' => $availableSlots,
                'availability_score' => count($availableSlots) / 12 * 100, // Percentage of total slots
            ]);
        }

        return $userData;
    }

    /**
     * Calculate optimal distribution of shifts per user
     */
    private function calculateOptimalDistribution(int $totalSlots, int $totalUsers): array
    {
        if ($totalUsers === 0) {
            return [];
        }

        $baseShifts = floor($totalSlots / $totalUsers);
        $remainder = $totalSlots % $totalUsers;

        $distribution = [];
        for ($i = 0; $i < $totalUsers; $i++) {
            // First $remainder users get one extra shift
            $distribution[] = $baseShifts + ($i < $remainder ? 1 : 0);
        }

        return $distribution;
    }

    /**
     * Select best user for a slot
     */
    private function selectBestUser(
        array $slot,
        Collection $availableUsers,
        array $currentDistribution,
        array $optimalDistribution
    ): ?User {
        $slotKey = $slot['day'] . '_' . $slot['session'];
        $candidates = [];

        foreach ($availableUsers as $userId => $userData) {
            // Check if user is available for this slot
            if (!isset($userData['available_slots'][$slotKey])) {
                continue;
            }

            // Check if user already has assignment at this time
            $hasConflict = ScheduleAssignment::where('user_id', $userId)
                ->where('date', $slot['date'])
                ->where('session', $slot['session'])
                ->exists();

            if ($hasConflict) {
                continue;
            }

            // Calculate scores
            $availabilityScore = $userData['availability_score'];
            
            // Fairness score: prefer users with fewer assignments
            $currentCount = $currentDistribution[$userId];
            $targetCount = $optimalDistribution[array_search($userId, array_keys($currentDistribution))] ?? 0;
            $fairnessScore = $targetCount > 0 
                ? (1 - ($currentCount / $targetCount)) * 100 
                : 100;

            // Combined score
            $totalScore = ($availabilityScore * $this->availabilityWeight) + 
                         ($fairnessScore * $this->fairnessWeight);

            $candidates[] = [
                'user' => $userData['user'],
                'score' => $totalScore,
                'availability_score' => $availabilityScore,
                'fairness_score' => $fairnessScore,
                'current_count' => $currentCount,
            ];
        }

        if (empty($candidates)) {
            return null;
        }

        // Sort by score (highest first)
        usort($candidates, function($a, $b) {
            return $b['score'] <=> $a['score'];
        });

        return $candidates[0]['user'];
    }

    /**
     * Calculate fairness score
     */
    private function calculateFairnessScore(array $distribution): float
    {
        if (empty($distribution)) {
            return 0;
        }

        $avg = array_sum($distribution) / count($distribution);
        
        if ($avg == 0) {
            return 100;
        }

        $variance = array_sum(array_map(function($x) use ($avg) {
            return pow($x - $avg, 2);
        }, $distribution)) / count($distribution);

        $stdDev = sqrt($variance);
        
        // Convert to score (lower std dev = higher score)
        // Perfect distribution (std dev = 0) = 100 score
        // High std dev (> 2) = low score
        $score = max(0, 100 - ($stdDev * 30));

        return round($score, 2);
    }

    /**
     * Preview auto-assignment without saving
     */
    public function previewAssignments(Schedule $schedule, array $options = []): array
    {
        $assignments = $this->generateAssignments($schedule, $options);

        // Calculate statistics
        $userCounts = [];
        foreach ($assignments as $assignment) {
            $userId = $assignment['user_id'];
            if (!isset($userCounts[$userId])) {
                $userCounts[$userId] = 0;
            }
            $userCounts[$userId]++;
        }

        $fairnessScore = $this->calculateFairnessScore(array_values($userCounts));

        return [
            'assignments' => $assignments,
            'statistics' => [
                'total_assignments' => count($assignments),
                'total_slots' => 12,
                'coverage_rate' => (count($assignments) / 12) * 100,
                'unique_users' => count($userCounts),
                'fairness_score' => $fairnessScore,
                'assignments_per_user' => $userCounts,
                'min_assignments' => !empty($userCounts) ? min($userCounts) : 0,
                'max_assignments' => !empty($userCounts) ? max($userCounts) : 0,
                'avg_assignments' => !empty($userCounts) ? array_sum($userCounts) / count($userCounts) : 0,
            ],
        ];
    }

    /**
     * Set custom weights
     */
    public function setWeights(float $fairnessWeight, float $availabilityWeight): void
    {
        // Ensure weights sum to 1.0
        $total = $fairnessWeight + $availabilityWeight;
        $this->fairnessWeight = $fairnessWeight / $total;
        $this->availabilityWeight = $availabilityWeight / $total;
    }
}
