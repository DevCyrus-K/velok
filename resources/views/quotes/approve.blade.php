@php
    $company = $company ?? app(\App\Support\CompanyProfile::class)->data();
    $companyName = trim((string) ($company['name'] ?? config('app.name'))) ?: config('app.name');
    $companyPhone = trim((string) ($company['phone'] ?? ''));
    $companyEmail = trim((string) ($company['email'] ?? ''));
    $companyLogoUrl = app(\App\Support\CompanyProfile::class)->logoUrl();
    $companyAddress = collect([
        $company['address_line_1'] ?? null,
        $company['address_line_2'] ?? null,
    ])->map(fn ($line) => trim((string) $line))->filter()->implode(', ');
    $callPhone = preg_replace('/[^0-9+]/', '', $companyPhone);
    $whatsappPhone = preg_replace('/[^0-9]/', '', $companyPhone);
    $quoteRequest = $quotation->quoteRequest;
    $quoteReference = $quotation->reference;
    $quoteTotal = (float) ($quotation->quote_amount ?? 0);
    $quoteServices = collect($quotation->services_included ?? [])
        ->map(function ($service) {
            if (is_array($service)) {
                return trim(implode(' ', array_filter($service, fn ($value) => filled($value))));
            }

            return trim((string) $service);
        })
        ->filter()
        ->values();
    $quoteServiceCount = max(1, $quoteServices->count());
    $quoteBaseAmount = round($quoteTotal / $quoteServiceCount, 2);
    $quoteLineItems = $quoteServices->isNotEmpty()
        ? $quoteServices->map(fn ($description, $index) => (object) [
            'description' => $description,
            'quantity' => 1,
            'unit_price' => $index + 1 === $quoteServiceCount ? round($quoteTotal - ($quoteBaseAmount * ($quoteServiceCount - 1)), 2) : $quoteBaseAmount,
            'amount' => $index + 1 === $quoteServiceCount ? round($quoteTotal - ($quoteBaseAmount * ($quoteServiceCount - 1)), 2) : $quoteBaseAmount,
        ])
        : collect([(object) [
            'description' => trim(collect([
                $quoteRequest?->serviceTypeLabel() ?? 'Moving service',
                $quotation->pickup_location,
                $quotation->dropoff_location,
            ])->filter()->implode(' - ')),
            'quantity' => 1,
            'unit_price' => $quoteTotal,
            'amount' => $quoteTotal,
        ]]);
