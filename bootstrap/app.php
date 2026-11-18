<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Register API Key verification & Admin middleware aliases
        $middleware->alias([
            'api.key' => \App\Http\Middleware\EnsureApiKeyIsValid::class,
            'admin' => \App\Http\Middleware\EnsureUserIsAdmin::class,
        ]);

        // Exempt API, v1, and auth routes from CSRF verification
        $middleware->validateCsrfTokens(except: [
            'v1/*',
            'v1/auth/*',
            'api/*',
            'auth/*',
            'upload/*',
            'upload-image',
            'website/*',
            'website',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->is('v1/*') || $request->is('auth/*') || $request->is('upload/*') || $request->is('upload-image') || $request->is('website/*') || $request->is('website') || $request->expectsJson(),
        );
    })->create();
