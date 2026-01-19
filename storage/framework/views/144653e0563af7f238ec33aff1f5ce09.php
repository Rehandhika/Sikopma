<div class="space-y-6">
    <!-- Welcome Section -->
    <div class="bg-gradient-to-r from-indigo-500 to-purple-600 overflow-hidden shadow-lg rounded-lg">
        <div class="p-6 text-white">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-16 h-16 bg-white bg-opacity-20 rounded-full flex items-center justify-center backdrop-blur-sm">
                            <!--[if BLOCK]><![endif]--><?php if(auth()->check()): ?>
                                <span class="text-white font-bold text-2xl"><?php echo e(substr(auth()->user()->name, 0, 1)); ?></span>
                            <?php else: ?>
                                <span class="text-white font-bold text-2xl">?</span>
                            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                        </div>
                    </div>
                    <div class="ml-5">
                        <!--[if BLOCK]><![endif]--><?php if(auth()->check()): ?>
                            <h2 class="text-2xl font-bold">
                                Selamat datang, <?php echo e(auth()->user()->name); ?>!
                            </h2>
                            <p class="text-indigo-100 mt-1">
                                NIM: <?php echo e(auth()->user()->nim); ?> • 
                                <!--[if BLOCK]><![endif]--><?php $__currentLoopData = auth()->user()->roles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $role): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <span class="capitalize"><?php echo e($role->name); ?></span><?php echo e(!$loop->last ? ', ' : ''); ?>

                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
                            </p>
                        <?php else: ?>
                            <h2 class="text-2xl font-bold">
                                Selamat datang di SIKOPMA
                            </h2>
                            <p class="text-indigo-100 mt-1">
                                Silakan login untuk melihat statistik pribadi.
                            </p>
                        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                    </div>
                </div>
                <div class="text-right">
                    <p class="text-sm text-indigo-100"><?php echo e(now()->isoFormat('dddd')); ?></p>
                    <p class="text-lg font-semibold"><?php echo e(now()->isoFormat('D MMMM Y')); ?></p>
                    <p class="text-2xl font-bold mt-1" id="current-time"><?php echo e(now()->format('H:i:s')); ?></p>
                    <p class="text-xs text-indigo-100">Waktu Portugal (WET/WEST)</p>
                </div>
            </div>
        </div>
    </div>

    <!-- User Stats Cards -->
    <?php if (isset($component)) { $__componentOriginalddfdd8eb2a2b0d47c29a0436357ad57f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalddfdd8eb2a2b0d47c29a0436357ad57f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.layout.grid','data' => ['cols' => '4','class' => 'gap-5']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('layout.grid'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['cols' => '4','class' => 'gap-5']); ?>
        <?php if (isset($component)) { $__componentOriginala4a09407c281b10513bf47f7415fb4c1 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginala4a09407c281b10513bf47f7415fb4c1 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.layout.stat-card','data' => ['label' => 'Kehadiran Bulan Ini','value' => ''.e($userStats['monthlyAttendance']['present']).'/'.e($userStats['monthlyAttendance']['total']).'','icon' => 'check-circle','iconColor' => 'bg-green-100','iconTextColor' => 'text-green-600']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('layout.stat-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => 'Kehadiran Bulan Ini','value' => ''.e($userStats['monthlyAttendance']['present']).'/'.e($userStats['monthlyAttendance']['total']).'','icon' => 'check-circle','iconColor' => 'bg-green-100','iconTextColor' => 'text-green-600']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginala4a09407c281b10513bf47f7415fb4c1)): ?>
