@php
    $company = $company ?? app(\App\Support\CompanyProfile::class)->data();
    $companyName = trim((string) ($company['name'] ?? config('app.name'))) ?: config('app.name');
@endphp
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Quotation Approved</title>
    <style>body{margin:0;background:#f0fdf4;color:#1f2937;font-family:Arial,Helvetica,sans-serif}.wrap{max-width:720px;margin:0 auto;padding:28px 16px}.card{background:#fff;border:1px solid #bbf7d0;border-radius:8px;padding:24px}.money{font-size:24px;font-weight:800;color:#166534}.box{border:1px solid #e5e7eb;border-radius:6px;padding:12px;margin-top:12px}</style>
</head>
<body>
    <main class="wrap">
        <section class="card">
            <h1 style="margin-top:0;color:#166534">Quotation approved. Thank you.</h1>
            <p>{{ $companyName }} has received your approval for {{ $quotation->reference }}.</p>
            <div class="box">
                <div>Deposit required to confirm booking</div>
                <div class="money">KES {{ number_format($quotation->depositAmount(), 2) }}</div>
            </div>
            @if($paymentMethods->isNotEmpty())
                <div class="box">
                    <strong>Payment Methods</strong>
                    @foreach($paymentMethods as $method)
                        <p style="margin:6px 0 0">{{ $method->display }}</p>
                    @endforeach
                </div>
            @endif
            <p style="margin-bottom:0">Your booking is confirmed once our team verifies the deposit.</p>
        </section>
    </main>
</body>
</html>
