<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Manajemen Obat' }} — Klinik Dokterku</title>

    {{-- PWA Meta --}}
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#11241c">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="Apotik PRB">
    <link rel="apple-touch-icon" href="/icons/icon-192.png">
    <link rel="apple-touch-icon" sizes="152x152" href="/icons/icon-152.png">
    <link rel="apple-touch-icon" sizes="144x144" href="/icons/icon-144.png">
    <link rel="icon" type="image/png" sizes="192x192" href="/icons/icon-192.png">
    <link rel="icon" type="image/png" sizes="96x96" href="/icons/icon-96.png">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles

    <style>
        :root {
            --bg:    #0a1410;
            --panel: #11241c;
            --card:  #152b21;
            --line:  #1f3d30;
            --line2: #2a5343;
            --line3: #36705a;
            --ink:   #eaf3ee;
            --mut:   #8fae9f;
            --mut2:  #82a596;
            --gold:  #d9a441;
            --gold2: #f2c668;
            --gold3: #fcd98a;
            --emer:  #3fcf8e;
            --emer2: #5ce0a4;
            --red:   #e8645a;
            --red2:  #ff8077;
            --blue:  #6fb1e0;
        }
        * { box-sizing: border-box; }
        body {
            background: var(--bg);
            color: var(--ink);
            font-family: 'Inter', system-ui, sans-serif;
            min-height: 100vh;
        }
        body::before {
            content: '';
            position: fixed;
            inset: 0;
            background: radial-gradient(ellipse 80% 60% at 20% -10%, rgba(63,207,142,.06) 0%, transparent 60%),
                        radial-gradient(ellipse 60% 40% at 90% 110%, rgba(217,164,65,.05) 0%, transparent 55%);
            pointer-events: none;
            z-index: 0;
        }
        .font-heading { font-family: 'Inter', system-ui, sans-serif; font-weight: 800; letter-spacing: -0.03em; }
        .font-label   { font-family: 'Inter', system-ui, sans-serif; letter-spacing: .06em; text-transform: uppercase; font-weight: 600; }
        .font-mono    { font-family: 'JetBrains Mono', monospace; }
        .kpi-card {
            background: linear-gradient(135deg, var(--card) 0%, var(--panel) 100%);
            border: 1px solid var(--line);
            border-radius: 1rem;
            padding: 1.4rem 1.6rem;
            transition: border-color .2s, transform .2s;
        }
        .kpi-card:hover { border-color: var(--line2); transform: translateY(-1px); }
        .glass-card {
            background: rgba(17,36,28,.7);
            backdrop-filter: blur(12px);
            border: 1px solid var(--line);
            border-radius: 1rem;
        }
        .badge-laba  { background: rgba(63,207,142,.15); color: var(--emer2); border: 1px solid rgba(63,207,142,.25); }
        .badge-rugi  { background: rgba(232,100,90,.15); color: var(--red2);  border: 1px solid rgba(232,100,90,.25); }
        .badge-cek   { background: rgba(217,164,65,.15); color: var(--gold2); border: 1px solid rgba(217,164,65,.25); }
        .badge-po    { background: rgba(111,177,224,.15); color: var(--blue); border: 1px solid rgba(111,177,224,.25); }
        .badge-real  { background: rgba(63,207,142,.15); color: var(--emer2); border: 1px solid rgba(63,207,142,.25); }
        .badge-est   { background: rgba(217,164,65,.12); color: var(--gold3); border: 1px solid rgba(217,164,65,.2); }
        .badge { display: inline-flex; align-items: center; padding: .18rem .6rem; border-radius: 999px; font-size: .72rem; font-weight: 600; }
        .data-table { width: 100%; border-collapse: collapse; }
        .data-table th {
            background: var(--panel); color: var(--mut);
            font-size: .7rem; font-family: 'Inter', system-ui, sans-serif;
            text-transform: uppercase; letter-spacing: .07em;
            padding: .75rem 1rem; text-align: left;
            border-bottom: 1px solid var(--line);
            white-space: nowrap; cursor: pointer;
        }
        .data-table th:hover { color: var(--ink); }
        .data-table td {
            padding: .7rem 1rem; border-bottom: 1px solid rgba(31,61,48,.5);
            font-size: .85rem; vertical-align: middle;
        }
        .data-table tr:hover td { background: rgba(255,255,255,.02); }
        .data-table tr:last-child td { border-bottom: none; }
        .form-input {
            background: var(--panel);
            border: 1px solid var(--line);
            color: var(--ink);
            border-radius: .5rem;
            padding: .6rem .9rem;
            font-size: .875rem;
            font-family: 'Inter', system-ui, sans-serif;
            width: 100%;
            transition: border-color .2s;
        }
        .form-input:focus { outline: none; border-color: var(--gold); box-shadow: 0 0 0 3px rgba(217,164,65,.12); }
        .form-input::placeholder { color: var(--mut2); }
        .form-input option { background: var(--panel); }
        .form-label {
            display: block; font-size: .72rem; font-weight: 600;
            font-family: 'Inter', system-ui, sans-serif;
            letter-spacing: .06em; text-transform: uppercase;
            color: var(--mut); margin-bottom: .35rem;
        }
        .btn-gold {
            background: linear-gradient(135deg, var(--gold) 0%, #c4892e 100%);
            color: #1a0e00; font-weight: 700; font-size: .85rem;
            padding: .65rem 1.4rem; border-radius: .5rem;
            border: none; cursor: pointer;
            transition: opacity .2s, transform .2s;
            display: inline-flex; align-items: center; gap: .4rem;
        }
        .btn-gold:hover { opacity: .9; transform: translateY(-1px); }
        .btn-outline {
            background: transparent; color: var(--mut);
            border: 1px solid var(--line2); border-radius: .5rem;
            padding: .6rem 1.2rem; font-size: .82rem; cursor: pointer;
            transition: color .2s, border-color .2s;
            display: inline-flex; align-items: center; gap: .35rem;
            text-decoration: none;
        }
        .btn-outline:hover { color: var(--ink); border-color: var(--line3); }
        .btn-danger {
            background: rgba(232,100,90,.1); color: var(--red);
            border: 1px solid rgba(232,100,90,.2); border-radius: .5rem;
            padding: .4rem .8rem; font-size: .78rem; cursor: pointer;
            transition: background .2s;
        }
        .btn-danger:hover { background: rgba(232,100,90,.2); }
        input[type=range] { -webkit-appearance: none; width: 100%; height: 4px; background: var(--line2); border-radius: 2px; cursor: pointer; }
        input[type=range]::-webkit-slider-thumb { -webkit-appearance: none; width: 16px; height: 16px; border-radius: 50%; background: var(--gold); cursor: pointer; box-shadow: 0 0 0 3px rgba(217,164,65,.2); }
        input[type=range]:focus { outline: none; }
        /* Aksesibilitas: focus ring keyboard yang jelas untuk semua elemen interaktif */
        a:focus-visible, button:focus-visible, [role="button"]:focus-visible,
        input:focus-visible, select:focus-visible, textarea:focus-visible, [tabindex]:focus-visible {
            outline: 2px solid var(--gold);
            outline-offset: 2px;
            border-radius: 5px;
        }
        /* Jaring pengaman responsif: tabel besar selalu bisa di-scroll di layar sempit */
        .data-table { min-width: 640px; }
        @media (min-width: 900px) { .data-table { min-width: 0; } }
        #toast-container { position: fixed; bottom: 1.5rem; right: 1.5rem; z-index: 999; display: flex; flex-direction: column; gap: .5rem; }
        .toast { padding: .75rem 1.2rem; border-radius: .6rem; font-size: .85rem; font-weight: 500; animation: slideIn .3s ease; max-width: 360px; display: flex; align-items: center; gap: .6rem; }
        .toast-success { background: rgba(63,207,142,.15); border: 1px solid rgba(63,207,142,.3); color: var(--emer2); }
        .toast-error   { background: rgba(232,100,90,.15);  border: 1px solid rgba(232,100,90,.3);  color: var(--red2); }
        @keyframes slideIn { from { opacity: 0; transform: translateX(1rem); } to { opacity: 1; transform: translateX(0); } }
        @keyframes pulse { 0%,100%{opacity:1} 50%{opacity:.4} }
        @keyframes ddFadeIn { from { opacity: 0; transform: translateY(-4px) scale(.98); } to { opacity: 1; transform: translateY(0) scale(1); } }
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: var(--line2); border-radius: 3px; }
        ::-webkit-scrollbar-thumb:hover { background: var(--line3); }
        [x-cloak] { display: none !important; }
        .prb-drawer { display: flex; flex-direction: column; }

        /* ── Navigation ── */
        .nav-tab { display: inline-flex; align-items: center; gap: .45rem; padding: .6rem 1rem; border-radius: .5rem .5rem 0 0; color: var(--mut); font-size: .8rem; font-weight: 500; transition: color .2s, background .2s; text-decoration: none; white-space: nowrap; }
        .nav-tab:hover { color: var(--ink); background: rgba(255,255,255,.04); }
        .nav-tab.active { color: var(--gold2); border-bottom: 2px solid var(--gold); }
        .nav-dropdown { position: absolute; top: calc(100% + 6px); left: 0; min-width: 200px; background: #0e1e17; border: 1px solid var(--line2); border-radius: .75rem; padding: .4rem; box-shadow: 0 16px 48px rgba(0,0,0,.5), 0 4px 12px rgba(0,0,0,.3); z-index: 200; }
        .nav-dropdown a { display: flex; align-items: center; gap: .6rem; padding: .6rem .9rem; border-radius: .5rem; font-size: .8rem; font-weight: 500; color: var(--mut); text-decoration: none; transition: background .15s, color .15s; }
        .nav-dropdown a:hover { background: rgba(255,255,255,.05); color: var(--ink); }
        .nav-dropdown a.active { background: rgba(217,164,65,.08); color: var(--gold2); }
        .nav-dropdown .sep { height: 1px; background: var(--line); margin: .3rem .2rem; }

        /* ── Mobile Drawer Navigation ── */
        .mobile-nav-overlay {
            position: fixed; inset: 0; background: rgba(0,0,0,.6);
            backdrop-filter: blur(4px); z-index: 300;
            transition: opacity .25s;
        }
        .mobile-nav-drawer {
            position: fixed; top: 0; left: 0; bottom: 0;
            width: min(85vw, 320px);
            background: var(--panel);
            border-right: 1px solid var(--line2);
            z-index: 301;
            overflow-y: auto;
            transform: translateX(-100%);
            transition: transform .28s cubic-bezier(.4,0,.2,1);
            display: flex; flex-direction: column;
        }
        .mobile-nav-drawer.open { transform: translateX(0); }
        .mobile-drawer-header {
            padding: 1.25rem 1.25rem .75rem;
            border-bottom: 1px solid var(--line);
            display: flex; align-items: center; justify-content: space-between;
        }
        .mobile-drawer-section { padding: .5rem .75rem; }
        .mobile-drawer-link {
            display: flex; align-items: center; gap: .75rem;
            padding: .75rem 1rem; border-radius: .6rem;
            color: var(--mut); font-size: .875rem; font-weight: 500;
            text-decoration: none; transition: background .15s, color .15s;
            min-height: 44px;
        }
        .mobile-drawer-link:hover, .mobile-drawer-link.active {
            background: rgba(255,255,255,.05); color: var(--ink);
        }
        .mobile-drawer-link.active { background: rgba(217,164,65,.1); color: var(--gold2); }
        .mobile-drawer-group-label {
            font-size: .65rem; letter-spacing: .1em; text-transform: uppercase;
            color: var(--mut2); font-weight: 700;
            padding: .6rem 1rem .25rem;
        }

        /* ── Responsive Utility Classes ── */
        .grid-kpi  { display: grid; grid-template-columns: repeat(4,1fr); gap: .85rem; margin-bottom: 1.5rem; }
        .grid-form-3 { display: grid; grid-template-columns: repeat(3,1fr); gap: .85rem; }
        .grid-form-2 { display: grid; grid-template-columns: repeat(2,1fr); gap: .85rem; }
        .hide-mobile { }
        .show-mobile { display: none !important; }

        /* ── Improved global touch targets & font sizes ── */
        .badge { display: inline-flex; align-items: center; padding: .25rem .72rem; border-radius: 999px; font-size: .78rem; font-weight: 600; }
        .form-label { display: block; font-size: .78rem; font-weight: 600; font-family: 'Inter', system-ui, sans-serif; letter-spacing: .06em; text-transform: uppercase; color: var(--mut); margin-bottom: .35rem; }
        .data-table th { background: var(--panel); color: var(--mut); font-size: .72rem; font-family: 'Inter', system-ui, sans-serif; text-transform: uppercase; letter-spacing: .07em; padding: .75rem 1rem; text-align: left; border-bottom: 1px solid var(--line); white-space: nowrap; cursor: pointer; }
        .btn-gold { background: linear-gradient(135deg, var(--gold) 0%, #c4892e 100%); color: #1a0e00; font-weight: 700; font-size: .85rem; padding: .65rem 1.4rem; border-radius: .5rem; border: none; cursor: pointer; transition: opacity .2s, transform .2s; display: inline-flex; align-items: center; gap: .4rem; min-height: 44px; }
        .btn-outline { background: transparent; color: var(--mut); border: 1px solid var(--line2); border-radius: .5rem; padding: .6rem 1.2rem; font-size: .82rem; cursor: pointer; transition: color .2s, border-color .2s; display: inline-flex; align-items: center; gap: .35rem; text-decoration: none; min-height: 40px; }

        /* ── Media Queries ── */
        /* ── Kalkulator grid responsive ── */
        .kalk-grid     { display: grid; grid-template-columns: 2fr 3fr; gap: 1.25rem; align-items: start; }
        .kalk-kpi-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: .7rem; }

        @media (max-width: 768px) {
            .kalk-grid     { grid-template-columns: 1fr !important; }
            .kalk-kpi-grid { grid-template-columns: repeat(2, 1fr) !important; }
            #nav-tabs { display: none !important; }
            #nav-hamburger { display: flex !important; }
            .hide-mobile { display: none !important; }
            .show-mobile { display: flex !important; }

            main { padding: 1rem .875rem !important; }

            .grid-kpi { grid-template-columns: repeat(2,1fr) !important; gap: .6rem !important; }
            .grid-form-3 { grid-template-columns: 1fr !important; }
            .grid-form-2 { grid-template-columns: 1fr !important; }

            .kpi-card { padding: 1rem 1.1rem; border-radius: .75rem; }
            .glass-card { border-radius: .75rem; overflow-x: auto !important; }
            .glass-card:has(.data-table) { overflow-x: auto !important; }

            .data-table { min-width: 540px; }
            .data-table th { padding: .55rem .65rem; font-size: .68rem; }
            .data-table td { padding: .55rem .65rem; font-size: .82rem; }

            .btn-gold { padding: .7rem 1.1rem; font-size: .82rem; }
            .btn-outline { padding: .6rem 1rem; font-size: .8rem; }

            #toast-container { right: .75rem; left: .75rem; bottom: .75rem; }
            .toast { max-width: 100%; }

            footer { padding: 1rem; font-size: .7rem; }

            /* Drawer overlay: fix z-index conflicts with sticky nav */
            .mobile-nav-drawer { z-index: 60 !important; }
            .mobile-nav-overlay { z-index: 59 !important; }
        }

        @media (max-width: 480px) {
            .grid-kpi { grid-template-columns: 1fr 1fr !important; gap: .5rem !important; }
            main { padding: .75rem !important; }
            .kpi-card { padding: .85rem; }
            .kpi-card .font-heading { font-size: 1.6rem !important; }
        }
    </style>
</head>
<body x-data="{ mobileNav: false }">

    {{-- Mobile Navigation Drawer --}}
    <div class="mobile-nav-overlay" :style="mobileNav ? 'display:block;opacity:1' : 'display:none;opacity:0'" @click="mobileNav=false"></div>
    <div class="mobile-nav-drawer" :class="{ 'open': mobileNav }" style="z-index:301;">
        <div class="mobile-drawer-header">
            <div style="display:flex;align-items:center;gap:.75rem;">
                <img src="/img/logo-klinik.png" alt="Klinik Dokterku"
                     style="height:50px;width:50px;object-fit:contain;border-radius:.5rem;
                            filter:drop-shadow(0 2px 10px rgba(74,144,217,.4)) drop-shadow(0 2px 8px rgba(242,192,0,.25));">
                <div>
                    <div style="font-size:.95rem;font-weight:700;line-height:1.2;">
                        <span style="color:#6fb1e0;">Klinik</span> <span style="color:var(--gold2);">Dokterku</span>
                    </div>
                    <div style="font-size:.6rem;color:var(--mut);letter-spacing:.07em;text-transform:uppercase;margin-top:.15rem;">
                        Manajemen Obat PRB
                    </div>
                </div>
            </div>
            <button @click="mobileNav=false" style="background:rgba(255,255,255,.06); border:1px solid var(--line2); border-radius:.5rem; width:36px; height:36px; display:flex; align-items:center; justify-content:center; color:var(--mut); cursor:pointer;">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>
        <div class="mobile-drawer-section" style="flex:1; overflow-y:auto; padding-bottom:1rem;">
            <a href="{{ route('dashboard') }}" @click="mobileNav=false" class="mobile-drawer-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>
                Dashboard
            </a>

            <div class="mobile-drawer-group-label">Pengadaan</div>
            <a href="{{ route('pengadaan.pengajuan') }}" @click="mobileNav=false" class="mobile-drawer-link {{ request()->routeIs('pengadaan.pengajuan') ? 'active' : '' }}">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><path d="M9 15l2 2 4-4"/></svg>
                Pengajuan Pengadaan
            </a>
            <a href="{{ route('pengadaan.create') }}" @click="mobileNav=false" class="mobile-drawer-link {{ request()->routeIs('pengadaan.create') ? 'active' : '' }}">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="16"/><line x1="8" y1="12" x2="16" y2="12"/></svg>
                Pengadaan Baru
            </a>
            <a href="{{ route('riwayat.index') }}" @click="mobileNav=false" class="mobile-drawer-link {{ request()->routeIs('riwayat.*') ? 'active' : '' }}">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                Riwayat PO
            </a>
            <a href="{{ route('pengadaan.harian') }}" @click="mobileNav=false" class="mobile-drawer-link {{ request()->routeIs('pengadaan.harian') ? 'active' : '' }}">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                Barang Masuk Harian
            </a>
            <a href="{{ route('pengadaan.kebutuhan') }}" @click="mobileNav=false" class="mobile-drawer-link {{ request()->routeIs('pengadaan.kebutuhan') ? 'active' : '' }}">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
                Kebutuhan Obat Kronis
            </a>

            <div class="mobile-drawer-group-label">Inventori</div>
            <a href="{{ route('katalog.index') }}" @click="mobileNav=false" class="mobile-drawer-link {{ request()->routeIs('katalog.index') ? 'active' : '' }}">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M4 19.5A2.5 2.5 0 016.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 014 19.5v-15A2.5 2.5 0 016.5 2z"/></svg>
                Katalog Obat
            </a>
            <a href="{{ route('katalog.gabung') }}" @click="mobileNav=false" class="mobile-drawer-link {{ request()->routeIs('katalog.gabung') ? 'active' : '' }}">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="18" cy="18" r="3"/><circle cx="6" cy="6" r="3"/><path d="M6 21V9a9 9 0 0 0 9 9"/></svg>
                Gabung Obat
            </a>
            <a href="{{ route('stok.index') }}" @click="mobileNav=false" class="mobile-drawer-link {{ request()->routeIs('stok.index') ? 'active' : '' }}">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/></svg>
                Stok Obat
            </a>
            <a href="{{ route('stok-keluar.index') }}" @click="mobileNav=false" class="mobile-drawer-link {{ request()->routeIs('stok-keluar.*') ? 'active' : '' }}">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M5 12h14m-7-7 7 7-7 7"/></svg>
                Stok Keluar
            </a>
            <a href="{{ route('distributor.index') }}" @click="mobileNav=false" class="mobile-drawer-link {{ request()->routeIs('distributor.*') ? 'active' : '' }}">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="1" y="3" width="15" height="13" rx="1"/><path d="M16 8h4l3 3v5h-7V8z"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>
                Distributor
            </a>

            <div class="mobile-drawer-group-label">Pasien</div>
            <a href="{{ route('pasien.index') }}" @click="mobileNav=false" class="mobile-drawer-link {{ request()->routeIs('pasien.index') ? 'active' : '' }}">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                Daftar Pasien
            </a>
            <a href="{{ route('pasien.pengambilan') }}" @click="mobileNav=false" class="mobile-drawer-link {{ request()->routeIs('pasien.pengambilan') ? 'active' : '' }}">
                Pengambilan Obat
            </a>
            <a href="{{ route('pasien.riwayat') }}" @click="mobileNav=false" class="mobile-drawer-link {{ request()->routeIs('pasien.riwayat') ? 'active' : '' }}">
                Riwayat Pengambilan
            </a>
            <a href="{{ route('pasien.jadwal') }}" @click="mobileNav=false" class="mobile-drawer-link {{ request()->routeIs('pasien.jadwal') ? 'active' : '' }}">
                Jadwal & Reminder
            </a>

            <div class="mobile-drawer-group-label">Keuangan</div>
            <a href="{{ route('tagihan.index') }}" @click="mobileNav=false" class="mobile-drawer-link {{ request()->routeIs('tagihan.*') ? 'active' : '' }}">Tagihan</a>
            <a href="{{ route('laporan.index') }}" @click="mobileNav=false" class="mobile-drawer-link {{ request()->routeIs('laporan.*') ? 'active' : '' }}">Laporan Bulanan</a>
            <a href="{{ route('rekonsiliasi.index') }}" @click="mobileNav=false" class="mobile-drawer-link {{ request()->routeIs('rekonsiliasi.*') ? 'active' : '' }}">Rekonsiliasi BPJS</a>
            <a href="{{ route('kalkulator.index') }}" @click="mobileNav=false" class="mobile-drawer-link {{ request()->routeIs('kalkulator.*') ? 'active' : '' }}" style="{{ request()->routeIs('kalkulator.*') ? '' : 'color:var(--gold);' }}">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6"/></svg>
                Kalkulator Laba
            </a>

            <div class="mobile-drawer-group-label">Manajemen</div>
            <a href="{{ route('notifikasi.index') }}" @click="mobileNav=false" class="mobile-drawer-link {{ request()->routeIs('notifikasi.*') ? 'active' : '' }}">Notifikasi</a>
            <a href="{{ route('audit.index') }}" @click="mobileNav=false" class="mobile-drawer-link {{ request()->routeIs('audit.*') ? 'active' : '' }}">Audit Log</a>
            <a href="{{ route('pengaturan.index') }}" @click="mobileNav=false" class="mobile-drawer-link {{ request()->routeIs('pengaturan.*') ? 'active' : '' }}">Pengaturan</a>
        </div>
        <div style="padding:.75rem 1.25rem; border-top:1px solid var(--line);">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" style="width:100%; background:rgba(255,255,255,.05); border:1px solid var(--line2); border-radius:.6rem; color:var(--mut); padding:.75rem; font-size:.85rem; cursor:pointer; display:flex; align-items:center; justify-content:center; gap:.5rem; min-height:44px;">
                    <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                    Keluar
                </button>
            </form>
        </div>
    </div>

    <nav style="background:rgba(17,36,28,.95); backdrop-filter:blur(16px); border-bottom:1px solid var(--line); position:sticky; top:0; z-index:50;">
        <div style="max-width:1400px; margin:0 auto; padding:0 1.5rem;">
            <div style="display:flex; align-items:center; justify-content:space-between; padding:.8rem 0 .5rem;">
                <div style="display:flex; align-items:center; gap:.75rem;">
                    {{-- Hamburger (visible only on mobile via CSS) --}}
                    <button id="nav-hamburger" @click="mobileNav=true" style="display:none; align-items:center; justify-content:center; background:rgba(255,255,255,.06); border:1px solid var(--line2); border-radius:.5rem; width:40px; height:40px; color:var(--ink); cursor:pointer; flex-shrink:0;">
                        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
                    </button>
                    {{-- Brand: Logo + Name --}}
                    <div style="display:flex;align-items:center;gap:.85rem;">
                        <a href="{{ route('dashboard') }}" style="display:flex;align-items:center;flex-shrink:0;">
                            <img src="/img/logo-klinik.png" alt="Klinik Dokterku"
                                 style="height:48px;width:48px;object-fit:contain;border-radius:.5rem;
                                        filter:drop-shadow(0 2px 8px rgba(74,144,217,.35)) drop-shadow(0 2px 6px rgba(242,192,0,.2));">
                        </a>
                        <div>
                            <div style="font-size:.58rem;color:var(--mut);letter-spacing:.1em;text-transform:uppercase;margin-bottom:.1rem;line-height:1;" class="hide-mobile">
                                Sistem Manajemen Obat · PRB BPJS
                            </div>
                            <div style="display:flex;align-items:center;gap:.45rem;line-height:1.1;">
                                <span class="font-heading" style="font-size:1.15rem;font-weight:600;">
                                    <span style="color:#6fb1e0;">Klinik</span> <em style="color:var(--gold2);">Dokterku</em>
                                </span>
                                <span style="background:rgba(63,207,142,.12);border:1px solid rgba(63,207,142,.25);border-radius:999px;padding:.1rem .5rem;font-size:.62rem;color:var(--emer);display:inline-flex;align-items:center;gap:.25rem;" class="hide-mobile">
                                    <span style="width:5px;height:5px;border-radius:50%;background:var(--emer);animation:pulse 2s infinite;display:inline-block;"></span>
                                    Aktif
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                <form method="POST" action="{{ route('logout') }}" style="display:flex;align-items:center;gap:.75rem;" class="hide-mobile">
                    @csrf
                    <span style="color:var(--mut);font-size:.78rem;">{{ auth()->user()->name }}</span>
                    <button type="submit" class="btn-outline" style="padding:.35rem .8rem;font-size:.75rem;">Keluar</button>
                </form>
            </div>
            @php
                $inPengadaan = request()->routeIs('pengadaan.*') || request()->routeIs('riwayat.*');
                $inInventori = request()->routeIs('katalog.*') || request()->routeIs('stok.*') || request()->routeIs('bmhp.*') || request()->routeIs('stok-keluar.*') || request()->routeIs('distributor.*');
                $inKeuangan  = request()->routeIs('laporan.*') || request()->routeIs('rekonsiliasi.*') || request()->routeIs('tagihan.*') || request()->routeIs('kalkulator.*');
                $inPasien    = request()->routeIs('pasien*');
                $inManajemen = request()->routeIs('notifikasi.*') || request()->routeIs('audit.*') || request()->routeIs('pengaturan.*') || request()->routeIs('persyaratan-klaim.*') || request()->routeIs('deploy.*');
            @endphp
            <div style="display:flex; gap:.15rem; margin-top:.25rem; align-items:stretch;" id="nav-tabs">

                {{-- Dashboard --}}
                <a href="{{ route('dashboard') }}" class="nav-tab {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>
                    Dashboard
                </a>

                {{-- Pengadaan dropdown --}}
                <div class="nav-group" style="position:relative;">
                    <button onclick="navToggle('dd-pengadaan',this)" class="nav-tab {{ $inPengadaan ? 'active' : '' }}" style="background:none;border:none;cursor:pointer;" id="btn-pengadaan">
                        <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M6 2 3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 01-8 0"/></svg>
                        Pengadaan
                        <svg class="nav-chevron" width="11" height="11" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" style="transition:transform .15s;"><polyline points="6 9 12 15 18 9"/></svg>
                    </button>
                    <div class="nav-dropdown" id="dd-pengadaan" style="display:none;">
                        <a href="{{ route('pengadaan.pengajuan') }}" class="{{ request()->routeIs('pengadaan.pengajuan') ? 'active' : '' }}">
                            <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><path d="M9 15l2 2 4-4"/></svg>
                            Pengajuan Pengadaan
                        </a>
                        <a href="{{ route('pengadaan.create') }}" class="{{ request()->routeIs('pengadaan.create') || request()->routeIs('pengadaan.store') ? 'active' : '' }}">
                            <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="16"/><line x1="8" y1="12" x2="16" y2="12"/></svg>
                            Pengadaan Baru
                        </a>
                        <a href="{{ route('riwayat.index') }}" class="{{ request()->routeIs('riwayat.*') ? 'active' : '' }}">
                            <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                            Riwayat PO
                        </a>
                        <a href="{{ route('pengadaan.harian') }}" class="{{ request()->routeIs('pengadaan.harian') ? 'active' : '' }}">
                            <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                            Barang Masuk Harian
                        </a>
                        <div class="sep"></div>
                        <a href="{{ route('pengadaan.kebutuhan') }}" class="{{ request()->routeIs('pengadaan.kebutuhan') ? 'active' : '' }}">
                            <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
                            Kebutuhan Obat Kronis
                        </a>
                    </div>
                </div>

                {{-- Inventori dropdown --}}
                <div class="nav-group" style="position:relative;">
                    <button onclick="navToggle('dd-inventori',this)" class="nav-tab {{ $inInventori ? 'active' : '' }}" style="background:none;border:none;cursor:pointer;" id="btn-inventori">
                        <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 7V5a2 2 0 00-2-2h-4a2 2 0 00-2 2v2"/><line x1="12" y1="12" x2="12" y2="16"/><line x1="10" y1="14" x2="14" y2="14"/></svg>
                        Inventori
                        <svg class="nav-chevron" width="11" height="11" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" style="transition:transform .15s;"><polyline points="6 9 12 15 18 9"/></svg>
                    </button>
                    <div class="nav-dropdown" id="dd-inventori" style="display:none;">
                        <a href="{{ route('katalog.index') }}" class="{{ request()->routeIs('katalog.index') ? 'active' : '' }}">
                            <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M4 19.5A2.5 2.5 0 016.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 014 19.5v-15A2.5 2.5 0 016.5 2z"/></svg>
                            Katalog Obat
                        </a>
                        <a href="{{ route('katalog.gabung') }}" class="{{ request()->routeIs('katalog.gabung') ? 'active' : '' }}">
                            <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="18" cy="18" r="3"/><circle cx="6" cy="6" r="3"/><path d="M6 21V9a9 9 0 0 0 9 9"/></svg>
                            Gabung Obat
                        </a>
                        <a href="{{ route('stok.index') }}" class="{{ request()->routeIs('stok.index') ? 'active' : '' }}">
                            <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/></svg>
                            Stok Obat
                        </a>
                        <a href="{{ route('bmhp.index') }}" class="{{ request()->routeIs('bmhp.*') ? 'active' : '' }}">
                            <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0016.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 002 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"/></svg>
                            BMHP
                        </a>
                        <a href="{{ route('stok-keluar.index') }}" class="{{ request()->routeIs('stok-keluar.*') ? 'active' : '' }}">
                            <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M5 12h14m-7-7 7 7-7 7"/></svg>
                            Stok Keluar
                        </a>
                        <div class="sep"></div>
                        <a href="{{ route('distributor.index') }}" class="{{ request()->routeIs('distributor.*') ? 'active' : '' }}">
                            <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="1" y="3" width="15" height="13" rx="1"/><path d="M16 8h4l3 3v5h-7V8z"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>
                            Distributor
                        </a>
                    </div>
                </div>

                {{-- Keuangan dropdown --}}
                <div class="nav-group" style="position:relative;">
                    <button onclick="navToggle('dd-keuangan',this)" class="nav-tab {{ $inKeuangan ? 'active' : '' }}" style="background:none;border:none;cursor:pointer;" id="btn-keuangan">
                        <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6"/></svg>
                        Keuangan
                        <svg class="nav-chevron" width="11" height="11" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" style="transition:transform .15s;"><polyline points="6 9 12 15 18 9"/></svg>
                    </button>
                    <div class="nav-dropdown" id="dd-keuangan" style="display:none;">
                        <a href="{{ route('tagihan.index') }}" class="{{ request()->routeIs('tagihan.*') ? 'active' : '' }}">
                            <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="2" y="5" width="20" height="14" rx="2"/><line x1="2" y1="10" x2="22" y2="10"/></svg>
                            Tagihan
                        </a>
                        <a href="{{ route('laporan.index') }}" class="{{ request()->routeIs('laporan.*') ? 'active' : '' }}">
                            <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
                            Laporan Bulanan
                        </a>
                        <a href="{{ route('rekonsiliasi.index') }}" class="{{ request()->routeIs('rekonsiliasi.*') ? 'active' : '' }}">
                            <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="17 1 21 5 17 9"/><path d="M3 11V9a4 4 0 014-4h14"/><polyline points="7 23 3 19 7 15"/><path d="M21 13v2a4 4 0 01-4 4H3"/></svg>
                            Rekonsiliasi BPJS
                        </a>
                        <div class="sep"></div>
                        <a href="{{ route('kalkulator.index') }}" class="{{ request()->routeIs('kalkulator.*') ? 'active' : '' }}" style="{{ request()->routeIs('kalkulator.*') ? '' : 'color:var(--gold);' }}">
                            <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6"/></svg>
                            Kalkulator Laba
                        </a>
                    </div>
                </div>

                {{-- Pasien dropdown (top-level) --}}
                <div class="nav-group" style="position:relative;">
                    <button onclick="navToggle('dd-pasien',this)" class="nav-tab {{ $inPasien ? 'active' : '' }}" style="background:none;border:none;cursor:pointer;" id="btn-pasien">
                        <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87"/><path d="M16 3.13a4 4 0 010 7.75"/></svg>
                        Pasien
                        <svg class="nav-chevron" width="11" height="11" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" style="transition:transform .15s;"><polyline points="6 9 12 15 18 9"/></svg>
                    </button>
                    <div class="nav-dropdown" id="dd-pasien" style="display:none;">
                        <a href="{{ route('pasien.index') }}" class="{{ request()->routeIs('pasien.index') ? 'active' : '' }}">
                            <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                            Daftar Pasien
                        </a>
                        <a href="{{ route('pasien.pengambilan') }}" class="{{ request()->routeIs('pasien.pengambilan') ? 'active' : '' }}">
                            <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 12h6m-3-3v6m-7 4h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                            Pengambilan Obat
                        </a>
                        <a href="{{ route('pasien.riwayat') }}" class="{{ request()->routeIs('pasien.riwayat') ? 'active' : '' }}">
                            <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 8v4l3 3"/><circle cx="12" cy="12" r="9"/></svg>
                            Riwayat Pengambilan
                        </a>
                        <a href="{{ route('pasien.jadwal') }}" class="{{ request()->routeIs('pasien.jadwal') ? 'active' : '' }}">
                            <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                            Jadwal & Reminder
                        </a>
                    </div>
                </div>

                {{-- Manajemen dropdown --}}
                <div class="nav-group" style="position:relative;">
                    <button onclick="navToggle('dd-manajemen',this)" class="nav-tab {{ $inManajemen ? 'active' : '' }}" style="background:none;border:none;cursor:pointer;" id="btn-manajemen">
                        <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 00.33 1.82l.06.06a2 2 0 010 2.83 2 2 0 01-2.83 0l-.06-.06a1.65 1.65 0 00-1.82-.33 1.65 1.65 0 00-1 1.51V21a2 2 0 01-4 0v-.09A1.65 1.65 0 009 19.4a1.65 1.65 0 00-1.82.33l-.06.06a2 2 0 01-2.83-2.83l.06-.06A1.65 1.65 0 004.68 15a1.65 1.65 0 00-1.51-1H3a2 2 0 010-4h.09A1.65 1.65 0 004.6 9a1.65 1.65 0 00-.33-1.82l-.06-.06a2 2 0 012.83-2.83l.06.06A1.65 1.65 0 009 4.68a1.65 1.65 0 001-1.51V3a2 2 0 014 0v.09a1.65 1.65 0 001 1.51 1.65 1.65 0 001.82-.33l.06-.06a2 2 0 012.83 2.83l-.06.06A1.65 1.65 0 0019.4 9a1.65 1.65 0 001.51 1H21a2 2 0 010 4h-.09a1.65 1.65 0 00-1.51 1z"/></svg>
                        Manajemen
                        <svg class="nav-chevron" width="11" height="11" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" style="transition:transform .15s;"><polyline points="6 9 12 15 18 9"/></svg>
                    </button>
                    <div class="nav-dropdown" id="dd-manajemen" style="display:none;">
                        <a href="{{ route('notifikasi.index') }}" class="{{ request()->routeIs('notifikasi.*') ? 'active' : '' }}" style="position:relative;">
                            <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M18 8A6 6 0 006 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 01-3.46 0"/></svg>
                            Notifikasi
                            @php $notifBadge = \App\Models\PengambilanObat::where('jadwal_berikutnya', today()->toDateString())->whereNotIn('status', ['selesai','batal'])->count() + \App\Models\PengambilanObat::where('jadwal_berikutnya', '<', today()->toDateString())->whereNotIn('status', ['selesai','batal'])->whereNotNull('jadwal_berikutnya')->count(); @endphp
                            @if($notifBadge > 0)<span style="position:absolute;top:4px;right:-2px;background:var(--red);color:#fff;border-radius:50%;width:14px;height:14px;font-size:.6rem;font-weight:700;display:flex;align-items:center;justify-content:center;">{{ min($notifBadge, 9) }}</span>@endif
                        </a>
                        <a href="{{ route('persyaratan-klaim.index') }}" class="{{ request()->routeIs('persyaratan-klaim.*') ? 'active' : '' }}">
                            <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2h11"/></svg>
                            Persyaratan Klaim
                        </a>
                        <a href="{{ route('audit.index') }}" class="{{ request()->routeIs('audit.*') ? 'active' : '' }}">
                            <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
                            Audit Log
                        </a>
                        <div class="sep"></div>
                        <a href="{{ route('pengaturan.index') }}" class="{{ request()->routeIs('pengaturan.*') ? 'active' : '' }}">
                            <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 00.33 1.82l.06.06a2 2 0 010 2.83 2 2 0 01-2.83 0l-.06-.06a1.65 1.65 0 00-1.82-.33 1.65 1.65 0 00-1 1.51V21a2 2 0 01-4 0v-.09A1.65 1.65 0 009 19.4a1.65 1.65 0 00-1.82.33l-.06.06a2 2 0 01-2.83-2.83l.06-.06A1.65 1.65 0 004.68 15a1.65 1.65 0 00-1.51-1H3a2 2 0 010-4h.09A1.65 1.65 0 004.6 9a1.65 1.65 0 00-.33-1.82l-.06-.06a2 2 0 012.83-2.83l.06.06A1.65 1.65 0 009 4.68a1.65 1.65 0 001-1.51V3a2 2 0 014 0v.09a1.65 1.65 0 001 1.51 1.65 1.65 0 001.82-.33l.06-.06a2 2 0 012.83 2.83l-.06.06A1.65 1.65 0 0019.4 9a1.65 1.65 0 001.51 1H21a2 2 0 010 4h-.09a1.65 1.65 0 00-1.51 1z"/></svg>
                            Pengaturan
                        </a>
                        <a href="{{ route('deploy.index') }}" class="{{ request()->routeIs('deploy.*') ? 'active' : '' }}" style="color:var(--gold);">
                            <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 2L2 7l10 5 10-5-10-5z"/><path d="M2 17l10 5 10-5"/><path d="M2 12l10 5 10-5"/></svg>
                            Deploy Panel
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <main style="max-width:1400px; margin:0 auto; padding:2rem 1.5rem; position:relative;">
        @if(session('success'))
        <div class="toast toast-success" style="margin-bottom:1rem; max-width:100%; position:relative; animation:none;">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
            {{ session('success') }}
        </div>
        @endif
        {{ $slot }}
    </main>

    <footer style="text-align:center;padding:1.5rem;border-top:1px solid var(--line);margin-top:2rem;font-size:.72rem;color:var(--mut2);">
        Manajemen Obat Klinik Dokterku &nbsp;·&nbsp; <span style="color:var(--mut);">by dr Yaya Mulyana, M.Kes</span>
    </footer>

    <div id="toast-container"></div>

    @livewireScripts
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>
    @stack('scripts')
    <script>
        // ── Service Worker Registration (PWA) ──
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/sw.js', { scope: '/' })
                    .then(reg => {
                        console.log('[PWA] SW registered, scope:', reg.scope);
                        // Check for updates every 60 min
                        setInterval(() => reg.update(), 3600000);
                    })
                    .catch(err => console.warn('[PWA] SW registration failed:', err));
            });
        }

        // ── PWA Install Prompt ──
        let deferredPrompt = null;
        window.addEventListener('beforeinstallprompt', (e) => {
            e.preventDefault();
            deferredPrompt = e;
            // Show install banner after 3s if not dismissed
            setTimeout(() => {
                if (deferredPrompt && !localStorage.getItem('pwa-dismissed')) {
                    showPwaBanner();
                }
            }, 3000);
        });

        function showPwaBanner() {
            if (document.getElementById('pwa-banner')) return;
            const banner = document.createElement('div');
            banner.id = 'pwa-banner';
            banner.innerHTML = `
                <div style="position:fixed;bottom:1rem;left:50%;transform:translateX(-50%);
                            background:#11241c;border:1px solid rgba(217,164,65,.35);
                            border-radius:.85rem;padding:.85rem 1.1rem;
                            display:flex;align-items:center;gap:.75rem;
                            box-shadow:0 8px 32px rgba(0,0,0,.5);
                            z-index:500;max-width:360px;width:calc(100% - 2rem);
                            animation:slideUp .3s ease;">
                    <div style="width:40px;height:40px;border-radius:.5rem;
                                background:rgba(217,164,65,.15);border:1px solid rgba(217,164,65,.3);
                                display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                        <svg width="20" height="20" fill="none" stroke="#d9a441" stroke-width="2" viewBox="0 0 24 24">
                            <path d="M12 2L2 7l10 5 10-5-10-5z"/><path d="M2 17l10 5 10-5"/><path d="M2 12l10 5 10-5"/>
                        </svg>
                    </div>
                    <div style="flex:1;min-width:0;">
                        <div style="font-size:.8rem;font-weight:700;color:#eaf3ee;margin-bottom:.12rem;">Install Aplikasi</div>
                        <div style="font-size:.68rem;color:#8fae9f;line-height:1.3;">Pasang di home screen untuk akses cepat</div>
                    </div>
                    <div style="display:flex;gap:.4rem;flex-shrink:0;">
                        <button onclick="installPwa()" style="background:linear-gradient(135deg,#d9a441,#c4892e);color:#1a0e00;
                                border:none;padding:.4rem .85rem;border-radius:.45rem;font-weight:700;font-size:.75rem;cursor:pointer;">
                            Install
                        </button>
                        <button onclick="dismissPwa()" style="background:rgba(255,255,255,.07);color:#8fae9f;
                                border:1px solid rgba(255,255,255,.1);padding:.4rem .6rem;border-radius:.45rem;font-size:.75rem;cursor:pointer;">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" style="display:inline-block;vertical-align:middle"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                        </button>
                    </div>
                </div>
            `;
            document.body.appendChild(banner);
        }

        function installPwa() {
            if (!deferredPrompt) return;
            deferredPrompt.prompt();
            deferredPrompt.userChoice.then(choice => {
                if (choice.outcome === 'accepted') {
                    document.getElementById('pwa-banner')?.remove();
                }
                deferredPrompt = null;
            });
        }

        function dismissPwa() {
            document.getElementById('pwa-banner')?.remove();
            localStorage.setItem('pwa-dismissed', '1');
        }

        window.addEventListener('appinstalled', () => {
            document.getElementById('pwa-banner')?.remove();
            deferredPrompt = null;
        });

        function navToggle(id, btn) {
            const dd = document.getElementById(id);
            const allDDs = document.querySelectorAll('.nav-dropdown');
            const allChevrons = document.querySelectorAll('#nav-tabs .nav-chevron');
            const isOpen = dd.style.display !== 'none';
            allDDs.forEach(d => d.style.display = 'none');
            allChevrons.forEach(c => c.style.transform = '');
            if (!isOpen) {
                dd.style.display = 'block';
                dd.style.animation = 'ddFadeIn .12s ease';
                btn.querySelector('.nav-chevron').style.transform = 'rotate(180deg)';
            }
        }
        document.addEventListener('click', (e) => {
            if (!e.target.closest('.nav-group')) {
                document.querySelectorAll('.nav-dropdown').forEach(d => d.style.display = 'none');
                document.querySelectorAll('#nav-tabs .nav-chevron').forEach(c => c.style.transform = '');
            }
        });

        window.addEventListener('toast', (e) => {
            const d = e.detail[0] ?? e.detail;
            const { message, type = 'success' } = d;
            const el = document.createElement('div');
            el.className = `toast toast-${type}`;
            el.innerHTML = (type==='success'
                ? '<svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>'
                : '<svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>')
                + `<span>${message}</span>`;
            document.getElementById('toast-container').appendChild(el);
            setTimeout(() => el.remove(), 3500);
        });
    </script>
</body>
</html>
