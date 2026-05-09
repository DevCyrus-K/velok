@php
    $appName = config('app.name', 'Kwikshift Admin Panel');
    $logoUrl = asset('images/logo-dark.png');
    $codeType = $type ?? 'two_factor';
    $ttl = (int) ($ttlMinutes ?? 10);

    $headline = match ($codeType) {
        'email_verification' => 'Verify your email',
        'password_reset' => 'Reset your password',
        default => 'Verify your login',
    };

    $intro = match ($codeType) {
        'email_verification' => 'Use this code to verify your email and finish securing your account.',
        'password_reset' => 'Use this code to reset your password. If you did not request a reset, you can safely ignore this email.',
        default => 'Your one-time verification code is:',
    };
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
                            <p style="margin:0 0 10px;color:#22b956;font-size:13px;font-weight:700;text-transform:uppercase;letter-spacing:.04em;">Security verification</p>
                            <h1 style="font-size:24px;line-height:1.3;margin:0;color:#111827;">{{ $headline }}</h1>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:8px 30px 20px;font-size:15px;line-height:1.65;color:#4b5563;">
                            <p style="margin:0;">{{ $intro }}</p>
                        </td>
                    </tr>
                    <tr>
                        <td align="center" style="padding:4px 30px 28px;">
                            <table role="presentation" cellspacing="0" cellpadding="0">
                                <tr>
                                    @foreach($digits as $digit)
                                        <td style="padding:0 4px;">
                                            <span style="display:inline-block;width:44px;height:54px;line-height:54px;text-align:center;border:1px solid #d7dde5;border-radius:8px;background:#f8fafc;color:#111827;font-size:28px;font-weight:800;letter-spacing:0;">{{ $digit }}</span>
                                        </td>
                                    @endforeach
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:0 30px 30px;font-size:14px;line-height:1.6;color:#6b7280;">
                            <p style="margin:0 0 10px;">This code expires in {{ $ttl }} minutes.</p>
                            <p style="margin:0 0 10px;">If you did not request this, please secure your account immediately.</p>
                            <p style="margin:0;">Never share this code with anyone.</p>
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