<?php $attributes = $__attributesOriginala4a09407c281b10513bf47f7415fb4c1; ?>
<?php unset($__attributesOriginala4a09407c281b10513bf47f7415fb4c1); ?>
<?php endif; ?>
<?php if (isset($__componentOriginala4a09407c281b10513bf47f7415fb4c1)): ?>
<?php $component = $__componentOriginala4a09407c281b10513bf47f7415fb4c1; ?>
<?php unset($__componentOriginala4a09407c281b10513bf47f7415fb4c1); ?>
<?php endif; ?>

        <?php if (isset($component)) { $__componentOriginala4a09407c281b10513bf47f7415fb4c1 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginala4a09407c281b10513bf47f7415fb4c1 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.layout.stat-card','data' => ['label' => 'Terlambat','value' => ''.e($userStats['monthlyAttendance']['late']).'','icon' => 'clock','iconColor' => 'bg-yellow-100','iconTextColor' => 'text-yellow-600']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('layout.stat-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => 'Terlambat','value' => ''.e($userStats['monthlyAttendance']['late']).'','icon' => 'clock','iconColor' => 'bg-yellow-100','iconTextColor' => 'text-yellow-600']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginala4a09407c281b10513bf47f7415fb4c1)): ?>
<?php $attributes = $__attributesOriginala4a09407c281b10513bf47f7415fb4c1; ?>
<?php unset($__attributesOriginala4a09407c281b10513bf47f7415fb4c1); ?>
<?php endif; ?>
<?php if (isset($__componentOriginala4a09407c281b10513bf47f7415fb4c1)): ?>
<?php $component = $__componentOriginala4a09407c281b10513bf47f7415fb4c1; ?>
<?php unset($__componentOriginala4a09407c281b10513bf47f7415fb4c1); ?>
<?php endif; ?>

        <?php if (isset($component)) { $__componentOriginala4a09407c281b10513bf47f7415fb4c1 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginala4a09407c281b10513bf47f7415fb4c1 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.layout.stat-card','data' => ['label' => 'Penalti Aktif','value' => ''.e($userStats['penalties']['count']).'','subtitle' => ''.e($userStats['penalties']['points']).' poin','icon' => 'exclamation-triangle','iconColor' => 'bg-red-100','iconTextColor' => 'text-red-600']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('layout.stat-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => 'Penalti Aktif','value' => ''.e($userStats['penalties']['count']).'','subtitle' => ''.e($userStats['penalties']['points']).' poin','icon' => 'exclamation-triangle','iconColor' => 'bg-red-100','iconTextColor' => 'text-red-600']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginala4a09407c281b10513bf47f7415fb4c1)): ?>
<?php $attributes = $__attributesOriginala4a09407c281b10513bf47f7415fb4c1; ?>
<?php unset($__attributesOriginala4a09407c281b10513bf47f7415fb4c1); ?>
<?php endif; ?>
<?php if (isset($__componentOriginala4a09407c281b10513bf47f7415fb4c1)): ?>
<?php $component = $__componentOriginala4a09407c281b10513bf47f7415fb4c1; ?>
<?php unset($__componentOriginala4a09407c281b10513bf47f7415fb4c1); ?>
<?php endif; ?>

        <?php if (isset($component)) { $__componentOriginala4a09407c281b10513bf47f7415fb4c1 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginala4a09407c281b10513bf47f7415fb4c1 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.layout.stat-card','data' => ['label' => 'Notifikasi','value' => ''.e($userStats['notifications']->count()).'','icon' => 'bell','iconColor' => 'bg-blue-100','iconTextColor' => 'text-blue-600']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('layout.stat-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => 'Notifikasi','value' => ''.e($userStats['notifications']->count()).'','icon' => 'bell','iconColor' => 'bg-blue-100','iconTextColor' => 'text-blue-600']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginala4a09407c281b10513bf47f7415fb4c1)): ?>
<?php $attributes = $__attributesOriginala4a09407c281b10513bf47f7415fb4c1; ?>
<?php unset($__attributesOriginala4a09407c281b10513bf47f7415fb4c1); ?>
<?php endif; ?>
<?php if (isset($__componentOriginala4a09407c281b10513bf47f7415fb4c1)): ?>
<?php $component = $__componentOriginala4a09407c281b10513bf47f7415fb4c1; ?>
<?php unset($__componentOriginala4a09407c281b10513bf47f7415fb4c1); ?>
<?php endif; ?>
     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalddfdd8eb2a2b0d47c29a0436357ad57f)): ?>
