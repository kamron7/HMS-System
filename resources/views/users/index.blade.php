@extends('layouts.app')

@section('title', 'Сотрудники')

@section('content')
@php
$roleLabels = ['owner' => 'Владелец', 'manager' => 'Менеджер', 'receptionist' => 'Администратор'];
@endphp

<div class="flex items-center justify-between mb-6">
    <h1 class="text-2xl font-bold text-gray-900">
        Сотрудники
        <span class="ml-2 text-base font-normal text-gray-400">({{ $users->count() }})</span>
    </h1>
    <a href="{{ route('users.create') }}"
       class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition">
        + Добавить сотрудника
    </a>
</div>

<div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
    <table class="w-full text-sm">
        <thead>
            <tr class="bg-gray-50 border-b border-gray-200">
                <th class="text-left px-5 py-3 font-semibold text-gray-600">Имя</th>
                <th class="text-left px-5 py-3 font-semibold text-gray-600">Email</th>
                <th class="text-left px-5 py-3 font-semibold text-gray-600">Роль</th>
                <th class="text-left px-5 py-3 font-semibold text-gray-600">Статус</th>
                <th class="text-right px-5 py-3 font-semibold text-gray-600">Действия</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse($users as $user)
                <tr class="hover:bg-gray-50 {{ $user->is_active ? '' : 'opacity-50' }}">
                    <td class="px-5 py-4 font-medium text-gray-900">{{ $user->name }}</td>
                    <td class="px-5 py-4 text-gray-600">{{ $user->email }}</td>
                    <td class="px-5 py-4">
                        @php
                            $roleBg = match($user->role->value) {
                                'owner'        => 'bg-purple-100 text-purple-700',
                                'manager'      => 'bg-blue-100 text-blue-700',
                                'receptionist' => 'bg-gray-100 text-gray-700',
                                default        => 'bg-gray-100 text-gray-700',
                            };
                        @endphp
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $roleBg }}">
                            {{ $roleLabels[$user->role->value] ?? ucfirst($user->role->value) }}
                        </span>
                    </td>
                    <td class="px-5 py-4">
                        @if($user->is_active)
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700">
                                Активен
                            </span>
                        @else
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-700">
                                Неактивен
                            </span>
                        @endif
                    </td>
                    <td class="px-5 py-4 text-right">
                        <div class="flex items-center justify-end gap-2">
                            <a href="{{ route('users.edit', $user) }}"
                               class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-blue-700 bg-blue-50 rounded-lg hover:bg-blue-100 transition">
                                Изменить
                            </a>

                            @if(auth()->id() !== $user->id)
                                <form method="POST" action="{{ route('users.toggle-active', $user) }}">
                                    @csrf
                                    @method('PATCH')
                                    @if($user->is_active)
                                        <button type="submit"
                                                class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-red-700 bg-red-50 rounded-lg hover:bg-red-100 transition">
                                            Деактивировать
                                        </button>
                                    @else
                                        <button type="submit"
                                                class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-green-700 bg-green-50 rounded-lg hover:bg-green-100 transition">
                                            Активировать
                                        </button>
                                    @endif
                                </form>
                            @endif
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="px-5 py-10 text-center text-gray-400">
                        Сотрудники не найдены
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
