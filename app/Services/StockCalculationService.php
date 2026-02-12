<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class StockCalculationService
{
    /**
     * Calculate comprehensive stock statistics.
     * Handles both simple products and variant products correctly.
     *
     * @return array
     */
    public function calculateStockStats(): array
    {
        // 1. Simple Products Stats (Value & Counts)
        $simpleStats = DB::table('products')
            ->where('has_variants', false)
            ->whereNull('deleted_at')
            ->selectRaw('
                COUNT(*) as total,
                SUM(CASE WHEN stock <= min_stock AND stock > 0 THEN 1 ELSE 0 END) as low_stock,
                SUM(CASE WHEN stock <= 0 THEN 1 ELSE 0 END) as out_of_stock,
                SUM(CASE WHEN stock > min_stock THEN 1 ELSE 0 END) as normal_stock,
                COALESCE(SUM(stock * price), 0) as stock_value,
                COALESCE(SUM(stock * cost_price), 0) as stock_cost
            ')->first();

        // 2. Parent Products Stats (Counts only)
        // We use the synced 'stock' column in products table for counts
        $parentStats = DB::table('products')
            ->where('has_variants', true)
            ->whereNull('deleted_at')
            ->selectRaw('
                COUNT(*) as total,
                SUM(CASE WHEN stock <= min_stock AND stock > 0 THEN 1 ELSE 0 END) as low_stock,
                SUM(CASE WHEN stock <= 0 THEN 1 ELSE 0 END) as out_of_stock,
                SUM(CASE WHEN stock > min_stock THEN 1 ELSE 0 END) as normal_stock
            ')->first();

        // 3. Variant Stats (Value only)
        // We calculate value from variants directly to ensure correct pricing
        $variantValueStats = DB::table('product_variants')
            ->where('is_active', true)
            ->whereNull('deleted_at')
            ->whereExists(function ($query) {
                $query->select(DB::raw(1))
                      ->from('products')
                      ->whereColumn('products.id', 'product_variants.product_id')
                      ->whereNull('deleted_at');
            })
            ->selectRaw('
                COALESCE(SUM(stock * price), 0) as stock_value,
                COALESCE(SUM(stock * cost_price), 0) as stock_cost
            ')->first();

        $totalValue = ($simpleStats->stock_value ?? 0) + ($variantValueStats->stock_value ?? 0);
        $totalCost = ($simpleStats->stock_cost ?? 0) + ($variantValueStats->stock_cost ?? 0);

        return [
            'total' => ($simpleStats->total ?? 0) + ($parentStats->total ?? 0),
            'low_stock' => ($simpleStats->low_stock ?? 0) + ($parentStats->low_stock ?? 0),
            'out_of_stock' => ($simpleStats->out_of_stock ?? 0) + ($parentStats->out_of_stock ?? 0),
            'normal_stock' => ($simpleStats->normal_stock ?? 0) + ($parentStats->normal_stock ?? 0),
            'total_value' => (float) $totalValue,
            'total_cost' => (float) $totalCost,
            'potential_profit' => (float) ($totalValue - $totalCost),
        ];
    }
}
