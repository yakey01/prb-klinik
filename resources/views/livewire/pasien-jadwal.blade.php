<div>

{{-- ===================== KPI STATS ROW ===================== --}}
<div class="grid-kpi">
    {{-- Hari Ini --}}
    <div class="kpi-card" wire:click="$set('filterPeriode','hari_ini')"
        style="cursor:pointer;transition:all .15s;border-color:{{ $filterPeriode==='hari_ini' ? 'rgba(217,164,65,.5)' : 'rgba(217,164,65,.2)' }};{{ $filterPeriode==='hari_ini' ? 'box-shadow:0 0 0 2px rgba(217,164,65,.2);' : '' }}"
        onmouseover="this.style.borderColor='rgba(217,164,65,.45)'" onmouseout="this.style.borderColor='{{ $filterPeriode==='hari_ini' ? 'rgba(217,164,65,.5)' : 'rgba(217,164,65,.2)' }}'">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:.5rem;">
            <span style="font-size:.65rem;color:var(--mut);text-transform:uppercase;letter-spacing:.06em;font-weight:600;">Hari Ini</span>
            <span style="width:28px;height:28px;border-radius:50%;background:rgba(217,164,65,.12);display:flex;align-items:center;justify-content:center;">
                <svg width="13" height="13" fill="none" stroke="var(--gold2)" stroke-width="2.2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
            </span>
        </div>
        <div class="font-heading" style="font-size:2rem;color:var(--gold2);line-height:1;">{{ $this->stats['hari_ini'] ?? 0 }}</div>
        <div style="font-size:.68rem;color:var(--mut);margin-top:.25rem;">Jadwal pengambilan hari ini</div>
    </div>
    {{-- Minggu Ini --}}
    <div class="kpi-card" wire:click="$set('filterPeriode','minggu_ini')"
        style="cursor:pointer;transition:all .15s;border-color:{{ $filterPeriode==='minggu_ini' ? 'rgba(111,177,224,.5)' : 'rgba(111,177,224,.18)' }};{{ $filterPeriode==='minggu_ini' ? 'box-shadow:0 0 0 2px rgba(111,177,224,.2);' : '' }}">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:.5rem;">
            <span style="font-size:.65rem;color:var(--mut);text-transform:uppercase;letter-spacing:.06em;font-weight:600;">Minggu Ini</span>
            <span style="width:28px;height:28px;border-radius:50%;background:rgba(111,177,224,.1);display:flex;align-items:center;justify-content:center;">
                <svg width="13" height="13" fill="none" stroke="var(--blue)" stroke-width="2.2" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
            </span>
        </div>
        <div class="font-heading" style="font-size:2rem;color:var(--blue);line-height:1;">{{ $this->stats['minggu_ini'] ?? 0 }}</div>
        <div style="font-size:.68rem;color:var(--mut);margin-top:.25rem;">Jadwal 7 hari ke depan</div>
    </div>
    {{-- Bulan Ini --}}
    <div class="kpi-card" wire:click="$set('filterPeriode','bulan_ini')"
        style="cursor:pointer;transition:all .15s;border-color:{{ $filterPeriode==='bulan_ini' ? 'rgba(63,207,142,.45)' : 'rgba(63,207,142,.18)' }};{{ $filterPeriode==='bulan_ini' ? 'box-shadow:0 0 0 2px rgba(63,207,142,.15);' : '' }}">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:.5rem;">
            <span style="font-size:.65rem;color:var(--mut);text-transform:uppercase;letter-spacing:.06em;font-weight:600;">Bulan Ini</span>
            <span style="width:28px;height:28px;border-radius:50%;background:rgba(63,207,142,.1);display:flex;align-items:center;justify-content:center;">
                <svg width="13" height="13" fill="none" stroke="var(--emer)" stroke-width="2.2" viewBox="0 0 24 24"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
            </span>
        </div>
        <div class="font-heading" style="font-size:2rem;color:var(--emer);line-height:1;">{{ $this->stats['bulan_ini'] ?? 0 }}</div>
        <div style="font-size:.68rem;color:var(--mut);margin-top:.25rem;">Jadwal bulan ini</div>
    </div>
    {{-- Terlambat --}}
    <div class="kpi-card" wire:click="$set('filterPeriode','terlewat')"
        style="cursor:pointer;transition:all .15s;{{ ($this->stats['terlewat'] ?? 0) > 0 ? 'border-color:rgba(232,100,90,.45);' : 'border-color:rgba(232,100,90,.15);' }}{{ $filterPeriode==='terlewat' ? 'box-shadow:0 0 0 2px rgba(232,100,90,.2);' : '' }}">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:.5rem;">
            <span style="font-size:.65rem;color:var(--mut);text-transform:uppercase;letter-spacing:.06em;font-weight:600;">Terlambat</span>
            <span style="width:28px;height:28px;border-radius:50%;background:rgba(232,100,90,.1);display:flex;align-items:center;justify-content:center;">
                <svg width="13" height="13" fill="none" stroke="var(--red)" stroke-width="2.2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
            </span>
        </div>
        <div class="font-heading" style="font-size:2rem;line-height:1;color:{{ ($this->stats['terlewat'] ?? 0) > 0 ? 'var(--red)' : 'var(--mut)' }};">{{ $this->stats['terlewat'] ?? 0 }}</div>
        <div style="font-size:.68rem;color:var(--mut);margin-top:.25rem;">Jadwal terlewat belum ditangani</div>
    </div>
