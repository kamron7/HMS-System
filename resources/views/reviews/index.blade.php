@extends('layouts.app')

@section('title', 'Отзывы')

@section('content')
<div class="mb-6 flex items-center justify-between">
    <div>
        <h1 class="text-2xl font-bold text-slate-900 dark:text-slate-100">
            Отзывы гостей
            <span class="ml-2 text-base font-normal text-slate-400">({{ $reviews->total() }})</span>
        </h1>
        @if($avgRating)
        <p class="text-sm text-slate-500 mt-0.5">
            Средняя оценка: <span class="font-semibold text-slate-700 dark:text-slate-300">{{ number_format($avgRating, 1) }}★</span>
        </p>
        @endif
    </div>
</div>

{{-- Stats + Filters --}}
<div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm p-4 mb-6">
    {{-- Star filter pills --}}
    <div class="flex items-center gap-1.5 flex-wrap mb-4">
        <a href="{{ route('reviews.index', array_filter(['room_id' => request('room_id'), 'search' => request('search'), 'date_from' => request('date_from'), 'date_to' => request('date_to')])) }}"
           class="px-3 py-1.5 text-xs font-semibold rounded-full transition-colors {{ !request('rating') ? 'bg-slate-900 dark:bg-white text-white dark:text-slate-900' : 'bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-600' }}">
            Все {{ $counts->sum(fn($c) => (int)$c) }}
        </a>
        @for($i = 5; $i >= 1; $i--)
        @php $cnt = $counts->get($i, 0); @endphp
        <a href="{{ route('reviews.index', array_filter(array_merge(['rating' => $i], ['room_id' => request('room_id'), 'search' => request('search'), 'date_from' => request('date_from'), 'date_to' => request('date_to')]))) }}"
           class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-semibold rounded-full transition-colors {{ request('rating') == $i ? 'bg-slate-900 dark:bg-white text-white dark:text-slate-900' : 'bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-600' }}">
            {{ $i }}★ {{ $cnt }}
        </a>
        @endfor
    </div>

    <form method="GET" action="{{ route('reviews.index') }}" class="flex flex-col sm:flex-row gap-3">
        {{-- Search --}}
        <div class="relative flex-1">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"/></svg>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Комментарий или гость..."
                   class="w-full pl-9 pr-4 py-2 text-sm border border-slate-200 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-blue-500 focus:outline-none">
        </div>

        {{-- Room filter --}}
        <select name="room_id" class="px-3 py-2 text-sm border border-slate-200 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-blue-500 focus:outline-none">
            <option value="">Все номера</option>
            @foreach($rooms as $r)
            <option value="{{ $r->id }}" @selected(request('room_id') == $r->id)>№{{ $r->number }} — {{ $r->roomType->name }}</option>
            @endforeach
        </select>

        {{-- Date range --}}
        <input type="date" name="date_from" value="{{ request('date_from') }}" placeholder="От"
               class="px-3 py-2 text-sm border border-slate-200 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-blue-500 focus:outline-none">
        <input type="date" name="date_to" value="{{ request('date_to') }}" placeholder="До"
               class="px-3 py-2 text-sm border border-slate-200 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-blue-500 focus:outline-none">

        <button type="submit" class="px-4 py-2 text-xs font-semibold bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-200 rounded-lg hover:bg-slate-200 dark:hover:bg-slate-600 transition-colors">Фильтр</button>
    </form>
</div>

{{-- Reviews feed --}}
@if($reviews->isEmpty())
<div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm px-6 py-14 text-center">
    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-12 h-12 mx-auto text-slate-300 dark:text-slate-600 mb-3"><path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.562.562 0 0 1 1.04 0l2.125 5.111a.563.563 0 0 0 .475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 0 0-.182.557l1.285 5.385a.562.562 0 0 1-.84.61l-4.725-2.885a.562.562 0 0 0-.586 0L6.982 20.54a.562.562 0 0 1-.84-.61l1.285-5.386a.562.562 0 0 0-.182-.557l-4.204-3.602a.562.562 0 0 1 .321-.988l5.518-.442a.563.563 0 0 0 .475-.345L11.48 3.5Z"/></svg>
    <p class="text-slate-400 dark:text-slate-500 text-sm">Отзывов не найдено</p>
</div>
@else
<div class="space-y-3">
    @foreach($reviews as $review)
    <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden">
        <div class="px-5 py-4 flex items-start justify-between gap-4">
            <div class="flex-1 min-w-0">
                {{-- Header --}}
                <div class="flex items-center gap-2 mb-2 flex-wrap">
                    <span class="text-yellow-400 text-sm">{{ str_repeat('★', $review->rating) }}<span class="text-slate-300 dark:text-slate-600">{{ str_repeat('★', 5 - $review->rating) }}</span></span>
                    <span class="text-xs text-slate-400">{{ $review->submitted_at?->format('d.m.Y H:i') }}</span>

                    {{-- Room badge --}}
                    @if($review->room)
                    <a href="{{ route('rooms.edit', $review->room) }}"
                       class="inline-flex items-center gap-1 px-2 py-0.5 text-xs font-semibold bg-blue-50 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400 rounded-md hover:bg-blue-100 transition-colors">
                        №{{ $review->room->number }}
                    </a>
                    @endif

                    {{-- Guest --}}
                    @if($review->guest)
                    <a href="{{ route('guests.show', $review->guest) }}" class="text-xs text-slate-600 dark:text-slate-400 hover:text-blue-600 transition-colors">
                        {{ $review->guest->fullName }}
                    </a>
                    @endif

                    {{-- Booking ref --}}
                    @if($review->booking)
                    <a href="{{ route('bookings.show', $review->booking) }}" class="text-xs text-slate-400 font-mono hover:text-blue-600">
                        ({{ $review->booking->booking_ref }})
                    </a>
                    @endif
                </div>

                {{-- Photos --}}
                @if($review->photos)
                <div class="flex gap-2 mb-3">
                    @foreach(explode(',', $review->photos) as $photo)
                    <a href="{{ asset('storage/' . trim($photo)) }}" target="_blank" class="rounded-lg overflow-hidden border border-slate-200 dark:border-slate-600 hover:opacity-90 transition-opacity flex-shrink-0">
                        <img src="{{ asset('storage/' . trim($photo)) }}" class="w-20 h-20 object-cover" alt="">
                    </a>
                    @endforeach
                </div>
                @endif

                {{-- Comment --}}
                @if($review->comment)
                <p class="text-sm text-slate-700 dark:text-slate-300 whitespace-pre-wrap">{{ $review->comment }}</p>
                @else
                <p class="text-xs text-slate-400 italic">Без комментария</p>
                @endif
            </div>

            {{-- Delete (owner only) --}}
            @if(auth()->user()->role->value === 'owner')
            <form method="POST" action="{{ route('reviews.destroy', $review) }}" onsubmit="return confirm('Удалить отзыв?')">
                @csrf @method('DELETE')
                <button type="submit" class="text-slate-400 hover:text-red-600 transition-colors flex-shrink-0 p-1.5 rounded-lg hover:bg-red-50 dark:hover:bg-red-900/20" title="Удалить">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0"/></svg>
                </button>
            </form>
            @endif
        </div>
    </div>
    @endforeach
</div>

{{-- Pagination --}}
@if($reviews->hasPages())
<div class="mt-4">
    {{ $reviews->links() }}
</div>
@endif
@endif
@endsection
