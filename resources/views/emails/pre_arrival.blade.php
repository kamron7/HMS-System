<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Напоминаем о вашем заезде</title>
</head>
<body style="margin:0;padding:0;background:#f1f5f9;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f1f5f9;">
    <tr><td align="center" style="padding:40px 20px;">
        <table role="presentation" width="560" cellpadding="0" cellspacing="0" style="background:#fff;border-radius:16px;overflow:hidden;box-shadow:0 1px 3px rgba(0,0,0,.1);">
            {{-- Header --}}
            <tr>
                <td style="background:linear-gradient(135deg,#1d4ed8,#3b82f6);padding:32px 40px;text-align:center;">
                    <h1 style="color:#fff;font-size:22px;font-weight:800;margin:0 0 4px;">{{ config('hotel.name') }}</h1>
                    <p style="color:#bfdbfe;font-size:13px;margin:0;">Ждём вас завтра!</p>
                </td>
            </tr>
            {{-- Body --}}
            <tr>
                <td style="padding:32px 40px;">
                    <p style="font-size:15px;color:#334155;margin:0 0 24px;line-height:1.6;">
                        Уважаемый(ая) <strong>{{ $booking->guest->fullName }}</strong>,<br><br>
                        Напоминаем, что завтра ваш заезд в наш отель.
                    </p>

                    {{-- Details card --}}
                    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f8fafc;border-radius:12px;overflow:hidden;margin-bottom:24px;">
                        <tr>
                            <td style="padding:20px;">
                                <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                                    <tr>
                                        <td style="font-size:13px;color:#64748b;padding:4px 0;">Номер</td>
                                        <td style="font-size:13px;font-weight:600;color:#1e293b;text-align:right;padding:4px 0;">{{ $booking->room->number }} ({{ $booking->room->roomType->name }})</td>
                                    </tr>
                                    <tr>
                                        <td style="font-size:13px;color:#64748b;padding:4px 0;border-top:1px solid #e2e8f0;">Заезд</td>
                                        <td style="font-size:13px;font-weight:600;color:#1e293b;text-align:right;padding:4px 0;border-top:1px solid #e2e8f0;">{{ $booking->check_in_date->translatedFormat('d F Y') }}</td>
                                    </tr>
                                    <tr>
                                        <td style="font-size:13px;color:#64748b;padding:4px 0;border-top:1px solid #e2e8f0;">Выезд</td>
                                        <td style="font-size:13px;font-weight:600;color:#1e293b;text-align:right;padding:4px 0;border-top:1px solid #e2e8f0;">{{ $booking->check_out_date->translatedFormat('d F Y') }}</td>
                                    </tr>
                                    <tr>
                                        <td style="font-size:13px;color:#64748b;padding:4px 0;border-top:1px solid #e2e8f0;">Код бронирования</td>
                                        <td style="font-size:13px;font-weight:600;color:#2563eb;text-align:right;padding:4px 0;border-top:1px solid #e2e8f0;">{{ $booking->booking_ref }}</td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    </table>

                    @if($portalUrl)
                    <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                        <tr>
                            <td align="center">
                                <a href="{{ $portalUrl }}" style="display:inline-block;background:#2563eb;color:#fff;padding:14px 32px;border-radius:12px;text-decoration:none;font-size:14px;font-weight:600;">
                                    Открыть портал номера
                                </a>
                            </td>
                        </tr>
                    </table>
                    @endif

                    <p style="font-size:13px;color:#94a3b8;margin:24px 0 0;text-align:center;line-height:1.6;">
                        Время заезда: с 14:00 · Время выезда: до 12:00<br>
                        @if(config('hotel.phone'))<strong>Тел:</strong> {{ config('hotel.phone') }}@endif
                    </p>
                </td>
            </tr>
            {{-- Footer --}}
            <tr>
                <td style="background:#f8fafc;padding:20px 40px;text-align:center;font-size:11px;color:#94a3b8;">
                    {{ config('hotel.name') }} &bull; {{ config('hotel.address') }}
                </td>
            </tr>
        </table>
    </td></tr>
</table>
</body>
</html>
