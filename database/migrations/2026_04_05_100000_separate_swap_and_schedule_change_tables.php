<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Memisahkan tabel swap_requests dan schedule_change_requests
     * - swap_requests: untuk tukar jadwal antar user
     * - schedule_change_requests: untuk reschedule/cancel jadwal sendiri
     */
    public function up(): void
    {
        // 1. Check if swap_requests table already exists (from old migration)
        if (!Schema::hasTable('swap_requests')) {
            // Create new swap_requests table
            Schema::create('swap_requests', function (Blueprint $table) {
                $table->id();
                $table->foreignId('requester_id')->constrained('users')->onDelete('cascade');
                $table->foreignId('target_id')->constrained('users')->onDelete('cascade');
                $table->foreignId('requester_assignment_id')->constrained('schedule_assignments')->onDelete('cascade');
                $table->foreignId('target_assignment_id')->constrained('schedule_assignments')->onDelete('cascade');
                $table->text('reason');
                $table->string('status', 20)->default('pending'); // pending, target_approved, target_rejected, admin_approved, admin_rejected, cancelled
                $table->text('target_response')->nullable();
                $table->timestamp('target_responded_at')->nullable();
                $table->text('admin_response')->nullable();
                $table->foreignId('admin_responded_by')->nullable()->constrained('users')->onDelete('set null');
                $table->timestamp('admin_responded_at')->nullable();
                $table->timestamp('completed_at')->nullable();
                $table->timestamps();
                $table->softDeletes();

                // Indexes
                $table->index(['requester_id', 'status'], 'idx_swap_requester_status');
                $table->index(['target_id', 'status'], 'idx_swap_target_status');
                $table->index(['status', 'created_at'], 'idx_swap_status_created');
            });
        } else {
            // Table exists, check if it has the correct structure
            // If it has old structure (requester_id column), we need to update it
            if (!Schema::hasColumn('swap_requests', 'requester_id')) {
                // Old structure detected, rename columns
                Schema::table('swap_requests', function (Blueprint $table) {
                    // Check and rename if needed
                    if (Schema::hasColumn('swap_requests', 'user_id')) {
                        $table->renameColumn('user_id', 'requester_id');
                    }
                    if (Schema::hasColumn('swap_requests', 'original_assignment_id')) {
                        $table->renameColumn('original_assignment_id', 'requester_assignment_id');
                    }
                });
            }
            
            // Add missing columns if they don't exist
            Schema::table('swap_requests', function (Blueprint $table) {
                if (!Schema::hasColumn('swap_requests', 'deleted_at')) {
                    $table->softDeletes();
                }
                if (!Schema::hasColumn('swap_requests', 'admin_response')) {
                    $table->text('admin_response')->nullable();
                }
                if (!Schema::hasColumn('swap_requests', 'admin_responded_by')) {
                    $table->foreignId('admin_responded_by')->nullable()->constrained('users')->onDelete('set null');
                }
                if (!Schema::hasColumn('swap_requests', 'admin_responded_at')) {
                    $table->timestamp('admin_responded_at')->nullable();
                }
                if (!Schema::hasColumn('swap_requests', 'completed_at')) {
                    $table->timestamp('completed_at')->nullable();
                }
            });
            
            // Add indexes if they don't exist
            try {
                Schema::table('swap_requests', function (Blueprint $table) {
                    $table->index(['requester_id', 'status'], 'idx_swap_requester_status');
                });
            } catch (\Exception $e) {
                // Index already exists
            }
            
            try {
                Schema::table('swap_requests', function (Blueprint $table) {
                    $table->index(['target_id', 'status'], 'idx_swap_target_status');
                });
            } catch (\Exception $e) {
                // Index already exists
            }
            
            try {
                Schema::table('swap_requests', function (Blueprint $table) {
                    $table->index(['status', 'created_at'], 'idx_swap_status_created');
                });
            } catch (\Exception $e) {
                // Index already exists
            }
        }

        // 2. Migrate data from schedule_change_requests where change_type = 'swap' (if any)
        if (Schema::hasTable('schedule_change_requests') && Schema::hasColumn('schedule_change_requests', 'change_type')) {
            $swapData = DB::table('schedule_change_requests')
                ->where('change_type', 'swap')
                ->get();

            foreach ($swapData as $row) {
                // Check if record already exists in swap_requests
                $exists = DB::table('swap_requests')->where('id', $row->id)->exists();
                
                if (!$exists) {
                    DB::table('swap_requests')->insert([
                        'id' => $row->id,
                        'requester_id' => $row->user_id,
                        'target_id' => $row->target_id ?? null,
                        'requester_assignment_id' => $row->original_assignment_id,
                        'target_assignment_id' => $row->target_assignment_id ?? null,
                        'reason' => $row->reason ?? '',
                        'status' => $row->status,
                        'target_response' => $row->target_response ?? null,
                        'target_responded_at' => $row->target_responded_at ?? null,
                        'admin_response' => $row->admin_response ?? null,
                        'admin_responded_by' => $row->admin_responded_by ?? null,
                        'admin_responded_at' => $row->admin_responded_at ?? null,
                        'completed_at' => $row->completed_at ?? null,
                        'created_at' => $row->created_at,
                        'updated_at' => $row->updated_at,
                    ]);
                }
            }

            // 3. Delete swap records from schedule_change_requests
            DB::table('schedule_change_requests')
                ->where('change_type', 'swap')
                ->delete();
        }

        // 4. Remove swap-related columns from schedule_change_requests (if they exist)
        if (Schema::hasTable('schedule_change_requests')) {
            if (Schema::hasColumn('schedule_change_requests', 'target_id')) {
                Schema::table('schedule_change_requests', function (Blueprint $table) {
                    $table->dropForeign(['target_id']);
                    $table->dropColumn('target_id');
                });
            }
            
            if (Schema::hasColumn('schedule_change_requests', 'target_assignment_id')) {
                Schema::table('schedule_change_requests', function (Blueprint $table) {
                    $table->dropForeign(['target_assignment_id']);
                    $table->dropColumn('target_assignment_id');
                });
            }
            
            if (Schema::hasColumn('schedule_change_requests', 'target_response')) {
                Schema::table('schedule_change_requests', function (Blueprint $table) {
                    $table->dropColumn('target_response');
                });
            }
            
            if (Schema::hasColumn('schedule_change_requests', 'target_responded_at')) {
                Schema::table('schedule_change_requests', function (Blueprint $table) {
                    $table->dropColumn('target_responded_at');
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // 1. Add back columns to schedule_change_requests
        Schema::table('schedule_change_requests', function (Blueprint $table) {
            $table->foreignId('target_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->foreignId('target_assignment_id')->nullable()->constrained('schedule_assignments')->onDelete('cascade');
            $table->text('target_response')->nullable();
            $table->timestamp('target_responded_at')->nullable();
        });

        // 2. Migrate data back from swap_requests to schedule_change_requests
        $swapData = DB::table('swap_requests')->get();

        foreach ($swapData as $row) {
            DB::table('schedule_change_requests')->insert([
                'id' => $row->id,
                'user_id' => $row->requester_id,
                'target_id' => $row->target_id,
                'original_assignment_id' => $row->requester_assignment_id,
                'target_assignment_id' => $row->target_assignment_id,
                'requested_date' => null,
                'requested_session' => null,
                'change_type' => 'swap',
                'reason' => $row->reason,
                'status' => $row->status,
                'target_response' => $row->target_response,
                'target_responded_at' => $row->target_responded_at,
                'admin_response' => $row->admin_response,
                'admin_responded_by' => $row->admin_responded_by,
                'admin_responded_at' => $row->admin_responded_at,
                'completed_at' => $row->completed_at,
                'created_at' => $row->created_at,
                'updated_at' => $row->updated_at,
            ]);
        }

        // 3. Drop swap_requests table
        Schema::dropIfExists('swap_requests');
    }
};
