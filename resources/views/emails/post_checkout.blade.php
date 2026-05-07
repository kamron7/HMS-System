<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Спасибо за проживание</title>
</head>
<body style="margin:0;padding:0;background:#f1f5f9;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f1f5f9;">
    <tr><td align="center" style="padding:40px 20px;">
        <table role="presentation" width="560" cellpadding="0" cellspacing="0" style="background:#fff;border-radius:16px;overflow:hidden;box-shadow:0 1px 3px rgba(0,0,0,.1);">
            {{-- Header --}}
            <tr>
                <td style="background:linear-gradient(135deg,#059669,#10b981);padding:32px 40px;text-align:center;">
                    <h1 style="color:#fff;font-size:22px;font-weight:800;margin:0 0 4px;">Спасибо, что выбрали нас!</h1>
                    <p style="color:#a7f3d0;font-size:13px;margin:0;">{{ config('hotel.name') }}</p>
                </td>
            </tr>
            {{-- Body --}}
            <tr>
                <td style="padding:32px 40px;">
                    <p style="font-size:15px;color:#334155;margin:0 0 24px;line-height:1.6;">
                        Уважаемый(ая) <strong>{{ $booking->guest->fullName }}</strong>,<br><br>
                        Благодарим вас за проживание в нашем отеле! Надеемся, вам всё понравилось.
                    </p>

                    {{-- Details card --}}
                    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f8fafc;border-radius:12px;overflow:hidden;margin-bottom:24px;">
                        <tr>
                            <td style="padding:20px;">
                                <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                                    <tr>
                                        <td style="font-size:13px;color:#64748b;padding:4px 0;">Номер</td>
                                        <td style="font-size:13px;font-weight:600;color:#1e293b;text-align:right;padding:4px 0;">{{ $booking->room->number }}</td>
                                    </tr>
                                    <tr>
                                        <td style="font-size:13px;color:#64748b;padding:4px 0;border-top:1px solid #e2e8f0;">Период</td>
                                        <td style="font-size:13px;font-weight:600;color:#1e293b;text-align:right;padding:4px 0;border-top:1px solid #e2e8f0;">{{ $booking->check_in_date->format('d.m') }} — {{ $booking->check_out_date->format('d.m.Y') }}</td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    </table>

                    @if($feedbackUrl)
                    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:24px;">
                        <tr>
                            <td align="center">
                                <a href="{{ $feedbackUrl }}" style="display:inline-block;background:#059669;color:#fff;padding:14px 32px;border-radius:12px;text-decoration:none;font-size:14px;font-weight:600;">
                                    Оставить отзыв
                                </a>
                            </td>
                        </tr>
                    </table>
                    @endif

                    <p style="font-size:13px;color:#94a3b8;margin:24px 0 0;text-align:center;line-height:1.6;">
                        Будем рады видеть вас снова!<br>
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
