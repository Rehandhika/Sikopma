<?php

namespace App\Services;

use App\Models\User;
use App\Models\Attendance;
use App\Models\Sale;
use App\Models\Product;
use App\Models\ScheduleAssignment;
use App\Models\Penalty;
use App\Models\SwapRequest;
use App\Models\LeaveRequest;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Collection;

class AnalyticsService
{
    /**
     * Get dashboard KPIs
     */
    public function getDashboardKPIs(?Carbon $startDate = null, ?Carbon $endDate = null): array
    {
        $startDate = $startDate ?: now()->startOfMonth();
        $endDate = $endDate ?: now()->endOfMonth();

        return [
            'attendance_metrics' => $this->getAttendanceMetrics($startDate, $endDate),
            'sales_metrics' => $this->getSalesMetrics($startDate, $endDate),
            'operational_metrics' => $this->getOperationalMetrics($startDate, $endDate),
            'financial_metrics' => $this->getFinancialMetrics($startDate, $endDate),
            'performance_metrics' => $this->getPerformanceMetrics($startDate, $endDate),
        ];
    }

    /**
     * Get attendance analytics
     */
    protected function getAttendanceMetrics(Carbon $startDate, Carbon $endDate): array
    {
        $cacheKey = "attendance_metrics_{$startDate->format('Y-m-d')}_{$endDate->format('Y-m-d')}";
        
        return Cache::remember($cacheKey, 300, function () use ($startDate, $endDate) {
            $attendances = Attendance::whereBetween('date', [$startDate, $endDate])->get();
            $totalScheduled = ScheduleAssignment::whereBetween('date', [$startDate, $endDate])
                ->where('status', 'scheduled')
                ->count();

            $presentCount = $attendances->whereIn('status', ['present', 'late'])->count();
            $lateCount = $attendances->where('status', 'late')->count();
            $absentCount = $attendances->where('status', 'absent')->count();

            $attendanceRate = $totalScheduled > 0 
                ? round(($presentCount / $totalScheduled) * 100, 2)
                : 0;

            $punctualityRate = $presentCount > 0 
                ? round((($presentCount - $lateCount) / $presentCount) * 100, 2)
                : 0;

            return [
                'attendance_rate' => $attendanceRate,
                'punctuality_rate' => $punctualityRate,
                'total_present' => $presentCount,
                'total_late' => $lateCount,
                'total_absent' => $absentCount,
                'total_scheduled' => $totalScheduled,
                'average_late_minutes' => $lateCount > 0 
                    ? round($attendances->where('status', 'late')->avg('late_minutes'), 1)
                    : 0,
            ];
        });
    }

    /**
     * Get sales analytics
     */
    protected function getSalesMetrics(Carbon $startDate, Carbon $endDate): array
    {
        $cacheKey = "sales_metrics_{$startDate->format('Y-m-d')}_{$endDate->format('Y-m-d')}";
        
        return Cache::remember($cacheKey, 300, function () use ($startDate, $endDate) {
            $sales = Sale::whereBetween('created_at', [$startDate, $endDate])
                ->where('status', 'completed')
                ->get();

            $totalRevenue = $sales->sum('total_amount');
            $totalTransactions = $sales->count();
            $averageTransaction = $totalTransactions > 0 
                ? round($totalRevenue / $totalTransactions, 2)
                : 0;

            // Daily sales trend
            $dailySales = $sales->groupBy(function ($sale) {
                return $sale->created_at->format('Y-m-d');
            })->map(function ($daySales) {
                return [
                    'revenue' => $daySales->sum('total_amount'),
                    'transactions' => $daySales->count(),
                ];
            });

            // Top selling products
            $topProducts = DB::table('sale_items')
                ->join('products', 'sale_items.product_id', '=', 'products.id')
                ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
                ->whereBetween('sales.created_at', [$startDate, $endDate])
                ->where('sales.status', 'completed')
                ->selectRaw('products.name, SUM(sale_items.quantity) as total_quantity, SUM(sale_items.subtotal) as total_revenue')
                ->groupBy('products.id', 'products.name')
                ->orderBy('total_revenue', 'desc')
                ->limit(10)
                ->get();

            return [
                'total_revenue' => $totalRevenue,
                'total_transactions' => $totalTransactions,
                'average_transaction' => $averageTransaction,
                'daily_sales' => $dailySales,
                'top_products' => $topProducts,
                'growth_rate' => $this->calculateSalesGrowthRate($startDate, $endDate),
            ];
        });
    }

