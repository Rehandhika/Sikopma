<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Spatie\Permission\Middleware\PermissionMiddleware;
use Spatie\Permission\Middleware\RoleMiddleware;
use Spatie\Permission\Middleware\RoleOrPermissionMiddleware;
use App\Providers\RepositoryServiceProvider;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Spatie Permission middleware aliases
        $middleware->alias([
            'role' => RoleMiddleware::class,
            'permission' => PermissionMiddleware::class,
            'role_or_permission' => RoleOrPermissionMiddleware::class,
        ]);

        // Global middleware for security
        $middleware->append([
            \App\Http\Middleware\SecurityHeaders::class,
        ]);

        // Web middleware group
        $middleware->group('web', [
            \App\Http\Middleware\SanitizeInput::class,
        ]);

        // Rate limiting for sensitive routes
        $middleware->group('throttle-sensitive', [
            'throttle:10,1', // 10 requests per minute
        ]);

        $middleware->group('throttle-api', [
            'throttle:60,1', // 60 requests per minute
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
