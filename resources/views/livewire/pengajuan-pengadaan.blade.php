<div>
    @php
        $rp = fn ($n) => 'Rp ' . number_format((float) $n, 0, ',', '.');
        $k  = $this->kpi;
        $isMgr = $this->isManajer();
        $prioBadge = fn ($p) => match ($p) {
            'urgent' => ['Urgent', 'var(--red2)', 'rgba(232,100,90,.12)', 'rgba(232,100,90,.3)'],
            'segera' => ['Segera', 'var(--gold2)', 'rgba(217,164,65,.12)', 'rgba(217,164,65,.3)'],
            default  => ['Rutin', 'var(--mut)', 'rgba(255,255,255,.04)', 'var(--line2)'],
        };
    @endphp

    {{-- ══ HEADER ══ --}}
    <div style="display:flex;align-items:flex-start;justify-content:space-between;flex-wrap:wrap;gap:1rem;margin-bottom:1.2rem;">
        <div>
            <div class="font-label" style="font-size:.7rem;color:var(--mut);margin-bottom:.25rem;">Pengadaan</div>
            <h2 class="font-heading" style="font-size:1.5rem;color:var(--ink);margin:0;">Pengajuan Pengadaan</h2>
            <p style="color:var(--mut);font-size:.78rem;margin-top:.3rem;max-width:52ch;">Ajukan usulan belanja obat → disetujui manajer → baru direalisasikan jadi PO. Standar izin belanja klinik.</p>
        </div>
        @unless($showForm)
        <button wire:click="openAdd" class="btn-emerald" style="display:inline-flex;align-items:center;gap:.5rem;padding:.6rem 1.1rem;border-radius:.7rem;background:linear-gradient(180deg,rgba(63,207,142,.9),rgba(63,207,142,.75));border:1px solid rgba(63,207,142,.5);color:#04150d;font-weight:800;font-size:.82rem;cursor:pointer;box-shadow:0 6px 18px rgba(63,207,142,.2);">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Ajukan Pengadaan
        </button>
        @endunless
    </div>

    @unless($showForm)
    {{-- ══ KPI ══ --}}
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(170px,1fr));gap:.85rem;margin-bottom:1.2rem;">
        <div class="glass-card" style="padding:.9rem 1.1rem;border-color:rgba(91,155,213,.3);">
            <div style="font-size:.6rem;color:var(--mut);text-transform:uppercase;letter-spacing:.06em;">Menunggu Persetujuan</div>
            <div class="font-mono" style="font-size:1.35rem;font-weight:800;color:#5b9bd5;">{{ $k['menunggu'] }}</div>
            <div style="font-size:.66rem;color:var(--mut);margin-top:.15rem;">{{ $rp($k['nilai_menunggu']) }} nilai</div>
        </div>
        <div class="glass-card" style="padding:.9rem 1.1rem;border-color:rgba(63,207,142,.25);">
            <div style="font-size:.6rem;color:var(--mut);text-transform:uppercase;letter-spacing:.06em;">Disetujui (siap PO)</div>
            <div class="font-mono" style="font-size:1.35rem;font-weight:800;color:var(--emer2);">{{ $k['disetujui'] }}</div>
        </div>
        <div class="glass-card" style="padding:.9rem 1.1rem;">
            <div style="font-size:.6rem;color:var(--mut);text-transform:uppercase;letter-spacing:.06em;">Draft</div>
            <div class="font-mono" style="font-size:1.35rem;font-weight:800;color:var(--mut);">{{ $k['draft'] }}</div>
        </div>
        <div class="glass-card" style="padding:.9rem 1.1rem;display:flex;flex-direction:column;justify-content:center;">
            <div style="font-size:.62rem;color:var(--mut);line-height:1.4;">
                @if($isMgr)<span style="color:var(--emer2);font-weight:700;">✓ Anda manajer</span> — bisa menyetujui/menolak.@else Persetujuan oleh manajer.@endif
            </div>
        </div>
    </div>

    {{-- ══ TOOLBAR ══ --}}
    <div style="display:flex;gap:.6rem;flex-wrap:wrap;align-items:center;margin-bottom:1rem;">
        <input wire:model.live.debounce.300ms="search" type="text" placeholder="Cari no. PR / justifikasi…"
            style="flex:1;min-width:200px;padding:.55rem .9rem;border-radius:.6rem;background:var(--card);border:1px solid var(--line2);color:var(--ink);font-size:.8rem;">
        <div style="display:flex;gap:.25rem;flex-wrap:wrap;">
            @foreach(['semua'=>'Semua','draft'=>'Draft','diajukan'=>'Menunggu','disetujui'=>'Disetujui','ditolak'=>'Ditolak','revisi'=>'Revisi','direalisasi'=>'Direalisasi'] as $sv=>$sl)
            <button wire:click="$set('filterStatus','{{ $sv }}')"
                style="font-size:.68rem;padding:.4rem .7rem;border-radius:999px;cursor:pointer;border:1px solid {{ $filterStatus===$sv ? 'var(--gold)' : 'var(--line2)' }};background:{{ $filterStatus===$sv ? 'rgba(217,164,65,.12)' : 'transparent' }};color:{{ $filterStatus===$sv ? 'var(--gold2)' : 'var(--mut)' }};">{{ $sl }}</button>
            @endforeach
        </div>
    </div>

    {{-- ══ DAFTAR ══ --}}
    <div class="glass-card" style="padding:0;overflow:hidden;">
        <div style="overflow-x:auto;">
        <table style="width:100%;border-collapse:collapse;font-size:.78rem;min-width:720px;">
            <thead>
                <tr style="color:var(--mut);background:rgba(255,255,255,.02);">
                    <th style="text-align:left;padding:.7rem .9rem;font-size:.6rem;text-transform:uppercase;letter-spacing:.05em;">No. / Tanggal</th>
                    <th style="text-align:left;padding:.7rem .5rem;font-size:.6rem;text-transform:uppercase;">Prioritas</th>
                    <th style="text-align:right;padding:.7rem .5rem;font-size:.6rem;text-transform:uppercase;">Item</th>
                    <th style="text-align:right;padding:.7rem .5rem;font-size:.6rem;text-transform:uppercase;">Nilai Beli</th>
                    <th style="text-align:right;padding:.7rem .5rem;font-size:.6rem;text-transform:uppercase;">Est. Laba</th>
                    <th style="text-align:center;padding:.7rem .5rem;font-size:.6rem;text-transform:uppercase;">Status</th>
                    <th style="text-align:right;padding:.7rem .9rem;font-size:.6rem;text-transform:uppercase;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($this->daftar as $p)
                @php [$plbl,$pcol,$pbg,$pbd] = $prioBadge($p->prioritas); @endphp
                <tr wire:key="pr-{{ $p->id }}" style="border-top:1px solid var(--line);">
                    <td style="padding:.6rem .9rem;">
                        <button wire:click="openDetail({{ $p->id }})" class="font-mono" style="background:none;border:none;color:var(--gold2);font-weight:700;cursor:pointer;padding:0;font-size:.8rem;">{{ $p->no_pengajuan }}</button>
                        <div style="font-size:.64rem;color:var(--mut2);">{{ $p->tanggal->translatedFormat('d M Y') }} · {{ $p->pemohon_nama ?? '—' }}</div>
                    </td>
                    <td style="padding:.6rem .5rem;"><span style="font-size:.62rem;font-weight:700;padding:.12rem .5rem;border-radius:999px;color:{{ $pcol }};background:{{ $pbg }};border:1px solid {{ $pbd }};">{{ $plbl }}</span></td>
                    <td class="font-mono" style="padding:.6rem .5rem;text-align:right;color:var(--mut);">{{ $p->items_count ?? $p->items()->count() }}</td>
                    <td class="font-mono" style="padding:.6rem .5rem;text-align:right;color:var(--red2);">{{ $rp($p->total_beli) }}</td>
                    <td class="font-mono" style="padding:.6rem .5rem;text-align:right;color:{{ $p->estimasi_laba>=0?'var(--emer2)':'var(--red2)' }};">{{ ($p->estimasi_laba>=0?'+':'−').$rp(abs($p->estimasi_laba)) }}</td>
                    <td style="padding:.6rem .5rem;text-align:center;">
                        <span style="font-size:.64rem;font-weight:700;padding:.16rem .6rem;border-radius:999px;color:{{ $p->statusColor() }};background:color-mix(in srgb,{{ $p->statusColor() }} 14%, transparent);border:1px solid {{ $p->statusColor() }};white-space:nowrap;">{{ $p->statusLabel() }}</span>
                    </td>
                    <td style="padding:.6rem .9rem;text-align:right;white-space:nowrap;">
                        @if($p->bisaDiajukan())
                            <button wire:click="openEdit({{ $p->id }})" title="Edit" style="background:none;border:none;color:var(--mut);cursor:pointer;padding:.2rem;">✎</button>
                            <button wire:click="ajukan({{ $p->id }})" title="Ajukan" style="font-size:.66rem;padding:.25rem .6rem;border-radius:.5rem;background:rgba(91,155,213,.12);border:1px solid rgba(91,155,213,.35);color:#5b9bd5;cursor:pointer;font-weight:700;">Ajukan →</button>
                        @endif
                        @if($p->bisaApprove() && $isMgr)
                            <button wire:click="openApprove({{ $p->id }})" title="Setujui" style="font-size:.66rem;padding:.25rem .6rem;border-radius:.5rem;background:rgba(63,207,142,.14);border:1px solid rgba(63,207,142,.4);color:var(--emer2);cursor:pointer;font-weight:700;">✓ Setujui</button>
                            <button wire:click="openTolak({{ $p->id }})" title="Tolak" style="font-size:.66rem;padding:.25rem .55rem;border-radius:.5rem;background:rgba(232,100,90,.1);border:1px solid rgba(232,100,90,.3);color:var(--red2);cursor:pointer;">Tolak</button>
                        @endif
                        @if($p->bisaRealisasi())
                            <button wire:click="realisasi({{ $p->id }})" wire:confirm="Realisasikan {{ $p->no_pengajuan }} menjadi Purchase Order? Stok & tagihan akan diperbarui." title="Realisasi ke PO" style="font-size:.66rem;padding:.25rem .6rem;border-radius:.5rem;background:linear-gradient(180deg,rgba(63,207,142,.9),rgba(63,207,142,.7));border:1px solid rgba(63,207,142,.5);color:#04150d;cursor:pointer;font-weight:800;">🛒 Belanja (PO)</button>
                        @endif
                        <button wire:click="openDetail({{ $p->id }})" title="Detail" style="background:none;border:none;color:var(--mut);cursor:pointer;padding:.2rem;">⋯</button>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" style="padding:2.5rem;text-align:center;color:var(--mut);">Belum ada pengajuan. Klik <strong style="color:var(--emer2);">Ajukan Pengadaan</strong> untuk memulai.</td></tr>
                @endforelse
            </tbody>
        </table>
        </div>
    </div>
    <div style="margin-top:.8rem;">{{ $this->daftar->links() }}</div>

    @else
    {{-- ══════════ FORM PENGAJUAN ══════════ --}}
    <div class="glass-card" style="padding:1.2rem 1.3rem;">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1rem;">
            <h3 class="font-heading" style="font-size:1.1rem;color:var(--ink);margin:0;">{{ $editId ? 'Edit Pengajuan' : 'Pengajuan Pengadaan Baru' }}</h3>
            <button wire:click="cancel" style="background:none;border:none;color:var(--mut);cursor:pointer;font-size:1.2rem;">✕</button>
        </div>

        {{-- Header fields --}}
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:.8rem;margin-bottom:1rem;">
            <div>
                <label style="font-size:.64rem;color:var(--mut);text-transform:uppercase;letter-spacing:.05em;">Tanggal</label>
                <input type="date" wire:model="tanggal" style="width:100%;margin-top:.3rem;padding:.5rem .7rem;border-radius:.55rem;background:var(--card);border:1px solid var(--line2);color:var(--ink);font-size:.8rem;">
            </div>
            <div>
                <label style="font-size:.64rem;color:var(--mut);text-transform:uppercase;letter-spacing:.05em;">Distributor <span style="color:var(--red2);">*ajukan</span></label>
                <select wire:model="distributor_id" style="width:100%;margin-top:.3rem;padding:.5rem .7rem;border-radius:.55rem;background:var(--card);border:1px solid var(--line2);color:var(--ink);font-size:.8rem;">
                    <option value="0">— pilih —</option>
                    @foreach($this->distributors as $d)<option value="{{ $d->id }}">{{ $d->name }}</option>@endforeach
                </select>
                @error('distributor_id')<div style="color:var(--red2);font-size:.68rem;margin-top:.2rem;">{{ $message }}</div>@enderror
            </div>
            <div>
                <label style="font-size:.64rem;color:var(--mut);text-transform:uppercase;letter-spacing:.05em;">Prioritas</label>
                <select wire:model="prioritas" style="width:100%;margin-top:.3rem;padding:.5rem .7rem;border-radius:.55rem;background:var(--card);border:1px solid var(--line2);color:var(--ink);font-size:.8rem;">
                    <option value="rutin">Rutin</option>
                    <option value="segera">Segera</option>
                    <option value="urgent">Urgent</option>
                </select>
            </div>
        </div>
        <div style="margin-bottom:1rem;">
            <label style="font-size:.64rem;color:var(--mut);text-transform:uppercase;letter-spacing:.05em;">Justifikasi / Alasan Belanja <span style="color:var(--red2);">*wajib untuk diajukan</span></label>
            <textarea wire:model="justifikasi" rows="2" placeholder="Mis. stok kritis Metformin < minimum, permintaan pasien PRB meningkat…"
                style="width:100%;margin-top:.3rem;padding:.5rem .7rem;border-radius:.55rem;background:var(--card);border:1px solid var(--line2);color:var(--ink);font-size:.8rem;resize:vertical;"></textarea>
            @error('justifikasi')<div style="color:var(--red2);font-size:.68rem;margin-top:.2rem;">{{ $message }}</div>@enderror
        </div>

        {{-- Item rows --}}
        <div style="border-top:1px solid var(--line);padding-top:.9rem;margin-bottom:.5rem;display:flex;align-items:center;justify-content:space-between;">
            <span style="font-size:.7rem;font-weight:700;color:var(--ink);text-transform:uppercase;letter-spacing:.04em;">Daftar Obat Diusulkan</span>
            <button wire:click="addRow" style="font-size:.68rem;padding:.3rem .7rem;border-radius:.5rem;background:rgba(63,207,142,.1);border:1px solid rgba(63,207,142,.3);color:var(--emer2);cursor:pointer;font-weight:700;">+ Baris</button>
        </div>

        <div style="overflow-x:auto;">
        <table style="width:100%;border-collapse:collapse;font-size:.75rem;min-width:820px;">
            <thead><tr style="color:var(--mut);">
                <th style="text-align:left;padding:.35rem .4rem;font-size:.58rem;text-transform:uppercase;">Obat</th>
                <th style="text-align:right;padding:.35rem .4rem;font-size:.58rem;text-transform:uppercase;">Box</th>
                <th style="text-align:right;padding:.35rem .4rem;font-size:.58rem;text-transform:uppercase;">Isi/Box</th>
                <th style="text-align:right;padding:.35rem .4rem;font-size:.58rem;text-transform:uppercase;">Harga Beli/Box</th>
                <th style="text-align:right;padding:.35rem .4rem;font-size:.58rem;text-transform:uppercase;" title="Klaim BPJS per unit (kronis)">Klaim/unit</th>
                <th style="text-align:right;padding:.35rem .4rem;font-size:.58rem;text-transform:uppercase;">Subtotal Beli</th>
                <th style="text-align:right;padding:.35rem .4rem;font-size:.58rem;text-transform:uppercase;">Est. Klaim</th>
                <th></th>
            </tr></thead>
            <tbody>
                @foreach($rows as $i => $row)
                <tr wire:key="row-{{ $i }}" style="border-top:1px solid rgba(31,61,48,.4);">
                    <td style="padding:.3rem .4rem;min-width:220px;">
                        <select wire:model.live="rows.{{ $i }}.obat_id" style="width:100%;padding:.4rem .5rem;border-radius:.45rem;background:var(--card);border:1px solid var(--line2);color:var(--ink);font-size:.74rem;">
                            <option value="0">— pilih obat —</option>
                            @foreach($this->obatList as $o)<option value="{{ $o->id }}">{{ $o->nama_obat }} ({{ $o->tipe_obat }})</option>@endforeach
                        </select>
                        @error("rows.$i.obat_id")<div style="color:var(--red2);font-size:.6rem;">{{ $message }}</div>@enderror
                    </td>
                    <td style="padding:.3rem .4rem;"><input type="number" min="1" wire:model.live.debounce.400ms="rows.{{ $i }}.jumlah_box" style="width:56px;padding:.35rem;border-radius:.45rem;background:var(--card);border:1px solid var(--line2);color:var(--ink);font-size:.74rem;text-align:right;"></td>
                    <td style="padding:.3rem .4rem;"><input type="number" min="1" wire:model.live.debounce.400ms="rows.{{ $i }}.isi_per_box" style="width:56px;padding:.35rem;border-radius:.45rem;background:var(--card);border:1px solid var(--line2);color:var(--ink);font-size:.74rem;text-align:right;"></td>
                    <td style="padding:.3rem .4rem;"><input type="number" min="0" step="1" wire:model.live.debounce.400ms="rows.{{ $i }}.harga_per_box" style="width:92px;padding:.35rem;border-radius:.45rem;background:var(--card);border:1px solid var(--line2);color:var(--ink);font-size:.74rem;text-align:right;"></td>
                    <td class="font-mono" style="padding:.3rem .4rem;text-align:right;color:{{ ($row['tipe_obat']??'')==='kronis' ? 'var(--blue)' : 'var(--mut2)' }};">{{ ($row['tipe_obat']??'')==='kronis' ? $rp($row['klaim_bpjs_per_unit']??0) : '—' }}</td>
                    <td class="font-mono" style="padding:.3rem .4rem;text-align:right;color:var(--red2);">{{ $rp($row['subtotal_beli']??0) }}</td>
                    <td class="font-mono" style="padding:.3rem .4rem;text-align:right;color:var(--blue);">{{ $rp($row['estimasi_klaim']??0) }}</td>
                    <td style="padding:.3rem .4rem;text-align:right;">
                        <button wire:click="removeRow({{ $i }})" style="background:none;border:none;color:var(--red2);cursor:pointer;font-size:.9rem;">✕</button>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        </div>

        {{-- Totals --}}
        @php $ft = $this->formTotal; @endphp
        <div style="display:flex;flex-wrap:wrap;gap:.6rem;justify-content:flex-end;margin-top:1rem;padding-top:.9rem;border-top:1px solid var(--line);">
            <div style="text-align:right;"><div style="font-size:.58rem;color:var(--mut);text-transform:uppercase;">Total Beli (HPP)</div><div class="font-mono" style="font-size:.95rem;font-weight:800;color:var(--red2);">{{ $rp($ft['beli']) }}</div></div>
            <div style="text-align:right;"><div style="font-size:.58rem;color:var(--mut);text-transform:uppercase;">Est. Klaim/Jual</div><div class="font-mono" style="font-size:.95rem;font-weight:800;color:var(--blue);">{{ $rp($ft['klaim']) }}</div></div>
            <div style="text-align:right;padding:.35rem .9rem;border-radius:.7rem;background:{{ $ft['laba']>=0?'rgba(63,207,142,.1)':'rgba(232,100,90,.1)' }};border:1px solid {{ $ft['laba']>=0?'rgba(63,207,142,.3)':'rgba(232,100,90,.3)' }};">
                <div style="font-size:.58rem;color:{{ $ft['laba']>=0?'var(--emer2)':'var(--red2)' }};text-transform:uppercase;">Est. Laba · {{ number_format(abs($ft['margin']),1) }}%</div>
                <div class="font-mono" style="font-size:1rem;font-weight:900;color:{{ $ft['laba']>=0?'var(--emer2)':'var(--red2)' }};">{{ ($ft['laba']>=0?'+':'−').$rp(abs($ft['laba'])) }}</div>
            </div>
        </div>

        {{-- Actions --}}
        <div style="display:flex;gap:.6rem;justify-content:flex-end;margin-top:1.1rem;">
            <button wire:click="cancel" style="padding:.6rem 1.1rem;border-radius:.6rem;background:transparent;border:1px solid var(--line2);color:var(--mut);cursor:pointer;font-size:.8rem;">Batal</button>
            <button wire:click="simpan(false)" style="padding:.6rem 1.1rem;border-radius:.6rem;background:rgba(255,255,255,.05);border:1px solid var(--line3);color:var(--ink);cursor:pointer;font-size:.8rem;font-weight:700;">Simpan Draft</button>
            <button wire:click="ajukanLangsung" style="padding:.6rem 1.3rem;border-radius:.6rem;background:linear-gradient(180deg,rgba(91,155,213,.95),rgba(91,155,213,.78));border:1px solid rgba(91,155,213,.5);color:#04121f;cursor:pointer;font-size:.8rem;font-weight:800;">Ajukan untuk Persetujuan →</button>
        </div>
    </div>
    @endunless

    {{-- ══════════ DETAIL DRAWER ══════════ --}}
    @if($this->detail)
    @php $d = $this->detail; @endphp
    <div style="position:fixed;inset:0;z-index:300;" wire:key="drawer-{{ $d->id }}">
        <div wire:click="closeDetail" style="position:absolute;inset:0;background:rgba(0,0,0,.55);backdrop-filter:blur(2px);"></div>
        <div style="position:absolute;top:0;right:0;height:100%;width:min(560px,94vw);background:var(--panel);border-left:1px solid var(--line2);box-shadow:-16px 0 48px rgba(0,0,0,.5);overflow-y:auto;padding:1.3rem;">
            <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:.5rem;">
                <div>
                    <div class="font-mono" style="font-size:1.05rem;font-weight:800;color:var(--gold2);">{{ $d->no_pengajuan }}</div>
                    <div style="font-size:.7rem;color:var(--mut);">{{ $d->tanggal->translatedFormat('l, d M Y') }} · {{ $d->pemohon_nama ?? '—' }}</div>
                </div>
                <button wire:click="closeDetail" style="background:none;border:none;color:var(--mut);cursor:pointer;font-size:1.3rem;">✕</button>
            </div>

            <div style="display:flex;gap:.5rem;flex-wrap:wrap;margin:.8rem 0;">
                <span style="font-size:.66rem;font-weight:700;padding:.2rem .7rem;border-radius:999px;color:{{ $d->statusColor() }};background:color-mix(in srgb,{{ $d->statusColor() }} 15%,transparent);border:1px solid {{ $d->statusColor() }};">{{ $d->statusLabel() }}</span>
                @php [$plbl,$pcol,$pbg,$pbd] = $prioBadge($d->prioritas); @endphp
                <span style="font-size:.66rem;font-weight:700;padding:.2rem .7rem;border-radius:999px;color:{{ $pcol }};background:{{ $pbg }};border:1px solid {{ $pbd }};">{{ $plbl }}</span>
                @if($d->distributor)<span style="font-size:.66rem;padding:.2rem .7rem;border-radius:999px;color:var(--mut);border:1px solid var(--line2);">{{ $d->distributor->name }}</span>@endif
            </div>

            @if($d->justifikasi)
            <div style="background:var(--card);border:1px solid var(--line);border-radius:.6rem;padding:.7rem .9rem;margin-bottom:.9rem;">
                <div style="font-size:.6rem;color:var(--mut);text-transform:uppercase;margin-bottom:.2rem;">Justifikasi</div>
                <div style="font-size:.8rem;color:var(--ink);">{{ $d->justifikasi }}</div>
            </div>
            @endif

            {{-- Items --}}
            <table style="width:100%;border-collapse:collapse;font-size:.72rem;margin-bottom:.9rem;">
                <thead><tr style="color:var(--mut);"><th style="text-align:left;padding:.3rem;font-size:.56rem;text-transform:uppercase;">Obat</th><th style="text-align:right;padding:.3rem;font-size:.56rem;">Qty</th><th style="text-align:right;padding:.3rem;font-size:.56rem;">Beli</th><th style="text-align:right;padding:.3rem;font-size:.56rem;">Est.Klaim</th></tr></thead>
                <tbody>
                    @foreach($d->items as $it)
                    <tr style="border-top:1px solid rgba(31,61,48,.4);">
                        <td style="padding:.35rem .3rem;color:var(--ink);">{{ $it->nama_obat }} <span style="color:var(--mut2);font-size:.6rem;">{{ $it->tipe_obat }}</span></td>
                        <td class="font-mono" style="padding:.35rem .3rem;text-align:right;color:var(--mut);">{{ (int)($it->jumlah_box*$it->isi_per_box) }}</td>
                        <td class="font-mono" style="padding:.35rem .3rem;text-align:right;color:var(--red2);">{{ $rp($it->subtotal_beli) }}</td>
                        <td class="font-mono" style="padding:.35rem .3rem;text-align:right;color:var(--blue);">{{ $rp($it->estimasi_klaim) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

            <div style="display:flex;justify-content:space-between;font-size:.8rem;padding:.6rem .2rem;border-top:1px solid var(--line);">
                <span style="color:var(--mut);">Total Beli <strong class="font-mono" style="color:var(--red2);">{{ $rp($d->total_beli) }}</strong></span>
                <span style="color:var(--mut);">Est. Laba <strong class="font-mono" style="color:{{ $d->estimasi_laba>=0?'var(--emer2)':'var(--red2)' }};">{{ ($d->estimasi_laba>=0?'+':'−').$rp(abs($d->estimasi_laba)) }}</strong></span>
            </div>

            {{-- Jejak approval --}}
            @if($d->approved_at || $d->status==='diajukan')
            <div style="background:var(--card);border:1px solid var(--line);border-radius:.6rem;padding:.7rem .9rem;margin-top:.9rem;font-size:.72rem;">
                <div style="font-size:.6rem;color:var(--mut);text-transform:uppercase;margin-bottom:.3rem;">Jejak Persetujuan</div>
                @if($d->submitted_at)<div style="color:var(--mut);">Diajukan {{ $d->submitted_at->translatedFormat('d M Y H:i') }}</div>@endif
                @if($d->approved_at)<div style="color:var(--ink);margin-top:.2rem;">{{ $d->statusLabel() }} oleh <strong>{{ $d->approver_nama ?? '—' }}</strong> ({{ $d->approver_sumber }}) · {{ $d->approved_at->translatedFormat('d M Y H:i') }}</div>@endif
                @if($d->catatan_approver)<div style="color:var(--mut);margin-top:.2rem;">Catatan: {{ $d->catatan_approver }}</div>@endif
                @if($d->alasan_tolak)<div style="color:var(--red2);margin-top:.2rem;">Alasan tolak: {{ $d->alasan_tolak }}</div>@endif
                @if($d->purchaseOrder)<div style="color:var(--emer2);margin-top:.2rem;">✓ Direalisasi → PO #{{ $d->purchase_order_id }}</div>@endif
            </div>
            @endif

            {{-- Aksi drawer --}}
            <div style="display:flex;gap:.5rem;flex-wrap:wrap;margin-top:1rem;">
                @if($d->bisaDiajukan())
                    <button wire:click="openEdit({{ $d->id }})" style="flex:1;padding:.55rem;border-radius:.55rem;background:rgba(255,255,255,.05);border:1px solid var(--line3);color:var(--ink);cursor:pointer;font-size:.76rem;">Edit</button>
                    <button wire:click="ajukan({{ $d->id }})" style="flex:1;padding:.55rem;border-radius:.55rem;background:rgba(91,155,213,.14);border:1px solid rgba(91,155,213,.4);color:#5b9bd5;cursor:pointer;font-size:.76rem;font-weight:700;">Ajukan →</button>
                @endif
                @if($d->bisaApprove() && $isMgr)
                    <button wire:click="openApprove({{ $d->id }})" style="flex:1;padding:.55rem;border-radius:.55rem;background:rgba(63,207,142,.14);border:1px solid rgba(63,207,142,.4);color:var(--emer2);cursor:pointer;font-size:.76rem;font-weight:700;">✓ Setujui</button>
                    <button wire:click="mintaRevisi({{ $d->id }})" style="padding:.55rem .8rem;border-radius:.55rem;background:rgba(217,164,65,.1);border:1px solid rgba(217,164,65,.3);color:var(--gold2);cursor:pointer;font-size:.76rem;">Revisi</button>
                    <button wire:click="openTolak({{ $d->id }})" style="padding:.55rem .8rem;border-radius:.55rem;background:rgba(232,100,90,.1);border:1px solid rgba(232,100,90,.3);color:var(--red2);cursor:pointer;font-size:.76rem;">Tolak</button>
                @endif
                @if($d->bisaRealisasi())
                    <button wire:click="realisasi({{ $d->id }})" wire:confirm="Realisasikan {{ $d->no_pengajuan }} menjadi PO?" style="flex:1;padding:.55rem;border-radius:.55rem;background:linear-gradient(180deg,rgba(63,207,142,.9),rgba(63,207,142,.7));border:1px solid rgba(63,207,142,.5);color:#04150d;cursor:pointer;font-size:.76rem;font-weight:800;">🛒 Belanja → Buat PO</button>
                @endif
                @if($d->bisaDihapus())
                    <button wire:click="hapus({{ $d->id }})" wire:confirm="Hapus {{ $d->no_pengajuan }}?" style="padding:.55rem .8rem;border-radius:.55rem;background:transparent;border:1px solid rgba(232,100,90,.3);color:var(--red2);cursor:pointer;font-size:.76rem;">Hapus</button>
                @endif
            </div>
        </div>
    </div>
    @endif

    {{-- ══ MODAL SETUJUI ══ --}}
    @if($showApprove)
    <div style="position:fixed;inset:0;z-index:400;display:flex;align-items:center;justify-content:center;padding:1rem;">
        <div wire:click="$set('showApprove',false)" style="position:absolute;inset:0;background:rgba(0,0,0,.55);"></div>
        <div class="glass-card" style="position:relative;width:min(420px,94vw);padding:1.3rem;">
            <h3 class="font-heading" style="font-size:1rem;color:var(--ink);margin:0 0 .3rem;">Setujui Pengajuan</h3>
            <p style="font-size:.76rem;color:var(--mut);margin:0 0 .8rem;">Setelah disetujui, pengajuan siap direalisasikan menjadi Purchase Order.</p>
            <label style="font-size:.64rem;color:var(--mut);text-transform:uppercase;">Catatan (opsional)</label>
            <textarea wire:model="catatanApprover" rows="2" style="width:100%;margin-top:.3rem;padding:.5rem;border-radius:.5rem;background:var(--card);border:1px solid var(--line2);color:var(--ink);font-size:.78rem;"></textarea>
            <div style="display:flex;gap:.5rem;justify-content:flex-end;margin-top:1rem;">
                <button wire:click="$set('showApprove',false)" style="padding:.5rem 1rem;border-radius:.5rem;background:transparent;border:1px solid var(--line2);color:var(--mut);cursor:pointer;font-size:.78rem;">Batal</button>
                <button wire:click="setujui" style="padding:.5rem 1.2rem;border-radius:.5rem;background:linear-gradient(180deg,rgba(63,207,142,.9),rgba(63,207,142,.75));border:1px solid rgba(63,207,142,.5);color:#04150d;cursor:pointer;font-size:.78rem;font-weight:800;">✓ Setujui</button>
            </div>
        </div>
    </div>
    @endif

    {{-- ══ MODAL TOLAK ══ --}}
    @if($showTolak)
    <div style="position:fixed;inset:0;z-index:400;display:flex;align-items:center;justify-content:center;padding:1rem;">
        <div wire:click="$set('showTolak',false)" style="position:absolute;inset:0;background:rgba(0,0,0,.55);"></div>
        <div class="glass-card" style="position:relative;width:min(420px,94vw);padding:1.3rem;">
            <h3 class="font-heading" style="font-size:1rem;color:var(--ink);margin:0 0 .3rem;">Tolak Pengajuan</h3>
            <label style="font-size:.64rem;color:var(--mut);text-transform:uppercase;">Alasan penolakan *</label>
            <textarea wire:model="alasanTolak" rows="2" style="width:100%;margin-top:.3rem;padding:.5rem;border-radius:.5rem;background:var(--card);border:1px solid var(--line2);color:var(--ink);font-size:.78rem;"></textarea>
            @error('alasanTolak')<div style="color:var(--red2);font-size:.68rem;margin-top:.2rem;">{{ $message }}</div>@enderror
            <div style="display:flex;gap:.5rem;justify-content:flex-end;margin-top:1rem;">
                <button wire:click="$set('showTolak',false)" style="padding:.5rem 1rem;border-radius:.5rem;background:transparent;border:1px solid var(--line2);color:var(--mut);cursor:pointer;font-size:.78rem;">Batal</button>
                <button wire:click="tolak" style="padding:.5rem 1.2rem;border-radius:.5rem;background:rgba(232,100,90,.15);border:1px solid rgba(232,100,90,.4);color:var(--red2);cursor:pointer;font-size:.78rem;font-weight:800;">Tolak</button>
            </div>
        </div>
    </div>
    @endif
</div>
