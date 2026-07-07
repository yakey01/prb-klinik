<div x-data="{ activeSection: '{{ $activeTab }}' }">
<style>
    /* ── Laporan World-Class Overrides ── */
    .lpr-num { font-variant-numeric: tabular-nums; font-family: 'JetBrains Mono','Fira Code','Courier New',monospace; letter-spacing: -.02em; }
    .lpr-label { font-size: .6rem; text-transform: uppercase; letter-spacing: .1em; font-weight: 700; color: var(--mut); }
    .lpr-kpi { border-radius: .9rem; padding: 1.2rem 1.35rem; position: relative; overflow: hidden; }
    .lpr-kpi-val { font-size: 1.55rem; font-weight: 800; line-height: 1.05; letter-spacing: -.03em; margin: .4rem 0 .3rem; }
    .lpr-delta { display: inline-flex; align-items: center; gap: .25rem; font-size: .68rem; font-weight: 700; padding: .13rem .45rem; border-radius: .35rem; }
    .lpr-tab { padding: .45rem 1.1rem; border-radius: .55rem; border: none; cursor: pointer; font-size: .78rem; font-weight: 600; transition: all .18s; font-family: inherit; display: inline-flex; align-items: center; gap: .45rem; }
    .lpr-section-hd { font-size: .6rem; text-transform: uppercase; letter-spacing: .12em; color: var(--mut); font-weight: 700; }
    .lpr-flow-bar { height: 8px; border-radius: 4px; overflow: hidden; background: rgba(0,0,0,.3); }
    .lpr-table { width: 100%; border-collapse: collapse; font-variant-numeric: tabular-nums; }
    .lpr-table th { background: rgba(14,30,23,.95); color: var(--mut); font-size: .63rem; font-family: 'Inter',sans-serif; text-transform: uppercase; letter-spacing: .09em; padding: .65rem .9rem; text-align: left; border-bottom: 1px solid var(--line2); white-space: nowrap; position: sticky; top: 0; z-index: 2; }
    .lpr-table td { padding: .6rem .9rem; border-bottom: 1px solid rgba(31,61,48,.5); font-size: .8rem; }
    .lpr-table tr:hover td { background: rgba(255,255,255,.025); }
    .lpr-table tfoot td { background: rgba(0,0,0,.2); border-top: 1.5px solid var(--line2); font-weight: 700; }
    .lpr-pill { font-size: .62rem; padding: .1rem .45rem; border-radius: .3rem; font-weight: 700; }
    .lpr-badge-pos { background: rgba(63,207,142,.13); color: var(--emer2); border: 1px solid rgba(63,207,142,.22); }
    .lpr-badge-neg { background: rgba(232,100,90,.13); color: var(--red2); border: 1px solid rgba(232,100,90,.22); }
    .lpr-badge-gold { background: rgba(217,164,65,.13); color: var(--gold2); border: 1px solid rgba(217,164,65,.22); }
    .lpr-badge-blue { background: rgba(111,177,224,.13); color: var(--blue); border: 1px solid rgba(111,177,224,.22); }
    .lpr-divider { height: 1px; background: var(--line); margin: 1.25rem 0; }
    .lpr-waterfall-bar { border-radius: 3px; transition: width .5s cubic-bezier(.4,0,.2,1); }
    @keyframes lprCountUp { from { opacity: 0; transform: translateY(6px); } to { opacity: 1; transform: none; } }
    .lpr-animate { animation: lprCountUp .3s ease forwards; }
</style>

@php
$r = $this->ringkasan;
$isProfit = $r['labaBersih'] >= 0;
$isKotorProfit = $r['labaKotor'] >= 0;
$bpjsPct  = $r['totalPend'] > 0 ? round($r['pendBpjs']/$r['totalPend']*100,1) : 0;
$tunaiPct = $r['totalPend'] > 0 ? round($r['pendTunai']/$r['totalPend']*100,1) : 0;
// Margin bar width (capped at 100%)
$marginW  = min(abs($r['marginPersen']), 100);
$bersihW  = $r['labaKotor'] > 0 ? min(abs($r['labaBersih']/$r['labaKotor']*100), 100) : 0;
$hppW     = $r['totalPend'] > 0 ? min($r['totalHpp']/$r['totalPend']*100, 100) : 0;
$opsW     = $r['labaKotor'] > 0 ? min($r['totalBiayaOps']/$r['labaKotor']*100, 100) : 0;
@endphp

{{-- ═══════════════════════════════════════════════════════════ --}}
{{-- EXECUTIVE BANNER — health status + period + actions        --}}
{{-- ═══════════════════════════════════════════════════════════ --}}
<div style="background:{{ $isProfit ? 'linear-gradient(135deg,rgba(63,207,142,.07),rgba(63,207,142,.02))' : 'linear-gradient(135deg,rgba(232,100,90,.07),rgba(232,100,90,.02))' }};
            border:1px solid {{ $isProfit ? 'rgba(63,207,142,.2)' : 'rgba(232,100,90,.2)' }};
            border-radius:1.1rem; padding:1.35rem 1.6rem; margin-bottom:1.35rem;
            display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:1rem;">

    {{-- Left: status + period --}}
    <div style="display:flex; align-items:center; gap:1.1rem; flex-wrap:wrap;">
        {{-- Health indicator --}}
        <div style="display:flex; align-items:center; gap:.55rem;">
            <div style="width:42px; height:42px; border-radius:50%;
                        background:{{ $isProfit ? 'rgba(63,207,142,.15)' : 'rgba(232,100,90,.15)' }};
                        border:1.5px solid {{ $isProfit ? 'rgba(63,207,142,.4)' : 'rgba(232,100,90,.4)' }};
                        display:flex; align-items:center; justify-content:center;">
                @if($isProfit)
                <svg width="18" height="18" fill="none" stroke="#3fcf8e" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
                @else
                <svg width="18" height="18" fill="none" stroke="#e8645a" stroke-width="2.5" viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><polyline points="19 12 12 19 5 12"/></svg>
                @endif
            </div>
            <div>
                <div style="font-size:.6rem; text-transform:uppercase; letter-spacing:.1em; color:var(--mut); font-weight:700; margin-bottom:.1rem;">Status Keuangan</div>
                <div style="font-size:.92rem; font-weight:800; color:{{ $isProfit ? 'var(--emer2)' : 'var(--red2)' }};">
                    {{ $isProfit ? 'Periode Untung' : 'Periode Rugi' }}
                </div>
            </div>
        </div>

        <div style="width:1px; height:36px; background:var(--line); flex-shrink:0;"></div>

        {{-- Period info --}}
        <div>
            <div style="font-size:.6rem; text-transform:uppercase; letter-spacing:.1em; color:var(--mut); font-weight:700; margin-bottom:.15rem;">Laporan Periodik</div>
            <div style="font-size:1.05rem; font-weight:700; color:var(--ink);">
                Laporan Bulan <em style="color:var(--gold2);">{{ $this->periode }}</em>
            </div>
        </div>

        <div style="width:1px; height:36px; background:var(--line); flex-shrink:0;" class="hide-mobile"></div>

        {{-- Laba bersih hero --}}
        <div class="hide-mobile">
            <div class="lpr-label" style="margin-bottom:.15rem;">Laba Bersih</div>
            <div class="lpr-num" style="font-size:1.5rem; font-weight:800; color:{{ $isProfit ? 'var(--gold2)' : 'var(--red2)' }}; letter-spacing:-.03em;">
                {{ $r['labaBersih'] >= 0 ? '+' : '' }}Rp {{ number_format($r['labaBersih'],0,',','.') }}
            </div>
        </div>
    </div>

    {{-- Right: period selector + export --}}
    <div style="display:flex; gap:.5rem; align-items:center; flex-wrap:wrap;">
        <div style="display:flex; background:var(--panel); border:1px solid var(--line2); border-radius:.65rem; overflow:hidden;">
            <select wire:model.live="bulan" style="background:transparent; border:none; color:var(--ink); font-size:.8rem; padding:.5rem .75rem; outline:none; cursor:pointer; font-family:inherit; font-weight:600; min-width:100px;">
                @foreach(\App\Models\RekonsiliasiiBpjs::bulanLabels() as $b => $nm)
                <option value="{{ $b }}" style="background:#0e1e17;">{{ $nm }}</option>
                @endforeach
            </select>
            <div style="width:1px; background:var(--line2); flex-shrink:0;"></div>
            <select wire:model.live="tahun" style="background:transparent; border:none; color:var(--ink); font-size:.8rem; padding:.5rem .75rem; outline:none; cursor:pointer; font-family:inherit; font-weight:600;">
                @foreach(range(2024, date('Y')+1) as $y)
                <option value="{{ $y }}" style="background:#0e1e17;">{{ $y }}</option>
                @endforeach
            </select>
        </div>
        <button wire:click="exportCsv" class="btn-outline" style="font-size:.78rem; padding:.5rem 1rem; gap:.4rem;">
            <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
            Export CSV
        </button>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════════ --}}
{{-- TAB NAVIGATION                                             --}}
{{-- ═══════════════════════════════════════════════════════════ --}}
<div style="display:flex; gap:.25rem; margin-bottom:1.35rem; background:var(--panel); border:1px solid var(--line); border-radius:.9rem; padding:.3rem; width:fit-content;">
    <button wire:click="$set('activeTab','ringkasan')" class="lpr-tab"
        style="background:{{ $activeTab==='ringkasan' ? 'var(--bg2)' : 'transparent' }};
               color:{{ $activeTab==='ringkasan' ? 'var(--gold2)' : 'var(--mut)' }};
               box-shadow:{{ $activeTab==='ringkasan' ? '0 1px 6px rgba(0,0,0,.35)' : 'none' }};">
        <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
        Ringkasan
    </button>
    <button wire:click="$set('activeTab','bpjs')" class="lpr-tab"
        style="background:{{ $activeTab==='bpjs' ? 'rgba(63,207,142,.1)' : 'transparent' }};
               color:{{ $activeTab==='bpjs' ? 'var(--emer2)' : 'var(--mut)' }};
               box-shadow:{{ $activeTab==='bpjs' ? '0 1px 6px rgba(0,0,0,.3)' : 'none' }};">
        <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path d="M22 12h-4l-3 9L9 3l-3 9H2"/></svg>
        Obat Kronis
        <span class="lpr-pill lpr-badge-pos">{{ $this->detailBpjs->count() }}</span>
    </button>
    <button wire:click="$set('activeTab','nonkronis')" class="lpr-tab"
        style="background:{{ $activeTab==='nonkronis' ? 'rgba(111,177,224,.1)' : 'transparent' }};
               color:{{ $activeTab==='nonkronis' ? 'var(--blue)' : 'var(--mut)' }};
               box-shadow:{{ $activeTab==='nonkronis' ? '0 1px 6px rgba(0,0,0,.3)' : 'none' }};">
        <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path d="M5 12h14m-7-7 7 7-7 7"/></svg>
        Obat Non-Kronis
        <span class="lpr-pill lpr-badge-blue">{{ $this->detailNonKronis->count() }}</span>
    </button>
