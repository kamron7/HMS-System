@extends('layouts.app')

@section('title', 'Находка #' . $item->id)

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="mb-6 flex items-center gap-3">
        <a href="{{ route('lost-items.index') }}"
           class="inline-flex items-center gap-1.5 text-sm text-slate-500 hover:text-slate-700 transition-colors">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18"/></svg>
            Находки
        </a>
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 text-slate-300"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5"/></svg>
        <h1 class="text-xl font-bold text-slate-900">{{ $item->title }}</h1>
    </div>

    <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm p-6 mb-4">
        <div class="flex items-start justify-between gap-4 mb-4">
            <div>
                <p class="text-sm text-slate-500 mt-0.5">
                    @if($item->room)
                        Номер {{ $item->room->number }}
                    @endif
                    @if($item->storage_location)
                        · Хранится: <span class="font-semibold text-slate-700">{{ $item->storage_location }}</span>
                    @endif
                </p>
            </div>
            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold
                @if($item->status->color() === 'blue') bg-blue-50 text-blue-700 ring-1 ring-blue-200
                @elseif($item->status->color() === 'yellow') bg-yellow-50 text-yellow-700 ring-1 ring-yellow-200
                @elseif($item->status->color() === 'green') bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200
                @else bg-gray-50 text-gray-600 ring-1 ring-gray-200 @endif">
                {{ $item->status->label() }}
            </span>
        </div>

        @if($item->description)
        <p class="text-sm text-slate-600 leading-relaxed mb-4">{{ $item->description }}</p>
        @endif

        {{-- Photos --}}
        @if($item->photos)
        <div class="grid grid-cols-3 gap-3 mb-4">
            @foreach(explode(',', $item->photos) as $photo)
            <a href="{{ asset('storage/' . $photo) }}" target="_blank" class="rounded-lg overflow-hidden border border-slate-200 dark:border-slate-600 hover:opacity-90 transition-opacity">
                <img src="{{ asset('storage/' . $photo) }}" class="w-full h-24 object-cover" alt="">
            </a>
            @endforeach
        </div>
        @endif

        <div class="grid grid-cols-2 gap-4 text-sm border-t border-slate-100 dark:border-slate-700 pt-4">
            <div>
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Нашёл</p>
                <p class="text-slate-700 dark:text-slate-200">{{ $item->foundBy?->name ?? '—' }}</p>
                <p class="text-xs text-slate-400 font-mono">{{ $item->found_at->format('d.m.Y') }}</p>
            </div>
            <div>
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Гость</p>
                @if($item->guest)
                <a href="{{ route('guests.show', $item->guest) }}" class="text-blue-600 hover:text-blue-700">{{ $item->guest->fullName }}</a>
                @if($item->booking)
                <p class="text-xs text-slate-400 mt-0.5">Бронь: <a href="{{ route('bookings.show', $item->booking) }}" class="font-mono text-blue-500 hover:text-blue-600">{{ $item->booking->booking_ref }}</a></p>
                @endif
                @else
                <p class="text-slate-400 italic">Не указан</p>
                @endif
            </div>
            @if($item->returned_at)
            <div>
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Возвращено</p>
                <p class="text-xs text-slate-400 font-mono">{{ $item->returned_at->format('d.m.Y') }}</p>
            </div>
            @endif
        </div>
    </div>

    {{-- Status actions --}}
    @if($item->status->value !== 'returned' && $item->status->value !== 'discarded')
    <div class="flex items-center gap-3 mb-4">
        <form method="POST" action="{{ route('lost-items.status', $item) }}">
            @csrf
            <button type="submit" name="status" value="returned"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-600 text-white text-sm font-semibold rounded-lg hover:bg-emerald-700 transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                Отметить возвращённым
            </button>
        </form>
        @if($item->status->value === 'found')
        <form method="POST" action="{{ route('lost-items.status', $item) }}">
            @csrf
            <button type="submit" name="status" value="stored"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-yellow-600 text-white text-sm font-semibold rounded-lg hover:bg-yellow-700 transition-colors">
                На хранение
            </button>
        </form>
        @endif
    </div>
    @endif

    {{-- Delete --}}
    <form method="POST" action="{{ route('lost-items.destroy', $item) }}" onsubmit="return confirm('Удалить запись?')">
        @csrf @method('DELETE')
        <button type="submit" class="text-xs text-red-500 hover:text-red-700 transition-colors">Удалить запись</button>
    </form>
</div>
@endsection
