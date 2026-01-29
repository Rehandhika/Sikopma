<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
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
    }

    public function down(): void
    {
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
    }
};
