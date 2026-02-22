<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // banners.created_by - skip if column is NOT NULL (can't SET NULL on NOT NULL column)
        if (Schema::hasTable('banners') && Schema::hasColumn('banners', 'created_by')) {
            try {
                $column = DB::select("SHOW COLUMNS FROM banners WHERE Field = 'created_by'")[0];
                if ($column->Null === 'YES') {
                    DB::statement('ALTER TABLE banners DROP FOREIGN KEY banners_created_by_foreign');
                }
                DB::statement('ALTER TABLE banners ADD CONSTRAINT banners_created_by_foreign FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL');
            } catch (\Exception $e) {
                // Skip if constraint already exists or other issues
            }
        }

        // news.created_by
        if (Schema::hasTable('news') && Schema::hasColumn('news', 'created_by')) {
            try {
                $column = DB::select("SHOW COLUMNS FROM news WHERE Field = 'created_by'")[0];
                if ($column->Null === 'YES') {
                    DB::statement('ALTER TABLE news DROP FOREIGN KEY news_created_by_foreign');
                }
                DB::statement('ALTER TABLE news ADD CONSTRAINT news_created_by_foreign FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL');
            } catch (\Exception $e) {
                // Skip if constraint already exists or other issues
            }
        }

        // leave_affected_schedules
        if (Schema::hasTable('leave_affected_schedules')) {
            try {
                if (Schema::hasColumn('leave_affected_schedules', 'schedule_assignment_id')) {
                    DB::statement('ALTER TABLE leave_affected_schedules DROP FOREIGN KEY leave_affected_schedules_schedule_assignment_id_foreign');
                }
            } catch (\Exception $e) {}
            
            try {
                if (Schema::hasColumn('leave_affected_schedules', 'schedule_assignment_id')) {
                    DB::statement('ALTER TABLE leave_affected_schedules ADD CONSTRAINT leave_affected_schedules_schedule_assignment_id_foreign FOREIGN KEY (schedule_assignment_id) REFERENCES schedule_assignments(id) ON DELETE CASCADE');
                }
            } catch (\Exception $e) {}
            
            try {
                if (Schema::hasColumn('leave_affected_schedules', 'replacement_user_id')) {
                    DB::statement('ALTER TABLE leave_affected_schedules DROP FOREIGN KEY leave_affected_schedules_replacement_user_id_foreign');
                }
            } catch (\Exception $e) {}
            
            try {
                if (Schema::hasColumn('leave_affected_schedules', 'replacement_user_id')) {
                    DB::statement('ALTER TABLE leave_affected_schedules ADD CONSTRAINT leave_affected_schedules_replacement_user_id_foreign FOREIGN KEY (replacement_user_id) REFERENCES users(id) ON DELETE SET NULL');
                }
            } catch (\Exception $e) {}
        }
    }

    public function down(): void
    {
        // Revert to default FK actions (restrict)
        if (Schema::hasTable('banners') && Schema::hasColumn('banners', 'created_by')) {
            try {
                DB::statement('ALTER TABLE banners DROP FOREIGN KEY banners_created_by_foreign');
                DB::statement('ALTER TABLE banners ADD CONSTRAINT banners_created_by_foreign FOREIGN KEY (created_by) REFERENCES users(id)');
            } catch (\Exception $e) {}
        }
        
        if (Schema::hasTable('news') && Schema::hasColumn('news', 'created_by')) {
            try {
                DB::statement('ALTER TABLE news DROP FOREIGN KEY news_created_by_foreign');
                DB::statement('ALTER TABLE news ADD CONSTRAINT news_created_by_foreign FOREIGN KEY (created_by) REFERENCES users(id)');
            } catch (\Exception $e) {}
        }
        
        if (Schema::hasTable('leave_affected_schedules')) {
            try {
                DB::statement('ALTER TABLE leave_affected_schedules DROP FOREIGN KEY leave_affected_schedules_schedule_assignment_id_foreign');
                DB::statement('ALTER TABLE leave_affected_schedules ADD CONSTRAINT leave_affected_schedules_schedule_assignment_id_foreign FOREIGN KEY (schedule_assignment_id) REFERENCES schedule_assignments(id)');
            } catch (\Exception $e) {}
            
            try {
                DB::statement('ALTER TABLE leave_affected_schedules DROP FOREIGN KEY leave_affected_schedules_replacement_user_id_foreign');
                DB::statement('ALTER TABLE leave_affected_schedules ADD CONSTRAINT leave_affected_schedules_replacement_user_id_foreign FOREIGN KEY (replacement_user_id) REFERENCES users(id)');
            } catch (\Exception $e) {}
        }
    }
};
