<div>
    {{-- HEADER + ADD BUTTON --}}
    <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:1.2rem;">
        <div style="font-size:.82rem; color:var(--mut);">
            {{ $this->distributors->count() }} distributor terdaftar
        </div>
        <button wire:click="openAdd" class="btn-gold">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Tambah PBF
        </button>
    </div>

    {{-- FORM TAMBAH/EDIT --}}
    @if($showForm)
    <div class="glass-card" style="padding:1.5rem; margin-bottom:1.5rem; border-color:var(--gold);">
        <div class="font-heading" style="font-size:1rem; color:var(--gold2); margin-bottom:1rem;">
            {{ $editId ? 'Edit Distributor' : '+ Tambah Distributor / PBF Baru' }}
        </div>
        <div style="display:grid; grid-template-columns:repeat(auto-fit,minmax(200px,1fr)); gap:1rem; margin-bottom:1.2rem;">
            <div>
                <label class="form-label">Nama PBF *</label>
                <input wire:model="name" type="text" placeholder="PT. Nama Distributor" class="form-input"
                       id="input-nama-pbf">
                @error('name') <span style="color:var(--red);font-size:.72rem;">{{ $message }}</span> @enderror
            </div>
            <div>
                <label class="form-label">Telepon</label>
                <input wire:model="phone" type="text" placeholder="021-xxxxxxxx" class="form-input">
            </div>
            <div>
                <label class="form-label">Alamat</label>
                <input wire:model="address" type="text" placeholder="Jl. ..." class="form-input">
            </div>
        </div>
        <div style="display:flex; gap:.75rem;">
            <button wire:click="save" class="btn-gold">
                <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h11l5 5v11a2 2 0 01-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/></svg>
                {{ $editId ? 'Simpan Perubahan' : 'Tambahkan' }}
            </button>
            <button wire:click="cancel" class="btn-outline">Batal</button>
        </div>
    </div>
    @endif

    {{-- TABLE --}}
    <div class="glass-card" style="overflow:hidden;">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Nama Distributor / PBF</th>
                    <th>Telepon</th>
                    <th>Alamat</th>
                    <th style="text-align:center;">Status</th>
                    <th style="text-align:center;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($this->distributors as $dist)
                <tr>
                    <td style="font-weight:500;">{{ $dist->name }}</td>
                    <td style="color:var(--mut); font-size:.82rem;">{{ $dist->phone ?? '—' }}</td>
                    <td style="color:var(--mut); font-size:.82rem;">{{ $dist->address ?? '—' }}</td>
                    <td style="text-align:center;">
                        <span class="badge {{ $dist->is_active ? 'badge-laba' : 'badge-cek' }}">
                            {{ $dist->is_active ? 'Aktif' : 'Non-aktif' }}
                        </span>
                    </td>
                    <td style="text-align:center;">
                        <div style="display:flex; gap:.5rem; justify-content:center;">
                            <button wire:click="openEdit({{ $dist->id }})" class="btn-outline" style="padding:.3rem .65rem; font-size:.75rem;">
                                <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                Edit
                            </button>
                            <button wire:click="toggleActive({{ $dist->id }})" class="btn-outline"
                                style="padding:.3rem .65rem; font-size:.75rem; color:{{ $dist->is_active ? 'var(--mut)' : 'var(--emer)' }};">
                                {{ $dist->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                            </button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="5" style="text-align:center; color:var(--mut); padding:2rem;">Belum ada distributor.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
