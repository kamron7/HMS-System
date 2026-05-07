@extends('layouts.app')

@section('title', 'Добавить гостя')

@section('content')
<div class="max-w-2xl mx-auto">

    <div class="mb-6 flex items-center gap-3">
        <a href="{{ route('guests.index') }}"
           class="inline-flex items-center gap-1.5 text-sm text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 transition-colors">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18"/></svg>
            Гости
        </a>
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 text-slate-300 dark:text-slate-600"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5"/></svg>
        <h1 class="text-xl font-bold text-slate-900 dark:text-slate-100">Добавить гостя</h1>
    </div>

    <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm p-6">
        <form method="POST" action="{{ route('guests.store') }}">
            @csrf

            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                <div>
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                        Имя <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="first_name" value="{{ old('first_name') }}"
                           class="w-full px-3 py-2.5 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100 dark:placeholder-slate-500 {{ $errors->has('first_name') ? 'border-red-400 dark:border-red-600' : 'border-slate-200 dark:border-slate-600' }}">
                    @error('first_name')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                        Фамилия <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="last_name" value="{{ old('last_name') }}"
                           class="w-full px-3 py-2.5 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100 dark:placeholder-slate-500 {{ $errors->has('last_name') ? 'border-red-400 dark:border-red-600' : 'border-slate-200 dark:border-slate-600' }}">
                    @error('last_name')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>

                {{-- Phone with country-code dropdown --}}
                <div>
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Телефон</label>
                    <div x-data="phoneDropdown('{{ old('phone') }}')" class="relative">
                        <div class="flex rounded-lg border overflow-hidden {{ $errors->has('phone') ? 'border-red-400 dark:border-red-600' : 'border-slate-200 dark:border-slate-600' }} focus-within:ring-2 focus-within:ring-blue-500 focus-within:border-blue-500">
                            <button type="button" @click="open = !open"
                                    class="flex items-center gap-1.5 pl-3 pr-2 py-2.5 shrink-0 bg-slate-50 dark:bg-slate-700/60 hover:bg-slate-100 dark:hover:bg-slate-700 border-r border-slate-200 dark:border-slate-600 transition-colors">
                                <img :src="'https://flagcdn.com/20x15/' + sel.iso + '.png'" :alt="sel.n" class="w-5 h-auto rounded-sm flex-shrink-0">
                                <span x-text="sel.c" class="text-sm font-semibold text-slate-700 dark:text-slate-200 tabular-nums"></span>
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-3 h-3 text-slate-400 shrink-0"><path fill-rule="evenodd" d="M5.22 8.22a.75.75 0 0 1 1.06 0L10 11.94l3.72-3.72a.75.75 0 1 1 1.06 1.06l-4.25 4.25a.75.75 0 0 1-1.06 0L5.22 9.28a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd"/></svg>
                            </button>
                            <input type="tel" x-model="local" placeholder="901 234 567"
                                   class="flex-1 min-w-0 px-3 py-2.5 text-sm bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500 focus:outline-none">
                        </div>
                        {{-- Dropdown --}}
                        <div x-show="open" x-cloak @click.away="open = false"
                             class="absolute top-full left-0 z-50 mt-1 w-60 rounded-xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 shadow-xl overflow-hidden">
                            <div class="p-2 border-b border-slate-100 dark:border-slate-700">
                                <input type="text" x-model="search" placeholder="Поиск страны…" @click.stop x-ref="searchInput"
                                       class="w-full px-2.5 py-1.5 text-xs bg-slate-50 dark:bg-slate-700 border border-slate-200 dark:border-slate-600 rounded-lg text-slate-900 dark:text-slate-100 placeholder-slate-400 focus:outline-none focus:ring-1 focus:ring-blue-500">
                            </div>
                            <div class="overflow-y-auto max-h-52">
                                <template x-for="c in filtered" :key="c.n">
                                    <button type="button" @click="pick(c)"
                                            :class="sel.n === c.n ? 'bg-blue-50 dark:bg-blue-900/30' : 'hover:bg-slate-50 dark:hover:bg-slate-700/50'"
                                            class="w-full flex items-center gap-2.5 px-3 py-2 text-left transition-colors">
                                        <img :src="'https://flagcdn.com/20x15/' + c.iso + '.png'" :alt="c.n" class="w-5 h-auto rounded-sm flex-shrink-0">
                                        <span x-text="c.n" :class="sel.n === c.n ? 'text-blue-700 dark:text-blue-300 font-medium' : 'text-slate-700 dark:text-slate-200'" class="flex-1 text-xs truncate"></span>
                                        <span x-text="c.c" class="text-xs text-slate-400 tabular-nums shrink-0"></span>
                                    </button>
                                </template>
                                <p x-show="filtered.length === 0" class="px-3 py-3 text-xs text-slate-400 text-center">Не найдено</p>
                            </div>
                        </div>
                        <input type="hidden" name="phone" :value="fullNumber">
                    </div>
                    @error('phone')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Email</label>
                    <input type="email" name="email" value="{{ old('email') }}"
                           class="w-full px-3 py-2.5 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100 dark:placeholder-slate-500 {{ $errors->has('email') ? 'border-red-400 dark:border-red-600' : 'border-slate-200 dark:border-slate-600' }}">
                    @error('email')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Номер паспорта</label>
                    <input type="text" name="passport_number" value="{{ old('passport_number') }}"
                           class="w-full px-3 py-2.5 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100 dark:placeholder-slate-500 {{ $errors->has('passport_number') ? 'border-red-400 dark:border-red-600' : 'border-slate-200 dark:border-slate-600' }}">
                    @error('passport_number')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Гражданство</label>
                    @php
                        $priorityCountries = ['Узбекистан','Россия','Казахстан','Кыргызстан','Таджикистан','Туркменистан','Азербайджан','Грузия','Армения','Беларусь','Украина','Молдова'];
                        $otherCountries = ['Австралия','Австрия','Афганистан','Бангладеш','Бельгия','Болгария','Бразилия','Великобритания','Венгрия','Вьетнам','Германия','Греция','Дания','Египет','Израиль','Индия','Индонезия','Иордания','Иран','Испания','Италия','Канада','Китай','Латвия','Литва','Малайзия','Мексика','Монголия','Нидерланды','Новая Зеландия','Норвегия','ОАЭ','Пакистан','Польша','Португалия','Румыния','Саудовская Аравия','Словакия','Словения','США','Таиланд','Турция','Финляндия','Франция','Хорватия','Чехия','Швейцария','Швеция','Эстония','Южная Корея','ЮАР','Япония'];
                        $selectedNationality = old('nationality', '');
                    @endphp
                    <select name="nationality" class="w-full px-3 py-2.5 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100 {{ $errors->has('nationality') ? 'border-red-400 dark:border-red-600' : 'border-slate-200 dark:border-slate-600' }}">
                        <option value="">— Не указано —</option>
                        <optgroup label="СНГ и ближнее зарубежье">
                            @foreach($priorityCountries as $c)
                                <option value="{{ $c }}" {{ $selectedNationality === $c ? 'selected' : '' }}>{{ $c }}</option>
                            @endforeach
                        </optgroup>
                        <optgroup label="Другие страны">
                            @foreach($otherCountries as $c)
                                <option value="{{ $c }}" {{ $selectedNationality === $c ? 'selected' : '' }}>{{ $c }}</option>
                            @endforeach
                        </optgroup>
                    </select>
                    @error('nationality')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>

                <div class="sm:col-span-2">
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Метка</label>
                    <select name="tag" class="w-full px-3 py-2.5 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100 border-slate-200 dark:border-slate-600">
                        <option value="">— Без метки —</option>
                        @foreach(\App\Enums\GuestTag::cases() as $tag)
                            <option value="{{ $tag->value }}" {{ old('tag') === $tag->value ? 'selected' : '' }}>
                                {{ $tag->label() }}
                            </option>
                        @endforeach
                    </select>
                    @error('tag')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>
            </div>

            <div class="mt-6 flex items-center gap-3">
                <button type="submit"
                        class="inline-flex items-center gap-2 px-5 py-2.5 bg-blue-600 text-white text-sm font-semibold rounded-lg hover:bg-blue-700 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                    Сохранить
                </button>
                <a href="{{ route('guests.index') }}"
                   class="px-5 py-2.5 text-sm font-semibold text-slate-600 dark:text-slate-300 bg-slate-100 dark:bg-slate-700 rounded-lg hover:bg-slate-200 dark:hover:bg-slate-600 transition-colors">
                    Отмена
                </a>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
