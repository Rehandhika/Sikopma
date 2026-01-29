<?php

use App\Models\SystemSetting;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $settings = [
            [
                'key' => 'timezone',
                'value' => 'Asia/Jakarta',
                'type' => 'string',
                'description' => 'Zona waktu sistem',
                'group' => 'datetime',
            ],
            [
                'key' => 'date_format',
                'value' => 'd/m/Y',
                'type' => 'string',
                'description' => 'Format tampilan tanggal',
                'group' => 'datetime',
            ],
            [
                'key' => 'time_format',
                'value' => 'H:i',
                'type' => 'string',
                'description' => 'Format tampilan waktu',
                'group' => 'datetime',
            ],
            [
                'key' => 'datetime_format',
                'value' => 'd/m/Y H:i',
                'type' => 'string',
                'description' => 'Format tampilan tanggal dan waktu',
                'group' => 'datetime',
            ],
            [
                'key' => 'use_24_hour',
                'value' => '1',
                'type' => 'boolean',
                'description' => 'Gunakan format 24 jam',
                'group' => 'datetime',
            ],
            [
                'key' => 'first_day_of_week',
                'value' => '1',
                'type' => 'integer',
                'description' => 'Hari pertama dalam minggu (0=Minggu, 1=Senin)',
                'group' => 'datetime',
            ],
            [
                'key' => 'locale',
                'value' => 'id',
                'type' => 'string',
                'description' => 'Bahasa/locale untuk format tanggal',
                'group' => 'datetime',
            ],
        ];

        foreach ($settings as $setting) {
            SystemSetting::updateOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        SystemSetting::whereIn('key', [
            'timezone',
            'date_format',
            'time_format',
            'datetime_format',
            'use_24_hour',
            'first_day_of_week',
            'locale',
        ])->delete();
    }
};
