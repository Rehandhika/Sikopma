<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivityLog extends Model
{
    use HasFactory;

    const UPDATED_AT = null; // Activity logs don't need updated_at

    protected $fillable = [
        'user_id',
        'activity',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    /**
     * Get the user who performed the activity
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope for recent activity logs
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  int  $days  Number of days to look back (default: 90)
     */
    public function scopeRecent($query, int $days = 90)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    /**
     * Scope for filtering by specific user
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     */
    public function scopeByUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope for searching activity description
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $term  Search term
     */
    public function scopeSearch($query, string $term)
    {
        return $query->where('activity', 'like', '%'.$term.'%');
    }

    /**
     * Scope for filtering by date range
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  mixed  $startDate
     * @param  mixed  $endDate
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        if ($startDate) {
            $query->where('created_at', '>=', $startDate);
        }

        if ($endDate) {
            $query->where('created_at', '<=', $endDate);
        }

        return $query;
    }
}
