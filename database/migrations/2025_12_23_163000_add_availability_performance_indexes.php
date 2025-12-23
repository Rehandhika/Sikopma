<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('availabilities', function (Blueprint $table) {
            $table->index(['user_id', 'created_at'], 'avail_user_created_idx');
        });

        Schema::table('availability_details', function (Blueprint $table) {
            $table->index(['availability_id', 'is_available'], 'avail_detail_avail_available_idx');
        });
    }

    public function down(): void
    {
        Schema::table('availabilities', function (Blueprint $table) {
            $table->dropIndex('avail_user_created_idx');
        });

        Schema::table('availability_details', function (Blueprint $table) {
            $table->dropIndex('avail_detail_avail_available_idx');
        });
    }
};
