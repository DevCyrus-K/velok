@php
    $appName = config('app.name', 'Kwikshift Admin Panel');
    $logoUrl = asset('images/logo-dark.png');
    $headline = $successful ? 'New login detected' : 'Someone tried to login';
    $accent = $successful ? '#22b956' : '#f59e0b';
@endphp
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $headline }}</title>
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
                            <p style="margin:0 0 10px;color:{{ $accent }};font-size:13px;font-weight:700;text-transform:uppercase;letter-spacing:.04em;">Security alert</p>
                            <h1 style="font-size:24px;line-height:1.3;margin:0;color:#111827;">{{ $headline }}</h1>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:8px 30px 20px;font-size:15px;line-height:1.65;color:#4b5563;">
                            <p style="margin:0 0 12px;">Hi {{ $user->name }},</p>
                            @if($successful)
                                <p style="margin:0;">Your {{ $appName }} account was just accessed. If this was you, no action is needed.</p>
                            @else
                                <p style="margin:0;">Someone tried to sign in to your {{ $appName }} account but did not complete the login.</p>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:0 30px 30px;">
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="border:1px solid #e5e7eb;border-radius:8px;">
                                <tr>
                                    <td style="padding:12px 14px;border-bottom:1px solid #e5e7eb;color:#6b7280;font-size:13px;">Time</td>
                                    <td style="padding:12px 14px;border-bottom:1px solid #e5e7eb;color:#111827;font-size:13px;text-align:right;">{{ $occurredAt }}</td>
                                </tr>
                                <tr>
                                    <td style="padding:12px 14px;border-bottom:1px solid #e5e7eb;color:#6b7280;font-size:13px;">IP address</td>
                                    <td style="padding:12px 14px;border-bottom:1px solid #e5e7eb;color:#111827;font-size:13px;text-align:right;">{{ $ipAddress ?: 'Unknown' }}</td>
                                </tr>
                                <tr>
                                    <td style="padding:12px 14px;color:#6b7280;font-size:13px;">Device</td>
                                    <td style="padding:12px 14px;color:#111827;font-size:13px;text-align:right;">{{ $userAgent ?: 'Unknown browser' }}</td>
                                </tr>
                            </table>
                            <p style="margin:18px 0 0;font-size:14px;line-height:1.6;color:#6b7280;">If this was not you, reset your password immediately.</p>
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
