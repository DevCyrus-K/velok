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
@endphp
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="x-apple-disable-message-reformatting">
    <meta name="color-scheme" content="light dark">
    <meta name="supported-color-schemes" content="light dark">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <title>{{ $emailTitle ?? 'Message' }}</title>
    <style>
        :root {
            color-scheme: light dark;
            supported-color-schemes: light dark;
        }

        body, table, td, p, a {
            -webkit-text-size-adjust: 100%;
            -ms-text-size-adjust: 100%;
        }

        table, td {
            mso-table-lspace: 0pt;
            mso-table-rspace: 0pt;
        }

        table {
            border-collapse: collapse !important;
        }

        img {
            border: 0;
            outline: none;
            -ms-interpolation-mode: bicubic;
            display: block;
        }

        body {
            margin: 0 !important;
            padding: 0 !important;
            width: 100% !important;
            height: 100% !important;
            background-color: #f5f7fb;
        }

        .email-bg {
            background-color: #f5f7fb !important;
        }

        .hero-bg {
            background-color: #df1119 !important;
        }

        .surface-bg {
            background-color: #f5f7fb !important;
        }

        .footer-bg {
            background-color: #04223e !important;
        }

        .footer-top {
            border-top: 1px solid #e6edf5 !important;
        }

        .divider {
            background-color: #e6edf5 !important;
        }

        .text-light, .text-light a {
            color: #ffffff !important;
        }

        .text-light-soft {
            color: #fff0f1 !important;
        }

        .text-heading, .text-heading a {
            color: #04223e !important;
        }

        .text-body, .text-body a {
            color: #5c6b7a !important;
        }

        .text-dark, .text-dark a {
            color: #04223e !important;
        }

        .text-footer-sep {
            color: #91a2b3 !important;
        }

        .safe-note {
            background-color: #fff4e7 !important;
            border-left: 4px solid #df1119 !important;
        }

        .btn {
            display: inline-block;
            padding: 12px 24px;
            background-color: #16a34a;
            color: #ffffff;
            text-decoration: none;
            border: 0;
            border-radius: 0;
            font-weight: 700;
            font-size: 16px;
            line-height: 1;
        }

        @media (prefers-color-scheme: dark) {
            body, .email-bg {
                background-color: #11161d !important;
            }

            .hero-bg {
                background-color: #b60d15 !important;
            }

            .surface-bg {
                background-color: #18212b !important;
            }

            .footer-bg {
                background-color: #0d141b !important;
            }

            .footer-top {
                border-top-color: #32414f !important;
            }

            .divider {
                background-color: #32414f !important;
            }

            .text-heading, .text-heading a, .text-dark, .text-dark a {
                color: #f5f7fa !important;
            }

            .text-body, .text-body a {
                color: #d1d7df !important;
            }

            .text-light-soft {
                color: #ffd9dc !important;
            }

            .text-footer-sep {
                color: #91a2b3 !important;
            }

            .safe-note {
                background-color: #231f1a !important;
                border-left-color: #ff6a71 !important;
            }
        }

        [data-ogsc] .email-bg, [data-ogsb] .email-bg {
            background-color: #11161d !important;
        }

        [data-ogsc] .hero-bg, [data-ogsb] .hero-bg {
            background-color: #b60d15 !important;
        }

        [data-ogsc] .surface-bg, [data-ogsb] .surface-bg {
            background-color: #18212b !important;
        }

        [data-ogsc] .footer-bg, [data-ogsb] .footer-bg {
            background-color: #0d141b !important;
        }

        [data-ogsc] .footer-top, [data-ogsb] .footer-top {
            border-top-color: #32414f !important;
        }

        [data-ogsc] .divider, [data-ogsb] .divider {
            background-color: #32414f !important;
        }

        [data-ogsc] .text-heading, [data-ogsb] .text-heading,
        [data-ogsc] .text-heading a, [data-ogsb] .text-heading a,
        [data-ogsc] .text-dark, [data-ogsb] .text-dark,
        [data-ogsc] .text-dark a, [data-ogsb] .text-dark a {
            color: #f5f7fa !important;
        }

        [data-ogsc] .text-body, [data-ogsb] .text-body,
        [data-ogsc] .text-body a, [data-ogsb] .text-body a {
            color: #d1d7df !important;
        }

        [data-ogsc] .text-light-soft, [data-ogsb] .text-light-soft {
            color: #ffd9dc !important;
        }

        [data-ogsc] .text-footer-sep, [data-ogsb] .text-footer-sep {
            color: #91a2b3 !important;
        }

        [data-ogsc] .safe-note, [data-ogsb] .safe-note {
            background-color: #231f1a !important;
            border-left-color: #ff6a71 !important;
        }

        @media screen and (max-width: 640px) {
            .container {
                width: 100% !important;
            }

            .mobile-pad {
                padding-right: 20px !important;
                padding-left: 20px !important;
            }

            .mobile-center {
                text-align: center !important;
            }

            .mobile-block {
                display: block !important;
                width: 100% !important;
            }
        }
    </style>
