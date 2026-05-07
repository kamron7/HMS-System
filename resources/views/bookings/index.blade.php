@extends('layouts.app')

@section('title', 'Бронирования')

@section('content')

<div x-data="{
    activeDropdown: null,
    quickViewBooking: null,
    showFilters: {{ ($check_in || $check_out || $room || $group_filter) ? 'true' : 'false' }},
    selectedIds: [],
    selectedStatuses: {},
    toggleSelect(id, status) {
        const i = this.selectedIds.indexOf(id);
        if (i === -1) { this.selectedIds.push(id); this.selectedStatuses[id] = status; }
        else { this.selectedIds.splice(i, 1); delete this.selectedStatuses[id]; }
    },
    isSelected(id) { return this.selectedIds.includes(id); },
    clearSelected() { this.selectedIds = []; this.selectedStatuses = {}; },
    bulkAction(transition) { window._bookingBulkAction(transition, this.selectedIds); },
    availableTransitions() {
        const statuses = Object.values(this.selectedStatuses);
        if (!statuses.length) return [];
        const ALLOWED = {
            inquiry:     ['pending','cancelled'],
            pending:     ['confirmed','checked_in','cancelled','no_show'],
            confirmed:   ['checked_in','cancelled','no_show'],
            checked_in:  ['checked_out','cancelled'],
            checked_out: [],
            cancelled:   [],
            no_show:     ['cancelled'],
        };
        const ORDER = ['pending','confirmed','checked_in','checked_out','cancelled','no_show'];
        const union = new Set();
        statuses.forEach(s => (ALLOWED[s] || []).forEach(t => union.add(t)));
        return ORDER.filter(t => union.has(t));
    },
    transitionDef(t) {
        const defs = {
            pending:     { label: 'На ожидание', cls: 'bg-yellow-500 hover:bg-yellow-400' },
            confirmed:   { label: 'Подтвердить', cls: 'bg-blue-600 hover:bg-blue-500' },
            checked_in:  { label: 'Заселить',    cls: 'bg-emerald-600 hover:bg-emerald-500' },
            checked_out: { label: 'Выселить',    cls: 'bg-slate-500 hover:bg-slate-400' },
            cancelled:   { label: 'Отменить',    cls: 'bg-red-600 hover:bg-red-500' },
            no_show:     { label: 'Не явился',   cls: 'bg-orange-600 hover:bg-orange-500' },
        };
        return defs[t] || { label: t, cls: 'bg-slate-600 hover:bg-slate-500' };
    },
}">

{{-- Hidden bulk form --}}
<form id="bulk-form" method="POST" action="{{ route('bookings.bulk-status') }}" class="hidden">
    @csrf
    <input type="hidden" id="bulk-transition-input" name="transition">
</form>

{{-- Header --}}
<div class="flex items-center justify-between mb-5">
    <div>
        <h1 class="text-2xl font-bold text-slate-900 dark:text-slate-100">Бронирования</h1>
        <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">
            Всего: {{ $bookings->total() }}
            @if($search || $status || $check_in || $check_out || $room || $group_filter)
                · <a href="{{ route('bookings.index') }}" data-status-reset class="text-blue-600 dark:text-blue-400 hover:underline text-xs">сбросить</a>
            @endif
        </p>
    </div>
    <div class="flex items-center gap-2">
        <a href="{{ route('bookings.timeline') }}"
           class="inline-flex items-center gap-2 px-3 py-2 text-sm font-semibold text-slate-600 dark:text-slate-300 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors shadow-sm"
           title="Таймлайн">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 flex-shrink-0"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z"/></svg>
            <span class="hidden sm:inline">Таймлайн</span>
        </a>
        <a href="{{ route('bookings.export', request()->query()) }}"
           class="inline-flex items-center gap-2 px-3 py-2 text-sm font-semibold text-slate-600 dark:text-slate-300 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors shadow-sm"
           title="Экспорт CSV">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 flex-shrink-0"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
            <span class="hidden sm:inline">CSV</span>
        </a>
        <a href="{{ route('bookings.create') }}"
           class="inline-flex items-center gap-2 px-3 py-2 bg-blue-600 text-white text-sm font-semibold rounded-lg hover:bg-blue-700 transition-colors shadow-sm"
           title="Новое бронирование">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4 flex-shrink-0"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
            <span class="hidden sm:inline">Новое бронирование</span>
        </a>
    </div>
</div>

{{-- Inquiry alert --}}
@if($inquiryCount > 0)
<div class="mb-4 flex items-center gap-3 bg-purple-50 dark:bg-purple-900/20 border border-purple-200 dark:border-purple-800 rounded-xl px-4 py-3">
    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-purple-600 dark:text-purple-400 flex-shrink-0 hidden sm:block"><path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0"/></svg>
    <p class="text-sm text-purple-800 dark:text-purple-200">
        <span class="font-semibold">{{ $inquiryCount }} {{ $inquiryCount < 5 ? 'новых запроса' : 'новых запросов' }}</span>
        <span class="hidden sm:inline"> от клиентов</span> ожидают рассмотрения.
    </p>
    <a href="{{ route('bookings.index', ['status' => 'inquiry']) }}"
       class="ml-auto text-xs font-semibold text-purple-700 dark:text-purple-300 hover:text-purple-900 dark:hover:text-purple-100 whitespace-nowrap">Просмотреть →</a>
