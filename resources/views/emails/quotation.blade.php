@component('mail::message')
# Professional Moving Quotation

Dear {{ $client->full_name }},

Thank you for requesting a quotation from **KwikShift Movers**. We are pleased to provide you with a professional quotation for your upcoming relocation.

## Quotation Details

| Item | Value |
|------|-------|
| **Quote Reference** | {{ $client->reference() }} |
| **Issue Date** | {{ $quotation->quote_date?->format('d M, Y') }} |
| **Valid Until** | {{ $quotation->quote_valid_until?->format('d M, Y') }} |

## Your Move

| Detail | Information |
|--------|-------------|
| **Service Type** | {{ $client->serviceTypeLabel() }} |
| **Move Size** | {{ $client->move_size }} |
| **Route** | {{ $quotation->moving_from ?? $client->moving_from }} → {{ $quotation->moving_to ?? $client->moving_to }} |
| **Preferred Date** | {{ $quotation->move_date?->format('d M, Y') ?? $client->move_date?->format('d M, Y') }} |

## Quote Amount

**Total Quotation: KES {{ number_format($quotation->quote_amount ?? 0, 2) }}**

- Deposit Required ({{ $quotation->deposit_percentage ?? 30 }}%): KES {{ number_format(($quotation->quote_amount ?? 0) * (($quotation->deposit_percentage ?? 30) / 100), 2) }}
- Balance Due: KES {{ number_format(($quotation->quote_amount ?? 0) - (($quotation->quote_amount ?? 0) * (($quotation->deposit_percentage ?? 30) / 100)), 2) }}

## Services Included

@if ($quotation->services_included && count($quotation->services_included) > 0)
@foreach ($quotation->services_included as $service)
- **{{ $service['name'] }}**: {{ $service['description'] ?? 'Professional relocation service' }}
@endforeach
@else
- Professional moving and relocation services
@endif

## Payment Terms

{{ $quotation->payment_terms ?? 'Deposit required to secure the booking. Balance due upon service completion.' }}

## Important Information

**Cancellation Policy:** {{ $quotation->cancellation_notice_hours ?? 24 }} hours notice required for modifications or cancellations.

**Valid Until:** This quotation is valid until {{ $quotation->quote_valid_until?->format('d M, Y') }}.

@if ($quotation->additional_notes)
## Additional Notes

{{ $quotation->additional_notes }}
@endif

## Next Steps

To proceed with your booking or if you have any questions, please:

- **Reply to this email** with your confirmation
- **Call us**: +254 112587581 / +254111330980
- **Email us**: info@kwikshiftmovers.co.ke

A detailed PDF version of this quotation is attached for your reference.

---

**Authorized By:** {{ $quotation->authorized_by ?? 'Admin' }}  
**Signature:** {{ $quotation->signature ?? 'Pending' }}  
**Approval Date:** {{ $quotation->approval_date?->format('d M, Y') ?? 'Pending' }}

@component('mail::footer')
{{ config('app.name') }} - Professional Moving & Storage Services  
Londiani Road, off Likoni Road, Industrial Area, Nairobi, 00200, KE  
info@kwikshiftmovers.co.ke | +254 112587581 / +254111330980
@endcomponent
@endcomponent
