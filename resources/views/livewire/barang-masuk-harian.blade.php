<div>
    {{-- HEADER --}}
    <div style="margin-bottom:1.4rem;">
        <div class="font-label" style="font-size:.7rem;color:var(--mut);margin-bottom:.25rem;">Pengadaan</div>
        <h2 class="font-heading" style="font-size:1.5rem;color:var(--ink);margin:0;">Barang Masuk Harian</h2>
        <p style="color:var(--mut);font-size:.78rem;margin-top:.3rem;">Foto barang masuk (PO) per hari. Klik tanggal untuk pilih · <strong style="color:var(--ink);">Shift+klik</strong> untuk rentang · bisa gabung tanggal lepas + rentang.</p>
    </div>

    @php
        $first       = \Carbon\Carbon::create($year, $month, 1);
        $daysInMonth = $first->daysInMonth;
        $offset      = $first->dayOfWeekIso - 1;           // Senin=0 .. Minggu=6
        $today       = now()->toDateString();
        $rp = fn ($n) => 'Rp ' . number_format($n, 0, ',', '.');
        $rpShort = function ($n) {
            if ($n >= 1_000_000) return number_format($n / 1_000_000, 1, ',', '.') . 'jt';
            if ($n >= 1_000)     return number_format($n / 1_000, 0, ',', '.') . 'rb';
            return (string) (int) $n;
        };
        $sm = $this->summary;
    @endphp

    <div style="display:grid;grid-template-columns:minmax(300px,360px) 1fr;gap:1.25rem;align-items:start;" class="bmh-grid">

        {{-- ═══════════ KALENDER ═══════════ --}}
        <div class="glass-card" style="padding:1rem 1.1rem;">
            {{-- Nav bulan --}}
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:.85rem;">
                <button wire:click="prevMonth" style="background:rgba(255,255,255,.04);border:1px solid var(--line2);color:var(--mut);border-radius:.45rem;width:30px;height:30px;cursor:pointer;display:flex;align-items:center;justify-content:center;">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="15 18 9 12 15 6"/></svg>
                </button>
                <div class="font-heading" style="font-size:1rem;color:var(--gold2);">{{ $first->translatedFormat('F Y') }}</div>
                <button wire:click="nextMonth" style="background:rgba(255,255,255,.04);border:1px solid var(--line2);color:var(--mut);border-radius:.45rem;width:30px;height:30px;cursor:pointer;display:flex;align-items:center;justify-content:center;">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="9 18 15 12 9 6"/></svg>
                </button>
            </div>

            {{-- Header hari --}}
            <div style="display:grid;grid-template-columns:repeat(7,1fr);gap:4px;margin-bottom:4px;">
                @foreach(['Sn','Sl','Rb','Km','Jm','Sb','Mg'] as $h)
                <div style="text-align:center;font-size:.6rem;font-weight:700;color:var(--mut2);text-transform:uppercase;letter-spacing:.04em;padding:.2rem 0;">{{ $h }}</div>
                @endforeach
            </div>

            {{-- Grid tanggal --}}
            <div style="display:grid;grid-template-columns:repeat(7,1fr);gap:4px;">
                @for($i = 0; $i < $offset; $i++)<div></div>@endfor
                @for($d = 1; $d <= $daysInMonth; $d++)
                    @php
                        $dateStr = sprintf('%04d-%02d-%02d', $year, $month, $d);
                        $info    = $this->monthMap[$dateStr] ?? null;
                        $isSel   = in_array($dateStr, $selected, true);
                        $isToday = $dateStr === $today;
                        $hasPO   = $info && $info['count'] > 0;
                        $labaPos = $hasPO && $info['laba'] >= 0;
                        $pm      = $hasPO ? \App\Livewire\BarangMasukHarian::payMeta($info['pay'] ?? null) : null;
                        $terut   = $hasPO ? ($info['pay']['terutang'] ?? 0) : 0;
                    @endphp
                    <div class="bmh-cell {{ $hasPO && $pm ? 'paycell paycell-'.$pm['s'] : '' }}" wire:key="cal-{{ $dateStr }}"
                         style="position:relative;{{ $hasPO && $pm ? '--pc:'.$pm['color'].';--pr:'.$pm['ring'].';' : '' }}">
                        <button type="button" wire:click="toggleDate('{{ $dateStr }}', $event.shiftKey)"
                            @if($hasPO) title="{{ $info['count'] }} PO · Beli {{ $rp($info['beli']) }} · Klaim {{ $rp($info['klaim']) }} · {{ $info['laba']>=0?'Untung':'Rugi' }} {{ $rp(abs($info['laba'])) }} · Bayar: {{ $pm['label'] }}" @endif
                            style="position:relative;width:100%;aspect-ratio:1;border-radius:.5rem;cursor:pointer;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:1px;transition:all .12s;font-size:.78rem;
                                {{ $isSel
                                    ? 'background:rgba(217,164,65,.9);border:1px solid var(--gold);color:#1a0e00;font-weight:800;'
                                    : ($hasPO
                                        ? ($labaPos ? 'background:rgba(63,207,142,.08);border:1px solid rgba(63,207,142,.3);color:var(--ink);' : 'background:rgba(232,100,90,.08);border:1px solid rgba(232,100,90,.3);color:var(--ink);')
                                        : 'background:rgba(255,255,255,.015);border:1px solid var(--line);color:var(--mut2);') }}
                                {{ $isToday && !$isSel ? 'box-shadow:0 0 0 2px rgba(111,177,224,.4);' : '' }}">
                            <span style="line-height:1;">{{ $d }}</span>
                            @if($hasPO)
                            <span style="font-size:.5rem;font-weight:800;line-height:1;{{ $isSel ? 'color:#3d2600;' : ($labaPos ? 'color:var(--emer);' : 'color:var(--red2);') }}">{{ ($info['laba']>=0?'+':'−').$rpShort(abs($info['laba'])) }}</span>
                            @else
                            <span style="height:.5rem;"></span>
                            @endif
                        </button>
                        @if($hasPO && $pm)
                        {{-- Titik status bayar (glass · glowing · blinking) → link ke Tagihan hari ini --}}
                        <a href="{{ route('tagihan.index', ['tanggal' => $dateStr]) }}" wire:navigate
                           onclick="event.stopPropagation()"
                           title="Status bayar: {{ $pm['label'] }}{{ $terut > 0 ? ' · terutang '.$rp($terut) : '' }} — klik untuk lihat tagihan"
                           class="pay-dot pay-{{ $pm['s'] }} {{ $pm['hollow'] ? 'pay-hollow' : '' }}"></a>
                        @endif
                    </div>
                @endfor
            </div>

            {{-- Legend laba/rugi --}}
            <div style="display:flex;align-items:center;gap:.7rem;margin-top:.7rem;font-size:.58rem;color:var(--mut2);flex-wrap:wrap;">
                <span style="display:inline-flex;align-items:center;gap:.3rem;"><span style="width:9px;height:9px;border-radius:3px;background:rgba(63,207,142,.3);border:1px solid rgba(63,207,142,.5);"></span>untung</span>
                <span style="display:inline-flex;align-items:center;gap:.3rem;"><span style="width:9px;height:9px;border-radius:3px;background:rgba(232,100,90,.3);border:1px solid rgba(232,100,90,.5);"></span>rugi</span>
                <span style="display:inline-flex;align-items:center;gap:.3rem;"><span style="width:9px;height:9px;border-radius:3px;background:var(--gold);"></span>terpilih</span>
            </div>
            {{-- Legend status bayar (titik sudut → klik ke Tagihan) --}}
            <div style="display:flex;align-items:center;gap:.75rem;margin-top:.45rem;font-size:.58rem;color:var(--mut2);flex-wrap:wrap;">
                <span style="font-weight:700;color:var(--mut);letter-spacing:.03em;">Bayar:</span>
                <span style="display:inline-flex;align-items:center;gap:.3rem;"><span class="pay-dot pay-lunas" style="position:static;display:inline-block;"></span>lunas</span>
                <span style="display:inline-flex;align-items:center;gap:.3rem;"><span class="pay-dot pay-sebagian" style="position:static;display:inline-block;"></span>sebagian</span>
                <span style="display:inline-flex;align-items:center;gap:.3rem;"><span class="pay-dot pay-belum" style="position:static;display:inline-block;"></span>belum</span>
                <span style="display:inline-flex;align-items:center;gap:.3rem;"><span class="pay-dot pay-overdue" style="position:static;display:inline-block;"></span>jatuh tempo</span>
                <span style="display:inline-flex;align-items:center;gap:.3rem;"><span class="pay-dot pay-none pay-hollow" style="position:static;display:inline-block;"></span>belum ada</span>
            </div>

            {{-- Preset cepat --}}
            <div style="display:flex;flex-wrap:wrap;gap:.35rem;margin-top:.85rem;padding-top:.85rem;border-top:1px solid var(--line);">
                @foreach(['hari_ini'=>'Hari Ini','kemarin'=>'Kemarin','tujuh'=>'7 Hari','bulan_ini'=>'Bulan Ini (ada PO)'] as $k=>$lbl)
                <button wire:click="preset('{{ $k }}')" style="font-size:.66rem;padding:.28rem .6rem;border-radius:999px;background:transparent;border:1px solid var(--line2);color:var(--mut);cursor:pointer;transition:.12s;"
                    onmouseover="this.style.color='var(--ink)';this.style.borderColor='var(--line3)'" onmouseout="this.style.color='var(--mut)';this.style.borderColor='var(--line2)'">{{ $lbl }}</button>
                @endforeach
                @if(count($selected))
                <button wire:click="resetPilihan" style="font-size:.66rem;padding:.28rem .6rem;border-radius:999px;background:rgba(232,100,90,.06);border:1px solid rgba(232,100,90,.25);color:var(--red2);cursor:pointer;">Reset</button>
                @endif
            </div>
        </div>

        {{-- ═══════════ HASIL PER HARI ═══════════ --}}
        <div>
            {{-- Summary bar: OVERALL beli vs klaim -> untung/rugi gabungan semua tanggal terpilih --}}
            @php $oLaba = $sm['laba']; $oCol = $oLaba>0?'var(--emer2)':($oLaba<0?'var(--red2)':'var(--gold2)'); $oBg = $oLaba>0?'rgba(63,207,142,.1)':($oLaba<0?'rgba(232,100,90,.1)':'rgba(217,164,65,.08)'); $oBd = $oLaba>0?'rgba(63,207,142,.3)':($oLaba<0?'rgba(232,100,90,.3)':'rgba(217,164,65,.25)'); @endphp
            <div style="display:flex;flex-wrap:wrap;gap:.6rem;margin-bottom:1rem;">
                @foreach([
                    ['Hari', $sm['hari'] . ' hari', 'var(--gold2)'],
                    ['Total PO', $sm['po'] . ' PO', 'var(--blue)'],
                    ['Nilai Beli (HPP)', $rp($sm['beli']), 'var(--red2)'],
                    ['Nilai Klaim/Jual', $rp($sm['klaim']), 'var(--blue)'],
                ] as [$lbl,$val,$col])
                <div class="glass-card" style="padding:.7rem 1rem;flex:1;min-width:120px;">
                    <div style="font-size:.58rem;font-weight:700;letter-spacing:.05em;text-transform:uppercase;color:var(--mut);margin-bottom:.3rem;">{{ $lbl }}</div>
                    <div class="font-mono" style="font-size:.92rem;font-weight:800;color:{{ $col }};">{{ $val }}</div>
                </div>
                @endforeach
                {{-- Headline laba/rugi --}}
                <div style="flex:1.4;min-width:170px;border-radius:1rem;padding:.7rem 1.1rem;background:{{ $oBg }};border:1px solid {{ $oBd }};">
                    <div style="font-size:.58rem;font-weight:700;letter-spacing:.05em;text-transform:uppercase;color:{{ $oCol }};margin-bottom:.3rem;display:flex;align-items:center;gap:.3rem;">
                        <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">@if($oLaba>=0)<polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/><polyline points="17 6 23 6 23 12"/>@else<polyline points="23 18 13.5 8.5 8.5 13.5 1 6"/><polyline points="17 18 23 18 23 12"/>@endif</svg>
                        {{ $oLaba>=0 ? 'Untung' : 'Rugi' }} Pengadaan
                    </div>
                    <div class="font-mono" style="font-size:1.1rem;font-weight:900;color:{{ $oCol }};line-height:1.1;">{{ ($oLaba>=0?'+':'−').$rp(abs($oLaba)) }}</div>
                    <div style="font-size:.62rem;color:var(--mut);margin-top:.15rem;">margin {{ number_format(abs($sm['margin']),1) }}% · klaim vs beli</div>
                </div>
            </div>

            @if(empty($selected))
            <div class="glass-card" style="padding:2.5rem 1.5rem;text-align:center;color:var(--mut);">
                <svg width="40" height="40" fill="none" stroke="var(--line3)" stroke-width="1" viewBox="0 0 24 24" style="margin:0 auto .75rem;"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                <div style="font-size:.95rem;margin-bottom:.3rem;color:var(--ink);">Pilih tanggal di kalender</div>
                <div style="font-size:.76rem;">Klik satu/beberapa hari, atau Shift+klik untuk rentang. Bisa gabung mis. 5 & 7 atau 5–7.</div>
            </div>
            @else
                @forelse($this->grouped as $tgl => $g)
                @php $c = \Carbon\Carbon::parse($tgl); @endphp
                <div class="glass-card" style="padding:0;overflow:hidden;margin-bottom:.9rem;" wire:key="day-{{ $tgl }}">
                    {{-- Header hari --}}
                    <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:.5rem;padding:.7rem 1rem;background:linear-gradient(90deg,rgba(217,164,65,.07),transparent);border-bottom:1px solid var(--line);">
                        <div style="display:flex;align-items:center;gap:.6rem;">
                            <div style="width:8px;height:8px;border-radius:50%;background:var(--gold);box-shadow:0 0 6px var(--gold);"></div>
                            <span class="font-heading" style="font-size:.95rem;color:var(--ink);">{{ $c->translatedFormat('l, d M Y') }}</span>
                            <span style="font-size:.66rem;padding:.1rem .5rem;border-radius:999px;background:rgba(111,177,224,.12);border:1px solid rgba(111,177,224,.25);color:var(--blue);">{{ $g['count'] }} PO</span>
                            @php $pmH = \App\Livewire\BarangMasukHarian::payMeta($g['pay'] ?? null); $terH = $g['pay']['terutang'] ?? 0; @endphp
                            <a href="{{ route('tagihan.index', ['tanggal' => $tgl]) }}" wire:navigate
                               title="{{ $pmH['label'] }}{{ $terH > 0 ? ' · terutang '.$rp($terH) : '' }} — buka Tagihan hari ini"
                               style="display:inline-flex;align-items:center;gap:.34rem;font-size:.64rem;font-weight:700;padding:.14rem .55rem .14rem .4rem;border-radius:999px;text-decoration:none;--pc:{{ $pmH['color'] }};--pr:{{ $pmH['ring'] }};background:linear-gradient(180deg,rgba(255,255,255,.05),transparent);border:1px solid var(--pr);color:var(--pc);backdrop-filter:blur(4px);-webkit-backdrop-filter:blur(4px);">
                                <span class="pay-dot pay-{{ $pmH['s'] }} {{ $pmH['hollow'] ? 'pay-hollow' : '' }}" style="position:static;width:9px;height:9px;"></span>
                                {{ $pmH['label'] }}@if($terH > 0) · {{ $rp($terH) }}@endif
                                <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" style="margin-left:1px;opacity:.85;"><polyline points="9 18 15 12 9 6"/></svg>
                            </a>
                        </div>
                        @php $dLaba=$g['laba']; $dCol=$dLaba>0?'var(--emer2)':($dLaba<0?'var(--red2)':'var(--gold2)'); @endphp
                        <div style="display:flex;align-items:center;gap:.7rem;flex-wrap:wrap;">
                            <span class="font-mono" style="font-size:.7rem;color:var(--red2);" title="Nilai beli (HPP)">{{ $rp($g['beli']) }}</span>
                            <span style="color:var(--mut2);"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" style="display:inline-block;vertical-align:middle"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg></span>
                            <span class="font-mono" style="font-size:.7rem;color:var(--blue);" title="Nilai klaim/jual">{{ $rp($g['klaim']) }}</span>
                            <span class="font-mono" style="font-size:.82rem;font-weight:800;padding:.12rem .55rem;border-radius:999px;color:{{ $dCol }};background:{{ $dLaba>=0?'rgba(63,207,142,.12)':'rgba(232,100,90,.12)' }};border:1px solid {{ $dLaba>=0?'rgba(63,207,142,.3)':'rgba(232,100,90,.3)' }};">{{ ($dLaba>=0?'+':'−').$rp(abs($dLaba)) }}</span>
                        </div>
                    </div>

                    {{-- PO per hari --}}
                    @foreach($g['rows'] as $r)
                    @php $po = $r['po']; $fin = $r['fin']; $pLaba = $fin['laba']; $pCol = $pLaba>0?'var(--emer2)':($pLaba<0?'var(--red2)':'var(--mut)'); @endphp
                    <div x-data="{ open:false }" wire:key="bmpo-{{ $po->id }}" style="border-bottom:1px solid rgba(31,61,48,.4);">
                        <div @click="open=!open" style="display:flex;align-items:center;gap:.8rem;padding:.65rem 1rem;cursor:pointer;" onmouseover="this.style.background='rgba(255,255,255,.015)'" onmouseout="this.style.background='transparent'">
                            <svg width="14" height="14" fill="none" stroke="var(--mut)" stroke-width="1.8" viewBox="0 0 24 24" style="flex-shrink:0;"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/></svg>
                            <div style="flex:1;min-width:0;">
                                <div style="font-size:.84rem;font-weight:600;color:var(--ink);">{{ $po->distributor->name ?? '—' }}</div>
                                <div style="font-size:.66rem;color:var(--mut2);">
                                    @if($po->nomor_invoice)<span class="font-mono">#{{ $po->nomor_invoice }}</span> · @endif{{ $po->items->count() }} item
                                    @if(isset($po->status_bayar))<span style="margin-left:.3rem;color:{{ $po->status_bayar==='lunas'?'var(--emer)':'var(--gold2)' }};">{{ ucfirst($po->status_bayar) }}</span>@endif
                                </div>
                            </div>
                            <div style="text-align:right;flex-shrink:0;">
                                <div style="font-size:.62rem;color:var(--mut2);line-height:1.2;"><span class="font-mono" style="color:var(--red2);">{{ $rp($fin['beli']) }}</span> <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" style="display:inline-block;vertical-align:middle"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg> <span class="font-mono" style="color:var(--blue);">{{ $rp($fin['klaim']) }}</span></div>
                                <div class="font-mono" style="font-size:.82rem;font-weight:800;color:{{ $pCol }};">{{ ($pLaba>=0?'+':'−').$rp(abs($pLaba)) }}</div>
                            </div>
                            <svg x-bind:style="open ? 'transform:rotate(180deg)' : ''" width="13" height="13" fill="none" stroke="var(--mut)" stroke-width="2" viewBox="0 0 24 24" style="transition:transform .2s;flex-shrink:0;"><polyline points="6 9 12 15 18 9"/></svg>
                        </div>
                        {{-- detail item --}}
                        <div x-show="open" x-cloak style="padding:.5rem 1rem .8rem 2.6rem;background:rgba(10,20,16,.25);">
                            <table style="width:100%;border-collapse:collapse;font-size:.72rem;">
                                <thead><tr style="color:var(--mut);">
                                    <th style="text-align:left;padding:.3rem .5rem;font-size:.6rem;text-transform:uppercase;">Obat</th>
                                    <th style="text-align:right;padding:.3rem .5rem;font-size:.6rem;text-transform:uppercase;">Box</th>
                                    <th style="text-align:right;padding:.3rem .5rem;font-size:.6rem;text-transform:uppercase;">Beli</th>
                                    <th style="text-align:right;padding:.3rem .5rem;font-size:.6rem;text-transform:uppercase;">Klaim/Jual</th>
                                    <th style="text-align:right;padding:.3rem .5rem;font-size:.6rem;text-transform:uppercase;">Laba/Rugi</th>
                                </tr></thead>
                                <tbody>
                                    @foreach($po->items as $it)
                                    @php
                                        $o = $it->obat;
                                        $units = (float)$it->jumlah_box * max(1,(float)$it->isi_per_box);
                                        $iBeli = (float)$it->subtotal;
                                        $iKronis = (($it->tipe_obat ?? $o->tipe_obat ?? 'kronis') === 'kronis');
                                        $iPer = $iKronis ? (float)($o->klaim_bpjs_per_unit ?? 0) * \App\Livewire\BarangMasukHarian::faktorMul($o->faktor_jasa_farmasi) : (float)($o->harga_jual_per_unit ?? 0);
                                        $iKlaim = $units * $iPer;
                                        $iLaba = $iKlaim - $iBeli; $iCol = $iLaba>0?'var(--emer2)':($iLaba<0?'var(--red2)':'var(--mut)');
                                    @endphp
                                    <tr style="border-top:1px solid rgba(31,61,48,.35);">
                                        <td style="padding:.3rem .5rem;color:var(--ink);">{{ $o->nama_obat ?? '—' }} <span style="color:var(--mut2);font-size:.6rem;">×{{ $it->jumlah_box }}box</span></td>
                                        <td class="font-mono" style="padding:.3rem .5rem;text-align:right;color:var(--mut2);">{{ (int)$units }} {{ $o->satuan ?? '' }}</td>
                                        <td class="font-mono" style="padding:.3rem .5rem;text-align:right;color:var(--red2);">{{ $rp($iBeli) }}</td>
                                        <td class="font-mono" style="padding:.3rem .5rem;text-align:right;color:var(--blue);">{{ $rp($iKlaim) }}</td>
                                        <td class="font-mono" style="padding:.3rem .5rem;text-align:right;font-weight:700;color:{{ $iCol }};">{{ ($iLaba>=0?'+':'−').$rp(abs($iLaba)) }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    @endforeach
                </div>
                @empty
                <div class="glass-card" style="padding:2rem;text-align:center;color:var(--mut);font-size:.84rem;">Tidak ada barang masuk pada tanggal terpilih.</div>
                @endforelse
            @endif
        </div>
    </div>

    <style>
        @media (max-width: 820px) { .bmh-grid { grid-template-columns: 1fr !important; } }

        /* ═══ Titik status bayar — GLASS · GLOW · BLINK ═══ */
        .pay-dot{
            position:absolute; top:3px; right:3px;
            width:12px; height:12px; border-radius:50%;
            z-index:3; cursor:pointer; text-decoration:none;
            border:1px solid var(--pr, rgba(255,255,255,.45));
            background:
                radial-gradient(circle at 32% 27%, rgba(255,255,255,.9), rgba(255,255,255,.18) 42%, transparent 64%),
                var(--pc, #888);
            box-shadow:0 0 7px 1px var(--pc, #888), inset 0 0 3px rgba(255,255,255,.45);
            backdrop-filter:blur(3px); -webkit-backdrop-filter:blur(3px);
            transition:transform .14s ease, box-shadow .14s ease;
        }
        .pay-dot:hover{ transform:scale(1.5); box-shadow:0 0 12px 3px var(--pc,#888), inset 0 0 4px rgba(255,255,255,.6); }

        /* warna per status (dipakai kalender + legend) */
        .pay-lunas   { --pc:#3fcf8e; --pr:rgba(63,207,142,.6); }
        .pay-sebagian{ --pc:#f2c668; --pr:rgba(242,198,104,.6); }
        .pay-belum   { --pc:#d9a441; --pr:rgba(217,164,65,.6); }
        .pay-overdue { --pc:#e8645a; --pr:rgba(232,100,90,.72); }
        .pay-none    { --pc:transparent; --pr:var(--line3); }
        .pay-hollow  { background:radial-gradient(circle at 32% 27%, rgba(255,255,255,.16), transparent 60%); box-shadow:none; border:1px dashed var(--mut2); backdrop-filter:none; }

        /* BLINK — tarik perhatian utk yg belum lunas */
        .pay-overdue { animation:payBlink 1s steps(1,end) infinite; }
        .pay-belum   { animation:payPulse 1.8s ease-in-out infinite; }
        .pay-sebagian{ animation:payPulse 2.5s ease-in-out infinite; }
        @keyframes payBlink{
            0%,100%{ box-shadow:0 0 9px 2px var(--pc), inset 0 0 3px rgba(255,255,255,.55); opacity:1; }
            50%    { box-shadow:0 0 2px 0 var(--pc), inset 0 0 2px rgba(255,255,255,.3); opacity:.32; }
        }
        @keyframes payPulse{
            0%,100%{ box-shadow:0 0 5px 1px var(--pc), inset 0 0 3px rgba(255,255,255,.45); }
            50%    { box-shadow:0 0 12px 3px var(--pc), inset 0 0 3px rgba(255,255,255,.55); }
        }

        /* Ring berdenyut di SEL utk hari belum lunas — glass halo */
        .paycell{ border-radius:.6rem; }
        .paycell-overdue{ animation:cellBlink 1s steps(1,end) infinite; }
        .paycell-belum  { animation:cellPulse 2s ease-in-out infinite; }
        @keyframes cellBlink{
            0%,100%{ box-shadow:0 0 0 1.5px var(--pr,#e8645a), 0 0 10px 1px var(--pr,#e8645a); }
            50%    { box-shadow:0 0 0 1px rgba(232,100,90,.12); }
        }
        @keyframes cellPulse{
            0%,100%{ box-shadow:0 0 0 1px var(--pr,#d9a441); }
            50%    { box-shadow:0 0 0 1.5px var(--pr,#d9a441), 0 0 9px 0 var(--pr,#d9a441); }
        }
        @media (prefers-reduced-motion: reduce){
            .pay-overdue,.pay-belum,.pay-sebagian,.paycell-overdue,.paycell-belum{ animation:none !important; }
        }
    </style>
</div>
