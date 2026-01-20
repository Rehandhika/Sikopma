<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">

    <title><?php echo e($title ?? config('app.name', 'SIKOPMA')); ?></title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link rel="dns-prefetch" href="https://fonts.bunny.net">

    <link rel="preload" href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" as="style">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <?php echo $__env->make('public.partials.theme-init', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <?php echo app('Illuminate\Foundation\Vite')->reactRefresh(); ?>
    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/react/main.jsx']); ?>
</head>
<body class="bg-background text-foreground antialiased">
    <script type="application/json" id="public-initial-data"><?php echo json_encode($initial ?? null, 15, 512) ?></script>
    <div
        id="react-public"
        data-page="<?php echo e($page ?? 'home'); ?>"
        <?php if(isset($slug)): ?> data-slug="<?php echo e($slug); ?>" <?php endif; ?>
    ></div>
</body>
</html>
<?php /**PATH C:\laragon\www\Kopma\resources\views/public/react.blade.php ENDPATH**/ ?>