<?php
namespace App\Livewire;

use App\Models\Diagnosis;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Component;

class KebutuhanObatKronis extends Component
{
    public string $filterDiagnosis = '';
    public string $filterStatus    = '';
    public string $search          = '';
    public int    $horizon         = 3;

    #[Computed]
    public function diagnosisList()
    {
        $db = Diagnosis::where('is_active', true)->orderBy('sort_order')->pluck('nama');
        return $db->isNotEmpty() ? $db
            : collect(['Diabetes Melitus','Hipertensi','Jantung Koroner','PPOK','Epilepsi','Stroke','Lupus','Skizofrenia','Kanker']);
    }

    #[Computed]
    public function kebutuhanKronis()
    {
        $rows = DB::table('resep_pasien as rp')
            ->join('obat as o', 'rp.obat_id', '=', 'o.id')
            ->join('pasien as p', 'rp.pasien_id', '=', 'p.id')
            ->where('rp.is_aktif', true)
            ->where('p.is_aktif', true)
            ->where('o.is_active', true)
            ->when($this->filterDiagnosis, fn($q) => $q->where('o.kategori_diagnosis', $this->filterDiagnosis))
            ->when($this->search, fn($q) => $q->where('o.nama_obat', 'like', "%{$this->search}%"))
            ->select(
                'o.id', 'o.nama_obat', 'o.kategori_diagnosis', 'o.satuan',
                'o.stok_aktual', 'o.stok_minimum', 'o.harga_beli_per_unit',
                DB::raw('COUNT(DISTINCT rp.pasien_id) AS jumlah_pasien'),
                DB::raw('SUM(rp.jumlah_default) AS unit_per_bulan'),
            )
            ->groupBy('o.id', 'o.nama_obat', 'o.kategori_diagnosis', 'o.satuan',
                      'o.stok_aktual', 'o.stok_minimum', 'o.harga_beli_per_unit')
            ->get()
            ->map(fn($r) => $this->enrichRow($r));

        if ($this->filterStatus) {
            $rows = $rows->filter(fn($r) => $r->status === $this->filterStatus);
        }

        return $rows->sortBy(fn($r) =>
            ['habis'=>0,'kritis'=>1,'hampir_habis'=>2,'perhatian'=>3,'aman'=>4][$r->status] * 100000
            + min($r->hari_tersisa, 99999)
        )->values();
    }

    private function enrichRow(object $row): object
    {
        $unitPerHari  = $row->unit_per_bulan > 0 ? $row->unit_per_bulan / 30 : 0;
        $hariTersisa  = $unitPerHari > 0 ? max(0, (int) floor($row->stok_aktual / $unitPerHari)) : 9999;
        $bulanTersisa = $row->unit_per_bulan > 0 ? max(0, round($row->stok_aktual / $row->unit_per_bulan, 1)) : 99;

        $status = match(true) {
            $row->stok_aktual <= 0 => 'habis',
            $hariTersisa < 7       => 'kritis',
            $hariTersisa < 30      => 'hampir_habis',
            $hariTersisa < 60      => 'perhatian',
            default                => 'aman',
        };

        $rekoPengadaan  = max(0, (int) ceil($row->unit_per_bulan * $this->horizon * 1.1 - $row->stok_aktual));
        $targetStok3Bln = $row->unit_per_bulan * 3;
        $persenStok     = $targetStok3Bln > 0
            ? min(100, (int) round($row->stok_aktual / $targetStok3Bln * 100))
            : 100;

        return (object) [
            'id'                  => $row->id,
            'nama_obat'           => $row->nama_obat,
            'kategori_diagnosis'  => $row->kategori_diagnosis ?? '—',
            'satuan'              => $row->satuan ?? 'tablet',
            'stok_aktual'         => (int) $row->stok_aktual,
            'stok_minimum'        => (int) $row->stok_minimum,
            'harga_beli_per_unit' => (float) $row->harga_beli_per_unit,
            'jumlah_pasien'       => (int) $row->jumlah_pasien,
            'unit_per_bulan'      => (int) $row->unit_per_bulan,
            'unit_per_hari'       => round($unitPerHari, 1),
            'hari_tersisa'        => $hariTersisa,
            'bulan_tersisa'       => $bulanTersisa,
            'habis_tanggal'       => $hariTersisa < 9999
                ? now()->addDays($hariTersisa)->isoFormat('D MMM YYYY')
                : null,
            'status'              => $status,
            'reko_pengadaan'      => $rekoPengadaan,
            'nilai_per_bulan'     => (float) ($row->unit_per_bulan * $row->harga_beli_per_unit),
            'nilai_reko'          => (float) ($rekoPengadaan * $row->harga_beli_per_unit),
            'persen_stok'         => $persenStok,
        ];
    }

