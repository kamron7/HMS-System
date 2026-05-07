@extends('layouts.app')
@section('title', 'Выручка')
@section('content')

@include('reports._header', ['title' => 'Выручка по периоду'])

@php
$dailyRevenue = $rows->sortBy('paid_at')
    ->groupBy(fn($p) => $p->paid_at->format('d.m'))
    ->map->sum('amount');
$maxDay = $dailyRevenue->max() ?: 1;
$avgDay = $dailyRevenue->count() > 0 ? $dailyRevenue->avg() : 0;
@endphp

{{-- Controls --}}
<div class="flex flex-wrap items-center gap-3 mb-6">
    @include('reports._period_filter', ['route' => 'reports.revenue'])
    <div class="ml-auto flex items-center gap-2">
        <a href="{{ route('reports.revenue', ['period' => $period, 'export' => 'csv']) }}"
           class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-semibold bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-3.5 h-3.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3"/>
            </svg> CSV
        </a>
        <a href="{{ route('reports.revenue', ['period' => $period, 'export' => 'pdf']) }}"
           class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-semibold bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">
            PDF
        </a>
    </div>
</div>

{{-- KPI strip --}}
<div class="grid grid-cols-3 gap-4 mb-5">
    <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm p-5">
        <p class="text-xs font-semibold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-2">Итого</p>
        <p class="text-3xl font-black text-emerald-600 dark:text-emerald-400 tabular-nums">{{ number_format($total / 1000000, 2) }}<span class="text-base text-slate-400 dark:text-slate-500 font-semibold ml-1">млн</span></p>
    </div>
    <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm p-5">
        <p class="text-xs font-semibold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-2">Среднее в день</p>
        <p class="text-3xl font-black text-slate-900 dark:text-white tabular-nums">{{ number_format($avgDay, 0, '.', ' ') }}</p>
        <p class="text-xs text-slate-400 dark:text-slate-500 mt-0.5">сум</p>
    </div>
    <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm p-5">
        <p class="text-xs font-semibold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-2">Транзакций</p>
        <p class="text-3xl font-black text-slate-900 dark:text-white tabular-nums">{{ $rows->count() }}</p>
    </div>
</div>

{{-- Chart --}}
@if($dailyRevenue->isNotEmpty())
<div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm p-5 mb-5">
    <div class="flex items-center justify-between mb-5">
        <div>
            <p class="text-sm font-bold text-slate-900 dark:text-white">Выручка по дням</p>
            <p class="text-xs text-slate-400 dark:text-slate-500 mt-0.5">{{ $start->format('d.m.Y') }} — {{ $end->format('d.m.Y') }}</p>
        </div>
        <div class="flex items-center gap-1.5 text-xs text-slate-400 dark:text-slate-500">
            <span class="w-3 h-3 rounded bg-emerald-500 inline-block"></span> Выручка
        </div>
    </div>
    <div style="position:relative;height:280px;"><canvas id="revenueChart"></canvas></div>
</div>
@endif

{{-- Table --}}
<div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden"
     x-data="tablePager({{ $rows->count() }}, 25)">
    <div class="px-5 py-4 border-b border-slate-100 dark:border-slate-700/50 flex items-center justify-between">
        <p class="text-sm font-semibold text-slate-700 dark:text-slate-300">{{ $rows->count() }} платежей</p>
        <p class="text-sm font-bold text-emerald-600 dark:text-emerald-400 tabular-nums">{{ number_format($total, 0, '.', ' ') }} сум</p>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-slate-50 dark:bg-slate-800/80 border-b border-slate-100 dark:border-slate-700/50">
                    <th class="text-left px-5 py-3 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Дата</th>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Гость</th>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">№</th>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Тип</th>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Метод</th>
                    <th class="text-right px-5 py-3 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Сумма</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-700/50">
                @forelse($rows as $payment)
                <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/30 transition-colors" x-show="show({{ $loop->index }})">
                    <td class="px-5 py-3.5 text-slate-500 dark:text-slate-400 font-mono text-xs">{{ $payment->paid_at->format('d.m.Y') }}</td>
                    <td class="px-5 py-3.5 font-medium text-slate-900 dark:text-white">{{ $payment->booking->guest->fullName ?? '—' }}</td>
                    <td class="px-5 py-3.5 font-bold text-slate-700 dark:text-slate-300">{{ $payment->booking->room->number ?? '—' }}</td>
                    <td class="px-5 py-3.5">
                        <span class="text-xs font-medium px-2 py-0.5 rounded-full bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300">{{ $payment->booking->room->roomType->name ?? '—' }}</span>
                    </td>
                    <td class="px-5 py-3.5">
                        <span class="text-xs font-semibold px-2 py-0.5 rounded-full
                            {{ $payment->method === 'cash' ? 'bg-emerald-50 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400' : 'bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400' }}">
                            {{ $payment->method === 'cash' ? 'Наличные' : ($payment->method === 'card' ? 'Карта' : ucfirst($payment->method)) }}
                        </span>
                    </td>
                    <td class="px-5 py-3.5 text-right font-bold text-slate-900 dark:text-white tabular-nums">{{ number_format($payment->amount, 0, '.', ' ') }}</td>
                </tr>
                @empty
                <tr><td colspan="6" class="px-5 py-16 text-center text-slate-400 dark:text-slate-500">Нет данных за выбранный период</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @include('reports._pagination')
</div>

@if($dailyRevenue->isNotEmpty())
@push('scripts')
<script>
(function () {
    const isDark = document.documentElement.classList.contains('dark');
    const gc = isDark ? 'rgba(255,255,255,0.05)' : 'rgba(0,0,0,0.04)';
    const tc = isDark ? '#64748b' : '#94a3b8';

    const labels = @json($dailyRevenue->keys());
    const data   = @json($dailyRevenue->values()->map(fn($v)=>round($v)));
    const maxVal = Math.max(...data);

    const ctx = document.getElementById('revenueChart');
    const grad = ctx.getContext('2d').createLinearGradient(0, 0, 0, 280);
    grad.addColorStop(0, 'rgba(16,185,129,0.25)');
    grad.addColorStop(1, 'rgba(16,185,129,0.02)');

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels,
            datasets: [{
                data,
                backgroundColor: data.map(v => v === maxVal ? '#10b981' : 'rgba(16,185,129,0.5)'),
                hoverBackgroundColor: '#10b981',
                borderRadius: { topLeft: 5, topRight: 5 },
                borderSkipped: false,
                barPercentage: 0.7,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            animation: { duration: 600, easing: 'easeOutQuart' },
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
                        title: c => c[0].label,
                        label: c => '  ' + Number(c.raw).toLocaleString('ru') + ' сум',
                    }
                }
            },
            scales: {
                x: {
                    grid: { display: false },
                    border: { display: false },
                    ticks: { color: tc, font: { size: 11 }, maxTicksLimit: 18 },
                },
                y: {
                    grid: { color: gc, drawBorder: false },
                    border: { display: false },
                    ticks: {
                        color: tc, font: { size: 11 },
                        callback: v => v >= 1e6 ? (v/1e6).toFixed(1)+'M' : v >= 1e3 ? (v/1e3).toFixed(0)+'K' : v,
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
