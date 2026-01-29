<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            // Add composite index for common queries
            if (!$this->indexExists('sales', 'sales_cashier_date_index')) {
                $table->index(['cashier_id', 'date'], 'sales_cashier_date_index');
            }
            if (!$this->indexExists('sales', 'sales_payment_method_index')) {
                $table->index('payment_method');
            }
        });

        Schema::table('attendances', function (Blueprint $table) {
            // Add composite index for user attendance queries
            if (!$this->indexExists('attendances', 'attendances_user_date_index')) {
                $table->index(['user_id', 'date'], 'attendances_user_date_index');
            }
        });

        Schema::table('schedule_assignments', function (Blueprint $table) {
            // Add composite indexes for schedule queries
            if (!$this->indexExists('schedule_assignments', 'schedule_assignments_user_date_index')) {
                $table->index(['user_id', 'date'], 'schedule_assignments_user_date_index');
            }
            if (!$this->indexExists('schedule_assignments', 'schedule_assignments_date_status_index')) {
                $table->index(['date', 'status'], 'schedule_assignments_date_status_index');
            }
        });

        Schema::table('penalties', function (Blueprint $table) {
            // Add composite index for user penalties
            if (!$this->indexExists('penalties', 'penalties_user_status_index')) {
                $table->index(['user_id', 'status'], 'penalties_user_status_index');
            }
            if (!$this->indexExists('penalties', 'penalties_date_index')) {
                $table->index('date');
            }
        });

        Schema::table('leave_requests', function (Blueprint $table) {
            // Add composite index for leave queries
            if (!$this->indexExists('leave_requests', 'leave_requests_user_status_index')) {
                $table->index(['user_id', 'status'], 'leave_requests_user_status_index');
            }
            if (!$this->indexExists('leave_requests', 'leave_requests_dates_index')) {
                $table->index(['start_date', 'end_date'], 'leave_requests_dates_index');
            }
        });

        Schema::table('swap_requests', function (Blueprint $table) {
            // Add indexes for swap request queries
            if (!$this->indexExists('swap_requests', 'swap_requests_requester_status_index')) {
                $table->index(['requester_id', 'status'], 'swap_requests_requester_status_index');
            }
            if (!$this->indexExists('swap_requests', 'swap_requests_target_status_index')) {
                $table->index(['target_id', 'status'], 'swap_requests_target_status_index');
            }
        });

        Schema::table('products', function (Blueprint $table) {
            // Add index for stock queries
            if (!$this->indexExists('products', 'products_stock_index')) {
                $table->index('stock');
            }
            if (!$this->indexExists('products', 'products_category_index')) {
                $table->index('category');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropIndex('sales_cashier_date_index');
            $table->dropIndex('sales_payment_method_index');
        });

        Schema::table('attendances', function (Blueprint $table) {
            $table->dropIndex('attendances_user_date_index');
        });

        Schema::table('schedule_assignments', function (Blueprint $table) {
            $table->dropIndex('schedule_assignments_user_date_index');
            $table->dropIndex('schedule_assignments_date_status_index');
        });

        Schema::table('penalties', function (Blueprint $table) {
            $table->dropIndex('penalties_user_status_index');
            $table->dropIndex('penalties_date_index');
        });

        Schema::table('leave_requests', function (Blueprint $table) {
            $table->dropIndex('leave_requests_user_status_index');
            $table->dropIndex('leave_requests_dates_index');
        });

        Schema::table('swap_requests', function (Blueprint $table) {
            $table->dropIndex('swap_requests_requester_status_index');
            $table->dropIndex('swap_requests_target_status_index');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex('products_stock_index');
            $table->dropIndex('products_category_index');
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
};
