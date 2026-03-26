@extends('layouts.app')

@section('title', 'Изменить тип номера')

@section('content')
<div class="mb-6">
    <a href="{{ route('room-types.index') }}" class="text-sm text-gray-500 hover:text-gray-700">
        &larr; Назад к типам номеров
    </a>
</div>

<div class="max-w-2xl">
    <h1 class="text-2xl font-bold text-gray-900 mb-6">Изменить тип номера</h1>

    <div class="bg-white rounded-xl border border-gray-200 p-6">
        <form method="POST" action="{{ route('room-types.update', $roomType) }}">
            @csrf
            @method('PUT')

            {{-- Название --}}
            <div class="mb-5">
                <label for="name" class="block text-sm font-medium text-gray-700 mb-1">
                    Название <span class="text-red-500">*</span>
                </label>
                <input type="text"
                       id="name"
                       name="name"
                       value="{{ old('name', $roomType->name) }}"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('name') border-red-400 @enderror"
                       placeholder="Напр: Стандарт, Делюкс, Люкс">
                @error('name')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Цена за ночь --}}
            <div class="mb-5">
                <label for="base_price" class="block text-sm font-medium text-gray-700 mb-1">
                    Цена за ночь <span class="text-red-500">*</span>
                </label>
                <div class="flex items-center gap-2">
                    <input type="number"
                           id="base_price"
                           name="base_price"
                           value="{{ old('base_price', $roomType->base_price) }}"
                           min="0"
                           step="1000"
                           class="flex-1 px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('base_price') border-red-400 @enderror"
                           placeholder="0">
                    <span class="text-sm text-gray-500 whitespace-nowrap">сум</span>
                </div>
                @error('base_price')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Вместимость --}}
            <div class="mb-5">
                <label for="capacity" class="block text-sm font-medium text-gray-700 mb-1">
                    Вместимость <span class="text-red-500">*</span>
                </label>
                <div class="flex items-center gap-2">
                    <input type="number"
                           id="capacity"
                           name="capacity"
                           value="{{ old('capacity', $roomType->capacity) }}"
                           min="1"
                           class="w-32 px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('capacity') border-red-400 @enderror"
                           placeholder="1">
                    <span class="text-sm text-gray-500 whitespace-nowrap">чел.</span>
                </div>
                @error('capacity')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Описание --}}
            <div class="mb-5">
                <label for="description" class="block text-sm font-medium text-gray-700 mb-1">
                    Описание
                </label>
                <textarea id="description"
                          name="description"
                          rows="3"
                          class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('description') border-red-400 @enderror"
                          placeholder="Необязательно">{{ old('description', $roomType->description) }}</textarea>
                @error('description')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Удобства --}}
            <div class="mb-6">
                <label for="amenities" class="block text-sm font-medium text-gray-700 mb-1">
                    Удобства
                </label>
                <input type="text"
                       id="amenities"
                       name="amenities"
                       value="{{ old('amenities', html_entity_decode(implode(', ', $roomType->amenities ?? []), ENT_QUOTES)) }}"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('amenities') border-red-400 @enderror"
                       placeholder="Wi-Fi, ТВ, Сейф">
                <p class="mt-1 text-xs text-gray-400">через запятую, напр: Wi-Fi, ТВ, Сейф</p>
                @error('amenities')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex items-center gap-3">
                <button type="submit"
                        class="px-5 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition">
                    Сохранить
                </button>
                <a href="{{ route('room-types.index') }}"
                   class="px-5 py-2 text-sm font-medium text-gray-600 bg-gray-100 rounded-lg hover:bg-gray-200 transition">
                    Отмена
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
