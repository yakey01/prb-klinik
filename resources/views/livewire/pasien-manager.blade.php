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
            <label class="form-label">No. BPJS <span style="color:var(--red);">*</span> <span style="color:var(--mut);font-weight:400;">(min 8 digit)</span></label>
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
            <label class="form-label">No. Handphone <span style="color:var(--red);">*</span></label>
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
            <th style="text-align:center;">Pasien</th><th style="text-align:center;">Rata/Kunjungan</th>
            <th style="text-align:right;">Est. Bulanan</th>
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
                            <div style="font-weight:600;font-size:.85rem;color:var(--ink);">{{ $pasien->nama }}</div>
                            <div class="font-mono" style="font-size:.68rem;color:var(--mut2);">{{ $pasien->no_bpjs ?: '—' }}</div>
                            @if($age || $pasien->jenis_kelamin)
                            <div style="font-size:.66rem;color:var(--mut);margin-top:.1rem;">
                                @if($age){{ $age }} thn @endif
                                @if($age && $pasien->jenis_kelamin) &middot; @endif
                                @if($pasien->jenis_kelamin) {{ $pasien->jenis_kelamin === 'L' ? '♂ L' : '♀ P' }} @endif
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
                        <button wire:click="deletePasien({{ $pasien->id }})" wire:confirm="Hapus pasien '{{ $pasien->nama }}'? Data pengambilan juga ikut terhapus." class="btn-danger" style="padding:.28rem .4rem;font-size:.7rem;" title="Hapus">
                            <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14H6L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4h6v2"/></svg>
                        </button>
                    </div>
                </td>
            </tr>
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
    style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.55);z-index:40;backdrop-filter:blur(2px);"></div>

