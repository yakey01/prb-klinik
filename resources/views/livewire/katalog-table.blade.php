<div class="katalog-glass">
    <style>
        .cb-scroll::-webkit-scrollbar{width:6px;}
        .cb-scroll::-webkit-scrollbar-track{background:transparent;}
        .cb-scroll::-webkit-scrollbar-thumb{background:rgba(217,164,65,.25);border-radius:6px;}
        .cb-scroll::-webkit-scrollbar-thumb:hover{background:rgba(217,164,65,.45);}
        /* Combobox premium (bentuk sediaan + kategori diagnosis) */
        #bs-dd .bs-grp,#diag-dd .bs-grp{font-size:.58rem;font-weight:700;text-transform:uppercase;letter-spacing:.09em;color:var(--mut2);padding:.5rem .9rem .25rem;position:sticky;top:0;background:#0e1e17;}
        #bs-dd .bs-opt,#diag-dd .bs-opt{display:flex;align-items:center;gap:.55rem;padding:.46rem .9rem;font-size:.83rem;color:var(--ink);cursor:pointer;transition:background .08s;}
        #bs-dd .bs-opt.act,#diag-dd .bs-opt.act{background:rgba(217,164,65,.12);}
        #bs-dd .bs-ck,#diag-dd .bs-ck{width:13px;flex-shrink:0;text-align:center;font-size:.72rem;color:var(--gold2);font-weight:700;}
        #bs-dd .bs-empty,#diag-dd .bs-empty{padding:1rem;text-align:center;font-size:.8rem;color:var(--mut);}
        #bs-dd mark.hl,#diag-dd mark.hl{background:rgba(217,164,65,.3);color:var(--gold2);border-radius:2px;font-weight:700;font-style:normal;padding:0 1px;}
        @keyframes bsIn{from{opacity:0;transform:translateY(-5px) scaleY(.97)}to{opacity:1;transform:none}}
        #bs-dd.bs-anim,#diag-dd.bs-anim{animation:bsIn .13s cubic-bezier(.4,0,.2,1) both;transform-origin:top;}
        /* Laba/Bln · Margin cell — premium */
        .lc{text-align:right;white-space:nowrap;min-width:172px;padding-right:1.1rem!important;}
        .lc-main{font-family:'JetBrains Mono',monospace;font-size:.95rem;font-weight:800;line-height:1.1;letter-spacing:-.01em;display:flex;align-items:center;justify-content:flex-end;gap:.28rem;}
        .lc-arrow{font-size:.62rem;opacity:.85;}
        .lc-margin{font-family:'JetBrains Mono',monospace;font-size:.66rem;margin-top:.18rem;font-weight:600;}
        .lc-margin .u{color:var(--mut2);font-weight:400;}
        .lc-pill{display:inline-flex;align-items:center;gap:.32rem;margin-top:.32rem;font-size:.57rem;font-weight:700;border-radius:999px;padding:.12rem .58rem;line-height:1.5;white-space:nowrap;letter-spacing:.02em;text-transform:uppercase;}
        .lc-pill .dot{width:5px;height:5px;border-radius:50%;flex-shrink:0;}
        .lc-pill.ok{color:var(--emer2);background:rgba(63,207,142,.1);border:1px solid rgba(63,207,142,.24);}
        .lc-pill.ok .dot{background:var(--emer2);}
        .lc-pill.warn{color:var(--gold3);background:rgba(217,164,65,.1);border:1px solid rgba(217,164,65,.24);}
        .lc-pill.warn .dot{background:var(--gold3);}
        .lc-pill.bad{color:var(--red2);background:rgba(232,100,90,.1);border:1px solid rgba(232,100,90,.24);}
        .lc-pill.bad .dot{background:var(--red2);}
        /* Keterangan column */
        .ket{min-width:184px;}
        .ket-desc{font-size:.66rem;color:var(--mut);margin-top:.32rem;line-height:1.35;max-width:200px;white-space:normal;}
        .ket-muted{color:var(--mut2);font-size:.8rem;}
        /* Audit Data panel */
        .audit-overlay{position:fixed;inset:0;z-index:300;display:flex;align-items:center;justify-content:center;padding:2.5vh 2vw;}
        .audit-bg{position:absolute;inset:0;background:rgba(4,10,7,.7);backdrop-filter:blur(4px);animation:umFade .2s ease;}
        @keyframes umFade{from{opacity:0}to{opacity:1}}
        .audit-panel{position:relative;width:860px;max-width:96vw;max-height:95vh;display:flex;flex-direction:column;background:var(--bg);border:1px solid var(--line2);border-radius:1rem;box-shadow:0 30px 80px rgba(0,0,0,.6);animation:auPop .24s cubic-bezier(.16,1,.3,1);overflow:hidden;}
        @keyframes auPop{from{transform:scale(.96);opacity:0}to{transform:scale(1);opacity:1}}
        .audit-head{display:flex;align-items:flex-start;justify-content:space-between;padding:1.3rem 1.5rem 1rem;border-bottom:1px solid var(--line);}
        .audit-title{font-size:1.15rem;color:var(--ink);}
        .audit-sub{font-size:.74rem;color:var(--mut);margin-top:.25rem;max-width:46rem;line-height:1.4;}
        .audit-close{width:32px;height:32px;border-radius:.45rem;background:var(--panel);border:1px solid var(--line);color:var(--mut);cursor:pointer;font-size:1rem;flex-shrink:0;transition:.15s;}
        .audit-close:hover{color:var(--ink);border-color:var(--line3);}
        .audit-stats{display:flex;gap:1.3rem;flex-wrap:wrap;padding:.8rem 1.5rem;background:rgba(255,255,255,.02);border-bottom:1px solid var(--line);font-size:.76rem;color:var(--mut);}
        .audit-stat{display:flex;align-items:center;gap:.45rem;}
        .audit-stat strong{color:var(--ink);}
        .audit-stat-dot{width:8px;height:8px;border-radius:50%;flex-shrink:0;}
        .audit-body{overflow-y:auto;padding:.5rem .75rem;flex:1;}
        .audit-row{padding:.85rem 1rem;border:1px solid var(--line);border-radius:.7rem;margin:.5rem .25rem;background:rgba(17,36,28,.4);transition:border-color .15s;}
        .audit-row:hover{border-color:var(--line2);}
        .audit-row-main{display:flex;align-items:center;justify-content:space-between;gap:1rem;margin-bottom:.7rem;}
        .audit-obat{display:flex;flex-direction:column;gap:.12rem;min-width:0;}
        .audit-nm{font-weight:600;font-size:.88rem;color:var(--ink);}
        .audit-cat{font-size:.68rem;color:var(--mut2);}
        .audit-fields{display:flex;align-items:flex-end;gap:.6rem;flex-wrap:wrap;}
        .audit-op{color:var(--mut2);font-size:.9rem;padding-bottom:.5rem;font-weight:700;}
        .audit-field{display:flex;flex-direction:column;gap:.22rem;}
        .audit-flabel{font-size:.6rem;text-transform:uppercase;letter-spacing:.05em;color:var(--mut2);font-weight:600;}
        .audit-input{width:96px;background:var(--panel);border:1px solid var(--line2);border-radius:.45rem;padding:.4rem .55rem;font-size:.84rem;font-family:'JetBrains Mono',monospace;text-align:right;transition:border-color .15s,box-shadow .15s;}
        .audit-input:focus{outline:none;border-color:var(--gold);box-shadow:0 0 0 3px rgba(217,164,65,.12);}
        .audit-result{display:flex;flex-direction:column;gap:.22rem;margin-left:auto;align-items:flex-end;}
        .audit-rval{display:flex;flex-direction:column;align-items:flex-end;gap:.1rem;font-size:.82rem;line-height:1.25;}
        .audit-empty{display:flex;flex-direction:column;align-items:center;gap:.7rem;padding:3rem 1rem;color:var(--mut);text-align:center;}
        .audit-empty p{font-size:.85rem;margin:0;}
        .audit-foot{display:flex;align-items:center;justify-content:space-between;gap:1rem;padding:1rem 1.5rem;border-top:1px solid var(--line);flex-wrap:wrap;}
        .audit-hint{font-size:.7rem;color:var(--mut2);max-width:34rem;line-height:1.45;}
        @media(max-width:680px){.audit-fields{gap:.4rem;}.audit-input{width:76px;}.audit-op{display:none;}.audit-result{margin-left:0;width:100%;align-items:flex-start;margin-top:.4rem;}}

        /* ═══════════ GLASS 3D SKIN (scoped) ═══════════ */
        /* Ambient glow (feel "ruang kaca") di atas konten katalog */
        .katalog-glass{ position:relative; }
        .katalog-glass::before{
            content:''; position:absolute; left:-3rem; right:-3rem; top:-3rem; height:420px;
            z-index:0; pointer-events:none;
            background:
                radial-gradient(1100px 380px at 50% 0%, rgba(63,207,142,.10), transparent 62%),
                radial-gradient(760px 320px at 100% 0%, rgba(217,164,65,.06), transparent 58%);
        }
        .katalog-glass > *{ position:relative; z-index:1; }
        /* Kartu kaca berlapis: blur + highlight atas + shadow dalam */
        .katalog-glass .glass-card{
            background:
                linear-gradient(180deg, rgba(255,255,255,.045), rgba(255,255,255,.008) 40%, rgba(0,0,0,.06)) ,
                color-mix(in srgb, var(--card) 82%, transparent);
            backdrop-filter: blur(14px) saturate(1.15);
            -webkit-backdrop-filter: blur(14px) saturate(1.15);
            border:1px solid rgba(255,255,255,.10);
            border-radius:1.1rem;
            box-shadow:
                inset 0 1px 0 rgba(255,255,255,.10),
                inset 0 0 0 1px rgba(255,255,255,.02),
                0 18px 40px -12px rgba(0,0,0,.55);
        }
        /* Container tabel: sudut membulat + header gelap sticky */
        .katalog-glass .glass-card:has(.data-table){ padding:0; }
        .katalog-glass .data-table thead th{
            background:linear-gradient(180deg, rgba(20,43,33,.96), rgba(14,30,23,.96));
            backdrop-filter:blur(6px);
            position:sticky; top:0; z-index:2;
            border-bottom:1px solid rgba(255,255,255,.08);
            box-shadow:0 1px 0 rgba(0,0,0,.4);
        }
        .katalog-glass .data-table td{ border-bottom:1px solid rgba(255,255,255,.045); }
        .katalog-glass .data-table tbody tr{ transition:background .13s ease, box-shadow .13s ease; }
        .katalog-glass .data-table tbody tr:hover td{ background:rgba(255,255,255,.035)!important; }
        /* Tint baris menurut untung/rugi (data-laba bucket) */
        .katalog-glass .data-table tr[data-laba="2"]  td{ background:linear-gradient(90deg, rgba(63,207,142,.07), transparent 60%); }
        .katalog-glass .data-table tr[data-laba="1"]  td{ background:linear-gradient(90deg, rgba(63,207,142,.045), transparent 55%); }
        .katalog-glass .data-table tr[data-laba="0"]  td{ background:linear-gradient(90deg, rgba(217,164,65,.045), transparent 55%); }
        .katalog-glass .data-table tr[data-laba="-1"] td{ background:linear-gradient(90deg, rgba(232,100,90,.05), transparent 55%); }
        .katalog-glass .data-table tr[data-laba="-2"] td{ background:linear-gradient(90deg, rgba(232,100,90,.09), transparent 62%); }

        /* Input angka → PILL kaca + chevron spinner selalu tampil */
        .katalog-glass .data-table td input[type="number"]{
            border-radius:.65rem !important;
            padding:.34rem .5rem !important;
            box-shadow: inset 0 1px 2px rgba(0,0,0,.28), inset 0 0 0 1px rgba(255,255,255,.03);
            transition:border-color .15s, box-shadow .15s, transform .1s;
            -moz-appearance:auto;
        }
        .katalog-glass .data-table td input[type="number"]:focus{
            outline:none; transform:translateY(-1px);
            box-shadow: inset 0 1px 2px rgba(0,0,0,.28), 0 0 0 3px rgba(217,164,65,.16);
        }
        .katalog-glass .data-table td input[type="number"]::-webkit-inner-spin-button{
            opacity:1; height:1.5rem; margin-left:.15rem;
        }

        /* Badge (sumber & status) → efek timbul halus */
        .katalog-glass .badge, .katalog-glass .lc-pill{
            box-shadow: inset 0 1px 0 rgba(255,255,255,.08), 0 1px 3px rgba(0,0,0,.25);
            font-weight:700; letter-spacing:.02em;
        }

        /* Tombol emas → glossy 3D */
        .katalog-glass .btn-gold{
            border-radius:.7rem;
            box-shadow: inset 0 1px 0 rgba(255,255,255,.35), 0 6px 16px -4px rgba(217,164,65,.5);
        }
        .katalog-glass .btn-gold:hover{ transform:translateY(-1px); box-shadow: inset 0 1px 0 rgba(255,255,255,.4), 0 10px 22px -4px rgba(217,164,65,.6); }
        .katalog-glass .btn-outline{
            border-radius:.7rem;
            background:linear-gradient(180deg, rgba(255,255,255,.04), rgba(255,255,255,.01));
            box-shadow: inset 0 1px 0 rgba(255,255,255,.06);
        }
        .katalog-glass .btn-outline:hover{ background:linear-gradient(180deg, rgba(255,255,255,.07), rgba(255,255,255,.02)); }
        /* Kotak cari kaca */
        .katalog-glass .form-input{
            border-radius:.7rem;
            background:linear-gradient(180deg, rgba(255,255,255,.035), rgba(0,0,0,.06));
            box-shadow: inset 0 1px 2px rgba(0,0,0,.22);
        }
    </style>
    {{-- ─────────────── JUDUL HALAMAN ─────────────── --}}
    <div class="kg-title" style="display:flex;align-items:flex-end;justify-content:space-between;gap:1rem;flex-wrap:wrap;margin-bottom:1.1rem;">
        <div>
            <div style="font-size:.66rem;font-weight:700;letter-spacing:.18em;text-transform:uppercase;color:var(--mut2);margin-bottom:.15rem;">Manajemen</div>
            <h1 class="font-heading" style="font-size:1.9rem;font-weight:800;line-height:1;color:var(--ink);margin:0;">Katalog Obat <span style="color:var(--gold2);">PRB</span></h1>
        </div>
        <div style="font-size:.72rem;color:var(--mut);text-align:right;">Referensi: <strong style="color:var(--gold2);">KMK 730/2025</strong> · <strong style="color:var(--gold2);">PMK 3/2023</strong></div>
    </div>

    {{-- ─────────────── HEADER ─────────────── --}}
    <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:1.2rem; flex-wrap:wrap; gap:.75rem;">
        <div style="display:flex; gap:.5rem; align-items:center; flex-wrap:wrap;">
            <div style="position:relative; min-width:200px; max-width:280px;">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"
                     style="position:absolute;left:.75rem;top:50%;transform:translateY(-50%);color:var(--mut);">
                    <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
                </svg>
                <input wire:model.live.debounce.300ms="search" type="text" placeholder="Cari nama / kode obat…"
                       class="form-input" style="padding-left:2.2rem;">
            </div>
            <label style="display:flex;align-items:center;gap:.4rem;font-size:.75rem;color:var(--mut);cursor:pointer;white-space:nowrap;">
                <input wire:model.live="showInactive" type="checkbox"
                       style="accent-color:var(--gold);width:13px;height:13px;cursor:pointer;">
                Tampilkan nonaktif
            </label>
        </div>
        <div style="display:flex;gap:.5rem;">
            @php $auditCount = $this->auditList->count(); @endphp
            <button wire:click="openAudit" class="btn-outline" style="font-size:.8rem;position:relative;{{ $auditCount>0 ? 'border-color:rgba(232,100,90,.4);color:var(--red2);' : '' }}" title="Tinjau & perbaiki obat dengan data bermasalah">
                <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                Audit Data
                @if($auditCount>0)
                <span style="display:inline-flex;align-items:center;justify-content:center;min-width:17px;height:17px;padding:0 4px;border-radius:9px;background:var(--red);color:#fff;font-size:.62rem;font-weight:800;margin-left:.15rem;">{{ $auditCount }}</span>
                @endif
            </button>
            <button wire:click="openHarga" class="btn-outline" style="font-size:.8rem;" title="Atur margin & harga jual pasien umum">
                <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6"/></svg>
                Harga &amp; Margin
            </button>
            <button wire:click="$set('showImport',!$showImport)" class="btn-outline" style="font-size:.8rem;">
                <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                Import CSV
            </button>
            <button wire:click="openAdd" class="btn-gold">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                Tambah Obat
            </button>
        </div>
    </div>

    {{-- ─────────────── AUDIT DATA PANEL ─────────────── --}}
    @if($showAudit)
    <div class="audit-overlay">
        <div class="audit-bg" wire:click="closeAudit"></div>
        <div class="audit-panel">
            <div class="audit-head">
                <div>
                    <div class="audit-title font-heading">Audit &amp; Perbaikan Data</div>
                    <div class="audit-sub">Perbaiki faktor jasa farmasi &amp; klaim BPJS per obat. Laba dihitung ulang otomatis saat Anda ketik.</div>
                </div>
                <button wire:click="closeAudit" class="audit-close" aria-label="Tutup"><x-i name="x" :size="16" /></button>
            </div>
            @php
                $auditRows  = $this->auditList;
                $cFaktor    = $auditRows->where('issue','faktor_invalid')->count();
                $cKlaim     = $auditRows->where('issue','klaim_kosong')->count();
                $cRugi      = $auditRows->where('issue','rugi')->count();
            @endphp
            <div class="audit-stats">
                <div class="audit-stat"><span class="audit-stat-dot" style="background:var(--gold3);"></span><strong>{{ $cFaktor }}</strong> faktor jasa ngaco (≤0 / &gt;2)</div>
                <div class="audit-stat"><span class="audit-stat-dot" style="background:var(--blue);"></span><strong>{{ $cKlaim }}</strong> klaim BPJS kosong</div>
                <div class="audit-stat"><span class="audit-stat-dot" style="background:var(--red2);"></span><strong>{{ $cRugi }}</strong> rugi (bayar &lt; beli)</div>
            </div>

            <div class="audit-body cb-scroll">
                @forelse($auditRows as $r)
                <div class="audit-row" wire:key="audit-{{ $r->id }}"
                     x-data="{ klaim: {{ $r->klaim }}, faktor: {{ $r->faktor }}, beli: {{ $r->beli }}, unit: {{ $r->unit }},
                               jf(f){ f=+f; return (f<=0||f>2)?1.28:(f<1?1+f:f); },
                               get bayar(){ return this.klaim * this.jf(this.faktor); },
                               get lpu(){ return this.bayar - this.beli; },
                               get laba(){ return this.lpu * this.unit; },
                               rp(n){ n=Math.round(n||0); return (n<0?'−':'')+'Rp '+Math.abs(n).toLocaleString('id-ID'); } }">
                    <div class="audit-row-main">
                        <div class="audit-obat">
                            <span class="audit-nm">{{ $r->nama }}</span>
                            <span class="audit-cat">{{ $r->kategori ?: '—' }} · {{ $r->tipe==='kronis'?'Kronis':'Non Kronis' }}</span>
                        </div>
                        @php
                            $issueLabel = match($r->issue){ 'klaim_kosong'=>['Klaim kosong','warn'], 'faktor_invalid'=>['Faktor ngaco','warn'], default=>['Rugi','bad'] };
                        @endphp
                        <span class="lc-pill {{ $issueLabel[1]=='bad'?'bad':'warn' }}" style="text-transform:none;"><span class="dot"></span>{{ $issueLabel[0] }}</span>
                    </div>
                    <div class="audit-fields">
                        <label class="audit-field">
                            <span class="audit-flabel">Klaim BPJS/unit</span>
                            <input type="number" step="any" min="0" x-model.number="klaim"
                                   wire:change="updateKlaim({{ $r->id }}, $event.target.value)"
                                   class="audit-input" style="color:var(--blue);">
                        </label>
                        <span class="audit-op">×</span>
                        <label class="audit-field">
                            <span class="audit-flabel">Faktor jasa</span>
                            <input type="number" step="0.01" min="0.01" max="9.99" x-model.number="faktor"
                                   wire:change="updateFaktor({{ $r->id }}, $event.target.value)"
                                   class="audit-input" style="color:var(--gold2);">
                        </label>
                        <span class="audit-op">−</span>
                        <label class="audit-field">
                            <span class="audit-flabel">Beli/unit</span>
                            <input type="number" step="any" min="0" x-model.number="beli"
                                   wire:change="updateHarga({{ $r->id }}, $event.target.value)"
                                   class="audit-input" style="color:var(--red2);">
                        </label>
                        <div class="audit-result">
                            <span class="audit-flabel">Laba/unit &rarr; Laba/bln</span>
                            <div class="audit-rval">
                                <span class="font-mono" :style="'color:'+(lpu>=0?'var(--emer2)':'var(--red2)')" x-text="rp(lpu)+'/unit'"></span>
                                <span class="font-mono" :style="'font-weight:800;color:'+(laba>=0?'var(--emer2)':'var(--red2)')" x-text="(laba>=0?'+':'')+rp(laba)"></span>
                            </div>
                        </div>
                    </div>
                </div>
                @empty
                <div class="audit-empty">
                    <svg width="38" height="38" fill="none" stroke="var(--emer2)" stroke-width="1.5" viewBox="0 0 24 24"><path d="M22 11.08V12a10 10 0 11-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                    <p>Semua data obat sudah sehat. Tidak ada yang perlu dikoreksi.</p>
                </div>
                @endforelse
            </div>

            <div class="audit-foot">
                <span class="audit-hint"><x-i name="lightbulb" :size="13" style="margin-right:.3rem;" />Faktor jasa farmasi default aplikasi <strong>1,15</strong>. Nilai &lt; 1 biasanya typo (mis. 0,15 &rarr; 1,15). Setiap perubahan langsung tersimpan.</span>
                <button wire:click="closeAudit" class="btn-gold">Selesai</button>
            </div>
        </div>
    </div>
    @endif

    {{-- ─── MARGIN KEUNTUNGAN → HARGA JUAL (kasir SIM) · client-side, buka INSTAN ─── --}}
    {{-- Data di-embed sekali sbg JSON; modal di-render Alpine (x-for) tanpa round-trip Livewire. --}}
    <script type="application/json" id="margin-rows-json">@json($this->hargaList)</script>
    <div wire:ignore
         x-data="marginModal({{ round((float) \App\Models\PengaturanHarga::get()->margin_umum_default * 100, 1) }})"
         x-init="init()" @open-margin.window="openModal()">
        <template x-if="open">
        <div class="audit-overlay">
            <div class="audit-bg" @click="close()"></div>
            <div class="audit-panel">
                <div class="audit-head">
                    <div>
                        <div class="audit-title font-heading">Margin Keuntungan → Harga Jual</div>
                        <div class="audit-sub"><strong>Harga jual = harga beli × (1 + margin)</strong>. Inilah harga yang dipakai <strong style="color:var(--gold2);">kasir di SIM</strong> untuk pasien umum/tunai. Tersimpan otomatis &amp; tersinkron ke SIM tiap jam.</div>
                    </div>
                    <button @click="close()" class="audit-close" aria-label="Tutup"><x-i name="x" :size="16" /></button>
                </div>

                {{-- Global margin + pencarian instan --}}
                <div class="audit-stats" style="gap:.7rem;flex-wrap:wrap;align-items:center;">
                    <span style="font-size:.78rem;color:var(--muted);font-weight:700;">Margin default global:</span>
                    <div style="display:flex;align-items:center;gap:.25rem;">
                        <input type="number" step="0.5" min="0" max="500" x-model.number="g" class="audit-input" style="width:74px;text-align:right;color:var(--gold2);">
                        <span style="color:var(--muted);font-weight:800;">%</span>
                    </div>
                    <button class="btn-outline" style="font-size:.72rem;" @click="$wire.call('setMarginGlobal', g)">Simpan default</button>
                    <button class="btn-gold" style="font-size:.72rem;" @click="applyAll()">Terapkan ke semua obat</button>
                    <input type="text" x-model.debounce.150ms="q" @input="limit=50" placeholder="Cari obat / kode…" class="audit-input" style="width:200px;text-align:left;padding-left:.6rem;">
                    <div style="margin-left:auto;font-size:.73rem;color:var(--muted);">Menampilkan <strong x-text="visible.length"></strong> / <strong x-text="filtered.length"></strong> · <strong style="color:var(--red2);" x-text="noPrice"></strong> belum ada harga beli</div>
                </div>

                <div class="audit-body cb-scroll">
                    <template x-for="r in visible" :key="r.id">
                    <div class="audit-row">
                        <div class="audit-row-main">
                            <div class="audit-obat">
                                <span class="audit-nm">
                                    <span x-text="r.nama"></span>
                                    <span :style="`font-size:.6rem;font-weight:700;color:${tipeLabel(r.tipe)[1]};background:${tipeLabel(r.tipe)[1]}1a;border:1px solid ${tipeLabel(r.tipe)[1]}44;border-radius:999px;padding:.04rem .42rem;margin-left:.3rem;vertical-align:middle;`" x-text="tipeLabel(r.tipe)[0]"></span>
                                </span>
                                <span class="audit-cat" x-text="`${r.bentuk||'—'} · per ${r.satuan} · stok ${r.stok}`"></span>
                            </div>
                            <span class="lc-pill ok" style="text-transform:none;"><span class="dot"></span><span x-text="'Margin '+r.margin+'%'"></span></span>
                        </div>
                        <div class="audit-fields">
                            <label class="audit-field">
                                <span class="audit-flabel">Harga beli</span>
                                <input type="number" step="any" min="0" x-model.number="r.beli" @change="saveBeli(r)" class="audit-input" style="color:var(--red2);">
                            </label>
                            <span class="audit-op">×(1+</span>
                            <label class="audit-field">
                                <span class="audit-flabel">Margin %</span>
                                <input type="number" step="0.5" min="0" max="500" x-model.number="r.margin" @change="saveMargin(r)" class="audit-input" style="color:var(--gold2);">
                            </label>
                            <span class="audit-op">)=</span>
                            <div class="audit-result">
                                <span class="audit-flabel">Harga jual &rarr; Laba/unit</span>
                                <div class="audit-rval">
                                    <span class="font-mono" style="font-weight:800;color:var(--emer2)" x-text="rp(jualOf(r))"></span>
                                    <span class="font-mono" style="color:var(--emer2)" x-text="'+'+rp(labaOf(r))"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    </template>
                    <div x-show="filtered.length > visible.length" style="text-align:center;padding:.7rem;">
                        <button type="button" class="btn-outline" style="font-size:.74rem;" @click="limit+=80">Muat lebih banyak (<span x-text="filtered.length - visible.length"></span> obat lagi)</button>
                    </div>
                    <div class="audit-empty" x-show="filtered.length===0"><p>Tidak ada obat cocok dengan pencarian.</p></div>
                </div>

                <div class="audit-foot">
                    <span class="audit-hint"><x-i name="lightbulb" :size="13" style="margin-right:.3rem;" />Atur margin per-obat, atau set margin default global lalu "Terapkan ke semua". Harga jual hasil margin inilah yang <strong>dipakai kasir di SIM</strong> (tersinkron tiap jam). Ketik di kotak cari untuk filter instan.</span>
                    <button @click="close()" class="btn-gold">Selesai</button>
                </div>
            </div>
        </div>
        </template>
    </div>
    <script>
        function marginModal(defaultG){
            return {
                open:false, q:'', g:defaultG, rows:[], limit:50,
                init(){
                    try{
                        const raw=JSON.parse(document.getElementById('margin-rows-json').textContent||'[]');
                        this.rows=raw.map(r=>({ id:r.id, nama:r.nama, tipe:r.tipe, bentuk:r.bentuk,
                            satuan:r.satuan, stok:r.stok, beli:+r.beli||0,
                            margin: Math.round((+r.margin||0)*1000)/10 })); // desimal→persen
                    }catch(e){ this.rows=[]; }
                },
                openModal(){ this.open=true; this.limit=50; },
                close(){ this.open=false; },
                get filtered(){
                    const q=this.q.trim().toLowerCase();
                    if(!q) return this.rows;
                    return this.rows.filter(r=> (r.nama||'').toLowerCase().includes(q));
                },
                get visible(){ return this.filtered.slice(0, this.limit); },
                get noPrice(){ return this.rows.filter(r=>!r.beli).length; },
                jualOf(r){ return Math.round((+r.beli||0)*(1+(+r.margin||0)/100)); },
                labaOf(r){ return this.jualOf(r)-(+r.beli||0); },
                rp(n){ n=Math.round(n||0); return (n<0?'−':'')+'Rp '+Math.abs(n).toLocaleString('id-ID'); },
                tipeLabel(t){ return ({kronis:['Kronis','#6fb1e0'],non_kronis:['Umum','#3fcf8e'],bmhp:['BMHP','#d9a441']})[t]||['—','#8a8a8a']; },
                saveMargin(r){ r.margin=Math.max(0,Math.min(500,+r.margin||0)); this.$wire.call('updateMargin', r.id, r.margin); },
                saveBeli(r){ r.beli=Math.max(0,+r.beli||0); this.$wire.call('updateHarga', r.id, r.beli); },
                applyAll(){
                    if(!confirm('Terapkan margin '+this.g+'% ke SEMUA obat aktif? Harga jual dihitung ulang.')) return;
                    this.rows.forEach(r=>{ r.margin=this.g; });
                    this.$wire.call('setMarginGlobal', this.g).then(()=> this.$wire.call('applyMarginAll'));
                },
            };
        }
    </script>

    {{-- ─────────────── CSV IMPORT PANEL ─────────────── --}}
    @if($showImport)
    <div class="glass-card" style="padding:1.25rem;margin-bottom:1.25rem;border-color:var(--blue);">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:.85rem;">
            <div style="font-size:.9rem;font-weight:600;color:var(--blue);">Import Obat dari CSV</div>
            <button wire:click="$set('showImport',false)" style="background:none;border:none;color:var(--mut);cursor:pointer;font-size:1.1rem;"><x-i name="x" :size="15" /></button>
        </div>
        <div style="font-size:.75rem;color:var(--mut);margin-bottom:.75rem;">
            Kolom CSV: <span class="font-mono" style="color:var(--gold2);font-size:.7rem;">nama_obat, kategori_diagnosis, kode_obat, bentuk_sediaan, komposisi, jumlah_pasien, unit_per_bulan, harga_beli_per_unit, sumber_harga, klaim_bpjs_per_unit, faktor_jasa_farmasi, tipe_obat</span>
        </div>
        <form wire:submit="importCsv" style="display:flex;gap:.75rem;align-items:flex-end;flex-wrap:wrap;">
            <div style="flex:1;min-width:220px;">
                <label class="form-label">Pilih File CSV</label>
                <input wire:model="csvFile" type="file" accept=".csv,.txt" class="form-input" style="padding:.45rem .7rem;">
                @error('csvFile')<div style="color:var(--red);font-size:.7rem;margin-top:.2rem;">{{ $message }}</div>@enderror
            </div>
            <button type="submit" class="btn-gold" wire:loading.attr="disabled">
                <span wire:loading.remove wire:target="importCsv">Upload & Import</span>
                <span wire:loading wire:target="importCsv">Memproses...</span>
            </button>
        </form>
    </div>
    @endif

    {{-- ─────────────── FILTER PILLS ─────────────── --}}
    <div style="display:flex; gap:.4rem; flex-wrap:wrap; margin-bottom:1.2rem; align-items:center;">
        {{-- Sumbu TIPE OBAT: Kronis / Non-Kronis (segmented) --}}
        @php $tc = $this->tipeCounts; @endphp
        <div style="display:inline-flex;border:1px solid var(--line2);border-radius:999px;overflow:hidden;">
            <button wire:click="$set('filterTipe','semua')"
                style="padding:.3rem .8rem;font-size:.72rem;font-weight:700;cursor:pointer;border:none;transition:all .15s;
                    {{ $filterTipe==='semua' ? 'background:var(--gold);color:#1a0e00;' : 'background:transparent;color:var(--mut);' }}">
                Semua Tipe
            </button>
            <button wire:click="$set('filterTipe','kronis')" title="Obat kronis (PRB)"
                style="padding:.3rem .8rem;font-size:.72rem;font-weight:700;cursor:pointer;border:none;border-left:1px solid var(--line2);transition:all .15s;display:inline-flex;align-items:center;gap:.35rem;
                    {{ $filterTipe==='kronis' ? 'background:rgba(63,207,142,.22);color:var(--emer2);' : 'background:transparent;color:var(--mut);' }}">
                Kronis <span style="font-size:.62rem;padding:.02rem .35rem;border-radius:999px;background:rgba(63,207,142,.18);color:var(--emer2);">{{ $tc['kronis'] }}</span>
            </button>
            <button wire:click="$set('filterTipe','non_kronis')" title="Obat non-kronis / umum"
                style="padding:.3rem .8rem;font-size:.72rem;font-weight:700;cursor:pointer;border:none;border-left:1px solid var(--line2);transition:all .15s;display:inline-flex;align-items:center;gap:.35rem;
                    {{ $filterTipe==='non_kronis' ? 'background:rgba(111,177,224,.22);color:var(--blue);' : 'background:transparent;color:var(--mut);' }}">
                Non-Kronis <span style="font-size:.62rem;padding:.02rem .35rem;border-radius:999px;background:rgba(111,177,224,.18);color:var(--blue);">{{ $tc['non_kronis'] }}</span>
            </button>
        </div>
        <div style="width:1px;height:1.4rem;background:var(--line2);margin:0 .2rem;"></div>
        @foreach(['semua'=>'Semua','laba'=>'Laba','rugi'=>'Rugi','perlu_cek'=>'Perlu Cek'] as $val => $lbl)
        <button wire:click="$set('filter','{{ $val }}')"
            style="padding:.3rem .8rem;border-radius:999px;font-size:.73rem;cursor:pointer;border:1px solid;transition:all .2s;
                {{ $filter===$val ? 'background:var(--gold);border-color:var(--gold);color:#1a0e00;font-weight:700;' : 'background:transparent;border-color:var(--line2);color:var(--mut);' }}">
            {{ $lbl }}
        </button>
        @endforeach
        @foreach($this->kategoriList as $diag)
        <button wire:click="$set('filter','{{ $diag }}')"
            style="padding:.3rem .8rem;border-radius:999px;font-size:.7rem;cursor:pointer;border:1px solid;transition:all .2s;
                {{ $filter===$diag ? 'background:rgba(111,177,224,.2);border-color:var(--blue);color:var(--blue);font-weight:600;' : 'background:transparent;border-color:var(--line);color:var(--mut2);' }}">
            {{ $diag }}
        </button>
        @endforeach
    </div>

    {{-- ─────────────── CRUD FORM ─────────────── --}}
    @if($showForm)
    @php
        $prev_bayar      = $klaim_bpjs_per_unit * \App\Models\Obat::jfMultiplier($faktor_jasa_farmasi);
        $prev_pendapatan = $prev_bayar * $unit_per_bulan;
        $prev_biaya      = $harga_beli_per_unit * $unit_per_bulan;
        $prev_laba       = $prev_pendapatan - $prev_biaya;
    @endphp
    <div class="glass-card" style="padding:1.5rem; margin-bottom:1.5rem; border-color:var(--gold);">
        <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:1.2rem;">
            <div class="font-heading" style="font-size:1.05rem; color:var(--gold2);">
                {{ $editId ? "Edit Obat" : "Tambah Obat Baru" }}
            </div>
            <button wire:click="cancel" style="background:none;border:none;color:var(--mut);cursor:pointer;line-height:1;"><x-i name="x" :size="16" /></button>
        </div>

        <form wire:submit="save">
            {{-- Row 1: Identitas --}}
            <div style="display:grid; grid-template-columns:2fr 1fr 1fr; gap:.85rem; margin-bottom:.85rem;">
                <div>
                    <label class="form-label">Nama Obat *</label>
                    <input wire:model="nama_obat" type="text" placeholder="misal: Metformin 500mg" class="form-input">
                    @error('nama_obat')<div style="color:var(--red);font-size:.7rem;margin-top:.2rem;">{{ $message }}</div>@enderror
                </div>
                <div>
                    <label class="form-label">Kode Obat</label>
                    <input wire:model="kode_obat" type="text" placeholder="Opsional" class="form-input">
                </div>
                <div id="diag-wrap">
                    <label class="form-label" style="display:flex;align-items:center;gap:.5rem;">
                        Kategori Diagnosis
                        <span style="font-weight:400;color:var(--mut2);text-transform:none;letter-spacing:0;">— pilih atau ketik sendiri</span>
                    </label>

                    {{-- hidden input: sumber-kebenaran Livewire (preset atau custom) --}}
                    <input wire:model.live="kategori_diagnosis" id="diag-input" type="text" style="display:none" aria-hidden="true">

                    {{-- trigger ringkas --}}
                    <button type="button" id="diag-btn" onclick="DiagCb.toggle()" onkeydown="DiagCb.keyBtn(event)"
                        style="width:100%;display:flex;align-items:center;gap:.55rem;padding:.62rem .85rem;background:var(--panel);border:1px solid var(--line2);border-radius:.55rem;cursor:pointer;text-align:left;transition:border-color .15s,box-shadow .15s;outline:none;">
                        <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" style="color:var(--gold2);flex-shrink:0;"><path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"/><line x1="7" y1="7" x2="7.01" y2="7"/></svg>
                        <span id="diag-display" style="flex:1;font-size:.88rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;color:{{ $kategori_diagnosis ? 'var(--ink)' : 'var(--mut2)' }};">{{ $kategori_diagnosis ?: '— Pilih kategori (opsional) —' }}</span>
                        @if($kategori_diagnosis)
                        <span onclick="event.stopPropagation();DiagCb.clear()" title="Kosongkan" style="background:rgba(255,255,255,.08);border-radius:50%;width:18px;height:18px;display:flex;align-items:center;justify-content:center;cursor:pointer;color:var(--mut);font-size:.7rem;line-height:1;flex-shrink:0;"><svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg></span>
                        @endif
                        <svg id="diag-chevron" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" style="color:var(--mut);flex-shrink:0;transition:transform .2s;"><polyline points="6 9 12 15 18 9"/></svg>
                    </button>

                    @error('kategori_diagnosis')<div style="color:var(--red);font-size:.7rem;margin-top:.2rem;">{{ $message }}</div>@enderror
                </div>
            </div>

            {{-- Row 1.5: Bentuk / Sediaan + Komposisi --}}
            <div style="display:grid;grid-template-columns:360px 1fr;gap:.85rem;margin-bottom:1rem;align-items:start;">
                <div id="bs-wrap">
                    <label class="form-label" style="display:flex;align-items:center;gap:.5rem;">
                        Bentuk / Sediaan
                        <span style="font-weight:400;color:var(--mut2);text-transform:none;letter-spacing:0;">— pilih atau ketik sendiri</span>
                    </label>

                    {{-- hidden input: sumber-kebenaran Livewire (preset atau custom) --}}
                    <input wire:model.live="bentuk_sediaan" id="bs-input" type="text" style="display:none" aria-hidden="true">

                    {{-- trigger ringkas --}}
                    <button type="button" id="bs-btn" onclick="BsCb.toggle()" onkeydown="BsCb.keyBtn(event)"
                        style="width:100%;display:flex;align-items:center;gap:.55rem;padding:.62rem .85rem;background:var(--panel);border:1px solid var(--line2);border-radius:.55rem;cursor:pointer;text-align:left;transition:border-color .15s,box-shadow .15s;outline:none;">
                        <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" style="color:var(--gold2);flex-shrink:0;"><rect x="3" y="8.5" width="18" height="7" rx="3.5"/><line x1="12" y1="8.6" x2="12" y2="15.4"/></svg>
                        <span id="bs-display" style="flex:1;font-size:.88rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;color:{{ $bentuk_sediaan ? 'var(--ink)' : 'var(--mut2)' }};">{{ $bentuk_sediaan ?: '— Pilih bentuk sediaan —' }}</span>
                        @if($bentuk_sediaan)
                        <span onclick="event.stopPropagation();BsCb.clear()" title="Kosongkan" style="background:rgba(255,255,255,.08);border-radius:50%;width:18px;height:18px;display:flex;align-items:center;justify-content:center;cursor:pointer;color:var(--mut);font-size:.7rem;line-height:1;flex-shrink:0;"><svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg></span>
                        @endif
                        <svg id="bs-chevron" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" style="color:var(--mut);flex-shrink:0;transition:transform .2s;"><polyline points="6 9 12 15 18 9"/></svg>
                    </button>

                    @error('bentuk_sediaan')<div style="color:var(--red);font-size:.7rem;margin-top:.2rem;">{{ $message }}</div>@enderror
                </div>

                {{-- Komposisi — zat aktif + kekuatan, untuk tracking generik lintas merek --}}
                <div>
                    <label class="form-label" style="display:flex;align-items:center;gap:.5rem;">
                        Komposisi / Zat Aktif
                        <span style="font-weight:400;color:var(--mut2);text-transform:none;letter-spacing:0;">— kandungan + kekuatan</span>
                    </label>
                    <div style="position:relative;display:flex;align-items:center;">
                        <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" style="position:absolute;left:.7rem;color:#2dd4bf;flex-shrink:0;pointer-events:none;"><path d="M9 3h6M10 3v6.5L5.2 17a2 2 0 0 0 1.7 3h10.2a2 2 0 0 0 1.7-3L14 9.5V3"/><line x1="7.5" y1="14" x2="16.5" y2="14"/></svg>
                        <input wire:model.live.debounce.500ms="komposisi" type="text"
                            placeholder="mis. Amoxicillin 500 mg · Paracetamol 120 mg/5 mL"
                            class="form-input" style="padding-left:2.1rem;{{ $komposisi ? 'border-color:rgba(45,212,191,.4);' : '' }}">
                        @if($nama_obat && !$komposisi)
                        <button type="button" wire:click="$set('komposisi', '{{ addslashes($nama_obat) }}')"
                            title="Salin dari nama obat"
                            style="position:absolute;right:.45rem;display:inline-flex;align-items:center;gap:.25rem;background:rgba(45,212,191,.12);border:1px solid rgba(45,212,191,.3);color:#2dd4bf;border-radius:.4rem;padding:.22rem .5rem;font-size:.65rem;font-weight:700;cursor:pointer;letter-spacing:.02em;">
                            <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>SARAN
                        </button>
                        @endif
                    </div>
                    @error('komposisi')<div style="color:var(--red);font-size:.7rem;margin-top:.2rem;">{{ $message }}</div>@enderror
                    @if($komposisi)
                    <div style="margin-top:.35rem;display:inline-flex;align-items:center;gap:.3rem;font-size:.63rem;font-weight:600;color:#2dd4bf;background:rgba(45,212,191,.1);border:1px solid rgba(45,212,191,.25);border-radius:999px;padding:.1rem .55rem;line-height:1.5;">
                        <span style="width:4px;height:4px;border-radius:50%;background:#2dd4bf;"></span>{{ $komposisi }}
                    </div>
                    @endif
                </div>
            </div>

            {{-- Row 2: Klaim BPJS --}}
            <div style="display:grid; grid-template-columns:repeat(4,1fr) auto; gap:.85rem; margin-bottom:1rem; align-items:end;">
                <div>
                    <label class="form-label">Klaim BPJS / Unit (Rp) *
                        <span style="font-weight:400;color:var(--mut2);text-transform:none;letter-spacing:0;"> — KMK 730/2025</span>
                    </label>
                    <input wire:model.live.debounce.400ms="klaim_bpjs_per_unit" type="number" min="0" step="any" class="form-input font-mono">
                    @error('klaim_bpjs_per_unit')<div style="color:var(--red);font-size:.7rem;margin-top:.2rem;">{{ $message }}</div>@enderror
                </div>
                <div>
                    <label class="form-label">Faktor Jasa Farmasi *
                        <span style="font-weight:400;color:var(--mut2);text-transform:none;letter-spacing:0;"> — PMK 3/2023</span>
                    </label>
                    <input wire:model.live.debounce.400ms="faktor_jasa_farmasi" type="number" min="0.01" max="9.99" step="0.01" class="form-input font-mono">
                    @error('faktor_jasa_farmasi')<div style="color:var(--red);font-size:.7rem;margin-top:.2rem;">{{ $message }}</div>@enderror
                </div>
                <div>
                    <label class="form-label">Tipe Obat</label>
                    <div style="display:flex;border-radius:.5rem;overflow:hidden;border:1px solid var(--line2);height:2.6rem;">
                        <button type="button" wire:click="$set('tipe_obat','kronis')"
                            style="flex:1;font-size:.75rem;font-weight:700;cursor:pointer;border:none;line-height:1;transition:all .15s;letter-spacing:.03em;
                                {{ $tipe_obat==='kronis' ? 'background:rgba(63,207,142,.22);color:var(--emer2);' : 'background:transparent;color:var(--mut);' }}">
                            KRONIS
                        </button>
                        <button type="button" wire:click="$set('tipe_obat','non_kronis')"
                            style="flex:1;font-size:.75rem;font-weight:700;cursor:pointer;border:none;border-left:1px solid var(--line2);line-height:1;transition:all .15s;letter-spacing:.03em;
                                {{ $tipe_obat==='non_kronis' ? 'background:rgba(111,177,224,.22);color:var(--blue);' : 'background:transparent;color:var(--mut);' }}">
                            NON KRONIS
                        </button>
                    </div>
                </div>
                <div>
                    <label class="form-label">Status Obat</label>
                    <label style="display:flex;align-items:center;gap:.6rem;padding:.65rem .9rem;background:var(--panel);border:1px solid var(--line);border-radius:.5rem;cursor:pointer;">
                        <input wire:model="is_active" type="checkbox" style="accent-color:var(--emer);width:16px;height:16px;cursor:pointer;">
                        <span style="font-size:.875rem; color:{{ $is_active ? 'var(--emer2)' : 'var(--mut)' }};">
                            {{ $is_active ? 'Aktif' : 'Nonaktif' }}
                        </span>
                    </label>
                </div>
                <div style="display:flex;gap:.5rem;">
                    <button type="submit" class="btn-gold" wire:loading.attr="disabled" wire:target="save">
                        <span wire:loading.remove wire:target="save" style="display:inline-flex;align-items:center;gap:.4rem;">
                            <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h11l5 5v11a2 2 0 01-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/></svg>
                            {{ $editId ? 'Simpan Perubahan' : 'Tambah Obat' }}
                        </span>
                        <span wire:loading wire:target="save">Menyimpan…</span>
                    </button>
                    <button type="button" wire:click="cancel" class="btn-outline">Batal</button>
                </div>
            </div>

            {{-- Live Preview --}}
            <div style="background:rgba(63,207,142,.06);border:1px solid rgba(63,207,142,.15);border-radius:.6rem;padding:.75rem 1rem;display:grid;grid-template-columns:repeat(4,1fr);gap:.5rem;">
                <div>
                    <div style="font-size:.65rem;color:var(--mut);text-transform:uppercase;letter-spacing:.06em;margin-bottom:.2rem;">Bayar BPJS/unit</div>
                    <div class="font-mono" style="font-size:.82rem;color:var(--blue);">Rp {{ number_format($prev_bayar,0,',','.') }}</div>
                </div>
                <div>
                    <div style="font-size:.65rem;color:var(--mut);text-transform:uppercase;letter-spacing:.06em;margin-bottom:.2rem;">Pendapatan/Bln</div>
                    <div class="font-mono" style="font-size:.82rem;color:var(--emer2);">Rp {{ number_format($prev_pendapatan,0,',','.') }}</div>
                </div>
                <div>
                    <div style="font-size:.65rem;color:var(--mut);text-transform:uppercase;letter-spacing:.06em;margin-bottom:.2rem;">Biaya Beli/Bln</div>
                    <div class="font-mono" style="font-size:.82rem;color:var(--red2);">Rp {{ number_format($prev_biaya,0,',','.') }}</div>
                </div>
                <div>
                    <div style="font-size:.65rem;color:var(--mut);text-transform:uppercase;letter-spacing:.06em;margin-bottom:.2rem;">Estimasi Laba/Bln</div>
                    <div class="font-mono" style="font-size:.88rem;font-weight:700;color:{{ $prev_laba >= 0 ? 'var(--emer2)' : 'var(--red2)' }};">
                        {{ $prev_laba >= 0 ? '+' : '' }}Rp {{ number_format($prev_laba,0,',','.') }}
                    </div>
                </div>
            </div>
        </form>
    </div>
    @endif

    {{-- ─────────────── e-CATALOG: toolbar (lensa · kolom · kepadatan) ─────────────── --}}
    @php $ecOrder = ['nama','diagnosis','pasien','item','beli','sumber','klaim','bayar','pend','laba','ket','aksi']; @endphp
    <style>
        [x-cloak]{ display:none !important; }
        .ec-toolbar{ display:flex; flex-wrap:wrap; align-items:center; gap:.5rem; margin-bottom:.7rem; }
        .ec-lens{ display:inline-flex; align-items:center; gap:.3rem; background:rgba(255,255,255,.03); border:1px solid var(--line2); border-radius:.6rem; padding:.25rem .35rem; }
        .ec-lens-label{ font-size:.62rem; text-transform:uppercase; letter-spacing:.08em; color:var(--mut2); padding:0 .25rem 0 .35rem; }
        .ec-lens-btn{ background:none; border:none; color:var(--mut); font-size:.76rem; font-weight:600; padding:.3rem .65rem; border-radius:.45rem; cursor:pointer; transition:.15s; }
        .ec-lens-btn:hover{ color:var(--ink); background:rgba(255,255,255,.05); }
        .ec-lens-btn.active{ color:#0c1611; background:var(--gold2); }
        .ec-btn{ display:inline-flex; align-items:center; gap:.35rem; background:rgba(255,255,255,.03); border:1px solid var(--line2); color:var(--mut); font-size:.76rem; font-weight:600; padding:.42rem .7rem; border-radius:.55rem; cursor:pointer; transition:.15s; }
        .ec-btn:hover{ color:var(--ink); border-color:var(--gold); }
        .ec-btn .ec-dim{ color:var(--mut2); font-weight:500; }
        .ec-colmenu{ position:relative; }
        .ec-colpop{ position:absolute; top:calc(100% + .35rem); left:0; z-index:50; min-width:210px; background:#0e1e17; border:1px solid var(--line2); border-radius:.7rem; box-shadow:0 20px 60px rgba(0,0,0,.7); padding:.4rem; }
        .ec-colpop-h{ font-size:.62rem; text-transform:uppercase; letter-spacing:.08em; color:var(--mut2); padding:.35rem .55rem .3rem; }
        .ec-colrow{ display:flex; align-items:center; gap:.55rem; padding:.4rem .55rem; border-radius:.45rem; cursor:pointer; font-size:.8rem; color:var(--ink); }
        .ec-colrow:hover{ background:rgba(255,255,255,.04); }
        .ec-colrow.locked{ opacity:.5; cursor:not-allowed; }
        .ec-colrow input{ accent-color:var(--gold2); width:15px; height:15px; cursor:pointer; }
        .ec-colreset{ width:100%; text-align:left; margin-top:.25rem; background:none; border:none; border-top:1px solid var(--line2); color:var(--gold2); font-size:.74rem; padding:.5rem .55rem .35rem; cursor:pointer; }
        .ec-colreset:hover{ color:var(--gold3); }
        /* sembunyikan kolom per lensa/pilihan */
        @foreach($ecOrder as $c)
        .ec-h-{{ $c }} [data-col="{{ $c }}"]{ display:none !important; }
        @endforeach
        /* kepadatan padat */
        .data-table.ec-dense td, .data-table.ec-dense th{ padding-top:.28rem !important; padding-bottom:.28rem !important; font-size:.75rem; }
        /* pin kolom Obat (kiri) & Aksi (kanan) saat scroll horizontal */
        .data-table .ec-pin{ position:sticky; left:0; z-index:3; background:#0d1812; box-shadow:1px 0 0 var(--line2); }
        .data-table thead .ec-pin{ z-index:5; background:#0f1c15; }
        .data-table tfoot .ec-pin{ z-index:5; background:#101d16; }
        .data-table .ec-pin-r{ position:sticky; right:0; z-index:3; background:#0d1812; box-shadow:-1px 0 0 var(--line2); }
        .data-table thead .ec-pin-r, .data-table tfoot .ec-pin-r{ z-index:5; background:#0f1c15; }
        /* baris TOTAL */
        .data-table tfoot .ec-total td{ border-top:2px solid var(--gold); background:rgba(217,164,65,.05); font-weight:600; padding-top:.6rem; padding-bottom:.6rem; }
        .data-table tfoot .ec-total td[data-col="nama"]{ color:var(--gold3); font-size:.78rem; letter-spacing:.02em; }
        /* tombol toggle aktif (Grup / Heatmap) */
        .ec-btn.ec-on{ color:#0c1611; background:var(--gold2); border-color:var(--gold2); }
        /* heatmap baris (untung hijau → rugi merah) */
        .data-table.ec-heat tbody tr[data-laba="2"]{ background:rgba(63,207,142,.16); }
        .data-table.ec-heat tbody tr[data-laba="1"]{ background:rgba(63,207,142,.07); }
        .data-table.ec-heat tbody tr[data-laba="-1"]{ background:rgba(232,100,90,.09); }
        .data-table.ec-heat tbody tr[data-laba="-2"]{ background:rgba(232,100,90,.18); }
        .data-table.ec-heat tbody tr[data-laba="2"] .ec-pin{ background:#11241a; }
        .data-table.ec-heat tbody tr[data-laba="-2"] .ec-pin{ background:#241413; }
        /* header grup kategori */
        .data-table tbody tr.ec-group td{ background:rgba(217,164,65,.07); border-top:1px solid var(--gold); cursor:pointer; padding:.5rem .75rem; }
        .data-table tbody tr.ec-group:hover td{ background:rgba(217,164,65,.11); }
        .ec-group-bar{ display:flex; align-items:center; gap:.6rem; flex-wrap:wrap; }
        .ec-group-chev{ transition:transform .15s; color:var(--gold2); }
        .ec-group-chev.col{ transform:rotate(-90deg); }
        .ec-group-name{ font-weight:700; color:var(--gold3); font-size:.82rem; }
        .ec-group-count{ font-size:.68rem; color:var(--mut2); background:rgba(255,255,255,.04); padding:.1rem .45rem; border-radius:999px; }
        .ec-group-stat{ margin-left:auto; display:flex; gap:1.1rem; align-items:center; font-size:.75rem; font-family:'JetBrains Mono',monospace; }
        /* checkbox bandingkan */
        .ec-cmp-chk{ accent-color:var(--gold2); width:15px; height:15px; cursor:pointer; flex-shrink:0; margin-top:.15rem; }
        /* bar bandingkan mengambang */
        .ec-cmpbar{ position:fixed; left:50%; transform:translateX(-50%); bottom:1.2rem; z-index:200; display:flex; align-items:center; gap:.85rem; background:#0e1e17; border:1px solid var(--gold); border-radius:.85rem; padding:.55rem .65rem .55rem 1rem; box-shadow:0 18px 50px rgba(0,0,0,.6); }
        .ec-cmpbar-txt{ font-size:.8rem; color:var(--ink); }
        .ec-cmpbar-txt strong{ color:var(--gold3); }
        .ec-cmpbar-btn{ display:inline-flex; align-items:center; gap:.35rem; background:var(--gold2); color:#0c1611; border:none; font-weight:700; font-size:.8rem; padding:.5rem .9rem; border-radius:.55rem; cursor:pointer; }
        .ec-cmpbar-btn:disabled{ opacity:.45; cursor:not-allowed; }
        .ec-cmpbar-x{ background:none; border:1px solid var(--line2); color:var(--mut); border-radius:.5rem; padding:.45rem .6rem; cursor:pointer; font-size:.74rem; }
        .ec-cmpbar-x:hover{ color:var(--red2); border-color:var(--red2); }
        /* panel bandingkan */
        .cmp-wrap{ overflow-x:auto; }
        .cmp-table{ width:100%; border-collapse:separate; border-spacing:0; font-size:.82rem; }
        .cmp-table th, .cmp-table td{ padding:.6rem .8rem; text-align:left; border-bottom:1px solid var(--line2); vertical-align:top; }
        .cmp-table thead th{ position:sticky; top:0; background:#0f1c15; z-index:2; }
        .cmp-table thead th.cmp-metric{ z-index:3; }
        .cmp-table .cmp-metric{ position:sticky; left:0; background:#0d1812; color:var(--mut); font-weight:600; min-width:130px; box-shadow:1px 0 0 var(--line2); }
        .cmp-table .cmp-obat{ font-weight:700; color:var(--gold3); font-size:.86rem; min-width:150px; }
        .cmp-table .cmp-obat .cmp-cat{ display:block; font-weight:500; font-size:.68rem; color:var(--mut2); margin-top:.15rem; }
        .cmp-table td.cmp-num{ font-family:'JetBrains Mono',monospace; text-align:right; }
        .cmp-table td.cmp-best{ background:rgba(63,207,142,.13); color:var(--emer2); font-weight:700; }
        .cmp-table td.cmp-worst{ background:rgba(232,100,90,.12); color:var(--red2); }
        .cmp-badge{ display:inline-block; font-size:.62rem; font-weight:700; padding:.05rem .4rem; border-radius:999px; margin-left:.3rem; vertical-align:middle; }
        .cmp-badge.win{ background:rgba(63,207,142,.18); color:var(--emer2); }
        /* paginasi */
        .ec-pager{ display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:.75rem; margin-top:.85rem; padding:.65rem .2rem; border-top:1px solid var(--line2); }
        .ec-pager-info{ font-size:.78rem; color:var(--mut); }
        .ec-pager-info strong{ color:var(--gold3); }
        .ec-pager-ctrl{ display:flex; align-items:center; gap:.5rem; flex-wrap:wrap; }
        .ec-pager-sel{ background:rgba(255,255,255,.04); border:1px solid var(--line2); color:var(--ink); border-radius:.45rem; padding:.32rem .5rem; font-size:.76rem; cursor:pointer; }
        .ec-pager-btn{ background:rgba(255,255,255,.03); border:1px solid var(--line2); color:var(--ink); font-size:.76rem; font-weight:600; padding:.42rem .8rem; border-radius:.5rem; cursor:pointer; }
        .ec-pager-btn:hover:not(:disabled){ border-color:var(--gold); color:var(--gold3); }
        .ec-pager-btn:disabled{ opacity:.4; cursor:not-allowed; }
        .ec-pager-pg{ font-size:.76rem; color:var(--mut); font-family:'JetBrains Mono',monospace; padding:0 .35rem; }
    </style>
    <script>
        function ecatalog(){
            const ORDER  = @json($ecOrder);
            const LOCKED = ['nama','aksi'];
            const LABELS = { nama:'Obat', diagnosis:'Diagnosis', pasien:'Pasien', item:'Item/Bln', beli:'Beli/Unit', sumber:'Sumber', klaim:'Klaim BPJS', bayar:'Bayar BPJS', pend:'Pend/Bln', laba:'Untung/Rugi', ket:'Keterangan', aksi:'Aksi' };
            const LENSES = [
                { id:'keuangan', label:'Keuangan', cols:['nama','pasien','item','beli','klaim','bayar','pend','laba','ket','aksi'] },
                { id:'ringkas',  label:'Ringkas',  cols:['nama','beli','bayar','laba','aksi'] },
                { id:'lengkap',  label:'Lengkap',  cols:ORDER.slice() },
            ];
            const KEY = 'ecatalog_katalog_v1';
            return {
                order:ORDER, locked:LOCKED, labels:LABELS, lenses:LENSES,
                visible:{}, lens:'keuangan', density:'comfortable', colOpen:false,
                heat:false, collapsed:{},
                init(){
                    let saved=null; try{ saved=JSON.parse(localStorage.getItem(KEY)); }catch(e){}
                    if(saved && saved.visible){ this.visible=saved.visible; this.lens=saved.lens||'custom'; this.density=saved.density||'comfortable'; this.heat=!!saved.heat; }
                    else { this.applyLens('keuangan'); }
                    // pastikan kolom terkunci selalu tampil
                    this.locked.forEach(c=>this.visible[c]=true);
                },
                toggleHeat(){ this.heat=!this.heat; this.save(); },
                toggleGroup(i){ this.collapsed[i]=!this.collapsed[i]; },
                isCollapsed(i){ return !!this.collapsed[i]; },
                cmp: [],
                inCmp(id){ return this.cmp.includes(id); },
                toggleCmp(id){ const i=this.cmp.indexOf(id); if(i>-1){ this.cmp.splice(i,1); } else if(this.cmp.length<5){ this.cmp.push(id); } else { this.$dispatch('toast',{type:'error',message:'Maksimal 5 obat untuk dibandingkan.'}); } },
                applyLens(id){
                    const l=this.lenses.find(x=>x.id===id); if(!l) return;
                    const v={}; this.order.forEach(c=>v[c]=l.cols.includes(c));
                    this.locked.forEach(c=>v[c]=true);
                    this.visible=v; this.lens=id; this.save();
                },
                toggleCol(c){ if(this.locked.includes(c)) return; this.visible[c]=!this.visible[c]; this.lens='custom'; this.save(); },
                toggleDensity(){ this.density = this.density==='dense'?'comfortable':'dense'; this.save(); },
                reset(){ this.applyLens('keuangan'); this.colOpen=false; },
                save(){ try{ localStorage.setItem(KEY, JSON.stringify({visible:this.visible, lens:this.lens, density:this.density, heat:this.heat})); }catch(e){} },
                get shownCount(){ return this.order.filter(c=>this.visible[c]).length; },
                ecTableClass(){ return [...this.order.filter(c=>!this.visible[c]).map(c=>'ec-h-'+c), this.density==='dense'?'ec-dense':'', this.heat?'ec-heat':''].join(' '); },
            };
        }
    </script>
    <div x-data="ecatalog()" x-init="init()" class="ec-wrap">
        <div class="ec-toolbar">
            <div class="ec-lens">
                <span class="ec-lens-label">Lensa</span>
                <template x-for="l in lenses" :key="l.id">
                    <button class="ec-lens-btn" :class="{active: lens===l.id}" @click="applyLens(l.id)" x-text="l.label"></button>
                </template>
            </div>
            <div class="ec-colmenu" @click.outside="colOpen=false">
                <button class="ec-btn" @click="colOpen=!colOpen">
                    <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="8" y1="3" x2="8" y2="21"/><line x1="16" y1="3" x2="16" y2="21"/><rect x="3" y="3" width="18" height="18" rx="2"/></svg>
                    Kolom <span class="ec-dim" x-text="'('+shownCount+'/'+order.length+')'"></span>
                </button>
                <div class="ec-colpop" x-show="colOpen" x-cloak @click.stop>
                    <div class="ec-colpop-h">Tampilkan kolom</div>
                    <template x-for="c in order" :key="c">
                        <label class="ec-colrow" :class="{locked: locked.includes(c)}">
                            <input type="checkbox" :checked="visible[c]" :disabled="locked.includes(c)" @change="toggleCol(c)">
                            <span x-text="labels[c]"></span>
                        </label>
                    </template>
                    <button class="ec-colreset" @click="reset()">↺ Reset ke lensa Keuangan</button>
                </div>
            </div>
            <button class="ec-btn" @click="toggleDensity()">
                <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
                <span x-text="density==='dense'?'Padat':'Lega'"></span>
            </button>
            <div style="width:1px;height:20px;background:var(--line2);margin:0 .15rem;"></div>
            <button class="ec-btn {{ $groupMode ? 'ec-on' : '' }}" wire:click="toggleGroupMode" wire:loading.attr="disabled">
                <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/></svg>
                Grup Kategori
            </button>
            <button class="ec-btn" :class="{ 'ec-on': heat }" @click="toggleHeat()">
                <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M8.5 14.5A2.5 2.5 0 0 0 11 12c0-1.38-.5-2-1-3-1.072-2.143-.224-4.054 2-6 .5 2.5 2 4.9 4 6.5 2 1.6 3 3.5 3 5.5a7 7 0 1 1-14 0c0-1.153.433-2.294 1-3a2.5 2.5 0 0 0 2.5 2.5z"/></svg>
                Heatmap
            </button>
            <div style="width:1px;height:20px;background:var(--line2);margin:0 .15rem;"></div>
            <button type="button" @click="$dispatch('open-margin')" class="ec-btn"
                    style="background:linear-gradient(135deg,rgba(217,164,65,.18),rgba(217,164,65,.08));border-color:rgba(217,164,65,.5);color:var(--gold2);font-weight:700;"
                    title="Atur margin keuntungan → harga jual yang dipakai kasir di SIM">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6"/></svg>
                Margin Keuntungan
            </button>
        </div>

    {{-- ─────────────── TABLE ─────────────── --}}
    <div class="glass-card" style="overflow-x:auto;">
        <table class="data-table" :class="ecTableClass()">
            <thead>
                <tr>
                    <th data-col="nama" class="ec-pin" wire:click="sortBy('nama_obat')" style="min-width:160px;">
                        Obat @if($sortBy==='nama_obat')<x-i :name="$sortDir==='asc'?'arrow-up':'arrow-down'" :size="11" />@endif
                    </th>
                    <th data-col="diagnosis" wire:click="sortBy('kategori_diagnosis')" style="min-width:100px;">
                        Diagnosis @if($sortBy==='kategori_diagnosis')<x-i :name="$sortDir==='asc'?'arrow-up':'arrow-down'" :size="11" />@endif
                    </th>
                    <th data-col="pasien" wire:click="sortBy('jumlah_pasien')" style="text-align:right;">
                        Pasien @if($sortBy==='jumlah_pasien')<x-i :name="$sortDir==='asc'?'arrow-up':'arrow-down'" :size="11" />@endif
                    </th>
                    <th data-col="item" style="text-align:right;">Item/Bln</th>
                    <th data-col="beli" style="text-align:right; min-width:90px;">Beli/Unit ✎</th>
                    <th data-col="sumber" style="text-align:center;">Sumber</th>
                    <th data-col="klaim" style="text-align:right; min-width:90px;">Klaim BPJS ✎</th>
                    <th data-col="bayar" style="text-align:right;">Bayar BPJS</th>
                    <th data-col="pend" style="text-align:right;">Pend/Bln</th>
                    <th data-col="laba" style="text-align:right; min-width:140px;" title="Untung/rugi per bulan + untung/rugi per unit">Untung/Rugi</th>
                    <th data-col="ket" style="min-width:184px;">Keterangan</th>
                    <th data-col="aksi" class="ec-pin-r" style="text-align:center; min-width:100px;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $gMeta = []; $gIndex = []; $prevKat = '__none__';
                    if ($groupMode) {
                        $gi = 0;
                        foreach ($this->obatList->groupBy(fn ($o) => $o->kategori_diagnosis ?: 'Tanpa kategori') as $kat => $items) {
                            $gMeta[$kat]  = ['idx' => $gi, 'count' => $items->count(), 'laba' => $items->sum('laba'), 'pend' => $items->sum('pendapatan_bulan')];
                            $gIndex[$kat] = $gi; $gi++;
                        }
                    }
                @endphp
                @forelse($this->pagedList as $obat)
                @php
                    $curKat = $obat->kategori_diagnosis ?: 'Tanpa kategori';
                    // Heatmap berdasarkan laba PER UNIT (profitabilitas intrinsik) — bermakna walau belum ada volume bulan ini.
                    $lpuB = (float) $obat->laba_per_unit;
                    $labaBucket = $lpuB > 0 ? ($lpuB >= 300 ? 2 : 1) : ($lpuB < 0 ? ($lpuB <= -300 ? -2 : -1) : 0);
                    // idx grup baris: -1 saat mode datar (tak pernah collapse). Selalu dirender agar morph Livewire patch nilainya.
                    $rowIdx = $groupMode ? ($gIndex[$curKat] ?? 0) : -1;
                @endphp
                @if($groupMode && $curKat !== $prevKat)
                @php $m = $gMeta[$curKat]; $prevKat = $curKat; @endphp
                <tr class="ec-group" wire:key="grp-{{ $m['idx'] }}" @click="toggleGroup({{ $m['idx'] }})">
                    <td colspan="12">
                        <div class="ec-group-bar">
                            <svg class="ec-group-chev" :class="{ col: isCollapsed({{ $m['idx'] }}) }" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="6 9 12 15 18 9"/></svg>
                            <span class="ec-group-name">{{ $curKat }}</span>
                            <span class="ec-group-count">{{ $m['count'] }} obat</span>
                            <span class="ec-group-stat">
                                <span style="color:var(--mut);">Pend <span style="color:var(--ink);">Rp {{ number_format($m['pend'],0,',','.') }}</span></span>
                                <span style="color:{{ $m['laba']>=0?'var(--emer2)':'var(--red2)' }};font-weight:700;">{{ $m['laba']>=0?'untung +':'rugi −' }}Rp {{ number_format(abs($m['laba']),0,',','.') }}</span>
                            </span>
                        </div>
                    </td>
                </tr>
                @endif
                <tr wire:key="obat-{{ $obat->id }}" data-laba="{{ $labaBucket }}" data-katidx="{{ $rowIdx }}" x-show="!isCollapsed({{ $rowIdx }})" style="{{ !$obat->is_active ? 'opacity:.45;' : '' }}">
                    <td data-col="nama" class="ec-pin">
                        <div style="display:flex;align-items:flex-start;gap:.5rem;">
                        <input type="checkbox" class="ec-cmp-chk" title="Pilih untuk dibandingkan" :checked="inCmp({{ $obat->id }})" @click="toggleCmp({{ $obat->id }})">
                        <div style="min-width:0;flex:1;">
                        <div style="font-weight:500;line-height:1.3;">{{ $obat->nama_obat }}</div>
                        <div style="display:flex;align-items:center;gap:.4rem;flex-wrap:wrap;margin-top:.18rem;">
                            @if($obat->kode_obat)
                            <span style="font-size:.68rem;color:var(--mut2);font-family:'JetBrains Mono',monospace;">{{ $obat->kode_obat }}</span>
                            @endif
                            @if($obat->bentuk_sediaan)
                            <span title="Bentuk / sediaan" style="display:inline-flex;align-items:center;gap:.28rem;font-size:.63rem;font-weight:600;color:var(--blue);background:rgba(111,177,224,.1);border:1px solid rgba(111,177,224,.22);border-radius:999px;padding:.05rem .5rem;line-height:1.5;">
                                <span style="width:4px;height:4px;border-radius:50%;background:var(--blue);"></span>{{ $obat->bentuk_sediaan }}
                            </span>
                            @endif
                            @if($obat->komposisi)
                            <span title="Komposisi / zat aktif" style="display:inline-flex;align-items:center;gap:.28rem;font-size:.63rem;font-weight:600;color:#2dd4bf;background:rgba(45,212,191,.1);border:1px solid rgba(45,212,191,.22);border-radius:999px;padding:.05rem .5rem;line-height:1.5;">
                                <svg width="9" height="9" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M9 3h6M10 3v6.5L5.2 17a2 2 0 0 0 1.7 3h10.2a2 2 0 0 0 1.7-3L14 9.5V3"/></svg>{{ $obat->komposisi }}
                            </span>
                            @endif
                        </div>
                        </div></div>
                    </td>
                    <td data-col="diagnosis" style="color:var(--mut);font-size:.77rem;">{{ $obat->kategori_diagnosis ?? '—' }}</td>

                    <td data-col="pasien" style="text-align:right;">
                        @if($obat->dari_resep)
                        <div style="display:flex;align-items:center;gap:.3rem;justify-content:flex-end;">
                            <span title="Data real dari resep aktif pasien" style="color:var(--emer);font-size:.6rem;line-height:1;">●</span>
                            <span class="font-mono" style="font-size:.82rem;color:var(--emer2);font-weight:600;">{{ $obat->jumlah_pasien }}</span>
                        </div>
                        @else
                        <input type="number" value="{{ $obat->jumlah_pasien }}" min="0"
                            wire:change="updatePasien({{ $obat->id }}, $event.target.value)"
                            class="font-mono"
                            title="Input manual — belum ada resep aktif"
                            style="width:58px;text-align:right;background:rgba(217,164,65,.08);border:1px solid rgba(217,164,65,.2);border-radius:.3rem;padding:.18rem .35rem;font-size:.8rem;color:var(--gold2);">
                        @endif
                    </td>
                    <td data-col="item" style="text-align:right;">
                        @if($obat->dari_resep)
                        <div style="display:flex;align-items:center;gap:.3rem;justify-content:flex-end;">
                            <span title="Data real dari resep aktif pasien" style="color:var(--emer);font-size:.6rem;line-height:1;">●</span>
                            <span class="font-mono" style="font-size:.82rem;color:var(--emer2);font-weight:600;">{{ number_format($obat->unit_per_bulan, 0) }}</span>
                        </div>
                        @else
                        <input type="number" value="{{ $obat->unit_per_bulan }}" min="0" step="any"
                            wire:change="updateUnit({{ $obat->id }}, $event.target.value)"
                            class="font-mono"
                            title="Input manual — belum ada resep aktif"
                            style="width:68px;text-align:right;background:rgba(217,164,65,.08);border:1px solid rgba(217,164,65,.2);border-radius:.3rem;padding:.18rem .35rem;font-size:.8rem;color:var(--gold2);">
                        @endif
                    </td>
                    <td data-col="beli" style="text-align:right;">
                        <input type="number" value="{{ $obat->harga_beli_per_unit }}" min="0" step="any"
                            wire:change="updateHarga({{ $obat->id }}, $event.target.value)"
                            class="font-mono"
                            style="width:90px;text-align:right;background:rgba(232,100,90,.07);border:1px solid rgba(232,100,90,.2);border-radius:.3rem;padding:.18rem .35rem;font-size:.8rem;color:var(--red2);">
                    </td>
                    <td data-col="sumber" style="text-align:center;">
                        @php $src = $obat->sumber_harga; @endphp
                        <span class="badge badge-{{ $src==='PO'?'po':($src==='REAL'?'real':'est') }}">{{ $src }}</span>
                    </td>
                    <td data-col="klaim" style="text-align:right;">
                        <input type="number" value="{{ $obat->klaim_bpjs_per_unit }}" min="0" step="any"
                            wire:change="updateKlaim({{ $obat->id }}, $event.target.value)"
                            class="font-mono"
                            style="width:90px;text-align:right;background:rgba(111,177,224,.08);border:1px solid rgba(111,177,224,.2);border-radius:.3rem;padding:.18rem .35rem;font-size:.8rem;color:var(--blue);">
                    </td>
                    <td data-col="bayar" class="font-mono" style="text-align:right;font-size:.8rem;color:var(--blue);">{{ number_format($obat->bayar_bpjs,0,',','.') }}</td>
                    <td data-col="pend" class="font-mono" style="text-align:right;font-size:.8rem;">{{ number_format($obat->pendapatan_bulan,0,',','.') }}</td>
                    @php
                        $lpu        = $obat->laba_per_unit;
                        $noVolume   = $obat->unit_per_bulan <= 0;
                        $lpuColor   = $lpu > 0 ? 'var(--emer2)' : ($lpu < 0 ? 'var(--red2)' : 'var(--mut)');
                    @endphp
                    <td data-col="laba" class="lc">
                        <div class="lc-main" title="Untung/rugi per bulan = untung/rugi per unit × Item/Bln" style="color:{{ $obat->laba>0?'var(--emer2)':($obat->laba<0?'var(--red2)':'var(--mut)') }};">
                            <span class="lc-arrow">@if($obat->laba>0)<x-i name="chevron-up" :size="11" />@elseif($obat->laba<0)<x-i name="chevron-down" :size="11" />@endif</span>{{ $obat->laba>=0?'+':'−' }}Rp {{ number_format(abs($obat->laba),0,',','.') }}
                        </div>
                        <div class="lc-margin" title="Untung/rugi per unit (lepas dari jumlah pasien)" style="color:{{ $lpuColor }};">
                            {{ $lpu>=0?'untung +':'rugi −' }}{{ number_format(abs($lpu),0,',','.') }}<span class="u">/unit</span>
                        </div>
                    </td>
                    {{-- Keterangan: penjelasan status laba/rugi/potensi --}}
                    <td data-col="ket" class="ket">
                        @if($obat->klaim_bpjs_per_unit <= 0 && $obat->tipe_obat === 'kronis')
                        <span class="lc-pill warn"><span class="dot"></span>Data belum lengkap</span>
                        <div class="ket-desc">Klaim BPJS/unit belum diisi — laba belum bisa dihitung.</div>
                        @elseif($obat->laba < 0)
                        <span class="lc-pill bad"><span class="dot"></span>Rugi</span>
                        <div class="ket-desc">Bayar BPJS (Rp {{ number_format($obat->bayar_bpjs,0,',','.') }}) &lt; harga beli (Rp {{ number_format($obat->harga_beli_per_unit,0,',','.') }})/unit — cek tarif klaim &amp; faktor jasa farmasi.</div>
                        @elseif($noVolume && $lpu > 0)
                        <span class="lc-pill warn"><span class="dot"></span>Potensi laba</span>
                        <div class="ket-desc">Margin +Rp {{ number_format($lpu,0,',','.') }}/unit, tapi belum ada pasien/volume bulan ini.</div>
                        @elseif($obat->laba > 0)
                        <span class="lc-pill ok"><span class="dot"></span>Laba</span>
                        <div class="ket-desc">{{ $obat->jumlah_pasien }} pasien · {{ number_format($obat->unit_per_bulan,0) }} item/bln{{ $obat->dari_resep ? ' (dari resep aktif)' : ' (input manual)' }}.</div>
                        @else
                        <span class="ket-muted">—</span>
                        @endif
                    </td>
                    <td data-col="aksi" class="ec-pin-r" style="text-align:center;">
                        <div style="display:flex;gap:.3rem;justify-content:center;">
                            <button wire:click="openEdit({{ $obat->id }})" title="Edit"
                                style="background:rgba(217,164,65,.1);border:1px solid rgba(217,164,65,.25);color:var(--gold2);border-radius:.35rem;padding:.25rem .5rem;cursor:pointer;font-size:.75rem;">
                                <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4z"/></svg>
                            </button>
                            <button wire:click="toggleActive({{ $obat->id }})"
                                style="background:{{ $obat->is_active?'rgba(232,100,90,.1)':'rgba(63,207,142,.1)' }};border:1px solid {{ $obat->is_active?'rgba(232,100,90,.25)':'rgba(63,207,142,.25)' }};color:{{ $obat->is_active?'var(--red2)':'var(--emer2)' }};border-radius:.35rem;padding:.25rem .5rem;cursor:pointer;font-size:.7rem;"
                                wire:confirm="{{ $obat->is_active?'Nonaktifkan':'Aktifkan' }} obat ini?">
                                {{ $obat->is_active ? 'Nonaktif' : 'Aktifkan' }}
                            </button>
                            <button wire:click="delete({{ $obat->id }})" title="Hapus"
                                wire:confirm="Hapus permanen obat &quot;{{ $obat->nama_obat }}&quot;? Tindakan ini tidak dapat dibatalkan."
                                style="background:rgba(232,100,90,.08);border:1px solid rgba(232,100,90,.2);color:var(--red2);border-radius:.35rem;padding:.25rem .5rem;cursor:pointer;font-size:.75rem;">
                                <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6"/><path d="M10 11v6M14 11v6"/><path d="M9 6V4a1 1 0 011-1h4a1 1 0 011 1v2"/></svg>
                            </button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="12" style="text-align:center;padding:2rem;color:var(--mut);">Tidak ada data obat.</td>
                </tr>
                @endforelse
            </tbody>
            @php
                $tPasien = $this->obatList->sum('jumlah_pasien');
                $tItem   = $this->obatList->sum('unit_per_bulan');
                $tPend   = $this->obatList->sum('pendapatan_bulan');
                $tLaba   = $this->obatList->sum('laba');
            @endphp
            <tfoot>
                <tr class="ec-total">
                    <td data-col="nama" class="ec-pin">TOTAL · {{ $this->obatList->count() }} obat</td>
                    <td data-col="diagnosis"></td>
                    <td data-col="pasien" class="font-mono" style="text-align:right;">{{ number_format($tPasien,0,',','.') }}</td>
                    <td data-col="item" class="font-mono" style="text-align:right;">{{ number_format($tItem,0,',','.') }}</td>
                    <td data-col="beli"></td>
                    <td data-col="sumber"></td>
                    <td data-col="klaim"></td>
                    <td data-col="bayar"></td>
                    <td data-col="pend" class="font-mono" style="text-align:right;">{{ number_format($tPend,0,',','.') }}</td>
                    <td data-col="laba" class="font-mono" style="text-align:right;font-weight:700;color:{{ $tLaba>=0?'var(--emer2)':'var(--red2)' }};">{{ $tLaba>=0?'untung +':'rugi −' }}Rp {{ number_format(abs($tLaba),0,',','.') }}</td>
                    <td data-col="ket"></td>
                    <td data-col="aksi" class="ec-pin-r"></td>
                </tr>
            </tfoot>
        </table>
    </div>

    {{-- ─────────────── BANDINGKAN: bar mengambang (Alpine, instan tanpa round-trip) ─────────────── --}}
    <div class="ec-cmpbar" x-show="cmp.length > 0" x-cloak>
        <span class="ec-cmpbar-txt"><strong x-text="cmp.length"></strong> obat dipilih <span x-show="cmp.length < 2" style="color:var(--mut2);">· pilih min. 2</span></span>
        <button class="ec-cmpbar-btn" :disabled="cmp.length < 2" @click="$wire.openCompareWith(cmp)">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
            Bandingkan
        </button>
        <button class="ec-cmpbar-x" @click="cmp = []">Bersihkan</button>
    </div>
    </div>{{-- /ec-wrap --}}

    {{-- ─────────────── BANDINGKAN: panel komparasi ─────────────── --}}
    @if($showCompare)
    <div class="audit-overlay">
        <div class="audit-bg" wire:click="closeCompare"></div>
        <div class="audit-panel">
            <div class="audit-head">
                <div>
                    <div class="audit-title font-heading">Bandingkan Obat</div>
                    <div class="audit-sub">Komparasi {{ $this->compareList->count() }} obat berdampingan — sel <span style="color:var(--emer2);">hijau</span> = terbaik, <span style="color:var(--red2);">merah</span> = terlemah per metrik.</div>
                </div>
                <button wire:click="closeCompare" class="audit-close" aria-label="Tutup"><x-i name="x" :size="16" /></button>
            </div>
            @php
                $cmp = $this->compareList;
                // [label, getter, type(text|rp|pct|int), dir(hi|lo|null)]
                $metrics = [
                    ['Tipe',            fn ($o) => ucfirst(str_replace('_',' ', $o->tipe_obat ?? '—')),  'text', null],
                    ['Kategori',        fn ($o) => $o->kategori_diagnosis ?: '—',                          'text', null],
                    ['Bentuk sediaan',  fn ($o) => $o->bentuk_sediaan ?: '—',                              'text', null],
                    ['Komposisi',       fn ($o) => $o->komposisi ?: '—',                                   'text', null],
                    ['Satuan',          fn ($o) => $o->satuan ?: '—',                                      'text', null],
                    ['Harga Beli/unit', fn ($o) => (float) $o->harga_beli_per_unit,                        'rp',   'lo'],
                    ['Klaim BPJS/unit', fn ($o) => (float) $o->klaim_bpjs_per_unit,                        'rp',   'hi'],
                    ['Bayar BPJS/unit', fn ($o) => (float) $o->bayar_bpjs,                                 'rp',   'hi'],
                    ['Margin/unit',     fn ($o) => (float) $o->laba_per_unit,                              'rp',   'hi'],
                    ['Margin %',        fn ($o) => ($pu = ($o->tipe_obat === 'non_kronis' ? (float) $o->harga_jual_per_unit : (float) $o->bayar_bpjs)) > 0 ? round($o->laba_per_unit / $pu * 100, 1) : null, 'pct', 'hi'],
                    ['Stok aktual',     fn ($o) => (int) $o->stok_aktual,                                  'int',  'hi'],
                ];
            @endphp
            <div class="cmp-wrap audit-body cb-scroll">
                <table class="cmp-table">
                    <thead>
                        <tr>
                            <th class="cmp-metric">Metrik</th>
                            @foreach($cmp as $o)
                            <th class="cmp-obat">{{ $o->nama_obat }}<span class="cmp-cat">{{ $o->bentuk_sediaan ?: ($o->kategori_diagnosis ?: '—') }}</span></th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($metrics as [$label, $fn, $type, $dir])
                        @php
                            $vals = $cmp->map($fn);
                            $best = $worst = null;
                            if ($dir && $type !== 'text') {
                                $nums = $vals->filter(fn ($v) => $v !== null)->map(fn ($v) => (float) $v);
                                if ($nums->count() > 1 && $nums->unique()->count() > 1) {
                                    $best  = $dir === 'hi' ? $nums->max() : $nums->min();
                                    $worst = $dir === 'hi' ? $nums->min() : $nums->max();
                                }
                            }
                        @endphp
                        <tr>
                            <td class="cmp-metric">{{ $label }}</td>
                            @foreach($cmp as $i => $o)
                            @php
                                $v = $vals[$i]; $cls = '';
                                if ($best !== null && $v !== null) {
                                    if ((float) $v === $best)  $cls = 'cmp-best';
                                    elseif ((float) $v === $worst) $cls = 'cmp-worst';
                                }
                            @endphp
                            <td class="{{ $type !== 'text' ? 'cmp-num' : '' }} {{ $cls }}">
                                @if($v === null || $v === '—')<span style="color:var(--mut2);">—</span>
                                @elseif($type === 'text'){{ $v }}
                                @elseif($type === 'rp'){{ $v < 0 ? '−' : '' }}Rp {{ number_format(abs($v),0,',','.') }}
                                @elseif($type === 'pct'){{ $v > 0 ? '+' : '' }}{{ number_format($v,1,',','.') }}%
                                @else{{ number_format($v,0,',','.') }}@endif
                                @if($cls === 'cmp-best' && in_array($label, ['Margin/unit','Margin %','Laba/Bln']))<span class="cmp-badge win">terbaik</span>@endif
                            </td>
                            @endforeach
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif

    {{-- ─────────────── PAGINASI ─────────────── --}}
    @php
        $ttl = $this->obatList->count();
        $from = $ttl ? (($pageNum - 1) * $perPage) + 1 : 0;
        $to   = $perPage >= 9999 ? $ttl : min($pageNum * $perPage, $ttl);
    @endphp
    @if($groupMode)
    <div class="ec-pager"><div class="ec-pager-info">Mode grup — menampilkan semua <strong>{{ number_format($ttl,0,',','.') }}</strong> obat</div></div>
    @elseif($ttl > 0)
    <div class="ec-pager">
        <div class="ec-pager-info">{{ number_format($from,0,',','.') }}–{{ number_format($to,0,',','.') }} dari <strong>{{ number_format($ttl,0,',','.') }}</strong> obat</div>
        <div class="ec-pager-ctrl">
            <span style="font-size:.72rem;color:var(--mut2);">Per halaman</span>
            <select wire:model.live="perPage" class="ec-pager-sel">
                <option value="25">25</option>
                <option value="50">50</option>
                <option value="100">100</option>
                <option value="9999">Semua</option>
            </select>
            @if($perPage < 9999)
            <button class="ec-pager-btn" wire:click="prevPage" @disabled($pageNum <= 1)>‹ Sebelumnya</button>
            <span class="ec-pager-pg">{{ $pageNum }} / {{ $this->totalPages }}</span>
            <button class="ec-pager-btn" wire:click="nextPage" @disabled($pageNum >= $this->totalPages)>Berikutnya ›</button>
            @endif
        </div>
    </div>
    @endif

    <div style="margin-top:.75rem;font-size:.75rem;color:var(--mut2);display:flex;gap:1rem;flex-wrap:wrap;">
        <span>Total <strong style="color:var(--gold3);">{{ number_format($ttl,0,',','.') }}</strong> obat</span>
        <span>·</span>
        <span style="color:var(--mut);">
            <span style="color:var(--emer);font-size:.65rem;">●</span> Pasien &amp; Item/Bln otomatis dari resep aktif
            &nbsp;·&nbsp; ✎ Beli/Unit &amp; Klaim BPJS dapat diedit langsung
            &nbsp;·&nbsp; <strong style="color:var(--mut);">Laba/Bln = laba/unit × Item/Bln</strong> — tanpa pasien aktif = 0 walau margin/unit positif
        </span>
    </div>

    {{-- ─────────────── BsCb: dropdown bentuk sediaan (pure JS, position:fixed) ─────────────── --}}
    {{-- wire:ignore agar Livewire tidak morph/duplikat saat dropdown dipindah ke body --}}
    <div wire:ignore>
        <div id="bs-dd" style="display:none;position:fixed;z-index:9999;background:#0e1e17;border:1px solid var(--line2);border-radius:.65rem;overflow:hidden;box-shadow:0 20px 60px rgba(0,0,0,.8),0 0 0 1px rgba(255,255,255,.03);">
            <div style="padding:.45rem .5rem;border-bottom:1px solid rgba(255,255,255,.06);">
                <div style="display:flex;align-items:center;gap:.45rem;background:rgba(255,255,255,.05);border-radius:.4rem;padding:.4rem .65rem;">
                    <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="color:var(--mut);flex-shrink:0;"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                    <input id="bs-q" type="text" autocomplete="off" placeholder="Cari atau ketik bentuk…"
                           oninput="BsCb.filter(this.value)" onkeydown="BsCb.keyDd(event)"
                           style="background:none;border:none;outline:none;color:var(--ink);font-size:.84rem;width:100%;caret-color:var(--gold2);">
                </div>
            </div>
            <div id="bs-list" class="cb-scroll" style="max-height:264px;overflow-y:auto;"></div>
        </div>
    </div>
    <script>
    window.BsCb = (function () {
        const GROUPS = @json(\App\Livewire\KatalogTable::BENTUK_SEDIAAN);
        let activeIdx = 0, flat = [];
        const el  = id => document.getElementById(id);
        const esc = s => String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
        const curVal = () => { const i = el('bs-input'); return i ? i.value : ''; };
        function hl(str, q){ if(!q) return esc(str); const r=q.replace(/[.*+?^${}()|[\]\\]/g,'\\$&'); return esc(str).replace(new RegExp(r,'gi'), m=>'<mark class="hl">'+m+'</mark>'); }
        function pos(){ const b=el('bs-btn'), d=el('bs-dd'); if(!b||!d) return; const r=b.getBoundingClientRect(); const below=window.innerHeight-r.bottom-8; d.style.top=(r.bottom+4)+'px'; d.style.left=r.left+'px'; d.style.width=r.width+'px'; d.style.maxHeight=Math.max(180,Math.min(360,below))+'px'; }
        function render(q){
            const list=el('bs-list'); if(!list) return;
            const query=(q||'').trim().toLowerCase(); flat=[]; let html=''; const cur=curVal();
            for(const g in GROUPS){
                const items=GROUPS[g].filter(o=>!query||o.label.toLowerCase().includes(query));
                if(!items.length) continue;
                html+='<div class="bs-grp">'+esc(g)+'</div>';
                items.forEach(o=>{ const idx=flat.length; flat.push(o.label); const sel=cur===o.label;
                    html+='<div class="bs-opt'+(idx===activeIdx?' act':'')+'" data-v="'+esc(o.label)+'" onmousedown="event.preventDefault();BsCb.pick(this.dataset.v)" onmouseenter="BsCb.hover('+idx+')"><span class="bs-ck">'+(sel?'<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>':'')+'</span><span>'+hl(o.label,q)+'</span></div>';
                });
            }
            const exact = flat.some(l=>l.toLowerCase()===query);
            if(query && !exact){ const idx=flat.length; const v=q.trim(); flat.push(v);
                html+='<div class="bs-grp">Custom</div><div class="bs-opt'+(idx===activeIdx?' act':'')+'" data-v="'+esc(v)+'" onmousedown="event.preventDefault();BsCb.pick(this.dataset.v)" onmouseenter="BsCb.hover('+idx+')"><span class="bs-ck">+</span><span>Pakai “'+esc(v)+'”</span></div>';
            }
            if(!flat.length) html='<div class="bs-empty">Tidak ditemukan</div>';
            list.innerHTML=html;
            const a=list.querySelector('.bs-opt.act'); if(a) a.scrollIntoView({block:'nearest'});
        }
        function setDisplay(v){ const d=el('bs-display'); if(d){ d.textContent=v||'— Pilih bentuk sediaan —'; d.style.color=v?'var(--ink)':'var(--mut2)'; } }
        function pick(v){ const i=el('bs-input'); if(i){ i.value=v; i.dispatchEvent(new Event('input',{bubbles:true})); } setDisplay(v); close(); }
        function open(){ const d=el('bs-dd'); if(!d) return; if(d.parentElement!==document.body) document.body.appendChild(d);
            activeIdx=0; pos(); const q=el('bs-q'); if(q) q.value=''; render('');
            d.style.display='block'; d.classList.remove('bs-anim'); void d.offsetWidth; d.classList.add('bs-anim');
            const b=el('bs-btn'); if(b){ b.style.borderColor='var(--gold)'; b.style.boxShadow='0 0 0 3px rgba(217,164,65,.12)'; }
            const c=el('bs-chevron'); if(c) c.style.transform='rotate(180deg)';
            setTimeout(()=>{ const qi=el('bs-q'); if(qi) qi.focus(); },40);
        }
        function close(){ const d=el('bs-dd'); if(d) d.style.display='none'; const b=el('bs-btn'); if(b){ b.style.borderColor=''; b.style.boxShadow=''; } const c=el('bs-chevron'); if(c) c.style.transform=''; }
        function isOpen(){ const d=el('bs-dd'); return d && d.style.display!=='none'; }
        function move(dir){ if(!flat.length) return; activeIdx=Math.max(0,Math.min(activeIdx+dir,flat.length-1)); const opts=el('bs-list').querySelectorAll('.bs-opt'); opts.forEach((o,i)=>o.classList.toggle('act',i===activeIdx)); if(opts[activeIdx]) opts[activeIdx].scrollIntoView({block:'nearest'}); }
        document.addEventListener('click', e=>{ if(!e.target.closest('#bs-wrap') && !e.target.closest('#bs-dd')) close(); });
        window.addEventListener('scroll', ()=>{ if(isOpen()) pos(); }, true);
        window.addEventListener('resize', ()=>{ if(isOpen()) pos(); });
        document.addEventListener('livewire:update', ()=>{ setDisplay(curVal()); const d=el('bs-dd'); if(d && d.parentElement!==document.body && d.style.display==='none') {/* keep in form when closed */} });
        return {
            toggle(){ isOpen()?close():open(); },
            keyBtn(e){ if(e.key==='ArrowDown'||e.key==='Enter'){ open(); e.preventDefault(); } else if(e.key==='Escape') close(); },
            keyDd(e){ if(e.key==='ArrowDown'){ move(1); e.preventDefault(); } else if(e.key==='ArrowUp'){ move(-1); e.preventDefault(); } else if(e.key==='Enter'){ if(flat[activeIdx]!==undefined) pick(flat[activeIdx]); e.preventDefault(); } else if(e.key==='Escape'){ close(); const b=el('bs-btn'); if(b) b.focus(); } },
            filter(q){ activeIdx=0; render(q); },
            hover(i){ activeIdx=i; const opts=el('bs-list').querySelectorAll('.bs-opt'); opts.forEach((o,j)=>o.classList.toggle('act',j===i)); },
            pick, clear(){ pick(''); }, sync(){ setDisplay(curVal()); }
        };
    })();
    </script>

    {{-- ─────────────── DiagCb: dropdown kategori diagnosis (pure JS, position:fixed) ─────────────── --}}
    <div wire:ignore>
        <div id="diag-dd" style="display:none;position:fixed;z-index:9999;background:#0e1e17;border:1px solid var(--line2);border-radius:.65rem;overflow:hidden;box-shadow:0 20px 60px rgba(0,0,0,.8),0 0 0 1px rgba(255,255,255,.03);">
            <div style="padding:.45rem .5rem;border-bottom:1px solid rgba(255,255,255,.06);">
                <div style="display:flex;align-items:center;gap:.45rem;background:rgba(255,255,255,.05);border-radius:.4rem;padding:.4rem .65rem;">
                    <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="color:var(--mut);flex-shrink:0;"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                    <input id="diag-q" type="text" autocomplete="off" placeholder="Cari atau ketik kategori…"
                           oninput="DiagCb.filter(this.value)" onkeydown="DiagCb.keyDd(event)"
                           style="background:none;border:none;outline:none;color:var(--ink);font-size:.84rem;width:100%;caret-color:var(--gold2);">
                </div>
            </div>
            <div id="diag-list" class="cb-scroll" style="max-height:264px;overflow-y:auto;"></div>
        </div>
    </div>
    <script>
    window.DiagCb = (function () {
        const ITEMS = @json($this->kategoriList);
        let activeIdx = 0, flat = [];
        const el  = id => document.getElementById(id);
        const esc = s => String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
        const curVal = () => { const i = el('diag-input'); return i ? i.value : ''; };
        function hl(str, q){ if(!q) return esc(str); const r=q.replace(/[.*+?^${}()|[\]\\]/g,'\\$&'); return esc(str).replace(new RegExp(r,'gi'), m=>'<mark class="hl">'+m+'</mark>'); }
        function pos(){ const b=el('diag-btn'), d=el('diag-dd'); if(!b||!d) return; const r=b.getBoundingClientRect(); const below=window.innerHeight-r.bottom-8; d.style.top=(r.bottom+4)+'px'; d.style.left=r.left+'px'; d.style.width=r.width+'px'; d.style.maxHeight=Math.max(180,Math.min(360,below))+'px'; }
        function render(q){
            const list=el('diag-list'); if(!list) return;
            const query=(q||'').trim().toLowerCase(); flat=[]; let html=''; const cur=curVal();
            const items=(ITEMS||[]).filter(o=>!query||String(o).toLowerCase().includes(query));
            if(items.length){
                html+='<div class="bs-grp">Kategori</div>';
                items.forEach(o=>{ const idx=flat.length; flat.push(o); const sel=cur===o;
                    html+='<div class="bs-opt'+(idx===activeIdx?' act':'')+'" data-v="'+esc(o)+'" onmousedown="event.preventDefault();DiagCb.pick(this.dataset.v)" onmouseenter="DiagCb.hover('+idx+')"><span class="bs-ck">'+(sel?'<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>':'')+'</span><span>'+hl(o,q)+'</span></div>';
                });
            }
            const exact = flat.some(l=>String(l).toLowerCase()===query);
            if(query && !exact){ const idx=flat.length; const v=q.trim(); flat.push(v);
                html+='<div class="bs-grp">Custom</div><div class="bs-opt'+(idx===activeIdx?' act':'')+'" data-v="'+esc(v)+'" onmousedown="event.preventDefault();DiagCb.pick(this.dataset.v)" onmouseenter="DiagCb.hover('+idx+')"><span class="bs-ck">+</span><span>Pakai “'+esc(v)+'”</span></div>';
            }
            if(!flat.length) html='<div class="bs-empty">Tidak ditemukan</div>';
            list.innerHTML=html;
            const a=list.querySelector('.bs-opt.act'); if(a) a.scrollIntoView({block:'nearest'});
        }
        function setDisplay(v){ const d=el('diag-display'); if(d){ d.textContent=v||'— Pilih kategori (opsional) —'; d.style.color=v?'var(--ink)':'var(--mut2)'; } }
        function pick(v){ const i=el('diag-input'); if(i){ i.value=v; i.dispatchEvent(new Event('input',{bubbles:true})); } setDisplay(v); close(); }
        function open(){ const d=el('diag-dd'); if(!d) return; if(d.parentElement!==document.body) document.body.appendChild(d);
            activeIdx=0; pos(); const q=el('diag-q'); if(q) q.value=''; render('');
            d.style.display='block'; d.classList.remove('bs-anim'); void d.offsetWidth; d.classList.add('bs-anim');
            const b=el('diag-btn'); if(b){ b.style.borderColor='var(--gold)'; b.style.boxShadow='0 0 0 3px rgba(217,164,65,.12)'; }
            const c=el('diag-chevron'); if(c) c.style.transform='rotate(180deg)';
            setTimeout(()=>{ const qi=el('diag-q'); if(qi) qi.focus(); },40);
        }
        function close(){ const d=el('diag-dd'); if(d) d.style.display='none'; const b=el('diag-btn'); if(b){ b.style.borderColor=''; b.style.boxShadow=''; } const c=el('diag-chevron'); if(c) c.style.transform=''; }
        function isOpen(){ const d=el('diag-dd'); return d && d.style.display!=='none'; }
        function move(dir){ if(!flat.length) return; activeIdx=Math.max(0,Math.min(activeIdx+dir,flat.length-1)); const opts=el('diag-list').querySelectorAll('.bs-opt'); opts.forEach((o,i)=>o.classList.toggle('act',i===activeIdx)); if(opts[activeIdx]) opts[activeIdx].scrollIntoView({block:'nearest'}); }
        document.addEventListener('click', e=>{ if(!e.target.closest('#diag-wrap') && !e.target.closest('#diag-dd')) close(); });
        window.addEventListener('scroll', ()=>{ if(isOpen()) pos(); }, true);
        window.addEventListener('resize', ()=>{ if(isOpen()) pos(); });
        return {
            toggle(){ isOpen()?close():open(); },
            keyBtn(e){ if(e.key==='ArrowDown'||e.key==='Enter'){ open(); e.preventDefault(); } else if(e.key==='Escape') close(); },
            keyDd(e){ if(e.key==='ArrowDown'){ move(1); e.preventDefault(); } else if(e.key==='ArrowUp'){ move(-1); e.preventDefault(); } else if(e.key==='Enter'){ if(flat[activeIdx]!==undefined) pick(flat[activeIdx]); e.preventDefault(); } else if(e.key==='Escape'){ close(); const b=el('diag-btn'); if(b) b.focus(); } },
            filter(q){ activeIdx=0; render(q); },
            hover(i){ activeIdx=i; const opts=el('diag-list').querySelectorAll('.bs-opt'); opts.forEach((o,j)=>o.classList.toggle('act',j===i)); },
            pick, clear(){ pick(''); }, sync(){ setDisplay(curVal()); }
        };
    })();
    </script>
</div>
