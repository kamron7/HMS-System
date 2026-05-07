@extends('layouts.app')

@section('title', 'Дневной отчёт — ' . $date)

@section('content')
<div class="mb-6 flex items-center justify-between">
    <div class="flex items-center gap-3">
        <a href="{{ route('cashier.index') }}"
           class="inline-flex items-center gap-1.5 text-sm text-slate-500 hover:text-slate-700 transition-colors">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18"/></svg>
            Касса
        </a>
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 text-slate-300"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5"/></svg>
        <h1 class="text-xl font-bold text-slate-900">Дневной отчёт — {{ \Carbon\Carbon::parse($date)->format('d.m.Y') }}</h1>
    </div>
    <div class="flex items-center gap-2">
        @foreach($availableDates->take(7) as $d)
        <a href="{{ route('cashier.daily', ['date' => $d]) }}"
           class="px-3 py-1.5 text-xs font-semibold rounded-lg transition-colors {{ $d == $date ? 'bg-blue-600 text-white' : 'bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-600' }}">
            {{ \Carbon\Carbon::parse($d)->format('d.m') }}
        </a>
        @endforeach
    </div>
</div>

{{-- Summary cards --}}
<div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-6">
    <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm p-4">
        <p class="text-xs font-semibold text-slate-400 uppercase mb-1">Смен закрыто</p>
        <p class="text-2xl font-bold text-slate-900 dark:text-slate-100">{{ $shifts->count() }}</p>
    </div>
    <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm p-4">
        <p class="text-xs font-semibold text-slate-400 uppercase mb-1">Всего начало</p>
        <p class="text-lg font-bold text-slate-900 dark:text-slate-100">{{ number_format($totalOpen, 0, '.', ' ') }}</p>
    </div>
    <div class="bg-white dark:bg-slate-800 rounded-xl border border-emerald-200 dark:border-emerald-800 shadow-sm p-4">
        <p class="text-xs font-semibold text-slate-400 uppercase mb-1">Принято наличными</p>
        <p class="text-lg font-bold text-emerald-600">+{{ number_format($totalIn, 0, '.', ' ') }}</p>
    </div>
    <div class="bg-white dark:bg-slate-800 rounded-xl border border-red-200 dark:border-red-800 shadow-sm p-4">
        <p class="text-xs font-semibold text-slate-400 uppercase mb-1">Выдано наличными</p>
        <p class="text-lg font-bold text-red-600">−{{ number_format($totalOut, 0, '.', ' ') }}</p>
    </div>
    <div class="bg-white dark:bg-slate-800 rounded-xl border border-2 {{ $totalDiff == 0 ? 'border-emerald-200' : 'border-red-200 dark:border-red-800' }} shadow-sm p-4">
        <p class="text-xs font-semibold text-slate-400 uppercase mb-1">Общая разница</p>
        <p class="text-lg font-extrabold {{ $totalDiff == 0 ? 'text-emerald-600' : 'text-red-600' }}">
            {{ ($totalDiff >= 0 ? '+' : '') . number_format($totalDiff, 0, '.', ' ') }}
        </p>
    </div>
</div>

