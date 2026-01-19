<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'striped' => true,
    'hoverable' => true,
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
    'striped' => true,
    'hoverable' => true,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<tr <?php echo e($attributes->merge([
    'class' => implode(' ', array_filter([
        $striped ? 'odd:bg-white even:bg-gray-50' : '',
        $hoverable ? 'hover:bg-gray-100 transition-colors' : '',
    ]))
])); ?>>
    <?php echo e($slot); ?>

</tr>
<?php /**PATH C:\laragon\www\Kopma\resources\views/components/data/table-row.blade.php ENDPATH**/ ?>