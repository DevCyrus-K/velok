<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Providers\RouteServiceProvider;
use App\Support\AuthSession;
use App\Support\EmailVerificationCode;
use App\Support\TwoFactorOtp;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthenticatedSessionController extends Controller
{
    public function __construct(
        private readonly AuthSession $authSession,
        private readonly EmailVerificationCode $verificationCode,
        private readonly TwoFactorOtp $twoFactorOtp,
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
        /** @var \App\Models\User $user */
        $user = $request->authenticate();
        $remember = $request->filled('remember');

        if ($user->two_factor_enabled) {
            $request->session()->regenerate();

            $this->twoFactorOtp->send($user);

            $request->session()->put([
                'otp_user_id' => $user->getAuthIdentifier(),
                'otp_remember' => $remember,
                'otp_last_sent_at' => now()->timestamp,
                'otp_resend_count' => 0,
                'otp_verify_attempts' => 0,
            ]);

            return redirect()->route('otp.verify')
                ->with('toast-info', 'Enter the 6-digit code we sent to your email.');
        }

        $this->authSession->login($request, $user, $remember);

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
