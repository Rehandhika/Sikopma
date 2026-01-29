<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Variant Options (Ukuran, Warna, Tipe)
        Schema::create('variant_options', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('slug', 100)->unique();
            $table->integer('display_order')->default(0);
            $table->timestamps();

            $table->index('display_order');
        });

        // Variant Option Values (S, M, L, Hitam, Putih, etc.)
        Schema::create('variant_option_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('variant_option_id')->constrained('variant_options')->cascadeOnDelete();
            $table->string('value', 100);
            $table->string('slug', 100);
            $table->integer('display_order')->default(0);
            $table->timestamps();

            $table->unique(['variant_option_id', 'slug'], 'uk_option_value_slug');
            $table->index(['variant_option_id', 'display_order'], 'idx_option_order');
        });

        // Product Variant Options (which options a product uses)
        Schema::create('product_variant_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('variant_option_id')->constrained('variant_options')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['product_id', 'variant_option_id'], 'uk_product_option');
        });

        // Product Variants (actual variants with SKU, price, stock)
        Schema::create('product_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->string('sku', 50)->unique();
            $table->string('variant_name', 255);
            $table->decimal('price', 12, 2);
            $table->decimal('cost_price', 12, 2)->default(0);
            $table->integer('stock')->default(0);
            $table->integer('min_stock')->default(5);
            $table->json('option_values'); // {"ukuran": {"option_id": 1, "value_id": 5, "value": "30"}}
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['product_id', 'is_active'], 'idx_product_active');
            $table->index(['stock', 'min_stock'], 'idx_variant_stock');
        });

        // Add has_variants column to products
        Schema::table('products', function (Blueprint $table) {
            if (! Schema::hasColumn('products', 'has_variants')) {
                $table->boolean('has_variants')->default(false)->after('status');
            }
        });

        // Add variant_id to sale_items for tracking which variant was sold
        Schema::table('sale_items', function (Blueprint $table) {
            if (! Schema::hasColumn('sale_items', 'variant_id')) {
                $table->foreignId('variant_id')->nullable()->after('product_id')
                    ->constrained('product_variants')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('sale_items', function (Blueprint $table) {
            if (Schema::hasColumn('sale_items', 'variant_id')) {
                $table->dropForeign(['variant_id']);
                $table->dropColumn('variant_id');
            }
        });

        Schema::table('products', function (Blueprint $table) {
            if (Schema::hasColumn('products', 'has_variants')) {
                $table->dropColumn('has_variants');
            }
        });

        Schema::dropIfExists('product_variants');
        Schema::dropIfExists('product_variant_options');
        Schema::dropIfExists('variant_option_values');
        Schema::dropIfExists('variant_options');
    }
};
