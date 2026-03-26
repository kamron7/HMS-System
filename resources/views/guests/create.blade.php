@extends('layouts.app')

@section('title', 'Добавить гостя')

@section('content')
<div class="flex items-center justify-between mb-6">
    <h1 class="text-2xl font-bold text-gray-900">Добавить гостя</h1>
    <a href="{{ route('guests.index') }}"
       class="text-sm text-gray-500 hover:text-gray-700">
        ← Назад к списку
    </a>
</div>

<div class="max-w-2xl bg-white rounded-xl border border-gray-200 p-6">
    <form method="POST" action="{{ route('guests.store') }}">
        @csrf

        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
            {{-- Имя --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Имя <span class="text-red-500">*</span>
                </label>
                <input type="text"
                       name="first_name"
                       value="{{ old('first_name') }}"
                       class="w-full px-3 py-2 border {{ $errors->has('first_name') ? 'border-red-400' : 'border-gray-300' }} rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                @error('first_name')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Фамилия --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Фамилия <span class="text-red-500">*</span>
                </label>
                <input type="text"
                       name="last_name"
                       value="{{ old('last_name') }}"
                       class="w-full px-3 py-2 border {{ $errors->has('last_name') ? 'border-red-400' : 'border-gray-300' }} rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                @error('last_name')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Телефон --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Телефон</label>
                <input type="text"
                       name="phone"
                       value="{{ old('phone') }}"
                       class="w-full px-3 py-2 border {{ $errors->has('phone') ? 'border-red-400' : 'border-gray-300' }} rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                @error('phone')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Email --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                <input type="email"
                       name="email"
                       value="{{ old('email') }}"
                       class="w-full px-3 py-2 border {{ $errors->has('email') ? 'border-red-400' : 'border-gray-300' }} rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                @error('email')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Номер паспорта --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Номер паспорта</label>
                <input type="text"
                       name="passport_number"
                       value="{{ old('passport_number') }}"
                       class="w-full px-3 py-2 border {{ $errors->has('passport_number') ? 'border-red-400' : 'border-gray-300' }} rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                @error('passport_number')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Гражданство --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Гражданство</label>
                <input type="text"
                       name="nationality"
                       value="{{ old('nationality') }}"
                       class="w-full px-3 py-2 border {{ $errors->has('nationality') ? 'border-red-400' : 'border-gray-300' }} rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                @error('nationality')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div class="mt-6 flex items-center gap-3">
            <button type="submit"
                    class="px-5 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition">
                Сохранить
            </button>
            <a href="{{ route('guests.index') }}"
               class="px-5 py-2 text-sm text-gray-600 hover:text-gray-800">
                Отмена
            </a>
        </div>
    </form>
</div>
@endsection
