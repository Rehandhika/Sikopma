<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeaveAffectedSchedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'leave_request_id',
        'schedule_assignment_id',
        'replacement_user_id',
    ];

    /**
     * Get the leave request that owns this affected schedule
     */
    public function leaveRequest(): BelongsTo
    {
        return $this->belongsTo(LeaveRequest::class);
    }

    /**
     * Get the schedule assignment that is affected
     */
    public function scheduleAssignment(): BelongsTo
    {
        return $this->belongsTo(ScheduleAssignment::class);
    }

    /**
     * Get the replacement user
     */
    public function replacementUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'replacement_user_id');
    }

    /**
     * Check if a replacement has been assigned
     */
    public function hasReplacement(): bool
    {
        return !is_null($this->replacement_user_id);
    }
}
