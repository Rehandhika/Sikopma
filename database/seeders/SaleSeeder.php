<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\{Sale, SaleItem, Product, User, StockAdjustment};
use Illuminate\Support\Facades\DB;

class SaleSeeder extends Seeder
{
    public function run(): void
    {
        if (Sale::count() > 0) {
            return;
        }

        $cashier = User::whereIn('email', ['bph1@sikopma.test','bph2@sikopma.test','bph3@sikopma.test'])->inRandomOrder()->first()
            ?? User::first();
        if (!$cashier) return;

        $products = Product::where('status', 'active')->where('stock', '>', 0)->inRandomOrder()->get();
        if ($products->isEmpty()) return;

        DB::transaction(function () use ($products, $cashier) {
            // Create a few sales across recent days
            for ($s = 0; $s < 5; $s++) {
                $date = now()->subDays(rand(0, 7));
                $invoice = Sale::generateInvoiceNumber();

                $sale = Sale::create([
                    'cashier_id' => $cashier->id,
                    'invoice_number' => $invoice,
                    'date' => $date->toDateString(),
                    'total_amount' => 0,
                    'payment_method' => 'cash',
                    'payment_amount' => 0,
                    'change_amount' => 0,
                    'notes' => 'Demo sale seeder',
                ]);

                $lineProducts = $products->shuffle()->take(rand(2, 5));
                $total = 0;

                foreach ($lineProducts as $product) {
                    $maxQty = max(1, min(5, $product->stock));
                    $qty = rand(1, $maxQty);
                    if ($qty <= 0) continue;

                    $price = (int) $product->price;
                    $subtotal = $qty * $price;
                    $total += $subtotal;

                    SaleItem::create([
                        'sale_id' => $sale->id,
                        'product_id' => $product->id,
                        'product_name' => $product->name,
                        'quantity' => $qty,
                        'price' => $price,
                        'subtotal' => $subtotal,
                    ]);

                    // Decrease stock and record adjustment
                    $previous = $product->stock;
                    $product->decrement('stock', $qty);

                    StockAdjustment::create([
                        'user_id' => $cashier->id,
                        'product_id' => $product->id,
                        'type' => 'out',
                        'quantity' => $qty,
                        'previous_stock' => $previous,
                        'new_stock' => $product->stock,
                        'reason' => 'Sale: ' . $invoice,
                    ]);
                }

                // finalize totals and payment
                $sale->update([
                    'total_amount' => $total,
                    'payment_amount' => $total,
                    'change_amount' => 0,
                ]);
            }
        });
    }
}
