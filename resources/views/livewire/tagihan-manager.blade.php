<div>
    {{-- ══ BANNER FOKUS TANGGAL (deep-link dari kalender Barang Masuk Harian) ══ --}}
    @if($tanggal !== '')
    @php $tglC = \Carbon\Carbon::parse($tanggal); @endphp
    <div class="glass-card" style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:.6rem;padding:.7rem 1.1rem;margin-bottom:1rem;border-color:rgba(217,164,65,.4);background:linear-gradient(90deg,rgba(217,164,65,.1),transparent);">
        <div style="display:flex;align-items:center;gap:.6rem;">
            <svg width="16" height="16" fill="none" stroke="var(--gold2)" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
            <span style="font-size:.82rem;color:var(--ink);">Menampilkan tagihan barang masuk <strong style="color:var(--gold2);">{{ $tglC->translatedFormat('l, d M Y') }}</strong></span>
        </div>
        <div style="display:flex;align-items:center;gap:.5rem;">
            <a href="{{ route('pengadaan.harian') }}" wire:navigate style="font-size:.72rem;color:var(--mut);text-decoration:none;display:inline-flex;align-items:center;gap:.3rem;" title="Kembali ke kalender">← kalender</a>
            <button wire:click="clearTanggal" style="font-size:.72rem;padding:.3rem .75rem;border-radius:999px;background:rgba(232,100,90,.08);border:1px solid rgba(232,100,90,.28);color:var(--red2);cursor:pointer;">Tampilkan semua ✕</button>
        </div>
    </div>
    @endif

    {{-- ══ KPI CARDS ══════════════════════════════════════════════════ --}}
    @php $k = $this->kpiCards; @endphp
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:.85rem;margin-bottom:1.5rem;">

        {{-- Total Terutang --}}
        <div class="glass-card" style="padding:1rem 1.2rem;border-color:rgba(232,100,90,.3);">
            <div style="font-size:.65rem;color:var(--mut);text-transform:uppercase;letter-spacing:.07em;margin-bottom:.3rem;">Total Terutang</div>
            <div class="font-mono" style="font-size:1.2rem;font-weight:800;color:var(--red2);">Rp {{ number_format($k['total_terutang'],0,',','.') }}</div>
            <div style="font-size:.7rem;color:var(--mut);margin-top:.2rem;">{{ $k['count_aktif'] }} tagihan aktif</div>
        </div>

        {{-- Overdue --}}
        <div class="glass-card" style="padding:1rem 1.2rem;border-color:{{ $k['count_overdue']>0 ? 'rgba(232,100,90,.5)' : 'var(--line)' }};">
            <div style="font-size:.65rem;color:var(--mut);text-transform:uppercase;letter-spacing:.07em;margin-bottom:.3rem;">
                Overdue 🔴
            </div>
            <div class="font-mono" style="font-size:1.2rem;font-weight:800;color:{{ $k['count_overdue']>0 ? 'var(--red2)' : 'var(--mut)' }};">
                Rp {{ number_format($k['overdue'],0,',','.') }}
            </div>
            <div style="font-size:.7rem;color:var(--mut);margin-top:.2rem;">{{ $k['count_overdue'] }} tagihan lewat jatuh tempo</div>
        </div>

        {{-- Jatuh Tempo 7 Hari --}}
        <div class="glass-card" style="padding:1rem 1.2rem;border-color:rgba(217,164,65,.3);">
            <div style="font-size:.65rem;color:var(--mut);text-transform:uppercase;letter-spacing:.07em;margin-bottom:.3rem;">Jatuh Tempo ≤7 Hari</div>
            <div class="font-mono" style="font-size:1.2rem;font-weight:800;color:var(--gold2);">Rp {{ number_format($k['jatuh_tempo_7'],0,',','.') }}</div>
        </div>

        {{-- Kronis --}}
        <div class="glass-card" style="padding:1rem 1.2rem;border-color:rgba(63,207,142,.2);">
            <div style="font-size:.65rem;color:var(--mut);text-transform:uppercase;letter-spacing:.07em;margin-bottom:.3rem;">Kronis Terutang</div>
            <div class="font-mono" style="font-size:1.1rem;font-weight:700;color:var(--emer2);">Rp {{ number_format($k['kronis_terutang'],0,',','.') }}</div>
        </div>

        {{-- Non-Kronis --}}
        <div class="glass-card" style="padding:1rem 1.2rem;border-color:rgba(111,177,224,.2);">
            <div style="font-size:.65rem;color:var(--mut);text-transform:uppercase;letter-spacing:.07em;margin-bottom:.3rem;">Non-Kronis Terutang</div>
            <div class="font-mono" style="font-size:1.1rem;font-weight:700;color:var(--blue);">Rp {{ number_format($k['non_kronis_terutang'],0,',','.') }}</div>
        </div>

        {{-- Lunas Bulan Ini --}}
        <div class="glass-card" style="padding:1rem 1.2rem;border-color:rgba(63,207,142,.2);">
            <div style="font-size:.65rem;color:var(--mut);text-transform:uppercase;letter-spacing:.07em;margin-bottom:.3rem;">Lunas Bulan Ini</div>
            <div class="font-mono" style="font-size:1.1rem;font-weight:700;color:var(--emer2);">Rp {{ number_format($k['lunas_bulan_ini'],0,',','.') }}</div>
        </div>
    </div>

    {{-- ══ FILTER BAR ══════════════════════════════════════════════════ --}}
    <div class="glass-card" style="padding:1rem 1.25rem;margin-bottom:1.25rem;display:flex;gap:.75rem;flex-wrap:wrap;align-items:flex-end;">

        {{-- View mode tabs --}}
        <div style="display:flex;border-radius:.5rem;overflow:hidden;border:1px solid var(--line2);">
            @foreach(['semua'=>'Semua','mingguan'=>'Mingguan','bulanan'=>'Bulanan'] as $v=>$l)
            <button wire:click="$set('viewMode','{{ $v }}')"
                style="padding:.4rem .9rem;font-size:.75rem;font-weight:600;cursor:pointer;border:none;transition:all .15s;
                    {{ $viewMode===$v ? 'background:rgba(217,164,65,.2);color:var(--gold2);' : 'background:transparent;color:var(--mut);' }}">
                {{ $l }}
            </button>
            @endforeach
        </div>

        {{-- Tipe --}}
        <div style="display:flex;border-radius:.5rem;overflow:hidden;border:1px solid var(--line2);">
            @foreach(['semua'=>'Semua','kronis'=>'Kronis','non_kronis'=>'Non-Kronis'] as $v=>$l)
            <button wire:click="$set('filterTipe','{{ $v }}')"
                style="padding:.4rem .85rem;font-size:.73rem;font-weight:600;cursor:pointer;border:none;transition:all .15s;
                    {{ $filterTipe===$v ? ($v==='kronis' ? 'background:rgba(63,207,142,.2);color:var(--emer2);' : ($v==='non_kronis' ? 'background:rgba(111,177,224,.2);color:var(--blue);' : 'background:rgba(217,164,65,.15);color:var(--gold2);')) : 'background:transparent;color:var(--mut);' }}
                    {{ $v!=='semua' ? 'border-left:1px solid var(--line2);' : '' }}">
                {{ $l }}
            </button>
            @endforeach
        </div>

        {{-- Status --}}
        <div style="display:flex;border-radius:.5rem;overflow:hidden;border:1px solid var(--line2);">
            @foreach(['aktif'=>'Aktif','semua'=>'Semua','lunas'=>'Lunas'] as $v=>$l)
            <button wire:click="$set('filterStatus','{{ $v }}')"
                style="padding:.4rem .8rem;font-size:.73rem;font-weight:600;cursor:pointer;border:none;transition:all .15s;
                    {{ $filterStatus===$v ? 'background:rgba(217,164,65,.15);color:var(--gold2);' : 'background:transparent;color:var(--mut);' }}
                    {{ $v!=='aktif' ? 'border-left:1px solid var(--line2);' : '' }}">
                {{ $l }}
            </button>
            @endforeach
        </div>

        {{-- ══ PBF COMBOBOX (pure JS, position:fixed) ══ --}}
        <div id="pbf-wrap" style="position:relative;">
            {{-- Hidden select — wire:model.live untuk Livewire filter --}}
            <select wire:model.live="filterDist" id="pbf-sel" style="display:none;" aria-hidden="true">
                <option value="0">Semua PBF</option>
                @foreach($this->distributors as $d)
                <option value="{{ $d->id }}" data-name="{{ $d->name }}">{{ $d->name }}</option>
                @endforeach
            </select>

            {{-- Trigger --}}
            <button type="button" id="pbf-btn"
                onclick="PbfCb.toggle()"
                onkeydown="PbfCb.keyBtn(event)"
                style="display:flex;align-items:center;gap:.45rem;padding:.4rem .75rem;
                    background:var(--panel);border:1px solid var(--line2);border-radius:.45rem;
                    cursor:pointer;white-space:nowrap;transition:border-color .15s,box-shadow .15s;outline:none;min-width:140px;">
                <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"
                    style="color:var(--mut);flex-shrink:0;">
                    <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
                </svg>
                <span id="pbf-display" style="flex:1;font-size:.78rem;color:var(--ink);text-align:left;overflow:hidden;text-overflow:ellipsis;">Semua PBF</span>
                <svg id="pbf-chevron" width="11" height="11" fill="none" stroke="currentColor" stroke-width="2.5"
                    viewBox="0 0 24 24" style="flex-shrink:0;color:var(--mut);transition:transform .18s,color .15s;">
                    <polyline points="6 9 12 15 18 9"/>
                </svg>
            </button>

            {{-- Dropdown (moved to body) --}}
            <div id="pbf-dd"
                style="display:none;position:fixed;z-index:9999;background:#0d1c15;
                    border:1px solid rgba(217,164,65,.28);border-radius:.6rem;overflow:hidden;
                    box-shadow:0 16px 48px rgba(0,0,0,.85),0 0 0 1px rgba(255,255,255,.03);">
                <div style="padding:.4rem .45rem;border-bottom:1px solid rgba(255,255,255,.06);">
                    <div style="display:flex;align-items:center;gap:.4rem;background:rgba(255,255,255,.05);border-radius:.35rem;padding:.35rem .6rem;">
                        <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"
                            style="color:var(--mut);flex-shrink:0;">
                            <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
                        </svg>
                        <input id="pbf-qi" type="text" placeholder="Cari PBF…" autocomplete="off"
                            oninput="PbfCb.filter(this.value)"
                            onkeydown="PbfCb.keyDd(event)"
                            style="background:none;border:none;outline:none;color:var(--ink);font-size:.78rem;width:100%;caret-color:var(--gold2);">
                        <button type="button" id="pbf-qclr" onclick="PbfCb.clearQ()"
                            style="display:none;background:none;border:none;cursor:pointer;color:var(--mut);font-size:.7rem;padding:0;line-height:1;flex-shrink:0;"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" style="display:inline-block;vertical-align:middle"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg></button>
                    </div>
                </div>
                <div id="pbf-list" class="cb-scroll" style="max-height:200px;overflow-y:auto;"></div>
                <div id="pbf-foot" style="padding:.25rem .75rem;font-size:.65rem;color:var(--mut);border-top:1px solid rgba(255,255,255,.05);text-align:right;"></div>
            </div>
        </div>
        {{-- ══ END PBF ══ --}}

        {{-- Periode (bulanan mode) --}}
        @if($viewMode==='bulanan')
        <input wire:model.live="filterPeriode" type="month" style="background:var(--panel);border:1px solid var(--line2);color:var(--ink);border-radius:.45rem;padding:.4rem .75rem;font-size:.78rem;">
        @endif
    </div>

    {{-- ══ BULANAN SUMMARY (jika mode bulanan) ════════════════════════ --}}
    @if($viewMode === 'bulanan')
    <div class="glass-card" style="padding:1.2rem 1.5rem;margin-bottom:1.25rem;overflow-x:auto;">
        <div class="font-heading" style="font-size:.95rem;color:var(--ink);margin-bottom:1rem;">Ringkasan per Bulan</div>
        <table style="width:100%;border-collapse:collapse;font-size:.82rem;min-width:600px;">
            <thead>
                <tr>
                    <th style="text-align:left;padding:.4rem .75rem;font-size:.65rem;color:var(--mut);text-transform:uppercase;letter-spacing:.05em;border-bottom:1px solid var(--line);">Periode</th>
                    <th style="text-align:right;padding:.4rem .75rem;font-size:.65rem;color:var(--mut);text-transform:uppercase;letter-spacing:.05em;border-bottom:1px solid var(--line);">Kronis</th>
                    <th style="text-align:right;padding:.4rem .75rem;font-size:.65rem;color:var(--mut);text-transform:uppercase;letter-spacing:.05em;border-bottom:1px solid var(--line);">Non-Kronis</th>
                    <th style="text-align:right;padding:.4rem .75rem;font-size:.65rem;color:var(--mut);text-transform:uppercase;letter-spacing:.05em;border-bottom:1px solid var(--line);">Total</th>
                    <th style="text-align:right;padding:.4rem .75rem;font-size:.65rem;color:var(--mut);text-transform:uppercase;letter-spacing:.05em;border-bottom:1px solid var(--line);">Lunas</th>
                    <th style="text-align:right;padding:.4rem .75rem;font-size:.65rem;color:var(--mut);text-transform:uppercase;letter-spacing:.05em;border-bottom:1px solid var(--line);">Terutang</th>
                    <th style="text-align:right;padding:.4rem .75rem;font-size:.65rem;color:var(--mut);text-transform:uppercase;letter-spacing:.05em;border-bottom:1px solid var(--line);">Tagihan</th>
                </tr>
            </thead>
            <tbody>
                @forelse($this->bulananList as $bln)
                <tr style="border-bottom:1px solid rgba(255,255,255,.04);">
                    <td style="padding:.55rem .75rem;font-weight:600;color:var(--gold2);">{{ \Carbon\Carbon::parse($bln['periode'].'-01')->format('M Y') }}</td>
                    <td class="font-mono" style="text-align:right;padding:.55rem .75rem;color:var(--emer2);">{{ number_format($bln['kronis'],0,',','.') }}</td>
                    <td class="font-mono" style="text-align:right;padding:.55rem .75rem;color:var(--blue);">{{ number_format($bln['non_kronis'],0,',','.') }}</td>
                    <td class="font-mono" style="text-align:right;padding:.55rem .75rem;font-weight:700;">{{ number_format($bln['total'],0,',','.') }}</td>
                    <td class="font-mono" style="text-align:right;padding:.55rem .75rem;color:var(--emer2);">{{ number_format($bln['lunas'],0,',','.') }}</td>
                    <td class="font-mono" style="text-align:right;padding:.55rem .75rem;color:{{ $bln['terutang']>0 ? 'var(--red2)' : 'var(--mut)' }};">{{ number_format($bln['terutang'],0,',','.') }}</td>
                    <td class="font-mono" style="text-align:right;padding:.55rem .75rem;color:var(--mut);">{{ $bln['count'] }}</td>
                </tr>
                @empty
                <tr><td colspan="7" style="text-align:center;padding:1.5rem;color:var(--mut);">Tidak ada data.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @endif

    {{-- ══ TAGIHAN LIST — grouped by Faktur/PO ═══════════════════════ --}}
    <div class="glass-card" style="overflow:hidden !important;">
        <div style="padding:1rem 1.5rem;border-bottom:1px solid var(--line);display:flex;align-items:center;justify-content:space-between;">
            <div class="font-heading" style="font-size:.95rem;color:var(--ink);">
                Daftar Tagihan
                <span style="font-size:.72rem;font-weight:400;color:var(--mut);margin-left:.5rem;">{{ $this->tagihanList->total() }} tagihan · {{ $this->tagihanGrouped->count() }} faktur</span>
            </div>
        </div>

        @forelse($this->tagihanGrouped as $poId => $poTagihan)
        @php
            $firstTag  = $poTagihan->first();
            $po        = $firstTag->purchaseOrder;
            $dist      = $firstTag->distributor;
            $poTotal   = $poTagihan->sum('total_tagihan');
            $poSisa    = $poTagihan->sum(fn($t) => $t->sisa_tagihan);
            $hasKronis = $poTagihan->where('tipe_obat','kronis')->count() > 0;
            $hasNonKro = $poTagihan->where('tipe_obat','non_kronis')->count() > 0;
        @endphp

        {{-- PO / Faktur header --}}
        <div style="padding:.65rem 1.25rem;background:rgba(255,255,255,.025);border-bottom:1px solid rgba(255,255,255,.06);display:flex;align-items:center;gap:.85rem;flex-wrap:wrap;">
            <div style="display:flex;align-items:center;gap:.5rem;">
                <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="color:var(--gold2);flex-shrink:0;"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                <span class="font-mono" style="font-size:.72rem;color:var(--gold2);font-weight:700;">PO #{{ $poId }}</span>
                @if($po && $po->nomor_invoice)
                <span style="font-size:.7rem;color:var(--mut);">#{{ $po->nomor_invoice }}</span>
                @endif
            </div>
            <span style="font-size:.78rem;font-weight:600;color:var(--ink);">{{ $dist->name }}</span>
            @if($po)
            <span style="font-size:.7rem;color:var(--mut);">{{ $po->tanggal_po->format('d M Y') }}</span>
            @endif
            {{-- Tipe badges --}}
            @if($hasKronis)
            <span style="font-size:.65rem;padding:.1rem .45rem;border-radius:.3rem;background:rgba(63,207,142,.12);border:1px solid rgba(63,207,142,.25);color:var(--emer2);">Kronis</span>
            @endif
            @if($hasNonKro)
            <span style="font-size:.65rem;padding:.1rem .45rem;border-radius:.3rem;background:rgba(111,177,224,.12);border:1px solid rgba(111,177,224,.25);color:var(--blue);">Non-Kronis</span>
            @endif
            {{-- Faktur total --}}
            <div style="margin-left:auto;text-align:right;">
                <span class="font-mono" style="font-size:.82rem;font-weight:700;color:var(--ink);">Rp {{ number_format($poTotal,0,',','.') }}</span>
                @if($poSisa > 0)
                <span style="font-size:.68rem;color:var(--red2);margin-left:.5rem;">sisa Rp {{ number_format($poSisa,0,',','.') }}</span>
                @endif
            </div>
        </div>

        {{-- Tagihan rows for this PO --}}
        <div style="overflow-x:auto;overscroll-behavior-x:contain;-webkit-overflow-scrolling:touch;">
        <table style="width:100%;border-collapse:collapse;min-width:780px;">
            @foreach($poTagihan as $t)
            @php
                $aging  = $t->aging;
                $agingH = $t->aging_hari;
                $agingColor = match($aging) {
                    'overdue'   => 'var(--red2)',
                    'segera'    => 'var(--gold2)',
                    'perhatian' => '#e09a40',
                    'lunas'     => 'var(--emer2)',
                    default     => 'var(--mut)',
                };
                $borderColor = $t->tipe_obat === 'kronis'
                    ? 'rgba(63,207,142,.5)' : 'rgba(111,177,224,.5)';
                $statusCfg = match($t->status) {
                    'lunas'       => ['bg'=>'rgba(63,207,142,.15)', 'border'=>'rgba(63,207,142,.3)',  'color'=>'var(--emer2)', 'label'=>'Lunas'],
                    'sebagian'    => ['bg'=>'rgba(217,164,65,.15)', 'border'=>'rgba(217,164,65,.35)', 'color'=>'var(--gold2)', 'label'=>'Sebagian'],
                    'belum_bayar' => ['bg'=>'rgba(232,100,90,.12)', 'border'=>'rgba(232,100,90,.3)',  'color'=>'var(--red2)',  'label'=>'Belum Bayar'],
                    default       => ['bg'=>'rgba(100,100,100,.1)', 'border'=>'rgba(100,100,100,.2)', 'color'=>'var(--mut)',   'label'=>'Draft'],
                };
            @endphp
            <tr style="border-left:4px solid {{ $borderColor }};border-bottom:1px solid rgba(255,255,255,.03);background:rgba(0,0,0,.12);">
                <td style="padding:.55rem .75rem .55rem 1.25rem;min-width:145px;">
                    <div class="font-mono" style="font-size:.77rem;color:{{ $t->tipe_obat==='kronis' ? 'var(--emer2)' : 'var(--blue)' }};font-weight:600;">{{ $t->nomor_tagihan }}</div>
                    <div style="font-size:.63rem;color:var(--mut);">{{ $t->periode_bulan }}</div>
                </td>
                <td style="padding:.55rem .75rem;width:90px;text-align:center;">
                    <span style="display:inline-block;padding:.18rem .45rem;border-radius:.3rem;font-size:.65rem;font-weight:700;
                        {{ $t->tipe_obat==='kronis'
                            ? 'background:rgba(63,207,142,.12);border:1px solid rgba(63,207,142,.3);color:var(--emer2);'
                            : 'background:rgba(111,177,224,.12);border:1px solid rgba(111,177,224,.3);color:var(--blue);' }}">
                        {{ $t->label_tipe }}
                    </span>
                </td>
                <td class="font-mono" style="padding:.55rem .75rem;text-align:right;font-size:.82rem;">Rp {{ number_format($t->total_tagihan,0,',','.') }}</td>
                <td class="font-mono" style="padding:.55rem .75rem;text-align:right;font-size:.82rem;color:var(--emer2);">Rp {{ number_format($t->jumlah_dibayar,0,',','.') }}</td>
                <td class="font-mono" style="padding:.55rem .75rem;text-align:right;font-size:.82rem;font-weight:700;color:{{ $t->sisa_tagihan>0 ? 'var(--red2)' : 'var(--emer2)' }};">
                    Rp {{ number_format($t->sisa_tagihan,0,',','.') }}
                </td>
                <td style="padding:.55rem .75rem;text-align:center;min-width:110px;">
                    <div style="font-size:.76rem;font-weight:600;color:{{ $agingColor }};">{{ $t->tanggal_jatuh_tempo->format('d M Y') }}</div>
                    <div style="font-size:.63rem;color:{{ $agingColor }};">
                        @if($aging==='overdue') {{ abs($agingH) }} hari lewat
                        @elseif($aging==='lunas') Lunas
                        @else {{ $agingH }} hari lagi
                        @endif
                    </div>
                </td>
                <td style="padding:.55rem .75rem;text-align:center;width:95px;">
                    <span style="display:inline-block;padding:.18rem .5rem;border-radius:.3rem;font-size:.65rem;font-weight:700;
                        background:{{ $statusCfg['bg'] }};border:1px solid {{ $statusCfg['border'] }};color:{{ $statusCfg['color'] }};">
                        {{ $statusCfg['label'] }}
                    </span>
                </td>
                <td style="padding:.55rem .5rem;text-align:center;width:78px;">
                    @if($t->status === 'draft')
                    <button wire:click="konfirm({{ $t->id }})"
                        style="font-size:.68rem;padding:.22rem .5rem;border-radius:.3rem;cursor:pointer;background:rgba(217,164,65,.15);border:1px solid rgba(217,164,65,.3);color:var(--gold2);">
                        Konfirm
                    </button>
                    @elseif(in_array($t->status, ['belum_bayar','sebagian']))
                    <button wire:click="openBayar({{ $t->id }})"
                        style="font-size:.68rem;padding:.22rem .5rem;border-radius:.3rem;cursor:pointer;background:rgba(63,207,142,.15);border:1px solid rgba(63,207,142,.3);color:var(--emer2);">
                        Bayar
                    </button>
                    @else
                    <span style="font-size:.65rem;color:var(--mut);"><svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round" style="display:inline-block;vertical-align:middle"><polyline points="20 6 9 17 4 12"/></svg></span>
                    @endif
                </td>
            </tr>
            @endforeach
        </table>
        </div>

        {{-- Separator between POs --}}
        <div style="height:1px;background:rgba(255,255,255,.07);"></div>

        @empty
        <div style="text-align:center;padding:3rem;color:var(--mut);">
            <svg width="40" height="40" fill="none" stroke="currentColor" stroke-width="1" viewBox="0 0 24 24" style="margin:0 auto .75rem;display:block;"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
            Tidak ada tagihan ditemukan.
        </div>
        @endforelse

        {{-- Pagination --}}
        @if($this->tagihanList->hasPages())
        <div style="padding:.75rem 1.5rem;border-top:1px solid var(--line);display:flex;justify-content:space-between;align-items:center;font-size:.75rem;color:var(--mut);">
            <span>Hal {{ $this->tagihanList->currentPage() }} / {{ $this->tagihanList->lastPage() }} · {{ $this->tagihanList->total() }} tagihan</span>
            <div style="display:flex;gap:.35rem;">
                @if(!$this->tagihanList->onFirstPage())
                <button wire:click="previousPage" style="padding:.3rem .65rem;border-radius:.35rem;border:1px solid rgba(217,164,65,.3);color:var(--gold2);background:transparent;cursor:pointer;font-size:.75rem;"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" style="display:inline-block;vertical-align:middle"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg> Prev</button>
                @endif
                @if($this->tagihanList->hasMorePages())
                <button wire:click="nextPage" style="padding:.3rem .65rem;border-radius:.35rem;border:1px solid rgba(217,164,65,.3);color:var(--gold2);background:transparent;cursor:pointer;font-size:.75rem;">Next <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" style="display:inline-block;vertical-align:middle"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg></button>
                @endif
            </div>
        </div>
        @endif
    </div>

    {{-- ══ MODAL BAYAR ══════════════════════════════════════════════════ --}}
    @if($showBayar)
    @php $t = $bayarId ? \App\Models\Tagihan::find($bayarId) : null; @endphp
    <div style="position:fixed;inset:0;background:rgba(0,0,0,.7);z-index:5000;display:flex;align-items:flex-start;justify-content:center;padding:1.5rem 1rem;overflow-y:auto;" wire:click.self="$set('showBayar',false)">
        <div class="glass-card" style="width:100%;max-width:600px;padding:1.6rem;border-color:var(--emer);box-shadow:0 24px 64px rgba(0,0,0,.7);">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.25rem;">
                <div class="font-heading" style="font-size:1rem;color:{{ $editPembayaranId ? 'var(--gold2)' : 'var(--emer2)' }};">{{ $editPembayaranId ? '✎ Koreksi Pembayaran' : 'Catat Pembayaran' }}</div>
                <button wire:click="$set('showBayar',false)" style="background:none;border:none;color:var(--mut);cursor:pointer;font-size:1.2rem;"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" style="display:inline-block;vertical-align:middle"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg></button>
            </div>
            @if($t)
            <div style="background:rgba(255,255,255,.04);border-radius:.5rem;padding:.75rem 1rem;margin-bottom:1.25rem;font-size:.8rem;">
                <div style="display:flex;justify-content:space-between;margin-bottom:.3rem;">
                    <span style="color:var(--mut);">No. Tagihan</span>
                    <span class="font-mono" style="color:var(--gold2);">{{ $t->nomor_tagihan }}</span>
                </div>
                <div style="display:flex;justify-content:space-between;margin-bottom:.3rem;">
                    <span style="color:var(--mut);">PBF</span>
                    <span>{{ $t->distributor->name }}</span>
                </div>
                <div style="display:flex;justify-content:space-between;margin-bottom:.3rem;">
                    <span style="color:var(--mut);">Jenis</span>
                    <span style="color:{{ $t->tipe_obat==='kronis' ? 'var(--emer2)' : 'var(--blue)' }};">{{ $t->label_tipe }}</span>
                </div>
                <div style="display:flex;justify-content:space-between;border-top:1px solid var(--line);padding-top:.3rem;margin-top:.3rem;">
                    <span style="color:var(--mut);">Sisa Tagihan</span>
                    <span class="font-mono" style="font-weight:700;color:var(--red2);">Rp {{ number_format($t->sisa_tagihan,0,',','.') }}</span>
                </div>
            </div>
            @php $errS = 'color:var(--red2);font-size:.68rem;margin-top:.2rem;'; @endphp
            <form wire:submit="bayar">
                {{-- Metode pembayaran --}}
                <div style="margin-bottom:.85rem;">
                    <label class="form-label">Metode Pembayaran *</label>
                    <div style="display:flex;gap:.4rem;flex-wrap:wrap;">
                        @foreach(['transfer_bank'=>'🏦 Transfer','tunai'=>'💵 Tunai','qris'=>'📱 QRIS','giro'=>'📄 Giro','cek'=>'🧾 Cek','lainnya'=>'⋯ Lainnya'] as $mv=>$ml)
                        <button type="button" wire:click="$set('bayarMetode','{{ $mv }}')"
                            style="font-size:.72rem;font-weight:700;padding:.42rem .75rem;border-radius:.5rem;cursor:pointer;border:1px solid {{ $bayarMetode===$mv?'var(--emer)':'var(--line2)' }};background:{{ $bayarMetode===$mv?'rgba(63,207,142,.14)':'transparent' }};color:{{ $bayarMetode===$mv?'var(--emer2)':'var(--mut)' }};">{{ $ml }}</button>
                        @endforeach
                    </div>
                </div>

                {{-- Detail bank (non-tunai) --}}
                @if($bayarMetode !== 'tunai')
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:.65rem;margin-bottom:.85rem;">
                    <div>
                        <label class="form-label">Bank {!! $bayarMetode==='transfer_bank' ? '<span style="color:var(--red2)">*</span>' : '' !!}</label>
                        <input wire:model="bayarBank" list="bank-list" type="text" placeholder="mis. BCA" class="form-input">
                        <datalist id="bank-list">@foreach($bankList as $b)<option value="{{ $b }}"></option>@endforeach</datalist>
                        @error('bayarBank')<div style="{{ $errS }}">{{ $message }}</div>@enderror
                    </div>
                    <div>
                        <label class="form-label">No. Referensi/Transaksi {!! $bayarMetode==='transfer_bank' ? '<span style="color:var(--red2)">*</span>' : '' !!}</label>
                        <input wire:model="bayarNoRef" type="text" placeholder="mis. TRX0012345" class="form-input font-mono">
                        @error('bayarNoRef')<div style="{{ $errS }}">{{ $message }}</div>@enderror
                    </div>
                    <div>
                        <label class="form-label">Rekening Tujuan (PBF)</label>
                        <input wire:model="bayarRekening" type="text" placeholder="no. rekening" class="form-input font-mono">
                    </div>
                    <div>
                        <label class="form-label">Atas Nama</label>
                        <input wire:model="bayarAtasNama" type="text" class="form-input">
                    </div>
                </div>
                @endif

                {{-- Jumlah + tanggal + jam --}}
                <div style="display:grid;grid-template-columns:1.5fr 1fr .85fr;gap:.65rem;margin-bottom:.85rem;">
                    <div>
                        <label class="form-label">Jumlah (Rp) *</label>
                        <input wire:model="bayarJumlah" type="number" min="1" step="1" class="form-input font-mono" style="font-size:1rem;">
                        @error('bayarJumlah')<div style="{{ $errS }}">{{ $message }}</div>@enderror
                    </div>
                    <div>
                        <label class="form-label">Tanggal *</label>
                        <input wire:model="bayarTanggal" type="date" class="form-input">
                        @error('bayarTanggal')<div style="{{ $errS }}">{{ $message }}</div>@enderror
                    </div>
                    <div>
                        <label class="form-label">Jam</label>
                        <input wire:model="bayarJam" type="time" class="form-input">
                    </div>
                </div>

                {{-- Link bukti transfer — WAJIB non-tunai --}}
                @if($bayarMetode !== 'tunai')
                <div style="margin-bottom:.85rem;">
                    <label class="form-label">🔗 Link Bukti Transfer <span style="color:var(--red2)">*</span> <span style="color:var(--mut);font-weight:400;font-size:.66rem;">— upload dulu ke Google Drive, tempel link</span></label>
                    <input wire:model="bayarLinkBukti" type="url" placeholder="https://drive.google.com/…" class="form-input">
                    @error('bayarLinkBukti')<div style="{{ $errS }}">{{ $message }}</div>@enderror
                </div>
                @endif

                {{-- Link faktur pembelian — WAJIB kecuali pemutihan --}}
                <div style="margin-bottom:.85rem;">
                    <div style="display:flex;align-items:center;justify-content:space-between;gap:.5rem;flex-wrap:wrap;margin-bottom:.25rem;">
                        <label class="form-label" style="margin:0;">🧾 Link Faktur Pembelian {!! $bayarPemutihan ? '' : '<span style="color:var(--red2)">*</span>' !!} <span style="color:var(--mut);font-weight:400;font-size:.66rem;">— scan & upload</span></label>
                        <label style="display:inline-flex;align-items:center;gap:.35rem;font-size:.7rem;color:var(--gold2);cursor:pointer;font-weight:700;">
                            <input type="checkbox" wire:model.live="bayarPemutihan"> Pemutihan (tanpa faktur)
                        </label>
                    </div>
                    <input wire:model="bayarLinkFaktur" type="url" placeholder="https://drive.google.com/…" class="form-input" {{ $bayarPemutihan?'disabled':'' }} style="{{ $bayarPemutihan?'opacity:.45;':'' }}">
                    @error('bayarLinkFaktur')<div style="{{ $errS }}">{{ $message }}</div>@enderror
                </div>

                {{-- Catatan --}}
                <div style="margin-bottom:1rem;">
                    <label class="form-label">Catatan (opsional)</label>
                    <input wire:model="bayarCatatan" type="text" placeholder="keterangan tambahan" class="form-input">
                </div>

                {{-- Riwayat pembayaran (arsip audit — tak pernah dihapus) --}}
                @php $riw = $this->riwayatBayar; $riwAktif = $riw->where('dibatalkan', false); @endphp
                @if($riw->count())
                <div style="margin-bottom:1rem;border-top:1px solid var(--line);padding-top:.7rem;">
                    <div class="form-label" style="margin-bottom:.35rem;display:flex;align-items:center;gap:.4rem;">
                        <span>Riwayat Pembayaran ({{ $riwAktif->count() }} aktif{{ $riw->count()>$riwAktif->count() ? ' · '.($riw->count()-$riwAktif->count()).' dibatalkan' : '' }})</span>
                    </div>
                    @foreach($riw as $r)
                    @php $miss = ! $r->dibatalkan && ! $r->link_faktur && ! $r->pemutihan; @endphp
                    <div style="border-top:1px solid rgba(31,61,48,.35);padding:.4rem 0;{{ $r->dibatalkan ? 'opacity:.5;' : '' }}{{ $miss ? 'background:rgba(217,164,65,.06);border-left:2px solid var(--gold);padding-left:.4rem;' : '' }}">
                        <div style="display:flex;align-items:center;gap:.5rem;font-size:.7rem;flex-wrap:wrap;">
                            <span class="font-mono" style="font-weight:800;color:{{ $r->dibatalkan ? 'var(--mut2)' : 'var(--emer2)' }};{{ $r->dibatalkan ? 'text-decoration:line-through;' : '' }}">Rp {{ number_format($r->jumlah,0,',','.') }}</span>
                            <span style="color:var(--mut2);">{{ $r->metodeLabel() }}{{ $r->bank_nama?' · '.$r->bank_nama:'' }}{{ $r->nomor_referensi?' · #'.$r->nomor_referensi:'' }}</span>
                            @if($r->dibatalkan)<span style="font-size:.58rem;font-weight:800;color:var(--red2);background:rgba(232,100,90,.14);border:1px solid rgba(232,100,90,.35);border-radius:999px;padding:.05rem .4rem;">DIBATALKAN</span>@endif
                            <span style="color:var(--mut2);margin-left:auto;">{{ $r->tanggal->format('d/m/y') }}{{ $r->waktu?' '.substr($r->waktu,0,5):'' }}</span>
                            @if($r->link_bukti)<a href="{{ $r->link_bukti }}" target="_blank" style="color:var(--blue);text-decoration:none;">🔗bukti</a>@endif
                            @if($r->link_faktur)<a href="{{ $r->link_faktur }}" target="_blank" style="color:var(--gold2);text-decoration:none;">🧾faktur</a>@elseif($r->pemutihan)<span style="color:var(--gold2);" title="Pemutihan — faktur tidak wajib">putih</span>@elseif($miss)<span style="color:var(--gold);font-weight:700;" title="Belum ada faktur — klik Edit untuk menambahkan">⚠ tanpa faktur</span>@endif
                        </div>
                        {{-- meta audit --}}
                        <div style="font-size:.58rem;color:var(--mut2);margin-top:.15rem;display:flex;gap:.5rem;flex-wrap:wrap;">
                            @if($r->dicatat_oleh)<span>dicatat: {{ $r->dicatat_oleh }}</span>@endif
                            @if($r->diubah_at)<span style="color:var(--gold2);">diedit: {{ $r->diubah_oleh }} · {{ $r->diubah_at->format('d/m/y H:i') }}</span>@endif
                            @if($r->dibatalkan)<span style="color:var(--red2);">dibatalkan: {{ $r->dibatalkan_oleh }} — {{ $r->alasan_batal }}</span>@endif
                        </div>
                        {{-- aksi koreksi --}}
                        @if(! $r->dibatalkan && $voidId !== $r->id)
                        <div style="display:flex;gap:.4rem;margin-top:.3rem;">
                            <button type="button" wire:click="editBayar({{ $r->id }})" style="font-size:.6rem;font-weight:700;padding:.15rem .5rem;border-radius:.4rem;background:rgba(217,164,65,.12);border:1px solid rgba(217,164,65,.35);color:var(--gold2);cursor:pointer;">✎ Edit{{ $miss ? ' / +faktur' : '' }}</button>
                            <button type="button" wire:click="mintaBatal({{ $r->id }})" style="font-size:.6rem;font-weight:700;padding:.15rem .5rem;border-radius:.4rem;background:transparent;border:1px solid rgba(232,100,90,.3);color:var(--red2);cursor:pointer;">✕ Batalkan</button>
                        </div>
                        @endif
                        {{-- panel konfirmasi pembatalan --}}
                        @if($voidId === $r->id)
                        <div style="margin-top:.4rem;padding:.5rem;border:1px solid rgba(232,100,90,.35);border-radius:.5rem;background:rgba(232,100,90,.06);">
                            <div style="font-size:.62rem;color:var(--red2);font-weight:700;margin-bottom:.3rem;">Alasan pembatalan (wajib — jejak audit):</div>
                            <input wire:model="voidAlasan" type="text" placeholder="mis. salah input jumlah / transaksi gagal / dobel" class="form-input" style="font-size:.7rem;margin-bottom:.35rem;">
                            @error('voidAlasan')<div style="{{ $errS }}">{{ $message }}</div>@enderror
                            <div style="display:flex;gap:.4rem;">
                                <button type="button" wire:click="batalkanBayar" style="font-size:.62rem;font-weight:800;padding:.28rem .6rem;border-radius:.4rem;background:rgba(232,100,90,.18);border:1px solid rgba(232,100,90,.45);color:var(--red2);cursor:pointer;">Ya, Batalkan Pembayaran</button>
                                <button type="button" wire:click="tutupBatal" style="font-size:.62rem;padding:.28rem .6rem;border-radius:.4rem;background:transparent;border:1px solid var(--line2);color:var(--mut);cursor:pointer;">Kembali</button>
                            </div>
                        </div>
                        @endif
                    </div>
                    @endforeach
                </div>
                @endif

                <div style="display:flex;gap:.6rem;">
                    <button type="submit" class="btn-gold" style="flex:1;">
                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
                        {{ $editPembayaranId ? 'Simpan Koreksi Pembayaran' : 'Catat & Arsipkan Pembayaran' }}
                    </button>
                    @if($editPembayaranId)
                    <button type="button" wire:click="batalEditBayar" class="btn-outline">Batal Edit</button>
                    @else
                    <button type="button" wire:click="$set('showBayar',false)" class="btn-outline">Batal</button>
                    @endif
                </div>
            </form>
            @endif
        </div>
    </div>
    @endif