</div>


{{-- ═══════════════════════════════════════════════════════════ --}}
{{-- TAB: RINGKASAN                                             --}}
{{-- ═══════════════════════════════════════════════════════════ --}}
@if($activeTab === 'ringkasan')

{{-- ① 4-KOLOM KPI CARDS — world-class 4-layer pattern --}}
<div class="grid-kpi" style="margin-bottom:1.35rem;">

    {{-- KPI 1: Total Pendapatan --}}
    <div class="lpr-kpi" style="background:linear-gradient(145deg,rgba(217,164,65,.1),rgba(217,164,65,.03)); border:1px solid rgba(217,164,65,.25);">
        <div style="position:absolute;top:-20px;right:-20px;width:80px;height:80px;background:radial-gradient(circle,rgba(217,164,65,.1),transparent 70%);pointer-events:none;"></div>
        <div class="lpr-label" style="margin-bottom:.05rem;">Total Pendapatan</div>
        <div class="lpr-num lpr-kpi-val" style="color:var(--gold2);">Rp {{ number_format($r['totalPend'],0,',','.') }}</div>
        <div style="display:flex; gap:.4rem; flex-wrap:wrap; margin-bottom:.6rem;">
            <span class="lpr-delta lpr-badge-pos">BPJS {{ $bpjsPct }}%</span>
            <span class="lpr-delta lpr-badge-blue">Tunai {{ $tunaiPct }}%</span>
        </div>
        {{-- Composition mini-bar --}}
        <div class="lpr-flow-bar">
            <div style="height:100%; display:flex; gap:1px;">
                @if($bpjsPct > 0)<div style="width:{{ $bpjsPct }}%; background:linear-gradient(90deg,#3fcf8e,rgba(63,207,142,.7)); border-radius:4px 0 0 4px;"></div>@endif
                @if($tunaiPct > 0)<div style="flex:1; background:linear-gradient(90deg,rgba(111,177,224,.8),#6fb1e0); border-radius:0 4px 4px 0;"></div>@endif
            </div>
        </div>
    </div>

    {{-- KPI 2: Laba Kotor --}}
    <div class="lpr-kpi" style="background:rgba({{ $isKotorProfit ? '63,207,142' : '232,100,90' }},.07); border:1px solid rgba({{ $isKotorProfit ? '63,207,142' : '232,100,90' }},.22);">
        <div class="lpr-label" style="margin-bottom:.05rem;">Laba Kotor</div>
        <div class="lpr-num lpr-kpi-val" style="color:{{ $isKotorProfit ? 'var(--emer2)' : 'var(--red2)' }};">
            {{ $r['labaKotor'] >= 0 ? '+' : '' }}Rp {{ number_format($r['labaKotor'],0,',','.') }}
        </div>
        <div style="margin-bottom:.6rem;">
            <span class="lpr-delta {{ $isKotorProfit ? 'lpr-badge-pos' : 'lpr-badge-neg' }}">
                @if($isKotorProfit)<svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" style="display:inline-block;vertical-align:middle"><line x1="12" y1="19" x2="12" y2="5"/><polyline points="5 12 12 5 19 12"/></svg>@else<svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" style="display:inline-block;vertical-align:middle"><line x1="12" y1="5" x2="12" y2="19"/><polyline points="19 12 12 19 5 12"/></svg>@endif {{ $r['marginPersen'] }}% margin
            </span>
        </div>
        <div class="lpr-flow-bar">
            <div style="height:100%; width:{{ $marginW }}%; background:{{ $isKotorProfit ? 'linear-gradient(90deg,#3fcf8e,rgba(63,207,142,.6))' : 'linear-gradient(90deg,#e8645a,rgba(232,100,90,.6))' }}; border-radius:4px; transition:width .5s;"></div>
        </div>
        <div style="display:flex; justify-content:space-between; margin-top:.35rem;">
            <span style="font-size:.6rem; color:var(--mut);">HPP {{ $r['totalPend'] > 0 ? round($r['totalHpp']/$r['totalPend']*100,1) : 0 }}%</span>
            <span style="font-size:.6rem; color:var(--mut);">Pend. Rp {{ number_format($r['totalPend'],0,',','.') }}</span>
        </div>
    </div>

    {{-- KPI 3: Biaya Ops --}}
    <div class="lpr-kpi" style="border:1px solid var(--line2); background:rgba(0,0,0,.18);">
        <div class="lpr-label" style="margin-bottom:.05rem;">Biaya Operasional</div>
        <div class="lpr-num lpr-kpi-val" style="color:var(--mut2);">(Rp {{ number_format($r['totalBiayaOps'],0,',','.') }})</div>
        <div style="margin-bottom:.6rem;">
            <span class="lpr-delta" style="background:rgba(255,255,255,.06); color:var(--mut); border:1px solid rgba(255,255,255,.08);">SDM · Utilitas · Admin · Sewa</span>
        </div>
        <div class="lpr-flow-bar">
            <div style="height:100%; width:{{ min($opsW,100) }}%; background:rgba(255,255,255,.15); border-radius:4px; transition:width .5s;"></div>
        </div>
        <div style="font-size:.6rem; color:var(--mut); margin-top:.35rem;">{{ $r['labaKotor'] > 0 ? round($r['totalBiayaOps']/$r['labaKotor']*100,1) : '—' }}% dari laba kotor</div>
    </div>

    {{-- KPI 4: Laba Bersih --}}
    <div class="lpr-kpi" style="background:rgba({{ $isProfit ? '217,164,65' : '232,100,90' }},.09); border:1.5px solid rgba({{ $isProfit ? '217,164,65' : '232,100,90' }},.3);">
        <div style="position:absolute;top:-15px;right:-15px;width:70px;height:70px;background:radial-gradient(circle,rgba({{ $isProfit ? '217,164,65' : '232,100,90' }},.08),transparent 70%);pointer-events:none;"></div>
        <div class="lpr-label" style="margin-bottom:.05rem; color:{{ $isProfit ? 'var(--gold2)' : 'var(--red2)' }};">Laba Bersih</div>
        <div class="lpr-num lpr-kpi-val" style="color:{{ $isProfit ? 'var(--gold2)' : 'var(--red2)' }};">
            {{ $r['labaBersih'] >= 0 ? '+' : '' }}Rp {{ number_format($r['labaBersih'],0,',','.') }}
        </div>
        <div style="margin-bottom:.6rem;">
            <span class="lpr-delta {{ $isProfit ? 'lpr-badge-gold' : 'lpr-badge-neg' }}">
                <x-i :name="$isProfit ? 'arrow-up' : 'arrow-down'" :size="13" /> {{ $isProfit ? 'Untung' : 'Rugi' }} Bulan Ini
            </span>
        </div>
        <div class="lpr-flow-bar">
            <div style="height:100%; width:{{ min($bersihW,100) }}%; background:{{ $isProfit ? 'linear-gradient(90deg,var(--gold),rgba(217,164,65,.6))' : 'linear-gradient(90deg,#e8645a,rgba(232,100,90,.5))' }}; border-radius:4px; transition:width .5s;"></div>
        </div>
        <div style="font-size:.6rem; color:var(--mut); margin-top:.35rem;">Net setelah seluruh biaya</div>
    </div>
</div>

