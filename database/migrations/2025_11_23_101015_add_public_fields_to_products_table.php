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
        Schema::table('products', function (Blueprint $table) {
            if (! Schema::hasColumn('products', 'slug')) {
                $table->string('slug')->nullable()->after('name');
            }
            if (! Schema::hasColumn('products', 'image_url')) {
                $table->string('image_url')->nullable()->after('description');
            }
            if (! Schema::hasColumn('products', 'is_featured')) {
                $table->boolean('is_featured')->default(false)->after('image_url');
            }
            if (! Schema::hasColumn('products', 'is_public')) {
                $table->boolean('is_public')->default(true)->after('is_featured');
            }
            if (! Schema::hasColumn('products', 'display_order')) {
                $table->integer('display_order')->default(0)->after('is_public');
            }
        });

        // Generate slugs for existing products
        $products = \App\Models\Product::whereNull('slug')->orWhere('slug', '')->get();
        foreach ($products as $product) {
            $slug = \Illuminate\Support\Str::slug($product->name);
            $originalSlug = $slug;
            $count = 1;

            while (\App\Models\Product::where('slug', $slug)->where('id', '!=', $product->id)->exists()) {
                $slug = $originalSlug.'-'.$count;
                $count++;
            }

            $product->slug = $slug;
            $product->save();
        }

        // Now make slug unique and not nullable
        Schema::table('products', function (Blueprint $table) {
            $table->string('slug')->nullable(false)->unique()->change();
        });

        // Add indexes for performance
        try {
            Schema::table('products', function (Blueprint $table) {
                $table->index(['is_public', 'is_featured', 'display_order'], 'idx_products_public');
            });
        } catch (\Exception $e) {
            // Index might already exist, ignore
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex('idx_products_public');
            $table->dropColumn(['slug', 'image_url', 'is_featured', 'is_public', 'display_order']);
        });
    }
};