</div>

{{-- ===================== PERIOD FILTER PILLS (grouped: alert · sekarang · depan · semua) ===================== --}}
@php
    $cfgMap = [
        'hari_ini'        => ['label' => 'Hari Ini',     'color' => 'var(--gold2)', 'bg' => 'rgba(217,164,65,.14)',  'border' => 'rgba(217,164,65,.4)'],
        'minggu_ini'      => ['label' => 'Minggu Ini',   'color' => 'var(--blue)',  'bg' => 'rgba(111,177,224,.12)', 'border' => 'rgba(111,177,224,.35)'],
        'bulan_ini'       => ['label' => 'Bulan Ini',    'color' => 'var(--emer)',  'bg' => 'rgba(63,207,142,.12)',  'border' => 'rgba(63,207,142,.35)'],
        'minggu_depan'    => ['label' => 'Minggu Depan', 'color' => '#a78bfa',      'bg' => 'rgba(167,139,250,.14)', 'border' => 'rgba(167,139,250,.4)'],
        'bulan_depan'     => ['label' => 'Bulan Depan',  'color' => '#a78bfa',      'bg' => 'rgba(167,139,250,.14)', 'border' => 'rgba(167,139,250,.4)'],
        'semua_mendatang' => ['label' => 'Semua',        'color' => 'var(--ink)',   'bg' => 'rgba(255,255,255,.07)', 'border' => 'var(--line3)'],
    ];
    $pillCss = fn ($val) => $filterPeriode === $val
        ? 'background:'.$cfgMap[$val]['bg'].';border-color:'.$cfgMap[$val]['border'].';color:'.$cfgMap[$val]['color'].';font-weight:600;'
        : 'background:transparent;border-color:var(--line2);color:var(--mut);font-weight:500;';
    $sep = '<span style="color:var(--line3);font-size:.9rem;opacity:.55;user-select:none;padding:0 .1rem;">&#8202;&#8758;&#8202;</span>';
