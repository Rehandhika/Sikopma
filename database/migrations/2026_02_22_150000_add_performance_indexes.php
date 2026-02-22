<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Consolidated Performance Indexes Migration
 * 
 * Consolidates all performance indexes into a single migration.
 * Replaces 11+ separate index migrations.
 */
return new class extends Migration
{
    public function up(): void
    {
        try { $this->addCoreIndexes(); } catch (\Exception $e) {}
        try { $this->addScheduleIndexes(); } catch (\Exception $e) {}
        try { $this->addAttendanceIndexes(); } catch (\Exception $e) {}
        try { $this->addPosIndexes(); } catch (\Exception $e) {}
        try { $this->addInventoryIndexes(); } catch (\Exception $e) {}
        try { $this->addContentIndexes(); } catch (\Exception $e) {}
    }

    public function down(): void
    {
        $this->dropCoreIndexes();
        $this->dropScheduleIndexes();
        $this->dropAttendanceIndexes();
        $this->dropPosIndexes();
        $this->dropInventoryIndexes();
        $this->dropContentIndexes();
    }

    private function addCoreIndexes(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->index('status', 'idx_users_status');
            $table->index('nim', 'idx_users_nim');
            $table->index(['status', 'created_at'], 'idx_users_status_created');
        });
    }

    private function addScheduleIndexes(): void
    {
        Schema::table('schedules', function (Blueprint $table) {
            $table->index('week_start_date', 'idx_schedules_week_start');
            $table->index(['status', 'published_at'], 'idx_schedules_status_published');
        });

        Schema::table('schedule_assignments', function (Blueprint $table) {
            $table->index(['user_id', 'date'], 'idx_sa_user_date');
            $table->index(['user_id', 'date', 'session'], 'idx_sa_user_date_session');
            $table->index(['schedule_id', 'status'], 'idx_sa_schedule_status');
        });

        Schema::table('availabilities', function (Blueprint $table) {
            $table->index(['user_id', 'week_start_date'], 'idx_avail_user_week');
            $table->index('status', 'idx_avail_status');
        });
    }

    private function addAttendanceIndexes(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->index(['user_id', 'date'], 'idx_att_user_date');
            $table->index(['date', 'status'], 'idx_att_date_status');
        });

        Schema::table('penalties', function (Blueprint $table) {
            $table->index(['user_id', 'status'], 'idx_penalties_user_status');
            $table->index('date', 'idx_penalties_date');
        });
    }

    private function addPosIndexes(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->index('sku', 'idx_products_sku');
            $table->index('name', 'idx_products_name');
            $table->index(['category', 'status'], 'idx_products_category_status');
            $table->index(['status', 'is_public'], 'idx_products_status_is_public');
        });

        Schema::table('sales', function (Blueprint $table) {
            $table->index('date', 'idx_sales_date');
            $table->index(['cashier_id', 'date'], 'idx_sales_cashier_date');
            $table->index('payment_method', 'idx_sales_payment_method');
        });

        Schema::table('sale_items', function (Blueprint $table) {
            $table->index(['sale_id', 'product_id'], 'idx_si_sale_product');
        });

        Schema::table('product_variants', function (Blueprint $table) {
            $table->index(['product_id', 'is_active'], 'idx_pv_product_active');
        });

        Schema::table('shu_point_transactions', function (Blueprint $table) {
            $table->index(['student_id', 'created_at'], 'idx_shu_student_date');
        });
    }

    private function addInventoryIndexes(): void
    {
        Schema::table('purchases', function (Blueprint $table) {
            $table->index('date', 'idx_purchases_date');
            $table->index('user_id', 'idx_purchases_user');
        });

        Schema::table('purchase_items', function (Blueprint $table) {
            $table->index(['purchase_id', 'product_id'], 'idx_pi_purchase_product');
        });

        Schema::table('stock_adjustments', function (Blueprint $table) {
            $table->index(['user_id', 'product_id'], 'idx_sa_user_product');
            $table->index('type', 'idx_sa_type');
        });
    }

    private function addContentIndexes(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->index(['user_id', 'is_read'], 'idx_notif_user_read');
        });

        Schema::table('banners', function (Blueprint $table) {
            $table->index(['is_active', 'priority'], 'idx_banners_active_priority');
            $table->index('created_by', 'idx_banners_created_by');
        });

        Schema::table('news', function (Blueprint $table) {
            $table->index(['is_active', 'published_at'], 'idx_news_active_published');
            $table->index('created_by', 'idx_news_created_by');
        });

        Schema::table('activity_logs', function (Blueprint $table) {
            $table->index('user_id', 'idx_al_user');
        });

        Schema::table('login_histories', function (Blueprint $table) {
            $table->index('user_id', 'idx_lh_user');
        });
    }

    private function dropCoreIndexes(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('idx_users_status');
            $table->dropIndex('idx_users_nim');
            $table->dropIndex('idx_users_status_created');
        });
    }

    private function dropScheduleIndexes(): void
    {
        Schema::table('schedules', function (Blueprint $table) {
            $table->dropIndex('idx_schedules_week_start');
            $table->dropIndex('idx_schedules_status_published');
        });
        Schema::table('schedule_assignments', function (Blueprint $table) {
            $table->dropIndex('idx_sa_user_date');
            $table->dropIndex('idx_sa_user_date_session');
            $table->dropIndex('idx_sa_schedule_status');
        });
        Schema::table('availabilities', function (Blueprint $table) {
            $table->dropIndex('idx_avail_user_week');
            $table->dropIndex('idx_avail_status');
        });
    }

    private function dropAttendanceIndexes(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropIndex('idx_att_user_date');
            $table->dropIndex('idx_att_date_status');
        });
        Schema::table('penalties', function (Blueprint $table) {
            $table->dropIndex('idx_penalties_user_status');
            $table->dropIndex('idx_penalties_date');
        });
    }

    private function dropPosIndexes(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex('idx_products_sku');
            $table->dropIndex('idx_products_name');
            $table->dropIndex('idx_products_category_status');
            $table->dropIndex('idx_products_status_is_public');
        });
        Schema::table('sales', function (Blueprint $table) {
            $table->dropIndex('idx_sales_date');
            $table->dropIndex('idx_sales_cashier_date');
            $table->dropIndex('idx_sales_payment_method');
        });
        Schema::table('sale_items', function (Blueprint $table) {
            $table->dropIndex('idx_si_sale_product');
        });
        Schema::table('product_variants', function (Blueprint $table) {
            $table->dropIndex('idx_pv_product_active');
        });
        Schema::table('shu_point_transactions', function (Blueprint $table) {
            $table->dropIndex('idx_shu_student_date');
        });
    }

    private function dropInventoryIndexes(): void
    {
        Schema::table('purchases', function (Blueprint $table) {
            $table->dropIndex('idx_purchases_date');
            $table->dropIndex('idx_purchases_user');
        });
        Schema::table('purchase_items', function (Blueprint $table) {
            $table->dropIndex('idx_pi_purchase_product');
        });
        Schema::table('stock_adjustments', function (Blueprint $table) {
            $table->dropIndex('idx_sa_user_product');
            $table->dropIndex('idx_sa_type');
        });
    }

    private function dropContentIndexes(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->dropIndex('idx_notif_user_read');
        });
        Schema::table('banners', function (Blueprint $table) {
            $table->dropIndex('idx_banners_active_priority');
            $table->dropIndex('idx_banners_created_by');
        });
        Schema::table('news', function (Blueprint $table) {
            $table->dropIndex('idx_news_active_published');
            $table->dropIndex('idx_news_created_by');
        });
        Schema::table('activity_logs', function (Blueprint $table) {
            $table->dropIndex('idx_al_user');
        });
        Schema::table('login_histories', function (Blueprint $table) {
            $table->dropIndex('idx_lh_user');
        });
    }
};
