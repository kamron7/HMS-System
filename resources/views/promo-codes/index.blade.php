@extends('layouts.app')

@section('title', 'Промокоды')

@section('content')
<div class="flex items-center justify-between mb-6">
    <h1 class="text-2xl font-bold text-slate-900 dark:text-slate-100">Промокоды</h1>
    <a href="{{ route('promo-codes.create') }}"
       class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white text-sm font-semibold rounded-lg hover:bg-blue-700 transition-colors shadow-sm">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
        Создать промокод
    </a>
</div>

<div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden">
    <table class="w-full text-sm">
        <thead>
            <tr class="bg-slate-50 dark:bg-slate-900/50 border-b border-slate-200 dark:border-slate-700">
                <th class="text-left px-5 py-3 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Код</th>
                <th class="text-left px-5 py-3 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Скидка</th>
                <th class="text-left px-5 py-3 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Период</th>
                <th class="text-left px-5 py-3 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Использований</th>
                <th class="text-left px-5 py-3 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Статус</th>
                <th class="text-right px-5 py-3 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Действия</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
            @forelse($codes as $code)
                <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors">
                    <td class="px-5 py-3.5">
                        <span class="font-mono font-bold text-slate-900 dark:text-slate-100 tracking-widest">{{ $code->code }}</span>
                    </td>
                    <td class="px-5 py-3.5 font-semibold text-emerald-700 dark:text-emerald-400">
                        {{ $code->discount_percent }}%
                    </td>
                    <td class="px-5 py-3.5 text-slate-500 dark:text-slate-400 text-xs font-mono">
                        @if($code->valid_from || $code->valid_to)
                            {{ $code->valid_from?->format('d.m.Y') ?? '∞' }}
                            —
                            {{ $code->valid_to?->format('d.m.Y') ?? '∞' }}
                        @else
                            Без ограничений
                        @endif
                    </td>
                    <td class="px-5 py-3.5 text-slate-600 dark:text-slate-300">
                        {{ $code->uses_count }}
                        @if($code->max_uses) / {{ $code->max_uses }} @endif
                    </td>
                    <td class="px-5 py-3.5">
                        @if($code->isValid())
                            <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-semibold bg-emerald-50 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-300 ring-1 ring-emerald-200 dark:ring-emerald-700">Активен</span>
                        @else
                            <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-semibold bg-slate-100 dark:bg-slate-700 text-slate-500 dark:text-slate-400 ring-1 ring-slate-200 dark:ring-slate-600">Неактивен</span>
                        @endif
                    </td>
                    <td class="px-5 py-3.5 text-right">
                        <div class="flex items-center justify-end gap-2">
                            <a href="{{ route('promo-codes.edit', $code) }}"
                               class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-blue-700 dark:text-blue-300 bg-blue-50 dark:bg-blue-900/30 rounded-lg hover:bg-blue-100 dark:hover:bg-blue-900/50 transition-colors">
                                Изменить
                            </a>
                            <form method="POST" action="{{ route('promo-codes.destroy', $code) }}"
                                  onsubmit="return confirm('Удалить промокод {{ $code->code }}?')">
                                @csrf @method('DELETE')
                                <button type="submit"
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-red-700 dark:text-red-300 bg-red-50 dark:bg-red-900/30 rounded-lg hover:bg-red-100 dark:hover:bg-red-900/50 transition-colors">
                                    Удалить
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="px-5 py-14 text-center">
                        <p class="text-slate-400 dark:text-slate-500 text-sm">Промокодов нет</p>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
