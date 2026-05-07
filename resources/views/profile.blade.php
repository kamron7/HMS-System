@extends('layouts.app')
@section('title', 'Мой профиль')
@section('content')

<div class="max-w-2xl mx-auto">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Мой профиль</h1>
        <p class="text-sm text-slate-400 dark:text-slate-500 mt-0.5">Обновите свои контактные данные и пароль</p>
    </div>

    @if(session('success'))
    <div class="mb-5 flex items-center gap-3 bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800 rounded-xl px-4 py-3">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-emerald-600 dark:text-emerald-400 flex-shrink-0"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
        <p class="text-sm font-medium text-emerald-800 dark:text-emerald-200">{{ session('success') }}</p>
    </div>
    @endif

    <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="space-y-5">
        @csrf @method('PUT')

        {{-- Identity card (read-only) --}}
        <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm p-6">
            <h2 class="text-sm font-bold text-slate-700 dark:text-slate-200 uppercase tracking-wider mb-5">Аккаунт</h2>
            <div class="flex items-center gap-5 mb-5">
                {{-- Avatar --}}
                <div x-data="{ preview: '{{ $user->avatar ? Storage::url($user->avatar) : '' }}' }" class="flex items-center gap-4">
                    <div class="relative">
                        <div class="w-20 h-20 rounded-2xl overflow-hidden flex-shrink-0 border-2 border-slate-200 dark:border-slate-600 shadow-sm">
                            <img x-show="preview" :src="preview" class="w-full h-full object-cover" x-cloak>
                            @if(!$user->avatar)
                            @php $colors=['#3b82f6','#10b981','#f59e0b','#8b5cf6','#ef4444','#06b6d4','#ec4899']; $c=$colors[crc32($user->name)%count($colors)]; @endphp
                            <div x-show="!preview" class="w-full h-full flex items-center justify-center text-white font-black text-2xl" style="background:{{ $c }}">
                                {{ mb_strtoupper(mb_substr($user->name,0,1)) }}
                            </div>
                            @else
                            <div x-show="!preview" class="w-full h-full bg-slate-100 dark:bg-slate-700 flex items-center justify-center">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-8 h-8 text-slate-400"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z"/></svg>
                            </div>
                            @endif
                        </div>
                        <label class="absolute -bottom-1.5 -right-1.5 cursor-pointer w-7 h-7 bg-blue-600 hover:bg-blue-700 text-white rounded-full flex items-center justify-center shadow-md transition-colors">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-3.5 h-3.5"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Z"/></svg>
                            <input type="file" name="avatar" accept="image/*" class="sr-only"
                                   @change="preview = $event.target.files[0] ? URL.createObjectURL($event.target.files[0]) : preview">
                        </label>
                    </div>
                    <div>
                        <p class="font-bold text-slate-900 dark:text-white text-lg leading-tight">{{ $user->name }}</p>
                        <p class="text-sm text-slate-500 dark:text-slate-400">{{ $user->role->label() }}@if($user->position) · {{ $user->position }}@endif</p>
                        <p class="text-xs text-slate-400 dark:text-slate-500 mt-1">{{ $user->email }}</p>
                    </div>
                </div>
            </div>
            @error('avatar')<p class="text-xs text-red-600 -mt-2 mb-3">{{ $message }}</p>@enderror

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Полное имя <span class="text-red-500">*</span></label>
                    <input type="text" name="name" value="{{ old('name', $user->name) }}" required maxlength="100"
                           class="w-full px-3 py-2.5 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100 {{ $errors->has('name') ? 'border-red-400' : 'border-slate-200 dark:border-slate-600' }}">
                    @error('name')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Телефон</label>
                    <input type="text" name="phone" value="{{ old('phone', $user->phone) }}" maxlength="30"
                           placeholder="+998 90 123 45 67"
                           class="w-full px-3 py-2.5 border border-slate-200 dark:border-slate-600 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100">
                    @error('phone')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>

                <div class="sm:col-span-2">
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Telegram Chat ID</label>
                    <input type="text" name="telegram_chat_id" value="{{ old('telegram_chat_id', $user->telegram_chat_id) }}" maxlength="50"
                           placeholder="123456789"
                           class="w-full px-3 py-2.5 border border-slate-200 dark:border-slate-600 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100 font-mono">
                    <p class="mt-1 text-xs text-slate-400">Для уведомлений в Telegram. Узнать через @userinfobot</p>
                    @error('telegram_chat_id')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>
            </div>
        </div>

        {{-- Password --}}
        <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm p-6">
            <h2 class="text-sm font-bold text-slate-700 dark:text-slate-200 uppercase tracking-wider mb-5">Изменить пароль</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Новый пароль</label>
                    <input type="password" name="password" minlength="8"
                           class="w-full px-3 py-2.5 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100 {{ $errors->has('password') ? 'border-red-400' : 'border-slate-200 dark:border-slate-600' }}">
                    <p class="mt-1 text-xs text-slate-400">Оставьте пустым, чтобы не менять</p>
                    @error('password')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Подтверждение</label>
                    <input type="password" name="password_confirmation" minlength="8"
                           class="w-full px-3 py-2.5 border border-slate-200 dark:border-slate-600 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100">
                </div>
            </div>
        </div>

        {{-- Read-only info --}}
        <div class="bg-slate-50 dark:bg-slate-800/50 rounded-2xl border border-slate-200 dark:border-slate-700 p-6">
            <h2 class="text-sm font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-4">Информация о работе</h2>
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-sm">
                @if($user->position)
                <div><p class="text-xs text-slate-400 mb-0.5">Должность</p><p class="font-semibold text-slate-700 dark:text-slate-200">{{ $user->position }}</p></div>
                @endif
                @if($user->hire_date)
                <div><p class="text-xs text-slate-400 mb-0.5">С нами с</p><p class="font-semibold text-slate-700 dark:text-slate-200">{{ $user->hire_date->format('d.m.Y') }}</p></div>
                @endif
                @if($user->birth_date)
                <div><p class="text-xs text-slate-400 mb-0.5">Дата рождения</p><p class="font-semibold text-slate-700 dark:text-slate-200">{{ $user->birth_date->format('d.m.Y') }}</p></div>
                @endif
                <div><p class="text-xs text-slate-400 mb-0.5">Роль</p><p class="font-semibold text-slate-700 dark:text-slate-200">{{ $user->role->label() }}</p></div>
            </div>
        </div>

        <div class="flex items-center gap-3">
            <button type="submit" class="inline-flex items-center gap-2 px-5 py-2.5 bg-blue-600 text-white text-sm font-semibold rounded-xl hover:bg-blue-700 transition-colors shadow-sm">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                Сохранить
            </button>
        </div>
    </form>
</div>
@endsection
