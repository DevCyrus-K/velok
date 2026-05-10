<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Approval Link Expired</title>
    <style>body{margin:0;background:#f6f7f9;color:#1f2937;font-family:Arial,Helvetica,sans-serif}.wrap{max-width:620px;margin:0 auto;padding:32px 16px}.card{background:#fff;border:1px solid #e5e7eb;border-radius:8px;padding:24px}.btn{display:inline-block;margin-top:10px;background:#df1119;color:#fff;text-decoration:none;border-radius:6px;padding:11px 14px;font-weight:700}</style>
</head>
<body>
    <main class="wrap">
        <section class="card">
            <h1 style="margin-top:0">This approval link has expired.</h1>
            <p>Please contact our team and we will resend a fresh quotation approval link.</p>
            @if(! empty($companyPhone))
                <a class="btn" href="tel:{{ preg_replace('/[^0-9+]/', '', $companyPhone) }}">Call {{ $companyPhone }}</a>
            @endif
            @if(! empty($companyEmail))
                <p>Email: <a href="mailto:{{ $companyEmail }}">{{ $companyEmail }}</a></p>
            @endif
        </section>
    </main>
</body>
</html>
