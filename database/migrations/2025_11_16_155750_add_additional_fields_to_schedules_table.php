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
        Schema::table('schedules', function (Blueprint $table) {
            $table->foreignId('published_by')->nullable()->after('published_at')->constrained('users')->onDelete('set null');
            $table->unsignedInteger('total_slots')->default(12)->after('published_by'); // 4 days × 3 sessions
            $table->unsignedInteger('filled_slots')->default(0)->after('total_slots');
            $table->decimal('coverage_rate', 5, 2)->default(0)->after('filled_slots'); // Percentage

            $table->index('coverage_rate');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('schedules', function (Blueprint $table) {
            $table->dropForeign(['published_by']);
            $table->dropIndex(['coverage_rate']);
            $table->dropColumn(['published_by', 'total_slots', 'filled_slots', 'coverage_rate']);
        });
    }
};
