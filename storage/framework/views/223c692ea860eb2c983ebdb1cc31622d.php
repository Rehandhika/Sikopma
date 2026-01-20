<div class="min-h-screen flex flex-col lg:flex-row bg-gray-50 dark:bg-gray-900">
    
    
    <div class="flex-1 flex flex-col lg:h-screen <?php echo e($showCart ? 'hidden lg:flex' : ''); ?>">
        
        
        <header class="sticky top-0 z-10 flex items-center justify-between px-4 lg:px-6 py-3 lg:py-4 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700">
            <div class="flex items-center gap-3 lg:gap-4">
                <a href="<?php echo e(route('admin.dashboard')); ?>" class="p-2 -ml-2 text-gray-500 dark:text-gray-400 rounded-lg">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                </a>
                <div>
                    <h1 class="text-lg lg:text-xl font-bold text-gray-900 dark:text-white">Kasir</h1>
                    <p class="text-xs text-gray-500 dark:text-gray-400 hidden sm:block"><?php echo e(auth()->user()->name); ?></p>
                </div>
            </div>
            
            
            <button wire:click="toggleCart" class="lg:hidden relative p-2.5 text-gray-600 dark:text-gray-300 rounded-xl">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
                <!--[if BLOCK]><![endif]--><?php if($this->cartItemsCount > 0): ?>
                    <span class="absolute -top-0.5 -right-0.5 min-w-5 h-5 px-1 bg-primary-600 text-white text-xs font-bold rounded-full flex items-center justify-center"><?php echo e($this->cartItemsCount); ?></span>
                <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
            </button>
        </header>

        
        <div class="sticky top-[57px] lg:top-[65px] z-10 px-4 lg:px-6 py-3 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 space-y-3">
            <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari produk..." 
                class="w-full px-4 py-2.5 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-xl text-gray-900 dark:text-white placeholder-gray-500 focus:ring-2 focus:ring-primary-500">
            
            <!--[if BLOCK]><![endif]--><?php if($this->categories->isNotEmpty()): ?>
                <div class="flex gap-2 overflow-x-auto pb-1 -mx-4 px-4 lg:mx-0 lg:px-0">
                    <button wire:click="$set('category', '')" 
                        class="px-3 py-1.5 rounded-full text-sm font-medium whitespace-nowrap flex-shrink-0 <?php echo e($category === '' ? 'bg-primary-600 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300'); ?>">
                        Semua
                    </button>
                    <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $this->categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <button wire:click="$set('category', '<?php echo e($cat); ?>')" 
                            class="px-3 py-1.5 rounded-full text-sm font-medium whitespace-nowrap flex-shrink-0 <?php echo e($category === $cat ? 'bg-primary-600 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300'); ?>">
                            <?php echo e($cat); ?>

                        </button>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
                </div>
            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
        </div>

        
        <div class="flex-1 p-3 lg:p-4 lg:overflow-y-auto <?php echo e($this->cartItemsCount > 0 ? 'pb-20 lg:pb-4' : ''); ?>">
            <!--[if BLOCK]><![endif]--><?php if($this->products->isEmpty()): ?>
                <div class="flex flex-col items-center justify-center py-20 text-gray-400">
                    <svg class="w-16 h-16 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                    </svg>
                    <p class="font-medium">Tidak ada produk</p>
                </div>
            <?php else: ?>
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-3 xl:grid-cols-4 2xl:grid-cols-5 gap-2 lg:gap-3">
                    <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $this->products; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <button wire:click="addToCart(<?php echo e($product->id); ?>)" wire:key="product-<?php echo e($product->id); ?>"
                            class="bg-white dark:bg-gray-800 rounded-xl overflow-hidden border border-gray-200 dark:border-gray-700 text-left active:scale-[0.97] active:bg-gray-50 dark:active:bg-gray-700">
                            
                            <div class="aspect-square bg-gray-100 dark:bg-gray-700 relative">
                                <!--[if BLOCK]><![endif]--><?php if($product->image_thumbnail_url): ?>
                                    <img src="<?php echo e($product->image_thumbnail_url); ?>" alt="<?php echo e($product->name); ?>" class="w-full h-full object-cover" loading="lazy">
                                <?php else: ?>
                                    <div class="w-full h-full flex items-center justify-center">
                                        <svg class="w-10 h-10 text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                        </svg>
                                    </div>
                                <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                <span class="absolute top-1.5 right-1.5 px-1.5 py-0.5 text-[10px] font-bold rounded <?php echo e($product->stock <= 5 ? 'bg-red-500 text-white' : 'bg-black/60 text-white'); ?>"><?php echo e($product->stock); ?></span>
                            </div>
                            
                            <div class="p-2 lg:p-2.5">
                                <h3 class="font-medium text-gray-900 dark:text-white text-xs lg:text-sm line-clamp-2 leading-tight min-h-[2rem]"><?php echo e($product->name); ?></h3>
                                <p class="text-primary-600 dark:text-primary-400 font-bold text-xs lg:text-sm mt-1">Rp <?php echo e(number_format($product->price, 0, ',', '.')); ?></p>
                            </div>
                        </button>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
                </div>
            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
        </div>

        
        <!--[if BLOCK]><![endif]--><?php if($this->cartItemsCount > 0): ?>
            <div class="lg:hidden fixed bottom-0 left-0 right-0 z-20 px-4 py-3 bg-white dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700">
                <button wire:click="toggleCart" class="w-full flex items-center justify-between px-4 py-3 bg-primary-600 text-white rounded-xl">
                    <div class="flex items-center gap-3">
                        <span class="w-6 h-6 bg-white/20 rounded-full flex items-center justify-center text-sm font-bold"><?php echo e($this->cartItemsCount); ?></span>
                        <span class="font-semibold">Lihat Keranjang</span>
                    </div>
                    <span class="font-bold">Rp <?php echo e(number_format($this->cartTotal, 0, ',', '.')); ?></span>
                </button>
            </div>
        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
    </div>


    
    <aside class="lg:w-80 xl:w-96 lg:h-screen flex flex-col bg-white dark:bg-gray-800 lg:border-l border-gray-200 dark:border-gray-700
        <?php echo e($showCart ? 'fixed inset-0 z-30' : 'hidden lg:flex'); ?>">
        
        
        <header class="sticky top-0 flex items-center justify-between px-4 py-3 border-b border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800">
            <div class="flex items-center gap-2">
                <button wire:click="toggleCart" class="lg:hidden p-1.5 -ml-1.5 text-gray-500 rounded-lg">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                </button>
                <h2 class="text-base font-bold text-gray-900 dark:text-white">Keranjang</h2>
                <!--[if BLOCK]><![endif]--><?php if($this->cartItemsCount > 0): ?>
                    <span class="px-2 py-0.5 bg-primary-100 dark:bg-primary-900/30 text-primary-700 dark:text-primary-400 text-xs font-semibold rounded-full"><?php echo e($this->cartItemsCount); ?></span>
                <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
            </div>
            <!--[if BLOCK]><![endif]--><?php if(count($cart) > 0): ?>
                <button wire:click="clearCart" wire:confirm="Hapus semua?" class="text-xs text-red-600 font-medium px-2 py-1 rounded">Hapus</button>
            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
        </header>

        
        <div class="flex-1 overflow-y-auto">
            <!--[if BLOCK]><![endif]--><?php if(empty($cart)): ?>
                <div class="flex flex-col items-center justify-center h-full text-gray-400 p-4">
                    <svg class="w-16 h-16 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                    <p class="text-sm font-medium">Keranjang Kosong</p>
                </div>
            <?php else: ?>
                <ul class="divide-y divide-gray-100 dark:divide-gray-700/50">
                    <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $cart; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <li wire:key="cart-<?php echo e($key); ?>" class="p-3 flex gap-2.5">
                            <div class="w-12 h-12 bg-gray-100 dark:bg-gray-700 rounded-lg overflow-hidden flex-shrink-0">
                                <!--[if BLOCK]><![endif]--><?php if($item['image']): ?>
                                    <img src="<?php echo e($item['image']); ?>" alt="" class="w-full h-full object-cover">
                                <?php else: ?>
                                    <div class="w-full h-full flex items-center justify-center">
                                        <svg class="w-6 h-6 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                        </svg>
                                    </div>
                                <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                            </div>
                            
                            <div class="flex-1 min-w-0">
                                <div class="flex items-start justify-between gap-1">
                                    <h4 class="text-sm font-medium text-gray-900 dark:text-white line-clamp-1"><?php echo e($item['name']); ?></h4>
                                    <button wire:click="removeFromCart('<?php echo e($key); ?>')" class="p-1 text-gray-400">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                        </svg>
                                    </button>
                                </div>
                                
                                <div class="flex items-center justify-between mt-1.5">
                                    <div class="flex items-center bg-gray-100 dark:bg-gray-700 rounded-lg">
                                        <button wire:click="decrementQty('<?php echo e($key); ?>')" class="w-7 h-7 flex items-center justify-center text-gray-600 dark:text-gray-300">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/>
                                            </svg>
                                        </button>
                                        <span class="w-8 text-center text-sm font-semibold text-gray-900 dark:text-white"><?php echo e($item['quantity']); ?></span>
                                        <button wire:click="incrementQty('<?php echo e($key); ?>')" class="w-7 h-7 flex items-center justify-center text-gray-600 dark:text-gray-300">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                            </svg>
                                        </button>
                                    </div>
                                    <p class="text-sm font-bold text-gray-900 dark:text-white">Rp <?php echo e(number_format($item['price'] * $item['quantity'], 0, ',', '.')); ?></p>
                                </div>
                            </div>
                        </li>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
                </ul>
            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
        </div>

        
        <!--[if BLOCK]><![endif]--><?php if(count($cart) > 0): ?>
            <footer class="border-t border-gray-200 dark:border-gray-700 p-3 lg:p-4 space-y-3 bg-gray-50 dark:bg-gray-800/50">
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-600 dark:text-gray-400">Total (<?php echo e($this->cartItemsCount); ?>)</span>
                    <span class="text-lg font-bold text-primary-600 dark:text-primary-400">Rp <?php echo e(number_format($this->cartTotal, 0, ',', '.')); ?></span>
                </div>
                <button wire:click="openPayment" class="w-full py-3 bg-primary-600 text-white font-bold rounded-xl">Bayar</button>
            </footer>
        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
    </aside>


    
    <!--[if BLOCK]><![endif]--><?php if($showPayment): ?>
        <div class="fixed inset-0 z-50 flex items-end lg:items-center justify-center">
            <div wire:click="closePayment" class="absolute inset-0 bg-black/50"></div>
            
            <div class="relative w-full lg:max-w-md bg-white dark:bg-gray-800 rounded-t-2xl lg:rounded-2xl max-h-[85vh] flex flex-col" @click.stop>
                
                <div class="lg:hidden flex justify-center pt-2 pb-1">
                    <div class="w-10 h-1 bg-gray-300 rounded-full"></div>
                </div>
                
                <header class="flex items-center justify-between px-5 py-3 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white">Pembayaran</h3>
                    <button wire:click="closePayment" class="p-1.5 text-gray-400">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </header>

                <div class="flex-1 overflow-y-auto p-5 space-y-5">
                    <div class="text-center py-4 bg-primary-50 dark:bg-primary-900/20 rounded-xl">
                        <p class="text-sm text-primary-600 dark:text-primary-400 font-medium">Total</p>
                        <p class="text-2xl font-bold text-primary-700 dark:text-primary-300">Rp <?php echo e(number_format($this->cartTotal, 0, ',', '.')); ?></p>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Metode</label>
                        <div class="grid grid-cols-2 gap-2">
                            <button wire:click="$set('paymentMethod', 'cash')" 
                                class="p-3 rounded-xl border-2 flex items-center justify-center gap-2 <?php echo e($paymentMethod === 'cash' ? 'border-primary-500 bg-primary-50 dark:bg-primary-900/30' : 'border-gray-200 dark:border-gray-700'); ?>">
                                <span class="font-semibold text-sm <?php echo e($paymentMethod === 'cash' ? 'text-primary-700' : 'text-gray-700 dark:text-gray-300'); ?>">Tunai</span>
                            </button>
                            <button wire:click="$set('paymentMethod', 'qris')" 
                                class="p-3 rounded-xl border-2 flex items-center justify-center gap-2 <?php echo e($paymentMethod === 'qris' ? 'border-primary-500 bg-primary-50 dark:bg-primary-900/30' : 'border-gray-200 dark:border-gray-700'); ?>">
                                <span class="font-semibold text-sm <?php echo e($paymentMethod === 'qris' ? 'text-primary-700' : 'text-gray-700 dark:text-gray-300'); ?>">QRIS</span>
                            </button>
                        </div>
                    </div>

                    <!--[if BLOCK]><![endif]--><?php if($paymentMethod === 'cash'): ?>
                        <div class="space-y-3">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Jumlah Bayar</label>
                                <div class="relative">
                                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500 font-semibold text-sm">Rp</span>
                                    <input type="number" wire:model.live="paymentAmount" 
                                        class="w-full pl-10 pr-4 py-3 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-xl text-lg font-bold text-right focus:ring-2 focus:ring-primary-500"
                                        placeholder="0" inputmode="numeric">
                                </div>
                            </div>
                            
                            <div class="grid grid-cols-4 gap-1.5">
                                <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $quickAmounts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $amount): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <button wire:click="setQuickAmount(<?php echo e($amount); ?>)" class="py-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 text-sm font-semibold rounded-lg"><?php echo e(number_format($amount / 1000)); ?>rb</button>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
                            </div>
                            
                            <button wire:click="setExactAmount" class="w-full py-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 text-sm font-semibold rounded-lg">Uang Pas</button>

                            <!--[if BLOCK]><![endif]--><?php if($paymentAmount >= $this->cartTotal && $paymentAmount > 0): ?>
                                <div class="p-3 bg-green-50 dark:bg-green-900/20 rounded-xl flex justify-between items-center">
                                    <span class="text-green-700 dark:text-green-400 font-medium text-sm">Kembalian</span>
                                    <span class="text-lg font-bold text-green-700 dark:text-green-400">Rp <?php echo e(number_format($this->change, 0, ',', '.')); ?></span>
                                </div>
                            <?php elseif($paymentAmount > 0 && $paymentAmount < $this->cartTotal): ?>
                                <div class="p-3 bg-red-50 dark:bg-red-900/20 rounded-xl">
                                    <p class="text-red-600 font-medium text-sm">Kurang Rp <?php echo e(number_format($this->cartTotal - $paymentAmount, 0, ',', '.')); ?></p>
                                </div>
                            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                        </div>
                    <?php else: ?>
                        <div class="p-3 bg-blue-50 dark:bg-blue-900/20 rounded-xl">
                            <p class="text-blue-700 dark:text-blue-400 text-sm">Pembayaran QRIS dengan jumlah pas.</p>
                        </div>
                    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                </div>

                <footer class="px-5 py-4 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50">
                    <button wire:click="processPayment" 
                        wire:loading.attr="disabled"
                        <?php if($paymentMethod === 'cash' && $paymentAmount < $this->cartTotal): ?> disabled <?php endif; ?>
                        class="w-full py-3.5 bg-green-600 disabled:bg-gray-300 dark:disabled:bg-gray-700 text-white font-bold rounded-xl">
                        <span wire:loading.remove wire:target="processPayment">Proses Pembayaran</span>
                        <span wire:loading wire:target="processPayment">Memproses...</span>
                    </button>
                </footer>
            </div>
        </div>
    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
</div>
<?php /**PATH C:\laragon\www\Kopma\resources\views/livewire/cashier/pos.blade.php ENDPATH**/ ?>