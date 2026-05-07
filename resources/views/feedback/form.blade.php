<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Оставьте отзыв — {{ config('hotel.name', 'Отель') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>[x-cloak]{display:none!important}</style>
</head>
<body class="bg-slate-50 min-h-screen flex items-center justify-center p-4">

<div class="w-full max-w-lg">

    {{-- Header --}}
    <div class="text-center mb-8">
        <div class="w-14 h-14 bg-blue-600 rounded-2xl flex items-center justify-center mx-auto mb-4">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-8 h-8 text-white"><path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.562.562 0 0 1 1.04 0l2.125 5.111a.563.563 0 0 0 .475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 0 0-.182.557l1.285 5.385a.562.562 0 0 1-.84.61l-4.725-2.885a.562.562 0 0 0-.586 0L6.982 20.54a.562.562 0 0 1-.84-.61l1.285-5.386a.562.562 0 0 0-.182-.557l-4.204-3.602a.562.562 0 0 1 .321-.988l5.518-.442a.563.563 0 0 0 .475-.345L11.48 3.5Z"/></svg>
        </div>
        <h1 class="text-2xl font-bold text-slate-900">Как вам понравилось?</h1>
        <p class="mt-1 text-slate-500">{{ config('hotel.name', 'Отель') }}</p>
    </div>

    @if($alreadyReviewed)
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-8 text-center">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-12 h-12 text-emerald-500 mx-auto mb-3"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
        <p class="text-lg font-semibold text-slate-800">Вы уже оставили отзыв</p>
        <p class="text-sm text-slate-500 mt-1">Спасибо, что поделились мнением!</p>
    </div>
    @else
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-8" x-data="{ rating: 0, hover: 0 }">

        {{-- Booking summary --}}
        <div class="flex items-center gap-3 p-3 bg-slate-50 rounded-xl mb-6 text-sm text-slate-600">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-slate-400 flex-shrink-0"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21m-3.75 3.75h.008v.008h-.008v-.008Zm0 3h.008v.008h-.008v-.008Zm0 3h.008v.008h-.008v-.008Z"/></svg>
            <div>
                <p class="font-semibold">Номер {{ optional($booking->room)->number }} — {{ optional(optional($booking->room)->roomType)->name }}</p>
                <p class="text-xs text-slate-400">{{ $booking->check_in_date->translatedFormat('d M') }} — {{ $booking->check_out_date->translatedFormat('d M Y') }}</p>
            </div>
        </div>

        <form method="POST" action="{{ route('feedback.store', ['booking' => $booking->id]) }}">
            @csrf

            {{-- Star rating --}}
            <div class="mb-6">
                <label class="block text-sm font-semibold text-slate-700 mb-3">Ваша оценка <span class="text-red-500">*</span></label>
                <div class="flex gap-2">
                    @for($i = 1; $i <= 5; $i++)
                    <button type="button"
                            @click="rating = {{ $i }}"
                            @mouseover="hover = {{ $i }}"
                            @mouseleave="hover = 0"
                            class="text-3xl transition-transform hover:scale-110 focus:outline-none">
                        <span :class="(hover || rating) >= {{ $i }} ? 'text-amber-400' : 'text-slate-200'">★</span>
                    </button>
                    @endfor
                </div>
                <input type="hidden" name="rating" :value="rating">
                <p x-show="rating > 0" x-cloak class="mt-2 text-sm font-medium"
                   :class="rating >= 4 ? 'text-emerald-600' : (rating >= 3 ? 'text-amber-600' : 'text-red-600')"
                   x-text="['','Очень плохо','Плохо','Нормально','Хорошо','Отлично'][rating]"></p>
                @error('rating')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
            </div>

            {{-- Comment --}}
            <div class="mb-6">
                <label class="block text-sm font-semibold text-slate-700 mb-1.5">Комментарий (необязательно)</label>
                <textarea name="comment" rows="4" maxlength="1000"
                          placeholder="Расскажите о своём опыте…"
                          class="w-full border border-slate-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none">{{ old('comment') }}</textarea>
            </div>

            <button type="submit" :disabled="rating === 0"
                    :class="rating > 0 ? 'bg-blue-600 hover:bg-blue-700 text-white' : 'bg-slate-200 text-slate-400 cursor-not-allowed'"
                    class="w-full py-3 rounded-xl text-sm font-semibold transition-colors">
                Отправить отзыв
            </button>
        </form>
    </div>
    @endif
</div>

</body>
</html>