{{-- Shifts table --}}
<div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden mb-6">
    <div class="px-5 py-3 border-b border-slate-100 dark:border-slate-700">
        <h2 class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Смены за день</h2>
    </div>
    @if($shifts->isEmpty())
    <p class="px-5 py-10 text-center text-xs text-slate-400">Нет смен за этот день</p>
    @else
    <table class="w-full text-sm">
        <thead>
            <tr class="bg-slate-50 dark:bg-slate-900/50 border-b border-slate-100 dark:border-slate-700">
                <th class="text-left px-5 py-2.5 text-xs font-semibold text-slate-500 uppercase">Кассир</th>
                <th class="text-left px-5 py-2.5 text-xs font-semibold text-slate-500 uppercase">Смена</th>
                <th class="text-left px-5 py-2.5 text-xs font-semibold text-slate-500 uppercase">Время</th>
                <th class="text-right px-5 py-2.5 text-xs font-semibold text-slate-500 uppercase">Начало</th>
                <th class="text-right px-5 py-2.5 text-xs font-semibold text-slate-500 uppercase">Принято</th>
                <th class="text-right px-5 py-2.5 text-xs font-semibold text-slate-500 uppercase">Выдано</th>
                <th class="text-right px-5 py-2.5 text-xs font-semibold text-slate-500 uppercase">Конец</th>
                <th class="text-right px-5 py-2.5 text-xs font-semibold text-slate-500 uppercase">Разница</th>
                <th class="px-5 py-2.5"></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
            @foreach($shifts as $h)
            <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors">
                <td class="px-5 py-2.5">
                    <span class="text-xs font-medium text-slate-700 dark:text-slate-200">{{ $h->user->name }}</span>
                </td>
                <td class="px-5 py-2.5"><span class="text-xs text-slate-500">{{ $h->shift }}</span></td>
                <td class="px-5 py-2.5"><span class="text-[10px] text-slate-400 font-mono">{{ $h->opened_at->format('H:i') }} → {{ $h->closed_at?->format('H:i') }}</span></td>
                <td class="px-5 py-2.5 text-right text-xs font-mono text-slate-500">{{ number_format($h->opening_actual, 0, '.', ' ') }}</td>
                <td class="px-5 py-2.5 text-right text-xs font-mono text-emerald-600">+{{ number_format($h->cash_in, 0, '.', ' ') }}</td>
                <td class="px-5 py-2.5 text-right text-xs font-mono text-red-600">−{{ number_format($h->cash_out, 0, '.', ' ') }}</td>
                <td class="px-5 py-2.5 text-right text-xs font-bold font-mono text-slate-900 dark:text-slate-100">{{ number_format($h->closing_actual, 0, '.', ' ') }}</td>
                <td class="px-5 py-2.5 text-right">
                    @if($h->closing_difference != 0)
                    <span class="text-xs font-bold font-mono {{ $h->closing_difference < 0 ? 'text-red-600' : 'text-emerald-600' }}">
                        {{ ($h->closing_difference >= 0 ? '+' : '') . number_format($h->closing_difference, 0, '.', ' ') }}
                    </span>
                    @else
                    <span class="text-[10px] text-emerald-500">✓</span>
                    @endif
                </td>
                <td class="px-5 py-2.5 text-right">
                    <a href="{{ route('cashier.show', $h) }}"
                       class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-semibold text-blue-700 dark:text-blue-300 bg-blue-50 dark:bg-blue-900/30 rounded-lg hover:bg-blue-100 transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-3 h-3"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/></svg>
                        Обзор
                    </a>
                </td>
            </tr>
            @endforeach
            {{-- Totals row --}}
            <tr class="bg-slate-50 dark:bg-slate-900/50 border-t-2 border-slate-200 dark:border-slate-700">
                <td colspan="3" class="px-5 py-3 text-xs font-bold text-slate-700 dark:text-slate-300">ИТОГО ЗА ДЕНЬ</td>
                <td class="px-5 py-3 text-right text-xs font-bold font-mono text-slate-900 dark:text-slate-100">{{ number_format($totalOpen, 0, '.', ' ') }}</td>
                <td class="px-5 py-3 text-right text-xs font-bold font-mono text-emerald-600">+{{ number_format($totalIn, 0, '.', ' ') }}</td>
                <td class="px-5 py-3 text-right text-xs font-bold font-mono text-red-600">−{{ number_format($totalOut, 0, '.', ' ') }}</td>
                <td class="px-5 py-3 text-right text-xs font-bold font-mono text-slate-900 dark:text-slate-100">{{ number_format($totalClose, 0, '.', ' ') }}</td>
                <td class="px-5 py-3 text-right">
                    <span class="text-xs font-bold font-mono {{ $totalDiff == 0 ? 'text-emerald-600' : 'text-red-600' }}">
                        {{ ($totalDiff >= 0 ? '+' : '') . number_format($totalDiff, 0, '.', ' ') }}
                    </span>
                </td>
                <td></td>
            </tr>
        </tbody>
    </table>
    @endif
</div>

