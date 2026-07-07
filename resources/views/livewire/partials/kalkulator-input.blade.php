{{-- Variables: $slot (A), $cariObat, $obatId, $satuanList --}}
{{-- Input form — preview laba dihitung CLIENT-SIDE via Alpine (kalkDraft), 0 round-trip. --}}
{{-- overflow:visible !important overrides mobile .glass-card { overflow-x: auto !important } so dropdown can show --}}
<div class="glass-card" style="padding:1.4rem; overflow:visible !important;">

    {{-- Search obat (tetap Livewire — autocomplete dari DB) --}}
    <div style="margin-bottom:1.1rem;">
        <label class="form-label">Cari Obat dari Stok</label>
        <div style="position:relative;">
            <div style="position:absolute;left:.7rem;top:50%;transform:translateY(-50%);pointer-events:none;color:var(--mut2);z-index:1;">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            </div>
            <input
                type="text"
                wire:model.live.debounce.300ms="search{{ $slot }}"
                placeholder="Ketik nama obat… atau isi manual di bawah"
                class="form-input"
                style="padding-left:2.2rem;padding-right:{{ $obatId ? '2.4rem' : '.9rem' }};{{ $obatId ? 'border-color:rgba(63,207,142,.35);' : '' }}"
                autocomplete="off"
            >
            @if($obatId)
            <div style="position:absolute;right:.6rem;top:50%;transform:translateY(-50%);">
                <button type="button" wire:click="reset{{ $slot }}"
                    style="background:rgba(255,255,255,.06);border:1px solid var(--line2);border-radius:.35rem;padding:.2rem .45rem;cursor:pointer;color:var(--mut);display:flex;align-items:center;"
                    title="Reset obat">
                    <svg width="10" height="10" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                </button>
            </div>
            @endif

            @if(count($cariObat) > 0)
            <div
                style="position:absolute;left:0;right:0;top:calc(100% + 4px);background:#0e1e17;border:1px solid var(--line2);border-radius:.65rem;padding:.35rem;z-index:200;box-shadow:0 16px 40px rgba(0,0,0,.65);max-height:280px;overflow-y:auto;"
            >
                @foreach($cariObat as $ob)
                <button
                    type="button"
                    wire:click="pilihObat{{ $slot }}({{ $ob['id'] }})"
                    style="width:100%;text-align:left;padding:.6rem .85rem;border-radius:.45rem;border:none;background:transparent;cursor:pointer;"
                    onmouseover="this.style.background='rgba(255,255,255,.06)'" onmouseout="this.style.background='transparent'"
                >
                    <div style="font-size:.83rem;font-weight:600;color:var(--ink);">{{ $ob['nama_obat'] }}</div>
                    <div style="font-size:.7rem;color:var(--mut);margin-top:.12rem;display:flex;gap:.65rem;flex-wrap:wrap;">
                        <span style="color:{{ $ob['tipe_obat'] === 'kronis' ? 'var(--emer)' : 'var(--blue)' }}">{{ ucfirst(str_replace('_', ' ', $ob['tipe_obat'] ?? '—')) }}</span>
                        <span>{{ $ob['satuan'] ?? '' }}</span>
                        @if($ob['harga_beli_per_unit'] > 0)
                        <span>Beli Rp {{ number_format($ob['harga_beli_per_unit'], 0, ',', '.') }}</span>
                        @endif
                        @if($ob['klaim_bpjs_per_unit'] > 0)
                        <span style="color:var(--gold);">Klaim Rp {{ number_format($ob['klaim_bpjs_per_unit'], 0, ',', '.') }}</span>
                        @endif
                        @if($ob['kategori_diagnosis'])
                        <span style="color:var(--mut2);">{{ $ob['kategori_diagnosis'] }}</span>
                        @endif
                    </div>
                </button>
                @endforeach
            </div>
            @endif
        </div>

        @if($obatId)
        <div style="margin-top:.3rem;font-size:.7rem;color:var(--emer);display:flex;align-items:center;gap:.3rem;">
            <svg width="10" height="10" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
            Dari stok · nilai bisa diubah
        </div>
        @endif
    </div>

    {{-- Tipe obat (Alpine — instan) --}}
    <div style="margin-bottom:1rem;">
        <label class="form-label">Tipe Obat</label>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:.5rem;">
            <button type="button" x-on:click="tipe='kronis'"
                style="padding:.55rem .9rem;border-radius:.5rem;text-align:center;font-size:.78rem;font-weight:600;cursor:pointer;transition:.15s;border:none;"
                :style="tipe==='kronis' ? 'background:rgba(63,207,142,.12);outline:1px solid rgba(63,207,142,.3);color:var(--emer);' : 'background:rgba(255,255,255,.04);outline:1px solid var(--line);color:var(--mut);'">
                Kronis (PRB)
            </button>
            <button type="button" x-on:click="tipe='non_kronis'"
                style="padding:.55rem .9rem;border-radius:.5rem;text-align:center;font-size:.78rem;font-weight:600;cursor:pointer;transition:.15s;border:none;"
                :style="tipe==='non_kronis' ? 'background:rgba(111,177,224,.12);outline:1px solid rgba(111,177,224,.3);color:var(--blue);' : 'background:rgba(255,255,255,.04);outline:1px solid var(--line);color:var(--mut);'">
                Non-Kronis
            </button>
        </div>
    </div>

    {{-- Satuan pills (Alpine — instan) --}}
    <div style="margin-bottom:1rem;">
        <label class="form-label">Satuan Harga</label>
        <div style="display:flex;flex-wrap:wrap;gap:.35rem;">
            @foreach($satuanList as $key => $label)
            <button type="button" x-on:click="satuan='{{ $key }}'"
                style="padding:.28rem .7rem;border-radius:999px;font-size:.72rem;font-weight:600;cursor:pointer;border:1px solid;transition:.12s;"
                :style="satuan==='{{ $key }}' ? 'background:rgba(217,164,65,.15);border-color:rgba(217,164,65,.35);color:var(--gold2);' : 'background:rgba(255,255,255,.03);border-color:var(--line);color:var(--mut2);'"
            >{{ $label }}</button>
            @endforeach
        </div>
    </div>

    {{-- Numeric inputs (Alpine x-model — instan) --}}
    <div style="display:grid;gap:.85rem;">

        {{-- Harga Beli --}}
        <div>
            <label class="form-label">Harga Beli per <span x-text="cap(satuan)"></span></label>
            <div style="position:relative;">
                <span style="position:absolute;left:.75rem;top:50%;transform:translateY(-50%);font-size:.78rem;color:var(--mut);font-weight:600;pointer-events:none;">Rp</span>
                <input type="number" x-model="harga" placeholder="0" min="0" class="form-input" style="padding-left:2.4rem;">
            </div>
        </div>

        {{-- Klaim / Nilai --}}
        <div>
            <label class="form-label">
                <span x-text="tipe==='kronis' ? 'Nilai Klaim BPJS per '+cap(satuan) : 'Harga Jual per '+cap(satuan)"></span>
            </label>
            <div style="position:relative;">
                <span style="position:absolute;left:.75rem;top:50%;transform:translateY(-50%);font-size:.78rem;color:var(--mut);font-weight:600;pointer-events:none;">Rp</span>
                <input type="number" x-model="klaim" placeholder="0" min="0" class="form-input" style="padding-left:2.4rem;">
            </div>
            <p x-show="tipe==='kronis'" style="font-size:.68rem;color:var(--mut2);margin-top:.2rem;">Dari KMK / e-Katalog BPJS</p>
        </div>

        {{-- Faktor JF (kronis only) --}}
        <div x-show="tipe==='kronis'" x-cloak>
            <label class="form-label">Faktor Jasa Farmasi <span style="font-weight:400;text-transform:none;letter-spacing:0;color:var(--mut2);">(PMK 3/2023)</span></label>
            <input type="number" x-model="faktor" placeholder="0.15" min="0.01" max="5" step="0.01"
                class="form-input font-mono" style="font-size:.9rem;">
            <p style="font-size:.68rem;color:var(--mut2);margin-top:.2rem;">Default FKTP: 0.15 (15%)</p>
        </div>

        {{-- Volume per bulan --}}
        <div>
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:.35rem;">
                <label class="form-label" style="margin-bottom:0;">Volume per Bulan</label>
                <span class="font-mono" style="font-size:.85rem;font-weight:700;color:var(--gold2);">
                    <span x-text="nVolume"></span> <span style="font-weight:400;color:var(--mut);font-size:.7rem;" x-text="satuan"></span>
                </span>
            </div>
            <input type="range" x-model="volume" min="1" max="500" step="1" style="width:100%;margin-bottom:.5rem;">
            <input type="number" x-model="volume" min="1" class="form-input" style="text-align:center;">
        </div>

    </div>

    {{-- Reset (Livewire — bersihkan obat terpilih juga) --}}
    <div style="margin-top:1.1rem;padding-top:1rem;border-top:1px solid var(--line);">
        <button type="button" wire:click="reset{{ $slot }}" class="btn-outline" style="width:100%;justify-content:center;padding:.5rem;font-size:.8rem;">
            <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="1 4 1 10 7 10"/><path d="M3.51 15a9 9 0 102.13-9.36L1 10"/></svg>
            Reset
        </button>
    </div>

</div>
