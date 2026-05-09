<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureScreenIsUnlocked
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user() || ! $request->session()->has('auth.screen_locked_at')) {
            return $next($request);
        }

        if ($request->routeIs('lock-screen', 'lock-screen.lock', 'lock-screen.unlock', 'logout')) {
            return $next($request);
        }

        if ($request->isMethod('GET')) {
            $request->session()->put('url.intended', $request->fullUrl());
        }

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Your screen is locked. Enter your password to continue.',
                'redirect' => route('lock-screen'),
            ], 423);
        }

        return redirect()
            ->route('lock-screen')
            ->with('toast-info', 'Your screen is locked. Enter your password to continue.');
    }
}