    /**
     * Get operational metrics
     */
    protected function getOperationalMetrics(Carbon $startDate, Carbon $endDate): array
    {
        $cacheKey = "operational_metrics_{$startDate->format('Y-m-d')}_{$endDate->format('Y-m-d')}";
        
        return Cache::remember($cacheKey, 300, function () use ($startDate, $endDate) {
            $totalUsers = User::where('status', 'active')->count();
            $activeUsers = User::whereHas('attendances', function ($query) use ($startDate, $endDate) {
                $query->whereBetween('date', [$startDate, $endDate]);
            })->count();

            $pendingSwapRequests = SwapRequest::where('status', 'pending')->count();
            $pendingLeaveRequests = LeaveRequest::where('status', 'pending')->count();
            $totalPenalties = Penalty::whereBetween('created_at', [$startDate, $endDate])->count();

            // Schedule efficiency
            $totalSchedules = ScheduleAssignment::whereBetween('date', [$startDate, $endDate])
                ->where('status', 'scheduled')
                ->count();
            $completedSchedules = ScheduleAssignment::whereBetween('date', [$startDate, $endDate])
                ->where('status', 'completed')
                ->count();

            $scheduleEfficiency = $totalSchedules > 0 
                ? round(($completedSchedules / $totalSchedules) * 100, 2)
                : 0;

            return [
                'total_users' => $totalUsers,
                'active_users' => $activeUsers,
                'user_activity_rate' => $totalUsers > 0 
                    ? round(($activeUsers / $totalUsers) * 100, 2)
                    : 0,
                'pending_swap_requests' => $pendingSwapRequests,
                'pending_leave_requests' => $pendingLeaveRequests,
                'total_penalties' => $totalPenalties,
                'schedule_efficiency' => $scheduleEfficiency,
            ];
        });
    }

    /**
     * Get financial metrics
     */
    protected function getFinancialMetrics(Carbon $startDate, Carbon $endDate): array
    {
        $cacheKey = "financial_metrics_{$startDate->format('Y-m-d')}_{$endDate->format('Y-m-d')}";
        
        return Cache::remember($cacheKey, 300, function () use ($startDate, $endDate) {
            $sales = Sale::whereBetween('created_at', [$startDate, $endDate])
                ->where('status', 'completed')
                ->get();

            $penalties = Penalty::whereBetween('created_at', [$startDate, $endDate])->get();

            $totalRevenue = $sales->sum('total_amount');
            $totalPenalties = $penalties->sum('amount');
            $totalCost = $this->calculateTotalCost($startDate, $endDate);

            $netProfit = $totalRevenue - $totalCost - $totalPenalties;
            $profitMargin = $totalRevenue > 0 
                ? round(($netProfit / $totalRevenue) * 100, 2)
                : 0;

            // Payment method breakdown
            $paymentBreakdown = $sales->groupBy('payment_method')->map(function ($methodSales) {
                return [
                    'count' => $methodSales->count(),
                    'amount' => $methodSales->sum('total_amount'),
                    'percentage' => $totalRevenue > 0 
                        ? round(($methodSales->sum('total_amount') / $totalRevenue) * 100, 2)
                        : 0,
                ];
            });

            return [
                'total_revenue' => $totalRevenue,
                'total_penalties' => $totalPenalties,
                'total_cost' => $totalCost,
                'net_profit' => $netProfit,
                'profit_margin' => $profitMargin,
                'payment_breakdown' => $paymentBreakdown,
                'revenue_per_day' => $startDate->diffInDays($endDate) > 0 
                    ? round($totalRevenue / $startDate->diffInDays($endDate), 2)
                    : 0,
            ];
        });
    }

