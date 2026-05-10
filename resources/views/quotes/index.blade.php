@extends('layouts.vertical', ['title' => 'Quotes'])

@section('css')
<style>
    .quote-list-toolbar > * {
        flex: 0 1 auto;
    }

    .quote-search-bar {
        min-width: 220px;
    }

    .quote-table-wrap .table {
        min-width: 980px;
    }

    .quote-table-wrap {
        max-height: clamp(420px, 62vh, 720px);
        overflow: auto;
    }

    .quote-table-wrap thead th {
        position: sticky;
        top: 0;
        z-index: 2;
        background: var(--bs-body-bg);
        box-shadow: inset 0 -1px 0 var(--bs-border-color);
    }

    .quote-row-actions {
        flex-wrap: nowrap !important;
        white-space: nowrap;
    }

    .quote-row-actions form {
        margin: 0;
    }

    .quote-route-text {
        display: inline-block;
        max-width: 220px;
        overflow: hidden;
        text-overflow: ellipsis;
        vertical-align: bottom;
        white-space: nowrap;
    }

    @media (max-width: 767.98px) {
        .quote-list-toolbar > *,
        .quote-list-toolbar .dropdown,
        .quote-list-toolbar .dropdown > .btn,
        .quote-list-toolbar .quote-search-bar,
        .quote-list-toolbar .quote-search-bar input,
        .quote-list-toolbar .btn-success {
            width: 100%;
        }

        .quote-list-toolbar .quote-search-bar {
            margin-left: 0 !important;
        }

        .quote-table-wrap {
            max-height: none;
            overflow: visible;
        }

        .quote-table-wrap .table {
            min-width: 0;
        }

        .quote-table-wrap table,
        .quote-table-wrap thead,
        .quote-table-wrap tbody,
        .quote-table-wrap tr,
        .quote-table-wrap td {
            display: block;
            width: 100%;
        }

        .quote-table-wrap thead {
            display: none;
        }

        .quote-table-wrap tr[data-quote-row] {
            background: var(--bs-body-bg);
            border: 1px solid var(--bs-border-color);
            border-radius: 8px;
            margin: 0.75rem;
            padding: 0.75rem;
        }

        .quote-table-wrap td {
            border: 0;
            padding: 0.35rem 0;
            white-space: normal;
        }

        .quote-table-wrap td::before {
            color: var(--bs-secondary-color);
            content: attr(data-label);
            display: block;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
        }

        .quote-row-actions {
            justify-content: flex-start !important;
        }

        .quote-route-text {
            max-width: 100%;
        }
    }

    @media (min-width: 768px) and (max-width: 1024px) {
        .quote-table-wrap .quote-phone-col,
        .quote-table-wrap .quote-service-col {
            display: none;
        }
    }
</style>
@endsection

@section('content')
@php
    $shortLocation = static function (?string $value): string {
        $text = trim((string) $value);

        if ($text === '') {
            return 'Not set';
        }

        return collect(preg_split('/\s+/', $text) ?: [])
            ->filter()
            ->map(fn (string $word) => Str::length($word) > 10 ? Str::substr($word, 0, 10) . '..' : $word)
            ->implode(' ');
    };
@endphp

<!-- Stat Cards -->
<div class="row">
    <div class="col-md-6 col-xl-3">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center gap-3">
                    <div>
                        <p class="text-dark fw-semibold fs-26 mb-1">{{ $summary['total'] }}</p>
                        <p class="card-title mb-0">Total Quotes</p>
                    </div>
                    <div class="ms-auto">
                        <a class="btn btn-primary avatar-md rounded-circle d-flex align-items-center justify-content-center"
                            href="#!">
                            <i data-lucide="file-text" class="fs-5 text-white"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-xl-3">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center gap-3">
                    <div>
                        <p class="text-dark fw-semibold fs-26 mb-1">{{ $summary['pending'] }}</p>
                        <p class="card-title mb-0">Pending Quotes</p>
                    </div>
                    <div class="ms-auto">
                        <a class="btn btn-warning avatar-md rounded-circle d-flex align-items-center justify-content-center"
                            href="#!">
                            <i data-lucide="clock" class="fs-5 text-white"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-xl-3">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center gap-3">
                    <div>
                        <p class="text-dark fw-semibold fs-26 mb-1">{{ $summary['approved'] }}</p>
                        <p class="card-title mb-0">Approved Quotes</p>
                    </div>
                    <div class="ms-auto">
                        <a class="btn btn-success avatar-md rounded-circle d-flex align-items-center justify-content-center"
                            href="#!">
                            <i data-lucide="check-circle" class="fs-5 text-white"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-xl-3">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center gap-3">
                    <div>
                        <p class="text-dark fw-semibold fs-26 mb-1">{{ $summary['declined'] }}</p>
                        <p class="card-title mb-0">Rejected Quotes</p>
                    </div>
                    <div class="ms-auto">
                        <a class="btn btn-danger avatar-md rounded-circle d-flex align-items-center justify-content-center"
                            href="#!">
                            <i data-lucide="x-circle" class="fs-5 text-white"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Quotes Table -->
