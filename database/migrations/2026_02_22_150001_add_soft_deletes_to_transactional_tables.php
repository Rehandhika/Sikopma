<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tables = ['sale_items', 'purchase_items', 'stock_adjustments', 'penalties', 'attendances', 'leave_requests', 'schedule_assignments', 'schedules', 'shu_point_transactions'];
        
        foreach ($tables as $table) {
            if (Schema::hasTable($table) && !Schema::hasColumn($table, 'deleted_at')) {
                Schema::table($table, fn(Blueprint $table) => $table->softDeletes());
            }
        }
    }

    public function down(): void
    {
        $tables = ['sale_items', 'purchase_items', 'stock_adjustments', 'penalties', 'attendances', 'leave_requests', 'schedule_assignments', 'schedules', 'shu_point_transactions'];
        
        foreach ($tables as $table) {
            if (Schema::hasTable($table) && Schema::hasColumn($table, 'deleted_at')) {
                Schema::table($table, fn(Blueprint $table) => $table->dropSoftDeletes());
            }
        }
    }
};
