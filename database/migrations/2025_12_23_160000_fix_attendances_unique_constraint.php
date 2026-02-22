<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Fix: Allow user to have multiple attendances per day for different schedules.
     * Old constraint: user_id + date (wrong - user can only check-in once per day)
     * New constraint: user_id + date + schedule_assignment_id (correct - user can check-in for each schedule)
     */
    public function up(): void
    {
        try {
            Schema::table('attendances', function (Blueprint $table) {
                // Drop old unique constraint
                $table->dropUnique(['user_id', 'date']);

                // Add new unique constraint that includes schedule_assignment_id
                $table->unique(['user_id', 'date', 'schedule_assignment_id'], 'attendances_user_date_schedule_unique');
            });
        } catch (\Exception $e) {
            // If dropUnique fails due to FK constraint, try raw SQL
            try {
                DB::statement('ALTER TABLE attendances DROP INDEX attendances_user_id_date_unique');
                DB::statement('ALTER TABLE attendances ADD UNIQUE KEY attendances_user_date_schedule_unique (user_id, date, schedule_assignment_id)');
            } catch (\Exception $e2) {
                // Skip if already exists or other issues
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        try {
            Schema::table('attendances', function (Blueprint $table) {
                $table->dropUnique('attendances_user_date_schedule_unique');
                $table->unique(['user_id', 'date']);
            });
        } catch (\Exception $e) {
            // Skip on error
        }
    }
};
