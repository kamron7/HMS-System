@extends('layouts.app')

@section('title', 'Долги гостей')

@section('content')

<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-xl font-bold text-slate-900">Долги гостей</h1>
        <p class="text-sm text-slate-500 mt-0.5">Бронирования с непогашенной задолженностью</p>
    </div>
    <a href="{{ route('reports.index') }}"
       class="inline-flex items-center gap-1.5 text-sm text-slate-500 hover:text-slate-700 transition-colors">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18"/></svg>
        Отчёты
    </a>
</div>

@if($debtors->isEmpty())
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm px-6 py-16 text-center">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-10 h-10 mx-auto text-emerald-400 mb-3"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
        <p class="text-base font-semibold text-slate-700">Нет задолженностей</p>
        <p class="text-sm text-slate-400 mt-1">Все гости рассчитались</p>
    </div>
@else

{{-- Summary --}}
@php
    $totalDebt = $debtors->sum('balance_due');
@endphp
<div class="mb-5 flex items-center gap-4">
    <div class="bg-red-50 border border-red-200 rounded-xl px-5 py-3 flex items-center gap-3">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-red-500 flex-shrink-0"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z"/></svg>
        <div>
            <p class="text-xs font-semibold text-red-700 uppercase tracking-wide">Общая задолженность</p>
            <p class="text-lg font-bold text-red-700">{{ number_format($totalDebt, 0, '.', ' ') }} сум</p>
        </div>
    </div>
    <div class="text-sm text-slate-500">
        {{ $debtors->count() }} {{ $debtors->count() === 1 ? 'бронирование' : ($debtors->count() < 5 ? 'бронирования' : 'бронирований') }}
    </div>
</div>

<div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
    <table class="w-full text-sm">
        <thead>
            <tr class="bg-slate-50 border-b border-slate-200">
                <th class="text-left px-5 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Гость</th>
                <th class="text-left px-5 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Номер</th>
                <th class="text-left px-5 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Выезд</th>
                <th class="text-right px-5 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Итого</th>
                <th class="text-right px-5 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Оплачено</th>
                <th class="text-right px-5 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">К оплате</th>
                <th class="text-center px-5 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Просрочка</th>
                <th class="px-5 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Действия</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            @foreach($debtors as $item)
            @php
                $booking = $item->booking;
                $isUrgent = $item->days_overdue > 7;
            @endphp
            <tr class="hover:bg-slate-50 transition-colors {{ $isUrgent ? 'bg-amber-50' : '' }}">
                <td class="px-5 py-3.5">
                    <a href="{{ route('guests.show', $booking->guest) }}"
                       class="font-semibold text-blue-600 hover:text-blue-700">
                        {{ $booking->guest->fullName }}
                    </a>
                    @if($booking->guest->phone)
                        <p class="text-xs text-slate-400">{{ $booking->guest->phone }}</p>
                    @endif
                </td>
                <td class="px-5 py-3.5 text-slate-700">
                    <span class="font-semibold">{{ $booking->room->number }}</span>
                    <span class="text-xs text-slate-400 ml-1">{{ $booking->room->roomType->name }}</span>
                </td>
                <td class="px-5 py-3.5 font-mono text-xs text-slate-600">
                    {{ $booking->check_out_date->format('d.m.Y') }}
                </td>
                <td class="px-5 py-3.5 text-right text-slate-700 font-semibold">
                    {{ number_format($item->grand_total, 0, '.', ' ') }} сум
                </td>
                <td class="px-5 py-3.5 text-right text-emerald-700 font-semibold">
                    {{ number_format($item->paid, 0, '.', ' ') }} сум
                </td>
                <td class="px-5 py-3.5 text-right">
                    <span class="font-bold text-red-600">{{ number_format($item->balance_due, 0, '.', ' ') }} сум</span>
                </td>
                <td class="px-5 py-3.5 text-center">
                    @if($item->days_overdue > 0)
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold {{ $item->days_overdue > 7 ? 'bg-red-100 text-red-700' : 'bg-amber-100 text-amber-700' }}">
                            {{ $item->days_overdue }} дн.
                        </span>
                    @else
                        <span class="text-slate-400 text-xs">—</span>
                    @endif
                </td>
                <td class="px-5 py-3.5">
                    <div class="flex items-center gap-2 flex-nowrap">
                        <a href="{{ route('bookings.show', $booking) }}#payment"
                           class="inline-flex items-center gap-1 px-2.5 py-1.5 text-xs font-semibold bg-emerald-50 text-emerald-700 rounded-lg hover:bg-emerald-100 transition-colors whitespace-nowrap">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-3 h-3"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                            Платёж
                        </a>
                        <a href="{{ route('bookings.invoice', $booking) }}"
                           class="inline-flex items-center gap-1 px-2.5 py-1.5 text-xs font-semibold bg-slate-100 text-slate-700 rounded-lg hover:bg-slate-200 transition-colors whitespace-nowrap">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-3 h-3"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m.75 12 3 3m0 0 3-3m-3 3v-6m-1.5-9H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z"/></svg>
                            Счёт
                        </a>
                    </div>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endif

@endsection
