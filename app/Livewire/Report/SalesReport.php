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
        $query = Sale::query()
            ->whereBetween('date', [$this->dateFrom, $this->dateTo])
            ->when($this->cashierFilter !== 'all', fn($q) => $q->where('cashier_id', $this->cashierFilter))
            ->when($this->paymentMethodFilter !== 'all', fn($q) => $q->where('payment_method', $this->paymentMethodFilter));

        return [
            'total_sales' => $query->count(),
            'total_revenue' => $query->sum('total_amount'),
            'average_transaction' => $query->avg('total_amount') ?? 0,
            'cash_transactions' => Sale::whereBetween('date', [$this->dateFrom, $this->dateTo])
                ->where('payment_method', 'cash')->count(),
            'transfer_transactions' => Sale::whereBetween('date', [$this->dateFrom, $this->dateTo])
                ->where('payment_method', 'transfer')->count(),
            'qris_transactions' => Sale::whereBetween('date', [$this->dateFrom, $this->dateTo])
                ->where('payment_method', 'qris')->count(),
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
