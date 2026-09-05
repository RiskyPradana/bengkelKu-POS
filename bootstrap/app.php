<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Route;
use Spatie\Permission\Middleware\PermissionMiddleware;
use Spatie\Permission\Middleware\RoleMiddleware;
use Spatie\Permission\Middleware\RoleOrPermissionMiddleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function (): void {
            Route::middleware('web')->group(base_path('routes/filament.php'));
            Route::middleware('web')->group(base_path('routes/pwa.php'));
            // Modul 7: pindah dari middleware 'api' ke 'web' supaya endpoint sync
            // bisa memakai sesi login mekanik (Sanctum belum terpasang di project ini).
            Route::middleware('web')->group(base_path('routes/sync.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role' => RoleMiddleware::class,
            'permission' => PermissionMiddleware::class,
            'role_or_permission' => RoleOrPermissionMiddleware::class,
        ]);

        // Modul 7: Hybrid Offline Sync — endpoint ini dipanggil oleh Service
        // Worker (Background Sync) di background, yang tidak selalu membawa
        // token CSRF terbaru. Keamanan tetap dijaga lewat middleware `auth`
        // (harus login dengan sesi yang sama) pada routes/sync.php.
        $middleware->validateCsrfTokens(except: [
            'api/sync/push',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Centralized exception customization will be added here.
    })
    ->create();
