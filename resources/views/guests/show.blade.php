@extends('layouts.app')

@section('title', $guest->full_name)

@section('content')

@php
    $tagBadge = match($guest->tag?->value) {
        'vip'       => 'bg-yellow-50 dark:bg-yellow-900/30 text-yellow-800 dark:text-yellow-300 ring-1 ring-yellow-300 dark:ring-yellow-700',
        'regular'   => 'bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 ring-1 ring-slate-200 dark:ring-slate-600',
        'blacklist' => 'bg-red-50 dark:bg-red-900/30 text-red-700 dark:text-red-300 ring-1 ring-red-200 dark:ring-red-700',
        default     => null,
    };
    $initials = strtoupper(mb_substr($guest->first_name ?? '?', 0, 1) . mb_substr($guest->last_name ?? '', 0, 1));
    $avatarColors = ['bg-blue-100 dark:bg-blue-900/40 text-blue-700 dark:text-blue-300', 'bg-emerald-100 dark:bg-emerald-900/40 text-emerald-700 dark:text-emerald-300', 'bg-violet-100 dark:bg-violet-900/40 text-violet-700 dark:text-violet-300', 'bg-amber-100 dark:bg-amber-900/40 text-amber-700 dark:text-amber-300'];
    $avatarBg = $avatarColors[$guest->id % count($avatarColors)];
@endphp

{{-- Breadcrumb --}}
<div class="flex items-center gap-2 mb-6 text-sm text-slate-500 dark:text-slate-400">
    <a href="{{ route('guests.index') }}" class="hover:text-slate-700 dark:hover:text-slate-200 transition-colors flex items-center gap-1.5">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18"/></svg>
        Гости
    </a>
    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 text-slate-300 dark:text-slate-600"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5"/></svg>
    <span class="text-slate-700 dark:text-slate-200 font-medium">{{ $guest->full_name }}</span>
</div>

{{-- Hero profile card --}}
<div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm p-6 mb-5">
    <div class="flex flex-col sm:flex-row sm:items-start gap-5">

        {{-- Avatar --}}
        <div class="w-16 h-16 rounded-2xl {{ $avatarBg }} flex items-center justify-center text-2xl font-bold flex-shrink-0 select-none">
            {{ $initials }}
        </div>

        {{-- Name + contact --}}
        <div class="flex-1 min-w-0">
            <div class="flex flex-wrap items-center gap-2.5 mb-1">
                <h1 class="text-xl font-bold text-slate-900 dark:text-slate-100">{{ $guest->full_name }}</h1>
                @if($guest->tag && $tagBadge)
                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-semibold {{ $tagBadge }}">
                        {{ $guest->tag->label() }}
                    </span>
                @endif
            </div>
            <div class="flex flex-wrap items-center gap-x-4 gap-y-1 text-sm text-slate-500 dark:text-slate-400">
                @if($guest->phone)
                    <a href="tel:{{ $guest->phone }}" class="flex items-center gap-1.5 hover:text-blue-600 dark:hover:text-blue-400 transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 flex-shrink-0"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 0 0 2.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 0 1-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 0 0-1.091-.852H4.5A2.25 2.25 0 0 0 2.25 4.5v2.25Z"/></svg>
                        {{ $guest->phone }}
                    </a>
                @endif
                @if($guest->email)
                    <a href="mailto:{{ $guest->email }}" class="flex items-center gap-1.5 hover:text-blue-600 dark:hover:text-blue-400 transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 flex-shrink-0"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75"/></svg>
                        {{ $guest->email }}
                    </a>
                @endif
                <span class="flex items-center gap-1.5">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 flex-shrink-0"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5"/></svg>
                    С нами с {{ $guest->created_at->translatedFormat('d M Y') }}
                </span>
            </div>
        </div>

        {{-- Action buttons --}}
        <div class="flex items-center gap-2 sm:flex-shrink-0">
            <a href="{{ route('bookings.create', ['guest_id' => $guest->id]) }}"
               class="inline-flex items-center gap-2 px-3 py-2 bg-emerald-600 text-white text-sm font-semibold rounded-lg hover:bg-emerald-700 transition-colors shadow-sm">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                Бронирование
            </a>
            <a href="{{ route('guests.edit', $guest) }}"
               class="inline-flex items-center gap-2 px-3 py-2 bg-blue-600 text-white text-sm font-semibold rounded-lg hover:bg-blue-700 transition-colors shadow-sm">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125"/></svg>
                Изменить
            </a>
            @if(in_array(auth()->user()->role->value, ['owner', 'manager']))
            <form method="POST" action="{{ route('guests.destroy', $guest) }}"
                  onsubmit="return confirm('Удалить гостя {{ addslashes($guest->full_name) }}? Это действие необратимо.')">
                @csrf @method('DELETE')
                <button type="submit"
                        class="inline-flex items-center gap-2 px-3 py-2 text-sm font-semibold text-red-600 dark:text-red-400 bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-700 rounded-lg hover:bg-red-100 dark:hover:bg-red-900/50 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0"/></svg>
                    Удалить
                </button>
            </form>
            @endif
        </div>
    </div>

    {{-- Stats strip --}}
    <div class="mt-5 pt-5 border-t border-slate-100 dark:border-slate-700 grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div>
            <p class="text-xs text-slate-400 dark:text-slate-500 mb-0.5">Всего бронирований</p>
            <p class="text-xl font-bold text-slate-900 dark:text-slate-100">{{ $stats['total_bookings'] }}</p>
        </div>
        <div>
            <p class="text-xs text-slate-400 dark:text-slate-500 mb-0.5">Завершённых пребываний</p>
            <p class="text-xl font-bold text-slate-900 dark:text-slate-100">{{ $stats['total_stays'] }}</p>
        </div>
        <div>
            <p class="text-xs text-slate-400 dark:text-slate-500 mb-0.5">Потрачено всего</p>
            <p class="text-xl font-bold text-slate-900 dark:text-slate-100">
                {{ $stats['total_spent'] > 0 ? number_format($stats['total_spent'], 0, '.', ' ') . ' сум' : '—' }}
            </p>
        </div>
        <div>
            <p class="text-xs text-slate-400 dark:text-slate-500 mb-0.5">Последний визит</p>
            <p class="text-xl font-bold text-slate-900 dark:text-slate-100">
                {{ $stats['last_stay'] ? \Carbon\Carbon::parse($stats['last_stay'])->translatedFormat('d M Y') : '—' }}
            </p>
        </div>
    </div>
