@extends('layouts.vertical', ['title' => 'Customers'])

@section('css')
<style>
     .customer-card-title {
          display: inline-flex;
          align-items: center;
          gap: .65rem;
     }

     .customer-card-icon {
          width: 38px;
          height: 38px;
          border-radius: 10px;
          display: inline-flex;
          align-items: center;
          justify-content: center;
          flex: 0 0 38px;
     }

     .customer-table-wrap {
          max-height: 620px;
          overflow: auto;
     }

     .customer-table-wrap .table {
          min-width: 840px;
     }

     .customer-table-wrap thead th {
          position: sticky;
          top: 0;
          z-index: 2;
          background: var(--bs-light-bg-subtle);
          box-shadow: inset 0 -1px 0 var(--bs-border-color);
     }
</style>
@endsection

@section('content')
@php
     $errors = $errors ?? new \Illuminate\Support\ViewErrorBag();
@endphp

<div class="row">
     <div class="col-md-6 col-xl">
          <div class="card">
               <div class="card-body">
                    <div class="d-flex align-items-center gap-3">
                         <div>
                              <p class="text-dark fw-semibold fs-26 mb-1">{{ number_format($customerSummary['total'] ?? 0) }}</p>
                              <p class="card-title mb-0">Total Customers</p>
                         </div>
                         <div class="ms-auto">
                              <span class="btn btn-primary avatar-md rounded-circle d-flex align-items-center justify-content-center">
                                   <iconify-icon class="fs-2 text-white"
                                        icon="solar:users-group-two-rounded-bold-duotone"></iconify-icon>
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
                              <p class="text-dark fw-semibold fs-26 mb-1">{{ number_format($customerSummary['lead'] ?? 0) }}</p>
                              <p class="card-title mb-0">Leads</p>
                         </div>
                         <div class="ms-auto">
                              <span class="btn btn-warning avatar-md rounded-circle d-flex align-items-center justify-content-center">
                                   <iconify-icon class="fs-2 text-white" icon="solar:user-plus-rounded-bold-duotone">
                                   </iconify-icon>
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
                              <p class="text-dark fw-semibold fs-26 mb-1">{{ number_format($customerSummary['active_client'] ?? 0) }}</p>
                              <p class="card-title mb-0">Active Clients</p>
                         </div>
                         <div class="ms-auto">
                              <span class="btn btn-info avatar-md rounded-circle d-flex align-items-center justify-content-center">
                                   <iconify-icon class="fs-2 text-white" icon="solar:user-check-rounded-bold-duotone">
                                   </iconify-icon>
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
                              <p class="text-dark fw-semibold fs-26 mb-1">{{ number_format($customerSummary['completed'] ?? 0) }}</p>
                              <p class="card-title mb-0">Completed</p>
                         </div>
                         <div class="ms-auto">
                              <span class="btn btn-success avatar-md rounded-circle d-flex align-items-center justify-content-center">
                                   <iconify-icon class="fs-2 text-white" icon="solar:shield-check-bold-duotone">
                                   </iconify-icon>
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
                              <p class="text-dark fw-semibold fs-26 mb-1">{{ number_format($customerSummary['inactive'] ?? 0) }}</p>
                              <p class="card-title mb-0">Inactive</p>
                         </div>
                         <div class="ms-auto">
                              <span class="btn btn-secondary avatar-md rounded-circle d-flex align-items-center justify-content-center">
                                   <iconify-icon class="fs-2 text-white" icon="solar:user-block-rounded-bold-duotone">
                                   </iconify-icon>
                              </span>
                         </div>
                    </div>
               </div>
          </div>
     </div>
</div>

