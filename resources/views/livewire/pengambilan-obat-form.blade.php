<div>
{{-- ===================== STEP INDICATOR ===================== --}}
@php
    $hasPatient  = (bool)$this->selectedPasien;
    $hasResep    = $hasPatient && !empty($rows);
    $hasChecklist= $hasPatient && count($checklist) > 0;
    $checkOk     = $hasPatient && $this->checklistOk;
    $dateOk      = $this->dateValid;
    $canDispense = $this->readyToDispense;
@endphp
<div style="display:flex;align-items:center;justify-content:center;gap:0;margin-bottom:2rem;padding:.85rem 1.5rem;background:var(--card);border:1px solid var(--line);border-radius:.6rem;">
    {{-- Step 1 --}}
    <div style="display:flex;align-items:center;gap:.45rem;">
        <div style="width:26px;height:26px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:.72rem;font-weight:700;flex-shrink:0;transition:all .3s;
            {{ $hasPatient ? 'background:rgba(63,207,142,.15);border:2px solid var(--emer);color:var(--emer);' : 'background:rgba(217,164,65,.15);border:2px solid var(--gold);color:var(--gold2);' }}">
            @if($hasPatient) <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
            @else 1 @endif
        </div>
        <span style="font-size:.77rem;font-weight:{{ $hasPatient ? '400' : '600' }};color:{{ $hasPatient ? 'var(--mut)' : 'var(--gold2)' }};">Pilih Pasien</span>
    </div>
    <div style="width:36px;height:2px;margin:0 .5rem;background:{{ $hasResep ? 'var(--emer)' : 'var(--line2)' }};border-radius:1px;transition:background .3s;"></div>
    {{-- Step 2 --}}
    <div style="display:flex;align-items:center;gap:.45rem;">
        <div style="width:26px;height:26px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:.72rem;font-weight:700;flex-shrink:0;transition:all .3s;
            {{ $hasResep && (!$hasChecklist || $checkOk) ? 'background:rgba(63,207,142,.15);border:2px solid var(--emer);color:var(--emer);' : ($hasResep ? 'background:rgba(217,164,65,.15);border:2px solid var(--gold);color:var(--gold2);' : 'background:rgba(255,255,255,.04);border:2px solid var(--line2);color:var(--mut);') }}">
            @if($hasResep && (!$hasChecklist || $checkOk)) <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
            @else 2 @endif
        </div>
        <span style="font-size:.77rem;color:{{ $hasResep ? 'var(--gold2)' : 'var(--mut)' }};font-weight:{{ $hasResep && !$checkOk ? '600' : '400' }};">{{ $hasChecklist ? 'Verifikasi & Serahkan' : 'Konfirmasi & Serahkan' }}</span>
    </div>
</div>

{{-- ===================== MAIN LAYOUT ===================== --}}
<div style="display:grid;grid-template-columns:{{ $hasChecklist ? '1fr 1.1fr 1fr' : '1fr 1fr' }};gap:1.25rem;align-items:start;">

