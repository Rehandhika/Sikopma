<div class="space-y-6">
    <!-- Header Controls -->
    <div class="bg-white rounded-lg shadow p-4">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-2xl font-bold text-gray-900">Business Intelligence Dashboard</h2>
            <div class="flex items-center space-x-4">
                <!-- Period Selector -->
                <select wire:model.live="selectedPeriod" class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                    <option value="today">Hari Ini</option>
                    <option value="week">Minggu Ini</option>
                    <option value="month">Bulan Ini</option>
                    <option value="quarter">Kuartal Ini</option>
                    <option value="year">Tahun Ini</option>
                </select>

                <!-- Auto Refresh Toggle -->
                <div class="flex items-center space-x-2">
                    <button wire:click="toggleAutoRefresh" 
                            class="px-3 py-2 {{ $autoRefresh ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-700' }} rounded-lg transition">
                        <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                        </svg>
                        {{ $autoRefresh ? 'Auto Refresh ON' : 'Auto Refresh OFF' }}
                    </button>
                    
                    @if($autoRefresh)
                        <select wire:model.live="refreshInterval" class="px-2 py-1 border border-gray-300 rounded text-sm">
                            <option value="15">15s</option>
                            <option value="30">30s</option>
                            <option value="60">1m</option>
                            <option value="300">5m</option>
                        </select>
                    @endif
                </div>

                <!-- Actions -->
                <button wire:click="exportReport" class="btn btn-white">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    Export
                </button>
                
                <button wire:click="scheduleReport" class="btn btn-indigo-600 text-white">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    Jadwalkan
                </button>
            </div>
        </div>

        <!-- Date Range Display -->
        <div class="text-sm text-gray-600">
            Periode: {{ $startDate->locale('id')->isoFormat('D MMMM YYYY') }} - {{ $endDate->locale('id')->isoFormat('D MMMM YYYY') }}
        </div>
    </div>

    <!-- KPI Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Attendance KPIs -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900">Kehadiran</h3>
                <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                    <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                </div>
            </div>
            
            <div class="space-y-3">
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-600">Tingkat Kehadiran</span>
                    <span class="text-lg font-bold text-gray-900">{{ $getKPIDisplay('attendance_metrics', 'attendance_rate')['formatted_value'] }}</span>
                </div>
                
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-600">Tingkat Ketepatan</span>
                    <span class="text-lg font-bold text-gray-900">{{ $getKPIDisplay('attendance_metrics', 'punctuality_rate')['formatted_value'] }}</span>
                </div>
                
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-600">Total Hadir</span>
                    <span class="text-lg font-bold text-green-600">{{ $kpis['attendance_metrics']['total_present'] ?? 0 }}</span>
                </div>
                
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-600">Total Terlambat</span>
                    <span class="text-lg font-bold text-yellow-600">{{ $kpis['attendance_metrics']['total_late'] ?? 0 }}</span>
                </div>
            </div>
        </div>

        <!-- Sales KPIs -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900">Penjualan</h3>
                <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                    <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>
            
            <div class="space-y-3">
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-600">Total Pendapatan</span>
                    <span class="text-lg font-bold text-gray-900">{{ $getKPIDisplay('sales_metrics', 'total_revenue')['formatted_value'] }}</span>
                </div>
                
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-600">Total Transaksi</span>
                    <span class="text-lg font-bold text-gray-900">{{ $kpis['sales_metrics']['total_transactions'] ?? 0 }}</span>
                </div>
                
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-600">Rata-rata Transaksi</span>
                    <span class="text-lg font-bold text-gray-900">{{ $getKPIDisplay('sales_metrics', 'average_transaction')['formatted_value'] }}</span>
                </div>
                
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-600">Pertumbuhan</span>
                    <span class="text-lg font-bold {{ $kpis['sales_metrics']['growth_rate'] > 0 ? 'text-green-600' : 'text-red-600' }}">
                        {{ $kpis['sales_metrics']['growth_rate'] ?? 0 }}%
                    </span>
                </div>
            </div>
        </div>

        <!-- Operational KPIs -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900">Operasional</h3>
                <div class="w-8 h-8 bg-purple-100 rounded-full flex items-center justify-center">
                    <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                </div>
            </div>
            
            <div class="space-y-3">
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-600">Total Pengguna</span>
                    <span class="text-lg font-bold text-gray-900">{{ $kpis['operational_metrics']['total_users'] ?? 0 }}</span>
                </div>
                
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-600">Pengguna Aktif</span>
                    <span class="text-lg font-bold text-green-600">{{ $kpis['operational_metrics']['active_users'] ?? 0 }}</span>
                </div>
                
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-600">Efisiensi Jadwal</span>
                    <span class="text-lg font-bold text-gray-900">{{ $getKPIDisplay('operational_metrics', 'schedule_efficiency')['formatted_value'] }}</span>
                </div>
                
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-600">Request Pending</span>
                    <span class="text-lg font-bold text-yellow-600">{{ ($kpis['operational_metrics']['pending_swap_requests'] ?? 0) + ($kpis['operational_metrics']['pending_leave_requests'] ?? 0) }}</span>
                </div>
            </div>
        </div>

        <!-- Financial KPIs -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900">Keuangan</h3>
                <div class="w-8 h-8 bg-yellow-100 rounded-full flex items-center justify-center">
                    <svg class="w-4 h-4 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                    </svg>
                </div>
            </div>
            
            <div class="space-y-3">
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-600">Laba Bersih</span>
                    <span class="text-lg font-bold {{ ($kpis['financial_metrics']['net_profit'] ?? 0) > 0 ? 'text-green-600' : 'text-red-600' }}">
                        {{ $getKPIDisplay('financial_metrics', 'net_profit')['formatted_value'] }}
                    </span>
                </div>
                
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-600">Margin Laba</span>
                    <span class="text-lg font-bold text-gray-900">{{ $getKPIDisplay('financial_metrics', 'profit_margin')['formatted_value'] }}</span>
                </div>
                
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-600">Total Penalti</span>
                    <span class="text-lg font-bold text-red-600">{{ $getKPIDisplay('financial_metrics', 'total_penalties')['formatted_value'] }}</span>
                </div>
                
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-600">Pendapatan/Hari</span>
                    <span class="text-lg font-bold text-gray-900">{{ $getKPIDisplay('financial_metrics', 'revenue_per_day')['formatted_value'] }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Real-time Metrics -->
    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Metrik Real-time</h3>
        <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
            <div class="text-center">
                <div class="relative inline-flex items-center justify-center">
                    <svg class="w-16 h-16 transform -rotate-90">
                        <circle cx="32" cy="32" r="28" stroke="#e5e7eb" stroke-width="4" fill="none"></circle>
                        <circle cx="32" cy="32" r="28" stroke="#10b981" stroke-width="4" fill="none"
                                stroke-dasharray="{{ ($realTimeMetrics['online_users'] / 50) * 176 }} 176"
                                stroke-linecap="round"></circle>
                    </svg>
                    <div class="absolute">
                        <span class="text-lg font-bold">{{ $realTimeMetrics['online_users'] }}</span>
                    </div>
                </div>
                <p class="text-sm text-gray-600 mt-2">Online</p>
            </div>

            <div class="text-center">
                <div class="relative inline-flex items-center justify-center">
                    <svg class="w-16 h-16 transform -rotate-90">
                        <circle cx="32" cy="32" r="28" stroke="#e5e7eb" stroke-width="4" fill="none"></circle>
                        <circle cx="32" cy="32" r="28" stroke="#3b82f6" stroke-width="4" fill="none"
                                stroke-dasharray="{{ ($realTimeMetrics['active_sessions'] / 100) * 176 }} 176"
                                stroke-linecap="round"></circle>
                    </svg>
                    <div class="absolute">
                        <span class="text-lg font-bold">{{ $realTimeMetrics['active_sessions'] }}</span>
                    </div>
                </div>
                <p class="text-sm text-gray-600 mt-2">Sesi Aktif</p>
            </div>

            <div class="text-center">
                <div class="relative inline-flex items-center justify-center">
                    <svg class="w-16 h-16 transform -rotate-90">
                        <circle cx="32" cy="32" r="28" stroke="#e5e7eb" stroke-width="4" fill="none"></circle>
                        <circle cx="32" cy="32" r="28" stroke="#8b5cf6" stroke-width="4" fill="none"
                                stroke-dasharray="{{ ($realTimeMetrics['today_attendance']['total'] / 50) * 176 }} 176"
                                stroke-linecap="round"></circle>
                    </svg>
                    <div class="absolute">
                        <span class="text-lg font-bold">{{ $realTimeMetrics['today_attendance']['total'] }}</span>
                    </div>
                </div>
                <p class="text-sm text-gray-600 mt-2">Absen Hari Ini</p>
            </div>

            <div class="text-center">
                <div class="relative inline-flex items-center justify-center">
                    <svg class="w-16 h-16 transform -rotate-90">
                        <circle cx="32" cy="32" r="28" stroke="#e5e7eb" stroke-width="4" fill="none"></circle>
                        <circle cx="32" cy="32" r="28" stroke="#f59e0b" stroke-width="4" fill="none"
                                stroke-dasharray="{{ ($realTimeMetrics['today_sales']['total'] / 100) * 176 }} 176"
                                stroke-linecap="round"></circle>
                    </svg>
                    <div class="absolute">
                        <span class="text-lg font-bold">{{ $realTimeMetrics['today_sales']['total'] }}</span>
                    </div>
                </div>
                <p class="text-sm text-gray-600 mt-2">Transaksi Hari Ini</p>
            </div>

            <div class="text-center">
                <div class="relative inline-flex items-center justify-center">
                    <svg class="w-16 h-16 transform -rotate-90">
                        <circle cx="32" cy="32" r="28" stroke="#e5e7eb" stroke-width="4" fill="none"></circle>
                        <circle cx="32" cy="32" r="28" stroke="#ef4444" stroke-width="4" fill="none"
                                stroke-dasharray="{{ ($realTimeMetrics['system_health']['cpu_usage'] / 100) * 176 }} 176"
                                stroke-linecap="round"></circle>
                    </svg>
                    <div class="absolute">
                        <span class="text-lg font-bold">{{ $realTimeMetrics['system_health']['cpu_usage'] }}%</span>
                    </div>
                </div>
                <p class="text-sm text-gray-600 mt-2">CPU Usage</p>
            </div>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Attendance Trend Chart -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900">Trend Kehadiran</h3>
                <select wire:model.live="chartType" class="px-2 py-1 border border-gray-300 rounded text-sm">
                    <option value="line">Line</option>
                    <option value="bar">Bar</option>
                    <option value="area">Area</option>
                </select>
            </div>
            <div class="h-64">
                <canvas id="attendance-chart"></canvas>
            </div>
        </div>

        <!-- Sales Trend Chart -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900">Trend Penjualan</h3>
                <button wire:click="toggleComparison" 
                        class="px-2 py-1 {{ $compareWithPrevious ? 'bg-indigo-100 text-indigo-700' : 'bg-gray-100 text-gray-700' }} rounded text-sm">
                    {{ $compareWithPrevious ? 'Bandingkan: ON' : 'Bandingkan: OFF' }}
                </button>
            </div>
            <div class="h-64">
                <canvas id="sales-chart"></canvas>
            </div>
        </div>
    </div>

    <!-- Predictions Section -->
    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Prediksi & Analitik</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="border border-gray-200 rounded-lg p-4">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-sm font-medium text-gray-700">Prediksi Kehadiran</span>
                    <span class="text-xs bg-blue-100 text-blue-800 px-2 py-1 rounded">
                        {{ $predictions['attendance_forecast']['confidence'] }}% confidence
                    </span>
                </div>
                <div class="text-2xl font-bold text-gray-900">{{ $predictions['attendance_forecast']['prediction'] }}</div>
                <div class="text-sm text-gray-600">orang/hari</div>
                <div class="text-xs {{ $predictions['attendance_forecast']['trend'] === 'improving' ? 'text-green-600' : 'text-red-600' }} mt-1">
                    Trend: {{ $predictions['attendance_forecast']['trend'] }}
                </div>
            </div>

            <div class="border border-gray-200 rounded-lg p-4">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-sm font-medium text-gray-700">Prediksi Penjualan</span>
                    <span class="text-xs bg-green-100 text-green-800 px-2 py-1 rounded">
                        {{ $predictions['sales_forecast']['confidence'] }}% confidence
                    </span>
                </div>
                <div class="text-2xl font-bold text-gray-900">Rp {{ number_format($predictions['sales_forecast']['prediction'], 0, ',', '.') }}</div>
                <div class="text-sm text-gray-600">per hari</div>
                <div class="text-xs {{ $predictions['sales_forecast']['trend'] === 'growing' ? 'text-green-600' : 'text-red-600' }} mt-1">
                    Trend: {{ $predictions['sales_forecast']['trend'] }}
                </div>
            </div>

            <div class="border border-gray-200 rounded-lg p-4">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-sm font-medium text-gray-700">Kebutuhan Staff</span>
                    <span class="text-xs bg-purple-100 text-purple-800 px-2 py-1 rounded">
                        {{ $predictions['staffing_needs']['utilization_rate'] }}% utilized
                    </span>
                </div>
                <div class="text-2xl font-bold text-gray-900">{{ $predictions['staffing_needs']['required_staff'] }}</div>
                <div class="text-sm text-gray-600">staff dibutuhkan</div>
                <div class="text-xs {{ $predictions['staffing_needs']['recommendation'] === 'optimal' ? 'text-green-600' : 'text-yellow-600' }} mt-1">
                    {{ $predictions['staffing_needs']['recommendation'] }}
                </div>
            </div>

            <div class="border border-gray-200 rounded-lg p-4">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-sm font-medium text-gray-700">Prediksi Revenue</span>
                    <span class="text-xs bg-yellow-100 text-yellow-800 px-2 py-1 rounded">
                        {{ $predictions['revenue_forecast']['confidence'] }}% confidence
                    </span>
                </div>
                <div class="text-2xl font-bold text-gray-900">Rp {{ number_format($predictions['revenue_forecast']['prediction'], 0, ',', '.') }}</div>
                <div class="text-sm text-gray-600">per bulan</div>
                <div class="text-xs {{ $predictions['revenue_forecast']['growth_rate'] > 0 ? 'text-green-600' : 'text-red-600' }} mt-1">
                    +{{ $predictions['revenue_forecast']['growth_rate'] }}% growth
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Chart JavaScript -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize Chart.js
    Chart.defaults.font.family = 'system-ui, -apple-system, sans-serif';
    
    // Attendance Chart
    const attendanceCtx = document.getElementById('attendance-chart');
    if (attendanceCtx) {
        new Chart(attendanceCtx, {
            type: '{{ $chartType }}',
            data: {
                labels: @json(array_keys($trends['attendance']['data'] ?? [])),
                datasets: [{
                    label: 'Tingkat Kehadiran (%)',
                    data: @json(array_values($trends['attendance']['data'] ?? [])),
                    borderColor: 'rgb(59, 130, 246)',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 100
                    }
                }
            }
        });
    }

    // Sales Chart
    const salesCtx = document.getElementById('sales-chart');
    if (salesCtx) {
        new Chart(salesCtx, {
            type: '{{ $chartType }}',
            data: {
                labels: @json(array_keys($trends['sales']['data'] ?? [])),
                datasets: [{
                    label: 'Penjualan (Rp)',
                    data: @json(array_values($trends['sales']['data'] ?? [])),
                    borderColor: 'rgb(34, 197, 94)',
                    backgroundColor: 'rgba(34, 197, 94, 0.1)',
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    }

    // Auto refresh functionality
    @this.on('startAutoRefresh', (interval) => {
        setInterval(() => {
            @this.call('refreshData');
        }, interval * 1000);
    });

    @this.on('stopAutoRefresh', () => {
        // Stop auto refresh logic
    });

    @this.on('updateRefreshInterval', (interval) => {
        // Update refresh interval
    });

    // Handle drill down
    @this.on('drillDownData', (data) => {
        console.log('Drill down data:', data);
        // Show modal or update view with detailed data
    });

    @this.on('dataRefreshed', () => {
        console.log('Dashboard data refreshed');
    });

    @this.on('reportExported', (data) => {
        // Trigger download
        const link = document.createElement('a');
        link.href = data.url;
        link.download = data.filename;
        link.click();
    });

    @this.on('reportScheduled', (message) => {
        // Show success notification
        alert(message);
    });
});
</script>
