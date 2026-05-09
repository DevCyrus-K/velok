<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

class BlockSuspiciousLoginIp
{
    public function handle(Request $request, Closure $next): Response
    {
        if (RateLimiter::tooManyAttempts('login-ip:'.$request->ip(), 10)) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Access temporarily blocked. Try later.',
                ], 429);
            }

            return response()->view('errors.429', [
                'message' => 'Access temporarily blocked. Try later.',
            ], 429);
        }

        return $next($request);
    }
}
