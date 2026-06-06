<div>

{{-- ===================== KPI STATS ROW ===================== --}}
<div style="display:grid;grid-template-columns:repeat(3,1fr);gap:.85rem;margin-bottom:1.5rem;">
    <div class="kpi-card" style="border-color:rgba(217,164,65,.25);">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:.5rem;">
            <span style="font-size:.65rem;color:var(--mut);text-transform:uppercase;letter-spacing:.06em;font-weight:600;">Total Persyaratan</span>
            <span style="width:28px;height:28px;border-radius:50%;background:rgba(217,164,65,.1);display:flex;align-items:center;justify-content:center;">
                <svg width="13" height="13" fill="none" stroke="var(--gold2)" stroke-width="2.2" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
            </span>
        </div>
        <div class="font-heading" style="font-size:2rem;color:var(--gold2);line-height:1;">{{ $this->totalStats['total'] ?? 0 }}</div>
        <div style="font-size:.68rem;color:var(--mut);margin-top:.25rem;">Semua persyaratan terdaftar</div>
    </div>
    <div class="kpi-card" style="border-color:rgba(232,100,90,.25);">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:.5rem;">
            <span style="font-size:.65rem;color:var(--mut);text-transform:uppercase;letter-spacing:.06em;font-weight:600;">Yang Wajib</span>
            <span style="width:28px;height:28px;border-radius:50%;background:rgba(232,100,90,.1);display:flex;align-items:center;justify-content:center;">
                <svg width="13" height="13" fill="none" stroke="var(--red)" stroke-width="2.2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
            </span>
        </div>
        <div class="font-heading" style="font-size:2rem;color:var(--red);line-height:1;">{{ $this->totalStats['wajib'] ?? 0 }}</div>
        <div style="font-size:.68rem;color:var(--mut);margin-top:.25rem;">Persyaratan harus dipenuhi</div>
    </div>
    <div class="kpi-card" style="border-color:rgba(63,207,142,.2);">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:.5rem;">
            <span style="font-size:.65rem;color:var(--mut);text-transform:uppercase;letter-spacing:.06em;font-weight:600;">Diagnosis Terdaftar</span>
            <span style="width:28px;height:28px;border-radius:50%;background:rgba(63,207,142,.1);display:flex;align-items:center;justify-content:center;">
                <svg width="13" height="13" fill="none" stroke="var(--emer)" stroke-width="2.2" viewBox="0 0 24 24"><path d="M22 12h-4l-3 9L9 3l-3 9H2"/></svg>
            </span>
        </div>
        <div class="font-heading" style="font-size:2rem;color:var(--emer);line-height:1;">{{ $this->totalStats['diagnosis'] ?? 0 }}</div>
        <div style="font-size:.68rem;color:var(--mut);margin-top:.25rem;">Jenis diagnosis dengan syarat</div>
    </div>
</div>

{{-- ===================== FILTER PILLS ===================== --}}
<div style="display:flex;align-items:center;gap:.5rem;margin-bottom:1.25rem;flex-wrap:wrap;">
    <span style="font-size:.75rem;color:var(--mut2);font-weight:500;">Filter:</span>
    <button wire:click="$set('filterDiagnosis','')"
        style="padding:.35rem .85rem;font-size:.75rem;border-radius:999px;border:1px solid;cursor:pointer;transition:all .15s;{{ $filterDiagnosis==='' ? 'background:var(--gold);border-color:var(--gold);color:#1a0e00;font-weight:600;' : 'background:transparent;border-color:var(--line2);color:var(--mut);' }}">
        Semua
    </button>
    @foreach($this->diagnosisList as $d)
    <button wire:click="$set('filterDiagnosis','{{ $d }}')"
        style="padding:.35rem .85rem;font-size:.75rem;border-radius:999px;border:1px solid;cursor:pointer;transition:all .15s;{{ $filterDiagnosis===$d ? 'background:var(--gold);border-color:var(--gold);color:#1a0e00;font-weight:600;' : 'background:transparent;border-color:var(--line2);color:var(--mut);' }}">
        {{ $d }}
    </button>
    @endforeach
    <button wire:click="openAdd()" class="btn-gold" style="margin-left:auto;white-space:nowrap;">
        <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        Tambah Persyaratan
    </button>
