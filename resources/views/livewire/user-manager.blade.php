<div>
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.5rem;">
        <div>
            <h2 class="font-heading" style="font-size:1.1rem;color:var(--ink);margin:0 0 .2rem;">Manajemen Pengguna</h2>
            <p style="font-size:.77rem;color:var(--mut);margin:0;">Kelola akun yang dapat mengakses sistem.</p>
        </div>
        <button wire:click="openAdd" class="btn-gold">
            <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Tambah Pengguna
        </button>
    </div>

    @if($showForm)
    <div class="glass-card" style="padding:1.5rem;margin-bottom:1.5rem;border-color:var(--gold);">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.2rem;">
            <div class="font-heading" style="font-size:1rem;color:var(--gold2);">{{ $editId ? '✏️ Edit Pengguna' : '+ Tambah Pengguna Baru' }}</div>
            <button wire:click="cancel" style="background:none;border:none;color:var(--mut);cursor:pointer;font-size:1.2rem;">✕</button>
        </div>
        <form wire:submit="save">
            <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:.85rem;margin-bottom:.85rem;">
                <div>
                    <label class="form-label">Nama Lengkap *</label>
                    <input wire:model="name" type="text" placeholder="misal: dr. Budi Santoso" class="form-input">
                    @error('name')<div style="color:var(--red);font-size:.7rem;margin-top:.2rem;">{{ $message }}</div>@enderror
                </div>
                <div>
                    <label class="form-label">Email Login *</label>
                    <input wire:model="email" type="email" placeholder="user@klinikdokterku.id" class="form-input">
                    @error('email')<div style="color:var(--red);font-size:.7rem;margin-top:.2rem;">{{ $message }}</div>@enderror
                </div>
                <div>
                    <label class="form-label">Role / Hak Akses *</label>
                    <select wire:model="role" class="form-input">
                        @foreach(\App\Livewire\UserManager::ROLES as $val => $lbl)
                        <option value="{{ $val }}">{{ $lbl }}</option>
                        @endforeach
                    </select>
                    @error('role')<div style="color:var(--red);font-size:.7rem;margin-top:.2rem;">{{ $message }}</div>@enderror
                </div>
            </div>

            @if($editId)
            <div style="margin-bottom:.85rem;">
                <label style="display:flex;align-items:center;gap:.6rem;cursor:pointer;padding:.6rem .9rem;background:var(--panel);border:1px solid var(--line);border-radius:.5rem;width:fit-content;">
                    <input wire:model.live="ubahPassword" type="checkbox" style="accent-color:var(--gold);width:15px;height:15px;cursor:pointer;">
                    <span style="font-size:.82rem;color:var(--mut);">Ubah password</span>
                </label>
            </div>
            @endif

            @if(!$editId || $ubahPassword)
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:.85rem;margin-bottom:.85rem;">
                <div>
                    <label class="form-label">Password {{ $editId ? '(baru)' : '*' }}</label>
                    <input wire:model="password" type="password" placeholder="Min. 8 karakter" class="form-input">
                    @error('password')<div style="color:var(--red);font-size:.7rem;margin-top:.2rem;">{{ $message }}</div>@enderror
                </div>
                <div>
                    <label class="form-label">Konfirmasi Password</label>
                    <input wire:model="password_confirmation" type="password" placeholder="Ulangi password" class="form-input">
                </div>
            </div>
            @endif

            <div style="display:flex;gap:.5rem;margin-top:1rem;">
                <button type="submit" class="btn-gold">{{ $editId ? 'Simpan Perubahan' : 'Buat Akun' }}</button>
                <button type="button" wire:click="cancel" class="btn-outline">Batal</button>
            </div>
        </form>
    </div>
    @endif

    <div class="glass-card" style="overflow:hidden;">
        @forelse($this->users as $user)
        <div style="display:flex;align-items:center;justify-content:space-between;padding:1rem 1.25rem;border-bottom:1px solid rgba(31,61,48,.5);gap:1rem;flex-wrap:wrap;">
            <div style="display:flex;align-items:center;gap:.9rem;flex:1;min-width:200px;">
                <div style="width:40px;height:40px;border-radius:50%;background:linear-gradient(135deg,var(--gold) 0%,#c4892e 100%);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                    <span style="color:#1a0e00;font-weight:700;font-size:.9rem;">{{ strtoupper(substr($user->name,0,1)) }}</span>
                </div>
                <div>
                    <div style="font-weight:600;font-size:.88rem;color:var(--ink);">{{ $user->name }}</div>
                    <div style="font-size:.73rem;color:var(--mut);font-family:'JetBrains Mono',monospace;">{{ $user->email }}</div>
                </div>
            </div>
            <div style="display:flex;align-items:center;gap:.6rem;flex-shrink:0;flex-wrap:wrap;">
                <span class="badge badge-{{ \App\Models\User::roleBadgeClass($user->role) }}" style="font-size:.68rem;">
                    {{ \App\Livewire\UserManager::ROLES[$user->role] ?? $user->role }}
                </span>
                @if($user->id === auth()->id())
                <span style="font-size:.68rem;background:rgba(111,177,224,.1);border:1px solid rgba(111,177,224,.2);color:var(--blue);border-radius:999px;padding:.18rem .6rem;">Anda</span>
                @endif
                <button wire:click="openEdit({{ $user->id }})" style="background:rgba(217,164,65,.1);border:1px solid rgba(217,164,65,.25);color:var(--gold2);border-radius:.35rem;padding:.28rem .6rem;cursor:pointer;font-size:.73rem;">Edit</button>
                @if($user->id !== auth()->id())
                <button wire:click="deleteUser({{ $user->id }})" wire:confirm="Hapus akun '{{ $user->name }}'?" class="btn-danger" style="padding:.28rem .6rem;font-size:.73rem;">Hapus</button>
                @endif
            </div>
        </div>
        @empty
        <div style="text-align:center;padding:2.5rem;color:var(--mut);">Tidak ada pengguna.</div>
        @endforelse
    </div>
    <div style="margin-top:.6rem;font-size:.72rem;color:var(--mut2);">
        <strong>Role:</strong> Admin = akses penuh · Apoteker = bisa edit data · Viewer = baca saja
    </div>
</div>
