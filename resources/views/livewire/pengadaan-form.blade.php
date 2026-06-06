<div>
    <form wire:submit="save">

        {{-- HEADER INFO --}}
        <div class="glass-card" style="padding:1.5rem; margin-bottom:1.5rem;">
            <div class="font-heading" style="font-size:1.05rem; color:var(--ink); margin-bottom:1.2rem;">Informasi Purchase Order</div>
            <div style="display:grid; grid-template-columns:repeat(auto-fit,minmax(220px,1fr)); gap:1rem;">
                <div>
                    <label class="form-label">Distributor / PBF *</label>
                    <select wire:model="distributor_id" class="form-input" style="appearance:auto;-webkit-appearance:auto;">
                        <option value="0">-- Pilih Distributor --</option>
                        @foreach($this->distributors as $dist)
                        <option value="{{ $dist->id }}">{{ $dist->name }}</option>
                        @endforeach
                    </select>
                    @error('distributor_id') <span style="color:var(--red);font-size:.75rem;">{{ $message }}</span> @enderror
                </div>
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
        {{-- overflow:visible agar dropdown tidak terpotong; scroll hanya pada tabel --}}
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
                    @php
                        $obatMap = $this->obatList->keyBy('id');
                    @endphp
                    @foreach($rows as $i => $row)
                    @php
                        $isKronis      = ($row['tipe_obat'] ?? 'kronis') === 'kronis';
                        $selectedNama = $row['obat_id'] ? ($obatMap[$row['obat_id']]->nama_obat ?? '') : '';
                    @endphp
                    <tr style="border-left:3px solid {{ $isKronis ? 'rgba(63,207,142,.4)' : 'rgba(111,177,224,.4)' }};">
                        <td style="padding:.5rem .75rem;">
                            <div style="display:flex;align-items:center;gap:.5rem;">
                                {{-- BPJS / UMUM toggle --}}
                                <div style="display:flex;border-radius:.5rem;overflow:hidden;border:1px solid var(--line2);flex-shrink:0;">
                                    <button type="button"
                                        wire:click="$set('rows.{{ $i }}.tipe_obat','kronis')"
                                        style="padding:.45rem .85rem;font-size:.75rem;font-weight:700;cursor:pointer;border:none;line-height:1;transition:all .15s;letter-spacing:.03em;
                                            {{ $isKronis ? 'background:rgba(63,207,142,.22);color:var(--emer2);' : 'background:transparent;color:var(--mut);' }}">
                                        BPJS
                                    </button>
                                    <button type="button"
                                        wire:click="$set('rows.{{ $i }}.tipe_obat','non_kronis')"
                                        style="padding:.45rem .85rem;font-size:.75rem;font-weight:700;cursor:pointer;border:none;border-left:1px solid var(--line2);line-height:1;transition:all .15s;letter-spacing:.03em;
                                            {{ !$isKronis ? 'background:rgba(111,177,224,.22);color:var(--blue);' : 'background:transparent;color:var(--mut);' }}">
                                        UMUM
                                    </button>
                                </div>
                                {{-- Searchable obat picker --}}
                                <div style="position:relative;flex:1;" id="obat-wrap-{{ $i }}">
                                    <input
                                        type="text"
                                        id="obat-txt-{{ $i }}"
                                        autocomplete="off"
                                        placeholder="Ketik nama obat..."
                                        value="{{ $selectedNama }}"
                                        oninput="obatSearch({{ $i }},this.value)"
                                        onfocus="obatSearch({{ $i }},this.value)"
                                        class="form-input"
                                        style="font-size:.82rem;padding-right:1.8rem;cursor:text;">
                                    <svg style="position:absolute;right:.55rem;top:50%;transform:translateY(-50%);pointer-events:none;color:var(--mut);" width="11" height="11" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="6 9 12 15 18 9"/></svg>
                                    {{-- Hidden select for Livewire binding --}}
                                    <select wire:model.live="rows.{{ $i }}.obat_id"
                                        id="obat-sel-{{ $i }}"
                                        style="display:none;">
                                        <option value="0">--</option>
                                        @foreach($this->obatList as $obat)
                                        <option value="{{ $obat->id }}"
                                            data-nama="{{ $obat->nama_obat }}{{ $obat->tipe_obat==='non_kronis' ? ' [Umum]' : '' }}"
                                            data-tipe="{{ $obat->tipe_obat }}">
                                            {{ $obat->nama_obat }}
                                        </option>
                                        @endforeach
                                    </select>
                                    {{-- Dropdown list — position:fixed agar tidak terpotong overflow container --}}
                                    <div id="obat-dd-{{ $i }}"
                                        style="display:none;position:fixed;background:#0e1e17;border:1px solid var(--line2);border-radius:.6rem;z-index:9999;max-height:240px;overflow-y:auto;box-shadow:0 12px 36px rgba(0,0,0,.75);">
                                    </div>
                                </div>
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
            </div>{{-- end overflow-x:auto wrapper --}}
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
                            <span style="width:.5rem;height:.5rem;border-radius:50%;background:var(--emer2);display:inline-block;"></span>
                            Obat Kronis
                        </span>
                        <span class="font-mono" style="font-size:.8rem;color:var(--emer2);font-weight:600;">Rp {{ number_format($this->subtotalKronis,0,',','.') }}</span>
                    </div>
                    @endif
                    @if($this->subtotalNonKronis > 0)
                    <div style="display:flex;align-items:center;justify-content:space-between;gap:1.5rem;">
                        <span style="font-size:.75rem;color:var(--blue);display:flex;align-items:center;gap:.3rem;">
                            <span style="width:.5rem;height:.5rem;border-radius:50%;background:var(--blue);display:inline-block;"></span>
                            Obat Non-Kronis
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
                <button type="submit" class="btn-gold">
                    <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h11l5 5v11a2 2 0 01-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/></svg>
                    Simpan PO
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
            <a href="{{ route('riwayat.index') }}" style="font-size:.78rem;color:var(--gold2);text-decoration:none;">
                Lihat semua →
            </a>
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
                    @php
                        $hasBpjs = $po->items->contains('tipe_obat','kronis');
                        $hasUmum = $po->items->contains('tipe_obat','non_kronis');
                    @endphp
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
                                    {{ $isKronis ? 'BPJS' : 'UMUM' }}
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

        {{-- Pagination --}}
        @if($this->recentOrders->hasPages())
        <div style="margin-top:1rem;display:flex;justify-content:center;gap:.4rem;flex-wrap:wrap;">
            @if($this->recentOrders->onFirstPage())
            <span style="padding:.35rem .7rem;border-radius:.4rem;font-size:.78rem;color:var(--mut);border:1px solid var(--line);opacity:.4;">← Prev</span>
            @else
            <button wire:click="previousPage" style="padding:.35rem .7rem;border-radius:.4rem;font-size:.78rem;color:var(--gold2);border:1px solid rgba(217,164,65,.3);background:transparent;cursor:pointer;">← Prev</button>
            @endif

            <span style="padding:.35rem .75rem;font-size:.78rem;color:var(--mut);">
                Hal {{ $this->recentOrders->currentPage() }} / {{ $this->recentOrders->lastPage() }}
                &nbsp;·&nbsp; {{ $this->recentOrders->total() }} PO
            </span>

            @if($this->recentOrders->hasMorePages())
            <button wire:click="nextPage" style="padding:.35rem .7rem;border-radius:.4rem;font-size:.78rem;color:var(--gold2);border:1px solid rgba(217,164,65,.3);background:transparent;cursor:pointer;">Next →</button>
            @else
            <span style="padding:.35rem .7rem;border-radius:.4rem;font-size:.78rem;color:var(--mut);border:1px solid var(--line);opacity:.4;">Next →</span>
            @endif
        </div>
        @endif
    </div>
