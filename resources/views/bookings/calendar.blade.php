@extends('layouts.app')

@section('title', 'Календарь бронирований')

@section('content')
<div class="flex items-center justify-between mb-4">
    <div class="flex items-center gap-3">
        <a href="{{ route('bookings.index') }}" class="text-sm text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200 flex items-center gap-1.5">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18"/></svg>
            Бронирования
        </a>
        <span class="text-slate-300 dark:text-slate-600">/</span>
        <h1 class="text-xl font-bold text-slate-900 dark:text-white">Календарь</h1>
    </div>
    <div class="flex items-center gap-2">
        <a href="{{ route('bookings.calendar', ['from' => $from->copy()->subDays(30)->toDateString()]) }}"
           class="p-2 rounded-lg border border-slate-200 dark:border-slate-700 text-slate-500 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors" title="На 30 дней назад">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5"/></svg>
        </a>
        <a href="{{ route('bookings.calendar') }}"
           class="px-3 py-2 text-xs font-semibold rounded-lg border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors">
            Сегодня
        </a>
        <form method="GET" action="{{ route('bookings.calendar') }}" class="flex items-center gap-2">
            <input type="date" name="from" value="{{ $from->toDateString() }}"
                   class="border border-slate-200 dark:border-slate-700 dark:bg-slate-800 dark:text-white rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            <button type="submit" class="px-4 py-2 bg-blue-600 text-white text-sm font-semibold rounded-lg hover:bg-blue-700 transition-colors">Перейти</button>
        </form>
        <a href="{{ route('bookings.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-slate-800 dark:bg-slate-600 text-white text-sm font-semibold rounded-lg hover:bg-slate-700 transition-colors">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
            Новое
        </a>
        <button onclick="document.getElementById('info-modal').classList.remove('hidden')"
                class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 dark:hover:bg-slate-600 text-slate-600 dark:text-slate-300 text-sm font-medium transition-colors" title="Справка">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M9.879 7.519c1.171-1.025 3.071-1.025 4.242 0 1.172 1.025 1.172 2.687 0 3.712-.203.179-.43.326-.67.442-.745.361-1.45.999-1.45 1.827v.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 5.25h.008v.008H12v-.008Z"/></svg>
            Справка
        </button>
    </div>
</div>

{{-- Today's compact status strip --}}
@php $pct = $todayStats['total'] > 0 ? round($todayStats['occupied'] / $todayStats['total'] * 100) : 0; @endphp
<div class="mb-3 bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 px-4 py-3 flex flex-wrap items-center gap-x-6 gap-y-2">

    {{-- Stat pills --}}
    <div class="flex items-center gap-1.5">
        <span class="w-2 h-2 rounded-full bg-emerald-500 flex-shrink-0"></span>
        <span class="text-sm font-bold text-emerald-600 dark:text-emerald-400">{{ $todayStats['available'] }}</span>
        <span class="text-xs text-slate-400">свободно</span>
    </div>
    <div class="flex items-center gap-1.5">
        <span class="w-2 h-2 rounded-full bg-blue-500 flex-shrink-0"></span>
        <span class="text-sm font-bold text-blue-600 dark:text-blue-400">{{ $todayStats['occupied'] }}</span>
        <span class="text-xs text-slate-400">заселено</span>
    </div>
    <div class="flex items-center gap-1.5">
        <span class="w-2 h-2 rounded-full bg-orange-400 flex-shrink-0"></span>
        <span class="text-sm font-bold text-orange-500 dark:text-orange-400">{{ $todayStats['checking_out'] }}</span>
        <span class="text-xs text-slate-400">выезд</span>
    </div>
    <div class="flex items-center gap-1.5">
        <span class="w-2 h-2 rounded-full bg-purple-500 flex-shrink-0"></span>
        <span class="text-sm font-bold text-purple-600 dark:text-purple-400">{{ $todayStats['checking_in'] }}</span>
        <span class="text-xs text-slate-400">заезд</span>
    </div>

    {{-- Occupancy bar --}}
    <div class="flex items-center gap-2">
        <div class="w-24 h-1.5 bg-slate-100 dark:bg-slate-700 rounded-full overflow-hidden">
            <div class="h-full rounded-full {{ $pct >= 80 ? 'bg-red-500' : ($pct >= 50 ? 'bg-amber-400' : 'bg-emerald-500') }}" style="width:{{ $pct }}%"></div>
        </div>
        <span class="text-xs text-slate-400">{{ $pct }}%</span>
    </div>

    {{-- Divider --}}
    <div class="hidden sm:block w-px h-4 bg-slate-200 dark:bg-slate-600"></div>

    {{-- Available room badges — updated by JS filter --}}
    <div id="avail-strip" class="flex flex-wrap items-center gap-1"></div>

</div>

{{-- Filter bar --}}
<div id="type-filter" class="mb-3 hidden flex-wrap gap-2">
    <button type="button" data-type="all" class="filter-btn active px-3 py-2 rounded-lg border text-xs font-medium transition-colors" id="filter-btn-all">Все номера</button>
</div>

{{-- Drag hint --}}
<p id="drag-hint" class="mb-2 text-xs text-slate-400 hidden">
    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-3.5 h-3.5 inline mr-1"><path stroke-linecap="round" stroke-linejoin="round" d="M7.5 21 3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 3M21 7.5H7.5"/></svg>
    Перетащите блок для изменения дат · Тяните правый край для изменения длительности · Кликните дважды на пустые ячейки строки для нового бронирования · ПКМ — быстрые действия
</p>

{{-- Range selection hint --}}
<div id="sel-hint" class="hidden fixed bottom-6 left-1/2 -translate-x-1/2 z-50 px-4 py-2.5 bg-blue-600 text-white text-sm font-semibold rounded-xl shadow-xl flex items-center gap-2">
    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 flex-shrink-0"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5"/></svg>
    Выберите дату выезда в той же строке · ESC — отмена
</div>

<div id="gantt-root" class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm overflow-auto" style="max-height:calc(100vh - 220px);">
    <div class="flex items-center justify-center h-48 text-slate-400 text-sm">
        <svg class="w-6 h-6 animate-spin mr-2" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
        Строим календарь…
    </div>
</div>

{{-- Toast --}}
<div id="gantt-toast" class="fixed bottom-6 right-6 z-50 hidden">
    <div class="flex items-center gap-3 px-4 py-3 rounded-xl shadow-lg text-sm font-medium text-white"></div>
</div>

{{-- ── Info modal ───────────────────────────────────────────────────────────── --}}
<div id="info-modal" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4" style="background:rgba(0,0,0,.5);backdrop-filter:blur(2px);">
    <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-2xl w-full max-w-md overflow-hidden max-h-[90vh] flex flex-col">
        <div class="px-5 py-4 border-b border-slate-100 dark:border-slate-700 flex items-center justify-between flex-shrink-0">
            <h3 class="font-bold text-slate-900 dark:text-slate-100">Справка по календарю</h3>
            <button onclick="document.getElementById('info-modal').classList.add('hidden')" class="w-8 h-8 flex items-center justify-center rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <div class="p-5 space-y-5 overflow-y-auto">
            <div>
                <p class="text-xs font-semibold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-2.5">Статусы бронирований</p>
                <div class="grid grid-cols-2 gap-2">
                    <span class="flex items-center gap-2"><span class="w-3 h-3 rounded bg-amber-400 flex-shrink-0"></span><span class="text-xs text-slate-600 dark:text-slate-300">Ожидает</span></span>
                    <span class="flex items-center gap-2"><span class="w-3 h-3 rounded bg-blue-500 flex-shrink-0"></span><span class="text-xs text-slate-600 dark:text-slate-300">Подтверждено</span></span>
                    <span class="flex items-center gap-2"><span class="w-3 h-3 rounded bg-emerald-500 flex-shrink-0"></span><span class="text-xs text-slate-600 dark:text-slate-300">Заселён</span></span>
                    <span class="flex items-center gap-2"><span class="w-3 h-3 rounded bg-purple-500 flex-shrink-0"></span><span class="text-xs text-slate-600 dark:text-slate-300">Запрос</span></span>
                    <span class="flex items-center gap-2"><span class="w-3 h-3 rounded bg-slate-500 flex-shrink-0"></span><span class="text-xs text-slate-600 dark:text-slate-300">Выехал</span></span>
                    <span class="flex items-center gap-2"><span class="w-3 h-3 rounded border border-slate-400 flex-shrink-0" style="background:repeating-linear-gradient(45deg,#cbd5e1 0,#cbd5e1 3px,#e2e8f0 3px,#e2e8f0 6px)"></span><span class="text-xs text-slate-600 dark:text-slate-300">Заблокировано</span></span>
                </div>
            </div>
            <div>
                <p class="text-xs font-semibold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-2.5">Точка оплаты (верхний правый угол блока)</p>
                <div class="space-y-1.5">
                    <span class="flex items-center gap-2.5"><span class="w-3 h-3 rounded-full bg-emerald-500 flex-shrink-0 ring-2 ring-white dark:ring-slate-800"></span><span class="text-xs text-slate-600 dark:text-slate-300">Полностью оплачено</span></span>
                    <span class="flex items-center gap-2.5"><span class="w-3 h-3 rounded-full bg-amber-400 flex-shrink-0 ring-2 ring-white dark:ring-slate-800"></span><span class="text-xs text-slate-600 dark:text-slate-300">Частичная оплата (≥ 50%)</span></span>
                    <span class="flex items-center gap-2.5"><span class="w-3 h-3 rounded-full bg-red-500 flex-shrink-0 ring-2 ring-white dark:ring-slate-800"></span><span class="text-xs text-slate-600 dark:text-slate-300">Не оплачено / долг</span></span>
                </div>
            </div>
            <div>
                <p class="text-xs font-semibold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-2.5">Статус номера (цветная точка)</p>
                <div class="grid grid-cols-2 gap-2">
                    <span class="flex items-center gap-2"><span class="w-2.5 h-2.5 rounded-full bg-green-500 flex-shrink-0"></span><span class="text-xs text-slate-600 dark:text-slate-300">Свободен</span></span>
                    <span class="flex items-center gap-2"><span class="w-2.5 h-2.5 rounded-full bg-red-500 flex-shrink-0"></span><span class="text-xs text-slate-600 dark:text-slate-300">Занят</span></span>
                    <span class="flex items-center gap-2"><span class="w-2.5 h-2.5 rounded-full bg-orange-400 flex-shrink-0"></span><span class="text-xs text-slate-600 dark:text-slate-300">Грязный</span></span>
                    <span class="flex items-center gap-2"><span class="w-2.5 h-2.5 rounded-full bg-yellow-400 flex-shrink-0"></span><span class="text-xs text-slate-600 dark:text-slate-300">Уборка</span></span>
                    <span class="flex items-center gap-2"><span class="w-2.5 h-2.5 rounded-full bg-blue-400 flex-shrink-0"></span><span class="text-xs text-slate-600 dark:text-slate-300">Проверен</span></span>
                    <span class="flex items-center gap-2"><span class="w-2.5 h-2.5 rounded-full bg-gray-400 flex-shrink-0"></span><span class="text-xs text-slate-600 dark:text-slate-300">Ремонт</span></span>
                </div>
            </div>
            <div>
                <p class="text-xs font-semibold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-2.5">Управление</p>
                <div class="space-y-1.5 text-xs text-slate-600 dark:text-slate-400">
                    <p class="flex items-start gap-2"><span class="text-slate-400 flex-shrink-0">→</span>Перетащите блок бронирования для изменения дат</p>
                    <p class="flex items-start gap-2"><span class="text-slate-400 flex-shrink-0">→</span>Тяните правый край блока для изменения длительности</p>
                    <p class="flex items-start gap-2"><span class="text-slate-400 flex-shrink-0">→</span>Клик на пустую ячейку → выберите вторую дату → меню действий (бронирование / аренда / блокировка)</p>
                    <p class="flex items-start gap-2"><span class="text-slate-400 flex-shrink-0">→</span>ПКМ на блок бронирования — быстрые действия (смена статуса)</p>
                    <p class="flex items-start gap-2"><span class="text-slate-400 flex-shrink-0">→</span>Клик на серый блок — удалить блокировку</p>
                    <p class="flex items-start gap-2"><span class="text-slate-400 flex-shrink-0">→</span>Скролл влево — прошлые месяцы · Скролл вправо — будущие месяцы</p>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ── Action modal (shown after date range selection) ─────────────────────── --}}
