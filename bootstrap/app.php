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
    ->withMiddleware(function (Middleware $middleware) {
        //
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (Throwable $e) {
            if ($e instanceof \Symfony\Component\HttpKernel\Exception\HttpException) {
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
            
            // For general exceptions, show 500 error page
            if (!($e instanceof \Symfony\Component\HttpKernel\Exception\HttpException)) {
                return response()->view('errors.500', [], 500);
            }
        });
    })->create();
