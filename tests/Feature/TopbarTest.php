<?php

use App\Models\Message;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function createUnreadMessage(int $index): void
{
    Message::query()->create([
        'name' => "Buyer {$index}",
        'email' => "buyer{$index}@example.com",
        'phone' => '+254700000001',
        'subject' => "Listing inquiry {$index}",
        'message' => 'I would like to know if this property is still available.',
        'status' => 'unread',
        'origin_page' => '/properties/for-sale',
    ]);
}

it('returns topbar data with a capped notification badge when unread messages exceed nine', function () {
    $user = User::factory()->create([
        'name' => 'Lead Manager',
    ]);

    foreach (range(1, 12) as $index) {
        createUnreadMessage($index);
    }

    $response = $this->actingAs($user)->getJson(route('topbar.data'));

    $response
        ->assertOk()
        ->assertJsonPath('user.name', 'Lead Manager')
        ->assertJsonPath('notifications.count', 12)
        ->assertJsonPath('notifications.display_count', '9+');

    expect($response->json('notifications.items'))->toHaveCount(5);
});

it('renders the authenticated name and the single notification badge immediately in the topbar', function () {
    $user = User::factory()->create([
        'name' => 'Closer Jane',
    ]);

    createUnreadMessage(1);

    $this->actingAs($user);

    $html = view('layouts.partials.topbar')->render();

    expect($html)->toContain('Closer Jane');
    expect($html)->toContain('>1<');
    expect($html)->not->toContain('Loading notifications...');
});
