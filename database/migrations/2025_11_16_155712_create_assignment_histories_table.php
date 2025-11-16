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
        Schema::create('assignment_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('schedule_id')->constrained()->onDelete('cascade');
            $table->enum('action', ['create', 'update', 'delete']);
            $table->json('assignment_data'); // Snapshot of assignment
            $table->foreignId('performed_by')->constrained('users')->onDelete('cascade');
            $table->timestamp('performed_at');
            $table->timestamps();

            $table->index('schedule_id');
            $table->index('performed_at');
            $table->index(['schedule_id', 'performed_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assignment_histories');
    }
};
