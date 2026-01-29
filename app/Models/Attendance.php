<?php

namespace App\Models;

use App\Services\ThumbnailService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'schedule_assignment_id',
        'date',
        'check_in',
        'check_in_photo',
        'check_out',
        'work_hours',
        'status',
        'notes',
    ];

    protected $casts = [
        'date' => 'date',
        'check_in' => 'datetime',
        'check_out' => 'datetime',
        'work_hours' => 'decimal:2',
    ];

    protected $appends = [
        'check_in_photo_url',
    ];

    /**
     * Get the full URL for check-in photo
     */
    public function getCheckInPhotoUrlAttribute(): ?string
    {
        if (! $this->check_in_photo) {
            return null;
        }

        return \Storage::url($this->check_in_photo);
    }

    /**
     * Get optimized WebP thumbnail URL for check-in photo
     * Used in admin list views for faster loading
     */
    public function getCheckInPhotoThumbnailAttribute(): ?string
    {
        if (! $this->check_in_photo) {
            return null;
        }

        try {
            return ThumbnailService::getThumbnailUrl($this->check_in_photo, 80, 80);
        } catch (\Exception $e) {
            return $this->check_in_photo_url;
        }
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function scheduleAssignment()
    {
        return $this->belongsTo(ScheduleAssignment::class);
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
}
