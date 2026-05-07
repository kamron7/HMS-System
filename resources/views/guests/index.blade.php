@extends('layouts.app')

@section('title', 'Гости')

@section('content')

{{-- Header --}}
<div class="flex items-center justify-between mb-5">
    <div>
        <h1 class="text-2xl font-bold text-slate-900 dark:text-slate-100">Гости</h1>
        <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">{{ $guests->total() }} {{ $guests->total() === 1 ? 'гость' : 'гостей' }}</p>
    </div>
    <div class="flex items-center gap-2">
        <a href="{{ route('guests.export', request()->query()) }}"
           class="inline-flex items-center gap-2 px-3 py-2 text-sm font-semibold text-slate-600 dark:text-slate-300 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors shadow-sm"
           title="Экспорт CSV">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 flex-shrink-0"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
            <span class="hidden sm:inline">CSV</span>
        </a>
        @if(in_array(auth()->user()->role->value, ['owner', 'manager']))
        <a href="{{ route('guests.mail') }}"
           class="inline-flex items-center gap-2 px-3 py-2 bg-slate-700 dark:bg-slate-600 text-white text-sm font-semibold rounded-lg hover:bg-slate-800 dark:hover:bg-slate-500 transition-colors shadow-sm"
           title="Рассылка">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4 flex-shrink-0"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75"/></svg>
            <span class="hidden sm:inline">Рассылка</span>
        </a>
        @endif
        <a href="{{ route('guests.create') }}"
           class="inline-flex items-center gap-2 px-3 py-2 bg-blue-600 text-white text-sm font-semibold rounded-lg hover:bg-blue-700 transition-colors shadow-sm">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4 flex-shrink-0"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
            <span class="hidden sm:inline">Добавить гостя</span>
        </a>
    </div>
</div>

{{-- Stats chips --}}
<div class="flex flex-wrap gap-3 mb-5">
    <div class="inline-flex items-center gap-2 px-3 py-1.5 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg shadow-sm">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 text-slate-400"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z"/></svg>
        <span class="text-sm font-semibold text-slate-700 dark:text-slate-200">{{ number_format($totals['all']) }}</span>
        <span class="text-xs text-slate-400">всего</span>
    </div>
    @if($totals['vip'] > 0)
    <div class="inline-flex items-center gap-2 px-3 py-1.5 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg">
        <span class="w-2 h-2 rounded-full bg-yellow-400"></span>
        <span class="text-sm font-semibold text-yellow-800 dark:text-yellow-300">{{ $totals['vip'] }}</span>
        <span class="text-xs text-yellow-600 dark:text-yellow-500">VIP</span>
    </div>
    @endif
    @if($totals['blacklist'] > 0)
    <div class="inline-flex items-center gap-2 px-3 py-1.5 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg">
        <span class="w-2 h-2 rounded-full bg-red-400"></span>
        <span class="text-sm font-semibold text-red-700 dark:text-red-300">{{ $totals['blacklist'] }}</span>
        <span class="text-xs text-red-500 dark:text-red-400">в чёрном списке</span>
    </div>
    @endif
</div>

{{-- Tag filter pills + search --}}
<div class="flex flex-col sm:flex-row sm:items-center gap-3 mb-5">
    {{-- Tag pills --}}
    <div class="flex flex-wrap gap-1.5">
        <a href="{{ route('guests.index', array_filter(['q' => request('q')])) }}"
           class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-semibold transition-colors
                  {{ !request('tag') ? 'bg-slate-900 dark:bg-white text-white dark:text-slate-900' : 'bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-600' }}">
            Все
        </a>
        @foreach($tags as $tag)
        @php
            $isActive = request('tag') === $tag->value;
            $dotColor = match($tag->value) {
                'vip'       => 'bg-yellow-400',
                'blacklist' => 'bg-red-400',
                default     => 'bg-slate-400',
            };
        @endphp
        <a href="{{ route('guests.index', array_filter(['q' => request('q'), 'tag' => $tag->value])) }}"
           class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold transition-colors
                  {{ $isActive ? 'bg-slate-900 dark:bg-white text-white dark:text-slate-900' : 'bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-600' }}">
            <span class="w-1.5 h-1.5 rounded-full {{ $dotColor }}"></span>
            {{ $tag->label() }}
        </a>
        @endforeach
    </div>

    {{-- Search --}}
    <form method="GET" action="{{ route('guests.index') }}" class="flex gap-2 sm:ml-auto">
        @if(request('tag'))
            <input type="hidden" name="tag" value="{{ request('tag') }}">
        @endif
        <div class="relative">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"/></svg>
            <input type="text" name="q" value="{{ request('q') }}"
                   placeholder="Имя, телефон…"
                   class="pl-9 pr-4 py-2 border border-slate-200 dark:border-slate-600 rounded-lg text-sm bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500 w-52">
        </div>
        <button type="submit"
                class="inline-flex items-center gap-1.5 px-3 py-2 bg-blue-600 text-white text-sm font-semibold rounded-lg hover:bg-blue-700 transition-colors flex-shrink-0">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"/></svg>
            <span class="hidden sm:inline">Найти</span>
        </button>
        @if(request('q') || request('tag'))
        <a href="{{ route('guests.index') }}"
           class="px-3 py-2 text-sm font-semibold text-slate-600 dark:text-slate-300 bg-slate-100 dark:bg-slate-700 rounded-lg hover:bg-slate-200 dark:hover:bg-slate-600 transition-colors flex-shrink-0">
            Сброс
        </a>
        @endif
    </form>
