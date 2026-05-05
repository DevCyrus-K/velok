<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\EmailVerificationCode;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PasswordResetCodeController extends Controller
{
    private const INVALID_CODE_MESSAGE = 'Invalid or expired code. Please try again.';
    private const EXPIRED_RESET_MESSAGE = 'This password reset session has expired. Please request a new verification code.';
    private const INVALID_RESET_MESSAGE = 'This password reset session is invalid. Please request a new verification code.';
    private const SESSION_PREFIX = 'password_reset_token_';

    public function __construct(private readonly EmailVerificationCode $verificationCode)
    {
    }

    /**
     * Show the password reset code verification view.
     */
    public function show(Request $request)
    {
        $email = strtolower((string) $request->query('email', ''));

        if (! $email) {
            return redirect()->route('password.request');
        }

        return view('auth.verify-reset-code', [
            'email' => $email,
            'maskedEmail' => $this->maskEmail($email),
            'codeLength' => $this->verificationCode->digitsFor('password_reset'),
            'ttlMinutes' => $this->verificationCode->ttlMinutes(),
        ]);
    }

    /**
     * Verify the password reset code.
     */
    public function verify(Request $request)
    {
        $request->merge([
            'email' => strtolower((string) $request->input('email')),
            'code' => preg_replace('/\D+/', '', (string) $request->input('code')),
        ]);

        $request->validate([
            'email' => 'required|email',
            'code' => 'required|digits:' . $this->verificationCode->digitsFor('password_reset'),
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user || ! $this->verificationCode->verify($user, $request->code, 'password_reset')) {
            return back()
                ->withInput($request->only('email'))
                ->withErrors(['code' => self::INVALID_CODE_MESSAGE])
                ->with('toast-error', self::INVALID_CODE_MESSAGE);
        }

        $codeToken = Str::random(64);
        $request->session()->put($this->sessionKey($codeToken), [
            'email' => $user->email,
            'verified_at' => now()->timestamp,
            'expires_at' => now()->addMinutes($this->verificationCode->ttlMinutes())->timestamp,
        ]);

        return redirect()->route('password.reset-form', ['token' => $codeToken]);
    }

    /**
     * Show the password reset form after code verification.
     */
    public function resetForm(Request $request)
    {
        $token = $request->route('token');
        $sessionData = $this->getValidResetSession($request, (string) $token);

        if (! $sessionData) {
            return redirect()->route('password.request')->withErrors(['email' => self::EXPIRED_RESET_MESSAGE]);
        }

        return view('auth.reset-password-new', [
            'email' => $sessionData['email'],
            'codeToken' => $token,
        ]);
    }

    /**
     * Handle the password reset confirmation.
     */
    public function resetConfirm(Request $request)
    {
        $request->merge([
            'email' => strtolower((string) $request->input('email')),
        ]);

        $request->validate([
            'email' => 'required|email',
            'code_token' => 'required|string',
            'password' => 'required|string|confirmed|min:8',
        ]);

        $token = (string) $request->code_token;
        $sessionData = $this->getValidResetSession($request, $token);

        if (! $sessionData) {
            return redirect()->route('password.request')->withErrors(['email' => self::EXPIRED_RESET_MESSAGE]);
        }

        if (strcasecmp((string) $sessionData['email'], (string) $request->email) !== 0) {
            $request->session()->forget($this->sessionKey($token));

            return redirect()->route('password.request')->withErrors(['email' => self::INVALID_RESET_MESSAGE]);
        }

        $user = User::where('email', $sessionData['email'])->first();

        if (! $user) {
            $request->session()->forget($this->sessionKey($token));

            return redirect()->route('password.request')->withErrors(['email' => self::INVALID_RESET_MESSAGE]);
        }

        $user->forceFill([
            'password' => $request->password,
            'remember_token' => Str::random(60),
        ])->save();

        event(new PasswordReset($user));

        $request->session()->forget($this->sessionKey($token));

        return response()->view('auth.reset-password-success', [
            'redirectUrl' => route('login'),
            'redirectDelayMs' => 2500,
        ]);
    }

    private function getValidResetSession(Request $request, string $token): ?array
    {
        $sessionData = $request->session()->get($this->sessionKey($token));

        if (! is_array($sessionData)) {
            return null;
        }

        if ((int) ($sessionData['expires_at'] ?? 0) <= now()->timestamp) {
            $request->session()->forget($this->sessionKey($token));

            return null;
        }

        return $sessionData;
    }

    private function sessionKey(string $token): string
    {
        return self::SESSION_PREFIX . $token;
    }

    private function maskEmail(string $email): string
    {
        [$name, $domain] = array_pad(explode('@', $email, 2), 2, '');

        if ($name === '' || $domain === '') {
            return $email;
        }

        $visible = substr($name, 0, min(2, strlen($name)));

        return $visible . str_repeat('*', max(strlen($name) - strlen($visible), 1)) . '@' . $domain;
    }
}
