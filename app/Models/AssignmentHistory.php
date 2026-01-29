<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class AssignmentHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'schedule_id',
        'action',
        'assignment_data',
        'performed_by',
        'performed_at',
    ];

    protected $casts = [
        'assignment_data' => 'array',
        'performed_at' => 'datetime',
    ];

    /**
     * Relationship: Schedule
     */
    public function schedule()
    {
        return $this->belongsTo(Schedule::class);
    }

    /**
     * Relationship: User who performed the action
     */
    public function performer()
    {
        return $this->belongsTo(User::class, 'performed_by');
    }

    /**
     * Scope: Recent history
     */
    public function scopeRecent($query, int $limit = 20)
    {
        return $query->orderBy('performed_at', 'desc')->limit($limit);
    }

    /**
     * Scope: By schedule
     */
    public function scopeForSchedule($query, int $scheduleId)
    {
        return $query->where('schedule_id', $scheduleId);
    }

    /**
     * Scope: By action type
     */
    public function scopeByAction($query, string $action)
    {
        return $query->where('action', $action);
    }

    /**
     * Revert to this history state
     */
    public function revert(): bool
    {
        try {
            DB::beginTransaction();

            $data = $this->assignment_data;

            switch ($this->action) {
                case 'create':
                    // If this was a create action, delete the assignment
                    if (isset($data['id'])) {
                        ScheduleAssignment::find($data['id'])?->delete();
                    }
                    break;

                case 'delete':
                    // If this was a delete action, recreate the assignment
                    ScheduleAssignment::create($data);
                    break;

                case 'update':
                    // If this was an update action, restore previous state
                    if (isset($data['id'])) {
                        ScheduleAssignment::find($data['id'])?->update($data);
                    }
                    break;
            }

            DB::commit();

            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Failed to revert assignment history', [
                'history_id' => $this->id,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Get action label
     */
    public function getActionLabelAttribute(): string
    {
        return match ($this->action) {
            'create' => 'Ditambahkan',
            'update' => 'Diubah',
            'delete' => 'Dihapus',
            default => $this->action,
        };
    }

    /**
     * Get summary of the change
     */
    public function getSummary(): string
    {
        $data = $this->assignment_data;
        $user = User::find($data['user_id'] ?? null);
        $userName = $user ? $user->name : 'Unknown';

        $date = $data['date'] ?? 'Unknown date';
        $session = $data['session'] ?? 'Unknown session';

        return "{$this->action_label}: {$userName} - {$date} Sesi {$session}";
    }
}
