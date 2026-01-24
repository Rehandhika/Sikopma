<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
use App\Models\StockAdjustment;

class ProductVariant extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'product_id',
        'sku',
        'variant_name',
        'price',
        'cost_price',
        'stock',
        'min_stock',
        'option_values',
        'is_active',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'cost_price' => 'decimal:2',
        'stock' => 'integer',
        'min_stock' => 'integer',
        'option_values' => 'array',
        'is_active' => 'boolean',
    ];

    protected $appends = ['profit_margin', 'profit_per_unit'];

    /**
     * Boot method for cache invalidation and stock sync
     */
    protected static function boot()
    {
        parent::boot();

        // Invalidate product cache and sync stock when variant is saved (created/updated)
        static::saved(function ($variant) {
            if ($variant->product) {
                $variant->product->invalidateVariantCache();
                
                // Auto-sync product total stock when variant stock changes
                if ($variant->isDirty('stock') || $variant->wasRecentlyCreated) {
                    static::syncProductStock($variant->product);
                }
            }
        });

        // Invalidate product cache and sync stock when variant is deleted
        static::deleted(function ($variant) {
            if ($variant->product) {
                $variant->product->invalidateVariantCache();
                static::syncProductStock($variant->product);
            }
        });
    }

    /**
     * Sync product total stock from all active variants
     * Called automatically when variant stock changes
     */
    protected static function syncProductStock(Product $product): void
    {
        if (!$product->has_variants) {
            return;
        }

        $totalStock = static::where('product_id', $product->id)
            ->where('is_active', true)
            ->sum('stock');

        // Use query builder to avoid triggering model events
        DB::table('products')
            ->where('id', $product->id)
            ->update(['stock' => $totalStock, 'updated_at' => now()]);
    }

    // Relationships
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function saleItems(): HasMany
    {
        return $this->hasMany(SaleItem::class, 'variant_id');
    }

    /**
     * Stock adjustments for this variant
     * Requirements: 6.4
     */
    public function stockAdjustments(): HasMany
    {
        return $this->hasMany(StockAdjustment::class, 'variant_id');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeInStock($query)
    {
        return $query->where('stock', '>', 0);
    }

    public function scopeLowStock($query)
    {
        return $query->whereColumn('stock', '<=', 'min_stock');
    }

    // Helpers
    public function isLowStock(): bool
    {
        return $this->stock <= $this->min_stock;
    }

    public function isOutOfStock(): bool
    {
        return $this->stock <= 0;
    }

    public function canSell(int $quantity = 1): bool
    {
        return $this->is_active && $this->stock >= $quantity;
    }

    public function decreaseStock(int $quantity): void
    {
        $this->decrement('stock', $quantity);
    }

    public function increaseStock(int $quantity): void
    {
        $this->increment('stock', $quantity);
    }

    // Accessors
    public function getProfitPerUnitAttribute(): float
    {
        return (float) $this->price - (float) $this->cost_price;
    }

    public function getProfitMarginAttribute(): float
    {
        if ($this->price <= 0) {
            return 0;
        }
        return round((($this->price - $this->cost_price) / $this->price) * 100, 2);
    }

    public function getOptionValueString(): string
    {
        if (empty($this->option_values)) {
            return '';
        }

        return collect($this->option_values)
            ->pluck('value')
            ->implode(' / ');
    }
}
