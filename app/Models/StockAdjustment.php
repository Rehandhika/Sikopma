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
        return $query->where('type', 'in');
    }

    public function scopeReductions($query)
    {
        return $query->where('type', 'out');
    }

    public function scopeRecent($query)
    {
        return $query->orderBy('created_at', 'desc');
    }

    public function scopeByProduct($query, int $productId)
    {
        return $query->where('product_id', $productId);
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

    // Helpers
    public function isAddition(): bool
    {
        return $this->type === 'in';
    }

    public function isReduction(): bool
    {
        return $this->type === 'out';
    }

    public function getTypeLabel(): string
    {
        return match($this->type) {
            'in' => 'Masuk',
            'out' => 'Keluar',
            default => $this->type,
        };
    }
}
