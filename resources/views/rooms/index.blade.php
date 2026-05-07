@extends('layouts.app')

@section('title', 'Номера')

@section('content')
<div x-data="roomsPage({{ Js::from(request('status', '')) }}, {{ Js::from(request('search', '')) }})">

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
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-slate-900 dark:text-slate-100">
            Номера
            <span class="ml-2 text-base font-normal text-slate-400 dark:text-slate-500">({{ $rooms->flatten()->count() }})</span>
        </h1>
        <a href="{{ route('rooms.create') }}"
           class="inline-flex items-center gap-2 px-3 py-2 bg-blue-600 text-white text-sm font-semibold rounded-lg hover:bg-blue-700 transition-colors shadow-sm"
           title="Добавить номер">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4 flex-shrink-0"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
            <span class="hidden sm:inline">Добавить номер</span>
        </a>
    </div>

    {{-- Search + Status filters --}}
    <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm p-4 mb-6">
        <div class="flex flex-col sm:flex-row gap-3">
            {{-- Search --}}
            <form method="GET" action="{{ route('rooms.index') }}" class="flex-1 flex gap-2" x-data="{ s: initSearch }">
                <div class="relative flex-1">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"/></svg>
                    <input type="text" name="search" x-model="s" placeholder="Номер или гость..."
                           class="w-full pl-9 pr-8 py-2 text-sm border border-slate-200 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    <template x-if="s.length > 0">
                        <button type="button" @click="s = ''; $root.parentElement.querySelector('input').value = ''; $root.parentElement.submit();"
                                class="absolute right-2 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg>
                        </button>
                    </template>
                </div>
                <input type="hidden" name="status" :value="initStatus">
                <button type="submit" class="px-4 py-2 text-xs font-semibold bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-200 rounded-lg hover:bg-slate-200 dark:hover:bg-slate-600 transition-colors">Найти</button>
            </form>

            {{-- Status filter pills --}}
            <div class="flex items-center gap-1.5 flex-wrap">
                <a href="{{ route('rooms.index') }}"
                   class="px-3 py-1.5 text-xs font-semibold rounded-full transition-colors {{ !request('status') ? 'bg-slate-900 dark:bg-white text-white dark:text-slate-900' : 'bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-600' }}">
                    Все {{ $counts->sum(fn($c) => (int)$c) }}
                </a>
                @foreach([
                    ['value' => 'available',   'label' => 'Свободен',   'dot' => 'bg-emerald-500'],
                    ['value' => 'occupied',    'label' => 'Занят',      'dot' => 'bg-red-500'],
                    ['value' => 'dirty',       'label' => 'Грязный',    'dot' => 'bg-orange-500'],
                    ['value' => 'cleaning',    'label' => 'Уборка',     'dot' => 'bg-yellow-400'],
                    ['value' => 'inspected',   'label' => 'Проверен',   'dot' => 'bg-blue-500'],
                    ['value' => 'maintenance', 'label' => 'Ремонт',     'dot' => 'bg-gray-400'],
                ] as $f)
                @php $cnt = $counts->get($f['value'], 0); @endphp
                <a href="{{ route('rooms.index', array_filter(['status' => $f['value'], 'search' => request('search')])) }}"
                   class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold rounded-full transition-colors {{ request('status') === $f['value'] ? 'bg-slate-900 dark:bg-white text-white dark:text-slate-900' : 'bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-600' }}">
                    <span class="w-2 h-2 rounded-full {{ $f['dot'] }}"></span>
                    {{ $f['label'] }} {{ $cnt }}
                </a>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Rooms grouped by floor --}}
    @forelse($rooms as $floor => $floorRooms)

    {{-- Floor group --}}
    <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden {{ !$loop->first ? 'mt-4' : '' }}">
        {{-- Floor header --}}
        <div class="px-4 py-2.5 bg-slate-50 dark:bg-slate-900/40 border-b border-slate-100 dark:border-slate-700 flex items-center gap-2">
            <span class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                {{ $floor ? 'Этаж ' . $floor : 'Этаж не указан' }}
            </span>
            <span class="text-xs text-slate-400 dark:text-slate-500">&middot; {{ $floorRooms->count() }}</span>
        </div>

        {{-- Room rows --}}
        <div class="divide-y divide-slate-100 dark:divide-slate-700">
        @foreach($floorRooms as $room)
        @php
            $openCount    = $room->maintenanceRequests()->whereIn('status', ['open', 'in_progress'])->count();
            $latestTicket = $openCount ? $room->maintenanceRequests()->whereIn('status', ['open', 'in_progress'])->orderByDesc('created_at')->first() : null;
            $images       = $room->imageUrls();
            $statusDot    = match($room->status->color()) {
                'green'  => 'bg-emerald-500',
                'red'    => 'bg-red-500',
                'orange' => 'bg-orange-500',
                'yellow' => 'bg-yellow-400',
                'blue'   => 'bg-blue-500',
                default  => 'bg-slate-400',
            };
            $activeBooking = $room->bookings->first();
            $isOverdue     = isset($overdueBookings[$room->id]);
        @endphp

        <div class="flex items-center gap-3 px-4 py-2.5 hover:bg-slate-50 dark:hover:bg-slate-700/40 transition-colors {{ $isOverdue ? 'border-l-2 border-red-400' : '' }}">

            {{-- Thumbnail --}}
            <div class="w-12 h-10 rounded-md overflow-hidden flex-shrink-0 bg-slate-100 dark:bg-slate-700">
                @if(count($images))
                <img src="{{ $images[0] }}" class="w-full h-full object-cover" alt="">
                @else
                <div class="w-full h-full flex items-center justify-center text-slate-400 dark:text-slate-500">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75"/></svg>
                </div>
                @endif
            </div>

            {{-- Room info --}}
            <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2">
                    <span class="font-bold text-slate-900 dark:text-slate-100 text-sm">{{ $room->number }}</span>
                    <span class="w-1.5 h-1.5 rounded-full flex-shrink-0 {{ $statusDot }}"></span>
                    @if($isOverdue)
                    <span class="inline-flex items-center gap-1 px-1.5 py-0.5 text-[9px] font-bold text-white bg-red-500 rounded-full">
                        <span class="w-1 h-1 rounded-full bg-white animate-pulse"></span>Просрочен
                    </span>
                    @endif
                    @if($openCount > 0)
                    <a href="{{ route('maintenance.show', $latestTicket) }}"
                       class="inline-flex items-center justify-center min-w-[1.1rem] h-4 px-1 text-[9px] font-bold text-white bg-red-500 rounded-full hover:bg-red-600"
                       title="{{ $latestTicket?->title ?? '' }}">{{ $openCount }}</a>
                    @endif
                </div>
                <div class="flex items-center gap-2 mt-0.5">
                    <p class="text-xs text-slate-400 dark:text-slate-500 truncate">{{ $room->roomType->name }}</p>
                    @if($activeBooking)
                    <span class="text-slate-300 dark:text-slate-600 text-xs">·</span>
                    <a href="{{ route('bookings.show', $activeBooking) }}"
                       class="text-xs text-slate-600 dark:text-slate-300 hover:text-blue-600 dark:hover:text-blue-400 font-medium truncate max-w-[120px]"
                       title="Открыть бронирование">{{ $activeBooking->guest->fullName }}</a>
                    <span class="text-[10px] text-slate-400">до {{ $activeBooking->check_out_date->format('d.m') }}</span>
                    @endif
                </div>
            </div>

            {{-- Photo count --}}
            @if(count($images) > 1)
            <span class="text-[10px] text-slate-400 dark:text-slate-500 flex-shrink-0">{{ count($images) }}ф</span>
            @endif

            {{-- Actions --}}
            <div class="flex items-center gap-1 flex-shrink-0">
                @if($activeBooking)
                <form method="POST" action="{{ route('bookings.status', $activeBooking) }}">
                    @csrf
                    <input type="hidden" name="transition" value="checked_out">
                    <button type="submit"
                            onclick="return confirm('Выселить гостя из номера {{ $room->number }}?')"
                            class="inline-flex items-center gap-1 px-2 py-1.5 text-[11px] font-semibold text-slate-600 dark:text-slate-300 bg-slate-100 dark:bg-slate-700 rounded-lg hover:bg-slate-200 dark:hover:bg-slate-600 transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-3.5 h-3.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15m3 0 3-3m0 0-3-3m3 3H9"/></svg>
                        Выселить
                    </button>
                </form>
                @endif
                <a href="{{ route('room-portal.show', $room->qr_token) }}" target="_blank"
                   title="Портал"
                   class="w-7 h-7 flex items-center justify-center text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 rounded transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-3.5 h-3.5"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 0 0 3 8.25v10.5A2.25 2.25 0 0 0 5.25 21h10.5A2.25 2.25 0 0 0 18 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25"/></svg>
                </a>
                <a href="{{ route('rooms.qr', $room) }}" download="room-{{ $room->number }}-qr.svg"
                   title="QR"
                   class="w-7 h-7 flex items-center justify-center text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 rounded transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-3.5 h-3.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0 1 3.75 9.375v-4.5ZM3.75 14.625c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5a1.125 1.125 0 0 1-1.125-1.125v-4.5ZM13.5 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0 1 13.5 9.375v-4.5Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 6.75h.75v.75h-.75v-.75ZM6.75 16.5h.75v.75h-.75v-.75ZM16.5 6.75h.75v.75h-.75v-.75ZM13.5 13.5h.75v.75h-.75v-.75ZM13.5 19.5h.75v.75h-.75v-.75ZM19.5 13.5h.75v.75h-.75v-.75ZM19.5 19.5h.75v.75h-.75v-.75ZM16.5 16.5h.75v.75h-.75v-.75Z"/></svg>
                </a>
                <a href="{{ route('rooms.edit', $room) }}"
                   class="inline-flex items-center gap-1 px-2.5 py-1.5 text-xs font-semibold text-blue-700 dark:text-blue-300 bg-blue-50 dark:bg-blue-900/30 rounded-lg hover:bg-blue-100 dark:hover:bg-blue-900/50 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-3 h-3"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125"/></svg>
                    Изменить
                </a>
            </div>
        </div>
        @endforeach
        </div>
    </div>

    @empty
    <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 py-16 text-center">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-10 h-10 mx-auto text-slate-300 dark:text-slate-600 mb-3"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75"/></svg>
        <p class="text-slate-400 dark:text-slate-500 text-sm">Номера не найдены</p>
    </div>
    @endforelse

</div>

<script>
function roomsPage(initStatus, initSearch) {
    return { initStatus, initSearch };
}
</script>
@endsection
