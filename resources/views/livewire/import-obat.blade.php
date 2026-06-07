<div>
    {{-- Trigger button --}}
    <button wire:click="$set('show',true)"
        style="display:flex;align-items:center;gap:.45rem;padding:.45rem .9rem;background:rgba(111,177,224,.08);border:1px solid rgba(111,177,224,.25);color:var(--blue);border-radius:.5rem;font-size:.78rem;font-weight:600;cursor:pointer;transition:all .15s;"
        onmouseover="this.style.background='rgba(111,177,224,.14)'" onmouseout="this.style.background='rgba(111,177,224,.08)'">
        <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/>
            <polyline points="7 10 12 15 17 10"/>
            <line x1="12" y1="15" x2="12" y2="3"/>
        </svg>
        Import CSV Obat
        @if($lastImportCount !== null)
            <span style="background:rgba(63,207,142,.15);color:var(--emer2);border-radius:999px;padding:.05rem .45rem;font-size:.65rem;font-weight:700;">{{ $lastImportCount }} terakhir</span>
        @endif
    </button>

    {{-- Modal --}}
    @if($show)
    <div style="position:fixed;inset:0;z-index:9999;display:flex;align-items:center;justify-content:center;padding:1rem;"
         x-data x-on:keydown.escape.window="$wire.set('show',false)">

        {{-- Backdrop --}}
        <div wire:click="$set('show',false)"
             style="position:absolute;inset:0;background:rgba(0,0,0,.6);backdrop-filter:blur(2px);"></div>

        {{-- Modal card --}}
        <div style="position:relative;z-index:1;background:var(--card);border:1px solid rgba(111,177,224,.3);border-radius:1rem;padding:1.75rem;width:100%;max-width:520px;box-shadow:0 24px 48px rgba(0,0,0,.5);">

            {{-- Header --}}
            <div style="display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:1.25rem;">
                <div style="display:flex;align-items:center;gap:.65rem;">
                    <div style="width:36px;height:36px;background:rgba(111,177,224,.12);border:1px solid rgba(111,177,224,.25);border-radius:.5rem;display:flex;align-items:center;justify-content:center;">
                        <svg width="17" height="17" fill="none" stroke="var(--blue)" stroke-width="2" viewBox="0 0 24 24">
                            <path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/>
                            <polyline points="7 10 12 15 17 10"/>
                            <line x1="12" y1="15" x2="12" y2="3"/>
                        </svg>
                    </div>
                    <div>
                        <div style="font-size:1rem;font-weight:700;color:var(--ink);">Import Katalog Obat</div>
                        <div style="font-size:.73rem;color:var(--mut);">Upload file CSV untuk menambah atau update data obat</div>
                    </div>
                </div>
                <button wire:click="$set('show',false)"
                    style="background:none;border:none;color:var(--mut);cursor:pointer;padding:.2rem;line-height:1;font-size:1.2rem;">✕</button>
            </div>

            {{-- Format info --}}
            <div style="background:rgba(111,177,224,.05);border:1px solid rgba(111,177,224,.15);border-radius:.6rem;padding:.85rem 1rem;margin-bottom:1.1rem;">
                <div style="font-size:.7rem;font-weight:700;color:var(--blue);text-transform:uppercase;letter-spacing:.06em;margin-bottom:.5rem;">Format Kolom CSV</div>
                <div style="font-family:monospace;font-size:.68rem;color:var(--gold2);line-height:1.8;word-break:break-all;">
                    nama_obat, kategori_diagnosis, kode_obat, harga_beli_per_unit,<br>
                    sumber_harga, klaim_bpjs_per_unit, faktor_jasa_farmasi, tipe_obat, satuan, stok_minimum
                </div>
                <div style="font-size:.68rem;color:var(--mut);margin-top:.5rem;">
                    Baris yang sudah ada akan di-<em>update</em> berdasarkan nama_obat. Nilai kosong memakai default.
                </div>
            </div>

            {{-- Error --}}
            @if($lastImportError)
            <div style="background:rgba(232,100,90,.08);border:1px solid rgba(232,100,90,.25);border-radius:.5rem;padding:.7rem .85rem;margin-bottom:.9rem;font-size:.78rem;color:var(--red2);">
                {{ $lastImportError }}
            </div>
            @endif

            {{-- Form --}}
            <form wire:submit="import">
                <div style="margin-bottom:1rem;">
                    <label style="display:block;font-size:.75rem;font-weight:600;color:var(--mut);margin-bottom:.4rem;text-transform:uppercase;letter-spacing:.05em;">File CSV</label>
                    <input wire:model="csvFile" type="file" accept=".csv,.txt"
                        style="width:100%;background:var(--panel);border:1px solid var(--line2);color:var(--ink);border-radius:.5rem;padding:.55rem .75rem;font-size:.8rem;cursor:pointer;">
                    @error('csvFile')
                        <div style="color:var(--red);font-size:.72rem;margin-top:.3rem;">{{ $message }}</div>
                    @enderror
                    <div wire:loading wire:target="csvFile" style="font-size:.72rem;color:var(--mut2);margin-top:.3rem;">Memuat file...</div>
                </div>

                <div style="display:flex;gap:.6rem;justify-content:flex-end;">
                    <button type="button" wire:click="$set('show',false)"
                        style="padding:.5rem 1rem;background:transparent;border:1px solid var(--line2);color:var(--mut);border-radius:.5rem;font-size:.8rem;cursor:pointer;">
                        Batal
                    </button>
                    <button type="submit" wire:loading.attr="disabled" wire:target="import"
                        style="display:flex;align-items:center;gap:.4rem;padding:.5rem 1.25rem;background:var(--blue);border:none;color:#fff;border-radius:.5rem;font-size:.8rem;font-weight:700;cursor:pointer;transition:opacity .15s;"
                        wire:loading.class="opacity-60">
                        <span wire:loading.remove wire:target="import">
                            <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" style="display:inline;vertical-align:middle;margin-right:.2rem;"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                            Upload & Import
                        </span>
                        <span wire:loading wire:target="import">Memproses...</span>
                    </button>
                </div>
            </form>

        </div>
    </div>
    @endif
</div>
