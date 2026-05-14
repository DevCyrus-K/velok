@php
    $companyName = trim((string) ($company['name'] ?? '')) ?: 'Company';
    $depositAmount = $quotation->depositAmount();
    $balanceDue = $quotation->balanceDue();
    $quoteDate = $quotation->quote_date?->format('d M Y') ?? now()->format('d M Y');
    $validUntil = $quotation->quote_valid_until?->format('d M Y') ?? 'To be confirmed';
    $total = number_format((float) $quotation->quote_amount, 2);
@endphp
<x-email-layout
    emailTitle="Quotation {{ $quote->reference() }}"
    emailHeading="Your quotation is ready"
    emailSubheading="Quotation {{ $quote->reference() }} from {{ $companyName }}"
    :company="$company"
>
    <p class="text-heading" style="margin:0 0 18px 0; font-family:Arial, Helvetica, sans-serif; font-size:17px; line-height:28px; color:#04223e;">
        Dear {{ $quote->full_name }},
    </p>
    <div style="font-size:16px; line-height:28px; color:#5c6b7a; margin-bottom:22px; font-family:Arial, Helvetica, sans-serif; white-space:pre-line;">
        {{ $messageBody }}
    </div>

    <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="width:100%; border-collapse:collapse; margin:0 0 24px 0; border:1px solid #e6edf5;">
        <tr>
            <td colspan="2" style="padding:14px 16px; background:#fff5f5; color:#04223e; font-size:14px; font-weight:700; font-family:Arial, Helvetica, sans-serif; border-top:2px solid #df1119;">Quotation Summary</td>
        </tr>
        <tr>
            <td style="padding:11px 16px; border-top:1px solid #e6edf5; color:#5c6b7a; font-size:14px; font-family:Arial, Helvetica, sans-serif;">Quote Number</td>
            <td align="right" style="padding:11px 16px; border-top:1px solid #e6edf5; font-size:14px; font-weight:700; font-family:Arial, Helvetica, sans-serif;">{{ $quote->reference() }}</td>
        </tr>
        <tr>
            <td style="padding:11px 16px; border-top:1px solid #e6edf5; color:#5c6b7a; font-size:14px; font-family:Arial, Helvetica, sans-serif;">Date</td>
            <td align="right" style="padding:11px 16px; border-top:1px solid #e6edf5; font-size:14px; font-family:Arial, Helvetica, sans-serif;">{{ $quoteDate }}</td>
        </tr>
        <tr>
            <td style="padding:11px 16px; border-top:1px solid #e6edf5; color:#5c6b7a; font-size:14px; font-family:Arial, Helvetica, sans-serif;">Valid Until</td>
            <td align="right" style="padding:11px 16px; border-top:1px solid #e6edf5; font-size:14px; font-family:Arial, Helvetica, sans-serif;">{{ $validUntil }}</td>
        </tr>
        <tr>
            <td style="padding:12px 16px; border-top:1px solid #e6edf5; color:#04223e; font-size:16px; font-weight:800; font-family:Arial, Helvetica, sans-serif; border-top:2px solid #df1119;">Total</td>
            <td align="right" style="padding:12px 16px; border-top:1px solid #e6edf5; color:#04223e; font-size:16px; font-weight:800; font-family:Arial, Helvetica, sans-serif; border-top:2px solid #df1119;">KES {{ $total }}</td>
        </tr>
    </table>

    <p style="margin:0 0 20px 0; text-align:center;">
        <a href="{{ $viewUrl }}" class="btn" style="display:inline-block; background:#16a34a; color:#ffffff; text-decoration:none; font-size:16px; font-weight:700; padding:12px 24px; border-radius:0; font-family:Arial, Helvetica, sans-serif;">Approve Quotation</a>
    </p>
    @if(! empty($pdfUrl))
        <p style="margin:0 0 20px 0; text-align:center;">
            <a href="{{ $pdfUrl }}" style="color:#df1119; font-size:14px; font-weight:700; text-decoration:none; font-family:Arial, Helvetica, sans-serif;">Download PDF quotation</a>
        </p>
    @endif

    <div style="height:1px; line-height:1px; background:#e6edf5; margin:24px 0;"></div>

    <h2 style="margin:0 0 10px 0; font-size:16px; line-height:24px; color:#04223e; font-family:Arial, Helvetica, sans-serif; font-weight:700;">Payment Information</h2>
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="width:100%; border-collapse:collapse; margin-bottom:20px;">
        <tr>
            <td style="padding:6px 0; font-size:14px; color:#5c6b7a; font-family:Arial, Helvetica, sans-serif;">Deposit Required</td>
            <td align="right" style="padding:6px 0; font-size:14px; color:#04223e; font-family:Arial, Helvetica, sans-serif;">KES {{ number_format($depositAmount, 2) }}</td>
        </tr>
        <tr>
            <td style="padding:6px 0; font-size:14px; color:#5c6b7a; font-family:Arial, Helvetica, sans-serif;">Balance Due</td>
            <td align="right" style="padding:6px 0; font-size:14px; color:#04223e; font-family:Arial, Helvetica, sans-serif;">KES {{ number_format($balanceDue, 2) }}</td>
        </tr>
        <tr>
            <td colspan="2" style="padding:8px 0 0 0; font-size:14px; line-height:22px; color:#5c6b7a; font-family:Arial, Helvetica, sans-serif;">{{ $quotation->payment_terms ?: 'Payment terms are included in the attached PDF quotation.' }}</td>
        </tr>
    </table>

    @if($attachPdf ?? true)
        <p style="margin:0 0 16px 0; font-size:14px; line-height:22px; color:#5c6b7a; font-family:Arial, Helvetica, sans-serif;">The official PDF quotation is attached for your records.</p>
    @endif

    <p class="text-body" style="margin:0; font-family:Arial, Helvetica, sans-serif; font-size:16px; line-height:28px; color:#5c6b7a;">
        Best regards,<br>
        <span class="text-heading" style="font-weight:700; color:#04223e;">{{ $companyName }}</span>
    </p>
</x-email-layout>

@if(! empty($trackingToken))<img src="{{ route('email.track.open', ['token' => $trackingToken]) }}" width="1" height="1" style="display:none" alt="">@endif
