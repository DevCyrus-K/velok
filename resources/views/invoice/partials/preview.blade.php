@php
    $company = $company ?? app(\App\Support\CompanyProfile::class)->data();
    $paymentMethods = $paymentMethods ?? app(\App\Support\PaymentSettings::class)->methodsForInvoice($invoice);
    $thankYouMessage = $thankYouMessage ?? app(\App\Support\CompanyProfile::class)->thankYouMessage();
    $authorization = $authorization ?? app(\App\Support\InvoiceAuthorization::class)->data($invoice, $company, auth()->user());
    $companyName = trim((string) ($company['name'] ?? ''));
    $companyLogoPath = trim((string) ($company['logo_path'] ?? ''));
    $companyAddressLine = trim((string) ($company['address_line_1'] ?? ''));
    $companyContactLine = collect([$company['phone'] ?? null, $company['email'] ?? null])
        ->map(fn ($value) => trim((string) $value))
        ->filter()
        ->implode(' • ');
@endphp

<div class="invoice-preview-sheet bg-white text-dark p-3 p-md-4">
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 border-bottom pb-3 mb-4">
        <div>
            <h3 class="mb-1">Invoice Preview</h3>
            <p class="text-muted mb-0">{{ $invoice->invoice_number }}</p>
        </div>
        <div class="text-md-end">
            <div class="d-inline-flex align-items-center gap-2 justify-content-md-end">
                @if($companyLogoPath !== '')
                    <img alt="{{ $companyName ?: 'Company' }} logo" height="24" src="{{ asset(ltrim($companyLogoPath, '/')) }}">
                @endif
                @if($companyName !== '')
                    <span class="fw-semibold">{{ $companyName }}</span>
                @endif
            </div>
            @if($companyAddressLine !== '')
                <div class="text-muted small">{{ $companyAddressLine }}</div>
            @endif
            @if($companyContactLine !== '')
                <div class="text-muted small">{{ $companyContactLine }}</div>
            @endif
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-6">
            <div class="border rounded p-3 h-100">
                <p class="text-muted mb-1">Invoice To</p>
                <div class="fw-semibold">{{ $invoice->customer_name }}</div>
                <div class="small text-muted">{{ $invoice->customer_email }}</div>
                <div class="small text-muted">{{ $invoice->customer_phone ?: 'Phone not provided' }}</div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="border rounded p-3 h-100">
                <p class="text-muted mb-1">Move Summary</p>
                <div class="fw-semibold">{{ $invoice->move_origin ?: 'Origin not recorded' }} to {{ $invoice->move_destination ?: 'Destination not recorded' }}</div>
                <div class="small text-muted">Move date: {{ $invoice->move_date?->format('d M Y') ?? 'Not scheduled' }}</div>
                <div class="small text-muted">Quote ref: {{ $invoice->quote_reference ?: 'Not linked' }}</div>
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
                @forelse($invoice->items as $item)
                <tr>
                    <td>{{ $item->description }}</td>
                    <td>{{ $item->quantity }}</td>
                    <td>KES {{ number_format((float) $item->unit_price, 2) }}</td>
                    <td class="text-end">KES {{ number_format((float) $item->total, 2) }}</td>
                </tr>
                @empty
                <tr>
                    <td class="text-center text-muted py-4" colspan="4">No line items have been added to this invoice yet.</td>
                </tr>
                @endforelse
            </tbody>
            <tfoot>
                <tr>
                    <td class="text-end fw-semibold" colspan="3">Subtotal</td>
                    <td class="text-end fw-semibold">KES {{ number_format((float) $invoice->subtotal, 2) }}</td>
                </tr>
                <tr>
                    <td class="text-end text-muted" colspan="3">Tax</td>
                    <td class="text-end">KES {{ number_format((float) $invoice->tax, 2) }}</td>
                </tr>
                <tr>
                    <td class="text-end fw-semibold border-top" colspan="3">Total</td>
                    <td class="text-end fw-semibold border-top">KES {{ number_format((float) $invoice->total_amount, 2) }}</td>
                </tr>
            </tfoot>
        </table>
    </div>

    <div class="row g-3 align-items-start">
        <div class="col-12">
            <div class="border rounded p-3 h-100">
                <p class="text-muted mb-1">Payment Details</p>
                <div class="small text-muted">Invoice date: {{ $invoice->invoice_date?->format('d M Y') ?? 'N/A' }}</div>
                <div class="small text-muted">Due date: {{ $invoice->due_date?->format('d M Y') ?? 'N/A' }}</div>
                <div class="small text-muted">Payment method: {{ $invoice->paymentMethodLabel() }}</div>
            </div>
        </div>
    </div>

    <div class="p-3 mt-3">
        <div class="d-flex flex-wrap justify-content-between gap-3">
            <div>
                <p class="text-muted mb-1">Authorization</p>
                <div class="fw-semibold">{{ $authorization['name'] }}</div>
                <div class="small text-muted">{{ $authorization['job_title'] ?: 'Authorized Signatory' }}</div>
                <div class="small text-muted">Date: {{ $authorization['date_label'] }}</div>
            </div>
            <div class="text-md-end">
                @if(($authorization['is_complete'] ?? false) && ! empty($authorization['signature_url']))
                    <img alt="Authorized Signature" src="{{ $authorization['signature_url'] }}" style="border: none !important; outline: none !important; box-shadow: none !important; background: transparent !important; padding: 0 !important; max-height: 60px; max-width: 200px;">
                @else
                    <span class="text-muted small fst-italic">No signature on file.</span>
                @endif
            </div>
        </div>
    </div>

    <div class="row g-3 mt-1">
        <div class="col-12">
            <p class="text-muted mb-1">Payment Information</p>
        </div>
        @foreach($paymentMethods as $method)
            <div class="col-md-4">
                <div class="border rounded p-3 h-100">
                    <div class="fw-semibold mb-1">{{ $method['title'] }}</div>
                    @if(! empty($method['subtitle']))
                        <div class="small text-muted mb-2">{{ $method['subtitle'] }}</div>
                    @endif
                    @foreach($method['rows'] as $row)
                        <div class="small d-flex justify-content-between gap-2">
                            <span class="text-muted">{{ $row['label'] }}</span>
                            <span class="text-end">{{ $row['value'] }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>

    <div class="border-top mt-4 pt-3 text-center">
        <div class="small text-muted">{{ $thankYouMessage }}</div>
    </div>
</div>
