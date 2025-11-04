<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'SIKOPMA') }} - @yield('title', 'Auth')</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="bg-gray-50 font-sans antialiased min-h-screen flex items-center justify-center p-6">
    <main class="w-full max-w-md">
        @yield('content')
        {{ $slot ?? '' }}
    </main>

    @livewireScripts
    @stack('scripts')
</body>
</html>
