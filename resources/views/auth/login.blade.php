<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — PRB Klinik Dokterku</title>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:ital,opsz,wght@0,9..144,600;1,9..144,500&family=Archivo:wght@400;500;600&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        :root { --bg:#0a1410; --panel:#11241c; --card:#152b21; --line:#1f3d30; --line2:#2a5343; --ink:#eaf3ee; --mut:#8fae9f; --mut2:#5f8071; --gold:#d9a441; --gold2:#f2c668; --emer:#3fcf8e; --red:#e8645a; }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { background: var(--bg); color: var(--ink); font-family: 'Archivo', sans-serif; min-height: 100vh; display: flex; align-items: center; justify-content: center; }
        body::before { content:''; position:fixed; inset:0; background: radial-gradient(ellipse 80% 60% at 20% 0%, rgba(63,207,142,.08) 0%, transparent 60%), radial-gradient(ellipse 60% 40% at 90% 110%, rgba(217,164,65,.06) 0%, transparent 55%); pointer-events:none; }
        .login-card { width: 100%; max-width: 400px; background: linear-gradient(135deg, var(--card) 0%, var(--panel) 100%); border: 1px solid var(--line); border-radius: 1.25rem; padding: 2.5rem; position: relative; z-index: 1; }
        .form-input { background: var(--panel); border: 1px solid var(--line); color: var(--ink); border-radius: .5rem; padding: .65rem .9rem; font-size: .875rem; font-family: 'Archivo', sans-serif; width: 100%; transition: border-color .2s; }
        .form-input:focus { outline: none; border-color: var(--gold); box-shadow: 0 0 0 3px rgba(217,164,65,.12); }
        .btn-gold { background: linear-gradient(135deg, var(--gold) 0%, #c4892e 100%); color: #1a0e00; font-weight: 700; font-size: .9rem; padding: .75rem; border-radius: .5rem; border: none; cursor: pointer; width: 100%; transition: opacity .2s; }
        .btn-gold:hover { opacity: .9; }
        .error-msg { color: var(--red); font-size: .75rem; margin-top: .3rem; }
    </style>
</head>
<body>
    <div class="login-card">
        <div style="text-align:center; margin-bottom:2rem;">
            <div style="font-size:.68rem; color:var(--mut); letter-spacing:.12em; text-transform:uppercase; margin-bottom:.5rem;">
                Klinik Dokterku · Kabupaten Kediri
            </div>
            <h1 style="font-family:'Fraunces',serif; font-size:1.6rem; color:var(--ink); margin-bottom:.3rem;">
                Masuk ke <em style="color:var(--gold2);">Sistem PRB</em>
            </h1>
            <p style="font-size:.8rem; color:var(--mut);">Sistem Manajemen Pengadaan & Laba Obat</p>
        </div>

        @if(session('status'))
        <div style="background:rgba(63,207,142,.1);border:1px solid rgba(63,207,142,.25);border-radius:.5rem;padding:.6rem 1rem;margin-bottom:1rem;font-size:.82rem;color:var(--emer);">
            {{ session('status') }}
        </div>
        @endif

        <form method="POST" action="{{ route('login') }}">
            @csrf
            <div style="margin-bottom:1rem;">
                <label style="display:block;font-size:.7rem;font-weight:600;letter-spacing:.07em;text-transform:uppercase;color:var(--mut);margin-bottom:.35rem;">Email</label>
                <input type="email" name="email" value="{{ old('email') }}" placeholder="admin@klinikdokterku.id" class="form-input" required autofocus>
                @error('email') <div class="error-msg">{{ $message }}</div> @enderror
            </div>

            <div style="margin-bottom:1.5rem;">
                <label style="display:block;font-size:.7rem;font-weight:600;letter-spacing:.07em;text-transform:uppercase;color:var(--mut);margin-bottom:.35rem;">Password</label>
                <input type="password" name="password" placeholder="••••••••" class="form-input" required autocomplete="current-password">
                @error('password') <div class="error-msg">{{ $message }}</div> @enderror
            </div>

            <button type="submit" class="btn-gold">Masuk ke Sistem</button>
        </form>

        <div style="margin-top:1.5rem; padding:1rem; background:rgba(217,164,65,.06); border:1px solid rgba(217,164,65,.15); border-radius:.5rem; font-size:.75rem; color:var(--mut);">
            <strong style="color:var(--gold2); display:block; margin-bottom:.25rem;">Akun Default:</strong>
            Email: admin@klinikdokterku.id<br>
            Password: klinik2024
        </div>
    </div>
</body>
</html>
