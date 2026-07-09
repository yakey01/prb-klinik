<?php

namespace App\Http\Controllers;

use App\Services\LabaCalculatorService;
use App\Models\Obat;
use App\Models\Pasien;
use App\Models\PurchaseOrder;
use App\Models\RekonsiliasiiBpjs;
use App\Support\Periode;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function __construct(private LabaCalculatorService $calc) {}

    /**
     * Rincian finansial PER OBAT untuk pengambilan dengan status tertentu pada bulan/tahun.
     *
     * Tabel pembukti kartu KPI — penjumlahan baris di sini = nilai kartu (rekonsiliasi).
     * Sumber nilai per pengambilan:
     *  1. item_pengambilan (snapshot harga saat penyerahan) — paling akurat;
     *  2. fallback: resep aktif pasien × harga katalog obat — saat item belum tercatat.
     *
     * @return array<int, array{obat_id:int, nama:string, tipe:string, status:string, source:string, qty:float, hpp:float, klaim:float, laba:float}>
     */
    private function financialBreakdown(array $statuses, int $bulan, int $tahun): array
    {
        // Sumber 1 — dari item_pengambilan (pengambilan yang sudah punya rincian item)
        $items = DB::table('item_pengambilan as ip')
            ->join('pengambilan_obat as po', 'ip.pengambilan_obat_id', '=', 'po.id')
            ->join('obat as o', 'ip.obat_id', '=', 'o.id')
            ->whereNull('po.deleted_at')
            ->whereIn('po.status', $statuses)
            ->whereBetween('po.tanggal_pengambilan', Periode::bulan($tahun, $bulan))
            ->groupBy('o.id', 'o.nama_obat', 'o.tipe_obat', 'po.status')
            ->selectRaw('
                o.id AS obat_id, o.nama_obat AS nama, o.tipe_obat AS tipe, po.status AS status,
                SUM(ip.jumlah_unit) AS qty,
                SUM(ip.jumlah_unit * COALESCE(ip.harga_beli_snapshot, 0)) AS hpp,
                SUM(CASE WHEN o.tipe_obat = "kronis"
                    THEN ip.jumlah_unit * COALESCE(ip.harga_klaim_bpjs_snapshot, 0) * ' . \App\Models\Obat::jfSql('ip.faktor_jasa_farmasi_snapshot') . '
                    ELSE 0 END) AS klaim
            ')
            ->get()
            ->map(fn ($r) => (array) $r + ['source' => 'item']);

        // Sumber 2 — fallback dari resep aktif untuk pengambilan TANPA item
        $resep = DB::table('pengambilan_obat as po')
            ->join('resep_pasien as rp', function ($j) {
                $j->on('rp.pasien_id', '=', 'po.pasien_id')->where('rp.is_aktif', 1);
            })
            ->join('obat as o', 'o.id', '=', 'rp.obat_id')
            ->whereNull('po.deleted_at')
            ->whereIn('po.status', $statuses)
            ->whereBetween('po.tanggal_pengambilan', Periode::bulan($tahun, $bulan))
            ->whereNotExists(function ($q) {
                $q->select(DB::raw(1))->from('item_pengambilan as ip')
                  ->whereColumn('ip.pengambilan_obat_id', 'po.id');
            })
            ->groupBy('o.id', 'o.nama_obat', 'o.tipe_obat', 'po.status')
            ->selectRaw('
                o.id AS obat_id, o.nama_obat AS nama, o.tipe_obat AS tipe, po.status AS status,
                SUM(rp.jumlah_default) AS qty,
                SUM(rp.jumlah_default * COALESCE(o.harga_beli_per_unit, 0)) AS hpp,
                SUM(CASE WHEN o.tipe_obat = "kronis"
                    THEN rp.jumlah_default * COALESCE(o.klaim_bpjs_per_unit, 0) * ' . \App\Models\Obat::jfSql('o.faktor_jasa_farmasi') . '
                    ELSE 0 END) AS klaim
            ')
            ->get()
            ->map(fn ($r) => (array) $r + ['source' => 'resep']);

        // Gabung + merge per (obat, status) agar 1 obat = 1 baris per status
        $merged = [];
        foreach ($items->concat($resep) as $row) {
            $key = $row['obat_id'] . '|' . $row['status'];
            if (!isset($merged[$key])) {
                $merged[$key] = [
                    'obat_id' => (int) $row['obat_id'],
                    'nama'    => $row['nama'],
                    'tipe'    => $row['tipe'] ?: 'non_kronis',
                    'status'  => $row['status'],
                    'source'  => $row['source'],
                    'qty'     => 0.0, 'hpp' => 0.0, 'klaim' => 0.0,
                ];
            }
            $merged[$key]['qty']   += (float) $row['qty'];
            $merged[$key]['hpp']   += (float) $row['hpp'];
            $merged[$key]['klaim'] += (float) $row['klaim'];
        }

        $rows = array_values($merged);
        foreach ($rows as &$r) {
            $r['laba'] = $r['klaim'] - $r['hpp'];
        }
        unset($r);

        // Urut: laba menaik (penyumbang rugi terbesar di atas — membuktikan angka kartu)
        usort($rows, fn ($a, $b) => $a['laba'] <=> $b['laba']);

        return $rows;
    }

    /**
     * Total HPP & klaim untuk status tertentu — dijumlahkan dari rincian per obat
     * agar kartu KPI dan tabel rincian SELALU rekonsiliasi.
     *
     * @return array{hpp: float, klaim: float}
     */
    private function financialsForStatuses(array $statuses, int $bulan, int $tahun): array
    {
        $rows = $this->financialBreakdown($statuses, $bulan, $tahun);
        return [
            'hpp'   => array_sum(array_column($rows, 'hpp')),
            'klaim' => array_sum(array_column($rows, 'klaim')),
        ];
    }

    public function index()
    {
        $bulan = now()->month;
        $tahun = now()->year;

        // ── Finansial bulan ini ────────────────────────────────────
        // Realisasi = pengambilan 'selesai'; Proyeksi = pengambilan 'dijadwalkan'.
        // Tiap pengambilan dinilai dari item_pengambilan (snapshot harga saat serah)
        // bila ada; bila item kosong, fallback ke resep aktif pasien × harga katalog.
        // Rincian per obat (pembukti kartu) — sekali query, lalu dipecah per status
        $breakdownRows = $this->financialBreakdown(['selesai', 'dijadwalkan'], $bulan, $tahun);

        $sumBy = function (array $rows, string $col) {
            return array_sum(array_column($rows, $col));
        };
        $realRows = array_values(array_filter($breakdownRows, fn ($r) => $r['status'] === 'selesai'));
        $projRows = array_values(array_filter($breakdownRows, fn ($r) => $r['status'] === 'dijadwalkan'));

        $hppRealisasi   = $sumBy($realRows, 'hpp');
        $klaimRealisasi = $sumBy($realRows, 'klaim');
        $hppProyeksi    = $sumBy($projRows, 'hpp');
        $klaimProyeksi  = $sumBy($projRows, 'klaim');

        // Total bulan ini = realisasi + proyeksi
        $hppBulanIni      = $hppRealisasi + $hppProyeksi;
        $proyeksiBulanIni = $klaimRealisasi + $klaimProyeksi;

        // ── Rekonsiliasi BPJS bulan ini ────────────────────────────
        $rekon = RekonsiliasiiBpjs::where('bulan', $bulan)->where('tahun', $tahun)->first();
        $rekonData = [
            'proyeksi'    => $proyeksiBulanIni,
            'diajukan'    => $rekon ? (float) $rekon->tagihan_diajukan : 0.0,
            'dibayar'     => $rekon ? (float) $rekon->tagihan_dibayar  : 0.0,
            'selisih'     => $rekon ? (float) ($rekon->tagihan_dibayar - $rekon->tagihan_diajukan) : 0.0,
            'status'      => $rekon?->status ?? 'belum_diajukan',
            'is_pending'  => !$rekon || $rekon->tagihan_dibayar == 0,
        ];

        $pendapatanBpjs = $rekonData['dibayar'] > 0 ? $rekonData['dibayar'] : $proyeksiBulanIni;
        $labaKotor = $pendapatanBpjs - $hppBulanIni;

        // Pasien kronis aktif (dari resep_pasien aktif, bukan formula obat.jumlah_pasien)
        $totalPasienKronis = DB::table('pasien')
            ->where('is_aktif', true)
            ->whereExists(fn ($q) => $q->from('resep_pasien')
                ->whereColumn('resep_pasien.pasien_id', 'pasien.id')
                ->where('resep_pasien.is_aktif', true))
            ->count();

        // Jumlah pasien yang mengambil obat bulan ini
        $pasienBulanIni = DB::table('pengambilan_obat')
            ->whereNull('deleted_at')
            ->where('status', 'selesai')
            ->whereBetween('tanggal_pengambilan', Periode::bulan($tahun, $bulan))
            ->distinct('pasien_id')
            ->count('pasien_id');

        $data = $this->calc->getDashboardData();

        // Real by_diagnosis from actual dispensed items this month
        $byDiagnosisReal = DB::table('item_pengambilan as ip')
            ->join('pengambilan_obat as po', 'ip.pengambilan_obat_id', '=', 'po.id')
            ->join('obat as o', 'ip.obat_id', '=', 'o.id')
            ->whereNull('po.deleted_at')
            ->where('po.status', 'selesai')
            ->whereBetween('po.tanggal_pengambilan', Periode::bulan($tahun, $bulan))
            ->whereNotNull('o.kategori_diagnosis')
            ->where('o.kategori_diagnosis', '!=', '')
            ->groupBy('o.kategori_diagnosis')
            ->select('o.kategori_diagnosis', DB::raw('SUM(ip.jumlah_unit * ip.harga_klaim_bpjs_snapshot * ' . \App\Models\Obat::jfSql('ip.faktor_jasa_farmasi_snapshot') . ') as total'))
            ->orderByDesc('total')
            ->havingRaw('total > 0')
            ->get()
            ->pluck('total', 'kategori_diagnosis')
            ->map(fn ($v) => (int) round((float) $v))
            ->toArray();

        // Real ranking_obat laba from actual transactions this month
        $rankingObatReal = DB::table('item_pengambilan as ip')
            ->join('pengambilan_obat as po', 'ip.pengambilan_obat_id', '=', 'po.id')
            ->join('obat as o', 'ip.obat_id', '=', 'o.id')
            ->whereNull('po.deleted_at')
            ->where('po.status', 'selesai')
            ->whereBetween('po.tanggal_pengambilan', Periode::bulan($tahun, $bulan))
            ->groupBy('ip.obat_id', 'o.nama_obat')
            ->select(
                'ip.obat_id as id',
                'o.nama_obat as nama',
                DB::raw('ROUND(SUM(ip.jumlah_unit * ip.harga_klaim_bpjs_snapshot * ' . \App\Models\Obat::jfSql('ip.faktor_jasa_farmasi_snapshot') . ') - SUM(ip.jumlah_unit * ip.harga_beli_snapshot)) as laba')
            )
            ->orderByDesc('laba')
            ->limit(20)
            ->get()
            ->filter(fn ($row) => (int) $row->laba !== 0)
            ->map(fn ($row) => [
                'id'     => $row->id,
                'nama'   => $row->nama,
                'laba'   => (int) $row->laba,
                'status' => $row->laba >= 0 ? 'Laba' : 'Rugi',
            ])
            ->values()
            ->toArray();

        // Use real transaction data when available; fall back to catalog projection
        if (!empty($byDiagnosisReal)) {
            $data['by_diagnosis'] = $byDiagnosisReal;
        }
        if (!empty($rankingObatReal)) {
            $data['ranking_obat'] = $rankingObatReal;
        }

        $pengeluaranBulanIni = PurchaseOrder::whereBetween('tanggal_po', Periode::bulan($tahun, $bulan))
            ->sum('total_nilai');

        $jumlahPoBulanIni = PurchaseOrder::whereBetween('tanggal_po', Periode::bulan($tahun, $bulan))
            ->count();

        // Alert counts
        $obatAktif = Obat::where('is_active', true)->get();
        $alerts = [
            'rugi'       => $obatAktif->filter(fn ($o) => $o->laba < 0)->count(),
            'stok_habis' => $obatAktif->filter(fn ($o) => $o->stok_aktual <= 0)->count(),
            'stok_kritis'=> $obatAktif->filter(fn ($o) => $o->stok_aktual > 0 && $o->stok_aktual <= $o->stok_minimum)->count(),
            'kadaluarsa' => $obatAktif->filter(fn ($o) => in_array($o->kadaluarsa_status, ['kadaluarsa', 'segera']))->count(),
        ];

        // 6-month trend — cached for 1 hour (12 queries otherwise)
        $cacheKey = "dashboard-tren-6m-{$tahun}-{$bulan}";
        [$trenLabels, $trenPendapatan, $trenPengeluaran] = Cache::remember($cacheKey, 3600, function () {
            $labels = $pendapatan = $pengeluaran = [];
            for ($i = 5; $i >= 0; $i--) {
                $dt = now()->subMonths($i);
                $labels[] = $dt->translatedFormat('M Y');

                $pendapatan[] = round($this->financialsForStatuses(['selesai', 'dijadwalkan'], $dt->month, $dt->year)['klaim']);

                $pengeluaran[] = round(
                    PurchaseOrder::whereBetween('tanggal_po', Periode::bulan($dt->year, $dt->month))
                        ->sum('total_nilai')
                );
            }
            return [$labels, $pendapatan, $pengeluaran];
        });

        return view('dashboard.index', array_merge($data, [
            'pendapatan_bpjs'       => round($pendapatanBpjs),
            'biaya_beli'            => round($hppBulanIni),
            'laba_kotor'            => round($labaKotor),
            // Rincian realisasi (selesai) vs proyeksi (dijadwalkan)
            'hpp_realisasi'         => round($hppRealisasi),
            'klaim_realisasi'       => round($klaimRealisasi),
            'laba_realisasi'        => round($klaimRealisasi - $hppRealisasi),
            'hpp_proyeksi'          => round($hppProyeksi),
            'klaim_proyeksi'        => round($klaimProyeksi),
            'laba_proyeksi'         => round($klaimProyeksi - $hppProyeksi),
            // Tabel rincian per obat (pembukti rekonsiliasi kartu)
            'breakdown_rows'        => $breakdownRows,
            'total_pasien'          => $totalPasienKronis,
            'pasien_bulan_ini'      => $pasienBulanIni,
            'rekon_bpjs'            => $rekonData,
            'pengeluaran_bulan_ini' => round($pengeluaranBulanIni),
            'jumlah_po_bulan_ini'   => $jumlahPoBulanIni,
            'alerts'                => $alerts,
            'tren_labels'           => $trenLabels,
            'tren_pendapatan'       => $trenPendapatan,
            'tren_pengeluaran'      => $trenPengeluaran,
        ]));
    }
}
