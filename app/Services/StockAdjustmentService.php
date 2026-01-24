<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\StockAdjustment;
use App\Exceptions\BusinessException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Service for managing stock adjustments
 * Supports both product-level and variant-level adjustments
 * 
 * Requirements: 6.1, 6.2, 6.3, 6.4
 */
class StockAdjustmentService
{
    protected ProductVariantService $variantService;

    public function __construct(ProductVariantService $variantService)
    {
        $this->variantService = $variantService;
    }

    /**
     * Adjust stock for a variant with logging
     * Requirements: 6.2, 6.3
     * 
     * @param ProductVariant $variant
     * @param int $quantity Positive number
     * @param string $type 'in' or 'out'
     * @param string $reason
     * @return StockAdjustment
     * @throws BusinessException
     */
    public function adjustVariantStock(
        ProductVariant $variant,
        int $quantity,
        string $type,
        string $reason
    ): StockAdjustment {
        if ($quantity <= 0) {
            throw new BusinessException('Jumlah penyesuaian harus lebih dari 0.');
        }

        if (!in_array($type, ['in', 'out'])) {
            throw new BusinessException('Tipe penyesuaian tidak valid. Gunakan "in" atau "out".');
        }

        // Validate stock for 'out' type
        if ($type === 'out' && $variant->stock < $quantity) {
            throw new BusinessException(
                "Stok varian tidak mencukupi. Tersedia: {$variant->stock}, Diminta: {$quantity}"
            );
        }

        return DB::transaction(function () use ($variant, $quantity, $type, $reason) {
            $previousStock = $variant->stock;

            // Update variant stock
            if ($type === 'in') {
                $variant->increment('stock', $quantity);
            } else {
                $variant->decrement('stock', $quantity);
            }

            $newStock = $variant->fresh()->stock;

            // Create adjustment record with logging
            $adjustment = StockAdjustment::create([
                'user_id' => auth()->id(),
                'product_id' => $variant->product_id,
                'variant_id' => $variant->id,
                'type' => $type,
                'quantity' => $quantity,
                'previous_stock' => $previousStock,
                'new_stock' => $newStock,
                'reason' => $reason,
            ]);

            // Log the adjustment
            Log::channel('daily')->info('Variant stock adjustment', [
                'adjustment_id' => $adjustment->id,
                'variant_id' => $variant->id,
                'variant_name' => $variant->variant_name,
                'product_id' => $variant->product_id,
                'type' => $type,
                'quantity' => $quantity,
                'previous_stock' => $previousStock,
                'new_stock' => $newStock,
                'reason' => $reason,
                'user_id' => auth()->id(),
            ]);

            // Auto-sync product total stock (Requirements: 6.3)
            $this->variantService->syncProductTotalStock($variant->product);

            // Log audit
            log_audit('variant_stock_adjustment', $variant, [
                'type' => $type,
                'quantity' => $quantity,
                'reason' => $reason,
            ]);

            return $adjustment;
        });
    }

    /**
     * Adjust stock for a product (non-variant)
     * 
     * @param Product $product
     * @param int $quantity
     * @param string $type
     * @param string $reason
     * @return StockAdjustment
     * @throws BusinessException
     */
    public function adjustProductStock(
        Product $product,
        int $quantity,
        string $type,
        string $reason
    ): StockAdjustment {
        if ($quantity <= 0) {
            throw new BusinessException('Jumlah penyesuaian harus lebih dari 0.');
        }

        if (!in_array($type, ['in', 'out'])) {
            throw new BusinessException('Tipe penyesuaian tidak valid.');
        }

        // Block direct stock adjustment for variant products
        if ($product->has_variants) {
            throw new BusinessException(
                'Produk dengan varian tidak dapat disesuaikan stoknya secara langsung. ' .
                'Sesuaikan stok pada level varian.'
            );
        }

        if ($type === 'out' && $product->stock < $quantity) {
            throw new BusinessException(
                "Stok produk tidak mencukupi. Tersedia: {$product->stock}, Diminta: {$quantity}"
            );
        }

        return DB::transaction(function () use ($product, $quantity, $type, $reason) {
            $previousStock = $product->stock;

            if ($type === 'in') {
                $product->increment('stock', $quantity);
            } else {
                $product->decrement('stock', $quantity);
            }

            $newStock = $product->fresh()->stock;

            $adjustment = StockAdjustment::create([
                'user_id' => auth()->id(),
                'product_id' => $product->id,
                'variant_id' => null,
                'type' => $type,
                'quantity' => $quantity,
                'previous_stock' => $previousStock,
                'new_stock' => $newStock,
                'reason' => $reason,
            ]);

            Log::channel('daily')->info('Product stock adjustment', [
                'adjustment_id' => $adjustment->id,
                'product_id' => $product->id,
                'product_name' => $product->name,
                'type' => $type,
                'quantity' => $quantity,
                'previous_stock' => $previousStock,
                'new_stock' => $newStock,
                'reason' => $reason,
                'user_id' => auth()->id(),
            ]);

            log_audit('stock_adjustment', $product, [
                'type' => $type,
                'quantity' => $quantity,
                'reason' => $reason,
            ]);

            return $adjustment;
        });
    }

