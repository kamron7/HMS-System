@extends('layouts.app')
@section('title', 'Загрузка номеров')
@section('content')

@include('reports._header', ['title' => 'Загрузка номеров'])

<div class="flex flex-wrap items-center gap-3 mb-6">
    @include('reports._period_filter', ['route' => 'reports.occupancy'])
    <div class="ml-auto flex items-center gap-2">
        <a href="{{ route('reports.occupancy', ['period' => $period, 'export' => 'csv']) }}"
           class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-semibold bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-3.5 h-3.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg> CSV
        </a>
        <a href="{{ route('reports.occupancy', ['period' => $period, 'export' => 'pdf']) }}"
           class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-semibold bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">
            PDF
        </a>
    </div>
</div>

{{-- Top: gauge + stats --}}
<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-5">
    {{-- Gauge card --}}
    <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm p-6 flex flex-col items-center justify-center text-center">
        <p class="text-xs font-semibold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-4">Средняя загрузка</p>
        <div class="relative" style="width:140px;height:140px;">
            <svg viewBox="0 0 100 100" style="width:140px;height:140px;transform:rotate(-90deg);">
                <circle cx="50" cy="50" r="40" fill="none" stroke="{{ request()->is('*') && true ? '#f1f5f9' : '#1e293b' }}" stroke-width="10" class="text-slate-100 dark:text-slate-700" style="stroke:currentColor"/>
                <circle cx="50" cy="50" r="40" fill="none" stroke="#3b82f6" stroke-width="10"
                    stroke-linecap="round"
                    stroke-dasharray="{{ round($avgPct * 2.513) }} 251.3"
                    style="transition:stroke-dasharray 1s ease;"/>
            </svg>
            <div class="absolute inset-0 flex flex-col items-center justify-center">
                <span class="text-3xl font-black text-slate-900 dark:text-white">{{ $avgPct }}<span class="text-lg">%</span></span>
            </div>
        </div>
        @php
        $label = $avgPct >= 80 ? 'Высокая' : ($avgPct >= 50 ? 'Средняя' : 'Низкая');
        $labelColor = $avgPct >= 80 ? 'text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-900/30' : ($avgPct >= 50 ? 'text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-900/30' : 'text-slate-600 dark:text-slate-400 bg-slate-100 dark:bg-slate-700');
        @endphp
        <span class="mt-3 text-xs font-bold px-3 py-1 rounded-full {{ $labelColor }}">{{ $label }}</span>
    </div>

    {{-- Stat cards --}}
    <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm p-6 flex flex-col justify-center">
        <div class="w-10 h-10 rounded-xl bg-blue-50 dark:bg-blue-900/30 border border-blue-100 dark:border-blue-800/50 flex items-center justify-center mb-4">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-blue-600 dark:text-blue-400">
                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21"/>
            </svg>
        </div>
        <p class="text-xs font-semibold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-1.5">Всего номеров</p>
        <p class="text-4xl font-black text-slate-900 dark:text-white">{{ $totalRooms }}</p>
    </div>

    <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm p-6 flex flex-col justify-center">
        <div class="w-10 h-10 rounded-xl bg-blue-50 dark:bg-blue-900/30 border border-blue-100 dark:border-blue-800/50 flex items-center justify-center mb-4">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-blue-600 dark:text-blue-400">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5"/>
            </svg>
        </div>
        <p class="text-xs font-semibold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-1.5">Дней в периоде</p>
        <p class="text-4xl font-black text-slate-900 dark:text-white">{{ count($rows) }}</p>
    </div>
</div>

{{-- Area chart --}}
@if(count($rows) > 0)
<div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm p-5 mb-5">
    <div class="flex items-center justify-between mb-5">
        <div>
            <p class="text-sm font-bold text-slate-900 dark:text-white">Динамика загрузки</p>
            <p class="text-xs text-slate-400 dark:text-slate-500 mt-0.5">% занятых номеров по дням</p>
        </div>
        <div class="flex items-center gap-4 text-xs text-slate-400 dark:text-slate-500">
            <span class="flex items-center gap-1.5"><span class="w-8 h-0.5 bg-blue-500 rounded inline-block"></span>Факт</span>
            <span class="flex items-center gap-1.5"><span class="w-8 border-t-2 border-dashed border-amber-400 inline-block"></span>Цель 75%</span>
        </div>
    </div>
    <div style="position:relative;height:280px;"><canvas id="occChart"></canvas></div>
</div>
@endif

