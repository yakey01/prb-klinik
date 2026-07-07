<div x-data="{
    drawerOpen: false,
    drawerTab: 'resep'
}"
@open-drawer.window="drawerOpen = true; drawerTab = 'resep'"
@close-drawer.window="drawerOpen = false">

{{-- ===================== KPI CARDS ===================== --}}
<div class="grid-kpi">
    <div class="kpi-card" style="border-color:rgba(63,207,142,.25);">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:.5rem;">
            <span style="font-size:.65rem;color:var(--mut);text-transform:uppercase;letter-spacing:.06em;font-weight:600;">Total Aktif</span>
            <span style="width:28px;height:28px;border-radius:50%;background:rgba(63,207,142,.12);display:flex;align-items:center;justify-content:center;">
                <svg width="13" height="13" fill="none" stroke="var(--emer)" stroke-width="2.2" viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
            </span>
        </div>
        <div class="font-heading" style="font-size:2rem;color:var(--emer);line-height:1;">{{ $this->stats['total_aktif'] ?? 0 }}</div>
        <div style="font-size:.68rem;color:var(--mut);margin-top:.25rem;">Pasien terdaftar & aktif</div>
    </div>
    <div class="kpi-card" style="border-color:rgba(217,164,65,.25);">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:.5rem;">
            <span style="font-size:.65rem;color:var(--mut);text-transform:uppercase;letter-spacing:.06em;font-weight:600;">Jadwal Hari Ini</span>
            <span style="width:28px;height:28px;border-radius:50%;background:rgba(217,164,65,.12);display:flex;align-items:center;justify-content:center;">
                <svg width="13" height="13" fill="none" stroke="var(--gold2)" stroke-width="2.2" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
            </span>
        </div>
        <div class="font-heading" style="font-size:2rem;color:var(--gold2);line-height:1;">{{ $this->stats['jadwal_hari_ini'] ?? 0 }}</div>
        <div style="font-size:.68rem;color:var(--mut);margin-top:.25rem;">Pengambilan dijadwalkan</div>
    </div>
    <div class="kpi-card" style="{{ ($this->stats['terlambat'] ?? 0) > 0 ? 'border-color:rgba(232,100,90,.3);' : '' }}">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:.5rem;">
            <span style="font-size:.65rem;color:var(--mut);text-transform:uppercase;letter-spacing:.06em;font-weight:600;">Terlambat</span>
            <span style="width:28px;height:28px;border-radius:50%;background:rgba(232,100,90,.1);display:flex;align-items:center;justify-content:center;">
                <svg width="13" height="13" fill="none" stroke="var(--red)" stroke-width="2.2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
            </span>
        </div>
        <div class="font-heading" style="font-size:2rem;line-height:1;color:{{ ($this->stats['terlambat'] ?? 0) > 0 ? 'var(--red)' : 'var(--mut)' }};">{{ $this->stats['terlambat'] ?? 0 }}</div>
        <div style="font-size:.68rem;color:var(--mut);margin-top:.25rem;">Jadwal terlewat</div>
    </div>
    <div class="kpi-card" style="border-color:rgba(111,177,224,.2);">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:.5rem;">
            <span style="font-size:.65rem;color:var(--mut);text-transform:uppercase;letter-spacing:.06em;font-weight:600;">Baru Bulan Ini</span>
            <span style="width:28px;height:28px;border-radius:50%;background:rgba(111,177,224,.1);display:flex;align-items:center;justify-content:center;">
                <svg width="13" height="13" fill="none" stroke="var(--blue)" stroke-width="2.2" viewBox="0 0 24 24"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="19" y1="8" x2="19" y2="14"/><line x1="22" y1="11" x2="16" y2="11"/></svg>
            </span>
        </div>
        <div class="font-heading" style="font-size:2rem;color:var(--blue);line-height:1;">{{ $this->stats['baru_bulan_ini'] ?? 0 }}</div>
        <div style="font-size:.68rem;color:var(--mut);margin-top:.25rem;">Pasien baru didaftarkan</div>
    </div>
</div>

{{-- ===================== FILTER BAR ===================== --}}
<div style="display:flex;align-items:center;gap:.75rem;margin-bottom:1.25rem;flex-wrap:wrap;">
    <div style="position:relative;flex:1;min-width:200px;">
        <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="position:absolute;left:.75rem;top:50%;transform:translateY(-50%);color:var(--mut);pointer-events:none;"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
        <input wire:model.live.debounce.300ms="search" type="text" placeholder="Cari nama atau BPJS..." class="form-input" style="padding-left:2.2rem;">
    </div>
    <select wire:model.live="filterDiagnosis" class="form-input" style="width:auto;min-width:170px;">
        <option value="">Semua Diagnosis</option>
        @foreach($this->diagnosisList as $d)
        <option value="{{ $d }}">{{ $d }}</option>
        @endforeach
    </select>
    <div style="display:flex;gap:0;border:1px solid var(--line2);border-radius:.45rem;overflow:hidden;">
        <button wire:click="$set('filterStatus','aktif')" style="padding:.45rem .9rem;font-size:.78rem;border:none;cursor:pointer;transition:all .15s;{{ $filterStatus==='aktif' ? 'background:var(--gold);color:#1a0e00;font-weight:600;' : 'background:transparent;color:var(--mut);' }}">Aktif</button>
        <button wire:click="$set('filterStatus','semua')" style="padding:.45rem .9rem;font-size:.78rem;border:none;cursor:pointer;transition:all .15s;{{ $filterStatus==='semua' ? 'background:var(--gold);color:#1a0e00;font-weight:600;' : 'background:transparent;color:var(--mut);' }}">Semua</button>
    </div>
    <button wire:click="$toggle('showKebutuhan')"
        style="padding:.45rem .85rem;font-size:.78rem;border-radius:.4rem;border:1px solid;cursor:pointer;transition:all .15s;{{ $showKebutuhan ? 'background:rgba(111,177,224,.15);border-color:rgba(111,177,224,.4);color:var(--blue);' : 'background:transparent;border-color:var(--line);color:var(--mut);' }}">
        <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="display:inline;vertical-align:middle;margin-right:.3rem;"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>Kebutuhan
    </button>
    <button wire:click="openAdd" class="btn-gold" style="white-space:nowrap;">
        <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        Tambah Pasien
    </button>
</div>

