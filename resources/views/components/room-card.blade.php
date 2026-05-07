@props(['room', 'activeBooking' => null, 'staff' => collect()])

@php
    $dotColor = match($room->status) {
        \App\Enums\RoomStatus::Available   => 'bg-emerald-500',
        \App\Enums\RoomStatus::Occupied    => 'bg-red-500',
        \App\Enums\RoomStatus::Dirty       => 'bg-orange-400',
        \App\Enums\RoomStatus::Cleaning    => 'bg-amber-500',
        \App\Enums\RoomStatus::Inspected   => 'bg-blue-500',
        \App\Enums\RoomStatus::Maintenance => 'bg-slate-400',
    };
    $borderColor = match($room->status) {
        \App\Enums\RoomStatus::Available   => 'border-emerald-200 hover:border-emerald-400 hover:shadow-md',
        \App\Enums\RoomStatus::Occupied    => 'border-red-200 hover:border-red-400 hover:shadow-md',
        \App\Enums\RoomStatus::Dirty       => 'border-orange-200 hover:border-orange-400 hover:shadow-md',
        \App\Enums\RoomStatus::Cleaning    => 'border-amber-200 hover:border-amber-400 hover:shadow-md',
        \App\Enums\RoomStatus::Inspected   => 'border-blue-200 hover:border-blue-400 hover:shadow-md',
        \App\Enums\RoomStatus::Maintenance => 'border-slate-200 hover:border-slate-400 hover:shadow-md',
    };
@endphp

<div
    x-data="{ showModal: false, showTooltip: false }"
    class="relative bg-white border-2 rounded-xl p-4 cursor-pointer transition-all duration-200 select-none {{ $borderColor }}"
    @click="showModal = true"
    @mouseenter="showTooltip = true"
    @mouseleave="showTooltip = false"
>
    {{-- Hover preview for occupied rooms --}}
    @if($room->status === \App\Enums\RoomStatus::Occupied && $activeBooking)
    <div
        x-show="showTooltip"
        x-cloak
        class="absolute bottom-full left-0 mb-2 w-56 bg-slate-900 text-white text-xs rounded-xl p-3 z-10 shadow-xl"
    >
        <p class="font-semibold">{{ $activeBooking->guest->fullName }}</p>
        <p class="text-slate-300 mt-1">До: {{ $activeBooking->check_out_date->format('d M Y') }}</p>
    </div>
    @endif

    <div class="text-center">
        <div class="flex justify-center mb-2">
            <span class="inline-block w-2.5 h-2.5 rounded-full {{ $dotColor }}"></span>
        </div>
        <p class="text-xl font-bold text-slate-900">{{ $room->number }}</p>
        <p class="text-xs text-slate-500 mt-0.5">{{ $room->roomType->name }}</p>
        <div class="mt-2 flex justify-center">
            <x-status-badge :status="$room->status" />
        </div>
    </div>

    {{-- Modal --}}
    <div
        x-show="showModal"
        x-cloak
        @click.stop
        class="fixed inset-0 bg-black/40 flex items-center justify-center z-50 p-4"
        @click.self="showModal = false"
    >
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm p-6">
            <div class="flex justify-between items-center mb-5">
                <div>
                    <h3 class="text-lg font-bold text-slate-900">Номер {{ $room->number }}</h3>
                    <p class="text-sm text-slate-500 mt-0.5">{{ $room->roomType->name }}</p>
                </div>
                <button @click="showModal = false" class="w-8 h-8 flex items-center justify-center rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <div class="flex items-center gap-2 mb-5">
                <span class="text-sm text-slate-500">Статус:</span>
                <x-status-badge :status="$room->status" />
            </div>

            @if($room->status === \App\Enums\RoomStatus::Occupied && $activeBooking)
                <div class="bg-slate-50 rounded-xl p-4 mb-4">
                    <p class="text-sm font-semibold text-slate-900">{{ $activeBooking->guest->fullName }}</p>
                    <p class="text-sm text-slate-500 mt-1">Выезд: {{ $activeBooking->check_out_date->format('d.m.Y') }}</p>
                </div>
                <a href="{{ route('bookings.show', $activeBooking) }}"
                   class="inline-flex items-center justify-center gap-2 w-full bg-blue-600 hover:bg-blue-700 text-white px-4 py-2.5 rounded-lg text-sm font-semibold transition-colors mb-2">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 0 0 3 8.25v10.5A2.25 2.25 0 0 0 5.25 21h10.5A2.25 2.25 0 0 0 18 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25"/></svg>
                    Открыть бронирование
                </a>
            @endif

            @php
                $statusActions = [
                    \App\Enums\RoomStatus::Dirty->value       => [['status' => 'cleaning',   'label' => 'Начать уборку',    'color' => 'bg-amber-600 hover:bg-amber-700']],
                    \App\Enums\RoomStatus::Cleaning->value    => [
                        ['status' => 'inspected', 'label' => 'Отметить проверенным', 'color' => 'bg-blue-600 hover:bg-blue-700'],
                        ['status' => 'available', 'label' => 'Отметить свободным',   'color' => 'bg-emerald-600 hover:bg-emerald-700'],
                    ],
                    \App\Enums\RoomStatus::Inspected->value   => [['status' => 'available',  'label' => 'Отметить свободным',  'color' => 'bg-emerald-600 hover:bg-emerald-700']],
                    \App\Enums\RoomStatus::Maintenance->value => [['status' => 'available',  'label' => 'Завершить ремонт',    'color' => 'bg-blue-600 hover:bg-blue-700']],
                ];
                $actions = $statusActions[$room->status->value] ?? [];
            @endphp

            @foreach($actions as $action)
            <form method="POST" action="{{ route('housekeeping.update', $room) }}" @click.stop class="mt-2">
                @csrf @method('PATCH')
                <input type="hidden" name="status" value="{{ $action['status'] }}">
                <button type="submit" class="inline-flex items-center justify-center gap-2 w-full text-white px-4 py-2.5 rounded-lg text-sm font-semibold transition-colors {{ $action['color'] }}">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                    {{ $action['label'] }}
                </button>
            </form>
            @endforeach

            {{-- Assign staff dropdown (for cleaning/dirty rooms) --}}
            @if(in_array($room->status->value, ['cleaning', 'dirty']) && $staff->isNotEmpty())
            <div class="mt-3 pt-3 border-t border-slate-100" @click.stop>
                <p class="text-xs font-semibold text-slate-500 mb-2">Назначить сотрудника</p>
                <form method="POST" action="{{ route('housekeeping.update', $room) }}" x-data>
                    @csrf @method('PATCH')
                    <input type="hidden" name="action" value="assign">
                    <select name="assigned_to"
                        @change="$el.closest('form').submit()"
                        class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">— Не назначен —</option>
                        @foreach($staff as $member)
                            <option value="{{ $member->id }}" {{ $room->assigned_to == $member->id ? 'selected' : '' }}>
                                {{ $member->name }}
                            </option>
                        @endforeach
                    </select>
                </form>
            </div>
            @endif
        </div>
    </div>
</div>