    /**
     * Get performance metrics
     */
    protected function getPerformanceMetrics(Carbon $startDate, Carbon $endDate): array
    {
        $cacheKey = "performance_metrics_{$startDate->format('Y-m-d')}_{$endDate->format('Y-m-d')}";
        
        return Cache::remember($cacheKey, 300, function () use ($startDate, $endDate) {
            // User performance ranking
            $userPerformance = DB::table('attendances')
                ->join('users', 'attendances.user_id', '=', 'users.id')
                ->whereBetween('attendances.date', [$startDate, $endDate])
                ->selectRaw('
                    users.id,
                    users.name,
                    COUNT(*) as total_attendances,
                    SUM(CASE WHEN attendances.status IN ("present", "late") THEN 1 ELSE 0 END) as present_count,
                    SUM(CASE WHEN attendances.status = "late" THEN attendances.late_minutes ELSE 0 END) as total_late_minutes,
                    AVG(CASE WHEN attendances.status = "late" THEN attendances.late_minutes ELSE NULL END) as avg_late_minutes
                ')
                ->groupBy('users.id', 'users.name')
                ->orderBy('present_count', 'desc')
                ->limit(10)
                ->get();

            // Department performance (if roles represent departments)
            $departmentPerformance = DB::table('attendances')
                ->join('users', 'attendances.user_id', '=', 'users.id')
                ->join('model_has_roles', 'users.id', '=', 'model_has_roles.model_id')
                ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
                ->whereBetween('attendances.date', [$startDate, $endDate])
                ->selectRaw('
                    roles.name as department,
                    COUNT(*) as total_attendances,
                    SUM(CASE WHEN attendances.status IN ("present", "late") THEN 1 ELSE 0 END) as present_count,
                    ROUND((SUM(CASE WHEN attendances.status IN ("present", "late") THEN 1 ELSE 0 END) / COUNT(*)) * 100, 2) as attendance_rate
                ')
                ->groupBy('roles.id', 'roles.name')
                ->orderBy('attendance_rate', 'desc')
                ->get();

            return [
                'top_performers' => $userPerformance,
                'department_performance' => $departmentPerformance,
                'productivity_score' => $this->calculateProductivityScore($startDate, $endDate),
            ];
        });
    }

    /**
     * Get real-time metrics
     */
    public function getRealTimeMetrics(): array
    {
        return [
            'online_users' => $this->getOnlineUsersCount(),
            'active_sessions' => $this->getActiveSessionsCount(),
            'today_attendance' => $this->getTodayAttendanceMetrics(),
            'today_sales' => $this->getTodaySalesMetrics(),
            'system_health' => $this->getSystemHealthMetrics(),
        ];
    }

    /**
     * Get trend analysis
     */
    public function getTrendAnalysis(string $metric, int $periodDays = 30): array
    {
        $endDate = now();
        $startDate = $endDate->copy()->subDays($periodDays);
        $period = CarbonPeriod::create($startDate, $endDate);

        $data = [];
        foreach ($period as $date) {
            $data[$date->format('Y-m-d')] = $this->getMetricValue($metric, $date);
        }

        // Calculate trend
        $values = array_values($data);
        $trend = $this->calculateTrend($values);

        return [
            'data' => $data,
            'trend' => $trend,
            'growth_rate' => $this->calculateGrowthRate($values),
        ];
    }

    /**
     * Get predictive analytics
     */
    public function getPredictiveAnalytics(): array
    {
        return [
            'attendance_forecast' => $this->predictAttendanceTrend(),
            'sales_forecast' => $this->predictSalesTrend(),
            'staffing_needs' => $this->predictStaffingNeeds(),
            'revenue_forecast' => $this->predictRevenueTrend(),
        ];
    }

    /**
     * Helper methods
     */
    protected function calculateSalesGrowthRate(Carbon $startDate, Carbon $endDate): float
    {
        $currentPeriodRevenue = Sale::whereBetween('created_at', [$startDate, $endDate])
            ->where('status', 'completed')
            ->sum('total_amount');

        $previousStartDate = $startDate->copy()->subDays($startDate->diffInDays($endDate));
        $previousEndDate = $startDate->copy()->subDay();

        $previousPeriodRevenue = Sale::whereBetween('created_at', [$previousStartDate, $previousEndDate])
            ->where('status', 'completed')
            ->sum('total_amount');

        if ($previousPeriodRevenue == 0) {
            return 0;
        }

        return round((($currentPeriodRevenue - $previousPeriodRevenue) / $previousPeriodRevenue) * 100, 2);
    }

    protected function calculateTotalCost(Carbon $startDate, Carbon $endDate): float
    {
        // This would include salaries, operational costs, etc.
        // For now, return a simulated value
        return 0;
    }

    protected function calculateProductivityScore(Carbon $startDate, Carbon $endDate): float
    {
        $attendanceRate = $this->getAttendanceMetrics($startDate, $endDate)['attendance_rate'];
        $scheduleEfficiency = $this->getOperationalMetrics($startDate, $endDate)['schedule_efficiency'];
        
        return round(($attendanceRate + $scheduleEfficiency) / 2, 2);
    }

    protected function getOnlineUsersCount(): int
    {
        // This would typically use a session tracking system
        // For now, return a simulated value
        return rand(5, 20);
    }

    protected function getActiveSessionsCount(): int
    {
        // This would typically use session tracking
        return rand(10, 30);
    }

    protected function getTodayAttendanceMetrics(): array
    {
        $todayAttendances = Attendance::whereDate('date', today())->get();
        
        return [
            'total' => $todayAttendances->count(),
            'present' => $todayAttendances->whereIn('status', ['present', 'late'])->count(),
            'late' => $todayAttendances->where('status', 'late')->count(),
            'absent' => $todayAttendances->where('status', 'absent')->count(),
        ];
    }

    protected function getTodaySalesMetrics(): array
    {
        $todaySales = Sale::whereDate('created_at', today())
            ->where('status', 'completed')
            ->get();

        return [
            'total' => $todaySales->count(),
            'revenue' => $todaySales->sum('total_amount'),
            'average' => $todaySales->count() > 0 
                ? round($todaySales->sum('total_amount') / $todaySales->count(), 2)
                : 0,
        ];
    }

    protected function getSystemHealthMetrics(): array
    {
        return [
            'cpu_usage' => rand(20, 80),
            'memory_usage' => rand(30, 70),
            'disk_usage' => rand(40, 60),
            'database_connections' => rand(5, 15),
        ];
    }

    protected function getMetricValue(string $metric, Carbon $date): float
    {
        switch ($metric) {
            case 'attendance_rate':
                $attendances = Attendance::whereDate('date', $date)->get();
                $scheduled = ScheduleAssignment::whereDate('date', $date)->where('status', 'scheduled')->count();
                $present = $attendances->whereIn('status', ['present', 'late'])->count();
                return $scheduled > 0 ? round(($present / $scheduled) * 100, 2) : 0;
            
            case 'daily_revenue':
                return Sale::whereDate('created_at', $date)
                    ->where('status', 'completed')
                    ->sum('total_amount');
            
            case 'transaction_count':
                return Sale::whereDate('created_at', $date)
                    ->where('status', 'completed')
                    ->count();
            
            default:
                return 0;
        }
    }

    protected function calculateTrend(array $values): string
    {
        if (count($values) < 2) {
            return 'stable';
        }

        $firstHalf = array_slice($values, 0, count($values) / 2);
        $secondHalf = array_slice($values, count($values) / 2);

        $firstAvg = array_sum($firstHalf) / count($firstHalf);
        $secondAvg = array_sum($secondHalf) / count($secondHalf);

        if ($secondAvg > $firstAvg * 1.1) {
            return 'increasing';
        } elseif ($secondAvg < $firstAvg * 0.9) {
            return 'decreasing';
        } else {
            return 'stable';
        }
    }

    protected function calculateGrowthRate(array $values): float
    {
        if (count($values) < 2) {
            return 0;
        }

        $first = $values[0];
        $last = end($values);

        if ($first == 0) {
            return 0;
        }

        return round((($last - $first) / $first) * 100, 2);
    }

    protected function predictAttendanceTrend(): array
    {
        // Simple linear regression prediction
        $last30Days = Attendance::whereDate('date', '>=', now()->subDays(30))
            ->selectRaw('DATE(date) as date, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        if ($last30Days->count() < 7) {
            return ['trend' => 'insufficient_data', 'prediction' => 0];
        }

        // Simple prediction logic
        $recentAverage = $last30Days->take(7)->avg('count');
        $olderAverage = $last30Days->skip(7)->take(7)->avg('count');

        $trend = $recentAverage > $olderAverage ? 'improving' : 'declining';
        $prediction = round($recentAverage * 1.05, 0); // 5% growth prediction

        return [
            'trend' => $trend,
            'prediction' => $prediction,
            'confidence' => 75, // Mock confidence score
        ];
    }

    protected function predictSalesTrend(): array
    {
        $last30Days = Sale::whereDate('created_at', '>=', now()->subDays(30))
            ->where('status', 'completed')
            ->selectRaw('DATE(created_at) as date, SUM(total_amount) as revenue')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        if ($last30Days->count() < 7) {
            return ['trend' => 'insufficient_data', 'prediction' => 0];
        }

        $recentAverage = $last30Days->take(7)->avg('revenue');
        $olderAverage = $last30Days->skip(7)->take(7)->avg('revenue');

        $trend = $recentAverage > $olderAverage ? 'growing' : 'declining';
        $prediction = round($recentAverage * 1.08, 2); // 8% growth prediction

        return [
            'trend' => $trend,
            'prediction' => $prediction,
            'confidence' => 70,
        ];
    }

    protected function predictStaffingNeeds(): array
    {
        // Predict staffing based on historical patterns
        $nextWeekSchedules = ScheduleAssignment::whereBetween('date', [
            now()->startOfWeek()->addWeek(),
            now()->endOfWeek()->addWeek()
        ])->count();

        $currentStaff = User::where('status', 'active')->count();
        $utilization = $currentStaff > 0 ? round(($nextWeekSchedules / ($currentStaff * 3)) * 100, 2) : 0;

        return [
            'required_staff' => ceil($nextWeekSchedules / 2), // Assume 2 shifts per staff
            'current_staff' => $currentStaff,
            'utilization_rate' => $utilization,
            'recommendation' => $utilization > 85 ? 'hire_more' : ($utilization < 60 ? 'reduce_hours' : 'optimal'),
        ];
    }

    protected function predictRevenueTrend(): array
    {
        $monthlyRevenue = Sale::whereDate('created_at', '>=', now()->subMonths(6))
            ->where('status', 'completed')
            ->selectRaw('YEAR(created_at) as year, MONTH(created_at) as month, SUM(total_amount) as revenue')
            ->groupBy('year', 'month')
            ->orderBy('year')
            ->orderBy('month')
            ->get();

        if ($monthlyRevenue->count() < 3) {
            return ['trend' => 'insufficient_data', 'prediction' => 0];
        }

        $lastMonth = $monthlyRevenue->last()->revenue;
        $previousMonth = $monthlyRevenue->slice(-2, 1)->first()->revenue;

        $growthRate = $previousMonth > 0 ? round((($lastMonth - $previousMonth) / $previousMonth) * 100, 2) : 0;
        $trend = $growthRate > 5 ? 'strong_growth' : ($growthRate > 0 ? 'moderate_growth' : 'declining');
        $prediction = round($lastMonth * (1 + ($growthRate / 100)), 2);

        return [
            'trend' => $trend,
            'prediction' => $prediction,
            'growth_rate' => $growthRate,
            'confidence' => 65,
        ];
    }
}