{{-- ===================== ADD / EDIT FORM ===================== --}}
@if($showForm)
<div class="glass-card" style="padding:1.25rem;margin-bottom:1.25rem;border-color:rgba(217,164,65,.35);">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1rem;">
        <h3 class="font-heading" style="font-size:.95rem;color:var(--gold2);margin:0;">
            {{ $editId ? 'Edit Pasien' : 'Tambah Pasien Baru' }}
        </h3>
        <button wire:click="cancel" style="background:none;border:none;color:var(--mut);cursor:pointer;font-size:1.2rem;line-height:1;padding:.25rem;">&times;</button>
    </div>

    @if($errors->any())
    <div style="margin-bottom:.9rem;padding:.75rem 1rem;background:rgba(232,100,90,.1);border:1px solid rgba(232,100,90,.35);border-radius:.5rem;display:flex;align-items:flex-start;gap:.6rem;">
        <svg width="15" height="15" fill="none" stroke="var(--red)" stroke-width="2" viewBox="0 0 24 24" style="flex-shrink:0;margin-top:.1rem;"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
        <div>
            <div style="font-size:.72rem;font-weight:700;color:var(--red);margin-bottom:.3rem;">Perbaiki kesalahan berikut sebelum menyimpan:</div>
            <ul style="margin:0;padding-left:1rem;font-size:.71rem;color:var(--red);opacity:.9;">
                @foreach($errors->all() as $err)
                <li style="margin-bottom:.1rem;">{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    </div>
    @endif
    <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:.75rem;">
        {{-- Nama --}}
        <div>
            <label class="form-label">Nama Lengkap <span style="color:var(--red);">*</span></label>
            <input wire:model="nama" type="text" class="form-input" placeholder="Nama pasien">
            @error('nama')<div style="color:var(--red);font-size:.68rem;margin-top:.15rem;">{{ $message }}</div>@enderror
        </div>
        {{-- No. BPJS --}}
        <div>
            <label class="form-label">No. BPJS <span style="color:var(--red);">*</span> <span style="color:var(--mut);font-weight:400;">(13 digit)</span></label>
            <input wire:model="no_bpjs" type="text" inputmode="numeric" maxlength="13"
                   class="form-input font-mono" placeholder="0001234567890" style="font-size:.78rem;"
                   wire:keydown="$set('no_bpjs', $event.target.value.replace(/\D/g,'').substring(0,13))">
            @error('no_bpjs')<div style="color:var(--red);font-size:.68rem;margin-top:.15rem;">{{ $message }}</div>@enderror
        </div>
        {{-- Diagnosis --}}
        <div>
            <label class="form-label">Diagnosis</label>
            <select wire:model="kategori_diagnosis" class="form-input">
                <option value="">— Pilih Diagnosis —</option>
                @foreach($this->diagnosisList as $d)
                <option value="{{ $d }}">{{ $d }}</option>
                @endforeach
            </select>
        </div>
        {{-- Tanggal Lahir --}}
        <div>
            <label class="form-label">Tanggal Lahir <span style="color:var(--red);">*</span></label>
            <input wire:model="tanggal_lahir" type="date" class="form-input" max="{{ date('Y-m-d') }}">
            @error('tanggal_lahir')<div style="color:var(--red);font-size:.68rem;margin-top:.15rem;">{{ $message }}</div>@enderror
        </div>
        {{-- Jenis Kelamin --}}
        <div>
            <label class="form-label">Jenis Kelamin <span style="color:var(--red);">*</span></label>
            <select wire:model="jenis_kelamin" class="form-input">
                <option value="">— Pilih —</option>
                <option value="L">Laki-laki</option>
                <option value="P">Perempuan</option>
            </select>
            @error('jenis_kelamin')<div style="color:var(--red);font-size:.68rem;margin-top:.15rem;">{{ $message }}</div>@enderror
        </div>
        {{-- Telepon --}}
        <div>
            <label class="form-label">No. Handphone <span style="color:var(--mut);font-size:.7rem;font-weight:400;">(opsional)</span></label>
            <input wire:model="telepon" type="text" inputmode="numeric" maxlength="15"
                   class="form-input" placeholder="08123456789">
            @error('telepon')<div style="color:var(--red);font-size:.68rem;margin-top:.15rem;">{{ $message }}</div>@enderror
        </div>
        {{-- Alamat --}}
        <div style="grid-column:span 2;">
            <label class="form-label">Alamat <span style="color:var(--red);">*</span></label>
            <input wire:model="alamat" type="text" class="form-input" placeholder="Alamat lengkap sesuai KTP">
            @error('alamat')<div style="color:var(--red);font-size:.68rem;margin-top:.15rem;">{{ $message }}</div>@enderror
        </div>
        {{-- Catatan --}}
        <div>
            <label class="form-label">Catatan</label>
            <input wire:model="catatan" type="text" class="form-input" placeholder="Catatan tambahan">
        </div>
    </div>

    {{-- ── Jadwal Ambil Obat ─────────────────────────────────────────── --}}
    <div style="margin-top:.9rem;padding:.85rem;background:rgba(217,164,65,.07);border:1px solid rgba(217,164,65,.25);border-radius:.6rem;">
        <div style="font-size:.72rem;font-weight:700;color:var(--gold2);text-transform:uppercase;letter-spacing:.05em;margin-bottom:.6rem;">
            📅 Jadwal Ambil Obat Berikutnya
        </div>
        <div style="display:grid;grid-template-columns:1fr 2fr;gap:.75rem;align-items:end;">
            <div>
                <label class="form-label">Tanggal Pengambilan</label>
                <input wire:model="jadwal_ambil_obat" type="date" class="form-input"
                       min="{{ date('Y-m-d') }}">
                @error('jadwal_ambil_obat')<div style="color:var(--red);font-size:.68rem;margin-top:.15rem;">{{ $message }}</div>@enderror
            </div>
            <div style="font-size:.72rem;color:var(--mut);padding-bottom:.25rem;line-height:1.5;">
                Jadwal ini akan tersimpan ke halaman <strong style="color:var(--ink);">Jadwal Pasien</strong>
                dan muncul di reminder pengambilan obat kronis.
                @if($jadwal_ambil_obat)
                <br><span style="color:var(--gold2);">
                    ≈ {{ \Carbon\Carbon::parse($jadwal_ambil_obat)->diffForHumans() }}
                    ({{ \Carbon\Carbon::parse($jadwal_ambil_obat)->locale('id')->translatedFormat('d F Y') }})
                </span>
                @endif
            </div>
        </div>
    </div>

    {{-- ── Resep Obat Awal (hanya untuk tambah pasien baru) ─────────────────── --}}
    @if(!$editId)
    <div style="margin-top:.9rem;padding:.85rem;background:rgba(63,207,142,.05);border:1px solid rgba(63,207,142,.2);border-radius:.6rem;">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:.7rem;">
            <div style="font-size:.72rem;font-weight:700;color:var(--emer);text-transform:uppercase;letter-spacing:.05em;">
                💊 Resep Obat Rutin
                <span style="font-size:.65rem;color:var(--mut);font-weight:400;text-transform:none;margin-left:.4rem;">(opsional)</span>
            </div>
        </div>
        <div style="display:flex;flex-direction:column;gap:.4rem;margin-bottom:.6rem;">
            @foreach($formResepRows as $fi => $frow)
            <div wire:key="formResep-{{ $fi }}" style="display:flex;align-items:center;gap:.4rem;padding:.55rem .65rem;background:rgba(255,255,255,.04);border:1px solid var(--line2);border-radius:.4rem;">
                <div style="flex:1;min-width:0;">
                    <select wire:model.live="formResepRows.{{ $fi }}.obat_id" class="form-input" style="font-size:.76rem;padding:.32rem .45rem;background:var(--card);border-color:var(--line2);">
                        <option value="0">— Pilih Obat —</option>
                        @foreach($this->obatList as $ob)
                        <option value="{{ $ob->id }}">{{ $ob->nama_obat }}</option>
                        @endforeach
                    </select>
                </div>
                <div style="display:flex;align-items:center;gap:.3rem;flex-shrink:0;">
                    <input wire:model="formResepRows.{{ $fi }}.jumlah_default" type="number" min="1" max="999"
                        class="form-input font-mono" placeholder="30"
                        style="width:58px;font-size:.82rem;padding:.32rem .4rem;text-align:center;background:var(--card);border-color:var(--line2);">
                    <select wire:model="formResepRows.{{ $fi }}.satuan" class="form-input" style="width:82px;font-size:.72rem;padding:.32rem .35rem;background:var(--card);border-color:var(--line2);">
                        <option value="tablet">Tablet</option>
                        <option value="kapsul">Kapsul</option>
                        <option value="botol">Botol</option>
                        <option value="sachet">Sachet</option>
                        <option value="strip">Strip</option>
                        <option value="tube">Tube</option>
                        <option value="mg">mg</option>
                        <option value="ml">ml</option>
                    </select>
                    @if(count($formResepRows) > 1)
                    <button type="button" wire:click="removeFormResepRow({{ $fi }})"
                        style="width:26px;height:26px;background:rgba(232,100,90,.08);border:1px solid rgba(232,100,90,.2);color:var(--red);border-radius:.3rem;cursor:pointer;display:flex;align-items:center;justify-content:center;flex-shrink:0;font-size:1rem;">&times;</button>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
        <button type="button" wire:click="addFormResepRow"
            style="width:100%;padding:.38rem;background:transparent;border:1px dashed rgba(63,207,142,.3);color:var(--mut);border-radius:.4rem;cursor:pointer;font-size:.73rem;display:flex;align-items:center;justify-content:center;gap:.3rem;transition:all .2s;"
            onmouseover="this.style.borderColor='var(--emer)';this.style.color='var(--emer)'" onmouseout="this.style.borderColor='rgba(63,207,142,.3)';this.style.color='var(--mut)'">
            <svg width="10" height="10" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>Tambah Obat
        </button>
    </div>
    @endif

    <div style="display:flex;gap:.6rem;margin-top:1rem;justify-content:flex-end;align-items:center;">
        <span style="font-size:.68rem;color:var(--mut);">* wajib diisi</span>
        <button wire:click="cancel" class="btn-outline">Batal</button>
        <button wire:click="save" class="btn-gold" wire:loading.attr="disabled">
            <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
            <span wire:loading.remove>Simpan</span>
            <span wire:loading>Menyimpan...</span>
        </button>
    </div>
</div>
@endif

{{-- ===================== KEBUTUHAN BULANAN (toggle) ===================== --}}
@if($showKebutuhan)
<div class="glass-card" style="overflow:hidden;margin-bottom:1.25rem;border-color:rgba(111,177,224,.3);">
    <div style="padding:.85rem 1.2rem;border-bottom:1px solid var(--line);display:flex;align-items:center;justify-content:space-between;">
        <span style="font-size:.75rem;color:var(--blue);text-transform:uppercase;letter-spacing:.06em;font-weight:600;">Estimasi Kebutuhan Obat Bulanan</span>
        <span style="font-size:.7rem;color:var(--mut);">Berdasarkan rata-rata pasien aktif</span>
    </div>
    @if($this->kebutuhanObatBulanan->isEmpty())
    <div style="padding:2rem;text-align:center;color:var(--mut);font-size:.82rem;">Belum ada data pengambilan obat tercatat.</div>
    @else
    <table class="data-table">
        <thead><tr>
            <th>Nama Obat</th><th style="text-align:center;">Satuan</th>
            <th style="text-align:center;">Pasien</th><th style="text-align:center;">Kebutuhan per Pasien</th>
            <th style="text-align:right;">Kebutuhan Bulanan</th>
        </tr></thead>
        <tbody>
            @foreach($this->kebutuhanObatBulanan as $row)
            <tr>
                <td style="font-size:.83rem;">{{ $row->nama_obat }}</td>
                <td style="text-align:center;"><span style="font-size:.68rem;padding:.12rem .45rem;border-radius:.3rem;background:rgba(31,61,48,.6);border:1px solid var(--line);color:var(--mut);">{{ $row->satuan ?: 'tablet' }}</span></td>
                <td style="text-align:center;"><span style="font-size:.7rem;background:rgba(63,207,142,.1);border:1px solid rgba(63,207,142,.2);color:var(--emer2);border-radius:999px;padding:.12rem .45rem;">{{ $row->jumlah_pasien }}</span></td>
                <td class="font-mono" style="text-align:center;font-size:.8rem;color:var(--mut);">{{ $row->rata_unit_per_kunjungan }}</td>
                <td class="font-mono" style="text-align:right;font-size:.9rem;font-weight:700;color:var(--gold2);">{{ number_format($row->estimasi_bulanan) }} <span style="font-size:.68rem;color:var(--mut);font-weight:400;">{{ $row->satuan ?: 'tablet' }}</span></td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif
</div>
@endif

{{-- ===================== PATIENT TABLE ===================== --}}
<div class="glass-card" style="overflow-x:auto;padding:0;">
    <table class="data-table" style="width:100%;">
        <thead>
            <tr>
                <th style="padding:.8rem 1rem;text-align:left;">Pasien</th>
                <th style="padding:.8rem 1rem;text-align:left;width:155px;">Diagnosis</th>
                <th style="padding:.8rem 1rem;text-align:left;min-width:220px;">Resep Obat</th>
                <th style="padding:.8rem 1rem;text-align:left;width:160px;">Jadwal Berikutnya</th>
                <th style="padding:.8rem 1rem;text-align:left;width:150px;">Terakhir Ambil</th>
                <th style="padding:.8rem 1rem;text-align:center;width:80px;">Status</th>
                <th style="padding:.8rem 1rem;text-align:right;width:175px;">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($this->pasienList as $pasien)
            @php
                $jadwal = $this->jadwalPerPasien[$pasien->id] ?? null;
                $lastPickup = $this->lastPickupPerPasien[$pasien->id] ?? null;
                $initial = strtoupper(substr($pasien->nama, 0, 1));
                $avatarColors = ['#3fcf8e','#d9a441','#6fb1e0','#e0a46f','#cf3f7a','#9e6fe0'];
                $avatarColor = $avatarColors[abs(crc32($pasien->nama) % count($avatarColors))];
                if ($pasien->tanggal_lahir) {
                    try { $age = \Carbon\Carbon::parse($pasien->tanggal_lahir)->age; } catch(\Exception $e) { $age = null; }
                } else { $age = null; }
            @endphp
            <tr style="border-bottom:1px solid rgba(31,61,48,.5);transition:background .12s;" onmouseover="this.style.background='rgba(255,255,255,.018)'" onmouseout="this.style.background='transparent'">
                {{-- Pasien --}}
                <td style="padding:.75rem 1rem;">
                    <div style="display:flex;align-items:center;gap:.7rem;">
                        <div style="width:36px;height:36px;border-radius:50%;background:{{ $avatarColor }}1a;border:1.5px solid {{ $avatarColor }}44;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                            <span style="color:{{ $avatarColor }};font-weight:700;font-size:.8rem;">{{ $initial }}</span>
                        </div>
                        <div>
                            <div wire:click="openDrawer({{ $pasien->id }})"
                                 style="font-weight:600;font-size:.85rem;color:var(--ink);cursor:pointer;transition:color .15s;display:inline-block;"
                                 onmouseover="this.style.color='var(--emer2)';this.style.textDecoration='underline'"
                                 onmouseout="this.style.color='var(--ink)';this.style.textDecoration='none'"
                                 title="Klik untuk lihat rekam medis">{{ $pasien->nama }}</div>
                            <div class="font-mono" style="font-size:.68rem;color:var(--mut2);">{{ $pasien->no_bpjs ?: '—' }}</div>
                            @if($age || $pasien->jenis_kelamin)
                            <div style="font-size:.66rem;color:var(--mut);margin-top:.1rem;">
                                @if($age){{ $age }} thn @endif
                                @if($age && $pasien->jenis_kelamin) &middot; @endif
                                @if($pasien->jenis_kelamin) {{ $pasien->jenis_kelamin === 'L' ? 'L' : 'P' }} @endif
                            </div>
                            @endif
                        </div>
                    </div>
                </td>
                {{-- Diagnosis --}}
                <td style="padding:.75rem 1rem;">
                    @if($pasien->kategori_diagnosis)
                    <span style="font-size:.67rem;padding:.2rem .55rem;border-radius:999px;background:rgba(217,164,65,.1);border:1px solid rgba(217,164,65,.22);color:var(--gold2);">{{ $pasien->kategori_diagnosis }}</span>
                    @else
                    <span style="color:var(--mut);font-size:.78rem;">—</span>
                    @endif
                </td>
                {{-- Resep Obat --}}
                <td style="padding:.75rem 1rem;">
                    @php $resepObat = $this->resepPerPasien[$pasien->id] ?? [] @endphp
                    @if(count($resepObat))
                    <div style="display:flex;flex-wrap:wrap;gap:.25rem;">
                        @foreach($resepObat as $ro)
                        <span style="font-size:.62rem;padding:.18rem .5rem;border-radius:.3rem;background:rgba(63,207,142,.07);border:1px solid rgba(63,207,142,.18);color:var(--emer2);white-space:nowrap;line-height:1.4;">
                            {{ $ro['nama'] }}<span style="color:var(--mut);margin-left:.25rem;">×{{ $ro['jumlah'] }}</span>
                        </span>
                        @endforeach
                    </div>
                    @else
                    <span style="color:var(--mut2);font-size:.72rem;font-style:italic;">Belum ada resep</span>
                    @endif
                </td>
                {{-- Jadwal Countdown --}}
                <td style="padding:.75rem 1rem;">
                    @if($jadwal)
                    @php
                        $jadwalArr = (array)$jadwal;
                        $tglStr = $jadwalArr['tanggal_pengambilan'] ?? null;
                        if($tglStr) {
                            $tglJadwal = \Carbon\Carbon::parse($tglStr)->startOfDay();
                            $diffDays = (int) now()->startOfDay()->diffInDays($tglJadwal, false);
                        } else { $diffDays = 0; }
                    @endphp
                    @if($diffDays < 0)
                        <span style="font-size:.7rem;padding:.22rem .6rem;border-radius:999px;background:rgba(232,100,90,.12);border:1px solid rgba(232,100,90,.3);color:var(--red);font-weight:600;">&#9888; Terlambat {{ abs($diffDays) }} hr</span>
                    @elseif($diffDays === 0)
                        <span style="font-size:.7rem;padding:.22rem .6rem;border-radius:999px;background:rgba(217,164,65,.15);border:1px solid rgba(217,164,65,.4);color:var(--gold2);font-weight:600;" class="pulse-badge">&#10022; Hari Ini</span>
                    @elseif($diffDays <= 7)
                        <span style="font-size:.7rem;padding:.22rem .6rem;border-radius:999px;background:rgba(217,164,65,.1);border:1px solid rgba(217,164,65,.25);color:var(--gold2);">{{ $diffDays }} hr lagi</span>
                    @else
                        <span style="font-size:.7rem;padding:.22rem .6rem;border-radius:999px;background:rgba(63,207,142,.08);border:1px solid rgba(63,207,142,.2);color:var(--emer);">{{ $diffDays }} hr lagi</span>
                    @endif
                    @else
                    <span style="color:var(--mut);font-size:.78rem;">—</span>
                    @endif
                </td>
                {{-- Terakhir --}}
                <td style="padding:.75rem 1rem;">
                    @if($lastPickup)
                    @php
                        $lpArr = (array)$lastPickup;
                        $lpTgl = \Carbon\Carbon::parse($lpArr['tanggal_pengambilan'] ?? now());
                    @endphp
                    <div style="font-size:.78rem;color:var(--ink);">{{ $lpTgl->format('d M Y') }}</div>
                    <div style="font-size:.68rem;color:var(--mut);">{{ $lpArr['total_item'] ?? 0 }} item</div>
                    @else
                    <span style="color:var(--mut);font-size:.75rem;">Belum ada</span>
                    @endif
                </td>
                {{-- Status --}}
                <td style="padding:.75rem 1rem;text-align:center;">
                    @if($pasien->is_aktif)
                    <span style="display:inline-flex;align-items:center;gap:.3rem;font-size:.7rem;color:var(--emer);">
                        <span style="width:7px;height:7px;border-radius:50%;background:var(--emer);box-shadow:0 0 5px rgba(63,207,142,.5);flex-shrink:0;"></span>Aktif
                    </span>
                    @else
                    <span style="display:inline-flex;align-items:center;gap:.3rem;font-size:.7rem;color:var(--mut);">
                        <span style="width:7px;height:7px;border-radius:50%;background:var(--mut);flex-shrink:0;"></span>Nonaktif
                    </span>
                    @endif
                </td>
                {{-- Aksi --}}
                <td style="padding:.75rem 1rem;text-align:right;">
                    <div style="display:flex;align-items:center;justify-content:flex-end;gap:.3rem;flex-wrap:wrap;">
                        <button wire:click="catat({{ $pasien->id }})"
                            style="background:rgba(217,164,65,.12);border:1px solid rgba(217,164,65,.3);color:var(--gold2);border-radius:.35rem;padding:.3rem .6rem;cursor:pointer;font-size:.7rem;font-weight:600;transition:all .15s;"
                            onmouseover="this.style.background='rgba(217,164,65,.25)'" onmouseout="this.style.background='rgba(217,164,65,.12)'" title="Catat Pengambilan">
                            <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24" style="display:inline;vertical-align:middle;margin-right:.2rem;"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>Catat
                        </button>
                        <button wire:click="openEdit({{ $pasien->id }})" class="btn-outline" style="padding:.28rem .55rem;font-size:.7rem;" title="Edit">
                            <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                        </button>
                        <button wire:click="openDrawer({{ $pasien->id }})"
                            style="background:rgba(63,207,142,.08);border:1px solid rgba(63,207,142,.2);color:var(--emer);border-radius:.35rem;padding:.28rem .55rem;cursor:pointer;font-size:.7rem;transition:all .15s;display:flex;align-items:center;gap:.25rem;"
                            onmouseover="this.style.background='rgba(63,207,142,.18)'" onmouseout="this.style.background='rgba(63,207,142,.08)'" title="Resep & Detail Pasien">
                            <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path d="M9 5H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2"/><rect x="9" y="3" width="6" height="4" rx="1"/><line x1="9" y1="12" x2="15" y2="12"/><line x1="9" y1="16" x2="13" y2="16"/></svg>
                            Resep
                        </button>
                        @php $isExpanded = $expandedRiwayatId === $pasien->id; @endphp
                        <button wire:click="toggleRiwayat({{ $pasien->id }})" wire:key="rwbtn-{{ $pasien->id }}"
                            style="background:{{ $isExpanded ? 'rgba(111,177,224,.22)' : 'rgba(111,177,224,.08)' }};border:1px solid rgba(111,177,224,.3);color:var(--blue);border-radius:.35rem;padding:.28rem .55rem;cursor:pointer;font-size:.7rem;transition:all .15s;display:flex;align-items:center;gap:.25rem;"
                            onmouseover="this.style.background='rgba(111,177,224,.2)'" onmouseout="this.style.background='{{ $isExpanded ? 'rgba(111,177,224,.22)' : 'rgba(111,177,224,.08)' }}'" title="Riwayat pengambilan obat + untung/rugi">
                            <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path d="M12 8v4l3 3"/><circle cx="12" cy="12" r="9"/></svg>
                            Riwayat
                            <svg width="10" height="10" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" style="transition:transform .2s;{{ $isExpanded ? 'transform:rotate(180deg);' : '' }}"><polyline points="6 9 12 15 18 9"/></svg>
                        </button>
                        <button wire:click="deletePasien({{ $pasien->id }})" wire:confirm="Hapus pasien '{{ $pasien->nama }}'? Data pengambilan juga ikut terhapus." class="btn-danger" style="padding:.28rem .4rem;font-size:.7rem;" title="Hapus">
                            <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14H6L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4h6v2"/></svg>
                        </button>
                    </div>
                </td>
            </tr>

            {{-- ═══ RIWAYAT PENGAMBILAN OBAT — accordion detail (untung/rugi per obat) ═══ --}}
            @if($expandedRiwayatId === $pasien->id)
            @php $rt = $this->riwayatTotals; @endphp
            <tr wire:key="rwdetail-{{ $pasien->id }}">
                <td colspan="7" style="padding:0;background:linear-gradient(180deg,rgba(111,177,224,.05),rgba(10,20,16,.2));border-bottom:1px solid rgba(31,61,48,.5);">
                    <div style="padding:1.1rem 1.25rem;">
                        {{-- Header + total kumulatif --}}
                        <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:.6rem;margin-bottom:1rem;">
                            <div style="display:flex;align-items:center;gap:.55rem;">
                                <svg width="15" height="15" fill="none" stroke="var(--blue)" stroke-width="2" viewBox="0 0 24 24"><path d="M12 8v4l3 3"/><circle cx="12" cy="12" r="9"/></svg>
                                <span style="font-size:.82rem;font-weight:700;color:var(--ink);">Riwayat Pengambilan Obat</span>
                                <span style="font-size:.72rem;color:var(--mut);">— {{ $pasien->nama }}</span>
                            </div>
                            <div style="display:flex;align-items:center;gap:.4rem;flex-wrap:wrap;">
                                <span style="font-size:.66rem;padding:.2rem .55rem;border-radius:999px;background:rgba(255,255,255,.04);border:1px solid var(--line);color:var(--mut);">{{ $rt['kunjungan'] }} kunjungan · {{ $rt['item'] }} item</span>
                                <span style="font-size:.66rem;padding:.2rem .55rem;border-radius:999px;background:rgba(232,100,90,.1);border:1px solid rgba(232,100,90,.25);color:var(--red2);">HPP Rp {{ number_format($rt['biaya'],0,',','.') }}</span>
                                <span style="font-size:.66rem;padding:.2rem .55rem;border-radius:999px;background:rgba(111,177,224,.1);border:1px solid rgba(111,177,224,.25);color:var(--blue);">Klaim Rp {{ number_format($rt['klaim'],0,',','.') }}</span>
                                @php $glColor = $rt['laba'] > 0 ? 'var(--emer2)' : ($rt['laba'] < 0 ? 'var(--red2)' : 'var(--gold2)'); $glBg = $rt['laba']>0?'rgba(63,207,142,.12)':($rt['laba']<0?'rgba(232,100,90,.12)':'rgba(217,164,65,.12)'); @endphp
                                <span style="font-size:.68rem;font-weight:800;padding:.2rem .6rem;border-radius:999px;background:{{ $glBg }};border:1px solid {{ $glColor }};color:{{ $glColor }};">
                                    {{ $rt['laba'] >= 0 ? 'LABA' : 'RUGI' }} Rp {{ number_format(abs($rt['laba']),0,',','.') }} · {{ number_format(abs($rt['margin']),1) }}%
                                </span>
                            </div>
                        </div>

                        @forelse($this->riwayatRows as $rw)
                        @php
                            $pBiaya = 0; $pKlaim = 0;
                            foreach($rw->items as $it){ $bu=(float)$it->harga_beli_snapshot; $ku=(float)$it->harga_klaim_bpjs_snapshot*\App\Models\Obat::jfMultiplier($it->faktor_jasa_farmasi_snapshot); $pBiaya+=$it->jumlah_unit*$bu; $pKlaim+=$it->jumlah_unit*$ku; }
                            $pLaba = $pKlaim - $pBiaya; $pColor = $pLaba>0?'var(--emer2)':($pLaba<0?'var(--red2)':'var(--gold2)');
                        @endphp
                        <div style="background:rgba(17,36,28,.5);border:1px solid var(--line);border-radius:.7rem;overflow:hidden;margin-bottom:.7rem;">
                            <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:.4rem;padding:.6rem .9rem;border-bottom:1px solid rgba(31,61,48,.5);">
                                <div style="display:flex;align-items:center;gap:.5rem;">
                                    <span class="font-mono" style="font-size:.74rem;color:var(--gold2);">{{ \Carbon\Carbon::parse($rw->tanggal_pengambilan)->format('d M Y') }}</span>
                                    <span style="font-size:.66rem;color:var(--mut);">{{ $rw->items->count() }} obat</span>
                                    @if($rw->sumber_resep==='rme')<span style="font-size:.6rem;padding:.05rem .4rem;border-radius:999px;background:rgba(167,139,250,.12);border:1px solid rgba(167,139,250,.3);color:#c4b5fd;">RME</span>@endif
                                </div>
                                <span style="font-size:.7rem;font-weight:700;color:{{ $pColor }};">{{ $pLaba>=0?'+':'−' }}Rp {{ number_format(abs($pLaba),0,',','.') }}</span>
                            </div>
                            <div style="overflow-x:auto;">
                            <table style="width:100%;border-collapse:collapse;font-size:.72rem;">
                                <thead>
                                    <tr style="color:var(--mut);">
                                        <th style="text-align:left;padding:.4rem .9rem;font-weight:600;font-size:.62rem;text-transform:uppercase;letter-spacing:.04em;">Obat</th>
                                        <th style="text-align:right;padding:.4rem .6rem;font-weight:600;font-size:.62rem;text-transform:uppercase;">Jml</th>
                                        <th style="text-align:right;padding:.4rem .6rem;font-weight:600;font-size:.62rem;text-transform:uppercase;">Beli/unit</th>
                                        <th style="text-align:right;padding:.4rem .6rem;font-weight:600;font-size:.62rem;text-transform:uppercase;">Klaim/unit</th>
                                        <th style="text-align:right;padding:.4rem .6rem;font-weight:600;font-size:.62rem;text-transform:uppercase;">HPP</th>
                                        <th style="text-align:right;padding:.4rem .6rem;font-weight:600;font-size:.62rem;text-transform:uppercase;">Klaim</th>
                                        <th style="text-align:right;padding:.4rem .9rem;font-weight:600;font-size:.62rem;text-transform:uppercase;">Laba/Rugi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($rw->items as $it)
                                    @php
                                        $bu=(float)$it->harga_beli_snapshot; $ku=(float)$it->harga_klaim_bpjs_snapshot*\App\Models\Obat::jfMultiplier($it->faktor_jasa_farmasi_snapshot);
                                        $hpp=$it->jumlah_unit*$bu; $klm=$it->jumlah_unit*$ku; $lb=$klm-$hpp;
                                        $lc=$lb>0?'var(--emer2)':($lb<0?'var(--red2)':'var(--mut)');
                                    @endphp
                                    <tr style="border-top:1px solid rgba(31,61,48,.35);">
                                        <td style="padding:.4rem .9rem;color:var(--ink);">{{ $it->obat->nama_obat ?? '—' }}</td>
                                        <td class="font-mono" style="padding:.4rem .6rem;text-align:right;color:var(--mut);">{{ $it->jumlah_unit }} {{ $it->satuan }}</td>
                                        <td class="font-mono" style="padding:.4rem .6rem;text-align:right;color:var(--red2);">{{ number_format($bu,0,',','.') }}</td>
                                        <td class="font-mono" style="padding:.4rem .6rem;text-align:right;color:var(--blue);">{{ number_format($ku,0,',','.') }}</td>
                                        <td class="font-mono" style="padding:.4rem .6rem;text-align:right;color:var(--mut);">{{ number_format($hpp,0,',','.') }}</td>
                                        <td class="font-mono" style="padding:.4rem .6rem;text-align:right;color:var(--ink);">{{ number_format($klm,0,',','.') }}</td>
                                        <td class="font-mono" style="padding:.4rem .9rem;text-align:right;font-weight:700;color:{{ $lc }};">{{ $lb>=0?'+':'−' }}{{ number_format(abs($lb),0,',','.') }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                            </div>
                        </div>
                        @empty
                        <div style="padding:1.5rem;text-align:center;color:var(--mut);font-size:.78rem;border:1px dashed var(--line);border-radius:.6rem;">
                            Belum ada pengambilan obat selesai untuk pasien ini.
                        </div>
                        @endforelse
                    </div>
                </td>
            </tr>
            @endif
            @empty
            <tr>
                <td colspan="7" style="text-align:center;padding:3rem 1rem;">
                    <div style="width:48px;height:48px;border-radius:50%;background:rgba(255,255,255,.04);display:flex;align-items:center;justify-content:center;margin:0 auto .75rem;">
                        <svg width="22" height="22" fill="none" stroke="var(--mut)" stroke-width="1.5" viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
                    </div>
                    <div style="color:var(--mut);font-size:.84rem;">Tidak ada pasien ditemukan.</div>
                    <div style="color:var(--mut2);font-size:.74rem;margin-top:.25rem;">Coba ubah filter atau tambah pasien baru.</div>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

{{-- Pagination --}}
<div style="margin-top:.75rem;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:.5rem;">
    <div style="font-size:.73rem;color:var(--mut2);">{{ $this->pasienList->total() }} total pasien</div>
    <div>{{ $this->pasienList->links() }}</div>
</div>

{{-- ===================== RIGHT SIDE DRAWER ===================== --}}
<div x-show="drawerOpen" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150"
    x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
    @click="drawerOpen=false; $wire.closeDrawer()"
    style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.55);z-index:95;backdrop-filter:blur(2px);"></div>

<div x-show="drawerOpen"
    x-transition:enter="transition ease-out duration-250"
    x-transition:enter-start="transform translate-x-full"
    x-transition:enter-end="transform translate-x-0"
    x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="transform translate-x-0"
    x-transition:leave-end="transform translate-x-full"
    class="prb-drawer"
    style="display:none;position:fixed;top:0;right:0;bottom:0;width:500px;max-width:98vw;background:var(--panel);border-left:1px solid var(--line2);z-index:100;overflow:hidden;box-shadow:-12px 0 60px rgba(0,0,0,.6);">

    @if($this->drawerPasien)
    @php
        $dp = $this->drawerPasien;
        $dpInitial = strtoupper(substr($dp->nama, 0, 1));
        $dpColors = ['#3fcf8e','#d9a441','#6fb1e0','#e0a46f','#cf3f7a','#9e6fe0'];
        $dpColor = $dpColors[abs(crc32($dp->nama) % count($dpColors))];
        if ($dp->tanggal_lahir) {
            try { $dpAge = \Carbon\Carbon::parse($dp->tanggal_lahir)->age; } catch(\Exception $e) { $dpAge = null; }
        } else { $dpAge = null; }
    @endphp

    {{-- ══ DRAWER HEADER — Rekam Medis ══════════════════════════════════════ --}}
    @php
        $dStats  = $this->drawerStats;
        $dPnl    = $this->drawerPnl;
        $dPlus   = ($dPnl['total_laba'] ?? 0) >= 0;
        $dHasPnl = ($dPnl['count'] ?? 0) > 0;

        // Jadwal countdown
        $dJadwal = $dStats['jadwal'] ?? null;
        if ($dJadwal) {
            $dJadwalCarbon = \Carbon\Carbon::parse($dJadwal);
            $dJadwalDiff   = (int) now()->startOfDay()->diffInDays($dJadwalCarbon->startOfDay(), false);
        }
    @endphp
    <div style="flex-shrink:0;border-bottom:1px solid var(--line);background:var(--card);">

        {{-- ── Baris 1: Avatar + Identitas + Close ──────────────────── --}}
        <div style="padding:1.35rem 1.35rem .9rem;display:flex;align-items:flex-start;justify-content:space-between;gap:.85rem;">
            <div style="display:flex;align-items:flex-start;gap:1rem;flex:1;min-width:0;">
                {{-- Avatar dengan status dot --}}
                <div style="position:relative;flex-shrink:0;">
                    <div style="width:64px;height:64px;border-radius:50%;
                        background:linear-gradient(135deg,{{ $dpColor }}30,{{ $dpColor }}12);
                        border:3px solid {{ $dpColor }}60;
                        display:flex;align-items:center;justify-content:center;
                        box-shadow:0 6px 24px {{ $dpColor }}35;">
                        <span style="color:{{ $dpColor }};font-weight:800;font-size:1.55rem;line-height:1;">{{ $dpInitial }}</span>
                    </div>
                    <span style="position:absolute;bottom:2px;right:2px;width:16px;height:16px;border-radius:50%;
                        border:2.5px solid var(--card);
                        background:{{ $dp->is_aktif ? 'var(--emer)' : 'var(--mut)' }};
                        box-shadow:{{ $dp->is_aktif ? '0 0 8px rgba(63,207,142,.8)' : 'none' }};"></span>
                </div>
                {{-- Identitas --}}
                <div style="flex:1;min-width:0;">
                    <div class="font-heading" style="font-size:1.18rem;color:var(--ink);line-height:1.25;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;font-weight:700;">{{ $dp->nama }}</div>
                    <div class="font-mono" style="font-size:.76rem;color:var(--mut2);margin-top:.1rem;letter-spacing:.04em;">{{ $dp->no_bpjs ?: '— BPJS belum diisi' }}</div>
                    <div style="margin-top:.4rem;display:flex;align-items:center;gap:.35rem;flex-wrap:wrap;">
                        @if($dp->kategori_diagnosis)
                        <span style="font-size:.72rem;padding:.14rem .52rem;border-radius:999px;background:rgba(217,164,65,.12);border:1px solid rgba(217,164,65,.28);color:var(--gold2);font-weight:600;letter-spacing:.03em;">{{ $dp->kategori_diagnosis }}</span>
                        @endif
                        @if($dpAge)<span style="font-size:.76rem;color:var(--mut);">{{ $dpAge }}&thinsp;thn</span>@endif
                        @if($dp->jenis_kelamin)<span style="font-size:.78rem;color:var(--mut);">{{ $dp->jenis_kelamin==='L' ? 'L' : 'P' }}</span>@endif
                        @if($dp->tanggal_lahir)<span style="font-size:.72rem;color:var(--mut2);">{{ \Carbon\Carbon::parse($dp->tanggal_lahir)->format('d M Y') }}</span>@endif
                    </div>
                </div>
            </div>
            {{-- Close --}}
            <button @click="drawerOpen=false; $wire.closeDrawer()"
                style="background:rgba(255,255,255,.06);border:1px solid var(--line);color:var(--mut);cursor:pointer;border-radius:.5rem;width:36px;height:36px;display:flex;align-items:center;justify-content:center;font-size:1.3rem;flex-shrink:0;transition:all .15s;"
                onmouseover="this.style.background='rgba(232,100,90,.18)';this.style.color='var(--red)'" onmouseout="this.style.background='rgba(255,255,255,.06)';this.style.color='var(--mut)'">&times;</button>
        </div>

        {{-- ── Baris 2: P&L Summary (jika ada data) ────────────────── --}}
        @if($dHasPnl)
        <div style="margin:0 1.35rem .85rem;padding:.9rem 1.1rem;
            border-radius:.65rem;
            background:{{ $dPlus ? 'linear-gradient(135deg,rgba(63,207,142,.1),rgba(63,207,142,.04))' : 'linear-gradient(135deg,rgba(232,100,90,.1),rgba(232,100,90,.04))' }};
            border:1px solid {{ $dPlus ? 'rgba(63,207,142,.25)' : 'rgba(232,100,90,.25)' }};">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:.6rem;">
                <span style="font-size:.7rem;color:var(--mut);text-transform:uppercase;letter-spacing:.07em;font-weight:700;">Analisis Keuangan Pasien</span>
                <span style="font-size:.7rem;padding:.2rem .55rem;border-radius:999px;font-weight:700;
                    background:{{ $dPlus ? 'rgba(63,207,142,.18)' : 'rgba(232,100,90,.18)' }};
                    color:{{ $dPlus ? 'var(--emer)' : 'var(--red2)' }};
                    border:1px solid {{ $dPlus ? 'rgba(63,207,142,.35)' : 'rgba(232,100,90,.35)' }};">
                    <x-i :name="$dPlus ? 'arrow-up' : 'arrow-down'" :size="12" /> {{ $dPlus ? 'PROFIT' : 'DEFISIT' }}
                </span>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1px 1fr 1px 1fr;gap:.6rem;align-items:center;">
                <div style="text-align:center;">
                    <div style="font-size:.68rem;color:var(--mut);margin-bottom:.18rem;font-weight:500;">HPP Kumulatif</div>
                    <div class="font-mono" style="font-size:.95rem;font-weight:700;color:var(--ink);">Rp&nbsp;{{ number_format($dPnl['total_biaya'],0,',','.') }}</div>
                </div>
                <div style="background:var(--line);height:36px;"></div>
                <div style="text-align:center;">
                    <div style="font-size:.68rem;color:var(--emer2);margin-bottom:.18rem;font-weight:500;">Klaim BPJS</div>
                    <div class="font-mono" style="font-size:.95rem;font-weight:700;color:var(--emer2);">Rp&nbsp;{{ number_format($dPnl['total_klaim'],0,',','.') }}</div>
                </div>
                <div style="background:var(--line);height:36px;"></div>
                <div style="text-align:center;">
                    <div style="font-size:.68rem;color:var(--mut);margin-bottom:.18rem;font-weight:500;">Net P&amp;L</div>
                    <div class="font-mono" style="font-size:1.1rem;font-weight:800;line-height:1;color:{{ $dPlus ? 'var(--emer)' : 'var(--red2)' }};">
                        {{ $dPlus ? '+' : '−' }}Rp&nbsp;{{ number_format(abs($dPnl['total_laba']),0,',','.') }}
                    </div>
                </div>
            </div>
            @if($dPnl['total_klaim'] > 0)
            @php $margin = round(($dPnl['total_laba'] / $dPnl['total_klaim']) * 100, 1); @endphp
            <div style="margin-top:.55rem;display:flex;align-items:center;gap:.6rem;">
                <div style="flex:1;height:5px;border-radius:3px;background:rgba(255,255,255,.07);overflow:hidden;">
                    <div style="height:100%;width:{{ min(100,abs($margin)) }}%;border-radius:3px;background:{{ $dPlus ? 'var(--emer)' : 'var(--red2)' }};transition:width .4s;"></div>
                </div>
                <span style="font-size:.74rem;color:{{ $dPlus ? 'var(--emer)' : 'var(--red2)' }};font-weight:700;white-space:nowrap;flex-shrink:0;">{{ $margin }}% margin</span>
            </div>
            @endif
        </div>
        @endif

        {{-- ── Baris 3: Quick Stats (4 tiles) ──────────────────────── --}}
        <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:.4rem;margin:0 1.35rem .9rem;">
            {{-- Jadwal --}}
            <div style="padding:.6rem .35rem;text-align:center;background:rgba(255,255,255,.04);border:1px solid var(--line);border-radius:.5rem;">
                <div style="font-size:.64rem;color:var(--mut);text-transform:uppercase;letter-spacing:.04em;margin-bottom:.18rem;font-weight:600;">Jadwal</div>
                @if($dJadwal ?? null)
                    @php $jc = $dJadwalDiff ?? 0; @endphp
                    <div style="font-size:.9rem;font-weight:800;color:{{ $jc < 0 ? 'var(--red)' : ($jc <= 7 ? 'var(--gold2)' : 'var(--emer)') }};">
                        @if($jc < 0) −{{ abs($jc) }}hr @elseif($jc === 0) Hari ini @else {{ $jc }}hr @endif
                    </div>
                @else
                    <div style="font-size:.8rem;color:var(--mut);">—</div>
                @endif
            </div>
            {{-- Terakhir Ambil --}}
            <div style="padding:.6rem .35rem;text-align:center;background:rgba(255,255,255,.04);border:1px solid var(--line);border-radius:.5rem;">
                <div style="font-size:.64rem;color:var(--mut);text-transform:uppercase;letter-spacing:.04em;margin-bottom:.18rem;font-weight:600;">Terakhir</div>
                @if($dStats['last_pickup'] ?? null)
                    <div style="font-size:.82rem;font-weight:700;color:var(--ink);">{{ \Carbon\Carbon::parse($dStats['last_pickup'])->format('d M') }}</div>
                @else
                    <div style="font-size:.8rem;color:var(--mut);">—</div>
                @endif
            </div>
            {{-- Obat Aktif --}}
            <div style="padding:.6rem .35rem;text-align:center;background:rgba(255,255,255,.04);border:1px solid var(--line);border-radius:.5rem;">
                <div style="font-size:.64rem;color:var(--mut);text-transform:uppercase;letter-spacing:.04em;margin-bottom:.18rem;font-weight:600;">Obat</div>
                <div style="font-size:.95rem;font-weight:800;color:var(--emer2);">{{ $dStats['resep_count'] ?? 0 }}</div>
            </div>
            {{-- Total Kunjungan --}}
            <div style="padding:.6rem .35rem;text-align:center;background:rgba(255,255,255,.04);border:1px solid var(--line);border-radius:.5rem;">
                <div style="font-size:.64rem;color:var(--mut);text-transform:uppercase;letter-spacing:.04em;margin-bottom:.18rem;font-weight:600;">Kunjungan</div>
                <div style="font-size:.95rem;font-weight:800;color:var(--gold2);">{{ $dPnl['count'] ?? 0 }}</div>
            </div>
        </div>

        {{-- ── Baris 4: Catatan (jika ada) ─────────────────────────── --}}
        @if($dp->catatan)
        <div style="margin:0 1.35rem .8rem;padding:.55rem .8rem;background:rgba(217,164,65,.07);border-left:3px solid rgba(217,164,65,.45);border-radius:0 .5rem .5rem 0;font-size:.8rem;color:var(--gold2);font-style:italic;line-height:1.45;">
            {{ $dp->catatan }}
        </div>
        @endif

        {{-- ── Baris 5: Action Buttons ──────────────────────────────── --}}
        <div style="padding:0 1.35rem 1rem;display:flex;gap:.5rem;">
            <button wire:click="catat({{ $dp->id }})" @click="drawerOpen=false; $wire.closeDrawer()"
                class="btn-gold" style="flex:1;justify-content:center;font-size:.82rem;padding:.58rem .85rem;">
                <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
                Catat Pengambilan
            </button>
            <button wire:click="openEdit({{ $dp->id }})" @click="drawerOpen=false"
                class="btn-outline" style="font-size:.82rem;padding:.58rem .8rem;">
                <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                Edit
            </button>
            @if($dp->telepon)
            <a href="https://wa.me/62{{ ltrim($dp->telepon,'0') }}" target="_blank"
                style="display:inline-flex;align-items:center;justify-content:center;gap:.35rem;padding:.58rem .75rem;
                    background:rgba(37,211,102,.12);border:1px solid rgba(37,211,102,.3);color:#25D366;
                    border-radius:.5rem;font-size:.82rem;font-weight:600;text-decoration:none;transition:all .15s;white-space:nowrap;"
                onmouseover="this.style.background='rgba(37,211,102,.24)'" onmouseout="this.style.background='rgba(37,211,102,.12)'">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                WA
            </a>
            @endif
        </div>
    </div>

    {{-- ── Drawer Tabs — Segmented Control ──────────────────────────────── --}}
    <div style="padding:.8rem 1rem;background:var(--card);flex-shrink:0;border-bottom:1px solid var(--line);">
        <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:.45rem;background:rgba(0,0,0,.25);border-radius:.7rem;padding:.3rem;">

            {{-- Tab Resep --}}
            <button @click="drawerTab='resep'"
                :style="drawerTab==='resep'
                    ? 'background:rgba(63,207,142,.2);color:var(--emer);box-shadow:0 2px 12px rgba(63,207,142,.2);border:1px solid rgba(63,207,142,.3);'
                    : 'background:transparent;color:var(--mut);border:1px solid transparent;'"
                style="padding:.65rem .4rem;border:none;cursor:pointer;transition:all .2s;border-radius:.5rem;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:.28rem;min-width:0;">
                <svg width="17" height="17" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"/>
                </svg>
                <span style="font-size:.76rem;font-weight:700;letter-spacing:.01em;white-space:nowrap;line-height:1;">Resep</span>
            </button>

            {{-- Tab Riwayat P&L --}}
            <button @click="drawerTab='riwayat'"
                :style="drawerTab==='riwayat'
                    ? 'background:rgba(217,164,65,.2);color:var(--gold2);box-shadow:0 2px 12px rgba(217,164,65,.2);border:1px solid rgba(217,164,65,.3);'
                    : 'background:transparent;color:var(--mut);border:1px solid transparent;'"
                style="padding:.65rem .4rem;border:none;cursor:pointer;transition:all .2s;border-radius:.5rem;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:.28rem;min-width:0;">
                <svg width="17" height="17" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/>
                </svg>
                <span style="font-size:.76rem;font-weight:700;letter-spacing:.01em;white-space:nowrap;line-height:1;">Riwayat P&amp;L</span>
            </button>

            {{-- Tab Persyaratan --}}
            <button @click="drawerTab='persyaratan'"
                :style="drawerTab==='persyaratan'
                    ? 'background:rgba(111,177,224,.2);color:var(--blue);box-shadow:0 2px 12px rgba(111,177,224,.2);border:1px solid rgba(111,177,224,.3);'
                    : 'background:transparent;color:var(--mut);border:1px solid transparent;'"
                style="padding:.65rem .4rem;border:none;cursor:pointer;transition:all .2s;border-radius:.5rem;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:.28rem;min-width:0;">
                <svg width="17" height="17" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <polyline points="20 6 9 17 4 12"/><circle cx="12" cy="12" r="10"/>
                </svg>
                <span style="font-size:.76rem;font-weight:700;letter-spacing:.01em;white-space:nowrap;line-height:1;">Persyaratan</span>
            </button>

        </div>
    </div>

    {{-- Scrollable tab content area --}}
    <div style="flex:1;overflow-y:auto;min-height:0;">

    {{-- ══ Tab: Resep Obat ════════════════════════════════════════════════ --}}
    <div x-show="drawerTab==='resep'" style="padding:1.25rem;">
        @if(!$resepEditing)
        {{-- View mode --}}
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1rem;">
            <div style="display:flex;align-items:center;gap:.5rem;">
                <span style="font-size:.72rem;color:var(--mut);text-transform:uppercase;letter-spacing:.06em;font-weight:700;">Obat Rutin Bulanan</span>
                @if($this->drawerResep->count() > 0)
                <span style="font-size:.7rem;padding:.14rem .45rem;border-radius:999px;background:rgba(63,207,142,.12);border:1px solid rgba(63,207,142,.28);color:var(--emer);font-weight:700;">{{ $this->drawerResep->count() }} obat</span>
                @endif
            </div>
            <button wire:click="startResepEdit"
                style="font-size:.78rem;padding:.38rem .75rem;background:rgba(63,207,142,.08);border:1px solid rgba(63,207,142,.22);color:var(--emer);border-radius:.45rem;cursor:pointer;display:inline-flex;align-items:center;gap:.35rem;transition:all .15s;"
                onmouseover="this.style.background='rgba(63,207,142,.18)'" onmouseout="this.style.background='rgba(63,207,142,.08)'">
                <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>Edit
            </button>
        </div>
        @if($this->drawerResep->isEmpty())
        <div style="text-align:center;padding:3rem 0;">
            <div style="width:56px;height:56px;border-radius:50%;background:rgba(63,207,142,.07);border:1.5px solid rgba(63,207,142,.18);display:flex;align-items:center;justify-content:center;margin:0 auto .85rem;">
                <svg width="24" height="24" fill="none" stroke="rgba(63,207,142,.55)" stroke-width="1.5" viewBox="0 0 24 24"><path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"/></svg>
            </div>
            <div style="font-size:.9rem;color:var(--mut);margin-bottom:.38rem;font-weight:500;">Belum ada resep obat.</div>
            <div style="font-size:.78rem;color:var(--mut2);">Klik Edit untuk menambah obat rutin pasien ini.</div>
        </div>
        @else
        <div style="display:flex;flex-direction:column;gap:.5rem;margin-bottom:1rem;">
            @foreach($this->drawerResep as $ri => $rsp)
            <div style="display:flex;align-items:center;gap:.75rem;padding:.75rem .95rem;
                background:rgba(63,207,142,.04);border:1px solid rgba(63,207,142,.14);
                border-left:4px solid rgba(63,207,142,.4);border-radius:0 .55rem .55rem 0;transition:border-color .15s;"
                onmouseover="this.style.borderLeftColor='var(--emer)'" onmouseout="this.style.borderLeftColor='rgba(63,207,142,.4)'">
                <div style="width:32px;height:32px;border-radius:.4rem;background:rgba(63,207,142,.13);border:1px solid rgba(63,207,142,.22);display:flex;align-items:center;justify-content:center;flex-shrink:0;font-size:.76rem;font-weight:800;color:var(--emer);">{{ $ri + 1 }}</div>
                <div style="flex:1;min-width:0;">
                    <div style="font-size:.95rem;font-weight:600;color:var(--ink);overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $rsp->obat?->nama_obat ?? '—' }}</div>
                    <div style="font-size:.74rem;color:var(--mut);margin-top:.08rem;">{{ $rsp->satuan ?? 'tablet' }}</div>
                </div>
                <div style="text-align:right;flex-shrink:0;">
                    <div class="font-mono" style="font-size:1.05rem;font-weight:800;color:var(--emer2);">{{ $rsp->jumlah_default }}</div>
                    <div style="font-size:.66rem;color:var(--mut);">/bulan</div>
                </div>
            </div>
            @endforeach
        </div>
        <div style="padding:.65rem .9rem;background:rgba(111,177,224,.05);border:1px solid rgba(111,177,224,.18);border-radius:.5rem;font-size:.76rem;color:var(--blue);display:flex;align-items:center;gap:.45rem;">
            <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="flex-shrink:0;"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
            Resep ini otomatis terisi saat catat pengambilan obat.
        </div>
        @endif

        @else
        {{-- Edit mode --}}
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1rem;">
            <span style="font-size:.75rem;color:var(--gold2);font-weight:600;">Edit Resep Obat</span>
            <button wire:click="cancelResepEdit" style="font-size:.7rem;padding:.25rem .55rem;background:transparent;border:1px solid var(--line);color:var(--mut);border-radius:.3rem;cursor:pointer;">Batal</button>
        </div>

        <div style="display:flex;flex-direction:column;gap:.45rem;margin-bottom:.75rem;">
            @foreach($resepRows as $ri => $rrow)
            <div style="display:flex;align-items:center;gap:.4rem;padding:.6rem .7rem;background:rgba(255,255,255,.06);border:1px solid var(--line2);border-radius:.45rem;">
                <div style="flex:1;min-width:0;">
                    <select wire:model.live="resepRows.{{ $ri }}.obat_id" class="form-input" style="font-size:.73rem;padding:.3rem .45rem;margin-bottom:.3rem;background:#1e3828;border-color:var(--line2);color:var(--ink);">
                        <option value="0">— Pilih Obat —</option>
                        @foreach($this->obatList as $ob)
                        <option value="{{ $ob->id }}">{{ $ob->nama_obat }}</option>
                        @endforeach
                    </select>
                    @error('resepRows.'.$ri.'.obat_id')<div style="color:var(--red);font-size:.63rem;margin-top:.1rem;">{{ $message }}</div>@enderror
                </div>
                <div style="display:flex;align-items:center;gap:.3rem;flex-shrink:0;">
                    <input wire:model="resepRows.{{ $ri }}.jumlah_default" type="number" min="1" max="999"
                        class="form-input font-mono" style="width:60px;font-size:.82rem;padding:.3rem .4rem;text-align:center;background:#1e3828;border-color:var(--line2);color:var(--ink);">
                    <select wire:model="resepRows.{{ $ri }}.satuan" class="form-input" style="width:85px;font-size:.72rem;padding:.3rem .35rem;background:#1e3828;border-color:var(--line2);color:var(--ink);">
                        <option value="tablet">Tablet</option>
                        <option value="kapsul">Kapsul</option>
                        <option value="botol">Botol</option>
                        <option value="sachet">Sachet</option>
                        <option value="strip">Strip</option>
                        <option value="tube">Tube</option>
                        <option value="mg">mg</option>
                        <option value="ml">ml</option>
                    </select>
                    <button type="button" wire:click="removeResepRow({{ $ri }})"
                        style="width:28px;height:28px;background:rgba(232,100,90,.1);border:1px solid rgba(232,100,90,.3);color:var(--red);border-radius:.3rem;cursor:pointer;display:flex;align-items:center;justify-content:center;flex-shrink:0;font-size:1rem;">&times;</button>
                </div>
            </div>
            @endforeach
        </div>

        <button type="button" wire:click="addResepRow"
            style="width:100%;padding:.42rem;background:transparent;border:1px dashed var(--line2);color:var(--mut);border-radius:.4rem;cursor:pointer;font-size:.75rem;display:flex;align-items:center;justify-content:center;gap:.35rem;margin-bottom:.85rem;transition:all .2s;"
            onmouseover="this.style.borderColor='var(--emer)';this.style.color='var(--emer)'" onmouseout="this.style.borderColor='var(--line2)';this.style.color='var(--mut)'">
            <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>Tambah Obat
        </button>

        <button wire:click="saveResep" class="btn-gold" style="width:100%;justify-content:center;" wire:loading.attr="disabled" wire:target="saveResep">
            <span wire:loading.remove wire:target="saveResep" style="display:inline-flex;align-items:center;gap:.4rem;">
                <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
                Simpan Resep
            </span>
            <span wire:loading wire:target="saveResep">Menyimpan…</span>
        </button>
        @endif
    </div>

    {{-- Tab: Riwayat Pengambilan + P&L --}}
    <div x-show="drawerTab==='riwayat'" style="padding:1.25rem;">

        {{-- ── P&L SUMMARY BANNER ──────────────────────────────────────── --}}
        @php
            $pnl      = $this->drawerPnl;
            $pnlPlus  = $pnl['total_laba'] >= 0;
            $pnlColor = $pnlPlus ? 'var(--emer)' : 'var(--red2)';
            $pnlBg    = $pnlPlus ? 'rgba(63,207,142,.06)' : 'rgba(232,100,90,.06)';
            $pnlBdr   = $pnlPlus ? 'rgba(63,207,142,.22)' : 'rgba(232,100,90,.22)';
        @endphp
        @if($pnl['count'] > 0)
        <div style="margin-bottom:1.2rem;padding:1rem 1.1rem;background:{{ $pnlBg }};border:1px solid {{ $pnlBdr }};border-radius:.65rem;">
            <div style="font-size:.7rem;color:var(--mut);text-transform:uppercase;letter-spacing:.07em;font-weight:700;margin-bottom:.7rem;display:flex;align-items:center;justify-content:space-between;">
                <span>Akumulasi P&amp;L Pasien</span>
                <span style="font-weight:500;color:var(--mut2);font-size:.68rem;text-transform:none;letter-spacing:0;">{{ $pnl['count'] }} kunjungan selesai</span>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:.5rem;text-align:center;">
                <div>
                    <div style="font-size:.68rem;color:var(--mut);margin-bottom:.18rem;font-weight:500;">HPP Total</div>
                    <div class="font-mono" style="font-size:.92rem;font-weight:700;color:var(--ink);">Rp&nbsp;{{ number_format($pnl['total_biaya'],0,',','.') }}</div>
                </div>
                <div style="border-left:1px solid var(--line);border-right:1px solid var(--line);">
                    <div style="font-size:.68rem;color:var(--mut);margin-bottom:.18rem;font-weight:500;">Klaim BPJS</div>
                    <div class="font-mono" style="font-size:.92rem;font-weight:700;color:var(--emer2);">Rp&nbsp;{{ number_format($pnl['total_klaim'],0,',','.') }}</div>
                </div>
                <div>
                    <div style="font-size:.68rem;color:var(--mut);margin-bottom:.18rem;font-weight:500;">Net P&amp;L</div>
                    <div class="font-mono" style="font-size:1.05rem;font-weight:800;color:{{ $pnlColor }};">
                        {{ $pnlPlus ? '+' : '−' }}Rp&nbsp;{{ number_format(abs($pnl['total_laba']),0,',','.') }}
                    </div>
                </div>
            </div>
            @if($pnl['total_biaya'] > 0)
            <div style="margin-top:.6rem;padding-top:.55rem;border-top:1px solid {{ $pnlBdr }};font-size:.74rem;color:{{ $pnlColor }};text-align:center;font-weight:700;">
                @php $margin = $pnl['total_klaim'] > 0 ? round(($pnl['total_laba'] / $pnl['total_klaim']) * 100, 1) : 0; @endphp
                <x-i :name="$pnlPlus ? 'arrow-up' : 'arrow-down'" :size="12" /> {{ $pnlPlus ? 'Profit' : 'Defisit' }} {{ abs($margin) }}% dari total klaim
            </div>
            @endif
        </div>
        @endif

        {{-- ── TIMELINE KUNJUNGAN ──────────────────────────────────────── --}}
        @if($this->drawerRiwayat->count() > 0)
        <div style="position:relative;padding-left:1.5rem;">
            <div style="position:absolute;left:.6rem;top:.5rem;bottom:1rem;width:1px;background:var(--line2);"></div>

            @foreach($this->drawerRiwayat as $rwyt)
            @php
                $rStatus  = $rwyt->status ?? 'dijadwalkan';
                $rColor   = match($rStatus) { 'selesai' => 'var(--emer)', 'lewat' => 'var(--red)', default => 'var(--gold2)' };
                $rBg      = match($rStatus) { 'selesai' => 'rgba(63,207,142,.1)', 'lewat' => 'rgba(232,100,90,.1)', default => 'rgba(217,164,65,.1)' };
                $rBorder  = match($rStatus) { 'selesai' => 'rgba(63,207,142,.25)', 'lewat' => 'rgba(232,100,90,.25)', default => 'rgba(217,164,65,.25)' };
                $rLabel   = match($rStatus) { 'selesai' => '<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round" style="display:inline-block;vertical-align:middle"><polyline points="20 6 9 17 4 12"/></svg> Selesai', 'lewat' => '<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" style="display:inline-block;vertical-align:middle"><path d="M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg> Lewat', default => '⏳ Jadwal' };

                // P&L per kunjungan ini
                $kBiaya   = $rwyt->items->sum(fn($i) => $i->total_biaya);
                $kKlaim   = $rwyt->items->sum(fn($i) => $i->proyeksi_klaim);
                $kLaba    = $kKlaim - $kBiaya;
                $kPlus    = $kLaba >= 0;
                $hasHarga = $kBiaya > 0 || $kKlaim > 0;
            @endphp
            <div style="position:relative;margin-bottom:1rem;">
                <div style="position:absolute;left:-1.2rem;top:.45rem;width:10px;height:10px;border-radius:50%;background:{{ $rColor }};border:2px solid var(--panel);box-shadow:0 0 6px {{ $rColor }}55;"></div>
                <div class="glass-card" style="padding:.8rem;margin-left:.2rem;">

                    {{-- Baris header tanggal + badge --}}
                    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:.5rem;gap:.4rem;flex-wrap:wrap;">
                        <span style="font-size:.82rem;font-weight:700;color:var(--ink);">
                            {{ \Carbon\Carbon::parse($rwyt->tanggal_pengambilan)->locale('id')->translatedFormat('d M Y') }}
                        </span>
                        <div style="display:flex;align-items:center;gap:.35rem;flex-wrap:wrap;">
                            @if($hasHarga && $rStatus === 'selesai')
                            <span class="font-mono" style="font-size:.74rem;padding:.16rem .55rem;border-radius:999px;font-weight:700;
                                background:{{ $kPlus ? 'rgba(63,207,142,.12)' : 'rgba(232,100,90,.12)' }};
                                border:1px solid {{ $kPlus ? 'rgba(63,207,142,.3)' : 'rgba(232,100,90,.3)' }};
                                color:{{ $kPlus ? 'var(--emer)' : 'var(--red2)' }};">
                                {{ $kPlus ? '+' : '−' }}Rp&nbsp;{{ number_format(abs($kLaba),0,',','.') }}
                            </span>
                            @endif
                            <span style="font-size:.74rem;padding:.16rem .55rem;border-radius:999px;background:{{ $rBg }};border:1px solid {{ $rBorder }};color:{{ $rColor }};font-weight:600;">{{ $rLabel }}</span>
                        </div>
                    </div>

                    {{-- Tabel per-item --}}
                    @if($rwyt->items && $rwyt->items->count() > 0)
                    <div style="overflow-x:auto;">
                    <table style="width:100%;border-collapse:collapse;min-width:260px;">
                        <thead>
                            <tr style="border-bottom:1px solid rgba(255,255,255,.07);">
                                <th style="text-align:left;font-size:.68rem;color:var(--mut);font-weight:700;padding:.3rem .35rem;text-transform:uppercase;letter-spacing:.04em;">Obat</th>
                                <th style="text-align:center;font-size:.68rem;color:var(--mut);font-weight:700;padding:.3rem .35rem;text-transform:uppercase;letter-spacing:.04em;">Jml</th>
                                @if($hasHarga)
                                <th style="text-align:right;font-size:.68rem;color:var(--mut);font-weight:700;padding:.3rem .35rem;text-transform:uppercase;letter-spacing:.04em;">HPP</th>
                                <th style="text-align:right;font-size:.68rem;color:var(--emer2);font-weight:700;padding:.3rem .35rem;text-transform:uppercase;letter-spacing:.04em;">Klaim</th>
                                <th style="text-align:right;font-size:.68rem;color:var(--mut);font-weight:700;padding:.3rem .35rem;text-transform:uppercase;letter-spacing:.04em;">P&amp;L</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($rwyt->items as $itm)
                            @php
                                $iBiaya  = $itm->total_biaya;
                                $iKlaim  = $itm->proyeksi_klaim;
                                $iLaba   = $iKlaim - $iBiaya;
                                $iPlus   = $iLaba >= 0;
                                $iHasHrg = $iBiaya > 0 || $iKlaim > 0;
                            @endphp
                            <tr style="border-bottom:1px solid rgba(255,255,255,.04);">
                                <td style="padding:.35rem .35rem;font-size:.82rem;color:var(--ink);">{{ $itm->obat?->nama_obat ?? '—' }}</td>
                                <td style="padding:.35rem .35rem;text-align:center;">
                                    <span class="font-mono" style="font-size:.8rem;color:var(--mut2);font-weight:600;">{{ $itm->jumlah_unit }}</span>
                                    <span style="font-size:.68rem;color:var(--mut);margin-left:.15rem;">{{ $itm->satuan }}</span>
                                </td>
                                @if($hasHarga)
                                <td class="font-mono" style="padding:.35rem .35rem;text-align:right;font-size:.78rem;color:var(--mut2);">
                                    @if($iBiaya > 0) Rp&nbsp;{{ number_format($iBiaya,0,',','.') }} @else <span style="color:var(--mut);">—</span> @endif
                                </td>
                                <td class="font-mono" style="padding:.35rem .35rem;text-align:right;font-size:.78rem;color:var(--emer2);">
                                    @if($iKlaim > 0) Rp&nbsp;{{ number_format($iKlaim,0,',','.') }} @else <span style="color:var(--mut);">—</span> @endif
                                </td>
                                <td class="font-mono" style="padding:.35rem .35rem;text-align:right;font-size:.82rem;font-weight:700;
                                    color:{{ $iHasHrg ? ($iPlus ? 'var(--emer)' : 'var(--red2)') : 'var(--mut)' }};">
                                    @if($iHasHrg)
                                        {{ $iPlus ? '+' : '−' }}Rp&nbsp;{{ number_format(abs($iLaba),0,',','.') }}
                                    @else <span style="color:var(--mut);">—</span>
                                    @endif
                                </td>
                                @endif
                            </tr>
                            @endforeach
                        </tbody>
                        @if($hasHarga && $rwyt->items->count() > 1)
                        <tfoot>
                            <tr style="border-top:1px solid rgba(255,255,255,.1);">
                                <td colspan="2" style="padding:.38rem .35rem;font-size:.7rem;color:var(--mut);font-weight:700;text-transform:uppercase;letter-spacing:.04em;">Total</td>
                                <td class="font-mono" style="padding:.38rem .35rem;text-align:right;font-size:.8rem;font-weight:700;color:var(--ink);">Rp&nbsp;{{ number_format($kBiaya,0,',','.') }}</td>
                                <td class="font-mono" style="padding:.38rem .35rem;text-align:right;font-size:.8rem;font-weight:700;color:var(--emer2);">Rp&nbsp;{{ number_format($kKlaim,0,',','.') }}</td>
                                <td class="font-mono" style="padding:.38rem .35rem;text-align:right;font-size:.88rem;font-weight:800;color:{{ $kPlus ? 'var(--emer)' : 'var(--red2)' }};">
                                    {{ $kPlus ? '+' : '−' }}Rp&nbsp;{{ number_format(abs($kLaba),0,',','.') }}
                                </td>
                            </tr>
                        </tfoot>
                        @endif
                    </table>
                    </div>
                    @else
                    <div style="font-size:.72rem;color:var(--mut);margin-top:.25rem;">Tidak ada data obat.</div>
                    @endif

                    @if($rwyt->catatan)
                    <div style="margin-top:.5rem;font-size:.69rem;color:var(--mut);font-style:italic;padding:.3rem .5rem;background:rgba(255,255,255,.03);border-radius:.3rem;border-left:2px solid var(--line2);">
                        {{ $rwyt->catatan }}
                    </div>
                    @endif
                </div>
            </div>
            @endforeach

            @if($this->drawerRiwayat->count() >= 10)
            <div style="text-align:center;font-size:.68rem;color:var(--mut2);padding:.4rem 0 0;margin-left:.2rem;">
                Menampilkan 10 kunjungan terakhir
            </div>
            @endif
        </div>

        @else
        <div style="text-align:center;padding:2.5rem 0;">
            <div style="width:40px;height:40px;border-radius:50%;background:rgba(255,255,255,.04);display:flex;align-items:center;justify-content:center;margin:0 auto .75rem;">
                <svg width="18" height="18" fill="none" stroke="var(--mut)" stroke-width="1.5" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="3" y1="10" x2="21" y2="10"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="16" y1="2" x2="16" y2="6"/></svg>
            </div>
            <div style="color:var(--mut);font-size:.82rem;">Belum ada riwayat pengambilan.</div>
        </div>
        @endif
    </div>

    {{-- Tab: Persyaratan Klaim --}}
    <div x-show="drawerTab==='persyaratan'" style="padding:1.25rem;">
        @if($this->drawerPersyaratan && $this->drawerPersyaratan->count() > 0)
        @foreach($this->drawerPersyaratan as $syarat)
        @php
            $sTipe = $syarat->tipe ?? 'dokumen';
            $sStyle = match($sTipe) {
                'lab' => ['bg'=>'rgba(111,177,224,.12)','border'=>'rgba(111,177,224,.3)','text'=>'var(--blue)'],
                'pemeriksaan' => ['bg'=>'rgba(63,207,142,.1)','border'=>'rgba(63,207,142,.25)','text'=>'var(--emer2)'],
                default => ['bg'=>'rgba(217,164,65,.1)','border'=>'rgba(217,164,65,.25)','text'=>'var(--gold2)']
            };
            $sLabel = match($sTipe) { 'lab' => 'Lab', 'pemeriksaan' => 'Pemeriksaan', default => 'Dokumen' };
        @endphp
        <div class="glass-card" style="padding:.85rem;margin-bottom:.6rem;">
            <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:.5rem;">
                <div style="flex:1;">
                    <div style="display:flex;align-items:center;gap:.4rem;margin-bottom:.35rem;flex-wrap:wrap;">
                        <span style="font-size:.74rem;padding:.17rem .5rem;border-radius:.35rem;background:{{ $sStyle['bg'] }};border:1px solid {{ $sStyle['border'] }};color:{{ $sStyle['text'] }};font-weight:600;">{{ $sLabel }}</span>
                        @if($syarat->is_wajib ?? false)
                        <span style="font-size:.72rem;padding:.15rem .48rem;border-radius:.35rem;background:rgba(232,100,90,.1);border:1px solid rgba(232,100,90,.25);color:var(--red);font-weight:700;">WAJIB</span>
                        @endif
                    </div>
                    <div style="font-size:.95rem;font-weight:600;color:var(--ink);">{{ $syarat->nama_syarat }}</div>
                    @if($syarat->deskripsi ?? null)
                    <div style="font-size:.82rem;color:var(--mut);margin-top:.25rem;">{{ $syarat->deskripsi }}</div>
                    @endif
                    <div style="font-size:.76rem;color:var(--mut2);margin-top:.35rem;">
                        <svg width="10" height="10" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="display:inline;vertical-align:middle;margin-right:.2rem;"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                        @if(($syarat->periode_bulan ?? 0) > 0) Setiap {{ $syarat->periode_bulan }} bulan @else Setiap kunjungan @endif
                    </div>
                </div>
                <div class="font-mono" style="font-size:.65rem;color:var(--mut2);flex-shrink:0;">{{ str_pad($loop->iteration,2,'0',STR_PAD_LEFT) }}</div>
            </div>
        </div>
        @endforeach
        @else
        <div style="text-align:center;padding:2.5rem 0;">
            <div style="width:40px;height:40px;border-radius:50%;background:rgba(63,207,142,.1);display:flex;align-items:center;justify-content:center;margin:0 auto .7rem;">
                <svg width="18" height="18" fill="none" stroke="var(--emer)" stroke-width="2.2" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
            </div>
            <div style="color:var(--mut);font-size:.82rem;">Tidak ada persyaratan klaim untuk diagnosis ini.</div>
        </div>
        @endif
    </div>

    </div>{{-- end scrollable tab content --}}

    @else
    {{-- Loading state --}}
    <div style="display:flex;align-items:center;justify-content:center;height:200px;">
        <div style="text-align:center;color:var(--mut);font-size:.82rem;">Memuat data pasien...</div>
    </div>
    @endif
</div>

<style>
@keyframes prb-pulse { 0%,100%{opacity:1;box-shadow:0 0 0 0 rgba(217,164,65,.4);} 50%{opacity:.75;box-shadow:0 0 0 4px rgba(217,164,65,.0);} }
.pulse-badge { animation: prb-pulse 1.8s ease-in-out infinite; }
</style>
</div>
