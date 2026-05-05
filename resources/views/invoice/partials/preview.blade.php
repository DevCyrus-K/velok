<div class="invoice-preview-sheet bg-white text-dark p-3 p-md-4">
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 border-bottom pb-3 mb-4">
        <div>
            <h3 class="mb-1">Invoice Preview</h3>
            <p class="text-muted mb-0">{{ $invoice->invoice_number }}</p>
        </div>
        <div class="text-md-end">
            <div class="fw-semibold">KwikShift Movers Ltd</div>
            <div class="text-muted small">Londiani Road, Industrial Area, Nairobi</div>
            <div class="text-muted small">+254 112 587 581 • info@kwikshiftmovers.co.ke</div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-6">
            <div class="border rounded p-3 h-100">
                <p class="text-muted mb-1">Bill To</p>
                <div class="fw-semibold">{{ $invoice->customer_name }}</div>
                <div class="small text-muted">{{ $invoice->customer_email }}</div>
                <div class="small text-muted">{{ $invoice->customer_phone ?: 'Phone not provided' }}</div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="border rounded p-3 h-100">
                <p class="text-muted mb-1">Move Summary</p>
                <div class="fw-semibold">{{ $invoice->move_origin ?: 'Origin not recorded' }} to {{ $invoice->move_destination ?: 'Destination not recorded' }}</div>
                <div class="small text-muted">Move date: {{ $invoice->move_date?->format('d M Y') ?? 'Not scheduled' }}</div>
                <div class="small text-muted">Quote ref: {{ $invoice->quote_reference ?: 'Not linked' }}</div>
            </div>
        </div>
    </div>

    <div class="table-responsive mb-4">
        <table class="table align-middle mb-0">
            <thead class="bg-light-subtle">
                <tr>
                    <th>Line Item</th>
                    <th>Qty</th>
                    <th>Unit Price</th>
                    <th class="text-end">Total</th>
                </tr>
            </thead>
            <tbody>
                @forelse($invoice->items as $item)
                <tr>
                    <td>{{ $item->description }}</td>
                    <td>{{ $item->quantity }}</td>
                    <td>KES {{ number_format((float) $item->unit_price, 2) }}</td>
                    <td class="text-end">KES {{ number_format((float) $item->total, 2) }}</td>
                </tr>
                @empty
                <tr>
                    <td class="text-center text-muted py-4" colspan="4">No line items have been added to this invoice yet.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="row g-3 align-items-start">
        <div class="col-md-7">
            <div class="border rounded p-3 h-100">
                <p class="text-muted mb-1">Payment Details</p>
                <div class="small text-muted">Invoice date: {{ $invoice->invoice_date?->format('d M Y') ?? 'N/A' }}</div>
                <div class="small text-muted">Due date: {{ $invoice->due_date?->format('d M Y') ?? 'N/A' }}</div>
                <div class="small text-muted">Payment method: {{ $invoice->payment_method ? Str::headline(str_replace('_', ' ', $invoice->payment_method)) : 'To be agreed' }}</div>
                <div class="small text-muted">Status: {{ ucfirst($invoice->status) }}</div>
            </div>
        </div>
        <div class="col-md-5">
            <div class="border rounded p-3 h-100">
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Subtotal</span>
                    <span>KES {{ number_format((float) $invoice->subtotal, 2) }}</span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Tax</span>
                    <span>KES {{ number_format((float) $invoice->tax, 2) }}</span>
                </div>
                <div class="d-flex justify-content-between pt-2 border-top fw-semibold">
                    <span>Total</span>
                    <span>KES {{ number_format((float) $invoice->total_amount, 2) }}</span>
                </div>
            </div>
        </div>
    </div>
</div>