{{-- ② P&L FLOW VISUALIZATION — Stripe-style waterfall --}}
<div class="glass-card" style="padding:1.1rem 1.4rem; margin-bottom:1.35rem;">
    <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:1rem;">
        <div>
            <div class="lpr-section-hd">Alur Laba Rugi · {{ $this->periode }}</div>
            <div style="font-size:.75rem; color:var(--ink); font-weight:600; margin-top:.2rem;">Pendapatan <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" style="display:inline-block;vertical-align:middle"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg> HPP <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" style="display:inline-block;vertical-align:middle"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg> Laba Kotor <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" style="display:inline-block;vertical-align:middle"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg> Biaya Ops <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" style="display:inline-block;vertical-align:middle"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg> Laba Bersih</div>
        </div>
        <span style="font-size:.58rem; color:var(--mut2); background:var(--bg2); border:1px solid var(--line); padding:.2rem .65rem; border-radius:.4rem; letter-spacing:.05em;">IFRS-like Format</span>
    </div>

    {{-- Waterfall bars --}}
    @php
        $maxVal = max($r['totalPend'], 1);
        $wPend  = 100;
        $wHpp   = $r['totalPend'] > 0 ? round($r['totalHpp']/$r['totalPend']*100,1) : 0;
        $wKotor = $r['totalPend'] > 0 ? round(abs($r['labaKotor'])/$r['totalPend']*100,1) : 0;
        $wOps   = $r['totalPend'] > 0 ? round($r['totalBiayaOps']/$r['totalPend']*100,1) : 0;
        $wBersih= $r['totalPend'] > 0 ? round(abs($r['labaBersih'])/$r['totalPend']*100,1) : 0;
    @endphp
    <div style="display:flex; flex-direction:column; gap:.55rem;">

        {{-- Row template macro --}}
        @php
        $flows = [
            ['label'=>'Pendapatan', 'sub'=>'BPJS Kronis + Tunai Pasien Umum', 'val'=>$r['totalPend'], 'w'=>$wPend, 'color'=>'#d9a441', 'alpha'=>'rgba(217,164,65,.55)', 'sign'=>''],
            ['label'=>'Harga Pokok Penjualan (HPP)', 'sub'=>'Biaya beli obat kronis + non-kronis', 'val'=>-$r['totalHpp'], 'w'=>$wHpp, 'color'=>'#e8645a', 'alpha'=>'rgba(232,100,90,.5)', 'sign'=>'−'],
            ['label'=>'Laba Kotor', 'sub'=>'Pendapatan − HPP · Margin '.$r['marginPersen'].'%', 'val'=>$r['labaKotor'], 'w'=>$wKotor, 'color'=>$isKotorProfit?'#3fcf8e':'#e8645a', 'alpha'=>$isKotorProfit?'rgba(63,207,142,.5)':'rgba(232,100,90,.45)', 'sign'=>$isKotorProfit?'+':''],
            ['label'=>'Biaya Operasional', 'sub'=>'SDM, utilitas, administrasi, sewa', 'val'=>-$r['totalBiayaOps'], 'w'=>$wOps, 'color'=>'#8fae9f', 'alpha'=>'rgba(143,174,159,.35)', 'sign'=>'−'],
            ['label'=>'Laba Bersih', 'sub'=>'Net setelah seluruh biaya', 'val'=>$r['labaBersih'], 'w'=>$wBersih, 'color'=>$isProfit?'#d9a441':'#e8645a', 'alpha'=>$isProfit?'rgba(217,164,65,.55)':'rgba(232,100,90,.5)', 'sign'=>$isProfit?'+':'', 'bold'=>true],
        ];
        @endphp

        @foreach($flows as $flow)
        <div style="display:flex; align-items:center; gap:.75rem;">
            <div style="min-width:210px; flex-shrink:0;">
                <div style="font-size:.78rem; font-weight:{{ isset($flow['bold']) && $flow['bold'] ? '800' : '600' }}; color:{{ isset($flow['bold']) && $flow['bold'] ? $flow['color'] : 'var(--ink)' }};">{{ $flow['label'] }}</div>
                <div style="font-size:.61rem; color:var(--mut);">{{ $flow['sub'] }}</div>
            </div>
            <div style="flex:1; height:24px; background:rgba(0,0,0,.2); border-radius:4px; overflow:hidden;">
                <div style="height:100%; width:{{ $flow['w'] }}%; background:{{ $flow['alpha'] }}; border-left:2.5px solid {{ $flow['color'] }}; border-radius:0 3px 3px 0; transition:width .5s cubic-bezier(.4,0,.2,1); display:flex; align-items:center; padding-left:.5rem;">
                </div>
            </div>
            <div class="lpr-num" style="min-width:140px; text-align:right; font-size:.82rem; font-weight:{{ isset($flow['bold']) && $flow['bold'] ? '800' : '700' }}; color:{{ $flow['color'] }}; white-space:nowrap;">
                {{ $flow['sign'] }}Rp {{ number_format(abs($flow['val']),0,',','.') }}
            </div>
        </div>
        @endforeach
    </div>
</div>

