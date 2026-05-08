@extends('layouts.vertical', ['title' => 'Review Details'])

@section('content')
@php
    $reviewDate = $review->submitted_at ?? $review->created_at;
@endphp

<div class="row">
    <div class="col-xl-8">
        <div class="card">
            <div class="card-body">
                <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
                    <div>
                        <p class="text-muted mb-1">Customer Review</p>
                        <h4 class="mb-1">{{ $review->reviewer_name }}</h4>
                        <p class="text-muted mb-0">{{ $review->reference() }} submitted {{ $reviewDate?->format('d M Y h:i A') ?? 'N/A' }}</p>
                    </div>
                    <span class="badge badge-soft-{{ $review->statusBadgeClass() }}">{{ $review->statusLabel() }}</span>
                </div>

                <div class="row g-3 mt-3">
                    <div class="col-md-4">
                        <div class="border rounded p-3 h-100">
                            <p class="text-muted mb-1">Reviewer</p>
                            <div class="fw-semibold">{{ $review->reviewer_name }}</div>
                            <small class="text-muted">{{ $review->reviewer_role }}</small>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="border rounded p-3 h-100">
                            <p class="text-muted mb-1">Rating</p>
                            <div class="fw-semibold">{{ $review->ratingLabel() }} / 5</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="border rounded p-3 h-100">
                            <p class="text-muted mb-1">Featured</p>
                            <div class="fw-semibold">{{ $review->featured ? 'Yes' : 'No' }}</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="border rounded p-3 h-100">
                            <p class="text-muted mb-1">Source Page</p>
                            <div class="fw-semibold">{{ $review->source_page ?: 'Not captured' }}</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="border rounded p-3 h-100">
                            <p class="text-muted mb-1">Reviewed</p>
                            <div class="fw-semibold">{{ $review->reviewed_at?->format('d M Y h:i A') ?? 'Not reviewed' }}</div>
                            @if($review->reviewedByUser)
                                <small class="text-muted">by {{ $review->reviewedByUser->name }}</small>
                            @endif
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="border rounded p-3 h-100">
                            <p class="text-muted mb-1">IP Address</p>
                            <div class="fw-semibold">{{ $review->ip_address ?: 'Not captured' }}</div>
                        </div>
                    </div>
                </div>

                <div class="row g-3 mt-4">
                    <div class="col-md-5">
                        <img alt="{{ $review->reviewer_name }}" class="img-fluid rounded border" src="{{ $review->photoUrl() }}">
                    </div>
                    <div class="col-md-7">
                        <h6 class="text-muted mb-2">Review</h6>
                        <div class="border rounded p-3 bg-light-subtle h-100" style="white-space: pre-line;">{{ $review->review_message }}</div>
                    </div>
                </div>

                @if($review->moderation_notes)
                    <div class="mt-4">
                        <h6 class="text-muted mb-2">Moderation Notes</h6>
                        <div class="border rounded p-3 bg-light-subtle" style="white-space: pre-line;">{{ $review->moderation_notes }}</div>
                    </div>
                @endif

                <div class="d-flex flex-wrap gap-2 mt-4">
                    <a class="btn btn-outline-secondary" href="{{ route('reviews.index') }}">Back to Reviews</a>
                    @if($review->status !== \App\Models\Review::STATUS_APPROVED)
                        <form action="{{ route('reviews.approve', $review) }}" method="POST">
                            @csrf
                            @method('PATCH')
                            <button class="btn btn-success" type="submit">Approve Review</button>
                        </form>
                    @endif
                    @if($review->status !== \App\Models\Review::STATUS_DECLINED)
                        <form action="{{ route('reviews.decline', $review) }}" method="POST">
                            @csrf
                            @method('PATCH')
                            <button class="btn btn-warning" type="submit">Decline Review</button>
                        </form>
                    @endif
                    <form action="{{ route('reviews.destroy', $review) }}" data-delete-confirm data-delete-message="Do you want to delete this review?" data-delete-title="Delete review?" method="POST">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-outline-danger" type="submit">Delete Review</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-4">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title mb-3">Moderate Review</h5>
                <form action="{{ route('reviews.approve', $review) }}" method="POST">
                    @csrf
                    @method('PATCH')
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" id="featured" name="featured" type="checkbox" value="1" @checked(old('featured', $review->featured || $review->status !== \App\Models\Review::STATUS_APPROVED))>
                        <label class="form-check-label" for="featured">Feature when approved</label>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="moderation_notes">Moderation Notes</label>
                        <textarea class="form-control @error('moderation_notes') is-invalid @enderror" id="moderation_notes" name="moderation_notes" rows="7">{{ old('moderation_notes', $review->moderation_notes) }}</textarea>
                        @error('moderation_notes')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="d-grid gap-2">
                        <button class="btn btn-success" type="submit">Approve</button>
                    </div>
                </form>
                <form action="{{ route('reviews.decline', $review) }}" class="mt-2" method="POST">
                    @csrf
                    @method('PATCH')
                    <input name="moderation_notes" type="hidden" value="{{ old('moderation_notes', $review->moderation_notes) }}">
                    <button class="btn btn-outline-warning w-100" type="submit">Decline</button>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <h5 class="card-title mb-3">Submission</h5>
                <div class="mb-3">
                    <p class="text-muted mb-1">User Agent</p>
                    <div class="fw-semibold text-wrap">{{ $review->user_agent ?: 'Not captured' }}</div>
                </div>
                <div>
                    <p class="text-muted mb-1">Created</p>
                    <div class="fw-semibold">{{ $review->created_at?->format('d M Y h:i A') ?? 'N/A' }}</div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