</div>
@endif

{{-- Filter area --}}
<div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl shadow-sm mb-5 overflow-hidden">

    {{-- Status pills --}}
    <form method="GET" action="{{ route('bookings.index') }}">
        <div class="flex flex-wrap gap-1.5 px-3 py-3 border-b border-slate-100 dark:border-slate-700">
            @php
                $statusPills = [
                    ['value' => '',            'label' => 'Все',        'dot' => 'bg-slate-400'],
                    ['value' => 'inquiry',     'label' => 'Запросы',    'dot' => 'bg-purple-500'],
                    ['value' => 'pending',     'label' => 'Ожидание',   'dot' => 'bg-amber-400'],
                    ['value' => 'confirmed',   'label' => 'Подтвержд.', 'dot' => 'bg-blue-500'],
                    ['value' => 'checked_in',  'label' => 'Заселён',    'dot' => 'bg-emerald-500'],
                    ['value' => 'checked_out', 'label' => 'Выселен',    'dot' => 'bg-slate-300'],
                    ['value' => 'cancelled',   'label' => 'Отменён',    'dot' => 'bg-red-400'],
                    ['value' => 'no_show',     'label' => 'Не явился',  'dot' => 'bg-orange-400'],
                ];
            @endphp
            @foreach($statusPills as $pill)
            @php
                $cnt = $pill['value'] === '' ? $statusCounts->sum() : (int)($statusCounts[$pill['value']] ?? 0);
                $active = $status === $pill['value'];
                $pillParams = array_filter(['status' => $pill['value'], 'search' => $search, 'check_in' => $check_in, 'check_out' => $check_out, 'room' => $room, 'group_filter' => $group_filter]);
            @endphp
            @if($cnt > 0 || $pill['value'] === '')
            <a href="{{ route('bookings.index', $pillParams) }}"
               data-status-pill="{{ $pill['value'] }}"
               class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold rounded-full transition-colors
                      {{ $active
                            ? 'bg-slate-900 dark:bg-white text-white dark:text-slate-900'
                            : 'bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-600' }}">
                <span class="w-1.5 h-1.5 rounded-full {{ $pill['dot'] }}"></span>
                {{ $pill['label'] }}
                <span class="opacity-60">{{ $cnt }}</span>
            </a>
            @endif
            @endforeach
        </div>

        {{-- Search row --}}
        <div class="flex items-center gap-2 px-3 py-2.5">
            <input type="hidden" name="status" value="{{ $status }}">
            <div class="relative flex-1">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"/></svg>
                <input type="text" name="search" value="{{ $search }}" placeholder="Имя, телефон..."
                    class="w-full pl-9 pr-3 py-2 text-sm border border-slate-200 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <button type="button" @click="showFilters = !showFilters"
                    class="inline-flex items-center gap-1.5 px-2.5 py-2.5 text-xs font-semibold rounded-lg border transition-colors flex-shrink-0"
                    :class="showFilters
                        ? 'bg-blue-50 dark:bg-blue-900/30 border-blue-200 dark:border-blue-700 text-blue-700 dark:text-blue-300'
                        : 'bg-white dark:bg-slate-700 border-slate-200 dark:border-slate-600 text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-600'">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 flex-shrink-0"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6h9.75M10.5 6a1.5 1.5 0 1 1-3 0m3 0a1.5 1.5 0 1 0-3 0M3.75 6H7.5m3 12h9.75m-9.75 0a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m-3.75 0H7.5m9-6h3.75m-3.75 0a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m-9.75 0h9.75"/></svg>
                <span class="hidden sm:inline">Ещё фильтры</span>
                @if($check_in || $check_out || $room || $group_filter)
                <span class="w-1.5 h-1.5 rounded-full bg-blue-500 flex-shrink-0"></span>
                @endif
            </button>
            <button type="submit"
                    class="px-3 py-2 bg-blue-600 text-white text-sm font-semibold rounded-lg hover:bg-blue-700 transition-colors whitespace-nowrap flex-shrink-0">
                <span class="hidden sm:inline">Найти</span>
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4 sm:hidden"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"/></svg>
            </button>
            @if($search || $status || $check_in || $check_out || $room || $group_filter)
            <a href="{{ route('bookings.index') }}"
               data-status-reset
               class="px-2.5 py-2 text-sm text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors whitespace-nowrap flex-shrink-0 hidden sm:block">Сброс</a>
            @endif
        </div>

        {{-- Advanced filters --}}
        <div x-show="showFilters" x-cloak
             x-transition:enter="transition ease-out duration-150"
             x-transition:enter-start="opacity-0 -translate-y-1"
             x-transition:enter-end="opacity-100 translate-y-0"
             class="border-t border-slate-100 dark:border-slate-700 px-3 py-3 flex flex-wrap gap-3">
            <div class="min-w-[8rem] flex-1 sm:flex-none sm:min-w-[9rem]">
                <label class="block text-xs font-semibold text-slate-400 dark:text-slate-500 uppercase tracking-wide mb-1">Тип</label>
                <select name="group_filter" class="w-full px-3 py-1.5 border border-slate-200 dark:border-slate-600 rounded-lg text-sm bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">Все</option>
                    <option value="individual" @selected($group_filter === 'individual')>Индивидуальные</option>
                    <option value="group"      @selected($group_filter === 'group')>Групповые</option>
                </select>
            </div>
            <div class="min-w-[6rem] flex-1 sm:flex-none sm:min-w-[7rem]">
                <label class="block text-xs font-semibold text-slate-400 dark:text-slate-500 uppercase tracking-wide mb-1">Номер</label>
                <input type="text" name="room" value="{{ $room }}" placeholder="101…"
                    class="w-full px-3 py-1.5 border border-slate-200 dark:border-slate-600 rounded-lg text-sm bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div class="min-w-[8rem] flex-1 sm:flex-none sm:min-w-[9rem]">
                <label class="block text-xs font-semibold text-slate-400 dark:text-slate-500 uppercase tracking-wide mb-1">Заезд от</label>
                <input type="date" name="check_in" value="{{ $check_in }}"
                    class="w-full px-3 py-1.5 border border-slate-200 dark:border-slate-600 rounded-lg text-sm bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div class="min-w-[8rem] flex-1 sm:flex-none sm:min-w-[9rem]">
                <label class="block text-xs font-semibold text-slate-400 dark:text-slate-500 uppercase tracking-wide mb-1">Выезд до</label>
                <input type="date" name="check_out" value="{{ $check_out }}"
                    class="w-full px-3 py-1.5 border border-slate-200 dark:border-slate-600 rounded-lg text-sm bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
        </div>
    </form>
