<!doctype html><html><body style="margin:0;background:#f6f7f9;font-family:Arial,Helvetica,sans-serif;color:#1f2937">
<table role="presentation" width="100%" style="padding:24px 0"><tr><td align="center">
<table role="presentation" width="640" style="max-width:640px;width:100%;background:#fff;border:1px solid #e5e7eb;border-radius:8px">
<tr><td style="padding:24px"><h1 style="margin:0 0 8px;color:#111827;font-size:22px">How was your move?</h1>
<p>Hello {{ $quotation->customer_name }}, we hope you are settling in well.</p>
@if(! empty($reviewLink))<p><a href="{{ $reviewLink }}" style="color:#df1119;font-weight:700">Leave a review</a></p>@endif
<p style="margin-bottom:0">Thank you for choosing us. Refer a friend and ask our team about your next-move discount.</p></td></tr>
</table>
</td></tr></table>
@if(! empty($trackingToken))<img src="{{ route('email.track.open', ['token' => $trackingToken]) }}" width="1" height="1" style="display:none" alt="">@endif
</body></html>
