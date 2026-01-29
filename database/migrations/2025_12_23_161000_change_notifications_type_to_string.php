<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Change type column from enum to string to allow flexible notification types
     * like 'check_in_success', 'swap_request_received', etc.
     */
    public function up(): void
    {
        // For MySQL, we need to alter the column type - skip for SQLite
        $driver = Schema::getConnection()->getDriverName();
        if ($driver !== 'sqlite') {
            DB::statement("ALTER TABLE notifications MODIFY COLUMN type VARCHAR(50) NOT NULL DEFAULT 'info'");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert to enum (note: this may fail if there are values not in the enum) - skip for SQLite
        $driver = Schema::getConnection()->getDriverName();
        if ($driver !== 'sqlite') {
            DB::statement("ALTER TABLE notifications MODIFY COLUMN type ENUM('info', 'warning', 'error', 'success') NOT NULL DEFAULT 'info'");
        }
    }
};
