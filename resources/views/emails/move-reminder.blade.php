<!doctype html><html><body style="margin:0;background:#f6f7f9;font-family:Arial,Helvetica,sans-serif;color:#1f2937">
<table role="presentation" width="100%" style="padding:24px 0"><tr><td align="center">
<table role="presentation" width="640" style="max-width:640px;width:100%;background:#fff;border:1px solid #e5e7eb;border-radius:8px">
<tr><td style="padding:24px"><h1 style="margin:0 0 8px;color:#111827;font-size:22px">Your move is tomorrow</h1>
<p>Hello {{ $quotation->customer_name }}, this is a reminder for your move on {{ $quotation->move_date?->format('d M Y') }}.</p>
<p><strong>Pickup:</strong> {{ $quotation->pickup_location }} at 8:00 AM<br><strong>Drop-off:</strong> {{ $quotation->dropoff_location }}<br><strong>Balance:</strong> KES {{ number_format($quotation->balanceDue(), 2) }}</p>
<p style="margin-bottom:0">Please have all items packed and ready.</p></td></tr>
</table>
</td></tr></table>
@if(! empty($trackingToken))<img src="{{ route('email.track.open', ['token' => $trackingToken]) }}" width="1" height="1" style="display:none" alt="">@endif
</body></html>
