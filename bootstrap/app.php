<?php

use App\Http\Middleware\LocaleMiddleware;
use App\Http\Middleware\VerificarGrupo;
use App\Http\Middleware\VerificarReporteReunion;
use App\Http\Middleware\VerificarReunion;
use App\Http\Middleware\VerificarUsuario;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->web(LocaleMiddleware::class);

        $middleware->alias([
            'verificarGrupo' => VerificarGrupo::class,
            'verificarUsuario' => VerificarUsuario::class,
            'verificarReunion' => VerificarReunion::class,
            'verificarReporteReunion' => VerificarReporteReunion::class,
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,

        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