<div class="row">
     <div class="col-xl-12">
          <div class="card">
               <div class="card-body">
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                         <div class="customer-card-title">
                              <span class="customer-card-icon bg-primary-subtle text-primary">
                                   <i data-lucide="users" class="icon-sm"></i>
                              </span>
                              <div>
                                   <h5 class="card-title mb-0">Customers</h5>
                              </div>
                         </div>
                         <div class="dropdown">
                              <a aria-expanded="false" class="dropdown-toggle btn btn-sm btn-outline-light rounded"
                                   data-bs-toggle="dropdown" href="#">
                                   Filter: <span id="customer-filter-label">All</span>
                              </a>
                              <div class="dropdown-menu">
                                   <a class="dropdown-item customer-filter-option" href="#!" data-filter="all">All</a>
                                   <a class="dropdown-item customer-filter-option" href="#!" data-filter="lead">Lead</a>
                                   <a class="dropdown-item customer-filter-option" href="#!" data-filter="active_client">Active Client</a>
                                   <a class="dropdown-item customer-filter-option" href="#!" data-filter="completed">Completed</a>
                                   <a class="dropdown-item customer-filter-option" href="#!" data-filter="inactive">Inactive</a>
                              </div>
                         </div>
                         <div class="dropdown">
                              <a aria-expanded="false" class="dropdown-toggle btn btn-sm btn-outline-light rounded"
                                   data-bs-toggle="dropdown" href="#">
                                   Service: <span id="customer-service-filter-label">All</span>
                              </a>
                              <div class="dropdown-menu">
                                   <a class="dropdown-item customer-service-filter-option" href="#!" data-service="all">All services</a>
                                   @foreach($customers->map(fn ($customer) => $customer->latestServiceLabel())->unique()->sort()->values() as $serviceLabel)
                                   <a class="dropdown-item customer-service-filter-option" href="#!" data-service="{{ Str::slug($serviceLabel) }}">{{ $serviceLabel }}</a>
                                   @endforeach
                              </div>
                         </div>
                         <div class="search-bar ms-auto">
                              <span style="top: 2px;"><i data-lucide="search"></i></span>
                              <input class="form-control form-control-sm" id="customer-search" placeholder="Search customers..."
                                   type="search" />
                         </div>
                         <div class="dropdown">
                              <a aria-expanded="false" class="dropdown-toggle btn btn-sm btn-outline-light rounded"
                                   data-bs-toggle="dropdown" href="#">
                                   Sort: <span id="customer-sort-label">Newest</span>
                              </a>
                              <div class="dropdown-menu dropdown-menu-end">
                                   <a class="dropdown-item customer-sort-option" href="#!" data-sort="newest">Newest First</a>
                                   <a class="dropdown-item customer-sort-option" href="#!" data-sort="oldest">Oldest First</a>
                                   <a class="dropdown-item customer-sort-option" href="#!" data-sort="customer">Customer Name</a>
                              </div>
                         </div>
                         <div class="d-flex flex-wrap gap-2">
                              <a class="btn btn-sm btn-success" href="{{ route('customers.create') }}">
                                   <i class="icon-sm me-1" data-lucide="user-plus"></i>Add User
                              </a>
                              <button class="btn btn-sm btn-outline-primary" id="customer-import-trigger" type="button">
                                   <i class="icon-sm me-1" data-lucide="upload"></i>Import CSV
                              </button>
                              <a class="btn btn-sm btn-primary" href="{{ route('customers.export') }}" id="customer-export-trigger">
                                   <i class="icon-sm me-1" data-lucide="download"></i>Export CSV
                              </a>
                         </div>
                    </div>
               </div>
               <div class="card-body">
                    @if($errors->any())
                    <div class="alert alert-danger" role="alert">
                         <div class="fw-semibold mb-2">Import failed.</div>
                         <ul class="mb-0 ps-3">
                              @foreach($errors->all() as $error)
                              <li>{{ $error }}</li>
                              @endforeach
                         </ul>
                    </div>
                    @endif

                    @if(session('customer-import-error'))
                    <div class="alert alert-danger" role="alert">
                         {{ session('customer-import-error') }}
                    </div>
                    @endif

                    <div class="table-responsive customer-table-wrap">
                         <table class="table align-middle mb-0 table-hover table-centered">
                              <thead class="bg-light-subtle">
                                   <tr>
                                        <th>Customer Name</th>
                                        <th>Customer Email</th>
                                        <th>Latest Service</th>
                                        <th>Phone Number</th>
                                        <th>Date</th>
                                        <th class="text-end">Actions</th>
                                   </tr>
                              </thead>
                              <tbody id="customer-table-body">
                                   @foreach($customers as $customer)
                                   @php
                                        $customerDate = $customer->first_seen_at ?? $customer->last_quote_at ?? $customer->created_at;
                                   @endphp
                                   <tr data-customer-row
                                        data-status="{{ $customer->status }}"
                                        data-service="{{ Str::slug($customer->latestServiceLabel()) }}"
                                        data-date="{{ $customerDate?->format('c') ?? '' }}"
                                        data-name="{{ $customer->full_name }}"
                                        data-search="{{ strtolower(implode(' ', [
                                             $customer->full_name,
                                             $customer->email,
                                             $customer->phone,
                                             $customer->latestServiceLabel(),
                                             $customer->statusLabel(),
                                             $customerDate?->format('d M Y') ?? '',
                                        ])) }}">
                                        <td>
                                             <div class="d-flex align-items-center gap-2">
                                                  <div class="avatar-sm rounded-circle bg-primary-subtle text-primary d-flex align-items-center justify-content-center fw-semibold">
                                                       {{ $customer->initials() }}
                                                  </div>
                                                  <div>
                                                       <div class="fw-semibold text-dark">{{ $customer->full_name }}</div>
                                                  </div>
                                             </div>
                                        </td>
                                        <td>{{ $customer->email }}</td>
                                        <td>
                                             <span class="badge badge-soft-primary">{{ $customer->latestServiceLabel() }}</span>
                                        </td>
                                        <td>{{ $customer->phone }}</td>
                                        <td>{{ $customerDate?->format('d M Y') ?? 'Not available' }}</td>
                                        <td class="text-end">
                                             <div class="d-inline-flex flex-wrap gap-1 justify-content-end">
                                                  <a class="btn btn-icon btn-sm btn-soft-primary" href="{{ route('customers.show', $customer) }}" title="View">
                                                       <i class="align-middle" data-lucide="eye"></i>
                                                  </a>
                                                  <a class="btn btn-icon btn-sm btn-soft-secondary" href="{{ route('customers.edit', $customer) }}" title="Edit">
                                                       <i class="align-middle" data-lucide="square-pen"></i>
                                                  </a>
                                                  <form action="{{ route('customers.destroy', $customer) }}" class="d-inline-flex" data-delete-confirm data-delete-message="Do you want to delete this customer and all linked quote requests?" data-delete-title="Delete customer?" method="POST">
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
                                   <tr class="{{ $customers->isNotEmpty() ? 'd-none' : '' }}" id="customer-empty-state">
                                        <td class="text-center text-muted py-4" colspan="6" id="customer-empty-message">
                                             {{ $customers->isEmpty() ? 'No customers found yet. Import a CSV or wait for synced customer records to appear.' : 'No customers match your search.' }}
                                        </td>
                                   </tr>
                              </tbody>
                         </table>
                    </div>
                    <div class="pt-3 border-top mt-3">
                         <div class="d-flex justify-content-between align-items-center">
                              <small class="text-muted" id="customer-count">{{ $customers->total() }} customers</small>
                              <div>
                                   {{ $customers->links() }}
                              </div>
                         </div>
                    </div>
               </div>
          </div>
     </div>
