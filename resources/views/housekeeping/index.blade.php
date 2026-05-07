@extends('layouts.app')

@section('title', 'Горничные')

@section('content')
@php
    $dirtyRoomIds = $rooms->flatten()->where('status', \App\Enums\RoomStatus::Dirty)->pluck('id')->values()->toArray();

    $tileBg = [
        \App\Enums\RoomStatus::Available->value   => 'bg-emerald-50 dark:bg-emerald-900/20 border-emerald-300 dark:border-emerald-700 hover:border-emerald-500',
        \App\Enums\RoomStatus::Occupied->value     => 'bg-red-50 dark:bg-red-900/20 border-red-300 dark:border-red-700 hover:border-red-500',
        \App\Enums\RoomStatus::Dirty->value        => 'bg-orange-50 dark:bg-orange-900/20 border-orange-300 dark:border-orange-700 hover:border-orange-500',
        \App\Enums\RoomStatus::Cleaning->value     => 'bg-amber-50 dark:bg-amber-900/20 border-amber-300 dark:border-amber-700 hover:border-amber-500',
        \App\Enums\RoomStatus::Inspected->value    => 'bg-blue-50 dark:bg-blue-900/20 border-blue-300 dark:border-blue-700 hover:border-blue-500',
        \App\Enums\RoomStatus::Maintenance->value  => 'bg-slate-100 dark:bg-slate-700/50 border-slate-300 dark:border-slate-600 hover:border-slate-500',
    ];
    $tileText = [
        \App\Enums\RoomStatus::Available->value   => 'text-emerald-800 dark:text-emerald-300',
        \App\Enums\RoomStatus::Occupied->value     => 'text-red-800 dark:text-red-300',
        \App\Enums\RoomStatus::Dirty->value        => 'text-orange-800 dark:text-orange-300',
        \App\Enums\RoomStatus::Cleaning->value     => 'text-amber-800 dark:text-amber-300',
        \App\Enums\RoomStatus::Inspected->value    => 'text-blue-800 dark:text-blue-300',
        \App\Enums\RoomStatus::Maintenance->value  => 'text-slate-600 dark:text-slate-300',
    ];
    $dotColor = [
        \App\Enums\RoomStatus::Available->value   => 'bg-emerald-500',
        \App\Enums\RoomStatus::Occupied->value     => 'bg-red-500',
        \App\Enums\RoomStatus::Dirty->value        => 'bg-orange-400',
        \App\Enums\RoomStatus::Cleaning->value     => 'bg-amber-500',
        \App\Enums\RoomStatus::Inspected->value    => 'bg-blue-500',
        \App\Enums\RoomStatus::Maintenance->value  => 'bg-slate-400',
    ];
    $statusActions = [
        \App\Enums\RoomStatus::Dirty->value       => [['status' => 'cleaning',   'label' => 'Начать уборку',        'color' => 'bg-amber-600 hover:bg-amber-700']],
        \App\Enums\RoomStatus::Cleaning->value    => [
            ['status' => 'inspected', 'label' => 'Отметить проверенным',  'color' => 'bg-blue-600 hover:bg-blue-700'],
            ['status' => 'available', 'label' => 'Отметить свободным',    'color' => 'bg-emerald-600 hover:bg-emerald-700'],
        ],
        \App\Enums\RoomStatus::Inspected->value   => [['status' => 'available',  'label' => 'Отметить свободным',   'color' => 'bg-emerald-600 hover:bg-emerald-700']],
        \App\Enums\RoomStatus::Maintenance->value => [['status' => 'available',  'label' => 'Завершить ремонт',     'color' => 'bg-blue-600 hover:bg-blue-700']],
    ];
@endphp

<div
    x-data="{
        selected: [],
        dirtyIds: @js($dirtyRoomIds),
        selectAll() { this.selected = [...this.dirtyIds]; },
        clearAll() { this.selected = []; },
        toggle(id) {
            const idx = this.selected.indexOf(id);
            if (idx === -1) this.selected.push(id);
            else this.selected.splice(idx, 1);
        },
        async bulkMarkCleaning() {
            if (this.selected.length === 0) return;
            const res = await fetch('{{ route('housekeeping.bulk') }}', {
                method: 'PATCH',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content },
                body: JSON.stringify({ room_ids: this.selected, status: 'cleaning' }),
            });
            if (res.ok) { window.location.reload(); }
            else { const d = await res.json(); alert(d.error ?? 'Ошибка'); }
        }
    }"
