@extends('layouts.app')
@section('title', 'Главная')
@section('content')

<script src="https://cdn.jsdelivr.net/npm/apexcharts@3.45.2/dist/apexcharts.min.js"></script>

{{-- ══ SHIFT BANNERS ══ --}}
@if(!in_array(auth()->user()->role->value, ['owner', 'manager']) && !$myOpenShift)
<div class="mb-5 bg-gradient-to-r from-blue-600 to-blue-700 rounded-2xl p-4 flex items-center justify-between">
    <div class="flex items-center gap-3">
        <div class="w-10 h-10 rounded-xl bg-white/15 flex items-center justify-center flex-shrink-0">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-white"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
        </div>
        <div>
            <p class="text-white font-bold text-sm">Вы не на смене</p>
            <p class="text-blue-200 text-xs">Начните смену чтобы приступить к работе</p>
        </div>
    </div>
    <a href="{{ route('attendance.index') }}" class="px-4 py-2 bg-white text-blue-700 hover:bg-blue-50 rounded-xl text-xs font-bold transition-colors flex-shrink-0">Начать смену</a>
</div>
@elseif($myOpenShift)
<div class="mb-5 bg-gradient-to-r from-emerald-500 to-emerald-600 rounded-2xl p-4 flex items-center justify-between">
    <div class="flex items-center gap-3">
        <div class="w-10 h-10 rounded-xl bg-white/15 flex items-center justify-center flex-shrink-0">
            <span class="w-2.5 h-2.5 rounded-full bg-white animate-pulse"></span>
        </div>
        <div>
            <p class="text-white font-bold text-sm">Смена активна · с {{ $myOpenShift->started_at->format('H:i') }}</p>
            <p class="text-emerald-100 text-xs">{{ floor($myOpenShift->duration) }}ч {{ round(($myOpenShift->duration - floor($myOpenShift->duration)) * 60) }}м</p>
        </div>
    </div>
    <a href="{{ route('attendance.index') }}" class="px-4 py-2 bg-white/20 hover:bg-white/30 text-white rounded-xl text-xs font-semibold transition-colors flex-shrink-0">Завершить</a>
</div>
@endif

{{-- ══ HEADER ══ --}}
<div class="flex items-center justify-between mb-5">
    <div>
        <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Обзор</h1>
        <p class="text-slate-400 text-sm mt-0.5">{{ today()->translatedFormat('l, d F Y') }}</p>
    </div>
    <div class="flex items-center gap-3">
        @if($weather)
        <div class="hidden sm:flex items-center gap-2 px-3 py-2 bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700">
            <img src="https://openweathermap.org/img/wn/{{ $weather['icon'] }}.png" alt="" class="w-6 h-6">
            <span class="text-sm font-semibold text-slate-700 dark:text-slate-200">{{ $weather['temp'] }}°C</span>
            <span class="text-xs text-slate-400">{{ $weather['description'] }}</span>
        </div>
        @endif
        <a href="{{ route('bookings.create') }}"
           class="inline-flex items-center gap-2 px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-xl transition-colors shadow-sm">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
            Бронирование
        </a>
    </div>
</div>

{{-- ══ ALERTS STRIP ══ --}}
@if($lateCheckouts->count() > 0)
<div class="mb-4 flex items-center gap-3 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-700/50 rounded-xl px-4 py-3">
    <div class="w-7 h-7 bg-red-100 dark:bg-red-900/60 rounded-lg flex items-center justify-center flex-shrink-0">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-3.5 h-3.5 text-red-600"><path fill-rule="evenodd" d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16Zm.75-13a.75.75 0 0 0-1.5 0v5c0 .414.336.75.75.75h4a.75.75 0 0 0 0-1.5h-3.25V5Z" clip-rule="evenodd"/></svg>
    </div>
    <div class="flex-1 min-w-0">
        <p class="text-sm text-red-800 dark:text-red-300">
            <span class="font-bold">{{ $lateCheckouts->count() }} {{ $lateCheckouts->count() === 1 ? 'гость' : ($lateCheckouts->count() < 5 ? 'гостя' : 'гостей') }} просрочили выезд</span>
            <span class="hidden sm:inline"> — должны были покинуть номер вчера или ранее</span>
        </p>
        <p class="text-xs text-red-600 dark:text-red-400 mt-0.5 truncate hidden sm:block">
            {{ $lateCheckouts->map(fn($b) => '№'.$b->room?->number.' · '.$b->guest?->full_name)->join(', ') }}
        </p>
    </div>
    <a href="{{ route('bookings.index', ['status' => 'checked_in']) }}" class="text-xs font-bold text-red-700 dark:text-red-300 bg-red-100 dark:bg-red-800/50 px-3 py-1.5 rounded-lg hover:bg-red-200 dark:hover:bg-red-800 transition-colors whitespace-nowrap flex-shrink-0">Просмотреть →</a>
</div>
@endif

