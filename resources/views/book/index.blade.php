<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Бронирование — {{ config('hotel.name', 'Отель') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        * { font-family: 'Inter', sans-serif; }
        [x-cloak] { display: none !important; }
        .hero-bg { background-image: url('/images/hotel-bg.jpg'); background-size: cover; background-position: center; }
    </style>
</head>
<body class="bg-slate-50 min-h-screen" x-data="bookingForm()" x-init="init()">

{{-- ══ HERO ══ --}}
<div class="relative hero-bg" style="min-height:520px">
    <div class="absolute inset-0" style="background:linear-gradient(160deg,rgba(2,8,23,.9) 0%,rgba(15,40,90,.8) 55%,rgba(29,78,216,.55) 100%)"></div>

    {{-- Nav --}}
    <nav class="relative z-10 flex items-center justify-between px-5 sm:px-10 py-5">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl flex items-center justify-center border border-white/20 bg-white/10">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-white">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21m-3.75 3.75h.008v.008h-.008v-.008Zm0 3h.008v.008h-.008v-.008Zm0 3h.008v.008h-.008v-.008Z"/>
                </svg>
            </div>
            <div>
                <p class="text-white font-bold text-sm leading-tight">{{ config('hotel.name', 'Отель') }}</p>
                <p class="text-blue-300 text-xs leading-tight">Официальное бронирование</p>
            </div>
        </div>
        @if(config('hotel.phone'))
        <a href="tel:{{ config('hotel.phone') }}"
           class="flex items-center gap-2 border border-white/25 bg-white/10 hover:bg-white/20 text-white text-sm font-medium px-4 py-2 rounded-xl transition-colors">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 0 0 2.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 0 1-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 0 0-1.091-.852H4.5A2.25 2.25 0 0 0 2.25 4.5v2.25Z"/>
            </svg>
            <span class="hidden sm:inline">{{ config('hotel.phone') }}</span>
            <span class="sm:hidden">Позвонить</span>
        </a>
        @endif
    </nav>

    {{-- Hero copy --}}
    <div class="relative z-10 px-5 sm:px-10 pt-6 pb-36">
        <div class="max-w-2xl">
            <div class="inline-flex items-center gap-2 bg-white/10 border border-white/20 text-blue-200 text-xs font-semibold px-3 py-1.5 rounded-full mb-5">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-3.5 h-3.5 text-yellow-400"><path fill-rule="evenodd" d="M10.868 2.884c-.321-.772-1.415-.772-1.736 0l-1.83 4.401-4.753.381c-.833.067-1.171 1.107-.536 1.651l3.62 3.102-1.106 4.637c-.194.813.691 1.456 1.405 1.02L10 15.591l4.069 2.485c.713.436 1.598-.207 1.404-1.02l-1.106-4.637 3.62-3.102c.635-.544.297-1.584-.536-1.65l-4.752-.382-1.831-4.401Z" clip-rule="evenodd"/></svg>
                Лучшая цена на официальном сайте
            </div>
            <h1 class="text-4xl sm:text-5xl font-black text-white mb-4 leading-tight tracking-tight">
                {{ config('hotel.name', 'Отель') }}
            </h1>
            <p class="text-blue-100 text-lg leading-relaxed mb-5 max-w-lg">
                Забронируйте напрямую и получите лучшую цену без комиссий
            </p>
            <div class="flex flex-wrap gap-3">
                <div class="flex items-center gap-1.5 text-sm text-white/80">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-4 h-4 text-emerald-400"><path fill-rule="evenodd" d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16Zm3.857-9.809a.75.75 0 0 0-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 1 0-1.06 1.061l2.5 2.5a.75.75 0 0 0 1.137-.089l4-5.5Z" clip-rule="evenodd"/></svg>
                    Бесплатная отмена
                </div>
                <div class="flex items-center gap-1.5 text-sm text-white/80">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-4 h-4 text-emerald-400"><path fill-rule="evenodd" d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16Zm3.857-9.809a.75.75 0 0 0-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 1 0-1.06 1.061l2.5 2.5a.75.75 0 0 0 1.137-.089l4-5.5Z" clip-rule="evenodd"/></svg>
                    Оплата при заезде
                </div>
                @if(config('hotel.address'))
                <div class="flex items-center gap-1.5 text-sm text-white/70">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-4 h-4 text-blue-300 flex-shrink-0"><path fill-rule="evenodd" d="m9.69 18.933.003.001C9.89 19.02 10 19 10 19s.11.02.308-.066l.002-.001.006-.003.018-.008a5.741 5.741 0 0 0 .281-.14c.186-.096.446-.24.757-.433.62-.384 1.445-.966 2.274-1.765C15.302 14.988 17 12.493 17 9A7 7 0 1 0 3 9c0 3.492 1.698 5.988 3.355 7.584a13.731 13.731 0 0 0 2.273 1.765 11.842 11.842 0 0 0 .976.544l.062.029.018.008.006.003ZM10 11.25a2.25 2.25 0 1 0 0-4.5 2.25 2.25 0 0 0 0 4.5Z" clip-rule="evenodd"/></svg>
                    {{ config('hotel.address') }}
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- ══ MAIN ══ --}}
<div class="max-w-7xl mx-auto px-4 sm:px-6 -mt-24 relative z-10 pb-24">

    {{-- ── Search widget ── --}}
    <div class="relative mb-3">
        <div class="bg-white rounded-2xl shadow-2xl border border-slate-100">
            <div class="grid grid-cols-1 sm:grid-cols-[1fr_1fr_auto_auto]">

                {{-- Check-in --}}
                <button type="button" @click="openCalendar('check_in')"
                        class="relative flex items-center gap-4 px-6 py-5 hover:bg-slate-50 transition-colors border-b sm:border-b-0 sm:border-r border-slate-100 text-left group rounded-tl-2xl sm:rounded-bl-2xl rounded-tr-2xl sm:rounded-tr-none">
                    <div class="w-11 h-11 rounded-xl flex items-center justify-center flex-shrink-0 transition-colors"
                         :class="calendarMode==='check_in' && calendarOpen ? 'bg-blue-600' : 'bg-blue-50 group-hover:bg-blue-100'">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"
                             :class="calendarMode==='check_in' && calendarOpen ? 'text-white' : 'text-blue-600'"
                             class="w-5 h-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5"/>
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400 mb-0.5">Заезд</p>
                        <p class="font-bold text-slate-900" x-text="checkIn ? fmtFull(checkIn) : 'Выберите дату'"></p>
                    </div>
                    <div x-show="calendarMode==='check_in' && calendarOpen" x-cloak
                         class="absolute bottom-0 left-6 right-6 h-0.5 bg-blue-600 rounded-full"></div>
                </button>

                {{-- Check-out --}}
                <button type="button" @click="openCalendar('check_out')"
                        class="relative flex items-center gap-4 px-6 py-5 hover:bg-slate-50 transition-colors border-b sm:border-b-0 sm:border-r border-slate-100 text-left group">
                    <div class="w-11 h-11 rounded-xl flex items-center justify-center flex-shrink-0 transition-colors"
                         :class="calendarMode==='check_out' && calendarOpen ? 'bg-orange-500' : 'bg-orange-50 group-hover:bg-orange-100'">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"
                             :class="calendarMode==='check_out' && calendarOpen ? 'text-white' : 'text-orange-500'"
                             class="w-5 h-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5"/>
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400 mb-0.5">Выезд</p>
                        <p class="font-bold text-slate-900" x-text="checkOut ? fmtFull(checkOut) : 'Выберите дату'"></p>
                    </div>
                    <div x-show="calendarMode==='check_out' && calendarOpen" x-cloak
                         class="absolute bottom-0 left-6 right-6 h-0.5 bg-orange-500 rounded-full"></div>
                </button>

                {{-- Guests --}}
                <div class="flex items-center gap-4 px-6 py-5 border-b sm:border-b-0 sm:border-r border-slate-100">
                    <div class="w-11 h-11 bg-violet-50 rounded-xl flex items-center justify-center flex-shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-violet-600">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400 mb-1.5">Гостей</p>
                        <div class="flex items-center gap-2.5">
                            <button type="button" @click="if(adults>1){adults--;onSearchChange();}"
                                    class="w-8 h-8 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg font-bold text-lg flex items-center justify-center transition-colors leading-none">−</button>
                            <span class="font-bold text-slate-900 text-lg w-5 text-center" x-text="adults"></span>
                            <button type="button" @click="if(adults<10){adults++;onSearchChange();}"
                                    class="w-8 h-8 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg font-bold text-lg flex items-center justify-center transition-colors leading-none">+</button>
                        </div>
                    </div>
                </div>

                {{-- Search button --}}
                <div class="flex items-center gap-3 px-5 py-5">
                    <div x-show="nights > 0" x-cloak class="hidden md:flex flex-col items-center min-w-[2.5rem]">
                        <p class="text-2xl font-extrabold text-slate-900 leading-none" x-text="nights"></p>
                        <p class="text-xs text-slate-400 font-medium" x-text="nightsLabel(nights)"></p>
                    </div>
                    <button type="button" @click="loadRooms()"
                            class="flex-1 sm:flex-none bg-blue-600 hover:bg-blue-700 active:scale-95 text-white font-bold px-7 py-3.5 rounded-xl transition-all flex items-center justify-center gap-2 text-sm"
                            style="box-shadow:0 4px 20px rgba(37,99,235,.4)">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"/>
                        </svg>
                        Найти
                    </button>
                </div>
            </div>
        </div>

        {{-- Calendar popup --}}
        <div x-show="calendarOpen" x-cloak
             @click.away="calendarOpen = false"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 -translate-y-2"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 translate-y-0"
             x-transition:leave-end="opacity-0 -translate-y-2"
             class="absolute z-50 top-full left-0 mt-3 bg-white rounded-2xl shadow-2xl border border-slate-200 p-5 w-full sm:w-[680px]">

            <div class="flex items-center justify-between mb-4">
                <button type="button" @click="prevMonth()" :disabled="!canGoPrev()"
                        class="w-9 h-9 flex items-center justify-center rounded-xl hover:bg-slate-100 disabled:opacity-30 disabled:cursor-not-allowed transition-colors text-slate-600">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5"/></svg>
                </button>
                <div class="flex gap-8 sm:gap-32 text-center">
                    <span class="text-sm font-bold text-slate-800 capitalize" x-text="monthLabel(viewYear, viewMonth)"></span>
                    <span class="hidden sm:block text-sm font-bold text-slate-800 capitalize" x-text="monthLabel(nextViewYear, nextViewMonth)"></span>
                </div>
                <button type="button" @click="nextMonth()"
                        class="w-9 h-9 flex items-center justify-center rounded-xl hover:bg-slate-100 transition-colors text-slate-600">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5"/></svg>
                </button>
            </div>

            <div class="flex items-center gap-2 mb-4 flex-wrap">
                <div class="flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold transition-colors"
                     :class="calendarMode==='check_in' ? 'bg-blue-600 text-white' : 'bg-slate-100 text-slate-600'">
                    Заезд: <span x-text="checkIn ? fmtDisplay(checkIn) : '—'"></span>
                </div>
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4 text-slate-300 flex-shrink-0"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/></svg>
                <div class="flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold transition-colors"
                     :class="calendarMode==='check_out' ? 'bg-orange-500 text-white' : 'bg-slate-100 text-slate-600'">
                    Выезд: <span x-text="checkOut ? fmtDisplay(checkOut) : '—'"></span>
                </div>
                <span x-show="nights > 0" x-cloak
                      class="ml-auto text-xs font-bold text-blue-600 bg-blue-50 px-3 py-1.5 rounded-xl"
                      x-text="nights + ' ' + nightsLabel(nights)"></span>
            </div>

            <div class="flex gap-6">
                <div class="flex-1">
                    <div class="grid grid-cols-7 mb-2">
                        <template x-for="d in ['Пн','Вт','Ср','Чт','Пт','Сб','Вс']" :key="'h1'+d">
                            <div class="text-center text-[10px] font-bold text-slate-400 uppercase py-1" x-text="d"></div>
                        </template>
                    </div>
                    <div class="grid grid-cols-7">
                        <template x-for="(day,idx) in viewMonthDays" :key="'m1'+idx">
                            <div :class="{'bg-blue-100':day&&isInRange(day),'bg-blue-100 rounded-l-full':day&&isCheckIn(day)&&checkOut&&!isSingleDay(),'bg-blue-100 rounded-r-full':day&&isCheckOut(day)&&checkIn&&!isSingleDay()}">
                                <button type="button" :disabled="!day||isPast(day)"
                                        @click="day&&selectDay(day)" @mouseenter="hoverDate=day" @mouseleave="hoverDate=null"
                                        :class="{'bg-blue-600 text-white hover:bg-blue-700 rounded-full':day&&isCheckIn(day),'bg-orange-500 text-white hover:bg-orange-600 rounded-full':day&&isCheckOut(day)&&!isSingleDay(),'text-slate-300 cursor-not-allowed pointer-events-none':!day||isPast(day),'text-slate-700 hover:bg-blue-50 hover:rounded-full cursor-pointer':day&&!isPast(day)&&!isCheckIn(day)&&!isCheckOut(day),'ring-2 ring-inset ring-blue-400 rounded-full font-bold':day&&isToday(day)&&!isCheckIn(day)&&!isCheckOut(day)}"
                                        class="w-9 h-9 text-sm flex items-center justify-center mx-auto transition-all">
                                    <span x-text="day?day.getDate():''"></span>
                                </button>
                            </div>
                        </template>
                    </div>
                </div>
                <div class="flex-1 hidden sm:block">
                    <div class="grid grid-cols-7 mb-2">
                        <template x-for="d in ['Пн','Вт','Ср','Чт','Пт','Сб','Вс']" :key="'h2'+d">
                            <div class="text-center text-[10px] font-bold text-slate-400 uppercase py-1" x-text="d"></div>
                        </template>
                    </div>
                    <div class="grid grid-cols-7">
                        <template x-for="(day,idx) in nextMonthDays" :key="'m2'+idx">
                            <div :class="{'bg-blue-100':day&&isInRange(day),'bg-blue-100 rounded-l-full':day&&isCheckIn(day)&&checkOut&&!isSingleDay(),'bg-blue-100 rounded-r-full':day&&isCheckOut(day)&&checkIn&&!isSingleDay()}">
                                <button type="button" :disabled="!day||isPast(day)"
                                        @click="day&&selectDay(day)" @mouseenter="hoverDate=day" @mouseleave="hoverDate=null"
                                        :class="{'bg-blue-600 text-white hover:bg-blue-700 rounded-full':day&&isCheckIn(day),'bg-orange-500 text-white hover:bg-orange-600 rounded-full':day&&isCheckOut(day)&&!isSingleDay(),'text-slate-300 cursor-not-allowed pointer-events-none':!day||isPast(day),'text-slate-700 hover:bg-blue-50 hover:rounded-full cursor-pointer':day&&!isPast(day)&&!isCheckIn(day)&&!isCheckOut(day),'ring-2 ring-inset ring-blue-400 rounded-full font-bold':day&&isToday(day)&&!isCheckIn(day)&&!isCheckOut(day)}"
                                        class="w-9 h-9 text-sm flex items-center justify-center mx-auto transition-all">
                                    <span x-text="day?day.getDate():''"></span>
                                </button>
                            </div>
                        </template>
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-between pt-4 mt-4 border-t border-slate-100">
                <button type="button" @click="checkIn=null;checkOut=null;calendarOpen=false;roomTypes=[];"
                        class="text-sm text-slate-500 hover:text-slate-700 font-medium transition-colors">Сбросить</button>
                <div class="flex gap-2">
                    <button type="button" @click="calendarOpen=false"
                            class="px-4 py-2 border border-slate-200 text-slate-600 text-sm font-semibold rounded-xl hover:bg-slate-50 transition-colors">Закрыть</button>
                    <button type="button" @click="calendarOpen=false;if(checkIn&&checkOut)loadRooms();"
                            :disabled="!checkIn||!checkOut"
                            class="px-4 py-2 bg-blue-600 disabled:bg-slate-200 disabled:text-slate-400 text-white text-sm font-semibold rounded-xl transition-colors">Применить</button>
                </div>
            </div>
        </div>
    </div>
    {{-- end search widget --}}

    {{-- ── Content ── --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Left: room cards --}}
        <div class="lg:col-span-2 space-y-5">

            {{-- Loading skeletons --}}
            <template x-if="loading">
                <div class="space-y-5">
                    <template x-for="i in 3" :key="i">
                        <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden animate-pulse">
                            <div class="h-52 bg-slate-100"></div>
                            <div class="p-5 space-y-3">
                                <div class="h-5 bg-slate-100 rounded-lg w-1/3"></div>
                                <div class="h-4 bg-slate-100 rounded-lg w-2/3"></div>
                                <div class="flex gap-2">
                                    <div class="h-6 bg-slate-100 rounded-full w-16"></div>
                                    <div class="h-6 bg-slate-100 rounded-full w-20"></div>
                                    <div class="h-6 bg-slate-100 rounded-full w-14"></div>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
            </template>

            {{-- No rooms --}}
            <div x-show="!loading && roomTypes.length === 0 && searched" x-cloak
                 class="bg-white rounded-2xl border border-slate-200 py-20 text-center">
                <div class="w-16 h-16 bg-slate-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-7 h-7 text-slate-400"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21"/></svg>
                </div>
                <p class="text-lg font-bold text-slate-700 mb-1">Свободных номеров нет</p>
                <p class="text-sm text-slate-400 mb-6">Попробуйте другие даты или свяжитесь с нами</p>
                @if(config('hotel.phone'))
                <a href="tel:{{ config('hotel.phone') }}" class="inline-flex items-center gap-2 px-6 py-3 bg-blue-600 text-white text-sm font-bold rounded-xl hover:bg-blue-700 transition">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 0 0 2.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 0 1-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 0 0-1.091-.852H4.5A2.25 2.25 0 0 0 2.25 4.5v2.25Z"/></svg>
                    Позвонить нам
                </a>
                @endif
            </div>

            {{-- Prompt --}}
            <div x-show="!loading && !searched"
                 class="bg-white rounded-2xl border-2 border-dashed border-slate-200 py-20 text-center">
                <div class="w-16 h-16 bg-blue-50 rounded-2xl flex items-center justify-center mx-auto mb-4">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-7 h-7 text-blue-500"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5"/></svg>
                </div>
                <p class="text-lg font-bold text-slate-700 mb-1">Выберите даты</p>
                <p class="text-sm text-slate-400">Нажмите на поле «Заезд» или «Выезд» выше</p>
            </div>

            {{-- Room cards --}}
            <div x-show="!loading && roomTypes.length > 0" x-cloak class="space-y-5">
                <template x-for="(type, index) in roomTypes" :key="type.id">
                    <div class="bg-white rounded-2xl border-2 overflow-hidden transition-all duration-200"
                         :class="selectedType?.id === type.id
                             ? 'border-blue-500 shadow-lg shadow-blue-500/15'
                             : 'border-slate-200 hover:border-slate-300 hover:shadow-md'"
                         x-data="{ imgIdx: 0 }">

                        {{-- Card image --}}
                        <div class="relative overflow-hidden select-none" style="height:220px">
                            {{-- Image or gradient placeholder --}}
                            <template x-if="type.images && type.images.length > 0">
                                <img :src="type.images[imgIdx]" :alt="type.name"
                                     class="w-full h-full object-cover transition-opacity duration-300">
                            </template>
                            <template x-if="!type.images || type.images.length === 0">
                                <div class="w-full h-full flex items-center justify-center"
                                     :class="[
                                         index % 4 === 0 ? 'bg-gradient-to-br from-blue-600 to-blue-800' : '',
                                         index % 4 === 1 ? 'bg-gradient-to-br from-violet-600 to-violet-800' : '',
                                         index % 4 === 2 ? 'bg-gradient-to-br from-emerald-600 to-emerald-800' : '',
                                         index % 4 === 3 ? 'bg-gradient-to-br from-slate-600 to-slate-800' : '',
                                     ]">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor" class="w-16 h-16 text-white/30">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 21v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21m0 0h4.5V3.545M12.75 21h7.5V10.75M2.25 21h1.5m18 0h-18M2.25 9l4.5-1.636M18.75 3l-1.5.545m0 6.205 3 1m1.5.5-1.5-.5M6.75 7.364V3h-3v18m3-13.636 10.5-3.819"/>
                                    </svg>
                                </div>
                            </template>

                            {{-- Gradient overlay --}}
                            <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent pointer-events-none"></div>

                            {{-- Image navigation --}}
                            <template x-if="type.images && type.images.length > 1">
                                <div>
                                    <button type="button" @click.stop="imgIdx=(imgIdx-1+type.images.length)%type.images.length"
                                            class="absolute left-3 top-1/2 -translate-y-1/2 w-9 h-9 bg-black/40 hover:bg-black/60 text-white rounded-full flex items-center justify-center transition-colors">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5"/></svg>
                                    </button>
                                    <button type="button" @click.stop="imgIdx=(imgIdx+1)%type.images.length"
                                            class="absolute right-3 top-1/2 -translate-y-1/2 w-9 h-9 bg-black/40 hover:bg-black/60 text-white rounded-full flex items-center justify-center transition-colors">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5"/></svg>
                                    </button>
                                    <div class="absolute bottom-4 left-0 right-0 flex justify-center gap-1.5">
                                        <template x-for="(_, di) in type.images" :key="di">
                                            <button type="button" @click.stop="imgIdx=di"
                                                    class="w-1.5 h-1.5 rounded-full transition-all"
                                                    :class="di===imgIdx ? 'bg-white w-4' : 'bg-white/50'"></button>
                                        </template>
                                    </div>
                                    <span class="absolute top-3 right-3 text-[10px] text-white bg-black/40 backdrop-blur-sm px-2 py-0.5 rounded-full font-semibold"
                                          x-text="(imgIdx+1)+' / '+type.images.length"></span>
                                </div>
                            </template>

                            {{-- Pricing banner --}}
                            <template x-if="type.pricing_banner">
                                <div class="absolute top-3 left-3 flex items-center gap-1 bg-orange-500 text-white text-xs font-bold px-2.5 py-1 rounded-full shadow">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-3.5 h-3.5"><path fill-rule="evenodd" d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16Zm.75-11.25a.75.75 0 0 0-1.5 0v2.5h-2.5a.75.75 0 0 0 0 1.5h2.5v2.5a.75.75 0 0 0 1.5 0v-2.5h2.5a.75.75 0 0 0 0-1.5h-2.5v-2.5Z" clip-rule="evenodd"/></svg>
                                    <span x-text="type.pricing_banner"></span>
                                </div>
                            </template>

                            {{-- Selected badge --}}
                            <div x-show="selectedType?.id===type.id" x-cloak
                                 class="absolute top-3 right-3 flex items-center gap-1 bg-blue-600 text-white text-xs font-bold px-2.5 py-1 rounded-full shadow">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-3.5 h-3.5"><path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 0 1 .143 1.052l-8 10.5a.75.75 0 0 1-1.127.075l-4.5-4.5a.75.75 0 0 1 1.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 0 1 1.05-.143Z" clip-rule="evenodd"/></svg>
                                Выбрано
                            </div>

                            {{-- Room name overlay --}}
                            <div class="absolute bottom-0 left-0 right-0 px-5 pb-4">
                                <h3 class="text-xl font-black text-white leading-tight" x-text="type.name"></h3>
                            </div>
                        </div>

                        {{-- Card body --}}
                        <div class="p-5">
                            <div class="flex items-start justify-between gap-4 mb-4">
                                <div class="flex-1 min-w-0">
                                    {{-- Capacity --}}
                                    <div class="flex items-center gap-3 mb-2">
                                        <div class="flex items-center gap-1 text-sm text-slate-500">
                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-4 h-4 text-slate-400"><path d="M10 8a3 3 0 1 0 0-6 3 3 0 0 0 0 6ZM3.465 14.493a1.23 1.23 0 0 0 .41 1.412A9.957 9.957 0 0 0 10 18c2.31 0 4.438-.784 6.131-2.1.43-.333.604-.903.408-1.41a7.002 7.002 0 0 0-13.074.003Z"/></svg>
                                            <span x-text="'до ' + type.capacity + ' гостей'"></span>
                                        </div>
                                    </div>
                                    {{-- Description --}}
                                    <p x-show="type.description" class="text-sm text-slate-500 leading-relaxed line-clamp-2" x-text="type.description"></p>
                                </div>
                                {{-- Price --}}
                                <div class="text-right flex-shrink-0">
                                    <p class="text-2xl font-extrabold text-slate-900 leading-none" x-text="formatPrice(type.price_per_night)"></p>
                                    <p class="text-xs text-slate-400 mt-0.5">за ночь</p>
                                    <p x-show="type.nights > 1" x-cloak
                                       class="text-sm font-semibold text-blue-600 mt-1"
                                       x-text="formatPrice(type.total_price) + ' за ' + type.nights + ' ' + nightsLabel(type.nights)"></p>
                                </div>
                            </div>

                            {{-- Amenities --}}
                            <div x-show="type.amenities && type.amenities.length > 0" class="flex flex-wrap gap-1.5 mb-4">
                                <template x-for="a in (type.amenities || []).slice(0, 6)" :key="a">
                                    <span class="inline-flex items-center text-xs text-slate-500 bg-slate-50 border border-slate-200 px-2.5 py-1 rounded-full font-medium" x-text="a"></span>
                                </template>
                                <span x-show="(type.amenities||[]).length > 6" x-cloak
                                      class="text-xs text-slate-400 px-2.5 py-1 rounded-full border border-dashed border-slate-200"
                                      x-text="'+' + ((type.amenities||[]).length - 6) + ' ещё'"></span>
                            </div>

                            {{-- Action row --}}
                            <div class="flex items-center justify-between gap-3 pt-4 border-t border-slate-100">
                                <div class="text-sm text-slate-400" x-show="nights > 0" x-cloak>
                                    <span x-text="nights + ' ' + nightsLabel(nights) + ' · ' + adults + ' ' + guestsLabel(adults)"></span>
                                </div>
                                <button type="button" @click="selectRoom(type)"
                                        class="ml-auto flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm font-bold transition-all"
                                        :class="selectedType?.id===type.id
                                            ? 'bg-blue-600 text-white shadow-lg shadow-blue-500/30'
                                            : 'bg-slate-900 hover:bg-blue-600 text-white'">
                                    <template x-if="selectedType?.id===type.id">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-4 h-4"><path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 0 1 .143 1.052l-8 10.5a.75.75 0 0 1-1.127.075l-4.5-4.5a.75.75 0 0 1 1.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 0 1 1.05-.143Z" clip-rule="evenodd"/></svg>
                                    </template>
                                    <span x-text="selectedType?.id===type.id ? 'Выбрано' : 'Выбрать'"></span>
                                </button>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        {{-- ── Sidebar ── --}}
        <div class="lg:col-span-1">
            <div class="sticky top-6 space-y-4">

                {{-- Room selected — summary + form --}}
                <div x-show="selectedType" x-cloak
                     x-transition:enter="transition ease-out duration-300"
                     x-transition:enter-start="opacity-0 translate-y-3"
                     x-transition:enter-end="opacity-100 translate-y-0"
                     class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">

                    {{-- Summary header --}}
                    <div class="px-5 py-4 text-white" style="background:linear-gradient(135deg,#1e40af,#2563eb)">
                        <div class="flex items-center justify-between mb-3">
                            <p class="font-black text-white text-base" x-text="selectedType?.name ?? ''"></p>
                            <button type="button" @click="selectedType=null"
                                    class="w-7 h-7 bg-white/15 hover:bg-white/25 rounded-full flex items-center justify-center transition-colors">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-3.5 h-3.5 text-white"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg>
                            </button>
                        </div>
                        <div class="grid grid-cols-2 gap-3 text-sm">
                            <div>
                                <p class="text-blue-300 text-xs mb-0.5">Заезд</p>
                                <p class="font-semibold text-white" x-text="checkIn ? fmtDisplay(checkIn) : '—'"></p>
                            </div>
                            <div>
                                <p class="text-blue-300 text-xs mb-0.5">Выезд</p>
                                <p class="font-semibold text-white" x-text="checkOut ? fmtDisplay(checkOut) : '—'"></p>
                            </div>
                        </div>
                        <div class="mt-3 pt-3 border-t border-white/20 flex items-center justify-between">
                            <div>
                                <p class="text-blue-300 text-xs" x-text="nights + ' ' + nightsLabel(nights) + ' · ' + adults + ' ' + guestsLabel(adults)"></p>
                                <div x-show="discountPercent > 0" x-cloak class="text-emerald-300 text-xs font-semibold mt-0.5">
                                    Скидка <span x-text="discountPercent"></span>% применена
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="text-blue-300 text-xs">Итого</p>
                                <p class="text-xl font-extrabold text-white" x-text="formatPrice(discountedTotal)"></p>
                            </div>
                        </div>
                    </div>

                    {{-- Booking form --}}
                    <div class="p-5" id="booking-form">
                        <p class="text-sm font-bold text-slate-800 mb-4">Ваши данные</p>
                        <form method="POST" action="{{ route('book.store') }}" @submit="submitting=true" class="space-y-3">
                            @csrf
                            <input type="hidden" name="room_type_id" :value="selectedType?.id ?? ''">
                            <input type="hidden" name="check_in"     :value="checkInStr">
                            <input type="hidden" name="check_out"    :value="checkOutStr">
                            <input type="hidden" name="adults"       :value="adults">
                            <input type="hidden" name="children"     value="0">

                            <div class="grid grid-cols-2 gap-2.5">
                                <div>
                                    <label class="block text-xs font-semibold text-slate-500 mb-1.5">Имя <span class="text-red-400">*</span></label>
                                    <input type="text" name="first_name" required maxlength="80" placeholder="Иван"
                                           class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white focus:border-transparent transition-all">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-slate-500 mb-1.5">Фамилия <span class="text-red-400">*</span></label>
                                    <input type="text" name="last_name" required maxlength="80" placeholder="Иванов"
                                           class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white focus:border-transparent transition-all">
                                </div>
                            </div>
                            <div class="col-span-2" x-data="bookPhoneDropdown()">
                                <label class="block text-xs font-semibold text-slate-500 mb-1.5">Телефон <span class="text-red-400">*</span></label>
                                <div class="relative">
                                    <div class="flex rounded-xl border border-slate-200 overflow-hidden focus-within:ring-2 focus-within:ring-blue-500 bg-slate-50">
                                        <button type="button" @click="open = !open"
                                                class="flex items-center gap-1.5 pl-3 pr-2 py-2.5 shrink-0 bg-slate-100 hover:bg-slate-200 border-r border-slate-200 transition-colors">
                                            <img :src="'https://flagcdn.com/20x15/' + sel.iso + '.png'" :alt="sel.n" class="w-5 h-auto rounded-sm flex-shrink-0">
                                            <span x-text="sel.c" class="text-sm font-semibold text-slate-700 tabular-nums"></span>
                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-3 h-3 text-slate-400 shrink-0"><path fill-rule="evenodd" d="M5.22 8.22a.75.75 0 0 1 1.06 0L10 11.94l3.72-3.72a.75.75 0 1 1 1.06 1.06l-4.25 4.25a.75.75 0 0 1-1.06 0L5.22 9.28a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd"/></svg>
                                        </button>
                                        <input type="tel" x-model="local" placeholder="901 234 567" required
                                               class="flex-1 min-w-0 bg-transparent px-3 py-2.5 text-sm focus:outline-none placeholder-slate-400">
                                    </div>
                                    <div x-show="open" x-cloak @click.away="open = false"
                                         class="absolute top-full left-0 z-50 mt-1 w-60 rounded-xl bg-white border border-slate-200 shadow-xl overflow-hidden">
                                        <div class="p-2 border-b border-slate-100">
                                            <input type="text" x-model="search" placeholder="Поиск страны…" @click.stop
                                                   class="w-full px-2.5 py-1.5 text-xs bg-slate-50 border border-slate-200 rounded-lg text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-1 focus:ring-blue-500">
                                        </div>
                                        <div class="overflow-y-auto max-h-52">
                                            <template x-for="c in filtered" :key="c.n">
                                                <button type="button" @click="pick(c)"
                                                        :class="sel.n === c.n ? 'bg-blue-50' : 'hover:bg-slate-50'"
                                                        class="w-full flex items-center gap-2.5 px-3 py-2 text-left transition-colors">
                                                    <img :src="'https://flagcdn.com/20x15/' + c.iso + '.png'" :alt="c.n" class="w-5 h-auto rounded-sm flex-shrink-0">
                                                    <span x-text="c.n" :class="sel.n === c.n ? 'text-blue-700 font-medium' : 'text-slate-700'" class="flex-1 text-xs truncate"></span>
                                                    <span x-text="c.c" class="text-xs text-slate-400 tabular-nums shrink-0"></span>
                                                </button>
                                            </template>
                                        </div>
                                    </div>
                                    <input type="hidden" name="phone" :value="local.trim() ? sel.c + local.trim() : ''">
                                </div>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-500 mb-1.5">Email</label>
                                <input type="email" name="email" maxlength="150" placeholder="ivan@example.com"
                                       class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white focus:border-transparent transition-all">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-500 mb-1.5">Пожелания</label>
                                <textarea name="notes" rows="2" maxlength="500" placeholder="Ранний заезд, детская кроватка…"
                                          class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white focus:border-transparent transition-all resize-none"></textarea>
                            </div>

                            {{-- Promo --}}
                            <div class="rounded-xl border border-slate-200 overflow-hidden">
                                <div class="flex">
                                    <input type="text" x-model="promoCode" placeholder="Промокод"
                                           @input="promoStatus=null" @keydown.enter.prevent="checkPromo()"
                                           class="flex-1 px-3 py-2.5 text-sm font-mono uppercase placeholder:normal-case placeholder:font-normal placeholder:text-slate-400 bg-slate-50 focus:outline-none focus:bg-white transition-colors border-0">
                                    <button type="button" @click="checkPromo()"
                                            class="px-3 py-2 bg-slate-800 hover:bg-slate-900 text-white text-xs font-bold transition-colors whitespace-nowrap">
                                        Применить
                                    </button>
                                </div>
                                <p x-show="promoStatus==='valid'" x-cloak class="px-3 pb-2 text-xs text-emerald-600 font-semibold flex items-center gap-1">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-3.5 h-3.5"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                                    <span x-text="promoMessage"></span>
                                </p>
                                <p x-show="promoStatus==='invalid'" x-cloak class="px-3 pb-2 text-xs text-red-500 flex items-center gap-1">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-3.5 h-3.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg>
                                    <span x-text="promoMessage"></span>
                                </p>
                            </div>
                            <input type="hidden" name="promo_code" :value="promoStatus==='valid' ? promoCode.trim().toUpperCase() : ''">

                            @if($errors->any())
                            <div class="p-3 bg-red-50 border border-red-200 rounded-xl text-xs text-red-600 space-y-0.5">
                                @foreach($errors->all() as $e)<p>{{ $e }}</p>@endforeach
                            </div>
                            @endif

                            <button type="submit" :disabled="submitting"
                                    class="w-full bg-blue-600 hover:bg-blue-700 disabled:opacity-60 text-white font-bold py-3.5 rounded-xl transition-all flex items-center justify-center gap-2 text-sm"
                                    style="box-shadow:0 4px 14px rgba(37,99,235,.35)">
                                <svg x-show="submitting" x-cloak class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                                </svg>
                                <span x-text="submitting ? 'Отправляем…' : 'Отправить запрос'"></span>
                            </button>
                            <p class="text-center text-xs text-slate-400">Подтверждение придёт на телефон в течение 15 минут</p>
                        </form>
                    </div>
                </div>

                {{-- Placeholder --}}
                <div x-show="!selectedType"
                     class="bg-white rounded-2xl border-2 border-dashed border-slate-200 p-8 text-center">
                    <div class="w-14 h-14 bg-blue-50 rounded-2xl flex items-center justify-center mx-auto mb-3">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-7 h-7 text-blue-500"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21"/></svg>
                    </div>
                    <p class="text-sm font-bold text-slate-700 mb-1">Выберите номер</p>
                    <p class="text-xs text-slate-400">Нажмите «Выбрать» на понравившемся варианте</p>
                </div>

                {{-- Contact info --}}
                @if(config('hotel.phone') || config('hotel.address'))
                <div class="bg-white rounded-2xl border border-slate-200 p-4 space-y-3">
                    @if(config('hotel.phone'))
                    <a href="tel:{{ config('hotel.phone') }}" class="flex items-center gap-3 group">
                        <div class="w-9 h-9 bg-blue-50 group-hover:bg-blue-100 rounded-xl flex items-center justify-center flex-shrink-0 transition-colors">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 text-blue-600"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 0 0 2.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 0 1-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 0 0-1.091-.852H4.5A2.25 2.25 0 0 0 2.25 4.5v2.25Z"/></svg>
                        </div>
                        <div>
                            <p class="text-xs text-slate-400 leading-tight">Позвонить нам</p>
                            <p class="text-sm font-bold text-slate-900 group-hover:text-blue-600 transition-colors">{{ config('hotel.phone') }}</p>
                        </div>
                    </a>
                    @endif
                    @if(config('hotel.address'))
                    <div class="flex items-start gap-3">
                        <div class="w-9 h-9 bg-slate-50 rounded-xl flex items-center justify-center flex-shrink-0 mt-0.5">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 text-slate-400"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z"/></svg>
                        </div>
                        <p class="text-sm text-slate-500 leading-relaxed">{{ config('hotel.address') }}</p>
                    </div>
                    @endif
                </div>
                @endif

            </div>
        </div>
    </div>
