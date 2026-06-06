@php
$sc = [
    'habis'        => ['label'=>'HABIS',       'bg'=>'rgba(232,100,90,.18)', 'color'=>'var(--red2)',  'bdr'=>'rgba(232,100,90,.4)'],
    'kritis'       => ['label'=>'KRITIS',      'bg'=>'rgba(232,100,90,.1)',  'color'=>'var(--red)',   'bdr'=>'rgba(232,100,90,.3)'],
    'hampir_habis' => ['label'=>'HAMPIR HABIS','bg'=>'rgba(255,136,0,.1)',   'color'=>'#ff9944',      'bdr'=>'rgba(255,136,0,.3)'],
    'perhatian'    => ['label'=>'PERHATIAN',   'bg'=>'rgba(217,164,65,.1)',  'color'=>'var(--gold2)', 'bdr'=>'rgba(217,164,65,.3)'],
    'aman'         => ['label'=>'AMAN',        'bg'=>'rgba(63,207,142,.08)', 'color'=>'var(--emer2)', 'bdr'=>'rgba(63,207,142,.25)'],
];
$hariClr = fn($h) => $h < 7 ? 'var(--red)' : ($h < 30 ? '#ff8800' : ($h < 60 ? 'var(--gold2)' : 'var(--emer2)'));
$barClr  = fn($p) => $p < 15 ? 'var(--red)' : ($p < 33 ? '#ff8800' : ($p < 66 ? 'var(--gold)' : 'var(--emer)'));
$lBdr    = fn($s) => in_array($s, ['habis','kritis']) ? 'var(--red)' : ($s === 'hampir_habis' ? '#ff8800' : ($s === 'perhatian' ? 'var(--gold)' : 'transparent'));
$fmtRp   = fn($n) => 'Rp ' . number_format($n, 0, ',', '.');
$stats   = $statsKronis;
@endphp
<div>{{-- Livewire single root --}}

{{-- ===================== PAGE HEADER ===================== --}}
<div style="margin-bottom:1.5rem;display:flex;align-items:flex-end;justify-content:space-between;flex-wrap:wrap;gap:1rem;">
    <div>
        <div style="font-size:.7rem;color:var(--mut2);letter-spacing:.06em;text-transform:uppercase;margin-bottom:.3rem;">
            Pengadaan › Analisis Kebutuhan
        </div>
        <h1 class="font-heading" style="font-size:1.65rem;color:var(--ink);margin:0 0 .3rem;">
            Kebutuhan Obat <em style="color:var(--gold2);">Kronis</em>
        </h1>
        <p style="font-size:.82rem;color:var(--mut);margin:0;">
            Analisis real-time dari
            <strong style="color:var(--ink);">{{ $stats['total_jenis_obat'] }} jenis obat</strong> dalam
            <strong style="color:var(--ink);">{{ $stats['total_pasien'] }} resep aktif</strong> pasien PRB —
            diperbaharui setiap kali data berubah.
        </p>
    </div>
    <div style="display:flex;gap:.5rem;align-items:center;">
        <div style="font-size:.7rem;color:var(--mut2);text-align:right;">
            <span style="color:var(--mut);">Horizon rekomendasi:</span><br>
            <select wire:model.live="horizon" style="background:var(--panel);border:1px solid var(--line2);color:var(--ink);border-radius:.4rem;padding:.25rem .5rem;font-size:.78rem;margin-top:.2rem;">
                <option value="1">1 Bulan</option>
                <option value="3">3 Bulan</option>
                <option value="6">6 Bulan</option>
            </select>
        </div>
    </div>
</div>

{{-- ===================== CRITICAL ALERT ===================== --}}
@if($stats['kritis_count'] > 0)
<div style="margin-bottom:1.25rem;background:rgba(232,100,90,.07);border:1px solid rgba(232,100,90,.25);border-left:4px solid var(--red);border-radius:.6rem;padding:.8rem 1.1rem;display:flex;align-items:flex-start;gap:.75rem;">
    <div style="flex-shrink:0;margin-top:.05rem;">
        <svg width="16" height="16" fill="none" stroke="var(--red)" stroke-width="2.5" viewBox="0 0 24 24"><path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
    </div>
    <div>
        <div style="font-size:.82rem;font-weight:700;color:var(--red2);margin-bottom:.2rem;">
            {{ $stats['kritis_count'] }} obat dalam kondisi KRITIS — perlu reorder segera
        </div>
        <div style="font-size:.77rem;color:var(--mut);display:flex;flex-wrap:wrap;gap:.4rem;">
            @foreach($kebutuhanKronis->whereIn('status',['habis','kritis'])->take(6) as $item)
                <span style="background:rgba(232,100,90,.1);border:1px solid rgba(232,100,90,.25);border-radius:999px;padding:.1rem .55rem;color:var(--red);">
                    {{ $item->nama_obat }}{{ $item->hari_tersisa < 9999 ? ' (' . $item->hari_tersisa . ' hr)' : ' (habis)' }}
                </span>
            @endforeach
            @if($stats['kritis_count'] > 6)
                <span style="color:var(--mut);">+{{ $stats['kritis_count'] - 6 }} lainnya</span>
            @endif
        </div>
    </div>
