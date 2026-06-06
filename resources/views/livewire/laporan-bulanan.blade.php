<div>
    {{-- Header + controls --}}
    <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:.75rem;margin-bottom:1.5rem;">
        <div>
            <div style="font-size:.68rem;color:var(--mut);text-transform:uppercase;letter-spacing:.08em;margin-bottom:.3rem;">Keuangan · Laporan Periodik</div>
            <h2 class="font-heading" style="font-size:1.35rem;color:var(--ink);margin:0 0 .3rem;">
                Laporan Bulan: <em style="color:var(--gold2);">{{ $this->periode }}</em>
            </h2>
            <p style="font-size:.76rem;color:var(--mut);margin:0;">
                Analisis laba rugi keuangan klinik — <span style="color:var(--emer2);">Obat Kronis</span> &amp; <span style="color:var(--blue);">Obat Non-Kronis</span>
            </p>
        </div>
        <div style="display:flex;gap:.5rem;align-items:center;">
            <select wire:model.live="bulan" class="form-input" style="width:130px;">
                @foreach(\App\Models\RekonsiliasiiBpjs::bulanLabels() as $b => $nm)
                <option value="{{ $b }}">{{ $nm }}</option>
                @endforeach
            </select>
            <select wire:model.live="tahun" class="form-input" style="width:90px;">
                @foreach(range(2024, date('Y')+1) as $y)
                <option value="{{ $y }}">{{ $y }}</option>
                @endforeach
            </select>
            <button wire:click="exportCsv" class="btn-outline">
                <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                Export CSV
            </button>
        </div>
    </div>

    {{-- Tab switcher --}}
    <div style="display:flex;gap:.3rem;margin-bottom:1.25rem;background:var(--panel);border:1px solid var(--line);border-radius:.75rem;padding:.3rem;width:fit-content;">
        <button wire:click="$set('activeTab','ringkasan')"
            style="padding:.4rem 1rem;border-radius:.55rem;border:none;cursor:pointer;font-size:.78rem;font-weight:600;transition:all .15s;
                background:{{ $activeTab==='ringkasan' ? 'var(--bg2)' : 'transparent' }};
                color:{{ $activeTab==='ringkasan' ? 'var(--gold2)' : 'var(--mut)' }};
                box-shadow:{{ $activeTab==='ringkasan' ? '0 1px 4px rgba(0,0,0,.3)' : 'none' }};">
            Ringkasan
        </button>
        <button wire:click="$set('activeTab','bpjs')"
            style="padding:.4rem 1rem;border-radius:.55rem;border:none;cursor:pointer;font-size:.78rem;font-weight:600;transition:all .15s;
                background:{{ $activeTab==='bpjs' ? 'rgba(63,207,142,.12)' : 'transparent' }};
                color:{{ $activeTab==='bpjs' ? 'var(--emer2)' : 'var(--mut)' }};
                box-shadow:{{ $activeTab==='bpjs' ? '0 1px 4px rgba(0,0,0,.3)' : 'none' }};">
            Obat Kronis
            <span style="font-size:.65rem;background:rgba(63,207,142,.15);color:var(--emer2);padding:.05rem .35rem;border-radius:.3rem;margin-left:.3rem;">{{ $this->detailBpjs->count() }}</span>
        </button>
        <button wire:click="$set('activeTab','nonkronis')"
            style="padding:.4rem 1rem;border-radius:.55rem;border:none;cursor:pointer;font-size:.78rem;font-weight:600;transition:all .15s;
                background:{{ $activeTab==='nonkronis' ? 'rgba(111,177,224,.12)' : 'transparent' }};
                color:{{ $activeTab==='nonkronis' ? 'var(--blue)' : 'var(--mut)' }};
                box-shadow:{{ $activeTab==='nonkronis' ? '0 1px 4px rgba(0,0,0,.3)' : 'none' }};">
            Obat Non-Kronis
            <span style="font-size:.65rem;background:rgba(111,177,224,.15);color:var(--blue);padding:.05rem .35rem;border-radius:.3rem;margin-left:.3rem;">{{ $this->detailNonKronis->count() }}</span>
        </button>
    </div>

    @php $r = $this->ringkasan; @endphp

    {{-- ===== TAB: RINGKASAN ===== --}}
    @if($activeTab === 'ringkasan')

    @php
        $bpjsPct  = $r['totalPend'] > 0 ? round($r['pendBpjs']/$r['totalPend']*100,2) : 0;
        $tunaiPct = $r['totalPend'] > 0 ? round($r['pendTunai']/$r['totalPend']*100,2) : 0;
    @endphp

    {{-- ① HERO METRICS ROW --}}
    <div style="display:grid;grid-template-columns:1.7fr 1fr 1fr 1fr;gap:.85rem;margin-bottom:1.1rem;">

        {{-- Total Pendapatan — hero card --}}
        <div style="background:linear-gradient(135deg,rgba(217,164,65,.14) 0%,rgba(217,164,65,.03) 100%);border:1px solid rgba(217,164,65,.32);border-radius:1rem;padding:1.3rem 1.6rem;position:relative;overflow:hidden;">
            <div style="position:absolute;top:-12px;right:-12px;width:90px;height:90px;background:radial-gradient(circle,rgba(217,164,65,.12),transparent 70%);pointer-events:none;"></div>
            <div style="font-size:.57rem;text-transform:uppercase;letter-spacing:.11em;color:var(--mut);font-weight:700;margin-bottom:.55rem;">Total Pendapatan</div>
            <div class="font-mono" style="font-size:1.6rem;font-weight:800;color:var(--gold2);line-height:1.05;margin-bottom:.45rem;letter-spacing:-.02em;">Rp {{ number_format($r['totalPend'],0,',','.') }}</div>
            <div style="display:flex;gap:.45rem;flex-wrap:wrap;">
                <span style="font-size:.62rem;color:var(--emer2);background:rgba(63,207,142,.12);border:1px solid rgba(63,207,142,.2);padding:.12rem .5rem;border-radius:.3rem;font-weight:600;">BPJS {{ $bpjsPct }}%</span>
                <span style="font-size:.62rem;color:var(--blue);background:rgba(111,177,224,.12);border:1px solid rgba(111,177,224,.2);padding:.12rem .5rem;border-radius:.3rem;font-weight:600;">Tunai {{ $tunaiPct }}%</span>
            </div>
        </div>

        {{-- Laba Kotor --}}
        <div style="background:rgba({{ $r['labaKotor'] >= 0 ? '63,207,142' : '232,100,90' }},.09);border:1px solid rgba({{ $r['labaKotor'] >= 0 ? '63,207,142' : '232,100,90' }},.28);border-radius:1rem;padding:1.3rem 1.25rem;">
            <div style="font-size:.57rem;text-transform:uppercase;letter-spacing:.1em;color:var(--mut);font-weight:700;margin-bottom:.5rem;">Laba Kotor</div>
            <div class="font-mono" style="font-size:1.2rem;font-weight:800;color:{{ $r['labaKotor'] >= 0 ? 'var(--emer2)' : 'var(--red2)' }};line-height:1.1;margin-bottom:.5rem;">
                {{ $r['labaKotor'] >= 0 ? '+' : '' }}Rp {{ number_format($r['labaKotor'],0,',','.') }}
            </div>
            <div style="display:flex;align-items:center;gap:.5rem;">
                <div style="flex:1;height:4px;background:rgba(0,0,0,.25);border-radius:2px;overflow:hidden;">
                    <div style="height:100%;width:{{ min(abs($r['marginPersen']),100) }}%;background:{{ $r['marginPersen'] >= 0 ? '#3fcf8e' : '#e8645a' }};border-radius:2px;transition:width .4s;"></div>
                </div>
                <span class="font-mono" style="font-size:.72rem;font-weight:800;color:{{ $r['marginPersen'] >= 0 ? 'var(--emer2)' : 'var(--red2)' }};white-space:nowrap;">{{ $r['marginPersen'] }}%</span>
            </div>
        </div>

        {{-- Biaya Operasional --}}
        <div style="border:1px solid var(--line);border-radius:1rem;padding:1.3rem 1.25rem;background:rgba(0,0,0,.15);">
            <div style="font-size:.57rem;text-transform:uppercase;letter-spacing:.1em;color:var(--mut);font-weight:700;margin-bottom:.5rem;">Biaya Ops</div>
            <div class="font-mono" style="font-size:1.2rem;font-weight:700;color:var(--mut2);line-height:1.1;margin-bottom:.35rem;">(Rp {{ number_format($r['totalBiayaOps'],0,',','.') }})</div>
            <div style="font-size:.63rem;color:var(--mut);line-height:1.4;">SDM · Utilitas<br>Admin · Sewa</div>
        </div>

        {{-- Laba Bersih --}}
        <div style="background:rgba({{ $r['labaBersih'] >= 0 ? '217,164,65' : '232,100,90' }},.11);border:1px solid rgba({{ $r['labaBersih'] >= 0 ? '217,164,65' : '232,100,90' }},.32);border-radius:1rem;padding:1.3rem 1.25rem;">
            <div style="font-size:.57rem;text-transform:uppercase;letter-spacing:.1em;color:var(--mut);font-weight:700;margin-bottom:.5rem;">Laba Bersih</div>
            <div class="font-mono" style="font-size:1.2rem;font-weight:800;color:{{ $r['labaBersih'] >= 0 ? 'var(--gold2)' : 'var(--red2)' }};line-height:1.1;margin-bottom:.35rem;">
                {{ $r['labaBersih'] >= 0 ? '+' : '' }}Rp {{ number_format($r['labaBersih'],0,',','.') }}
            </div>
            <div style="font-size:.63rem;color:var(--mut);">Net setelah ops</div>
        </div>
    </div>

    {{-- ② REVENUE COMPOSITION BAR --}}
    <div class="glass-card" style="padding:.9rem 1.25rem 1rem;margin-bottom:1.1rem;">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:.65rem;">
            <span style="font-size:.58rem;text-transform:uppercase;letter-spacing:.1em;color:var(--mut);font-weight:700;">Komposisi Pendapatan {{ $this->periode }}</span>
            <span class="font-mono" style="font-size:.65rem;color:var(--mut);">Total Rp {{ number_format($r['totalPend'],0,',','.') }}</span>
        </div>
        <div style="display:flex;height:12px;border-radius:6px;overflow:hidden;background:rgba(0,0,0,.25);gap:2px;">
            @if($bpjsPct > 0)<div style="width:{{ $bpjsPct }}%;background:linear-gradient(90deg,#3fcf8e,rgba(63,207,142,.7));flex-shrink:0;"></div>@endif
            @if($tunaiPct > 0)<div style="flex:1;background:linear-gradient(90deg,rgba(111,177,224,.8),#6fb1e0);"></div>@endif
        </div>
        <div style="display:flex;align-items:center;justify-content:space-between;margin-top:.65rem;">
            <div style="display:flex;align-items:center;gap:.75rem;">
                <div style="display:flex;align-items:center;gap:.38rem;">
                    <div style="width:9px;height:9px;background:#3fcf8e;border-radius:2px;flex-shrink:0;"></div>
                    <span style="font-size:.7rem;color:var(--mut2);">BPJS/JKN — Obat Kronis</span>
                    <span class="font-mono" style="font-size:.72rem;font-weight:700;color:var(--emer2);">{{ $bpjsPct }}%</span>
                    <span style="font-size:.65rem;color:var(--mut);">· Rp {{ number_format($r['pendBpjs'],0,',','.') }}</span>
                </div>
            </div>
            <div style="display:flex;align-items:center;gap:.38rem;">
                <div style="width:9px;height:9px;background:#6fb1e0;border-radius:2px;flex-shrink:0;"></div>
                <span style="font-size:.7rem;color:var(--mut2);">Tunai — Pasien Umum</span>
                <span class="font-mono" style="font-size:.72rem;font-weight:700;color:var(--blue);">{{ $tunaiPct }}%</span>
                <span style="font-size:.65rem;color:var(--mut);">· Rp {{ number_format($r['pendTunai'],0,',','.') }}</span>
            </div>
        </div>
    </div>

    {{-- ③ TWO-COLUMN: P&L Statement (left) + Segment Deep Dive (right) --}}
    <div style="display:grid;grid-template-columns:1.55fr 1fr;gap:1rem;margin-bottom:1.25rem;align-items:start;">

        {{-- LEFT: Styled P&L Income Statement --}}
        <div class="glass-card" style="overflow:hidden;">
            <div style="padding:.75rem 1.2rem;border-bottom:1px solid var(--line);background:rgba(0,0,0,.22);display:flex;align-items:center;justify-content:space-between;">
                <div>
                    <div style="font-size:.73rem;font-weight:700;color:var(--ink);">Laporan Laba Rugi</div>
                    <div style="font-size:.61rem;color:var(--mut);">{{ $this->periode }} · Klinik Dokterku</div>
                </div>
                <span style="font-size:.58rem;color:var(--mut);background:var(--bg2);border:1px solid var(--line);padding:.15rem .55rem;border-radius:.35rem;letter-spacing:.05em;">IFRS-like Format</span>
            </div>
            <table style="width:100%;border-collapse:collapse;font-size:.8rem;">
            <tbody>

            {{-- PENDAPATAN --}}
            <tr style="background:rgba(63,207,142,.06);">
                <td colspan="3" style="padding:.42rem 1.2rem .28rem;border-left:3px solid #3fcf8e;">
                    <span style="font-size:.57rem;text-transform:uppercase;letter-spacing:.1em;color:var(--emer2);font-weight:800;">I. Pendapatan</span>
                </td>
            </tr>
            <tr>
                <td style="padding:.5rem 1.2rem .5rem 2rem;border-left:3px solid rgba(63,207,142,.22);">
                    <div style="font-weight:600;color:var(--emer2);">Pendapatan BPJS/JKN</div>
                    <div style="font-size:.61rem;color:var(--mut);margin-top:.08rem;">Klaim BPJS × Faktor JF (PMK 3/2023) · Piutang klaim</div>
                </td>
                <td class="font-mono" style="text-align:right;padding:.5rem .6rem;color:var(--emer2);font-weight:700;white-space:nowrap;">Rp {{ number_format($r['pendBpjs'],0,',','.') }}</td>
                <td style="text-align:right;padding:.5rem 1rem;width:58px;"><span style="font-size:.63rem;color:var(--emer2);background:rgba(63,207,142,.1);padding:.1rem .38rem;border-radius:.25rem;font-weight:600;">{{ $bpjsPct }}%</span></td>
            </tr>
            <tr style="border-bottom:1px solid var(--line);">
                <td style="padding:.5rem 1.2rem .5rem 2rem;border-left:3px solid rgba(111,177,224,.22);">
                    <div style="font-weight:600;color:var(--blue);">Pendapatan Tunai (Pasien Umum)</div>
                    <div style="font-size:.61rem;color:var(--mut);margin-top:.08rem;">Penjualan kas aktual — stok keluar bulan ini</div>
                </td>
                <td class="font-mono" style="text-align:right;padding:.5rem .6rem;color:var(--blue);font-weight:700;white-space:nowrap;">Rp {{ number_format($r['pendTunai'],0,',','.') }}</td>
                <td style="text-align:right;padding:.5rem 1rem;"><span style="font-size:.63rem;color:var(--blue);background:rgba(111,177,224,.1);padding:.1rem .38rem;border-radius:.25rem;font-weight:600;">{{ $tunaiPct }}%</span></td>
            </tr>
            <tr style="background:rgba(217,164,65,.06);border-bottom:2px solid rgba(217,164,65,.22);">
                <td style="padding:.52rem 1.2rem;font-weight:800;color:var(--gold2);">TOTAL PENDAPATAN</td>
                <td class="font-mono" style="text-align:right;padding:.52rem .6rem;font-size:.9rem;font-weight:800;color:var(--gold2);white-space:nowrap;">Rp {{ number_format($r['totalPend'],0,',','.') }}</td>
                <td style="padding:.52rem 1rem;"></td>
            </tr>

            {{-- HPP --}}
            <tr style="background:rgba(232,100,90,.04);">
                <td colspan="3" style="padding:.42rem 1.2rem .28rem;border-left:3px solid rgba(232,100,90,.55);">
                    <span style="font-size:.57rem;text-transform:uppercase;letter-spacing:.1em;color:var(--red2);font-weight:800;">II. Harga Pokok Penjualan (HPP)</span>
                </td>
            </tr>
            <tr>
                <td style="padding:.44rem 1.2rem .44rem 2rem;color:var(--mut2);border-left:3px solid rgba(232,100,90,.14);">HPP Obat Kronis</td>
                <td class="font-mono" style="text-align:right;padding:.44rem .6rem;color:var(--mut2);white-space:nowrap;">(Rp {{ number_format($r['hppBpjs'],0,',','.') }})</td>
                <td style="padding:.44rem 1rem;"></td>
            </tr>
            <tr style="border-bottom:1px solid var(--line);">
                <td style="padding:.44rem 1.2rem .44rem 2rem;color:var(--mut2);border-left:3px solid rgba(232,100,90,.14);">HPP Obat Non-Kronis</td>
                <td class="font-mono" style="text-align:right;padding:.44rem .6rem;color:var(--mut2);white-space:nowrap;">(Rp {{ number_format($r['hppTunai'],0,',','.') }})</td>
                <td style="padding:.44rem 1rem;"></td>
            </tr>
            <tr style="border-bottom:2px solid var(--line);">
                <td style="padding:.5rem 1.2rem;font-weight:600;color:var(--mut2);">TOTAL HPP</td>
                <td class="font-mono" style="text-align:right;padding:.5rem .6rem;font-weight:700;color:var(--red2);white-space:nowrap;">(Rp {{ number_format($r['totalHpp'],0,',','.') }})</td>
                <td style="padding:.5rem 1rem;"></td>
            </tr>

            {{-- LABA KOTOR --}}
            <tr style="background:rgba({{ $r['labaKotor'] >= 0 ? '63,207,142' : '232,100,90' }},.08);border-bottom:2px solid rgba({{ $r['labaKotor'] >= 0 ? '63,207,142' : '232,100,90' }},.18);">
                <td style="padding:.6rem 1.2rem;border-left:3px solid {{ $r['labaKotor'] >= 0 ? '#3fcf8e' : '#e8645a' }};">
                    <span style="font-size:.83rem;font-weight:800;color:{{ $r['labaKotor'] >= 0 ? 'var(--emer2)' : 'var(--red2)' }};">III. LABA KOTOR</span>
                </td>
                <td class="font-mono" style="text-align:right;padding:.6rem .6rem;font-weight:800;font-size:.9rem;color:{{ $r['labaKotor'] >= 0 ? 'var(--emer2)' : 'var(--red2)' }};white-space:nowrap;">
                    {{ $r['labaKotor'] >= 0 ? '+' : '' }}Rp {{ number_format($r['labaKotor'],0,',','.') }}
                </td>
                <td style="text-align:right;padding:.6rem 1rem;"><span style="font-size:.7rem;font-weight:800;color:{{ $r['marginPersen'] >= 0 ? 'var(--emer2)' : 'var(--red2)' }};">{{ $r['marginPersen'] }}%</span></td>
            </tr>

            {{-- BIAYA OPS --}}
            <tr style="border-bottom:1px solid var(--line);">
                <td style="padding:.48rem 1.2rem .48rem 2rem;border-left:3px solid rgba(255,255,255,.07);">
                    <div style="color:var(--mut2);">IV. Biaya Operasional</div>
                    <div style="font-size:.61rem;color:var(--mut);">SDM, utilitas, administrasi, sewa</div>
                </td>
                <td class="font-mono" style="text-align:right;padding:.48rem .6rem;color:var(--mut2);white-space:nowrap;">(Rp {{ number_format($r['totalBiayaOps'],0,',','.') }})</td>
                <td style="padding:.48rem 1rem;"></td>
            </tr>

            {{-- LABA BERSIH --}}
            <tr style="background:rgba({{ $r['labaBersih'] >= 0 ? '217,164,65' : '232,100,90' }},.1);border-top:1px solid rgba({{ $r['labaBersih'] >= 0 ? '217,164,65' : '232,100,90' }},.2);">
                <td style="padding:.72rem 1.2rem;border-left:3px solid {{ $r['labaBersih'] >= 0 ? 'var(--gold2)' : '#e8645a' }};">
                    <div style="font-size:.85rem;font-weight:800;color:{{ $r['labaBersih'] >= 0 ? 'var(--gold2)' : 'var(--red2)' }};">V. LABA BERSIH</div>
                    <div style="font-size:.61rem;color:var(--mut);">Setelah seluruh biaya operasional</div>
                </td>
                <td class="font-mono" style="text-align:right;padding:.72rem .6rem;font-weight:800;font-size:.95rem;color:{{ $r['labaBersih'] >= 0 ? 'var(--gold2)' : 'var(--red2)' }};white-space:nowrap;">
                    {{ $r['labaBersih'] >= 0 ? '+' : '' }}Rp {{ number_format($r['labaBersih'],0,',','.') }}
                </td>
                <td style="padding:.72rem 1rem;"></td>
            </tr>

            </tbody>
            </table>
        </div>

        {{-- RIGHT: Segment Cards + Pengadaan --}}
        <div style="display:flex;flex-direction:column;gap:.85rem;">

            {{-- Segmen A: BPJS/Kronis --}}
            <div style="background:linear-gradient(145deg,rgba(63,207,142,.11),rgba(63,207,142,.03));border:1px solid rgba(63,207,142,.3);border-radius:.9rem;padding:1.1rem 1.2rem;">
                <div style="display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:.85rem;">
                    <div>
                        <div style="font-size:.57rem;text-transform:uppercase;letter-spacing:.1em;color:var(--emer2);font-weight:700;margin-bottom:.18rem;">Segmen A</div>
                        <div style="font-size:.84rem;font-weight:700;color:var(--ink);">BPJS / Obat Kronis</div>
                    </div>
                    <span style="font-size:.58rem;background:rgba(63,207,142,.13);color:var(--emer2);padding:.2rem .55rem;border-radius:.35rem;border:1px solid rgba(63,207,142,.28);white-space:nowrap;font-weight:700;">JKN · Non-Tunai</span>
                </div>
                <div class="font-mono" style="font-size:1.25rem;font-weight:800;color:var(--emer2);margin-bottom:.65rem;letter-spacing:-.01em;">Rp {{ number_format($r['pendBpjs'],0,',','.') }}</div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:.4rem;margin-bottom:.65rem;">
                    <div style="background:rgba(0,0,0,.2);border-radius:.5rem;padding:.45rem .6rem;">
                        <div style="font-size:.58rem;color:var(--mut);margin-bottom:.1rem;">HPP</div>
                        <div class="font-mono" style="font-size:.74rem;color:var(--mut2);">({{ number_format($r['hppBpjs'],0,',','.') }})</div>
                    </div>
                    <div style="background:rgba(63,207,142,.09);border-radius:.5rem;padding:.45rem .6rem;">
                        <div style="font-size:.58rem;color:var(--mut);margin-bottom:.1rem;">Laba</div>
                        <div class="font-mono" style="font-size:.74rem;font-weight:700;color:{{ $r['labaBpjs'] >= 0 ? 'var(--emer2)' : 'var(--red2)' }};">{{ $r['labaBpjs'] >= 0 ? '+' : '' }}{{ number_format($r['labaBpjs'],0,',','.') }}</div>
                    </div>
                </div>
                <div style="display:flex;align-items:center;gap:.5rem;">
                    <div style="flex:1;height:5px;background:rgba(0,0,0,.3);border-radius:3px;overflow:hidden;">
                        <div style="height:100%;width:{{ min(abs($r['marginBpjs']),100) }}%;background:linear-gradient(90deg,#3fcf8e,rgba(63,207,142,.6));border-radius:3px;"></div>
                    </div>
                    <span class="font-mono" style="font-size:.72rem;font-weight:800;color:var(--emer2);white-space:nowrap;">{{ $r['marginBpjs'] }}% margin</span>
                </div>
            </div>

            {{-- Segmen B: Tunai/Umum --}}
            <div style="background:linear-gradient(145deg,rgba(111,177,224,.11),rgba(111,177,224,.03));border:1px solid rgba(111,177,224,.3);border-radius:.9rem;padding:1.1rem 1.2rem;">
                <div style="display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:.85rem;">
                    <div>
                        <div style="font-size:.57rem;text-transform:uppercase;letter-spacing:.1em;color:var(--blue);font-weight:700;margin-bottom:.18rem;">Segmen B</div>
                        <div style="font-size:.84rem;font-weight:700;color:var(--ink);">Tunai / Pasien Umum</div>
                    </div>
                    <span style="font-size:.58rem;background:rgba(111,177,224,.13);color:var(--blue);padding:.2rem .55rem;border-radius:.35rem;border:1px solid rgba(111,177,224,.28);white-space:nowrap;font-weight:700;">Kas · Langsung</span>
                </div>
                @if($r['pendTunai'] == 0)
                <div style="text-align:center;padding:.75rem 0;color:var(--mut);font-size:.75rem;">Belum ada transaksi.<br><a href="{{ route('stok-keluar.index') }}" style="color:var(--blue);">→ Catat Stok Keluar</a></div>
                @else
                <div class="font-mono" style="font-size:1.25rem;font-weight:800;color:var(--blue);margin-bottom:.65rem;letter-spacing:-.01em;">Rp {{ number_format($r['pendTunai'],0,',','.') }}</div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:.4rem;margin-bottom:.65rem;">
                    <div style="background:rgba(0,0,0,.2);border-radius:.5rem;padding:.45rem .6rem;">
                        <div style="font-size:.58rem;color:var(--mut);margin-bottom:.1rem;">HPP</div>
                        <div class="font-mono" style="font-size:.74rem;color:var(--mut2);">({{ number_format($r['hppTunai'],0,',','.') }})</div>
                    </div>
                    <div style="background:rgba(111,177,224,.09);border-radius:.5rem;padding:.45rem .6rem;">
                        <div style="font-size:.58rem;color:var(--mut);margin-bottom:.1rem;">Laba</div>
                        <div class="font-mono" style="font-size:.74rem;font-weight:700;color:{{ $r['labaTunai'] >= 0 ? 'var(--blue)' : 'var(--red2)' }};">{{ $r['labaTunai'] >= 0 ? '+' : '' }}{{ number_format($r['labaTunai'],0,',','.') }}</div>
                    </div>
                </div>
                <div style="display:flex;align-items:center;gap:.5rem;">
                    <div style="flex:1;height:5px;background:rgba(0,0,0,.3);border-radius:3px;overflow:hidden;">
                        <div style="height:100%;width:{{ min(abs($r['marginTunai']),100) }}%;background:linear-gradient(90deg,#6fb1e0,rgba(111,177,224,.5));border-radius:3px;"></div>
                    </div>
                    <span class="font-mono" style="font-size:.72rem;font-weight:800;color:var(--blue);white-space:nowrap;">{{ $r['marginTunai'] }}% margin</span>
                </div>
                @endif
            </div>

            {{-- Pengadaan mini --}}
            <div class="glass-card" style="padding:.9rem 1.1rem;">
                <div style="font-size:.58rem;text-transform:uppercase;letter-spacing:.1em;color:var(--mut);font-weight:700;margin-bottom:.7rem;">Realisasi Pengadaan (PO)</div>
                <div style="display:flex;justify-content:space-between;align-items:baseline;margin-bottom:.5rem;">
                    <span style="font-size:.74rem;color:var(--ink);font-weight:600;">Total PO</span>
                    <span class="font-mono" style="font-size:.8rem;font-weight:700;color:var(--blue);">Rp {{ number_format($r['pengeluaran'],0,',','.') }}</span>
                </div>
                @if($r['pengeluaran'] > 0)
                <div style="height:6px;background:rgba(0,0,0,.25);border-radius:3px;overflow:hidden;display:flex;gap:1px;margin-bottom:.55rem;">
                    <div style="width:{{ round($r['pengeluaranBpjs']/$r['pengeluaran']*100,1) }}%;background:rgba(63,207,142,.65);border-radius:3px 0 0 3px;"></div>
                    <div style="flex:1;background:rgba(111,177,224,.55);border-radius:0 3px 3px 0;"></div>
                </div>
                @endif
                <div style="display:flex;flex-direction:column;gap:.3rem;">
                    <div style="display:flex;justify-content:space-between;">
                        <div style="display:flex;align-items:center;gap:.35rem;font-size:.7rem;color:var(--mut2);">
                            <div style="width:7px;height:7px;background:#3fcf8e;border-radius:1px;"></div>PO Kronis
                        </div>
                        <span class="font-mono" style="font-size:.7rem;color:var(--emer2);">{{ number_format($r['pengeluaranBpjs'],0,',','.') }}
                            @if($r['pengeluaran'] > 0)<span style="color:var(--mut);"> ({{ round($r['pengeluaranBpjs']/$r['pengeluaran']*100,0) }}%)</span>@endif
                        </span>
                    </div>
                    <div style="display:flex;justify-content:space-between;">
                        <div style="display:flex;align-items:center;gap:.35rem;font-size:.7rem;color:var(--mut2);">
                            <div style="width:7px;height:7px;background:#6fb1e0;border-radius:1px;"></div>PO Non-Kronis
                        </div>
                        <span class="font-mono" style="font-size:.7rem;color:var(--blue);">{{ number_format($r['pengeluaranUmum'],0,',','.') }}
                            @if($r['pengeluaran'] > 0)<span style="color:var(--mut);"> ({{ round($r['pengeluaranUmum']/$r['pengeluaran']*100,0) }}%)</span>@endif
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Charts --}}
    <div style="display:grid;grid-template-columns:2fr 1fr;gap:1rem;margin-bottom:1.25rem;">
        <div class="glass-card" style="padding:1.25rem;">
            <div style="font-size:.68rem;color:var(--mut);text-transform:uppercase;letter-spacing:.06em;margin-bottom:1rem;">
                Tren Pendapatan &amp; Pengeluaran — 6 Bulan Terakhir
            </div>
            <canvas id="chartTren" style="max-height:260px;"></canvas>
        </div>
        <div class="glass-card" style="padding:1.25rem;display:flex;flex-direction:column;">
            <div style="font-size:.68rem;color:var(--mut);text-transform:uppercase;letter-spacing:.06em;margin-bottom:1rem;">
                Komposisi Pendapatan
            </div>
            <canvas id="chartDonut" style="max-height:200px;margin:auto;"></canvas>
            <div style="margin-top:.75rem;display:flex;gap:1rem;justify-content:center;flex-wrap:wrap;">
                <div style="display:flex;align-items:center;gap:.35rem;font-size:.7rem;color:var(--mut);">
                    <div style="width:10px;height:10px;border-radius:2px;background:rgba(63,207,142,.75);"></div>
                    BPJS/Kronis ({{ $r['totalPend'] > 0 ? round($r['pendBpjs']/$r['totalPend']*100,1) : 0 }}%)
                </div>
                <div style="display:flex;align-items:center;gap:.35rem;font-size:.7rem;color:var(--mut);">
                    <div style="width:10px;height:10px;border-radius:2px;background:rgba(111,177,224,.75);"></div>
                    Tunai/Umum ({{ $r['totalPend'] > 0 ? round($r['pendTunai']/$r['totalPend']*100,1) : 0 }}%)
                </div>
            </div>
        </div>
    </div>

    {{-- Top Laba / Rugi --}}
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
        <div class="glass-card" style="overflow:hidden;">
            <div style="padding:.9rem 1.2rem;border-bottom:1px solid var(--line);display:flex;align-items:center;justify-content:space-between;">
                <div style="font-size:.68rem;color:var(--emer2);text-transform:uppercase;letter-spacing:.06em;font-weight:600;">Top 10 Obat Laba Tertinggi</div>
                <span style="font-size:.65rem;color:var(--mut);">per bulan</span>
            </div>
            <table class="data-table">
                <tbody>
                    @foreach($this->topLaba as $i => $o)
                    <tr>
                        <td style="color:var(--mut);width:24px;font-size:.73rem;">{{ $i+1 }}</td>
                        <td>
                            <div style="font-size:.8rem;">{{ $o->nama_obat }}</div>
                            <div style="font-size:.65rem;color:var(--mut);">{{ $o->tipe_obat === 'kronis' ? 'Kronis' : 'Non-Kronis' }}</div>
                        </td>
                        <td class="font-mono" style="text-align:right;font-size:.78rem;color:var(--emer2);">+{{ number_format($o->laba,0,',','.') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="glass-card" style="overflow:hidden;">
            <div style="padding:.9rem 1.2rem;border-bottom:1px solid var(--line);display:flex;align-items:center;justify-content:space-between;">
                <div style="font-size:.68rem;color:var(--red2);text-transform:uppercase;letter-spacing:.06em;font-weight:600;">Obat Rugi — Perlu Perhatian</div>
                <span style="font-size:.65rem;color:var(--mut);">perlu review harga</span>
            </div>
            @if($this->topRugi->isEmpty())
            <div style="padding:2rem;text-align:center;color:var(--emer2);font-size:.82rem;">
                <svg width="28" height="28" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" style="display:block;margin:0 auto .5rem;"><polyline points="20 6 9 17 4 12"/></svg>
                Tidak ada obat rugi bulan ini
            </div>
            @else
            <table class="data-table">
                <tbody>
                    @foreach($this->topRugi as $i => $o)
                    <tr>
                        <td style="color:var(--mut);width:24px;font-size:.73rem;">{{ $i+1 }}</td>
                        <td>
                            <div style="font-size:.8rem;">{{ $o->nama_obat }}</div>
                            <div style="font-size:.65rem;color:var(--mut);">{{ $o->tipe_obat === 'kronis' ? 'Kronis' : 'Non-Kronis' }}</div>
                        </td>
                        <td class="font-mono" style="text-align:right;font-size:.78rem;color:var(--red2);">{{ number_format($o->laba,0,',','.') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @endif
        </div>
    </div>

    @endif

    {{-- ===== TAB: OBAT KRONIS ===== --}}
    @if($activeTab === 'bpjs')

    {{-- Summary strip --}}
    @php
        $kTotalPend  = $this->detailBpjs->sum('pendapatan');
        $kTotalBiaya = $this->detailBpjs->sum('biaya');
        $kTotalLaba  = $this->detailBpjs->sum('laba');
        $kLaba       = $this->detailBpjs->filter(fn($x) => $x['laba'] > 0)->count();
        $kRugi       = $this->detailBpjs->filter(fn($x) => $x['laba'] < 0)->count();
    @endphp
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(155px,1fr));gap:.7rem;margin-bottom:1.25rem;">
        <div style="background:rgba(63,207,142,.07);border:1px solid rgba(63,207,142,.2);border-radius:.7rem;padding:.85rem 1rem;">
            <div style="font-size:.6rem;color:var(--mut);text-transform:uppercase;letter-spacing:.07em;margin-bottom:.25rem;">Proyeksi Pendapatan</div>
            <div class="font-mono" style="font-size:.95rem;font-weight:700;color:var(--emer2);">Rp {{ number_format($kTotalPend,0,',','.') }}</div>
        </div>
        <div style="background:rgba(232,100,90,.06);border:1px solid rgba(232,100,90,.15);border-radius:.7rem;padding:.85rem 1rem;">
            <div style="font-size:.6rem;color:var(--mut);text-transform:uppercase;letter-spacing:.07em;margin-bottom:.25rem;">Total Biaya Beli</div>
            <div class="font-mono" style="font-size:.95rem;font-weight:700;color:var(--mut2);">Rp {{ number_format($kTotalBiaya,0,',','.') }}</div>
        </div>
        <div style="background:rgba(217,164,65,.07);border:1px solid rgba(217,164,65,.2);border-radius:.7rem;padding:.85rem 1rem;">
            <div style="font-size:.6rem;color:var(--mut);text-transform:uppercase;letter-spacing:.07em;margin-bottom:.25rem;">Laba Kronis</div>
            <div class="font-mono" style="font-size:.95rem;font-weight:700;color:{{ $kTotalLaba >= 0 ? 'var(--gold2)' : 'var(--red2)' }};">
                {{ $kTotalLaba >= 0 ? '+' : '' }}Rp {{ number_format($kTotalLaba,0,',','.') }}
            </div>
        </div>
        <div style="background:rgba(255,255,255,.04);border:1px solid var(--line);border-radius:.7rem;padding:.85rem 1rem;">
            <div style="font-size:.6rem;color:var(--mut);text-transform:uppercase;letter-spacing:.07em;margin-bottom:.25rem;">Status Obat</div>
            <div style="font-size:.82rem;font-weight:600;">
                <span style="color:var(--emer2);">{{ $kLaba }} laba</span>
                <span style="color:var(--mut);margin:0 .3rem;">/</span>
                <span style="color:var(--red2);">{{ $kRugi }} rugi</span>
            </div>
            <div style="font-size:.63rem;color:var(--mut);margin-top:.15rem;">dari {{ $this->detailBpjs->count() }} obat aktif</div>
        </div>
    </div>

    <div class="glass-card" style="overflow-x:auto;">
        <div style="padding:.9rem 1.2rem;border-bottom:1px solid var(--line);display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:.5rem;">
            <div>
                <span style="font-size:.73rem;color:var(--emer2);text-transform:uppercase;letter-spacing:.06em;font-weight:600;">Obat Kronis — Analisis Laba BPJS</span>
                <span style="font-size:.68rem;color:var(--mut);margin-left:.75rem;">{{ $this->periode }}</span>
            </div>
            <div style="font-size:.68rem;color:var(--mut);background:rgba(63,207,142,.07);border:1px solid rgba(63,207,142,.15);padding:.2rem .6rem;border-radius:.4rem;">
                Formula: Bayar BPJS = Klaim/Unit × Faktor Jasa Farmasi (PMK 3/2023)
            </div>
        </div>
        <table class="data-table" style="font-size:.78rem;">
            <thead>
                <tr>
                    <th style="text-align:left;">Nama Obat</th>
                    <th>Diagnosis</th>
                    <th style="text-align:right;">Pasien</th>
                    <th style="text-align:right;">Unit/Bln</th>
                    <th style="text-align:right;">Klaim BPJS/Unit</th>
                    <th style="text-align:right;">Faktor JF</th>
                    <th style="text-align:right;color:var(--emer2);">Bayar BPJS/Unit</th>
                    <th style="text-align:right;">Harga Beli/Unit</th>
                    <th style="text-align:right;color:var(--emer2);">Pend./Bln</th>
                    <th style="text-align:right;color:var(--red2);">Biaya/Bln</th>
                    <th style="text-align:right;color:var(--gold2);">Laba/Bln</th>
                </tr>
            </thead>
            <tbody>
                @php $totalPend = 0; $totalBiaya = 0; $totalLaba = 0; @endphp
                @foreach($this->detailBpjs as $row)
                @php
                    $totalPend  += $row['pendapatan'];
                    $totalBiaya += $row['biaya'];
                    $totalLaba  += $row['laba'];
                @endphp
                <tr>
                    <td style="font-weight:600;">{{ $row['nama'] }}</td>
                    <td><span style="font-size:.68rem;background:var(--bg2);padding:.1rem .4rem;border-radius:.3rem;color:var(--mut2);">{{ $row['kategori'] }}</span></td>
                    <td class="font-mono" style="text-align:right;">{{ $row['pasien'] }}</td>
                    <td class="font-mono" style="text-align:right;">{{ $row['unit'] }}</td>
                    <td class="font-mono" style="text-align:right;font-size:.74rem;">{{ number_format($row['klaim'],0,',','.') }}</td>
                    <td class="font-mono" style="text-align:right;font-size:.74rem;color:var(--mut2);">{{ $row['faktor'] }}</td>
                    <td class="font-mono" style="text-align:right;font-size:.74rem;color:var(--emer2);">{{ number_format($row['bayar_bpjs'],0,',','.') }}</td>
                    <td class="font-mono" style="text-align:right;font-size:.74rem;color:var(--mut2);">{{ number_format($row['harga_beli'],0,',','.') }}</td>
                    <td class="font-mono" style="text-align:right;color:var(--emer2);">{{ number_format($row['pendapatan'],0,',','.') }}</td>
                    <td class="font-mono" style="text-align:right;color:var(--mut2);">{{ number_format($row['biaya'],0,',','.') }}</td>
                    <td class="font-mono" style="text-align:right;font-weight:700;color:{{ $row['laba'] >= 0 ? 'var(--gold2)' : 'var(--red2)' }};">
                        {{ $row['laba'] >= 0 ? '+' : '' }}{{ number_format($row['laba'],0,',','.') }}
                    </td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr style="border-top:2px solid var(--line);font-weight:700;background:rgba(0,0,0,.15);">
                    <td colspan="8" style="padding:.65rem .75rem;font-size:.78rem;color:var(--mut);">TOTAL — {{ $this->detailBpjs->count() }} obat kronis</td>
                    <td class="font-mono" style="text-align:right;color:var(--emer2);padding:.65rem .75rem;">{{ number_format($totalPend,0,',','.') }}</td>
                    <td class="font-mono" style="text-align:right;color:var(--mut2);padding:.65rem .75rem;">{{ number_format($totalBiaya,0,',','.') }}</td>
                    <td class="font-mono" style="text-align:right;padding:.65rem .75rem;color:{{ $totalLaba >= 0 ? 'var(--gold2)' : 'var(--red2)' }};">{{ $totalLaba >= 0 ? '+' : '' }}{{ number_format($totalLaba,0,',','.') }}</td>
                </tr>
            </tfoot>
        </table>
    </div>
    @endif

    {{-- ===== TAB: OBAT NON-KRONIS ===== --}}
    @if($activeTab === 'nonkronis')

    @if($this->detailNonKronis->isNotEmpty())
    @php
        $nkPend  = $this->detailNonKronis->sum('pendapatan');
        $nkBiaya = $this->detailNonKronis->sum('biaya');
        $nkLaba  = $this->detailNonKronis->sum('laba');
        $nkLabaItems = $this->detailNonKronis->filter(fn($x) => $x['laba'] > 0)->count();
        $nkRugiItems = $this->detailNonKronis->filter(fn($x) => $x['laba'] < 0)->count();
    @endphp
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(155px,1fr));gap:.7rem;margin-bottom:1.25rem;">
        <div style="background:rgba(63,207,142,.06);border:1px solid rgba(63,207,142,.15);border-radius:.7rem;padding:.85rem 1rem;">
            <div style="font-size:.6rem;color:var(--mut);text-transform:uppercase;letter-spacing:.07em;margin-bottom:.25rem;">Total Pendapatan</div>
            <div class="font-mono" style="font-size:.95rem;font-weight:700;color:var(--emer2);">Rp {{ number_format($nkPend,0,',','.') }}</div>
        </div>
        <div style="background:rgba(232,100,90,.06);border:1px solid rgba(232,100,90,.12);border-radius:.7rem;padding:.85rem 1rem;">
            <div style="font-size:.6rem;color:var(--mut);text-transform:uppercase;letter-spacing:.07em;margin-bottom:.25rem;">Total Biaya HPP</div>
            <div class="font-mono" style="font-size:.95rem;font-weight:700;color:var(--mut2);">Rp {{ number_format($nkBiaya,0,',','.') }}</div>
        </div>
        <div style="background:rgba(217,164,65,.06);border:1px solid rgba(217,164,65,.15);border-radius:.7rem;padding:.85rem 1rem;">
            <div style="font-size:.6rem;color:var(--mut);text-transform:uppercase;letter-spacing:.07em;margin-bottom:.25rem;">Laba Kotor</div>
            <div class="font-mono" style="font-size:.95rem;font-weight:700;color:{{ $nkLaba >= 0 ? 'var(--gold2)' : 'var(--red2)' }};">
                {{ $nkLaba >= 0 ? '+' : '' }}Rp {{ number_format($nkLaba,0,',','.') }}
            </div>
        </div>
        <div style="background:rgba(111,177,224,.06);border:1px solid rgba(111,177,224,.12);border-radius:.7rem;padding:.85rem 1rem;">
            <div style="font-size:.6rem;color:var(--mut);text-transform:uppercase;letter-spacing:.07em;margin-bottom:.25rem;">Transaksi</div>
            <div class="font-mono" style="font-size:.95rem;font-weight:700;color:var(--blue);">{{ $this->detailNonKronis->count() }}</div>
            <div style="font-size:.63rem;color:var(--mut);margin-top:.15rem;">
                <span style="color:var(--emer2);">{{ $nkLabaItems }} laba</span> /
                <span style="color:var(--red2);">{{ $nkRugiItems }} rugi</span>
            </div>
        </div>
    </div>
    @endif

    <div class="glass-card" style="overflow-x:auto;">
        <div style="padding:.9rem 1.2rem;border-bottom:1px solid var(--line);display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:.5rem;">
            <div>
                <span style="font-size:.73rem;color:var(--blue);text-transform:uppercase;letter-spacing:.06em;font-weight:600;">Obat Non-Kronis — Stok Keluar Aktual</span>
                <span style="font-size:.68rem;color:var(--mut);margin-left:.75rem;">{{ $this->periode }}</span>
            </div>
            <span style="font-size:.72rem;color:var(--mut);">{{ $this->detailNonKronis->count() }} transaksi</span>
        </div>
        @if($this->detailNonKronis->isEmpty())
        <div style="padding:3rem;text-align:center;color:var(--mut);">
            <svg width="36" height="36" fill="none" stroke="currentColor" stroke-width="1" viewBox="0 0 24 24" style="display:block;margin:0 auto .75rem;color:var(--line2);"><path d="M20 7H4a2 2 0 00-2 2v10a2 2 0 002 2h16a2 2 0 002-2V9a2 2 0 00-2-2z"/><path d="M16 21V5a2 2 0 00-2-2h-4a2 2 0 00-2 2v16"/></svg>
            Belum ada stok keluar obat non-kronis bulan ini.
            <div style="margin-top:.5rem;font-size:.75rem;">
                <a href="{{ route('stok-keluar.index') }}" style="color:var(--blue);text-decoration:none;">→ Catat Stok Keluar</a>
            </div>
        </div>
        @else
        <table class="data-table" style="font-size:.78rem;">
            <thead>
                <tr>
                    <th>Tanggal</th>
                    <th style="text-align:left;">Nama Obat</th>
                    <th style="text-align:right;">Jumlah</th>
                    <th style="text-align:right;">Harga Jual/Unit</th>
                    <th style="text-align:right;">HPP/Unit</th>
                    <th style="text-align:right;color:var(--emer2);">Pendapatan</th>
                    <th style="text-align:right;color:var(--red2);">Biaya HPP</th>
                    <th style="text-align:right;color:var(--gold2);">Laba</th>
                    <th>Keterangan</th>
                </tr>
            </thead>
            <tbody>
                @foreach($this->detailNonKronis as $row)
                <tr>
                    <td class="font-mono" style="font-size:.73rem;color:var(--mut2);">{{ $row['tanggal'] }}</td>
                    <td style="font-weight:600;">{{ $row['nama'] }}</td>
                    <td class="font-mono" style="text-align:right;">{{ $row['jumlah'] }} <span style="color:var(--mut);font-size:.68rem;">{{ $row['satuan'] }}</span></td>
                    <td class="font-mono" style="text-align:right;font-size:.74rem;">{{ number_format($row['harga_jual'],0,',','.') }}</td>
                    <td class="font-mono" style="text-align:right;font-size:.74rem;color:var(--mut2);">{{ number_format($row['harga_beli'],0,',','.') }}</td>
                    <td class="font-mono" style="text-align:right;color:var(--emer2);">{{ number_format($row['pendapatan'],0,',','.') }}</td>
                    <td class="font-mono" style="text-align:right;color:var(--mut2);">{{ number_format($row['biaya'],0,',','.') }}</td>
                    <td class="font-mono" style="text-align:right;font-weight:700;color:{{ $row['laba'] >= 0 ? 'var(--gold2)' : 'var(--red2)' }};">
                        {{ $row['laba'] >= 0 ? '+' : '' }}{{ number_format($row['laba'],0,',','.') }}
                    </td>
                    <td style="font-size:.72rem;color:var(--mut2);">{{ $row['keterangan'] ?? '—' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif
    </div>
    @endif

</div>

<script>
(function() {
    const trenEl   = document.getElementById('chartTren');
    const donutEl  = document.getElementById('chartDonut');
    if (!trenEl && !donutEl) return;

    const tren  = @json($this->tren);
    const donut = { kronis: {{ $r['pendBpjs'] }}, nonKronis: {{ $r['pendTunai'] }} };

    function initCharts() {
        if (typeof Chart === 'undefined') { setTimeout(initCharts, 200); return; }

        // Tren chart — 3 datasets: Kronis, Non-Kronis, Pengeluaran PO
        if (trenEl) {
            if (trenEl._chart) trenEl._chart.destroy();
            trenEl._chart = new Chart(trenEl, {
                type: 'bar',
                data: {
                    labels: tren.labels,
                    datasets: [
                        {
                            label: 'Pend. Obat Kronis',
                            data: tren.pendKronisData,
                            backgroundColor: 'rgba(63,207,142,.55)',
                            borderColor: '#3fcf8e',
                            borderWidth: 1.5,
                            borderRadius: 4,
                        },
                        {
                            label: 'Pend. Tunai (Pasien Umum)',
                            data: tren.pendTunaiData,
                            backgroundColor: 'rgba(111,177,224,.5)',
                            borderColor: '#6fb1e0',
                            borderWidth: 1.5,
                            borderRadius: 4,
                        },
                        {
                            label: 'Pengeluaran PO (Aktual)',
                            data: tren.pengeluaranData,
                            backgroundColor: 'rgba(232,100,90,.4)',
                            borderColor: '#e8645a',
                            borderWidth: 1.5,
                            borderRadius: 4,
                            type: 'line',
                            tension: 0.3,
                            pointRadius: 4,
                            fill: false,
                        },
                    ],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    plugins: {
                        legend: { labels: { color: '#8fae9f', font: { size: 11 } } },
                        tooltip: {
                            callbacks: {
                                label: ctx => ctx.dataset.label + ': Rp ' + Intl.NumberFormat('id').format(ctx.parsed.y),
                            }
                        }
                    },
                    scales: {
                        x: { ticks: { color: '#8fae9f', font: { size: 10 } }, grid: { color: 'rgba(31,61,48,.4)' } },
                        y: {
                            ticks: { color: '#8fae9f', font: { size: 10 }, callback: v => 'Rp ' + Intl.NumberFormat('id').format(v) },
                            grid: { color: 'rgba(31,61,48,.4)' },
                        },
                    },
                },
            });
        }

        // Donut komposisi
        if (donutEl) {
            if (donutEl._chart) donutEl._chart.destroy();
            donutEl._chart = new Chart(donutEl, {
                type: 'doughnut',
                data: {
                    labels: ['Obat Kronis', 'Obat Non-Kronis'],
                    datasets: [{
                        data: [donut.kronis, donut.nonKronis],
                        backgroundColor: ['rgba(63,207,142,.7)', 'rgba(111,177,224,.7)'],
                        borderColor: ['#3fcf8e', '#6fb1e0'],
                        borderWidth: 2,
                        hoverOffset: 6,
                    }],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    cutout: '65%',
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                label: ctx => ctx.label + ': Rp ' + Intl.NumberFormat('id').format(ctx.parsed),
                            }
                        }
                    },
                },
            });
        }
    }

    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', initCharts);
    else initCharts();
})();
</script>
