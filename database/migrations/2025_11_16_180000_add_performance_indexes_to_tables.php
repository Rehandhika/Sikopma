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
        Schema::table('attendances', function (Blueprint $table) {
            // Performance indexes for attendance queries
            $table->index(['user_id', 'date'], 'idx_attendances_user_date');
            $table->index(['schedule_assignment_id', 'status'], 'idx_attendances_schedule_status');
            $table->index(['date', 'status'], 'idx_attendances_date_status');
            $table->index(['check_in', 'status'], 'idx_attendances_checkin_status');
        });

        Schema::table('schedule_assignments', function (Blueprint $table) {
            // Performance indexes for schedule queries
            $table->index(['user_id', 'date', 'status'], 'idx_schedules_user_date_status');
            $table->index(['date', 'session', 'status'], 'idx_schedules_date_session_status');
            $table->index(['schedule_id', 'date'], 'idx_schedules_schedule_date');
        });

        Schema::table('swap_requests', function (Blueprint $table) {
            // Performance indexes for swap queries
            $table->index(['requester_id', 'status'], 'idx_swaps_requester_status');
            $table->index(['target_id', 'status'], 'idx_swaps_target_status');
            $table->index(['status', 'created_at'], 'idx_swaps_status_created');
        });

        Schema::table('leave_requests', function (Blueprint $table) {
            // Performance indexes for leave queries
            $table->index(['user_id', 'status'], 'idx_leaves_user_status');
            $table->index(['status', 'created_at'], 'idx_leaves_status_created');
            $table->index(['date_from', 'date_to', 'status'], 'idx_leaves_dates_status');
        });

        Schema::table('notifications', function (Blueprint $table) {
            // Performance indexes for notification queries
            $table->index(['user_id', 'read_at'], 'idx_notifications_user_read');
            $table->index(['created_at', 'read_at'], 'idx_notifications_created_read');
            $table->index(['type', 'created_at'], 'idx_notifications_type_created');
        });

        Schema::table('penalties', function (Blueprint $table) {
            // Performance indexes for penalty queries
            $table->index(['user_id', 'status'], 'idx_penalties_user_status');
            $table->index(['status', 'created_at'], 'idx_penalties_status_created');
            $table->index(['type', 'status'], 'idx_penalties_type_status');
        });

        Schema::table('sales', function (Blueprint $table) {
            // Performance indexes for sales queries
            $table->index(['created_at', 'status'], 'idx_sales_created_status');
            $table->index(['user_id', 'created_at'], 'idx_sales_user_created');
            $table->index(['total_amount', 'created_at'], 'idx_sales_amount_created');
        });

        Schema::table('products', function (Blueprint $table) {
            // Performance indexes for product queries
            $table->index(['stock', 'min_stock'], 'idx_products_stock_min');
            $table->index(['category', 'status'], 'idx_products_category_status');
            $table->index(['sku'], 'idx_products_sku');
        });

        Schema::table('users', function (Blueprint $table) {
            // Performance indexes for user queries
            $table->index(['status', 'created_at'], 'idx_users_status_created');
            $table->index(['nim'], 'idx_users_nim');
            $table->index(['email'], 'idx_users_email');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropIndex('idx_attendances_user_date');
            $table->dropIndex('idx_attendances_schedule_status');
            $table->dropIndex('idx_attendances_date_status');
            $table->dropIndex('idx_attendances_checkin_status');
        });

        Schema::table('schedule_assignments', function (Blueprint $table) {
            $table->dropIndex('idx_schedules_user_date_status');
            $table->dropIndex('idx_schedules_date_session_status');
            $table->dropIndex('idx_schedules_schedule_date');
        });

        Schema::table('swap_requests', function (Blueprint $table) {
            $table->dropIndex('idx_swaps_requester_status');
            $table->dropIndex('idx_swaps_target_status');
            $table->dropIndex('idx_swaps_status_created');
        });

        Schema::table('leave_requests', function (Blueprint $table) {
            $table->dropIndex('idx_leaves_user_status');
            $table->dropIndex('idx_leaves_status_created');
            $table->dropIndex('idx_leaves_dates_status');
        });

        Schema::table('notifications', function (Blueprint $table) {
            $table->dropIndex('idx_notifications_user_read');
            $table->dropIndex('idx_notifications_created_read');
            $table->dropIndex('idx_notifications_type_created');
        });

        Schema::table('penalties', function (Blueprint $table) {
            $table->dropIndex('idx_penalties_user_status');
            $table->dropIndex('idx_penalties_status_created');
            $table->dropIndex('idx_penalties_type_status');
        });

        Schema::table('sales', function (Blueprint $table) {
            $table->dropIndex('idx_sales_created_status');
            $table->dropIndex('idx_sales_user_created');
            $table->dropIndex('idx_sales_amount_created');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex('idx_products_stock_min');
            $table->dropIndex('idx_products_category_status');
            $table->dropIndex('idx_products_sku');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('idx_users_status_created');
            $table->dropIndex('idx_users_nim');
            $table->dropIndex('idx_users_email');
        });
    }
};
