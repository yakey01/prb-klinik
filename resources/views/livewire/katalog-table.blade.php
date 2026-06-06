<div>
    {{-- ─────────────── HEADER ─────────────── --}}
    <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:1.2rem; flex-wrap:wrap; gap:.75rem;">
        <div style="display:flex; gap:.5rem; align-items:center; flex-wrap:wrap;">
            <div style="position:relative; min-width:200px; max-width:280px;">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"
                     style="position:absolute;left:.75rem;top:50%;transform:translateY(-50%);color:var(--mut);">
                    <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
                </svg>
                <input wire:model.live.debounce.300ms="search" type="text" placeholder="Cari nama / kode obat…"
                       class="form-input" style="padding-left:2.2rem;">
            </div>
            <label style="display:flex;align-items:center;gap:.4rem;font-size:.75rem;color:var(--mut);cursor:pointer;white-space:nowrap;">
                <input wire:model.live="showInactive" type="checkbox"
                       style="accent-color:var(--gold);width:13px;height:13px;cursor:pointer;">
                Tampilkan nonaktif
            </label>
        </div>
        <div style="display:flex;gap:.5rem;">
            <button wire:click="$set('showImport',!$showImport)" class="btn-outline" style="font-size:.8rem;">
                <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                Import CSV
            </button>
            <button wire:click="openAdd" class="btn-gold">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                Tambah Obat
            </button>
        </div>
    </div>

    {{-- ─────────────── CSV IMPORT PANEL ─────────────── --}}
    @if($showImport)
    <div class="glass-card" style="padding:1.25rem;margin-bottom:1.25rem;border-color:var(--blue);">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:.85rem;">
            <div style="font-size:.9rem;font-weight:600;color:var(--blue);">Import Obat dari CSV</div>
            <button wire:click="$set('showImport',false)" style="background:none;border:none;color:var(--mut);cursor:pointer;font-size:1.1rem;">✕</button>
        </div>
        <div style="font-size:.75rem;color:var(--mut);margin-bottom:.75rem;">
            Kolom CSV: <span class="font-mono" style="color:var(--gold2);font-size:.7rem;">nama_obat, kategori_diagnosis, kode_obat, jumlah_pasien, unit_per_bulan, harga_beli_per_unit, sumber_harga, klaim_bpjs_per_unit, faktor_jasa_farmasi</span>
        </div>
        <form wire:submit="importCsv" style="display:flex;gap:.75rem;align-items:flex-end;flex-wrap:wrap;">
            <div style="flex:1;min-width:220px;">
                <label class="form-label">Pilih File CSV</label>
                <input wire:model="csvFile" type="file" accept=".csv,.txt" class="form-input" style="padding:.45rem .7rem;">
                @error('csvFile')<div style="color:var(--red);font-size:.7rem;margin-top:.2rem;">{{ $message }}</div>@enderror
            </div>
            <button type="submit" class="btn-gold" wire:loading.attr="disabled">
                <span wire:loading.remove wire:target="importCsv">Upload & Import</span>
                <span wire:loading wire:target="importCsv">Memproses...</span>
            </button>
        </form>
    </div>
    @endif

    {{-- ─────────────── FILTER PILLS ─────────────── --}}
    <div style="display:flex; gap:.4rem; flex-wrap:wrap; margin-bottom:1.2rem;">
        @foreach(['semua'=>'Semua','laba'=>'Laba','rugi'=>'Rugi','perlu_cek'=>'Perlu Cek'] as $val => $lbl)
        <button wire:click="$set('filter','{{ $val }}')"
            style="padding:.3rem .8rem;border-radius:999px;font-size:.73rem;cursor:pointer;border:1px solid;transition:all .2s;
                {{ $filter===$val ? 'background:var(--gold);border-color:var(--gold);color:#1a0e00;font-weight:700;' : 'background:transparent;border-color:var(--line2);color:var(--mut);' }}">
            {{ $lbl }}
        </button>
        @endforeach
        @foreach($this->kategoriList as $diag)
        <button wire:click="$set('filter','{{ $diag }}')"
            style="padding:.3rem .8rem;border-radius:999px;font-size:.7rem;cursor:pointer;border:1px solid;transition:all .2s;
                {{ $filter===$diag ? 'background:rgba(111,177,224,.2);border-color:var(--blue);color:var(--blue);font-weight:600;' : 'background:transparent;border-color:var(--line);color:var(--mut2);' }}">
            {{ $diag }}
        </button>
        @endforeach
    </div>

    {{-- ─────────────── CRUD FORM ─────────────── --}}
    @if($showForm)
    @php
        $prev_bayar      = $klaim_bpjs_per_unit * $faktor_jasa_farmasi;
        $prev_pendapatan = $prev_bayar * $unit_per_bulan;
        $prev_biaya      = $harga_beli_per_unit * $unit_per_bulan;
        $prev_laba       = $prev_pendapatan - $prev_biaya;
    @endphp
    <div class="glass-card" style="padding:1.5rem; margin-bottom:1.5rem; border-color:var(--gold);">
        <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:1.2rem;">
            <div class="font-heading" style="font-size:1.05rem; color:var(--gold2);">
                {{ $editId ? '✏️ Edit Obat' : '+ Tambah Obat Baru' }}
            </div>
            <button wire:click="cancel" style="background:none;border:none;color:var(--mut);cursor:pointer;font-size:1.2rem;line-height:1;">✕</button>
        </div>

        <form wire:submit="save">
            {{-- Row 1: Identitas --}}
            <div style="display:grid; grid-template-columns:2fr 1fr 1fr; gap:.85rem; margin-bottom:.85rem;">
                <div>
                    <label class="form-label">Nama Obat *</label>
                    <input wire:model="nama_obat" type="text" placeholder="misal: Metformin 500mg" class="form-input">
                    @error('nama_obat')<div style="color:var(--red);font-size:.7rem;margin-top:.2rem;">{{ $message }}</div>@enderror
                </div>
                <div>
                    <label class="form-label">Kode Obat</label>
                    <input wire:model="kode_obat" type="text" placeholder="Opsional" class="form-input">
                </div>
                <div>
                    <label class="form-label">Kategori Diagnosis</label>
                    <select wire:model="kategori_diagnosis" class="form-input" style="appearance:auto;-webkit-appearance:auto;">
                        <option value="">— Pilih Kategori (opsional) —</option>
                        @foreach($this->kategoriList as $k)
                        <option value="{{ $k }}">{{ $k }}</option>
                        @endforeach
                    </select>
                    @error('kategori_diagnosis')<div style="color:var(--red);font-size:.7rem;margin-top:.2rem;">{{ $message }}</div>@enderror
                </div>
            </div>

            {{-- Row 2: Klaim BPJS --}}
            <div style="display:grid; grid-template-columns:repeat(3,1fr) auto; gap:.85rem; margin-bottom:1rem; align-items:end;">
                <div>
                    <label class="form-label">Klaim BPJS / Unit (Rp) *
                        <span style="font-weight:400;color:var(--mut2);text-transform:none;letter-spacing:0;"> — KMK 730/2025</span>
                    </label>
                    <input wire:model.live.debounce.400ms="klaim_bpjs_per_unit" type="number" min="0" step="100" class="form-input font-mono">
                    @error('klaim_bpjs_per_unit')<div style="color:var(--red);font-size:.7rem;margin-top:.2rem;">{{ $message }}</div>@enderror
                </div>
                <div>
                    <label class="form-label">Faktor Jasa Farmasi *
                        <span style="font-weight:400;color:var(--mut2);text-transform:none;letter-spacing:0;"> — PMK 3/2023</span>
                    </label>
                    <input wire:model.live.debounce.400ms="faktor_jasa_farmasi" type="number" min="0.01" max="9.99" step="0.01" class="form-input font-mono">
                    @error('faktor_jasa_farmasi')<div style="color:var(--red);font-size:.7rem;margin-top:.2rem;">{{ $message }}</div>@enderror
                </div>
                <div>
                    <label class="form-label">Status Obat</label>
                    <label style="display:flex;align-items:center;gap:.6rem;padding:.65rem .9rem;background:var(--panel);border:1px solid var(--line);border-radius:.5rem;cursor:pointer;">
                        <input wire:model="is_active" type="checkbox" style="accent-color:var(--emer);width:16px;height:16px;cursor:pointer;">
                        <span style="font-size:.875rem; color:{{ $is_active ? 'var(--emer2)' : 'var(--mut)' }};">
                            {{ $is_active ? 'Aktif' : 'Nonaktif' }}
                        </span>
                    </label>
                </div>
                <div style="display:flex;gap:.5rem;">
                    <button type="submit" class="btn-gold">
                        <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h11l5 5v11a2 2 0 01-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/></svg>
                        {{ $editId ? 'Simpan Perubahan' : 'Tambah Obat' }}
                    </button>
                    <button type="button" wire:click="cancel" class="btn-outline">Batal</button>
                </div>
            </div>

            {{-- Live Preview --}}
            <div style="background:rgba(63,207,142,.06);border:1px solid rgba(63,207,142,.15);border-radius:.6rem;padding:.75rem 1rem;display:grid;grid-template-columns:repeat(4,1fr);gap:.5rem;">
                <div>
                    <div style="font-size:.65rem;color:var(--mut);text-transform:uppercase;letter-spacing:.06em;margin-bottom:.2rem;">Bayar BPJS/unit</div>
                    <div class="font-mono" style="font-size:.82rem;color:var(--blue);">Rp {{ number_format($prev_bayar,0,',','.') }}</div>
                </div>
                <div>
                    <div style="font-size:.65rem;color:var(--mut);text-transform:uppercase;letter-spacing:.06em;margin-bottom:.2rem;">Pendapatan/Bln</div>
                    <div class="font-mono" style="font-size:.82rem;color:var(--emer2);">Rp {{ number_format($prev_pendapatan,0,',','.') }}</div>
                </div>
                <div>
                    <div style="font-size:.65rem;color:var(--mut);text-transform:uppercase;letter-spacing:.06em;margin-bottom:.2rem;">Biaya Beli/Bln</div>
                    <div class="font-mono" style="font-size:.82rem;color:var(--red2);">Rp {{ number_format($prev_biaya,0,',','.') }}</div>
                </div>
                <div>
                    <div style="font-size:.65rem;color:var(--mut);text-transform:uppercase;letter-spacing:.06em;margin-bottom:.2rem;">Estimasi Laba/Bln</div>
                    <div class="font-mono" style="font-size:.88rem;font-weight:700;color:{{ $prev_laba >= 0 ? 'var(--emer2)' : 'var(--red2)' }};">
                        {{ $prev_laba >= 0 ? '+' : '' }}Rp {{ number_format($prev_laba,0,',','.') }}
                    </div>
                </div>
            </div>
        </form>
    </div>
    @endif

    {{-- ─────────────── TABLE ─────────────── --}}
    <div class="glass-card" style="overflow-x:auto;">
        <table class="data-table">
            <thead>
                <tr>
                    <th wire:click="sortBy('nama_obat')" style="min-width:160px;">
                        Obat {{ $sortBy==='nama_obat' ? ($sortDir==='asc' ? '↑' : '↓') : '' }}
                    </th>
                    <th wire:click="sortBy('kategori_diagnosis')" style="min-width:100px;">
                        Diagnosis {{ $sortBy==='kategori_diagnosis' ? ($sortDir==='asc' ? '↑' : '↓') : '' }}
                    </th>
                    <th wire:click="sortBy('jumlah_pasien')" style="text-align:right;">
                        Pasien {{ $sortBy==='jumlah_pasien' ? ($sortDir==='asc' ? '↑' : '↓') : '' }}
                    </th>
                    <th style="text-align:right;">Unit/Bln</th>
                    <th style="text-align:right; min-width:90px;">Beli/Unit ✎</th>
                    <th style="text-align:center;">Sumber</th>
                    <th style="text-align:right; min-width:90px;">Klaim BPJS ✎</th>
                    <th style="text-align:right;">Bayar BPJS</th>
                    <th style="text-align:right;">Pend/Bln</th>
                    <th style="text-align:right;">Laba/Bln</th>
                    <th style="text-align:center;">Status</th>
                    <th style="text-align:center; min-width:100px;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($this->obatList as $obat)
                <tr style="{{ !$obat->is_active ? 'opacity:.45;' : '' }}">
                    <td>
                        <div style="font-weight:500;line-height:1.3;">{{ $obat->nama_obat }}</div>
                        @if($obat->kode_obat)
                        <div style="font-size:.68rem;color:var(--mut2);font-family:'JetBrains Mono',monospace;">{{ $obat->kode_obat }}</div>
                        @endif
                    </td>
                    <td style="color:var(--mut);font-size:.77rem;">{{ $obat->kategori_diagnosis ?? '—' }}</td>

                    <td style="text-align:right;">
                        @if($obat->dari_resep)
                        <div style="display:flex;align-items:center;gap:.3rem;justify-content:flex-end;">
                            <span title="Data real dari resep aktif pasien" style="color:var(--emer);font-size:.6rem;line-height:1;">●</span>
                            <span class="font-mono" style="font-size:.82rem;color:var(--emer2);font-weight:600;">{{ $obat->jumlah_pasien }}</span>
                        </div>
                        @else
                        <input type="number" value="{{ $obat->jumlah_pasien }}" min="0"
                            wire:change="updatePasien({{ $obat->id }}, $event.target.value)"
                            class="font-mono"
                            title="Input manual — belum ada resep aktif"
                            style="width:58px;text-align:right;background:rgba(217,164,65,.08);border:1px solid rgba(217,164,65,.2);border-radius:.3rem;padding:.18rem .35rem;font-size:.8rem;color:var(--gold2);">
                        @endif
                    </td>
                    <td style="text-align:right;">
                        @if($obat->dari_resep)
                        <div style="display:flex;align-items:center;gap:.3rem;justify-content:flex-end;">
                            <span title="Data real dari resep aktif pasien" style="color:var(--emer);font-size:.6rem;line-height:1;">●</span>
                            <span class="font-mono" style="font-size:.82rem;color:var(--emer2);font-weight:600;">{{ number_format($obat->unit_per_bulan, 0) }}</span>
                        </div>
                        @else
                        <input type="number" value="{{ $obat->unit_per_bulan }}" min="0" step="10"
                            wire:change="updateUnit({{ $obat->id }}, $event.target.value)"
                            class="font-mono"
                            title="Input manual — belum ada resep aktif"
                            style="width:68px;text-align:right;background:rgba(217,164,65,.08);border:1px solid rgba(217,164,65,.2);border-radius:.3rem;padding:.18rem .35rem;font-size:.8rem;color:var(--gold2);">
                        @endif
                    </td>
                    <td style="text-align:right;">
                        <input type="number" value="{{ $obat->harga_beli_per_unit }}" min="0" step="100"
                            wire:change="updateHarga({{ $obat->id }}, $event.target.value)"
                            class="font-mono"
                            style="width:90px;text-align:right;background:rgba(232,100,90,.07);border:1px solid rgba(232,100,90,.2);border-radius:.3rem;padding:.18rem .35rem;font-size:.8rem;color:var(--red2);">
                    </td>
                    <td style="text-align:center;">
                        @php $src = $obat->sumber_harga; @endphp
                        <span class="badge badge-{{ $src==='PO'?'po':($src==='REAL'?'real':'est') }}">{{ $src }}</span>
                    </td>
                    <td style="text-align:right;">
                        <input type="number" value="{{ $obat->klaim_bpjs_per_unit }}" min="0" step="100"
                            wire:change="updateKlaim({{ $obat->id }}, $event.target.value)"
                            class="font-mono"
                            style="width:90px;text-align:right;background:rgba(111,177,224,.08);border:1px solid rgba(111,177,224,.2);border-radius:.3rem;padding:.18rem .35rem;font-size:.8rem;color:var(--blue);">
                    </td>
                    <td class="font-mono" style="text-align:right;font-size:.8rem;color:var(--blue);">{{ number_format($obat->bayar_bpjs,0,',','.') }}</td>
                    <td class="font-mono" style="text-align:right;font-size:.8rem;">{{ number_format($obat->pendapatan_bulan,0,',','.') }}</td>
                    <td class="font-mono" style="text-align:right;font-size:.85rem;font-weight:700;color:{{ $obat->laba>=0?'var(--emer2)':'var(--red2)' }};">
                        {{ $obat->laba>=0?'+':'' }}{{ number_format($obat->laba,0,',','.') }}
                    </td>
                    <td style="text-align:center;">
                        @php $st=$obat->status_laba; @endphp
                        <span class="badge badge-{{ $st==='Laba'?'laba':($st==='Rugi'?'rugi':'cek') }}">{{ $st }}</span>
                    </td>
                    <td style="text-align:center;">
                        <div style="display:flex;gap:.3rem;justify-content:center;">
                            <button wire:click="openEdit({{ $obat->id }})" title="Edit"
                                style="background:rgba(217,164,65,.1);border:1px solid rgba(217,164,65,.25);color:var(--gold2);border-radius:.35rem;padding:.25rem .5rem;cursor:pointer;font-size:.75rem;">
                                <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4z"/></svg>
                            </button>
                            <button wire:click="toggleActive({{ $obat->id }})"
                                style="background:{{ $obat->is_active?'rgba(232,100,90,.1)':'rgba(63,207,142,.1)' }};border:1px solid {{ $obat->is_active?'rgba(232,100,90,.25)':'rgba(63,207,142,.25)' }};color:{{ $obat->is_active?'var(--red2)':'var(--emer2)' }};border-radius:.35rem;padding:.25rem .5rem;cursor:pointer;font-size:.7rem;"
                                wire:confirm="{{ $obat->is_active?'Nonaktifkan':'Aktifkan' }} obat ini?">
                                {{ $obat->is_active ? 'Nonaktif' : 'Aktifkan' }}
                            </button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="12" style="text-align:center;padding:2rem;color:var(--mut);">Tidak ada data obat.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div style="margin-top:.75rem;font-size:.75rem;color:var(--mut2);display:flex;gap:1rem;flex-wrap:wrap;">
        <span>Menampilkan <strong style="color:var(--gold3);">{{ $this->obatList->count() }}</strong> obat</span>
        <span>·</span>
        <span style="color:var(--mut);">
            <span style="color:var(--emer);font-size:.65rem;">●</span> Pasien &amp; Unit/Bln otomatis dari resep aktif
            &nbsp;·&nbsp; ✎ Beli/Unit &amp; Klaim BPJS dapat diedit langsung
        </span>
    </div>
</div>
