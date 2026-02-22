<?php

namespace App\Services;

use App\Enums\ShuTransactionType;
use App\Models\Sale;
use App\Models\Setting;
use App\Models\ShuPointTransaction;
use App\Models\Student;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * SHU Point Service
 *
 * Handles all SHU (Sisa Hasil Usaha) point calculations and transactions.
 *
 * IMPORTANT: Column Naming Convention
 * ================================
 * The `conversion_rate` column in sales and shu_point_transactions tables stores
 * the CONVERSION AMOUNT (rupiah per point).
 *
 * Example:
 * - conversion_rate = 10000 means Rp 10,000 purchase earns 1 point
 * - Formula: points = floor(purchase_amount / conversion_amount)
 *
 * The setting key is `shu_point_conversion_amount` (default: 10000).
 */
class ShuPointService
{
    /**
     * Get the conversion amount from settings.
     *
     * @return int The rupiah amount required to earn 1 point (default: 10000)
     */
    public function getConversionAmount(): int
    {
        return (int) Cache::remember('shu_point_conversion_amount', 60, function () {
            // Default: 10000 rupiah = 1 point
            return (int) Setting::get('shu_point_conversion_amount', 10000);
        });
    }

    /**
     * Compute earned points based on purchase amount.
     * Formula: floor(amount / conversion_amount)
     */
    public function computeEarnedPoints(int $amount, int $conversionAmount): int
    {
        if ($amount <= 0 || $conversionAmount <= 0) {
            return 0;
        }

        // 1 Point per 10000 logic:
        // Example: 10000 / 10000 = 1 point
        // Example: 25000 / 10000 = 2 points
        return (int) floor($amount / $conversionAmount);
    }

    public function awardPointsForSale(Sale $sale, Student $student, ?int $conversionAmount = null): int
    {
        return app(\App\Actions\Shu\AwardPointsAction::class)->execute($sale, $student, $conversionAmount);
    }

    public function redeemPoints(Student $student, int $pointsToRedeem, ?int $cashAmount = null, ?string $notes = null): ShuPointTransaction
    {
        return app(\App\Actions\Shu\RedeemPointsAction::class)->execute($student, $pointsToRedeem, $cashAmount, $notes);
    }

    public function adjustPoints(Student $student, int $pointsDelta, ?string $notes = null): ShuPointTransaction
    {
        if ($pointsDelta === 0) {
            throw new \InvalidArgumentException('Perubahan poin tidak boleh 0');
        }

        return DB::transaction(function () use ($student, $pointsDelta, $notes) {
            $lockedStudent = Student::lockForUpdate()->findOrFail($student->id);

            if ($pointsDelta < 0 && $lockedStudent->points_balance < abs($pointsDelta)) {
                throw new \RuntimeException('Saldo poin tidak mencukupi');
            }

            $lockedStudent->points_balance += $pointsDelta;
            $lockedStudent->save();

            return ShuPointTransaction::create([
                'student_id' => $lockedStudent->id,
                'sale_id' => null,
                'type' => 'adjust',
                'amount' => null,
                'conversion_rate' => 0,
                'points' => $pointsDelta,
                'notes' => $notes,
                'created_by' => Auth::id(),
            ]);
        });
    }

    public function adjustSalePoints(Sale $sale, int $newPoints, ?string $notes = null): void
    {
        if ($newPoints < 0) {
            throw new \InvalidArgumentException('Poin tidak boleh negatif');
        }

        DB::transaction(function () use ($sale, $newPoints, $notes) {
            $lockedSale = Sale::lockForUpdate()->findOrFail($sale->id);

            if (! $lockedSale->student_id) {
                throw new \RuntimeException('Transaksi ini tidak terkait mahasiswa');
            }

            $lockedStudent = Student::lockForUpdate()->findOrFail($lockedSale->student_id);

            $earnTrx = ShuPointTransaction::where('sale_id', $lockedSale->id)
                ->where('type', ShuTransactionType::EARN->value)
                ->first();

            if (! $earnTrx) {
                throw new \RuntimeException('Transaksi poin (earn) tidak ditemukan');
            }

            $oldPoints = (int) $earnTrx->points;
            $delta = $newPoints - $oldPoints;

            if ($delta === 0) {
                return;
            }

            if ($delta < 0 && $lockedStudent->points_balance < abs($delta)) {
                throw new \RuntimeException('Saldo poin tidak mencukupi untuk penyesuaian transaksi');
            }

            $lockedStudent->points_balance += $delta;
            $lockedStudent->save();

            $lockedSale->update([
                'shu_points_earned' => $newPoints,
            ]);

            $baseNotes = "Penyesuaian poin transaksi {$lockedSale->invoice_number}: {$oldPoints} -> {$newPoints}";
            $finalNotes = $notes ? $baseNotes.' | '.$notes : $baseNotes;

            $earnTrx->update([
                'points' => $newPoints,
                'notes' => $finalNotes,
                'created_by' => Auth::id(),
            ]);
        });
    }

    public function reverseSalePoints(Sale $sale): void
    {
        if ((int) $sale->shu_points_earned <= 0 || ! $sale->student_id) {
            return;
        }

        DB::transaction(function () use ($sale) {
            $lockedStudent = Student::lockForUpdate()->find($sale->student_id);
            if (! $lockedStudent) {
                return;
            }

            $existingEarn = ShuPointTransaction::where('sale_id', $sale->id)->where('type', ShuTransactionType::EARN->value)->first();
            if (! $existingEarn) {
                return;
            }

            $points = (int) $existingEarn->points;
            if ($points <= 0) {
                return;
            }

            $existingReverse = ShuPointTransaction::where('sale_id', $sale->id)->where('type', ShuTransactionType::ADJUST->value)->where('points', -$points)->first();
            if ($existingReverse) {
                return;
            }

            if ($lockedStudent->points_balance >= $points) {
                $lockedStudent->points_balance -= $points;
            } else {
                $lockedStudent->points_balance = 0;
            }
            $lockedStudent->save();

            ShuPointTransaction::create([
                'student_id' => $lockedStudent->id,
                'sale_id' => $sale->id,
                'type' => 'adjust',
                'amount' => null,
                'conversion_rate' => 0,
                'points' => -$points,
                'notes' => 'Reversal poin karena transaksi dihapus',
                'created_by' => Auth::id(),
            ]);

            $sale->update([
                'shu_points_earned' => 0,
                'shu_percentage_bps' => 0,
                'student_id' => null,
            ]);
        });
    }
}
