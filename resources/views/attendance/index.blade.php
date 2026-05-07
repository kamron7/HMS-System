@extends('layouts.app')

@section('title', 'Учёт рабочего времени')

@section('content')

@if(!isset($isOwnerView) || !$isOwnerView)
{{-- ═══ WORKER VIEW ═══ --}}
<div x-data="workerAttendance()" x-init="init()">

@else
{{-- ═══ OWNER VIEW ═══ --}}
<div x-data="ownerAttendance()">

@endif

{{-- ══ PAGE HEADER ══ --}}
<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-slate-900 dark:text-white tracking-tight">
            @if(isset($isOwnerView) && $isOwnerView)
                Контроль посещаемости
            @else
                Моя смена
            @endif
        </h1>
        <p class="text-slate-400 dark:text-slate-500 text-sm mt-0.5">
            {{ today()->translatedFormat('l, d F Y') }}
        </p>
    </div>
    @if(isset($isOwnerView) && $isOwnerView)
    <div class="flex items-center gap-2 text-sm">
        <a href="{{ route('attendance.index') }}"
           class="px-3 py-1.5 rounded-lg text-xs font-semibold {{ !request('status') ? 'bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-200' : 'text-slate-400 hover:text-slate-600' }}">
            Все ({{ $onShiftCount + $offShiftCount }})
        </a>
        <a href="{{ route('attendance.index', ['status' => 'on']) }}"
           class="px-3 py-1.5 rounded-lg text-xs font-semibold {{ request('status') === 'on' ? 'bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-300' : 'text-slate-400 hover:text-slate-600' }}">
            На смене ({{ $onShiftCount }})
        </a>
        <a href="{{ route('attendance.index', ['status' => 'off']) }}"
           class="px-3 py-1.5 rounded-lg text-xs font-semibold {{ request('status') === 'off' ? 'bg-slate-100 dark:bg-slate-700 text-slate-500 dark:text-slate-400' : 'text-slate-400 hover:text-slate-600' }}">
            Не на смене ({{ $offShiftCount }})
        </a>
    </div>
    @endif
</div>

@if(isset($isOwnerView) && $isOwnerView)
{{-- ══ OWNER VIEW: All workers ══ --}}

