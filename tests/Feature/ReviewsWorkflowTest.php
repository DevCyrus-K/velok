<?php

use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;

uses(RefreshDatabase::class);

function reviewSubmissionPayload(array $overrides = []): array
{
    $payload = [
        'review-name' => 'Happy Homeowner',
        'review-role' => 'Homeowner',
        'review-rating' => '4.5',
        'review-message' => 'Kwikshift handled our house move carefully and kept communication clear from start to finish.',
        'source_page' => '/review-us',
    ];

    if (!array_key_exists('review-photo', $overrides) && !array_key_exists('photo', $overrides)) {
        $payload['review-photo'] = reviewPhoto();
    }

    return array_merge($payload, $overrides);
}

function reviewPhoto(): UploadedFile
{
    return UploadedFile::fake()->createWithContent(
        'happy-homeowner.png',
        base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+/p9sAAAAASUVORK5CYII=')
    );
}

it('accepts review-us form submissions as pending reviews', function () {
    $this->post('/api/reviews/submit', reviewSubmissionPayload())
        ->assertCreated()
        ->assertJsonPath('ok', true)
        ->assertJsonPath('data.status', Review::STATUS_PENDING);

    $review = Review::query()->first();

    $this->assertDatabaseHas('reviews', [
        'reviewer_name' => 'Happy Homeowner',
        'reviewer_role' => 'Homeowner',
        'rating' => 4.5,
        'status' => Review::STATUS_PENDING,
    ]);

    expect($review->photo_path)->toStartWith('general/')
        ->and($review->image_public_id)->toBe($review->photo_path)
        ->and($review->image_url)->toStartWith('https://res.cloudinary.test/image/upload/');
});

it('lets admin view approve and decline reviews', function () {
    $user = User::factory()->create();

    $this->post('/api/reviews/submit', reviewSubmissionPayload([
        'review-name' => 'Grace Reviewer',
        'review-rating' => '5',
    ]))->assertCreated();

    $review = Review::query()->first();

    $this->getJson('/api/reviews')
        ->assertOk()
        ->assertJsonCount(0, 'data');

    $this->actingAs($user)
        ->get(route('reviews.index'))
        ->assertOk()
        ->assertSee('Grace Reviewer')
        ->assertSee('Pending');

    $this->actingAs($user)
        ->get(route('reviews.show', $review))
        ->assertOk()
        ->assertSee('Approve Review')
        ->assertSee('Decline Review');

    $this->actingAs($user)
        ->patch(route('reviews.approve', $review), [
            'featured' => '1',
            'moderation_notes' => 'Useful trust-building review.',
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('reviews', [
        'id' => $review->id,
        'status' => Review::STATUS_APPROVED,
        'featured' => true,
        'moderation_notes' => 'Useful trust-building review.',
    ]);

    $this->getJson('/api/reviews')
        ->assertOk()
        ->assertJsonPath('data.0.name', 'Grace Reviewer')
        ->assertJsonPath('data.0.rating', 5);

    $this->actingAs($user)
        ->patch(route('reviews.decline', $review))
        ->assertRedirect();

    $this->assertDatabaseHas('reviews', [
        'id' => $review->id,
        'status' => Review::STATUS_DECLINED,
    ]);
});

it('validates review rating and photo from the review form', function () {
    $this->post('/api/reviews/submit', reviewSubmissionPayload([
        'review-rating' => '0',
        'review-photo' => null,
    ]))->assertStatus(422)
        ->assertJsonPath('ok', false)
        ->assertJsonValidationErrors(['review-rating']);
});
