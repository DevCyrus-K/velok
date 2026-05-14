<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ContentSecurityPolicy
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $response->headers->set('Content-Security-Policy', implode('; ', [
            "default-src 'self'",
            "script-src 'self' 'unsafe-inline' http://localhost:* http://127.0.0.1:*",
            "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com http://localhost:* http://127.0.0.1:*",
            "font-src 'self' data: https://fonts.gstatic.com",
            "img-src 'self' data: blob: https://res.cloudinary.com",
            "connect-src 'self' https://nominatim.openstreetmap.org http://localhost:* http://127.0.0.1:* ws://localhost:* ws://127.0.0.1:*",
            "object-src 'none'",
            "base-uri 'self'",
            "frame-ancestors 'self'",
            "form-action 'self'",
        ]));

        return $response;
    }
}
