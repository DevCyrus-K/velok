@extends('layouts.vertical', ['title' => $isEditing ? 'Edit Job' : 'List Job'])

@section('content')
@php
    $errors = $errors ?? new \Illuminate\Support\ViewErrorBag();
    $postedAtValue = old('posted_at', $job->posted_at?->format('Y-m-d\TH:i'));
    $closesAtValue = old('closes_at', $job->closes_at?->format('Y-m-d\TH:i'));
@endphp

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
    <div class="col-xl-10">
        <form action="{{ $isEditing ? route('careers.jobs.update', $job) : route('careers.jobs.store') }}" method="POST">
            @csrf
            @if($isEditing)
                @method('PUT')
            @endif

            <div class="card">
                <div class="card-body">
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
                        <div>
                            <h4 class="card-title mb-1">{{ $isEditing ? 'Edit Job' : 'List Job' }}</h4>
                            <p class="text-muted mb-0">Keep the listing clear so applicants can decide and apply quickly.</p>
                        </div>
                        <a class="btn btn-sm btn-outline-secondary" href="{{ $isEditing ? route('careers.jobs.show', $job) : route('careers.jobs.index') }}">Back</a>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label" for="title">Job Title</label>
                            <input class="form-control @error('title') is-invalid @enderror" id="title" name="title" type="text" value="{{ old('title', $job->title) }}" required>
                            @error('title')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="status">Status</label>
                            <select class="form-select @error('status') is-invalid @enderror" id="status" name="status" required>
                                @foreach($statusOptions as $status => $label)
                                    <option value="{{ $status }}" @selected(old('status', $job->status) === $status)>{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="department">Department</label>
                            <input class="form-control @error('department') is-invalid @enderror" id="department" name="department" type="text" value="{{ old('department', $job->department) }}" placeholder="Operations">
                            @error('department')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="location">Location</label>
                            <input class="form-control @error('location') is-invalid @enderror" id="location" name="location" type="text" value="{{ old('location', $job->location) }}" placeholder="Nairobi">
                            @error('location')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="employment_type">Employment Type</label>
                            <input class="form-control @error('employment_type') is-invalid @enderror" id="employment_type" list="employment-type-options" name="employment_type" type="text" value="{{ old('employment_type', $job->employment_type) }}" placeholder="Full-time">
                            <datalist id="employment-type-options">
                                @foreach($employmentTypeOptions as $type)
                                    <option value="{{ $type }}">
                                @endforeach
                            </datalist>
                            @error('employment_type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="salary_range">Salary Range</label>
                            <input class="form-control @error('salary_range') is-invalid @enderror" id="salary_range" name="salary_range" type="text" value="{{ old('salary_range', $job->salary_range) }}" placeholder="Optional">
                            @error('salary_range')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="posted_at">Posted At</label>
                            <input class="form-control @error('posted_at') is-invalid @enderror" id="posted_at" name="posted_at" type="datetime-local" value="{{ $postedAtValue }}">
                            @error('posted_at')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="closes_at">Closes At</label>
                            <input class="form-control @error('closes_at') is-invalid @enderror" id="closes_at" name="closes_at" type="datetime-local" value="{{ $closesAtValue }}">
                            @error('closes_at')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-12">
                            <label class="form-label" for="summary">Short Summary</label>
                            <input class="form-control @error('summary') is-invalid @enderror" id="summary" name="summary" type="text" value="{{ old('summary', $job->summary) }}" maxlength="255">
                            @error('summary')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="description">Job Description</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="8">{{ old('description', $job->description) }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="requirements">Requirements</label>
                            <textarea class="form-control @error('requirements') is-invalid @enderror" id="requirements" name="requirements" rows="8">{{ old('requirements', $job->requirements) }}</textarea>
                            @error('requirements')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="d-flex flex-wrap gap-2 mt-4">
                        <button class="btn btn-success" type="submit">{{ $isEditing ? 'Update Job' : 'List Job' }}</button>
                        <a class="btn btn-outline-secondary" href="{{ $isEditing ? route('careers.jobs.show', $job) : route('careers.jobs.index') }}">Cancel</a>
                    </div>
                </div>
            </div>
        </form>

        @if($isEditing)
            <div class="card border-danger border-opacity-25">
                <div class="card-body">
                    <h5 class="card-title text-danger mb-2">Delete Job</h5>
                    <p class="text-muted mb-3">Remove this listing. Applications already received stay available for review.</p>
                    <form action="{{ route('careers.jobs.destroy', $job) }}" data-delete-confirm data-delete-message="Do you want to delete this job listing? Applications already received will stay available." data-delete-title="Delete job?" method="POST">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-danger" type="submit">Delete Job</button>
                    </form>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
