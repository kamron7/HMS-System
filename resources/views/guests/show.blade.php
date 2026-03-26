@extends('layouts.app')

@section('title', $guest->full_name)

@section('content')
<div class="flex items-center justify-between mb-6">
    <h1 class="text-2xl font-bold text-gray-900">{{ $guest->full_name }}</h1>
    <div class="flex items-center gap-3">
        <a href="{{ route('guests.edit', $guest) }}"
           class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition">
            Изменить
        </a>
        <a href="{{ route('guests.index') }}"
           class="text-sm text-gray-500 hover:text-gray-700">
            ← К списку гостей
        </a>
    </div>
</div>

{{-- Guest info card --}}
<div class="bg-white rounded-xl border border-gray-200 p-6 mb-6">
    <h2 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-4">Информация о госте</h2>
    <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
            <dt class="text-xs text-gray-500">ФИО</dt>
            <dd class="mt-1 text-sm font-medium text-gray-900">{{ $guest->full_name }}</dd>
        </div>
        <div>
            <dt class="text-xs text-gray-500">Телефон</dt>
            <dd class="mt-1 text-sm text-gray-900">{{ $guest->phone ?? '—' }}</dd>
        </div>
        <div>
            <dt class="text-xs text-gray-500">Email</dt>
            <dd class="mt-1 text-sm text-gray-900">{{ $guest->email ?? '—' }}</dd>
        </div>
        <div>
            <dt class="text-xs text-gray-500">Паспорт</dt>
            <dd class="mt-1 text-sm text-gray-900">{{ $guest->passport_number ?? '—' }}</dd>
        </div>
        <div>
            <dt class="text-xs text-gray-500">Гражданство</dt>
            <dd class="mt-1 text-sm text-gray-900">{{ $guest->nationality ?? '—' }}</dd>
        </div>
    </dl>
</div>

{{-- Booking history --}}
<div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
    <div class="px-5 py-4 border-b border-gray-100">
        <h2 class="text-sm font-semibold text-gray-700">
            История бронирований
            <span class="ml-1 text-gray-400 font-normal">({{ $guest->bookings->count() }})</span>
        </h2>
    </div>
    <table class="w-full text-sm">
        <thead>
            <tr class="bg-gray-50 border-b border-gray-200">
                <th class="text-left px-5 py-3 font-semibold text-gray-600">Номер</th>
                <th class="text-left px-5 py-3 font-semibold text-gray-600">Даты</th>
                <th class="text-left px-5 py-3 font-semibold text-gray-600">Статус</th>
                <th class="text-right px-5 py-3 font-semibold text-gray-600">Сумма</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse($guest->bookings as $booking)
                <tr class="hover:bg-gray-50">
                    <td class="px-5 py-4 font-medium text-gray-900">
                        {{ $booking->room->number ?? '—' }}
                        @if($booking->room && $booking->room->roomType)
                            <span class="text-xs text-gray-400 font-normal ml-1">{{ $booking->room->roomType->name }}</span>
                        @endif
                    </td>
                    <td class="px-5 py-4 text-gray-600">
                        {{ $booking->check_in_date->format('d.m.Y') }}
                        —
                        {{ $booking->check_out_date->format('d.m.Y') }}
                    </td>
                    <td class="px-5 py-4">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                            {{ match($booking->status->value ?? $booking->status) {
                                'confirmed' => 'bg-green-100 text-green-800',
                                'checked_in' => 'bg-blue-100 text-blue-800',
                                'checked_out' => 'bg-gray-100 text-gray-700',
                                'cancelled' => 'bg-red-100 text-red-800',
                                default => 'bg-yellow-100 text-yellow-800',
                            } }}">
                            {{ $booking->status->value ?? $booking->status }}
                        </span>
                    </td>
                    <td class="px-5 py-4 text-right text-gray-900">
                        {{ number_format($booking->total_price, 0, '.', ' ') }} сум
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="px-5 py-10 text-center text-gray-400">
                        Нет бронирований
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
