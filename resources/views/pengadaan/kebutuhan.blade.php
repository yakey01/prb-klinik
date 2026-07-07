<x-app-layout>
    <x-slot name="title">Kebutuhan Obat Kronis</x-slot>
    @livewire('kebutuhan-obat-kronis')
    @push('scripts')
    <script>
    function kebutuhanCharts() {
        return {
            data: window.__kcInitData || {},
            charts: {},
            init() {
                this.$nextTick(() => this.drawAll(this.data));
            },
            refresh(newData) {
                if (!newData) return;
                this.data = newData;
                this.drawAll(newData);
            },
            drawAll(data) {
                ['bar','donut'].forEach(k => { try { this.charts[k]?.destroy(); } catch(e){} delete this.charts[k]; });
                this.drawBar(data);
                this.drawDonut(data);
            },
            drawBar(data) {
                const el = this.$el.querySelector('[data-chart="topobat"]');
                if (!el || !data.top10Labels?.length) return;
                this.charts.bar = new Chart(el, {
                    type: 'bar',
                    data: {
                        labels: data.top10Labels,
                        datasets: [
                            {
                                label: 'Kebutuhan (unit/bln)',
                                data: data.top10Units,
                                backgroundColor: 'rgba(63,207,142,0.22)',
                                borderColor: 'rgba(63,207,142,0.75)',
                                borderWidth: 1.5,
                                borderRadius: 3,
                                barPercentage: 0.55,
                            },
                            {
                                label: 'Stok Aktual',
                                data: data.top10Stok,
                                backgroundColor: 'rgba(111,177,224,0.18)',
                                borderColor: 'rgba(111,177,224,0.6)',
                                borderWidth: 1.5,
                                borderRadius: 3,
                                barPercentage: 0.55,
                            }
                        ]
                    },
                    options: {
                        indexAxis: 'y',
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                labels: {
                                    color: '#8fae9f',
                                    font: { size: 11, family: "'Inter', system-ui, sans-serif" },
                                    padding: 14,
                                    usePointStyle: true,
                                    pointStyleWidth: 10,
                                }
                            },
                            tooltip: {
                                backgroundColor: '#0e1e17',
                                borderColor: '#1f3d30',
                                borderWidth: 1,
                                titleColor: '#eaf3ee',
                                bodyColor: '#8fae9f',
                                padding: 10,
                                callbacks: {
                                    label(ctx) { return `  ${ctx.dataset.label}: ${ctx.parsed.x.toLocaleString('id-ID')} unit`; }
                                }
                            }
                        },
                        scales: {
                            x: {
                                grid: { color: 'rgba(31,61,48,0.5)', drawBorder: false },
                                ticks: { color: '#8fae9f', font: { size: 10 } }
                            },
                            y: {
                                grid: { display: false },
                                ticks: {
                                    color: '#eaf3ee',
                                    font: { size: 10.5, family: "'Inter', system-ui, sans-serif" },
                                    callback(val) {
                                        const lbl = this.getLabelForValue(val);
                                        return lbl.length > 18 ? lbl.slice(0,17) + '…' : lbl;
                                    }
                                }
                            }
                        }
                    }
                });
            },
            drawDonut(data) {
                const el = this.$el.querySelector('[data-chart="status"]');
                if (!el) return;
                const total = (data.statusData || []).reduce((a,b) => a+b, 0);
                this.charts.donut = new Chart(el, {
                    type: 'doughnut',
                    data: {
                        labels: ['Aman', 'Perhatian', 'Hampir Habis', 'Kritis'],
                        datasets: [{
                            data: data.statusData || [0,0,0,0],
                            backgroundColor: [
                                'rgba(63,207,142,0.6)',
                                'rgba(217,164,65,0.6)',
                                'rgba(255,140,0,0.6)',
                                'rgba(232,100,90,0.6)',
                            ],
                            borderColor: ['#3fcf8e', '#d9a441', '#ff8c00', '#e8645a'],
                            borderWidth: 1.5,
                            hoverOffset: 5,
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        cutout: '68%',
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: {
                                    color: '#8fae9f',
                                    padding: 8,
                                    font: { size: 10.5, family: "'Inter', system-ui, sans-serif" },
                                    usePointStyle: true,
                                    pointStyleWidth: 7,
                                }
                            },
                            tooltip: {
                                backgroundColor: '#0e1e17',
                                borderColor: '#1f3d30',
                                borderWidth: 1,
                                titleColor: '#eaf3ee',
                                bodyColor: '#8fae9f',
                                padding: 10,
                                callbacks: {
                                    label(ctx) {
                                        const pct = total > 0 ? Math.round(ctx.parsed / total * 100) : 0;
                                        return `  ${ctx.label}: ${ctx.parsed} jenis (${pct}%)`;
                                    }
                                }
                            }
                        }
                    }
                });
            }
        };
    }
    </script>
    @endpush
</x-app-layout>