</div>

{{-- Info + Details --}}
<div class="grid grid-cols-1 md:grid-cols-3 gap-5 mb-5">

    {{-- Passport / nationality --}}
    <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm p-5">
        <h2 class="text-xs font-semibold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-4">Документы</h2>
        <dl class="space-y-3">
            <div class="flex items-start gap-3">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 text-slate-400 mt-0.5 flex-shrink-0"><path stroke-linecap="round" stroke-linejoin="round" d="M15 9h3.75M15 12h3.75M15 15h3.75M4.5 19.5h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 4.5 19.5Zm6-10.125a1.875 1.875 0 1 1-3.75 0 1.875 1.875 0 0 1 3.75 0Zm1.294 6.336a6.721 6.721 0 0 1-3.17.789 6.721 6.721 0 0 1-3.168-.789 3.376 3.376 0 0 1 6.338 0Z"/></svg>
                <div>
                    <dt class="text-xs text-slate-400 dark:text-slate-500 mb-0.5">Паспорт</dt>
                    <dd class="text-sm font-mono font-semibold text-slate-900 dark:text-slate-100">{{ $guest->passport_number ?? '—' }}</dd>
                </div>
            </div>
            <div class="flex items-start gap-3">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 text-slate-400 mt-0.5 flex-shrink-0"><path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9.004 9.004 0 0 0 8.716-6.747M12 21a9.004 9.004 0 0 1-8.716-6.747M12 21c2.485 0 4.5-4.03 4.5-9S14.485 3 12 3m0 18c-2.485 0-4.5-4.03-4.5-9S9.515 3 12 3m0 0a8.997 8.997 0 0 1 7.843 4.582M12 3a8.997 8.997 0 0 0-7.843 4.582m15.686 0A11.953 11.953 0 0 1 12 10.5c-2.998 0-5.74-1.1-7.843-2.918m15.686 0A8.959 8.959 0 0 1 21 12c0 .778-.099 1.533-.284 2.253M3 12a8.96 8.96 0 0 0 .717 3.547"/></svg>
                <div>
                    <dt class="text-xs text-slate-400 dark:text-slate-500 mb-0.5">Гражданство</dt>
                    <dd class="text-sm font-semibold text-slate-900 dark:text-slate-100">{{ $guest->nationality ?? '—' }}</dd>
                </div>
            </div>
        </dl>
    </div>

    {{-- Active bookings summary (spans 2 cols) --}}
    <div class="md:col-span-2 bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm p-5">
        <h2 class="text-xs font-semibold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-4">Активные бронирования</h2>
        @php
            $activeBookings = $guest->bookings->whereIn('status', ['inquiry', 'pending', 'confirmed', 'checked_in'])->values();
        @endphp
        @if($activeBookings->isEmpty())
            <p class="text-sm text-slate-400 dark:text-slate-500">Нет активных бронирований</p>
        @else
            <div class="space-y-2">
                @foreach($activeBookings as $ab)
                @php
                    $isCheckedIn = $ab->status->value === 'checked_in';
                @endphp
                <a href="{{ route('bookings.show', $ab) }}"
                   class="flex items-center gap-3 p-3 rounded-lg {{ $isCheckedIn ? 'bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800' : 'bg-slate-50 dark:bg-slate-700/40 border border-slate-100 dark:border-slate-700' }} hover:border-blue-300 dark:hover:border-blue-600 transition-colors">
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 mb-0.5">
                            <span class="text-sm font-semibold text-slate-900 dark:text-slate-100">Ном. {{ $ab->room->number ?? '—' }}</span>
                            @if($ab->room && $ab->room->roomType)
                                <span class="text-xs text-slate-400">{{ $ab->room->roomType->name }}</span>
                            @endif
                            <x-status-badge :status="$ab->status" class="ml-auto" />
                        </div>
                        <p class="text-xs text-slate-500 dark:text-slate-400">
                            {{ $ab->check_in_date->translatedFormat('d M Y') }}
                            @if($ab->check_in_time) <span class="text-blue-500 font-semibold">{{ substr($ab->check_in_time, 0, 5) }}</span>@endif
                            →
                            {{ $ab->check_out_date->translatedFormat('d M Y') }}
                            @if($ab->check_out_time) <span class="text-orange-500 font-semibold">{{ substr($ab->check_out_time, 0, 5) }}</span>@endif
                        </p>
                    </div>
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 text-slate-300 dark:text-slate-600 flex-shrink-0"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5"/></svg>
                </a>
                @endforeach
            </div>
        @endif
    </div>
