@extends('layouts.app')

@section('title', 'Создать промокод')

@section('content')
<div class="max-w-lg mx-auto">
    <div class="mb-6 flex items-center gap-3">
        <a href="{{ route('promo-codes.index') }}"
           class="inline-flex items-center gap-1.5 text-sm text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 transition-colors">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18"/></svg>
            Промокоды
        </a>
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 text-slate-300 dark:text-slate-600"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5"/></svg>
        <h1 class="text-xl font-bold text-slate-900 dark:text-slate-100">Создать промокод</h1>
    </div>

    <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm p-6">
        <form method="POST" action="{{ route('promo-codes.store') }}" class="space-y-5">
            @csrf

            <div>
                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                    Код <span class="text-red-500">*</span>
                </label>
                <input type="text" name="code" value="{{ old('code') }}" placeholder="SUMMER20"
                       class="w-full px-3 py-2.5 border rounded-lg text-sm uppercase focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100 font-mono tracking-widest {{ $errors->has('code') ? 'border-red-400' : 'border-slate-200 dark:border-slate-600' }}">
                @error('code')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                    Скидка (%) <span class="text-red-500">*</span>
                </label>
                <input type="number" name="discount_percent" value="{{ old('discount_percent') }}"
                       min="1" max="100" step="0.01" placeholder="15"
                       class="w-full px-3 py-2.5 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100 {{ $errors->has('discount_percent') ? 'border-red-400' : 'border-slate-200 dark:border-slate-600' }}">
                @error('discount_percent')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Действует с</label>
                    <input type="date" name="valid_from" value="{{ old('valid_from') }}"
                           class="w-full px-3 py-2.5 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100 border-slate-200 dark:border-slate-600">
                    @error('valid_from')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Действует до</label>
                    <input type="date" name="valid_to" value="{{ old('valid_to') }}"
                           class="w-full px-3 py-2.5 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100 border-slate-200 dark:border-slate-600">
                    @error('valid_to')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>
            </div>

            <div>
                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Макс. использований</label>
                <input type="number" name="max_uses" value="{{ old('max_uses') }}" min="1" placeholder="Без ограничений"
                       class="w-full px-3 py-2.5 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100 border-slate-200 dark:border-slate-600">
                @error('max_uses')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                    Ограничить по типу номера
                    <span class="ml-1 text-xs font-normal text-slate-400">(оставьте пустым — действует на все)</span>
                </label>
                <div class="grid grid-cols-2 gap-2">
                    @foreach($roomTypes as $rt)
                    <label class="flex items-center gap-2 p-2.5 rounded-lg border border-slate-200 dark:border-slate-600 hover:bg-slate-50 dark:hover:bg-slate-700 cursor-pointer transition-colors">
                        <input type="checkbox" name="room_type_ids[]" value="{{ $rt->id }}"
                               @checked(in_array($rt->id, old('room_type_ids', [])))
                               class="w-4 h-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                        <span class="text-sm text-slate-700 dark:text-slate-300">{{ $rt->name }}</span>
                    </label>
                    @endforeach
                </div>
            </div>

            <div class="flex items-center gap-3">
                <input type="hidden" name="is_active" value="0">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="is_active" value="1" checked
                           class="w-4 h-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                    <span class="text-sm font-semibold text-slate-700 dark:text-slate-300">Активен</span>
                </label>
            </div>

            <div class="flex items-center gap-3 pt-1">
                <button type="submit"
                        class="inline-flex items-center gap-2 px-5 py-2.5 bg-blue-600 text-white text-sm font-semibold rounded-lg hover:bg-blue-700 transition-colors">
                    Создать
                </button>
                <a href="{{ route('promo-codes.index') }}"
                   class="px-5 py-2.5 text-sm font-semibold text-slate-600 dark:text-slate-300 bg-slate-100 dark:bg-slate-700 rounded-lg hover:bg-slate-200 dark:hover:bg-slate-600 transition-colors">
                    Отмена
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
