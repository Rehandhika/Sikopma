<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Update existing data
        DB::table('stock_adjustments')
            ->where('type', 'addition')
            ->update(['type' => 'in']);

        DB::table('stock_adjustments')
            ->where('type', 'reduction')
            ->update(['type' => 'out']);

        // Alter column enum values - only for MySQL
        $driver = Schema::getConnection()->getDriverName();
        if ($driver !== 'sqlite') {
            DB::statement("ALTER TABLE stock_adjustments MODIFY COLUMN type ENUM('in', 'out') NOT NULL");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert data
        DB::table('stock_adjustments')
            ->where('type', 'in')
            ->update(['type' => 'addition']);

        DB::table('stock_adjustments')
            ->where('type', 'out')
            ->update(['type' => 'reduction']);

        // Revert column enum values - only for MySQL
        $driver = Schema::getConnection()->getDriverName();
        if ($driver !== 'sqlite') {
            DB::statement("ALTER TABLE stock_adjustments MODIFY COLUMN type ENUM('addition', 'reduction') NOT NULL");
        }
    }
};
