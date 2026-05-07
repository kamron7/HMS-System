@extends('layouts.app')

@section('title', 'Редактировать бронирование #' . $booking->id)

@section('content')
<div class="max-w-2xl mx-auto">

    <div class="mb-6 flex items-center gap-3">
        <a href="{{ route('bookings.show', $booking) }}"
           class="inline-flex items-center gap-1.5 text-sm text-slate-500 hover:text-slate-700 transition-colors">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18"/></svg>
            Бронирование #{{ $booking->id }}
        </a>
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 text-slate-300"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5"/></svg>
        <h1 class="text-xl font-bold text-slate-900">Редактирование</h1>
    </div>

    @if($errors->any())
        <div class="mb-5 p-4 bg-red-50 border border-red-200 rounded-xl text-red-800 text-sm">
            <ul class="list-disc list-inside space-y-1">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Read-only info --}}
    <div class="bg-slate-50 border border-slate-200 rounded-xl p-5 mb-5">
        <h2 class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-3">Не редактируется</h2>
        <dl class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div>
                <dt class="text-xs text-slate-500 mb-0.5">Гость</dt>
                <dd class="text-sm font-semibold text-slate-900">{{ $booking->guest->fullName }}</dd>
            </div>
            <div>
                <dt class="text-xs text-slate-500 mb-0.5">Номер</dt>
                <dd class="text-sm font-semibold text-slate-900">{{ $booking->room->number }}</dd>
            </div>
            <div>
                <dt class="text-xs text-slate-500 mb-0.5">Тип</dt>
                <dd class="text-sm font-semibold text-slate-900">{{ $booking->room->roomType->name }}</dd>
            </div>
        </dl>
    </div>

    <form method="POST" action="{{ route('bookings.update', $booking) }}"
          class="bg-white rounded-xl border border-slate-200 shadow-sm p-6">
        @csrf
        @method('PUT')
        <input type="hidden" name="room_id" value="{{ $booking->room_id }}">
        <input type="hidden" name="guest_id" value="{{ $booking->guest_id }}">

        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-5">
            <div class="col-span-1 sm:col-span-2">
                <label for="check_in_date" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                    Дата заезда <span class="text-red-500">*</span>
                </label>
                <input type="date" id="check_in_date" name="check_in_date"
                       value="{{ old('check_in_date', $booking->check_in_date->format('Y-m-d')) }}" required
                       class="w-full border border-slate-200 dark:border-slate-600 dark:bg-slate-700 dark:text-white rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('check_in_date') border-red-400 @enderror">
                @error('check_in_date')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>
            <div class="col-span-1">
                <label for="check_in_time" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                    Время заезда
                </label>
                <input type="time" id="check_in_time" name="check_in_time"
                       value="{{ old('check_in_time', $booking->check_in_time ?? '') }}"
                       class="w-full border border-slate-200 dark:border-slate-600 dark:bg-slate-700 dark:text-white rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            </div>
            <div class="col-span-1 sm:col-span-2">
                <label for="check_out_date" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                    Дата выезда <span class="text-red-500">*</span>
                </label>
                <input type="date" id="check_out_date" name="check_out_date"
                       value="{{ old('check_out_date', $booking->check_out_date->format('Y-m-d')) }}" required
                       class="w-full border border-slate-200 dark:border-slate-600 dark:bg-slate-700 dark:text-white rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('check_out_date') border-red-400 @enderror">
                @error('check_out_date')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>
            <div class="col-span-1">
                <label for="check_out_time" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                    Время выезда
                </label>
                <input type="time" id="check_out_time" name="check_out_time"
                       value="{{ old('check_out_time', $booking->check_out_time ?? '') }}"
                       class="w-full border border-slate-200 dark:border-slate-600 dark:bg-slate-700 dark:text-white rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 mb-5">
            <div>
                <label for="adults" class="block text-sm font-semibold text-slate-700 mb-1.5">
                    Взрослых <span class="text-red-500">*</span>
                </label>
                <input type="number" id="adults" name="adults"
                       value="{{ old('adults', $booking->adults) }}" min="1" max="20" required
                       class="w-32 border border-slate-200 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('adults') border-red-400 @enderror">
                @error('adults')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="children" class="block text-sm font-semibold text-slate-700 mb-1.5">Детей</label>
                <input type="number" id="children" name="children"
                       value="{{ old('children', $booking->children) }}" min="0" max="20"
                       class="w-32 border border-slate-200 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('children') border-red-400 @enderror">
                @error('children')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>
        </div>

        <div class="mb-5">
            <label for="notes" class="block text-sm font-semibold text-slate-700 mb-1.5">Примечания</label>
            <textarea id="notes" name="notes" rows="3" maxlength="1000" placeholder="Особые пожелания…"
                      class="w-full border border-slate-200 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 resize-none @error('notes') border-red-400 @enderror">{{ old('notes', $booking->notes) }}</textarea>
            @error('notes')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
        </div>

        <div class="mb-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">
            <p class="text-xs text-blue-600 mb-0.5">Текущая стоимость (пересчитается при сохранении)</p>
            <p class="text-xl font-bold text-blue-900">
                {{ number_format($booking->total_price, 0, '.', ' ') }} сум
            </p>
        </div>

        <div class="flex items-center justify-between">
            <a href="{{ route('bookings.show', $booking) }}"
               class="inline-flex items-center gap-2 px-5 py-2.5 rounded-lg text-sm font-semibold border border-slate-200 text-slate-600 hover:bg-slate-50 transition-colors">
                Отмена
            </a>
            <button type="submit"
                    class="inline-flex items-center gap-2 px-6 py-2.5 bg-blue-600 text-white text-sm font-semibold rounded-lg hover:bg-blue-700 transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                Сохранить изменения
            </button>
        </div>
    </form>

</div>
@endsection
