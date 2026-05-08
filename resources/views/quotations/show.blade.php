@extends('layouts.vertical', ['title' => 'Quote - ' . $quotation->quoteRequest->reference()])

@section('css')
<style>
    .document-action-bar .btn {
        align-items: center;
        display: inline-flex;
        gap: 0.35rem;
        justify-content: center;
    }

    .document-action-bar .btn-icon-only {
        height: 36px;
        width: 36px;
        padding: 0;
    }

    .document-action-bar .dropdown-menu form {
        margin: 0;
    }

    .document-action-bar .dropdown-item {
        align-items: center;
        display: flex;
        gap: 0.5rem;
    }

    .document-action-bar .dropdown-item i {
        flex: 0 0 auto;
    }

    .signature-clean {
        background: transparent !important;
        border: none !important;
        box-shadow: none !important;
        outline: none !important;
        padding: 0 !important;
    }

    .quotation-total-box {
        margin-left: auto;
        max-width: 360px;
        width: 100%;
    }

    @media (max-width: 575.98px) {
        .document-action-bar .btn,
        .document-action-bar form,
        .document-action-bar .dropdown,
        .document-action-bar .dropdown > .btn {
            width: 100%;
        }

        .send-document-modal {
            height: 100vh;
            margin: 0;
            max-width: 100%;
        }

        .send-document-modal .modal-content {
            border: 0;
            border-radius: 0;
            min-height: 100vh;
        }
    }

    @media (max-width: 639.98px) {
        .responsive-document-table {
            overflow: visible;
        }

        .responsive-document-table table,
        .responsive-document-table thead,
        .responsive-document-table tbody,
        .responsive-document-table tr,
        .responsive-document-table td {
            display: block;
            width: 100%;
        }

        .responsive-document-table thead {
            display: none;
        }

        .responsive-document-table tr {
            border: 1px solid var(--bs-border-color);
            border-radius: 8px;
            margin-bottom: 0.75rem;
            padding: 0.75rem;
        }

        .responsive-document-table td {
            border: 0;
            padding: 0.35rem 0;
            text-align: left !important;
            white-space: normal;
        }

        .responsive-document-table td::before {
            color: var(--bs-secondary-color);
            content: attr(data-label);
            display: block;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
        }

        .quotation-total-box,
        .quotation-auth-box {
            float: none !important;
            max-width: none;
            width: 100%;
        }
    }
</style>
@endsection

@section('content')
@php
    $authorization = $authorization ?? [
        'name' => $quotation->authorized_by ?: 'Pending',
        'job_title' => $quotation->authorized_role,
        'signature_url' => null,
        'is_complete' => false,
        'profile_url' => route('account.show'),
        'date_label' => $quotation->authorizationDate()?->format('d M Y') ?? now()->format('d M Y'),
        'prompt' => 'Please complete your profile to display authorization details',
    ];
    $quote = $quotation->quoteRequest;
    $company = app(\App\Support\CompanyProfile::class)->data();
    $quotationEmail = app(\App\Support\QuotationEmail::class);
    $sendQuotationSubject = $quotationEmail->defaultSubject($quotation);
    $sendQuotationMessage = $quotationEmail->defaultMessage($quotation, auth()->user());
    $sentAtLabel = $quotation->sent_at?->format('d M Y, h:i A');
    $quotationStatus = (string) $quotation->status;
    $isDraftQuotation = $quotationStatus === \App\Models\Quotation::STATUS_DRAFT;
    $isSentQuotation = $quotationStatus === \App\Models\Quotation::STATUS_SENT;
    $isApprovedQuotation = $quotationStatus === \App\Models\Quotation::STATUS_APPROVED;
    $isRejectedQuotation = in_array($quotationStatus, [\App\Models\Quotation::STATUS_DECLINED, \App\Models\Quotation::STATUS_REJECTED], true);
    $canSendQuotation = $isDraftQuotation || $isSentQuotation;
    $canDeleteQuotation = $isDraftQuotation || $isRejectedQuotation;
    $invoice = $quotation->invoice;
    $companyName = trim((string) ($quotation->company_name ?: ($company['name'] ?? '')));
    $companyEmail = trim((string) ($quotation->company_email ?: ($company['email'] ?? '')));
    $companyPhone = trim((string) ($quotation->company_phone ?: ($company['phone'] ?? '')));
    $companyLogoPath = trim((string) ($company['logo_path'] ?? ''));
    $companyAddressLines = collect([
        $company['address_line_1'] ?? null,
        $company['address_line_2'] ?? null,
    ])->map(fn ($line) => trim((string) $line))->filter();
