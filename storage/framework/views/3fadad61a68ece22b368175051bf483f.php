<div class="space-y-6">
    <!-- Page Header -->
    <?php if (isset($component)) { $__componentOriginal4743781065990dfe96029737c4f06097 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal4743781065990dfe96029737c4f06097 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.layout.page-header','data' => ['title' => 'Penalti Saya','description' => 'Lihat dan kelola penalti yang Anda terima']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('layout.page-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Penalti Saya','description' => 'Lihat dan kelola penalti yang Anda terima']); ?>
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

    <!-- Warning Alert for High Points -->
    <!--[if BLOCK]><![endif]--><?php if($summary['total_points'] >= 50): ?>
        <?php if (isset($component)) { $__componentOriginal746de018ded8594083eb43be3f1332e1 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal746de018ded8594083eb43be3f1332e1 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.alert','data' => ['variant' => 'danger','dismissible' => true,'icon' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.alert'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => 'danger','dismissible' => true,'icon' => true]); ?>
            <div class="font-medium">Peringatan: Total Poin Penalti Tinggi</div>
            <div class="mt-1 text-sm">
                Anda memiliki <?php echo e($summary['total_points']); ?> poin penalti aktif. Harap segera menyelesaikan masalah ini untuk menghindari sanksi lebih lanjut.
            </div>
         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal746de018ded8594083eb43be3f1332e1)): ?>
<?php $attributes = $__attributesOriginal746de018ded8594083eb43be3f1332e1; ?>
<?php unset($__attributesOriginal746de018ded8594083eb43be3f1332e1); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal746de018ded8594083eb43be3f1332e1)): ?>
<?php $component = $__componentOriginal746de018ded8594083eb43be3f1332e1; ?>
<?php unset($__componentOriginal746de018ded8594083eb43be3f1332e1); ?>
<?php endif; ?>
    <?php elseif($summary['total_points'] >= 30): ?>
        <?php if (isset($component)) { $__componentOriginal746de018ded8594083eb43be3f1332e1 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal746de018ded8594083eb43be3f1332e1 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.alert','data' => ['variant' => 'warning','dismissible' => true,'icon' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.alert'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => 'warning','dismissible' => true,'icon' => true]); ?>
            <div class="font-medium">Perhatian: Poin Penalti Meningkat</div>
            <div class="mt-1 text-sm">
                Anda memiliki <?php echo e($summary['total_points']); ?> poin penalti aktif. Mohon perhatikan kehadiran dan kinerja Anda.
            </div>
         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal746de018ded8594083eb43be3f1332e1)): ?>
