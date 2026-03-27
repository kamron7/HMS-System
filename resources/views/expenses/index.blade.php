@extends('layouts.app')

@section('title', 'Расходы')

@section('content')
<div class="flex items-center justify-between mb-6">
    <h1 class="text-2xl font-bold text-gray-900">Расходы</h1>
    <a href="{{ route('expenses.create') }}"
       class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition">
        + Добавить расход
    </a>
</div>

{{-- Filter bar --}}
<form method="GET" action="{{ route('expenses.index') }}" class="flex flex-wrap items-end gap-3 mb-6 bg-white rounded-xl border border-gray-200 p-4">
    <div>
        <label for="filter_category" class="block text-xs font-medium text-gray-600 mb-1">Категория</label>
        <select id="filter_category" name="category"
                class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            <option value="">— Все категории —</option>
            @foreach($categories as $key => $label)
                <option value="{{ $key }}" @selected($filters['category'] === $key)>{{ $label }}</option>
            @endforeach
        </select>
    </div>

    <div>
        <label for="filter_month" class="block text-xs font-medium text-gray-600 mb-1">Месяц</label>
        <input type="month" id="filter_month" name="month" value="{{ $filters['month'] }}"
               class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
    </div>

    <button type="submit"
            class="px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition">
        Применить
    </button>

    <a href="{{ route('expenses.index') }}"
       class="px-4 py-2 text-sm font-medium text-gray-600 bg-gray-100 rounded-lg hover:bg-gray-200 transition">
        Сбросить
    </a>
</form>

{{-- Flash messages --}}
@if(session('success'))
    <div class="mb-4 px-4 py-3 bg-green-50 border border-green-200 text-green-800 rounded-lg text-sm">
        {{ session('success') }}
    </div>
@endif

{{-- Table --}}
<div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
    <table class="w-full text-sm">
        <thead>
            <tr class="bg-gray-50 border-b border-gray-200">
                <th class="text-left px-5 py-3 font-semibold text-gray-600">Дата</th>
                <th class="text-left px-5 py-3 font-semibold text-gray-600">Категория</th>
                <th class="text-left px-5 py-3 font-semibold text-gray-600">Описание</th>
                <th class="text-right px-5 py-3 font-semibold text-gray-600">Сумма</th>
                <th class="text-left px-5 py-3 font-semibold text-gray-600">Кто добавил</th>
                <th class="text-right px-5 py-3 font-semibold text-gray-600">Действия</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse($expenses as $expense)
                <tr class="hover:bg-gray-50">
                    <td class="px-5 py-4 text-gray-700 whitespace-nowrap">
                        {{ $expense->expense_date->format('d.m.Y') }}
                    </td>
                    <td class="px-5 py-4 text-gray-700">
                        {{ $categories[$expense->category] ?? $expense->category }}
                    </td>
                    <td class="px-5 py-4 text-gray-600 max-w-xs truncate">
                        {{ \Illuminate\Support\Str::limit($expense->description, 60) }}
                    </td>
                    <td class="px-5 py-4 text-right font-medium text-gray-900 whitespace-nowrap">
                        {{ number_format($expense->amount, 0, '.', ' ') }} сум
                    </td>
                    <td class="px-5 py-4 text-gray-600">
                        {{ $expense->creator->name ?? '—' }}
                    </td>
                    <td class="px-5 py-4 text-right">
                        <div class="inline-flex items-center gap-2">
                            <a href="{{ route('expenses.edit', $expense) }}"
                               class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-blue-700 bg-blue-50 rounded-lg hover:bg-blue-100 transition">
                                Изменить
                            </a>
                            <form method="POST" action="{{ route('expenses.destroy', $expense) }}" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                        onclick="return confirm('Удалить?')"
                                        class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-red-700 bg-red-50 rounded-lg hover:bg-red-100 transition">
                                    Удалить
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="px-5 py-10 text-center text-gray-400">
                        Расходы не найдены
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

{{-- Total --}}
<div class="mt-4 text-right text-sm font-medium text-gray-700">
    Итого: {{ number_format($total, 0, '.', ' ') }} сум
</div>

{{-- Pagination --}}
@if($expenses->hasPages())
    <div class="mt-4">
        {{ $expenses->links() }}
    </div>
@endif
@endsection
