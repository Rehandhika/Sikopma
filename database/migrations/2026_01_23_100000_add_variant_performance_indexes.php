<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration untuk optimasi performa query variants
 * 
 * Task 1.1: Tambah database indexes untuk performa query variants
 * - Index pada product_variants(product_id, is_active, stock)
 * - Index pada product_variants(product_id, is_active, price)
 * 
 * Requirements: 1.4 - THE System SHALL menggunakan database indexes pada kolom yang sering di-query
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_variants', function (Blueprint $table) {
            // Index untuk query stock (total stock calculation, low stock filtering)
            // Digunakan saat: syncProductTotalStock, getVariantsOptimized, low stock reports
            $table->index(
                ['product_id', 'is_active', 'stock'], 
                'idx_product_variants_stock'
            );
            
            // Index untuk query price (price range calculation, sorting by price)
            // Digunakan saat: getCachedPriceRangeAttribute, catalog display
            $table->index(
                ['product_id', 'is_active', 'price'], 
                'idx_product_variants_price'
            );
        });
    }

    public function down(): void
    {
        Schema::table('product_variants', function (Blueprint $table) {
            $table->dropIndex('idx_product_variants_stock');
            $table->dropIndex('idx_product_variants_price');
        });
    }
};
