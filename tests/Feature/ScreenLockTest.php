<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('locks the session and requires the current password before returning to the intended page', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('lock-screen.lock'))
        ->assertRedirect(route('lock-screen'))
        ->assertSessionHas('auth.screen_locked_at');

    $this->get(route('messages.index'))
        ->assertRedirect(route('lock-screen'));

    $this->get(route('lock-screen'))
        ->assertOk()
        ->assertSee('Enter your password to continue.');

    $this->post(route('lock-screen.unlock'), [
        'password' => 'wrong-password',
    ])
        ->assertSessionHasErrors('password')
        ->assertSessionHas('auth.screen_locked_at');

    $this->post(route('lock-screen.unlock'), [
        'password' => 'password',
    ])
        ->assertRedirect(route('messages.index'))
        ->assertSessionMissing('auth.screen_locked_at');

    $this->get(route('messages.index'))->assertOk();
});

it('returns a lock redirect for ajax requests while the screen is locked', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->postJson(route('lock-screen.lock'))
        ->assertOk()
        ->assertJson([
            'message' => 'Screen locked.',
            'redirect' => route('lock-screen'),
        ])
        ->assertSessionHas('auth.screen_locked_at');

    $this->getJson(route('topbar.data'))
        ->assertStatus(423)
        ->assertJson([
            'message' => 'Your screen is locked. Enter your password to continue.',
            'redirect' => route('lock-screen'),
        ]);
});
