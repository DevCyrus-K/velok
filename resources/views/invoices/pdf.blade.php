<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Invoice {{ $invoice->invoice_number }}</title>
    <style>
        @page { margin: 15mm; }
        * { box-sizing: border-box; }
        body {
            color: #1f2937;
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
            line-height: 1.45;
            margin: 0;
        }
        h1, h2, h3, h4, p { margin: 0; }
        .muted { color: #6b7280; }
        .text-right { text-align: right; }
        .header-table, .summary-table, .items, .totals-table, .payment-table, .auth-table {
            border-collapse: collapse;
            width: 100%;
        }
        .header {
            border-bottom: 1px solid #d1d5db;
            margin-bottom: 18px;
            padding-bottom: 14px;
        }
        .logo {
            max-height: 30px;
            max-width: 150px;
        }
        .company-name {
            color: #111827;
            font-size: 15px;
            font-weight: 700;
            margin-top: 6px;
        }
        .document-title {
            color: #111827;
            font-size: 24px;
            letter-spacing: 0;
            margin-bottom: 4px;
        }
        .status {
            background: #e0f2fe;
            border-radius: 3px;
            color: #0369a1;
            display: inline-block;
            font-size: 10px;
            font-weight: 700;
            margin-top: 8px;
            padding: 4px 8px;
            text-transform: uppercase;
        }
        .box {
            border: 1px solid #d1d5db;
            border-radius: 5px;
            padding: 10px;
            vertical-align: top;
        }
        .section-title {
            color: #111827;
            font-size: 12px;
            font-weight: 700;
            margin-bottom: 8px;
            text-transform: uppercase;
        }
        .items {
            margin-top: 18px;
            table-layout: fixed;
        }
        .items th {
            background: #f3f4f6;
            color: #374151;
            font-size: 10px;
            text-align: left;
            text-transform: uppercase;
        }
        .items th, .items td {
            border-bottom: 1px solid #e5e7eb;
            padding: 7px;
            vertical-align: top;
            word-wrap: break-word;
        }
        .items tr { page-break-inside: avoid; }
        .totals-table {
            margin-left: auto;
            margin-top: 14px;
            width: 255px;
        }
        .totals-table td { padding: 4px 0; }
        .grand-total {
            border-top: 1px solid #9ca3af;
            color: #111827;
            font-size: 15px;
            font-weight: 700;
            padding-top: 7px;
        }
        .two-column {
            margin-top: 16px;
            width: 100%;
        }
        .payment-card {
            border: 1px solid #d1d5db;
            border-radius: 5px;
            margin-bottom: 8px;
            padding: 9px;
        }
        .payment-card h4 {
            color: #111827;
            font-size: 12px;
            margin-bottom: 3px;
        }
        .payment-table td {
            padding: 2px 0;
            vertical-align: top;
        }
        .payment-table .label {
            color: #6b7280;
            width: 82px;
        }
        .thank-you {
            border-top: 1px solid #e5e7eb;
            margin-top: 16px;
            padding-top: 12px;
        }
        .signature {
            max-height: 58px;
            max-width: 210px;
        }
        .footer {
            bottom: -8mm;
            color: #6b7280;
            font-size: 10px;
            left: 0;
            position: fixed;
            right: 0;
            text-align: center;
        }
    </style>
</head>
<body>
    @php
        $company = $company ?? app(\App\Support\CompanyProfile::class)->data();
        $logoDataUri = $logoDataUri ?? app(\App\Support\CompanyProfile::class)->logoDataUri();
        $paymentMethods = $paymentMethods ?? app(\App\Support\PaymentSettings::class)->methodsForInvoice($invoice);
        $thankYouMessage = $thankYouMessage ?? app(\App\Support\CompanyProfile::class)->thankYouMessage();
        $canEmbedImages = extension_loaded('gd');
        $pdfUser = $user ?? null;
        $signaturePath = $pdfUser?->signaturePath();
        $signatureDataUri = $signatureDataUri ?? app(\App\Support\UserSignature::class)->dataUri($signaturePath);
        $authorization = $authorization ?? app(\App\Support\InvoiceAuthorization::class)->data($invoice, $company, $pdfUser);
        $moveRoute = collect([$invoice->move_origin, $invoice->move_destination])->filter()->implode(' to ');
        $notes = trim((string) ($invoice->notes ?? ''));
        $companyName = trim((string) ($company['name'] ?? ''));
        $companyPhone = trim((string) ($company['phone'] ?? ''));
        $companyEmail = trim((string) ($company['email'] ?? ''));
        $companyAddressLines = collect([
            $company['address_line_1'] ?? null,
            $company['address_line_2'] ?? null,
        ])->map(fn ($line) => trim((string) $line))->filter();
    @endphp

    <div class="header">
        <table class="header-table">
            <tr>
                <td style="width: 50%; vertical-align: top;">
                    <h1 class="document-title">Invoice</h1>
                    <p class="muted">{{ $invoice->invoice_number }}</p>
                    <p class="muted">Issued {{ $invoice->invoice_date?->format('d M, Y') ?? 'date not recorded' }}</p>
                    <span class="status">{{ $invoice->statusLabel() }}</span>
                </td>
                <td class="text-right" style="width: 50%; vertical-align: top;">
                    @if($logoDataUri && $canEmbedImages)
                        <img alt="{{ $companyName ?: 'Company' }} logo" class="logo" src="{{ $logoDataUri }}">
                    @endif
                    @if($companyName !== '')
                        <div class="company-name">{{ $companyName }}</div>
                    @endif
                    @foreach($companyAddressLines as $companyAddressLine)
                        <p class="muted">{{ $companyAddressLine }}</p>
                    @endforeach
                    @if($companyPhone !== '')
                        <p class="muted">{{ $companyPhone }}</p>
                    @endif
                    @if($companyEmail !== '')
                        <p class="muted">{{ $companyEmail }}</p>
                    @endif
                </td>
            </tr>
        </table>
    </div>

    <table class="summary-table">
        <tr>
            <td class="box" style="width: 49%;">
                <div class="section-title">Bill To</div>
                <h3>{{ $invoice->customer_name }}</h3>
                <p>{{ $invoice->customer_email }}</p>
                <p>{{ $invoice->customer_phone ?: 'Phone not provided' }}</p>
            </td>
            <td style="width: 2%;"></td>
            <td class="box" style="width: 49%;">
                <div class="section-title">Move Details</div>
                <h3>{{ $moveRoute !== '' ? $moveRoute : 'Move route not recorded' }}</h3>
                <p>Move date: {{ $invoice->move_date?->format('d M, Y') ?? 'Not scheduled' }}</p>
                <p>Move size: {{ $invoice->move_size ?: 'Not recorded' }}</p>
                <p>Quote ref: {{ $invoice->quote_reference ?: 'Not linked' }}</p>
            </td>
        </tr>
    </table>

    <table class="items">
        <thead>
            <tr>
                <th style="width: 48%;">Line Item</th>
                <th style="width: 14%;">Quantity</th>
                <th style="width: 19%;">Unit Price</th>
                <th class="text-right" style="width: 19%;">Total</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($invoice->items as $item)
                <tr>
                    <td>{{ $item->description }}</td>
                    <td>{{ $item->quantity }}</td>
                    <td>KES {{ number_format((float) $item->unit_price, 2) }}</td>
                    <td class="text-right">KES {{ number_format((float) $item->total, 2) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="4">No line items have been added to this invoice yet.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <table class="totals-table">
        <tr>
            <td>Sub-total</td>
            <td class="text-right">KES {{ number_format((float) $invoice->subtotal, 2) }}</td>
        </tr>
        <tr>
            <td>Tax</td>
            <td class="text-right">KES {{ number_format((float) $invoice->tax, 2) }}</td>
        </tr>
        <tr>
            <td class="grand-total">Total</td>
            <td class="text-right grand-total">KES {{ number_format((float) $invoice->total_amount, 2) }}</td>
        </tr>
    </table>

    <table class="two-column">
        <tr>
            <td style="width: 52%; vertical-align: top;">
                <div class="section-title">Notes</div>
                <p class="muted">{{ $notes !== '' ? $notes : 'No additional notes recorded for this invoice.' }}</p>
                <p class="muted" style="margin-top: 8px;">Due: {{ $invoice->due_date?->format('d M, Y') ?? 'Not recorded' }}</p>
                <p class="muted">Payment method: {{ $invoice->paymentMethodLabel() }}</p>
            </td>
            <td style="width: 4%;"></td>
            <td style="width: 44%; vertical-align: top;">
                <div class="section-title">Authorization</div>
                <table class="auth-table">
                    <tr>
                        <td class="muted">Authorized By</td>
                        <td class="text-right">{{ $authorization['name'] }}</td>
                    </tr>
                    <tr>
                        <td class="muted">Job Title</td>
                        <td class="text-right">{{ $authorization['job_title'] ?: 'Authorized Signatory' }}</td>
                    </tr>
                    <tr>
                        <td class="muted">Date</td>
                        <td class="text-right">{{ $authorization['date_label'] }}</td>
                    </tr>
                </table>
                @if($signatureDataUri && $canEmbedImages)
                    <div style="margin-top: 10px;">
                        <p class="muted">Signature</p>
                        <img alt="Authorized signature" class="signature" src="{{ $signatureDataUri }}" style="border:none; outline:none; box-shadow:none; background:transparent; padding:0; max-height:60px; max-width:200px;">
                    </div>
                @else
                    <p style="color: #999; font-style: italic; font-size: 12px; margin-top: 10px;">
                        Signature not available
                    </p>
                @endif
            </td>
        </tr>
    </table>

    @if(! empty($paymentMethods))
        <div style="margin-top: 16px;">
            <div class="section-title">Payment Information</div>
            @foreach($paymentMethods as $method)
                <div class="payment-card">
                    <h4>{{ $method['title'] }}</h4>
                    @if(! empty($method['subtitle']))
                        <p class="muted" style="margin-bottom: 4px;">{{ $method['subtitle'] }}</p>
                    @endif
                    <table class="payment-table">
                        @foreach($method['rows'] as $row)
                            <tr>
                                <td class="label">{{ $row['label'] }}:</td>
                                <td>{{ $row['value'] }}</td>
                            </tr>
                        @endforeach
                    </table>
                </div>
            @endforeach
        </div>
    @endif

    <div class="thank-you">
        <div class="section-title">Thank You</div>
        <p>{{ $thankYouMessage }}</p>
    </div>

    <div class="footer">
        Page 1 of 1
    </div>
</body>
</html>