<?php $attributes = $__attributesOriginalddfdd8eb2a2b0d47c29a0436357ad57f; ?>
<?php unset($__attributesOriginalddfdd8eb2a2b0d47c29a0436357ad57f); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalddfdd8eb2a2b0d47c29a0436357ad57f)): ?>
<?php $component = $__componentOriginalddfdd8eb2a2b0d47c29a0436357ad57f; ?>
<?php unset($__componentOriginalddfdd8eb2a2b0d47c29a0436357ad57f); ?>
<?php endif; ?>

    <!--[if BLOCK]><![endif]--><?php if($isAdmin): ?>
    <!-- Admin Stats -->
    <?php if (isset($component)) { $__componentOriginaldae4cd48acb67888a4631e1ba48f2f93 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginaldae4cd48acb67888a4631e1ba48f2f93 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.card','data' => ['title' => 'Statistik Hari Ini (Admin)']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Statistik Hari Ini (Admin)']); ?>
        <?php if (isset($component)) { $__componentOriginalddfdd8eb2a2b0d47c29a0436357ad57f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalddfdd8eb2a2b0d47c29a0436357ad57f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.layout.grid','data' => ['cols' => '4','class' => 'gap-4']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('layout.grid'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['cols' => '4','class' => 'gap-4']); ?>
            <div class="border-l-4 border-green-500 pl-4">
                <p class="text-sm text-gray-600">Kehadiran</p>
                <p class="text-2xl font-bold text-gray-900"><?php echo e($adminStats['todayAttendance']['present']); ?>/<?php echo e($adminStats['todayAttendance']['total']); ?></p>
            </div>
            <div class="border-l-4 border-blue-500 pl-4">
                <p class="text-sm text-gray-600">Penjualan</p>
                <p class="text-2xl font-bold text-gray-900"><?php echo e(format_currency($adminStats['todaySales'])); ?></p>
                <p class="text-xs text-gray-500"><?php echo e($adminStats['todayTransactions']); ?> transaksi</p>
            </div>
            <div class="border-l-4 border-yellow-500 pl-4">
                <p class="text-sm text-gray-600">Stok Rendah</p>
                <p class="text-2xl font-bold text-gray-900"><?php echo e($adminStats['lowStockProducts']); ?></p>
            </div>
            <div class="border-l-4 border-red-500 pl-4">
                <p class="text-sm text-gray-600">Persetujuan</p>
                <p class="text-2xl font-bold text-gray-900"><?php echo e($adminStats['pendingLeaves'] + $adminStats['pendingSwaps']); ?></p>
                <p class="text-xs text-gray-500"><?php echo e($adminStats['pendingLeaves']); ?> cuti, <?php echo e($adminStats['pendingSwaps']); ?> swap</p>
            </div>
         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalddfdd8eb2a2b0d47c29a0436357ad57f)): ?>
<?php $attributes = $__attributesOriginalddfdd8eb2a2b0d47c29a0436357ad57f; ?>
<?php unset($__attributesOriginalddfdd8eb2a2b0d47c29a0436357ad57f); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalddfdd8eb2a2b0d47c29a0436357ad57f)): ?>
<?php $component = $__componentOriginalddfdd8eb2a2b0d47c29a0436357ad57f; ?>
<?php unset($__componentOriginalddfdd8eb2a2b0d47c29a0436357ad57f); ?>
<?php endif; ?>
     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginaldae4cd48acb67888a4631e1ba48f2f93)): ?>
