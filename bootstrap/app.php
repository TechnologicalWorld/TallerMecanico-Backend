<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        api: __DIR__.'/../routes/api.php',
        health: '/up',
        then: function () {
            // Registro de rutas API específicas para WorldTech
            Route::middleware('api')
                ->prefix('api')
                ->group(base_path('routes/api_taller_mecanico.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->append([
            \App\Http\Middleware\SetBranchTeam::class,
        ]);

        $middleware->alias([
            'branch.active' => \App\Http\Middleware\RequireActiveBranch::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
