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
        // Bump last_seen_at on every web request so the header presence pill
        // reflects "Available" while the user is actively navigating.
        $middleware->web(append: [
            \App\Http\Middleware\TrackPresence::class,
        ]);

        $middleware->alias([
            'no.back'         => \App\Http\Middleware\PreventBackHistory::class,
            'force.pw.change' => \App\Http\Middleware\ForcePasswordChange::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
