<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory, \App\Traits\Auditable;

    protected $fillable = [
        'user_id',
        'schedule_assignment_id',
        'date',
        'check_in',
        'check_out',
        'work_hours',
        'status',
        'late_minutes',
        'late_category',
        'notes',
    ];

    protected $casts = [
        'date' => 'date',
        // FIX: Database menggunakan tipe 'time', bukan 'datetime'
        // Cast sebagai datetime untuk kompatibilitas dengan kode existing
        'check_in' => 'datetime',
        'check_out' => 'datetime',
        'work_hours' => 'decimal:2',
        'late_minutes' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function scheduleAssignment()
    {
        return $this->belongsTo(ScheduleAssignment::class);
    }

    /**
     * Get all of the attendance's penalties.
     */
    public function penalties()
    {
        return $this->morphMany(Penalty::class, 'reference');
    }

    public function scopePresent($query)
    {
        return $query->where('status', 'present');
    }

    public function scopeLate($query)
    {
        return $query->where('status', 'late');
    }

    public function scopeAbsent($query)
    {
        return $query->where('status', 'absent');
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
     * Scope to filter by today's date.
     */
    public function scopeToday($query)
    {
        return $query->where('date', today());
    }

    /**
     * Scope to filter by this month.
     */
    public function scopeThisMonth($query)
    {
        return $query->whereMonth('date', now()->month)
            ->whereYear('date', now()->year);
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
}
