<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SwapRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'requester_id',
        'target_id',
        'requester_assignment_id',
        'target_assignment_id',
        'reason',
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
    ];

    /**
     * Get the user who requested the swap
     */
    public function requester()
    {
        return $this->belongsTo(User::class, 'requester_id');
    }

    /**
     * Get the target user for the swap
     */
    public function target()
    {
        return $this->belongsTo(User::class, 'target_id');
    }

    /**
     * Get the requester's schedule assignment
     */
    public function requesterAssignment()
    {
        return $this->belongsTo(ScheduleAssignment::class, 'requester_assignment_id');
    }

    /**
     * Get the target's schedule assignment
     */
    public function targetAssignment()
    {
        return $this->belongsTo(ScheduleAssignment::class, 'target_assignment_id');
    }

    /**
     * Get the admin who responded
     */
    public function adminResponder()
    {
        return $this->belongsTo(User::class, 'admin_responded_by');
    }

    /**
     * Scope for pending requests
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope for target approved requests
     */
    public function scopeTargetApproved($query)
    {
        return $query->where('status', 'target_approved');
    }

    /**
     * Scope for admin approved requests
     */
    public function scopeAdminApproved($query)
    {
        return $query->where('status', 'admin_approved');
    }

    /**
     * Scope for rejected requests
     */
    public function scopeRejected($query)
    {
        return $query->whereIn('status', ['target_rejected', 'admin_rejected']);
    }

    /**
     * Scope for completed requests
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'admin_approved')
                     ->whereNotNull('completed_at');
    }

    /**
     * Check if request is pending
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Check if request is approved by target
     */
    public function isTargetApproved(): bool
    {
        return $this->status === 'target_approved';
    }

    /**
     * Check if request is approved by admin
     */
    public function isAdminApproved(): bool
    {
        return $this->status === 'admin_approved';
    }

    /**
     * Check if request is rejected
     */
    public function isRejected(): bool
    {
        return in_array($this->status, ['target_rejected', 'admin_rejected', 'cancelled']);
    }
}
