@extends('layouts.app')

@section('title', 'Добавить тип номера')

@section('content')
<div class="max-w-2xl mx-auto">

    <div class="mb-6 flex items-center gap-3">
        <a href="{{ route('room-types.index') }}"
           class="inline-flex items-center gap-1.5 text-sm text-slate-500 hover:text-slate-700 transition-colors">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18"/></svg>
            Типы номеров
        </a>
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 text-slate-300"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5"/></svg>
        <h1 class="text-xl font-bold text-slate-900">Добавить тип номера</h1>
    </div>

    <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-6">
        <form method="POST" action="{{ route('room-types.store') }}">
            @csrf

            <div class="mb-5">
                <label for="name" class="block text-sm font-semibold text-slate-700 mb-1.5">
                    Название <span class="text-red-500">*</span>
                </label>
                <input type="text" id="name" name="name" value="{{ old('name') }}"
                       placeholder="Напр: Стандарт, Делюкс, Люкс"
                       class="w-full px-3 py-2.5 border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('name') border-red-400 @enderror">
                @error('name')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>

            <div class="mb-5">
                <label for="base_price" class="block text-sm font-semibold text-slate-700 mb-1.5">
                    Цена за ночь <span class="text-red-500">*</span>
                </label>
                <div class="flex items-center gap-2">
                    <input type="number" id="base_price" name="base_price" value="{{ old('base_price') }}"
                           min="0" step="1000" placeholder="0"
                           class="flex-1 px-3 py-2.5 border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('base_price') border-red-400 @enderror">
                    <span class="text-sm font-medium text-slate-500">сум</span>
                </div>
                @error('base_price')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>

            <div class="mb-5">
                <label for="capacity" class="block text-sm font-semibold text-slate-700 mb-1.5">
                    Вместимость <span class="text-red-500">*</span>
                </label>
                <div class="flex items-center gap-2">
                    <input type="number" id="capacity" name="capacity" value="{{ old('capacity') }}"
                           min="1" placeholder="1"
                           class="w-32 px-3 py-2.5 border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('capacity') border-red-400 @enderror">
                    <span class="text-sm font-medium text-slate-500">чел.</span>
                </div>
                @error('capacity')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>

            <div class="mb-5">
                <label for="description" class="block text-sm font-semibold text-slate-700 mb-1.5">Описание</label>
                <textarea id="description" name="description" rows="3" placeholder="Необязательно"
                          class="w-full px-3 py-2.5 border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 resize-none @error('description') border-red-400 @enderror">{{ old('description') }}</textarea>
                @error('description')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>

            <div class="mb-6">
                <label for="amenities" class="block text-sm font-semibold text-slate-700 mb-1.5">Удобства</label>
                <input type="text" id="amenities" name="amenities" value="{{ old('amenities') }}"
                       placeholder="Wi-Fi, ТВ, Сейф"
                       class="w-full px-3 py-2.5 border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('amenities') border-red-400 @enderror">
                <p class="mt-1 text-xs text-slate-400">Через запятую, напр: Wi-Fi, ТВ, Сейф</p>
                @error('amenities')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>

            <div class="flex items-center gap-3">
                <button type="submit"
                        class="inline-flex items-center gap-2 px-5 py-2.5 bg-blue-600 text-white text-sm font-semibold rounded-lg hover:bg-blue-700 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                    Создать
                </button>
                <a href="{{ route('room-types.index') }}"
                   class="px-5 py-2.5 text-sm font-semibold text-slate-600 bg-slate-100 rounded-lg hover:bg-slate-200 transition-colors">
                    Отмена
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
