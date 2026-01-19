<?php

namespace App\Livewire\Report;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Title;
use Livewire\Attributes\Computed;
use App\Models\Sale;
use Illuminate\Support\Facades\DB;

#[Title('Laporan Penjualan')]
class SalesReport extends Component
{
    use WithPagination;

    public string $dateFrom = '';
    public string $dateTo = '';
    public string $period = 'month';
    public ?int $selectedSaleId = null;

    public function mount()
    {
        $this->setPeriod('month');
    }

    public function setPeriod(string $period)
    {
        $this->period = $period;
        $now = now();

        [$this->dateFrom, $this->dateTo] = match ($period) {
            'today' => [$now->format('Y-m-d'), $now->format('Y-m-d')],
            'week' => [$now->copy()->startOfWeek()->format('Y-m-d'), $now->copy()->endOfWeek()->format('Y-m-d')],
            'month' => [$now->copy()->startOfMonth()->format('Y-m-d'), $now->copy()->endOfMonth()->format('Y-m-d')],
            'year' => [$now->copy()->startOfYear()->format('Y-m-d'), $now->copy()->endOfYear()->format('Y-m-d')],
            default => [$this->dateFrom, $this->dateTo],
        };

        $this->resetPage();
    }

    public function updatedDateFrom()
    {
        $this->updatePeriodBasedOnDates();
        $this->resetPage();
    }

    public function updatedDateTo()
    {
        $this->updatePeriodBasedOnDates();
        $this->resetPage();
    }

    private function updatePeriodBasedOnDates()
    {
        if (empty($this->dateFrom) || empty($this->dateTo)) {
            $this->period = 'custom';
            return;
        }

        $now = now();
        $dateFrom = \Carbon\Carbon::parse($this->dateFrom);
        $dateTo = \Carbon\Carbon::parse($this->dateTo);

        // Check if dates match predefined periods
        if ($dateFrom->format('Y-m-d') === $now->format('Y-m-d') && 
            $dateTo->format('Y-m-d') === $now->format('Y-m-d')) {
            $this->period = 'today';
        } elseif ($dateFrom->format('Y-m-d') === $now->copy()->startOfWeek()->format('Y-m-d') && 
                  $dateTo->format('Y-m-d') === $now->copy()->endOfWeek()->format('Y-m-d')) {
            $this->period = 'week';
        } elseif ($dateFrom->format('Y-m-d') === $now->copy()->startOfMonth()->format('Y-m-d') && 
                  $dateTo->format('Y-m-d') === $now->copy()->endOfMonth()->format('Y-m-d')) {
            $this->period = 'month';
        } elseif ($dateFrom->format('Y-m-d') === $now->copy()->startOfYear()->format('Y-m-d') && 
                  $dateTo->format('Y-m-d') === $now->copy()->endOfYear()->format('Y-m-d')) {
            $this->period = 'year';
        } else {
            $this->period = 'custom';
        }
    }

    #[Computed]
    public function stats()
    {
        // Single optimized query for all stats including payment amounts
        return DB::table('sales')
            ->whereBetween('date', [$this->dateFrom, $this->dateTo])
            ->selectRaw('
                COUNT(*) as total,
                COALESCE(SUM(total_amount), 0) as revenue,
                COALESCE(AVG(total_amount), 0) as avg_amount,
                COALESCE(MAX(total_amount), 0) as max_amount,
                SUM(payment_method = "cash") as cash_count,
                SUM(payment_method = "transfer") as transfer_count,
                SUM(payment_method = "qris") as qris_count,
                SUM(CASE WHEN payment_method = "cash" THEN total_amount ELSE 0 END) as cash_amount,
                SUM(CASE WHEN payment_method = "transfer" THEN total_amount ELSE 0 END) as transfer_amount,
                SUM(CASE WHEN payment_method = "qris" THEN total_amount ELSE 0 END) as qris_amount
            ')
            ->first();
    }

    #[Computed]
    public function topProducts()
    {
        return DB::table('sale_items')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->whereBetween('sales.date', [$this->dateFrom, $this->dateTo])
            ->selectRaw('products.name, SUM(sale_items.quantity) as total_qty, SUM(sale_items.subtotal) as total_revenue')
            ->groupBy('products.id', 'products.name')
            ->orderByDesc('total_revenue')
            ->limit(5)
            ->get();
    }

    #[Computed]
    public function chartData()
    {
        // Single query for all daily revenue
        $dailyData = DB::table('sales')
            ->whereBetween('date', [$this->dateFrom, $this->dateTo])
            ->selectRaw('DATE(date) as day, SUM(total_amount) as revenue')
            ->groupBy('day')
            ->pluck('revenue', 'day')
            ->toArray();

        $labels = [];
        $revenue = [];
        
        $period = \Carbon\CarbonPeriod::create($this->dateFrom, $this->dateTo);
        foreach ($period as $date) {
            $key = $date->format('Y-m-d');
            $labels[] = $date->format('d/m');
            $revenue[] = $dailyData[$key] ?? 0;
        }

        return ['labels' => $labels, 'revenue' => $revenue];
    }

    #[Computed]
    public function hourlySales()
    {
        $data = DB::table('sales')
            ->whereBetween('date', [$this->dateFrom, $this->dateTo])
            ->selectRaw('HOUR(created_at) as hour, COUNT(*) as count')
            ->groupBy('hour')
            ->pluck('count', 'hour')
            ->toArray();

        // Fill all 24 hours
        $hourly = array_fill(0, 24, 0);
        foreach ($data as $hour => $count) {
            $hourly[$hour] = $count;
        }
        return $hourly;
    }

    #[Computed]
    public function peakHour()
    {
        $hourly = $this->hourlySales;
        $maxCount = max($hourly);
        if ($maxCount === 0) return null;
        
        return [
            'hour' => array_search($maxCount, $hourly),
            'count' => $maxCount
        ];
    }

    #[Computed]
    public function paymentSummary()
    {
        $stats = $this->stats;
        if ($stats->total === 0) return [];

        $methods = [
            ['name' => 'Cash', 'count' => $stats->cash_count, 'amount' => $stats->cash_amount, 'color' => 'emerald'],
            ['name' => 'Transfer', 'count' => $stats->transfer_count, 'amount' => $stats->transfer_amount, 'color' => 'blue'],
            ['name' => 'QRIS', 'count' => $stats->qris_count, 'amount' => $stats->qris_amount, 'color' => 'violet']
        ];

        return collect($methods)
            ->filter(fn($m) => $m['count'] > 0)
            ->map(fn($m) => array_merge($m, [
                'percentage' => round(($m['count'] / $stats->total) * 100, 1)
            ]))
            ->values()
            ->toArray();
    }

    public function render()
    {
        $sales = Sale::query()
            ->whereBetween('date', [$this->dateFrom, $this->dateTo])
            ->with('cashier:id,name')
            ->withCount('items')
            ->withSum('items', 'quantity')
            ->select('id', 'invoice_number', 'cashier_id', 'payment_method', 'total_amount', 'created_at')
            ->latest('created_at')
            ->paginate(15);

        return view('livewire.report.sales-report', ['sales' => $sales])
            ->layout('layouts.app');
    }
}