>

{{-- Overdue alert --}}
@if($overdueBookings->isNotEmpty())
<div class="mb-4 flex flex-wrap items-center gap-2 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-xl px-4 py-2.5">
    <span class="w-2 h-2 rounded-full bg-red-500 animate-pulse flex-shrink-0"></span>
    <span class="text-sm font-semibold text-red-800 dark:text-red-300 mr-1">Просрочен выезд:</span>
    @foreach($overdueBookings as $ob)
    <a href="{{ route('bookings.show', $ob) }}"
       title="{{ $ob->guest->fullName }} — выезд {{ $ob->check_out_date->format('d.m') }}"
       class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-red-100 dark:bg-red-900/40 border border-red-300 dark:border-red-700 text-xs font-bold text-red-700 dark:text-red-300 hover:bg-red-200 dark:hover:bg-red-900/60 transition-colors">
        № {{ $ob->room->number }}
        <span class="font-normal opacity-75">· {{ $ob->check_out_date->diffInDays(\Carbon\Carbon::today()) }}д</span>
    </a>
    @endforeach
</div>
@endif

{{-- Header --}}
<div class="flex items-center justify-between mb-5">
    <div>
        <h1 class="text-2xl font-bold text-slate-900 dark:text-slate-100">Горничные</h1>
        <p class="text-xs text-slate-400 dark:text-slate-500 mt-0.5">Нажмите на номер чтобы изменить статус</p>
    </div>
    <template x-if="dirtyIds.length > 0">
        <button @click="selectAll()" x-show="selected.length === 0"
                class="inline-flex items-center gap-2 px-3 py-2 text-sm font-semibold text-orange-700 dark:text-orange-300 bg-orange-50 dark:bg-orange-900/30 border border-orange-200 dark:border-orange-700 rounded-lg hover:bg-orange-100 dark:hover:bg-orange-900/50 transition-colors">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4 flex-shrink-0"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
            <span class="hidden sm:inline">Все грязные ({{ count($dirtyRoomIds) }})</span>
            <span class="sm:hidden">Грязные ({{ count($dirtyRoomIds) }})</span>
        </button>
    </template>
</div>

{{-- Status filter pills --}}
@php
    $tabs = [
        null          => 'Все',
        'available'   => \App\Enums\RoomStatus::Available->label(),
        'occupied'    => \App\Enums\RoomStatus::Occupied->label(),
        'dirty'       => \App\Enums\RoomStatus::Dirty->label(),
        'cleaning'    => \App\Enums\RoomStatus::Cleaning->label(),
        'inspected'   => \App\Enums\RoomStatus::Inspected->label(),
        'maintenance' => \App\Enums\RoomStatus::Maintenance->label(),
    ];
    $tabCounts = [
        null          => $counts['all'],
        'available'   => $counts['available'],
        'occupied'    => $counts['occupied'],
        'dirty'       => $counts['dirty'],
        'cleaning'    => $counts['cleaning'],
        'inspected'   => $counts['inspected'],
        'maintenance' => $counts['maintenance'],
    ];
@endphp

<div class="flex flex-wrap gap-2 mb-5">
    @foreach($tabs as $value => $label)
        @php
            $isActive = ($statusFilter ?? null) === ($value ?: null);
            $url = $value ? route('housekeeping.index', ['status' => $value]) : route('housekeeping.index');
        @endphp
        <a href="{{ $url }}"
           class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-sm font-semibold transition-colors
                  {{ $isActive
                      ? 'bg-blue-600 text-white shadow-sm'
                      : 'bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700' }}">
            {{ $label }}
            <span class="inline-flex items-center justify-center min-w-[1.25rem] h-4 px-1 rounded-full text-xs
                         {{ $isActive ? 'bg-blue-500 text-white' : 'bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300' }}">
                {{ $tabCounts[$value] ?? 0 }}
            </span>
        </a>
    @endforeach
</div>

