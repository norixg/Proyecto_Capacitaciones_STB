<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function ($middleware) {
        $middleware->alias([
            'active' => \App\Http\Middleware\EnsureUserIsActive::class,
            'password.changed' => \App\Http\Middleware\EnsurePasswordWasChanged::class,
            'rol' => \App\Http\Middleware\RolMiddleware::class,
            'capacitacion.instructor' => \App\Http\Middleware\AutorizaCapacitacionInstructor::class,

            'spatie_role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'spatie_permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'spatie_role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
        ]);
    })

    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
