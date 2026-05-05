@extends('layouts.vertical', ['title' => 'Invoices'])

@section('content')
@php
    $invoiceDetailsRoute = route('second', ['invoice', 'invoice-details']);

    $invoices = [
        [
            'id' => '#IN9023',
            'customer' => 'Ethan Walker',
            'avatar' => '/images/users/avatar-8.jpg',
            'created_date' => '15 Mar, 2025',
            'created_time' => '10:30 AM',
            'created_sort' => '2025-03-15T10:30:00',
            'due_date' => '22 Mar, 2025',
            'due_sort' => '2025-03-22',
            'amount' => 1250.75,
            'status' => 'Unpaid',
            'status_key' => 'unpaid',
            'status_class' => 'warning',
            'via' => 'Credit Card',
        ],
        [
            'id' => '#IN3147',
            'customer' => 'Sophia Adams',
            'avatar' => '/images/users/avatar-9.jpg',
            'created_date' => '07 Feb, 2025',
            'created_time' => '02:45 PM',
            'created_sort' => '2025-02-07T14:45:00',
            'due_date' => '15 Feb, 2025',
            'due_sort' => '2025-02-15',
            'amount' => 980.00,
            'status' => 'Overdue',
            'status_key' => 'overdue',
            'status_class' => 'danger',
            'via' => 'PayPal',
        ],
        [
            'id' => '#IN7654',
            'customer' => 'Daniel Carter',
            'avatar' => '/images/users/avatar-10.jpg',
            'created_date' => '28 Jan, 2025',
            'created_time' => '11:10 AM',
            'created_sort' => '2025-01-28T11:10:00',
            'due_date' => '05 Feb, 2025',
            'due_sort' => '2025-02-05',
            'amount' => 715.25,
            'status' => 'Paid',
            'status_key' => 'paid',
            'status_class' => 'success',
            'via' => 'Wire Transfer',
        ],
        [
            'id' => '#IN5532',
            'customer' => 'Mia Johnson',
            'avatar' => '/images/users/avatar-1.jpg',
            'created_date' => '10 Apr, 2025',
            'created_time' => '09:50 AM',
            'created_sort' => '2025-04-10T09:50:00',
            'due_date' => '18 Apr, 2025',
            'due_sort' => '2025-04-18',
            'amount' => 560.90,
            'status' => 'Unpaid',
            'status_key' => 'unpaid',
            'status_class' => 'warning',
            'via' => 'Bank Transfer',
        ],
        [
            'id' => '#IN7823',
            'customer' => 'James Anderson',
            'avatar' => '/images/users/avatar-2.jpg',
            'created_date' => '20 Feb, 2025',
            'created_time' => '02:15 PM',
            'created_sort' => '2025-02-20T14:15:00',
            'due_date' => '28 Feb, 2025',
            'due_sort' => '2025-02-28',
            'amount' => 1230.50,
            'status' => 'Unpaid',
            'status_key' => 'unpaid',
            'status_class' => 'warning',
            'via' => 'Stripe',
        ],
        [
            'id' => '#IN9124',
            'customer' => 'Charlotte Brown',
            'avatar' => '/images/users/avatar-3.jpg',
            'created_date' => '18 Feb, 2025',
            'created_time' => '11:45 AM',
            'created_sort' => '2025-02-18T11:45:00',
            'due_date' => '28 Mar, 2025',
            'due_sort' => '2025-03-28',
            'amount' => 875.00,
            'status' => 'Paid',
            'status_key' => 'paid',
            'status_class' => 'success',
            'via' => 'Payoneer',
        ],
        [
            'id' => '#IN2345',
            'customer' => 'Benjamin Wilson',
            'avatar' => '/images/users/avatar-4.jpg',
            'created_date' => '15 Feb, 2025',
            'created_time' => '03:30 PM',
            'created_sort' => '2025-02-15T15:30:00',
            'due_date' => '25 Feb, 2025',
            'due_sort' => '2025-02-25',
            'amount' => 650.75,
            'status' => 'Overdue',
            'status_key' => 'overdue',
            'status_class' => 'danger',
            'via' => 'Bank Transfer',
        ],
        [
            'id' => '#IN5689',
            'customer' => 'Amelia Clark',
            'avatar' => '/images/users/avatar-5.jpg',
            'created_date' => '10 Feb, 2025',
            'created_time' => '01:10 PM',
            'created_sort' => '2025-02-10T13:10:00',
            'due_date' => '20 Feb, 2025',
            'due_sort' => '2025-02-20',
            'amount' => 350.00,
            'status' => 'Unpaid',
            'status_key' => 'unpaid',
            'status_class' => 'warning',
            'via' => 'Wise',
        ],
        [
            'id' => '#IN7482',
            'customer' => 'Lucas Harris',
            'avatar' => '/images/users/avatar-6.jpg',
            'created_date' => '08 Feb, 2025',
            'created_time' => '09:20 AM',
            'created_sort' => '2025-02-08T09:20:00',
            'due_date' => '18 Feb, 2025',
            'due_sort' => '2025-02-18',
            'amount' => 780.99,
            'status' => 'Paid',
            'status_key' => 'paid',
            'status_class' => 'success',
            'via' => 'Stripe',
        ],
        [
            'id' => '#IN9823',
            'customer' => 'Mia Robinson',
            'avatar' => '/images/users/avatar-7.jpg',
            'created_date' => '05 Feb, 2025',
            'created_time' => '05:45 PM',
            'created_sort' => '2025-02-05T17:45:00',
            'due_date' => '15 Feb, 2025',
            'due_sort' => '2025-02-15',
            'amount' => 920.00,
            'status' => 'Overdue',
            'status_key' => 'overdue',
            'status_class' => 'danger',
            'via' => 'PayPal',
        ],
    ];

    $invoiceCount = count($invoices);