{{-- ===== PANEL 1: PILIH PASIEN + RESEP ===== --}}
<div class="glass-card" style="padding:1.25rem;{{ $hasPatient ? 'border-color:rgba(63,207,142,.2);' : 'border-color:rgba(217,164,65,.3);' }}">
    <div style="display:flex;align-items:center;gap:.55rem;margin-bottom:1rem;">
        <div style="width:22px;height:22px;border-radius:50%;background:{{ $hasPatient ? 'rgba(63,207,142,.15)' : 'rgba(217,164,65,.15)' }};border:1.5px solid {{ $hasPatient ? 'var(--emer)' : 'var(--gold)' }};display:flex;align-items:center;justify-content:center;flex-shrink:0;">
            @if($hasPatient) <svg width="10" height="10" fill="none" stroke="var(--emer)" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
            @else <span style="font-size:.65rem;font-weight:700;color:var(--gold2);">1</span> @endif
        </div>
        <span class="font-heading" style="font-size:.88rem;color:{{ $hasPatient ? 'var(--emer)' : 'var(--gold2)' }};">Pasien & Resep Obat</span>
    </div>

    {{-- Search --}}
    @if(!$hasPatient)
    <div style="margin-bottom:.85rem;">
        <label class="form-label">Cari Nama / No. BPJS</label>
        <div style="position:relative;">
            <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="position:absolute;left:.7rem;top:50%;transform:translateY(-50%);color:var(--mut);pointer-events:none;"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            <input wire:model.live="searchPasien" type="text" placeholder="Ketik nama atau no. BPJS..."
                class="form-input" style="padding-left:2.1rem;" wire:focus="$set('showPasienDropdown',true)">
        </div>
        @error('selectedPasienId')<div style="color:var(--red);font-size:.7rem;margin-top:.2rem;">{{ $message }}</div>@enderror

        @if($showPasienDropdown && $this->pasienSuggestions->count() > 0)
        <div style="position:relative;z-index:50;">
            <div style="position:absolute;top:.25rem;left:0;right:0;background:var(--panel);border:1px solid var(--line2);border-radius:.5rem;overflow:hidden;box-shadow:0 8px 30px rgba(0,0,0,.5);">
                @foreach($this->pasienSuggestions as $p)
                <button type="button" wire:click="selectPasien({{ $p->id }})"
                    style="display:flex;align-items:center;gap:.65rem;width:100%;padding:.6rem .9rem;background:none;border:none;border-bottom:1px solid rgba(31,61,48,.4);cursor:pointer;text-align:left;transition:background .15s;"
                    onmouseover="this.style.background='rgba(217,164,65,.08)'" onmouseout="this.style.background='none'">
                    <div style="width:28px;height:28px;border-radius:50%;background:rgba(217,164,65,.12);border:1px solid rgba(217,164,65,.25);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                        <span style="color:var(--gold2);font-weight:700;font-size:.72rem;">{{ strtoupper(substr($p->nama,0,1)) }}</span>
                    </div>
                    <div style="flex:1;min-width:0;">
                        <div style="font-size:.82rem;font-weight:600;color:var(--ink);">{{ $p->nama }}</div>
                        <div class="font-mono" style="font-size:.67rem;color:var(--mut);">{{ $p->no_bpjs ?: 'No BPJS belum diisi' }}</div>
                    </div>
                    @if($p->kategori_diagnosis)
                    <span style="font-size:.62rem;padding:.1rem .38rem;border-radius:999px;background:rgba(217,164,65,.08);border:1px solid rgba(217,164,65,.18);color:var(--gold2);white-space:nowrap;flex-shrink:0;">{{ $p->kategori_diagnosis }}</span>
                    @endif
                </button>
                @endforeach
            </div>
        </div>
        @endif
    </div>
    @endif

    {{-- Selected patient card --}}
    @if($this->selectedPasien)
    @php
        $sp = $this->selectedPasien;
        $spColors = ['#3fcf8e','#d9a441','#6fb1e0','#e0a46f','#cf3f7a','#9e6fe0'];
        $spColor = $spColors[abs(crc32($sp->nama) % count($spColors))];
    @endphp
    <div style="background:rgba(63,207,142,.05);border:1px solid rgba(63,207,142,.18);border-radius:.55rem;padding:.85rem;margin-bottom:1rem;">
        <div style="display:flex;align-items:center;gap:.65rem;">
            <div style="width:40px;height:40px;border-radius:50%;background:{{ $spColor }}1a;border:2px solid {{ $spColor }}44;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                <span style="color:{{ $spColor }};font-weight:700;font-size:.88rem;">{{ strtoupper(substr($sp->nama,0,1)) }}</span>
            </div>
            <div style="flex:1;min-width:0;">
                <div style="font-weight:600;font-size:.87rem;color:var(--ink);">{{ $sp->nama }}</div>
                <div class="font-mono" style="font-size:.68rem;color:var(--mut2);">{{ $sp->no_bpjs ?: '—' }}</div>
                @if($sp->kategori_diagnosis)
                <span style="display:inline-block;margin-top:.25rem;font-size:.63rem;padding:.12rem .42rem;border-radius:999px;background:rgba(217,164,65,.1);border:1px solid rgba(217,164,65,.22);color:var(--gold2);">{{ $sp->kategori_diagnosis }}</span>
                @endif
            </div>
            <button type="button" wire:click="clearPasien"
                style="background:rgba(255,255,255,.05);border:1px solid var(--line);color:var(--mut);border-radius:.35rem;width:24px;height:24px;cursor:pointer;display:flex;align-items:center;justify-content:center;font-size:.85rem;flex-shrink:0;">&times;</button>
        </div>
    </div>

    {{-- Resep obat (read-only display) --}}
    <div style="margin-bottom:1rem;">
        <div style="font-size:.68rem;color:var(--mut);text-transform:uppercase;letter-spacing:.05em;font-weight:600;margin-bottom:.55rem;">Resep Bulanan</div>
        @if(empty($rows))
        <div style="padding:.85rem;background:rgba(232,100,90,.05);border:1px solid rgba(232,100,90,.2);border-radius:.45rem;display:flex;align-items:flex-start;gap:.55rem;">
            <svg width="14" height="14" fill="none" stroke="var(--red)" stroke-width="2" viewBox="0 0 24 24" style="flex-shrink:0;margin-top:.1rem;"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
            <div>
                <div style="font-size:.8rem;font-weight:600;color:var(--red);margin-bottom:.2rem;">Resep Belum Diatur</div>
                <div style="font-size:.71rem;color:var(--mut);">Buka halaman <strong style="color:var(--ink);">Daftar Pasien</strong> <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" style="display:inline-block;vertical-align:middle"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg> klik ikon detail pasien <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" style="display:inline-block;vertical-align:middle"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg> tab <strong style="color:var(--emer);">Resep Obat</strong> untuk menambahkan obat rutin pasien ini.</div>
            </div>
        </div>
        @else
        <div style="display:flex;flex-direction:column;gap:.38rem;">
            @foreach($rows as $i => $row)
            @php $pv = $this->rowsPreview[$i] ?? null; @endphp
            <div wire:key="resepRow-{{ $row['obat_id'] ?? $i }}" style="display:flex;flex-direction:column;gap:.35rem;padding:.5rem .75rem;background:rgba(255,255,255,.025);border:1px solid {{ $pv && !$pv['cukup'] ? 'rgba(232,100,90,.4)' : 'var(--line)' }};border-radius:.4rem;">
                <div style="display:flex;align-items:center;gap:.6rem;">
                    <div style="width:7px;height:7px;border-radius:50%;background:{{ $pv && !$pv['cukup'] ? 'var(--red2)' : 'var(--emer)' }};flex-shrink:0;"></div>
                    <div style="flex:1;font-size:.82rem;color:var(--ink);font-weight:500;">{{ $row['nama_obat'] }}</div>
                    <div style="display:flex;align-items:center;gap:.4rem;">
                        <input wire:model.live.debounce.400ms="rows.{{ $i }}.jumlah_unit" type="number" min="1" max="999"
                            class="form-input font-mono"
                            style="width:58px;font-size:.8rem;padding:.28rem .4rem;text-align:center;border-color:rgba(63,207,142,.25);">
                        <span style="font-size:.7rem;color:var(--mut);white-space:nowrap;">{{ $row['satuan'] }}</span>
                    </div>
                </div>
                @if($pv)
                <div style="display:flex;align-items:center;gap:.45rem;padding-left:1.3rem;font-size:.67rem;font-family:monospace;flex-wrap:wrap;">
                    <span style="color:var(--mut);">stok</span>
                    <span style="color:var(--mut2);">{{ number_format($pv['stok'],0,',','.') }}</span>
                    <span style="color:var(--gold2);font-weight:700;"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" style="display:inline-block;vertical-align:middle"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg></span>
                    <span style="color:{{ !$pv['cukup'] ? 'var(--red2)' : ($pv['low'] ? 'var(--gold2)' : 'var(--emer2)') }};font-weight:700;">{{ number_format($pv['sesudah'],0,',','.') }}</span>
                    <span style="color:var(--mut);">{{ $pv['satuan'] }}</span>
                    @if($pv['isi'] > 1)<span style="color:var(--mut2);opacity:.65;">({{ number_format(max(0,intdiv($pv['sesudah'],$pv['isi'])),0,',','.') }} box)</span>@endif
                    @if(!$pv['cukup'])
                    <span style="color:var(--red2);background:rgba(232,100,90,.13);border-radius:2rem;padding:.05rem .45rem;font-weight:700;"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" style="display:inline-block;vertical-align:middle"><path d="M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg> stok kurang</span>
                    @elseif($pv['low'])
                    <span style="color:var(--gold2);background:rgba(217,164,65,.13);border-radius:2rem;padding:.05rem .45rem;font-weight:600;">menipis (min {{ number_format($pv['min'],0,',','.') }})</span>
                    @endif
                </div>
                @endif
            </div>
            @endforeach
        </div>
        <div style="margin-top:.55rem;font-size:.68rem;color:var(--mut2);display:flex;align-items:center;gap:.3rem;">
            <svg width="10" height="10" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
            Sesuaikan jumlah jika berbeda dari resep default.
        </div>
        @endif
    </div>

    {{-- 3 riwayat terakhir --}}
    @if($this->riwayatPasien->count() > 0)
    <div style="border-top:1px solid var(--line);padding-top:.75rem;">
        <div style="font-size:.67rem;color:var(--mut);margin-bottom:.4rem;font-weight:600;text-transform:uppercase;letter-spacing:.04em;">3 Kunjungan Terakhir</div>
        @foreach($this->riwayatPasien->take(3) as $rv)
        @php $rvColor = match($rv->status ?? 'selesai') { 'selesai' => 'var(--emer)', 'lewat' => 'var(--red)', default => 'var(--gold2)' }; @endphp
        <div style="display:flex;align-items:center;justify-content:space-between;padding:.38rem .55rem;border-radius:.3rem;margin-bottom:.25rem;background:rgba(255,255,255,.02);border:1px solid var(--line);">
            <span style="font-size:.75rem;color:var(--ink);">{{ \Carbon\Carbon::parse($rv->tanggal_pengambilan)->format('d M Y') }}</span>
            <div style="display:flex;align-items:center;gap:.4rem;">
                <span style="font-size:.67rem;color:var(--mut);">{{ $rv->items?->count() ?? 0 }} item</span>
                <span style="width:6px;height:6px;border-radius:50%;background:{{ $rvColor }};flex-shrink:0;"></span>
            </div>
        </div>
        @endforeach
    </div>
    @endif
    @else
    {{-- Empty state --}}
    <div style="text-align:center;padding:1.75rem 0;border:1px dashed var(--line2);border-radius:.5rem;">
        <svg width="30" height="30" fill="none" stroke="var(--mut)" stroke-width="1.5" viewBox="0 0 24 24" style="margin:0 auto .5rem;display:block;"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
        <div style="font-size:.78rem;color:var(--mut);">Cari dan pilih pasien untuk memulai</div>
    </div>
    @endif
