<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\{Product, Purchase, PurchaseItem, StockAdjustment, User};
use Illuminate\Support\Facades\DB;

class PurchaseSeeder extends Seeder
{
    public function run(): void
    {
        if (Purchase::count() > 0) {
            return;
        }

        $user = User::where('email', 'admin@sikopma.test')->first() ?? User::first();
        if (!$user) return;

        $products = Product::inRandomOrder()->get();
        if ($products->isEmpty()) return;

        DB::transaction(function () use ($products, $user) {
            // Create a few purchase documents across recent days
            for ($p = 0; $p < 3; $p++) {
                $date = now()->subDays(10 - ($p * 3));
                $invoice = Purchase::generateInvoiceNumber();

                $purchase = Purchase::create([
                    'user_id' => $user->id,
                    'supplier_name' => 'Supplier ' . chr(65 + $p),
                    'invoice_number' => $invoice,
                    'date' => $date->toDateString(),
                    'total_amount' => 0,
                    'payment_status' => 'paid',
                    'notes' => 'Demo purchase seeder',
                ]);

                $items = $products->shuffle()->take(rand(4, 7));
                $total = 0;

                foreach ($items as $product) {
                    $qty = rand(5, 20);
                    // Simulate cost as a fraction of price
                    $cost = max(1000, (int) round($product->price * 0.6));
                    $subtotal = $qty * $cost;
                    $total += $subtotal;

                    PurchaseItem::create([
                        'purchase_id' => $purchase->id,
                        'product_id' => $product->id,
                        'quantity' => $qty,
                        'cost_price' => $cost,
                        'subtotal' => $subtotal,
                    ]);

                    // Stock increase and log adjustment
                    $previous = $product->stock;
                    $product->increment('stock', $qty);

                    StockAdjustment::create([
                        'user_id' => $user->id,
                        'product_id' => $product->id,
                        'type' => 'in',
                        'quantity' => $qty,
                        'previous_stock' => $previous,
                        'new_stock' => $product->stock,
                        'reason' => 'Purchase: ' . $invoice,
                    ]);
                }

                $purchase->update(['total_amount' => $total]);
            }
        });
    }
}
