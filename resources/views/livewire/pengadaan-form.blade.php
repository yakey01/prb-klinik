<div>
<style>
/* ── Combobox transitions ── */
.cb-dd-enter,.cb-dd-leave{transition:opacity .13s cubic-bezier(.4,0,.2,1),transform .13s cubic-bezier(.4,0,.2,1);}
.cb-dd-enter-from,.cb-dd-leave-to{opacity:0;transform:translateY(-5px) scaleY(.96);pointer-events:none;}
.cb-dd-enter-to,.cb-dd-leave-from{opacity:1;transform:translateY(0) scaleY(1);}
/* Obat dropdown animation */
@keyframes cbSlideIn{from{opacity:0;transform:translateY(-5px) scaleY(.96);}to{opacity:1;transform:translateY(0) scaleY(1);}}
@keyframes cbSlideOut{from{opacity:1;transform:translateY(0) scaleY(1);}to{opacity:0;transform:translateY(-5px) scaleY(.96);}}
.obat-dd-open{animation:cbSlideIn .13s cubic-bezier(.4,0,.2,1) both;}
/* Scrollbar slim */
.cb-scroll::-webkit-scrollbar{width:4px;} .cb-scroll::-webkit-scrollbar-track{background:transparent;} .cb-scroll::-webkit-scrollbar-thumb{background:rgba(255,255,255,.12);border-radius:4px;}
mark.hl{background:rgba(217,164,65,.3);color:var(--gold2);border-radius:2px;font-weight:700;font-style:normal;}
</style>

