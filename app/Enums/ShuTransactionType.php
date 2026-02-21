<?php

namespace App\Enums;

enum ShuTransactionType: string
{
    case EARN = 'earn';
    case REDEEM = 'redeem';
    case ADJUST = 'adjust';
    case REFUND = 'refund';

    public function label(): string
    {
        return match($this) {
            self::EARN => 'Perolehan Poin',
            self::REDEEM => 'Penukaran Poin',
            self::ADJUST => 'Penyesuaian Manual',
            self::REFUND => 'Pengembalian (Refund)',
        };
    }
}
