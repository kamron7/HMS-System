<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Вход — {{ config('hotel.name', 'HMS') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Playfair+Display:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        * { font-family: 'Inter', sans-serif; }
        .serif { font-family: 'Playfair Display', Georgia, serif; }

        .photo-side {
            background:
                linear-gradient(to right, rgba(0,0,0,0.15) 0%, rgba(0,0,0,0.55) 100%),
                url('/images/hotel-bg.jpg') center/cover no-repeat;
        }

        .form-side {
            background: #0c111d;
        }

        .input-field {
            background: rgba(255,255,255,0.05);
            border: 1px solid rgba(255,255,255,0.1);
            color: #f1f5f9;
            transition: border-color .2s, background .2s;
        }
        .input-field:focus {
            outline: none;
            border-color: rgba(99,138,255,0.6);
            background: rgba(255,255,255,0.08);
        }
        .input-field::placeholder { color: rgba(148,163,184,0.4); }

        input:-webkit-autofill {
            -webkit-box-shadow: 0 0 0 40px #161e30 inset !important;
            -webkit-text-fill-color: #f1f5f9 !important;
        }

        .login-btn {
            background: linear-gradient(135deg, #3b82f6, #2563eb);
            transition: box-shadow .2s, transform .15s;
        }
        .login-btn:hover {
            background: linear-gradient(135deg, #306ac7, #1e52c2);
            transition: box-shadow .2s, transform .15s;
        }
        .login-btn:active { transform: translateY(0); }

        .divider { width: 40px; height: 1px; background: rgba(255,255,255,0.15); }
    </style>
</head>
<body class="h-screen flex overflow-hidden">

    {{-- Left: photo + branding --}}
    <div class="hidden lg:flex photo-side flex-1 flex-col justify-between p-12 relative">

        {{-- Top logo --}}
        <div class="flex items-center gap-3">
            <div class="w-9 h-9 bg-white/15 backdrop-blur-sm rounded-xl flex items-center justify-center border border-white/25">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-white">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21m-3.75 3.75h.008v.008h-.008v-.008Zm0 3h.008v.008h-.008v-.008Zm0 3h.008v.008h-.008v-.008Z"/>
                </svg>
            </div>
            <span class="text-white/80 text-sm font-medium tracking-wide">HMS Платформа</span>
        </div>

        {{-- Bottom: hotel name --}}
        <div>
            <div class="divider mb-6"></div>
            <h1 class="serif text-5xl text-white leading-tight mb-4">{{ config('hotel.name', 'HMS') }}</h1>
            <!-- @if(config('hotel.address'))
            <p class="text-white/50 text-sm font-light tracking-wide">{{ config('hotel.address') }}</p>
            @endif
            @if(config('hotel.phone'))
            <p class="text-white/40 text-sm mt-1">{{ config('hotel.phone') }}</p>
            @endif -->
        </div>
    </div>

    {{-- Right: form --}}
    <div class="form-side w-full lg:w-[440px] flex-shrink-0 flex flex-col justify-between p-10 sm:p-14">

        <div class="flex-1 flex flex-col justify-center">

            {{-- Mobile hotel name --}}
            <div class="lg:hidden mb-10 text-center">
                <div class="w-12 h-12 bg-blue-600 rounded-2xl flex items-center justify-center mx-auto mb-4 shadow-lg shadow-blue-900/40">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6 text-white">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21m-3.75 3.75h.008v.008h-.008v-.008Zm0 3h.008v.008h-.008v-.008Zm0 3h.008v.008h-.008v-.008Z"/>
                    </svg>
                </div>
                <h2 class="serif text-2xl text-white">{{ config('hotel.name', 'HMS') }}</h2>
            </div>

            <p class="text-slate-500 text-xs font-medium tracking-[0.2em] uppercase mb-2">Добро пожаловать</p>
            <h2 class="text-2xl font-bold text-white mb-8">Войдите в систему</h2>

            @if($errors->any())
            <div class="mb-6 flex items-center gap-3 px-4 py-3.5 rounded-xl border border-red-500/20 bg-red-500/10 text-sm text-red-400">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 flex-shrink-0"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z"/></svg>
                {{ $errors->first() }}
            </div>
            @endif

            <form method="POST" action="/login" class="space-y-5">
                @csrf

                <div>
                    <label class="block text-xs font-medium text-slate-500 tracking-wide mb-2">Email</label>
                    <input type="email" name="email" value="{{ old('email') }}" autofocus required
                           class="input-field w-full rounded-xl px-4 py-3 text-sm"
                           placeholder="admin@hotel.uz">
                </div>

                <div>
                    <label class="block text-xs font-medium text-slate-500 tracking-wide mb-2">Пароль</label>
                    <div class="relative">
                        <input type="password" name="password" id="pwd" required
                               class="input-field w-full rounded-xl px-4 py-3 pr-12 text-sm"
                               placeholder="••••••••">
                        <button type="button" onclick="togglePwd()"
                                class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-600 hover:text-slate-400 transition-colors">
                            <svg id="eye-on" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.641 0-8.573-3.007-9.964-7.178Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/>
                            </svg>
                            <svg id="eye-off" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 hidden">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.242 4.242L9.88 9.88"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <button type="submit" class="login-btn w-full rounded-xl py-3 text-sm font-semibold text-white mt-2">
                    Войти в систему
                </button>
            </form>
        </div>

        {{-- Footer --}}
      <div class="flex items-center justify-center text-xs text-slate-300 pt-8 border-t border-white/5 mt-8">
        <a href="https://osg.uz" target="_blank" rel="noopener" class="hover:text-slate-500 transition-colors">
            Разработано компанией : Online Service Group
        </a>
</div>
    </div>

</body>
<script>
function togglePwd() {
    const p = document.getElementById('pwd'), on = document.getElementById('eye-on'), off = document.getElementById('eye-off');
    if (p.type === 'password') { p.type = 'text'; on.classList.add('hidden'); off.classList.remove('hidden'); }
    else { p.type = 'password'; on.classList.remove('hidden'); off.classList.add('hidden'); }
}
</script>
</html>
