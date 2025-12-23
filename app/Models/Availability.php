<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Availability extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'schedule_id',
        'status',
        'submitted_at',
        'total_available_sessions',
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function schedule()
    {
        return $this->belongsTo(Schedule::class);
    }

    public function details()
    {
        return $this->hasMany(AvailabilityDetail::class);
    }

    // Scopes
    public function scopeSubmitted($query)
    {
        return $query->where('status', 'submitted');
    }

    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    // Helpers
    public function isSubmitted(): bool
    {
        return $this->status === 'submitted';
    }

    public function canEdit(): bool
    {
        return $this->status === 'draft';
    }

    public function meetsMinimumRequirement(): bool
    {
        return $this->total_available_sessions >= 6;
    }
}
