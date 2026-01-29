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
        Schema::table('schedule_assignments', function (Blueprint $table) {
            // Composite index for slot queries (date, session)
            $table->index(['date', 'session'], 'idx_date_session');

            // Composite index for user slot queries
            $table->index(['user_id', 'date', 'session'], 'idx_user_date_session');

            // Composite index for schedule status queries
            $table->index(['schedule_id', 'status'], 'idx_schedule_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('schedule_assignments', function (Blueprint $table) {
            $table->dropIndex('idx_date_session');
            $table->dropIndex('idx_user_date_session');
            $table->dropIndex('idx_schedule_status');
        });
    }
};
