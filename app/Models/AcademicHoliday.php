<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class AcademicHoliday extends Model
{
    protected $fillable = [
        'name',
        'start_date',
        'end_date',
        'description',
        'is_active',
        'created_by',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_active' => 'boolean',
    ];

    // Relationships
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Scopes
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeCurrent(Builder $query): Builder
    {
        $today = Carbon::today();

        return $query->where('start_date', '<=', $today)
            ->where('end_date', '>=', $today);
    }

    public function scopeUpcoming(Builder $query): Builder
    {
        return $query->where('start_date', '>', Carbon::today())
            ->orderBy('start_date');
    }

    public function scopeInRange(Builder $query, Carbon $date): Builder
    {
        return $query->where('start_date', '<=', $date)
            ->where('end_date', '>=', $date);
    }

    // Helpers
    public function isCurrentlyActive(): bool
    {
        $today = Carbon::today();

        return $this->is_active
            && $this->start_date->lte($today)
            && $this->end_date->gte($today);
    }

    public function getDurationInDays(): int
    {
        return $this->start_date->diffInDays($this->end_date) + 1;
    }

    public function getFormattedPeriod(): string
    {
        if ($this->start_date->isSameDay($this->end_date)) {
            return $this->start_date->locale('id')->isoFormat('D MMMM YYYY');
        }

        if ($this->start_date->isSameMonth($this->end_date)) {
            return $this->start_date->locale('id')->isoFormat('D').' - '.
                   $this->end_date->locale('id')->isoFormat('D MMMM YYYY');
        }

        return $this->start_date->locale('id')->isoFormat('D MMMM').' - '.
               $this->end_date->locale('id')->isoFormat('D MMMM YYYY');
    }
}
