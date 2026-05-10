<!doctype html><html><body style="margin:0;background:#f0fdf4;font-family:Arial,Helvetica,sans-serif;color:#1f2937">
<table role="presentation" width="100%" style="padding:24px 0"><tr><td align="center">
<table role="presentation" width="640" style="max-width:640px;width:100%;background:#fff;border:1px solid #bbf7d0;border-radius:8px">
<tr><td style="padding:24px"><h1 style="margin:0 0 8px;color:#166534;font-size:22px">Deposit received. Booking confirmed.</h1>
<p>Hello {{ $quotation->customer_name }}, we received your deposit for {{ $quotation->reference }}.</p>
<p><strong>Amount:</strong> KES {{ number_format($quotation->depositAmount(), 2) }}<br><strong>Reference:</strong> {{ $quotation->deposit_reference }}<br><strong>Move date:</strong> {{ $quotation->move_date?->format('d M Y') ?? 'To be confirmed' }}</p>
<p style="margin-bottom:0">Balance due on move day: KES {{ number_format($quotation->balanceDue(), 2) }}</p></td></tr>
</table>
</td></tr></table>
@if(! empty($trackingToken))<img src="{{ route('email.track.open', ['token' => $trackingToken]) }}" width="1" height="1" style="display:none" alt="">@endif
</body></html>
