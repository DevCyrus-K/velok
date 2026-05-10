<?php

$disabledSignupMessage = 'Contact admin. Signups are not allowed currently.';

it('redirects registration page requests to login with a disabled signup toast', function () use ($disabledSignupMessage) {
    $this->get(route('register'))
        ->assertRedirect(route('login'))
        ->assertSessionHas('toast-info', $disabledSignupMessage);
});

it('does not accept registration posts', function () use ($disabledSignupMessage) {
    $this->post(route('register'), [
        'name' => 'New User',
        'email' => 'new-user@example.com',
        'password' => 'Password1',
        'password_confirmation' => 'Password1',
    ])
        ->assertRedirect(route('login'))
        ->assertSessionHas('toast-info', $disabledSignupMessage);

    $this->assertGuest();
});
