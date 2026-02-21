<?php

namespace App\Enums;

enum UserRole: string
{
    case SUPER_ADMIN = 'Super Admin';
    case ADMIN = 'Admin';
    case STAFF = 'Staff';
    case MEMBER = 'Anggota';

    public function label(): string
    {
        return match($this) {
            self::SUPER_ADMIN => 'Super Administrator',
            self::ADMIN => 'Administrator',
            self::STAFF => 'Staf / Pengurus',
            self::MEMBER => 'Anggota Koperasi',
        };
    }
}
