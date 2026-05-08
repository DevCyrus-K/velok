@extends('layouts.vertical', ['title' => $isEditing ? 'Edit FAQ' : 'Add FAQ'])

@section('content')
@if($errors->any())
    <div class="alert alert-danger" role="alert">
        <div class="fw-semibold mb-2">Please fix the highlighted fields.</div>
        <ul class="mb-0 ps-3">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="row justify-content-center">
    <div class="col-xl-9">
        <form action="{{ $isEditing ? route('faqs.update', $faq) : route('faqs.store') }}" method="POST">
            @csrf
            @if($isEditing)
                @method('PUT')
            @endif
            <div class="card">
                <div class="card-body">
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
                        <div>
                            <h4 class="card-title mb-1">{{ $isEditing ? 'Edit FAQ' : 'Add FAQ' }}</h4>
                            <p class="text-muted mb-0">Keep answers direct so visitors can decide and contact faster.</p>
                        </div>
                        <a class="btn btn-sm btn-outline-secondary" href="{{ $isEditing ? route('faqs.show', $faq) : route('faqs.index') }}">Back</a>
                    </div>
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label" for="question">Question</label>
                            <input class="form-control @error('question') is-invalid @enderror" id="question" name="question" type="text" value="{{ old('question', $faq->question) }}" required>
                            @error('question')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="category">Category</label>
                            <input class="form-control @error('category') is-invalid @enderror" id="category" list="faq-category-options" name="category" type="text" value="{{ old('category', $faq->category) }}">
                            <datalist id="faq-category-options">
                                @foreach($categories as $category)
                                    <option value="{{ $category }}">
                                @endforeach
                            </datalist>
                            @error('category')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="display_order">Display Order</label>
                            <input class="form-control @error('display_order') is-invalid @enderror" id="display_order" min="0" name="display_order" type="number" value="{{ old('display_order', $faq->display_order) }}">
                            @error('display_order')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="status">Status</label>
                            <select class="form-select @error('status') is-invalid @enderror" id="status" name="status" required>
                                @foreach($statusOptions as $status => $label)
                                    <option value="{{ $status }}" @selected(old('status', $faq->status) === $status)>{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12">
                            <label class="form-label" for="answer">Answer</label>
                            <textarea class="form-control @error('answer') is-invalid @enderror" id="answer" name="answer" rows="8" required>{{ old('answer', $faq->answer) }}</textarea>
                            @error('answer')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                    <div class="d-flex flex-wrap gap-2 mt-4">
                        <button class="btn btn-success" type="submit">{{ $isEditing ? 'Update FAQ' : 'Save FAQ' }}</button>
                        <a class="btn btn-outline-secondary" href="{{ $isEditing ? route('faqs.show', $faq) : route('faqs.index') }}">Cancel</a>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
