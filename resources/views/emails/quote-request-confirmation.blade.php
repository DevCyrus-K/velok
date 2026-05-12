@php($companyName = trim((string) ($company['name'] ?? config('app.name'))) ?: config('app.name'))
<x-email-layout
    emailTitle="Quote Request Confirmation"
    emailHeading="We received your quote request"
    emailSubheading="{{ $quoteRequest->reference() }} from {{ $companyName }}"
    :company="$company"
>
    <p class="text-heading" style="margin:0 0 18px 0; font-family:Arial, Helvetica, sans-serif; font-size:17px; line-height:28px; color:#04223e;">
        Dear {{ $quoteRequest->full_name }},
    </p>
    <p class="text-body" style="margin:0 0 22px 0; font-family:Arial, Helvetica, sans-serif; font-size:16px; line-height:28px; color:#5c6b7a;">
        Thank you for submitting your moving quote request. Our team has received it and will review {{ $quoteRequest->reference() }} carefully.
    </p>
    <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" class="safe-note" bgcolor="#fff4e7" style="margin:0 0 22px 0;">
        <tr>
            <td style="padding:16px 18px; font-family:Arial, Helvetica, sans-serif; font-size:14px; line-height:24px; color:#5b4730;">
                <strong style="font-weight:700;">Move Details:</strong><br>
                <strong>From:</strong> {{ $quoteRequest->moving_from }}<br>
                <strong>To:</strong> {{ $quoteRequest->moving_to }}<br>
                <strong>Date:</strong> {{ $quoteRequest->move_date?->format('d M Y') ?? 'To be confirmed' }}
            </td>
        </tr>
    </table>
    <p class="text-body" style="margin:0 0 22px 0; font-family:Arial, Helvetica, sans-serif; font-size:16px; line-height:28px; color:#5c6b7a;">
        We will contact you within 24 hours with a detailed quotation and answer any questions you may have about your move.
    </p>
    <p class="text-body" style="margin:0; font-family:Arial, Helvetica, sans-serif; font-size:16px; line-height:28px; color:#5c6b7a;">
        Best regards,<br>
        <span class="text-heading" style="font-weight:700; color:#04223e;">{{ $companyName }}</span>
    </p>
</x-email-layout>

@if(! empty($trackingToken))<img src="{{ route('email.track.open', ['token' => $trackingToken]) }}" width="1" height="1" style="display:none" alt="">@endif
