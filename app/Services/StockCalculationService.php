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
        // 1. Simple Products Stats
        $simpleStats = DB::table('products')
            ->where('has_variants', false)
            ->whereNull('deleted_at')
            ->selectRaw('
                COUNT(*) as total_count,
                SUM(CASE WHEN stock <= min_stock AND stock > 0 THEN 1 ELSE 0 END) as low_stock_count,
                SUM(CASE WHEN stock <= 0 THEN 1 ELSE 0 END) as out_of_stock_count,
                SUM(CASE WHEN stock > min_stock THEN 1 ELSE 0 END) as normal_stock_count,
                COALESCE(SUM(stock * price), 0) as total_value,
                COALESCE(SUM(stock * cost_price), 0) as total_cost
            ')->first();

        // 2. Parent Products Stats (Counts only - based on synced stock)
        $parentStats = DB::table('products')
            ->where('has_variants', true)
            ->whereNull('deleted_at')
            ->selectRaw('
                COUNT(*) as total_count,
                SUM(CASE WHEN stock <= min_stock AND stock > 0 THEN 1 ELSE 0 END) as low_stock_count,
                SUM(CASE WHEN stock <= 0 THEN 1 ELSE 0 END) as out_of_stock_count,
                SUM(CASE WHEN stock > min_stock THEN 1 ELSE 0 END) as normal_stock_count
            ')->first();

        // 3. Variant Stats (Value only - for accurate asset valuation)
        $variantStats = DB::table('product_variants')
            ->join('products', 'products.id', '=', 'product_variants.product_id')
            ->whereNull('products.deleted_at')
            ->whereNull('product_variants.deleted_at')
            ->where('product_variants.is_active', true)
            ->selectRaw('
                COALESCE(SUM(product_variants.stock * product_variants.price), 0) as total_value,
                COALESCE(SUM(product_variants.stock * product_variants.cost_price), 0) as total_cost
            ')->first();

        // Combine results
        $totalCount = ($simpleStats->total_count ?? 0) + ($parentStats->total_count ?? 0);
        $lowStock = ($simpleStats->low_stock_count ?? 0) + ($parentStats->low_stock_count ?? 0);
        $outOfStock = ($simpleStats->out_of_stock_count ?? 0) + ($parentStats->out_of_stock_count ?? 0);
        $normalStock = ($simpleStats->normal_stock_count ?? 0) + ($parentStats->normal_stock_count ?? 0);
        
        $totalValue = ($simpleStats->total_value ?? 0) + ($variantStats->total_value ?? 0);
        $totalCost = ($simpleStats->total_cost ?? 0) + ($variantStats->total_cost ?? 0);

        return [
            'total' => $totalCount,
            'low_stock' => $lowStock,
            'out_of_stock' => $outOfStock,
            'normal_stock' => $normalStock,
            'total_value' => (float) $totalValue,
            'total_cost' => (float) $totalCost,
            'potential_profit' => (float) ($totalValue - $totalCost),
        ];
    }
}
