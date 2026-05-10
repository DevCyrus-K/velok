@extends('layouts.vertical', ['title' => ($isEditing ?? false) ? 'Edit Invoice' : 'Create Invoice'])

@section('css')
<style>
    @media (max-width: 575.98px) {
        #invoicePreviewModal .modal-dialog {
            height: 100vh;
            margin: 0;
            max-width: 100%;
        }

        #invoicePreviewModal .modal-content {
            border: 0;
            border-radius: 0;
            min-height: 100vh;
        }
    }
</style>
@endsection

@section('content')
@php
    $invoice = $invoice ?? null;
    $isEditing = (bool) ($isEditing ?? false);
    $selectedQuote = $selectedQuote ?? null;
    $selectedQuoteId = old('quote_request_id', $invoice?->quote_request_id ?? $selectedQuote?->id);
    $nextInvoiceNumber = $nextInvoiceNumber ?? '';
    $savedItems = $isEditing && $invoice ? $invoice->items : collect();
    $prefillLineItems = collect($prefillLineItems ?? []);
    $quoteInvoiceLineItems = collect($quoteInvoiceLineItems ?? []);
    $defaultItems = $prefillLineItems->isNotEmpty()
        ? $prefillLineItems
        : collect([
            ['description' => 'Transportation & Fuel', 'quantity' => 1, 'unit_price' => 0],
            ['description' => 'Professional Moving Crew', 'quantity' => 1, 'unit_price' => 0],
        ]);
    $itemDescriptions = old('items.description', $savedItems->isNotEmpty() ? $savedItems->pluck('description')->all() : $defaultItems->pluck('description')->all());
    $itemQuantities = old('items.quantity', $savedItems->isNotEmpty() ? $savedItems->pluck('quantity')->all() : $defaultItems->pluck('quantity')->all());
    $itemPrices = old('items.unit_price', $savedItems->isNotEmpty() ? $savedItems->pluck('unit_price')->all() : $defaultItems->pluck('unit_price')->all());
    $lineItems = collect($itemDescriptions)->map(function ($description, $index) use ($itemQuantities, $itemPrices) {
        return [
            'description' => $description,
            'quantity' => $itemQuantities[$index] ?? 1,
            'unit_price' => $itemPrices[$index] ?? 0,
        ];
    })->values();
    $company = $company ?? app(\App\Support\CompanyProfile::class)->data();
    $companyName = trim((string) ($company['name'] ?? '')) ?: 'Company';
    $companyLogoPath = trim((string) ($company['logo_path'] ?? ''));
    $companyLogoUrl = $companyLogoPath !== '' ? asset(ltrim($companyLogoPath, '/')) : '';
    $companyAddressLines = collect([
        $company['address_line_1'] ?? null,
        $company['address_line_2'] ?? null,
    ])->map(fn ($line) => trim((string) $line))->filter();
    $companyContactLines = collect([
        filled($company['phone'] ?? null) ? 'Phone No: '.trim((string) $company['phone']) : null,
        filled($company['email'] ?? null) ? 'Email: '.trim((string) $company['email']) : null,
    ])->filter();