@endphp
<div style="display:flex;align-items:center;gap:.4rem;margin-bottom:1.5rem;border-bottom:1px solid var(--line);padding-bottom:.85rem;flex-wrap:wrap;">

    {{-- Alert: Terlambat (badge angka, merah bila ada) --}}
    @php $tl = $this->stats['terlewat'] ?? 0; @endphp
    <button wire:click="$set('filterPeriode','terlewat')"
        style="display:inline-flex;align-items:center;gap:.4rem;padding:.38rem .8rem;font-size:.76rem;border-radius:999px;border:1px solid;cursor:pointer;transition:all .18s;
            {{ $filterPeriode==='terlewat'
                ? 'background:rgba(232,100,90,.14);border-color:rgba(232,100,90,.45);color:var(--red);font-weight:600;'
                : ($tl > 0 ? 'background:transparent;border-color:rgba(232,100,90,.3);color:var(--red2);font-weight:500;' : 'background:transparent;border-color:var(--line2);color:var(--mut);font-weight:500;') }}">
        <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
        Terlambat
        @if($tl > 0)<span style="background:rgba(232,100,90,.25);color:var(--red2);border-radius:999px;padding:0 .4rem;font-size:.66rem;font-weight:800;line-height:1.5;">{{ $tl }}</span>@endif
    </button>

    <span style="width:1px;height:18px;background:var(--line2);margin:0 .25rem;"></span>

    {{-- Kluster SEKARANG --}}
    @foreach(['hari_ini','minggu_ini','bulan_ini'] as $val)
    <button wire:click="$set('filterPeriode','{{ $val }}')" style="padding:.38rem .85rem;font-size:.76rem;border-radius:999px;border:1px solid;cursor:pointer;transition:all .18s;{{ $pillCss($val) }}">{{ $cfgMap[$val]['label'] }}</button>
    @endforeach

    {!! $sep !!}

    {{-- Kluster DEPAN --}}
    @foreach(['minggu_depan','bulan_depan'] as $val)
    <button wire:click="$set('filterPeriode','{{ $val }}')" style="padding:.38rem .85rem;font-size:.76rem;border-radius:999px;border:1px solid;cursor:pointer;transition:all .18s;{{ $pillCss($val) }}">{{ $cfgMap[$val]['label'] }}</button>
    @endforeach

    {!! $sep !!}

    {{-- Semua --}}
    <button wire:click="$set('filterPeriode','semua_mendatang')" style="padding:.38rem .85rem;font-size:.76rem;border-radius:999px;border:1px solid;cursor:pointer;transition:all .18s;{{ $pillCss('semua_mendatang') }}">{{ $cfgMap['semua_mendatang']['label'] }}</button>
</div>

