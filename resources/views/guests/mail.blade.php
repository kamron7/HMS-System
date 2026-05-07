@extends('layouts.app')
@section('title', 'Рассылка гостям')

@section('content')
<div class="max-w-5xl mx-auto">

    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-slate-100">Рассылка гостям</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">Только гости с email-адресом ({{ $guests->count() }})</p>
        </div>
        <a href="{{ route('guests.index') }}"
           class="inline-flex items-center gap-1.5 text-sm text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200 transition-colors">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18"/></svg>
            Гости
        </a>
    </div>

    @if(session('success'))
    <div class="mb-5 bg-emerald-50 dark:bg-emerald-900/30 border border-emerald-200 dark:border-emerald-700 rounded-xl px-4 py-3 flex items-center gap-3">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5 text-emerald-600 flex-shrink-0"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
        <p class="text-sm font-medium text-emerald-800 dark:text-emerald-200">{{ session('success') }}</p>
    </div>
    @endif

    <form method="POST" action="{{ route('guests.mail.send') }}" id="mail-form">
        @csrf

        <div class="grid grid-cols-1 lg:grid-cols-5 gap-5">

            {{-- Left: guest list --}}
            <div class="lg:col-span-2 bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden flex flex-col" style="max-height:640px">

                <div class="px-4 py-3 border-b border-slate-100 dark:border-slate-700 flex-shrink-0">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-xs font-semibold text-slate-700 dark:text-slate-300">Получатели</span>
                        <label class="flex items-center gap-1.5 cursor-pointer text-xs font-medium text-blue-600 dark:text-blue-400 select-none">
                            <input type="checkbox" id="select-all" class="rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                            Выбрать всех
                        </label>
                    </div>
                    {{-- Filter tabs --}}
                    <div class="flex gap-1">
                        @foreach(['all' => 'Все', 'active' => 'Активные', 'past' => 'Прошлые'] as $key => $label)
                        <a href="{{ route('guests.mail', ['filter' => $key]) }}"
                           class="px-2.5 py-1 rounded-md text-xs font-medium transition-colors {{ $filter === $key ? 'bg-blue-600 text-white' : 'text-slate-500 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700' }}">
                            {{ $label }}
                        </a>
                        @endforeach
                    </div>
                </div>

                <div class="px-3 py-2 border-b border-slate-100 dark:border-slate-700 flex-shrink-0">
                    <input type="text" id="guest-search" placeholder="Поиск по имени или email..."
                           class="w-full px-3 py-1.5 text-xs border border-slate-200 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <div class="overflow-y-auto flex-1 divide-y divide-slate-100 dark:divide-slate-700" id="guest-list">
                    @forelse($guests as $guest)
                    <label class="flex items-center gap-3 px-4 py-2.5 hover:bg-slate-50 dark:hover:bg-slate-700/50 cursor-pointer guest-row"
                           data-name="{{ strtolower($guest->fullName) }}"
                           data-email="{{ strtolower($guest->email) }}">
                        <input type="checkbox" name="guest_ids[]" value="{{ $guest->id }}"
                               class="guest-checkbox rounded border-slate-300 text-blue-600 focus:ring-blue-500 flex-shrink-0">
                        <div class="min-w-0">
                            <p class="text-sm font-medium text-slate-900 dark:text-slate-100 truncate">{{ $guest->fullName }}</p>
                            <p class="text-xs text-slate-400 truncate">{{ $guest->email }}</p>
                        </div>
                    </label>
                    @empty
                    <div class="px-4 py-10 text-center text-sm text-slate-400">Нет гостей с email-адресом</div>
                    @endforelse
                </div>

                <div class="px-4 py-2.5 border-t border-slate-100 dark:border-slate-700 bg-slate-50 dark:bg-slate-900/30 flex-shrink-0">
                    <p class="text-xs text-slate-500 dark:text-slate-400">
                        Выбрано: <span id="selected-count" class="font-semibold text-slate-700 dark:text-slate-300">0</span>
                    </p>
                </div>
            </div>

            {{-- Right: compose --}}
            <div class="lg:col-span-3 space-y-4">

                <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm p-5">
                    <h2 class="text-sm font-bold text-slate-700 dark:text-slate-300 mb-4">Письмо</h2>

                    <div class="mb-4">
                        <label for="subject" class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5">
                            Тема письма <span class="text-red-500">*</span>
                        </label>
                        <input type="text" id="subject" name="subject" maxlength="200"
                               value="{{ old('subject') }}"
                               placeholder="Напр: Специальное предложение для наших гостей"
                               class="w-full px-3 py-2.5 border border-slate-200 dark:border-slate-600 rounded-lg text-sm bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-500 @error('subject') border-red-400 @enderror">
                        @error('subject')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <div class="mb-2">
                        <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5">
                            Текст письма <span class="text-red-500">*</span>
                        </label>
                        {{-- TinyMCE target --}}
                        <textarea id="body" name="body" class="@error('body') border-red-400 @enderror">{{ old('body') }}</textarea>
                        @error('body')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                        <p class="mt-2 text-xs text-slate-400">Имя гостя подставляется автоматически в приветствии.</p>
                    </div>
                </div>

                @if($errors->has('guest_ids'))
                <p class="text-sm text-red-600">{{ $errors->first('guest_ids') }}</p>
                @endif

                <button type="submit" id="send-btn"
                        class="w-full py-3 bg-blue-600 text-white text-sm font-semibold rounded-xl hover:bg-blue-700 transition-colors flex items-center justify-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75"/></svg>
                    Отправить (<span id="send-count">0</span>)
                </button>
            </div>
        </div>
    </form>
