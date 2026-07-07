<div>
    {{-- ── PAGE HEADER ─────────────────────────────────────────────────────── --}}
    <div style="display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:1.5rem;flex-wrap:wrap;gap:1rem;">
        <div>
            <div style="display:flex;align-items:center;gap:.6rem;margin-bottom:.35rem;">
                <h2 class="font-heading" style="font-size:1.15rem;color:var(--ink);margin:0;">Obat Keluar — Ledger Stok</h2>
                <span style="background:rgba(63,207,142,.1);border:1px solid rgba(63,207,142,.22);color:var(--emer2);font-size:.62rem;font-weight:700;padding:.15rem .5rem;border-radius:2rem;letter-spacing:.06em;text-transform:uppercase;">PRB Kronis</span>
                <span style="background:rgba(167,139,250,.12);border:1px solid rgba(167,139,250,.25);color:#a78bfa;font-size:.62rem;font-weight:700;padding:.15rem .5rem;border-radius:2rem;letter-spacing:.06em;text-transform:uppercase;">RME</span>
            </div>
            <p style="font-size:.73rem;color:var(--mut);margin:0;">Semua obat keluar dari 2 channel — <strong style="color:var(--emer2);">PRB/Kronis</strong> (diserahkan langsung di apotik) &amp; <strong style="color:#a78bfa;">RME</strong> (resep dari SIM). Keduanya mengurangi stok total yang sama.</p>
        </div>
        <div style="display:flex;gap:.6rem;align-items:center;flex-wrap:wrap;">
            <input wire:model.live="search" type="text" placeholder="Cari obat / pasien..." class="form-input" style="max-width:210px;font-size:.8rem;">
            <input wire:model.live="filterBulan" type="month" class="form-input font-mono" style="max-width:150px;font-size:.8rem;">
        </div>
    </div>

    {{-- ── KPI SUMMARY CARDS ────────────────────────────────────────────────── --}}
    @php $s = $this->summary; @endphp
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(155px,1fr));gap:.75rem;margin-bottom:1.5rem;">
        <div style="background:linear-gradient(135deg,rgba(63,207,142,.08),rgba(63,207,142,.03));border:1px solid rgba(63,207,142,.18);border-radius:.85rem;padding:1rem 1.1rem;position:relative;overflow:hidden;">
            <div style="position:absolute;top:-.4rem;right:-.4rem;font-size:2.5rem;opacity:.06;"><svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" style="display:inline-block;vertical-align:middle"><line x1="12" y1="19" x2="12" y2="5"/><polyline points="5 12 12 5 19 12"/></svg></div>
            <div style="font-size:.6rem;color:var(--mut);text-transform:uppercase;letter-spacing:.08em;margin-bottom:.4rem;font-weight:600;">Pendapatan</div>
            <div class="font-mono" style="font-size:.95rem;font-weight:700;color:var(--emer2);">Rp {{ number_format($s['total_pendapatan'],0,',','.') }}</div>
            <div style="font-size:.65rem;color:var(--mut);margin-top:.2rem;">{{ number_format($s['jumlah_transaksi'],0,',','.') }} transaksi keluar</div>
        </div>
        <div style="background:linear-gradient(135deg,rgba(217,164,65,.08),rgba(217,164,65,.03));border:1px solid rgba(217,164,65,.16);border-radius:.85rem;padding:1rem 1.1rem;position:relative;overflow:hidden;">
            <div style="position:absolute;top:-.4rem;right:-.4rem;font-size:2.5rem;opacity:.06;">=</div>
            <div style="font-size:.6rem;color:var(--mut);text-transform:uppercase;letter-spacing:.08em;margin-bottom:.4rem;font-weight:600;">Laba Kotor</div>
            <div class="font-mono" style="font-size:.95rem;font-weight:700;color:{{ $s['total_laba'] >= 0 ? 'var(--gold2)' : 'var(--red2)' }};">
                {{ $s['total_laba'] >= 0 ? '+' : '' }}Rp {{ number_format($s['total_laba'],0,',','.') }}
            </div>
            @if($s['total_pendapatan'] > 0)
            <div style="font-size:.65rem;color:var(--mut);margin-top:.2rem;">margin {{ round(($s['total_laba'] / $s['total_pendapatan']) * 100, 1) }}%</div>
            @endif
        </div>
        <div style="background:linear-gradient(135deg,rgba(63,207,142,.07),rgba(63,207,142,.02));border:1px solid rgba(63,207,142,.16);border-radius:.85rem;padding:1rem 1.1rem;position:relative;overflow:hidden;">
            <div style="position:absolute;top:-.4rem;right:-.4rem;font-size:2.5rem;opacity:.06;">⚕</div>
            <div style="font-size:.6rem;color:var(--mut);text-transform:uppercase;letter-spacing:.08em;margin-bottom:.4rem;font-weight:600;">Keluar via PRB/Kronis</div>
            <div class="font-mono" style="font-size:.95rem;font-weight:700;color:var(--emer2);">{{ number_format($s['prb_item'],0,',','.') }} <span style="font-size:.6rem;color:var(--mut);">item</span></div>
            <div style="font-size:.65rem;color:var(--mut);margin-top:.2rem;">diserahkan langsung di apotik</div>
        </div>
        <div style="background:linear-gradient(135deg,rgba(167,139,250,.1),rgba(167,139,250,.03));border:1px solid rgba(167,139,250,.2);border-radius:.85rem;padding:1rem 1.1rem;position:relative;overflow:hidden;">
            <div style="position:absolute;top:-.4rem;right:-.4rem;font-size:2.5rem;opacity:.06;">🔗</div>
            <div style="font-size:.6rem;color:var(--mut);text-transform:uppercase;letter-spacing:.08em;margin-bottom:.4rem;font-weight:600;">Keluar via RME</div>
            <div class="font-mono" style="font-size:.95rem;font-weight:700;color:#a78bfa;">{{ number_format($s['rme_item'],0,',','.') }} <span style="font-size:.6rem;color:var(--mut);">item</span></div>
            <div style="font-size:.65rem;color:var(--mut);margin-top:.2rem;">resep dari SIM</div>
        </div>
    </div>

    {{-- ── TAB BAR ──────────────────────────────────────────────────────────── --}}
    @php $counts = $this->tabCounts; @endphp
    <div style="display:flex;gap:0;border-bottom:2px solid rgba(255,255,255,.07);margin-bottom:1.25rem;">
        <button wire:click="setTab('semua')"
            style="padding:.65rem 1.4rem;border:none;border-bottom:2px solid {{ $activeTab==='semua' ? 'var(--gold2)' : 'transparent' }};margin-bottom:-2px;background:transparent;cursor:pointer;display:flex;align-items:center;gap:.5rem;transition:all .18s;color:{{ $activeTab==='semua' ? 'var(--gold2)' : 'var(--mut)' }};">
            <span style="font-size:.8rem;font-weight:{{ $activeTab==='semua' ? '700' : '500' }};">SEMUA</span>
            <span style="background:{{ $activeTab==='semua' ? 'rgba(217,164,65,.15)' : 'rgba(255,255,255,.05)' }};color:{{ $activeTab==='semua' ? 'var(--gold2)' : 'var(--mut)' }};font-size:.6rem;font-weight:700;padding:.1rem .45rem;border-radius:2rem;font-family:monospace;">{{ $counts['semua'] }}</span>
            <span style="font-size:.62rem;color:var(--mut);font-weight:400;">gabungan</span>
        </button>
        <button wire:click="setTab('prb')"
            style="padding:.65rem 1.4rem;border:none;border-bottom:2px solid {{ $activeTab==='prb' ? 'var(--emer2)' : 'transparent' }};margin-bottom:-2px;background:transparent;cursor:pointer;display:flex;align-items:center;gap:.5rem;transition:all .18s;color:{{ $activeTab==='prb' ? 'var(--emer2)' : 'var(--mut)' }};">
            <span style="font-size:.8rem;font-weight:{{ $activeTab==='prb' ? '700' : '500' }};">PRB / KRONIS</span>
            <span style="background:{{ $activeTab==='prb' ? 'rgba(63,207,142,.15)' : 'rgba(255,255,255,.05)' }};color:{{ $activeTab==='prb' ? 'var(--emer2)' : 'var(--mut)' }};font-size:.6rem;font-weight:700;padding:.1rem .45rem;border-radius:2rem;font-family:monospace;">{{ $counts['prb'] }}</span>
            <span style="font-size:.62rem;color:var(--mut);font-weight:400;">langsung</span>
        </button>
        <button wire:click="setTab('rme')"
            style="padding:.65rem 1.4rem;border:none;border-bottom:2px solid {{ $activeTab==='rme' ? '#a78bfa' : 'transparent' }};margin-bottom:-2px;background:transparent;cursor:pointer;display:flex;align-items:center;gap:.5rem;transition:all .18s;color:{{ $activeTab==='rme' ? '#a78bfa' : 'var(--mut)' }};">
            <span style="font-size:.8rem;font-weight:{{ $activeTab==='rme' ? '700' : '500' }};">RESEP RME</span>
            <span style="background:{{ $activeTab==='rme' ? 'rgba(167,139,250,.18)' : 'rgba(255,255,255,.05)' }};color:{{ $activeTab==='rme' ? '#a78bfa' : 'var(--mut)' }};font-size:.6rem;font-weight:700;padding:.1rem .45rem;border-radius:2rem;font-family:monospace;">{{ $counts['rme'] }}</span>
            <span style="font-size:.62rem;color:var(--mut);font-weight:400;">SIM</span>
        </button>
    </div>

    {{-- ── INFO BANNER ──────────────────────────────────────────────────────── --}}
    <div style="background:linear-gradient(135deg,rgba(217,164,65,.07),rgba(167,139,250,.05));border:1px solid rgba(255,255,255,.1);border-radius:.75rem;padding:.8rem 1.1rem;margin-bottom:1.1rem;display:flex;align-items:flex-start;gap:.75rem;">
        <div style="font-size:1rem;line-height:1;margin-top:.05rem;opacity:.8;">📒</div>
        <div style="font-size:.72rem;color:var(--mut);line-height:1.5;">
            Obat yang sama (mis. <strong style="color:var(--mut2);">Amlodipine</strong>) bisa keluar lewat <strong style="color:var(--emer2);">PRB/Kronis</strong> maupun <strong style="color:#a78bfa;">RME</strong> — keduanya tercatat &amp; sama-sama mengurangi stok total apotik secara real-time. Data <em>read-only</em>.
        </div>
    </div>

    {{-- ── TABEL LEDGER ─────────────────────────────────────────────────────── --}}
    <div class="glass-card" style="overflow-x:auto;">
        <table class="data-table">
            <thead>
                <tr>
                    <th style="min-width:95px;">Tanggal</th>
                    <th style="min-width:170px;">Nama Obat</th>
                    <th style="min-width:155px;">Pasien</th>
                    <th style="text-align:center;min-width:95px;">Sumber</th>
                    <th style="text-align:right;min-width:75px;">Jumlah</th>
                    <th style="text-align:right;min-width:105px;">Total</th>
                    <th style="text-align:center;min-width:150px;">Stok: Sebelum <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" style="display:inline-block;vertical-align:middle"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg> Sesudah</th>
                </tr>
            </thead>
            <tbody>
                @forelse($this->records as $sk)
                @php
                    $isPrb = $sk->sumber === 'pengambilan';
                    if ($isPrb) {
                        $namaPasien = $sk->pasien->nama ?? '—';
                        $subInfo = $sk->pasien->no_bpjs ?? 'BPJS —';
                    } else {
                        $ket = $sk->keterangan ?? '';
                        preg_match('/Resep SIM \(RME\):\s*(.*?)(?:\s*\[|\s*ref:|$)/i', $ket, $mNama);
                        $namaPasien = trim($mNama[1] ?? '') ?: '—';
                        preg_match('/ref:\s*(\S+)/i', $ket, $mRef);
                        $subInfo = $mRef[1] ?? ($sk->ref ?? 'RME');
                    }
                    $total = $sk->jumlah_unit * $sk->harga_jual_per_unit;
                @endphp
                <tr>
                    <td class="font-mono" style="font-size:.77rem;color:var(--mut2);">{{ $sk->tanggal_keluar->format('d/m/Y') }}</td>
                    <td>
                        <div style="font-size:.83rem;font-weight:600;color:var(--ink);">{{ $sk->obat->nama_obat ?? '—' }}</div>
                        <div style="font-size:.65rem;color:var(--mut);">{{ $sk->obat->kategori_diagnosis ?? '' }}</div>
                    </td>
                    <td>
                        <div style="font-size:.82rem;font-weight:600;color:var(--ink);">{{ \Illuminate\Support\Str::title(strtolower($namaPasien)) }}</div>
                        <div class="font-mono" style="font-size:.62rem;color:var(--mut);">{{ $subInfo }}</div>
                    </td>
                    <td style="text-align:center;">
                        @if($isPrb)
                        <span style="font-size:.62rem;font-weight:700;color:var(--emer2);background:rgba(63,207,142,.12);border:1px solid rgba(63,207,142,.25);border-radius:2rem;padding:.15rem .55rem;white-space:nowrap;">PRB / Kronis</span>
                        @else
                        <span style="font-size:.62rem;font-weight:700;color:#a78bfa;background:rgba(167,139,250,.12);border:1px solid rgba(167,139,250,.25);border-radius:2rem;padding:.15rem .55rem;white-space:nowrap;">RME</span>
                        @endif
                    </td>
                    <td class="font-mono" style="text-align:right;font-size:.82rem;">
                        {{ number_format($sk->jumlah_unit,0,',','.') }}
                        <span style="color:var(--mut);font-size:.7rem;">{{ $sk->satuan }}</span>
                    </td>
                    <td class="font-mono" style="text-align:right;font-weight:600;color:var(--emer2);">Rp {{ number_format($total,0,',','.') }}</td>
                    <td class="font-mono" style="text-align:center;font-size:.78rem;white-space:nowrap;">
                        @if(!is_null($sk->stok_sebelum))
                            <span style="color:var(--mut2);">{{ number_format($sk->stok_sebelum,0,',','.') }}</span>
                            <span style="color:var(--gold2);font-weight:700;"> <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" style="display:inline-block;vertical-align:middle"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg> </span>
                            <span style="color:var(--gold2);font-weight:700;">{{ number_format($sk->stok_sesudah,0,',','.') }}</span>
                            <span style="color:var(--red2);font-size:.66rem;"> (−{{ number_format($sk->jumlah_unit,0,',','.') }})</span>
                        @else
                            <span style="color:var(--mut);font-size:.7rem;">kini {{ number_format($sk->obat->stok_aktual ?? 0,0,',','.') }}</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" style="text-align:center;padding:3rem 1rem;">
                        <div style="font-size:2.5rem;margin-bottom:.6rem;opacity:.3;">📒</div>
                        <div style="font-size:.85rem;color:var(--mut);font-weight:600;margin-bottom:.3rem;">Belum ada obat keluar bulan ini</div>
                        <div style="font-size:.72rem;color:var(--mut);">
                            Pengambilan PRB/kronis di apotik &amp; resep dari SIM akan muncul otomatis di sini.
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($this->records->isNotEmpty())
    <div style="margin-top:.6rem;font-size:.72rem;color:var(--mut);display:flex;align-items:center;gap:.6rem;flex-wrap:wrap;">
        <span><span style="width:.5rem;height:.5rem;border-radius:50%;background:var(--emer2);display:inline-block;margin-right:.3rem;"></span>PRB/Kronis {{ number_format($s['prb_item'],0,',','.') }} item</span>
        <span><span style="width:.5rem;height:.5rem;border-radius:50%;background:#a78bfa;display:inline-block;margin-right:.3rem;"></span>RME {{ number_format($s['rme_item'],0,',','.') }} item</span>
        <span style="color:var(--mut2);">· Total {{ number_format($s['total_item'],0,',','.') }} item keluar</span>
        @if($filterBulan)<span style="color:var(--mut2);">· {{ \Carbon\Carbon::createFromFormat('Y-m',$filterBulan)->translatedFormat('F Y') }}</span>@endif
    </div>
    @endif
</div>