</div>

{{-- Bookings list --}}
<div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden">

    @forelse($bookings as $booking)
    @php
        $nights = $booking->check_in_date->diffInDays($booking->check_out_date);
        $paid   = $booking->payments->sum('amount');
        $total  = $booking->total_price;

        $payBadge = match(true) {
            $paid >= $total && $total > 0 => ['label' => 'Оплачено',    'class' => 'text-emerald-600 dark:text-emerald-400'],
            $paid > 0                     => ['label' => 'Частично',    'class' => 'text-amber-600 dark:text-amber-400'],
            default                       => ['label' => 'Не оплачено', 'class' => 'text-slate-400 dark:text-slate-500'],
        };

        $statusBorder = match($booking->status->value) {
            'inquiry'     => 'border-l-purple-500',
            'pending'     => 'border-l-amber-400',
            'confirmed'   => 'border-l-blue-500',
            'checked_in'  => 'border-l-emerald-500',
            'checked_out' => 'border-l-slate-300 dark:border-l-slate-600',
            'cancelled'   => 'border-l-red-400',
            default       => 'border-l-slate-200',
        };

        $avatarBg = match($booking->status->value) {
            'checked_in'  => 'bg-emerald-100 dark:bg-emerald-900/40 text-emerald-700 dark:text-emerald-300',
            'confirmed'   => 'bg-blue-100 dark:bg-blue-900/40 text-blue-700 dark:text-blue-300',
            'pending'     => 'bg-amber-100 dark:bg-amber-900/40 text-amber-700 dark:text-amber-300',
            'inquiry'     => 'bg-purple-100 dark:bg-purple-900/40 text-purple-700 dark:text-purple-300',
            default       => 'bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300',
        };

        $initials = strtoupper(mb_substr($booking->guest->first_name ?? '?', 0, 1) . mb_substr($booking->guest->last_name ?? '', 0, 1));

        $transitions = $booking->status->allowedTransitions();
        $transitionDefs = [
            'confirmed'   => ['label' => 'Подтвердить',  'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>',                                                                                                                                                                                  'desc' => 'Подтвердить бронирование',           'color' => 'text-blue-700 dark:text-blue-300 hover:bg-blue-50 dark:hover:bg-blue-900/30'],
            'checked_in'  => ['label' => 'Заселить',     'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M8.25 9V5.25A2.25 2.25 0 0 1 10.5 3h6a2.25 2.25 0 0 1 2.25 2.25v13.5A2.25 2.25 0 0 1 16.5 21h-6a2.25 2.25 0 0 1-2.25-2.25V15m-3 0-3-3m0 0 3-3m-3 3H15"/>',  'desc' => 'Гость прибыл и заселился',           'color' => 'text-emerald-700 dark:text-emerald-300 hover:bg-emerald-50 dark:hover:bg-emerald-900/30'],
            'checked_out' => ['label' => 'Выселить',     'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15m3 0 3-3m0 0-3-3m3 3H9"/>',   'desc' => 'Гость выселился, номер освободить',  'color' => 'text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700/50'],
            'cancelled'   => ['label' => 'Отменить',     'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>',                                                                                                                                                                                                                          'desc' => 'Отменить бронирование полностью',    'color' => 'text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/30'],
            'no_show'     => ['label' => 'Не явился',    'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/>',                                                         'desc' => 'Гость не прибыл, номер освободить',  'color' => 'text-orange-600 dark:text-orange-400 hover:bg-orange-50 dark:hover:bg-orange-900/30'],
        ];

        $allActions = array_filter($transitions, fn($t) => isset($transitionDefs[$t->value]));

        // Primary = first positive action (not cancel/no_show)
        $primaryTransition = null;
        foreach ($allActions as $t) {
            if (! in_array($t->value, ['cancelled', 'no_show'])) {
                $primaryTransition = $t;
                break;
            }
        }

        $dropdownId = 'booking-actions-' . $booking->id;
        $nightsLabel = $nights === 1 ? 'ночь' : ($nights < 5 ? 'ночи' : 'ночей');
    @endphp

    <div class="group border-b border-slate-100 dark:border-slate-700 last:border-b-0 border-l-4 {{ $statusBorder }}
                hover:bg-slate-50/60 dark:hover:bg-slate-700/30 transition-colors">

        {{-- ══════════ MOBILE LAYOUT (< sm) ══════════ --}}
        <div class="sm:hidden">
            <a href="{{ route('bookings.show', $booking) }}" class="flex items-start gap-3 px-4 pt-3 pb-2">
                {{-- Avatar --}}
                <div class="w-8 h-8 rounded-full {{ $avatarBg }} flex items-center justify-center text-xs font-bold flex-shrink-0 mt-0.5 select-none">
                    {{ $initials }}
                </div>
                {{-- Info --}}
                <div class="flex-1 min-w-0">
                    <div class="flex items-start justify-between gap-2">
                        <p class="text-sm font-semibold text-slate-900 dark:text-slate-100 truncate leading-snug">{{ $booking->guest->fullName }}</p>
                        <div class="flex-shrink-0 text-right">
                            <p class="text-sm font-semibold text-slate-900 dark:text-slate-100 whitespace-nowrap">
                                {{ number_format($total, 0, '.', ' ') }}&nbsp;<span class="text-xs font-normal text-slate-400">сум</span>
                            </p>
                        </div>
                    </div>
                    <div class="flex items-center justify-between mt-1">
                        <p class="text-xs text-slate-400 dark:text-slate-500 truncate">{{ $booking->guest->phone ?? $booking->guest->email ?? '—' }}</p>
                        <p class="text-xs {{ $payBadge['class'] }} flex-shrink-0 ml-2">{{ $payBadge['label'] }}</p>
                    </div>
                    <div class="flex items-center gap-1.5 mt-1.5 text-xs text-slate-500 dark:text-slate-400 flex-wrap">
                        <span class="font-semibold text-slate-700 dark:text-slate-300">Ном.&nbsp;{{ $booking->room->number }}</span>
                        <span class="text-slate-300 dark:text-slate-600">·</span>
                        <span>{{ $booking->check_in_date->format('d M') }}&nbsp;→&nbsp;{{ $booking->check_out_date->format('d M Y') }}</span>
                        <span class="text-slate-400 dark:text-slate-500">· {{ $nights }}&nbsp;{{ $nightsLabel }}</span>
                        @if($booking->adults)
                        <span class="text-slate-400 dark:text-slate-500">· {{ $booking->adults }}&nbsp;чел.</span>
                        @endif
                    </div>
                </div>
            </a>
            {{-- Mobile footer: status + action --}}
            <div class="flex items-center justify-between px-4 pb-3 pl-15" style="padding-left:3.25rem">
                <x-status-badge :status="$booking->status" />
                <div class="flex items-center gap-2">
                    @if($primaryTransition)
                    @php $pt = $transitionDefs[$primaryTransition->value]; @endphp
                    <form method="POST" action="{{ route('bookings.status', $booking) }}">
                        @csrf
                        <input type="hidden" name="transition" value="{{ $primaryTransition->value }}">
                        <button type="submit"
                                class="inline-flex items-center px-2.5 py-1 text-xs font-semibold rounded-lg transition-colors {{ $pt['color'] }}"
                                @if($primaryTransition->value === 'checked_out') onclick="return confirm('Выселить гостя?')" @endif>
                            {{ $pt['label'] }}
                        </button>
                    </form>
                    @endif
                    <a href="{{ route('bookings.show', $booking) }}"
                       class="w-7 h-7 flex items-center justify-center rounded-lg text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5"/></svg>
                    </a>
                </div>
            </div>
        </div>

        {{-- ══════════ DESKTOP LAYOUT (sm+) ══════════ --}}
        <div class="hidden sm:flex items-stretch">

            {{-- Checkbox --}}
            @if(!in_array($booking->status->value, ['cancelled','no_show','checked_out']))
            <div class="flex items-center pl-3 pr-0 flex-shrink-0" @click.stop>
                <div @click.stop="toggleSelect({{ $booking->id }}, '{{ $booking->status->value }}')"
                     :class="isSelected({{ $booking->id }})
                         ? 'bg-blue-600 border-blue-600 shadow-sm shadow-blue-200 dark:shadow-blue-900 opacity-100 scale-100'
                         : 'bg-white dark:bg-slate-800 border-slate-300 dark:border-slate-600 opacity-0 group-hover:opacity-100 group-hover:scale-100 scale-90'"
                     class="w-5 h-5 rounded-md border-2 flex items-center justify-center cursor-pointer transition-all duration-150 flex-shrink-0">
                    <template x-if="isSelected({{ $booking->id }})">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor" class="w-3 h-3 text-white pointer-events-none">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/>
                        </svg>
                    </template>
                </div>
            </div>
            @else
            <div class="w-8 flex-shrink-0"></div>
            @endif

            {{-- Main clickable area --}}
            <a href="{{ route('bookings.show', $booking) }}" class="flex-1 flex items-center gap-4 pl-3 pr-5 py-4 min-w-0">

                {{-- Avatar --}}
                <div class="w-9 h-9 rounded-full {{ $avatarBg }} flex items-center justify-center text-xs font-bold flex-shrink-0 select-none">
                    {{ $initials }}
                </div>

                {{-- Guest --}}
                <div class="min-w-0 w-40 flex-shrink-0">
                    <p class="text-sm font-semibold text-slate-900 dark:text-slate-100 truncate">{{ $booking->guest->fullName }}</p>
                    <p class="text-xs text-slate-400 dark:text-slate-500 truncate mt-0.5">{{ $booking->guest->phone ?? $booking->guest->email ?? '—' }}</p>
                </div>

                {{-- Room --}}
                <div class="hidden md:block flex-shrink-0 w-28">
                    <p class="text-sm font-bold text-slate-900 dark:text-slate-100">Ном. {{ $booking->room->number }}</p>
                    <p class="text-xs text-slate-400 dark:text-slate-500 mt-0.5 truncate">{{ $booking->room->roomType->name }}</p>
                </div>

                {{-- Group badge --}}
                @if($booking->bookingGroup)
                <a href="{{ route('group-bookings.show', $booking->bookingGroup) }}"
                   onclick="event.stopPropagation()"
                   class="hidden lg:inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-xs font-semibold bg-violet-50 dark:bg-violet-900/30 text-violet-700 dark:text-violet-300 ring-1 ring-violet-200 dark:ring-violet-700 hover:bg-violet-100 dark:hover:bg-violet-900/50 transition-colors flex-shrink-0 whitespace-nowrap">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-3 h-3"><path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.058 2.772m0 0a3 3 0 0 0-4.681 2.72 8.986 8.986 0 0 0 3.74.477m.94-3.197a5.971 5.971 0 0 0-.94 3.197"/></svg>
                    {{ $booking->bookingGroup->group_ref }}
                </a>
                @endif

                {{-- Dates --}}
                <div class="hidden md:block flex-shrink-0 w-44"
                     x-data="{ tip: false, ty: 0, tx: 0 }"
                     @mouseenter="tip = true; const r = $el.getBoundingClientRect(); ty = r.top - 8; tx = r.left"
                     @mouseleave="tip = false">
                    <p class="text-sm text-slate-700 dark:text-slate-300 whitespace-nowrap">
                        {{ $booking->check_in_date->format('d M') }}
                        <span class="text-slate-300 dark:text-slate-600 mx-1">→</span>
                        {{ $booking->check_out_date->format('d M Y') }}
                    </p>
                    <p class="text-xs text-slate-400 dark:text-slate-500 mt-0.5">
                        {{ $nights }}&nbsp;{{ $nightsLabel }}
                        @if($booking->adults)
                        <span class="mx-1 text-slate-300 dark:text-slate-600">·</span>{{ $booking->adults }}&nbsp;чел.
                        @endif
                    </p>
                    @if($booking->isOverdue())
                        <span class="inline-flex items-center gap-1 mt-1 px-1.5 py-0.5 bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-300 text-[10px] font-bold rounded">
                            <span class="w-1.5 h-1.5 rounded-full bg-red-500 animate-pulse"></span>Просрочен
                        </span>
                    @elseif($booking->hasDiscrepancy())
                        <span class="inline-flex items-center gap-1 mt-1 px-1.5 py-0.5 bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-300 text-[10px] font-bold rounded">
                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>Расхождение
                        </span>
                    @endif
                    <template x-teleport="body">
                        <div x-show="tip" x-cloak
                             :style="`position:fixed;top:${ty}px;left:${tx}px;transform:translateY(-100%) translateY(-6px);z-index:9999`"
                             class="w-56 bg-slate-900 text-white rounded-xl shadow-2xl p-3 pointer-events-none">
                            <div class="space-y-2 text-xs">
                                <div class="flex items-center gap-2">
                                    <span class="text-blue-400 font-semibold shrink-0">Заезд</span>
                                    <span class="ml-auto font-semibold">{{ $booking->check_in_date->format('d M Y') }}</span>
                                    @if($booking->check_in_time)
                                    <span class="text-blue-400 font-bold tabular-nums">{{ substr($booking->check_in_time, 0, 5) }}</span>
                                    @endif
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="text-orange-400 font-semibold shrink-0">Выезд</span>
                                    <span class="ml-auto font-semibold">{{ $booking->check_out_date->format('d M Y') }}</span>
                                    @if($booking->check_out_time)
                                    <span class="text-orange-400 font-bold tabular-nums">{{ substr($booking->check_out_time, 0, 5) }}</span>
                                    @endif
                                </div>
                                <div class="border-t border-slate-700 pt-1.5 flex items-center gap-2">
                                    <span class="text-slate-400 shrink-0">Создано</span>
                                    <span class="font-semibold ml-auto tabular-nums">{{ $booking->created_at->format('d M, H:i') }}</span>
                                </div>
                                @if($booking->creator)
                                <div class="flex items-center gap-2">
                                    <span class="text-slate-400 shrink-0">Кем</span>
                                    <span class="font-semibold ml-auto truncate">{{ $booking->creator->name }}</span>
                                </div>
                                @endif
                            </div>
                            <div class="absolute top-full left-5 border-4 border-transparent border-t-slate-900"></div>
                        </div>
                    </template>
                </div>

                {{-- Status --}}
                <div class="hidden lg:block flex-shrink-0">
                    <x-status-badge :status="$booking->status" />
                </div>

            </a>

            {{-- Amount --}}
            <div class="flex items-center justify-end px-4 flex-shrink-0 my-auto">
                <div class="text-right">
                    <p class="text-sm font-semibold text-slate-900 dark:text-slate-100 whitespace-nowrap">
                        {{ number_format($total, 0, '.', ' ') }} <span class="text-slate-400 dark:text-slate-500 font-normal text-xs">сум</span>
                    </p>
                    <p class="text-xs {{ $payBadge['class'] }} mt-0.5">{{ $payBadge['label'] }}</p>
                </div>
            </div>

            {{-- Actions dropdown --}}
            @if(count($allActions) > 0)
            <div class="relative flex items-center px-4 border-l border-slate-100 dark:border-slate-700 my-auto">
                <button type="button"
                        @click.stop="activeDropdown = activeDropdown === '{{ $dropdownId }}' ? null : '{{ $dropdownId }}'"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-slate-600 dark:text-slate-300 bg-white dark:bg-slate-700 border border-slate-200 dark:border-slate-600 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-600 transition-colors"
                        :class="{ 'bg-slate-50 dark:bg-slate-600': activeDropdown === '{{ $dropdownId }}' }">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20" class="w-4 h-4">
                        <path d="M10 3a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3ZM10 8.5a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3ZM11.5 15.5a1.5 1.5 0 1 0-3 0 1.5 1.5 0 0 0 3 0Z"/>
                    </svg>
                    Действия
                </button>
                <div x-show="activeDropdown === '{{ $dropdownId }}'" x-cloak
                     x-transition:enter="transition ease-out duration-100"
                     x-transition:enter-start="opacity-0 scale-95"
                     x-transition:enter-end="opacity-100 scale-100"
                     x-transition:leave="transition ease-in duration-75"
                     x-transition:leave-start="opacity-100 scale-100"
                     x-transition:leave-end="opacity-0 scale-95"
                     @click.outside="activeDropdown = null"
                     class="absolute right-4 top-full mt-0.5 w-72 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl shadow-xl z-30 overflow-hidden">
                    <div class="py-1.5">
                        @foreach($allActions as $transition)
                        @php $t = $transitionDefs[$transition->value] @endphp
                        @if($t)
                        <div class="px-2">
                            <form method="POST" action="{{ route('bookings.status', $booking) }}">
                                @csrf
                                <input type="hidden" name="transition" value="{{ $transition->value }}">
                                <button type="submit"
                                        class="w-full flex items-center gap-3 px-3 py-2.5 rounded-lg {{ $t['color'] }} transition-colors text-left"
                                        @if($transition->value === 'checked_out') onclick="return confirm('Выселить гостя? Номер будет освобождён для уборки.')" @endif
                                        @if($transition->value === 'cancelled') onclick="return confirm('Отменить бронирование #{{ $booking->id }}? Это действие нельзя отменить.')" @endif
                                        @if($transition->value === 'no_show') onclick="return confirm('Отметить как «Не явился»? Бронирование будет закрыто.')" @endif>
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 flex-shrink-0 opacity-70">
                                        {!! $t['icon'] !!}
                                    </svg>
                                    <div class="min-w-0">
                                        <span class="text-sm font-semibold block">{{ $t['label'] }}</span>
                                        <span class="text-xs opacity-70 block leading-tight">{{ $t['desc'] }}</span>
                                    </div>
                                </button>
                            </form>
                        </div>
                        @if(!$loop->last)
                        <div class="mx-3 my-1 border-t border-slate-100 dark:border-slate-700"></div>
                        @endif
                        @endif
                        @endforeach
                    </div>
                </div>
            </div>
            @endif

            {{-- Quick view + open --}}
            <div class="flex items-center gap-1 flex-shrink-0 my-auto {{ count($allActions) === 0 ? 'px-4 border-l border-slate-100 dark:border-slate-700' : 'pr-2' }}">
                <button @click.stop="quickViewBooking = @js([
                    'id'             => $booking->id,
                    'guest'          => ['first_name' => $booking->guest->first_name, 'last_name' => $booking->guest->last_name, 'full_name' => $booking->guest->fullName, 'phone' => $booking->guest->phone, 'email' => $booking->guest->email],
                    'room'           => ['number' => $booking->room->number],
                    'room_type_name' => $booking->room->roomType->name,
                    'check_in'       => $booking->check_in_date->format('d M Y'),
                    'check_in_time'  => $booking->check_in_time ? substr($booking->check_in_time, 0, 5) : null,
                    'check_out'      => $booking->check_out_date->format('d M Y'),
                    'check_out_time' => $booking->check_out_time ? substr($booking->check_out_time, 0, 5) : null,
                    'nights'         => $nights,
                    'nights_label'   => $nightsLabel,
                    'adults'         => $booking->adults,
                    'status'         => $booking->status->value,
                    'status_label'   => $booking->status->label(),
                    'total_price'    => $booking->total_price,
                ]);"
                       class="inline-flex items-center justify-center w-9 h-9 rounded-lg text-slate-400 dark:text-slate-500 hover:text-blue-600 dark:hover:text-blue-400 hover:bg-blue-50 dark:hover:bg-blue-900/30 transition-colors flex-shrink-0"
                       title="Быстрый просмотр">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/></svg>
                </button>
                <a href="{{ route('bookings.show', $booking) }}"
                   class="inline-flex items-center justify-center w-9 h-9 rounded-lg text-slate-400 dark:text-slate-500 hover:text-slate-700 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors flex-shrink-0"
                   title="Открыть">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5"/></svg>
                </a>
            </div>

        </div>{{-- /desktop --}}

    </div>

    @empty
    <div class="px-6 py-16 text-center">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-12 h-12 mx-auto text-slate-300 dark:text-slate-600 mb-3"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5"/></svg>
        <p class="text-slate-400 dark:text-slate-500 text-sm mb-3">Нет бронирований</p>
        @if($search || $status || $check_in || $check_out)
            <a href="{{ route('bookings.index') }}" class="text-blue-600 dark:text-blue-400 text-sm hover:underline font-medium">Сбросить фильтры</a>
        @else
            <a href="{{ route('bookings.create') }}"
               class="inline-flex items-center gap-1.5 px-4 py-2 bg-blue-600 text-white text-sm font-semibold rounded-lg hover:bg-blue-700 transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                Создать первое бронирование
            </a>
        @endif
    </div>
    @endforelse

