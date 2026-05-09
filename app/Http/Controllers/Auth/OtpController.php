<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use App\Support\AuthSession;
use App\Support\EmailVerificationCode;
use App\Support\TwoFactorOtp;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OtpController extends Controller
{
    private const SESSION_KEYS = [
        'otp_user_id',
        'otp_remember',
        'otp_last_sent_at',
        'otp_resend_count',
        'otp_verify_attempts',
    ];

    public function __construct(
        private readonly AuthSession $authSession,
        private readonly EmailVerificationCode $verificationCode,
        private readonly TwoFactorOtp $twoFactorOtp,
    ) {
    }

    public function show(Request $request): View
    {
        $user = $this->pendingUser($request);

        return view('auth.otp-verify', [
            'maskedEmail' => $this->maskEmail($user->email),
            'resendAvailableIn' => $this->resendAvailableIn($request),
            'resendCount' => (int) $request->session()->get('otp_resend_count', 0),
            'maxResends' => 3,
        ]);
    }

    public function verify(Request $request): RedirectResponse
    {
        $request->merge([
            'otp' => preg_replace('/\D+/', '', (string) $request->input('otp')),
        ]);

        $request->validate([
            'otp' => ['required', 'digits:6'],
        ]);

        if ((int) $request->session()->get('otp_verify_attempts', 0) >= $this->twoFactorOtp->maxAttempts()) {
            $this->clearPendingLogin($request);

            return redirect()->route('login')->with('toast-error', 'Too many attempts. Please login again.');
        }

        $user = $this->pendingUser($request);
        $status = $this->twoFactorOtp->verify($user, (string) $request->input('otp'));

        if ($status === TwoFactorOtp::STATUS_VALID) {
            $remember = (bool) $request->session()->get('otp_remember', false);
            $this->clearPendingLogin($request);
            $this->authSession->login($request, $user, $remember);

            if (! $user->hasVerifiedEmail()) {
                $this->verificationCode->send($user);

                return redirect()->route('verification.notice')
                    ->with('toast-info', 'Login successful. Enter the 5-digit code we sent to your email.');
            }

            return redirect()->intended(RouteServiceProvider::HOME)
                ->with('toast-success', 'Login successful');
        }

        if ($status === TwoFactorOtp::STATUS_EXPIRED || $status === TwoFactorOtp::STATUS_MISSING) {
            $this->clearPendingLogin($request);

            return redirect()->route('login')->with('toast-error', 'Code expired. Please login again.');
        }

        if ($status === TwoFactorOtp::STATUS_LOCKED) {
            $this->clearPendingLogin($request);

            return redirect()->route('login')->with('toast-error', 'Too many attempts. Please login again.');
        }

        $attempts = (int) $request->session()->get('otp_verify_attempts', 0) + 1;
        $request->session()->put('otp_verify_attempts', $attempts);

        if ($attempts >= $this->twoFactorOtp->maxAttempts()) {
            $this->twoFactorOtp->clear($user);
            $this->clearPendingLogin($request);

            return redirect()->route('login')->with('toast-error', 'Too many attempts. Please login again.');
        }

        return back()
            ->withInput()
            ->withErrors(['otp' => 'The verification code is invalid.'])
            ->with('toast-error', 'The verification code is invalid.');
    }

    public function resend(Request $request): RedirectResponse
    {
        $user = $this->pendingUser($request);
        $resendCount = (int) $request->session()->get('otp_resend_count', 0);

        if ($resendCount >= 3) {
            return back()
                ->withErrors(['otp' => 'You have reached the resend limit. Please login again.'])
                ->with('toast-error', 'You have reached the resend limit. Please login again.');
        }

        if ($this->resendAvailableIn($request) > 0) {
            return back()
                ->withErrors(['otp' => 'Please wait before requesting another code.'])
                ->with('toast-error', 'Please wait before requesting another code.');
        }

        $this->twoFactorOtp->send($user);

        $request->session()->put('otp_last_sent_at', now()->timestamp);
        $request->session()->put('otp_resend_count', $resendCount + 1);
        $request->session()->put('otp_verify_attempts', 0);

        return back()->with('toast-success', 'A new verification code has been sent to your email.');
    }

    private function pendingUser(Request $request): User
    {
        $user = User::query()->find($request->session()->get('otp_user_id'));

        if (! $user) {
            $this->clearPendingLogin($request);

            throw new HttpResponseException(
                redirect()->route('login')->with('toast-error', 'Please login again.')
            );
        }

        return $user;
    }

    private function clearPendingLogin(Request $request): void
    {
        $request->session()->forget(self::SESSION_KEYS);
    }

    private function resendAvailableIn(Request $request): int
    {
        $lastSentAt = (int) $request->session()->get('otp_last_sent_at', 0);

        if (! $lastSentAt) {
            return 0;
        }

        return max(0, 60 - (now()->timestamp - $lastSentAt));
    }

    private function maskEmail(string $email): string
    {
        [$name, $domain] = array_pad(explode('@', $email, 2), 2, '');

        if ($name === '' || $domain === '') {
            return $email;
        }

        $visible = substr($name, 0, min(2, strlen($name)));

        return $visible.str_repeat('*', max(strlen($name) - strlen($visible), 1)).'@'.$domain;
    }
}
