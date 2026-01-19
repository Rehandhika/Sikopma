<div class="space-y-4 sm:space-y-6" wire:init="$refresh">
    
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-gray-900 dark:text-white">Laporan Penjualan</h1>
            <p class="text-xs sm:text-sm text-gray-500 mt-0.5">
                <?php echo e(\Carbon\Carbon::parse($dateFrom)->translatedFormat('d M Y')); ?> - 
                <?php echo e(\Carbon\Carbon::parse($dateTo)->translatedFormat('d M Y')); ?>

            </p>
        </div>
    </div>

    
    <div class="bg-white dark:bg-gray-800 rounded-lg p-3 sm:p-4 border border-gray-200 dark:border-gray-700">
        <div class="flex flex-col sm:flex-row sm:items-center gap-3">
            <div class="flex flex-wrap gap-2">
                <!--[if BLOCK]><![endif]--><?php $__currentLoopData = ['today' => 'Hari Ini', 'week' => 'Minggu', 'month' => 'Bulan']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <button wire:click="setPeriod('<?php echo e($key); ?>')"
                        class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                            'px-3 py-1.5 text-xs sm:text-sm font-medium rounded-lg transition',
                            'bg-primary-600 text-white' => $period === $key,
                            'bg-gray-100 text-gray-700 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300' => $period !== $key,
                        ]); ?>">
                        <?php echo e($label); ?>

                    </button>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
            </div>
            
            <div class="flex items-center gap-2 sm:ml-auto">
                <input type="date" wire:model.live.debounce.500ms="dateFrom" 
                    class="flex-1 sm:flex-none px-2 py-1.5 text-xs sm:text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                <span class="text-gray-400 text-xs">—</span>
                <input type="date" wire:model.live.debounce.500ms="dateTo" 
                    class="flex-1 sm:flex-none px-2 py-1.5 text-xs sm:text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
            </div>
        </div>
    </div>

    
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
        <div class="bg-gradient-to-br from-emerald-500 to-emerald-600 rounded-lg p-3 sm:p-4 text-white">
            <p class="text-emerald-100 text-xs font-medium">Pendapatan</p>
            <p class="text-lg sm:text-xl font-bold mt-1"><?php echo e(format_currency($this->stats->revenue)); ?></p>
            <p class="text-emerald-200 text-xs mt-0.5"><?php echo e(number_format($this->stats->total)); ?> transaksi</p>
        </div>
        
        <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-lg p-3 sm:p-4 text-white">
            <p class="text-blue-100 text-xs font-medium">Rata-rata</p>
            <p class="text-lg sm:text-xl font-bold mt-1"><?php echo e(format_currency($this->stats->avg_amount)); ?></p>
            <p class="text-blue-200 text-xs mt-0.5">per transaksi</p>
        </div>
        
        <div class="bg-gradient-to-br from-violet-500 to-violet-600 rounded-lg p-3 sm:p-4 text-white">
            <p class="text-violet-100 text-xs font-medium">Terbesar</p>
            <p class="text-lg sm:text-xl font-bold mt-1"><?php echo e(format_currency($this->stats->max_amount)); ?></p>
            <p class="text-violet-200 text-xs mt-0.5">nilai tertinggi</p>
        </div>
        
        <div class="bg-gradient-to-br from-amber-500 to-amber-600 rounded-lg p-3 sm:p-4 text-white">
            <p class="text-amber-100 text-xs font-medium">Transaksi</p>
            <p class="text-lg sm:text-xl font-bold mt-1"><?php echo e(number_format($this->stats->total)); ?></p>
            <p class="text-amber-200 text-xs mt-0.5">total</p>
        </div>
    </div>

    
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
        
        <div class="lg:col-span-2 bg-white dark:bg-gray-800 rounded-lg p-4 border border-gray-200 dark:border-gray-700">
            <h3 class="font-semibold text-sm text-gray-900 dark:text-white mb-3">Grafik Penjualan</h3>
            <div class="h-48 sm:h-56" wire:ignore>
                <canvas id="revenueChart"></canvas>
            </div>
        </div>

        
        <div class="bg-white dark:bg-gray-800 rounded-lg p-4 border border-gray-200 dark:border-gray-700">
            <h3 class="font-semibold text-sm text-gray-900 dark:text-white mb-3">Metode Pembayaran</h3>
            <div class="space-y-3">
                <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $this->paymentSummary; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $method): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div>
                        <div class="flex justify-between text-xs mb-1">
                            <span class="text-gray-700 dark:text-gray-300"><?php echo e($method['name']); ?></span>
                            <span class="font-semibold"><?php echo e($method['count']); ?> (<?php echo e($method['percentage']); ?>%)</span>
                        </div>
                        <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                            <div class="bg-<?php echo e($method['color']); ?>-500 h-2 rounded-full" style="width: <?php echo e($method['percentage']); ?>%"></div>
                        </div>
                        <p class="text-xs text-gray-500 mt-0.5"><?php echo e(format_currency($method['amount'])); ?></p>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
            </div>
        </div>
    </div>

    
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
        
        <div class="bg-white dark:bg-gray-800 rounded-lg p-4 border border-gray-200 dark:border-gray-700">
            <h3 class="font-semibold text-sm text-gray-900 dark:text-white mb-3">Produk Terlaris</h3>
            <!--[if BLOCK]><![endif]--><?php if($this->topProducts->isNotEmpty()): ?>
                <div class="space-y-2">
                    <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $this->topProducts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="flex items-center gap-2 p-2 rounded hover:bg-gray-50 dark:hover:bg-gray-700/50">
                            <span class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                                'w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold',
                                'bg-amber-100 text-amber-700 dark:bg-amber-900/50 dark:text-amber-400' => $i < 3,
                                'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400' => $i >= 3,
                            ]); ?>"><?php echo e($i + 1); ?></span>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-900 dark:text-white truncate"><?php echo e($product->name); ?></p>
                                <p class="text-xs text-gray-500"><?php echo e(number_format($product->total_qty)); ?> terjual</p>
                            </div>
                            <span class="text-xs font-bold text-emerald-600 dark:text-emerald-400">
                                <?php echo e(format_currency($product->total_revenue)); ?>

                            </span>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
                </div>
            <?php else: ?>
                <p class="text-sm text-gray-400 text-center py-8">Belum ada data</p>
            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
        </div>

        
        <div class="bg-white dark:bg-gray-800 rounded-lg p-4 border border-gray-200 dark:border-gray-700">
            <h3 class="font-semibold text-sm text-gray-900 dark:text-white mb-3">Distribusi Jam</h3>
            <div class="h-36" wire:ignore>
                <canvas id="hourlyChart"></canvas>
            </div>
            <!--[if BLOCK]><![endif]--><?php if($this->peakHour): ?>
                <div class="mt-3 p-2 bg-primary-50 dark:bg-primary-900/20 rounded text-center">
                    <p class="text-xs text-gray-600 dark:text-gray-400">
                        Jam Tersibuk: 
                        <span class="font-bold text-primary-600 dark:text-primary-400">
                            <?php echo e(str_pad($this->peakHour['hour'], 2, '0', STR_PAD_LEFT)); ?>:00
                        </span>
                        (<?php echo e($this->peakHour['count']); ?> tx)
                    </p>
                </div>
            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
        </div>
    </div>

    
    <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
            <h3 class="font-semibold text-sm text-gray-900 dark:text-white">Detail Transaksi</h3>
            <span class="text-xs text-gray-500"><?php echo e($sales->total()); ?> data</span>
        </div>
        
        
        <div class="sm:hidden divide-y divide-gray-200 dark:divide-gray-700">
            <!--[if BLOCK]><![endif]--><?php $__empty_1 = true; $__currentLoopData = $sales; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $sale): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <div class="p-3 space-y-1">
                    <div class="flex justify-between items-start">
                        <span class="font-mono text-xs bg-gray-100 dark:bg-gray-700 px-1.5 py-0.5 rounded"><?php echo e($sale->invoice_number); ?></span>
                        <span class="font-semibold text-sm text-gray-900 dark:text-white"><?php echo e(format_currency($sale->total_amount)); ?></span>
                    </div>
                    <div class="flex justify-between text-xs text-gray-500">
                        <span><?php echo e($sale->created_at->format('d/m H:i')); ?></span>
                        <span class="px-1.5 py-0.5 rounded text-xs font-medium
                            <?php echo e($sale->payment_method === 'cash' ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/50 dark:text-emerald-400' : ''); ?>

                            <?php echo e($sale->payment_method === 'transfer' ? 'bg-blue-100 text-blue-700 dark:bg-blue-900/50 dark:text-blue-400' : ''); ?>

                            <?php echo e($sale->payment_method === 'qris' ? 'bg-violet-100 text-violet-700 dark:bg-violet-900/50 dark:text-violet-400' : ''); ?>">
                            <?php echo e(strtoupper($sale->payment_method)); ?>

                        </span>
                    </div>
                    <div class="text-xs text-gray-500"><?php echo e($sale->cashier->name ?? '-'); ?> • <?php echo e($sale->items_count); ?> item</div>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <div class="p-8 text-center text-gray-400 text-sm">Tidak ada transaksi</div>
            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
        </div>

        
        <div class="hidden sm:block overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 dark:bg-gray-900/50 text-xs text-gray-500 uppercase">
                    <tr>
                        <th class="px-4 py-2.5 text-left">Invoice</th>
                        <th class="px-4 py-2.5 text-left">Tanggal</th>
                        <th class="px-4 py-2.5 text-left">Kasir</th>
                        <th class="px-4 py-2.5 text-center">Item</th>
                        <th class="px-4 py-2.5 text-center">Metode</th>
                        <th class="px-4 py-2.5 text-right">Total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    <!--[if BLOCK]><![endif]--><?php $__empty_1 = true; $__currentLoopData = $sales; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $sale): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-900/30">
                            <td class="px-4 py-2.5">
                                <span class="font-mono text-xs bg-gray-100 dark:bg-gray-700 px-1.5 py-0.5 rounded"><?php echo e($sale->invoice_number); ?></span>
                            </td>
                            <td class="px-4 py-2.5 text-gray-600 dark:text-gray-400 text-xs">
                                <?php echo e($sale->created_at->format('d/m/Y H:i')); ?>

                            </td>
                            <td class="px-4 py-2.5 text-gray-900 dark:text-white"><?php echo e($sale->cashier->name ?? '-'); ?></td>
                            <td class="px-4 py-2.5 text-center text-xs"><?php echo e($sale->items_count); ?></td>
                            <td class="px-4 py-2.5 text-center">
                                <span class="px-1.5 py-0.5 rounded text-xs font-medium
                                    <?php echo e($sale->payment_method === 'cash' ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/50 dark:text-emerald-400' : ''); ?>

                                    <?php echo e($sale->payment_method === 'transfer' ? 'bg-blue-100 text-blue-700 dark:bg-blue-900/50 dark:text-blue-400' : ''); ?>

                                    <?php echo e($sale->payment_method === 'qris' ? 'bg-violet-100 text-violet-700 dark:bg-violet-900/50 dark:text-violet-400' : ''); ?>">
                                    <?php echo e(strtoupper($sale->payment_method)); ?>

                                </span>
                            </td>
                            <td class="px-4 py-2.5 text-right font-semibold text-gray-900 dark:text-white">
                                <?php echo e(format_currency($sale->total_amount)); ?>

                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="6" class="px-4 py-12 text-center text-gray-400 text-sm">Tidak ada transaksi</td>
                        </tr>
                    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                </tbody>
            </table>
        </div>
        
        <!--[if BLOCK]><![endif]--><?php if($sales->hasPages()): ?>
            <div class="px-4 py-3 border-t border-gray-200 dark:border-gray-700">
                <?php echo e($sales->links()); ?>

            </div>
        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
    </div>

    
    <div wire:loading.delay class="fixed inset-0 bg-black/20 backdrop-blur-sm flex items-center justify-center z-50">
        <div class="bg-white dark:bg-gray-800 rounded-lg px-4 py-3 shadow-lg flex items-center gap-2">
            <svg class="animate-spin h-4 w-4 text-primary-600" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
            </svg>
            <span class="text-sm text-gray-700 dark:text-gray-300">Memuat...</span>
        </div>
    </div>

    
    <div id="chartData" class="hidden"
        data-labels='<?php echo json_encode($this->chartData["labels"], 15, 512) ?>'
        data-revenue='<?php echo json_encode($this->chartData["revenue"], 15, 512) ?>'
        data-hourly='<?php echo json_encode(array_values($this->hourlySales), 15, 512) ?>'>
    </div>
</div>

    <?php
        $__scriptKey = '2871291815-0';
        ob_start();
    ?>
<script>
(function() {
    let charts = {};
    const colors = { emerald: '#10b981', blue: '#3b82f6' };

    function fmt(v) {
        return v >= 1e6 ? (v/1e6).toFixed(1)+'jt' : v >= 1e3 ? (v/1e3).toFixed(0)+'rb' : v;
    }

    function init() {
        if (typeof Chart === 'undefined') return;
        
        const el = document.getElementById('chartData');
        if (!el) return;

        const d = {
            labels: JSON.parse(el.dataset.labels || '[]'),
            revenue: JSON.parse(el.dataset.revenue || '[]'),
            hourly: JSON.parse(el.dataset.hourly || '[]')
        };

        Object.values(charts).forEach(c => c?.destroy?.());
        charts = {};

        const rc = document.getElementById('revenueChart');
        if (rc) {
            charts.r = new Chart(rc, {
                type: 'line',
                data: {
                    labels: d.labels,
                    datasets: [{
                        data: d.revenue,
                        borderColor: colors.emerald,
                        backgroundColor: 'rgba(16,185,129,0.1)',
                        fill: true,
                        tension: 0.3,
                        pointRadius: d.labels.length > 20 ? 0 : 3,
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    animation: { duration: 300 },
                    plugins: { legend: { display: false } },
                    scales: {
                        x: { grid: { display: false }, ticks: { maxTicksLimit: 10, font: { size: 10 } } },
                        y: { beginAtZero: true, ticks: { callback: v => fmt(v), font: { size: 10 } } }
                    }
                }
            });
        }

        const hc = document.getElementById('hourlyChart');
        if (hc) {
            const hrs = Array.from({length:24}, (_,i) => i+'h');
            charts.h = new Chart(hc, {
                type: 'bar',
                data: {
                    labels: hrs,
                    datasets: [{
                        data: d.hourly,
                        backgroundColor: 'rgba(59,130,246,0.6)',
                        borderRadius: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    animation: { duration: 300 },
                    plugins: { legend: { display: false } },
                    scales: {
                        x: { grid: { display: false }, ticks: { callback: (_,i) => i%6===0 ? i+'h' : '', font: { size: 9 } } },
                        y: { beginAtZero: true, ticks: { stepSize: 1, font: { size: 9 } } }
                    }
                }
            });
        }
    }

    init();
    Livewire.hook('morph.updated', () => setTimeout(init, 50));
})();
</script>
    <?php
        $__output = ob_get_clean();

        \Livewire\store($this)->push('scripts', $__output, $__scriptKey)
    ?>
<?php /**PATH C:\laragon\www\Kopma\resources\views/livewire/report/sales-report.blade.php ENDPATH**/ ?>