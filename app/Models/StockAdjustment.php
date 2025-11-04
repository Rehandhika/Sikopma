<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class StockAdjustment extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'product_id',
        'type',
        'quantity',
        'previous_stock',
        'new_stock',
        'reason',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'previous_stock' => 'integer',
        'new_stock' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    // Scopes
    public function scopeAdditions($query)
    {
        return $query->where('type', 'addition');
    }

    public function scopeReductions($query)
    {
        return $query->where('type', 'reduction');
    }

    public function scopeRecent($query)
    {
        return $query->orderBy('created_at', 'desc');
    }

    // Helpers
    public function isAddition(): bool
    {
        return $this->type === 'addition';
    }

    public function isReduction(): bool
    {
        return $this->type === 'reduction';
    }
}
