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
        Schema::table('news', function (Blueprint $table) {
            // Make title and content nullable
            $table->string('title', 255)->nullable()->change();
            $table->text('content')->nullable()->change();

            // Add link column (optional)
            $table->string('link', 500)->nullable()->after('content');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('news', function (Blueprint $table) {
            // Revert title and content to not nullable
            $table->string('title', 255)->nullable(false)->change();
            $table->text('content')->nullable(false)->change();

            // Drop link column
            $table->dropColumn('link');
        });
    }
};