@endphp

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                @if (isset($errors) && $errors->any())
                    <div class="alert alert-danger">
                        <div class="fw-semibold mb-1">Please fix the invoice details below.</div>
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form id="invoiceForm" method="POST" action="{{ $isEditing ? route('invoice.update', $invoice) : route('invoice.store') }}">
                    @csrf
                    @if ($isEditing)
                        @method('PUT')
                    @endif

                    <div class="clearfix mb-4">
                        <div class="float-sm-end text-sm-end">
                            <div class="auth-logo d-inline-flex align-items-start gap-2 text-start">
                                @if($companyLogoUrl !== '')
                                    <span class="d-inline-flex align-items-center" style="min-height: 28px;">
                                        <img alt="{{ $companyName }} logo" height="24" src="{{ $companyLogoUrl }}" />
                                    </span>
                                @endif
                                <span class="d-inline-block lh-sm">
                                    <span class="d-block fw-semibold">{{ $companyName }}</span>
                                    @foreach($companyAddressLines as $companyAddressLine)
                                        <span class="d-block small text-muted {{ $loop->first ? 'mt-1' : '' }}">{{ $companyAddressLine }}</span>
                                    @endforeach
                                    @foreach($companyContactLines as $companyContactLine)
                                        <span class="d-block small text-muted">{{ $companyContactLine }}</span>
                                    @endforeach
                                </span>
                            </div>
                        </div>
                        <div class="float-sm-start">
                            <h5 class="card-title mb-2">{{ $isEditing ? 'Edit Invoice' : 'Create Invoice' }}</h5>
                            <div class="row g-2">
                                <div class="col-md-6">
                                    <label class="form-label mb-1">Invoice ID</label>
                                    <div class="input-group input-group-sm">
                                        <input class="form-control" id="invoiceNumber" name="invoice_number" placeholder="{{ $nextInvoiceNumber ?: 'Auto-generated' }}" type="text" value="{{ old('invoice_number', $isEditing ? $invoice?->invoice_number : '') }}">
                                        @unless ($isEditing)
                                            <button class="btn btn-outline-primary" data-fallback-number="{{ $nextInvoiceNumber }}" data-generate-url="{{ route('invoice.next-number') }}" id="generateInvoiceNumber" type="button">Generate</button>
                                        @endunless
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label mb-1">Invoice Date</label>
                                    <input class="form-control form-control-sm" name="invoice_date" required type="date" value="{{ old('invoice_date', $invoice?->invoice_date?->format('Y-m-d') ?? now()->format('Y-m-d')) }}">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label mb-1">Due Date</label>
                                    <input class="form-control form-control-sm" name="due_date" required type="date" value="{{ old('due_date', $invoice?->due_date?->format('Y-m-d') ?? now()->addDays(7)->format('Y-m-d')) }}">
                                </div>
                            </div>
                        </div>
                    </div>

                    <hr>

                    <div class="row mt-3 g-3">
                        <div class="col-md-6">
                            <h6 class="fw-normal text-muted">Customer</h6>
                            <div class="mb-2">
                                <label class="form-label mb-1">Link Quote</label>
                                <select class="form-select form-select-sm" id="quoteSelector" name="quote_request_id">
                                    <option value="">Manual invoice</option>
                                    @foreach ($quotes as $quote)
                                        @php
                                            $quoteRoute = trim(collect([$quote->moving_from, $quote->moving_to])->filter()->implode(' to '));
                                            $quoteLineDescription = Str::limit(collect([
                                                $quote->serviceTypeLabel(),
                                                $quoteRoute !== '' ? $quoteRoute : null,
                                                $quote->reference(),
                                            ])->filter()->implode(' - '), 255, '');
                                        @endphp
                                        <option value="{{ $quote->id }}"
                                            data-email="{{ $quote->email }}"
                                            data-line-description="{{ $quoteLineDescription }}"
                                            data-line-items='@json($quoteInvoiceLineItems->get($quote->id, []))'
                                            data-line-price="{{ round((float) ($quote->quotation?->quote_amount ?? 0), 2) }}"
                                            data-quote-url="{{ route('invoice.quote', $quote) }}"
                                            data-move-date="{{ $quote->move_date?->format('Y-m-d') }}"
                                            data-move-size="{{ $quote->move_size }}"
                                            data-name="{{ $quote->full_name }}"
                                            data-origin="{{ $quote->moving_from }}"
                                            data-phone="{{ $quote->phone }}"
                                            data-reference="{{ $quote->reference() }}"
                                            data-service="{{ $quote->serviceTypeLabel() }}"
                                            data-destination="{{ $quote->moving_to }}"
                                            @selected((string) $selectedQuoteId === (string) $quote->id)>
                                            {{ $quote->reference() }} - {{ $quote->full_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-2">
                                <label class="form-label mb-1">Customer Name</label>
                                <input class="form-control form-control-sm" id="customerName" name="customer_name" required type="text" value="{{ old('customer_name', $invoice?->customer_name ?? $selectedQuote?->full_name) }}">
                            </div>
                            <div class="mb-2">
                                <label class="form-label mb-1">Email</label>
                                <input class="form-control form-control-sm" id="customerEmail" name="customer_email" required type="email" value="{{ old('customer_email', $invoice?->customer_email ?? $selectedQuote?->email) }}">
                            </div>
                            <div class="mb-0">
                                <label class="form-label mb-1">Phone</label>
                                <input class="form-control form-control-sm" id="customerPhone" name="customer_phone" required type="text" value="{{ old('customer_phone', $invoice?->customer_phone ?? $selectedQuote?->phone) }}">
                            </div>
                        </div>

                        <div class="col-md-6">
                            <h6 class="fw-normal text-muted">Move Details</h6>
                            <div class="row g-2">
                                <div class="col-md-6">
                                    <label class="form-label mb-1">From</label>
                                    <input class="form-control form-control-sm" id="moveOrigin" name="move_origin" type="text" value="{{ old('move_origin', $invoice?->move_origin ?? $selectedQuote?->moving_from) }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label mb-1">To</label>
                                    <input class="form-control form-control-sm" id="moveDestination" name="move_destination" type="text" value="{{ old('move_destination', $invoice?->move_destination ?? $selectedQuote?->moving_to) }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label mb-1">Move Date</label>
                                    <input class="form-control form-control-sm" id="moveDate" name="move_date" type="date" value="{{ old('move_date', $invoice?->move_date?->format('Y-m-d') ?? $selectedQuote?->move_date?->format('Y-m-d')) }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label mb-1">Move Size</label>
                                    <input class="form-control form-control-sm" id="moveSize" name="move_size" type="text" value="{{ old('move_size', $invoice?->move_size ?? $selectedQuote?->move_size) }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label mb-1">Quote Ref</label>
                                    <input class="form-control form-control-sm" id="quoteReference" name="quote_reference" type="text" value="{{ old('quote_reference', $invoice?->quote_reference ?? $selectedQuote?->reference()) }}">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12">
                            <div class="table-responsive table-borderless text-nowrap mt-4 table-centered">
                                <table class="table mb-0">
                                    <thead class="bg-light bg-opacity-50">
                                        <tr>
                                            <th class="border-0 py-2">Line Item</th>
                                            <th class="border-0 py-2" style="width: 120px;">Quantity</th>
                                            <th class="border-0 py-2" style="width: 180px;">Unit Price</th>
                                            <th class="text-end border-0 py-2" style="width: 160px;">Total</th>
                                            <th class="text-end border-0 py-2" style="width: 90px;">
                                                <button class="btn btn-sm btn-outline-primary" id="addLineItem" type="button">Add</button>
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody id="lineItemsBody">
                                        @foreach ($lineItems as $item)
                                            <tr class="line-item-row">
                                                <td>
                                                    <input class="form-control form-control-sm line-description" name="items[description][]" placeholder="Service or charge" required type="text" value="{{ $item['description'] }}">
                                                </td>
                                                <td>
                                                    <input class="form-control form-control-sm line-quantity" min="1" name="items[quantity][]" required type="number" value="{{ $item['quantity'] }}">
                                                </td>
                                                <td>
                                                    <input class="form-control form-control-sm line-price" min="0" name="items[unit_price][]" required step="0.01" type="number" value="{{ $item['unit_price'] }}">
                                                </td>
                                                <td class="text-end">
                                                    <span class="line-total">KES 0.00</span>
                                                </td>
                                                <td class="text-end">
                                                    <button class="btn btn-sm btn-outline-danger remove-line-item" type="button">Remove</button>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="row mt-3">
                        <div class="col-sm-7">
                            <div class="clearfix pt-xl-3 pt-0">
                                <h6 class="text-muted mb-2">Notes:</h6>
                                <textarea class="form-control form-control-sm" name="notes" rows="4">{{ old('notes', $invoice?->notes) }}</textarea>
                            </div>
                        </div>
                        <div class="col-sm-5">
                            <div class="float-end w-100" style="max-width: 360px;">
                                <div class="mb-3">
                                    <label class="form-label mb-1">Payment Method</label>
                                    <select class="form-select form-select-sm" name="payment_method">
                                        <option value="">To be agreed</option>
                                        @foreach ($paymentMethodOptions as $value => $label)
                                            <option value="{{ $value }}" @selected(old('payment_method', $invoice?->payment_method ?? 'mobile_money') === $value)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label mb-1">Tax (KES)</label>
                                    <input class="form-control form-control-sm" id="taxAmount" min="0" name="tax" step="0.01" type="number" value="{{ old('tax', $invoice?->tax ?? 0) }}">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label mb-1">Status</label>
                                    <select class="form-select form-select-sm" name="status" required>
                                        @foreach ($statusOptions as $value => $label)
                                            <option value="{{ $value }}" @selected(old('status', $invoice?->status ?? 'draft') === $value)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <p><span class="fw-medium">Sub-total :</span>
                                    <span class="float-end" id="subtotalDisplay">KES 0.00</span>
                                </p>
                                <p><span class="fw-medium">Tax :</span>
                                    <span class="float-end" id="taxDisplay">KES 0.00</span>
                                </p>
                                <h3 id="totalDisplay">KES 0.00</h3>
                            </div>
                            <div class="clearfix"></div>
                        </div>
                    </div>

                    <div class="mt-5 mb-1">
                        <div class="d-flex flex-wrap justify-content-end gap-2 d-print-none">
                            <a class="btn btn-outline-secondary" href="{{ $isEditing ? route('invoice.details', ['invoice' => $invoice->id]) : route('invoice.index') }}">Cancel</a>
                            <button class="btn btn-outline-primary" id="previewInvoiceButton" type="button">Preview Invoice</button>
                        </div>
                    </div>
                </form>

                <div class="modal fade" id="invoicePreviewModal" tabindex="-1" aria-labelledby="invoicePreviewModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-xl modal-dialog-scrollable">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="invoicePreviewModalLabel">Invoice Preview</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body bg-light" id="invoicePreviewBody"></div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
                                <button class="btn btn-primary" form="invoiceForm" type="submit">{{ $isEditing ? 'Update Invoice' : 'Save Invoice' }}</button>
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
        const quoteSelector = document.getElementById('quoteSelector');
        const invoiceNumberInput = document.getElementById('invoiceNumber');
        const generateInvoiceNumber = document.getElementById('generateInvoiceNumber');
        const lineItemsBody = document.getElementById('lineItemsBody');
        const addLineItem = document.getElementById('addLineItem');
        const taxAmount = document.getElementById('taxAmount');
        const subtotalDisplay = document.getElementById('subtotalDisplay');
        const taxDisplay = document.getElementById('taxDisplay');
        const totalDisplay = document.getElementById('totalDisplay');
        const previewButton = document.getElementById('previewInvoiceButton');
        const previewBody = document.getElementById('invoicePreviewBody');
        const previewModalElement = document.getElementById('invoicePreviewModal');
        const previewModal = previewModalElement && window.bootstrap ? new bootstrap.Modal(previewModalElement) : null;
        const companyLogoUrl = @json($companyLogoUrl);
        const companyName = @json($companyName);
        const companyAddressLines = @json($companyAddressLines->values());
        const companyContactLines = @json($companyContactLines->values());

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

        const fieldValue = (selector, fallback = 'Not provided') => {
            const field = document.querySelector(selector);
            const value = field ? field.value.trim() : '';

            return value === '' ? fallback : value;
        };

        const selectedText = (selector, fallback = 'Not provided') => {
            const field = document.querySelector(selector);
            const value = field?.selectedOptions?.[0]?.textContent.trim() || '';

            return value === '' ? fallback : value;
        };

        const setField = (selector, value) => {
            const field = document.querySelector(selector);

            if (field) {
                field.value = value || '';
            }
        };

        const parseLineItems = (value) => {
            if (!value) {
                return null;
            }

            try {
                const parsed = JSON.parse(value);

                return Array.isArray(parsed) ? parsed : null;
            } catch (error) {
                return null;
            }
        };

        const applyQuoteData = (quote) => {
            setField('#customerName', quote.customer_name || quote.name);
            setField('#customerEmail', quote.customer_email || quote.email);
            setField('#customerPhone', quote.customer_phone || quote.phone);
            setField('#moveOrigin', quote.move_origin || quote.origin);
            setField('#moveDestination', quote.move_destination || quote.destination);
            setField('#moveDate', quote.move_date);
            setField('#moveSize', quote.move_size);
            setField('#quoteReference', quote.reference);

            if (Array.isArray(quote.line_items) && quote.line_items.length > 0) {
                replaceLineItems(quote.line_items);
            }
        };

        const collectLineItems = () => {
            return Array.from(document.querySelectorAll('.line-item-row')).map((row) => {
                const description = row.querySelector('.line-description')?.value.trim() || '';
                const quantity = Number.parseInt(row.querySelector('.line-quantity')?.value || '1', 10);
                const unitPrice = Number.parseFloat(row.querySelector('.line-price')?.value || '0');
                const safeQuantity = Number.isFinite(quantity) && quantity > 0 ? quantity : 1;
                const safeUnitPrice = Number.isFinite(unitPrice) && unitPrice >= 0 ? unitPrice : 0;

                return {
                    description,
                    quantity: safeQuantity,
                    unitPrice: safeUnitPrice,
                    total: safeQuantity * safeUnitPrice,
                };
            }).filter((item) => item.description !== '');
        };

        const recalculateTotals = () => {
            let subtotal = 0;

            document.querySelectorAll('.line-item-row').forEach((row) => {
                const quantity = Number.parseInt(row.querySelector('.line-quantity')?.value || '1', 10);
                const unitPrice = Number.parseFloat(row.querySelector('.line-price')?.value || '0');
                const lineTotal = Math.max(1, Number.isFinite(quantity) ? quantity : 1) * Math.max(0, Number.isFinite(unitPrice) ? unitPrice : 0);
                subtotal += lineTotal;

                const lineTotalElement = row.querySelector('.line-total');

                if (lineTotalElement) {
                    lineTotalElement.textContent = formatCurrency(lineTotal);
                }
            });

            const tax = Number.parseFloat(taxAmount?.value || '0');
            const safeTax = Number.isFinite(tax) && tax >= 0 ? tax : 0;

            subtotalDisplay.textContent = formatCurrency(subtotal);
            taxDisplay.textContent = formatCurrency(safeTax);
            totalDisplay.textContent = formatCurrency(subtotal + safeTax);
        };

        const bindRow = (row) => {
            row.querySelectorAll('.line-quantity, .line-price').forEach((input) => {
                input.addEventListener('input', recalculateTotals);
            });
        };

        const normalizedLineItem = (item = {}) => {
            const quantity = Number.parseInt(item.quantity || '1', 10);
            const unitPrice = Number.parseFloat(item.unit_price ?? item.unitPrice ?? '0');

            return {
                description: item.description || '',
                quantity: Number.isFinite(quantity) && quantity > 0 ? quantity : 1,
                unitPrice: Number.isFinite(unitPrice) && unitPrice >= 0 ? unitPrice : 0,
            };
        };

        const lineItemHtml = (item = {}) => {
            const lineItem = normalizedLineItem(item);

            return `
                <td>
                    <input class="form-control form-control-sm line-description" name="items[description][]" placeholder="Service or charge" required type="text" value="${escapeHtml(lineItem.description)}">
                </td>
                <td>
                    <input class="form-control form-control-sm line-quantity" min="1" name="items[quantity][]" required type="number" value="${lineItem.quantity}">
                </td>
                <td>
                    <input class="form-control form-control-sm line-price" min="0" name="items[unit_price][]" required step="0.01" type="number" value="${lineItem.unitPrice.toFixed(2)}">
                </td>
                <td class="text-end">
                    <span class="line-total">KES 0.00</span>
                </td>
                <td class="text-end">
                    <button class="btn btn-sm btn-outline-danger remove-line-item" type="button">Remove</button>
                </td>
            `;
        };

        const appendLineItem = (item = {}) => {
            const row = document.createElement('tr');
            row.className = 'line-item-row';
            row.innerHTML = lineItemHtml(item);

            lineItemsBody.appendChild(row);
            bindRow(row);
        };

        const addBlankLine = () => {
            appendLineItem();
            recalculateTotals();
        };

        const replaceLineItems = (items = []) => {
            const usableItems = Array.isArray(items)
                ? items.filter((item) => (item.description || '').trim() !== '')
                : [];

            lineItemsBody.innerHTML = '';
            (usableItems.length > 0 ? usableItems : [{}]).forEach(appendLineItem);
            recalculateTotals();
        };

        if (quoteSelector) {
            quoteSelector.addEventListener('change', async function () {
                const selected = this.selectedOptions[0];

                if (!selected || selected.value === '') {
                    return;
                }

                if (selected.dataset.quoteUrl) {
                    this.disabled = true;

                    try {
                        const response = await fetch(selected.dataset.quoteUrl, {
                            headers: {
                                Accept: 'application/json',
                                'X-Requested-With': 'XMLHttpRequest',
                            },
                        });

                        if (response.ok) {
                            applyQuoteData(await response.json());
                            return;
                        }
                    } catch (error) {
                        // Keep the form usable with the option data if the fetch is interrupted.
                    } finally {
                        this.disabled = false;
                    }
                }

                applyQuoteData({
                    name: selected.dataset.name,
                    email: selected.dataset.email,
                    phone: selected.dataset.phone,
                    origin: selected.dataset.origin,
                    destination: selected.dataset.destination,
                    move_date: selected.dataset.moveDate,
                    move_size: selected.dataset.moveSize,
                    reference: selected.dataset.reference,
                    line_items: parseLineItems(selected.dataset.lineItems) ?? (selected.dataset.lineDescription ? [{
                        description: selected.dataset.lineDescription,
                        quantity: 1,
                        unit_price: selected.dataset.linePrice || 0,
                    }] : []),
                });
            });
        }

        if (generateInvoiceNumber && invoiceNumberInput) {
            generateInvoiceNumber.addEventListener('click', async function () {
                const fallbackNumber = this.dataset.fallbackNumber || '';

                this.disabled = true;

                try {
                    const response = await fetch(this.dataset.generateUrl, {
                        headers: {
                            Accept: 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                    });

                    if (response.ok) {
                        const payload = await response.json();
                        invoiceNumberInput.value = payload.invoice_number || fallbackNumber;
                        return;
                    }
                } catch (error) {
                    // Use the rendered next number if the request cannot complete.
                } finally {
                    this.disabled = false;
                }

                invoiceNumberInput.value = fallbackNumber;
            });
        }

        if (addLineItem) {
            addLineItem.addEventListener('click', addBlankLine);
        }

        document.addEventListener('click', function (event) {
            if (!event.target.classList.contains('remove-line-item')) {
                return;
            }

            const rows = document.querySelectorAll('.line-item-row');

            if (rows.length <= 1) {
                return;
            }

            event.target.closest('tr')?.remove();
            recalculateTotals();
        });

        document.querySelectorAll('.line-item-row').forEach(bindRow);

        if (taxAmount) {
            taxAmount.addEventListener('input', recalculateTotals);
        }

        if (previewButton && previewBody && previewModal) {
            previewButton.addEventListener('click', function () {
                const items = collectLineItems();
                const subtotal = items.reduce((total, item) => total + item.total, 0);
                const tax = Number.parseFloat(taxAmount?.value || '0') || 0;
                const route = [fieldValue('#moveOrigin', ''), fieldValue('#moveDestination', '')].filter(Boolean).join(' to ') || 'Move route not recorded';

                previewBody.innerHTML = `
                    <div class="bg-white text-dark p-3 p-md-4">
                        <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 border-bottom pb-3 mb-4">
                            <div>
                                <h3 class="mb-1">Invoice Preview</h3>
                                <p class="text-muted mb-0">${escapeHtml(fieldValue('[name="invoice_number"]', 'Auto-generated'))}</p>
                            </div>
                            <div class="text-md-end">
                                <div class="d-inline-flex align-items-start gap-2 justify-content-md-end text-start">
                                    ${companyLogoUrl ? `<img alt="${escapeHtml(companyName)} logo" height="24" src="${escapeHtml(companyLogoUrl)}">` : ''}
                                    <span class="d-inline-block lh-sm">
                                        <span class="d-block fw-semibold">${escapeHtml(companyName)}</span>
                                        ${companyAddressLines.map((line, index) => `<span class="d-block text-muted small ${index === 0 ? 'mt-1' : ''}">${escapeHtml(line)}</span>`).join('')}
                                        ${companyContactLines.map((line) => `<span class="d-block text-muted small">${escapeHtml(line)}</span>`).join('')}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <div class="border rounded p-3 h-100">
                                    <p class="text-muted mb-1">Bill To</p>
                                    <div class="fw-semibold">${escapeHtml(fieldValue('#customerName'))}</div>
                                    <div class="small text-muted">${escapeHtml(fieldValue('#customerEmail'))}</div>
                                    <div class="small text-muted">${escapeHtml(fieldValue('#customerPhone'))}</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="border rounded p-3 h-100">
                                    <p class="text-muted mb-1">Move Summary</p>
                                    <div class="fw-semibold">${escapeHtml(route)}</div>
                                    <div class="small text-muted">Move date: ${escapeHtml(fieldValue('#moveDate', 'Not scheduled'))}</div>
                                    <div class="small text-muted">Quote ref: ${escapeHtml(fieldValue('#quoteReference', 'Not linked'))}</div>
                                </div>
                            </div>
                        </div>

                        <div class="table-responsive mb-4">
                            <table class="table align-middle mb-0">
                                <thead class="bg-light-subtle">
                                    <tr>
                                        <th>Line Item</th>
                                        <th>Qty</th>
                                        <th>Unit Price</th>
                                        <th class="text-end">Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    ${items.map((item) => `
                                        <tr>
                                            <td>${escapeHtml(item.description)}</td>
                                            <td>${item.quantity}</td>
                                            <td>${formatCurrency(item.unitPrice)}</td>
                                            <td class="text-end">${formatCurrency(item.total)}</td>
                                        </tr>
                                    `).join('') || '<tr><td class="text-center text-muted py-4" colspan="4">No line items added.</td></tr>'}
                                </tbody>
                            </table>
                        </div>

                        <div class="row g-3 align-items-start">
                            <div class="col-md-7">
                                <div class="border rounded p-3 h-100">
                                    <p class="text-muted mb-1">Payment Details</p>
                                    <div class="small text-muted">Invoice date: ${escapeHtml(fieldValue('[name="invoice_date"]'))}</div>
                                    <div class="small text-muted">Due date: ${escapeHtml(fieldValue('[name="due_date"]'))}</div>
                                    <div class="small text-muted">Status: ${escapeHtml(selectedText('[name="status"]'))}</div>
                                    <div class="small text-muted">Notes: ${escapeHtml(fieldValue('[name="notes"]', 'No notes recorded'))}</div>
                                </div>
                            </div>
                            <div class="col-md-5">
                                <div class="border rounded p-3 h-100">
                                    <div class="d-flex justify-content-between mb-2">
                                        <span class="text-muted">Subtotal</span>
                                        <span>${formatCurrency(subtotal)}</span>
                                    </div>
                                    <div class="d-flex justify-content-between mb-2">
                                        <span class="text-muted">Tax</span>
                                        <span>${formatCurrency(tax)}</span>
                                    </div>
                                    <div class="d-flex justify-content-between pt-2 border-top fw-semibold">
                                        <span>Total</span>
                                        <span>${formatCurrency(subtotal + tax)}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                `;

                previewModal.show();
            });
        }

        recalculateTotals();
    });
</script>
@endsection
