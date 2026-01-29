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
        Schema::create('store_settings', function (Blueprint $table) {
            $table->id();

            // Real-Time Status
            $table->boolean('is_open')->default(false);
            $table->text('status_reason')->nullable();
            $table->timestamp('last_status_change')->nullable();

            // Mode Control
            $table->boolean('auto_status')->default(true);
            $table->boolean('manual_mode')->default(false);
            $table->boolean('manual_is_open')->default(false);
            $table->text('manual_close_reason')->nullable();
            $table->timestamp('manual_close_until')->nullable();
            $table->boolean('manual_open_override')->default(false);
            $table->foreignId('manual_set_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('manual_set_at')->nullable();

            // Operating Configuration
            $table->json('operating_hours');

            // Contact Information
            $table->string('contact_phone', 20)->nullable();
            $table->string('contact_email', 100)->nullable();
            $table->text('contact_address')->nullable();
            $table->string('contact_whatsapp', 20)->nullable();
            $table->text('about_text')->nullable();

            $table->timestamps();

            // Indexes for performance
            $table->index(['is_open', 'manual_mode'], 'idx_store_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('store_settings');
    }
};
