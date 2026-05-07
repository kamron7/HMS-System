@extends('layouts.app')

@section('title', 'Финансы')

@section('content')
<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-slate-900 dark:text-slate-100">Финансы</h1>
        <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">{{ $periodLabel }}</p>
    </div>
    <a href="{{ route('expenses.create') }}"
       class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white text-sm font-semibold rounded-lg hover:bg-blue-700 transition-colors shadow-sm">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
        Добавить расход
    </a>
</div>

{{-- Period selector --}}
<div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm p-4 mb-6"
     x-data="{ rangeMode: {{ request()->has('start') && request()->has('end') ? 'true' : 'false' }} }">
    <form method="GET" action="{{ route('finances.index') }}" class="flex flex-wrap items-end gap-4">

        <div class="flex items-center gap-4">
            <label class="flex items-center gap-2 text-sm text-slate-600 dark:text-slate-300 cursor-pointer">
                <input type="radio" name="mode" value="month" @change="rangeMode = false"
                       {{ !request()->has('start') ? 'checked' : '' }}
                       class="accent-blue-600">
                По месяцу
            </label>
            <label class="flex items-center gap-2 text-sm text-slate-600 dark:text-slate-300 cursor-pointer">
                <input type="radio" name="mode" value="range" @change="rangeMode = true"
                       {{ request()->has('start') ? 'checked' : '' }}
                       class="accent-blue-600">
                Произвольный период
            </label>
        </div>

        <div x-show="!rangeMode">
            <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide mb-1.5">Месяц</label>
            <input type="month" name="period" value="{{ $period }}"
                   class="px-3 py-2 border border-slate-200 dark:border-slate-600 rounded-lg text-sm bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
        </div>

        <div x-show="rangeMode" class="flex items-end gap-2">
            <div>
                <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide mb-1.5">Начало</label>
                <input type="date" name="start" value="{{ request()->input('start', $start->toDateString()) }}"
                       class="px-3 py-2 border border-slate-200 dark:border-slate-600 rounded-lg text-sm bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide mb-1.5">Конец</label>
                <input type="date" name="end" value="{{ request()->input('end', $end->toDateString()) }}"
                       class="px-3 py-2 border border-slate-200 dark:border-slate-600 rounded-lg text-sm bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            </div>
        </div>

        <button type="submit"
                class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white text-sm font-semibold rounded-lg hover:bg-blue-700 transition-colors">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3c2.755 0 5.455.232 8.083.678.533.09.917.556.917 1.096v1.044a2.25 2.25 0 0 1-.659 1.591l-5.432 5.432a2.25 2.25 0 0 0-.659 1.591v2.927a2.25 2.25 0 0 1-1.244 2.013L9.75 21v-6.568a2.25 2.25 0 0 0-.659-1.591L3.659 7.409A2.25 2.25 0 0 1 3 5.818V4.774c0-.54.384-1.006.917-1.096A48.32 48.32 0 0 1 12 3Z"/></svg>
            Применить
        </button>

        <a href="{{ route('finances.index') }}"
           class="px-4 py-2 text-sm font-semibold text-slate-600 dark:text-slate-300 bg-slate-100 dark:bg-slate-700 rounded-lg hover:bg-slate-200 dark:hover:bg-slate-600 transition-colors">
            Сбросить
        </a>
    </form>
</div>

{{-- Summary cards --}}
<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
    <x-stat-card
        title="Выручка"
        value="{{ number_format($revenue, 0, '.', ' ') }} сум"
        color="green"
    />
    <x-stat-card
        title="Расходы"
        value="{{ number_format($expenses, 0, '.', ' ') }} сум"
        color="red"
    />
    <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm p-6 border-l-4 {{ $profit >= 0 ? 'border-l-emerald-500' : 'border-l-red-500' }}">
        <p class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Прибыль</p>
        <p class="text-3xl font-bold mt-2 {{ $profit >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-red-600 dark:text-red-400' }}">
            {{ number_format($profit, 0, '.', ' ') }} <span class="text-base font-normal text-slate-400 dark:text-slate-500">сум</span>
        </p>
    </div>
</div>

