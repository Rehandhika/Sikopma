<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'title' => null,
    'subtitle' => null,
    'padding' => true,
    'shadow' => 'md',
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
    'title' => null,
    'subtitle' => null,
    'padding' => true,
    'shadow' => 'md',
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
$shadows = [
    'none' => '',
    'sm' => 'shadow-sm',
    'md' => 'shadow-md',
    'lg' => 'shadow-lg',
];
?>

<div <?php echo e($attributes->merge(['class' => 'bg-white rounded-lg border border-gray-200 overflow-hidden ' . $shadows[$shadow]])); ?>>
    <!--[if BLOCK]><![endif]--><?php if($title || $subtitle): ?>
    <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
        <!--[if BLOCK]><![endif]--><?php if($title): ?>
        <h3 class="text-lg font-semibold text-gray-900"><?php echo e($title); ?></h3>
        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
        <!--[if BLOCK]><![endif]--><?php if($subtitle): ?>
        <p class="mt-1 text-sm text-gray-500"><?php echo e($subtitle); ?></p>
        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
    </div>
    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->

    <div class="<?php echo \Illuminate\Support\Arr::toCssClasses(['px-6 py-4' => $padding]); ?>">
        <?php echo e($slot); ?>

    </div>

    <!--[if BLOCK]><![endif]--><?php if(isset($footer)): ?>
    <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
        <?php echo e($footer); ?>

    </div>
    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
</div>
<?php /**PATH C:\laragon\www\Kopma\resources\views/components/ui/card.blade.php ENDPATH**/ ?>