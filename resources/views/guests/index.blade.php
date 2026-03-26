@extends('layouts.app')

@section('title', 'Гости')

@section('content')
<div class="flex items-center justify-between mb-6">
    <h1 class="text-2xl font-bold text-gray-900">
        Гости
        <span class="ml-2 text-base font-normal text-gray-400">({{ $guests->total() }})</span>
    </h1>
    <a href="{{ route('guests.create') }}"
       class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition">
        + Добавить гостя
    </a>
</div>

{{-- Search bar --}}
<form method="GET" action="{{ route('guests.index') }}" class="mb-6">
    <div class="flex gap-3">
        <input
            type="text"
            name="q"
            value="{{ request('q') }}"
            placeholder="Поиск по имени или телефону"
            class="flex-1 px-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
        >
        <button type="submit"
                class="px-4 py-2 bg-gray-100 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-200 transition">
            Найти
        </button>
        @if(request('q'))
            <a href="{{ route('guests.index') }}"
               class="px-4 py-2 text-sm text-gray-500 hover:text-gray-700 rounded-lg hover:bg-gray-100 transition">
                Сбросить
            </a>
        @endif
    </div>
</form>

<div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
    <table class="w-full text-sm">
        <thead>
            <tr class="bg-gray-50 border-b border-gray-200">
                <th class="text-left px-5 py-3 font-semibold text-gray-600">ФИО</th>
                <th class="text-left px-5 py-3 font-semibold text-gray-600">Телефон</th>
                <th class="text-left px-5 py-3 font-semibold text-gray-600">Email</th>
                <th class="text-left px-5 py-3 font-semibold text-gray-600">Паспорт</th>
                <th class="text-right px-5 py-3 font-semibold text-gray-600">Действия</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse($guests as $guest)
                <tr class="hover:bg-gray-50">
                    <td class="px-5 py-4">
                        <a href="{{ route('guests.show', $guest) }}"
                           class="font-medium text-blue-700 hover:underline">
                            {{ $guest->full_name }}
                        </a>
                    </td>
                    <td class="px-5 py-4 text-gray-600">{{ $guest->phone ?? '—' }}</td>
                    <td class="px-5 py-4 text-gray-600">{{ $guest->email ?? '—' }}</td>
                    <td class="px-5 py-4 text-gray-600">{{ $guest->passport_number ?? '—' }}</td>
                    <td class="px-5 py-4 text-right">
                        <a href="{{ route('guests.edit', $guest) }}"
                           class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-blue-700 bg-blue-50 rounded-lg hover:bg-blue-100 transition">
                            Изменить
                        </a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="px-5 py-10 text-center text-gray-400">
                        @if(request('q'))
                            Гости не найдены по запросу «{{ request('q') }}»
                        @else
                            Гости не добавлены
                        @endif
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if($guests->hasPages())
    <div class="mt-4">
        {{ $guests->appends(request()->query())->links() }}
    </div>
@endif
@endsection
