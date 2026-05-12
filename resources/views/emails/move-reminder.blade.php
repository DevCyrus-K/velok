@php($companyName = trim((string) ($company['name'] ?? config('app.name'))) ?: config('app.name'))
<x-email-layout
    emailTitle="Move Reminder"
    emailHeading="Your move is tomorrow"
    emailSubheading="Reminder for {{ $quotation->reference }}"
    :company="$company"
>
    <p class="text-heading" style="margin:0 0 18px 0; font-family:Arial, Helvetica, sans-serif; font-size:17px; line-height:28px; color:#04223e;">
        Dear {{ $quotation->customer_name }},
    </p>
    <p class="text-body" style="margin:0 0 22px 0; font-family:Arial, Helvetica, sans-serif; font-size:16px; line-height:28px; color:#5c6b7a;">
        This is a friendly reminder about your move scheduled for {{ $quotation->move_date?->format('d M Y') }}.
    </p>
    <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" class="safe-note" bgcolor="#fff4e7" style="margin:0 0 22px 0;">
        <tr>
            <td style="padding:16px 18px; font-family:Arial, Helvetica, sans-serif; font-size:14px; line-height:24px; color:#5b4730;">
                <strong style="font-weight:700;">Move Details:</strong><br>
                <strong>Pickup Location:</strong> {{ $quotation->pickup_location }} at 8:00 AM<br>
                <strong>Drop-off Location:</strong> {{ $quotation->dropoff_location }}<br>
                <strong>Balance Due:</strong> KES {{ number_format($quotation->balanceDue(), 2) }}
            </td>
        </tr>
    </table>
    <p class="text-body" style="margin:0 0 22px 0; font-family:Arial, Helvetica, sans-serif; font-size:16px; line-height:28px; color:#5c6b7a;">
        Please ensure all items are packed and ready for pickup. Our team will arrive on time, and we look forward to making your move smooth and hassle-free.
    </p>
    <p class="text-body" style="margin:0; font-family:Arial, Helvetica, sans-serif; font-size:16px; line-height:28px; color:#5c6b7a;">
        Best regards,<br>
        <span class="text-heading" style="font-weight:700; color:#04223e;">{{ $companyName }}</span>
    </p>
</x-email-layout>

@if(! empty($trackingToken))<img src="{{ route('email.track.open', ['token' => $trackingToken]) }}" width="1" height="1" style="display:none" alt="">@endif
