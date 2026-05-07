@extends('layouts.app')

@section('title', 'Групповое бронирование')

@section('content')
<script>
window.__groupBookingOpts = {
    name:     @json(old('name', '')),
    adults:   @json((int) old('adults', 2)),
    children: @json((int) old('children', 0)),
    notes:    @json(old('notes', '')),
    checkIn:  @json(old('check_in_date', '')),
    checkOut: @json(old('check_out_date', '')),
    oldRoomIds: @json(old('room_ids', [])),
};
</script>

<div class="max-w-3xl mx-auto" x-data="groupBookingForm(window.__groupBookingOpts)" x-init="init()">

    <div class="mb-6 flex items-center gap-3">
        <a href="{{ route('bookings.index') }}"
           class="inline-flex items-center gap-1.5 text-sm text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 transition-colors">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18"/></svg>
            Бронирования
        </a>
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 text-slate-300 dark:text-slate-600"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5"/></svg>
        <h1 class="text-xl font-bold text-slate-900 dark:text-slate-100">Групповое бронирование</h1>
    </div>

    @if($errors->any())
    <div class="mb-5 p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-xl text-sm text-red-700 dark:text-red-300">
        <ul class="list-disc list-inside space-y-1">
            @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
        </ul>
    </div>
    @endif

    <form method="POST" action="{{ route('group-bookings.store') }}">
        @csrf

        {{-- Hidden date/people inputs synced from Alpine --}}
        <input type="hidden" name="check_in_date"  :value="checkIn">
        <input type="hidden" name="check_out_date" :value="checkOut">
        <input type="hidden" name="adults"         :value="adults">
        <input type="hidden" name="children"       :value="children">

        {{-- ══ SECTION 1: Dates & Calendar ══ --}}
        <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm p-6 mb-5">
            <h2 class="text-base font-semibold text-slate-800 dark:text-slate-100 mb-5">Период проживания</h2>

            {{-- Date pills --}}
            <div class="flex flex-wrap items-center gap-3 mb-6">
                <button type="button" @click="calMode = 'check_in'"
                        :class="{
                            'bg-blue-600 text-white shadow-sm': calMode === 'check_in',
                            'bg-emerald-50 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-700': calMode !== 'check_in' && checkIn,
                            'bg-slate-100 dark:bg-slate-700 text-slate-500 dark:text-slate-400': calMode !== 'check_in' && !checkIn,
                        }"
                        class="flex items-center gap-2.5 px-4 py-2.5 rounded-xl text-sm font-semibold transition-all">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4 flex-shrink-0"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5"/></svg>
                    <div class="text-left">
                        <p class="text-[9px] font-bold uppercase tracking-widest opacity-70 leading-none mb-0.5">Заезд</p>
                        <p x-text="checkIn ? fmtDisplay(checkIn) : 'Выберите дату'"></p>
                    </div>
                </button>

                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4 text-slate-300 dark:text-slate-600 flex-shrink-0"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/></svg>

                <button type="button" @click="if(checkIn) calMode = 'check_out'" :disabled="!checkIn"
                        :class="{
                            'bg-orange-500 text-white shadow-sm': calMode === 'check_out',
                            'bg-emerald-50 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-700': calMode !== 'check_out' && checkOut,
                            'border border-dashed border-slate-300 dark:border-slate-600 text-slate-400': !checkOut && calMode !== 'check_out',
                        }"
                        class="flex items-center gap-2.5 px-4 py-2.5 rounded-xl text-sm font-semibold transition-all disabled:opacity-40 disabled:cursor-not-allowed">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4 flex-shrink-0"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5"/></svg>
                    <div class="text-left">
                        <p class="text-[9px] font-bold uppercase tracking-widest opacity-70 leading-none mb-0.5">Выезд</p>
                        <p x-text="checkOut ? fmtDisplay(checkOut) : 'Выберите дату'"></p>
                    </div>
                </button>

                <span x-show="nights > 0" x-cloak
                      class="bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 border border-blue-200 dark:border-blue-700 text-sm font-bold px-3 py-2 rounded-xl"
                      x-text="nights + ' ' + nightsLabel(nights)"></span>
            </div>

            {{-- Calendar nav --}}
            <div class="flex items-center justify-between mb-4">
                <button type="button" @click="calPrev()" :disabled="!calCanPrev()"
                        class="w-9 h-9 flex items-center justify-center rounded-xl hover:bg-slate-100 dark:hover:bg-slate-700 disabled:opacity-30 disabled:cursor-not-allowed transition-colors text-slate-600 dark:text-slate-400">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5"/></svg>
                </button>
                <div class="flex gap-4 lg:gap-32 text-center">
                    <span class="text-sm font-bold text-slate-700 dark:text-slate-300 capitalize" x-text="monthLabel(calYear, calMonth)"></span>
                    <span class="hidden lg:block text-sm font-bold text-slate-700 dark:text-slate-300 capitalize" x-text="monthLabel(nextYear, nextMonth)"></span>
                </div>
                <button type="button" @click="calNext()"
                        class="w-9 h-9 flex items-center justify-center rounded-xl hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors text-slate-600 dark:text-slate-400">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5"/></svg>
                </button>
            </div>

            {{-- Calendar grids --}}
            <div class="flex gap-8">
                {{-- Month 1 --}}
                <div class="flex-1">
                    <div class="grid grid-cols-7 mb-2">
                        <template x-for="d in ['Пн','Вт','Ср','Чт','Пт','Сб','Вс']" :key="'h1'+d">
                            <div class="text-center text-[10px] font-bold text-slate-400 uppercase py-1" x-text="d"></div>
                        </template>
                    </div>
                    <div class="grid grid-cols-7">
                        <template x-for="(day,i) in month1Days" :key="'d1'+i">
                            <div :class="{
                                'bg-blue-100 dark:bg-blue-900/40': day && isInRange(day),
                                'bg-blue-100 dark:bg-blue-900/40 rounded-l-full': day && isCheckIn(day) && checkOut,
                                'bg-blue-100 dark:bg-blue-900/40 rounded-r-full': day && isCheckOut(day) && checkIn,
                            }">
                                <button type="button"
                                        :disabled="!day || isPast(day)"
                                        @click="day && pickDay(day)"
                                        @mouseenter="hover = day"
                                        @mouseleave="hover = null"
                                        :class="{
                                            'bg-blue-600 text-white hover:bg-blue-700 rounded-full': day && isCheckIn(day),
                                            'bg-orange-500 text-white hover:bg-orange-600 rounded-full': day && isCheckOut(day),
                                            'text-slate-300 dark:text-slate-600 cursor-not-allowed pointer-events-none': !day || isPast(day),
                                            'text-slate-700 dark:text-slate-300 hover:bg-blue-100 dark:hover:bg-blue-900/40 hover:rounded-full cursor-pointer': day && !isPast(day) && !isCheckIn(day) && !isCheckOut(day),
                                            'ring-2 ring-inset ring-blue-400 rounded-full font-bold': day && isToday(day) && !isCheckIn(day) && !isCheckOut(day),
                                        }"
                                        class="w-9 h-9 text-sm flex items-center justify-center mx-auto transition-all">
                                    <span x-text="day ? day.getDate() : ''"></span>
                                </button>
                            </div>
                        </template>
                    </div>
                </div>
                {{-- Month 2 --}}
                <div class="flex-1 hidden lg:block">
                    <div class="grid grid-cols-7 mb-2">
                        <template x-for="d in ['Пн','Вт','Ср','Чт','Пт','Сб','Вс']" :key="'h2'+d">
                            <div class="text-center text-[10px] font-bold text-slate-400 uppercase py-1" x-text="d"></div>
                        </template>
                    </div>
                    <div class="grid grid-cols-7">
                        <template x-for="(day,i) in month2Days" :key="'d2'+i">
                            <div :class="{
                                'bg-blue-100 dark:bg-blue-900/40': day && isInRange(day),
                                'bg-blue-100 dark:bg-blue-900/40 rounded-l-full': day && isCheckIn(day) && checkOut,
                                'bg-blue-100 dark:bg-blue-900/40 rounded-r-full': day && isCheckOut(day) && checkIn,
                            }">
                                <button type="button"
                                        :disabled="!day || isPast(day)"
                                        @click="day && pickDay(day)"
                                        @mouseenter="hover = day"
                                        @mouseleave="hover = null"
                                        :class="{
                                            'bg-blue-600 text-white hover:bg-blue-700 rounded-full': day && isCheckIn(day),
                                            'bg-orange-500 text-white hover:bg-orange-600 rounded-full': day && isCheckOut(day),
                                            'text-slate-300 dark:text-slate-600 cursor-not-allowed pointer-events-none': !day || isPast(day),
                                            'text-slate-700 dark:text-slate-300 hover:bg-blue-100 dark:hover:bg-blue-900/40 hover:rounded-full cursor-pointer': day && !isPast(day) && !isCheckIn(day) && !isCheckOut(day),
                                            'ring-2 ring-inset ring-blue-400 rounded-full font-bold': day && isToday(day) && !isCheckIn(day) && !isCheckOut(day),
                                        }"
                                        class="w-9 h-9 text-sm flex items-center justify-center mx-auto transition-all">
                                    <span x-text="day ? day.getDate() : ''"></span>
                                </button>
                            </div>
                        </template>
                    </div>
                </div>
            </div>

            {{-- People row --}}
            <div class="mt-6 pt-5 border-t border-slate-100 dark:border-slate-700 flex flex-wrap items-center gap-6">
                <div class="flex items-center gap-3">
                    <span class="text-sm font-semibold text-slate-700 dark:text-slate-300">Взрослых</span>
                    <div class="flex items-center gap-2">
                        <button type="button" @click="if(adults > 1) adults--"
                                class="w-8 h-8 rounded-full border border-slate-200 dark:border-slate-600 flex items-center justify-center text-slate-600 dark:text-slate-400 hover:border-blue-400 hover:text-blue-600 transition-colors text-lg font-bold">−</button>
                        <span class="w-6 text-center font-bold text-slate-900 dark:text-slate-100" x-text="adults"></span>
                        <button type="button" @click="if(adults < 50) adults++"
                                class="w-8 h-8 rounded-full border border-slate-200 dark:border-slate-600 flex items-center justify-center text-slate-600 dark:text-slate-400 hover:border-blue-400 hover:text-blue-600 transition-colors text-lg font-bold">+</button>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <span class="text-sm font-semibold text-slate-700 dark:text-slate-300">Детей</span>
                    <div class="flex items-center gap-2">
                        <button type="button" @click="if(children > 0) children--"
                                class="w-8 h-8 rounded-full border border-slate-200 dark:border-slate-600 flex items-center justify-center text-slate-600 dark:text-slate-400 hover:border-blue-400 hover:text-blue-600 transition-colors text-lg font-bold">−</button>
                        <span class="w-6 text-center font-bold text-slate-900 dark:text-slate-100" x-text="children"></span>
                        <button type="button" @click="if(children < 50) children++"
                                class="w-8 h-8 rounded-full border border-slate-200 dark:border-slate-600 flex items-center justify-center text-slate-600 dark:text-slate-400 hover:border-blue-400 hover:text-blue-600 transition-colors text-lg font-bold">+</button>
                    </div>
                </div>
                <button type="button" @click="loadRooms()" :disabled="!checkIn || !checkOut"
                        :class="checkIn && checkOut ? 'bg-blue-600 hover:bg-blue-700 text-white' : 'bg-slate-100 dark:bg-slate-700 text-slate-400 cursor-not-allowed'"
                        class="ml-auto inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-semibold transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"/></svg>
                    Найти номера
                </button>
            </div>
        </div>

        {{-- ══ SECTION 2: Available Rooms ══ --}}
        <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm p-6 mb-5"
             x-show="roomsLoaded || loadingRooms" x-cloak>
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h2 class="text-base font-semibold text-slate-800 dark:text-slate-100">Доступные номера</h2>
                    <p class="text-xs text-slate-400 dark:text-slate-500 mt-0.5" x-show="rooms.length > 0 && !loadingRooms">
                        Выберите минимум 2 &nbsp;·&nbsp; <span class="font-semibold text-blue-600 dark:text-blue-400" x-text="selectedRoomIds.length + ' выбрано'"></span>
                    </p>
                </div>
                <span x-show="selectedRoomIds.length >= 2" x-cloak
                      class="inline-flex items-center gap-1.5 text-xs font-semibold text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-900/30 border border-emerald-200 dark:border-emerald-700 px-2.5 py-1 rounded-full">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-3.5 h-3.5"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                    Готово
                </span>
            </div>

            {{-- Loading --}}
            <div x-show="loadingRooms" class="flex items-center justify-center py-10">
                <div class="w-7 h-7 border-4 border-blue-500 border-t-transparent rounded-full animate-spin"></div>
                <span class="ml-3 text-sm text-slate-500 dark:text-slate-400">Загрузка…</span>
            </div>

            {{-- No rooms --}}
            <div x-show="!loadingRooms && roomsLoaded && rooms.length === 0" class="text-center py-10">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-10 h-10 mx-auto text-slate-300 dark:text-slate-600 mb-3"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75"/></svg>
                <p class="text-sm text-slate-400 dark:text-slate-500">Нет свободных номеров на выбранные даты</p>
            </div>

            {{-- Room grid grouped by floor --}}
            <div x-show="!loadingRooms && rooms.length > 0">
                <template x-for="(floorRooms, floor) in roomsByFloor" :key="'fl'+floor">
                    <div class="mb-5 last:mb-0">
                        <p class="text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-2"
                           x-text="'Этаж ' + floor"></p>
                        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-2">
                            <template x-for="room in floorRooms" :key="room.id">
                                <label :for="'room-'+room.id"
                                       :class="selectedRoomIds.includes(room.id)
                                           ? 'border-blue-500 bg-blue-50 dark:bg-blue-900/20 ring-2 ring-blue-400 ring-offset-1 dark:ring-offset-slate-800'
                                           : 'border-slate-200 dark:border-slate-600 hover:border-blue-300 dark:hover:border-blue-500'"
                                       class="flex items-start gap-2.5 p-3 rounded-xl border-2 cursor-pointer transition-all">
                                    <input type="checkbox" :id="'room-'+room.id" name="room_ids[]" :value="room.id"
                                           :checked="selectedRoomIds.includes(room.id)"
                                           @change="toggleRoom(room.id)"
                                           class="mt-0.5 rounded border-slate-300 text-blue-600 focus:ring-blue-500 flex-shrink-0">
                                    <div class="min-w-0">
                                        <p class="text-sm font-bold text-slate-900 dark:text-slate-100" x-text="'№ ' + room.number"></p>
                                        <p class="text-xs text-slate-500 dark:text-slate-400 truncate" x-text="room.room_type.name"></p>
                                        <p class="text-xs font-semibold text-blue-600 dark:text-blue-400 mt-0.5"
                                           x-text="formatPrice(room.room_type.base_price) + '/ночь'"></p>
                                    </div>
                                </label>
                            </template>
                        </div>
                    </div>
                </template>

                {{-- Total summary --}}
                <div x-show="selectedRoomIds.length >= 2 && nights > 0" x-cloak
                     class="mt-4 p-4 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-700 rounded-xl">
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-slate-600 dark:text-slate-300">
                            <span x-text="selectedRoomIds.length"></span> номера · <span x-text="nights + ' ' + nightsLabel(nights)"></span>
                        </span>
                        <span class="font-bold text-blue-700 dark:text-blue-300 text-base" x-text="formatPrice(totalPrice)"></span>
                    </div>
                </div>
            </div>
        </div>

        {{-- ══ SECTION 3: Group info & Guest ══ --}}
        <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm p-6 mb-5">
            <h2 class="text-base font-semibold text-slate-800 dark:text-slate-100 mb-5">Информация о группе</h2>

            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Название группы</label>
                    <input type="text" name="name" value="{{ old('name') }}"
                           placeholder="Корпоратив Acme Inc, Свадьба Ивановых…"
                           class="w-full px-3 py-2.5 border border-slate-200 dark:border-slate-600 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100">
                </div>

                {{-- Guest search --}}
                <div x-data="guestSearch()">
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                        Основной гость <span class="text-red-500">*</span>
                    </label>
                    <input type="hidden" name="guest_id" :value="selectedGuest?.id ?? ''">
                    <div class="relative">
                        <input type="text"
                               x-model="query"
                               @input.debounce.300ms="search()"
                               @focus="open = true"
                               placeholder="Поиск по имени или телефону…"
                               class="w-full px-3 py-2.5 border border-slate-200 dark:border-slate-600 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100">
                        <div x-show="open && results.length > 0" x-cloak
                             class="absolute z-20 top-full left-0 right-0 mt-1 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl shadow-xl overflow-hidden">
                            <template x-for="g in results" :key="g.id">
                                <button type="button"
                                        @click="select(g); open = false"
                                        class="w-full text-left px-4 py-3 hover:bg-blue-50 dark:hover:bg-blue-900/30 transition-colors border-b border-slate-100 dark:border-slate-700 last:border-0">
                                    <p class="text-sm font-semibold text-slate-900 dark:text-slate-100" x-text="g.full_name"></p>
                                    <p class="text-xs text-slate-400" x-text="g.phone ?? ''"></p>
                                </button>
                            </template>
                        </div>
                    </div>
                    <p x-show="selectedGuest" x-cloak class="mt-2 text-xs text-emerald-600 dark:text-emerald-400 font-medium">
                        ✓ Выбран: <span x-text="selectedGuest?.full_name"></span>
                    </p>
                    @error('guest_id')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Примечания</label>
                    <textarea name="notes" rows="2" maxlength="500"
                              class="w-full px-3 py-2.5 border border-slate-200 dark:border-slate-600 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100 resize-none"
                              placeholder="Особые пожелания, заметки…">{{ old('notes') }}</textarea>
                </div>
            </div>
        </div>

        {{-- ══ Submit ══ --}}
        <div class="flex items-center gap-3">
            <button type="submit"
                    :disabled="!checkIn || !checkOut || selectedRoomIds.length < 2"
                    :class="checkIn && checkOut && selectedRoomIds.length >= 2
                        ? 'bg-blue-600 hover:bg-blue-700 text-white cursor-pointer'
                        : 'bg-slate-200 dark:bg-slate-700 text-slate-400 cursor-not-allowed'"
                    class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-semibold rounded-xl transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                Создать групповое бронирование
            </button>
            <a href="{{ route('bookings.index') }}"
               class="px-5 py-2.5 text-sm font-semibold text-slate-600 dark:text-slate-300 bg-slate-100 dark:bg-slate-700 rounded-xl hover:bg-slate-200 dark:hover:bg-slate-600 transition-colors">
                Отмена
            </a>
        </div>
    </form>
