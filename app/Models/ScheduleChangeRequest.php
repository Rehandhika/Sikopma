<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ScheduleChangeRequest extends Model
{
    use HasFactory;

    protected $table = 'schedule_change_requests';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'user_id',
        'original_assignment_id',
        'requested_date',
        'requested_session',
        'change_type',
        'reason',
    ];

    /**
     * The attributes that should be guarded from mass assignment.
     */
    protected $guarded = [
        'status',
        'admin_response',
        'admin_responded_by',
        'admin_responded_at',
        'completed_at',
    ];

    protected $casts = [
        'requested_date' => 'date',
        'admin_responded_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function originalAssignment()
    {
        return $this->belongsTo(ScheduleAssignment::class, 'original_assignment_id')->withTrashed();
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

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    public function scopeCancelled($query)
    {
        return $query->where('status', 'cancelled');
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('change_type', $type);
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

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    public function canCancel(): bool
    {
        return $this->status === 'pending';
    }

    public function canApprove(): bool
    {
        return $this->status === 'pending';
    }

    public function canReject(): bool
    {
        return $this->status === 'pending';
    }

    public function isReschedule(): bool
    {
        return $this->change_type === 'reschedule';
    }

    public function isCancelType(): bool
    {
        return $this->change_type === 'cancel';
    }

    public function getChangeTypeLabel(): string
    {
        return match ($this->change_type) {
            'reschedule' => 'Pindah Jadwal',
            'cancel' => 'Batalkan Jadwal',
            default => $this->change_type,
        };
    }

    public function getStatusLabel(): string
    {
        return match ($this->status) {
            'pending' => 'Menunggu Persetujuan',
            'approved' => 'Disetujui',
            'rejected' => 'Ditolak',
            'cancelled' => 'Dibatalkan',
            default => ucfirst($this->status),
        };
    }

    public function getStatusColor(): string
    {
        return match ($this->status) {
            'pending' => 'warning',
            'approved' => 'success',
            'rejected' => 'danger',
            'cancelled' => 'secondary',
            default => 'secondary',
        };
    }

    public function getSessionLabel(?int $session = null): string
    {
        $s = $session ?? $this->requested_session;

        return match ($s) {
            1 => 'Sesi 1 (07:30-10:00)',
            2 => 'Sesi 2 (10:20-12:50)',
            3 => 'Sesi 3 (13:30-16:00)',
            default => '-',
        };
    }

    /**
     * Get the minimum notice hours required for this change type
     */
    public function getMinNoticeHours(): int
    {
        return match ($this->change_type) {
            'reschedule' => config('schedule-change.reschedule.min_notice_hours', 3),
            'cancel' => config('schedule-change.cancel.min_notice_hours', 24),
            default => 24,
        };
    }

    /**
     * Check if this request is within the allowed time window
     */
    public function isWithinTimeWindow(): bool
    {
        if (! $this->originalAssignment) {
            return false;
        }

        $assignmentDateTime = $this->originalAssignment->date
            ->copy()
            ->setTimeFromTimeString($this->originalAssignment->time_start);

        $deadline = $assignmentDateTime->subHours($this->getMinNoticeHours());

        return now()->lte($deadline);
    }
}
