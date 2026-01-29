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
            if (Schema::hasColumn('attendances', 'location_lat')) {
                $table->dropColumn('location_lat');
            }
            if (Schema::hasColumn('attendances', 'location_lng')) {
                $table->dropColumn('location_lng');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->decimal('location_lat', 10, 8)->nullable()->after('status');
            $table->decimal('location_lng', 11, 8)->nullable()->after('location_lat');
        });
    }
};
