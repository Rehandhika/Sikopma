<?php

namespace App\Services;

use App\Models\Product;
use App\Models\StockAdjustment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;

class ProductService
{
    /**
     * Create new product
     *
     * @param array $data
     * @return Product
     */
    public function create(array $data): Product
    {
        return DB::transaction(function () use ($data) {
            $product = Product::create($data);

            // Log initial stock if provided
            if (isset($data['stock']) && $data['stock'] > 0) {
                StockAdjustment::create([
                    'product_id' => $product->id,
                    'type' => 'in',
                    'quantity' => $data['stock'],
                    'previous_stock' => 0,
                    'new_stock' => $data['stock'],
                    'reason' => 'Initial stock',
                    'user_id' => auth()->id(),
                ]);
            }

            log_audit('create', $product);

            return $product;
        });
    }

    /**
     * Update product
     *
     * @param int $id
     * @param array $data
     * @return Product
     */
    public function update(int $id, array $data): Product
    {
        return DB::transaction(function () use ($id, $data) {
            $product = Product::findOrFail($id);
            $oldValues = $product->toArray();

            $product->update($data);

            log_audit('update', $product, $oldValues, $product->toArray());

            return $product->fresh();
        });
    }

    /**
     * Delete product (soft delete)
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool
    {
        $product = Product::findOrFail($id);
        
        // Check if product has been sold
        if ($product->saleItems()->exists()) {
            throw new \Exception('Tidak dapat menghapus produk yang sudah pernah dijual. Nonaktifkan saja.');
        }

        log_audit('delete', $product);

        return $product->delete();
    }

    /**
     * Adjust stock
     *
     * @param int $productId
     * @param string $type (in/out)
     * @param int $quantity
     * @param string $reason
     * @return Product
     */
    public function adjustStock(int $productId, string $type, int $quantity, string $reason): Product
    {
        return DB::transaction(function () use ($productId, $type, $quantity, $reason) {
            $product = Product::findOrFail($productId);
            $previousStock = $product->stock;

            // Validate stock for 'out' type
            if ($type === 'out' && $product->stock < $quantity) {
                throw new \Exception('Stok tidak mencukupi. Stok tersedia: ' . $product->stock);
            }

            // Update stock
            if ($type === 'in') {
                $product->increment('stock', $quantity);
            } else {
                $product->decrement('stock', $quantity);
            }

            $newStock = $product->fresh()->stock;

            // Log adjustment
            StockAdjustment::create([
                'product_id' => $productId,
                'type' => $type,
                'quantity' => $quantity,
                'previous_stock' => $previousStock,
                'new_stock' => $newStock,
                'reason' => $reason,
                'user_id' => auth()->id(),
            ]);

            log_audit('stock_adjustment', $product, ['type' => $type, 'quantity' => $quantity]);

            return $product->fresh();
        });
    }

    /**
     * Get low stock products
     *
     * @return Collection
     */
    public function getLowStock(): Collection
    {
        return Product::whereColumn('stock', '<=', 'min_stock')
            ->where('stock', '>', 0)
            ->where('status', 'active')
            ->orderBy('stock')
            ->get();
    }

    /**
     * Get out of stock products
     *
     * @return Collection
     */
    public function getOutOfStock(): Collection
    {
        return Product::where('stock', 0)
            ->where('status', 'active')
            ->get();
    }

    /**
     * Get stock value statistics
     *
     * @return array
     */
    public function getStockStats(): array
    {
        return [
            'total_products' => Product::where('status', 'active')->count(),
            'total_stock_value' => Product::where('status', 'active')
                ->selectRaw('SUM(stock * price) as total')
                ->value('total') ?? 0,
            'low_stock_count' => Product::whereColumn('stock', '<=', 'min_stock')
                ->where('stock', '>', 0)
                ->where('status', 'active')
                ->count(),
            'out_of_stock_count' => Product::where('stock', 0)
                ->where('status', 'active')
                ->count(),
        ];
    }

    /**
     * Generate SKU automatically
     *
     * @param string $name
     * @param string|null $category
     * @return string
     */
    public function generateSKU(string $name, ?string $category = null): string
    {
        $prefix = $category ? strtoupper(substr($category, 0, 3)) : 'PRD';
        $nameCode = strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $name), 0, 3));
        $timestamp = now()->format('ymd');
        $random = str_pad(rand(0, 999), 3, '0', STR_PAD_LEFT);

        return "{$prefix}-{$nameCode}-{$timestamp}-{$random}";
    }

    /**
     * Check if product can be sold
     *
     * @param int $productId
     * @param int $quantity
     * @return bool
     */
    public function canSell(int $productId, int $quantity): bool
    {
        $product = Product::find($productId);

        if (!$product) {
            return false;
        }

        return $product->isActive() && $product->stock >= $quantity;
    }

    /**
     * Bulk update stock from purchase
     *
     * @param array $items [['product_id' => 1, 'quantity' => 10], ...]
     * @param string $reference
     * @return void
     */
    public function bulkIncreaseStock(array $items, string $reference): void
    {
        DB::transaction(function () use ($items, $reference) {
            foreach ($items as $item) {
                $product = Product::find($item['product_id']);
                
                if ($product) {
                    $product->increment('stock', $item['quantity']);

                    StockAdjustment::create([
                        'product_id' => $item['product_id'],
                        'type' => 'in',
                        'quantity' => $item['quantity'],
                        'previous_stock' => $product->stock - $item['quantity'],
                        'new_stock' => $product->stock,
                        'reason' => "Purchase: {$reference}",
                        'user_id' => auth()->id(),
                    ]);
                }
            }
        });
    }

    /**
     * Bulk decrease stock from sale
     *
     * @param array $items [['product_id' => 1, 'quantity' => 2], ...]
     * @param string $reference
     * @return void
     */
    public function bulkDecreaseStock(array $items, string $reference): void
    {
        DB::transaction(function () use ($items, $reference) {
            foreach ($items as $item) {
                $product = Product::find($item['product_id']);
                
                if ($product) {
                    if ($product->stock < $item['quantity']) {
                        throw new \Exception("Stok {$product->name} tidak mencukupi");
                    }

                    $product->decrement('stock', $item['quantity']);

                    StockAdjustment::create([
                        'product_id' => $item['product_id'],
                        'type' => 'out',
                        'quantity' => $item['quantity'],
                        'previous_stock' => $product->stock + $item['quantity'],
                        'new_stock' => $product->stock,
                        'reason' => "Sale: {$reference}",
                        'user_id' => auth()->id(),
                    ]);
                }
            }
        });
    }
}
