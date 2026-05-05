@extends('layouts.vertical', ['title' => 'Quotation - ' . $quotation->quoteRequest->reference()])

@section('content')

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <!-- Header & Branding -->
                <div class="clearfix">
                    <div class="float-sm-end">
                        <div class="auth-logo">
                            <img alt="logo-dark" class="logo-dark me-1" height="24" src="/images/logo-dark.png" />
                            <img alt="logo-light" class="logo-light me-1" height="24" src="/images/logo-white.png" />
                        </div>
                        <h6 class="fw-bold mt-3 mb-2">KwikShift Movers & Relocators</h6>
                        <address class="mt-2">
                            Londiani Road, off Likoni Road<br />
                            Industrial Area, Nairobi, 00200, KE<br />
                            <abbr title="Phone">P:</abbr> +254 112587581 / +254111330980 <br>
                            <abbr title="Email">E:</abbr> info@kwikshiftmovers.co.ke
                        </address>
                    </div>
                    <div class="float-sm-start">
                        <h2 class="fw-bold mb-3">QUOTATION</h2>
                        <h5 class="card-title mb-2">Quote: {{ $quotation->quoteRequest->reference() }}</h5>
                        <p class="mb-2">{{ $quotation->quote_date?->format('d M, Y h:i A') ?? 'N/A' }}</p>
                        <span class="badge badge-soft-{{ $quotation->status === 'sent' ? 'success' : ($quotation->status === 'draft' ? 'info' : 'warning') }}">
                            {{ ucfirst($quotation->status) }}
                        </span>
                    </div>
                </div>

                <hr class="my-4">

                <div class="row mt-4">
                    <div class="col-md-6">
                        <h6 class="fw-normal text-muted">Customer</h6>
                        <h6 class="fs-14 fw-bold">{{ $quotation->quoteRequest->full_name }}</h6>
                        <address class="mb-0">
                            {{ $quotation->quoteRequest->email }}<br />
                            <abbr title="Phone">P:</abbr> {{ $quotation->quoteRequest->phone }}<br />
                            <span class="text-muted">{{ $quotation->quoteRequest->serviceTypeLabel() }}</span>
                        </address>
                    </div>
                    <div class="col-md-6">
                        <h6 class="fw-normal text-muted">Moving Route</h6>
                        <h6 class="fs-14 fw-bold">{{ $quotation->moving_from ?? $quotation->quoteRequest->moving_from }}</h6>
                        <address class="mb-0">
                            to {{ $quotation->moving_to ?? $quotation->quoteRequest->moving_to }}<br />
                            Scheduled: {{ $quotation->move_date?->format('d M, Y') ?? $quotation->quoteRequest->move_date?->format('d M, Y') ?? 'Not specified' }}<br />
                            Size: {{ $quotation->quoteRequest->move_size ?: 'Not specified' }}
                        </address>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12">
                        <div class="table-responsive table-borderless text-nowrap mt-3 table-centered">
                            <table class="table mb-0">
                                <thead class="bg-light bg-opacity-50">
                                    <tr>
                                        <th class="border-0 py-2">Detail</th>
                                        <th class="border-0 py-2">Description</th>
                                        <th class="text-end border-0 py-2">Value</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td><strong>Quote Amount (KES)</strong></td>
                                        <td>Professional moving service</td>
                                        <td class="text-end"><strong>KES {{ number_format($quotation->quote_amount ?? 0, 2) }}</strong></td>
                                    </tr>
                                    <tr>
                                        <td>Deposit Required</td>
                                        <td>{{ ($quotation->deposit_percentage ?? 30) }}% to secure booking</td>
                                        <td class="text-end">KES {{ number_format(($quotation->quote_amount ?? 0) * (($quotation->deposit_percentage ?? 30) / 100), 2) }}</td>
                                    </tr>
                                    <tr>
                                        <td>Balance Due</td>
                                        <td>Amount due upon service completion</td>
                                        <td class="text-end">KES {{ number_format(($quotation->quote_amount ?? 0) - (($quotation->quote_amount ?? 0) * (($quotation->deposit_percentage ?? 30) / 100)), 2) }}</td>
                                    </tr>
                                    <tr>
                                        <td>Quote Valid Until</td>
                                        <td>{{ $quotation->quote_valid_until?->format('d M, Y') ?? 'Not specified' }}</td>
                                        <td class="text-end">{{ $quotation->quote_valid_until?->diffInDays() ?? 'N/A' }} days</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                @if ($quotation->services_included && count($quotation->services_included) > 0)
                    <div class="row mt-4">
                        <div class="col-12">
                            <h6 class="fw-normal text-muted mb-2">Services Included</h6>
                            <div class="table-responsive table-borderless text-nowrap table-centered">
                                <table class="table mb-0">
                                    <thead class="bg-light bg-opacity-50">
                                        <tr>
                                            <th class="border-0 py-2">Service</th>
                                            <th class="border-0 py-2">Description</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($quotation->services_included as $service)
                                            <tr>
                                                <td><strong>{{ $service['name'] ?? 'Service' }}</strong></td>
                                                <td>{{ $service['description'] ?? 'Professional relocation service' }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                @endif

                <div class="row mt-4">
                    <div class="col-sm-7">
                        <div class="clearfix">
                            @if ($quotation->payment_terms)
                                <h6 class="text-muted">Payment Terms:</h6>
                                <small class="text-muted">{{ $quotation->payment_terms }}</small>
                            @endif

                            @if ($quotation->additional_notes)
                                <div class="mt-3">
                                    <h6 class="text-muted mb-1">Additional Notes:</h6>
                                    <small class="text-muted">{{ $quotation->additional_notes }}</small>
                                </div>
                            @endif

                            @if ($quotation->cancellation_notice_hours)
                                <div class="mt-3">
                                    <h6 class="text-muted mb-1">Cancellation Policy:</h6>
                                    <small class="text-muted">{{ $quotation->cancellation_notice_hours }} hours notice required</small>
                                </div>
                            @endif
                        </div>
                    </div>
                    <div class="col-sm-5">
                        <div class="float-end">
                            <p><span class="fw-medium">Quote ID :</span>
                                <span class="float-end">{{ $quotation->quoteRequest->reference() }}</span>
                            </p>
                            <p><span class="fw-medium">Authorized By :</span>
                                <span class="float-end">{{ $quotation->authorized_by ?? 'Admin' }}</span>
                            </p>
                            <p><span class="fw-medium">Approval Date :</span>
                                <span class="float-end">{{ $quotation->approval_date?->format('d M, Y') ?? 'Pending' }}</span>
                            </p>
                            <p><span class="fw-medium">Signature :</span>
                                <span class="float-end">{{ $quotation->signature ?? 'Pending' }}</span>
                            </p>
                            <h3>KES {{ number_format($quotation->quote_amount ?? 0, 2) }}</h3>
                        </div>
                        <div class="clearfix"></div>
                    </div>
                </div>

                <div class="mt-5 mb-1">
                    <div class="text-end d-print-none d-flex flex-wrap justify-content-end gap-2">
                        <a class="btn btn-outline-secondary" href="{{ route('quotes.show', $quotation->quoteRequest) }}">
                            <i data-lucide="arrow-left" class="align-middle me-1"></i>
                            Back to Quote
                        </a>
                        <a class="btn btn-info" href="{{ route('quotations.pdf', $quotation) }}" download>
                            <i data-lucide="download" class="align-middle me-1"></i>
                            Download PDF
                        </a>
                        <a class="btn btn-primary" href="javascript:window.print()">
                            <i data-lucide="printer" class="align-middle me-1"></i>
                            Print
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
