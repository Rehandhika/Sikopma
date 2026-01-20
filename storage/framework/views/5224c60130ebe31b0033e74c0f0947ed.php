<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 sm:py-6">
    
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-gray-900">Manajemen Jadwal</h1>
            <p class="text-sm text-gray-500 mt-1">Kelola jadwal shift mingguan</p>
        </div>
        <a href="<?php echo e(route('admin.schedule.create')); ?>" 
           class="inline-flex items-center justify-center gap-2 px-4 py-2.5 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Buat Jadwal
        </a>
    </div>

    
    <div class="bg-white rounded-xl border border-gray-200 p-4 sm:p-6 mb-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4">
            <div>
                <h2 class="text-base font-semibold text-gray-900">Status Ketersediaan</h2>
                <p class="text-sm text-gray-500"><?php echo e($this->currentWeekStart->format('d M')); ?> - <?php echo e($this->currentWeekEnd->format('d M Y')); ?></p>
            </div>
            <div class="flex items-center gap-4 text-sm">
                <span class="inline-flex items-center gap-1.5">
                    <span class="w-2 h-2 bg-green-500 rounded-full"></span>
                    <span class="text-gray-600"><?php echo e($this->availabilityStats['submitted']); ?> Sudah</span>
                </span>
                <span class="inline-flex items-center gap-1.5">
                    <span class="w-2 h-2 bg-gray-300 rounded-full"></span>
                    <span class="text-gray-600"><?php echo e($this->availabilityStats['pending']); ?> Belum</span>
                </span>
            </div>
        </div>

        
        <div class="mb-4">
            <div class="flex items-center justify-between text-sm mb-1.5">
                <span class="text-gray-600">Progress</span>
                <span class="font-semibold text-gray-900"><?php echo e($this->availabilityStats['percentage']); ?>%</span>
            </div>
            <div class="w-full bg-gray-100 rounded-full h-2">
                <div class="bg-green-500 h-2 rounded-full transition-all duration-500" style="width: <?php echo e($this->availabilityStats['percentage']); ?>%"></div>
            </div>
        </div>

        
        <div class="sm:hidden space-y-2 max-h-80 overflow-y-auto">
            <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $this->membersWithAvailability; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $member): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-gray-900 truncate"><?php echo e($member['name']); ?></p>
                    <p class="text-xs text-gray-500"><?php echo e($member['nim'] ?? '-'); ?></p>
                </div>
                <div class="flex items-center gap-3 ml-3">
                    <span class="text-sm text-gray-600"><?php echo e($member['total_sessions']); ?>/12</span>
                    <!--[if BLOCK]><![endif]--><?php if($member['has_submitted']): ?>
                        <button wire:click="viewMemberAvailability(<?php echo e($member['id']); ?>)" 
                                class="px-2.5 py-1 text-xs font-medium text-green-700 bg-green-100 rounded-full">
                            Lihat
                        </button>
                    <?php else: ?>
                        <span class="px-2.5 py-1 text-xs font-medium text-gray-500 bg-gray-200 rounded-full">Belum</span>
                    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                </div>
            </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
        </div>

        
        <div class="hidden sm:block max-h-72 overflow-y-auto rounded-lg border border-gray-200">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50 sticky top-0">
                    <tr>
                        <th class="px-4 py-2.5 text-left text-xs font-semibold text-gray-600 uppercase">Nama</th>
                        <th class="px-4 py-2.5 text-left text-xs font-semibold text-gray-600 uppercase">NIM</th>
                        <th class="px-4 py-2.5 text-center text-xs font-semibold text-gray-600 uppercase">Status</th>
                        <th class="px-4 py-2.5 text-center text-xs font-semibold text-gray-600 uppercase">Sesi</th>
                        <th class="px-4 py-2.5 text-center text-xs font-semibold text-gray-600 uppercase">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-100">
                    <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $this->membersWithAvailability; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $member): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-2.5 text-sm text-gray-900"><?php echo e($member['name']); ?></td>
                        <td class="px-4 py-2.5 text-sm text-gray-500"><?php echo e($member['nim'] ?? '-'); ?></td>
                        <td class="px-4 py-2.5 text-center">
                            <!--[if BLOCK]><![endif]--><?php if($member['has_submitted']): ?>
                                <span class="inline-flex px-2 py-0.5 text-xs font-medium rounded-full bg-green-100 text-green-700">Sudah</span>
                            <?php else: ?>
                                <span class="inline-flex px-2 py-0.5 text-xs font-medium rounded-full bg-gray-100 text-gray-500">Belum</span>
                            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                        </td>
                        <td class="px-4 py-2.5 text-center text-sm text-gray-600"><?php echo e($member['total_sessions']); ?>/12</td>
                        <td class="px-4 py-2.5 text-center">
                            <!--[if BLOCK]><![endif]--><?php if($member['has_submitted']): ?>
                                <button wire:click="viewMemberAvailability(<?php echo e($member['id']); ?>)" 
                                        class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                    Lihat
                                </button>
                            <?php else: ?>
                                <span class="text-gray-400 text-sm">-</span>
                            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                        </td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
                </tbody>
            </table>
        </div>
    </div>

    
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <div class="px-4 sm:px-6 py-4 border-b border-gray-200">
            <h2 class="text-base font-semibold text-gray-900">Daftar Jadwal</h2>
        </div>

        <!--[if BLOCK]><![endif]--><?php if($schedules->isEmpty()): ?>
            <div class="text-center py-12 px-4">
                <svg class="w-12 h-12 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                <p class="text-gray-500 mb-4">Belum ada jadwal</p>
                <a href="<?php echo e(route('admin.schedule.create')); ?>" class="text-blue-600 hover:text-blue-700 text-sm font-medium">
                    + Buat Jadwal Baru
                </a>
            </div>
        <?php else: ?>
            
            <div class="sm:hidden divide-y divide-gray-100">
                <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $schedules; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $schedule): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="p-4">
                    <div class="flex items-start justify-between mb-2">
                        <div>
                            <p class="text-sm font-medium text-gray-900">
                                <?php echo e(\Carbon\Carbon::parse($schedule->week_start_date)->format('d M')); ?> - 
                                <?php echo e(\Carbon\Carbon::parse($schedule->week_end_date)->format('d M Y')); ?>

                            </p>
                            <p class="text-xs text-gray-500 mt-0.5"><?php echo e($schedule->assignments_count); ?> assignments</p>
                        </div>
                        <!--[if BLOCK]><![endif]--><?php if($schedule->status === 'published'): ?>
                            <span class="px-2 py-0.5 text-xs font-medium rounded-full bg-green-100 text-green-700">Published</span>
                        <?php else: ?>
                            <span class="px-2 py-0.5 text-xs font-medium rounded-full bg-amber-100 text-amber-700">Draft</span>
                        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                    </div>
                    <div class="flex items-center gap-2 mt-3">
                        <a href="<?php echo e(route('admin.schedule.edit', $schedule)); ?>" 
                           class="flex-1 px-3 py-2 text-sm font-medium text-center text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200">
                            Lihat
                        </a>
                        <!--[if BLOCK]><![endif]--><?php if($schedule->status === 'draft'): ?>
                        <button wire:click="publish(<?php echo e($schedule->id); ?>)" 
                                class="flex-1 px-3 py-2 text-sm font-medium text-center text-white bg-blue-600 rounded-lg hover:bg-blue-700">
                            Publish
                        </button>
                        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                    </div>
                </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
            </div>

            
            <div class="hidden sm:block overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Periode</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Statistik</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Dibuat</th>
                            <th class="px-6 py-3 text-right text-xs font-semibold text-gray-600 uppercase">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $schedules; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $schedule): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4">
                                <p class="text-sm font-medium text-gray-900">
                                    <?php echo e(\Carbon\Carbon::parse($schedule->week_start_date)->format('d M')); ?> - 
                                    <?php echo e(\Carbon\Carbon::parse($schedule->week_end_date)->format('d M Y')); ?>

                                </p>
                            </td>
                            <td class="px-6 py-4">
                                <!--[if BLOCK]><![endif]--><?php if($schedule->status === 'published'): ?>
                                    <span class="inline-flex px-2.5 py-1 text-xs font-medium rounded-full bg-green-100 text-green-700">Published</span>
                                <?php else: ?>
                                    <span class="inline-flex px-2.5 py-1 text-xs font-medium rounded-full bg-amber-100 text-amber-700">Draft</span>
                                <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-sm text-gray-900"><?php echo e($schedule->assignments_count); ?> assignments</p>
                                <p class="text-xs text-gray-500"><?php echo e(number_format(($schedule->assignments_count / 12) * 100, 0)); ?>% coverage</p>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-sm text-gray-900"><?php echo e($schedule->created_at->format('d M Y')); ?></p>
                                <p class="text-xs text-gray-500"><?php echo e($schedule->created_at->diffForHumans()); ?></p>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="<?php echo e(route('admin.schedule.edit', $schedule)); ?>" 
                                       class="p-2 text-gray-500 hover:text-gray-700 hover:bg-gray-100 rounded-lg" title="Lihat">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                    </a>
                                    <!--[if BLOCK]><![endif]--><?php if($schedule->status === 'draft'): ?>
                                    <button wire:click="publish(<?php echo e($schedule->id); ?>)" 
                                            class="p-2 text-green-600 hover:text-green-700 hover:bg-green-50 rounded-lg" title="Publish">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                        </svg>
                                    </button>
                                    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                    <button wire:click="delete(<?php echo e($schedule->id); ?>)" 
                                            wire:confirm="Yakin ingin menghapus jadwal ini?"
                                            class="p-2 text-red-500 hover:text-red-700 hover:bg-red-50 rounded-lg" title="Hapus">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
                    </tbody>
                </table>
            </div>

            <!--[if BLOCK]><![endif]--><?php if($schedules->hasPages()): ?>
            <div class="px-4 sm:px-6 py-3 border-t border-gray-200">
                <?php echo e($schedules->links()); ?>

            </div>
            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
    </div>

    
    <!--[if BLOCK]><![endif]--><?php if($showMemberModal && $selectedMemberAvailability): ?>
    <div class="fixed inset-0 z-50 overflow-y-auto" x-data x-on:keydown.escape.window="$wire.closeMemberModal()">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="fixed inset-0 bg-black/50" wire:click="closeMemberModal"></div>
            
            <div class="relative bg-white rounded-xl shadow-xl w-full max-w-md mx-auto">
                <div class="flex items-center justify-between p-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900"><?php echo e($selectedMemberName); ?></h3>
                    <button wire:click="closeMemberModal" class="p-1 text-gray-400 hover:text-gray-600 rounded">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
                
                <div class="p-4">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-gray-200">
                                <th class="pb-2 text-left font-medium text-gray-700">Hari</th>
                                <th class="pb-2 text-center font-medium text-gray-700">S1</th>
                                <th class="pb-2 text-center font-medium text-gray-700">S2</th>
                                <th class="pb-2 text-center font-medium text-gray-700">S3</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $days = ['monday' => 'Senin', 'tuesday' => 'Selasa', 'wednesday' => 'Rabu', 'thursday' => 'Kamis']; ?>
                            <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $days; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $dayKey => $dayLabel): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr class="border-b border-gray-100 last:border-0">
                                <td class="py-2.5 font-medium text-gray-900"><?php echo e($dayLabel); ?></td>
                                <!--[if BLOCK]><![endif]--><?php for($s = 1; $s <= 3; $s++): ?>
                                <td class="py-2.5 text-center">
                                    <!--[if BLOCK]><![endif]--><?php if($selectedMemberAvailability[$dayKey][$s] ?? false): ?>
                                        <span class="inline-flex items-center justify-center w-7 h-7 bg-green-500 text-white rounded-lg">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                                            </svg>
                                        </span>
                                    <?php else: ?>
                                        <span class="inline-flex items-center justify-center w-7 h-7 bg-gray-100 rounded-lg">
                                            <svg class="w-3 h-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/>
                                            </svg>
                                        </span>
                                    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                </td>
                                <?php endfor; ?><!--[if ENDBLOCK]><![endif]-->
                            </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
                        </tbody>
                    </table>
                </div>
                
                <div class="p-4 border-t border-gray-200">
                    <button wire:click="closeMemberModal" class="w-full px-4 py-2.5 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
</div>
<?php /**PATH C:\laragon\www\Kopma\resources\views/livewire/schedule/index.blade.php ENDPATH**/ ?>