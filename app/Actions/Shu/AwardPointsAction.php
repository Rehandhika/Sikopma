<?php

namespace App\Actions\Shu;

use App\Enums\ShuTransactionType;
use App\Models\Sale;
use App\Models\ShuPointTransaction;
use App\Models\Student;
use App\Services\ShuPointService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AwardPointsAction
{
    public function execute(Sale $sale, Student $student, ?int $conversionAmount = null): int
    {
        $shuService = app(ShuPointService::class);
        $conversionAmount ??= $shuService->getConversionAmount();
        
        $amount = (int) round((float) $sale->total_amount);
        $points = $shuService->computeEarnedPoints($amount, $conversionAmount);

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
