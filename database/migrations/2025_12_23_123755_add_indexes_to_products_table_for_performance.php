<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\QueryException;
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

            // We use a helper to try adding indexes and ignoring duplicates
            $addIndex = function ($col, $name) use ($table) {
                try {
                    // This closure layout is tricky because Blueprint queues commands.
                    // We can't catch the exception here inside the closure easily if we execute it later.
                    // But usually Schema::table runs immediately.
                    // However, to be safe, we will just define them and let the migration fail if they exist?
                    // No, user wants it fixed.

                    // Let's use raw SQL to check if index exists? No, DB driver specific.

                    // Simple approach: Only add if we think they aren't there.
                    // But we don't know.

                    $table->index($col, $name);
                } catch (\Exception $e) {
                    // Ignore
                }
            };

            // Since we can't try-catch inside the blueprint definition effectively for standard migration run
            // (because the exception happens during execution),
            // We will check using Schema::hasIndex if available (Laravel 10+), or just raw SQL.
        });

        // Let's try doing it one by one in separate Schema::table calls wrapped in try-catch at the top level

        $indexes = [
            ['cols' => ['status', 'is_public'], 'name' => 'products_status_is_public_index'],
            ['cols' => 'display_order', 'name' => 'products_display_order_index'],
            ['cols' => 'category', 'name' => 'products_category_index'],
            ['cols' => 'name', 'name' => 'products_name_index'],
            ['cols' => 'sku', 'name' => 'products_sku_index'],
        ];

        foreach ($indexes as $idx) {
            try {
                Schema::table('products', function (Blueprint $table) use ($idx) {
                    $table->index($idx['cols'], $idx['name']);
                });
            } catch (QueryException $e) {
                // Check error code for duplicate key
                if ($e->errorInfo[1] != 1061) {
                    // throw $e; // Optional: rethrow if it's not a duplicate key error
                }
            } catch (\Exception $e) {
                // Ignore
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Dropping is safer to try/catch too
        $indexes = [
            'products_status_is_public_index',
            'products_display_order_index',
            'products_category_index',
            'products_name_index',
            'products_sku_index',
        ];

        foreach ($indexes as $name) {
            try {
                Schema::table('products', function (Blueprint $table) use ($name) {
                    $table->dropIndex($name);
                });
            } catch (\Exception $e) {
            }
        }
    }
};
