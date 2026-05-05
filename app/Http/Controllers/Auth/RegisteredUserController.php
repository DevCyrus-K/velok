<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\EmailVerificationCode;
use App\Support\TopbarData;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RegisteredUserController extends Controller
{
    public function __construct(
        private readonly EmailVerificationCode $verificationCode,
        private readonly TopbarData $topbarData,
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
            'password' => 'required|string|confirmed|min:8',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => $request->password,
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        $request->session()->put('user_name', $user->name);
        $request->session()->put('user_email', $user->email);
        $request->session()->put('user_id', $user->id);
        $request->session()->put('user_avatar', $this->topbarData->avatarUrl($user));

        $this->verificationCode->send($user);

        return redirect()->route('verification.notice')
            ->with('toast-success', 'Signup successful. Enter the 5-digit code we sent to your email.');
    }
}
