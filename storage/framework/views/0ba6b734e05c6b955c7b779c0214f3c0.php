<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'variant' => 'primary',
    'size' => 'md',
    'type' => 'button',
    'loading' => false,
    'disabled' => false,
    'icon' => null,
    'href' => null,
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
    'variant' => 'primary',
    'size' => 'md',
    'type' => 'button',
    'loading' => false,
    'disabled' => false,
    'icon' => null,
    'href' => null,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
$variants = [
    'primary' => 'bg-indigo-600 hover:bg-indigo-700 text-white focus:ring-indigo-500',
    'secondary' => 'bg-green-600 hover:bg-green-700 text-white focus:ring-green-500',
    'success' => 'bg-green-500 hover:bg-green-700 text-white focus:ring-green-500',
    'danger' => 'bg-red-500 hover:bg-red-700 text-white focus:ring-red-500',
    'warning' => 'bg-yellow-500 hover:bg-yellow-600 text-white focus:ring-yellow-500',
    'info' => 'bg-blue-500 hover:bg-blue-700 text-white focus:ring-blue-500',
    'white' => 'bg-white text-gray-700 border border-gray-300 hover:bg-gray-50 focus:ring-indigo-500',
    'outline' => 'bg-transparent border-2 border-indigo-600 text-indigo-600 hover:bg-indigo-50 focus:ring-indigo-500',
    'ghost' => 'bg-transparent text-gray-700 hover:bg-gray-100 focus:ring-gray-500',
];

$sizes = [
    'sm' => 'px-3 py-1.5 text-xs',
    'md' => 'px-4 py-2 text-sm',
    'lg' => 'px-6 py-3 text-base',
];

$baseClasses = 'inline-flex items-center justify-center font-semibold rounded-lg transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed';

$isDisabled = $loading || $disabled;
?>

<!--[if BLOCK]><![endif]--><?php if($href && !$isDisabled): ?>
<a
    href="<?php echo e($href); ?>"
    <?php echo e($attributes->merge(['class' => implode(' ', [$baseClasses, $variants[$variant], $sizes[$size]])])); ?>

>
    <!--[if BLOCK]><![endif]--><?php if($loading): ?>
        <?php if (isset($component)) { $__componentOriginal7ee43febc033d8a87ae157694e6933ee = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal7ee43febc033d8a87ae157694e6933ee = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.spinner','data' => ['class' => 'mr-2','size' => 'sm','color' => 'white']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.spinner'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'mr-2','size' => 'sm','color' => 'white']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal7ee43febc033d8a87ae157694e6933ee)): ?>
<?php $attributes = $__attributesOriginal7ee43febc033d8a87ae157694e6933ee; ?>
<?php unset($__attributesOriginal7ee43febc033d8a87ae157694e6933ee); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal7ee43febc033d8a87ae157694e6933ee)): ?>
<?php $component = $__componentOriginal7ee43febc033d8a87ae157694e6933ee; ?>
<?php unset($__componentOriginal7ee43febc033d8a87ae157694e6933ee); ?>
<?php endif; ?>
    <?php elseif($icon): ?>
        <?php if (isset($component)) { $__componentOriginal56804098dcf376a0e2227cb77b6cd00a = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal56804098dcf376a0e2227cb77b6cd00a = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.icon','data' => ['name' => $icon,'class' => 'mr-2 w-5 h-5']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($icon),'class' => 'mr-2 w-5 h-5']); ?>
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
    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
    <?php echo e($slot); ?>

</a>
<?php else: ?>
<button
    type="<?php echo e($type); ?>"
    <?php echo e($attributes->merge(['class' => implode(' ', [$baseClasses, $variants[$variant], $sizes[$size]])])); ?>

    <?php if($isDisabled): ?> disabled <?php endif; ?>
>
    <!--[if BLOCK]><![endif]--><?php if($loading): ?>
        <?php if (isset($component)) { $__componentOriginal7ee43febc033d8a87ae157694e6933ee = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal7ee43febc033d8a87ae157694e6933ee = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.spinner','data' => ['class' => 'mr-2','size' => 'sm','color' => 'white']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.spinner'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'mr-2','size' => 'sm','color' => 'white']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal7ee43febc033d8a87ae157694e6933ee)): ?>
<?php $attributes = $__attributesOriginal7ee43febc033d8a87ae157694e6933ee; ?>
<?php unset($__attributesOriginal7ee43febc033d8a87ae157694e6933ee); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal7ee43febc033d8a87ae157694e6933ee)): ?>
<?php $component = $__componentOriginal7ee43febc033d8a87ae157694e6933ee; ?>
<?php unset($__componentOriginal7ee43febc033d8a87ae157694e6933ee); ?>
<?php endif; ?>
    <?php elseif($icon): ?>
        <?php if (isset($component)) { $__componentOriginal56804098dcf376a0e2227cb77b6cd00a = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal56804098dcf376a0e2227cb77b6cd00a = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.icon','data' => ['name' => $icon,'class' => 'mr-2 w-5 h-5']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($icon),'class' => 'mr-2 w-5 h-5']); ?>
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
    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
    <?php echo e($slot); ?>

</button>
<?php endif; ?><!--[if ENDBLOCK]><![endif]-->
<?php /**PATH C:\laragon\www\Kopma\resources\views/components/ui/button.blade.php ENDPATH**/ ?>