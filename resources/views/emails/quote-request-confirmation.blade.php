@php($companyName = trim((string) ($company['name'] ?? config('app.name'))) ?: config('app.name'))
<!doctype html><html><body style="margin:0;background:#f6f7f9;font-family:Arial,Helvetica,sans-serif;color:#1f2937">
<table role="presentation" width="100%" style="padding:24px 0"><tr><td align="center">
<table role="presentation" width="640" style="max-width:640px;width:100%;background:#fff;border:1px solid #e5e7eb;border-radius:8px">
<tr><td style="padding:24px"><h1 style="margin:0 0 8px;color:#111827;font-size:22px">We received your quote request</h1>
<p>Hello {{ $quoteRequest->full_name }}, our team will review {{ $quoteRequest->reference() }} and contact you within 24 hours.</p>
<table width="100%" style="border-collapse:collapse;margin-top:14px">
<tr><td style="padding:8px;border:1px solid #e5e7eb">Pickup</td><td style="padding:8px;border:1px solid #e5e7eb">{{ $quoteRequest->moving_from }}</td></tr>
<tr><td style="padding:8px;border:1px solid #e5e7eb">Drop-off</td><td style="padding:8px;border:1px solid #e5e7eb">{{ $quoteRequest->moving_to }}</td></tr>
<tr><td style="padding:8px;border:1px solid #e5e7eb">Move Date</td><td style="padding:8px;border:1px solid #e5e7eb">{{ $quoteRequest->move_date?->format('d M Y') ?? 'To be confirmed' }}</td></tr>
</table>
<p style="margin-bottom:0">Thank you for choosing {{ $companyName }}.</p></td></tr>
</table>
</td></tr></table>
@if(! empty($trackingToken))<img src="{{ route('email.track.open', ['token' => $trackingToken]) }}" width="1" height="1" style="display:none" alt="">@endif
</body></html>
