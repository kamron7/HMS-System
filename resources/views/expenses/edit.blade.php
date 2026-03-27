@extends('layouts.app')

@section('title', 'Редактировать расход')

@section('content')
<div class="mb-6">
    <a href="{{ route('expenses.index') }}" class="text-sm text-gray-500 hover:text-gray-700">
        &larr; Назад к расходам
    </a>
</div>

<div class="max-w-2xl">
    <h1 class="text-2xl font-bold text-gray-900 mb-6">Редактировать расход</h1>

    <div class="bg-white rounded-xl border border-gray-200 p-6">
        <form method="POST" action="{{ route('expenses.update', $expense) }}">
            @csrf
            @method('PUT')

            {{-- Категория --}}
            <div class="mb-5">
                <label for="category" class="block text-sm font-medium text-gray-700 mb-1">
                    Категория <span class="text-red-500">*</span>
                </label>
                <select id="category" name="category"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 @error('category') border-red-400 @enderror">
                    <option value="">— Выберите категорию —</option>
                    @foreach($categories as $key => $label)
                        <option value="{{ $key }}" @selected(old('category', $expense->category) === $key)>{{ $label }}</option>
                    @endforeach
                </select>
                @error('category')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Описание --}}
            <div class="mb-5">
                <label for="description" class="block text-sm font-medium text-gray-700 mb-1">
                    Описание <span class="text-red-500">*</span>
                </label>
                <textarea id="description" name="description" rows="3" maxlength="500" required
                          class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 @error('description') border-red-400 @enderror"
                          placeholder="Описание расхода">{{ old('description', $expense->description) }}</textarea>
                @error('description')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Сумма --}}
            <div class="mb-5">
                <label for="amount" class="block text-sm font-medium text-gray-700 mb-1">
                    Сумма <span class="text-red-500">*</span>
                </label>
                <div class="flex items-center gap-2">
                    <input type="number" id="amount" name="amount"
                           value="{{ old('amount', $expense->amount) }}"
                           step="0.01" min="0.01" required
                           class="w-48 px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 @error('amount') border-red-400 @enderror"
                           placeholder="0.00">
                    <span class="text-sm text-gray-500">сум</span>
                </div>
                @error('amount')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Дата расхода --}}
            <div class="mb-6">
                <label for="expense_date" class="block text-sm font-medium text-gray-700 mb-1">
                    Дата расхода <span class="text-red-500">*</span>
                </label>
                <input type="date" id="expense_date" name="expense_date"
                       value="{{ old('expense_date', $expense->expense_date->format('Y-m-d')) }}" required
                       class="w-48 px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 @error('expense_date') border-red-400 @enderror">
                @error('expense_date')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex items-center gap-3">
                <button type="submit"
                        class="px-5 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition">
                    Сохранить изменения
                </button>
                <a href="{{ route('expenses.index') }}"
                   class="px-5 py-2 text-sm font-medium text-gray-600 bg-gray-100 rounded-lg hover:bg-gray-200 transition">
                    Отмена
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