</div>

{{-- Booking history --}}
<div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden">
    <div class="px-5 py-4 border-b border-slate-100 dark:border-slate-700 flex items-center justify-between">
        <h2 class="text-sm font-semibold text-slate-700 dark:text-slate-200">
            История бронирований
            <span class="ml-1.5 px-2 py-0.5 bg-slate-100 dark:bg-slate-700 text-slate-500 dark:text-slate-400 text-xs rounded-full font-normal">{{ $guest->bookings->count() }}</span>
        </h2>
    </div>

    @php $completedBookings = $guest->bookings->whereIn('status', ['checked_out', 'cancelled', 'no_show']); @endphp

    @if($guest->bookings->isEmpty())
    <div class="px-5 py-14 text-center">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-8 h-8 mx-auto text-slate-300 dark:text-slate-600 mb-2"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5"/></svg>
        <p class="text-slate-400 dark:text-slate-500 text-sm">Нет бронирований</p>
    </div>
    @else
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-slate-50 dark:bg-slate-900/50 border-b border-slate-100 dark:border-slate-700">
                    <th class="text-left px-5 py-3 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Номер</th>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Заезд — Выезд</th>
                    <th class="text-center px-4 py-3 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider hidden sm:table-cell">Ночей</th>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Статус</th>
                    <th class="text-right px-5 py-3 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Сумма</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                @foreach($guest->bookings as $booking)
                @php
                    $nights = $booking->check_in_date->diffInDays($booking->check_out_date);
                    $paid   = $booking->payments->sum('amount');
                    $isPaid = $paid >= $booking->total_price && $booking->total_price > 0;
                    $isActive = in_array($booking->status->value, ['checked_in', 'confirmed', 'pending', 'inquiry']);
                @endphp
                <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors {{ $isActive ? 'bg-blue-50/30 dark:bg-blue-900/10' : '' }}">
                    <td class="px-5 py-3.5">
                        <a href="{{ route('bookings.show', $booking) }}"
                           class="font-semibold text-slate-900 dark:text-slate-100 hover:text-blue-600 dark:hover:text-blue-400 transition-colors">
                            №{{ $booking->room->number ?? '—' }}
                        </a>
                        @if($booking->room && $booking->room->roomType)
                            <p class="text-xs text-slate-400 dark:text-slate-500 mt-0.5">{{ $booking->room->roomType->name }}</p>
                        @endif
                    </td>
                    <td class="px-5 py-3.5 text-slate-600 dark:text-slate-300">
                        <div class="flex items-baseline gap-1.5 text-xs font-mono">
                            <span class="text-blue-600 dark:text-blue-400">{{ $booking->check_in_date->format('d.m.Y') }}</span>
                            @if($booking->check_in_time)<span class="text-blue-500 font-semibold">{{ substr($booking->check_in_time, 0, 5) }}</span>@endif
                        </div>
                        <div class="flex items-baseline gap-1.5 text-xs font-mono mt-0.5">
                            <span class="text-orange-600 dark:text-orange-400">{{ $booking->check_out_date->format('d.m.Y') }}</span>
                            @if($booking->check_out_time)<span class="text-orange-500 font-semibold">{{ substr($booking->check_out_time, 0, 5) }}</span>@endif
                        </div>
                    </td>
                    <td class="px-4 py-3.5 text-center hidden sm:table-cell">
                        <span class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 text-xs font-bold">
                            {{ $nights }}
                        </span>
                    </td>
                    <td class="px-5 py-3.5">
                        <x-status-badge :status="$booking->status" />
                    </td>
                    <td class="px-5 py-3.5 text-right">
                        <p class="font-semibold text-slate-900 dark:text-slate-100 tabular-nums">
                            {{ number_format($booking->total_price, 0, '.', ' ') }}
                            <span class="text-slate-400 dark:text-slate-500 font-normal text-xs">сум</span>
                        </p>
                        @if($booking->total_price > 0)
                        <p class="text-xs mt-0.5 {{ $isPaid ? 'text-emerald-600 dark:text-emerald-400' : 'text-amber-600 dark:text-amber-400' }}">
                            {{ $isPaid ? 'Оплачено' : 'Долг ' . number_format($booking->total_price - $paid, 0, '.', ' ') }}
                        </p>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif
</div>

@endsection
