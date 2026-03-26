@props(['room', 'activeBooking' => null])

@php
    $statusIcon = match($room->status) {
        \App\Enums\RoomStatus::Available   => '✅',
        \App\Enums\RoomStatus::Occupied    => '🔴',
        \App\Enums\RoomStatus::Cleaning    => '🧹',
        \App\Enums\RoomStatus::Maintenance => '🔧',
    };
@endphp

<div
    x-data="{ showModal: false, showTooltip: false }"
    class="relative bg-white border border-gray-200 rounded-xl p-4 cursor-pointer hover:border-blue-300 hover:shadow-sm transition select-none"
    @click="showModal = true"
    @mouseenter="showTooltip = true"
    @mouseleave="showTooltip = false"
>
    {{-- Hover preview for occupied rooms --}}
    @if($room->status === \App\Enums\RoomStatus::Occupied && $activeBooking)
    <div
        x-show="showTooltip"
        x-cloak
        class="absolute bottom-full left-0 mb-2 w-52 bg-gray-900 text-white text-xs rounded-lg p-3 z-10 shadow-lg"
    >
        <p class="font-medium">{{ $activeBooking->guest->fullName }}</p>
        <p class="text-gray-300 mt-1">До: {{ $activeBooking->check_out_date->format('d M') }}</p>
    </div>
    @endif

    <div class="text-center">
        <p class="text-2xl">{{ $statusIcon }}</p>
        <p class="text-lg font-bold text-gray-900 mt-1">{{ $room->number }}</p>
        <p class="text-xs text-gray-500">{{ $room->roomType->name }}</p>
        <x-status-badge :status="$room->status" class="mt-2" />
    </div>

    {{-- Modal --}}
    <div
        x-show="showModal"
        x-cloak
        @click.stop
        class="fixed inset-0 bg-black/30 flex items-center justify-center z-50"
        @click.self="showModal = false"
    >
        <div class="bg-white rounded-xl shadow-xl w-80 p-6">
            <div class="flex justify-between items-start mb-4">
                <h3 class="text-lg font-bold">Номер {{ $room->number }}</h3>
                <button @click="showModal = false" class="text-gray-400 hover:text-gray-600">✕</button>
            </div>
            <p class="text-sm text-gray-600 mb-1">Тип: {{ $room->roomType->name }}</p>
            <p class="text-sm text-gray-600 mb-4">Статус: <x-status-badge :status="$room->status" /></p>

            @if($room->status === \App\Enums\RoomStatus::Occupied && $activeBooking)
                <p class="text-sm font-medium text-gray-800 mb-1">{{ $activeBooking->guest->fullName }}</p>
                <p class="text-sm text-gray-500 mb-4">Выезд: {{ $activeBooking->check_out_date->format('d.m.Y') }}</p>
                <a href="{{ route('bookings.show', $activeBooking) }}"
                   class="block w-full text-center bg-blue-600 text-white rounded-lg px-4 py-2 text-sm font-medium hover:bg-blue-700 mb-2">
                    Открыть бронирование
                </a>
            @endif

            @if($room->status === \App\Enums\RoomStatus::Cleaning || $room->status === \App\Enums\RoomStatus::Maintenance)
            <form method="POST" action="{{ route('housekeeping.update', $room) }}" @click.stop>
                @csrf @method('PATCH')
                @if($room->status === \App\Enums\RoomStatus::Cleaning)
                    <input type="hidden" name="status" value="available">
                    <button type="submit" class="w-full bg-green-600 text-white rounded-lg px-4 py-2 text-sm font-medium hover:bg-green-700">
                        ✅ Отметить свободным
                    </button>
                @elseif($room->status === \App\Enums\RoomStatus::Maintenance)
                    <input type="hidden" name="status" value="available">
                    <button type="submit" class="w-full bg-blue-600 text-white rounded-lg px-4 py-2 text-sm font-medium hover:bg-blue-700">
                        ✅ Завершить ремонт
                    </button>
                @endif
            </form>
            @endif
        </div>
    </div>
</div>