function phoneDropdown(existing) {
    return {
        open: false, search: '', local: '',
        countries: [
            {iso:'uz',n:'Узбекистан',c:'+998'},{iso:'ru',n:'Россия',c:'+7'},
            {iso:'kz',n:'Казахстан',c:'+7'},{iso:'kg',n:'Кыргызстан',c:'+996'},
            {iso:'tj',n:'Таджикистан',c:'+992'},{iso:'tm',n:'Туркменистан',c:'+993'},
            {iso:'az',n:'Азербайджан',c:'+994'},{iso:'ge',n:'Грузия',c:'+995'},
            {iso:'am',n:'Армения',c:'+374'},{iso:'by',n:'Беларусь',c:'+375'},
            {iso:'ua',n:'Украина',c:'+380'},{iso:'md',n:'Молдова',c:'+373'},
            {iso:'tr',n:'Турция',c:'+90'},{iso:'cn',n:'Китай',c:'+86'},
            {iso:'in',n:'Индия',c:'+91'},{iso:'de',n:'Германия',c:'+49'},
            {iso:'fr',n:'Франция',c:'+33'},{iso:'gb',n:'Великобритания',c:'+44'},
            {iso:'us',n:'США',c:'+1'},
        ],
        sel: {iso:'uz',n:'Узбекистан',c:'+998'},
        get filtered() {
            if (!this.search) return this.countries;
            const q = this.search.toLowerCase();
            return this.countries.filter(c => c.n.toLowerCase().includes(q) || c.c.includes(q));
        },
        get fullNumber() { return this.local.trim() ? this.sel.c + this.local.trim() : ''; },
        pick(c) { this.sel = c; this.open = false; this.search = ''; },
        init() {
            if (!existing) return;
            const sorted = [...this.countries].sort((a,b) => b.c.length - a.c.length);
            const match = sorted.find(c => existing.startsWith(c.c));
            if (match) { this.sel = match; this.local = existing.slice(match.c.length); }
            else { this.local = existing; }
        },
    };
}
</script>
@endpush
