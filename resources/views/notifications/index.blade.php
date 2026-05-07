@extends('layouts.app')

@section('title', 'Уведомления')

@section('content')

<div class="mb-6 flex items-center justify-between">
    <div>
        <h1 class="text-2xl font-bold text-slate-900 dark:text-slate-100">Уведомления</h1>
        <p class="text-slate-500 dark:text-slate-400 mt-1 text-sm">Системные оповещения для вашей роли</p>
    </div>
    @if($notifications->where('read_at', null)->isNotEmpty())
    <form method="POST" action="{{ route('notifications.markAllRead') }}">
        @csrf
        <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white text-sm font-semibold rounded-lg hover:bg-blue-700 transition-colors">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
            </svg>
            Прочитать все
        </button>
    </form>
    @endif
</div>

<div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden">
    @if($notifications->isEmpty())
        <div class="px-6 py-12 text-center">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-10 h-10 mx-auto text-slate-300 dark:text-slate-600 mb-3">
                <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0"/>
            </svg>
            <p class="text-sm text-slate-400 dark:text-slate-500">Нет уведомлений</p>
        </div>
    @else
        <div class="divide-y divide-slate-100 dark:divide-slate-700">
            @foreach($notifications as $notification)
            @php
                $typeIcon = match($notification->type) {
                    'checkin_unconfirmed' => ['icon' => 'M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5', 'bg' => 'bg-blue-100 dark:bg-blue-900/40', 'color' => 'text-blue-600 dark:text-blue-400'],
                    'payment_overdue'     => ['icon' => 'M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z', 'bg' => 'bg-red-100 dark:bg-red-900/40', 'color' => 'text-red-600 dark:text-red-400'],
                    'maintenance_new'     => ['icon' => 'M11.42 15.17 17.25 21A2.652 2.652 0 0 0 21 17.25l-5.877-5.877M11.42 15.17l2.496-3.03c.317-.384.74-.626 1.208-.766M11.42 15.17l-4.655 5.653a2.548 2.548 0 1 1-3.586-3.586l5.654-4.654m5.546-4.634.166-.175c.33-.33.795-.5 1.26-.5a1.783 1.783 0 0 1 1.26 3.04l-.166.175m-5.52 4.56-.174.166a1.783 1.783 0 0 1-3.04-1.26c0-.465.17-.93.5-1.26l.175-.166', 'bg' => 'bg-orange-100 dark:bg-orange-900/40', 'color' => 'text-orange-600 dark:text-orange-400'],
                    'maintenance_urgent'  => ['icon' => 'M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z', 'bg' => 'bg-red-100 dark:bg-red-900/40', 'color' => 'text-red-600 dark:text-red-400'],
                    'inquiry_new'         => ['icon' => 'M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0', 'bg' => 'bg-purple-100 dark:bg-purple-900/40', 'color' => 'text-purple-600 dark:text-purple-400'],
                    'pending_stale'       => ['icon' => 'M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z', 'bg' => 'bg-amber-100 dark:bg-amber-900/40', 'color' => 'text-amber-600 dark:text-amber-400'],
                    'booking_confirmed'   => ['icon' => 'M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z', 'bg' => 'bg-emerald-100 dark:bg-emerald-900/40', 'color' => 'text-emerald-600 dark:text-emerald-400'],
                    'checkout_done'       => ['icon' => 'M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15m3 0 3-3m0 0-3-3m3 3H9', 'bg' => 'bg-slate-100 dark:bg-slate-700', 'color' => 'text-slate-500 dark:text-slate-400'],
                    default               => ['icon' => 'M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0', 'bg' => 'bg-slate-100 dark:bg-slate-700', 'color' => 'text-slate-600 dark:text-slate-400'],
                };
            @endphp
            <div class="flex items-start gap-4 px-5 py-4 {{ $notification->isUnread() ? 'bg-blue-50/40 dark:bg-blue-900/10' : 'hover:bg-slate-50 dark:hover:bg-slate-700/50' }} transition-colors">
                <div class="w-9 h-9 {{ $typeIcon['bg'] }} rounded-full flex items-center justify-center flex-shrink-0 mt-0.5">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 {{ $typeIcon['color'] }}">
                        <path stroke-linecap="round" stroke-linejoin="round" d="{{ $typeIcon['icon'] }}"/>
                    </svg>
                </div>
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2">
                        <p class="text-sm font-semibold text-slate-900 dark:text-slate-100">{{ $notification->title }}</p>
                        @if($notification->isUnread())
                        <span class="inline-block w-2 h-2 rounded-full bg-blue-500 flex-shrink-0"></span>
                        @endif
                    </div>
                    <p class="text-sm text-slate-600 dark:text-slate-300 mt-0.5">{{ $notification->body }}</p>
                </div>
                <div class="flex-shrink-0 text-right">
                    <p class="text-xs text-slate-400 dark:text-slate-500">{{ $notification->created_at->diffForHumans() }}</p>
                    @if($notification->url)
                    <a href="{{ $notification->url }}" class="inline-block mt-1 text-xs font-medium text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300">
                        Перейти →
                    </a>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
    @endif
</div>

@endsection