</div>

<script>
function groupBookingForm(opts) {
    return {
        checkIn:  opts.checkIn  || '',
        checkOut: opts.checkOut || '',
        adults:   opts.adults   || 2,
        children: opts.children || 0,

        calMode: 'check_in',
        calYear: 0, calMonth: 0, hover: null,

        rooms: [], selectedRoomIds: opts.oldRoomIds || [],
        loadingRooms: false, roomsLoaded: false,

        get nights() {
            if (!this.checkIn || !this.checkOut) return 0;
            return Math.max(0, Math.round((new Date(this.checkOut) - new Date(this.checkIn)) / 86400000));
        },
        get nextYear()  { return this.calMonth === 11 ? this.calYear + 1 : this.calYear; },
        get nextMonth() { return (this.calMonth + 1) % 12; },
        get month1Days(){ return this.buildDays(this.calYear, this.calMonth); },
        get month2Days(){ return this.buildDays(this.nextYear, this.nextMonth); },
        get roomsByFloor() {
            const grouped = {};
            this.rooms.forEach(r => {
                const f = r.floor ?? 1;
                if (!grouped[f]) grouped[f] = [];
                grouped[f].push(r);
            });
            return grouped;
        },
        get totalPrice() {
            if (this.nights <= 0) return 0;
            return this.rooms
                .filter(r => this.selectedRoomIds.includes(r.id))
                .reduce((sum, r) => sum + (r.room_type.base_price * this.nights), 0);
        },

        init() {
            const n = new Date();
            this.calYear = n.getFullYear();
            this.calMonth = n.getMonth();
            if (this.checkIn && this.checkOut) this.loadRooms();
        },
        buildDays(y, m) {
            const first  = new Date(y, m, 1);
            const offset = (first.getDay() + 6) % 7;
            const total  = new Date(y, m + 1, 0).getDate();
            const days   = Array(offset).fill(null);
            for (let d = 1; d <= total; d++) days.push(new Date(y, m, d));
            while (days.length % 7 !== 0) days.push(null);
            return days;
        },
        monthLabel(y, m) {
            return new Date(y, m, 1).toLocaleDateString('ru-RU', { month: 'long', year: 'numeric' });
        },
        fmtDisplay(iso) {
            return new Date(iso + 'T00:00:00').toLocaleDateString('ru-RU', { day: 'numeric', month: 'short' });
        },
        calPrev() {
            if (this.calMonth === 0) { this.calYear--; this.calMonth = 11; } else this.calMonth--;
        },
        calNext() {
            if (this.calMonth === 11) { this.calYear++; this.calMonth = 0; } else this.calMonth++;
        },
        calCanPrev() {
            const n = new Date();
            return !(this.calYear === n.getFullYear() && this.calMonth === n.getMonth());
        },
        pickDay(date) {
            const today = new Date(); today.setHours(0,0,0,0);
            if (date < today) return;
            const str = date.toISOString().split('T')[0];
            if (this.calMode === 'check_in') {
                this.checkIn = str; this.checkOut = ''; this.calMode = 'check_out';
                this.rooms = []; this.roomsLoaded = false;
            } else {
                const ci = new Date(this.checkIn + 'T00:00:00');
                if (date <= ci) { this.checkIn = str; this.checkOut = ''; this.calMode = 'check_out'; this.rooms = []; this.roomsLoaded = false; }
                else            { this.checkOut = str; this.calMode = 'check_in'; this.$nextTick(() => this.loadRooms()); }
            }
        },
        isCheckIn(date)  { return this.checkIn  && date.getTime() === new Date(this.checkIn  + 'T00:00:00').getTime(); },
        isCheckOut(date) { return this.checkOut && date.getTime() === new Date(this.checkOut + 'T00:00:00').getTime(); },
        isInRange(date) {
            if (!this.checkIn) return false;
            const start = new Date(this.checkIn + 'T00:00:00');
            const end   = this.checkOut ? new Date(this.checkOut + 'T00:00:00') : this.hover;
            return end && date > start && date < end;
        },
        isPast(date) { const t = new Date(); t.setHours(0,0,0,0); return date < t; },
        isToday(date) { const t = new Date(); t.setHours(0,0,0,0); return date.getTime() === t.getTime(); },
        async loadRooms() {
            if (!this.checkIn || !this.checkOut) return;
            this.loadingRooms = true; this.rooms = []; this.selectedRoomIds = [];
            const res = await fetch('/rooms/available?check_in=' + this.checkIn + '&check_out=' + this.checkOut);
            this.rooms = await res.json();
            this.loadingRooms = false; this.roomsLoaded = true;
        },
        toggleRoom(id) {
            if (this.selectedRoomIds.includes(id)) {
                this.selectedRoomIds = this.selectedRoomIds.filter(x => x !== id);
            } else {
                this.selectedRoomIds.push(id);
            }
        },
        nightsLabel(n) {
            const m = n % 10, c = n % 100;
            if (m === 1 && c !== 11) return 'ночь';
            if ([2,3,4].includes(m) && ![12,13,14].includes(c)) return 'ночи';
            return 'ночей';
        },
        formatPrice(v) { return new Intl.NumberFormat('ru-RU').format(v) + ' сум'; },
    };
}

function guestSearch() {
    return {
        query: '', results: [], selectedGuest: null, open: false,
        async search() {
            if (this.query.length < 2) { this.results = []; return; }
            const res = await fetch('/guests/search?q=' + encodeURIComponent(this.query));
            this.results = await res.json();
        },
        select(g) { this.selectedGuest = g; this.query = g.full_name; this.results = []; },
    };
}
</script>
@endsection
