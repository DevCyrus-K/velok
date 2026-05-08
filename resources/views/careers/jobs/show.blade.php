@extends('layouts.vertical', ['title' => 'Job Details'])

@section('content')
<div class="row">
    <div class="col-xl-5">
        <div class="card">
            <div class="card-body">
                <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
                    <div>
                        <p class="text-muted mb-1">Career Job</p>
                        <h4 class="mb-1">{{ $job->title }}</h4>
                        <p class="text-muted mb-0">{{ $job->reference() }}</p>
                    </div>
                    <span class="badge badge-soft-{{ $job->statusBadgeClass() }}">{{ $job->statusLabel() }}</span>
                </div>

                <div class="row g-3 mt-3">
                    <div class="col-md-6">
                        <div class="border rounded p-3 h-100">
                            <p class="text-muted mb-1">Department</p>
                            <div class="fw-semibold">{{ $job->department ?: 'Not set' }}</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="border rounded p-3 h-100">
                            <p class="text-muted mb-1">Location</p>
                            <div class="fw-semibold">{{ $job->location ?: 'Not set' }}</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="border rounded p-3 h-100">
                            <p class="text-muted mb-1">Type</p>
                            <div class="fw-semibold">{{ $job->employment_type ?: 'Not set' }}</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="border rounded p-3 h-100">
                            <p class="text-muted mb-1">Salary</p>
                            <div class="fw-semibold">{{ $job->salary_range ?: 'Not shown' }}</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="border rounded p-3 h-100">
                            <p class="text-muted mb-1">Posted</p>
                            <div class="fw-semibold">{{ $job->posted_at?->format('d M Y') ?? 'Not set' }}</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="border rounded p-3 h-100">
                            <p class="text-muted mb-1">Closing</p>
                            <div class="fw-semibold">{{ $job->closes_at?->format('d M Y') ?? 'Open ended' }}</div>
                        </div>
                    </div>
                </div>

                @if($job->summary)
                    <div class="mt-4">
                        <h6 class="text-muted mb-2">Summary</h6>
                        <p class="mb-0">{{ $job->summary }}</p>
                    </div>
                @endif

                <div class="d-flex flex-wrap gap-2 mt-4">
                    <a class="btn btn-outline-secondary" href="{{ route('careers.jobs.index') }}">Back to Jobs</a>
                    <a class="btn btn-outline-primary" href="{{ route('careers.jobs.edit', $job) }}">Edit Job</a>
                    <a class="btn btn-primary" href="{{ route('careers.applications.index') }}">All Applications</a>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <h5 class="card-title mb-3">Job Details</h5>
                <div class="mb-4">
                    <h6 class="text-muted mb-2">Description</h6>
                    <div class="border rounded p-3 bg-light-subtle" style="white-space: pre-line;">{{ $job->description ?: 'No description added yet.' }}</div>
                </div>
                <div>
                    <h6 class="text-muted mb-2">Requirements</h6>
                    <div class="border rounded p-3 bg-light-subtle" style="white-space: pre-line;">{{ $job->requirements ?: 'No requirements added yet.' }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-7">
        <div class="card">
            <div class="card-body">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                    <div>
                        <h5 class="card-title mb-1">Applications</h5>
                        <p class="text-muted mb-0">{{ $applications->count() }} received for this job.</p>
                    </div>
                    <a class="btn btn-sm btn-outline-primary" href="{{ route('careers.applications.index') }}">View All</a>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light-subtle">
                        <tr>
                            <th>Applicant</th>
                            <th>Phone</th>
                            <th>Applied</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($applications as $application)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="avatar-sm rounded-circle bg-primary-subtle text-primary d-flex align-items-center justify-content-center fw-semibold">
                                            {{ $application->initials() }}
                                        </div>
                                        <div>
                                            <a class="fw-semibold link-dark" href="{{ route('careers.applications.show', $application) }}">{{ $application->applicant_name }}</a>
                                            <small class="text-muted d-block">{{ $application->email }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="text-muted">{{ $application->phone }}</span>
                                </td>
                                <td>{{ $application->applied_at?->format('d M Y') ?? $application->created_at?->format('d M Y') }}</td>
                                <td>
                                    <span class="badge badge-soft-{{ $application->statusBadgeClass() }}">{{ $application->statusLabel() }}</span>
                                </td>
                                <td class="text-end">
                                    <div class="d-inline-flex flex-wrap gap-1 justify-content-end">
                                        <a class="btn btn-icon btn-sm btn-soft-primary" href="{{ route('careers.applications.show', $application) }}" title="View">
                                            <i class="align-middle" data-lucide="eye"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td class="text-center text-muted py-4" colspan="5">No applications for this job yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
