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
    ->withMiddleware(function (Middleware $middleware): void {
        // Alias phase 2 : rôles CDC + compte actif (voir decoupage_phases_cahier_charge.md).
        $middleware->alias([
            'role' => \App\Http\Middleware\EnsureRole::class,
            'compte.actif' => \App\Http\Middleware\EnsureCompteActif::class,
        ]);

        // Utilisateur déjà connecté qui ouvre /login → tableau de bord.
        $middleware->redirectUsersTo(fn () => route('dashboard'));
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