</div>

{{-- ===================== ADD / EDIT FORM ===================== --}}
@if($showForm)
<div class="glass-card" style="padding:1.25rem;margin-bottom:1.25rem;border-color:rgba(217,164,65,.35);">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1rem;">
        <h3 class="font-heading" style="font-size:.95rem;color:var(--gold2);margin:0;">{{ $editId ? 'Edit Persyaratan' : 'Tambah Persyaratan Baru' }}</h3>
        <button wire:click="cancel" style="background:none;border:none;color:var(--mut);cursor:pointer;font-size:1.2rem;line-height:1;">&times;</button>
    </div>
    <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:.75rem;margin-bottom:.75rem;">
        <div>
            <label class="form-label">Diagnosis *</label>
            <select wire:model="diagnosis" class="form-input">
                <option value="">— Pilih Diagnosis —</option>
                @foreach($this->diagnosisList as $d)
                <option value="{{ $d }}">{{ $d }}</option>
                @endforeach
            </select>
            @error('diagnosis')<div style="color:var(--red);font-size:.68rem;margin-top:.15rem;">{{ $message }}</div>@enderror
        </div>
        <div style="grid-column:span 2;">
            <label class="form-label">Nama Persyaratan *</label>
            <input wire:model="nama_syarat" type="text" class="form-input" placeholder="cth: Hasil HbA1c terbaru">
            @error('nama_syarat')<div style="color:var(--red);font-size:.68rem;margin-top:.15rem;">{{ $message }}</div>@enderror
        </div>
    </div>
    <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:.75rem;margin-bottom:.75rem;">
        <div>
            <label class="form-label">Tipe</label>
            <select wire:model="tipe" class="form-input">
                <option value="dokumen">Dokumen</option>
                <option value="lab">Lab</option>
                <option value="pemeriksaan">Pemeriksaan</option>
            </select>
        </div>
        <div>
            <label class="form-label">Periode (bulan)</label>
            <input wire:model="periode_bulan" type="number" min="0" max="24" class="form-input" placeholder="0 = setiap kunjungan">
            @error('periode_bulan')<div style="color:var(--red);font-size:.68rem;margin-top:.15rem;">{{ $message }}</div>@enderror
        </div>
        <div>
            <label class="form-label">Urutan</label>
            <input wire:model="urutan" type="number" min="0" class="form-input" placeholder="0">
        </div>
        <div style="display:flex;flex-direction:column;justify-content:flex-end;">
            <label class="form-label">&nbsp;</label>
            <label style="display:flex;align-items:center;gap:.5rem;cursor:pointer;padding:.5rem .75rem;border:1px solid var(--line2);border-radius:.4rem;background:{{ $is_wajib ? 'rgba(232,100,90,.1)' : 'transparent' }};border-color:{{ $is_wajib ? 'rgba(232,100,90,.35)' : 'var(--line2)' }};transition:all .15s;">
                <input wire:model="is_wajib" type="checkbox" style="width:14px;height:14px;accent-color:var(--red);">
                <span style="font-size:.78rem;color:{{ $is_wajib ? 'var(--red)' : 'var(--mut)' }};font-weight:{{ $is_wajib ? '600' : '400' }};">Wajib</span>
            </label>
        </div>
    </div>
    <div style="margin-bottom:.85rem;">
        <label class="form-label">Deskripsi</label>
        <textarea wire:model="deskripsi" rows="2" class="form-input" placeholder="Deskripsi singkat (opsional)..." style="resize:vertical;"></textarea>
    </div>
    <div style="display:flex;gap:.6rem;justify-content:flex-end;">
        <button wire:click="cancel" class="btn-outline">Batal</button>
        <button wire:click="save" class="btn-gold">
            <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
            Simpan
        </button>
    </div>
