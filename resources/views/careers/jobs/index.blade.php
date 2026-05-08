@extends('layouts.vertical', ['title' => 'Career Jobs'])

@section('css')
<style>
    .career-toolbar > * {
        flex: 0 1 auto;
    }

    .career-search-bar {
        min-width: 220px;
    }

    .career-table-wrap {
        max-height: clamp(420px, 62vh, 720px);
        overflow: auto;
    }

    .career-table-wrap .table {
        min-width: 980px;
    }

    .career-table-wrap thead th {
        position: sticky;
        top: 0;
        z-index: 2;
        background: var(--bs-body-bg);
        box-shadow: inset 0 -1px 0 var(--bs-border-color);
    }

    .career-row-actions .dropdown-menu form {
        margin: 0;
    }

    .career-row-actions .dropdown-item {
        align-items: center;
        display: flex;
        gap: 0.5rem;
    }

    .career-row-actions .dropdown-item i {
        flex: 0 0 auto;
    }

    @media (max-width: 767.98px) {
        .career-toolbar > *,
        .career-toolbar .dropdown,
        .career-toolbar .dropdown > .btn,
        .career-toolbar .career-search-bar,
        .career-toolbar .career-search-bar input,
        .career-toolbar .btn-success {
            width: 100%;
        }

        .career-toolbar .career-search-bar {
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
                        <p class="card-title mb-0">Total Jobs</p>
                    </div>
                    <div class="ms-auto">
                        <span class="btn btn-primary avatar-md rounded-circle d-flex align-items-center justify-content-center">
                            <i class="fs-5 text-white" data-lucide="briefcase"></i>
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
                        <p class="text-dark fw-semibold fs-26 mb-1">{{ number_format($summary['open'] ?? 0) }}</p>
                        <p class="card-title mb-0">Open Jobs</p>
                    </div>
                    <div class="ms-auto">
                        <span class="btn btn-success avatar-md rounded-circle d-flex align-items-center justify-content-center">
                            <i class="fs-5 text-white" data-lucide="badge-check"></i>
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
                        <p class="text-dark fw-semibold fs-26 mb-1">{{ number_format($summary['draft'] ?? 0) }}</p>
                        <p class="card-title mb-0">Draft Jobs</p>
                    </div>
                    <div class="ms-auto">
                        <span class="btn btn-warning avatar-md rounded-circle d-flex align-items-center justify-content-center">
                            <i class="fs-5 text-white" data-lucide="file-clock"></i>
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
                        <p class="text-dark fw-semibold fs-26 mb-1">{{ number_format($summary['applications'] ?? 0) }}</p>
                        <p class="card-title mb-0">Applications</p>
                    </div>
                    <div class="ms-auto">
                        <span class="btn btn-info avatar-md rounded-circle d-flex align-items-center justify-content-center">
                            <i class="fs-5 text-white" data-lucide="file-user"></i>
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
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 career-toolbar">
                    <div>
                        <h5 class="card-title mb-1">Career Jobs</h5>
                    </div>

                    <div class="dropdown">
                        <a aria-expanded="false" class="dropdown-toggle btn btn-sm btn-outline-light rounded" data-bs-toggle="dropdown" href="#">
                            Filter: <span id="career-job-filter-label">All</span>
                        </a>
                        <div class="dropdown-menu">
                            <a class="dropdown-item career-job-filter-option" data-filter="all" href="#!">All</a>
                            @foreach($statusOptions as $status => $label)
                                <a class="dropdown-item career-job-filter-option" data-filter="{{ $status }}" href="#!">{{ $label }}</a>
                            @endforeach
                        </div>
                    </div>

                    <div class="dropdown">
                        <a aria-expanded="false" class="dropdown-toggle btn btn-sm btn-outline-light rounded" data-bs-toggle="dropdown" href="#">
                            Department: <span id="career-department-filter-label">All</span>
                        </a>
                        <div class="dropdown-menu">
                            <a class="dropdown-item career-department-filter-option" data-department="all" href="#!">All departments</a>
                            @foreach($jobs->pluck('department')->filter()->unique()->sort()->values() as $department)
                                <a class="dropdown-item career-department-filter-option" data-department="{{ Str::slug($department) }}" href="#!">{{ $department }}</a>
                            @endforeach
                        </div>
                    </div>

                    <div class="search-bar ms-auto career-search-bar">
                        <span style="top: 2px;"><i data-lucide="search"></i></span>
                        <input class="form-control form-control-sm" id="career-job-search" placeholder="Search jobs..." type="search" />
                    </div>

                    <div class="dropdown">
                        <a aria-expanded="false" class="dropdown-toggle btn btn-sm btn-outline-light rounded" data-bs-toggle="dropdown" href="#">
                            Sort: <span id="career-job-sort-label">Newest</span>
                        </a>
                        <div class="dropdown-menu dropdown-menu-end">
                            <a class="dropdown-item career-job-sort-option" data-sort="newest" href="#!">Newest First</a>
                            <a class="dropdown-item career-job-sort-option" data-sort="oldest" href="#!">Oldest First</a>
                            <a class="dropdown-item career-job-sort-option" data-sort="title" href="#!">Job Title</a>
                            <a class="dropdown-item career-job-sort-option" data-sort="applications" href="#!">Most Applications</a>
                        </div>
                    </div>

                    <div class="dropdown">
                        <a aria-expanded="false" class="dropdown-toggle btn btn-sm btn-outline-light rounded" data-bs-toggle="dropdown" href="#">
                            Reports
                        </a>
                        <div class="dropdown-menu dropdown-menu-end">
                            <a class="dropdown-item" href="#!" id="career-job-export-trigger">Export CSV</a>
                        </div>
                    </div>

                    <a class="btn btn-sm btn-success" href="{{ route('careers.jobs.create') }}">
                        <i class="icon-sm me-1" data-lucide="plus"></i>List Job
                    </a>
                </div>
            </div>

            <div>
                <div class="table-responsive table-centered career-table-wrap">
                    <table class="table table-hover align-middle text-nowrap mb-0">
                        <thead class="text-uppercase fs-12">
                            <tr>
                                <th class="border-0 py-2 text-dark">Job</th>
                                <th class="border-0 py-2 text-dark">Department</th>
                                <th class="border-0 py-2 text-dark">Location</th>
                                <th class="border-0 py-2 text-dark">Type</th>
                                <th class="border-0 py-2 text-dark">Applications</th>
                                <th class="border-0 py-2 text-dark">Closing</th>
                                <th class="border-0 py-2 text-dark">Status</th>
                                <th class="border-0 py-2 text-dark text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="career-job-table-body">
                            @foreach($jobs as $job)
                                @php
                                    $jobDate = $job->posted_at ?? $job->created_at;
                                @endphp
                                <tr data-applications="{{ (int) $job->applications_count }}"
                                    data-created="{{ $jobDate?->format('c') ?? '' }}"
                                    data-department="{{ Str::slug($job->department ?: 'Unassigned') }}"
                                    data-job-row
                                    data-search="{{ strtolower(implode(' ', [$job->reference(), $job->title, $job->department, $job->location, $job->employment_type, $job->salary_range, $job->summary, $job->statusLabel()])) }}"
                                    data-status="{{ $job->status }}"
                                    data-title="{{ strtolower($job->title) }}">
                                    <td>
                                        <a class="fw-semibold" href="{{ route('careers.jobs.show', $job) }}">{{ $job->title }}</a>
                                        <small class="text-muted d-block">{{ $job->reference() }}</small>
                                    </td>
                                    <td>{{ $job->department ?: 'Not set' }}</td>
                                    <td>{{ $job->location ?: 'Not set' }}</td>
                                    <td>{{ $job->employment_type ?: 'Not set' }}</td>
                                    <td>
                                        <a class="link-dark" href="{{ route('careers.jobs.show', $job) }}">{{ $job->applications_count }} total</a>
                                        <small class="text-muted d-block">{{ $job->open_applications_count }} active</small>
                                    </td>
                                    <td>{{ $job->closes_at?->format('d M Y') ?? 'Open ended' }}</td>
                                    <td>
                                        <span class="badge badge-soft-{{ $job->statusBadgeClass() }}">{{ $job->statusLabel() }}</span>
                                    </td>
                                    <td class="text-end">
                                        <div class="career-row-actions">
                                            <div class="d-none d-md-inline-flex flex-wrap gap-1 justify-content-end">
                                                <a class="btn btn-icon btn-sm btn-soft-primary" href="{{ route('careers.jobs.show', $job) }}" title="View">
                                                    <i class="align-middle" data-lucide="eye"></i>
                                                </a>
                                                <a class="btn btn-icon btn-sm btn-soft-secondary" href="{{ route('careers.jobs.edit', $job) }}" title="Edit">
                                                    <i class="align-middle" data-lucide="square-pen"></i>
                                                </a>
                                                <form action="{{ route('careers.jobs.destroy', $job) }}" class="d-inline-flex" data-delete-confirm data-delete-message="Do you want to delete this job listing? Applications already received will stay available." data-delete-title="Delete job?" method="POST">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button class="btn btn-icon btn-sm btn-soft-danger" title="Delete" type="submit">
                                                        <i class="align-middle" data-lucide="trash-2"></i>
                                                    </button>
                                                </form>
                                            </div>
                                            <div class="dropdown d-md-none">
                                                <button class="btn btn-icon btn-sm btn-soft-light" type="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="More actions for {{ $job->title }}">
                                                    <i class="align-middle" data-lucide="ellipsis-vertical"></i>
                                                </button>
                                                <div class="dropdown-menu dropdown-menu-end">
                                                    <a class="dropdown-item" href="{{ route('careers.jobs.show', $job) }}">
                                                        <i class="icon-sm" data-lucide="eye"></i>View
                                                    </a>
                                                    <a class="dropdown-item" href="{{ route('careers.jobs.edit', $job) }}">
                                                        <i class="icon-sm" data-lucide="square-pen"></i>Edit
                                                    </a>
                                                    <form action="{{ route('careers.jobs.destroy', $job) }}" data-delete-confirm data-delete-message="Do you want to delete this job listing? Applications already received will stay available." data-delete-title="Delete job?" method="POST">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button class="dropdown-item text-danger" type="submit">
                                                            <i class="icon-sm" data-lucide="trash-2"></i>Delete
                                                        </button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                            <tr class="{{ $jobs->isNotEmpty() ? 'd-none' : '' }}" id="career-job-empty-state">
                                <td class="text-center text-muted py-4" colspan="8" id="career-job-empty-message">
                                    {{ $jobs->isEmpty() ? 'No jobs listed yet.' : 'No jobs match your search.' }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="card-body border-top py-3">
                    <small class="text-muted" id="career-job-count">{{ $jobs->count() }} jobs</small>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const searchInput = document.getElementById('career-job-search');
        const rows = Array.from(document.querySelectorAll('[data-job-row]'));
        const tbody = document.getElementById('career-job-table-body');
        const emptyState = document.getElementById('career-job-empty-state');
        const emptyMessage = document.getElementById('career-job-empty-message');
        const countLabel = document.getElementById('career-job-count');
        const filterOptions = document.querySelectorAll('.career-job-filter-option');
        const departmentOptions = document.querySelectorAll('.career-department-filter-option');
        const sortOptions = document.querySelectorAll('.career-job-sort-option');
        const filterLabel = document.getElementById('career-job-filter-label');
        const departmentLabel = document.getElementById('career-department-filter-label');
        const sortLabel = document.getElementById('career-job-sort-label');
        const exportTrigger = document.getElementById('career-job-export-trigger');

        if (!searchInput || !tbody || !emptyState || !emptyMessage || !countLabel) {
            return;
        }

        const total = rows.length;
        let debounceTimer = null;
        let currentFilter = 'all';
        let currentDepartment = 'all';
        let currentSort = 'newest';

        const filterLabels = {
            all: 'All',
            draft: 'Draft',
            open: 'Open',
            closed: 'Closed',
        };

        const sortLabels = {
            newest: 'Newest',
            oldest: 'Oldest',
            title: 'Job Title',
            applications: 'Applications',
        };

        const departmentLabels = {
            all: 'All',
        };

        departmentOptions.forEach((option) => {
            departmentLabels[option.dataset.department] = option.textContent.trim();
        });

        const getDate = (row) => new Date(row.dataset.created || 0);
        const getTitle = (row) => row.dataset.title || '';
        const getApplications = (row) => Number(row.dataset.applications || 0);
        const visibleRows = () => rows.filter((row) => !row.classList.contains('d-none'));

        const sortRows = (rowsToSort) => {
            const sorted = [...rowsToSort];

            if (currentSort === 'newest') {
                sorted.sort((a, b) => getDate(b) - getDate(a));
            } else if (currentSort === 'oldest') {
                sorted.sort((a, b) => getDate(a) - getDate(b));
            } else if (currentSort === 'title') {
                sorted.sort((a, b) => getTitle(a).localeCompare(getTitle(b)));
            } else if (currentSort === 'applications') {
                sorted.sort((a, b) => getApplications(b) - getApplications(a));
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
                const matchesDepartment = currentDepartment === 'all' || row.dataset.department === currentDepartment;
                const matches = matchesSearch && matchesStatus && matchesDepartment;

                row.classList.toggle('d-none', !matches);

                if (matches) {
                    matchingRows.push(row);
                }
            });

            sortRows(matchingRows).forEach((row) => tbody.appendChild(row));

            emptyState.classList.toggle('d-none', matchingRows.length > 0);
            emptyMessage.textContent = total === 0 ? 'No jobs listed yet.' : 'No jobs match your search.';
            countLabel.textContent = total === 0 ? '0 jobs' : `${matchingRows.length} of ${total} jobs`;
        };

        filterOptions.forEach((option) => {
            option.addEventListener('click', function (event) {
                event.preventDefault();
                currentFilter = this.dataset.filter;
                filterLabel.textContent = filterLabels[currentFilter] || 'All';
                applyFilters();
            });
        });

        departmentOptions.forEach((option) => {
            option.addEventListener('click', function (event) {
                event.preventDefault();
                currentDepartment = this.dataset.department;
                departmentLabel.textContent = departmentLabels[currentDepartment] || 'All';
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
                    ['Job', 'Department', 'Location', 'Type', 'Applications', 'Closing', 'Status']
                ];

                visibleRows().forEach((row) => {
                    const cells = row.querySelectorAll('td');

                    if (cells.length < 7) {
                        return;
                    }

                    csvRows.push([
                        cells[0].textContent.trim(),
                        cells[1].textContent.trim(),
                        cells[2].textContent.trim(),
                        cells[3].textContent.trim(),
                        cells[4].textContent.trim(),
                        cells[5].textContent.trim(),
                        cells[6].textContent.trim(),
                    ]);
                });

                const csvContent = csvRows
                    .map((columns) => columns.map((value) => `"${String(value).replace(/"/g, '""')}"`).join(','))
                    .join('\n');
                const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
                const url = URL.createObjectURL(blob);
                const link = document.createElement('a');

                link.href = url;
                link.download = 'career-jobs.csv';
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
