@extends('layouts.app')

@section('title', 'Главная')

@section('content')

<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-900">Главная</h1>
    <p class="text-gray-500 mt-1">Добро пожаловать, {{ auth()->user()->name }}!</p>
</div>

{{-- Section 1: Room Status --}}
<section class="mb-8">
    <h2 class="text-lg font-semibold text-gray-700 mb-4">Статус номеров</h2>
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <x-stat-card
            title="Свободно"
            value="{{ $roomStats['available'] }} из {{ $roomStats['total'] }}"
            color="green"
        />
        <x-stat-card
            title="Занято"
            value="{{ $roomStats['occupied'] }}"
            color="red"
        />
        <x-stat-card
            title="Уборка"
            value="{{ $roomStats['cleaning'] }}"
            color="yellow"
        />
        <x-stat-card
            title="Загрузка"
            value="{{ $occupancyRate }}%"
            color="blue"
        />
    </div>
</section>

{{-- Section 2: Today's Activity --}}
<section class="mb-8">
    <h2 class="text-lg font-semibold text-gray-700 mb-4">Активность сегодня</h2>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
        {{-- Check-ins today --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <h3 class="text-sm font-semibold text-gray-600 mb-3">Заезды сегодня
                <span class="ml-1 text-blue-600">({{ $checkInsToday->count() }})</span>
            </h3>
            @forelse($checkInsToday as $booking)
                <div class="flex items-center justify-between py-2 border-b border-gray-50 last:border-0">
                    <div>
                        <p class="text-sm font-medium text-gray-900">{{ $booking->guest->fullName }}</p>
                        <p class="text-xs text-gray-500">Номер {{ $booking->room->number }}</p>
                    </div>
                    <x-status-badge :status="$booking->status" />
                </div>
            @empty
                <p class="text-sm text-gray-400">Нет заездов на сегодня</p>
            @endforelse
        </div>

        {{-- Check-outs today --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <h3 class="text-sm font-semibold text-gray-600 mb-3">Выезды сегодня
                <span class="ml-1 text-red-600">({{ $checkOutsToday->count() }})</span>
            </h3>
            @forelse($checkOutsToday as $booking)
                <div class="flex items-center justify-between py-2 border-b border-gray-50 last:border-0">
                    <div>
                        <p class="text-sm font-medium text-gray-900">{{ $booking->guest->fullName }}</p>
                        <p class="text-xs text-gray-500">Номер {{ $booking->room->number }}</p>
                    </div>
                    <x-status-badge :status="$booking->status" />
                </div>
            @empty
                <p class="text-sm text-gray-400">Нет выездов на сегодня</p>
            @endforelse
        </div>
    </div>

    {{-- Revenue today --}}
    <div class="bg-white rounded-xl border border-gray-200 p-5">
        <p class="text-sm font-medium text-gray-500">Выручка сегодня</p>
        <p class="text-2xl font-bold text-gray-900 mt-1">
            {{ number_format($revenueToday, 0, '.', ' ') }} сум
        </p>
    </div>
</section>

{{-- Section 3: Pending Bookings --}}
@if($pendingBookings->isNotEmpty())
<section class="mb-8">
    <div class="flex items-center justify-between mb-4">
        <h2 class="text-lg font-semibold text-gray-700">
            Ожидают подтверждения ({{ $pendingBookings->count() }})
        </h2>
        <a href="{{ route('bookings.index', ['status' => 'pending']) }}"
           class="text-sm text-blue-600 hover:text-blue-800 font-medium">
            Посмотреть все →
        </a>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        @foreach($pendingBookings as $booking)
            <x-booking-row :booking="$booking" />
        @endforeach
    </div>
</section>
@endif

{{-- Section 4: Upcoming Check-ins --}}
<section class="mb-8">
    <h2 class="text-lg font-semibold text-gray-700 mb-4">Предстоящие заезды</h2>
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        @if($upcomingCheckIns->isNotEmpty())
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-100 bg-gray-50">
                        <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Дата</th>
                        <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Гость</th>
                        <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Номер</th>
                        <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Тип</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($upcomingCheckIns as $booking)
                        <tr class="border-b border-gray-50 hover:bg-gray-50 transition">
                            <td class="px-6 py-3 text-gray-700">{{ $booking->check_in_date->format('d.m.Y') }}</td>
                            <td class="px-6 py-3 font-medium text-gray-900">{{ $booking->guest->fullName }}</td>
                            <td class="px-6 py-3 text-gray-700">{{ $booking->room->number }}</td>
                            <td class="px-6 py-3 text-gray-500">{{ $booking->room->roomType->name }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <div class="px-6 py-8 text-center">
                <p class="text-sm text-gray-400">Нет предстоящих заездов на ближайшие 7 дней</p>
            </div>
        @endif
    </div>
</section>

@endsection
