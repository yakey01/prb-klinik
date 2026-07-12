<div>
    <style>
        tr.pr-row{transition:background .12s ease;cursor:pointer;}
        tr.pr-row:hover{background:rgba(63,207,142,.07)!important;}
        .pr-menu-item{display:block;width:100%;text-align:left;background:none;border:none;color:var(--ink);cursor:pointer;font-size:.76rem;padding:.5rem .6rem;border-radius:.4rem;transition:background .1s;}
        .pr-menu-item:hover{background:rgba(255,255,255,.06);}
    </style>
    @php
        $rp = fn ($n) => 'Rp ' . number_format((float) $n, 0, ',', '.');
        $k  = $this->kpi;
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
        <div wire:click="$set('filterStatus','disetujui')" class="glass-card" style="padding:.9rem 1.1rem;cursor:pointer;{{ $k['disetujui']>0 ? 'border-color:rgba(217,164,65,.5);box-shadow:0 0 16px rgba(217,164,65,.16);' : 'border-color:rgba(63,207,142,.25);' }}" title="Klik untuk saring — lalu 🛒 Belanja (PO)">
            <div style="font-size:.6rem;color:var(--mut);text-transform:uppercase;letter-spacing:.06em;">Disetujui — siap belanja</div>
            <div class="font-mono" style="font-size:1.35rem;font-weight:800;color:{{ $k['disetujui']>0 ? 'var(--gold2)' : 'var(--emer2)' }};">{{ $k['disetujui'] }}</div>
            @if($k['disetujui']>0)<div style="font-size:.6rem;color:var(--gold2);margin-top:.1rem;">🛒 klik → belanjakan jadi PO</div>@endif
        </div>
        <div class="glass-card" style="padding:.9rem 1.1rem;">
            <div style="font-size:.6rem;color:var(--mut);text-transform:uppercase;letter-spacing:.06em;">Draft</div>
            <div class="font-mono" style="font-size:1.35rem;font-weight:800;color:var(--mut);">{{ $k['draft'] }}</div>
        </div>
        <div class="glass-card" style="padding:.9rem 1.1rem;display:flex;align-items:center;gap:.55rem;">
            <svg width="18" height="18" fill="none" stroke="#5b9bd5" stroke-width="2" viewBox="0 0 24 24" style="flex-shrink:0;"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><path d="M9 15l2 2 4-4"/></svg>
            <div style="font-size:.62rem;color:var(--mut);line-height:1.4;">Persetujuan dilakukan <strong style="color:#5b9bd5;">manajer di SIM</strong>. Apotek: ajukan → tunggu → realisasi PO.</div>
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
                <tr wire:key="pr-{{ $p->id }}" wire:click="openDetail({{ $p->id }})" class="pr-row" title="Klik untuk lihat detail obat" style="border-top:1px solid var(--line);{{ $p->status==='disetujui' ? 'background:linear-gradient(90deg,rgba(217,164,65,.09),transparent);box-shadow:inset 3px 0 0 var(--gold);' : '' }}">
                    <td style="padding:.6rem .9rem;">
                        <span class="font-mono" style="color:var(--gold2);font-weight:700;font-size:.8rem;">{{ $p->no_pengajuan }}</span>
                        <div style="font-size:.64rem;color:var(--mut2);">{{ $p->tanggal->translatedFormat('d M Y') }} · {{ $p->pemohon_nama ?? '—' }}</div>
                    </td>
                    <td style="padding:.6rem .5rem;"><span style="font-size:.62rem;font-weight:700;padding:.12rem .5rem;border-radius:999px;color:{{ $pcol }};background:{{ $pbg }};border:1px solid {{ $pbd }};">{{ $plbl }}</span></td>
                    <td class="font-mono" style="padding:.6rem .5rem;text-align:right;color:var(--mut);">{{ $p->items_count ?? $p->items()->count() }}</td>
                    <td class="font-mono" style="padding:.6rem .5rem;text-align:right;color:var(--red2);">{{ $rp($p->total_beli) }}</td>
                    <td class="font-mono" style="padding:.6rem .5rem;text-align:right;color:{{ $p->estimasi_laba>=0?'var(--emer2)':'var(--red2)' }};">{{ ($p->estimasi_laba>=0?'+':'−').$rp(abs($p->estimasi_laba)) }}</td>
                    <td style="padding:.6rem .5rem;text-align:center;">
                        <span style="font-size:.64rem;font-weight:700;padding:.16rem .6rem;border-radius:999px;color:{{ $p->statusColor() }};background:color-mix(in srgb,{{ $p->statusColor() }} 14%, transparent);border:1px solid {{ $p->statusColor() }};white-space:nowrap;">{{ $p->statusLabel() }}</span>
                    </td>
                    <td style="padding:.6rem .9rem;text-align:right;white-space:nowrap;" wire:click.stop>
                        @if($p->bisaDiedit())
                            <button wire:click="openEdit({{ $p->id }})" title="{{ $p->editButuhReApprove() ? 'Edit — persetujuan akan gugur, minta ACC ulang manajer' : 'Edit pengajuan' }}" style="font-size:.66rem;padding:.25rem .55rem;border-radius:.5rem;background:{{ $p->editButuhReApprove() ? 'rgba(217,164,65,.1)' : 'rgba(255,255,255,.05)' }};border:1px solid {{ $p->editButuhReApprove() ? 'rgba(217,164,65,.35)' : 'var(--line3)' }};color:{{ $p->editButuhReApprove() ? 'var(--gold2)' : 'var(--ink)' }};cursor:pointer;">✎ Edit{{ $p->editButuhReApprove() ? ' ⟳' : '' }}</button>
                        @endif
                        @if($p->bisaDiajukan())
                            <button wire:click="ajukan({{ $p->id }})" title="Ajukan" style="font-size:.66rem;padding:.25rem .6rem;border-radius:.5rem;background:rgba(91,155,213,.12);border:1px solid rgba(91,155,213,.35);color:#5b9bd5;cursor:pointer;font-weight:700;">Ajukan →</button>
                        @endif
                        @if($p->status === 'diajukan')
                            <span title="Menunggu keputusan manajer di SIM · masih bisa diedit" style="font-size:.64rem;color:#5b9bd5;padding:.25rem .5rem;">⏳ di manajer SIM</span>
                        @endif
                        @if($p->bisaRealisasi())
                            <button wire:click="mintaRealisasi({{ $p->id }})" title="Input faktur pengadaan → buat PO" style="font-size:.66rem;padding:.25rem .6rem;border-radius:.5rem;background:linear-gradient(180deg,rgba(63,207,142,.9),rgba(63,207,142,.7));border:1px solid rgba(63,207,142,.5);color:#04150d;cursor:pointer;font-weight:800;">🛒 Belanja (PO)</button>
                        @endif
                        {{-- Kebab menu CRUD (x-teleport → lolos overflow tabel) --}}
                        <div x-data="{o:false,x:0,y:0}" @keydown.escape.window="o=false" style="display:inline-block;">
                            <button type="button" @click.stop="const r=$el.getBoundingClientRect(); x=Math.max(8,r.right-190); y=r.bottom+4; o=!o" title="Menu aksi" style="background:none;border:none;color:var(--mut);cursor:pointer;padding:.2rem .35rem;font-size:1rem;line-height:1;">⋯</button>
                            <template x-teleport="body">
                                <div x-show="o" x-transition.opacity.duration.100ms @click.outside="o=false" @click="o=false" :style="`position:fixed;top:${y}px;left:${x}px;z-index:9000;width:190px;background:var(--card,#152b21);border:1px solid var(--line2,#1f3d30);border-radius:.6rem;box-shadow:0 12px 32px rgba(0,0,0,.55);padding:.3rem;`" style="display:none;">
                                    <button type="button" wire:click="openDetail({{ $p->id }})" class="pr-menu-item">🔍 Lihat Detail</button>
                                    @if($p->bisaDiedit())
                                    <button type="button" wire:click="openEdit({{ $p->id }})" class="pr-menu-item">✎ Edit Pengajuan</button>
                                    @endif
                                    @if($p->bisaDiajukan())
                                    <button type="button" wire:click="ajukan({{ $p->id }})" class="pr-menu-item" style="color:#5b9bd5;">📤 Ajukan untuk Persetujuan</button>
                                    @endif
                                    @if($p->bisaRealisasi())
                                    <button type="button" wire:click="mintaRealisasi({{ $p->id }})" class="pr-menu-item" style="color:var(--emer2);">🧾 Input Faktur → Buat PO</button>
                                    @endif
                                    @if($p->bisaDibatalkan())
                                    <button type="button" wire:click="batalkan({{ $p->id }})" wire:confirm="Batalkan / tarik {{ $p->no_pengajuan }} dari antrean manajer SIM?" class="pr-menu-item" style="color:var(--gold2);">✕ Batalkan / Tarik</button>
                                    @endif
                                    @if($p->bisaDihapus())
                                    <button type="button" wire:click="hapus({{ $p->id }})" wire:confirm="Hapus permanen {{ $p->no_pengajuan }}? Tindakan ini tidak bisa dibatalkan." class="pr-menu-item" style="color:var(--red2);">🗑 Hapus</button>
                                    @endif
                                </div>
                            </template>
                        </div>
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
        <table style="width:100%;border-collapse:collapse;font-size:.75rem;min-width:960px;">
            <thead><tr style="color:var(--mut);">
                <th style="text-align:left;padding:.35rem .4rem;font-size:.58rem;text-transform:uppercase;">Tipe (BPJS?)</th>
                <th style="text-align:left;padding:.35rem .4rem;font-size:.58rem;text-transform:uppercase;">Obat</th>
                <th style="text-align:right;padding:.35rem .4rem;font-size:.58rem;text-transform:uppercase;">Box</th>
                <th style="text-align:right;padding:.35rem .4rem;font-size:.58rem;text-transform:uppercase;">Isi/Box</th>
                <th style="text-align:right;padding:.35rem .4rem;font-size:.58rem;text-transform:uppercase;">Harga Beli/Box</th>
                <th style="text-align:right;padding:.35rem .4rem;font-size:.58rem;text-transform:uppercase;" title="Klaim BPJS per unit — hanya obat kronis">Klaim BPJS/unit</th>
                <th style="text-align:right;padding:.35rem .4rem;font-size:.58rem;text-transform:uppercase;">Subtotal Beli</th>
                <th style="text-align:right;padding:.35rem .4rem;font-size:.58rem;text-transform:uppercase;" title="Estimasi klaim BPJS — hanya obat kronis">Est. Klaim BPJS</th>
                <th></th>
            </tr></thead>
            <tbody>
                @foreach($rows as $i => $row)
                <tr wire:key="row-{{ $i }}" style="border-top:1px solid rgba(31,61,48,.4);">
                    @php $tp = $row['tipe_obat'] ?? 'kronis'; $isK = $tp === 'kronis'; @endphp
                    {{-- FIELD TIPE: pilih dulu kategori (menentukan apakah diklaim BPJS) --}}
                    <td style="padding:.3rem .5rem;vertical-align:top;min-width:210px;">
                        <div style="display:inline-flex;border-radius:.85rem;overflow:hidden;border:1px solid rgba(255,255,255,.14);
                                    background:linear-gradient(180deg,rgba(255,255,255,.05),rgba(0,0,0,.08));
                                    box-shadow:inset 0 1.5px 0 rgba(255,255,255,.14), inset 0 0 0 1px rgba(255,255,255,.02), 0 8px 20px -8px rgba(0,0,0,.6);
                                    backdrop-filter:blur(10px);-webkit-backdrop-filter:blur(10px);">
                            <button type="button" wire:click="$set('rows.{{ $i }}.tipe_obat','kronis')" title="Obat kronis — diklaim ke BPJS"
                                style="padding:.62rem 1.35rem;font-size:.84rem;font-weight:800;letter-spacing:.01em;cursor:pointer;border:none;transition:all .15s;
                                    {{ $isK ? 'background:linear-gradient(180deg,rgba(132,187,245,.42),rgba(132,187,245,.2));color:#d5e8fb;box-shadow:inset 0 1.5px 0 rgba(255,255,255,.35),0 0 16px rgba(132,187,245,.3);text-shadow:0 1px 2px rgba(0,0,0,.35);' : 'background:transparent;color:var(--mut);' }}">Kronis</button>
                            <button type="button" wire:click="$set('rows.{{ $i }}.tipe_obat','non_kronis')" title="Obat umum — tidak diklaim BPJS"
                                style="padding:.62rem 1.2rem;font-size:.84rem;font-weight:800;letter-spacing:.01em;cursor:pointer;border:none;border-left:1px solid rgba(255,255,255,.1);transition:all .15s;
                                    {{ !$isK ? 'background:linear-gradient(180deg,rgba(242,193,78,.45),rgba(224,168,50,.22));color:#ffe8ac;box-shadow:inset 0 1.5px 0 rgba(255,255,255,.4),0 0 16px rgba(242,193,78,.32);text-shadow:0 1px 2px rgba(0,0,0,.35);' : 'background:transparent;color:var(--mut);' }}">Non-Kronis</button>
                        </div>
                        <div style="font-size:.66rem;color:{{ $isK ? '#8fbdf5' : '#f2c14e' }};margin-top:.4rem;font-weight:800;letter-spacing:.02em;">{{ $isK ? '✓ diklaim BPJS' : '✓ umum · non-BPJS' }}</div>
                    </td>
                    {{-- OBAT: difilter sesuai tipe terpilih --}}
                    <td style="padding:.3rem .4rem;min-width:220px;vertical-align:top;">
                        <select wire:model.live="rows.{{ $i }}.obat_id" style="width:100%;padding:.4rem .5rem;border-radius:.45rem;background:var(--card);border:1px solid var(--line2);color:var(--ink);font-size:.74rem;">
                            <option value="0">— pilih obat {{ $isK ? 'kronis' : 'non-kronis' }} —</option>
                            @foreach($this->obatList->where('tipe_obat', $tp) as $o)<option value="{{ $o->id }}">{{ $o->nama_obat }}</option>@endforeach
                        </select>
                        @error("rows.$i.obat_id")<div style="color:var(--red2);font-size:.6rem;">{{ $message }}</div>@enderror
                    </td>
                    <td style="padding:.3rem .4rem;"><input type="number" min="1" wire:model.live.debounce.400ms="rows.{{ $i }}.jumlah_box" style="width:56px;padding:.35rem;border-radius:.45rem;background:var(--card);border:1px solid var(--line2);color:var(--ink);font-size:.74rem;text-align:right;"></td>
                    <td style="padding:.3rem .4rem;"><input type="number" min="1" wire:model.live.debounce.400ms="rows.{{ $i }}.isi_per_box" style="width:56px;padding:.35rem;border-radius:.45rem;background:var(--card);border:1px solid var(--line2);color:var(--ink);font-size:.74rem;text-align:right;"></td>
                    <td style="padding:.3rem .4rem;"><input type="number" min="0" step="1" wire:model.live.debounce.400ms="rows.{{ $i }}.harga_per_box" style="width:92px;padding:.35rem;border-radius:.45rem;background:var(--card);border:1px solid var(--line2);color:var(--ink);font-size:.74rem;text-align:right;"></td>
                    <td class="font-mono" style="padding:.3rem .4rem;text-align:right;color:{{ $isK ? 'var(--blue)' : 'var(--mut2)' }};">{{ $isK ? $rp($row['klaim_bpjs_per_unit']??0) : '—' }}</td>
                    <td class="font-mono" style="padding:.3rem .4rem;text-align:right;color:var(--red2);">{{ $rp($row['subtotal_beli']??0) }}</td>
                    <td class="font-mono" style="padding:.3rem .4rem;text-align:right;color:{{ $isK ? 'var(--blue)' : 'var(--mut2)' }};" title="{{ $isK ? 'Estimasi klaim BPJS' : 'Non-kronis: dijual umum, tidak diklaim BPJS' }}">{{ $isK ? $rp($row['estimasi_klaim']??0) : '—' }}</td>
                    <td style="padding:.3rem .4rem;text-align:right;">
                        <button wire:click="removeRow({{ $i }})" style="background:none;border:none;color:var(--red2);cursor:pointer;font-size:.9rem;">✕</button>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        </div>

        {{-- Totals — dipisah: BPJS (kronis) vs Umum (non-kronis) --}}
        @php $ft = $this->formTotal; @endphp
        <div style="display:flex;flex-wrap:wrap;gap:.6rem;justify-content:flex-end;margin-top:1rem;padding-top:.9rem;border-top:1px solid var(--line);align-items:stretch;">
            <div style="text-align:right;">
                <div style="font-size:.58rem;color:var(--mut);text-transform:uppercase;">Total Beli (HPP)</div>
                <div class="font-mono" style="font-size:.95rem;font-weight:800;color:var(--red2);">{{ $rp($ft['beli']) }}</div>
                @if($ft['ada_umum'] && $ft['ada_kronis'])
                <div style="font-size:.54rem;color:var(--mut2);">kronis {{ $rp($ft['beli_kronis']) }} · umum {{ $rp($ft['beli_umum']) }}</div>
                @endif
            </div>
            @if($ft['ada_kronis'])
            <div style="text-align:right;">
                <div style="font-size:.58rem;color:var(--mut);text-transform:uppercase;">Est. Klaim BPJS</div>
                <div class="font-mono" style="font-size:.95rem;font-weight:800;color:var(--blue);">{{ $rp($ft['klaim']) }}</div>
                <div style="font-size:.54rem;color:var(--mut2);">kronis saja</div>
            </div>
            <div style="text-align:right;padding:.35rem .9rem;border-radius:.7rem;background:{{ $ft['laba']>=0?'rgba(63,207,142,.1)':'rgba(232,100,90,.1)' }};border:1px solid {{ $ft['laba']>=0?'rgba(63,207,142,.3)':'rgba(232,100,90,.3)' }};">
                <div style="font-size:.58rem;color:{{ $ft['laba']>=0?'var(--emer2)':'var(--red2)' }};text-transform:uppercase;">Est. Laba BPJS · {{ number_format(abs($ft['margin']),1) }}%</div>
                <div class="font-mono" style="font-size:1rem;font-weight:900;color:{{ $ft['laba']>=0?'var(--emer2)':'var(--red2)' }};">{{ ($ft['laba']>=0?'+':'−').$rp(abs($ft['laba'])) }}</div>
                <div style="font-size:.52rem;color:var(--mut2);">klaim − beli kronis</div>
            </div>
            @endif
            @if($ft['ada_umum'])
            <div style="text-align:right;padding:.35rem .9rem;border-radius:.7rem;background:rgba(217,164,65,.08);border:1px solid rgba(217,164,65,.25);">
                <div style="font-size:.58rem;color:var(--gold2);text-transform:uppercase;">Beli Umum (non-BPJS)</div>
                <div class="font-mono" style="font-size:1rem;font-weight:900;color:var(--gold2);">{{ $rp($ft['beli_umum']) }}</div>
                <div style="font-size:.52rem;color:var(--mut2);">dijual umum · di luar klaim BPJS</div>
            </div>
            @endif
        </div>

        {{-- Peringatan re-approve (edit pengajuan yang sudah disetujui) --}}
        @if($editStatus === 'disetujui')
        <div style="margin-top:1rem;padding:.7rem .9rem;border-radius:.7rem;background:rgba(217,164,65,.1);border:1px solid rgba(217,164,65,.4);font-size:.74rem;color:var(--gold2);line-height:1.5;">
            ⚠ Pengajuan ini <strong>SUDAH DISETUJUI</strong> manajer. Menyimpan perubahan akan <strong>menggugurkan persetujuan</strong> dan mengembalikannya untuk <strong>PERSETUJUAN ULANG</strong> manajer SIM.
        </div>
        @endif

        {{-- Actions --}}
        <div style="display:flex;gap:.6rem;justify-content:flex-end;margin-top:1.1rem;align-items:center;">
            @if($editStatus === 'diajukan')
            <span style="font-size:.66rem;color:#5b9bd5;margin-right:auto;">✎ Mengedit pengajuan yang sudah diajukan — perubahan langsung terlihat manajer SIM.</span>
            @endif
            <button wire:click="cancel" style="padding:.6rem 1.1rem;border-radius:.6rem;background:transparent;border:1px solid var(--line2);color:var(--mut);cursor:pointer;font-size:.8rem;">Batal</button>
            @if($editStatus === 'disetujui')
            <button wire:click="simpan(false)" wire:confirm="Simpan perubahan? Persetujuan lama akan gugur dan pengajuan dikembalikan untuk ACC ulang manajer." style="padding:.6rem 1.3rem;border-radius:.6rem;background:linear-gradient(180deg,rgba(217,164,65,.95),rgba(217,164,65,.78));border:1px solid rgba(217,164,65,.5);color:#1a0e00;cursor:pointer;font-size:.8rem;font-weight:800;">💾 Simpan &amp; Ajukan Ulang →</button>
            @elseif($editStatus === 'diajukan')
            <button wire:click="simpan(false)" style="padding:.6rem 1.3rem;border-radius:.6rem;background:linear-gradient(180deg,rgba(91,155,213,.95),rgba(91,155,213,.78));border:1px solid rgba(91,155,213,.5);color:#04121f;cursor:pointer;font-size:.8rem;font-weight:800;">Simpan Perubahan</button>
            @else
            <button wire:click="simpan(false)" style="padding:.6rem 1.1rem;border-radius:.6rem;background:rgba(255,255,255,.05);border:1px solid var(--line3);color:var(--ink);cursor:pointer;font-size:.8rem;font-weight:700;">Simpan Draft</button>
            <button wire:click="ajukanLangsung" style="padding:.6rem 1.3rem;border-radius:.6rem;background:linear-gradient(180deg,rgba(91,155,213,.95),rgba(91,155,213,.78));border:1px solid rgba(91,155,213,.5);color:#04121f;cursor:pointer;font-size:.8rem;font-weight:800;">Ajukan untuk Persetujuan →</button>
            @endif
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
                @if($d->bisaDiedit())
                    <button wire:click="openEdit({{ $d->id }})" style="flex:1;padding:.55rem;border-radius:.55rem;background:rgba(255,255,255,.05);border:1px solid var(--line3);color:var(--ink);cursor:pointer;font-size:.76rem;">✎ Edit</button>
                @endif
                @if($d->bisaDiajukan())
                    <button wire:click="ajukan({{ $d->id }})" style="flex:1;padding:.55rem;border-radius:.55rem;background:rgba(91,155,213,.14);border:1px solid rgba(91,155,213,.4);color:#5b9bd5;cursor:pointer;font-size:.76rem;font-weight:700;">Ajukan →</button>
                @endif
                @if($d->status === 'diajukan')
                    <div style="flex:1;padding:.55rem;border-radius:.55rem;background:rgba(91,155,213,.1);border:1px solid rgba(91,155,213,.3);color:#5b9bd5;font-size:.72rem;text-align:center;">⏳ Menunggu <strong>manajer SIM</strong> · masih bisa diedit</div>
                @endif
                @if($d->bisaRealisasi())
                    <button wire:click="mintaRealisasi({{ $d->id }})" style="flex:1;padding:.55rem;border-radius:.55rem;background:linear-gradient(180deg,rgba(63,207,142,.9),rgba(63,207,142,.7));border:1px solid rgba(63,207,142,.5);color:#04150d;cursor:pointer;font-size:.76rem;font-weight:800;">🛒 Belanja → Buat PO</button>
                @endif
                @if($d->bisaDibatalkan())
                    <button wire:click="batalkan({{ $d->id }})" wire:confirm="Batalkan / tarik {{ $d->no_pengajuan }} dari antrean manajer SIM?" style="padding:.55rem .8rem;border-radius:.55rem;background:rgba(217,164,65,.12);border:1px solid rgba(217,164,65,.35);color:var(--gold2);cursor:pointer;font-size:.76rem;">✕ Batalkan</button>
                @endif
                @if($d->bisaDihapus())
                    <button wire:click="hapus({{ $d->id }})" wire:confirm="Hapus permanen {{ $d->no_pengajuan }}? Tidak bisa dibatalkan." style="padding:.55rem .8rem;border-radius:.55rem;background:transparent;border:1px solid rgba(232,100,90,.3);color:var(--red2);cursor:pointer;font-size:.76rem;">🗑 Hapus</button>
                @endif
            </div>
        </div>
    </div>
    @endif

    {{-- ══ MODAL INPUT FAKTUR PENGADAAN (realisasi → PO, tarik data pengajuan disetujui) ══ --}}
    @if($showFaktur && $this->fakturPr)
    @php $fp = $this->fakturPr; @endphp
    <div style="position:fixed;inset:0;z-index:400;display:flex;align-items:center;justify-content:center;padding:1.5rem;">
        <div wire:click="tutupFaktur" style="position:absolute;inset:0;background:rgba(3,8,6,.82);backdrop-filter:blur(5px);"></div>
        <div class="glass-card" style="position:relative;width:100%;max-width:520px;padding:1.5rem;border:1px solid rgba(63,207,142,.4);box-shadow:0 30px 80px rgba(0,0,0,.7);">
            <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:1rem;">
                <div>
                    <div class="font-heading" style="font-size:1.05rem;color:var(--emer2);">🧾 Input Faktur Pengadaan</div>
                    <div style="font-size:.72rem;color:var(--mut);margin-top:.2rem;line-height:1.5;">Data ditarik dari pengajuan yang <strong style="color:var(--emer2);">sudah disetujui</strong> manajer. Isi faktur/invoice supplier untuk membuat PO.</div>
                </div>
                <button wire:click="tutupFaktur" style="background:none;border:none;color:var(--mut);cursor:pointer;font-size:1.2rem;">✕</button>
            </div>
            {{-- Ringkasan pengajuan (read-only, ditarik) --}}
            <div style="background:rgba(255,255,255,.04);border:1px solid var(--line);border-radius:.6rem;padding:.75rem .9rem;margin-bottom:1rem;font-size:.78rem;">
                <div style="display:flex;justify-content:space-between;margin-bottom:.28rem;"><span style="color:var(--mut);">No. Pengajuan</span><span class="font-mono" style="color:var(--gold2);font-weight:700;">{{ $fp->no_pengajuan }}</span></div>
                <div style="display:flex;justify-content:space-between;margin-bottom:.28rem;"><span style="color:var(--mut);">Distributor</span><span>{{ $fp->distributor->name ?? '—' }}</span></div>
                <div style="display:flex;justify-content:space-between;margin-bottom:.28rem;"><span style="color:var(--mut);">Item</span><span>{{ $fp->items->count() }} obat</span></div>
                <div style="display:flex;justify-content:space-between;border-top:1px solid var(--line);padding-top:.28rem;margin-top:.28rem;"><span style="color:var(--mut);">Total Beli (HPP)</span><span class="font-mono" style="font-weight:800;color:var(--red2);">{{ $rp($fp->items->sum('subtotal_beli')) }}</span></div>
            </div>
            {{-- Input faktur --}}
            <div style="display:grid;grid-template-columns:1fr 155px;gap:.7rem;margin-bottom:.9rem;">
                <div>
                    <label style="font-size:.62rem;color:var(--mut);text-transform:uppercase;letter-spacing:.04em;">Nomor Faktur / Invoice <span style="color:var(--red2);">*</span></label>
                    <input wire:model="nomorFaktur" type="text" placeholder="mis. INV-2026-0123" style="width:100%;margin-top:.28rem;padding:.55rem .7rem;border-radius:.55rem;background:var(--card);border:1px solid var(--line2);color:var(--ink);font-size:.82rem;">
                    @error('nomorFaktur')<div style="color:var(--red2);font-size:.66rem;margin-top:.2rem;">{{ $message }}</div>@enderror
                </div>
                <div>
                    <label style="font-size:.62rem;color:var(--mut);text-transform:uppercase;letter-spacing:.04em;">Tanggal Faktur <span style="color:var(--red2);">*</span></label>
                    <input wire:model="tanggalFaktur" type="date" style="width:100%;margin-top:.28rem;padding:.55rem .5rem;border-radius:.55rem;background:var(--card);border:1px solid var(--line2);color:var(--ink);font-size:.82rem;">
                    @error('tanggalFaktur')<div style="color:var(--red2);font-size:.66rem;margin-top:.2rem;">{{ $message }}</div>@enderror
                </div>
            </div>
            <div style="font-size:.66rem;color:var(--mut2);margin-bottom:1rem;line-height:1.5;">⚠ Membuat PO akan <strong>menambah stok</strong> &amp; membuat <strong>tagihan otomatis</strong>. Setelah ini pengajuan terkunci (tak bisa diedit).</div>
            <div style="display:flex;gap:.6rem;justify-content:flex-end;">
                <button wire:click="tutupFaktur" style="padding:.6rem 1.1rem;border-radius:.6rem;background:transparent;border:1px solid var(--line2);color:var(--mut);cursor:pointer;font-size:.8rem;">Batal</button>
                <button wire:click="konfirmRealisasi" style="padding:.6rem 1.3rem;border-radius:.6rem;background:linear-gradient(180deg,rgba(63,207,142,.9),rgba(63,207,142,.75));border:1px solid rgba(63,207,142,.5);color:#04150d;cursor:pointer;font-size:.8rem;font-weight:800;">🛒 Buat PO dari Faktur →</button>
            </div>
        </div>
    </div>
    @endif
</div>
