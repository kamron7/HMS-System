@extends('layouts.app')

@section('title', 'Тарифные правила')

@section('content')

<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-xl font-bold text-slate-900">Тарифные правила</h1>
        <p class="text-sm text-slate-500 mt-0.5">Управление динамическим ценообразованием</p>
    </div>
    <a href="{{ route('pricing-rules.create') }}"
       class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white text-sm font-semibold rounded-lg hover:bg-blue-700 transition-colors">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
        Добавить правило
    </a>
</div>

<div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
    @if($rules->isEmpty())
        <div class="px-6 py-16 text-center">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-10 h-10 mx-auto text-slate-300 mb-3"><path stroke-linecap="round" stroke-linejoin="round" d="M9 14.25l6-6m4.5-3.493V21.75l-3.75-1.5-3.75 1.5-3.75-1.5-3.75 1.5V4.757c0-1.108.806-2.057 1.907-2.185a48.507 48.507 0 0 1 11.186 0c1.1.128 1.907 1.077 1.907 2.185ZM9.75 9h.008v.008H9.75V9Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm4.125 4.5h.008v.008h-.008V13.5Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z"/></svg>
            <p class="text-sm text-slate-400">Нет тарифных правил</p>
            <a href="{{ route('pricing-rules.create') }}" class="mt-3 inline-flex items-center gap-1.5 text-sm font-medium text-blue-600 hover:text-blue-700">
                Создать первое правило →
            </a>
        </div>
    @else
    <table class="w-full text-sm">
        <thead>
            <tr class="bg-slate-50 border-b border-slate-200">
                <th class="text-left px-5 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Название</th>
                <th class="text-left px-5 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Тип номера</th>
                <th class="text-left px-5 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Период</th>
                <th class="text-left px-5 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Модификатор</th>
                <th class="text-center px-5 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Приоритет</th>
                <th class="text-center px-5 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Статус</th>
                <th class="px-5 py-3"></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            @foreach($rules as $rule)
            <tr class="hover:bg-slate-50 transition-colors {{ ! $rule->is_active ? 'opacity-60' : '' }}">
                <td class="px-5 py-3.5 font-semibold text-slate-900">{{ $rule->name }}</td>
                <td class="px-5 py-3.5 text-slate-600">
                    @if($rule->roomType)
                        <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-semibold bg-blue-50 text-blue-700 ring-1 ring-blue-200">{{ $rule->roomType->name }}</span>
                    @else
                        <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-semibold bg-slate-100 text-slate-600">Все типы</span>
                    @endif
                </td>
                <td class="px-5 py-3.5 font-mono text-xs text-slate-600">
                    {{ $rule->date_from->format('d.m.Y') }} — {{ $rule->date_to->format('d.m.Y') }}
                </td>
                <td class="px-5 py-3.5">
                    @if($rule->modifier_type === 'percent')
                        @php $val = (float) $rule->modifier_value; @endphp
                        <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-bold {{ $val >= 0 ? 'bg-emerald-50 text-emerald-700' : 'bg-red-50 text-red-700' }}">
                            {{ $val >= 0 ? '+' : '' }}{{ number_format($val, 0) }}%
                        </span>
                    @else
                        <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-bold bg-purple-50 text-purple-700">
                            {{ number_format((float) $rule->modifier_value, 0, '.', ' ') }} сум/ночь
                        </span>
                    @endif
                </td>
                <td class="px-5 py-3.5 text-center">
                    <span class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-slate-100 text-slate-700 text-xs font-bold">{{ $rule->priority }}</span>
                </td>
                <td class="px-5 py-3.5 text-center">
                    @if($rule->is_active)
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200">Активно</span>
                    @else
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-slate-100 text-slate-500">Выключено</span>
                    @endif
                </td>
                <td class="px-5 py-3.5 text-right">
                    <div class="flex items-center justify-end gap-2">
                        <a href="{{ route('pricing-rules.edit', $rule) }}"
                           class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold bg-slate-100 text-slate-700 rounded-lg hover:bg-slate-200 transition-colors">
                            Изменить
                        </a>
                        <form method="POST" action="{{ route('pricing-rules.destroy', $rule) }}">
                            @csrf @method('DELETE')
                            <button type="submit" onclick="return confirm('Удалить тарифное правило?')"
                                    class="inline-flex items-center px-3 py-1.5 text-xs font-semibold bg-red-50 text-red-600 rounded-lg hover:bg-red-100 transition-colors">
                                Удалить
                            </button>
                        </form>
                    </div>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif
</div>

@endsection
