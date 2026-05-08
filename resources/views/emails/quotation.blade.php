@php
    $company = $company ?? app(\App\Support\CompanyProfile::class)->data();
    $depositAmount = $quotation->depositAmount();
    $balanceDue = $quotation->balanceDue();
    $quoteDate = $quotation->quote_date?->format('d M Y') ?? now()->format('d M Y');
    $validUntil = $quotation->quote_valid_until?->format('d M Y') ?? 'To be confirmed';
    $total = number_format((float) $quotation->quote_amount, 2);
    $companyName = trim((string) ($company['name'] ?? '')) ?: 'Company';
    $companyEmail = trim((string) ($company['email'] ?? ''));
    $companyPhone = trim((string) ($company['phone'] ?? ''));
    $companyAddressLines = collect([
        $company['address_line_1'] ?? null,
        $company['address_line_2'] ?? null,
    ])->map(fn ($line) => trim((string) $line))->filter();
@endphp
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Quotation {{ $quote->reference() }}</title>
</head>
<body style="margin:0; padding:0; background:#f3f6f4; font-family:Arial, Helvetica, sans-serif; color:#17212b;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="width:100%; background:#f3f6f4; margin:0; padding:24px 0;">
        <tr>
            <td align="center" style="padding:0 12px;">
                <table role="presentation" width="640" cellspacing="0" cellpadding="0" style="width:100%; max-width:640px; background:#ffffff; border:1px solid #dde5de; border-radius:8px; overflow:hidden;">
                    <tr>
                        <td style="padding:28px 32px 18px 32px; background:#ffffff;">
                            @if(! empty($logoDataUri))
                                <img src="{{ $logoDataUri }}" alt="{{ $companyName }}" style="display:block; max-height:38px; max-width:180px; margin-bottom:18px;">
                            @endif
                            <h1 style="margin:0; font-size:24px; line-height:32px; color:#102033;">Your quotation is ready</h1>
                            <p style="margin:8px 0 0 0; font-size:14px; line-height:22px; color:#5d6b78;">Quotation {{ $quote->reference() }} from {{ $companyName }}</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:0 32px 24px 32px;">
                            <p style="margin:0 0 14px 0; font-size:15px; line-height:24px;">Dear {{ $quote->full_name }},</p>
                            <div style="font-size:15px; line-height:24px; color:#26333f; margin-bottom:22px;">
                                {!! nl2br(e($messageBody)) !!}
                            </div>

                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="width:100%; border-collapse:collapse; margin:0 0 24px 0; border:1px solid #d9e2dc; border-radius:6px;">
                                <tr>
                                    <td colspan="2" style="padding:14px 16px; background:#eef8f1; color:#123c20; font-size:14px; font-weight:700;">Quotation Summary</td>
                                </tr>
                                <tr>
                                    <td style="padding:11px 16px; border-top:1px solid #d9e2dc; color:#6b7680; font-size:13px;">Quote Number</td>
                                    <td align="right" style="padding:11px 16px; border-top:1px solid #d9e2dc; font-size:13px; font-weight:700;">{{ $quote->reference() }}</td>
                                </tr>
                                <tr>
                                    <td style="padding:11px 16px; border-top:1px solid #d9e2dc; color:#6b7680; font-size:13px;">Date</td>
                                    <td align="right" style="padding:11px 16px; border-top:1px solid #d9e2dc; font-size:13px;">{{ $quoteDate }}</td>
                                </tr>
                                <tr>
                                    <td style="padding:11px 16px; border-top:1px solid #d9e2dc; color:#6b7680; font-size:13px;">Valid Until</td>
                                    <td align="right" style="padding:11px 16px; border-top:1px solid #d9e2dc; font-size:13px;">{{ $validUntil }}</td>
                                </tr>
                                <tr>
                                    <td style="padding:12px 16px; border-top:1px solid #d9e2dc; color:#123c20; font-size:14px; font-weight:700;">Total</td>
                                    <td align="right" style="padding:12px 16px; border-top:1px solid #d9e2dc; color:#123c20; font-size:16px; font-weight:700;">KES {{ $total }}</td>
                                </tr>
                            </table>

                            <p style="margin:0 0 20px 0; text-align:center;">
                                <a href="{{ $viewUrl }}" style="display:inline-block; background:#22b956; color:#ffffff; text-decoration:none; font-size:15px; font-weight:700; padding:12px 20px; border-radius:6px;">View Quotation Details</a>
                            </p>

                            <div style="height:1px; line-height:1px; background:#e3e9e5; margin:24px 0;"></div>

                            <h2 style="margin:0 0 10px 0; font-size:16px; line-height:24px; color:#102033;">Payment Information</h2>
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="width:100%; border-collapse:collapse; margin-bottom:20px;">
                                <tr>
                                    <td style="padding:6px 0; font-size:14px; color:#6b7680;">Deposit Required</td>
                                    <td align="right" style="padding:6px 0; font-size:14px; color:#17212b;">KES {{ number_format($depositAmount, 2) }}</td>
                                </tr>
                                <tr>
                                    <td style="padding:6px 0; font-size:14px; color:#6b7680;">Balance Due</td>
                                    <td align="right" style="padding:6px 0; font-size:14px; color:#17212b;">KES {{ number_format($balanceDue, 2) }}</td>
                                </tr>
                                <tr>
                                    <td colspan="2" style="padding:8px 0 0 0; font-size:14px; line-height:22px; color:#5d6b78;">{{ $quotation->payment_terms ?: 'Payment terms are included in the attached PDF quotation.' }}</td>
                                </tr>
                            </table>

                            @if($attachPdf ?? true)
                                <p style="margin:0 0 16px 0; font-size:14px; line-height:22px; color:#5d6b78;">The official PDF quotation is attached for your records.</p>
                            @endif

                            <p style="margin:0; font-size:15px; line-height:24px;">Thank you for choosing {{ $companyName }}.</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:20px 32px; background:#f8faf9; border-top:1px solid #e3e9e5;">
                            <p style="margin:0 0 4px 0; font-size:13px; line-height:20px; color:#102033; font-weight:700;">{{ $companyName }}</p>
                            @if($companyAddressLines->isNotEmpty())
                                <p style="margin:0; font-size:12px; line-height:20px; color:#6b7680;">{{ $companyAddressLines->implode(' | ') }}</p>
                            @endif
                            @if($companyPhone !== '' || $companyEmail !== '')
                                <p style="margin:8px 0 0 0; font-size:12px; line-height:20px; color:#6b7680;">
                                    {{ $companyPhone }}
                                    @if($companyPhone !== '' && $companyEmail !== '')
                                        |
                                    @endif
                                    @if($companyEmail !== '')
                                        <a href="mailto:{{ $companyEmail }}" style="color:#1f7a3b; text-decoration:none;">{{ $companyEmail }}</a>
                                    @endif
                                </p>
                            @endif
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
    @if(! empty($trackingToken))
        <img src="{{ route('email.track.open', ['token' => $trackingToken]) }}" width="1" height="1" style="display:none" alt="">
    @endif
</body>
</html>