@if($inquiryCount > 0)
<div class="mb-4 flex items-center gap-3 bg-purple-50 dark:bg-purple-900/20 border border-purple-200 dark:border-purple-700/50 rounded-xl px-4 py-3">
    <div class="w-7 h-7 bg-purple-100 dark:bg-purple-900/60 rounded-lg flex items-center justify-center flex-shrink-0">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-3.5 h-3.5 text-purple-600"><path d="M10 2a6 6 0 0 0-6 6v3.586l-.707.707A1 1 0 0 0 4 14h12a1 1 0 0 0 .707-1.707L16 11.586V8a6 6 0 0 0-6-6ZM10 18a3 3 0 0 1-3-3h6a3 3 0 0 1-3 3Z"/></svg>
    </div>
    <p class="text-sm text-purple-800 dark:text-purple-300 flex-1">
        <span class="font-bold">{{ $inquiryCount }} {{ $inquiryCount === 1 ? 'новый запрос' : ($inquiryCount < 5 ? 'новых запроса' : 'новых запросов') }}</span> от клиентов ожидают рассмотрения
    </p>
    <a href="{{ route('bookings.index', ['status' => 'inquiry']) }}" class="text-xs font-bold text-purple-700 dark:text-purple-300 bg-purple-100 dark:bg-purple-800/50 px-3 py-1.5 rounded-lg hover:bg-purple-200 transition-colors">Просмотреть →</a>
</div>
@endif

{{-- ══ KPI CARDS ══ --}}
@php
    $total = max($roomStats['total'], 1);
    $revenueThisMonth = end($revenueValues) ?: 0;
    $revenuePrevMonth = count($revenueValues) >= 2 ? $revenueValues[count($revenueValues) - 2] : 0;
    $revDelta = $revenuePrevMonth > 0 ? round(($revenueThisMonth - $revenuePrevMonth) / $revenuePrevMonth * 100) : null;
@endphp

