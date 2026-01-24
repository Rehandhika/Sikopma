<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'name' => 'image',
    'label' => 'Gambar',
    'preview' => null,
    'existingImage' => null,
    'accept' => 'image/jpeg,image/png,image/webp,image/gif',
    'maxSize' => '5MB',
    'hint' => null,
    'error' => null,
    'required' => false,
    'aspectRatio' => 'square', // square, landscape, portrait
]));

foreach ($attributes->all() as $__key => $__value) {
    if (in_array($__key, $__propNames)) {
        $$__key = $$__key ?? $__value;
    } else {
        $__newAttributes[$__key] = $__value;
    }
}

$attributes = new \Illuminate\View\ComponentAttributeBag($__newAttributes);

unset($__propNames);
unset($__newAttributes);

foreach (array_filter(([
    'name' => 'image',
    'label' => 'Gambar',
    'preview' => null,
    'existingImage' => null,
    'accept' => 'image/jpeg,image/png,image/webp,image/gif',
    'maxSize' => '5MB',
    'hint' => null,
    'error' => null,
    'required' => false,
    'aspectRatio' => 'square', // square, landscape, portrait
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $aspectClasses = match($aspectRatio) {
        'landscape' => 'aspect-video',
        'portrait' => 'aspect-[3/4]',
        default => 'aspect-square',
    };
?>

<div 
    x-data="{ 
        isDragging: false,
        isUploading: false,
        progress: 0
    }"
    <?php echo e($attributes->class(['space-y-2'])); ?>

>
    
    <!--[if BLOCK]><![endif]--><?php if($label): ?>
        <label class="block text-sm font-medium text-gray-700">
            <?php echo e($label); ?>

            <!--[if BLOCK]><![endif]--><?php if($required): ?>
                <span class="text-danger-500">*</span>
            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
        </label>
    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->

    
    <div 
        class="relative"
        x-on:dragover.prevent="isDragging = true"
        x-on:dragleave.prevent="isDragging = false"
        x-on:drop.prevent="isDragging = false; $refs.fileInput.files = $event.dataTransfer.files; $refs.fileInput.dispatchEvent(new Event('change'))"
    >
        
        <!--[if BLOCK]><![endif]--><?php if($preview || $existingImage): ?>
            <div class="relative group">
                <div class="w-full max-w-xs <?php echo e($aspectClasses); ?> bg-gray-100 rounded-lg overflow-hidden border-2 border-gray-200">
                    <img 
                        src="<?php echo e($preview ?? $existingImage); ?>" 
                        alt="Preview" 
                        class="w-full h-full object-cover"
                        loading="lazy"
                    >
                </div>
                
                
                <div class="absolute inset-0 max-w-xs <?php echo e($aspectClasses); ?> bg-black/50 opacity-0 group-hover:opacity-100 transition-opacity rounded-lg flex items-center justify-center gap-2">
                    
                    <label class="cursor-pointer p-2 bg-white rounded-full hover:bg-gray-100 transition">
                        <svg class="w-5 h-5 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        <input 
                            type="file" 
                            wire:model="<?php echo e($name); ?>"
                            accept="<?php echo e($accept); ?>"
                            class="hidden"
                            x-ref="fileInput"
                        >
                    </label>
                    
                    
                    <!--[if BLOCK]><![endif]--><?php if($preview): ?>
                        <button 
                            type="button"
                            wire:click="removeImage"
                            class="p-2 bg-white rounded-full hover:bg-gray-100 transition"
                        >
                            <svg class="w-5 h-5 text-danger-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                        </button>
                    <?php elseif($existingImage): ?>
                        <button 
                            type="button"
                            wire:click="deleteExistingImage"
                            wire:confirm="Hapus gambar ini?"
                            class="p-2 bg-white rounded-full hover:bg-gray-100 transition"
                        >
                            <svg class="w-5 h-5 text-danger-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                        </button>
                    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                </div>

                
                <div 
                    wire:loading 
                    wire:target="<?php echo e($name); ?>"
                    class="absolute inset-0 max-w-xs <?php echo e($aspectClasses); ?> bg-white/80 rounded-lg flex items-center justify-center"
                >
                    <div class="text-center">
                        <svg class="animate-spin h-8 w-8 text-primary-600 mx-auto" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <p class="mt-2 text-sm text-gray-600">Mengupload...</p>
                    </div>
                </div>
            </div>
        <?php else: ?>
            
            <label 
                class="flex flex-col items-center justify-center w-full max-w-xs <?php echo e($aspectClasses); ?> border-2 border-dashed rounded-lg cursor-pointer transition-colors"
                :class="isDragging ? 'border-primary-500 bg-primary-50' : 'border-gray-300 hover:border-gray-400 bg-gray-50 hover:bg-gray-100'"
            >
                <div class="flex flex-col items-center justify-center p-6 text-center" wire:loading.remove wire:target="<?php echo e($name); ?>">
                    <svg class="w-10 h-10 mb-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    <p class="mb-1 text-sm text-gray-600">
                        <span class="font-semibold text-primary-600">Klik untuk upload</span> atau drag & drop
                    </p>
                    <p class="text-xs text-gray-500">PNG, JPG, WebP, GIF (Maks. <?php echo e($maxSize); ?>)</p>
                </div>

                
                <div wire:loading wire:target="<?php echo e($name); ?>" class="flex flex-col items-center justify-center p-6">
                    <svg class="animate-spin h-8 w-8 text-primary-600" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <p class="mt-2 text-sm text-gray-600">Mengupload...</p>
                </div>

                <input 
                    type="file" 
                    wire:model="<?php echo e($name); ?>"
                    accept="<?php echo e($accept); ?>"
                    class="hidden"
                    x-ref="fileInput"
                >
            </label>
        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
    </div>

    
    <!--[if BLOCK]><![endif]--><?php if($hint): ?>
        <p class="text-xs text-gray-500"><?php echo e($hint); ?></p>
    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->

    
    <!--[if BLOCK]><![endif]--><?php if($error): ?>
        <p class="text-sm text-danger-600"><?php echo e($error); ?></p>
    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
</div>
<?php /**PATH C:\laragon\www\Kopma\resources\views/components/ui/image-upload.blade.php ENDPATH**/ ?>