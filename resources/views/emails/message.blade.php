<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $messageRecord->subject }}</title>
</head>
<body style="margin:0;padding:0;background:#f6f7fb;color:#1f2937;font-family:Arial,Helvetica,sans-serif;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background:#f6f7fb;padding:24px 0;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width:640px;background:#ffffff;border:1px solid #e5e7eb;border-radius:8px;overflow:hidden;">
                    <tr>
                        <td style="padding:24px 28px;border-bottom:1px solid #e5e7eb;">
                            <h1 style="font-size:20px;line-height:1.35;margin:0;color:#111827;">{{ $messageRecord->subject }}</h1>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:28px;font-size:15px;line-height:1.65;color:#1f2937;white-space:pre-line;">
                            {{ $body }}
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    @if($trackingToken)
        <img src="{{ route('email.track.open', $trackingToken) }}" width="1" height="1" style="display:none;border:none" alt="">
    @endif
</body>
</html>
