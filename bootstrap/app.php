<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'role' => \App\Http\Middleware\RoleMiddleware::class,
            'school.bot' => \App\Http\Middleware\EnsureSchoolBotSecret::class,
            'super_admin' => \App\Http\Middleware\SuperAdminMiddleware::class,
            'school.admin' => \App\Http\Middleware\EnsureSchoolAdmin::class,
            'school.active' => \App\Http\Middleware\EnsureSchoolIsActive::class,
        ]);
        
        $middleware->append(\App\Http\Middleware\TrustProxies::class);

        $middleware->web(append: [
            \App\Http\Middleware\SyncTenantSchoolSession::class,
            \App\Http\Middleware\SecurityHeaders::class,
            \App\Http\Middleware\SanitizeInput::class,
        ]);

        $middleware->api(append: [
            \App\Http\Middleware\SanitizeInput::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        if (app()->isProduction()) {
            $exceptions->render(function (\Throwable $e, Request $request) {
                if ($e instanceof HttpException) {
                    $status = $e->getStatusCode();
                    if (view()->exists("errors.{$status}")) {
                        return response()->view("errors.{$status}", [], $status);
                    }
                }
                return response()->view('errors.500', [], 500);
            });
        }
    })->create();
