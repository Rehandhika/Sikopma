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
            // Safety check: only drop column if it exists
            if (Schema::hasColumn('attendances', 'check_in_photo')) {
                $table->dropColumn('check_in_photo');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            // Restore column if rollback is needed
            if (!Schema::hasColumn('attendances', 'check_in_photo')) {
                $table->string('check_in_photo')->nullable()->after('check_in');
            }
        });
    }
};
