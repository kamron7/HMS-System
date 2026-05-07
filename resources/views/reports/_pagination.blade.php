<div x-show="pages > 1"
     x-cloak
     class="px-5 py-3 border-t border-slate-100 dark:border-slate-700/50 flex flex-wrap items-center justify-between gap-3">
    <p class="text-sm text-slate-500 dark:text-slate-400">
        Показано
        <span class="font-semibold text-slate-700 dark:text-slate-200" x-text="rangeStart"></span>–<span class="font-semibold text-slate-700 dark:text-slate-200" x-text="rangeEnd"></span>
        из <span class="font-semibold text-slate-700 dark:text-slate-200" x-text="total"></span>
    </p>
    <div class="flex items-center gap-1">
        <button @click="prev()" :disabled="page === 1"
                class="inline-flex items-center px-2.5 py-1.5 text-sm rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700 disabled:opacity-40 disabled:cursor-not-allowed transition-colors">
            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
        </button>
        <template x-if="pageRange[0] > 1">
            <span class="px-1 text-sm text-slate-400 dark:text-slate-500">…</span>
        </template>
        <template x-for="p in pageRange" :key="p">
            <button @click="goTo(p)"
                    :class="page === p ? 'bg-blue-600 text-white border-blue-600' : 'bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-300 border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700'"
                    class="min-w-[2rem] px-2.5 py-1.5 text-sm rounded-lg border transition-colors tabular-nums"
                    x-text="p"></button>
        </template>
        <template x-if="pageRange[pageRange.length - 1] < pages">
            <span class="px-1 text-sm text-slate-400 dark:text-slate-500">…</span>
        </template>
        <button @click="next()" :disabled="page === pages"
                class="inline-flex items-center px-2.5 py-1.5 text-sm rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700 disabled:opacity-40 disabled:cursor-not-allowed transition-colors">
            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/></svg>
        </button>
    </div>
</div>
