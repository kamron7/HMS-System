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

                {{-- Header --}}
                <tr>
                    <td style="background:#1e3a5f;padding:28px 40px;text-align:center;">
                        <p style="margin:0;font-size:22px;font-weight:700;color:#ffffff;">{{ config('hotel.name', 'Отель') }}</p>
                        <p style="margin:6px 0 0;font-size:13px;color:#93c5fd;">Сообщение для сотрудника</p>
                    </td>
                </tr>

                {{-- Body --}}
                <tr>
                    <td style="padding:32px 40px;">
                        <p style="margin:0 0 16px;font-size:15px;font-weight:600;color:#1e293b;">Здравствуйте, {{ $user->name }}!</p>
                        <div style="line-height:1.7;color:#475569;">{!! $body !!}</div>
                    </td>
                </tr>

                {{-- Footer --}}
                <tr>
                    <td style="padding:20px 40px;border-top:1px solid #e2e8f0;text-align:center;">
                        <p style="margin:0;font-size:12px;color:#94a3b8;">{{ config('hotel.name', 'Отель') }} · Это письмо отправлено администрацией</p>
                    </td>
                </tr>

            </table>
        </td>
    </tr>
</table>
</body>
</html>
