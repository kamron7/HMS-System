<!DOCTYPE html>
<html lang="ru">
<head>
<meta charset="UTF-8">
<style>
body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #1e293b; margin: 0; padding: 20px; }
h1 { font-size: 16px; margin: 0 0 4px; }
.sub { color: #64748b; font-size: 10px; margin-bottom: 16px; }
table { width: 100%; border-collapse: collapse; margin-top: 8px; }
th { background: #f1f5f9; text-align: left; padding: 6px 8px; font-size: 9px; text-transform: uppercase; letter-spacing: .05em; color: #64748b; border-bottom: 1px solid #e2e8f0; }
td { padding: 5px 8px; border-bottom: 1px solid #f1f5f9; }
.right { text-align: right; }
.debt { color: #dc2626; font-weight: bold; }
</style>
</head>
<body>
<h1>Неоплаченные бронирования</h1>
<p class="sub">Сформировано: {{ now()->format('d.m.Y H:i') }}</p>

<table>
    <thead>
        <tr>
            <th>#</th>
            <th>Гость</th>
            <th>Номер</th>
            <th>Заезд</th>
            <th>Выезд</th>
            <th class="right">Сумма</th>
            <th class="right">Оплачено</th>
            <th class="right">Долг</th>
        </tr>
    </thead>
    <tbody>
        @foreach($bookings as $booking)
        @php
            $paid = (float) $booking->payments->where('type', \App\Enums\PaymentType::Prepayment->value)->sum('amount');
            $debt = max(0, (float) $booking->total_price - $paid);
        @endphp
        <tr>
            <td>#{{ $booking->id }}</td>
            <td>{{ $booking->guest->fullName }}</td>
            <td>{{ $booking->room->number }}</td>
            <td>{{ $booking->check_in_date->format('d.m.Y') }}</td>
            <td>{{ $booking->check_out_date->format('d.m.Y') }}</td>
            <td class="right">{{ number_format($booking->total_price, 0, '.', ' ') }}</td>
            <td class="right">{{ number_format($paid, 0, '.', ' ') }}</td>
            <td class="right debt">{{ number_format($debt, 0, '.', ' ') }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
</body>
</html>
