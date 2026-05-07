@extends('layouts.app')

@section('title', 'Ночной аудит')

@section('content')
<div class="max-w-xl mx-auto">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Ночной аудит</h1>
        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
            Выставляет начисления за проживание по заселённым бронированиям и помечает незаезды.
        </p>
    </div>

    @if(session('audit_output'))
    <div class="mb-5 p-4 bg-slate-900 dark:bg-slate-950 rounded-xl text-emerald-400 font-mono text-sm whitespace-pre leading-relaxed overflow-x-auto">{{ session('audit_output') }}</div>
    @endif

    <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm p-6">
        <form method="POST" action="{{ route('audit.run') }}">
            @csrf
            <div class="mb-5">
                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1.5">Дата аудита</label>
                <input type="date" name="date" value="{{ today()->toDateString() }}"
                       class="w-full border border-slate-200 dark:border-slate-600 dark:bg-slate-700 dark:text-white rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                <p class="mt-1.5 text-xs text-slate-400">Обычно оставьте сегодняшнюю дату.</p>
            </div>
            <div class="bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-700 rounded-lg p-3 mb-5 text-xs text-amber-800 dark:text-amber-300">
                <strong>Внимание:</strong> Операция необратима. Повторный запуск за одну и ту же дату не дублирует начисления.
            </div>
            <button type="submit"
                    class="w-full inline-flex items-center justify-center gap-2 px-5 py-3 bg-slate-900 dark:bg-slate-600 text-white text-sm font-semibold rounded-lg hover:bg-slate-800 dark:hover:bg-slate-500 transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5"><path stroke-linecap="round" stroke-linejoin="round" d="M21.752 15.002A9.72 9.72 0 0 1 18 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 0 0 3 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 0 0 9.002-5.998Z"/></svg>
                Запустить ночной аудит
            </button>
        </form>
    </div>
</div>
@endsection
