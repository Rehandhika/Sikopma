<?php

namespace App\Enums;

enum AttendanceStatus: string
{
    case PRESENT = 'present';
    case LATE = 'late';
    case PERMIT = 'permit';
    case SICK = 'sick';
    case ABSENT = 'absent';
    case ALPHA = 'alpha';

    public function label(): string
    {
        return match($this) {
            self::PRESENT => 'Hadir',
            self::LATE => 'Terlambat',
            self::PERMIT => 'Izin',
            self::SICK => 'Sakit',
            self::ABSENT => 'Alpa / Tanpa Keterangan',
            self::ALPHA => 'Alpa',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::PRESENT => 'success',
            self::LATE => 'warning',
            self::PERMIT => 'info',
            self::SICK => 'info',
            self::ABSENT => 'danger',
            self::ALPHA => 'danger',
        };
    }
}
