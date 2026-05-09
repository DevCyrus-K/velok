<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\EmailVerificationCode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class PasswordResetLinkController extends Controller
{
    private const NEUTRAL_MESSAGE = "If this email is registered with us, we'll send a verification code to it shortly.";
    private const RATE_LIMIT_MESSAGE = 'Too many reset attempts. Check your email or try again later.';

    public function __construct(private readonly EmailVerificationCode $verificationCode)
    {
    }

    /**
     * Display the password reset link request view.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('auth.password');
    }

    /**
     * Handle an incoming password reset link request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $email = Str::lower(trim((string) $request->string('email')));

        $this->ensureIsNotRateLimited($email, $request->ip());

        RateLimiter::hit($this->emailThrottleKey($email), 3600);
        RateLimiter::hit($this->ipThrottleKey($request->ip()), 3600);

        $user = User::whereRaw('LOWER(email) = ?', [$email])->first();

        if ($user) {
            $this->verificationCode->send($user, 'password_reset');
        }

        return redirect()
            ->route('password.verify-code-form', ['email' => $email])
            ->with('toast-success', self::NEUTRAL_MESSAGE);
    }

    private function ensureIsNotRateLimited(string $email, string $ip): void
    {
        if (
            RateLimiter::tooManyAttempts($this->emailThrottleKey($email), 3)
            || RateLimiter::tooManyAttempts($this->ipThrottleKey($ip), 5)
        ) {
            throw ValidationException::withMessages([
                'email' => self::RATE_LIMIT_MESSAGE,
            ]);
        }
    }

    private function emailThrottleKey(string $email): string
    {
        return 'password-reset-email:'.$email;
    }

    private function ipThrottleKey(string $ip): string
    {
        return 'password-reset-ip:'.$ip;
    }
}
