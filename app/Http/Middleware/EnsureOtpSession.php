<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureOtpSession
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->session()->has('otp_user_id')) {
            return redirect()
                ->route('login')
                ->with('toast-error', 'Please login to request a verification code.');
        }

        return $next($request);
    }
}
