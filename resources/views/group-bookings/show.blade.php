@extends('layouts.app')

@section('title', 'Группа ' . $group->group_ref)

@section('content')
{{-- Header --}}
<div class="flex items-center justify-between mb-6">
    <div class="flex items-center gap-3">
        <a href="{{ route('bookings.index') }}"
           class="inline-flex items-center gap-1.5 text-sm text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 transition-colors">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18"/></svg>
            Бронирования
        </a>
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 text-slate-300 dark:text-slate-600"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5"/></svg>
        <h1 class="text-xl font-bold text-slate-900 dark:text-slate-100">
            Группа <span class="font-mono text-slate-400 dark:text-slate-500">{{ $group->group_ref }}</span>
        </h1>
        @if($group->name)
            <span class="text-slate-600 dark:text-slate-300 font-medium">— {{ $group->name }}</span>
        @endif
    </div>
    <div class="flex items-center gap-2">
        <a href="{{ route('group-bookings.invoice', $group) }}"
           class="inline-flex items-center gap-2 px-4 py-2 bg-slate-700 dark:bg-slate-600 text-white text-sm font-semibold rounded-lg hover:bg-slate-800 dark:hover:bg-slate-500 transition-colors">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z"/></svg>
            Счёт PDF
        </a>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

    {{-- Summary card --}}
    <div class="lg:col-span-1 space-y-4">
        <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm p-5">
            <h2 class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-4">Итого по группе</h2>
            <dl class="space-y-2 text-sm">
                <div class="flex justify-between">
                    <dt class="text-slate-500 dark:text-slate-400">Номеров</dt>
                    <dd class="font-semibold text-slate-900 dark:text-slate-100">{{ $group->bookings->count() }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-slate-500 dark:text-slate-400">Сумма</dt>
                    <dd class="font-semibold text-slate-900 dark:text-slate-100">{{ number_format($totalsPerBooking->sum('grand_total'), 0, '.', ' ') }} <span class="text-xs font-normal text-slate-400">сум</span></dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-slate-500 dark:text-slate-400">Оплачено</dt>
                    <dd class="font-semibold text-emerald-700 dark:text-emerald-400">{{ number_format($totalsPerBooking->sum('paid'), 0, '.', ' ') }} <span class="text-xs font-normal text-slate-400">сум</span></dd>
                </div>
                @php $balance = $totalsPerBooking->sum('balance_due') @endphp
                @if($balance > 0)
                <div class="flex justify-between pt-2 border-t border-slate-100 dark:border-slate-700">
                    <dt class="font-semibold text-slate-700 dark:text-slate-200">Долг</dt>
                    <dd class="font-bold text-red-600 dark:text-red-400">{{ number_format($balance, 0, '.', ' ') }} <span class="text-xs font-normal">сум</span></dd>
                </div>
                @endif
            </dl>
        </div>

        {{-- Bulk actions --}}
        @php
            $allConfirmed = $group->bookings->filter(fn($b) => in_array($b->status->value, ['confirmed', 'pending']))->count();
            $allCheckedIn = $group->bookings->filter(fn($b) => $b->status->value === 'checked_in')->count();
        @endphp

        @if($allConfirmed > 0)
        <form method="POST" action="{{ route('group-bookings.check-in-all', $group) }}">
            @csrf
            <button type="submit"
                    class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-lg transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15M12 9l-3 3m0 0 3 3m-3-3h12.75"/></svg>
                Заселить всех ({{ $allConfirmed }})
            </button>
        </form>
        @endif

        @if($allCheckedIn > 0)
        <form method="POST" action="{{ route('group-bookings.check-out-all', $group) }}"
              onsubmit="return confirm('Выселить всех гостей группы?')">
            @csrf
            <button type="submit"
                    class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold rounded-lg transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 9V5.25A2.25 2.25 0 0 1 10.5 3h6a2.25 2.25 0 0 1 2.25 2.25v13.5A2.25 2.25 0 0 1 16.5 21h-6a2.25 2.25 0 0 1-2.25-2.25V15m-3 0-3-3m0 0 3-3m-3 3H15"/></svg>
                Выселить всех ({{ $allCheckedIn }})
            </button>
        </form>
        @endif

        <div class="text-xs text-slate-400 dark:text-slate-500 space-y-0.5 px-1">
            <p>Создано: {{ $group->created_at->format('d.m.Y H:i') }}</p>
            <p>Сотрудник: {{ $group->creator->name }}</p>
        </div>
    </div>

    {{-- Bookings table --}}
    <div class="lg:col-span-2">
        <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-100 dark:border-slate-700">
                <h2 class="text-sm font-semibold text-slate-700 dark:text-slate-200">Номера в группе</h2>
            </div>
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-slate-50 dark:bg-slate-900/50 border-b border-slate-100 dark:border-slate-700">
                        <th class="text-left px-5 py-3 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Номер</th>
                        <th class="text-left px-5 py-3 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Гость</th>
                        <th class="text-left px-5 py-3 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Статус</th>
                        <th class="text-right px-5 py-3 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Долг</th>
                        <th class="text-right px-5 py-3 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                    @foreach($group->bookings as $booking)
                        @php $t = $totalsPerBooking[$booking->id]; @endphp
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors">
                            <td class="px-5 py-3.5 font-bold text-slate-900 dark:text-slate-100">
                                {{ $booking->room?->number ?? '—' }}
                                <span class="text-xs font-normal text-slate-400 ml-1">{{ $booking->room?->roomType?->name }}</span>
                            </td>
                            <td class="px-5 py-3.5 text-slate-700 dark:text-slate-200">{{ $booking->guest?->full_name ?? '—' }}</td>
                            <td class="px-5 py-3.5">
                                <x-status-badge :status="$booking->status" />
                            </td>
                            <td class="px-5 py-3.5 text-right font-semibold {{ $t['balance_due'] > 0 ? 'text-red-600 dark:text-red-400' : 'text-emerald-600 dark:text-emerald-400' }}">
                                {{ $t['balance_due'] > 0 ? number_format($t['balance_due'], 0, '.', ' ') . ' сум' : '—' }}
                            </td>
                            <td class="px-5 py-3.5 text-right">
                                <a href="{{ route('bookings.show', $booking) }}"
                                   class="text-xs font-semibold text-blue-600 dark:text-blue-400 hover:text-blue-700 transition-colors">
                                    Открыть →
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
