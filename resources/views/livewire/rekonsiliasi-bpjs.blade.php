<div>
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.2rem;flex-wrap:wrap;gap:.75rem;">
        <div>
            <h2 class="font-heading" style="font-size:1.1rem;color:var(--ink);margin:0 0 .25rem;">Rekonsiliasi BPJS</h2>
            <p style="font-size:.77rem;color:var(--mut);margin:0;">Proyeksi tagihan BPJS &amp; klaim aktual yang diterima.</p>
        </div>
        <div style="display:flex;gap:.6rem;align-items:center;">
            <div style="background:var(--panel);border:1px solid var(--line);border-radius:.5rem;padding:.5rem .85rem;">
                <div style="font-size:.65rem;color:var(--mut);text-transform:uppercase;letter-spacing:.06em;margin-bottom:.15rem;">Proyeksi Bulan Ini</div>
                <div class="font-mono" style="font-size:.9rem;color:var(--emer2);">Rp {{ number_format($this->proyeksi,0,',','.') }}</div>
            </div>
            <button wire:click="openAdd" class="btn-gold">+ Tambah Rekonsiliasi</button>
        </div>
    </div>

    @if($showForm)
    <div class="glass-card" style="padding:1.25rem;margin-bottom:1.25rem;border-color:var(--gold);">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1rem;">
            <div class="font-heading" style="font-size:.95rem;color:var(--gold2);">{{ $editId ? '✏️ Edit' : '+ Tambah' }} Rekonsiliasi BPJS</div>
            <button wire:click="cancel" style="background:none;border:none;color:var(--mut);cursor:pointer;font-size:1.1rem;">✕</button>
        </div>
        <form wire:submit="save">
            <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:.75rem;margin-bottom:.75rem;">
                <div>
                    <label class="form-label">Bulan *</label>
                    <select wire:model="bulan" class="form-input">
                        @foreach(\App\Models\RekonsiliasiiBpjs::bulanLabels() as $b => $nm)
                        <option value="{{ $b }}">{{ $nm }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="form-label">Tahun *</label>
                    <input wire:model="tahun" type="number" min="2020" max="2099" class="form-input font-mono">
                </div>
                <div>
                    <label class="form-label">Tagihan Diajukan (Rp)</label>
                    <input wire:model="tagihan_diajukan" type="number" min="0" step="1000" class="form-input font-mono">
                    @error('tagihan_diajukan')<div style="color:var(--red);font-size:.7rem;">{{ $message }}</div>@enderror
                </div>
                <div>
                    <label class="form-label">Tagihan Dibayar (Rp)</label>
                    <input wire:model="tagihan_dibayar" type="number" min="0" step="1000" class="form-input font-mono">
                    @error('tagihan_dibayar')<div style="color:var(--red);font-size:.7rem;">{{ $message }}</div>@enderror
                </div>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:.75rem;margin-bottom:.75rem;">
                <div>
                    <label class="form-label">Status</label>
                    <select wire:model="status" class="form-input">
                        <option value="draft">Draft</option>
                        <option value="diajukan">Diajukan</option>
                        <option value="dibayar">Dibayar</option>
                        <option value="selisih">Ada Selisih</option>
                    </select>
                </div>
                <div>
                    <label class="form-label">Tgl Pengajuan</label>
                    <input wire:model="tanggal_pengajuan" type="date" class="form-input">
                </div>
                <div>
                    <label class="form-label">Tgl Pembayaran</label>
                    <input wire:model="tanggal_pembayaran" type="date" class="form-input">
                </div>
            </div>
            <div style="margin-bottom:.75rem;">
                <label class="form-label">Catatan</label>
                <textarea wire:model="catatan" rows="2" placeholder="Catatan rekonsiliasi..." class="form-input" style="resize:vertical;"></textarea>
            </div>
            <div style="display:flex;gap:.5rem;">
                <button type="submit" class="btn-gold">Simpan</button>
                <button type="button" wire:click="cancel" class="btn-outline">Batal</button>
            </div>
        </form>
    </div>
    @endif

    <div class="glass-card" style="overflow-x:auto;">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Periode</th>
                    <th style="text-align:right;">Proyeksi</th>
                    <th style="text-align:right;">Diajukan</th>
                    <th style="text-align:right;">Dibayar</th>
                    <th style="text-align:right;">Selisih</th>
                    <th style="text-align:center;">Status</th>
                    <th>Tgl Pengajuan</th>
                    <th>Tgl Bayar</th>
                    <th style="text-align:center;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($this->records as $rec)
                @php $selisih = $rec->tagihan_dibayar - $rec->tagihan_diajukan; @endphp
                <tr>
                    <td class="font-mono" style="font-weight:600;">{{ \Carbon\Carbon::create($rec->tahun,$rec->bulan,1)->translatedFormat('F Y') }}</td>
                    <td class="font-mono" style="text-align:right;color:var(--mut);font-size:.8rem;">{{ number_format($rec->proyeksi_pendapatan,0,',','.') }}</td>
                    <td class="font-mono" style="text-align:right;font-size:.82rem;">{{ number_format($rec->tagihan_diajukan,0,',','.') }}</td>
                    <td class="font-mono" style="text-align:right;font-size:.82rem;color:var(--emer2);">{{ number_format($rec->tagihan_dibayar,0,',','.') }}</td>
                    <td class="font-mono" style="text-align:right;font-size:.82rem;font-weight:700;color:{{ $selisih >= 0 ? 'var(--emer2)' : 'var(--red2)' }};">
                        {{ $selisih >= 0 ? '+' : '' }}{{ number_format($selisih,0,',','.') }}
                    </td>
                    <td style="text-align:center;">
                        <span class="badge badge-{{ $rec->status==='dibayar'?'laba':($rec->status==='diajukan'?'po':($rec->status==='selisih'?'rugi':'est')) }}" style="font-size:.7rem;">
                            {{ ucfirst($rec->status) }}
                        </span>
                    </td>
                    <td style="font-size:.77rem;color:var(--mut2);">{{ $rec->tanggal_pengajuan?->format('d/m/Y') ?? '—' }}</td>
                    <td style="font-size:.77rem;color:var(--mut2);">{{ $rec->tanggal_pembayaran?->format('d/m/Y') ?? '—' }}</td>
                    <td style="text-align:center;">
                        <div style="display:flex;gap:.3rem;justify-content:center;">
                            <button wire:click="openEdit({{ $rec->id }})" style="background:rgba(217,164,65,.1);border:1px solid rgba(217,164,65,.25);color:var(--gold2);border-radius:.3rem;padding:.2rem .5rem;cursor:pointer;font-size:.72rem;">Edit</button>
                            <button wire:click="delete({{ $rec->id }})" wire:confirm="Hapus data rekonsiliasi ini?" class="btn-danger" style="padding:.2rem .5rem;font-size:.72rem;">Hapus</button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="9" style="text-align:center;padding:2rem;color:var(--mut);">Belum ada data rekonsiliasi BPJS.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
