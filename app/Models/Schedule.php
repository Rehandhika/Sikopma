<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Schedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'week_start_date',
        'week_end_date',
        'status',
        'generated_by',
        'generated_at',
        'published_at',
        'published_by',
        'total_slots',
        'filled_slots',
        'coverage_rate',
        'notes',
    ];

    protected $casts = [
        'week_start_date' => 'date',
        'week_end_date' => 'date',
        'generated_at' => 'datetime',
        'published_at' => 'datetime',
    ];

    // Relationships
    public function generatedBy()
    {
        return $this->belongsTo(User::class, 'generated_by');
    }

    public function publishedBy()
    {
        return $this->belongsTo(User::class, 'published_by');
    }

    public function availabilities()
    {
        return $this->hasMany(Availability::class);
    }

    public function assignments()
    {
        return $this->hasMany(ScheduleAssignment::class);
    }

    public function histories()
    {
        return $this->hasMany(AssignmentHistory::class);
    }

    // Scopes
    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    public function scopeCurrentWeek($query)
    {
        $monday = Carbon::now()->startOfWeek();
        return $query->where('week_start_date', $monday->toDateString());
    }

    // Helpers
    public function isPublished(): bool
    {
        return $this->status === 'published';
    }

    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    public function canEdit(): bool
    {
        return $this->status === 'draft';
    }

    /**
     * Get assignment grid (4 days × 3 sessions)
     */
    public function getAssignmentGrid(): array
    {
        $assignments = $this->assignments()
            ->with('user:id,name,photo')
            ->get()
            ->groupBy(function($assignment) {
                return $assignment->date->format('Y-m-d') . '_' . $assignment->session;
            });

        $grid = [];
        $startDate = Carbon::parse($this->week_start_date);
        
        for ($day = 0; $day < 4; $day++) {
            $date = $startDate->copy()->addDays($day);
            $dateStr = $date->format('Y-m-d');
            
            for ($session = 1; $session <= 3; $session++) {
                $key = $dateStr . '_' . $session;
                $grid[$dateStr][$session] = $assignments->get($key, collect())->first();
            }
        }

        return $grid;
    }

    /**
     * Calculate and update coverage rate
     */
    public function calculateCoverage(): float
    {
        $this->filled_slots = $this->assignments()->count();
        $this->coverage_rate = $this->total_slots > 0 
            ? ($this->filled_slots / $this->total_slots) * 100 
            : 0;
        
        $this->save();
        
        return $this->coverage_rate;
    }

    /**
     * Check if schedule can be published
     */
    public function canPublish(): bool
    {
        if (!$this->isDraft()) {
            return false;
        }

        // Check minimum coverage (at least 50%)
        if ($this->coverage_rate < 50) {
            return false;
        }

        // Check for conflicts
        $conflicts = $this->detectConflicts();
        if (!empty($conflicts['critical'])) {
            return false;
        }

        return true;
    }

    /**
     * Detect conflicts in assignments
     */
    public function detectConflicts(): array
    {
        $conflicts = [
            'critical' => [],
            'warning' => [],
            'info' => [],
        ];

        // Check for double assignments
        $doubleAssignments = $this->assignments()
            ->select('user_id', 'date', 'session')
            ->groupBy('user_id', 'date', 'session')
            ->havingRaw('COUNT(*) > 1')
            ->get();

        if ($doubleAssignments->isNotEmpty()) {
            $conflicts['critical'][] = [
                'type' => 'double_assignment',
                'message' => 'Terdapat anggota dengan assignment ganda',
                'count' => $doubleAssignments->count(),
            ];
        }

        // Check for inactive users
        $inactiveUsers = $this->assignments()
            ->whereHas('user', function($query) {
                $query->where('status', '!=', 'active');
            })
            ->count();

        if ($inactiveUsers > 0) {
            $conflicts['critical'][] = [
                'type' => 'inactive_users',
                'message' => 'Terdapat assignment untuk user yang tidak aktif',
                'count' => $inactiveUsers,
            ];
        }

        return $conflicts;
    }

    /**
     * Get statistics
     */
    public function getStatistics(): array
    {
        $assignments = $this->assignments()->with('user')->get();
        
        $userCounts = $assignments->groupBy('user_id')->map(function($group) {
            return [
                'user' => $group->first()->user,
                'count' => $group->count(),
            ];
        })->sortByDesc('count');

        $sessionCounts = $assignments->groupBy('session')->map->count();

        return [
            'total_assignments' => $assignments->count(),
            'coverage_rate' => $this->coverage_rate,
            'unique_users' => $assignments->pluck('user_id')->unique()->count(),
            'assignments_per_user' => $userCounts->values()->toArray(),
            'assignments_per_session' => $sessionCounts->toArray(),
            'unassigned_slots' => $this->total_slots - $this->filled_slots,
        ];
    }
}
