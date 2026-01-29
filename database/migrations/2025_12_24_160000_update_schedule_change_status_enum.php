<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // First update existing status values to new format
        DB::table('schedule_change_requests')
            ->where('status', 'admin_approved')
            ->update(['status' => 'approved']);

        DB::table('schedule_change_requests')
            ->where('status', 'admin_rejected')
            ->update(['status' => 'rejected']);

        DB::table('schedule_change_requests')
            ->whereIn('status', ['target_approved', 'target_rejected'])
            ->update(['status' => 'cancelled']);

        // Change column type from enum to varchar to allow new values - only for MySQL
        $driver = Schema::getConnection()->getDriverName();
        if ($driver !== 'sqlite') {
            DB::statement("ALTER TABLE schedule_change_requests MODIFY COLUMN status VARCHAR(20) NOT NULL DEFAULT 'pending'");
        }
    }

    public function down(): void
    {
        // Revert to enum - only for MySQL
        $driver = Schema::getConnection()->getDriverName();
        if ($driver !== 'sqlite') {
            DB::statement("ALTER TABLE schedule_change_requests MODIFY COLUMN status ENUM('pending','target_approved','target_rejected','admin_approved','admin_rejected','cancelled') NOT NULL DEFAULT 'pending'");
        }
    }
};
