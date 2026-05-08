@extends('layouts.vertical', ['title' => 'Invoices'])

@section('css')
<style>
    .invoice-list-toolbar > * {
        flex: 0 1 auto;
    }

    .invoice-row-actions .dropdown-menu form {
        margin: 0;
    }

    .invoice-row-actions .dropdown-item {
        align-items: center;
        display: flex;
        gap: 0.5rem;
    }

    .invoice-row-actions .dropdown-item i {
        flex: 0 0 auto;
    }

    @media (max-width: 767.98px) {
        .invoice-list-toolbar > *,
        .invoice-list-toolbar .dropdown,
        .invoice-list-toolbar .dropdown > .btn,
        .invoice-list-toolbar .search-bar,
        .invoice-list-toolbar .search-bar input,
        .invoice-list-toolbar > .btn {
            width: 100%;
        }

        .invoice-list-toolbar .search-bar {
            margin-left: 0 !important;
        }

        .invoice-table-wrap {
            overflow: visible;
        }

        .invoice-table-wrap table,
        .invoice-table-wrap thead,
        .invoice-table-wrap tbody,
        .invoice-table-wrap tr,
        .invoice-table-wrap td {
            display: block;
            width: 100%;
        }

        .invoice-table-wrap thead {
            display: none;
        }

        .invoice-table-wrap tr[data-invoice-row] {
            background: var(--bs-body-bg);
            border: 1px solid var(--bs-border-color);
            border-radius: 8px;
            margin: 0.75rem;
            padding: 0.75rem;
        }

        .invoice-table-wrap td {
            border: 0;
            padding: 0.35rem 0;
            white-space: normal;
        }

        .invoice-table-wrap td::before {
            color: var(--bs-secondary-color);
            content: attr(data-label);
            display: block;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
        }

        .invoice-row-actions {
            justify-content: flex-start !important;
        }
    }

    @media (min-width: 768px) and (max-width: 1024px) {
        .invoice-table-wrap .invoice-due-col,
        .invoice-table-wrap .invoice-payment-col {
            display: none;
        }
    }
</style>
@endsection

@section('content')
@php
    $invoices = collect($invoices ?? []);
    $invoiceCount = $invoices->count();
    $firstInvoice = $invoices->first();
    $fallbackDetailsRoute = $firstInvoice ? route('invoice.details', ['invoice' => $firstInvoice->id]) : route('invoice.details');
@endphp

