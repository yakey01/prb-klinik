<div>
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

        {{-- Distributor --}}
        <select wire:model.live="filterDist" style="background:var(--panel);border:1px solid var(--line2);color:var(--ink);border-radius:.45rem;padding:.4rem .75rem;font-size:.78rem;appearance:auto;-webkit-appearance:auto;">
            <option value="0">Semua PBF</option>
            @foreach($this->distributors as $d)
            <option value="{{ $d->id }}">{{ $d->name }}</option>
            @endforeach
        </select>

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
    <div class="glass-card" style="overflow:hidden;">
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
        <div style="overflow-x:auto;">
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
                    <span style="font-size:.65rem;color:var(--mut);">✓</span>
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
                <button wire:click="previousPage" style="padding:.3rem .65rem;border-radius:.35rem;border:1px solid rgba(217,164,65,.3);color:var(--gold2);background:transparent;cursor:pointer;font-size:.75rem;">← Prev</button>
                @endif
                @if($this->tagihanList->hasMorePages())
                <button wire:click="nextPage" style="padding:.3rem .65rem;border-radius:.35rem;border:1px solid rgba(217,164,65,.3);color:var(--gold2);background:transparent;cursor:pointer;font-size:.75rem;">Next →</button>
                @endif
            </div>
        </div>
        @endif
    </div>

    {{-- ══ MODAL BAYAR ══════════════════════════════════════════════════ --}}
    @if($showBayar)
    @php $t = $bayarId ? \App\Models\Tagihan::find($bayarId) : null; @endphp
    <div style="position:fixed;inset:0;background:rgba(0,0,0,.65);z-index:5000;display:flex;align-items:center;justify-content:center;padding:1rem;" wire:click.self="$set('showBayar',false)">
        <div class="glass-card" style="width:100%;max-width:440px;padding:1.75rem;border-color:var(--emer);box-shadow:0 24px 64px rgba(0,0,0,.7);">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.25rem;">
                <div class="font-heading" style="font-size:1rem;color:var(--emer2);">Catat Pembayaran</div>
                <button wire:click="$set('showBayar',false)" style="background:none;border:none;color:var(--mut);cursor:pointer;font-size:1.2rem;">✕</button>
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
            <form wire:submit="bayar">
                <div style="margin-bottom:.85rem;">
                    <label class="form-label">Jumlah Dibayar (Rp) *</label>
                    <input wire:model="bayarJumlah" type="number" min="1" step="1000" class="form-input font-mono" style="font-size:1rem;">
                    @error('bayarJumlah')<div style="color:var(--red2);font-size:.7rem;margin-top:.2rem;">{{ $message }}</div>@enderror
                </div>
                <div style="margin-bottom:.85rem;">
                    <label class="form-label">Tanggal Bayar *</label>
                    <input wire:model="bayarTanggal" type="date" class="form-input">
                    @error('bayarTanggal')<div style="color:var(--red2);font-size:.7rem;margin-top:.2rem;">{{ $message }}</div>@enderror
                </div>
                <div style="margin-bottom:1.25rem;">
                    <label class="form-label">Catatan (opsional)</label>
                    <input wire:model="bayarCatatan" type="text" placeholder="mis. Transfer BCA ref. 123" class="form-input">
                </div>
                <div style="display:flex;gap:.6rem;">
                    <button type="submit" class="btn-gold" style="flex:1;">
                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
                        Catat Pembayaran
                    </button>
                    <button type="button" wire:click="$set('showBayar',false)" class="btn-outline">Batal</button>
                </div>
            </form>
            @endif
        </div>
    </div>
    @endif
</div>