</div>

@if($bookings->hasPages())
<div class="mt-4">
    {{ $bookings->links() }}
</div>
@endif

{{-- Quick View Slide-Over (desktop only — hidden on mobile) --}}
<div x-show="quickViewBooking" x-cloak
     x-transition:enter="transition ease-out duration-200"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition ease-in duration-150"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     class="fixed inset-0 z-50 hidden sm:flex justify-end"
     @keydown.escape.window="quickViewBooking = null">
    <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" @click="quickViewBooking = null"></div>

    <div x-show="quickViewBooking"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="translate-x-full"
         x-transition:enter-end="translate-x-0"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="translate-x-0"
         x-transition:leave-end="translate-x-full"
         class="relative w-full max-w-md bg-white dark:bg-slate-800 shadow-2xl overflow-y-auto">

        <div class="sticky top-0 z-10 bg-white dark:bg-slate-800 border-b border-slate-200 dark:border-slate-700 px-5 py-4 flex items-center justify-between">
            <h3 class="text-base font-bold text-slate-900 dark:text-white">Бронирование</h3>
            <button @click="quickViewBooking = null" class="w-8 h-8 rounded-lg text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-700 flex items-center justify-center transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <div class="p-5 space-y-4">
            <template x-if="quickViewBooking">
                <div class="space-y-4">
                    <div class="flex items-center gap-3">
                        <div class="w-11 h-11 rounded-full bg-blue-100 dark:bg-blue-900/40 text-blue-700 dark:text-blue-300 flex items-center justify-center text-sm font-bold flex-shrink-0"
                             x-text="(quickViewBooking.guest?.first_name?.[0] || '') + (quickViewBooking.guest?.last_name?.[0] || '')">
                        </div>
                        <div>
                            <p class="text-sm font-bold text-slate-900 dark:text-white" x-text="quickViewBooking.guest?.full_name"></p>
                            <p class="text-xs text-slate-400" x-text="quickViewBooking.guest?.phone || quickViewBooking.guest?.email"></p>
                        </div>
                    </div>

                    <div class="bg-slate-50 dark:bg-slate-700/50 rounded-xl p-3.5">
                        <p class="text-xs text-slate-500 dark:text-slate-400 mb-1">Номер</p>
                        <p class="text-sm font-bold text-slate-900 dark:text-white">Ном. <span x-text="quickViewBooking.room?.number"></span></p>
                        <p class="text-xs text-slate-400" x-text="quickViewBooking.room_type_name"></p>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div class="bg-slate-50 dark:bg-slate-700/50 rounded-xl p-3.5">
                            <p class="text-xs text-slate-500 dark:text-slate-400 mb-1">Заезд</p>
                            <p class="text-sm font-semibold text-slate-900 dark:text-white" x-text="quickViewBooking.check_in"></p>
                            <p x-show="quickViewBooking.check_in_time" class="text-xs font-bold text-blue-600 dark:text-blue-400 mt-0.5 tabular-nums" x-text="quickViewBooking.check_in_time"></p>
                        </div>
                        <div class="bg-slate-50 dark:bg-slate-700/50 rounded-xl p-3.5">
                            <p class="text-xs text-slate-500 dark:text-slate-400 mb-1">Выезд</p>
                            <p class="text-sm font-semibold text-slate-900 dark:text-white" x-text="quickViewBooking.check_out"></p>
                            <p x-show="quickViewBooking.check_out_time" class="text-xs font-bold text-orange-600 dark:text-orange-400 mt-0.5 tabular-nums" x-text="quickViewBooking.check_out_time"></p>
                        </div>
                        <div class="bg-slate-50 dark:bg-slate-700/50 rounded-xl p-3.5">
                            <p class="text-xs text-slate-500 dark:text-slate-400 mb-1">Ночей</p>
                            <p class="text-sm font-semibold text-slate-900 dark:text-white" x-text="quickViewBooking.nights + ' ' + quickViewBooking.nights_label"></p>
                        </div>
                        <div class="bg-slate-50 dark:bg-slate-700/50 rounded-xl p-3.5">
                            <p class="text-xs text-slate-500 dark:text-slate-400 mb-1">Гостей</p>
                            <p class="text-sm font-semibold text-slate-900 dark:text-white" x-text="quickViewBooking.adults + ' чел.'"></p>
                        </div>
                    </div>

                    <div class="flex items-center justify-between">
                        <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-semibold"
                              :class="{
                                  'bg-purple-50 text-purple-700 dark:bg-purple-900/30 dark:text-purple-300': quickViewBooking.status === 'inquiry',
                                  'bg-amber-50 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300': quickViewBooking.status === 'pending',
                                  'bg-blue-50 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300': quickViewBooking.status === 'confirmed',
                                  'bg-emerald-50 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300': quickViewBooking.status === 'checked_in',
                                  'bg-slate-50 text-slate-500 dark:bg-slate-700 dark:text-slate-300': quickViewBooking.status === 'checked_out',
                                  'bg-red-50 text-red-600 dark:bg-red-900/20 dark:text-red-400': ['cancelled','no_show'].includes(quickViewBooking.status),
                              }"
                              x-text="quickViewBooking.status_label">
                        </span>
                        <p class="text-lg font-bold text-slate-900 dark:text-white" x-text="quickViewBooking.total_price ? Number(quickViewBooking.total_price).toLocaleString('ru-RU') + ' сум' : ''"></p>
                    </div>
                </div>
            </template>

            <a :href="'/bookings/' + quickViewBooking?.id"
               class="block w-full text-center px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-xl transition-colors">
                Открыть полностью →
            </a>
        </div>
    </div>
