@extends('layouts.app')

@section('title', 'Горничные')

@section('content')
<div>
    {{-- Page header --}}
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Управление номерами</h1>
    </div>

    {{-- Filter tabs --}}
    @php
        $tabs = [
            null              => 'Все',
            'available'       => 'Свободен',
            'occupied'        => 'Занят',
            'cleaning'        => 'Уборка',
            'maintenance'     => 'Ремонт',
        ];

        $allRooms = \App\Models\Room::all();
        $counts = [
            null          => $allRooms->count(),
            'available'   => $allRooms->where('status', \App\Enums\RoomStatus::Available)->count(),
            'occupied'    => $allRooms->where('status', \App\Enums\RoomStatus::Occupied)->count(),
            'cleaning'    => $allRooms->where('status', \App\Enums\RoomStatus::Cleaning)->count(),
            'maintenance' => $allRooms->where('status', \App\Enums\RoomStatus::Maintenance)->count(),
        ];
    @endphp

    <div class="flex flex-wrap gap-2 mb-6">
        @foreach($tabs as $value => $label)
            @php
                $isActive = ($statusFilter ?? null) === ($value ?: null);
                $url = $value ? route('housekeeping.index', ['status' => $value]) : route('housekeeping.index');
            @endphp
            <a href="{{ $url }}"
               class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg text-sm font-medium transition
                      {{ $isActive
                          ? 'bg-blue-600 text-white'
                          : 'bg-white border border-gray-200 text-gray-700 hover:bg-gray-50' }}">
                {{ $label }}
                <span class="inline-flex items-center justify-center w-5 h-5 text-xs rounded-full
                             {{ $isActive ? 'bg-blue-500 text-white' : 'bg-gray-100 text-gray-600' }}">
                    {{ $counts[$value] ?? 0 }}
                </span>
            </a>
        @endforeach
    </div>

    {{-- Room grid grouped by floor --}}
    @if($rooms->isEmpty())
        <div class="text-center py-16 text-gray-400">
            <p class="text-lg">Нет номеров для отображения</p>
        </div>
    @else
        @foreach($rooms as $floor => $floorRooms)
            <div class="mb-8">
                <h2 class="text-base font-semibold text-gray-700 mb-3">
                    Этаж {{ $floor }}
                    <span class="text-sm font-normal text-gray-400">({{ $floorRooms->count() }} {{ trans_choice('номер|номера|номеров', $floorRooms->count()) }})</span>
                </h2>
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-3">
                    @foreach($floorRooms as $room)
                        <x-room-card :room="$room" :active-booking="$room->bookings->first()" />
                    @endforeach
                </div>
            </div>
        @endforeach
    @endif
</div>
@endsection
