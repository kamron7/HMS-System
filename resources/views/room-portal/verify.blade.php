<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
    <title>Вход — {{ config('hotel.name') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Inter', sans-serif;
            background: #0a0d12;
            min-height: 100dvh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 32px 20px;
        }

        .wrap { width: 100%; max-width: 360px; }

        /* brand */
        .brand { text-align: center; margin-bottom: 32px; }
        .brand-icon {
            width: 54px; height: 54px;
            border-radius: 16px;
            background: rgba(245,158,11,0.12);
            border: 1px solid rgba(245,158,11,0.3);
            display: inline-flex; align-items: center; justify-content: center;
            margin-bottom: 14px;
        }
        .brand-name {
            font-size: 10px; font-weight: 800;
            letter-spacing: 0.28em; text-transform: uppercase;
            color: #f59e0b;
        }
        .brand-addr {
            font-size: 11px; color: rgba(255,255,255,0.25);
            margin-top: 4px;
        }

        /* card */
        .card {
            background: #131a24;
            border: 1px solid rgba(255,255,255,0.07);
            border-radius: 24px;
            overflow: hidden;
        }

        .card-top {
            padding: 28px 28px 24px;
            border-bottom: 1px solid rgba(255,255,255,0.05);
        }
        .room-label {
            font-size: 10px; font-weight: 700;
            letter-spacing: 0.18em; text-transform: uppercase;
            color: rgba(255,255,255,0.25);
            margin-bottom: 10px;
        }
        .room-number {
            font-size: 76px; font-weight: 900;
            color: #fff; line-height: 1;
            letter-spacing: -3px;
        }
        .room-type {
            font-size: 13px; font-weight: 500;
            color: rgba(255,255,255,0.35);
            margin-top: 8px;
        }

        /* form */
        .card-body { padding: 24px 28px 28px; }
        .form-title {
            font-size: 15px; font-weight: 700; color: #fff;
            margin-bottom: 6px;
        }
        .form-hint {
            font-size: 12px; color: rgba(255,255,255,0.3);
            line-height: 1.6; margin-bottom: 24px;
        }
        .form-hint code {
            font-family: ui-monospace, monospace;
            color: rgba(245,158,11,0.65);
            font-size: 11px;
        }

        /* OTP grid — grid guarantees equal columns */
        .otp-grid {
            display: grid;
            grid-template-columns: repeat(6, 1fr);
            gap: 8px;
            margin-bottom: 16px;
        }
        .otp-box {
            height: 56px;
            border-radius: 12px;
            border: 1.5px solid rgba(255,255,255,0.1);
            background: rgba(255,255,255,0.04);
            color: #fff;
            font-size: 20px; font-weight: 800;
            font-family: 'Inter', sans-serif;
            text-align: center;
            text-transform: uppercase;
            transition: border-color 0.18s, background 0.18s, box-shadow 0.18s, color 0.18s;
            /* kill browser defaults */
            outline: none;
            -webkit-tap-highlight-color: transparent;
            caret-color: transparent;
            width: 100%;
        }
        .otp-box::selection { background: transparent; }
        .otp-box:focus {
            border-color: #f59e0b;
            background: rgba(245,158,11,0.1);
            box-shadow: 0 0 0 3px rgba(245,158,11,0.18);
            color: #f59e0b;
        }

        /* error */
        .error-box {
            display: flex; align-items: center; gap: 8px;
            padding: 11px 14px;
            background: rgba(239,68,68,0.08);
            border: 1px solid rgba(239,68,68,0.25);
            border-radius: 12px;
            margin-bottom: 16px;
        }
        .error-box p { font-size: 12px; color: #f87171; font-weight: 500; }

        /* submit */
        .btn-submit {
            width: 100%; padding: 15px;
            border-radius: 14px;
            background: linear-gradient(135deg, #f59e0b, #d97706);
            color: #000;
            font-size: 14px; font-weight: 800;
            font-family: 'Inter', sans-serif;
            border: none; cursor: pointer;
            letter-spacing: 0.02em;
            box-shadow: 0 6px 20px rgba(245,158,11,0.3);
            transition: transform 0.14s, box-shadow 0.14s;
            display: flex; align-items: center; justify-content: center; gap: 8px;
        }
        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 28px rgba(245,158,11,0.42);
        }
        .btn-submit:active { transform: none; }

        /* reception */
        .reception {
            text-align: center;
            margin-top: 18px;
        }
        .reception p {
            font-size: 11px;
            color: rgba(255,255,255,0.18);
            margin-bottom: 8px;
        }
        .reception a {
            display: inline-flex; align-items: center; gap: 7px;
            text-decoration: none;
            padding: 9px 18px;
            border-radius: 12px;
            border: 1px solid rgba(255,255,255,0.08);
            background: rgba(255,255,255,0.03);
            font-size: 12px; font-weight: 600;
            color: rgba(255,255,255,0.45);
            transition: background 0.15s, border-color 0.15s, color 0.15s;
        }
        .reception a:hover {
            background: rgba(255,255,255,0.07);
            border-color: rgba(255,255,255,0.14);
            color: rgba(255,255,255,0.7);
        }
    </style>
</head>
<body>
<div class="wrap">

    {{-- Brand --}}
    <div class="brand">
        <div class="brand-icon">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="#f59e0b" width="24" height="24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25"/>
            </svg>
        </div>
        <div class="brand-name">{{ config('hotel.name') }}</div>
        <div class="brand-addr">{{ config('hotel.address') }}</div>
    </div>

    {{-- Card --}}
    <div class="card">

        {{-- Room info --}}
        <div class="card-top">
            <p class="room-label">Ваш номер</p>
            <p class="room-number">{{ $room->number }}</p>
            <p class="room-type">{{ $room->roomType->name }}</p>
        </div>

        {{-- Form --}}
        <div class="card-body">
            <p class="form-title">Введите код бронирования</p>
            <p class="form-hint">
                6 символов из письма-подтверждения<br>
                (часть после <code>H-</code>)
            </p>

            <form id="vForm" method="POST" action="{{ route('room-portal.verify.post', $token) }}">
                @csrf
                <input type="hidden" name="booking_ref" id="hid">

                <div class="otp-grid">
                    @foreach(range(0,5) as $i)
                    <input type="text" maxlength="1" autocomplete="off"
                           autocapitalize="characters" inputmode="text"
                           data-idx="{{ $i }}" id="c{{ $i }}"
                           class="otp-box">
                    @endforeach
                </div>

                @if($errors->has('booking_ref'))
                <div class="error-box">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="#f87171" width="15" height="15" style="flex-shrink:0;">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z"/>
                    </svg>
                    <p>{{ $errors->first('booking_ref') }}</p>
                </div>
                @endif

                <button type="submit" class="btn-submit">
                    Войти в портал
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" width="14" height="14">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/>
                    </svg>
                </button>
            </form>
        </div>
    </div>

    {{-- Reception --}}
    <div class="reception">
        <p>Не знаете свой код?</p>
        <a href="tel:{{ config('hotel.phone') }}">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="13" height="13">
                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 0 0 2.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 0 1-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 0 0-1.091-.852H4.5A2.25 2.25 0 0 0 2.25 4.5v2.25Z"/>
            </svg>
            Ресепшн · {{ config('hotel.phone') }}
        </a>
    </div>
</div>

<script>
(function () {
    const boxes  = Array.from({ length: 6 }, (_, i) => document.getElementById('c' + i));
    const hid    = document.getElementById('hid');
    const form   = document.getElementById('vForm');
    const hasErr = {{ $errors->has('booking_ref') ? 'true' : 'false' }};

    function sync() {
        hid.value = 'H-' + boxes.map(b => b.value.toUpperCase()).join('');
    }

    function paint(box) {
        const focused = document.activeElement === box;
        const filled  = box.value.length > 0;
        if (focused) return; // CSS :focus handles focused state
        if (filled) {
            box.style.borderColor = 'rgba(255,255,255,0.22)';
            box.style.background  = 'rgba(255,255,255,0.07)';
            box.style.boxShadow   = 'none';
            box.style.color       = '#fff';
        } else {
            box.style.borderColor = hasErr ? 'rgba(239,68,68,0.45)' : 'rgba(255,255,255,0.1)';
            box.style.background  = hasErr ? 'rgba(239,68,68,0.07)' : 'rgba(255,255,255,0.04)';
            box.style.boxShadow   = 'none';
            box.style.color       = '#fff';
        }
    }

    @if(old('booking_ref'))
    const oldVal = '{{ old('booking_ref') }}'.replace(/^H-?/i, '').toUpperCase();
    oldVal.split('').forEach((ch, i) => { if (boxes[i]) boxes[i].value = ch; });
    sync();
    @endif

    boxes.forEach((box, idx) => {
        paint(box);

        box.addEventListener('focus', () => { box.select(); });
        box.addEventListener('blur',  () => paint(box));

        box.addEventListener('keydown', e => {
            if (e.key === 'Backspace') {
                if (box.value) { box.value = ''; sync(); paint(box); }
                else if (idx > 0) { boxes[idx - 1].focus(); boxes[idx - 1].value = ''; sync(); }
                e.preventDefault(); return;
            }
            if (e.key === 'ArrowLeft'  && idx > 0) { e.preventDefault(); boxes[idx - 1].focus(); }
            if (e.key === 'ArrowRight' && idx < 5) { e.preventDefault(); boxes[idx + 1].focus(); }
        });

        box.addEventListener('input', () => {
            const v = box.value.toUpperCase().replace(/[^A-Z0-9]/g, '');
            box.value = v ? v.slice(-1) : '';
            sync(); paint(box);
            if (box.value && idx < 5) boxes[idx + 1].focus();
        });

        box.addEventListener('paste', e => {
            e.preventDefault();
            const t = (e.clipboardData || window.clipboardData).getData('text')
                .toUpperCase().replace(/^H-?/, '').replace(/[^A-Z0-9]/g, '');
            t.split('').slice(0, 6).forEach((ch, i) => { if (boxes[i]) boxes[i].value = ch; });
            sync(); boxes.forEach(paint);
            (boxes.find(b => !b.value) || boxes[5]).focus();
        });
    });

    form.addEventListener('submit', e => {
        sync();
        if (boxes.map(b => b.value).join('').length < 6) {
            e.preventDefault();
            (boxes.find(b => !b.value) || boxes[0]).focus();
        }
    });

    boxes[0].focus();
})();
</script>
</body>
</html>
