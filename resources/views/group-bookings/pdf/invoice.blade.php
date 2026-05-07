<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Групповой счёт {{ $group->group_ref }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 12px; color: #1e293b; background: #fff; }
        .page { padding: 40px 48px; }
        .header { display: table; width: 100%; margin-bottom: 32px; }
        .header-left { display: table-cell; vertical-align: top; width: 50%; }
        .header-right { display: table-cell; vertical-align: top; width: 50%; text-align: right; }
        .hotel-name { font-size: 22px; font-weight: bold; color: #1e293b; margin-bottom: 4px; }
        .hotel-info { font-size: 11px; color: #64748b; line-height: 1.6; }
        .invoice-title { font-size: 28px; font-weight: bold; color: #2563eb; letter-spacing: -0.5px; }
        .invoice-meta { font-size: 11px; color: #64748b; margin-top: 6px; line-height: 1.6; }
        .divider { border: none; border-top: 2px solid #e2e8f0; margin: 24px 0; }
        .section-label { font-size: 9px; font-weight: bold; text-transform: uppercase; letter-spacing: 0.08em; color: #94a3b8; margin-bottom: 6px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th { background: #f8fafc; font-size: 10px; font-weight: bold; text-transform: uppercase; letter-spacing: 0.06em; color: #64748b; padding: 10px 12px; text-align: left; border-bottom: 2px solid #e2e8f0; }
        th.right { text-align: right; }
        td { padding: 10px 12px; font-size: 12px; color: #334155; border-bottom: 1px solid #f1f5f9; }
        td.right { text-align: right; }
        td.bold { font-weight: bold; color: #1e293b; }
        tr:last-child td { border-bottom: none; }
        .totals-box { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 16px 20px; margin-top: 20px; }
        .total-row { display: table; width: 100%; padding: 4px 0; }
        .total-label { display: table-cell; font-size: 12px; color: #64748b; }
        .total-value { display: table-cell; text-align: right; font-size: 12px; font-weight: bold; color: #1e293b; }
        .total-grand .total-label, .total-grand .total-value { font-size: 15px; color: #1e293b; border-top: 2px solid #e2e8f0; padding-top: 8px; margin-top: 4px; }
        .balance-due .total-value { color: #dc2626; }
        .footer { margin-top: 40px; padding-top: 16px; border-top: 1px solid #e2e8f0; font-size: 10px; color: #94a3b8; text-align: center; }
    </style>
</head>
<body>
<div class="page">
    <div class="header">
        <div class="header-left">
            <div class="hotel-name">{{ $hotel['name'] ?? 'Отель' }}</div>
            <div class="hotel-info">
                @if(!empty($hotel['address'])){{ $hotel['address'] }}<br>@endif
                @if(!empty($hotel['phone'])){{ $hotel['phone'] }}<br>@endif
                @if(!empty($hotel['email'])){{ $hotel['email'] }}@endif
            </div>
        </div>
        <div class="header-right">
            <div class="invoice-title">СЧЁТ</div>
            <div class="invoice-meta">
                № {{ $group->group_ref }}<br>
                {{ now()->format('d.m.Y') }}<br>
                @if($group->name){{ $group->name }}<br>@endif
            </div>
        </div>
    </div>

    <hr class="divider">

    <div class="section-label">Бронирования в группе</div>
    <table>
        <thead>
            <tr>
                <th>Номер</th>
                <th>Гость</th>
                <th>Даты</th>
                <th>Ночей</th>
                <th class="right">Стоимость</th>
                <th class="right">Оплачено</th>
                <th class="right">Долг</th>
            </tr>
        </thead>
        <tbody>
            @foreach($group->bookings as $booking)
                @php $t = $totalsPerBooking[$booking->id]; @endphp
                <tr>
                    <td class="bold">{{ $booking->room?->number }}</td>
                    <td>{{ $booking->guest?->full_name ?? '—' }}</td>
                    <td>{{ $booking->check_in_date->format('d.m.Y') }} — {{ $booking->check_out_date->format('d.m.Y') }}</td>
                    <td>{{ $booking->check_in_date->diffInDays($booking->check_out_date) }}</td>
                    <td class="right">{{ number_format($t['grand_total'], 0, '.', ' ') }}</td>
                    <td class="right">{{ number_format($t['paid'], 0, '.', ' ') }}</td>
                    <td class="right bold">{{ $t['balance_due'] > 0 ? number_format($t['balance_due'], 0, '.', ' ') : '—' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="totals-box">
        <div class="total-row">
            <span class="total-label">Итого по номерам</span>
            <span class="total-value">{{ number_format($grandTotalAll, 0, '.', ' ') }} сум</span>
        </div>
        <div class="total-row">
            <span class="total-label">Оплачено</span>
            <span class="total-value" style="color:#16a34a">{{ number_format($paidAll, 0, '.', ' ') }} сум</span>
        </div>
        @if($balanceDueAll > 0)
        <div class="total-row balance-due">
            <span class="total-label" style="font-weight:bold;font-size:14px">К оплате</span>
            <span class="total-value" style="font-size:14px">{{ number_format($balanceDueAll, 0, '.', ' ') }} сум</span>
        </div>
        @endif
    </div>

    <div class="footer">
        {{ $hotel['name'] ?? '' }} · {{ now()->format('d.m.Y') }} · Групповой счёт {{ $group->group_ref }}
    </div>
</div>
</body>
</html>
