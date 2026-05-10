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
        ->assertJsonPath('user.initials', 'LM')
        ->assertJsonPath('user.has_avatar', false)
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
    expect($html)->toContain('id="user-avatar-initials"');
    expect($html)->toContain('>CJ<');
    expect($html)->toContain('>1<');
    expect($html)->not->toContain('Loading notifications...');
});

it('renders the cleaned navigation search and menu labels', function () {
    $user = User::factory()->create([
        'name' => 'Navigation Manager',
    ]);

    $this->actingAs($user);

    $topbar = view('layouts.partials.topbar')->render();
    $sidebar = view('layouts.partials.main-nav')->render();

    expect($topbar)->toContain('id="topbar-search-input"');
    expect($topbar)->toContain('Search pages...');
    expect($topbar)->toContain('button-toggle-menu');
    expect($topbar)->toContain('aria-controls="navbar-nav"');
    expect($topbar)->toContain('Help Center');
    expect($topbar)->toContain('<span class="align-middle">Settings</span>');
    expect($topbar)->not->toContain('<span class="align-middle">Help</span>');
    expect($topbar)->not->toContain('Gallery');
    expect($topbar)->not->toContain('Photos');
    expect($topbar)->not->toContain('Pricing');

    expect($sidebar)->toContain('Sales');
    expect($sidebar)->toContain('Content');
    expect($sidebar)->toContain('Insights');
    expect($sidebar)->toContain('Workspace');
    expect($sidebar)->toContain('Gallery');
    expect($sidebar)->not->toContain('Components');
    expect($sidebar)->not->toContain('Base UI');
    expect($sidebar)->not->toContain('Pricing');
});
