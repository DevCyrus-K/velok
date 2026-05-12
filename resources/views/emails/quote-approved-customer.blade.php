@php($companyName = trim((string) ($company['name'] ?? config('app.name'))) ?: config('app.name'))
<x-email-layout
    emailTitle="Quotation Approved"
    emailHeading="Quotation approved"
    emailSubheading="Thank you for approving {{ $quotation->reference }}"
    :company="$company"
>
    <p class="text-heading" style="margin:0 0 18px 0; font-family:Arial, Helvetica, sans-serif; font-size:17px; line-height:28px; color:#04223e;">
        Dear {{ $quotation->customer_name }},
    </p>
    <p class="text-body" style="margin:0 0 22px 0; font-family:Arial, Helvetica, sans-serif; font-size:16px; line-height:28px; color:#5c6b7a;">
        Thank you for approving quotation {{ $quotation->reference }}. We're excited to assist with your move!
    </p>
    <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" class="safe-note" bgcolor="#fff4e7" style="margin:0 0 22px 0;">
        <tr>
            <td style="padding:16px 18px; font-family:Arial, Helvetica, sans-serif; font-size:14px; line-height:24px; color:#5b4730;">
                <strong style="font-weight:700;">Next Steps:</strong><br>
                <strong style="font-weight:700;">Deposit Required ({{ number_format((float) ($quotation->deposit_percentage ?? 0), 2) }}%):</strong> KES {{ number_format($quotation->depositAmount(), 2) }}<br>
                <strong style="font-weight:700;">Balance Due on Move Day:</strong> KES {{ number_format($quotation->balanceDue(), 2) }}
            </td>
        </tr>
    </table>
    <p class="text-body" style="margin:0 0 22px 0; font-family:Arial, Helvetica, sans-serif; font-size:16px; line-height:28px; color:#5c6b7a;">
        <strong style="font-weight:700;">Payment Methods:</strong>
    </p>
    @foreach($paymentMethods as $method)
        <p class="text-body" style="margin:0 0 8px 0; font-family:Arial, Helvetica, sans-serif; font-size:16px; line-height:28px; color:#5c6b7a;">
            {{ $method->display }}
        </p>
    @endforeach
    <p class="text-body" style="margin:0 0 22px 0; font-family:Arial, Helvetica, sans-serif; font-size:16px; line-height:28px; color:#5c6b7a;">
        Your booking is confirmed once the deposit has been received and verified by {{ $companyName }}. We will send you a confirmation with all the details once payment is processed.
    </p>
    <p class="text-body" style="margin:0; font-family:Arial, Helvetica, sans-serif; font-size:16px; line-height:28px; color:#5c6b7a;">
        Best regards,<br>
        <span class="text-heading" style="font-weight:700; color:#04223e;">{{ $companyName }}</span>
    </p>
</x-email-layout>

@if(! empty($trackingToken))<img src="{{ route('email.track.open', ['token' => $trackingToken]) }}" width="1" height="1" style="display:none" alt="">@endif
