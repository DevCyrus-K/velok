@php
    $company = $company ?? app(\App\Support\CompanyProfile::class)->data();
    $companyName = trim((string) ($company['name'] ?? config('app.name'))) ?: config('app.name');
@endphp
@extends('emails.layouts.customer-base', [
    'emailHeading' => 'Invoice ' . $invoice->invoice_number,
    'emailSubheading' => 'Payment Due: KES ' . number_format($invoice->total_amount, 2),
    'customerName' => $invoice->customer_name,
    'closingName' => $companyName,
    'company' => $company,
])

@section('content')
@php
    $company = $company ?? app(\App\Support\CompanyProfile::class)->data();
    $companyPhone = trim((string) ($company['phone'] ?? ''));
    $companyEmail = trim((string) ($company['email'] ?? ''));
@endphp

<p class="text-body" style="margin:0 0 22px 0; font-family:Arial, Helvetica, sans-serif; font-size:16px; line-height:28px; color:#666666; white-space:pre-line;">
    {{ $messageBody ?? 'Please find your invoice details below. Please review and process payment at your earliest convenience.' }}
</p>

<!-- Invoice Summary Table -->
<table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="margin:0 0 24px 0; border:1px solid #e0e8e3; border-radius:6px; overflow:hidden;">
    <tr>
        <td style="padding:14px 16px; background:#e8f5ed; color:#1a3f4e; font-family:Arial, Helvetica, sans-serif; font-size:14px; font-weight:700;" colspan="2">
            Invoice Details
        </td>
    </tr>
    <tr>
        <td style="padding:11px 16px; border-top:1px solid #e0e8e3; color:#666666; font-family:Arial, Helvetica, sans-serif; font-size:13px;">Invoice Number</td>
        <td align="right" style="padding:11px 16px; border-top:1px solid #e0e8e3; font-family:Arial, Helvetica, sans-serif; font-size:13px; font-weight:700;">{{ $invoice->invoice_number }}</td>
    </tr>
    <tr>
        <td style="padding:11px 16px; border-top:1px solid #e0e8e3; color:#666666; font-family:Arial, Helvetica, sans-serif; font-size:13px;">Invoice Date</td>
        <td align="right" style="padding:11px 16px; border-top:1px solid #e0e8e3; font-family:Arial, Helvetica, sans-serif; font-size:13px;">{{ $invoice->invoice_date?->format('d M Y') ?? 'N/A' }}</td>
    </tr>
    <tr>
        <td style="padding:11px 16px; border-top:1px solid #e0e8e3; color:#666666; font-family:Arial, Helvetica, sans-serif; font-size:13px;">Due Date</td>
        <td align="right" style="padding:11px 16px; border-top:1px solid #e0e8e3; font-family:Arial, Helvetica, sans-serif; font-size:13px;">{{ $invoice->due_date?->format('d M Y') ?? 'N/A' }}</td>
    </tr>
    <tr>
        <td style="padding:12px 16px; border-top:1px solid #e0e8e3; color:#1a3f4e; font-family:Arial, Helvetica, sans-serif; font-size:14px; font-weight:700;">Amount Due</td>
        <td align="right" style="padding:12px 16px; border-top:1px solid #e0e8e3; color:#1a3f4e; font-family:Arial, Helvetica, sans-serif; font-size:16px; font-weight:700;">KES {{ number_format($invoice->total_amount, 2) }}</td>
    </tr>
</table>

<!-- Action Buttons -->
<table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="margin:0 0 24px 0;">
    <tr>
        <td align="center" style="padding:0 0 12px 0;">
            <table role="presentation" border="0" cellpadding="0" cellspacing="0">
                <tr>
                    <td style="background:#22b956; border-radius:6px;">
                        <a href="{{ route('invoices.download', $invoice) }}" style="display:inline-block; background:#22b956; color:#ffffff; text-decoration:none; font-family:Arial, Helvetica, sans-serif; font-size:15px; font-weight:700; padding:12px 24px; border-radius:6px;">Download Invoice PDF</a>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>

<!-- Payment Information -->
<h3 style="margin:24px 0 12px 0; font-family:Arial, Helvetica, sans-serif; font-size:16px; line-height:24px; color:#1a3f4e; font-weight:700;">Payment Methods</h3>
@if($paymentMethods->isNotEmpty())
    @foreach($paymentMethods as $method)
    <p class="text-body" style="margin:0 0 12px 0; font-family:Arial, Helvetica, sans-serif; font-size:14px; line-height:24px; color:#666666;">
        <strong>{{ $method['label'] ?? 'Payment Method' }}:</strong><br>
        <span style="white-space:pre-line;">{{ $method['details'] ?? '' }}</span>
    </p>
    @endforeach
@else
    <p class="text-body" style="margin:0 0 12px 0; font-family:Arial, Helvetica, sans-serif; font-size:14px; line-height:24px; color:#666666;">
        Payment details will be provided separately. Please contact us if you have any questions.
    </p>
@endif

<p class="text-body" style="margin:0 0 0 0; font-family:Arial, Helvetica, sans-serif; font-size:14px; line-height:24px; color:#666666;">
    For questions regarding this invoice, please contact us at 
    @if($companyPhone)
        {{ $companyPhone }}
    @endif
    @if($companyPhone && $companyEmail)
        or 
    @endif
    @if($companyEmail)
        <a href="mailto:{{ $companyEmail }}" style="color:#22b956; text-decoration:none;">{{ $companyEmail }}</a>
    @endif
</p>

@endsection
