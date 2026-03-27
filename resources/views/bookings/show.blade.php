@extends('layouts.app')

@section('title', 'Бронирование #' . $booking->id)

@section('content')

{{-- Header --}}
<div class="flex items-center justify-between mb-6">
    <div class="flex items-center gap-4">
        <a href="{{ route('bookings.index') }}"
           class="text-sm text-gray-500 hover:text-gray-700">
            ← К списку бронирований
        </a>
        <h1 class="text-2xl font-bold text-gray-900">
            Бронирование #{{ $booking->id }}
        </h1>
    </div>
</div>

<div class="grid grid-cols-1 md:grid-cols-3 gap-6">

    {{-- LEFT: main details --}}
    <div class="md:col-span-2 space-y-6">

        {{-- Booking details card --}}
        <div class="bg-white rounded-xl border border-gray-200 p-6">
            <h2 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-4">
                Детали бронирования
            </h2>
            <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <dt class="text-xs text-gray-500">Гость</dt>
                    <dd class="mt-1 text-sm font-medium text-gray-900">
                        <a href="{{ route('guests.show', $booking->guest) }}"
                           class="text-blue-700 hover:underline">
                            {{ $booking->guest->fullName }}
                        </a>
                    </dd>
                    @if($booking->guest->phone)
                        <dd class="text-xs text-gray-500 mt-0.5">{{ $booking->guest->phone }}</dd>
                    @endif
                </div>

                <div>
                    <dt class="text-xs text-gray-500">Номер</dt>
                    <dd class="mt-1 text-sm font-medium text-gray-900">
                        {{ $booking->room->number }}
                        <span class="text-xs text-gray-400 font-normal ml-1">
                            {{ $booking->room->roomType->name }}
                        </span>
                    </dd>
                    <dd class="text-xs text-gray-500 mt-0.5">Этаж {{ $booking->room->floor }}</dd>
                </div>

                <div>
                    <dt class="text-xs text-gray-500">Дата заезда</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $booking->check_in_date->format('d.m.Y') }}</dd>
                </div>

                <div>
                    <dt class="text-xs text-gray-500">Дата выезда</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $booking->check_out_date->format('d.m.Y') }}</dd>
                </div>

                <div>
                    <dt class="text-xs text-gray-500">Ночей</dt>
                    <dd class="mt-1 text-sm text-gray-900">
                        {{ $booking->check_in_date->diffInDays($booking->check_out_date) }}
                    </dd>
                </div>

                <div>
                    <dt class="text-xs text-gray-500">Гости</dt>
                    <dd class="mt-1 text-sm text-gray-900">
                        {{ $booking->adults }} взр.@if($booking->children > 0), {{ $booking->children }} дет.@endif
                    </dd>
                </div>

                <div>
                    <dt class="text-xs text-gray-500">Статус</dt>
                    <dd class="mt-1">
                        <x-status-badge :status="$booking->status" />
                    </dd>
                </div>

                @if($booking->notes)
                <div class="sm:col-span-2">
                    <dt class="text-xs text-gray-500">Заметки</dt>
                    <dd class="mt-1 text-sm text-gray-700">{{ $booking->notes }}</dd>
                </div>
                @endif
            </dl>
        </div>

        {{-- Payments table --}}
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100">
                <h2 class="text-sm font-semibold text-gray-700">
                    Платежи
                    <span class="ml-1 text-gray-400 font-normal">({{ $booking->payments->count() }})</span>
                </h2>
            </div>
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-200">
                        <th class="text-left px-5 py-3 font-semibold text-gray-600">Сумма</th>
                        <th class="text-left px-5 py-3 font-semibold text-gray-600">Способ</th>
                        <th class="text-left px-5 py-3 font-semibold text-gray-600">Дата</th>
                        <th class="text-left px-5 py-3 font-semibold text-gray-600">Заметки</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($booking->payments as $payment)
                        <tr class="hover:bg-gray-50">
                            <td class="px-5 py-4 font-medium text-gray-900">
                                {{ number_format($payment->amount, 0, '.', ' ') }} сум
                            </td>
                            <td class="px-5 py-4 text-gray-600">{{ $payment->method }}</td>
                            <td class="px-5 py-4 text-gray-600">{{ $payment->paid_at->format('d.m.Y H:i') }}</td>
                            <td class="px-5 py-4 text-gray-500">{{ $payment->notes ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-5 py-10 text-center text-gray-400">
                                Нет платежей
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Meta info --}}
        <div class="text-xs text-gray-400 px-1">
            Создано: {{ $booking->creator->name ?? '—' }} · {{ $booking->created_at->format('d.m.Y H:i') }}
        </div>

    </div>

    {{-- RIGHT: sidebar --}}
    <div class="space-y-4">

        {{-- Summary box --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <h2 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-4">Итог</h2>

            <div class="mb-3">
                <p class="text-xs text-gray-500">Сумма бронирования</p>
                <p class="text-xl font-bold text-gray-900 mt-0.5">
                    {{ number_format($booking->total_price, 0, '.', ' ') }} сум
                </p>
            </div>

            <div class="mb-4">
                <p class="text-xs text-gray-500 mb-1">Оплата</p>
                @php
                    $paymentColors = [
                        'paid'    => 'bg-green-100 text-green-800',
                        'partial' => 'bg-yellow-100 text-yellow-800',
                        'unpaid'  => 'bg-red-100 text-red-800',
                    ];
                    $paymentLabels = [
                        'paid'    => 'Оплачено',
                        'partial' => 'Частично',
                        'unpaid'  => 'Не оплачено',
                    ];
                    $psColor = $paymentColors[$paymentStatus] ?? 'bg-gray-100 text-gray-700';
                    $psLabel = $paymentLabels[$paymentStatus] ?? $paymentStatus;
                @endphp
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $psColor }}">
                    {{ $psLabel }}
                </span>
            </div>

            @if($booking->payments->count() > 0)
                @php
                    $paidAmount = $booking->payments->sum('amount');
                    $remaining  = (float) $booking->total_price - (float) $paidAmount;
                @endphp
                <div class="text-xs text-gray-500 space-y-1 border-t border-gray-100 pt-3">
                    <div class="flex justify-between">
                        <span>Оплачено</span>
                        <span class="font-medium text-gray-900">{{ number_format($paidAmount, 0, '.', ' ') }} сум</span>
                    </div>
                    @if($remaining > 0)
                        <div class="flex justify-between">
                            <span>Остаток</span>
                            <span class="font-medium text-red-600">{{ number_format($remaining, 0, '.', ' ') }} сум</span>
                        </div>
                    @endif
                </div>
            @endif
        </div>

        {{-- Quick actions --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <h2 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-3">Действия</h2>
            <div class="space-y-2">
                @if(in_array($booking->status, [\App\Enums\BookingStatus::Pending, \App\Enums\BookingStatus::Confirmed]))
                    <a href="{{ route('bookings.edit', $booking) }}"
                       class="block w-full text-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition">
                        Редактировать
                    </a>
                @endif
                @if($booking->status->canTransitionTo(\App\Enums\BookingStatus::Cancelled))
                    <form method="POST" action="{{ route('bookings.status', $booking) }}">
                        @csrf
                        <input type="hidden" name="transition" value="cancelled">
                        <button type="submit"
                                class="w-full border border-red-300 text-red-600 rounded-lg px-4 py-2 text-sm font-medium hover:bg-red-50"
                                onclick="return confirm('Отменить бронирование?')">
                            Отменить бронирование
                        </button>
                    </form>
                @endif
            </div>
        </div>

    </div>
</div>

@endsection