<div id="action-modal" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4" style="background:rgba(0,0,0,.5);backdrop-filter:blur(2px);">
    <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-2xl w-full max-w-sm overflow-hidden">
        {{-- Header --}}
        <div id="action-modal-header" class="px-5 py-4 border-b border-slate-100 dark:border-slate-700">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-xs font-semibold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-0.5">Номер <span id="am-room-num" class="text-slate-700 dark:text-slate-200"></span></p>
                    <p class="text-base font-bold text-slate-900 dark:text-slate-100" id="am-dates"></p>
                    <p class="text-sm text-slate-500 dark:text-slate-400" id="am-nights"></p>
                </div>
                <button onclick="closeActionModal()" class="w-8 h-8 flex items-center justify-center rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors flex-shrink-0 mt-0.5">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg>
                </button>
            </div>
        </div>

        {{-- Actions --}}
        <div id="action-modal-main" class="p-4 space-y-2">
            {{-- Booking --}}
            <a id="am-booking-btn" href="#"
               class="flex items-center gap-4 p-4 rounded-xl border-2 border-blue-200 dark:border-blue-800 bg-blue-50 dark:bg-blue-900/20 hover:border-blue-400 dark:hover:border-blue-600 hover:bg-blue-100 dark:hover:bg-blue-900/40 transition-all group cursor-pointer">
                <div class="w-10 h-10 rounded-xl bg-blue-100 dark:bg-blue-900/40 flex items-center justify-center flex-shrink-0 group-hover:bg-blue-200 dark:group-hover:bg-blue-900/60 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-blue-600 dark:text-blue-400"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z"/></svg>
                </div>
                <div class="flex-1">
                    <p class="text-sm font-bold text-blue-700 dark:text-blue-300">Бронирование</p>
                    <p class="text-xs text-blue-500 dark:text-blue-400">Стандартное бронирование с профилем гостя</p>
                </div>
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4 text-blue-400"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5"/></svg>
            </a>

            {{-- Rent --}}
            <a id="am-rent-btn" href="#"
               class="flex items-center gap-4 p-4 rounded-xl border-2 border-emerald-200 dark:border-emerald-800 bg-emerald-50 dark:bg-emerald-900/20 hover:border-emerald-400 dark:hover:border-emerald-600 hover:bg-emerald-100 dark:hover:bg-emerald-900/40 transition-all group cursor-pointer">
                <div class="w-10 h-10 rounded-xl bg-emerald-100 dark:bg-emerald-900/40 flex items-center justify-center flex-shrink-0 group-hover:bg-emerald-200 dark:group-hover:bg-emerald-900/60 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-emerald-600 dark:text-emerald-400"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75"/></svg>
                </div>
                <div class="flex-1">
                    <p class="text-sm font-bold text-emerald-700 dark:text-emerald-300">Аренда</p>
                    <p class="text-xs text-emerald-500 dark:text-emerald-400">Быстрая аренда — без лишних шагов</p>
                </div>
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4 text-emerald-400"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5"/></svg>
            </a>

            {{-- Block --}}
            <button type="button" onclick="showBlockForm()"
               class="w-full flex items-center gap-4 p-4 rounded-xl border-2 border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-700/40 hover:border-slate-300 dark:hover:border-slate-600 hover:bg-slate-100 dark:hover:bg-slate-700 transition-all group text-left">
                <div class="w-10 h-10 rounded-xl bg-slate-100 dark:bg-slate-700 flex items-center justify-center flex-shrink-0 group-hover:bg-slate-200 dark:group-hover:bg-slate-600 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-slate-500 dark:text-slate-400"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z"/></svg>
                </div>
                <div class="flex-1">
                    <p class="text-sm font-bold text-slate-700 dark:text-slate-200">Заблокировать</p>
                    <p class="text-xs text-slate-400 dark:text-slate-500">Закрыть даты без бронирования</p>
                </div>
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4 text-slate-400"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5"/></svg>
            </button>
        </div>
    </div>
</div>

{{-- Block reason sub-form (replaces action-modal-main) --}}
<div id="block-form-modal" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4" style="background:rgba(0,0,0,.5);backdrop-filter:blur(2px);">
    <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-2xl w-full max-w-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-100 dark:border-slate-700 flex items-center gap-3">
            <button onclick="closeBlockForm()" class="w-8 h-8 flex items-center justify-center rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors flex-shrink-0">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18"/></svg>
            </button>
            <div>
                <p class="text-sm font-bold text-slate-900 dark:text-slate-100">Блокировка дат</p>
                <p class="text-xs text-slate-400 dark:text-slate-500">Ном. <span id="bf-room"></span> · <span id="bf-dates"></span></p>
            </div>
        </div>
        <div class="p-4">
            <p class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-3">Причина</p>
            <div class="space-y-2" id="block-reasons">
                @php
            $blockReasons = [
                ['cleaning',    '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 0 0-3.09 3.09ZM18.259 8.715 18 9.75l-.259-1.035a3.375 3.375 0 0 0-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 0 0 2.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 0 0 2.456 2.456L21.75 6l-1.035.259a3.375 3.375 0 0 0-2.456 2.456Z"/></svg>', 'Уборка / Санитарная обработка', 'text-blue-500'],
                ['maintenance', '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M11.42 15.17 17.25 21A2.652 2.652 0 0 0 21 17.25l-5.877-5.877M11.42 15.17l2.496-3.03c.317-.384.74-.626 1.208-.766M11.42 15.17l-4.655 5.653a2.548 2.548 0 1 1-3.586-3.586l5.654-4.654m5.58-5.58 1.857-1.857a2.652 2.652 0 0 1 3.535 3.536L19.41 9.75m-5.58-5.58L6.474 2.614a2.25 2.25 0 0 0-3.182 3.182L5.5 7.904m0 0 2.614 2.614M5.5 7.904l2.614 2.614m3.617 7.578.707-.707-5.58-5.58-.707.707"/></svg>', 'Ремонт / Техническое обслуживание', 'text-orange-500'],
                ['owner',       '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25"/></svg>', 'Использование владельцем', 'text-purple-500'],
                ['admin',       '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25ZM6.75 12h.008v.008H6.75V12Zm0 3h.008v.008H6.75V15Zm0 3h.008v.008H6.75V18Z"/></svg>', 'Административная бронь', 'text-slate-500'],
                ['other',       '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L6.832 19.82a4.5 4.5 0 0 1-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 0 1 1.13-1.897L16.863 4.487Zm0 0L19.5 7.125"/></svg>', 'Другое', 'text-slate-400'],
            ];
            @endphp
            @foreach($blockReasons as [$val, $icon, $label, $iconClass])
                <label class="flex items-center gap-3 p-3 rounded-xl border-2 border-transparent hover:border-slate-200 dark:hover:border-slate-600 hover:bg-slate-50 dark:hover:bg-slate-700/50 cursor-pointer transition-all has-[:checked]:border-slate-800 dark:has-[:checked]:border-slate-300 has-[:checked]:bg-slate-50 dark:has-[:checked]:bg-slate-700/50">
                    <input type="radio" name="block_reason" value="{{ $val }}" class="sr-only" {{ $val === 'cleaning' ? 'checked' : '' }}>
                    <span class="{{ $iconClass }} flex-shrink-0">{!! $icon !!}</span>
                    <span class="text-sm font-semibold text-slate-700 dark:text-slate-200">{{ $label }}</span>
                    <span class="ml-auto w-4 h-4 rounded-full border-2 border-slate-300 dark:border-slate-600 flex items-center justify-center flex-shrink-0 reason-dot">
                        <span class="w-2 h-2 rounded-full bg-slate-800 dark:bg-white hidden reason-dot-inner"></span>
                    </span>
                </label>
            @endforeach
            </div>

            <div id="block-notes-wrap" class="mt-3 hidden">
                <textarea id="block-notes" rows="2" placeholder="Уточните причину…"
                          class="w-full border border-slate-200 dark:border-slate-600 dark:bg-slate-700 dark:text-white rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-slate-500 resize-none placeholder-slate-400"></textarea>
            </div>

            <div id="block-error" class="hidden mt-2 text-xs text-red-600 dark:text-red-400 font-semibold"></div>

            <button id="block-submit-btn" onclick="submitBlock()"
                    class="mt-4 w-full inline-flex items-center justify-center gap-2 px-4 py-3 bg-slate-800 dark:bg-slate-200 text-white dark:text-slate-900 text-sm font-bold rounded-xl hover:bg-slate-700 dark:hover:bg-slate-300 transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z"/></svg>
                Заблокировать даты
            </button>
        </div>
    </div>
</div>

{{-- Conflict modal --}}
<div id="conflict-modal" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4" style="background:rgba(0,0,0,.45);">
    <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-2xl w-full max-w-sm p-6">
        <div class="flex items-start gap-3 mb-4">
            <div class="w-10 h-10 rounded-full bg-red-100 dark:bg-red-900/40 flex items-center justify-center flex-shrink-0">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5 text-red-600 dark:text-red-400"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z"/></svg>
            </div>
            <div>
                <h3 class="font-bold text-slate-900 dark:text-slate-100 text-base">Номер недоступен</h3>
                <p id="conflict-room" class="text-sm text-slate-500 dark:text-slate-400 mt-0.5"></p>
            </div>
        </div>
        <div id="conflict-body" class="space-y-2 mb-5 text-sm"></div>
        <button onclick="document.getElementById('conflict-modal').classList.add('hidden')"
                class="w-full px-4 py-2.5 bg-slate-800 dark:bg-slate-600 hover:bg-slate-700 text-white font-semibold rounded-xl transition-colors text-sm">
            Понятно
        </button>
    </div>
</div>

{{-- Hover popover --}}
<div id="booking-popover" class="fixed z-40 hidden pointer-events-none" style="min-width:220px;max-width:260px;"></div>

{{-- Context menu --}}
<div id="ctx-menu" class="fixed z-50 hidden rounded-xl shadow-xl border py-1" style="min-width:180px;"></div>

