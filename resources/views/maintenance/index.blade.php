@extends('layouts.app')

@section('title', 'Техобслуживание')

@section('content')
@php
$columns = [
    'open'        => ['label' => 'Открыто',  'dot' => 'bg-red-500',     'ring' => 'ring-red-200 dark:ring-red-800'],
    'in_progress' => ['label' => 'В работе', 'dot' => 'bg-yellow-400',  'ring' => 'ring-yellow-200 dark:ring-yellow-800'],
    'resolved'    => ['label' => 'Решено',   'dot' => 'bg-emerald-500', 'ring' => 'ring-emerald-200 dark:ring-emerald-800'],
];
$priorityDot = [
    'low'    => 'bg-slate-400',
    'medium' => 'bg-yellow-400',
    'high'   => 'bg-orange-500',
    'urgent' => 'bg-red-600',
];
$priorityLabel = ['low' => 'Низкий', 'medium' => 'Средний', 'high' => 'Высокий', 'urgent' => 'Срочный'];
$priorityBadge = [
    'low'    => 'bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-300',
    'medium' => 'bg-yellow-50 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400',
    'high'   => 'bg-orange-50 text-orange-700 dark:bg-orange-900/30 dark:text-orange-400',
    'urgent' => 'bg-red-50 text-red-700 dark:bg-red-900/30 dark:text-red-400',
];
$statusBadge = [
    'open'        => 'bg-red-50 text-red-700 dark:bg-red-900/30 dark:text-red-400',
    'in_progress' => 'bg-yellow-50 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400',
    'resolved'    => 'bg-emerald-50 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400',
];
$statusLabel = ['open' => 'Открыто', 'in_progress' => 'В работе', 'resolved' => 'Решено'];
@endphp