@endphp
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Approve Quote {{ $quoteReference }}</title>
    <style>
        *{box-sizing:border-box}
        body{margin:0;background:#f5f7fb;color:#04223e;font-family:Arial,Helvetica,sans-serif;font-size:14px;line-height:1.55}
        .wrap{max-width:960px;margin:0 auto;padding:16px 10px}
        .sheet{background:#fff;border:1px solid #e6edf5;padding:22px 18px 34px 34px}
        .doc-head,.party-row,.summary-row{display:grid;grid-template-columns:30% 70%;gap:18px;align-items:start}
        .doc-title{text-align:right}
        .doc-title h1{font-size:48px;line-height:1;margin:0;color:#04223e;letter-spacing:0;text-transform:uppercase}
        .accent{width:86px;height:4px;background:#df1119;margin:12px 0 0 auto}
        .logo img{max-height:54px;max-width:170px}
        .rule{height:7px;background:#df1119;border-radius:0;margin:20px 0 8px}
        .meta{margin:0 0 24px}
        .meta p,.party p,.move p,.summary p{margin:0 0 4px}
        .party-row{grid-template-columns:43% 57%;margin-bottom:18px}
        .party-right{text-align:right}
        .label{font-weight:800;color:#04223e}
        .muted{color:#5c6b7a}
        .move{margin-bottom:22px}
        table{width:100%;border-collapse:collapse}
        th,td{padding:11px 14px;border-top:1px solid #e6edf5;text-align:left;vertical-align:middle}
        thead th{background:#fff5f5;border-top:2px solid #df1119;font-weight:800}
        .text-right{text-align:right}
        .items{border:1px solid #e6edf5;margin-bottom:0}
        .summary-row{grid-template-columns:55% 45%;gap:0;margin:0 0 28px;align-items:start}
        .payment{padding:0 18px 0 0}
        .payment p{margin:4px 0}
        .totals table{margin-top:0}
        .totals td{border-top:0;padding:0 14px 9px}
        .totals tr:first-child td{padding-top:0}
        .totals .strong td{font-weight:800}
        .totals .grand td,.totals .balance td{border-top:2px solid #df1119;padding-top:12px;font-size:17px;font-weight:800}
        .terms,.approval{border:1px solid #e6edf5;padding:18px 20px;margin-top:18px}
        .terms ul{margin:8px 0 0;padding-left:20px}
        .terms li{margin-bottom:6px}
        .approval-grid{display:grid;grid-template-columns:1fr 1fr;gap:18px;align-items:start}
        .notice{border:1px solid #fecaca;background:#fef2f2;color:#991b1b;padding:12px;margin-bottom:14px}
        label{display:block;font-weight:800;margin:0 0 6px}
        input[type=text]{width:100%;border:1px solid #cfd8e3;border-radius:0;padding:12px;font-size:15px;color:#04223e}
        .check{display:flex;gap:10px;align-items:flex-start;margin:14px 0 16px;font-weight:400}
        .check input{margin-top:4px}
        .actions{display:flex;gap:8px;flex-wrap:wrap;justify-content:flex-end}
        .btn{display:inline-flex;align-items:center;justify-content:center;gap:7px;border:0;border-radius:0;color:#fff;cursor:pointer;font-weight:800;line-height:1;text-decoration:none;padding:12px 16px}
        .btn-call{background:#df1119}
        .btn-wa,.btn-approve{background:#16a34a}
        .btn-approve{width:100%;font-size:16px;padding:14px 16px}
        .wa-icon{height:16px;width:16px}
        .footer{border-top:1px solid #e6edf5;color:#5c6b7a;font-size:12px;margin-top:22px;padding-top:14px;text-align:center}
        @media(max-width:720px){
            .wrap{padding:0}
            .sheet{border-left:0;border-right:0;padding:18px 14px 28px}
            .doc-head,.party-row,.summary-row,.approval-grid{grid-template-columns:1fr}
            .doc-title,.party-right{text-align:left}
            .accent{margin-left:0}
            .doc-title h1{font-size:34px}
            .actions{justify-content:stretch}
            .btn{width:100%}
            .items{display:block;overflow-x:auto}
            .items table{min-width:620px}
            .payment{padding-right:0}
        }
    </style>
</head>
<body>
    <main class="wrap">
        <section class="sheet">
            <div class="doc-head">
                <div class="logo">
                    @if($companyLogoUrl !== '')
                        <img alt="{{ $companyName }} logo" src="{{ $companyLogoUrl }}">
                    @endif
                </div>
                <div class="doc-title">
                    <h1>Quotation</h1>
                    <div class="accent"></div>
                </div>
            </div>

            <div class="rule"></div>

            <div class="meta">
                <p>Quotation No: <strong>{{ $quoteReference }}</strong></p>
                <p>Date: <strong>{{ $quotation->quote_date?->format('d M Y') ?? $quotation->created_at?->format('d M Y') }}</strong></p>
                <p>Valid Until: <strong>{{ $quotation->quote_valid_until?->format('d M Y') ?? 'To be confirmed' }}</strong></p>
            </div>

            <div class="party-row">
                <div class="party">
                    <p class="label">Quote To:</p>
                    <p>{{ $quotation->customer_name }}</p>
                    <p>{{ $quotation->customer_email }}</p>
                    <p>{{ $quotation->customer_phone }}</p>
                </div>
                <div class="party party-right">
                    <p class="label">Quote From:</p>
                    <p>{{ $companyName }}</p>
                    @if($companyAddress !== '')<p>{{ $companyAddress }}</p>@endif
                    @if($companyPhone !== '')<p>{{ $companyPhone }}</p>@endif
                    @if($companyEmail !== '')<p>{{ $companyEmail }}</p>@endif
                </div>
            </div>

            <div class="move">
                <p class="label">Move Details:</p>
                <p>From: {{ $quotation->pickup_location ?: 'Origin to be confirmed' }}</p>
                <p>To: {{ $quotation->dropoff_location ?: 'Destination to be confirmed' }}</p>
                <p>Move Date: {{ $quotation->move_date?->format('d M Y') ?? 'To be confirmed' }}</p>
                <p>Service Type: {{ $quoteRequest?->serviceTypeLabel() ?? 'Moving service' }}</p>
            </div>

            <div class="items">
                <table>
                    <thead>
                        <tr>
                            <th style="width:12%">Item</th>
                            <th>Description</th>
                            <th style="width:18%">Price</th>
                            <th style="width:10%">Qty</th>
                            <th class="text-right" style="width:18%">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($quoteLineItems as $index => $item)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $item->description }}</td>
                                <td>KES {{ number_format((float) $item->unit_price, 2) }}</td>
                                <td>{{ $item->quantity }}</td>
                                <td class="text-right">KES {{ number_format((float) $item->amount, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="summary-row">
                <div class="payment">
                    <p class="label">Payment info:</p>
                    @forelse($paymentMethods as $method)
                        <p>{{ $method->display }}</p>
                    @empty
                        <p>Payment details will be shared by our team.</p>
                    @endforelse
                </div>
                <div class="totals">
                    <table>
                        <tbody>
                            <tr class="strong">
                                <td>Subtotal</td>
                                <td class="text-right">KES {{ number_format($quotation->subtotal, 2) }}</td>
                            </tr>
                            <tr class="grand">
                                <td>Grand Total</td>
                                <td class="text-right">KES {{ number_format($quotation->total, 2) }}</td>
                            </tr>
                            <tr>
                                <td>Deposit Required ({{ number_format((float) ($quotation->deposit_percentage ?? 0), 2) }}%)</td>
                                <td class="text-right">KES {{ number_format($quotation->depositAmount(), 2) }}</td>
                            </tr>
                            <tr class="balance">
                                <td>Balance Due on Move Day</td>
                                <td class="text-right">KES {{ number_format($quotation->balanceDue(), 2) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="terms">
                <p class="label">Terms & Conditions:</p>
                <ul>
                    @if($quotation->payment_terms)
                        <li>{{ $quotation->payment_terms }}</li>
                    @endif
                    <li>{{ $quotation->cancellationPolicyText() }}</li>
                    <li>Your booking is confirmed once the deposit has been received and verified.</li>
                </ul>
            </div>

            <div class="approval">
                <div class="approval-grid">
                    <div>
                        <p class="label" style="margin-top:0">Approve this quotation</p>
                        <p class="muted" style="margin:0">Call, WhatsApp, or approve online. We will confirm your booking after deposit verification.</p>
                        <div class="actions" style="margin-top:14px">
                            @if($callPhone !== '')
                                <a class="btn btn-call" href="tel:{{ $callPhone }}">Call</a>
                            @endif
                            @if($whatsappPhone !== '')
                                <a class="btn btn-wa" href="https://wa.me/{{ $whatsappPhone }}" target="_blank" rel="noopener">
                                    <x-icons.whatsapp class="wa-icon" />WhatsApp
                                </a>
                            @endif
                        </div>
                    </div>
                    <form action="{{ route('quote.customer.approve.submit', ['token' => $quotation->approval_token]) }}" method="POST">
                        @csrf
                        @if ($errors->any())
                            <div class="notice">{{ $errors->first() }}</div>
                        @endif
                        <label for="full_name">Full Name</label>
                        <input id="full_name" name="full_name" type="text" value="{{ old('full_name', $quotation->customer_name) }}" required>
                        <label class="check">
                            <input name="agreement" type="checkbox" value="1" required>
                            <span>I approve this quotation and agree to the payment terms, deposit requirement, and cancellation policy.</span>
                        </label>
                        <button class="btn btn-approve" type="submit">Approve Quotation</button>
                    </form>
                </div>
            </div>

            <div class="footer">
                {{ $companyName }}@if($companyEmail !== '') | {{ $companyEmail }}@endif @if($companyPhone !== '') | {{ $companyPhone }}@endif
            </div>
        </section>
    </main>
</body>
</html>
