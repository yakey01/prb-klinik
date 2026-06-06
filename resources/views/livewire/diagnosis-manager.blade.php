<div>
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.2rem;">
        <div>
            <h2 class="font-heading" style="font-size:1.1rem;color:var(--ink);margin:0 0 .2rem;">Kategori Diagnosis</h2>
            <p style="font-size:.75rem;color:var(--mut);margin:0;">Kelola kategori diagnosis untuk katalog obat PRB.</p>
        </div>
        <button wire:click="openAdd" class="btn-gold">
            <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Tambah Diagnosis
        </button>
    </div>

    @if($showForm)
    <div class="glass-card" style="padding:1.25rem;margin-bottom:1.25rem;border-color:var(--gold);">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1rem;">
            <div class="font-heading" style="font-size:.95rem;color:var(--gold2);">{{ $editId ? '✏️ Edit Diagnosis' : '+ Tambah Diagnosis Baru' }}</div>
            <button wire:click="cancel" style="background:none;border:none;color:var(--mut);cursor:pointer;font-size:1.1rem;">✕</button>
        </div>
        <form wire:submit="save">
            <div style="display:grid;grid-template-columns:2fr 1fr 1fr auto;gap:.75rem;align-items:end;">
                <div>
                    <label class="form-label">Nama Diagnosis *</label>
                    <input wire:model="nama" type="text" placeholder="misal: Diabetes Tipe 2" class="form-input">
                    @error('nama')<div style="color:var(--red);font-size:.7rem;margin-top:.2rem;">{{ $message }}</div>@enderror
                </div>
                <div>
                    <label class="form-label">Warna</label>
                    <div style="display:flex;gap:.4rem;align-items:center;">
                        <input wire:model.live="warna" type="color" style="width:40px;height:38px;border:1px solid var(--line);border-radius:.4rem;background:var(--panel);cursor:pointer;padding:2px;">
                        <input wire:model="warna" type="text" placeholder="#6fb1e0" class="form-input font-mono" style="flex:1;font-size:.8rem;">
                    </div>
                    @error('warna')<div style="color:var(--red);font-size:.7rem;margin-top:.2rem;">{{ $message }}</div>@enderror
                </div>
                <div>
                    <label class="form-label">Urutan</label>
                    <input wire:model="sort_order" type="number" min="0" class="form-input font-mono">
                </div>
                <div style="display:flex;gap:.4rem;">
                    <button type="submit" class="btn-gold" style="white-space:nowrap;">
                        {{ $editId ? 'Simpan' : 'Tambah' }}
                    </button>
                    <button type="button" wire:click="cancel" class="btn-outline">Batal</button>
                </div>
            </div>
            <div style="margin-top:.75rem;">
                <label style="display:flex;align-items:center;gap:.5rem;cursor:pointer;">
                    <input wire:model="is_active" type="checkbox" style="accent-color:var(--emer);width:14px;height:14px;">
                    <span style="font-size:.8rem;color:var(--mut);">Aktif (tampil di filter katalog)</span>
                </label>
            </div>
        </form>
    </div>
    @endif

    <div class="glass-card" style="overflow:hidden;">
        @forelse($this->diagnoses as $d)
        <div style="display:flex;align-items:center;justify-content:space-between;padding:.85rem 1.25rem;border-bottom:1px solid rgba(31,61,48,.4);gap:1rem;{{ !$d->is_active ? 'opacity:.45;' : '' }}">
            <div style="display:flex;align-items:center;gap:.85rem;flex:1;">
                <div style="width:16px;height:16px;border-radius:50%;background:{{ $d->warna }};flex-shrink:0;"></div>
                <div style="font-weight:500;font-size:.88rem;">{{ $d->nama }}</div>
                <div style="font-size:.7rem;color:var(--mut2);font-family:'JetBrains Mono',monospace;">{{ $d->warna }}</div>
                @if(!$d->is_active)
                <span class="badge badge-cek" style="font-size:.65rem;">nonaktif</span>
                @endif
            </div>
            <div style="display:flex;align-items:center;gap:.5rem;">
                <span style="font-size:.7rem;color:var(--mut2);">#{{ $d->sort_order }}</span>
                <button wire:click="openEdit({{ $d->id }})" style="background:rgba(217,164,65,.1);border:1px solid rgba(217,164,65,.25);color:var(--gold2);border-radius:.35rem;padding:.25rem .55rem;cursor:pointer;font-size:.75rem;">Edit</button>
                <button wire:click="toggleActive({{ $d->id }})" style="background:{{ $d->is_active ? 'rgba(232,100,90,.1)' : 'rgba(63,207,142,.1)' }};border:1px solid {{ $d->is_active ? 'rgba(232,100,90,.25)' : 'rgba(63,207,142,.25)' }};color:{{ $d->is_active ? 'var(--red2)' : 'var(--emer2)' }};border-radius:.35rem;padding:.25rem .55rem;cursor:pointer;font-size:.7rem;">
                    {{ $d->is_active ? 'Nonaktif' : 'Aktifkan' }}
                </button>
                @if($d->nama !== 'Lainnya')
                <button wire:click="delete({{ $d->id }})" wire:confirm="Hapus diagnosis '{{ $d->nama }}'?" class="btn-danger" style="padding:.25rem .55rem;font-size:.7rem;">Hapus</button>
                @endif
            </div>
        </div>
        @empty
        <div style="text-align:center;padding:2rem;color:var(--mut);">Belum ada data diagnosis.</div>
        @endforelse
    </div>
    <div style="margin-top:.6rem;font-size:.72rem;color:var(--mut2);">{{ $this->diagnoses->count() }} diagnosis terdaftar</div>
</div>
