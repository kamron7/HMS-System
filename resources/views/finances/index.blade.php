@extends('layouts.app')

@section('title', 'Финансы')

@section('content')
<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Финансы</h1>
        <p class="text-sm text-gray-500 mt-0.5">{{ $periodLabel }}</p>
    </div>
    <a href="{{ route('expenses.create') }}"
       class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition">
        + Добавить расход
    </a>
</div>

{{-- Period selector --}}
<div class="bg-white rounded-xl border border-gray-200 p-4 mb-6" x-data="{ rangeMode: {{ request()->has('start') && request()->has('end') ? 'true' : 'false' }} }">
    <form method="GET" action="{{ route('finances.index') }}" class="flex flex-wrap items-end gap-4">

        {{-- Toggle between month and range --}}
        <div class="flex items-center gap-3">
            <label class="flex items-center gap-1.5 text-sm text-gray-600 cursor-pointer">
                <input type="radio" name="mode" value="month" x-model="rangeMode" :value="false"
                       @change="rangeMode = false"
                       {{ !request()->has('start') ? 'checked' : '' }}
                       class="accent-blue-600">
                По месяцу
            </label>
            <label class="flex items-center gap-1.5 text-sm text-gray-600 cursor-pointer">
                <input type="radio" name="mode" value="range" x-model="rangeMode" :value="true"
                       @change="rangeMode = true"
                       {{ request()->has('start') ? 'checked' : '' }}
                       class="accent-blue-600">
                Произвольный период
            </label>
        </div>

        {{-- Month picker --}}
        <div x-show="!rangeMode">
            <label class="block text-xs font-medium text-gray-600 mb-1">Месяц</label>
            <input type="month" name="period"
                   value="{{ $period }}"
                   class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>

        {{-- Date range picker --}}
        <div x-show="rangeMode" class="flex items-end gap-2">
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Начало</label>
                <input type="date" name="start"
                       value="{{ request()->input('start', $start->toDateString()) }}"
                       class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Конец</label>
                <input type="date" name="end"
                       value="{{ request()->input('end', $end->toDateString()) }}"
                       class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
        </div>

        <button type="submit"
                class="px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition">
            Применить
        </button>

        <a href="{{ route('finances.index') }}"
           class="px-4 py-2 text-sm font-medium text-gray-600 bg-gray-100 rounded-lg hover:bg-gray-200 transition">
            Сбросить
        </a>
    </form>
</div>

{{-- Summary cards --}}
<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
    <x-stat-card
        title="Выручка"
        value="{{ number_format($revenue, 0, '.', ' ') }} сум"
    />
    <x-stat-card
        title="Расходы"
        value="{{ number_format($expenses, 0, '.', ' ') }} сум"
    />

    {{-- Profit card with conditional color --}}
    <div class="bg-white rounded-xl border border-gray-200 p-6">
        <p class="text-sm font-medium text-gray-500">Прибыль</p>
        <p class="text-3xl font-bold mt-1 {{ $profit >= 0 ? 'text-green-600' : 'text-red-600' }}">
            {{ number_format($profit, 0, '.', ' ') }} сум
        </p>
    </div>
</div>

