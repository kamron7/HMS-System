@extends('layouts.app')

@section('title', 'Касса')

@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-bold text-slate-900 dark:text-slate-100">Кассовая смена</h1>
    <p class="text-slate-500 dark:text-slate-400 mt-1 text-sm">{{ auth()->user()->name }} · {{ now()->format('d.m.Y H:i') }}</p>
</div>

{{-- Today's summary cards --}}
@if($todayShiftes->isNotEmpty())
<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
    <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm p-4">
        <p class="text-xs font-semibold text-slate-400 uppercase mb-1">Смен закрыто</p>
        <p class="text-2xl font-bold text-slate-900 dark:text-slate-100">{{ $todayShiftes->count() }}</p>
    </div>
    <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm p-4">
        <p class="text-xs font-semibold text-slate-400 uppercase mb-1">Принято</p>
        <p class="text-lg font-bold text-emerald-600">+{{ number_format(max($todayTotal, 0), 0, '.', ' ') }}</p>
    </div>
    <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm p-4">
        <p class="text-xs font-semibold text-slate-400 uppercase mb-1">Выдано</p>
        <p class="text-lg font-bold text-red-600">−{{ number_format(abs(min($todayTotal, 0)), 0, '.', ' ') }}</p>
    </div>
    <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm p-4">
        <p class="text-xs font-semibold text-slate-400 uppercase mb-1">Итого за день</p>
        <p class="text-lg font-bold {{ $todayTotal >= 0 ? 'text-emerald-600' : 'text-red-600' }}">
            {{ ($todayTotal >= 0 ? '+' : '−') . number_format(abs($todayTotal), 0, '.', ' ') }}
        </p>
        <a href="{{ route('cashier.daily', ['date' => now()->toDateString()]) }}" class="block mt-1 text-[10px] text-blue-600 hover:underline">Полный отчёт →</a>
    </div>
</div>
@endif

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    {{-- LEFT: Open shift form --}}
    <div class="lg:col-span-1">
        <form method="POST" action="{{ route('cashier.open') }}" class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm p-6 space-y-5">
            @csrf
            <div class="flex items-center gap-3 mb-1">
                <div class="w-10 h-10 rounded-xl bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center flex-shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-blue-600 dark:text-blue-400"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 10.5V6.75a4.5 4.5 0 1 1 9 0v3.75M3.75 21.75h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H3.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z"/></svg>
                </div>
                <div>
                    <h2 class="text-base font-bold text-slate-900 dark:text-slate-100">Открыть смену</h2>
                    <p class="text-xs text-slate-400">Начните новую кассовую смену</p>
                </div>
            </div>

            {{-- Shift type --}}
            <div x-data="{
                    custom: {{ old('shift') !== 'morning' && old('shift') !== 'evening' && old('shift') !== 'night' ? 'true' : 'false' }},
                    startH: 8, startM: 0, endH: 22, endM: 0,
                    get label() {
                        const pad = n => String(n).padStart(2, '0');
                        return pad(this.startH) + ':' + pad(this.startM) + ' — ' + pad(this.endH) + ':' + pad(this.endM);
                    }
                }"
                @submit="$refs.shiftInput.value = label">
                <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-2">Тип смены</label>
                <div class="grid grid-cols-4 gap-1.5">
                    <label class="cursor-pointer">
                        <input type="radio" name="shift" value="morning" x-on:change="custom = false" class="sr-only peer" {{ old('shift', $defaultShift) === 'morning' ? 'checked' : '' }}>
                        <div class="text-center px-1.5 py-2.5 border border-slate-200 dark:border-slate-600 rounded-xl peer-checked:border-blue-500 peer-checked:bg-blue-50 dark:peer-checked:bg-blue-900/30 hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">
                            <span class="text-xs font-medium text-slate-700 dark:text-slate-300 peer-checked:text-blue-700 dark:peer-checked:text-blue-400">Утро</span>
                        </div>
                    </label>
                    <label class="cursor-pointer">
                        <input type="radio" name="shift" value="evening" x-on:change="custom = false" class="sr-only peer" {{ old('shift', $defaultShift) === 'evening' ? 'checked' : '' }}>
                        <div class="text-center px-1.5 py-2.5 border border-slate-200 dark:border-slate-600 rounded-xl peer-checked:border-blue-500 peer-checked:bg-blue-50 dark:peer-checked:bg-blue-900/30 hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">
                            <span class="text-xs font-medium text-slate-700 dark:text-slate-300 peer-checked:text-blue-700 dark:peer-checked:text-blue-400">Вечер</span>
                        </div>
                    </label>
                    <label class="cursor-pointer">
                        <input type="radio" name="shift" value="night" x-on:change="custom = false" class="sr-only peer" {{ old('shift', $defaultShift) === 'night' ? 'checked' : '' }}>
                        <div class="text-center px-1.5 py-2.5 border border-slate-200 dark:border-slate-600 rounded-xl peer-checked:border-blue-500 peer-checked:bg-blue-50 dark:peer-checked:bg-blue-900/30 hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">
                            <span class="text-xs font-medium text-slate-700 dark:text-slate-300 peer-checked:text-blue-700 dark:peer-checked:text-blue-400">Ночь</span>
                        </div>
                    </label>
                    <label class="cursor-pointer">
                        <input type="radio" name="shift" value="custom" x-on:change="custom = true" class="sr-only peer" :checked="custom">
                        <div class="text-center px-1.5 py-2.5 border border-slate-200 dark:border-slate-600 rounded-xl peer-checked:border-blue-500 peer-checked:bg-blue-50 dark:peer-checked:bg-blue-900/30 hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">
                            <span class="text-xs font-medium text-slate-700 dark:text-slate-300 peer-checked:text-blue-700 dark:peer-checked:text-blue-400">Своё</span>
                        </div>
                    </label>
                </div>
                <div x-show="custom" x-cloak class="mt-3">
                    <input type="text" name="shift_custom" value="{{ old('shift') !== 'morning' && old('shift') !== 'evening' && old('shift') !== 'night' ? old('shift', '') : '' }}"
                           placeholder="Напр: 08:00 — 22:00"
                           class="w-full px-3 py-2 border border-slate-200 dark:border-slate-600 rounded-xl text-sm bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-blue-500 focus:outline-none">
                </div>
            </div>

            {{-- Initial cash --}}
            <div>
                <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1.5">Сумма в кассе при открытии</label>
                <div class="relative">
                    <input type="number" name="opening_actual" value="{{ old('opening_actual', 0) }}" required min="0" step="1"
                           class="w-full px-12 py-3.5 text-2xl font-bold border border-slate-200 dark:border-slate-600 rounded-xl bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-blue-500 focus:outline-none text-right tabular-nums">
                    <span class="absolute left-4 top-1/2 -translate-y-1/2 text-sm font-medium text-slate-400">сум</span>
                </div>
                <p class="text-xs text-slate-400 mt-1.5">Пересчитайте наличные и введите точную сумму</p>
                @error('opening_actual')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
            </div>

            {{-- Notes --}}
            <div>
                <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1.5">Заметка <span class="font-normal text-slate-400">(необязательно)</span></label>
                <textarea name="notes_open" rows="2" maxlength="500"
                          class="w-full px-3 py-2.5 border border-slate-200 dark:border-slate-600 rounded-xl text-sm bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100 resize-none focus:ring-2 focus:ring-blue-500 focus:outline-none"
                          placeholder="Оставьте заметку для следующей смены...">{{ old('notes_open') }}</textarea>
            </div>

            <button type="submit"
                    class="w-full px-4 py-3 bg-blue-600 text-white text-sm font-bold rounded-xl hover:bg-blue-700 active:scale-[0.98] transition-all flex items-center justify-center gap-2 shadow-sm shadow-blue-500/25">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 10.5V6.75a4.5 4.5 0 1 1 9 0v3.75M3.75 21.75h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H3.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z"/></svg>
                Открыть смену
            </button>
        </form>
    </div>

    {{-- RIGHT: History table --}}
    <div class="lg:col-span-2">
        <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-700 flex items-center justify-between">
                <div>
                    <h2 class="text-sm font-bold text-slate-900 dark:text-slate-100">История смен</h2>
                    <p class="text-xs text-slate-400 mt-0.5">Последние закрытые смены</p>
                </div>
                <a href="{{ route('cashier.daily', ['date' => now()->toDateString()]) }}" class="inline-flex items-center gap-1.5 text-xs text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300 font-semibold bg-blue-50 dark:bg-blue-900/20 px-3 py-1.5 rounded-lg hover:bg-blue-100 dark:hover:bg-blue-900/40 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-3.5 h-3.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 12h16.5m-16.5 3.75h16.5M3.75 19.5h16.5M5.625 4.5h12.75a1.875 1.875 0 0 1 0 3.75H5.625a1.875 1.875 0 0 1 0-3.75Z"/></svg>
                    Отчёт за сегодня
                </a>
            </div>

            @php
                $recentShifts = \App\Models\CashierShift::with('user')
                    ->where('status', \App\Enums\CashierShiftStatus::Closed->value)
                    ->orderByDesc('opened_at')
                    ->limit(30)
                    ->get();
            @endphp

            @if($recentShifts->isEmpty())
            <div class="px-6 py-16 text-center">
                <div class="w-12 h-12 rounded-full bg-slate-100 dark:bg-slate-700 flex items-center justify-center mx-auto mb-3">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6 text-slate-400"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                </div>
                <p class="text-sm text-slate-400 dark:text-slate-500">Закрытых смен пока нет</p>
                <p class="text-xs text-slate-300 dark:text-slate-600 mt-1">Откройте первую смену</p>
            </div>
            @else
            <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-slate-50 dark:bg-slate-900/50 border-b border-slate-100 dark:border-slate-700">
                        <th class="text-left px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Кассир</th>
                        <th class="text-left px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Смена</th>
                        <th class="text-left px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Период</th>
                        <th class="text-right px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Начало</th>
                        <th class="text-right px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Принято</th>
                        <th class="text-right px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Выдано</th>
                        <th class="text-right px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Конец</th>
                        <th class="text-right px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Разница</th>
                        <th class="px-6 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                    @foreach($recentShifts as $h)
                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/30 transition-colors">
                        <td class="px-6 py-3.5">
                            <div class="flex items-center gap-2.5">
                                <div class="w-7 h-7 rounded-full bg-slate-200 dark:bg-slate-600 flex items-center justify-center flex-shrink-0">
                                    <span class="text-[10px] font-bold text-slate-600 dark:text-slate-200">{{ strtoupper(substr($h->user->name, 0, 1)) }}</span>
                                </div>
                                <span class="text-xs font-medium text-slate-700 dark:text-slate-200">{{ $h->user->name }}</span>
                            </div>
                        </td>
                        <td class="px-6 py-3.5">
                            <span class="text-xs text-slate-500 bg-slate-100 dark:bg-slate-700 px-2 py-0.5 rounded-md">{{ $h->shift }}</span>
                        </td>
                        <td class="px-6 py-3.5">
                            <span class="text-xs text-slate-400 font-mono">{{ $h->opened_at->format('d.m H:i') }} → {{ $h->closed_at?->format('H:i') }}</span>
                        </td>
                        <td class="px-6 py-3.5 text-right text-xs font-mono text-slate-500">{{ number_format($h->opening_actual, 0, '.', ' ') }}</td>
                        <td class="px-6 py-3.5 text-right text-xs font-mono font-semibold text-emerald-600">+{{ number_format($h->cash_in, 0, '.', ' ') }}</td>
                        <td class="px-6 py-3.5 text-right text-xs font-mono font-semibold text-red-600">−{{ number_format($h->cash_out, 0, '.', ' ') }}</td>
                        <td class="px-6 py-3.5 text-right text-xs font-bold font-mono text-slate-900 dark:text-slate-100">{{ number_format($h->closing_actual, 0, '.', ' ') }}</td>
                        <td class="px-6 py-3.5 text-right">
                            @if($h->closing_difference != 0)
                            <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-bold font-mono {{ $h->closing_difference < 0 ? 'bg-red-50 text-red-700 dark:bg-red-900/30 dark:text-red-400' : 'bg-emerald-50 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400' }}">
                                {{ ($h->closing_difference >= 0 ? '+' : '') . number_format($h->closing_difference, 0, '.', ' ') }}
                            </span>
                            @else
                            <span class="inline-flex items-center gap-1 text-xs text-emerald-500">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-3.5 h-3.5"><path fill-rule="evenodd" d="M2.25 12c0-5.385 4.365-9.75 9.75-9.75s9.75 4.365 9.75 9.75-4.365 9.75-9.75 9.75S2.25 17.385 2.25 12Zm13.36-1.814a.75.75 0 1 0-1.22-.872l-3.236 4.53L9.53 12.22a.75.75 0 0 0-1.06 1.06l2.25 2.25a.75.75 0 0 0 1.14-.094l3.75-5.25Z" clip-rule="evenodd"/></svg>
                                0
                            </span>
                            @endif
                        </td>
                        <td class="px-6 py-3.5 text-right">
                            <a href="{{ route('cashier.show', $h) }}"
                               class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-blue-700 dark:text-blue-300 bg-blue-50 dark:bg-blue-900/20 rounded-lg hover:bg-blue-100 dark:hover:bg-blue-900/40 transition-colors">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-3.5 h-3.5"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/></svg>
                                Обзор
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
