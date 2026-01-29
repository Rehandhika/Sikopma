<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Mengubah konsep swap_requests dari "tukar dengan orang lain"
     * menjadi "pengajuan pindah/ubah jadwal sendiri"
     */
    public function up(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'sqlite') {
            // For SQLite, we need to recreate the table
            $this->upSqlite();
        } else {
            // For MySQL
            $this->upMysql();
        }
    }

    private function upMysql(): void
    {
        // Drop existing indexes and foreign keys first
        Schema::table('swap_requests', function (Blueprint $table) {
            // Drop foreign keys
            $table->dropForeign(['target_id']);
            $table->dropForeign(['target_assignment_id']);
        });

        // Try to drop indexes if they exist (ignore errors)
        try {
            Schema::table('swap_requests', function (Blueprint $table) {
                $table->dropIndex('idx_swaps_target_status');
            });
        } catch (\Exception $e) {
        }

        try {
            Schema::table('swap_requests', function (Blueprint $table) {
                $table->dropIndex('swap_requests_target_status_index');
            });
        } catch (\Exception $e) {
        }

        // Drop columns that are not needed
        Schema::table('swap_requests', function (Blueprint $table) {
            $table->dropColumn([
                'target_id',
                'target_assignment_id',
                'target_response',
                'target_responded_at',
            ]);
        });

        // Rename columns
        Schema::table('swap_requests', function (Blueprint $table) {
            $table->renameColumn('requester_id', 'user_id');
            $table->renameColumn('requester_assignment_id', 'original_assignment_id');
        });

        // Add new columns
        Schema::table('swap_requests', function (Blueprint $table) {
            $table->date('requested_date')->nullable()->after('original_assignment_id');
            $table->tinyInteger('requested_session')->nullable()->after('requested_date');
            $table->string('change_type', 20)->default('reschedule')->after('requested_session');
        });

        // Update status values
        DB::table('swap_requests')
            ->whereIn('status', ['target_approved', 'target_rejected', 'admin_approved', 'admin_rejected'])
            ->update(['status' => 'cancelled']);

        // Rename table
        Schema::rename('swap_requests', 'schedule_change_requests');

        // Update indexes for new structure
        Schema::table('schedule_change_requests', function (Blueprint $table) {
            $table->index(['user_id', 'status'], 'idx_schedule_change_user_status');
            $table->index(['status', 'created_at'], 'idx_schedule_change_status_created');
        });
    }

    private function upSqlite(): void
    {
        // For SQLite, create new table with new structure
        Schema::create('schedule_change_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users');
            $table->unsignedBigInteger('original_assignment_id')->nullable();
            $table->date('requested_date')->nullable();
            $table->tinyInteger('requested_session')->nullable();
            $table->string('change_type', 20)->default('reschedule');
            $table->text('reason');
            $table->string('status', 20)->default('pending');
            $table->foreignId('reviewed_by')->nullable()->constrained('users');
            $table->timestamp('reviewed_at')->nullable();
            $table->text('review_notes')->nullable();
            $table->text('admin_response')->nullable();
            $table->foreignId('admin_responded_by')->nullable()->constrained('users');
            $table->timestamp('admin_responded_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status'], 'idx_schedule_change_user_status');
            $table->index(['status', 'created_at'], 'idx_schedule_change_status_created');

            // Add foreign key with SET NULL on delete
            $table->foreign('original_assignment_id')
                ->references('id')
                ->on('schedule_assignments')
                ->onDelete('set null');
        });

        // Copy data from old table if exists
        if (Schema::hasTable('swap_requests')) {
            $oldData = DB::table('swap_requests')->get();
            foreach ($oldData as $row) {
                $status = in_array($row->status, ['target_approved', 'target_rejected', 'admin_approved', 'admin_rejected'])
                    ? 'cancelled'
                    : $row->status;

                DB::table('schedule_change_requests')->insert([
                    'id' => $row->id,
                    'user_id' => $row->requester_id,
                    'original_assignment_id' => $row->requester_assignment_id,
                    'requested_date' => null,
                    'requested_session' => null,
                    'change_type' => 'reschedule',
                    'reason' => $row->reason ?? '',
                    'status' => $status,
                    'reviewed_by' => $row->reviewed_by ?? null,
                    'reviewed_at' => $row->reviewed_at ?? null,
                    'review_notes' => $row->review_notes ?? null,
                    'created_at' => $row->created_at,
                    'updated_at' => $row->updated_at,
                ]);
            }

            // Drop old table
            Schema::dropIfExists('swap_requests');
        }
    }

    public function down(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'sqlite') {
            $this->downSqlite();
        } else {
            $this->downMysql();
        }
    }

    private function downMysql(): void
    {
        // Rename table back
        Schema::rename('schedule_change_requests', 'swap_requests');

        // Drop new indexes
        Schema::table('swap_requests', function (Blueprint $table) {
            $table->dropIndex('idx_schedule_change_user_status');
            $table->dropIndex('idx_schedule_change_status_created');
        });

        // Drop new columns
        Schema::table('swap_requests', function (Blueprint $table) {
            $table->dropColumn(['requested_date', 'requested_session', 'change_type']);
        });

        // Rename columns back
        Schema::table('swap_requests', function (Blueprint $table) {
            $table->renameColumn('user_id', 'requester_id');
            $table->renameColumn('original_assignment_id', 'requester_assignment_id');
        });

        // Add back old columns
        Schema::table('swap_requests', function (Blueprint $table) {
            $table->foreignId('target_id')->nullable()->constrained('users');
            $table->foreignId('target_assignment_id')->nullable()->constrained('schedule_assignments');
            $table->text('target_response')->nullable();
            $table->timestamp('target_responded_at')->nullable();
        });
    }

    private function downSqlite(): void
    {
        // For SQLite, recreate old table structure
        Schema::create('swap_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('requester_id')->constrained('users');
            $table->foreignId('target_id')->nullable()->constrained('users');
            $table->foreignId('requester_assignment_id')->constrained('schedule_assignments');
            $table->foreignId('target_assignment_id')->nullable()->constrained('schedule_assignments');
            $table->text('reason');
            $table->text('target_response')->nullable();
            $table->timestamp('target_responded_at')->nullable();
            $table->string('status', 20)->default('pending');
            $table->foreignId('reviewed_by')->nullable()->constrained('users');
            $table->timestamp('reviewed_at')->nullable();
            $table->text('review_notes')->nullable();
            $table->timestamps();
        });

        // Copy data back
        if (Schema::hasTable('schedule_change_requests')) {
            $data = DB::table('schedule_change_requests')->get();
            foreach ($data as $row) {
                DB::table('swap_requests')->insert([
                    'id' => $row->id,
                    'requester_id' => $row->user_id,
                    'target_id' => null,
                    'requester_assignment_id' => $row->original_assignment_id,
                    'target_assignment_id' => null,
                    'reason' => $row->reason,
                    'target_response' => null,
                    'target_responded_at' => null,
                    'status' => $row->status,
                    'reviewed_by' => $row->reviewed_by,
                    'reviewed_at' => $row->reviewed_at,
                    'review_notes' => $row->review_notes,
                    'created_at' => $row->created_at,
                    'updated_at' => $row->updated_at,
                ]);
            }

            Schema::dropIfExists('schedule_change_requests');
        }
    }
};
