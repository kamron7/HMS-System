@extends('layouts.app')

@section('title', 'Прогноз загрузки')

@section('content')

<div class="flex items-center gap-3 mb-6">
    <a href="{{ route('reports.index') }}"
       class="inline-flex items-center gap-1.5 text-sm text-slate-500 hover:text-slate-700 transition-colors">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18"/></svg>
        Отчёты
    </a>
    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 text-slate-300"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5"/></svg>
    <h1 class="text-xl font-bold text-slate-900">Прогноз загрузки — 90 дней</h1>
</div>

{{-- Legend --}}
<div class="flex items-center gap-4 mb-5 text-xs text-slate-500">
    <span class="font-medium">Загрузка:</span>
    <div class="flex items-center gap-1.5">
        <div class="w-4 h-4 rounded bg-white border border-slate-200"></div><span>0%</span>
    </div>
    <div class="flex items-center gap-1.5">
        <div class="w-4 h-4 rounded bg-blue-100"></div><span>1–25%</span>
    </div>
    <div class="flex items-center gap-1.5">
        <div class="w-4 h-4 rounded bg-blue-300"></div><span>26–50%</span>
    </div>
    <div class="flex items-center gap-1.5">
        <div class="w-4 h-4 rounded bg-blue-500"></div><span>51–75%</span>
    </div>
    <div class="flex items-center gap-1.5">
        <div class="w-4 h-4 rounded bg-blue-700"></div><span>76–100%</span>
    </div>
</div>

@php
function forecastColor(int $pct): string {
    if ($pct === 0)       return 'bg-white border border-slate-200';
    if ($pct <= 25)       return 'bg-blue-100';
    if ($pct <= 50)       return 'bg-blue-300';
    if ($pct <= 75)       return 'bg-blue-500';
    return 'bg-blue-700';
}
function forecastText(int $pct): string {
    return $pct > 50 ? 'text-white' : 'text-slate-700';
}
@endphp

<div class="space-y-6">
@foreach($months as $monthLabel => $days)
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5">
        <p class="text-sm font-semibold text-slate-700 mb-3 capitalize">{{ $monthLabel }}</p>

        {{-- Weekday header --}}
        <div class="grid grid-cols-7 gap-1 mb-1">
            @foreach(['Пн','Вт','Ср','Чт','Пт','Сб','Вс'] as $wd)
            <div class="text-center text-xs font-medium text-slate-400">{{ $wd }}</div>
            @endforeach
        </div>

        @php
            $firstDay = $days->first();
            $offset   = $firstDay['weekday'] - 1; // 0=Mon
        @endphp

        <div class="grid grid-cols-7 gap-1">
            {{-- Empty offset cells --}}
            @for($o = 0; $o < $offset; $o++)
            <div></div>
            @endfor

            @foreach($days as $day)
            <div x-data
                 class="relative rounded-md h-10 flex flex-col items-center justify-center cursor-default {{ forecastColor($day['pct']) }} {{ forecastText($day['pct']) }}"
                 x-tooltip.raw="{{ $day['booked'] }} из {{ $day['total'] }} номеров">
                <span class="text-xs font-semibold leading-none">{{ $day['label'] }}</span>
                @if($day['pct'] > 0)
                <span class="text-[9px] leading-none mt-0.5 opacity-80">{{ $day['pct'] }}%</span>
                @endif
            </div>
            @endforeach
        </div>
    </div>
@endforeach
</div>

<script>
// Simple tooltip via title attribute fallback
document.querySelectorAll('[x-tooltip\\.raw]').forEach(el => {
    el.title = el.getAttribute('x-tooltip.raw');
});
</script>

@endsection
