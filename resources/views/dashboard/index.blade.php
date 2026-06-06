<x-app-layout>
    <x-slot name="title">Dashboard</x-slot>

    {{-- ALERT BANNER --}}
    @php $a = $alerts ?? []; @endphp
    @if(($a['rugi'] ?? 0) > 0 || ($a['stok_habis'] ?? 0) > 0 || ($a['stok_kritis'] ?? 0) > 0 || ($a['kadaluarsa'] ?? 0) > 0)
    <div style="display:flex;gap:.75rem;flex-wrap:wrap;margin-bottom:1.5rem;">
        @if(($a['rugi'] ?? 0) > 0)
        <a href="{{ route('katalog.index') }}" style="background:rgba(232,100,90,.1);border:1px solid rgba(232,100,90,.3);border-radius:.7rem;padding:.7rem 1.1rem;display:flex;align-items:center;gap:.5rem;text-decoration:none;">
            <svg width="14" height="14" fill="none" stroke="var(--red2)" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
            <span style="font-size:.78rem;color:var(--red2);"><strong>{{ $a['rugi'] }}</strong> obat rugi</span>
        </a>
        @endif
        @if(($a['stok_habis'] ?? 0) > 0)
        <a href="{{ route('stok.index') }}" style="background:rgba(232,100,90,.08);border:1px solid rgba(232,100,90,.2);border-radius:.7rem;padding:.7rem 1.1rem;display:flex;align-items:center;gap:.5rem;text-decoration:none;">
            <svg width="14" height="14" fill="none" stroke="var(--red2)" stroke-width="2" viewBox="0 0 24 24"><path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
            <span style="font-size:.78rem;color:var(--red2);"><strong>{{ $a['stok_habis'] }}</strong> stok habis</span>
        </a>
        @endif
        @if(($a['stok_kritis'] ?? 0) > 0)
        <a href="{{ route('stok.index') }}" style="background:rgba(217,164,65,.1);border:1px solid rgba(217,164,65,.25);border-radius:.7rem;padding:.7rem 1.1rem;display:flex;align-items:center;gap:.5rem;text-decoration:none;">
            <svg width="14" height="14" fill="none" stroke="var(--gold2)" stroke-width="2" viewBox="0 0 24 24"><path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>
            <span style="font-size:.78rem;color:var(--gold2);"><strong>{{ $a['stok_kritis'] }}</strong> stok kritis</span>
        </a>
        @endif
        @if(($a['kadaluarsa'] ?? 0) > 0)
        <a href="{{ route('stok.index') }}" style="background:rgba(111,177,224,.08);border:1px solid rgba(111,177,224,.2);border-radius:.7rem;padding:.7rem 1.1rem;display:flex;align-items:center;gap:.5rem;text-decoration:none;">
            <svg width="14" height="14" fill="none" stroke="var(--blue)" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
            <span style="font-size:.78rem;color:var(--blue);"><strong>{{ $a['kadaluarsa'] }}</strong> segera kadaluarsa</span>
        </a>
        @endif
    </div>
    @endif

    {{-- KPI CARDS --}}
    <div style="display:grid; grid-template-columns:repeat(auto-fit,minmax(220px,1fr)); gap:1rem; margin-bottom:2rem;">

        <div class="kpi-card">
            <div class="font-label" style="font-size:.68rem; color:var(--mut); margin-bottom:.5rem;">Laba Kotor Obat/Bln</div>
            <div class="font-mono" style="font-size:1.5rem; font-weight:700; color:{{ $laba_kotor >= 0 ? 'var(--emer2)' : 'var(--red2)' }};">
                {{ $laba_kotor >= 0 ? '+' : '' }}Rp {{ number_format($laba_kotor,0,',','.') }}
            </div>
            <div style="margin-top:.4rem; font-size:.75rem; color:var(--mut);">
                Margin: <span style="color:var(--gold2);">{{ $pendapatan_bpjs > 0 ? number_format(($laba_kotor/$pendapatan_bpjs)*100,1) : 0 }}%</span>
            </div>
        </div>

        <div class="kpi-card">
            <div class="font-label" style="font-size:.68rem; color:var(--mut); margin-bottom:.5rem;">Pendapatan BPJS</div>
            <div class="font-mono" style="font-size:1.5rem; font-weight:700; color:var(--ink);">
                Rp {{ number_format($pendapatan_bpjs,0,',','.') }}
            </div>
            <div style="margin-top:.4rem; font-size:.75rem; color:var(--mut);">Klaim + jasa farmasi PMK 3/2023</div>
        </div>

        <div class="kpi-card">
            <div class="font-label" style="font-size:.68rem; color:var(--mut); margin-bottom:.5rem;">Biaya Beli Obat</div>
            <div class="font-mono" style="font-size:1.5rem; font-weight:700; color:var(--ink);">
                Rp {{ number_format($biaya_beli,0,',','.') }}
            </div>
            <div style="margin-top:.4rem; font-size:.75rem; color:var(--mut);">Berdasarkan harga PO/REAL/EST</div>
        </div>

        <div class="kpi-card">
            <div class="font-label" style="font-size:.68rem; color:var(--mut); margin-bottom:.5rem;">Total Pasien Kronis</div>
            <div class="font-mono" style="font-size:1.5rem; font-weight:700; color:var(--ink);">
                {{ number_format($total_pasien,0,',','.') }}
            </div>
            <div style="margin-top:.4rem; font-size:.75rem; color:var(--mut);">{{ $jumlah_obat_aktif }} jenis obat aktif</div>
        </div>

        <div class="kpi-card">
            <div class="font-label" style="font-size:.68rem; color:var(--mut); margin-bottom:.5rem;">Pengeluaran Bulan Ini</div>
            <div class="font-mono" style="font-size:1.5rem; font-weight:700; color:var(--ink);">
                Rp {{ number_format($pengeluaran_bulan_ini,0,',','.') }}
            </div>
            <div style="margin-top:.4rem; font-size:.75rem; color:var(--mut);">{{ $jumlah_po_bulan_ini }} PO bulan ini</div>
        </div>

    </div>

    {{-- RENCANA PENGAMBILAN OBAT --}}
    <livewire:rencana-ambil-obat />

    {{-- CHARTS ROW --}}
    <div style="display:grid; grid-template-columns:1fr 380px; gap:1.5rem; margin-bottom:2rem;">

        {{-- Bar Chart: Ranking Obat --}}
        <div class="glass-card" style="padding:1.5rem;">
            <div style="margin-bottom:1.2rem;">
                <div class="font-label" style="font-size:.7rem; color:var(--mut); margin-bottom:.2rem;">Kontributor Laba</div>
                <div class="font-heading" style="font-size:1.1rem; color:var(--ink);">Ranking Laba per Obat</div>
            </div>
            <canvas id="chartRanking" style="max-height:380px;"></canvas>
        </div>

        {{-- Donut Chart: per Diagnosis --}}
        <div class="glass-card" style="padding:1.5rem;">
            <div style="margin-bottom:1.2rem;">
                <div class="font-label" style="font-size:.7rem; color:var(--mut); margin-bottom:.2rem;">Distribusi</div>
                <div class="font-heading" style="font-size:1.1rem; color:var(--ink);">Pendapatan per Diagnosis</div>
            </div>
            <div style="position:relative; height:220px; margin-bottom:1rem;">
                <canvas id="chartDiagnosis"></canvas>
                <div style="position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);text-align:center;pointer-events:none;">
                    <div style="font-size:.65rem; color:var(--mut); text-transform:uppercase; letter-spacing:.06em;">Total/bln</div>
                    <div class="font-mono" style="font-size:.9rem; color:var(--ink); font-weight:700;">
                        Rp {{ number_format($pendapatan_bpjs/1000000,1,',','.') }}jt
                    </div>
                </div>
            </div>
            <div id="diagnosisLegend" style="display:flex; flex-direction:column; gap:.4rem;"></div>
        </div>

    </div>

    {{-- TREND CHART 6 BULAN --}}
    <div class="glass-card" style="padding:1.5rem;margin-bottom:2rem;">
        <div style="margin-bottom:1.2rem;">
            <div class="font-label" style="font-size:.7rem;color:var(--mut);margin-bottom:.2rem;">Histori</div>
            <div class="font-heading" style="font-size:1.1rem;color:var(--ink);">Tren Keuangan 6 Bulan Terakhir</div>
        </div>
        <canvas id="chartTren6" style="max-height:250px;"></canvas>
    </div>

    {{-- PROYEKSI LABA BERSIH --}}
    <livewire:proyeksi-slider :laba-kotor="$laba_kotor" />

    @push('scripts')
    <script>
        (function() {
            // Ranking Bar Chart
            const rankingData = @json(array_slice($ranking_obat, 0, 15));
            const labels = rankingData.map(d => d.nama);
            const values = rankingData.map(d => d.laba);
            const colors = rankingData.map(d => d.laba >= 0 ? 'rgba(63,207,142,.7)' : 'rgba(232,100,90,.7)');
            const borderColors = rankingData.map(d => d.laba >= 0 ? '#3fcf8e' : '#e8645a');

            new Chart(document.getElementById('chartRanking'), {
                type: 'bar',
                data: {
                    labels,
                    datasets: [{ data: values, backgroundColor: colors, borderColor: borderColors, borderWidth: 1, borderRadius: 4 }]
                },
                options: {
                    indexAxis: 'y', responsive: true, maintainAspectRatio: false,
                    plugins: { legend: { display: false }, tooltip: { callbacks: { label: ctx => ' Rp ' + new Intl.NumberFormat('id-ID').format(ctx.raw) } } },
                    scales: {
                        x: { grid: { color: 'rgba(31,61,48,.5)' }, ticks: { color: '#8fae9f', font: { size: 11 }, callback: v => 'Rp ' + Intl.NumberFormat('id-ID',{notation:'compact'}).format(v) } },
                        y: { grid: { display: false }, ticks: { color: '#eaf3ee', font: { size: 11 } } }
                    }
                }
            });

            // Donut Chart
            const diagData = @json($by_diagnosis);
            const diagLabels = Object.keys(diagData);
            const diagValues = Object.values(diagData);
            const palette = ['#3fcf8e','#6fb1e0','#d9a441','#e8645a','#a78bfa','#fb923c','#34d399','#f472b6','#94a3b8'];

            new Chart(document.getElementById('chartDiagnosis'), {
                type: 'doughnut',
                data: { labels: diagLabels, datasets: [{ data: diagValues, backgroundColor: palette.map(c => c + '99'), borderColor: palette, borderWidth: 1.5, hoverOffset: 6 }] },
                options: { cutout: '68%', responsive: true, maintainAspectRatio: false,
                    plugins: { legend: { display: false }, tooltip: { callbacks: { label: ctx => ' Rp ' + new Intl.NumberFormat('id-ID').format(ctx.raw) } } } }
            });

            // Legend
            const total = diagValues.reduce((a, b) => a + b, 0);
            const legend = document.getElementById('diagnosisLegend');
            diagLabels.forEach((label, i) => {
                const pct = total > 0 ? ((diagValues[i] / total) * 100).toFixed(1) : 0;
                const row = document.createElement('div');
                row.style.cssText = 'display:flex;align-items:center;justify-content:space-between;font-size:.75rem;';
                row.innerHTML = '<span style="display:flex;align-items:center;gap:.4rem;color:#8fae9f;"><span style="width:8px;height:8px;border-radius:50%;background:'+palette[i]+';flex-shrink:0;"></span>'+label+'</span><span style="color:#eaf3ee;font-weight:600;">'+pct+'%</span>';
                legend.appendChild(row);
            });

            // Tren 6 Bulan Chart
            new Chart(document.getElementById('chartTren6'), {
                type: 'bar',
                data: {
                    labels: @json($tren_labels),
                    datasets: [
                        { label: 'Proyeksi Pendapatan', data: @json($tren_pendapatan), backgroundColor: 'rgba(63,207,142,.55)', borderColor: '#3fcf8e', borderWidth: 1.5, borderRadius: 4 },
                        { label: 'Pengeluaran PO', data: @json($tren_pengeluaran), backgroundColor: 'rgba(232,100,90,.45)', borderColor: '#e8645a', borderWidth: 1.5, borderRadius: 4 },
                    ]
                },
                options: {
                    responsive: true, maintainAspectRatio: false,
                    plugins: { legend: { labels: { color: '#8fae9f', font: { size: 11 } } }, tooltip: { callbacks: { label: ctx => ' '+ctx.dataset.label+': Rp '+new Intl.NumberFormat('id-ID').format(ctx.raw) } } },
                    scales: {
                        x: { ticks: { color: '#8fae9f', font: { size: 10 } }, grid: { color: 'rgba(31,61,48,.4)' } },
                        y: { ticks: { color: '#8fae9f', font: { size: 10 }, callback: v => 'Rp '+Intl.NumberFormat('id').format(v) }, grid: { color: 'rgba(31,61,48,.4)' } }
                    }
                }
            });
        })();
    </script>
    @endpush
</x-app-layout>
