<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'cols' => '1',
    'gap' => '6',
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
    'cols' => '1',
    'gap' => '6',
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
// Responsive column configurations
$colsConfig = [
    '1' => 'grid-cols-1',
    '2' => 'grid-cols-1 md:grid-cols-2',
    '3' => 'grid-cols-1 md:grid-cols-2 lg:grid-cols-3',
    '4' => 'grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4',
];

// Gap configurations
$gapConfig = [
    '2' => 'gap-2',
    '3' => 'gap-3',
    '4' => 'gap-4',
    '5' => 'gap-5',
    '6' => 'gap-6',
    '8' => 'gap-8',
];

$gridClasses = $colsConfig[$cols] ?? $colsConfig['1'];
$gapClasses = $gapConfig[$gap] ?? $gapConfig['6'];
?>

<div <?php echo e($attributes->merge(['class' => 'grid ' . $gridClasses . ' ' . $gapClasses])); ?>>
    <?php echo e($slot); ?>

</div>
<?php /**PATH C:\laragon\www\Kopma\resources\views/components/layout/grid.blade.php ENDPATH**/ ?>