@extends('layouts.app')

@section('title', 'Заявка #{{ $maintenance->id }}')

@section('content')
@php
$priorityColors = [
    'low'    => 'bg-slate-100 text-slate-600 ring-1 ring-slate-200',
    'medium' => 'bg-yellow-50 text-yellow-700 ring-1 ring-yellow-200',
    'high'   => 'bg-orange-50 text-orange-700 ring-1 ring-orange-200',
    'urgent' => 'bg-red-50 text-red-700 ring-1 ring-red-200',
];
$statusColors = [
    'open'        => 'bg-red-50 text-red-700 ring-1 ring-red-200',
    'in_progress' => 'bg-yellow-50 text-yellow-700 ring-1 ring-yellow-200',
    'resolved'    => 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200',
];
@endphp

<div class="max-w-2xl mx-auto">

    <div class="mb-6 flex items-center gap-3">
        <a href="{{ route('maintenance.index') }}"
           class="inline-flex items-center gap-1.5 text-sm text-slate-500 hover:text-slate-700 transition-colors">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18"/></svg>
            Техобслуживание
        </a>
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 text-slate-300"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5"/></svg>
        <h1 class="text-xl font-bold text-slate-900">Заявка #{{ $maintenance->id }}</h1>
    </div>

    <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-6 mb-4">
        <div class="flex items-start justify-between gap-4 mb-4">
            <div>
                <div class="flex items-center gap-2 mb-1">
                    <h2 class="text-lg font-bold text-slate-900">{{ $maintenance->title }}</h2>
                    @if($maintenance->category)
                    <span class="text-xs bg-slate-100 text-slate-500 px-2 py-0.5 rounded-full font-medium">{{ ucfirst($maintenance->category) }}</span>
                    @endif
                </div>
                <p class="text-sm text-slate-500 mt-0.5">Номер {{ $maintenance->room->number }}, этаж {{ $maintenance->room->floor }}</p>
            </div>
            <div class="flex items-center gap-2 flex-shrink-0">
                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold {{ $priorityColors[$maintenance->priority->value] }}">
                    {{ $maintenance->priority->label() }}
                </span>
                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold {{ $statusColors[$maintenance->status->value] }}">
                    {{ $maintenance->status->label() }}
                </span>
            </div>
        </div>

        @if($maintenance->description)
        <p class="text-sm text-slate-600 leading-relaxed mb-4">{{ $maintenance->description }}</p>
        @endif

        {{-- Photos from guest --}}
        @if($maintenance->photos)
        <div class="grid grid-cols-3 gap-3 mb-4">
            @foreach(explode(',', $maintenance->photos) as $photo)
            <a href="{{ asset('storage/' . trim($photo)) }}" target="_blank" class="rounded-lg overflow-hidden border border-slate-200 hover:opacity-90 transition-opacity">
                <img src="{{ asset('storage/' . trim($photo)) }}" class="w-full h-28 object-cover" alt="">
            </a>
            @endforeach
        </div>
        @endif

        <div class="grid grid-cols-2 gap-4 text-sm border-t border-slate-100 pt-4">
            <div>
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Создал</p>
                @if($maintenance->guest)
                    <p class="text-slate-700">{{ $maintenance->guest->full_name }} <span class="text-xs text-amber-600 font-medium">(гость)</span></p>
                    @if($maintenance->booking)
                    <p class="text-xs text-slate-400">Бронь: <span class="font-mono">{{ $maintenance->booking->booking_ref }}</span></p>
                    @endif
                @elseif($maintenance->creator)
                    <p class="text-slate-700">{{ $maintenance->creator->name }}</p>
                    <p class="text-xs text-slate-400 font-mono">{{ $maintenance->created_at->format('d.m.Y H:i') }}</p>
                @else
                    <p class="text-slate-400 italic">—</p>
                @endif
            </div>
            <div>
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Назначен</p>
                @if($maintenance->assignee)
                    <p class="text-slate-700">{{ $maintenance->assignee->name }}</p>
                @else
                    <p class="text-slate-400 italic">Не назначен</p>
                @endif
            </div>
            @if($maintenance->resolved_at)
            <div>
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Решено</p>
                <p class="text-xs text-slate-400 font-mono">{{ $maintenance->resolved_at->format('d.m.Y H:i') }}</p>
            </div>
            @endif
        </div>
    </div>

    <div class="flex items-center gap-3">
        @if($maintenance->status->value !== 'resolved')
            @can('manage', $maintenance)
            <form method="POST" action="{{ route('maintenance.resolve', $maintenance) }}">
                @csrf @method('PATCH')
                <button type="submit"
                        class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-600 text-white text-sm font-semibold rounded-lg hover:bg-emerald-700 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                    Отметить решённым
                </button>
            </form>
            @endcan
        @endif

        @if(auth()->user()->role->value !== 'receptionist' || $maintenance->created_by === auth()->id())
        <a href="{{ route('maintenance.edit', $maintenance) }}"
           class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-blue-700 bg-blue-50 rounded-lg hover:bg-blue-100 transition-colors">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125"/></svg>
            Редактировать
        </a>
        @endif
    </div>
</div>
@endsection