</div>

{{-- ── Mobile sticky CTA ── --}}
<div x-show="selectedType" x-cloak
     x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="opacity-0 translate-y-full"
     x-transition:enter-end="opacity-100 translate-y-0"
     class="lg:hidden fixed bottom-0 left-0 right-0 z-50 bg-white border-t border-slate-200 px-4 py-3 shadow-2xl">
    <div class="flex items-center gap-3">
        <div class="flex-1 min-w-0">
            <p class="text-xs text-slate-500 truncate" x-text="selectedType?.name ?? ''"></p>
            <p class="font-extrabold text-slate-900 leading-tight" x-text="formatPrice(discountedTotal)"></p>
        </div>
        <a href="#booking-form"
           class="flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white font-bold px-5 py-3 rounded-xl text-sm transition-colors"
           style="box-shadow:0 4px 14px rgba(37,99,235,.4)">
            Забронировать
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/></svg>
        </a>
    </div>
</div>

<footer class="border-t border-slate-200 bg-white mt-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 py-5 flex items-center justify-between text-xs text-slate-400">
        <span>© {{ date('Y') }} {{ config('hotel.name', 'Отель') }}</span>
        <a href="https://osg.uz" target="_blank" rel="noopener" class="hover:text-slate-600 transition-colors">Разработано OSG Company</a>
    </div>
