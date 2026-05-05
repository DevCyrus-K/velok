<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\EmailVerificationCode;
use Illuminate\Http\Request;

class PasswordResetLinkController extends Controller
{
    private const NEUTRAL_MESSAGE = "If this email is registered with us, we'll send a verification code to it shortly.";

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

        $email = strtolower((string) $request->string('email'));
        $user = User::where('email', $email)->first();

        if ($user) {
            $this->verificationCode->send($user, 'password_reset');
        }

        return redirect()
            ->route('password.verify-code-form', ['email' => $email])
            ->with('toast-success', self::NEUTRAL_MESSAGE);
    }
}
