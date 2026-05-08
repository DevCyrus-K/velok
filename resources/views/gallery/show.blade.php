@extends('layouts.vertical', ['title' => 'Gallery Image'])

@section('content')
<div class="row">
    <div class="col-xl-7">
        <div class="card">
            <div class="card-body">
                <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
                    <div>
                        <p class="text-muted mb-1">Gallery Image</p>
                        <h4 class="mb-1">{{ $item->title }}</h4>
                        <p class="text-muted mb-0">{{ $item->reference() }}</p>
                    </div>
                    <span class="badge badge-soft-{{ $item->statusBadgeClass() }}">{{ $item->statusLabel() }}</span>
                </div>
                <img alt="{{ $item->altText() }}" class="img-fluid rounded border mt-4" src="{{ $item->publicUrl() }}">
                <div class="row g-3 mt-3">
                    <div class="col-md-4"><div class="border rounded p-3 h-100"><p class="text-muted mb-1">Category</p><div class="fw-semibold">{{ $item->category ?: 'General' }}</div></div></div>
                    <div class="col-md-4"><div class="border rounded p-3 h-100"><p class="text-muted mb-1">Featured</p><div class="fw-semibold">{{ $item->featured ? 'Yes' : 'No' }}</div></div></div>
                    <div class="col-md-4"><div class="border rounded p-3 h-100"><p class="text-muted mb-1">Updated</p><div class="fw-semibold">{{ $item->updated_at?->format('d M Y h:i A') ?? 'N/A' }}</div></div></div>
                </div>
                <div class="mt-4">
                    <h6 class="text-muted mb-2">Alt Text</h6>
                    <div class="border rounded p-3 bg-light-subtle">{{ $item->altText() }}</div>
                </div>
                @if($item->description)
                    <div class="mt-4">
                        <h6 class="text-muted mb-2">Description</h6>
                        <div class="border rounded p-3 bg-light-subtle" style="white-space: pre-line;">{{ $item->description }}</div>
                    </div>
                @endif
                <div class="d-flex flex-wrap gap-2 mt-4">
                    <a class="btn btn-outline-secondary" href="{{ route('gallery.index') }}">Back to Gallery</a>
                    <a class="btn btn-outline-primary" href="{{ route('gallery.edit', $item) }}">Edit Image</a>
                    @if($item->status !== \App\Models\GalleryItem::STATUS_PUBLISHED)
                        <form action="{{ route('gallery.publish', $item) }}" method="POST">@csrf @method('PATCH')<button class="btn btn-success" type="submit">Publish</button></form>
                    @endif
                    @if($item->status !== \App\Models\GalleryItem::STATUS_ARCHIVED)
                        <form action="{{ route('gallery.archive', $item) }}" method="POST">@csrf @method('PATCH')<button class="btn btn-warning" type="submit">Archive</button></form>
                    @endif
                    <form action="{{ route('gallery.destroy', $item) }}" data-delete-confirm data-delete-message="Do you want to delete this gallery image?" data-delete-title="Delete gallery image?" method="POST">@csrf @method('DELETE')<button class="btn btn-outline-danger" type="submit">Delete</button></form>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-5">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title mb-3">Image Source</h5>
                <p class="text-muted mb-1">Saved Path</p>
                <div class="fw-semibold text-wrap">{{ $item->imagePath() }}</div>
                <hr>
                <p class="text-muted mb-1">Public URL</p>
                <a class="fw-semibold text-wrap d-block" href="{{ $item->publicUrl() }}" target="_blank" rel="noopener">{{ $item->publicUrl() }}</a>
            </div>
        </div>
    </div>
</div>
@endsection
