<div class="glass-card" style="padding:1.5rem;">
    <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:1.2rem; flex-wrap:wrap; gap:1rem;">
        <div>
            <div class="font-label" style="font-size:.7rem; color:var(--mut); margin-bottom:.2rem;">Simulasi</div>
            <div class="font-heading" style="font-size:1.1rem; color:var(--ink);">Proyeksi Laba Bersih</div>
        </div>
        <div style="text-align:right;">
            <div style="font-size:.72rem; color:var(--mut); margin-bottom:.2rem;">Laba Bersih / Bulan</div>
            <div class="font-mono" style="font-size:1.8rem; font-weight:800; color:{{ $this->labaBersih >= 0 ? 'var(--emer2)' : 'var(--red2)' }};">
                {{ $this->labaBersih >= 0 ? '+' : '' }}Rp {{ number_format($this->labaBersih,0,',','.') }}
            </div>
        </div>
    </div>

    <div style="display:grid; grid-template-columns:repeat(auto-fit,minmax(260px,1fr)); gap:1.2rem; margin-bottom:1.2rem;">
        @foreach([
            ['biaya_sdm',          'SDM & Tenaga Farmasi', 10000000],
            ['biaya_utilitas',     'Utilitas (Listrik/Air/Internet)', 2000000],
            ['biaya_administrasi', 'Administrasi & Klaim', 1000000],
            ['biaya_sewa',         'Sewa / Depresiasi', 5000000],
            ['biaya_lainnya',      'Biaya Lainnya', 2000000],
        ] as [$field, $label, $max])
        <div>
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:.4rem;">
                <span class="font-label" style="font-size:.65rem; color:var(--mut);">{{ $label }}</span>
                <span class="font-mono" style="font-size:.78rem; color:var(--gold2);">Rp {{ number_format($this->$field,0,',','.') }}</span>
            </div>
            <input type="range" wire:model.live="{{ $field }}" min="0" max="{{ $max }}" step="50000">
        </div>
        @endforeach
    </div>

    <div style="display:flex; align-items:center; justify-content:space-between; padding-top:1rem; border-top:1px solid var(--line); flex-wrap:wrap; gap:.75rem;">
        <div style="font-size:.82rem; color:var(--mut);">
            Total Biaya Ops: <span class="font-mono" style="color:var(--ink);">Rp {{ number_format($this->totalBiayaOps,0,',','.') }}</span>
            &nbsp;·&nbsp;
            Laba Kotor: <span class="font-mono" style="color:var(--emer);">Rp {{ number_format($labaKotor,0,',','.') }}</span>
        </div>
        <button wire:click="simpanBiaya" class="btn-outline" style="font-size:.78rem; padding:.45rem 1rem;">
            <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h11l5 5v11a2 2 0 01-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/></svg>
            Simpan
        </button>
    </div>
</div>
