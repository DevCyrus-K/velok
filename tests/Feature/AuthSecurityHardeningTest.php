<?php

use App\Mail\AccountLockedMail;
use App\Mail\OtpMail;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Mail;

uses(RefreshDatabase::class);

beforeEach(function () {
    Cache::flush();
});

it('requires email OTP before authenticating users with two factor enabled', function () {
    Mail::fake();

    $user = User::factory()->create([
        'email' => 'secure@example.com',
        'two_factor_enabled' => true,
    ]);

    $this->post(route('login'), [
        'email' => $user->email,
        'password' => 'password',
    ])
        ->assertRedirect(route('otp.verify'))
        ->assertSessionHas('otp_user_id', $user->id);

    $this->assertGuest();
    Mail::assertSent(OtpMail::class);

    $otp = (string) Crypt::decrypt($user->fresh()->otp_code);

    $this->post(route('otp.verify.store'), [
        'otp' => $otp,
    ])->assertRedirect(RouteServiceProvider::HOME);

    $this->assertAuthenticatedAs($user);

    $user->refresh();

    expect($user->otp_code)->toBeNull()
        ->and($user->last_login_at)->not->toBeNull()
        ->and($user->last_login_ip)->not->toBeNull();
});

it('locks an account key after five failed login attempts and sends a lock email', function () {
    Mail::fake();

    $user = User::factory()->create([
        'email' => 'locked@example.com',
    ]);

    foreach (range(1, 5) as $attempt) {
        $this->from(route('login'))->post(route('login'), [
            'email' => $user->email,
            'password' => 'wrong-password',
        ])->assertSessionHasErrors('email');
    }

    Mail::assertSent(AccountLockedMail::class);

    $this->from(route('login'))->post(route('login'), [
        'email' => $user->email,
        'password' => 'wrong-password',
    ])->assertSessionHasErrors([
        'email' => 'Too many login attempts. Please try again in 15 minutes.',
    ]);
});

it('temporarily blocks the login page after ten failed attempts from one IP', function () {
    foreach (range(1, 9) as $attempt) {
        $this->from(route('login'))->post(route('login'), [
            'email' => "missing{$attempt}@example.com",
            'password' => 'wrong-password',
        ])->assertSessionHasErrors('email');
    }

    $this->post(route('login'), [
        'email' => 'missing10@example.com',
        'password' => 'wrong-password',
    ])
        ->assertStatus(429)
        ->assertSee('Access temporarily blocked. Try later.');

    $this->get(route('login'))
        ->assertStatus(429)
        ->assertSee('Access temporarily blocked. Try later.');
});

it('limits password reset requests by email', function () {
    Mail::fake();

    $user = User::factory()->create([
        'email' => 'reset@example.com',
    ]);

    foreach (range(1, 3) as $attempt) {
        $this->post(route('password.email'), [
            'email' => $user->email,
        ])->assertRedirect(route('password.verify-code-form', ['email' => $user->email]));
    }

    $this->from(route('password.request'))->post(route('password.email'), [
        'email' => $user->email,
    ])->assertSessionHasErrors([
        'email' => 'Too many reset attempts. Check your email or try again later.',
    ]);
});
