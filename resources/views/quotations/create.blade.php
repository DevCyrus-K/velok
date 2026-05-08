@extends('layouts.vertical', ['title' => 'Create Quote - ' . $quote->reference()])

@php
    $autofill = $autofill ?? [];
    $authorization = $authorization ?? [];
    $quoteDateValue = old('quote_date', $autofill['quote_date'] ?? now()->format('Y-m-d'));
    $validUntilValue = old('quote_valid_until', $autofill['quote_valid_until'] ?? now()->addDays(7)->format('Y-m-d'));
    $validityDays = (int) old('quote_validity_days', $autofill['quote_validity_days'] ?? 7);
    $defaultPaymentTerms = $autofill['payment_terms'] ?? '50% deposit required to confirm booking. Remaining balance due on day of move. Accepted payments: M-Pesa, Bank Transfer, Cash.';
    $defaultCancellationPolicy = $autofill['cancellation_policy'] ?? 'Free cancellation up to 48 hours before the scheduled move date. Cancellations made within 48 hours will incur a cancellation fee.';
    $serviceTypeOptions = $serviceTypeOptions ?? \App\Models\QuoteRequest::serviceTypeOptions();
    $selectedServiceType = \App\Support\LeadCategory::serviceTypeLabel(
        old('service_type', $quote->serviceTypeLabel()),
        ''
    );
    $company = $company ?? app(\App\Support\CompanyProfile::class)->data();
    $companyName = trim((string) ((isset($quotation) && $quotation->company_name) ? $quotation->company_name : ($company['name'] ?? '')));
    $companyEmail = trim((string) ((isset($quotation) && $quotation->company_email) ? $quotation->company_email : ($company['email'] ?? '')));
    $companyPhone = trim((string) ((isset($quotation) && $quotation->company_phone) ? $quotation->company_phone : ($company['phone'] ?? '')));
    $companyLogoPath = trim((string) ($company['logo_path'] ?? ''));
    $companyAddressLines = collect([
        $company['address_line_1'] ?? null,
        $company['address_line_2'] ?? null,
    ])->map(fn ($line) => trim((string) $line))->filter();
    $companyContactLine = collect([$companyPhone, $companyEmail])->filter()->implode(' • ');
@endphp

@section('css')
<style>
    .signature-clean {
        background: transparent !important;
        border: none !important;
        box-shadow: none !important;
        outline: none !important;
        padding: 0 !important;
    }

    @media (max-width: 575.98px) {
        #quotationPreviewModal .modal-dialog {
            height: 100vh;
            margin: 0;
            max-width: 100%;
        }

        #quotationPreviewModal .modal-content {
            border: 0;
            border-radius: 0;
            min-height: 100vh;
        }
    }
