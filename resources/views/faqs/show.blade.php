@extends('layouts.vertical', ['title' => 'FAQ Details'])

@section('content')
<div class="row justify-content-center">
    <div class="col-xl-8">
        <div class="card">
            <div class="card-body">
                <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
                    <div>
                        <p class="text-muted mb-1">FAQ</p>
                        <h4 class="mb-1">{{ $faq->question }}</h4>
                        <p class="text-muted mb-0">{{ $faq->reference() }}</p>
                    </div>
                    <span class="badge badge-soft-{{ $faq->statusBadgeClass() }}">{{ $faq->statusLabel() }}</span>
                </div>
                <div class="row g-3 mt-3">
                    <div class="col-md-4"><div class="border rounded p-3 h-100"><p class="text-muted mb-1">Category</p><div class="fw-semibold">{{ $faq->category }}</div></div></div>
                    <div class="col-md-4"><div class="border rounded p-3 h-100"><p class="text-muted mb-1">Order</p><div class="fw-semibold">{{ $faq->display_order }}</div></div></div>
                    <div class="col-md-4"><div class="border rounded p-3 h-100"><p class="text-muted mb-1">Updated</p><div class="fw-semibold">{{ $faq->updated_at?->format('d M Y h:i A') ?? 'N/A' }}</div></div></div>
                </div>
                <div class="mt-4">
                    <h6 class="text-muted mb-2">Answer</h6>
                    <div class="border rounded p-3 bg-light-subtle" style="white-space: pre-line;">{{ $faq->answer }}</div>
                </div>
                <div class="d-flex flex-wrap gap-2 mt-4">
                    <a class="btn btn-outline-secondary" href="{{ route('faqs.index') }}">Back to FAQs</a>
                    <a class="btn btn-outline-primary" href="{{ route('faqs.edit', $faq) }}">Edit FAQ</a>
                    @if($faq->status !== \App\Models\Faq::STATUS_PUBLISHED)
                        <form action="{{ route('faqs.publish', $faq) }}" method="POST">@csrf @method('PATCH')<button class="btn btn-success" type="submit">Publish</button></form>
                    @endif
                    @if($faq->status !== \App\Models\Faq::STATUS_ARCHIVED)
                        <form action="{{ route('faqs.archive', $faq) }}" method="POST">@csrf @method('PATCH')<button class="btn btn-warning" type="submit">Archive</button></form>
                    @endif
                    <form action="{{ route('faqs.destroy', $faq) }}" data-delete-confirm data-delete-message="Do you want to delete this FAQ?" data-delete-title="Delete FAQ?" method="POST">@csrf @method('DELETE')<button class="btn btn-outline-danger" type="submit">Delete</button></form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
