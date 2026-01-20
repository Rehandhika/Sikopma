<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    
    <title><?php echo e($title ?? 'POS'); ?> - <?php echo e(config('app.name')); ?></title>
    
    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>
    <?php echo \Livewire\Mechanisms\FrontendAssets\FrontendAssets::styles(); ?>

    
    <style>
        input[type="number"]::-webkit-inner-spin-button,
        input[type="number"]::-webkit-outer-spin-button { -webkit-appearance: none; margin: 0; }
        input[type="number"] { -moz-appearance: textfield; }
    </style>
</head>
<body class="bg-gray-50 dark:bg-gray-900 antialiased">
    <?php echo e($slot); ?>

    
    <?php echo \Livewire\Mechanisms\FrontendAssets\FrontendAssets::scripts(); ?>

    
    <script>
        document.addEventListener('livewire:init', () => {
            Livewire.on('alert', (data) => {
                const params = Array.isArray(data) ? data[0] : data;
                alert(params.message);
            });
        });
    </script>
</body>
</html>
<?php /**PATH C:\laragon\www\Kopma\resources\views/layouts/pos.blade.php ENDPATH**/ ?>