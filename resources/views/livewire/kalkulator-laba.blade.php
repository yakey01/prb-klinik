<div style="position:relative;">

{{-- ═══════════ HEADER ═══════════ --}}
<div style="margin-bottom:1.75rem;">
    <div style="font-size:.7rem;color:var(--mut);letter-spacing:.07em;text-transform:uppercase;margin-bottom:.3rem;">Keuangan</div>
    <div style="display:flex;align-items:center;gap:.75rem;">
        <h2 class="font-heading" style="font-size:1.5rem;color:var(--ink);margin:0;">Kalkulator Profitabilitas</h2>
        @if(count($items) > 0)
        <span style="background:rgba(217,164,65,.15);border:1px solid rgba(217,164,65,.35);color:var(--gold2);border-radius:999px;padding:.18rem .75rem;font-size:.65rem;font-weight:700;letter-spacing:.05em;">
            {{ count($items) }} obat ditambahkan
        </span>
        @endif
    </div>
    <p style="color:var(--mut);font-size:.78rem;margin-top:.3rem;">Tambah satu per satu obat PRB · klik <strong style="color:var(--ink);">Tambah</strong> lalu <strong style="color:var(--ink);">Kalkulasi</strong> untuk lihat hasil agregat</p>
</div>

{{-- ═══════════ FORM + PREVIEW (preview hitung CLIENT-SIDE via Alpine) ═══════════ --}}
<div x-data="kalkDraft($wire)">
<div style="display:grid;grid-template-columns:2fr 3fr;gap:1.25rem;align-items:start;" class="kalk-grid">

    @include('livewire.partials.kalkulator-input', [
        'slot'       => 'A',
        'obatId'     => $obatIdA,
        'cariObat'   => $cariObatA,
        'satuanList' => $satuanList,
    ])

    @include('livewire.partials.kalkulator-hasil')

</div>

{{-- ═══════════ TOMBOL AKSI ═══════════ --}}
<div style="display:flex;align-items:center;gap:.75rem;margin-top:1.1rem;flex-wrap:wrap;">

    {{-- Tambah ke Daftar (status enable via Alpine `ready`) --}}
    <button
        type="button"
        wire:click="tambahObat"
        :disabled="!ready"
        :style="ready
            ? 'background:rgba(63,207,142,.15);border:1px solid rgba(63,207,142,.4);color:var(--emer2);cursor:pointer;opacity:1;'
            : 'background:rgba(255,255,255,.03);border:1px solid var(--line);color:var(--mut2);cursor:not-allowed;opacity:.5;'"
        style="display:inline-flex;align-items:center;gap:.6rem;padding:.72rem 1.6rem;border-radius:.6rem;font-size:.88rem;font-weight:700;transition:.15s;letter-spacing:.02em;"
        x-on:mouseover="ready && ($el.style.background='rgba(63,207,142,.26)')" x-on:mouseout="ready && ($el.style.background='rgba(63,207,142,.15)')"
        :title="ready ? 'Tambahkan obat ini ke daftar' : 'Isi harga beli atau nilai klaim terlebih dahulu'"
    >
        <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
            <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
        </svg>
        Tambah ke Daftar
        @if(count($items) > 0)
        <span style="background:rgba(63,207,142,.2);border-radius:999px;padding:.05rem .55rem;font-size:.72rem;">{{ count($items) }}</span>
        @endif
    </button>

    {{-- Kalkulasi --}}
    @if(count($items) > 0 && ! $showKalkulasi)
    <button wire:click="kalkulasi"
        style="display:inline-flex;align-items:center;gap:.6rem;padding:.72rem 1.6rem;border-radius:.6rem;background:linear-gradient(135deg,rgba(217,164,65,.2),rgba(242,198,104,.14));border:1px solid rgba(217,164,65,.45);color:var(--gold2);font-size:.88rem;font-weight:700;cursor:pointer;transition:.15s;letter-spacing:.02em;"
        onmouseover="this.style.background='linear-gradient(135deg,rgba(217,164,65,.32),rgba(242,198,104,.22))'" onmouseout="this.style.background='linear-gradient(135deg,rgba(217,164,65,.2),rgba(242,198,104,.14))'">
        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/>
        </svg>
        Kalkulasi ({{ count($items) }} Obat)
    </button>
    @endif

    @if($showKalkulasi && $ringkasan['ready'])
    <button wire:click="kalkulasi"
        style="display:inline-flex;align-items:center;gap:.6rem;padding:.72rem 1.6rem;border-radius:.6rem;background:rgba(63,207,142,.1);border:1px solid rgba(63,207,142,.25);color:var(--emer2);font-size:.88rem;font-weight:700;cursor:pointer;opacity:.7;transition:.15s;"
        title="Tambahkan lebih banyak obat untuk kalkulasi ulang">
        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/>
        </svg>
        {{ count($items) }} Obat — Terhitung
    </button>
    @endif

    {{-- Reset semua (muncul jika ada item) --}}
    @if(count($items) > 0)
    <button wire:click="resetSemua"
        style="display:inline-flex;align-items:center;gap:.45rem;padding:.72rem 1rem;border-radius:.6rem;background:rgba(232,100,90,.06);border:1px solid rgba(232,100,90,.2);color:var(--mut2);font-size:.82rem;font-weight:600;cursor:pointer;transition:.12s;margin-left:auto;"
        onmouseover="this.style.color='var(--red2)'" onmouseout="this.style.color='var(--mut2)'">
        <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
            <polyline points="1 4 1 10 7 10"/><path d="M3.51 15a9 9 0 102.13-9.36L1 10"/>
        </svg>
        Reset Semua
    </button>
    @endif

