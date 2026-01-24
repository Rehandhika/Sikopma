<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration untuk support variant-level stock adjustments
 * 
 * Task 1.2: Update stock_adjustments table untuk support variant_id
 * - Tambah kolom variant_id dengan foreign key
 * - Tambah index untuk variant adjustments
 * 
 * Requirements: 6.1 - THE Stock_Adjustment SHALL support variant-level adjustments
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stock_adjustments', function (Blueprint $table) {
            // Tambah kolom variant_id setelah product_id
            // Nullable karena adjustment bisa untuk product tanpa variant
            $table->foreignId('variant_id')
                ->nullable()
                ->after('product_id')
                ->constrained('product_variants')
                ->nullOnDelete();
            
            // Index untuk query adjustment history per variant
            // Digunakan saat: getVariantAdjustmentHistory, variant stock reports
            $table->index('variant_id', 'idx_stock_adj_variant');
            
            // Composite index untuk query adjustment by product and variant
            $table->index(
                ['product_id', 'variant_id'], 
                'idx_stock_adj_product_variant'
            );
        });
    }

    public function down(): void
    {
        Schema::table('stock_adjustments', function (Blueprint $table) {
            $table->dropForeign(['variant_id']);
            $table->dropIndex('idx_stock_adj_variant');
            $table->dropIndex('idx_stock_adj_product_variant');
            $table->dropColumn('variant_id');
        });
    }
};