</footer>

<script>
function bookingForm() {
    return {
        calendarOpen: false, calendarMode: 'check_in',
        viewYear: 0, viewMonth: 0, hoverDate: null,
        checkIn: null, checkOut: null, adults: 2,
        roomTypes: [], selectedType: null,
        loading: false, searched: false, debounceTimer: null,
        submitting: false,
        promoCode: '', promoStatus: null, promoMessage: '', discountPercent: 0,

        get nights() {
            if (!this.checkIn || !this.checkOut) return 0;
            return Math.round((this.checkOut - this.checkIn) / 86400000);
        },
        get checkInStr()  { return this.checkIn  ? this.fmtIso(this.checkIn)  : ''; },
        get checkOutStr() { return this.checkOut ? this.fmtIso(this.checkOut) : ''; },
        get discountedTotal() {
            const base = this.selectedType?.total_price ?? 0;
            return Math.round(base - base * this.discountPercent / 100);
        },
        get viewMonthDays()  { return this.buildMonthDays(this.viewYear, this.viewMonth); },
        get nextViewYear()   { return this.viewMonth === 11 ? this.viewYear + 1 : this.viewYear; },
        get nextViewMonth()  { return (this.viewMonth + 1) % 12; },
        get nextMonthDays()  { return this.buildMonthDays(this.nextViewYear, this.nextViewMonth); },

        init() {
            const t = new Date(); t.setHours(0,0,0,0);
            const tm = new Date(t); tm.setDate(tm.getDate() + 1);
            this.checkIn = t; this.checkOut = tm;
            this.viewYear = t.getFullYear(); this.viewMonth = t.getMonth();
            this.loadRooms();
        },
        buildMonthDays(year, month) {
            const first = new Date(year, month, 1);
            const offset = (first.getDay() + 6) % 7;
            const total  = new Date(year, month + 1, 0).getDate();
            const days   = Array(offset).fill(null);
            for (let d = 1; d <= total; d++) days.push(new Date(year, month, d));
            while (days.length % 7 !== 0) days.push(null);
            return days;
        },
        monthLabel(y, m) { return new Date(y, m, 1).toLocaleDateString('ru-RU', { month: 'long', year: 'numeric' }); },
        prevMonth() { if (this.viewMonth === 0) { this.viewYear--; this.viewMonth = 11; } else this.viewMonth--; },
        nextMonth() { if (this.viewMonth === 11) { this.viewYear++; this.viewMonth = 0; } else this.viewMonth++; },
        canGoPrev() { const n = new Date(); return !(this.viewYear === n.getFullYear() && this.viewMonth === n.getMonth()); },
        openCalendar(mode) { this.calendarMode = mode; this.calendarOpen = true; },
        selectDay(date) {
            if (!date) return;
            const today = new Date(); today.setHours(0,0,0,0);
            if (date < today) return;
            if (this.calendarMode === 'check_in') {
                this.checkIn = date;
                if (this.checkOut && this.checkOut <= date) this.checkOut = null;
                this.calendarMode = 'check_out';
            } else {
                if (date <= this.checkIn) {
                    this.checkIn = date; this.checkOut = null; this.calendarMode = 'check_out';
                } else {
                    this.checkOut = date;
                    this.calendarOpen = false;
                    clearTimeout(this.debounceTimer);
                    this.debounceTimer = setTimeout(() => this.loadRooms(), 300);
                }
            }
        },
        isCheckIn(date)  { return date && this.checkIn  && date.getTime() === this.checkIn.getTime(); },
        isCheckOut(date) { return date && this.checkOut && date.getTime() === this.checkOut.getTime(); },
        isInRange(date) {
            if (!date || !this.checkIn) return false;
            const end = this.checkOut || this.hoverDate;
            if (!end) return false;
            return date > this.checkIn && date < end;
        },
        isPast(date) { if (!date) return false; const t = new Date(); t.setHours(0,0,0,0); return date < t; },
        isToday(date) { if (!date) return false; const t = new Date(); t.setHours(0,0,0,0); return date.getTime() === t.getTime(); },
        isSingleDay() { return this.checkIn && this.checkOut && this.checkIn.getTime() === this.checkOut.getTime(); },
        onSearchChange() {
            clearTimeout(this.debounceTimer);
            this.debounceTimer = setTimeout(() => this.loadRooms(), 400);
        },
        loadRooms() {
            if (!this.checkIn || !this.checkOut || this.nights <= 0) return;
            this.loading = true; this.selectedType = null;
            const p = new URLSearchParams({ check_in: this.checkInStr, check_out: this.checkOutStr, adults: this.adults });
            fetch(`{{ route('book.rooms') }}?${p}`)
                .then(r => r.json())
                .then(d => { this.roomTypes = d; this.searched = true; this.loading = false; })
                .catch(() => { this.loading = false; });
        },
        selectRoom(type) {
            this.selectedType = type;
            this.$nextTick(() => {
                document.querySelector('#booking-form')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
            });
        },
        async checkPromo() {
            if (!this.promoCode.trim()) return;
            const res  = await fetch(`{{ route('book.promo') }}?code=${encodeURIComponent(this.promoCode.trim().toUpperCase())}`);
            const data = await res.json();
            this.promoStatus    = data.valid ? 'valid' : 'invalid';
            this.promoMessage   = data.message;
            this.discountPercent = data.valid ? data.discount_percent : 0;
        },
        fmtIso(d) {
            if (!d) return '';
            // Use local date components — toISOString() returns UTC which shifts the date in UTC+5
            return d.getFullYear() + '-'
                + String(d.getMonth() + 1).padStart(2, '0') + '-'
                + String(d.getDate()).padStart(2, '0');
        },
        fmtDisplay(d) { if (!d) return ''; return d.toLocaleDateString('ru-RU', { day: 'numeric', month: 'short' }); },
        fmtFull(d) { if (!d) return ''; return d.toLocaleDateString('ru-RU', { weekday: 'short', day: 'numeric', month: 'long' }); },
        formatPrice: v => new Intl.NumberFormat('ru-RU').format(v) + ' сум',
        nightsLabel(n) {
            const m = n % 10, c = n % 100;
            if (m === 1 && c !== 11) return 'ночь';
            if ([2,3,4].includes(m) && ![12,13,14].includes(c)) return 'ночи';
            return 'ночей';
        },
        guestsLabel(n) {
            const m = n % 10, c = n % 100;
            if (m === 1 && c !== 11) return 'гость';
            if ([2,3,4].includes(m) && ![12,13,14].includes(c)) return 'гостя';
            return 'гостей';
        },
    };
}
</script>
<script>
function bookPhoneDropdown() {
    return {
        open: false, search: '', local: '',
        countries: [
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
        sel: {iso:'uz',n:'Узбекистан',c:'+998'},
        get filtered() {
            if (!this.search) return this.countries;
            const q = this.search.toLowerCase();
            return this.countries.filter(c => c.n.toLowerCase().includes(q) || c.c.includes(q));
        },
        pick(c) { this.sel = c; this.open = false; this.search = ''; },
    };
}
</script>
</body>
</html>
