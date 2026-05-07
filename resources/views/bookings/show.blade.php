@extends('layouts.app')

@section('title', 'Бронирование #' . $booking->id)

@section('content')
@php
$paymentMethods   = ['cash' => 'Наличные', 'card' => 'Карта', 'transfer' => 'Перевод', 'other' => 'Другое'];
$isManagerOrOwner = in_array(auth()->user()->role->value, ['owner', 'manager']);

$nights = $booking->check_in_date->diffInDays($booking->check_out_date);

$statusHero = match($booking->status->value) {
    'pending'     => ['bg' => 'bg-amber-50 dark:bg-amber-900/20',   'border' => 'border-amber-200 dark:border-amber-800',   'dot' => 'bg-amber-400'],
    'confirmed'   => ['bg' => 'bg-blue-50 dark:bg-blue-900/20',     'border' => 'border-blue-200 dark:border-blue-800',     'dot' => 'bg-blue-500'],
    'checked_in'  => ['bg' => 'bg-emerald-50 dark:bg-emerald-900/20','border' => 'border-emerald-200 dark:border-emerald-800','dot' => 'bg-emerald-500'],
    'checked_out' => ['bg' => 'bg-slate-50 dark:bg-slate-700/30',   'border' => 'border-slate-200 dark:border-slate-700',   'dot' => 'bg-slate-400'],
    'cancelled'   => ['bg' => 'bg-red-50 dark:bg-red-900/20',       'border' => 'border-red-200 dark:border-red-800',       'dot' => 'bg-red-400'],
    'no_show'     => ['bg' => 'bg-orange-50 dark:bg-orange-900/20', 'border' => 'border-orange-200 dark:border-orange-800', 'dot' => 'bg-orange-400'],
    'inquiry'     => ['bg' => 'bg-purple-50 dark:bg-purple-900/20', 'border' => 'border-purple-200 dark:border-purple-800', 'dot' => 'bg-purple-400'],
    default       => ['bg' => 'bg-slate-50 dark:bg-slate-800',      'border' => 'border-slate-200 dark:border-slate-700',   'dot' => 'bg-slate-400'],
};

$today = now()->toDateString();
$checkInStr  = $booking->check_in_date->toDateString();
$checkOutStr = $booking->check_out_date->toDateString();
$stayProgress = 0;
if ($booking->status->value === 'checked_in' && $today >= $checkInStr) {
    $elapsed = $booking->check_in_date->diffInDays(now());
    $stayProgress = $nights > 0 ? min(100, round($elapsed / $nights * 100)) : 100;
}
@endphp

<div x-data="{ moveOpen: false, extendOpen: false }">

{{-- ── Breadcrumb ─────────────────────────────────────────────────────────── --}}
<div class="flex items-center gap-2 mb-5 text-sm">
    <a href="{{ route('bookings.index') }}"
       class="inline-flex items-center gap-1 text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200 transition-colors">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18"/></svg>
        Бронирования
    </a>
    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-3.5 h-3.5 text-slate-300 dark:text-slate-600"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5"/></svg>
    <span class="text-slate-900 dark:text-slate-100 font-semibold">Бронирование #{{ $booking->id }}</span>
    @if($booking->booking_ref)
    <span class="ml-1 inline-flex items-center px-2 py-0.5 rounded-md text-xs font-bold font-mono tracking-wider bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 select-all">{{ $booking->booking_ref }}</span>
    @endif
</div>

{{-- ── Blacklist warning ───────────────────────────────────────────────────── --}}
@if($booking->guest?->tag === \App\Enums\GuestTag::Blacklist)
<div class="mb-5 bg-red-50 dark:bg-red-900/20 border border-red-300 dark:border-red-700 rounded-xl p-4 flex items-center gap-3">
    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-red-600 dark:text-red-400 flex-shrink-0"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z"/></svg>
    <div>
        <p class="text-sm font-bold text-red-800 dark:text-red-300">Гость в чёрном списке</p>
        <p class="text-xs text-red-700 dark:text-red-400 mt-0.5">{{ $booking->guest->full_name }} — проверьте перед заселением.</p>
    </div>
</div>
@endif

{{-- ── Inquiry banner ──────────────────────────────────────────────────────── --}}
@if($booking->status === \App\Enums\BookingStatus::Inquiry)
<div x-data="{ acceptOpen: false }" class="mb-5">
    <div class="bg-purple-50 dark:bg-purple-900/20 border border-purple-200 dark:border-purple-800 rounded-xl p-4 flex items-center gap-4">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-purple-600 flex-shrink-0"><path stroke-linecap="round" stroke-linejoin="round" d="M9.879 7.519c1.171-1.025 3.071-1.025 4.242 0 1.172 1.025 1.172 2.687 0 3.712-.203.179-.43.326-.67.442-.745.361-1.45.999-1.45 1.827v.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 5.25h.008v.008H12v-.008Z"/></svg>
        <div class="flex-1 min-w-0">
            <p class="text-sm font-semibold text-purple-900 dark:text-purple-200">Запрос от клиента</p>
            @if($booking->inquiry)
            <p class="text-xs text-purple-700 mt-0.5">
                {{ $booking->inquiry->fullName() }}
                @if($booking->inquiry->phone) · {{ $booking->inquiry->phone }}@endif
                @if($booking->inquiry->email) · {{ $booking->inquiry->email }}@endif
            </p>
            @endif
        </div>
        <div class="flex items-center gap-2 flex-shrink-0">
            <button @click="acceptOpen = true" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-3.5 h-3.5"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>Принять
            </button>
            <form method="POST" action="{{ route('bookings.reject-inquiry', $booking) }}">
                @csrf
                <button type="submit" onclick="return confirm('Отклонить запрос?')" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold bg-red-50 text-red-700 rounded-lg hover:bg-red-100 transition-colors border border-red-200">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-3.5 h-3.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg>Отклонить
                </button>
            </form>
        </div>
    </div>
    <div x-show="acceptOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40">
        <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-xl w-full max-w-md p-6" @click.outside="acceptOpen = false">
            <h3 class="text-base font-bold text-slate-900 dark:text-slate-100 mb-4">Принять запрос</h3>
            <form method="POST" action="{{ route('bookings.accept-inquiry', $booking) }}" x-data="{ action: '{{ $booking->source->value === 'client' ? 'existing' : 'new' }}' }">
                @csrf
                <input type="hidden" name="action" :value="action">
                <div class="flex gap-3 mb-4">
                    <label class="flex-1 flex items-center gap-2 p-3 rounded-lg border cursor-pointer transition-colors" :class="action === 'new' ? 'border-blue-500 bg-blue-50 dark:bg-blue-900/30' : 'border-slate-200 dark:border-slate-600 hover:border-slate-300'">
                        <input type="radio" value="new" x-model="action" class="sr-only">
                        <div><p class="text-sm font-semibold text-slate-800 dark:text-slate-100">Новый гость</p><p class="text-xs text-slate-500">Создать профиль из запроса</p></div>
                    </label>
                    <label class="flex-1 flex items-center gap-2 p-3 rounded-lg border cursor-pointer transition-colors" :class="action === 'existing' ? 'border-blue-500 bg-blue-50 dark:bg-blue-900/30' : 'border-slate-200 dark:border-slate-600 hover:border-slate-300'">
                        <input type="radio" value="existing" x-model="action" class="sr-only">
                        <div><p class="text-sm font-semibold text-slate-800 dark:text-slate-100">Существующий</p><p class="text-xs text-slate-500">Привязать к гостю из базы</p></div>
                    </label>
                </div>
                <div x-show="action === 'new'" class="space-y-3">
                    <div class="grid grid-cols-2 gap-3">
                        <div><label class="block text-xs font-semibold text-slate-500 mb-1">Имя</label><input type="text" name="first_name" value="{{ $booking->inquiry?->first_name }}" class="w-full px-3 py-2 border border-slate-200 dark:border-slate-600 rounded-lg text-sm bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-blue-500 focus:outline-none"></div>
                        <div><label class="block text-xs font-semibold text-slate-500 mb-1">Фамилия</label><input type="text" name="last_name" value="{{ $booking->inquiry?->last_name }}" class="w-full px-3 py-2 border border-slate-200 dark:border-slate-600 rounded-lg text-sm bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-blue-500 focus:outline-none"></div>
                    </div>
                    <div><label class="block text-xs font-semibold text-slate-500 mb-1">Телефон</label><input type="text" name="phone" value="{{ $booking->inquiry?->phone }}" class="w-full px-3 py-2 border border-slate-200 dark:border-slate-600 rounded-lg text-sm bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-blue-500 focus:outline-none"></div>
                    <div><label class="block text-xs font-semibold text-slate-500 mb-1">Email</label><input type="email" name="email" value="{{ $booking->inquiry?->email }}" class="w-full px-3 py-2 border border-slate-200 dark:border-slate-600 rounded-lg text-sm bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-blue-500 focus:outline-none"></div>
                </div>
                <div x-show="action === 'existing'" class="space-y-3">
                    <div><label class="block text-xs font-semibold text-slate-500 mb-1">Выберите гостя</label>
                    <select name="guest_id" class="w-full px-3 py-2 border border-slate-200 dark:border-slate-600 rounded-lg text-sm bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-blue-500 focus:outline-none"><option value="">— Выберите —</option>@foreach($guests as $g)<option value="{{ $g->id }}" {{ $g->id === $booking->guest_id ? 'selected' : '' }}>{{ $g->fullName }} ({{ $g->phone }})</option>@endforeach</select></div>
                </div>
                <div class="flex items-center gap-3 mt-5">
                    <button type="submit" class="flex-1 px-4 py-2.5 bg-emerald-600 text-white text-sm font-semibold rounded-lg hover:bg-emerald-700 transition-colors">Принять запрос</button>
                    <button type="button" @click="acceptOpen = false" class="px-4 py-2.5 text-sm font-semibold text-slate-600 dark:text-slate-300 bg-slate-100 dark:bg-slate-700 rounded-lg hover:bg-slate-200 dark:hover:bg-slate-600 transition-colors">Отмена</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