{{-- ===================== JADWAL CARDS GRID ===================== --}}
@if($this->jadwalList->count() > 0)
<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:1rem;margin-bottom:1rem;">
    @foreach($this->jadwalList as $jadwal)
    @php
        $tgl = \Carbon\Carbon::parse($jadwal->tanggal_jadwal ?? $jadwal->tanggal_pengambilan);
        $today = \Carbon\Carbon::today();
        $isToday = $tgl->isToday();
        $isPast = $tgl->lt($today) && !$isToday;
        $diffDays = (int) $today->diffInDays($tgl->startOfDay(), false);
        $isUrgent = $isPast || $isToday;

        $borderColor = $isPast
            ? 'rgba(232,100,90,.45)'
            : ($isToday ? 'rgba(217,164,65,.5)' : 'rgba(31,61,48,.7)');
        $bgTint = $isPast
            ? 'rgba(232,100,90,.04)'
            : ($isToday ? 'rgba(217,164,65,.04)' : 'transparent');
        $animClass = $isPast ? ' class="card-pulse-red"' : ($isToday ? ' class="card-pulse-gold"' : '');

        $pasienNama = $jadwal->pasien->nama ?? '—';
        $pasienBpjs = $jadwal->pasien->no_bpjs ?? null;
        $pasienDiag = $jadwal->pasien->kategori_diagnosis ?? null;
        $pColors = ['#3fcf8e','#d9a441','#6fb1e0','#e0a46f','#cf3f7a','#9e6fe0'];
        $pColor = $pColors[abs(crc32($pasienNama) % count($pColors))];
        $pInitial = strtoupper(substr($pasienNama, 0, 1));
    @endphp
    <div style="background:var(--card);border:1px solid {{ $borderColor }};border-radius:.65rem;padding:1rem;transition:all .2s;background-color:{{ $bgTint }};position:relative;overflow:hidden;"{{ $animClass }}>
        {{-- Urgency top bar --}}
        @if($isPast)
        <div style="position:absolute;top:0;left:0;right:0;height:3px;background:linear-gradient(90deg,var(--red),var(--red2));border-radius:.65rem .65rem 0 0;"></div>
        @elseif($isToday)
        <div style="position:absolute;top:0;left:0;right:0;height:3px;background:linear-gradient(90deg,var(--gold),var(--gold2));border-radius:.65rem .65rem 0 0;"></div>
        @endif

        {{-- Patient info --}}
        <div style="display:flex;align-items:center;gap:.65rem;margin-bottom:.85rem;{{ $isPast || $isToday ? 'margin-top:.25rem;' : '' }}">
            <div style="width:38px;height:38px;border-radius:50%;background:{{ $pColor }}1a;border:1.5px solid {{ $pColor }}44;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                <span style="color:{{ $pColor }};font-weight:700;font-size:.88rem;">{{ $pInitial }}</span>
            </div>
            <div style="flex:1;min-width:0;">
                <div style="font-weight:600;font-size:.87rem;color:var(--ink);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ $pasienNama }}</div>
                @if($pasienBpjs)
                <div class="font-mono" style="font-size:.68rem;color:var(--mut2);">{{ $pasienBpjs }}</div>
                @endif
            </div>
            {{-- Status dot --}}
            @if($jadwal->status === 'selesai')
            <span style="font-size:.65rem;padding:.15rem .45rem;border-radius:999px;background:rgba(63,207,142,.12);border:1px solid rgba(63,207,142,.25);color:var(--emer);flex-shrink:0;">Selesai</span>
            @elseif($jadwal->status === 'lewat')
            <span style="font-size:.65rem;padding:.15rem .45rem;border-radius:999px;background:rgba(232,100,90,.12);border:1px solid rgba(232,100,90,.25);color:var(--red);flex-shrink:0;">Lewat</span>
            @else
            <span style="font-size:.65rem;padding:.15rem .45rem;border-radius:999px;background:rgba(111,177,224,.1);border:1px solid rgba(111,177,224,.2);color:var(--blue);flex-shrink:0;">Dijadwalkan</span>
            @endif
        </div>

        {{-- Diagnosis badge --}}
        @if($pasienDiag)
        <div style="margin-bottom:.75rem;">
            <span style="font-size:.66rem;padding:.18rem .52rem;border-radius:999px;background:rgba(217,164,65,.1);border:1px solid rgba(217,164,65,.22);color:var(--gold2);">{{ $pasienDiag }}</span>
        </div>
        @endif

        {{-- Date + countdown --}}
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:.85rem;padding:.7rem .8rem;border-radius:.4rem;background:rgba(255,255,255,.03);border:1px solid {{ $isPast ? 'rgba(232,100,90,.2)' : ($isToday ? 'rgba(217,164,65,.2)' : 'var(--line)') }};">
            <div>
                <div style="font-size:.65rem;color:var(--mut);margin-bottom:.15rem;text-transform:uppercase;letter-spacing:.04em;">Tanggal Jadwal</div>
                <div class="font-heading" style="font-size:1.05rem;color:{{ $isPast ? 'var(--red)' : ($isToday ? 'var(--gold2)' : 'var(--ink)') }};">{{ $tgl->format('d M Y') }}</div>
            </div>
            @if($isPast)
            <span style="font-size:.72rem;padding:.25rem .65rem;border-radius:999px;background:rgba(232,100,90,.12);border:1px solid rgba(232,100,90,.3);color:var(--red);font-weight:600;white-space:nowrap;">
                &#9888; Terlambat {{ abs($diffDays) }} hr
            </span>
            @elseif($isToday)
            <span style="font-size:.72rem;padding:.25rem .65rem;border-radius:999px;background:rgba(217,164,65,.15);border:1px solid rgba(217,164,65,.4);color:var(--gold2);font-weight:700;" class="pulse-badge">
                &#10022; Hari Ini
            </span>
            @elseif($diffDays <= 7)
            <span style="font-size:.72rem;padding:.25rem .65rem;border-radius:999px;background:rgba(217,164,65,.08);border:1px solid rgba(217,164,65,.2);color:var(--gold2);white-space:nowrap;">
                {{ $diffDays }} hr lagi
            </span>
            @else
            <span style="font-size:.72rem;padding:.25rem .65rem;border-radius:999px;background:rgba(63,207,142,.07);border:1px solid rgba(63,207,142,.18);color:var(--emer);white-space:nowrap;">
                {{ $diffDays }} hr lagi
            </span>
            @endif
        </div>

        {{-- Action buttons --}}
        @if($jadwal->status === 'dijadwalkan')
        <div style="display:flex;gap:.5rem;">
            <button wire:click="selesaikan({{ $jadwal->id }})"
                style="flex:1;background:rgba(63,207,142,.12);border:1px solid rgba(63,207,142,.25);color:var(--emer);border-radius:.4rem;padding:.45rem .6rem;cursor:pointer;font-size:.75rem;font-weight:600;transition:all .15s;display:flex;align-items:center;justify-content:center;gap:.35rem;"
                onmouseover="this.style.background='rgba(63,207,142,.22)'" onmouseout="this.style.background='rgba(63,207,142,.12)'">
                <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
                Catat Selesai
            </button>
            <button wire:click="tandaiLewat({{ $jadwal->id }})" wire:confirm="Tandai jadwal ini sebagai lewat?"
                style="background:rgba(232,100,90,.08);border:1px solid rgba(232,100,90,.2);color:var(--red);border-radius:.4rem;padding:.45rem .6rem;cursor:pointer;font-size:.73rem;transition:all .15s;display:flex;align-items:center;gap:.3rem;"
                onmouseover="this.style.background='rgba(232,100,90,.15)'" onmouseout="this.style.background='rgba(232,100,90,.08)'">
                <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
                Lewat
            </button>
        </div>
        @else
        <div style="display:flex;align-items:center;justify-content:center;padding:.45rem;border:1px solid var(--line);border-radius:.4rem;font-size:.75rem;color:var(--mut);">
            @if($jadwal->status === 'selesai')
            <svg width="12" height="12" fill="none" stroke="var(--emer)" stroke-width="2.5" viewBox="0 0 24 24" style="margin-right:.3rem;"><polyline points="20 6 9 17 4 12"/></svg>
            <span style="color:var(--emer);">Sudah diselesaikan</span>
            @else
            <span style="color:var(--mut);">Sudah ditandai lewat</span>
            @endif
        </div>
        @endif
    </div>
    @endforeach