{{-- Table --}}
<div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden"
     x-data="tablePager({{ count($rows) }}, 31)">
    <div class="px-5 py-4 border-b border-slate-100 dark:border-slate-700/50">
        <p class="text-sm font-semibold text-slate-700 dark:text-slate-300">Детализация по дням</p>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-slate-50 dark:bg-slate-800/80 border-b border-slate-100 dark:border-slate-700/50">
                    <th class="text-left px-5 py-3 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Дата</th>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Занято</th>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Всего</th>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider w-56">Загрузка</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-700/50">
                @foreach($rows as $i => $row)
                @php
                $col = $row['pct'] >= 80 ? ['bar'=>'bg-emerald-500','text'=>'text-emerald-600 dark:text-emerald-400']
                     : ($row['pct'] >= 50 ? ['bar'=>'bg-blue-500',   'text'=>'text-blue-600 dark:text-blue-400']
                     :                      ['bar'=>'bg-slate-300 dark:bg-slate-600','text'=>'text-slate-500 dark:text-slate-400']);
                @endphp
                <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/30 transition-colors" x-show="show({{ $i }})">
                    <td class="px-5 py-3.5 text-slate-500 dark:text-slate-400 font-mono text-xs">{{ $row['date'] }}</td>
                    <td class="px-5 py-3.5 font-bold text-slate-900 dark:text-white tabular-nums">{{ $row['booked'] }}</td>
                    <td class="px-5 py-3.5 text-slate-500 dark:text-slate-400">{{ $row['total'] }}</td>
                    <td class="px-5 py-3.5">
                        <div class="flex items-center gap-3">
                            <div class="flex-1 bg-slate-100 dark:bg-slate-700 rounded-full h-2 overflow-hidden">
                                <div class="{{ $col['bar'] }} h-2 rounded-full transition-all" style="width:{{ $row['pct'] }}%"></div>
                            </div>
                            <span class="text-xs font-bold {{ $col['text'] }} w-10 text-right tabular-nums">{{ $row['pct'] }}%</span>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @include('reports._pagination')
</div>

@if(count($rows) > 0)
@push('scripts')
<script>
(function () {
    const isDark = document.documentElement.classList.contains('dark');
    const gc = isDark ? 'rgba(255,255,255,0.05)' : 'rgba(0,0,0,0.04)';
    const tc = isDark ? '#64748b' : '#94a3b8';

    const labels = @json(array_column($rows, 'date'));
    const pcts   = @json(array_column($rows, 'pct'));

    const ctx  = document.getElementById('occChart');
    const gCtx = ctx.getContext('2d');
    const grad = gCtx.createLinearGradient(0, 0, 0, 280);
    grad.addColorStop(0, 'rgba(59,130,246,0.22)');
    grad.addColorStop(1, 'rgba(59,130,246,0.01)');

    new Chart(ctx, {
        type: 'line',
        data: {
            labels,
            datasets: [
                {
                    label: 'Загрузка',
                    data: pcts,
                    borderColor: '#3b82f6',
                    borderWidth: 2,
                    backgroundColor: grad,
                    fill: true,
                    tension: 0.4,
                    pointRadius: pcts.length > 40 ? 0 : 3,
                    pointHoverRadius: 5,
                    pointBackgroundColor: '#fff',
                    pointBorderColor: '#3b82f6',
                    pointBorderWidth: 2,
                },
                {
                    label: 'Цель 75%',
                    data: labels.map(() => 75),
                    borderColor: 'rgba(251,191,36,0.6)',
                    borderWidth: 1.5,
                    borderDash: [6, 4],
                    pointRadius: 0,
                    fill: false,
                    tension: 0,
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            animation: { duration: 700, easing: 'easeOutQuart' },
            interaction: { intersect: false, mode: 'index' },
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
                        label: c => c.datasetIndex === 1 ? null : '  ' + c.raw + '% занято',
                    },
                    filter: item => item.datasetIndex === 0,
                }
            },
            scales: {
                x: {
                    grid: { display: false },
                    border: { display: false },
                    ticks: { color: tc, font: { size: 11 }, maxTicksLimit: 14 },
                },
                y: {
                    min: 0, max: 100,
                    grid: { color: gc, drawBorder: false },
                    border: { display: false },
                    ticks: { color: tc, font: { size: 11 }, callback: v => v + '%', stepSize: 25 },
                }
            }
        }
    });
})();
</script>
@endpush
@endif

@endsection