<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-5">

    {{-- Occupancy --}}
    <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-5 shadow-sm">
        <div class="flex items-center justify-between mb-3">
            <p class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Загрузка</p>
            <span class="text-xs font-bold px-2 py-0.5 rounded-full {{ $occupancyRate >= 80 ? 'bg-emerald-100 text-emerald-700' : ($occupancyRate >= 50 ? 'bg-amber-100 text-amber-700' : 'bg-red-100 text-red-700') }}">
                {{ $occupancyRate >= 80 ? 'Высокая' : ($occupancyRate >= 50 ? 'Средняя' : 'Низкая') }}
            </span>
        </div>
        <p class="text-4xl font-black text-slate-900 dark:text-white tabular-nums">{{ $occupancyRate }}<span class="text-lg font-semibold text-slate-400 ml-0.5">%</span></p>
        <p class="text-xs text-slate-400 mt-1 mb-3">{{ $roomStats['occupied'] }} из {{ $roomStats['total'] }} номеров</p>
        {{-- Segmented bar --}}
        <div class="flex h-1.5 rounded-full overflow-hidden gap-px">
            @if($roomStats['available'] > 0)<div class="bg-emerald-500 rounded-full" style="width:{{ round($roomStats['available']/$total*100) }}%"></div>@endif
            @if($roomStats['occupied'] > 0)<div class="bg-blue-500 rounded-full" style="width:{{ round($roomStats['occupied']/$total*100) }}%"></div>@endif
            @if($roomStats['cleaning'] > 0)<div class="bg-amber-400 rounded-full" style="width:{{ round($roomStats['cleaning']/$total*100) }}%"></div>@endif
            @if($roomStats['maintenance'] > 0)<div class="bg-red-400 rounded-full" style="width:{{ round($roomStats['maintenance']/$total*100) }}%"></div>@endif
        </div>
        <div class="flex flex-wrap gap-x-3 gap-y-1 mt-2">
            <span class="text-[10px] text-slate-500 flex items-center gap-1"><span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>Своб. {{ $roomStats['available'] }}</span>
            <span class="text-[10px] text-slate-500 flex items-center gap-1"><span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span>Занято {{ $roomStats['occupied'] }}</span>
            @if($roomStats['cleaning'] > 0)<span class="text-[10px] text-slate-500 flex items-center gap-1"><span class="w-1.5 h-1.5 rounded-full bg-amber-400"></span>Уборка {{ $roomStats['cleaning'] }}</span>@endif
            @if($roomStats['maintenance'] > 0)<span class="text-[10px] text-red-500 flex items-center gap-1"><span class="w-1.5 h-1.5 rounded-full bg-red-400"></span>Ремонт {{ $roomStats['maintenance'] }}</span>@endif
        </div>
    </div>

    {{-- Revenue today --}}
    <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-5 shadow-sm">
        <div class="flex items-center justify-between mb-3">
            <p class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Выручка сегодня</p>
            <a href="{{ route('reports.index') }}" class="text-xs text-slate-400 hover:text-blue-600 transition-colors">Отчёт →</a>
        </div>
        <p class="text-3xl font-black text-slate-900 dark:text-white tabular-nums leading-tight">{{ number_format($revenueToday, 0, '.', ' ') }}</p>
        <p class="text-xs text-slate-400 mt-1 mb-2">сум</p>
        <div class="flex items-center gap-3 text-xs">
            <span class="text-slate-500">Месяц: <span class="font-bold text-slate-700 dark:text-slate-200">{{ number_format($revenueThisMonth, 0, '.', ' ') }}</span></span>
            @if($revDelta !== null)
            <span class="font-bold {{ $revDelta >= 0 ? 'text-emerald-600' : 'text-red-500' }}">{{ $revDelta >= 0 ? '+' : '' }}{{ $revDelta }}%</span>
            @endif
        </div>
        @if(in_array(auth()->user()->role->value, ['owner','manager']) && $debtTotal > 0)
        <p class="text-xs text-red-500 mt-2 font-medium">Долг: {{ number_format($debtTotal, 0, '.', ' ') }} сум</p>
        @endif
    </div>

    {{-- Today's activity --}}
    <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-5 shadow-sm">
        <div class="flex items-center justify-between mb-3">
            <p class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Сегодня</p>
            <span class="text-xs text-slate-400">{{ today()->format('d.m') }}</span>
        </div>
        <div class="space-y-3">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <div class="w-7 h-7 bg-emerald-100 dark:bg-emerald-900/30 rounded-lg flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-3.5 h-3.5 text-emerald-600"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 9V5.25A2.25 2.25 0 0 1 10.5 3h6a2.25 2.25 0 0 1 2.25 2.25v13.5A2.25 2.25 0 0 1 16.5 21h-6a2.25 2.25 0 0 1-2.25-2.25V15m-3 0-3-3m0 0 3-3m-3 3H15"/></svg>
                    </div>
                    <span class="text-sm text-slate-600 dark:text-slate-300">Заезды</span>
                </div>
                <span class="text-xl font-black text-slate-900 dark:text-white">{{ $checkInsToday->count() }}</span>
            </div>
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <div class="w-7 h-7 bg-amber-100 dark:bg-amber-900/30 rounded-lg flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-3.5 h-3.5 text-amber-600"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15m3 0 3-3m0 0-3-3m3 3H9"/></svg>
                    </div>
                    <span class="text-sm text-slate-600 dark:text-slate-300">Выезды</span>
                </div>
                <span class="text-xl font-black text-slate-900 dark:text-white">{{ $checkOutsToday->count() }}</span>
            </div>
            <div class="h-px bg-slate-100 dark:bg-slate-700"></div>
            <div class="flex items-center justify-between text-xs">
                <span class="text-slate-400">Ожидают подтв.</span>
                <a href="{{ route('bookings.index', ['status' => 'pending']) }}" class="font-bold text-amber-600 hover:underline">{{ $pendingBookings->count() }}</a>
            </div>
        </div>
    </div>

    {{-- Guest / reviews --}}
    <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-5 shadow-sm">
        <div class="flex items-center justify-between mb-3">
            <p class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Гости</p>
            @if($averageRating)
            <span class="flex items-center gap-1 text-xs font-bold text-amber-600">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-3.5 h-3.5"><path fill-rule="evenodd" d="M10.788 3.21c.448-1.077 1.976-1.077 2.424 0l2.082 5.006 5.404.434c1.164.093 1.636 1.545.749 2.305l-4.117 3.527 1.257 5.273c.271 1.136-.964 2.033-1.96 1.425L12 18.354 7.373 21.18c-.996.608-2.231-.29-1.96-1.425l1.257-5.273-4.117-3.527c-.887-.76-.415-2.212.749-2.305l5.404-.434 2.082-5.005Z" clip-rule="evenodd"/></svg>
                {{ $averageRating }}
            </span>
            @endif
        </div>
        <div class="space-y-3">
            @if($vipsInHouse->isNotEmpty())
            <div class="flex items-center justify-between">
                <span class="text-sm text-slate-600 dark:text-slate-300 flex items-center gap-1.5">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-3.5 h-3.5 text-amber-500"><path fill-rule="evenodd" d="M10.788 3.21c.448-1.077 1.976-1.077 2.424 0l2.082 5.006 5.404.434c1.164.093 1.636 1.545.749 2.305l-4.117 3.527 1.257 5.273c.271 1.136-.964 2.033-1.96 1.425L12 18.354 7.373 21.18c-.996.608-2.231-.29-1.96-1.425l1.257-5.273-4.117-3.527c-.887-.76-.415-2.212.749-2.305l5.404-.434 2.082-5.005Z" clip-rule="evenodd"/></svg>
                    VIP в отеле
                </span>
                <span class="text-xl font-black text-slate-900 dark:text-white">{{ $vipsInHouse->count() }}</span>
            </div>
            @endif
            <div class="flex items-center justify-between">
                <span class="text-sm text-slate-600 dark:text-slate-300">Повторные гости</span>
                <span class="text-xl font-black text-slate-900 dark:text-white">{{ $retentionRate }}<span class="text-sm font-semibold text-slate-400">%</span></span>
            </div>
            @if($recentReviews->isNotEmpty())
            <div class="h-px bg-slate-100 dark:bg-slate-700"></div>
            <a href="{{ route('reviews.index') }}" class="text-xs text-blue-600 dark:text-blue-400 hover:underline">{{ $recentReviews->count() }} новых отзывов →</a>
            @endif
        </div>
    </div>
</div>

{{-- ══ CHARTS ROW ══ --}}
<div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-4">

    {{-- Revenue 12m — area chart (wide) --}}
    <div class="lg:col-span-2 bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm p-5">
        <div class="flex items-center justify-between mb-1">
            <div>
                <p class="text-sm font-bold text-slate-800 dark:text-slate-100">Выручка за 12 месяцев</p>
                <p class="text-xs text-slate-400 mt-0.5">Оплаченные суммы по месяцам</p>
            </div>
            <a href="{{ route('reports.index') }}" class="text-xs text-slate-400 hover:text-blue-600 transition-colors">Подробнее →</a>
        </div>
        <div id="revenueChart" class="h-56 -mx-1 mt-2"></div>
    </div>

    {{-- Status donut --}}
    <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm p-5">
        <p class="text-sm font-bold text-slate-800 dark:text-slate-100 mb-0.5">Бронирования</p>
        <p class="text-xs text-slate-400 mb-4">По статусу</p>
        <div class="relative" style="height:144px">
            <div id="statusChart"></div>
            <div class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none">
                <p class="text-2xl font-black text-slate-900 dark:text-white">{{ array_sum($statusValues) }}</p>
                <p class="text-[10px] text-slate-400">всего</p>
            </div>
        </div>
        <div class="mt-4 space-y-1.5">
            @foreach($statusLabels as $i => $label)
            <div class="flex items-center gap-2">
                <span class="w-2 h-2 rounded-full flex-shrink-0" style="background:{{ $statusColors[$i] }}"></span>
                <span class="text-xs text-slate-500 dark:text-slate-400 flex-1 truncate">{{ $label }}</span>
                <span class="text-xs font-bold text-slate-700 dark:text-slate-200">{{ $statusValues[$i] }}</span>
            </div>
            @endforeach
        </div>
    </div>
</div>

{{-- ══ OCCUPANCY 30D ══ --}}
<div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm p-5 mb-5">
    <div class="flex items-center justify-between mb-1">
        <div>
            <p class="text-sm font-bold text-slate-800 dark:text-slate-100">Загрузка за 30 дней</p>
            <p class="text-xs text-slate-400 mt-0.5">% занятых номеров по дням</p>
        </div>
        <div class="flex items-center gap-2">
            <span class="text-xs text-slate-400">Ср. {{ round(array_sum($occupancyValues) / max(count($occupancyValues),1)) }}%</span>
            @if(count($typeValues) > 0)
            <span class="w-px h-4 bg-slate-200 dark:bg-slate-600"></span>
            @endif
        </div>
    </div>
    <div id="occupancyChart" class="h-44 -mx-1 mt-2"></div>
</div>

{{-- ══ TODAY'S ACTIVITY ══ --}}
<div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-5">

    {{-- Check-ins --}}
    <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden">
        <div class="px-5 py-4 flex items-center justify-between border-b border-slate-100 dark:border-slate-700/60">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-xl bg-emerald-500 flex items-center justify-center shadow-sm shadow-emerald-500/30">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4 text-white"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 9V5.25A2.25 2.25 0 0 1 10.5 3h6a2.25 2.25 0 0 1 2.25 2.25v13.5A2.25 2.25 0 0 1 16.5 21h-6a2.25 2.25 0 0 1-2.25-2.25V15m-3 0-3-3m0 0 3-3m-3 3H15"/></svg>
                </div>
                <h3 class="text-sm font-bold text-slate-800 dark:text-white">Заезды</h3>
            </div>
            <span class="inline-flex items-center justify-center min-w-[1.5rem] h-6 px-1.5 rounded-full bg-emerald-100 dark:bg-emerald-900/40 text-emerald-700 dark:text-emerald-300 text-xs font-bold">{{ $checkInsToday->count() }}</span>
        </div>
        <div class="divide-y divide-slate-50 dark:divide-slate-700/40">
            @forelse($checkInsToday as $booking)
            <div x-data="{ done: false }" x-show="!done"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                 class="flex items-center gap-3 px-5 py-3 hover:bg-slate-50 dark:hover:bg-slate-700/30 transition-colors">
                <div class="w-8 h-8 rounded-full flex items-center justify-center flex-shrink-0 text-white text-xs font-bold"
                     style="background: hsl({{ (ord(strtolower($booking->guest->first_name[0])) - 97) * 13 + 200 }}, 65%, 50%)">
                    {{ strtoupper(substr($booking->guest->first_name, 0, 1)) }}
                </div>
                <a href="{{ route('bookings.show', $booking) }}" class="min-w-0 flex-1 group">
                    <p class="text-sm font-semibold text-slate-900 dark:text-white truncate group-hover:text-blue-600 transition-colors">{{ $booking->guest->fullName }}</p>
                    <p class="text-xs text-slate-400">№ {{ $booking->room->number }} · {{ $booking->room->roomType->name }}</p>
                </a>
                @if($booking->status !== \App\Enums\BookingStatus::CheckedIn)
                <button @click="fetch('{{ route('bookings.status', $booking) }}',{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}'},body:JSON.stringify({transition:'checked_in'})}).then(r=>r.json()).then(d=>{if(!d.error)done=true})"
                        class="flex-shrink-0 px-3 py-1.5 text-xs font-bold bg-emerald-500 hover:bg-emerald-600 text-white rounded-xl transition-colors">
                    Заселить
                </button>
                @else
                    <x-status-badge :status="$booking->status" />
                @endif
            </div>
            @empty
            <div class="px-5 py-8 text-center text-xs text-slate-400 dark:text-slate-500">Нет заездов сегодня</div>
            @endforelse
        </div>
    </div>

    {{-- Check-outs --}}
    <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden">
        <div class="px-5 py-4 flex items-center justify-between border-b border-slate-100 dark:border-slate-700/60">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-xl bg-amber-500 flex items-center justify-center shadow-sm shadow-amber-500/30">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4 text-white"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15m3 0 3-3m0 0-3-3m3 3H9"/></svg>
                </div>
                <h3 class="text-sm font-bold text-slate-800 dark:text-white">Выезды</h3>
            </div>
            <span class="inline-flex items-center justify-center min-w-[1.5rem] h-6 px-1.5 rounded-full bg-amber-100 dark:bg-amber-900/40 text-amber-700 dark:text-amber-300 text-xs font-bold">{{ $checkOutsToday->count() }}</span>
        </div>
        <div class="divide-y divide-slate-50 dark:divide-slate-700/40">
            @forelse($checkOutsToday as $booking)
            <div x-data="{ done: false }" x-show="!done"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                 class="flex items-center gap-3 px-5 py-3 hover:bg-slate-50 dark:hover:bg-slate-700/30 transition-colors">
                <div class="w-8 h-8 rounded-full flex items-center justify-center flex-shrink-0 text-white text-xs font-bold"
                     style="background: hsl({{ (ord(strtolower($booking->guest->first_name[0])) - 97) * 13 + 200 }}, 55%, 55%)">
                    {{ strtoupper(substr($booking->guest->first_name, 0, 1)) }}
                </div>
                <a href="{{ route('bookings.show', $booking) }}" class="min-w-0 flex-1 group">
                    <p class="text-sm font-semibold text-slate-900 dark:text-white truncate group-hover:text-blue-600 transition-colors">{{ $booking->guest->fullName }}</p>
                    <p class="text-xs text-slate-400">№ {{ $booking->room->number }} · {{ $booking->room->roomType->name }}</p>
                </a>
                <button @click="if(!confirm('Выселить {{ addslashes($booking->guest->fullName) }}?'))return;fetch('{{ route('bookings.status', $booking) }}',{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}'},body:JSON.stringify({transition:'checked_out'})}).then(r=>r.json()).then(d=>{if(!d.error)done=true})"
                        class="flex-shrink-0 px-3 py-1.5 text-xs font-bold bg-slate-800 dark:bg-slate-600 hover:bg-slate-900 text-white rounded-xl transition-colors">
                    Выселить
                </button>
            </div>
            @empty
            <div class="px-5 py-8 text-center text-xs text-slate-400 dark:text-slate-500">Нет выездов сегодня</div>
            @endforelse
        </div>
    </div>
</div>

{{-- ══ ALERTS ROW ══ --}}
@if($dirtyRooms->isNotEmpty() || $openMaintenance->isNotEmpty() || $vipsInHouse->isNotEmpty() || $pendingBookings->isNotEmpty())
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-5">

    {{-- Dirty rooms --}}
    @if($dirtyRooms->isNotEmpty())
    <div class="bg-white dark:bg-slate-800 rounded-2xl border border-orange-200 dark:border-orange-800/60 shadow-sm p-4">
        <div class="flex items-center gap-2 mb-3">
            <div class="w-7 h-7 bg-orange-100 dark:bg-orange-900/40 rounded-lg flex items-center justify-center">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-3.5 h-3.5 text-orange-600"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z"/></svg>
            </div>
            <span class="text-xs font-bold text-orange-700 dark:text-orange-300">Грязные номера</span>
            <span class="ml-auto text-xs font-bold text-orange-600 bg-orange-100 dark:bg-orange-900/40 px-1.5 py-0.5 rounded-full">{{ $dirtyRooms->count() }}</span>
        </div>
        <div class="flex flex-wrap gap-1.5">
            @foreach($dirtyRooms as $dr)
            <a href="{{ route('rooms.index') }}" class="px-2.5 py-1 text-xs font-bold bg-orange-50 dark:bg-orange-900/20 text-orange-700 dark:text-orange-300 rounded-lg hover:bg-orange-100 transition-colors">{{ $dr->number }}</a>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Maintenance --}}
    @if($openMaintenance->isNotEmpty())
    <div class="bg-white dark:bg-slate-800 rounded-2xl border border-red-200 dark:border-red-800/60 shadow-sm p-4">
        <div class="flex items-center gap-2 mb-3">
            <div class="w-7 h-7 bg-red-100 dark:bg-red-900/40 rounded-lg flex items-center justify-center">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-3.5 h-3.5 text-red-600"><path stroke-linecap="round" stroke-linejoin="round" d="M11.42 15.17 17.25 21A2.652 2.652 0 0 0 21 17.25l-5.877-5.877M11.42 15.17l2.496-3.03c.317-.384.74-.626 1.208-.766M11.42 15.17l-4.655 5.653a2.548 2.548 0 1 1-3.586-3.586l5.654-4.654m5.546-4.634.166-.175c.33-.33.795-.5 1.26-.5a1.783 1.783 0 0 1 1.26 3.04l-.166.175m-5.52 4.56-.174.166a1.783 1.783 0 0 1-3.04-1.26c0-.465.17-.93.5-1.26l.175-.166"/></svg>
            </div>
            <span class="text-xs font-bold text-red-700 dark:text-red-300">Заявки</span>
            <span class="ml-auto text-xs font-bold text-red-600 bg-red-100 dark:bg-red-900/40 px-1.5 py-0.5 rounded-full">{{ $openMaintenance->count() }}</span>
        </div>
        <div class="space-y-1.5">
            @foreach($openMaintenance->take(4) as $m)
            <a href="{{ route('maintenance.show', $m) }}" class="flex items-center gap-2 text-xs hover:text-blue-600 transition-colors">
                <span class="truncate text-slate-700 dark:text-slate-300 flex-1">№{{ $m->room->number }} — {{ \Illuminate\Support\Str::limit($m->title, 28) }}</span>
                @if($m->priority->value === 'urgent')<span class="flex-shrink-0 text-[9px] font-bold text-red-600 bg-red-100 px-1.5 py-0.5 rounded">СРОЧНО</span>@endif
            </a>
            @endforeach
            @if($openMaintenance->count() > 4)
            <a href="{{ route('maintenance.index') }}" class="text-xs text-red-600 font-medium">+ ещё {{ $openMaintenance->count() - 4 }}</a>
            @endif
        </div>
    </div>
    @endif

    {{-- VIPs --}}
    @if($vipsInHouse->isNotEmpty())
    <div class="bg-white dark:bg-slate-800 rounded-2xl border border-amber-200 dark:border-amber-800/60 shadow-sm p-4">
        <div class="flex items-center gap-2 mb-3">
            <div class="w-7 h-7 bg-amber-100 dark:bg-amber-900/40 rounded-lg flex items-center justify-center">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-3.5 h-3.5 text-amber-500"><path fill-rule="evenodd" d="M10.788 3.21c.448-1.077 1.976-1.077 2.424 0l2.082 5.006 5.404.434c1.164.093 1.636 1.545.749 2.305l-4.117 3.527 1.257 5.273c.271 1.136-.964 2.033-1.96 1.425L12 18.354 7.373 21.18c-.996.608-2.231-.29-1.96-1.425l1.257-5.273-4.117-3.527c-.887-.76-.415-2.212.749-2.305l5.404-.434 2.082-5.005Z" clip-rule="evenodd"/></svg>
            </div>
            <span class="text-xs font-bold text-amber-700 dark:text-amber-300">VIP в отеле</span>
        </div>
        <div class="space-y-1.5">
            @foreach($vipsInHouse as $vip)
            <a href="{{ route('bookings.show', $vip) }}" class="flex items-center justify-between text-xs hover:text-blue-600 transition-colors">
                <span class="text-slate-700 dark:text-slate-300 font-medium truncate">{{ $vip->guest->fullName }}</span>
                <span class="text-slate-400 font-mono ml-2 flex-shrink-0">№{{ $vip->room->number }}</span>
            </a>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Pending --}}
    @if($pendingBookings->isNotEmpty())
    <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm p-4">
        <div class="flex items-center gap-2 mb-3">
            <div class="w-7 h-7 bg-amber-100 dark:bg-amber-900/30 rounded-lg flex items-center justify-center">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-3.5 h-3.5 text-amber-600"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
            </div>
            <span class="text-xs font-bold text-slate-700 dark:text-slate-200">Ожидают подтв.</span>
            <a href="{{ route('bookings.index', ['status' => 'pending']) }}" class="ml-auto text-xs text-blue-600 hover:underline">Все →</a>
        </div>
        <div class="space-y-1.5">
            @foreach($pendingBookings->take(4) as $booking)
            <a href="{{ route('bookings.show', $booking) }}" class="flex items-center justify-between text-xs hover:text-blue-600 transition-colors">
                <span class="text-slate-700 dark:text-slate-300 font-medium truncate flex-1">{{ $booking->guest->fullName }}</span>
                <span class="text-slate-400 ml-2 flex-shrink-0">{{ $booking->check_in_date->format('d.m') }}</span>
            </a>
            @endforeach
        </div>
    </div>
    @endif

