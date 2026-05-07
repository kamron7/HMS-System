<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Оставьте отзыв о пребывании</title>
</head>
<body style="margin:0;padding:0;background:#f8fafc;font-family:Arial,sans-serif;font-size:14px;color:#334155;">

<table width="100%" cellpadding="0" cellspacing="0" style="background:#f8fafc;padding:32px 0;">
    <tr>
        <td align="center">
            <table width="600" cellpadding="0" cellspacing="0" style="background:#ffffff;border-radius:12px;overflow:hidden;box-shadow:0 1px 4px rgba(0,0,0,.08);">

                <tr>
                    <td style="background:#1e3a5f;padding:28px 40px;text-align:center;">
                        <p style="margin:0;font-size:22px;font-weight:700;color:#ffffff;">{{ config('hotel.name', 'Отель') }}</p>
                        <p style="margin:6px 0 0;font-size:13px;color:#93c5fd;">Как вам понравилось?</p>
                    </td>
                </tr>

                <tr>
                    <td style="padding:36px 40px;">
                        <p style="margin:0 0 16px;">Уважаемый(-ая) <strong>{{ optional($booking->guest)->first_name }}</strong>,</p>
                        <p style="margin:0 0 24px;color:#64748b;">
                            Благодарим за выбор нашего отеля! Вы останавливались у нас
                            с {{ $booking->check_in_date->translatedFormat('d M') }}
                            по {{ $booking->check_out_date->translatedFormat('d M Y') }}.
                            Нам важно ваше мнение — уделите минуту и оставьте отзыв.
                        </p>

                        <table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:28px;">
                            <tr>
                                <td align="center">
                                    <a href="{{ $feedbackUrl }}"
                                       style="display:inline-block;background:#2563eb;color:#ffffff;text-decoration:none;font-weight:700;font-size:15px;padding:14px 36px;border-radius:8px;">
                                        ★ Оставить отзыв
                                    </a>
                                </td>
                            </tr>
                        </table>

                        <p style="margin:0;font-size:12px;color:#94a3b8;text-align:center;">
                            Ссылка действительна 7 дней и предназначена только для вас.
                        </p>
                    </td>
                </tr>

                <tr>
                    <td style="background:#f1f5f9;padding:20px 40px;text-align:center;font-size:12px;color:#94a3b8;">
                        {{ config('hotel.name') }} · {{ config('hotel.address', '') }}
                    </td>
                </tr>

            </table>
        </td>
    </tr>
</table>

</body>
</html>
