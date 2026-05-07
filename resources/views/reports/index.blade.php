@extends('layouts.app')
@section('title', 'Отчёты')

@section('content')
@php
$netIncome = $roomRevenue - $totalExpenses;
$periods = ['month' => 'Месяц', 'quarter' => 'Квартал', 'year' => 'Год'];
@endphp

{{-- ── Page header ──────────────────────────────────────────────────────── --}}
<div class="flex flex-wrap items-center justify-between gap-4 mb-8">
    <div>
        <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Отчёты и аналитика</h1>
        <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">{{ $start->translatedFormat('d F') }} — {{ $end->translatedFormat('d F Y') }}</p>
    </div>
    <div class="flex items-center gap-1 p-1 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl shadow-sm">
        @foreach($periods as $p => $label)
        <a href="{{ route('reports.index', ['period' => $p]) }}"
           class="px-4 py-1.5 text-xs font-semibold rounded-lg transition-all
                  {{ $period === $p
                    ? 'bg-slate-900 dark:bg-white text-white dark:text-slate-900 shadow-sm'
                    : 'text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-white' }}">
            {{ $label }}
        </a>
        @endforeach
    </div>
</div>

{{-- ── Primary KPI row ──────────────────────────────────────────────────── --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-4">

    {{-- Revenue --}}
    <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm p-5 group hover:shadow-md transition-shadow">
        <div class="flex items-start justify-between mb-4">
            <div class="w-10 h-10 rounded-xl bg-emerald-50 dark:bg-emerald-900/30 border border-emerald-100 dark:border-emerald-800/50 flex items-center justify-center">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-emerald-600 dark:text-emerald-400">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
                </svg>
            </div>
            <a href="{{ route('reports.revenue', ['period' => $period]) }}" class="opacity-0 group-hover:opacity-100 text-xs text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 transition-all">
                Подробнее →
            </a>
        </div>
        <p class="text-xs font-semibold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-1">Выручка</p>
        <p class="text-2xl font-black text-slate-900 dark:text-white tabular-nums">{{ number_format($roomRevenue / 1000000, 2) }}<span class="text-sm font-semibold text-slate-400 dark:text-slate-500 ml-1">млн</span></p>
    </div>

    {{-- Net income --}}
    <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm p-5 group hover:shadow-md transition-shadow">
        <div class="flex items-start justify-between mb-4">
            <div class="w-10 h-10 rounded-xl {{ $netIncome >= 0 ? 'bg-blue-50 dark:bg-blue-900/30 border-blue-100 dark:border-blue-800/50' : 'bg-red-50 dark:bg-red-900/30 border-red-100 dark:border-red-800/50' }} border flex items-center justify-center">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 {{ $netIncome >= 0 ? 'text-blue-600 dark:text-blue-400' : 'text-red-500' }}">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18 9 11.25l4.306 4.306a11.95 11.95 0 0 1 5.814-5.518l2.74-1.22m0 0-5.94-2.281m5.94 2.28-2.28 5.941"/>
                </svg>
            </div>
        </div>
        <p class="text-xs font-semibold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-1">Чистый доход</p>
        <p class="text-2xl font-black tabular-nums {{ $netIncome >= 0 ? 'text-slate-900 dark:text-white' : 'text-red-500' }}">{{ $netIncome >= 0 ? '' : '−' }}{{ number_format(abs($netIncome) / 1000000, 2) }}<span class="text-sm font-semibold text-slate-400 dark:text-slate-500 ml-1">млн</span></p>
    </div>

    {{-- ADR --}}
    <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm p-5 group hover:shadow-md transition-shadow">
        <div class="flex items-start justify-between mb-4">
            <div class="w-10 h-10 rounded-xl bg-violet-50 dark:bg-violet-900/30 border border-violet-100 dark:border-violet-800/50 flex items-center justify-center">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-violet-600 dark:text-violet-400">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z"/>
                </svg>
            </div>
        </div>
        <p class="text-xs font-semibold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-1">ADR</p>
        <p class="text-2xl font-black text-slate-900 dark:text-white tabular-nums">{{ number_format($adr, 0, '.', ' ') }}</p>
        <p class="text-xs text-slate-400 dark:text-slate-500 mt-0.5">сум / ночь</p>
    </div>

    {{-- RevPAR --}}
    <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm p-5 group hover:shadow-md transition-shadow">
        <div class="flex items-start justify-between mb-4">
            <div class="w-10 h-10 rounded-xl bg-amber-50 dark:bg-amber-900/30 border border-amber-100 dark:border-amber-800/50 flex items-center justify-center">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-amber-600 dark:text-amber-400">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21"/>
                </svg>
            </div>
        </div>
        <p class="text-xs font-semibold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-1">RevPAR</p>
        <p class="text-2xl font-black text-slate-900 dark:text-white tabular-nums">{{ number_format($revpar, 0, '.', ' ') }}</p>
        <p class="text-xs text-slate-400 dark:text-slate-500 mt-0.5">сум / номер / день</p>
    </div>
</div>

{{-- ── Secondary KPI strip ──────────────────────────────────────────────── --}}
<div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-8">
    @foreach([
        ['label' => 'Ночей продано',  'value' => $nightsSold,    'fmt' => false],
        ['label' => 'Бронирований',   'value' => $totalBookings, 'fmt' => false],
        ['label' => 'Новых гостей',   'value' => $totalGuests,   'fmt' => false],
        ['label' => 'Расходы',        'value' => $totalExpenses, 'fmt' => true],
    ] as $stat)
    <div class="bg-slate-900 dark:bg-slate-700 rounded-xl px-4 py-3.5">
        <p class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">{{ $stat['label'] }}</p>
        <p class="text-xl font-black text-white tabular-nums">
            @if($stat['fmt'])
                {{ number_format($stat['value'] / 1000000, 1) }}<span class="text-sm font-semibold text-slate-400 ml-0.5">М</span>
            @else
                {{ number_format($stat['value']) }}
            @endif
        </p>
    </div>
    @endforeach
</div>

{{-- ── Report cards ────────────────────────────────────────────────────── --}}
@php
$reports = [
    ['route'=>'reports.revenue',   'title'=>'Выручка',              'desc'=>'Платежи с разбивкой по дате и гостю',     'accent'=>'emerald', 'export'=>true,
     'icon'=>'M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm3 0h.008v.008H18V10.5Zm-12 0h.008v.008H6V10.5Z'],
    ['route'=>'reports.occupancy', 'title'=>'Загрузка номеров',     'desc'=>'Ежедневный % занятости + диаграмма',      'accent'=>'blue',    'export'=>true,
     'icon'=>'M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21m-3.75 3.75h.008v.008h-.008v-.008Zm0 3h.008v.008h-.008v-.008Zm0 3h.008v.008h-.008v-.008Z'],
    ['route'=>'reports.forecast',  'title'=>'Прогноз загрузки',     'desc'=>'90-дневный тепловой календарь',           'accent'=>'indigo',  'export'=>false,
     'icon'=>'M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5'],
    ['route'=>'reports.guests',    'title'=>'Статистика гостей',    'desc'=>'Новые/повторные, топ национальностей',    'accent'=>'violet',  'export'=>false,
     'icon'=>'M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z'],
    ['route'=>'reports.expenses',  'title'=>'Расходы',              'desc'=>'Структура расходов по категориям',        'accent'=>'orange',  'export'=>true,
     'icon'=>'M9 14.25l6-6m4.5-3.493V21.75l-3.75-1.5-3.75 1.5-3.75-1.5-3.75 1.5V4.757c0-1.108.806-2.057 1.907-2.185a48.507 48.507 0 0 1 11.186 0c1.1.128 1.907 1.077 1.907 2.185Z'],
    ['route'=>'reports.unpaid',    'title'=>'Неоплаченные брони',   'desc'=>'Активные бронирования с задолженностью', 'accent'=>'red',     'export'=>true,
     'icon'=>'M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z'],
    ['route'=>'reports.sources',   'title'=>'Источники броней',     'desc'=>'Персонал vs клиентский портал',          'accent'=>'slate',   'export'=>false,
     'icon'=>'M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z'],
];
$accent = [
    'emerald' => ['icon'=>'text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-900/30',  'btn'=>'bg-emerald-600 hover:bg-emerald-700 text-white', 'bar'=>'bg-emerald-500'],
    'blue'    => ['icon'=>'text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-900/30',              'btn'=>'bg-blue-600 hover:bg-blue-700 text-white',     'bar'=>'bg-blue-500'],
    'indigo'  => ['icon'=>'text-indigo-600 dark:text-indigo-400 bg-indigo-50 dark:bg-indigo-900/30',      'btn'=>'bg-indigo-600 hover:bg-indigo-700 text-white', 'bar'=>'bg-indigo-500'],
    'violet'  => ['icon'=>'text-violet-600 dark:text-violet-400 bg-violet-50 dark:bg-violet-900/30',      'btn'=>'bg-violet-600 hover:bg-violet-700 text-white', 'bar'=>'bg-violet-500'],
    'orange'  => ['icon'=>'text-orange-600 dark:text-orange-400 bg-orange-50 dark:bg-orange-900/30',      'btn'=>'bg-orange-500 hover:bg-orange-600 text-white', 'bar'=>'bg-orange-500'],
    'red'     => ['icon'=>'text-red-600 dark:text-red-400 bg-red-50 dark:bg-red-900/30',                  'btn'=>'bg-red-600 hover:bg-red-700 text-white',       'bar'=>'bg-red-500'],
    'slate'   => ['icon'=>'text-slate-600 dark:text-slate-400 bg-slate-100 dark:bg-slate-700',            'btn'=>'bg-slate-800 hover:bg-slate-900 dark:bg-slate-700 dark:hover:bg-slate-600 text-white', 'bar'=>'bg-slate-500'],
];
@endphp

<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-3">
    @foreach($reports as $r)
    @php $a = $accent[$r['accent']]; @endphp
    <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl p-5 flex flex-col gap-4 hover:shadow-lg hover:-translate-y-0.5 transition-all duration-200">
        <div class="flex items-start gap-3">
            <div class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0 {{ $a['icon'] }}">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="{{ $r['icon'] }}"/>
                </svg>
            </div>
            <div class="flex-1 min-w-0">
                <p class="font-bold text-slate-900 dark:text-white text-sm leading-tight">{{ $r['title'] }}</p>
                <p class="text-xs text-slate-400 dark:text-slate-500 mt-1 leading-relaxed">{{ $r['desc'] }}</p>
            </div>
        </div>

        <div class="flex items-center gap-2 mt-auto pt-1">
            <a href="{{ route($r['route'], ['period' => $period]) }}"
               class="flex-1 text-center py-2 text-xs font-bold rounded-xl transition-colors {{ $a['btn'] }}">
                Открыть
            </a>
            @if($r['export'])
            <a href="{{ route($r['route'], ['period' => $period, 'export' => 'csv']) }}"
               class="py-2 px-3 text-xs font-semibold rounded-xl border border-slate-200 dark:border-slate-600 text-slate-500 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">CSV</a>
            <a href="{{ route($r['route'], ['period' => $period, 'export' => 'pdf']) }}"
               class="py-2 px-3 text-xs font-semibold rounded-xl border border-slate-200 dark:border-slate-600 text-slate-500 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">PDF</a>
            @endif
        </div>
    </div>
    @endforeach
</div>

@endsection
