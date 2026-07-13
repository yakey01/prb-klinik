<div>
    @php
        $rep = $this->report;
        $c = $rep->counts();
        $sevCfg = [
            'kritis' => ['c'=>'var(--red2)',  'bg'=>'rgba(232,100,90,.12)',  'bd'=>'rgba(232,100,90,.4)',  'ic'=>'🔴', 'l'=>'Kritis'],
            'tinggi' => ['c'=>'#e6863c',       'bg'=>'rgba(230,134,60,.12)',  'bd'=>'rgba(230,134,60,.4)',  'ic'=>'🟠', 'l'=>'Tinggi'],
            'sedang' => ['c'=>'var(--gold2)',  'bg'=>'rgba(217,164,65,.12)',  'bd'=>'rgba(217,164,65,.4)',  'ic'=>'🟡', 'l'=>'Sedang'],
            'rendah' => ['c'=>'var(--blue)',   'bg'=>'rgba(111,177,224,.12)', 'bd'=>'rgba(111,177,224,.35)','ic'=>'🔵', 'l'=>'Rendah'],
        ];
    @endphp

    {{-- ══ HERO ═══════════════════════════════════════════════════════ --}}
    <div class="glass-card" style="padding:1.25rem 1.5rem;margin-bottom:1.25rem;display:flex;align-items:center;gap:1.1rem;flex-wrap:wrap;border-color:rgba(63,207,142,.28);background:linear-gradient(120deg,rgba(63,207,142,.08),transparent 60%);">
        <div style="width:46px;height:46px;border-radius:.8rem;display:flex;align-items:center;justify-content:center;background:linear-gradient(135deg,#3fcf8e,#2b9d68);box-shadow:0 6px 18px rgba(63,207,142,.35);flex-shrink:0;">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#04120c" stroke-width="2.1" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><path d="M9 12l2 2 4-4"/></svg>
        </div>
        <div style="flex:1;min-width:220px;">
            <div class="font-heading" style="font-size:1.15rem;color:var(--ink);display:flex;align-items:center;gap:.5rem;">Pharmacy Guardian AI</div>
            <div style="font-size:.78rem;color:var(--mut);margin-top:.15rem;">Rekonsiliasi <strong style="color:var(--emer2);">Riwayat PO ↔ Tagihan</strong> + deteksi anomali · {{ $rep->poDiperiksa }} faktur · {{ $rep->itemDiperiksa }} item diperiksa</div>
        </div>
        <div style="text-align:right;">
            <div style="font-size:.62rem;color:var(--mut);text-transform:uppercase;letter-spacing:.07em;">Skor Risiko</div>
            <div class="font-mono" style="font-size:1.6rem;font-weight:800;color:{{ $c['total']>0 ? ($c['kritis']>0?'var(--red2)':'var(--gold2)') : 'var(--emer2)' }};">{{ $rep->totalScore() }}</div>
        </div>
    </div>

    {{-- ══ KPI keparahan ══════════════════════════════════════════════ --}}
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(150px,1fr));gap:.75rem;margin-bottom:1.1rem;">
        @foreach($sevCfg as $sev=>$cfg)
        <button wire:click="$set('filterSeverity', '{{ $filterSeverity===$sev ? 'semua' : $sev }}')"
            class="glass-card" style="text-align:left;padding:.85rem 1rem;cursor:pointer;border-color:{{ $filterSeverity===$sev ? $cfg['bd'] : 'var(--line)' }};{{ $filterSeverity===$sev ? 'box-shadow:0 0 0 1px '.$cfg['bd'].';' : '' }}background:{{ $filterSeverity===$sev ? $cfg['bg'] : '' }};">
            <div style="font-size:.66rem;color:var(--mut);text-transform:uppercase;letter-spacing:.06em;">{{ $cfg['ic'] }} {{ $cfg['l'] }}</div>
            <div class="font-mono" style="font-size:1.5rem;font-weight:800;color:{{ $cfg['c'] }};line-height:1.3;">{{ $c[$sev] }}</div>
        </button>
        @endforeach
    </div>

    {{-- ══ FILTER BAR ═════════════════════════════════════════════════ --}}
    <div class="glass-card" style="padding:.8rem 1rem;margin-bottom:1.1rem;display:flex;gap:.7rem;flex-wrap:wrap;align-items:center;">
        <div style="position:relative;flex:1;min-width:200px;">
            <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="color:var(--mut);position:absolute;left:.65rem;top:50%;transform:translateY(-50%);"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            <input wire:model.live.debounce.300ms="search" type="text" placeholder="Cari obat, PBF, PO#, temuan…" class="form-input" style="padding-left:2rem;">
        </div>
        {{-- Kategori --}}
        <select wire:model.live="filterKategori" class="form-input" style="max-width:180px;">
            <option value="semua">Semua kategori</option>
            @foreach($this->kategoriList as $kat)
            <option value="{{ $kat }}">{{ $kat }}</option>
            @endforeach
        </select>
        {{-- Toggle konfirmasi --}}
        <button wire:click="$toggle('showConfirmed')"
            style="font-size:.73rem;font-weight:700;padding:.45rem .8rem;border-radius:.5rem;cursor:pointer;border:1px solid {{ $showConfirmed ? 'var(--emer)' : 'var(--line2)' }};background:{{ $showConfirmed ? 'rgba(63,207,142,.12)' : 'transparent' }};color:{{ $showConfirmed ? 'var(--emer2)' : 'var(--mut)' }};">
            {{ $showConfirmed ? '✓ Termasuk yg dikonfirmasi' : 'Sembunyikan yg dikonfirmasi' }}
            @if($rep->dikonfirmasi>0)<span style="opacity:.8;">({{ $rep->dikonfirmasi }})</span>@endif
        </button>
    </div>

    {{-- ══ DAFTAR TEMUAN per FAKTUR ═══════════════════════════════════ --}}
    @php $groups = $this->grouped; @endphp
    @forelse($groups as $poId => $findings)
    @php $risk = $this->report->riskByPo()[$poId] ?? null; $rc = $sevCfg[$risk['level'] ?? 'rendah']; @endphp
    <div class="glass-card" style="margin-bottom:.9rem;overflow:hidden;border-color:{{ $rc['bd'] }};">
        {{-- header faktur --}}
        <div style="padding:.7rem 1.1rem;display:flex;align-items:center;gap:.7rem;flex-wrap:wrap;background:{{ $rc['bg'] }};border-bottom:1px solid {{ $rc['bd'] }};">
            <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="color:{{ $rc['c'] }};"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
            <span class="font-mono" style="font-weight:800;color:{{ $rc['c'] }};">PO #{{ $poId }}</span>
            <span style="font-size:.7rem;font-weight:700;padding:.12rem .5rem;border-radius:.3rem;background:{{ $rc['bg'] }};border:1px solid {{ $rc['bd'] }};color:{{ $rc['c'] }};">{{ $rc['ic'] }} {{ $rc['l'] }}</span>
            <span style="font-size:.73rem;color:var(--mut);">{{ $findings->count() }} temuan</span>
            <a href="{{ route('riwayat.index') }}#po-{{ $poId }}" wire:navigate style="margin-left:auto;font-size:.7rem;color:var(--gold2);text-decoration:none;display:inline-flex;align-items:center;gap:.3rem;">Lihat di Riwayat PO →</a>
            <a href="{{ route('tagihan.index') }}" wire:navigate style="font-size:.7rem;color:var(--emer2);text-decoration:none;">Tagihan →</a>
        </div>

        {{-- temuan --}}
        @foreach($findings as $f)
        @php $fc = $sevCfg[$f->severity]; @endphp
        <div style="padding:.85rem 1.1rem;border-bottom:1px solid rgba(255,255,255,.05);{{ $f->ack ? 'opacity:.6;' : '' }}">
            <div style="display:flex;align-items:flex-start;gap:.7rem;flex-wrap:wrap;">
                <div style="flex:1;min-width:240px;">
                    <div style="display:flex;align-items:center;gap:.5rem;flex-wrap:wrap;margin-bottom:.25rem;">
                        <span style="font-size:.6rem;font-weight:800;padding:.1rem .45rem;border-radius:.3rem;background:{{ $fc['bg'] }};border:1px solid {{ $fc['bd'] }};color:{{ $fc['c'] }};">{{ $fc['ic'] }} {{ strtoupper($fc['l']) }}</span>
                        <span style="font-size:.6rem;font-weight:700;padding:.1rem .45rem;border-radius:.3rem;background:rgba(255,255,255,.05);color:var(--mut);">{{ $f->category }}</span>
                        @if($f->berubahSejakAck)<span title="Kondisi berubah sejak dikonfirmasi" style="font-size:.58rem;font-weight:800;color:var(--gold2);background:rgba(217,164,65,.14);border:1px solid var(--gold);border-radius:.3rem;padding:.05rem .4rem;">⟳ berubah sejak dikonfirmasi</span>@endif
                        @if($f->ack)<span style="font-size:.58rem;font-weight:800;color:var(--emer2);background:rgba(63,207,142,.12);border-radius:.3rem;padding:.05rem .4rem;">✓ {{ $f->ack['status']==='resolved' ? 'diperbaiki' : 'aman' }}{{ !empty($f->ack['oleh']) ? ' · '.$f->ack['oleh'] : '' }}</span>@endif
                    </div>
                    <div style="font-size:.85rem;font-weight:700;color:var(--ink);">{{ $f->title }}</div>
                    <div style="font-size:.75rem;color:var(--mut);margin-top:.2rem;line-height:1.5;">{{ $f->detail }}</div>
                </div>
                {{-- confidence gauge --}}
                <div style="text-align:right;min-width:78px;">
                    <div style="font-size:.58rem;color:var(--mut);text-transform:uppercase;letter-spacing:.05em;">Keyakinan</div>
                    <div class="font-mono" style="font-size:1rem;font-weight:800;color:{{ $fc['c'] }};">{{ $f->confidence }}%</div>
                    <div style="height:4px;border-radius:3px;background:rgba(255,255,255,.08);overflow:hidden;margin-top:.15rem;"><div style="height:100%;width:{{ $f->confidence }}%;background:{{ $fc['c'] }};"></div></div>
                </div>
            </div>

            {{-- evidence --}}
            @if($f->evidence)
            <div style="display:flex;flex-wrap:wrap;gap:.4rem;margin-top:.5rem;">
                @foreach($f->evidence as $lbl=>$val)
                <span style="font-size:.66rem;background:rgba(0,0,0,.2);border:1px solid rgba(255,255,255,.06);border-radius:.35rem;padding:.15rem .5rem;color:var(--mut);"><span style="opacity:.75;">{{ $lbl }}:</span> <strong style="color:var(--ink);">{{ $val }}</strong></span>
                @endforeach
            </div>
            @endif

            {{-- rekomendasi + aksi --}}
            <div style="display:flex;align-items:center;gap:.6rem;flex-wrap:wrap;margin-top:.55rem;">
                @if($f->recommendation)
                <div style="flex:1;min-width:220px;font-size:.7rem;color:var(--emer2);display:flex;align-items:flex-start;gap:.35rem;">
                    <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="flex-shrink:0;margin-top:.15rem;"><path d="M9 18h6M10 22h4M12 2a7 7 0 00-4 12.7c.6.5 1 1.2 1 2h6c0-.8.4-1.5 1-2A7 7 0 0012 2z"/></svg>
                    <span>{{ $f->recommendation }}</span>
                </div>
                @endif
                <div style="display:flex;gap:.4rem;flex-shrink:0;">
                    @if($f->ack && !$f->berubahSejakAck)
                    <button wire:click="bukaKembali('{{ $f->code }}','{{ $f->subjectType }}',{{ $f->subjectId }})" style="font-size:.66rem;font-weight:700;padding:.28rem .6rem;border-radius:.4rem;cursor:pointer;background:transparent;border:1px solid var(--line2);color:var(--mut);">↺ Buka lagi</button>
                    @else
                    <button wire:click="tandaiDiperbaikI('{{ $f->code }}','{{ $f->subjectType }}',{{ $f->subjectId }},{{ $f->poId ?? 'null' }},'{{ $f->fingerprint() }}')" style="font-size:.66rem;font-weight:700;padding:.28rem .6rem;border-radius:.4rem;cursor:pointer;background:rgba(217,164,65,.12);border:1px solid rgba(217,164,65,.35);color:var(--gold2);">🔧 Sudah diperbaiki</button>
                    <button wire:click="tandaiAman('{{ $f->code }}','{{ $f->subjectType }}',{{ $f->subjectId }},{{ $f->poId ?? 'null' }},'{{ $f->fingerprint() }}')" style="font-size:.66rem;font-weight:700;padding:.28rem .6rem;border-radius:.4rem;cursor:pointer;background:rgba(63,207,142,.14);border:1px solid rgba(63,207,142,.35);color:var(--emer2);">✓ Tandai aman</button>
                    @endif
                </div>
            </div>
        </div>
        @endforeach
    </div>

    @empty
    <div class="glass-card" style="text-align:center;padding:3rem 1.5rem;">
        <div style="width:56px;height:56px;border-radius:50%;margin:0 auto 1rem;display:flex;align-items:center;justify-content:center;background:rgba(63,207,142,.12);border:1px solid rgba(63,207,142,.3);">
            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="var(--emer2)" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><path d="M9 12l2 2 4-4"/></svg>
        </div>
        <div class="font-heading" style="font-size:1rem;color:var(--emer2);margin-bottom:.3rem;">
            @if($c['total']===0 && !$showConfirmed && $rep->dikonfirmasi>0)Semua temuan sudah dikonfirmasi ✓
            @elseif($c['total']===0)Tidak ada anomali terdeteksi
            @else Tidak ada temuan pada filter ini
            @endif
        </div>
        <div style="font-size:.78rem;color:var(--mut);">Riwayat PO & Tagihan konsisten — tidak ada yang tertukar.</div>
        @if($rep->dikonfirmasi>0 && !$showConfirmed)
        <button wire:click="$toggle('showConfirmed')" style="margin-top:.9rem;font-size:.73rem;padding:.4rem .9rem;border-radius:.5rem;cursor:pointer;background:transparent;border:1px solid var(--line2);color:var(--gold2);">Tampilkan {{ $rep->dikonfirmasi }} yang sudah dikonfirmasi</button>
        @endif
    </div>
    @endforelse
</div>
