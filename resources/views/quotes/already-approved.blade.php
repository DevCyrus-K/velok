<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Quotation Already Approved</title>
    <style>body{margin:0;background:#f0fdf4;color:#1f2937;font-family:Arial,Helvetica,sans-serif}.wrap{max-width:620px;margin:0 auto;padding:32px 16px}.card{background:#fff;border:1px solid #bbf7d0;border-radius:8px;padding:24px}</style>
</head>
<body>
    <main class="wrap">
        <section class="card">
            <h1 style="margin-top:0;color:#166534">Quotation already approved.</h1>
            <p>{{ $quotation->reference }} was approved by {{ $quotation->approved_by_name ?: $quotation->customer_name }}.</p>
            <p>The next step is deposit confirmation so your booking can be fully confirmed.</p>
        </section>
    </main>
</body>
</html>