@endphp

<div class="row">
    <div class="col">
        <div class="card">
            <div class="card-body">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                    <div>
                        <h5 class="card-title mb-1">My Invoices</h5>
                    </div>

                    <div class="dropdown">
                        <a aria-expanded="false" class="dropdown-toggle btn btn-sm btn-outline-light rounded"
                            data-bs-toggle="dropdown" href="#">
                            Filter: <span id="invoice-filter-label">All</span>
                        </a>
                        <div class="dropdown-menu">
                            <a class="dropdown-item invoice-filter-option" data-filter="all" href="#!">All</a>
                            <a class="dropdown-item invoice-filter-option" data-filter="paid" href="#!">Paid</a>
                            <a class="dropdown-item invoice-filter-option" data-filter="unpaid" href="#!">Unpaid</a>
                            <a class="dropdown-item invoice-filter-option" data-filter="overdue" href="#!">Overdue</a>
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

                    <div class="dropdown">
                        <a aria-expanded="false" class="dropdown-toggle btn btn-sm btn-outline-light rounded"
                            data-bs-toggle="dropdown" href="#">
                            Reports
                        </a>
                        <div class="dropdown-menu dropdown-menu-end">
                            <a class="dropdown-item" href="#!">Export CSV</a>
                            <a class="dropdown-item" href="#!">Download PDF</a>
                            <a class="dropdown-item" href="#!">Send Reminders</a>
                        </div>
                    </div>

                    <div>
                        <a class="btn btn-sm btn-success" href="#!">
                            New Invoice
                        </a>
                    </div>
                </div>
            </div>

            <div>
                <div class="table-responsive table-centered">
                    <table class="table table-striped text-nowrap mb-0">
                        <thead class="text-uppercase fs-12">
                            <tr>
                                <th class="border-0 py-2 text-dark">Invoice ID</th>
                                <th class="border-0 py-2 text-dark">Customer</th>
                                <th class="border-0 py-2 text-dark">Created Date</th>
                                <th class="border-0 py-2 text-dark">Due Date</th>
                                <th class="border-0 py-2 text-dark">Amount</th>
                                <th class="border-0 py-2 text-dark">Payment Status</th>
                                <th class="border-0 py-2 text-dark">Via</th>
                                <th class="border-0 py-2 text-dark">Action</th>
                            </tr>
                        </thead>
                        <tbody id="invoice-table-body">
                            @foreach ($invoices as $invoice)
                                <tr data-amount="{{ number_format($invoice['amount'], 2, '.', '') }}"
                                    data-created="{{ $invoice['created_sort'] }}"
                                    data-customer="{{ strtolower($invoice['customer']) }}"
                                    data-due="{{ $invoice['due_sort'] }}"
                                    data-invoice-row
                                    data-search="{{ strtolower(implode(' ', [$invoice['id'], $invoice['customer'], $invoice['created_date'], $invoice['due_date'], $invoice['status'], $invoice['via'], number_format($invoice['amount'], 2)])) }}"
                                    data-status="{{ $invoice['status_key'] }}">
                                    <td>
                                        <a class="fw-medium" href="{{ $invoiceDetailsRoute }}">{{ $invoice['id'] }}</a>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <img alt="{{ $invoice['customer'] }}" class="avatar-xs rounded-circle me-2"
                                                src="{{ $invoice['avatar'] }}" />
                                            <div>
                                                <h5 class="fs-14 m-0 fw-normal">{{ $invoice['customer'] }}</h5>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        {{ $invoice['created_date'] }}
                                        <small class="text-muted">{{ $invoice['created_time'] }}</small>
                                    </td>
                                    <td>{{ $invoice['due_date'] }}</td>
                                    <td>KES {{ number_format($invoice['amount'], 2) }}</td>
                                    <td>
                                        <span class="badge badge-soft-{{ $invoice['status_class'] }}">{{ $invoice['status'] }}</span>
                                    </td>
                                    <td>{{ $invoice['via'] }}</td>
                                    <td>
                                        <div class="d-flex flex-wrap gap-1">
                                            <a class="btn btn-icon btn-sm btn-soft-primary" href="{{ $invoiceDetailsRoute }}" title="View">
                                                <i class="align-middle" data-lucide="eye"></i>
                                            </a>
                                            <a class="btn btn-icon btn-sm btn-soft-secondary" href="#!" title="Edit">
                                                <i class="align-middle" data-lucide="square-pen"></i>
                                            </a>
                                            <button class="btn btn-icon btn-sm btn-soft-danger" data-delete-confirm
                                                data-delete-message="Do you want to delete this invoice?"
                                                data-delete-remove-closest="tr"
                                                data-delete-success-toast="Invoice deleted successfully."
                                                data-delete-title="Delete invoice?" title="Delete" type="button">
                                                <i class="align-middle" data-lucide="trash-2"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach

                            <tr class="d-none" id="invoice-empty-state">
                                <td class="text-center text-muted py-4" colspan="8">No invoices match your search.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="align-items-center justify-content-between row g-0 text-center text-sm-start p-3 border-top">
                    <div class="col-sm">
                        <div class="text-muted" id="invoice-count">Showing {{ $invoiceCount }} of {{ $invoiceCount }} invoices</div>
                    </div>
                    <div class="col-sm-auto mt-3 mt-sm-0">
                        <ul class="pagination justify-content-end mb-0">
                            <li class="page-item">
                                <a class="page-link" href="javascript:void(0);">
                                    <iconify-icon class="fs-18" icon="lucide:chevron-left"></iconify-icon>
                                </a>
                            </li>
                            <li class="page-item active"><a class="page-link" href="javascript:void(0);">1</a></li>
                            <li class="page-item"><a class="page-link" href="javascript:void(0);">2</a></li>
                            <li class="page-item"><a class="page-link" href="javascript:void(0);">3</a></li>
                            <li class="page-item">
                                <a class="page-link" href="javascript:void(0);">
                                    <iconify-icon class="fs-18" icon="lucide:chevron-right"></iconify-icon>
                                </a>
                            </li>
                        </ul>
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
            paid: 'Paid',
            unpaid: 'Unpaid',
            overdue: 'Overdue',
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

            emptyState.classList.toggle('d-none', visibleCount > 0);
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

        document.addEventListener('confirmation:local-success', function (event) {
            if (event.detail?.source?.closest('#invoice-table-body')) {
                applyFilters();
            }
        });

        previewButtons.forEach((button) => {
            button.addEventListener('click', function () {
                const previewId = this.dataset.invoicePreviewId;
                const template = document.querySelector(`[data-invoice-preview-template="${previewId}"]`);

                if (!template || !previewBody || !previewModal) {
                    return;
                }

                previewBody.innerHTML = template.innerHTML;

                if (previewTitle) {
                    previewTitle.textContent = `Invoice Preview • ${this.dataset.invoicePreviewNumber || ''}`.trim();
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
