<?php

namespace App\Livewire\Analytics;

use Livewire\Component;
use App\Services\AnalyticsService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class BIDashboard extends Component
{
    public $selectedPeriod = 'month';
    public $startDate;
    public $endDate;
    public $refreshInterval = 30; // seconds
    public $autoRefresh = true;
    public $selectedMetrics = ['attendance', 'sales', 'operational', 'financial'];
    public $chartType = 'line';
    public $compareWithPrevious = false;

    protected $analyticsService;
    protected $listeners = [
        'periodChanged' => 'updatePeriod',
        'metricsUpdated' => 'updateSelectedMetrics',
        'refreshData' => 'refreshData',
    ];

    public function boot(AnalyticsService $analyticsService)
    {
        $this->analyticsService = $analyticsService;
    }

    public function mount()
    {
        $this->initializeDateRange();
        $this->loadDashboardData();
    }

    public function render()
    {
        return view('livewire.analytics.bi-dashboard', [
            'kpis' => $this->kpis,
            'realTimeMetrics' => $this->realTimeMetrics,
            'trends' => $this->trends,
            'predictions' => $this->predictions,
            'selectedPeriod' => $this->selectedPeriod,
            'startDate' => $this->startDate,
            'endDate' => $this->endDate,
        ])->layout('layouts.app')->title('Business Intelligence Dashboard');
    }

    protected function initializeDateRange()
    {
        switch ($this->selectedPeriod) {
            case 'today':
                $this->startDate = today();
                $this->endDate = today();
                break;
            case 'week':
                $this->startDate = now()->startOfWeek();
                $this->endDate = now()->endOfWeek();
                break;
            case 'month':
                $this->startDate = now()->startOfMonth();
                $this->endDate = now()->endOfMonth();
                break;
            case 'quarter':
                $this->startDate = now()->startOfQuarter();
                $this->endDate = now()->endOfQuarter();
                break;
            case 'year':
                $this->startDate = now()->startOfYear();
                $this->endDate = now()->endOfYear();
                break;
            default:
                $this->startDate = now()->startOfMonth();
                $this->endDate = now()->endOfMonth();
        }
    }

    public function loadDashboardData()
    {
        $this->kpis = $this->analyticsService->getDashboardKPIs($this->startDate, $this->endDate);
        $this->realTimeMetrics = $this->analyticsService->getRealTimeMetrics();
        $this->trends = $this->getTrendData();
        $this->predictions = $this->analyticsService->getPredictiveAnalytics();
    }

    protected function getTrendData(): array
    {
        $trends = [];
        $periodDays = $this->getPeriodDays();

        foreach ($this->selectedMetrics as $metric) {
            $trends[$metric] = $this->analyticsService->getTrendAnalysis($metric, $periodDays);
        }

        return $trends;
    }

    protected function getPeriodDays(): int
    {
        switch ($this->selectedPeriod) {
            case 'today':
                return 1;
            case 'week':
                return 7;
            case 'month':
                return 30;
            case 'quarter':
                return 90;
            case 'year':
                return 365;
            default:
                return 30;
        }
    }

    public function updatePeriod($period)
    {
        $this->selectedPeriod = $period;
        $this->initializeDateRange();
        $this->refreshData();
    }

    public function updateSelectedMetrics($metrics)
    {
        $this->selectedMetrics = $metrics;
        $this->refreshData();
    }

    public function refreshData()
    {
        $this->loadDashboardData();
        $this->dispatch('dataRefreshed');
    }

    public function exportReport()
    {
        try {
            $reportData = [
                'period' => [
                    'start' => $this->startDate->toDateString(),
                    'end' => $this->endDate->toDateString(),
                    'type' => $this->selectedPeriod,
                ],
                'kpis' => $this->kpis,
                'trends' => $this->trends,
                'predictions' => $this->predictions,
                'generated_at' => now()->toISOString(),
            ];

            $filename = "bi_report_{$this->startDate->format('Y-m-d')}_to_{$this->endDate->format('Y-m-d')}.json";
            
            // Store report in storage
            $path = "reports/{$filename}";
            \Storage::disk('public')->put($path, json_encode($reportData, JSON_PRETTY_PRINT));

            $this->dispatch('reportExported', [
                'url' => \Storage::url($path),
                'filename' => $filename,
            ]);

        } catch (\Exception $e) {
            \Log::error('Failed to export BI report', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            $this->dispatch('error', 'Gagal mengekspor laporan');
        }
    }

    public function scheduleReport()
    {
        try {
            // Schedule report generation
            $scheduleData = [
                'period' => $this->selectedPeriod,
                'metrics' => $this->selectedMetrics,
                'recipients' => auth()->user()->email,
                'frequency' => 'weekly', // Can be made configurable
                'next_run' => now()->addWeek(),
            ];

            // Store schedule in database or cache
            Cache::put('scheduled_report_' . auth()->id(), $scheduleData, now()->addMonth());

            $this->dispatch('reportScheduled', 'Laporan akan dikirim setiap minggu');

        } catch (\Exception $e) {
            \Log::error('Failed to schedule report', [
                'error' => $e->getMessage()
            ]);
            $this->dispatch('error', 'Gagal menjadwalkan laporan');
        }
    }

    public function toggleAutoRefresh()
    {
        $this->autoRefresh = !$this->autoRefresh;
        
        if ($this->autoRefresh) {
            $this->dispatch('startAutoRefresh', $this->refreshInterval);
        } else {
            $this->dispatch('stopAutoRefresh');
        }
    }

    public function setRefreshInterval($interval)
    {
        $this->refreshInterval = $interval;
        
        if ($this->autoRefresh) {
            $this->dispatch('updateRefreshInterval', $interval);
        }
    }

    public function changeChartType($type)
    {
        $this->chartType = $type;
        $this->dispatch('chartTypeChanged', $type);
    }

    public function toggleComparison()
    {
        $this->compareWithPrevious = !$this->compareWithPrevious;
        $this->refreshData();
    }

    public function drillDown($category, $value)
    {
        try {
            $drillDownData = $this->getDrillDownData($category, $value);
            
            $this->dispatch('drillDownData', [
                'category' => $category,
                'value' => $value,
                'data' => $drillDownData,
            ]);

        } catch (\Exception $e) {
            \Log::error('Failed to get drill down data', [
                'category' => $category,
                'value' => $value,
                'error' => $e->getMessage()
            ]);
            $this->dispatch('error', 'Gagal mendapatkan detail data');
        }
    }

    protected function getDrillDownData($category, $value)
    {
        switch ($category) {
            case 'attendance':
                return $this->getAttendanceDrillDown($value);
            case 'sales':
                return $this->getSalesDrillDown($value);
            case 'users':
                return $this->getUserDrillDown($value);
            case 'products':
                return $this->getProductDrillDown($value);
            default:
                return [];
        }
    }

    protected function getAttendanceDrillDown($value)
    {
        // Detailed attendance data for specific date or user
        if (is_string($value) && \Carbon\Carbon::canBeCreatedFromFormat($value, 'Y-m-d')) {
            return \App\Models\Attendance::whereDate('date', $value)
                ->with(['user', 'scheduleAssignment'])
                ->get()
                ->map(function ($attendance) {
                    return [
                        'user' => $attendance->user->name,
                        'check_in' => $attendance->check_in?->format('H:i'),
                        'check_out' => $attendance->check_out?->format('H:i'),
                        'status' => $attendance->status,
                        'late_minutes' => $attendance->late_minutes ?? 0,
                    ];
                });
        }

        return [];
    }

    protected function getSalesDrillDown($value)
    {
        // Detailed sales data for specific date or product
        if (is_string($value) && \Carbon\Carbon::canBeCreatedFromFormat($value, 'Y-m-d')) {
            return \App\Models\Sale::whereDate('created_at', $value)
                ->where('status', 'completed')
                ->with(['items.product', 'user'])
                ->get()
                ->map(function ($sale) {
                    return [
                        'id' => $sale->id,
                        'cashier' => $sale->user->name,
                        'total' => $sale->total_amount,
                        'payment_method' => $sale->payment_method,
                        'items_count' => $sale->items->count(),
                    ];
                });
        }

        return [];
    }

    protected function getUserDrillDown($userId)
    {
        $user = \App\Models\User::find($userId);
        if (!$user) {
            return [];
        }

        return [
            'user_info' => [
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'role' => $user->roles->first()->name ?? 'N/A',
            ],
            'attendance_summary' => $this->analyticsService->getAttendanceMetrics($this->startDate, $this->endDate),
            'performance_metrics' => $this->analyticsService->getPerformanceMetrics($this->startDate, $this->endDate),
        ];
    }

    protected function getProductDrillDown($productId)
    {
        $product = \App\Models\Product::find($productId);
        if (!$product) {
            return [];
        }

        $salesData = \App\Models\SaleItem::where('product_id', $productId)
            ->whereHas('sale', function ($query) {
                $query->whereBetween('created_at', [$this->startDate, $this->endDate])
                      ->where('status', 'completed');
            })
            ->with('sale')
            ->get()
            ->groupBy(function ($item) {
                return $item->sale->created_at->format('Y-m-d');
            })
            ->map(function ($daySales) {
                return [
                    'quantity_sold' => $daySales->sum('quantity'),
                    'revenue' => $daySales->sum('subtotal'),
                    'transactions' => $daySales->count(),
                ];
            });

        return [
            'product_info' => [
                'name' => $product->name,
                'sku' => $product->sku,
                'price' => $product->price,
                'stock' => $product->stock,
            ],
            'sales_data' => $salesData,
        ];
    }

    public function getKPIDisplay($category, $metric)
    {
        if (!isset($this->kpis[$category][$metric])) {
            return [
                'value' => 0,
                'label' => ucfirst(str_replace('_', ' ', $metric)),
                'trend' => 'stable',
                'change' => 0,
            ];
        }

        $value = $this->kpis[$category][$metric];
        $previousValue = $this->getPreviousPeriodValue($category, $metric);
        
        $change = $previousValue > 0 
            ? round((($value - $previousValue) / $previousValue) * 100, 2)
            : 0;

        $trend = $change > 5 ? 'up' : ($change < -5 ? 'down' : 'stable');

        return [
            'value' => $value,
            'label' => ucfirst(str_replace('_', ' ', $metric)),
            'trend' => $trend,
            'change' => $change,
            'formatted_value' => $this->formatKPIValue($category, $metric, $value),
        ];
    }

    protected function getPreviousPeriodValue($category, $metric)
    {
        // Calculate previous period value for comparison
        $previousStartDate = $this->startDate->copy()->subDays($this->startDate->diffInDays($this->endDate));
        $previousEndDate = $this->startDate->copy()->subDay();

        $previousKPIs = $this->analyticsService->getDashboardKPIs($previousStartDate, $previousEndDate);
        
        return $previousKPIs[$category][$metric] ?? 0;
    }

    protected function formatKPIValue($category, $metric, $value)
    {
        switch ($metric) {
            case 'attendance_rate':
            case 'punctuality_rate':
            case 'schedule_efficiency':
            case 'user_activity_rate':
            case 'profit_margin':
                return number_format($value, 1) . '%';
            
            case 'total_revenue':
            case 'total_cost':
            case 'net_profit':
            case 'average_transaction':
            case 'revenue_per_day':
                return 'Rp ' . number_format($value, 0, ',', '.');
            
            case 'average_late_minutes':
            case 'productivity_score':
                return number_format($value, 1);
            
            default:
                return number_format($value);
        }
    }

    public function getRealTimeGauge($metric)
    {
        if (!isset($this->realTimeMetrics[$metric])) {
            return [
                'value' => 0,
                'max' => 100,
                'unit' => '',
                'status' => 'normal',
            ];
        }

        $data = $this->realTimeMetrics[$metric];
        
        return [
            'value' => $data['value'] ?? 0,
            'max' => $data['max'] ?? 100,
            'unit' => $data['unit'] ?? '',
            'status' => $this->getGaugeStatus($metric, $data['value'] ?? 0),
        ];
    }

    protected function getGaugeStatus($metric, $value)
    {
        switch ($metric) {
            case 'cpu_usage':
            case 'memory_usage':
            case 'disk_usage':
                return $value > 80 ? 'critical' : ($value > 60 ? 'warning' : 'normal');
            
            case 'online_users':
            case 'active_sessions':
                return $value > 50 ? 'high' : ($value > 20 ? 'medium' : 'low');
            
            default:
                return 'normal';
        }
    }
}
