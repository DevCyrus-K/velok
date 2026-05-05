<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

it('always redirects to the verify code screen with a neutral message', function () {
    $user = User::factory()->create([
        'email' => 'known@example.com',
    ]);

    $neutralMessage = "If this email is registered with us, we'll send a verification code to it shortly.";

    $this->post(route('password.email'), [
        'email' => $user->email,
    ])
        ->assertRedirect(route('password.verify-code-form', ['email' => $user->email]))
        ->assertSessionHas('toast-success', $neutralMessage);

    expect(Cache::has(passwordResetCacheKey($user)))->toBeTrue();

    $this->post(route('password.email'), [
        'email' => 'missing@example.com',
    ])
        ->assertRedirect(route('password.verify-code-form', ['email' => 'missing@example.com']))
        ->assertSessionHas('toast-success', $neutralMessage);
});

it('shows the same invalid code message for unknown emails and wrong codes', function () {
    $user = User::factory()->create([
        'email' => 'known@example.com',
    ]);

    Cache::put(passwordResetCacheKey($user), [
        'hash' => Hash::make('123456'),
        'type' => 'password_reset',
    ], now()->addMinutes(10));

    $this->from(route('password.verify-code-form', ['email' => 'missing@example.com']))
        ->post(route('password.verify-code'), [
            'email' => 'missing@example.com',
            'code' => '123456',
        ])
        ->assertRedirect(route('password.verify-code-form', ['email' => 'missing@example.com']))
        ->assertSessionHasErrors([
            'code' => 'Invalid or expired code. Please try again.',
        ])
        ->assertSessionDoesntHaveErrors('email');

    $this->from(route('password.verify-code-form', ['email' => $user->email]))
        ->post(route('password.verify-code'), [
            'email' => $user->email,
            'code' => '654321',
        ])
        ->assertRedirect(route('password.verify-code-form', ['email' => $user->email]))
        ->assertSessionHasErrors([
            'code' => 'Invalid or expired code. Please try again.',
        ])
        ->assertSessionDoesntHaveErrors('email');
});

it('resets the password after a valid 6 digit code and invalidates the code after use', function () {
    $user = User::factory()->create([
        'email' => 'known@example.com',
        'password' => 'old-password',
    ]);

    Cache::put(passwordResetCacheKey($user), [
        'hash' => Hash::make('123456'),
        'type' => 'password_reset',
    ], now()->addMinutes(10));

    $verificationResponse = $this->post(route('password.verify-code'), [
        'email' => $user->email,
        'code' => '123456',
    ]);

    $verificationResponse->assertRedirect();

    $resetFormUrl = $verificationResponse->headers->get('Location');

    expect($resetFormUrl)->not->toBeNull();

    $codeToken = basename((string) parse_url($resetFormUrl, PHP_URL_PATH));

    $this->get($resetFormUrl)
        ->assertOk()
        ->assertSee('Set a New Password');

    $this->post(route('password.reset-confirmed'), [
        'email' => $user->email,
        'code_token' => $codeToken,
        'password' => 'new-password',
        'password_confirmation' => 'new-password',
    ])
        ->assertOk()
        ->assertSee('Password reset successful!');

    expect(Hash::check('new-password', $user->fresh()->password))->toBeTrue();
    expect(Cache::has(passwordResetCacheKey($user)))->toBeFalse();

    $this->from(route('password.verify-code-form', ['email' => $user->email]))
        ->post(route('password.verify-code'), [
            'email' => $user->email,
            'code' => '123456',
        ])
        ->assertRedirect(route('password.verify-code-form', ['email' => $user->email]))
        ->assertSessionHasErrors([
            'code' => 'Invalid or expired code. Please try again.',
        ]);
});

it('expires the reset session after ten minutes', function () {
    $user = User::factory()->create([
        'email' => 'known@example.com',
    ]);

    Cache::put(passwordResetCacheKey($user), [
        'hash' => Hash::make('123456'),
        'type' => 'password_reset',
    ], now()->addMinutes(10));

    $verificationResponse = $this->post(route('password.verify-code'), [
        'email' => $user->email,
        'code' => '123456',
    ]);

    $resetFormUrl = $verificationResponse->headers->get('Location');

    $this->travel(11)->minutes();

    $this->get($resetFormUrl)
        ->assertRedirect(route('password.request'))
        ->assertSessionHasErrors([
            'email' => 'This password reset session has expired. Please request a new verification code.',
        ]);
});

function passwordResetCacheKey(User $user): string
{
    return 'auth.email_verification_code.' . $user->getKey() . '.password_reset';
}
