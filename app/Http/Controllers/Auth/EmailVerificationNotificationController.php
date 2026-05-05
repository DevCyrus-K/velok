<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use App\Support\EmailVerificationCode;
use Illuminate\Http\Request;

class EmailVerificationNotificationController extends Controller
{
    public function __construct(private readonly EmailVerificationCode $verificationCode)
    {
    }

    /**
     * Send a new email verification notification.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->intended(RouteServiceProvider::HOME)
                ->with('toast-info', 'Your email is already verified.');
        }

        $this->verificationCode->send($request->user());

        return back()->with('toast-success', 'A new 5-digit verification code has been sent to your email.');
    }
}
