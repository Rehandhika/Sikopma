<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;

class StockAdjustment extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'product_id',
        'variant_id',
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

    /**
     * Create a stock adjustment for a product (non-variant)
     * 
     * @param int $productId
     * @param string $type 'in' or 'out'
     * @param int $quantity
     * @param string $reason
     * @param int|null $userId
     * @return static
     */
    public static function createForProduct(
        int $productId,
        string $type,
        int $quantity,
        string $reason,
        ?int $userId = null
    ): static {
        $product = Product::findOrFail($productId);
        $previousStock = $product->stock;
        
        return static::create([
            'user_id' => $userId ?? auth()->id(),
            'product_id' => $productId,
            'variant_id' => null,
            'type' => $type,
            'quantity' => $quantity,
            'previous_stock' => $previousStock,
            'new_stock' => $type === 'in' 
                ? $previousStock + $quantity 
                : $previousStock - $quantity,
            'reason' => $reason,
        ]);
    }

    /**
     * Create a stock adjustment for a variant
     * Requirements: 6.1, 6.2
     * 
     * @param int $variantId
     * @param string $type 'in' or 'out'
     * @param int $quantity
     * @param string $reason
     * @param int|null $userId
     * @return static
     */
    public static function createForVariant(
        int $variantId,
        string $type,
        int $quantity,
        string $reason,
        ?int $userId = null
    ): static {
        $variant = ProductVariant::findOrFail($variantId);
        $previousStock = $variant->stock;
        
        return static::create([
            'user_id' => $userId ?? auth()->id(),
            'product_id' => $variant->product_id,
            'variant_id' => $variantId,
            'type' => $type,
            'quantity' => $quantity,
            'previous_stock' => $previousStock,
            'new_stock' => $type === 'in' 
                ? $previousStock + $quantity 
                : $previousStock - $quantity,
            'reason' => $reason,
        ]);
    }

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Relationship to ProductVariant
     * Nullable - adjustment can be for product without variant
     */
    public function variant()
    {
        return $this->belongsTo(ProductVariant::class, 'variant_id');
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

    /**
     * Scope to filter by variant
     */
    public function scopeByVariant($query, int $variantId)
    {
        return $query->where('variant_id', $variantId);
    }

    /**
     * Scope to get only variant adjustments
     */
    public function scopeVariantAdjustments($query)
    {
        return $query->whereNotNull('variant_id');
    }

    /**
     * Scope to get only product-level adjustments (no variant)
     */
    public function scopeProductAdjustments($query)
    {
        return $query->whereNull('variant_id');
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

    /**
     * Check if this adjustment is for a variant
     */
    public function isVariantAdjustment(): bool
    {
        return $this->variant_id !== null;
    }

    /**
     * Get the target name (variant name or product name)
     */
    public function getTargetName(): string
    {
        if ($this->isVariantAdjustment() && $this->variant) {
            return $this->variant->variant_name;
        }
        
        return $this->product?->name ?? 'Unknown';
    }
}
