<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PenaltyType;

class PenaltyTypeSeeder extends Seeder
{
    public function run(): void
    {
        $penaltyTypes = [
            [
                'code' => 'MISSED_SCHEDULE',
                'name' => 'Tidak Hadir Jadwal',
                'description' => 'Tidak hadir pada jadwal yang telah ditentukan',
                'points' => 5,
                'is_active' => true,
            ],
            [
                'code' => 'LATE_CHECKIN',
                'name' => 'Terlambat Check-in',
                'description' => 'Check-in melebihi batas waktu yang ditentukan',
                'points' => 2,
                'is_active' => true,
            ],
            [
                'code' => 'EARLY_CHECKOUT',
                'name' => 'Check-out Awal',
                'description' => 'Check-out sebelum waktu jadwal berakhir',
                'points' => 3,
                'is_active' => true,
            ],
            [
                'code' => 'UNAUTHORIZED_ABSENCE',
                'name' => 'Tidak Hadir Tanpa Izin',
                'description' => 'Tidak hadir tanpa mengajukan izin terlebih dahulu',
                'points' => 10,
                'is_active' => true,
            ],
            [
                'code' => 'SWAP_VIOLATION',
                'name' => 'Pelanggaran Tukar Jadwal',
                'description' => 'Melanggar aturan tukar jadwal yang telah disepakati',
                'points' => 5,
                'is_active' => true,
            ],
            [
                'code' => 'EQUIPMENT_DAMAGE',
                'name' => 'Kerusakan Peralatan',
                'description' => 'Merusak peralatan atau fasilitas koperasi',
                'points' => 15,
                'is_active' => true,
            ],
            [
                'code' => 'CASH_DISCREPANCY',
                'name' => 'Selisih Kas',
                'description' => 'Terdapat selisih dalam pencatatan kas',
                'points' => 8,
                'is_active' => true,
            ],
            [
                'code' => 'BAD_BEHAVIOR',
                'name' => 'Perilaku Tidak Pantas',
                'description' => 'Perilaku yang tidak sesuai dengan norma koperasi',
                'points' => 7,
                'is_active' => true,
            ],
        ];

        foreach ($penaltyTypes as $type) {
            PenaltyType::firstOrCreate(
                ['code' => $type['code']],
                $type
            );
        }
    }
}