</div>

<script>
(function() {
    function getObatOptions(rowIdx) {
        const sel = document.getElementById('obat-sel-' + rowIdx);
        if (!sel) return [];
        return Array.from(sel.options)
            .filter(o => o.value !== '0')
            .map(o => ({ id: o.value, nama: o.dataset.nama, tipe: o.dataset.tipe }));
    }

    // Posisikan dropdown tepat di bawah input — selalu ke bawah, max-height menyesuaikan
    function positionDropdown(rowIdx) {
        const txt = document.getElementById('obat-txt-' + rowIdx);
        const dd  = document.getElementById('obat-dd-' + rowIdx);
        if (!txt || !dd) return;
        const rect       = txt.getBoundingClientRect();
        const spaceBelow = window.innerHeight - rect.bottom - 8;
        dd.style.top       = (rect.bottom + 4) + 'px';
        dd.style.left      = rect.left + 'px';
        dd.style.width     = rect.width + 'px';
        dd.style.maxHeight = Math.max(120, Math.min(280, spaceBelow)) + 'px';
    }

    window.obatSearch = function(rowIdx, query) {
        const dd    = document.getElementById('obat-dd-' + rowIdx);
        const txt   = document.getElementById('obat-txt-' + rowIdx);
        if (!dd || !txt) return;

        const q       = query.trim().toLowerCase();
        const options = getObatOptions(rowIdx);
        const matches = q ? options.filter(o => o.nama.toLowerCase().includes(q)) : options;
        const shown   = matches.slice(0, 50);

        dd.innerHTML = '';

        if (shown.length === 0) {
            dd.innerHTML = '<div style="padding:.75rem 1rem;font-size:.77rem;color:#8aa;text-align:center;">Tidak ditemukan</div>';
        } else {
            shown.forEach(opt => {
                const div = document.createElement('div');
                div.style.cssText = `padding:.55rem 1rem;font-size:.83rem;cursor:pointer;
                    color:${opt.tipe === 'non_kronis' ? '#6fb1e0' : '#5ce0a4'};
                    border-bottom:1px solid rgba(31,61,48,.4);transition:background .1s;`;
                div.textContent = opt.nama;
                div.onmouseenter = () => div.style.background = 'rgba(255,255,255,.06)';
                div.onmouseleave = () => div.style.background = '';
                div.onmousedown  = (e) => { e.preventDefault(); selectObat(rowIdx, opt.id, opt.nama, opt.tipe); };
                dd.appendChild(div);
            });
            if (matches.length > 50) {
                const hint = document.createElement('div');
                hint.style.cssText = 'padding:.45rem 1rem;font-size:.7rem;color:#668;text-align:center;';
                hint.textContent = `+${matches.length - 50} lainnya — ketik lebih spesifik`;
                dd.appendChild(hint);
            }
        }

        // Pindahkan ke body agar tidak terpotong oleh overflow container manapun
        if (dd.parentElement !== document.body) {
            document.body.appendChild(dd);
        }
        positionDropdown(rowIdx);
        dd.style.display = 'block';
    };

    window.selectObat = function(rowIdx, id, nama, tipe) {
        const sel = document.getElementById('obat-sel-' + rowIdx);
        if (sel) {
            sel.value = id;
            sel.dispatchEvent(new Event('input',  { bubbles: true }));
            sel.dispatchEvent(new Event('change', { bubbles: true }));
        }
        const txt = document.getElementById('obat-txt-' + rowIdx);
        if (txt) txt.value = nama;
        const dd = document.getElementById('obat-dd-' + rowIdx);
        if (dd) dd.style.display = 'none';
    };

    // Repositioning saat scroll/resize agar dropdown ikut input
    function repositionAllOpen() {
        document.querySelectorAll('[id^="obat-dd-"]').forEach(dd => {
            if (dd.style.display !== 'none') {
                const idx = dd.id.replace('obat-dd-', '');
                positionDropdown(idx);
            }
        });
    }
    window.addEventListener('scroll', repositionAllOpen, true);
    window.addEventListener('resize', repositionAllOpen);

    // Tutup dropdown klik di luar
    document.addEventListener('click', function(e) {
        const inWrap = e.target.closest('[id^="obat-wrap-"]');
        const inDd   = e.target.closest('[id^="obat-dd-"]');
        if (!inWrap && !inDd) {
            document.querySelectorAll('[id^="obat-dd-"]').forEach(dd => dd.style.display = 'none');
        }
    });

    // Setelah Livewire re-render: kosongkan text baris baru, pindahkan dropdown ke body lagi
    document.addEventListener('livewire:update', function() {
        document.querySelectorAll('[id^="obat-sel-"]').forEach(sel => {
            const idx = sel.id.replace('obat-sel-', '');
            const txt = document.getElementById('obat-txt-' + idx);
            if (txt && sel.value === '0') txt.value = '';
        });
    });
})();
</script>
