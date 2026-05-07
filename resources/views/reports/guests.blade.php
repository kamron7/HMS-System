@extends('layouts.app')
@section('title', 'Статистика гостей')
@section('content')

@include('reports._header', ['title' => 'Статистика гостей'])

<div class="flex flex-wrap items-center gap-3 mb-6">
    @include('reports._period_filter', ['route' => 'reports.guests'])
    <div class="ml-auto">
        <a href="{{ route('reports.guests', ['period' => $period, 'export' => 'csv']) }}"
           class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-semibold bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-3.5 h-3.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg> CSV
        </a>
    </div>
</div>

@php
$total    = $newGuests + $repeatGuests;
$newPct   = $total > 0 ? round($newGuests   / $total * 100) : 0;
$repPct   = $total > 0 ? round($repeatGuests / $total * 100) : 0;
$natLabels = $nationalityBreakdown->keys()->toArray();
$natData   = $nationalityBreakdown->values()->toArray();
$maxNat    = $nationalityBreakdown->max() ?: 1;
@endphp

<div class="grid grid-cols-1 lg:grid-cols-2 gap-5">

    {{-- ── New vs Repeat ──────────────────────────────────────────────────── --}}
    <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm p-6">
        <p class="text-sm font-bold text-slate-900 dark:text-white mb-5">Новые и повторные гости</p>

        {{-- Big stat cards --}}
        <div class="grid grid-cols-2 gap-3 mb-6">
            <div class="rounded-xl p-4 bg-blue-50 dark:bg-blue-900/20 border border-blue-100 dark:border-blue-800/40 text-center">
                <p class="text-3xl font-black text-blue-600 dark:text-blue-400 tabular-nums">{{ $newGuests }}</p>
                <p class="text-xs font-semibold text-blue-500 dark:text-blue-400/70 mt-1">Новые</p>
                <p class="text-xs text-blue-400 dark:text-blue-500 mt-0.5">{{ $newPct }}%</p>
            </div>
            <div class="rounded-xl p-4 bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-100 dark:border-emerald-800/40 text-center">
                <p class="text-3xl font-black text-emerald-600 dark:text-emerald-400 tabular-nums">{{ $repeatGuests }}</p>
                <p class="text-xs font-semibold text-emerald-500 dark:text-emerald-400/70 mt-1">Повторные</p>
                <p class="text-xs text-emerald-400 dark:text-emerald-500 mt-0.5">{{ $repPct }}%</p>
            </div>
        </div>

        @if($total > 0)
        <div class="flex items-center gap-6">
            {{-- Doughnut --}}
            <div class="relative flex-shrink-0" style="width:130px;height:130px;">
                <canvas id="guestDonut" width="130" height="130"></canvas>
                <div class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none">
                    <span class="text-xl font-black text-slate-900 dark:text-white">{{ $total }}</span>
                    <span class="text-[10px] font-semibold text-slate-400 dark:text-slate-500 uppercase tracking-wide">всего</span>
                </div>
            </div>
            {{-- Legend bars --}}
            <div class="flex-1 space-y-4">
                <div>
                    <div class="flex justify-between items-center text-xs mb-1.5">
                        <span class="flex items-center gap-1.5 font-semibold text-slate-700 dark:text-slate-300">
                            <span class="w-2.5 h-2.5 rounded-full bg-blue-500 inline-block"></span> Новые
                        </span>
                        <span class="font-bold text-slate-900 dark:text-white tabular-nums">{{ $newPct }}%</span>
                    </div>
                    <div class="bg-slate-100 dark:bg-slate-700 rounded-full h-2 overflow-hidden">
                        <div class="bg-blue-500 h-2 rounded-full transition-all" style="width:{{ $newPct }}%"></div>
                    </div>
                </div>
                <div>
                    <div class="flex justify-between items-center text-xs mb-1.5">
                        <span class="flex items-center gap-1.5 font-semibold text-slate-700 dark:text-slate-300">
                            <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 inline-block"></span> Повторные
                        </span>
                        <span class="font-bold text-slate-900 dark:text-white tabular-nums">{{ $repPct }}%</span>
                    </div>
                    <div class="bg-slate-100 dark:bg-slate-700 rounded-full h-2 overflow-hidden">
                        <div class="bg-emerald-500 h-2 rounded-full transition-all" style="width:{{ $repPct }}%"></div>
                    </div>
                </div>
            </div>
        </div>
        @else
        <div class="flex items-center justify-center h-32 text-slate-400 dark:text-slate-500 text-sm">
            Нет данных за выбранный период
        </div>
        @endif
    </div>

    {{-- ── Nationality ──────────────────────────────────────────────────── --}}
    <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm p-6">
        <div class="flex items-center justify-between mb-5">
            <p class="text-sm font-bold text-slate-900 dark:text-white">Национальность</p>
            <span class="text-xs text-slate-400 dark:text-slate-500">топ-10</span>
        </div>

        @if($nationalityBreakdown->isNotEmpty())
        <div style="position:relative;height:{{ max(160, count($natLabels) * 36) }}px;"><canvas id="natChart"></canvas></div>
        @else
        <div class="flex items-center justify-center h-40 text-slate-400 dark:text-slate-500 text-sm">
            Нет данных о национальности
        </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
(function () {
    const isDark = document.documentElement.classList.contains('dark');
    const gc = isDark ? 'rgba(255,255,255,0.05)' : 'rgba(0,0,0,0.04)';
    const tc = isDark ? '#64748b' : '#94a3b8';

    @if($total > 0)
    new Chart(document.getElementById('guestDonut'), {
        type: 'doughnut',
        data: {
            labels: ['Новые', 'Повторные'],
            datasets: [{
                data: [{{ $newGuests }}, {{ $repeatGuests }}],
                backgroundColor: ['#3b82f6', '#10b981'],
                borderWidth: 3,
                borderColor: isDark ? '#1e293b' : '#fff',
                hoverOffset: 5,
            }]
        },
        options: {
            responsive: false,
            cutout: '65%',
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
                    callbacks: { label: c => '  ' + c.raw + ' гостей' }
                }
            }
        }
    });
    @endif

    @if($nationalityBreakdown->isNotEmpty())
    const natCanvas = document.getElementById('natChart');
    const natLabels = @json($natLabels);
    const natData   = @json($natData);

    new Chart(natCanvas, {
        type: 'bar',
        data: {
            labels: natLabels,
            datasets: [{
                data: natData,
                backgroundColor: 'rgba(99,102,241,0.65)',
                hoverBackgroundColor: 'rgba(99,102,241,1)',
                borderRadius: 5,
                borderSkipped: false,
                barPercentage: 0.65,
            }]
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            maintainAspectRatio: false,
            animation: { duration: 700, easing: 'easeOutQuart' },
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
                    callbacks: { label: c => '  ' + c.raw + ' гостей' }
                }
            },
            scales: {
                x: {
                    grid: { color: gc, drawBorder: false },
                    border: { display: false },
                    ticks: { color: tc, font: { size: 11 }, stepSize: 1 },
                },
                y: {
                    grid: { display: false },
                    border: { display: false },
                    ticks: { color: isDark ? '#cbd5e1' : '#475569', font: { size: 12 } },
                }
            }
        }
    });
    @endif
})();
</script>
@endpush

@endsection
