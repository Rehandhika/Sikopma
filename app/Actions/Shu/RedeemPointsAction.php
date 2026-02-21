<?php

namespace App\Actions\Shu;

use App\Enums\ShuTransactionType;
use App\Models\ShuPointTransaction;
use App\Models\Student;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use RuntimeException;

class RedeemPointsAction
{
    public function execute(Student $student, int $pointsToRedeem, ?int $cashAmount = null, ?string $notes = null): ShuPointTransaction
    {
        if ($pointsToRedeem <= 0) {
            throw new InvalidArgumentException('Poin redeem harus lebih dari 0');
        }

        return DB::transaction(function () use ($student, $pointsToRedeem, $cashAmount, $notes) {
            $lockedStudent = Student::lockForUpdate()->findOrFail($student->id);

            if ($lockedStudent->points_balance < $pointsToRedeem) {
                throw new RuntimeException('Saldo poin tidak mencukupi');
            }

            $lockedStudent->points_balance -= $pointsToRedeem;
            $lockedStudent->save();

            return ShuPointTransaction::create([
                'student_id' => $lockedStudent->id,
                'sale_id' => null,
                'type' => ShuTransactionType::REDEEM->value,
                'amount' => null,
                'conversion_rate' => 0,
                'points' => -$pointsToRedeem,
                'cash_amount' => $cashAmount,
                'notes' => $notes,
                'created_by' => Auth::id(),
            ]);
        });
    }
}
