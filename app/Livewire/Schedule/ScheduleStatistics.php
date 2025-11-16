<?php

namespace App\Livewire\Schedule;

use Livewire\Component;

class ScheduleStatistics extends Component
{
    // Props from parent
    public $totalAssignments = 0;
    public $coverageRate = 0;
    public $assignmentsPerUser = [];
    public $conflicts = [];
    public $totalSlots = 12; // 4 days x 3 sessions
    
    // Computed statistics
    public $unassignedSlots = 0;
    public $fairnessScore = 0;
    public $distributionPerSession = [];
    
    protected $listeners = ['statisticsUpdated' => 'updateStatistics'];

    public function mount($totalAssignments = 0, $coverageRate = 0, $assignmentsPerUser = [], $conflicts = [])
    {
        $this->totalAssignments = $totalAssignments;
        $this->coverageRate = $coverageRate;
        $this->assignmentsPerUser = $assignmentsPerUser;
        $this->conflicts = $conflicts;
        
        $this->computeStatistics();
    }

    /**
     * Update statistics from parent
     */
    public function updateStatistics($data): void
    {
        $this->totalAssignments = $data['totalAssignments'] ?? 0;
        $this->coverageRate = $data['coverageRate'] ?? 0;
        $this->assignmentsPerUser = $data['assignmentsPerUser'] ?? [];
        $this->conflicts = $data['conflicts'] ?? [];
        
        $this->computeStatistics();
    }

    /**
     * Compute additional statistics
     */
    private function computeStatistics(): void
    {
        // Calculate unassigned slots
        $this->unassignedSlots = $this->totalSlots - $this->totalAssignments;
        
        // Calculate fairness score (based on standard deviation)
        $this->fairnessScore = $this->calculateFairnessScore();
    }

    /**
     * Calculate fairness score
     * Lower standard deviation = more fair distribution = higher score
     */
    private function calculateFairnessScore(): float
    {
        if (empty($this->assignmentsPerUser)) {
            return 0;
        }
        
        $counts = array_column($this->assignmentsPerUser, 'count');
        
        if (count($counts) === 0) {
            return 0;
        }
        
        // Calculate mean
        $mean = array_sum($counts) / count($counts);
        
        // Calculate standard deviation
        $variance = 0;
        foreach ($counts as $count) {
            $variance += pow($count - $mean, 2);
        }
        $variance = $variance / count($counts);
        $stdDev = sqrt($variance);
        
        // Convert to score (0-100)
        // Lower std dev = higher score
        // Assuming max reasonable std dev is 2 (very unfair)
        $maxStdDev = 2;
        $score = max(0, 100 - ($stdDev / $maxStdDev * 100));
        
        return round($score, 1);
    }

    /**
     * Get coverage status color
     */
    public function getCoverageColor(): string
    {
        if ($this->coverageRate >= 80) {
            return 'green';
        } elseif ($this->coverageRate >= 50) {
            return 'yellow';
        } else {
            return 'red';
        }
    }

    /**
     * Get fairness status color
     */
    public function getFairnessColor(): string
    {
        if ($this->fairnessScore >= 80) {
            return 'green';
        } elseif ($this->fairnessScore >= 60) {
            return 'yellow';
        } else {
            return 'red';
        }
    }

    /**
     * Get conflict count by severity
     */
    public function getConflictCount(string $severity): int
    {
        return count($this->conflicts[$severity] ?? []);
    }

    /**
     * Get total conflict count
     */
    public function getTotalConflicts(): int
    {
        return $this->getConflictCount('critical') + 
               $this->getConflictCount('warning') + 
               $this->getConflictCount('info');
    }

    /**
     * Get max assignments for chart scaling
     */
    public function getMaxAssignments(): int
    {
        if (empty($this->assignmentsPerUser)) {
            return 1;
        }
        
        return max(array_column($this->assignmentsPerUser, 'count'));
    }

    /**
     * Get bar width percentage for user
     */
    public function getBarWidth(int $count): float
    {
        $max = $this->getMaxAssignments();
        if ($max === 0) {
            return 0;
        }
        
        return ($count / $max) * 100;
    }

    /**
     * Get bar color based on assignment count
     */
    public function getBarColor(int $count): string
    {
        $avg = $this->totalAssignments > 0 ? $this->totalAssignments / max(1, count($this->assignmentsPerUser)) : 0;
        
        if ($count > $avg * 1.5) {
            return 'bg-red-500'; // Overloaded
        } elseif ($count > $avg * 1.2) {
            return 'bg-yellow-500'; // Slightly overloaded
        } elseif ($count < $avg * 0.5) {
            return 'bg-blue-300'; // Underloaded
        } else {
            return 'bg-green-500'; // Balanced
        }
    }

    public function render()
    {
        return view('livewire.schedule.schedule-statistics');
    }
}