{{-- ── Hero card ────────────────────────────────────────────────────────────── --}}
<div class="rounded-2xl border {{ $statusHero['border'] }} {{ $statusHero['bg'] }} p-6 mb-6">
    <div class="flex flex-wrap items-start gap-6">

        {{-- Guest avatar + name --}}
        @if($booking->guest)
        <div class="flex items-center gap-4 min-w-0">
            <div class="w-14 h-14 rounded-2xl bg-white dark:bg-slate-700 shadow-sm border border-white/60 dark:border-slate-600 flex items-center justify-center flex-shrink-0">
                <span class="text-xl font-bold text-slate-700 dark:text-slate-200 leading-none select-none">
                    {{ mb_strtoupper(mb_substr($booking->guest->first_name, 0, 1)) }}{{ mb_strtoupper(mb_substr($booking->guest->last_name, 0, 1)) }}
                </span>
            </div>
            <div class="min-w-0">
                <div class="flex items-center gap-2 flex-wrap">
                    <a href="{{ route('guests.show', $booking->guest) }}"
                       class="text-lg font-bold text-slate-900 dark:text-slate-100 hover:underline truncate">
                        {{ $booking->guest->fullName }}
                    </a>
                    @if($booking->guest->tag)
                    @php $tagColors = ['vip' => 'bg-yellow-100 text-yellow-800 ring-yellow-300', 'regular' => 'bg-slate-100 text-slate-600 ring-slate-200', 'blacklist' => 'bg-red-100 text-red-700 ring-red-300']; @endphp
                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-semibold ring-1 {{ $tagColors[$booking->guest->tag->value] ?? 'bg-slate-100 text-slate-600' }}">{{ $booking->guest->tag->label() }}</span>
                    @endif
                    @if($booking->source->value === 'client')
                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-semibold bg-purple-100 text-purple-700 ring-1 ring-purple-200">Клиент</span>
                    @endif
                </div>
                @if($booking->guest->phone)
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">{{ $booking->guest->phone }}</p>
                @endif
            </div>
        </div>
        @endif

        {{-- Divider --}}
        <div class="hidden sm:block w-px self-stretch bg-current opacity-10"></div>

        {{-- Stay stats strip --}}
        <div class="flex items-center gap-6 flex-wrap flex-1">
            {{-- Room --}}
            <div class="text-center min-w-[60px]">
                <p class="text-2xl font-black text-slate-900 dark:text-slate-100 leading-none">{{ $booking->room->number }}</p>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">{{ $booking->room->roomType->name }}</p>
                <p class="text-xs text-slate-400 dark:text-slate-500">{{ $booking->room->floor }} эт.</p>
            </div>
            <div class="w-px self-stretch bg-current opacity-10"></div>
            {{-- Check-in --}}
            <div class="text-center min-w-[70px]">
                <p class="text-lg font-bold text-slate-900 dark:text-slate-100 leading-none tabular-nums">{{ $booking->check_in_date->format('d.m') }}</p>
                <p class="text-xs font-medium text-slate-500 dark:text-slate-400 mt-1">{{ $booking->check_in_date->format('Y') }}</p>
                @if($booking->check_in_time)
                <p class="text-xs font-bold text-blue-600 dark:text-blue-400 mt-0.5 tabular-nums">{{ substr($booking->check_in_time, 0, 5) }}</p>
                @endif
                <p class="text-xs text-slate-400 dark:text-slate-500">Заезд</p>
            </div>
            {{-- Nights bar --}}
            <div class="flex-1 min-w-[80px]">
                <div class="flex items-center gap-2">
                    <div class="flex-1 h-0.5 bg-current opacity-20 rounded-full"></div>
                    <span class="text-xs font-bold text-slate-700 dark:text-slate-300 whitespace-nowrap">{{ $nights }} ноч.</span>
                    <div class="flex-1 h-0.5 bg-current opacity-20 rounded-full"></div>
                </div>
                @if($stayProgress > 0)
                <div class="mt-1.5 h-1 bg-current opacity-20 rounded-full overflow-hidden">
                    <div class="h-full bg-emerald-500 rounded-full transition-all" style="width: {{ $stayProgress }}%"></div>
                </div>
                <p class="text-xs text-slate-400 dark:text-slate-500 text-center mt-0.5">{{ $stayProgress }}% прошло</p>
                @endif
            </div>
            {{-- Check-out --}}
            <div class="text-center min-w-[70px]">
                <p class="text-lg font-bold text-slate-900 dark:text-slate-100 leading-none tabular-nums">{{ $booking->check_out_date->format('d.m') }}</p>
                <p class="text-xs font-medium text-slate-500 dark:text-slate-400 mt-1">{{ $booking->check_out_date->format('Y') }}</p>
                @if($booking->check_out_time)
                <p class="text-xs font-bold text-orange-600 dark:text-orange-400 mt-0.5 tabular-nums">{{ substr($booking->check_out_time, 0, 5) }}</p>
                @endif
                <p class="text-xs text-slate-400 dark:text-slate-500">Выезд</p>
            </div>
        </div>

        {{-- Status + adults --}}
        <div class="flex flex-col items-end gap-2 ml-auto flex-shrink-0">
            <x-status-badge :status="$booking->status" />
            <span class="text-xs text-slate-500 dark:text-slate-400">
                {{ $booking->adults }} взр.@if($booking->children > 0), {{ $booking->children }} дет.@endif
            </span>
        </div>
    </div>

    @if($booking->notes)
    <div class="mt-4 pt-4 border-t border-current border-opacity-10">
        <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-1">Заметки</p>
        <p class="text-sm text-slate-700 dark:text-slate-300">{{ $booking->notes }}</p>
    </div>
    @endif
