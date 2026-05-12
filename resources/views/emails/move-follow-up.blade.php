@php($companyName = trim((string) ($company['name'] ?? config('app.name'))) ?: config('app.name'))
<x-email-layout
    emailTitle="Move Follow-up"
    emailHeading="How was your move?"
    emailSubheading="We'd love to hear from you"
    :company="$company"
>
    <p class="text-heading" style="margin:0 0 18px 0; font-family:Arial, Helvetica, sans-serif; font-size:17px; line-height:28px; color:#04223e;">
        Dear {{ $quotation->customer_name }},
    </p>
    <p class="text-body" style="margin:0 0 22px 0; font-family:Arial, Helvetica, sans-serif; font-size:16px; line-height:28px; color:#5c6b7a;">
        We hope you are settling in well at your new location! We'd love to hear about your experience with us.
    </p>
    @if(! empty($reviewLink))
        <p style="margin:0 0 20px 0; text-align:center;">
            <a href="{{ $reviewLink }}" class="btn" style="display:inline-block; background:#df1119; color:#ffffff; text-decoration:none; font-size:16px; font-weight:700; padding:12px 24px; border-radius:0; font-family:Arial, Helvetica, sans-serif;">Leave a Review</a>
        </p>
    @endif
    <p class="text-body" style="margin:0 0 22px 0; font-family:Arial, Helvetica, sans-serif; font-size:16px; line-height:28px; color:#5c6b7a;">
        Your feedback helps us improve our service. Plus, if you refer a friend, ask our team about your next-move discount!
    </p>
    <p class="text-body" style="margin:0; font-family:Arial, Helvetica, sans-serif; font-size:16px; line-height:28px; color:#5c6b7a;">
        Best regards,<br>
        <span class="text-heading" style="font-weight:700; color:#04223e;">{{ $companyName }}</span>
    </p>
</x-email-layout>

@if(! empty($trackingToken))<img src="{{ route('email.track.open', ['token' => $trackingToken]) }}" width="1" height="1" style="display:none" alt="">@endif