<?php $attributes = $__attributesOriginaldae4cd48acb67888a4631e1ba48f2f93; ?>
<?php unset($__attributesOriginaldae4cd48acb67888a4631e1ba48f2f93); ?>
<?php endif; ?>
<?php if (isset($__componentOriginaldae4cd48acb67888a4631e1ba48f2f93)): ?>
<?php $component = $__componentOriginaldae4cd48acb67888a4631e1ba48f2f93; ?>
<?php unset($__componentOriginaldae4cd48acb67888a4631e1ba48f2f93); ?>
<?php endif; ?>
    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->

    <!-- Main Content Grid -->
    <?php if (isset($component)) { $__componentOriginalddfdd8eb2a2b0d47c29a0436357ad57f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalddfdd8eb2a2b0d47c29a0436357ad57f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.layout.grid','data' => ['cols' => '2','class' => 'gap-6']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('layout.grid'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['cols' => '2','class' => 'gap-6']); ?>
        <!-- Today's Schedule Detail -->
        <?php if (isset($component)) { $__componentOriginaldae4cd48acb67888a4631e1ba48f2f93 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginaldae4cd48acb67888a4631e1ba48f2f93 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.card','data' => ['title' => 'Jadwal Hari Ini']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Jadwal Hari Ini']); ?>
            <!--[if BLOCK]><![endif]--><?php if($userStats['todaySchedule']): ?>
                <div class="bg-blue-50 border border-blue-200 rounded-md p-4">
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <?php if (isset($component)) { $__componentOriginal56804098dcf376a0e2227cb77b6cd00a = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal56804098dcf376a0e2227cb77b6cd00a = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.icon','data' => ['name' => 'clock','class' => 'h-5 w-5 text-blue-600']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'clock','class' => 'h-5 w-5 text-blue-600']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal56804098dcf376a0e2227cb77b6cd00a)): ?>
<?php $attributes = $__attributesOriginal56804098dcf376a0e2227cb77b6cd00a; ?>
<?php unset($__attributesOriginal56804098dcf376a0e2227cb77b6cd00a); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal56804098dcf376a0e2227cb77b6cd00a)): ?>
<?php $component = $__componentOriginal56804098dcf376a0e2227cb77b6cd00a; ?>
<?php unset($__componentOriginal56804098dcf376a0e2227cb77b6cd00a); ?>
<?php endif; ?>
                        </div>
                        <div class="ml-3 flex-1">
                            <h4 class="text-sm font-medium text-blue-900">Sesi <?php echo e($userStats['todaySchedule']->session); ?></h4>
                            <p class="text-sm text-blue-700 mt-1"><?php echo e($userStats['todaySchedule']->date->format('d M Y')); ?></p>
                            <a href="<?php echo e(route('admin.attendance.check-in-out')); ?>" class="inline-flex items-center mt-3 text-sm font-medium text-blue-600 hover:text-blue-800">
                                Check-in Sekarang
                                <?php if (isset($component)) { $__componentOriginal56804098dcf376a0e2227cb77b6cd00a = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal56804098dcf376a0e2227cb77b6cd00a = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.icon','data' => ['name' => 'chevron-right','class' => 'ml-1 w-4 h-4']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'chevron-right','class' => 'ml-1 w-4 h-4']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal56804098dcf376a0e2227cb77b6cd00a)): ?>
<?php $attributes = $__attributesOriginal56804098dcf376a0e2227cb77b6cd00a; ?>
<?php unset($__attributesOriginal56804098dcf376a0e2227cb77b6cd00a); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal56804098dcf376a0e2227cb77b6cd00a)): ?>
<?php $component = $__componentOriginal56804098dcf376a0e2227cb77b6cd00a; ?>
<?php unset($__componentOriginal56804098dcf376a0e2227cb77b6cd00a); ?>
<?php endif; ?>
                            </a>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <?php if (isset($component)) { $__componentOriginalfe16eb12133e72aabae529d081318460 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalfe16eb12133e72aabae529d081318460 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.layout.empty-state','data' => ['icon' => 'calendar','title' => 'Tidak ada jadwal hari ini']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('layout.empty-state'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => 'calendar','title' => 'Tidak ada jadwal hari ini']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalfe16eb12133e72aabae529d081318460)): ?>
<?php $attributes = $__attributesOriginalfe16eb12133e72aabae529d081318460; ?>
<?php unset($__attributesOriginalfe16eb12133e72aabae529d081318460); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalfe16eb12133e72aabae529d081318460)): ?>
<?php $component = $__componentOriginalfe16eb12133e72aabae529d081318460; ?>
<?php unset($__componentOriginalfe16eb12133e72aabae529d081318460); ?>
<?php endif; ?>
            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginaldae4cd48acb67888a4631e1ba48f2f93)): ?>
