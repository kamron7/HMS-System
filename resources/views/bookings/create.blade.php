@extends('layouts.app')

@section('title', 'Новое бронирование')

@section('content')
@php
$_roomData = $prefilledRoom ? [
    'id'        => $prefilledRoom->id,
    'number'    => $prefilledRoom->number,
    'floor'     => $prefilledRoom->floor,
    'image_url' => collect($prefilledRoom->images ?? [])->map(fn($p) => asset('storage/'.$p))->first(),
    'room_type' => [
        'name'       => optional($prefilledRoom->roomType)->name,
        'capacity'   => optional($prefilledRoom->roomType)->capacity ?? 1,
        'base_price' => (float) optional($prefilledRoom->roomType)->base_price,
    ],
] : null;
$_roomId = $prefilledRoomId ? (int)$prefilledRoomId : (old('room_id') ? (int)old('room_id') : null);
@endphp
<script>
window.__staffBookingOpts = {
    checkIn:           @json(old('check_in_date', $prefilledCheckIn ?? '')),
    checkOut:          @json(old('check_out_date', $prefilledCheckOut ?? '')),
    prefilledRoomId:   @json($_roomId),
    prefilledRoomData: @json($_roomData),
    hasErrors:         @json($errors->any()),
    adults:            @json((int) old('adults', 1)),
    children:          @json((int) old('children', 0)),
    notes:             @json(old('notes', '')),
};
</script>

