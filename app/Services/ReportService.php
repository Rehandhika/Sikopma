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
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Queue;
use App\Jobs\GenerateReportJob;

class ReportService
{
    /**
     * Generate attendance report
     */
    public function generateAttendanceReport(array $filters): array
    {
        $startDate = $filters['start_date'] ?? now()->startOfMonth();
        $endDate = $filters['end_date'] ?? now()->endOfMonth();
        $userId = $filters['user_id'] ?? null;
        $department = $filters['department'] ?? null;

        $query = Attendance::with(['user', 'scheduleAssignment'])
            ->whereBetween('date', [$startDate, $endDate]);

        if ($userId) {
            $query->where('user_id', $userId);
        }

        if ($department) {
            $query->whereHas('user.roles', function ($q) use ($department) {
                $q->where('name', $department);
            });
        }

        $attendances = $query->get();

        $reportData = [
            'summary' => $this->calculateAttendanceSummary($attendances),
            'details' => $attendances->map(function ($attendance) {
                return [
                    'date' => $attendance->date->format('Y-m-d'),
                    'user_name' => $attendance->user->name,
                    'user_nim' => $attendance->user->nim,
                    'check_in' => $attendance->check_in?->format('H:i:s'),
                    'check_out' => $attendance->check_out?->format('H:i:s'),
                    'status' => $attendance->status,
                    'late_minutes' => $attendance->late_minutes ?? 0,
                    'notes' => $attendance->notes,
                ];
            })->toArray(),
            'analytics' => $this->getAttendanceAnalytics($attendances),
        ];

        return $reportData;
    }

    /**
     * Generate sales report
     */
    public function generateSalesReport(array $filters): array
    {
        $startDate = $filters['start_date'] ?? now()->startOfMonth();
        $endDate = $filters['end_date'] ?? now()->endOfMonth();
        $cashierId = $filters['cashier_id'] ?? null;
        $paymentMethod = $filters['payment_method'] ?? null;

        $query = Sale::with(['user', 'items.product'])
            ->whereBetween('created_at', [$startDate, $endDate])
            ->where('status', 'completed');

        if ($cashierId) {
            $query->where('user_id', $cashierId);
        }

        if ($paymentMethod) {
            $query->where('payment_method', $paymentMethod);
        }

        $sales = $query->get();

        $reportData = [
            'summary' => $this->calculateSalesSummary($sales),
            'details' => $sales->map(function ($sale) {
                return [
                    'id' => $sale->id,
                    'date' => $sale->created_at->format('Y-m-d'),
                    'time' => $sale->created_at->format('H:i:s'),
                    'cashier' => $sale->user->name,
                    'customer_name' => $sale->customer_name,
                    'subtotal' => $sale->subtotal,
                    'tax' => $sale->tax,
                    'discount' => $sale->discount,
                    'total_amount' => $sale->total_amount,
                    'payment_method' => $sale->payment_method,
                    'items_count' => $sale->items->count(),
                ];
            })->toArray(),
            'product_breakdown' => $this->getProductSalesBreakdown($sales),
            'payment_breakdown' => $this->getPaymentMethodBreakdown($sales),
            'daily_trends' => $this->getSalesDailyTrends($sales),
        ];

        return $reportData;
    }

    /**
     * Generate financial report
     */
    public function generateFinancialReport(array $filters): array
    {
        $startDate = $filters['start_date'] ?? now()->startOfMonth();
        $endDate = $filters['end_date'] ?? now()->endOfMonth();

        $sales = Sale::whereBetween('created_at', [$startDate, $endDate])
            ->where('status', 'completed')
            ->get();

        $penalties = Penalty::whereBetween('created_at', [$startDate, $endDate])->get();

        $reportData = [
            'revenue_summary' => $this->calculateRevenueSummary($sales),
            'penalty_summary' => $this->calculatePenaltySummary($penalties),
            'profit_loss_statement' => $this->generateProfitLossStatement($sales, $penalties),
            'cash_flow' => $this->generateCashFlowStatement($sales, $penalties, $startDate, $endDate),
            'key_metrics' => $this->calculateFinancialMetrics($sales, $penalties),
        ];

        return $reportData;
    }

