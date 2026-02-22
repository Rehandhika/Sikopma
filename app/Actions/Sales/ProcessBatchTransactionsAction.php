<?php

namespace App\Actions\Sales;

use App\Enums\ShuTransactionType;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\ShuPointTransaction;
use App\Models\Student;
use App\Services\ShuPointService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class ProcessBatchTransactionsAction
{
    /**
     * Process batch insert with proper transaction handling
     *
     * @param array $salesData Array of validated sales data
     * @param string $date Date of transaction (Y-m-d)
     * @param int|null $cashierId User ID of cashier (optional, defaults to Auth::id())
     * @return int Number of transactions processed
     * @throws \Exception
     */
    public function execute(array $salesData, string $date, ?int $cashierId = null): int
    {
        if (empty($salesData)) {
            throw new \Exception('Tidak ada data valid untuk disimpan.');
        }

        $cashierId = $cashierId ?? Auth::id();

        return DB::transaction(function () use ($salesData, $date, $cashierId) {
            $now = now();
            $conversionAmount = app(ShuPointService::class)->getConversionAmount();

            // Generate invoice numbers inside transaction with lock
            $invoices = Sale::generateBatchInvoiceNumbers(count($salesData), $date);

            // Prepare sales data
            $salesToInsert = [];
            $stockUpdates = [];
            $transactionsToInsert = [];
            $pointsByStudent = [];

            foreach ($salesData as $i => $data) {
                $subtotal = $data['qty'] * $data['price'];
                $amount = (int) round($subtotal);
                $studentId = $data['student_id'] ?? null;
                
                $points = 0;
                if ($studentId) {
                    $points = app(ShuPointService::class)->computeEarnedPoints($amount, $conversionAmount);
                    $pointsByStudent[$studentId] = ($pointsByStudent[$studentId] ?? 0) + $points;
                }

                $salesToInsert[] = [
                    'cashier_id' => $cashierId,
                    'student_id' => $studentId,
                    'invoice_number' => $invoices[$i],
                    'date' => $date,
                    'total_amount' => $subtotal,
                    'payment_method' => $data['payment_method'],
                    'payment_amount' => $subtotal,
                    'change_amount' => 0,
                    'shu_points_earned' => $points,
                    'conversion_rate' => $studentId ? $conversionAmount : 0, // Using standardized column name
                    'created_at' => $now,
                    'updated_at' => $now,
                ];

                $stockUpdates[$data['product_id']] = ($stockUpdates[$data['product_id']] ?? 0) + $data['qty'];
            }

            // Insert sales
            Sale::insert($salesToInsert);

            $saleIdsByInvoice = Sale::query()
                ->whereIn('invoice_number', $invoices)
                ->pluck('id', 'invoice_number')
                ->toArray();

            if (count($saleIdsByInvoice) !== count($invoices)) {
                throw new \Exception('Gagal mengambil ID transaksi yang baru dibuat.');
            }

            // Prepare sale items
            $itemsToInsert = [];
            foreach ($salesData as $i => $data) {
                $saleId = $saleIdsByInvoice[$invoices[$i]] ?? null;
                if (! $saleId) {
                    throw new \Exception('Gagal memetakan transaksi tersimpan.');
                }

                $itemsToInsert[] = [
                    'sale_id' => $saleId,
                    'product_id' => $data['product_id'],
                    'product_name' => $data['product_name'],
                    'quantity' => $data['qty'],
                    'price' => $data['price'],
                    'subtotal' => $data['qty'] * $data['price'],
                    'created_at' => $now,
                    'updated_at' => $now,
                ];

                $studentId = $data['student_id'] ?? null;
                if ($studentId) {
                    $amount = (int) round($data['qty'] * $data['price']);
                    $points = app(ShuPointService::class)->computeEarnedPoints($amount, $conversionAmount);

                    $transactionsToInsert[] = [
                        'student_id' => $studentId,
                        'sale_id' => $saleId,
                        'type' => ShuTransactionType::EARN->value,
                        'amount' => $amount,
                        'conversion_rate' => $conversionAmount, // Using standardized column name
                        'points' => $points,
                        'cash_amount' => null, // Explicitly null for EARN
                        'notes' => null,
                        'created_by' => $cashierId,
                        'created_at' => $now,
                    ];
                }
            }

            SaleItem::insert($itemsToInsert);

            if (! empty($pointsByStudent)) {
                $lockedStudents = Student::query()
                    ->whereIn('id', array_keys($pointsByStudent))
                    ->lockForUpdate()
                    ->get(['id', 'points_balance']);

                foreach ($lockedStudents as $lockedStudent) {
                    $lockedStudent->points_balance += (int) ($pointsByStudent[$lockedStudent->id] ?? 0);
                    $lockedStudent->save();
                }

                if (! empty($transactionsToInsert)) {
                    ShuPointTransaction::insert($transactionsToInsert);
                }
            }

            // Update stock
            foreach ($stockUpdates as $productId => $qty) {
                Product::where('id', $productId)->decrement('stock', $qty);
            }

            return count($salesData);
        });
    }
}