</div>
@endif

{{-- ══ ROOM TYPE REVENUE + CASHIER ══ --}}
<div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-5">

    {{-- Room type revenue chart --}}
    @if(count($typeValues) > 0)
    <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm p-5">
        <p class="text-sm font-bold text-slate-800 dark:text-slate-100 mb-0.5">Выручка по типу номера</p>
        <p class="text-xs text-slate-400 mb-3">Текущий месяц</p>
        <div id="typeChart" class="h-40 -mx-2"></div>
    </div>
    @endif

    {{-- Cashier + shift notes --}}
    <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-100 dark:border-slate-700 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <div class="w-7 h-7 bg-blue-100 dark:bg-blue-900/30 rounded-lg flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-3.5 h-3.5 text-blue-600"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75"/></svg>
                </div>
                <h3 class="text-sm font-bold text-slate-800 dark:text-white">Касса сегодня</h3>
            </div>
            <a href="{{ route('cashier.daily') }}" class="text-xs text-blue-600 dark:text-blue-400 font-medium hover:underline">Отчёт →</a>
        </div>
        @if($cashierShiftsToday->isEmpty())
        <div class="px-5 py-8 text-center text-xs text-slate-400">Нет смен сегодня</div>
        @else
        <div class="divide-y divide-slate-50 dark:divide-slate-700/40">
            @foreach($cashierShiftsToday as $cs)
            <div class="px-5 py-3 flex items-center justify-between">
                <div>
                    <p class="text-sm font-semibold text-slate-900 dark:text-white">{{ $cs->user->name }}</p>
                    <p class="text-xs text-slate-400">{{ $cs->shift }} · {{ $cs->opened_at->format('H:i') }} — {{ $cs->closed_at?->format('H:i') ?? '...' }}</p>
                </div>
                @if($cs->status->value === 'open')
                <span class="text-xs font-bold text-emerald-600 bg-emerald-100 dark:bg-emerald-900/40 px-2 py-1 rounded-lg">Открыта</span>
                @else
                <span class="text-sm font-bold text-emerald-600">+{{ number_format($cs->cash_in, 0, '.', ' ') }}</span>
                @endif
            </div>
            @endforeach
            <div class="px-5 py-3 bg-slate-50 dark:bg-slate-900/30 flex items-center justify-between">
                <span class="text-sm font-bold text-slate-700 dark:text-slate-200">Итого принято</span>
                <span class="text-sm font-extrabold text-emerald-600">+{{ number_format($cashierTotalToday, 0, '.', ' ') }}</span>
            </div>
        </div>
        @endif
    </div>
