<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            \App\Http\Middleware\HandleInertiaRequests::class,
            \Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets::class,
            \App\Http\Middleware\RegistrarVisita::class,
        ]);

        //AQUI ESTAMOS TRABAJANDO ESTOS ESTADOS DE PERMISOS CON SPATIE
        $middleware->alias([
            'permiso' => \Spatie\Permission\Middlewares\PermissionMiddleware::class,
            'rol' => \Spatie\Permission\Middlewares\RoleMiddleware::class,
            'rol_o_permiso' => \Spatie\Permission\Middlewares\RoleOrPermissionMiddleware::class,
        ]);

        // Excluir callback de PagoFácil de verificación CSRF (webhook externo)
        $middleware->validateCsrfTokens(except: [
            'cliente/pagos/callback',
        ]);

        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
