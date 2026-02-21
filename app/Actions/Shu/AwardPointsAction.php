<?php

namespace App\Actions\Shu;

use App\Enums\ShuTransactionType;
use App\Models\Sale;
use App\Models\Setting;
use App\Models\ShuPointTransaction;
use App\Models\Student;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class AwardPointsAction
{
    public function getConversionAmount(): int
    {
        return (int) Cache::remember('shu_point_conversion_amount', 60, function () {
            return (int) Setting::get('shu_point_conversion_amount', 10000);
        });
    }

    public function computeEarnedPoints(int $amount, int $conversionAmount): int
    {
        if ($amount <= 0 || $conversionAmount <= 0) {
            return 0;
        }
        return (int) floor($amount / $conversionAmount);
    }

    public function execute(Sale $sale, Student $student, ?int $conversionAmount = null): int
    {
        $conversionAmount ??= $this->getConversionAmount();
        $amount = (int) round((float) $sale->total_amount);
        $points = $this->computeEarnedPoints($amount, $conversionAmount);

        if ($points <= 0) {
            $sale->update([
                'student_id' => $student->id,
                'shu_points_earned' => 0,
                'conversion_rate' => $conversionAmount,
            ]);

            return 0;
        }

        return DB::transaction(function () use ($sale, $student, $amount, $conversionAmount, $points) {
            $lockedStudent = Student::lockForUpdate()->findOrFail($student->id);

            $existing = ShuPointTransaction::where('sale_id', $sale->id)
                ->where('type', ShuTransactionType::EARN->value)
                ->first();
                
            if ($existing) {
                return (int) $existing->points;
            }

            $lockedStudent->points_balance += $points;
            $lockedStudent->save();

            $sale->update([
                'student_id' => $lockedStudent->id,
                'shu_points_earned' => $points,
                'conversion_rate' => $conversionAmount,
            ]);

            ShuPointTransaction::create([
                'student_id' => $lockedStudent->id,
                'sale_id' => $sale->id,
                'type' => ShuTransactionType::EARN->value,
                'amount' => $amount,
                'conversion_rate' => $conversionAmount,
                'points' => $points,
                'created_by' => Auth::id(),
            ]);

            return $points;
        });
    }
}
