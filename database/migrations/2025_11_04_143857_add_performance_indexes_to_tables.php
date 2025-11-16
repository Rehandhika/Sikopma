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
        // Add indexes to attendances table (check_in and status already exist)
        Schema::table('attendances', function (Blueprint $table) {
            if (!$this->indexExists('attendances', 'attendances_user_id_check_in_index')) {
                $table->index(['user_id', 'check_in']);
            }
        });

        // Add indexes to penalties table
        Schema::table('penalties', function (Blueprint $table) {
            if (!$this->indexExists('penalties', 'penalties_status_index')) {
                $table->index('status');
            }
            if (!$this->indexExists('penalties', 'penalties_user_id_status_index')) {
                $table->index(['user_id', 'status']);
            }
        });

        // Add composite index to sales table
        Schema::table('sales', function (Blueprint $table) {
            if (!$this->indexExists('sales', 'sales_cashier_id_date_index')) {
                $table->index(['cashier_id', 'date']);
            }
            if (!$this->indexExists('sales', 'sales_date_index')) {
                $table->index('date');
            }
        });

        // Add composite index to notifications table
        Schema::table('notifications', function (Blueprint $table) {
            if (!$this->indexExists('notifications', 'notifications_user_id_read_at_index')) {
                $table->index(['user_id', 'read_at']);
            }
        });

        // Add indexes to schedule_assignments table (date already exists)
        Schema::table('schedule_assignments', function (Blueprint $table) {
            if (!$this->indexExists('schedule_assignments', 'schedule_assignments_user_id_date_index')) {
                $table->index(['user_id', 'date']);
            }
            if (!$this->indexExists('schedule_assignments', 'schedule_assignments_date_status_index')) {
                $table->index(['date', 'status']);
            }
        });

        // Add indexes to leave_requests table
        Schema::table('leave_requests', function (Blueprint $table) {
            if (!$this->indexExists('leave_requests', 'leave_requests_user_id_status_index')) {
                $table->index(['user_id', 'status']);
            }
            if (!$this->indexExists('leave_requests', 'leave_requests_start_date_index')) {
                $table->index('start_date');
            }
            if (!$this->indexExists('leave_requests', 'leave_requests_end_date_index')) {
                $table->index('end_date');
            }
        });

        // Add index for soft deletes where missing
        if (Schema::hasColumn('products', 'deleted_at') && !$this->indexExists('products', 'products_deleted_at_index')) {
            Schema::table('products', function (Blueprint $table) {
                $table->index('deleted_at');
            });
        }

        if (Schema::hasColumn('sales', 'deleted_at') && !$this->indexExists('sales', 'sales_deleted_at_index')) {
            Schema::table('sales', function (Blueprint $table) {
                $table->index('deleted_at');
            });
        }

        if (Schema::hasColumn('purchases', 'deleted_at') && !$this->indexExists('purchases', 'purchases_deleted_at_index')) {
            Schema::table('purchases', function (Blueprint $table) {
                $table->index('deleted_at');
            });
        }
    }

    /**
     * Check if an index exists on a table
     */
    private function indexExists(string $table, string $index): bool
    {
        $connection = Schema::getConnection();
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
        // Drop indexes from attendances table
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropIndex(['check_in']);
            $table->dropIndex(['status']);
            $table->dropIndex(['user_id', 'check_in']);
        });

        // Drop indexes from penalties table
        Schema::table('penalties', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropIndex(['user_id', 'status']);
        });

        // Drop indexes from sales table
        Schema::table('sales', function (Blueprint $table) {
            $table->dropIndex(['cashier_id', 'date']);
            $table->dropIndex(['date']);
        });

        // Drop indexes from notifications table
        Schema::table('notifications', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'read_at']);
        });

        // Drop indexes from schedule_assignments table
        Schema::table('schedule_assignments', function (Blueprint $table) {
            $table->dropIndex(['date']);
            $table->dropIndex(['user_id', 'date']);
            $table->dropIndex(['date', 'status']);
        });

        // Drop indexes from leave_requests table
        Schema::table('leave_requests', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'status']);
            $table->dropIndex(['start_date']);
            $table->dropIndex(['end_date']);
        });

        // Drop soft delete indexes
        if (Schema::hasColumn('products', 'deleted_at')) {
            Schema::table('products', function (Blueprint $table) {
                $table->dropIndex(['deleted_at']);
            });
        }

        if (Schema::hasColumn('sales', 'deleted_at')) {
            Schema::table('sales', function (Blueprint $table) {
                $table->dropIndex(['deleted_at']);
            });
        }

        if (Schema::hasColumn('purchases', 'deleted_at')) {
            Schema::table('purchases', function (Blueprint $table) {
                $table->dropIndex(['deleted_at']);
            });
        }
    }
};
