@php
    $companyName = trim((string) ($company['name'] ?? '')) ?: 'Company';
    $companyContact = collect([$company['phone'] ?? null, $company['email'] ?? null])
        ->map(fn ($value) => trim((string) $value))
        ->filter()
        ->implode(' | ');
@endphp

@component('mail::message')
# Invoice {{ $invoice->invoice_number }}


@if(filled($messageBody ?? null))
{!! nl2br(e($messageBody)) !!}
@else
Your invoice from {{ $companyName }} is ready.
@endif

@component('mail::table')
| Detail | Value |
| --- | --- |
| Invoice date | {{ $invoice->invoice_date?->format('d M, Y') ?? 'Not recorded' }} |
| Due date | {{ $invoice->due_date?->format('d M, Y') ?? 'Not recorded' }} |
| Amount due | KES {{ number_format((float) $invoice->total_amount, 2) }} |
| Payment method | {{ $invoice->paymentMethodLabel() }} |
@endcomponent

@if ($invoice->move_origin || $invoice->move_destination)
**Move:** {{ $invoice->move_origin ?: 'Origin not recorded' }} to {{ $invoice->move_destination ?: 'Destination not recorded' }}
@endif

@if($attachPdf ?? true)
The PDF invoice is attached for your records.
@endif

To confirm payment or ask a question, reply to this email or contact us directly.

@if($companyContact !== '')
**Contact:** {{ $companyContact }}
@endif

@if(! empty($trackingToken))
<img src="{{ route('email.track.open', ['token' => $trackingToken]) }}" width="1" height="1" style="display:none" alt="">
@endif

Thanks,<br>
{{ $companyName }}
@endcomponent
