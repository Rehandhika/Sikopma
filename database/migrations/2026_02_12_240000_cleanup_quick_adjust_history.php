<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Delete records where reason is NOT one of the system types (Procurement or Initial stock)
        // This removes history from the "Quick Adjust" and "Bulk Adjust" features which are now deprecated.
        DB::table('stock_adjustments')
            ->where(function($query) {
                $query->where('reason', 'not like', 'Procurement:%')
                      ->where('reason', '!=', 'Initial stock');
            })
            ->delete();
    }

    public function down(): void
    {
        // Irreversible operation
    }
};
