@extends('layouts.vertical', ['title' => 'Create Quotation - ' . $quote->reference()])

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <form id="quotationForm" method="POST" action="{{ (isset($quotation) && $quotation->id) ? route('quotations.update', $quotation) : route('quotations.store') }}">
                    @csrf
                    @if (isset($quotation) && $quotation->id)
                        @method('PUT')
                    @endif

                    <input type="hidden" name="quote_request_id" value="{{ $quote->id }}">

                    <!-- Logo & title -->
                    <div class="clearfix mb-4">
                        <div class="float-sm-end">
                            <div class="auth-logo">
                                <img alt="logo-dark" class="logo-dark me-1" height="24" src="/images/logo-dark.png" />
                                <img alt="logo-light" class="logo-light me-1" height="24" src="/images/logo-white.png" />
                            </div>
                            <address class="mt-3">
                                +254 112587581 / +254111330980<br />
                                info@kwikshiftmovers.co.ke<br />
                                Londiani Road, Industrial Area, Nairobi
                            </address>
                        </div>
                        <div class="float-sm-start">
                            <h5 class="card-title mb-2">Quotation: {{ $quote->reference() }}</h5>
                            <div class="row">
                                <div class="col-6">
                                    <label class="form-label mb-1">Quote Date</label>
                                    <input type="date" name="quote_date" class="form-control form-control-sm" value="{{ old('quote_date', isset($quotation) ? $quotation->quote_date?->format('Y-m-d') : now()->format('Y-m-d')) }}" required>
                                </div>
                                <div class="col-6">
                                    <label class="form-label mb-1">Valid Until</label>
                                    <input type="date" name="quote_valid_until" class="form-control form-control-sm" value="{{ old('quote_valid_until', isset($quotation) ? $quotation->quote_valid_until?->format('Y-m-d') : now()->addDays(14)->format('Y-m-d')) }}" required>
                                </div>
                            </div>
                        </div>
                    </div>

                    <hr>

                    <!-- Customer Information -->
                    <div class="row mt-3">
                        <div class="col-md-6">
                            <h6 class="fw-normal text-muted">Customer</h6>
                            <h6 class="fs-14 fw-bold">{{ $quote->full_name }}</h6>
                            <address>
                                {{ $quote->email }}<br />
                                <abbr title="Phone">P:</abbr> {{ $quote->phone }}<br>
                                <span class="text-muted">{{ $quote->serviceTypeLabel() }}</span>
                            </address>
                        </div>
                        <div class="col-md-6">
                            <h6 class="fw-normal text-muted">Moving Route</h6>
                            <h6 class="fs-14 fw-bold">
                                <input type="text" name="moving_from" class="form-control form-control-sm mb-2" placeholder="From" value="{{ old('moving_from', isset($quotation) ? $quotation->moving_from : $quote->moving_from) }}" required>
                            </h6>
                            <address>
                                To: <input type="text" name="moving_to" class="form-control form-control-sm mb-2" placeholder="To" value="{{ old('moving_to', isset($quotation) ? $quotation->moving_to : $quote->moving_to) }}" required><br>
                                <label class="form-label mb-1">Move Date</label>
                                <input type="date" name="move_date" class="form-control form-control-sm" value="{{ old('move_date', isset($quotation) ? $quotation->move_date?->format('Y-m-d') : $quote->move_date?->format('Y-m-d')) }}" required>
                            </address>
                        </div>
                    </div>

                    <!-- Services & Pricing Table -->
                    <div class="row">
                        <div class="col-12">
                            <div class="table-responsive table-borderless text-nowrap mt-4 table-centered">
                                <table class="table mb-0">
                                    <thead class="bg-light bg-opacity-50">
                                        <tr>
                                            <th class="border-0 py-2">Service</th>
                                            <th class="border-0 py-2">Description</th>
                                            <th class="text-end border-0 py-2">
                                                <button type="button" class="btn btn-sm btn-outline-primary" id="addServiceBtn">+ Add</button>
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody id="servicesContainer">
                                        @if (isset($quotation) && $quotation->services_included)
                                            @foreach ($quotation->services_included as $service)
                                                <tr class="service-row">
                                                    <td>
                                                        <input type="text" name="services[name][]" class="form-control form-control-sm" placeholder="Service name" value="{{ $service['name'] }}" required>
                                                    </td>
                                                    <td>
                                                        <input type="text" name="services[description][]" class="form-control form-control-sm" placeholder="Description" value="{{ $service['description'] }}">
                                                    </td>
                                                    <td class="text-end">
                                                        <button type="button" class="btn btn-sm btn-outline-danger remove-service">Remove</button>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        @else
                                            <tr class="service-row">
                                                <td>
                                                    <input type="text" name="services[name][]" class="form-control form-control-sm" placeholder="Service name" value="Transportation & Fuel" required>
                                                </td>
                                                <td>
                                                    <input type="text" name="services[description][]" class="form-control form-control-sm" placeholder="Description" value="Moving vehicle rental and fuel">
                                                </td>
                                                <td class="text-end">
                                                    <button type="button" class="btn btn-sm btn-outline-danger remove-service">Remove</button>
                                                </td>
                                            </tr>
                                            <tr class="service-row">
                                                <td>
                                                    <input type="text" name="services[name][]" class="form-control form-control-sm" placeholder="Service name" value="Labour Charges" required>
                                                </td>
                                                <td>
                                                    <input type="text" name="services[description][]" class="form-control form-control-sm" placeholder="Description" value="Professional moving team (2-3 persons)">
                                                </td>
                                                <td class="text-end">
                                                    <button type="button" class="btn btn-sm btn-outline-danger remove-service">Remove</button>
                                                </td>
                                            </tr>
                                            <tr class="service-row">
                                                <td>
                                                    <input type="text" name="services[name][]" class="form-control form-control-sm" placeholder="Service name" value="Loading & Unloading" required>
                                                </td>
                                                <td>
                                                    <input type="text" name="services[description][]" class="form-control form-control-sm" placeholder="Description" value="Professional loading and unloading service">
                                                </td>
                                                <td class="text-end">
                                                    <button type="button" class="btn btn-sm btn-outline-danger remove-service">Remove</button>
                                                </td>
                                            </tr>
                                        @endif
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Notes & Amount -->
                    <div class="row mt-3">
                        <div class="col-sm-7">
                            <div class="clearfix pt-xl-3 pt-0">
                                <h6 class="text-muted mb-2">Payment Terms:</h6>
                                <textarea name="payment_terms" class="form-control form-control-sm" rows="2" placeholder="e.g., 50% deposit upfront, 50% on completion">{{ old('payment_terms', isset($quotation) && $quotation->payment_terms ? $quotation->payment_terms : '50% deposit upfront to confirm booking, 50% balance on day of move') }}</textarea>

                                <h6 class="text-muted mt-3 mb-2">Additional Notes:</h6>
                                <textarea name="additional_notes" class="form-control form-control-sm" rows="2" placeholder="Any additional information for the client">{{ old('additional_notes', isset($quotation) ? $quotation->additional_notes : '') }}</textarea>

                                <div class="mt-3">
                                    <label class="form-label">Cancellation Notice (Hours)</label>
                                    <input type="number" name="cancellation_notice_hours" class="form-control form-control-sm" value="{{ old('cancellation_notice_hours', isset($quotation) ? $quotation->cancellation_notice_hours : '24') }}" required>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-5">
                            <div class="float-end">
                                <div class="mb-3">
                                    <label class="form-label">Quote Amount (KES)</label>
                                    <input type="number" name="quote_amount" class="form-control form-control-sm quote-amount" step="0.01" placeholder="0.00" value="{{ old('quote_amount', isset($quotation) ? $quotation->quote_amount : '10000') }}" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Deposit %</label>
                                    <input type="number" name="deposit_percentage" class="form-control form-control-sm deposit-percent" step="0.01" value="{{ old('deposit_percentage', isset($quotation) ? $quotation->deposit_percentage : '50') }}" required>
                                </div>
                                <p><span class="fw-medium">Deposit Amount :</span>
                                    <span class="float-end deposit-display">KES 0.00</span>
                                </p>
                                <h3 id="totalAmount">KES 0.00</h3>
                            </div>
                            <div class="clearfix"></div>
                        </div>
                    </div>

                    <!-- Approval Section -->
                    <hr class="mt-4">
                    <div class="row mt-4">
                        <div class="col-md-4">
                            <label class="form-label">Authorized By (Name)</label>
                            <input type="text" name="authorized_by" class="form-control form-control-sm" value="{{ old('authorized_by', isset($quotation) ? $quotation->authorized_by : auth()->user()->name) }}" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Job Title/Role</label>
                            <input type="text" name="authorized_role" class="form-control form-control-sm" placeholder="e.g., Manager" value="{{ old('authorized_role', isset($quotation) ? $quotation->authorized_role : '') }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Approval Date</label>
                            <input type="date" name="approval_date" class="form-control form-control-sm" value="{{ old('approval_date', isset($quotation) ? $quotation->approval_date?->format('Y-m-d') : now()->format('Y-m-d')) }}" required>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="mt-5 mb-1">
                        <div class="d-flex flex-wrap justify-content-end gap-2 d-print-none">
                            <a class="btn btn-outline-secondary" href="{{ route('quotes.show', $quote) }}">Cancel</a>
                            <button type="submit" name="action" value="draft" class="btn btn-outline-primary">Save as Draft</button>
                            <button type="button" id="previewQuotationButton" class="btn btn-primary">Preview Quotation</button>
                            @if (isset($quotation) && $quotation->id && $quotation->status === 'draft')
                                <button type="submit" form="sendQuotationForm" class="btn btn-success">
                                    <i data-lucide="mail" class="align-middle me-1"></i>
                                    Send to Client
                                </button>
                            @endif
                        </div>
                    </div>
                </form>

                @if (isset($quotation) && $quotation->id && $quotation->status === 'draft')
                    <form id="sendQuotationForm" action="{{ route('quotations.send', $quotation) }}" method="POST">
                        @csrf
                    </form>
                @endif

                <div class="modal fade" id="quotationPreviewModal" tabindex="-1" aria-labelledby="quotationPreviewModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-xl modal-dialog-scrollable">
                        <div class="modal-content">
                            <div class="modal-header">
                                <div>
                                    <h5 class="modal-title" id="quotationPreviewModalLabel">Quotation Preview</h5>
                                    <p class="text-muted mb-0 small">Review the quotation inline before you save or send it.</p>
                                </div>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body bg-light" id="quotationPreviewBody"></div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const servicesContainer = document.getElementById('servicesContainer');
    const addServiceBtn = document.getElementById('addServiceBtn');
    const quoteAmount = document.querySelector('.quote-amount');
    const depositPercent = document.querySelector('.deposit-percent');
    const depositDisplay = document.querySelector('.deposit-display');
    const totalAmount = document.getElementById('totalAmount');
    const previewButton = document.getElementById('previewQuotationButton');
    const previewBody = document.getElementById('quotationPreviewBody');
    const previewModalElement = document.getElementById('quotationPreviewModal');
    const previewModal = previewModalElement && window.bootstrap ? new bootstrap.Modal(previewModalElement) : null;

    // Add service row
    addServiceBtn.addEventListener('click', function(e) {
        e.preventDefault();
        const row = document.createElement('tr');
        row.className = 'service-row';
        row.innerHTML = `
            <td>
                <input type="text" name="services[name][]" class="form-control form-control-sm" placeholder="Service name" required>
            </td>
            <td>
                <input type="text" name="services[description][]" class="form-control form-control-sm" placeholder="Description">
            </td>
            <td class="text-end">
                <button type="button" class="btn btn-sm btn-outline-danger remove-service">Remove</button>
            </td>
        `;
        servicesContainer.appendChild(row);
    });

    // Remove service row
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('remove-service')) {
            e.preventDefault();
            e.target.closest('tr').remove();
        }
    });

    // Update deposit calculation
    function updateDeposit() {
        if (quoteAmount.value) {
            const amount = parseFloat(quoteAmount.value);
            const percent = parseFloat(depositPercent.value) || 30;
            const deposit = (amount * percent) / 100;
            depositDisplay.textContent = 'KES ' + deposit.toLocaleString('en-KE', {minimumFractionDigits: 2, maximumFractionDigits: 2});
            totalAmount.textContent = 'KES ' + amount.toLocaleString('en-KE', {minimumFractionDigits: 2, maximumFractionDigits: 2});
        } else {
            depositDisplay.textContent = 'KES 0.00';
            totalAmount.textContent = 'KES 0.00';
        }
    }

    quoteAmount.addEventListener('change', updateDeposit);
    quoteAmount.addEventListener('keyup', updateDeposit);
    depositPercent.addEventListener('change', updateDeposit);
    depositPercent.addEventListener('keyup', updateDeposit);

    // Initial calculation
    updateDeposit();

    const formatCurrency = (value) => {
        const numericValue = Number.parseFloat(value || '0');

        return 'KES ' + numericValue.toLocaleString('en-KE', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    };

    const escapeHtml = (value) => {
        return String(value ?? '')
            .replaceAll('&', '&amp;')
            .replaceAll('<', '&lt;')
            .replaceAll('>', '&gt;')
            .replaceAll('"', '&quot;')
            .replaceAll("'", '&#039;');
    };

    const inputValue = (selector, fallback = 'Not provided') => {
        const field = document.querySelector(selector);
        const value = field ? field.value.trim() : '';

        return value === '' ? fallback : value;
    };

    const collectServices = () => {
        return Array.from(document.querySelectorAll('.service-row')).map((row) => {
            const inputs = row.querySelectorAll('input');

            return {
                name: inputs[0]?.value.trim() || 'Service',
                description: inputs[1]?.value.trim() || 'Description not provided',
            };
        }).filter((service) => service.name !== '');
    };

    if (previewButton && previewBody && previewModal) {
        previewButton.addEventListener('click', function () {
            const amount = Number.parseFloat(quoteAmount.value || '0');
            const percent = Number.parseFloat(depositPercent.value || '0');
            const depositAmount = amount * (percent / 100);
            const services = collectServices();

            previewBody.innerHTML = `
                <div class="bg-white text-dark p-3 p-md-4">
                    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 border-bottom pb-3 mb-4">
                        <div>
                            <h3 class="mb-1">Quotation Preview</h3>
                            <p class="text-muted mb-0">{{ $quote->reference() }}</p>
                        </div>
                        <div class="text-md-end">
                            <div class="fw-semibold">KwikShift Movers & Relocators</div>
                            <div class="text-muted small">Londiani Road, Industrial Area, Nairobi</div>
                            <div class="text-muted small">+254 112 587 581 • info@kwikshiftmovers.co.ke</div>
                        </div>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <div class="border rounded p-3 h-100">
                                <p class="text-muted mb-1">Customer</p>
                                <div class="fw-semibold">{{ $quote->full_name }}</div>
                                <div class="small text-muted">{{ $quote->email }}</div>
                                <div class="small text-muted">{{ $quote->phone }}</div>
                                <div class="small text-muted mt-2">{{ $quote->serviceTypeLabel() }}</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="border rounded p-3 h-100">
                                <p class="text-muted mb-1">Route & Schedule</p>
                                <div class="fw-semibold">${escapeHtml(inputValue('[name="moving_from"]'))} to ${escapeHtml(inputValue('[name="moving_to"]'))}</div>
                                <div class="small text-muted">Move date: ${escapeHtml(inputValue('[name="move_date"]'))}</div>
                                <div class="small text-muted">Valid until: ${escapeHtml(inputValue('[name="quote_valid_until"]'))}</div>
                            </div>
                        </div>
                    </div>

                    <div class="table-responsive mb-4">
                        <table class="table align-middle mb-0">
                            <thead class="bg-light-subtle">
                                <tr>
                                    <th>Service</th>
                                    <th>Description</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${services.map((service) => `
                                    <tr>
                                        <td>${escapeHtml(service.name)}</td>
                                        <td>${escapeHtml(service.description)}</td>
                                    </tr>
                                `).join('')}
                            </tbody>
                        </table>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-7">
                            <div class="border rounded p-3 h-100">
                                <p class="text-muted mb-1">Terms & Notes</p>
                                <div class="small text-muted mb-2">Payment terms: ${escapeHtml(inputValue('[name="payment_terms"]'))}</div>
                                <div class="small text-muted">Special notes: ${escapeHtml(inputValue('[name="additional_notes"]'))}</div>
                            </div>
                        </div>
                        <div class="col-md-5">
                            <div class="border rounded p-3 h-100">
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted">Quote amount</span>
                                    <span>${formatCurrency(amount)}</span>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted">Deposit (${percent || 0}%)</span>
                                    <span>${formatCurrency(depositAmount)}</span>
                                </div>
                                <div class="d-flex justify-content-between pt-2 border-top fw-semibold">
                                    <span>Authorized by</span>
                                    <span>${escapeHtml(inputValue('[name="authorized_by"]'))}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;

            previewModal.show();
        });
    }
});
</script>

@endsection
