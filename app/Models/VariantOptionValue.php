<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class VariantOptionValue extends Model
{
    protected $fillable = ['variant_option_id', 'value', 'slug', 'display_order'];

    protected $casts = [
        'display_order' => 'integer',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($value) {
            if (empty($value->slug)) {
                $value->slug = Str::slug($value->value);
            }
        });
    }

    public function option(): BelongsTo
    {
        return $this->belongsTo(VariantOption::class, 'variant_option_id');
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('display_order')->orderBy('value');
    }
}