</div>

{{-- TinyMCE --}}
<script src="https://cdn.tiny.cloud/1/no-api-key/tinymce/7/tinymce.min.js" referrerpolicy="origin"></script>
<script>
const isDark = document.documentElement.classList.contains('dark');

tinymce.init({
    selector: '#body',
    language: 'ru',
    language_url: 'https://cdn.jsdelivr.net/npm/tinymce-i18n@24.9.20/langs7/ru.js',
    skin: isDark ? 'oxide-dark' : 'oxide',
    content_css: isDark ? 'dark' : 'default',
    height: 380,
    menubar: false,
    statusbar: false,
    branding: false,
    promotion: false,
    plugins: 'lists link autolink paste wordcount',
    toolbar:
        'styles | bold italic underline strikethrough | forecolor | ' +
        'bullist numlist | link | alignleft aligncenter alignright | removeformat',
    style_formats: [
        { title: 'Абзац',     block: 'p' },
        { title: 'Заголовок 1', block: 'h2', styles: { 'font-size': '22px', 'font-weight': '700', 'color': '#1e293b' } },
        { title: 'Заголовок 2', block: 'h3', styles: { 'font-size': '18px', 'font-weight': '600', 'color': '#334155' } },
        { title: 'Заголовок 3', block: 'h4', styles: { 'font-size': '15px', 'font-weight': '600', 'color': '#475569' } },
        { title: 'Цитата',    block: 'blockquote', styles: { 'border-left': '4px solid #93c5fd', 'padding-left': '16px', 'color': '#475569', 'margin': '12px 0' } },
    ],
    content_style: `
        body { font-family: Arial, sans-serif; font-size: 14px; color: #334155; line-height: 1.7; margin: 16px; }
        p { margin: 0 0 12px; }
        h2 { font-size: 22px; font-weight: 700; color: #1e293b; margin: 20px 0 10px; }
        h3 { font-size: 18px; font-weight: 600; color: #334155; margin: 16px 0 8px; }
        h4 { font-size: 15px; font-weight: 600; color: #475569; margin: 14px 0 6px; }
        a { color: #2563eb; }
        blockquote { border-left: 4px solid #93c5fd; padding-left: 16px; color: #475569; margin: 12px 0; }
        ul, ol { padding-left: 20px; margin: 0 0 12px; }
    `,
    paste_as_text: false,
    setup(editor) {
        editor.on('change input keyup', () => editor.save());
    },
});

// Guest list logic
const checkboxes  = document.querySelectorAll('.guest-checkbox');
const selectAll   = document.getElementById('select-all');
const countEl     = document.getElementById('selected-count');
const sendCountEl = document.getElementById('send-count');
const sendBtn     = document.getElementById('send-btn');
const searchInput = document.getElementById('guest-search');

function updateCount() {
    const n = document.querySelectorAll('.guest-checkbox:checked').length;
    countEl.textContent     = n;
    sendCountEl.textContent = n;
    sendBtn.disabled        = n === 0;
    const visible = [...checkboxes].filter(c => c.closest('.guest-row').style.display !== 'none').length;
    selectAll.checked       = n > 0 && n === visible;
    selectAll.indeterminate = n > 0 && !selectAll.checked;
}

checkboxes.forEach(cb => cb.addEventListener('change', updateCount));

selectAll.addEventListener('change', () => {
    document.querySelectorAll('.guest-row').forEach(row => {
        if (row.style.display !== 'none') {
            row.querySelector('.guest-checkbox').checked = selectAll.checked;
        }
    });
    updateCount();
});

searchInput.addEventListener('input', () => {
    const q = searchInput.value.toLowerCase();
    document.querySelectorAll('.guest-row').forEach(row => {
        const match = row.dataset.name.includes(q) || row.dataset.email.includes(q);
        row.style.display = match ? '' : 'none';
    });
    updateCount();
});

document.getElementById('mail-form').addEventListener('submit', function(e) {
    tinymce.triggerSave();
    const n = document.querySelectorAll('.guest-checkbox:checked').length;
    if (n === 0) { e.preventDefault(); return; }
    const body = tinymce.get('body')?.getContent()?.trim();
    if (!body) { e.preventDefault(); alert('Введите текст письма.'); return; }
    if (n > 10 && !confirm(`Отправить письмо ${n} гостям?`)) e.preventDefault();
});

updateCount();
</script>
@endsection
