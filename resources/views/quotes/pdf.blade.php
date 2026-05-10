@php
    $company = $company ?? app(\App\Support\CompanyProfile::class)->data();
    $logoDataUri = $logoDataUri ?? app(\App\Support\CompanyProfile::class)->logoDataUri();
    $canEmbedImages = extension_loaded('gd');
    $paymentMethods = collect($paymentMethods ?? app(\App\Support\BookingFlow::class)->paymentMethodDisplays());
    $thankYouMessage = $thankYouMessage ?? app(\App\Support\CompanyProfile::class)->thankYouMessage();
    $companyName = trim((string) ($quotation->company_name ?: ($company['name'] ?? config('app.name'))));
    $companyPhone = trim((string) ($quotation->company_phone ?: ($company['phone'] ?? '')));
    $companyEmail = trim((string) ($quotation->company_email ?: ($company['email'] ?? '')));
    $companyAddress = collect([$company['address_line_1'] ?? null, $company['address_line_2'] ?? null])->map(fn ($line) => trim((string) $line))->filter()->implode(', ');
    $quoteNumber = $quote->reference();
    $quoteDate = $quotation->quote_date?->format('d M Y') ?? now()->format('d M Y');
    $validUntil = $quotation->quote_valid_until?->format('d M Y') ?? 'To be confirmed';
    $moveDate = $quotation->move_date ?: $quote->move_date;
    $pickup = $quotation->moving_from ?: $quote->moving_from;
    $dropoff = $quotation->moving_to ?: $quote->moving_to;
    $lineItems = collect($quotation->services_included ?: [['name' => 'Professional moving service', 'description' => $quote->serviceTypeLabel()]]);
    $lineCount = max(1, $lineItems->count());
    $total = round((float) ($quotation->quote_amount ?? 0), 2);
    $baseAmount = $lineCount > 0 ? round($total / $lineCount, 2) : $total;
    $depositAmount = $quotation->depositAmount();
    $balance = $quotation->balanceDue();
    $approvalUrl = $approvalUrl ?? ($quotation->approval_token ? route('quote.customer.approve', ['token' => $quotation->approval_token]) : null);
    $pdfUrl = $pdfUrl ?? ($quotation->pdf_token ? route('quote.pdf.download', ['id' => $quotation->id, 'token' => $quotation->pdf_token]) : null);
    $paymentTerms = $quotation->payment_terms ?: 'Deposit is required to confirm booking. Remaining balance is due on move day.';
    $cancellationPolicy = $quotation->cancellationPolicyText();
    $authorization = $authorization ?? [];
    $signatureDataUri = $signatureDataUri ?? null;
    $authorizedName = $authorization['name'] ?? $user?->name ?? $quotation->authorized_by ?? 'Authorized Signatory';
    $authorizedTitle = $authorization['job_title'] ?? $user?->job_title ?? $quotation->authorized_role ?? 'Authorized Signatory';