{{-- Summary cards --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-5 shadow-sm">
        <div class="flex items-center gap-3 mb-3">
            <div class="w-10 h-10 rounded-xl bg-emerald-50 dark:bg-emerald-900/30 flex items-center justify-center">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-emerald-600"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z"/></svg>
            </div>
        </div>
        <p class="text-2xl font-black text-slate-900 dark:text-white">{{ $onShiftCount }}</p>
        <p class="text-xs text-slate-500 mt-1">На смене</p>
    </div>
    <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-5 shadow-sm">
        <div class="flex items-center gap-3 mb-3">
            <div class="w-10 h-10 rounded-xl bg-slate-100 dark:bg-slate-700 flex items-center justify-center">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-slate-400"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z"/></svg>
            </div>
        </div>
        <p class="text-2xl font-black text-slate-900 dark:text-white">{{ $offShiftCount }}</p>
        <p class="text-xs text-slate-500 mt-1">Не на смене</p>
    </div>
    <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-5 shadow-sm">
        <div class="flex items-center gap-3 mb-3">
            <div class="w-10 h-10 rounded-xl bg-blue-50 dark:bg-blue-900/30 flex items-center justify-center">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-blue-600"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
            </div>
        </div>
        <p class="text-2xl font-black text-slate-900 dark:text-white">{{ number_format($totalHoursToday, 1) }}ч</p>
        <p class="text-xs text-slate-500 mt-1">Отработано сегодня</p>
    </div>
    <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-5 shadow-sm">
        <div class="flex items-center gap-3 mb-3">
            <div class="w-10 h-10 rounded-xl bg-violet-50 dark:bg-violet-900/30 flex items-center justify-center">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-violet-600"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5"/></svg>
            </div>
        </div>
        <p class="text-2xl font-black text-slate-900 dark:text-white">{{ today()->translatedFormat('D') }}</p>
        <p class="text-xs text-slate-500 mt-1">День недели</p>
    </div>
</div>

{{-- Workers list --}}
<div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden">
    <div class="px-5 py-4 border-b border-slate-100 dark:border-slate-700/60">
        <h3 class="text-sm font-bold text-slate-800 dark:text-white">Сотрудники</h3>
    </div>
    <div class="divide-y divide-slate-100 dark:divide-slate-700/40">
        @forelse($workerStatuses as $w)
        <div class="flex items-center gap-4 px-5 py-4">
            {{-- Avatar --}}
            <div class="w-10 h-10 rounded-full flex items-center justify-center text-sm font-bold flex-shrink-0
                {{ $w['isOpen'] ? 'bg-emerald-100 dark:bg-emerald-900/40 text-emerald-700 dark:text-emerald-300' : 'bg-slate-100 dark:bg-slate-700 text-slate-500 dark:text-slate-400' }}">
                {{ strtoupper(mb_substr($w['user']->name, 0, 1)) }}
            </div>

            {{-- Info --}}
            <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2">
                    <p class="text-sm font-semibold text-slate-900 dark:text-white truncate">{{ $w['user']->name }}</p>
                    @if($w['isOpen'])
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 dark:bg-emerald-900/40 text-emerald-700 dark:text-emerald-300">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                        На смене
                    </span>
                    @endif
                </div>
                <div class="flex items-center gap-3 text-xs text-slate-400 mt-0.5">
                    <span>{{ $w['user']->role->label() }}</span>
                    @if($w['startedAt'])
                    <span>· с {{ $w['startedAt']->format('H:i') }}</span>
                    @endif
                    @if($w['duration'])
                    <span>· {{ $w['duration'] }}</span>
                    @endif
                    @if($w['lastAction'])
                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-semibold {{ $w['lastActionColor'] }}">
                        {{ $w['lastAction'] }}
                    </span>
                    @endif
                </div>
            </div>

            {{-- Actions --}}
            @if($w['isOpen'] && $w['shift'])
            <button @click="closeShift({{ $w['shift']->id }})"
                    class="px-3 py-1.5 text-xs font-semibold text-red-600 dark:text-red-400 border border-red-200 dark:border-red-800 rounded-lg hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors">
                Закрыть смену
            </button>
            @endif

            {{-- Week hours badge --}}
            @if($w['totalHoursWeek'] > 0)
            <div class="hidden lg:block text-right flex-shrink-0">
                <p class="text-xs text-slate-400">Неделя</p>
                <p class="text-sm font-bold text-slate-700 dark:text-slate-200">{{ number_format($w['totalHoursWeek'], 1) }}ч</p>
            </div>
            @endif
        </div>
        @empty
        <div class="px-5 py-12 text-center">
            <p class="text-sm text-slate-400">Нет сотрудников</p>
        </div>
        @endforelse
    </div>
</div>

@else
{{-- ══ WORKER VIEW: Own shift ═══ --}}

{{-- Big action card --}}
<div class="mb-6">
    @if($myShift)
    {{-- On shift --}}
    <div class="bg-gradient-to-br from-emerald-500 to-emerald-600 rounded-2xl p-6 shadow-lg shadow-emerald-500/20 text-white relative overflow-hidden">
        <div class="absolute top-0 right-0 w-32 h-32 rounded-full opacity-10 -translate-y-8 translate-x-8"></div>
        <div class="absolute bottom-0 left-0 w-24 h-24 rounded-full opacity-10 translate-y-6 -translate-x-6"></div>

        <div class="relative flex items-start justify-between">
            <div>
                <div class="flex items-center gap-2 mb-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-white animate-pulse"></span>
                    <span class="text-sm font-semibold text-emerald-100">Смена активна</span>
                </div>
                <p class="text-3xl font-black leading-tight">Смена идёт</p>
                <p class="text-emerald-100 text-sm mt-1">
                    Начата в {{ $myShift->started_at->format('H:i') }} ·
                    Прошло {{ floor($myShift->duration) }}ч {{ round(($myShift->duration - floor($myShift->duration)) * 60) }}м
                </p>
            </div>
            <div class="flex flex-col gap-2">
                <button @click="toggleBreakAction()"
                        class="px-4 py-2 bg-white/20 hover:bg-white/30 rounded-xl text-sm font-semibold transition-colors backdrop-blur-sm">
                    Перерыв
                </button>
                <button @click="openEndShiftModal()"
                        class="px-4 py-2 bg-white text-emerald-700 hover:bg-emerald-50 rounded-xl text-sm font-bold transition-colors shadow-sm">
                    Завершить смену
                </button>
            </div>
        </div>
    </div>
    @else
    {{-- Not on shift --}}
    <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-2xl p-8 shadow-lg shadow-blue-500/20 text-white text-center relative overflow-hidden">
        <div class="absolute top-0 right-0 w-40 h-40 rounded-full opacity-10 -translate-y-10 translate-x-10"></div>
        <div class="absolute bottom-0 left-0 w-32 h-32 rounded-full opacity-10 translate-y-8 -translate-x-8"></div>

        <div class="relative">
            <div class="w-16 h-16 rounded-2xl bg-white/20 flex items-center justify-center mx-auto mb-4 backdrop-blur-sm">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-8 h-8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z"/>
                </svg>
            </div>
            <p class="text-xl font-bold mb-1">Вы не на смене</p>
            <p class="text-blue-100 text-sm mb-6">Нажмите кнопку ниже чтобы начать рабочую смену</p>
            <button @click="openStartShiftModal()"
                    class="inline-flex items-center gap-2 px-8 py-3 bg-white text-blue-700 hover:bg-blue-50 rounded-xl text-sm font-bold transition-colors shadow-sm">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5.25 5.653c0-.856.917-1.398 1.667-.986l11.54 6.347a1.125 1.125 0 0 1 0 1.972l-11.54 6.347a1.125 1.125 0 0 1-1.667-.986V5.653Z"/>
                </svg>
                Начать смену
            </button>
        </div>
    </div>
    @endif
</div>

{{-- Today's log --}}
<div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden mb-6">
    <div class="px-5 py-4 border-b border-slate-100 dark:border-slate-700/60">
        <h3 class="text-sm font-bold text-slate-800 dark:text-white">Записи сегодня</h3>
    </div>
    <div class="divide-y divide-slate-100 dark:divide-slate-700/40">
        @forelse($todayLogs as $log)
        <div class="flex items-center gap-3 px-5 py-3">
            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold {{ $log->typeColor() }}">
                {{ $log->typeLabel() }}
            </span>
            <span class="text-sm text-slate-700 dark:text-slate-300 flex-1">
                {{ $log->logged_at->format('H:i') }}
            </span>
            @if($log->note)
            <span class="text-xs text-slate-400 truncate max-w-[200px]">{{ $log->note }}</span>
            @endif
        </div>
        @empty
        <div class="px-5 py-8 text-center">
            <p class="text-xs text-slate-400">Нет записей за сегодня</p>
        </div>
        @endforelse
    </div>
</div>

{{-- Week overview --}}
@if($weekShifts->isNotEmpty())
<div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden">
    <div class="px-5 py-4 border-b border-slate-100 dark:border-slate-700/60 flex items-center justify-between">
        <h3 class="text-sm font-bold text-slate-800 dark:text-white">Эта неделя</h3>
        <span class="text-xs font-bold text-blue-600 dark:text-blue-400">{{ number_format($totalHoursWeek, 1) }}ч</span>
    </div>
    <div class="divide-y divide-slate-100 dark:divide-slate-700/40">
        @foreach($weekShifts as $ws)
        <div class="flex items-center gap-3 px-5 py-3">
            <div class="w-2 h-2 rounded-full flex-shrink-0 {{ $ws->status === 'open' ? 'bg-emerald-500' : 'bg-slate-300 dark:bg-slate-600' }}"></div>
            <span class="text-sm text-slate-700 dark:text-slate-300 flex-1">
                {{ $ws->started_at->format('d.m') }} · {{ $ws->started_at->format('H:i') }}
                @if($ws->ended_at)
                    — {{ $ws->ended_at->format('H:i') }}
                @else
                    <span class="text-emerald-600 dark:text-emerald-400 text-xs font-semibold"> · сейчас</span>
                @endif
            </span>
            <span class="text-xs font-bold text-slate-500 dark:text-slate-400">{{ $ws->duration_formatted }}</span>
        </div>
        @endforeach
    </div>
</div>
@endif
@endif

</div>{{-- /x-data wrapper --}}

{{-- ═══ MODALS ═══ --}}
{{-- Start/End Shift Note Modal --}}
<div x-data="{ open: false, note: '', action: '' }"
     @open-note-modal.window="open=true; action=$event.detail.action; note=''"
     x-show="open" x-cloak
     x-transition:enter="transition ease-out duration-200"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition ease-in duration-150"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     class="fixed inset-0 z-50 flex items-center justify-center p-4"
     @keydown.escape.window="open=false">
    <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" @click="open=false"></div>
    <div class="relative w-full max-w-sm bg-white dark:bg-slate-800 rounded-2xl shadow-2xl p-6">
        <h3 class="text-base font-bold text-slate-900 dark:text-white mb-4" x-text="action === 'start' ? 'Начать смену' : 'Завершить смену'"></h3>
        <textarea x-model="note" rows="3" placeholder="Комментарий (необязательно)..."
                  class="w-full px-3 py-2 border border-slate-200 dark:border-slate-600 rounded-lg text-sm bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500 mb-4"></textarea>
        <div class="flex gap-2 justify-end">
            <button @click="open=false"
                    class="px-4 py-2 text-sm text-slate-500 hover:text-slate-700 dark:hover:text-slate-200 rounded-lg transition-colors">
                Отмена
            </button>
            <button @click="
                const route = action === 'start' ? '{{ route('attendance.start') }}' : '{{ route('attendance.end') }}';
                const msg = action === 'start' ? 'Начать смену?' : 'Завершить смену?';
                if (!confirm(msg)) return;
                fetch(route, {
                    method: 'POST',
                    headers: {'Content-Type':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}'},
                    body: JSON.stringify({note: note})
                }).then(r => r.json()).then(d => {
                    if (d.error) { alert(d.error); return; }
                    location.reload();
                });
            " class="px-4 py-2 text-sm font-semibold text-white rounded-lg transition-colors"
               :class="action === 'start' ? 'bg-blue-600 hover:bg-blue-700' : 'bg-red-600 hover:bg-red-700'">
                <span x-text="action === 'start' ? 'Начать' : 'Завершить'"></span>
            </button>
        </div>
    </div>
</div>

<script>
function workerAttendance() {
    return {
        toggleBreakAction() {
            fetch('{{ route('attendance.break') }}', {
                method: 'POST',
                headers: {'Content-Type':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}'},
                body: JSON.stringify({})
            }).then(r => r.json()).then(d => {
                if (d.error) { alert(d.error); return; }
                location.reload();
            });
        },
        openStartShiftModal() {
            window.dispatchEvent(new CustomEvent('open-note-modal', {detail: {action: 'start'}}));
        },
        openEndShiftModal() {
            window.dispatchEvent(new CustomEvent('open-note-modal', {detail: {action: 'end'}}));
        }
    };
}

function ownerAttendance() {
    return {
        closeShift(shiftId) {
            if (!confirm('Закрыть эту смену принудительно?')) return;
            fetch('/attendance/shift/' + shiftId + '/close', {
                method: 'POST',
                headers: {'Content-Type':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}'},
                body: JSON.stringify({})
            }).then(r => r.json()).then(d => {
                if (d.error) { alert(d.error); return; }
                location.reload();
            });
        }
    };
}
</script>

@endsection