</div>

{{-- ══ SHIFT NOTES + UPCOMING ══ --}}
<div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-5">

    {{-- Shift notes --}}
    <div x-data="{ open: true }" class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-100 dark:border-slate-700/60 flex items-center justify-between cursor-pointer" @click="open = !open">
            <div class="flex items-center gap-2">
                <div class="w-7 h-7 bg-slate-100 dark:bg-slate-700 rounded-lg flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-3.5 h-3.5 text-slate-500"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z"/></svg>
                </div>
                <h3 class="text-sm font-bold text-slate-800 dark:text-white">Заметки смены</h3>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('shift-notes.index') }}" @click.stop class="text-xs font-semibold text-blue-600 dark:text-blue-400 hover:underline">Все →</a>
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4 text-slate-300 transition-transform" :class="open ? '' : '-rotate-90'"><path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5"/></svg>
            </div>
        </div>
        <div x-show="open" x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
            @forelse($lastShiftNotes as $note)
            <div class="px-5 py-3.5 border-b border-slate-50 dark:border-slate-700/40 last:border-0">
                <div class="flex items-center gap-2 mb-1">
                    <span class="text-xs font-bold text-slate-800 dark:text-slate-200">{{ $note->user->name }}</span>
                    <span class="inline-flex items-center px-1.5 py-0.5 rounded-md text-xs font-semibold {{ $note->shiftColor() }}">{{ $note->shiftLabel() }}</span>
                    <span class="text-xs text-slate-400 ml-auto">{{ $note->created_at->format('d.m H:i') }}</span>
                </div>
                <p class="text-xs text-slate-600 dark:text-slate-300 leading-relaxed line-clamp-2">{{ $note->body }}</p>
            </div>
            @empty
            <div class="px-5 py-8 text-center text-xs text-slate-400">Нет заметок за 7 дней</div>
            @endforelse
        </div>
    </div>

    {{-- Upcoming check-ins --}}
    <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-100 dark:border-slate-700/60 flex items-center justify-between">
            <h3 class="text-sm font-bold text-slate-800 dark:text-white flex items-center gap-2">
                Предстоящие заезды
                <span class="text-xs font-normal text-slate-400">7 дней</span>
            </h3>
        </div>
        @if($upcomingCheckIns->isNotEmpty())
        <div class="divide-y divide-slate-50 dark:divide-slate-700/40">
            @foreach($upcomingCheckIns->take(6) as $booking)
            <div class="flex items-center gap-3 px-5 py-3 hover:bg-slate-50 dark:hover:bg-slate-700/30 transition-colors">
                <span class="text-xs font-mono font-bold text-slate-400 w-10 flex-shrink-0">{{ $booking->check_in_date->format('d.m') }}</span>
                <a href="{{ route('bookings.show', $booking) }}" class="flex-1 min-w-0 text-sm font-semibold text-slate-900 dark:text-white truncate hover:text-blue-600 transition-colors">{{ $booking->guest->fullName }}</a>
                <span class="text-xs text-slate-400 bg-slate-100 dark:bg-slate-700 px-2 py-0.5 rounded-lg flex-shrink-0">{{ $booking->room->number }}</span>
            </div>
            @endforeach
        </div>
        @else
        <div class="px-5 py-8 text-center text-xs text-slate-400">Нет предстоящих заездов</div>
        @endif
    </div>
