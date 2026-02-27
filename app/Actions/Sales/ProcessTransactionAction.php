<?php

namespace App\Actions\Sales;

use App\Actions\Shu\AwardPointsAction;
use App\Data\Sales\TransactionData;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Student;
use App\Services\ActivityLogService;
use App\Services\PaymentConfigurationService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class ProcessTransactionAction
{
    public function __construct(
        protected PaymentConfigurationService $paymentConfig,
        protected AwardPointsAction $awardPointsAction
    ) {}

    /**
     * Process a sales transaction.
     *
     * @param TransactionData $data
     * @return array Result containing invoice, total, change, points, etc.
     * @throws Exception
     */
    public function execute(TransactionData $data): array
    {
        // 1. Validate Payment Method
        if (!$this->paymentConfig->isMethodEnabled($data->payment_method)) {
            throw new Exception('Metode pembayaran tidak tersedia');
        }

        // 2. Resolve Student (if any)
        $student = null;
        if (!empty($data->student_nim)) {
            $student = Student::where('nim', $data->student_nim)->first();
            if (!$student) {
                throw new Exception('NIM mahasiswa tidak ditemukan');
            }
        }

        // 3. Calculate Total
        $total = collect($data->cart_items)->sum(fn($item) => $item['price'] * $item['quantity']);

        // 4. Validate Payment Amount
        if ($data->payment_amount < $total) {
            throw new Exception('Pembayaran kurang dari total belanja');
        }

        return DB::transaction(function () use ($data, $total, $student) {
            // 5. Lock and Validate Stock
            foreach ($data->cart_items as $item) {
                if (!empty($item['variant_id'])) {
                    $variant = ProductVariant::lockForUpdate()->find($item['variant_id']);
                    if (!$variant) {
                        throw new Exception("Varian produk '{$item['name']}' tidak ditemukan");
                    }
                    if ($variant->stock < $item['quantity']) {
                        throw new Exception("Stok varian '{$item['name']}' tidak mencukupi (Sisa: {$variant->stock})");
                    }
                } else {
                    $product = Product::lockForUpdate()->find($item['product_id']);
                    if (!$product) {
                        throw new Exception("Produk '{$item['name']}' tidak ditemukan");
                    }
                    if ($product->stock < $item['quantity']) {
                        throw new Exception("Stok produk '{$item['name']}' tidak mencukupi (Sisa: {$product->stock})");
                    }
                }
            }

            // 6. Create Sale Record
            $sale = Sale::create([
                'cashier_id' => $data->cashier_id,
                'invoice_number' => Sale::generateInvoiceNumber(),
                'date' => now()->toDateString(),
                'total_amount' => $total,
                'payment_method' => $data->payment_method,
                'payment_amount' => $data->payment_amount,
                'change_amount' => $data->payment_amount - $total,
                'notes' => $data->notes,
            ]);

            // 7. Process Items and Stock
            $saleItems = [];
            $stockUpdates = [];
            $variantStockUpdates = [];
            $now = now();

            foreach ($data->cart_items as $item) {
                $saleItems[] = [
                    'sale_id' => $sale->id,
                    'product_id' => $item['product_id'],
                    'variant_id' => $item['variant_id'] ?? null,
                    'product_name' => $item['name'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'subtotal' => $item['price'] * $item['quantity'],
                    'created_at' => $now,
                    'updated_at' => $now,
                ];

                if (!empty($item['variant_id'])) {
                    $variantStockUpdates[$item['variant_id']] = ($variantStockUpdates[$item['variant_id']] ?? 0) + $item['quantity'];
                } else {
                    $stockUpdates[$item['product_id']] = ($stockUpdates[$item['product_id']] ?? 0) + $item['quantity'];
                }
            }

            SaleItem::insert($saleItems);

            foreach ($stockUpdates as $productId => $quantity) {
                Product::where('id', $productId)->decrement('stock', $quantity);
            }

            foreach ($variantStockUpdates as $variantId => $quantity) {
                ProductVariant::where('id', $variantId)->decrement('stock', $quantity);
            }

            // 8. Award Points
            $shuPointsEarned = 0;
            if ($student) {
                $shuPointsEarned = $this->awardPointsAction->execute($sale, $student);
            }

            // 9. Log Activity
            ActivityLogService::logSaleCreated($sale->invoice_number, $total, $student?->nim);

            return [
                'sale' => $sale,
                'invoice' => $sale->invoice_number,
                'total' => $total,
                'payment' => $data->payment_amount,
                'change' => $data->payment_amount - $total,
                'method' => $data->payment_method,
                'student_nim' => $student?->nim,
                'shu_points' => $shuPointsEarned,
            ];
        });
    }
}
