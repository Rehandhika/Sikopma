<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

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
        Schema::table('attendances', function (Blueprint $table) {
            // Drop old unique constraint
            $table->dropUnique(['user_id', 'date']);

            // Add new unique constraint that includes schedule_assignment_id
            $table->unique(['user_id', 'date', 'schedule_assignment_id'], 'attendances_user_date_schedule_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            // Drop new constraint
            $table->dropUnique('attendances_user_date_schedule_unique');

            // Restore old constraint
            $table->unique(['user_id', 'date']);
        });
    }
};
