<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Login — Klinik Dokterku</title>
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#0a1410">
    <link rel="apple-touch-icon" href="/icons/icon-192.png">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=DM+Serif+Display:ital@0;1&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        :root {
            --bg:#0a1410; --panel:#11241c; --card:#152b21;
            --line:#1f3d30; --line2:#2a5343;
            --ink:#eaf3ee; --mut:#8fae9f; --mut2:#5f8071;
            --gold:#d9a441; --gold2:#f2c668;
            --emer:#3fcf8e; --emer2:#5ce0a4;
            --red:#e8645a;
            --kd-blue: #4a90d9;
            --kd-gold: #f2c000;
        }
        * { box-sizing:border-box; margin:0; padding:0; }
        body {
            background: var(--bg);
            color: var(--ink);
            font-family: 'Plus Jakarta Sans', sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: flex-start;
            justify-content: center;
            padding: 2rem 0;
            overflow-y: auto;
        }

        /* Layered background gradients */
        body::before {
            content: '';
            position: fixed;
            inset: 0;
            background:
                radial-gradient(ellipse 90% 70% at 15% -5%,  rgba(74,144,217,.07) 0%, transparent 55%),
                radial-gradient(ellipse 70% 50% at 85% 110%, rgba(242,192,0,.05)  0%, transparent 50%),
                radial-gradient(ellipse 50% 40% at 50% 50%,  rgba(63,207,142,.04) 0%, transparent 60%);
            pointer-events: none;
            z-index: 0;
        }

        /* Subtle grid pattern */
        body::after {
            content: '';
            position: fixed;
            inset: 0;
            background-image: linear-gradient(rgba(255,255,255,.012) 1px, transparent 1px),
                              linear-gradient(90deg, rgba(255,255,255,.012) 1px, transparent 1px);
            background-size: 48px 48px;
            pointer-events: none;
            z-index: 0;
        }

        .login-wrap {
            width: 100%;
            max-width: 420px;
            padding: 1rem;
            position: relative;
            z-index: 1;
        }

        /* Logo container */
        .logo-area {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-bottom: 1.75rem;
        }

        .logo-img-wrap {
            position: relative;
            margin-bottom: 1rem;
        }

        .logo-img-wrap::before {
            content: '';
            position: absolute;
            inset: -12px;
            border-radius: 2rem;
            background: radial-gradient(
                ellipse 80% 80% at 50% 50%,
                rgba(74,144,217,.18) 0%,
                rgba(242,192,0,.12) 50%,
                transparent 75%
            );
            filter: blur(8px);
            z-index: -1;
        }

        .logo-img {
            width: 160px;
            height: 160px;
            object-fit: contain;
            border-radius: .75rem;
            display: block;
            filter:
                drop-shadow(0 0 28px rgba(74,144,217,.45))
                drop-shadow(0 0 16px rgba(242,192,0,.3))
                drop-shadow(0 8px 24px rgba(0,0,0,.6));
        }

        .brand-name {
            font-family: 'DM Serif Display', serif;
            font-size: 1.85rem;
            font-weight: 600;
            line-height: 1;
            letter-spacing: -.01em;
        }
        .brand-name .kd-blue { color: var(--kd-blue); }
        .brand-name .kd-gold { color: var(--kd-gold); }

        .brand-sub {
            font-size: .72rem;
            color: var(--mut);
            letter-spacing: .1em;
            text-transform: uppercase;
            margin-top: .4rem;
            display: flex;
            align-items: center;
            gap: .5rem;
        }

        .brand-sub::before, .brand-sub::after {
            content: '';
            flex: 1;
            height: 1px;
            background: linear-gradient(to right, transparent, var(--line2));
        }
        .brand-sub::after {
            background: linear-gradient(to left, transparent, var(--line2));
        }

        /* Login card */
        .login-card {
            background: linear-gradient(160deg, rgba(21,43,33,.95) 0%, rgba(17,36,28,.98) 100%);
            border: 1px solid var(--line2);
            border-radius: 1.25rem;
            padding: 2rem;
            box-shadow:
                0 24px 64px rgba(0,0,0,.5),
                0 0 0 1px rgba(255,255,255,.03) inset;
        }

        .login-card h2 {
            font-family: 'DM Serif Display', serif;
            font-size: 1.3rem;
            color: var(--ink);
            margin-bottom: .35rem;
            font-weight: 600;
        }

        .login-card .subtitle {
            font-size: .77rem;
            color: var(--mut);
            margin-bottom: 1.5rem;
        }

        .form-label {
            display: block;
            font-size: .68rem;
            font-weight: 700;
            letter-spacing: .07em;
            text-transform: uppercase;
            color: var(--mut);
            margin-bottom: .35rem;
        }

        .form-input {
            background: rgba(10,20,16,.7);
            border: 1px solid var(--line2);
            color: var(--ink);
            border-radius: .55rem;
            padding: .7rem 1rem;
            font-size: .88rem;
            font-family: 'Plus Jakarta Sans', sans-serif;
            width: 100%;
            transition: border-color .2s, box-shadow .2s;
        }
        .form-input:focus {
            outline: none;
            border-color: var(--gold);
            box-shadow: 0 0 0 3px rgba(217,164,65,.12);
        }
        .form-input::placeholder { color: var(--mut2); }

        .btn-login {
            width: 100%;
            background: linear-gradient(135deg, var(--gold) 0%, #c4892e 100%);
            color: #1a0e00;
            font-weight: 800;
            font-size: .9rem;
            font-family: 'Plus Jakarta Sans', sans-serif;
            letter-spacing: .02em;
            padding: .8rem;
            border-radius: .6rem;
            border: none;
            cursor: pointer;
            transition: opacity .2s, transform .15s, box-shadow .2s;
            margin-top: .25rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: .5rem;
            box-shadow: 0 4px 16px rgba(217,164,65,.25);
        }
        .btn-login:hover {
            opacity: .93;
            transform: translateY(-1px);
            box-shadow: 0 6px 24px rgba(217,164,65,.35);
        }
        .btn-login:active { transform: translateY(0); }

        .error-msg { color: var(--red); font-size: .73rem; margin-top: .3rem; }

        .divider {
            height: 1px;
            background: linear-gradient(to right, transparent, var(--line2), transparent);
            margin: 1.5rem 0;
        }

        .info-box {
            background: rgba(217,164,65,.05);
            border: 1px solid rgba(217,164,65,.12);
            border-radius: .65rem;
            padding: .85rem 1rem;
            font-size: .73rem;
            color: var(--mut);
            line-height: 1.6;
        }

        .info-box strong { color: var(--gold2); }

        @keyframes pulse { 0%,100%{opacity:1} 50%{opacity:.4} }

        @media (max-width: 480px) {
            .login-wrap { padding: .75rem; }
            .logo-img { width: 130px; height: 130px; }
            .brand-name { font-size: 1.6rem; }
            .login-card { padding: 1.5rem; }
        }
    </style>
</head>
<body>
    <div class="login-wrap">

        {{-- Logo + Brand --}}
        <div class="logo-area">
            <div class="logo-img-wrap">
                <img src="/img/logo-klinik.png" alt="Klinik Dokterku" class="logo-img">
            </div>
            <div class="brand-name">
                <span class="kd-blue">Klinik</span> <span class="kd-gold">Dokterku</span>
            </div>
            <div class="brand-sub">
                Sistem Manajemen Obat Klinik
            </div>
        </div>

        {{-- Login Card --}}
        <div class="login-card">
            <h2>Masuk ke <em style="color:var(--gold2);">Sistem</em></h2>
            <p class="subtitle">Sahabat Menuju Sehat</p>

            @if(session('status'))
            <div style="background:rgba(63,207,142,.1);border:1px solid rgba(63,207,142,.25);border-radius:.5rem;padding:.6rem 1rem;margin-bottom:1rem;font-size:.82rem;color:var(--emer);">
                {{ session('status') }}
            </div>
            @endif

            <form method="POST" action="{{ route('login') }}">
                @csrf
                <div style="margin-bottom:1rem;">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" value="{{ old('email') }}"
                           placeholder="admin@klinikdokterku.id"
                           class="form-input" required autofocus autocomplete="email">
                    @error('email')<div class="error-msg">{{ $message }}</div>@enderror
                </div>

                <div style="margin-bottom:1.25rem;">
                    <label class="form-label">Password</label>
                    <input type="password" name="password"
                           placeholder="••••••••"
                           class="form-input" required autocomplete="current-password">
                    @error('password')<div class="error-msg">{{ $message }}</div>@enderror
                </div>

                <button type="submit" class="btn-login">
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path d="M15 3h4a2 2 0 012 2v14a2 2 0 01-2 2h-4"/>
                        <polyline points="10 17 15 12 10 7"/>
                        <line x1="15" y1="12" x2="3" y2="12"/>
                    </svg>
                    Masuk ke Sistem
                </button>
            </form>

            <div class="divider"></div>

            <div class="info-box">
                <strong>Akun Default:</strong><br>
                Email: admin@klinikdokterku.id<br>
                Password: klinik2024
            </div>
        </div>

        {{-- Footer note --}}
        <div style="text-align:center;margin-top:1.5rem;font-size:.65rem;color:var(--mut2);">
            Klinik Dokterku · Kabupaten Kediri
            <span style="margin:0 .4rem;opacity:.4;">·</span>
            <span style="display:inline-flex;align-items:center;gap:.25rem;">
                <span style="width:5px;height:5px;border-radius:50%;background:var(--emer);animation:pulse 2s infinite;display:inline-block;"></span>
                Sistem Aktif
            </span>
        </div>

    </div>
</body>
</html>
