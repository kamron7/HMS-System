@extends('layouts.app')

@section('title', 'Бронирования')

@section('content')
<div class="flex items-center justify-between mb-6">
    <h1 class="text-2xl font-bold text-gray-900">
        Бронирования
        <span class="ml-2 text-base font-normal text-gray-400">({{ $bookings->total() }})</span>
    </h1>
    <a href="{{ route('bookings.create') }}"
       class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition">
        + Новое бронирование
    </a>
</div>

{{-- Filter bar --}}
<form method="GET" action="{{ route('bookings.index') }}" class="mb-6">
    <div class="flex flex-wrap gap-3 items-end bg-white border border-gray-200 rounded-xl px-4 py-3">
        {{-- Search --}}
        <div class="flex-1 min-w-48">
            <label class="block text-xs text-gray-500 mb-1">Гость</label>
            <input
                type="text"
                name="search"
                value="{{ $search }}"
                placeholder="Поиск по гостю..."
                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            >
        </div>

        {{-- Status --}}
        <div class="min-w-44">
            <label class="block text-xs text-gray-500 mb-1">Статус</label>
            <select
                name="status"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            >
                <option value="">Все статусы</option>
                @foreach($statuses as $s)
                    <option value="{{ $s->value }}" @selected($status === $s->value)>
                        {{ $s->label() }}
                    </option>
                @endforeach
            </select>
        </div>

        {{-- Check-in from --}}
        <div class="min-w-40">
            <label class="block text-xs text-gray-500 mb-1">Заезд от</label>
            <input
                type="date"
                name="check_in"
                value="{{ $check_in }}"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            >
        </div>

        {{-- Check-out to --}}
        <div class="min-w-40">
            <label class="block text-xs text-gray-500 mb-1">Выезд до</label>
            <input
                type="date"
                name="check_out"
                value="{{ $check_out }}"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            >
        </div>

        {{-- Actions --}}
        <div class="flex gap-2">
            <button type="submit"
                    class="px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition">
                Найти
            </button>
            <a href="{{ route('bookings.index') }}"
               class="px-4 py-2 text-sm text-gray-500 hover:text-gray-700 rounded-lg hover:bg-gray-100 transition">
                Сбросить
            </a>
        </div>
    </div>
</form>

{{-- Bookings list --}}
<div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
    @forelse($bookings as $booking)
        <x-booking-row :booking="$booking" />
    @empty
        <div class="px-6 py-16 text-center">
            <p class="text-gray-400 text-sm mb-3">Нет бронирований</p>
            @if($search || $status || $check_in || $check_out)
                <a href="{{ route('bookings.index') }}"
                   class="text-blue-600 text-sm hover:underline">
                    Сбросить фильтры
                </a>
            @else
                <a href="{{ route('bookings.create') }}"
                   class="inline-flex items-center gap-1 px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition">
                    + Создать первое бронирование
                </a>
            @endif
        </div>
    @endforelse
</div>

@if($bookings->hasPages())
    <div class="mt-4">
        {{ $bookings->links() }}
    </div>
@endif
@endsection
