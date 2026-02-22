<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Add attendance override mode setting to disable "check-in bebas" by default.
     */
    public function up(): void
    {
        DB::table('system_settings')->updateOrInsert(
            ['key' => 'attendance.override_mode'],
            [
                'value' => 'false',
                'type' => 'boolean',
                'description' => 'Mode check-in bebas (tanpa jadwal) - false = mode otomatis',
                'group' => 'attendance',
                'updated_at' => now(),
            ]
        );

        // Also add other attendance settings
        DB::table('system_settings')->updateOrInsert(
            ['key' => 'attendance.auto_absent_after_hours'],
            [
                'value' => '2',
                'type' => 'integer',
                'description' => 'Otomatis mark absent setelah X jam',
                'group' => 'attendance',
                'updated_at' => now(),
            ]
        );

        DB::table('system_settings')->updateOrInsert(
            ['key' => 'attendance.require_photo'],
            [
                'value' => 'true',
                'type' => 'boolean',
                'description' => 'Wajib foto saat check-in',
                'group' => 'attendance',
                'updated_at' => now(),
            ]
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('system_settings')->where('key', 'attendance.override_mode')->delete();
        DB::table('system_settings')->where('key', 'attendance.auto_absent_after_hours')->delete();
        DB::table('system_settings')->where('key', 'attendance.require_photo')->delete();
    }
};