{{-- All operations for the day --}}
@php
    $allOps = $payments->map(fn($p) => [
        'type' => $p->type->value === 'deposit' ? 'deposit' : 'payment',
        'label' => $p->type->value === 'deposit' ? 'Залог' : 'Оплата',
        'label_class' => $p->type->value === 'deposit'
            ? 'bg-amber-50 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400'
            : 'bg-emerald-50 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400',
        'amount' => $p->amount,
        'time' => $p->paid_at,
        'guest' => $p->booking?->guest?->fullName,
        'room' => $p->booking?->room?->number,
        'booking_id' => $p->booking?->id,
        'booking_ref' => $p->booking?->booking_ref,
        'detail' => null,
    ])->merge($cashExpenses->map(fn($e) => [
        'type' => 'expense',
        'label' => $e->category,
        'label_class' => 'bg-red-50 text-red-700 dark:bg-red-900/30 dark:text-red-400',
        'amount' => -abs($e->amount),
        'time' => $e->created_at,
        'guest' => null,
        'room' => null,
        'booking_id' => null,
        'booking_ref' => null,
        'detail' => $e->description,
    ]))->sortByDesc('time')->values();
@endphp

<div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden">
    <div class="px-5 py-3 border-b border-slate-100 dark:border-slate-700">
        <h2 class="text-xs font-semibold text-slate-400 uppercase tracking-wider">
            Все операции за день <span class="text-slate-300">({{ $allOps->count() }})</span>
        </h2>
    </div>
    @if($allOps->isEmpty())
    <p class="px-5 py-10 text-center text-xs text-slate-400">Нет операций</p>
    @else
    <table class="w-full text-sm">
        <thead>
            <tr class="bg-slate-50 dark:bg-slate-900/50 border-b border-slate-100 dark:border-slate-700">
                <th class="text-left px-5 py-2.5 text-xs font-semibold text-slate-500 uppercase">Время</th>
                <th class="text-left px-5 py-2.5 text-xs font-semibold text-slate-500 uppercase">Тип</th>
                <th class="text-left px-5 py-2.5 text-xs font-semibold text-slate-500 uppercase">Описание</th>
                <th class="text-left px-5 py-2.5 text-xs font-semibold text-slate-500 uppercase">Гость / Номер</th>
                <th class="text-right px-5 py-2.5 text-xs font-semibold text-slate-500 uppercase">Сумма</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
            @foreach($allOps as $op)
            <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/30 transition-colors {{ $op['amount'] < 0 ? 'bg-red-50/30 dark:bg-red-900/5' : '' }}">
                <td class="px-5 py-2.5 text-xs text-slate-400 font-mono">{{ $op['time']->format('H:i:s') }}</td>
                <td class="px-5 py-2.5">
                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-semibold {{ $op['label_class'] }}">{{ $op['label'] }}</span>
                </td>
                <td class="px-5 py-2.5">
                    @if($op['booking_id'])
                    <a href="{{ route('bookings.show', $op['booking_id']) }}" class="text-blue-600 hover:text-blue-700 text-xs font-medium">
                        {{ $op['booking_ref'] ?? '#' . $op['booking_id'] }}
                    </a>
                    @endif
                    @if($op['detail'])
                    <span class="text-xs text-slate-500 ml-1">{{ $op['detail'] }}</span>
                    @endif
                </td>
                <td class="px-5 py-2.5">
                    @if($op['guest'])
                    <span class="text-xs text-slate-600 dark:text-slate-300">{{ $op['guest'] }}</span>
                    @endif
                    @if($op['room'])
                    <span class="text-xs text-slate-400 ml-1 font-mono">№{{ $op['room'] }}</span>
                    @endif
                </td>
                <td class="px-5 py-2.5 text-right font-bold {{ $op['amount'] >= 0 ? 'text-emerald-600' : 'text-red-600' }}">
                    {{ ($op['amount'] >= 0 ? '+' : '−') . number_format(abs($op['amount']), 0, '.', ' ') }}
                </td>
            </tr>
            @endforeach
            <tr class="bg-slate-50 dark:bg-slate-900/50 border-t-2 border-slate-200 dark:border-slate-700">
                <td colspan="4" class="px-5 py-3 text-xs font-bold text-slate-700 dark:text-slate-300">ИТОГО ЗА ДЕНЬ</td>
                <td class="px-5 py-3 text-right">
                    <span class="text-base font-extrabold {{ $allOps->sum('amount') >= 0 ? 'text-emerald-600' : 'text-red-600' }}">
                        {{ ($allOps->sum('amount') >= 0 ? '+' : '−') . number_format(abs($allOps->sum('amount')), 0, '.', ' ') }} сум
                    </span>
                </td>
            </tr>
        </tbody>
    </table>
    @endif
</div>
@endsection
