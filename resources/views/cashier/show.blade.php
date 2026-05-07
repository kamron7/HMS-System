@extends('layouts.app')

@section('title', 'Смена #' . $shift->id)

@section('content')
<div class="mb-6 flex items-center justify-between">
    <div class="flex items-center gap-3">
        <a href="{{ route('cashier.index') }}"
           class="inline-flex items-center gap-1.5 text-sm text-slate-500 hover:text-slate-700 transition-colors">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18"/></svg>
            Касса
        </a>
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 text-slate-300"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5"/></svg>
        <h1 class="text-xl font-bold text-slate-900">Смена #{{ $shift->id }} — {{ $shift->shift }}</h1>
    </div>
</div>

{{-- Summary cards --}}
<div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-6">
    <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm p-4">
        <p class="text-xs font-semibold text-slate-400 uppercase mb-1">Кассир</p>
        <p class="text-sm font-bold text-slate-900 dark:text-slate-100">{{ $shift->user->name }}</p>
        <p class="text-xs text-slate-400">{{ $shift->opened_at->format('d.m.Y H:i') }} — {{ $shift->closed_at?->format('H:i') ?? '...' }}</p>
    </div>
    <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm p-4">
        <p class="text-xs font-semibold text-slate-400 uppercase mb-1">Было</p>
        <p class="text-lg font-bold text-slate-900 dark:text-slate-100">{{ number_format($shift->opening_actual, 0, '.', ' ') }}</p>
        @if($shift->opening_difference != 0)
        <p class="text-xs {{ $shift->opening_difference < 0 ? 'text-red-500' : 'text-emerald-500' }}">
            {{ ($shift->opening_difference >= 0 ? '+' : '') . number_format($shift->opening_difference, 0, '.', ' ') }}
        </p>
        @endif
    </div>
    <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm p-4">
        <p class="text-xs font-semibold text-slate-400 uppercase mb-1">Принято</p>
        <p class="text-lg font-bold text-emerald-600">{{ number_format($shift->cash_in, 0, '.', ' ') }}</p>
    </div>
    <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm p-4">
        <p class="text-xs font-semibold text-slate-400 uppercase mb-1">Выдано</p>
        <p class="text-lg font-bold text-red-600">{{ number_format($shift->cash_out, 0, '.', ' ') }}</p>
    </div>
    <div class="bg-white dark:bg-slate-800 rounded-xl border border-2 {{ $shift->closing_difference == 0 ? 'border-emerald-200 dark:border-emerald-800' : 'border-red-200 dark:border-red-800' }} shadow-sm p-4">
        <p class="text-xs font-semibold text-slate-400 uppercase mb-1">Разница</p>
        <p class="text-lg font-extrabold {{ $shift->closing_difference == 0 ? 'text-emerald-600' : 'text-red-600' }}">
            {{ ($shift->closing_difference >= 0 ? '+' : '') . number_format($shift->closing_difference, 0, '.', ' ') }}
        </p>
        @if($shift->notes_close)
        <p class="text-[10px] text-slate-400 mt-0.5 truncate" title="{{ $shift->notes_close }}">{{ $shift->notes_close }}</p>
        @endif
    </div>
</div>

