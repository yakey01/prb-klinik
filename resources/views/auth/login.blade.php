<!DOCTYPE html>
<html lang="id" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Masuk — Apotek PRB · Klinik Dokterku</title>
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#160a12">
    <link rel="apple-touch-icon" href="/icons/icon-192.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=DM+Serif+Display:ital@0;1&display=swap" rel="stylesheet">
    @php
        $stats = $stats ?? ['pasien'=>0,'obat'=>0,'pengambilan'=>0,'distributor'=>0,'tanggal'=>now()->locale('id')->isoFormat('D MMM YYYY')];
    @endphp
    <style>
        :root{
            /* ── Pink world-class palette ── */
            --bg:#140811; --bg2:#1b0c17;
            --rose-900:#3a0f29; --rose-800:#5b1640;
            --pink:#ec4899; --pink-400:#f472b6; --pink-300:#f9a8d4; --pink-600:#db2777;
            --fuchsia:#d946ef; --rose:#fb7185;
            --ink:#fdeef6; --ink2:#fbd7ea;
            --mut:#d3a3bf; --mut2:#a87b97; --mut3:#7e5870;
            --line:rgba(244,114,182,.14); --line2:rgba(244,114,182,.22);
            --glass:rgba(34,14,27,.62);
            --ok:#34d399; --err:#fb7185;
            --radius:1.4rem;
        }
        *{box-sizing:border-box;margin:0;padding:0;}
        html,body{height:100%;}
        body{
            background:var(--bg);
            color:var(--ink);
            font-family:'Inter',-apple-system,BlinkMacSystemFont,sans-serif;
            min-height:100vh;
            display:flex; align-items:center; justify-content:center;
            padding:1.5rem; position:relative; overflow-x:hidden;
            -webkit-font-smoothing:antialiased;
        }

        /* ── Aurora / mesh ambient background ── */
        .bg-aurora{position:fixed; inset:0; z-index:0; overflow:hidden; pointer-events:none;}
        .bg-aurora span{position:absolute; border-radius:50%; filter:blur(70px); opacity:.55; mix-blend-mode:screen;}
        .blob1{width:46vw; height:46vw; left:-8vw; top:-10vw;  background:radial-gradient(circle,#ec4899,transparent 62%); animation:drift1 18s ease-in-out infinite;}
        .blob2{width:40vw; height:40vw; right:-6vw; top:8vw;   background:radial-gradient(circle,#d946ef,transparent 60%); animation:drift2 22s ease-in-out infinite;}
        .blob3{width:38vw; height:38vw; left:22vw; bottom:-14vw; background:radial-gradient(circle,#fb7185,transparent 60%); opacity:.4; animation:drift3 26s ease-in-out infinite;}
        @keyframes drift1{0%,100%{transform:translate(0,0) scale(1)}50%{transform:translate(5vw,4vw) scale(1.12)}}
        @keyframes drift2{0%,100%{transform:translate(0,0) scale(1)}50%{transform:translate(-4vw,5vw) scale(1.08)}}
        @keyframes drift3{0%,100%{transform:translate(0,0) scale(1)}50%{transform:translate(3vw,-4vw) scale(1.15)}}
        /* vignette + grid overlay */
        .bg-aurora::after{content:'';position:absolute;inset:0;
            background:
                radial-gradient(ellipse 80% 80% at 50% 50%, transparent 35%, rgba(10,4,8,.55) 100%),
                linear-gradient(rgba(255,255,255,.014) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255,255,255,.014) 1px, transparent 1px);
            background-size:100% 100%, 46px 46px, 46px 46px;}

        /* ── Stage wrapper (holds rotating glow, NOT clipped) ── */
        .stage{position:relative; z-index:1; width:100%; max-width:1000px;}
        .stage::before{content:''; position:absolute; inset:-2px; border-radius:calc(var(--radius) + 3px); z-index:0;
            background:conic-gradient(from 0deg, transparent 0deg, rgba(236,72,153,.85) 55deg, rgba(217,70,239,.7) 120deg, transparent 190deg, transparent 360deg);
            filter:blur(3px); opacity:.85; animation:borderSpin 7s linear infinite;}
        @keyframes borderSpin{to{transform:rotate(360deg)}}

        /* ── Card shell ── */
        .shell{
            position:relative; z-index:1; width:100%;
            display:grid; grid-template-columns:1.18fr .92fr;
            background:linear-gradient(150deg, rgba(40,16,31,.72), rgba(22,9,18,.82));
            border:1px solid var(--line2);
            border-radius:var(--radius);
            box-shadow:
                0 40px 120px -20px rgba(0,0,0,.7),
                0 0 0 1px rgba(255,255,255,.03) inset,
                0 1px 0 rgba(255,255,255,.06) inset;
            backdrop-filter:blur(22px) saturate(140%);
            -webkit-backdrop-filter:blur(22px) saturate(140%);
            overflow:hidden;
            animation:rise .7s cubic-bezier(.16,1,.3,1) both;
        }
        @keyframes rise{from{opacity:0; transform:translateY(22px) scale(.98)}to{opacity:1; transform:none}}

        /* ── LEFT brand panel ── */
        .brand{
            position:relative; padding:2.6rem 2.5rem 2.3rem;
            background:
                radial-gradient(ellipse 120% 90% at 0% 0%, rgba(236,72,153,.16), transparent 55%),
                radial-gradient(ellipse 100% 80% at 100% 100%, rgba(217,70,239,.12), transparent 55%);
            border-right:1px solid var(--line);
            display:flex; flex-direction:column;
        }
        .brand-top{display:flex; align-items:center; gap:.85rem; margin-bottom:2.2rem;}
        .logo-badge{
            width:48px; height:48px; border-radius:14px; flex-shrink:0;
            background:linear-gradient(140deg, var(--pink), var(--fuchsia));
            display:flex; align-items:center; justify-content:center;
            box-shadow:0 8px 24px -6px rgba(236,72,153,.6), 0 0 0 1px rgba(255,255,255,.14) inset;
            position:relative;
        }
        .logo-badge img{width:30px;height:30px;object-fit:contain;filter:drop-shadow(0 1px 2px rgba(0,0,0,.4));}
        /* ── Heartbeat logo mark ── */
        .heart-mark{display:flex; align-items:center; justify-content:center; color:#fff; will-change:transform;
            animation:heartbeat 1.5s ease-in-out infinite; filter:drop-shadow(0 1px 3px rgba(0,0,0,.35));}
        .heart-mark .ecg{stroke-dasharray:34; stroke-dashoffset:34; animation:ecgTrace 1.5s ease-in-out infinite;}
        @keyframes heartbeat{0%{transform:scale(1)}12%{transform:scale(1.22)}24%{transform:scale(.98)}36%{transform:scale(1.14)}55%,100%{transform:scale(1)}}
        @keyframes ecgTrace{0%{stroke-dashoffset:34}35%{stroke-dashoffset:0}70%{stroke-dashoffset:0}100%{stroke-dashoffset:-34}}
        /* soft glow ring behind the badge that beats with the heart */
        .logo-badge::after{content:''; position:absolute; inset:-6px; border-radius:18px; z-index:-1;
            background:radial-gradient(circle, rgba(236,72,153,.55), transparent 70%);
            animation:badgeGlow 1.5s ease-in-out infinite;}
        @keyframes badgeGlow{0%,55%,100%{opacity:.35; transform:scale(1)}12%{opacity:.8; transform:scale(1.18)}36%{opacity:.6; transform:scale(1.08)}}
        .brand-meta .nm{font-weight:800; font-size:1.02rem; letter-spacing:-.01em; line-height:1;}
        .brand-meta .nm em{font-style:normal; background:linear-gradient(90deg,var(--pink-300),var(--fuchsia)); -webkit-background-clip:text; background-clip:text; color:transparent;}
        .brand-meta .sub{font-size:.66rem; color:var(--mut2); letter-spacing:.16em; text-transform:uppercase; margin-top:.32rem;}

        .pill{
            display:inline-flex; align-items:center; gap:.5rem; align-self:flex-start;
            background:rgba(236,72,153,.1); border:1px solid var(--line2);
            color:var(--pink-300); font-size:.68rem; font-weight:700; letter-spacing:.04em;
            padding:.38rem .8rem; border-radius:999px; margin-bottom:1.15rem;
        }
        .pill .dot{width:6px;height:6px;border-radius:50%;background:var(--ok); box-shadow:0 0 0 0 rgba(52,211,153,.5); animation:beat 1.8s infinite;}
        @keyframes beat{0%{box-shadow:0 0 0 0 rgba(52,211,153,.5)}70%{box-shadow:0 0 0 7px rgba(52,211,153,0)}100%{box-shadow:0 0 0 0 rgba(52,211,153,0)}}

        .hero{font-family:'Inter',serif; font-weight:400; font-size:2.35rem; line-height:1.08; letter-spacing:-.01em; margin-bottom:.6rem;}
        .hero em{font-style:italic; background:linear-gradient(105deg,var(--pink-400),var(--fuchsia) 70%); -webkit-background-clip:text; background-clip:text; color:transparent;}
        .hero-sub{font-size:.84rem; color:var(--mut); line-height:1.6; max-width:34ch; margin-bottom:1.7rem;}

        .feats{display:flex; flex-direction:column; gap:.85rem; margin-bottom:1.9rem;}
        .feat{display:flex; align-items:center; gap:.75rem;}
        .feat .ic{width:34px;height:34px;border-radius:10px; flex-shrink:0; display:flex; align-items:center; justify-content:center;
            background:rgba(236,72,153,.1); border:1px solid var(--line2); color:var(--pink-300);}
        .feat .tx{font-size:.82rem; color:var(--ink2); font-weight:500;}
        .feat .tx small{display:block; font-size:.68rem; color:var(--mut2); font-weight:400; margin-top:.1rem;}

        .stats{display:grid; grid-template-columns:repeat(4,1fr); gap:.6rem; margin-top:auto;}
        .stat{background:rgba(255,255,255,.035); border:1px solid var(--line); border-radius:.85rem; padding:.75rem .7rem; text-align:left; transition:transform .2s, border-color .2s;}
        .stat:hover{transform:translateY(-2px); border-color:var(--line2);}
        .stat .v{font-size:1.32rem; font-weight:800; line-height:1; letter-spacing:-.02em; background:linear-gradient(120deg,var(--pink-300),#fff 80%); -webkit-background-clip:text; background-clip:text; color:transparent;}
        .stat .k{font-size:.6rem; color:var(--mut2); text-transform:uppercase; letter-spacing:.07em; margin-top:.4rem; line-height:1.3;}

        /* ── RIGHT form panel ── */
        .form-side{padding:2.6rem 2.5rem; display:flex; flex-direction:column; justify-content:center;}
        .fs-head h2{font-family:'Inter',serif; font-weight:400; font-size:1.55rem; letter-spacing:-.01em; margin-bottom:.3rem;}
        .fs-head p{font-size:.8rem; color:var(--mut); margin-bottom:1.7rem;}

        .alert{border-radius:.7rem; padding:.7rem 1rem; font-size:.8rem; margin-bottom:1.1rem; display:flex; gap:.55rem; align-items:flex-start; line-height:1.5;}
        .alert.ok{background:rgba(52,211,153,.1); border:1px solid rgba(52,211,153,.25); color:#6ee7b7;}
        .alert.err{background:rgba(251,113,133,.1); border:1px solid rgba(251,113,133,.28); color:#fda4af;}

        .field{margin-bottom:1.1rem;}
        .lbl{display:block; font-size:.68rem; font-weight:700; letter-spacing:.07em; text-transform:uppercase; color:var(--mut); margin-bottom:.45rem;}
        .lbl-row{display:flex; align-items:center; justify-content:space-between;}
        .lbl-row a{font-size:.7rem; color:var(--pink-300); text-decoration:none; font-weight:600; text-transform:none; letter-spacing:0;}
        .lbl-row a:hover{color:var(--pink-400); text-decoration:underline;}

        .inwrap{position:relative; display:flex; align-items:center;}
        .inwrap .lead{position:absolute; left:.95rem; color:var(--mut2); pointer-events:none; display:flex;}
        .in{
            width:100%; background:rgba(10,4,8,.6); border:1px solid var(--line2);
            color:var(--ink); border-radius:.7rem; padding:.82rem 1rem .82rem 2.7rem;
            font-size:.9rem; font-family:inherit; transition:border-color .2s, box-shadow .2s, background .2s;
        }
        .in::placeholder{color:var(--mut3);}
        .in:focus{outline:none; border-color:var(--pink); background:rgba(10,4,8,.85); box-shadow:0 0 0 4px rgba(236,72,153,.14);}
        .in.has-toggle{padding-right:3rem;}
        .toggle-pw{position:absolute; right:.7rem; background:none; border:none; cursor:pointer; color:var(--mut2); display:flex; padding:.3rem; border-radius:.4rem; transition:color .15s;}
        .toggle-pw:hover{color:var(--pink-300);}
        .err-tx{color:var(--err); font-size:.72rem; margin-top:.35rem; display:flex; gap:.3rem; align-items:center;}

        /* remember toggle */
        .remember{display:flex; align-items:center; gap:.6rem; margin-bottom:1.4rem; cursor:pointer; user-select:none; width:fit-content;}
        .remember input{position:absolute; opacity:0; pointer-events:none;}
        .switch{width:38px; height:21px; border-radius:999px; background:rgba(255,255,255,.1); border:1px solid var(--line2); position:relative; transition:background .2s, border-color .2s; flex-shrink:0;}
        .switch::after{content:''; position:absolute; top:2px; left:2px; width:15px; height:15px; border-radius:50%; background:var(--mut); transition:transform .2s, background .2s;}
        .remember input:checked + .switch{background:linear-gradient(120deg,var(--pink),var(--fuchsia)); border-color:transparent;}
        .remember input:checked + .switch::after{transform:translateX(17px); background:#fff;}
        .remember .rt{font-size:.78rem; color:var(--mut);}
        .remember:hover .rt{color:var(--ink2);}

        .btn{
            width:100%; border:none; cursor:pointer; border-radius:.8rem; padding:.92rem;
            font-family:inherit; font-size:.92rem; font-weight:800; letter-spacing:.01em;
            color:#fff; position:relative; overflow:hidden;
            background:linear-gradient(120deg,var(--pink-600),var(--pink) 45%,var(--fuchsia));
            background-size:180% 100%;
            box-shadow:0 10px 30px -8px rgba(236,72,153,.6), 0 0 0 1px rgba(255,255,255,.1) inset;
            display:flex; align-items:center; justify-content:center; gap:.55rem;
            transition:transform .15s, box-shadow .2s, background-position .5s;
        }
        /* periodic shine sweep (like sim btnShine) */
        .btn::after{content:''; position:absolute; top:0; left:-60%; width:45%; height:100%;
            background:linear-gradient(105deg, transparent, rgba(255,255,255,.45), transparent);
            transform:skewX(-18deg); animation:btnShine 4.5s ease-in-out infinite;}
        @keyframes btnShine{0%,18%{left:-60%}38%,100%{left:130%}}
        .btn:hover{transform:translateY(-2px); background-position:100% 0; box-shadow:0 16px 38px -8px rgba(236,72,153,.7);}
        .btn:active{transform:translateY(0);}
        .btn .spin{display:none; width:16px; height:16px; border:2px solid rgba(255,255,255,.4); border-top-color:#fff; border-radius:50%; animation:sp .6s linear infinite;}
        @keyframes sp{to{transform:rotate(360deg)}}
        .btn.loading .spin{display:inline-block;}
        .btn.loading .lbl-go, .btn.loading .arr{display:none;}

        .or{display:flex; align-items:center; gap:.85rem; margin:1.4rem 0; color:var(--mut3); font-size:.7rem; text-transform:uppercase; letter-spacing:.1em;}
        .or::before,.or::after{content:''; flex:1; height:1px; background:linear-gradient(90deg,transparent,var(--line2),transparent);}

        .info{background:rgba(236,72,153,.06); border:1px solid var(--line); border-radius:.7rem; padding:.8rem .95rem; font-size:.74rem; color:var(--mut); line-height:1.65;}
        .info strong{color:var(--pink-300);} .info code{font-family:ui-monospace,monospace; color:var(--ink2); background:rgba(255,255,255,.05); padding:.05rem .3rem; border-radius:.25rem; font-size:.72rem;}

        .trust{display:flex; align-items:center; justify-content:center; gap:1.1rem; margin-top:1.5rem; flex-wrap:wrap;}
        .trust span{display:inline-flex; align-items:center; gap:.35rem; font-size:.66rem; color:var(--mut2);}
        .trust svg{color:var(--pink-300); opacity:.8;}

        /* ── Responsive ── */
        @media (max-width:900px){
            .shell{grid-template-columns:1fr; max-width:440px;}
            .brand{display:none;}
            .brand-mini{display:flex !important;}
            .form-side{padding:2.2rem 1.8rem;}
        }
        @media (min-width:901px){ .brand-mini{display:none;} }
        @media (max-width:420px){ .form-side{padding:1.8rem 1.4rem;} .hero{font-size:2rem;} }

        .brand-mini{align-items:center; gap:.7rem; margin-bottom:1.6rem;}
        .brand-mini .logo-badge{width:40px;height:40px;border-radius:12px;}
        .brand-mini .logo-badge img{width:25px;height:25px;}
        .brand-mini .nm{font-weight:800; font-size:.95rem;}
        .brand-mini .nm em{font-style:normal; background:linear-gradient(90deg,var(--pink-300),var(--fuchsia)); -webkit-background-clip:text; background-clip:text; color:transparent;}
        .brand-mini .sub{font-size:.62rem; color:var(--mut2); letter-spacing:.12em; text-transform:uppercase; margin-top:.18rem;}

        @media (prefers-reduced-motion:reduce){
            .bg-aurora span,.shell,.pill .dot,.heart-mark,.heart-mark .ecg,.logo-badge::after,.stage::before,.btn::after{animation:none;}
            .heart-mark .ecg{stroke-dashoffset:0;}
        }
    </style>
</head>
<body>
    <div class="bg-aurora" aria-hidden="true">
        <span class="blob1"></span><span class="blob2"></span><span class="blob3"></span>
    </div>

    <div class="stage">
    <main class="shell">

        {{-- ─────────── LEFT: BRAND PANEL ─────────── --}}
        <section class="brand">
            <div class="brand-top">
                <div class="logo-badge"><span class="heart-mark"><svg width="26" height="26" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M12 20.5S3.5 15.2 3.5 9.2A4.7 4.7 0 0112 6.9a4.7 4.7 0 018.5 2.3c0 6-8.5 11.3-8.5 11.3z" fill="rgba(255,255,255,.16)" stroke="#fff" stroke-width="1.6" stroke-linejoin="round"/><path class="ecg" d="M5 12.2h2.6l1.4-3 2.2 6 1.6-4 1.1 2H19" stroke="#fff" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" fill="none"/></svg></span></div>
                <div class="brand-meta">
                    <div class="nm">Apotek <em>PRB</em></div>
                    <div class="sub">Klinik Dokterku</div>
                </div>
            </div>

            <span class="pill"><span class="dot"></span> Sahabat Menuju Sehat</span>

            <h1 class="hero">Selamat datang di <em>Apotek&nbsp;PRB</em></h1>
            <p class="hero-sub">Pusat kendali obat kronis BPJS, stok, pengadaan, dan klaim — dalam satu sistem yang elegan dan cepat.</p>

            <div class="feats">
                <div class="feat">
                    <div class="ic"><svg width="17" height="17" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M20 7H4a2 2 0 00-2 2v10a2 2 0 002 2h16a2 2 0 002-2V9a2 2 0 00-2-2z"/><path d="M16 21V5a2 2 0 00-2-2h-4a2 2 0 00-2 2v16"/></svg></div>
                    <div class="tx">Manajemen obat &amp; stok real-time<small>Katalog, kadaluarsa, dan minimum otomatis</small></div>
                </div>
                <div class="feat">
                    <div class="ic"><svg width="17" height="17" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2h11"/></svg></div>
                    <div class="tx">Klaim BPJS &amp; pengadaan satu alur<small>Rekonsiliasi dan tagihan terintegrasi</small></div>
                </div>
                <div class="feat">
                    <div class="ic"><svg width="17" height="17" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg></div>
                    <div class="tx">Terenkripsi &amp; aman<small>Akses berbasis sesi terproteksi</small></div>
                </div>
            </div>

            <div class="stats">
                <div class="stat"><div class="v">{{ number_format($stats['pasien']) }}</div><div class="k">Pasien Aktif</div></div>
                <div class="stat"><div class="v">{{ number_format($stats['obat']) }}</div><div class="k">Obat Dikelola</div></div>
                <div class="stat"><div class="v">{{ number_format($stats['pengambilan']) }}</div><div class="k">Ambil Bln Ini</div></div>
                <div class="stat"><div class="v">{{ number_format($stats['distributor']) }}</div><div class="k">Distributor</div></div>
            </div>
        </section>

        {{-- ─────────── RIGHT: FORM PANEL ─────────── --}}
        <section class="form-side">
            {{-- mini brand for mobile --}}
            <div class="brand-mini">
                <div class="logo-badge"><span class="heart-mark"><svg width="26" height="26" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M12 20.5S3.5 15.2 3.5 9.2A4.7 4.7 0 0112 6.9a4.7 4.7 0 018.5 2.3c0 6-8.5 11.3-8.5 11.3z" fill="rgba(255,255,255,.16)" stroke="#fff" stroke-width="1.6" stroke-linejoin="round"/><path class="ecg" d="M5 12.2h2.6l1.4-3 2.2 6 1.6-4 1.1 2H19" stroke="#fff" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" fill="none"/></svg></span></div>
                <div>
                    <div class="nm">Apotek <em>PRB</em></div>
                    <div class="sub">Klinik Dokterku</div>
                </div>
            </div>

            <div class="fs-head">
                <h2>Masuk ke akun Anda</h2>
                <p>Gunakan email dan kata sandi yang terdaftar.</p>
            </div>

            @if(session('status'))
            <div class="alert ok">
                <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" style="flex-shrink:0;margin-top:1px;"><polyline points="20 6 9 17 4 12"/></svg>
                <span>{{ session('status') }}</span>
            </div>
            @endif

            @if($errors->any() && !$errors->has('email') && !$errors->has('password'))
            <div class="alert err">
                <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" style="flex-shrink:0;margin-top:1px;"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                <span>{{ $errors->first() }}</span>
            </div>
            @endif

            <form method="POST" action="{{ route('login') }}" id="loginForm" novalidate>
                @csrf

                <div class="field">
                    <label class="lbl" for="email">Email</label>
                    <div class="inwrap">
                        <span class="lead"><svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="m22 7-10 5L2 7"/></svg></span>
                        <input id="email" class="in" type="email" name="email" value="{{ old('email') }}"
                               placeholder="nama@klinikdokterku.id" required autofocus autocomplete="email">
                    </div>
                    @error('email')<div class="err-tx"><svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>{{ $message }}</div>@enderror
                </div>

                <div class="field">
                    <div class="lbl-row">
                        <label class="lbl" for="password">Kata Sandi</label>
                        <a href="{{ Route::has('password.request') ? route('password.request') : '#' }}">Lupa sandi?</a>
                    </div>
                    <div class="inwrap">
                        <span class="lead"><svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0110 0v4"/></svg></span>
                        <input id="password" class="in has-toggle" type="password" name="password"
                               placeholder="••••••••••" required autocomplete="current-password">
                        <button type="button" class="toggle-pw" id="togglePw" aria-label="Tampilkan kata sandi">
                            <svg id="eyeOpen" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                            <svg id="eyeOff" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="display:none;"><path d="M17.94 17.94A10.07 10.07 0 0112 20c-7 0-11-8-11-8a18.45 18.45 0 015.06-5.94M9.9 4.24A9.12 9.12 0 0112 4c7 0 11 8 11 8a18.5 18.5 0 01-2.16 3.19m-6.72-1.07a3 3 0 11-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                        </button>
                    </div>
                    @error('password')<div class="err-tx"><svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>{{ $message }}</div>@enderror
                </div>

                <label class="remember">
                    <input type="checkbox" name="remember">
                    <span class="switch"></span>
                    <span class="rt">Ingat saya di perangkat ini</span>
                </label>

                <button type="submit" class="btn" id="submitBtn">
                    <span class="spin"></span>
                    <span class="lbl-go">Masuk</span>
                    <svg class="arr" width="17" height="17" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
                </button>
            </form>

            <div class="or">atau</div>

            <div class="info">
                <strong>Akun demo:</strong> <code>admin@klinikdokterku.id</code> · <code>klinik2024</code>
            </div>

            <div class="trust">
                <span><svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0110 0v4"/></svg>Data terenkripsi</span>
                <span><svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/></svg>Akses instan</span>
                <span><svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M22 11.08V12a10 10 0 11-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>Sistem andal</span>
            </div>
        </section>
    </main>
    </div>

    <script>
        // Password show/hide
        (function(){
            var btn=document.getElementById('togglePw'), pw=document.getElementById('password'),
                eo=document.getElementById('eyeOpen'), ef=document.getElementById('eyeOff');
            if(btn&&pw){ btn.addEventListener('click',function(){
                var show=pw.type==='password';
                pw.type=show?'text':'password';
                eo.style.display=show?'none':''; ef.style.display=show?'':'none';
                btn.setAttribute('aria-label', show?'Sembunyikan kata sandi':'Tampilkan kata sandi');
                pw.focus();
            });}
        })();
        // Submit loading state
        (function(){
            var f=document.getElementById('loginForm'), b=document.getElementById('submitBtn');
            if(f&&b){ f.addEventListener('submit',function(){
                if(f.checkValidity && !f.checkValidity()) return;
                b.classList.add('loading'); b.disabled=true;
            });}
        })();
    </script>
</body>
</html>
