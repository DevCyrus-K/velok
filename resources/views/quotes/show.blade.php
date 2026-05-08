@extends('layouts.vertical', ['title' => 'Quote Details'])

@section('css')
<style>
    @media (max-width: 639.98px) {
        .quote-request-actions .btn,
        .quote-request-actions form {
            width: 100%;
        }
    }
</style>
@endsection

@section('content')
@php
    $requestGroup = $quote->statusGroup();
    $quotation = $quotation ?? $quote->quote;
    $invoice = $invoice ?? $quote->invoice;
@endphp
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
                    <div>
                        <p class="text-muted mb-1">Quote</p>
                        <h3 class="mb-2">{{ $quote->reference() }}</h3>
                        <div class="d-flex flex-wrap align-items-center gap-2">
                            <span class="badge badge-soft-{{ $quote->statusBadgeClass() }}" id="quote-status-badge">{{ $quote->statusLabel() }}</span>
                            <span class="text-muted small">Submitted {{ $quote->created_at?->format('d M Y, h:i A') ?? 'N/A' }}</span>
                            <span class="text-muted small" id="quote-approval-date-label">
                                @if($quote->approval_date)
                                    Approved {{ $quote->approval_date->format('d M Y') }}
                                @endif
                            </span>
                        </div>
                    </div>
                    <div class="d-flex flex-wrap gap-2">
                        <a class="btn btn-outline-secondary" href="{{ route('quotes.index') }}">Back to Quotes</a>
                    </div>
                </div>

                <div class="row g-3 mt-4">
                    <div class="col-md-6 col-xl-3">
                        <div class="border rounded p-3 h-100">
                            <p class="text-muted mb-1">Customer</p>
                            <div class="fw-semibold text-dark">{{ $quote->full_name }}</div>
                            <small class="text-muted d-block mt-2">{{ $quote->email }}</small>
                            <small class="text-muted d-block">{{ $quote->phone }}</small>
                        </div>
                    </div>
                    <div class="col-md-6 col-xl-3">
                        <div class="border rounded p-3 h-100">
                            <p class="text-muted mb-1">Service Type</p>
                            <div class="fw-semibold text-dark">{{ $quote->serviceTypeLabel() }}</div>
                            <small class="text-muted d-block mt-2">Item details</small>
                            <small class="text-muted d-block">{{ $quote->move_size ?: 'Not specified' }}</small>
                        </div>
                    </div>
                    <div class="col-md-6 col-xl-3">
                        <div class="border rounded p-3 h-100">
                            <p class="text-muted mb-1">Pickup Location</p>
                            <div class="fw-semibold text-dark">{{ $quote->moving_from ?: 'Not specified' }}</div>
                            <small class="text-muted d-block mt-2">Drop-off</small>
                            <small class="text-muted d-block">{{ $quote->moving_to ?: 'Not specified' }}</small>
                        </div>
                    </div>
                    <div class="col-md-6 col-xl-3">
                        <div class="border rounded p-3 h-100">
                            <p class="text-muted mb-1">Preferred Date</p>
                            <div class="fw-semibold text-dark">{{ $quote->move_date?->format('d M Y') ?? 'Not specified' }}</div>
                            <small class="text-muted d-block mt-2">Source</small>
                            <small class="text-muted d-block">{{ $quote->source_page ?: 'Not captured' }}</small>
                        </div>
                    </div>
                </div>

                <div class="row g-4 mt-1">
                    <div class="col-xl-8">
                        <div class="border rounded p-4 h-100">
                            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
                                <div>
                                    <h5 class="mb-1">Submitted Quote Details</h5>
                                    <p class="text-muted mb-0">Everything below is pulled directly from the stored quote record.</p>
                                </div>
                                <span class="badge badge-soft-primary">{{ $quote->serviceTypeLabel() }}</span>
                            </div>

                            <div class="table-responsive">
                                <table class="table align-middle mb-0">
                                    <tbody>
                                        <tr>
                                            <th class="ps-0 text-muted fw-medium" style="width: 220px;">Customer Name</th>
                                            <td>{{ $quote->full_name }}</td>
                                        </tr>
                                        <tr>
                                            <th class="ps-0 text-muted fw-medium">Contact Info</th>
                                            <td>{{ $quote->email }} • {{ $quote->phone }}</td>
                                        </tr>
                                        <tr>
                                            <th class="ps-0 text-muted fw-medium">Service Type</th>
                                            <td>{{ $quote->serviceTypeLabel() }}</td>
                                        </tr>
                                        <tr>
                                            <th class="ps-0 text-muted fw-medium">Pickup Location</th>
                                            <td>{{ $quote->moving_from ?: 'Not specified' }}</td>
                                        </tr>
                                        <tr>
                                            <th class="ps-0 text-muted fw-medium">Drop-off Location</th>
                                            <td>{{ $quote->moving_to ?: 'Not specified' }}</td>
                                        </tr>
                                        <tr>
                                            <th class="ps-0 text-muted fw-medium">Preferred Move Date</th>
                                            <td>{{ $quote->move_date?->format('d M Y') ?? 'Not specified' }}</td>
                                        </tr>
                                        <tr>
                                            <th class="ps-0 text-muted fw-medium">Item Details</th>
                                            <td>{{ $quote->move_size ?: 'Not specified' }}</td>
                                        </tr>
                                        <tr>
                                            <th class="ps-0 text-muted fw-medium">Special Notes</th>
                                            <td>{{ $quote->additional_notes ?: 'No special notes were submitted.' }}</td>
                                        </tr>
                                        <tr>
                                            <th class="ps-0 text-muted fw-medium">Approval Date</th>
                                            <td id="quote-approval-date-value">{{ $quote->approval_date?->format('d M Y') ?? 'Pending approval' }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-4">
                        <div class="border rounded p-4 h-100">
                            <h5 class="mb-3">Action Center</h5>
                            <div class="d-grid gap-2 quote-request-actions">
                                @if($requestGroup === 'pending')
                                    <form action="{{ route('quotes.approve', $quote) }}" id="approveQuoteRequestForm" method="POST">
                                        @csrf
                                        @method('PATCH')
                                        <button class="btn btn-success w-100" type="submit">
                                            <i data-lucide="check" class="align-middle me-1"></i>Approve
                                        </button>
                                    </form>
                                    <form action="{{ route('quotes.decline', $quote) }}" data-confirm-button-class="btn-warning" data-confirm-cancel-text="No, Keep" data-confirm-confirm-text="Yes, Reject" data-confirm-message="Do you want to reject this quote request?" data-confirm-modal data-confirm-title="Reject quote request?" id="declineQuoteRequestForm" method="POST">
                                        @csrf
                                        @method('PATCH')
                                        <button class="btn btn-outline-warning w-100" type="submit">
                                            <i data-lucide="x" class="align-middle me-1"></i>Reject
                                        </button>
                                    </form>
                                    <form action="{{ route('quotes.destroy', $quote) }}" data-delete-confirm data-delete-message="Do you want to delete this quote request?" data-delete-title="Delete quote request?" id="deleteQuoteRequestForm" method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-outline-danger w-100" type="submit">
                                            <i data-lucide="trash-2" class="align-middle me-1"></i>Delete
                                        </button>
                                    </form>
                                    <a class="btn btn-primary d-none" id="createQuotationButton" href="{{ route('quotations.create', $quote) }}">
                                        <i data-lucide="plus" class="align-middle me-1"></i>Create Quote
                                    </a>
                                @elseif($requestGroup === 'approved')
                                    @if($quotation)
                                        <a class="btn btn-info" href="{{ route('quotations.show', $quotation) }}">
                                            <i data-lucide="file-text" class="align-middle me-1"></i>View Quote
                                        </a>
                                    @else
                                        <a class="btn btn-primary" id="createQuotationButton" href="{{ route('quotations.create', $quote) }}">
                                            <i data-lucide="plus" class="align-middle me-1"></i>Create Quote
                                        </a>
                                    @endif
                                @else
                                    <form action="{{ route('quotes.destroy', $quote) }}" data-delete-confirm data-delete-message="Do you want to delete this rejected quote request?" data-delete-title="Delete quote request?" method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-outline-danger w-100" type="submit">
                                            <i data-lucide="trash-2" class="align-middle me-1"></i>Delete
                                        </button>
                                    </form>
                                @endif
                            </div>
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
        const form = document.getElementById('approveQuoteRequestForm');

        if (!form) {
            return;
        }

        const modalElement = document.getElementById('deleteConfirmModal');
        const titleElement = document.getElementById('deleteConfirmModalTitle');
        const messageElement = document.getElementById('deleteConfirmModalMessage');
        const confirmButton = document.getElementById('deleteConfirmButton');
        const cancelButton = document.getElementById('deleteConfirmCancelButton');
        const modal = modalElement && window.bootstrap ? new bootstrap.Modal(modalElement) : null;
        const statusBadge = document.getElementById('quote-status-badge');
        const approvalDateLabel = document.getElementById('quote-approval-date-label');
        const approvalDateValue = document.getElementById('quote-approval-date-value');
        const createButton = document.getElementById('createQuotationButton');
        const declineForm = document.getElementById('declineQuoteRequestForm');
        const deleteForm = document.getElementById('deleteQuoteRequestForm');

        const showToast = (message, className = 'bg-success') => {
            if (!window.Toastify || !message) {
                return;
            }

            Toastify({
                text: message,
                duration: 3000,
                close: true,
                gravity: 'top',
                position: 'right',
                className,
            }).showToast();
        };

        const approve = async () => {
            const submitButton = form.querySelector('[type="submit"]');
            const formData = new FormData(form);

            submitButton.disabled = true;

            try {
                const response = await fetch(form.action, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: formData,
                });

                if (!response.ok) {
                    throw new Error('Approval failed.');
                }

                const data = await response.json();

                if (statusBadge) {
                    statusBadge.className = `badge badge-soft-${data.status_badge_class || 'success'}`;
                    statusBadge.textContent = data.status_label || 'Approved';
                }

                if (approvalDateLabel) {
                    approvalDateLabel.textContent = data.approval_date_formatted ? `Approved ${data.approval_date_formatted}` : '';
                }

                if (approvalDateValue) {
                    approvalDateValue.textContent = data.approval_date_formatted || 'Pending approval';
                }

                if (createButton && data.create_url) {
                    createButton.href = data.create_url;
                    createButton.classList.remove('d-none');
                }

                form.classList.add('d-none');
                declineForm?.classList.add('d-none');
                deleteForm?.classList.add('d-none');
                showToast(data.message || 'Quote request approved.');
            } catch (error) {
                submitButton.disabled = false;
                showToast('Quote request approval failed. Please try again.', 'bg-danger');
            }
        };

        form.addEventListener('submit', function (event) {
            event.preventDefault();

            if (!modal || !titleElement || !messageElement || !confirmButton || !cancelButton) {
                approve();
                return;
            }

            titleElement.textContent = 'Approve quote?';
            messageElement.textContent = 'Do you want to approve this quote request?';
            confirmButton.textContent = 'Yes, Approve';
            cancelButton.textContent = 'No, Keep';
            confirmButton.className = 'btn btn-success';

            const onConfirm = () => {
                confirmButton.removeEventListener('click', onConfirm);
                modal.hide();
                approve();
            };

            confirmButton.addEventListener('click', onConfirm);
            modalElement.addEventListener('hidden.bs.modal', function cleanup() {
                confirmButton.removeEventListener('click', onConfirm);
                modalElement.removeEventListener('hidden.bs.modal', cleanup);
            });
            modal.show();
        });
    });
</script>
@endsection
