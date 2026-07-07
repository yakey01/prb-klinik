<div>
    @php
        $stats   = $this->stats;
        $me      = auth()->id();
        $isAdmin = auth()->user()?->isAdmin();
    @endphp

    {{-- ══════════════════════════ HEADER ══════════════════════════ --}}
    <div class="um-head">
        <div>
            <h2 class="font-heading um-title">Manajemen Akun</h2>
            <p class="um-sub">Kelola pengguna, peran, dan hak akses sistem PRB dengan kontrol penuh.</p>
        </div>
        @if($isAdmin)
        <button wire:click="openAdd" class="btn-gold um-add">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Tambah Pengguna
        </button>
        @endif
    </div>

    {{-- ══════════════════════════ STAT CARDS ══════════════════════ --}}
    <div class="um-stats">
        <div class="um-stat">
            <div class="um-stat-ico" style="background:rgba(217,164,65,.12);color:var(--gold2);">
                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75"/></svg>
            </div>
            <div class="um-stat-body">
                <div class="um-stat-num">{{ $stats['total'] }}</div>
                <div class="um-stat-lbl">Total Pengguna</div>
            </div>
        </div>
        <div class="um-stat">
            <div class="um-stat-ico" style="background:rgba(63,207,142,.12);color:var(--emer2);">
                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M22 11.08V12a10 10 0 11-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
            </div>
            <div class="um-stat-body">
                <div class="um-stat-num">{{ $stats['active'] }}</div>
                <div class="um-stat-lbl">Akun Aktif</div>
            </div>
        </div>
        <div class="um-stat">
            <div class="um-stat-ico" style="background:rgba(232,100,90,.12);color:var(--red2);">
                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="4.93" y1="4.93" x2="19.07" y2="19.07"/></svg>
            </div>
            <div class="um-stat-body">
                <div class="um-stat-num">{{ $stats['inactive'] }}</div>
                <div class="um-stat-lbl">Nonaktif</div>
            </div>
        </div>
        <div class="um-stat um-stat-roles">
            <div class="um-rolechip" style="--c:var(--gold2);"><span class="um-dot" style="background:var(--gold2);"></span>{{ $stats['admin'] }} Admin</div>
            <div class="um-rolechip" style="--c:var(--blue);"><span class="um-dot" style="background:var(--blue);"></span>{{ $stats['apoteker'] }} Apoteker</div>
            <div class="um-rolechip" style="--c:var(--mut);"><span class="um-dot" style="background:var(--mut);"></span>{{ $stats['viewer'] }} Viewer</div>
        </div>
    </div>

    {{-- ══════════════════════════ TOOLBAR ═════════════════════════ --}}
    <div class="um-toolbar">
        <div class="um-search">
            <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            <input wire:model.live.debounce.300ms="search" type="text" placeholder="Cari nama, email, telepon…" class="um-search-input">
            @if($search)
            <button wire:click="$set('search','')" class="um-search-clear" aria-label="Bersihkan"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" style="display:inline-block;vertical-align:middle"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg></button>
            @endif
        </div>
        <select wire:model.live="filterRole" class="form-input um-filter">
            <option value="">Semua Peran</option>
            <option value="admin">Administrator</option>
            <option value="apoteker">Apoteker</option>
            <option value="viewer">Viewer</option>
        </select>
        <select wire:model.live="filterStatus" class="form-input um-filter">
            <option value="">Semua Status</option>
            <option value="active">Aktif</option>
            <option value="inactive">Nonaktif</option>
        </select>
        @if($search || $filterRole || $filterStatus)
        <button wire:click="resetFilters" class="btn-outline um-reset">Reset</button>
        @endif
    </div>

    {{-- ══════════════════════════ TABLE ═══════════════════════════ --}}
    <div class="glass-card um-tablewrap">
        <table class="data-table um-table">
            <thead>
                <tr>
                    <th wire:click="sort('name')" class="um-sortable">
                        Pengguna @include('livewire.partials.sort-caret', ['col' => 'name'])
                    </th>
                    <th wire:click="sort('role')" class="um-sortable">
                        Peran @include('livewire.partials.sort-caret', ['col' => 'role'])
                    </th>
                    <th>Status</th>
                    <th wire:click="sort('last_login_at')" class="um-sortable">
                        Login Terakhir @include('livewire.partials.sort-caret', ['col' => 'last_login_at'])
                    </th>
                    <th style="text-align:right;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($this->users as $user)
                @php $meta = $user->roleMeta(); @endphp
                <tr wire:key="user-{{ $user->id }}" class="{{ $user->is_active ? '' : 'um-row-off' }}">
                    {{-- Pengguna --}}
                    <td>
                        <div class="um-userc">
                            <div class="um-avatar" style="background:{{ $user->avatarGradient() }};color:{{ $meta['ink'] }};">
                                {{ $user->initials() }}
                            </div>
                            <div class="um-userinfo">
                                <div class="um-username">
                                    {{ $user->name }}
                                    @if($user->id === $me)<span class="um-you">Anda</span>@endif
                                </div>
                                <div class="um-useremail font-mono">{{ $user->email }}</div>
                                @if($user->jabatan)<div class="um-jabatan">{{ $user->jabatan }}</div>@endif
                            </div>
                        </div>
                    </td>
                    {{-- Peran --}}
                    <td>
                        <span class="um-rolebadge" style="background:{{ $meta['bg'] }};color:{{ $meta['color'] }};border:1px solid {{ $meta['border'] }};">
                            {{ $meta['label'] }}
                        </span>
                    </td>
                    {{-- Status --}}
                    <td>
                        @if($isAdmin && $user->id !== $me)
                        <button wire:click="toggleStatus({{ $user->id }})"
                                class="um-statustoggle {{ $user->is_active ? 'on' : 'off' }}"
                                title="{{ $user->is_active ? 'Klik untuk nonaktifkan' : 'Klik untuk aktifkan' }}">
                            <span class="um-statusdot"></span>{{ $user->statusLabel() }}
                        </button>
                        @else
                        <span class="um-statustoggle {{ $user->is_active ? 'on' : 'off' }} um-static">
                            <span class="um-statusdot"></span>{{ $user->statusLabel() }}
                        </span>
                        @endif
                    </td>
                    {{-- Login terakhir --}}
                    <td>
                        <div class="um-lastlogin">{{ $user->lastLoginHuman() }}</div>
                        @if($user->login_count > 0)<div class="um-logincount">{{ $user->login_count }}× login</div>@endif
                    </td>
                    {{-- Aksi --}}
                    <td>
                        <div class="um-actions" x-data="{ open:false }" @click.outside="open=false">
                            <button class="um-actbtn" wire:click="viewActivity({{ $user->id }})" title="Riwayat aktivitas">
                                <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                            </button>
                            @if($isAdmin)
                            <button class="um-actbtn um-edit" wire:click="openEdit({{ $user->id }})" title="Edit">
                                <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.12 2.12 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                            </button>
                            <button class="um-actbtn" @click="open=!open" title="Lainnya">
                                <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="1"/><circle cx="12" cy="5" r="1"/><circle cx="12" cy="19" r="1"/></svg>
                            </button>
                            <div x-show="open" x-transition x-cloak class="um-menu">
                                <button wire:click="resetPassword({{ $user->id }})" @click="open=false" class="um-menu-item">
                                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0110 0v4"/></svg>
                                    Reset Password
                                </button>
                                @if($user->id !== $me)
                                <button wire:click="deleteUser({{ $user->id }})"
                                        wire:confirm="Hapus akun '{{ $user->name }}'? Tindakan ini permanen."
                                        @click="open=false" class="um-menu-item um-menu-danger">
                                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2"/></svg>
                                    Hapus Akun
                                </button>
                                @endif
                            </div>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5">
                        <div class="um-empty">
                            <svg width="34" height="34" fill="none" stroke="currentColor" stroke-width="1.4" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                            <p>Tidak ada pengguna yang cocok dengan filter.</p>
                            @if($search || $filterRole || $filterStatus)
                            <button wire:click="resetFilters" class="btn-outline" style="margin-top:.5rem;">Hapus filter</button>
                            @endif
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- ══════════════════════════ ROLE MATRIX ═════════════════════ --}}
    <div class="um-matrix">
        @foreach(\App\Models\User::ROLE_META as $key => $m)
        <div class="um-matrix-card" style="border-color:{{ $m['border'] }};">
            <div class="um-matrix-head">
                <span class="um-rolebadge" style="background:{{ $m['bg'] }};color:{{ $m['color'] }};border:1px solid {{ $m['border'] }};">{{ $m['label'] }}</span>
            </div>
            <p class="um-matrix-desc">{{ $m['desc'] }}</p>
            <ul class="um-matrix-perms">
                @foreach($m['perms'] as $p)
                <li><svg width="12" height="12" fill="none" stroke="{{ $m['color'] }}" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>{{ $p }}</li>
                @endforeach
            </ul>
        </div>
        @endforeach
    </div>

    {{-- ══════════════════════════ SLIDE-OVER FORM ═════════════════ --}}
    @if($showForm)
    <div class="um-overlay" wire:key="form-overlay">
        <div class="um-overlay-bg" wire:click="cancel"></div>
        <div class="um-panel"
             x-data="umForm()"
             @password-generated.window="reveal($event.detail.value)"
             x-transition:enter="um-slide-enter" x-transition:enter-start="um-slide-start">
            <div class="um-panel-head">
                <div>
                    <div class="um-panel-title font-heading">{{ $editId ? 'Edit Pengguna' : 'Tambah Pengguna Baru' }}</div>
                    <div class="um-panel-sub">{{ $editId ? 'Perbarui detail & hak akses akun.' : 'Buat akun baru beserta peran dan kata sandinya.' }}</div>
                </div>
                <button wire:click="cancel" class="um-panel-close" aria-label="Tutup"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" style="display:inline-block;vertical-align:middle"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg></button>
            </div>

            <form wire:submit="save" class="um-panel-body">
                {{-- Live avatar preview --}}
                <div class="um-preview">
                    <div class="um-preview-av"
                         style="background:{{ \App\Models\User::ROLE_META[$role]['grad'] ?? 'var(--line2)' }};color:{{ \App\Models\User::ROLE_META[$role]['ink'] ?? 'var(--ink)' }};">
                        <span x-text="initials($wire.name) || '?'">?</span>
                    </div>
                    <div>
                        <div class="um-preview-name" x-text="$wire.name || 'Nama Pengguna'">Nama Pengguna</div>
                        <div class="um-preview-mail font-mono" x-text="$wire.email || 'email@klinik.id'">email@klinik.id</div>
                    </div>
                </div>

                {{-- Identitas --}}
                <div class="um-field-grid">
                    <div class="um-field">
                        <label class="form-label">Nama Lengkap *</label>
                        <input wire:model.live="name" type="text" placeholder="mis. dr. Budi Santoso" class="form-input">
                        @error('name')<div class="um-err">{{ $message }}</div>@enderror
                    </div>
                    <div class="um-field">
                        <label class="form-label">Email Login *</label>
                        <input wire:model.live="email" type="email" placeholder="user@klinikdokterku.id" class="form-input">
                        @error('email')<div class="um-err">{{ $message }}</div>@enderror
                    </div>
                    <div class="um-field">
                        <label class="form-label">No. Telepon</label>
                        <input wire:model="phone" type="text" placeholder="08xxxxxxxxxx" class="form-input">
                        @error('phone')<div class="um-err">{{ $message }}</div>@enderror
                    </div>
                    <div class="um-field">
                        <label class="form-label">Jabatan</label>
                        <input wire:model="jabatan" type="text" placeholder="mis. Apoteker Penanggung Jawab" class="form-input">
                        @error('jabatan')<div class="um-err">{{ $message }}</div>@enderror
                    </div>
                </div>

                {{-- Role picker --}}
                <div class="um-field">
                    <label class="form-label">Peran / Hak Akses *</label>
                    <div class="um-roles">
                        @foreach(\App\Models\User::ROLE_META as $key => $m)
                        <label class="um-rolecard {{ $role === $key ? 'sel' : '' }}" style="{{ $role === $key ? '--rc:'.$m['color'].';--rb:'.$m['border'].';' : '' }}">
                            <input type="radio" wire:model.live="role" value="{{ $key }}" class="um-rolecard-radio">
                            <div class="um-rolecard-top">
                                <span class="um-rolecard-name" style="color:{{ $m['color'] }};">{{ $m['label'] }}</span>
                                <span class="um-rolecard-check"><svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round" style="display:inline-block;vertical-align:middle"><polyline points="20 6 9 17 4 12"/></svg></span>
                            </div>
                            <span class="um-rolecard-desc">{{ $m['desc'] }}</span>
                        </label>
                        @endforeach
                    </div>
                    @error('role')<div class="um-err">{{ $message }}</div>@enderror
                </div>

                {{-- Status --}}
                <div class="um-field">
                    <label class="form-label">Status Akun</label>
                    <label class="um-switch-row">
                        <span class="um-switch {{ $is_active ? 'on' : '' }}" wire:click="$toggle('is_active')">
                            <span class="um-switch-knob"></span>
                        </span>
                        <span class="um-switch-text">
                            <strong>{{ $is_active ? 'Aktif' : 'Nonaktif' }}</strong>
                            <span>{{ $is_active ? 'Pengguna dapat masuk & mengakses sistem.' : 'Pengguna diblokir dari login.' }}</span>
                        </span>
                    </label>
                    @if($editId === $me)<div class="um-hint">Anda tidak dapat menonaktifkan / menurunkan peran akun sendiri.</div>@endif
                </div>

                {{-- Password --}}
                @if($editId)
                <label class="um-pwtoggle">
                    <input wire:model.live="ubahPassword" type="checkbox" class="um-check">
                    <span>Ubah kata sandi</span>
                </label>
                @endif

                @if(!$editId || $ubahPassword)
                <div class="um-pwsection">
                    <div class="um-field">
                        <div class="um-pwlabelrow">
                            <label class="form-label" style="margin:0;">Kata Sandi {{ $editId ? '(baru)' : '*' }}</label>
                            <button type="button" wire:click="generatePassword" class="um-genbtn">
                                <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="23 4 23 10 17 10"/><path d="M20.49 15a9 9 0 11-2.12-9.36L23 10"/></svg>
                                Buatkan
                            </button>
                        </div>
                        <div class="um-pwwrap">
                            <input wire:model.live="password" :type="show ? 'text' : 'password'"
                                   x-ref="pw" @input="score($el.value)"
                                   placeholder="Min. 8 karakter" class="form-input" style="padding-right:2.5rem;"
                                   autocomplete="new-password">
                            <button type="button" @click="show=!show" class="um-pweye" tabindex="-1">
                                <svg x-show="!show" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                <svg x-show="show" x-cloak width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M17.94 17.94A10.07 10.07 0 0112 20c-7 0-11-8-11-8a18.45 18.45 0 015.06-5.94M9.9 4.24A9.12 9.12 0 0112 4c7 0 11 8 11 8a18.5 18.5 0 01-2.16 3.19m-6.72-1.07a3 3 0 11-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                            </button>
                        </div>
                        {{-- Strength meter --}}
                        <div class="um-strength" x-show="$wire.password.length > 0" x-cloak>
                            <div class="um-strength-bars">
                                <span :class="strength >= 1 && 'fill'" :style="strength>=1 && ('background:'+color)"></span>
                                <span :class="strength >= 2 && 'fill'" :style="strength>=2 && ('background:'+color)"></span>
                                <span :class="strength >= 3 && 'fill'" :style="strength>=3 && ('background:'+color)"></span>
                                <span :class="strength >= 4 && 'fill'" :style="strength>=4 && ('background:'+color)"></span>
                            </div>
                            <span class="um-strength-lbl" x-text="label" :style="'color:'+color"></span>
                        </div>
                        @error('password')<div class="um-err">{{ $message }}</div>@enderror
                    </div>
                    <div class="um-field">
                        <label class="form-label">Konfirmasi Kata Sandi</label>
                        <input wire:model="password_confirmation" :type="show ? 'text' : 'password'" x-ref="pwc" placeholder="Ulangi kata sandi" class="form-input" autocomplete="new-password">
                    </div>
                </div>
                @endif

                <div class="um-panel-foot">
                    <button type="button" wire:click="cancel" class="btn-outline">Batal</button>
                    <button type="submit" class="btn-gold" wire:loading.attr="disabled" wire:target="save">
                        <span wire:loading.remove wire:target="save">{{ $editId ? 'Simpan Perubahan' : 'Buat Akun' }}</span>
                        <span wire:loading wire:target="save">Menyimpan…</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif

    {{-- ══════════════════════════ ACTIVITY DRAWER ═════════════════ --}}
    @if($activityUserId && $this->activityUser)
    <div class="um-overlay" wire:key="activity-overlay">
        <div class="um-overlay-bg" wire:click="closeActivity"></div>
        <div class="um-panel um-panel-sm">
            <div class="um-panel-head">
                <div>
                    <div class="um-panel-title font-heading">Riwayat Aktivitas</div>
                    <div class="um-panel-sub">{{ $this->activityUser->name }}</div>
                </div>
                <button wire:click="closeActivity" class="um-panel-close" aria-label="Tutup"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" style="display:inline-block;vertical-align:middle"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg></button>
            </div>
            <div class="um-panel-body">
                @forelse($this->activityLogs as $log)
                @php
                    $actColor = match($log->action) {
                        'created' => 'var(--emer2)', 'deleted' => 'var(--red2)',
                        'updated' => 'var(--gold2)', default => 'var(--blue)',
                    };
                @endphp
                <div class="um-log">
                    <div class="um-log-dot" style="background:{{ $actColor }};"></div>
                    <div class="um-log-body">
                        <div class="um-log-desc">{{ $log->description }}</div>
                        <div class="um-log-meta">
                            <span class="um-log-action" style="color:{{ $actColor }};">{{ ucfirst($log->action) }}</span>
                            · {{ $log->created_at?->diffForHumans() }}
                            @if($log->ip_address)· <span class="font-mono">{{ $log->ip_address }}</span>@endif
                        </div>
                    </div>
                </div>
                @empty
                <div class="um-empty">
                    <svg width="30" height="30" fill="none" stroke="currentColor" stroke-width="1.4" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                    <p>Belum ada aktivitas tercatat.</p>
                </div>
                @endforelse
            </div>
        </div>
    </div>
    @endif

    {{-- ══════════════════════════ GENERATED PW MODAL ══════════════ --}}
    @if($generatedPlain)
    <div class="um-overlay" wire:key="pw-modal">
        <div class="um-overlay-bg" wire:click="$set('generatedPlain', null)"></div>
        <div class="um-pwmodal" x-data="{ copied:false }">
            <div class="um-pwmodal-ico">
                <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0110 0v4"/></svg>
            </div>
            <div class="um-pwmodal-title font-heading">Kata Sandi Baru</div>
            <p class="um-pwmodal-sub">Salin & berikan kepada pengguna. Sandi ini hanya ditampilkan sekali.</p>
            <div class="um-pwmodal-code">
                <code class="font-mono" x-ref="code">{{ $generatedPlain }}</code>
                <button @click="navigator.clipboard.writeText($refs.code.textContent.trim()); copied=true; setTimeout(()=>copied=false,1800)" class="um-copybtn">
                    <span x-show="!copied">Salin</span>
                    <span x-show="copied" x-cloak style="color:var(--emer2);"><svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round" style="display:inline-block;vertical-align:middle"><polyline points="20 6 9 17 4 12"/></svg> Tersalin</span>
                </button>
            </div>
            <button wire:click="$set('generatedPlain', null)" class="btn-gold" style="width:100%;justify-content:center;">Selesai</button>
        </div>
    </div>
    @endif

    {{-- ══════════════════════════ STYLES ══════════════════════════ --}}
    @include('livewire.partials.user-manager-styles')

    {{-- ══════════════════════════ ALPINE ══════════════════════════ --}}
    <script>
        function umForm() {
            return {
                show: false,
                strength: 0, label: '', color: '#5f8071',
                initials(name) {
                    if (!name) return '';
                    const parts = name.trim().split(/\s+/).filter(p => !/^(dr|drg|apt|s\.?farm|m\.?kes|sim|\.|,)/i.test(p));
                    const use = parts.length ? parts : name.trim().split(/\s+/);
                    if (use.length === 1) return use[0].substring(0,2).toUpperCase();
                    return (use[0][0] + use[use.length-1][0]).toUpperCase();
                },
                score(v) {
                    let s = 0;
                    if (!v) { this.strength = 0; this.label=''; return; }
                    if (v.length >= 8) s++;
                    if (v.length >= 12) s++;
                    if (/[A-Z]/.test(v) && /[a-z]/.test(v)) s++;
                    if (/[0-9]/.test(v) && /[^A-Za-z0-9]/.test(v)) s++;
                    this.strength = s;
                    const map = {
                        0: ['', '#5f8071'], 1: ['Lemah', '#e8645a'], 2: ['Sedang', '#d9a441'],
                        3: ['Kuat', '#6fb1e0'], 4: ['Sangat Kuat', '#3fcf8e'],
                    };
                    [this.label, this.color] = map[s] || map[0];
                },
                reveal(v) {
                    this.show = true;
                    // Livewire doesn't re-hydrate password inputs on morph, so write the
                    // generated value into the fields directly and sync it back to the server.
                    this.$nextTick(() => {
                        if (this.$refs.pw) {
                            this.$refs.pw.value = v;
                            this.$refs.pw.dispatchEvent(new Event('input', { bubbles: true }));
                        }
                        if (this.$refs.pwc) {
                            this.$refs.pwc.value = v;
                            this.$refs.pwc.dispatchEvent(new Event('input', { bubbles: true }));
                        }
                        this.score(v);
                    });
                },
            }
        }
    </script>
</div>
