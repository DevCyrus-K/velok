<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\AuthSession;
use App\Support\EmailVerificationCode;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

class RegisteredUserController extends Controller
{
    public function __construct(
        private readonly EmailVerificationCode $verificationCode,
        private readonly AuthSession $authSession,
    ) {
    }

    /**
     * Display the registration view.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('auth.signup');
    }

    /**
     * Handle an incoming registration request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:users,name',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => ['required', 'string', 'confirmed', Password::min(8)->letters()->mixedCase()->numbers()],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => Str::lower(trim((string) $request->email)),
            'password' => $request->password,
        ]);

        $this->authSession->login($request, $user, false, false);

        $this->verificationCode->send($user);

        return redirect()->route('verification.notice')
            ->with('toast-success', 'Signup successful. Enter the 5-digit code we sent to your email.');
    }
}
