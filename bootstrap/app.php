<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpKernel\Exception\HttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->web(append: [
            \Illuminate\Session\Middleware\AuthenticateSession::class,
            \App\Http\Middleware\EnsureScreenIsUnlocked::class,
            \App\Http\Middleware\ContentSecurityPolicy::class,
        ]);

        $middleware->api(append: [
            'throttle:api',
        ]);

        $middleware->alias([
            'admin' => \App\Http\Middleware\EnsureAdmin::class,
            'otp.session' => \App\Http\Middleware\EnsureOtpSession::class,
            'block.suspicious.login.ip' => \App\Http\Middleware\BlockSuspiciousLoginIp::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->report(function (Throwable $e): void {
            // Production hardening: every unhandled exception is logged with full context.
            Log::error('Unhandled application exception', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        });

        $exceptions->render(function (Throwable $e, $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                $statusCode = $e instanceof HttpException ? $e->getStatusCode() : 500;

                return response()->json([
                    'message' => $statusCode >= 500
                        ? 'Something went wrong. Please try again.'
                        : ($e->getMessage() ?: 'Request could not be completed.'),
                ], $statusCode);
            }

            if ($e instanceof HttpException) {
                $statusCode = $e->getStatusCode();
                
                if ($statusCode === 404) {
                    return response()->view('errors.404', [], 404);
                }
                
                if ($statusCode === 403) {
                    return response()->view('errors.403', [], 403);
                }
                
                if ($statusCode === 500) {
                    return response()->view('errors.500', [], 500);
                }
                
                if ($statusCode === 503) {
                    return response()->view('errors.503', [], 503);
                }
            }
            
            return null;
        });
    })->create();
