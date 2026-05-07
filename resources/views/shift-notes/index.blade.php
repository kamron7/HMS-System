@extends('layouts.app')

@section('title', 'Смена')

@section('content')
<div class="mb-6 flex items-center justify-between">
    <div>
        <h1 class="text-2xl font-bold text-slate-900 dark:text-slate-100">Смена</h1>
        <p class="text-slate-500 dark:text-slate-400 mt-1 text-sm">
            {{ now()->format('d.m.Y') }} · {{ now()->format('H:i') }}
            @php $currentShift = now()->hour < 14 ? 'Утро' : (now()->hour < 22 ? 'Вечер' : 'Ночь'); @endphp
            · <span class="font-semibold">{{ $currentShift }}</span>
        </p>
    </div>
    <button x-data x-on:click="$dispatch('open-palette')"
            class="inline-flex items-center gap-2 px-3 py-2 text-xs font-semibold bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 rounded-lg hover:bg-slate-200 dark:hover:bg-slate-600 transition-colors">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"/></svg>
        Поиск
    </button>
</div>

{{-- Summary cards --}}
<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
    <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm p-4">
        <div class="flex items-center gap-2 mb-1">
            <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
            <p class="text-xs font-semibold text-slate-500 uppercase">Заезды сегодня</p>
        </div>
        <p class="text-2xl font-bold text-slate-900 dark:text-slate-100">{{ $arrivals->count() }}</p>
    </div>
    <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm p-4">
        <div class="flex items-center gap-2 mb-1">
            <span class="w-2 h-2 rounded-full bg-red-500"></span>
            <p class="text-xs font-semibold text-slate-500 uppercase">Выезды сегодня</p>
        </div>
        <p class="text-2xl font-bold text-slate-900 dark:text-slate-100">{{ $departures->count() }}</p>
    </div>
    <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm p-4">
        <div class="flex items-center gap-2 mb-1">
            <span class="w-2 h-2 rounded-full bg-orange-500"></span>
            <p class="text-xs font-semibold text-slate-500 uppercase">Грязные номера</p>
        </div>
        <p class="text-2xl font-bold text-slate-900 dark:text-slate-100">{{ $dirtyRooms->count() }}</p>
    </div>
    <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm p-4">
        <div class="flex items-center gap-2 mb-1">
            <span class="w-2 h-2 rounded-full bg-yellow-500"></span>
            <p class="text-xs font-semibold text-slate-500 uppercase">Открытые заявки</p>
        </div>
        <p class="text-2xl font-bold text-slate-900 dark:text-slate-100">{{ $openMaintenance->count() }}</p>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    {{-- LEFT: Dashboard panels --}}
    <div class="lg:col-span-2 space-y-6">

        {{-- VIPs checked in --}}
        @if($vips->isNotEmpty())
        <div class="bg-white dark:bg-slate-800 rounded-xl border border-amber-200 dark:border-amber-800 shadow-sm overflow-hidden">
            <div class="px-5 py-3 border-b border-amber-100 dark:border-amber-800 bg-amber-50 dark:bg-amber-900/20">
                <h2 class="text-sm font-semibold text-amber-800 dark:text-amber-300 flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-4 h-4"><path fill-rule="evenodd" d="M10.788 3.21c.448-1.077 1.976-1.077 2.424 0l2.082 5.006 5.404.434c1.164.093 1.636 1.545.749 2.305l-4.117 3.527 1.257 5.273c.271 1.136-.964 2.033-1.96 1.425L12 18.354 7.373 21.18c-.996.608-2.231-.29-1.96-1.425l1.257-5.273-4.117-3.527c-.887-.76-.415-2.212.749-2.305l5.404-.434 2.082-5.005Z" clip-rule="evenodd"/></svg>
                    VIP гости в отеле ({{ $vips->count() }})
                </h2>
            </div>
            <div class="divide-y divide-slate-100 dark:divide-slate-700">
                @foreach($vips as $vip)
                <a href="{{ route('bookings.show', $vip) }}" class="px-5 py-3 flex items-center justify-between hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors">
                    <div>
                        <p class="text-sm font-semibold text-slate-800 dark:text-white">{{ $vip->guest->fullName }}</p>
                        <p class="text-xs text-slate-400">№{{ $vip->room->number }} · {{ $vip->check_in_date->format('d.m') }} — {{ $vip->check_out_date->format('d.m') }}</p>
                    </div>
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 text-slate-400"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5"/></svg>
                </a>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Arrivals --}}
        <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden">
            <div class="px-5 py-3 border-b border-slate-100 dark:border-slate-700">
                <h2 class="text-sm font-semibold text-slate-700 dark:text-slate-200">Заезды сегодня ({{ $arrivals->count() }})</h2>
            </div>
            @if($arrivals->isEmpty())
            <p class="px-5 py-6 text-sm text-slate-400">Нет ожидаемых заездов</p>
            @else
            <div class="divide-y divide-slate-100 dark:divide-slate-700">
                @foreach($arrivals as $b)
                <a href="{{ route('bookings.show', $b) }}" class="px-5 py-3 flex items-center justify-between hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors">
                    <div>
                        <p class="text-sm font-semibold text-slate-800 dark:text-white">{{ $b->guest->fullName }}</p>
                        <p class="text-xs text-slate-400">№{{ $b->room->number }} · {{ $b->room->roomType->name }}</p>
                    </div>
                    <span class="text-xs px-2 py-0.5 rounded-md font-semibold {{ $b->status->color() === 'green' ? 'bg-emerald-50 text-emerald-700' : 'bg-blue-50 text-blue-700' }}">
                        {{ $b->status->label() }}
                    </span>
                </a>
                @endforeach
            </div>
            @endif
        </div>

        {{-- Departures --}}
        <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden">
            <div class="px-5 py-3 border-b border-slate-100 dark:border-slate-700">
                <h2 class="text-sm font-semibold text-slate-700 dark:text-slate-200">Выезды сегодня ({{ $departures->count() }})</h2>
            </div>
            @if($departures->isEmpty())
            <p class="px-5 py-6 text-sm text-slate-400">Нет ожидаемых выездов</p>
            @else
            <div class="divide-y divide-slate-100 dark:divide-slate-700">
                @foreach($departures as $b)
                <a href="{{ route('bookings.show', $b) }}" class="px-5 py-3 flex items-center justify-between hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors">
                    <div>
                        <p class="text-sm font-semibold text-slate-800 dark:text-white">{{ $b->guest->fullName }}</p>
                        <p class="text-xs text-slate-400">№{{ $b->room->number }}</p>
                    </div>
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 text-slate-400"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5"/></svg>
                </a>
                @endforeach
            </div>
            @endif
        </div>

        {{-- Dirty rooms --}}
        @if($dirtyRooms->isNotEmpty() || $cleaningRooms->isNotEmpty())
        <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden">
            <div class="px-5 py-3 border-b border-slate-100 dark:border-slate-700">
                <h2 class="text-sm font-semibold text-slate-700 dark:text-slate-200">Уборка</h2>
            </div>
            @if($dirtyRooms->isNotEmpty())
            <div class="px-5 py-3 border-b border-slate-100 dark:border-slate-700">
                <p class="text-xs font-semibold text-orange-600 mb-2">Ждут уборку ({{ $dirtyRooms->count() }})</p>
                <div class="flex flex-wrap gap-1.5">
                    @foreach($dirtyRooms as $r)
                    <a href="{{ route('rooms.index') }}" class="inline-flex items-center px-2 py-1 text-xs font-mono font-semibold bg-orange-50 text-orange-700 rounded hover:bg-orange-100 transition-colors">
                        {{ $r->number }}
                    </a>
                    @endforeach
                </div>
            </div>
            @endif
            @if($cleaningRooms->isNotEmpty())
            <div class="px-5 py-3">
                <p class="text-xs font-semibold text-yellow-600 mb-2">Убираются ({{ $cleaningRooms->count() }})</p>
                <div class="flex flex-wrap gap-1.5">
                    @foreach($cleaningRooms as $r)
                    <a href="{{ route('rooms.index') }}" class="inline-flex items-center px-2 py-1 text-xs font-mono font-semibold bg-yellow-50 text-yellow-700 rounded hover:bg-yellow-100 transition-colors">
                        {{ $r->number }}
                    </a>
                    @endforeach
                </div>
            </div>
            @endif
        </div>
        @endif

        {{-- Open maintenance --}}
        @if($openMaintenance->isNotEmpty())
        <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden">
            <div class="px-5 py-3 border-b border-slate-100 dark:border-slate-700 flex items-center justify-between">
                <h2 class="text-sm font-semibold text-slate-700 dark:text-slate-200">Открытые заявки ({{ $openMaintenance->count() }})</h2>
                <a href="{{ route('maintenance.index') }}" class="text-xs text-blue-600 hover:underline">Все →</a>
            </div>
            <div class="divide-y divide-slate-100 dark:divide-slate-700">
                @foreach($openMaintenance->take(5) as $m)
                <a href="{{ route('maintenance.show', $m) }}" class="px-5 py-3 flex items-center justify-between hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors">
                    <div>
                        <p class="text-sm font-medium text-slate-800 dark:text-white">
                            №{{ $m->room->number }} — {{ $m->title }}
                            @if($m->guest)
                            <span class="text-xs text-blue-500 ml-1">(гость)</span>
                            @endif
                        </p>
                        @if($m->description)
                        <p class="text-xs text-slate-400 truncate max-w-md">{{ $m->description }}</p>
                        @endif
                    </div>
                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-semibold
                        @if($m->priority->value === 'urgent') bg-red-50 text-red-700
                        @elseif($m->priority->value === 'high') bg-orange-50 text-orange-700
                        @else bg-slate-100 text-slate-600 @endif">
                        {{ $m->priority->label() }}
                    </span>
                </a>
                @endforeach
            </div>
        </div>
        @endif
    </div>

    {{-- RIGHT: Notes form + feed --}}
    <div class="space-y-6">
        {{-- Note form --}}
        <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm p-5">
            <h2 class="text-sm font-semibold text-slate-700 dark:text-slate-200 mb-4">Заметка смены</h2>
            <form method="POST" action="{{ route('shift-notes.store') }}">
                @csrf
                <div class="mb-3">
                    <label class="block text-xs font-semibold text-slate-500 mb-1">Смена</label>
                    @php $now = now(); $defaultShift = $now->hour < 14 ? 'morning' : ($now->hour < 22 ? 'evening' : 'night'); @endphp
                    <select name="shift" required
                        class="w-full border border-slate-200 dark:border-slate-600 rounded-lg px-3 py-2 text-sm bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-blue-500 focus:outline-none">
                        <option value="morning" {{ old('shift', $defaultShift) === 'morning' ? 'selected' : '' }}>Утро</option>
                        <option value="evening" {{ old('shift', $defaultShift) === 'evening' ? 'selected' : '' }}>Вечер</option>
                        <option value="night"   {{ old('shift', $defaultShift) === 'night'   ? 'selected' : '' }}>Ночь</option>
                    </select>
                    @error('shift')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
                </div>
                <div class="mb-3">
                    <label class="block text-xs font-semibold text-slate-500 mb-1">Текст</label>
                    <textarea name="body" rows="4" required maxlength="2000"
                              class="w-full border border-slate-200 dark:border-slate-600 rounded-lg px-3 py-2 text-sm bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100 resize-none focus:ring-2 focus:ring-blue-500 focus:outline-none"
                              placeholder="Что произошло за смену...">{{ old('body') }}</textarea>
                    @error('body')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
                </div>
                <button type="submit"
                    class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-blue-600 text-white text-sm font-semibold rounded-lg hover:bg-blue-700 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                    Сохранить
                </button>
            </form>
        </div>

        {{-- Notes feed --}}
        <div class="space-y-3">
            <h2 class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Заметки за 7 дней</h2>
            @forelse($notes as $note)
            <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm p-4">
                <div class="flex items-center gap-2 mb-2">
                    <div class="w-6 h-6 rounded-full bg-slate-600 dark:bg-slate-500 flex items-center justify-center flex-shrink-0">
                        <span class="text-[10px] font-semibold text-white">{{ substr($note->user->name, 0, 1) }}</span>
                    </div>
                    <span class="text-xs font-semibold text-slate-700 dark:text-slate-200">{{ $note->user->name }}</span>
                    <span class="text-xs text-slate-400">{{ $note->created_at->format('d.m H:i') }}</span>
                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-semibold {{ $note->shiftColor() }}">
                        {{ $note->shiftLabel() }}
                    </span>
                </div>
                <p class="text-sm text-slate-700 dark:text-slate-300 whitespace-pre-wrap">{{ $note->body }}</p>
            </div>
            @empty
            <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm px-5 py-8 text-center">
                <p class="text-sm text-slate-400">Заметок нет</p>
            </div>
            @endforelse
        </div>
    </div>
</div>
@endsection
