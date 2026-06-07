<div>
    {{-- ─────────── RENCANA PENGAMBILAN OBAT WIDGET ─────────── --}}
    <div style="margin-bottom:2rem;">

        {{-- Header --}}
        <div style="display:flex;align-items:flex-end;justify-content:space-between;margin-bottom:1.25rem;flex-wrap:wrap;gap:.75rem;">
            <div style="display:flex;align-items:center;gap:.85rem;">
                {{-- Icon --}}
                <div style="width:2.75rem;height:2.75rem;border-radius:.75rem;background:linear-gradient(135deg,rgba(63,207,142,.18),rgba(63,207,142,.06));border:1px solid rgba(63,207,142,.22);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="var(--emer2)" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="4" width="18" height="18" rx="2.5"/>
                        <line x1="16" y1="2" x2="16" y2="6"/>
                        <line x1="8" y1="2" x2="8" y2="6"/>
                        <line x1="3" y1="10" x2="21" y2="10"/>
                        <circle cx="12" cy="16" r="2"/>
                        <path d="M12 14v-2m0 6v.01"/>
                    </svg>
                </div>
                <div>
                    <div style="display:flex;align-items:center;gap:.55rem;">
                        <h2 class="font-heading" style="font-size:1.05rem;color:var(--ink);margin:0;">Rencana Pengambilan Obat</h2>
                        <span style="background:rgba(63,207,142,.12);border:1px solid rgba(63,207,142,.25);color:var(--emer2);font-size:.65rem;font-weight:700;padding:.15rem .55rem;border-radius:2rem;letter-spacing:.04em;text-transform:uppercase;">
                            {{ now()->translatedFormat('F Y') }}
                        </span>
                    </div>
                    <p style="font-size:.73rem;color:var(--mut);margin:.18rem 0 0;line-height:1.4;">
                        Jadwal pengambilan obat pasien diurutkan dari yang paling dekat
                    </p>
                </div>
            </div>

            {{-- Stats summary + link --}}
            <div style="display:flex;align-items:center;gap:1rem;flex-wrap:wrap;">
                @if($this->stats['overdue'] > 0)
                <div style="display:flex;align-items:center;gap:.35rem;">
                    <span style="width:.55rem;height:.55rem;border-radius:50%;background:#e8645a;display:inline-block;box-shadow:0 0 6px rgba(232,100,90,.5);"></span>
                    <span style="font-size:.73rem;color:#e8645a;font-weight:600;">{{ $this->stats['overdue'] }} terlambat</span>
                </div>
                @endif
                @if($this->stats['today'] > 0)
                <div style="display:flex;align-items:center;gap:.35rem;">
                    <span style="width:.55rem;height:.55rem;border-radius:50%;background:var(--gold);display:inline-block;box-shadow:0 0 6px rgba(217,164,65,.5);"></span>
                    <span style="font-size:.73rem;color:var(--gold2);font-weight:600;">{{ $this->stats['today'] }} hari ini</span>
                </div>
                @endif
                <div style="font-size:.73rem;color:var(--mut);">
                    <strong style="color:var(--ink);">{{ $this->stats['total'] }}</strong> total jadwal
                </div>
                <a href="{{ route('pasien.index') }}" style="display:flex;align-items:center;gap:.3rem;font-size:.73rem;color:var(--emer2);text-decoration:none;font-weight:600;padding:.35rem .8rem;border:1px solid rgba(63,207,224,.25);border-radius:.5rem;background:rgba(63,207,142,.06);transition:background .15s;" onmouseover="this.style.background='rgba(63,207,142,.12)'" onmouseout="this.style.background='rgba(63,207,142,.06)'">
                    Semua jadwal
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14m-7-7 7 7-7 7"/></svg>
                </a>
            </div>
        </div>

        @if($this->jadwal->total() === 0)
        {{-- Empty State --}}
        <div class="glass-card" style="padding:3rem 2rem;text-align:center;">
            <div style="width:3.5rem;height:3.5rem;border-radius:50%;background:rgba(63,207,142,.08);border:1px solid rgba(63,207,142,.15);margin:0 auto 1rem;display:flex;align-items:center;justify-content:center;">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--emer2)" stroke-width="1.5"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div class="font-heading" style="font-size:.9rem;color:var(--ink);margin-bottom:.35rem;">Semua Bersih</div>
            <p style="font-size:.78rem;color:var(--mut);margin:0;">Tidak ada jadwal pengambilan obat yang tertunda saat ini.</p>
        </div>
        @else
        {{-- Cards Grid --}}
        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(320px,1fr));gap:1rem;">
            @foreach($this->jadwal as $idx => $item)
            @php
                $colors = match($item['urgency']) {
                    'overdue' => ['bar'=>'#e8645a','bg'=>'rgba(232,100,90,.06)','border'=>'rgba(232,100,90,.25)','text'=>'#e8645a','avatar'=>'rgba(232,100,90,.15)','avtext'=>'#e8645a'],
                    'today'   => ['bar'=>'#d9a441','bg'=>'rgba(217,164,65,.07)','border'=>'rgba(217,164,65,.3)','text'=>'#d9a441','avatar'=>'rgba(217,164,65,.18)','avtext'=>'#d9a441'],
                    'soon'    => ['bar'=>'#fb923c','bg'=>'rgba(251,146,60,.05)','border'=>'rgba(251,146,60,.22)','text'=>'#fb923c','avatar'=>'rgba(251,146,60,.15)','avtext'=>'#fb923c'],
                    'week'    => ['bar'=>'#6fb1e0','bg'=>'rgba(111,177,224,.05)','border'=>'rgba(111,177,224,.2)','text'=>'#6fb1e0','avatar'=>'rgba(111,177,224,.15)','avtext'=>'#6fb1e0'],
                    default   => ['bar'=>'rgba(63,207,142,.5)','bg'=>'rgba(63,207,142,.03)','border'=>'rgba(63,207,142,.12)','text'=>'var(--emer2)','avatar'=>'rgba(63,207,142,.1)','avtext'=>'var(--emer2)'],
                };
            @endphp
            <div style="background:var(--panel);border:1px solid {{ $colors['border'] }};border-radius:1rem;overflow:hidden;position:relative;transition:box-shadow .2s,transform .2s;" onmouseover="this.style.transform='translateY(-2px)';this.style.boxShadow='0 8px 32px rgba(0,0,0,.35)'" onmouseout="this.style.transform='translateY(0)';this.style.boxShadow='none'">

                {{-- Urgency bar --}}
                <div style="height:3px;background:linear-gradient(90deg,{{ $colors['bar'] }},transparent);"></div>

                <div style="padding:1.1rem 1.2rem;">
                    {{-- Patient row --}}
                    <div style="display:flex;align-items:center;gap:.85rem;margin-bottom:.9rem;">
                        {{-- Avatar --}}
                        <div style="width:2.6rem;height:2.6rem;border-radius:.7rem;background:{{ $colors['avatar'] }};border:1px solid {{ $colors['border'] }};display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                            <span style="font-size:1rem;font-weight:800;color:{{ $colors['avtext'] }};font-family:'Fraunces',serif;">{{ $item['inisial'] }}</span>
                        </div>

                        {{-- Patient info --}}
                        <div style="flex:1;min-width:0;">
                            <div style="font-size:.88rem;font-weight:700;color:var(--ink);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                                {{ $item['pasien_nama'] }}
                            </div>
                            <div style="font-size:.7rem;color:var(--mut);margin-top:.1rem;font-family:'JetBrains Mono',monospace;">
                                BPJS {{ $item['no_bpjs'] }}
                            </div>
                        </div>

                        {{-- Countdown badge --}}
                        <div style="text-align:right;flex-shrink:0;">
                            <div style="font-size:.68rem;font-weight:700;color:{{ $colors['text'] }};background:{{ $colors['bg'] }};border:1px solid {{ $colors['border'] }};border-radius:.45rem;padding:.22rem .6rem;white-space:nowrap;">
                                @if($item['urgency'] === 'overdue')
                                ⚠ {{ $item['label'] }}
                                @elseif($item['urgency'] === 'today')
                                ● {{ $item['label'] }}
                                @else
                                {{ $item['label'] }}
                                @endif
                            </div>
                            <div style="font-size:.68rem;color:var(--mut);margin-top:.2rem;text-align:right;">
                                {{ $item['tanggal'] }}
                            </div>
                        </div>
                    </div>

                    {{-- Divider --}}
                    <div style="height:1px;background:var(--line);margin-bottom:.8rem;"></div>

                    {{-- Drugs section --}}
                    <div>
                        <div style="font-size:.65rem;text-transform:uppercase;letter-spacing:.07em;color:var(--mut);margin-bottom:.5rem;display:flex;align-items:center;gap:.35rem;">
                            <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 3H5a2 2 0 0 0-2 2v4m6-6h10a2 2 0 0 1 2 2v4M9 3v18m0 0h10a2 2 0 0 0 2-2V9M9 21H5a2 2 0 0 1-2-2V9m0 0h18"/></svg>
                            Resep Obat
                        </div>
                        @if($item['drugs']->isEmpty())
                        <div style="font-size:.75rem;color:var(--mut);font-style:italic;padding:.3rem 0;">
                            Resep belum diatur
                        </div>
                        @else
                        <div style="display:flex;flex-direction:column;gap:.4rem;">
                            @foreach($item['drugs'] as $drug)
                            <div style="display:flex;align-items:center;justify-content:space-between;background:rgba(255,255,255,.025);border-radius:.5rem;padding:.35rem .65rem;">
                                <div style="display:flex;align-items:center;gap:.5rem;min-width:0;">
                                    <div style="width:.35rem;height:.35rem;border-radius:50%;background:{{ $colors['bar'] }};flex-shrink:0;"></div>
                                    <span style="font-size:.78rem;color:var(--ink2);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                                        {{ $drug['nama'] }}
                                    </span>
                                </div>
                                <span style="font-size:.73rem;color:{{ $colors['text'] }};font-weight:700;font-family:'JetBrains Mono',monospace;flex-shrink:0;margin-left:.5rem;">
                                    {{ $drug['jumlah'] }} <span style="font-weight:400;color:var(--mut);font-size:.68rem;">{{ $drug['satuan'] }}</span>
                                </span>
                            </div>
                            @endforeach
                        </div>
                        @endif
                    </div>
                </div>

                {{-- Card footer --}}
                <div style="padding:.6rem 1.2rem;background:rgba(0,0,0,.15);border-top:1px solid var(--line);display:flex;align-items:center;justify-content:space-between;">
                    <div style="font-size:.67rem;color:var(--mut);">
                        ID #{{ $item['id'] }}
                    </div>
                    @if($item['urgency'] === 'overdue')
                    <div style="display:flex;align-items:center;gap:.3rem;font-size:.67rem;color:#e8645a;font-weight:600;">
                        <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                        Perlu dihubungi
                    </div>
                    @elseif($item['urgency'] === 'today')
                    <div style="display:flex;align-items:center;gap:.3rem;font-size:.67rem;color:#d9a441;font-weight:600;">
                        <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                        Siapkan hari ini
                    </div>
                    @elseif(in_array($item['urgency'], ['soon', 'week']))
                    <div style="font-size:.67rem;color:var(--mut);">
                        Segera dijadwalkan
                    </div>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
        @endif

        {{-- Bottom bar: summary + pagination --}}
        @if($this->jadwal->total() > 0)
        <div style="margin-top:1rem;padding:.75rem 1.1rem;background:rgba(63,207,142,.04);border:1px solid rgba(63,207,142,.1);border-radius:.75rem;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:.75rem;">

            {{-- Stats --}}
            <div style="display:flex;align-items:center;gap:1.25rem;flex-wrap:wrap;">
                <div style="font-size:.72rem;color:var(--mut);">
                    <span style="color:var(--ink);font-weight:700;">{{ $this->stats['total'] }}</span> jadwal aktif
                </div>
                @if($this->stats['overdue'] > 0)
                <div style="font-size:.72rem;color:#e8645a;">
                    <span style="font-weight:700;">{{ $this->stats['overdue'] }}</span> terlambat
                </div>
                @endif
                @if($this->stats['today'] > 0)
                <div style="font-size:.72rem;color:#d9a441;">
                    <span style="font-weight:700;">{{ $this->stats['today'] }}</span> hari ini
                </div>
                @endif
                @if($this->stats['soon'] > 0)
                <div style="font-size:.72rem;color:#6fb1e0;">
                    <span style="font-weight:700;">{{ $this->stats['soon'] }}</span> dalam 7 hari
                </div>
                @endif
            </div>

            {{-- Pagination controls --}}
            @if($this->jadwal->lastPage() > 1)
            <div style="display:flex;align-items:center;gap:.5rem;">
                <span style="font-size:.68rem;color:var(--mut);">
                    {{ ($this->jadwal->currentPage() - 1) * $this->jadwal->perPage() + 1 }}–{{ min($this->jadwal->currentPage() * $this->jadwal->perPage(), $this->jadwal->total()) }}
                    dari {{ $this->jadwal->total() }}
                </span>
                <button wire:click="previousPage" {{ $this->jadwal->onFirstPage() ? 'disabled' : '' }}
                    style="display:flex;align-items:center;justify-content:center;width:28px;height:28px;border-radius:.4rem;background:{{ $this->jadwal->onFirstPage() ? 'rgba(255,255,255,.04)' : 'rgba(63,207,142,.1)' }};border:1px solid {{ $this->jadwal->onFirstPage() ? 'var(--line)' : 'rgba(63,207,142,.3)' }};color:{{ $this->jadwal->onFirstPage() ? 'var(--mut2)' : 'var(--emer)' }};cursor:{{ $this->jadwal->onFirstPage() ? 'not-allowed' : 'pointer' }};transition:all .15s;">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M15 18l-6-6 6-6"/></svg>
                </button>
                <span style="font-size:.72rem;color:var(--ink);font-weight:600;min-width:2rem;text-align:center;">
                    {{ $this->jadwal->currentPage() }}/{{ $this->jadwal->lastPage() }}
                </span>
                <button wire:click="nextPage" {{ !$this->jadwal->hasMorePages() ? 'disabled' : '' }}
                    style="display:flex;align-items:center;justify-content:center;width:28px;height:28px;border-radius:.4rem;background:{{ !$this->jadwal->hasMorePages() ? 'rgba(255,255,255,.04)' : 'rgba(63,207,142,.1)' }};border:1px solid {{ !$this->jadwal->hasMorePages() ? 'var(--line)' : 'rgba(63,207,142,.3)' }};color:{{ !$this->jadwal->hasMorePages() ? 'var(--mut2)' : 'var(--emer)' }};cursor:{{ !$this->jadwal->hasMorePages() ? 'not-allowed' : 'pointer' }};transition:all .15s;">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M9 18l6-6-6-6"/></svg>
                </button>
            </div>
            @else
            <div style="font-size:.68rem;color:var(--mut);">Diperbarui otomatis</div>
            @endif

        </div>
        @endif
    </div>
</div>