{{-- Full timeline --}}
<div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden mb-6">
    <div class="px-5 py-4 border-b border-slate-100 dark:border-slate-700">
        <h2 class="text-sm font-bold text-slate-900 dark:text-slate-100">
            Все операции
            <span class="text-slate-400 font-normal">({{ $timeline->count() }})</span>
        </h2>
        <p class="text-xs text-slate-400 mt-0.5">
            {{ $shift->opened_at->format('d.m.Y H:i') }} → {{ $shift->closed_at?->format('d.m.Y H:i') }}
        </p>
    </div>

    @if($timeline->isEmpty())
    <p class="px-5 py-10 text-center text-sm text-slate-400">Нет операций за смену</p>
    @else
    {{-- Summary by type --}}
    <div class="px-5 py-3 bg-slate-50 dark:bg-slate-900/30 border-b border-slate-100 dark:border-slate-700 flex items-center gap-4 text-xs flex-wrap">
        <span class="font-semibold text-slate-500">Итого:</span>
        <span class="text-emerald-600 font-bold">
            +{{ number_format($timeline->where('direction', 'in')->sum('amount'), 0, '.', ' ') }}
        </span>
        <span class="text-red-600 font-bold">
            −{{ number_format(abs($timeline->where('direction', 'out')->sum('amount')), 0, '.', ' ') }}
        </span>
        <span class="text-slate-400 ml-auto">
            Чистый поток: <span class="font-bold {{ ($timeline->sum('amount') >= 0) ? 'text-emerald-600' : 'text-red-600' }}">
                {{ ($timeline->sum('amount') >= 0 ? '+' : '') . number_format($timeline->sum('amount'), 0, '.', ' ') }}
            </span>
        </span>
    </div>

    <table class="w-full text-sm">
        <thead>
            <tr class="bg-slate-50 dark:bg-slate-900/50 border-b border-slate-100 dark:border-slate-700">
                <th class="text-left px-5 py-2.5 text-xs font-semibold text-slate-500 uppercase">Время</th>
                <th class="text-left px-5 py-2.5 text-xs font-semibold text-slate-500 uppercase">Тип</th>
                <th class="text-left px-5 py-2.5 text-xs font-semibold text-slate-500 uppercase">Описание</th>
                <th class="text-left px-5 py-2.5 text-xs font-semibold text-slate-500 uppercase">Гость / Номер</th>
                <th class="text-left px-5 py-2.5 text-xs font-semibold text-slate-500 uppercase">Детали</th>
                <th class="text-right px-5 py-2.5 text-xs font-semibold text-slate-500 uppercase">Сумма</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
            @foreach($timeline as $op)
            <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/30 transition-colors {{ $op['direction'] === 'out' ? 'bg-red-50/30 dark:bg-red-900/5' : '' }}">
                <td class="px-5 py-3 text-xs text-slate-400 font-mono whitespace-nowrap">
                    {{ $op['time']->format('H:i:s') }}
                </td>
                <td class="px-5 py-3">
                    @if($op['type'] === 'payment')
                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-semibold bg-emerald-50 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400">Оплата</span>
                    @elseif($op['type'] === 'deposit')
                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-semibold bg-amber-50 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400">Залог</span>
                    @elseif($op['type'] === 'charge')
                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-semibold bg-blue-50 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400">Услуга</span>
                    @else
                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-semibold bg-red-50 text-red-700 dark:bg-red-900/30 dark:text-red-400">Расход</span>
                    @endif
                </td>
                <td class="px-5 py-3">
                    @if($op['booking_id'])
                    <a href="{{ route('bookings.show', $op['booking_id']) }}" class="text-blue-600 hover:text-blue-700 text-xs font-medium">
                        {{ $op['booking_ref'] ?? '#' . $op['booking_id'] }}
                    </a>
                    @endif
                    <span class="text-xs text-slate-600 dark:text-slate-300 ml-1">{{ $op['description'] }}</span>
                </td>
                <td class="px-5 py-3">
                    @if($op['guest'])
                    <a href="{{ route('guests.show', $op['guest_id']) }}" class="text-xs text-blue-600 hover:text-blue-700">
                        {{ $op['guest'] }}
                    </a>
                    @endif
                    @if($op['room_number'])
                    <a href="{{ route('rooms.edit', $op['room_id']) }}" class="text-xs text-slate-500 hover:text-blue-600 ml-1 font-mono">
                        №{{ $op['room_number'] }}
                    </a>
                    @endif
                    @if(!$op['guest'] && !$op['room_number'])
                    <span class="text-xs text-slate-400">—</span>
                    @endif
                </td>
                <td class="px-5 py-3">
                    @if($op['detail'])
                    <span class="text-xs text-slate-500 bg-slate-100 dark:bg-slate-700 px-2 py-0.5 rounded-full">{{ $op['detail'] }}</span>
                    @elseif($op['user'])
                    <span class="text-xs text-slate-400">от {{ $op['user'] }}</span>
                    @else
                    <span class="text-xs text-slate-300">—</span>
                    @endif
                </td>
                <td class="px-5 py-3 text-right">
                    <span class="font-bold text-sm {{ $op['direction'] === 'in' ? 'text-emerald-600' : 'text-red-600' }}">
                        {{ ($op['direction'] === 'in' ? '+' : '−') . number_format(abs($op['amount']), 0, '.', ' ') }}
                    </span>
                </td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="bg-slate-50 dark:bg-slate-900/50 border-t-2 border-slate-200 dark:border-slate-700">
                <td colspan="5" class="px-5 py-3 text-xs font-bold text-slate-700 dark:text-slate-300">
                    ИТОГО за смену
                </td>
                <td class="px-5 py-3 text-right">
                    <span class="text-base font-extrabold {{ $timeline->sum('amount') >= 0 ? 'text-emerald-600' : 'text-red-600' }}">
                        {{ ($timeline->sum('amount') >= 0 ? '+' : '−') . number_format(abs($timeline->sum('amount')), 0, '.', ' ') }} сум
                    </span>
                </td>
            </tr>
        </tfoot>
    </table>
    @endif
</div>

{{-- Notes --}}
@if($shift->notes_open || $shift->notes_close)
<div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm p-5">
    <h2 class="text-sm font-bold text-slate-900 dark:text-slate-100 mb-3">Заметки</h2>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        @if($shift->notes_open)
        <div>
            <p class="text-xs font-semibold text-slate-400 uppercase mb-1">Открытие</p>
            <p class="text-sm text-slate-700 dark:text-slate-300 bg-slate-50 dark:bg-slate-700 rounded-lg px-3 py-2">{{ $shift->notes_open }}</p>
        </div>
        @endif
        @if($shift->notes_close)
        <div>
            <p class="text-xs font-semibold text-slate-400 uppercase mb-1">Закрытие</p>
            <p class="text-sm text-slate-700 dark:text-slate-300 bg-slate-50 dark:bg-slate-700 rounded-lg px-3 py-2">{{ $shift->notes_close }}</p>
        </div>
        @endif
    </div>
</div>
@endif
@endsection