</div>

{{-- Indikator auto-save: bangun kepercayaan bahwa daftar aman saat pindah halaman --}}
<div x-show="hasDraft" x-cloak style="display:flex;align-items:center;gap:.4rem;margin-top:.7rem;font-size:.7rem;color:var(--mut2);">
    <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="flex-shrink:0;">
        <path d="M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h11l5 5v11a2 2 0 01-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/>
    </svg>
    <span>Tersimpan otomatis — daftar aman walau kamu buka Katalog/halaman lain lalu kembali.</span>
    <button type="button" @click="clearDraft()" title="Hapus draft tersimpan"
        style="background:none;border:none;color:var(--mut2);text-decoration:underline;cursor:pointer;font-size:.7rem;padding:0;margin-left:.15rem;"
        onmouseover="this.style.color='var(--red2)'" onmouseout="this.style.color='var(--mut2)'">bersihkan draft</button>
</div>
</div>{{-- /x-data kalkDraft --}}

{{-- ═══════════ DAFTAR OBAT YANG DITAMBAHKAN ═══════════ --}}
@if(count($items) > 0)
<div style="margin-top:1.5rem;">

    <div style="font-size:.68rem;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:var(--mut);margin-bottom:.75rem;display:flex;align-items:center;gap:.45rem;">
        <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/>
            <line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/>
        </svg>
        Daftar Obat
        <span style="background:rgba(63,207,142,.15);border:1px solid rgba(63,207,142,.25);color:var(--emer);border-radius:999px;padding:.05rem .55rem;font-size:.65rem;font-weight:700;">{{ count($items) }}</span>
    </div>

    <div class="glass-card" style="padding:0;overflow:hidden;">
        <table style="width:100%;border-collapse:collapse;">
            <thead>
                <tr>
                    @foreach([
                        ['Obat', 'left'],
                        ['Bayar BPJS', 'right'],
                        ['Laba/Unit', 'right'],
                        ['Volume/Bln', 'right'],
                        ['Laba/Bulan', 'right'],
                        ['', 'center'],
                    ] as [$th, $align])
                    <th style="padding:.55rem {{ $align === 'center' ? '.6rem' : '1rem' }};text-align:{{ $align }};font-size:.64rem;font-weight:700;letter-spacing:.07em;text-transform:uppercase;color:var(--mut);border-bottom:1px solid var(--line);background:var(--panel);">{{ $th }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach($items as $i => $item)
                @php
                    $h  = $item['hasil'];
                    $sc = match($h['status']) {
                        'profit' => 'var(--emer)',
                        'loss'   => 'var(--red2)',
                        default  => 'var(--gold2)',
                    };
                @endphp
                <tr style="{{ $i % 2 !== 0 ? 'background:rgba(255,255,255,.015);' : '' }}">
                    <td style="padding:.65rem 1rem;border-bottom:1px solid rgba(31,61,48,.4);">
                        <div style="font-size:.82rem;font-weight:600;color:var(--ink);">{{ $item['nama'] }}</div>
                        <div style="font-size:.67rem;color:var(--mut);margin-top:.07rem;display:flex;gap:.5rem;flex-wrap:wrap;">
                            <span style="color:{{ $item['tipe'] === 'kronis' ? 'var(--emer)' : 'var(--blue)' }}">
                                {{ $item['tipe'] === 'kronis' ? 'Kronis' : 'Non-Kronis' }}
                            </span>
                            <span>{{ ucfirst($item['satuan']) }}</span>
                            @if($item['tipe'] === 'kronis')
                            <span style="color:var(--mut2);">JF {{ number_format($item['faktorJf'], 2) }}</span>
                            @endif
                        </div>
                    </td>
                    <td class="font-mono" style="padding:.65rem 1rem;text-align:right;font-size:.82rem;color:var(--blue);border-bottom:1px solid rgba(31,61,48,.4);">
                        Rp {{ number_format($h['bayar'], 0, ',', '.') }}
                    </td>
                    <td class="font-mono" style="padding:.65rem 1rem;text-align:right;font-size:.83rem;font-weight:700;color:{{ $sc }};border-bottom:1px solid rgba(31,61,48,.4);">
                        {{ $h['laba_per_unit'] >= 0 ? '+' : '−' }}Rp {{ number_format(abs($h['laba_per_unit']), 0, ',', '.') }}
                    </td>
                    <td class="font-mono" style="padding:.65rem 1rem;text-align:right;font-size:.8rem;color:var(--mut);border-bottom:1px solid rgba(31,61,48,.4);">
                        {{ $item['volume'] }} {{ $item['satuan'] }}
                    </td>
                    <td class="font-mono" style="padding:.65rem 1rem;text-align:right;font-size:.87rem;font-weight:800;color:{{ $sc }};border-bottom:1px solid rgba(31,61,48,.4);">
                        {{ $h['laba_bln'] >= 0 ? '+' : '−' }}Rp {{ number_format(abs($h['laba_bln']), 0, ',', '.') }}
                    </td>
                    <td style="padding:.65rem .75rem;text-align:center;border-bottom:1px solid rgba(31,61,48,.4);">
                        <button wire:click="hapusItem({{ $i }})"
                            style="background:rgba(232,100,90,.1);border:1px solid rgba(232,100,90,.2);border-radius:.4rem;padding:.28rem .5rem;cursor:pointer;color:var(--red2);display:inline-flex;align-items:center;transition:.12s;"
                            onmouseover="this.style.background='rgba(232,100,90,.22)'" onmouseout="this.style.background='rgba(232,100,90,.1)'"
                            title="Hapus">
                            <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                            </svg>
                        </button>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

</div>
@endif

{{-- ═══════════ HASIL KALKULASI AGREGAT ═══════════ --}}
@if($showKalkulasi && $ringkasan['ready'])
@php
    $r  = $ringkasan;
    $sc = match($r['status']) {
        'profit' => ['color' => 'var(--emer2)', 'bg' => 'rgba(63,207,142,.12)',  'border' => 'rgba(63,207,142,.3)'],
        'loss'   => ['color' => 'var(--red2)',  'bg' => 'rgba(232,100,90,.12)',   'border' => 'rgba(232,100,90,.3)'],
        default  => ['color' => 'var(--gold2)', 'bg' => 'rgba(217,164,65,.12)',   'border' => 'rgba(217,164,65,.3)'],
    };
    $statusLabel = match($r['status']) { 'profit' => 'PROFIT', 'loss' => 'RUGI', default => 'IMPAS' };
@endphp

<div class="glass-card" style="padding:1.5rem;margin-top:1.25rem;border-color:{{ $sc['border'] }};">

    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.3rem;flex-wrap:wrap;gap:.5rem;">
        <div style="font-size:.68rem;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:var(--mut);display:flex;align-items:center;gap:.45rem;">
            <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/>
            </svg>
            Hasil Kalkulasi — {{ $r['jumlah_obat'] }} Obat
        </div>
        <span style="font-size:.75rem;font-weight:800;color:{{ $sc['color'] }};background:{{ $sc['bg'] }};border:1px solid {{ $sc['border'] }};border-radius:999px;padding:.22rem .9rem;letter-spacing:.05em;">
            {{ $statusLabel }}
        </span>
    </div>

    {{-- KPI 4-kolom --}}
    <div class="kalk-kpi-grid" style="display:grid;grid-template-columns:repeat(4,1fr);gap:.7rem;margin-bottom:1.25rem;">
        @php
            $kpis = [
                ['label' => 'Pendapatan/Bulan', 'value' => '+Rp ' . number_format($r['pendapatan'], 0, ',', '.'), 'color' => 'var(--emer)'],
                ['label' => 'Biaya/Bulan',      'value' => '−Rp ' . number_format($r['biaya'], 0, ',', '.'),     'color' => 'var(--red)'],
                ['label' => 'Laba/Bulan',       'value' => ($r['laba_bln'] < 0 ? '−' : '+') . 'Rp ' . number_format(abs($r['laba_bln']), 0, ',', '.'), 'color' => $sc['color']],
                ['label' => 'Proyeksi/Tahun',   'value' => ($r['laba_tahun'] < 0 ? '−' : '+') . 'Rp ' . number_format(abs($r['laba_tahun']), 0, ',', '.'), 'color' => 'var(--gold2)'],
            ];
        @endphp
        @foreach($kpis as $kpi)
        <div style="padding:.85rem .9rem;border-radius:.65rem;background:rgba(255,255,255,.03);border:1px solid var(--line);text-align:center;">
            <div style="font-size:.58rem;font-weight:700;letter-spacing:.07em;text-transform:uppercase;color:var(--mut);margin-bottom:.4rem;">{{ $kpi['label'] }}</div>
            <div class="font-mono" style="font-size:.88rem;font-weight:800;color:{{ $kpi['color'] }};line-height:1.2;word-break:break-all;">{{ $kpi['value'] }}</div>
        </div>
        @endforeach
    </div>

    {{-- Breakdown --}}
    <div style="display:grid;gap:.4rem;margin-bottom:1.1rem;">
        <div style="display:flex;justify-content:space-between;align-items:center;padding:.52rem .9rem;border-radius:.45rem;background:rgba(255,255,255,.02);border:1px solid rgba(31,61,48,.5);">
            <span style="font-size:.78rem;color:var(--mut);">Pendapatan Bulanan ({{ $r['jumlah_obat'] }} obat)</span>
            <span class="font-mono" style="font-size:.85rem;font-weight:700;color:var(--emer);">+Rp {{ number_format($r['pendapatan'], 0, ',', '.') }}</span>
        </div>
        <div style="display:flex;justify-content:space-between;align-items:center;padding:.52rem .9rem;border-radius:.45rem;background:rgba(255,255,255,.02);border:1px solid rgba(31,61,48,.5);">
            <span style="font-size:.78rem;color:var(--mut);">Biaya Pembelian Bulanan ({{ $r['jumlah_obat'] }} obat)</span>
            <span class="font-mono" style="font-size:.85rem;font-weight:700;color:var(--red);">−Rp {{ number_format($r['biaya'], 0, ',', '.') }}</span>
        </div>
        <div style="display:flex;justify-content:space-between;align-items:center;padding:.62rem .9rem;border-radius:.45rem;background:{{ $sc['bg'] }};border:1px solid {{ $sc['border'] }};">
            <span style="font-size:.83rem;font-weight:700;color:var(--ink);">Total Laba / Bulan</span>
            <span class="font-mono" style="font-size:1rem;font-weight:800;color:{{ $sc['color'] }};">
                {{ $r['laba_bln'] >= 0 ? '+' : '−' }}Rp {{ number_format(abs($r['laba_bln']), 0, ',', '.') }}
            </span>
        </div>
        <div style="display:flex;justify-content:space-between;align-items:center;padding:.5rem .9rem;border-radius:.45rem;background:rgba(255,255,255,.02);border:1px dashed var(--line);">
            <span style="font-size:.74rem;color:var(--mut2);">Proyeksi Laba per Tahun (×12)</span>
            <span class="font-mono" style="font-size:.85rem;font-weight:700;color:var(--gold2);">
                {{ $r['laba_tahun'] < 0 ? '−' : '+' }}Rp {{ number_format(abs($r['laba_tahun']), 0, ',', '.') }}
            </span>
        </div>
    </div>

    {{-- Margin info --}}
    <div style="display:flex;align-items:center;gap:.65rem;padding:.75rem 1rem;border-radius:.5rem;background:rgba(217,164,65,.06);border:1px solid rgba(217,164,65,.18);">
        <svg width="13" height="13" fill="none" stroke="var(--gold)" stroke-width="2" viewBox="0 0 24 24">
            <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>
        </svg>
        <span style="font-size:.8rem;color:var(--gold2);">
            Margin rata-rata: <strong>{{ number_format(abs($r['margin']), 1) }}%</strong> dari total pendapatan
        </span>
    </div>

</div>
@endif

<script>
    function kalkDraft($wire) {
        return {
            // Entangle deferred — instan di client, sinkron ke server saat aksi (Tambah).
            harga:  $wire.entangle('hargaBeliA'),
            klaim:  $wire.entangle('klaimA'),
            faktor: $wire.entangle('faktorJfA'),
            volume: $wire.entangle('volumeA'),
            tipe:   $wire.entangle('tipeA'),
            satuan: $wire.entangle('satuanA'),

            // ── Auto-save draft (anti-hilang saat pindah halaman) ──
            draftKey:      'prb_kalk_draft_v1',
            hasDraft:      false,
            _saveTimer:    null,

            // Entangle deferred tidak push server->client saat aksi (pilih obat / reset),
            // jadi tarik manual nilai dari server saat event 'search-selected-a' difire.
            init() {
                const pull = () => {
                    this.harga  = this.$wire.get('hargaBeliA');
                    this.klaim  = this.$wire.get('klaimA');
                    this.faktor = this.$wire.get('faktorJfA');
                    this.volume = this.$wire.get('volumeA');
                    this.tipe   = this.$wire.get('tipeA');
                    this.satuan = this.$wire.get('satuanA');
                };
                this.$wire.on('search-selected-a', pull);

                // Simpan tiap kali daftar berubah (server dispatch 'kalk-changed' di tambah/hapus/reset).
                this.$wire.on('kalk-changed', () => this.persist());

                this.$nextTick(() => {
                    this.restoreDraft();

                    // Simpan tiap kali form draft berubah (entangled -> Alpine-reactive, debounce ringan).
                    ['harga', 'klaim', 'faktor', 'volume', 'tipe', 'satuan'].forEach(
                        k => this.$watch(k, () => this.persistDebounced())
                    );
                    // Jaring pengaman terakhir: simpan saat tab ditutup/pindah.
                    window.addEventListener('beforeunload', () => this.persist());
                });
            },

            // Pulihkan draft tersimpan bila server masih kosong (mis. baru balik dari Katalog).
            restoreDraft() {
                try {
                    const raw = localStorage.getItem(this.draftKey);
                    if (! raw) return;
                    const d = JSON.parse(raw);
                    const serverItems = this.$wire.get('items') || [];
                    const serverFormEmpty = ! this.$wire.get('searchA') && ! this.$wire.get('hargaBeliA') && ! this.$wire.get('klaimA');
                    const items = Array.isArray(d.items) ? d.items : [];
                    const form  = d.form || {};
                    const hasFormDraft = form && (form.searchA || form.hargaBeliA || form.klaimA);

                    if ((items.length && serverItems.length === 0) || (hasFormDraft && serverFormEmpty)) {
                        this.hasDraft = true;
                        // Banner dipicu server via event 'kalk-restored' setelah round-trip (anti-race).
                        this.$wire.restoreDraft(items, serverFormEmpty ? form : {});
                    } else {
                        this.hasDraft = !! raw;
                    }
                } catch (e) {}
            },

            persist() {
                try {
                    const items = this.$wire.get('items') || [];
                    const form  = {
                        searchA:    this.$wire.get('searchA') || '',
                        obatIdA:    this.$wire.get('obatIdA') || null,
                        hargaBeliA: this.harga  || '',
                        klaimA:     this.klaim  || '',
                        faktorJfA:  this.faktor || '',
                        volumeA:    this.volume || '',
                        tipeA:      this.tipe   || '',
                        satuanA:    this.satuan || '',
                    };
                    const empty = ! items.length && ! form.searchA && ! form.hargaBeliA && ! form.klaimA;
                    if (empty) {
                        localStorage.removeItem(this.draftKey);
                        this.hasDraft = false;
                    } else {
                        localStorage.setItem(this.draftKey, JSON.stringify({ v: 1, items, form, ts: Date.now() }));
                        this.hasDraft = true;
                    }
                } catch (e) {}
            },
            persistDebounced() {
                clearTimeout(this._saveTimer);
                this._saveTimer = setTimeout(() => this.persist(), 400);
            },
            clearDraft() {
                try { localStorage.removeItem(this.draftKey); } catch (e) {}
                this.hasDraft = false;
            },

            get nHarga()  { return Math.max(0, parseFloat(this.harga) || 0); },
            get nKlaim()  { return Math.max(0, parseFloat(this.klaim) || 0); },
            get nFaktor() { return Math.max(0.01, parseFloat(this.faktor) || 0.01); },
            get nVolume() { return Math.max(1, parseInt(this.volume) || 1); },

            get ready()      { return this.nHarga > 0 || this.nKlaim > 0; },
            jf(f)            { f = +f; return (f <= 0 || f > 2) ? 1.28 : (f < 1 ? 1 + f : f); },
            get bayar()      { return this.tipe === 'kronis' ? Math.round(this.nKlaim * this.jf(this.nFaktor)) : this.nKlaim; },
            get labaUnit()   { return this.bayar - this.nHarga; },
            get pendapatan() { return this.bayar * this.nVolume; },
            get biaya()      { return this.nHarga * this.nVolume; },
            get labaBln()    { return this.pendapatan - this.biaya; },
            get labaTahun()  { return this.labaBln * 12; },
            get margin()     { return this.bayar > 0 ? Math.round(this.labaUnit / this.bayar * 1000) / 10 : 0; },
            get status()     { return this.labaBln > 0 ? 'profit' : (this.labaBln < 0 ? 'loss' : 'bep'); },

            get statusLabel() { return this.status === 'profit' ? 'PROFIT' : (this.status === 'loss' ? 'RUGI' : 'IMPAS'); },
            get sText()   { return this.status === 'profit' ? 'var(--emer2)' : (this.status === 'loss' ? 'var(--red2)' : 'var(--gold2)'); },
            get sBg()     { return this.status === 'profit' ? 'rgba(63,207,142,.12)' : (this.status === 'loss' ? 'rgba(232,100,90,.12)' : 'rgba(217,164,65,.12)'); },
            get sBorder() { return this.status === 'profit' ? 'rgba(63,207,142,.3)' : (this.status === 'loss' ? 'rgba(232,100,90,.3)' : 'rgba(217,164,65,.3)'); },
            get sGlow()   { return this.status === 'profit' ? 'rgba(63,207,142,.08)' : (this.status === 'loss' ? 'rgba(232,100,90,.08)' : 'rgba(217,164,65,.08)'); },

            cap(s)      { return s ? s.charAt(0).toUpperCase() + s.slice(1) : ''; },
            rp(n)       { n = Math.round(n || 0); return (n < 0 ? '−' : '') + 'Rp ' + Math.abs(n).toLocaleString('id-ID'); },
            rpSigned(n) { n = Math.round(n || 0); return (n > 0 ? '+' : (n < 0 ? '−' : '')) + 'Rp ' + Math.abs(n).toLocaleString('id-ID'); },
        }
    }
</script>

</div>{{-- /Livewire root --}}