</div>

{{-- Pagination --}}
<div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:.5rem;padding-top:.5rem;border-top:1px solid var(--line);">
    <div style="font-size:.73rem;color:var(--mut2);">{{ $this->jadwalList->total() }} total jadwal</div>
    <div>{{ $this->jadwalList->links() }}</div>
</div>

@else
{{-- Empty state --}}
<div class="glass-card" style="text-align:center;padding:4rem 1.5rem;">
    <div style="width:56px;height:56px;border-radius:50%;background:rgba(255,255,255,.04);border:1px solid var(--line2);display:flex;align-items:center;justify-content:center;margin:0 auto 1rem;">
        <svg width="24" height="24" fill="none" stroke="var(--mut)" stroke-width="1.5" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
    </div>
    <div style="color:var(--ink);font-size:.9rem;font-weight:500;margin-bottom:.35rem;">Tidak ada jadwal</div>
    <div style="color:var(--mut);font-size:.8rem;">Tidak ada jadwal untuk periode yang dipilih.</div>
</div>
@endif

<style>
@keyframes prb-pulse { 0%,100%{opacity:1;box-shadow:0 0 0 0 rgba(217,164,65,.4);} 50%{opacity:.75;box-shadow:0 0 0 4px rgba(217,164,65,.0);} }
.pulse-badge { animation: prb-pulse 1.8s ease-in-out infinite; }
@keyframes card-pulse-red { 0%,100%{box-shadow:0 0 0 0 rgba(232,100,90,.0);} 50%{box-shadow:0 0 0 3px rgba(232,100,90,.18);} }
.card-pulse-red { animation: card-pulse-red 2.5s ease-in-out infinite; }
@keyframes card-pulse-gold { 0%,100%{box-shadow:0 0 0 0 rgba(217,164,65,.0);} 50%{box-shadow:0 0 0 3px rgba(217,164,65,.2);} }
.card-pulse-gold { animation: card-pulse-gold 2.5s ease-in-out infinite; }
</style>
</div>
