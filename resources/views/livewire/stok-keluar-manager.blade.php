<div>
    {{-- Header --}}
    <div style="display:flex;align-items:flex-end;justify-content:space-between;margin-bottom:1.25rem;flex-wrap:wrap;gap:.75rem;">
        <div>
            <div style="display:flex;align-items:center;gap:.6rem;margin-bottom:.2rem;">
                <h2 class="font-heading" style="font-size:1.1rem;color:var(--ink);margin:0;">Stok Keluar Obat</h2>
                <span style="background:rgba(63,207,142,.1);border:1px solid rgba(63,207,142,.22);color:var(--emer2);font-size:.65rem;font-weight:700;padding:.15rem .55rem;border-radius:2rem;letter-spacing:.05em;text-transform:uppercase;">Kronis</span>
                <span style="background:rgba(111,177,224,.12);border:1px solid rgba(111,177,224,.25);color:var(--blue);font-size:.65rem;font-weight:700;padding:.15rem .55rem;border-radius:2rem;letter-spacing:.05em;text-transform:uppercase;">Non-Kronis</span>
            </div>
            <p style="font-size:.73rem;color:var(--mut);margin:0;">Pencatatan pengeluaran obat ke pasien — kronis &amp; non-kronis</p>
        </div>
        <button wire:click="openAdd" class="btn-gold">+ Catat Keluar</button>
    </div>

    {{-- Form --}}
    @if($showForm)
    <div class="glass-card" style="padding:1.25rem;margin-bottom:1.25rem;border-color:rgba(111,177,224,.3);">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1rem;">
            <div class="font-heading" style="font-size:.95rem;color:var(--blue);">{{ $editId ? '✏️ Edit' : '+ Catat' }} Stok Keluar</div>
            <button wire:click="cancel" style="background:none;border:none;color:var(--mut);cursor:pointer;font-size:1.1rem;">✕</button>
        </div>
        <form wire:submit="save">
            <div style="display:grid;grid-template-columns:2fr 1fr 1fr 1fr;gap:.75rem;margin-bottom:.75rem;">
                <div>
                    <label class="form-label">Nama Obat *</label>
                    <select wire:model.live="obat_id" class="form-input">
                        <option value="0">— Pilih Obat —</option>
                        @foreach($this->obatList as $o)
                        <option value="{{ $o->id }}">{{ $o->nama_obat }} ({{ $o->tipe_obat === 'kronis' ? 'Kronis' : 'Non-Kronis' }})</option>
                        @endforeach
                    </select>
                    @error('obat_id')<div style="color:var(--red);font-size:.7rem;">{{ $message }}</div>@enderror
                </div>
                <div>
                    <label class="form-label">Tanggal Keluar *</label>
                    <input wire:model="tanggal_keluar" type="date" class="form-input">
                    @error('tanggal_keluar')<div style="color:var(--red);font-size:.7rem;">{{ $message }}</div>@enderror
                </div>
                <div>
                    <label class="form-label">Jumlah Unit *</label>
                    <input wire:model="jumlah_unit" type="number" min="1" class="form-input font-mono">
                    @error('jumlah_unit')<div style="color:var(--red);font-size:.7rem;">{{ $message }}</div>@enderror
                </div>
                <div>
                    <label class="form-label">Satuan</label>
                    <input wire:model="satuan" type="text" placeholder="tablet" class="form-input">
                </div>
            </div>
            <div style="display:grid;grid-template-columns:1fr 2fr;gap:.75rem;margin-bottom:.75rem;">
                <div>
                    <label class="form-label">Harga Jual / Unit (Rp) *</label>
                    <input wire:model="harga_jual_per_unit" type="number" min="0" step="100" class="form-input font-mono">
                    @error('harga_jual_per_unit')<div style="color:var(--red);font-size:.7rem;">{{ $message }}</div>@enderror
                    @if($harga_jual_per_unit > 0 && $jumlah_unit > 0)
                    <div style="font-size:.7rem;color:var(--emer2);margin-top:.2rem;font-family:monospace;">
                        Total: Rp {{ number_format($harga_jual_per_unit * $jumlah_unit, 0, ',', '.') }}
                    </div>
                    @endif
                </div>
                <div>
                    <label class="form-label">Keterangan</label>
                    <input wire:model="keterangan" type="text" placeholder="No. resep / diagnosa / keterangan..." class="form-input">
                </div>
            </div>
            <div style="display:flex;gap:.5rem;">
                <button type="submit" class="btn-gold">Simpan</button>
                <button type="button" wire:click="cancel" class="btn-outline">Batal</button>
            </div>
        </form>
    </div>
    @endif

    {{-- Summary cards --}}
    @if($this->records->isNotEmpty())
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:.75rem;margin-bottom:1.25rem;">
        <div style="background:rgba(63,207,142,.06);border:1px solid rgba(63,207,142,.15);border-radius:.75rem;padding:.9rem 1.1rem;">
            <div style="font-size:.65rem;color:var(--mut);text-transform:uppercase;letter-spacing:.07em;margin-bottom:.3rem;">Pendapatan</div>
            <div class="font-mono" style="font-size:.95rem;font-weight:700;color:var(--emer2);">Rp {{ number_format($this->summary['total_pendapatan'],0,',','.') }}</div>
        </div>
        <div style="background:rgba(232,100,90,.06);border:1px solid rgba(232,100,90,.12);border-radius:.75rem;padding:.9rem 1.1rem;">
            <div style="font-size:.65rem;color:var(--mut);text-transform:uppercase;letter-spacing:.07em;margin-bottom:.3rem;">Biaya Beli</div>
            <div class="font-mono" style="font-size:.95rem;font-weight:700;color:var(--mut2);">Rp {{ number_format($this->summary['total_biaya'],0,',','.') }}</div>
        </div>
        <div style="background:rgba(217,164,65,.06);border:1px solid rgba(217,164,65,.15);border-radius:.75rem;padding:.9rem 1.1rem;">
            <div style="font-size:.65rem;color:var(--mut);text-transform:uppercase;letter-spacing:.07em;margin-bottom:.3rem;">Laba Kotor</div>
            <div class="font-mono" style="font-size:.95rem;font-weight:700;color:{{ $this->summary['total_laba'] >= 0 ? 'var(--gold2)' : 'var(--red2)' }};">
                {{ $this->summary['total_laba'] >= 0 ? '+' : '' }}Rp {{ number_format($this->summary['total_laba'],0,',','.') }}
            </div>
        </div>
        <div style="background:rgba(111,177,224,.06);border:1px solid rgba(111,177,224,.12);border-radius:.75rem;padding:.9rem 1.1rem;">
            <div style="font-size:.65rem;color:var(--mut);text-transform:uppercase;letter-spacing:.07em;margin-bottom:.3rem;">Total Unit Keluar</div>
            <div class="font-mono" style="font-size:.95rem;font-weight:700;color:var(--blue);">{{ number_format($this->summary['total_item'],0,',','.') }}</div>
        </div>
    </div>
    @endif

    {{-- Filter bar --}}
    <div style="display:flex;gap:.75rem;margin-bottom:1rem;flex-wrap:wrap;align-items:center;">
        <input wire:model.live="search" type="text" placeholder="Cari nama obat..." class="form-input" style="max-width:240px;">
        <input wire:model.live="filterBulan" type="month" class="form-input font-mono" style="max-width:160px;">
        <span style="font-size:.75rem;color:var(--mut);">{{ $this->records->count() }} transaksi</span>
    </div>

    {{-- Table --}}
    <div class="glass-card" style="overflow-x:auto;">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Tanggal</th>
                    <th>Nama Obat</th>
                    <th style="text-align:right;">Jumlah</th>
                    <th style="text-align:right;">Harga Jual/Unit</th>
                    <th style="text-align:right;">Total Pend.</th>
                    <th style="text-align:right;">Total Biaya</th>
                    <th style="text-align:right;">Laba</th>
                    <th>Keterangan</th>
                    <th style="text-align:center;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($this->records as $sk)
                @php $laba = $sk->laba; @endphp
                <tr>
                    <td class="font-mono" style="font-size:.78rem;color:var(--mut2);">{{ $sk->tanggal_keluar->format('d/m/Y') }}</td>
                    <td style="font-weight:600;font-size:.85rem;">{{ $sk->obat->nama_obat ?? '—' }}</td>
                    <td class="font-mono" style="text-align:right;">{{ $sk->jumlah_unit }} <span style="color:var(--mut);font-size:.73rem;">{{ $sk->satuan }}</span></td>
                    <td class="font-mono" style="text-align:right;font-size:.8rem;">{{ number_format($sk->harga_jual_per_unit,0,',','.') }}</td>
                    <td class="font-mono" style="text-align:right;color:var(--emer2);font-weight:600;">{{ number_format($sk->total_pendapatan,0,',','.') }}</td>
                    <td class="font-mono" style="text-align:right;font-size:.8rem;color:var(--mut2);">{{ number_format($sk->total_biaya,0,',','.') }}</td>
                    <td class="font-mono" style="text-align:right;font-weight:700;color:{{ $laba >= 0 ? 'var(--emer2)' : 'var(--red2)' }};">
                        {{ $laba >= 0 ? '+' : '' }}{{ number_format($laba,0,',','.') }}
                    </td>
                    <td style="font-size:.77rem;color:var(--mut2);">{{ $sk->keterangan ?? '—' }}</td>
                    <td style="text-align:center;">
                        <div style="display:flex;gap:.3rem;justify-content:center;">
                            <button wire:click="openEdit({{ $sk->id }})" style="background:rgba(217,164,65,.1);border:1px solid rgba(217,164,65,.25);color:var(--gold2);border-radius:.3rem;padding:.2rem .5rem;cursor:pointer;font-size:.72rem;">Edit</button>
                            <button wire:click="delete({{ $sk->id }})" wire:confirm="Hapus data ini? Stok akan dikembalikan." class="btn-danger" style="padding:.2rem .5rem;font-size:.72rem;">Hapus</button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="9" style="text-align:center;padding:2.5rem;color:var(--mut);">
                    <div style="margin-bottom:.5rem;">📦</div>
                    Belum ada pencatatan stok keluar.
                </td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
