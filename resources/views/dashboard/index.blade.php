<x-app-layout>
    <x-slot name="title">Dashboard</x-slot>

    {{-- TOP BAR: ALERT BANNER + QUICK ACTIONS --}}
    @php $a = $alerts ?? []; $hasAlert = ($a['rugi']??0)>0||($a['stok_habis']??0)>0||($a['stok_kritis']??0)>0||($a['kadaluarsa']??0)>0; @endphp
    <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:1rem;flex-wrap:wrap;margin-bottom:1.5rem;">

        {{-- Alert pills --}}
        <div style="display:flex;gap:.6rem;flex-wrap:wrap;align-items:center;">
            @if($hasAlert)
            @if(($a['stok_habis']??0)>0)
            <a href="{{ route('stok.index') }}" style="display:flex;align-items:center;gap:.45rem;padding:.55rem 1rem;background:rgba(232,100,90,.1);border:1px solid rgba(232,100,90,.3);border-left:3px solid var(--red);border-radius:.6rem;text-decoration:none;transition:background .15s;" onmouseover="this.style.background='rgba(232,100,90,.16)'" onmouseout="this.style.background='rgba(232,100,90,.1)'">
                <svg width="13" height="13" fill="none" stroke="var(--red2)" stroke-width="2.5" viewBox="0 0 24 24" style="animation:pulse-red 1.5s infinite;"><path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                <span style="font-size:.78rem;color:var(--red2);font-weight:700;">{{ $a['stok_habis'] }} stok habis</span>
            </a>
            @endif
            @if(($a['stok_kritis']??0)>0)
            <a href="{{ route('stok.index') }}" style="display:flex;align-items:center;gap:.45rem;padding:.55rem 1rem;background:rgba(217,164,65,.1);border:1px solid rgba(217,164,65,.28);border-left:3px solid var(--gold);border-radius:.6rem;text-decoration:none;transition:background .15s;" onmouseover="this.style.background='rgba(217,164,65,.16)'" onmouseout="this.style.background='rgba(217,164,65,.1)'">
                <svg width="13" height="13" fill="none" stroke="var(--gold2)" stroke-width="2.5" viewBox="0 0 24 24"><path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>
                <span style="font-size:.78rem;color:var(--gold2);font-weight:700;">{{ $a['stok_kritis'] }} stok kritis</span>
            </a>
            @endif
            @if(($a['rugi']??0)>0)
            <a href="{{ route('katalog.index') }}" style="display:flex;align-items:center;gap:.45rem;padding:.55rem 1rem;background:rgba(232,100,90,.07);border:1px solid rgba(232,100,90,.2);border-left:3px solid rgba(232,100,90,.5);border-radius:.6rem;text-decoration:none;transition:background .15s;" onmouseover="this.style.background='rgba(232,100,90,.12)'" onmouseout="this.style.background='rgba(232,100,90,.07)'">
                <svg width="13" height="13" fill="none" stroke="var(--red2)" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                <span style="font-size:.78rem;color:var(--red2);font-weight:700;">{{ $a['rugi'] }} obat rugi</span>
            </a>
            @endif
            @if(($a['kadaluarsa']??0)>0)
            <a href="{{ route('stok.index') }}" style="display:flex;align-items:center;gap:.45rem;padding:.55rem 1rem;background:rgba(111,177,224,.07);border:1px solid rgba(111,177,224,.2);border-left:3px solid rgba(111,177,224,.5);border-radius:.6rem;text-decoration:none;transition:background .15s;" onmouseover="this.style.background='rgba(111,177,224,.12)'" onmouseout="this.style.background='rgba(111,177,224,.07)'">
                <svg width="13" height="13" fill="none" stroke="var(--blue)" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                <span style="font-size:.78rem;color:var(--blue);font-weight:700;">{{ $a['kadaluarsa'] }} segera kadaluarsa</span>
            </a>
            @endif
            @else
            <div style="display:flex;align-items:center;gap:.4rem;font-size:.78rem;color:var(--emer2);">
                <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
                Semua stok & keuangan dalam kondisi baik
            </div>
            @endif
        </div>

        {{-- Quick Actions --}}
        <div style="display:flex;align-items:center;gap:.5rem;flex-shrink:0;">
            <span style="font-size:.67rem;color:var(--mut2);text-transform:uppercase;letter-spacing:.06em;">Aksi Cepat:</span>
            <a href="{{ route('katalog.index') }}"
                style="display:flex;align-items:center;gap:.35rem;padding:.42rem .8rem;background:transparent;border:1px solid var(--line2);color:var(--mut);border-radius:.45rem;font-size:.75rem;font-weight:600;text-decoration:none;transition:all .15s;"
                onmouseover="this.style.color='var(--ink)';this.style.borderColor='var(--line)'" onmouseout="this.style.color='var(--mut)';this.style.borderColor='var(--line2)'">
                <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                Katalog
            </a>
            <livewire:import-obat />
        </div>
    </div>
    <style>@keyframes pulse-red{0%,100%{opacity:1}50%{opacity:.4}}</style>

    {{-- KPI CARDS --}}
    @php
        // Formatter ringkas Rupiah (rb / jt / M) untuk sub-label
        $fmtRp = function ($n) {
            $n = (float) $n; $a = abs($n); $sign = $n < 0 ? '-' : '';
            if ($a >= 1e9) return $sign.'Rp '.number_format($a/1e9, $a >= 1e10 ? 0 : 1, ',', '.').' M';
            if ($a >= 1e6) return $sign.'Rp '.number_format($a/1e6, $a >= 1e7 ? 0 : 1, ',', '.').' jt';
            if ($a >= 1e3) return $sign.'Rp '.number_format(round($a/1e3), 0, ',', '.').' rb';
            return $sign.'Rp '.number_format($a, 0, ',', '.');
        };
        // Meter realisasi vs proyeksi
        $splitMeter = function ($real, $proj) {
            $real = (float) $real; $proj = (float) $proj;
            $tot  = abs($real) + abs($proj);
            return $tot > 0 ? round(abs($real) / $tot * 100) : ($real != 0 ? 100 : 0);
        };
        $klaimTot = $klaim_realisasi + $klaim_proyeksi;
        $hppTot   = $hpp_realisasi + $hpp_proyeksi;
    @endphp
    <style>
        .pm-split { margin-top:.6rem; }
        .pm-bar { display:flex; height:5px; border-radius:3px; overflow:hidden; background:var(--line); }
        .pm-bar-real { background:linear-gradient(90deg,var(--emer),var(--emer2)); }
        .pm-bar-proj { background:linear-gradient(90deg,var(--gold),var(--gold2)); }
        .pm-legend { display:flex; align-items:center; gap:.7rem; margin-top:.4rem; font-size:.68rem; flex-wrap:wrap; }
        .pm-leg { display:inline-flex; align-items:center; gap:.3rem; color:var(--mut); }
        .pm-dot { width:7px; height:7px; border-radius:2px; flex-shrink:0; }
        .pm-val { font-family:'JetBrains Mono',monospace; font-weight:600; }
    </style>
    <div style="display:grid; grid-template-columns:repeat(auto-fit,minmax(220px,1fr)); gap:1rem; margin-bottom:2rem;">

        <div class="kpi-card">
            <div class="font-label" style="font-size:.68rem; color:var(--mut); margin-bottom:.5rem;">Laba Kotor Bulan Ini</div>
            <div class="font-mono" style="font-size:1.5rem; font-weight:700; color:{{ $laba_kotor >= 0 ? 'var(--emer2)' : 'var(--red2)' }};">
                {{ $laba_kotor >= 0 ? '+' : '' }}Rp {{ number_format($laba_kotor,0,',','.') }}
            </div>
            <div style="margin-top:.4rem; font-size:.75rem; color:var(--mut);">
                Margin: <span style="color:var(--gold2);">{{ $pendapatan_bpjs > 0 ? number_format(($laba_kotor/$pendapatan_bpjs)*100,1) : 0 }}%</span>
                · klaim − HPP
            </div>
            @if($klaimTot != 0 || $hppTot != 0)
            <div class="pm-split">
                @php $lr = $laba_realisasi; $lp = $laba_proyeksi; $lpct = $splitMeter($lr, $lp); @endphp
                <div class="pm-bar"><div class="pm-bar-real" style="width:{{ $lpct }}%"></div><div class="pm-bar-proj" style="width:{{ 100-$lpct }}%"></div></div>
                <div class="pm-legend">
                    <span class="pm-leg"><span class="pm-dot" style="background:var(--emer2);"></span>Realisasi <span class="pm-val" style="color:var(--emer2);">{{ $fmtRp($lr) }}</span></span>
                    <span class="pm-leg"><span class="pm-dot" style="background:var(--gold2);"></span>Proyeksi <span class="pm-val" style="color:var(--gold2);">{{ $fmtRp($lp) }}</span></span>
                </div>
            </div>
            @else
            <div style="margin-top:.5rem; font-size:.68rem; color:var(--mut2);">Belum ada pengambilan bulan ini</div>
            @endif
        </div>

        <div class="kpi-card">
            <div class="font-label" style="font-size:.68rem; color:var(--mut); margin-bottom:.5rem;">Proyeksi Klaim BPJS</div>
            <div class="font-mono" style="font-size:1.5rem; font-weight:700; color:var(--ink);">
                Rp {{ number_format($pendapatan_bpjs,0,',','.') }}
            </div>
            <div style="margin-top:.4rem; font-size:.75rem; color:var(--mut);">Obat kronis diserahkan ke pasien</div>
            @if($klaimTot != 0)
            <div class="pm-split">
                @php $kpct = $splitMeter($klaim_realisasi, $klaim_proyeksi); @endphp
                <div class="pm-bar"><div class="pm-bar-real" style="width:{{ $kpct }}%"></div><div class="pm-bar-proj" style="width:{{ 100-$kpct }}%"></div></div>
                <div class="pm-legend">
                    <span class="pm-leg"><span class="pm-dot" style="background:var(--emer2);"></span>Realisasi <span class="pm-val" style="color:var(--emer2);">{{ $fmtRp($klaim_realisasi) }}</span></span>
                    <span class="pm-leg"><span class="pm-dot" style="background:var(--gold2);"></span>Proyeksi <span class="pm-val" style="color:var(--gold2);">{{ $fmtRp($klaim_proyeksi) }}</span></span>
                </div>
            </div>
            @else
            <div style="margin-top:.5rem; font-size:.68rem; color:var(--mut2);">Belum ada klaim bulan ini</div>
            @endif
        </div>

        <div class="kpi-card">
            <div class="font-label" style="font-size:.68rem; color:var(--mut); margin-bottom:.5rem;">HPP Obat Bulan Ini</div>
            <div class="font-mono" style="font-size:1.5rem; font-weight:700; color:var(--ink);">
                Rp {{ number_format($biaya_beli,0,',','.') }}
            </div>
            <div style="margin-top:.4rem; font-size:.75rem; color:var(--mut);">Harga beli saat penyerahan</div>
            @if($hppTot != 0)
            <div class="pm-split">
                @php $hpct = $splitMeter($hpp_realisasi, $hpp_proyeksi); @endphp
                <div class="pm-bar"><div class="pm-bar-real" style="width:{{ $hpct }}%"></div><div class="pm-bar-proj" style="width:{{ 100-$hpct }}%"></div></div>
                <div class="pm-legend">
                    <span class="pm-leg"><span class="pm-dot" style="background:var(--emer2);"></span>Realisasi <span class="pm-val" style="color:var(--emer2);">{{ $fmtRp($hpp_realisasi) }}</span></span>
                    <span class="pm-leg"><span class="pm-dot" style="background:var(--gold2);"></span>Proyeksi <span class="pm-val" style="color:var(--gold2);">{{ $fmtRp($hpp_proyeksi) }}</span></span>
                </div>
            </div>
            @else
            <div style="margin-top:.5rem; font-size:.68rem; color:var(--mut2);">Belum ada HPP bulan ini</div>
            @endif
        </div>

        <div class="kpi-card">
            <div class="font-label" style="font-size:.68rem; color:var(--mut); margin-bottom:.5rem;">Pasien Kronis Aktif</div>
            <div class="font-mono" style="font-size:1.5rem; font-weight:700; color:var(--ink);">
                {{ number_format($total_pasien,0,',','.') }}
            </div>
            <div style="margin-top:.4rem; font-size:.75rem; color:var(--mut);">
                {{ $pasien_bulan_ini ?? 0 }} ambil obat bulan ini
            </div>
        </div>

        <div class="kpi-card">
            <div class="font-label" style="font-size:.68rem; color:var(--mut); margin-bottom:.5rem;">Pengeluaran Bulan Ini</div>
            <div class="font-mono" style="font-size:1.5rem; font-weight:700; color:var(--ink);">
                Rp {{ number_format($pengeluaran_bulan_ini,0,',','.') }}
            </div>
            <div style="margin-top:.4rem; font-size:.75rem; color:var(--mut);">{{ $jumlah_po_bulan_ini }} PO bulan ini</div>
        </div>

    </div>

    {{-- ══ PENGADAAN DISETUJUI — SIAP DIBELANJAKAN (call-to-action) ══ --}}
    @if(($siap_belanja['count'] ?? 0) > 0)
    <a href="{{ route('pengadaan.pengajuan') }}" style="display:flex;align-items:center;gap:1rem;flex-wrap:wrap;text-decoration:none;padding:.9rem 1.3rem;margin-bottom:1.5rem;border-radius:1rem;background:linear-gradient(100deg,rgba(217,164,65,.16),rgba(217,164,65,.04));border:1px solid rgba(217,164,65,.45);box-shadow:0 0 22px rgba(217,164,65,.14);">
        <div style="width:42px;height:42px;border-radius:12px;background:linear-gradient(180deg,#f2c668,#d9a441);display:flex;align-items:center;justify-content:center;flex-shrink:0;font-size:1.2rem;box-shadow:0 0 12px rgba(217,164,65,.5);">🛒</div>
        <div style="flex:1;min-width:200px;">
            <div class="font-heading" style="font-size:1rem;color:var(--gold2);font-weight:800;">{{ $siap_belanja['count'] }} pengadaan disetujui — menunggu dibelanjakan</div>
            <div style="font-size:.78rem;color:var(--mut);margin-top:.15rem;">Total <strong class="font-mono" style="color:var(--ink);">Rp {{ number_format($siap_belanja['nilai'],0,',','.') }}</strong> · sudah di-ACC manajer SIM, klik <strong style="color:var(--gold2);">🛒 Belanja (PO)</strong> untuk realisasikan jadi barang masuk.</div>
        </div>
        <span style="display:inline-flex;align-items:center;gap:.4rem;padding:.55rem 1.1rem;border-radius:.7rem;background:linear-gradient(180deg,#f2c668,#d9a441);color:#1a0e00;font-weight:800;font-size:.82rem;white-space:nowrap;">Belanja sekarang →</span>
    </a>
    @endif

    {{-- ══════════════ RINCIAN LABA PER OBAT (pembukti kartu) ══════════════ --}}
    <style>
        .ob-wrap { margin-bottom:1.5rem; }
        .ob-head { display:flex; align-items:flex-start; justify-content:space-between; gap:1rem; padding:1.1rem 1.3rem .9rem; flex-wrap:wrap; }
        .ob-title { font-size:1.05rem; color:var(--ink); margin:0; display:flex; align-items:center; gap:.5rem; }
        .ob-sub { font-size:.73rem; color:var(--mut); margin:.25rem 0 0; }
        .ob-recon { display:inline-flex; align-items:center; gap:.35rem; font-size:.68rem; font-weight:600; padding:.22rem .6rem; border-radius:999px; background:rgba(63,207,142,.12); color:var(--emer2); border:1px solid rgba(63,207,142,.25); }
        .ob-recon.off { background:rgba(217,164,65,.12); color:var(--gold2); border-color:rgba(217,164,65,.25); }
        .ob-filters { display:flex; gap:.3rem; background:rgba(0,0,0,.18); border:1px solid var(--line); border-radius:.6rem; padding:.25rem; }
        .ob-fbtn { font-size:.73rem; font-weight:600; color:var(--mut); background:none; border:none; padding:.35rem .8rem; border-radius:.45rem; cursor:pointer; transition:.15s; white-space:nowrap; }
        .ob-fbtn:hover { color:var(--ink); }
        .ob-fbtn.active { background:var(--line2); color:var(--ink); }
        .ob-tablewrap { overflow-x:auto; }
        .ob-table { width:100%; border-collapse:collapse; min-width:640px; }
        .ob-table th { position:sticky; top:0; background:var(--panel); color:var(--mut); font-size:.66rem; text-transform:uppercase; letter-spacing:.06em; font-weight:700; padding:.6rem 1rem; text-align:left; border-bottom:1px solid var(--line); white-space:nowrap; cursor:pointer; user-select:none; }
        .ob-table th.num { text-align:right; }
        .ob-table th:hover { color:var(--ink); }
        .ob-caret { font-size:.6rem; color:var(--gold2); margin-left:.2rem; }
        .ob-table td { padding:.6rem 1rem; border-bottom:1px solid rgba(31,61,48,.45); font-size:.82rem; vertical-align:middle; }
        .ob-table td.num { text-align:right; font-family:'JetBrains Mono',monospace; font-variant-numeric:tabular-nums; }
        .ob-table tr:hover td { background:rgba(255,255,255,.02); }
        .ob-rank { display:inline-flex; align-items:center; justify-content:center; width:20px; height:20px; border-radius:5px; background:var(--line); color:var(--mut); font-size:.65rem; font-weight:700; margin-right:.55rem; flex-shrink:0; }
        .ob-name { display:flex; align-items:center; }
        .ob-nm { font-weight:600; color:var(--ink); }
        .ob-tipe { font-size:.6rem; font-weight:700; padding:.08rem .4rem; border-radius:4px; margin-left:.5rem; text-transform:uppercase; letter-spacing:.03em; }
        .ob-tipe.kronis { background:rgba(111,177,224,.14); color:var(--blue); }
        .ob-tipe.non { background:rgba(143,174,159,.12); color:var(--mut); }
        .ob-stat { font-size:.62rem; font-weight:700; padding:.12rem .5rem; border-radius:999px; }
        .ob-stat.real { background:rgba(63,207,142,.14); color:var(--emer2); }
        .ob-stat.proj { background:rgba(217,164,65,.14); color:var(--gold2); }
        .ob-laba-pos { color:var(--emer2); font-weight:700; }
        .ob-laba-neg { color:var(--red2); font-weight:700; }
        .ob-foot td { padding:.7rem 1rem; border-top:2px solid var(--line2); font-weight:700; font-size:.82rem; background:rgba(0,0,0,.15); }
        .ob-foot .num { font-family:'JetBrains Mono',monospace; }
        .ob-empty { text-align:center; padding:2rem; color:var(--mut2); font-size:.82rem; }
        .ob-src { font-size:.58rem; color:var(--mut2); margin-left:.4rem; }
    </style>
    <div class="glass-card ob-wrap" x-data="obatBreakdown(@js($breakdown_rows), {{ (int) $pendapatan_bpjs }}, {{ (int) $biaya_beli }})">
        <div class="ob-head">
            <div>
                <h3 class="font-heading ob-title">
                    <svg width="17" height="17" fill="none" stroke="var(--gold2)" stroke-width="1.8" viewBox="0 0 24 24"><path d="M3 3v18h18"/><path d="M7 14l3-3 3 3 5-5"/></svg>
                    Rincian Laba per Obat — Bulan Ini
                </h3>
                <p class="ob-sub">
                    Pembukti angka kartu di atas · <span x-text="filtered.length"></span> obat ·
                    <span class="ob-recon" :class="{ 'off': !reconciled }">
                        <template x-if="reconciled"><span><svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round" style="display:inline-block;vertical-align:middle"><polyline points="20 6 9 17 4 12"/></svg> Cocok dengan kartu</span></template>
                        <template x-if="!reconciled"><span x-text="'Σ Laba ' + rp(totals.laba)"></span></template>
                    </span>
                </p>
            </div>
            <div class="ob-filters">
                <button class="ob-fbtn" :class="{ active: filter==='all' }" @click="filter='all'">Semua</button>
                <button class="ob-fbtn" :class="{ active: filter==='realisasi' }" @click="filter='realisasi'">Realisasi</button>
                <button class="ob-fbtn" :class="{ active: filter==='proyeksi' }" @click="filter='proyeksi'">Proyeksi</button>
            </div>
        </div>
        <div class="ob-tablewrap">
            <table class="ob-table">
                <thead>
                    <tr>
                        <th @click="sort('nama')">Obat <span class="ob-caret" x-show="sortCol==='nama'" x-html="sortDir==='asc' ? '<svg width=\'11\' height=\'11\' viewBox=\'0 0 24 24\' fill=\'none\' stroke=\'currentColor\' stroke-width=\'2.5\' stroke-linecap=\'round\' stroke-linejoin=\'round\'><polyline points=\'18 15 12 9 6 15\'/></svg>' : '<svg width=\'11\' height=\'11\' viewBox=\'0 0 24 24\' fill=\'none\' stroke=\'currentColor\' stroke-width=\'2.5\' stroke-linecap=\'round\' stroke-linejoin=\'round\'><polyline points=\'6 9 12 15 18 9\'/></svg>'"></span></th>
                        <th @click="sort('status')">Status <span class="ob-caret" x-show="sortCol==='status'" x-html="sortDir==='asc' ? '<svg width=\'11\' height=\'11\' viewBox=\'0 0 24 24\' fill=\'none\' stroke=\'currentColor\' stroke-width=\'2.5\' stroke-linecap=\'round\' stroke-linejoin=\'round\'><polyline points=\'18 15 12 9 6 15\'/></svg>' : '<svg width=\'11\' height=\'11\' viewBox=\'0 0 24 24\' fill=\'none\' stroke=\'currentColor\' stroke-width=\'2.5\' stroke-linecap=\'round\' stroke-linejoin=\'round\'><polyline points=\'6 9 12 15 18 9\'/></svg>'"></span></th>
                        <th class="num" @click="sort('qty')">Qty <span class="ob-caret" x-show="sortCol==='qty'" x-html="sortDir==='asc' ? '<svg width=\'11\' height=\'11\' viewBox=\'0 0 24 24\' fill=\'none\' stroke=\'currentColor\' stroke-width=\'2.5\' stroke-linecap=\'round\' stroke-linejoin=\'round\'><polyline points=\'18 15 12 9 6 15\'/></svg>' : '<svg width=\'11\' height=\'11\' viewBox=\'0 0 24 24\' fill=\'none\' stroke=\'currentColor\' stroke-width=\'2.5\' stroke-linecap=\'round\' stroke-linejoin=\'round\'><polyline points=\'6 9 12 15 18 9\'/></svg>'"></span></th>
                        <th class="num" @click="sort('hpp')">HPP <span class="ob-caret" x-show="sortCol==='hpp'" x-html="sortDir==='asc' ? '<svg width=\'11\' height=\'11\' viewBox=\'0 0 24 24\' fill=\'none\' stroke=\'currentColor\' stroke-width=\'2.5\' stroke-linecap=\'round\' stroke-linejoin=\'round\'><polyline points=\'18 15 12 9 6 15\'/></svg>' : '<svg width=\'11\' height=\'11\' viewBox=\'0 0 24 24\' fill=\'none\' stroke=\'currentColor\' stroke-width=\'2.5\' stroke-linecap=\'round\' stroke-linejoin=\'round\'><polyline points=\'6 9 12 15 18 9\'/></svg>'"></span></th>
                        <th class="num" @click="sort('klaim')">Klaim BPJS <span class="ob-caret" x-show="sortCol==='klaim'" x-html="sortDir==='asc' ? '<svg width=\'11\' height=\'11\' viewBox=\'0 0 24 24\' fill=\'none\' stroke=\'currentColor\' stroke-width=\'2.5\' stroke-linecap=\'round\' stroke-linejoin=\'round\'><polyline points=\'18 15 12 9 6 15\'/></svg>' : '<svg width=\'11\' height=\'11\' viewBox=\'0 0 24 24\' fill=\'none\' stroke=\'currentColor\' stroke-width=\'2.5\' stroke-linecap=\'round\' stroke-linejoin=\'round\'><polyline points=\'6 9 12 15 18 9\'/></svg>'"></span></th>
                        <th class="num" @click="sort('laba')">Laba <span class="ob-caret" x-show="sortCol==='laba'" x-html="sortDir==='asc' ? '<svg width=\'11\' height=\'11\' viewBox=\'0 0 24 24\' fill=\'none\' stroke=\'currentColor\' stroke-width=\'2.5\' stroke-linecap=\'round\' stroke-linejoin=\'round\'><polyline points=\'18 15 12 9 6 15\'/></svg>' : '<svg width=\'11\' height=\'11\' viewBox=\'0 0 24 24\' fill=\'none\' stroke=\'currentColor\' stroke-width=\'2.5\' stroke-linecap=\'round\' stroke-linejoin=\'round\'><polyline points=\'6 9 12 15 18 9\'/></svg>'"></span></th>
                    </tr>
                </thead>
                <tbody>
                    <template x-for="(row, i) in filtered" :key="row.obat_id + '-' + row.status">
                        <tr>
                            <td>
                                <span class="ob-name">
                                    <span class="ob-rank" x-text="i+1"></span>
                                    <span class="ob-nm" x-text="row.nama"></span>
                                    <span class="ob-tipe" :class="row.tipe==='kronis' ? 'kronis' : 'non'" x-text="row.tipe==='kronis'?'Kronis':'Non'"></span>
                                    <span class="ob-src" x-text="row.source==='item'?'·item':'·resep'"></span>
                                </span>
                            </td>
                            <td>
                                <span class="ob-stat" :class="row.status==='selesai' ? 'real' : 'proj'" x-text="row.status==='selesai'?'Realisasi':'Proyeksi'"></span>
                            </td>
                            <td class="num" x-text="Math.round(row.qty).toLocaleString('id-ID')"></td>
                            <td class="num" x-text="rp(row.hpp)"></td>
                            <td class="num" x-text="rp(row.klaim)"></td>
                            <td class="num" :class="row.laba>=0 ? 'ob-laba-pos' : 'ob-laba-neg'" x-text="(row.laba>=0?'+':'')+rp(row.laba)"></td>
                        </tr>
                    </template>
                    <template x-if="filtered.length===0">
                        <tr><td colspan="6" class="ob-empty">Tidak ada data untuk filter ini.</td></tr>
                    </template>
                </tbody>
                <tfoot>
                    <tr class="ob-foot">
                        <td>TOTAL</td>
                        <td><span x-text="filtered.length + ' baris'"></span></td>
                        <td class="num" x-text="Math.round(totals.qty).toLocaleString('id-ID')"></td>
                        <td class="num" x-text="rp(totals.hpp)"></td>
                        <td class="num" x-text="rp(totals.klaim)"></td>
                        <td class="num" :class="totals.laba>=0 ? 'ob-laba-pos' : 'ob-laba-neg'" x-text="(totals.laba>=0?'+':'')+rp(totals.laba)"></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
    <script>
        function obatBreakdown(rows, cardKlaim, cardHpp) {
            return {
                rows: Array.isArray(rows) ? rows : [],
                filter: 'all',
                sortCol: 'laba', sortDir: 'asc',
                cardKlaim: cardKlaim, cardHpp: cardHpp,
                get filtered() {
                    let r = this.rows;
                    if (this.filter === 'realisasi') r = r.filter(x => x.status === 'selesai');
                    else if (this.filter === 'proyeksi') r = r.filter(x => x.status === 'dijadwalkan');
                    const m = this.sortDir === 'asc' ? 1 : -1;
                    return [...r].sort((a, b) => {
                        let av = a[this.sortCol], bv = b[this.sortCol];
                        if (this.sortCol === 'nama' || this.sortCol === 'status') return String(av).localeCompare(String(bv)) * m;
                        return ((+av) - (+bv)) * m;
                    });
                },
                get totals() {
                    return this.filtered.reduce((t, x) => ({
                        qty: t.qty + (+x.qty), hpp: t.hpp + (+x.hpp),
                        klaim: t.klaim + (+x.klaim), laba: t.laba + (+x.laba)
                    }), { qty: 0, hpp: 0, klaim: 0, laba: 0 });
                },
                get reconciled() {
                    if (this.filter !== 'all') return false;
                    return Math.round(this.totals.hpp) === Math.round(this.cardHpp)
                        && Math.round(this.totals.klaim) === Math.round(this.cardKlaim);
                },
                sort(c) {
                    if (this.sortCol === c) this.sortDir = this.sortDir === 'asc' ? 'desc' : 'asc';
                    else { this.sortCol = c; this.sortDir = (c === 'nama') ? 'asc' : 'desc'; }
                },
                rp(n) {
                    n = Math.round(+n || 0);
                    return (n < 0 ? '-' : '') + 'Rp ' + Math.abs(n).toLocaleString('id-ID');
                },
            }
        }
    </script>

    {{-- BPJS CYCLE KPI STRIP --}}
    @php
    $rb = $rekon_bpjs ?? [];
    $rbStatus = $rb['status'] ?? 'belum_diajukan';
    $rbIsPending = $rb['is_pending'] ?? true;
    $statusLabel = match($rbStatus) {
        'diajukan'  => 'Diajukan',
        'dibayar'   => 'Dibayar',
        'selisih'   => 'Ada Selisih',
        'draft'     => 'Draft',
        default     => 'Belum Diajukan',
    };
    $statusColor = match($rbStatus) {
        'diajukan'  => 'var(--gold2)',
        'dibayar'   => 'var(--emer2)',
        'selisih'   => 'var(--red2)',
        default     => 'var(--mut2)',
    };
    @endphp
    <div style="background:rgba(63,207,142,.04); border:1px solid rgba(63,207,142,.18); border-radius:.9rem; padding:1rem 1.3rem; margin-bottom:1.5rem;">
        <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:.8rem; flex-wrap:wrap; gap:.5rem;">
            <div style="font-size:.7rem; text-transform:uppercase; letter-spacing:.1em; color:var(--emer2); font-weight:700;">Siklus Klaim BPJS — {{ now()->translatedFormat('F Y') }}</div>
            <span style="font-size:.7rem; font-weight:700; color:{{ $statusColor }};">{{ $statusLabel }}</span>
        </div>
        @if($rbIsPending)
        <div style="font-size:.68rem; color:var(--gold2); background:rgba(217,164,65,.08); border:1px solid rgba(217,164,65,.18); border-radius:.5rem; padding:.45rem .7rem; margin-bottom:.8rem;">
            Pembayaran BPJS bulan ini belum masuk — angka proyeksi berdasarkan obat yang diserahkan ke pasien.
            Klaim baru dibayar bulan berikutnya.
        </div>
        @endif
        <div style="display:grid; grid-template-columns:repeat(4,1fr); gap:.7rem;">
            <div style="background:rgba(0,0,0,.2); border-radius:.6rem; padding:.7rem .85rem; border:1px solid var(--line);">
                <div style="font-size:.58rem; text-transform:uppercase; letter-spacing:.07em; color:var(--mut); font-weight:700; margin-bottom:.25rem;">Proyeksi Klaim</div>
                <div class="font-mono" style="font-size:1rem; font-weight:800; color:var(--blue);">Rp {{ number_format($rb['proyeksi'] ?? 0,0,',','.') }}</div>
                <div style="font-size:.6rem; color:var(--mut); margin-top:.15rem;">Dari obat diserahkan</div>
            </div>
            <div style="background:rgba(0,0,0,.2); border-radius:.6rem; padding:.7rem .85rem; border:1px solid var(--line);">
                <div style="font-size:.58rem; text-transform:uppercase; letter-spacing:.07em; color:var(--mut); font-weight:700; margin-bottom:.25rem;">Diajukan</div>
                <div class="font-mono" style="font-size:1rem; font-weight:800; color:var(--gold2);">
                    {{ ($rb['diajukan'] ?? 0) > 0 ? 'Rp '.number_format($rb['diajukan'],0,',','.') : '—' }}
                </div>
                <div style="font-size:.6rem; color:var(--mut); margin-top:.15rem;">Tagihan ke BPJS</div>
            </div>
            <div style="background:rgba(63,207,142,.05); border-radius:.6rem; padding:.7rem .85rem; border:1px solid rgba(63,207,142,.15);">
                <div style="font-size:.58rem; text-transform:uppercase; letter-spacing:.07em; color:var(--emer2); font-weight:700; margin-bottom:.25rem;">Dibayar BPJS</div>
                <div class="font-mono" style="font-size:1rem; font-weight:800; color:var(--emer2);">
                    {{ ($rb['dibayar'] ?? 0) > 0 ? 'Rp '.number_format($rb['dibayar'],0,',','.') : ($rbIsPending ? 'Pending' : '—') }}
                </div>
                <div style="font-size:.6rem; color:var(--mut); margin-top:.15rem;">Revenue aktual</div>
            </div>
            @php $selisih = $rb['selisih'] ?? 0; @endphp
            <div style="background:rgba({{ $selisih >= 0 ? '63,207,142' : '232,100,90' }},.05); border-radius:.6rem; padding:.7rem .85rem; border:1px solid rgba({{ $selisih >= 0 ? '63,207,142' : '232,100,90' }},.15);">
                <div style="font-size:.58rem; text-transform:uppercase; letter-spacing:.07em; color:{{ $selisih >= 0 ? 'var(--emer2)' : 'var(--red2)' }}; font-weight:700; margin-bottom:.25rem;">Selisih</div>
                <div class="font-mono" style="font-size:1rem; font-weight:800; color:{{ $selisih >= 0 ? 'var(--emer2)' : 'var(--red2)' }};">
                    {{ ($rb['diajukan'] ?? 0) > 0 ? (($selisih >= 0 ? '+' : '').number_format($selisih,0,',','.')) : '—' }}
                </div>
                <div style="font-size:.6rem; color:var(--mut); margin-top:.15rem;">Dibayar − Diajukan</div>
            </div>
        </div>
        <div style="margin-top:.75rem; text-align:right;">
            <a href="{{ route('rekonsiliasi.index') }}" style="font-size:.68rem; color:var(--emer2); text-decoration:none; font-weight:600;">Kelola Rekonsiliasi <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" style="display:inline-block;vertical-align:middle"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg></a>
        </div>
    </div>

    {{-- RENCANA PENGAMBILAN OBAT --}}
    <livewire:rencana-ambil-obat />

    {{-- CHARTS ROW --}}
    <div style="display:grid; grid-template-columns:1fr 380px; gap:1.5rem; margin-bottom:2rem;">

        {{-- Bar Chart: Ranking Obat --}}
        <div class="glass-card" style="padding:1.5rem;">
            <div style="margin-bottom:1.2rem;">
                <div class="font-label" style="font-size:.7rem; color:var(--mut); margin-bottom:.2rem;">Kontributor Laba</div>
                <div class="font-heading" style="font-size:1.1rem; color:var(--ink);">Ranking Laba per Obat</div>
            </div>
            <canvas id="chartRanking" style="max-height:380px;"></canvas>
        </div>

        {{-- Donut Chart: per Diagnosis --}}
        <div class="glass-card" style="padding:1.5rem;">
            <div style="margin-bottom:1.2rem;">
                <div class="font-label" style="font-size:.7rem; color:var(--mut); margin-bottom:.2rem;">Distribusi</div>
                <div class="font-heading" style="font-size:1.1rem; color:var(--ink);">Pendapatan per Diagnosis</div>
            </div>
            <div style="position:relative; height:220px; margin-bottom:1rem;">
                <canvas id="chartDiagnosis"></canvas>
                <div style="position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);text-align:center;pointer-events:none;">
                    <div style="font-size:.65rem; color:var(--mut); text-transform:uppercase; letter-spacing:.06em;">Total/bln</div>
                    <div class="font-mono" style="font-size:.9rem; color:var(--ink); font-weight:700;">
                        Rp {{ number_format($pendapatan_bpjs/1000000,1,',','.') }}jt
                    </div>
                </div>
            </div>
            <div id="diagnosisLegend" style="display:flex; flex-direction:column; gap:.4rem;"></div>
        </div>

    </div>

    {{-- TREND CHART 6 BULAN --}}
    <div class="glass-card" style="padding:1.5rem;margin-bottom:2rem;">
        <div style="margin-bottom:1.2rem;">
            <div class="font-label" style="font-size:.7rem;color:var(--mut);margin-bottom:.2rem;">Histori</div>
            <div class="font-heading" style="font-size:1.1rem;color:var(--ink);">Tren Keuangan 6 Bulan Terakhir</div>
        </div>
        <canvas id="chartTren6" style="max-height:250px;"></canvas>
    </div>

    {{-- PROYEKSI LABA BERSIH --}}
    <livewire:proyeksi-slider :laba-kotor="$laba_kotor" />

    @push('scripts')
    <script>
        (function() {
            // Ranking Bar Chart
            const rankingData = @json(array_slice($ranking_obat, 0, 15));
            const labels = rankingData.map(d => d.nama);
            const values = rankingData.map(d => d.laba);
            const colors = rankingData.map(d => d.laba >= 0 ? 'rgba(63,207,142,.7)' : 'rgba(232,100,90,.7)');
            const borderColors = rankingData.map(d => d.laba >= 0 ? '#3fcf8e' : '#e8645a');

            new Chart(document.getElementById('chartRanking'), {
                type: 'bar',
                data: {
                    labels,
                    datasets: [{ data: values, backgroundColor: colors, borderColor: borderColors, borderWidth: 1, borderRadius: 4 }]
                },
                options: {
                    indexAxis: 'y', responsive: true, maintainAspectRatio: false,
                    plugins: { legend: { display: false }, tooltip: { callbacks: { label: ctx => ' Rp ' + new Intl.NumberFormat('id-ID').format(ctx.raw) } } },
                    scales: {
                        x: { grid: { color: 'rgba(31,61,48,.5)' }, ticks: { color: '#8fae9f', font: { size: 11 }, callback: v => 'Rp ' + Intl.NumberFormat('id-ID',{notation:'compact'}).format(v) } },
                        y: { grid: { display: false }, ticks: { color: '#eaf3ee', font: { size: 11 } } }
                    }
                }
            });

            // Donut Chart
            const diagData = @json($by_diagnosis);
            const diagLabels = Object.keys(diagData);
            const diagValues = Object.values(diagData);
            const palette = ['#3fcf8e','#6fb1e0','#d9a441','#e8645a','#a78bfa','#fb923c','#34d399','#f472b6','#94a3b8'];

            new Chart(document.getElementById('chartDiagnosis'), {
                type: 'doughnut',
                data: { labels: diagLabels, datasets: [{ data: diagValues, backgroundColor: palette.map(c => c + '99'), borderColor: palette, borderWidth: 1.5, hoverOffset: 6 }] },
                options: { cutout: '68%', responsive: true, maintainAspectRatio: false,
                    plugins: { legend: { display: false }, tooltip: { callbacks: { label: ctx => ' Rp ' + new Intl.NumberFormat('id-ID').format(ctx.raw) } } } }
            });

            // Legend
            const total = diagValues.reduce((a, b) => a + b, 0);
            const legend = document.getElementById('diagnosisLegend');
            diagLabels.forEach((label, i) => {
                const pct = total > 0 ? ((diagValues[i] / total) * 100).toFixed(1) : 0;
                const row = document.createElement('div');
                row.style.cssText = 'display:flex;align-items:center;justify-content:space-between;font-size:.75rem;';
                row.innerHTML = '<span style="display:flex;align-items:center;gap:.4rem;color:#8fae9f;"><span style="width:8px;height:8px;border-radius:50%;background:'+palette[i]+';flex-shrink:0;"></span>'+label+'</span><span style="color:#eaf3ee;font-weight:600;">'+pct+'%</span>';
                legend.appendChild(row);
            });

            // Tren 6 Bulan Chart
            new Chart(document.getElementById('chartTren6'), {
                type: 'bar',
                data: {
                    labels: @json($tren_labels),
                    datasets: [
                        { label: 'Proyeksi Pendapatan', data: @json($tren_pendapatan), backgroundColor: 'rgba(63,207,142,.55)', borderColor: '#3fcf8e', borderWidth: 1.5, borderRadius: 4 },
                        { label: 'Pengeluaran PO', data: @json($tren_pengeluaran), backgroundColor: 'rgba(232,100,90,.45)', borderColor: '#e8645a', borderWidth: 1.5, borderRadius: 4 },
                    ]
                },
                options: {
                    responsive: true, maintainAspectRatio: false,
                    plugins: { legend: { labels: { color: '#8fae9f', font: { size: 11 } } }, tooltip: { callbacks: { label: ctx => ' '+ctx.dataset.label+': Rp '+new Intl.NumberFormat('id-ID').format(ctx.raw) } } },
                    scales: {
                        x: { ticks: { color: '#8fae9f', font: { size: 10 } }, grid: { color: 'rgba(31,61,48,.4)' } },
                        y: { ticks: { color: '#8fae9f', font: { size: 10 }, callback: v => 'Rp '+Intl.NumberFormat('id').format(v) }, grid: { color: 'rgba(31,61,48,.4)' } }
                    }
                }
            });
        })();
    </script>
    @endpush
</x-app-layout>
