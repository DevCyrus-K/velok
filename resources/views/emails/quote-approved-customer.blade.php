@php($companyName = trim((string) ($company['name'] ?? config('app.name'))) ?: config('app.name'))
<!doctype html><html><body style="margin:0;background:#f0fdf4;font-family:Arial,Helvetica,sans-serif;color:#1f2937">
<table role="presentation" width="100%" style="padding:24px 0"><tr><td align="center">
<table role="presentation" width="640" style="max-width:640px;width:100%;background:#fff;border:1px solid #bbf7d0;border-radius:8px">
<tr><td style="padding:24px"><h1 style="margin:0 0 8px;color:#166534;font-size:22px">Quotation approved</h1>
<p>Hello {{ $quotation->customer_name }}, thank you for approving {{ $quotation->reference }}.</p>
<p style="font-size:18px"><strong>Deposit required:</strong> KES {{ number_format($quotation->depositAmount(), 2) }}</p>
@foreach($paymentMethods as $method)<p style="margin:4px 0">{{ $method->display }}</p>@endforeach
<p style="margin-bottom:0">Your booking is confirmed once the deposit is received and verified by {{ $companyName }}.</p></td></tr>
</table>
</td></tr></table>
@if(! empty($trackingToken))<img src="{{ route('email.track.open', ['token' => $trackingToken]) }}" width="1" height="1" style="display:none" alt="">@endif
</body></html>