</div>

<style>
.cb-scroll::-webkit-scrollbar{width:4px;}.cb-scroll::-webkit-scrollbar-track{background:transparent;}.cb-scroll::-webkit-scrollbar-thumb{background:rgba(255,255,255,.12);border-radius:4px;}
mark.hl{background:rgba(217,164,65,.3);color:var(--gold2);border-radius:2px;font-weight:700;font-style:normal;}
@keyframes pbfSlideIn{from{opacity:0;transform:translateY(-4px) scaleY(.96);}to{opacity:1;transform:translateY(0) scaleY(1);}}
.pbf-dd-open{animation:pbfSlideIn .12s cubic-bezier(.4,0,.2,1) both;}
</style>

<script>
window.PbfCb = (function () {
    let activeIdx = 0;
    let filtered  = [];
    let selId     = 0;

    const el  = id => document.getElementById(id);
    const esc = s  => String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');

    function hlText(str, q) {
        if (!q) return esc(str);
        const r = q.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
        return esc(str).replace(new RegExp(r, 'gi'), m => '<mark class="hl">' + m + '</mark>');
    }

    function getOpts() {
        const sel = el('pbf-sel');
        if (!sel) return [];
        // First item is "Semua PBF" (value 0), keep it always
        return Array.from(sel.options).map(o => ({ id: o.value, name: o.dataset.name || o.text }));
    }

    function posDD() {
        const btn = el('pbf-btn'), dd = el('pbf-dd');
        if (!btn || !dd) return;
        const r = btn.getBoundingClientRect();
        const below = window.innerHeight - r.bottom - 8;
        dd.style.top   = (r.bottom + 4) + 'px';
        dd.style.left  = r.left + 'px';
        dd.style.width = Math.max(r.width, 200) + 'px';
        dd.style.maxHeight = Math.max(120, Math.min(280, below)) + 'px';
    }

    function renderList(q) {
        const listEl = el('pbf-list'), footEl = el('pbf-foot');
        if (!listEl) return;
        const query = (q || '').trim().toLowerCase();
        const opts  = getOpts();
        filtered = query
            ? opts.filter(o => o.name.toLowerCase().includes(query))
            : [...opts];
        activeIdx = Math.min(activeIdx, Math.max(0, filtered.length - 1));

        if (!filtered.length) {
            listEl.innerHTML = '<div style="padding:.85rem;text-align:center;font-size:.76rem;color:var(--mut);">Tidak ditemukan</div>';
        } else {
            listEl.innerHTML = filtered.map((o, i) => {
                const isAll     = o.id === '0';
                const isActive  = i === activeIdx;
                const isSel     = selId == o.id;
                return `<div class="pbf-item" data-id="${o.id}" data-name="${esc(o.name)}"
                    style="display:flex;align-items:center;gap:.55rem;padding:.5rem .85rem;font-size:.8rem;
                        cursor:pointer;border-bottom:1px solid rgba(255,255,255,.03);transition:background .07s;
                        ${isActive ? 'background:rgba(217,164,65,.1);' : ''}">
                    <span style="width:13px;flex-shrink:0;text-align:center;font-size:.72rem;color:var(--gold2);">${isSel ? '<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" style="display:inline-block;vertical-align:middle"><polyline points="20 6 9 17 4 12"/></svg>' : ''}</span>
                    <span style="${isSel ? 'color:var(--gold2);font-weight:600;' : isAll ? 'color:var(--mut);font-style:italic;' : 'color:var(--ink);'}">${hlText(o.name, q)}</span>
                </div>`;
            }).join('');

            listEl.querySelectorAll('.pbf-item').forEach((item, i) => {
                item.addEventListener('mouseenter', () => {
                    activeIdx = i;
                    listEl.querySelectorAll('.pbf-item').forEach((e, j) => {
                        e.style.background = j === i ? 'rgba(217,164,65,.1)' : '';
                    });
                });
                item.addEventListener('mousedown', e => {
                    e.preventDefault();
                    pick(item.dataset.id, item.dataset.name);
                });
            });
        }

        if (footEl) {
            const total = getOpts().length - 1; // minus "Semua PBF"
            footEl.textContent = query ? (filtered.length + ' hasil') : (total + ' PBF');
        }

        const activeEl = listEl.querySelectorAll('.pbf-item')[activeIdx];
        if (activeEl) activeEl.scrollIntoView({ block: 'nearest' });
    }

    function pick(id, name) {
        selId = +id;
        // Update hidden select -> trigger wire:model.live -> Livewire re-filter
        const sel = el('pbf-sel');
        if (sel) {
            sel.value = id;
            sel.dispatchEvent(new Event('input',  { bubbles: true }));
            sel.dispatchEvent(new Event('change', { bubbles: true }));
        }
        // Update display
        const disp = el('pbf-display');
        if (disp) {
            disp.textContent = id === '0' ? 'Semua PBF' : name;
            disp.style.color = id === '0' ? 'var(--ink)' : 'var(--gold2)';
            disp.style.fontWeight = id === '0' ? '' : '600';
        }
        doClose();
    }

    function doOpen() {
        const dd = el('pbf-dd');
        if (!dd) return;
        if (dd.parentElement !== document.body) document.body.appendChild(dd);
        activeIdx = 0;
        posDD();
        const qi = el('pbf-qi');
        if (qi) qi.value = '';
        el('pbf-qclr') && (el('pbf-qclr').style.display = 'none');
        renderList('');
        dd.style.display = 'block';
        dd.classList.add('pbf-dd-open');
        const btn = el('pbf-btn');
        if (btn) { btn.style.borderColor = 'var(--gold)'; btn.style.boxShadow = '0 0 0 2px rgba(217,164,65,.18)'; }
        const chev = el('pbf-chevron');
        if (chev) { chev.style.transform = 'rotate(180deg)'; chev.style.color = 'var(--gold2)'; }
        setTimeout(() => el('pbf-qi')?.focus(), 40);
    }

    function doClose() {
        const dd = el('pbf-dd');
        if (dd) dd.style.display = 'none';
        const btn = el('pbf-btn');
        if (btn) { btn.style.borderColor = ''; btn.style.boxShadow = ''; }
        const chev = el('pbf-chevron');
        if (chev) { chev.style.transform = ''; chev.style.color = 'var(--mut)'; }
    }

    function isOpen() {
        const dd = el('pbf-dd');
        return dd && dd.style.display !== 'none';
    }

    function moveActive(dir) {
        const listEl = el('pbf-list');
        if (!listEl) return;
        const items = listEl.querySelectorAll('.pbf-item');
        activeIdx = Math.max(0, Math.min(activeIdx + dir, items.length - 1));
        items.forEach((e, i) => { e.style.background = i === activeIdx ? 'rgba(217,164,65,.1)' : ''; });
        items[activeIdx]?.scrollIntoView({ block: 'nearest' });
    }

    function syncDisplay() {
        const sel = el('pbf-sel');
        if (!sel) return;
        selId = +sel.value || 0;
        const disp = el('pbf-display');
        if (!disp) return;
        if (selId) {
            const opt = Array.from(sel.options).find(o => +o.value === selId);
            disp.textContent = opt ? (opt.dataset.name || opt.text) : 'Semua PBF';
            disp.style.color = 'var(--gold2)';
            disp.style.fontWeight = '600';
        } else {
            disp.textContent = 'Semua PBF';
            disp.style.color = 'var(--ink)';
            disp.style.fontWeight = '';
        }
        // Ensure dd stays in body after Livewire re-render
        const dd = el('pbf-dd');
        if (dd && dd.parentElement !== document.body) document.body.appendChild(dd);
    }

    // Global events
    document.addEventListener('click', e => {
        if (!e.target.closest('#pbf-wrap') && !e.target.closest('#pbf-dd')) doClose();
    });
    window.addEventListener('scroll', () => {
        if (isOpen()) posDD();
    }, true);
    window.addEventListener('resize', () => { if (isOpen()) posDD(); });
    document.addEventListener('livewire:update', () => syncDisplay());
    document.addEventListener('livewire:initialized', () => syncDisplay());
    if (document.readyState !== 'loading') syncDisplay();
    else document.addEventListener('DOMContentLoaded', () => syncDisplay());

    return {
        toggle()  { isOpen() ? doClose() : doOpen(); },
        keyBtn(e) {
            if (e.key === 'ArrowDown' || e.key === 'Enter') { doOpen(); e.preventDefault(); }
            else if (e.key === 'Escape') doClose();
        },
        keyDd(e) {
            if (e.key === 'ArrowDown')  { moveActive(1);  e.preventDefault(); }
            else if (e.key === 'ArrowUp')   { moveActive(-1); e.preventDefault(); }
            else if (e.key === 'Enter') {
                const items = el('pbf-list')?.querySelectorAll('.pbf-item');
                const item  = items?.[activeIdx];
                if (item) pick(item.dataset.id, item.dataset.name);
                e.preventDefault();
            }
            else if (e.key === 'Escape') { doClose(); el('pbf-btn')?.focus(); }
        },
        filter(q) {
            activeIdx = 0;
            const qclr = el('pbf-qclr');
            if (qclr) qclr.style.display = q ? 'flex' : 'none';
            renderList(q);
        },
        clearQ() {
            const qi = el('pbf-qi');
            if (qi) { qi.value = ''; qi.focus(); }
            el('pbf-qclr') && (el('pbf-qclr').style.display = 'none');
            activeIdx = 0;
            renderList('');
        },
        doClose,
        syncDisplay,
    };
})();
</script>