</div>
@endif

{{-- ===================== KPI CARDS ===================== --}}
<div style="display:grid;grid-template-columns:repeat(5,1fr);gap:.75rem;margin-bottom:1.75rem;">

    {{-- Jenis Obat Kronis --}}
    <div class="kpi-card" style="border-left:3px solid rgba(111,177,224,.4);">
        <div style="font-size:.68rem;font-weight:600;letter-spacing:.06em;text-transform:uppercase;color:var(--mut);margin-bottom:.6rem;">Jenis Obat Kronis</div>
        <div style="font-size:1.9rem;font-weight:800;color:var(--blue);line-height:1;margin-bottom:.4rem;">{{ $stats['total_jenis_obat'] }}</div>
        <div style="font-size:.72rem;color:var(--mut2);">dari resep pasien aktif</div>
    </div>

    {{-- Pasien Aktif --}}
    <div class="kpi-card" style="border-left:3px solid rgba(63,207,142,.4);">
        <div style="font-size:.68rem;font-weight:600;letter-spacing:.06em;text-transform:uppercase;color:var(--mut);margin-bottom:.6rem;">Pasien Aktif</div>
        <div style="font-size:1.9rem;font-weight:800;color:var(--emer);line-height:1;margin-bottom:.4rem;">{{ $stats['total_pasien'] }}</div>
        <div style="font-size:.72rem;color:var(--mut2);">terdaftar program PRB</div>
    </div>

    {{-- Kebutuhan/Bulan --}}
    <div class="kpi-card" style="border-left:3px solid rgba(217,164,65,.4);">
        <div style="font-size:.68rem;font-weight:600;letter-spacing:.06em;text-transform:uppercase;color:var(--mut);margin-bottom:.6rem;">Kebutuhan / Bulan</div>
        <div style="font-size:1.9rem;font-weight:800;color:var(--gold2);line-height:1;margin-bottom:.4rem;">{{ number_format($stats['total_unit_bulan'],0,',','.') }}</div>
        <div style="font-size:.72rem;color:var(--mut2);">unit dari semua obat kronis</div>
    </div>

    {{-- Stok Perhatian --}}
    <div class="kpi-card" style="border-left:3px solid {{ $stats['kritis_count'] > 0 ? 'rgba(232,100,90,.5)' : 'rgba(255,136,0,.4)' }};">
        <div style="font-size:.68rem;font-weight:600;letter-spacing:.06em;text-transform:uppercase;color:var(--mut);margin-bottom:.6rem;">Butuh Perhatian</div>
        <div style="font-size:1.9rem;font-weight:800;color:{{ $stats['kritis_count'] > 0 ? 'var(--red)' : ($stats['hampir_habis_count'] > 0 ? '#ff9944' : 'var(--emer2)') }};line-height:1;margin-bottom:.4rem;">
            {{ $stats['kritis_count'] + $stats['hampir_habis_count'] }}
        </div>
        <div style="font-size:.72rem;color:var(--mut2);">
            @if($stats['kritis_count'] > 0)
                {{ $stats['kritis_count'] }} kritis · {{ $stats['hampir_habis_count'] }} hampir habis
            @elseif($stats['hampir_habis_count'] > 0)
                {{ $stats['hampir_habis_count'] }} hampir habis
            @else
                semua stok aman ✓
            @endif
        </div>
    </div>

    {{-- Nilai / Bulan --}}
    <div class="kpi-card" style="border-left:3px solid rgba(63,207,142,.25);">
        <div style="font-size:.68rem;font-weight:600;letter-spacing:.06em;text-transform:uppercase;color:var(--mut);margin-bottom:.6rem;">Est. Nilai / Bulan</div>
        <div style="font-size:1.2rem;font-weight:800;color:var(--ink);line-height:1;margin-bottom:.4rem;">{{ $fmtRp($stats['nilai_bulan']) }}</div>
        <div style="font-size:.72rem;color:var(--mut2);">
            reko {{ $horizon }} bln: <span style="color:var(--gold2);">{{ $fmtRp($stats['nilai_reko']) }}</span>
        </div>
    </div>
</div>

