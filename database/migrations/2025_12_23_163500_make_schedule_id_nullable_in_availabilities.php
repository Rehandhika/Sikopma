<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        try {
            Schema::table('availabilities', function (Blueprint $table) {
                // Drop the foreign key constraint first
                $table->dropForeign(['schedule_id']);

                // Make schedule_id nullable
                $table->foreignId('schedule_id')->nullable()->change();

                // Re-add foreign key with nullable support
                $table->foreign('schedule_id')
                    ->references('id')
                    ->on('schedules')
                    ->onDelete('cascade');
            });

            // Update unique constraint to allow multiple null schedule_id per user
            Schema::table('availabilities', function (Blueprint $table) {
                $table->dropUnique(['user_id', 'schedule_id']);
                $table->index(['user_id', 'schedule_id']);
            });
        } catch (\Exception $e) {
            // If Schema builder fails, try raw SQL
            try {
                DB::statement('ALTER TABLE availabilities DROP FOREIGN KEY availabilities_schedule_id_foreign');
                DB::statement('ALTER TABLE availabilities MODIFY schedule_id BIGINT UNSIGNED NULL');
                DB::statement('ALTER TABLE availabilities ADD CONSTRAINT availabilities_schedule_id_foreign FOREIGN KEY (schedule_id) REFERENCES schedules(id) ON DELETE CASCADE');
                DB::statement('ALTER TABLE availabilities DROP INDEX availabilities_user_id_schedule_id_unique');
                DB::statement('ALTER TABLE availabilities ADD INDEX availabilities_user_id_schedule_id (user_id, schedule_id)');
            } catch (\Exception $e2) {
                // Skip if already modified or other issues
            }
        }
    }

    public function down(): void
    {
        try {
            Schema::table('availabilities', function (Blueprint $table) {
                $table->dropIndex(['user_id', 'schedule_id']);
                $table->unique(['user_id', 'schedule_id']);

                $table->dropForeign(['schedule_id']);
                $table->foreignId('schedule_id')->nullable(false)->change();
                $table->foreign('schedule_id')
                    ->references('id')
                    ->on('schedules')
                    ->onDelete('cascade');
            });
        } catch (\Exception $e) {
            // Skip on error
        }
    }
};
