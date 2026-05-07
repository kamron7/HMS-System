@extends('layouts.app')
@section('title', 'Таймлайн')

@php
$statusColors = [
    'inquiry'     => 'bg-purple-400 hover:bg-purple-500 text-white',
    'pending'     => 'bg-amber-400 hover:bg-amber-500 text-white',
    'confirmed'   => 'bg-blue-500 hover:bg-blue-600 text-white',
    'checked_in'  => 'bg-emerald-500 hover:bg-emerald-600 text-white',
    'checked_out' => 'bg-slate-300 hover:bg-slate-400 text-slate-700 dark:text-slate-800',
];
$prevStart = $start->copy()->subDays(7)->format('Y-m-d');
$nextStart = $start->copy()->addDays(7)->format('Y-m-d');
$todayStr  = today()->toDateString();
$roomColW  = 160;
$totalW    = $roomColW + $days * $cellW;
@endphp

@section('content')

{{-- Header --}}
<div class="flex items-center justify-between mb-5 flex-wrap gap-3">
    <div>
        <h1 class="text-2xl font-bold text-slate-900 dark:text-slate-100">Таймлайн</h1>
        <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">
            {{ $start->translatedFormat('d M') }} — {{ $start->copy()->addDays($days - 1)->translatedFormat('d M Y') }}
        </p>
    </div>
    <div class="flex items-center gap-2">
        <a href="{{ route('bookings.timeline', ['start' => $prevStart]) }}"
           class="inline-flex items-center gap-1.5 px-3 py-2 text-sm font-semibold text-slate-600 dark:text-slate-300 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors shadow-sm">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5"/></svg>
            <span class="hidden sm:inline">7 дней</span>
        </a>
        <a href="{{ route('bookings.timeline') }}"
           class="px-3 py-2 text-sm font-semibold rounded-lg transition-colors {{ $start->isToday() ? 'bg-blue-600 text-white' : 'bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700' }} shadow-sm">
            Сегодня
        </a>
        <a href="{{ route('bookings.timeline', ['start' => $nextStart]) }}"
           class="inline-flex items-center gap-1.5 px-3 py-2 text-sm font-semibold text-slate-600 dark:text-slate-300 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors shadow-sm">
            <span class="hidden sm:inline">7 дней</span>
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5"/></svg>
        </a>
        <a href="{{ route('bookings.create') }}"
           class="inline-flex items-center gap-1.5 px-3 py-2 bg-blue-600 text-white text-sm font-semibold rounded-lg hover:bg-blue-700 transition-colors shadow-sm">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
            <span class="hidden sm:inline">Бронирование</span>
        </a>
    </div>
</div>

{{-- Legend --}}
<div class="flex flex-wrap items-center gap-3 mb-4">
    @foreach(['inquiry' => 'Запрос', 'pending' => 'Ожидание', 'confirmed' => 'Подтверждено', 'checked_in' => 'Заселён', 'checked_out' => 'Выселен'] as $status => $label)
    <div class="flex items-center gap-1.5">
        <span class="w-3 h-3 rounded-sm {{ explode(' ', $statusColors[$status])[0] }}"></span>
        <span class="text-xs text-slate-500 dark:text-slate-400">{{ $label }}</span>
    </div>
    @endforeach
</div>

