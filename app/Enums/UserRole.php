<?php

namespace App\Enums;

/**
 * UserRole enum - nilai harus sesuai dengan nama role di database (Spatie Permission)
 * Format: lowercase dengan dash, kecuali 'Super Admin'
 */
enum UserRole: string
{
    case SUPER_ADMIN = 'Super Admin';
    
    // Organization Roles (Wirus Angkatan 66)
    case KETUA = 'ketua';
    case WAKIL_KETUA = 'wakil-ketua';
    case SEKRETARIS = 'sekretaris';
    case BENDAHARA = 'bendahara';
    case KOORDINATOR_TOKO = 'koordinator-toko';
    case KOORDINATOR_PSDA = 'koordinator-psda';
    case KOORDINATOR_PRODUKSI = 'koordinator-produksi';
    case KOORDINATOR_DESAIN = 'koordinator-desain';
    case KOORDINATOR_HUMSAR = 'koordinator-humsar';
    case ANGGOTA = 'anggota';

    public function label(): string
    {
        return match($this) {
            self::SUPER_ADMIN => 'Super Administrator',
            self::KETUA => 'Ketua Umum',
            self::WAKIL_KETUA => 'Wakil Ketua',
            self::SEKRETARIS => 'Sekretaris',
            self::BENDAHARA => 'Bendahara',
            self::KOORDINATOR_TOKO => 'Koordinator Toko',
            self::KOORDINATOR_PSDA => 'Koordinator PSDA',
            self::KOORDINATOR_PRODUKSI => 'Koordinator Produksi',
            self::KOORDINATOR_DESAIN => 'Koordinator Desain',
            self::KOORDINATOR_HUMSAR => 'Koordinator Humsar',
            self::ANGGOTA => 'Anggota',
        };
    }
}
