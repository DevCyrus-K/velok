<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class ScreenLockController extends Controller
{
    public function show(Request $request)
    {
        $this->rememberIntendedUrl($request);
        $this->lockSession($request);

        return view('auth.lock-screen', [
            'lockedUser' => $request->user(),
        ]);
    }

    public function lock(Request $request)
    {
        $this->rememberIntendedUrl($request);
        $this->lockSession($request);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Screen locked.',
                'redirect' => route('lock-screen'),
            ]);
        }

        return redirect()
            ->route('lock-screen')
            ->with('toast-info', 'Screen locked. Enter your password to continue.');
    }

    public function unlock(Request $request)
    {
        $request->validate([
            'password' => ['required', 'string'],
        ]);

        $user = $request->user();

        if (! $user || ! Auth::guard('web')->validate([
            'email' => $user->email,
            'password' => $request->input('password'),
        ])) {
            throw ValidationException::withMessages([
                'password' => __('auth.password'),
            ]);
        }

        $request->session()->forget('auth.screen_locked_at');
        $request->session()->put('auth.password_confirmed_at', time());

        return redirect()
            ->intended(RouteServiceProvider::HOME)
            ->with('toast-success', 'Welcome back.');
    }

    private function lockSession(Request $request): void
    {
        $request->session()->put('auth.screen_locked_at', now()->timestamp);
    }

    private function rememberIntendedUrl(Request $request): void
    {
        $previous = url()->previous();

        if (! is_string($previous) || $previous === '' || $previous === $request->fullUrl()) {
            return;
        }

        if (! str_starts_with($previous, $request->getSchemeAndHttpHost())) {
            return;
        }

        $path = trim((string) parse_url($previous, PHP_URL_PATH), '/');

        if (in_array($path, ['lock-screen', 'lock-screen/unlock', 'auth/lock-screen', 'logout'], true)) {
            return;
        }

        $request->session()->put('url.intended', $previous);
    }
}