    #[Computed]
    public function kebutuhanNonKronis()
    {
        $tigaBulanLalu = now()->subMonths(3)->format('Y-m-d');

        return DB::table('stok_keluar as sk')
            ->join('obat as o', 'sk.obat_id', '=', 'o.id')
            ->where('sk.tanggal_keluar', '>=', $tigaBulanLalu)
            ->where('o.is_active', true)
            ->when($this->search, fn($q) => $q->where('o.nama_obat', 'like', "%{$this->search}%"))
            ->select(
                'o.id', 'o.nama_obat', 'o.kategori_diagnosis', 'o.satuan',
                'o.stok_aktual', 'o.stok_minimum', 'o.harga_beli_per_unit',
                DB::raw('SUM(sk.jumlah_unit) AS total_3bulan'),
                DB::raw('ROUND(SUM(sk.jumlah_unit) / 3.0, 0) AS rata_per_bulan'),
                DB::raw('COUNT(sk.id) AS frekuensi'),
                DB::raw('MAX(sk.tanggal_keluar) AS terakhir_keluar'),
            )
            ->groupBy('o.id', 'o.nama_obat', 'o.kategori_diagnosis', 'o.satuan',
                      'o.stok_aktual', 'o.stok_minimum', 'o.harga_beli_per_unit')
            ->orderByDesc('total_3bulan')
            ->get()
            ->map(function($row) {
                $unitPerHari  = $row->rata_per_bulan > 0 ? $row->rata_per_bulan / 30 : 0;
                $hariTersisa  = $unitPerHari > 0 ? (int) floor($row->stok_aktual / $unitPerHari) : 9999;
                $status = match(true) {
                    $row->stok_aktual <= 0 => 'habis',
                    $hariTersisa < 7       => 'kritis',
                    $hariTersisa < 30      => 'hampir_habis',
                    $hariTersisa < 60      => 'perhatian',
                    default                => 'aman',
                };
                $rekoPengadaan  = max(0, (int) ceil($row->rata_per_bulan * $this->horizon * 1.1 - $row->stok_aktual));
                $targetStok3Bln = $row->rata_per_bulan * 3;
                $persenStok     = $targetStok3Bln > 0
                    ? min(100, (int) round($row->stok_aktual / $targetStok3Bln * 100))
                    : 100;

                return (object) [
                    'id'                  => $row->id,
                    'nama_obat'           => $row->nama_obat,
                    'kategori_diagnosis'  => $row->kategori_diagnosis ?? 'Umum',
                    'satuan'              => $row->satuan ?? 'tablet',
                    'stok_aktual'         => (int) $row->stok_aktual,
                    'stok_minimum'        => (int) $row->stok_minimum,
                    'harga_beli_per_unit' => (float) $row->harga_beli_per_unit,
                    'total_3bulan'        => (int) $row->total_3bulan,
                    'rata_per_bulan'      => (int) $row->rata_per_bulan,
                    'frekuensi'           => (int) $row->frekuensi,
                    'terakhir_keluar'     => $row->terakhir_keluar
                        ? \Carbon\Carbon::parse($row->terakhir_keluar)->isoFormat('D MMM YYYY')
                        : '—',
                    'hari_tersisa'        => $hariTersisa,
                    'habis_tanggal'       => $hariTersisa < 9999
                        ? now()->addDays($hariTersisa)->isoFormat('D MMM YYYY')
                        : null,
                    'status'             => $status,
                    'reko_pengadaan'     => $rekoPengadaan,
                    'nilai_per_bulan'    => (float) ($row->rata_per_bulan * $row->harga_beli_per_unit),
                    'nilai_reko'         => (float) ($rekoPengadaan * $row->harga_beli_per_unit),
                    'persen_stok'        => $persenStok,
                ];
            });
    }

    #[Computed]
    public function statsKronis(): array
    {
        $d = $this->kebutuhanKronis;
        return [
            'total_jenis_obat'   => $d->count(),
            'total_pasien'       => DB::table('pasien')->where('is_aktif', true)->count(),
            'total_unit_bulan'   => $d->sum('unit_per_bulan'),
            'kritis_count'       => $d->whereIn('status', ['habis','kritis'])->count(),
            'hampir_habis_count' => $d->where('status', 'hampir_habis')->count(),
            'perhatian_count'    => $d->where('status', 'perhatian')->count(),
            'aman_count'         => $d->where('status', 'aman')->count(),
            'nilai_bulan'        => $d->sum('nilai_per_bulan'),
            'nilai_reko'         => $d->sum('nilai_reko'),
        ];
    }

    #[Computed]
    public function chartData(): array
    {
        $d   = $this->kebutuhanKronis;
        $top = $d->sortByDesc('unit_per_bulan')->take(10)->values();

        return [
            'top10Labels' => $top->pluck('nama_obat')->toArray(),
            'top10Units'  => $top->pluck('unit_per_bulan')->toArray(),
            'top10Stok'   => $top->pluck('stok_aktual')->toArray(),
            'statusData'  => [
                $d->where('status', 'aman')->count(),
                $d->where('status', 'perhatian')->count(),
                $d->where('status', 'hampir_habis')->count(),
                $d->whereIn('status', ['kritis','habis'])->count(),
            ],
        ];
    }

    public function updatedFilterDiagnosis(): void { $this->pushCharts(); }
    public function updatedSearch(): void           { $this->pushCharts(); }
    public function updatedFilterStatus(): void     { $this->pushCharts(); }
    public function updatedHorizon(): void          { $this->pushCharts(); }

    private function pushCharts(): void
    {
        $this->dispatch('charts-refresh', data: $this->chartData);
    }

    public function render()
    {
        return view('livewire.kebutuhan-obat-kronis', [
            'statsKronis'        => $this->statsKronis,
            'chartData'          => $this->chartData,
            'kebutuhanKronis'    => $this->kebutuhanKronis,
            'kebutuhanNonKronis' => $this->kebutuhanNonKronis,
            'diagnosisList'      => $this->diagnosisList,
        ]);
    }
}