<div class="max-w-6xl mx-auto">

    {{-- Header --}}
    <div class="mb-6 flex items-center gap-3">
        <a href="{{ route('bookings.index') }}"
           class="inline-flex items-center gap-1.5 text-sm text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 transition-colors">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18"/></svg>
            Бронирования
        </a>
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 text-slate-300 dark:text-slate-600"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5"/></svg>
        <h1 class="text-xl font-bold text-slate-900 dark:text-slate-100">Новое бронирование</h1>
    </div>

    @if($errors->any())
    <div id="booking-errors" class="mb-5 p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-xl text-red-800 dark:text-red-300 text-sm">
        <div class="flex items-start gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 mt-0.5 flex-shrink-0"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z"/></svg>
            <ul class="list-disc list-inside space-y-1">
                @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        </div>
    </div>
    <script>document.getElementById('booking-errors')?.scrollIntoView({behavior:'smooth',block:'center'});</script>
    @endif

    <form method="POST" action="{{ route('bookings.store') }}"
          x-data="staffBookingForm(window.__staffBookingOpts)"
          x-init="init()"
          @submit="clearDraft()">
        @csrf

        {{-- Hidden inputs --}}
        <input type="hidden" name="room_id"        :value="selectedRoom ? selectedRoom.id : ''">
        <template x-for="(g, i) in selectedGuests" :key="'hg'+i">
            <input type="hidden" :name="'guest_ids['+i+']'" :value="g.id">
        </template>
        <input type="hidden" name="check_in_date"  :value="checkIn">
        <input type="hidden" name="check_out_date" :value="checkOut">
        <input type="hidden" name="adults"         :value="adults">
        <input type="hidden" name="children"       :value="children">
        <input type="hidden" name="notes"          :value="notes">
        <input type="hidden" name="promo_code"     :value="promoStatus === 'valid' ? promoCode.trim().toUpperCase() : ''">

        {{-- Step indicator --}}
        <div class="mb-8 flex items-center max-w-sm mx-auto lg:max-w-none">
            @foreach([1 => 'Гость', 2 => 'Номер', 3 => 'Детали'] as $n => $label)
            @if($n > 1)
            <div class="flex-1 h-px mx-3 transition-colors"
                 :class="{{ $n }} <= step ? 'bg-blue-400' : 'bg-slate-200 dark:bg-slate-700'"></div>
            @endif
            <div class="flex items-center gap-2 flex-shrink-0">
                <div class="w-8 h-8 rounded-full flex items-center justify-center text-sm font-bold transition-colors"
                     :class="step > {{ $n }} ? 'bg-emerald-500 text-white' : (step === {{ $n }} ? 'bg-blue-600 text-white' : 'bg-slate-200 dark:bg-slate-700 text-slate-500')">
                    <template x-if="step <= {{ $n }}"><span>{{ $n }}</span></template>
                    <template x-if="step > {{ $n }}">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                    </template>
                </div>
                <span class="text-sm font-semibold hidden sm:block"
                      :class="step === {{ $n }} ? 'text-blue-700 dark:text-blue-400' : (step > {{ $n }} ? 'text-emerald-700 dark:text-emerald-400' : 'text-slate-400')">{{ $label }}</span>
            </div>
            @endforeach
        </div>

        {{-- ══ STEP 1: Guest ══ --}}
        <div x-show="step === 1" x-cloak>
            <div class="max-w-2xl mx-auto space-y-4">

                {{-- Pre-selected room banner (from calendar click) --}}
                <template x-if="prefilledRoomData && (checkIn || checkOut)">
                    <div class="flex items-center gap-3 px-4 py-3 bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800 rounded-xl text-sm">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 text-emerald-600 dark:text-emerald-400 flex-shrink-0"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75"/></svg>
                        <div class="flex-1 min-w-0">
                            <span class="font-semibold text-emerald-800 dark:text-emerald-200"
                                  x-text="'Номер №' + prefilledRoomData.number + ' · ' + (prefilledRoomData.room_type?.name ?? '')"></span>
                            <span x-show="checkIn && checkOut" class="text-emerald-700 dark:text-emerald-300">
                                &nbsp;·&nbsp;<span x-text="checkIn"></span> → <span x-text="checkOut"></span>
                            </span>
                        </div>
                        <span class="text-xs text-emerald-600 dark:text-emerald-400 whitespace-nowrap">Выбран из календаря</span>
                    </div>
                </template>
                <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm p-6">
                    <div class="flex items-center justify-between mb-5">
                        <div>
                            <h2 class="text-lg font-bold text-slate-900 dark:text-slate-100">Кто заселяется?</h2>
                            <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">Найдите или создайте нового гостя</p>
                        </div>
                        <button type="button" @click="showNewGuest = !showNewGuest"
                                :class="showNewGuest ? 'bg-blue-600 text-white' : 'border border-slate-200 dark:border-slate-600 text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700'"
                                class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg text-sm font-semibold transition-colors">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M18 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0ZM3 19.235v-.11a6.375 6.375 0 0 1 12.75 0v.109A12.318 12.318 0 0 1 9.374 21c-2.331 0-4.512-.645-6.374-1.766Z"/></svg>
                            Новый гость
                        </button>
                    </div>

                    {{-- Selected guest chips --}}
                    <div x-show="selectedGuests.length > 0" class="flex flex-wrap gap-2 mb-4">
                        <template x-for="(g, i) in selectedGuests" :key="'chip'+g.id">
                            <span class="inline-flex items-center gap-1.5 pl-3 pr-1.5 py-1.5 rounded-full text-sm font-semibold transition-colors"
                                  :class="i === 0 ? 'bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 ring-1 ring-blue-200 dark:ring-blue-700' : 'bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300'">
                                <span x-show="i === 0" class="w-2 h-2 rounded-full bg-blue-500 dark:bg-blue-400 flex-shrink-0"></span>
                                <span x-text="g.full_name"></span>
                                <span x-show="g.phone" class="text-slate-400 dark:text-slate-500 font-normal text-xs" x-text="'· ' + g.phone"></span>
                                <button type="button" @click="removeGuest(i)"
                                        class="ml-0.5 w-5 h-5 rounded-full flex items-center justify-center hover:bg-red-100 dark:hover:bg-red-900/40 hover:text-red-600 dark:hover:text-red-400 text-slate-400 transition-colors">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-3 h-3"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg>
                                </button>
                            </span>
                        </template>
                    </div>

                    {{-- Search existing guest --}}
                    <div x-show="!showNewGuest" class="relative" @click.away="showGuestDropdown = false">
                        <div class="relative">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"/></svg>
                            <input type="text" x-model="guestQuery"
                                   @input.debounce.250ms="searchGuests()"
                                   @focus="if(guestResults.length > 0) showGuestDropdown = true"
                                   placeholder="Поиск по имени или телефону…"
                                   class="w-full pl-9 pr-3 py-3 border border-slate-200 dark:border-slate-600 rounded-xl text-sm bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div x-show="showGuestDropdown" x-cloak
                             class="absolute z-20 w-full mt-1 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-600 rounded-xl shadow-lg max-h-60 overflow-y-auto">
                            <template x-for="g in guestResults" :key="g.id">
                                <button type="button"
                                        @click="!g.active_booking && addGuest(g)"
                                        :disabled="isGuestSelected(g.id) || !!g.active_booking"
                                        :class="g.active_booking ? 'cursor-not-allowed opacity-75 bg-slate-50 dark:bg-slate-700/40' : 'hover:bg-blue-50 dark:hover:bg-blue-900/30'"
                                        class="w-full text-left px-4 py-3 text-sm border-b border-slate-100 dark:border-slate-700 last:border-0 transition-colors">
                                    <div class="flex items-center justify-between gap-3">
                                        <div class="min-w-0 flex-1">
                                            <div class="flex items-center gap-2 flex-wrap">
                                                <p class="font-semibold text-slate-900 dark:text-slate-100" x-text="g.full_name"></p>
                                                <span x-show="g.tag_label" x-text="g.tag_label"
                                                      class="text-[10px] font-semibold px-1.5 py-0.5 rounded-full bg-slate-100 dark:bg-slate-700 text-slate-500 dark:text-slate-400"></span>
                                            </div>
                                            <div class="flex items-center gap-2 mt-0.5 flex-wrap">
                                                <p class="text-xs text-slate-400 dark:text-slate-500" x-text="g.phone || '—'"></p>
                                                <template x-if="g.stays_count > 0 && !g.active_booking">
                                                    <span class="inline-flex items-center gap-1 text-[10px] font-semibold text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-900/30 px-1.5 py-0.5 rounded-full">
                                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-3 h-3"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75"/></svg>
                                                        <span x-text="g.stays_count + ' ' + (g.stays_count === 1 ? 'визит' : g.stays_count < 5 ? 'визита' : 'визитов')"></span>
                                                        <template x-if="g.last_stay">
                                                            <span x-text="'· ' + g.last_stay" class="font-normal opacity-80"></span>
                                                        </template>
                                                    </span>
                                                </template>
                                                {{-- Active booking badge --}}
                                                <template x-if="g.active_booking">
                                                    <span class="inline-flex items-center gap-1 text-[10px] font-semibold px-2 py-0.5 rounded-full"
                                                          :class="{
                                                            'bg-emerald-100 dark:bg-emerald-900/40 text-emerald-700 dark:text-emerald-300': g.active_booking.status === 'checked_in',
                                                            'bg-blue-100 dark:bg-blue-900/40 text-blue-700 dark:text-blue-300': g.active_booking.status === 'confirmed',
                                                            'bg-amber-100 dark:bg-amber-900/40 text-amber-700 dark:text-amber-300': g.active_booking.status === 'pending',
                                                          }">
                                                        <span x-text="g.active_booking.status_label"></span>
                                                        <template x-if="g.active_booking.room">
                                                            <span x-text="'· №' + g.active_booking.room" class="font-normal"></span>
                                                        </template>
                                                        <span x-text="'до ' + g.active_booking.check_out" class="font-normal opacity-80"></span>
                                                    </span>
                                                </template>
                                            </div>
                                        </div>
                                        <span x-show="isGuestSelected(g.id)" class="text-xs text-emerald-600 dark:text-emerald-400 font-semibold flex-shrink-0">Добавлен</span>
                                        <span x-show="g.active_booking && !isGuestSelected(g.id)" class="text-xs text-slate-400 dark:text-slate-500 flex-shrink-0">Недоступен</span>
                                    </div>
                                </button>
                            </template>
                        </div>
                    </div>

                    {{-- Inline new guest form --}}
                    <div x-show="showNewGuest" x-cloak class="border border-blue-200 dark:border-blue-800 rounded-xl p-4 bg-blue-50/50 dark:bg-blue-900/10">
                        <p class="text-xs font-bold text-blue-700 dark:text-blue-400 mb-3 uppercase tracking-wide">Создать нового гостя</p>
                        <div class="grid grid-cols-2 gap-3 mb-3">
                            <div>
                                <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1">Имя <span class="text-red-500">*</span></label>
                                <input type="text" x-model="newGuest.first_name" placeholder="Имя"
                                       class="w-full border border-slate-200 dark:border-slate-600 rounded-lg px-3 py-2 text-sm bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1">Фамилия</label>
                                <input type="text" x-model="newGuest.last_name" placeholder="Фамилия"
                                       class="w-full border border-slate-200 dark:border-slate-600 rounded-lg px-3 py-2 text-sm bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-3 mb-3">
                            <div class="relative">
                                <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1">Телефон</label>
                                <div class="flex rounded-lg border border-slate-200 dark:border-slate-600 overflow-hidden focus-within:ring-2 focus-within:ring-blue-500">
                                    <button type="button" @click="phoneOpen = !phoneOpen"
                                            class="flex items-center gap-1 pl-2 pr-1.5 py-2 shrink-0 bg-slate-50 dark:bg-slate-700/60 hover:bg-slate-100 dark:hover:bg-slate-700 border-r border-slate-200 dark:border-slate-600 transition-colors">
                                        <img :src="'https://flagcdn.com/20x15/' + phoneSel.iso + '.png'" :alt="phoneSel.n" class="w-5 h-auto rounded-sm flex-shrink-0">
                                        <span x-text="phoneSel.c" class="text-xs font-semibold text-slate-700 dark:text-slate-200 tabular-nums"></span>
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-3 h-3 text-slate-400 shrink-0"><path fill-rule="evenodd" d="M5.22 8.22a.75.75 0 0 1 1.06 0L10 11.94l3.72-3.72a.75.75 0 1 1 1.06 1.06l-4.25 4.25a.75.75 0 0 1-1.06 0L5.22 9.28a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd"/></svg>
                                    </button>
                                    <input type="tel" x-model="phoneLocal" placeholder="901 234 567"
                                           class="flex-1 min-w-0 px-2 py-2 text-sm bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500 focus:outline-none">
                                </div>
                                <div x-show="phoneOpen" x-cloak @click.away="phoneOpen = false"
                                     class="absolute top-full left-0 z-50 mt-1 w-56 rounded-xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 shadow-xl overflow-hidden">
                                    <div class="p-2 border-b border-slate-100 dark:border-slate-700">
                                        <input type="text" x-model="phoneSearch" placeholder="Поиск…" @click.stop
                                               class="w-full px-2.5 py-1.5 text-xs bg-slate-50 dark:bg-slate-700 border border-slate-200 dark:border-slate-600 rounded-lg text-slate-900 dark:text-slate-100 placeholder-slate-400 focus:outline-none focus:ring-1 focus:ring-blue-500">
                                    </div>
                                    <div class="overflow-y-auto max-h-48">
                                        <template x-for="c in phoneFiltered" :key="c.n">
                                            <button type="button" @click="phonePick(c)"
                                                    :class="phoneSel.n === c.n ? 'bg-blue-50 dark:bg-blue-900/30' : 'hover:bg-slate-50 dark:hover:bg-slate-700/50'"
                                                    class="w-full flex items-center gap-2 px-3 py-2 text-left transition-colors">
                                                <img :src="'https://flagcdn.com/20x15/' + c.iso + '.png'" :alt="c.n" class="w-5 h-auto rounded-sm flex-shrink-0">
                                                <span x-text="c.n" :class="phoneSel.n === c.n ? 'text-blue-700 dark:text-blue-300 font-medium' : 'text-slate-700 dark:text-slate-200'" class="flex-1 text-xs truncate"></span>
                                                <span x-text="c.c" class="text-xs text-slate-400 tabular-nums shrink-0"></span>
                                            </button>
                                        </template>
                                    </div>
                                </div>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1">Email</label>
                                <input type="email" x-model="newGuest.email" placeholder="email@…"
                                       class="w-full border border-slate-200 dark:border-slate-600 rounded-lg px-3 py-2 text-sm bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <button type="button" @click="createGuest()"
                                    :disabled="!newGuest.first_name.trim() || creatingGuest"
                                    class="inline-flex items-center gap-1.5 px-4 py-2 bg-blue-600 hover:bg-blue-700 disabled:bg-slate-300 dark:disabled:bg-slate-600 text-white disabled:text-slate-500 text-sm font-semibold rounded-lg transition-colors">
                                <svg x-show="creatingGuest" class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                                <span x-text="creatingGuest ? 'Создаём…' : 'Добавить гостя'"></span>
                            </button>
                            <button type="button" @click="showNewGuest = false; newGuest = {first_name:'',last_name:'',phone:'',email:''}; phoneSel = {iso:'uz',n:'Узбекистан',c:'+998'}; phoneLocal = ''"
                                    class="px-3 py-2 text-sm text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 transition-colors">Отмена</button>
                            <p x-show="newGuestError" x-cloak class="text-xs text-red-500 ml-2" x-text="newGuestError"></p>
                        </div>
                    </div>

                    {{-- Help text --}}
                    <p x-show="selectedGuests.length === 0 && !showNewGuest" class="text-xs text-slate-400 dark:text-slate-500 mt-4">
                        Введите имя или телефон чтобы найти гостя, или нажмите «Новый гость»
                    </p>
                </div>

                <div class="flex justify-end">
                    <button type="button" @click="nextStep()" :disabled="!canGoToStep2()"
                            :class="canGoToStep2() ? 'bg-blue-600 hover:bg-blue-700 text-white shadow-sm shadow-blue-500/30' : 'bg-slate-200 dark:bg-slate-700 text-slate-400 cursor-not-allowed'"
                            class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl text-sm font-semibold transition-colors">
                        Выбрать номер
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/></svg>
                    </button>
                </div>
            </div>
        </div>

        {{-- ══ STEP 2: Dates + Room ══ --}}
        <div x-show="step === 2" x-cloak>
            <div class="grid grid-cols-1 lg:grid-cols-5 gap-5">

                {{-- Left: Date picker --}}
                <div class="lg:col-span-2">
                    <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm p-5 lg:sticky lg:top-6">
                        <h2 class="text-base font-bold text-slate-900 dark:text-slate-100 mb-4">Период проживания</h2>

                        {{-- Date pills --}}
                        <div class="flex flex-wrap items-center gap-2 mb-5">
                            <button type="button" @click="calMode = 'check_in'"
                                    :class="{
                                        'bg-blue-600 text-white shadow-sm': calMode === 'check_in',
                                        'bg-emerald-50 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-700': calMode !== 'check_in' && checkIn,
                                        'bg-slate-100 dark:bg-slate-700 text-slate-500 dark:text-slate-400': calMode !== 'check_in' && !checkIn,
                                    }"
                                    class="flex items-center gap-2 px-3 py-2 rounded-xl text-sm font-semibold transition-all flex-1 min-w-0">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4 flex-shrink-0"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5"/></svg>
                                <div class="text-left min-w-0">
                                    <p class="text-[9px] font-bold uppercase tracking-widest opacity-70 leading-none mb-0.5">Заезд</p>
                                    <p class="truncate" x-text="checkIn || 'Выберите'"></p>
                                </div>
                            </button>
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4 text-slate-300 dark:text-slate-600 flex-shrink-0"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/></svg>
                            <button type="button" @click="if(checkIn) calMode = 'check_out'" :disabled="!checkIn"
                                    :class="{
                                        'bg-orange-500 text-white shadow-sm': calMode === 'check_out',
                                        'bg-emerald-50 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-700': calMode !== 'check_out' && checkOut,
                                        'border border-dashed border-slate-300 dark:border-slate-600 text-slate-400': !checkOut && calMode !== 'check_out',
                                    }"
                                    class="flex items-center gap-2 px-3 py-2 rounded-xl text-sm font-semibold transition-all flex-1 min-w-0 disabled:opacity-40 disabled:cursor-not-allowed">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4 flex-shrink-0"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5"/></svg>
                                <div class="text-left min-w-0">
                                    <p class="text-[9px] font-bold uppercase tracking-widest opacity-70 leading-none mb-0.5">Выезд</p>
                                    <p class="truncate" x-text="checkOut || 'Выберите'"></p>
                                </div>
                            </button>
                        </div>
                        <div x-show="nights > 0" x-cloak class="mb-4 text-center">
                            <span class="bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 border border-blue-200 dark:border-blue-700 text-sm font-bold px-3 py-1 rounded-full"
                                  x-text="nights + ' ' + calNightsLabel(nights)"></span>
                        </div>

                        {{-- Time inputs --}}
                        <div x-show="checkIn || checkOut" x-cloak class="grid grid-cols-2 gap-3 mb-4">
                            <div x-show="checkIn">
                                <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1.5">Время заезда</label>
                                <input type="time" name="check_in_time"
                                       value="{{ old('check_in_time', '14:00') }}"
                                       class="w-full border border-slate-200 dark:border-slate-600 dark:bg-slate-700/50 dark:text-white rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            </div>
                            <div x-show="checkOut">
                                <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1.5">Время выезда</label>
                                <input type="time" name="check_out_time"
                                       value="{{ old('check_out_time', '12:00') }}"
                                       class="w-full border border-slate-200 dark:border-slate-600 dark:bg-slate-700/50 dark:text-white rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            </div>
                        </div>

                        {{-- Calendar nav --}}
                        <div class="flex items-center justify-between mb-3">
                            <button type="button" @click="calPrev()" :disabled="!calCanPrev()"
                                    class="w-8 h-8 flex items-center justify-center rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700 disabled:opacity-30 disabled:cursor-not-allowed transition-colors text-slate-600 dark:text-slate-400">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5"/></svg>
                            </button>
                            <span class="text-sm font-bold text-slate-700 dark:text-slate-300 capitalize" x-text="calMonthLabel(calViewYear, calViewMonth)"></span>
                            <button type="button" @click="calNext()"
                                    class="w-8 h-8 flex items-center justify-center rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors text-slate-600 dark:text-slate-400">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5"/></svg>
                            </button>
                        </div>

                        {{-- Calendar grid --}}
                        <div class="grid grid-cols-7 mb-1">
                            <template x-for="d in ['Пн','Вт','Ср','Чт','Пт','Сб','Вс']" :key="'sh'+d">
                                <div class="text-center text-[10px] font-bold text-slate-400 uppercase py-1" x-text="d"></div>
                            </template>
                        </div>
                        <div class="grid grid-cols-7">
                            <template x-for="(day,idx) in calViewDays" :key="'cd'+idx">
                                <div :class="{
                                    'bg-blue-100 dark:bg-blue-900/40': day && isCalInRange(day),
                                    'bg-blue-100 dark:bg-blue-900/40 rounded-l-full': day && isCalCheckIn(day) && checkOut,
                                    'bg-blue-100 dark:bg-blue-900/40 rounded-r-full': day && isCalCheckOut(day) && checkIn,
                                }">
                                    <button type="button"
                                            :disabled="!day || isCalPast(day)"
                                            @click="day && selectCalDay(day)"
                                            @mouseenter="calHover = day"
                                            @mouseleave="calHover = null"
                                            :class="{
                                                'bg-blue-600 text-white hover:bg-blue-700 rounded-full': day && isCalCheckIn(day),
                                                'bg-orange-500 text-white hover:bg-orange-600 rounded-full': day && isCalCheckOut(day),
                                                'text-slate-300 dark:text-slate-600 cursor-not-allowed pointer-events-none': !day || isCalPast(day),
                                                'text-slate-700 dark:text-slate-300 hover:bg-blue-100 dark:hover:bg-blue-900/40 hover:rounded-full cursor-pointer': day && !isCalPast(day) && !isCalCheckIn(day) && !isCalCheckOut(day),
                                                'ring-2 ring-inset ring-blue-400 rounded-full font-bold': day && isCalToday(day) && !isCalCheckIn(day) && !isCalCheckOut(day),
                                            }"
                                            class="w-9 h-9 text-sm flex items-center justify-center mx-auto transition-all">
                                        <span x-text="day ? day.getDate() : ''"></span>
                                    </button>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>

                {{-- Right: Room grid --}}
                <div class="lg:col-span-3">
                    <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm p-5 min-h-[400px]">
                        <div class="flex items-center justify-between mb-4">
                            <h2 class="text-base font-bold text-slate-900 dark:text-slate-100">Доступные номера</h2>
                            <span x-show="rooms.length > 0 && !loadingRooms" x-cloak
                                  class="text-xs font-semibold text-slate-400 bg-slate-100 dark:bg-slate-700 px-2 py-1 rounded-full"
                                  x-text="rooms.length + ' номеров'"></span>
                        </div>

                        {{-- Prompt: no dates yet --}}
                        <div x-show="!checkIn || !checkOut || nights <= 0" class="flex flex-col items-center justify-center py-16 text-center">
                            <div class="w-14 h-14 rounded-full bg-slate-100 dark:bg-slate-700 flex items-center justify-center mb-3">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6 text-slate-400 dark:text-slate-500"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5"/></svg>
                            </div>
                            <p class="text-sm font-semibold text-slate-500 dark:text-slate-400">Выберите даты заезда и выезда</p>
                            <p class="text-xs text-slate-400 dark:text-slate-500 mt-1">Номера загрузятся автоматически</p>
                        </div>

                        {{-- Loading --}}
                        <div x-show="loadingRooms" x-cloak class="flex items-center justify-center py-16">
                            <div class="w-8 h-8 border-4 border-blue-500 border-t-transparent rounded-full animate-spin"></div>
                            <span class="ml-3 text-sm text-slate-500 dark:text-slate-400">Ищем номера…</span>
                        </div>

                        {{-- No rooms --}}
                        <div x-show="!loadingRooms && rooms.length === 0 && nights > 0" x-cloak class="flex flex-col items-center justify-center py-16 text-center">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-10 h-10 mx-auto text-slate-300 dark:text-slate-600 mb-3"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25"/></svg>
                            <p class="text-sm font-semibold text-slate-500 dark:text-slate-400">Нет свободных номеров</p>
                            <p class="text-xs text-slate-400 dark:text-slate-500 mt-1">Попробуйте другие даты</p>
                        </div>

                        {{-- Suggestions loading notice --}}
                        <div x-show="loadingSuggestions && rooms.length > 0" x-cloak
                             class="mb-3 flex items-center gap-2 px-3 py-2 bg-purple-50 dark:bg-purple-900/20 rounded-lg text-xs text-purple-600 dark:text-purple-400">
                            <div class="w-3.5 h-3.5 border-2 border-purple-400 border-t-transparent rounded-full animate-spin flex-shrink-0"></div>
                            Подбираем рекомендации для гостя…
                        </div>

                        {{-- Room grid --}}
                        <div x-show="!loadingRooms && rooms.length > 0" x-cloak
                             class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-3">
                            <template x-for="room in sortedRooms" :key="room.id">
                                <div @click="selectedRoom = room"
                                     :class="selectedRoom && selectedRoom.id === room.id
                                         ? 'border-blue-500 ring-2 ring-blue-400 ring-offset-1'
                                         : (isSuggested(room.id) ? 'border-purple-300 dark:border-purple-700 hover:border-purple-400' : 'border-slate-200 dark:border-slate-600 hover:border-slate-300 hover:shadow-sm')"
                                     class="border-2 rounded-xl cursor-pointer transition-all overflow-hidden bg-white dark:bg-slate-800 relative">

                                    {{-- Image area --}}
                                    <div class="h-28 relative overflow-hidden bg-slate-100 dark:bg-slate-700/50">
                                        <img x-show="room.image_url" :src="room.image_url" :alt="'Номер ' + room.number"
                                             class="w-full h-full object-cover">
                                        <div x-show="!room.image_url"
                                             class="w-full h-full flex items-center justify-center text-slate-300 dark:text-slate-600">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor" class="w-10 h-10"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 21v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21m0 0h4.5V3.545M12.75 21h7.5V10.75M2.25 21h1.5m18 0h-18M2.25 9l4.5-1.636M18.75 3l-1.5.545m0 6.205 3 1m1.5.5-1.5-.5M6.75 7.364V3h-3v18m3-13.636 10.5-3.819"/></svg>
                                        </div>

                                        {{-- Suggested badge --}}
                                        <div x-show="isSuggested(room.id)" x-cloak
                                             class="absolute top-2 left-2 flex items-center gap-1 bg-purple-500 text-white text-[10px] font-bold px-2 py-0.5 rounded-full shadow-sm">
                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-3 h-3"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292Z"/></svg>
                                            Рекомендован
                                        </div>

                                        {{-- Selected overlay --}}
                                        <div x-show="selectedRoom && selectedRoom.id === room.id"
                                             class="absolute inset-0 bg-blue-600/20 flex items-center justify-center">
                                            <div class="w-9 h-9 bg-blue-600 rounded-full flex items-center justify-center shadow-lg">
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor" class="w-5 h-5 text-white"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Card body --}}
                                    <div class="p-3">
                                        <div class="flex items-start justify-between gap-1 mb-1">
                                            <div class="min-w-0">
                                                <p class="font-bold text-slate-900 dark:text-slate-100 leading-tight" x-text="'№' + room.number"></p>
                                                <p class="text-xs text-slate-500 dark:text-slate-400 truncate" x-text="room.room_type.name"></p>
                                            </div>
                                            <span x-show="room.floor" class="text-[11px] text-slate-400 flex-shrink-0 mt-0.5" x-text="room.floor + ' эт.'"></span>
                                        </div>

                                        {{-- Capacity dots --}}
                                        <div class="flex items-center gap-0.5 my-2">
                                            <template x-for="i in room.room_type.capacity" :key="i">
                                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"
                                                     class="w-3.5 h-3.5 text-slate-300 dark:text-slate-600">
                                                    <path fill-rule="evenodd" d="M7.5 6a4.5 4.5 0 1 1 9 0 4.5 4.5 0 0 1-9 0ZM3.751 20.105a8.25 8.25 0 0 1 16.498 0 .75.75 0 0 1-.437.695A18.683 18.683 0 0 1 12 22.5c-2.786 0-5.433-.608-7.812-1.7a.75.75 0 0 1-.437-.695Z" clip-rule="evenodd"/>
                                                </svg>
                                            </template>
                                            <span class="text-[10px] text-slate-400 ml-1" x-text="'до ' + room.room_type.capacity + ' чел.'"></span>
                                        </div>

                                        <div>
                                            <p class="text-sm font-bold text-blue-600 dark:text-blue-400"
                                               x-text="new Intl.NumberFormat('ru-RU').format(room.price_per_night ?? room.room_type.base_price) + ' сум/ночь'"></p>
                                            <p x-show="room.pricing_banner" x-cloak
                                               x-text="room.pricing_banner"
                                               class="text-[10px] text-emerald-600 dark:text-emerald-400 font-medium truncate"></p>
                                        </div>

                                        {{-- Suggestion reason --}}
                                        <p x-show="isSuggested(room.id)" x-cloak
                                           class="text-[11px] text-purple-600 dark:text-purple-400 mt-1 truncate capitalize"
                                           x-text="suggestionReason(room.id)"></p>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-4 flex justify-between">
                <button type="button" @click="prevStep()"
                        class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm font-semibold border border-slate-200 dark:border-slate-600 text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18"/></svg>
                    Назад
                </button>
                <button type="button" @click="nextStep()" :disabled="!canGoToStep3()"
                        :class="canGoToStep3() ? 'bg-blue-600 hover:bg-blue-700 text-white shadow-sm shadow-blue-500/30' : 'bg-slate-200 dark:bg-slate-700 text-slate-400 cursor-not-allowed'"
                        class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl text-sm font-semibold transition-colors">
                    Далее к деталям
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/></svg>
                </button>
            </div>
        </div>

        {{-- ══ STEP 3: Details + Confirm ══ --}}
        <div x-show="step === 3" x-cloak>
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

                {{-- Left: details --}}
                <div class="lg:col-span-2 space-y-4">

                    {{-- Occupancy --}}
                    <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm p-5">
                        <h2 class="text-sm font-bold text-slate-900 dark:text-slate-100 mb-4">Заселение</h2>
                        <div class="flex flex-wrap gap-8">
                            <div>
                                <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide mb-2">Взрослых</label>
                                <div class="flex items-center gap-3">
                                    <button type="button" @click="if(adults > 1) adults--"
                                            class="w-9 h-9 rounded-full border border-slate-200 dark:border-slate-600 text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 flex items-center justify-center text-xl font-bold transition-colors">−</button>
                                    <span class="w-8 text-center text-xl font-bold text-slate-900 dark:text-slate-100" x-text="adults"></span>
                                    <button type="button" @click="adults++"
                                            class="w-9 h-9 rounded-full border border-slate-200 dark:border-slate-600 text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 flex items-center justify-center text-xl font-bold transition-colors">+</button>
                                </div>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide mb-2">Детей</label>
                                <div class="flex items-center gap-3">
                                    <button type="button" @click="if(children > 0) children--"
                                            class="w-9 h-9 rounded-full border border-slate-200 dark:border-slate-600 text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 flex items-center justify-center text-xl font-bold transition-colors">−</button>
                                    <span class="w-8 text-center text-xl font-bold text-slate-900 dark:text-slate-100" x-text="children"></span>
                                    <button type="button" @click="children++"
                                            class="w-9 h-9 rounded-full border border-slate-200 dark:border-slate-600 text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 flex items-center justify-center text-xl font-bold transition-colors">+</button>
                                </div>
                            </div>
                            <div class="flex items-end pb-1" x-show="selectedRoom">
                                <div :class="capacityExceeded ? 'text-red-600 dark:text-red-400' : 'text-slate-400 dark:text-slate-500'"
                                     class="flex items-center gap-1.5 text-sm">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z"/></svg>
                                    <span x-text="(adults + children) + ' / ' + (selectedRoom ? selectedRoom.room_type.capacity : '?') + ' чел.'"></span>
                                </div>
                            </div>
                        </div>
                        <div x-show="capacityExceeded" x-cloak
                             class="mt-3 flex items-center gap-2 px-3 py-2 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg text-xs text-red-700 dark:text-red-400">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 flex-shrink-0"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z"/></svg>
                            <span x-text="'Превышена вместимость: ' + (adults+children) + ' чел., макс. ' + (selectedRoom ? selectedRoom.room_type.capacity : '?')"></span>
                        </div>
                    </div>

                    {{-- Promo + Notes --}}
                    <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm p-5 space-y-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide mb-2">Промокод</label>
                            <div class="flex gap-2">
                                <input type="text" x-model="promoCode" placeholder="PROMO25"
                                       @input="promoStatus = null" @keydown.enter.prevent="checkPromo()"
                                       class="flex-1 border border-slate-200 dark:border-slate-600 rounded-lg px-3 py-2 text-sm font-mono uppercase placeholder:normal-case placeholder:font-normal placeholder:text-slate-400 bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <button type="button" @click="checkPromo()"
                                        class="px-4 py-2 bg-slate-800 dark:bg-slate-600 hover:bg-slate-900 dark:hover:bg-slate-500 text-white text-sm font-semibold rounded-lg transition-colors">Применить</button>
                            </div>
                            <p x-show="promoStatus === 'valid'" x-cloak class="mt-1.5 text-xs text-emerald-600 dark:text-emerald-400 font-semibold flex items-center gap-1">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-3.5 h-3.5"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                                <span x-text="promoMessage"></span>
                            </p>
                            <p x-show="promoStatus === 'invalid'" x-cloak class="mt-1.5 text-xs text-red-500 flex items-center gap-1">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-3.5 h-3.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg>
                                <span x-text="promoMessage"></span>
                            </p>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide mb-2">Примечания</label>
                            <textarea x-model="notes" rows="3" maxlength="1000" placeholder="Особые пожелания, аллергии, поздний заезд…"
                                      class="w-full border border-slate-200 dark:border-slate-600 rounded-lg px-3 py-2.5 text-sm bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none"></textarea>
                        </div>
                    </div>

                    <div class="flex justify-between">
                        <button type="button" @click="prevStep()"
                                class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm font-semibold border border-slate-200 dark:border-slate-600 text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18"/></svg>
                            Назад
                        </button>
                        <button type="submit" :disabled="!canSubmit()"
                                :class="canSubmit() ? 'bg-blue-600 hover:bg-blue-700 text-white shadow-sm shadow-blue-500/30' : 'bg-slate-200 dark:bg-slate-700 text-slate-400 cursor-not-allowed'"
                                class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl text-sm font-semibold transition-colors">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                            Создать бронирование
                        </button>
                    </div>
                </div>

                {{-- Right: summary sidebar --}}
                <div>
                    <div class="rounded-xl p-5 text-white sticky top-6" style="background:linear-gradient(135deg,#2563eb,#1d4ed8)">
                        <h3 class="text-xs font-bold text-blue-200 uppercase tracking-widest mb-4">Итог бронирования</h3>
                        <div class="space-y-3 text-sm">
                            <div x-show="selectedGuests.length > 0">
                                <p class="text-blue-300 text-xs mb-1">Гость</p>
                                <template x-for="(g,i) in selectedGuests" :key="'sg'+i">
                                    <p class="font-semibold leading-5">
                                        <span x-show="i===0" class="text-yellow-300">★ </span>
                                        <span x-text="g.full_name"></span>
                                    </p>
                                </template>
                            </div>
                            <div>
                                <p class="text-blue-300 text-xs mb-0.5">Номер</p>
                                <p class="font-bold" x-text="selectedRoom ? '№'+selectedRoom.number+' · '+selectedRoom.room_type.name : '—'"></p>
                            </div>
                            <div class="flex gap-4">
                                <div>
                                    <p class="text-blue-300 text-xs mb-0.5">Заезд</p>
                                    <p class="font-semibold" x-text="checkIn || '—'"></p>
                                </div>
                                <div>
                                    <p class="text-blue-300 text-xs mb-0.5">Выезд</p>
                                    <p class="font-semibold" x-text="checkOut || '—'"></p>
                                </div>
                                <div>
                                    <p class="text-blue-300 text-xs mb-0.5">Ночей</p>
                                    <p class="font-semibold" x-text="nights > 0 ? nights : '—'"></p>
                                </div>
                            </div>
                            <div class="pt-3 mt-1 border-t border-blue-500/60 space-y-1.5">
                                <div x-show="discountPercent > 0" x-cloak class="flex justify-between text-xs">
                                    <span class="text-blue-300">Скидка (<span x-text="discountPercent"></span>%)</span>
                                    <span class="text-emerald-300 font-semibold" x-text="'−' + new Intl.NumberFormat('ru-RU').format(discountAmount) + ' сум'"></span>
                                </div>
                                <div class="flex justify-between items-baseline">
                                    <span class="font-bold">Итого</span>
                                    <span class="text-xl font-extrabold" x-text="totalPriceFormatted"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </form>
