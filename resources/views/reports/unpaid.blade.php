@extends('layouts.app')
@section('title', 'Неоплаченные брони')
@section('content')

@include('reports._header', ['title' => 'Неоплаченные бронирования'])

@php
$totalDebt  = 0;
$totalBilled = 0;
$totalPaid  = 0;
foreach ($bookings as $b) {
    $paid        = (float) $b->payments->where('type', \App\Enums\PaymentType::Prepayment->value)->sum('amount');
    $totalPaid  += $paid;
    $totalBilled += (float) $b->total_price;
    $totalDebt  += max(0, (float) $b->total_price - $paid);
}
$paidPct = $totalBilled > 0 ? round($totalPaid / $totalBilled * 100) : 0;
@endphp

{{-- Controls --}}
<div class="flex flex-wrap items-center gap-3 mb-6">
    <div class="ml-auto">
        <a href="{{ route('reports.unpaid', ['export' => 'pdf']) }}"
           class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-semibold bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">
            PDF
        </a>
    </div>
</div>

{{-- KPI strip --}}
<div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-5">

    {{-- Unpaid count --}}
    <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm p-5 relative overflow-hidden">
        <div class="absolute -top-6 -right-6 w-24 h-24 rounded-full bg-red-50 dark:bg-red-900/20 pointer-events-none"></div>
        <div class="relative">
            <div class="w-10 h-10 rounded-xl bg-red-50 dark:bg-red-900/30 border border-red-100 dark:border-red-800/50 flex items-center justify-center mb-3">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-red-600 dark:text-red-400">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z"/>
                </svg>
            </div>
            <p class="text-xs font-semibold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-1">Задолженностей</p>
            <p class="text-4xl font-black text-slate-900 dark:text-white tabular-nums">{{ $bookings->count() }}</p>
        </div>
    </div>

    {{-- Total debt --}}
    <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm p-5 relative overflow-hidden">
        <div class="absolute -top-6 -right-6 w-24 h-24 rounded-full bg-red-50 dark:bg-red-900/20 pointer-events-none"></div>
        <div class="relative">
            <div class="w-10 h-10 rounded-xl bg-red-50 dark:bg-red-900/30 border border-red-100 dark:border-red-800/50 flex items-center justify-center mb-3">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-red-600 dark:text-red-400">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm3 0h.008v.008H18V10.5Zm-12 0h.008v.008H6V10.5Z"/>
                </svg>
            </div>
            <p class="text-xs font-semibold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-1">Общий долг</p>
            <p class="text-3xl font-black text-red-600 dark:text-red-400 tabular-nums">{{ number_format($totalDebt / 1000000, 2) }}<span class="text-base text-slate-400 dark:text-slate-500 font-semibold ml-1">млн</span></p>
        </div>
    </div>

    {{-- Paid percentage --}}
    <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm p-5 relative overflow-hidden">
        <div class="absolute -top-6 -right-6 w-24 h-24 rounded-full bg-emerald-50 dark:bg-emerald-900/20 pointer-events-none"></div>
        <div class="relative">
            <div class="w-10 h-10 rounded-xl bg-emerald-50 dark:bg-emerald-900/30 border border-emerald-100 dark:border-emerald-800/50 flex items-center justify-center mb-3">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-emerald-600 dark:text-emerald-400">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
                </svg>
            </div>
            <p class="text-xs font-semibold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-1">Оплачено</p>
            <p class="text-3xl font-black text-slate-900 dark:text-white tabular-nums">{{ $paidPct }}<span class="text-base text-slate-400 font-semibold ml-0.5">%</span></p>
            <div class="mt-2 bg-slate-100 dark:bg-slate-700 rounded-full h-1.5 overflow-hidden">
                <div class="bg-emerald-500 h-1.5 rounded-full transition-all duration-700" style="width:{{ $paidPct }}%"></div>
            </div>
        </div>
    </div>
</div>

{{-- Table --}}
<div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden"
     x-data="tablePager({{ $bookings->count() }}, 25)">
    <div class="px-5 py-4 border-b border-slate-100 dark:border-slate-700/50 flex items-center justify-between">
        <p class="text-sm font-semibold text-slate-700 dark:text-slate-300">{{ $bookings->count() }} бронирований с задолженностью</p>
        @if($totalDebt > 0)
        <p class="text-sm font-bold text-red-600 dark:text-red-400 tabular-nums">{{ number_format($totalDebt, 0, '.', ' ') }} сум</p>
        @endif
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-slate-50 dark:bg-slate-800/80 border-b border-slate-100 dark:border-slate-700/50">
                    <th class="text-left px-5 py-3 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">#</th>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Гость</th>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Номер</th>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Заезд — Выезд</th>
                    <th class="text-right px-5 py-3 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Сумма</th>
                    <th class="text-right px-5 py-3 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Оплачено</th>
                    <th class="text-right px-5 py-3 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Долг</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-700/50">
                @forelse($bookings as $booking)
                @php
                    $paid = (float) $booking->payments->where('type', \App\Enums\PaymentType::Prepayment->value)->sum('amount');
                    $debt = max(0, (float) $booking->total_price - $paid);
                    $debtPct = $booking->total_price > 0 ? round($debt / $booking->total_price * 100) : 0;
                @endphp
                <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/30 transition-colors" x-show="show({{ $loop->index }})">
                    <td class="px-5 py-3.5">
                        <a href="{{ route('bookings.show', $booking) }}" class="text-blue-600 dark:text-blue-400 hover:underline font-mono text-xs font-bold">#{{ $booking->id }}</a>
                    </td>
                    <td class="px-5 py-3.5 font-medium text-slate-900 dark:text-white">{{ $booking->guest->fullName }}</td>
                    <td class="px-5 py-3.5">
                        <span class="text-xs font-bold px-2 py-1 rounded-lg bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300">{{ $booking->room->number }}</span>
                    </td>
                    <td class="px-5 py-3.5 text-slate-500 dark:text-slate-400 font-mono text-xs">{{ $booking->check_in_date->format('d.m') }} — {{ $booking->check_out_date->format('d.m.Y') }}</td>
                    <td class="px-5 py-3.5 text-right text-slate-600 dark:text-slate-300 tabular-nums">{{ number_format($booking->total_price, 0, '.', ' ') }}</td>
                    <td class="px-5 py-3.5 text-right tabular-nums">
                        <span class="text-emerald-600 dark:text-emerald-400 font-semibold">{{ number_format($paid, 0, '.', ' ') }}</span>
                    </td>
                    <td class="px-5 py-3.5 text-right">
                        <div class="flex flex-col items-end gap-1">
                            <span class="font-bold text-red-600 dark:text-red-400 tabular-nums">{{ number_format($debt, 0, '.', ' ') }}</span>
                            @if($debtPct > 0)
                            <span class="text-[10px] font-semibold text-red-400 dark:text-red-500">{{ $debtPct }}% долга</span>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-5 py-16 text-center">
                        <div class="flex flex-col items-center gap-3 text-slate-400 dark:text-slate-500">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-10 h-10 opacity-40">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
                            </svg>
                            <span class="text-sm font-medium">Все бронирования оплачены</span>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @include('reports._pagination')
</div>

@endsection
