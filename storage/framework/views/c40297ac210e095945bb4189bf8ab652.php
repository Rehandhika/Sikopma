<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'src' => null,
    'name' => '',
    'size' => 'md',
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
    'src' => null,
    'name' => '',
    'size' => 'md',
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
$sizes = [
    'sm' => 'w-8 h-8 text-xs',
    'md' => 'w-10 h-10 text-sm',
    'lg' => 'w-12 h-12 text-base',
    'xl' => 'w-16 h-16 text-xl',
];

$initials = collect(explode(' ', $name))
    ->map(fn($word) => strtoupper(substr($word, 0, 1)))
    ->take(2)
    ->join('');
?>

<div <?php echo e($attributes->merge(['class' => 'inline-flex items-center justify-center rounded-full overflow-hidden bg-indigo-500 text-white font-semibold ' . $sizes[$size]])); ?>>
    <?php if($src): ?>
        <img src="<?php echo e($src); ?>" alt="<?php echo e($name); ?>" class="w-full h-full object-cover">
    <?php else: ?>
        <span><?php echo e($initials); ?></span>
    <?php endif; ?>
</div>
<?php /**PATH C:\laragon\www\Kopma\resources\views/components/ui/avatar.blade.php ENDPATH**/ ?>