{{-- ===================== MAIN TABS ===================== --}}
<div x-data="{ tab: 'kronis' }">

    {{-- Tab Nav --}}
    <div style="display:flex;gap:.25rem;border-bottom:1px solid var(--line);margin-bottom:1.25rem;">
        <button @click="tab='kronis'"
            :style="tab==='kronis' ? 'color:var(--gold2);border-bottom:2px solid var(--gold);background:rgba(217,164,65,.04);' : ''"
            style="display:flex;align-items:center;gap:.5rem;padding:.65rem 1.1rem;font-size:.82rem;font-weight:600;color:var(--mut);background:none;border:none;border-bottom:2px solid transparent;cursor:pointer;border-radius:.4rem .4rem 0 0;transition:color .15s;">
            <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M19 3H5a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2V5a2 2 0 00-2-2z"/><path d="M3 9h18"/><path d="M9 21V9"/></svg>
            Obat Kronis
            <span style="background:rgba(63,207,142,.12);color:var(--emer);border-radius:999px;padding:.05rem .45rem;font-size:.68rem;font-weight:700;">{{ $stats['total_jenis_obat'] }}</span>
        </button>
        <button @click="tab='nonkronis'"
            :style="tab==='nonkronis' ? 'color:var(--gold2);border-bottom:2px solid var(--gold);background:rgba(217,164,65,.04);' : ''"
            style="display:flex;align-items:center;gap:.5rem;padding:.65rem 1.1rem;font-size:.82rem;font-weight:600;color:var(--mut);background:none;border:none;border-bottom:2px solid transparent;cursor:pointer;border-radius:.4rem .4rem 0 0;transition:color .15s;">
            <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M5 12h14m-7-7 7 7-7 7"/></svg>
            Non-Kronis
            <span style="background:rgba(111,177,224,.1);color:var(--blue);border-radius:999px;padding:.05rem .45rem;font-size:.68rem;font-weight:700;">{{ $kebutuhanNonKronis->count() }}</span>
        </button>
        <button @click="tab='info'"
            :style="tab==='info' ? 'color:var(--gold2);border-bottom:2px solid var(--gold);background:rgba(217,164,65,.04);' : ''"
            style="display:flex;align-items:center;gap:.5rem;padding:.65rem 1.1rem;font-size:.82rem;font-weight:600;color:var(--mut);background:none;border:none;border-bottom:2px solid transparent;cursor:pointer;border-radius:.4rem .4rem 0 0;transition:color .15s;">
            <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
            Metodologi
        </button>
    </div>

    {{-- =================== TAB: KRONIS =================== --}}
    <div x-show="tab==='kronis'" x-transition.opacity.duration.150ms>

        {{-- Filter Toolbar --}}
        <div style="display:flex;flex-wrap:wrap;gap:.6rem;align-items:center;margin-bottom:1.25rem;padding:.85rem 1rem;background:var(--panel);border:1px solid var(--line);border-radius:.65rem;">
            <svg width="14" height="14" fill="none" stroke="var(--mut)" stroke-width="2" viewBox="0 0 24 24"><line x1="4" y1="6" x2="20" y2="6"/><line x1="8" y1="12" x2="16" y2="12"/><line x1="11" y1="18" x2="13" y2="18"/></svg>
            <span style="font-size:.7rem;font-weight:700;letter-spacing:.07em;text-transform:uppercase;color:var(--mut2);margin-right:.25rem;">Filter</span>

            <select wire:model.live="filterDiagnosis" style="background:var(--card);border:1px solid var(--line2);color:var(--ink);border-radius:.4rem;padding:.38rem .7rem;font-size:.8rem;flex:1;min-width:140px;max-width:200px;">
                <option value="">Semua Diagnosis</option>
                @foreach($diagnosisList as $d)
                    <option value="{{ $d }}">{{ $d }}</option>
                @endforeach
            </select>

            <select wire:model.live="filterStatus" style="background:var(--card);border:1px solid var(--line2);color:var(--ink);border-radius:.4rem;padding:.38rem .7rem;font-size:.8rem;min-width:130px;">
                <option value="">Semua Status</option>
                <option value="habis">Habis</option>
                <option value="kritis">Kritis (&lt;7 hr)</option>
                <option value="hampir_habis">Hampir Habis (&lt;30 hr)</option>
                <option value="perhatian">Perhatian (&lt;60 hr)</option>
                <option value="aman">Aman</option>
            </select>

            <div style="position:relative;flex:1;min-width:160px;max-width:240px;">
                <svg width="13" height="13" fill="none" stroke="var(--mut2)" stroke-width="2" viewBox="0 0 24 24" style="position:absolute;left:.6rem;top:50%;transform:translateY(-50%);pointer-events:none;"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari nama obat..." style="background:var(--card);border:1px solid var(--line2);color:var(--ink);border-radius:.4rem;padding:.38rem .7rem .38rem 2rem;font-size:.8rem;width:100%;">
            </div>

            @if($filterDiagnosis || $filterStatus || $search)
            <button wire:click="$set('filterDiagnosis','');$set('filterStatus','');$set('search','')"
                style="background:rgba(232,100,90,.1);border:1px solid rgba(232,100,90,.25);color:var(--red);border-radius:.4rem;padding:.38rem .75rem;font-size:.75rem;cursor:pointer;display:flex;align-items:center;gap:.3rem;">
                <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                Reset
            </button>
            @endif
        </div>

        {{-- Charts Row --}}
        <div
            wire:ignore
            x-data="kebutuhanCharts()"
            x-init="init()"
            @charts-refresh.window="refresh($event.detail.data)"
            style="display:grid;grid-template-columns:2fr 1fr;gap:1rem;margin-bottom:1.25rem;"
        >
            <script>window.__kcInitData = @json($chartData);</script>

            {{-- Bar chart --}}
            <div style="background:var(--card);border:1px solid var(--line);border-radius:.75rem;padding:1.1rem 1.25rem;">
                <div style="font-size:.72rem;font-weight:700;letter-spacing:.06em;text-transform:uppercase;color:var(--mut);margin-bottom:.9rem;display:flex;align-items:center;gap:.45rem;">
                    <svg width="12" height="12" fill="none" stroke="var(--emer)" stroke-width="2" viewBox="0 0 24 24"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
                    Top 10 Obat — Kebutuhan vs Stok
                </div>
                <div style="height:260px;position:relative;">
                    <canvas data-chart="topobat"></canvas>
                </div>
            </div>

            {{-- Donut chart --}}
            <div style="background:var(--card);border:1px solid var(--line);border-radius:.75rem;padding:1.1rem 1.25rem;">
                <div style="font-size:.72rem;font-weight:700;letter-spacing:.06em;text-transform:uppercase;color:var(--mut);margin-bottom:.9rem;display:flex;align-items:center;gap:.45rem;">
                    <svg width="12" height="12" fill="none" stroke="var(--gold2)" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                    Distribusi Status Stok
                </div>
                <div style="height:200px;position:relative;">
                    <canvas data-chart="status"></canvas>
                </div>
                {{-- Summary numbers --}}
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:.4rem;margin-top:.85rem;">
                    <div style="text-align:center;padding:.4rem;background:rgba(63,207,142,.06);border-radius:.4rem;">
                        <div style="font-size:1.1rem;font-weight:800;color:var(--emer2);">{{ $stats['aman_count'] }}</div>
                        <div style="font-size:.65rem;color:var(--mut2);">Aman</div>
                    </div>
                    <div style="text-align:center;padding:.4rem;background:rgba(217,164,65,.06);border-radius:.4rem;">
                        <div style="font-size:1.1rem;font-weight:800;color:var(--gold2);">{{ $stats['perhatian_count'] }}</div>
                        <div style="font-size:.65rem;color:var(--mut2);">Perhatian</div>
                    </div>
                    <div style="text-align:center;padding:.4rem;background:rgba(255,136,0,.06);border-radius:.4rem;">
                        <div style="font-size:1.1rem;font-weight:800;color:#ff9944;">{{ $stats['hampir_habis_count'] }}</div>
                        <div style="font-size:.65rem;color:var(--mut2);">Hampir Habis</div>
                    </div>
                    <div style="text-align:center;padding:.4rem;background:rgba(232,100,90,.06);border-radius:.4rem;">
                        <div style="font-size:1.1rem;font-weight:800;color:var(--red);">{{ $stats['kritis_count'] }}</div>
                        <div style="font-size:.65rem;color:var(--mut2);">Kritis</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Insight Bar --}}
        <div style="display:flex;align-items:center;gap:.6rem;padding:.65rem 1rem;background:rgba(63,207,142,.04);border:1px solid rgba(63,207,142,.12);border-radius:.5rem;margin-bottom:1rem;font-size:.78rem;color:var(--mut);flex-wrap:wrap;">
            <svg width="13" height="13" fill="none" stroke="var(--emer)" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
            <span>Menampilkan <strong style="color:var(--ink);">{{ $kebutuhanKronis->count() }} obat</strong></span>
            <span style="color:var(--line2);">·</span>
            <span>Total kebutuhan <strong style="color:var(--ink);">{{ number_format($stats['total_unit_bulan'],0,',','.') }} unit/bulan</strong></span>
            <span style="color:var(--line2);">·</span>
            <span>Est. nilai <strong style="color:var(--gold2);">{{ $fmtRp($stats['nilai_bulan']) }}/bulan</strong></span>
            <span style="color:var(--line2);">·</span>
            <span>Reko. {{ $horizon }} bulan: <strong style="color:var(--gold2);">{{ $fmtRp($stats['nilai_reko']) }}</strong> (termasuk 10% buffer)</span>
        </div>

        {{-- =================== TABLE: KRONIS =================== --}}
        @if($kebutuhanKronis->isEmpty())
            <div style="text-align:center;padding:4rem 2rem;background:var(--panel);border:1px solid var(--line);border-radius:.75rem;">
                <svg width="48" height="48" fill="none" stroke="var(--mut2)" stroke-width="1.5" viewBox="0 0 24 24" style="margin:0 auto 1rem;display:block;"><path d="M9 12h6m-3-3v6m-7 4h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                <div style="font-size:1rem;font-weight:700;color:var(--mut);margin-bottom:.5rem;">Belum ada resep obat aktif</div>
                <div style="font-size:.82rem;color:var(--mut2);">Buka <strong>Pasien › Daftar Pasien</strong>, klik tombol Resep pada pasien, tambahkan obat rutin untuk memulai analisis.</div>
            </div>
        @else
        <div style="background:var(--card);border:1px solid var(--line);border-radius:.75rem;overflow:hidden;">
            <div style="overflow-x:auto;">
                <table class="data-table" style="min-width:900px;">
                    <thead>
                        <tr>
                            <th style="width:34px;">#</th>
                            <th>Nama Obat</th>
                            <th style="text-align:center;">Pasien</th>
                            <th style="text-align:right;">Unit / Bln</th>
                            <th style="min-width:160px;">Stok Aktual</th>
                            <th style="text-align:center;min-width:90px;">Status</th>
                            <th style="text-align:center;">Habis Pada</th>
                            <th style="text-align:right;min-width:120px;">Reko. {{ $horizon }}Bln</th>
                            <th style="text-align:right;">Est. Biaya</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($kebutuhanKronis as $i => $item)
                        @php
                            $s    = $sc[$item->status];
                            $lbdr = $lBdr($item->status);
                            $bclr = $barClr($item->persen_stok);
                            $hclr = $hariClr($item->hari_tersisa);
                        @endphp
                        <tr style="border-left:3px solid {{ $lbdr }};">
                            <td style="color:var(--mut2);font-size:.75rem;">{{ $i+1 }}</td>
                            <td>
                                <div style="font-weight:600;color:var(--ink);font-size:.85rem;margin-bottom:.2rem;">{{ $item->nama_obat }}</div>
                                <div style="display:flex;gap:.3rem;align-items:center;flex-wrap:wrap;">
                                    <span style="font-size:.68rem;background:rgba(111,177,224,.08);color:var(--blue);border:1px solid rgba(111,177,224,.2);border-radius:999px;padding:.08rem .45rem;">{{ $item->kategori_diagnosis }}</span>
                                    <span style="font-size:.68rem;color:var(--mut2);">{{ $item->satuan }}</span>
                                </div>
                            </td>
                            <td style="text-align:center;">
                                <div style="font-size:1.05rem;font-weight:700;color:var(--ink);">{{ $item->jumlah_pasien }}</div>
                                <div style="font-size:.65rem;color:var(--mut2);">pasien</div>
                            </td>
                            <td style="text-align:right;">
                                <div style="font-size:.95rem;font-weight:700;color:var(--ink);">{{ number_format($item->unit_per_bulan,0,',','.') }}</div>
                                <div style="font-size:.65rem;color:var(--mut2);">{{ number_format($item->unit_per_hari,1,',','.') }}/hari</div>
                            </td>
                            <td>
                                <div style="font-size:.85rem;font-weight:600;color:var(--ink);margin-bottom:.35rem;">
                                    {{ number_format($item->stok_aktual,0,',','.') }}
                                    <span style="font-size:.68rem;color:var(--mut2);font-weight:400;">{{ $item->satuan }}</span>
                                </div>
                                <div style="background:rgba(31,61,48,.6);border-radius:3px;height:5px;overflow:hidden;">
                                    <div style="width:{{ $item->persen_stok }}%;height:100%;background:{{ $bclr }};border-radius:3px;"></div>
                                </div>
                                <div style="font-size:.63rem;color:var(--mut2);margin-top:.2rem;">{{ $item->persen_stok }}% dari target 3 bln</div>
                            </td>
                            <td style="text-align:center;">
                                <span style="display:inline-flex;align-items:center;padding:.2rem .6rem;border-radius:999px;font-size:.68rem;font-weight:700;background:{{ $s['bg'] }};color:{{ $s['color'] }};border:1px solid {{ $s['bdr'] }};">
                                    {{ $s['label'] }}
                                </span>
                            </td>
                            <td style="text-align:center;">
                                @if($item->hari_tersisa < 9999)
                                    <div style="font-weight:700;color:{{ $hclr }};font-size:.85rem;">{{ $item->hari_tersisa }} hari</div>
                                    <div style="font-size:.68rem;color:var(--mut2);">{{ $item->habis_tanggal }}</div>
                                @else
                                    <span style="color:var(--mut2);font-size:.8rem;">—</span>
                                @endif
                            </td>
                            <td style="text-align:right;">
                                @if($item->reko_pengadaan > 0)
                                    <div style="font-weight:700;color:var(--gold2);font-size:.9rem;">+{{ number_format($item->reko_pengadaan,0,',','.') }}</div>
                                    <div style="font-size:.65rem;color:var(--mut2);">{{ $item->satuan }}</div>
                                @else
                                    <span style="color:var(--emer2);font-size:.8rem;font-weight:600;">✓ Cukup</span>
                                @endif
                            </td>
                            <td style="text-align:right;">
                                @if($item->reko_pengadaan > 0)
                                    <div style="font-size:.82rem;color:var(--ink);">{{ $fmtRp($item->nilai_reko) }}</div>
                                @else
                                    <span style="font-size:.75rem;color:var(--mut2);">—</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr style="background:var(--panel);">
                            <td colspan="3" style="padding:.7rem 1rem;font-size:.72rem;color:var(--mut);font-weight:600;text-transform:uppercase;letter-spacing:.05em;">Total</td>
                            <td style="text-align:right;padding:.7rem 1rem;font-weight:700;color:var(--gold2);">{{ number_format($stats['total_unit_bulan'],0,',','.') }}</td>
                            <td colspan="3" style="padding:.7rem 1rem;font-size:.75rem;color:var(--mut);">Nilai/bln: <strong style="color:var(--ink);">{{ $fmtRp($stats['nilai_bulan']) }}</strong></td>
                            <td style="text-align:right;padding:.7rem 1rem;font-weight:700;color:var(--gold2);">{{ number_format($kebutuhanKronis->sum('reko_pengadaan'),0,',','.') }}</td>
                            <td style="text-align:right;padding:.7rem 1rem;font-weight:700;color:var(--gold2);">{{ $fmtRp($stats['nilai_reko']) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
        @endif
    </div>{{-- end tab kronis --}}

    {{-- =================== TAB: NON-KRONIS =================== --}}
    <div x-show="tab==='nonkronis'" x-transition.opacity.duration.150ms>

        <div style="padding:.75rem 1rem;background:rgba(111,177,224,.04);border:1px solid rgba(111,177,224,.15);border-radius:.5rem;margin-bottom:1.1rem;font-size:.78rem;color:var(--mut);display:flex;align-items:flex-start;gap:.6rem;">
            <svg width="14" height="14" fill="none" stroke="var(--blue)" stroke-width="2" viewBox="0 0 24 24" style="margin-top:.1rem;flex-shrink:0;"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
            <span>Data non-kronis dihitung dari <strong style="color:var(--ink);">rata-rata pengeluaran stok 3 bulan terakhir</strong>. Rekomendasi pengadaan menggunakan horizon <strong style="color:var(--ink);">{{ $horizon }} bulan</strong> + 10% buffer.</span>
        </div>

        {{-- Search untuk non-kronis --}}
        <div style="display:flex;gap:.6rem;margin-bottom:1rem;">
            <div style="position:relative;max-width:260px;width:100%;">
                <svg width="13" height="13" fill="none" stroke="var(--mut2)" stroke-width="2" viewBox="0 0 24 24" style="position:absolute;left:.6rem;top:50%;transform:translateY(-50%);pointer-events:none;"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari nama obat..." style="background:var(--panel);border:1px solid var(--line2);color:var(--ink);border-radius:.4rem;padding:.38rem .7rem .38rem 2rem;font-size:.8rem;width:100%;">
            </div>
        </div>

        @if($kebutuhanNonKronis->isEmpty())
            <div style="text-align:center;padding:4rem 2rem;background:var(--panel);border:1px solid var(--line);border-radius:.75rem;">
                <svg width="48" height="48" fill="none" stroke="var(--mut2)" stroke-width="1.5" viewBox="0 0 24 24" style="margin:0 auto 1rem;display:block;"><path d="M5 12h14m-7-7 7 7-7 7"/></svg>
                <div style="font-size:1rem;font-weight:700;color:var(--mut);margin-bottom:.5rem;">Belum ada data pengeluaran obat non-kronis</div>
                <div style="font-size:.82rem;color:var(--mut2);">Data akan muncul setelah ada catatan stok keluar di menu Inventori › Stok Keluar (3 bulan terakhir).</div>
            </div>
        @else
        <div style="background:var(--card);border:1px solid var(--line);border-radius:.75rem;overflow:hidden;">
            <div style="overflow-x:auto;">
                <table class="data-table" style="min-width:800px;">
                    <thead>
                        <tr>
                            <th style="width:34px;">#</th>
                            <th>Nama Obat</th>
                            <th style="text-align:right;">Total 3 Bln</th>
                            <th style="text-align:right;">Rata / Bln</th>
                            <th style="min-width:160px;">Stok Aktual</th>
                            <th style="text-align:center;">Status</th>
                            <th style="text-align:center;">Habis Pada</th>
                            <th style="text-align:right;">Reko. {{ $horizon }}Bln</th>
                            <th style="text-align:center;">Terakhir Keluar</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($kebutuhanNonKronis as $i => $item)
                        @php
                            $s    = $sc[$item->status];
                            $bclr = $barClr($item->persen_stok);
                            $hclr = $hariClr($item->hari_tersisa);
                            $lbdr = $lBdr($item->status);
                        @endphp
                        <tr style="border-left:3px solid {{ $lbdr }};">
                            <td style="color:var(--mut2);font-size:.75rem;">{{ $i+1 }}</td>
                            <td>
                                <div style="font-weight:600;color:var(--ink);font-size:.85rem;margin-bottom:.2rem;">{{ $item->nama_obat }}</div>
                                <div style="display:flex;gap:.3rem;align-items:center;">
                                    <span style="font-size:.68rem;background:rgba(111,177,224,.06);color:var(--blue);border:1px solid rgba(111,177,224,.18);border-radius:999px;padding:.08rem .45rem;">{{ $item->kategori_diagnosis }}</span>
                                    <span style="font-size:.68rem;color:var(--mut2);">{{ $item->frekuensi }}× transaksi</span>
                                </div>
                            </td>
                            <td style="text-align:right;font-weight:600;color:var(--ink);">{{ number_format($item->total_3bulan,0,',','.') }}<span style="font-size:.65rem;color:var(--mut2);font-weight:400;"> {{ $item->satuan }}</span></td>
                            <td style="text-align:right;font-weight:700;color:var(--gold2);">{{ number_format($item->rata_per_bulan,0,',','.') }}<span style="font-size:.65rem;color:var(--mut2);font-weight:400;"> /bln</span></td>
                            <td>
                                <div style="font-size:.85rem;font-weight:600;color:var(--ink);margin-bottom:.35rem;">{{ number_format($item->stok_aktual,0,',','.') }} <span style="font-size:.68rem;color:var(--mut2);font-weight:400;">{{ $item->satuan }}</span></div>
                                <div style="background:rgba(31,61,48,.6);border-radius:3px;height:5px;overflow:hidden;">
                                    <div style="width:{{ $item->persen_stok }}%;height:100%;background:{{ $bclr }};border-radius:3px;"></div>
                                </div>
                                <div style="font-size:.63rem;color:var(--mut2);margin-top:.2rem;">{{ $item->persen_stok }}% dari target 3 bln</div>
                            </td>
                            <td style="text-align:center;">
                                <span style="display:inline-flex;align-items:center;padding:.2rem .6rem;border-radius:999px;font-size:.68rem;font-weight:700;background:{{ $s['bg'] }};color:{{ $s['color'] }};border:1px solid {{ $s['bdr'] }};">
                                    {{ $s['label'] }}
                                </span>
                            </td>
                            <td style="text-align:center;">
                                @if($item->hari_tersisa < 9999)
                                    <div style="font-weight:700;color:{{ $hclr }};font-size:.82rem;">{{ $item->hari_tersisa }} hari</div>
                                    <div style="font-size:.67rem;color:var(--mut2);">{{ $item->habis_tanggal }}</div>
                                @else
                                    <span style="color:var(--mut2);font-size:.8rem;">—</span>
                                @endif
                            </td>
                            <td style="text-align:right;">
                                @if($item->reko_pengadaan > 0)
                                    <div style="font-weight:700;color:var(--gold2);">+{{ number_format($item->reko_pengadaan,0,',','.') }}</div>
                                    <div style="font-size:.65rem;color:var(--mut2);">{{ $item->satuan }}</div>
                                @else
                                    <span style="color:var(--emer2);font-size:.8rem;font-weight:600;">✓ Cukup</span>
                                @endif
                            </td>
                            <td style="text-align:center;font-size:.75rem;color:var(--mut);">{{ $item->terakhir_keluar }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif
    </div>{{-- end tab nonkronis --}}

    {{-- =================== TAB: INFO =================== --}}
    <div x-show="tab==='info'" x-transition.opacity.duration.150ms>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">

            <div style="background:var(--card);border:1px solid var(--line);border-radius:.75rem;padding:1.4rem 1.5rem;">
                <div style="font-size:.8rem;font-weight:700;color:var(--emer2);margin-bottom:.9rem;display:flex;align-items:center;gap:.5rem;">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M19 3H5a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2V5a2 2 0 00-2-2z"/><path d="M3 9h18"/><path d="M9 21V9"/></svg>
                    Bagaimana Obat Kronis Dihitung
                </div>
                <div style="font-size:.8rem;color:var(--mut);line-height:1.65;">
                    <p style="margin:0 0 .7rem;">Data obat kronis diambil dari <strong style="color:var(--ink);">Resep Pasien</strong> — daftar obat rutin yang di-input di halaman <em>Pasien › Daftar Pasien › tab Resep Obat</em>.</p>
                    <p style="margin:0 0 .7rem;">Setiap pasien aktif yang memiliki resep aktif berkontribusi ke perhitungan kebutuhan. <strong style="color:var(--ink);">Unit/Bulan</strong> adalah jumlah total dari <code style="background:var(--panel);padding:.1rem .3rem;border-radius:3px;font-size:.75rem;">jumlah_default</code> semua pasien untuk obat tersebut.</p>
                    <p style="margin:0;">Rumus: <code style="background:var(--panel);padding:.15rem .5rem;border-radius:3px;font-size:.75rem;">Hari Stok = Stok Aktual ÷ (Unit/Bulan ÷ 30)</code></p>
                </div>
            </div>

            <div style="background:var(--card);border:1px solid var(--line);border-radius:.75rem;padding:1.4rem 1.5rem;">
                <div style="font-size:.8rem;font-weight:700;color:var(--blue);margin-bottom:.9rem;display:flex;align-items:center;gap:.5rem;">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M5 12h14m-7-7 7 7-7 7"/></svg>
                    Bagaimana Obat Non-Kronis Dihitung
                </div>
                <div style="font-size:.8rem;color:var(--mut);line-height:1.65;">
                    <p style="margin:0 0 .7rem;">Data non-kronis diambil dari <strong style="color:var(--ink);">Riwayat Stok Keluar</strong> 3 bulan terakhir (menu Inventori › Stok Keluar).</p>
                    <p style="margin:0 0 .7rem;">Rata-rata konsumsi dihitung sebagai total unit keluar dibagi 3 bulan. Obat yang tidak memiliki riwayat stok keluar tidak ditampilkan.</p>
                    <p style="margin:0;">Rumus: <code style="background:var(--panel);padding:.15rem .5rem;border-radius:3px;font-size:.75rem;">Rata/Bln = Total 3 Bulan ÷ 3</code></p>
                </div>
            </div>

            <div style="background:var(--card);border:1px solid var(--line);border-radius:.75rem;padding:1.4rem 1.5rem;">
                <div style="font-size:.8rem;font-weight:700;color:var(--gold2);margin-bottom:.9rem;display:flex;align-items:center;gap:.5rem;">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6"/></svg>
                    Rekomendasi Pengadaan
                </div>
                <div style="font-size:.8rem;color:var(--mut);line-height:1.65;">
                    <p style="margin:0 0 .7rem;">Rekomendasi dihitung untuk memenuhi kebutuhan selama <strong style="color:var(--ink);">horizon bulan yang dipilih</strong> (1/3/6 bulan) dengan buffer keamanan <strong style="color:var(--ink);">10%</strong>.</p>
                    <p style="margin:0;">Rumus: <code style="background:var(--panel);padding:.15rem .5rem;border-radius:3px;font-size:.75rem;">Reko = (Unit/Bln × Horizon × 1.1) − Stok Aktual</code></p>
                </div>
            </div>

            <div style="background:var(--card);border:1px solid var(--line);border-radius:.75rem;padding:1.4rem 1.5rem;">
                <div style="font-size:.8rem;font-weight:700;color:var(--mut);margin-bottom:.9rem;display:flex;align-items:center;gap:.5rem;">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="2"/><circle cx="12" cy="5" r="1" fill="currentColor" stroke="none"/><circle cx="12" cy="19" r="1" fill="currentColor" stroke="none"/></svg>
                    Definisi Status Stok
                </div>
                <div style="font-size:.78rem;color:var(--mut);line-height:1.8;">
                    <div style="display:flex;gap:.5rem;align-items:center;"><span style="width:8px;height:8px;border-radius:50%;background:var(--red);flex-shrink:0;"></span><span><strong style="color:var(--red);">Habis</strong> — stok = 0</span></div>
                    <div style="display:flex;gap:.5rem;align-items:center;"><span style="width:8px;height:8px;border-radius:50%;background:var(--red2);flex-shrink:0;"></span><span><strong style="color:var(--red2);">Kritis</strong> — stok cukup &lt; 7 hari</span></div>
                    <div style="display:flex;gap:.5rem;align-items:center;"><span style="width:8px;height:8px;border-radius:50%;background:#ff9944;flex-shrink:0;"></span><span><strong style="color:#ff9944;">Hampir Habis</strong> — stok cukup &lt; 30 hari</span></div>
                    <div style="display:flex;gap:.5rem;align-items:center;"><span style="width:8px;height:8px;border-radius:50%;background:var(--gold2);flex-shrink:0;"></span><span><strong style="color:var(--gold2);">Perhatian</strong> — stok cukup &lt; 60 hari</span></div>
                    <div style="display:flex;gap:.5rem;align-items:center;"><span style="width:8px;height:8px;border-radius:50%;background:var(--emer2);flex-shrink:0;"></span><span><strong style="color:var(--emer2);">Aman</strong> — stok cukup ≥ 60 hari</span></div>
                </div>
            </div>

        </div>
    </div>{{-- end tab info --}}

</div>{{-- end x-data tabs --}}
</div>{{-- end Livewire single root --}}
