@extends('layouts.vertical', ['title' => 'Application Details'])

@section('content')
<div class="row">
    <div class="col-xl-8">
        <div class="card">
            <div class="card-body">
                <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
                    <div>
                        <p class="text-muted mb-1">Job Application</p>
                        <h4 class="mb-1">{{ $application->applicant_name }}</h4>
                        <p class="text-muted mb-0">{{ $application->reference() }} for {{ $application->job_title }}</p>
                    </div>
                    <div class="d-flex flex-wrap gap-2">
                        <a class="btn btn-outline-primary btn-sm" href="mailto:{{ $application->email }}">Email</a>
                    </div>
                </div>

                <div class="row g-3 mt-3">
                    <div class="col-md-4">
                        <div class="border rounded p-3 h-100">
                            <p class="text-muted mb-1">Email</p>
                            <div class="fw-semibold">{{ $application->email }}</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="border rounded p-3 h-100">
                            <p class="text-muted mb-1">Phone</p>
                            <div class="fw-semibold">{{ $application->phone }}</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="border rounded p-3 h-100">
                            <p class="text-muted mb-1">Status</p>
                            <span class="badge badge-soft-{{ $application->statusBadgeClass() }}">{{ $application->statusLabel() }}</span>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="border rounded p-3 h-100">
                            <p class="text-muted mb-1">Job</p>
                            @if($application->careerJob)
                                <a class="fw-semibold" href="{{ route('careers.jobs.show', $application->careerJob) }}">{{ $application->careerJob->title }}</a>
                            @else
                                <div class="fw-semibold">{{ $application->job_title }}</div>
                            @endif
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="border rounded p-3 h-100">
                            <p class="text-muted mb-1">Current Location</p>
                            <div class="fw-semibold">{{ $application->current_location ?: 'Not set' }}</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="border rounded p-3 h-100">
                            <p class="text-muted mb-1">Applied</p>
                            <div class="fw-semibold">{{ ($application->applied_at ?? $application->created_at)?->format('d M Y h:i A') ?? 'Not available' }}</div>
                        </div>
                    </div>
                </div>

                <div class="mt-4">
                    <h6 class="text-muted mb-2">Cover Letter</h6>
                    <div class="border rounded p-3 bg-light-subtle" style="min-height: 140px; white-space: pre-line;">{{ $application->cover_letter ?: 'No cover letter included.' }}</div>
                </div>

                @if($application->resume_url)
                    <div class="mt-4">
                        <h6 class="text-muted mb-2">Resume</h6>
                        <a class="btn btn-outline-primary btn-sm" href="{{ app(\App\Services\StorageService::class)->url($application->resume_url) ?? $application->resume_url }}" target="_blank" rel="noopener">
                            <i class="icon-sm me-1" data-lucide="external-link"></i>Open Resume
                        </a>
                    </div>
                @endif

                <div class="d-flex flex-wrap gap-2 mt-4">
                    <a class="btn btn-outline-secondary" href="{{ route('careers.applications.index') }}">Back to Applications</a>
                    <a class="btn btn-outline-primary" href="{{ route('careers.jobs.index') }}">Jobs</a>
                    <form action="{{ route('careers.applications.destroy', $application) }}" data-delete-confirm data-delete-message="Do you want to delete this job application?" data-delete-title="Delete application?" method="POST">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-outline-danger" type="submit">Delete Application</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-4">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title mb-3">Review Status</h5>
                <form action="{{ route('careers.applications.status', $application) }}" method="POST">
                    @csrf
                    @method('PATCH')
                    <div class="mb-3">
                        <label class="form-label" for="status">Status</label>
                        <select class="form-select @error('status') is-invalid @enderror" id="status" name="status" required>
                            @foreach($statusOptions as $status => $label)
                                <option value="{{ $status }}" @selected(old('status', $application->status) === $status)>{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('status')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="notes">Review Notes</label>
                        <textarea class="form-control @error('notes') is-invalid @enderror" id="notes" name="notes" rows="7">{{ old('notes', $application->notes) }}</textarea>
                        @error('notes')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <button class="btn btn-success w-100" type="submit">Update Application</button>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <h5 class="card-title mb-3">Quick Actions</h5>
                <div class="d-grid gap-2">
                    @foreach([
                        \App\Models\JobApplication::STATUS_REVIEWING => 'Mark Reviewing',
                        \App\Models\JobApplication::STATUS_SHORTLISTED => 'Shortlist',
                        \App\Models\JobApplication::STATUS_HIRED => 'Mark Hired',
                        \App\Models\JobApplication::STATUS_REJECTED => 'Reject',
                    ] as $status => $label)
                        <form action="{{ route('careers.applications.status', $application) }}" method="POST">
                            @csrf
                            @method('PATCH')
                            <input name="status" type="hidden" value="{{ $status }}">
                            <button class="btn btn-outline-secondary w-100" type="submit">{{ $label }}</button>
                        </form>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <h5 class="card-title mb-3">Source</h5>
                <div class="mb-3">
                    <p class="text-muted mb-1">Source Page</p>
                    <div class="fw-semibold">{{ $application->source_page ?: 'Not captured' }}</div>
                </div>
                <div>
                    <p class="text-muted mb-1">Created</p>
                    <div class="fw-semibold">{{ $application->created_at?->format('d M Y h:i A') ?? 'Not available' }}</div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