<form wire:submit="save">

    {{-- HEADER INFO --}}
    <div class="glass-card" style="padding:1.5rem; margin-bottom:1.5rem;">
        <div class="font-heading" style="font-size:1.05rem; color:var(--ink); margin-bottom:1.2rem;">Informasi Purchase Order</div>
        <div style="display:grid; grid-template-columns:repeat(auto-fit,minmax(220px,1fr)); gap:1rem;">

            {{-- ══ DISTRIBUTOR COMBOBOX (pure JS, position:fixed) ══ --}}
            <div id="dist-wrap" style="position:relative;">
                <label class="form-label">Distributor / PBF *</label>

                {{-- Hidden select — Livewire binding + data source --}}
                <select wire:model="distributor_id" id="dist-sel" style="display:none;" aria-hidden="true">
                    <option value="0">--</option>
                    @foreach($this->distributors as $d)
                    <option value="{{ $d->id }}" data-name="{{ $d->name }}">{{ $d->name }}</option>
                    @endforeach
                </select>

                {{-- Trigger button --}}
                <button type="button" id="dist-btn"
                    onclick="DistCb.toggle()"
                    onkeydown="DistCb.keyBtn(event)"
                    style="width:100%;display:flex;align-items:center;gap:.6rem;padding:.58rem .85rem;
                        background:var(--panel);border:1px solid var(--line);border-radius:.5rem;
                        cursor:pointer;text-align:left;transition:border-color .15s,box-shadow .15s;outline:none;">
                    <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"
                        style="color:var(--mut);flex-shrink:0;">
                        <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
                    </svg>
                    <span id="dist-display"
                        style="flex:1;font-size:.85rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;color:var(--mut);">
                        — Pilih Distributor —
                    </span>
                    <svg id="dist-chevron" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5"
                        viewBox="0 0 24 24" style="flex-shrink:0;color:var(--mut);transition:transform .2s,color .15s;">
                        <polyline points="6 9 12 15 18 9"/>
                    </svg>
                </button>

                {{-- Dropdown panel — JS moves this to document.body --}}
                <div id="dist-dd"
                    style="display:none;position:fixed;z-index:9999;background:#0d1c15;
                        border:1px solid rgba(217,164,65,.28);border-radius:.65rem;overflow:hidden;
                        box-shadow:0 20px 60px rgba(0,0,0,.85),0 0 0 1px rgba(255,255,255,.03);">
                    <div style="padding:.45rem .5rem;border-bottom:1px solid rgba(255,255,255,.06);">
                        <div style="display:flex;align-items:center;gap:.45rem;background:rgba(255,255,255,.05);border-radius:.4rem;padding:.4rem .65rem;">
                            <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"
                                style="color:var(--mut);flex-shrink:0;">
                                <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
                            </svg>
                            <input id="dist-qi" type="text" placeholder="Cari distributor…" autocomplete="off"
                                oninput="DistCb.filter(this.value)"
                                onkeydown="DistCb.keyDd(event)"
                                style="background:none;border:none;outline:none;color:var(--ink);font-size:.82rem;width:100%;caret-color:var(--gold2);">
                            <button type="button" id="dist-qclr" onclick="DistCb.clearQ()"
                                style="display:none;background:none;border:none;cursor:pointer;color:var(--mut);font-size:.75rem;padding:0;line-height:1;flex-shrink:0;"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" style="display:inline-block;vertical-align:middle"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg></button>
                        </div>
                    </div>
                    <div id="dist-list" class="cb-scroll" style="max-height:220px;overflow-y:auto;"></div>
                    <div id="dist-foot" style="padding:.28rem .85rem;font-size:.67rem;color:var(--mut);border-top:1px solid rgba(255,255,255,.05);text-align:right;"></div>
                </div>

                @error('distributor_id') <span style="color:var(--red);font-size:.75rem;">{{ $message }}</span> @enderror
            </div>
            {{-- ══ END DISTRIBUTOR ══ --}}

            <div>
                <label class="form-label">Tanggal PO *</label>
                <input wire:model="tanggal_po" type="date" class="form-input">
                @error('tanggal_po') <span style="color:var(--red);font-size:.75rem;">{{ $message }}</span> @enderror
            </div>
            <div>
                <label class="form-label">Nomor Invoice</label>
                <input wire:model="nomor_invoice" type="text" placeholder="Opsional" class="form-input">
            </div>
            <div>
                <label class="form-label">Catatan</label>
                <input wire:model="catatan" type="text" placeholder="Opsional" class="form-input">
            </div>
        </div>
    </div>

    {{-- ITEMS TABLE --}}
    <div class="glass-card" style="padding:1.5rem; margin-bottom:1.5rem; overflow:visible; min-height:200px;">
        <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:1.1rem;">
            <div>
                <div class="font-heading" style="font-size:1.05rem; color:var(--ink);">Daftar Obat</div>
                <div style="font-size:.7rem;color:var(--mut);margin-top:.2rem;">
                    Kolom bertanda <span style="color:var(--red2);font-weight:700;">*</span> wajib diisi sebelum menyimpan
                </div>
            </div>
            <button type="button" wire:click="addRow" class="btn-outline" style="font-size:.78rem; padding:.4rem .9rem;">
                <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                Tambah Baris
            </button>
        </div>

        <div style="overflow-x:auto;">
        <table style="width:100%; border-collapse:collapse; min-width:720px;">
            <thead>
                <tr>
                    <th class="font-label" style="font-size:.65rem;color:var(--mut);padding:.6rem .75rem;text-align:left;border-bottom:1px solid var(--line);min-width:340px;">
                        Obat &amp; Jenis <span style="color:var(--red2);">*</span>
                    </th>
                    <th class="font-label" style="font-size:.65rem;color:var(--mut);padding:.6rem .75rem;text-align:right;border-bottom:1px solid var(--line);min-width:70px;">
                        Box <span style="color:var(--red2);">*</span>
                    </th>
                    <th class="font-label" style="font-size:.65rem;color:var(--mut);padding:.6rem .75rem;text-align:right;border-bottom:1px solid var(--line);min-width:80px;">
                        Isi/Box <span style="color:var(--red2);">*</span>
                    </th>
                    <th class="font-label" style="font-size:.65rem;color:var(--mut);padding:.6rem .75rem;text-align:right;border-bottom:1px solid var(--line);min-width:130px;">
                        Harga/Box (Rp) <span style="color:var(--red2);">*</span>
                    </th>
                    <th class="font-label" style="font-size:.65rem;color:var(--mut);padding:.6rem .75rem;text-align:center;border-bottom:1px solid var(--line);min-width:120px;">Expired</th>
                    <th class="font-label" style="font-size:.65rem;color:var(--mut);padding:.6rem .75rem;text-align:right;border-bottom:1px solid var(--line);min-width:120px;">Subtotal</th>
                    <th style="border-bottom:1px solid var(--line);width:40px;"></th>
                </tr>
            </thead>
            <tbody>
                @php $obatMap = $this->obatList->keyBy('id'); @endphp
                @foreach($rows as $i => $row)
                @php
                    $isKronis     = ($row['tipe_obat'] ?? 'kronis') === 'kronis';
                    $selectedNama = $row['obat_id'] ? ($obatMap[$row['obat_id']]->nama_obat ?? '') : '';
                @endphp
                <tr wire:key="poRow-{{ $i }}" style="border-left:3px solid {{ $isKronis ? 'rgba(63,207,142,.4)' : 'rgba(111,177,224,.4)' }};">
                    <td style="padding:.5rem .75rem;">
                        <div style="display:flex;align-items:center;gap:.5rem;">
                            {{-- KRONIS / NON KRONIS toggle --}}
                            <div style="display:flex;border-radius:.5rem;overflow:hidden;border:1px solid var(--line2);flex-shrink:0;">
                                <button type="button"
                                    wire:click="$set('rows.{{ $i }}.tipe_obat','kronis')"
                                    style="padding:.45rem .85rem;font-size:.75rem;font-weight:700;cursor:pointer;border:none;line-height:1;transition:all .15s;letter-spacing:.03em;
                                        {{ $isKronis ? 'background:rgba(63,207,142,.22);color:var(--emer2);' : 'background:transparent;color:var(--mut);' }}">
                                    KRONIS
                                </button>
                                <button type="button"
                                    wire:click="$set('rows.{{ $i }}.tipe_obat','non_kronis')"
                                    style="padding:.45rem .85rem;font-size:.75rem;font-weight:700;cursor:pointer;border:none;border-left:1px solid var(--line2);line-height:1;transition:all .15s;letter-spacing:.03em;
                                        {{ !$isKronis ? 'background:rgba(111,177,224,.22);color:var(--blue);' : 'background:transparent;color:var(--mut);' }}">
                                    NON KRONIS
                                </button>
                            </div>

                            {{-- ══ OBAT SEARCHABLE COMBOBOX ══ --}}
                            <div style="position:relative;flex:1;" id="obat-wrap-{{ $i }}">
                                <div style="position:relative;">
                                    <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"
                                        style="position:absolute;left:.65rem;top:50%;transform:translateY(-50%);color:var(--mut);pointer-events:none;z-index:1;">
                                        <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
                                    </svg>
                                    <input
                                        type="text"
                                        id="obat-txt-{{ $i }}"
                                        autocomplete="off"
                                        placeholder="Ketik nama obat…"
                                        value="{{ $selectedNama }}"
                                        oninput="ObatCb.search({{ $i }},this.value)"
                                        onfocus="ObatCb.focus({{ $i }},this.value)"
                                        onkeydown="ObatCb.key(event,{{ $i }})"
                                        onblur="ObatCb.blur({{ $i }})"
                                        class="form-input"
                                        style="font-size:.82rem;padding-left:2rem;padding-right:1.6rem;cursor:text;">
                                    {{-- Clear button --}}
                                    <button type="button" id="obat-clr-{{ $i }}"
                                        onclick="ObatCb.clear({{ $i }})"
                                        style="display:{{ $selectedNama ? 'flex' : 'none' }};position:absolute;right:.45rem;top:50%;transform:translateY(-50%);
                                            background:rgba(255,255,255,.08);border:none;border-radius:50%;width:16px;height:16px;
                                            align-items:center;justify-content:center;cursor:pointer;color:var(--mut);font-size:.7rem;line-height:1;padding:0;">
                                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" style="display:inline-block;vertical-align:middle"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                                    </button>
                                </div>

                                {{-- Hidden select for Livewire binding --}}
                                <select wire:model.live="rows.{{ $i }}.obat_id"
                                    id="obat-sel-{{ $i }}"
                                    style="display:none;">
                                    <option value="0">--</option>
                                    @foreach($this->obatList as $obat)
                                    <option value="{{ $obat->id }}"
                                        data-nama="{{ $obat->nama_obat }}"
                                        data-diag="{{ $obat->kategori_diagnosis ?? '' }}"
                                        data-tipe="{{ $obat->tipe_obat }}">
                                        {{ $obat->nama_obat }}
                                    </option>
                                    @endforeach
                                </select>

                                {{-- Dropdown appended to body by JS --}}
                                <div id="obat-dd-{{ $i }}"
                                    style="display:none;position:fixed;background:#0d1c15;
                                        border:1px solid rgba(63,207,142,.25);border-radius:.65rem;z-index:9999;
                                        max-height:260px;overflow-y:auto;
                                        box-shadow:0 20px 60px rgba(0,0,0,.85),0 0 0 1px rgba(255,255,255,.03);
                                        transform-origin:top;">
                                </div>
                            </div>
                            {{-- ══ END OBAT ══ --}}
                        </div>
                        @error("rows.$i.obat_id") <div style="color:var(--red2);font-size:.68rem;margin-top:.2rem;">Pilih obat terlebih dahulu</div> @enderror
                    </td>
                    <td style="padding:.5rem .75rem;">
                        <input wire:model.live="rows.{{ $i }}.jumlah_box" type="number" min="1" class="form-input font-mono"
                            style="text-align:right;font-size:.82rem;width:70px;{{ $errors->has('rows.'.$i.'.jumlah_box') ? 'border-color:var(--red2);' : '' }}">
                        @error("rows.$i.jumlah_box") <div style="color:var(--red2);font-size:.65rem;margin-top:.15rem;">Wajib ≥ 1</div> @enderror
                    </td>
                    <td style="padding:.5rem .75rem;">
                        <input wire:model.live="rows.{{ $i }}.isi_per_box" type="number" min="1" class="form-input font-mono"
                            style="text-align:right;font-size:.82rem;width:70px;{{ $errors->has('rows.'.$i.'.isi_per_box') ? 'border-color:var(--red2);' : '' }}">
                        @error("rows.$i.isi_per_box") <div style="color:var(--red2);font-size:.65rem;margin-top:.15rem;">Wajib ≥ 1</div> @enderror
                    </td>
                    <td style="padding:.5rem .75rem;">
                        <input wire:model.live="rows.{{ $i }}.harga_per_box" type="number" min="1" step="any" class="form-input font-mono"
                            style="text-align:right;font-size:.82rem;{{ $errors->has('rows.'.$i.'.harga_per_box') ? 'border-color:var(--red2);' : '' }}">
                        @error("rows.$i.harga_per_box") <div style="color:var(--red2);font-size:.65rem;margin-top:.15rem;">Wajib diisi</div> @enderror
                    </td>
                    <td style="padding:.5rem .75rem;text-align:center;">
                        <input wire:model="rows.{{ $i }}.tanggal_kadaluarsa" type="date"
                            style="background:var(--panel);border:1px solid var(--line);color:var(--ink);border-radius:.35rem;padding:.22rem .4rem;font-size:.75rem;font-family:monospace;max-width:120px;"
                            title="Tanggal Kadaluarsa Obat">
                    </td>
                    <td style="padding:.5rem .75rem;text-align:right;">
                        <span class="font-mono" style="font-size:.85rem; color:var(--emer2);">
                            Rp {{ number_format($row['subtotal'],0,',','.') }}
                        </span>
                    </td>
                    <td style="padding:.5rem .4rem;text-align:center;">
                        <button type="button" wire:click="removeRow({{ $i }})" class="btn-danger" style="padding:.3rem .5rem;">
                            <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                        </button>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        </div>
    </div>

    {{-- SPLIT SUBTOTALS + SUBMIT --}}
    <div style="display:flex;align-items:flex-end;justify-content:space-between;flex-wrap:wrap;gap:1rem;">
        <div style="font-size:.8rem;color:var(--mut);">{{ count($rows) }} item dalam PO ini</div>
        <div style="display:flex;align-items:flex-end;gap:1.5rem;flex-wrap:wrap;">
            @if($this->subtotalKronis > 0 || $this->subtotalNonKronis > 0)
            <div style="background:var(--panel);border:1px solid var(--line);border-radius:.75rem;padding:.8rem 1.1rem;">
                <div style="font-size:.65rem;color:var(--mut);text-transform:uppercase;letter-spacing:.07em;margin-bottom:.5rem;">Rincian per Jenis</div>
                @if($this->subtotalKronis > 0)
                <div style="display:flex;align-items:center;justify-content:space-between;gap:1.5rem;margin-bottom:.3rem;">
                    <span style="font-size:.75rem;color:var(--emer2);display:flex;align-items:center;gap:.3rem;">
                        <span style="width:.5rem;height:.5rem;border-radius:50%;background:var(--emer2);display:inline-block;"></span>Obat Kronis
                    </span>
                    <span class="font-mono" style="font-size:.8rem;color:var(--emer2);font-weight:600;">Rp {{ number_format($this->subtotalKronis,0,',','.') }}</span>
                </div>
                @endif
                @if($this->subtotalNonKronis > 0)
                <div style="display:flex;align-items:center;justify-content:space-between;gap:1.5rem;">
                    <span style="font-size:.75rem;color:var(--blue);display:flex;align-items:center;gap:.3rem;">
                        <span style="width:.5rem;height:.5rem;border-radius:50%;background:var(--blue);display:inline-block;"></span>Obat Non-Kronis
                    </span>
                    <span class="font-mono" style="font-size:.8rem;color:var(--blue);font-weight:600;">Rp {{ number_format($this->subtotalNonKronis,0,',','.') }}</span>
                </div>
                @endif
            </div>
            @endif
            <div style="text-align:right;">
                <div class="font-label" style="font-size:.65rem;color:var(--mut);">Total Invoice</div>
                <div class="font-mono" style="font-size:1.5rem;font-weight:800;color:var(--gold2);">
                    Rp {{ number_format($this->grandTotal,0,',','.') }}
                </div>
            </div>
            <button type="submit" class="btn-gold" wire:loading.attr="disabled" wire:target="save">
                <span wire:loading.remove wire:target="save" style="display:inline-flex;align-items:center;gap:.4rem;">
                    <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h11l5 5v11a2 2 0 01-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/></svg>
                    Simpan PO
                </span>
                <span wire:loading wire:target="save">Menyimpan PO…</span>
            </button>
        </div>
    </div>

