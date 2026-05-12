@php
    $clientName = trim((string) ($quote->full_name ?? $quotation->customer_name ?? 'Client')) ?: 'Client';
    $companyName = trim((string) ($company['name'] ?? config('app.name'))) ?: config('app.name');
    $companyPhone = trim((string) ($company['phone'] ?? ''));
    $companyEmail = trim((string) ($company['email'] ?? ''));
    $companyWebsite = trim((string) ($company['website'] ?? ''));
    $representativeName = trim((string) ($company['authorized_representative_name'] ?? $companyName)) ?: $companyName;
@endphp
<x-email-layout
    emailTitle="Service Agreement {{ $quote->reference() }}"
    emailHeading="Your Service Agreement"
    emailSubheading="Quote Ref: {{ $quote->reference() }}"
    :company="$company"
    closingName="{{ $representativeName }}"
>
    <p class="text-heading" style="margin:0 0 18px 0; font-family:Arial, Helvetica, sans-serif; font-size:17px; line-height:28px; color:#04223e;">
        Dear {{ $clientName }},
    </p>
    <p style="margin:0 0 16px 0; font-size:16px; line-height:28px; color:#5c6b7a; font-family:Arial, Helvetica, sans-serif;">
        Thank you for approving your quote with {{ $companyName }}.
    </p>
    <p style="margin:0 0 16px 0; font-size:16px; line-height:28px; color:#5c6b7a; font-family:Arial, Helvetica, sans-serif;">
        Please find attached your Service Agreement for your upcoming move.
    </p>
    <p style="margin:0 0 18px 0; font-size:16px; line-height:28px; color:#5c6b7a; font-family:Arial, Helvetica, sans-serif;">
        Kindly review the document carefully. Fields marked with a blank line (___________) will be completed and confirmed by our team before your move date. If you have any questions or corrections, please contact us at {{ $companyPhone }} or {{ $companyEmail }}.
    </p>
    <p style="margin:0 0 22px 0; font-size:16px; line-height:28px; color:#5c6b7a; font-family:Arial, Helvetica, sans-serif;">
        We look forward to serving you.
    </p>
    <p class="text-body" style="margin:0; font-family:Arial, Helvetica, sans-serif; font-size:16px; line-height:28px; color:#5c6b7a;">
        Warm regards,<br>
        <span class="text-heading" style="font-weight:700; color:#04223e;">{{ $representativeName }}</span><br>
        {{ $companyName }}<br>
        {{ $companyPhone }}<br>
        <a href="mailto:{{ $companyEmail }}" style="color:#22b956; text-decoration:none;">{{ $companyEmail }}</a>
        @if($companyWebsite !== '')
            <br><a href="{{ $companyWebsite }}" style="color:#22b956; text-decoration:none;">{{ $companyWebsite }}</a>
        @endif
    </p>
</x-email-layout>

@if(! empty($trackingToken))<img src="{{ route('email.track.open', ['token' => $trackingToken]) }}" width="1" height="1" style="display:none" alt="">@endif
