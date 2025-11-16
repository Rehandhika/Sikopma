<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PenaltyType extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'description',
        'points',
        'is_active',
    ];

    protected $casts = [
        'points' => 'integer',
        'is_active' => 'boolean',
    ];

    /**
     * Get all penalties of this type
     */
    public function penalties(): HasMany
    {
        return $this->hasMany(Penalty::class);
    }

    /**
     * Scope for active penalty types
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for inactive penalty types
     */
    public function scopeInactive($query)
    {
        return $query->where('is_active', false);
    }

    /**
     * Scope to order by points (highest first)
     */
    public function scopeOrderByPoints($query, $direction = 'desc')
    {
        return $query->orderBy('points', $direction);
    }

    /**
     * Check if this penalty type is active
     */
    public function isActive(): bool
    {
        return $this->is_active === true;
    }

    /**
     * Activate this penalty type
     */
    public function activate(): void
    {
        $this->update(['is_active' => true]);
    }

    /**
     * Deactivate this penalty type
     */
    public function deactivate(): void
    {
        $this->update(['is_active' => false]);
    }
}
