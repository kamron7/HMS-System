@extends('layouts.app')
@section('title', 'Источники бронирований')
@section('content')

@include('reports._header', ['title' => 'Источники бронирований'])

<div class="flex flex-wrap items-center gap-3 mb-6">
    @include('reports._period_filter', ['route' => 'reports.sources'])
    <div class="ml-auto">
        <a href="{{ route('reports.sources', ['period' => $period, 'export' => 'csv']) }}"
           class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-semibold bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-3.5 h-3.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg> CSV
        </a>
    </div>
</div>

@php
$staffPct  = $total > 0 ? round($staffCount  / $total * 100) : 0;
$clientPct = $total > 0 ? round($clientCount / $total * 100) : 0;
@endphp

<div class="grid grid-cols-1 md:grid-cols-5 gap-5">

    {{-- Doughnut --}}
    <div class="md:col-span-2 bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm p-6 flex flex-col">
        <p class="text-sm font-bold text-slate-900 dark:text-white mb-5">Распределение</p>

        @if($total > 0)
        <div class="flex justify-center mb-6">
            <div class="relative" style="width:190px;height:190px;">
                <canvas id="srcDonut" width="190" height="190"></canvas>
                <div class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none">
                    <span class="text-2xl font-black text-slate-900 dark:text-white tabular-nums">{{ $total }}</span>
                    <span class="text-xs font-semibold text-slate-400 dark:text-slate-500 uppercase tracking-wide mt-0.5">броней</span>
                </div>
            </div>
        </div>
        <div class="space-y-3 mt-auto">
            <div class="flex items-center gap-3">
                <span class="w-3 h-3 rounded-full bg-blue-500 flex-shrink-0"></span>
                <span class="text-sm text-slate-600 dark:text-slate-400 flex-1">Персонал</span>
                <span class="text-sm font-bold text-slate-900 dark:text-white tabular-nums">{{ $staffCount }}</span>
                <span class="text-xs text-slate-400 dark:text-slate-500 w-8 text-right tabular-nums">{{ $staffPct }}%</span>
            </div>
            <div class="flex items-center gap-3">
                <span class="w-3 h-3 rounded-full bg-violet-500 flex-shrink-0"></span>
                <span class="text-sm text-slate-600 dark:text-slate-400 flex-1">Клиентский портал</span>
                <span class="text-sm font-bold text-slate-900 dark:text-white tabular-nums">{{ $clientCount }}</span>
                <span class="text-xs text-slate-400 dark:text-slate-500 w-8 text-right tabular-nums">{{ $clientPct }}%</span>
            </div>
        </div>
        @else
        <div class="flex-1 flex items-center justify-center text-slate-400 dark:text-slate-500 text-sm">
            Нет данных за выбранный период
        </div>
        @endif
    </div>

    {{-- Stat cards --}}
    <div class="md:col-span-3 flex flex-col gap-4">

        {{-- Staff --}}
        <div class="flex-1 bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm p-6 relative overflow-hidden">
            <div class="absolute -top-8 -right-8 w-32 h-32 rounded-full bg-blue-50 dark:bg-blue-900/20 pointer-events-none"></div>
            <div class="flex items-start gap-4 relative">
                <div class="w-12 h-12 rounded-xl bg-blue-50 dark:bg-blue-900/30 border border-blue-100 dark:border-blue-800/50 flex items-center justify-center flex-shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6 text-blue-600 dark:text-blue-400">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z"/>
                    </svg>
                </div>
                <div class="flex-1">
                    <p class="text-xs font-semibold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-1.5">Персонал</p>
                    <p class="text-4xl font-black text-slate-900 dark:text-white tabular-nums">{{ $staffCount }}</p>
                    <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">бронирований</p>
                </div>
                <div class="text-right flex-shrink-0">
                    <span class="text-3xl font-black text-blue-600 dark:text-blue-400 tabular-nums">{{ $staffPct }}%</span>
                </div>
            </div>
            @if($total > 0)
            <div class="mt-5 bg-slate-100 dark:bg-slate-700 rounded-full h-2 overflow-hidden">
                <div class="bg-blue-500 h-2 rounded-full transition-all duration-700" style="width:{{ $staffPct }}%"></div>
            </div>
            @endif
        </div>

        {{-- Client portal --}}
        <div class="flex-1 bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm p-6 relative overflow-hidden">
            <div class="absolute -top-8 -right-8 w-32 h-32 rounded-full bg-violet-50 dark:bg-violet-900/20 pointer-events-none"></div>
            <div class="flex items-start gap-4 relative">
                <div class="w-12 h-12 rounded-xl bg-violet-50 dark:bg-violet-900/30 border border-violet-100 dark:border-violet-800/50 flex items-center justify-center flex-shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6 text-violet-600 dark:text-violet-400">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 1.5H8.25A2.25 2.25 0 0 0 6 3.75v16.5a2.25 2.25 0 0 0 2.25 2.25h7.5A2.25 2.25 0 0 0 18 20.25V3.75a2.25 2.25 0 0 0-2.25-2.25H13.5m-3 0V3h3V1.5m-3 0h3m-3 18.75h3"/>
                    </svg>
                </div>
                <div class="flex-1">
                    <p class="text-xs font-semibold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-1.5">Клиентский портал</p>
                    <p class="text-4xl font-black text-slate-900 dark:text-white tabular-nums">{{ $clientCount }}</p>
                    <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">бронирований</p>
                </div>
                <div class="text-right flex-shrink-0">
                    <span class="text-3xl font-black text-violet-600 dark:text-violet-400 tabular-nums">{{ $clientPct }}%</span>
                </div>
            </div>
            @if($total > 0)
            <div class="mt-5 bg-slate-100 dark:bg-slate-700 rounded-full h-2 overflow-hidden">
                <div class="bg-violet-500 h-2 rounded-full transition-all duration-700" style="width:{{ $clientPct }}%"></div>
            </div>
            @endif
        </div>
    </div>
</div>

@if($total > 0)
@push('scripts')
<script>
(function () {
    const isDark = document.documentElement.classList.contains('dark');
    const tc = isDark ? '#64748b' : '#94a3b8';

    new Chart(document.getElementById('srcDonut'), {
        type: 'doughnut',
        data: {
            labels: ['Персонал', 'Клиентский портал'],
            datasets: [{
                data: [{{ $staffCount }}, {{ $clientCount }}],
                backgroundColor: ['#3b82f6', '#8b5cf6'],
                borderWidth: 3,
                borderColor: isDark ? '#1e293b' : '#fff',
                hoverOffset: 6,
            }]
        },
        options: {
            responsive: false,
            cutout: '66%',
            animation: { animateRotate: true, duration: 800 },
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: isDark ? '#1e293b' : '#0f172a',
                    titleColor: tc,
                    bodyColor: isDark ? '#e2e8f0' : '#f1f5f9',
                    borderColor: 'rgba(255,255,255,0.08)',
                    borderWidth: 1,
                    padding: 12,
                    cornerRadius: 10,
                    callbacks: {
                        label: c => '  ' + c.raw + ' броней (' + c.label + ')',
                    }
                }
            }
        }
    });
})();
</script>
@endpush
@endif

@endsection
