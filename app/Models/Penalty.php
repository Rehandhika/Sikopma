<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Penalty extends Model
{
    use \Illuminate\Database\Eloquent\Factories\HasFactory, \App\Traits\Auditable;

    protected $fillable = [
        'user_id',
        'penalty_type_id',
        'reference_type',
        'reference_id',
        'points',
        'description',
        'date',
        'status',
        'appeal_reason',
        'appeal_status',
        'appealed_at',
        'reviewed_by',
        'reviewed_at',
        'review_notes',
    ];

    protected $casts = [
        'date' => 'date',
        'appealed_at' => 'datetime',
        'reviewed_at' => 'datetime',
        'points' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function penaltyType(): BelongsTo
    {
        return $this->belongsTo(PenaltyType::class);
    }

    /**
     * Get the reviewer who reviewed this penalty
     */
    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    /**
     * Get the owning reference model (polymorphic relationship)
     */
    public function reference(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Scope for active penalties
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope for resolved penalties
     */
    public function scopeResolved($query)
    {
        return $query->where('status', 'resolved');
    }

    /**
     * Scope for appealed penalties
     */
    public function scopeAppealed($query)
    {
        return $query->whereNotNull('appealed_at');
    }

    /**
     * Scope for pending appeal
     */
    public function scopePendingAppeal($query)
    {
        return $query->where('appeal_status', 'pending');
    }

    /**
     * Scope to filter by date range.
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('date', [$startDate, $endDate]);
    }

    /**
     * Scope to eager load user relationship.
     */
    public function scopeWithUser($query)
    {
        return $query->with(['user']);
    }

    /**
     * Scope to eager load penalty type.
     */
    public function scopeWithType($query)
    {
        return $query->with(['penaltyType']);
    }

    /**
     * Scope to filter by user ID.
     */
    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope to order by date descending.
     */
    public function scopeLatest($query)
    {
        return $query->orderByDesc('date');
    }

    /**
     * Scope to filter by high points (> 50).
     */
    public function scopeHighPoints($query)
    {
        return $query->where('points', '>', 50);
    }

    /**
     * Check if penalty is active
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Check if penalty has been appealed
     */
    public function isAppealed(): bool
    {
        return ! is_null($this->appealed_at);
    }

    /**
     * Resolve this penalty
     */
    public function resolve(): void
    {
        $this->update(['status' => 'resolved']);
    }
}
