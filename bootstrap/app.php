<?php

use App\Http\Middleware\HandleInertiaRequests;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

$app = Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->web(append: [
            HandleInertiaRequests::class,
        ]);

        $middleware->trustProxies([
            '*',
            Request::HEADER_X_FORWARDED_FOR |
            Request::HEADER_X_FORWARDED_HOST |
            Request::HEADER_X_FORWARDED_PORT |
            Request::HEADER_X_FORWARDED_PROTO,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })
    ->create();

if (getenv('VERCEL')) {
    $storagePath = sys_get_temp_dir() . '/laravel';

    $app->useStoragePath($storagePath);

    foreach ([
        $storagePath . '/app/private',
        $storagePath . '/app/public',
        $storagePath . '/framework/cache/data',
        $storagePath . '/framework/sessions',
        $storagePath . '/framework/views',
        $storagePath . '/logs',
    ] as $path) {
        if (! is_dir($path)) {
            mkdir($path, 0777, true);
        }
    }
}

return $app;
