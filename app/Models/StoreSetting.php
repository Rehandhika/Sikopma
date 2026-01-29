<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StoreSetting extends Model
{
    protected $fillable = [
        'is_open',
        'status_reason',
        'last_status_change',
        'auto_status',
        'manual_mode',
        'manual_is_open',
        'manual_close_reason',
        'manual_close_until',
        'manual_open_override',
        'manual_set_by',
        'manual_set_at',
        'operating_hours',
        'next_open_mode',
        'custom_closed_message',
        'custom_next_open_date',
        'academic_holiday_start',
        'academic_holiday_end',
        'academic_holiday_name',
        'contact_phone',
        'contact_email',
        'contact_address',
        'contact_whatsapp',
        'about_text',
    ];

    protected $casts = [
        'is_open' => 'boolean',
        'auto_status' => 'boolean',
        'manual_mode' => 'boolean',
        'manual_is_open' => 'boolean',
        'manual_open_override' => 'boolean',
        'last_status_change' => 'datetime',
        'manual_close_until' => 'datetime',
        'manual_set_at' => 'datetime',
        'operating_hours' => 'array',
        'custom_next_open_date' => 'date',
        'academic_holiday_start' => 'date',
        'academic_holiday_end' => 'date',
    ];

    // Constants for next_open_mode
    const MODE_DEFAULT = 'default';

    const MODE_CUSTOM = 'custom';

    // Check if currently in academic holiday
    public function isInAcademicHoliday(): bool
    {
        if (! $this->academic_holiday_start || ! $this->academic_holiday_end) {
            return false;
        }

        $today = now()->startOfDay();

        return $today->between($this->academic_holiday_start, $this->academic_holiday_end);
    }

    // Get active academic holiday from holidays table
    public function getActiveAcademicHoliday(): ?\App\Models\AcademicHoliday
    {
        return \App\Models\AcademicHoliday::active()->current()->first();
    }

    // Relationship
    public function manualSetBy()
    {
        return $this->belongsTo(User::class, 'manual_set_by');
    }
}
