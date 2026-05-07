<div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5">
    <div class="flex items-center gap-2 mb-5">
        <div class="w-7 h-7 rounded-full bg-blue-600 flex items-center justify-center text-white text-xs font-bold flex-shrink-0">3</div>
        <h2 class="font-semibold text-slate-900">Ваши данные</h2>
    </div>

    <form method="POST" action="{{ route('book.store') }}" @submit="submitting = true">
        @csrf
        <input type="hidden" name="room_type_id" :value="selectedType ? selectedType.id : ''">
        <input type="hidden" name="check_in"     :value="checkIn">
        <input type="hidden" name="check_out"    :value="checkOut">
        <input type="hidden" name="adults"       :value="adults">
        <input type="hidden" name="children"     value="0">

        <div class="grid grid-cols-2 gap-3 mb-3">
            <div>
                <label class="block text-xs font-medium text-slate-500 mb-1.5">Имя <span class="text-red-500">*</span></label>
                <input type="text" name="first_name" required maxlength="80"
                       class="w-full border border-slate-200 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-slate-50"
                       placeholder="Иван">
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-500 mb-1.5">Фамилия <span class="text-red-500">*</span></label>
                <input type="text" name="last_name" required maxlength="80"
                       class="w-full border border-slate-200 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-slate-50"
                       placeholder="Иванов">
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-500 mb-1.5">Телефон <span class="text-red-500">*</span></label>
                <input type="tel" name="phone" required maxlength="30"
                       class="w-full border border-slate-200 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-slate-50"
                       placeholder="+998 90 000 00 00">
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-500 mb-1.5">Email</label>
                <input type="email" name="email" maxlength="150"
                       class="w-full border border-slate-200 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-slate-50"
                       placeholder="ivan@example.com">
            </div>
        </div>

        <div class="mb-4">
            <label class="block text-xs font-medium text-slate-500 mb-1.5">Пожелания</label>
            <textarea name="notes" rows="2" maxlength="500"
                      class="w-full border border-slate-200 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-slate-50 resize-none"
                      placeholder="Ранний заезд, детская кроватка и т.д."></textarea>
        </div>

        @if($errors->any())
        <div class="mb-4 p-3 bg-red-50 border border-red-200 rounded-xl text-sm text-red-700">
            <ul class="space-y-0.5">
                @foreach($errors->all() as $error)
                    <li class="flex items-start gap-1.5"><span class="mt-0.5 w-1 h-1 rounded-full bg-red-500 flex-shrink-0 inline-block"></span>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <button type="submit" :disabled="submitting"
                class="w-full bg-blue-600 hover:bg-blue-700 disabled:opacity-60 text-white font-semibold py-3 rounded-xl text-sm transition-colors flex items-center justify-center gap-2">
            <svg x-show="!submitting" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M6 12 3.269 3.125A59.769 59.769 0 0 1 21.485 12 59.768 59.768 0 0 1 3.27 20.875L5.999 12Zm0 0h7.5"/></svg>
            <svg x-show="submitting" x-cloak class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
            <span x-show="!submitting">Отправить запрос</span>
            <span x-show="submitting" x-cloak>Отправляем…</span>
        </button>
    </form>
</div>
