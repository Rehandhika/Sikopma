<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AvailabilityDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'availability_id',
        'day',
        'session',
        'is_available',
    ];

    protected $casts = [
        'is_available' => 'boolean',
    ];

    /**
     * Get the availability that owns this detail
     */
    public function availability(): BelongsTo
    {
        return $this->belongsTo(Availability::class);
    }

    /**
     * Scope for available sessions
     */
    public function scopeAvailable($query)
    {
        return $query->where('is_available', true);
    }

    /**
     * Scope for unavailable sessions
     */
    public function scopeUnavailable($query)
    {
        return $query->where('is_available', false);
    }

    /**
     * Get formatted day label
     */
    public function getDayLabelAttribute(): string
    {
        return match($this->day) {
            'monday' => 'Senin',
            'tuesday' => 'Selasa',
            'wednesday' => 'Rabu',
            'thursday' => 'Kamis',
            'friday' => 'Jumat',
            'saturday' => 'Sabtu',
            'sunday' => 'Minggu',
            default => $this->day,
        };
    }

    /**
     * Get formatted session label
     */
    public function getSessionLabelAttribute(): string
    {
        return match($this->session) {
            '1' => '08:00 - 12:00',
            '2' => '13:00 - 17:00',
            '3' => '17:00 - 21:00',
            default => 'Unknown',
        };
    }
}
