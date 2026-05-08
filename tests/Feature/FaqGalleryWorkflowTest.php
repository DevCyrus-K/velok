<?php

use App\Models\Faq;
use App\Models\GalleryItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function faqPayload(array $overrides = []): array
{
    return array_merge([
        'question' => 'Do you handle packing for fragile items?',
        'answer' => 'Yes. Our movers can pack fragile items carefully and label boxes so unpacking is faster.',
        'category' => 'packing',
        'display_order' => 3,
        'status' => Faq::STATUS_DRAFT,
    ], $overrides);
}

function galleryPayload(array $overrides = []): array
{
    return array_merge([
        'title' => 'Apartment Move in Nairobi',
        'image_path' => 'assets/img/post-1.jpg',
        'category' => 'Residential Relocation',
        'alt_text' => 'residential apartment moving service by Kwikshift Movers',
        'description' => 'A completed residential relocation in Nairobi.',
        'featured' => '1',
        'status' => GalleryItem::STATUS_DRAFT,
        'order' => 1,
    ], $overrides);
}

it('lets admin create publish and archive FAQs', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)
        ->post(route('faqs.store'), faqPayload());

    $faq = Faq::query()->first();

    $response->assertRedirect(route('faqs.show', $faq));

    $this->actingAs($user)
        ->get(route('faqs.index'))
        ->assertOk()
        ->assertSee('Do you handle packing for fragile items?')
        ->assertSee('Draft');

    $this->getJson('/api/faqs')
        ->assertOk()
        ->assertJsonCount(0, 'data');

    $this->actingAs($user)
        ->patch(route('faqs.publish', $faq))
        ->assertRedirect();

    $this->getJson('/api/faqs')
        ->assertOk()
        ->assertJsonPath('data.0.question', 'Do you handle packing for fragile items?');

    $this->actingAs($user)
        ->patch(route('faqs.archive', $faq))
        ->assertRedirect();

    $this->assertDatabaseHas('faqs', [
        'id' => $faq->id,
        'status' => Faq::STATUS_ARCHIVED,
    ]);
});

it('lets admin create publish and archive gallery items', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)
        ->post(route('gallery.store'), galleryPayload());

    $item = GalleryItem::query()->first();

    $response->assertRedirect(route('gallery.show', $item));

    $this->actingAs($user)
        ->get(route('gallery.index'))
        ->assertOk()
        ->assertSee('Apartment Move in Nairobi')
        ->assertSee('Draft');

    $this->getJson('/api/gallery')
        ->assertOk()
        ->assertJsonCount(0, 'data');

    $this->actingAs($user)
        ->patch(route('gallery.publish', $item))
        ->assertRedirect();

    $this->getJson('/api/gallery')
        ->assertOk()
        ->assertJsonPath('data.0.title', 'Apartment Move in Nairobi')
        ->assertJsonPath('data.0.image_path', 'assets/img/post-1.jpg');

    $this->actingAs($user)
        ->patch(route('gallery.archive', $item))
        ->assertRedirect();

    $this->assertDatabaseHas('gallery', [
        'id' => $item->id,
        'status' => GalleryItem::STATUS_ARCHIVED,
    ]);
});
