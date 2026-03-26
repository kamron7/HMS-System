@extends('layouts.app')

@section('title', 'Добавить номер')

@section('content')
<div class="mb-6">
    <a href="{{ route('rooms.index') }}" class="text-sm text-gray-500 hover:text-gray-700">
        &larr; Назад к номерам
    </a>
</div>

<div class="max-w-2xl">
    <h1 class="text-2xl font-bold text-gray-900 mb-6">Добавить номер</h1>

    <div class="bg-white rounded-xl border border-gray-200 p-6">
        <form method="POST" action="{{ route('rooms.store') }}">
            @csrf

            {{-- Номер комнаты --}}
            <div class="mb-5">
                <label for="number" class="block text-sm font-medium text-gray-700 mb-1">
                    Номер комнаты <span class="text-red-500">*</span>
                </label>
                <input type="text"
                       id="number"
                       name="number"
                       value="{{ old('number') }}"
                       maxlength="10"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('number') border-red-400 @enderror"
                       placeholder="Напр: 101, 202А">
                @error('number')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Этаж --}}
            <div class="mb-5">
                <label for="floor" class="block text-sm font-medium text-gray-700 mb-1">
                    Этаж
                </label>
                <input type="number"
                       id="floor"
                       name="floor"
                       value="{{ old('floor') }}"
                       min="1"
                       max="100"
                       class="w-32 px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('floor') border-red-400 @enderror"
                       placeholder="1">
                @error('floor')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Тип номера --}}
            <div class="mb-5">
                <label for="room_type_id" class="block text-sm font-medium text-gray-700 mb-1">
                    Тип номера <span class="text-red-500">*</span>
                </label>
                <select id="room_type_id"
                        name="room_type_id"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('room_type_id') border-red-400 @enderror">
                    <option value="">— Выберите тип —</option>
                    @foreach($roomTypes as $roomType)
                        <option value="{{ $roomType->id }}" @selected(old('room_type_id') == $roomType->id)>
                            {{ $roomType->name }}
                        </option>
                    @endforeach
                </select>
                @error('room_type_id')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Статус --}}
            <div class="mb-5">
                <label for="status" class="block text-sm font-medium text-gray-700 mb-1">
                    Статус <span class="text-red-500">*</span>
                </label>
                <select id="status"
                        name="status"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('status') border-red-400 @enderror">
                    <option value="">— Выберите статус —</option>
                    @foreach($statuses as $status)
                        <option value="{{ $status->value }}" @selected(old('status') === $status->value)>
                            {{ $status->label() }}
                        </option>
                    @endforeach
                </select>
                @error('status')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Заметки --}}
            <div class="mb-6">
                <label for="notes" class="block text-sm font-medium text-gray-700 mb-1">
                    Заметки
                </label>
                <textarea id="notes"
                          name="notes"
                          rows="3"
                          maxlength="1000"
                          class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('notes') border-red-400 @enderror"
                          placeholder="Необязательно">{{ old('notes') }}</textarea>
                @error('notes')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex items-center gap-3">
                <button type="submit"
                        class="px-5 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition">
                    Создать
                </button>
                <a href="{{ route('rooms.index') }}"
                   class="px-5 py-2 text-sm font-medium text-gray-600 bg-gray-100 rounded-lg hover:bg-gray-200 transition">
                    Отмена
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
