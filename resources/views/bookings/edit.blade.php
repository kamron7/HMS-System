@extends('layouts.app')

@section('title', 'Редактировать бронирование #' . $booking->id)

@section('content')
<div class="max-w-2xl mx-auto">

    {{-- Page header --}}
    <div class="mb-6 flex items-center gap-4">
        <a href="{{ route('bookings.show', $booking) }}"
           class="text-sm text-blue-600 hover:text-blue-800 flex items-center gap-1">
            ← Бронирование #{{ $booking->id }}
        </a>
        <h1 class="text-2xl font-bold text-gray-900">Редактировать бронирование #{{ $booking->id }}</h1>
    </div>

    {{-- Validation errors --}}
    @if($errors->any())
        <div class="mb-4 p-3 bg-red-50 border border-red-200 rounded-lg text-red-800 text-sm">
            <ul class="list-disc list-inside space-y-1">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Read-only info block --}}
    <div class="bg-gray-50 border border-gray-200 rounded-xl p-5 mb-6">
        <h2 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-3">Информация (не редактируется)</h2>
        <dl class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div>
                <dt class="text-xs text-gray-500">Гость</dt>
                <dd class="mt-1 text-sm font-medium text-gray-900">{{ $booking->guest->fullName }}</dd>
            </div>
            <div>
                <dt class="text-xs text-gray-500">Номер</dt>
                <dd class="mt-1 text-sm font-medium text-gray-900">{{ $booking->room->number }}</dd>
            </div>
            <div>
                <dt class="text-xs text-gray-500">Тип номера</dt>
                <dd class="mt-1 text-sm font-medium text-gray-900">{{ $booking->room->roomType->name }}</dd>
            </div>
        </dl>
    </div>

    {{-- Edit form --}}
    <form method="POST" action="{{ route('bookings.update', $booking) }}"
          class="bg-white rounded-xl border border-gray-200 p-6 shadow-sm">
        @csrf
        @method('PUT')

        {{-- Dates --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 mb-5">
            <div>
                <label for="check_in_date" class="block text-sm font-medium text-gray-700 mb-1">
                    Дата заезда <span class="text-red-500">*</span>
                </label>
                <input type="date"
                       id="check_in_date"
                       name="check_in_date"
                       value="{{ old('check_in_date', $booking->check_in_date->format('Y-m-d')) }}"
                       required
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 @error('check_in_date') border-red-400 @enderror">
                @error('check_in_date')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label for="check_out_date" class="block text-sm font-medium text-gray-700 mb-1">
                    Дата выезда <span class="text-red-500">*</span>
                </label>
                <input type="date"
                       id="check_out_date"
                       name="check_out_date"
                       value="{{ old('check_out_date', $booking->check_out_date->format('Y-m-d')) }}"
                       required
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 @error('check_out_date') border-red-400 @enderror">
                @error('check_out_date')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </div>

        {{-- Adults / Children --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 mb-5">
            <div>
                <label for="adults" class="block text-sm font-medium text-gray-700 mb-1">
                    Количество взрослых <span class="text-red-500">*</span>
                </label>
                <input type="number"
                       id="adults"
                       name="adults"
                       value="{{ old('adults', $booking->adults) }}"
                       min="1" max="20"
                       required
                       class="w-32 border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 @error('adults') border-red-400 @enderror">
                @error('adults')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label for="children" class="block text-sm font-medium text-gray-700 mb-1">
                    Количество детей
                </label>
                <input type="number"
                       id="children"
                       name="children"
                       value="{{ old('children', $booking->children) }}"
                       min="0" max="20"
                       class="w-32 border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 @error('children') border-red-400 @enderror">
                @error('children')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </div>

        {{-- Notes --}}
        <div class="mb-6">
            <label for="notes" class="block text-sm font-medium text-gray-700 mb-1">
                Примечания
            </label>
            <textarea id="notes"
                      name="notes"
                      rows="3"
                      maxlength="1000"
                      placeholder="Особые пожелания, аллергии и т.д."
                      class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none @error('notes') border-red-400 @enderror">{{ old('notes', $booking->notes) }}</textarea>
            @error('notes')
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- Current total (informational) --}}
        <div class="mb-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">
            <p class="text-xs text-blue-600 mb-0.5">Текущая стоимость (пересчитается при сохранении)</p>
            <p class="text-lg font-bold text-blue-900">
                {{ number_format($booking->total_price, 0, '.', ' ') }} сум
            </p>
        </div>

        {{-- Action buttons --}}
        <div class="flex items-center justify-between">
            <a href="{{ route('bookings.show', $booking) }}"
               class="px-5 py-2 rounded-lg text-sm font-medium border border-gray-300 text-gray-700 hover:bg-gray-50 transition-colors">
                Отмена
            </a>
            <button type="submit"
                    class="px-6 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors">
                Сохранить изменения
            </button>
        </div>

    </form>

</div>
@endsection
