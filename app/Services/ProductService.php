<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\StockAdjustment;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ProductService
{
    /**
     * Create new product
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
     */
    public function delete(int $id): bool
    {
        $product = Product::findOrFail($id);
        $productName = $product->name;

        // Check if product has been sold
        if ($product->saleItems()->exists()) {
            throw new \Exception('Tidak dapat menghapus produk yang sudah pernah dijual. Nonaktifkan saja.');
        }

        log_audit('delete', $product);

        // Log activity
        ActivityLogService::logProductDeleted($productName);

        return $product->delete();
    }

    /**
     * Process procurement (purchasing)
     * Handles stock increment and cost price update (Weighted Average)
     */
    public function createProcurement(array $data, array $items): Purchase
    {
        return DB::transaction(function () use ($data, $items) {
            // 1. Create Purchase Record
            $purchase = Purchase::create([
                'user_id' => auth()->id(),
                'supplier_name' => $data['supplier_name'] ?? null,
                'invoice_number' => $data['invoice_number'],
                'date' => $data['date'],
                'notes' => $data['notes'] ?? null,
                'payment_status' => 'paid', // Assuming paid for simplicity, or add field in form
                'total_amount' => 0, // Will update after calculating items
            ]);

            $totalAmount = 0;

            foreach ($items as $item) {
                // 2. Create Purchase Item
                $subtotal = $item['quantity'] * $item['cost_price'];
                $totalAmount += $subtotal;

                PurchaseItem::create([
                    'purchase_id' => $purchase->id,
                    'product_id' => $item['product_id'],
                    'product_variant_id' => $item['variant_id'] ?? null,
                    'quantity' => $item['quantity'],
                    'cost_price' => $item['cost_price'],
                    'subtotal' => $subtotal,
                ]);

                // 3. Update Product Stock & Cost Price (Weighted Average)
                if (!empty($item['variant_id'])) {
                    $this->updateVariantStockAndCost($item['product_id'], $item['variant_id'], $item['quantity'], $item['cost_price'], $purchase->invoice_number);
                } else {
                    // Check if product actually has variants but no variant_id provided
                    $product = Product::find($item['product_id']);
                    if ($product && $product->has_variants) {
                        throw new \Exception("Produk '{$product->name}' memiliki varian. Mohon pilih varian spesifik.");
                    }
                    
                    $this->updateProductStockAndCost($item['product_id'], $item['quantity'], $item['cost_price'], $purchase->invoice_number);
                }
            }

            // Update total amount
            $purchase->update(['total_amount' => $totalAmount]);

            ActivityLogService::logPurchaseCreated($purchase->invoice_number, $purchase->total_amount);

            return $purchase;
        });
    }

    private function updateProductStockAndCost(int $productId, int $qty, float $newCost, string $ref)
    {
        $product = Product::find($productId);
        if (!$product) return;

        $oldStock = $product->stock;
        $oldCost = $product->cost_price;

        // Calculate Weighted Average Cost
        // If stock is negative or zero, we take the new cost as the base
        if ($oldStock <= 0) {
            $avgCost = $newCost;
        } else {
            $avgCost = (($oldStock * $oldCost) + ($qty * $newCost)) / ($oldStock + $qty);
        }

        $product->stock += $qty;
        $product->cost_price = $avgCost;
        $product->save();

        // Log Adjustment
        StockAdjustment::create([
            'product_id' => $productId,
            'type' => 'in',
            'quantity' => $qty,
            'previous_stock' => $oldStock,
            'new_stock' => $product->stock,
            'reason' => "Procurement: {$ref}",
            'user_id' => auth()->id(),
        ]);
    }

    private function updateVariantStockAndCost(int $productId, int $variantId, int $qty, float $newCost, string $ref)
    {
        $variant = ProductVariant::find($variantId);
        if (!$variant) return;

        $oldStock = $variant->stock;
        $oldCost = $variant->cost_price;

        if ($oldStock <= 0) {
            $avgCost = $newCost;
        } else {
            $avgCost = (($oldStock * $oldCost) + ($qty * $newCost)) / ($oldStock + $qty);
        }

        $variant->stock += $qty;
        $variant->cost_price = $avgCost;
        $variant->save();

        // Sync Parent Stock
        app(\App\Services\ProductVariantService::class)->syncProductTotalStock(Product::find($productId));

        // Log Adjustment
        StockAdjustment::create([
            'product_id' => $productId,
            'variant_id' => $variantId,
            'type' => 'in',
            'quantity' => $qty,
            'previous_stock' => $oldStock,
            'new_stock' => $variant->stock,
            'reason' => "Procurement: {$ref} (Var)",
            'user_id' => auth()->id(),
        ]);
    }

    /**
     * Get low stock products
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
     */
    public function getOutOfStock(): Collection
    {
        return Product::where('stock', 0)
            ->where('status', 'active')
            ->get();
    }

    /**
     * Get stock value statistics
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
     */
    public function canSell(int $productId, int $quantity): bool
    {
        $product = Product::find($productId);

        if (! $product) {
            return false;
        }

        return $product->isActive() && $product->stock >= $quantity;
    }


}
