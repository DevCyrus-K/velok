@extends('layouts.vertical', ['title' => 'Invoice Details'])

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

    .invoice-total-box {
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

        .invoice-total-box,
        .invoice-auth-box {
            float: none !important;
            max-width: none;
            width: 100%;
        }
    }
</style>
@endsection

@section('content')
@if (! $invoice)
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body text-center py-5">
                    <h5 class="mb-2">No invoice found</h5>
                    <p class="text-muted mb-4">Create an invoice first, then the details page will show the saved record.</p>
                    <a class="btn btn-primary" href="{{ route('invoice.index') }}">Back to invoices</a>
                </div>
            </div>
        </div>
    </div>
@else
    @php
        $customerPhone = trim((string) $invoice->customer_phone);
        $moveRoute = collect([$invoice->move_origin, $invoice->move_destination])->filter()->implode(' to ');
        $notes = trim((string) ($invoice->notes ?? ''));
        $company = $company ?? app(\App\Support\CompanyProfile::class)->data();
        $paymentMethods = $paymentMethods ?? app(\App\Support\PaymentSettings::class)->methodsForInvoice($invoice);
        $thankYouMessage = $thankYouMessage ?? app(\App\Support\CompanyProfile::class)->thankYouMessage();
        $authorization = $authorization ?? app(\App\Support\InvoiceAuthorization::class)->data($invoice, $company);
        $signatureDataUri = $signatureDataUri ?? app(\App\Support\InvoiceAuthorization::class)->signatureDataUri($invoice, $company);
        $companyName = trim((string) ($company['name'] ?? '')) ?: 'Company';
        $companyLogoPath = trim((string) ($company['logo_path'] ?? ''));
        $companyEmail = trim((string) ($company['email'] ?? ''));
        $companyPhone = trim((string) ($company['phone'] ?? ''));
        $companyAddressLines = collect([
            $company['address_line_1'] ?? null,
            $company['address_line_2'] ?? null,
        ])->map(fn ($line) => trim((string) $line))->filter();
        $companyContactLine = collect([$companyEmail, $companyPhone])->filter()->implode(' or ') ?: 'the contact details on this invoice';
        $sendInvoiceSubject = 'Invoice ' . $invoice->invoice_number . ' from ' . $companyName;
        $sendInvoiceMessage = 'Dear ' . $invoice->customer_name . "\n\n"
            . 'Please find your invoice ' . $invoice->invoice_number . ' for KES ' . number_format((float) $invoice->total_amount, 2) . ".\n\n"
            . 'Kindly review the attached invoice and use the payment details provided. For any questions, contact us at '
            . $companyContactLine . ".\n\n"
            . 'Thank you for choosing ' . $companyName . '.';
        $invoiceStatus = (string) $invoice->status;
        $linkedQuotation = $invoice->quoteRequest?->quotation;
        $linkedQuote = $invoice->quoteRequest;
        $isDraftInvoice = $invoiceStatus === \App\Models\Invoice::STATUS_DRAFT;
        $isSentInvoice = $invoiceStatus === \App\Models\Invoice::STATUS_SENT;
        $isPaidInvoice = $invoiceStatus === \App\Models\Invoice::STATUS_PAID;
        $isOverdueInvoice = $invoiceStatus === \App\Models\Invoice::STATUS_OVERDUE;
        $isVoidInvoice = in_array($invoiceStatus, [\App\Models\Invoice::STATUS_VOID, \App\Models\Invoice::STATUS_CANCELLED], true);
        $canSendInvoice = in_array($invoiceStatus, [
            \App\Models\Invoice::STATUS_DRAFT,
            \App\Models\Invoice::STATUS_SENT,
            \App\Models\Invoice::STATUS_OVERDUE,
            \App\Models\Invoice::STATUS_UNPAID,
            \App\Models\Invoice::STATUS_PENDING,
            \App\Models\Invoice::STATUS_FAILED,
        ], true);
        $canDeleteInvoice = $isDraftInvoice || $isVoidInvoice;
        $canMarkPaid = in_array($invoiceStatus, [
            \App\Models\Invoice::STATUS_SENT,
            \App\Models\Invoice::STATUS_OVERDUE,
            \App\Models\Invoice::STATUS_UNPAID,
            \App\Models\Invoice::STATUS_PENDING,
            \App\Models\Invoice::STATUS_FAILED,
        ], true);
        $canMarkUnpaid = ! in_array($invoiceStatus, [
            \App\Models\Invoice::STATUS_DRAFT,
            \App\Models\Invoice::STATUS_UNPAID,
            \App\Models\Invoice::STATUS_VOID,
            \App\Models\Invoice::STATUS_CANCELLED,
        ], true);
        $canMarkVoid = $isSentInvoice || $isOverdueInvoice || $invoiceStatus === \App\Models\Invoice::STATUS_UNPAID;
        $invoiceSentAtLabel = $invoice->sent_at?->format('d M Y, h:i A');
        $paidAtLabel = $invoice->paid_at?->format('d M Y \a\t H:i') ?? $invoice->updated_at?->format('d M Y \a\t H:i');
    @endphp

    <div class="card d-print-none">
        <div class="card-body py-3">
            <div class="document-action-bar d-flex flex-wrap align-items-center justify-content-between gap-2">
                <div class="d-flex flex-wrap align-items-center gap-2">
                    <a class="btn btn-outline-secondary" href="{{ route('invoice.index') }}">
                        <i data-lucide="arrow-left" class="icon-sm"></i>
                        Back
                    </a>
                </div>
                <div class="d-flex flex-wrap align-items-center justify-content-end gap-2">
                    @if($canSendInvoice)
                        <button class="btn btn-success" id="sendInvoiceButton" type="button" data-bs-toggle="modal" data-bs-target="#sendInvoiceModal" title="{{ $invoiceSentAtLabel ? 'Last sent ' . $invoiceSentAtLabel : 'Send invoice by email' }}" data-bs-title="{{ $invoiceSentAtLabel ? 'Last sent ' . $invoiceSentAtLabel : 'Send invoice by email' }}">
                            <i data-lucide="mail" class="icon-sm"></i>
                            <span data-invoice-send-label>{{ ($isSentInvoice || $isOverdueInvoice) ? 'Resend' : 'Send' }}</span>
                        </button>
                    @endif
                    @if($canSendInvoice && !empty($invoice->customer_phone))
                        <a class="btn btn-outline-success" href="{{ route('invoice.send-whatsapp', $invoice) }}" target="_blank" rel="noopener">
                            <x-icons.whatsapp class="icon-sm" />
                            Send via WhatsApp
                        </a>
                    @endif
                    @if($linkedQuotation)
                        <a class="btn btn-outline-info" href="{{ route('quotations.show', $linkedQuotation) }}">
                            <i data-lucide="file-text" class="icon-sm"></i>
                            View Quotation
                        </a>
                    @elseif($linkedQuote)
                        <a class="btn btn-outline-info" href="{{ route('quotes.show', $linkedQuote) }}">
                            <i data-lucide="message-square-quote" class="icon-sm"></i>
                            View Quote
                        </a>
                    @endif
                    <a class="btn btn-primary" href="{{ route('invoices.download', $invoice) }}">
                        <i data-lucide="download" class="icon-sm"></i>
                        Download PDF
                    </a>
                    <div class="dropdown">
                        <button class="btn btn-outline-secondary btn-icon-only" type="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="More invoice actions">
                            <i data-lucide="ellipsis-vertical" class="icon-sm"></i>
                        </button>
                        <div class="dropdown-menu dropdown-menu-end">
                            @if($isDraftInvoice)
                                <a class="dropdown-item" href="{{ route('invoice.edit', $invoice) }}">
                                    <i data-lucide="edit-3" class="icon-sm"></i>Edit
                                </a>
                            @endif
                            <button class="dropdown-item" type="button" onclick="window.print()">
                                <i data-lucide="printer" class="icon-sm"></i>Print
                            </button>
                            @if($canMarkPaid || $canMarkUnpaid || $canMarkVoid)
                                <div class="dropdown-divider"></div>
                            @endif
                            @if($canMarkPaid)
                                <form action="{{ route('invoice.mark-paid', $invoice) }}" method="POST">
                                    @csrf
                                    @method('PATCH')
                                    <button class="dropdown-item text-success" type="submit">
                                        <i data-lucide="check-circle" class="icon-sm"></i>Mark as Paid
                                    </button>
                                </form>
                            @endif
                            @if($canMarkUnpaid)
                                <form action="{{ route('invoice.mark-unpaid', $invoice) }}" method="POST">
                                    @csrf
                                    @method('PATCH')
                                    <button class="dropdown-item text-warning" type="submit">
                                        <i data-lucide="credit-card" class="icon-sm"></i>Mark as Unpaid
                                    </button>
                                </form>
                            @endif
                            @if($canMarkVoid)
                                <form action="{{ route('invoice.mark-void', $invoice) }}" data-confirm-button-class="btn-warning" data-confirm-confirm-text="Yes, Void" data-confirm-message="Do you want to mark this invoice as void?" data-confirm-modal data-confirm-title="Void invoice?" method="POST">
                                    @csrf
                                    @method('PATCH')
                                    <button class="dropdown-item text-warning" type="submit">
                                        <i data-lucide="ban" class="icon-sm"></i>Mark as Void
                                    </button>
                                </form>
                            @endif
                            @if($canDeleteInvoice)
                                <div class="dropdown-divider"></div>
                                <form action="{{ route('invoice.destroy', $invoice) }}" data-delete-confirm data-delete-message="Do you want to delete this invoice?" data-delete-title="Delete invoice?" method="POST">
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
            <div class="small text-muted mt-2" data-invoice-send-state>
                @if($invoiceSentAtLabel)
                    Sent ✓ {{ $invoiceSentAtLabel }}
                @endif
            </div>
            @if($isPaidInvoice)
                <div class="mt-2">
                    <span class="badge badge-soft-success">Paid — {{ $paidAtLabel }}</span>
                </div>
            @endif
            @include('partials.email-history', ['logs' => $invoice->emailLogs, 'retryTarget' => '#sendInvoiceModal'])
        </div>
    </div>

    @if($linkedQuotation && ! $linkedQuotation->deposit_paid && $linkedQuotation->status === \App\Models\Quotation::STATUS_APPROVED)
        <div class="card border-warning border-opacity-50 d-print-none">
            <div class="card-body d-flex flex-wrap align-items-center justify-content-between gap-3">
                <div>
                    <h5 class="mb-1">Deposit Pending</h5>
                    <p class="mb-0 text-muted">KES {{ number_format($linkedQuotation->depositAmount(), 2) }} awaiting payment</p>
                </div>
                <a class="btn btn-warning" href="{{ route('quotations.show', $linkedQuotation) }}">Mark Deposit Received</a>
            </div>
        </div>
    @elseif($linkedQuotation && $linkedQuotation->deposit_paid)
        <div class="card border-success border-opacity-50 d-print-none">
            <div class="card-body">
                <h5 class="mb-1">Deposit Received</h5>
                <p class="mb-1">KES {{ number_format($linkedQuotation->depositAmount(), 2) }}</p>
                <p class="mb-1">Ref: {{ $linkedQuotation->deposit_reference }}</p>
                <p class="mb-0 text-muted">{{ $linkedQuotation->deposit_paid_at?->format('d M Y \a\t H:i') }}</p>
            </div>
        </div>
    @endif

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="clearfix">
                        <div class="float-sm-end text-sm-end">
                            <div class="auth-logo d-inline-flex align-items-center gap-2">
                                @if($companyLogoPath !== '')
                                    <img alt="{{ $companyName }} logo" height="24" src="{{ asset(ltrim($companyLogoPath, '/')) }}" />
                                @endif
                                <span class="fw-semibold text-nowrap">{{ $companyName }}</span>
                            </div>
                            <address class="mt-3 mb-0">
                                @foreach($companyAddressLines as $companyAddressLine)
                                    {{ $companyAddressLine }}<br />
                                @endforeach
                                @if($companyPhone !== '')
                                    <abbr title="Phone">Phone No:</abbr> {{ $companyPhone }}<br />
                                @endif
                                @if($companyEmail !== '')
                                    <abbr title="Email">Email:</abbr> {{ $companyEmail }}
                                @endif
                            </address>
                        </div>
                        <div class="float-sm-start">
                            <h5 class="card-title mb-2">Invoice: {{ $invoice->invoice_number }}</h5>
                            <p class="mb-2">{{ $invoice->invoice_date?->format('d M, Y') ?? 'Date not recorded' }}</p>
                            <span class="badge badge-soft-{{ $invoice->statusBadgeClass() }}" id="invoice-status-badge">{{ $invoice->statusLabel() }}</span>
                        </div>
                    </div>

                    <div class="row mt-4 g-3">
                        <div class="col-md-6">
                            <h6 class="fw-normal text-muted">Customer</h6>
                            <h6 class="fs-14 fw-bold">{{ $invoice->customer_name }}</h6>
                            <address>
                                {{ $invoice->customer_email }}<br />
                                {{ $customerPhone !== '' ? $customerPhone : 'Phone not provided' }}
                            </address>
                        </div>
                        <div class="col-md-6">
                            <h6 class="fw-normal text-muted">Move Details</h6>
                            <h6 class="fs-14 fw-bold">{{ $moveRoute !== '' ? $moveRoute : 'Move route not recorded' }}</h6>
                            <address>
                                Move date: {{ $invoice->move_date?->format('d M, Y') ?? 'Not scheduled' }}<br />
                                Move size: {{ $invoice->move_size ?: 'Not recorded' }}<br />
                                Quote ref:
                                @if($linkedQuotation)
                                    <a href="{{ route('quotations.show', $linkedQuotation) }}">{{ $invoice->quote_reference ?: $linkedQuote?->reference() }}</a>
                                @elseif($linkedQuote)
                                    <a href="{{ route('quotes.show', $linkedQuote) }}">{{ $invoice->quote_reference ?: $linkedQuote->reference() }}</a>
                                @else
                                    {{ $invoice->quote_reference ?: 'Not linked' }}
                                @endif
                            </address>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12">
                            <div class="table-responsive table-borderless mt-3 table-centered responsive-document-table">
                                <table class="table mb-0">
                                    <thead class="bg-light bg-opacity-50">
                                        <tr>
                                            <th class="border-0 py-2">Line Item</th>
                                            <th class="border-0 py-2">Quantity</th>
                                            <th class="border-0 py-2">Unit Price</th>
                                            <th class="text-end border-0 py-2">Total</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($invoice->items as $item)
                                            <tr>
                                                <td data-label="Line Item">{{ $item->description }}</td>
                                                <td data-label="Quantity">{{ $item->quantity }}</td>
                                                <td data-label="Unit Price">KES {{ number_format((float) $item->unit_price, 2) }}</td>
                                                <td class="text-end" data-label="Total">KES {{ number_format((float) $item->total, 2) }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td class="text-center text-muted py-4" colspan="4">No line items have been added to this invoice yet.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="row mt-3">
                        <div class="col-sm-7">
                            <div class="clearfix pt-xl-3 pt-0">
                                <h6 class="text-muted">Notes:</h6>
                                <small class="text-muted">
                                    {{ $notes !== '' ? $notes : 'No additional notes recorded for this invoice.' }}
                                </small>
                            </div>
                        </div>
                        <div class="col-sm-5">
                            <div class="float-end invoice-total-box">
                                <p><span class="fw-medium">Sub-total :</span>
                                    <span class="float-end">KES {{ number_format((float) $invoice->subtotal, 2) }}</span>
                                </p>
                                <p><span class="fw-medium">Tax :</span>
                                    <span class="float-end">KES {{ number_format((float) $invoice->tax, 2) }}</span>
                                </p>
                                <h3>KES {{ number_format((float) $invoice->total_amount, 2) }}</h3>
                                <p class="text-muted mb-0">Due: {{ $invoice->due_date?->format('d M, Y') ?? 'Not recorded' }}</p>
                                <p class="text-muted mb-0">Payment: {{ $invoice->paymentMethodLabel() }}</p>
                            </div>
                            <div class="clearfix"></div>
                        </div>
                    </div>

                    <div class="row mt-4 g-3">
                        <div class="col-12">
                            <h6 class="text-muted mb-2">Payment Information</h6>
                        </div>
                        @forelse($paymentMethods as $method)
                            <div class="col-md-4">
                                <div class="border rounded p-3 h-100">
                                    <h6 class="fw-semibold mb-1">{{ $method['title'] }}</h6>
                                    @if(! empty($method['subtitle']))
                                        <p class="text-muted mb-2 small">{{ $method['subtitle'] }}</p>
                                    @endif
                                    @foreach($method['rows'] as $row)
                                        <div class="d-flex justify-content-between gap-3 small mb-1">
                                            <span class="text-muted">{{ $row['label'] }}</span>
                                            <span class="fw-medium text-end">{{ $row['value'] }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @empty
                            <div class="col-12 d-print-none">
                                <div class="alert alert-warning mb-0">
                                    Payment information not configured. Please update Payment Settings.
                                </div>
                            </div>
                        @endforelse
                    </div>

                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="p-3 invoice-auth-box">
                                <h6 class="text-muted mb-2">Authorization</h6>
                                <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
                                    <div>
                                        <p class="mb-1"><span class="fw-medium">Authorized By:</span> {{ $authorization['name'] }}</p>
                                        <p class="mb-1"><span class="fw-medium">Job Title:</span> {{ $authorization['job_title'] ?: 'Authorized Signatory' }}</p>
                                        <p class="mb-0"><span class="fw-medium">Date:</span> {{ $authorization['date_label'] }}</p>
                                    </div>
                                    <div class="text-md-end">
                                        @if(($authorization['is_complete'] ?? false) && ! empty($authorization['signature_url']))
                                            <img alt="Authorized Signature" class="signature-clean" src="{{ $authorization['signature_url'] }}" style="border: none !important; outline: none !important; box-shadow: none !important; background: transparent !important; padding: 0 !important; max-height: 60px; max-width: 200px;">
                                        @else
                                            <p class="small text-muted fst-italic mb-0">
                                                No signature on file.
                                                @if(! empty($authorization['profile_url']))
                                                    <a href="{{ $authorization['profile_url'] }}">Upload your signature</a>
                                                @endif
                                            </p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="border rounded p-3">
                                <h6 class="text-muted mb-2">Thank You</h6>
                                <p class="mb-0">{{ $thankYouMessage }}</p>
                            </div>
                        </div>
                    </div>

                    @if($invoice->stages->isNotEmpty())
                        @include('partials.booking-timeline', ['stageable' => $invoice])
                    @endif

                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="sendInvoiceModal" tabindex="-1" aria-labelledby="sendInvoiceModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg send-document-modal">
            <div class="modal-content">
                <form action="{{ route('invoices.send', $invoice) }}" method="POST" data-invoice-send-form>
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="sendInvoiceModalLabel">Send Invoice</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-danger d-none" data-invoice-send-error role="alert"></div>
                        <div class="mb-3">
                            <label class="form-label" for="invoiceRecipientEmail">To</label>
                            <input class="form-control @error('recipient_email') is-invalid @enderror" id="invoiceRecipientEmail" name="recipient_email" type="email" value="{{ old('recipient_email', $invoice->customer_email) }}" required>
                            @error('recipient_email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="invoiceEmailSubject">Subject</label>
                            <input class="form-control @error('subject') is-invalid @enderror" id="invoiceEmailSubject" name="subject" type="text" value="{{ old('subject', $sendInvoiceSubject) }}" required>
                            @error('subject')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="invoiceEmailMessage">Message</label>
                            <textarea class="form-control @error('message') is-invalid @enderror" id="invoiceEmailMessage" name="message" rows="7" style="min-height: 120px; resize: vertical;" required>{{ old('message', $sendInvoiceMessage) }}</textarea>
                            @error('message')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <input type="hidden" name="attach_pdf" value="0">
                        <div class="form-check">
                            <input class="form-check-input" id="invoiceAttachPdf" name="attach_pdf" type="checkbox" value="1" @checked(old('attach_pdf', '1') === '1')>
                            <label class="form-check-label" for="invoiceAttachPdf">Attach Invoice PDF</label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary" data-invoice-send-submit>Send Invoice</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endif
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const shouldOpenSendModal = @json($errors->has('recipient_email') || $errors->has('subject') || $errors->has('message') || $errors->has('attach_pdf'));
        const modalElement = document.getElementById('sendInvoiceModal');
        const modal = modalElement && window.bootstrap ? bootstrap.Modal.getOrCreateInstance(modalElement) : null;
        const form = document.querySelector('[data-invoice-send-form]');
        const errorBox = document.querySelector('[data-invoice-send-error]');
        const submitButton = document.querySelector('[data-invoice-send-submit]');
        const sendButton = document.getElementById('sendInvoiceButton');
        const sendLabel = document.querySelector('[data-invoice-send-label]');
        const sendState = document.querySelector('[data-invoice-send-state]');
        const statusBadge = document.getElementById('invoice-status-badge');
        const shouldPrint = @json(request()->boolean('print'));

        if (shouldOpenSendModal && modal) {
            modal.show();
        }

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

            errorBox.textContent = message || 'Invoice could not be sent. Please try again.';
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

                    throw new Error(validationMessage || 'Invoice could not be sent. Please try again.');
                }

                if (sendLabel) {
                    sendLabel.textContent = 'Resend';
                }

                if (sendState) {
                    sendState.textContent = data.status === 'sent' ? 'Sent ✓' : 'Delivery checked ✓';
                }

                if (statusBadge) {
                    statusBadge.className = `badge badge-soft-${data.status_badge_class || 'warning'}`;
                    statusBadge.textContent = data.status_label || 'Pending';
                }

                refreshTooltip(sendButton, 'Invoice email sent');
                modal?.hide();
                showToast(`Invoice email sent to ${data.recipient_email || formData.get('recipient_email')}`);
            } catch (error) {
                setError(error.message);
            } finally {
                submitButton.disabled = false;
                submitButton.innerHTML = originalButtonHtml;
            }
        });
    });
</script>
@endsection
