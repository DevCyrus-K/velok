@extends('layouts.vertical', ['title' => 'Quote Details'])

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
                    <div>
                        <p class="text-muted mb-1">Quote Request</p>
                        <h3 class="mb-2">{{ $quote->reference() }}</h3>
                        <div class="d-flex flex-wrap align-items-center gap-2">
                            <span class="badge badge-soft-{{ $quote->statusBadgeClass() }}">{{ $quote->statusLabel() }}</span>
                            <span class="text-muted small">Submitted {{ $quote->created_at?->format('d M Y, h:i A') ?? 'N/A' }}</span>
                        </div>
                    </div>
                    <div class="d-flex flex-wrap gap-2">
                        <a class="btn btn-outline-secondary" href="{{ route('quotes.index') }}">Back to Quotes</a>
                        <a class="btn btn-outline-primary" href="mailto:{{ $quote->email }}">Email</a>
                        @if($quote->whatsappUrl())
                        <a class="btn btn-success" href="{{ $quote->whatsappUrl() }}" target="_blank" rel="noopener">WhatsApp</a>
                        @endif
                        <a class="btn btn-primary" href="{{ $quote->telLink() }}">Call</a>
                    </div>
                </div>

                <div class="row g-3 mt-4">
                    <div class="col-md-6 col-xl-3">
                        <div class="border rounded p-3 h-100">
                            <p class="text-muted mb-1">Customer</p>
                            <div class="fw-semibold text-dark">{{ $quote->full_name }}</div>
                            <small class="text-muted d-block mt-2">{{ $quote->email }}</small>
                            <small class="text-muted d-block">{{ $quote->phone }}</small>
                        </div>
                    </div>
                    <div class="col-md-6 col-xl-3">
                        <div class="border rounded p-3 h-100">
                            <p class="text-muted mb-1">Service Type</p>
                            <div class="fw-semibold text-dark">{{ $quote->serviceTypeLabel() }}</div>
                            <small class="text-muted d-block mt-2">Item details</small>
                            <small class="text-muted d-block">{{ $quote->move_size ?: 'Not specified' }}</small>
                        </div>
                    </div>
                    <div class="col-md-6 col-xl-3">
                        <div class="border rounded p-3 h-100">
                            <p class="text-muted mb-1">Pickup Location</p>
                            <div class="fw-semibold text-dark">{{ $quote->moving_from ?: 'Not specified' }}</div>
                            <small class="text-muted d-block mt-2">Drop-off</small>
                            <small class="text-muted d-block">{{ $quote->moving_to ?: 'Not specified' }}</small>
                        </div>
                    </div>
                    <div class="col-md-6 col-xl-3">
                        <div class="border rounded p-3 h-100">
                            <p class="text-muted mb-1">Preferred Date</p>
                            <div class="fw-semibold text-dark">{{ $quote->move_date?->format('d M Y') ?? 'Not specified' }}</div>
                            <small class="text-muted d-block mt-2">Source</small>
                            <small class="text-muted d-block">{{ $quote->source_page ?: 'Not captured' }}</small>
                        </div>
                    </div>
                </div>

                <div class="row g-4 mt-1">
                    <div class="col-xl-8">
                        <div class="border rounded p-4 h-100">
                            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
                                <div>
                                    <h5 class="mb-1">Submitted Request Details</h5>
                                    <p class="text-muted mb-0">Everything below is pulled directly from the stored quote request record.</p>
                                </div>
                                <span class="badge badge-soft-primary">{{ $quote->serviceTypeLabel() }}</span>
                            </div>

                            <div class="table-responsive">
                                <table class="table align-middle mb-0">
                                    <tbody>
                                        <tr>
                                            <th class="ps-0 text-muted fw-medium" style="width: 220px;">Customer Name</th>
                                            <td>{{ $quote->full_name }}</td>
                                        </tr>
                                        <tr>
                                            <th class="ps-0 text-muted fw-medium">Contact Info</th>
                                            <td>{{ $quote->email }} • {{ $quote->phone }}</td>
                                        </tr>
                                        <tr>
                                            <th class="ps-0 text-muted fw-medium">Service Type</th>
                                            <td>{{ $quote->serviceTypeLabel() }}</td>
                                        </tr>
                                        <tr>
                                            <th class="ps-0 text-muted fw-medium">Pickup Location</th>
                                            <td>{{ $quote->moving_from ?: 'Not specified' }}</td>
                                        </tr>
                                        <tr>
                                            <th class="ps-0 text-muted fw-medium">Drop-off Location</th>
                                            <td>{{ $quote->moving_to ?: 'Not specified' }}</td>
                                        </tr>
                                        <tr>
                                            <th class="ps-0 text-muted fw-medium">Preferred Move Date</th>
                                            <td>{{ $quote->move_date?->format('d M Y') ?? 'Not specified' }}</td>
                                        </tr>
                                        <tr>
                                            <th class="ps-0 text-muted fw-medium">Item Details</th>
                                            <td>{{ $quote->move_size ?: 'Not specified' }}</td>
                                        </tr>
                                        <tr>
                                            <th class="ps-0 text-muted fw-medium">Special Notes</th>
                                            <td>{{ $quote->additional_notes ?: 'No special notes were submitted.' }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-4">
                        <div class="border rounded p-4 h-100">
                            <h5 class="mb-3">Action Center</h5>
                            <div class="d-grid gap-2">
                                @if ($quotation)
                                    <a class="btn btn-info" href="{{ route('quotations.show', $quotation) }}">
                                        <i data-lucide="file-text" class="align-middle me-1"></i>View Quotation
                                    </a>
                                    @if ($quotation->status === 'draft')
                                        <a class="btn btn-warning" href="{{ route('quotations.edit', $quotation) }}">
                                            <i data-lucide="edit-3" class="align-middle me-1"></i>Edit Quotation
                                        </a>
                                        <form action="{{ route('quotations.send', $quotation) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="btn btn-success w-100">
                                                <i data-lucide="mail" class="align-middle me-1"></i>Send Quotation
                                            </button>
                                        </form>
                                    @endif
                                @elseif ($quote->status === 'processing' || $quote->status === 'quoted')
                                    <a class="btn btn-primary" href="{{ route('quotations.create', $quote) }}">
                                        <i data-lucide="plus" class="align-middle me-1"></i>Create Quotation
                                    </a>
                                @endif

                                @if ($quote->status !== 'quoted' && $quote->status !== 'closed')
                                    <form action="{{ route('quotes.approve', $quote) }}" data-confirm-button-class="btn-success" data-confirm-cancel-text="No, Keep" data-confirm-confirm-text="Yes, Approve" data-confirm-message="Do you want to approve this quote request?" data-confirm-modal data-confirm-title="Approve quote?" method="POST">
                                        @csrf
                                        @method('PATCH')
                                        <button class="btn btn-success w-100" type="submit">
                                            <i data-lucide="check" class="align-middle me-1"></i>Approve
                                        </button>
                                    </form>
                                @endif

                                @if ($quote->status !== 'closed' && $quote->status !== 'quoted')
                                    <form action="{{ route('quotes.decline', $quote) }}" data-confirm-button-class="btn-warning" data-confirm-cancel-text="No, Keep" data-confirm-confirm-text="Yes, Decline" data-confirm-message="Do you want to decline this quote request?" data-confirm-modal data-confirm-title="Decline quote?" method="POST">
                                        @csrf
                                        @method('PATCH')
                                        <button class="btn btn-outline-warning w-100" type="submit">
                                            <i data-lucide="x" class="align-middle me-1"></i>Decline
                                        </button>
                                    </form>
                                @endif

                                <a class="btn btn-outline-secondary" href="{{ route('quotes.edit', $quote) }}">
                                    <i data-lucide="edit-3" class="align-middle me-1"></i>Edit Request
                                </a>
                                <a class="btn btn-outline-dark" href="javascript:window.print()">
                                    <i data-lucide="printer" class="align-middle me-1"></i>Print
                                </a>
                                <form action="{{ route('quotes.destroy', $quote) }}" data-delete-confirm data-delete-message="Do you want to delete this quote request?" data-delete-title="Delete quote?" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-outline-danger w-100" type="submit">
                                        <i data-lucide="trash-2" class="align-middle me-1"></i>Delete Request
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
