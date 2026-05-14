@php
    $company = $company ?? app(\App\Support\CompanyProfile::class)->data();
    $companyName = trim((string) ($company['name'] ?? config('app.name'))) ?: config('app.name');
@endphp
@extends('emails.layouts.customer-base', [
    'emailHeading' => 'Your quotation is ready',
    'emailSubheading' => 'Quotation ' . ($quote->reference() ?? '#' . ($quotation->id ?? 'N/A')) . ' from ' . $companyName,
    'customerName' => $quote->full_name ?? $quotation->customer_name ?? 'Valued Customer',
    'closingName' => $companyName,
    'company' => $company,
])
@section('content')
@php
    $depositAmount = $quotation->depositAmount();
    $balanceDue = $quotation->balanceDue();
    $quoteDate = $quotation->quote_date?->format('d M Y') ?? now()->format('d M Y');
    $validUntil = $quotation->quote_valid_until?->format('d M Y') ?? 'To be confirmed';
    $total = number_format((float) $quotation->quote_amount, 2);
@endphp

<p class="text-body" style="margin:0 0 22px 0; font-family:Arial, Helvetica, sans-serif; font-size:16px; line-height:28px; color:#666666; white-space:pre-line;">
    {{ $messageBody }}
</p>

<!-- Quotation Summary Table -->
<table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="margin:0 0 24px 0; border:1px solid #e0e8e3; border-radius:6px; overflow:hidden;">
    <tr>
        <td style="padding:14px 16px; background:#e8f5ed; color:#1a3f4e; font-family:Arial, Helvetica, sans-serif; font-size:14px; font-weight:700;" colspan="2">
            Quotation Summary
        </td>
    </tr>
    <tr>
        <td style="padding:11px 16px; border-top:1px solid #e0e8e3; color:#666666; font-family:Arial, Helvetica, sans-serif; font-size:13px;">Quote Number</td>
        <td align="right" style="padding:11px 16px; border-top:1px solid #e0e8e3; font-family:Arial, Helvetica, sans-serif; font-size:13px; font-weight:700;">{{ $quote->reference() }}</td>
    </tr>
    <tr>
        <td style="padding:11px 16px; border-top:1px solid #e0e8e3; color:#666666; font-family:Arial, Helvetica, sans-serif; font-size:13px;">Date</td>
        <td align="right" style="padding:11px 16px; border-top:1px solid #e0e8e3; font-family:Arial, Helvetica, sans-serif; font-size:13px;">{{ $quoteDate }}</td>
    </tr>
    <tr>
        <td style="padding:11px 16px; border-top:1px solid #e0e8e3; color:#666666; font-family:Arial, Helvetica, sans-serif; font-size:13px;">Valid Until</td>
        <td align="right" style="padding:11px 16px; border-top:1px solid #e0e8e3; font-family:Arial, Helvetica, sans-serif; font-size:13px;">{{ $validUntil }}</td>
    </tr>
    <tr>
        <td style="padding:12px 16px; border-top:1px solid #e0e8e3; color:#1a3f4e; font-family:Arial, Helvetica, sans-serif; font-size:14px; font-weight:700;">Total</td>
        <td align="right" style="padding:12px 16px; border-top:1px solid #e0e8e3; color:#1a3f4e; font-family:Arial, Helvetica, sans-serif; font-size:16px; font-weight:700;">KES {{ $total }}</td>
    </tr>
</table>

<!-- Action Buttons -->
<table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="margin:0 0 24px 0;">
    <tr>
        <td align="center" style="padding:0 0 12px 0;">
            <table role="presentation" border="0" cellpadding="0" cellspacing="0">
                <tr>
                    <td style="background:#22b956; border-radius:6px;">
                        <a href="{{ route('quote.customer.approve', ['token' => $quotation->approval_token]) }}" style="display:inline-block; background:#22b956; color:#ffffff; text-decoration:none; font-family:Arial, Helvetica, sans-serif; font-size:15px; font-weight:700; padding:12px 24px; border-radius:6px;">Approve Quotation</a>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
    @if(!empty($pdfUrl))
    <tr>
        <td align="center" style="padding:0;">
            <a href="{{ $pdfUrl }}" style="color:#22b956; font-family:Arial, Helvetica, sans-serif; font-size:13px; font-weight:700; text-decoration:none;">Download PDF quotation</a>
        </td>
    </tr>
    @endif
</table>

<!-- Payment Information -->
<h3 style="margin:24px 0 12px 0; font-family:Arial, Helvetica, sans-serif; font-size:16px; line-height:24px; color:#1a3f4e; font-weight:700;">Payment Information</h3>
<table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="margin-bottom:20px;">
    <tr>
        <td style="padding:6px 0; font-family:Arial, Helvetica, sans-serif; font-size:14px; color:#666666;">Deposit Required</td>
        <td align="right" style="padding:6px 0; font-family:Arial, Helvetica, sans-serif; font-size:14px; color:#000000; font-weight:700;">KES {{ number_format($depositAmount, 2) }}</td>
    </tr>
    <tr>
        <td style="padding:6px 0; font-family:Arial, Helvetica, sans-serif; font-size:14px; color:#666666;">Balance Due</td>
        <td align="right" style="padding:6px 0; font-family:Arial, Helvetica, sans-serif; font-size:14px; color:#000000; font-weight:700;">KES {{ number_format($balanceDue, 2) }}</td>
    </tr>
    <tr>
        <td colspan="2" style="padding:8px 0 0 0; font-family:Arial, Helvetica, sans-serif; font-size:14px; line-height:22px; color:#666666;">
            {{ $quotation->payment_terms ?: 'Payment terms are included in the attached PDF quotation.' }}
        </td>
    </tr>
</table>

@if($attachPdf ?? true)
<p class="text-body" style="margin:0 0 16px 0; font-family:Arial, Helvetica, sans-serif; font-size:14px; line-height:22px; color:#666666;">
    The official PDF quotation is attached for your records.
</p>
@endif

@endsection