    /**
     * Generate user performance report
     */
    public function generateUserPerformanceReport(array $filters): array
    {
        $startDate = $filters['start_date'] ?? now()->startOfMonth();
        $endDate = $filters['end_date'] ?? now()->endOfMonth();
        $department = $filters['department'] ?? null;

        $query = User::where('status', 'active')
            ->with(['attendances' => function ($q) use ($startDate, $endDate) {
                $q->whereBetween('date', [$startDate, $endDate]);
            }, 'roles', 'penalties' => function ($q) use ($startDate, $endDate) {
                $q->whereBetween('created_at', [$startDate, $endDate]);
            }]);

        if ($department) {
            $query->whereHas('roles', function ($q) use ($department) {
                $q->where('name', $department);
            });
        }

        $users = $query->get();

        $reportData = [
            'summary' => $this->calculateUserPerformanceSummary($users),
            'individual_performance' => $users->map(function ($user) {
                return [
                    'user_id' => $user->id,
                    'name' => $user->name,
                    'nim' => $user->nim,
                    'department' => $user->roles->first()->name ?? 'N/A',
                    'attendance_rate' => $this->calculateUserAttendanceRate($user),
                    'punctuality_rate' => $this->calculateUserPunctualityRate($user),
                    'total_penalties' => $user->penalties->sum('amount'),
                    'penalty_count' => $user->penalties->count(),
                    'performance_score' => $this->calculateUserPerformanceScore($user),
                ];
            })->toArray(),
            'department_ranking' => $this->getDepartmentRanking($users),
            'top_performers' => $this->getTopPerformers($users),
            'areas_for_improvement' => $this->getAreasForImprovement($users),
        ];

        return $reportData;
    }