<div x-data="maintenancePage()" x-init="init()">

    {{-- Header --}}
    <div class="flex items-center justify-between mb-5">
        <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Техобслуживание</h1>
        <div class="flex items-center gap-2">
            {{-- View toggle --}}
            <div class="flex items-center bg-slate-100 dark:bg-slate-800 rounded-lg p-1 gap-0.5">
                <button @click="setView('kanban')"
                        :class="view === 'kanban' ? 'bg-white dark:bg-slate-600 shadow-sm text-slate-900 dark:text-white' : 'text-slate-500 dark:text-slate-400 hover:text-slate-700'"
                        class="flex items-center gap-1.5 px-3 py-1.5 rounded-md text-xs font-semibold transition-all">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-3.5 h-3.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z"/></svg>
                    Канбан
                </button>
                <button @click="setView('table')"
                        :class="view === 'table' ? 'bg-white dark:bg-slate-600 shadow-sm text-slate-900 dark:text-white' : 'text-slate-500 dark:text-slate-400 hover:text-slate-700'"
                        class="flex items-center gap-1.5 px-3 py-1.5 rounded-md text-xs font-semibold transition-all">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-3.5 h-3.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3.375 19.5h17.25m-17.25 0a1.125 1.125 0 0 1-1.125-1.125M3.375 19.5h7.5c.621 0 1.125-.504 1.125-1.125m-9.75 0V5.625m0 12.75v-1.5c0-.621.504-1.125 1.125-1.125m18.375 2.625V5.625m0 12.75c0 .621-.504 1.125-1.125 1.125m1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125m0 3.75h-7.5A1.125 1.125 0 0 1 12 18.375m9.75-12.75c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125m19.5 0v1.5c0 .621-.504 1.125-1.125 1.125M2.25 5.625v1.5c0 .621.504 1.125 1.125 1.125m0 0h17.25m-17.25 0h7.5c.621 0 1.125.504 1.125 1.125M3.375 8.25c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125m17.25-3.75h-7.5c-.621 0-1.125.504-1.125 1.125m8.625-1.125c.621 0 1.125.504 1.125 1.125v1.5c0 .621-.504 1.125-1.125 1.125m-17.25 0h7.5m-7.5 0c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125M12 10.875v-1.5m0 1.5c0 .621-.504 1.125-1.125 1.125H9.375m3.75 0h1.5"/></svg>
                    Таблица
                </button>
            </div>

            <a href="{{ route('maintenance.create') }}"
               class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white text-sm font-semibold rounded-lg hover:bg-blue-700 transition-colors shadow-sm">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                Новая заявка
            </a>
        </div>
    </div>

    {{-- ══════════════════ KANBAN VIEW ══════════════════ --}}
    <div x-show="view === 'kanban'" x-cloak class="grid grid-cols-1 md:grid-cols-3 gap-4">

        @foreach($columns as $colKey => $col)
        @php $colCards = $requests[$colKey] ?? collect(); @endphp

        <div class="flex flex-col bg-slate-50 dark:bg-slate-900/40 rounded-xl border border-slate-200 dark:border-slate-700 overflow-hidden"
             @dragover.prevent
             @drop="onDrop($event, '{{ $colKey }}')">

            {{-- Column header --}}
            <div class="flex items-center justify-between px-3 py-2.5 border-b border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800">
                <div class="flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full {{ $col['dot'] }}"></span>
                    <h2 class="text-xs font-bold text-slate-700 dark:text-slate-200 uppercase tracking-wider">{{ $col['label'] }}</h2>
                </div>
                <span class="text-xs font-semibold text-slate-400 bg-slate-100 dark:bg-slate-700 px-2 py-0.5 rounded-full">{{ $colCards->count() }}</span>
            </div>

            {{-- Scrollable card list --}}
            <div class="flex flex-col gap-1.5 p-2 overflow-y-auto" style="max-height: 70vh;" id="col-{{ $colKey }}">
                @forelse($colCards as $req)
                <div class="group bg-white dark:bg-slate-800 rounded-lg border border-slate-200 dark:border-slate-700 hover:border-slate-300 dark:hover:border-slate-600 hover:shadow-sm transition-all cursor-grab select-none"
                     draggable="true"
                     @dragstart="onDragStart($event, {{ $req->id }})"
                     id="card-{{ $req->id }}">

                    {{-- Priority stripe + content --}}
                    <div class="flex items-stretch">
                        {{-- Left priority bar --}}
                        <div class="w-1 rounded-l-lg flex-shrink-0 {{ $priorityDot[$req->priority->value] }}"></div>

                        <div class="flex-1 px-2.5 py-2 min-w-0">
                            {{-- Top row: room + assignee avatar --}}
                            <div class="flex items-center justify-between gap-2 mb-1">
                                <span class="text-[11px] font-mono font-semibold text-slate-500 dark:text-slate-400">№{{ $req->room->number }}</span>
                                <div class="flex items-center gap-1.5">
                                    @if($req->assignee)
                                    <div class="w-4 h-4 rounded-full bg-blue-600 flex items-center justify-center text-white text-[8px] font-bold flex-shrink-0"
                                         title="{{ $req->assignee->name }}">
                                        {{ strtoupper(mb_substr($req->assignee->name, 0, 1)) }}
                                    </div>
                                    @endif
                                    @if($req->guest)
                                    <a href="{{ $req->booking ? route('bookings.show', $req->booking) : '#' }}"
                                       title="{{ $req->guest->full_name }}"
                                       class="w-4 h-4 rounded-full bg-blue-100 hover:bg-blue-200 flex items-center justify-center transition-colors flex-shrink-0">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-2.5 h-2.5 text-blue-600"><path fill-rule="evenodd" d="M7.5 6a4.5 4.5 0 1 1 9 0 4.5 4.5 0 0 1-9 0ZM3.751 20.105a8.25 8.25 0 0 1 16.498 0 .75.75 0 0 1-.437.695A18.158 18.158 0 0 1 12 22.5c-2.837 0-5.518-.649-7.906-1.808a.75.75 0 0 1-.437-.695Z" clip-rule="evenodd"/></svg>
                                    </a>
                                    @endif
                                </div>
                            </div>

                            {{-- Title --}}
                            <p class="text-xs font-semibold text-slate-800 dark:text-white leading-snug truncate">{{ $req->title }}</p>

                            {{-- Bottom row: date + open link --}}
                            <div class="flex items-center justify-between mt-1.5">
                                <span class="text-[10px] text-slate-400 dark:text-slate-500">{{ $req->created_at->format('d.m H:i') }}</span>
                                <a href="{{ route('maintenance.show', $req) }}"
                                   class="text-[10px] font-semibold text-blue-600 dark:text-blue-400 hover:underline opacity-0 group-hover:opacity-100 transition-opacity">
                                    Открыть →
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                @empty
                <div class="flex items-center justify-center h-16 rounded-lg border-2 border-dashed border-slate-200 dark:border-slate-700 m-1">
                    <p class="text-xs text-slate-400">Нет заявок</p>
                </div>
                @endforelse
            </div>
        </div>
        @endforeach

    </div>

    {{-- ══════════════════ TABLE VIEW ══════════════════ --}}
    <div x-show="view === 'table'" x-cloak>
        <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900/50">
                        <th class="px-3 py-2.5 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider w-16">Ном.</th>
                        <th class="px-3 py-2.5 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Заявка</th>
                        <th class="px-3 py-2.5 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider w-24 hidden sm:table-cell">Приоритет</th>
                        <th class="px-3 py-2.5 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider w-24">Статус</th>
                        <th class="px-3 py-2.5 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider hidden md:table-cell">Исполнитель</th>
                        <th class="px-3 py-2.5 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider hidden lg:table-cell w-28">Дата</th>
                        <th class="px-3 py-2.5 w-8"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                    @forelse(array_merge(
                        ($requests['open'] ?? collect())->all(),
                        ($requests['in_progress'] ?? collect())->all(),
                        ($requests['resolved'] ?? collect())->all()
                    ) as $req)
                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/40 transition-colors">
                        <td class="px-3 py-2">
                            <span class="font-mono font-semibold text-xs text-slate-700 dark:text-slate-200">{{ $req->room->number }}</span>
                        </td>
                        <td class="px-3 py-2">
                            <div class="flex items-center gap-2">
                                <span class="w-1.5 h-1.5 rounded-full flex-shrink-0 {{ $priorityDot[$req->priority->value] }}"></span>
                                <span class="font-medium text-slate-800 dark:text-white text-xs truncate max-w-xs">{{ $req->title }}</span>
                                @if($req->guest)
                                <a href="{{ $req->booking ? route('bookings.show', $req->booking) : '#' }}"
                                   title="{{ $req->guest->full_name }}"
                                   class="w-5 h-5 rounded-full bg-blue-100 hover:bg-blue-200 flex items-center justify-center transition-colors flex-shrink-0">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-3 h-3 text-blue-600"><path fill-rule="evenodd" d="M7.5 6a4.5 4.5 0 1 1 9 0 4.5 4.5 0 0 1-9 0ZM3.751 20.105a8.25 8.25 0 0 1 16.498 0 .75.75 0 0 1-.437.695A18.158 18.158 0 0 1 12 22.5c-2.837 0-5.518-.649-7.906-1.808a.75.75 0 0 1-.437-.695Z" clip-rule="evenodd"/></svg>
                                </a>
                                @endif
                            </div>
                        </td>
                        <td class="px-3 py-2 hidden sm:table-cell">
                            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-semibold {{ $priorityBadge[$req->priority->value] }}">
                                {{ $priorityLabel[$req->priority->value] }}
                            </span>
                        </td>
                        <td class="px-3 py-2">
                            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-semibold {{ $statusBadge[$req->status->value] ?? 'bg-slate-100 text-slate-600' }}">
                                {{ $statusLabel[$req->status->value] ?? $req->status->value }}
                            </span>
                        </td>
                        <td class="px-3 py-2 hidden md:table-cell">
                            @if($req->assignee)
                            <div class="flex items-center gap-1.5">
                                <div class="w-5 h-5 rounded-full bg-blue-600 flex items-center justify-center text-white text-[9px] font-bold flex-shrink-0">
                                    {{ strtoupper(mb_substr($req->assignee->name, 0, 1)) }}
                                </div>
                                <span class="text-xs text-slate-500 dark:text-slate-400 truncate max-w-[100px]">{{ $req->assignee->name }}</span>
                            </div>
                            @else
                            <span class="text-xs text-slate-400 italic">—</span>
                            @endif
                        </td>
                        <td class="px-3 py-2 hidden lg:table-cell text-xs text-slate-400 dark:text-slate-500 whitespace-nowrap">
                            {{ $req->created_at->format('d.m H:i') }}
                        </td>
                        <td class="px-3 py-2 text-right">
                            <a href="{{ route('maintenance.show', $req) }}" class="text-xs text-blue-600 dark:text-blue-400 hover:underline font-medium">→</a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-4 py-10 text-center text-sm text-slate-400">Нет заявок</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>

<script>
function maintenancePage() {
    return {
        view: 'kanban',
        draggingId: null,

        init() {
            this.view = localStorage.getItem('maintenance_view') || 'kanban';
        },
        setView(v) {
            this.view = v;
            localStorage.setItem('maintenance_view', v);
        },

        onDragStart(e, id) {
            this.draggingId = id;
            e.dataTransfer.effectAllowed = 'move';
        },

        onDrop(e, newStatus) {
            if (!this.draggingId) return;
            const id = this.draggingId;
            this.draggingId = null;

            fetch(`/maintenance/${id}/status`, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                },
                body: JSON.stringify({ status: newStatus }),
            }).then(r => { if (r.ok) window.location.reload(); });
        },
    };
}
</script>
@endsection
