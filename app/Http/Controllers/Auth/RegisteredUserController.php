<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;

class RegisteredUserController extends Controller
{
    private const SIGNUPS_DISABLED_MESSAGE = 'Contact admin. Signups are not allowed currently.';

    /**
     * Display the registration view.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function create()
    {
        return redirect()->route('login')
            ->with('toast-info', self::SIGNUPS_DISABLED_MESSAGE);
    }

    /**
     * Handle an incoming registration request.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store()
    {
        return redirect()->route('login')
            ->with('toast-info', self::SIGNUPS_DISABLED_MESSAGE);
    }
}
