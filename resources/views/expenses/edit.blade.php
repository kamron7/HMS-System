@extends('layouts.app')

@section('title', 'Редактировать расход')

@section('content')
<div class="max-w-2xl mx-auto">

    <div class="mb-6 flex items-center gap-3">
        <a href="{{ route('expenses.index') }}"
           class="inline-flex items-center gap-1.5 text-sm text-slate-500 hover:text-slate-700 transition-colors">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18"/></svg>
            Расходы
        </a>
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 text-slate-300"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5"/></svg>
        <h1 class="text-xl font-bold text-slate-900">Редактировать расход</h1>
    </div>

    <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-6">
        <form method="POST" action="{{ route('expenses.update', $expense) }}">
            @csrf
            @method('PUT')

            <div class="mb-5">
                <label for="category" class="block text-sm font-semibold text-slate-700 mb-1.5">
                    Категория <span class="text-red-500">*</span>
                </label>
                <select id="category" name="category"
                        class="w-full px-3 py-2.5 border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('category') border-red-400 @enderror">
                    <option value="">— Выберите категорию —</option>
                    @foreach($categories as $key => $label)
                        <option value="{{ $key }}" @selected(old('category', $expense->category) === $key)>{{ $label }}</option>
                    @endforeach
                </select>
                @error('category')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>

            <div class="mb-5">
                <label for="description" class="block text-sm font-semibold text-slate-700 mb-1.5">
                    Описание <span class="text-red-500">*</span>
                </label>
                <textarea id="description" name="description" rows="3" maxlength="500" required
                          placeholder="Описание расхода"
                          class="w-full px-3 py-2.5 border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 resize-none @error('description') border-red-400 @enderror">{{ old('description', $expense->description) }}</textarea>
                @error('description')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>

            <div class="mb-5">
                <label for="amount" class="block text-sm font-semibold text-slate-700 mb-1.5">
                    Сумма <span class="text-red-500">*</span>
                </label>
                <div class="flex items-center gap-2">
                    <input type="number" id="amount" name="amount"
                           value="{{ old('amount', $expense->amount) }}" step="0.01" min="0.01" required
                           class="w-48 px-3 py-2.5 border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('amount') border-red-400 @enderror">
                    <span class="text-sm font-medium text-slate-500">сум</span>
                </div>
                @error('amount')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>

            <div class="mb-6">
                <label for="expense_date" class="block text-sm font-semibold text-slate-700 mb-1.5">
                    Дата расхода <span class="text-red-500">*</span>
                </label>
                <input type="date" id="expense_date" name="expense_date" required
                       value="{{ old('expense_date', $expense->expense_date->format('Y-m-d')) }}"
                       class="w-48 px-3 py-2.5 border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('expense_date') border-red-400 @enderror">
                @error('expense_date')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>

            <div class="flex items-center gap-3">
                <button type="submit"
                        class="inline-flex items-center gap-2 px-5 py-2.5 bg-blue-600 text-white text-sm font-semibold rounded-lg hover:bg-blue-700 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                    Сохранить изменения
                </button>
                <a href="{{ route('expenses.index') }}"
                   class="px-5 py-2.5 text-sm font-semibold text-slate-600 bg-slate-100 rounded-lg hover:bg-slate-200 transition-colors">
                    Отмена
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
