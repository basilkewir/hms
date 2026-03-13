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
    ->withSchedule(function (\Illuminate\Console\Scheduling\Schedule $schedule): void {
        // Every morning at 06:00 – mark all occupied rooms dirty and generate cleaning tasks.
        // This ensures housekeeping staff always see dirty rooms for every occupied room.
        $schedule->command('housekeeping:generate-daily-tasks')
                 ->dailyAt('06:00')
                 ->withoutOverlapping()
                 ->appendOutputTo(storage_path('logs/housekeeping-daily.log'));
    })
    ->withMiddleware(function (Middleware $middleware): void {
        // CORS for public booking API (must run before auth middleware)
        $middleware->api(prepend: [
            \Illuminate\Http\Middleware\HandleCors::class,
        ]);

        $middleware->web(append: [
            \App\Http\Middleware\HandleInertiaRequests::class,
            \App\Http\Middleware\ShareSettings::class,
            \App\Http\Middleware\ShareUserPermissions::class,
            \App\Http\Middleware\CheckLicense::class,
            \Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets::class,
        ]);

        // Register Spatie Permission middleware
        $middleware->alias([
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
            'admin_or_permission' => \App\Http\Middleware\EnsureUserHasRoleOrPermission::class,
            'check_permission' => \App\Http\Middleware\CheckPermission::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Handle 403 Authorization Exceptions
        $exceptions->render(function (\Illuminate\Auth\Access\AuthorizationException $e, $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'You do not have permission to perform this action.',
                    'error' => 'Forbidden'
                ], 403);
            }

            return \Inertia\Inertia::render('Errors/403', [
                'message' => $e->getMessage() ?: 'You do not have permission to access this page.',
                'status' => 403
            ])->toResponse($request)->setStatusCode(403);
        });

        $exceptions->render(function (\Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException $e, $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'You do not have permission to perform this action.',
                    'error' => 'Forbidden'
                ], 403);
            }

            return \Inertia\Inertia::render('Errors/403', [
                'message' => $e->getMessage() ?: 'You do not have permission to access this page.',
                'status' => 403
            ])->toResponse($request)->setStatusCode(403);
        });
    })->create();
