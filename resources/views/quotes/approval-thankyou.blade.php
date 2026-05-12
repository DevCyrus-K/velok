@php
    $company = $company ?? app(\App\Support\CompanyProfile::class)->data();
    $companyName = trim((string) ($company['name'] ?? config('app.name'))) ?: config('app.name');
    $companyEmail = trim((string) ($company['email'] ?? ''));
    $companyPhone = trim((string) ($company['phone'] ?? ''));
    $logoDataUri = app(\App\Support\CompanyProfile::class)->logoDataUri();
@endphp
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Quotation Approved - Thank You</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            background-color: #f5f7fb;
            font-family: Arial, Helvetica, sans-serif;
            color: #5c6b7a;
        }
        .email-wrapper {
            max-width: 640px;
            margin: 0 auto;
            padding: 24px 12px;
        }
        .email-content {
            background: white;
            border-radius: 6px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .hero-section {
            background: linear-gradient(135deg, #df1119 0%, #b60d15 100%);
            color: white;
            padding: 40px 32px;
            text-align: center;
        }
        .hero-section h1 {
            margin: 0 0 12px 0;
            font-size: 34px;
            line-height: 42px;
            font-weight: 700;
        }
        .hero-section p {
            margin: 0;
            font-size: 16px;
            line-height: 26px;
            opacity: 0.95;
        }
        .content-section {
            padding: 36px 32px;
            background: #f5f7fb;
        }
        .greeting {
            font-size: 17px;
            line-height: 28px;
            color: #1a3f4e;
            font-weight: 600;
            margin: 0 0 18px 0;
        }
        .section-text {
            font-size: 16px;
            line-height: 28px;
            color: #666666;
            margin: 0 0 22px 0;
        }
        .info-table {
            width: 100%;
            margin: 0 0 24px 0;
            border: 1px solid #e0e8e3;
            border-radius: 6px;
            overflow: hidden;
            border-collapse: collapse;
        }
        .info-table th {
            padding: 14px 16px;
            background: #e8f5ed;
            color: #1a3f4e;
            font-size: 14px;
            font-weight: 700;
            text-align: left;
            border: none;
        }
        .info-table td {
            padding: 11px 16px;
            border-top: 1px solid #e0e8e3;
            font-size: 13px;
            color: #666666;
        }
        .info-table td.label {
            color: #666666;
        }
        .info-table td.value {
            text-align: right;
            font-weight: 700;
            color: #1a3f4e;
        }
        .info-table tr:last-child td {
            padding: 12px 16px;
            font-size: 16px;
            background: #f0fff3;
        }
        .payment-section {
            margin: 24px 0;
        }
        .payment-section h3 {
            margin: 0 0 16px 0;
            font-size: 16px;
            color: #1a3f4e;
            font-weight: 700;
        }
        .payment-item {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            font-size: 14px;
            border-bottom: 1px solid #e0e8e3;
        }
        .payment-item.total {
            font-weight: 700;
            color: #1a3f4e;
            border: none;
            padding: 12px 0;
            margin-top: 4px;
        }
        .payment-item .label {
            color: #666666;
        }
        .payment-item .amount {
            color: #1a3f4e;
            text-align: right;
        }
        .action-button {
            display: inline-block;
            background: #22b956;
            color: white;
            padding: 12px 24px;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 700;
            font-size: 15px;
            text-align: center;
            margin: 24px 0;
        }
        .footer-section {
            padding: 20px 32px;
            background: #04223e;
            color: white;
            text-align: center;
            font-size: 13px;
            line-height: 22px;
        }
        .footer-info {
            margin: 16px 0 0 0;
            color: #91a2b3;
        }
        .footer-contact {
            margin-top: 12px;
        }
        .footer-contact a {
            color: #22b956;
            text-decoration: none;
        }
        @media (max-width: 640px) {
            .hero-section {
                padding: 32px 20px;
            }
            .content-section {
                padding: 20px;
            }
            .footer-section {
                padding: 16px 20px;
            }
        }
    </style>
</head>
<body>
    <div class="email-wrapper">
        <div class="email-content">
            <!-- Hero Section -->
            <div class="hero-section">
                <h1>Thank You for Your Approval</h1>
                <p>Your quotation has been approved successfully</p>
            </div>

            <!-- Content Section -->
            <div class="content-section">
                <p class="greeting">Hello,</p>
                
                <p class="section-text">
                    {{ $companyName }} has received your approval for your quotation. We're excited to proceed with your move!
                </p>

                <!-- Quotation Details Table -->
                <table class="info-table">
                    <thead>
                        <tr>
                            <th colspan="2">Quotation Details</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="label">Reference</td>
                            <td class="value">{{ $quotation->reference() }}</td>
                        </tr>
                        <tr>
                            <td class="label">Quote Date</td>
                            <td class="value">{{ $quotation->quote_date?->format('d M Y') ?? now()->format('d M Y') }}</td>
                        </tr>
                        <tr>
                            <td class="label">Valid Until</td>
                            <td class="value">{{ $quotation->quote_valid_until?->format('d M Y') ?? 'To be confirmed' }}</td>
                        </tr>
                        <tr>
                            <td class="label">Total Amount</td>
                            <td class="value">KES {{ number_format((float) $quotation->quote_amount, 2) }}</td>
                        </tr>
                    </tbody>
                </table>

                <!-- Payment Information -->
                <div class="payment-section">
                    <h3>Payment Required</h3>
                    <div class="payment-item">
                        <span class="label">Deposit Required</span>
                        <span class="amount">KES {{ number_format($quotation->depositAmount(), 2) }}</span>
                    </div>
                    <div class="payment-item">
                        <span class="label">Balance Due</span>
                        <span class="amount">KES {{ number_format($quotation->balanceDue(), 2) }}</span>
                    </div>
                    @if($paymentMethods->isNotEmpty())
                        <div class="payment-item" style="border: none; margin-top: 12px;">
                            <strong>Payment Methods:</strong>
                        </div>
                        @foreach($paymentMethods as $method)
                            <div class="payment-item" style="border: none; padding: 4px 0;">
                                <span>{{ $method->display }}</span>
                            </div>
                        @endforeach
                    @endif
                </div>

                <p class="section-text">
                    Your booking is confirmed once our team verifies the deposit payment. Payment terms and additional details are in your quotation PDF.
                </p>

                <p class="section-text">
                    If you have any questions or need assistance, please don't hesitate to contact us.
                </p>

                <p style="margin: 0; font-size: 16px; line-height: 28px; color: #1a3f4e;">
                    Best regards,<br>
                    <strong>{{ $companyName }} Team</strong>
                </p>
            </div>

            <!-- Footer -->
            <div class="footer-section">
                <div>All communication from {{ $companyName }} is handled with care and security.</div>
                <div class="footer-info">
                    &copy; {{ date('Y') }} {{ $companyName }}. All rights reserved.
                </div>
                @if($companyPhone || $companyEmail)
                    <div class="footer-contact">
                        @if($companyPhone)
                            {{ $companyPhone }}
                        @endif
                        @if($companyPhone && $companyEmail)
                            |
                        @endif
                        @if($companyEmail)
                            <a href="mailto:{{ $companyEmail }}">{{ $companyEmail }}</a>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </div>
</body>
</html>
