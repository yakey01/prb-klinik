<?php

namespace App\Http\Controllers;

use App\Services\LabaCalculatorService;
use App\Models\Obat;
use App\Models\Pasien;
use App\Models\PurchaseOrder;
use App\Models\RekonsiliasiiBpjs;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function __construct(private LabaCalculatorService $calc) {}

    public function index()
    {
        $bulan = now()->month;
        $tahun = now()->year;

        // ── HPP aktual dari item_pengambilan bulan ini ─────────────
        $hppBulanIni = (float) DB::table('item_pengambilan as ip')
            ->join('pengambilan_obat as po', 'ip.pengambilan_obat_id', '=', 'po.id')
            ->where('po.status', 'selesai')
            ->whereYear('po.tanggal_pengambilan', $tahun)
            ->whereMonth('po.tanggal_pengambilan', $bulan)
            ->sum(DB::raw('ip.jumlah_unit * ip.harga_beli_snapshot'));

        // ── Proyeksi klaim BPJS dari item diserahkan bulan ini ─────
        $proyeksiBulanIni = (float) DB::table('item_pengambilan as ip')
            ->join('pengambilan_obat as po', 'ip.pengambilan_obat_id', '=', 'po.id')
            ->join('obat as o', 'ip.obat_id', '=', 'o.id')
            ->where('po.status', 'selesai')
            ->where('o.tipe_obat', 'kronis')
            ->whereYear('po.tanggal_pengambilan', $tahun)
            ->whereMonth('po.tanggal_pengambilan', $bulan)
            ->sum(DB::raw('ip.jumlah_unit * ip.harga_klaim_bpjs_snapshot * ip.faktor_jasa_farmasi_snapshot'));

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
            ->where('status', 'selesai')
            ->whereYear('tanggal_pengambilan', $tahun)
            ->whereMonth('tanggal_pengambilan', $bulan)
            ->distinct('pasien_id')
            ->count('pasien_id');

        $data = $this->calc->getDashboardData();

        // Real by_diagnosis from actual dispensed items this month
        $byDiagnosisReal = DB::table('item_pengambilan as ip')
            ->join('pengambilan_obat as po', 'ip.pengambilan_obat_id', '=', 'po.id')
            ->join('obat as o', 'ip.obat_id', '=', 'o.id')
            ->where('po.status', 'selesai')
            ->whereYear('po.tanggal_pengambilan', $tahun)
            ->whereMonth('po.tanggal_pengambilan', $bulan)
            ->whereNotNull('o.kategori_diagnosis')
            ->where('o.kategori_diagnosis', '!=', '')
            ->groupBy('o.kategori_diagnosis')
            ->select('o.kategori_diagnosis', DB::raw('SUM(ip.jumlah_unit * ip.harga_klaim_bpjs_snapshot * ip.faktor_jasa_farmasi_snapshot) as total'))
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
            ->where('po.status', 'selesai')
            ->whereYear('po.tanggal_pengambilan', $tahun)
            ->whereMonth('po.tanggal_pengambilan', $bulan)
            ->groupBy('ip.obat_id', 'o.nama_obat')
            ->select(
                'ip.obat_id as id',
                'o.nama_obat as nama',
                DB::raw('ROUND(SUM(ip.jumlah_unit * ip.harga_klaim_bpjs_snapshot * ip.faktor_jasa_farmasi_snapshot) - SUM(ip.jumlah_unit * ip.harga_beli_snapshot)) as laba')
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

        $pengeluaranBulanIni = PurchaseOrder::whereMonth('tanggal_po', $bulan)
            ->whereYear('tanggal_po', $tahun)
            ->sum('total_nilai');

        $jumlahPoBulanIni = PurchaseOrder::whereMonth('tanggal_po', $bulan)
            ->whereYear('tanggal_po', $tahun)
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

                $pendapatan[] = round((float) DB::table('item_pengambilan as ip')
                    ->join('pengambilan_obat as po', 'ip.pengambilan_obat_id', '=', 'po.id')
                    ->join('obat as o', 'ip.obat_id', '=', 'o.id')
                    ->where('po.status', 'selesai')
                    ->where('o.tipe_obat', 'kronis')
                    ->whereYear('po.tanggal_pengambilan', $dt->year)
                    ->whereMonth('po.tanggal_pengambilan', $dt->month)
                    ->sum(DB::raw('ip.jumlah_unit * ip.harga_klaim_bpjs_snapshot * ip.faktor_jasa_farmasi_snapshot')));

                $pengeluaran[] = round(
                    PurchaseOrder::whereMonth('tanggal_po', $dt->month)
                        ->whereYear('tanggal_po', $dt->year)
                        ->sum('total_nilai')
                );
            }
            return [$labels, $pendapatan, $pengeluaran];
        });

        return view('dashboard.index', array_merge($data, [
            'pendapatan_bpjs'       => round($pendapatanBpjs),
            'biaya_beli'            => round($hppBulanIni),
            'laba_kotor'            => round($labaKotor),
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
