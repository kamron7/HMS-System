<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Спасибо за отзыв — {{ config('hotel.name', 'Отель') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-50 min-h-screen flex items-center justify-center p-4">
<div class="w-full max-w-md text-center">
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-10">
        <div class="w-16 h-16 bg-emerald-100 rounded-full flex items-center justify-center mx-auto mb-5">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-9 h-9 text-emerald-600"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
        </div>
        <h1 class="text-2xl font-bold text-slate-900 mb-2">Спасибо!</h1>
        <p class="text-slate-500 text-sm">Ваш отзыв получен. Мы ценим ваше мнение и используем его для улучшения сервиса.</p>
        <div class="mt-5 flex justify-center gap-1">
            @for($i = 1; $i <= 5; $i++)
                <span class="text-2xl {{ $i <= $review->rating ? 'text-amber-400' : 'text-slate-200' }}">★</span>
            @endfor
        </div>
    </div>
</div>
</body>
</html>
