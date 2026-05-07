@extends('layouts.app')
@section('title', 'Сотрудники')
@section('content')

<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-slate-900 dark:text-white tracking-tight">Сотрудники</h1>
        <p class="text-sm text-slate-400 dark:text-slate-500 mt-0.5">{{ $users->count() }} сотрудников в системе</p>
    </div>
    <div class="flex items-center gap-2">
        <a href="{{ route('users.create') }}"
           class="inline-flex items-center gap-2 px-4 py-2.5 bg-blue-600 text-white text-sm font-semibold rounded-xl hover:bg-blue-700 transition-colors shadow-sm shadow-blue-500/25">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
            Добавить сотрудника
        </a>
    </div>
</div>

@php
    $roleBgMap = [
        'owner'        => 'bg-purple-50 dark:bg-purple-900/30 text-purple-700 dark:text-purple-300',
        'manager'      => 'bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300',
        'receptionist' => 'bg-emerald-50 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-300',
        'housekeeper'  => 'bg-amber-50 dark:bg-amber-900/30 text-amber-700 dark:text-amber-300',
        'security'     => 'bg-red-50 dark:bg-red-900/30 text-red-700 dark:text-red-300',
        'accountant'   => 'bg-teal-50 dark:bg-teal-900/30 text-teal-700 dark:text-teal-300',
    ];
@endphp

<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
    @forelse($users as $user)
    <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden {{ $user->is_active ? '' : 'opacity-60' }}">
        {{-- Card header --}}
        <div class="h-16 bg-gradient-to-r from-slate-100 to-slate-50 dark:from-slate-700 dark:to-slate-800 relative">
            <div class="absolute -bottom-6 left-5">
                @if($user->avatar)
                    <img src="{{ Storage::url($user->avatar) }}" alt="{{ $user->name }}"
                         class="w-14 h-14 rounded-xl object-cover border-2 border-white dark:border-slate-800 shadow-sm">
                @else
                    @php
                        $colors = ['#3b82f6','#10b981','#f59e0b','#8b5cf6','#ef4444','#06b6d4','#ec4899'];
                        $color = $colors[crc32($user->name) % count($colors)];
                    @endphp
                    <div class="w-14 h-14 rounded-xl border-2 border-white dark:border-slate-800 shadow-sm flex items-center justify-center text-white font-black text-xl"
                         style="background: {{ $color }}">
                        {{ mb_strtoupper(mb_substr($user->name, 0, 1)) }}
                    </div>
                @endif
            </div>
        </div>

        <div class="pt-8 pb-4 px-5">
            <div class="flex items-start justify-between mb-1">
                <div>
                    <h3 class="font-bold text-slate-900 dark:text-white text-base leading-tight">{{ $user->name }}</h3>
                    @if($user->position)
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">{{ $user->position }}</p>
                    @endif
                </div>
                <span class="inline-flex items-center px-2 py-1 rounded-lg text-xs font-bold {{ $roleBgMap[$user->role->value] ?? '' }}">
                    {{ $user->role->label() }}
                </span>
            </div>

            <div class="mt-3 space-y-1.5">
                <div class="flex items-center gap-2 text-xs text-slate-500 dark:text-slate-400">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-3.5 h-3.5 flex-shrink-0"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75"/></svg>
                    <span class="truncate">{{ $user->email }}</span>
                </div>
                @if($user->phone)
                <div class="flex items-center gap-2 text-xs text-slate-500 dark:text-slate-400">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-3.5 h-3.5 flex-shrink-0"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 0 0 2.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 0 1-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 0 0-1.091-.852H4.5A2.25 2.25 0 0 0 2.25 4.5v2.25Z"/></svg>
                    {{ $user->phone }}
                </div>
                @endif
                @if($user->hire_date)
                <div class="flex items-center gap-2 text-xs text-slate-500 dark:text-slate-400">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-3.5 h-3.5 flex-shrink-0"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5"/></svg>
                    С {{ $user->hire_date->format('d.m.Y') }}
                </div>
                @endif
            </div>

            <div class="mt-4 pt-3 border-t border-slate-100 dark:border-slate-700/60 flex items-center justify-between">
                <div class="flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full {{ $user->is_active ? 'bg-emerald-500' : 'bg-slate-400' }}"></span>
                    <span class="text-xs {{ $user->is_active ? 'text-emerald-600 dark:text-emerald-400' : 'text-slate-400' }} font-medium">
                        {{ $user->is_active ? 'Активен' : 'Неактивен' }}
                    </span>
                </div>
                <div class="flex items-center gap-1.5">
                    <a href="{{ route('users.edit', $user) }}"
                       class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-semibold text-blue-700 dark:text-blue-300 bg-blue-50 dark:bg-blue-900/30 rounded-lg hover:bg-blue-100 dark:hover:bg-blue-900/50 transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-3 h-3"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Z"/></svg>
                        Изменить
                    </a>
                    @if(auth()->id() !== $user->id)
                    <form method="POST" action="{{ route('users.toggle-active', $user) }}">
                        @csrf @method('PATCH')
                        <button type="submit"
                                class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-semibold rounded-lg transition-colors {{ $user->is_active ? 'text-red-600 dark:text-red-400 bg-red-50 dark:bg-red-900/30 hover:bg-red-100' : 'text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-900/30 hover:bg-emerald-100' }}">
                            {{ $user->is_active ? 'Откл.' : 'Вкл.' }}
                        </button>
                    </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @empty
    <div class="col-span-3 py-16 text-center">
        <p class="text-slate-400 text-sm">Сотрудники не найдены</p>
    </div>
    @endforelse
</div>
@endsection
