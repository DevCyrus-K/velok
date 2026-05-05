<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Providers\RouteServiceProvider;
use App\Support\EmailVerificationCode;
use App\Support\TopbarData;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthenticatedSessionController extends Controller
{
    public function __construct(
        private readonly TopbarData $topbarData,
        private readonly EmailVerificationCode $verificationCode,
    ) {
    }

    /**
     * Display the login view.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     *
     * @param  \App\Http\Requests\Auth\LoginRequest  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(LoginRequest $request)
    {
        $request->authenticate();

        $request->session()->regenerate();
        
        // Store user data in session to avoid repeated database queries
        $user = Auth::user();
        $request->session()->put('user_name', $user->name);
        $request->session()->put('user_email', $user->email);
        $request->session()->put('user_id', $user->id);
        $request->session()->put('user_avatar', $this->topbarData->avatarUrl($user));

        if (! $user->hasVerifiedEmail()) {
            $this->verificationCode->send($user);

            return redirect()->route('verification.notice')
                ->with('toast-info', 'Login successful. Enter the 5-digit code we sent to your email.');
        }

        $request->session()->flash('toast-success', 'Login successful');

        return redirect()->intended(RouteServiceProvider::HOME);
    }

    /**
     * Destroy an authenticated session.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Request $request)
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();
        $request->session()->flash('toast-success', 'Logged out successfully');

        return redirect()->route('login');
    }
}
