@extends('layouts.app')

@section('title', 'Номера')

@section('content')
<div class="flex items-center justify-between mb-6">
    <h1 class="text-2xl font-bold text-gray-900">
        Номера
        <span class="ml-2 text-base font-normal text-gray-400">({{ $rooms->flatten()->count() }})</span>
    </h1>
    <a href="{{ route('rooms.create') }}"
       class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition">
        + Добавить номер
    </a>
</div>

<div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
    <table class="w-full text-sm">
        <thead>
            <tr class="bg-gray-50 border-b border-gray-200">
                <th class="text-left px-5 py-3 font-semibold text-gray-600">Номер</th>
                <th class="text-left px-5 py-3 font-semibold text-gray-600">Этаж</th>
                <th class="text-left px-5 py-3 font-semibold text-gray-600">Тип</th>
                <th class="text-left px-5 py-3 font-semibold text-gray-600">Статус</th>
                <th class="text-left px-5 py-3 font-semibold text-gray-600">Заметки</th>
                <th class="text-right px-5 py-3 font-semibold text-gray-600">Действия</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse($rooms as $floor => $floorRooms)
                {{-- Floor header row --}}
                <tr class="bg-gray-50">
                    <td colspan="6" class="px-5 py-2 text-xs font-semibold text-gray-500 uppercase tracking-wider">
                        @if($floor)
                            Этаж {{ $floor }}
                        @else
                            Этаж не указан
                        @endif
                        <span class="ml-1 font-normal text-gray-400">({{ $floorRooms->count() }})</span>
                    </td>
                </tr>
                @foreach($floorRooms as $room)
                    <tr class="hover:bg-gray-50">
                        <td class="px-5 py-4 font-medium text-gray-900">{{ $room->number }}</td>
                        <td class="px-5 py-4 text-gray-600">{{ $room->floor ?? '—' }}</td>
                        <td class="px-5 py-4 text-gray-700">{{ $room->roomType->name }}</td>
                        <td class="px-5 py-4">
                            <x-status-badge :status="$room->status" />
                        </td>
                        <td class="px-5 py-4 text-gray-500 max-w-xs truncate">
                            {{ $room->notes ? \Illuminate\Support\Str::limit($room->notes, 60) : '—' }}
                        </td>
                        <td class="px-5 py-4 text-right">
                            <a href="{{ route('rooms.edit', $room) }}"
                               class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-blue-700 bg-blue-50 rounded-lg hover:bg-blue-100 transition">
                                Изменить
                            </a>
                        </td>
                    </tr>
                @endforeach
            @empty
                <tr>
                    <td colspan="6" class="px-5 py-10 text-center text-gray-400">
                        Номера не найдены
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
