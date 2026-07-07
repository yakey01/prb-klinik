{{-- Preview laba — CLIENT-SIDE via Alpine (kalkDraft). Variabel scope: harga,klaim,faktor,volume,tipe,satuan + getters. --}}
<div class="glass-card" style="padding:1.5rem;">

{{-- Empty state --}}
<div x-show="!ready" style="text-align:center;padding:3rem 1rem;">
    <div style="width:52px;height:52px;border-radius:50%;background:rgba(255,255,255,.04);border:1px dashed var(--line2);display:flex;align-items:center;justify-content:center;margin:0 auto 1rem auto;">
        <svg width="22" height="22" fill="none" stroke="var(--mut2)" stroke-width="1.5" viewBox="0 0 24 24">
            <line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6"/>
        </svg>
    </div>
    <p style="color:var(--mut);font-size:.82rem;">Isi harga beli &amp; nilai klaim</p>
    <p style="color:var(--mut2);font-size:.74rem;margin-top:.25rem;">untuk melihat simulasi laba/rugi</p>
</div>

{{-- Result (reactive) --}}
<div x-show="ready" x-cloak>

    {{-- Status banner --}}
    <div style="display:flex;align-items:center;gap:.85rem;margin-bottom:1.25rem;padding:.85rem 1rem;border-radius:.75rem;" :style="'background:'+sGlow+';border:1px solid '+sBorder+';'">
        <div style="width:40px;height:40px;border-radius:50%;display:flex;align-items:center;justify-content:center;flex-shrink:0;" :style="'background:'+sBg+';border:1px solid '+sBorder+';'">
            <template x-if="status==='profit'"><svg width="17" height="17" fill="none" :stroke="sText" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg></template>
            <template x-if="status==='loss'"><svg width="17" height="17" fill="none" :stroke="sText" stroke-width="2.5" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg></template>
            <template x-if="status==='bep'"><svg width="17" height="17" fill="none" :stroke="sText" stroke-width="2.5" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="8" y1="12" x2="16" y2="12"/></svg></template>
        </div>
        <div style="flex:1;min-width:0;">
            <div style="font-size:1.1rem;font-weight:800;letter-spacing:.04em;" :style="'color:'+sText" x-text="statusLabel"></div>
            <div x-show="$wire.searchA" style="font-size:.7rem;color:var(--mut);margin-top:.08rem;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;max-width:180px;" x-text="$wire.searchA"></div>
        </div>
        <div style="text-align:right;flex-shrink:0;">
            <div style="font-size:.62rem;color:var(--mut);letter-spacing:.06em;text-transform:uppercase;margin-bottom:.12rem;">Laba/<span x-text="cap(satuan)"></span></div>
            <div class="font-mono" style="font-size:1.15rem;font-weight:800;" :style="'color:'+sText" x-text="rpSigned(labaUnit)"></div>
        </div>
    </div>

    {{-- KPI cards --}}
    <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:.6rem;margin-bottom:1.25rem;">
        <div style="padding:.7rem .75rem;border-radius:.6rem;background:rgba(255,255,255,.03);border:1px solid var(--line);text-align:center;">
            <div style="font-size:.6rem;font-weight:700;letter-spacing:.07em;text-transform:uppercase;color:var(--mut);margin-bottom:.3rem;" x-text="tipe==='kronis'?'Bayar BPJS':'Harga Jual'"></div>
            <div class="font-mono" style="font-size:.85rem;font-weight:800;color:var(--blue);line-height:1.1;word-break:break-all;" x-text="rp(bayar)"></div>
            <div style="font-size:.62rem;color:var(--mut2);margin-top:.18rem;">per <span x-text="satuan"></span></div>
        </div>
        <div style="padding:.7rem .75rem;border-radius:.6rem;background:rgba(255,255,255,.03);border:1px solid var(--line);text-align:center;">
            <div style="font-size:.6rem;font-weight:700;letter-spacing:.07em;text-transform:uppercase;color:var(--mut);margin-bottom:.3rem;">Margin</div>
            <div class="font-mono" style="font-size:.85rem;font-weight:800;line-height:1.1;word-break:break-all;" :style="'color:'+(margin>=0?'var(--emer)':'var(--red)')" x-text="Math.abs(margin).toFixed(1)+'%'"></div>
            <div style="font-size:.62rem;color:var(--mut2);margin-top:.18rem;" x-text="margin>=0?'dari harga bayar':'negatif'"></div>
        </div>
        <div style="padding:.7rem .75rem;border-radius:.6rem;background:rgba(255,255,255,.03);border:1px solid var(--line);text-align:center;">
            <div style="font-size:.6rem;font-weight:700;letter-spacing:.07em;text-transform:uppercase;color:var(--mut);margin-bottom:.3rem;">Laba/Bulan</div>
            <div class="font-mono" style="font-size:.85rem;font-weight:800;line-height:1.1;word-break:break-all;" :style="'color:'+(labaBln>=0?'var(--gold2)':'var(--red)')" x-text="rp(labaBln)"></div>
            <div style="font-size:.62rem;color:var(--mut2);margin-top:.18rem;"><span x-text="nVolume"></span> <span x-text="satuan"></span>/bln</div>
        </div>
    </div>

    {{-- Breakdown --}}
    <div style="margin-bottom:1.2rem;">
        <div style="font-size:.67rem;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:var(--mut);margin-bottom:.7rem;display:flex;align-items:center;gap:.4rem;">
            <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
            Proyeksi Bulanan
        </div>
        <div style="display:grid;gap:.4rem;">
            <div style="display:flex;justify-content:space-between;align-items:center;padding:.5rem .85rem;border-radius:.45rem;background:rgba(255,255,255,.02);border:1px solid rgba(31,61,48,.5);">
                <span style="font-size:.78rem;color:var(--mut);">Pendapatan (Bayar × Volume)</span>
                <span class="font-mono" style="font-size:.83rem;font-weight:700;color:var(--emer);" x-text="'+'+rp(pendapatan)"></span>
            </div>
            <div style="display:flex;justify-content:space-between;align-items:center;padding:.5rem .85rem;border-radius:.45rem;background:rgba(255,255,255,.02);border:1px solid rgba(31,61,48,.5);">
                <span style="font-size:.78rem;color:var(--mut);">Biaya (Harga Beli × Volume)</span>
                <span class="font-mono" style="font-size:.83rem;font-weight:700;color:var(--red);" x-text="'−'+rp(biaya).replace('−','')"></span>
            </div>
            <div style="display:flex;justify-content:space-between;align-items:center;padding:.6rem .85rem;border-radius:.45rem;" :style="'background:'+sBg+';border:1px solid '+sBorder+';'">
                <span style="font-size:.82rem;font-weight:700;color:var(--ink);">Laba / Bulan</span>
                <span class="font-mono" style="font-size:.95rem;font-weight:800;" :style="'color:'+sText" x-text="rpSigned(labaBln)"></span>
            </div>
            <div style="display:flex;justify-content:space-between;align-items:center;padding:.45rem .85rem;border-radius:.45rem;background:rgba(255,255,255,.02);border:1px dashed var(--line);">
                <span style="font-size:.74rem;color:var(--mut2);">Proyeksi per Tahun (×12)</span>
                <span class="font-mono" style="font-size:.8rem;font-weight:700;" :style="'color:'+(labaTahun>=0?'var(--gold2)':'var(--red)')" x-text="rpSigned(labaTahun)"></span>
            </div>
        </div>
    </div>

    {{-- Formula transparency --}}
    <div style="padding:.85rem 1rem;border-radius:.6rem;background:rgba(255,255,255,.02);border:1px solid var(--line);">
        <div style="font-size:.63rem;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:var(--mut2);margin-bottom:.6rem;display:flex;align-items:center;gap:.35rem;">
            <svg width="10" height="10" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
            Transparansi Formula
        </div>
        <div style="display:grid;gap:.4rem;">
            <div x-show="tipe==='kronis'" style="display:flex;align-items:center;gap:.4rem;flex-wrap:wrap;font-size:.75rem;">
                <span class="font-mono" style="color:var(--mut);" x-text="nKlaim.toLocaleString('id-ID')"></span>
                <span style="color:var(--mut2);">×</span>
                <span class="font-mono" style="color:var(--mut);" x-text="'(1 + '+nFaktor+')'"></span>
                <span style="color:var(--mut2);">=</span>
                <span class="font-mono" style="color:var(--blue);font-weight:700;" x-text="rp(bayar)"></span>
                <span style="font-size:.67rem;color:var(--mut2);">(bayar BPJS)</span>
            </div>
            <div style="display:flex;align-items:center;gap:.4rem;flex-wrap:wrap;font-size:.75rem;">
                <span class="font-mono" style="color:var(--mut);" x-text="bayar.toLocaleString('id-ID')"></span>
                <span style="color:var(--mut2);">−</span>
                <span class="font-mono" style="color:var(--mut);" x-text="nHarga.toLocaleString('id-ID')"></span>
                <span style="color:var(--mut2);">=</span>
                <span class="font-mono" style="font-weight:700;" :style="'color:'+(labaUnit>=0?'var(--emer)':'var(--red)')" x-text="rpSigned(labaUnit)"></span>
                <span style="font-size:.67rem;color:var(--mut2);">(per <span x-text="satuan"></span>)</span>
            </div>
            <div style="display:flex;align-items:center;gap:.4rem;flex-wrap:wrap;font-size:.75rem;">
                <span class="font-mono" style="color:var(--mut);" x-text="Math.abs(labaUnit).toLocaleString('id-ID')"></span>
                <span style="color:var(--mut2);">×</span>
                <span class="font-mono" style="color:var(--mut);" x-text="nVolume"></span>
                <span style="color:var(--mut2);">=</span>
                <span class="font-mono" style="font-weight:700;" :style="'color:'+(labaBln>=0?'var(--gold2)':'var(--red)')" x-text="rpSigned(labaBln)+'/bln'"></span>
            </div>
        </div>
    </div>

</div>
</div>