{{-- Legend --}}
<div class="flex flex-wrap gap-3 mb-5 text-xs text-slate-500 dark:text-slate-400">
    @foreach([
        ['Available', 'bg-emerald-500', \App\Enums\RoomStatus::Available->label()],
        ['Occupied',  'bg-red-500',     \App\Enums\RoomStatus::Occupied->label()],
        ['Dirty',     'bg-orange-400',  \App\Enums\RoomStatus::Dirty->label()],
        ['Cleaning',  'bg-amber-500',   \App\Enums\RoomStatus::Cleaning->label()],
        ['Inspected', 'bg-blue-500',    \App\Enums\RoomStatus::Inspected->label()],
        ['Maintenance','bg-slate-400',  \App\Enums\RoomStatus::Maintenance->label()],
    ] as [$key, $dot, $lbl])
    <span class="flex items-center gap-1.5">
        <span class="w-2.5 h-2.5 rounded-full {{ $dot }} flex-shrink-0"></span>{{ $lbl }}
    </span>
    @endforeach
</div>

{{-- Room tiles grouped by floor --}}
@if($rooms->isEmpty())
    <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm py-16 text-center">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-12 h-12 mx-auto text-slate-300 dark:text-slate-600 mb-3"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25"/></svg>
        <p class="text-slate-400 dark:text-slate-500 text-sm">Нет номеров для отображения</p>
    </div>