</div>

<form action="{{ route('customers.import') }}" class="d-none" enctype="multipart/form-data" id="customer-import-form" method="POST">
     @csrf
     <input accept=".csv,text/csv" id="customer-import-file" name="customers_file" type="file">
</form>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const searchInput = document.getElementById('customer-search');
        const rows = Array.from(document.querySelectorAll('[data-customer-row]'));
        const emptyState = document.getElementById('customer-empty-state');
        const emptyMessage = document.getElementById('customer-empty-message');
        const countLabel = document.getElementById('customer-count');
        const filterOptions = document.querySelectorAll('.customer-filter-option');
        const serviceFilterOptions = document.querySelectorAll('.customer-service-filter-option');
        const sortOptions = document.querySelectorAll('.customer-sort-option');
        const filterLabel = document.getElementById('customer-filter-label');
        const serviceFilterLabel = document.getElementById('customer-service-filter-label');
        const sortLabel = document.getElementById('customer-sort-label');
        const tbody = document.getElementById('customer-table-body');
        const exportTrigger = document.getElementById('customer-export-trigger');
        const importTrigger = document.getElementById('customer-import-trigger');
        const importForm = document.getElementById('customer-import-form');
        const importFile = document.getElementById('customer-import-file');

        const total = rows.length;
        let debounceTimer = null;
        let currentFilter = 'all';
        let currentService = 'all';
        let currentSort = 'newest';

        const filterLabels = {
            'all': 'All',
            'lead': 'Lead',
            'active_client': 'Active Client',
            'completed': 'Completed',
            'inactive': 'Inactive'
        };

        const sortLabels = {
            'newest': 'Newest',
            'oldest': 'Oldest',
            'customer': 'Customer'
        };

        const serviceLabels = {
            'all': 'All',
        };

        serviceFilterOptions.forEach((option) => {
            serviceLabels[option.dataset.service] = option.textContent.trim();
        });

        const getStatusFromRow = (row) => row.dataset.status || 'lead';
        const getDateFromRow = (row) => new Date(row.dataset.date || 0);
        const getCustomerFromRow = (row) => (row.dataset.name || '').toLowerCase();

        const sortRows = (rowsToSort) => {
            const sorted = [...rowsToSort];

            if (currentSort === 'newest') {
                sorted.sort((a, b) => getDateFromRow(b) - getDateFromRow(a));
            } else if (currentSort === 'oldest') {
                sorted.sort((a, b) => getDateFromRow(a) - getDateFromRow(b));
            } else if (currentSort === 'customer') {
                sorted.sort((a, b) => getCustomerFromRow(a).localeCompare(getCustomerFromRow(b)));
            }

            return sorted;
        };

        const applyFilters = () => {
            if (!searchInput || !emptyState || !emptyMessage || !countLabel || !tbody) {
                return;
            }

            const query = searchInput.value.trim().toLowerCase();
            let visibleCount = 0;
            const matchingRows = [];

            rows.forEach((row) => {
                const haystack = (row.dataset.search || '').toLowerCase();
                const status = getStatusFromRow(row);
                const service = row.dataset.service || 'all';
                const matchesSearch = query === '' || haystack.includes(query);
                const matchesFilter = currentFilter === 'all' || status === currentFilter;
                const matchesService = currentService === 'all' || service === currentService;
                const matches = matchesSearch && matchesFilter && matchesService;

                row.classList.toggle('d-none', !matches);

                if (matches) {
                    visibleCount += 1;
                    matchingRows.push(row);
                }
            });

            sortRows(matchingRows).forEach((row) => {
                tbody.appendChild(row);
            });

            emptyState.classList.toggle('d-none', visibleCount > 0);
            emptyMessage.textContent = total === 0
                ? 'No customers found yet. Import a CSV or wait for synced customer records to appear.'
                : 'No customers match your search.';
            countLabel.textContent = total === 0
                ? '0 customers'
                : `${visibleCount} of ${total} customers`;
        };

        filterOptions.forEach((option) => {
            option.addEventListener('click', function (event) {
                event.preventDefault();
                currentFilter = this.dataset.filter;

                if (filterLabel) {
                    filterLabel.textContent = filterLabels[currentFilter];
                }

                applyFilters();
            });
        });

        serviceFilterOptions.forEach((option) => {
            option.addEventListener('click', function (event) {
                event.preventDefault();
                currentService = this.dataset.service;

                if (serviceFilterLabel) {
                    serviceFilterLabel.textContent = serviceLabels[currentService] || 'All';
                }

                applyFilters();
            });
        });

        sortOptions.forEach((option) => {
            option.addEventListener('click', function (event) {
                event.preventDefault();
                currentSort = this.dataset.sort;

                if (sortLabel) {
                    sortLabel.textContent = sortLabels[currentSort];
                }

                applyFilters();
            });
        });

        if (searchInput) {
            searchInput.addEventListener('input', function () {
                window.clearTimeout(debounceTimer);
                debounceTimer = window.setTimeout(applyFilters, 180);
            });
        }

        if (exportTrigger) {
            exportTrigger.addEventListener('click', function (event) {
                event.preventDefault();

                const url = new URL(exportTrigger.href, window.location.origin);
                const query = searchInput ? searchInput.value.trim() : '';

                if (query !== '') {
                    url.searchParams.set('search', query);
                }

                url.searchParams.set('status', currentFilter);
                url.searchParams.set('service', currentService);
                url.searchParams.set('sort', currentSort);
                window.location.href = url.toString();
            });
        }

        if (importTrigger && importFile) {
            importTrigger.addEventListener('click', function (event) {
                event.preventDefault();
                importFile.click();
            });
        }

        if (importFile && importForm) {
            importFile.addEventListener('change', function () {
                if (this.files && this.files.length > 0) {
                    importForm.submit();
                }
            });
        }

        applyFilters();
    });
</script>
@endsection