{{-- ③ 2-COL: P&L Statement + Segment Analysis --}}
<div style="display:grid; grid-template-columns:1.5fr 1fr; gap:1.1rem; margin-bottom:1.35rem; align-items:start;">

    {{-- LEFT: P&L Table --}}
    <div class="glass-card" style="overflow:hidden;">
        <div style="padding:.85rem 1.2rem; border-bottom:1px solid var(--line); background:rgba(0,0,0,.2); display:flex; align-items:center; justify-content:space-between;">
            <div>
                <div style="font-size:.8rem; font-weight:700; color:var(--ink);">Laporan Laba Rugi</div>
                <div style="font-size:.62rem; color:var(--mut); margin-top:.08rem;">{{ $this->periode }} · Klinik Dokterku PRB</div>
            </div>
            <span style="font-size:.58rem; color:var(--mut2); background:rgba(63,207,142,.08); border:1px solid rgba(63,207,142,.15); padding:.15rem .55rem; border-radius:.35rem; letter-spacing:.05em;">IFRS-like</span>
        </div>
        <table style="width:100%; border-collapse:collapse; font-variant-numeric:tabular-nums;">
        <tbody>

        {{-- PENDAPATAN --}}
        <tr style="background:rgba(63,207,142,.05);">
            <td colspan="3" style="padding:.4rem 1.1rem .25rem; border-left:3px solid #3fcf8e;">
                <span style="font-size:.57rem; text-transform:uppercase; letter-spacing:.1em; color:var(--emer2); font-weight:800;">I. Pendapatan</span>
            </td>
        </tr>
        <tr>
            <td style="padding:.5rem 1.1rem .5rem 1.8rem; border-left:3px solid rgba(63,207,142,.2);">
                <div style="font-weight:600; color:var(--emer2); font-size:.8rem;">Pendapatan BPJS / JKN — Kronis</div>
                <div style="font-size:.6rem; color:var(--mut); margin-top:.05rem;">Klaim × Faktor JF (PMK 3/2023) · Proyeksi bulanan</div>
            </td>
            <td class="lpr-num" style="text-align:right; padding:.5rem .5rem; color:var(--emer2); font-weight:700; font-size:.82rem; white-space:nowrap;">Rp {{ number_format($r['pendBpjs'],0,',','.') }}</td>
            <td style="text-align:right; padding:.5rem .85rem; width:52px;"><span class="lpr-pill lpr-badge-pos">{{ $bpjsPct }}%</span></td>
        </tr>
        <tr style="border-bottom:1px solid var(--line);">
            <td style="padding:.5rem 1.1rem .5rem 1.8rem; border-left:3px solid rgba(111,177,224,.2);">
                <div style="font-weight:600; color:var(--blue); font-size:.8rem;">Pendapatan Tunai — Pasien Umum</div>
                <div style="font-size:.6rem; color:var(--mut); margin-top:.05rem;">Stok keluar aktual bulan ini</div>
            </td>
            <td class="lpr-num" style="text-align:right; padding:.5rem .5rem; color:var(--blue); font-weight:700; font-size:.82rem; white-space:nowrap;">Rp {{ number_format($r['pendTunai'],0,',','.') }}</td>
            <td style="text-align:right; padding:.5rem .85rem;"><span class="lpr-pill lpr-badge-blue">{{ $tunaiPct }}%</span></td>
        </tr>
        <tr style="background:rgba(217,164,65,.05); border-bottom:2px solid rgba(217,164,65,.2);">
            <td style="padding:.5rem 1.1rem; font-weight:800; color:var(--gold2); font-size:.82rem;">TOTAL PENDAPATAN</td>
            <td class="lpr-num" style="text-align:right; padding:.5rem .5rem; font-size:.88rem; font-weight:800; color:var(--gold2); white-space:nowrap;">Rp {{ number_format($r['totalPend'],0,',','.') }}</td>
            <td style="padding:.5rem .85rem;"></td>
        </tr>

        {{-- HPP --}}
        <tr style="background:rgba(232,100,90,.03);">
            <td colspan="3" style="padding:.4rem 1.1rem .25rem; border-left:3px solid rgba(232,100,90,.5);">
                <span style="font-size:.57rem; text-transform:uppercase; letter-spacing:.1em; color:var(--red2); font-weight:800;">II. Harga Pokok Penjualan (HPP)</span>
            </td>
        </tr>
        <tr>
            <td style="padding:.42rem 1.1rem .42rem 1.8rem; color:var(--mut2); font-size:.8rem; border-left:3px solid rgba(232,100,90,.12);">HPP Obat Kronis</td>
            <td class="lpr-num" style="text-align:right; padding:.42rem .5rem; color:var(--mut2); font-size:.8rem; white-space:nowrap;">(Rp {{ number_format($r['hppBpjs'],0,',','.') }})</td>
            <td></td>
        </tr>
        <tr style="border-bottom:1px solid var(--line);">
            <td style="padding:.42rem 1.1rem .42rem 1.8rem; color:var(--mut2); font-size:.8rem; border-left:3px solid rgba(232,100,90,.12);">HPP Obat Non-Kronis</td>
            <td class="lpr-num" style="text-align:right; padding:.42rem .5rem; color:var(--mut2); font-size:.8rem; white-space:nowrap;">(Rp {{ number_format($r['hppTunai'],0,',','.') }})</td>
            <td></td>
        </tr>
        <tr style="border-bottom:2px solid var(--line);">
            <td style="padding:.5rem 1.1rem; font-weight:600; color:var(--red2); font-size:.8rem;">TOTAL HPP</td>
            <td class="lpr-num" style="text-align:right; padding:.5rem .5rem; font-weight:700; color:var(--red2); font-size:.82rem; white-space:nowrap;">(Rp {{ number_format($r['totalHpp'],0,',','.') }})</td>
            <td></td>
        </tr>

        {{-- LABA KOTOR --}}
        <tr style="background:rgba({{ $isKotorProfit ? '63,207,142' : '232,100,90' }},.07); border-bottom:2px solid rgba({{ $isKotorProfit ? '63,207,142' : '232,100,90' }},.15);">
            <td style="padding:.55rem 1.1rem; border-left:3px solid {{ $isKotorProfit ? '#3fcf8e' : '#e8645a' }};">
                <span style="font-size:.82rem; font-weight:800; color:{{ $isKotorProfit ? 'var(--emer2)' : 'var(--red2)' }};">III. LABA KOTOR</span>
            </td>
            <td class="lpr-num" style="text-align:right; padding:.55rem .5rem; font-weight:800; font-size:.88rem; color:{{ $isKotorProfit ? 'var(--emer2)' : 'var(--red2)' }}; white-space:nowrap;">
                {{ $r['labaKotor'] >= 0 ? '+' : '' }}Rp {{ number_format($r['labaKotor'],0,',','.') }}
            </td>
            <td style="text-align:right; padding:.55rem .85rem;"><span style="font-size:.72rem; font-weight:800; color:{{ $r['marginPersen'] >= 0 ? 'var(--emer2)' : 'var(--red2)' }};">{{ $r['marginPersen'] }}%</span></td>
        </tr>

        {{-- BIAYA OPS --}}
        <tr style="border-bottom:1px solid var(--line);">
            <td style="padding:.48rem 1.1rem .48rem 1.8rem; border-left:3px solid rgba(255,255,255,.06);">
                <div style="color:var(--mut2); font-size:.8rem;">IV. Biaya Operasional</div>
                <div style="font-size:.6rem; color:var(--mut);">SDM · utilitas · admin · sewa</div>
            </td>
            <td class="lpr-num" style="text-align:right; padding:.48rem .5rem; color:var(--mut2); font-size:.8rem; white-space:nowrap;">(Rp {{ number_format($r['totalBiayaOps'],0,',','.') }})</td>
            <td></td>
        </tr>

        {{-- LABA BERSIH --}}
        <tr style="background:rgba({{ $isProfit ? '217,164,65' : '232,100,90' }},.09);">
            <td style="padding:.7rem 1.1rem; border-left:3px solid {{ $isProfit ? 'var(--gold2)' : '#e8645a' }};">
                <div style="font-size:.84rem; font-weight:800; color:{{ $isProfit ? 'var(--gold2)' : 'var(--red2)' }};">V. LABA BERSIH</div>
                <div style="font-size:.6rem; color:var(--mut); margin-top:.07rem;">Setelah seluruh biaya operasional</div>
            </td>
            <td class="lpr-num" style="text-align:right; padding:.7rem .5rem; font-weight:800; font-size:.92rem; color:{{ $isProfit ? 'var(--gold2)' : 'var(--red2)' }}; white-space:nowrap;">
                {{ $r['labaBersih'] >= 0 ? '+' : '' }}Rp {{ number_format($r['labaBersih'],0,',','.') }}
            </td>
            <td></td>
        </tr>

        </tbody>
        </table>
    </div>

    {{-- RIGHT: Segment Cards + PO --}}
    <div style="display:flex; flex-direction:column; gap:.9rem;">

        {{-- Segment A: BPJS Kronis --}}
        <div style="background:linear-gradient(145deg,rgba(63,207,142,.1),rgba(63,207,142,.03)); border:1px solid rgba(63,207,142,.28); border-radius:.95rem; padding:1.1rem 1.2rem;">
            <div style="display:flex; align-items:flex-start; justify-content:space-between; margin-bottom:.75rem;">
                <div>
                    <div style="font-size:.57rem; text-transform:uppercase; letter-spacing:.1em; color:var(--emer2); font-weight:700; margin-bottom:.1rem;">Segmen A</div>
                    <div style="font-size:.88rem; font-weight:700; color:var(--ink);">BPJS / Obat Kronis</div>
                </div>
                <span class="lpr-pill lpr-badge-pos">JKN · Non-Tunai</span>
            </div>
            <div class="lpr-num" style="font-size:1.2rem; font-weight:800; color:var(--emer2); margin-bottom:.55rem; letter-spacing:-.025em;">Rp {{ number_format($r['pendBpjs'],0,',','.') }}</div>
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:.4rem; margin-bottom:.55rem;">
                <div style="background:rgba(0,0,0,.2); border-radius:.5rem; padding:.45rem .65rem;">
                    <div style="font-size:.57rem; color:var(--mut); margin-bottom:.08rem;">HPP</div>
                    <div class="lpr-num" style="font-size:.76rem; color:var(--mut2);">({{ number_format($r['hppBpjs'],0,',','.') }})</div>
                </div>
                <div style="background:rgba(63,207,142,.08); border-radius:.5rem; padding:.45rem .65rem;">
                    <div style="font-size:.57rem; color:var(--mut); margin-bottom:.08rem;">Laba</div>
                    <div class="lpr-num" style="font-size:.76rem; font-weight:700; color:{{ $r['labaBpjs'] >= 0 ? 'var(--emer2)' : 'var(--red2)' }};">{{ $r['labaBpjs'] >= 0 ? '+' : '' }}{{ number_format($r['labaBpjs'],0,',','.') }}</div>
                </div>
            </div>
            <div style="display:flex; align-items:center; gap:.5rem;">
                <div style="flex:1; height:5px; background:rgba(0,0,0,.3); border-radius:3px; overflow:hidden;">
                    <div style="height:100%; width:{{ min(abs($r['marginBpjs']),100) }}%; background:linear-gradient(90deg,#3fcf8e,rgba(63,207,142,.5)); border-radius:3px;"></div>
                </div>
                <span class="lpr-num" style="font-size:.7rem; font-weight:800; color:var(--emer2); white-space:nowrap;">{{ $r['marginBpjs'] }}% margin</span>
            </div>
        </div>

        {{-- Segment B: Tunai --}}
        <div style="background:linear-gradient(145deg,rgba(111,177,224,.1),rgba(111,177,224,.03)); border:1px solid rgba(111,177,224,.28); border-radius:.95rem; padding:1.1rem 1.2rem;">
            <div style="display:flex; align-items:flex-start; justify-content:space-between; margin-bottom:.75rem;">
                <div>
                    <div style="font-size:.57rem; text-transform:uppercase; letter-spacing:.1em; color:var(--blue); font-weight:700; margin-bottom:.1rem;">Segmen B</div>
                    <div style="font-size:.88rem; font-weight:700; color:var(--ink);">Tunai / Pasien Umum</div>
                </div>
                <span class="lpr-pill lpr-badge-blue">Kas · Langsung</span>
            </div>
            @if($r['pendTunai'] == 0)
            <div style="text-align:center; padding:.75rem 0; color:var(--mut); font-size:.75rem;">
                Belum ada transaksi.<br>
                <a href="{{ route('stok-keluar.index') }}" style="color:var(--blue);"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" style="display:inline-block;vertical-align:middle"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg> Catat Stok Keluar</a>
            </div>
            @else
            <div class="lpr-num" style="font-size:1.2rem; font-weight:800; color:var(--blue); margin-bottom:.55rem; letter-spacing:-.025em;">Rp {{ number_format($r['pendTunai'],0,',','.') }}</div>
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:.4rem; margin-bottom:.55rem;">
                <div style="background:rgba(0,0,0,.2); border-radius:.5rem; padding:.45rem .65rem;">
                    <div style="font-size:.57rem; color:var(--mut); margin-bottom:.08rem;">HPP</div>
                    <div class="lpr-num" style="font-size:.76rem; color:var(--mut2);">({{ number_format($r['hppTunai'],0,',','.') }})</div>
                </div>
                <div style="background:rgba(111,177,224,.08); border-radius:.5rem; padding:.45rem .65rem;">
                    <div style="font-size:.57rem; color:var(--mut); margin-bottom:.08rem;">Laba</div>
                    <div class="lpr-num" style="font-size:.76rem; font-weight:700; color:{{ $r['labaTunai'] >= 0 ? 'var(--blue)' : 'var(--red2)' }};">{{ $r['labaTunai'] >= 0 ? '+' : '' }}{{ number_format($r['labaTunai'],0,',','.') }}</div>
                </div>
            </div>
            <div style="display:flex; align-items:center; gap:.5rem;">
                <div style="flex:1; height:5px; background:rgba(0,0,0,.3); border-radius:3px; overflow:hidden;">
                    <div style="height:100%; width:{{ min(abs($r['marginTunai']),100) }}%; background:linear-gradient(90deg,#6fb1e0,rgba(111,177,224,.5)); border-radius:3px;"></div>
                </div>
                <span class="lpr-num" style="font-size:.7rem; font-weight:800; color:var(--blue); white-space:nowrap;">{{ $r['marginTunai'] }}% margin</span>
            </div>
            @endif
        </div>

        {{-- Pengadaan PO summary --}}
        <div class="glass-card" style="padding:.95rem 1.1rem;">
            <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:.7rem;">
                <div class="lpr-section-hd">Realisasi Pengadaan (PO)</div>
                <span class="lpr-pill" style="background:rgba(111,177,224,.1); color:var(--blue); border:1px solid rgba(111,177,224,.2);">Bulan Ini</span>
            </div>
            <div style="display:flex; justify-content:space-between; align-items:baseline; margin-bottom:.5rem;">
                <span style="font-size:.75rem; color:var(--ink); font-weight:600;">Total PO</span>
                <span class="lpr-num" style="font-size:.82rem; font-weight:700; color:var(--blue);">Rp {{ number_format($r['pengeluaran'],0,',','.') }}</span>
            </div>
            @if($r['pengeluaran'] > 0)
            <div style="height:5px; background:rgba(0,0,0,.25); border-radius:3px; overflow:hidden; display:flex; gap:1px; margin-bottom:.5rem;">
                <div style="width:{{ round($r['pengeluaranBpjs']/$r['pengeluaran']*100,1) }}%; background:rgba(63,207,142,.6); border-radius:3px 0 0 3px;"></div>
                <div style="flex:1; background:rgba(111,177,224,.5); border-radius:0 3px 3px 0;"></div>
            </div>
            @endif
            <div style="display:flex; flex-direction:column; gap:.28rem;">
                <div style="display:flex; justify-content:space-between; align-items:center;">
                    <div style="display:flex; align-items:center; gap:.35rem; font-size:.72rem; color:var(--mut2);">
                        <div style="width:7px; height:7px; background:#3fcf8e; border-radius:1px;"></div>PO Kronis
                    </div>
                    <span class="lpr-num" style="font-size:.72rem; color:var(--emer2);">{{ number_format($r['pengeluaranBpjs'],0,',','.') }}
                        @if($r['pengeluaran'] > 0)<span style="color:var(--mut); font-size:.65rem;"> ({{ round($r['pengeluaranBpjs']/$r['pengeluaran']*100,0) }}%)</span>@endif
                    </span>
                </div>
                <div style="display:flex; justify-content:space-between; align-items:center;">
                    <div style="display:flex; align-items:center; gap:.35rem; font-size:.72rem; color:var(--mut2);">
                        <div style="width:7px; height:7px; background:#6fb1e0; border-radius:1px;"></div>PO Non-Kronis
                    </div>
                    <span class="lpr-num" style="font-size:.72rem; color:var(--blue);">{{ number_format($r['pengeluaranUmum'],0,',','.') }}
                        @if($r['pengeluaran'] > 0)<span style="color:var(--mut); font-size:.65rem;"> ({{ round($r['pengeluaranUmum']/$r['pengeluaran']*100,0) }}%)</span>@endif
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ⑤ TOP PERFORMERS TABLES --}}
<div style="display:grid; grid-template-columns:1fr 1fr; gap:1.1rem;">

    {{-- Top Laba --}}
    <div class="glass-card" style="overflow:hidden;">
        <div style="padding:.85rem 1.2rem; border-bottom:1px solid var(--line); display:flex; align-items:center; justify-content:space-between; background:rgba(0,0,0,.15);">
            <div style="display:flex; align-items:center; gap:.5rem;">
                <div style="width:6px; height:6px; border-radius:50%; background:var(--emer2);"></div>
                <span style="font-size:.7rem; color:var(--emer2); text-transform:uppercase; letter-spacing:.07em; font-weight:700;">Top 10 Laba Tertinggi</span>
            </div>
            <span style="font-size:.63rem; color:var(--mut);">dari item diserahkan bulan ini</span>
        </div>
        @if($this->topLaba->isEmpty())
        <div style="padding:2rem; text-align:center; color:var(--mut); font-size:.78rem;">Belum ada data pengambilan obat bulan ini.</div>
        @else
        <table class="lpr-table">
            <tbody>
                @foreach($this->topLaba as $i => $o)
                <tr>
                    <td style="color:var(--mut); width:22px; font-size:.7rem; font-weight:600; padding-right:0;">{{ $i+1 }}</td>
                    <td style="font-size:.8rem; font-weight:600;">{{ $o->nama_obat }}</td>
                    <td class="lpr-num" style="text-align:right; font-size:.78rem; color:var(--emer2); font-weight:700;">+{{ number_format($o->laba,0,',','.') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif
    </div>

    {{-- Obat Rugi --}}
    <div class="glass-card" style="overflow:hidden;">
        <div style="padding:.85rem 1.2rem; border-bottom:1px solid var(--line); display:flex; align-items:center; justify-content:space-between; background:rgba(0,0,0,.15);">
            <div style="display:flex; align-items:center; gap:.5rem;">
                <div style="width:6px; height:6px; border-radius:50%; background:var(--red2);"></div>
                <span style="font-size:.7rem; color:var(--red2); text-transform:uppercase; letter-spacing:.07em; font-weight:700;">Obat Rugi — Perlu Review</span>
            </div>
            <span style="font-size:.63rem; color:var(--mut);">cek harga beli</span>
        </div>
        @if($this->topRugi->isEmpty())
        <div style="padding:2.5rem; text-align:center; color:var(--emer2); font-size:.82rem;">
            <svg width="28" height="28" fill="none" stroke="#3fcf8e" stroke-width="1.5" viewBox="0 0 24 24" style="display:block; margin:0 auto .5rem; opacity:.7;"><polyline points="20 6 9 17 4 12"/></svg>
            Tidak ada obat rugi bulan ini
        </div>
        @else
        <table class="lpr-table">
            <tbody>
                @foreach($this->topRugi as $i => $o)
                <tr>
                    <td style="color:var(--mut); width:22px; font-size:.7rem; font-weight:600; padding-right:0;">{{ $i+1 }}</td>
                    <td style="font-size:.8rem; font-weight:600;">{{ $o->nama_obat }}</td>
                    <td class="lpr-num" style="text-align:right; font-size:.78rem; color:var(--red2); font-weight:700;">{{ number_format($o->laba,0,',','.') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif
    </div>
</div>

{{-- ④ CHARTS: Tren + Donut --}}
<div style="display:grid; grid-template-columns:2fr 1fr; gap:1.1rem; margin-top:1.35rem; margin-bottom:1.35rem;">

    {{-- Area Trend Chart --}}
    <div class="glass-card" style="padding:1.2rem 1.35rem;">
        <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:1rem;">
            <div>
                <div class="lpr-section-hd">Tren Pendapatan &amp; Pengeluaran</div>
                <div style="font-size:.73rem; color:var(--ink); font-weight:600; margin-top:.15rem;">6 Bulan Terakhir</div>
            </div>
            <div style="display:flex; gap:.75rem; align-items:center;">
                <div style="display:flex; align-items:center; gap:.3rem; font-size:.67rem; color:var(--mut2);">
                    <div style="width:8px; height:8px; border-radius:2px; background:#3fcf8e;"></div>Kronis
                </div>
                <div style="display:flex; align-items:center; gap:.3rem; font-size:.67rem; color:var(--mut2);">
                    <div style="width:8px; height:8px; border-radius:2px; background:#6fb1e0;"></div>Tunai
                </div>
                <div style="display:flex; align-items:center; gap:.3rem; font-size:.67rem; color:var(--mut2);">
                    <div style="width:8px; height:8px; border-radius:2px; background:#e8645a;"></div>PO
                </div>
            </div>
        </div>
        <canvas id="chartTren" style="max-height:240px;"></canvas>
    </div>

    {{-- Donut + legend --}}
    <div class="glass-card" style="padding:1.2rem 1.35rem; display:flex; flex-direction:column;">
        <div class="lpr-section-hd" style="margin-bottom:.2rem;">Komposisi Pendapatan</div>
        <div style="font-size:.73rem; color:var(--ink); font-weight:600; margin-bottom:.85rem;">{{ $this->periode }}</div>
        <canvas id="chartDonut" style="max-height:170px; margin:auto;"></canvas>
        <div style="margin-top:.85rem; display:flex; flex-direction:column; gap:.5rem;">
            <div style="display:flex; align-items:center; justify-content:space-between;">
                <div style="display:flex; align-items:center; gap:.4rem;">
                    <div style="width:10px; height:10px; border-radius:2px; background:rgba(63,207,142,.8);"></div>
                    <span style="font-size:.72rem; color:var(--mut2);">BPJS / Obat Kronis</span>
                </div>
                <span class="lpr-num" style="font-size:.72rem; font-weight:700; color:var(--emer2);">{{ $bpjsPct }}%</span>
            </div>
            <div style="height:4px; background:rgba(0,0,0,.3); border-radius:2px; overflow:hidden;">
                <div style="height:100%; width:{{ $bpjsPct }}%; background:rgba(63,207,142,.7); border-radius:2px;"></div>
            </div>
            <div style="display:flex; align-items:center; justify-content:space-between; margin-top:.2rem;">
                <div style="display:flex; align-items:center; gap:.4rem;">
                    <div style="width:10px; height:10px; border-radius:2px; background:rgba(111,177,224,.8);"></div>
                    <span style="font-size:.72rem; color:var(--mut2);">Tunai / Pasien Umum</span>
                </div>
                <span class="lpr-num" style="font-size:.72rem; font-weight:700; color:var(--blue);">{{ $tunaiPct }}%</span>
            </div>
            <div style="height:4px; background:rgba(0,0,0,.3); border-radius:2px; overflow:hidden;">
                <div style="height:100%; width:{{ $tunaiPct }}%; background:rgba(111,177,224,.7); border-radius:2px;"></div>
            </div>
        </div>
        {{-- Total summary --}}
        <div style="margin-top:1rem; padding:.65rem .8rem; background:rgba(0,0,0,.2); border-radius:.6rem; border:1px solid var(--line);">
            <div style="font-size:.6rem; color:var(--mut); margin-bottom:.2rem;">Total Pendapatan</div>
            <div class="lpr-num" style="font-size:.88rem; font-weight:800; color:var(--gold2);">Rp {{ number_format($r['totalPend'],0,',','.') }}</div>
        </div>
    </div>
</div>

{{-- ⑥ REKONSILIASI BPJS — Siklus Klaim --}}
@php
$rekonStatus = $r['statusRekon'];
$statusColors = [
    'belum_diajukan' => ['bg'=>'rgba(143,174,159,.06)','border'=>'rgba(143,174,159,.2)','color'=>'var(--mut2)','label'=>'Belum Diajukan'],
    'draft'          => ['bg'=>'rgba(111,177,224,.06)','border'=>'rgba(111,177,224,.2)','color'=>'var(--blue)','label'=>'Draft'],
    'diajukan'       => ['bg'=>'rgba(217,164,65,.07)','border'=>'rgba(217,164,65,.22)','color'=>'var(--gold2)','label'=>'Diajukan ke BPJS'],
    'dibayar'        => ['bg'=>'rgba(63,207,142,.08)','border'=>'rgba(63,207,142,.22)','color'=>'var(--emer2)','label'=>'Dibayar BPJS'],
    'selisih'        => ['bg'=>'rgba(232,100,90,.07)','border'=>'rgba(232,100,90,.2)','color'=>'var(--red2)','label'=>'Ada Selisih'],
];
$sc = $statusColors[$rekonStatus] ?? $statusColors['belum_diajukan'];
@endphp
<div style="background:{{ $sc['bg'] }}; border:1px solid {{ $sc['border'] }}; border-radius:1rem; padding:1.2rem 1.4rem; margin-top:1.35rem;">
    <div style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:.75rem; margin-bottom:1rem;">
        <div>
            <div style="font-size:.6rem; text-transform:uppercase; letter-spacing:.1em; color:{{ $sc['color'] }}; font-weight:700; margin-bottom:.15rem;">Rekonsiliasi BPJS / JKN — {{ $this->periode }}</div>
            <div style="font-size:.88rem; font-weight:700; color:var(--ink);">Siklus Klaim: Pengajuan <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" style="display:inline-block;vertical-align:middle"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg> Persetujuan <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" style="display:inline-block;vertical-align:middle"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg> Pembayaran</div>
        </div>
        <span style="font-size:.7rem; font-weight:700; padding:.3rem .85rem; border-radius:.45rem;
                     background:{{ $sc['bg'] }}; color:{{ $sc['color'] }}; border:1px solid {{ $sc['border'] }};">
            {{ $sc['label'] }}
        </span>
    </div>

    @if($r['isPending'])
    <div style="background:rgba(217,164,65,.08); border:1px solid rgba(217,164,65,.2); border-radius:.65rem; padding:.65rem .9rem; margin-bottom:.9rem; display:flex; align-items:flex-start; gap:.6rem;">
        <svg width="15" height="15" fill="none" stroke="#d9a441" stroke-width="2" viewBox="0 0 24 24" style="flex-shrink:0; margin-top:.05rem;"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
        <div style="font-size:.72rem; color:var(--gold2); line-height:1.5;">
            <strong>Bulan berjalan — pembayaran BPJS pending.</strong>
            Klaim bulan <em>{{ $this->periode }}</em> baru dibayar di bulan berikutnya setelah verifikasi.
            Angka di bawah adalah <strong>proyeksi estimasi</strong> berdasarkan obat yang sudah diserahkan ke pasien.
        </div>
    </div>
    @endif

    <div style="display:grid; grid-template-columns:repeat(4,1fr); gap:.75rem;">

        {{-- Proyeksi --}}
        <div style="background:rgba(0,0,0,.2); border-radius:.7rem; padding:.8rem .95rem; border:1px solid var(--line);">
            <div style="font-size:.57rem; text-transform:uppercase; letter-spacing:.08em; color:var(--mut); font-weight:700; margin-bottom:.3rem;">Proyeksi Klaim</div>
            <div class="lpr-num" style="font-size:.92rem; font-weight:800; color:var(--blue);">Rp {{ number_format($r['proyeksiBpjs'],0,',','.') }}</div>
            <div style="font-size:.6rem; color:var(--mut); margin-top:.2rem;">Dari obat diserahkan bulan ini</div>
        </div>

        {{-- Diajukan --}}
        <div style="background:rgba(0,0,0,.2); border-radius:.7rem; padding:.8rem .95rem; border:1px solid var(--line);">
            <div style="font-size:.57rem; text-transform:uppercase; letter-spacing:.08em; color:var(--mut); font-weight:700; margin-bottom:.3rem;">Tagihan Diajukan</div>
            <div class="lpr-num" style="font-size:.92rem; font-weight:800; color:var(--gold2);">
                {{ $r['diajukanBpjs'] > 0 ? 'Rp '.number_format($r['diajukanBpjs'],0,',','.') : '—' }}
            </div>
            <div style="font-size:.6rem; color:var(--mut); margin-top:.2rem;">Nilai klaim ke BPJS</div>
        </div>

        {{-- Dibayar --}}
        <div style="background:rgba(63,207,142,.05); border-radius:.7rem; padding:.8rem .95rem; border:1px solid rgba(63,207,142,.15);">
            <div style="font-size:.57rem; text-transform:uppercase; letter-spacing:.08em; color:var(--emer2); font-weight:700; margin-bottom:.3rem;">Dibayar BPJS</div>
            <div class="lpr-num" style="font-size:.92rem; font-weight:800; color:var(--emer2);">
                {{ $r['pendBpjs'] > 0 ? 'Rp '.number_format($r['pendBpjs'],0,',','.') : ($r['isPending'] ? 'Pending' : '—') }}
            </div>
            <div style="font-size:.6rem; color:var(--mut); margin-top:.2rem;">Revenue aktual BPJS</div>
        </div>

        {{-- Selisih --}}
        @php $selisih = $r['selisihRekon']; $isPositifSelisih = $selisih >= 0; @endphp
        <div style="background:rgba({{ $isPositifSelisih ? '63,207,142' : '232,100,90' }},.05); border-radius:.7rem; padding:.8rem .95rem; border:1px solid rgba({{ $isPositifSelisih ? '63,207,142' : '232,100,90' }},.15);">
            <div style="font-size:.57rem; text-transform:uppercase; letter-spacing:.08em; color:{{ $isPositifSelisih ? 'var(--emer2)' : 'var(--red2)' }}; font-weight:700; margin-bottom:.3rem;">Selisih Rekon</div>
            <div class="lpr-num" style="font-size:.92rem; font-weight:800; color:{{ $isPositifSelisih ? 'var(--emer2)' : 'var(--red2)' }};">
                {{ $r['diajukanBpjs'] > 0 ? (($selisih >= 0 ? '+' : '').number_format($selisih,0,',','.')) : '—' }}
            </div>
            <div style="font-size:.6rem; color:var(--mut); margin-top:.2rem;">Dibayar − Diajukan</div>
        </div>
    </div>

    {{-- Siklus timeline visual --}}
    <div style="margin-top:.9rem; padding:.65rem .9rem; background:rgba(0,0,0,.15); border-radius:.6rem; border:1px solid var(--line);">
        <div style="font-size:.6rem; color:var(--mut); font-weight:700; text-transform:uppercase; letter-spacing:.08em; margin-bottom:.5rem;">Alur Siklus BPJS</div>
        <div style="display:flex; align-items:center; gap:.35rem; flex-wrap:wrap;">
            @php
            $steps = [
                ['label'=>'Obat Diserahkan','done'=>true,'color'=>'#3fcf8e'],
                ['label'=>'Klaim Diajukan','done'=>in_array($rekonStatus,['diajukan','dibayar','selisih']),'color'=>'#d9a441'],
                ['label'=>'BPJS Verifikasi','done'=>in_array($rekonStatus,['dibayar','selisih']),'color'=>'#6fb1e0'],
                ['label'=>'Pembayaran Masuk','done'=>$rekonStatus==='dibayar'||$rekonStatus==='selisih','color'=>'#3fcf8e'],
            ];
            @endphp
            @foreach($steps as $si => $step)
            <div style="display:flex; align-items:center; gap:.25rem;">
                <div style="width:18px; height:18px; border-radius:50%;
                            background:{{ $step['done'] ? $step['color'] : 'rgba(255,255,255,.07)' }};
                            border:1.5px solid {{ $step['done'] ? $step['color'] : 'var(--line)' }};
                            display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                    @if($step['done'])
                    <svg width="9" height="9" fill="none" stroke="#0a1410" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
                    @else
                    <div style="width:5px; height:5px; border-radius:50%; background:var(--line);"></div>
                    @endif
                </div>
                <span style="font-size:.67rem; font-weight:600; color:{{ $step['done'] ? $step['color'] : 'var(--mut)' }}; white-space:nowrap;">{{ $step['label'] }}</span>
            </div>
            @if($si < count($steps)-1)
            <div style="flex:1; min-width:16px; height:1px; background:var(--line);"></div>
            @endif
            @endforeach
        </div>
        <div style="margin-top:.5rem; font-size:.63rem; color:var(--mut2);">
            Klaim bulan <strong style="color:var(--ink);">{{ $this->periode }}</strong> umumnya dibayar pada bulan berikutnya.
            Akibatnya, bulan berjalan cenderung negatif sampai klaim disetujui dan masuk rekening.
        </div>
    </div>
</div>

@endif {{-- end ringkasan --}}


{{-- ═══════════════════════════════════════════════════════════ --}}
{{-- TAB: OBAT KRONIS                                           --}}
{{-- ═══════════════════════════════════════════════════════════ --}}
@if($activeTab === 'bpjs')
@php
    $kTotalPend  = $this->detailBpjs->sum('pendapatan');
    $kTotalBiaya = $this->detailBpjs->sum('biaya');
    $kTotalLaba  = $this->detailBpjs->sum('laba');
    $kLaba       = $this->detailBpjs->filter(fn($x) => $x['laba'] > 0)->count();
    $kRugi       = $this->detailBpjs->filter(fn($x) => $x['laba'] < 0)->count();
@endphp

<div class="grid-kpi" style="margin-bottom:1.35rem;">
    <div class="lpr-kpi" style="border:1px solid rgba(63,207,142,.22); background:rgba(63,207,142,.06);">
        <div class="lpr-label">Proyeksi Pendapatan</div>
        <div class="lpr-num lpr-kpi-val" style="color:var(--emer2);">Rp {{ number_format($kTotalPend,0,',','.') }}</div>
        <div style="font-size:.63rem; color:var(--mut);">BPJS/JKN · Formula PMK 3/2023</div>
    </div>
    <div class="lpr-kpi" style="border:1px solid rgba(232,100,90,.18); background:rgba(232,100,90,.05);">
        <div class="lpr-label">Total HPP</div>
        <div class="lpr-num lpr-kpi-val" style="color:var(--mut2);">(Rp {{ number_format($kTotalBiaya,0,',','.') }})</div>
        <div style="font-size:.63rem; color:var(--mut);">Biaya beli seluruh obat kronis</div>
    </div>
    <div class="lpr-kpi" style="border:1px solid rgba(217,164,65,.22); background:rgba(217,164,65,.06);">
        <div class="lpr-label">Laba Segmen Kronis</div>
        <div class="lpr-num lpr-kpi-val" style="color:{{ $kTotalLaba >= 0 ? 'var(--gold2)' : 'var(--red2)' }};">
            {{ $kTotalLaba >= 0 ? '+' : '' }}Rp {{ number_format($kTotalLaba,0,',','.') }}
        </div>
        <div style="font-size:.63rem; color:var(--mut);">Margin {{ $kTotalPend > 0 ? round($kTotalLaba/$kTotalPend*100,1) : 0 }}%</div>
    </div>
    <div class="lpr-kpi" style="border:1px solid var(--line2); background:rgba(0,0,0,.15);">
        <div class="lpr-label">Status Obat</div>
        <div style="font-size:1.35rem; font-weight:800; margin:.35rem 0 .25rem;">
            <span style="color:var(--emer2);">{{ $kLaba }}</span><span style="color:var(--mut); font-size:.85rem;"> laba</span>
            <span style="color:var(--line2); margin:0 .2rem;">/</span>
            <span style="color:var(--red2);">{{ $kRugi }}</span><span style="color:var(--mut); font-size:.85rem;"> rugi</span>
        </div>
        <div style="font-size:.63rem; color:var(--mut);">dari {{ $this->detailBpjs->count() }} obat kronis aktif</div>
    </div>
</div>

<div class="glass-card">
    <div style="padding:.9rem 1.2rem; border-bottom:1px solid var(--line); display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:.5rem; background:rgba(0,0,0,.15);">
        <div>
            <span style="font-size:.73rem; color:var(--emer2); text-transform:uppercase; letter-spacing:.07em; font-weight:700;">Analisis Laba Per Obat — BPJS/JKN</span>
            <span style="font-size:.68rem; color:var(--mut); margin-left:.75rem;">{{ $this->periode }}</span>
        </div>
        <div style="font-size:.67rem; color:var(--mut2); background:rgba(63,207,142,.07); border:1px solid rgba(63,207,142,.15); padding:.2rem .65rem; border-radius:.4rem; font-weight:600;">
            Bayar BPJS = Klaim/Unit × Faktor JF (PMK 3/2023)
        </div>
    </div>
    <div style="overflow-x:auto;">
    <table class="lpr-table" style="font-size:.78rem;">
        <thead>
            <tr>
                <th style="text-align:left;">Nama Obat</th>
                <th>Diagnosis</th>
                <th style="text-align:right;">Pasien</th>
                <th style="text-align:right;">Unit/Bln</th>
                <th style="text-align:right;">Klaim/Unit</th>
                <th style="text-align:right;">Faktor JF</th>
                <th style="text-align:right; color:var(--emer2);">Bayar BPJS</th>
                <th style="text-align:right;">Harga Beli</th>
                <th style="text-align:right; color:var(--emer2);">Pend./Bln</th>
                <th style="text-align:right; color:var(--red2);">Biaya/Bln</th>
                <th style="text-align:right; color:var(--gold2);">Laba/Bln</th>
            </tr>
        </thead>
        <tbody>
            @php $totalPend2 = 0; $totalBiaya2 = 0; $totalLaba2 = 0; @endphp
            @foreach($this->detailBpjs as $row)
            @php $totalPend2 += $row['pendapatan']; $totalBiaya2 += $row['biaya']; $totalLaba2 += $row['laba']; @endphp
            <tr>
                <td style="font-weight:600;">{{ $row['nama'] }}</td>
                <td><span class="lpr-pill" style="background:var(--bg2); color:var(--mut2); border:1px solid var(--line);">{{ $row['kategori'] }}</span></td>
                <td class="lpr-num" style="text-align:right;">{{ $row['pasien'] }}</td>
                <td class="lpr-num" style="text-align:right;">{{ $row['unit'] }}</td>
                <td class="lpr-num" style="text-align:right; font-size:.74rem;">{{ number_format($row['klaim'],0,',','.') }}</td>
                <td class="lpr-num" style="text-align:right; font-size:.74rem; color:var(--mut2);">{{ $row['faktor'] }}</td>
                <td class="lpr-num" style="text-align:right; font-size:.74rem; color:var(--emer2);">{{ number_format($row['bayar_bpjs'],0,',','.') }}</td>
                <td class="lpr-num" style="text-align:right; font-size:.74rem; color:var(--mut2);">{{ number_format($row['harga_beli'],0,',','.') }}</td>
                <td class="lpr-num" style="text-align:right; color:var(--emer2);">{{ number_format($row['pendapatan'],0,',','.') }}</td>
                <td class="lpr-num" style="text-align:right; color:var(--mut2);">{{ number_format($row['biaya'],0,',','.') }}</td>
                <td class="lpr-num" style="text-align:right; font-weight:700; color:{{ $row['laba'] >= 0 ? 'var(--gold2)' : 'var(--red2)' }};">
                    {{ $row['laba'] >= 0 ? '+' : '' }}{{ number_format($row['laba'],0,',','.') }}
                </td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="8" style="padding:.65rem .9rem; font-size:.78rem; color:var(--mut);">TOTAL — {{ $this->detailBpjs->count() }} obat kronis aktif</td>
                <td class="lpr-num" style="text-align:right; color:var(--emer2); padding:.65rem .9rem;">{{ number_format($totalPend2,0,',','.') }}</td>
                <td class="lpr-num" style="text-align:right; color:var(--mut2); padding:.65rem .9rem;">{{ number_format($totalBiaya2,0,',','.') }}</td>
                <td class="lpr-num" style="text-align:right; padding:.65rem .9rem; color:{{ $totalLaba2 >= 0 ? 'var(--gold2)' : 'var(--red2)' }};">{{ $totalLaba2 >= 0 ? '+' : '' }}{{ number_format($totalLaba2,0,',','.') }}</td>
            </tr>
        </tfoot>
    </table>
    </div>
</div>
@endif


{{-- ═══════════════════════════════════════════════════════════ --}}
{{-- TAB: OBAT NON-KRONIS                                       --}}
{{-- ═══════════════════════════════════════════════════════════ --}}
@if($activeTab === 'nonkronis')
@if($this->detailNonKronis->isNotEmpty())
@php
    $nkPend  = $this->detailNonKronis->sum('pendapatan');
    $nkBiaya = $this->detailNonKronis->sum('biaya');
    $nkLaba  = $this->detailNonKronis->sum('laba');
    $nkLabaItems = $this->detailNonKronis->filter(fn($x) => $x['laba'] > 0)->count();
    $nkRugiItems = $this->detailNonKronis->filter(fn($x) => $x['laba'] < 0)->count();
@endphp
<div class="grid-kpi" style="margin-bottom:1.35rem;">
    <div class="lpr-kpi" style="border:1px solid rgba(63,207,142,.2); background:rgba(63,207,142,.06);">
        <div class="lpr-label">Total Pendapatan</div>
        <div class="lpr-num lpr-kpi-val" style="color:var(--emer2);">Rp {{ number_format($nkPend,0,',','.') }}</div>
        <div style="font-size:.63rem; color:var(--mut);">{{ $this->detailNonKronis->count() }} transaksi stok keluar</div>
    </div>
    <div class="lpr-kpi" style="border:1px solid rgba(232,100,90,.18); background:rgba(232,100,90,.05);">
        <div class="lpr-label">Total HPP</div>
        <div class="lpr-num lpr-kpi-val" style="color:var(--mut2);">(Rp {{ number_format($nkBiaya,0,',','.') }})</div>
        <div style="font-size:.63rem; color:var(--mut);">Biaya beli aktual dari stok keluar</div>
    </div>
    <div class="lpr-kpi" style="border:1px solid rgba(217,164,65,.22); background:rgba(217,164,65,.06);">
        <div class="lpr-label">Laba Kotor</div>
        <div class="lpr-num lpr-kpi-val" style="color:{{ $nkLaba >= 0 ? 'var(--gold2)' : 'var(--red2)' }};">
            {{ $nkLaba >= 0 ? '+' : '' }}Rp {{ number_format($nkLaba,0,',','.') }}
        </div>
        <div style="font-size:.63rem; color:var(--mut);">Margin {{ $nkPend > 0 ? round($nkLaba/$nkPend*100,1) : 0 }}%</div>
    </div>
    <div class="lpr-kpi" style="border:1px solid rgba(111,177,224,.18); background:rgba(111,177,224,.05);">
        <div class="lpr-label">Transaksi</div>
        <div class="lpr-num lpr-kpi-val" style="color:var(--blue);">{{ $this->detailNonKronis->count() }}</div>
        <div style="font-size:.63rem; margin-top:.1rem;">
            <span style="color:var(--emer2);">{{ $nkLabaItems }} laba</span>
            <span style="color:var(--mut);">·</span>
            <span style="color:var(--red2);">{{ $nkRugiItems }} rugi</span>
        </div>
    </div>
</div>
@endif

<div class="glass-card">
    <div style="padding:.9rem 1.2rem; border-bottom:1px solid var(--line); display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:.5rem; background:rgba(0,0,0,.15);">
        <div>
            <span style="font-size:.73rem; color:var(--blue); text-transform:uppercase; letter-spacing:.07em; font-weight:700;">Stok Keluar — Obat Non-Kronis Aktual</span>
            <span style="font-size:.68rem; color:var(--mut); margin-left:.75rem;">{{ $this->periode }}</span>
        </div>
        <span style="font-size:.7rem; color:var(--mut);">{{ $this->detailNonKronis->count() }} transaksi</span>
    </div>
    @if($this->detailNonKronis->isEmpty())
    <div style="padding:3.5rem; text-align:center; color:var(--mut);">
        <svg width="38" height="38" fill="none" stroke="currentColor" stroke-width="1" viewBox="0 0 24 24" style="display:block; margin:0 auto .75rem; color:var(--line2);"><path d="M20 7H4a2 2 0 00-2 2v10a2 2 0 002 2h16a2 2 0 002-2V9a2 2 0 00-2-2z"/><path d="M16 21V5a2 2 0 00-2-2h-4a2 2 0 00-2 2v16"/></svg>
        <div style="font-size:.82rem; margin-bottom:.5rem;">Belum ada stok keluar obat non-kronis bulan ini.</div>
        <a href="{{ route('stok-keluar.index') }}" style="color:var(--blue); font-size:.78rem; text-decoration:none;"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" style="display:inline-block;vertical-align:middle"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg> Catat Stok Keluar</a>
    </div>
    @else
    <div style="overflow-x:auto;">
    <table class="lpr-table" style="font-size:.78rem;">
        <thead>
            <tr>
                <th>Tanggal</th>
                <th style="text-align:left;">Nama Obat</th>
                <th style="text-align:right;">Jumlah</th>
                <th style="text-align:right;">Harga Jual/Unit</th>
                <th style="text-align:right;">HPP/Unit</th>
                <th style="text-align:right; color:var(--emer2);">Pendapatan</th>
                <th style="text-align:right; color:var(--red2);">Biaya HPP</th>
                <th style="text-align:right; color:var(--gold2);">Laba</th>
                <th>Keterangan</th>
            </tr>
        </thead>
        <tbody>
            @foreach($this->detailNonKronis as $row)
            <tr>
                <td class="lpr-num" style="font-size:.72rem; color:var(--mut2);">{{ $row['tanggal'] }}</td>
                <td style="font-weight:600;">{{ $row['nama'] }}</td>
                <td class="lpr-num" style="text-align:right;">{{ $row['jumlah'] }} <span style="color:var(--mut); font-size:.68rem;">{{ $row['satuan'] }}</span></td>
                <td class="lpr-num" style="text-align:right; font-size:.74rem;">{{ number_format($row['harga_jual'],0,',','.') }}</td>
                <td class="lpr-num" style="text-align:right; font-size:.74rem; color:var(--mut2);">{{ number_format($row['harga_beli'],0,',','.') }}</td>
                <td class="lpr-num" style="text-align:right; color:var(--emer2);">{{ number_format($row['pendapatan'],0,',','.') }}</td>
                <td class="lpr-num" style="text-align:right; color:var(--mut2);">{{ number_format($row['biaya'],0,',','.') }}</td>
                <td class="lpr-num" style="text-align:right; font-weight:700; color:{{ $row['laba'] >= 0 ? 'var(--gold2)' : 'var(--red2)' }};">
                    {{ $row['laba'] >= 0 ? '+' : '' }}{{ number_format($row['laba'],0,',','.') }}
                </td>
                <td style="font-size:.72rem; color:var(--mut2);">{{ $row['keterangan'] ?? '—' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    </div>
    @endif
</div>
@endif

</div>

{{-- ── Chart JS ── --}}
@if($activeTab === 'ringkasan')
<script>
(function() {
    const trenEl  = document.getElementById('chartTren');
    const donutEl = document.getElementById('chartDonut');
    if (!trenEl && !donutEl) return;

    const tren  = @json($this->tren);
    const donut = { kronis: {{ $r['pendBpjs'] }}, tunai: {{ $r['pendTunai'] }} };

    function initCharts() {
        if (typeof Chart === 'undefined') { setTimeout(initCharts, 200); return; }
        Chart.defaults.font.family = "'JetBrains Mono','Fira Code','Courier New',monospace";

        if (trenEl) {
            if (trenEl._chart) trenEl._chart.destroy();
            trenEl._chart = new Chart(trenEl, {
                type: 'bar',
                data: {
                    labels: tren.labels,
                    datasets: [
                        {
                            label: 'Kronis (BPJS)',
                            data: tren.pendKronisData,
                            backgroundColor: 'rgba(63,207,142,.45)',
                            borderColor: '#3fcf8e',
                            borderWidth: 1.5,
                            borderRadius: 5,
                            borderSkipped: false,
                        },
                        {
                            label: 'Tunai (Pasien Umum)',
                            data: tren.pendTunaiData,
                            backgroundColor: 'rgba(111,177,224,.4)',
                            borderColor: '#6fb1e0',
                            borderWidth: 1.5,
                            borderRadius: 5,
                            borderSkipped: false,
                        },
                        {
                            label: 'Pengeluaran PO',
                            data: tren.pengeluaranData,
                            type: 'line',
                            borderColor: '#e8645a',
                            backgroundColor: 'rgba(232,100,90,.12)',
                            borderWidth: 2,
                            pointRadius: 4,
                            pointHoverRadius: 6,
                            pointBackgroundColor: '#e8645a',
                            tension: 0.4,
                            fill: true,
                        },
                    ],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    animation: { duration: 600, easing: 'easeOutQuart' },
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: 'rgba(10,20,16,.95)',
                            borderColor: 'rgba(255,255,255,.1)',
                            borderWidth: 1,
                            padding: 10,
                            callbacks: {
                                label: ctx => '  ' + ctx.dataset.label + ': Rp ' + Intl.NumberFormat('id').format(ctx.parsed.y),
                            }
                        }
                    },
                    scales: {
                        x: {
                            ticks: { color: '#8fae9f', font: { size: 10 } },
                            grid: { color: 'rgba(31,61,48,.35)', drawBorder: false },
                        },
                        y: {
                            ticks: {
                                color: '#8fae9f',
                                font: { size: 10 },
                                callback: v => v >= 1000000 ? 'Rp ' + (v/1000000).toFixed(1) + 'jt' : 'Rp ' + Intl.NumberFormat('id').format(v),
                            },
                            grid: { color: 'rgba(31,61,48,.35)', drawBorder: false },
                        },
                    },
                },
            });
        }

        if (donutEl) {
            if (donutEl._chart) donutEl._chart.destroy();
            donutEl._chart = new Chart(donutEl, {
                type: 'doughnut',
                data: {
                    labels: ['Obat Kronis (BPJS)', 'Tunai/Non-Kronis'],
                    datasets: [{
                        data: [donut.kronis, donut.tunai],
                        backgroundColor: ['rgba(63,207,142,.65)', 'rgba(111,177,224,.65)'],
                        borderColor: ['rgba(63,207,142,.9)', 'rgba(111,177,224,.9)'],
                        borderWidth: 2,
                        hoverOffset: 8,
                    }],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    cutout: '68%',
                    animation: { duration: 600 },
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: 'rgba(10,20,16,.95)',
                            borderColor: 'rgba(255,255,255,.1)',
                            borderWidth: 1,
                            callbacks: {
                                label: ctx => '  ' + ctx.label + ': Rp ' + Intl.NumberFormat('id').format(ctx.parsed),
                            }
                        }
                    },
                },
            });
        }
    }

    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', initCharts);
    else initCharts();
    document.addEventListener('livewire:navigated', initCharts);
})();
</script>
@endif