</div>

{{-- ===== PANEL 2: CHECKLIST PERSYARATAN (jika ada) ===== --}}
@if($hasChecklist)
<div class="glass-card" style="padding:1.25rem;border-color:{{ $checkOk ? 'rgba(63,207,142,.25)' : 'rgba(232,100,90,.25)' }};">
    <div style="display:flex;align-items:center;gap:.55rem;margin-bottom:.85rem;">
        <div style="width:22px;height:22px;border-radius:50%;background:{{ $checkOk ? 'rgba(63,207,142,.15)' : 'rgba(217,164,65,.15)' }};border:1.5px solid {{ $checkOk ? 'var(--emer)' : 'var(--gold)' }};display:flex;align-items:center;justify-content:center;flex-shrink:0;">
            @if($checkOk) <svg width="10" height="10" fill="none" stroke="var(--emer)" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
            @else <span style="font-size:.65rem;font-weight:700;color:var(--gold2);">2</span> @endif
        </div>
        <span class="font-heading" style="font-size:.88rem;color:{{ $checkOk ? 'var(--emer)' : 'var(--gold2)' }};">Verifikasi Persyaratan BPJS</span>
    </div>

    {{-- Progress bar --}}
    @php
        $totalItems = count($checklist);
        $doneItems  = collect($checklist)->where('terpenuhi', true)->count();
        $pct = $totalItems > 0 ? round(($doneItems / $totalItems) * 100) : 0;
        $wajibTotal = collect($checklist)->where('is_wajib', true)->count();
        $wajibDone  = collect($checklist)->where('is_wajib', true)->where('terpenuhi', true)->count();
    @endphp
    <div style="margin-bottom:1rem;padding:.75rem;background:rgba(255,255,255,.025);border:1px solid var(--line);border-radius:.45rem;">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:.5rem;">
            <span style="font-size:.68rem;color:var(--mut);">{{ $doneItems }}/{{ $totalItems }} terpenuhi</span>
            <span style="font-size:.7rem;font-weight:700;color:{{ $checkOk ? 'var(--emer)' : 'var(--gold2)' }};">{{ $pct }}%</span>
        </div>
        <div style="height:6px;background:rgba(255,255,255,.06);border-radius:999px;overflow:hidden;">
            <div style="height:100%;width:{{ $pct }}%;background:{{ $pct===100 ? 'var(--emer)' : 'var(--gold)' }};border-radius:999px;transition:width .4s ease;"></div>
        </div>
        @if($wajibTotal > 0)
        <div style="margin-top:.45rem;font-size:.66rem;color:{{ $wajibDone===$wajibTotal ? 'var(--emer)' : 'var(--red)' }};">
            {{ $wajibDone }}/{{ $wajibTotal }} persyaratan wajib terpenuhi
        </div>
        @endif
    </div>

    {{-- Checklist items --}}
    <div style="display:flex;flex-direction:column;gap:.4rem;">
        @foreach($checklist as $i => $item)
        @php
            $iTipe = $item['tipe'] ?? 'dokumen';
            $iStyle = match($iTipe) {
                'lab'        => ['bg'=>'rgba(111,177,224,.1)','border'=>'rgba(111,177,224,.25)','text'=>'var(--blue)'],
                'pemeriksaan'=> ['bg'=>'rgba(63,207,142,.08)','border'=>'rgba(63,207,142,.2)','text'=>'var(--emer2)'],
                default      => ['bg'=>'rgba(217,164,65,.08)','border'=>'rgba(217,164,65,.2)','text'=>'var(--gold2)']
            };
            $iLabel   = match($iTipe) { 'lab' => 'Lab', 'pemeriksaan' => 'Periksa', default => 'Dokumen' };
            $iChecked = (bool)($item['terpenuhi'] ?? false);
            $iWajib   = (bool)($item['is_wajib'] ?? false);
        @endphp
        <label wire:click.prevent="toggleChecklist({{ $i }})" style="display:flex;align-items:flex-start;gap:.6rem;padding:.65rem .8rem;border-radius:.4rem;cursor:pointer;transition:all .15s;border:1px solid {{ $iChecked ? 'rgba(63,207,142,.25)' : ($iWajib ? 'rgba(232,100,90,.15)' : 'var(--line)') }};background:{{ $iChecked ? 'rgba(63,207,142,.05)' : 'rgba(255,255,255,.02)' }};">
            <div style="width:16px;height:16px;margin-top:.15rem;flex-shrink:0;border-radius:.28rem;border:2px solid {{ $iChecked ? 'var(--emer)' : ($iWajib ? 'rgba(232,100,90,.5)' : 'var(--line2)') }};background:{{ $iChecked ? 'rgba(63,207,142,.2)' : 'transparent' }};display:flex;align-items:center;justify-content:center;transition:all .15s;">
                @if($iChecked)
                <svg width="9" height="9" fill="none" stroke="var(--emer)" stroke-width="3" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
                @endif
            </div>
            <div style="flex:1;min-width:0;">
                <div style="display:flex;align-items:center;gap:.35rem;flex-wrap:wrap;margin-bottom:.18rem;">
                    <span style="font-size:.62rem;padding:.1rem .38rem;border-radius:.28rem;background:{{ $iStyle['bg'] }};border:1px solid {{ $iStyle['border'] }};color:{{ $iStyle['text'] }};font-weight:600;">{{ $iLabel }}</span>
                    @if($iWajib && !$iChecked)
                    <span style="font-size:.6rem;padding:.08rem .35rem;border-radius:.28rem;background:rgba(232,100,90,.1);border:1px solid rgba(232,100,90,.25);color:var(--red);font-weight:700;">WAJIB</span>
                    @endif
                </div>
                <div style="font-size:.8rem;font-weight:{{ $iWajib ? '600' : '500' }};color:{{ $iChecked ? 'var(--mut)' : 'var(--ink)' }};{{ $iChecked ? 'text-decoration:line-through;' : '' }}">{{ $item['nama'] }}</div>
                @if(!empty($item['deskripsi']))
                <div style="font-size:.68rem;color:var(--mut);margin-top:.1rem;">{{ $item['deskripsi'] }}</div>
                @endif
            </div>
            {{-- checkmark in custom box above --}}
        </label>
        @endforeach
    </div>

    @if($this->wajibBelumChecked > 0)
    <div style="margin-top:.85rem;padding:.6rem .8rem;background:rgba(232,100,90,.07);border:1px solid rgba(232,100,90,.22);border-radius:.4rem;display:flex;align-items:center;gap:.45rem;">
        <svg width="13" height="13" fill="none" stroke="var(--red)" stroke-width="2" viewBox="0 0 24 24" style="flex-shrink:0;"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
        <span style="font-size:.73rem;color:var(--red);font-weight:500;">{{ $this->wajibBelumChecked }} persyaratan wajib belum diverifikasi</span>
    </div>
    @else
    <div style="margin-top:.85rem;padding:.6rem .8rem;background:rgba(63,207,142,.07);border:1px solid rgba(63,207,142,.2);border-radius:.4rem;display:flex;align-items:center;gap:.45rem;">
        <svg width="13" height="13" fill="none" stroke="var(--emer)" stroke-width="2.2" viewBox="0 0 24 24" style="flex-shrink:0;"><polyline points="20 6 9 17 4 12"/></svg>
        <span style="font-size:.73rem;color:var(--emer);font-weight:500;">Semua persyaratan terpenuhi — siap diserahkan</span>
    </div>
    @endif
