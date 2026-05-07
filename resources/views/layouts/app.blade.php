<!DOCTYPE html>
<html lang="ru" class="">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'HMS') — Отель</title>

    {{-- ① Flash-prevention: run before ANY stylesheet so no FOUC --}}
    <script>
        if (localStorage.theme === 'dark' ||
            (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        }
    </script>

    {{-- ② Load CDN first --}}
    <script src="https://cdn.tailwindcss.com"></script>

    {{-- ③ Configure dark mode AFTER CDN (CDN reads tailwind.config on next tick) --}}
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: { sans: ['Inter', 'ui-sans-serif', 'system-ui', 'sans-serif'] },
                }
            }
        };
    </script>

    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        [x-cloak]   { display: none !important; }
        * { font-family: 'Inter', ui-sans-serif, system-ui, sans-serif; }
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 3px; }
        .dark ::-webkit-scrollbar-thumb { background: #475569; }
    </style>
    @stack('styles')
</head>
<body class="bg-slate-100 dark:bg-slate-950 min-h-screen antialiased" x-data="{ sidebarOpen: false }">

{{-- ══════════════ COMMAND PALETTE ══════════════ --}}
<div x-data="commandPalette()" x-cloak
     x-show="open"
     x-transition:enter="transition ease-out duration-150"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition ease-in duration-100"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     class="fixed inset-0 z-[60] flex items-start justify-center pt-[12vh]"
     @click.self="open = false"
     @keydown.escape.window="open = false">
    <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm"></div>
    <div class="relative w-full max-w-xl mx-4 bg-white dark:bg-slate-800 rounded-2xl shadow-2xl border border-slate-200 dark:border-slate-700 overflow-hidden">
        <div class="flex items-center gap-3 px-4 py-3.5 border-b border-slate-100 dark:border-slate-700">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5 text-slate-400 flex-shrink-0"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"/></svg>
            <input type="text" x-ref="searchInput" x-model="query"
                   @input.debounce.250ms="search()"
                   @keydown.arrow-down.prevent="focusNext()"
                   @keydown.arrow-up.prevent="focusPrev()"
                   @keydown.enter.prevent="goSelected()"
                   placeholder="Поиск гостей, бронирований, номеров…"
                   class="flex-1 bg-transparent text-sm text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none">
            <kbd class="hidden sm:inline-flex items-center px-1.5 py-0.5 text-xs text-slate-400 bg-slate-100 dark:bg-slate-700 rounded border border-slate-200 dark:border-slate-600">Esc</kbd>
        </div>
        <div class="max-h-80 overflow-y-auto py-2">
            <div x-show="loading" class="flex items-center gap-3 px-4 py-3 text-sm text-slate-400">
                <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                Поиск…
            </div>
            <template x-for="(item, idx) in results" :key="idx">
                <a :href="item.url" @mouseenter="selected = idx"
                   :class="selected === idx ? 'bg-blue-50 dark:bg-blue-900/30' : 'hover:bg-slate-50 dark:hover:bg-slate-700/50'"
                   class="flex items-center gap-3 px-4 py-2.5 transition-colors cursor-pointer">
                    <div class="w-7 h-7 rounded-lg flex items-center justify-center flex-shrink-0"
                         :class="{'bg-blue-100 dark:bg-blue-900/50':item.type==='booking','bg-emerald-100 dark:bg-emerald-900/50':item.type==='guest','bg-amber-100 dark:bg-amber-900/50':item.type==='room','bg-slate-100 dark:bg-slate-700':item.type==='nav'}">
                        <svg x-show="item.type==='booking'" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 text-blue-600 dark:text-blue-400"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5"/></svg>
                        <svg x-show="item.type==='guest'" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 text-emerald-600 dark:text-emerald-400"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z"/></svg>
                        <svg x-show="item.type==='room'" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 text-amber-600 dark:text-amber-400"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12"/></svg>
                        <svg x-show="item.type==='nav'" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 text-slate-500 dark:text-slate-400"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/></svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-slate-900 dark:text-white truncate" x-text="item.label"></p>
                        <p class="text-xs text-slate-400 truncate" x-text="item.sub"></p>
                    </div>
                </a>
            </template>
            <div x-show="!loading && results.length===0 && query.length>=2" class="px-4 py-6 text-center text-sm text-slate-400">
                Ничего не найдено по «<span x-text="query"></span>»
            </div>
            <div x-show="query.length===0 && !loading">
                <p class="px-4 py-1.5 text-xs font-semibold text-slate-400 uppercase tracking-wider">Быстрый переход</p>
                <template x-for="(item,idx) in quickNav" :key="'n'+idx">
                    <a :href="item.url" @mouseenter="selected=1000+idx"
                       :class="selected===1000+idx?'bg-blue-50 dark:bg-blue-900/30':'hover:bg-slate-50 dark:hover:bg-slate-700/50'"
                       class="flex items-center gap-3 px-4 py-2.5 transition-colors">
                        <div class="w-7 h-7 rounded-lg bg-slate-100 dark:bg-slate-700 flex items-center justify-center flex-shrink-0">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 text-slate-500 dark:text-slate-400"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/></svg>
                        </div>
                        <span class="text-sm text-slate-700 dark:text-slate-300" x-text="item.label"></span>
                    </a>
                </template>
            </div>
        </div>
        <div class="px-4 py-2 border-t border-slate-100 dark:border-slate-700 flex items-center gap-4 text-xs text-slate-400">
            <span><kbd class="px-1 py-0.5 bg-slate-100 dark:bg-slate-700 rounded text-[10px]">↑↓</kbd> навигация</span>
            <span><kbd class="px-1 py-0.5 bg-slate-100 dark:bg-slate-700 rounded text-[10px]">Enter</kbd> открыть</span>
            <span><kbd class="px-1 py-0.5 bg-slate-100 dark:bg-slate-700 rounded text-[10px]">Esc</kbd> закрыть</span>
        </div>
    </div>
</div>

{{-- ══════════════ MOBILE OVERLAY ══════════════ --}}
<div x-show="sidebarOpen" x-cloak
     @click="sidebarOpen = false"
     x-transition:enter="transition ease-out duration-200"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition ease-in duration-150"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     class="fixed inset-0 z-30 bg-black/50 backdrop-blur-sm lg:hidden"></div>

{{-- ══════════════ SIDEBAR ══════════════ --}}
<aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
       class="fixed z-40 w-64 h-screen bg-slate-900 flex flex-col transition-transform duration-300 ease-in-out overflow-y-auto">

    {{-- Logo + dark mode toggle --}}
    <div class="px-5 py-4 border-b border-slate-800 flex-shrink-0">
        <div class="flex items-center gap-3">
            <div class="w-9 h-9 bg-gradient-to-br from-blue-500 to-blue-700 rounded-xl flex items-center justify-center flex-shrink-0 shadow-lg shadow-blue-900/40">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-white">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21m-3.75 3.75h.008v.008h-.008v-.008Zm0 3h.008v.008h-.008v-.008Zm0 3h.008v.008h-.008v-.008Z"/>
                </svg>
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-white font-bold text-sm leading-tight">{{ config('hotel.name', 'HMS') }}</p>
                <p class="text-slate-500 text-xs truncate">Управление отелем</p>
            </div>
            <div class="flex items-center gap-1">
                {{-- Dark mode --}}
                <button onclick="const d=document.documentElement.classList.toggle('dark');localStorage.theme=d?'dark':'light';"
                        class="w-7 h-7 flex items-center justify-center rounded-lg text-slate-500 hover:text-white hover:bg-slate-800 transition-colors" title="Тема">
                    <svg class="w-4 h-4 dark:hidden" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21.752 15.002A9.72 9.72 0 0 1 18 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 0 0 3 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 0 0 9.002-5.998Z"/></svg>
                    <svg class="w-4 h-4 hidden dark:block" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25m6.364.386-1.591 1.591M21 12h-2.25m-.386 6.364-1.591-1.591M12 18.75V21m-4.773-4.227-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0Z"/></svg>
                </button>
                {{-- Mobile close --}}
                <button @click="sidebarOpen=false" class="lg:hidden w-7 h-7 flex items-center justify-center rounded-lg text-slate-500 hover:text-white hover:bg-slate-800 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg>
                </button>
            </div>
        </div>
    </div>

    {{-- Search trigger --}}
    <div class="px-3 pt-3 pb-1 flex-shrink-0">
        <button onclick="window.dispatchEvent(new CustomEvent('open-palette'))"
                class="w-full flex items-center gap-2 px-3 py-2 bg-slate-800 hover:bg-slate-700/80 text-slate-400 hover:text-slate-300 rounded-xl text-sm transition-colors border border-slate-700/50">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 flex-shrink-0"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"/></svg>
            <span class="flex-1 text-left text-xs">Поиск…</span>
            <kbd class="text-[9px] px-1 py-0.5 bg-slate-700 rounded border border-slate-600 text-slate-500">⌃K</kbd>
        </button>
    </div>

    {{-- Navigation --}}
    <nav class="flex-1 px-3 py-2 space-y-0.5 overflow-y-auto"
         x-data="{ bellCount: 0, serviceCount: 0 }"
         x-init="
            fetch('/notifications/count').then(r=>r.json()).then(d=>{ bellCount=d.count });
            setInterval(()=>fetch('/notifications/count').then(r=>r.json()).then(d=>{ bellCount=d.count }),30000);
            fetch('/service-requests/count').then(r=>r.json()).then(d=>{ serviceCount=d.count });
            setInterval(()=>fetch('/service-requests/count').then(r=>r.json()).then(d=>{ serviceCount=d.count }),30000);
         ">

        {{-- Main section --}}
        <a href="{{ route('dashboard') }}"
           class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-colors
                  {{ request()->routeIs('dashboard') ? 'bg-blue-600 text-white shadow-sm shadow-blue-900/30' : 'text-slate-400 hover:text-white hover:bg-slate-800' }}">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 flex-shrink-0"><path stroke-linecap="round" stroke-linejoin="round" d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25"/></svg>
            Главная
        </a>

        @php $role = auth()->user()->role; @endphp

        {{-- Bookings --}}
        @if($role->can('bookings'))
        @php $bookingsActive = request()->routeIs('bookings.*'); @endphp
        <div class="flex items-center rounded-xl transition-colors {{ $bookingsActive ? 'bg-blue-600 shadow-sm shadow-blue-900/30' : 'hover:bg-slate-800' }}">
            <a href="{{ route('bookings.index') }}"
               class="flex flex-1 items-center gap-3 px-3 py-2.5 text-sm font-medium {{ $bookingsActive ? 'text-white' : 'text-slate-400 hover:text-white' }}">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 flex-shrink-0"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5"/></svg>
                Бронирования
            </a>
            @if($role->can('guests'))
            <a href="{{ route('bookings.calendar') }}" title="Календарь"
               class="pr-3 py-2.5 {{ $bookingsActive ? 'text-blue-200 hover:text-white' : 'text-slate-600 hover:text-slate-300' }} transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z"/></svg>
            </a>
            @endif
        </div>
        @endif

        {{-- Group Bookings --}}
        @if($role->can('bookings'))
        <a href="{{ route('group-bookings.create') }}"
           class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-colors
                  {{ request()->routeIs('group-bookings.*') ? 'bg-blue-600 text-white shadow-sm shadow-blue-900/30' : 'text-slate-400 hover:text-white hover:bg-slate-800' }}">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 flex-shrink-0"><path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.058 2.772m0 0a3 3 0 0 0-4.681 2.72 8.986 8.986 0 0 0 3.74.477m.94-3.197a5.971 5.971 0 0 0-.94 3.197M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z"/></svg>
            Групповые бронирования
        </a>
        @endif

        {{-- Guests --}}
        @if($role->can('guests'))
        <a href="{{ route('guests.index') }}"
           class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-colors
                  {{ request()->routeIs('guests.*') ? 'bg-blue-600 text-white shadow-sm shadow-blue-900/30' : 'text-slate-400 hover:text-white hover:bg-slate-800' }}">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 flex-shrink-0"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z"/></svg>
            Гости
        </a>
        @endif

        {{-- Housekeeping --}}
        @if($role->can('housekeeping'))
        <a href="{{ route('housekeeping.index') }}"
           class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-colors
                  {{ request()->routeIs('housekeeping.*') ? 'bg-blue-600 text-white shadow-sm shadow-blue-900/30' : 'text-slate-400 hover:text-white hover:bg-slate-800' }}">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 flex-shrink-0"><path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 0 0-3.09 3.09Z"/></svg>
            Горничные
        </a>
        @endif

        {{-- Maintenance --}}
        @if($role->can('maintenance'))
        <a href="{{ route('maintenance.index') }}"
           class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-colors
                  {{ request()->routeIs('maintenance.*') ? 'bg-blue-600 text-white shadow-sm shadow-blue-900/30' : 'text-slate-400 hover:text-white hover:bg-slate-800' }}">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 flex-shrink-0"><path stroke-linecap="round" stroke-linejoin="round" d="M11.42 15.17 17.25 21A2.652 2.652 0 0 0 21 17.25l-5.877-5.877M11.42 15.17l2.496-3.03c.317-.384.74-.626 1.208-.766M11.42 15.17l-4.655 5.653a2.548 2.548 0 1 1-3.586-3.586l5.654-4.654m5.546-4.634.166-.175c.33-.33.795-.5 1.26-.5a1.783 1.783 0 0 1 1.26 3.04l-.166.175m-5.52 4.56-.174.166a1.783 1.783 0 0 1-3.04-1.26c0-.465.17-.93.5-1.26l.175-.166"/></svg>
            Техслужба
        </a>
        @endif

        {{-- Guest Service Requests --}}
        <a href="{{ route('service-requests.index') }}"
           class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-colors
                  {{ request()->routeIs('service-requests.*') ? 'bg-blue-600 text-white shadow-sm shadow-blue-900/30' : 'text-slate-400 hover:text-white hover:bg-slate-800' }}">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 flex-shrink-0"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 1 0-7.5 0v4.5m11.356-1.993 1.263 12c.076.721-.506 1.357-1.235 1.357H4.366c-.729 0-1.311-.636-1.235-1.357l1.263-12a1.125 1.125 0 0 1 1.118-1.007h12.976c.58 0 1.077.443 1.118 1.007Z"/></svg>
            <span class="flex-1">Услуги гостей</span>
            <template x-if="serviceCount > 0">
                <span x-text="serviceCount" class="inline-flex items-center justify-center min-w-[1.1rem] h-4 px-1 text-[9px] font-bold bg-amber-400 text-white rounded-full"></span>
            </template>
        </a>

        {{-- Lost & Found --}}
        <a href="{{ route('lost-items.index') }}"
           class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-colors
                  {{ request()->routeIs('lost-items.*') ? 'bg-blue-600 text-white shadow-sm shadow-blue-900/30' : 'text-slate-400 hover:text-white hover:bg-slate-800' }}">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 flex-shrink-0"><path stroke-linecap="round" stroke-linejoin="round" d="m20.25 7.5-.625 10.632a2.25 2.25 0 0 1-2.247 2.118H6.622a2.25 2.25 0 0 1-2.247-2.118L3.75 7.5m6 4.125 2.25 2.25m0 0 2.25 2.25M12 13.875l2.25-2.25M12 13.875l-2.25 2.25M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125Z"/></svg>
            Находки
        </a>

        {{-- Attendance --}}
        <a href="{{ route('attendance.index') }}"
           class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-colors
                  {{ request()->routeIs('attendance.*') ? 'bg-blue-600 text-white shadow-sm shadow-blue-900/30' : 'text-slate-400 hover:text-white hover:bg-slate-800' }}">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 flex-shrink-0"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
            Посещаемость
        </a>

        {{-- Management section (rooms, reports, expenses) --}}
        @if($role->can('rooms') || $role->can('reports') || $role->can('expenses') || $role->can('debt'))
        <div class="pt-4 pb-1.5">
            <p class="px-3 text-[10px] font-bold text-slate-600 uppercase tracking-widest">Управление</p>
        </div>
        @endif

        @if($role->can('room_types'))
        <a href="{{ route('room-types.index') }}"
           class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-colors
                  {{ request()->routeIs('room-types.*') ? 'bg-blue-600 text-white shadow-sm shadow-blue-900/30' : 'text-slate-400 hover:text-white hover:bg-slate-800' }}">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 flex-shrink-0"><path stroke-linecap="round" stroke-linejoin="round" d="M9.568 3H5.25A2.25 2.25 0 0 0 3 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 0 0 5.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 0 0 9.568 3Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M6 6h.008v.008H6V6Z"/></svg>
            Типы номеров
        </a>
        @endif

        @if($role->can('rooms'))
        <a href="{{ route('rooms.index') }}"
           class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-colors
                  {{ request()->routeIs('rooms.*') && !request()->routeIs('reviews.*') ? 'bg-blue-600 text-white shadow-sm shadow-blue-900/30' : 'text-slate-400 hover:text-white hover:bg-slate-800' }}">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 flex-shrink-0"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 5.25a3 3 0 0 1 3 3m3 0a6 6 0 0 1-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.621.504-1.125 1.125-1.125H5.25A2.25 2.25 0 0 0 3 12.75V8.25A2.25 2.25 0 0 1 5.25 6H10M15.75 5.25a3 3 0 0 1 3 3m3 0a6 6 0 0 1-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 0 1 21.75 8.25Z"/></svg>
            Номера
        </a>
        @endif

        <a href="{{ route('reviews.index') }}"
           class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-colors
                  {{ request()->routeIs('reviews.*') ? 'bg-blue-600 text-white shadow-sm shadow-blue-900/30' : 'text-slate-400 hover:text-white hover:bg-slate-800' }}">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 flex-shrink-0"><path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.562.562 0 0 1 1.04 0l2.125 5.111a.563.563 0 0 0 .475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 0 0-.182.557l1.285 5.385a.562.562 0 0 1-.84.61l-4.725-2.885a.562.562 0 0 0-.586 0L6.982 20.54a.562.562 0 0 1-.84-.61l1.285-5.386a.562.562 0 0 0-.182-.557l-4.204-3.602a.562.562 0 0 1 .321-.988l5.518-.442a.563.563 0 0 0 .475-.345L11.48 3.5Z"/></svg>
            Отзывы
        </a>

        @if($role->can('reports'))
        <a href="{{ route('reports.index') }}"
           class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-colors
                  {{ request()->routeIs('reports.*') ? 'bg-blue-600 text-white shadow-sm shadow-blue-900/30' : 'text-slate-400 hover:text-white hover:bg-slate-800' }}">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 flex-shrink-0"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z"/></svg>
            Отчёты
        </a>
        @endif

        @if($role->can('expenses'))
        <a href="{{ route('expenses.index') }}"
           class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-colors
                  {{ request()->routeIs('expenses.*') ? 'bg-blue-600 text-white shadow-sm shadow-blue-900/30' : 'text-slate-400 hover:text-white hover:bg-slate-800' }}">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 flex-shrink-0"><path stroke-linecap="round" stroke-linejoin="round" d="M9 14.25l6-6m4.5-3.493V21.75l-3.75-1.5-3.75 1.5-3.75-1.5-3.75 1.5V4.757c0-1.108.806-2.057 1.907-2.185a48.507 48.507 0 0 1 11.186 0c1.1.128 1.907 1.077 1.907 2.185Z"/></svg>
            Расходы
        </a>
        @endif

        @if($role->can('debt'))
        <a href="{{ route('finances.debt') }}"
           class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-colors
                  {{ request()->routeIs('finances.debt') ? 'bg-blue-600 text-white shadow-sm shadow-blue-900/30' : 'text-slate-400 hover:text-white hover:bg-slate-800' }}">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 flex-shrink-0"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z"/></svg>
            Долги
        </a>
        @endif

        <a href="{{ route('cashier.index') }}"
           class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-colors
                  {{ request()->routeIs('cashier.*') && !request()->routeIs('cashier.daily') ? 'bg-blue-600 text-white shadow-sm shadow-blue-900/30' : 'text-slate-400 hover:text-white hover:bg-slate-800' }}">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 flex-shrink-0"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm3 0h.008v.008H18V10.5Zm-12 0h.008v.008H6V10.5Z"/></svg>
            Касса
        </a>
        <a href="{{ route('cashier.daily') }}"
           class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-colors
                  {{ request()->routeIs('cashier.daily') ? 'bg-blue-600 text-white shadow-sm shadow-blue-900/30' : 'text-slate-400 hover:text-white hover:bg-slate-800' }}">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 flex-shrink-0"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 12h16.5m-16.5 3.75h16.5M3.75 19.5h16.5M5.625 4.5h12.75a1.875 1.875 0 0 1 0 3.75H5.625a1.875 1.875 0 0 1 0-3.75Z"/></svg>
            Дневной отчёт
        </a>

        {{-- Admin (owner only) --}}
        @if($role->can('users'))
        <a href="{{ route('users.index') }}"
           class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-colors
                  {{ request()->routeIs('users.*') ? 'bg-blue-600 text-white shadow-sm shadow-blue-900/30' : 'text-slate-400 hover:text-white hover:bg-slate-800' }}">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 flex-shrink-0"><path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.058 2.772m0 0a3 3 0 0 0-4.681 2.72 8.986 8.986 0 0 0 3.74.477m.94-3.197a5.971 5.971 0 0 0-.94 3.197M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z"/></svg>
            Сотрудники
        </a>
        @endif

        @if($role->can('pricing'))
        <a href="{{ route('pricing-rules.index') }}"
           class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-colors
                  {{ request()->routeIs('pricing-rules.*') ? 'bg-blue-600 text-white shadow-sm shadow-blue-900/30' : 'text-slate-400 hover:text-white hover:bg-slate-800' }}">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 flex-shrink-0"><path stroke-linecap="round" stroke-linejoin="round" d="M9 14.25l6-6m4.5-3.493V21.75l-3.75-1.5-3.75 1.5-3.75-1.5-3.75 1.5V4.757c0-1.108.806-2.057 1.907-2.185a48.507 48.507 0 0 1 11.186 0c1.1.128 1.907 1.077 1.907 2.185ZM9.75 9h.008v.008H9.75V9Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm4.125 4.5h.008v.008h-.008V13.5Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z"/></svg>
            Тарифы
        </a>
        @endif

        @if($role->can('users'))
        <a href="{{ route('promo-codes.index') }}"
           class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-colors
                  {{ request()->routeIs('promo-codes.*') ? 'bg-blue-600 text-white shadow-sm shadow-blue-900/30' : 'text-slate-400 hover:text-white hover:bg-slate-800' }}">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 flex-shrink-0"><path stroke-linecap="round" stroke-linejoin="round" d="M9 14.25l6-6m4.5-3.493V21.75l-3.75-1.5-3.75 1.5-3.75-1.5-3.75 1.5V4.757c0-1.108.806-2.057 1.907-2.185a48.507 48.507 0 0 1 11.186 0c1.1.128 1.907 1.077 1.907 2.185ZM9.75 9h.008v.008H9.75V9Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm4.125 4.5h.008v.008h-.008V13.5Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z"/></svg>
            Промокоды
        </a>
        @endif

        <!-- @if($role->can('audit'))
        <a href="{{ route('audit.show') }}"
           class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-colors
                  {{ request()->routeIs('audit.*') ? 'bg-blue-600 text-white shadow-sm shadow-blue-900/30' : 'text-slate-400 hover:text-white hover:bg-slate-800' }}">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 flex-shrink-0"><path stroke-linecap="round" stroke-linejoin="round" d="M21.752 15.002A9.72 9.72 0 0 1 18 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 0 0 3 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 0 0 9.002-5.998Z"/></svg>
            Ночной аудит
        </a>
        @endif -->

        {{-- Tools --}}
        <div class="pt-4 pb-1.5">
            <p class="px-3 text-[10px] font-bold text-slate-600 uppercase tracking-widest">Инструменты</p>
        </div>

        <a href="{{ route('notifications.index') }}"
           class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-colors
                  {{ request()->routeIs('notifications.*') ? 'bg-blue-600 text-white shadow-sm shadow-blue-900/30' : 'text-slate-400 hover:text-white hover:bg-slate-800' }}">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 flex-shrink-0"><path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0"/></svg>
            <span class="flex-1">Уведомления</span>
            <span x-show="bellCount > 0" x-text="bellCount > 99 ? '99+' : bellCount" x-cloak
                  class="inline-flex items-center justify-center min-w-5 h-5 px-1 rounded-full bg-red-500 text-white text-[10px] font-bold leading-none"></span>
        </a>

        @if($role->can('shift_notes'))
        <a href="{{ route('shift-notes.index') }}"
           class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-colors
                  {{ request()->routeIs('shift-notes.*') ? 'bg-blue-600 text-white shadow-sm shadow-blue-900/30' : 'text-slate-400 hover:text-white hover:bg-slate-800' }}">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 flex-shrink-0"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z"/></svg>
            Заметки смены
        </a>
        @endif

        @if($role->can('activity'))
        <a href="{{ route('activity.index') }}"
           class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-colors
                  {{ request()->routeIs('activity.*') ? 'bg-blue-600 text-white shadow-sm shadow-blue-900/30' : 'text-slate-400 hover:text-white hover:bg-slate-800' }}">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 flex-shrink-0"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25Z"/></svg>
            Журнал
        </a>
        @endif
    </nav>

    {{-- User footer --}}
    <div class="px-3 py-3 border-t border-slate-800 flex-shrink-0">
        <a href="{{ route('profile') }}"
           class="flex items-center gap-3 px-2 py-2 mb-1 rounded-xl bg-slate-800/50 hover:bg-slate-700/60 transition-colors group">
            @php $authUser = auth()->user(); @endphp
            @if($authUser->avatar)
                <img src="{{ Storage::url($authUser->avatar) }}" alt="{{ $authUser->name }}"
                     class="w-8 h-8 rounded-full object-cover flex-shrink-0 ring-1 ring-slate-700">
            @else
                @php $colors=['#3b82f6','#10b981','#f59e0b','#8b5cf6','#ef4444','#06b6d4','#ec4899']; $uc=$colors[crc32($authUser->name)%count($colors)]; @endphp
                <div class="w-8 h-8 rounded-full flex items-center justify-center flex-shrink-0 text-xs font-bold text-white" style="background:{{ $uc }}">
                    {{ mb_strtoupper(mb_substr($authUser->name,0,1)) }}
                </div>
            @endif
            <div class="min-w-0 flex-1">
                <p class="text-sm font-semibold text-white truncate group-hover:text-blue-300 transition-colors">{{ $authUser->name }}</p>
                <p class="text-[11px] text-slate-400">{{ $authUser->role->label() }}</p>
            </div>
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-3.5 h-3.5 text-slate-600 group-hover:text-slate-400 flex-shrink-0"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5"/></svg>
        </a>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="w-full flex items-center gap-3 px-3 py-2 text-xs text-slate-500 hover:text-white hover:bg-slate-800 rounded-xl transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 flex-shrink-0"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15M12 9l-3 3m0 0 3 3m-3-3h12.75"/></svg>
                Выйти
            </button>
        </form>
    </div>
</aside>

{{-- ══════════════ MAIN ══════════════ --}}
<main class="lg:ml-64 min-h-screen flex flex-col">

    {{-- Mobile topbar --}}
    <div class="lg:hidden sticky top-0 z-20 bg-white dark:bg-slate-900 border-b border-slate-200 dark:border-slate-800 flex items-center gap-3 px-4 h-14 flex-shrink-0">
        <button @click="sidebarOpen = true" class="w-9 h-9 flex items-center justify-center rounded-xl text-slate-500 hover:text-slate-700 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5"/></svg>
        </button>
        <p class="font-bold text-slate-900 dark:text-white text-sm flex-1">@yield('title', 'HMS')</p>
        <button onclick="window.dispatchEvent(new CustomEvent('open-palette'))"
                class="w-9 h-9 flex items-center justify-center rounded-xl text-slate-500 hover:text-slate-700 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"/></svg>
        </button>
    </div>

    {{-- Flash messages --}}
    @if(session('success'))
    <div class="mx-4 sm:mx-8 mt-4 flex items-center gap-3 p-4 bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-700/50 rounded-xl text-emerald-800 dark:text-emerald-300 text-sm">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 flex-shrink-0 text-emerald-500"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
        {{ session('success') }}
    </div>
    @endif
    @if(session('error'))
    <div class="mx-4 sm:mx-8 mt-4 flex items-center gap-3 p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-700/50 rounded-xl text-red-800 dark:text-red-300 text-sm">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 flex-shrink-0 text-red-500"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z"/></svg>
        {{ session('error') }}
    </div>
    @endif

    <div class="flex-1 px-4 sm:px-8 pb-8 pt-2">
        @yield('content')
    </div>
</main>

@stack('scripts')
<script>
function tablePager(total, perPage) {
    return {
        page: 1, perPage, total,
        get pages() { return Math.max(1, Math.ceil(this.total / this.perPage)); },
        get rangeStart() { return this.total === 0 ? 0 : (this.page - 1) * this.perPage + 1; },
        get rangeEnd() { return Math.min(this.page * this.perPage, this.total); },
        get pageRange() {
            const r = [], d = 2;
            for (let i = Math.max(1, this.page - d); i <= Math.min(this.pages, this.page + d); i++) r.push(i);
            return r;
        },
        show(i) { return i >= (this.page - 1) * this.perPage && i < this.page * this.perPage; },
        prev() { if (this.page > 1) this.page--; },
        next() { if (this.page < this.pages) this.page++; },
        goTo(p) { this.page = p; },
    };
}
function commandPalette() {
    return {
        open: false, query: '', results: [], loading: false, selected: 0,
        quickNav: [
            { label: 'Новое бронирование',  url: '{{ route('bookings.create') }}' },
            { label: 'Календарь',           url: '{{ route('bookings.calendar') }}' },
            { label: 'Гости',              url: '{{ route('guests.index') }}' },
            { label: 'Горничные',           url: '{{ route('housekeeping.index') }}' },
            { label: 'Техобслуживание',     url: '{{ route('maintenance.index') }}' },
            { label: 'Находки',            url: '{{ route('lost-items.index') }}' },
            { label: 'Касса',              url: '{{ route('cashier.index') }}' },
            { label: 'Отзывы',             url: '{{ route('reviews.index') }}' },
            @if(auth()->user()->role->value !== 'receptionist')
            { label: 'Отчёты',             url: '{{ route('reports.index') }}' },
            @endif
        ],
        init() {
            window.addEventListener('keydown', e => {
                if ((e.ctrlKey || e.metaKey) && e.key === 'k') { e.preventDefault(); this.open = !this.open; if (this.open) this.$nextTick(() => this.$refs.searchInput?.focus()); }
            });
            window.addEventListener('open-palette', () => { this.open = true; this.$nextTick(() => this.$refs.searchInput?.focus()); });
        },
        async search() {
            if (this.query.length < 2) { this.results = []; this.loading = false; return; }
            this.loading = true;
            try { const r = await fetch('/search?q=' + encodeURIComponent(this.query)); this.results = await r.json(); } catch { this.results = []; }
            this.loading = false; this.selected = 0;
        },
        focusNext() { this.selected = this.selected < this.results.length - 1 ? this.selected + 1 : 0; },
        focusPrev() { this.selected = this.selected > 0 ? this.selected - 1 : this.results.length - 1; },
        goSelected() { const i = this.results[this.selected]; if (i) window.location.href = i.url; },
    };
}
</script>
</body>
</html>
