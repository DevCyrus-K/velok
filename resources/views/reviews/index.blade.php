@extends('layouts.vertical', ['title' => 'Reviews'])

@section('css')
<style>
    .review-toolbar > * {
        flex: 0 1 auto;
    }

    .review-search-bar {
        min-width: 220px;
    }

    .review-table-wrap {
        max-height: clamp(420px, 62vh, 720px);
        overflow: auto;
    }

    .review-table-wrap .table {
        min-width: 1020px;
    }

    .review-table-wrap thead th {
        position: sticky;
        top: 0;
        z-index: 2;
        background: var(--bs-body-bg);
        box-shadow: inset 0 -1px 0 var(--bs-border-color);
    }

    .review-photo-thumb {
        width: 44px;
        height: 44px;
        object-fit: cover;
    }

    @media (max-width: 767.98px) {
        .review-toolbar > *,
        .review-toolbar .dropdown,
        .review-toolbar .dropdown > .btn,
        .review-toolbar .review-search-bar,
        .review-toolbar .review-search-bar input {
            width: 100%;
        }

        .review-toolbar .review-search-bar {
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
                        <p class="card-title mb-0">Total Reviews</p>
                    </div>
                    <div class="ms-auto">
                        <span class="btn btn-primary avatar-md rounded-circle d-flex align-items-center justify-content-center">
                            <i class="fs-5 text-white" data-lucide="star"></i>
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
                        <p class="text-dark fw-semibold fs-26 mb-1">{{ number_format($summary['pending'] ?? 0) }}</p>
                        <p class="card-title mb-0">Pending</p>
                    </div>
                    <div class="ms-auto">
                        <span class="btn btn-warning avatar-md rounded-circle d-flex align-items-center justify-content-center">
                            <i class="fs-5 text-white" data-lucide="clock"></i>
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
                        <p class="text-dark fw-semibold fs-26 mb-1">{{ number_format($summary['approved'] ?? 0) }}</p>
                        <p class="card-title mb-0">Approved</p>
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
                        <p class="text-dark fw-semibold fs-26 mb-1">{{ number_format($summary['declined'] ?? 0) }}</p>
                        <p class="card-title mb-0">Declined</p>
                    </div>
                    <div class="ms-auto">
                        <span class="btn btn-danger avatar-md rounded-circle d-flex align-items-center justify-content-center">
                            <i class="fs-5 text-white" data-lucide="x-circle"></i>
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
                        <p class="text-dark fw-semibold fs-26 mb-1">{{ $summary['average_rating'] ?? '0.0' }}</p>
                        <p class="card-title mb-0">Average Rating</p>
                    </div>
                    <div class="ms-auto">
                        <span class="btn btn-info avatar-md rounded-circle d-flex align-items-center justify-content-center">
                            <i class="fs-5 text-white" data-lucide="sparkles"></i>
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
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 review-toolbar">
                    <div>
                        <h5 class="card-title mb-1">Customer Reviews</h5>
                    </div>

                    <div class="dropdown">
                        <a aria-expanded="false" class="dropdown-toggle btn btn-sm btn-outline-light rounded" data-bs-toggle="dropdown" href="#">
                            Filter: <span id="review-filter-label">All</span>
                        </a>
                        <div class="dropdown-menu">
                            <a class="dropdown-item review-filter-option" data-filter="all" href="#!">All</a>
                            @foreach($statusOptions as $status => $label)
                                <a class="dropdown-item review-filter-option" data-filter="{{ $status }}" href="#!">{{ $label }}</a>
                            @endforeach
                        </div>
                    </div>

                    <div class="dropdown">
                        <a aria-expanded="false" class="dropdown-toggle btn btn-sm btn-outline-light rounded" data-bs-toggle="dropdown" href="#">
                            Rating: <span id="review-rating-filter-label">All</span>
                        </a>
                        <div class="dropdown-menu">
                            <a class="dropdown-item review-rating-filter-option" data-rating="all" href="#!">All ratings</a>
                            @foreach($reviews->pluck('rating')->filter()->unique()->sortDesc()->values() as $rating)
                                @php $ratingLabel = number_format((float) $rating, fmod((float) $rating, 1.0) === 0.0 ? 0 : 1); @endphp
                                <a class="dropdown-item review-rating-filter-option" data-rating="{{ $ratingLabel }}" href="#!">{{ $ratingLabel }} / 5</a>
                            @endforeach
                        </div>
                    </div>

                    <div class="search-bar ms-auto review-search-bar">
                        <span style="top: 2px;"><i data-lucide="search"></i></span>
                        <input class="form-control form-control-sm" id="review-search" placeholder="Search reviews..." type="search" />
                    </div>

                    <div class="dropdown">
                        <a aria-expanded="false" class="dropdown-toggle btn btn-sm btn-outline-light rounded" data-bs-toggle="dropdown" href="#">
                            Sort: <span id="review-sort-label">Newest</span>
                        </a>
                        <div class="dropdown-menu dropdown-menu-end">
                            <a class="dropdown-item review-sort-option" data-sort="newest" href="#!">Newest First</a>
                            <a class="dropdown-item review-sort-option" data-sort="oldest" href="#!">Oldest First</a>
                            <a class="dropdown-item review-sort-option" data-sort="name" href="#!">Reviewer Name</a>
                            <a class="dropdown-item review-sort-option" data-sort="rating" href="#!">Highest Rating</a>
                        </div>
                    </div>

                    <div class="dropdown">
                        <a aria-expanded="false" class="dropdown-toggle btn btn-sm btn-outline-light rounded" data-bs-toggle="dropdown" href="#">
                            Reports
                        </a>
                        <div class="dropdown-menu dropdown-menu-end">
                            <a class="dropdown-item" href="#!" id="review-export-trigger">Export CSV</a>
                        </div>
                    </div>
                </div>
            </div>

            <div>
                <div class="table-responsive table-centered review-table-wrap">
                    <table class="table table-hover align-middle text-nowrap mb-0">
                        <thead class="text-uppercase fs-12">
                            <tr>
                                <th class="border-0 py-2 text-dark">Reviewer</th>
                                <th class="border-0 py-2 text-dark">Rating</th>
                                <th class="border-0 py-2 text-dark">Review</th>
                                <th class="border-0 py-2 text-dark">Submitted</th>
                                <th class="border-0 py-2 text-dark">Status</th>
                                <th class="border-0 py-2 text-dark text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="review-table-body">
                            @foreach($reviews as $review)
                                @php
                                    $reviewDate = $review->submitted_at ?? $review->created_at;
                                @endphp
                                <tr data-created="{{ $reviewDate?->format('c') ?? '' }}"
                                    data-name="{{ strtolower($review->reviewer_name) }}"
                                    data-rating="{{ $review->ratingLabel() }}"
                                    data-review-row
                                    data-search="{{ strtolower(implode(' ', [$review->reference(), $review->reviewer_name, $review->reviewer_role, $review->ratingLabel(), $review->review_message, $review->statusLabel(), $review->source_page])) }}"
                                    data-status="{{ $review->status }}">
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <img alt="{{ $review->reviewer_name }}" class="review-photo-thumb rounded-circle" src="{{ $review->photoUrl() }}">
                                            <div>
                                                <a class="fw-semibold link-dark" href="{{ route('reviews.show', $review) }}">{{ $review->reviewer_name }}</a>
                                                <small class="text-muted d-block">{{ $review->reviewer_role }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge badge-soft-info">{{ $review->ratingLabel() }} / 5</span>
                                    </td>
                                    <td>
                                        <span>{{ Str::limit($review->review_message, 80) }}</span>
                                        <small class="text-muted d-block">{{ $review->reference() }}</small>
                                    </td>
                                    <td>
                                        {{ $reviewDate?->format('d M, Y') ?? 'N/A' }}
                                        <small class="text-muted">{{ $reviewDate?->format('h:i A') ?? '' }}</small>
                                    </td>
                                    <td>
                                        <span class="badge badge-soft-{{ $review->statusBadgeClass() }}">{{ $review->statusLabel() }}</span>
                                    </td>
                                    <td class="text-end">
                                        <div class="d-inline-flex flex-wrap gap-1 justify-content-end">
                                            <a class="btn btn-icon btn-sm btn-soft-primary" href="{{ route('reviews.show', $review) }}" title="View">
                                                <i class="align-middle" data-lucide="eye"></i>
                                            </a>
                                            @if($review->status !== \App\Models\Review::STATUS_APPROVED)
                                                <form action="{{ route('reviews.approve', $review) }}" class="d-inline-flex" data-confirm-button-class="btn-success" data-confirm-cancel-text="No, Keep" data-confirm-confirm-text="Yes, Approve" data-confirm-message="Do you want to approve this review for publishing?" data-confirm-modal data-confirm-title="Approve review?" method="POST">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button class="btn btn-icon btn-sm btn-soft-success" title="Approve" type="submit">
                                                        <i class="align-middle" data-lucide="check"></i>
                                                    </button>
                                                </form>
                                            @endif
                                            @if($review->status !== \App\Models\Review::STATUS_DECLINED)
                                                <form action="{{ route('reviews.decline', $review) }}" class="d-inline-flex" data-confirm-button-class="btn-warning" data-confirm-cancel-text="No, Keep" data-confirm-confirm-text="Yes, Decline" data-confirm-message="Do you want to decline this review?" data-confirm-modal data-confirm-title="Decline review?" method="POST">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button class="btn btn-icon btn-sm btn-soft-warning" title="Decline" type="submit">
                                                        <i class="align-middle" data-lucide="x"></i>
                                                    </button>
                                                </form>
                                            @endif
                                            <form action="{{ route('reviews.destroy', $review) }}" class="d-inline-flex" data-delete-confirm data-delete-message="Do you want to delete this review?" data-delete-title="Delete review?" method="POST">
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
                            <tr class="{{ $reviews->isNotEmpty() ? 'd-none' : '' }}" id="review-empty-state">
                                <td class="text-center text-muted py-4" colspan="6" id="review-empty-message">
                                    {{ $reviews->isEmpty() ? 'No reviews submitted yet.' : 'No reviews match your search.' }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="card-body border-top py-3">
                    <small class="text-muted" id="review-count">{{ $reviews->count() }} reviews</small>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const searchInput = document.getElementById('review-search');
        const rows = Array.from(document.querySelectorAll('[data-review-row]'));
        const tbody = document.getElementById('review-table-body');
        const emptyState = document.getElementById('review-empty-state');
        const emptyMessage = document.getElementById('review-empty-message');
        const countLabel = document.getElementById('review-count');
        const filterOptions = document.querySelectorAll('.review-filter-option');
        const ratingOptions = document.querySelectorAll('.review-rating-filter-option');
        const sortOptions = document.querySelectorAll('.review-sort-option');
        const filterLabel = document.getElementById('review-filter-label');
        const ratingLabel = document.getElementById('review-rating-filter-label');
        const sortLabel = document.getElementById('review-sort-label');
        const exportTrigger = document.getElementById('review-export-trigger');

        if (!searchInput || !tbody || !emptyState || !emptyMessage || !countLabel) {
            return;
        }

        const total = rows.length;
        let debounceTimer = null;
        let currentFilter = 'all';
        let currentRating = 'all';
        let currentSort = 'newest';

        const filterLabels = {
            all: 'All',
            pending: 'Pending',
            approved: 'Approved',
            declined: 'Declined',
        };

        const sortLabels = {
            newest: 'Newest',
            oldest: 'Oldest',
            name: 'Reviewer',
            rating: 'Rating',
        };

        const ratingLabels = {
            all: 'All',
        };

        ratingOptions.forEach((option) => {
            ratingLabels[option.dataset.rating] = option.textContent.trim();
        });

        const getDate = (row) => new Date(row.dataset.created || 0);
        const visibleRows = () => rows.filter((row) => !row.classList.contains('d-none'));

        const sortRows = (rowsToSort) => {
            const sorted = [...rowsToSort];

            if (currentSort === 'newest') {
                sorted.sort((a, b) => getDate(b) - getDate(a));
            } else if (currentSort === 'oldest') {
                sorted.sort((a, b) => getDate(a) - getDate(b));
            } else if (currentSort === 'name') {
                sorted.sort((a, b) => (a.dataset.name || '').localeCompare(b.dataset.name || ''));
            } else if (currentSort === 'rating') {
                sorted.sort((a, b) => Number(b.dataset.rating || 0) - Number(a.dataset.rating || 0));
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
                const matchesRating = currentRating === 'all' || row.dataset.rating === currentRating;
                const matches = matchesSearch && matchesStatus && matchesRating;

                row.classList.toggle('d-none', !matches);

                if (matches) {
                    matchingRows.push(row);
                }
            });

            sortRows(matchingRows).forEach((row) => tbody.appendChild(row));

            emptyState.classList.toggle('d-none', matchingRows.length > 0);
            emptyMessage.textContent = total === 0 ? 'No reviews submitted yet.' : 'No reviews match your search.';
            countLabel.textContent = total === 0 ? '0 reviews' : `${matchingRows.length} of ${total} reviews`;
        };

        filterOptions.forEach((option) => {
            option.addEventListener('click', function (event) {
                event.preventDefault();
                currentFilter = this.dataset.filter;
                filterLabel.textContent = filterLabels[currentFilter] || 'All';
                applyFilters();
            });
        });

        ratingOptions.forEach((option) => {
            option.addEventListener('click', function (event) {
                event.preventDefault();
                currentRating = this.dataset.rating;
                ratingLabel.textContent = ratingLabels[currentRating] || 'All';
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
                    ['Reviewer', 'Role', 'Rating', 'Review', 'Submitted', 'Status']
                ];

                visibleRows().forEach((row) => {
                    const cells = row.querySelectorAll('td');

                    if (cells.length < 5) {
                        return;
                    }

                    csvRows.push([
                        cells[0].querySelector('.fw-semibold')?.textContent.trim() || '',
                        cells[0].querySelector('small')?.textContent.trim() || '',
                        cells[1].textContent.trim(),
                        cells[2].querySelector('span')?.textContent.trim() || '',
                        cells[3].textContent.trim(),
                        cells[4].textContent.trim(),
                    ]);
                });

                const csvContent = csvRows
                    .map((columns) => columns.map((value) => `"${String(value).replace(/"/g, '""')}"`).join(','))
                    .join('\n');
                const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
                const url = URL.createObjectURL(blob);
                const link = document.createElement('a');

                link.href = url;
                link.download = 'reviews.csv';
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
