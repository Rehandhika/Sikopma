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
        Schema::create('assignment_edit_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assignment_id')->constrained('schedule_assignments')->onDelete('cascade');
            $table->foreignId('schedule_id')->constrained('schedules')->onDelete('cascade');
            $table->foreignId('edited_by')->constrained('users');
            $table->string('action'); // created, updated, deleted, swapped
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->text('reason')->nullable();
            $table->timestamps();

            // Indexes for performance
            $table->index(['assignment_id', 'created_at']);
            $table->index(['schedule_id', 'created_at']);
            $table->index('edited_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assignment_edit_history');
    }
};
