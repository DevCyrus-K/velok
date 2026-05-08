@extends('layouts.vertical', ['title' => 'Messages'])

@section('content')
<div class="row">
    <div class="col">
        <div class="card">
            <div class="card-body">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                    <div>
                        <h5 class="card-title mb-1">Messages</h5>
                    </div>

                    <div class="dropdown">
                        <a aria-expanded="false" class="dropdown-toggle btn btn-sm btn-outline-light rounded"
                            data-bs-toggle="dropdown" href="#">
                            Filter: <span id="message-filter-label">All</span>
                        </a>
                        <div class="dropdown-menu">
                            <a class="dropdown-item message-filter-option" data-filter="all" href="#!">All</a>
                            <a class="dropdown-item message-filter-option" data-filter="unread" href="#!">Unread</a>
                            <a class="dropdown-item message-filter-option" data-filter="read" href="#!">Read</a>
                            <a class="dropdown-item message-filter-option" data-filter="responded" href="#!">Responded</a>
                            <a class="dropdown-item message-filter-option" data-filter="draft" href="#!">Draft</a>
                            <a class="dropdown-item message-filter-option" data-filter="sent" href="#!">Sent</a>
                        </div>
                    </div>

                    <div class="dropdown">
                        <a aria-expanded="false" class="dropdown-toggle btn btn-sm btn-outline-light rounded"
                            data-bs-toggle="dropdown" href="#">
                            Category: <span id="message-category-filter-label">All</span>
                        </a>
                        <div class="dropdown-menu">
                            <a class="dropdown-item message-category-filter-option" data-category="all" href="#!">All categories</a>
                            @foreach ($messages->getCollection()->map(fn ($message) => $message->categoryLabel())->unique()->sort()->values() as $messageCategory)
                                <a class="dropdown-item message-category-filter-option" data-category="{{ Str::slug($messageCategory) }}" href="#!">{{ $messageCategory }}</a>
                            @endforeach
                        </div>
                    </div>

                    <div class="search-bar ms-auto">
                        <span style="top: 2px;"><i data-lucide="search"></i></span>
                        <input class="form-control form-control-sm" id="message-search" placeholder="Search messages..."
                            type="search" />
                    </div>

                    <div class="dropdown">
                        <a aria-expanded="false" class="dropdown-toggle btn btn-sm btn-outline-light rounded"
                            data-bs-toggle="dropdown" href="#">
                            Sort: <span id="message-sort-label">Newest</span>
                        </a>
                        <div class="dropdown-menu dropdown-menu-end">
                            <a class="dropdown-item message-sort-option" data-sort="newest" href="#!">Newest First</a>
                            <a class="dropdown-item message-sort-option" data-sort="oldest" href="#!">Oldest First</a>
                            <a class="dropdown-item message-sort-option" data-sort="sender" href="#!">Sender Name</a>
                        </div>
                    </div>

                    <div>
                        <a class="btn btn-sm btn-primary" href="{{ route('messages.compose') }}">
                            <i data-lucide="edit" class="icon-sm me-1"></i>Compose
                        </a>
                    </div>
                </div>
            </div>

            <div>
                <div class="table-responsive table-centered">
                    <table class="table table-striped text-nowrap mb-0">
                        <thead class="text-uppercase fs-12">
                            <tr>
                                <th class="border-0 py-2 text-dark">From</th>
                                <th class="border-0 py-2 text-dark">Subject</th>
                                <th class="border-0 py-2 text-dark">Category</th>
                                <th class="border-0 py-2 text-dark">Message</th>
                                <th class="border-0 py-2 text-dark">Date</th>
                                <th class="border-0 py-2 text-dark">Status</th>
                                <th class="border-0 py-2 text-dark">Delivery</th>
                                <th class="border-0 py-2 text-dark">Action</th>
                            </tr>
                        </thead>
                        <tbody id="message-table-body">
                            @forelse($messages as $message)
                                @php($deliveryLog = $message->latestEmailLog)
                                <tr data-created="{{ $message->created_at->format('Y-m-d\TH:i:s') }}"
                                    data-category="{{ Str::slug($message->categoryLabel()) }}"
                                    data-sender="{{ strtolower($message->name) }}"
                                    data-message-row
                                    data-search="{{ strtolower(implode(' ', [$message->name, $message->email, $message->subject, $message->message, $message->status, $message->categoryLabel()])) }}"
                                    data-status="{{ $message->status }}">
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div>
                                                <h5 class="fs-14 m-0 fw-normal">{{ $message->name }}</h5>
                                                <small class="text-muted">{{ $message->email }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <a class="fw-medium" href="{{ route('messages.show', $message) }}">{{ $message->subject }}</a>
                                    </td>
                                    <td>
                                        <span class="badge badge-soft-{{ $message->categoryBadgeClass() }}">{{ $message->categoryLabel() }}</span>
                                    </td>
                                    <td>
                                        <small class="text-muted">{{ Str::limit($message->message, 60) }}</small>
                                    </td>
                                    <td>
                                        {{ $message->created_at->format('d M, Y') }}
                                        <small class="text-muted">{{ $message->created_at->format('h:i A') }}</small>
                                    </td>
                                    <td>
                                        @if($message->status === 'unread')
                                            <span class="badge badge-soft-danger">Unread</span>
                                        @elseif($message->status === 'responded')
                                            <span class="badge badge-soft-success">Responded</span>
                                        @elseif($message->status === 'draft')
                                            <span class="badge badge-soft-warning">Draft</span>
                                        @elseif($message->status === 'sent')
                                            <span class="badge badge-soft-info">Sent</span>
                                        @else
                                            <span class="badge badge-soft-secondary">Read</span>
                                        @endif
                                    </td>
                                    <td data-delivery-cell data-retry-url="{{ route('messages.retry', $message) }}">
                                        @if($deliveryLog?->status === \App\Models\EmailLog::STATUS_SENT)
                                            <span class="badge bg-success">✅ Sent</span>
                                        @elseif($deliveryLog?->status === \App\Models\EmailLog::STATUS_OPENED)
                                            <span class="badge bg-info">👁 Opened</span>
                                        @elseif($deliveryLog?->status === \App\Models\EmailLog::STATUS_FAILED)
                                            <div class="d-flex flex-wrap align-items-center gap-1">
                                                <span class="badge bg-danger">❌ Failed</span>
                                                <button class="btn btn-sm btn-outline-danger py-0" type="button" data-message-retry data-retry-url="{{ route('messages.retry', $message) }}">Retry</button>
                                            </div>
                                            @if($deliveryLog->failed_reason)
                                                <small class="text-muted d-block">{{ Str::limit($deliveryLog->failed_reason, 60) }}</small>
                                            @endif
                                        @elseif($deliveryLog)
                                            <span class="badge bg-warning">Sending</span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="d-flex flex-wrap gap-1">
                                            <a class="btn btn-icon btn-sm btn-soft-primary" href="{{ route('messages.show', $message) }}" title="View">
                                                <i class="align-middle" data-lucide="eye"></i>
                                            </a>
                                            @if($message->status !== 'responded')
                                                <a class="btn btn-icon btn-sm btn-soft-secondary" href="{{ route('messages.show', $message) }}" title="Reply">
                                                    <i class="align-middle" data-lucide="reply"></i>
                                                </a>
                                            @endif
                                            <form action="{{ route('messages.destroy', $message) }}" class="d-inline-flex" data-message-delete-form method="POST">
                                                @csrf
                                                @method('DELETE')
                                                <button class="btn btn-icon btn-sm btn-soft-danger" title="Delete" type="submit">
                                                    <i class="align-middle" data-lucide="trash-2"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr id="message-empty-state">
                                    <td class="text-center text-muted py-4" colspan="8">No messages yet.</td>
                                </tr>
                            @endforelse
                            @if($messages->count() > 0)
                                <tr id="message-empty-state" style="display: none;">
                                    <td class="text-center text-muted py-4" colspan="8">No messages match your filters.</td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>

                <div class="align-items-center justify-content-between row g-0 text-center text-sm-start p-3 border-top">
                    <div class="col-sm">
                        <div class="text-muted" id="message-count">Showing {{ $messages->count() }} of {{ $messages->total() }} messages</div>
                    </div>
                    <div class="col-sm-auto mt-3 mt-sm-0">
                        {{ $messages->links('pagination::bootstrap-5') }}
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
        const searchInput = document.getElementById('message-search');
        const tbody = document.getElementById('message-table-body');
        const emptyState = document.getElementById('message-empty-state');
        const countLabel = document.getElementById('message-count');
        const filterOptions = document.querySelectorAll('.message-filter-option');
        const categoryFilterOptions = document.querySelectorAll('.message-category-filter-option');
        const sortOptions = document.querySelectorAll('.message-sort-option');
        const filterLabel = document.getElementById('message-filter-label');
        const categoryFilterLabel = document.getElementById('message-category-filter-label');
        const sortLabel = document.getElementById('message-sort-label');
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';

        if (!searchInput || !tbody || !countLabel) {
            return;
        }

        let debounceTimer = null;
        let currentFilter = 'all';
        let currentCategory = 'all';
        let currentSort = 'newest';

        const filterLabels = {
            all: 'All',
            unread: 'Unread',
            read: 'Read',
            responded: 'Responded',
            draft: 'Draft',
            sent: 'Sent',
        };

        const sortLabels = {
            newest: 'Newest',
            oldest: 'Oldest',
            sender: 'Sender Name',
        };

        const categoryLabels = {
            all: 'All',
        };

        categoryFilterOptions.forEach(option => {
            categoryLabels[option.dataset.category] = option.textContent.trim();
        });

        // Search functionality
        searchInput.addEventListener('keyup', function () {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => {
                filterAndSort();
            }, 300);
        });

        // Filter functionality
        filterOptions.forEach(option => {
            option.addEventListener('click', function (e) {
                e.preventDefault();
                currentFilter = this.dataset.filter;
                filterLabel.textContent = filterLabels[currentFilter];
                filterAndSort();
            });
        });

        categoryFilterOptions.forEach(option => {
            option.addEventListener('click', function (e) {
                e.preventDefault();
                currentCategory = this.dataset.category;
                categoryFilterLabel.textContent = categoryLabels[currentCategory] || 'All';
                filterAndSort();
            });
        });

        // Sort functionality
        sortOptions.forEach(option => {
            option.addEventListener('click', function (e) {
                e.preventDefault();
                currentSort = this.dataset.sort;
                sortLabel.textContent = sortLabels[currentSort];
                filterAndSort();
            });
        });

        function filterAndSort() {
            const searchTerm = searchInput.value.toLowerCase();
            let visibleCount = 0;
            const rows = tbody.querySelectorAll('[data-message-row]');

            rows.forEach(row => {
                const searchText = row.dataset.search;
                const status = row.dataset.status;
                const category = row.dataset.category || 'all';

                let matchesSearch = searchTerm === '' || searchText.includes(searchTerm);
                let matchesFilter = currentFilter === 'all' || status === currentFilter;
                let matchesCategory = currentCategory === 'all' || category === currentCategory;

                if (matchesSearch && matchesFilter && matchesCategory) {
                    row.style.display = '';
                    visibleCount++;
                } else {
                    row.style.display = 'none';
                }
            });

            // Sort visible rows
            const visibleRows = Array.from(tbody.querySelectorAll('[data-message-row]')).filter(row => row.style.display !== 'none');
            
            visibleRows.sort((a, b) => {
                if (currentSort === 'newest') {
                    return new Date(b.dataset.created) - new Date(a.dataset.created);
                } else if (currentSort === 'oldest') {
                    return new Date(a.dataset.created) - new Date(b.dataset.created);
                } else if (currentSort === 'sender') {
                    return a.dataset.sender.localeCompare(b.dataset.sender);
                }
            });

            // Re-append sorted rows
            visibleRows.forEach(row => tbody.appendChild(row));

            // Show/hide empty state
            if (emptyState) {
                emptyState.style.display = visibleCount === 0 ? '' : 'none';
            }

            updateCount();
        }

        function updateCount() {
            const visibleRows = tbody.querySelectorAll('[data-message-row]:not([style*="display: none"])').length;
            countLabel.textContent = `Showing ${visibleRows} message${visibleRows !== 1 ? 's' : ''}`;
        }

        function escapeHtml(value) {
            return String(value || '')
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }

        function deliveryHtml(status, retryUrl, reason) {
            if (status === 'sent') {
                return '<span class="badge bg-success">✅ Sent</span>';
            }

            if (status === 'opened') {
                return '<span class="badge bg-info">👁 Opened</span>';
            }

            if (status === 'failed') {
                return `
                    <div class="d-flex flex-wrap align-items-center gap-1">
                        <span class="badge bg-danger">❌ Failed</span>
                        <button class="btn btn-sm btn-outline-danger py-0" type="button" data-message-retry data-retry-url="${escapeHtml(retryUrl)}">Retry</button>
                    </div>
                    ${reason ? `<small class="text-muted d-block">${escapeHtml(reason).slice(0, 80)}</small>` : ''}
                `;
            }

            if (status === 'sending') {
                return '<span class="badge bg-warning">Sending</span>';
            }

            return '<span class="text-muted">-</span>';
        }

        tbody.addEventListener('submit', async function (event) {
            const form = event.target.closest('[data-message-delete-form]');

            if (!form) {
                return;
            }

            event.preventDefault();

            try {
                const response = await fetch(form.action, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: new FormData(form),
                });
                const data = await response.json();

                if (!response.ok || !data.success) {
                    throw new Error(data.error || 'Delete failed');
                }

                form.closest('[data-message-row]')?.remove();
                filterAndSort();
                showToast('Message deleted', 'success');
            } catch (error) {
                showToast('Failed to delete message', 'error');
            }
        });

        tbody.addEventListener('click', async function (event) {
            const button = event.target.closest('[data-message-retry]');

            if (!button) {
                return;
            }

            event.preventDefault();
            const retryUrl = button.dataset.retryUrl;
            const cell = button.closest('[data-delivery-cell]');

            showToast('Retrying...', 'info');
            button.disabled = true;

            if (cell) {
                cell.innerHTML = deliveryHtml('sending', retryUrl);
            }

            try {
                const response = await fetch(retryUrl, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });
                const data = await response.json();

                if (!response.ok || !data.success) {
                    throw new Error(data.error || 'Retry failed');
                }

                if (cell) {
                    cell.innerHTML = deliveryHtml(data.delivery?.status || 'sent', retryUrl);
                }

                showToast('Email sent successfully', 'success');
            } catch (error) {
                if (cell) {
                    cell.innerHTML = deliveryHtml('failed', retryUrl, error.message);
                }

                showToast('Failed: ' + error.message, 'error');
            }
        });

        // Initial count
        updateCount();
    });
</script>
@endsection
