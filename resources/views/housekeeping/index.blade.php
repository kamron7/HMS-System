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
            'available'       => \App\Enums\RoomStatus::Available->label(),
            'occupied'        => \App\Enums\RoomStatus::Occupied->label(),
            'cleaning'        => \App\Enums\RoomStatus::Cleaning->label(),
            'maintenance'     => \App\Enums\RoomStatus::Maintenance->label(),
        ];

        $tabCounts = [
            null          => $counts['all'],
            'available'   => $counts['available'],
            'occupied'    => $counts['occupied'],
            'cleaning'    => $counts['cleaning'],
            'maintenance' => $counts['maintenance'],
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
                    {{ $tabCounts[$value] ?? 0 }}
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
                @php
                    $cnt = $floorRooms->count();
                    $n = $cnt % 100;
                    $n1 = $n % 10;
                    if ($n > 10 && $n < 20) $word = 'номеров';
                    elseif ($n1 > 1 && $n1 < 5) $word = 'номера';
                    elseif ($n1 === 1) $word = 'номер';
                    else $word = 'номеров';
                @endphp
                <h2 class="text-base font-semibold text-gray-700 mb-3">
                    Этаж {{ $floor }}
                    <span class="text-sm font-normal text-gray-400">({{ $cnt }} {{ $word }})</span>
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
