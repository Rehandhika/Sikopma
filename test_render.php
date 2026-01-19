<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

$now = Carbon::now();
$from = $now->copy()->startOfWeek(Carbon::MONDAY)->format('Y-m-d');
$to = $now->copy()->endOfWeek(Carbon::SUNDAY)->format('Y-m-d');

echo "Week range: $from to $to\n\n";

// Exact query from component
$stats = DB::table('sales')
    ->whereNull('deleted_at')
    ->whereDate('date', '>=', $from)
    ->whereDate('date', '<=', $to)
    ->selectRaw("
        COUNT(*) as total_transactions,
        COALESCE(SUM(total_amount), 0) as total_revenue,
        COALESCE(AVG(total_amount), 0) as avg_transaction,
        COALESCE(MAX(total_amount), 0) as max_transaction,
        SUM(CASE WHEN payment_method = 'cash' THEN 1 ELSE 0 END) as cash_count,
        SUM(CASE WHEN payment_method = 'transfer' THEN 1 ELSE 0 END) as transfer_count,
        SUM(CASE WHEN payment_method = 'qris' THEN 1 ELSE 0 END) as qris_count,
        SUM(CASE WHEN payment_method = 'cash' THEN total_amount ELSE 0 END) as cash_amount,
        SUM(CASE WHEN payment_method = 'transfer' THEN total_amount ELSE 0 END) as transfer_amount,
        SUM(CASE WHEN payment_method = 'qris' THEN total_amount ELSE 0 END) as qris_amount
    ")
    ->first();

echo "Stats result:\n";
print_r($stats);

// Check what format_currency returns
echo "\nformat_currency test: " . format_currency($stats->total_revenue) . "\n";

// Check raw data
echo "\nRaw sales in range:\n";
$raw = DB::table('sales')
    ->whereNull('deleted_at')
    ->whereDate('date', '>=', $from)
    ->whereDate('date', '<=', $to)
    ->get();
foreach($raw as $r) {
    echo "ID: {$r->id}, Date: {$r->date}, Amount: {$r->total_amount}\n";
}
