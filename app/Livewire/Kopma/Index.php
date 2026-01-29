<?php

namespace App\Livewire\Kopma;

use App\Models\Product;
use App\Models\Schedule;
use App\Models\ScheduleAssignment;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class Index extends Component
{
    public $totalMembers;

    public $activeMembers;

    public $totalSchedules;

    public $todaySchedules;

    public $totalTransactions;

    public $todayRevenue;

    public $totalProducts;

    public $lowStockProducts;

    public function mount()
    {
        $this->loadDashboardStats();
    }

    public function loadDashboardStats()
    {
        $this->totalMembers = User::count();
        $this->activeMembers = User::where('status', 'active')->count();

        $this->totalSchedules = ScheduleAssignment::count();
        $this->todaySchedules = ScheduleAssignment::whereDate('date', today())->count();

        $this->totalTransactions = Transaction::count();
        $this->todayRevenue = Transaction::whereDate('created_at', today())
            ->where('status', 'completed')
            ->sum('total_amount');

        $this->totalProducts = Product::count();
        $this->lowStockProducts = Product::where('stock', '<=', DB::raw('min_stock'))->count();
    }

    public function render()
    {
        // Recent activities
        $recentTransactions = Transaction::with('user:id,name')
            ->latest()
            ->take(5)
            ->get(['id', 'user_id', 'total_amount', 'status', 'created_at']);

        $recentSchedules = Schedule::with('user:id,name')
            ->whereDate('date', '>=', today())
            ->orderBy('date')
            ->take(5)
            ->get(['id', 'user_id', 'date', 'shift', 'status']);

        // Chart data for last 7 days
        $weeklyRevenue = Transaction::selectRaw('DATE(created_at) as date, SUM(total_amount) as total')
            ->where('status', 'completed')
            ->whereDate('created_at', '>=', now()->subDays(7))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Member growth data
        $memberGrowth = User::selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->whereDate('created_at', '>=', now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return view('livewire.kopma.index', [
            'recentTransactions' => $recentTransactions,
            'recentSchedules' => $recentSchedules,
            'weeklyRevenue' => $weeklyRevenue,
            'memberGrowth' => $memberGrowth,
        ])->layout('layouts.app')->title('Dashboard Koperasi');
    }
}
