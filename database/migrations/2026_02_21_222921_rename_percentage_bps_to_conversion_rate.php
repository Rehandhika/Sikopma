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
        Schema::table('shu_point_transactions', function (Blueprint $table) {
            $table->renameColumn('percentage_bps', 'conversion_rate');
        });

        Schema::table('sales', function (Blueprint $table) {
            $table->renameColumn('shu_percentage_bps', 'conversion_rate');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('shu_point_transactions', function (Blueprint $table) {
            $table->renameColumn('conversion_rate', 'percentage_bps');
        });

        Schema::table('sales', function (Blueprint $table) {
            $table->renameColumn('conversion_rate', 'shu_percentage_bps');
        });
    }
};