</style>
@endsection

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
                                @if($companyLogoPath !== '')
                                    <img alt="{{ $companyName ?: 'Company' }} logo" class="me-1" height="24" src="{{ asset(ltrim($companyLogoPath, '/')) }}" />
                                @endif
                            </div>
                            <address class="mt-3">
                                @if($companyPhone !== '')
                                    {{ $companyPhone }}<br />
                                @endif
                                @if($companyEmail !== '')
                                    {{ $companyEmail }}<br />
                                @endif
                                @foreach($companyAddressLines as $companyAddressLine)
                                    {{ $companyAddressLine }}@unless($loop->last)<br />@endunless
                                @endforeach
                            </address>
                        </div>
                        <div class="float-sm-start">
                            <h5 class="card-title mb-2">Quote: {{ $quote->reference() }}</h5>
                            <div class="row">
                                <div class="col-6">
                                    <label class="form-label mb-1">Quote Date</label>
                                    <input type="date" name="quote_date" class="form-control form-control-sm" value="{{ $quoteDateValue }}" required>
                                </div>
                                <div class="col-6">
                                    <label class="form-label mb-1">Valid Until</label>
                                    <input type="date" name="quote_valid_until" class="form-control form-control-sm" value="{{ $validUntilValue }}" required>
                                    <small class="text-muted">Quote validity: <span id="quoteValidityDays">{{ $validityDays }}</span> days</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <hr>

                    <!-- Customer Information -->
                    <div class="row mt-3">
                        <div class="col-md-6">
                            <h6 class="fw-normal text-muted">Customer Information</h6>
                            <div class="row g-2">
                                <div class="col-12">
                                    <label class="form-label mb-1">Customer Name</label>
                                    <input type="text" name="customer_name" class="form-control form-control-sm" value="{{ old('customer_name', $autofill['customer_name'] ?? $quote->full_name) }}" readonly>
                                </div>
                                <div class="col-12">
                                    <label class="form-label mb-1">Contact Info</label>
                                    <input type="text" name="contact_info" class="form-control form-control-sm" value="{{ old('contact_info', $autofill['contact_info'] ?? trim($quote->email . ' • ' . $quote->phone)) }}" readonly>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label mb-1">Service Type</label>
                                    <select class="form-control form-control-sm" name="service_type" required>
                                        <option value="" @selected($selectedServiceType === '')>Select Service Type</option>
                                        @foreach ($serviceTypeOptions as $value => $label)
                                            <option value="{{ $value }}" @selected($selectedServiceType === $value)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6" id="move_size_quotation_wrapper">
                                    <label class="form-label mb-1">Move Size</label>
                                    <!-- Residential Relocation Size Dropdown -->
                                    <select class="form-control form-control-sm" id="move_size_quotation_residential" name="move_size" style="display: block;">
                                        <option value="">Select Bedroom Type</option>
                                        <option value="Studio" @if(old('move_size', $quote->move_size) === 'Studio') selected @endif>Studio</option>
                                        <option value="Bedsitter" @if(old('move_size', $quote->move_size) === 'Bedsitter') selected @endif>Bedsitter</option>
                                        <option value="1 Bedroom" @if(old('move_size', $quote->move_size) === '1 Bedroom') selected @endif>1 Bedroom</option>
                                        <option value="2 Bedroom" @if(old('move_size', $quote->move_size) === '2 Bedroom') selected @endif>2 Bedroom</option>
                                        <option value="3 Bedroom" @if(old('move_size', $quote->move_size) === '3 Bedroom') selected @endif>3 Bedroom</option>
                                        <option value="4 Bedroom" @if(old('move_size', $quote->move_size) === '4 Bedroom') selected @endif>4 Bedroom</option>
                                        <option value="5 Bedroom" @if(old('move_size', $quote->move_size) === '5 Bedroom') selected @endif>5 Bedroom</option>
                                        <option value="Villa" @if(old('move_size', $quote->move_size) === 'Villa') selected @endif>Villa</option>
                                        <option value="Bungalow" @if(old('move_size', $quote->move_size) === 'Bungalow') selected @endif>Bungalow</option>
                                    </select>
                                    <!-- Office Relocation Size Text Input -->
                                    <input type="text" id="move_size_quotation_office" name="move_size" class="form-control form-control-sm" placeholder="Office size in square feet or description" value="{{ old('move_size', $quote->move_size) }}" style="display: none;">
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <h6 class="fw-normal text-muted">Moving Route</h6>
                            <div class="row g-2">
                                <div class="col-12">
                                    <label class="form-label mb-1" for="pickup_location">Pickup Location</label>
                                    <div class="location-autocomplete" data-location-autocomplete>
                                        <input type="text" id="pickup_location" name="moving_from" class="form-control form-control-sm" placeholder="Start typing a Kenyan pickup area" value="{{ old('moving_from', $autofill['pickup_location'] ?? $quote->moving_from) }}" autocomplete="off" data-location-next="#dropoff_location" required>
                                        <div class="location-autocomplete__menu" id="pickup_location_suggestions" role="listbox" aria-label="Pickup location suggestions"></div>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <label class="form-label mb-1" for="dropoff_location">Drop-off Location</label>
                                    <div class="location-autocomplete" data-location-autocomplete>
                                        <input type="text" id="dropoff_location" name="moving_to" class="form-control form-control-sm" placeholder="Start typing a Kenyan drop-off area" value="{{ old('moving_to', $autofill['dropoff_location'] ?? $quote->moving_to) }}" autocomplete="off" data-location-next="#quote_move_date" required>
                                        <div class="location-autocomplete__menu" id="dropoff_location_suggestions" role="listbox" aria-label="Drop-off location suggestions"></div>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <label class="form-label mb-1" for="quote_move_date">Preferred Move Date</label>
                                    <input type="date" id="quote_move_date" name="move_date" class="form-control form-control-sm" value="{{ old('move_date', $autofill['preferred_move_date'] ?? $quote->move_date?->format('Y-m-d')) }}" required>
                                </div>
                            </div>
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
                                <textarea name="payment_terms" class="form-control form-control-sm" rows="3" placeholder="e.g., 50% deposit upfront, 50% on completion">{{ old('payment_terms', $defaultPaymentTerms) }}</textarea>

                                <h6 class="text-muted mt-3 mb-2">Special Notes:</h6>
                                <textarea name="additional_notes" class="form-control form-control-sm" rows="2" placeholder="Any additional information for the client">{{ old('additional_notes', isset($quotation) && $quotation->additional_notes ? $quotation->additional_notes : ($autofill['special_notes'] ?? '')) }}</textarea>

                                <div class="mt-3">
                                    <label class="form-label">Cancellation Notice (Hours)</label>
                                    <input type="number" name="cancellation_notice_hours" class="form-control form-control-sm" value="{{ old('cancellation_notice_hours', $autofill['cancellation_notice_hours'] ?? 48) }}" required>
                                </div>

                                <div class="mt-3">
                                    <label class="form-label">Cancellation Policy</label>
                                    <textarea name="cancellation_policy" class="form-control form-control-sm" rows="3">{{ old('cancellation_policy', $defaultCancellationPolicy) }}</textarea>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-5">
                            <div class="float-end">
                                <div class="mb-3">
                                    <label class="form-label">Quote Amount (KES)</label>
                                    <input type="number" name="quote_amount" class="form-control form-control-sm quote-amount" min="0" step="0.01" placeholder="0.00" value="{{ old('quote_amount', isset($quotation) ? $quotation->quote_amount : '') }}" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Deposit %</label>
                                    <input type="number" name="deposit_percentage" class="form-control form-control-sm deposit-percent" inputmode="decimal" min="0" max="100" step="0.01" value="{{ old('deposit_percentage', isset($quotation) ? $quotation->deposit_percentage : '50') }}" required>
                                </div>
                                <p><span class="fw-medium">Deposit Amount :</span>
                                    <span class="float-end deposit-display">KES 0.00</span>
                                </p>
                                <p><span class="fw-medium">Deposit Rate :</span>
                                    <span class="float-end deposit-rate-display">0%</span>
                                </p>
                                <p><span class="fw-medium">Balance Due :</span>
                                    <span class="float-end balance-display">KES 0.00</span>
                                </p>
                                <h3 id="totalAmount">KES 0.00</h3>
                            </div>
                            <div class="clearfix"></div>
                        </div>
                    </div>

                    <!-- Approval Section -->
                    <hr class="mt-4">
                    <div class="row mt-4">
                        <div class="col-12">
                            @unless($authorization['is_complete'] ?? false)
                                <div class="alert alert-warning d-flex flex-wrap align-items-center justify-content-between gap-2" role="alert">
                                    <span>{{ $authorization['prompt'] ?? 'Please complete your profile to display authorization details' }}</span>
                                    <a class="btn btn-sm btn-outline-warning" href="{{ $authorization['profile_url'] ?? route('account.show') }}">Profile Settings</a>
                                </div>
                            @endunless
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Authorized By (Name)</label>
                            <input type="text" id="authorizedByDisplay" class="form-control form-control-sm" value="{{ $authorization['name'] ?? auth()->user()->name }}" readonly>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Job Title/Role</label>
                            <input type="text" class="form-control form-control-sm" value="{{ $authorization['job_title'] ?? '' }}" readonly>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Approval Date</label>
                            <input type="text" class="form-control form-control-sm" value="{{ $authorization['date_label'] ?? now()->format('d M Y') }}" readonly>
                        </div>
                        <div class="col-md-4 mt-3">
                            <label class="form-label d-block">Signature</label>
                            @if(! empty($authorization['signature_url']))
                                <img alt="Authorized signature" class="signature-clean" src="{{ $authorization['signature_url'] }}" style="border: none !important; outline: none !important; box-shadow: none !important; background: transparent !important; padding: 0 !important; max-height: 72px; max-width: 220px;">
                            @else
                                <span class="text-muted">Pending profile signature</span>
                            @endif
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="mt-5 mb-1">
                        <div class="d-flex flex-wrap justify-content-end gap-2 d-print-none">
                            <a class="btn btn-outline-secondary" href="{{ route('quotes.show', $quote) }}">Cancel</a>
                            <button type="submit" name="action" value="draft" class="btn btn-outline-primary">Save as Draft</button>
                            <button type="button" id="previewQuotationButton" class="btn btn-primary">Preview Quote</button>
	                            @if (isset($quotation) && $quotation->id)
	                                <button type="submit" form="sendQuotationForm" class="btn btn-success">
	                                    <i data-lucide="mail" class="align-middle me-1"></i>
	                                    Send to Client
                                </button>
                            @endif
                        </div>
                    </div>
                </form>

	                @if (isset($quotation) && $quotation->id)
	                    <form id="sendQuotationForm" action="{{ route('quotations.send', $quotation) }}" method="POST">
	                        @csrf
	                    </form>
                @endif

                <div class="modal fade" id="quotationPreviewModal" tabindex="-1" aria-labelledby="quotationPreviewModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-xl modal-dialog-scrollable">
                        <div class="modal-content">
                            <div class="modal-header">
                                <div>
                                    <h5 class="modal-title" id="quotationPreviewModalLabel">Quote Preview</h5>
                                    <p class="text-muted mb-0 small">Review the quote inline before you save or send it.</p>
                                </div>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body bg-light" id="quotationPreviewBody"></div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
                                <button type="submit" form="quotationForm" name="action" value="download" class="btn btn-info">
                                    <i data-lucide="download" class="align-middle me-1"></i>
                                    Download
                                </button>
                                <button type="submit" form="quotationForm" name="action" value="send" class="btn btn-success">
                                    <i data-lucide="mail" class="align-middle me-1"></i>
                                    Send
                                </button>
                                <button type="submit" form="quotationForm" name="action" value="continue" class="btn btn-primary">
                                    Continue
                                </button>
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
    const quotationForm = document.getElementById('quotationForm');
    const servicesContainer = document.getElementById('servicesContainer');
    const addServiceBtn = document.getElementById('addServiceBtn');
    const quoteAmount = document.querySelector('.quote-amount');
    const depositPercent = document.querySelector('.deposit-percent');
    const depositDisplay = document.querySelector('.deposit-display');
    const depositRateDisplay = document.querySelector('.deposit-rate-display');
    const balanceDisplay = document.querySelector('.balance-display');
    const totalAmount = document.getElementById('totalAmount');
    const quoteDateInput = document.querySelector('[name="quote_date"]');
    const validUntilInput = document.querySelector('[name="quote_valid_until"]');
    const quoteValidityDays = document.getElementById('quoteValidityDays');
    const previewButton = document.getElementById('previewQuotationButton');
    const previewBody = document.getElementById('quotationPreviewBody');
    const previewModalElement = document.getElementById('quotationPreviewModal');
    const previewModal = previewModalElement && window.bootstrap ? new bootstrap.Modal(previewModalElement) : null;
    const companyName = @json($companyName);
    const companyAddress = @json($companyAddressLines->implode(', '));
    const companyContact = @json($companyContactLine);

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

    const formatCurrency = (value) => {
        const numericValue = Number.parseFloat(value || '0');

        return 'KES ' + numericValue.toLocaleString('en-KE', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    };

    const formatPercent = (value) => {
        const numericValue = Number.isFinite(value) ? value : 0;

        return numericValue.toLocaleString('en-KE', {
            maximumFractionDigits: 2
        }) + '%';
    };

    const clampPercent = (value) => {
        const numericValue = Number.parseFloat(value || '0');

        if (!Number.isFinite(numericValue)) {
            return 0;
        }

        return Math.min(100, Math.max(0, numericValue));
    };

    const normalizedDepositPercent = () => {
        if (!depositPercent) {
            return 0;
        }

        const rawValue = depositPercent.value.trim();
        const safePercent = clampPercent(rawValue);

        if (rawValue !== '' && Number.parseFloat(rawValue) !== safePercent) {
            depositPercent.value = String(safePercent);
        }

        return safePercent;
    };

    // Update deposit calculation
    function updateDeposit() {
        const amount = Number.parseFloat(quoteAmount.value || '0');
        const safeAmount = Number.isFinite(amount) ? Math.max(0, amount) : 0;
        const safePercent = normalizedDepositPercent();
        const deposit = safeAmount * (safePercent / 100);
        const balance = Math.max(0, safeAmount - deposit);

        depositDisplay.textContent = formatCurrency(deposit);
        if (depositRateDisplay) depositRateDisplay.textContent = formatPercent(safePercent);
        if (balanceDisplay) balanceDisplay.textContent = formatCurrency(balance);
        totalAmount.textContent = formatCurrency(safeAmount);
    }

    const updateQuoteValidity = () => {
        if (!quoteDateInput || !validUntilInput || !quoteValidityDays) {
            return;
        }

        const quoteDate = new Date(quoteDateInput.value + 'T00:00:00');
        const validUntil = new Date(validUntilInput.value + 'T00:00:00');

        if (Number.isNaN(quoteDate.getTime()) || Number.isNaN(validUntil.getTime())) {
            quoteValidityDays.textContent = '0';
            return;
        }

        const days = Math.max(0, Math.round((validUntil - quoteDate) / 86400000));
        quoteValidityDays.textContent = String(days);
    };

    quoteAmount.addEventListener('input', updateDeposit);
    depositPercent.addEventListener('input', updateDeposit);
    depositPercent.addEventListener('change', updateDeposit);
    quoteDateInput?.addEventListener('input', updateQuoteValidity);
    validUntilInput?.addEventListener('input', updateQuoteValidity);
    quotationForm?.addEventListener('submit', function () {
        normalizedDepositPercent();
    });

    // Initial calculation
    updateDeposit();
    updateQuoteValidity();

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
            const safeAmount = Number.isFinite(amount) ? Math.max(0, amount) : 0;
            const percent = normalizedDepositPercent();
            const depositAmount = safeAmount * (percent / 100);
            const services = collectServices();

            previewBody.innerHTML = `
                <div class="bg-white text-dark p-3 p-md-4">
                    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 border-bottom pb-3 mb-4">
                        <div>
                            <h3 class="mb-1">Quote Preview</h3>
                            <p class="text-muted mb-0">{{ $quote->reference() }}</p>
                        </div>
                        <div class="text-md-end">
                            ${companyName ? `<div class="fw-semibold">${escapeHtml(companyName)}</div>` : ''}
                            ${companyAddress ? `<div class="text-muted small">${escapeHtml(companyAddress)}</div>` : ''}
                            ${companyContact ? `<div class="text-muted small">${escapeHtml(companyContact)}</div>` : ''}
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
                                    <span>${formatCurrency(safeAmount)}</span>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted">Deposit (${formatPercent(percent)})</span>
                                    <span>${formatCurrency(depositAmount)}</span>
                                </div>
                                <div class="d-flex justify-content-between pt-2 border-top fw-semibold">
                                    <span>Authorized by</span>
                                    <span>${escapeHtml(inputValue('#authorizedByDisplay'))}</span>
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


<script>
document.addEventListener("DOMContentLoaded", function() {
    const serviceTypeSelect = document.querySelector("[name=\"service_type\"]");
    const moveResidentialSelect = document.getElementById("move_size_quotation_residential");
    const moveOfficeInput = document.getElementById("move_size_quotation_office");

    if (!serviceTypeSelect || !moveResidentialSelect || !moveOfficeInput) {
        return;
    }

    function updateMoveSizeField() {
        const serviceType = serviceTypeSelect.value;
        if (serviceType === "Residential Relocation") {
            moveResidentialSelect.style.display = "block";
            moveOfficeInput.style.display = "none";
            moveResidentialSelect.name = "move_size";
            moveOfficeInput.name = "";
        } else if (serviceType === "Office Relocation") {
            moveResidentialSelect.style.display = "none";
            moveOfficeInput.style.display = "block";
            moveOfficeInput.placeholder = "Office size in square feet or description";
            moveResidentialSelect.name = "";
            moveOfficeInput.name = "move_size";
        } else if (serviceType === "Long-Distance Move") {
            moveResidentialSelect.style.display = "none";
            moveOfficeInput.style.display = "block";
            moveOfficeInput.placeholder = "Distance or route details";
            moveResidentialSelect.name = "";
            moveOfficeInput.name = "move_size";
        } else if (serviceType === "Packing & Storage") {
            moveResidentialSelect.style.display = "none";
            moveOfficeInput.style.display = "block";
            moveOfficeInput.placeholder = "Storage details or item description";
            moveResidentialSelect.name = "";
            moveOfficeInput.name = "move_size";
        } else {
            moveResidentialSelect.style.display = "none";
            moveOfficeInput.style.display = "none";
            moveResidentialSelect.name = "";
            moveOfficeInput.name = "";
        }
    }

    serviceTypeSelect.addEventListener("change", updateMoveSizeField);
    updateMoveSizeField();
});
</script>
@endsection