@endphp
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Quotation {{ $quoteNumber }}</title>
</head>
<body style="font-family:DejaVu Sans,Arial,sans-serif;color:#1f2937;font-size:11px;line-height:1.45;margin:0">
    <table style="width:100%;border-collapse:collapse;margin-bottom:16px">
        <tr>
            <td style="width:55%;vertical-align:top">
                @if($logoDataUri && $canEmbedImages)
                    <img src="{{ $logoDataUri }}" alt="{{ $companyName }}" style="max-height:46px;max-width:180px;margin-bottom:8px">
                @endif
                <h2 style="font-size:16px;margin:0 0 4px;color:#111827">{{ $companyName }}</h2>
                <p style="margin:0;color:#6b7280">{{ $companyAddress }}</p>
                <p style="margin:2px 0 0;color:#6b7280">{{ $companyPhone }} {{ $companyPhone && $companyEmail ? '|' : '' }} {{ $companyEmail }}</p>
            </td>
            <td style="width:45%;text-align:right;vertical-align:top">
                <h1 style="font-size:28px;margin:0 0 6px;color:#111827">QUOTATION</h1>
                <p style="margin:0">Quote Number: <strong>{{ $quoteNumber }}</strong></p>
                <p style="margin:2px 0">Date: <strong>{{ $quoteDate }}</strong></p>
                <p style="margin:2px 0">Valid Until: <strong>{{ $validUntil }}</strong></p>
            </td>
        </tr>
    </table>

    <table style="width:100%;border-collapse:collapse;margin:0 0 14px">
        <tr>
            <td style="width:50%;border:1px solid #e5e7eb;padding:10px;vertical-align:top">
                <h3 style="font-size:12px;margin:0 0 6px;color:#111827">CUSTOMER DETAILS</h3>
                <p style="margin:0"><strong>{{ $quote->full_name }}</strong></p>
                <p style="margin:2px 0">{{ $quote->email }}</p>
                <p style="margin:2px 0">{{ $quote->phone }}</p>
                <p style="margin:8px 0 0">Service: {{ $quote->serviceTypeLabel() }}</p>
                <p style="margin:2px 0">Move Date: {{ $moveDate?->format('d M Y') ?? 'To be confirmed' }}</p>
            </td>
            <td style="width:50%;border:1px solid #e5e7eb;padding:10px;vertical-align:top">
                <h3 style="font-size:12px;margin:0 0 6px;color:#111827">MOVE DETAILS</h3>
                <p style="margin:0">Pickup: <strong>{{ $pickup ?: 'Not specified' }}</strong></p>
                <p style="margin:2px 0">Drop-off: <strong>{{ $dropoff ?: 'Not specified' }}</strong></p>
                <p style="margin:8px 0 0">Item Details: {{ $quote->move_size ?: 'Not specified' }}</p>
                <p style="margin:2px 0">Notes: {{ $quote->additional_notes ?: 'No special notes.' }}</p>
            </td>
        </tr>
    </table>

    <table style="width:100%;border-collapse:collapse;font-size:11px">
        <thead>
            <tr style="background:#f3f4f6">
                <th style="padding:8px;text-align:left;border:1px solid #e5e7eb">#</th>
                <th style="padding:8px;text-align:left;border:1px solid #e5e7eb">Description</th>
                <th style="padding:8px;text-align:right;border:1px solid #e5e7eb">Qty</th>
                <th style="padding:8px;text-align:right;border:1px solid #e5e7eb">Unit Price</th>
                <th style="padding:8px;text-align:right;border:1px solid #e5e7eb">Amount</th>
            </tr>
        </thead>
        <tbody>
            @foreach($lineItems as $item)
                @php
                    $amount = $loop->last ? $total - ($baseAmount * max(0, $lineCount - 1)) : $baseAmount;
                    $description = trim(collect([$item['name'] ?? 'Service', $item['description'] ?? null])->filter()->implode(' - '));
                @endphp
                <tr>
                    <td style="padding:8px;border:1px solid #e5e7eb">{{ $loop->iteration }}</td>
                    <td style="padding:8px;border:1px solid #e5e7eb">{{ $description }}</td>
                    <td style="padding:8px;text-align:right;border:1px solid #e5e7eb">1</td>
                    <td style="padding:8px;text-align:right;border:1px solid #e5e7eb">KES {{ number_format($amount, 2) }}</td>
                    <td style="padding:8px;text-align:right;border:1px solid #e5e7eb">KES {{ number_format($amount, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="4" style="padding:8px;text-align:right;font-weight:bold">Subtotal</td>
                <td style="padding:8px;text-align:right">KES {{ number_format($total, 2) }}</td>
            </tr>
            <tr style="background:#f3f4f6">
                <td colspan="4" style="padding:8px;text-align:right;font-weight:bold;font-size:13px">TOTAL</td>
                <td style="padding:8px;text-align:right;font-weight:bold;font-size:13px">KES {{ number_format($total, 2) }}</td>
            </tr>
            <tr>
                <td colspan="4" style="padding:8px;text-align:right">Deposit ({{ number_format((float) $quotation->deposit_percentage, 0) }}%)</td>
                <td style="padding:8px;text-align:right;color:#16a34a;font-weight:bold">KES {{ number_format($depositAmount, 2) }}</td>
            </tr>
            <tr>
                <td colspan="4" style="padding:8px;text-align:right;font-weight:bold">Balance Due on Move Day</td>
                <td style="padding:8px;text-align:right;font-weight:bold">KES {{ number_format($balance, 2) }}</td>
            </tr>
        </tfoot>
    </table>

    <div style="border:2px solid #f59e0b;border-radius:8px;padding:16px;margin:16px 0;background:#fffbeb">
        <h3 style="color:#d97706;margin:0 0 8px 0;font-size:13px">DEPOSIT REQUIRED TO CONFIRM BOOKING</h3>
        <p style="font-size:20px;font-weight:bold;color:#d97706;margin:0 0 8px 0">KES {{ number_format($depositAmount, 2) }}</p>
        <p style="font-size:11px;margin:0 0 4px 0">Payment Methods:</p>
        @forelse($paymentMethods as $method)
            <p style="font-size:11px;margin:0 0 2px 0">{{ $method->display }}</p>
        @empty
            <p style="font-size:11px;margin:0 0 2px 0">Payment details will be shared by our team.</p>
        @endforelse
        <p style="font-size:10px;color:#d97706;margin:8px 0 0 0">Your booking is NOT confirmed until deposit has been received and verified.</p>
    </div>

    @if($approvalUrl)
        <div style="border:2px solid #16a34a;border-radius:8px;padding:16px;margin:16px 0;background:#f0fdf4">
            <h3 style="color:#16a34a;margin:0 0 8px 0;font-size:13px">APPROVE THIS QUOTATION</h3>
            <p style="font-size:11px;margin:0 0 8px 0">Scan the QR code or visit the link below to approve this quotation:</p>
            <table style="width:100%">
                <tr>
                    <td style="width:100px;vertical-align:top">{!! QrCode::format('svg')->size(90)->generate($approvalUrl) !!}</td>
                    <td style="vertical-align:top;padding-left:12px">
                        <p style="font-size:10px;word-break:break-all;margin:0 0 4px 0"><a href="{{ $approvalUrl }}" style="color:#16a34a">{{ $approvalUrl }}</a></p>
                        <p style="font-size:10px;color:#6b7280;margin:0">Link expires: {{ $quotation->approval_token_expires_at?->format('d M Y') ?? '7 days after sending' }}</p>
                        <p style="font-size:10px;color:#6b7280;margin:4px 0 0 0">By approving you agree to all terms stated in this quotation.</p>
                    </td>
                </tr>
            </table>
        </div>
    @endif

    <div style="margin:16px 0">
        <h3 style="font-size:12px;border-bottom:1px solid #e5e7eb;padding-bottom:4px">TERMS & AGREEMENT</h3>
        <ol style="font-size:10px;line-height:1.6;color:#374151">
            <li><strong>SERVICES:</strong> {{ $companyName }} agrees to provide moving services as described on {{ $moveDate?->format('d M Y') ?? 'the agreed date' }} from {{ $pickup }} to {{ $dropoff }}.</li>
            <li><strong>PAYMENT TERMS:</strong> {{ $paymentTerms }}</li>
            <li><strong>CANCELLATION POLICY:</strong> {{ $cancellationPolicy }}</li>
            <li><strong>LIABILITY:</strong> {{ $companyName }} will take reasonable care of all items. Customers are advised to insure high-value items separately. Liability is limited to direct damages caused by proven negligence.</li>
            <li><strong>ACCEPTANCE:</strong> By approving or paying the deposit, the customer agrees to all terms stated above.</li>
        </ol>
    </div>

    <table style="width:100%;margin-top:24px">
        <tr>
            <td style="width:50%;vertical-align:bottom">
                <p style="font-size:10px;color:#6b7280;margin:0 0 4px 0">Authorized By:</p>
                @if($signatureDataUri && $canEmbedImages)
                    <img src="{{ $signatureDataUri }}" style="max-height:50px;max-width:160px;border:none;outline:none;box-shadow:none;background:transparent">
                @endif
                <p style="font-size:11px;font-weight:bold;margin:4px 0 0 0">{{ $authorizedName }}</p>
                <p style="font-size:10px;color:#6b7280;margin:2px 0 0 0">{{ $authorizedTitle ?: 'Authorized Signatory' }}</p>
            </td>
            <td style="width:50%;text-align:right;vertical-align:bottom">
                <p style="font-size:10px;color:#6b7280;margin:0 0 4px 0">Date:</p>
                <p style="font-size:11px;font-weight:bold;margin:0">{{ now()->format('d M Y') }}</p>
            </td>
        </tr>
    </table>

    <div style="margin-top:24px;padding-top:12px;border-top:1px solid #e5e7eb;text-align:center">
        <p style="font-size:11px;color:#6b7280;margin:0">{{ $thankYouMessage }}</p>
        <p style="font-size:10px;color:#9ca3af;margin:4px 0 0 0">{{ $companyName }} · {{ $companyPhone }} · {{ $companyEmail }}</p>
    </div>
</body>
</html>
