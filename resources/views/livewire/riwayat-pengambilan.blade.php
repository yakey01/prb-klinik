<div>
    {{-- HEADER --}}
    <div style="margin-bottom:1.5rem;">
        <div class="font-label" style="font-size:.7rem;color:var(--mut);margin-bottom:.25rem;">Pasien</div>
        <h2 class="font-heading" style="font-size:1.5rem;color:var(--ink);margin:0;">Riwayat Pengambilan Obat</h2>
        <p style="color:var(--mut);font-size:.78rem;margin-top:.3rem;">Histori penyerahan obat per pasien — detail per-obat: harga beli, klaim BPJS, dan untung/rugi.</p>
    </div>

    {{-- KPI GLOBAL --}}
    @php $t = $this->totals; $lc = $t['laba']>0?'var(--emer2)':($t['laba']<0?'var(--red2)':'var(--gold2)'); @endphp
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(150px,1fr));gap:.7rem;margin-bottom:1.25rem;">
        @foreach([
            ['Kunjungan Selesai', $t['kunjungan'].' · '.$t['item'].' item', 'var(--blue)'],
            ['Total HPP (beli)', 'Rp '.number_format($t['biaya'],0,',','.'), 'var(--red2)'],
            ['Total Klaim BPJS', 'Rp '.number_format($t['klaim'],0,',','.'), 'var(--blue)'],
            ['Laba / Rugi', ($t['laba']>=0?'+':'−').'Rp '.number_format(abs($t['laba']),0,',','.').' · '.number_format(abs($t['margin']),1).'%', $lc],
        ] as [$lbl,$val,$col])
        <div class="glass-card" style="padding:.85rem 1rem;">
            <div style="font-size:.6rem;font-weight:700;letter-spacing:.06em;text-transform:uppercase;color:var(--mut);margin-bottom:.35rem;">{{ $lbl }}</div>
            <div class="font-mono" style="font-size:.92rem;font-weight:800;color:{{ $col }};line-height:1.2;">{{ $val }}</div>
        </div>
        @endforeach
    </div>

    {{-- FILTER --}}
    @php $dupCount = $this->duplikatCount; @endphp
    <div style="display:flex;gap:.6rem;align-items:center;flex-wrap:wrap;margin-bottom:1.1rem;">
        <div style="position:relative;min-width:230px;flex:1;max-width:340px;">
            <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="position:absolute;left:.75rem;top:50%;transform:translateY(-50%);color:var(--mut);"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            <input wire:model.live.debounce.350ms="search" type="text" placeholder="Cari nama pasien / no. BPJS…" class="form-input" style="padding-left:2.2rem;width:100%;">
        </div>
        <input wire:model.live="filterBulan" type="month" class="form-input" style="max-width:170px;" title="Filter bulan">

        {{-- Toggle: hanya duplikat --}}
        <button wire:click="$toggle('filterDuplikat')" class="btn-outline"
            style="font-size:.74rem;{{ $filterDuplikat ? 'background:rgba(232,100,90,.12);border-color:rgba(232,100,90,.45);color:var(--red2);' : ($dupCount>0 ? 'border-color:rgba(232,100,90,.3);color:var(--red2);' : '') }}"
            title="Tampilkan hanya entri duplikat (pasien + tanggal sama)">
            <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="margin-right:.3rem;"><rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 01-2-2V4a2 2 0 012-2h9a2 2 0 012 2v1"/></svg>
            Duplikat
            @if($dupCount>0)<span style="background:var(--red);color:#fff;border-radius:999px;padding:.02rem .42rem;font-size:.62rem;font-weight:800;margin-left:.35rem;">{{ $dupCount }}</span>@endif
        </button>

        {{-- Toggle: terhapus --}}
        <button wire:click="$toggle('showDeleted')" class="btn-outline"
            style="font-size:.74rem;{{ $showDeleted ? 'background:rgba(217,164,65,.12);border-color:rgba(217,164,65,.45);color:var(--gold2);' : '' }}"
            title="Tampilkan pengambilan yang sudah dihapus (untuk dipulihkan)">
            <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="margin-right:.3rem;"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6"/></svg>
            {{ $showDeleted ? 'Lihat aktif' : 'Terhapus' }}
        </button>

        @if($search || $filterBulan || $filterDuplikat || $showDeleted)
        <button wire:click="$set('search','');$set('filterBulan','');$set('filterDuplikat',false);$set('showDeleted',false)" class="btn-outline" style="font-size:.74rem;color:var(--red);border-color:rgba(232,100,90,.3);">Reset</button>
        @endif
    </div>

    @if($dupCount > 0 && ! $filterDuplikat && ! $showDeleted)
    <div style="display:flex;align-items:center;gap:.55rem;padding:.6rem .9rem;border-radius:.5rem;background:rgba(232,100,90,.08);border:1px solid rgba(232,100,90,.22);margin-bottom:1rem;font-size:.76rem;color:var(--red2);">
        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="flex-shrink:0;"><path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
        Terdeteksi <strong>{{ $dupCount }}</strong> entri duplikat (pasien + tanggal sama).
        <button wire:click="$set('filterDuplikat',true)" style="background:none;border:none;color:var(--red2);text-decoration:underline;cursor:pointer;font-size:.76rem;padding:0;font-weight:600;">Tinjau &amp; bersihkan <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" style="display:inline-block;vertical-align:middle"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg></button>
    </div>
    @endif

    {{-- LIST --}}
    <div class="glass-card" style="padding:0;overflow:hidden;">
        <table style="width:100%;border-collapse:collapse;">
            <thead>
                @php
                    $thBase = 'padding:.65rem 1rem;font-size:.63rem;font-weight:700;letter-spacing:.07em;text-transform:uppercase;border-bottom:1px solid var(--line);background:var(--panel);cursor:pointer;user-select:none;white-space:nowrap;';
                    $arrow = function ($f) use ($sortField, $sortDir) {
                        if ($sortField !== $f) return '<span style="color:var(--mut2);opacity:.45;font-size:.7rem;">&#8597;</span>';
                        return '<span style="color:var(--emer);font-size:.72rem;font-weight:900;">'.($sortDir==='asc'?'&#8593;':'&#8595;').'</span>';
                    };
                    $thColor = fn ($f) => $sortField === $f ? 'var(--ink)' : 'var(--mut)';
                @endphp
                <tr>
                    <th wire:click="sortBy('pasien')" title="Urutkan nama pasien" style="text-align:left;{{ $thBase }}color:{{ $thColor('pasien') }};">Pasien {!! $arrow('pasien') !!}</th>
                    <th wire:click="sortBy('tanggal')" title="Urutkan tanggal" style="text-align:left;{{ $thBase }}color:{{ $thColor('tanggal') }};">Tanggal {!! $arrow('tanggal') !!}</th>
                    <th wire:click="sortBy('item')" title="Urutkan jumlah item" style="text-align:center;{{ $thBase }}color:{{ $thColor('item') }};">Item {!! $arrow('item') !!}</th>
                    <th wire:click="sortBy('laba')" title="Urutkan laba/rugi" style="text-align:right;{{ $thBase }}color:{{ $thColor('laba') }};">Laba/Rugi {!! $arrow('laba') !!}</th>
                    <th style="width:118px;text-align:center;padding:.65rem .5rem;font-size:.63rem;font-weight:700;letter-spacing:.07em;text-transform:uppercase;color:var(--mut);border-bottom:1px solid var(--line);background:var(--panel);">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @php $dupSet = $this->duplikatIds; @endphp
                @forelse($this->rows as $po)
                @php
                    $pBiaya=0; $pKlaim=0;
                    foreach($po->items as $it){ $bu=(float)$it->harga_beli_snapshot; $ku=(float)$it->harga_klaim_bpjs_snapshot*\App\Models\Obat::jfMultiplier($it->faktor_jasa_farmasi_snapshot); $pBiaya+=$it->jumlah_unit*$bu; $pKlaim+=$it->jumlah_unit*$ku; }
                    $pLaba=$pKlaim-$pBiaya; $pColor=$pLaba>0?'var(--emer2)':($pLaba<0?'var(--red2)':'var(--mut)');
                    $isOpen = $expandedId === $po->id;
                    $hasItems = $po->items->count() > 0;
                @endphp
                <tr wire:key="po-{{ $po->id }}" style="border-bottom:1px solid rgba(31,61,48,.4);cursor:pointer;transition:background .12s;" wire:click="toggleDetail({{ $po->id }})"
                    onmouseover="this.style.background='rgba(255,255,255,.018)'" onmouseout="this.style.background='transparent'">
                    <td style="padding:.7rem 1rem;">
                        <div style="font-weight:600;font-size:.84rem;color:var(--ink);display:flex;align-items:center;gap:.4rem;flex-wrap:wrap;">
                            {{ $po->pasien->nama ?? '—' }}
                            @if(in_array($po->id, $dupSet))
                            <span title="Entri duplikat: pasien & tanggal sama dengan entri lain" style="font-size:.56rem;font-weight:800;letter-spacing:.04em;padding:.05rem .4rem;border-radius:999px;background:rgba(232,100,90,.15);border:1px solid rgba(232,100,90,.35);color:var(--red2);text-transform:uppercase;"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" style="display:inline-block;vertical-align:middle"><path d="M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg> Duplikat</span>
                            @endif
                            @if($showDeleted)
                            <span style="font-size:.56rem;font-weight:800;padding:.05rem .4rem;border-radius:999px;background:rgba(217,164,65,.12);border:1px solid rgba(217,164,65,.3);color:var(--gold2);text-transform:uppercase;">Terhapus</span>
                            @endif
                        </div>
                        <div class="font-mono" style="font-size:.66rem;color:var(--mut2);">{{ $po->pasien->no_bpjs ?? '' }}</div>
                    </td>
                    <td style="padding:.7rem 1rem;">
                        <span class="font-mono" style="font-size:.78rem;color:var(--gold2);">{{ \Carbon\Carbon::parse($po->tanggal_pengambilan)->format('d M Y') }}</span>
                        @if($po->sumber_resep==='rme')<span style="margin-left:.4rem;font-size:.58rem;padding:.05rem .4rem;border-radius:999px;background:rgba(167,139,250,.12);border:1px solid rgba(167,139,250,.3);color:#c4b5fd;">RME</span>@endif
                    </td>
                    <td style="padding:.7rem 1rem;text-align:center;">
                        <span style="font-size:.7rem;color:var(--mut);">{{ $po->total_item ?? $po->items->count() }}</span>
                    </td>
                    <td style="padding:.7rem 1rem;text-align:right;">
                        @if($hasItems)
                        <span class="font-mono" style="font-size:.82rem;font-weight:700;color:{{ $pColor }};">{{ $pLaba>=0?'+':'−' }}Rp {{ number_format(abs($pLaba),0,',','.') }}</span>
                        @else
                        <span style="font-size:.68rem;color:var(--mut2);font-style:italic;">—</span>
                        @endif
                    </td>
                    <td style="padding:.6rem .5rem;">
                        <div style="display:flex;align-items:center;justify-content:center;gap:.3rem;">
                            @if($showDeleted)
                            <button wire:click.stop="restorePengambilan({{ $po->id }})" title="Pulihkan"
                                style="background:rgba(63,207,142,.1);border:1px solid rgba(63,207,142,.25);color:var(--emer2);border-radius:.35rem;padding:.28rem .45rem;cursor:pointer;display:inline-flex;">
                                <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><polyline points="1 4 1 10 7 10"/><path d="M3.51 15a9 9 0 102.13-9.36L1 10"/></svg>
                            </button>
                            @else
                            <button wire:click.stop="openEdit({{ $po->id }})" title="Edit"
                                style="background:rgba(217,164,65,.1);border:1px solid rgba(217,164,65,.25);color:var(--gold2);border-radius:.35rem;padding:.28rem .45rem;cursor:pointer;display:inline-flex;">
                                <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.12 2.12 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                            </button>
                            <button wire:click.stop="deletePengambilan({{ $po->id }})"
                                wire:confirm="Hapus pengambilan {{ $po->pasien->nama ?? '' }} ({{ \Carbon\Carbon::parse($po->tanggal_pengambilan)->format('d M Y') }})? Bisa dipulihkan dari filter 'Terhapus'."
                                title="Hapus"
                                style="background:rgba(232,100,90,.1);border:1px solid rgba(232,100,90,.22);color:var(--red2);border-radius:.35rem;padding:.28rem .45rem;cursor:pointer;display:inline-flex;">
                                <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2"/></svg>
                            </button>
                            @endif
                            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24" style="color:var(--mut);transition:transform .2s;{{ $isOpen ? 'transform:rotate(180deg);' : '' }}"><polyline points="6 9 12 15 18 9"/></svg>
                        </div>
                    </td>
                </tr>
                @if($isOpen)
                <tr wire:key="det-{{ $po->id }}">
                    <td colspan="5" style="padding:0;background:linear-gradient(180deg,rgba(111,177,224,.05),rgba(10,20,16,.15));border-bottom:1px solid rgba(31,61,48,.5);">
                        <div style="padding:.9rem 1.25rem;">
                            @if($hasItems)
                            <div style="overflow-x:auto;">
                            <table style="width:100%;border-collapse:collapse;font-size:.74rem;">
                                <thead>
                                    <tr style="color:var(--mut);">
                                        <th style="text-align:left;padding:.4rem .9rem;font-size:.62rem;text-transform:uppercase;letter-spacing:.04em;">Obat</th>
                                        <th style="text-align:right;padding:.4rem .6rem;font-size:.62rem;text-transform:uppercase;">Jml</th>
                                        <th style="text-align:right;padding:.4rem .6rem;font-size:.62rem;text-transform:uppercase;">Beli/unit</th>
                                        <th style="text-align:right;padding:.4rem .6rem;font-size:.62rem;text-transform:uppercase;">Klaim/unit</th>
                                        <th style="text-align:right;padding:.4rem .6rem;font-size:.62rem;text-transform:uppercase;">HPP</th>
                                        <th style="text-align:right;padding:.4rem .6rem;font-size:.62rem;text-transform:uppercase;">Klaim</th>
                                        <th style="text-align:right;padding:.4rem .9rem;font-size:.62rem;text-transform:uppercase;">Laba/Rugi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($po->items as $it)
                                    @php
                                        $bu=(float)$it->harga_beli_snapshot; $ku=(float)$it->harga_klaim_bpjs_snapshot*\App\Models\Obat::jfMultiplier($it->faktor_jasa_farmasi_snapshot);
                                        $hpp=$it->jumlah_unit*$bu; $klm=$it->jumlah_unit*$ku; $lb=$klm-$hpp; $c=$lb>0?'var(--emer2)':($lb<0?'var(--red2)':'var(--mut)');
                                    @endphp
                                    <tr style="border-top:1px solid rgba(31,61,48,.35);">
                                        <td style="padding:.4rem .9rem;color:var(--ink);">{{ $it->obat->nama_obat ?? '—' }}</td>
                                        <td class="font-mono" style="padding:.4rem .6rem;text-align:right;color:var(--mut);">{{ $it->jumlah_unit }} {{ $it->satuan }}</td>
                                        <td class="font-mono" style="padding:.4rem .6rem;text-align:right;color:var(--red2);">{{ number_format($bu,0,',','.') }}</td>
                                        <td class="font-mono" style="padding:.4rem .6rem;text-align:right;color:var(--blue);">{{ number_format($ku,0,',','.') }}</td>
                                        <td class="font-mono" style="padding:.4rem .6rem;text-align:right;color:var(--mut);">{{ number_format($hpp,0,',','.') }}</td>
                                        <td class="font-mono" style="padding:.4rem .6rem;text-align:right;color:var(--ink);">{{ number_format($klm,0,',','.') }}</td>
                                        <td class="font-mono" style="padding:.4rem .9rem;text-align:right;font-weight:700;color:{{ $c }};">{{ $lb>=0?'+':'−' }}{{ number_format(abs($lb),0,',','.') }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                            </div>
                            @if($po->catatan)<div style="margin-top:.6rem;font-size:.72rem;color:var(--mut);">Catatan: {{ $po->catatan }}</div>@endif
                            @else
                            <div style="padding:.6rem;text-align:center;color:var(--mut);font-size:.74rem;border:1px dashed var(--line);border-radius:.5rem;">
                                Detail item tidak tersedia untuk pengambilan ini (data lama tanpa rincian obat).
                            </div>
                            @endif
                        </div>
                    </td>
                </tr>
                @endif
                @empty
                <tr><td colspan="5" style="text-align:center;padding:2.5rem 1rem;color:var(--mut);font-size:.84rem;">Belum ada riwayat pengambilan{{ $search ? ' untuk "'.$search.'"' : '' }}.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div style="margin-top:1rem;">{{ $this->rows->links() }}</div>

    {{-- ═══════════ EDIT MODAL ═══════════ --}}
    @if($editId)
    <div style="position:fixed;inset:0;z-index:300;display:flex;align-items:center;justify-content:center;padding:1.5rem;">
        <div wire:click="cancelEdit" style="position:absolute;inset:0;background:rgba(4,10,7,.7);backdrop-filter:blur(3px);"></div>
        <div class="glass-card" style="position:relative;width:440px;max-width:95vw;padding:1.5rem;background:var(--bg);border:1px solid var(--line2);">
            <div style="display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:1.2rem;">
                <div>
                    <div class="font-heading" style="font-size:1.1rem;color:var(--ink);">Edit Pengambilan</div>
                    <div style="font-size:.72rem;color:var(--mut);margin-top:.2rem;">#{{ $editId }} · ubah tanggal, status, jadwal &amp; catatan</div>
                </div>
                <button wire:click="cancelEdit" style="background:var(--panel);border:1px solid var(--line);color:var(--mut);width:30px;height:30px;border-radius:.4rem;cursor:pointer;font-size:.95rem;"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" style="display:inline-block;vertical-align:middle"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg></button>
            </div>

            <form wire:submit="saveEdit" style="display:grid;gap:.85rem;">
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:.7rem;">
                    <div>
                        <label class="form-label">Tanggal Pengambilan *</label>
                        <input wire:model="editTanggal" type="date" class="form-input">
                        @error('editTanggal')<div style="color:var(--red);font-size:.7rem;margin-top:.2rem;">{{ $message }}</div>@enderror
                    </div>
                    <div>
                        <label class="form-label">Jadwal Berikutnya</label>
                        <input wire:model="editJadwal" type="date" class="form-input">
                        @error('editJadwal')<div style="color:var(--red);font-size:.7rem;margin-top:.2rem;">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div>
                    <label class="form-label">Status</label>
                    <select wire:model="editStatus" class="form-input" style="appearance:auto;-webkit-appearance:auto;">
                        @foreach($statusList as $val => $lbl)
                        <option value="{{ $val }}">{{ $lbl }}</option>
                        @endforeach
                    </select>
                    @error('editStatus')<div style="color:var(--red);font-size:.7rem;margin-top:.2rem;">{{ $message }}</div>@enderror
                </div>
                <div>
                    <label class="form-label">Catatan</label>
                    <textarea wire:model="editCatatan" rows="2" class="form-input" placeholder="Opsional"></textarea>
                    @error('editCatatan')<div style="color:var(--red);font-size:.7rem;margin-top:.2rem;">{{ $message }}</div>@enderror
                </div>
                <div style="display:flex;gap:.5rem;justify-content:flex-end;margin-top:.4rem;">
                    <button type="button" wire:click="cancelEdit" class="btn-outline">Batal</button>
                    <button type="submit" class="btn-gold" wire:loading.attr="disabled" wire:target="saveEdit">
                        <span wire:loading.remove wire:target="saveEdit">Simpan</span>
                        <span wire:loading wire:target="saveEdit">Menyimpan…</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif
</div>
