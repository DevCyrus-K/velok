<?php

namespace App\Http\Controllers;

use App\Models\Review;
use App\Services\StorageService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ReviewController extends Controller
{
    public function index(): View
    {
        $reviews = Review::query()
            ->orderByDesc('submitted_at')
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->get();

        return view('reviews.index', [
            'reviews' => $reviews,
            'statusOptions' => Review::statusOptions(),
            'summary' => [
                'total' => $reviews->count(),
                'pending' => $reviews->where('status', Review::STATUS_PENDING)->count(),
                'approved' => $reviews->where('status', Review::STATUS_APPROVED)->count(),
                'declined' => $reviews->where('status', Review::STATUS_DECLINED)->count(),
                'average_rating' => $reviews->isNotEmpty() ? number_format((float) $reviews->avg('rating'), 1) : '0.0',
            ],
        ]);
    }

    public function show(Review $review): View
    {
        $review->load('reviewedByUser');

        return view('reviews.show', [
            'review' => $review,
            'statusOptions' => Review::statusOptions(),
        ]);
    }

    public function approve(Request $request, Review $review): RedirectResponse
    {
        $this->moderate($request, $review, Review::STATUS_APPROVED);

        return back()->with('toast-success', 'Review approved successfully.');
    }

    public function decline(Request $request, Review $review): RedirectResponse
    {
        $this->moderate($request, $review, Review::STATUS_DECLINED);

        return back()->with('toast-success', 'Review declined successfully.');
    }

    public function destroy(Review $review): RedirectResponse
    {
        $publicId = $review->getAttribute('image_public_id') ?: $review->getAttribute('storage_key') ?: $review->photo_path;

        if (is_string($publicId) && trim($publicId) !== '' && ! Str::startsWith($publicId, ['http://', 'https://', '/'])) {
            app(StorageService::class)->deleteImage($publicId);
        }

        $review->delete();

        return redirect()
            ->route('reviews.index')
            ->with('toast-success', 'Review deleted successfully.');
    }

    private function moderate(Request $request, Review $review, string $status): void
    {
        $validated = $request->validate([
            'moderation_notes' => ['nullable', 'string'],
            'featured' => ['nullable', 'boolean'],
        ]);

        $review->update([
            'status' => $status,
            'featured' => $status === Review::STATUS_APPROVED && (bool) ($validated['featured'] ?? true),
            'moderation_notes' => $validated['moderation_notes'] ?? $review->moderation_notes,
            'reviewed_at' => now(),
            'reviewed_by' => Auth::id(),
        ]);
    }
}
