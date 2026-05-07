<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ваше бронирование — {{ config('hotel.name') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>body { font-family: 'Inter', sans-serif; }</style>
</head>
<body class="bg-slate-50 min-h-screen">

{{-- Header --}}
<header class="bg-white border-b border-slate-200 py-4">
    <div class="max-w-2xl mx-auto px-4 flex items-center justify-between">
        <div>
            <p class="text-xs font-semibold text-blue-600 uppercase tracking-widest">{{ config('hotel.name') }}</p>
            <p class="text-xs text-slate-400 mt-0.5">{{ config('hotel.address') }}</p>
        </div>
        <div class="text-right">
            <p class="text-xs text-slate-400">{{ config('hotel.phone') }}</p>
        </div>
    </div>
</header>

<main class="max-w-2xl mx-auto px-4 py-8 space-y-5">

    {{-- Upsell success flash --}}
    @if(session('upsell_added'))
    <div class="bg-emerald-50 border border-emerald-200 rounded-xl px-4 py-3 flex items-center gap-3">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5 text-emerald-600 flex-shrink-0"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
        <p class="text-sm font-medium text-emerald-800">«{{ session('upsell_added') }}» добавлена к вашему бронированию.</p>
    </div>
    @endif

    {{-- Booking summary card --}}
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="bg-gradient-to-r from-blue-600 to-blue-500 px-6 py-5 text-white">
            <p class="text-xs font-semibold uppercase tracking-widest opacity-80 mb-1">Ваше бронирование</p>
            <h1 class="text-2xl font-bold">{{ $booking->guest->fullName }}</h1>
            <p class="text-sm opacity-80 mt-1">
                {{ $booking->check_in_date->translatedFormat('d MMMM') }} — {{ $booking->check_out_date->translatedFormat('d MMMM Y') }}
                · {{ $nights }} {{ $nights === 1 ? 'ночь' : ($nights < 5 ? 'ночи' : 'ночей') }}
            </p>
        </div>

        <div class="px-6 py-5">
            <div class="grid grid-cols-2 gap-4 text-sm">
                <div>
                    <p class="text-xs text-slate-500 mb-0.5">Номер</p>
                    <p class="font-semibold text-slate-900">{{ $booking->room->number }}</p>
                    <p class="text-xs text-slate-400">{{ $booking->room->roomType->name }}</p>
                </div>
                <div>
                    <p class="text-xs text-slate-500 mb-0.5">Гости</p>
                    <p class="font-semibold text-slate-900">{{ $booking->adults }} взр.@if($booking->children > 0), {{ $booking->children }} дет.@endif</p>
                </div>
                <div>
                    <p class="text-xs text-slate-500 mb-0.5">Заезд</p>
                    <p class="font-semibold text-slate-900">{{ $booking->check_in_date->format('d.m.Y') }}</p>
                    <p class="text-xs text-slate-400">с 14:00</p>
                </div>
                <div>
                    <p class="text-xs text-slate-500 mb-0.5">Выезд</p>
                    <p class="font-semibold text-slate-900">{{ $booking->check_out_date->format('d.m.Y') }}</p>
                    <p class="text-xs text-slate-400">до 12:00</p>
                </div>
            </div>
        </div>

        {{-- Financial summary --}}
        <div class="border-t border-slate-100 px-6 py-4 bg-slate-50 space-y-2 text-sm">
            <div class="flex justify-between text-slate-600">
                <span>Проживание ({{ $nights }} ноч.)</span>
                <span class="font-medium text-slate-900">{{ number_format($roomCost, 0, '.', ' ') }} сум</span>
            </div>
            @if($charges > 0)
            <div class="flex justify-between text-slate-600">
                <span>Доп. услуги</span>
                <span class="font-medium text-slate-900">{{ number_format($charges, 0, '.', ' ') }} сум</span>
            </div>
            @endif
            <div class="flex justify-between font-bold text-slate-900 border-t border-slate-200 pt-2">
                <span>Итого</span>
                <span>{{ number_format($grandTotal, 0, '.', ' ') }} сум</span>
            </div>
            @if($paid > 0)
            <div class="flex justify-between text-emerald-700 text-xs">
                <span>Оплачено</span>
                <span>−{{ number_format($paid, 0, '.', ' ') }} сум</span>
            </div>
            @endif
            @if($balanceDue > 0)
            <div class="flex justify-between font-bold text-red-600 border-t border-slate-200 pt-2">
                <span>К оплате при заезде</span>
                <span>{{ number_format($balanceDue, 0, '.', ' ') }} сум</span>
            </div>
            @else
            <div class="flex justify-between font-semibold text-emerald-600 border-t border-slate-200 pt-2">
                <span>Полностью оплачено</span>
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
            </div>
            @endif
        </div>
    </div>

    {{-- Upsells --}}
    @if($upsells->isNotEmpty())
    <div>
        <h2 class="text-base font-bold text-slate-900 mb-3">Дополнительные услуги</h2>
        <p class="text-sm text-slate-500 mb-4">Закажите заранее — мы всё подготовим к вашему приезду.</p>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            @foreach($upsells as $upsell)
            @php
                $alreadyAdded = $booking->charges->where('description', $upsell['label'])->isNotEmpty();
            @endphp
            <div class="bg-white rounded-xl border {{ $alreadyAdded ? 'border-emerald-300 bg-emerald-50' : 'border-slate-200' }} shadow-sm p-4 flex items-start gap-4">
                <div class="w-10 h-10 rounded-full {{ $alreadyAdded ? 'bg-emerald-100' : 'bg-blue-50' }} flex items-center justify-center flex-shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 {{ $alreadyAdded ? 'text-emerald-600' : 'text-blue-600' }}">
                        <path stroke-linecap="round" stroke-linejoin="round" d="{{ $upsell['icon'] }}"/>
                    </svg>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-semibold text-slate-900">{{ $upsell['label'] }}</p>
                    <p class="text-xs text-slate-500 mt-0.5">{{ number_format($upsell['price'], 0, '.', ' ') }} сум</p>

                    @if($alreadyAdded)
                        <span class="inline-flex items-center gap-1 mt-2 text-xs font-semibold text-emerald-700">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-3.5 h-3.5"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                            Добавлено
                        </span>
                    @else
                        <form method="POST"
                              action="{{ URL::signedRoute('guest.booking.upsell', ['booking' => $booking->id]) }}"
                              class="mt-2">
                            @csrf
                            <input type="hidden" name="key" value="{{ $upsell['key'] }}">
                            <button type="submit"
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-3.5 h-3.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                                Добавить
                            </button>
                        </form>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Contact --}}
    <div class="text-center text-xs text-slate-400 py-4">
        <p>Вопросы? Свяжитесь с нами: <a href="tel:{{ config('hotel.phone') }}" class="text-blue-600 hover:underline">{{ config('hotel.phone') }}</a></p>
        <p class="mt-1">{{ config('hotel.email') }}</p>
    </div>

</main>
</body>
</html>
