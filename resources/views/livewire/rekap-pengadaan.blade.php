<div>
    @php
        $rp = fn ($n) => 'Rp ' . number_format($n, 0, ',', '.');
        $s = $this->ringkasan;
        $rekap = $this->rekap;
    @endphp

    {{-- HEADER --}}
    <div style="display:flex;flex-wrap:wrap;gap:1rem;align-items:flex-end;justify-content:space-between;margin-bottom:1.4rem;">
        <div>
            <div class="font-label" style="font-size:.7rem;color:var(--mut);margin-bottom:.25rem;">Laporan</div>
            <h2 class="font-heading" style="font-size:1.5rem;color:var(--ink);margin:0;">Rekap Pengadaan Obat Kronis</h2>
            <p style="color:var(--mut);font-size:.78rem;margin-top:.3rem;max-width:660px;">
                Margin <strong style="color:var(--emer2);">PEMBELIAN</strong> obat kronis (PRB/BPJS) —
                <strong style="color:var(--ink);">nilai klaim BPJS − nilai beli</strong>, dihitung <strong style="color:var(--ink);">saat barang masuk (proyeksi)</strong>. Riwayat per bulan untuk direksi.
            </p>
        </div>
        <div style="display:flex;gap:.5rem;align-items:center;">
            <select wire:model.live="tahun" style="padding:.5rem .8rem;border-radius:.6rem;background:var(--card);border:1px solid var(--line2);color:var(--ink);font-size:.85rem;font-weight:700;cursor:pointer;">
                @foreach($this->tahunTersedia as $th)
                    <option value="{{ $th }}">Tahun {{ $th }}</option>
                @endforeach
            </select>
            <button type="button" onclick="window.print()" class="btn-outline" style="padding:.5rem .9rem;font-size:.8rem;">🖨️ Cetak</button>
        </div>
    </div>

    {{-- BANNER PEMBEDA — cegah bingung 2 angka laba kronis --}}
    <div style="display:flex;gap:.7rem;align-items:flex-start;margin-bottom:1.2rem;padding:.75rem 1rem;border-radius:.7rem;background:rgba(96,165,250,.08);border:1px solid rgba(96,165,250,.28);">
        <span style="font-size:1rem;line-height:1;">ℹ️</span>
        <div style="font-size:.74rem;color:var(--mut);line-height:1.55;">
            Halaman ini = <strong style="color:#7cc4ff;">margin PEMBELIAN</strong> (barang yang <u>dibeli</u> per bulan · proyeksi saat beli).
            Untuk <strong style="color:var(--ink);">laba PROGRAM</strong> (dari obat yang benar-benar <u>dipakai pasien</u>, termasuk obat yang beli&gt;klaim/rugi),
            buka <a wire:navigate.hover href="{{ route('laporan.index') }}" style="color:var(--gold2);font-weight:700;text-decoration:underline;">Laporan Bulanan → Obat Kronis</a>. Dua angka ini <strong style="color:var(--ink);">wajar berbeda</strong> — beda yang diukur.
        </div>
    </div>

    {{-- KPI TAHUN --}}
    <div class="grid-kpi" style="display:grid;grid-template-columns:repeat(4,1fr);gap:1rem;margin-bottom:1.4rem;">
        <div class="kpi-card">
            <div class="font-label" style="font-size:.62rem;color:var(--mut);">Nilai Beli {{ $tahun }}</div>
            <div class="font-heading" style="font-size:1.5rem;color:var(--ink);margin-top:.3rem;font-variant-numeric:tabular-nums;">{{ $rp($s['beli']) }}</div>
            <div style="font-size:.66rem;color:var(--mut);margin-top:.25rem;">modal beli obat kronis</div>
        </div>
        <div class="kpi-card">
            <div class="font-label" style="font-size:.62rem;color:var(--mut);">Klaim BPJS {{ $tahun }}</div>
            <div class="font-heading" style="font-size:1.5rem;color:#7cc4ff;margin-top:.3rem;font-variant-numeric:tabular-nums;">{{ $rp($s['klaim']) }}</div>
            <div style="font-size:.66rem;color:var(--mut);margin-top:.25rem;">klaim + jasa farmasi</div>
        </div>
        <div class="kpi-card" style="border-color:{{ $s['untung'] >= 0 ? 'rgba(63,207,142,.4)' : 'rgba(232,100,90,.4)' }};background:linear-gradient(135deg,{{ $s['untung'] >= 0 ? 'rgba(63,207,142,.12)' : 'rgba(232,100,90,.12)' }},var(--panel));">
            <div class="font-label" style="font-size:.62rem;color:var(--mut);">Untung Pembelian {{ $tahun }} <span style="font-weight:400;text-transform:none;">(proyeksi)</span></div>
            <div class="font-heading" style="font-size:1.7rem;color:{{ $s['untung'] >= 0 ? 'var(--emer2)' : 'var(--red2)' }};margin-top:.3rem;font-variant-numeric:tabular-nums;">{{ $s['untung'] >= 0 ? '+' : '' }}{{ $rp($s['untung']) }}</div>
            <div style="font-size:.66rem;color:var(--mut);margin-top:.25rem;">rata-rata {{ $rp($s['rata_untung']) }}/bulan</div>
        </div>
        <div class="kpi-card">
            <div class="font-label" style="font-size:.62rem;color:var(--mut);">Margin</div>
            <div class="font-heading" style="font-size:1.5rem;color:var(--gold2);margin-top:.3rem;font-variant-numeric:tabular-nums;">{{ number_format($s['margin'], 1, ',', '.') }}%</div>
            <div style="font-size:.66rem;color:var(--mut);margin-top:.25rem;">untung ÷ klaim · {{ $s['bulan_ada'] }} bulan aktif</div>
        </div>
    </div>

    {{-- GRAFIK TREN --}}
    <div class="glass-card" style="padding:1.1rem 1.2rem;margin-bottom:1.4rem;">
        <div class="font-label" style="font-size:.7rem;color:var(--mut);margin-bottom:.7rem;">📈 Tren Untung Kronis per Bulan — {{ $tahun }}</div>
        <div wire:key="chart-{{ $tahun }}" wire:ignore x-data="rekapChart(@js($this->chartData))" style="position:relative;height:220px;">
            <canvas x-ref="c"></canvas>
        </div>
    </div>

    {{-- TABEL RIWAYAT BULANAN --}}
    <div class="glass-card" style="padding:0;overflow:hidden;">
        <div style="padding:.9rem 1.1rem;border-bottom:1px solid var(--line);">
            <div class="font-heading" style="font-size:.95rem;color:var(--ink);">Riwayat Bulanan {{ $tahun }}</div>
        </div>
        <div style="overflow-x:auto;">
        <table style="width:100%;border-collapse:collapse;min-width:640px;font-variant-numeric:tabular-nums;">
            <thead>
                <tr style="border-bottom:1px solid var(--line);">
                    <th style="text-align:left;padding:.6rem 1.1rem;font-size:.62rem;text-transform:uppercase;color:var(--mut);font-weight:600;">Bulan</th>
                    <th style="text-align:center;padding:.6rem .5rem;font-size:.62rem;text-transform:uppercase;color:var(--mut);font-weight:600;">PO</th>
                    <th style="text-align:right;padding:.6rem .5rem;font-size:.62rem;text-transform:uppercase;color:var(--mut);font-weight:600;">Nilai Beli</th>
                    <th style="text-align:right;padding:.6rem .5rem;font-size:.62rem;text-transform:uppercase;color:var(--mut);font-weight:600;">Klaim BPJS</th>
                    <th style="text-align:right;padding:.6rem .5rem;font-size:.62rem;text-transform:uppercase;color:var(--mut);font-weight:600;">Untung</th>
                    <th style="text-align:right;padding:.6rem 1.1rem;font-size:.62rem;text-transform:uppercase;color:var(--mut);font-weight:600;">Margin</th>
                </tr>
            </thead>
            <tbody>
                @forelse($rekap as $idx => $row)
                    @php $prev = $rekap[$idx - 1]['kronis_untung'] ?? null; $naik = $prev !== null ? $row['kronis_untung'] - $prev : null; @endphp
                    <tr style="border-bottom:1px solid var(--line);">
                        <td style="padding:.65rem 1.1rem;font-size:.82rem;color:var(--ink);font-weight:600;">{{ $row['label'] }}</td>
                        <td style="text-align:center;padding:.65rem .5rem;font-size:.8rem;color:var(--mut);">{{ $row['po'] }}</td>
                        <td style="text-align:right;padding:.65rem .5rem;font-size:.82rem;color:var(--ink);">{{ $rp($row['kronis_beli']) }}</td>
                        <td style="text-align:right;padding:.65rem .5rem;font-size:.82rem;color:#7cc4ff;">{{ $rp($row['kronis_klaim']) }}</td>
                        <td style="text-align:right;padding:.65rem .5rem;font-size:.85rem;font-weight:700;color:{{ $row['kronis_untung'] >= 0 ? 'var(--emer2)' : 'var(--red2)' }};">
                            {{ $row['kronis_untung'] >= 0 ? '+' : '' }}{{ $rp($row['kronis_untung']) }}
                            @if($naik !== null)<span style="font-size:.6rem;color:{{ $naik >= 0 ? 'var(--emer)' : 'var(--red2)' }};margin-left:.3rem;">{{ $naik >= 0 ? '▲' : '▼' }}</span>@endif
                        </td>
                        <td style="text-align:right;padding:.65rem 1.1rem;font-size:.82rem;color:var(--gold2);">{{ number_format($row['kronis_margin'], 1, ',', '.') }}%</td>
                    </tr>
                @empty
                    <tr><td colspan="6" style="padding:2rem;text-align:center;color:var(--mut);font-size:.85rem;">Belum ada pengadaan di tahun {{ $tahun }}.</td></tr>
                @endforelse
            </tbody>
            @if($rekap)
            <tfoot>
                <tr style="border-top:2px solid var(--line2);background:rgba(255,255,255,.02);">
                    <td style="padding:.75rem 1.1rem;font-size:.82rem;font-weight:800;color:var(--ink);">TOTAL {{ $tahun }}</td>
                    <td></td>
                    <td style="text-align:right;padding:.75rem .5rem;font-size:.82rem;font-weight:700;color:var(--ink);">{{ $rp($s['beli']) }}</td>
                    <td style="text-align:right;padding:.75rem .5rem;font-size:.82rem;font-weight:700;color:#7cc4ff;">{{ $rp($s['klaim']) }}</td>
                    <td style="text-align:right;padding:.75rem .5rem;font-size:.88rem;font-weight:800;color:{{ $s['untung'] >= 0 ? 'var(--emer2)' : 'var(--red2)' }};">{{ $s['untung'] >= 0 ? '+' : '' }}{{ $rp($s['untung']) }}</td>
                    <td style="text-align:right;padding:.75rem 1.1rem;font-size:.82rem;font-weight:700;color:var(--gold2);">{{ number_format($s['margin'], 1, ',', '.') }}%</td>
                </tr>
            </tfoot>
            @endif
        </table>
        </div>
    </div>

    {{-- CATATAN NON-KRONIS + DASAR HITUNG --}}
    <div style="margin-top:1rem;padding:.85rem 1.1rem;border-radius:.7rem;background:rgba(217,164,65,.06);border:1px solid rgba(217,164,65,.2);font-size:.72rem;color:var(--mut);line-height:1.55;">
        <strong style="color:var(--gold2);">Catatan.</strong>
        Angka di atas <strong style="color:var(--ink);">khusus obat kronis</strong> (bisnis PRB/BPJS): untung = klaim BPJS (termasuk jasa farmasi) − nilai beli, dihitung saat barang masuk (per tanggal PO).
        Obat <strong style="color:var(--ink);">non-kronis</strong> memakai harga jual (proyeksi retail, <u>bukan</u> klaim BPJS) sehingga <strong>dipisah</strong> agar tak mengaburkan margin BPJS.
        @if($rekap)
            @php $totNon = array_sum(array_column($rekap, 'non_untung')); $totNonBeli = array_sum(array_column($rekap, 'non_beli')); @endphp
            @if($totNonBeli > 0)
                Non-kronis {{ $tahun }}: beli {{ $rp($totNonBeli) }}, proyeksi untung {{ $rp($totNon) }}.
            @endif
        @endif
    </div>

    @push('scripts')
    <script>
        function rekapChart(data) {
            return {
                chart: null,
                init() {
                    const draw = () => {
                        if (typeof Chart === 'undefined') { setTimeout(draw, 150); return; }
                        const ctx = this.$refs.c;
                        if (!ctx) return;
                        if (this.chart) { this.chart.destroy(); }
                        const vals = data.untung || [];
                        this.chart = new Chart(ctx, {
                            type: 'bar',
                            data: {
                                labels: data.labels || [],
                                datasets: [{
                                    data: vals,
                                    backgroundColor: vals.map(v => v >= 0 ? 'rgba(63,207,142,.55)' : 'rgba(232,100,90,.55)'),
                                    borderColor: vals.map(v => v >= 0 ? '#3fcf8e' : '#e8645a'),
                                    borderWidth: 1.5, borderRadius: 5, borderSkipped: false,
                                }],
                            },
                            options: {
                                responsive: true, maintainAspectRatio: false,
                                plugins: {
                                    legend: { display: false },
                                    tooltip: { callbacks: { label: c => 'Untung: Rp ' + Number(c.raw).toLocaleString('id-ID') } },
                                },
                                scales: {
                                    x: { grid: { display: false }, ticks: { color: '#8fae9f', font: { size: 11 } } },
                                    y: { grid: { color: 'rgba(255,255,255,.05)' }, ticks: { color: '#8fae9f', font: { size: 10 }, callback: v => 'Rp ' + (v/1e6).toLocaleString('id-ID') + 'jt' } },
                                },
                            },
                        });
                    };
                    draw();
                },
            };
        }
    </script>
    @endpush
</div>