</div>

{{-- Guest list --}}
<div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden">

    {{-- Mobile card list (< sm) --}}
    <div class="sm:hidden divide-y divide-slate-100 dark:divide-slate-700">
        @forelse($guests as $guest)
        @php
            $tagBg = match($guest->tag?->value) {
                'vip'       => 'bg-yellow-50 dark:bg-yellow-900/30 text-yellow-800 dark:text-yellow-300 ring-1 ring-yellow-300 dark:ring-yellow-700',
                'blacklist' => 'bg-red-50 dark:bg-red-900/30 text-red-700 dark:text-red-300 ring-1 ring-red-200 dark:ring-red-700',
                'regular'   => 'bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 ring-1 ring-slate-200 dark:ring-slate-600',
                default     => null,
            };
            $initials = strtoupper(mb_substr($guest->first_name ?? '?', 0, 1) . mb_substr($guest->last_name ?? '', 0, 1));
            $avatarColors = ['bg-blue-100 dark:bg-blue-900/40 text-blue-700 dark:text-blue-300', 'bg-emerald-100 dark:bg-emerald-900/40 text-emerald-700 dark:text-emerald-300', 'bg-violet-100 dark:bg-violet-900/40 text-violet-700 dark:text-violet-300', 'bg-amber-100 dark:bg-amber-900/40 text-amber-700 dark:text-amber-300'];
            $avatarBg = $avatarColors[$guest->id % count($avatarColors)];
        @endphp
        <a href="{{ route('guests.show', $guest) }}"
           class="flex items-center gap-3 px-4 py-3 hover:bg-slate-50 dark:hover:bg-slate-700/40 transition-colors">
            <div class="w-9 h-9 rounded-full {{ $avatarBg }} flex items-center justify-center text-xs font-bold flex-shrink-0 select-none">
                {{ $initials }}
            </div>
            <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2">
                    <p class="text-sm font-semibold text-slate-900 dark:text-slate-100 truncate">{{ $guest->full_name }}</p>
                    @if($tagBg)
                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-semibold flex-shrink-0 {{ $tagBg }}">{{ $guest->tag->label() }}</span>
                    @endif
                </div>
                <p class="text-xs text-slate-400 dark:text-slate-500 mt-0.5">
                    {{ $guest->phone ?? $guest->email ?? '—' }}
                    @if($guest->bookings_count > 0)
                        <span class="ml-2 text-slate-300 dark:text-slate-600">·</span>
                        <span class="ml-1">{{ $guest->bookings_count }} {{ $guest->bookings_count === 1 ? 'бронирование' : 'бронирований' }}</span>
                    @endif
                </p>
            </div>
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 text-slate-300 dark:text-slate-600 flex-shrink-0"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5"/></svg>
        </a>
        @empty
        <div class="px-5 py-14 text-center">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-10 h-10 mx-auto text-slate-300 dark:text-slate-600 mb-3"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z"/></svg>
            <p class="text-slate-400 dark:text-slate-500 text-sm font-medium">
                @if(request('q')) Гости не найдены по запросу «{{ request('q') }}» @else Гости не добавлены @endif
            </p>
            @if(!request('q') && !request('tag'))
            <a href="{{ route('guests.create') }}" class="mt-3 inline-flex items-center gap-1.5 text-sm text-blue-600 dark:text-blue-400 hover:underline">
                Добавить первого гостя
            </a>
            @endif
        </div>
        @endforelse
    </div>

    {{-- Desktop table (sm+) --}}
    <div class="hidden sm:block overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-slate-50 dark:bg-slate-900/50 border-b border-slate-200 dark:border-slate-700">
                    <th class="text-left px-5 py-3 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Гость</th>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Телефон / Email</th>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider hidden lg:table-cell">Паспорт</th>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider hidden xl:table-cell">Гражданство</th>
                    <th class="text-center px-5 py-3 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Визиты</th>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Метка</th>
                    <th class="text-right px-5 py-3 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Действия</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                @forelse($guests as $guest)
                @php
                    $tagBadge = match($guest->tag?->value) {
                        'vip'       => 'bg-yellow-50 dark:bg-yellow-900/30 text-yellow-800 dark:text-yellow-300 ring-1 ring-yellow-300 dark:ring-yellow-700',
                        'blacklist' => 'bg-red-50 dark:bg-red-900/30 text-red-700 dark:text-red-300 ring-1 ring-red-200 dark:ring-red-700',
                        'regular'   => 'bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 ring-1 ring-slate-200 dark:ring-slate-600',
                        default     => null,
                    };
                    $initials = strtoupper(mb_substr($guest->first_name ?? '?', 0, 1) . mb_substr($guest->last_name ?? '', 0, 1));
                    $avatarColors = ['bg-blue-100 dark:bg-blue-900/40 text-blue-700 dark:text-blue-300', 'bg-emerald-100 dark:bg-emerald-900/40 text-emerald-700 dark:text-emerald-300', 'bg-violet-100 dark:bg-violet-900/40 text-violet-700 dark:text-violet-300', 'bg-amber-100 dark:bg-amber-900/40 text-amber-700 dark:text-amber-300'];
                    $avatarBg = $avatarColors[$guest->id % count($avatarColors)];
                @endphp
                <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors">
                    <td class="px-5 py-3.5">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full {{ $avatarBg }} flex items-center justify-center text-xs font-bold flex-shrink-0 select-none">
                                {{ $initials }}
                            </div>
                            <a href="{{ route('guests.show', $guest) }}"
                               class="font-semibold text-slate-900 dark:text-slate-100 hover:text-blue-600 dark:hover:text-blue-400 transition-colors">
                                {{ $guest->full_name }}
                            </a>
                        </div>
                    </td>
                    <td class="px-5 py-3.5">
                        <div class="space-y-0.5">
                            @if($guest->phone)
                                <a href="tel:{{ $guest->phone }}" class="block text-slate-700 dark:text-slate-300 hover:text-blue-600 dark:hover:text-blue-400 transition-colors">{{ $guest->phone }}</a>
                            @endif
                            @if($guest->email)
                                <a href="mailto:{{ $guest->email }}" class="block text-xs text-slate-400 dark:text-slate-500 hover:text-blue-600 dark:hover:text-blue-400 transition-colors">{{ $guest->email }}</a>
                            @endif
                            @if(!$guest->phone && !$guest->email)
                                <span class="text-slate-300 dark:text-slate-600">—</span>
                            @endif
                        </div>
                    </td>
                    <td class="px-5 py-3.5 text-slate-500 dark:text-slate-400 font-mono text-xs hidden lg:table-cell">{{ $guest->passport_number ?? '—' }}</td>
                    <td class="px-5 py-3.5 text-slate-600 dark:text-slate-300 text-xs hidden xl:table-cell">{{ $guest->nationality ?? '—' }}</td>
                    <td class="px-5 py-3.5 text-center">
                        @if($guest->bookings_count > 0)
                            <span class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 text-xs font-bold">
                                {{ $guest->bookings_count }}
                            </span>
                        @else
                            <span class="text-slate-300 dark:text-slate-600 text-xs">0</span>
                        @endif
                    </td>
                    <td class="px-5 py-3.5">
                        @if($guest->tag && $tagBadge)
                            <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-semibold {{ $tagBadge }}">
                                {{ $guest->tag->label() }}
                            </span>
                        @else
                            <span class="text-slate-300 dark:text-slate-600">—</span>
                        @endif
                    </td>
                    <td class="px-5 py-3.5 text-right">
                        <div class="flex items-center justify-end gap-1">
                            <a href="{{ route('guests.show', $guest) }}"
                               class="inline-flex items-center gap-1 px-2.5 py-1.5 text-xs font-semibold text-slate-600 dark:text-slate-300 bg-slate-100 dark:bg-slate-700 rounded-lg hover:bg-slate-200 dark:hover:bg-slate-600 transition-colors">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-3.5 h-3.5"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/></svg>
                                Профиль
                            </a>
                            <a href="{{ route('guests.edit', $guest) }}"
                               class="inline-flex items-center gap-1 px-2.5 py-1.5 text-xs font-semibold text-blue-700 dark:text-blue-300 bg-blue-50 dark:bg-blue-900/30 rounded-lg hover:bg-blue-100 dark:hover:bg-blue-900/50 transition-colors">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-3.5 h-3.5"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125"/></svg>
                                Изменить
                            </a>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-5 py-14 text-center">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-10 h-10 mx-auto text-slate-300 dark:text-slate-600 mb-3"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z"/></svg>
                        <p class="text-slate-400 dark:text-slate-500 text-sm font-medium">
                            @if(request('q')) Гости не найдены по запросу «{{ request('q') }}» @else Гости не добавлены @endif
                        </p>
                        @if(!request('q') && !request('tag'))
                        <a href="{{ route('guests.create') }}" class="mt-3 inline-flex items-center gap-1.5 text-sm text-blue-600 dark:text-blue-400 hover:underline">
                            Добавить первого гостя
                        </a>
                        @endif
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

</div>

@if($guests->hasPages())
<div class="mt-5">
    {{ $guests->appends(request()->query())->links() }}
</div>
@endif

@endsection
