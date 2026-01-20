<?php

namespace App\Models;

use Illuminate\Database\Eloquent\{Model, SoftDeletes};
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;
use App\Services\ProductImageService;

class Product extends Model
{
    use HasFactory, SoftDeletes;

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
        'is_featured' => 'boolean',
        'is_public' => 'boolean',
        'display_order' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected $appends = ['image_thumbnail_url', 'image_medium_url', 'profit_margin', 'profit_per_unit'];

    // Boot method for automatic slug generation
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

    // Helpers
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

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
        return $this->isActive() && $this->stock >= $quantity;
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
}
