<?php

namespace App\Http\Controllers;

use App\Models\Review;
use App\Services\StorageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class ReviewApiController extends Controller
{
    public function index(): JsonResponse
    {
        $reviews = Review::query()
            ->where('status', Review::STATUS_APPROVED)
            ->orderByDesc('featured')
            ->orderByDesc('submitted_at')
            ->orderByDesc('id')
            ->get()
            ->map(fn (Review $review) => [
                'id' => $review->id,
                'name' => $review->reviewer_name,
                'role' => $review->reviewer_role,
                'rating' => (float) $review->rating,
                'rating_label' => $review->ratingLabel(),
                'message' => $review->review_message,
                'photo_url' => $review->photoUrl(),
                'featured' => $review->featured,
                'submitted_at' => $review->submitted_at?->toISOString(),
            ]);

        return response()->json([
            'ok' => true,
            'success' => true,
            'data' => $reviews,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'review-name' => ['required_without:reviewer_name', 'nullable', 'string', 'max:120'],
            'review-role' => ['required_without:reviewer_role', 'nullable', 'string', 'max:120'],
            'review-rating' => ['required_without:rating', 'nullable'],
            'review-message' => ['required_without:review_message', 'nullable', 'string', 'min:20', 'max:4000'],
            'review-photo' => ['required_without:photo', 'nullable', 'image', 'mimes:jpg,jpeg,png,webp,gif', 'max:10240'],
            'reviewer_name' => ['required_without:review-name', 'nullable', 'string', 'max:120'],
            'reviewer_role' => ['required_without:review-role', 'nullable', 'string', 'max:120'],
            'rating' => ['required_without:review-rating', 'nullable'],
            'review_message' => ['required_without:review-message', 'nullable', 'string', 'min:20', 'max:4000'],
            'photo' => ['required_without:review-photo', 'nullable', 'image', 'mimes:jpg,jpeg,png,webp,gif', 'max:10240'],
            'source_page' => ['nullable', 'string', 'max:255'],
        ]);

        $rating = $this->normalizeRating($request->input('review-rating', $request->input('rating')));

        if ($rating === null) {
            $validator->after(function ($validator) {
                $validator->errors()->add('review-rating', 'Please choose a star rating before submitting your review.');
            });
        }

        if ($validator->fails()) {
            return response()->json([
                'ok' => false,
                'success' => false,
                'message' => 'Please review the highlighted fields and try again.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $photo = $request->file('review-photo') ?: $request->file('photo');

        if (! $photo instanceof UploadedFile) {
            return response()->json([
                'ok' => false,
                'success' => false,
                'message' => 'Please upload your photo.',
                'errors' => [
                    'review-photo' => ['Please upload your photo.'],
                ],
            ], 422);
        }

        $name = $this->squish((string) $request->input('review-name', $request->input('reviewer_name')));
        $role = $this->squish((string) $request->input('review-role', $request->input('reviewer_role')));
        $message = trim((string) $request->input('review-message', $request->input('review_message')));
        $uploaded = app(StorageService::class)->storeUploadedFile($photo, 'images/reviews');

        $review = Review::query()->create([
            'reviewer_name' => $name,
            'reviewer_role' => $role,
            'rating' => $rating,
            'review_message' => $message,
            'photo_path' => $uploaded['key'],
            'image_url' => $uploaded['url'],
            'image_public_id' => $uploaded['public_id'] ?? $uploaded['key'],
            'storage_key' => $uploaded['key'],
            'storage_url' => $uploaded['url'],
            'status' => Review::STATUS_PENDING,
            'featured' => false,
            'submitted_at' => now(),
            'source_page' => $this->nullableTrim($request->input('source_page')) ?: '/review-us',
            'ip_address' => $request->ip(),
            'user_agent' => Str::limit((string) $request->userAgent(), 255, ''),
        ]);

        return response()->json([
            'ok' => true,
            'success' => true,
            'message' => 'Thank you! Your review has been received and will be checked before publishing.',
            'data' => [
                'id' => $review->id,
                'reference' => $review->reference(),
                'status' => $review->status,
            ],
            'submission_id' => $review->id,
        ], 201);
    }

    private function normalizeRating(mixed $value): ?float
    {
        $raw = str_replace(',', '.', trim((string) $value));

        if (! preg_match('/^(?:0\.5|[1-4](?:\.0|\.5)?|5(?:\.0)?)$/', $raw)) {
            return null;
        }

        return round(((float) $raw) * 2) / 2;
    }

    private function squish(string $value): string
    {
        return (string) Str::of($value)->squish();
    }

    private function nullableTrim(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $trimmed = trim((string) $value);

        return $trimmed === '' ? null : $trimmed;
    }
}