{{-- Two-column breakdown --}}
<div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-5">

    {{-- Expenses by category --}}
    <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-100 dark:border-slate-700">
            <h2 class="text-sm font-semibold text-slate-800 dark:text-slate-200">Расходы по категориям</h2>
        </div>
        @if($expenseByCategory->isEmpty())
            <p class="px-5 py-8 text-sm text-slate-400 dark:text-slate-500">Нет данных за период</p>
        @else
            <table class="w-full text-sm">
                <tbody class="divide-y divide-slate-50 dark:divide-slate-700">
                    @foreach($expenseByCategory as $key => $amount)
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors">
                            <td class="px-5 py-3 text-slate-700 dark:text-slate-300">{{ $categories[$key] ?? $key }}</td>
                            <td class="px-5 py-3 text-right font-semibold text-slate-900 dark:text-slate-100">
                                {{ number_format($amount, 0, '.', ' ') }} <span class="text-slate-400 dark:text-slate-500 font-normal text-xs">сум</span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="bg-slate-50 dark:bg-slate-900/50 border-t border-slate-200 dark:border-slate-700">
                        <td class="px-5 py-3 text-sm font-semibold text-slate-700 dark:text-slate-300">Итого</td>
                        <td class="px-5 py-3 text-right font-bold text-slate-900 dark:text-slate-100">
                            {{ number_format($expenses, 0, '.', ' ') }} <span class="text-slate-400 dark:text-slate-500 font-normal text-xs">сум</span>
                        </td>
                    </tr>
                </tfoot>
            </table>
        @endif
    </div>

    {{-- Revenue by payment method --}}
    <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-100 dark:border-slate-700">
            <h2 class="text-sm font-semibold text-slate-800 dark:text-slate-200">Выручка по способу оплаты</h2>
        </div>
        @if($revenueByMethod->isEmpty())
            <p class="px-5 py-8 text-sm text-slate-400 dark:text-slate-500">Нет данных за период</p>
        @else
            <table class="w-full text-sm">
                <tbody class="divide-y divide-slate-50 dark:divide-slate-700">
                    @foreach($revenueByMethod as $method => $amount)
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors">
                            <td class="px-5 py-3 text-slate-700 dark:text-slate-300">{{ $paymentMethods[$method] ?? $method }}</td>
                            <td class="px-5 py-3 text-right font-semibold text-slate-900 dark:text-slate-100">
                                {{ number_format($amount, 0, '.', ' ') }} <span class="text-slate-400 dark:text-slate-500 font-normal text-xs">сум</span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="bg-slate-50 dark:bg-slate-900/50 border-t border-slate-200 dark:border-slate-700">
                        <td class="px-5 py-3 text-sm font-semibold text-slate-700 dark:text-slate-300">Итого</td>
                        <td class="px-5 py-3 text-right font-bold text-slate-900 dark:text-slate-100">
                            {{ number_format($revenue, 0, '.', ' ') }} <span class="text-slate-400 dark:text-slate-500 font-normal text-xs">сум</span>
                        </td>
                    </tr>
                </tfoot>
            </table>
        @endif
    </div>
</div>

{{-- Recent transactions --}}
<div class="grid grid-cols-1 md:grid-cols-2 gap-5">

    {{-- Recent payments --}}
    <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-100 dark:border-slate-700">
            <h2 class="text-sm font-semibold text-slate-800 dark:text-slate-200">Последние платежи</h2>
        </div>
        @if($recentPayments->isEmpty())
            <p class="px-5 py-8 text-sm text-slate-400 dark:text-slate-500">Нет платежей за период</p>
        @else
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-slate-50 dark:bg-slate-900/50 border-b border-slate-100 dark:border-slate-700">
                        <th class="text-left px-5 py-2 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Гость</th>
                        <th class="text-right px-5 py-2 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Сумма</th>
                        <th class="text-left px-5 py-2 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Способ</th>
                        <th class="text-left px-5 py-2 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Дата</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50 dark:divide-slate-700">
                    @foreach($recentPayments as $payment)
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors">
                            <td class="px-5 py-3 text-slate-700 dark:text-slate-300 text-xs">
                                {{ optional(optional($payment->booking)->guest)->full_name ?? '—' }}
                            </td>
                            <td class="px-5 py-3 text-right font-semibold text-slate-900 dark:text-slate-100 text-xs whitespace-nowrap">
                                {{ number_format($payment->amount, 0, '.', ' ') }} сум
                            </td>
                            <td class="px-5 py-3 text-slate-500 dark:text-slate-400 text-xs">
                                {{ $paymentMethods[$payment->method] ?? $payment->method }}
                            </td>
                            <td class="px-5 py-3 text-slate-400 dark:text-slate-500 text-xs font-mono whitespace-nowrap">
                                {{ $payment->paid_at->format('d.m.Y') }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>

    {{-- Recent expenses --}}
    <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-100 dark:border-slate-700">
            <h2 class="text-sm font-semibold text-slate-800 dark:text-slate-200">Последние расходы</h2>
        </div>
        @if($recentExpenses->isEmpty())
            <p class="px-5 py-8 text-sm text-slate-400 dark:text-slate-500">Нет расходов за период</p>
        @else
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-slate-50 dark:bg-slate-900/50 border-b border-slate-100 dark:border-slate-700">
                        <th class="text-left px-5 py-2 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Категория</th>
                        <th class="text-left px-5 py-2 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Описание</th>
                        <th class="text-right px-5 py-2 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Сумма</th>
                        <th class="text-left px-5 py-2 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Дата</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50 dark:divide-slate-700">
                    @foreach($recentExpenses as $expense)
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors">
                            <td class="px-5 py-3 text-slate-600 dark:text-slate-300 text-xs whitespace-nowrap">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-md bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 text-xs font-medium">
                                    {{ $categories[$expense->category] ?? $expense->category }}
                                </span>
                            </td>
                            <td class="px-5 py-3 text-slate-500 dark:text-slate-400 text-xs max-w-xs truncate">
                                {{ \Illuminate\Support\Str::limit($expense->description, 40) }}
                            </td>
                            <td class="px-5 py-3 text-right font-semibold text-slate-900 dark:text-slate-100 text-xs whitespace-nowrap">
                                {{ number_format($expense->amount, 0, '.', ' ') }} сум
                            </td>
                            <td class="px-5 py-3 text-slate-400 dark:text-slate-500 text-xs font-mono whitespace-nowrap">
                                {{ $expense->expense_date->format('d.m.Y') }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
</div>

@endsection
