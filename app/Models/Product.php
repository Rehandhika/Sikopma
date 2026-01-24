<?php

namespace App\Models;

use Illuminate\Database\Eloquent\{Model, SoftDeletes};
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;
use App\Services\ProductImageService;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Cache TTL in minutes for product stats
     */
    protected const CACHE_TTL_MINUTES = 5;

    protected $fillable = [
        'name',
        'sku',
        'price',
        'cost_price',
        'stock',
        'min_stock',
        'category',
        'description',
        'status',
        'has_variants',
        'slug',
        'image',
        'image_url', // Keep for backward compatibility
        'is_featured',
        'is_public',
        'display_order',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'cost_price' => 'decimal:2',
        'stock' => 'integer',
        'min_stock' => 'integer',
        'has_variants' => 'boolean',
        'is_featured' => 'boolean',
        'is_public' => 'boolean',
        'display_order' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected $appends = ['image_thumbnail_url', 'image_medium_url', 'profit_margin', 'profit_per_unit', 'total_stock', 'price_range'];

    // Boot method for automatic slug generation and cache invalidation
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($product) {
            if (empty($product->slug)) {
                $product->slug = Str::slug($product->name);
                
                // Ensure slug is unique
                $originalSlug = $product->slug;
                $count = 1;
                while (static::where('slug', $product->slug)->exists()) {
                    $product->slug = $originalSlug . '-' . $count;
                    $count++;
                }
            }
        });
        
        static::updating(function ($product) {
            if ($product->isDirty('name') && empty($product->slug)) {
                $product->slug = Str::slug($product->name);
                
                // Ensure slug is unique
                $originalSlug = $product->slug;
                $count = 1;
                while (static::where('slug', $product->slug)->where('id', '!=', $product->id)->exists()) {
                    $product->slug = $originalSlug . '-' . $count;
                    $count++;
                }
            }
        });

        // Auto-invalidate cache when product is saved
        static::saved(function ($product) {
            if ($product->has_variants) {
                $product->invalidateVariantCache();
            }
        });
    }

    /**
     * Invalidate all variant-related cache for this product
     * Called when product or its variants change
     * 
     * Requirements: 1.2
     */
    public function invalidateVariantCache(): void
    {
        Cache::forget("product:{$this->id}:total_stock");
        Cache::forget("product:{$this->id}:price_range");
        Cache::forget("product:{$this->id}:variant_count");
        Cache::forget("product:{$this->id}:stats");
        Cache::forget("product:{$this->id}:variants");
    }

    // Relationships
    public function saleItems()
    {
        return $this->hasMany(SaleItem::class);
    }

    public function purchaseItems()
    {
        return $this->hasMany(PurchaseItem::class);
    }

    public function stockAdjustments()
    {
        return $this->hasMany(StockAdjustment::class);
    }

    public function variants()
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function activeVariants()
    {
        return $this->hasMany(ProductVariant::class)->where('is_active', true);
    }

    public function variantOptions()
    {
        return $this->belongsToMany(VariantOption::class, 'product_variant_options');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeLowStock($query)
    {
        return $query->whereColumn('stock', '<=', 'min_stock');
    }

    public function scopeOutOfStock($query)
    {
        return $query->where('stock', '<=', 0);
    }

    public function scopeInStock($query)
    {
        return $query->where('stock', '>', 0);
    }

    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('display_order')->orderBy('name');
    }

    /**
     * Scope untuk filter products yang memiliki variants dengan low stock
     * 
     * Requirements: 2.4
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWithLowStockVariants($query)
    {
        return $query->where('has_variants', true)
            ->whereHas('activeVariants', function ($q) {
                $q->whereColumn('stock', '<=', 'min_stock');
            });
    }

    /**
     * Scope untuk eager loading variant statistics
     * Menggunakan withCount, withSum, withMin, withMax untuk menghindari N+1 queries
     * 
     * Requirements: 1.1, 1.3
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWithVariantStats($query)
    {
        return $query->withCount(['activeVariants as active_variants_count'])
            ->withSum('activeVariants as variants_total_stock', 'stock')
            ->withMin('activeVariants as variants_min_price', 'price')
            ->withMax('activeVariants as variants_max_price', 'price');
    }

    // Helpers
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function hasVariants(): bool
    {
        return (bool) $this->has_variants;
    }

    public function isLowStock(): bool
    {
        return $this->getTotalStockAttribute() <= $this->min_stock;
    }

    /**
     * Check if product is out of stock
     * For variant products: returns true if ALL variants are out of stock
     * 
     * Requirements: 2.5
     * 
     * @return bool
     */
    public function isOutOfStock(): bool
    {
        if ($this->has_variants) {
            // For variant products, check if all active variants have zero stock
            // This ensures product is only marked out of stock when ALL variants are depleted
            $totalStock = $this->getTotalStockAttribute();
            
            // Also check if there are any active variants at all
            $hasActiveVariants = $this->activeVariants()->exists();
            
            // Out of stock if no active variants OR all variants have zero stock
            return !$hasActiveVariants || $totalStock <= 0;
        }
        
        return (int) ($this->attributes['stock'] ?? 0) <= 0;
    }

    /**
     * Check if any variant is out of stock (for variant products)
     * Useful for showing partial out-of-stock warnings
     * 
     * @return bool
     */
    public function hasOutOfStockVariants(): bool
    {
        if (!$this->has_variants) {
            return false;
        }

        return $this->activeVariants()
            ->where('stock', '<=', 0)
            ->exists();
    }

    /**
     * Get count of out-of-stock variants
     * 
     * @return int
     */
    public function getOutOfStockVariantsCount(): int
    {
        if (!$this->has_variants) {
            return 0;
        }

        return $this->activeVariants()
            ->where('stock', '<=', 0)
            ->count();
    }

    public function canSell(int $quantity = 1): bool
    {
        return $this->isActive() && $this->getTotalStockAttribute() >= $quantity;
    }

    public function decreaseStock(int $quantity): void
    {
        $this->decrement('stock', $quantity);
    }

    public function increaseStock(int $quantity): void
    {
        $this->increment('stock', $quantity);
    }

    // Image Accessors
    public function getImageThumbnailUrlAttribute(): ?string
    {
        return app(ProductImageService::class)->getThumbnailUrl($this->image);
    }

    public function getImageMediumUrlAttribute(): ?string
    {
        return app(ProductImageService::class)->getUrl($this->image, 'medium');
    }

    public function getImageLargeUrlAttribute(): ?string
    {
        return app(ProductImageService::class)->getUrl($this->image, 'large');
    }

    public function getImageOriginalUrlAttribute(): ?string
    {
        if (empty($this->image)) {
            return null;
        }
        // Use relative URL for cross-device compatibility
        return '/storage/' . ltrim($this->image, '/');
    }

    public function hasImage(): bool
    {
        return !empty($this->image);
    }

    // Profit Calculations
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

    public function getTotalStockValueAttribute(): float
    {
        return (float) $this->stock * (float) $this->price;
    }

    public function getTotalStockCostAttribute(): float
    {
        return (float) $this->stock * (float) $this->cost_price;
    }

    public function getPotentialProfitAttribute(): float
    {
        return $this->total_stock_value - $this->total_stock_cost;
    }

    // Variant-aware accessors with caching
    
    /**
     * Get total stock with caching for variant products
     * Cache dengan TTL 5 menit, auto-invalidate saat variant berubah
     * 
     * Requirements: 1.2, 2.1
     * 
     * @return int
     */
    public function getTotalStockAttribute(): int
    {
        // If eager loaded via scopeWithVariantStats, use that value
        if ($this->has_variants && isset($this->attributes['variants_total_stock'])) {
            return (int) ($this->attributes['variants_total_stock'] ?? 0);
        }

        if (!$this->has_variants) {
            return (int) ($this->attributes['stock'] ?? 0);
        }

        // Use cached value for variant products
        return $this->getCachedTotalStock();
    }

    /**
     * Get cached total stock for variant products
     * 
     * Requirements: 1.2
     * 
     * @return int
     */
    public function getCachedTotalStock(): int
    {
        if (!$this->has_variants) {
            return (int) ($this->attributes['stock'] ?? 0);
        }

        $cacheKey = "product:{$this->id}:total_stock";
        
        return Cache::remember($cacheKey, now()->addMinutes(self::CACHE_TTL_MINUTES), function () {
            return (int) $this->activeVariants()->sum('stock');
        });
    }

    /**
     * Get price range with caching for variant products
     * Cache dengan TTL 5 menit, auto-invalidate saat variant berubah
     * 
     * Requirements: 1.2, 4.2, 5.1
     * 
     * @return array{min: float, max: float}
     */
    public function getPriceRangeAttribute(): array
    {
        // If eager loaded via scopeWithVariantStats, use those values
        if ($this->has_variants && isset($this->attributes['variants_min_price'])) {
            return [
                'min' => (float) ($this->attributes['variants_min_price'] ?? $this->price),
                'max' => (float) ($this->attributes['variants_max_price'] ?? $this->price),
            ];
        }

        if (!$this->has_variants) {
            return ['min' => (float) $this->price, 'max' => (float) $this->price];
        }

        // Use cached value for variant products
        return $this->getCachedPriceRange();
    }

    /**
     * Get cached price range for variant products
     * 
     * Requirements: 1.2
     * 
     * @return array{min: float, max: float}
     */
    public function getCachedPriceRange(): array
    {
        if (!$this->has_variants) {
            return ['min' => (float) $this->price, 'max' => (float) $this->price];
        }

        $cacheKey = "product:{$this->id}:price_range";
        
        return Cache::remember($cacheKey, now()->addMinutes(self::CACHE_TTL_MINUTES), function () {
            $variants = $this->activeVariants();
            $minPrice = $variants->min('price');
            $maxPrice = $variants->max('price');
            
            return [
                'min' => (float) ($minPrice ?? $this->price),
                'max' => (float) ($maxPrice ?? $this->price),
            ];
        });
    }

    /**
     * Get variant count with caching
     * 
     * @return int
     */
    public function getVariantCountAttribute(): int
    {
        // If eager loaded via scopeWithVariantStats, use that value
        if ($this->has_variants && isset($this->attributes['active_variants_count'])) {
            return (int) $this->attributes['active_variants_count'];
        }

        if (!$this->has_variants) {
            return 0;
        }

        $cacheKey = "product:{$this->id}:variant_count";
        
        return Cache::remember($cacheKey, now()->addMinutes(self::CACHE_TTL_MINUTES), function () {
            return $this->activeVariants()->count();
        });
    }

    public function getDisplayPriceAttribute(): string
    {
        $range = $this->price_range;
        if ($range['min'] === $range['max']) {
            return 'Rp' . number_format($range['min'], 0, ',', '.');
        }
        return 'Rp' . number_format($range['min'], 0, ',', '.') . ' - Rp' . number_format($range['max'], 0, ',', '.');
    }
}
