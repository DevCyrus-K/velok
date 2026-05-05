<?php

use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('authenticates only when the email and password match', function () {
    $user = User::factory()->create([
        'name' => 'Velok Admin',
        'email' => 'admin@example.com',
    ]);

    $this->post(route('login'), [
        'email' => 'wrong@example.com',
        'password' => 'password',
    ])->assertSessionHasErrors('email');

    $this->assertGuest();

    $this->post(route('login'), [
        'email' => 'admin@example.com',
        'password' => 'password',
    ])->assertRedirect(RouteServiceProvider::HOME);

    $this->assertAuthenticatedAs($user);
});