</div>

<script>
function staffBookingForm(opts) {
    return {
        step: 1,
        checkIn:  opts.checkIn  || '',
        checkOut: opts.checkOut || '',
        prefilledRoomId:   opts.prefilledRoomId   ?? null,
        prefilledRoomData: opts.prefilledRoomData ?? null,
        hasErrors:         opts.hasErrors         ?? false,
        nights: 0,

        // Calendar
        calMode: 'check_in',
        calViewYear: 0, calViewMonth: 0, calHover: null,

        // Rooms
        rooms: [], selectedRoom: null, loadingRooms: false,
        suggestions: [], loadingSuggestions: false,

        // Guests
        guestQuery: '', guestResults: [], selectedGuests: @json($prefilledGuests),
        showGuestDropdown: false,
        showNewGuest: false,
        newGuest: { first_name: '', last_name: '', phone: '', email: '' },
        creatingGuest: false, newGuestError: '',
        phoneOpen: false, phoneSearch: '',
        phoneSel: {iso:'uz',n:'Узбекистан',c:'+998'}, phoneLocal: '',
        phoneCountries: [
            {iso:'uz',n:'Узбекистан',c:'+998'},{iso:'ru',n:'Россия',c:'+7'},
            {iso:'kz',n:'Казахстан',c:'+7'},{iso:'kg',n:'Кыргызстан',c:'+996'},
            {iso:'tj',n:'Таджикистан',c:'+992'},{iso:'tm',n:'Туркменистан',c:'+993'},
            {iso:'az',n:'Азербайджан',c:'+994'},{iso:'ge',n:'Грузия',c:'+995'},
            {iso:'am',n:'Армения',c:'+374'},{iso:'by',n:'Беларусь',c:'+375'},
            {iso:'ua',n:'Украина',c:'+380'},{iso:'md',n:'Молдова',c:'+373'},
            {iso:'tr',n:'Турция',c:'+90'},{iso:'cn',n:'Китай',c:'+86'},
            {iso:'in',n:'Индия',c:'+91'},{iso:'de',n:'Германия',c:'+49'},
            {iso:'fr',n:'Франция',c:'+33'},{iso:'gb',n:'Великобритания',c:'+44'},
            {iso:'us',n:'США',c:'+1'},
        ],
        get phoneFiltered() {
            if (!this.phoneSearch) return this.phoneCountries;
            const q = this.phoneSearch.toLowerCase();
            return this.phoneCountries.filter(c => c.n.toLowerCase().includes(q) || c.c.includes(q));
        },
        phonePick(c) { this.phoneSel = c; this.phoneOpen = false; this.phoneSearch = ''; },
        adults: opts.adults || 1, children: opts.children || 0, notes: opts.notes || '',

        // Promo
        promoCode: '', promoStatus: null, promoMessage: '', discountPercent: 0,

        get baseTotal() {
            if (!this.selectedRoom || this.nights <= 0) return 0;
            return this.nights * (this.selectedRoom.price_per_night ?? this.selectedRoom.room_type.base_price);
        },
        get discountAmount() { return Math.round(this.baseTotal * this.discountPercent / 100); },
        get finalTotal()     { return this.baseTotal - this.discountAmount; },
        get totalPriceFormatted() {
            if (!this.finalTotal) return '—';
            return new Intl.NumberFormat('ru-RU').format(this.finalTotal) + ' сум';
        },
        get capacityExceeded() {
            if (!this.selectedRoom) return false;
            return (this.adults + this.children) > this.selectedRoom.room_type.capacity;
        },
        get calViewDays()  { return this.buildMonthDays(this.calViewYear, this.calViewMonth); },
        get calNextYear()  { return this.calViewMonth === 11 ? this.calViewYear + 1 : this.calViewYear; },
        get calNextMonth() { return (this.calViewMonth + 1) % 12; },
        get calNextDays()  { return this.buildMonthDays(this.calNextYear, this.calNextMonth); },
        get sortedRooms() {
            return [...this.rooms].sort((a, b) => {
                const ai = this.suggestions.findIndex(s => s.id === a.id);
                const bi = this.suggestions.findIndex(s => s.id === b.id);
                if (ai === -1 && bi === -1) return 0;
                if (ai === -1) return 1;
                if (bi === -1) return -1;
                return ai - bi;
            });
        },
        isSuggested(id) { return this.suggestions.some(s => s.id === id); },
        suggestionReason(id) { return this.suggestions.find(s => s.id === id)?.reason ?? ''; },

        init() {
            const n = new Date();
            this.calViewYear = n.getFullYear(); this.calViewMonth = n.getMonth();
            this.loadDraft();
            this.computeNights();

            // Pre-select room if coming from timeline or after validation error
            if (this.prefilledRoomData) {
                this.selectedRoom = this.prefilledRoomData;
            }

            // Pre-load rooms in background if dates already known
            if (this.checkIn && this.checkOut && this.nights > 0) {
                this.loadRooms();
            }

            // Watch dates — load rooms and suggestions whenever dates change
            const onDateChange = () => {
                this.computeNights();
                if (this.checkIn && this.checkOut && this.nights > 0) {
                    this.loadRooms();
                    if (this.selectedGuests.length > 0) this.loadSuggestions();
                }
            };
            this.$watch('checkIn',  onDateChange);
            this.$watch('checkOut', onDateChange);

            // Re-run suggestions if guest changes while on step 2
            this.$watch('selectedGuests', () => {
                if (this.step === 2 && this.nights > 0 && this.selectedGuests.length > 0) {
                    this.loadSuggestions();
                }
                this.saveDraft();
            });

            // Auto-save
            const save = () => this.saveDraft();
            ['adults','children','notes','promoCode','step'].forEach(k => this.$watch(k, save));
            this.$watch('selectedRoom', save);

        },

        buildMonthDays(year, month) {
            const first  = new Date(year, month, 1);
            const offset = (first.getDay() + 6) % 7;
            const total  = new Date(year, month + 1, 0).getDate();
            const days   = Array(offset).fill(null);
            for (let d = 1; d <= total; d++) days.push(new Date(year, month, d));
            while (days.length % 7 !== 0) days.push(null);
            return days;
        },
        calMonthLabel(y, m) {
            return new Date(y, m, 1).toLocaleDateString('ru-RU', { month: 'long', year: 'numeric' });
        },
        calPrev() {
            if (this.calViewMonth === 0) { this.calViewYear--; this.calViewMonth = 11; } else this.calViewMonth--;
        },
        calNext() {
            if (this.calViewMonth === 11) { this.calViewYear++; this.calViewMonth = 0; } else this.calViewMonth++;
        },
        calCanPrev() {
            const n = new Date();
            return !(this.calViewYear === n.getFullYear() && this.calViewMonth === n.getMonth());
        },
        selectCalDay(date) {
            if (!date) return;
            const today = new Date(); today.setHours(0,0,0,0);
            if (date < today) return;
            const str = date.getFullYear() + '-' + String(date.getMonth()+1).padStart(2,'0') + '-' + String(date.getDate()).padStart(2,'0');
            if (this.calMode === 'check_in') {
                this.checkIn = str; this.checkOut = ''; this.calMode = 'check_out';
            } else {
                const ci = new Date(this.checkIn + 'T00:00:00');
                if (date <= ci) { this.checkIn = str; this.checkOut = ''; this.calMode = 'check_out'; }
                else            { this.checkOut = str; this.calMode = 'check_in'; }
            }
            this.computeNights();
        },
        isCalCheckIn(date) {
            if (!date || !this.checkIn) return false;
            return date.getTime() === new Date(this.checkIn + 'T00:00:00').getTime();
        },
        isCalCheckOut(date) {
            if (!date || !this.checkOut) return false;
            return date.getTime() === new Date(this.checkOut + 'T00:00:00').getTime();
        },
        isCalInRange(date) {
            if (!date || !this.checkIn) return false;
            const start = new Date(this.checkIn + 'T00:00:00');
            const end   = this.checkOut ? new Date(this.checkOut + 'T00:00:00') : this.calHover;
            if (!end) return false;
            return date > start && date < end;
        },
        isCalPast(date) {
            if (!date) return false;
            const today = new Date(); today.setHours(0,0,0,0); return date < today;
        },
        isCalToday(date) {
            if (!date) return false;
            const today = new Date(); today.setHours(0,0,0,0);
            return date.getTime() === today.getTime();
        },
        calNightsLabel(n) {
            const m = n % 10, c = n % 100;
            if (m === 1 && c !== 11) return 'ночь';
            if ([2,3,4].includes(m) && ![12,13,14].includes(c)) return 'ночи';
            return 'ночей';
        },
        computeNights() {
            if (this.checkIn && this.checkOut) {
                const d1 = new Date(this.checkIn), d2 = new Date(this.checkOut);
                this.nights = Math.max(0, Math.round((d2 - d1) / 86400000));
            } else { this.nights = 0; }
        },

        async loadRooms() {
            if (!this.checkIn || !this.checkOut || this.nights <= 0) return;
            this.loadingRooms = true; this.rooms = []; this.selectedRoom = null; this.suggestions = [];
            const res  = await fetch('/rooms/available?check_in=' + this.checkIn + '&check_out=' + this.checkOut);
            this.rooms = await res.json(); this.loadingRooms = false;
            // Restore prefilled room if it appears in results
            if (this.prefilledRoomId) {
                const match = this.rooms.find(r => r.id == this.prefilledRoomId);
                if (match) this.selectedRoom = match;
            }
            // Load suggestions immediately since guest is already known
            if (this.selectedGuests.length > 0) this.loadSuggestions();
        },

        async loadSuggestions() {
            if (!this.selectedGuests.length || !this.checkIn || !this.checkOut) return;
            this.loadingSuggestions = true;
            try {
                const params = new URLSearchParams({
                    guest_id: this.selectedGuests[0].id,
                    check_in: this.checkIn, check_out: this.checkOut, adults: this.adults,
                });
                const res = await fetch('/rooms/suggest?' + params);
                this.suggestions = await res.json();
            } catch(e) { this.suggestions = []; }
            this.loadingSuggestions = false;
        },

        async searchGuests() {
            if (this.guestQuery.length < 2) { this.guestResults = []; this.showGuestDropdown = false; return; }
            const res = await fetch('/guests/search?q=' + encodeURIComponent(this.guestQuery));
            this.guestResults = await res.json();
            this.showGuestDropdown = this.guestResults.length > 0;
        },
        addGuest(guest) {
            if (this.isGuestSelected(guest.id)) return;
            this.selectedGuests.push(guest);
            this.adults = this.selectedGuests.length;
            this.guestQuery = ''; this.guestResults = []; this.showGuestDropdown = false;
        },
        removeGuest(index) {
            this.selectedGuests.splice(index, 1);
            this.adults = Math.max(1, this.selectedGuests.length);
        },
        isGuestSelected(id) {
            return this.selectedGuests.some(g => g.id === id);
        },
        async createGuest() {
            if (!this.newGuest.first_name.trim() || this.creatingGuest) return;
            this.creatingGuest = true; this.newGuestError = '';
            this.newGuest.phone = this.phoneLocal.trim() ? this.phoneSel.c + this.phoneLocal.trim() : '';
            try {
                const res = await fetch('{{ route('guests.quick-store') }}', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content || '' },
                    body: JSON.stringify(this.newGuest),
                });
                if (!res.ok) { const err = await res.json(); this.newGuestError = err.message || 'Ошибка'; return; }
                const guest = await res.json();
                this.addGuest(guest);
                this.newGuest = { first_name: '', last_name: '', phone: '', email: '' };
                this.phoneSel = {iso:'uz',n:'Узбекистан',c:'+998'}; this.phoneLocal = '';
                this.showNewGuest = false;
            } catch(e) { this.newGuestError = 'Ошибка соединения'; }
            finally    { this.creatingGuest = false; }
        },

        saveDraft() {
            try {
                localStorage.setItem('_booking_draft', JSON.stringify({
                    step: this.step, checkIn: this.checkIn, checkOut: this.checkOut,
                    selectedRoom: this.selectedRoom, selectedGuests: this.selectedGuests,
                    adults: this.adults, children: this.children, notes: this.notes,
                    promoCode: this.promoCode, promoStatus: this.promoStatus,
                    promoMessage: this.promoMessage, discountPercent: this.discountPercent,
                }));
            } catch(e) {}
        },
        loadDraft() {
            if (this.prefilledRoomId || this.checkIn || this.checkOut) return;
            const navType = performance.getEntriesByType?.('navigation')[0]?.type
                         ?? (performance.navigation?.type === 1 ? 'reload' : 'navigate');
            if (navType !== 'reload') { this.clearDraft(); return; }
            try {
                const raw = localStorage.getItem('_booking_draft');
                if (!raw) return;
                const d = JSON.parse(raw);
                this.checkIn        = d.checkIn        || '';
                this.checkOut       = d.checkOut       || '';
                this.selectedRoom   = d.selectedRoom   || null;
                this.selectedGuests = Array.isArray(d.selectedGuests) ? d.selectedGuests : [];
                this.adults         = d.adults         || 1;
                this.children       = d.children       || 0;
                this.notes          = d.notes          || '';
                this.promoCode      = d.promoCode      || '';
                this.promoStatus    = d.promoStatus    || null;
                this.promoMessage   = d.promoMessage   || '';
                this.discountPercent= d.discountPercent|| 0;
                // Validate draft step against flow requirements
                const restoredStep = d.step || 1;
                const validStep = (restoredStep >= 2 && this.selectedGuests.length === 0) ? 1 : restoredStep;
                if (validStep > 1) this.$nextTick(() => { this.step = validStep; });
            } catch(e) {}
        },
        clearDraft() {
            try { localStorage.removeItem('_booking_draft'); } catch(e) {}
        },

        canGoToStep2() { return this.selectedGuests.length >= 1; },
        canGoToStep3() { return this.nights > 0 && this.selectedRoom !== null; },
        canSubmit()    { return this.selectedGuests.length >= 1 && !this.capacityExceeded && this.adults >= 1; },

        nextStep() {
            if (this.step === 1 && this.canGoToStep2()) {
                // Skip room selection when a room was pre-selected (e.g. from calendar)
                if (this.selectedRoom && this.nights > 0) {
                    this.step = 3;
                } else {
                    this.step = 2;
                    if (this.nights > 0 && this.rooms.length === 0 && !this.loadingRooms) this.loadRooms();
                }
            } else if (this.step === 2 && this.canGoToStep3()) {
                this.step = 3;
            }
        },
        prevStep() { if (this.step > 1) this.step--; },

        async checkPromo() {
            if (!this.promoCode.trim()) return;
            const res  = await fetch('/book/promo?code=' + encodeURIComponent(this.promoCode.trim().toUpperCase()));
            const data = await res.json();
            this.promoStatus     = data.valid ? 'valid' : 'invalid';
            this.promoMessage    = data.message;
            this.discountPercent = data.valid ? data.discount_percent : 0;
        },
    };
}
</script>

<style>[x-cloak] { display: none !important; }</style>
@endsection

