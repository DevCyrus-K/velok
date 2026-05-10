<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            color: #333;
            background: #fff;
            padding: 20px;
        }
        .container {
            max-width: 900px;
            margin: 0 auto;
            background: white;
            padding: 40px;
        }
        .clearfix {
            overflow: auto;
            margin-bottom: 30px;
        }
        .float-end {
            float: right;
        }
        .float-start {
            float: left;
        }
        .logo-section {
            text-align: right;
            margin-bottom: 20px;
        }
        .brand-mark {
            display: inline-block;
            padding: 10px 14px;
            background: #df1119;
            color: #fff;
            font-size: 14px;
            line-height: 18px;
            font-weight: 700;
            letter-spacing: 0.5px;
            border-radius: 4px;
        }
        .header-title {
            float: left;
            margin-top: 10px;
        }
        .header-title h5 {
            font-size: 24px;
            margin-bottom: 5px;
            color: #333;
        }
        .header-title p {
            font-size: 13px;
            color: #666;
            margin: 3px 0;
        }
        .badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 3px;
            font-size: 12px;
            font-weight: 500;
        }
        .badge-info {
            background: #e7f1f8;
            color: #0c5394;
        }
        .badge-success {
            background: #e8f5e9;
            color: #2e7d32;
        }
        .badge-warning {
            background: #fff3e0;
            color: #e65100;
        }
        .badge-danger {
            background: #ffebee;
            color: #c62828;
        }
        
        .section-row {
            display: flex;
            gap: 30px;
            margin: 30px 0;
        }
        .section-col {
            flex: 1;
        }
        .section-col h6 {
            color: #999;
            font-size: 12px;
            font-weight: 500;
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .section-col .title {
            font-size: 16px;
            font-weight: 600;
            color: #333;
            margin-bottom: 8px;
        }
        .section-col address {
            font-style: normal;
            line-height: 1.6;
            font-size: 13px;
            color: #666;
        }
        .section-col abbr {
            border: none;
            cursor: default;
            text-decoration: none;
            font-weight: 600;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            font-size: 13px;
        }
        table thead {
            background: #f5f5f5;
        }
        table thead tr th {
            padding: 12px;
            text-align: left;
            font-weight: 600;
            color: #333;
            border: none;
        }
        table tbody tr td {
            padding: 12px;
            border-bottom: 1px solid #eee;
            color: #666;
        }
        table tbody tr:last-child td {
            border-bottom: none;
        }
        table .text-end {
            text-align: right;
        }
        
        .notes-section {
            display: flex;
            gap: 30px;
            margin: 30px 0;
        }
        .notes-col {
            flex: 1;
        }
        .notes-col h6 {
            color: #999;
            font-size: 12px;
            font-weight: 500;
            margin-bottom: 8px;
            text-transform: uppercase;
        }
        .notes-col small {
            color: #666;
            font-size: 13px;
            line-height: 1.6;
        }
        
        .summary-col {
            flex: 1;
            text-align: right;
        }
        .summary-col p {
            font-size: 13px;
            margin-bottom: 8px;
            display: flex;
            justify-content: space-between;
        }
        .summary-col .label {
            font-weight: 600;
            color: #333;
        }
        .summary-col .value {
            color: #666;
        }
        .summary-col h3 {
            font-size: 18px;
            margin-top: 15px;
            color: #333;
        }
        
        .print-actions {
            margin-top: 40px;
            text-align: right;
            border-top: 1px solid #eee;
            padding-top: 20px;
        }
        
        .footer {
            margin-top: 50px;
            padding-top: 20px;
            border-top: 1px solid #eee;
            text-align: center;
            font-size: 12px;
            color: #999;
        }
        
        @media print {
            body {
                padding: 0;
            }
            .container {
                padding: 30px;
            }
            .print-actions {
                display: none;
            }
        }
    </style>
</head>
<body>
    @php
        $authorization = $authorization ?? [
            'name' => $quotation->authorized_by ?: 'Pending',
            'job_title' => $quotation->authorized_role,
            'is_complete' => filled($quotation->authorized_role) && filled($quotation->signature),
            'date_label' => $quotation->authorizationDate()?->format('d M Y') ?? now()->format('d M Y'),
            'prompt' => 'Please complete your profile to display authorization details',
        ];
        $signatureDataUri = $signatureDataUri ?? null;
        $company = $company ?? app(\App\Support\CompanyProfile::class)->data();
        $logoDataUri = $logoDataUri ?? app(\App\Support\CompanyProfile::class)->logoDataUri();
        $canEmbedImages = extension_loaded('gd');
        $companyName = trim((string) ($quotation->company_name ?: ($company['name'] ?? '')));
        $companyPhone = trim((string) ($quotation->company_phone ?: ($company['phone'] ?? '')));
        $companyEmail = trim((string) ($quotation->company_email ?: ($company['email'] ?? '')));
        $companyTagline = trim((string) ($company['tagline'] ?? ''));
        $companyAddressLines = collect([
            $company['address_line_1'] ?? null,
            $company['address_line_2'] ?? null,
        ])->map(fn ($line) => trim((string) $line))->filter();
        $companyContactLine = collect([$companyPhone, $companyEmail])->filter()->implode(' | ');
    @endphp
    <div class="container">
        <div class="clearfix">
            <div class="logo-section float-end">
                @if($logoDataUri && $canEmbedImages)
                    <img alt="{{ $companyName ?: 'Company' }} logo" style="max-height: 42px; max-width: 180px;" src="{{ $logoDataUri }}">
                @elseif($companyName !== '')
                    <span class="brand-mark">{{ $companyName }}</span>
                @endif
            </div>
            <div class="header-title float-start">
                <h5>PROFESSIONAL QUOTATION</h5>
                <p>Quotation: {{ $quotation->quoteRequest->reference() }}</p>
                <p>{{ $quotation->quote_date?->format('d M, Y') ?? 'N/A' }}</p>
                <span class="badge {{ $quotation->status === 'sent' ? 'badge-success' : ($quotation->status === 'draft' ? 'badge-info' : 'badge-warning') }}">
                    {{ in_array($quotation->status, ['declined', 'rejected'], true) ? 'Rejected' : ucfirst($quotation->status) }}
                </span>
            </div>
        </div>

        <!-- Customer and Route Information -->
        <div class="section-row">
            <div class="section-col">
                <h6>Customer</h6>
                <div class="title">{{ $quotation->quoteRequest->full_name }}</div>
                <address>
                    {{ $quotation->quoteRequest->email }} • {{ $quotation->quoteRequest->phone }}<br />
                    <span style="color: #999;">{{ $quotation->quoteRequest->serviceTypeLabel() }}</span>
                </address>
            </div>
            <div class="section-col">
                <h6>Requested Route</h6>
                <div class="title">{{ $quotation->moving_from ?? $quotation->quoteRequest->moving_from }}</div>
                <address>
                    to {{ $quotation->moving_to ?? $quotation->quoteRequest->moving_to }}<br />
                    Scheduled move: {{ $quotation->move_date?->format('d M, Y') ?? $quotation->quoteRequest->move_date?->format('d M, Y') ?? 'Not specified' }}<br />
                    Move size: {{ $quotation->quoteRequest->move_size ?: 'Not specified' }}
                </address>
            </div>
        </div>

        <!-- Quotation Details Table -->
        <table>
            <thead>
                <tr>
                    <th>Details</th>
                    <th>Description</th>
                    <th class="text-end">Value</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><strong>Quote Amount (KES)</strong></td>
                    <td>Professional moving service quotation</td>
                    <td class="text-end"><strong>KES {{ number_format($quotation->quote_amount ?? 0, 2) }}</strong></td>
                </tr>
                <tr>
                    <td>Deposit Required</td>
                    <td>{{ ($quotation->deposit_percentage ?? 30) }}% of total amount</td>
                    <td class="text-end">KES {{ number_format($quotation->depositAmount(), 2) }}</td>
                </tr>
                <tr>
                    <td>Quote Valid Until</td>
                    <td>{{ $quotation->quote_valid_until?->format('d M, Y') ?? 'Not specified' }}</td>
                    <td class="text-end">{{ $quotation->validityDays() ?? 'N/A' }} days</td>
                </tr>
                <tr>
                    <td>Service Type</td>
                    <td>{{ $quotation->quoteRequest->serviceTypeLabel() }}</td>
                    <td class="text-end">{{ $quotation->quoteRequest->move_size }}</td>
                </tr>
            </tbody>
        </table>

        <!-- Services Included -->
        @if ($quotation->services_included && count($quotation->services_included) > 0)
            <h6 style="color: #999; font-size: 12px; font-weight: 500; margin-top: 30px; margin-bottom: 15px; text-transform: uppercase;">Services Included</h6>
            <table>
                <thead>
                    <tr>
                        <th>Service</th>
                        <th>Description</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($quotation->services_included as $service)
                        <tr>
                            <td><strong>{{ $service['name'] }}</strong></td>
                            <td>{{ $service['description'] ?? 'N/A' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif

        <!-- Payment Terms & Additional Notes -->
        <div class="notes-section">
            <div class="notes-col">
                @if ($quotation->payment_terms)
                    <h6>Payment Terms</h6>
                    <small>{{ $quotation->payment_terms }}</small>
                @endif
                
                @if ($quotation->additional_notes)
                    <div style="margin-top: 20px;">
                        <h6>Additional Notes</h6>
                        <small>{{ $quotation->additional_notes }}</small>
                    </div>
                @endif
                
                @if ($quotation->cancellation_notice_hours)
                    <div style="margin-top: 20px;">
                        <h6>Cancellation Policy</h6>
                        <small>{{ $quotation->cancellationPolicyText() }}</small>
                    </div>
                @endif
            </div>
            
            <div class="summary-col">
                <p>
                    <span class="label">Quote Reference:</span>
                    <span class="value">{{ $quotation->quoteRequest->reference() }}</span>
                </p>
                <p>
                    <span class="label">Authorized By:</span>
                    <span class="value">{{ $authorization['name'] }}</span>
                </p>
                <p>
                    <span class="label">Job Title:</span>
                    <span class="value">{{ $authorization['job_title'] ?: 'Pending' }}</span>
                </p>
                <p>
                    <span class="label">Approval Date:</span>
                    <span class="value">{{ $authorization['date_label'] }}</span>
                </p>
                @if ($authorization['is_complete'] ?? false)
                    @if ($signatureDataUri && $canEmbedImages)
                        <div style="margin-top: 10px;">
                            <span class="label">Signature:</span><br>
                            <img alt="Authorized signature" src="{{ $signatureDataUri }}" style="border:none; outline:none; box-shadow:none; background:transparent; padding:0; max-height:60px; max-width:200px;">
                        </div>
                    @endif
                @else
                    <div style="margin-top: 10px; color: #e65100; font-size: 12px;">{{ $authorization['prompt'] }}</div>
                @endif
                <h3 style="color: #007bff;">KES {{ number_format($quotation->quote_amount ?? 0, 2) }}</h3>
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            @if($companyName !== '')
                <strong>{{ $companyName }}</strong><br>
            @endif
            @if($companyTagline !== '')
                {{ $companyTagline }}<br>
            @endif
            @if($companyContactLine !== '')
                {{ $companyContactLine }}<br>
            @endif
            @foreach($companyAddressLines as $companyAddressLine)
                {{ $companyAddressLine }}<br>
            @endforeach
            <br>
            <em>This quotation is valid until {{ $quotation->quote_valid_until?->format('d M, Y') ?? 'the specified date' }}. All prices are in Kenyan Shillings (KES).</em>
        </div>
    </div>
</body>
</html>
