<div>
    {{-- Stats bar --}}
    <div style="display:flex; gap:1rem; margin-bottom:1.5rem; flex-wrap:wrap;">
        <div class="kpi-card" style="flex:1; min-width:120px;">
            <div style="font-size:.72rem; color:var(--mut); text-transform:uppercase; letter-spacing:.06em;">Aktif</div>
            <div style="font-size:2rem; font-weight:700; color:var(--gold);">{{ $this->stats['aktif'] }}</div>
        </div>
        <div class="kpi-card" style="flex:1; min-width:120px;">
            <div style="font-size:.72rem; color:var(--mut); text-transform:uppercase; letter-spacing:.06em;">Pending</div>
            <div style="font-size:2rem; font-weight:700; color:var(--blue);">{{ $this->stats['pending'] }}</div>
        </div>
        <div class="kpi-card" style="flex:1; min-width:120px;">
            <div style="font-size:.72rem; color:var(--mut); text-transform:uppercase; letter-spacing:.06em;">Selesai Hari Ini</div>
            <div style="font-size:2rem; font-weight:700; color:var(--emer);">{{ $this->stats['selesai'] }}</div>
        </div>
        <div style="display:flex; align-items:center; gap:.5rem; flex-shrink:0;">
            <a href="{{ route('visite.peta') }}"
               style="background:rgba(217,164,65,.12); border:1px solid rgba(217,164,65,.3); color:var(--gold); border-radius:.6rem; padding:.6rem 1rem; font-size:.85rem; font-weight:600; text-decoration:none; display:flex; align-items:center; gap:.4rem;">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polygon points="3 6 9 3 15 6 21 3 21 18 15 21 9 18 3 21"/></svg>
                Lihat Peta
            </a>
            @if(auth()->user()?->isApoteker())
            <button wire:click="openAssign"
                    style="background:var(--emer); color:#0a1410; border:none; border-radius:.6rem; padding:.6rem 1rem; font-size:.85rem; font-weight:700; cursor:pointer; display:flex; align-items:center; gap:.4rem; min-height:40px;">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                Tugaskan Visite
            </button>
            @endif
        </div>
    </div>

    {{-- Filter bar --}}
    <div style="display:flex; gap:.75rem; margin-bottom:1rem; flex-wrap:wrap; align-items:center;">
        <input type="date" wire:model.live="filterTanggal"
               style="background:var(--card); border:1px solid var(--line2); color:var(--ink); border-radius:.5rem; padding:.5rem .75rem; font-size:.85rem;">
        <select wire:model.live="filterStatus"
                style="background:var(--card); border:1px solid var(--line2); color:var(--ink); border-radius:.5rem; padding:.5rem .75rem; font-size:.85rem;">
            <option value="">Semua Status</option>
            <option value="ditugaskan">Ditugaskan</option>
            <option value="dalam_perjalanan">Dalam Perjalanan</option>
            <option value="sampai">Sudah Sampai</option>
            <option value="selesai">Selesai</option>
            <option value="dibatalkan">Dibatalkan</option>
        </select>
        @if($filterStatus || $filterTanggal !== today()->format('Y-m-d'))
        <button wire:click="$set('filterStatus', ''); $set('filterTanggal', '{{ today()->format('Y-m-d') }}')"
                style="background:transparent; border:1px solid var(--line2); color:var(--mut); border-radius:.5rem; padding:.5rem .75rem; font-size:.82rem; cursor:pointer;">
            Reset
        </button>
        @endif
    </div>

    {{-- Daftar visite --}}
    <div class="glass-card" style="overflow:hidden;">
        @forelse($this->visitaList as $v)
        <div style="display:flex; align-items:center; justify-content:space-between; padding:.9rem 1.25rem; border-bottom:1px solid var(--line); gap:1rem; flex-wrap:wrap;">
            <div style="min-width:0; flex:1;">
                <div style="display:flex; align-items:center; gap:.5rem; flex-wrap:wrap;">
                    <span style="font-weight:600; color:var(--ink);">{{ $v->pasien?->nama }}</span>
                    <span style="background:{{ $v->statusColor() }}22; color:{{ $v->statusColor() }}; border:1px solid {{ $v->statusColor() }}44; border-radius:999px; padding:.1rem .55rem; font-size:.72rem; font-weight:600;">
                        {{ $v->statusLabel() }}
                    </span>
                </div>
                <div style="font-size:.8rem; color:var(--mut); margin-top:.2rem;">
                    {{ $v->kurir?->name }} · {{ $v->tanggal_visite->format('d M Y') }}
                </div>
                <div style="font-size:.78rem; color:var(--mut2); margin-top:.1rem; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; max-width:300px;">
                    {{ $v->alamat_tujuan }}
                </div>
            </div>
            <div style="display:flex; gap:.4rem; flex-shrink:0;">
                @if(!$v->isDone())
                <button wire:click="confirmBatalkan({{ $v->id }})"
                        style="background:transparent; border:1px solid rgba(232,100,90,.4); color:var(--red2); border-radius:.5rem; padding:.35rem .65rem; font-size:.78rem; cursor:pointer; min-height:36px;">
                    Batalkan
                </button>
                @endif
                <a href="{{ route('visite.riwayat', $v->id) }}"
                   style="background:rgba(255,255,255,.05); border:1px solid var(--line2); color:var(--mut); border-radius:.5rem; padding:.35rem .65rem; font-size:.78rem; text-decoration:none; display:inline-flex; align-items:center; min-height:36px;">
                    Riwayat
                </a>
            </div>
        </div>
        @empty
        <div style="text-align:center; padding:3rem; color:var(--mut); font-size:.9rem;">
            Belum ada data visite.
        </div>
        @endforelse
    </div>

    {{-- Modal Batalkan Confirm --}}
    @if($showBatalkanConfirm)
    <div style="position:fixed; inset:0; background:rgba(0,0,0,.6); z-index:200; display:flex; align-items:center; justify-content:center; padding:1rem;">
        <div class="glass-card" style="max-width:380px; width:100%; padding:1.5rem;">
            <div style="font-size:1rem; font-weight:700; margin-bottom:.75rem;">Batalkan Visite?</div>
            <div style="font-size:.88rem; color:var(--mut); margin-bottom:1.25rem;">Tindakan ini tidak bisa dibatalkan. Status akan berubah menjadi <strong>Dibatalkan</strong>.</div>
            <div style="display:flex; gap:.75rem;">
                <button wire:click="batalkan"
                        style="flex:1; background:var(--red); color:white; border:none; border-radius:.6rem; padding:.75rem; font-weight:700; cursor:pointer; min-height:44px;">
                    Ya, Batalkan
                </button>
                <button wire:click="$set('showBatalkanConfirm', false)"
                        style="flex:1; background:transparent; border:1px solid var(--line2); color:var(--ink); border-radius:.6rem; padding:.75rem; cursor:pointer; min-height:44px;">
                    Batal
                </button>
            </div>
        </div>
    </div>
    @endif

    {{-- Modal Form Tugaskan --}}
    @if($showForm)
    <div style="position:fixed; inset:0; background:rgba(0,0,0,.7); z-index:200; display:flex; align-items:flex-start; justify-content:center; padding:1rem; overflow-y:auto;">
        <div class="glass-card" style="max-width:520px; width:100%; margin:2rem auto; padding:1.75rem;">
            <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:1.25rem;">
                <div class="font-heading" style="font-size:1.2rem;">Tugaskan Home Visite</div>
                <button wire:click="cancel" style="background:transparent; border:none; color:var(--mut); cursor:pointer; padding:.25rem;">
                    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                </button>
            </div>

            <div style="display:flex; flex-direction:column; gap:1rem;">
                {{-- Cari Pasien --}}
                <div>
                    <label style="font-size:.78rem; color:var(--mut); display:block; margin-bottom:.35rem;">Pasien</label>
                    <div x-data="{ open: false }" style="position:relative;">
                        <input type="text" wire:model.live.debounce.300ms="pasienSearch"
                               @focus="open = true" @click.outside="open = false"
                               placeholder="Cari nama / No. BPJS..."
                               style="width:100%; background:var(--card); border:1px solid var(--line2); color:var(--ink); border-radius:.5rem; padding:.65rem .85rem; font-size:.88rem;">
                        @error('pasien_id') <div style="color:var(--red); font-size:.78rem; margin-top:.25rem;">{{ $message }}</div> @enderror

                        @if($pasienNama && $pasien_id)
                        <div style="font-size:.8rem; color:var(--emer); margin-top:.3rem;">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round" style="display:inline-block;vertical-align:middle"><polyline points="20 6 9 17 4 12"/></svg> {{ $pasienNama }}
                        </div>
                        @endif

                        <div x-show="open && $wire.pasienList.length > 0"
                             style="position:absolute; top:100%; left:0; right:0; background:var(--panel); border:1px solid var(--line2); border-radius:.5rem; z-index:50; overflow:hidden; margin-top:.25rem;">
                            @foreach($this->pasienList as $p)
                            <button type="button" wire:click="selectPasien({{ $p->id }}, '{{ addslashes($p->nama) }}', '{{ addslashes($p->alamat ?? '') }}')"
                                    @click="open = false"
                                    style="width:100%; text-align:left; background:transparent; border:none; border-bottom:1px solid var(--line); padding:.7rem 1rem; cursor:pointer; color:var(--ink); font-size:.85rem;">
                                <div>{{ $p->nama }}</div>
                                <div style="font-size:.75rem; color:var(--mut);">{{ $p->no_bpjs }}</div>
                            </button>
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- Pilih Kurir --}}
                <div>
                    <label style="font-size:.78rem; color:var(--mut); display:block; margin-bottom:.35rem;">Ditugaskan Kepada</label>
                    <select wire:model="assigned_to"
                            style="width:100%; background:var(--card); border:1px solid var(--line2); color:var(--ink); border-radius:.5rem; padding:.65rem .85rem; font-size:.88rem;">
                        <option value="">-- Pilih Karyawan --</option>
                        @foreach($this->kurirList as $k)
                        <option value="{{ $k->id }}">{{ $k->name }} ({{ ucfirst($k->role) }})</option>
                        @endforeach
                    </select>
                    @error('assigned_to') <div style="color:var(--red); font-size:.78rem; margin-top:.25rem;">{{ $message }}</div> @enderror
                </div>

                {{-- Tanggal --}}
                <div>
                    <label style="font-size:.78rem; color:var(--mut); display:block; margin-bottom:.35rem;">Tanggal Visite</label>
                    <input type="date" wire:model="tanggal_visite"
                           style="width:100%; background:var(--card); border:1px solid var(--line2); color:var(--ink); border-radius:.5rem; padding:.65rem .85rem; font-size:.88rem;">
                    @error('tanggal_visite') <div style="color:var(--red); font-size:.78rem; margin-top:.25rem;">{{ $message }}</div> @enderror
                </div>

                {{-- Alamat Tujuan --}}
                <div>
                    <label style="font-size:.78rem; color:var(--mut); display:block; margin-bottom:.35rem;">Alamat Tujuan</label>
                    <textarea wire:model="alamat_tujuan" rows="2"
                              placeholder="Jl. Contoh No. 123, Kediri..."
                              style="width:100%; background:var(--card); border:1px solid var(--line2); color:var(--ink); border-radius:.5rem; padding:.65rem .85rem; font-size:.88rem; resize:none;"></textarea>
                    @error('alamat_tujuan') <div style="color:var(--red); font-size:.78rem; margin-top:.25rem;">{{ $message }}</div> @enderror
                </div>

                {{-- Koordinat (opsional) --}}
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:.75rem;">
                    <div>
                        <label style="font-size:.78rem; color:var(--mut); display:block; margin-bottom:.35rem;">Latitude (opsional)</label>
                        <input type="text" wire:model="lat_tujuan" placeholder="-7.816"
                               style="width:100%; background:var(--card); border:1px solid var(--line2); color:var(--ink); border-radius:.5rem; padding:.65rem .85rem; font-size:.88rem;">
                    </div>
                    <div>
                        <label style="font-size:.78rem; color:var(--mut); display:block; margin-bottom:.35rem;">Longitude (opsional)</label>
                        <input type="text" wire:model="lng_tujuan" placeholder="112.016"
                               style="width:100%; background:var(--card); border:1px solid var(--line2); color:var(--ink); border-radius:.5rem; padding:.65rem .85rem; font-size:.88rem;">
                    </div>
                </div>

                {{-- Catatan Admin --}}
                <div>
                    <label style="font-size:.78rem; color:var(--mut); display:block; margin-bottom:.35rem;">Catatan (opsional)</label>
                    <textarea wire:model="catatan_admin" rows="2"
                              placeholder="Instruksi khusus untuk karyawan..."
                              style="width:100%; background:var(--card); border:1px solid var(--line2); color:var(--ink); border-radius:.5rem; padding:.65rem .85rem; font-size:.88rem; resize:none;"></textarea>
                </div>

                {{-- Tombol --}}
                <div style="display:flex; gap:.75rem; margin-top:.25rem;">
                    <button wire:click="save" wire:loading.attr="disabled"
                            style="flex:1; background:var(--emer); color:#0a1410; border:none; border-radius:.6rem; padding:.75rem; font-weight:700; cursor:pointer; min-height:44px; font-size:.9rem;"
                            wire:loading.class="opacity-60">
                        <span wire:loading.remove wire:target="save">Simpan Tugas</span>
                        <span wire:loading wire:target="save">Menyimpan...</span>
                    </button>
                    <button wire:click="cancel"
                            style="flex:1; background:transparent; border:1px solid var(--line2); color:var(--ink); border-radius:.6rem; padding:.75rem; cursor:pointer; min-height:44px; font-size:.9rem;">
                        Batal
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