@else
    @foreach($rooms as $floor => $floorRooms)
    <div class="mb-6">
        {{-- Floor header --}}
        <div class="flex items-center gap-3 mb-2.5">
            <span class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Этаж {{ $floor }}</span>
            <span class="flex-1 h-px bg-slate-200 dark:bg-slate-700"></span>
            <span class="text-xs text-slate-400 dark:text-slate-500">{{ $floorRooms->count() }} ном.</span>
        </div>

        {{-- Compact tile grid --}}
        <div class="grid grid-cols-5 sm:grid-cols-8 md:grid-cols-10 lg:grid-cols-12 gap-1.5">
            @foreach($floorRooms as $room)
            @php
                $isUrgent   = $room->status === \App\Enums\RoomStatus::Dirty && isset($urgentRoomIds[$room->id]);
                $isDirty    = $room->status === \App\Enums\RoomStatus::Dirty;
                $isOccupied = $room->status === \App\Enums\RoomStatus::Occupied;
                $booking    = $room->bookings->first();
                $sv         = $room->status->value;
                $actions    = $statusActions[$sv] ?? [];
                $tooltip    = $isOccupied && $booking
                    ? $booking->guest->fullName . ' — до ' . $booking->check_out_date->format('d.m')
                    : $room->status->label();
            @endphp

            <div x-data="{ open: false }" class="relative">

                {{-- Checkbox for dirty rooms --}}
                @if($isDirty)
                <div class="absolute top-0.5 left-0.5 z-10" @click.stop>
                    <input type="checkbox"
                           :checked="selected.includes({{ $room->id }})"
                           @click.stop="toggle({{ $room->id }})"
                           class="w-3.5 h-3.5 rounded border-slate-300 text-orange-500 focus:ring-orange-400 cursor-pointer">
                </div>
                @endif

                {{-- Tile button --}}
                <button
                    type="button"
                    @click="open = true"
                    title="{{ $tooltip }}"
                    :class="selected.includes({{ $room->id }}) ? 'ring-2 ring-orange-400' : '{{ $isUrgent ? 'ring-2 ring-red-400' : '' }}'"
                    class="relative w-full h-14 flex flex-col items-center justify-center rounded-lg border-2 transition-all cursor-pointer select-none
                           {{ $tileBg[$sv] ?? 'bg-slate-100 border-slate-300' }}"
                >
                    {{-- Urgent pulse dot --}}
                    @if($isUrgent)
                    <span class="absolute top-1 right-1 w-2 h-2 rounded-full bg-red-500 animate-pulse"></span>
                    @endif

                    {{-- Status dot --}}
                    <span class="w-1.5 h-1.5 rounded-full mb-0.5 {{ $dotColor[$sv] ?? 'bg-slate-400' }}"></span>

                    {{-- Room number --}}
                    <span class="text-sm font-bold leading-none {{ $tileText[$sv] ?? 'text-slate-700' }}">
                        {{ $room->number }}
                    </span>
                </button>

                {{-- Modal --}}
                <div
                    x-show="open"
                    x-cloak
                    @click.self="open = false"
                    class="fixed inset-0 bg-black/40 flex items-center justify-center z-50 p-4"
                >
                    <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-2xl w-full max-w-sm p-6" @click.stop>
                        <div class="flex justify-between items-center mb-4">
                            <div>
                                <h3 class="text-lg font-bold text-slate-900 dark:text-slate-100">Номер {{ $room->number }}</h3>
                                <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">{{ $room->roomType->name }}</p>
                            </div>
                            <button @click="open = false" class="w-8 h-8 flex items-center justify-center rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg>
                            </button>
                        </div>

                        <div class="flex items-center gap-2 mb-4">
                            <span class="text-sm text-slate-500 dark:text-slate-400">Статус:</span>
                            <x-status-badge :status="$room->status" />
                        </div>

                        @if($isOccupied && $booking)
                        <div class="bg-slate-50 dark:bg-slate-700/50 rounded-xl p-3 mb-4">
                            <p class="text-sm font-semibold text-slate-900 dark:text-slate-100">{{ $booking->guest->fullName }}</p>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Выезд: {{ $booking->check_out_date->format('d.m.Y') }}</p>
                        </div>
                        <a href="{{ route('bookings.show', $booking) }}"
                           class="inline-flex items-center justify-center gap-2 w-full bg-blue-600 hover:bg-blue-700 text-white px-4 py-2.5 rounded-lg text-sm font-semibold transition-colors mb-2">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 0 0 3 8.25v10.5A2.25 2.25 0 0 0 5.25 21h10.5A2.25 2.25 0 0 0 18 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25"/></svg>
                            Открыть бронирование
                        </a>
                        @endif

                        @foreach($actions as $action)
                        <form method="POST" action="{{ route('housekeeping.update', $room) }}" class="mt-2">
                            @csrf @method('PATCH')
                            <input type="hidden" name="status" value="{{ $action['status'] }}">
                            <button type="submit"
                                    class="inline-flex items-center justify-center gap-2 w-full text-white px-4 py-2.5 rounded-lg text-sm font-semibold transition-colors {{ $action['color'] }}">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                                {{ $action['label'] }}
                            </button>
                        </form>
                        @endforeach

                        {{-- Assign staff --}}
                        @if(in_array($sv, ['cleaning', 'dirty']) && $staff->isNotEmpty())
                        <div class="mt-3 pt-3 border-t border-slate-100 dark:border-slate-700">
                            <p class="text-xs font-semibold text-slate-500 dark:text-slate-400 mb-2">Назначить сотрудника</p>
                            <form method="POST" action="{{ route('housekeeping.update', $room) }}" x-data>
                                @csrf @method('PATCH')
                                <input type="hidden" name="action" value="assign">
                                <select name="assigned_to"
                                    @change="$el.closest('form').submit()"
                                    class="w-full border border-slate-300 dark:border-slate-600 rounded-lg px-3 py-2 text-sm bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-500">
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

            </div>{{-- /x-data tile --}}
            @endforeach
        </div>
    </div>
    @endforeach
@endif

{{-- Bulk action bar --}}
<div x-show="selected.length > 0" x-cloak
     class="fixed bottom-6 left-1/2 -translate-x-1/2 z-50 flex items-center gap-3 bg-slate-900 dark:bg-slate-700 text-white px-5 py-3 rounded-2xl shadow-2xl border border-slate-700 dark:border-slate-600">
    <span class="text-sm font-medium">
        Выбрано: <span x-text="selected.length" class="font-bold text-orange-400"></span>
    </span>
    <button @click="bulkMarkCleaning()"
            class="inline-flex items-center gap-2 px-4 py-2 bg-amber-500 hover:bg-amber-400 text-white text-sm font-semibold rounded-lg transition-colors">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
        Начать уборку
    </button>
    <button @click="clearAll()"
            class="inline-flex items-center gap-2 px-3 py-2 bg-slate-700 dark:bg-slate-600 hover:bg-slate-600 dark:hover:bg-slate-500 text-slate-300 text-sm font-semibold rounded-lg transition-colors">
        Отмена
    </button>
</div>

</div>{{-- end x-data --}}
@endsection