</div>
@endif

{{-- ===================== GROUPED DISPLAY ===================== --}}
@if($this->grouped->isEmpty())
<div class="glass-card" style="text-align:center;padding:3rem 1rem;">
    <div style="width:48px;height:48px;border-radius:50%;background:rgba(255,255,255,.04);display:flex;align-items:center;justify-content:center;margin:0 auto .75rem;">
        <svg width="22" height="22" fill="none" stroke="var(--mut)" stroke-width="1.5" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
    </div>
    <div style="color:var(--mut);font-size:.84rem;">Belum ada persyaratan klaim terdaftar.</div>
    <div style="color:var(--mut2);font-size:.74rem;margin-top:.25rem;">Klik "Tambah Persyaratan" untuk memulai.</div>
</div>
@else
@foreach($this->grouped as $diagnosisName => $items)
<div style="margin-bottom:1.5rem;">
    {{-- Group Header --}}
    <div style="display:flex;align-items:center;justify-content:space-between;padding:.75rem 1rem;background:rgba(217,164,65,.06);border:1px solid rgba(217,164,65,.18);border-radius:.6rem .6rem 0 0;border-bottom:none;">
        <div style="display:flex;align-items:center;gap:.65rem;">
            <span style="width:8px;height:8px;border-radius:50%;background:var(--gold);flex-shrink:0;"></span>
            <span class="font-heading" style="font-size:.95rem;color:var(--gold2);">{{ $diagnosisName }}</span>
            <span style="font-size:.68rem;padding:.15rem .5rem;border-radius:999px;background:rgba(217,164,65,.12);border:1px solid rgba(217,164,65,.2);color:var(--gold);">{{ $items->count() }} syarat</span>
            <span style="font-size:.67rem;color:var(--mut);">{{ $items->where('is_wajib',true)->count() }} wajib</span>
        </div>
        <button wire:click="openAdd('{{ $diagnosisName }}')"
            style="background:rgba(217,164,65,.1);border:1px solid rgba(217,164,65,.25);color:var(--gold2);border-radius:.35rem;padding:.3rem .65rem;cursor:pointer;font-size:.72rem;font-weight:500;transition:all .15s;display:flex;align-items:center;gap:.35rem;"
            onmouseover="this.style.background='rgba(217,164,65,.2)'" onmouseout="this.style.background='rgba(217,164,65,.1)'">
            <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Tambah Syarat
        </button>
    </div>

    {{-- Group Table --}}
    <div class="glass-card" style="overflow-x:auto;padding:0;border-radius:0 0 .6rem .6rem;border-top:none;">
        <table class="data-table" style="width:100%;">
            <thead>
                <tr>
                    <th style="padding:.6rem 1rem;width:40px;text-align:center;">#</th>
                    <th style="padding:.6rem 1rem;text-align:left;">Nama Persyaratan</th>
                    <th style="padding:.6rem 1rem;text-align:left;width:110px;">Tipe</th>
                    <th style="padding:.6rem 1rem;text-align:left;width:130px;">Periode</th>
                    <th style="padding:.6rem 1rem;text-align:center;width:70px;">Wajib</th>
                    <th style="padding:.6rem 1rem;text-align:center;width:80px;">Status</th>
                    <th style="padding:.6rem 1rem;text-align:right;width:100px;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($items as $syarat)
                @php
                    $tipe = $syarat->tipe ?? 'dokumen';
                    $tipeStyle = match($tipe) {
                        'lab' => ['bg'=>'rgba(111,177,224,.12)','border'=>'rgba(111,177,224,.3)','text'=>'var(--blue)'],
                        'pemeriksaan' => ['bg'=>'rgba(63,207,142,.1)','border'=>'rgba(63,207,142,.25)','text'=>'var(--emer2)'],
                        default => ['bg'=>'rgba(217,164,65,.1)','border'=>'rgba(217,164,65,.25)','text'=>'var(--gold2)']
                    };
                    $tipeLabel = match($tipe) { 'lab' => 'Lab', 'pemeriksaan' => 'Pemeriksaan', default => 'Dokumen' };
                @endphp
                <tr style="border-bottom:1px solid rgba(31,61,48,.4);transition:background .12s;" onmouseover="this.style.background='rgba(255,255,255,.018)'" onmouseout="this.style.background='transparent'">
                    <td style="padding:.6rem 1rem;text-align:center;">
                        <span class="font-mono" style="font-size:.72rem;color:var(--mut2);">{{ str_pad($syarat->urutan ?: $loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                    </td>
                    <td style="padding:.6rem 1rem;">
                        <div style="font-size:.84rem;font-weight:600;color:var(--ink);">{{ $syarat->nama_syarat }}</div>
                        @if($syarat->deskripsi)
                        <div style="font-size:.7rem;color:var(--mut);margin-top:.15rem;">{{ $syarat->deskripsi }}</div>
                        @endif
                    </td>
                    <td style="padding:.6rem 1rem;">
                        <span style="font-size:.67rem;padding:.18rem .5rem;border-radius:.3rem;background:{{ $tipeStyle['bg'] }};border:1px solid {{ $tipeStyle['border'] }};color:{{ $tipeStyle['text'] }};font-weight:600;">{{ $tipeLabel }}</span>
                    </td>
                    <td style="padding:.6rem 1rem;">
                        <span style="font-size:.75rem;color:var(--mut2);">
                            @if(($syarat->periode_bulan ?? 0) > 0)
                            <svg width="10" height="10" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="display:inline;vertical-align:middle;margin-right:.2rem;"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>Setiap {{ $syarat->periode_bulan }} bln
                            @else
                            <svg width="10" height="10" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="display:inline;vertical-align:middle;margin-right:.2rem;"><polyline points="17 1 21 5 17 9"/><path d="M3 11V9a4 4 0 0 1 4-4h14"/></svg>Setiap kunjungan
                            @endif
                        </span>
                    </td>
                    <td style="padding:.6rem 1rem;text-align:center;">
                        @if($syarat->is_wajib ?? false)
                        <span style="font-size:.63rem;padding:.14rem .42rem;border-radius:.3rem;background:rgba(232,100,90,.1);border:1px solid rgba(232,100,90,.25);color:var(--red);font-weight:700;">WAJIB</span>
                        @else
                        <span style="color:var(--mut);font-size:.72rem;">—</span>
                        @endif
                    </td>
                    <td style="padding:.6rem 1rem;text-align:center;">
                        <button wire:click="toggleAktif({{ $syarat->id }})" title="Toggle aktif"
                            style="background:{{ ($syarat->is_aktif ?? true) ? 'rgba(63,207,142,.12)' : 'rgba(255,255,255,.04)' }};border:1px solid {{ ($syarat->is_aktif ?? true) ? 'rgba(63,207,142,.25)' : 'var(--line)' }};color:{{ ($syarat->is_aktif ?? true) ? 'var(--emer)' : 'var(--mut)' }};border-radius:999px;padding:.2rem .55rem;cursor:pointer;font-size:.68rem;transition:all .15s;">
                            {{ ($syarat->is_aktif ?? true) ? 'Aktif' : 'Nonaktif' }}
                        </button>
                    </td>
                    <td style="padding:.6rem 1rem;text-align:right;">
                        <div style="display:flex;align-items:center;justify-content:flex-end;gap:.3rem;">
                            <button wire:click="openEdit({{ $syarat->id }})" class="btn-outline" style="padding:.25rem .45rem;font-size:.68rem;" title="Edit">
                                <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                            </button>
                            <button wire:click="delete({{ $syarat->id }})" wire:confirm="Hapus persyaratan '{{ $syarat->nama_syarat }}'?" class="btn-danger" style="padding:.25rem .45rem;font-size:.68rem;" title="Hapus">
                                <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14H6L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4h6v2"/></svg>
                            </button>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endforeach
@endif

</div>
