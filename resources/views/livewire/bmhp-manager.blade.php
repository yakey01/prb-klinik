<div>
    {{-- ── HEADER ───────────────────────────────────────────────────────────── --}}
    <div style="display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:1.4rem;flex-wrap:wrap;gap:1rem;">
        <div>
            <div style="display:flex;align-items:center;gap:.6rem;margin-bottom:.3rem;">
                <h2 class="font-heading" style="font-size:1.15rem;color:var(--ink);margin:0;">Manajemen BMHP</h2>
                <span style="background:rgba(45,212,191,.12);border:1px solid rgba(45,212,191,.28);color:#2dd4bf;font-size:.62rem;font-weight:700;padding:.15rem .5rem;border-radius:2rem;letter-spacing:.06em;text-transform:uppercase;">Bahan Medis Habis Pakai</span>
            </div>
            <p style="font-size:.73rem;color:var(--mut);margin:0;">Stok spuit, handscoon, kasa, infus set, dll — terhubung ke SIM/RME (bridge live).</p>
        </div>
        <div style="display:flex;gap:.6rem;align-items:center;flex-wrap:wrap;">
            <input wire:model.live.debounce.300ms="search" type="text" placeholder="Cari BMHP..." class="form-input" style="max-width:200px;font-size:.8rem;">
            <button wire:click="openAdd" class="btn-gold" style="white-space:nowrap;">+ Tambah BMHP</button>
        </div>
    </div>

    {{-- ── KPI ──────────────────────────────────────────────────────────────── --}}
    @php $s = $this->summary; @endphp
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(155px,1fr));gap:.75rem;margin-bottom:1.4rem;">
        <div style="background:linear-gradient(135deg,rgba(45,212,191,.09),rgba(45,212,191,.03));border:1px solid rgba(45,212,191,.2);border-radius:.85rem;padding:1rem 1.1rem;">
            <div style="font-size:.6rem;color:var(--mut);text-transform:uppercase;letter-spacing:.08em;margin-bottom:.4rem;font-weight:600;">Jenis BMHP</div>
            <div class="font-mono" style="font-size:1.1rem;font-weight:700;color:#2dd4bf;">{{ number_format($s['jenis'],0,',','.') }}</div>
            <div style="font-size:.65rem;color:var(--mut);margin-top:.2rem;">item aktif</div>
        </div>
        <div style="background:linear-gradient(135deg,rgba(217,164,65,.08),rgba(217,164,65,.03));border:1px solid rgba(217,164,65,.18);border-radius:.85rem;padding:1rem 1.1rem;">
            <div style="font-size:.6rem;color:var(--mut);text-transform:uppercase;letter-spacing:.08em;margin-bottom:.4rem;font-weight:600;">Stok Kritis</div>
            <div class="font-mono" style="font-size:1.1rem;font-weight:700;color:var(--gold2);">{{ number_format($s['kritis'],0,',','.') }}</div>
            <div style="font-size:.65rem;color:var(--mut);margin-top:.2rem;">≤ stok minimum</div>
        </div>
        <div style="background:linear-gradient(135deg,rgba(232,100,90,.08),rgba(232,100,90,.02));border:1px solid rgba(232,100,90,.16);border-radius:.85rem;padding:1rem 1.1rem;">
            <div style="font-size:.6rem;color:var(--mut);text-transform:uppercase;letter-spacing:.08em;margin-bottom:.4rem;font-weight:600;">Stok Habis</div>
            <div class="font-mono" style="font-size:1.1rem;font-weight:700;color:var(--red2);">{{ number_format($s['habis'],0,',','.') }}</div>
            <div style="font-size:.65rem;color:var(--mut);margin-top:.2rem;">perlu pengadaan</div>
        </div>
        <div style="background:linear-gradient(135deg,rgba(63,207,142,.08),rgba(63,207,142,.03));border:1px solid rgba(63,207,142,.18);border-radius:.85rem;padding:1rem 1.1rem;">
            <div style="font-size:.6rem;color:var(--mut);text-transform:uppercase;letter-spacing:.08em;margin-bottom:.4rem;font-weight:600;">Nilai Inventori</div>
            <div class="font-mono" style="font-size:1rem;font-weight:700;color:var(--emer2);">Rp {{ number_format($s['nilai_inventori'],0,',','.') }}</div>
            <div style="font-size:.65rem;color:var(--mut);margin-top:.2rem;">harga beli × stok</div>
        </div>
    </div>

    {{-- ── FORM TAMBAH/EDIT ─────────────────────────────────────────────────── --}}
    @if($showForm)
    <div class="glass-card" style="padding:1.25rem;margin-bottom:1.25rem;border-color:rgba(45,212,191,.3);">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1rem;">
            <div class="font-heading" style="font-size:.9rem;color:#2dd4bf;">{{ $editId ? 'Edit BMHP' : 'Tambah BMHP Baru' }}</div>
            <button wire:click="cancel" style="background:none;border:none;color:var(--mut);cursor:pointer;font-size:1.1rem;"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" style="display:inline-block;vertical-align:middle"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg></button>
        </div>
        <form wire:submit="save">
            <div style="display:grid;grid-template-columns:2fr 1.3fr 1fr 1fr;gap:.7rem;margin-bottom:.7rem;">
                <div>
                    <label class="form-label">Nama BMHP <span style="color:var(--red2);">*</span></label>
                    <input wire:model="nama_obat" type="text" placeholder="mis. Spuit 3cc" class="form-input">
                    @error('nama_obat')<div style="color:var(--red2);font-size:.68rem;margin-top:.2rem;">{{ $message }}</div>@enderror
                </div>
                <div>
                    <label class="form-label">Kategori <span style="color:var(--red2);">*</span></label>
                    <select wire:model="kategori_diagnosis" class="form-input">
                        @foreach($kategoriOpsi as $k)<option value="{{ $k }}">{{ $k }}</option>@endforeach
                    </select>
                </div>
                <div>
                    <label class="form-label">Satuan</label>
                    <input wire:model="satuan" type="text" placeholder="pcs" class="form-input">
                </div>
                <div>
                    <label class="form-label">Isi / Box</label>
                    <input wire:model="isi_per_box" type="number" min="1" class="form-input font-mono">
                </div>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr 1fr 1fr;gap:.7rem;margin-bottom:1rem;">
                <div>
                    <label class="form-label">Harga Beli/Unit <span style="color:var(--red2);">*</span></label>
                    <input wire:model="harga_beli_per_unit" type="number" min="0" step="50" class="form-input font-mono">
                </div>
                <div>
                    <label class="form-label">Harga Jual/Unit <span style="color:var(--red2);">*</span></label>
                    <input wire:model="harga_jual_per_unit" type="number" min="0" step="50" class="form-input font-mono">
                </div>
                <div>
                    <label class="form-label">Stok Awal ({{ $satuan ?: 'pcs' }})</label>
                    <input wire:model="stok_aktual" type="number" min="0" class="form-input font-mono">
                </div>
                <div>
                    <label class="form-label">Stok Minimum</label>
                    <input wire:model="stok_minimum" type="number" min="0" class="form-input font-mono">
                </div>
            </div>
            <div style="display:flex;gap:.5rem;padding-top:.7rem;border-top:1px solid rgba(255,255,255,.06);">
                <button type="submit" class="btn-gold" wire:loading.attr="disabled" wire:target="save">
                    <span wire:loading.remove wire:target="save">{{ $editId ? 'Perbarui' : 'Simpan' }}</span>
                    <span wire:loading wire:target="save">Menyimpan…</span>
                </button>
                <button type="button" wire:click="cancel" class="btn-outline">Batal</button>
            </div>
        </form>
    </div>
    @endif

    {{-- ── FILTER CHIPS ─────────────────────────────────────────────────────── --}}
    <div style="display:flex;gap:.5rem;align-items:center;flex-wrap:wrap;margin-bottom:1rem;">
        <button wire:click="$set('filterKategori','semua')" class="badge" style="cursor:pointer;font-size:.72rem;padding:.3rem .7rem;background:{{ $filterKategori==='semua' ? 'rgba(45,212,191,.18)' : 'rgba(255,255,255,.04)' }};color:{{ $filterKategori==='semua' ? '#2dd4bf' : 'var(--mut)' }};border:1px solid {{ $filterKategori==='semua' ? 'rgba(45,212,191,.3)' : 'var(--line)' }};">Semua Kategori</button>
        @foreach($this->kategoriList as $kat)
        <button wire:click="$set('filterKategori','{{ $kat }}')" class="badge" style="cursor:pointer;font-size:.72rem;padding:.3rem .7rem;background:{{ $filterKategori===$kat ? 'rgba(45,212,191,.18)' : 'rgba(255,255,255,.04)' }};color:{{ $filterKategori===$kat ? '#2dd4bf' : 'var(--mut)' }};border:1px solid {{ $filterKategori===$kat ? 'rgba(45,212,191,.3)' : 'var(--line)' }};">{{ $kat }}</button>
        @endforeach
        <span style="width:1px;height:1.2rem;background:var(--line);margin:0 .2rem;"></span>
        @foreach(['semua'=>'Semua','aman'=>'Aman','kritis'=>'Kritis','habis'=>'Habis'] as $k=>$lbl)
        <button wire:click="$set('filterStok','{{ $k }}')" class="badge" style="cursor:pointer;font-size:.72rem;padding:.3rem .7rem;background:{{ $filterStok===$k ? 'rgba(217,164,65,.16)' : 'rgba(255,255,255,.04)' }};color:{{ $filterStok===$k ? 'var(--gold2)' : 'var(--mut)' }};border:1px solid {{ $filterStok===$k ? 'rgba(217,164,65,.28)' : 'var(--line)' }};">{{ $lbl }}</button>
        @endforeach
    </div>

    {{-- ── TABEL ────────────────────────────────────────────────────────────── --}}
    <div class="glass-card" style="overflow-x:auto;">
        <table class="data-table">
            <thead>
                <tr>
                    <th wire:click="sortBy('nama_obat')" style="cursor:pointer;min-width:170px;">Nama BMHP {!! $sortBy==='nama_obat' ? ($sortDir==='asc'?'<svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" style="display:inline-block;vertical-align:middle"><line x1="12" y1="19" x2="12" y2="5"/><polyline points="5 12 12 5 19 12"/></svg>':'<svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" style="display:inline-block;vertical-align:middle"><line x1="12" y1="5" x2="12" y2="19"/><polyline points="19 12 12 19 5 12"/></svg>') : '' !!}</th>
                    <th>Kategori</th>
                    <th style="text-align:right;">Stok ✎</th>
                    <th style="text-align:center;">Isi/Box ✎</th>
                    <th style="text-align:right;">Min ✎</th>
                    <th style="text-align:right;">Harga Jual</th>
                    <th style="text-align:center;">Status</th>
                    <th style="text-align:center;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($this->bmhpList as $o)
                @php $isiB = max(1, (int) ($o->isi_per_box ?? 1)); $st = $o->stok_status; @endphp
                <tr wire:key="bmhp-{{ $o->id }}">
                    <td>
                        <div style="font-weight:600;color:var(--ink);font-size:.84rem;">{{ $o->nama_obat }}</div>
                        <div style="font-size:.66rem;color:var(--mut2);font-family:monospace;">{{ $o->kode_obat }}</div>
                    </td>
                    <td><span style="font-size:.68rem;color:#2dd4bf;background:rgba(45,212,191,.1);border:1px solid rgba(45,212,191,.2);border-radius:2rem;padding:.12rem .5rem;white-space:nowrap;">{{ $o->kategori_diagnosis }}</span></td>
                    <td style="text-align:right;">
                        <input type="number" value="{{ $o->stok_aktual }}" min="0"
                            wire:change="updateStok({{ $o->id }}, $event.target.value)" class="font-mono"
                            style="width:74px;text-align:right;background:rgba(45,212,191,.07);border:1px solid rgba(45,212,191,.2);border-radius:.3rem;padding:.18rem .35rem;font-size:.82rem;color:{{ $o->stok_aktual<=0 ? 'var(--red2)' : ($o->stok_aktual<=$o->stok_minimum ? 'var(--gold2)' : 'var(--emer2)') }};">
                        <div style="font-size:.62rem;color:var(--mut2);margin-top:2px;">{{ $o->satuan }}@if($isiB>1) · {{ intdiv((int)$o->stok_aktual,$isiB) }} box {{ '' }}@endif</div>
                    </td>
                    <td style="text-align:center;">
                        <input type="number" value="{{ $o->isi_per_box ?? 1 }}" min="1"
                            wire:change="updateIsiPerBox({{ $o->id }}, $event.target.value)" class="font-mono"
                            style="width:54px;text-align:center;background:rgba(122,162,247,.07);border:1px solid rgba(122,162,247,.2);border-radius:.3rem;padding:.18rem .3rem;font-size:.8rem;color:var(--mut);">
                    </td>
                    <td style="text-align:right;">
                        <input type="number" value="{{ $o->stok_minimum }}" min="0"
                            wire:change="updateMinimum({{ $o->id }}, $event.target.value)" class="font-mono"
                            style="width:56px;text-align:right;background:rgba(217,164,65,.07);border:1px solid rgba(217,164,65,.2);border-radius:.3rem;padding:.18rem .35rem;font-size:.82rem;color:var(--mut);">
                    </td>
                    <td class="font-mono" style="text-align:right;font-size:.82rem;color:var(--emer2);font-weight:600;">Rp {{ number_format($o->harga_jual_per_unit,0,',','.') }}</td>
                    <td style="text-align:center;">
                        <span class="badge badge-{{ $st==='aman'?'laba':($st==='kritis'?'cek':'rugi') }}" style="font-size:.68rem;">{{ ucfirst($st) }}</span>
                    </td>
                    <td style="text-align:center;">
                        <div style="display:flex;gap:.3rem;justify-content:center;">
                            <button wire:click="openEdit({{ $o->id }})" style="background:rgba(217,164,65,.1);border:1px solid rgba(217,164,65,.2);color:var(--gold2);border-radius:.3rem;padding:.2rem .5rem;cursor:pointer;font-size:.68rem;font-weight:600;">Edit</button>
                            <button wire:click="delete({{ $o->id }})" wire:confirm="Nonaktifkan BMHP ini?" class="btn-danger" style="padding:.2rem .5rem;font-size:.68rem;">Hapus</button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" style="text-align:center;padding:3rem 1rem;">
                        <div style="font-size:2.5rem;margin-bottom:.6rem;opacity:.3;">🧰</div>
                        <div style="font-size:.85rem;color:var(--mut);font-weight:600;margin-bottom:.3rem;">Belum ada BMHP</div>
                        <div style="font-size:.72rem;color:var(--mut);margin-bottom:1rem;">Tambah BMHP pertama (spuit, handscoon, kasa, dll).</div>
                        <button wire:click="openAdd" style="background:rgba(45,212,191,.1);border:1px solid rgba(45,212,191,.25);color:#2dd4bf;border-radius:.4rem;padding:.4rem 1rem;cursor:pointer;font-size:.75rem;font-weight:600;">+ Tambah BMHP</button>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div style="margin-top:.6rem;font-size:.72rem;color:var(--mut);display:flex;align-items:center;gap:.4rem;">
        <span style="width:.5rem;height:.5rem;border-radius:50%;background:#2dd4bf;display:inline-block;"></span>
        {{ $this->bmhpList->count() }} BMHP @if($filterKategori!=='semua')· {{ $filterKategori }}@endif · ✎ kolom Stok, Isi/Box, Min bisa diedit langsung · terhubung SIM/RME
    </div>
</div>
