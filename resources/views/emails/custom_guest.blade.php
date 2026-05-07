<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>{{ $mailSubject }}</title>
</head>
<body style="margin:0;padding:0;background:#f8fafc;font-family:Arial,sans-serif;font-size:14px;color:#334155;">

<table width="100%" cellpadding="0" cellspacing="0" style="background:#f8fafc;padding:32px 0;">
    <tr>
        <td align="center">
            <table width="600" cellpadding="0" cellspacing="0" style="background:#ffffff;border-radius:12px;overflow:hidden;box-shadow:0 1px 4px rgba(0,0,0,.08);">

                <tr>
                    <td style="background:#1e3a5f;padding:28px 40px;text-align:center;">
                        <p style="margin:0;font-size:22px;font-weight:700;color:#ffffff;">{{ config('hotel.name') }}</p>
                    </td>
                </tr>

                <tr>
                    <td style="padding:36px 40px;">
                        <p style="margin:0 0 20px;">Уважаемый(-ая) <strong>{{ $guest->fullName }}</strong>,</p>
                        <div style="line-height:1.7;color:#334155;">{!! $body !!}</div>
                    </td>
                </tr>

                <tr>
                    <td style="background:#f1f5f9;padding:20px 40px;text-align:center;font-size:12px;color:#94a3b8;">
                        {{ config('hotel.name') }} · {{ config('hotel.address') }}<br>
                        {{ config('hotel.phone') }} · {{ config('hotel.email') }}
                    </td>
                </tr>

            </table>
        </td>
    </tr>
</table>

</body>
</html>
