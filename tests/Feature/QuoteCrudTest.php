<?php

use App\Models\QuoteRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
});

function quotePayload(array $overrides = []): array
{
    return array_merge([
        'full_name' => 'Jane Doe',
        'email' => 'jane@example.com',
        'phone' => '+254700123456',
        'moving_from' => 'Nairobi',
        'moving_to' => 'Mombasa',
        'move_date' => '2026-05-14',
        'service_type' => 'Residential Relocation',
        'move_size' => '2 bedroom house',
        'additional_notes' => 'Handle with care.',
        'source_page' => '/get-quote',
        'ip_address' => '127.0.0.1',
        'user_agent' => 'Pest',
        'status' => 'new',
    ], $overrides);
}

it('lists quotes from the quote requests table', function () {
    QuoteRequest::query()->create(quotePayload());

    $this->actingAs($this->user)
        ->get(route('quotes.index'))
        ->assertOk()
        ->assertSee('Jane Doe')
        ->assertSee('Residential Relocation')
        ->assertSee('#QT00001');
});

it('creates a new quote', function () {
    $response = $this->actingAs($this->user)
        ->post(route('quotes.store'), quotePayload([
            'full_name' => 'Create Quote Lead',
            'email' => 'create@example.com',
        ]));

    $quote = QuoteRequest::query()->first();

    $response->assertRedirect(route('quotes.show', $quote));

    $this->assertDatabaseHas('quote_requests', [
        'full_name' => 'Create Quote Lead',
        'email' => 'create@example.com',
        'status' => 'new',
    ]);
});

it('updates an existing quote', function () {
    $quote = QuoteRequest::query()->create(quotePayload());

    $response = $this->actingAs($this->user)
        ->put(route('quotes.update', $quote), quotePayload([
            'full_name' => 'Updated Lead',
            'status' => 'processing',
        ]));

    $response->assertRedirect(route('quotes.show', $quote));

    $this->assertDatabaseHas('quote_requests', [
        'id' => $quote->id,
        'full_name' => 'Updated Lead',
        'status' => 'processing',
    ]);
});

it('approves and declines quotes', function () {
    $quote = QuoteRequest::query()->create(quotePayload());

    $this->actingAs($this->user)
        ->patch(route('quotes.approve', $quote))
        ->assertRedirect();

    $this->assertDatabaseHas('quote_requests', [
        'id' => $quote->id,
        'status' => 'quoted',
    ]);

    $this->actingAs($this->user)
        ->patch(route('quotes.decline', $quote))
        ->assertRedirect();

    $this->assertDatabaseHas('quote_requests', [
        'id' => $quote->id,
        'status' => 'closed',
    ]);
});

it('deletes a quote', function () {
    $quote = QuoteRequest::query()->create(quotePayload());

    $this->actingAs($this->user)
        ->delete(route('quotes.destroy', $quote))
        ->assertRedirect(route('quotes.index'));

    $this->assertDatabaseMissing('quote_requests', [
        'id' => $quote->id,
    ]);
});
