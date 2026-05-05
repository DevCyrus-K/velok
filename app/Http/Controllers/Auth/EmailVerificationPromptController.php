<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use App\Support\EmailVerificationCode;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\Request;

class EmailVerificationPromptController extends Controller
{
    public function __construct(private readonly EmailVerificationCode $verificationCode)
    {
    }

    /**
     * Display the email verification prompt.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return mixed
     */
    public function __invoke(Request $request)
    {
        return $request->user()->hasVerifiedEmail()
                    ? redirect()->intended(RouteServiceProvider::HOME)->with('toast-info', 'Your email is already verified.')
                    : $this->showPrompt($request);
    }

    public function verify(Request $request)
    {
        $request->merge([
            'code' => preg_replace('/\D+/', '', (string) $request->input('code')),
        ]);

        $request->validate([
            'code' => 'required|digits:5',
        ]);

        $user = $request->user();

        if (! $this->verificationCode->verify($user, (string) $request->input('code'))) {
            return back()
                ->withInput()
                ->withErrors(['code' => 'The verification code is invalid or has expired.'])
                ->with('toast-error', 'The verification code is invalid or has expired.');
        }

        if (! $user->hasVerifiedEmail() && $user->markEmailAsVerified()) {
            event(new Verified($user));
        }

        return redirect()->intended(RouteServiceProvider::HOME)
            ->with('toast-success', 'Signup successful. Your email has been verified.');
    }

    private function showPrompt(Request $request)
    {
        $user = $request->user();

        if (! $this->verificationCode->hasPending($user)) {
            $this->verificationCode->send($user);
        }

        return view('auth.verify-email', [
            'maskedEmail' => $this->maskEmail($user->email),
            'ttlMinutes' => $this->verificationCode->ttlMinutes(),
        ]);
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
