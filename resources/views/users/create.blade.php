@extends('layouts.app')

@section('title', 'Добавить сотрудника')

@section('content')
@php
$roleLabels = ['owner' => 'Владелец', 'manager' => 'Менеджер', 'receptionist' => 'Администратор'];
@endphp

<div class="mb-6">
    <a href="{{ route('users.index') }}" class="text-sm text-gray-500 hover:text-gray-700">← Назад к сотрудникам</a>
    <h1 class="text-2xl font-bold text-gray-900 mt-2">Добавить сотрудника</h1>
</div>

<div class="bg-white rounded-xl border border-gray-200 p-6 max-w-lg">
    <form method="POST" action="{{ route('users.store') }}" class="space-y-5">
        @csrf

        {{-- Имя --}}
        <div>
            <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Имя</label>
            <input type="text" id="name" name="name" value="{{ old('name') }}" required maxlength="100"
                   class="w-full px-3 py-2 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 {{ $errors->has('name') ? 'border-red-400' : 'border-gray-300' }}">
            @error('name')
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- Email --}}
        <div>
            <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
            <input type="email" id="email" name="email" value="{{ old('email') }}" required maxlength="150"
                   class="w-full px-3 py-2 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 {{ $errors->has('email') ? 'border-red-400' : 'border-gray-300' }}">
            @error('email')
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- Роль --}}
        <div>
            <label for="role" class="block text-sm font-medium text-gray-700 mb-1">Роль</label>
            <select id="role" name="role" required
                    class="w-full px-3 py-2 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 {{ $errors->has('role') ? 'border-red-400' : 'border-gray-300' }}">
                <option value="">— Выберите роль —</option>
                @foreach($roles as $role)
                    <option value="{{ $role->value }}" {{ old('role') === $role->value ? 'selected' : '' }}>
                        {{ $roleLabels[$role->value] ?? ucfirst($role->value) }}
                    </option>
                @endforeach
            </select>
            @error('role')
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- Пароль --}}
        <div>
            <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Пароль</label>
            <input type="password" id="password" name="password" required minlength="8"
                   class="w-full px-3 py-2 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 {{ $errors->has('password') ? 'border-red-400' : 'border-gray-300' }}">
            @error('password')
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- Подтверждение пароля --}}
        <div>
            <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">Подтверждение пароля</label>
            <input type="password" id="password_confirmation" name="password_confirmation" required minlength="8"
                   class="w-full px-3 py-2 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 border-gray-300">
        </div>

        <div class="flex items-center gap-3 pt-2">
            <button type="submit"
                    class="px-5 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition">
                Добавить
            </button>
            <a href="{{ route('users.index') }}"
               class="px-5 py-2 text-sm font-medium text-gray-600 bg-gray-100 rounded-lg hover:bg-gray-200 transition">
                Отмена
            </a>
        </div>
    </form>
</div>
@endsection