<div class="row">
    <div class="col">
        <div class="card">
            <div class="card-body">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 invoice-list-toolbar">
                    <div>
                        <h5 class="card-title mb-1">My Invoices</h5>
                        <p class="text-muted mb-0">Customer payment records</p>
                    </div>

                    <div class="dropdown">
                        <a aria-expanded="false" class="dropdown-toggle btn btn-sm btn-outline-light rounded"
                            data-bs-toggle="dropdown" href="#">
                            Filter: <span id="invoice-filter-label">All</span>
                        </a>
                        <div class="dropdown-menu">
                            <a class="dropdown-item invoice-filter-option" data-filter="all" href="#!">All</a>
                            <a class="dropdown-item invoice-filter-option" data-filter="draft" href="#!">Draft</a>
                            <a class="dropdown-item invoice-filter-option" data-filter="pending" href="#!">Pending</a>
                            <a class="dropdown-item invoice-filter-option" data-filter="sent" href="#!">Sent</a>
                            <a class="dropdown-item invoice-filter-option" data-filter="overdue" href="#!">Overdue</a>
                            <a class="dropdown-item invoice-filter-option" data-filter="void" href="#!">Void</a>
                            <a class="dropdown-item invoice-filter-option" data-filter="cancelled" href="#!">Cancelled</a>
                            <a class="dropdown-item invoice-filter-option" data-filter="failed" href="#!">Failed</a>
                            <a class="dropdown-item invoice-filter-option" data-filter="paid" href="#!">Paid</a>
                            <a class="dropdown-item invoice-filter-option" data-filter="unpaid" href="#!">Unpaid</a>
                        </div>
                    </div>

                    <div class="search-bar ms-auto">
                        <span style="top: 2px;"><i data-lucide="search"></i></span>
                        <input class="form-control form-control-sm" id="invoice-search" placeholder="Search invoices..."
                            type="search" />
                    </div>

                    <div class="dropdown">
                        <a aria-expanded="false" class="dropdown-toggle btn btn-sm btn-outline-light rounded"
                            data-bs-toggle="dropdown" href="#">
                            Sort: <span id="invoice-sort-label">Newest</span>
                        </a>
                        <div class="dropdown-menu dropdown-menu-end">
                            <a class="dropdown-item invoice-sort-option" data-sort="newest" href="#!">Newest First</a>
                            <a class="dropdown-item invoice-sort-option" data-sort="oldest" href="#!">Oldest First</a>
                            <a class="dropdown-item invoice-sort-option" data-sort="customer" href="#!">Customer Name</a>
                            <a class="dropdown-item invoice-sort-option" data-sort="highest" href="#!">Highest Amount</a>
                            <a class="dropdown-item invoice-sort-option" data-sort="due" href="#!">Due Soonest</a>
                        </div>
                    </div>

                    <a class="btn btn-sm btn-success" href="{{ route('invoice.create') }}">
                        New Invoice
                    </a>
                </div>
            </div>

            <div>
                <div class="table-responsive table-centered invoice-table-wrap">
                    <table class="table table-striped text-nowrap mb-0">
                        <thead class="text-uppercase fs-12">
                            <tr>
                                <th class="border-0 py-2 text-dark">Invoice ID</th>
                                <th class="border-0 py-2 text-dark">Customer</th>
                                <th class="border-0 py-2 text-dark">Invoice Date</th>
                                <th class="border-0 py-2 text-dark invoice-due-col">Due Date</th>
                                <th class="border-0 py-2 text-dark">Amount</th>
                                <th class="border-0 py-2 text-dark">Payment Status</th>
                                <th class="border-0 py-2 text-dark invoice-payment-col">Via</th>
                                <th class="border-0 py-2 text-dark">Action</th>
                            </tr>
                        </thead>
                        <tbody id="invoice-table-body">
                            @foreach ($invoices as $invoice)
                                @php
                                    $invoiceDate = $invoice->invoice_date ?? $invoice->created_at;
                                    $createdLabel = $invoiceDate?->format('d M, Y') ?? 'N/A';
                                    $createdTime = $invoice->created_at?->format('h:i A') ?? '';
                                    $dueLabel = $invoice->due_date?->format('d M, Y') ?? 'N/A';
                                    $detailRoute = route('invoice.details', ['invoice' => $invoice->id]);
                                    $invoicePreviewId = 'invoice-' . $invoice->id;
                                    $statusKey = Str::lower((string) ($invoice->status ?: 'draft'));
                                    $paymentMethod = $invoice->paymentMethodLabel();
                                    $canEditInvoice = $invoice->status === \App\Models\Invoice::STATUS_DRAFT;
                                    $canDeleteInvoice = true;
                                    $canMarkInvoicePaid = in_array($invoice->status, [
                                        \App\Models\Invoice::STATUS_SENT,
                                        \App\Models\Invoice::STATUS_OVERDUE,
                                        \App\Models\Invoice::STATUS_UNPAID,
                                        \App\Models\Invoice::STATUS_PENDING,
                                        \App\Models\Invoice::STATUS_FAILED,
                                    ], true);
                                    $canMarkInvoiceUnpaid = ! in_array($invoice->status, [
                                        \App\Models\Invoice::STATUS_DRAFT,
                                        \App\Models\Invoice::STATUS_UNPAID,
                                        \App\Models\Invoice::STATUS_VOID,
                                        \App\Models\Invoice::STATUS_CANCELLED,
                                    ], true);
                                    $canSendInvoice = in_array($invoice->status, [
                                        \App\Models\Invoice::STATUS_DRAFT,
                                        \App\Models\Invoice::STATUS_SENT,
                                        \App\Models\Invoice::STATUS_OVERDUE,
                                        \App\Models\Invoice::STATUS_UNPAID,
                                    ], true);
                                    $printRoute = route('invoice.details', ['invoice' => $invoice->id, 'print' => 1]);
                                    $searchText = Str::lower(implode(' ', array_filter([
                                        $invoice->invoice_number,
                                        $invoice->customer_name,
                                        $invoice->customer_email,
                                        $invoice->customer_phone,
                                        $createdLabel,
                                        $dueLabel,
                                        $invoice->statusLabel(),
                                        $paymentMethod,
                                        number_format((float) $invoice->total_amount, 2),
                                    ], fn ($value) => trim((string) $value) !== '')));
                                @endphp
                                <tr data-amount="{{ number_format((float) $invoice->total_amount, 2, '.', '') }}"
                                    data-created="{{ $invoiceDate?->toDateString() ?? '1970-01-01' }}"
                                    data-customer="{{ Str::lower((string) $invoice->customer_name) }}"
                                    data-due="{{ $invoice->due_date?->toDateString() ?? '9999-12-31' }}"
                                    data-invoice-row
                                    data-search="{{ $searchText }}"
                                    data-status="{{ $statusKey }}">
                                    <td data-label="Invoice ID">
                                        <a class="fw-medium" href="{{ $detailRoute }}">{{ $invoice->invoice_number }}</a>
                                    </td>
                                    <td data-label="Customer">
                                        <div class="d-flex align-items-center">
                                            <span class="avatar-xs rounded-circle bg-primary-subtle text-primary fw-semibold d-inline-flex align-items-center justify-content-center me-2">
                                                {{ $invoice->customerInitials() }}
                                            </span>
                                            <div>
                                                <h5 class="fs-14 m-0 fw-normal">{{ $invoice->customer_name }}</h5>
                                                <small class="text-muted">{{ $invoice->customer_phone }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td data-label="Invoice Date">
                                        {{ $createdLabel }}
                                        @if ($createdTime !== '')
                                            <small class="text-muted">{{ $createdTime }}</small>
                                        @endif
                                    </td>
                                    <td class="invoice-due-col" data-label="Due Date">{{ $dueLabel }}</td>
                                    <td data-label="Amount">KES {{ number_format((float) $invoice->total_amount, 2) }}</td>
                                    <td data-label="Status">
                                        <span class="badge badge-soft-{{ $invoice->statusBadgeClass() }}">{{ $invoice->statusLabel() }}</span>
                                    </td>
                                    <td class="invoice-payment-col" data-label="Via">{{ $paymentMethod }}</td>
                                    <td data-label="Action">
                                        <div class="d-flex flex-wrap gap-1 justify-content-end invoice-row-actions">
                                            <a class="btn btn-icon btn-sm btn-soft-primary" href="{{ $detailRoute }}" data-bs-toggle="tooltip" data-bs-title="View"
                                                    data-invoice-preview-id="{{ $invoicePreviewId }}"
                                                    data-invoice-preview-number="{{ $invoice->invoice_number }}"
                                                    data-invoice-preview-open
                                                    data-invoice-preview-url="{{ $detailRoute }}"
                                                    aria-label="View invoice {{ $invoice->invoice_number }}">
                                                <i class="align-middle" data-lucide="eye"></i>
                                            </a>
                                            @if($canSendInvoice)
                                                <form action="{{ route('invoices.send', $invoice) }}" method="POST" class="d-inline-flex">
                                                    @csrf
                                                    <button class="btn btn-icon btn-sm btn-soft-success" type="submit" data-bs-toggle="tooltip" data-bs-title="{{ in_array($invoice->status, [\App\Models\Invoice::STATUS_SENT, \App\Models\Invoice::STATUS_OVERDUE], true) ? 'Resend' : 'Send' }}" aria-label="Send invoice {{ $invoice->invoice_number }}">
                                                        <i class="align-middle" data-lucide="mail"></i>
                                                    </button>
                                                </form>
                                            @endif
                                            <a class="btn btn-icon btn-sm btn-soft-info" href="{{ route('invoices.download', $invoice) }}" data-bs-toggle="tooltip" data-bs-title="Download" aria-label="Download invoice {{ $invoice->invoice_number }}">
                                                <i class="align-middle" data-lucide="download"></i>
                                            </a>
                                            @if($canDeleteInvoice)
                                                <form action="{{ route('invoice.destroy', $invoice) }}" data-delete-confirm data-delete-message="Do you want to delete this invoice?" data-delete-title="Delete invoice?" method="POST" class="d-inline-flex">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button class="btn btn-icon btn-sm btn-soft-danger" type="submit" data-bs-toggle="tooltip" data-bs-title="Delete" aria-label="Delete invoice {{ $invoice->invoice_number }}">
                                                        <i class="align-middle" data-lucide="trash-2"></i>
                                                    </button>
                                                </form>
                                            @endif
                                            <div class="dropdown">
                                                <button class="btn btn-icon btn-sm btn-soft-light" type="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="More actions for invoice {{ $invoice->invoice_number }}">
                                                    <i class="align-middle" data-lucide="ellipsis-vertical"></i>
                                                </button>
                                                <div class="dropdown-menu dropdown-menu-end">
                                                    @if($canEditInvoice)
                                                        <a class="dropdown-item" href="{{ route('invoice.edit', $invoice) }}">
                                                            <i class="icon-sm" data-lucide="edit-3"></i>Edit
                                                        </a>
                                                    @endif
                                                    <a class="dropdown-item" href="{{ $printRoute }}">
                                                        <i class="icon-sm" data-lucide="printer"></i>Print
                                                    </a>
                                                    @if($canMarkInvoicePaid || $canMarkInvoiceUnpaid)
                                                        <div class="dropdown-divider"></div>
                                                    @endif
                                                    @if($canMarkInvoicePaid)
                                                        <form action="{{ route('invoice.mark-paid', $invoice) }}" method="POST">
                                                            @csrf
                                                            @method('PATCH')
                                                            <button class="dropdown-item text-success" type="submit">
                                                                <i class="icon-sm" data-lucide="check-circle"></i>Mark as Paid
                                                            </button>
                                                        </form>
                                                    @endif
                                                    @if($canMarkInvoiceUnpaid)
                                                        <form action="{{ route('invoice.mark-unpaid', $invoice) }}" method="POST">
                                                            @csrf
                                                            @method('PATCH')
                                                            <button class="dropdown-item text-warning" type="submit">
                                                                <i class="icon-sm" data-lucide="credit-card"></i>Mark as Unpaid
                                                            </button>
                                                        </form>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach

                            <tr class="{{ $invoiceCount > 0 ? 'd-none' : '' }}" id="invoice-empty-state">
                                <td class="text-center text-muted py-4" colspan="8">No invoices found.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="align-items-center justify-content-between row g-0 text-center text-sm-start p-3 border-top">
                    <div class="col-sm">
                        <div class="text-muted" id="invoice-count">Showing {{ $invoiceCount }} of {{ $invoiceCount }} invoices</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@foreach ($invoices as $invoice)
    <template data-invoice-preview-template="invoice-{{ $invoice->id }}">
        @include('invoice.partials.preview', ['invoice' => $invoice])
    </template>
@endforeach

<div class="modal fade" id="invoicePreviewModal" tabindex="-1" aria-labelledby="invoicePreviewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="invoicePreviewModalLabel">Invoice Preview</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body bg-light" id="invoicePreviewModalBody"></div>
            <div class="modal-footer">
                <a class="btn btn-primary" href="{{ $fallbackDetailsRoute }}" id="invoicePreviewFullPageLink">Open full page</a>
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const searchInput = document.getElementById('invoice-search');
        const tbody = document.getElementById('invoice-table-body');
        const emptyState = document.getElementById('invoice-empty-state');
        const countLabel = document.getElementById('invoice-count');
        const filterOptions = document.querySelectorAll('.invoice-filter-option');
        const sortOptions = document.querySelectorAll('.invoice-sort-option');
        const filterLabel = document.getElementById('invoice-filter-label');
        const sortLabel = document.getElementById('invoice-sort-label');
        const previewButtons = document.querySelectorAll('[data-invoice-preview-open]');
        const previewBody = document.getElementById('invoicePreviewModalBody');
        const previewTitle = document.getElementById('invoicePreviewModalLabel');
        const previewFullPageLink = document.getElementById('invoicePreviewFullPageLink');
        const previewModalElement = document.getElementById('invoicePreviewModal');
        const previewModal = previewModalElement && window.bootstrap ? new bootstrap.Modal(previewModalElement) : null;

        if (!searchInput || !tbody || !emptyState || !countLabel) {
            return;
        }

        let debounceTimer = null;
        let currentFilter = 'all';
        let currentSort = 'newest';

        const filterLabels = {
            all: 'All',
            draft: 'Draft',
            pending: 'Pending',
            sent: 'Sent',
            overdue: 'Overdue',
            void: 'Void',
            cancelled: 'Cancelled',
            failed: 'Failed',
            paid: 'Paid',
            unpaid: 'Unpaid',
        };

        const sortLabels = {
            newest: 'Newest',
            oldest: 'Oldest',
            customer: 'Customer',
            highest: 'Highest Amount',
            due: 'Due Soonest',
        };

        const getRows = () => Array.from(tbody.querySelectorAll('[data-invoice-row]'));
        const getDateValue = (row, key) => new Date(row.dataset[key] || 0).getTime();
        const getCustomerValue = (row) => row.dataset.customer || '';
        const getAmountValue = (row) => Number.parseFloat(row.dataset.amount || '0');

        const sortRows = (rowsToSort) => {
            const sortedRows = [...rowsToSort];

            if (currentSort === 'newest') {
                sortedRows.sort((a, b) => getDateValue(b, 'created') - getDateValue(a, 'created'));
            } else if (currentSort === 'oldest') {
                sortedRows.sort((a, b) => getDateValue(a, 'created') - getDateValue(b, 'created'));
            } else if (currentSort === 'customer') {
                sortedRows.sort((a, b) => getCustomerValue(a).localeCompare(getCustomerValue(b)));
            } else if (currentSort === 'highest') {
                sortedRows.sort((a, b) => getAmountValue(b) - getAmountValue(a));
            } else if (currentSort === 'due') {
                sortedRows.sort((a, b) => getDateValue(a, 'due') - getDateValue(b, 'due'));
            }

            return sortedRows;
        };

        const applyFilters = () => {
            const rows = getRows();
            const total = rows.length;
            const query = searchInput.value.trim().toLowerCase();
            let visibleCount = 0;
            const visibleRows = [];

            rows.forEach((row) => {
                const haystack = row.dataset.search || '';
                const status = row.dataset.status || 'all';
                const matchesSearch = query === '' || haystack.includes(query);
                const matchesFilter = currentFilter === 'all' || status === currentFilter;
                const matches = matchesSearch && matchesFilter;

                row.classList.toggle('d-none', !matches);

                if (matches) {
                    visibleRows.push(row);
                    visibleCount += 1;
                }
            });

            sortRows(visibleRows).forEach((row) => {
                tbody.appendChild(row);
            });
            tbody.appendChild(emptyState);

            emptyState.classList.toggle('d-none', visibleCount > 0);
            emptyState.querySelector('td').textContent = total === 0
                ? 'No invoices found.'
                : 'No invoices match your search.';
            countLabel.textContent = `Showing ${visibleCount} of ${total} invoices`;
        };

        filterOptions.forEach((option) => {
            option.addEventListener('click', function (event) {
                event.preventDefault();
                currentFilter = this.dataset.filter || 'all';
                filterLabel.textContent = filterLabels[currentFilter] || 'All';
                applyFilters();
            });
        });

        sortOptions.forEach((option) => {
            option.addEventListener('click', function (event) {
                event.preventDefault();
                currentSort = this.dataset.sort || 'newest';
                sortLabel.textContent = sortLabels[currentSort] || 'Newest';
                applyFilters();
            });
        });

        searchInput.addEventListener('input', function () {
            window.clearTimeout(debounceTimer);
            debounceTimer = window.setTimeout(applyFilters, 180);
        });

        previewButtons.forEach((button) => {
            button.addEventListener('click', function (event) {
                const previewId = this.dataset.invoicePreviewId;
                const template = document.querySelector(`[data-invoice-preview-template="${previewId}"]`);

                if (!template || !previewBody || !previewModal) {
                    return;
                }

                event.preventDefault();

                previewBody.innerHTML = template.innerHTML;

                if (previewTitle) {
                    previewTitle.textContent = `Invoice Preview - ${this.dataset.invoicePreviewNumber || ''}`.trim();
                }

                if (previewFullPageLink) {
                    previewFullPageLink.href = this.dataset.invoicePreviewUrl || '#';
                }

                previewModal.show();
            });
        });

        applyFilters();
    });
</script>
@endsection
