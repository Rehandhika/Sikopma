<?php

namespace App\Services;

use App\Models\Sale;
use Illuminate\Support\Facades\DB;

class SaleService
{
    /**
     * Calculate and save the total amount for a sale.
     */
    public function calculateTotal(Sale $sale): void
    {
        $sale->total_amount = $sale->items()->sum(DB::raw('quantity * price'));
        $sale->save();
    }

    /**
     * Calculate and save the change amount for a sale.
     */
    public function calculateChange(Sale $sale): void
    {
        $sale->change_amount = $sale->payment_amount - $sale->total_amount;
        $sale->save();
    }
}