<?php $attributes = $__attributesOriginaldae4cd48acb67888a4631e1ba48f2f93; ?>
<?php unset($__attributesOriginaldae4cd48acb67888a4631e1ba48f2f93); ?>
<?php endif; ?>
<?php if (isset($__componentOriginaldae4cd48acb67888a4631e1ba48f2f93)): ?>
<?php $component = $__componentOriginaldae4cd48acb67888a4631e1ba48f2f93; ?>
<?php unset($__componentOriginaldae4cd48acb67888a4631e1ba48f2f93); ?>
<?php endif; ?>

        <!-- Recent Notifications -->
        <?php if (isset($component)) { $__componentOriginaldae4cd48acb67888a4631e1ba48f2f93 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginaldae4cd48acb67888a4631e1ba48f2f93 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.card','data' => ['title' => 'Notifikasi Terbaru']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Notifikasi Terbaru']); ?>
            <!--[if BLOCK]><![endif]--><?php if($userStats['notifications']->count() > 0): ?>
                <div class="space-y-3">
                    <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $userStats['notifications']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $notification): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="flex items-start p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition">
                            <div class="flex-shrink-0">
                                <div class="w-2 h-2 bg-blue-500 rounded-full mt-2"></div>
                            </div>
                            <div class="ml-3 flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-900"><?php echo e($notification->title); ?></p>
                                <p class="text-sm text-gray-600 mt-1"><?php echo e(Str::limit($notification->message, 80)); ?></p>
                                <p class="text-xs text-gray-400 mt-1"><?php echo e($notification->created_at->diffForHumans()); ?></p>
                            </div>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
                    <a href="<?php echo e(route('admin.notifications.index')); ?>" class="block text-center text-sm text-indigo-600 hover:text-indigo-800 font-medium mt-4">
                        Lihat Semua Notifikasi →
                    </a>
                </div>
            <?php else: ?>
                <?php if (isset($component)) { $__componentOriginalfe16eb12133e72aabae529d081318460 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalfe16eb12133e72aabae529d081318460 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.layout.empty-state','data' => ['icon' => 'bell','title' => 'Tidak ada notifikasi baru']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('layout.empty-state'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => 'bell','title' => 'Tidak ada notifikasi baru']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalfe16eb12133e72aabae529d081318460)): ?>
<?php $attributes = $__attributesOriginalfe16eb12133e72aabae529d081318460; ?>
<?php unset($__attributesOriginalfe16eb12133e72aabae529d081318460); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalfe16eb12133e72aabae529d081318460)): ?>
<?php $component = $__componentOriginalfe16eb12133e72aabae529d081318460; ?>
<?php unset($__componentOriginalfe16eb12133e72aabae529d081318460); ?>
<?php endif; ?>
            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginaldae4cd48acb67888a4631e1ba48f2f93)): ?>
<?php $attributes = $__attributesOriginaldae4cd48acb67888a4631e1ba48f2f93; ?>
<?php unset($__attributesOriginaldae4cd48acb67888a4631e1ba48f2f93); ?>
<?php endif; ?>
<?php if (isset($__componentOriginaldae4cd48acb67888a4631e1ba48f2f93)): ?>
<?php $component = $__componentOriginaldae4cd48acb67888a4631e1ba48f2f93; ?>
<?php unset($__componentOriginaldae4cd48acb67888a4631e1ba48f2f93); ?>
<?php endif; ?>
     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalddfdd8eb2a2b0d47c29a0436357ad57f)): ?>