@endphp

<div class="card d-print-none">
    <div class="card-body py-3">
        <div class="document-action-bar d-flex flex-wrap align-items-center justify-content-between gap-2">
            <div class="d-flex flex-wrap align-items-center gap-2">
                <a class="btn btn-outline-secondary" href="{{ route('quotes.show', $quote) }}">
                    <i data-lucide="arrow-left" class="icon-sm"></i>
                    Back
                </a>
            </div>
            <div class="d-flex flex-wrap align-items-center justify-content-end gap-2">
                @if($canSendQuotation)
                    <button class="btn btn-success" id="sendQuotationButton" type="button" data-bs-toggle="modal" data-bs-target="#sendQuotationModal" title="{{ $sentAtLabel ? 'Last sent ' . $sentAtLabel : 'Send to client' }}" data-bs-title="{{ $sentAtLabel ? 'Last sent ' . $sentAtLabel : 'Send to client' }}">
                        <i data-lucide="mail" class="icon-sm"></i>
                        <span data-quotation-send-label>{{ $isSentQuotation ? 'Resend' : 'Send to Client' }}</span>
                    </button>
                @endif
                <a class="btn btn-primary" href="{{ route('quotes.download', $quote) }}">
                    <i data-lucide="download" class="icon-sm"></i>
                    Download PDF
                </a>
                <div class="dropdown">
                    <button class="btn btn-outline-secondary btn-icon-only" type="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="More quote actions">
                        <i data-lucide="ellipsis-vertical" class="icon-sm"></i>
                    </button>
                    <div class="dropdown-menu dropdown-menu-end">
                        @if($isDraftQuotation)
                            <a class="dropdown-item" href="{{ route('quotations.edit', $quotation) }}">
                                <i data-lucide="edit-3" class="icon-sm"></i>Edit
                            </a>
                        @endif
                        <button class="dropdown-item" type="button" onclick="window.print()">
                            <i data-lucide="printer" class="icon-sm"></i>Print
                        </button>
                        @if($isSentQuotation)
                            <div class="dropdown-divider"></div>
                            <form action="{{ route('quotations.approve', $quotation) }}" method="POST">
                                @csrf
                                @method('PATCH')
                                <button class="dropdown-item text-success" type="submit">
                                    <i data-lucide="check" class="icon-sm"></i>Approve
                                </button>
                            </form>
                            <form action="{{ route('quotations.reject', $quotation) }}" data-confirm-button-class="btn-warning" data-confirm-confirm-text="Yes, Reject" data-confirm-message="Do you want to reject this quotation?" data-confirm-modal data-confirm-title="Reject quotation?" method="POST">
                                @csrf
                                @method('PATCH')
                                <button class="dropdown-item text-warning" type="submit">
                                    <i data-lucide="x" class="icon-sm"></i>Reject
                                </button>
                            </form>
                        @endif
                        @if($isSentQuotation || $isApprovedQuotation)
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item text-info" href="{{ $invoice ? route('invoice.details', ['invoice' => $invoice->id]) : route('invoice.create', ['quote' => $quote->id]) }}">
                                <i data-lucide="receipt-text" class="icon-sm"></i>{{ $invoice ? 'View Invoice' : 'Create Invoice' }}
                            </a>
                        @endif
                        @if($canDeleteQuotation)
                            <div class="dropdown-divider"></div>
                            <form action="{{ route('quotations.destroy', $quotation) }}" data-delete-confirm data-delete-message="Do you want to delete this quotation?" data-delete-title="Delete quotation?" method="POST">
                                @csrf
                                @method('DELETE')
                                <button class="dropdown-item text-danger" type="submit">
                                    <i data-lucide="trash-2" class="icon-sm"></i>Delete
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        <div class="small text-muted mt-2" data-quotation-send-state>
            @if($sentAtLabel)
                Sent ✓ {{ $sentAtLabel }}
            @endif
        </div>
        @include('partials.email-history', ['logs' => $quotation->emailLogs, 'retryTarget' => '#sendQuotationModal'])
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <!-- Header & Branding -->
                <div class="clearfix">
                    <div class="float-sm-end">
                        <div class="auth-logo">
                            @if($companyLogoPath !== '')
                                <img alt="{{ $companyName ?: 'Company' }} logo" class="me-1" height="24" src="{{ asset(ltrim($companyLogoPath, '/')) }}" />
                            @endif
                        </div>
                        @if($companyName !== '')
                            <h6 class="fw-bold mt-3 mb-2">{{ $companyName }}</h6>
                        @endif
                        <address class="mt-2">
                            @foreach($companyAddressLines as $companyAddressLine)
                                {{ $companyAddressLine }}<br />
                            @endforeach
                            @if($companyPhone !== '')
                                <abbr title="Phone">P:</abbr> {{ $companyPhone }}<br>
                            @endif
                            @if($companyEmail !== '')
                                <abbr title="Email">E:</abbr> {{ $companyEmail }}
                            @endif
                        </address>
                    </div>
                    <div class="float-sm-start">
                        <h2 class="fw-bold mb-3">QUOTE</h2>
                        <h5 class="card-title mb-2">Quote: {{ $quotation->quoteRequest->reference() }}</h5>
                        <p class="mb-2">{{ $quotation->quote_date?->format('d M, Y') ?? 'N/A' }}</p>
                        <span class="badge badge-soft-{{ $isApprovedQuotation ? 'success' : ($isRejectedQuotation ? 'danger' : ($isSentQuotation ? 'info' : 'warning')) }}" id="quotation-status-badge">
                            {{ $isRejectedQuotation ? 'Rejected' : ucfirst($quotation->status) }}
                        </span>
                        <span class="badge badge-soft-{{ $quotation->quoteRequest->statusBadgeClass() }} ms-1">
                            {{ $quotation->quoteRequest->statusLabel() }}
                        </span>
                    </div>
                </div>

                <hr class="my-4">

                <div class="row mt-4">
                    <div class="col-md-6">
                        <h6 class="fw-normal text-muted">Customer</h6>
                        <h6 class="fs-14 fw-bold">{{ $quotation->quoteRequest->full_name }}</h6>
                        <address class="mb-0">
                            {{ $quotation->quoteRequest->email }} • {{ $quotation->quoteRequest->phone }}<br />
                            <span class="text-muted">{{ $quotation->quoteRequest->serviceTypeLabel() }}</span>
                        </address>
                    </div>
                    <div class="col-md-6">
                        <h6 class="fw-normal text-muted">Moving Route</h6>
                        <h6 class="fs-14 fw-bold">{{ $quotation->moving_from ?? $quotation->quoteRequest->moving_from }}</h6>
                        <address class="mb-0">
                            to {{ $quotation->moving_to ?? $quotation->quoteRequest->moving_to }}<br />
                            Scheduled: {{ $quotation->move_date?->format('d M, Y') ?? $quotation->quoteRequest->move_date?->format('d M, Y') ?? 'Not specified' }}<br />
                            Size: {{ $quotation->quoteRequest->move_size ?: 'Not specified' }}
                        </address>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12">
                        <div class="table-responsive table-borderless mt-3 table-centered responsive-document-table">
                            <table class="table mb-0">
                                <thead class="bg-light bg-opacity-50">
                                    <tr>
                                        <th class="border-0 py-2">Detail</th>
                                        <th class="border-0 py-2">Description</th>
                                        <th class="text-end border-0 py-2">Value</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td data-label="Detail"><strong>Quote Amount (KES)</strong></td>
                                        <td data-label="Description">Professional moving service</td>
                                        <td class="text-end" data-label="Value"><strong>KES {{ number_format($quotation->quote_amount ?? 0, 2) }}</strong></td>
                                    </tr>
                                    <tr>
                                        <td data-label="Detail">Deposit Required</td>
                                        <td data-label="Description">{{ ($quotation->deposit_percentage ?? 30) }}% to secure booking</td>
                                        <td class="text-end" data-label="Value">KES {{ number_format($quotation->depositAmount(), 2) }}</td>
                                    </tr>
                                    <tr>
                                        <td data-label="Detail">Balance Due</td>
                                        <td data-label="Description">Amount due upon service completion</td>
                                        <td class="text-end" data-label="Value">KES {{ number_format($quotation->balanceDue(), 2) }}</td>
                                    </tr>
                                    <tr>
                                        <td data-label="Detail">Quote Valid Until</td>
                                        <td data-label="Description">{{ $quotation->quote_valid_until?->format('d M, Y') ?? 'Not specified' }}</td>
                                        <td class="text-end" data-label="Value">{{ $quotation->validityDays() ?? 'N/A' }} days</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                @if ($quotation->services_included && count($quotation->services_included) > 0)
                    <div class="row mt-4">
                        <div class="col-12">
                            <h6 class="fw-normal text-muted mb-2">Services Included</h6>
                            <div class="table-responsive table-borderless table-centered responsive-document-table">
                                <table class="table mb-0">
                                    <thead class="bg-light bg-opacity-50">
                                        <tr>
                                            <th class="border-0 py-2">Service</th>
                                            <th class="border-0 py-2">Description</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($quotation->services_included as $service)
                                            <tr>
                                                <td data-label="Service"><strong>{{ $service['name'] ?? 'Service' }}</strong></td>
                                                <td data-label="Description">{{ $service['description'] ?? 'Professional relocation service' }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                @endif

                <div class="row mt-4">
                    <div class="col-sm-7">
                        <div class="clearfix">
                            @if ($quotation->payment_terms)
                                <h6 class="text-muted">Payment Terms:</h6>
                                <small class="text-muted">{{ $quotation->payment_terms }}</small>
                            @endif

                            @if ($quotation->additional_notes)
                                <div class="mt-3">
                                    <h6 class="text-muted mb-1">Additional Notes:</h6>
                                    <small class="text-muted">{{ $quotation->additional_notes }}</small>
                                </div>
                            @endif

                            @if ($quotation->cancellation_notice_hours)
                                <div class="mt-3">
                                    <h6 class="text-muted mb-1">Cancellation Policy:</h6>
                                    <small class="text-muted">{{ $quotation->cancellationPolicyText() }}</small>
                                </div>
                            @endif
                        </div>
                    </div>
                    <div class="col-sm-5">
                        <div class="float-end quotation-total-box quotation-auth-box">
                            @unless($authorization['is_complete'] ?? false)
                                <div class="alert alert-warning text-start" role="alert">
                                    <div>No signature on file.</div>
                                    <a class="alert-link" href="{{ $authorization['profile_url'] }}">Upload your signature</a>
                                </div>
                            @endunless
                            <p><span class="fw-medium">Quote ID :</span>
                                <span class="float-end">{{ $quotation->quoteRequest->reference() }}</span>
                            </p>
                            <p><span class="fw-medium">Authorized By :</span>
                                <span class="float-end">{{ $authorization['name'] }}</span>
                            </p>
                            <p><span class="fw-medium">Job Title :</span>
                                <span class="float-end">{{ $authorization['job_title'] ?: 'Authorized Signatory' }}</span>
                            </p>
                            <p><span class="fw-medium">Approval Date :</span>
                                <span class="float-end">{{ $authorization['date_label'] }}</span>
                            </p>
                            <div class="mb-3">
                                <span class="fw-medium d-block mb-1">Signature :</span>
                                @if(! empty($authorization['signature_url']))
                                    <img alt="Authorized Signature" class="signature-clean" src="{{ $authorization['signature_url'] }}" style="border: none !important; outline: none !important; box-shadow: none !important; background: transparent !important; padding: 0 !important; max-height: 60px; max-width: 200px;">
                                @else
                                    <p class="small text-muted fst-italic mb-0">
                                        No signature on file.
                                        <a href="{{ $authorization['profile_url'] }}">Upload your signature</a>
                                    </p>
                                @endif
                            </div>
                            <h3>KES {{ number_format($quotation->quote_amount ?? 0, 2) }}</h3>
                        </div>
                        <div class="clearfix"></div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="sendQuotationModal" tabindex="-1" aria-labelledby="sendQuotationModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg send-document-modal">
        <div class="modal-content">
            <form action="{{ route('quotations.send', $quotation) }}" method="POST" data-quotation-send-form>
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="sendQuotationModalLabel">{{ $isSentQuotation ? 'Resend Quotation' : 'Send to Client' }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-danger d-none" data-quotation-send-error role="alert"></div>
                    <div class="mb-3">
                        <label class="form-label" for="quotationRecipientEmail">To</label>
                        <input class="form-control" id="quotationRecipientEmail" name="recipient_email" type="email" value="{{ old('recipient_email', $quote->email) }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="quotationEmailSubject">Subject</label>
                        <input class="form-control" id="quotationEmailSubject" name="subject" type="text" value="{{ old('subject', $sendQuotationSubject) }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="quotationEmailMessage">Message</label>
                        <textarea class="form-control" id="quotationEmailMessage" name="message" rows="12" style="min-height: 120px; resize: vertical;" required>{{ old('message', $sendQuotationMessage) }}</textarea>
                    </div>
                    <input type="hidden" name="attach_pdf" value="0">
                    <div class="form-check">
                        <input class="form-check-input" id="quotationAttachPdf" name="attach_pdf" type="checkbox" value="1" checked>
                        <label class="form-check-label" for="quotationAttachPdf">Attach Quotation PDF</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success" data-quotation-send-submit>
                        <i data-lucide="send" class="icon-sm me-1"></i>
                        {{ $isSentQuotation ? 'Resend' : 'Send to Client' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const form = document.querySelector('[data-quotation-send-form]');
        const modalElement = document.getElementById('sendQuotationModal');
        const modal = modalElement && window.bootstrap ? bootstrap.Modal.getOrCreateInstance(modalElement) : null;
        const errorBox = document.querySelector('[data-quotation-send-error]');
        const submitButton = document.querySelector('[data-quotation-send-submit]');
        const sendButton = document.getElementById('sendQuotationButton');
        const sendLabel = document.querySelector('[data-quotation-send-label]');
        const sendState = document.querySelector('[data-quotation-send-state]');
        const statusBadge = document.getElementById('quotation-status-badge');
        const shouldPrint = @json(request()->boolean('print'));

        if (shouldPrint) {
            window.setTimeout(() => window.print(), 250);
        }

        if (!form || !submitButton) {
            return;
        }

        const showToast = (message, className = 'bg-success') => {
            if (!message || !window.Toastify) {
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

        const setError = (message) => {
            if (!errorBox) {
                return;
            }

            errorBox.textContent = message || 'Quotation could not be sent. Please try again.';
            errorBox.classList.remove('d-none');
        };

        const refreshTooltip = (element, title) => {
            if (!element || !window.bootstrap) {
                return;
            }

            element.setAttribute('data-bs-title', title);
            element.setAttribute('title', title);

            const tooltip = bootstrap.Tooltip.getInstance(element);
            tooltip?.dispose();
            new bootstrap.Tooltip(element);
        };

        form.addEventListener('submit', async function (event) {
            event.preventDefault();

            const originalButtonHtml = submitButton.innerHTML;
            const formData = new FormData(form);

            errorBox?.classList.add('d-none');
            submitButton.disabled = true;
            submitButton.innerHTML = '<span class="spinner-border spinner-border-sm me-1" aria-hidden="true"></span>Sending...';

            try {
                const response = await fetch(form.action, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: formData,
                });
                const data = await response.json().catch(() => ({}));

                if (!response.ok) {
                    const validationMessage = data.errors
                        ? Object.values(data.errors).flat().join(' ')
                        : data.message;

                    throw new Error(validationMessage || 'Quotation could not be sent. Please try again.');
                }

                if (sendLabel) {
                    sendLabel.textContent = 'Resend';
                }

                if (sendState) {
                    sendState.textContent = data.sent_at_human ? `Sent ✓ ${data.sent_at_human}` : 'Sent ✓';
                }

                if (statusBadge) {
                    statusBadge.className = 'badge badge-soft-info';
                    statusBadge.textContent = 'Sent';
                }

                refreshTooltip(sendButton, data.sent_at_human ? `Last sent ${data.sent_at_human}` : 'Quotation sent');
                modal?.hide();
                showToast(data.message || `Quotation sent successfully to ${formData.get('recipient_email')}`);
            } catch (error) {
                setError(error.message);
            } finally {
                submitButton.disabled = false;
                submitButton.innerHTML = originalButtonHtml;

                if (window.lucide?.createIcons) {
                    window.lucide.createIcons();
                }
            }
        });
    });
</script>
@endsection
