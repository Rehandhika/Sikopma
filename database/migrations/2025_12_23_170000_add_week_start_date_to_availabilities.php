<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('availabilities', function (Blueprint $table) {
            $table->date('week_start_date')->nullable()->after('schedule_id');
            $table->index(['user_id', 'week_start_date']);
        });
    }

    public function down(): void
    {
        Schema::table('availabilities', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'week_start_date']);
            $table->dropColumn('week_start_date');
        });
    }
};