</div>

{{-- Bulk action floating bar --}}
<div x-show="selectedIds.length > 0" x-cloak
     class="fixed bottom-6 left-1/2 -translate-x-1/2 z-50 flex items-center gap-2 bg-slate-900 dark:bg-slate-800 text-white px-4 py-3 rounded-2xl shadow-2xl border border-slate-700 dark:border-slate-600">
    <span class="text-sm font-medium pr-1 border-r border-slate-700 mr-1">
        <span x-text="selectedIds.length" class="font-bold text-blue-400"></span> выбрано
    </span>

    {{-- Dynamic transition buttons --}}
    <template x-for="t in availableTransitions()" :key="t">
        <button @click="bulkAction(t)"
                :class="transitionDef(t).cls"
                class="inline-flex items-center px-3 py-1.5 text-white text-xs font-semibold rounded-lg transition-colors"
                x-text="transitionDef(t).label">
        </button>
    </template>

    {{-- No transitions available (all selected are terminal statuses) --}}
    <template x-if="availableTransitions().length === 0">
        <span class="text-xs text-slate-400 italic px-1">Нет доступных действий</span>
    </template>

    <button @click="clearSelected()"
            class="ml-1 px-3 py-1.5 bg-slate-700 hover:bg-slate-600 text-slate-300 text-xs font-semibold rounded-lg transition-colors">
        Сброс
    </button>
