<div class="p-6">
    <?php if (isset($component)) { $__componentOriginal4743781065990dfe96029737c4f06097 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal4743781065990dfe96029737c4f06097 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.layout.page-header','data' => ['title' => 'Laporan Kehadiran','description' => 'Analisis dan statistik kehadiran karyawan']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('layout.page-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Laporan Kehadiran','description' => 'Analisis dan statistik kehadiran karyawan']); ?>
         <?php $__env->slot('actions', null, []); ?> 
            <?php if (isset($component)) { $__componentOriginala8bb031a483a05f647cb99ed3a469847 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginala8bb031a483a05f647cb99ed3a469847 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.button','data' => ['variant' => 'white','icon' => 'download']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => 'white','icon' => 'download']); ?>
                Export Excel
             <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginala8bb031a483a05f647cb99ed3a469847)): ?>
<?php $attributes = $__attributesOriginala8bb031a483a05f647cb99ed3a469847; ?>
<?php unset($__attributesOriginala8bb031a483a05f647cb99ed3a469847); ?>
<?php endif; ?>
<?php if (isset($__componentOriginala8bb031a483a05f647cb99ed3a469847)): ?>
<?php $component = $__componentOriginala8bb031a483a05f647cb99ed3a469847; ?>
<?php unset($__componentOriginala8bb031a483a05f647cb99ed3a469847); ?>
<?php endif; ?>
            <?php if (isset($component)) { $__componentOriginala8bb031a483a05f647cb99ed3a469847 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginala8bb031a483a05f647cb99ed3a469847 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.button','data' => ['variant' => 'primary','icon' => 'printer']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => 'primary','icon' => 'printer']); ?>
                Cetak
             <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginala8bb031a483a05f647cb99ed3a469847)): ?>
<?php $attributes = $__attributesOriginala8bb031a483a05f647cb99ed3a469847; ?>
<?php unset($__attributesOriginala8bb031a483a05f647cb99ed3a469847); ?>
<?php endif; ?>
<?php if (isset($__componentOriginala8bb031a483a05f647cb99ed3a469847)): ?>
<?php $component = $__componentOriginala8bb031a483a05f647cb99ed3a469847; ?>
<?php unset($__componentOriginala8bb031a483a05f647cb99ed3a469847); ?>
<?php endif; ?>
         <?php $__env->endSlot(); ?>
     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal4743781065990dfe96029737c4f06097)): ?>