</form>

{{-- ─────────────── HISTORY PO ─────────────── --}}
<div style="margin-top:2.5rem;">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1rem;">
        <div>
            <div class="font-label" style="font-size:.68rem;color:var(--mut);margin-bottom:.2rem;">Histori</div>
            <div class="font-heading" style="font-size:1.05rem;color:var(--ink);">Purchase Order Tersimpan</div>
        </div>
        <a href="{{ route('riwayat.index') }}" style="font-size:.78rem;color:var(--gold2);text-decoration:none;">Lihat semua <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" style="display:inline-block;vertical-align:middle"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg></a>
    </div>

    @forelse($this->recentOrders as $po)
    <div class="glass-card" style="margin-bottom:.75rem;overflow:hidden;" x-data="{ open: false }">
        <div style="display:flex;align-items:center;gap:1rem;padding:.9rem 1.25rem;cursor:pointer;" @click="open=!open">
            <div style="width:8px;height:8px;border-radius:50%;background:var(--emer);flex-shrink:0;box-shadow:0 0 5px var(--emer);"></div>
            <div style="flex:1;display:flex;align-items:center;gap:.85rem;flex-wrap:wrap;">
                <span class="font-mono" style="font-size:.78rem;color:var(--gold2);">{{ $po->tanggal_po->format('d M Y') }}</span>
                <span style="font-weight:600;color:var(--ink);font-size:.88rem;">{{ $po->distributor->name }}</span>
                @if($po->nomor_invoice)
                <span style="color:var(--mut);font-size:.75rem;">#{{ $po->nomor_invoice }}</span>
                @endif
                <span style="background:rgba(111,177,224,.1);border:1px solid rgba(111,177,224,.2);border-radius:999px;padding:.1rem .45rem;font-size:.68rem;color:var(--blue);">
                    {{ $po->items->count() }} item
                </span>
                @php $hasBpjs=$po->items->contains('tipe_obat','kronis'); $hasUmum=$po->items->contains('tipe_obat','non_kronis'); @endphp
                @if($hasBpjs)
                <span style="background:rgba(63,207,142,.1);border:1px solid rgba(63,207,142,.25);border-radius:.3rem;padding:.1rem .4rem;font-size:.65rem;font-weight:700;color:var(--emer2);">Kronis</span>
                @endif
                @if($hasUmum)
                <span style="background:rgba(111,177,224,.1);border:1px solid rgba(111,177,224,.25);border-radius:.3rem;padding:.1rem .4rem;font-size:.65rem;font-weight:700;color:var(--blue);">Non-Kronis</span>
                @endif
            </div>
            <div class="font-mono" style="font-size:1rem;font-weight:700;color:var(--emer2);flex-shrink:0;">
                Rp {{ number_format($po->total_nilai,0,',','.') }}
            </div>
            <svg x-bind:style="open?'transform:rotate(180deg)':''" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="transition:transform .2s;color:var(--mut);flex-shrink:0;"><polyline points="6 9 12 15 18 9"/></svg>
        </div>
        <div x-show="open" x-transition style="border-top:1px solid var(--line);padding:.75rem 1.25rem;">
            <table style="width:100%;border-collapse:collapse;font-size:.8rem;">
                <thead>
                    <tr>
                        <th style="text-align:left;padding:.3rem .5rem;font-size:.65rem;color:var(--mut);font-weight:600;text-transform:uppercase;letter-spacing:.05em;border-bottom:1px solid var(--line);width:70px;">Jenis</th>
                        <th style="text-align:left;padding:.3rem .5rem;font-size:.65rem;color:var(--mut);font-weight:600;text-transform:uppercase;letter-spacing:.05em;border-bottom:1px solid var(--line);">Obat</th>
                        <th style="text-align:right;padding:.3rem .5rem;font-size:.65rem;color:var(--mut);font-weight:600;text-transform:uppercase;letter-spacing:.05em;border-bottom:1px solid var(--line);">Box</th>
                        <th style="text-align:right;padding:.3rem .5rem;font-size:.65rem;color:var(--mut);font-weight:600;text-transform:uppercase;letter-spacing:.05em;border-bottom:1px solid var(--line);">Isi/Box</th>
                        <th style="text-align:right;padding:.3rem .5rem;font-size:.65rem;color:var(--mut);font-weight:600;text-transform:uppercase;letter-spacing:.05em;border-bottom:1px solid var(--line);">Harga/Box</th>
                        <th style="text-align:right;padding:.3rem .5rem;font-size:.65rem;color:var(--mut);font-weight:600;text-transform:uppercase;letter-spacing:.05em;border-bottom:1px solid var(--line);">Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($po->items as $item)
                    @php $isKronis = ($item->tipe_obat ?? 'kronis') === 'kronis'; @endphp
                    <tr style="border-left:3px solid {{ $isKronis ? 'rgba(63,207,142,.3)' : 'rgba(111,177,224,.3)' }};">
                        <td style="padding:.3rem .5rem;">
                            <span style="font-size:.65rem;font-weight:700;padding:.15rem .4rem;border-radius:.3rem;
                                {{ $isKronis ? 'background:rgba(63,207,142,.12);color:var(--emer2);border:1px solid rgba(63,207,142,.25);' : 'background:rgba(111,177,224,.12);color:var(--blue);border:1px solid rgba(111,177,224,.25);' }}">
                                {{ $isKronis ? 'KRONIS' : 'NON KRONIS' }}
                            </span>
                        </td>
                        <td style="padding:.3rem .5rem;color:var(--ink);">{{ $item->obat->nama_obat ?? '-' }}</td>
                        <td class="font-mono" style="padding:.3rem .5rem;text-align:right;color:var(--mut);">{{ $item->jumlah_box }}</td>
                        <td class="font-mono" style="padding:.3rem .5rem;text-align:right;color:var(--mut);">{{ $item->isi_per_box }}</td>
                        <td class="font-mono" style="padding:.3rem .5rem;text-align:right;color:var(--mut);">Rp {{ number_format($item->harga_per_box,0,',','.') }}</td>
                        <td class="font-mono" style="padding:.3rem .5rem;text-align:right;font-weight:600;color:var(--emer2);">Rp {{ number_format($item->subtotal,0,',','.') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @if($po->catatan)
            <div style="margin-top:.5rem;font-size:.75rem;color:var(--mut);">Catatan: {{ $po->catatan }}</div>
            @endif
        </div>
    </div>
    @empty
    <div class="glass-card" style="padding:2rem;text-align:center;color:var(--mut);font-size:.85rem;">
        Belum ada purchase order tersimpan.
    </div>
    @endforelse

    @if($this->recentOrders->hasPages())
    <div style="margin-top:1rem;display:flex;justify-content:center;gap:.4rem;flex-wrap:wrap;">
        @if($this->recentOrders->onFirstPage())
        <span style="padding:.35rem .7rem;border-radius:.4rem;font-size:.78rem;color:var(--mut);border:1px solid var(--line);opacity:.4;"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" style="display:inline-block;vertical-align:middle"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg> Prev</span>
        @else
        <button wire:click="previousPage" style="padding:.35rem .7rem;border-radius:.4rem;font-size:.78rem;color:var(--gold2);border:1px solid rgba(217,164,65,.3);background:transparent;cursor:pointer;"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" style="display:inline-block;vertical-align:middle"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg> Prev</button>
        @endif
        <span style="padding:.35rem .75rem;font-size:.78rem;color:var(--mut);">
            Hal {{ $this->recentOrders->currentPage() }} / {{ $this->recentOrders->lastPage() }}
            &nbsp;·&nbsp; {{ $this->recentOrders->total() }} PO
        </span>
        @if($this->recentOrders->hasMorePages())
        <button wire:click="nextPage" style="padding:.35rem .7rem;border-radius:.4rem;font-size:.78rem;color:var(--gold2);border:1px solid rgba(217,164,65,.3);background:transparent;cursor:pointer;">Next <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" style="display:inline-block;vertical-align:middle"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg></button>
        @else
        <span style="padding:.35rem .7rem;border-radius:.4rem;font-size:.78rem;color:var(--mut);border:1px solid var(--line);opacity:.4;">Next <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" style="display:inline-block;vertical-align:middle"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg></span>
        @endif
    </div>
    @endif
</div>

<script>
/* ══════════════════════════════════════════════════════
   DistCb — distributor combobox (pure JS, position:fixed)
   Root cause fix: no Alpine, no window.open conflict,
   dropdown appended to body -> never clipped by overflow
   ══════════════════════════════════════════════════════ */
window.DistCb = (function () {
    let activeIdx = 0;
    let filtered  = [];
    let selId     = 0;

    const el  = id => document.getElementById(id);
    const esc = s  => s.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');

    function hlText(str, q) {
        if (!q) return esc(str);
        const r = q.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
        return esc(str).replace(new RegExp(r, 'gi'), m => '<mark class="hl">' + m + '</mark>');
    }

    function getOpts() {
        const sel = el('dist-sel');
        if (!sel) return [];
        return Array.from(sel.options)
            .filter(o => o.value !== '0')
            .map(o => ({ id: o.value, name: o.dataset.name || o.text }));
    }

    function posDD() {
        const btn = el('dist-btn'), dd = el('dist-dd');
        if (!btn || !dd) return;
        const r = btn.getBoundingClientRect();
        const below = window.innerHeight - r.bottom - 8;
        dd.style.top    = (r.bottom + 4) + 'px';
        dd.style.left   = r.left + 'px';
        dd.style.width  = Math.max(r.width, 240) + 'px';
    }

    function renderList(q) {
        const listEl = el('dist-list'), footEl = el('dist-foot');
        if (!listEl) return;
        const query = (q || '').trim().toLowerCase();
        const opts  = getOpts();
        filtered = query ? opts.filter(o => o.name.toLowerCase().includes(query)) : [...opts];
        activeIdx = Math.min(activeIdx, Math.max(0, filtered.length - 1));

        if (!filtered.length) {
            listEl.innerHTML = '<div style="padding:1rem;text-align:center;font-size:.78rem;color:var(--mut);">Tidak ditemukan</div>';
        } else {
            listEl.innerHTML = filtered.map((o, i) =>
                `<div class="dist-item" data-id="${o.id}" data-name="${esc(o.name)}"
                    style="display:flex;align-items:center;gap:.65rem;padding:.58rem 1rem;font-size:.84rem;
                        cursor:pointer;border-bottom:1px solid rgba(255,255,255,.03);transition:background .07s;
                        ${i === activeIdx ? 'background:rgba(217,164,65,.1);' : ''}">
                    <span style="width:14px;flex-shrink:0;text-align:center;font-size:.75rem;color:var(--gold2);">${selId == o.id ? '<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" style="display:inline-block;vertical-align:middle"><polyline points="20 6 9 17 4 12"/></svg>' : ''}</span>
                    <span style="${selId == o.id ? 'color:var(--gold2);font-weight:600;' : 'color:var(--ink);'}">${hlText(o.name, q)}</span>
                </div>`
            ).join('');

            listEl.querySelectorAll('.dist-item').forEach((item, i) => {
                item.addEventListener('mouseenter', () => {
                    activeIdx = i;
                    listEl.querySelectorAll('.dist-item').forEach((e, j) => {
                        e.style.background = j === i ? 'rgba(217,164,65,.1)' : '';
                    });
                });
                item.addEventListener('mousedown', e => {
                    e.preventDefault();
                    pick(item.dataset.id, item.dataset.name);
                });
            });
        }

        if (footEl) footEl.textContent = query
            ? (filtered.length + ' hasil')
            : (getOpts().length + ' distributor');

        const activeEl = listEl.querySelectorAll('.dist-item')[activeIdx];
        if (activeEl) activeEl.scrollIntoView({ block: 'nearest' });
    }

    function pick(id, name) {
        selId = +id;
        const sel = el('dist-sel');
        if (sel) {
            sel.value = id;
            sel.dispatchEvent(new Event('input',  { bubbles: true }));
            sel.dispatchEvent(new Event('change', { bubbles: true }));
        }
        const disp = el('dist-display');
        if (disp) { disp.textContent = name; disp.style.color = 'var(--ink)'; }
        doClose();
    }

    function doOpen() {
        const dd = el('dist-dd');
        if (!dd) return;
        if (dd.parentElement !== document.body) document.body.appendChild(dd);
        activeIdx = 0;
        posDD();
        const qi = el('dist-qi');
        if (qi) { qi.value = ''; }
        const qclr = el('dist-qclr');
        if (qclr) qclr.style.display = 'none';
        renderList('');
        dd.style.display = 'block';
        dd.classList.add('obat-dd-open');
        const btn = el('dist-btn');
        if (btn) { btn.style.borderColor = 'var(--gold)'; btn.style.boxShadow = '0 0 0 3px rgba(217,164,65,.15)'; }
        const chev = el('dist-chevron');
        if (chev) { chev.style.transform = 'rotate(180deg)'; chev.style.color = 'var(--gold2)'; }
        setTimeout(() => el('dist-qi')?.focus(), 40);
    }

    function doClose() {
        const dd = el('dist-dd');
        if (dd) dd.style.display = 'none';
        const btn = el('dist-btn');
        if (btn) { btn.style.borderColor = ''; btn.style.boxShadow = ''; }
        const chev = el('dist-chevron');
        if (chev) { chev.style.transform = ''; chev.style.color = 'var(--mut)'; }
    }

    function isOpen() {
        const dd = el('dist-dd');
        return dd && dd.style.display !== 'none';
    }

    function moveActive(dir) {
        const listEl = el('dist-list');
        if (!listEl) return;
        const items = listEl.querySelectorAll('.dist-item');
        activeIdx = Math.max(0, Math.min(activeIdx + dir, items.length - 1));
        items.forEach((e, i) => { e.style.background = i === activeIdx ? 'rgba(217,164,65,.1)' : ''; });
        items[activeIdx]?.scrollIntoView({ block: 'nearest' });
    }

    function syncDisplay() {
        const sel = el('dist-sel');
        if (!sel) return;
        selId = +sel.value || 0;
        const disp = el('dist-display');
        if (!disp) return;
        if (selId) {
            const opt = Array.from(sel.options).find(o => +o.value === selId);
            disp.textContent = opt ? (opt.dataset.name || opt.text) : '— Pilih Distributor —';
            disp.style.color = opt ? 'var(--ink)' : 'var(--mut)';
        } else {
            disp.textContent = '— Pilih Distributor —';
            disp.style.color = 'var(--mut)';
        }
        /* Ensure dd stays in body after Livewire re-render */
        const dd = el('dist-dd');
        if (dd && dd.parentElement !== document.body) document.body.appendChild(dd);
    }

    return {
        toggle()   { isOpen() ? doClose() : doOpen(); },
        keyBtn(e)  {
            if (e.key === 'ArrowDown' || e.key === 'Enter') { doOpen(); e.preventDefault(); }
            else if (e.key === 'Escape') doClose();
        },
        keyDd(e) {
            if (e.key === 'ArrowDown')  { moveActive(1);  e.preventDefault(); }
            else if (e.key === 'ArrowUp')   { moveActive(-1); e.preventDefault(); }
            else if (e.key === 'Enter') {
                const items = el('dist-list')?.querySelectorAll('.dist-item');
                const item  = items?.[activeIdx];
                if (item) pick(item.dataset.id, item.dataset.name);
                e.preventDefault();
            }
            else if (e.key === 'Escape') { doClose(); el('dist-btn')?.focus(); }
        },
        filter(q) {
            activeIdx = 0;
            const qclr = el('dist-qclr');
            if (qclr) qclr.style.display = q ? 'flex' : 'none';
            renderList(q);
        },
        clearQ() {
            const qi = el('dist-qi');
            if (qi) { qi.value = ''; qi.focus(); }
            el('dist-qclr') && (el('dist-qclr').style.display = 'none');
            activeIdx = 0;
            renderList('');
        },
        syncDisplay,
        doClose,
    };
})();

(function() {
    /* ══════════════════════════════════════════
       ObatCb — world-class obat combobox engine
       ══════════════════════════════════════════ */
    const state = {}; // per-row state: { activeIdx, options, blurTimer }

    function getOpts(idx) {
        if (state[idx]?.options) return state[idx].options;
        const sel = document.getElementById('obat-sel-' + idx);
        if (!sel) return [];
        const opts = Array.from(sel.options)
            .filter(o => o.value !== '0')
            .map(o => ({ id: o.value, nama: o.dataset.nama, tipe: o.dataset.tipe, diag: o.dataset.diag || '' }));
        if (!state[idx]) state[idx] = {};
        state[idx].options = opts;
        return opts;
    }

    function getDD(idx) { return document.getElementById('obat-dd-' + idx); }
    function getTxt(idx) { return document.getElementById('obat-txt-' + idx); }
    function getClr(idx) { return document.getElementById('obat-clr-' + idx); }

    function hlText(str, q) {
        if (!q) return escHtml(str);
        const r = q.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
        return escHtml(str).replace(new RegExp(r, 'gi'), m => '<mark class="hl">' + m + '</mark>');
    }
    function escHtml(s) {
        return s.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
    }

    function posDD(idx) {
        const txt = getTxt(idx), dd = getDD(idx);
        if (!txt || !dd) return;
        const r = txt.getBoundingClientRect();
        const spaceBelow = window.innerHeight - r.bottom - 8;
        dd.style.top   = (r.bottom + 4) + 'px';
        dd.style.left  = r.left + 'px';
        dd.style.width = r.width + 'px';
        dd.style.maxHeight = Math.max(120, Math.min(280, spaceBelow)) + 'px';
    }

    function renderDD(idx, q) {
        const dd = getDD(idx);
        if (!dd) return;
        if (!state[idx]) state[idx] = {};

        const opts  = getOpts(idx);
        const query = (q || '').trim().toLowerCase();
        const matches = query
            ? opts.filter(o => o.nama.toLowerCase().includes(query) || o.diag.toLowerCase().includes(query))
            : opts;
        const shown = matches.slice(0, 60);
        const active = Math.min(state[idx].activeIdx || 0, shown.length - 1);
        state[idx].activeIdx = Math.max(0, active);

        let html = '';

        if (shown.length === 0) {
            html = '<div style="padding:1rem;text-align:center;font-size:.78rem;color:var(--mut);">Tidak ditemukan</div>';
        } else {
            // Group by tipe
            const kronik  = shown.filter(o => o.tipe !== 'non_kronis');
            const nonKron = shown.filter(o => o.tipe === 'non_kronis');
            let globalIdx = 0;

            const renderGroup = (items, label, color, border) => {
                if (!items.length) return '';
                let g = `<div style="padding:.28rem .85rem .18rem;font-size:.62rem;font-weight:700;color:${color};text-transform:uppercase;letter-spacing:.07em;border-top:${border};">${label}</div>`;
                items.forEach(opt => {
                    const isActive = globalIdx === (state[idx].activeIdx || 0);
                    g += `<div class="obat-item" data-idx="${idx}" data-id="${opt.id}" data-nama="${escHtml(opt.nama)}" data-tipe="${opt.tipe}"
                        style="padding:.52rem 1rem .52rem 1.3rem;font-size:.83rem;cursor:pointer;
                            border-bottom:1px solid rgba(255,255,255,.03);transition:background .07s;
                            ${isActive ? 'background:rgba(255,255,255,.08);' : ''}">
                        <div style="color:${opt.tipe==='non_kronis'?'#6fb1e0':'#5ce0a4'};line-height:1.3;">${hlText(opt.nama, q)}</div>
                        ${opt.diag ? `<div style="font-size:.68rem;color:var(--mut);margin-top:.1rem;line-height:1;">${escHtml(opt.diag)}</div>` : ''}
                    </div>`;
                    globalIdx++;
                });
                return g;
            };

            html += renderGroup(kronik,  'Kronis',      'var(--emer2)', 'none');
            html += renderGroup(nonKron, 'Non-Kronis',  'var(--blue)',  '1px solid rgba(255,255,255,.06)');

            if (matches.length > 60) {
                html += `<div style="padding:.35rem .85rem;font-size:.68rem;color:var(--mut);text-align:center;border-top:1px solid rgba(255,255,255,.05);">
                    +${matches.length - 60} lainnya — ketik lebih spesifik</div>`;
            }
        }

        if (query) {
            html += `<div style="padding:.28rem .85rem;font-size:.67rem;color:var(--mut);border-top:1px solid rgba(255,255,255,.05);text-align:right;">${matches.length} hasil</div>`;
        }

        dd.innerHTML = html;

        // Event delegation for items
        dd.querySelectorAll('.obat-item').forEach((el, i) => {
            el.addEventListener('mouseenter', () => {
                state[idx].activeIdx = i;
                dd.querySelectorAll('.obat-item').forEach((e, j) => {
                    e.style.background = j === i ? 'rgba(255,255,255,.08)' : '';
                });
            });
            el.addEventListener('mousedown', e => {
                e.preventDefault();
                ObatCb.pick(idx, el.dataset.id, el.dataset.nama, el.dataset.tipe);
            });
        });

        // Scroll active item into view
        const activeEl = dd.querySelectorAll('.obat-item')[state[idx].activeIdx || 0];
        if (activeEl) activeEl.scrollIntoView({ block: 'nearest' });
    }

    function openDD(idx, q) {
        const dd = getDD(idx);
        if (!dd) return;
        if (dd.parentElement !== document.body) document.body.appendChild(dd);
        renderDD(idx, q);
        posDD(idx);
        if (dd.style.display === 'none' || !dd.style.display) {
            dd.style.display = 'block';
            dd.classList.add('obat-dd-open cb-scroll');
        }
    }

    function closeDD(idx) {
        const dd = getDD(idx);
        if (dd) dd.style.display = 'none';
    }

    function moveActive(idx, dir) {
        if (!state[idx]) state[idx] = {};
        const dd  = getDD(idx);
        const items = dd ? dd.querySelectorAll('.obat-item') : [];
        const max = items.length - 1;
        let next = (state[idx].activeIdx || 0) + dir;
        next = Math.max(0, Math.min(next, max));
        state[idx].activeIdx = next;
        items.forEach((el, i) => { el.style.background = i === next ? 'rgba(255,255,255,.08)' : ''; });
        if (items[next]) items[next].scrollIntoView({ block: 'nearest' });
    }

    window.ObatCb = {
        search(idx, q) {
            if (!state[idx]) state[idx] = {};
            state[idx].activeIdx = 0;
            openDD(idx, q);
        },
        focus(idx, q) {
            if (state[idx]?.blurTimer) { clearTimeout(state[idx].blurTimer); }
            openDD(idx, q);
        },
        blur(idx) {
            if (!state[idx]) state[idx] = {};
            state[idx].blurTimer = setTimeout(() => closeDD(idx), 180);
        },
        key(e, idx) {
            const dd = getDD(idx);
            const isOpen = dd && dd.style.display !== 'none';

            if (e.key === 'ArrowDown') {
                if (!isOpen) { openDD(idx, getTxt(idx)?.value || ''); }
                else moveActive(idx, 1);
                e.preventDefault();
            } else if (e.key === 'ArrowUp') {
                if (isOpen) moveActive(idx, -1);
                e.preventDefault();
            } else if (e.key === 'Enter') {
                if (isOpen) {
                    const el = dd.querySelectorAll('.obat-item')[state[idx]?.activeIdx || 0];
                    if (el) ObatCb.pick(idx, el.dataset.id, el.dataset.nama, el.dataset.tipe);
                    e.preventDefault();
                }
            } else if (e.key === 'Escape') {
                closeDD(idx);
                e.preventDefault();
            }
        },
        pick(idx, id, nama, tipe) {
            // Update hidden select -> Livewire
            const sel = document.getElementById('obat-sel-' + idx);
            if (sel) {
                sel.value = id;
                sel.dispatchEvent(new Event('input',  { bubbles: true }));
                sel.dispatchEvent(new Event('change', { bubbles: true }));
            }
            // Update visible input
            const txt = getTxt(idx);
            if (txt) txt.value = nama;
            // Show/hide clear button
            const clr = getClr(idx);
            if (clr) clr.style.display = id ? 'flex' : 'none';
            // Close dropdown
            closeDD(idx);
            // Invalidate cached options (Livewire may re-render)
            if (state[idx]) state[idx].options = null;
        },
        clear(idx) {
            ObatCb.pick(idx, '0', '', '');
            const txt = getTxt(idx);
            if (txt) { txt.value = ''; txt.focus(); }
            openDD(idx, '');
        }
    };

    // Reposition open dropdowns on scroll/resize
    function reposAll() {
        document.querySelectorAll('[id^="obat-dd-"]').forEach(dd => {
            if (dd.style.display !== 'none') {
                const idx = dd.id.replace('obat-dd-', '');
                posDD(idx);
            }
        });
        // Reposition distributor dd too
        const distDd = document.getElementById('dist-dd');
        if (distDd && distDd.style.display !== 'none') DistCb.syncDisplay && window.DistCb._posDD?.();
    }
    window.addEventListener('scroll', e => {
        reposAll();
        // Also close dist dd on scroll outside
        const distDd = document.getElementById('dist-dd');
        if (distDd && distDd.style.display !== 'none' &&
            !e.target.closest?.('#dist-wrap') && !e.target.closest?.('#dist-dd')) {
            DistCb.doClose();
        }
    }, true);
    window.addEventListener('resize', reposAll);

    // Close all on outside click
    document.addEventListener('click', e => {
        // Obat dropdowns
        if (!e.target.closest('[id^="obat-wrap-"]') && !e.target.closest('[id^="obat-dd-"]')) {
            document.querySelectorAll('[id^="obat-dd-"]').forEach(dd => dd.style.display = 'none');
        }
        // Distributor dropdown
        if (!e.target.closest('#dist-wrap') && !e.target.closest('#dist-dd')) {
            DistCb.doClose();
        }
    });

    // After Livewire re-render: sync distributor display + reset obat rows
    document.addEventListener('livewire:update', () => {
        // Sync distributor display text
        DistCb.syncDisplay();
        // Obat rows
        document.querySelectorAll('[id^="obat-sel-"]').forEach(sel => {
            const idx = sel.id.replace('obat-sel-', '');
            if (state[idx]) state[idx].options = null; // bust cache
            const txt = getTxt(idx);
            if (txt && sel.value === '0') txt.value = '';
            const clr = getClr(idx);
            if (clr) clr.style.display = (sel.value && sel.value !== '0') ? 'flex' : 'none';
        });
    });

    // Init distributor display on first load
    document.addEventListener('livewire:initialized', () => DistCb.syncDisplay());
    if (document.readyState !== 'loading') DistCb.syncDisplay();
    else document.addEventListener('DOMContentLoaded', () => DistCb.syncDisplay());
})();
</script>
</div>
