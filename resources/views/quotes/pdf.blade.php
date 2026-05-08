<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Quote {{ $quote->reference() }}</title>
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
        .header-table, .summary-table, .items, .totals-table, .auth-table {
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
            margin-bottom: 4px;
        }
        .status {
            background: #dcfce7;
            border-radius: 3px;
            color: #166534;
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
            width: 285px;
        }
        .totals-table td { padding: 4px 0; }
        .grand-total {
            border-top: 1px solid #9ca3af;
            color: #111827;
            font-size: 15px;
            font-weight: 700;
            padding-top: 7px;
        }
        .notes-table {
            border-collapse: collapse;
            margin-top: 16px;
            width: 100%;
        }
        .notes-table td {
            vertical-align: top;
        }
        .signature {
            max-height: 70px;
            max-width: 220px;
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
        $pdfUser = $user ?? null;
        $signaturePath = $pdfUser?->signaturePath();
        $signatureDataUri = $signatureDataUri ?? app(\App\Support\UserSignature::class)->dataUri($signaturePath);
        $authorization = $authorization ?? [
            'name' => $pdfUser?->name ?: ($quotation->authorized_by ?: 'Pending'),
            'job_title' => $pdfUser?->job_title ?: ($quotation->authorized_role ?: 'Authorized Signatory'),
            'is_complete' => filled($signaturePath),
            'date_label' => $quotation->authorizationDate()?->format('d M Y') ?? now()->format('d M Y'),
        ];
        $canEmbedImages = extension_loaded('gd');
        $serviceCount = max(1, count($quotation->services_included ?? []));
        $quoteAmount = round((float) ($quotation->quote_amount ?? 0), 2);
        $lineAmount = $serviceCount > 0 ? round($quoteAmount / $serviceCount, 2) : $quoteAmount;
        $companyName = trim((string) ($quotation->company_name ?: ($company['name'] ?? '')));
        $companyPhone = trim((string) ($quotation->company_phone ?: ($company['phone'] ?? '')));
        $companyEmail = trim((string) ($quotation->company_email ?: ($company['email'] ?? '')));
        $companyAddressLines = collect([
            $company['address_line_1'] ?? null,
            $company['address_line_2'] ?? null,
        ])->map(fn ($line) => trim((string) $line))->filter();
    @endphp

    <div class="header">
        <table class="header-table">
            <tr>
                <td style="width: 50%; vertical-align: top;">
                    <h1 class="document-title">Quote</h1>
                    <p class="muted">{{ $quote->reference() }}</p>
                    <p class="muted">Issued {{ $quotation->quote_date?->format('d M, Y') ?? 'date not recorded' }}</p>
                    <span class="status">{{ in_array($quotation->status, ['declined', 'rejected'], true) ? 'Rejected' : ucfirst($quotation->status) }}</span>
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
                <div class="section-title">Customer</div>
                <h3>{{ $quote->full_name }}</h3>
                <p>{{ $quote->email }}</p>
                <p>{{ $quote->phone }}</p>
                <p class="muted">{{ $quote->serviceTypeLabel() }}</p>
            </td>
            <td style="width: 2%;"></td>
            <td class="box" style="width: 49%;">
                <div class="section-title">Move Details</div>
                <h3>{{ $quotation->moving_from ?: $quote->moving_from }} to {{ $quotation->moving_to ?: $quote->moving_to }}</h3>
                <p>Move date: {{ $quotation->move_date?->format('d M, Y') ?? $quote->move_date?->format('d M, Y') ?? 'Not scheduled' }}</p>
                <p>Move size: {{ $quote->move_size ?: 'Not recorded' }}</p>
                <p>Valid until: {{ $quotation->quote_valid_until?->format('d M, Y') ?? 'Not specified' }}</p>
            </td>
        </tr>
    </table>

    <table class="items">
        <thead>
            <tr>
                <th style="width: 32%;">Service</th>
                <th style="width: 45%;">Description</th>
                <th class="text-right" style="width: 23%;">Amount</th>
            </tr>
        </thead>
        <tbody>
            @forelse($quotation->services_included as $index => $service)
                @php
                    $amount = $index === count($quotation->services_included) - 1
                        ? $quoteAmount - ($lineAmount * max(0, count($quotation->services_included) - 1))
                        : $lineAmount;
                @endphp
                <tr>
                    <td><strong>{{ $service['name'] ?? 'Service' }}</strong></td>
                    <td>{{ $service['description'] ?? 'Professional relocation service' }}</td>
                    <td class="text-right">KES {{ number_format((float) $amount, 2) }}</td>
                </tr>
            @empty
                <tr>
                    <td><strong>Professional moving service</strong></td>
                    <td>{{ $quote->serviceTypeLabel() }}</td>
                    <td class="text-right">KES {{ number_format($quoteAmount, 2) }}</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <table class="totals-table">
        <tr>
            <td>Quote Amount</td>
            <td class="text-right">KES {{ number_format($quoteAmount, 2) }}</td>
        </tr>
        <tr>
            <td>Deposit ({{ number_format((float) ($quotation->deposit_percentage ?? 0), 0) }}%)</td>
            <td class="text-right">KES {{ number_format($quotation->depositAmount(), 2) }}</td>
        </tr>
        <tr>
            <td>Balance Due</td>
            <td class="text-right">KES {{ number_format($quotation->balanceDue(), 2) }}</td>
        </tr>
        <tr>
            <td class="grand-total">Total</td>
            <td class="text-right grand-total">KES {{ number_format($quoteAmount, 2) }}</td>
        </tr>
    </table>

    <table class="notes-table">
        <tr>
            <td style="width: 55%; padding-right: 18px;">
                <div class="section-title">Payment Terms</div>
                <p class="muted">{{ $quotation->payment_terms ?: 'Payment terms not specified.' }}</p>

                <div class="section-title" style="margin-top: 14px;">Cancellation Policy</div>
                <p class="muted">{{ $quotation->cancellationPolicyText() }}</p>

                @if($quotation->additional_notes)
                    <div class="section-title" style="margin-top: 14px;">Additional Notes</div>
                    <p class="muted">{{ $quotation->additional_notes }}</p>
                @endif
            </td>
            <td style="width: 45%; padding: 10px; vertical-align: top;">
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
                        <td class="muted">Approval Date</td>
                        <td class="text-right">{{ $authorization['date_label'] }}</td>
                    </tr>
                </table>
                @if($signatureDataUri)
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

    <div class="footer">
        Page 1 of 1
    </div>
</body>
</html>
