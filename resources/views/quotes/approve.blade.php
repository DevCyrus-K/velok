@php
    $company = $company ?? app(\App\Support\CompanyProfile::class)->data();
    $companyName = trim((string) ($company['name'] ?? config('app.name'))) ?: config('app.name');
    $companyPhone = trim((string) ($company['phone'] ?? ''));
    $companyEmail = trim((string) ($company['email'] ?? ''));
@endphp
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Approve Quote {{ $quotation->reference }}</title>
    <style>
        body{margin:0;background:#f6f7f9;color:#1f2937;font-family:Arial,Helvetica,sans-serif}
        .wrap{max-width:760px;margin:0 auto;padding:22px 14px}
        .panel{background:#fff;border:1px solid #e5e7eb;border-radius:8px;padding:22px}
        .top{display:flex;justify-content:space-between;gap:12px;flex-wrap:wrap;align-items:flex-start}
        .btn{display:inline-flex;align-items:center;justify-content:center;gap:6px;border-radius:6px;padding:11px 14px;text-decoration:none;font-weight:700;border:0;cursor:pointer}
        .btn-call{background:#df1119;color:#fff}.btn-wa{background:#16a34a;color:#fff}.btn-approve{background:#16a34a;color:#fff;width:100%;font-size:16px}
        .wa-icon{height:16px;width:16px}
        .muted{color:#6b7280}.grid{display:grid;grid-template-columns:1fr 1fr;gap:10px;margin:18px 0}
        .box{border:1px solid #e5e7eb;border-radius:6px;padding:12px}.money{font-size:24px;font-weight:800;color:#111827}
        label{display:block;font-weight:700;margin:14px 0 6px}input[type=text]{width:100%;box-sizing:border-box;border:1px solid #d1d5db;border-radius:6px;padding:12px;font-size:15px}
        @media(max-width:640px){.grid{grid-template-columns:1fr}.panel{padding:18px}.btn{width:100%;box-sizing:border-box;text-align:center}}
    </style>
</head>
<body>
    <main class="wrap">
        <section class="panel">
            <div class="top">
                <div>
                    <p class="muted" style="margin:0 0 4px">Quotation {{ $quotation->reference }}</p>
                    <h1 style="margin:0;font-size:28px">Approve Your Move Quote</h1>
                    <p class="muted" style="margin:8px 0 0">{{ $companyName }}</p>
                </div>
                <div style="display:flex;gap:8px;flex-wrap:wrap">
                    @if($companyPhone !== '')
                        <a class="btn btn-call" href="tel:{{ preg_replace('/[^0-9+]/', '', $companyPhone) }}">Call</a>
                    @endif
                    @if($companyPhone !== '')
                        <a class="btn btn-wa" href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $companyPhone) }}" target="_blank" rel="noopener">
                            <x-icons.whatsapp class="wa-icon" />WhatsApp
                        </a>
                    @endif
                </div>
            </div>

            <div class="grid">
                <div class="box">
                    <div class="muted">Customer</div>
                    <strong>{{ $quotation->customer_name }}</strong><br>
                    {{ $quotation->customer_email }}<br>{{ $quotation->customer_phone }}
                </div>
                <div class="box">
                    <div class="muted">Move</div>
                    <strong>{{ $quotation->move_date?->format('d M Y') ?? 'To be confirmed' }}</strong><br>
                    {{ $quotation->pickup_location }} to {{ $quotation->dropoff_location }}
                </div>
            </div>

            <div class="grid">
                <div class="box">
                    <div class="muted">Total</div>
                    <div class="money">KES {{ number_format($quotation->total, 2) }}</div>
                </div>
                <div class="box">
                    <div class="muted">Deposit To Confirm Booking</div>
                    <div class="money">KES {{ number_format($quotation->depositAmount(), 2) }}</div>
                    <small class="muted">Balance: KES {{ number_format($quotation->balanceDue(), 2) }}</small>
                </div>
            </div>

            @if($paymentMethods->isNotEmpty())
                <div class="box">
                    <strong>Payment Methods</strong>
                    @foreach($paymentMethods as $method)
                        <p style="margin:6px 0 0">{{ $method->display }}</p>
                    @endforeach
                </div>
            @endif

            @if ($errors->any())
                <div class="box" style="border-color:#fecaca;background:#fef2f2;color:#991b1b;margin-top:16px">
                    {{ $errors->first() }}
                </div>
            @endif

            <form action="{{ route('quote.customer.approve.submit', ['token' => $quotation->approval_token]) }}" method="POST" style="margin-top:18px">
                @csrf
                <label for="full_name">Full Name</label>
                <input id="full_name" name="full_name" type="text" value="{{ old('full_name', $quotation->customer_name) }}" required>
                <label style="font-weight:400;display:flex;gap:8px;align-items:flex-start;margin:16px 0">
                    <input name="agreement" type="checkbox" value="1" required style="margin-top:3px">
                    <span>I approve this quotation and agree to the payment terms, deposit requirement, and cancellation policy.</span>
                </label>
                <button class="btn btn-approve" type="submit">Approve Quotation</button>
            </form>
        </section>
    </main>
</body>
</html>