{{-- Gantt --}}
<div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <div style="min-width: {{ $totalW }}px">

            {{-- Date header --}}
            <div class="flex border-b border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900/40 sticky top-0 z-30">
                {{-- Room column header --}}
                <div class="sticky left-0 z-30 bg-slate-50 dark:bg-slate-900/40 border-r border-slate-200 dark:border-slate-700 flex items-center px-4"
                     style="width:{{ $roomColW }}px; min-width:{{ $roomColW }}px; height:52px">
                    <span class="text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider">Номер</span>
                </div>
                {{-- Day columns --}}
                @foreach($dates as $date)
                @php $isToday = $date->toDateString() === $todayStr; $isWeekend = $date->isWeekend(); @endphp
                <div class="border-r border-slate-100 dark:border-slate-700 text-center flex flex-col items-center justify-center flex-shrink-0
                            {{ $isToday ? 'bg-blue-50 dark:bg-blue-900/20' : ($isWeekend ? 'bg-slate-100/60 dark:bg-slate-900/20' : '') }}"
                     style="width:{{ $cellW }}px; min-width:{{ $cellW }}px; height:52px">
                    <span class="text-[10px] font-semibold uppercase {{ $isToday ? 'text-blue-500' : 'text-slate-400 dark:text-slate-500' }}">
                        {{ $date->translatedFormat('D') }}
                    </span>
                    <span class="text-sm font-bold leading-none mt-0.5 {{ $isToday ? 'text-blue-600 dark:text-blue-400' : 'text-slate-700 dark:text-slate-300' }}">
                        {{ $date->format('d') }}
                    </span>
                    @if($date->day === 1)
                    <span class="text-[9px] text-slate-400 dark:text-slate-500 leading-none mt-0.5">{{ $date->translatedFormat('M') }}</span>
                    @endif
                </div>
                @endforeach
            </div>

            {{-- Room rows --}}
            @forelse($rooms as $room)
            @php $roomBookings = $bookings->get($room->id, collect()); @endphp
            <div class="flex border-b border-slate-100 dark:border-slate-700 last:border-0 group" style="height:52px">

                {{-- Room info (sticky) --}}
                <a href="{{ route('rooms.edit', $room) }}"
                   class="sticky left-0 z-20 bg-white dark:bg-slate-800 group-hover:bg-slate-50 dark:group-hover:bg-slate-700/40 border-r border-slate-100 dark:border-slate-700 flex items-center gap-2.5 px-3 transition-colors flex-shrink-0"
                   style="width:{{ $roomColW }}px; min-width:{{ $roomColW }}px">
                    <div class="min-w-0">
                        <p class="text-sm font-bold text-slate-800 dark:text-slate-100 leading-tight">{{ $room->number }}</p>
                        <p class="text-[11px] text-slate-400 dark:text-slate-500 truncate leading-tight">{{ $room->roomType->name }}</p>
                    </div>
                </a>

                {{-- Timeline cells --}}
                <div class="relative flex-shrink-0" style="width:{{ $days * $cellW }}px; height:52px">

                    {{-- Day backgrounds + click-to-create --}}
                    @foreach($dates as $i => $date)
                    @php $isToday = $date->toDateString() === $todayStr; $isWeekend = $date->isWeekend(); @endphp
                    <a href="{{ route('bookings.create', ['room_id' => $room->id, 'check_in' => $date->format('Y-m-d'), 'check_out' => $date->copy()->addDay()->format('Y-m-d')]) }}"
                       title="Создать бронирование с {{ $date->format('d.m') }}"
                       class="absolute top-0 bottom-0 border-r border-slate-100 dark:border-slate-700 hover:bg-blue-50/70 dark:hover:bg-blue-900/20 transition-colors
                              {{ $isToday ? 'bg-blue-50/30 dark:bg-blue-900/10' : ($isWeekend ? 'bg-slate-50/50 dark:bg-slate-900/10' : '') }}"
                       style="left:{{ $i * $cellW }}px; width:{{ $cellW }}px; z-index:1">
                    </a>
                    @endforeach

                    {{-- Booking bars --}}
                    @foreach($roomBookings as $booking)
                    @php
                        $barIn  = $booking->check_in_date->max($start);
                        $barOut = $booking->check_out_date->min($boundary);
                        if ($barOut <= $barIn) continue;
                        $leftCells  = (int) $start->diffInDays($barIn);
                        $widthCells = (int) $barIn->diffInDays($barOut);
                        if ($widthCells <= 0) continue;
                        $left  = $leftCells * $cellW + 2;
                        $width = $widthCells * $cellW - 4;
                        $color = $statusColors[$booking->status->value] ?? 'bg-slate-400 text-white';
                    @endphp
                    <a href="{{ route('bookings.show', $booking) }}"
                       title="{{ $booking->guest->full_name }} · {{ $booking->check_in_date->format('d.m') }}–{{ $booking->check_out_date->format('d.m') }} · {{ $booking->status->label() }}"
                       class="absolute flex items-center px-2 rounded-md text-xs font-semibold truncate shadow-sm transition-colors {{ $color }}"
                       style="left:{{ $left }}px; width:{{ $width }}px; top:8px; height:36px; z-index:10">
                        @if($width >= 48)
                        <span class="truncate">{{ $booking->guest->full_name }}</span>
                        @endif
                    </a>
                    @endforeach

                </div>
            </div>
            @empty
            <div class="py-16 text-center text-slate-400 dark:text-slate-500 text-sm">Нет номеров</div>
            @endforelse

        </div>
    </div>
</div>

@endsection