</head>
<body class="email-bg" style="margin:0; padding:0; background-color:#f5f7fb;">
    <div style="display:none; max-height:0; overflow:hidden; opacity:0; mso-hide:all;">
        {{ $emailPreview ?? 'Message from ' . $companyName }}
    </div>

    <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" class="email-bg" bgcolor="#f5f7fb">
        <tr>
            <td align="center" style="padding:24px 12px;">
                <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" class="container" style="max-width:640px;">
                    <tr>
                        <td class="hero-bg mobile-pad" bgcolor="#df1119" style="padding:32px 32px 40px 32px;">
                            <table role="presentation" border="0" cellpadding="0" cellspacing="0" style="margin:0 0 16px 0;">
                                <tr>
                                    <td valign="middle" style="padding-right:12px;">
                                        @if($companyLogoUrl !== '')
                                            <img src="{{ $companyLogoUrl }}" alt="{{ $companyName }} logo" width="44" height="40" style="width:44px; height:40px; border-radius: 0;">
                                        @endif
                                    </td>
                                    <td valign="middle" class="text-light" style="font-family:Arial, Helvetica, sans-serif; font-size:20px; line-height:24px; font-weight:700; color:#ffffff;">
                                        {{ $companyName }}
                                    </td>
                                </tr>
                            </table>
                            <h1 class="text-light" style="margin:0 0 12px 0; font-family:Arial, Helvetica, sans-serif; font-size:34px; line-height:42px; font-weight:700; color:#ffffff;">
                                {{ $emailHeading }}
                            </h1>
                            <p class="text-light-soft" style="margin:0; font-family:Arial, Helvetica, sans-serif; font-size:16px; line-height:26px; color:#fff0f1;">
                                {{ $emailSubheading ?? '' }}
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <td class="surface-bg mobile-pad" bgcolor="#f5f7fb" style="padding:36px 32px 32px 32px;">
                            {{ $slot }}
                        </td>
                    </tr>
                    <tr>
                        <td class="surface-bg footer-top mobile-pad" bgcolor="#f5f7fb" style="padding:34px 32px 20px 32px; border-top:1px solid #e6edf5;">
                            <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">
                                <tr>
                                    <td class="mobile-center text-dark" style="padding-bottom:10px; font-family:Arial, Helvetica, sans-serif; font-size:14px; line-height:24px; color:#04223e;">
                                        &copy; {{ now()->year }} {{ $companyName }}. All rights reserved.
                                    </td>
                                </tr>
                                @if($companyAddress !== '')
                                    <tr>
                                        <td class="mobile-center text-dark" style="padding-bottom:10px; font-family:Arial, Helvetica, sans-serif; font-size:14px; line-height:24px; color:#04223e;">
                                            {{ $companyAddress }}
                                        </td>
                                    </tr>
                                @endif
                                <tr>
                                    <td class="mobile-center text-body" style="padding-bottom:16px; font-family:Arial, Helvetica, sans-serif; font-size:14px; line-height:24px; color:#5c6b7a;">
                                        @if($companyPhone !== '')
                                            <a href="tel:{{ preg_replace('/[^0-9+]/', '', $companyPhone) }}" style="color:#df1119; text-decoration:none;">{{ $companyPhone }}</a>
                                        @endif
                                        @if($companyPhone !== '' && $companyEmail !== '')
                                            <span style="color:#999999;"> | </span>
                                        @endif
                                        @if($companyEmail !== '')
                                            <a href="mailto:{{ $companyEmail }}" style="color:#df1119; text-decoration:none;">{{ $companyEmail }}</a>
                                        @endif
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td class="surface-bg" bgcolor="#f5f7fb" style="padding:0;">
                            <div class="divider" style="width:100%; height:1px; line-height:1px; font-size:1px; background-color:#e6edf5;">&nbsp;</div>
                        </td>
                    </tr>
                    <tr>
                        <td class="footer-bg mobile-pad" bgcolor="#04223e" style="padding:20px 32px;">
                            <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">
                                <tr>
                                    <td class="mobile-center text-light" style="font-family:Arial, Helvetica, sans-serif; font-size:13px; line-height:22px; color:#ffffff;">
                                        All communication from {{ $companyName }} is handled with care and security.
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
