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
        Schema::table('attendances', function (Blueprint $table) {
            // Add foreign key constraints if not exists
            if (!Schema::hasColumn('attendances', 'user_id_foreign')) {
                $table->foreign('user_id')
                      ->references('id')
                      ->on('users')
                      ->onDelete('cascade')
                      ->onUpdate('cascade');
            }
            
            if (!Schema::hasColumn('attendances', 'schedule_assignment_id_foreign')) {
                $table->foreign('schedule_assignment_id')
                      ->references('id')
                      ->on('schedule_assignments')
                      ->onDelete('cascade')
                      ->onUpdate('cascade');
            }
        });

        Schema::table('schedule_assignments', function (Blueprint $table) {
            if (!Schema::hasColumn('schedule_assignments', 'user_id_foreign')) {
                $table->foreign('user_id')
                      ->references('id')
                      ->on('users')
                      ->onDelete('cascade')
                      ->onUpdate('cascade');
            }
            
            if (!Schema::hasColumn('schedule_assignments', 'schedule_id_foreign')) {
                $table->foreign('schedule_id')
                      ->references('id')
                      ->on('schedules')
                      ->onDelete('cascade')
                      ->onUpdate('cascade');
            }
        });

        Schema::table('swap_requests', function (Blueprint $table) {
            if (!Schema::hasColumn('swap_requests', 'requester_id_foreign')) {
                $table->foreign('requester_id')
                      ->references('id')
                      ->on('users')
                      ->onDelete('cascade')
                      ->onUpdate('cascade');
            }
            
            if (!Schema::hasColumn('swap_requests', 'target_id_foreign')) {
                $table->foreign('target_id')
                      ->references('id')
                      ->on('users')
                      ->onDelete('cascade')
                      ->onUpdate('cascade');
            }
            
            if (!Schema::hasColumn('swap_requests', 'original_schedule_assignment_id_foreign')) {
                $table->foreign('original_schedule_assignment_id')
                      ->references('id')
                      ->on('schedule_assignments')
                      ->onDelete('cascade')
                      ->onUpdate('cascade');
            }
            
            if (!Schema::hasColumn('swap_requests', 'target_schedule_assignment_id_foreign')) {
                $table->foreign('target_schedule_assignment_id')
                      ->references('id')
                      ->on('schedule_assignments')
                      ->onDelete('set null')
                      ->onUpdate('cascade');
            }
        });

        Schema::table('leave_requests', function (Blueprint $table) {
            if (!Schema::hasColumn('leave_requests', 'user_id_foreign')) {
                $table->foreign('user_id')
                      ->references('id')
                      ->on('users')
                      ->onDelete('cascade')
                      ->onUpdate('cascade');
            }
            
            if (!Schema::hasColumn('leave_requests', 'reviewer_id_foreign')) {
                $table->foreign('reviewer_id')
                      ->references('id')
                      ->on('users')
                      ->onDelete('set null')
                      ->onUpdate('cascade');
            }
            
            if (!Schema::hasColumn('leave_requests', 'leave_type_id_foreign')) {
                $table->foreign('leave_type_id')
                      ->references('id')
                      ->on('leave_types')
                      ->onDelete('restrict')
                      ->onUpdate('cascade');
            }
        });

        Schema::table('leave_affected_schedules', function (Blueprint $table) {
            if (!Schema::hasColumn('leave_affected_schedules', 'leave_request_id_foreign')) {
                $table->foreign('leave_request_id')
                      ->references('id')
                      ->on('leave_requests')
                      ->onDelete('cascade')
                      ->onUpdate('cascade');
            }
            
            if (!Schema::hasColumn('leave_affected_schedules', 'schedule_assignment_id_foreign')) {
                $table->foreign('schedule_assignment_id')
                      ->references('id')
                      ->on('schedule_assignments')
                      ->onDelete('cascade')
                      ->onUpdate('cascade');
            }
        });

        Schema::table('penalties', function (Blueprint $table) {
            if (!Schema::hasColumn('penalties', 'user_id_foreign')) {
                $table->foreign('user_id')
                      ->references('id')
                      ->on('users')
                      ->onDelete('cascade')
                      ->onUpdate('cascade');
            }
            
            if (!Schema::hasColumn('penalties', 'penalty_type_id_foreign')) {
                $table->foreign('penalty_type_id')
                      ->references('id')
                      ->on('penalty_types')
                      ->onDelete('restrict')
                      ->onUpdate('cascade');
            }
        });

        Schema::table('sales', function (Blueprint $table) {
            if (!Schema::hasColumn('sales', 'user_id_foreign')) {
                $table->foreign('user_id')
                      ->references('id')
                      ->on('users')
                      ->onDelete('cascade')
                      ->onUpdate('cascade');
            }
        });

        Schema::table('sale_items', function (Blueprint $table) {
            if (!Schema::hasColumn('sale_items', 'sale_id_foreign')) {
                $table->foreign('sale_id')
                      ->references('id')
                      ->on('sales')
                      ->onDelete('cascade')
                      ->onUpdate('cascade');
            }
            
            if (!Schema::hasColumn('sale_items', 'product_id_foreign')) {
                $table->foreign('product_id')
                      ->references('id')
                      ->on('products')
                      ->onDelete('cascade')
                      ->onUpdate('cascade');
            }
        });

        Schema::table('purchases', function (Blueprint $table) {
            if (!Schema::hasColumn('purchases', 'user_id_foreign')) {
                $table->foreign('user_id')
                      ->references('id')
                      ->on('users')
                      ->onDelete('cascade')
                      ->onUpdate('cascade');
            }
        });

        Schema::table('purchase_items', function (Blueprint $table) {
            if (!Schema::hasColumn('purchase_items', 'purchase_id_foreign')) {
                $table->foreign('purchase_id')
                      ->references('id')
                      ->on('purchases')
                      ->onDelete('cascade')
                      ->onUpdate('cascade');
            }
            
            if (!Schema::hasColumn('purchase_items', 'product_id_foreign')) {
                $table->foreign('product_id')
                      ->references('id')
                      ->on('products')
                      ->onDelete('cascade')
                      ->onUpdate('cascade');
            }
        });

        Schema::table('stock_adjustments', function (Blueprint $table) {
            if (!Schema::hasColumn('stock_adjustments', 'user_id_foreign')) {
                $table->foreign('user_id')
                      ->references('id')
                      ->on('users')
                      ->onDelete('cascade')
                      ->onUpdate('cascade');
            }
            
            if (!Schema::hasColumn('stock_adjustments', 'product_id_foreign')) {
                $table->foreign('product_id')
                      ->references('id')
                      ->on('products')
                      ->onDelete('cascade')
                      ->onUpdate('cascade');
            }
        });

        Schema::table('notifications', function (Blueprint $table) {
            if (!Schema::hasColumn('notifications', 'user_id_foreign')) {
                $table->foreign('user_id')
                      ->references('id')
                      ->on('users')
                      ->onDelete('cascade')
                      ->onUpdate('cascade');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropForeign(['schedule_assignment_id']);
        });

        Schema::table('schedule_assignments', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropForeign(['schedule_id']);
        });

        Schema::table('swap_requests', function (Blueprint $table) {
            $table->dropForeign(['requester_id']);
            $table->dropForeign(['target_id']);
            $table->dropForeign(['original_schedule_assignment_id']);
            $table->dropForeign(['target_schedule_assignment_id']);
        });

        Schema::table('leave_requests', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropForeign(['reviewer_id']);
            $table->dropForeign(['leave_type_id']);
        });

        Schema::table('leave_affected_schedules', function (Blueprint $table) {
            $table->dropForeign(['leave_request_id']);
            $table->dropForeign(['schedule_assignment_id']);
        });

        Schema::table('penalties', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropForeign(['penalty_type_id']);
        });

        Schema::table('sales', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
        });

        Schema::table('sale_items', function (Blueprint $table) {
            $table->dropForeign(['sale_id']);
            $table->dropForeign(['product_id']);
        });

        Schema::table('purchases', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
        });

        Schema::table('purchase_items', function (Blueprint $table) {
            $table->dropForeign(['purchase_id']);
            $table->dropForeign(['product_id']);
        });

        Schema::table('stock_adjustments', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropForeign(['product_id']);
        });

        Schema::table('notifications', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
        });
    }
};
