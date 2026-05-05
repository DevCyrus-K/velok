<?php

use App\Models\Message;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function messagePayload(array $overrides = []): array
{
    return array_merge([
        'name' => 'Buyer Lead',
        'email' => 'buyer@example.com',
        'phone' => '+254700000001',
        'subject' => 'Listing inquiry',
        'message' => 'I would like to know if this property is still available.',
        'status' => 'unread',
        'origin_page' => '/properties/for-sale',
    ], $overrides);
}

it('renders the messages pages without missing route errors', function () {
    $user = User::factory()->create();
    $message = Message::query()->create(messagePayload());

    $this->actingAs($user)
        ->get(route('messages.index'))
        ->assertOk()
        ->assertSee('Listing inquiry');

    $this->actingAs($user)
        ->get(route('messages.compose'))
        ->assertOk()
        ->assertSee('Compose');

    $this->actingAs($user)
        ->get(route('messages.show', $message))
        ->assertOk()
        ->assertSee('Buyer Lead');
});

it('deletes a message from the inbox', function () {
    $user = User::factory()->create();
    $message = Message::query()->create(messagePayload());

    $this->actingAs($user)
        ->delete(route('messages.destroy', $message))
        ->assertRedirect(route('messages.index'));

    $this->assertDatabaseMissing('messages', [
        'id' => $message->id,
    ]);
});
