<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <title>{{ $title ?? 'POS' }} - {{ config('app.name') }}</title>
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    
    <style>
        input[type="number"]::-webkit-inner-spin-button,
        input[type="number"]::-webkit-outer-spin-button { -webkit-appearance: none; margin: 0; }
        input[type="number"] { -moz-appearance: textfield; }
    </style>
</head>
<body class="bg-gray-50 dark:bg-gray-900 antialiased">
    {{ $slot }}
    
    @livewireScripts
    
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