<?php $attributes = $__attributesOriginal746de018ded8594083eb43be3f1332e1; ?>
<?php unset($__attributesOriginal746de018ded8594083eb43be3f1332e1); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal746de018ded8594083eb43be3f1332e1)): ?>
<?php $component = $__componentOriginal746de018ded8594083eb43be3f1332e1; ?>
<?php unset($__componentOriginal746de018ded8594083eb43be3f1332e1); ?>
<?php endif; ?>
    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->

    <!-- Summary Cards -->
    <?php if (isset($component)) { $__componentOriginalddfdd8eb2a2b0d47c29a0436357ad57f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalddfdd8eb2a2b0d47c29a0436357ad57f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.layout.grid','data' => ['cols' => '4','gap' => '4']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('layout.grid'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['cols' => '4','gap' => '4']); ?>
        <?php if (isset($component)) { $__componentOriginala4a09407c281b10513bf47f7415fb4c1 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginala4a09407c281b10513bf47f7415fb4c1 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.layout.stat-card','data' => ['label' => 'Total Poin','value' => $summary['total_points'],'icon' => 'exclamation-triangle','iconColor' => 'bg-danger-100','iconTextColor' => 'text-danger-600']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('layout.stat-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => 'Total Poin','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($summary['total_points']),'icon' => 'exclamation-triangle','iconColor' => 'bg-danger-100','iconTextColor' => 'text-danger-600']); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.layout.stat-card','data' => ['label' => 'Aktif','value' => $summary['by_status']['active'] ?? 0,'icon' => 'exclamation-circle','iconColor' => 'bg-gray-100','iconTextColor' => 'text-gray-600']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('layout.stat-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => 'Aktif','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($summary['by_status']['active'] ?? 0),'icon' => 'exclamation-circle','iconColor' => 'bg-gray-100','iconTextColor' => 'text-gray-600']); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.layout.stat-card','data' => ['label' => 'Banding','value' => $summary['by_status']['appealed'] ?? 0,'icon' => 'clock','iconColor' => 'bg-warning-100','iconTextColor' => 'text-warning-600']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('layout.stat-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => 'Banding','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($summary['by_status']['appealed'] ?? 0),'icon' => 'clock','iconColor' => 'bg-warning-100','iconTextColor' => 'text-warning-600']); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.layout.stat-card','data' => ['label' => 'Total Penalti','value' => $summary['count'],'icon' => 'document-text','iconColor' => 'bg-gray-100','iconTextColor' => 'text-gray-600']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('layout.stat-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => 'Total Penalti','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($summary['count']),'icon' => 'document-text','iconColor' => 'bg-gray-100','iconTextColor' => 'text-gray-600']); ?>
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

    <!-- Filter -->
    <?php if (isset($component)) { $__componentOriginaldae4cd48acb67888a4631e1ba48f2f93 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginaldae4cd48acb67888a4631e1ba48f2f93 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.card','data' => ['padding' => 'true']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['padding' => 'true']); ?>
        <?php if (isset($component)) { $__componentOriginal231e2c645bf8af0c5c05a5dc5a94c862 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal231e2c645bf8af0c5c05a5dc5a94c862 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.select','data' => ['name' => 'statusFilter','wire:model.live' => 'statusFilter','label' => 'Filter Status']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.select'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'statusFilter','wire:model.live' => 'statusFilter','label' => 'Filter Status']); ?>
            <option value="">Semua Status</option>
            <option value="active">Aktif</option>
            <option value="appealed">Banding</option>
            <option value="dismissed">Dibatalkan</option>
            <option value="expired">Kedaluwarsa</option>
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
<?php if (isset($__attributesOriginaldae4cd48acb67888a4631e1ba48f2f93)): ?>
<?php $attributes = $__attributesOriginaldae4cd48acb67888a4631e1ba48f2f93; ?>
<?php unset($__attributesOriginaldae4cd48acb67888a4631e1ba48f2f93); ?>
<?php endif; ?>
<?php if (isset($__componentOriginaldae4cd48acb67888a4631e1ba48f2f93)): ?>
<?php $component = $__componentOriginaldae4cd48acb67888a4631e1ba48f2f93; ?>
<?php unset($__componentOriginaldae4cd48acb67888a4631e1ba48f2f93); ?>
<?php endif; ?>

    <!-- Penalty List -->
    <?php if (isset($component)) { $__componentOriginaldae4cd48acb67888a4631e1ba48f2f93 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginaldae4cd48acb67888a4631e1ba48f2f93 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.card','data' => ['padding' => 'false']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['padding' => 'false']); ?>
        <?php if (isset($component)) { $__componentOriginal8ed4d2c4c8d055b5cd9b81025b3a3f27 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal8ed4d2c4c8d055b5cd9b81025b3a3f27 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.data.table','data' => ['headers' => ['Tanggal', 'Jenis', 'Deskripsi', 'Poin', 'Status']]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('data.table'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['headers' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(['Tanggal', 'Jenis', 'Deskripsi', 'Poin', 'Status'])]); ?>
            <!--[if BLOCK]><![endif]--><?php $__empty_1 = true; $__currentLoopData = $penalties; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $penalty): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
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
<?php $component->withAttributes([]); ?>
                        <?php echo e($penalty->date->format('d/m/Y')); ?>

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
                    <?php if (isset($component)) { $__componentOriginal026d0ae70983922f13829f6932913f9f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal026d0ae70983922f13829f6932913f9f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.data.table-cell','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('data.table-cell'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
                        <?php echo e($penalty->penaltyType->name ?? '-'); ?>

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
                    <?php if (isset($component)) { $__componentOriginal026d0ae70983922f13829f6932913f9f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal026d0ae70983922f13829f6932913f9f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.data.table-cell','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('data.table-cell'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
                        <div class="max-w-xs truncate"><?php echo e($penalty->description); ?></div>
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
                    <?php if (isset($component)) { $__componentOriginal026d0ae70983922f13829f6932913f9f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal026d0ae70983922f13829f6932913f9f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.data.table-cell','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('data.table-cell'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
                        <span class="font-semibold text-danger-600"><?php echo e($penalty->points); ?></span>
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
                    <?php if (isset($component)) { $__componentOriginal026d0ae70983922f13829f6932913f9f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal026d0ae70983922f13829f6932913f9f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.data.table-cell','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('data.table-cell'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
                        <?php if (isset($component)) { $__componentOriginalab7baa01105b3dfe1e0cf1dfc58879b4 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalab7baa01105b3dfe1e0cf1dfc58879b4 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.badge','data' => ['variant' => match($penalty->status) {
                                'active' => 'danger',
                                'appealed' => 'warning',
                                'dismissed' => 'secondary',
                                'expired' => 'gray',
                                default => 'gray'
                            }]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(match($penalty->status) {
                                'active' => 'danger',
                                'appealed' => 'warning',
                                'dismissed' => 'secondary',
                                'expired' => 'gray',
                                default => 'gray'
                            })]); ?>
                            <?php echo e(match($penalty->status) {
                                'active' => 'Aktif',
                                'appealed' => 'Banding',
                                'dismissed' => 'Dibatalkan',
                                'expired' => 'Kedaluwarsa',
                                default => ucfirst($penalty->status)
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.data.table-cell','data' => ['colspan' => '5']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('data.table-cell'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['colspan' => '5']); ?>
                        <?php if (isset($component)) { $__componentOriginalfe16eb12133e72aabae529d081318460 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalfe16eb12133e72aabae529d081318460 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.layout.empty-state','data' => ['icon' => 'document-text','title' => 'Tidak ada penalti','description' => 'Anda tidak memiliki penalti saat ini']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('layout.empty-state'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => 'document-text','title' => 'Tidak ada penalti','description' => 'Anda tidak memiliki penalti saat ini']); ?>
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

    <!-- Pagination -->
    <?php if (isset($component)) { $__componentOriginal49b03e4fe6969761a6e8370f5d162f5c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal49b03e4fe6969761a6e8370f5d162f5c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.data.pagination','data' => ['paginator' => $penalties]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('data.pagination'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['paginator' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($penalties)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal49b03e4fe6969761a6e8370f5d162f5c)): ?>
<?php $attributes = $__attributesOriginal49b03e4fe6969761a6e8370f5d162f5c; ?>
<?php unset($__attributesOriginal49b03e4fe6969761a6e8370f5d162f5c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal49b03e4fe6969761a6e8370f5d162f5c)): ?>
<?php $component = $__componentOriginal49b03e4fe6969761a6e8370f5d162f5c; ?>
<?php unset($__componentOriginal49b03e4fe6969761a6e8370f5d162f5c); ?>
<?php endif; ?>
</div>
<?php /**PATH C:\laragon\www\Kopma\resources\views/livewire/penalty/index.blade.php ENDPATH**/ ?>