<?php

namespace App\Repositories;

use App\Models\Sale;
use App\Models\SaleItem;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class SalesRepository
{
    /**
     * Get sales statistics for a date range
     */
    public function getStats(Carbon $startDate, Carbon $endDate): array
    {
        $sales = Sale::whereBetween('date', [$startDate, $endDate])->get();

        return [
            'total_sales' => $sales->sum('total_amount'),
            'total_transactions' => $sales->count(),
            'average_transaction' => $sales->avg('total_amount'),
            'total_discount' => $sales->sum('discount'),
        ];
    }

    /**
     * Get top selling products
     */
    public function getTopProducts(Carbon $startDate, Carbon $endDate, int $limit = 10): Collection
    {
        return SaleItem::select('product_id', DB::raw('SUM(quantity) as total_quantity'), DB::raw('SUM(subtotal) as total_revenue'))
            ->whereHas('sale', function ($query) use ($startDate, $endDate) {
                $query->whereBetween('date', [$startDate, $endDate]);
            })
            ->with('product')
            ->groupBy('product_id')
            ->orderBy('total_quantity', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get sales by cashier
     */
    public function getByCashier(Carbon $startDate, Carbon $endDate): Collection
    {
        return Sale::select('cashier_id', DB::raw('COUNT(*) as transaction_count'), DB::raw('SUM(total_amount) as total_sales'))
            ->whereBetween('date', [$startDate, $endDate])
            ->with('cashier')
            ->groupBy('cashier_id')
            ->orderBy('total_sales', 'desc')
            ->get();
    }

    /**
     * Get daily sales trend
     */
    public function getDailyTrend(Carbon $startDate, Carbon $endDate): Collection
    {
        return Sale::select(DB::raw('DATE(date) as sale_date'), DB::raw('COUNT(*) as transaction_count'), DB::raw('SUM(total_amount) as total_sales'))
            ->whereBetween('date', [$startDate, $endDate])
            ->groupBy('sale_date')
            ->orderBy('sale_date')
            ->get();
    }

    /**
     * Get sales by payment method
     */
    public function getByPaymentMethod(Carbon $startDate, Carbon $endDate): Collection
    {
        return Sale::select('payment_method', DB::raw('COUNT(*) as count'), DB::raw('SUM(total_amount) as total'))
            ->whereBetween('date', [$startDate, $endDate])
            ->groupBy('payment_method')
            ->get();
    }

    /**
     * Get today's sales
     */
    public function getTodaySales(): array
    {
        $today = today();
        $sales = Sale::whereDate('date', $today)->get();

        return [
            'total' => $sales->sum('total_amount'),
            'count' => $sales->count(),
            'average' => $sales->avg('total_amount'),
        ];
    }

    /**
     * Get sales with items for a date range
     */
    public function getWithItems(Carbon $startDate, Carbon $endDate, ?int $cashierId = null): Collection
    {
        $query = Sale::whereBetween('date', [$startDate, $endDate])
            ->with(['items.product', 'cashier']);

        if ($cashierId) {
            $query->where('cashier_id', $cashierId);
        }

        return $query->orderBy('date', 'desc')->get();
    }

    /**
     * Create sale with items
     */
    public function createWithItems(array $saleData, array $items): Sale
    {
        return DB::transaction(function () use ($saleData, $items) {
            $sale = Sale::create($saleData);

            foreach ($items as $item) {
                SaleItem::create([
                    'sale_id' => $sale->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'subtotal' => $item['quantity'] * $item['price'],
                ]);
            }

            return $sale->fresh('items');
        });
    }

    /**
     * Get monthly comparison
     */
    public function getMonthlyComparison(int $year): Collection
    {
        return Sale::select(
            DB::raw('MONTH(date) as month'),
            DB::raw('COUNT(*) as transaction_count'),
            DB::raw('SUM(total_amount) as total_sales')
        )
            ->whereYear('date', $year)
            ->groupBy('month')
            ->orderBy('month')
            ->get();
    }
}