</div>

{{-- ── Overdue / Discrepancy alerts ────────────────────────────────────────── --}}
@if($booking->isOverdue())
<div class="mb-5 flex items-start gap-3 p-4 bg-red-50 dark:bg-red-900/20 border border-red-300 dark:border-red-700 rounded-xl">
    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5 text-red-500 flex-shrink-0 mt-0.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z"/></svg>
    <div class="flex-1">
        <p class="text-sm font-bold text-red-700 dark:text-red-300">Гость просрочил выезд</p>
        <p class="text-xs text-red-600 dark:text-red-400 mt-0.5">
            Плановая дата выезда <strong>{{ $booking->check_out_date->translatedFormat('d M Y') }}</strong> уже прошла,
            но гость всё ещё числится заселённым.
            @php $overdueDays = (int) $booking->check_out_date->diffInDays(today()); @endphp
            Просрочка: <strong>{{ $overdueDays }} {{ $overdueDays === 1 ? 'день' : ($overdueDays < 5 ? 'дня' : 'дней') }}</strong>.
        </p>
    </div>
    <button @click="extendOpen = true"
            class="flex-shrink-0 inline-flex items-center gap-1.5 px-3 py-1.5 bg-red-600 text-white text-xs font-semibold rounded-lg hover:bg-red-700 transition-colors">
        Продлить и пересчитать
    </button>
</div>
@endif

@php
    $ciDiff = $booking->checkInDiscrepancyDays();
    $coDiff = $booking->checkOutDiscrepancyDays();
    $hasDisc = ($ciDiff !== null && $ciDiff !== 0) || ($coDiff !== null && $coDiff !== 0);
@endphp
@if($hasDisc)
<div class="mb-5 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-700 rounded-xl overflow-hidden">
    <div class="px-4 py-3 border-b border-amber-200 dark:border-amber-700 flex items-center gap-2">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4 text-amber-600 dark:text-amber-400"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z"/></svg>
        <p class="text-sm font-bold text-amber-800 dark:text-amber-300">Расхождение плановых и фактических дат</p>
    </div>
    <div class="p-4 grid grid-cols-1 sm:grid-cols-2 gap-4">
        {{-- Check-in --}}
        @if($booking->actual_check_in_at)
        <div>
            <p class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-2">Заезд</p>
            <div class="flex items-center gap-3">
                <div class="text-center">
                    <p class="text-xs text-slate-400 mb-0.5">По плану</p>
                    <p class="text-sm font-bold text-slate-700 dark:text-slate-200">{{ $booking->check_in_date->translatedFormat('d M Y') }}</p>
                </div>
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 text-slate-300"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/></svg>
                <div class="text-center">
                    <p class="text-xs text-slate-400 mb-0.5">Фактически</p>
                    <p class="text-sm font-bold {{ $ciDiff > 0 ? 'text-red-600 dark:text-red-400' : ($ciDiff < 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-slate-700 dark:text-slate-200') }}">
                        {{ $booking->actual_check_in_at->translatedFormat('d M Y') }}
                        <span class="text-xs text-slate-400 font-normal">{{ $booking->actual_check_in_at->format('H:i') }}</span>
                    </p>
                </div>
                @if($ciDiff !== 0)
                <span class="ml-auto inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold
                    {{ $ciDiff > 0 ? 'bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-300' : 'bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-300' }}">
                    {{ $ciDiff > 0 ? '+' : '' }}{{ $ciDiff }} дн.
                </span>
                @endif
            </div>
        </div>
        @endif
        {{-- Check-out --}}
        @if($booking->actual_check_out_at)
        <div>
            <p class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-2">Выезд</p>
            <div class="flex items-center gap-3">
                <div class="text-center">
                    <p class="text-xs text-slate-400 mb-0.5">По плану</p>
                    <p class="text-sm font-bold text-slate-700 dark:text-slate-200">{{ $booking->check_out_date->translatedFormat('d M Y') }}</p>
                </div>
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 text-slate-300"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/></svg>
                <div class="text-center">
                    <p class="text-xs text-slate-400 mb-0.5">Фактически</p>
                    <p class="text-sm font-bold {{ $coDiff > 0 ? 'text-red-600 dark:text-red-400' : ($coDiff < 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-slate-700 dark:text-slate-200') }}">
                        {{ $booking->actual_check_out_at->translatedFormat('d M Y') }}
                        <span class="text-xs text-slate-400 font-normal">{{ $booking->actual_check_out_at->format('H:i') }}</span>
                    </p>
                </div>
                @if($coDiff !== 0)
                <span class="ml-auto inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold
                    {{ $coDiff > 0 ? 'bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-300' : 'bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-300' }}">
                    {{ $coDiff > 0 ? '+' : '' }}{{ $coDiff }} дн.
                </span>
                @endif
            </div>
            @if($coDiff > 0)
            <div class="mt-3 flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 text-amber-500 flex-shrink-0"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75"/></svg>
                <p class="text-xs text-amber-700 dark:text-amber-400">
                    Гость выехал на {{ $coDiff }} {{ $coDiff === 1 ? 'день' : 'дней' }} позже — возможна доплата.
                </p>
                <button @click="extendOpen = true"
                        class="ml-auto flex-shrink-0 inline-flex items-center gap-1 px-2.5 py-1 bg-amber-600 text-white text-xs font-semibold rounded-lg hover:bg-amber-700 transition-colors">
                    Пересчитать
                </button>
            </div>
            @endif
        </div>
        @endif
    </div>
</div>
@endif

