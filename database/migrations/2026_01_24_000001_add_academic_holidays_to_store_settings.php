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
        Schema::table('store_settings', function (Blueprint $table) {
            // Next Open Info Mode: 'default' or 'custom'
            $table->string('next_open_mode', 20)->default('default')->after('operating_hours');

            // Custom message when store is closed (for academic holidays, etc.)
            $table->text('custom_closed_message')->nullable()->after('next_open_mode');

            // Custom next open date (for academic holidays)
            $table->date('custom_next_open_date')->nullable()->after('custom_closed_message');

            // Academic holiday period
            $table->date('academic_holiday_start')->nullable()->after('custom_next_open_date');
            $table->date('academic_holiday_end')->nullable()->after('academic_holiday_start');
            $table->string('academic_holiday_name', 100)->nullable()->after('academic_holiday_end');
        });

        // Create academic_holidays table for recurring/scheduled holidays
        Schema::create('academic_holidays', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->date('start_date');
            $table->date('end_date');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();

            $table->index(['start_date', 'end_date', 'is_active'], 'idx_holiday_dates');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('store_settings', function (Blueprint $table) {
            $table->dropColumn([
                'next_open_mode',
                'custom_closed_message',
                'custom_next_open_date',
                'academic_holiday_start',
                'academic_holiday_end',
                'academic_holiday_name',
            ]);
        });

        Schema::dropIfExists('academic_holidays');
    }
};
