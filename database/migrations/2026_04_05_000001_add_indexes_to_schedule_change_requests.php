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
        Schema::table('schedule_change_requests', function (Blueprint $table) {
            // Composite indexes for common queries
            $table->index(['user_id', 'status', 'created_at'], 'idx_user_status_created');
            $table->index(['user_id', 'change_type', 'status'], 'idx_user_type_status');
            $table->index(['original_assignment_id', 'status'], 'idx_assignment_status');
            $table->index(['target_id', 'status'], 'idx_target_status');
            $table->index(['status', 'created_at'], 'idx_status_created');
            $table->index(['change_type', 'status'], 'idx_type_status');
            
            // Index for monthly limit checks
            $table->index(['user_id', 'change_type', 'created_at'], 'idx_user_type_created');
        });

        Schema::table('schedule_assignments', function (Blueprint $table) {
            // Add indexes if not exists
            if (! Schema::hasIndex('schedule_assignments', 'idx_user_date_session')) {
                $table->index(['user_id', 'date', 'session'], 'idx_user_date_session');
            }
            
            if (! Schema::hasIndex('schedule_assignments', 'idx_date_session_status')) {
                $table->index(['date', 'session', 'status'], 'idx_date_session_status');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('schedule_change_requests', function (Blueprint $table) {
            $table->dropIndex('idx_user_status_created');
            $table->dropIndex('idx_user_type_status');
            $table->dropIndex('idx_assignment_status');
            $table->dropIndex('idx_target_status');
            $table->dropIndex('idx_status_created');
            $table->dropIndex('idx_type_status');
            $table->dropIndex('idx_user_type_created');
        });

        Schema::table('schedule_assignments', function (Blueprint $table) {
            if (Schema::hasIndex('schedule_assignments', 'idx_user_date_session')) {
                $table->dropIndex('idx_user_date_session');
            }
            
            if (Schema::hasIndex('schedule_assignments', 'idx_date_session_status')) {
                $table->dropIndex('idx_date_session_status');
            }
        });
    }
};