    /**
     * Get adjustment history for a variant
     * Requirements: 6.4
     * 
     * @param int $variantId
     * @param int|null $limit
     * @return Collection
     */
    public function getVariantAdjustmentHistory(int $variantId, ?int $limit = null): Collection
    {
        $query = StockAdjustment::with(['user:id,name'])
            ->byVariant($variantId)
            ->recent();

        if ($limit) {
            $query->limit($limit);
        }

        return $query->get();
    }

    /**
     * Get adjustment history for a product (including all variants)
     * 
     * @param int $productId
     * @param int|null $limit
     * @return Collection
     */
    public function getProductAdjustmentHistory(int $productId, ?int $limit = null): Collection
    {
        $query = StockAdjustment::with(['user:id,name', 'variant:id,variant_name'])
            ->byProduct($productId)
            ->recent();

        if ($limit) {
            $query->limit($limit);
        }

        return $query->get();
    }

    /**
     * Bulk adjust stock for multiple variants
     * 
     * @param array $adjustments Array of ['variant_id' => int, 'quantity' => int, 'type' => string, 'reason' => string]
     * @return Collection
     */
    public function bulkAdjustVariantStock(array $adjustments): Collection
    {
        return DB::transaction(function () use ($adjustments) {
            $results = collect();
            $affectedProductIds = [];

            foreach ($adjustments as $data) {
                $variant = ProductVariant::find($data['variant_id']);
                if (!$variant) {
                    continue;
                }

                try {
                    $adjustment = $this->adjustVariantStockWithoutSync(
                        $variant,
                        $data['quantity'],
                        $data['type'],
                        $data['reason']
                    );
                    $results->push($adjustment);
                    $affectedProductIds[$variant->product_id] = true;
                } catch (BusinessException $e) {
                    // Log error but continue with other adjustments
                    Log::warning('Bulk variant adjustment failed', [
                        'variant_id' => $data['variant_id'],
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            // Sync all affected products at once
            foreach (array_keys($affectedProductIds) as $productId) {
                $product = Product::find($productId);
                if ($product) {
                    $this->variantService->syncProductTotalStock($product);
                }
            }

            return $results;
        });
    }

    /**
     * Internal method to adjust variant stock without syncing product
     * Used for bulk operations
     */
    protected function adjustVariantStockWithoutSync(
        ProductVariant $variant,
        int $quantity,
        string $type,
        string $reason
    ): StockAdjustment {
        if ($quantity <= 0) {
            throw new BusinessException('Jumlah penyesuaian harus lebih dari 0.');
        }

        if ($type === 'out' && $variant->stock < $quantity) {
            throw new BusinessException(
                "Stok varian tidak mencukupi. Tersedia: {$variant->stock}"
            );
        }

        $previousStock = $variant->stock;

        if ($type === 'in') {
            $variant->increment('stock', $quantity);
        } else {
            $variant->decrement('stock', $quantity);
        }

        $newStock = $variant->fresh()->stock;

        return StockAdjustment::create([
            'user_id' => auth()->id(),
            'product_id' => $variant->product_id,
            'variant_id' => $variant->id,
            'type' => $type,
            'quantity' => $quantity,
            'previous_stock' => $previousStock,
            'new_stock' => $newStock,
            'reason' => $reason,
        ]);
    }

    /**
     * Get stock adjustment statistics
     * 
     * @return array
     */
    public function getStats(): array
    {
        return [
            'total_adjustments' => StockAdjustment::count(),
            'total_additions' => StockAdjustment::additions()->sum('quantity'),
            'total_reductions' => StockAdjustment::reductions()->sum('quantity'),
            'variant_adjustments' => StockAdjustment::variantAdjustments()->count(),
            'product_adjustments' => StockAdjustment::productAdjustments()->count(),
        ];
    }
}
