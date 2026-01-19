<?php
// Navigation link base classes
$linkBaseClasses = 'flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2';
$linkActiveClasses = 'bg-indigo-50 text-indigo-700';
$linkInactiveClasses = 'text-gray-700 hover:bg-gray-100 hover:text-gray-900';

// Submenu link classes
$submenuLinkBaseClasses = 'block px-3 py-2 text-sm rounded-lg transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2';
$submenuLinkActiveClasses = 'bg-indigo-50 text-indigo-700 font-medium';
$submenuLinkInactiveClasses = 'text-gray-600 hover:bg-gray-100 hover:text-gray-900';

// Dropdown button classes
$dropdownButtonBaseClasses = 'w-full flex items-center justify-between px-3 py-2 text-sm font-medium rounded-lg transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2';
?>


<a href="<?php echo e(route('admin.dashboard')); ?>" 
   class="<?php echo e($linkBaseClasses); ?> <?php echo e(request()->routeIs('admin.dashboard') ? $linkActiveClasses : $linkInactiveClasses); ?>"
   aria-current="<?php echo e(request()->routeIs('admin.dashboard') ? 'page' : 'false'); ?>">
    <?php if (isset($component)) { $__componentOriginal56804098dcf376a0e2227cb77b6cd00a = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal56804098dcf376a0e2227cb77b6cd00a = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.icon','data' => ['name' => 'home','class' => 'w-5 h-5 mr-3 flex-shrink-0']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'home','class' => 'w-5 h-5 mr-3 flex-shrink-0']); ?>
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
    <span>Dashboard</span>
</a>


<div x-data="{ open: <?php echo e(request()->routeIs('admin.attendance.*') ? 'true' : 'false'); ?> }">
    <button @click="open = !open" 
            type="button"
            class="<?php echo e($dropdownButtonBaseClasses); ?> <?php echo e(request()->routeIs('admin.attendance.*') ? $linkActiveClasses : $linkInactiveClasses); ?>"
            aria-expanded="<?php echo e(request()->routeIs('admin.attendance.*') ? 'true' : 'false'); ?>"
            aria-controls="attendance-submenu">
        <div class="flex items-center min-w-0">
            <?php if (isset($component)) { $__componentOriginal56804098dcf376a0e2227cb77b6cd00a = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal56804098dcf376a0e2227cb77b6cd00a = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.icon','data' => ['name' => 'clipboard-list','class' => 'w-5 h-5 mr-3 flex-shrink-0']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'clipboard-list','class' => 'w-5 h-5 mr-3 flex-shrink-0']); ?>
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
            <span>Absensi</span>
        </div>
        <?php if (isset($component)) { $__componentOriginal56804098dcf376a0e2227cb77b6cd00a = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal56804098dcf376a0e2227cb77b6cd00a = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.icon','data' => ['name' => 'chevron-down','class' => 'w-4 h-4 ml-2 flex-shrink-0 transition-transform duration-200',':class' => '{ \'rotate-180\': open }']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'chevron-down','class' => 'w-4 h-4 ml-2 flex-shrink-0 transition-transform duration-200',':class' => '{ \'rotate-180\': open }']); ?>
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
    </button>
    <div x-show="open" 
         x-collapse 
         id="attendance-submenu"
         class="ml-8 mt-1 space-y-1"
         role="menu">
        <a href="<?php echo e(route('admin.attendance.check-in-out')); ?>" 
           class="<?php echo e($submenuLinkBaseClasses); ?> <?php echo e(request()->routeIs('admin.attendance.check-in-out') ? $submenuLinkActiveClasses : $submenuLinkInactiveClasses); ?>"
           role="menuitem"
           aria-current="<?php echo e(request()->routeIs('admin.attendance.check-in-out') ? 'page' : 'false'); ?>">
            Check In/Out
        </a>
        <a href="<?php echo e(route('admin.attendance.index')); ?>" 
           class="<?php echo e($submenuLinkBaseClasses); ?> <?php echo e(request()->routeIs('admin.attendance.index') ? $submenuLinkActiveClasses : $submenuLinkInactiveClasses); ?>"
           role="menuitem"
           aria-current="<?php echo e(request()->routeIs('admin.attendance.index') ? 'page' : 'false'); ?>">
            Daftar Absensi
        </a>
        <a href="<?php echo e(route('admin.attendance.history')); ?>" 
           class="<?php echo e($submenuLinkBaseClasses); ?> <?php echo e(request()->routeIs('admin.attendance.history') ? $submenuLinkActiveClasses : $submenuLinkInactiveClasses); ?>"
           role="menuitem"
           aria-current="<?php echo e(request()->routeIs('admin.attendance.history') ? 'page' : 'false'); ?>">
            Riwayat
        </a>
    </div>
</div>


<div x-data="{ open: <?php echo e(request()->routeIs('admin.schedule.*') || request()->routeIs('admin.leave.*') || request()->routeIs('admin.swap.*') ? 'true' : 'false'); ?> }">
    <button @click="open = !open" 
            type="button"
            class="<?php echo e($dropdownButtonBaseClasses); ?> <?php echo e(request()->routeIs('admin.schedule.*') || request()->routeIs('admin.leave.*') || request()->routeIs('admin.swap.*') ? $linkActiveClasses : $linkInactiveClasses); ?>"
            aria-expanded="<?php echo e(request()->routeIs('admin.schedule.*') || request()->routeIs('admin.leave.*') || request()->routeIs('admin.swap.*') ? 'true' : 'false'); ?>"
            aria-controls="schedule-submenu">
        <div class="flex items-center min-w-0">
            <?php if (isset($component)) { $__componentOriginal56804098dcf376a0e2227cb77b6cd00a = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal56804098dcf376a0e2227cb77b6cd00a = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.icon','data' => ['name' => 'calendar','class' => 'w-5 h-5 mr-3 flex-shrink-0']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'calendar','class' => 'w-5 h-5 mr-3 flex-shrink-0']); ?>
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
            <span>Jadwal</span>
        </div>
        <?php if (isset($component)) { $__componentOriginal56804098dcf376a0e2227cb77b6cd00a = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal56804098dcf376a0e2227cb77b6cd00a = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.icon','data' => ['name' => 'chevron-down','class' => 'w-4 h-4 ml-2 flex-shrink-0 transition-transform duration-200',':class' => '{ \'rotate-180\': open }']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'chevron-down','class' => 'w-4 h-4 ml-2 flex-shrink-0 transition-transform duration-200',':class' => '{ \'rotate-180\': open }']); ?>
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
    </button>
    <div x-show="open" 
         x-collapse 
         id="schedule-submenu"
         class="ml-8 mt-1 space-y-1"
         role="menu">
        
        <a href="<?php echo e(route('admin.schedule.index')); ?>" 
           class="<?php echo e($submenuLinkBaseClasses); ?> <?php echo e(request()->routeIs('admin.schedule.index') || request()->routeIs('admin.schedule.create') || request()->routeIs('admin.schedule.edit') || request()->routeIs('admin.schedule.history') ? $submenuLinkActiveClasses : $submenuLinkInactiveClasses); ?>"
           role="menuitem">
            Kelola Jadwal
        </a>
        
        <a href="<?php echo e(route('admin.schedule.my-schedule')); ?>" 
           class="<?php echo e($submenuLinkBaseClasses); ?> <?php echo e(request()->routeIs('admin.schedule.my-schedule') ? $submenuLinkActiveClasses : $submenuLinkInactiveClasses); ?>"
           role="menuitem">
            Jadwal Saya
        </a>
        
        <a href="<?php echo e(route('admin.schedule.availability')); ?>" 
           class="<?php echo e($submenuLinkBaseClasses); ?> <?php echo e(request()->routeIs('admin.schedule.availability') ? $submenuLinkActiveClasses : $submenuLinkInactiveClasses); ?>"
           role="menuitem">
            Ketersediaan
        </a>
        
        <a href="<?php echo e(route('admin.leave.index')); ?>" 
           class="<?php echo e($submenuLinkBaseClasses); ?> <?php echo e(request()->routeIs('admin.leave.*') ? $submenuLinkActiveClasses : $submenuLinkInactiveClasses); ?>"
           role="menuitem">
            Izin/Cuti
        </a>
        
        <a href="<?php echo e(route('admin.swap.index')); ?>" 
           class="<?php echo e($submenuLinkBaseClasses); ?> <?php echo e(request()->routeIs('admin.swap.*') ? $submenuLinkActiveClasses : $submenuLinkInactiveClasses); ?>"
           role="menuitem">
            Perubahan Jadwal
        </a>
    </div>
</div>


<div x-data="{ open: <?php echo e(request()->routeIs('admin.cashier.*') ? 'true' : 'false'); ?> }">
    <button @click="open = !open" 
            type="button"
            class="<?php echo e($dropdownButtonBaseClasses); ?> <?php echo e(request()->routeIs('admin.cashier.*') ? $linkActiveClasses : $linkInactiveClasses); ?>"
            aria-expanded="<?php echo e(request()->routeIs('admin.cashier.*') ? 'true' : 'false'); ?>"
            aria-controls="cashier-submenu">
        <div class="flex items-center min-w-0">
            <?php if (isset($component)) { $__componentOriginal56804098dcf376a0e2227cb77b6cd00a = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal56804098dcf376a0e2227cb77b6cd00a = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.icon','data' => ['name' => 'currency-dollar','class' => 'w-5 h-5 mr-3 flex-shrink-0']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'currency-dollar','class' => 'w-5 h-5 mr-3 flex-shrink-0']); ?>
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
            <span>Kasir / POS</span>
        </div>
        <?php if (isset($component)) { $__componentOriginal56804098dcf376a0e2227cb77b6cd00a = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal56804098dcf376a0e2227cb77b6cd00a = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.icon','data' => ['name' => 'chevron-down','class' => 'w-4 h-4 ml-2 flex-shrink-0 transition-transform duration-200',':class' => '{ \'rotate-180\': open }']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'chevron-down','class' => 'w-4 h-4 ml-2 flex-shrink-0 transition-transform duration-200',':class' => '{ \'rotate-180\': open }']); ?>
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
    </button>
    <div x-show="open" 
         x-collapse 
         id="cashier-submenu"
         class="ml-8 mt-1 space-y-1"
         role="menu">
        <a href="<?php echo e(route('admin.cashier.pos')); ?>" 
           class="<?php echo e($submenuLinkBaseClasses); ?> <?php echo e(request()->routeIs('admin.cashier.pos') ? $submenuLinkActiveClasses : $submenuLinkInactiveClasses); ?>"
           role="menuitem"
           aria-current="<?php echo e(request()->routeIs('admin.cashier.pos') ? 'page' : 'false'); ?>">
            POS Kasir
        </a>
        <?php if(auth()->user()->hasAnyRole(['Super Admin', 'Ketua', 'Wakil Ketua'])): ?>
        <a href="<?php echo e(route('admin.cashier.pos-entry')); ?>" 
           class="<?php echo e($submenuLinkBaseClasses); ?> <?php echo e(request()->routeIs('admin.cashier.pos-entry') ? $submenuLinkActiveClasses : $submenuLinkInactiveClasses); ?>"
           role="menuitem"
           aria-current="<?php echo e(request()->routeIs('admin.cashier.pos-entry') ? 'page' : 'false'); ?>">
            Entry Transaksi
        </a>
        <?php endif; ?>
    </div>
</div>


<div x-data="{ open: <?php echo e(request()->routeIs('admin.products.*') || request()->routeIs('admin.stock.*') ? 'true' : 'false'); ?> }">
    <button @click="open = !open" 
            type="button"
            class="<?php echo e($dropdownButtonBaseClasses); ?> <?php echo e(request()->routeIs('admin.products.*') || request()->routeIs('admin.stock.*') ? $linkActiveClasses : $linkInactiveClasses); ?>"
            aria-expanded="<?php echo e(request()->routeIs('admin.products.*') || request()->routeIs('admin.stock.*') ? 'true' : 'false'); ?>"
            aria-controls="inventory-submenu">
        <div class="flex items-center min-w-0">
            <?php if (isset($component)) { $__componentOriginal56804098dcf376a0e2227cb77b6cd00a = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal56804098dcf376a0e2227cb77b6cd00a = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.icon','data' => ['name' => 'cube','class' => 'w-5 h-5 mr-3 flex-shrink-0']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'cube','class' => 'w-5 h-5 mr-3 flex-shrink-0']); ?>
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
            <span>Inventaris</span>
        </div>
        <?php if (isset($component)) { $__componentOriginal56804098dcf376a0e2227cb77b6cd00a = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal56804098dcf376a0e2227cb77b6cd00a = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.icon','data' => ['name' => 'chevron-down','class' => 'w-4 h-4 ml-2 flex-shrink-0 transition-transform duration-200',':class' => '{ \'rotate-180\': open }']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'chevron-down','class' => 'w-4 h-4 ml-2 flex-shrink-0 transition-transform duration-200',':class' => '{ \'rotate-180\': open }']); ?>
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
    </button>
    <div x-show="open" 
         x-collapse 
         id="inventory-submenu"
         class="ml-8 mt-1 space-y-1"
         role="menu">
        <a href="<?php echo e(route('admin.products.index')); ?>" 
           class="<?php echo e($submenuLinkBaseClasses); ?> <?php echo e(request()->routeIs('admin.products.*') ? $submenuLinkActiveClasses : $submenuLinkInactiveClasses); ?>"
           role="menuitem"
           aria-current="<?php echo e(request()->routeIs('admin.products.*') ? 'page' : 'false'); ?>">
            Daftar Produk
        </a>
        <a href="<?php echo e(route('admin.stock.index')); ?>" 
           class="<?php echo e($submenuLinkBaseClasses); ?> <?php echo e(request()->routeIs('admin.stock.*') ? $submenuLinkActiveClasses : $submenuLinkInactiveClasses); ?>"
           role="menuitem"
           aria-current="<?php echo e(request()->routeIs('admin.stock.*') ? 'page' : 'false'); ?>">
            Manajemen Stok
        </a>
    </div>
</div>


<a href="<?php echo e(route('admin.penalties.index')); ?>" 
   class="<?php echo e($linkBaseClasses); ?> <?php echo e(request()->routeIs('admin.penalties.*') ? $linkActiveClasses : $linkInactiveClasses); ?>"
   aria-current="<?php echo e(request()->routeIs('admin.penalties.*') ? 'page' : 'false'); ?>">
    <?php if (isset($component)) { $__componentOriginal56804098dcf376a0e2227cb77b6cd00a = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal56804098dcf376a0e2227cb77b6cd00a = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.icon','data' => ['name' => 'exclamation-triangle','class' => 'w-5 h-5 mr-3 flex-shrink-0']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'exclamation-triangle','class' => 'w-5 h-5 mr-3 flex-shrink-0']); ?>
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
    <span>Sanksi</span>
</a>


<div x-data="{ open: <?php echo e(request()->routeIs('admin.reports.*') ? 'true' : 'false'); ?> }">
    <button @click="open = !open" 
            type="button"
            class="<?php echo e($dropdownButtonBaseClasses); ?> <?php echo e(request()->routeIs('admin.reports.*') ? $linkActiveClasses : $linkInactiveClasses); ?>"
            aria-expanded="<?php echo e(request()->routeIs('admin.reports.*') ? 'true' : 'false'); ?>"
            aria-controls="reports-submenu">
        <div class="flex items-center min-w-0">
            <?php if (isset($component)) { $__componentOriginal56804098dcf376a0e2227cb77b6cd00a = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal56804098dcf376a0e2227cb77b6cd00a = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.icon','data' => ['name' => 'document','class' => 'w-5 h-5 mr-3 flex-shrink-0']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'document','class' => 'w-5 h-5 mr-3 flex-shrink-0']); ?>
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
            <span>Laporan</span>
        </div>
        <?php if (isset($component)) { $__componentOriginal56804098dcf376a0e2227cb77b6cd00a = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal56804098dcf376a0e2227cb77b6cd00a = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.icon','data' => ['name' => 'chevron-down','class' => 'w-4 h-4 ml-2 flex-shrink-0 transition-transform duration-200',':class' => '{ \'rotate-180\': open }']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'chevron-down','class' => 'w-4 h-4 ml-2 flex-shrink-0 transition-transform duration-200',':class' => '{ \'rotate-180\': open }']); ?>
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
    </button>
    <div x-show="open" 
         x-collapse 
         id="reports-submenu"
         class="ml-8 mt-1 space-y-1"
         role="menu">
        <a href="<?php echo e(route('admin.reports.attendance')); ?>" 
           class="<?php echo e($submenuLinkBaseClasses); ?> <?php echo e(request()->routeIs('admin.reports.attendance') ? $submenuLinkActiveClasses : $submenuLinkInactiveClasses); ?>"
           role="menuitem"
           aria-current="<?php echo e(request()->routeIs('admin.reports.attendance') ? 'page' : 'false'); ?>">
            Laporan Absensi
        </a>
        <a href="<?php echo e(route('admin.reports.sales')); ?>" 
           class="<?php echo e($submenuLinkBaseClasses); ?> <?php echo e(request()->routeIs('admin.reports.sales') ? $submenuLinkActiveClasses : $submenuLinkInactiveClasses); ?>"
           role="menuitem"
           aria-current="<?php echo e(request()->routeIs('admin.reports.sales') ? 'page' : 'false'); ?>">
            Laporan Penjualan
        </a>
        <a href="<?php echo e(route('admin.reports.penalties')); ?>" 
           class="<?php echo e($submenuLinkBaseClasses); ?> <?php echo e(request()->routeIs('admin.reports.penalties') ? $submenuLinkActiveClasses : $submenuLinkInactiveClasses); ?>"
           role="menuitem"
           aria-current="<?php echo e(request()->routeIs('admin.reports.penalties') ? 'page' : 'false'); ?>">
            Laporan Sanksi
        </a>
    </div>
</div>


<div class="border-t border-gray-200 my-2" role="separator"></div>


<a href="<?php echo e(route('admin.users.index')); ?>" 
   class="<?php echo e($linkBaseClasses); ?> <?php echo e(request()->routeIs('admin.users.*') ? $linkActiveClasses : $linkInactiveClasses); ?>"
   aria-current="<?php echo e(request()->routeIs('admin.users.*') ? 'page' : 'false'); ?>">
    <?php if (isset($component)) { $__componentOriginal56804098dcf376a0e2227cb77b6cd00a = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal56804098dcf376a0e2227cb77b6cd00a = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.icon','data' => ['name' => 'user-group','class' => 'w-5 h-5 mr-3 flex-shrink-0']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'user-group','class' => 'w-5 h-5 mr-3 flex-shrink-0']); ?>
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
    <span>Manajemen User</span>
</a>


<a href="<?php echo e(route('admin.roles.index')); ?>" 
   class="<?php echo e($linkBaseClasses); ?> <?php echo e(request()->routeIs('admin.roles.*') ? $linkActiveClasses : $linkInactiveClasses); ?>"
   aria-current="<?php echo e(request()->routeIs('admin.roles.*') ? 'page' : 'false'); ?>">
    <?php if (isset($component)) { $__componentOriginal56804098dcf376a0e2227cb77b6cd00a = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal56804098dcf376a0e2227cb77b6cd00a = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.icon','data' => ['name' => 'check-circle','class' => 'w-5 h-5 mr-3 flex-shrink-0']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'check-circle','class' => 'w-5 h-5 mr-3 flex-shrink-0']); ?>
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
    <span>Role & Permission</span>
</a>


<div x-data="{ open: <?php echo e(request()->routeIs('admin.settings.*') ? 'true' : 'false'); ?> }">
    <button @click="open = !open" 
            type="button"
            class="<?php echo e($dropdownButtonBaseClasses); ?> <?php echo e(request()->routeIs('admin.settings.*') ? $linkActiveClasses : $linkInactiveClasses); ?>"
            aria-expanded="<?php echo e(request()->routeIs('admin.settings.*') ? 'true' : 'false'); ?>"
            aria-controls="settings-submenu">
        <div class="flex items-center min-w-0">
            <?php if (isset($component)) { $__componentOriginal56804098dcf376a0e2227cb77b6cd00a = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal56804098dcf376a0e2227cb77b6cd00a = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.icon','data' => ['name' => 'cog','class' => 'w-5 h-5 mr-3 flex-shrink-0']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'cog','class' => 'w-5 h-5 mr-3 flex-shrink-0']); ?>
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
            <span>Pengaturan</span>
        </div>
        <?php if (isset($component)) { $__componentOriginal56804098dcf376a0e2227cb77b6cd00a = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal56804098dcf376a0e2227cb77b6cd00a = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.icon','data' => ['name' => 'chevron-down','class' => 'w-4 h-4 ml-2 flex-shrink-0 transition-transform duration-200',':class' => '{ \'rotate-180\': open }']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'chevron-down','class' => 'w-4 h-4 ml-2 flex-shrink-0 transition-transform duration-200',':class' => '{ \'rotate-180\': open }']); ?>
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
    </button>
    <div x-show="open" 
         x-collapse 
         id="settings-submenu"
         class="ml-8 mt-1 space-y-1"
         role="menu">
        <a href="<?php echo e(route('admin.settings.general')); ?>" 
           class="<?php echo e($submenuLinkBaseClasses); ?> <?php echo e(request()->routeIs('admin.settings.general') ? $submenuLinkActiveClasses : $submenuLinkInactiveClasses); ?>"
           role="menuitem"
           aria-current="<?php echo e(request()->routeIs('admin.settings.general') ? 'page' : 'false'); ?>">
            Pengaturan Umum
        </a>
        <a href="<?php echo e(route('admin.settings.system')); ?>" 
           class="<?php echo e($submenuLinkBaseClasses); ?> <?php echo e(request()->routeIs('admin.settings.system') ? $submenuLinkActiveClasses : $submenuLinkInactiveClasses); ?>"
           role="menuitem"
           aria-current="<?php echo e(request()->routeIs('admin.settings.system') ? 'page' : 'false'); ?>">
            Pengaturan Sistem
        </a>
        <?php if(auth()->user()->hasAnyRole(['Super Admin', 'Ketua', 'Wakil Ketua'])): ?>
        <a href="<?php echo e(route('admin.settings.store')); ?>" 
           class="<?php echo e($submenuLinkBaseClasses); ?> <?php echo e(request()->routeIs('admin.settings.store') ? $submenuLinkActiveClasses : $submenuLinkInactiveClasses); ?>"
           role="menuitem"
           aria-current="<?php echo e(request()->routeIs('admin.settings.store') ? 'page' : 'false'); ?>">
            Pengaturan Toko
        </a>
        <?php endif; ?>
        <?php if(auth()->user()->hasAnyRole(['Super Admin', 'Ketua'])): ?>
        <a href="<?php echo e(route('admin.settings.banners')); ?>" 
           class="<?php echo e($submenuLinkBaseClasses); ?> <?php echo e(request()->routeIs('admin.settings.banners') ? $submenuLinkActiveClasses : $submenuLinkInactiveClasses); ?>"
           role="menuitem"
           aria-current="<?php echo e(request()->routeIs('admin.settings.banners') ? 'page' : 'false'); ?>">
            Kelola Banner
        </a>
        <?php endif; ?>
    </div>
</div>


<a href="<?php echo e(route('admin.profile.edit')); ?>" 
   class="<?php echo e($linkBaseClasses); ?> <?php echo e(request()->routeIs('admin.profile.*') ? $linkActiveClasses : $linkInactiveClasses); ?>"
   aria-current="<?php echo e(request()->routeIs('admin.profile.*') ? 'page' : 'false'); ?>">
    <?php if (isset($component)) { $__componentOriginal56804098dcf376a0e2227cb77b6cd00a = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal56804098dcf376a0e2227cb77b6cd00a = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.icon','data' => ['name' => 'user','class' => 'w-5 h-5 mr-3 flex-shrink-0']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'user','class' => 'w-5 h-5 mr-3 flex-shrink-0']); ?>
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
    <span>Profil Saya</span>
</a>
<?php /**PATH C:\laragon\www\Kopma\resources\views/components/navigation.blade.php ENDPATH**/ ?>