<?php

namespace Database\Seeders;

use App\Models\StoreSetting;
use Illuminate\Database\Seeder;

class StoreSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Use firstOrCreate to avoid duplicate entries
        StoreSetting::firstOrCreate(
            ['id' => 1], // Only one store settings record should exist
            [
                'is_open' => false,
                'status_reason' => 'Tidak ada pengurus yang bertugas',
                'auto_status' => true,
                'manual_mode' => false,
                'manual_is_open' => false,
                'manual_open_override' => false,
                'operating_hours' => [
                    'monday' => ['open' => '07:30', 'close' => '16:00', 'is_open' => true],
                    'tuesday' => ['open' => '07:30', 'close' => '16:00', 'is_open' => true],
                    'wednesday' => ['open' => '07:30', 'close' => '16:00', 'is_open' => true],
                    'thursday' => ['open' => '07:30', 'close' => '16:00', 'is_open' => true],
                    'friday' => ['open' => null, 'close' => null, 'is_open' => false],
                    'saturday' => ['open' => null, 'close' => null, 'is_open' => false],
                    'sunday' => ['open' => null, 'close' => null, 'is_open' => false],
                ],
                'next_open_mode' => 'default',
                'custom_closed_message' => null,
                'custom_next_open_date' => null,
                'academic_holiday_start' => null,
                'academic_holiday_end' => null,
                'academic_holiday_name' => null,
                'contact_phone' => null,
                'contact_email' => null,
                'contact_address' => null,
                'contact_whatsapp' => null,
                'about_text' => null,
            ]
        );
    }
}
