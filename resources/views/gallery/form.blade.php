@extends('layouts.vertical', ['title' => $isEditing ? 'Edit Gallery Image' : 'Add Gallery Image'])

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
        <form action="{{ $isEditing ? route('gallery.update', $item) : route('gallery.store') }}" enctype="multipart/form-data" method="POST">
            @csrf
            @if($isEditing)
                @method('PUT')
            @endif
            <div class="card">
                <div class="card-body">
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
                        <div>
                            <h4 class="card-title mb-1">{{ $isEditing ? 'Edit Gallery Image' : 'Add Gallery Image' }}</h4>
                            <p class="text-muted mb-0">Use a clear image and alt text that shows the real moving work.</p>
                        </div>
                        <a class="btn btn-sm btn-outline-secondary" href="{{ $isEditing ? route('gallery.show', $item) : route('gallery.index') }}">Back</a>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label" for="title">Title</label>
                            <input class="form-control @error('title') is-invalid @enderror" id="title" name="title" type="text" value="{{ old('title', $item->title) }}" required>
                            @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="status">Status</label>
                            <select class="form-select @error('status') is-invalid @enderror" id="status" name="status" required>
                                @foreach($statusOptions as $status => $label)
                                    <option value="{{ $status }}" @selected(old('status', $item->status) === $status)>{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="image_path">Cloudinary URL or Public ID</label>
                            <input class="form-control @error('image_path') is-invalid @enderror" id="image_path" name="image_path" type="text" value="{{ old('image_path', $item->imagePath()) }}" placeholder="https://res.cloudinary.com/...">
                            @error('image_path')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="image_file">Upload Image</label>
                            <input accept="image/jpeg,image/png,image/webp,image/gif" class="form-control @error('image_file') is-invalid @enderror" id="image_file" name="image_file" type="file">
                            @error('image_file')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-5">
                            <label class="form-label" for="category">Category</label>
                            <input class="form-control @error('category') is-invalid @enderror" id="category" list="gallery-category-options" name="category" type="text" value="{{ old('category', $item->category) }}">
                            <datalist id="gallery-category-options">
                                @foreach($categories as $category)
                                    <option value="{{ $category }}">
                                @endforeach
                            </datalist>
                            @error('category')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-3">
                            <label class="form-label" for="order">Order</label>
                            <input class="form-control @error('order') is-invalid @enderror" id="order" min="0" name="order" type="number" value="{{ old('order', $item->order) }}">
                            @error('order')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label d-block" for="featured">Featured</label>
                            <div class="form-check form-switch mt-2">
                                <input class="form-check-input" id="featured" name="featured" type="checkbox" value="1" @checked(old('featured', $item->featured))>
                                <label class="form-check-label" for="featured">Show first</label>
                            </div>
                        </div>
                        <div class="col-12">
                            <label class="form-label" for="alt_text">Alt Text</label>
                            <input class="form-control @error('alt_text') is-invalid @enderror" id="alt_text" name="alt_text" type="text" value="{{ old('alt_text', $item->altText()) }}">
                            @error('alt_text')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12">
                            <label class="form-label" for="description">Description</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="4">{{ old('description', $item->description) }}</textarea>
                            @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                    <div class="d-flex flex-wrap gap-2 mt-4">
                        <button class="btn btn-success" type="submit">{{ $isEditing ? 'Update Image' : 'Save Image' }}</button>
                        <a class="btn btn-outline-secondary" href="{{ $isEditing ? route('gallery.show', $item) : route('gallery.index') }}">Cancel</a>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
