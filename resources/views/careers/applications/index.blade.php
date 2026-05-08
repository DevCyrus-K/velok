@extends('layouts.vertical', ['title' => 'Job Applications'])

@section('css')
<style>
    .application-toolbar > * {
        flex: 0 1 auto;
    }

    .application-search-bar {
        min-width: 220px;
    }

    .application-table-wrap {
        max-height: clamp(420px, 62vh, 720px);
        overflow: auto;
    }

    .application-table-wrap .table {
        min-width: 1040px;
    }

    .application-table-wrap thead th {
        position: sticky;
        top: 0;
        z-index: 2;
        background: var(--bs-body-bg);
        box-shadow: inset 0 -1px 0 var(--bs-border-color);
    }

    @media (max-width: 767.98px) {
        .application-toolbar > *,
        .application-toolbar .dropdown,
        .application-toolbar .dropdown > .btn,
        .application-toolbar .application-search-bar,
        .application-toolbar .application-search-bar input {
            width: 100%;
        }

        .application-toolbar .application-search-bar {
            margin-left: 0 !important;
        }
    }
</style>
@endsection

@section('content')
<div class="row">
    <div class="col-md-6 col-xl">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center gap-3">
                    <div>
                        <p class="text-dark fw-semibold fs-26 mb-1">{{ number_format($summary['total'] ?? 0) }}</p>
                        <p class="card-title mb-0">Applications</p>
                    </div>
                    <div class="ms-auto">
                        <span class="btn btn-primary avatar-md rounded-circle d-flex align-items-center justify-content-center">
                            <i class="fs-5 text-white" data-lucide="file-user"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-xl">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center gap-3">
                    <div>
                        <p class="text-dark fw-semibold fs-26 mb-1">{{ number_format($summary['new'] ?? 0) }}</p>
                        <p class="card-title mb-0">New</p>
                    </div>
                    <div class="ms-auto">
                        <span class="btn btn-warning avatar-md rounded-circle d-flex align-items-center justify-content-center">
                            <i class="fs-5 text-white" data-lucide="bell"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-xl">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center gap-3">
                    <div>
                        <p class="text-dark fw-semibold fs-26 mb-1">{{ number_format($summary['reviewing'] ?? 0) }}</p>
                        <p class="card-title mb-0">Reviewing</p>
                    </div>
                    <div class="ms-auto">
                        <span class="btn btn-info avatar-md rounded-circle d-flex align-items-center justify-content-center">
                            <i class="fs-5 text-white" data-lucide="search-check"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-xl">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center gap-3">
                    <div>
                        <p class="text-dark fw-semibold fs-26 mb-1">{{ number_format($summary['shortlisted'] ?? 0) }}</p>
                        <p class="card-title mb-0">Shortlisted</p>
                    </div>
                    <div class="ms-auto">
                        <span class="btn btn-success avatar-md rounded-circle d-flex align-items-center justify-content-center">
                            <i class="fs-5 text-white" data-lucide="user-check"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col">
        <div class="card">
            <div class="card-body">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 application-toolbar">
                    <div>
                        <h5 class="card-title mb-1">Job Applications</h5>
                    </div>

                    <div class="dropdown">
                        <a aria-expanded="false" class="dropdown-toggle btn btn-sm btn-outline-light rounded" data-bs-toggle="dropdown" href="#">
                            Filter: <span id="application-filter-label">All</span>
                        </a>
                        <div class="dropdown-menu">
                            <a class="dropdown-item application-filter-option" data-filter="all" href="#!">All</a>
                            @foreach($statusOptions as $status => $label)
                                <a class="dropdown-item application-filter-option" data-filter="{{ $status }}" href="#!">{{ $label }}</a>
                            @endforeach
                        </div>
                    </div>

                    <div class="dropdown">
                        <a aria-expanded="false" class="dropdown-toggle btn btn-sm btn-outline-light rounded" data-bs-toggle="dropdown" href="#">
                            Job: <span id="application-job-filter-label">All</span>
                        </a>
                        <div class="dropdown-menu">
                            <a class="dropdown-item application-job-filter-option" data-job="all" href="#!">All jobs</a>
                            @foreach($applications->pluck('job_title')->filter()->unique()->sort()->values() as $jobTitle)
                                <a class="dropdown-item application-job-filter-option" data-job="{{ Str::slug($jobTitle) }}" href="#!">{{ $jobTitle }}</a>
                            @endforeach
                        </div>
                    </div>

                    <div class="search-bar ms-auto application-search-bar">
                        <span style="top: 2px;"><i data-lucide="search"></i></span>
                        <input class="form-control form-control-sm" id="application-search" placeholder="Search applications..." type="search" />
                    </div>

                    <div class="dropdown">
                        <a aria-expanded="false" class="dropdown-toggle btn btn-sm btn-outline-light rounded" data-bs-toggle="dropdown" href="#">
                            Sort: <span id="application-sort-label">Newest</span>
                        </a>
                        <div class="dropdown-menu dropdown-menu-end">
                            <a class="dropdown-item application-sort-option" data-sort="newest" href="#!">Newest First</a>
                            <a class="dropdown-item application-sort-option" data-sort="oldest" href="#!">Oldest First</a>
                            <a class="dropdown-item application-sort-option" data-sort="applicant" href="#!">Applicant Name</a>
                            <a class="dropdown-item application-sort-option" data-sort="job" href="#!">Job Title</a>
                        </div>
                    </div>

                    <div class="dropdown">
                        <a aria-expanded="false" class="dropdown-toggle btn btn-sm btn-outline-light rounded" data-bs-toggle="dropdown" href="#">
                            Reports
                        </a>
                        <div class="dropdown-menu dropdown-menu-end">
                            <a class="dropdown-item" href="#!" id="application-export-trigger">Export CSV</a>
                        </div>
                    </div>

                    <a class="btn btn-sm btn-outline-primary" href="{{ route('careers.jobs.index') }}">
                        <i class="icon-sm me-1" data-lucide="briefcase"></i>Jobs
                    </a>
                </div>
            </div>

            <div>
                <div class="table-responsive table-centered application-table-wrap">
                    <table class="table table-hover align-middle text-nowrap mb-0">
                        <thead class="text-uppercase fs-12">
                            <tr>
                                <th class="border-0 py-2 text-dark">Applicant</th>
                                <th class="border-0 py-2 text-dark">Job</th>
                                <th class="border-0 py-2 text-dark">Phone</th>
                                <th class="border-0 py-2 text-dark">Location</th>
                                <th class="border-0 py-2 text-dark">Applied</th>
                                <th class="border-0 py-2 text-dark">Status</th>
                                <th class="border-0 py-2 text-dark text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="application-table-body">
                            @foreach($applications as $application)
                                @php
                                    $applicationDate = $application->applied_at ?? $application->created_at;
                                @endphp
                                <tr data-applicant="{{ strtolower($application->applicant_name) }}"
                                    data-application-row
                                    data-created="{{ $applicationDate?->format('c') ?? '' }}"
                                    data-job="{{ Str::slug($application->job_title) }}"
                                    data-job-title="{{ strtolower($application->job_title) }}"
                                    data-search="{{ strtolower(implode(' ', [$application->reference(), $application->applicant_name, $application->email, $application->phone, $application->current_location, $application->job_title, $application->statusLabel(), $application->cover_letter])) }}"
                                    data-status="{{ $application->status }}">
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="avatar-sm rounded-circle bg-primary-subtle text-primary d-flex align-items-center justify-content-center fw-semibold">
                                                {{ $application->initials() }}
                                            </div>
                                            <div>
                                                <a class="fw-semibold link-dark" href="{{ route('careers.applications.show', $application) }}">{{ $application->applicant_name }}</a>
                                                <small class="text-muted d-block">
                                                    <a class="text-muted text-decoration-none" href="mailto:{{ $application->email }}">{{ $application->email }}</a>
                                                </small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        @if($application->careerJob)
                                            <a class="fw-medium" href="{{ route('careers.jobs.show', $application->careerJob) }}">{{ $application->job_title }}</a>
                                        @else
                                            <span class="fw-medium">{{ $application->job_title }}</span>
                                        @endif
                                        <small class="text-muted d-block">{{ $application->reference() }}</small>
                                    </td>
                                    <td>
                                        <a class="text-muted text-decoration-none" href="{{ $application->telLink() }}">{{ $application->phone }}</a>
                                    </td>
                                    <td>{{ $application->current_location ?: 'Not set' }}</td>
                                    <td>
                                        {{ $applicationDate?->format('d M, Y') ?? 'N/A' }}
                                        <small class="text-muted">{{ $applicationDate?->format('h:i A') ?? '' }}</small>
                                    </td>
                                    <td>
                                        <span class="badge badge-soft-{{ $application->statusBadgeClass() }}">{{ $application->statusLabel() }}</span>
                                    </td>
                                    <td class="text-end">
                                        <div class="d-inline-flex flex-wrap gap-1 justify-content-end">
                                            <a class="btn btn-icon btn-sm btn-soft-primary" href="{{ route('careers.applications.show', $application) }}" title="View">
                                                <i class="align-middle" data-lucide="eye"></i>
                                            </a>
                                            <form action="{{ route('careers.applications.destroy', $application) }}" class="d-inline-flex" data-delete-confirm data-delete-message="Do you want to delete this job application?" data-delete-title="Delete application?" method="POST">
                                                @csrf
                                                @method('DELETE')
                                                <button class="btn btn-icon btn-sm btn-soft-danger" title="Delete" type="submit">
                                                    <i class="align-middle" data-lucide="trash-2"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                            <tr class="{{ $applications->isNotEmpty() ? 'd-none' : '' }}" id="application-empty-state">
                                <td class="text-center text-muted py-4" colspan="7" id="application-empty-message">
                                    {{ $applications->isEmpty() ? 'No job applications yet.' : 'No applications match your search.' }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="card-body border-top py-3">
                    <small class="text-muted" id="application-count">{{ $applications->count() }} applications</small>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const searchInput = document.getElementById('application-search');
        const rows = Array.from(document.querySelectorAll('[data-application-row]'));
        const tbody = document.getElementById('application-table-body');
        const emptyState = document.getElementById('application-empty-state');
        const emptyMessage = document.getElementById('application-empty-message');
        const countLabel = document.getElementById('application-count');
        const filterOptions = document.querySelectorAll('.application-filter-option');
        const jobOptions = document.querySelectorAll('.application-job-filter-option');
        const sortOptions = document.querySelectorAll('.application-sort-option');
        const filterLabel = document.getElementById('application-filter-label');
        const jobLabel = document.getElementById('application-job-filter-label');
        const sortLabel = document.getElementById('application-sort-label');
        const exportTrigger = document.getElementById('application-export-trigger');

        if (!searchInput || !tbody || !emptyState || !emptyMessage || !countLabel) {
            return;
        }

        const total = rows.length;
        let debounceTimer = null;
        let currentFilter = 'all';
        let currentJob = 'all';
        let currentSort = 'newest';

        const filterLabels = {
            all: 'All',
            new: 'New',
            reviewing: 'Reviewing',
            shortlisted: 'Shortlisted',
            rejected: 'Rejected',
            hired: 'Hired',
        };

        const sortLabels = {
            newest: 'Newest',
            oldest: 'Oldest',
            applicant: 'Applicant',
            job: 'Job',
        };

        const jobLabels = {
            all: 'All',
        };

        jobOptions.forEach((option) => {
            jobLabels[option.dataset.job] = option.textContent.trim();
        });

        const getDate = (row) => new Date(row.dataset.created || 0);
        const visibleRows = () => rows.filter((row) => !row.classList.contains('d-none'));

        const sortRows = (rowsToSort) => {
            const sorted = [...rowsToSort];

            if (currentSort === 'newest') {
                sorted.sort((a, b) => getDate(b) - getDate(a));
            } else if (currentSort === 'oldest') {
                sorted.sort((a, b) => getDate(a) - getDate(b));
            } else if (currentSort === 'applicant') {
                sorted.sort((a, b) => (a.dataset.applicant || '').localeCompare(b.dataset.applicant || ''));
            } else if (currentSort === 'job') {
                sorted.sort((a, b) => (a.dataset.jobTitle || '').localeCompare(b.dataset.jobTitle || ''));
            }

            return sorted;
        };

        const applyFilters = () => {
            const query = searchInput.value.trim().toLowerCase();
            const matchingRows = [];

            rows.forEach((row) => {
                const haystack = (row.dataset.search || '').toLowerCase();
                const matchesSearch = query === '' || haystack.includes(query);
                const matchesStatus = currentFilter === 'all' || row.dataset.status === currentFilter;
                const matchesJob = currentJob === 'all' || row.dataset.job === currentJob;
                const matches = matchesSearch && matchesStatus && matchesJob;

                row.classList.toggle('d-none', !matches);

                if (matches) {
                    matchingRows.push(row);
                }
            });

            sortRows(matchingRows).forEach((row) => tbody.appendChild(row));

            emptyState.classList.toggle('d-none', matchingRows.length > 0);
            emptyMessage.textContent = total === 0 ? 'No job applications yet.' : 'No applications match your search.';
            countLabel.textContent = total === 0 ? '0 applications' : `${matchingRows.length} of ${total} applications`;
        };

        filterOptions.forEach((option) => {
            option.addEventListener('click', function (event) {
                event.preventDefault();
                currentFilter = this.dataset.filter;
                filterLabel.textContent = filterLabels[currentFilter] || 'All';
                applyFilters();
            });
        });

        jobOptions.forEach((option) => {
            option.addEventListener('click', function (event) {
                event.preventDefault();
                currentJob = this.dataset.job;
                jobLabel.textContent = jobLabels[currentJob] || 'All';
                applyFilters();
            });
        });

        sortOptions.forEach((option) => {
            option.addEventListener('click', function (event) {
                event.preventDefault();
                currentSort = this.dataset.sort;
                sortLabel.textContent = sortLabels[currentSort] || 'Newest';
                applyFilters();
            });
        });

        searchInput.addEventListener('input', function () {
            window.clearTimeout(debounceTimer);
            debounceTimer = window.setTimeout(applyFilters, 180);
        });

        if (exportTrigger) {
            exportTrigger.addEventListener('click', function (event) {
                event.preventDefault();

                const csvRows = [
                    ['Applicant', 'Email', 'Job', 'Phone', 'Location', 'Applied', 'Status']
                ];

                visibleRows().forEach((row) => {
                    const cells = row.querySelectorAll('td');

                    if (cells.length < 6) {
                        return;
                    }

                    csvRows.push([
                        cells[0].querySelector('.fw-semibold')?.textContent.trim() || '',
                        cells[0].querySelector('small')?.textContent.trim() || '',
                        cells[1].querySelector('.fw-medium')?.textContent.trim() || cells[1].textContent.trim(),
                        cells[2].textContent.trim(),
                        cells[3].textContent.trim(),
                        cells[4].textContent.trim(),
                        cells[5].textContent.trim(),
                    ]);
                });

                const csvContent = csvRows
                    .map((columns) => columns.map((value) => `"${String(value).replace(/"/g, '""')}"`).join(','))
                    .join('\n');
                const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
                const url = URL.createObjectURL(blob);
                const link = document.createElement('a');

                link.href = url;
                link.download = 'job-applications.csv';
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
                URL.revokeObjectURL(url);
            });
        }

        applyFilters();
    });
</script>
@endsection
