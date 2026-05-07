<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Подтверждение запроса на бронирование</title>
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
                        <p style="margin:6px 0 0;font-size:13px;color:#93c5fd;">Подтверждение запроса на бронирование</p>
                    </td>
                </tr>

                {{-- Body --}}
                <tr>
                    <td style="padding:36px 40px;">
                        <p style="margin:0 0 16px;">Уважаемый(-ая) <strong>{{ $booking->inquiry->first_name ?? '' }} {{ $booking->inquiry->last_name ?? '' }}</strong>,</p>
                        <p style="margin:0 0 24px;color:#64748b;">Мы получили ваш запрос на бронирование. Наш менеджер свяжется с вами для подтверждения.</p>

                        {{-- Booking details --}}
                        <table width="100%" cellpadding="0" cellspacing="0" style="background:#f1f5f9;border-radius:8px;padding:20px;margin-bottom:24px;">
                            <tr>
                                <td style="padding:6px 0;color:#64748b;width:140px;">Код бронирования</td>
                                <td style="padding:6px 0;font-weight:700;color:#1e293b;font-size:18px;letter-spacing:2px;">{{ $booking->booking_ref }}</td>
                            </tr>
                            <tr>
                                <td style="padding:6px 0;color:#64748b;">Тип номера</td>
                                <td style="padding:6px 0;color:#1e293b;">{{ optional(optional($booking->room)->roomType)->name ?? '—' }}</td>
                            </tr>
                            <tr>
                                <td style="padding:6px 0;color:#64748b;">Дата заезда</td>
                                <td style="padding:6px 0;color:#1e293b;">{{ $booking->check_in_date->translatedFormat('d F Y') }}</td>
                            </tr>
                            <tr>
                                <td style="padding:6px 0;color:#64748b;">Дата выезда</td>
                                <td style="padding:6px 0;color:#1e293b;">{{ $booking->check_out_date->translatedFormat('d F Y') }}</td>
                            </tr>
                            <tr>
                                <td style="padding:6px 0;color:#64748b;">Гостей</td>
                                <td style="padding:6px 0;color:#1e293b;">{{ $booking->adults }} взр.{{ $booking->children > 0 ? ', ' . $booking->children . ' дет.' : '' }}</td>
                            </tr>
                            <tr>
                                <td style="padding:6px 0;color:#64748b;">Ориентировочная стоимость</td>
                                <td style="padding:6px 0;font-weight:700;color:#1e293b;">{{ number_format((float)$booking->total_price, 0, '.', ' ') }} сум</td>
                            </tr>
                        </table>

                        {{-- Room portal block (only when room & qr_token are set) --}}
                        @if(optional($booking->room)->qr_token)
                        @php $portalUrl = route('room-portal.show', $booking->room->qr_token); @endphp
                        <table width="100%" cellpadding="0" cellspacing="0"
                               style="background:#0f172a;border-radius:12px;padding:24px 20px;margin-bottom:24px;text-align:center;">
                            <tr>
                                <td>
                                    <p style="margin:0 0 4px;font-size:10px;font-weight:700;color:#f59e0b;letter-spacing:3px;text-transform:uppercase;">{{ config('hotel.name') }}</p>
                                    <p style="margin:0 0 16px;font-size:13px;color:#94a3b8;">Портал гостя — номер {{ $booking->room->number }}</p>

                                    {{-- QR code --}}
                                    <img src="{{ route('room-portal.qr-image', $booking->room->qr_token) }}"
                                         alt="QR-код портала"
                                         width="140" height="140"
                                         style="display:block;margin:0 auto 16px;border-radius:10px;background:#fff;padding:8px;">

                                    <p style="margin:0 0 6px;font-size:12px;color:#64748b;">Или откройте по ссылке:</p>
                                    <a href="{{ $portalUrl }}"
                                       style="display:inline-block;background:#2563eb;color:#ffffff;padding:12px 28px;border-radius:10px;text-decoration:none;font-size:13px;font-weight:700;margin-bottom:16px;">
                                        Открыть портал номера
                                    </a>

                                    <p style="margin:0;font-size:11px;color:#475569;line-height:1.6;">
                                        Для входа потребуется код бронирования:
                                        <span style="font-family:monospace;font-weight:700;color:#e2e8f0;font-size:15px;letter-spacing:2px;">{{ $booking->booking_ref }}</span>
                                    </p>
                                </td>
                            </tr>
                        </table>
                        @endif

                        <p style="margin:0 0 8px;color:#64748b;font-size:13px;">По всем вопросам обращайтесь:</p>
                        <p style="margin:0;font-size:13px;"><strong>{{ config('hotel.phone', '') }}</strong> · {{ config('hotel.email', '') }}</p>
                    </td>
                </tr>

                {{-- Footer --}}
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
