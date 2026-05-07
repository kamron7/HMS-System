@extends('layouts.app')
@section('title', 'Редактировать сотрудника')
@section('content')

<div class="max-w-2xl mx-auto">
    <div class="mb-6 flex items-center gap-3">
        <a href="{{ route('users.index') }}" class="inline-flex items-center gap-1.5 text-sm text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200 transition-colors">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18"/></svg>
            Сотрудники
        </a>
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 text-slate-300 dark:text-slate-600"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5"/></svg>
        <h1 class="text-xl font-bold text-slate-900 dark:text-white">{{ $user->name }}</h1>
    </div>

    <form method="POST" action="{{ route('users.update', $user) }}" enctype="multipart/form-data" class="space-y-5">
        @csrf @method('PUT')

        {{-- Basic info --}}
        <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm p-6">
            <h2 class="text-sm font-bold text-slate-700 dark:text-slate-200 uppercase tracking-wider mb-5">Основная информация</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

                {{-- Avatar --}}
                <div class="sm:col-span-2">
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Фото профиля</label>
                    <div x-data="{ preview: '{{ $user->avatar ? Storage::url($user->avatar) : '' }}' }" class="flex items-center gap-4">
                        <div class="w-16 h-16 rounded-xl bg-slate-100 dark:bg-slate-700 flex items-center justify-center overflow-hidden flex-shrink-0 border border-slate-200 dark:border-slate-600">
                            <img x-show="preview" :src="preview" class="w-full h-full object-cover" x-cloak>
                            <svg x-show="!preview" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-7 h-7 text-slate-400"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z"/></svg>
                        </div>
                        <div>
                            <label class="cursor-pointer inline-flex items-center gap-2 px-3 py-2 border border-slate-200 dark:border-slate-600 rounded-lg text-sm text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5m-13.5-9L12 3m0 0 4.5 4.5M12 3v13.5"/></svg>
                                Изменить фото
                                <input type="file" name="avatar" accept="image/*" class="sr-only"
                                       @change="preview = $event.target.files[0] ? URL.createObjectURL($event.target.files[0]) : preview">
                            </label>
                            <p class="text-xs text-slate-400 mt-1">JPG, PNG до 2 МБ</p>
                        </div>
                    </div>
                    @error('avatar')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="name" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Полное имя <span class="text-red-500">*</span></label>
                    <input type="text" id="name" name="name" value="{{ old('name', $user->name) }}" required maxlength="100"
                           class="w-full px-3 py-2.5 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100 {{ $errors->has('name') ? 'border-red-400' : 'border-slate-200 dark:border-slate-600' }}">
                    @error('name')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="position" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Должность</label>
                    <input type="text" id="position" name="position" value="{{ old('position', $user->position) }}" maxlength="100"
                           class="w-full px-3 py-2.5 border border-slate-200 dark:border-slate-600 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100">
                </div>

                <div>
                    <label for="email" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Email <span class="text-red-500">*</span></label>
                    <input type="email" id="email" name="email" value="{{ old('email', $user->email) }}" required maxlength="150"
                           class="w-full px-3 py-2.5 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100 {{ $errors->has('email') ? 'border-red-400' : 'border-slate-200 dark:border-slate-600' }}">
                    @error('email')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="phone" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Телефон</label>
                    <input type="text" id="phone" name="phone" value="{{ old('phone', $user->phone) }}" maxlength="30"
                           placeholder="+998 90 123 45 67"
                           class="w-full px-3 py-2.5 border border-slate-200 dark:border-slate-600 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100">
                </div>

                <div>
                    <label for="role" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Роль <span class="text-red-500">*</span></label>
                    <select id="role" name="role" required
                            class="w-full px-3 py-2.5 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100 {{ $errors->has('role') ? 'border-red-400' : 'border-slate-200 dark:border-slate-600' }}">
                        <option value="">— Выберите роль —</option>
                        @foreach($roles as $role)
                            <option value="{{ $role->value }}" {{ old('role', $user->role->value) === $role->value ? 'selected' : '' }}>{{ $role->label() }}</option>
                        @endforeach
                    </select>
                    @error('role')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="hire_date" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Дата найма</label>
                    <input type="date" id="hire_date" name="hire_date" value="{{ old('hire_date', $user->hire_date?->format('Y-m-d')) }}"
                           class="w-full px-3 py-2.5 border border-slate-200 dark:border-slate-600 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100">
                </div>
            </div>
        </div>

        {{-- Personal info --}}
        <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm p-6">
            <h2 class="text-sm font-bold text-slate-700 dark:text-slate-200 uppercase tracking-wider mb-5">Личные данные</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="birth_date" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Дата рождения</label>
                    <input type="date" id="birth_date" name="birth_date" value="{{ old('birth_date', $user->birth_date?->format('Y-m-d')) }}"
                           class="w-full px-3 py-2.5 border border-slate-200 dark:border-slate-600 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100">
                    @error('birth_date')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="passport_number" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Паспорт / Серия и номер</label>
                    <input type="text" id="passport_number" name="passport_number" value="{{ old('passport_number', $user->passport_number) }}" maxlength="50"
                           placeholder="AA 1234567"
                           class="w-full px-3 py-2.5 border border-slate-200 dark:border-slate-600 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100 font-mono">
                    @error('passport_number')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>
            </div>
        </div>

        {{-- Password + Notifications --}}
        <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm p-6">
            <h2 class="text-sm font-bold text-slate-700 dark:text-slate-200 uppercase tracking-wider mb-5">Доступ и уведомления</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="password" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Новый пароль</label>
                    <input type="password" id="password" name="password" minlength="8"
                           class="w-full px-3 py-2.5 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100 {{ $errors->has('password') ? 'border-red-400' : 'border-slate-200 dark:border-slate-600' }}">
                    <p class="mt-1 text-xs text-slate-400">Оставьте пустым, чтобы не менять</p>
                    @error('password')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="password_confirmation" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Подтверждение пароля</label>
                    <input type="password" id="password_confirmation" name="password_confirmation" minlength="8"
                           class="w-full px-3 py-2.5 border border-slate-200 dark:border-slate-600 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100">
                </div>
                @php
                    $tgTypes  = \App\Services\TelegramService::$types;
                    $tgPrefs  = old('telegram_notifications', $user->telegram_notifications); // null = all
                @endphp
                <div class="sm:col-span-2" x-data="tgPrefsModal(@js($tgPrefs), @js(array_keys($tgTypes)))">
                    <label for="telegram_chat_id" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Telegram Chat ID</label>
                    <div class="flex items-center gap-2">
                        <input type="text" id="telegram_chat_id" name="telegram_chat_id" value="{{ old('telegram_chat_id', $user->telegram_chat_id) }}" maxlength="50"
                               placeholder="123456789"
                               class="flex-1 px-3 py-2.5 border border-slate-200 dark:border-slate-600 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100 font-mono">
                        <button type="button" @click="open = true"
                                class="inline-flex items-center gap-1.5 px-3 py-2.5 text-sm font-semibold rounded-lg border transition-colors
                                       bg-sky-50 dark:bg-sky-900/30 border-sky-200 dark:border-sky-700 text-sky-700 dark:text-sky-300 hover:bg-sky-100 dark:hover:bg-sky-900/50 flex-shrink-0">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-4 h-4">
                                <path d="M11.944 0A12 12 0 0 0 0 12a12 12 0 0 0 12 12 12 12 0 0 0 12-12A12 12 0 0 0 12 0a12 12 0 0 0-.056 0zm4.962 7.224c.1-.002.321.023.465.14a.506.506 0 0 1 .171.325c.016.093.036.306.02.472-.18 1.898-.962 6.502-1.36 8.627-.168.9-.499 1.201-.82 1.23-.696.065-1.225-.46-1.9-.902-1.056-.693-1.653-1.124-2.678-1.8-1.185-.78-.417-1.21.258-1.91.177-.184 3.247-2.977 3.307-3.23.007-.032.014-.15-.056-.212s-.174-.041-.249-.024c-.106.024-1.793 1.14-5.061 3.345-.48.33-.913.49-1.302.48-.428-.008-1.252-.241-1.865-.44-.752-.245-1.349-.374-1.297-.789.027-.216.325-.437.893-.663 3.498-1.524 5.83-2.529 6.998-3.014 3.332-1.386 4.025-1.627 4.476-1.635z"/>
                            </svg>
                            Уведомления
                            <span x-show="prefs !== null"
                                  class="inline-flex items-center justify-center min-w-[1.1rem] h-4 px-1 text-[10px] font-bold rounded-full bg-sky-500 text-white"
                                  x-text="prefs ? prefs.length : 0"></span>
                        </button>
                    </div>
                    <p class="mt-1 text-xs text-slate-400">Для уведомлений в Telegram. Узнать через @userinfobot</p>
                    @error('telegram_chat_id')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror

                    {{-- Hidden inputs that get submitted with the main form --}}
                    <input type="hidden" name="tg_prefs_submitted" x-bind:value="prefs !== null ? '1' : ''">
                    <template x-if="prefs !== null">
                        <template x-for="key in (prefs || [])" :key="key">
                            <input type="hidden" name="telegram_notifications[]" :value="key">
                        </template>
                    </template>

                    {{-- Modal --}}
                    <div x-show="open" x-cloak
                         class="fixed inset-0 z-50 flex items-center justify-center p-4"
                         @keydown.escape.window="open = false">
                        {{-- Backdrop --}}
                        <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="open = false"></div>

                        {{-- Panel --}}
                        <div class="relative w-full max-w-md bg-white dark:bg-slate-800 rounded-2xl shadow-2xl border border-slate-200 dark:border-slate-700 overflow-hidden">
                            {{-- Header --}}
                            <div class="flex items-center justify-between px-5 py-4 border-b border-slate-100 dark:border-slate-700">
                                <div class="flex items-center gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5 text-sky-500">
                                        <path d="M11.944 0A12 12 0 0 0 0 12a12 12 0 0 0 12 12 12 12 0 0 0 12-12A12 12 0 0 0 12 0a12 12 0 0 0-.056 0zm4.962 7.224c.1-.002.321.023.465.14a.506.506 0 0 1 .171.325c.016.093.036.306.02.472-.18 1.898-.962 6.502-1.36 8.627-.168.9-.499 1.201-.82 1.23-.696.065-1.225-.46-1.9-.902-1.056-.693-1.653-1.124-2.678-1.8-1.185-.78-.417-1.21.258-1.91.177-.184 3.247-2.977 3.307-3.23.007-.032.014-.15-.056-.212s-.174-.041-.249-.024c-.106.024-1.793 1.14-5.061 3.345-.48.33-.913.49-1.302.48-.428-.008-1.252-.241-1.865-.44-.752-.245-1.349-.374-1.297-.789.027-.216.325-.437.893-.663 3.498-1.524 5.83-2.529 6.998-3.014 3.332-1.386 4.025-1.627 4.476-1.635z"/>
                                    </svg>
                                    <h3 class="text-base font-bold text-slate-900 dark:text-white">Telegram-уведомления</h3>
                                </div>
                                <button type="button" @click="open = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg>
                                </button>
                            </div>

                            {{-- Body --}}
                            <div class="px-5 py-4 space-y-1 max-h-[60vh] overflow-y-auto">
                                <p class="text-xs text-slate-400 dark:text-slate-500 mb-3">
                                    Выберите какие уведомления будет получать этот сотрудник.
                                    <span class="font-medium text-slate-500 dark:text-slate-400">Если ничего не выбрано — уведомления не отправляются.</span>
                                </p>

                                {{-- All-or-none toggle row --}}
                                <div class="flex items-center justify-between py-2 border-b border-slate-100 dark:border-slate-700 mb-2">
                                    <span class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wide">Все уведомления</span>
                                    <div class="flex items-center gap-2">
                                        <button type="button" @click="selectAll()"
                                                class="text-xs font-semibold text-blue-600 dark:text-blue-400 hover:underline">Включить все</button>
                                        <span class="text-slate-300 dark:text-slate-600">·</span>
                                        <button type="button" @click="clearAll()"
                                                class="text-xs font-semibold text-red-500 hover:underline">Выключить все</button>
                                    </div>
                                </div>

                                @foreach($tgTypes as $key => $label)
                                <label class="flex items-center gap-3 py-2 px-2 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-700/50 cursor-pointer group">
                                    <input type="checkbox"
                                           :checked="isChecked('{{ $key }}')"
                                           @change="toggle('{{ $key }}')"
                                           class="w-4 h-4 rounded border-slate-300 text-sky-500 focus:ring-sky-400 cursor-pointer flex-shrink-0">
                                    <p class="text-sm font-medium text-slate-800 dark:text-slate-200">{{ $label }}</p>
                                </label>
                                @endforeach
                            </div>

                            {{-- Footer --}}
                            <div class="flex items-center justify-between px-5 py-4 border-t border-slate-100 dark:border-slate-700 bg-slate-50 dark:bg-slate-900/30">
                                <p class="text-xs text-slate-400">
                                    <template x-if="prefs === null">
                                        <span class="text-amber-500 font-medium">Не настроено — получает все</span>
                                    </template>
                                    <template x-if="prefs !== null">
                                        <span>Выбрано: <span x-text="prefs.length" class="font-semibold text-slate-600 dark:text-slate-300"></span> из {{ count($tgTypes) }}</span>
                                    </template>
                                </p>
                                <button type="button" @click="open = false"
                                        class="px-4 py-2 bg-sky-600 hover:bg-sky-700 text-white text-sm font-semibold rounded-lg transition-colors">
                                    Готово
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="flex items-center gap-3">
            <button type="submit" class="inline-flex items-center gap-2 px-5 py-2.5 bg-blue-600 text-white text-sm font-semibold rounded-xl hover:bg-blue-700 transition-colors shadow-sm">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                Сохранить
            </button>
            <a href="{{ route('users.index') }}" class="px-5 py-2.5 text-sm font-semibold text-slate-600 dark:text-slate-300 bg-slate-100 dark:bg-slate-700 rounded-xl hover:bg-slate-200 dark:hover:bg-slate-600 transition-colors">Отмена</a>
        </div>
    </form>
</div>
<script>
function tgPrefsModal(initialPrefs, allKeys) {
    return {
        open: false,
        // null = never configured (receive all), array = explicit list
        prefs: initialPrefs,

        isChecked(key) {
            if (this.prefs === null) return true;
            return this.prefs.includes(key);
        },
        toggle(key) {
            if (this.prefs === null) {
                // First interaction — clone all keys minus this one
                this.prefs = allKeys.filter(k => k !== key);
            } else if (this.prefs.includes(key)) {
                this.prefs = this.prefs.filter(k => k !== key);
            } else {
                this.prefs = [...this.prefs, key];
            }
        },
        selectAll() {
            this.prefs = [...allKeys];
        },
        clearAll() {
            this.prefs = [];
        },
    };
}
</script>
@endsection
