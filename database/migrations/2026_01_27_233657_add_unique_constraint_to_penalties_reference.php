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
        Schema::table('penalties', function (Blueprint $table) {
            // Add unique constraint on reference_type and reference_id combination
            // This prevents duplicate penalties for the same reference
            // Using a conditional unique index that only applies when reference_type and reference_id are not null
            $table->unique(['reference_type', 'reference_id'], 'penalties_reference_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('penalties', function (Blueprint $table) {
            $table->dropUnique('penalties_reference_unique');
        });
    }
};
