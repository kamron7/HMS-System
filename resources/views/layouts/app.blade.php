<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'HMS') — Отель</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="bg-gray-50 min-h-screen flex">

    {{-- Sidebar --}}
    <aside class="w-60 bg-white border-r border-gray-200 flex flex-col min-h-screen fixed">
        <div class="px-6 py-5 border-b border-gray-100">
            <span class="text-lg font-bold text-gray-900">🏨 HMS</span>
        </div>
        <nav class="flex-1 px-4 py-4 space-y-1">
            <a href="{{ route('dashboard') }}"
               class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium {{ request()->routeIs('dashboard') ? 'bg-blue-50 text-blue-700' : 'text-gray-600 hover:bg-gray-50' }}">
                📊 Главная
            </a>
            <a href="{{ route('bookings.index') }}"
               class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium {{ request()->routeIs('bookings.*') ? 'bg-blue-50 text-blue-700' : 'text-gray-600 hover:bg-gray-50' }}">
                📅 Бронирования
            </a>
            <a href="{{ route('guests.index') }}"
               class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium {{ request()->routeIs('guests.*') ? 'bg-blue-50 text-blue-700' : 'text-gray-600 hover:bg-gray-50' }}">
                👤 Гости
            </a>
            <a href="{{ route('housekeeping.index') }}"
               class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium {{ request()->routeIs('housekeeping.*') ? 'bg-blue-50 text-blue-700' : 'text-gray-600 hover:bg-gray-50' }}">
                🧹 Горничные
            </a>

            @if(auth()->user()->role->value !== 'receptionist')
            <div class="pt-2 pb-1">
                <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider px-3">Управление</span>
            </div>
            <a href="{{ route('room-types.index') }}"
               class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium {{ request()->routeIs('room-types.*') ? 'bg-blue-50 text-blue-700' : 'text-gray-600 hover:bg-gray-50' }}">
                🏷️ Типы номеров
            </a>
            <a href="{{ route('rooms.index') }}"
               class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium {{ request()->routeIs('rooms.*') ? 'bg-blue-50 text-blue-700' : 'text-gray-600 hover:bg-gray-50' }}">
                🚪 Номера
            </a>
            <a href="{{ route('finances.index') }}"
               class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium {{ request()->routeIs('finances.*') ? 'bg-blue-50 text-blue-700' : 'text-gray-600 hover:bg-gray-50' }}">
                💰 Финансы
            </a>
            @endif

            @if(auth()->user()->role->value === 'owner')
            <a href="{{ route('users.index') }}"
               class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium {{ request()->routeIs('users.*') ? 'bg-blue-50 text-blue-700' : 'text-gray-600 hover:bg-gray-50' }}">
                👥 Сотрудники
            </a>
            @endif
        </nav>
        <div class="px-4 py-4 border-t border-gray-100">
            <div class="px-3 py-2">
                <p class="text-sm font-medium text-gray-900">{{ auth()->user()->name }}</p>
                <p class="text-xs text-gray-500">{{ auth()->user()->role->value }}</p>
            </div>
            <form method="POST" action="{{ route('logout') }}" class="mt-1">
                @csrf
                <button type="submit" class="w-full text-left px-3 py-2 text-sm text-gray-500 hover:text-red-600 hover:bg-red-50 rounded-lg transition">
                    Выйти
                </button>
            </form>
        </div>
    </aside>

    {{-- Main content --}}
    <main class="flex-1 ml-60">
        <div class="max-w-7xl mx-auto px-6 py-8">
            @if(session('success'))
                <div class="mb-4 p-3 bg-green-50 border border-green-200 rounded-lg text-green-800 text-sm">
                    {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="mb-4 p-3 bg-red-50 border border-red-200 rounded-lg text-red-800 text-sm">
                    {{ session('error') }}
                </div>
            @endif
            @yield('content')
        </div>
    </main>

</body>
</html>