<?php $attributes = $__attributesOriginal4743781065990dfe96029737c4f06097; ?>
<?php unset($__attributesOriginal4743781065990dfe96029737c4f06097); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal4743781065990dfe96029737c4f06097)): ?>
<?php $component = $__componentOriginal4743781065990dfe96029737c4f06097; ?>
<?php unset($__componentOriginal4743781065990dfe96029737c4f06097); ?>
<?php endif; ?>

    
    <?php if (isset($component)) { $__componentOriginalddfdd8eb2a2b0d47c29a0436357ad57f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalddfdd8eb2a2b0d47c29a0436357ad57f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.layout.grid','data' => ['cols' => '4','class' => 'mb-6']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('layout.grid'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['cols' => '4','class' => 'mb-6']); ?>
        <?php if (isset($component)) { $__componentOriginala4a09407c281b10513bf47f7415fb4c1 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginala4a09407c281b10513bf47f7415fb4c1 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.layout.stat-card','data' => ['label' => 'Total Kehadiran','value' => $stats['total'],'icon' => 'clipboard-list','iconColor' => 'bg-primary-100','iconTextColor' => 'text-primary-600']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('layout.stat-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => 'Total Kehadiran','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($stats['total']),'icon' => 'clipboard-list','iconColor' => 'bg-primary-100','iconTextColor' => 'text-primary-600']); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.layout.stat-card','data' => ['label' => 'Hadir','value' => $stats['present'],'icon' => 'check-circle','iconColor' => 'bg-success-100','iconTextColor' => 'text-success-600']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('layout.stat-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => 'Hadir','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($stats['present']),'icon' => 'check-circle','iconColor' => 'bg-success-100','iconTextColor' => 'text-success-600']); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.layout.stat-card','data' => ['label' => 'Terlambat','value' => $stats['late'],'icon' => 'clock','iconColor' => 'bg-warning-100','iconTextColor' => 'text-warning-600']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('layout.stat-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => 'Terlambat','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($stats['late']),'icon' => 'clock','iconColor' => 'bg-warning-100','iconTextColor' => 'text-warning-600']); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.layout.stat-card','data' => ['label' => 'Tidak Hadir','value' => $stats['absent'],'icon' => 'x-circle','iconColor' => 'bg-danger-100','iconTextColor' => 'text-danger-600']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('layout.stat-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => 'Tidak Hadir','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($stats['absent']),'icon' => 'x-circle','iconColor' => 'bg-danger-100','iconTextColor' => 'text-danger-600']); ?>
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

    
    <?php if (isset($component)) { $__componentOriginaldae4cd48acb67888a4631e1ba48f2f93 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginaldae4cd48acb67888a4631e1ba48f2f93 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.card','data' => ['class' => 'mb-6']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'mb-6']); ?>
        <?php if (isset($component)) { $__componentOriginalddfdd8eb2a2b0d47c29a0436357ad57f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalddfdd8eb2a2b0d47c29a0436357ad57f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.layout.grid','data' => ['cols' => '4']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('layout.grid'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['cols' => '4']); ?>
            <?php if (isset($component)) { $__componentOriginal65bd7e7dbd93cec773ad6501ce127e46 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal65bd7e7dbd93cec773ad6501ce127e46 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.input','data' => ['label' => 'Dari','name' => 'dateFrom','type' => 'date','wire:model.live' => 'dateFrom']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.input'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => 'Dari','name' => 'dateFrom','type' => 'date','wire:model.live' => 'dateFrom']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal65bd7e7dbd93cec773ad6501ce127e46)): ?>
<?php $attributes = $__attributesOriginal65bd7e7dbd93cec773ad6501ce127e46; ?>
<?php unset($__attributesOriginal65bd7e7dbd93cec773ad6501ce127e46); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal65bd7e7dbd93cec773ad6501ce127e46)): ?>
<?php $component = $__componentOriginal65bd7e7dbd93cec773ad6501ce127e46; ?>
<?php unset($__componentOriginal65bd7e7dbd93cec773ad6501ce127e46); ?>
<?php endif; ?>
            <?php if (isset($component)) { $__componentOriginal65bd7e7dbd93cec773ad6501ce127e46 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal65bd7e7dbd93cec773ad6501ce127e46 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.input','data' => ['label' => 'Sampai','name' => 'dateTo','type' => 'date','wire:model.live' => 'dateTo']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.input'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => 'Sampai','name' => 'dateTo','type' => 'date','wire:model.live' => 'dateTo']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal65bd7e7dbd93cec773ad6501ce127e46)): ?>
<?php $attributes = $__attributesOriginal65bd7e7dbd93cec773ad6501ce127e46; ?>
<?php unset($__attributesOriginal65bd7e7dbd93cec773ad6501ce127e46); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal65bd7e7dbd93cec773ad6501ce127e46)): ?>
<?php $component = $__componentOriginal65bd7e7dbd93cec773ad6501ce127e46; ?>
<?php unset($__componentOriginal65bd7e7dbd93cec773ad6501ce127e46); ?>
<?php endif; ?>
            <?php if (isset($component)) { $__componentOriginal231e2c645bf8af0c5c05a5dc5a94c862 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal231e2c645bf8af0c5c05a5dc5a94c862 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.select','data' => ['label' => 'User','name' => 'userFilter','wire:model.live' => 'userFilter']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.select'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => 'User','name' => 'userFilter','wire:model.live' => 'userFilter']); ?>
                <option value="all">Semua</option>
                <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($user->id); ?>"><?php echo e($user->name); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
             <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal231e2c645bf8af0c5c05a5dc5a94c862)): ?>
