@php($companyName = trim((string) ($company['name'] ?? config('app.name'))) ?: config('app.name'))
<x-email-layout
    emailTitle="Deposit Received"
    emailHeading="Booking confirmed"
    emailSubheading="Deposit received for {{ $quotation->reference }}"
    :company="$company"
>
    <p class="text-heading" style="margin:0 0 18px 0; font-family:Arial, Helvetica, sans-serif; font-size:17px; line-height:28px; color:#04223e;">
        Dear {{ $quotation->customer_name }},
    </p>
    <p class="text-body" style="margin:0 0 22px 0; font-family:Arial, Helvetica, sans-serif; font-size:16px; line-height:28px; color:#5c6b7a;">
        Thank you! We have received your deposit for {{ $quotation->reference }}. Your booking is now confirmed.
    </p>
    <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" class="safe-note" bgcolor="#fff4e7" style="margin:0 0 22px 0;">
        <tr>
            <td style="padding:16px 18px; font-family:Arial, Helvetica, sans-serif; font-size:14px; line-height:24px; color:#5b4730;">
                <strong style="font-weight:700;">Booking Details:</strong><br>
                <strong>Amount Received:</strong> KES {{ number_format($quotation->depositAmount(), 2) }}<br>
                <strong>Reference:</strong> {{ $quotation->deposit_reference }}<br>
                <strong>Move Date:</strong> {{ $quotation->move_date?->format('d M Y') ?? 'To be confirmed' }}<br>
                <strong>Balance Due on Move Day:</strong> KES {{ number_format($quotation->balanceDue(), 2) }}
            </td>
        </tr>
    </table>
    <p class="text-body" style="margin:0 0 22px 0; font-family:Arial, Helvetica, sans-serif; font-size:16px; line-height:28px; color:#5c6b7a;">
        Our team will reach out to you shortly with further details about your upcoming move and any additional information we need.
    </p>
    <p class="text-body" style="margin:0; font-family:Arial, Helvetica, sans-serif; font-size:16px; line-height:28px; color:#5c6b7a;">
        Best regards,<br>
        <span class="text-heading" style="font-weight:700; color:#04223e;">{{ $companyName }}</span>
    </p>
</x-email-layout>

@if(! empty($trackingToken))<img src="{{ route('email.track.open', ['token' => $trackingToken]) }}" width="1" height="1" style="display:none" alt="">@endif