</div>

</div>{{-- /x-data --}}

<script>
window._bookingBulkAction = function (transition, selectedIds) {
    if (!selectedIds || selectedIds.length === 0) return;
    const form = document.getElementById('bulk-form');
    document.getElementById('bulk-transition-input').value = transition;
    form.querySelectorAll('input[name="booking_ids[]"]').forEach(function (el) { el.remove(); });
    selectedIds.forEach(function (id) {
        const inp = document.createElement('input');
        inp.type = 'hidden';
        inp.name = 'booking_ids[]';
        inp.value = id;
        form.appendChild(inp);
    });
    form.submit();
};

(function () {
    const KEY = 'bookings_status_filter';
    const url  = new URL(window.location.href);

    // If landing with no status param, restore last saved filter
    if (!url.searchParams.has('status') && !url.searchParams.has('search')
        && !url.searchParams.has('check_in') && !url.searchParams.has('check_out')
        && !url.searchParams.has('room') && !url.searchParams.has('group_filter')) {
        const saved = localStorage.getItem(KEY);
        if (saved && saved !== '') {
            url.searchParams.set('status', saved);
            window.history.replaceState(null, '', url.toString());
        }
    }

    // Save status on pill click; clear on reset links
    document.addEventListener('click', function (e) {
        const pill = e.target.closest('a[data-status-pill]');
        if (pill) {
            const val = pill.dataset.statusPill;
            if (val === '') localStorage.removeItem(KEY);
            else localStorage.setItem(KEY, val);
            return;
        }
        const reset = e.target.closest('a[data-status-reset]');
        if (reset) {
            localStorage.removeItem(KEY);
        }
    });
})();
</script>

@endsection
