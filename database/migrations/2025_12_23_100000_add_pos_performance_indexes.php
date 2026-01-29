<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Adds performance indexes for POS Entry System:
     * - idx_sales_date_payment: Optimize payment method breakdown queries
     * - idx_sale_items_sale_product: Optimize sale items lookup by product
     *
     * Requirements: 8.2, 8.3
     */
    public function up(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            // Composite index for daily summary with payment method breakdown
            $table->index(['date', 'payment_method'], 'idx_sales_date_payment');
        });

        Schema::table('sale_items', function (Blueprint $table) {
            // Composite index for common query pattern (sale with product lookup)
            $table->index(['sale_id', 'product_id'], 'idx_sale_items_sale_product');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropIndex('idx_sales_date_payment');
        });

        Schema::table('sale_items', function (Blueprint $table) {
            $table->dropIndex('idx_sale_items_sale_product');
        });
    }
};
