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
        // Exclude external webhook endpoints from CSRF verification
        $middleware->validateCsrfTokens(except: [
            'webhooks/*',
            'checkout/flutterwave/return',
        ]);

        // Security headers + login tracking on every web response
        $middleware->web(append: [
            \App\Http\Middleware\SecurityHeaders::class,
            \App\Http\Middleware\TrackLastLogin::class,
        ]);

        // M-Pesa IP whitelisting — applied via route alias
        $middleware->alias([
            'mpesa.whitelist' => \App\Http\Middleware\MpesaIpWhitelist::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
