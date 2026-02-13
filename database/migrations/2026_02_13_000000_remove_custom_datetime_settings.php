<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $keys = [
            'timezone',
            'date_format',
            'time_format',
            'datetime_format',
            'use_24_hour',
            'first_day_of_week',
            'locale',
            'use_custom_datetime',
            'custom_date',
            'custom_time'
        ];

        DB::table('settings')->whereIn('key', $keys)->delete();
    }

    public function down(): void
    {
        // No down, permanent removal
    }
};
