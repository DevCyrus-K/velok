@php
    $clientName = trim((string) ($quote->full_name ?? $quotation->customer_name ?? 'Client')) ?: 'Client';
    $companyName = trim((string) ($company['name'] ?? config('app.name'))) ?: config('app.name');
    $companyPhone = trim((string) ($company['phone'] ?? ''));
    $companyEmail = trim((string) ($company['email'] ?? ''));
    $companyWebsite = trim((string) ($company['website'] ?? ''));
    $representativeName = trim((string) ($company['authorized_representative_name'] ?? $companyName)) ?: $companyName;
@endphp
Dear {{ $clientName }},

Thank you for approving your quote with {{ $companyName }}.

Please find attached your Service Agreement for your upcoming move.

Kindly review the document carefully. Fields marked with a blank line
(___________) will be completed and confirmed by our team before your
move date. If you have any questions or corrections, please contact us
at {{ $companyPhone }} or {{ $companyEmail }}.

We look forward to serving you.

Warm regards,
{{ $representativeName }}
{{ $companyName }}
{{ $companyPhone }}
{{ $companyEmail }}
@if($companyWebsite !== '')
{{ $companyWebsite }}
@endif
