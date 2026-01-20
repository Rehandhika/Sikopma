<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Migration ini akan:
     * 1. Menghapus semua data user lama
     * 2. Menghapus role assignments lama
     * 3. Menghapus roles dan permissions lama
     * 
     * Setelah migration ini, jalankan seeder untuk mengisi data baru.
     */
    public function up(): void
    {
        // Disable foreign key checks temporarily
        Schema::disableForeignKeyConstraints();

        // Clear user-related data
        DB::table('model_has_roles')->truncate();
        DB::table('model_has_permissions')->truncate();
        DB::table('role_has_permissions')->truncate();
        
        // Clear roles and permissions
        DB::table('roles')->truncate();
        DB::table('permissions')->truncate();

        // Clear users (soft delete records too)
        DB::table('users')->truncate();

        // Re-enable foreign key checks
        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Cannot reverse data deletion
        // Run seeders to restore data if needed
    }
};