</div>

{{-- ══ REVIEWS ══ --}}
@if($recentReviews->isNotEmpty())
<div class="mb-2">
    <div class="flex items-center justify-between mb-3">
        <h2 class="text-sm font-bold text-slate-800 dark:text-white flex items-center gap-2">
            Отзывы гостей
            @if($averageRating)<span class="text-amber-500 font-bold">★ {{ $averageRating }}</span>@endif
        </h2>
        <a href="{{ route('reviews.index') }}" class="text-xs font-semibold text-blue-600 dark:text-blue-400 hover:underline">Все →</a>
    </div>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
        @foreach($recentReviews->take(3) as $review)
        <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm p-4">
            <div class="flex items-center gap-0.5 mb-2">
                @for($i = 0; $i < 5; $i++)
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-3 h-3 {{ $i < $review->rating ? 'text-amber-400' : 'text-slate-200 dark:text-slate-600' }}"><path fill-rule="evenodd" d="M10.788 3.21c.448-1.077 1.976-1.077 2.424 0l2.082 5.006 5.404.434c1.164.093 1.636 1.545.749 2.305l-4.117 3.527 1.257 5.273c.271 1.136-.964 2.033-1.96 1.425L12 18.354 7.373 21.18c-.996.608-2.231-.29-1.96-1.425l1.257-5.273-4.117-3.527c-.887-.76-.415-2.212.749-2.305l5.404-.434 2.082-5.005Z" clip-rule="evenodd"/></svg>
                @endfor
                <span class="text-[10px] text-slate-400 ml-auto">{{ $review->submitted_at->format('d.m') }}</span>
            </div>
            @if($review->comment)
            <p class="text-xs text-slate-600 dark:text-slate-300 leading-relaxed line-clamp-2 mb-2">{{ $review->comment }}</p>
            @endif
            <p class="text-[10px] text-slate-400">
                @if($review->booking?->guest)<span class="font-semibold text-slate-500 dark:text-slate-400">{{ $review->booking->guest->first_name }}</span>@endif
                @if($review->room) · Ном. {{ $review->room->number }}@endif
            </p>
        </div>
        @endforeach
    </div>