</div>
@endif

{{-- ===== PANEL TERAKHIR: KONFIRMASI & SERAHKAN ===== --}}
<div class="glass-card" style="padding:1.25rem;{{ $canDispense ? 'border-color:rgba(63,207,142,.35);' : (!$hasPatient ? 'opacity:.55;' : '') }}">
    <div style="display:flex;align-items:center;gap:.55rem;margin-bottom:1rem;">
        <div style="width:22px;height:22px;border-radius:50%;background:{{ $canDispense ? 'rgba(63,207,142,.15)' : 'rgba(255,255,255,.04)' }};border:1.5px solid {{ $canDispense ? 'var(--emer)' : 'var(--line2)' }};display:flex;align-items:center;justify-content:center;flex-shrink:0;transition:all .3s;">
            @if($canDispense)
            <svg width="10" height="10" fill="none" stroke="var(--emer)" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
            @else
            <span style="font-size:.65rem;font-weight:700;color:var(--mut);">{{ $hasChecklist ? '3' : '2' }}</span>
            @endif
        </div>
        <span class="font-heading" style="font-size:.88rem;color:{{ $canDispense ? 'var(--emer)' : 'var(--mut)' }};">Konfirmasi Penyerahan</span>
    </div>

    <form wire:submit="save">
        {{-- Tanggal --}}
        <div style="margin-bottom:.85rem;">
            <label class="form-label" style="display:flex;align-items:center;gap:.4rem;flex-wrap:wrap;">
                Tanggal Penyerahan
                <span style="font-weight:400;color:var(--mut2);text-transform:none;letter-spacing:0;font-size:.65rem;">— bisa mundur (backdate) bila penyerahan tercatat telat</span>
            </label>
            <div style="display:flex;align-items:center;gap:.5rem;flex-wrap:wrap;">
                <input wire:model.live="tanggalPengambilan" type="date" class="form-input" style="max-width:200px;
                    {{ ($hasPatient && !$dateOk) ? 'border-color:var(--red);box-shadow:0 0 0 3px rgba(232,100,90,.12);' : '' }}"
                    {{ !$hasPatient ? 'disabled' : '' }}
                    @if($this->floorTanggalPengambilan) min="{{ $this->floorTanggalPengambilan }}" @endif
                    max="{{ $this->maxTanggal }}">
                @if($hasPatient && $this->isBackdate && $dateOk)
                <span style="display:inline-flex;align-items:center;gap:.3rem;font-size:.66rem;font-weight:700;color:var(--blue);background:rgba(111,177,224,.12);border:1px solid rgba(111,177,224,.3);border-radius:999px;padding:.18rem .55rem;">
                    <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><polyline points="11 17 6 12 11 7"/><polyline points="18 17 13 12 18 7"/></svg>
                    Backdate · {{ \Carbon\Carbon::parse($this->tanggalPengambilan)->diffForHumans() }}
                </span>
                @endif
            </div>

            {{-- Jadwal info hint --}}
            @if($hasPatient && $this->jadwalInfoLabel)
            <div style="display:flex;align-items:center;gap:.3rem;margin-top:.3rem;font-size:.67rem;color:var(--mut);">
                <svg width="10" height="10" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                <span>{{ $this->jadwalInfoLabel }}</span>
            </div>
            @endif

            {{-- HARD error: masa depan / sebelum penyerahan terakhir --}}
            @if($hasPatient && !$dateOk)
            <div style="display:flex;align-items:center;gap:.3rem;margin-top:.35rem;padding:.4rem .6rem;background:rgba(232,100,90,.07);border:1px solid rgba(232,100,90,.22);border-radius:.35rem;">
                <svg width="11" height="11" fill="none" stroke="var(--red)" stroke-width="2" viewBox="0 0 24 24" style="flex-shrink:0;"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                <span style="font-size:.68rem;color:var(--red);font-weight:500;">
                    @if($this->tanggalPengambilan > $this->maxTanggal)Tanggal tidak boleh di masa depan.
                    @elseif($this->floorTanggalPengambilan)Tidak boleh sebelum penyerahan terakhir: <strong>{{ \Carbon\Carbon::parse($this->floorTanggalPengambilan)->format('d M Y') }}</strong>.
                    @else Tanggal tidak valid. @endif
                </span>
            </div>
            {{-- SOFT warning: lebih awal dari jadwal BPJS (tidak memblokir) --}}
            @elseif($hasPatient && $this->beforeJadwal)
            <div style="display:flex;align-items:center;gap:.3rem;margin-top:.35rem;padding:.4rem .6rem;background:rgba(217,164,65,.08);border:1px solid rgba(217,164,65,.25);border-radius:.35rem;">
                <svg width="11" height="11" fill="none" stroke="var(--gold2)" stroke-width="2" viewBox="0 0 24 24" style="flex-shrink:0;"><path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                <span style="font-size:.68rem;color:var(--gold2);font-weight:500;">Lebih awal dari jadwal BPJS ({{ \Carbon\Carbon::parse($this->minTanggalPengambilan)->format('d M Y') }}) — tetap bisa disimpan, pastikan klaim valid.</span>
            </div>
            @endif

            @error('tanggalPengambilan')<div style="color:var(--red);font-size:.7rem;margin-top:.2rem;">{{ $message }}</div>@enderror
        </div>

        {{-- Ringkasan obat --}}
        @if($hasResep)
        <div style="margin-bottom:.85rem;padding:.7rem .85rem;background:var(--card);border:1px solid var(--line);border-radius:.45rem;">
            <div style="font-size:.67rem;color:var(--mut);text-transform:uppercase;letter-spacing:.04em;font-weight:600;margin-bottom:.5rem;">Obat Diserahkan</div>
            @foreach($rows as $row)
            <div style="display:flex;align-items:center;justify-content:space-between;font-size:.78rem;padding:.2rem 0;border-bottom:1px solid rgba(31,61,48,.3);">
                <span style="color:var(--ink);">{{ $row['nama_obat'] }}</span>
                <span class="font-mono" style="color:var(--emer);font-weight:600;">{{ $row['jumlah_unit'] }} <span style="color:var(--mut);font-weight:400;">{{ $row['satuan'] }}</span></span>
            </div>
            @endforeach
        </div>
        @endif

        {{-- Catatan --}}
        <div style="margin-bottom:1.25rem;">
            <label class="form-label">Catatan (opsional)</label>
            <textarea wire:model="catatan" rows="2" placeholder="Catatan tambahan..." class="form-input" style="resize:none;" {{ !$hasPatient ? 'disabled' : '' }}></textarea>
        </div>

        {{-- CTA Button --}}
        @if(!$hasPatient)
        <div style="text-align:center;padding:1.25rem;border:1px dashed var(--line2);border-radius:.5rem;color:var(--mut);font-size:.78rem;">
            Pilih pasien terlebih dahulu
        </div>
        @elseif(!$hasResep)
        <div style="padding:.85rem;background:rgba(232,100,90,.06);border:1px solid rgba(232,100,90,.2);border-radius:.45rem;text-align:center;">
            <div style="font-size:.8rem;color:var(--red);font-weight:600;margin-bottom:.3rem;">Resep Belum Ada</div>
            <div style="font-size:.72rem;color:var(--mut);">Atur dulu resep obat di halaman Daftar Pasien</div>
        </div>
        @elseif(!$dateOk)
        <div style="padding:.85rem;background:rgba(232,100,90,.06);border:1px solid rgba(232,100,90,.2);border-radius:.45rem;margin-bottom:.75rem;display:flex;align-items:flex-start;gap:.5rem;">
            <svg width="14" height="14" fill="none" stroke="var(--red)" stroke-width="2" viewBox="0 0 24 24" style="flex-shrink:0;margin-top:.1rem;"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
            <div>
                <div style="font-size:.78rem;color:var(--red);font-weight:600;margin-bottom:.15rem;">Tanggal Tidak Valid</div>
                <div style="font-size:.7rem;color:var(--mut);">
                    @if($this->tanggalPengambilan > $this->maxTanggal)Tanggal penyerahan tidak boleh di masa depan.
                    @elseif($this->floorTanggalPengambilan)Tidak boleh sebelum penyerahan terakhir <strong style="color:var(--ink);">{{ \Carbon\Carbon::parse($this->floorTanggalPengambilan)->format('d M Y') }}</strong>.
                    @else Periksa kembali tanggal penyerahan. @endif
                </div>
            </div>
        </div>
        <button type="button" disabled
            style="width:100%;display:flex;align-items:center;justify-content:center;gap:.6rem;padding:.9rem 1.25rem;background:rgba(255,255,255,.04);border:1px solid var(--line2);color:var(--mut2);border-radius:.5rem;font-size:.88rem;font-weight:600;cursor:not-allowed;">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
            Belum Jadwalnya
        </button>
        @elseif(!$checkOk)
        <div style="padding:.85rem;background:rgba(232,100,90,.06);border:1px solid rgba(232,100,90,.2);border-radius:.45rem;margin-bottom:.75rem;display:flex;align-items:center;gap:.5rem;">
            <svg width="14" height="14" fill="none" stroke="var(--red)" stroke-width="2" viewBox="0 0 24 24" style="flex-shrink:0;"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
            <span style="font-size:.75rem;color:var(--red);">Lengkapi {{ $this->wajibBelumChecked }} persyaratan wajib terlebih dahulu</span>
        </div>
        <button type="button" disabled
            style="width:100%;display:flex;align-items:center;justify-content:center;gap:.6rem;padding:.9rem 1.25rem;background:rgba(255,255,255,.04);border:1px solid var(--line2);color:var(--mut2);border-radius:.5rem;font-size:.88rem;font-weight:600;cursor:not-allowed;">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
            Obat Diserahkan
        </button>
        @else
        {{-- THE MAIN CTA --}}
        <button type="submit" wire:loading.attr="disabled"
            style="width:100%;display:flex;align-items:center;justify-content:center;gap:.65rem;padding:.95rem 1.25rem;background:linear-gradient(135deg,rgba(63,207,142,.25) 0%,rgba(63,207,142,.15) 100%);border:1.5px solid rgba(63,207,142,.5);color:var(--emer);border-radius:.55rem;font-size:.92rem;font-weight:700;cursor:pointer;letter-spacing:.01em;transition:all .2s;box-shadow:0 4px 20px rgba(63,207,142,.12);"
            onmouseover="this.style.background='linear-gradient(135deg,rgba(63,207,142,.35) 0%,rgba(63,207,142,.2) 100%)';this.style.boxShadow='0 6px 28px rgba(63,207,142,.2)'"
            onmouseout="this.style.background='linear-gradient(135deg,rgba(63,207,142,.25) 0%,rgba(63,207,142,.15) 100%)';this.style.boxShadow='0 4px 20px rgba(63,207,142,.12)'">
            <span wire:loading.remove>
                <svg width="17" height="17" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" style="display:inline;vertical-align:middle;margin-right:.2rem;"><polyline points="20 6 9 17 4 12"/></svg>
                Obat Diserahkan
            </span>
            <span wire:loading.flex style="align-items:center;gap:.5rem;">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="animation:spin 1s linear infinite;"><path d="M21 12a9 9 0 1 1-6.219-8.56"/></svg>
                Menyimpan...
            </span>
        </button>
        <div style="margin-top:.5rem;text-align:center;font-size:.68rem;color:var(--mut2);">
            Setelah diserahkan, konfirmasi jadwal kunjungan berikutnya
        </div>
        @endif
    </form>

    {{-- Success + Jadwal banner --}}
    @if($jadwalBerikutnya)
    <div style="margin-top:1rem;background:rgba(63,207,142,.07);border:1px solid rgba(63,207,142,.25);border-radius:.55rem;padding:.9rem 1rem;">
        <div style="display:flex;align-items:center;gap:.65rem;margin-bottom:.75rem;">
            <div style="width:30px;height:30px;border-radius:50%;background:rgba(63,207,142,.15);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                <svg width="14" height="14" fill="none" stroke="var(--emer)" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
            </div>
            <div>
                <div style="font-size:.83rem;font-weight:700;color:var(--emer);">Obat berhasil diserahkan!</div>
                <div style="font-size:.71rem;color:var(--mut);">
                    Jadwal saran: <strong style="color:var(--ink);">{{ \Carbon\Carbon::parse($jadwalBerikutnya)->format('d M Y') }}</strong>
                </div>
            </div>
        </div>
        @if($jadwalSudahDibuat)
        <div style="display:flex;align-items:center;gap:.45rem;padding:.55rem .75rem;background:rgba(63,207,142,.1);border:1px solid rgba(63,207,142,.3);border-radius:.4rem;">
            <svg width="13" height="13" fill="none" stroke="var(--emer)" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
            <span style="font-size:.75rem;color:var(--emer);font-weight:600;">Jadwal berikutnya sudah dibuat</span>
        </div>
        @else
        <button type="button" wire:click="buatJadwal" wire:loading.attr="disabled" wire:target="buatJadwal"
            style="width:100%;display:flex;align-items:center;justify-content:center;gap:.55rem;padding:.65rem 1rem;background:rgba(217,164,65,.12);border:1.5px solid rgba(217,164,65,.4);color:var(--gold2);border-radius:.45rem;font-size:.82rem;font-weight:600;cursor:pointer;transition:all .2s;"
            onmouseover="this.style.background='rgba(217,164,65,.22)'" onmouseout="this.style.background='rgba(217,164,65,.12)'">
            <span wire:loading.remove wire:target="buatJadwal" style="display:flex;align-items:center;gap:.5rem;">
                <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                Jadwalkan {{ \Carbon\Carbon::parse($jadwalBerikutnya)->format('d M Y') }}
            </span>
            <span wire:loading.flex wire:target="buatJadwal" style="align-items:center;gap:.4rem;">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="animation:spin 1s linear infinite;"><path d="M21 12a9 9 0 1 1-6.219-8.56"/></svg>
                Menyimpan...
            </span>
        </button>
        @endif
    </div>
    @endif
</div>

</div>

<style>
@keyframes spin { to { transform: rotate(360deg); } }
</style>
</div>
