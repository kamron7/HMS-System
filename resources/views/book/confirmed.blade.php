<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Запрос получен</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-50 min-h-screen flex items-center justify-center">

<div class="max-w-md w-full mx-auto px-4 py-12">
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-8 text-center">

        {{-- Success icon --}}
        <div class="inline-flex items-center justify-center w-16 h-16 bg-emerald-100 rounded-full mb-5">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-8 h-8 text-emerald-600">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
            </svg>
        </div>

        <h1 class="text-xl font-bold text-slate-900 mb-2">Запрос получен!</h1>
        <p class="text-slate-500 text-sm mb-6">
            Ваш запрос на бронирование принят. Наш администратор свяжется с вами для подтверждения.
        </p>

        {{-- Booking summary --}}
        <div class="bg-slate-50 rounded-xl p-4 mb-6 text-left text-sm space-y-2.5">
            <div class="flex justify-between">
                <span class="text-slate-500">Номер</span>
                <span class="font-medium text-slate-900">{{ $booking->room->number }} — {{ $booking->room->roomType->name }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-slate-500">Заезд</span>
                <span class="font-medium text-slate-900">{{ $booking->check_in_date->format('d.m.Y') }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-slate-500">Выезд</span>
                <span class="font-medium text-slate-900">{{ $booking->check_out_date->format('d.m.Y') }}</span>
            </div>
            @if($booking->inquiry)
            <div class="flex justify-between">
                <span class="text-slate-500">Имя</span>
                <span class="font-medium text-slate-900">{{ $booking->inquiry->fullName() }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-slate-500">Телефон</span>
                <span class="font-medium text-slate-900">{{ $booking->inquiry->phone }}</span>
            </div>
            @endif
            <div class="flex justify-between pt-1 border-t border-slate-200">
                <span class="text-slate-500">Стоимость</span>
                <span class="font-bold text-slate-900">{{ number_format($booking->total_price, 0, '.', ' ') }} сум</span>
            </div>
        </div>

        <p class="text-xs text-slate-400 mb-1">Код вашего бронирования:</p>
        <p class="text-2xl font-bold tracking-widest text-slate-800 mb-4 font-mono">{{ $booking->booking_ref }}</p>
        <p class="text-xs text-slate-400 mb-4">Сохраните этот код — он понадобится для доступа к услугам через QR-код в номере.</p>

        {{-- Room QR code --}}
        @if($booking->room->qr_token)
        <div class="border-t border-slate-100 pt-5 mb-5">
            <p class="text-xs font-semibold text-slate-700 mb-1">QR-код вашего номера</p>
            <p class="text-xs text-slate-400 mb-3">Сохраните — во время проживания сканируйте, чтобы заказать услуги или оставить отзыв.</p>
            <div class="flex justify-center">
                <img src="{{ route('room-portal.qr-image', $booking->room->qr_token) }}"
                     alt="QR номер {{ $booking->room->number }}"
                     class="w-36 h-36 rounded-xl border border-slate-200 bg-white p-2">
            </div>
        </div>
        @endif

        <a href="{{ route('book.index') }}"
           class="inline-flex items-center gap-2 text-sm font-medium text-blue-600 hover:text-blue-700">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18"/></svg>
            Вернуться на главную
        </a>
    </div>
</div>

</body>
</html>
