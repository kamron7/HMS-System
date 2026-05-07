@extends('layouts.app')
@section('title', 'Расходы')
@section('content')

@include('reports._header', ['title' => 'Расходы по категориям'])

<div class="flex flex-wrap items-center gap-3 mb-6">
    @include('reports._period_filter', ['route' => 'reports.expenses'])
    <div class="ml-auto flex items-center gap-2">
        <a href="{{ route('reports.expenses', ['period' => $period, 'export' => 'csv']) }}"
           class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-semibold bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-3.5 h-3.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg> CSV
        </a>
        <a href="{{ route('reports.expenses', ['period' => $period, 'export' => 'pdf']) }}"
           class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-semibold bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">
            PDF
        </a>
    </div>
</div>

@php
$palette = ['#f97316','#3b82f6','#8b5cf6','#10b981','#f59e0b','#ec4899','#06b6d4','#64748b'];
$cats = $byCategory->keys()->values()->toArray();
$vals = $byCategory->values()->map(fn($v) => round($v))->toArray();
@endphp

@if($byCategory->isNotEmpty())
{{-- Chart + breakdown --}}
<div class="grid grid-cols-1 md:grid-cols-5 gap-5 mb-5">

    {{-- Doughnut --}}
    <div class="md:col-span-2 bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm p-6">
        <p class="text-sm font-bold text-slate-900 dark:text-white mb-5">Структура расходов</p>
        <div class="flex justify-center mb-5">
            <div class="relative" style="width:190px;height:190px;">
                <canvas id="expDonut" width="190" height="190"></canvas>
                <div class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none">
                    <span class="text-xs font-semibold text-slate-400 dark:text-slate-500 uppercase tracking-wide">Итого</span>
                    <span class="text-xl font-black text-slate-900 dark:text-white mt-1">{{ number_format($total/1000000, 1) }}М</span>
                </div>
            </div>
        </div>
        <div class="space-y-2.5">
            @foreach($byCategory as $cat => $sum)
            @php $i = $loop->index % count($palette); @endphp
            <div class="flex items-center gap-2.5">
                <span class="w-2.5 h-2.5 rounded-full flex-shrink-0" style="background:{{ $palette[$i] }}"></span>
                <span class="text-xs text-slate-600 dark:text-slate-400 flex-1 truncate capitalize">{{ $cat }}</span>
                <span class="text-xs font-bold text-slate-800 dark:text-slate-200 tabular-nums">{{ $total > 0 ? round($sum/$total*100) : 0 }}%</span>
            </div>
            @endforeach
        </div>
    </div>

    {{-- Category breakdown --}}
    <div class="md:col-span-3 bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm p-6">
        <p class="text-sm font-bold text-slate-900 dark:text-white mb-5">Сравнение категорий</p>
        <div style="position:relative;height:{{ max(120, count($byCategory) * 44) }}px;"><canvas id="expBars"></canvas></div>
    </div>
</div>
@endif

{{-- Table --}}
<div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden"
     x-data="tablePager({{ $rows->count() }}, 25)">
    <div class="px-5 py-4 border-b border-slate-100 dark:border-slate-700/50 flex items-center justify-between">
        <p class="text-sm font-semibold text-slate-700 dark:text-slate-300">{{ $rows->count() }} записей</p>
        <p class="text-sm font-bold text-orange-600 dark:text-orange-400 tabular-nums">{{ number_format($total, 0, '.', ' ') }} сум</p>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-slate-50 dark:bg-slate-800/80 border-b border-slate-100 dark:border-slate-700/50">
                    <th class="text-left px-5 py-3 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Дата</th>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Категория</th>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Описание</th>
                    <th class="text-right px-5 py-3 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Сумма</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-700/50">
                @forelse($rows as $expense)
                <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/30 transition-colors" x-show="show({{ $loop->index }})">
                    <td class="px-5 py-3.5 text-slate-500 dark:text-slate-400 font-mono text-xs">{{ $expense->expense_date->format('d.m.Y') }}</td>
                    <td class="px-5 py-3.5">
                        <span class="text-xs font-semibold capitalize px-2.5 py-1 rounded-full bg-orange-50 dark:bg-orange-900/30 text-orange-700 dark:text-orange-400 border border-orange-100 dark:border-orange-800/50">{{ $expense->category }}</span>
                    </td>
                    <td class="px-5 py-3.5 text-slate-700 dark:text-slate-300">{{ $expense->description }}</td>
                    <td class="px-5 py-3.5 text-right font-bold text-slate-900 dark:text-white tabular-nums">{{ number_format($expense->amount, 0, '.', ' ') }}</td>
                </tr>
                @empty
                <tr><td colspan="4" class="px-5 py-16 text-center text-slate-400 dark:text-slate-500">Нет расходов за выбранный период</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @include('reports._pagination')
</div>

@if($byCategory->isNotEmpty())
@push('scripts')
<script>
(function () {
    const isDark = document.documentElement.classList.contains('dark');
    const gc  = isDark ? 'rgba(255,255,255,0.05)' : 'rgba(0,0,0,0.04)';
    const tc  = isDark ? '#64748b' : '#94a3b8';
    const pal = @json($palette);
    const cats = @json($cats);
    const vals = @json($vals);

    // Doughnut
    new Chart(document.getElementById('expDonut'), {
        type: 'doughnut',
        data: {
            labels: cats,
            datasets: [{
                data: vals,
                backgroundColor: pal.slice(0, vals.length),
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
                        label: c => '  ' + Number(c.raw).toLocaleString('ru') + ' сум',
                    }
                }
            }
        }
    });

    // Horizontal bar
    const barCanvas = document.getElementById('expBars');
    const maxVal = Math.max(...vals);
    new Chart(barCanvas, {
        type: 'bar',
        data: {
            labels: cats,
            datasets: [{
                data: vals,
                backgroundColor: pal.slice(0, vals.length).map(c => c + 'cc'),
                hoverBackgroundColor: pal.slice(0, vals.length),
                borderRadius: 5,
                borderSkipped: false,
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
                    callbacks: {
                        label: c => '  ' + Number(c.raw).toLocaleString('ru') + ' сум',
                    }
                }
            },
            scales: {
                x: {
                    grid: { color: gc, drawBorder: false },
                    border: { display: false },
                    ticks: {
                        color: tc, font: { size: 11 },
                        callback: v => v >= 1e6 ? (v/1e6).toFixed(1)+'M' : v >= 1e3 ? (v/1e3).toFixed(0)+'K' : v,
                    }
                },
                y: {
                    grid: { display: false },
                    border: { display: false },
                    ticks: { color: isDark ? '#cbd5e1' : '#475569', font: { size: 12 } },
                }
            }
        }
    });
})();
</script>
@endpush
@endif

@endsection