<div x-show="drawerOpen"
    x-transition:enter="transition ease-out duration-250"
    x-transition:enter-start="transform translate-x-full"
    x-transition:enter-end="transform translate-x-0"
    x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="transform translate-x-0"
    x-transition:leave-end="transform translate-x-full"
    style="display:none;position:fixed;top:0;right:0;bottom:0;width:430px;max-width:95vw;background:var(--panel);border-left:1px solid var(--line2);z-index:50;overflow-y:auto;box-shadow:-8px 0 40px rgba(0,0,0,.5);">

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

    {{-- Drawer Header --}}
    <div style="padding:1.5rem 1.25rem 1rem;border-bottom:1px solid var(--line);background:var(--card);position:sticky;top:0;z-index:5;">
        <div style="display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:1rem;">
            <div style="display:flex;align-items:center;gap:.9rem;">
                <div style="width:54px;height:54px;border-radius:50%;background:{{ $dpColor }}1a;border:2px solid {{ $dpColor }}55;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                    <span style="color:{{ $dpColor }};font-weight:700;font-size:1.25rem;">{{ $dpInitial }}</span>
                </div>
                <div>
                    <div class="font-heading" style="font-size:1.05rem;color:var(--ink);">{{ $dp->nama }}</div>
                    <div class="font-mono" style="font-size:.7rem;color:var(--mut2);margin-top:.12rem;">{{ $dp->no_bpjs ?: 'No BPJS belum diisi' }}</div>
                    <div style="margin-top:.4rem;display:flex;align-items:center;gap:.4rem;flex-wrap:wrap;">
                        @if($dp->kategori_diagnosis)
                        <span style="font-size:.65rem;padding:.15rem .5rem;border-radius:999px;background:rgba(217,164,65,.1);border:1px solid rgba(217,164,65,.25);color:var(--gold2);">{{ $dp->kategori_diagnosis }}</span>
                        @endif
                        @if($dpAge) <span style="font-size:.68rem;color:var(--mut);">{{ $dpAge }} thn</span> @endif
                        @if($dp->jenis_kelamin) <span style="font-size:.7rem;color:var(--mut);">{{ $dp->jenis_kelamin === 'L' ? '♂' : '♀' }}</span> @endif
                        @if($dp->is_aktif) <span style="font-size:.64rem;color:var(--emer);display:inline-flex;align-items:center;gap:.25rem;"><span style="width:5px;height:5px;border-radius:50%;background:var(--emer);flex-shrink:0;"></span>Aktif</span> @endif
                    </div>
                </div>
            </div>
            <button @click="drawerOpen=false; $wire.closeDrawer()" style="background:rgba(255,255,255,.05);border:1px solid var(--line);color:var(--mut);cursor:pointer;border-radius:.35rem;width:28px;height:28px;display:flex;align-items:center;justify-content:center;font-size:1rem;flex-shrink:0;">&times;</button>
        </div>
        <div style="display:flex;gap:.5rem;">
            <button wire:click="catat({{ $dp->id }})" @click="drawerOpen=false; $wire.closeDrawer()" class="btn-gold" style="flex:1;justify-content:center;font-size:.78rem;padding:.5rem .75rem;">
                <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>Catat Pengambilan
            </button>
            <button wire:click="openEdit({{ $dp->id }})" @click="drawerOpen=false" class="btn-outline" style="font-size:.78rem;padding:.5rem .75rem;">
                <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>Edit
            </button>
        </div>
    </div>

    {{-- Drawer Tabs --}}
    <div style="display:flex;border-bottom:1px solid var(--line);background:var(--card);">
        <button @click="drawerTab='resep'"
            :style="drawerTab==='resep' ? 'color:var(--emer);border-bottom:2px solid var(--emer);' : 'color:var(--mut);border-bottom:2px solid transparent;'"
            style="flex:1;padding:.7rem .5rem;background:none;border:none;border-top:none;border-left:none;border-right:none;cursor:pointer;font-size:.75rem;font-weight:500;transition:all .15s;">
            💊 Resep Obat
        </button>
        <button @click="drawerTab='riwayat'"
            :style="drawerTab==='riwayat' ? 'color:var(--gold2);border-bottom:2px solid var(--gold);' : 'color:var(--mut);border-bottom:2px solid transparent;'"
            style="flex:1;padding:.7rem .5rem;background:none;border:none;border-top:none;border-left:none;border-right:none;cursor:pointer;font-size:.75rem;font-weight:500;transition:all .15s;">
            Riwayat
        </button>
        <button @click="drawerTab='persyaratan'"
            :style="drawerTab==='persyaratan' ? 'color:var(--gold2);border-bottom:2px solid var(--gold);' : 'color:var(--mut);border-bottom:2px solid transparent;'"
            style="flex:1;padding:.7rem .5rem;background:none;border:none;border-top:none;border-left:none;border-right:none;cursor:pointer;font-size:.75rem;font-weight:500;transition:all .15s;">
            Persyaratan
        </button>
    </div>

    {{-- Tab: Resep Obat --}}
    <div x-show="drawerTab==='resep'" style="padding:1.25rem;">
        @if(!$resepEditing)
        {{-- View mode --}}
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1rem;">
            <span style="font-size:.7rem;color:var(--mut);text-transform:uppercase;letter-spacing:.05em;font-weight:600;">Obat Rutin Bulanan</span>
            <button wire:click="startResepEdit"
                style="font-size:.72rem;padding:.32rem .7rem;background:rgba(63,207,142,.1);border:1px solid rgba(63,207,142,.25);color:var(--emer);border-radius:.35rem;cursor:pointer;display:inline-flex;align-items:center;gap:.3rem;">
                <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>Edit Resep
            </button>
        </div>
        @if($this->drawerResep->isEmpty())
        <div style="text-align:center;padding:2rem 0;">
            <div style="width:40px;height:40px;border-radius:50%;background:rgba(63,207,142,.08);display:flex;align-items:center;justify-content:center;margin:0 auto .6rem;">
                <svg width="18" height="18" fill="none" stroke="var(--emer)" stroke-width="1.8" viewBox="0 0 24 24"><path d="M9 3H5a2 2 0 0 0-2 2v4m6-6h10a2 2 0 0 1 2 2v4M9 3v18m0 0h10a2 2 0 0 0 2-2V9M9 21H5a2 2 0 0 1-2-2V9m0 0h18"/></svg>
            </div>
            <div style="font-size:.8rem;color:var(--mut);margin-bottom:.4rem;">Belum ada resep obat.</div>
            <div style="font-size:.72rem;color:var(--mut2);">Klik Edit Resep untuk menambah obat rutin pasien ini.</div>
        </div>
        @else
        <div style="display:flex;flex-direction:column;gap:.5rem;">
            @foreach($this->drawerResep as $rsp)
            <div style="display:flex;align-items:center;gap:.75rem;padding:.65rem .85rem;background:rgba(63,207,142,.04);border:1px solid rgba(63,207,142,.15);border-radius:.45rem;">
                <div style="width:32px;height:32px;border-radius:.4rem;background:rgba(63,207,142,.1);border:1px solid rgba(63,207,142,.2);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                    <svg width="14" height="14" fill="none" stroke="var(--emer)" stroke-width="2" viewBox="0 0 24 24"><path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"/></svg>
                </div>
                <div style="flex:1;">
                    <div style="font-size:.84rem;font-weight:600;color:var(--ink);">{{ $rsp->obat?->nama_obat ?? '—' }}</div>
                    <div style="font-size:.7rem;color:var(--mut);margin-top:.1rem;">{{ $rsp->jumlah_default }} {{ $rsp->satuan }}/bulan</div>
                </div>
                <span style="font-size:.63rem;padding:.12rem .45rem;border-radius:999px;background:rgba(63,207,142,.1);border:1px solid rgba(63,207,142,.2);color:var(--emer);font-weight:600;">{{ $rsp->jumlah_default }}</span>
            </div>
            @endforeach
        </div>
        <div style="margin-top:.85rem;padding:.6rem .85rem;background:rgba(111,177,224,.07);border:1px solid rgba(111,177,224,.2);border-radius:.4rem;font-size:.72rem;color:var(--blue);">
            <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="display:inline;vertical-align:middle;margin-right:.3rem;"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
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
            <div style="display:flex;align-items:center;gap:.4rem;padding:.6rem .7rem;background:rgba(255,255,255,.03);border:1px solid var(--line);border-radius:.45rem;">
                <div style="flex:1;min-width:0;">
                    <select wire:model.live="resepRows.{{ $ri }}.obat_id" class="form-input" style="font-size:.73rem;padding:.3rem .45rem;margin-bottom:.3rem;">
                        <option value="0">— Pilih Obat —</option>
                        @foreach($this->obatList as $ob)
                        <option value="{{ $ob->id }}">{{ $ob->nama_obat }}</option>
                        @endforeach
                    </select>
                    @error('resepRows.'.$ri.'.obat_id')<div style="color:var(--red);font-size:.63rem;margin-top:.1rem;">{{ $message }}</div>@enderror
                </div>
                <div style="display:flex;align-items:center;gap:.3rem;flex-shrink:0;">
                    <input wire:model="resepRows.{{ $ri }}.jumlah_default" type="number" min="1" max="999"
                        class="form-input font-mono" style="width:55px;font-size:.75rem;padding:.3rem .4rem;text-align:center;">
                    <select wire:model="resepRows.{{ $ri }}.satuan" class="form-input" style="width:80px;font-size:.7rem;padding:.3rem .35rem;">
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
                        style="width:26px;height:26px;background:rgba(232,100,90,.08);border:1px solid rgba(232,100,90,.2);color:var(--red);border-radius:.3rem;cursor:pointer;display:flex;align-items:center;justify-content:center;flex-shrink:0;">&times;</button>
                </div>
            </div>
            @endforeach
        </div>

        <button type="button" wire:click="addResepRow"
            style="width:100%;padding:.42rem;background:transparent;border:1px dashed var(--line2);color:var(--mut);border-radius:.4rem;cursor:pointer;font-size:.75rem;display:flex;align-items:center;justify-content:center;gap:.35rem;margin-bottom:.85rem;transition:all .2s;"
            onmouseover="this.style.borderColor='var(--emer)';this.style.color='var(--emer)'" onmouseout="this.style.borderColor='var(--line2)';this.style.color='var(--mut)'">
            <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>Tambah Obat
        </button>

        <button wire:click="saveResep" class="btn-gold" style="width:100%;justify-content:center;">
            <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
            Simpan Resep
        </button>
        @endif
    </div>

    {{-- Tab: Riwayat Pengambilan --}}
    <div x-show="drawerTab==='riwayat'" style="padding:1.25rem;">
        @if($this->drawerRiwayat->count() > 0)
        <div style="position:relative;padding-left:1.5rem;">
            <div style="position:absolute;left:.6rem;top:.5rem;bottom:1rem;width:1px;background:var(--line2);"></div>
            @foreach($this->drawerRiwayat as $rwyt)
            @php
                $rStatus = $rwyt->status ?? 'dijadwalkan';
                $rColor = match($rStatus) { 'selesai' => 'var(--emer)', 'lewat' => 'var(--red)', default => 'var(--gold2)' };
                $rBg = match($rStatus) { 'selesai' => 'rgba(63,207,142,.1)', 'lewat' => 'rgba(232,100,90,.1)', default => 'rgba(217,164,65,.1)' };
                $rBorder = match($rStatus) { 'selesai' => 'rgba(63,207,142,.25)', 'lewat' => 'rgba(232,100,90,.25)', default => 'rgba(217,164,65,.25)' };
                $rLabel = match($rStatus) { 'selesai' => '&#10003; Selesai', 'lewat' => '&#9888; Lewat', default => '&#8987; Jadwal' };
            @endphp
            <div style="position:relative;margin-bottom:1rem;">
                <div style="position:absolute;left:-1.2rem;top:.4rem;width:10px;height:10px;border-radius:50%;background:{{ $rColor }};border:2px solid var(--panel);box-shadow:0 0 5px {{ $rColor }}55;"></div>
                <div class="glass-card" style="padding:.85rem;margin-left:.2rem;">
                    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:.45rem;">
                        <span style="font-size:.8rem;font-weight:600;color:var(--ink);">{{ \Carbon\Carbon::parse($rwyt->tanggal_pengambilan)->format('d M Y') }}</span>
                        <span style="font-size:.65rem;padding:.15rem .5rem;border-radius:999px;background:{{ $rBg }};border:1px solid {{ $rBorder }};color:{{ $rColor }};font-weight:600;">{!! $rLabel !!}</span>
                    </div>
                    @if($rwyt->items && $rwyt->items->count() > 0)
                    <div style="font-size:.68rem;color:var(--mut2);margin-bottom:.4rem;">{{ $rwyt->items->count() }} item obat</div>
                    <div style="display:flex;flex-wrap:wrap;gap:.3rem;">
                        @foreach($rwyt->items->take(4) as $item)
                        <span style="font-size:.66rem;padding:.15rem .45rem;border-radius:.3rem;background:rgba(255,255,255,.04);border:1px solid var(--line);color:var(--mut2);">
                            {{ $item->obat?->nama_obat ?? '—' }} &times; {{ $item->jumlah_unit }}
                        </span>
                        @endforeach
                        @if($rwyt->items->count() > 4)
                        <span style="font-size:.66rem;color:var(--mut);padding:.15rem 0;">+{{ $rwyt->items->count()-4 }} lainnya</span>
                        @endif
                    </div>
                    @else
                    <div style="font-size:.72rem;color:var(--mut);">Tidak ada data obat.</div>
                    @endif
                </div>
            </div>
            @endforeach
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
                        <span style="font-size:.63rem;padding:.14rem .45rem;border-radius:.3rem;background:{{ $sStyle['bg'] }};border:1px solid {{ $sStyle['border'] }};color:{{ $sStyle['text'] }};font-weight:600;">{{ $sLabel }}</span>
                        @if($syarat->is_wajib ?? false)
                        <span style="font-size:.62rem;padding:.12rem .4rem;border-radius:.3rem;background:rgba(232,100,90,.1);border:1px solid rgba(232,100,90,.25);color:var(--red);font-weight:700;">WAJIB</span>
                        @endif
                    </div>
                    <div style="font-size:.84rem;font-weight:600;color:var(--ink);">{{ $syarat->nama_syarat }}</div>
                    @if($syarat->deskripsi ?? null)
                    <div style="font-size:.72rem;color:var(--mut);margin-top:.2rem;">{{ $syarat->deskripsi }}</div>
                    @endif
                    <div style="font-size:.67rem;color:var(--mut2);margin-top:.3rem;">
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