<?php $attributes = $__attributesOriginal231e2c645bf8af0c5c05a5dc5a94c862; ?>
<?php unset($__attributesOriginal231e2c645bf8af0c5c05a5dc5a94c862); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal231e2c645bf8af0c5c05a5dc5a94c862)): ?>
<?php $component = $__componentOriginal231e2c645bf8af0c5c05a5dc5a94c862; ?>
<?php unset($__componentOriginal231e2c645bf8af0c5c05a5dc5a94c862); ?>
<?php endif; ?>
            <?php if (isset($component)) { $__componentOriginal231e2c645bf8af0c5c05a5dc5a94c862 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal231e2c645bf8af0c5c05a5dc5a94c862 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.select','data' => ['label' => 'Status','name' => 'statusFilter','wire:model.live' => 'statusFilter']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.select'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => 'Status','name' => 'statusFilter','wire:model.live' => 'statusFilter']); ?>
                <option value="all">Semua</option>
                <option value="present">Hadir</option>
                <option value="late">Terlambat</option>
                <option value="absent">Tidak Hadir</option>
             <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal231e2c645bf8af0c5c05a5dc5a94c862)): ?>
<?php $attributes = $__attributesOriginal231e2c645bf8af0c5c05a5dc5a94c862; ?>
<?php unset($__attributesOriginal231e2c645bf8af0c5c05a5dc5a94c862); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal231e2c645bf8af0c5c05a5dc5a94c862)): ?>
<?php $component = $__componentOriginal231e2c645bf8af0c5c05a5dc5a94c862; ?>
<?php unset($__componentOriginal231e2c645bf8af0c5c05a5dc5a94c862); ?>
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

    
    <?php if (isset($component)) { $__componentOriginaldae4cd48acb67888a4631e1ba48f2f93 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginaldae4cd48acb67888a4631e1ba48f2f93 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.card','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
        <?php if (isset($component)) { $__componentOriginal8ed4d2c4c8d055b5cd9b81025b3a3f27 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal8ed4d2c4c8d055b5cd9b81025b3a3f27 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.data.table','data' => ['headers' => ['Tanggal', 'Nama', 'Check In', 'Check Out', 'Jam Kerja', 'Status']]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('data.table'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['headers' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(['Tanggal', 'Nama', 'Check In', 'Check Out', 'Jam Kerja', 'Status'])]); ?>
            <!--[if BLOCK]><![endif]--><?php $__empty_1 = true; $__currentLoopData = $attendances; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $attendance): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <?php if (isset($component)) { $__componentOriginalb4e1d3352348902d30955c9827e95353 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalb4e1d3352348902d30955c9827e95353 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.data.table-row','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('data.table-row'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
                    <?php if (isset($component)) { $__componentOriginal026d0ae70983922f13829f6932913f9f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal026d0ae70983922f13829f6932913f9f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.data.table-cell','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('data.table-cell'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?><?php echo e($attendance->date->format('d M Y')); ?> <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal026d0ae70983922f13829f6932913f9f)): ?>
<?php $attributes = $__attributesOriginal026d0ae70983922f13829f6932913f9f; ?>
<?php unset($__attributesOriginal026d0ae70983922f13829f6932913f9f); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal026d0ae70983922f13829f6932913f9f)): ?>
<?php $component = $__componentOriginal026d0ae70983922f13829f6932913f9f; ?>
<?php unset($__componentOriginal026d0ae70983922f13829f6932913f9f); ?>
<?php endif; ?>
                    <?php if (isset($component)) { $__componentOriginal026d0ae70983922f13829f6932913f9f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal026d0ae70983922f13829f6932913f9f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.data.table-cell','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('data.table-cell'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?><?php echo e($attendance->user->name); ?> <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal026d0ae70983922f13829f6932913f9f)): ?>
<?php $attributes = $__attributesOriginal026d0ae70983922f13829f6932913f9f; ?>
<?php unset($__attributesOriginal026d0ae70983922f13829f6932913f9f); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal026d0ae70983922f13829f6932913f9f)): ?>
<?php $component = $__componentOriginal026d0ae70983922f13829f6932913f9f; ?>
<?php unset($__componentOriginal026d0ae70983922f13829f6932913f9f); ?>
<?php endif; ?>
                    <?php if (isset($component)) { $__componentOriginal026d0ae70983922f13829f6932913f9f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal026d0ae70983922f13829f6932913f9f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.data.table-cell','data' => ['class' => 'text-center']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('data.table-cell'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'text-center']); ?><?php echo e($attendance->check_in ?? '-'); ?> <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal026d0ae70983922f13829f6932913f9f)): ?>
<?php $attributes = $__attributesOriginal026d0ae70983922f13829f6932913f9f; ?>
<?php unset($__attributesOriginal026d0ae70983922f13829f6932913f9f); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal026d0ae70983922f13829f6932913f9f)): ?>
<?php $component = $__componentOriginal026d0ae70983922f13829f6932913f9f; ?>
<?php unset($__componentOriginal026d0ae70983922f13829f6932913f9f); ?>
<?php endif; ?>
                    <?php if (isset($component)) { $__componentOriginal026d0ae70983922f13829f6932913f9f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal026d0ae70983922f13829f6932913f9f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.data.table-cell','data' => ['class' => 'text-center']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('data.table-cell'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'text-center']); ?><?php echo e($attendance->check_out ?? '-'); ?> <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal026d0ae70983922f13829f6932913f9f)): ?>
<?php $attributes = $__attributesOriginal026d0ae70983922f13829f6932913f9f; ?>
<?php unset($__attributesOriginal026d0ae70983922f13829f6932913f9f); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal026d0ae70983922f13829f6932913f9f)): ?>
<?php $component = $__componentOriginal026d0ae70983922f13829f6932913f9f; ?>
<?php unset($__componentOriginal026d0ae70983922f13829f6932913f9f); ?>
<?php endif; ?>
                    <?php if (isset($component)) { $__componentOriginal026d0ae70983922f13829f6932913f9f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal026d0ae70983922f13829f6932913f9f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.data.table-cell','data' => ['class' => 'text-center']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('data.table-cell'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'text-center']); ?><?php echo e($attendance->work_hours ?? '-'); ?> <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal026d0ae70983922f13829f6932913f9f)): ?>
<?php $attributes = $__attributesOriginal026d0ae70983922f13829f6932913f9f; ?>
<?php unset($__attributesOriginal026d0ae70983922f13829f6932913f9f); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal026d0ae70983922f13829f6932913f9f)): ?>
<?php $component = $__componentOriginal026d0ae70983922f13829f6932913f9f; ?>
<?php unset($__componentOriginal026d0ae70983922f13829f6932913f9f); ?>
<?php endif; ?>
                    <?php if (isset($component)) { $__componentOriginal026d0ae70983922f13829f6932913f9f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal026d0ae70983922f13829f6932913f9f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.data.table-cell','data' => ['class' => 'text-center']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('data.table-cell'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'text-center']); ?>
                        <?php if (isset($component)) { $__componentOriginalab7baa01105b3dfe1e0cf1dfc58879b4 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalab7baa01105b3dfe1e0cf1dfc58879b4 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.badge','data' => ['variant' => match($attendance->status) {
                                'present' => 'success',
                                'late' => 'warning',
                                'absent' => 'danger',
                                'excused' => 'info',
                                default => 'gray'
                            }]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(match($attendance->status) {
                                'present' => 'success',
                                'late' => 'warning',
                                'absent' => 'danger',
                                'excused' => 'info',
                                default => 'gray'
                            })]); ?>
                            <?php echo e(match($attendance->status) {
                                'present' => 'Hadir',
                                'late' => 'Terlambat',
                                'absent' => 'Tidak Hadir',
                                'excused' => 'Izin',
                                default => $attendance->status
                            }); ?>

                         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalab7baa01105b3dfe1e0cf1dfc58879b4)): ?>