<style>
.day-cell.drag-over { background: rgba(59,130,246,.18) !important; outline: 2px dashed #3b82f6; outline-offset: -2px; }
.booking-block[draggable="true"] { cursor: grab; }
.booking-block[draggable="true"]:active { cursor: grabbing; }
.booking-block.dragging { opacity: .35 !important; }
.resize-handle { position:absolute;right:0;top:0;bottom:0;width:9px;cursor:col-resize;border-radius:0 6px 6px 0;background:rgba(255,255,255,.15);z-index:6; }
.resize-handle:hover { background:rgba(255,255,255,.35); }
.filter-btn { position:relative; overflow:hidden; border-color: #e2e8f0; color: #475569; background: #fff; }
.filter-btn.active { color: #fff !important; border-color: transparent !important; }
.filter-btn.active .filter-btn-fill { opacity: 1 !important; }
.filter-btn-fill { position:absolute;inset:0;z-index:0;opacity:0;transition:opacity .2s; }
.filter-btn-label { position:relative;z-index:1;display:flex;flex-direction:column;align-items:flex-start;gap:1px; }
.filter-btn-bar-track { position:absolute;bottom:0;left:0;right:0;height:3px;background:rgba(0,0,0,.1);z-index:1; }
.filter-btn-bar { position:absolute;bottom:0;left:0;height:3px;transition:width .3s;z-index:2;opacity:.7; }
.filter-btn.active .filter-btn-bar { opacity:1; background:rgba(255,255,255,.5) !important; }
.dark .filter-btn { border-color: #334155; color: #94a3b8; }
.dark .filter-btn.active { color: #fff !important; }
.dark .filter-btn.active { background: #60a5fa; color: #fff; border-color: #60a5fa; }
.ctx-item { display:block;width:100%;text-align:left;padding:7px 14px;font-size:13px;cursor:pointer;text-decoration:none; }
.ctx-item:hover { background:rgba(0,0,0,.05); }
.ctx-danger:hover { background:rgba(239,68,68,.08); color:#ef4444; }
</style>

<script>
(function () {
    const rooms           = @json($roomsJson);
    let   bookings        = @json($bookingsJson);
    let   blocks          = @json($blocksJson);
    const FROM_STR        = '{{ $from->toDateString() }}';
    let   calFromStr      = FROM_STR;
    const TODAY           = '{{ today()->toDateString() }}';
    const INIT_DAYS       = {{ $days }};
    let   loadedDays      = INIT_DAYS;
    const TOTAL_ROOMS     = {{ $totalRooms }};
    const CSRF            = document.querySelector('meta[name="csrf-token"]').content;
    const AVAILABLE_ROOMS = @json($todayStats['available_rooms']);
    const BASE_CREATE_URL = '{{ route('bookings.create') }}';
    const CAL_DATA_URL    = '{{ route('bookings.calendar.data') }}';
    const TOMORROW        = (d => { d.setDate(d.getDate()+1); return d.toISOString().split('T')[0]; })(new Date(TODAY));

    const CELL_W      = 36;
    const ROW_H       = 44;
    const HDR_H       = 60;
    const MONTH_HDR_H = 28;
    const LABEL_W     = 175;
    const MONTH_NAMES = ['Январь','Февраль','Март','Апрель','Май','Июнь','Июль','Август','Сентябрь','Октябрь','Ноябрь','Декабрь'];

    const draggableStatuses = new Set(['pending', 'confirmed', 'inquiry']);

    const statusColors = { pending:'#f59e0b', confirmed:'#3b82f6', checked_in:'#10b981', inquiry:'#a855f7', checked_out:'#6b7280', blocked:'#94a3b8' };
    const statusLabels = { pending:'Ожидает', confirmed:'Подтверждён', checked_in:'Заселён', inquiry:'Запрос', checked_out:'Выехал', cancelled:'Отменён', blocked:'Заблокировано' };
    const roomStatusColors = { available:'#22c55e', occupied:'#ef4444', dirty:'#f97316', cleaning:'#eab308', inspected:'#3b82f6', maintenance:'#6b7280' };
    const transitionOptions = { inquiry:['pending','cancelled'], pending:['confirmed','checked_in','cancelled'], confirmed:['checked_in','cancelled'], checked_in:['checked_out','cancelled'] };
    const transitionLabels  = { pending:'Ожидание', confirmed:'Подтвердить', checked_in:'Заселить', checked_out:'Выселить', cancelled:'Отменить' };

    // ── Utilities ──
    function parseDate(s) { const [y,m,d]=s.split('-').map(Number); return new Date(y,m-1,d); }
    function addDays(date, n) { const d=new Date(date); d.setDate(d.getDate()+n); return d; }
    function toDateStr(d) { return [d.getFullYear(),String(d.getMonth()+1).padStart(2,'0'),String(d.getDate()).padStart(2,'0')].join('-'); }
    function daysBetween(a,b) { return Math.round((parseDate(b)-parseDate(a))/86400000); }
    function fmt(n) { return new Intl.NumberFormat('ru-RU').format(Math.round(n))+' сум'; }
    function fmtPrice(p) {
        if (!p) return '—';
        if (p >= 1000000) return (p % 1000000 === 0 ? (p/1000000) : (p/1000000).toFixed(1)) + 'M';
        if (p >= 1000)    return Math.round(p/1000) + 'K';
        return String(p);
    }
    function plural(n,one,few,many) {
        const m10=n%10,m100=n%100;
        if(m10===1&&m100!==11)return one;
        if([2,3,4].includes(m10)&&![12,13,14].includes(m100))return few;
        return many;
    }

    // ── Date array ──
    let fromDate = parseDate(FROM_STR);
    let   dateStrs = Array.from({length:INIT_DAYS},(_,i)=>toDateStr(addDays(fromDate,i)));

    // ── Occupancy map (confirmed bookings only) ──
    const occupancyMap = {};
    dateStrs.forEach(ds => {
        occupancyMap[ds] = bookings.filter(b=>['pending','confirmed','checked_in'].includes(b.status)&&b.check_in<=ds&&b.check_out>ds).length;
    });

    // ── Booking lookup ──
    const bookingById = {};
    bookings.forEach(b=>{ bookingById[b.id]=b; });

    // ── Theme colours ──
    const isDark   = document.documentElement.classList.contains('dark');
    const hdrBg    = isDark?'#1e293b':'#f8fafc';
    const hdrBdr   = isDark?'#334155':'#e2e8f0';
    const labelC   = isDark?'#94a3b8':'#475569';
    const textPri  = isDark?'#f1f5f9':'#1e293b';
    const textSec  = isDark?'#64748b':'#94a3b8';
    const cellBdr  = isDark?'#1e293b':'#e2e8f0';
    const rowBg1   = isDark?'#1e293b':'#ffffff';
    const rowBg2   = isDark?'#0f172a':'#f8fafc';
    const rowBdr   = isDark?'#334155':'#f1f5f9';
    const wkndBg   = isDark?'rgba(30,58,95,.25)':'rgba(219,234,254,.45)';
    const todayHdr = isDark?'#1e3a5f':'#dbeafe';
    const todayC   = isDark?'#60a5fa':'#1d4ed8';
    const todayCell= isDark?'rgba(30,58,95,.18)':'rgba(219,234,254,.3)';
    const popBg    = isDark?'#1e293b':'#fff';
    const popBdr   = isDark?'#334155':'#e2e8f0';
    const ctxBg    = isDark?'#1e293b':'#fff';
    const ctxBdr   = isDark?'#334155':'#e2e8f0';
    const ctxText  = isDark?'#f1f5f9':'#1e293b';

    // ── Build HTML ──
    let html = `<div id="gantt-inner" style="position:relative;min-width:${LABEL_W+INIT_DAYS*CELL_W}px;font-size:12px;user-select:none;">`;

    // ── Month header ──
    html += `<div id="gantt-month-hdr" style="display:flex;height:${MONTH_HDR_H}px;border-bottom:1px solid ${hdrBdr};background:${hdrBg};position:sticky;top:0;z-index:12;">`;
    html += `<div style="width:${LABEL_W}px;flex-shrink:0;border-right:1px solid ${hdrBdr};position:sticky;left:0;background:${hdrBg};z-index:13;"></div>`;
    // Group by month
    (function(){
        let cur=null;
        dateStrs.forEach(ds=>{
            const d=parseDate(ds);
            const y=d.getFullYear(),m=d.getMonth();
            const key=`${y}-${m}`;
            if(!cur||cur.key!==key){
                if(cur)html+=`</div>`;
                const w=CELL_W;
                html+=`<div id="mcell-${y}-${m}" data-y="${y}" data-m="${m}" data-count="1" style="width:${w}px;flex-shrink:0;display:flex;align-items:center;padding:0 10px;font-weight:700;font-size:11px;color:${textPri};border-right:1px solid ${hdrBdr};overflow:hidden;white-space:nowrap;background:${hdrBg};">`;
                html+=`${MONTH_NAMES[m]} ${y}`;
                cur={key,y,m};
            } else {
                // extend — we'll track via data-count then set final width after loop
                cur.count=(cur.count||1)+1;
            }
        });
        if(cur)html+=`</div>`;
    })();
    html+=`</div>`;

    // Fix month cell widths (done after building so we know actual counts)
    // We'll do this in JS after injection

    // ── Date header ──
    html += `<div id="gantt-date-hdr" style="display:flex;height:${HDR_H}px;border-bottom:1px solid ${hdrBdr};background:${hdrBg};position:sticky;top:${MONTH_HDR_H}px;z-index:10;">`;
    html += `<div style="width:${LABEL_W}px;flex-shrink:0;display:flex;align-items:center;padding:0 12px;font-weight:700;color:${labelC};border-right:1px solid ${hdrBdr};position:sticky;left:0;background:${hdrBg};z-index:11;">Номер</div>`;
    dateStrs.forEach(ds=>{
        const d=parseDate(ds);
        const dow=d.getDay();
        const isWeekend=dow===0||dow===6;
        const isToday=ds===TODAY;
        const occ=occupancyMap[ds]||0;
        const free=TOTAL_ROOMS-occ;
        const pct=TOTAL_ROOMS>0?Math.round(occ/TOTAL_ROOMS*100):0;
        const barColor=pct>=80?'#ef4444':pct>=50?'#f59e0b':'#22c55e';
        const freeColor=free===0?'#ef4444':(free<=2?'#f59e0b':'#22c55e');
        const bg=isToday?todayHdr:(isWeekend?wkndBg:'transparent');
        html+=`<div class="day-cell" data-date="${ds}" style="width:${CELL_W}px;flex-shrink:0;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:1px;border-right:1px solid ${cellBdr};background:${bg};padding-bottom:2px;">`;
        html+=`<span style="font-weight:700;font-size:12px;color:${isToday?todayC:(isWeekend?'#7c3aed':labelC)};">${d.getDate()}</span>`;
        html+=`<span style="font-size:9px;color:${isWeekend?'#7c3aed':textSec};">${['Вс','Пн','Вт','Ср','Чт','Пт','Сб'][dow]}</span>`;
        html+=`<div title="${occ}/${TOTAL_ROOMS} занято (${pct}%)" style="width:80%;height:3px;background:${isDark?'#334155':'#e2e8f0'};border-radius:2px;overflow:hidden;margin:1px 0;"><div style="width:${pct}%;height:100%;background:${barColor};border-radius:2px;"></div></div>`;
        html+=`<span title="${free} свободно" style="font-size:8px;font-weight:700;color:${freeColor}; display: none;line-height:1;">${free}св</span>`;
        html+=`</div>`;
    });
    html+=`</div>`;

    // Room rows
    rooms.forEach((room,rIdx)=>{
        const rBg  = rIdx%2===0?rowBg1:rowBg2;
        const lBg  = rIdx%2===0?rowBg1:rowBg2;
        const sDot = roomStatusColors[room.status]||'#6b7280';
        html+=`<div data-room-id="${room.id}" data-room-type="${room.type||''}" style="display:flex;height:${ROW_H}px;border-bottom:1px solid ${rowBdr};background:${rBg};position:relative;">`;

        // Label
        html+=`<div style="width:${LABEL_W}px;flex-shrink:0;display:flex;align-items:center;padding:0 10px;border-right:1px solid ${hdrBdr};gap:6px;background:${lBg};position:sticky;left:0;z-index:5;">`;
        html+=`<span style="display:inline-block;width:8px;height:8px;border-radius:50%;background:${sDot};flex-shrink:0;" title="${room.status}"></span>`;
        html+=`<div style="flex:1;overflow:hidden;min-width:0;">`;
        html+=`<div style="display:flex;align-items:baseline;gap:4px;white-space:nowrap;overflow:hidden;">`;
        html+=`<span style="font-weight:700;color:${textPri};font-size:13px;flex-shrink:0;">№${room.number}</span>`;
        html+=`<span style="color:${textSec};font-size:11px;overflow:hidden;text-overflow:ellipsis;">${room.type||''}</span>`;
        html+=`</div>`;
        html+=`<div style="font-size:9px;color:${textSec};margin-top:1px;display:flex;gap:5px;white-space:nowrap;">`;
        html+=`<span style="font-size:11px;color:${isDark?'#64748b':'#94a3b8'};">${room.capacity}чел</span>`;
        html+=`<span style="color:${isDark?'#475569':'#94a3b8'};">·</span>`;
        // html+=`<span style="font-size:11px;color:${isDark?'#60a5fa':'#3b82f6'};font-weight:600;">${fmtPrice(room.base_price)}/н</span>`;
        html+=`</div>`;
        html+=`</div>`;
        html+=`</div>`;

        // Day cells
        dateStrs.forEach(ds=>{
            const dow=parseDate(ds).getDay();
            const isWeekend=dow===0||dow===6;
            const isToday=ds===TODAY;
            const isPast=ds<TODAY;
            const cellBg=isToday?todayCell:(isWeekend?wkndBg:(isPast?(isDark?'rgba(15,23,42,0.5)':'rgba(241,245,249,0.7)'):'transparent'));
            const cellCursor=isPast?'default':'pointer';
            const cellTitle=isPast?'':`Создать бронирование ${ds}`;
            html+=`<div class="day-cell" data-date="${ds}" data-room-id="${room.id}" style="width:${CELL_W}px;flex-shrink:0;border-right:1px solid ${rowBdr};background:${cellBg};cursor:${cellCursor};" title="${cellTitle}"></div>`;
        });

        // Booking blocks
        bookings.filter(b=>b.room_id===room.id).forEach(b=>{
            const startOff=daysBetween(FROM_STR,b.check_in);
            const endOff  =daysBetween(FROM_STR,b.check_out);
            const cStart  =Math.max(0,startOff);
            const cEnd    =Math.min(loadedDays,endOff);
            if(cEnd<=cStart)return;
            const left    =LABEL_W+cStart*CELL_W+2;
            const width   =(cEnd-cStart)*CELL_W-4;
            const color   =statusColors[b.status]||'#94a3b8';
            const duration=daysBetween(b.check_in,b.check_out);
            const dragOk  =draggableStatuses.has(b.status);
            const isOut   =b.status==='checked_out';
            const payRatio=b.total>0?b.paid/b.total:null;
            const payColor=payRatio===null?null:(payRatio>=1?'#22c55e':(payRatio>=0.5?'#f59e0b':'#ef4444'));
            const payDot=payColor?`<div style="position:absolute;top:4px;right:4px;width:8px;height:8px;border-radius:50%;background:${payColor};box-shadow:0 0 0 2px rgba(255,255,255,0.85);z-index:5;flex-shrink:0;"></div>`:'';
            html+=`<div class="booking-block"
                        data-booking-id="${b.id}"
                        data-duration="${duration}"
                        data-check-in="${b.check_in}"
                        data-check-out="${b.check_out}"
                        draggable="${dragOk}"
                        style="position:absolute;left:${left}px;top:5px;width:${width}px;height:${ROW_H-12}px;background:${color};border-radius:6px;display:flex;align-items:center;padding:0 8px;overflow:hidden;z-index:4;box-shadow:0 1px 3px rgba(0,0,0,.2);transition:opacity .15s;${isOut?'opacity:0.72;':''}">
                     <a href="${b.url}" onclick="event.stopPropagation()"
                        style="color:white;font-weight:600;font-size:11px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;text-decoration:none;flex:1;">${b.guest}</a>
                     ${dragOk?'<div class="resize-handle"></div>':''}
                     ${payDot}
                   </div>`;
        });

        // Room block bars (striped gray)
        blocks.filter(bl=>bl.room_id===room.id).forEach(bl=>{
            const startOff=daysBetween(FROM_STR,bl.check_in);
            const endOff  =daysBetween(FROM_STR,bl.check_out);
            const cStart  =Math.max(0,startOff);
            const cEnd    =Math.min(loadedDays,endOff);
            if(cEnd<=cStart)return;
            const left =LABEL_W+cStart*CELL_W+2;
            const width=(cEnd-cStart)*CELL_W-4;
            html+=`<div class="block-bar"
                        data-block-id="${bl.id}"
                        data-delete-url="${bl.delete_url}"
                        title="${bl.reason_label}${bl.notes?' · '+bl.notes:''}"
                        style="position:absolute;left:${left}px;top:5px;width:${width}px;height:${ROW_H-12}px;
                               background:repeating-linear-gradient(45deg,${isDark?'#334155':'#cbd5e1'} 0px,${isDark?'#334155':'#cbd5e1'} 4px,${isDark?'#1e293b':'#e2e8f0'} 4px,${isDark?'#1e293b':'#e2e8f0'} 8px);
                               border-radius:6px;display:flex;align-items:center;padding:0 8px;overflow:hidden;z-index:4;border:1.5px solid ${isDark?'#475569':'#94a3b8'};cursor:pointer;">
                     <svg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke-width='2' stroke='${isDark?'#94a3b8':'#64748b'}' style='width:12px;height:12px;flex-shrink:0;margin-right:4px;'><path stroke-linecap='round' stroke-linejoin='round' d='M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z'/></svg>
                     <span style="font-weight:600;font-size:10px;color:${isDark?'#94a3b8':'#475569'};white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">${bl.reason_label}</span>
                   </div>`;
        });

        html+=`</div>`;
    });
    html+=`</div>`;

    const root=document.getElementById('gantt-root');
    root.innerHTML=html;
    document.getElementById('drag-hint').classList.remove('hidden');

    // Fix month cell widths — count actual cells per month
    (function(){
        const monthCounts={};
        dateStrs.forEach(ds=>{
            const d=parseDate(ds);
            const key=`${d.getFullYear()}-${d.getMonth()}`;
            monthCounts[key]=(monthCounts[key]||0)+1;
        });
        Object.entries(monthCounts).forEach(([key,cnt])=>{
            const [y,m]=key.split('-');
            const el=document.getElementById(`mcell-${y}-${m}`);
            if(el){el.style.width=(cnt*CELL_W)+'px';}
        });
    })();

    // Scroll so today is visible near the left (2 days of padding before it)
    (function(){
        const todayOff=daysBetween(FROM_STR,TODAY);
        if(todayOff>2){
            root.scrollLeft=Math.max(0,(todayOff-2)*CELL_W);
        }
    })();

    // ── Infinite scroll: load more days on left/right edges ──
    let isLoadingMore = false;
    let isLoadingPast = false;
    root.addEventListener('scroll', ()=>{
        const{scrollLeft,clientWidth,scrollWidth}=root;
        if(!isLoadingMore && scrollLeft+clientWidth >= scrollWidth - CELL_W*8) {
            loadMoreDays();
        }
        if(!isLoadingPast && scrollLeft < CELL_W*8) {
            loadPastDays();
        }
    }, {passive:true});

    // ── Available strip ──
    function renderAvailStrip(type) {
        const strip = document.getElementById('avail-strip');
        if (!strip) return;
        const filtered = type === 'all'
            ? AVAILABLE_ROOMS
            : AVAILABLE_ROOMS.filter(r => r.type === type);
        if (filtered.length === 0) { strip.innerHTML = ''; return; }
        const MAX = 12;
        const shown = filtered.slice(0, MAX);
        const extra = filtered.length - MAX;
        const isDark = document.documentElement.classList.contains('dark');
        strip.innerHTML =
            `<span style="font-size:11px;color:${isDark?'#64748b':'#94a3b8'};margin-right:2px;">Свободны:</span>` +
            shown.map(r =>
                `<a href="${BASE_CREATE_URL}?room_id=${r.id}&check_in=${TODAY}&check_out=${TOMORROW}"
                    style="display:inline-block;padding:1px 6px;font-size:11px;font-weight:600;border-radius:4px;border:1px solid ${isDark?'#065f46':'#6ee7b7'};background:${isDark?'rgba(6,78,59,.3)':'#ecfdf5'};color:${isDark?'#34d399':'#059669'};text-decoration:none;transition:background .15s;"
                    onmouseover="this.style.background='${isDark?'rgba(6,78,59,.5)':'#d1fae5'}'"
                    onmouseout="this.style.background='${isDark?'rgba(6,78,59,.3)':'#ecfdf5'}'">
                    №${r.number}
                </a>`
            ).join('') +
            (extra > 0 ? `<span style="font-size:11px;color:${isDark?'#475569':'#94a3b8'};">+${extra} ещё</span>` : '');
    }
    renderAvailStrip('all');

    // ── Filter bar ──
    const types=[...new Set(rooms.map(r=>r.type).filter(Boolean))].sort();
    const filterBar=document.getElementById('type-filter');

    // Per-type occupancy for TODAY
    function typeOccupancyPct(type) {
        const typeRooms = rooms.filter(r => r.type === type);
        if (!typeRooms.length) return 0;
        const ids = new Set(typeRooms.map(r => r.id));
        const occ = bookings.filter(b =>
            ['pending','confirmed','checked_in'].includes(b.status) &&
            b.check_in <= TODAY && b.check_out > TODAY &&
            ids.has(b.room_id)
        ).length;
        return Math.round(occ / typeRooms.length * 100);
    }

    // Colour ramp: green → amber → red
    function occColor(pct) {
        if (pct >= 80) return { bg: '#ef4444', light: 'rgba(239,68,68,.15)', dark: 'rgba(239,68,68,.35)' };
        if (pct >= 50) return { bg: '#f59e0b', light: 'rgba(245,158,11,.15)', dark: 'rgba(245,158,11,.35)' };
        return              { bg: '#10b981', light: 'rgba(16,185,129,.15)', dark: 'rgba(16,185,129,.3)' };
    }

    // Upgrade "Все номера" button with overall stats
    const allPct  = rooms.length ? Math.round(bookings.filter(b=>['pending','confirmed','checked_in'].includes(b.status)&&b.check_in<=TODAY&&b.check_out>TODAY).length / rooms.length * 100) : 0;
    const allCol  = occColor(allPct);
    const allBtn  = document.getElementById('filter-btn-all');
    allBtn.innerHTML =
        `<span class="filter-btn-fill" style="background:${allCol.bg}"></span>` +
        `<span class="filter-btn-bar-track"></span>` +
        `<span class="filter-btn-bar" style="width:${allPct}%;background:${allCol.bg}"></span>` +
        `<span class="filter-btn-label"><span class="font-semibold">Все номера</span><span style="font-size:10px;opacity:.75">${allPct}% · ${rooms.length} ном.</span></span>`;

    if(types.length>0){
        filterBar.classList.remove('hidden');
        filterBar.classList.add('flex');
        types.forEach(type=>{
            const pct   = typeOccupancyPct(type);
            const col   = occColor(pct);
            const total = rooms.filter(r=>r.type===type).length;

            const btn = document.createElement('button');
            btn.type = 'button';
            btn.dataset.type = type;
            btn.className = 'filter-btn px-3 py-2 rounded-lg border text-xs font-medium transition-colors';

            btn.innerHTML =
                // fill overlay (shown when active)
                `<span class="filter-btn-fill" style="background:${col.bg}"></span>` +
                // bottom bar track
                `<span class="filter-btn-bar-track"></span>` +
                // bottom bar fill
                `<span class="filter-btn-bar" style="width:${pct}%;background:${col.bg}"></span>` +
                // label
                `<span class="filter-btn-label">` +
                  `<span class="font-semibold">${type}</span>` +
                  `<span style="font-size:10px;opacity:.75">${pct}% · ${total} ном.</span>` +
                `</span>`;

            // tint background when not active
            btn.style.setProperty('--occ-light', col.light);
            btn.style.background = isDark ? col.dark : col.light;
            btn.style.borderColor = isDark ? 'rgba(255,255,255,.08)' : 'rgba(0,0,0,.06)';
            btn.style.color = isDark ? '#e2e8f0' : '#1e293b';

            filterBar.appendChild(btn);
        });

        filterBar.addEventListener('click',e=>{
            const btn=e.target.closest('.filter-btn');
            if(!btn)return;
            clearSelection();
            filterBar.querySelectorAll('.filter-btn').forEach(b=>b.classList.remove('active'));
            btn.classList.add('active');
            const type=btn.dataset.type||'all';
            root.querySelectorAll('[data-room-type]').forEach(row=>{
                row.style.display=(type==='all'||row.dataset.roomType===type)?'flex':'none';
            });
            renderAvailStrip(type);
        });
    }

    // ── Drag & Drop ──
    let dragging=null;
    let isResizing=false;

    root.addEventListener('dragstart',e=>{
        if(isResizing){e.preventDefault();return;}
        if(e.target.closest('.resize-handle')){e.preventDefault();return;}
        const block=e.target.closest('.booking-block[draggable="true"]');
        if(!block)return;
        dragging={bookingId:parseInt(block.dataset.bookingId),duration:parseInt(block.dataset.duration),el:block};
        block.classList.add('dragging');
        e.dataTransfer.effectAllowed='move';
        e.dataTransfer.setData('text/plain',block.dataset.bookingId);
    });
    root.addEventListener('dragend',e=>{
        const block=e.target.closest('.booking-block');
        if(block)block.classList.remove('dragging');
        root.querySelectorAll('.day-cell.drag-over').forEach(el=>el.classList.remove('drag-over'));
        dragging=null;
    });
    root.addEventListener('dragover',e=>{
        if(!dragging)return;
        const cell=e.target.closest('.day-cell[data-room-id]');
        if(!cell)return;
        e.preventDefault();
        e.dataTransfer.dropEffect='move';
        root.querySelectorAll('.day-cell.drag-over').forEach(el=>el.classList.remove('drag-over'));
        for(let i=0;i<dragging.duration;i++){
            const d=toDateStr(addDays(parseDate(cell.dataset.date),i));
            const t=root.querySelector(`.day-cell[data-date="${d}"][data-room-id="${cell.dataset.roomId}"]`);
            if(t)t.classList.add('drag-over');
        }
    });
    root.addEventListener('dragleave',e=>{
        if(!e.relatedTarget||!root.contains(e.relatedTarget))
            root.querySelectorAll('.day-cell.drag-over').forEach(el=>el.classList.remove('drag-over'));
    });
    root.addEventListener('drop',e=>{
        const cell=e.target.closest('.day-cell[data-room-id]');
        if(!cell||!dragging)return;
        e.preventDefault();
        root.querySelectorAll('.day-cell.drag-over').forEach(el=>el.classList.remove('drag-over'));
        const newIn =cell.dataset.date;
        const newOut=toDateStr(addDays(parseDate(newIn),dragging.duration));
        const bId   =dragging.bookingId;
        if(dragging.el)dragging.el.style.opacity='0.4';
        dragging=null;
        saveDates(bId,newIn,newOut);
    });

    // ── Resize ──
    let resizing=null;
    document.addEventListener('mousedown',e=>{
        const handle=e.target.closest('.resize-handle');
        if(!handle)return;
        e.preventDefault();
        e.stopPropagation();
        isResizing=true;
        const block=handle.closest('.booking-block');
        resizing={bookingId:parseInt(block.dataset.bookingId),checkIn:block.dataset.checkIn,startOut:block.dataset.checkOut,newOut:block.dataset.checkOut,el:block};
        document.body.style.cursor='col-resize';
        document.body.style.userSelect='none';
    });
    document.addEventListener('mousemove',e=>{
        if(!resizing)return;
        const els=document.elementsFromPoint(e.clientX,e.clientY);
        const cell=els.find(el=>el.classList&&el.classList.contains('day-cell')&&el.dataset&&el.dataset.date);
        if(!cell)return;
        const newOut=toDateStr(addDays(parseDate(cell.dataset.date),1));
        if(newOut<=resizing.checkIn)return;
        resizing.newOut=newOut;
        const s=Math.max(0,daysBetween(calFromStr,resizing.checkIn));
        const en=Math.min(loadedDays,daysBetween(calFromStr,newOut));
        if(en>s)resizing.el.style.width=((en-s)*CELL_W-4)+'px';
    });
    document.addEventListener('mouseup',e=>{
        if(!resizing){isResizing=false;return;}
        document.body.style.cursor='';
        document.body.style.userSelect='';
        const{bookingId,checkIn,startOut,newOut,el}=resizing;
        resizing=null;
        isResizing=false;
        if(newOut&&newOut!==startOut){
            el.style.opacity='0.5';
            saveDates(bookingId,checkIn,newOut);
        }
    });

    // ── Hover popover ──
    const popover=document.getElementById('booking-popover');
    popover.style.cssText+=`background:${popBg};border:1px solid ${popBdr};border-radius:12px;box-shadow:0 8px 24px rgba(0,0,0,.15);padding:12px 14px;`;

    root.addEventListener('mousemove',e=>{
        if(resizing||dragging){popover.classList.add('hidden');return;}

        // Empty cell hover — show room info
        const emptyCell=e.target.closest('.day-cell[data-room-id]');
        if(emptyCell && !e.target.closest('.booking-block')){
            const rId=parseInt(emptyCell.dataset.roomId);
            const room=rooms.find(r=>r.id===rId);
            if(room && emptyCell.dataset.date >= TODAY){
                const sc=roomStatusColors[room.status]||'#6b7280';
                popover.innerHTML=`
                    <div style="font-weight:700;font-size:13px;color:${textPri};margin-bottom:4px;">№${room.number} · ${room.type||''}</div>
                    <div style="font-size:11px;color:${textSec};display:flex;flex-direction:column;gap:3px;">
                        <div style="display:flex;justify-content:space-between;gap:16px;"><span>Вместимость</span><span style="font-weight:600;color:${textPri};">${room.capacity} чел.</span></div>
                        <div style="display:flex;justify-content:space-between;gap:16px;"><span>Цена</span><span style="font-weight:600;color:#3b82f6;">${fmt(room.base_price)}/ночь</span></div>
                        <div style="display:flex;justify-content:space-between;gap:16px;"><span>Этаж</span><span style="font-weight:600;color:${textPri};">${room.floor}</span></div>
                    </div>
                    <div style="margin-top:7px;padding-top:6px;border-top:1px solid ${popBdr};font-size:10px;color:${textSec};">Кликните дважды для бронирования</div>`;
                const vw=window.innerWidth,vh=window.innerHeight;
                let x=e.clientX+16,y=e.clientY+16;
                if(x+270>vw)x=e.clientX-276;
                if(y+180>vh)y=e.clientY-186;
                popover.style.left=x+'px';
                popover.style.top=y+'px';
                popover.classList.remove('hidden');
                return;
            }
        }

        const block=e.target.closest('.booking-block');
        if(!block||e.target.closest('.resize-handle')){popover.classList.add('hidden');return;}
        const b=bookingById[parseInt(block.dataset.bookingId)];
        if(!b){popover.classList.add('hidden');return;}
        const balance=b.total-b.paid;
        const sc=statusColors[b.status]||'#6b7280';
        popover.innerHTML=`
            <div style="font-weight:700;font-size:13px;color:${textPri};margin-bottom:3px;">${b.guest}</div>
            ${b.phone?`<div style="font-size:11px;color:${textSec};margin-bottom:6px;">${b.phone}</div>`:''}
            <div style="font-size:11px;color:${textSec};margin-bottom:5px;">${b.check_in} → ${b.check_out} · ${b.nights} ${plural(b.nights,'ночь','ночи','ночей')}</div>
            <div style="font-size:11px;margin-bottom:7px;display:flex;flex-direction:column;gap:2px;">
                <div style="display:flex;justify-content:space-between;gap:16px;"><span style="color:${textSec};">Итого</span><span style="font-weight:600;color:${textPri};">${fmt(b.total)}</span></div>
                <div style="display:flex;justify-content:space-between;gap:16px;"><span style="color:${textSec};">Оплачено</span><span style="font-weight:600;color:#22c55e;">${fmt(b.paid)}</span></div>
                ${balance>0.5?`<div style="display:flex;justify-content:space-between;gap:16px;"><span style="color:${textSec};">Долг</span><span style="font-weight:600;color:#ef4444;">${fmt(balance)}</span></div>`:''}
            </div>
            <div style="display:inline-block;padding:2px 10px;border-radius:9999px;background:${sc};color:#fff;font-size:10px;font-weight:700;">${statusLabels[b.status]||b.status}</div>`;
        const vw=window.innerWidth,vh=window.innerHeight;
        let x=e.clientX+16,y=e.clientY+16;
        if(x+270>vw)x=e.clientX-276;
        if(y+200>vh)y=e.clientY-206;
        popover.style.left=x+'px';
        popover.style.top=y+'px';
        popover.classList.remove('hidden');
    });
    root.addEventListener('mouseleave',()=>popover.classList.add('hidden'));

    // ── Context menu ──
    const ctxMenu=document.getElementById('ctx-menu');
    ctxMenu.style.cssText+=`background:${ctxBg};border-color:${ctxBdr};color:${ctxText};`;
    let ctxBid=null;

    root.addEventListener('contextmenu',e=>{
        const block=e.target.closest('.booking-block');
        if(!block)return;
        e.preventDefault();
        popover.classList.add('hidden');
        const b=bookingById[parseInt(block.dataset.bookingId)];
        if(!b)return;
        ctxBid=b.id;
        const transitions=transitionOptions[b.status]||[];
        let items=`<a href="${b.url}" class="ctx-item" style="color:${ctxText};display:block;width:100%;text-align:left;padding:7px 14px;font-size:13px;cursor:pointer;text-decoration:none;">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="display:inline;width:14px;height:14px;margin-right:6px;vertical-align:-2px;"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.641 0-8.573-3.007-9.964-7.178Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/></svg>
            Открыть
        </a>`;
        if(transitions.length)items+=`<div style="height:1px;background:${ctxBdr};margin:3px 0;"></div>`;
        transitions.forEach(t=>{
            const isDanger=t==='cancelled';
            items+=`<button type="button" class="ctx-item${isDanger?' ctx-danger':''}" data-transition="${t}"
                style="color:${isDanger?'#ef4444':ctxText};display:block;width:100%;text-align:left;padding:7px 14px;font-size:13px;cursor:pointer;background:none;border:none;">
                ${transitionLabels[t]||t}
            </button>`;
        });
        ctxMenu.innerHTML=items;
        ctxMenu.style.left=e.clientX+'px';
        ctxMenu.style.top=e.clientY+'px';
        ctxMenu.classList.remove('hidden');
        requestAnimationFrame(()=>{
            const r=ctxMenu.getBoundingClientRect();
            if(r.right>window.innerWidth)ctxMenu.style.left=(e.clientX-r.width)+'px';
            if(r.bottom>window.innerHeight)ctxMenu.style.top=(e.clientY-r.height)+'px';
        });
    });

    document.addEventListener('click',e=>{
        if(!ctxMenu.classList.contains('hidden')&&!ctxMenu.contains(e.target))
            ctxMenu.classList.add('hidden');
    });
    // ESC for ctx-menu handled by the range-selection keydown listener above

    ctxMenu.addEventListener('click',e=>{
        const btn=e.target.closest('[data-transition]');
        if(!btn||!ctxBid)return;
        const transition=btn.dataset.transition;
        const b=bookingById[ctxBid];
        if(!b)return;
        ctxMenu.classList.add('hidden');
        fetch(b.status_url,{
            method:'POST',
            headers:{'Content-Type':'application/json','X-CSRF-TOKEN':CSRF,'Accept':'application/json'},
            body:JSON.stringify({transition}),
        })
        .then(r=>r.json())
        .then(data=>{
            if(data.status){showToast('Статус: '+data.label,'#10b981');setTimeout(()=>window.location.reload(),700);}
            else showToast(data.error||'Ошибка','#ef4444');
        })
        .catch(()=>showToast('Ошибка соединения','#ef4444'));
    });

    // ── Two-click range selection → new booking ──
    let selStart=null; // {roomId, date}

    function clearSelection(){
        root.querySelectorAll('.day-cell.sel-start,.day-cell.sel-range').forEach(el=>{
            el.classList.remove('sel-start','sel-range');
            el.style.background=el._origBg||'';
        });
        selStart=null;
        selHint.classList.add('hidden');
    }

    function highlightRange(roomId,fromDate,toDate){
        // ensure fromDate <= toDate
        const a=fromDate<=toDate?fromDate:toDate;
        const b=fromDate<=toDate?toDate:fromDate;
        root.querySelectorAll(`.day-cell[data-room-id="${roomId}"]`).forEach(el=>{
            const d=el.dataset.date;
            if(d>=a&&d<=b){
                el.classList.add('sel-range');
                el.style.background='rgba(59,130,246,0.25)';
            } else {
                el.classList.remove('sel-range');
                if(!el.classList.contains('sel-start'))
                    el.style.background=el._origBg||'';
            }
        });
    }

    const selHint=document.getElementById('sel-hint');

    function isCellOccupied(roomId, date) {
        const rid = parseInt(roomId);
        return bookings.some(b =>
            b.room_id === rid &&
            ['pending','confirmed','checked_in'].includes(b.status) &&
            b.check_in <= date && b.check_out > date
        ) || blocks.some(bl =>
            bl.room_id === rid &&
            bl.check_in <= date && bl.check_out > date
        );
    }

    // ── Action modal state ──
    let pendingAction = null; // {roomId, checkIn, checkOut, room}

    root.addEventListener('click',e=>{
        if(dragging||isResizing)return;
        if(e.target.closest('.booking-block'))return;
        if(e.target.closest('.block-bar'))return;
        if(e.target.closest('.resize-handle'))return;
        const cell=e.target.closest('.day-cell[data-room-id]');
        if(!cell)return;
        const roomId=cell.dataset.roomId;
        const date=cell.dataset.date;

        if(date < TODAY){
            showToast('Нельзя бронировать прошедшие даты','#64748b');
            return;
        }
        const rid=parseInt(roomId);
        const room=rooms.find(r=>r.id===rid);
        if(room && ['cleaning','maintenance'].includes(room.status)){
            showConflictModal(room,date,toDateStr(addDays(parseDate(date),1)),[],room.status==='cleaning'?'Уборка':'Ремонт');
            return;
        }
        if(isCellOccupied(roomId, date)){
            const conflicts=findLocalConflicts(rid,date,toDateStr(addDays(parseDate(date),1)));
            showConflictModal(room,date,toDateStr(addDays(parseDate(date),1)),conflicts);
            return;
        }

        if(!selStart){
            cell._origBg=cell.style.background||'';
            cell.classList.add('sel-start');
            cell.style.background='rgba(59,130,246,0.5)';
            selStart={roomId,date};
            selHint.classList.remove('hidden');
        } else if(selStart.roomId===roomId){
            const a=selStart.date<=date?selStart.date:date;
            const b=selStart.date<=date?date:selStart.date;
            const checkOut=toDateStr(addDays(parseDate(b),1));
            clearSelection();

            fetch(`/rooms/${roomId}/check?check_in=${a}&check_out=${checkOut}`,{
                headers:{'Accept':'application/json','X-CSRF-TOKEN':CSRF}
            })
            .then(r=>r.json())
            .then(data=>{
                if(data.available){
                    openActionModal(room, a, checkOut);
                } else {
                    const conflicts=data.conflicts&&data.conflicts.length
                        ? data.conflicts
                        : findLocalConflicts(parseInt(roomId),a,checkOut);
                    showConflictModal(room, a, checkOut, conflicts, data.room_status_label||null);
                }
            })
            .catch(()=>openActionModal(room, a, checkOut));
        } else {
            clearSelection();
            cell._origBg=cell.style.background||'';
            cell.classList.add('sel-start');
            cell.style.background='rgba(59,130,246,0.5)';
            selStart={roomId,date};
            selHint.classList.remove('hidden');
        }
    });

    // Block bar click → delete confirmation
    root.addEventListener('click',e=>{
        const bar=e.target.closest('.block-bar');
        if(!bar)return;
        const bl=blocks.find(b=>b.id===parseInt(bar.dataset.blockId));
        if(!bl)return;
        const reason=bl.reason_label+(bl.notes?' — '+bl.notes:'');
        if(!confirm(`Снять блокировку?\n${reason}\n${bl.check_in} — ${bl.check_out}`))return;
        fetch(bar.dataset.deleteUrl,{
            method:'DELETE',
            headers:{'X-CSRF-TOKEN':CSRF,'Accept':'application/json'},
        })
        .then(r=>r.json())
        .then(data=>{
            if(data.ok){showToast('Блокировка снята','#10b981');setTimeout(()=>location.reload(),600);}
            else showToast(data.error||'Ошибка','#ef4444');
        })
        .catch(()=>showToast('Ошибка соединения','#ef4444'));
    });

    // Hover preview of range
    root.addEventListener('mousemove',e=>{
        if(!selStart||dragging||isResizing)return;
        const cell=e.target.closest('.day-cell[data-room-id]');
        if(!cell||cell.dataset.roomId!==selStart.roomId)return;
        highlightRange(selStart.roomId,selStart.date,cell.dataset.date);
    });

    // ESC cancels selection — full handler is at the bottom of this script
    // (kept here as a no-op placeholder so comments still make sense)
    void(function(){
    });

    // ── Helpers ──
    function saveDates(bookingId,checkIn,checkOut){
        fetch(`/bookings/${bookingId}/move-dates`,{
            method:'PATCH',
            headers:{'Content-Type':'application/json','X-CSRF-TOKEN':CSRF,'Accept':'application/json'},
            body:JSON.stringify({check_in_date:checkIn,check_out_date:checkOut}),
        })
        .then(r=>r.json())
        .then(data=>{
            if(data.ok){showToast('Даты обновлены','#10b981');setTimeout(()=>window.location.reload(),600);}
            else{showToast(data.error||'Ошибка','#ef4444');root.querySelectorAll('.booking-block').forEach(el=>el.style.opacity='1');}
        })
        .catch(()=>{showToast('Ошибка соединения','#ef4444');root.querySelectorAll('.booking-block').forEach(el=>el.style.opacity='1');});
    }

    function findLocalConflicts(rid, checkIn, checkOut) {
        const bookingConflicts = bookings.filter(b => {
            if (b.room_id !== rid) return false;
            if (!['pending','confirmed','checked_in','inquiry'].includes(b.status)) return false;
            // Normal date overlap
            if (b.check_in < checkOut && b.check_out > checkIn) return true;
            // Overdue checked-in: guest is still present past their planned checkout
            if (b.status === 'checked_in' && b.check_in < checkOut && b.check_out <= TODAY) return true;
            return false;
        }).map(b => ({
            ...b,
            overdue: b.status === 'checked_in' && b.check_out <= TODAY,
        }));
        const blockConflicts = blocks
            .filter(bl => bl.room_id === rid && bl.check_in < checkOut && bl.check_out > checkIn)
            .map(bl => ({
                guest: bl.reason_label,
                phone: bl.notes || '',
                status: 'blocked',
                check_in: bl.check_in,
                check_out: bl.check_out,
                isBlock: true,
            }));
        return [...bookingConflicts, ...blockConflicts];
    }

    function showConflictModal(room, checkIn, checkOut, conflicts, roomStatusLabel=null) {
        const fmtDate = d => { const p=d.split('-'); return `${p[2]}.${p[1]}.${p[0]}`; };
        const modal = document.getElementById('conflict-modal');
        document.getElementById('conflict-room').textContent =
            `№${room?.number || '?'} · ${room?.type || ''}`;
        const body = document.getElementById('conflict-body');

        if (roomStatusLabel) {
            body.innerHTML = `<div class="flex items-center gap-3 p-3 rounded-xl bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-700">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="#f59e0b" style="width:18px;height:18px;flex-shrink:0;"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z"/></svg>
                <div>
                    <p class="font-semibold text-amber-800 dark:text-amber-300 text-sm">Номер недоступен для бронирования</p>
                    <p class="text-xs text-amber-700 dark:text-amber-400 mt-0.5">Текущий статус: <strong>${roomStatusLabel}</strong></p>
                </div>
            </div>`;
        } else if (!conflicts || conflicts.length === 0) {
            body.innerHTML = `<div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-700/50 border border-slate-200 dark:border-slate-600 text-sm text-slate-500 dark:text-slate-400">
                Номер недоступен на выбранные даты: ${fmtDate(checkIn)} — ${fmtDate(checkOut)}
            </div>`;
        } else {
            body.innerHTML = conflicts.map(c => {
                const sc = statusColors[c.status] || '#94a3b8';
                const label = statusLabels[c.status] || c.status;
                const icon = c.isBlock
                    ? `<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="${sc}" style="width:14px;height:14px;flex-shrink:0;margin-top:1px;"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z"/></svg>`
                    : `<div style="width:10px;height:10px;border-radius:50%;background:${sc};flex-shrink:0;margin-top:3px;"></div>`;
                return `<div class="flex items-start gap-3 p-3 rounded-xl bg-slate-50 dark:bg-slate-700/50 border border-slate-200 dark:border-slate-600">
                    ${icon}
                    <div class="flex-1 min-w-0">
                        <p class="font-semibold text-slate-900 dark:text-slate-100 text-sm">${c.guest || '—'}</p>
                        ${c.phone ? `<p class="text-xs text-slate-400 dark:text-slate-500 mt-0.5">${c.phone}</p>` : ''}
                        <div class="flex items-center gap-2 mt-1 flex-wrap">
                            <span class="text-xs text-slate-500 dark:text-slate-400">${fmtDate(c.check_in)} → ${fmtDate(c.check_out)}</span>
                            <span style="padding:1px 7px;border-radius:9999px;background:${sc};color:#fff;font-size:10px;font-weight:700;">${label}</span>
                            ${c.overdue ? `<span style="padding:1px 7px;border-radius:9999px;background:#ef4444;color:#fff;font-size:10px;font-weight:700;">Просрочен — гость не выехал</span>` : ''}
                        </div>
                        ${c.url ? `<a href="${c.url}" class="text-xs text-blue-500 hover:text-blue-700 mt-1 inline-block">Открыть бронирование →</a>` : ''}
                    </div>
                </div>`;
            }).join('');
        }
        modal.classList.remove('hidden');
        modal.addEventListener('click', e => { if(e.target===modal) modal.classList.add('hidden'); }, {once:false});
    }

    function showToast(msg,bg){
        const t=document.getElementById('gantt-toast');
        t.querySelector('div').textContent=msg;
        t.querySelector('div').style.background=bg;
        t.classList.remove('hidden');
        setTimeout(()=>t.classList.add('hidden'),2800);
    }

    // ── Infinite scroll: load next batch ──
    function loadMoreDays(){
        if(isLoadingMore)return;
        isLoadingMore=true;
        const fromStr=toDateStr(addDays(fromDate,loadedDays));
        const batchDays=30;
        fetch(`${CAL_DATA_URL}?from=${fromStr}&days=${batchDays}`,{
            headers:{'Accept':'application/json','X-CSRF-TOKEN':CSRF}
        })
        .then(r=>r.json())
        .then(data=>{
            appendDays(fromStr,batchDays,data.bookings||[],data.blocks||[]);
            loadedDays+=batchDays;
        })
        .catch(()=>{})
        .finally(()=>{ isLoadingMore=false; });
    }

    function appendDays(fromStr,batchDays,newBookings,newBlocks){
        const newDates=Array.from({length:batchDays},(_,i)=>toDateStr(addDays(parseDate(fromStr),i)));
        dateStrs=[...dateStrs,...newDates];

        // Update occupancyMap for new dates
        newDates.forEach(ds=>{
            occupancyMap[ds]=bookings.filter(b=>['pending','confirmed','checked_in'].includes(b.status)&&b.check_in<=ds&&b.check_out>ds).length;
        });

        // -- Expand gantt-inner min-width
        const inner=document.getElementById('gantt-inner');
        inner.style.minWidth=(LABEL_W+(loadedDays+batchDays)*CELL_W)+'px';

        // -- Month header: extend existing or add new cell
        const monthHdr=document.getElementById('gantt-month-hdr');
        newDates.forEach(ds=>{
            const d=parseDate(ds);
            const y=d.getFullYear(),m=d.getMonth();
            const cellId=`mcell-${y}-${m}`;
            let cell=document.getElementById(cellId);
            if(cell){
                cell.style.width=(parseInt(cell.style.width)+CELL_W)+'px';
            } else {
                cell=document.createElement('div');
                cell.id=cellId;
                cell.dataset.y=y; cell.dataset.m=m;
                cell.style.cssText=`width:${CELL_W}px;flex-shrink:0;display:flex;align-items:center;padding:0 10px;font-weight:700;font-size:11px;color:${textPri};border-right:1px solid ${hdrBdr};overflow:hidden;white-space:nowrap;background:${hdrBg};`;
                cell.textContent=`${MONTH_NAMES[m]} ${y}`;
                monthHdr.appendChild(cell);
            }
        });

        // -- Date header: append new date cells
        const dateHdr=document.getElementById('gantt-date-hdr');
        newDates.forEach(ds=>{
            const d=parseDate(ds);
            const dow=d.getDay();
            const isWeekend=dow===0||dow===6;
            const isToday=ds===TODAY;
            const occ=occupancyMap[ds]||0;
            const pct=TOTAL_ROOMS>0?Math.round(occ/TOTAL_ROOMS*100):0;
            const barColor=pct>=80?'#ef4444':pct>=50?'#f59e0b':'#22c55e';
            const bg=isToday?todayHdr:(isWeekend?wkndBg:'transparent');
            const el=document.createElement('div');
            el.className='day-cell';
            el.dataset.date=ds;
            el.style.cssText=`width:${CELL_W}px;flex-shrink:0;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:1px;border-right:1px solid ${cellBdr};background:${bg};padding-bottom:2px;`;
            el.innerHTML=`<span style="font-weight:700;font-size:12px;color:${isToday?todayC:(isWeekend?'#7c3aed':labelC)};">${d.getDate()}</span><span style="font-size:9px;color:${isWeekend?'#7c3aed':textSec};">${['Вс','Пн','Вт','Ср','Чт','Пт','Сб'][dow]}</span><div style="width:80%;height:3px;background:${isDark?'#334155':'#e2e8f0'};border-radius:2px;overflow:hidden;margin:1px 0;"><div style="width:${pct}%;height:100%;background:${barColor};border-radius:2px;"></div></div>`;
            dateHdr.appendChild(el);
        });

        // -- Room rows: append day cells + booking/block elements
        const totalLoadedAfter=loadedDays+batchDays;
        rooms.forEach(room=>{
            const row=root.querySelector(`[data-room-id="${room.id}"]`);
            if(!row)return;

            // Append day cells
            newDates.forEach(ds=>{
                const dow=parseDate(ds).getDay();
                const isWeekend=dow===0||dow===6;
                const isToday=ds===TODAY;
                const isPast=ds<TODAY;
                const cellBg=isToday?todayCell:(isWeekend?wkndBg:(isPast?(isDark?'rgba(15,23,42,0.5)':'rgba(241,245,249,0.7)'):'transparent'));
                const el=document.createElement('div');
                el.className='day-cell';
                el.dataset.date=ds;
                el.dataset.roomId=room.id;
                el.style.cssText=`width:${CELL_W}px;flex-shrink:0;border-right:1px solid ${rowBdr};background:${cellBg};cursor:${isPast?'default':'pointer'};`;
                row.appendChild(el);
            });

            // Extend or add booking blocks that fall in new range
            newBookings.filter(b=>b.room_id===room.id).forEach(b=>{
                if(!bookings.find(x=>x.id===b.id)){bookings.push(b);bookingById[b.id]=b;}
                const startOff=Math.max(0,daysBetween(calFromStr,b.check_in));
                const endOff=Math.min(totalLoadedAfter,daysBetween(calFromStr,b.check_out));
                if(endOff<=startOff)return;
                const left=LABEL_W+startOff*CELL_W+2;
                const width=(endOff-startOff)*CELL_W-4;
                const existing=root.querySelector(`.booking-block[data-booking-id="${b.id}"]`);
                if(existing){existing.style.width=width+'px';return;}
                const color=statusColors[b.status]||'#94a3b8';
                const dragOk=draggableStatuses.has(b.status);
                const isOut=b.status==='checked_out';
                const payRatio=b.total>0?b.paid/b.total:null;
                const payColor=payRatio===null?null:(payRatio>=1?'#22c55e':(payRatio>=0.5?'#f59e0b':'#ef4444'));
                const pRatio=b.total>0?b.paid/b.total:null;
                const pColor=pRatio===null?null:(pRatio>=1?'#22c55e':(pRatio>=0.5?'#f59e0b':'#ef4444'));
                const pDot=pColor?`<div style="position:absolute;top:3px;right:4px;width:8px;height:8px;border-radius:50%;background:${pColor};box-shadow:0 0 0 2px rgba(255,255,255,0.85);z-index:5;"></div>`:'';
                const el=document.createElement('div');
                el.className='booking-block';
                el.dataset.bookingId=b.id;
                el.dataset.duration=daysBetween(b.check_in,b.check_out);
                el.dataset.checkIn=b.check_in;
                el.dataset.checkOut=b.check_out;
                el.draggable=dragOk;
                el.style.cssText=`position:absolute;left:${left}px;top:5px;width:${width}px;height:${ROW_H-12}px;background:${color};border-radius:6px;display:flex;align-items:center;padding:0 8px;overflow:hidden;z-index:4;box-shadow:0 1px 3px rgba(0,0,0,.2);transition:opacity .15s;${isOut?'opacity:0.72;':''}`;
                el.innerHTML=`<a href="${b.url}" onclick="event.stopPropagation()" style="color:white;font-weight:600;font-size:11px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;text-decoration:none;flex:1;">${b.guest}</a>${dragOk?'<div class="resize-handle"></div>':''}${pDot}`;
                row.appendChild(el);
            });

            // Extend or add block bars
            newBlocks.filter(bl=>bl.room_id===room.id).forEach(bl=>{
                if(!blocks.find(x=>x.id===bl.id))blocks.push(bl);
                const startOff=Math.max(0,daysBetween(calFromStr,bl.check_in));
                const endOff=Math.min(totalLoadedAfter,daysBetween(calFromStr,bl.check_out));
                if(endOff<=startOff)return;
                const left=LABEL_W+startOff*CELL_W+2;
                const width=(endOff-startOff)*CELL_W-4;
                const existing=root.querySelector(`.block-bar[data-block-id="${bl.id}"]`);
                if(existing){existing.style.width=width+'px';return;}
                const el=document.createElement('div');
                el.className='block-bar';
                el.dataset.blockId=bl.id;
                el.dataset.deleteUrl=bl.delete_url;
                el.title=bl.reason_label+(bl.notes?' · '+bl.notes:'');
                el.style.cssText=`position:absolute;left:${left}px;top:5px;width:${width}px;height:${ROW_H-12}px;background:repeating-linear-gradient(45deg,${isDark?'#334155':'#cbd5e1'} 0px,${isDark?'#334155':'#cbd5e1'} 4px,${isDark?'#1e293b':'#e2e8f0'} 4px,${isDark?'#1e293b':'#e2e8f0'} 8px);border-radius:6px;display:flex;align-items:center;padding:0 8px;overflow:hidden;z-index:4;border:1.5px solid ${isDark?'#475569':'#94a3b8'};cursor:pointer;`;
                el.innerHTML=`<svg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke-width='2' stroke='${isDark?'#94a3b8':'#64748b'}' style='width:12px;height:12px;flex-shrink:0;margin-right:4px;'><path stroke-linecap='round' stroke-linejoin='round' d='M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z'/></svg><span style="font-weight:600;font-size:10px;color:${isDark?'#94a3b8':'#475569'};white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">${bl.reason_label}</span>`;
                row.appendChild(el);
            });
        });

        // Also extend existing booking blocks that were truncated at the old right boundary
        bookings.forEach(b=>{
            const endOff=daysBetween(calFromStr,b.check_out);
            if(endOff<=loadedDays)return;
            const el=root.querySelector(`.booking-block[data-booking-id="${b.id}"]`);
            if(!el)return;
            const startOff=Math.max(0,daysBetween(calFromStr,b.check_in));
            const newEnd=Math.min(totalLoadedAfter,endOff);
            el.style.width=((newEnd-startOff)*CELL_W-4)+'px';
        });
        blocks.forEach(bl=>{
            const endOff=daysBetween(calFromStr,bl.check_out);
            if(endOff<=loadedDays)return;
            const el=root.querySelector(`.block-bar[data-block-id="${bl.id}"]`);
            if(!el)return;
            const startOff=Math.max(0,daysBetween(calFromStr,bl.check_in));
            const newEnd=Math.min(totalLoadedAfter,endOff);
            el.style.width=((newEnd-startOff)*CELL_W-4)+'px';
        });
    }

    // ── Load past days (prepend left) ──
    function loadPastDays(){
        if(isLoadingPast)return;
        isLoadingPast=true;
        const batchDays=30;
        const newFromStr=toDateStr(addDays(fromDate,-batchDays));
        fetch(`${CAL_DATA_URL}?from=${newFromStr}&days=${batchDays}`,{
            headers:{'Accept':'application/json','X-CSRF-TOKEN':CSRF}
        })
        .then(r=>r.json())
        .then(data=>{ prependDays(newFromStr,batchDays,data.bookings||[],data.blocks||[]); })
        .catch(()=>{})
        .finally(()=>{ isLoadingPast=false; });
    }

    function prependDays(newFromStr,batchDays,newBookings,newBlocks){
        const newDates=Array.from({length:batchDays},(_,i)=>toDateStr(addDays(parseDate(newFromStr),i)));
        const shiftPx=batchDays*CELL_W;

        // Update reference points
        calFromStr=newFromStr;
        fromDate=parseDate(newFromStr);
        dateStrs=[...newDates,...dateStrs];
        loadedDays+=batchDays;

        // Expand gantt-inner
        const inner=document.getElementById('gantt-inner');
        inner.style.minWidth=(LABEL_W+loadedDays*CELL_W)+'px';

        // Shift ALL existing booking blocks and block bars right
        root.querySelectorAll('.booking-block,.block-bar').forEach(el=>{
            el.style.left=(parseFloat(el.style.left)+shiftPx)+'px';
        });

        // Month header: group new dates by month, prepend cells
        const monthHdr=document.getElementById('gantt-month-hdr');
        const mLabelCell=monthHdr.firstElementChild;
        const mGroups=[];
        let mCur=null;
        newDates.forEach(ds=>{
            const d=parseDate(ds);
            const y=d.getFullYear(),m=d.getMonth();
            const key=`${y}-${m}`;
            if(!mCur||mCur.key!==key){mCur={key,y,m,count:1};mGroups.push(mCur);}
            else mCur.count++;
        });
        // Insert in reverse so leftmost ends up first after insertBefore chain
        [...mGroups].reverse().forEach(grp=>{
            const cellId=`mcell-${grp.y}-${grp.m}`;
            const existing=document.getElementById(cellId);
            if(existing){
                existing.style.width=(parseFloat(existing.style.width)+grp.count*CELL_W)+'px';
                monthHdr.insertBefore(existing,mLabelCell.nextSibling);
            } else {
                const cell=document.createElement('div');
                cell.id=cellId;
                cell.style.cssText=`width:${grp.count*CELL_W}px;flex-shrink:0;display:flex;align-items:center;padding:0 10px;font-weight:700;font-size:11px;color:${textPri};border-right:1px solid ${hdrBdr};overflow:hidden;white-space:nowrap;background:${hdrBg};`;
                cell.textContent=`${MONTH_NAMES[grp.m]} ${grp.y}`;
                monthHdr.insertBefore(cell,mLabelCell.nextSibling);
            }
        });

        // Date header: prepend new date cells
        const dateHdr=document.getElementById('gantt-date-hdr');
        const dLabelCell=dateHdr.firstElementChild;
        [...newDates].reverse().forEach(ds=>{
            const d=parseDate(ds);
            const dow=d.getDay();
            const isWeekend=dow===0||dow===6;
            const isToday=ds===TODAY;
            const bg=isToday?todayHdr:(isWeekend?wkndBg:'transparent');
            const el=document.createElement('div');
            el.className='day-cell';
            el.dataset.date=ds;
            el.style.cssText=`width:${CELL_W}px;flex-shrink:0;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:1px;border-right:1px solid ${cellBdr};background:${bg};padding-bottom:2px;`;
            el.innerHTML=`<span style="font-weight:700;font-size:12px;color:${isToday?todayC:(isWeekend?'#7c3aed':labelC)};">${d.getDate()}</span><span style="font-size:9px;color:${isWeekend?'#7c3aed':textSec};">${['Вс','Пн','Вт','Ср','Чт','Пт','Сб'][dow]}</span>`;
            dateHdr.insertBefore(el,dLabelCell.nextSibling);
        });

        // Room rows: prepend day cells, add new booking/block elements
        rooms.forEach(room=>{
            const row=root.querySelector(`div[data-room-id="${room.id}"][data-room-type]`);
            if(!row)return;
            const rowLabel=row.firstElementChild;

            // Prepend day cells (reverse so order is correct after chained insertBefore)
            [...newDates].reverse().forEach(ds=>{
                const dow=parseDate(ds).getDay();
                const isWeekend=dow===0||dow===6;
                const isToday=ds===TODAY;
                const isPast=ds<TODAY;
                const bg=isToday?todayCell:(isWeekend?wkndBg:(isPast?(isDark?'rgba(15,23,42,0.5)':'rgba(241,245,249,0.7)'):'transparent'));
                const el=document.createElement('div');
                el.className='day-cell';
                el.dataset.date=ds;
                el.dataset.roomId=room.id;
                el.style.cssText=`width:${CELL_W}px;flex-shrink:0;border-right:1px solid ${rowBdr};background:${bg};cursor:${isPast?'default':'pointer'};`;
                row.insertBefore(el,rowLabel.nextSibling);
            });

            // Add booking blocks for new date range
            newBookings.filter(b=>b.room_id===room.id).forEach(b=>{
                if(!bookings.find(x=>x.id===b.id)){bookings.push(b);bookingById[b.id]=b;}
                const startOff=Math.max(0,daysBetween(calFromStr,b.check_in));
                const endOff=Math.min(loadedDays,daysBetween(calFromStr,b.check_out));
                if(endOff<=startOff)return;
                const left=LABEL_W+startOff*CELL_W+2;
                const width=(endOff-startOff)*CELL_W-4;
                // If already in DOM (block spans into already-loaded range), just update left (already shifted) and width
                const existing=root.querySelector(`.booking-block[data-booking-id="${b.id}"]`);
                if(existing){existing.style.left=left+'px';existing.style.width=width+'px';return;}
                const color=statusColors[b.status]||'#94a3b8';
                const dragOk=draggableStatuses.has(b.status);
                const isOut=b.status==='checked_out';
                const payRatio=b.total>0?b.paid/b.total:null;
                const payColor=payRatio===null?null:(payRatio>=1?'#22c55e':(payRatio>=0.5?'#f59e0b':'#ef4444'));
                const pRatio=b.total>0?b.paid/b.total:null;
                const pColor=pRatio===null?null:(pRatio>=1?'#22c55e':(pRatio>=0.5?'#f59e0b':'#ef4444'));
                const pDot=pColor?`<div style="position:absolute;top:4px;right:4px;width:8px;height:8px;border-radius:50%;background:${pColor};box-shadow:0 0 0 2px rgba(255,255,255,0.85);z-index:5;"></div>`:'';
                const el=document.createElement('div');
                el.className='booking-block';
                el.dataset.bookingId=b.id;
                el.dataset.duration=daysBetween(b.check_in,b.check_out);
                el.dataset.checkIn=b.check_in;
                el.dataset.checkOut=b.check_out;
                el.draggable=dragOk;
                el.style.cssText=`position:absolute;left:${left}px;top:5px;width:${width}px;height:${ROW_H-12}px;background:${color};border-radius:6px;display:flex;align-items:center;padding:0 8px;overflow:hidden;z-index:4;box-shadow:0 1px 3px rgba(0,0,0,.2);transition:opacity .15s;${isOut?'opacity:0.72;':''}`;
                el.innerHTML=`<a href="${b.url}" onclick="event.stopPropagation()" style="color:white;font-weight:600;font-size:11px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;text-decoration:none;flex:1;">${b.guest}</a>${dragOk?'<div class="resize-handle"></div>':''}${pDot}`;
                row.appendChild(el);
            });

            // Add block bars for new date range
            newBlocks.filter(bl=>bl.room_id===room.id).forEach(bl=>{
                if(!blocks.find(x=>x.id===bl.id))blocks.push(bl);
                const startOff=Math.max(0,daysBetween(calFromStr,bl.check_in));
                const endOff=Math.min(loadedDays,daysBetween(calFromStr,bl.check_out));
                if(endOff<=startOff)return;
                const left=LABEL_W+startOff*CELL_W+2;
                const width=(endOff-startOff)*CELL_W-4;
                const existing=root.querySelector(`.block-bar[data-block-id="${bl.id}"]`);
                if(existing){existing.style.left=left+'px';existing.style.width=width+'px';return;}
                const el=document.createElement('div');
                el.className='block-bar';
                el.dataset.blockId=bl.id;
                el.dataset.deleteUrl=bl.delete_url;
                el.title=bl.reason_label+(bl.notes?' · '+bl.notes:'');
                el.style.cssText=`position:absolute;left:${left}px;top:5px;width:${width}px;height:${ROW_H-12}px;background:repeating-linear-gradient(45deg,${isDark?'#334155':'#cbd5e1'} 0px,${isDark?'#334155':'#cbd5e1'} 4px,${isDark?'#1e293b':'#e2e8f0'} 4px,${isDark?'#1e293b':'#e2e8f0'} 8px);border-radius:6px;display:flex;align-items:center;padding:0 8px;overflow:hidden;z-index:4;border:1.5px solid ${isDark?'#475569':'#94a3b8'};cursor:pointer;`;
                el.innerHTML=`<svg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke-width='2' stroke='${isDark?'#94a3b8':'#64748b'}' style='width:12px;height:12px;flex-shrink:0;margin-right:4px;'><path stroke-linecap='round' stroke-linejoin='round' d='M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z'/></svg><span style="font-weight:600;font-size:10px;color:${isDark?'#94a3b8':'#475569'};white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">${bl.reason_label}</span>`;
                row.appendChild(el);
            });
        });

        // Maintain scroll position so prepending doesn't jump the view
        root.scrollLeft+=shiftPx;
    }

    // ── Action modal ──
    function openActionModal(room, checkIn, checkOut){
        pendingAction={roomId:room.id,checkIn,checkOut,room};
        const nights=daysBetween(checkIn,checkOut);
        const fmt=d=>{const p=d.split('-');return `${p[2]}.${p[1]}.${p[0]}`;};
        document.getElementById('am-room-num').textContent=`№${room.number} · ${room.type||''}`;
        document.getElementById('am-dates').textContent=`${fmt(checkIn)} — ${fmt(checkOut)}`;
        document.getElementById('am-nights').textContent=`${nights} ${plural(nights,'ночь','ночи','ночей')}`;
        const url=`${BASE_CREATE_URL}?room_id=${room.id}&check_in=${checkIn}&check_out=${checkOut}`;
        document.getElementById('am-booking-btn').href=url;
        document.getElementById('am-rent-btn').href=url+'&source=rental';
        document.getElementById('action-modal').classList.remove('hidden');
    }

    window.closeActionModal=function(){
        document.getElementById('action-modal').classList.add('hidden');
        pendingAction=null;
    };

    document.getElementById('action-modal').addEventListener('click',e=>{
        if(e.target===document.getElementById('action-modal'))closeActionModal();
    });

    // ── Block form ──
    window.showBlockForm=function(){
        if(!pendingAction)return;
        document.getElementById('action-modal').classList.add('hidden'); // hide without nulling pendingAction
        const fmt=d=>{const p=d.split('-');return `${p[2]}.${p[1]}.${p[0]}`;};
        document.getElementById('bf-room').textContent=`№${pendingAction.room.number}`;
        document.getElementById('bf-dates').textContent=`${fmt(pendingAction.checkIn)} — ${fmt(pendingAction.checkOut)}`;
        document.getElementById('block-error').classList.add('hidden');
        document.getElementById('block-notes-wrap').classList.add('hidden');
        document.getElementById('block-notes').value='';
        document.getElementById('block-form-modal').classList.remove('hidden');
    };

    window.closeBlockForm=function(){
        document.getElementById('block-form-modal').classList.add('hidden');
        openActionModal(pendingAction.room,pendingAction.checkIn,pendingAction.checkOut);
    };

    document.getElementById('block-form-modal').addEventListener('click',e=>{
        if(e.target===document.getElementById('block-form-modal')){
            document.getElementById('block-form-modal').classList.add('hidden');
            pendingAction=null;
        }
    });

    // Show notes textarea when "Другое" is selected
    document.getElementById('block-reasons').addEventListener('change',e=>{
        const val=document.querySelector('input[name="block_reason"]:checked')?.value;
        const wrap=document.getElementById('block-notes-wrap');
        wrap.classList.toggle('hidden', val!=='other');
        // Update visual radio dots
        document.querySelectorAll('#block-reasons label').forEach(lbl=>{
            const checked=lbl.querySelector('input[type=radio]').checked;
            lbl.querySelector('.reason-dot-inner').classList.toggle('hidden',!checked);
        });
    });
    // Init radio visuals
    document.querySelectorAll('#block-reasons label').forEach(lbl=>{
        const checked=lbl.querySelector('input[type=radio]').checked;
        lbl.querySelector('.reason-dot-inner').classList.toggle('hidden',!checked);
    });

    window.submitBlock=function(){
        if(!pendingAction)return;
        const reason=document.querySelector('input[name="block_reason"]:checked')?.value;
        if(!reason)return;
        const notes=document.getElementById('block-notes').value.trim();
        const btn=document.getElementById('block-submit-btn');
        const errEl=document.getElementById('block-error');
        btn.disabled=true;
        btn.textContent='Блокировка…';
        errEl.classList.add('hidden');
        fetch('/room-blocks',{
            method:'POST',
            headers:{'Content-Type':'application/json','X-CSRF-TOKEN':CSRF,'Accept':'application/json'},
            body:JSON.stringify({
                room_id:pendingAction.roomId,
                check_in_date:pendingAction.checkIn,
                check_out_date:pendingAction.checkOut,
                reason,
                notes:notes||null,
            }),
        })
        .then(r=>r.json())
        .then(data=>{
            if(data.ok){
                document.getElementById('block-form-modal').classList.add('hidden');
                pendingAction=null;
                showToast('Даты заблокированы','#6b7280');
                setTimeout(()=>location.reload(),700);
            } else {
                errEl.textContent=data.error||'Ошибка';
                errEl.classList.remove('hidden');
                btn.disabled=false;
                btn.innerHTML='<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:16px;height:16px;"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z"/></svg> Заблокировать даты';
            }
        })
        .catch(()=>{
            errEl.textContent='Ошибка соединения';
            errEl.classList.remove('hidden');
            btn.disabled=false;
            btn.textContent='Заблокировать даты';
        });
    };

    // ESC closes all modals
    document.addEventListener('keydown',e=>{
        if(e.key==='Escape'){
            clearSelection();
            ctxMenu.classList.add('hidden');
            document.getElementById('conflict-modal').classList.add('hidden');
            document.getElementById('action-modal').classList.add('hidden');
            document.getElementById('block-form-modal').classList.add('hidden');
            document.getElementById('info-modal').classList.add('hidden');
        }
    });

    // Info modal: click outside closes
    document.getElementById('info-modal').addEventListener('click',e=>{
        if(e.target===document.getElementById('info-modal'))
            document.getElementById('info-modal').classList.add('hidden');
    });
})();
</script>
@endsection