{{-- ── Main grid ────────────────────────────────────────────────────────────── --}}
<div class="grid grid-cols-1 md:grid-cols-3 gap-6">

    {{-- LEFT column --}}
    <div class="md:col-span-2 space-y-5">

        {{-- Payments --}}
        <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-100 dark:border-slate-700 flex items-center justify-between">
                <h2 class="text-sm font-bold text-slate-800 dark:text-slate-100">Платежи</h2>
                <span class="text-xs text-slate-400 font-medium">{{ $booking->payments->count() }} записей</span>
            </div>
            @if($booking->payments->count())
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-slate-50 dark:bg-slate-900/40">
                        <th class="text-left px-5 py-2.5 text-xs font-semibold text-slate-400 uppercase tracking-wider">Сумма</th>
                        <th class="text-left px-5 py-2.5 text-xs font-semibold text-slate-400 uppercase tracking-wider">Тип</th>
                        <th class="text-left px-5 py-2.5 text-xs font-semibold text-slate-400 uppercase tracking-wider">Способ</th>
                        <th class="text-left px-5 py-2.5 text-xs font-semibold text-slate-400 uppercase tracking-wider">Дата</th>
                        <th class="text-left px-5 py-2.5 text-xs font-semibold text-slate-400 uppercase tracking-wider">Заметки</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50 dark:divide-slate-700/50">
                    @foreach($booking->payments as $payment)
                    <tr class="hover:bg-slate-50/60 dark:hover:bg-slate-700/30 transition-colors">
                        <td class="px-5 py-3 font-bold text-slate-900 dark:text-slate-100 tabular-nums whitespace-nowrap">
                            {{ number_format($payment->amount, 0, '.', ' ') }} <span class="text-xs font-normal text-slate-400">сум</span>
                        </td>
                        <td class="px-5 py-3">
                            @if($payment->type === \App\Enums\PaymentType::Deposit)
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-amber-50 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400 ring-1 ring-amber-200 dark:ring-amber-800">Залог</span>
                            @else
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400 ring-1 ring-emerald-200 dark:ring-emerald-800">Предоплата</span>
                            @endif
                        </td>
                        <td class="px-5 py-3 text-slate-600 dark:text-slate-300 text-xs">{{ $paymentMethods[$payment->method] ?? $payment->method }}</td>
                        <td class="px-5 py-3 text-slate-500 dark:text-slate-400 font-mono text-xs whitespace-nowrap">{{ $payment->paid_at->format('d.m.Y H:i') }}</td>
                        <td class="px-5 py-3 text-slate-400 text-xs">{{ $payment->notes ?? '—' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @else
            <div class="flex flex-col items-center py-10 text-slate-300 dark:text-slate-600">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor" class="w-10 h-10 mb-2"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 4.5 19.5Z"/></svg>
                <p class="text-sm">Нет платежей</p>
            </div>
            @endif
        </div>

        {{-- Add payment --}}
        @if($booking->status !== \App\Enums\BookingStatus::Cancelled)
        <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden"
             x-data="{ open: false }">
            <button @click="open = !open"
                    class="w-full flex items-center justify-between px-5 py-4 text-sm font-bold text-slate-800 dark:text-slate-100 hover:bg-slate-50 dark:hover:bg-slate-700/40 transition-colors">
                <span class="flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4 text-emerald-500"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                    Добавить платёж
                </span>
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4 text-slate-400 transition-transform" :class="open && 'rotate-180'"><path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5"/></svg>
            </button>
            <div x-show="open" x-show class="border-t border-slate-100 dark:border-slate-700">
                <form method="POST" action="{{ route('payments.store', $booking) }}" class="p-5">
                    @csrf
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide mb-1.5">Сумма (сум)</label>
                            <input type="number" name="amount" step="0.01" min="0.01" value="{{ old('amount') }}"
                                   class="w-full border border-slate-200 dark:border-slate-600 rounded-lg px-3 py-2 text-sm bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 focus:outline-none">
                            @error('amount')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide mb-1.5">Тип</label>
                            <select name="type" class="w-full border border-slate-200 dark:border-slate-600 rounded-lg px-3 py-2 text-sm bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                                <option value="prepayment" @selected(old('type','prepayment')==='prepayment')>Предоплата</option>
                                <option value="deposit" @selected(old('type')==='deposit')>Залог</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide mb-1.5">Способ оплаты</label>
                            <select name="method" class="w-full border border-slate-200 dark:border-slate-600 rounded-lg px-3 py-2 text-sm bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                                <option value="">— выберите —</option>
                                @foreach($paymentMethods as $val => $label)
                                <option value="{{ $val }}" @selected(old('method')===$val)>{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('method')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide mb-1.5">Дата оплаты</label>
                            <input type="date" name="paid_at" value="{{ old('paid_at', now()->format('Y-m-d')) }}"
                                   class="w-full border border-slate-200 dark:border-slate-600 rounded-lg px-3 py-2 text-sm bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                        </div>
                        <div class="col-span-2">
                            <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide mb-1.5">Заметка</label>
                            <input type="text" name="notes" value="{{ old('notes') }}"
                                   class="w-full border border-slate-200 dark:border-slate-600 rounded-lg px-3 py-2 text-sm bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                        </div>
                    </div>
                    <button type="submit" class="mt-4 inline-flex items-center gap-2 px-4 py-2 bg-emerald-600 text-white text-sm font-semibold rounded-lg hover:bg-emerald-700 transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                        Добавить платёж
                    </button>
                </form>
            </div>
        </div>
        @endif

        {{-- Guest Service Requests --}}
        @if($booking->serviceRequests->isNotEmpty())
        <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-100 dark:border-slate-700 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <h2 class="text-sm font-bold text-slate-800 dark:text-slate-100">Запросы услуг (портал)</h2>
                    @if($booking->serviceRequests->where('status','pending')->count())
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-amber-100 dark:bg-amber-900/40 text-amber-700 dark:text-amber-300">
                        {{ $booking->serviceRequests->where('status','pending')->count() }} ожидает
                    </span>
                    @endif
                </div>
            </div>
            <div class="divide-y divide-slate-50 dark:divide-slate-700/50">
                @foreach($booking->serviceRequests as $sr)
                <div class="flex items-center gap-4 px-5 py-3">
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold text-slate-800 dark:text-slate-100">{{ $sr->label }}</p>
                        <p class="text-xs text-slate-400 mt-0.5">{{ $sr->quantity }} × {{ number_format($sr->price_per_unit, 0, '.', ' ') }} = {{ number_format($sr->total_price, 0, '.', ' ') }} сум · {{ $sr->created_at->format('d.m H:i') }}</p>
                    </div>
                    @if($sr->status === 'pending')
                    <div class="flex items-center gap-2 flex-shrink-0">
                        <form method="POST" action="{{ route('service-requests.confirm', $sr) }}">@csrf
                            <button type="submit" class="px-3 py-1.5 text-xs font-semibold bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 transition-colors">Принять</button>
                        </form>
                        <form method="POST" action="{{ route('service-requests.decline', $sr) }}">@csrf
                            <button type="submit" class="px-3 py-1.5 text-xs font-semibold bg-red-50 dark:bg-red-900/20 text-red-600 dark:text-red-400 rounded-lg hover:bg-red-100 dark:hover:bg-red-900/40 transition-colors border border-red-200 dark:border-red-800">Отклонить</button>
                        </form>
                    </div>
                    @elseif($sr->status === 'confirmed')
                    <span class="px-2.5 py-1 text-xs font-semibold rounded-full bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-300">Принято</span>
                    @else
                    <span class="px-2.5 py-1 text-xs font-semibold rounded-full bg-red-100 dark:bg-red-900/30 text-red-600 dark:text-red-400">Отклонено</span>
                    @endif
                </div>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Extra charges --}}
        <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-100 dark:border-slate-700 flex items-center justify-between">
                <h2 class="text-sm font-bold text-slate-800 dark:text-slate-100">Дополнительные услуги</h2>
                <span class="text-xs text-slate-400 font-medium">{{ $booking->charges->count() }} записей</span>
            </div>
            @if($booking->charges->count())
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-slate-50 dark:bg-slate-900/40">
                        <th class="text-left px-5 py-2.5 text-xs font-semibold text-slate-400 uppercase tracking-wider">Описание</th>
                        <th class="text-left px-5 py-2.5 text-xs font-semibold text-slate-400 uppercase tracking-wider">Категория</th>
                        <th class="text-right px-5 py-2.5 text-xs font-semibold text-slate-400 uppercase tracking-wider">Сумма</th>
                        <th class="text-left px-5 py-2.5 text-xs font-semibold text-slate-400 uppercase tracking-wider">Дата</th>
                        @if($isManagerOrOwner)<th class="px-5 py-2.5"></th>@endif
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50 dark:divide-slate-700/50">
                    @foreach($booking->charges as $charge)
                    <tr class="hover:bg-slate-50/60 dark:hover:bg-slate-700/30 transition-colors">
                        <td class="px-5 py-3 text-slate-700 dark:text-slate-200">{{ $charge->description }}</td>
                        <td class="px-5 py-3">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-slate-100 dark:bg-slate-700 text-slate-500 dark:text-slate-300">{{ $categories[$charge->category] ?? $charge->category }}</span>
                        </td>
                        <td class="px-5 py-3 text-right font-bold text-slate-900 dark:text-slate-100 tabular-nums whitespace-nowrap">{{ number_format($charge->amount, 0, '.', ' ') }} <span class="text-xs font-normal text-slate-400">сум</span></td>
                        <td class="px-5 py-3 text-slate-400 text-xs font-mono">{{ $charge->created_at->format('d.m.Y') }}</td>
                        @if($isManagerOrOwner)
                        <td class="px-5 py-3 text-right">
                            <form method="POST" action="{{ route('charges.destroy', [$booking, $charge]) }}">
                                @csrf @method('DELETE')
                                <button type="submit" onclick="return confirm('Удалить услугу?')" class="text-slate-300 hover:text-red-500 dark:text-slate-600 dark:hover:text-red-400 transition-colors">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0"/></svg>
                                </button>
                            </form>
                        </td>
                        @endif
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @else
            <p class="px-5 py-5 text-sm text-slate-400 dark:text-slate-500">Нет дополнительных услуг</p>
            @endif
        </div>

        {{-- Add charge --}}
        @if(! in_array($booking->status, [\App\Enums\BookingStatus::Cancelled, \App\Enums\BookingStatus::CheckedOut, \App\Enums\BookingStatus::Inquiry]))
        <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden"
             x-data="{ open: false }">
            <button @click="open = !open"
                    class="w-full flex items-center justify-between px-5 py-4 text-sm font-bold text-slate-800 dark:text-slate-100 hover:bg-slate-50 dark:hover:bg-slate-700/40 transition-colors">
                <span class="flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4 text-blue-500"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                    Добавить услугу
                </span>
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4 text-slate-400 transition-transform" :class="open && 'rotate-180'"><path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5"/></svg>
            </button>
            <div x-show="open" x-show class="border-t border-slate-100 dark:border-slate-700">
                <form method="POST" action="{{ route('charges.store', $booking) }}" class="p-5">
                    @csrf
                    <div class="grid grid-cols-2 gap-4">
                        <div class="col-span-2">
                            <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide mb-1.5">Описание</label>
                            <input type="text" name="description" value="{{ old('description') }}" maxlength="200"
                                   placeholder="Напр: Мини-бар — вода, орешки"
                                   class="w-full border border-slate-200 dark:border-slate-600 rounded-lg px-3 py-2 text-sm bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-blue-500 focus:outline-none">
                            @error('description')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide mb-1.5">Категория</label>
                            <select name="category" class="w-full border border-slate-200 dark:border-slate-600 rounded-lg px-3 py-2 text-sm bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-blue-500 focus:outline-none">
                                <option value="">— выберите —</option>
                                @foreach($categories as $val => $label)
                                    @if($val !== 'room_night')
                                    <option value="{{ $val }}" @selected(old('category')===$val)>{{ $label }}</option>
                                    @endif
                                @endforeach
                            </select>
                            @error('category')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide mb-1.5">Сумма (сум)</label>
                            <input type="number" name="amount" step="0.01" min="0.01" value="{{ old('amount') }}"
                                   class="w-full border border-slate-200 dark:border-slate-600 rounded-lg px-3 py-2 text-sm bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-blue-500 focus:outline-none">
                            @error('amount')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
                        </div>
                    </div>
                    <button type="submit" class="mt-4 inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white text-sm font-semibold rounded-lg hover:bg-blue-700 transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                        Добавить услугу
                    </button>
                </form>
            </div>
        </div>
        @endif

        {{-- Maintenance tickets --}}
        @if($booking->maintenanceRequests->isNotEmpty())
        <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-100 dark:border-slate-700 flex items-center justify-between">
                <h2 class="text-sm font-bold text-slate-800 dark:text-slate-100">Заявки из номера</h2>
                <span class="text-xs text-slate-400 font-medium">{{ $booking->maintenanceRequests->count() }}</span>
            </div>
            @php
                $mStatusMap    = ['open' => ['label' => 'Открыто', 'class' => 'bg-red-50 text-red-700 dark:bg-red-900/30 dark:text-red-400'], 'in_progress' => ['label' => 'В работе', 'class' => 'bg-yellow-50 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400'], 'resolved' => ['label' => 'Решено', 'class' => 'bg-emerald-50 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400']];
                $mPriorityMap  = ['low' => 'bg-slate-100 text-slate-600', 'medium' => 'bg-yellow-50 text-yellow-700', 'high' => 'bg-orange-50 text-orange-700', 'urgent' => 'bg-red-50 text-red-700'];
                $mPriorityLabels = ['low' => 'Низкий', 'medium' => 'Средний', 'high' => 'Высокий', 'urgent' => 'Срочный'];
            @endphp
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-slate-50 dark:bg-slate-900/40">
                        <th class="text-left px-5 py-2.5 text-xs font-semibold text-slate-400 uppercase tracking-wider">Заявка</th>
                        <th class="text-left px-5 py-2.5 text-xs font-semibold text-slate-400 uppercase tracking-wider">Приоритет</th>
                        <th class="text-left px-5 py-2.5 text-xs font-semibold text-slate-400 uppercase tracking-wider">Статус</th>
                        <th class="text-left px-5 py-2.5 text-xs font-semibold text-slate-400 uppercase tracking-wider">Дата</th>
                        <th class="px-5 py-2.5"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50 dark:divide-slate-700/50">
                    @foreach($booking->maintenanceRequests as $req)
                    <tr class="hover:bg-slate-50/60 dark:hover:bg-slate-700/30 transition-colors">
                        <td class="px-5 py-3">
                            <p class="text-slate-800 dark:text-white font-medium">{{ $req->title }}</p>
                            @if($req->category)<p class="text-xs text-slate-400 mt-0.5">{{ ucfirst($req->category) }}</p>@endif
                        </td>
                        <td class="px-5 py-3"><span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold {{ $mPriorityMap[$req->priority->value] }}">{{ $mPriorityLabels[$req->priority->value] }}</span></td>
                        <td class="px-5 py-3">@php $ms = $mStatusMap[$req->status->value] ?? ['label' => $req->status->value, 'class' => 'bg-slate-100 text-slate-600']; @endphp<span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold {{ $ms['class'] }}">{{ $ms['label'] }}</span></td>
                        <td class="px-5 py-3 text-slate-400 text-xs font-mono">{{ $req->created_at->format('d.m.Y H:i') }}</td>
                        <td class="px-5 py-3 text-right"><a href="{{ route('maintenance.show', $req) }}" class="text-xs text-blue-600 hover:underline font-semibold">Открыть →</a></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif

        {{-- Meta --}}
        <p class="text-xs text-slate-400 dark:text-slate-500 px-1">
            Создано: <span class="font-medium">{{ $booking->creator->name ?? '—' }}</span> · {{ $booking->created_at->format('d.m.Y H:i') }}
        </p>

    </div>

    {{-- RIGHT sidebar --}}
    <div class="space-y-4">

        {{-- Financial summary --}}
        <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm p-5">
            <h2 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-4">Финансовый итог</h2>

            <div class="space-y-2 text-sm">
                <div class="flex justify-between items-center text-slate-600 dark:text-slate-300">
                    <span>Проживание · {{ $nights }} н.</span>
                    <span class="font-semibold text-slate-800 dark:text-slate-100 tabular-nums">{{ number_format($totals['room_cost'], 0, '.', ' ') }}</span>
                </div>
                @if($totals['charges'] > 0)
                <div class="flex justify-between items-center text-slate-600 dark:text-slate-300">
                    <span>Доп. услуги</span>
                    <span class="font-semibold text-slate-800 dark:text-slate-100 tabular-nums">{{ number_format($totals['charges'], 0, '.', ' ') }}</span>
                </div>
                @endif
                <div class="border-t border-slate-100 dark:border-slate-700 pt-2 flex justify-between items-center font-bold text-slate-900 dark:text-slate-100">
                    <span>Итого</span>
                    <span class="text-base tabular-nums">{{ number_format($totals['grand_total'], 0, '.', ' ') }} <span class="text-xs font-normal text-slate-400">сум</span></span>
                </div>
            </div>

            {{-- Payment progress bar --}}
            @if($totals['grand_total'] > 0)
            @php $paidPct = min(100, round($totals['paid'] / $totals['grand_total'] * 100)); @endphp
            <div class="mt-4">
                <div class="flex justify-between text-xs text-slate-500 mb-1">
                    <span>Оплачено {{ $paidPct }}%</span>
                    <span class="tabular-nums">{{ number_format($totals['paid'], 0, '.', ' ') }} / {{ number_format($totals['grand_total'], 0, '.', ' ') }}</span>
                </div>
                <div class="h-2 bg-slate-100 dark:bg-slate-700 rounded-full overflow-hidden">
                    <div class="h-full rounded-full transition-all {{ $paidPct >= 100 ? 'bg-emerald-500' : ($paidPct > 0 ? 'bg-amber-400' : 'bg-slate-200') }}"
                         style="width: {{ $paidPct }}%"></div>
                </div>
            </div>
            @endif

            @if($totals['deposit'] > 0)
            <div class="mt-3 flex justify-between items-center text-xs text-amber-700 dark:text-amber-400 bg-amber-50 dark:bg-amber-900/20 rounded-lg px-3 py-2">
                <span>Залог (возвратный)</span>
                <span class="font-bold tabular-nums">{{ number_format($totals['deposit'], 0, '.', ' ') }} сум</span>
            </div>
            @endif

            @if($totals['balance_due'] > 0)
            <div class="mt-3 flex justify-between items-center font-bold text-red-600 bg-red-50 dark:bg-red-900/20 rounded-lg px-3 py-2.5">
                <span class="text-sm">К оплате</span>
                <span class="text-base tabular-nums">{{ number_format($totals['balance_due'], 0, '.', ' ') }} <span class="text-xs font-normal">сум</span></span>
            </div>
            @else
            <div class="mt-3 flex items-center gap-2 text-emerald-600 bg-emerald-50 dark:bg-emerald-900/20 rounded-lg px-3 py-2.5">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4 flex-shrink-0"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                <span class="text-sm font-bold">Полностью оплачено</span>
            </div>
            @endif
        </div>

        {{-- Documents --}}
        <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm p-5">
            <h2 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-3">Документы</h2>
            <div class="space-y-2">
                <a href="{{ route('bookings.invoice', $booking) }}"
                   class="flex items-center gap-2.5 w-full px-3.5 py-2.5 bg-slate-700 dark:bg-slate-600 text-white text-sm font-semibold rounded-lg hover:bg-slate-800 dark:hover:bg-slate-500 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 flex-shrink-0 opacity-70"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m.75 12 3 3m0 0 3-3m-3 3v-6m-1.5-9H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z"/></svg>
                    Скачать счёт (PDF)
                </a>
                @if($booking->guest?->email)
                <form method="POST" action="{{ route('bookings.send-confirmation', $booking) }}">
                    @csrf
                    <button type="submit" class="flex items-center gap-2.5 w-full px-3.5 py-2.5 bg-emerald-600 text-white text-sm font-semibold rounded-lg hover:bg-emerald-700 transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 flex-shrink-0 opacity-70"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75"/></svg>
                        Отправить подтверждение
                    </button>
                </form>
                @endif
                @if(in_array($booking->status, [\App\Enums\BookingStatus::Confirmed, \App\Enums\BookingStatus::CheckedIn, \App\Enums\BookingStatus::Pending]))
                @php $roomPortalUrl = route('room-portal.show', $booking->room->qr_token); @endphp
                <button type="button"
                        onclick="navigator.clipboard.writeText('{{ $roomPortalUrl }}').then(()=>{ this.innerHTML='<svg xmlns=\'http://www.w3.org/2000/svg\' fill=\'none\' viewBox=\'0 0 24 24\' stroke-width=\'1.5\' stroke=\'currentColor\' class=\'w-4 h-4 flex-shrink-0 opacity-70\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' d=\'m4.5 12.75 6 6 9-13.5\'/></svg> Скопировано!'; this.classList.remove('bg-blue-600','hover:bg-blue-700'); this.classList.add('bg-emerald-600','hover:bg-emerald-700'); setTimeout(()=>{ this.innerHTML=`<svg xmlns=\'http://www.w3.org/2000/svg\' fill=\'none\' viewBox=\'0 0 24 24\' stroke-width=\'1.5\' stroke=\'currentColor\' class=\'w-4 h-4 flex-shrink-0 opacity-70\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' d=\'M13.5 6H5.25A2.25 2.25 0 0 0 3 8.25v10.5A2.25 2.25 0 0 0 5.25 21h10.5A2.25 2.25 0 0 0 18 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25\'/></svg> Ссылка на портал`; this.classList.remove('bg-emerald-600','hover:bg-emerald-700'); this.classList.add('bg-blue-600','hover:bg-blue-700'); },2000) })"
                        class="flex items-center gap-2.5 w-full px-3.5 py-2.5 bg-blue-600 text-white text-sm font-semibold rounded-lg hover:bg-blue-700 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 flex-shrink-0 opacity-70"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 0 0 3 8.25v10.5A2.25 2.25 0 0 0 5.25 21h10.5A2.25 2.25 0 0 0 18 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25"/></svg>
                    Ссылка на портал
                </button>
                @endif
            </div>
        </div>

        {{-- Actions --}}
        <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm p-5">
            <h2 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-3">Действия</h2>
            <div class="space-y-2">
                {{-- Edit --}}
                @if(in_array($booking->status, [\App\Enums\BookingStatus::Pending, \App\Enums\BookingStatus::Confirmed]))
                <a href="{{ route('bookings.edit', $booking) }}"
                   class="flex items-center gap-2.5 w-full px-3.5 py-2.5 bg-blue-600 text-white text-sm font-semibold rounded-lg hover:bg-blue-700 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 opacity-80"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125"/></svg>
                    Редактировать
                </a>
                @endif

                {{-- Status transitions --}}
                @foreach($booking->status->allowedTransitions() as $transition)
                @php
                    $tKey  = $transition->value;
                    $tDefs = [
                        'pending'     => ['label' => 'В ожидание',  'class' => 'border border-amber-200 text-amber-700 bg-amber-50 hover:bg-amber-100 dark:bg-amber-900/20 dark:text-amber-300 dark:border-amber-800 dark:hover:bg-amber-900/40'],
                        'confirmed'   => ['label' => 'Подтвердить', 'class' => 'border border-blue-200 text-blue-700 bg-blue-50 hover:bg-blue-100 dark:bg-blue-900/20 dark:text-blue-300 dark:border-blue-800 dark:hover:bg-blue-900/40'],
                        'checked_in'  => ['label' => 'Заселить',    'class' => 'border border-emerald-200 text-emerald-700 bg-emerald-50 hover:bg-emerald-100 dark:bg-emerald-900/20 dark:text-emerald-300 dark:border-emerald-800 dark:hover:bg-emerald-900/40'],
                        'checked_out' => ['label' => 'Выселить',    'class' => 'border border-slate-200 text-slate-600 bg-slate-50 hover:bg-slate-100 dark:bg-slate-700/40 dark:text-slate-300 dark:border-slate-600'],
                        'cancelled'   => ['label' => 'Отменить',    'class' => 'border border-red-200 text-red-600 bg-red-50 hover:bg-red-100 dark:bg-red-900/20 dark:text-red-400 dark:border-red-800'],
                        'no_show'     => ['label' => 'Не явился',   'class' => 'border border-orange-200 text-orange-600 bg-orange-50 hover:bg-orange-100 dark:bg-orange-900/20 dark:text-orange-400 dark:border-orange-800'],
                    ];
                    $tDef = $tDefs[$tKey] ?? ['label' => $tKey, 'class' => 'border border-slate-200 text-slate-600 bg-slate-50'];
                @endphp
                <form method="POST" action="{{ route('bookings.status', $booking) }}">
                    @csrf
                    <input type="hidden" name="transition" value="{{ $tKey }}">
                    <button type="submit"
                            class="w-full flex items-center justify-center px-3.5 py-2.5 text-sm font-semibold rounded-lg transition-colors {{ $tDef['class'] }}"
                            @if($tKey === 'cancelled') onclick="return confirm('Отменить бронирование?')"
                            @elseif($tKey === 'checked_out') onclick="return confirm('Выселить гостя?')"
                            @elseif($tKey === 'no_show') onclick="return confirm('Отметить как «Не явился»? Номер будет освобождён.')"
                            @endif>
                        {{ $tDef['label'] }}
                    </button>
                </form>
                @endforeach

                {{-- Divider before room actions --}}
                @if(in_array($booking->status, [\App\Enums\BookingStatus::Pending, \App\Enums\BookingStatus::Confirmed, \App\Enums\BookingStatus::CheckedIn]))
                <div class="border-t border-slate-100 dark:border-slate-700 pt-2 space-y-2">
                    <button type="button" @click="moveOpen = true"
                            class="flex items-center gap-2.5 w-full px-3.5 py-2.5 text-sm font-semibold rounded-lg transition-colors border border-purple-200 text-purple-700 bg-purple-50 hover:bg-purple-100 dark:bg-purple-900/20 dark:text-purple-300 dark:border-purple-800 dark:hover:bg-purple-900/40">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 opacity-80"><path stroke-linecap="round" stroke-linejoin="round" d="M7.5 21 3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12M21 7.5H7.5"/></svg>
                        Переселить в другой номер
                    </button>
                    @if(in_array($booking->status, [\App\Enums\BookingStatus::Confirmed, \App\Enums\BookingStatus::CheckedIn]))
                    <button type="button" @click="extendOpen = true"
                            class="flex items-center gap-2.5 w-full px-3.5 py-2.5 text-sm font-semibold rounded-lg transition-colors border border-teal-200 text-teal-700 bg-teal-50 hover:bg-teal-100 dark:bg-teal-900/20 dark:text-teal-300 dark:border-teal-800 dark:hover:bg-teal-900/40">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 opacity-80"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5"/></svg>
                        Продлить проживание
                    </button>
                    @endif
                </div>
                @endif
            </div>
        </div>

    </div>
</div>

{{-- ── Room Move Modal ──────────────────────────────────────────────────────── --}}
<div x-show="moveOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm"
     @keydown.escape.window="moveOpen = false" @click.self="moveOpen = false">
    <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-2xl w-full max-w-md p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-base font-bold text-slate-900 dark:text-slate-100">Переселение в другой номер</h3>
            <button @click="moveOpen = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <p class="text-xs text-slate-500 mb-4">
            Текущий: <span class="font-semibold text-slate-700 dark:text-slate-200">№{{ $booking->room->number }}</span>
            @if($booking->status === \App\Enums\BookingStatus::CheckedIn)
            <span class="text-orange-500 ml-1">· старый номер → «Грязный»</span>
            @endif
        </p>
        <div x-data="roomMove({{ $booking->id }}, {{ $booking->room_id }})">
            <input type="text" x-model="search" placeholder="Поиск по номеру или типу..."
                   class="w-full px-3 py-2 mb-3 text-sm border border-slate-200 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-purple-500 focus:outline-none">
            <div class="max-h-60 overflow-y-auto space-y-2 mb-4 pr-1">
                <template x-for="r in filteredRooms" :key="r.id">
                    <button type="button" @click="selectedRoom = r.id"
                            :class="selectedRoom == r.id ? 'border-purple-500 bg-purple-50 dark:bg-purple-900/20 ring-1 ring-purple-500' : 'border-slate-200 dark:border-slate-600 hover:border-purple-300'"
                            class="w-full text-left p-3 rounded-lg border transition-all">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div :class="selectedRoom == r.id ? 'bg-purple-600 text-white' : 'bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300'"
                                     class="w-10 h-10 rounded-lg flex items-center justify-center text-sm font-bold flex-shrink-0 transition-colors">
                                    <span x-text="r.number"></span>
                                </div>
                                <div>
                                    <p class="text-sm font-semibold text-slate-800 dark:text-white">
                                        <span x-text="r.room_type?.name || '—'"></span>
                                        <span class="text-xs font-normal text-slate-400 ml-1" x-show="r.floor">· эт. <span x-text="r.floor"></span></span>
                                    </p>
                                    <p class="text-xs text-slate-500" x-text="r.price.toLocaleString() + ' сум/ночь'"></p>
                                </div>
                            </div>
                            <div x-show="selectedRoom == r.id" class="text-purple-600">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5"><path fill-rule="evenodd" d="M2.25 12c0-5.385 4.365-9.75 9.75-9.75s9.75 4.365 9.75 9.75-4.365 9.75-9.75 9.75S2.25 17.385 2.25 12Zm13.36-1.814a.75.75 0 1 0-1.22-.872l-3.236 4.53L9.53 12.22a.75.75 0 0 0-1.06 1.06l2.25 2.25a.75.75 0 0 0 1.14-.094l3.75-5.25Z" clip-rule="evenodd"/></svg>
                            </div>
                        </div>
                    </button>
                </template>
                <p x-show="filteredRooms.length === 0 && availableRooms.length > 0" class="text-center text-xs text-slate-400 py-4">Ничего не найдено</p>
                <p x-show="availableRooms.length === 0" class="text-center text-xs text-slate-400 py-4">Загрузка номеров...</p>
            </div>
            <div x-show="selectedRoom" class="text-xs text-slate-500 mb-4 p-2.5 bg-slate-50 dark:bg-slate-700 rounded-lg">
                <p>Новая стоимость: <span class="font-bold text-slate-700 dark:text-slate-200" x-text="(availableRooms.find(r => r.id == selectedRoom)?.price * nights || 0).toLocaleString() + ' сум'"></span></p>
                @if($booking->status === \App\Enums\BookingStatus::CheckedIn)
                <p class="text-orange-600 mt-1">⚠ Номер {{ $booking->room->number }} → «Грязный»</p>
                @endif
            </div>
            <div class="flex gap-3">
                <button @click="doMove()" :disabled="!selectedRoom"
                        class="flex-1 px-4 py-2.5 bg-purple-600 text-white text-sm font-semibold rounded-lg hover:bg-purple-700 transition-colors disabled:opacity-40 disabled:cursor-not-allowed flex items-center justify-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M7.5 21 3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12M21 7.5H7.5"/></svg>
                    Переселить
                </button>
                <button @click="moveOpen = false" class="px-4 py-2.5 text-sm font-semibold text-slate-600 dark:text-slate-300 bg-slate-100 dark:bg-slate-700 rounded-lg hover:bg-slate-200 dark:hover:bg-slate-600 transition-colors">Отмена</button>
            </div>
        </div>
    </div>
</div>

{{-- ── Extend Stay Modal ────────────────────────────────────────────────────── --}}
@if(in_array($booking->status, [\App\Enums\BookingStatus::Confirmed, \App\Enums\BookingStatus::CheckedIn]))
<div x-show="extendOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm"
     @keydown.escape.window="extendOpen = false" @click.self="extendOpen = false">
    <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-2xl w-full max-w-sm p-6"
         x-data="extendStay({{ $booking->id }}, '{{ $booking->check_out_date->format('Y-m-d') }}', {{ (float) optional(optional($booking->room)->roomType)->base_price }})">
        <div class="flex items-center justify-between mb-1">
            <h3 class="text-base font-bold text-slate-900 dark:text-slate-100">Продлить проживание</h3>
            <button @click="extendOpen = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <p class="text-xs text-slate-500 mb-5">
            Текущая дата выезда: <span class="font-bold text-slate-700 dark:text-slate-200">{{ $booking->check_out_date->format('d.m.Y') }}</span>
        </p>
        <div class="flex gap-2 mb-4">
            @foreach([1, 2, 3, 7] as $n)
            <button type="button" @click="extraNights = {{ $n }}"
                    :class="extraNights == {{ $n }} ? 'bg-teal-600 text-white border-teal-600' : 'border-slate-200 dark:border-slate-600 text-slate-600 dark:text-slate-300 bg-white dark:bg-slate-700 hover:border-teal-400'"
                    class="flex-1 py-2 text-sm font-bold rounded-lg border transition-colors">+{{ $n }}</button>
            @endforeach
        </div>
        <div class="mb-4">
            <label class="block text-xs font-semibold text-slate-500 mb-1.5">Количество ночей</label>
            <input type="number" x-model.number="extraNights" min="1" max="90"
                   class="w-full px-3 py-2.5 text-sm border border-slate-200 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-teal-500 focus:outline-none">
        </div>
        <div class="mb-4 p-3.5 bg-teal-50 dark:bg-teal-900/20 rounded-xl text-sm text-teal-800 dark:text-teal-200 space-y-1.5" x-show="extraNights >= 1">
            <div class="flex justify-between">
                <span class="text-teal-600 dark:text-teal-400">Новая дата выезда</span>
                <span class="font-bold" x-text="newCheckOutFormatted"></span>
            </div>
            <div class="flex justify-between">
                <span class="text-teal-600 dark:text-teal-400">Доп. стоимость</span>
                <span class="font-bold" x-text="extraCost.toLocaleString() + ' сум'"></span>
            </div>
        </div>
        <div x-show="errorMsg" class="mb-4 p-3 bg-red-50 dark:bg-red-900/20 rounded-xl text-xs text-red-700 dark:text-red-300 space-y-1">
            <p x-text="errorMsg" class="font-semibold"></p>
            <template x-if="conflictInfo">
                <p>Занято: <a :href="conflictInfo.url" target="_blank" class="font-semibold underline hover:text-red-900 dark:hover:text-red-100" x-text="conflictInfo.guest_name + ' (' + conflictInfo.check_in + ' – ' + conflictInfo.check_out + ')'"></a></p>
            </template>
        </div>
        <div class="flex gap-3">
            <button @click="doExtend()" :disabled="extraNights < 1 || loading"
                    class="flex-1 px-4 py-2.5 bg-teal-600 text-white text-sm font-bold rounded-lg hover:bg-teal-700 transition-colors disabled:opacity-40 disabled:cursor-not-allowed flex items-center justify-center gap-2">
                <svg x-show="loading" class="animate-spin w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                Продлить
            </button>
            <button @click="extendOpen = false" class="px-4 py-2.5 text-sm font-semibold text-slate-600 dark:text-slate-300 bg-slate-100 dark:bg-slate-700 rounded-lg hover:bg-slate-200 dark:hover:bg-slate-600 transition-colors">Отмена</button>
        </div>
    </div>
</div>
@endif

<script>
function roomMove(bookingId, currentRoomId) {
    return {
        selectedRoom: '',
        availableRooms: [],
        search: '',
        nights: {{ $booking->check_in_date->diffInDays($booking->check_out_date) }},

        get filteredRooms() {
            if (!this.search) return this.availableRooms;
            const q = this.search.toLowerCase();
            return this.availableRooms.filter(r =>
                String(r.number).toLowerCase().includes(q) ||
                (r.room_type?.name || '').toLowerCase().includes(q)
            );
        },

        init() {
            fetch('{{ route('rooms.available') }}?check_in={{ $booking->check_in_date->format('Y-m-d') }}&check_out={{ $booking->check_out_date->format('Y-m-d') }}')
                .then(r => r.json())
                .then(data => {
                    this.availableRooms = data.filter(r => r.id !== currentRoomId).map(r => ({
                        ...r,
                        price: r.room_type?.base_price || 0,
                    }));
                });
        },

        doMove() {
            if (!this.selectedRoom) return;
            fetch(`/bookings/${bookingId}/move`, {
                method: 'PATCH',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                body: JSON.stringify({ room_id: parseInt(this.selectedRoom) }),
            })
            .then(r => r.json())
            .then(data => {
                if (data.error) { alert(data.error); return; }
                alert('Гость переселён в номер ' + data.room_number);
                window.location.reload();
            });
        },
    };
}

function extendStay(bookingId, currentCheckOut, pricePerNight) {
    return {
        extraNights: 1,
        loading: false,
        errorMsg: '',
        conflictInfo: null,

        get newCheckOut() {
            const d = new Date(currentCheckOut);
            d.setDate(d.getDate() + (parseInt(this.extraNights) || 0));
            return d;
        },
        get newCheckOutFormatted() {
            const d = this.newCheckOut;
            return d.getDate().toString().padStart(2,'0') + '.' + (d.getMonth()+1).toString().padStart(2,'0') + '.' + d.getFullYear();
        },
        get extraCost() {
            return (parseInt(this.extraNights) || 0) * pricePerNight;
        },

        doExtend() {
            if (this.extraNights < 1 || this.loading) return;
            this.loading = true;
            this.errorMsg = '';
            this.conflictInfo = null;
            fetch(`/bookings/${bookingId}/extend`, {
                method: 'PATCH',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                body: JSON.stringify({ extra_nights: parseInt(this.extraNights) }),
            })
            .then(r => r.json())
            .then(data => {
                this.loading = false;
                if (data.error) { this.errorMsg = data.error; this.conflictInfo = data.conflict || null; return; }
                window.location.reload();
            })
            .catch(() => { this.loading = false; this.errorMsg = 'Ошибка соединения. Попробуйте ещё раз.'; });
        },
    };
}
</script>
</div>
@endsection
