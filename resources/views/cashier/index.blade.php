@extends('layouts.app')

@section('title', 'Кассовая смена')

@section('content')
<div x-data="{ closeOpen: false }">
<div class="mb-6 flex items-center justify-between">
    <div>
        <h1 class="text-2xl font-bold text-slate-900 dark:text-slate-100">Кассовая смена</h1>
        <p class="text-slate-500 dark:text-slate-400 mt-1 text-sm">
            {{ $activeShift->user->name }} · Открыта {{ $activeShift->opened_at->format('d.m.Y H:i') }}
            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400 ml-2">
                {{ $activeShift->shift }}
            </span>
        </p>
    </div>
    <button type="button" @click="closeOpen = true"
            class="inline-flex items-center gap-2 px-4 py-2 bg-red-600 text-white text-sm font-semibold rounded-lg hover:bg-red-700 transition-colors">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M5.636 5.636a9 9 0 1 0 12.728 0M12 3v9"/></svg>
        Закрыть смену
    </button>
</div>

{{-- Cash summary cards --}}
<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
    <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm p-4">
        <p class="text-xs font-semibold text-slate-400 uppercase mb-1">Было на начало</p>
        <p class="text-lg font-bold text-slate-900 dark:text-slate-100">{{ number_format($activeShift->opening_actual, 0, '.', ' ') }}</p>
        @if($activeShift->opening_difference != 0)
        <p class="text-xs {{ $activeShift->opening_difference < 0 ? 'text-red-500' : 'text-emerald-500' }}">
            Разница: {{ ($activeShift->opening_difference >= 0 ? '+' : '') . number_format($activeShift->opening_difference, 0, '.', ' ') }}
        </p>
        @endif
    </div>
    <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm p-4">
        <p class="text-xs font-semibold text-slate-400 uppercase mb-1">Принято наличными</p>
        <p class="text-lg font-bold text-emerald-600">+{{ number_format($activeShift->cash_in, 0, '.', ' ') }}</p>
    </div>
    <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm p-4">
        <p class="text-xs font-semibold text-slate-400 uppercase mb-1">Выдано наличными</p>
        <p class="text-lg font-bold text-red-600">−{{ number_format($activeShift->cash_out, 0, '.', ' ') }}</p>
    </div>
    <div class="bg-white dark:bg-slate-800 rounded-xl border border-2 {{ $activeShift->closing_expected >= $activeShift->opening_actual ? 'border-emerald-200' : 'border-slate-200 dark:border-slate-700' }} shadow-sm p-4">
        <p class="text-xs font-semibold text-slate-400 uppercase mb-1">Должно быть</p>
        <p class="text-lg font-extrabold text-slate-900 dark:text-slate-100">{{ number_format($activeShift->closing_expected, 0, '.', ' ') }}</p>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    {{-- LEFT: Payments log --}}
    <div class="lg:col-span-2">
        <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-100 dark:border-slate-700 flex items-center justify-between">
                <h2 class="text-sm font-semibold text-slate-700 dark:text-slate-200">
                    Движение наличных <span class="text-slate-400 font-normal">({{ $payments->count() + $cashExpenses->count() }})</span>
                </h2>
            </div>
            @if($payments->isEmpty() && $cashExpenses->isEmpty())
            <p class="px-5 py-10 text-center text-sm text-slate-400">Нет операций за смену</p>
            @else
            {{-- Merge and sort by time --}}
            @php
                $allOps = $payments->map(fn($p) => [
                    'type' => 'payment',
                    'label' => $p->type->value === 'deposit' ? 'Залог' : 'Оплата',
                    'label_class' => $p->type->value === 'deposit'
                        ? 'bg-amber-50 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400'
                        : 'bg-emerald-50 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400',
                    'amount' => $p->amount,
                    'time' => $p->paid_at,
                    'guest' => $p->booking?->guest?->fullName ?? '—',
                    'room' => $p->booking?->room?->number,
                    'booking_id' => $p->booking?->id,
                    'booking_ref' => $p->booking?->booking_ref,
                    'expense_id' => null,
                ])->merge($cashExpenses->map(fn($e) => [
                    'type' => 'expense',
                    'label' => $e->category,
                    'label_class' => 'bg-red-50 text-red-700 dark:bg-red-900/30 dark:text-red-400',
                    'amount' => -abs($e->amount),
                    'time' => $e->created_at,
                    'guest' => '—',
                    'room' => null,
                    'booking_id' => null,
                    'booking_ref' => null,
                    'expense_id' => $e->id,
                ]))->sortByDesc('time')->values();
            @endphp
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-slate-50 dark:bg-slate-900/50 border-b border-slate-100 dark:border-slate-700">
                        <th class="text-left px-5 py-3 text-xs font-semibold text-slate-500 uppercase">Операция</th>
                        <th class="text-left px-5 py-3 text-xs font-semibold text-slate-500 uppercase">Описание</th>
                        <th class="text-right px-5 py-3 text-xs font-semibold text-slate-500 uppercase">Сумма</th>
                        <th class="text-left px-5 py-3 text-xs font-semibold text-slate-500 uppercase">Время</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                    @foreach($allOps as $op)
                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors">
                        <td class="px-5 py-3">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-semibold {{ $op['label_class'] }}">{{ $op['label'] }}</span>
                        </td>
                        <td class="px-5 py-3">
                            @if($op['type'] === 'payment' && $op['booking_id'])
                            <a href="{{ route('bookings.show', $op['booking_id']) }}" class="text-blue-600 hover:text-blue-700 text-xs font-medium">
                                {{ $op['booking_ref'] }}
                            </a>
                            <span class="text-xs text-slate-400 ml-1">{{ $op['guest'] }}</span>
                            @if($op['room'])
                            <a href="{{ route('rooms.edit', $op['booking_id'] ? null : null) }}" class="text-xs text-slate-400 ml-0.5 font-mono">№{{ $op['room'] }}</a>
                            @endif
                            @elseif($op['type'] === 'expense')
                            <span class="text-xs text-slate-500">{{ $op['label'] }}</span>
                            @else
                            <span class="text-xs text-slate-400">—</span>
                            @endif
                        </td>
                        <td class="px-5 py-3 text-right font-bold {{ ($op['amount'] >= 0) ? 'text-emerald-600' : 'text-red-600' }}">
                            {{ ($op['amount'] >= 0 ? '+' : '−') . number_format(abs($op['amount']), 0, '.', ' ') }}
                        </td>
                        <td class="px-5 py-3 text-xs text-slate-400 font-mono">{{ $op['time']->format('H:i') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @endif
        </div>
    </div>

    {{-- RIGHT: Shift history --}}
    <div class="space-y-4">
        <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden">
            <div class="px-5 py-3 border-b border-slate-100 dark:border-slate-700">
                <h2 class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Прошлые смены</h2>
            </div>
            @if($history->isEmpty())
            <p class="px-5 py-6 text-center text-xs text-slate-400">Нет закрытых смен</p>
            @else
            <div class="divide-y divide-slate-100 dark:divide-slate-700">
                @foreach($history as $h)
                <a href="{{ route('cashier.show', $h) }}" class="block px-5 py-3 hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors">
                    <div class="flex items-center justify-between mb-1">
                        <span class="text-xs font-semibold text-slate-700 dark:text-slate-200">{{ $h->user->name }}</span>
                        <span class="text-[10px] text-slate-400 font-mono">{{ $h->opened_at->format('d.m') }}</span>
                    </div>
                    <div class="flex items-center justify-between text-xs">
                        <span class="text-slate-400">{{ $h->shift }}</span>
                        <span class="font-bold text-slate-900 dark:text-slate-100">{{ number_format($h->closing_actual, 0, '.', ' ') }}</span>
                    </div>
                    @if($h->closing_difference != 0)
                    <p class="text-[10px] {{ $h->closing_difference < 0 ? 'text-red-500' : 'text-emerald-500' }} mt-0.5">
                        {{ ($h->closing_difference >= 0 ? '+' : '') . number_format($h->closing_difference, 0, '.', ' ') }}
                    </p>
                    @endif
                </a>
                @endforeach
            </div>
            @endif
        </div>

        {{-- Open shift info --}}
        <div class="bg-blue-50 dark:bg-blue-900/20 rounded-xl border border-blue-200 dark:border-blue-800 p-4">
            <p class="text-xs font-semibold text-blue-800 dark:text-blue-300 mb-1">Информация</p>
            <p class="text-xs text-blue-600 dark:text-blue-400">
                Пересчитайте наличные перед закрытием и введите фактическую сумму. Система покажет разницу.
            </p>
        </div>
    </div>
</div>

{{-- Close shift modal --}}
<div x-show="closeOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40" @click.self="closeOpen = false">
    <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-xl w-full max-w-md p-6" @click.outside="closeOpen = false">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-base font-bold text-slate-900 dark:text-slate-100">Закрытие смены</h3>
            <button @click="closeOpen = false" class="text-slate-400 hover:text-slate-600">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <div class="bg-slate-50 dark:bg-slate-700 rounded-lg p-3 mb-4 text-sm space-y-1">
            <div class="flex justify-between">
                <span class="text-slate-500">Было на начало</span>
                <span class="font-medium">{{ number_format($activeShift->opening_actual, 0, '.', ' ') }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-slate-500">+ Принято</span>
                <span class="font-medium text-emerald-600">{{ number_format($activeShift->cash_in, 0, '.', ' ') }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-slate-500">− Выдано</span>
                <span class="font-medium text-red-600">{{ number_format($activeShift->cash_out, 0, '.', ' ') }}</span>
            </div>
            <div class="flex justify-between border-t border-slate-200 dark:border-slate-600 pt-1 font-bold">
                <span>Должно быть</span>
                <span>{{ number_format($activeShift->closing_expected, 0, '.', ' ') }} сум</span>
            </div>
        </div>

        <form method="POST" action="{{ route('cashier.close') }}">
            @csrf
            <div class="mb-4">
                <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide mb-1.5">
                    Фактически в кассе (пересчитайте)
                </label>
                <div class="relative">
                    <input type="number" name="closing_actual" :value="{{ number_format($activeShift->closing_expected, 0, '.', '') }}" required min="0" step="1"
                           class="w-full px-4 py-3 text-lg font-bold border border-slate-200 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-blue-500 focus:outline-none text-right">
                    <span class="absolute left-4 top-1/2 -translate-y-1/2 text-sm text-slate-400">сум</span>
                </div>
                @error('closing_actual')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
            </div>
            <div class="mb-4">
                <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide mb-1.5">Заметка <span class="normal-case font-normal">(необязательно)</span></label>
                <textarea name="notes_close" rows="2" maxlength="500"
                          class="w-full px-3 py-2 border border-slate-200 dark:border-slate-600 rounded-lg text-sm bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100 resize-none focus:ring-2 focus:ring-blue-500 focus:outline-none"
                          placeholder="Примечания к закрытию..."></textarea>
            </div>
            <div class="flex gap-3">
                <button type="submit"
                        class="flex-1 px-4 py-2.5 bg-red-600 text-white text-sm font-bold rounded-lg hover:bg-red-700 transition-colors flex items-center justify-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M5.636 5.636a9 9 0 1 0 12.728 0M12 3v9"/></svg>
                    Закрыть смену
                </button>
                <button type="button" @click="closeOpen = false"
                        class="px-4 py-2.5 text-sm font-semibold text-slate-600 dark:text-slate-300 bg-slate-100 dark:bg-slate-700 rounded-lg hover:bg-slate-200 dark:hover:bg-slate-600 transition-colors">
                    Отмена
                </button>
            </div>
        </form>
    </div>
</div>
</div> {{-- end x-data closeOpen --}}
@endsection
