<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * SwapRequest Model
 * Handles swap requests between two users to exchange their schedule assignments
 */
class SwapRequest extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'swap_requests';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'requester_id',
        'target_id',
        'requester_assignment_id',
        'target_assignment_id',
        'reason',
    ];

    /**
     * The attributes that should be guarded from mass assignment.
     */
    protected $guarded = [
        'status',
        'target_response',
        'target_responded_at',
        'admin_response',
        'admin_responded_by',
        'admin_responded_at',
        'completed_at',
    ];

    protected $casts = [
        'target_responded_at' => 'datetime',
        'admin_responded_at' => 'datetime',
        'completed_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    // Relationships
    public function requester()
    {
        return $this->belongsTo(User::class, 'requester_id');
    }

    public function target()
    {
        return $this->belongsTo(User::class, 'target_id');
    }

    public function requesterAssignment()
    {
        return $this->belongsTo(ScheduleAssignment::class, 'requester_assignment_id');
    }

    public function targetAssignment()
    {
        return $this->belongsTo(ScheduleAssignment::class, 'target_assignment_id');
    }

    public function adminResponder()
    {
        return $this->belongsTo(User::class, 'admin_responded_by');
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeTargetApproved($query)
    {
        return $query->where('status', 'target_approved');
    }

    public function scopeAdminApproved($query)
    {
        return $query->where('status', 'admin_approved');
    }

    public function scopeRejected($query)
    {
        return $query->whereIn('status', ['target_rejected', 'admin_rejected']);
    }

    public function scopeCancelled($query)
    {
        return $query->where('status', 'cancelled');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'admin_approved');
    }

    public function scopeThisMonth($query)
    {
        return $query->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year);
    }

    // Helpers
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isTargetApproved(): bool
    {
        return $this->status === 'target_approved';
    }

    public function isAdminApproved(): bool
    {
        return $this->status === 'admin_approved';
    }

    public function isRejected(): bool
    {
        return in_array($this->status, ['target_rejected', 'admin_rejected', 'cancelled']);
    }

    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    public function canCancel(): bool
    {
        return $this->status === 'pending';
    }

    public function canTargetRespond(): bool
    {
        return $this->status === 'pending';
    }

    public function canAdminApprove(): bool
    {
        return $this->status === 'target_approved';
    }

    public function canAdminReject(): bool
    {
        return in_array($this->status, ['pending', 'target_approved']);
    }

    public function getStatusLabel(): string
    {
        return match ($this->status) {
            'pending' => 'Menunggu Persetujuan Target',
            'target_approved' => 'Disetujui Target, Menunggu Admin',
            'target_rejected' => 'Ditolak Target',
            'admin_approved' => 'Disetujui Admin',
            'admin_rejected' => 'Ditolak Admin',
            'cancelled' => 'Dibatalkan',
            default => ucfirst($this->status),
        };
    }

    public function getStatusColor(): string
    {
        return match ($this->status) {
            'pending' => 'warning',
            'target_approved' => 'info',
            'admin_approved' => 'success',
            'target_rejected', 'admin_rejected' => 'danger',
            'cancelled' => 'secondary',
            default => 'secondary',
        };
    }

    /**
     * Check if this swap request is within the allowed time window
     */
    public function isWithinTimeWindow(): bool
    {
        if (!$this->requesterAssignment) {
            return false;
        }

        $minNoticeHours = config('schedule-change.swap.min_notice_hours', 24);
        
        $assignmentDateTime = $this->requesterAssignment->date
            ->copy()
            ->setTimeFromTimeString($this->requesterAssignment->time_start);

        $deadline = $assignmentDateTime->subHours($minNoticeHours);

        return now()->lte($deadline);
    }
}
