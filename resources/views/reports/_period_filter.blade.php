<div class="flex items-center gap-1 p-1 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl shadow-sm">
    @foreach(['month' => 'Месяц', 'quarter' => 'Квартал', 'year' => 'Год'] as $p => $label)
    <a href="{{ route($route, ['period' => $p]) }}"
       class="px-4 py-1.5 text-xs font-semibold rounded-lg transition-all
              {{ isset($period) && $period === $p
                ? 'bg-slate-900 dark:bg-white text-white dark:text-slate-900 shadow-sm'
                : 'text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-white' }}">
        {{ $label }}
    </a>
    @endforeach
</div>
