@php
    $appName = config('app.name', 'Kwikshift Admin Panel');
    $logoUrl = asset('images/logo-dark.png');
@endphp
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Account temporarily locked</title>
</head>
<body style="margin:0;padding:0;background:#f6f7fb;color:#111827;font-family:Arial,Helvetica,sans-serif;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background:#f6f7fb;padding:28px 12px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width:600px;background:#ffffff;border:1px solid #e5e7eb;border-radius:8px;overflow:hidden;">
                    <tr>
                        <td style="padding:26px 30px;border-bottom:1px solid #eef2f7;">
                            <img src="{{ $logoUrl }}" alt="{{ $appName }}" height="30" style="display:block;border:0;max-width:160px;height:30px;">
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:30px 30px 8px;">
                            <p style="margin:0 0 10px;color:#f95c5c;font-size:13px;font-weight:700;text-transform:uppercase;letter-spacing:.04em;">Security alert</p>
                            <h1 style="font-size:24px;line-height:1.3;margin:0;color:#111827;">Account temporarily locked</h1>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:8px 30px 30px;font-size:15px;line-height:1.65;color:#4b5563;">
                            <p style="margin:0 0 12px;">We noticed multiple failed login attempts on your {{ $appName }} account.</p>
                            <p style="margin:0 0 12px;">Your account has been temporarily locked for security.</p>
                            <p style="margin:0;">If this was not you, please reset your password immediately.</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
    @if(! empty($trackingToken))
        <img src="{{ route('email.track.open', ['token' => $trackingToken]) }}" width="1" height="1" style="display:none;border:none;outline:none;" alt="">
    @endif
</body>
</html>
