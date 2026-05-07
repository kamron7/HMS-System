@extends('layouts.app')

@section('title', 'Найдённые вещи')

@section('content')
<div class="flex items-center justify-between mb-6">
    <h1 class="text-2xl font-bold text-slate-900 dark:text-slate-100">
        Журнал находок
        <span class="ml-2 text-base font-normal text-slate-400 dark:text-slate-500">({{ $items->total() }})</span>
    </h1>
    <a href="{{ route('lost-items.create') }}"
       class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white text-sm font-semibold rounded-lg hover:bg-blue-700 transition-colors shadow-sm">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
        Добавить вещь
    </a>
</div>

{{-- Filters --}}
<div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm p-4 mb-6">
    <form method="GET" action="{{ route('lost-items.index') }}" class="flex flex-col sm:flex-row gap-3">
        <div class="relative flex-1">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"/></svg>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Название, описание, место хранения..."
                   class="w-full pl-9 pr-4 py-2 text-sm border border-slate-200 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-blue-500 focus:outline-none">
        </div>
        <div class="flex items-center gap-1.5 flex-wrap">
            <a href="{{ route('lost-items.index', ['search' => request('search')]) }}"
               class="px-3 py-1.5 text-xs font-semibold rounded-full transition-colors {{ !request('status') ? 'bg-slate-900 dark:bg-white text-white dark:text-slate-900' : 'bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-600' }}">
                Все {{ $counts->sum(fn($c) => (int)$c) }}
            </a>
            @foreach([
                ['value' => 'found',     'label' => 'Найдено',      'dot' => 'bg-blue-500'],
                ['value' => 'stored',    'label' => 'На хранении',  'dot' => 'bg-yellow-400'],
                ['value' => 'returned',  'label' => 'Возвращено',   'dot' => 'bg-emerald-500'],
                ['value' => 'discarded', 'label' => 'Утилизировано','dot' => 'bg-gray-400'],
            ] as $f)
            @php $cnt = $counts->get($f['value'], 0); @endphp
            <a href="{{ route('lost-items.index', array_filter(['status' => $f['value'], 'search' => request('search')])) }}"
               class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold rounded-full transition-colors {{ request('status') === $f['value'] ? 'bg-slate-900 dark:bg-white text-white dark:text-slate-900' : 'bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-600' }}">
                <span class="w-2 h-2 rounded-full {{ $f['dot'] }}"></span>
                {{ $f['label'] }} {{ $cnt }}
            </a>
            @endforeach
        </div>
    </form>
</div>

{{-- Items table --}}
<div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden">
    <table class="w-full text-sm">
        <thead>
            <tr class="bg-slate-50 dark:bg-slate-900/50 border-b border-slate-200 dark:border-slate-700">
                <th class="text-left px-5 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Вещь</th>
                <th class="text-left px-5 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Номер</th>
                <th class="text-left px-5 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Гость</th>
                <th class="text-left px-5 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Где хранится</th>
                <th class="text-left px-5 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Статус</th>
                <th class="text-left px-5 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Дата</th>
                <th class="px-5 py-3"></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
            @forelse($items as $item)
            <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors">
                <td class="px-5 py-3.5">
                    <div class="flex items-center gap-2">
                        @if($item->photos)
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 text-slate-400 flex-shrink-0"><path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909M3.75 21h16.5A2.25 2.25 0 0 0 22.5 18.75V5.25A2.25 2.25 0 0 0 20.25 3H3.75A2.25 2.25 0 0 0 1.5 5.25v13.5A2.25 2.25 0 0 0 3.75 21Z"/></svg>
                        @endif
                        <p class="font-medium text-slate-800 dark:text-white">{{ $item->title }}</p>
                    </div>
                </td>
                <td class="px-5 py-3.5 text-slate-600 dark:text-slate-300 font-mono text-xs">{{ $item->room->number ?? '—' }}</td>
                <td class="px-5 py-3.5">
                    @if($item->guest)
                    <a href="{{ route('guests.show', $item->guest) }}" class="text-blue-600 hover:text-blue-700 text-xs">{{ $item->guest->fullName }}</a>
                    @else
                    <span class="text-slate-400 text-xs">—</span>
                    @endif
                </td>
                <td class="px-5 py-3.5 text-slate-500 text-xs">{{ $item->storage_location ?? '—' }}</td>
                <td class="px-5 py-3.5">
                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-semibold
                        @if($item->status->color() === 'blue') bg-blue-50 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400
                        @elseif($item->status->color() === 'yellow') bg-yellow-50 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400
                        @elseif($item->status->color() === 'green') bg-emerald-50 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400
                        @else bg-gray-50 text-gray-600 dark:bg-gray-900/30 dark:text-gray-400 @endif">
                        {{ $item->status->label() }}
                    </span>
                </td>
                <td class="px-5 py-3.5 text-slate-400 text-xs font-mono">{{ $item->found_at->format('d.m.Y') }}</td>
                <td class="px-5 py-3.5 text-right">
                    <a href="{{ route('lost-items.show', $item) }}" class="text-xs text-blue-600 hover:underline font-medium">Открыть</a>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="px-5 py-14 text-center">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-10 h-10 mx-auto text-slate-300 dark:text-slate-600 mb-3"><path stroke-linecap="round" stroke-linejoin="round" d="m20.25 7.5-.625 10.632a2.25 2.25 0 0 1-2.247 2.118H6.622a2.25 2.25 0 0 1-2.247-2.118L3.75 7.5m6 4.125 2.25 2.25m0 0 2.25 2.25M12 13.875l2.25-2.25M12 13.875l-2.25 2.25M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125Z"/></svg>
                    <p class="text-slate-400 dark:text-slate-500 text-sm">Нет записей</p>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

{{-- Pagination --}}
@if($items->hasPages())
<div class="mt-4">
    {{ $items->links() }}
</div>
@endif
@endsection
