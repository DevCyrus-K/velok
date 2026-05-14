<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return $request->expectsJson()
                ? response()->json(['message' => 'Authentication required.'], 401)
                : redirect()->route('login');
        }

        // Production hardening: admin routes now require an explicit admin gate when the column exists.
        if (Schema::hasColumn('users', 'is_admin') && ! (bool) $user->getAttribute('is_admin')) {
            abort(403, 'This area is restricted to administrators.');
        }

        return $next($request);
    }
}
