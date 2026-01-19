<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'headers' => [],
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
    'headers' => [],
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

<div <?php echo e($attributes->merge(['class' => 'overflow-x-auto'])); ?>>
    <table class="min-w-full divide-y divide-gray-200">
        <!--[if BLOCK]><![endif]--><?php if(count($headers) > 0): ?>
        <thead class="bg-gray-50">
            <tr>
                <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $headers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $header): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    <?php echo e($header); ?>

                </th>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
            </tr>
        </thead>
        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
        
        <tbody class="<?php echo \Illuminate\Support\Arr::toCssClasses([
            'bg-white divide-y divide-gray-200',
            'divide-y-0' => $striped,
        ]); ?>">
            <?php echo e($slot); ?>

        </tbody>
    </table>
</div>
<?php /**PATH C:\laragon\www\Kopma\resources\views/components/data/table.blade.php ENDPATH**/ ?>