<?php $attributes = $__attributesOriginalddfdd8eb2a2b0d47c29a0436357ad57f; ?>
<?php unset($__attributesOriginalddfdd8eb2a2b0d47c29a0436357ad57f); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalddfdd8eb2a2b0d47c29a0436357ad57f)): ?>
<?php $component = $__componentOriginalddfdd8eb2a2b0d47c29a0436357ad57f; ?>
<?php unset($__componentOriginalddfdd8eb2a2b0d47c29a0436357ad57f); ?>
<?php endif; ?>

    <!-- Upcoming Schedules -->
    <!--[if BLOCK]><![endif]--><?php if($userStats['upcomingSchedules']->count() > 0): ?>
        <?php if (isset($component)) { $__componentOriginaldae4cd48acb67888a4631e1ba48f2f93 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginaldae4cd48acb67888a4631e1ba48f2f93 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.card','data' => ['title' => 'Jadwal Mendatang (7 Hari)']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Jadwal Mendatang (7 Hari)']); ?>
            <div class="space-y-2">
                <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $userStats['upcomingSchedules']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $schedule): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition">
                        <div class="flex items-center space-x-3">
                            <div class="flex-shrink-0 w-12 text-center">
                                <p class="text-xs text-gray-500"><?php echo e($schedule->date->format('D')); ?></p>
                                <p class="text-lg font-semibold text-gray-900"><?php echo e($schedule->date->format('d')); ?></p>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-900">Sesi <?php echo e($schedule->session); ?></p>
                                <p class="text-xs text-gray-500"><?php echo e($schedule->date->format('F Y')); ?></p>
                            </div>
                        </div>
                        <?php if (isset($component)) { $__componentOriginalab7baa01105b3dfe1e0cf1dfc58879b4 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalab7baa01105b3dfe1e0cf1dfc58879b4 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.badge','data' => ['variant' => 'secondary']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => 'secondary']); ?><?php echo e($schedule->status); ?> <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalab7baa01105b3dfe1e0cf1dfc58879b4)): ?>
<?php $attributes = $__attributesOriginalab7baa01105b3dfe1e0cf1dfc58879b4; ?>
<?php unset($__attributesOriginalab7baa01105b3dfe1e0cf1dfc58879b4); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalab7baa01105b3dfe1e0cf1dfc58879b4)): ?>
<?php $component = $__componentOriginalab7baa01105b3dfe1e0cf1dfc58879b4; ?>
<?php unset($__componentOriginalab7baa01105b3dfe1e0cf1dfc58879b4); ?>
<?php endif; ?>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
            </div>
         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginaldae4cd48acb67888a4631e1ba48f2f93)): ?>
<?php $attributes = $__attributesOriginaldae4cd48acb67888a4631e1ba48f2f93; ?>
<?php unset($__attributesOriginaldae4cd48acb67888a4631e1ba48f2f93); ?>
<?php endif; ?>
<?php if (isset($__componentOriginaldae4cd48acb67888a4631e1ba48f2f93)): ?>
<?php $component = $__componentOriginaldae4cd48acb67888a4631e1ba48f2f93; ?>
<?php unset($__componentOriginaldae4cd48acb67888a4631e1ba48f2f93); ?>
<?php endif; ?>
    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
</div>

<?php $__env->startPush('scripts'); ?>
<script>
    // Update clock every second with Portugal timezone
    function updateClock() {
        const now = new Date();
        
        // Convert to Portugal timezone (Europe/Lisbon)
        const portugalTime = new Date(now.toLocaleString('en-US', { timeZone: 'Europe/Lisbon' }));
        
        const hours = String(portugalTime.getHours()).padStart(2, '0');
        const minutes = String(portugalTime.getMinutes()).padStart(2, '0');
        const seconds = String(portugalTime.getSeconds()).padStart(2, '0');
        
        const timeElement = document.getElementById('current-time');
        if (timeElement) {
            timeElement.textContent = `${hours}:${minutes}:${seconds}`;
        }
    }
    
    // Update immediately and then every second
    updateClock();
    setInterval(updateClock, 1000);
</script>
<?php $__env->stopPush(); ?>
<?php /**PATH C:\laragon\www\Kopma\resources\views/livewire/dashboard/index.blade.php ENDPATH**/ ?>