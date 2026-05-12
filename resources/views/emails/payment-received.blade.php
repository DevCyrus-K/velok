@php($companyName = trim((string) ($company['name'] ?? config('app.name'))) ?: config('app.name'))
<x-email-layout
    emailTitle="Payment Received"
    emailHeading="Payment received"
    emailSubheading="Invoice {{ $invoice->invoice_number }} has been paid"
    :company="$company"
>
    <p class="text-heading" style="margin:0 0 18px 0; font-family:Arial, Helvetica, sans-serif; font-size:17px; line-height:28px; color:#04223e;">
        Dear {{ $invoice->customer_name }},
    </p>
    <p class="text-body" style="margin:0 0 22px 0; font-family:Arial, Helvetica, sans-serif; font-size:16px; line-height:28px; color:#5c6b7a;">
        We have received your payment for invoice {{ $invoice->invoice_number }}. Thank you!
    </p>
    <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" class="safe-note" bgcolor="#fff4e7" style="margin:0 0 22px 0;">
        <tr>
            <td style="padding:16px 18px; font-family:Arial, Helvetica, sans-serif; font-size:14px; line-height:24px; color:#5b4730;">
                <strong style="font-weight:700;">Payment Details:</strong><br>
                <strong>Invoice Number:</strong> {{ $invoice->invoice_number }}<br>
                <strong>Amount Paid:</strong> KES {{ number_format((float) $invoice->total_amount, 2) }}<br>
                <strong>Date Received:</strong> {{ $invoice->paid_at?->format('d M Y H:i') ?? now()->format('d M Y H:i') }}
            </td>
        </tr>
    </table>
    <p class="text-body" style="margin:0; font-family:Arial, Helvetica, sans-serif; font-size:16px; line-height:28px; color:#5c6b7a;">
        Best regards,<br>
        <span class="text-heading" style="font-weight:700; color:#04223e;">{{ $companyName }}</span>
    </p>
</x-email-layout>

@if(! empty($trackingToken))<img src="{{ route('email.track.open', ['token' => $trackingToken]) }}" width="1" height="1" style="display:none" alt="">@endif