{{-- Two-column breakdown --}}
<div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">

    {{-- Expenses by category --}}
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100">
            <h2 class="text-base font-semibold text-gray-900">Расходы по категориям</h2>
        </div>
        @if($expenseByCategory->isEmpty())
            <p class="px-5 py-6 text-sm text-gray-400">Нет данных за период</p>
        @else
            <table class="w-full text-sm">
                <tbody class="divide-y divide-gray-50">
                    @foreach($expenseByCategory as $key => $amount)
                        <tr class="hover:bg-gray-50">
                            <td class="px-5 py-3 text-gray-700">
                                {{ $categories[$key] ?? $key }}
                            </td>
                            <td class="px-5 py-3 text-right font-medium text-gray-900">
                                {{ number_format($amount, 0, '.', ' ') }} сум
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="bg-gray-50 border-t border-gray-200">
                        <td class="px-5 py-3 text-sm font-semibold text-gray-700">Итого</td>
                        <td class="px-5 py-3 text-right font-semibold text-gray-900">
                            {{ number_format($expenses, 0, '.', ' ') }} сум
                        </td>
                    </tr>
                </tfoot>
            </table>
        @endif
    </div>

    {{-- Revenue by payment method --}}
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100">
            <h2 class="text-base font-semibold text-gray-900">Выручка по способу оплаты</h2>
        </div>
        @if($revenueByMethod->isEmpty())
            <p class="px-5 py-6 text-sm text-gray-400">Нет данных за период</p>
        @else
            <table class="w-full text-sm">
                <tbody class="divide-y divide-gray-50">
                    @foreach($revenueByMethod as $method => $amount)
                        <tr class="hover:bg-gray-50">
                            <td class="px-5 py-3 text-gray-700">
                                {{ $paymentMethods[$method] ?? $method }}
                            </td>
                            <td class="px-5 py-3 text-right font-medium text-gray-900">
                                {{ number_format($amount, 0, '.', ' ') }} сум
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="bg-gray-50 border-t border-gray-200">
                        <td class="px-5 py-3 text-sm font-semibold text-gray-700">Итого</td>
                        <td class="px-5 py-3 text-right font-semibold text-gray-900">
                            {{ number_format($revenue, 0, '.', ' ') }} сум
                        </td>
                    </tr>
                </tfoot>
            </table>
        @endif
    </div>
</div>

{{-- Recent transactions --}}
<div class="grid grid-cols-1 md:grid-cols-2 gap-6">

    {{-- Recent payments --}}
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100">
            <h2 class="text-base font-semibold text-gray-900">Последние платежи</h2>
        </div>
        @if($recentPayments->isEmpty())
            <p class="px-5 py-6 text-sm text-gray-400">Нет платежей за период</p>
        @else
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-100">
                        <th class="text-left px-5 py-2 font-semibold text-gray-600 text-xs">Гость</th>
                        <th class="text-right px-5 py-2 font-semibold text-gray-600 text-xs">Сумма</th>
                        <th class="text-left px-5 py-2 font-semibold text-gray-600 text-xs">Способ</th>
                        <th class="text-left px-5 py-2 font-semibold text-gray-600 text-xs">Дата</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach($recentPayments as $payment)
                        <tr class="hover:bg-gray-50">
                            <td class="px-5 py-3 text-gray-700">
                                {{ optional(optional($payment->booking)->guest)->full_name ?? '—' }}
                            </td>
                            <td class="px-5 py-3 text-right font-medium text-gray-900 whitespace-nowrap">
                                {{ number_format($payment->amount, 0, '.', ' ') }} сум
                            </td>
                            <td class="px-5 py-3 text-gray-600">
                                {{ $paymentMethods[$payment->method] ?? $payment->method }}
                            </td>
                            <td class="px-5 py-3 text-gray-500 whitespace-nowrap">
                                {{ $payment->paid_at->format('d.m.Y') }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>

    {{-- Recent expenses --}}
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100">
            <h2 class="text-base font-semibold text-gray-900">Последние расходы</h2>
        </div>
        @if($recentExpenses->isEmpty())
            <p class="px-5 py-6 text-sm text-gray-400">Нет расходов за период</p>
        @else
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-100">
                        <th class="text-left px-5 py-2 font-semibold text-gray-600 text-xs">Категория</th>
                        <th class="text-left px-5 py-2 font-semibold text-gray-600 text-xs">Описание</th>
                        <th class="text-right px-5 py-2 font-semibold text-gray-600 text-xs">Сумма</th>
                        <th class="text-left px-5 py-2 font-semibold text-gray-600 text-xs">Дата</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach($recentExpenses as $expense)
                        <tr class="hover:bg-gray-50">
                            <td class="px-5 py-3 text-gray-700 whitespace-nowrap">
                                {{ $categories[$expense->category] ?? $expense->category }}
                            </td>
                            <td class="px-5 py-3 text-gray-600 max-w-xs truncate">
                                {{ \Illuminate\Support\Str::limit($expense->description, 40) }}
                            </td>
                            <td class="px-5 py-3 text-right font-medium text-gray-900 whitespace-nowrap">
                                {{ number_format($expense->amount, 0, '.', ' ') }} сум
                            </td>
                            <td class="px-5 py-3 text-gray-500 whitespace-nowrap">
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