<?php $attributes = $__attributesOriginalab7baa01105b3dfe1e0cf1dfc58879b4; ?>
<?php unset($__attributesOriginalab7baa01105b3dfe1e0cf1dfc58879b4); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalab7baa01105b3dfe1e0cf1dfc58879b4)): ?>
<?php $component = $__componentOriginalab7baa01105b3dfe1e0cf1dfc58879b4; ?>
<?php unset($__componentOriginalab7baa01105b3dfe1e0cf1dfc58879b4); ?>
<?php endif; ?>
                     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal026d0ae70983922f13829f6932913f9f)): ?>
<?php $attributes = $__attributesOriginal026d0ae70983922f13829f6932913f9f; ?>
<?php unset($__attributesOriginal026d0ae70983922f13829f6932913f9f); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal026d0ae70983922f13829f6932913f9f)): ?>
<?php $component = $__componentOriginal026d0ae70983922f13829f6932913f9f; ?>
<?php unset($__componentOriginal026d0ae70983922f13829f6932913f9f); ?>
<?php endif; ?>
                 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalb4e1d3352348902d30955c9827e95353)): ?>
<?php $attributes = $__attributesOriginalb4e1d3352348902d30955c9827e95353; ?>
<?php unset($__attributesOriginalb4e1d3352348902d30955c9827e95353); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalb4e1d3352348902d30955c9827e95353)): ?>
<?php $component = $__componentOriginalb4e1d3352348902d30955c9827e95353; ?>
<?php unset($__componentOriginalb4e1d3352348902d30955c9827e95353); ?>
<?php endif; ?>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <?php if (isset($component)) { $__componentOriginalb4e1d3352348902d30955c9827e95353 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalb4e1d3352348902d30955c9827e95353 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.data.table-row','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('data.table-row'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
                    <?php if (isset($component)) { $__componentOriginal026d0ae70983922f13829f6932913f9f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal026d0ae70983922f13829f6932913f9f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.data.table-cell','data' => ['colspan' => '6']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('data.table-cell'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['colspan' => '6']); ?>
                        <?php if (isset($component)) { $__componentOriginalfe16eb12133e72aabae529d081318460 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalfe16eb12133e72aabae529d081318460 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.layout.empty-state','data' => ['icon' => 'clipboard-list','title' => 'Tidak ada data kehadiran','description' => 'Ubah filter atau periode waktu untuk melihat data']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('layout.empty-state'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => 'clipboard-list','title' => 'Tidak ada data kehadiran','description' => 'Ubah filter atau periode waktu untuk melihat data']); ?>
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
                     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal026d0ae70983922f13829f6932913f9f)): ?>
<?php $attributes = $__attributesOriginal026d0ae70983922f13829f6932913f9f; ?>
<?php unset($__attributesOriginal026d0ae70983922f13829f6932913f9f); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal026d0ae70983922f13829f6932913f9f)): ?>
<?php $component = $__componentOriginal026d0ae70983922f13829f6932913f9f; ?>
<?php unset($__componentOriginal026d0ae70983922f13829f6932913f9f); ?>
<?php endif; ?>
                 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalb4e1d3352348902d30955c9827e95353)): ?>
