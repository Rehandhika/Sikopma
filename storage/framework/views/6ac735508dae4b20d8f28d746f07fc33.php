<div class="space-y-4">
    
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-bold text-gray-900">Manajemen Stok</h1>
            <p class="text-sm text-gray-500"><?php echo e($this->stats['total']); ?> produk terdaftar</p>
        </div>
        <!--[if BLOCK]><![endif]--><?php if(count($selectedProducts) > 0): ?>
            <div class="flex items-center gap-2">
                <span class="text-sm text-gray-600"><?php echo e(count($selectedProducts)); ?> dipilih</span>
                <?php if (isset($component)) { $__componentOriginala8bb031a483a05f647cb99ed3a469847 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginala8bb031a483a05f647cb99ed3a469847 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.button','data' => ['variant' => 'primary','size' => 'sm','wire:click' => 'openBulkModal']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => 'primary','size' => 'sm','wire:click' => 'openBulkModal']); ?>Bulk Adjust <?php echo $__env->renderComponent(); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.button','data' => ['variant' => 'ghost','size' => 'sm','wire:click' => 'clearSelection']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => 'ghost','size' => 'sm','wire:click' => 'clearSelection']); ?>× <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginala8bb031a483a05f647cb99ed3a469847)): ?>
<?php $attributes = $__attributesOriginala8bb031a483a05f647cb99ed3a469847; ?>
<?php unset($__attributesOriginala8bb031a483a05f647cb99ed3a469847); ?>
<?php endif; ?>
<?php if (isset($__componentOriginala8bb031a483a05f647cb99ed3a469847)): ?>
<?php $component = $__componentOriginala8bb031a483a05f647cb99ed3a469847; ?>
<?php unset($__componentOriginala8bb031a483a05f647cb99ed3a469847); ?>
<?php endif; ?>
            </div>
        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
    </div>

    
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
        <?php
            $filters = [
                'all' => ['label' => 'Semua', 'value' => $this->stats['total'], 'color' => 'gray', 'icon' => 'cube'],
                'normal' => ['label' => 'Normal', 'value' => $this->stats['normal'], 'color' => 'green', 'icon' => 'check-circle'],
                'low' => ['label' => 'Rendah', 'value' => $this->stats['low'], 'color' => 'yellow', 'icon' => 'exclamation-triangle'],
                'out' => ['label' => 'Habis', 'value' => $this->stats['out'], 'color' => 'red', 'icon' => 'x-circle'],
            ];
        ?>
        <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $filters; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $filter): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <button 
                wire:click="$set('stockFilter', '<?php echo e($key); ?>')" 
                class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                    'p-3 rounded-xl text-left transition-all duration-200',
                    'ring-2 ring-primary-500 bg-primary-50' => $stockFilter === $key,
                    'bg-white border border-gray-200 hover:border-gray-300 hover:shadow-sm' => $stockFilter !== $key,
                ]); ?>"
            >
                <div class="flex items-center justify-between">
                    <span class="text-xs font-medium text-gray-500 uppercase"><?php echo e($filter['label']); ?></span>
                    <span class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                        'w-6 h-6 rounded-full flex items-center justify-center',
                        "bg-{$filter['color']}-100 text-{$filter['color']}-600",
                    ]); ?>">
                        <?php if (isset($component)) { $__componentOriginal56804098dcf376a0e2227cb77b6cd00a = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal56804098dcf376a0e2227cb77b6cd00a = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.icon','data' => ['name' => $filter['icon'],'class' => 'w-3.5 h-3.5']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($filter['icon']),'class' => 'w-3.5 h-3.5']); ?>
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
                    </span>
                </div>
                <p class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                    'text-2xl font-bold mt-1',
                    "text-{$filter['color']}-600" => $filter['value'] > 0 || $key === 'all',
                    'text-gray-400' => $filter['value'] === 0 && $key !== 'all',
                ]); ?>"><?php echo e($filter['value']); ?></p>
            </button>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
    </div>

    
    <div class="flex flex-wrap items-center gap-4 sm:gap-8 px-4 py-3 bg-slate-800 rounded-xl text-white text-sm">
        <div>
            <span class="text-slate-400">Modal:</span>
            <span class="font-semibold ml-1">Rp <?php echo e(number_format($this->stats['cost'], 0, ',', '.')); ?></span>
        </div>
        <div>
            <span class="text-slate-400">Nilai:</span>
            <span class="font-semibold ml-1">Rp <?php echo e(number_format($this->stats['value'], 0, ',', '.')); ?></span>
        </div>
        <div>
            <span class="text-slate-400">Profit:</span>
            <span class="font-semibold ml-1 text-green-400">+Rp <?php echo e(number_format($this->stats['profit'], 0, ',', '.')); ?></span>
        </div>
    </div>

    
    <div class="flex flex-col sm:flex-row sm:items-center gap-3">
        <div class="flex gap-1 p-1 bg-gray-100 rounded-lg w-fit">
            <button wire:click="setTab('products')" class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                'px-4 py-2 text-sm font-medium rounded-md transition-all',
                'bg-white shadow text-gray-900' => $activeTab === 'products',
                'text-gray-600 hover:text-gray-900' => $activeTab !== 'products',
            ]); ?>">Produk</button>
            <button wire:click="setTab('history')" class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                'px-4 py-2 text-sm font-medium rounded-md transition-all',
                'bg-white shadow text-gray-900' => $activeTab === 'history',
                'text-gray-600 hover:text-gray-900' => $activeTab !== 'history',
            ]); ?>">Riwayat</button>
        </div>
        
        <div class="flex-1 flex gap-2">
            <div class="relative flex-1">
                <?php if (isset($component)) { $__componentOriginal56804098dcf376a0e2227cb77b6cd00a = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal56804098dcf376a0e2227cb77b6cd00a = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.icon','data' => ['name' => 'search','class' => 'w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-gray-400']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'search','class' => 'w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-gray-400']); ?>
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
                <input 
                    type="text" 
                    wire:model.live.debounce.300ms="<?php echo e($activeTab === 'products' ? 'search' : 'historySearch'); ?>"
                    placeholder="Cari produk..."
                    class="w-full pl-9 pr-4 py-2 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                >
            </div>
            <!--[if BLOCK]><![endif]--><?php if($activeTab === 'products'): ?>
                <select wire:model.live="categoryFilter" class="text-sm border border-gray-200 rounded-lg px-3 py-2 focus:ring-2 focus:ring-primary-500">
                    <option value="">Kategori</option>
                    <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $this->categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($cat); ?>"><?php echo e($cat); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
                </select>
            <?php else: ?>
                <select wire:model.live="historyType" class="text-sm border border-gray-200 rounded-lg px-3 py-2 focus:ring-2 focus:ring-primary-500">
                    <option value="all">Semua</option>
                    <option value="in">Masuk</option>
                    <option value="out">Keluar</option>
                </select>
            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
        </div>
    </div>

    
    <!--[if BLOCK]><![endif]--><?php if($activeTab === 'products'): ?>
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            
            <div class="hidden sm:block overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 text-xs font-medium text-gray-500 uppercase">
                        <tr>
                            <th class="w-8 px-3 py-3">
                                <input type="checkbox" wire:click="selectAllVisible" 
                                    <?php if(count($selectedProducts) > 0 && count($selectedProducts) === $this->products->filter(fn($p) => !$p->has_variants)->count()): echo 'checked'; endif; ?>
                                    class="rounded border-gray-300 text-primary-600 focus:ring-primary-500">
                            </th>
                            <th class="w-10 px-2 py-3"></th>
                            <th class="px-3 py-3 text-left">Produk</th>
                            <th class="px-3 py-3 text-center w-24">Stok</th>
                            <th class="px-3 py-3 text-center w-28">Adjust</th>
                            <th class="px-3 py-3 text-right w-32">Harga</th>
                            <th class="px-3 py-3 text-right w-36">Nilai</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <!--[if BLOCK]><![endif]--><?php $__empty_1 = true; $__currentLoopData = $this->products; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            
                            <tr wire:key="p-<?php echo e($product->id); ?>" class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                                'transition-colors',
                                'bg-primary-50/30' => in_array($product->id, $expandedProducts) && $product->has_variants,
                                'hover:bg-gray-50/50' => !in_array($product->id, $expandedProducts),
                            ]); ?>">
                                <td class="px-3 py-2">
                                    <!--[if BLOCK]><![endif]--><?php if(!$product->has_variants): ?>
                                        <input type="checkbox" wire:click="toggleProductSelection(<?php echo e($product->id); ?>)"
                                            <?php if(in_array($product->id, $selectedProducts)): echo 'checked'; endif; ?>
                                            class="rounded border-gray-300 text-primary-600 focus:ring-primary-500">
                                    <?php else: ?>
                                        <span class="w-4 h-4 block"></span>
                                    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                </td>
                                <td class="px-2 py-2">
                                    <!--[if BLOCK]><![endif]--><?php if($product->has_variants): ?>
                                        <button wire:click="toggleExpand(<?php echo e($product->id); ?>)" 
                                            class="w-7 h-7 rounded-lg flex items-center justify-center hover:bg-gray-100 transition-all"
                                            title="<?php echo e(in_array($product->id, $expandedProducts) ? 'Tutup varian' : 'Lihat varian'); ?>">
                                            <?php if (isset($component)) { $__componentOriginal56804098dcf376a0e2227cb77b6cd00a = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal56804098dcf376a0e2227cb77b6cd00a = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.icon','data' => ['name' => in_array($product->id, $expandedProducts) ? 'chevron-down' : 'chevron-right','class' => 'w-4 h-4 text-gray-500 transition-transform']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(in_array($product->id, $expandedProducts) ? 'chevron-down' : 'chevron-right'),'class' => 'w-4 h-4 text-gray-500 transition-transform']); ?>
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
                                    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                </td>
                                <td class="px-3 py-2">
                                    <div class="flex items-center gap-2">
                                        <!--[if BLOCK]><![endif]--><?php if($product->image): ?>
                                            <img src="<?php echo e($product->image_thumbnail_url); ?>" alt="" class="w-8 h-8 rounded-lg object-cover bg-gray-100" loading="lazy">
                                        <?php else: ?>
                                            <div class="w-8 h-8 rounded-lg bg-gray-100 flex items-center justify-center">
                                                <?php if (isset($component)) { $__componentOriginal56804098dcf376a0e2227cb77b6cd00a = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal56804098dcf376a0e2227cb77b6cd00a = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.icon','data' => ['name' => 'cube','class' => 'w-4 h-4 text-gray-400']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'cube','class' => 'w-4 h-4 text-gray-400']); ?>
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
                                        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                        <div class="min-w-0">
                                            <div class="flex items-center gap-2">
                                                <p class="font-medium text-gray-900 text-sm truncate"><?php echo e($product->name); ?></p>
                                                <!--[if BLOCK]><![endif]--><?php if($product->has_variants): ?>
                                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-medium bg-purple-100 text-purple-700">
                                                        <?php echo e($product->variants->count()); ?> varian
                                                    </span>
                                                <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                            </div>
                                            <p class="text-xs text-gray-500"><?php echo e($product->sku ?? $product->category ?? '-'); ?></p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-3 py-2 text-center">
                                    <!--[if BLOCK]><![endif]--><?php if($product->has_variants): ?>
                                        <button wire:click="toggleExpand(<?php echo e($product->id); ?>)" 
                                            class="group cursor-pointer hover:bg-gray-100 rounded-lg px-2 py-1 transition-colors"
                                            title="Klik untuk lihat varian">
                                            <span class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                                                'text-lg font-bold',
                                                'text-red-600' => $product->total_stock <= 0,
                                                'text-yellow-600' => $product->total_stock > 0 && $product->total_stock <= $product->min_stock,
                                                'text-gray-900' => $product->total_stock > $product->min_stock,
                                            ]); ?>"><?php echo e($product->total_stock); ?></span>
                                            <span class="block text-[10px] text-gray-400">total</span>
                                        </button>
                                    <?php else: ?>
                                        <button wire:click="quickAdjust(<?php echo e($product->id); ?>, 'in')" 
                                            class="group cursor-pointer hover:bg-gray-100 rounded-lg px-2 py-1 transition-colors"
                                            title="Klik untuk adjust">
                                            <span class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                                                'text-lg font-bold',
                                                'text-red-600' => $product->stock <= 0,
                                                'text-yellow-600' => $product->stock > 0 && $product->stock <= $product->min_stock,
                                                'text-gray-900' => $product->stock > $product->min_stock,
                                            ]); ?>"><?php echo e($product->stock); ?></span>
                                            <span class="block text-[10px] text-gray-400">min <?php echo e($product->min_stock); ?></span>
                                        </button>
                                    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                </td>
                                <td class="px-3 py-2">
                                    <!--[if BLOCK]><![endif]--><?php if($product->has_variants): ?>
                                        <div class="flex items-center justify-center">
                                            <button wire:click="toggleExpand(<?php echo e($product->id); ?>)" 
                                                class="text-xs text-primary-600 hover:text-primary-700 font-medium">
                                                <?php echo e(in_array($product->id, $expandedProducts) ? 'Tutup' : 'Expand'); ?>

                                            </button>
                                        </div>
                                    <?php else: ?>
                                        <div class="flex items-center justify-center gap-1">
                                            <button wire:click="quickDecrement(<?php echo e($product->id); ?>)" 
                                                <?php if($product->stock <= 0): echo 'disabled'; endif; ?>
                                                class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                                                    'w-7 h-7 rounded-md text-sm font-bold transition-all',
                                                    'bg-red-50 text-red-600 hover:bg-red-100' => $product->stock > 0,
                                                    'bg-gray-100 text-gray-300 cursor-not-allowed' => $product->stock <= 0,
                                                ]); ?>">−</button>
                                            <button wire:click="quickIncrement(<?php echo e($product->id); ?>)" 
                                                class="w-7 h-7 rounded-md bg-green-50 text-green-600 hover:bg-green-100 text-sm font-bold transition-all">+</button>
                                        </div>
                                    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                </td>
                                <td class="px-3 py-2 text-right text-sm">
                                    <!--[if BLOCK]><![endif]--><?php if($product->has_variants): ?>
                                        <p class="font-medium text-gray-600"><?php echo e($product->display_price); ?></p>
                                    <?php else: ?>
                                        <p class="font-medium">Rp <?php echo e(number_format($product->price, 0, ',', '.')); ?></p>
                                        <p class="text-xs text-gray-400"><?php echo e(number_format($product->cost_price, 0, ',', '.')); ?></p>
                                    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                </td>
                                <td class="px-3 py-2 text-right text-sm">
                                    <?php 
                                        $stockVal = $product->has_variants ? $product->total_stock : $product->stock;
                                        $value = $stockVal * $product->price;
                                        $profit = $stockVal * ($product->price - $product->cost_price);
                                    ?>
                                    <p class="font-medium">Rp <?php echo e(number_format($value, 0, ',', '.')); ?></p>
                                    <p class="text-xs <?php echo e($profit >= 0 ? 'text-green-600' : 'text-red-600'); ?>">
                                        <?php echo e($profit >= 0 ? '+' : ''); ?><?php echo e(number_format($profit, 0, ',', '.')); ?>

                                    </p>
                                </td>
                            </tr>
                            
                            
                            <!--[if BLOCK]><![endif]--><?php if($product->has_variants && in_array($product->id, $expandedProducts)): ?>
                                <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $product->variants; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $variant): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <tr wire:key="v-<?php echo e($variant->id); ?>" class="bg-gray-50/70 hover:bg-gray-100/70 transition-colors">
                                        <td class="px-3 py-2"></td>
                                        <td class="px-2 py-2">
                                            <div class="w-7 h-7 flex items-center justify-center">
                                                <div class="w-2 h-2 rounded-full bg-gray-300"></div>
                                            </div>
                                        </td>
                                        <td class="px-3 py-2">
                                            <div class="flex items-center gap-2 pl-2">
                                                <div class="w-6 h-6 rounded bg-gray-200 flex items-center justify-center">
                                                    <?php if (isset($component)) { $__componentOriginal56804098dcf376a0e2227cb77b6cd00a = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal56804098dcf376a0e2227cb77b6cd00a = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.icon','data' => ['name' => 'tag','class' => 'w-3 h-3 text-gray-500']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'tag','class' => 'w-3 h-3 text-gray-500']); ?>
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
                                                <div class="min-w-0">
                                                    <p class="font-medium text-gray-700 text-sm"><?php echo e($variant->variant_name); ?></p>
                                                    <p class="text-xs text-gray-400"><?php echo e($variant->sku ?? '-'); ?></p>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-3 py-2 text-center">
                                            <button wire:click="quickAdjust(<?php echo e($product->id); ?>, 'in', <?php echo e($variant->id); ?>)" 
                                                class="group cursor-pointer hover:bg-white rounded-lg px-2 py-1 transition-colors"
                                                title="Klik untuk adjust varian">
                                                <span class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                                                    'text-base font-bold',
                                                    'text-red-600' => $variant->stock <= 0,
                                                    'text-yellow-600' => $variant->stock > 0 && $variant->stock <= $variant->min_stock,
                                                    'text-gray-900' => $variant->stock > $variant->min_stock,
                                                ]); ?>"><?php echo e($variant->stock); ?></span>
                                                <span class="block text-[10px] text-gray-400">min <?php echo e($variant->min_stock); ?></span>
                                            </button>
                                        </td>
                                        <td class="px-3 py-2">
                                            <div class="flex items-center justify-center gap-1">
                                                <button wire:click="quickDecrementVariant(<?php echo e($variant->id); ?>)" 
                                                    <?php if($variant->stock <= 0): echo 'disabled'; endif; ?>
                                                    class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                                                        'w-6 h-6 rounded text-xs font-bold transition-all',
                                                        'bg-red-50 text-red-600 hover:bg-red-100' => $variant->stock > 0,
                                                        'bg-gray-100 text-gray-300 cursor-not-allowed' => $variant->stock <= 0,
                                                    ]); ?>">−</button>
                                                <button wire:click="quickIncrementVariant(<?php echo e($variant->id); ?>)" 
                                                    class="w-6 h-6 rounded bg-green-50 text-green-600 hover:bg-green-100 text-xs font-bold transition-all">+</button>
                                            </div>
                                        </td>
                                        <td class="px-3 py-2 text-right text-sm">
                                            <p class="font-medium text-gray-700">Rp <?php echo e(number_format($variant->price, 0, ',', '.')); ?></p>
                                            <p class="text-xs text-gray-400"><?php echo e(number_format($variant->cost_price, 0, ',', '.')); ?></p>
                                        </td>
                                        <td class="px-3 py-2 text-right text-sm">
                                            <?php 
                                                $varValue = $variant->stock * $variant->price;
                                                $varProfit = $variant->stock * ($variant->price - $variant->cost_price);
                                            ?>
                                            <p class="font-medium text-gray-700">Rp <?php echo e(number_format($varValue, 0, ',', '.')); ?></p>
                                            <p class="text-xs <?php echo e($varProfit >= 0 ? 'text-green-600' : 'text-red-600'); ?>">
                                                <?php echo e($varProfit >= 0 ? '+' : ''); ?><?php echo e(number_format($varProfit, 0, ',', '.')); ?>

                                            </p>
                                        </td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
                            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="7" class="px-4 py-8 text-center text-gray-500">
                                    <?php if (isset($component)) { $__componentOriginal56804098dcf376a0e2227cb77b6cd00a = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal56804098dcf376a0e2227cb77b6cd00a = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.icon','data' => ['name' => 'cube','class' => 'w-8 h-8 mx-auto mb-2 text-gray-300']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'cube','class' => 'w-8 h-8 mx-auto mb-2 text-gray-300']); ?>
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
                                    <p>Tidak ada produk</p>
                                </td>
                            </tr>
                        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                    </tbody>
                </table>
            </div>

            
            <div class="sm:hidden divide-y divide-gray-100">
                <!--[if BLOCK]><![endif]--><?php $__empty_1 = true; $__currentLoopData = $this->products; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    
                    <div wire:key="pm-<?php echo e($product->id); ?>" class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                        'transition-colors',
                        'bg-primary-50/30' => in_array($product->id, $expandedProducts) && $product->has_variants,
                    ]); ?>">
                        <div class="p-3 flex items-center gap-3">
                            <!--[if BLOCK]><![endif]--><?php if(!$product->has_variants): ?>
                                <input type="checkbox" wire:click="toggleProductSelection(<?php echo e($product->id); ?>)"
                                    <?php if(in_array($product->id, $selectedProducts)): echo 'checked'; endif; ?>
                                    class="rounded border-gray-300 text-primary-600 flex-shrink-0">
                            <?php elseif($product->has_variants): ?>
                                <button wire:click="toggleExpand(<?php echo e($product->id); ?>)" 
                                    class="w-6 h-6 rounded flex items-center justify-center hover:bg-gray-100 flex-shrink-0">
                                    <?php if (isset($component)) { $__componentOriginal56804098dcf376a0e2227cb77b6cd00a = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal56804098dcf376a0e2227cb77b6cd00a = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.icon','data' => ['name' => in_array($product->id, $expandedProducts) ? 'chevron-down' : 'chevron-right','class' => 'w-4 h-4 text-gray-500']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(in_array($product->id, $expandedProducts) ? 'chevron-down' : 'chevron-right'),'class' => 'w-4 h-4 text-gray-500']); ?>
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
                            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                            
                            <!--[if BLOCK]><![endif]--><?php if($product->image): ?>
                                <img src="<?php echo e($product->image_thumbnail_url); ?>" class="w-10 h-10 rounded-lg object-cover flex-shrink-0" loading="lazy">
                            <?php else: ?>
                                <div class="w-10 h-10 rounded-lg bg-gray-100 flex items-center justify-center flex-shrink-0">
                                    <?php if (isset($component)) { $__componentOriginal56804098dcf376a0e2227cb77b6cd00a = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal56804098dcf376a0e2227cb77b6cd00a = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.icon','data' => ['name' => 'cube','class' => 'w-5 h-5 text-gray-400']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'cube','class' => 'w-5 h-5 text-gray-400']); ?>
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
                            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                            
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-1.5">
                                    <p class="font-medium text-sm truncate"><?php echo e($product->name); ?></p>
                                    <!--[if BLOCK]><![endif]--><?php if($product->has_variants): ?>
                                        <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-medium bg-purple-100 text-purple-700 flex-shrink-0">
                                            <?php echo e($product->variants->count()); ?>

                                        </span>
                                    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                </div>
                                <p class="text-xs text-gray-500">
                                    <!--[if BLOCK]><![endif]--><?php if($product->has_variants): ?>
                                        <?php echo e($product->display_price); ?>

                                    <?php else: ?>
                                        Rp <?php echo e(number_format($product->price, 0, ',', '.')); ?>

                                    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                </p>
                            </div>
                            
                            <!--[if BLOCK]><![endif]--><?php if($product->has_variants): ?>
                                <div class="flex items-center gap-2 flex-shrink-0">
                                    <button wire:click="toggleExpand(<?php echo e($product->id); ?>)" class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                                        'px-3 py-1.5 text-center font-bold rounded-lg',
                                        'text-red-600 bg-red-50' => $product->total_stock <= 0,
                                        'text-yellow-600 bg-yellow-50' => $product->total_stock > 0 && $product->total_stock <= $product->min_stock,
                                        'text-gray-900 bg-gray-100' => $product->total_stock > $product->min_stock,
                                    ]); ?>">
                                        <span class="text-base"><?php echo e($product->total_stock); ?></span>
                                        <span class="block text-[10px] font-normal text-gray-500">total</span>
                                    </button>
                                </div>
                            <?php else: ?>
                                <div class="flex items-center gap-2 flex-shrink-0">
                                    <button wire:click="quickDecrement(<?php echo e($product->id); ?>)" <?php if($product->stock <= 0): echo 'disabled'; endif; ?>
                                        class="w-8 h-8 rounded-lg bg-red-50 text-red-600 font-bold disabled:opacity-50">−</button>
                                    <button wire:click="quickAdjust(<?php echo e($product->id); ?>, 'in')" class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                                        'w-10 text-center font-bold',
                                        'text-red-600' => $product->stock <= 0,
                                        'text-yellow-600' => $product->stock > 0 && $product->stock <= $product->min_stock,
                                        'text-gray-900' => $product->stock > $product->min_stock,
                                    ]); ?>"><?php echo e($product->stock); ?></button>
                                    <button wire:click="quickIncrement(<?php echo e($product->id); ?>)" 
                                        class="w-8 h-8 rounded-lg bg-green-50 text-green-600 font-bold">+</button>
                                </div>
                            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                        </div>
                        
                        
                        <!--[if BLOCK]><![endif]--><?php if($product->has_variants && in_array($product->id, $expandedProducts)): ?>
                            <div class="border-t border-gray-100 bg-gray-50/70">
                                <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $product->variants; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $variant): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <div wire:key="vm-<?php echo e($variant->id); ?>" class="px-3 py-2.5 flex items-center gap-3 border-b border-gray-100 last:border-b-0">
                                        <div class="w-6 flex-shrink-0 flex justify-center">
                                            <div class="w-1.5 h-1.5 rounded-full bg-gray-300"></div>
                                        </div>
                                        
                                        <div class="flex-1 min-w-0">
                                            <p class="font-medium text-sm text-gray-700 truncate"><?php echo e($variant->variant_name); ?></p>
                                            <p class="text-xs text-gray-500">Rp <?php echo e(number_format($variant->price, 0, ',', '.')); ?></p>
                                        </div>
                                        
                                        <div class="flex items-center gap-2 flex-shrink-0">
                                            <button wire:click="quickDecrementVariant(<?php echo e($variant->id); ?>)" <?php if($variant->stock <= 0): echo 'disabled'; endif; ?>
                                                class="w-7 h-7 rounded bg-red-50 text-red-600 font-bold text-sm disabled:opacity-50">−</button>
                                            <button wire:click="quickAdjust(<?php echo e($product->id); ?>, 'in', <?php echo e($variant->id); ?>)" class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                                                'w-9 text-center font-bold text-sm',
                                                'text-red-600' => $variant->stock <= 0,
                                                'text-yellow-600' => $variant->stock > 0 && $variant->stock <= $variant->min_stock,
                                                'text-gray-900' => $variant->stock > $variant->min_stock,
                                            ]); ?>"><?php echo e($variant->stock); ?></button>
                                            <button wire:click="quickIncrementVariant(<?php echo e($variant->id); ?>)" 
                                                class="w-7 h-7 rounded bg-green-50 text-green-600 font-bold text-sm">+</button>
                                        </div>
                                    </div>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
                            </div>
                        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <div class="p-8 text-center text-gray-500">Tidak ada produk</div>
                <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
            </div>

            <!--[if BLOCK]><![endif]--><?php if($this->products->hasPages()): ?>
                <div class="px-4 py-3 border-t border-gray-100 bg-gray-50">
                    <?php echo e($this->products->links()); ?>

                </div>
            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
        </div>
    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->

    
    <!--[if BLOCK]><![endif]--><?php if($activeTab === 'history'): ?>
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <div class="divide-y divide-gray-100">
                <!--[if BLOCK]><![endif]--><?php $__empty_1 = true; $__currentLoopData = $this->adjustments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $adj): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <div wire:key="h-<?php echo e($adj->id); ?>" class="p-3 flex items-center gap-3 hover:bg-gray-50/50">
                        <div class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                            'w-8 h-8 rounded-full flex items-center justify-center flex-shrink-0',
                            'bg-green-100 text-green-600' => $adj->type === 'in',
                            'bg-red-100 text-red-600' => $adj->type === 'out',
                        ]); ?>">
                            <?php if (isset($component)) { $__componentOriginal56804098dcf376a0e2227cb77b6cd00a = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal56804098dcf376a0e2227cb77b6cd00a = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.icon','data' => ['name' => $adj->type === 'in' ? 'arrow-up' : 'arrow-down','class' => 'w-4 h-4']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($adj->type === 'in' ? 'arrow-up' : 'arrow-down'),'class' => 'w-4 h-4']); ?>
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
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-1.5">
                                <p class="font-medium text-sm truncate"><?php echo e($adj->product->name ?? '-'); ?></p>
                                <!--[if BLOCK]><![endif]--><?php if($adj->variant): ?>
                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-medium bg-purple-100 text-purple-700 flex-shrink-0">
                                        <?php echo e($adj->variant->variant_name); ?>

                                    </span>
                                <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                            </div>
                            <p class="text-xs text-gray-500 truncate"><?php echo e($adj->reason); ?></p>
                        </div>
                        <div class="text-right flex-shrink-0">
                            <p class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                                'font-bold',
                                'text-green-600' => $adj->type === 'in',
                                'text-red-600' => $adj->type === 'out',
                            ]); ?>"><?php echo e($adj->type === 'in' ? '+' : '-'); ?><?php echo e($adj->quantity); ?></p>
                            <p class="text-xs text-gray-400"><?php echo e($adj->previous_stock); ?> → <?php echo e($adj->new_stock); ?></p>
                        </div>
                        <div class="text-right flex-shrink-0 hidden sm:block">
                            <p class="text-xs text-gray-500"><?php echo e($adj->created_at->format('d/m H:i')); ?></p>
                            <p class="text-xs text-gray-400"><?php echo e($adj->user->name ?? '-'); ?></p>
                        </div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <div class="p-8 text-center text-gray-500">
                        <?php if (isset($component)) { $__componentOriginal56804098dcf376a0e2227cb77b6cd00a = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal56804098dcf376a0e2227cb77b6cd00a = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.icon','data' => ['name' => 'clock','class' => 'w-8 h-8 mx-auto mb-2 text-gray-300']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'clock','class' => 'w-8 h-8 mx-auto mb-2 text-gray-300']); ?>
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
                        <p>Belum ada riwayat</p>
                    </div>
                <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
            </div>
            
            <!--[if BLOCK]><![endif]--><?php if($this->adjustments->hasPages()): ?>
                <div class="px-4 py-3 border-t border-gray-100 bg-gray-50">
                    <?php echo e($this->adjustments->links()); ?>

                </div>
            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
        </div>
    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->

    
    <!--[if BLOCK]><![endif]--><?php if($showAdjustModal && $this->selectedProduct): ?>
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4" x-data x-init="$refs.qty.focus()">
            <div class="fixed inset-0 bg-black/50" wire:click="closeAdjustModal"></div>
            <div class="relative bg-white rounded-2xl shadow-xl w-full max-w-sm overflow-hidden">
                <form wire:submit="saveAdjustment">
                    
                    <div class="p-4 bg-gray-50 border-b flex items-center gap-3">
                        <!--[if BLOCK]><![endif]--><?php if($this->selectedProduct->image): ?>
                            <img src="<?php echo e($this->selectedProduct->image_thumbnail_url); ?>" class="w-10 h-10 rounded-lg object-cover">
                        <?php else: ?>
                            <div class="w-10 h-10 rounded-lg bg-gray-200 flex items-center justify-center">
                                <?php if (isset($component)) { $__componentOriginal56804098dcf376a0e2227cb77b6cd00a = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal56804098dcf376a0e2227cb77b6cd00a = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.icon','data' => ['name' => 'cube','class' => 'w-5 h-5 text-gray-400']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'cube','class' => 'w-5 h-5 text-gray-400']); ?>
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
                        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                        <div class="flex-1 min-w-0">
                            <p class="font-medium truncate"><?php echo e($this->selectedProduct->name); ?></p>
                            <!--[if BLOCK]><![endif]--><?php if($this->selectedVariant): ?>
                                <div class="flex items-center gap-1.5">
                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-medium bg-purple-100 text-purple-700">
                                        <?php echo e($this->selectedVariant->variant_name); ?>

                                    </span>
                                    <span class="text-sm text-gray-500">Stok: <?php echo e($this->selectedVariant->stock); ?></span>
                                </div>
                            <?php else: ?>
                                <p class="text-sm text-gray-500">Stok: <?php echo e($this->selectedProduct->stock); ?></p>
                            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                        </div>
                        <button type="button" wire:click="closeAdjustModal" class="text-gray-400 hover:text-gray-600">
                            <?php if (isset($component)) { $__componentOriginal56804098dcf376a0e2227cb77b6cd00a = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal56804098dcf376a0e2227cb77b6cd00a = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.icon','data' => ['name' => 'x-mark','class' => 'w-5 h-5']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'x-mark','class' => 'w-5 h-5']); ?>
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
                    </div>

                    
                    <!--[if BLOCK]><![endif]--><?php if($this->selectedProduct->has_variants && !$this->selectedVariantId): ?>
                        <div class="p-4 border-b bg-yellow-50">
                            <p class="text-sm text-yellow-800 font-medium mb-2">Pilih varian:</p>
                            <div class="grid grid-cols-2 gap-2 max-h-40 overflow-y-auto">
                                <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $this->selectedProduct->variants; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $variant): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <button type="button" 
                                        wire:click="$set('selectedVariantId', <?php echo e($variant->id); ?>)"
                                        class="p-2 text-left rounded-lg border border-gray-200 bg-white hover:border-primary-500 hover:bg-primary-50 transition-colors">
                                        <p class="font-medium text-sm truncate"><?php echo e($variant->variant_name); ?></p>
                                        <p class="text-xs text-gray-500">Stok: <?php echo e($variant->stock); ?></p>
                                    </button>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
                            </div>
                        </div>
                    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->

                    <div class="p-4 space-y-4">
                        
                        <div class="grid grid-cols-2 gap-2">
                            <button type="button" wire:click="$set('adjustType', 'in')" class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                                'py-3 rounded-xl font-medium transition-all flex items-center justify-center gap-2',
                                'bg-green-500 text-white' => $adjustType === 'in',
                                'bg-gray-100 text-gray-600' => $adjustType !== 'in',
                            ]); ?>">
                                <?php if (isset($component)) { $__componentOriginal56804098dcf376a0e2227cb77b6cd00a = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal56804098dcf376a0e2227cb77b6cd00a = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.icon','data' => ['name' => 'arrow-up','class' => 'w-4 h-4']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'arrow-up','class' => 'w-4 h-4']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal56804098dcf376a0e2227cb77b6cd00a)): ?>
<?php $attributes = $__attributesOriginal56804098dcf376a0e2227cb77b6cd00a; ?>
<?php unset($__attributesOriginal56804098dcf376a0e2227cb77b6cd00a); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal56804098dcf376a0e2227cb77b6cd00a)): ?>
<?php $component = $__componentOriginal56804098dcf376a0e2227cb77b6cd00a; ?>
<?php unset($__componentOriginal56804098dcf376a0e2227cb77b6cd00a); ?>
<?php endif; ?> Tambah
                            </button>
                            <button type="button" wire:click="$set('adjustType', 'out')" class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                                'py-3 rounded-xl font-medium transition-all flex items-center justify-center gap-2',
                                'bg-red-500 text-white' => $adjustType === 'out',
                                'bg-gray-100 text-gray-600' => $adjustType !== 'out',
                            ]); ?>">
                                <?php if (isset($component)) { $__componentOriginal56804098dcf376a0e2227cb77b6cd00a = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal56804098dcf376a0e2227cb77b6cd00a = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.icon','data' => ['name' => 'arrow-down','class' => 'w-4 h-4']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'arrow-down','class' => 'w-4 h-4']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal56804098dcf376a0e2227cb77b6cd00a)): ?>
<?php $attributes = $__attributesOriginal56804098dcf376a0e2227cb77b6cd00a; ?>
<?php unset($__attributesOriginal56804098dcf376a0e2227cb77b6cd00a); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal56804098dcf376a0e2227cb77b6cd00a)): ?>
<?php $component = $__componentOriginal56804098dcf376a0e2227cb77b6cd00a; ?>
<?php unset($__componentOriginal56804098dcf376a0e2227cb77b6cd00a); ?>
<?php endif; ?> Kurangi
                            </button>
                        </div>

                        
                        <div>
                            <input type="number" wire:model="adjustQuantity" x-ref="qty" min="1"
                                class="w-full text-center text-3xl font-bold py-4 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary-500">
                            <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['adjustQuantity'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="text-red-500 text-xs mt-1"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                            
                            
                            <?php
                                $currentStock = $this->selectedVariant ? $this->selectedVariant->stock : $this->selectedProduct->stock;
                                $newStock = $adjustType === 'in' 
                                    ? $currentStock + (int)$adjustQuantity 
                                    : max(0, $currentStock - (int)$adjustQuantity);
                            ?>
                            <div class="flex items-center justify-center gap-2 mt-2 text-sm text-gray-500">
                                <?php echo e($currentStock); ?>

                                <?php if (isset($component)) { $__componentOriginal56804098dcf376a0e2227cb77b6cd00a = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal56804098dcf376a0e2227cb77b6cd00a = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.icon','data' => ['name' => 'arrow-right','class' => 'w-4 h-4']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'arrow-right','class' => 'w-4 h-4']); ?>
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
                                <span class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                                    'font-bold',
                                    'text-green-600' => $adjustType === 'in',
                                    'text-red-600' => $adjustType === 'out',
                                ]); ?>"><?php echo e($newStock); ?></span>
                            </div>
                        </div>

                        
                        <div>
                            <input type="text" wire:model="adjustReason" placeholder="Alasan (wajib)"
                                class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary-500">
                            <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['adjustReason'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="text-red-500 text-xs mt-1"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                        </div>
                    </div>

                    
                    <div class="p-4 border-t bg-gray-50 flex gap-2">
                        <button type="button" wire:click="closeAdjustModal" class="flex-1 py-3 rounded-xl bg-gray-200 text-gray-700 font-medium">Batal</button>
                        <button type="submit" 
                            <?php if($this->selectedProduct->has_variants && !$this->selectedVariantId): echo 'disabled'; endif; ?>
                            class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                                'flex-1 py-3 rounded-xl text-white font-medium transition-all',
                                'bg-green-500 hover:bg-green-600' => $adjustType === 'in',
                                'bg-red-500 hover:bg-red-600' => $adjustType === 'out',
                                'opacity-50 cursor-not-allowed' => $this->selectedProduct->has_variants && !$this->selectedVariantId,
                            ]); ?>">
                            <span wire:loading.remove wire:target="saveAdjustment">Simpan</span>
                            <span wire:loading wire:target="saveAdjustment">...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->

    
    <!--[if BLOCK]><![endif]--><?php if($showBulkModal): ?>
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="fixed inset-0 bg-black/50" wire:click="closeBulkModal"></div>
            <div class="relative bg-white rounded-2xl shadow-xl w-full max-w-sm overflow-hidden">
                <form wire:submit="saveBulkAdjustment">
                    <div class="p-4 border-b">
                        <h3 class="font-semibold">Bulk Adjustment</h3>
                        <p class="text-sm text-gray-500"><?php echo e(count($selectedProducts)); ?> produk dipilih</p>
                    </div>
                    <div class="p-4 space-y-4">
                        <div class="grid grid-cols-2 gap-2">
                            <button type="button" wire:click="$set('bulkType', 'in')" class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                                'py-3 rounded-xl font-medium',
                                'bg-green-500 text-white' => $bulkType === 'in',
                                'bg-gray-100 text-gray-600' => $bulkType !== 'in',
                            ]); ?>">+ Tambah</button>
                            <button type="button" wire:click="$set('bulkType', 'out')" class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                                'py-3 rounded-xl font-medium',
                                'bg-red-500 text-white' => $bulkType === 'out',
                                'bg-gray-100 text-gray-600' => $bulkType !== 'out',
                            ]); ?>">− Kurangi</button>
                        </div>
                        <input type="number" wire:model="adjustQuantity" min="1" placeholder="Jumlah"
                            class="w-full text-center text-2xl font-bold py-3 border rounded-xl">
                        <input type="text" wire:model="bulkReason" placeholder="Alasan"
                            class="w-full px-4 py-3 border rounded-xl">
                    </div>
                    <div class="p-4 border-t flex gap-2">
                        <button type="button" wire:click="closeBulkModal" class="flex-1 py-3 rounded-xl bg-gray-200 font-medium">Batal</button>
                        <button type="submit" class="flex-1 py-3 rounded-xl bg-primary-500 text-white font-medium">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
</div>
<?php /**PATH C:\laragon\www\Kopma\resources\views/livewire/stock/stock-manager.blade.php ENDPATH**/ ?>