    /**
     * Generate inventory report
     */
    public function generateInventoryReport(array $filters): array
    {
        $categoryId = $filters['category_id'] ?? null;
        $lowStockThreshold = $filters['low_stock_threshold'] ?? 10;

        $query = Product::with(['category', 'saleItems' => function ($q) use ($filters) {
            if (isset($filters['start_date'])) {
                $q->whereHas('sale', function ($sq) use ($filters) {
                    $sq->whereBetween('created_at', [$filters['start_date'], $filters['end_date'] ?? now()])
                       ->where('status', 'completed');
                });
            }
        }]);

        if ($categoryId) {
            $query->where('category_id', $categoryId);
        }

        $products = $query->get();

        $reportData = [
            'summary' => $this->calculateInventorySummary($products),
            'stock_levels' => $products->map(function ($product) use ($lowStockThreshold) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'sku' => $product->sku,
                    'category' => $product->category->name ?? 'N/A',
                    'current_stock' => $product->stock,
                    'min_stock' => $product->min_stock,
                    'max_stock' => $product->max_stock,
                    'stock_status' => $this->getStockStatus($product, $lowStockThreshold),
                    'total_sold' => $product->saleItems->sum('quantity'),
                    'revenue' => $product->saleItems->sum('subtotal'),
                    'last_sold' => $product->saleItems->last()?->sale?->created_at?->format('Y-m-d'),
                ];
            })->toArray(),
            'low_stock_items' => $this->getLowStockItems($products, $lowStockThreshold),
            'overstock_items' => $this->getOverstockItems($products),
            'fast_moving_items' => $this->getFastMovingItems($products),
            'slow_moving_items' => $this->getSlowMovingItems($products),
        ];

        return $reportData;
    }

    /**
     * Export report to different formats
     */
    public function exportReport(array $reportData, string $format, string $filename): string
    {
        try {
            switch (strtolower($format)) {
                case 'excel':
                    return $this->exportToExcel($reportData, $filename);
                case 'pdf':
                    return $this->exportToPDF($reportData, $filename);
                case 'csv':
                    return $this->exportToCSV($reportData, $filename);
                case 'json':
                    return $this->exportToJSON($reportData, $filename);
                default:
                    throw new \InvalidArgumentException("Unsupported format: {$format}");
            }
        } catch (\Exception $e) {
            \Log::error('Report export failed', [
                'format' => $format,
                'filename' => $filename,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Schedule report generation
     */
    public function scheduleReport(array $reportConfig): bool
    {
        try {
            $scheduledReport = [
                'id' => uniqid(),
                'report_type' => $reportConfig['report_type'],
                'filters' => $reportConfig['filters'],
                'format' => $reportConfig['format'],
                'recipients' => $reportConfig['recipients'],
                'frequency' => $reportConfig['frequency'],
                'next_run' => $this->calculateNextRun($reportConfig['frequency']),
                'created_at' => now()->toISOString(),
            ];

            // Store scheduled report configuration
            Storage::disk('local')->put(
                "scheduled_reports/{$scheduledReport['id']}.json",
                json_encode($scheduledReport)
            );

            // Queue the report generation
            GenerateReportJob::dispatch($scheduledReport)
                ->delay(Carbon::parse($scheduledReport['next_run']));

            return true;

        } catch (\Exception $e) {
            \Log::error('Failed to schedule report', [
                'report_config' => $reportConfig,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Get available report templates
     */
    public function getReportTemplates(): array
    {
        return [
            'attendance' => [
                'name' => 'Laporan Kehadiran',
                'description' => 'Laporan kehadiran karyawan dengan detail check-in/out',
                'filters' => ['start_date', 'end_date', 'user_id', 'department'],
                'formats' => ['excel', 'pdf', 'csv'],
            ],
            'sales' => [
                'name' => 'Laporan Penjualan',
                'description' => 'Laporan penjualan dengan breakdown produk dan pembayaran',
                'filters' => ['start_date', 'end_date', 'cashier_id', 'payment_method'],
                'formats' => ['excel', 'pdf', 'csv'],
            ],
            'financial' => [
                'name' => 'Laporan Keuangan',
                'description' => 'Laporan keuangan lengkap dengan P&L dan cash flow',
                'filters' => ['start_date', 'end_date'],
                'formats' => ['excel', 'pdf'],
            ],
            'user_performance' => [
                'name' => 'Laporan Kinerja User',
                'description' => 'Laporan kinerja individu dan departemen',
                'filters' => ['start_date', 'end_date', 'department'],
                'formats' => ['excel', 'pdf'],
            ],
            'inventory' => [
                'name' => 'Laporan Inventaris',
                'description' => 'Laporan stok dan pergerakan produk',
                'filters' => ['category_id', 'low_stock_threshold'],
                'formats' => ['excel', 'pdf', 'csv'],
            ],
        ];
    }

    /**
     * Helper methods for calculations
     */
    protected function calculateAttendanceSummary($attendances): array
    {
        $totalScheduled = ScheduleAssignment::whereBetween('date', [
            $attendances->min('date'),
            $attendances->max('date')
        ])->where('status', 'scheduled')->count();

        $presentCount = $attendances->whereIn('status', ['present', 'late'])->count();
        $lateCount = $attendances->where('status', 'late')->count();
        $absentCount = $attendances->where('status', 'absent')->count();

        return [
            'total_scheduled' => $totalScheduled,
            'total_present' => $presentCount,
            'total_late' => $lateCount,
            'total_absent' => $absentCount,
            'attendance_rate' => $totalScheduled > 0 ? round(($presentCount / $totalScheduled) * 100, 2) : 0,
            'punctuality_rate' => $presentCount > 0 ? round((($presentCount - $lateCount) / $presentCount) * 100, 2) : 0,
            'average_late_minutes' => $lateCount > 0 ? round($attendances->where('status', 'late')->avg('late_minutes'), 1) : 0,
        ];
    }

    protected function calculateSalesSummary($sales): array
    {
        return [
            'total_transactions' => $sales->count(),
            'total_revenue' => $sales->sum('total_amount'),
            'total_tax' => $sales->sum('tax'),
            'total_discount' => $sales->sum('discount'),
            'average_transaction' => $sales->count() > 0 ? round($sales->sum('total_amount') / $sales->count(), 2) : 0,
            'total_items_sold' => $sales->sum(function ($sale) {
                return $sale->items->sum('quantity');
            }),
        ];
    }

    protected function calculateRevenueSummary($sales): array
    {
        return [
            'gross_revenue' => $sales->sum('total_amount'),
            'total_tax' => $sales->sum('tax'),
            'total_discount' => $sales->sum('discount'),
            'net_revenue' => $sales->sum('total_amount') - $sales->sum('discount'),
            'total_transactions' => $sales->count(),
            'average_transaction_value' => $sales->count() > 0 ? round($sales->sum('total_amount') / $sales->count(), 2) : 0,
        ];
    }

    protected function calculatePenaltySummary($penalties): array
    {
        return [
            'total_penalties' => $penalties->count(),
            'total_penalty_amount' => $penalties->sum('amount'),
            'average_penalty_amount' => $penalties->count() > 0 ? round($penalties->sum('amount') / $penalties->count(), 2) : 0,
            'by_type' => $penalties->groupBy('type')->map(function ($typePenalties) {
                return [
                    'count' => $typePenalties->count(),
                    'total_amount' => $typePenalties->sum('amount'),
                ];
            }),
        ];
    }

    protected function generateProfitLossStatement($sales, $penalties): array
    {
        $revenue = $sales->sum('total_amount');
        $costs = $this->calculateOperationalCosts($sales);
        $penaltyIncome = $penalties->sum('amount');

        return [
            'revenue' => $revenue,
            'cost_of_goods_sold' => $costs['cogs'],
            'operational_expenses' => $costs['operational'],
            'penalty_income' => $penaltyIncome,
            'gross_profit' => $revenue - $costs['cogs'],
            'net_profit' => $revenue - $costs['cogs'] - $costs['operational'] + $penaltyIncome,
            'profit_margin' => $revenue > 0 ? round((($revenue - $costs['cogs'] - $costs['operational'] + $penaltyIncome) / $revenue) * 100, 2) : 0,
        ];
    }

    protected function calculateOperationalCosts($sales): array
    {
        // This would typically include actual cost calculations
        // For now, return simulated values
        return [
            'cogs' => $sales->sum('total_amount') * 0.6, // 60% of revenue
            'operational' => 5000000, // Fixed operational costs
        ];
    }

    protected function exportToExcel(array $reportData, string $filename): string
    {
        // In real implementation, use Laravel Excel or similar library
        $path = "reports/{$filename}.xlsx";
        Storage::disk('public')->put($path, json_encode($reportData));
        return $path;
    }

    protected function exportToPDF(array $reportData, string $filename): string
    {
        // In real implementation, use DOMPDF or similar library
        $path = "reports/{$filename}.pdf";
        Storage::disk('public')->put($path, json_encode($reportData));
        return $path;
    }

    protected function exportToCSV(array $reportData, string $filename): string
    {
        $path = "reports/{$filename}.csv";
        $csv = $this->convertToCSV($reportData);
        Storage::disk('public')->put($path, $csv);
        return $path;
    }

    protected function exportToJSON(array $reportData, string $filename): string
    {
        $path = "reports/{$filename}.json";
        Storage::disk('public')->put($path, json_encode($reportData, JSON_PRETTY_PRINT));
        return $path;
    }

    protected function convertToCSV(array $data): string
    {
        // Simple CSV conversion - in real implementation, handle nested data properly
        $csv = '';
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $csv .= "{$key}," . implode(',', $value) . "\n";
            } else {
                $csv .= "{$key},{$value}\n";
            }
        }
        return $csv;
    }

    protected function calculateNextRun(string $frequency): string
    {
        switch ($frequency) {
            case 'daily':
                return now()->addDay()->toISOString();
            case 'weekly':
                return now()->addWeek()->toISOString();
            case 'monthly':
                return now()->addMonth()->toISOString();
            case 'quarterly':
                return now()->addQuarter()->toISOString();
            default:
                return now()->addWeek()->toISOString();
        }
    }

    // Additional helper methods for various calculations...
    protected function getAttendanceAnalytics($attendances): array
    {
        return [
            'daily_averages' => $attendances->groupBy(function ($attendance) {
                return $attendance->date->format('Y-m-d');
            })->map(function ($dayAttendances) {
                return [
                    'total' => $dayAttendances->count(),
                    'present' => $dayAttendances->whereIn('status', ['present', 'late'])->count(),
                    'late' => $dayAttendances->where('status', 'late')->count(),
                ];
            }),
        ];
    }

    protected function getProductSalesBreakdown($sales): array
    {
        return $sales->flatMap(function ($sale) {
            return $sale->items;
        })->groupBy('product_id')->map(function ($productSales) {
            return [
                'product_name' => $productSales->first()->product->name,
                'total_quantity' => $productSales->sum('quantity'),
                'total_revenue' => $productSales->sum('subtotal'),
            ];
        })->toArray();
    }

    protected function getPaymentMethodBreakdown($sales): array
    {
        return $sales->groupBy('payment_method')->map(function ($methodSales) {
            return [
                'count' => $methodSales->count(),
                'total_amount' => $methodSales->sum('total_amount'),
                'average_amount' => $methodSales->count() > 0 ? round($methodSales->sum('total_amount') / $methodSales->count(), 2) : 0,
            ];
        })->toArray();
    }

    protected function getSalesDailyTrends($sales): array
    {
        return $sales->groupBy(function ($sale) {
            return $sale->created_at->format('Y-m-d');
        })->map(function ($daySales) {
            return [
                'transactions' => $daySales->count(),
                'revenue' => $daySales->sum('total_amount'),
                'average_transaction' => $daySales->count() > 0 ? round($daySales->sum('total_amount') / $daySales->count(), 2) : 0,
            ];
        })->toArray();
    }

    // Additional methods for user performance, inventory, etc. would go here...
    protected function calculateUserPerformanceSummary($users): array
    {
        return [
            'total_users' => $users->count(),
            'average_attendance_rate' => $users->avg(function ($user) {
                return $this->calculateUserAttendanceRate($user);
            }),
            'total_penalties' => $users->sum(function ($user) {
                return $user->penalties->sum('amount');
            }),
        ];
    }

    protected function calculateUserAttendanceRate($user): float
    {
        $totalAttendances = $user->attendances->count();
        $presentAttendances = $user->attendances->whereIn('status', ['present', 'late'])->count();
        
        return $totalAttendances > 0 ? round(($presentAttendances / $totalAttendances) * 100, 2) : 0;
    }

    protected function calculateUserPunctualityRate($user): float
    {
        $presentAttendances = $user->attendances->whereIn('status', ['present', 'late']);
        $onTimeAttendances = $presentAttendances->where('status', 'present');
        
        return $presentAttendances->count() > 0 ? round(($onTimeAttendances->count() / $presentAttendances->count()) * 100, 2) : 0;
    }

    protected function calculateUserPerformanceScore($user): float
    {
        $attendanceRate = $this->calculateUserAttendanceRate($user);
        $punctualityRate = $this->calculateUserPunctualityRate($user);
        $penaltyScore = min(100, max(0, 100 - ($user->penalties->sum('amount') / 10000) * 10)); // Deduct points for penalties
        
        return round(($attendanceRate + $punctualityRate + $penaltyScore) / 3, 2);
    }

    protected function getDepartmentRanking($users): array
    {
        return $users->groupBy(function ($user) {
            return $user->roles->first()->name ?? 'N/A';
        })->map(function ($departmentUsers) {
            return [
                'user_count' => $departmentUsers->count(),
                'average_performance' => $departmentUsers->avg(function ($user) {
                    return $this->calculateUserPerformanceScore($user);
                }),
            ];
        })->toArray();
    }

    protected function getTopPerformers($users): array
    {
        return $users->sortByDesc(function ($user) {
            return $this->calculateUserPerformanceScore($user);
        })->take(10)->map(function ($user) {
            return [
                'name' => $user->name,
                'performance_score' => $this->calculateUserPerformanceScore($user),
                'attendance_rate' => $this->calculateUserAttendanceRate($user),
            ];
        })->toArray();
    }

    protected function getAreasForImprovement($users): array
    {
        $lowPerformers = $users->filter(function ($user) {
            return $this->calculateUserPerformanceScore($user) < 70;
        });

        return [
            'low_performers_count' => $lowPerformers->count(),
            'common_issues' => [
                'attendance' => $lowPerformers->filter(function ($user) {
                    return $this->calculateUserAttendanceRate($user) < 80;
                })->count(),
                'punctuality' => $lowPerformers->filter(function ($user) {
                    return $this->calculateUserPunctualityRate($user) < 80;
                })->count(),
                'penalties' => $lowPerformers->filter(function ($user) {
                    return $user->penalties->count() > 3;
                })->count(),
            ],
        ];
    }

    // Inventory-related helper methods
    protected function calculateInventorySummary($products): array
    {
        return [
            'total_products' => $products->count(),
            'total_stock_value' => $products->sum(function ($product) {
                return $product->stock * $product->price;
            }),
            'low_stock_count' => $products->filter(function ($product) {
                return $product->stock <= $product->min_stock;
            })->count(),
            'out_of_stock_count' => $products->where('stock', 0)->count(),
        ];
    }

    protected function getStockStatus($product, int $threshold): string
    {
        if ($product->stock == 0) {
            return 'out_of_stock';
        } elseif ($product->stock <= $product->min_stock) {
            return 'low_stock';
        } elseif ($product->stock >= $product->max_stock) {
            return 'overstock';
        } else {
            return 'normal';
        }
    }

    protected function getLowStockItems($products, int $threshold): array
    {
        return $products->filter(function ($product) use ($threshold) {
            return $product->stock > 0 && $product->stock <= $product->min_stock;
        })->map(function ($product) {
            return [
                'name' => $product->name,
                'current_stock' => $product->stock,
                'min_stock' => $product->min_stock,
                'recommended_order' => $product->max_stock - $product->stock,
            ];
        })->toArray();
    }

    protected function getOverstockItems($products): array
    {
        return $products->filter(function ($product) {
            return $product->stock > $product->max_stock;
        })->map(function ($product) {
            return [
                'name' => $product->name,
                'current_stock' => $product->stock,
                'max_stock' => $product->max_stock,
                'excess_stock' => $product->stock - $product->max_stock,
            ];
        })->toArray();
    }

    protected function getFastMovingItems($products): array
    {
        return $products->sortByDesc(function ($product) {
            return $product->saleItems->sum('quantity');
        })->take(10)->map(function ($product) {
            return [
                'name' => $product->name,
                'total_sold' => $product->saleItems->sum('quantity'),
                'revenue' => $product->saleItems->sum('subtotal'),
            ];
        })->toArray();
    }

    protected function getSlowMovingItems($products): array
    {
        return $products->filter(function ($product) {
            return $product->saleItems->sum('quantity') < 5 && $product->stock > 0;
        })->map(function ($product) {
            return [
                'name' => $product->name,
                'current_stock' => $product->stock,
                'total_sold' => $product->saleItems->sum('quantity'),
                'stock_age_days' => now()->diffInDays($product->updated_at),
            ];
        })->toArray();
    }

    protected function generateCashFlowStatement($sales, $penalties, Carbon $startDate, Carbon $endDate): array
    {
        // Simplified cash flow calculation
        $cashIn = $sales->sum('total_amount') + $penalties->sum('amount');
        $cashOut = $this->calculateOperationalCosts($sales)['operational'];
        
        return [
            'cash_in' => $cashIn,
            'cash_out' => $cashOut,
            'net_cash_flow' => $cashIn - $cashOut,
            'opening_balance' => 10000000, // Mock opening balance
            'closing_balance' => 10000000 + ($cashIn - $cashOut),
        ];
    }

    protected function calculateFinancialMetrics($sales, $penalties): array
    {
        $revenue = $sales->sum('total_amount');
        $costs = $this->calculateOperationalCosts($sales);
        $netProfit = $revenue - $costs['cogs'] - $costs['operational'] + $penalties->sum('amount');
        
        return [
            'revenue_growth_rate' => 15.5, // Mock calculation
            'profit_margin' => $revenue > 0 ? round(($netProfit / $revenue) * 100, 2) : 0,
            'return_on_investment' => 12.3, // Mock calculation
            'debt_to_equity_ratio' => 0.45, // Mock calculation
        ];
    }
}
