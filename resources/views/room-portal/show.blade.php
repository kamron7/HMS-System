<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Номер {{ $room->number }} — {{ config('hotel.name') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        [x-cloak] { display: none !important; }
        .star-icon { transition: color 0.12s, transform 0.12s; }
        .star-icon:hover { transform: scale(1.2); }
        @keyframes slideDown {
            from { opacity: 0; transform: translateY(-8px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        .flash-toast { animation: slideDown 0.35s ease both; }
        .service-card:hover .service-btn { background: #1e293b; }
        .qty-btn:hover { background: rgba(0,0,0,0.08); }
        input[type=file]::file-selector-button {
            padding: 7px 14px;
            border-radius: 8px;
            border: none;
            font-size: 11px;
            font-weight: 700;
            font-family: 'Inter', sans-serif;
            cursor: pointer;
            transition: opacity 0.15s;
        }
        input[type=file]::file-selector-button:hover { opacity: 0.8; }
    </style>
</head>
<body class="bg-slate-50 min-h-screen">

{{-- ── Hero ──────────────────────────────────────────────────────────────── --}}
<div class="relative overflow-hidden" style="background:linear-gradient(160deg,#0d1520 0%,#111827 50%,#0f1c2e 100%);">
    {{-- Decorative elements --}}
    <div class="absolute top-0 left-0 right-0 h-px" style="background:linear-gradient(90deg,transparent,rgba(245,158,11,0.4),transparent);"></div>
    <div class="absolute -top-24 -right-24 w-72 h-72 rounded-full pointer-events-none" style="background:radial-gradient(circle,rgba(245,158,11,0.07) 0%,transparent 70%);"></div>
    <div class="absolute -bottom-12 -left-12 w-48 h-48 rounded-full pointer-events-none" style="background:radial-gradient(circle,rgba(99,102,241,0.05) 0%,transparent 70%);"></div>

    <div class="max-w-lg mx-auto px-4 pt-5 pb-0 relative">

        {{-- Top bar --}}
        <div class="flex items-center justify-between mb-7">
            <div>
                <p class="text-[10px] font-bold uppercase tracking-[0.25em] text-amber-400">{{ config('hotel.name') }}</p>
                <p class="text-[10px] text-slate-500 mt-0.5">{{ config('hotel.address') }}</p>
            </div>
            <a href="tel:{{ config('hotel.phone') }}"
               class="flex items-center gap-2 text-xs font-semibold text-white rounded-xl px-3.5 py-2.5 transition-all"
               style="background:rgba(255,255,255,0.07);border:1px solid rgba(255,255,255,0.12);"
               onmouseover="this.style.background='rgba(255,255,255,0.12)'" onmouseout="this.style.background='rgba(255,255,255,0.07)'">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-3.5 h-3.5 text-amber-400">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 0 0 2.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 0 1-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 0 0-1.091-.852H4.5A2.25 2.25 0 0 0 2.25 4.5v2.25Z"/>
                </svg>
                Ресепшн
            </a>
        </div>

        {{-- Room number + guest --}}
        <div class="flex items-end justify-between gap-4">
            <div>
                <p class="text-[10px] font-semibold uppercase tracking-[0.18em] text-slate-500 mb-1.5">Ваш номер</p>
                <h1 class="font-black text-white leading-none tracking-tight" style="font-size:80px;letter-spacing:-4px;line-height:0.85;">{{ $room->number }}</h1>
                <div class="flex items-center gap-2 mt-3">
                    <span class="text-sm text-slate-400 font-medium">{{ $room->roomType->name }}</span>
                    <span class="w-1 h-1 rounded-full bg-slate-600"></span>
                    <span class="text-[11px] font-mono text-amber-400/70 tracking-wider">{{ $booking->booking_ref }}</span>
                </div>
            </div>

            @php $roomImages = $room->imageUrls(); @endphp
            @if(count($roomImages))
            <div class="flex-shrink-0 mb-1" x-data="{ idx: 0, imgs: {{ json_encode($roomImages) }} }">
                <div class="w-24 h-20 rounded-2xl overflow-hidden shadow-2xl" style="border:1.5px solid rgba(255,255,255,0.12);">
                    <img :src="imgs[idx]" class="w-full h-full object-cover" alt="Фото номера">
                </div>
            </div>
            @endif
        </div>

        {{-- Guest strip --}}
        <div class="mt-5 -mx-4 px-4 py-3.5 flex items-center gap-3" style="background:rgba(255,255,255,0.04);border-top:1px solid rgba(255,255,255,0.07);">
            <div class="w-8 h-8 rounded-full flex items-center justify-center flex-shrink-0" style="background:rgba(245,158,11,0.15);border:1px solid rgba(245,158,11,0.25);">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 text-amber-400">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z"/>
                </svg>
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-sm text-slate-200">Добро пожаловать, <span class="font-bold text-white">{{ $booking->guest->fullName }}</span></p>
            </div>
            <div class="flex items-center gap-1.5 flex-shrink-0">
                <div class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></div>
                <span class="text-[10px] text-slate-500 font-semibold uppercase tracking-wide">Активен</span>
            </div>
        </div>
    </div>
</div>

<main class="max-w-lg mx-auto px-4 py-5 space-y-3.5">

    {{-- Flash messages --}}
    @if(session('ordered'))
    <div class="flash-toast flex items-center gap-3 rounded-2xl px-4 py-3.5" style="background:linear-gradient(135deg,#ecfdf5,#f0fdf4);border:1px solid #bbf7d0;">
        <div class="w-9 h-9 rounded-xl bg-emerald-100 flex items-center justify-center flex-shrink-0">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4.5 h-4.5 text-emerald-600"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
        </div>
        <div>
            <p class="text-sm font-bold text-emerald-800">Заказ принят!</p>
            <p class="text-xs text-emerald-600 mt-0.5">«{{ session('ordered') }}» — скоро принесём.</p>
        </div>
    </div>
    @endif
    @if(session('feedback_sent'))
    <div class="flash-toast flex items-center gap-3 rounded-2xl px-4 py-3.5" style="background:linear-gradient(135deg,#ecfdf5,#f0fdf4);border:1px solid #bbf7d0;">
        <div class="w-9 h-9 rounded-xl bg-emerald-100 flex items-center justify-center flex-shrink-0">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4.5 h-4.5 text-emerald-600"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
        </div>
        <p class="text-sm font-bold text-emerald-800">Спасибо за ваш отзыв!</p>
    </div>
    @endif
    @if(session('maintenance_sent'))
    <div class="flash-toast flex items-center gap-3 rounded-2xl px-4 py-3.5" style="background:linear-gradient(135deg,#ecfdf5,#f0fdf4);border:1px solid #bbf7d0;">
        <div class="w-9 h-9 rounded-xl bg-emerald-100 flex items-center justify-center flex-shrink-0">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4.5 h-4.5 text-emerald-600"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
        </div>
        <div>
            <p class="text-sm font-bold text-emerald-800">Заявка принята!</p>
            <p class="text-xs text-emerald-600 mt-0.5">«{{ session('maintenance_sent') }}» — разберёмся.</p>
        </div>
    </div>
    @endif
    @if(session('portal_error'))
    <div class="flash-toast rounded-2xl px-4 py-3.5 text-sm text-red-700 font-medium" style="background:#fef2f2;border:1px solid #fecaca;">{{ session('portal_error') }}</div>
    @endif

    {{-- ── Image Gallery ──────────────────────────────────────────────────── --}}
    @if(count($roomImages) > 1)
    <div class="rounded-2xl overflow-hidden shadow-md" style="height:220px;"
         x-data="{ idx: 0, imgs: {{ json_encode($roomImages) }} }">
        <div class="relative w-full h-full">
            <img :src="imgs[idx]" class="w-full h-full object-cover" alt="Фото номера">
            <div class="absolute inset-0" style="background:linear-gradient(to top,rgba(0,0,0,0.45) 0%,transparent 55%)"></div>

            <button type="button" @click="idx = (idx - 1 + imgs.length) % imgs.length"
                    class="absolute left-3 top-1/2 -translate-y-1/2 w-9 h-9 rounded-full flex items-center justify-center transition-all"
                    style="background:rgba(0,0,0,0.35);backdrop-filter:blur(8px);border:1px solid rgba(255,255,255,0.15);">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="white" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5"/></svg>
            </button>
            <button type="button" @click="idx = (idx + 1) % imgs.length"
                    class="absolute right-3 top-1/2 -translate-y-1/2 w-9 h-9 rounded-full flex items-center justify-center transition-all"
                    style="background:rgba(0,0,0,0.35);backdrop-filter:blur(8px);border:1px solid rgba(255,255,255,0.15);">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="white" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5"/></svg>
            </button>

            <div class="absolute bottom-3 left-0 right-0 flex justify-center gap-1.5">
                <template x-for="(_, di) in imgs" :key="di">
                    <button type="button" @click="idx = di"
                            class="transition-all"
                            :class="di === idx ? 'w-4 h-1.5 bg-white rounded-full' : 'w-1.5 h-1.5 bg-white/40 rounded-full hover:bg-white/60'"></button>
                </template>
            </div>
            <span class="absolute top-3 right-3 text-[11px] text-white font-bold px-2 py-1 rounded-full"
                  style="background:rgba(0,0,0,0.4);backdrop-filter:blur(8px);"
                  x-text="(idx+1) + ' / ' + imgs.length"></span>
        </div>
    </div>
    @endif

    {{-- ── Services / Upsells ──────────────────────────────────────────────── --}}
    @if($upsells->isNotEmpty())
    <div class="bg-white rounded-2xl shadow-sm overflow-hidden" style="border:1px solid #e8ecf0;">
        <div class="px-5 pt-5 pb-2">
            <div class="flex items-center gap-3 mb-5">
                <div class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0" style="background:linear-gradient(135deg,#fef3c7,#fde68a);border:1px solid #fcd34d40;">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-amber-600">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 1 0-7.5 0v4.5m11.356-1.993 1.263 12c.076.721-.506 1.357-1.235 1.357H4.366c-.729 0-1.311-.636-1.235-1.357l1.263-12a1.125 1.125 0 0 1 1.118-1.007h12.976c.58 0 1.077.443 1.118 1.007z"/>
                    </svg>
                </div>
                <div>
                    <h2 class="text-base font-bold text-slate-900">Заказать в номер</h2>
                    <p class="text-xs text-slate-400 mt-0.5">Подтверждение от персонала</p>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3 pb-5">
                @foreach($upsells as $upsell)
                <div class="service-card rounded-xl p-3.5 flex flex-col gap-3.5 transition-all" style="border:1.5px solid #e8ecf0;background:#fafbfc;" x-data="{ qty: 1 }">
                    <div class="flex items-start gap-2.5">
                        <div class="w-9 h-9 rounded-xl flex items-center justify-center flex-shrink-0" style="background:#f1f5f9;border:1px solid #e2e8f0;">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4.5 h-4.5 text-slate-600">
                                <path stroke-linecap="round" stroke-linejoin="round" d="{{ $upsell['icon'] }}"/>
                            </svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-xs font-bold text-slate-900 leading-tight">{{ $upsell['label'] }}</p>
                            <p class="text-[11px] text-amber-600 mt-1 font-semibold">{{ number_format($upsell['price'], 0, '.', ' ') }} сум</p>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('room-portal.order', $token) }}" class="mt-auto">
                        @csrf
                        <input type="hidden" name="key" value="{{ $upsell['key'] }}">
                        <input type="hidden" name="qty" :value="qty">

                        <div class="flex items-center gap-2">
                            <div class="flex items-center rounded-lg overflow-hidden flex-shrink-0" style="border:1.5px solid #e2e8f0;background:#fff;">
                                <button type="button" @click="qty = Math.max(1, qty - 1)"
                                        class="qty-btn w-7 h-7 flex items-center justify-center text-slate-400 hover:text-slate-700 transition-colors">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-3 h-3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14"/></svg>
                                </button>
                                <span x-text="qty" class="w-6 text-center text-xs font-black text-slate-900 tabular-nums select-none"></span>
                                <button type="button" @click="qty = Math.min(10, qty + 1)"
                                        class="qty-btn w-7 h-7 flex items-center justify-center text-slate-400 hover:text-slate-700 transition-colors">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-3 h-3"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                                </button>
                            </div>
                            <button type="submit"
                                    class="service-btn flex-1 py-2 text-white text-xs font-bold rounded-lg transition-colors text-center"
                                    style="background:#0f172a;">
                                Заказать
                            </button>
                        </div>
                    </form>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif

    {{-- ── My Orders ────────────────────────────────────────────────────────── --}}
    @if($myRequests->isNotEmpty())
    <div class="bg-white rounded-2xl shadow-sm overflow-hidden" style="border:1px solid #e8ecf0;">
        <div class="px-5 py-4 flex items-center gap-3" style="border-bottom:1px solid #f1f5f9;">
            <div class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0" style="background:linear-gradient(135deg,#eff6ff,#dbeafe);border:1px solid #bfdbfe60;">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-blue-500">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25ZM6.75 12h.008v.008H6.75V12Zm0 3h.008v.008H6.75V15Zm0 3h.008v.008H6.75V18Z"/>
                </svg>
            </div>
            <div>
                <h2 class="text-base font-bold text-slate-900">Мои заказы</h2>
                <p class="text-xs text-slate-400 mt-0.5">{{ $myRequests->count() }} {{ $myRequests->count() === 1 ? 'запрос' : 'запроса' }}</p>
            </div>
        </div>
        <div class="divide-y" style="divide-color:#f8fafc;">
            @foreach($myRequests as $req)
            <div class="px-5 py-3.5 flex items-center gap-3">
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-semibold text-slate-900 truncate">{{ $req->label }}</p>
                    <p class="text-xs text-slate-400 mt-0.5">
                        {{ $req->quantity }} шт · <span class="font-medium text-slate-600">{{ number_format($req->price_per_unit * $req->quantity, 0, '.', ' ') }} сум</span>
                        · {{ $req->created_at->diffForHumans() }}
                    </p>
                </div>
                @if($req->status === 'pending')
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg text-[11px] font-bold flex-shrink-0" style="background:#fffbeb;border:1px solid #fde68a;color:#b45309;">
                    <span class="w-1.5 h-1.5 rounded-full bg-amber-400 animate-pulse"></span>
                    Ожидает
                </span>
                @elseif($req->status === 'confirmed')
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg text-[11px] font-bold flex-shrink-0" style="background:#f0fdf4;border:1px solid #bbf7d0;color:#15803d;">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-3 h-3"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                    Выполнен
                </span>
                @else
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg text-[11px] font-bold flex-shrink-0" style="background:#fef2f2;border:1px solid #fecaca;color:#b91c1c;">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-3 h-3"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg>
                    Отклонён
                </span>
                @endif
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- ── My Maintenance Requests ─────────────────────────────────────────── --}}
    @if($myMaintenanceRequests->isNotEmpty())
    <div class="bg-white rounded-2xl shadow-sm overflow-hidden" style="border:1px solid #e8ecf0;">
        <div class="px-5 py-4 flex items-center gap-3" style="border-bottom:1px solid #f1f5f9;">
            <div class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0" style="background:linear-gradient(135deg,#fff7ed,#fed7aa);border:1px solid #fdba7460;">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-orange-500"><path stroke-linecap="round" stroke-linejoin="round" d="M11.42 15.17 17.25 21A2.652 2.652 0 0 0 21 17.25l-5.877-5.877M11.42 15.17l2.496-3.03c.317-.384.74-.626 1.208-.766M11.42 15.17l-4.655 5.653a2.548 2.548 0 1 1-3.586-3.586l5.654-4.654m5.292-4.643.346-.005A2.25 2.25 0 0 1 21 10.5V11c0 .825-.338 1.571-.885 2.11l-.343.344-2.496 3.03c-.317.384-.74.626-1.208.766m-5.292-4.643.005-.346a2.25 2.25 0 0 0-2.25-2.25h-.5a2.25 2.25 0 0 0-2.25 2.25v.5a2.25 2.25 0 0 0 2.25 2.25H8.5"/></svg>
            </div>
            <div>
                <h2 class="text-base font-bold text-slate-900">Мои заявки</h2>
                <p class="text-xs text-slate-400 mt-0.5">{{ $myMaintenanceRequests->count() }} {{ $myMaintenanceRequests->count() === 1 ? 'заявка' : 'заявки' }}</p>
            </div>
        </div>
        <div class="divide-y" style="divide-color:#f8fafc;">
            @foreach($myMaintenanceRequests as $mr)
            <div class="px-5 py-3.5 flex items-center gap-3">
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-semibold text-slate-900 truncate">{{ $mr->title }}</p>
                    <p class="text-xs text-slate-400 mt-0.5">{{ $mr->created_at->diffForHumans() }}</p>
                </div>
                @if($mr->status->value === 'open')
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg text-[11px] font-bold flex-shrink-0" style="background:#fffbeb;border:1px solid #fde68a;color:#b45309;">
                    <span class="w-1.5 h-1.5 rounded-full bg-amber-400 animate-pulse"></span>Принята
                </span>
                @elseif($mr->status->value === 'in_progress')
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg text-[11px] font-bold flex-shrink-0" style="background:#eff6ff;border:1px solid #bfdbfe;color:#1d4ed8;">
                    <span class="w-1.5 h-1.5 rounded-full bg-blue-400 animate-pulse"></span>В работе
                </span>
                @else
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg text-[11px] font-bold flex-shrink-0" style="background:#f0fdf4;border:1px solid #bbf7d0;color:#15803d;">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-3 h-3"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                    Решено
                </span>
                @endif
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- ── Maintenance Request ──────────────────────────────────────────────── --}}
    <div class="bg-white rounded-2xl shadow-sm overflow-hidden" style="border:1px solid #e8ecf0;" x-data="{ open: false }">
        <button @click="open = !open" type="button"
                class="w-full px-5 py-4 flex items-center gap-3 transition-colors text-left hover:bg-slate-50/80">
            <div class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0" style="background:linear-gradient(135deg,#fff7ed,#fed7aa);border:1px solid #fdba7460;">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-orange-500">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M11.42 15.17 17.25 21A2.652 2.652 0 0 0 21 17.25l-5.877-5.877M11.42 15.17l2.496-3.03c.317-.384.74-.626 1.208-.766M11.42 15.17l-4.655 5.653a2.548 2.548 0 1 1-3.586-3.586l5.654-4.654m5.292-4.643.346-.005A2.25 2.25 0 0 1 21 10.5V11c0 .825-.338 1.571-.885 2.11l-.343.344-2.496 3.03c-.317.384-.74.626-1.208.766m-5.292-4.643.005-.346a2.25 2.25 0 0 0-2.25-2.25h-.5a2.25 2.25 0 0 0-2.25 2.25v.5a2.25 2.25 0 0 0 2.25 2.25H8.5"/>
                </svg>
            </div>
            <div class="flex-1">
                <p class="text-base font-bold text-slate-900">Заявка на обслуживание</p>
                <p class="text-xs text-slate-400 mt-0.5">Поломка, уборка, доп. принадлежности</p>
            </div>
            <div class="w-7 h-7 rounded-full flex items-center justify-center flex-shrink-0 transition-all" style="background:#f1f5f9;" :class="open ? 'rotate-180' : ''">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-3.5 h-3.5 text-slate-500">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5"/>
                </svg>
            </div>
        </button>

        <div x-show="open" x-cloak style="border-top:1px solid #f1f5f9;">
            <form method="POST" action="{{ route('room-portal.maintenance', $token) }}" enctype="multipart/form-data" class="px-5 py-5 space-y-5">
                @csrf

                <div>
                    <p class="text-xs font-bold text-slate-700 mb-3 uppercase tracking-wide">Что случилось?</p>
                    <div class="grid grid-cols-2 gap-2">
                        @foreach([
                            ['key' => 'кондиционер', 'label' => 'Кондиционер', 'icon' => 'M12 21a4.5 4.5 0 0 0 2.25-8.312V5.25a2.25 2.25 0 1 0-4.5 0v7.438A4.5 4.5 0 0 0 12 21z M12 9v4'],
                            ['key' => 'сантехника',  'label' => 'Сантехника',  'icon' => 'M12 2.25c-5.385 4.385-8.25 8.5-8.25 11.5a8.25 8.25 0 0 0 16.5 0c0-3-2.865-7.115-8.25-11.5z'],
                            ['key' => 'электрика',   'label' => 'Электрика',   'icon' => 'M13.5 3L4.5 14.25h6V21l9-11.25h-6V3z'],
                            ['key' => 'уборка',      'label' => 'Уборка',      'icon' => 'M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 0 0-3.09 3.09zM18.259 8.715L18 9.75l-.259-1.035a3.375 3.375 0 0 0-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 0 0 2.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 0 0 2.456 2.456L21.75 6l-1.035.259a3.375 3.375 0 0 0-2.456 2.456z'],
                            ['key' => 'принадлежности', 'label' => 'Принадлежности', 'icon' => 'M15.75 10.5V6a3.75 3.75 0 1 0-7.5 0v4.5m11.356-1.993l1.263 12c.076.721-.506 1.357-1.235 1.357H4.366c-.729 0-1.311-.636-1.235-1.357l1.263-12a1.125 1.125 0 0 1 1.118-1.007h12.976c.58 0 1.077.443 1.118 1.007z'],
                            ['key' => 'шум',         'label' => 'Шум',         'icon' => 'M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75v-.7V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0'],
                            ['key' => 'другое',      'label' => 'Другое',      'icon' => 'M6.75 12a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0ZM12.75 12a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0ZM18.75 12a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Z'],
                        ] as $cat)
                        <label class="cursor-pointer">
                            <input type="radio" name="category" value="{{ $cat['key'] }}" class="sr-only peer" required>
                            <div class="flex items-center gap-2.5 px-3 py-2.5 rounded-xl transition-all cursor-pointer"
                                 style="border:1.5px solid #e2e8f0;background:#fafbfc;"
                                 x-bind:style="''"
                                 data-peer-target="true">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                                     class="w-4 h-4 text-slate-400 flex-shrink-0 peer-checked:text-orange-500">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="{{ $cat['icon'] }}"/>
                                </svg>
                                <span class="text-xs font-semibold text-slate-600 peer-checked:text-orange-700">{{ $cat['label'] }}</span>
                            </div>
                        </label>
                        @endforeach
                    </div>
                </div>

                <div>
                    <p class="text-xs font-bold text-slate-700 mb-3 uppercase tracking-wide">Срочность</p>
                    <div class="grid grid-cols-4 gap-1.5">
                        @foreach([
                            ['key' => 'low',    'label' => 'Нет',    'color' => 'text-slate-500'],
                            ['key' => 'medium', 'label' => 'Средне', 'color' => 'text-yellow-600'],
                            ['key' => 'high',   'label' => 'Срочно', 'color' => 'text-orange-600'],
                            ['key' => 'urgent', 'label' => 'Экстр.', 'color' => 'text-red-600'],
                        ] as $p)
                        <label class="cursor-pointer">
                            <input type="radio" name="priority" value="{{ $p['key'] }}" class="sr-only peer">
                            <div class="text-center px-1 py-2.5 rounded-xl transition-all"
                                 style="border:1.5px solid #e2e8f0;background:#fafbfc;">
                                <span class="text-[11px] font-bold {{ $p['color'] }}">{{ $p['label'] }}</span>
                            </div>
                        </label>
                        @endforeach
                    </div>
                </div>

                <div>
                    <label for="maintenance_desc" class="text-xs font-bold text-slate-700 uppercase tracking-wide">Описание</label>
                    <textarea id="maintenance_desc" name="description" rows="3" maxlength="1000" required
                              placeholder="Опишите подробнее, что случилось..."
                              class="mt-2 w-full px-4 py-3 rounded-xl text-sm text-slate-900 resize-none focus:outline-none transition-all placeholder-slate-300"
                              style="border:1.5px solid #e2e8f0;background:#fafbfc;"
                              onfocus="this.style.borderColor='#fb923c';this.style.boxShadow='0 0 0 3px rgba(251,146,60,0.12)'"
                              onblur="this.style.borderColor='#e2e8f0';this.style.boxShadow='none'"></textarea>
                </div>

                <div>
                    <label class="text-xs font-bold text-slate-700 uppercase tracking-wide">Фото <span class="font-normal text-slate-400 normal-case tracking-normal">(до 3, необязательно)</span></label>
                    <div class="mt-2">
                        <input type="file" name="photos[]" multiple accept="image/*"
                               class="w-full text-sm text-slate-500"
                               style="--file-btn-bg:#fff7ed;--file-btn-color:#c2410c;">
                    </div>
                </div>

                <button type="submit"
                        class="w-full py-3.5 text-white text-sm font-bold rounded-xl transition-all"
                        style="background:linear-gradient(135deg,#ea580c,#c2410c);box-shadow:0 4px 16px rgba(234,88,12,0.3);"
                        onmouseover="this.style.transform='translateY(-1px)';this.style.boxShadow='0 8px 24px rgba(234,88,12,0.35)'"
                        onmouseout="this.style.transform='none';this.style.boxShadow='0 4px 16px rgba(234,88,12,0.3)'">
                    Отправить заявку
                </button>
            </form>
        </div>
    </div>

    {{-- ── Feedback ──────────────────────────────────────────────────────────── --}}
    <div class="bg-white rounded-2xl shadow-sm overflow-hidden" style="border:1px solid #e8ecf0;">
        <div class="px-5 py-4 flex items-center gap-3" style="border-bottom:1px solid #f1f5f9;">
            <div class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0" style="background:linear-gradient(135deg,#fefce8,#fef08a);border:1px solid #fde04760;">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5 text-yellow-500">
                    <path fill-rule="evenodd" d="M10.788 3.21c.448-1.077 1.976-1.077 2.424 0l2.082 5.006 5.404.434c1.164.093 1.636 1.545.749 2.305l-4.117 3.527 1.257 5.273c.271 1.136-.964 2.033-1.96 1.425L12 18.354 7.373 21.18c-.996.608-2.231-.29-1.96-1.425l1.257-5.273-4.117-3.527c-.887-.76-.415-2.212.749-2.305l5.404-.434 2.082-5.005Z" clip-rule="evenodd"/>
                </svg>
            </div>
            <div>
                <h2 class="text-base font-bold text-slate-900">Оставить отзыв</h2>
                <p class="text-xs text-slate-400 mt-0.5">Расскажите о вашем пребывании</p>
            </div>
        </div>

        @if($alreadyReviewed)
        <div class="px-5 py-10 text-center">
            <div class="w-14 h-14 rounded-2xl flex items-center justify-center mx-auto mb-4" style="background:linear-gradient(135deg,#f0fdf4,#dcfce7);border:1px solid #bbf7d0;">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-7 h-7 text-emerald-500">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
                </svg>
            </div>
            <p class="text-base font-bold text-slate-800">Отзыв уже оставлен</p>
            <p class="text-sm text-slate-400 mt-1.5">Спасибо, что поделились впечатлениями!</p>
        </div>
        @else
        <form method="POST" action="{{ route('room-portal.feedback', $token) }}" enctype="multipart/form-data" class="px-5 py-5 space-y-5">
            @csrf

            <div>
                <p class="text-xs font-bold text-slate-700 mb-4 uppercase tracking-wide">Ваша оценка</p>
                <div class="flex gap-3 justify-center" id="stars">
                    @for($i = 1; $i <= 5; $i++)
                    <label class="cursor-pointer flex flex-col items-center gap-1.5">
                        <input type="radio" name="rating" value="{{ $i }}" class="sr-only" required>
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
                             class="w-11 h-11 star-icon text-slate-200"
                             data-value="{{ $i }}" fill="currentColor">
                            <path fill-rule="evenodd" d="M10.788 3.21c.448-1.077 1.976-1.077 2.424 0l2.082 5.006 5.404.434c1.164.093 1.636 1.545.749 2.305l-4.117 3.527 1.257 5.273c.271 1.136-.964 2.033-1.96 1.425L12 18.354 7.373 21.18c-.996.608-2.231-.29-1.96-1.425l1.257-5.273-4.117-3.527c-.887-.76-.415-2.212.749-2.305l5.404-.434 2.082-5.005Z" clip-rule="evenodd"/>
                        </svg>
                        <span class="text-[9px] font-semibold text-slate-400">{{ ['', 'Плохо', 'Так себе', 'Хорошо', 'Отлично', 'Супер'][$i] }}</span>
                    </label>
                    @endfor
                </div>
            </div>

            <div>
                <label for="comment" class="text-xs font-bold text-slate-700 uppercase tracking-wide">Комментарий <span class="font-normal text-slate-400 normal-case tracking-normal">(необязательно)</span></label>
                <textarea id="comment" name="comment" rows="3" maxlength="1000"
                          placeholder="Что вам понравилось или что можно улучшить?"
                          class="mt-2 w-full px-4 py-3 rounded-xl text-sm text-slate-900 resize-none focus:outline-none placeholder-slate-300 transition-all"
                          style="border:1.5px solid #e2e8f0;background:#fafbfc;"
                          onfocus="this.style.borderColor='#facc15';this.style.boxShadow='0 0 0 3px rgba(250,204,21,0.15)'"
                          onblur="this.style.borderColor='#e2e8f0';this.style.boxShadow='none'"></textarea>
            </div>

            <div>
                <label class="text-xs font-bold text-slate-700 uppercase tracking-wide">Фото <span class="font-normal text-slate-400 normal-case tracking-normal">(до 3, необязательно)</span></label>
                <div class="mt-2">
                    <input type="file" name="photos[]" multiple accept="image/*" class="w-full text-sm text-slate-500">
                </div>
            </div>

            <button type="submit"
                    class="w-full py-3.5 text-slate-900 text-sm font-bold rounded-xl transition-all"
                    style="background:linear-gradient(135deg,#fbbf24,#f59e0b);box-shadow:0 4px 16px rgba(245,158,11,0.3);"
                    onmouseover="this.style.transform='translateY(-1px)';this.style.boxShadow='0 8px 24px rgba(245,158,11,0.4)'"
                    onmouseout="this.style.transform='none';this.style.boxShadow='0 4px 16px rgba(245,158,11,0.3)'">
                Отправить отзыв ✦
            </button>
        </form>
        @endif
    </div>

    {{-- ── Footer ───────────────────────────────────────────────────────────── --}}
    <div class="py-6 text-center space-y-3">
        <a href="tel:{{ config('hotel.phone') }}"
           class="inline-flex items-center gap-2.5 px-5 py-2.5 rounded-xl transition-all text-slate-600 hover:text-slate-900"
           style="background:#fff;border:1.5px solid #e2e8f0;box-shadow:0 1px 4px rgba(0,0,0,0.06);">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4 text-amber-500">
                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 0 0 2.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 0 1-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 0 0-1.091-.852H4.5A2.25 2.25 0 0 0 2.25 4.5v2.25Z"/>
            </svg>
            <span class="text-sm font-semibold">{{ config('hotel.phone') }}</span>
        </a>
        <p class="text-[10px] text-slate-300 uppercase tracking-[0.3em]">{{ config('hotel.name') }}</p>
    </div>

</main>

<script>
    const icons = document.querySelectorAll('.star-icon');
    icons.forEach(icon => {
        const val = parseInt(icon.dataset.value);
        icon.addEventListener('mouseenter', () => highlightStars(val));
        icon.addEventListener('mouseleave', applySelected);
        icon.parentElement.querySelector('input').addEventListener('change', applySelected);
    });
    function highlightStars(upTo) {
        icons.forEach(icon => {
            const v = parseInt(icon.dataset.value);
            icon.classList.toggle('text-yellow-400', v <= upTo);
            icon.classList.toggle('text-slate-200', v > upTo);
        });
    }
    function applySelected() {
        const sel = document.querySelector('input[name="rating"]:checked');
        highlightStars(sel ? parseInt(sel.value) : 0);
    }

    // Peer-checked styles for maintenance categories/priorities
    document.querySelectorAll('input[name="category"], input[name="priority"]').forEach(input => {
        input.addEventListener('change', () => {
            document.querySelectorAll(`input[name="${input.name}"]`).forEach(other => {
                const div = other.parentElement.querySelector('div');
                if (div) {
                    if (other.checked) {
                        div.style.borderColor = '#fb923c';
                        div.style.background = '#fff7ed';
                    } else {
                        div.style.borderColor = '#e2e8f0';
                        div.style.background = '#fafbfc';
                    }
                }
            });
        });
    });

    // File input styling
    document.querySelectorAll('input[type="file"]').forEach(input => {
        const isOrange = input.closest('form[action*="maintenance"]');
        const bg    = isOrange ? '#fff7ed' : '#fefce8';
        const color = isOrange ? '#9a3412' : '#713f12';
        input.style.cssText += `--file-btn-bg:${bg};--file-btn-color:${color}`;
    });
</script>

<style>
input[type="file"]::file-selector-button {
    background: #fff7ed;
    color: #9a3412;
    padding: 7px 14px;
    border-radius: 8px;
    border: none;
    font-size: 11px;
    font-weight: 700;
    font-family: 'Inter', sans-serif;
    cursor: pointer;
    margin-right: 10px;
    transition: opacity 0.15s;
}
input[type="file"]::file-selector-button:hover { opacity: 0.8; }
</style>
</body>
</html>