<?php $attributes = $__attributesOriginalb4e1d3352348902d30955c9827e95353; ?>
<?php unset($__attributesOriginalb4e1d3352348902d30955c9827e95353); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalb4e1d3352348902d30955c9827e95353)): ?>
<?php $component = $__componentOriginalb4e1d3352348902d30955c9827e95353; ?>
<?php unset($__componentOriginalb4e1d3352348902d30955c9827e95353); ?>
<?php endif; ?>
            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal8ed4d2c4c8d055b5cd9b81025b3a3f27)): ?>
<?php $attributes = $__attributesOriginal8ed4d2c4c8d055b5cd9b81025b3a3f27; ?>
<?php unset($__attributesOriginal8ed4d2c4c8d055b5cd9b81025b3a3f27); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal8ed4d2c4c8d055b5cd9b81025b3a3f27)): ?>
<?php $component = $__componentOriginal8ed4d2c4c8d055b5cd9b81025b3a3f27; ?>
<?php unset($__componentOriginal8ed4d2c4c8d055b5cd9b81025b3a3f27); ?>
<?php endif; ?>

         <?php $__env->slot('footer', null, []); ?> 
            <?php echo e($attendances->links()); ?>

         <?php $__env->endSlot(); ?>
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
</div>
<?php /**PATH C:\laragon\www\Kopma\resources\views/livewire/report/attendance-report.blade.php ENDPATH**/ ?>