<div class="row">
    <div class="col">
        <div class="card">
            <div class="card-body">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 quote-list-toolbar">
                    <div>
                        <h5 class="card-title mb-1">Quotes</h5>
                    </div>
                    <!-- Filter Dropdown -->
                    <div class="dropdown">
                        <a aria-expanded="false" class="dropdown-toggle btn btn-sm btn-outline-light rounded"
                            data-bs-toggle="dropdown" href="#">
                            Filter: <span id="filter-label">All</span>
                        </a>
                        <div class="dropdown-menu">
                            <a class="dropdown-item filter-option" href="#!" data-filter="all">All</a>
                            <a class="dropdown-item filter-option" href="#!" data-filter="pending">Pending</a>
                            <a class="dropdown-item filter-option" href="#!" data-filter="approved">Approved</a>
                            <a class="dropdown-item filter-option" href="#!" data-filter="declined">Rejected</a>
                        </div>
                    </div>
                    <div class="dropdown">
                        <a aria-expanded="false" class="dropdown-toggle btn btn-sm btn-outline-light rounded"
                            data-bs-toggle="dropdown" href="#">
                            Service: <span id="service-filter-label">All</span>
                        </a>
                        <div class="dropdown-menu">
                            <a class="dropdown-item service-filter-option" href="#!" data-service="all">All services</a>
                            @foreach ($serviceFilters as $serviceFilter)
                                <a class="dropdown-item service-filter-option" href="#!" data-service="{{ Str::slug($serviceFilter) }}">{{ $serviceFilter }}</a>
                            @endforeach
                        </div>
                    </div>
                    <div class="search-bar ms-auto quote-search-bar">
                        <span style="top: 2px;"><i data-lucide="search"></i></span>
                        <input class="form-control form-control-sm" id="search" placeholder="Search quotes..."
                            type="search" />
                    </div>
                    <!-- Sort Dropdown -->
                    <div class="dropdown">
                        <a aria-expanded="false" class="dropdown-toggle btn btn-sm btn-outline-light rounded"
                            data-bs-toggle="dropdown" href="#">
                            Sort: <span id="sort-label">Newest</span>
                        </a>
                        <div class="dropdown-menu dropdown-menu-end">
                            <a class="dropdown-item sort-option" href="#!" data-sort="newest">Newest First</a>
                            <a class="dropdown-item sort-option" href="#!" data-sort="oldest">Oldest First</a>
                            <a class="dropdown-item sort-option" href="#!" data-sort="customer">Customer Name</a>
                        </div>
                    </div>
                    <div class="dropdown">
                        <a aria-expanded="false" class="dropdown-toggle btn btn-sm btn-outline-light rounded"
                            data-bs-toggle="dropdown" href="#">
                            Reports
                        </a>
                        <div class="dropdown-menu dropdown-menu-end">
                            <a class="dropdown-item" href="#!">Export</a>
                            <a class="dropdown-item" href="#!">Import</a>
                        </div>
                    </div>
                    <div>
                        <a class="btn btn-sm btn-success" href="{{ route('quotes.create') }}">
                            Create Quote
                        </a>
                    </div>
                </div>
            </div>
            <div>
                <div class="table-responsive table-centered quote-table-wrap">
                    <table class="table table-striped text-nowrap mb-0">
                        <thead class="text-uppercase fs-12">
                            <tr>
                                <th class="border-0 py-2 text-dark">Quote ID</th>
                                <th class="border-0 py-2 text-dark">Customer</th>
                                <th class="border-0 py-2 text-dark quote-phone-col">Phone</th>
                                <th class="border-0 py-2 text-dark">Created Date</th>
                                <th class="border-0 py-2 text-dark">Move Date</th>
                                <th class="border-0 py-2 text-dark">Route</th>
                                <th class="border-0 py-2 text-dark quote-service-col">Service</th>
                                <th class="border-0 py-2 text-dark">Status</th>
                                <th class="border-0 py-2 text-dark">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($quotes as $quote)
                                @php
                                    $requestGroup = $quote->statusGroup();
                                    $quotation = $quote->quote ?: $quote->quotation;
                                    $canDeleteQuotation = $quotation && ! $quote->invoice;
                                    $canDeleteRequest = ! $quotation && $requestGroup !== 'approved';
                                    $quoteIdUrl = $quotation ? route('quotes.download', $quote) : route('quotes.edit', $quote);
                                    $quoteIdTitle = $quotation ? 'Download Quote' : 'Edit Request';
                                    $routeTitle = trim(collect([$quote->moving_from, $quote->moving_to])->filter()->implode(' to '));
                                    $routeLabel = $shortLocation($quote->moving_from) . ' to ' . $shortLocation($quote->moving_to);
                                    $whatsappUrl = $quote->whatsapp_url ?: $quote->whatsappUrl();
                                    $invoiceRoute = $quote->invoice
                                        ? route('invoice.details', ['invoice' => $quote->invoice->id])
                                        : route('invoice.create', ['quote' => $quote->id]);
                                    $invoiceActionLabel = $quote->invoice ? 'View invoice' : 'Create invoice';
                                @endphp
                                <tr data-quote-row
                                    data-search="{{ strtolower(implode(' ', [$quote->reference(), $quote->full_name, $quote->email, $quote->phone, $quote->moving_from, $quote->moving_to, $quote->serviceTypeLabel(), $quote->move_size, $quote->statusLabel()])) }}"
                                    data-created-at="{{ $quote->created_at?->timestamp ?? 0 }}"
                                    data-customer="{{ Str::lower($quote->full_name ?? '') }}"
                                    data-service="{{ Str::slug($quote->serviceTypeLabel()) }}"
                                    data-status-group="{{ $quote->statusGroup() }}">
                                    <td data-label="Quote ID">
                                        <a class="fw-medium" href="{{ $quoteIdUrl }}" title="{{ $quoteIdTitle }} {{ $quote->reference() }}">{{ $quote->reference() }}</a>
                                    </td>
                                    <td data-label="Customer">
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-xs rounded-circle bg-primary-subtle text-primary d-inline-flex align-items-center justify-content-center fw-semibold me-2">
                                                {{ $quote->initials() }}
                                            </div>
                                            <div>
                                                <h5 class="fs-14 m-0 fw-normal">
                                                    <a class="link-dark" href="{{ route('quotes.show', $quote) }}">{{ $quote->full_name }}</a>
                                                </h5>
                                                <small class="text-muted d-block">
                                                    <a class="text-muted text-decoration-none" href="mailto:{{ $quote->email }}">{{ $quote->email }}</a>
                                                </small>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="quote-phone-col" data-label="Phone">
                                        <div class="d-flex flex-wrap align-items-center gap-1">
                                            <a class="btn btn-icon btn-sm btn-soft-primary" href="{{ $quote->telLink() }}" data-bs-toggle="tooltip" data-bs-title="Call" aria-label="Call {{ $quote->full_name }}">
                                                <i data-lucide="phone" class="align-middle"></i>
                                            </a>
                                            @if($whatsappUrl)
                                                <a class="btn btn-icon btn-sm btn-soft-success" href='{{ $whatsappUrl }}' target="_blank" rel="noopener" data-bs-toggle="tooltip" data-bs-title="WhatsApp" aria-label="WhatsApp {{ $quote->full_name }}">
                                                    <x-icons.whatsapp class="align-middle" />
                                                </a>
                                            @endif
                                            <span class="text-muted ms-1">{{ $quote->phone }}</span>
                                        </div>
                                    </td>
                                    <td data-label="Created">
                                        {{ $quote->created_at?->format('d M, Y') ?? 'N/A' }}
                                        <small>{{ $quote->created_at?->format('h:i A') ?? '' }}</small>
                                    </td>
                                    <td data-label="Move Date">{{ $quote->move_date?->format('d M, Y') ?? 'Not set' }}</td>
                                    <td data-label="Route">
                                        <span class="quote-route-text" title="{{ $routeTitle ?: 'Not set' }}">{{ $routeLabel }}</span>
                                    </td>
                                    <td class="quote-service-col" data-label="Service">
                                        <div>{{ $quote->serviceTypeLabel() }}</div>
                                        <small class="text-muted">{{ $quote->move_size ?: 'Size not set' }}</small>
                                    </td>
                                    <td data-label="Status">
                                        <span class="badge badge-soft-{{ $quote->statusBadgeClass() }}">{{ $quote->statusLabel() }}</span>
                                    </td>
                                    <td data-label="Action">
                                        <div class="d-inline-flex align-items-center gap-1 justify-content-end quote-row-actions">
                                            @if($quotation)
                                                <a class="btn btn-icon btn-sm btn-soft-primary" href="{{ route('quotations.show', $quotation) }}" data-bs-toggle="tooltip" data-bs-title="View" aria-label="View quote {{ $quote->reference() }}">
                                                    <i data-lucide="eye" class="align-middle"></i>
                                                </a>
                                                <a class="btn btn-icon btn-sm btn-soft-info" href="{{ route('quotes.download', $quote) }}" data-bs-toggle="tooltip" data-bs-title="Download" aria-label="Download quote {{ $quote->reference() }}">
                                                    <i data-lucide="download" class="align-middle"></i>
                                                </a>
                                                <a class="btn btn-icon btn-sm btn-soft-secondary" href="{{ $invoiceRoute }}" data-bs-toggle="tooltip" data-bs-title="{{ $invoiceActionLabel }}" aria-label="{{ $invoiceActionLabel }} for {{ $quote->reference() }}">
                                                    <i data-lucide="receipt-text" class="align-middle"></i>
                                                </a>
                                                @if($canDeleteQuotation)
                                                    <form action="{{ route('quotations.destroy', $quotation) }}" data-delete-confirm data-delete-message="Do you want to delete this quotation?" data-delete-title="Delete quotation?" method="POST" class="d-inline-flex">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button class="btn btn-icon btn-sm btn-soft-danger" type="submit" data-bs-toggle="tooltip" data-bs-title="Delete" aria-label="Delete quote {{ $quote->reference() }}">
                                                            <i data-lucide="trash-2" class="align-middle"></i>
                                                        </button>
                                                    </form>
                                                @endif
                                            @else
                                                <a class="btn btn-icon btn-sm btn-soft-warning" href="{{ route('quotes.edit', $quote) }}" data-bs-toggle="tooltip" data-bs-title="Edit" aria-label="Edit request {{ $quote->reference() }}">
                                                    <i data-lucide="edit-3" class="align-middle"></i>
                                                </a>
                                                <a class="btn btn-icon btn-sm btn-soft-secondary" href="{{ $invoiceRoute }}" data-bs-toggle="tooltip" data-bs-title="{{ $invoiceActionLabel }}" aria-label="{{ $invoiceActionLabel }} for {{ $quote->reference() }}">
                                                    <i data-lucide="receipt-text" class="align-middle"></i>
                                                </a>
                                                @if($canDeleteRequest)
                                                    <form action="{{ route('quotes.destroy', $quote) }}" data-delete-confirm data-delete-message="Do you want to delete this quote request?" data-delete-title="Delete quote request?" method="POST" class="d-inline-flex">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button class="btn btn-icon btn-sm btn-soft-danger" type="submit" data-bs-toggle="tooltip" data-bs-title="Delete" aria-label="Delete request {{ $quote->reference() }}">
                                                            <i data-lucide="trash-2" class="align-middle"></i>
                                                        </button>
                                                    </form>
                                                @endif
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr id="quote-empty-state">
                                    <td class="text-center text-muted py-4" colspan="9">No quotes found.</td>
                                </tr>
                            @endforelse
                            @if ($quotes->isNotEmpty())
                                <tr class="d-none" id="quote-empty-state">
                                    <td class="text-center text-muted py-4" colspan="9">No quotes match your search.</td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
                <div class="card-body border-top py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <small class="text-muted" id="quote-count">{{ $summary['total'] }} quotes</small>
                        <div>
                            {{ $quotes->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const searchInput = document.getElementById('search');
        const rows = Array.from(document.querySelectorAll('[data-quote-row]'));
        const emptyState = document.getElementById('quote-empty-state');
        const countLabel = document.getElementById('quote-count');
        const filterOptions = document.querySelectorAll('.filter-option');
        const serviceFilterOptions = document.querySelectorAll('.service-filter-option');
        const sortOptions = document.querySelectorAll('.sort-option');
        const filterLabel = document.getElementById('filter-label');
        const serviceFilterLabel = document.getElementById('service-filter-label');
        const sortLabel = document.getElementById('sort-label');
        const tableBody = rows[0]?.parentElement;

        if (!searchInput || rows.length === 0 || !emptyState || !countLabel || !tableBody) {
            return;
        }

        const total = rows.length;
        let debounceTimer = null;
        let currentFilter = 'all';
        let currentService = 'all';
        let currentSort = 'newest';

        // Map filter/sort values to display labels
        const filterLabels = {
            'all': 'All',
            'pending': 'Pending',
            'approved': 'Approved',
            'declined': 'Rejected'
        };

        const sortLabels = {
            'newest': 'Newest',
            'oldest': 'Oldest',
            'customer': 'Customer'
        };

        const serviceLabels = {
            all: 'All'
        };

        serviceFilterOptions.forEach(option => {
            serviceLabels[option.dataset.service] = option.textContent.trim();
        });

        // Extract status from row
        const getStatusFromRow = (row) => {
            const statusBadge = row.querySelector('[class*="badge-soft-"]');
            if (!statusBadge) return 'pending';
            const text = (row.dataset.statusGroup || statusBadge.textContent).trim().toLowerCase();
            if (text === 'approved') return 'approved';
            if (text === 'declined') return 'declined';
            return 'pending';
        };

        // Extract stable sort values from row metadata.
        const getCreatedAtFromRow = (row) => Number.parseInt(row.dataset.createdAt || '0', 10) || 0;

        const getCustomerFromRow = (row) => {
            return row.dataset.customer || '';
        };

        // Sort rows
        const sortRows = (rowsToSort) => {
            const sorted = [...rowsToSort];
            if (currentSort === 'newest') {
                sorted.sort((a, b) => getCreatedAtFromRow(b) - getCreatedAtFromRow(a));
            } else if (currentSort === 'oldest') {
                sorted.sort((a, b) => getCreatedAtFromRow(a) - getCreatedAtFromRow(b));
            } else if (currentSort === 'customer') {
                sorted.sort((a, b) => getCustomerFromRow(a).localeCompare(getCustomerFromRow(b)));
            }
            return sorted;
        };

        // Apply filters and search
        const applyFilters = () => {
            const query = searchInput.value.trim().toLowerCase();
            let visibleCount = 0;
            const visibleRows = [];

            rows.forEach((row) => {
                const haystack = (row.dataset.search || '').toLowerCase();
                const status = getStatusFromRow(row);
                const service = row.dataset.service || 'all';
                
                const matchesSearch = query === '' || haystack.includes(query);
                const matchesFilter = currentFilter === 'all' || status === currentFilter;
                const matchesService = currentService === 'all' || service === currentService;
                const matches = matchesSearch && matchesFilter && matchesService;

                if (matches) {
                    visibleCount += 1;
                    visibleRows.push(row);
                }

                row.classList.toggle('d-none', !matches);
            });

            // Apply sorting to visible rows
            const sortedRows = sortRows(visibleRows);
            
            // Reorder DOM elements
            sortedRows.forEach(row => {
                tableBody.appendChild(row);
            });

            emptyState.classList.toggle('d-none', visibleCount > 0);
            countLabel.textContent = `${visibleCount} of ${total} quotes`;
        };

        // Filter option handlers
        filterOptions.forEach(option => {
            option.addEventListener('click', function (e) {
                e.preventDefault();
                currentFilter = this.dataset.filter;
                filterLabel.textContent = filterLabels[currentFilter];
                applyFilters();
            });
        });

        serviceFilterOptions.forEach(option => {
            option.addEventListener('click', function (e) {
                e.preventDefault();
                currentService = this.dataset.service;
                serviceFilterLabel.textContent = serviceLabels[currentService] || 'All';
                applyFilters();
            });
        });

        // Sort option handlers
        sortOptions.forEach(option => {
            option.addEventListener('click', function (e) {
                e.preventDefault();
                currentSort = this.dataset.sort;
                sortLabel.textContent = sortLabels[currentSort];
                applyFilters();
            });
        });

        // Search handler
        searchInput.addEventListener('input', function () {
            window.clearTimeout(debounceTimer);
            debounceTimer = window.setTimeout(applyFilters, 180);
        });

        applyFilters();
    });
</script>
@endsection