</div>
@endif

{{-- ══ APEXCHARTS ══ --}}
<script>
(function() {
    const dark = document.documentElement.classList.contains('dark');
    const fg      = dark ? '#94a3b8' : '#64748b';
    const gridCol = dark ? 'rgba(255,255,255,0.04)' : '#f1f5f9';
    const bg      = dark ? '#1e293b' : '#ffffff';

    const base = {
        chart: { toolbar: { show: false }, background: 'transparent', fontFamily: "'Inter', sans-serif" },
        theme: { mode: dark ? 'dark' : 'light' },
        grid: { borderColor: gridCol, strokeDashArray: 3, xaxis: { lines: { show: false } } },
        tooltip: { theme: dark ? 'dark' : 'light', style: { fontSize: '12px' } },
        dataLabels: { enabled: false },
    };

    // ── Revenue area ──
    new ApexCharts(document.querySelector('#revenueChart'), {
        ...base,
        chart: { ...base.chart, type: 'area', height: 224 },
        series: [{ name: 'Выручка', data: @json($revenueValues) }],
        xaxis: {
            categories: @json($revenueLabels),
            tickAmount: 6,
            labels: { style: { colors: fg, fontSize: '10px' }, rotate: -20 },
            axisBorder: { show: false }, axisTicks: { show: false },
        },
        yaxis: {
            labels: {
                formatter: v => v >= 1000000 ? (v/1000000).toFixed(1)+'M' : (v/1000).toFixed(0)+'K',
                style: { colors: fg, fontSize: '10px' }
            }
        },
        stroke: { curve: 'smooth', width: 2.5, colors: ['#6366f1'] },
        fill: {
            type: 'gradient',
            gradient: { shadeIntensity: 1, opacityFrom: 0.3, opacityTo: 0.02, stops: [0, 95] }
        },
        colors: ['#6366f1'],
        markers: { size: 0, hover: { size: 4 } },
        tooltip: { ...base.tooltip, y: { formatter: v => new Intl.NumberFormat('ru-RU').format(v) + ' сум' } },
    }).render();

    // ── Occupancy area ──
    new ApexCharts(document.querySelector('#occupancyChart'), {
        ...base,
        chart: { ...base.chart, type: 'area', height: 176 },
        series: [{ name: 'Загрузка', data: @json($occupancyValues) }],
        xaxis: {
            categories: @json($occupancyLabels),
            tickAmount: 8,
            labels: { style: { colors: fg, fontSize: '10px' } },
            axisBorder: { show: false }, axisTicks: { show: false },
        },
        yaxis: {
            min: 0, max: 100, tickAmount: 4,
            labels: { formatter: v => v+'%', style: { colors: fg, fontSize: '10px' } }
        },
        stroke: { curve: 'smooth', width: 2.5, colors: ['#10b981'] },
        fill: {
            type: 'gradient',
            gradient: { shadeIntensity: 1, opacityFrom: 0.28, opacityTo: 0.02, stops: [0, 95] }
        },
        colors: ['#10b981'],
        markers: { size: 0, hover: { size: 4 } },
        tooltip: { ...base.tooltip, y: { formatter: v => v + '%' } },
        annotations: {
            yaxis: [{
                y: Math.round(@json($occupancyValues).reduce((a,b)=>a+b,0) / Math.max(@json($occupancyValues).length,1)),
                borderColor: '#10b981',
                borderWidth: 1,
                strokeDashArray: 4,
                label: { text: 'Среднее', style: { color: '#10b981', background: 'transparent', fontSize: '10px', fontWeight: 600 }, position: 'right', offsetX: -2 }
            }]
        },
    }).render();

    // ── Status donut ──
    new ApexCharts(document.querySelector('#statusChart'), {
        ...base,
        chart: { ...base.chart, type: 'donut', height: 144 },
        series: @json($statusValues),
        labels: @json($statusLabels),
        colors: @json($statusColors),
        plotOptions: { pie: { donut: { size: '74%', labels: { show: false } } } },
        legend: { show: false },
        stroke: { width: 2, colors: [bg] },
        tooltip: { ...base.tooltip, y: { formatter: v => v + ' брон.' } },
    }).render();

    // ── Room type horizontal bars ──
    @if(count($typeValues) > 0)
    new ApexCharts(document.querySelector('#typeChart'), {
        ...base,
        chart: { ...base.chart, type: 'bar', height: 160 },
        series: [{ name: 'Выручка', data: @json($typeValues) }],
        xaxis: {
            categories: @json($typeLabels),
            labels: { style: { colors: fg, fontSize: '10px' } },
            axisBorder: { show: false }, axisTicks: { show: false },
        },
        yaxis: { labels: { style: { colors: fg, fontSize: '10px' } } },
        plotOptions: { bar: { horizontal: true, borderRadius: 5, barHeight: '50%', borderRadiusApplication: 'end' } },
        fill: {
            type: 'gradient',
            gradient: { shade: 'light', type: 'horizontal', shadeIntensity: 0.3, gradientToColors: ['#818cf8'], opacityFrom: 1, opacityTo: 0.85 }
        },
        colors: ['#6366f1'],
        tooltip: { ...base.tooltip, y: { formatter: v => new Intl.NumberFormat('ru-RU').format(v) + ' сум' } },
    }).render();
    @endif
})();
</script>

@endsection
