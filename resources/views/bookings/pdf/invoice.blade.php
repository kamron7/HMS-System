<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Счёт {{ $booking->invoice_number }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 12px; color: #1e293b; background: #fff; }
        .page { padding: 40px 48px; }

        /* Header */
        .header { display: table; width: 100%; margin-bottom: 32px; }
        .header-left { display: table-cell; vertical-align: top; width: 50%; }
        .header-right { display: table-cell; vertical-align: top; width: 50%; text-align: right; }
        .hotel-name { font-size: 22px; font-weight: bold; color: #1e293b; margin-bottom: 4px; }
        .hotel-info { font-size: 11px; color: #64748b; line-height: 1.6; }
        .invoice-title { font-size: 28px; font-weight: bold; color: #2563eb; letter-spacing: -0.5px; }
        .invoice-meta { font-size: 11px; color: #64748b; margin-top: 6px; line-height: 1.6; }
        .invoice-number { font-size: 13px; font-weight: bold; color: #1e293b; }

        /* Divider */
        .divider { border: none; border-top: 2px solid #e2e8f0; margin: 24px 0; }

        /* Guest + Booking Info */
        .info-row { display: table; width: 100%; margin-bottom: 24px; }
        .info-col { display: table-cell; vertical-align: top; width: 50%; }
        .info-col:last-child { text-align: right; }
        .section-label { font-size: 9px; font-weight: bold; text-transform: uppercase; letter-spacing: 0.08em; color: #94a3b8; margin-bottom: 6px; }
        .info-name { font-size: 14px; font-weight: bold; color: #1e293b; margin-bottom: 3px; }
        .info-detail { font-size: 11px; color: #64748b; line-height: 1.6; }

        /* Tables */
        table { width: 100%; border-collapse: collapse; }
        th { background: #f8fafc; font-size: 10px; font-weight: bold; text-transform: uppercase; letter-spacing: 0.06em; color: #64748b; padding: 10px 12px; text-align: left; border-bottom: 2px solid #e2e8f0; }
        th.right { text-align: right; }
        td { padding: 10px 12px; font-size: 12px; color: #334155; border-bottom: 1px solid #f1f5f9; }
        td.right { text-align: right; }
        td.bold { font-weight: bold; color: #1e293b; }
        tr:last-child td { border-bottom: none; }

        /* Section headers */
        .section-title { font-size: 11px; font-weight: bold; text-transform: uppercase; letter-spacing: 0.06em; color: #475569; margin: 20px 0 8px; padding-bottom: 4px; border-bottom: 1px solid #e2e8f0; }

        /* Totals */
        .totals-table { width: 300px; margin-left: auto; margin-top: 16px; }
        .totals-table td { padding: 7px 12px; border-bottom: 1px solid #f1f5f9; }
        .totals-table tr:last-child td { border-bottom: none; }
        .totals-row-grand { background: #f8fafc; font-weight: bold; font-size: 13px; }
        .totals-row-balance { background: #fef2f2; }
        .totals-row-balance td { color: #dc2626; font-weight: bold; font-size: 13px; }
        .totals-row-paid td { color: #16a34a; }
        .totals-row-complete { background: #f0fdf4; }
        .totals-row-complete td { color: #16a34a; font-weight: bold; }

        /* Payments */
        .payments-section { margin-top: 24px; }

        /* Footer */
        .footer { margin-top: 40px; padding-top: 20px; border-top: 1px solid #e2e8f0; text-align: center; font-size: 10px; color: #94a3b8; line-height: 1.6; }

        /* Badge */
        .badge { display: inline-block; padding: 2px 8px; border-radius: 4px; font-size: 10px; font-weight: bold; }
        .badge-blue { background: #dbeafe; color: #1d4ed8; }
        .badge-green { background: #dcfce7; color: #16a34a; }
        .badge-amber { background: #fef9c3; color: #92400e; }
    </style>
</head>
<body>
<div class="page">

    {{-- Header --}}
    <div class="header">
        <div class="header-left">
            <div class="hotel-name">{{ $hotel['name'] }}</div>
            <div class="hotel-info">
                {{ $hotel['address'] }}<br>
                Тел: {{ $hotel['phone'] }}<br>
                {{ $hotel['email'] }}
            </div>
        </div>
        <div class="header-right">
            <div class="invoice-title">СЧЁТ</div>
            <div class="invoice-meta">
                <span class="invoice-number">{{ $booking->invoice_number }}</span><br>
                Дата: {{ now()->format('d.m.Y') }}<br>
                Бронирование #{{ $booking->id }}
            </div>
        </div>
    </div>

    <hr class="divider">

    {{-- Guest & Booking Info --}}
    <div class="info-row">
        <div class="info-col">
            <div class="section-label">Гость</div>
            <div class="info-name">{{ $booking->guest->fullName }}</div>
            <div class="info-detail">
                @if($booking->guest->phone){{ $booking->guest->phone }}@endif
                @if($booking->guest->email)<br>{{ $booking->guest->email }}@endif
            </div>
        </div>
        <div class="info-col">
            <div class="section-label">Детали проживания</div>
            <div class="info-detail">
                Номер: <strong>{{ $booking->room->number }}</strong> ({{ $booking->room->roomType->name }})<br>
                Заезд: <strong>{{ $booking->check_in_date->format('d.m.Y') }}</strong><br>
                Выезд: <strong>{{ $booking->check_out_date->format('d.m.Y') }}</strong><br>
                Ночей: <strong>{{ $booking->check_in_date->diffInDays($booking->check_out_date) }}</strong>
                &nbsp; Гостей: <strong>{{ $booking->adults }}
                @if($booking->children > 0)+ {{ $booking->children }} дет.@endif</strong>
            </div>
        </div>
    </div>

    {{-- Line items --}}
    <div class="section-title">Услуги проживания</div>
    <table>
        <thead>
            <tr>
                <th>Описание</th>
                <th class="right">Кол-во</th>
                <th class="right">Цена за ед.</th>
                <th class="right">Сумма</th>
            </tr>
        </thead>
        <tbody>
            @php
                $nights = $booking->check_in_date->diffInDays($booking->check_out_date);
                $pricePerNight = $nights > 0 ? $totals['room_cost'] / $nights : $totals['room_cost'];
            @endphp
            <tr>
                <td class="bold">Проживание — номер {{ $booking->room->number }} ({{ $booking->room->roomType->name }})</td>
                <td class="right">{{ $nights }} ноч.</td>
                <td class="right">{{ number_format($pricePerNight, 0, '.', ' ') }} сум</td>
                <td class="right bold">{{ number_format($totals['room_cost'], 0, '.', ' ') }} сум</td>
            </tr>
        </tbody>
    </table>

    @if($booking->charges->count() > 0)
    <div class="section-title">Дополнительные услуги</div>
    <table>
        <thead>
            <tr>
                <th>Описание</th>
                <th>Категория</th>
                <th class="right">Дата</th>
                <th class="right">Сумма</th>
            </tr>
        </thead>
        <tbody>
            @php
                $chargeCategories = \App\Models\BookingCharge::categories();
            @endphp
            @foreach($booking->charges as $charge)
            <tr>
                <td>{{ $charge->description }}</td>
                <td><span class="badge badge-blue">{{ $chargeCategories[$charge->category] ?? $charge->category }}</span></td>
                <td class="right">{{ $charge->created_at->format('d.m.Y') }}</td>
                <td class="right bold">{{ number_format($charge->amount, 0, '.', ' ') }} сум</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    {{-- Totals --}}
    <table class="totals-table">
        <tbody>
            @if($totals['charges'] > 0)
            <tr>
                <td>Проживание</td>
                <td class="right">{{ number_format($totals['room_cost'], 0, '.', ' ') }} сум</td>
            </tr>
            <tr>
                <td>Доп. услуги</td>
                <td class="right">{{ number_format($totals['charges'], 0, '.', ' ') }} сум</td>
            </tr>
            @endif
            <tr class="totals-row-grand">
                <td>ИТОГО</td>
                <td class="right">{{ number_format($totals['grand_total'], 0, '.', ' ') }} сум</td>
            </tr>
            @if($totals['paid'] > 0)
            <tr class="totals-row-paid">
                <td>Оплачено</td>
                <td class="right">−{{ number_format($totals['paid'], 0, '.', ' ') }} сум</td>
            </tr>
            @endif
            @if($totals['deposit'] > 0)
            <tr>
                <td style="color:#92400e;">Залог (возвратный)</td>
                <td class="right" style="color:#92400e;">{{ number_format($totals['deposit'], 0, '.', ' ') }} сум</td>
            </tr>
            @endif
            @if($totals['balance_due'] > 0)
            <tr class="totals-row-balance">
                <td>К ОПЛАТЕ</td>
                <td class="right">{{ number_format($totals['balance_due'], 0, '.', ' ') }} сум</td>
            </tr>
            @else
            <tr class="totals-row-complete">
                <td colspan="2" style="text-align:center;">Полностью оплачено</td>
            </tr>
            @endif
        </tbody>
    </table>

    {{-- Payments --}}
    @if($booking->payments->count() > 0)
    <div class="payments-section">
        <div class="section-title">История платежей</div>
        @php
            $paymentMethods = ['cash' => 'Наличные', 'card' => 'Карта', 'transfer' => 'Перевод', 'other' => 'Другое'];
        @endphp
        <table>
            <thead>
                <tr>
                    <th>Дата</th>
                    <th>Тип</th>
                    <th>Способ</th>
                    <th class="right">Сумма</th>
                </tr>
            </thead>
            <tbody>
                @foreach($booking->payments as $payment)
                <tr>
                    <td>{{ $payment->paid_at->format('d.m.Y') }}</td>
                    <td>
                        @if($payment->type === \App\Enums\PaymentType::Deposit)
                            <span class="badge badge-amber">Залог</span>
                        @else
                            <span class="badge badge-green">Предоплата</span>
                        @endif
                    </td>
                    <td>{{ $paymentMethods[$payment->method] ?? $payment->method }}</td>
                    <td class="right bold">{{ number_format($payment->amount, 0, '.', ' ') }} сум</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    {{-- Verification info --}}
    <div style="margin-top:24px;padding:12px 16px;background:#f8fafc;border-radius:8px;font-size:10px;color:#64748b;line-height:1.6;">
        <p style="margin:0;">
            <strong>Проверка подлинности:</strong>
            <a href="{{ $verifyUrl }}" style="color:#2563eb;text-decoration:none;">{{ $verifyUrl }}</a>
        </p>
    </div>

    {{-- Footer --}}
    <div style="margin-top:32px;padding-top:16px;border-top:1px solid #e2e8f0;text-align:center;font-size:10px;color:#94a3b8;line-height:1.6;">
        <p>{{ $hotel['name'] }} &bull; {{ $hotel['address'] }} &bull; {{ $hotel['phone'] }} &bull; {{ $hotel['email'] }}</p>
        @if(isset($hotel['tax_id']))
        <p style="margin-top:2px;">ИНН: {{ $hotel['tax_id'] }} &bull; ОКОНХ: {{ $hotel['okonx'] ?? '—' }}</p>
        @endif
        <p style="margin-top:4px;">Документ сформирован {{ now()->format('d.m.Y H:i') }}</p>
    </div>

    {{-- QR Code for verification --}}
    @if(!empty($qrPath) && file_exists($qrPath))
    <div style="position:absolute;bottom:48px;right:48px;text-align:center;">
        <img src="{{ $qrPath }}" style="width:80px;height:80px;">
        <p style="font-size:8px;color:#94a3b8;margin-top:4px;">Проверка счёта</p>
    </div>
    @endif

</div>
</body>
</html>
