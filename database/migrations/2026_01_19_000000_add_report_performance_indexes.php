<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Menambahkan composite index untuk optimasi query report
     */
    public function up(): void
    {
        // Index untuk sales report - covering index untuk stats query
        Schema::table('sales', function (Blueprint $table) {
            // Composite index untuk date range + payment_method + total_amount
            if (!$this->indexExists('sales', 'sales_date_payment_total_idx')) {
                $table->index(['date', 'payment_method', 'total_amount'], 'sales_date_payment_total_idx');
            }
            
            // Index untuk hourly distribution query
            if (!$this->indexExists('sales', 'sales_date_created_idx')) {
                $table->index(['date', 'created_at'], 'sales_date_created_idx');
            }
        });

        // Index untuk sale_items - top products query
        Schema::table('sale_items', function (Blueprint $table) {
            if (!$this->indexExists('sale_items', 'sale_items_sale_product_idx')) {
                $table->index(['sale_id', 'product_id'], 'sale_items_sale_product_idx');
            }
        });

        // Index untuk attendances report
        Schema::table('attendances', function (Blueprint $table) {
            // Composite index untuk date range + user + status
            if (!$this->indexExists('attendances', 'attendances_date_user_status_idx')) {
                $table->index(['date', 'user_id', 'status'], 'attendances_date_user_status_idx');
            }
        });

        // Index untuk penalties report
        Schema::table('penalties', function (Blueprint $table) {
            // Composite index untuk date range + user + status + points
            if (!$this->indexExists('penalties', 'penalties_date_user_status_points_idx')) {
                $table->index(['date', 'user_id', 'status', 'points'], 'penalties_date_user_status_points_idx');
            }
        });
    }

    /**
     * Check if an index exists on a table
     */
    private function indexExists(string $table, string $index): bool
    {
        $connection = Schema::getConnection();
        $driver = $connection->getDriverName();
        
        if ($driver === 'sqlite') {
            // For SQLite, query sqlite_master
            $result = $connection->select(
                "SELECT COUNT(*) as count FROM sqlite_master 
                 WHERE type = 'index' AND tbl_name = ? AND name = ?",
                [$table, $index]
            );
            return $result[0]->count > 0;
        }
        
        // For MySQL/MariaDB
        $database = $connection->getDatabaseName();
        $result = $connection->select(
            "SELECT COUNT(*) as count FROM information_schema.statistics 
             WHERE table_schema = ? AND table_name = ? AND index_name = ?",
            [$database, $table, $index]
        );
        
        return $result[0]->count > 0;
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropIndex('sales_date_payment_total_idx');
            $table->dropIndex('sales_date_created_idx');
        });

        Schema::table('sale_items', function (Blueprint $table) {
            $table->dropIndex('sale_items_sale_product_idx');
        });

        Schema::table('attendances', function (Blueprint $table) {
            $table->dropIndex('attendances_date_user_status_idx');
        });

        Schema::table('penalties', function (Blueprint $table) {
            $table->dropIndex('penalties_date_user_status_points_idx');
        });
    }
};
