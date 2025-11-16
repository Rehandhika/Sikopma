<?php

namespace App\Livewire\Report;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Title;
use App\Models\{Sale, Product, User};
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

#[Title('Laporan Penjualan')]
class SalesReport extends Component
{
    use WithPagination;

    public $dateFrom;
    public $dateTo;
    public $cashierFilter = 'all';
    public $paymentMethodFilter = 'all';

    public function mount()
    {
        $this->dateFrom = now()->startOfMonth()->format('Y-m-d');
        $this->dateTo = now()->format('Y-m-d');
    }

    public function updatingDateFrom()
    {
        $this->resetPage();
    }

    public function updatingDateTo()
    {
        $this->resetPage();
    }

    public function getStatsProperty()
    {
        // Optimize with single query using selectRaw
        $baseQuery = Sale::whereBetween('date', [$this->dateFrom, $this->dateTo])
            ->when($this->cashierFilter !== 'all', fn($q) => $q->where('cashier_id', $this->cashierFilter))
            ->when($this->paymentMethodFilter !== 'all', fn($q) => $q->where('payment_method', $this->paymentMethodFilter));

        $stats = $baseQuery->selectRaw('COUNT(*) as total_sales')
            ->selectRaw('SUM(total_amount) as total_revenue')
            ->selectRaw('AVG(total_amount) as average_transaction')
            ->first();

        // Get payment method breakdown in single query
        $paymentStats = Sale::whereBetween('date', [$this->dateFrom, $this->dateTo])
            ->selectRaw('SUM(CASE WHEN payment_method = "cash" THEN 1 ELSE 0 END) as cash_transactions')
            ->selectRaw('SUM(CASE WHEN payment_method = "transfer" THEN 1 ELSE 0 END) as transfer_transactions')
            ->selectRaw('SUM(CASE WHEN payment_method = "qris" THEN 1 ELSE 0 END) as qris_transactions')
            ->first();

        return [
            'total_sales' => $stats->total_sales ?? 0,
            'total_revenue' => $stats->total_revenue ?? 0,
            'average_transaction' => $stats->average_transaction ?? 0,
            'cash_transactions' => $paymentStats->cash_transactions ?? 0,
            'transfer_transactions' => $paymentStats->transfer_transactions ?? 0,
            'qris_transactions' => $paymentStats->qris_transactions ?? 0,
        ];
    }

    public function getChartDataProperty()
    {
        $sales = Sale::query()
            ->selectRaw('DATE(date) as sale_date, COUNT(*) as count, SUM(total_amount) as total')
            ->whereBetween('date', [$this->dateFrom, $this->dateTo])
            ->when($this->cashierFilter !== 'all', fn($q) => $q->where('cashier_id', $this->cashierFilter))
            ->when($this->paymentMethodFilter !== 'all', fn($q) => $q->where('payment_method', $this->paymentMethodFilter))
            ->groupBy('sale_date')
            ->orderBy('sale_date')
            ->get();

        return [
            'labels' => $sales->pluck('sale_date')->map(fn($date) => Carbon::parse($date)->format('d/m'))->toArray(),
            'counts' => $sales->pluck('count')->toArray(),
            'totals' => $sales->pluck('total')->toArray(),
        ];
    }

    public function getTopProductsProperty()
    {
        return DB::table('sale_items')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->selectRaw('products.name, SUM(sale_items.quantity) as total_quantity, SUM(sale_items.subtotal) as total_revenue')
            ->whereBetween('sales.date', [$this->dateFrom, $this->dateTo])
            ->groupBy('products.id', 'products.name')
            ->orderByDesc('total_revenue')
            ->limit(10)
            ->get();
    }

    public function getCashiersProperty()
    {
        return User::whereHas('sales')->orderBy('name')->get();
    }

    public function render()
    {
        $sales = Sale::query()
            ->with(['cashier', 'items'])
            ->whereBetween('date', [$this->dateFrom, $this->dateTo])
            ->when($this->cashierFilter !== 'all', fn($q) => $q->where('cashier_id', $this->cashierFilter))
            ->when($this->paymentMethodFilter !== 'all', fn($q) => $q->where('payment_method', $this->paymentMethodFilter))
            ->latest('created_at')
            ->paginate(20);

        return view('livewire.report.sales-report', [
            'sales' => $sales,
            'stats' => $this->stats,
            'chartData' => $this->chartData,
            'topProducts' => $this->topProducts,
            'cashiers' => $this->cashiers,
        ])->layout('layouts.app');
    }
}
