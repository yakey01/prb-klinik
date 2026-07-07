<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PrbManagerController extends Controller
{
    // ──────────────────────────────────────────────────────────────
    // Public endpoints
    // ──────────────────────────────────────────────────────────────

    public function summary(): JsonResponse
    {
        return response()->json($this->summaryData());
    }

    public function stokKritis(): JsonResponse
    {
        return response()->json(['data' => $this->stokKritisData()]);
    }

    public function pasienOverdue(): JsonResponse
    {
        return response()->json(['data' => $this->pasienOverdueData()]);
    }

    public function keuangan(): JsonResponse
    {
        return response()->json($this->keuanganData());
    }

    public function pengambilanTerbaru(): JsonResponse
    {
        return response()->json(['data' => $this->pengambilanTerbaruData()]);
    }

    public function obatDefisit(): JsonResponse
    {
        return response()->json($this->obatDefisitData());
    }

    public function prediksiStok(): JsonResponse
    {
        return response()->json(['data' => $this->prediksiStokData()]);
    }

    // ──────────────────────────────────────────────────────────────
    // Bridge RME — katalog, stok live, pasien (read-only PRB → RME)
    // ──────────────────────────────────────────────────────────────

    /** Katalog obat lengkap untuk sinkronisasi ke RME. ?since=ISO8601 untuk incremental. */
    public function katalog(Request $request): JsonResponse
    {
        $since = $request->query('since');
        $data  = $this->katalogData(is_string($since) ? $since : null);

        return response()->json([
            'data'        => $data,
            'total'       => count($data),
            'server_time' => now()->toIso8601String(),
        ]);
    }

    /** Stok + harga live ringan untuk badge di layar resep RME. ?ids=1,2,3 */
    public function katalogStok(Request $request): JsonResponse
    {
        $ids = array_values(array_filter(array_map(
            'intval',
            explode(',', (string) $request->query('ids', ''))
        )));

        return response()->json([
            'data'        => $this->katalogStokData($ids),
            'server_time' => now()->toIso8601String(),
        ]);
    }

    /** Cari pasien PRB (Rujuk Balik) + konteks kronis. ?bpjs= atau ?nama= */
    public function pasienCari(Request $request): JsonResponse
    {
        $bpjs = trim((string) $request->query('bpjs', ''));
        $nama = trim((string) $request->query('nama', ''));

        $data = $this->pasienCariData($bpjs !== '' ? $bpjs : null, $nama !== '' ? $nama : null);

        return response()->json([
            'found' => $data !== null,
            'data'  => $data,
        ]);
    }

    /**
     * Daftarkan resep dari RME → PRB sebagai pengambilan terjadwal.
     * Idempoten via ref_rme (no_recipe). Tidak pernah membuat pasien baru.
     */
    public function resepDaftar(Request $request): JsonResponse
    {
        $v = $request->validate([
            'ref_rme'            => ['required', 'string', 'max:40'],
            'bpjs'               => ['required', 'string', 'max:20'],
            'status'             => ['nullable', 'in:dijadwalkan,selesai'],
            'tanggal'            => ['nullable', 'date'],
            'jadwal_berikutnya'  => ['nullable', 'date'],
            'catatan'            => ['nullable', 'string', 'max:255'],
            'items'              => ['required', 'array', 'min:1'],
            'items.*.prb_obat_id'=> ['required', 'integer'],
            'items.*.jumlah'     => ['required', 'numeric', 'min:0.01'],
            'items.*.satuan'     => ['nullable', 'string', 'max:20'],
        ]);
        $status = $v['status'] ?? 'dijadwalkan';

        // Idempotency / penyelesaian — sudah pernah didaftarkan?
        $existing = \App\Models\PengambilanObat::with('items', 'pasien')->where('ref_rme', $v['ref_rme'])->first();
        if ($existing) {
            // Sudah selesai → idempoten penuh.
            if ($existing->status === 'selesai') {
                return response()->json([
                    'ok' => true, 'duplicate' => true,
                    'pengambilan_id' => $existing->id, 'status' => 'selesai',
                    'message' => 'Pengambilan sudah selesai sebelumnya (idempoten).',
                ]);
            }
            // Diminta SELESAIKAN (obat diserahkan) → catat stok keluar + kurangi stok SEKALI.
            if ($status === 'selesai') {
                DB::transaction(function () use ($existing) {
                    $namaPasien = $existing->pasien->nama ?? '-';
                    foreach ($existing->items as $it) {
                        $obat = \App\Models\Obat::find($it->obat_id);
                        if (!$obat) continue;
                        $sblm = (int) $obat->stok_aktual;
                        $jml  = (int) $it->jumlah_unit;
                        // GERBANG ANTI-MINUS: stok tak cukup → lewati (tak catat, tak potong).
                        if ($sblm < $jml) continue;
                        \App\Models\StokKeluar::create([
                            'obat_id'             => $obat->id,
                            'tanggal_keluar'      => now()->toDateString(),
                            'jumlah_unit'         => $it->jumlah_unit,
                            'stok_sebelum'        => $sblm,
                            'stok_sesudah'        => $sblm - $it->jumlah_unit,
                            'satuan'              => $it->satuan,
                            'harga_beli_snapshot' => (float) $it->harga_beli_snapshot,
                            'harga_jual_per_unit' => round($it->harga_klaim_bpjs_snapshot * \App\Models\Obat::jfMultiplier($it->faktor_jasa_farmasi_snapshot), 2),
                            'keterangan'          => 'Pengambilan (RME): ' . $namaPasien,
                            'sumber'              => 'pengambilan',
                            'pengambilan_obat_id' => $existing->id,
                            'pasien_id'           => $existing->pasien_id,
                        ]);
                        \App\Models\Obat::kurangiStok((int) $obat->id, $jml);
                    }
                    $existing->update(['status' => 'selesai', 'tanggal_pengambilan' => now()->toDateString()]);
                });
                return response()->json([
                    'ok' => true, 'completed' => true,
                    'pengambilan_id' => $existing->id, 'status' => 'selesai',
                    'message' => 'Pengambilan diselesaikan, stok dikurangi.',
                ]);
            }
            // Masih dijadwalkan, dipanggil ulang sbg dijadwalkan → no-op.
            return response()->json([
                'ok' => true, 'duplicate' => true,
                'pengambilan_id' => $existing->id, 'status' => $existing->status,
                'message' => 'Resep sudah terdaftar sebelumnya (idempoten).',
            ]);
        }

        // Pasien WAJIB sudah ada di PRB (cocokkan no_bpjs). Tidak membuat pasien dari RME.
        $pasien = \App\Models\Pasien::whereNull('deleted_at')->where('no_bpjs', $v['bpjs'])->first();
        if (!$pasien) {
            return response()->json([
                'ok' => false, 'reason' => 'pasien_not_found',
                'message' => 'Pasien dengan no BPJS tersebut belum terdaftar di PRB.',
            ], 404);
        }

        // Validasi obat ada
        $obatIds = collect($v['items'])->pluck('prb_obat_id')->map('intval')->unique();
        $obatMap = \App\Models\Obat::whereIn('id', $obatIds)->get()->keyBy('id');
        $valid = collect($v['items'])->filter(fn ($i) => $obatMap->has((int) $i['prb_obat_id']))->values();
        if ($valid->isEmpty()) {
            return response()->json([
                'ok' => false, 'reason' => 'no_valid_obat',
                'message' => 'Tidak ada obat valid yang cocok dengan katalog PRB.',
            ], 422);
        }

        $tanggal = $v['tanggal'] ?? now()->toDateString();

        $result = DB::transaction(function () use ($v, $pasien, $valid, $obatMap, $status, $tanggal) {
            $pengambilan = \App\Models\PengambilanObat::create([
                'pasien_id'          => $pasien->id,
                'tanggal_pengambilan'=> $tanggal,
                // Default siklus PRB 30 hari bila RME tak kirim — tanpa ini pasien hilang dari notifikasi.
                'jadwal_berikutnya'  => $v['jadwal_berikutnya'] ?? date('Y-m-d', strtotime($tanggal . ' +30 days')),
                'status'             => $status,
                'total_item'         => $valid->count(),
                'catatan'            => $v['catatan'] ?? 'Resep dari RME klinik',
                'ref_rme'            => $v['ref_rme'],
                'sumber_resep'       => 'rme',
            ]);

            foreach ($valid as $i) {
                $obat   = $obatMap[(int) $i['prb_obat_id']];
                $jumlah = (int) $i['jumlah'];
                $satuan = $i['satuan'] ?? $obat->satuan ?? 'tablet';
                $beli   = (float) $obat->harga_beli_per_unit;
                $klaim  = (float) $obat->klaim_bpjs_per_unit;
                $faktor = (float) $obat->faktor_jasa_farmasi;

                \App\Models\ItemPengambilan::create([
                    'pengambilan_obat_id'          => $pengambilan->id,
                    'obat_id'                      => $obat->id,
                    'jumlah_unit'                  => $jumlah,
                    'satuan'                       => $satuan,
                    'harga_beli_snapshot'          => $beli,
                    'harga_klaim_bpjs_snapshot'    => $klaim,
                    'faktor_jasa_farmasi_snapshot' => $faktor,
                    'catatan'                      => 'RME',
                ]);

                // Pengambilan SELESAI = obat benar-benar diserahkan → catat stok keluar + kurangi stok
                // (mirror PengambilanObatForm: stok_keluar + decrement stok_aktual).
                if ($status === 'selesai') {
                    $sblm = (int) $obat->stok_aktual;
                    // GERBANG ANTI-MINUS: hanya catat & potong bila stok mencukupi.
                    if ($sblm >= (int) $jumlah) {
                        \App\Models\StokKeluar::create([
                            'obat_id'             => $obat->id,
                            'tanggal_keluar'      => $tanggal,
                            'jumlah_unit'         => $jumlah,
                            'stok_sebelum'        => $sblm,
                            'stok_sesudah'        => $sblm - $jumlah,
                            'satuan'              => $satuan,
                            'harga_beli_snapshot' => $beli,
                            'harga_jual_per_unit' => round($klaim * $faktor, 2),
                            'keterangan'          => 'Pengambilan (RME): ' . $pasien->nama,
                            'sumber'              => 'pengambilan',
                            'pengambilan_obat_id' => $pengambilan->id,
                            'pasien_id'           => $pasien->id,
                        ]);
                        \App\Models\Obat::kurangiStok((int) $obat->id, (int) $jumlah);
                    }
                }
            }

            return $pengambilan;
        });

        return response()->json([
            'ok' => true, 'duplicate' => false,
            'pengambilan_id' => $result->id,
            'status'         => $result->status,
            'total_item'     => $result->total_item,
            'pasien'         => $pasien->nama,
            'message'        => 'Resep berhasil didaftarkan ke PRB.',
        ], 201);
    }

    /**
     * Batch endpoint — satu HTTP call untuk beberapa endpoint sekaligus.
     * Param: ?endpoints=summary,stok-kritis,pasien-overdue,keuangan,pengambilan-terbaru,obat-defisit,prediksi-stok
     */
    public function batch(Request $request): JsonResponse
    {
        $want = array_filter(array_map('trim', explode(',', $request->query(
            'endpoints',
            'summary,stok-kritis,pasien-overdue,keuangan,pengambilan-terbaru,obat-defisit,prediksi-stok'
        ))));

        $map = [
            'summary'             => fn() => $this->summaryData(),
            'stok-kritis'         => fn() => ['data' => $this->stokKritisData()],
            'pasien-overdue'      => fn() => ['data' => $this->pasienOverdueData()],
            'keuangan'            => fn() => $this->keuanganData(),
            'pengambilan-terbaru' => fn() => ['data' => $this->pengambilanTerbaruData()],
            'obat-defisit'        => fn() => $this->obatDefisitData(),
            'prediksi-stok'       => fn() => ['data' => $this->prediksiStokData()],
        ];

        $result = [];
        foreach ($want as $key) {
            if (isset($map[$key])) {
                $result[$key] = ($map[$key])();
            }
        }

        return response()->json([
            'data'      => $result,
            'timestamp' => now()->toIso8601String(),
        ]);
    }

    // ──────────────────────────────────────────────────────────────
    // Private data methods (testable, reusable by batch)
    // ──────────────────────────────────────────────────────────────

    private function summaryData(): array
    {
        $now    = now();
        $month  = $now->month;
        $year   = $now->year;
        $ym     = $now->format('Y-m');

        $pasienAktif = DB::table('pasien')
            ->where('is_aktif', true)->whereNull('deleted_at')->count();

        $pengambilanBulanIni = DB::table('pengambilan_obat')
            ->whereYear('tanggal_pengambilan', $year)
            ->whereMonth('tanggal_pengambilan', $month)
            ->whereNull('deleted_at')->count();

        $pengambilanBulanLalu = DB::table('pengambilan_obat')
            ->whereYear('tanggal_pengambilan', $now->copy()->subMonth()->year)
            ->whereMonth('tanggal_pengambilan', $now->copy()->subMonth()->month)
            ->whereNull('deleted_at')->count();

        $proyeksiKlaim = DB::table('item_pengambilan')
            ->join('pengambilan_obat', 'pengambilan_obat.id', '=', 'item_pengambilan.pengambilan_obat_id')
            ->whereYear('pengambilan_obat.tanggal_pengambilan', $year)
            ->whereMonth('pengambilan_obat.tanggal_pengambilan', $month)
            ->whereNull('pengambilan_obat.deleted_at')
            ->selectRaw('SUM(item_pengambilan.jumlah_unit * item_pengambilan.harga_klaim_bpjs_snapshot * ' . \App\Models\Obat::jfSql('item_pengambilan.faktor_jasa_farmasi_snapshot') . ') as total')
            ->value('total') ?? 0;

        $proyeksiKlaimLalu = DB::table('item_pengambilan')
            ->join('pengambilan_obat', 'pengambilan_obat.id', '=', 'item_pengambilan.pengambilan_obat_id')
            ->whereYear('pengambilan_obat.tanggal_pengambilan', $now->copy()->subMonth()->year)
            ->whereMonth('pengambilan_obat.tanggal_pengambilan', $now->copy()->subMonth()->month)
            ->whereNull('pengambilan_obat.deleted_at')
            ->selectRaw('SUM(item_pengambilan.jumlah_unit * item_pengambilan.harga_klaim_bpjs_snapshot * ' . \App\Models\Obat::jfSql('item_pengambilan.faktor_jasa_farmasi_snapshot') . ') as total')
            ->value('total') ?? 0;

        $hppBulanIni = DB::table('item_pengambilan')
            ->join('pengambilan_obat', 'pengambilan_obat.id', '=', 'item_pengambilan.pengambilan_obat_id')
            ->whereYear('pengambilan_obat.tanggal_pengambilan', $year)
            ->whereMonth('pengambilan_obat.tanggal_pengambilan', $month)
            ->whereNull('pengambilan_obat.deleted_at')
            ->selectRaw('SUM(item_pengambilan.jumlah_unit * item_pengambilan.harga_beli_snapshot) as total')
            ->value('total') ?? 0;

        $obatDefisit = DB::table('obat')
            ->where('is_active', true)
            ->where(function ($q) {
                $q->whereRaw('klaim_bpjs_per_unit > 0 AND harga_beli_per_unit > klaim_bpjs_per_unit * ' . \App\Models\Obat::jfSql('faktor_jasa_farmasi') . '')
                  ->orWhereRaw('klaim_bpjs_per_unit = 0 AND harga_beli_per_unit > 0');
            })->count();

        // Habis: stok = 0 (atau NULL) — benar-benar tidak ada stok
        $stokHabis = DB::table('obat')
            ->where('is_active', true)
            ->where(fn($q) => $q->whereNull('stok_aktual')->orWhere('stok_aktual', '<=', 0))->count();

        // Kritis: stok > 0 tapi di bawah minimum — TIDAK termasuk habis
        $stokKritis = DB::table('obat')
            ->where('is_active', true)
            ->whereNotNull('stok_minimum')
            ->where('stok_minimum', '>', 0)
            ->whereRaw('stok_aktual > 0')
            ->whereRaw('stok_aktual <= stok_minimum')
            ->count();

        $pasienOverdue = DB::table('pengambilan_obat')
            ->join('pasien', 'pasien.id', '=', 'pengambilan_obat.pasien_id')
            ->whereNotNull('pengambilan_obat.jadwal_berikutnya')
            ->where('pengambilan_obat.jadwal_berikutnya', '<', $now->toDateString())
            ->where('pasien.is_aktif', true)
            ->whereNull('pasien.deleted_at')->whereNull('pengambilan_obat.deleted_at')
            ->whereRaw('pengambilan_obat.id = (
                SELECT MAX(p2.id) FROM pengambilan_obat p2
                WHERE p2.pasien_id = pengambilan_obat.pasien_id AND p2.deleted_at IS NULL
            )')
            ->count();

        $tagihanAktif = DB::table('tagihan')
            ->whereIn('status', ['belum_bayar', 'sebagian'])
            ->selectRaw('COUNT(*) as count, SUM(total_tagihan - jumlah_dibayar) as total_sisa')
            ->first();

        $tagihanOverdue = DB::table('tagihan')
            ->whereIn('status', ['belum_bayar', 'sebagian'])
            ->where('tanggal_jatuh_tempo', '<', $now->toDateString())->count();

        $nilaiPoBulanIni = DB::table('purchase_orders')
            ->whereYear('tanggal_po', $year)->whereMonth('tanggal_po', $month)
            ->sum('total_nilai');

        $trendPengambilan = $pengambilanBulanLalu > 0
            ? round(($pengambilanBulanIni - $pengambilanBulanLalu) / $pengambilanBulanLalu * 100, 1) : 0;
        $trendKlaim = $proyeksiKlaimLalu > 0
            ? round(($proyeksiKlaim - $proyeksiKlaimLalu) / $proyeksiKlaimLalu * 100, 1) : 0;

        return [
            'period'                => $ym,
            'pasien_aktif'          => $pasienAktif,
            'pengambilan_bulan_ini' => $pengambilanBulanIni,
            'trend_pengambilan'     => $trendPengambilan,
            'proyeksi_klaim_bpjs'   => round($proyeksiKlaim),
            'hpp_bulan_ini'         => round($hppBulanIni),
            'laba_kotor'            => round($proyeksiKlaim - $hppBulanIni),
            'trend_klaim'           => $trendKlaim,
            'obat_defisit'          => $obatDefisit,
            'stok_kritis'           => $stokKritis,
            'stok_habis'            => $stokHabis,
            'pasien_overdue'        => $pasienOverdue,
            'tagihan_aktif_count'   => (int) ($tagihanAktif->count ?? 0),
            'tagihan_aktif_nominal' => (int) ($tagihanAktif->total_sisa ?? 0),
            'tagihan_overdue'       => $tagihanOverdue,
            'nilai_po_bulan_ini'    => round($nilaiPoBulanIni),
            'updated_at'            => $now->toIso8601String(),
        ];
    }

    private function stokKritisData(): array
    {
        return DB::table('obat')
            ->where('is_active', true)
            ->where(function ($q) {
                $q->whereRaw('stok_aktual <= stok_minimum AND stok_minimum > 0')
                  ->orWhereNull('stok_aktual')
                  ->orWhere('stok_aktual', '<=', 0);
            })
            ->orderByRaw('COALESCE(stok_aktual, 0) ASC')
            ->select('id', 'nama_obat', 'kategori_diagnosis', 'satuan',
                     'stok_aktual', 'stok_minimum', 'tipe_obat', 'klaim_bpjs_per_unit')
            ->limit(20)->get()
            ->map(fn($r) => [
                'id'                 => $r->id,
                'nama_obat'          => $r->nama_obat,
                'kategori_diagnosis' => $r->kategori_diagnosis,
                'satuan'             => $r->satuan,
                'stok_aktual'        => (int) ($r->stok_aktual ?? 0),
                'stok_minimum'       => (int) ($r->stok_minimum ?? 0),
                'tipe_obat'          => $r->tipe_obat,
                'klaim_bpjs'         => (float) $r->klaim_bpjs_per_unit,
                'status'             => ($r->stok_aktual ?? 0) <= 0 ? 'habis' : 'kritis',
            ])->toArray();
    }

    private function pasienOverdueData(): array
    {
        $today = now()->toDateString();
        return DB::table('pengambilan_obat')
            ->join('pasien', 'pasien.id', '=', 'pengambilan_obat.pasien_id')
            ->whereNotNull('pengambilan_obat.jadwal_berikutnya')
            ->where('pengambilan_obat.jadwal_berikutnya', '<', $today)
            ->where('pasien.is_aktif', true)
            ->whereNull('pasien.deleted_at')->whereNull('pengambilan_obat.deleted_at')
            ->whereRaw('pengambilan_obat.id = (
                SELECT MAX(p2.id) FROM pengambilan_obat p2
                WHERE p2.pasien_id = pengambilan_obat.pasien_id AND p2.deleted_at IS NULL
            )')
            ->select(
                'pasien.id', 'pasien.nama', 'pasien.no_bpjs',
                'pasien.kategori_diagnosis', 'pasien.telepon',
                'pengambilan_obat.jadwal_berikutnya',
                DB::raw('DATEDIFF("' . $today . '", pengambilan_obat.jadwal_berikutnya) as hari_terlambat')
            )
            ->orderBy('hari_terlambat', 'desc')->limit(15)->get()
            ->map(fn($r) => [
                'id'                 => $r->id,
                'nama'               => $r->nama,
                'no_bpjs'            => $r->no_bpjs,
                'kategori_diagnosis' => $r->kategori_diagnosis,
                'telepon'            => $r->telepon,
                'jadwal_berikutnya'  => $r->jadwal_berikutnya,
                'hari_terlambat'     => (int) $r->hari_terlambat,
            ])->toArray();
    }

    private function keuanganData(): array
    {
        $now   = now();
        $start = $now->copy()->subMonths(5)->startOfMonth();
        $end   = $now->copy()->endOfMonth();

        $trend = DB::table('item_pengambilan')
            ->join('pengambilan_obat', 'pengambilan_obat.id', '=', 'item_pengambilan.pengambilan_obat_id')
            ->whereNull('pengambilan_obat.deleted_at')
            ->whereBetween('pengambilan_obat.tanggal_pengambilan', [$start->toDateString(), $end->toDateString()])
            ->selectRaw("
                DATE_FORMAT(pengambilan_obat.tanggal_pengambilan, '%Y-%m') as ym,
                SUM(item_pengambilan.jumlah_unit * item_pengambilan.harga_klaim_bpjs_snapshot * ' . \App\Models\Obat::jfSql('item_pengambilan.faktor_jasa_farmasi_snapshot') . ') as klaim,
                SUM(item_pengambilan.jumlah_unit * item_pengambilan.harga_beli_snapshot) as hpp
            ")
            ->groupBy('ym')->orderBy('ym')->get()
            ->map(fn($r) => [
                'bulan' => $r->ym,
                'klaim' => round((float) $r->klaim),
                'hpp'   => round((float) $r->hpp),
                'laba'  => round((float) $r->klaim - (float) $r->hpp),
            ])->toArray();

        $tagihanPerStatus = DB::table('tagihan')
            ->selectRaw('status, COUNT(*) as count, SUM(total_tagihan) as total, SUM(jumlah_dibayar) as dibayar')
            ->groupBy('status')->get()
            ->map(fn($r) => [
                'status'  => $r->status,
                'count'   => (int) $r->count,
                'total'   => (int) $r->total,
                'dibayar' => (int) $r->dibayar,
                'sisa'    => (int) max(0, $r->total - $r->dibayar),
            ])->toArray();

        $rekonsiliasi = DB::table('rekonsiliasi_bpjs')
            ->orderByRaw('tahun DESC, bulan DESC')->limit(6)->get()
            ->map(fn($r) => [
                'periode'   => sprintf('%04d-%02d', $r->tahun, $r->bulan),
                'proyeksi'  => (float) ($r->proyeksi_pendapatan ?? 0),
                'diajukan'  => (float) ($r->tagihan_diajukan ?? 0),
                'dibayar'   => (float) ($r->tagihan_dibayar ?? 0),
                'status'    => $r->status,
            ])->toArray();

        $poTrend = DB::table('purchase_orders')
            ->whereBetween('tanggal_po', [$start->toDateString(), $end->toDateString()])
            ->selectRaw("DATE_FORMAT(tanggal_po, '%Y-%m') as ym, COUNT(*) as count, SUM(total_nilai) as total")
            ->groupBy('ym')->orderBy('ym')->get()
            ->map(fn($r) => [
                'bulan' => $r->ym,
                'count' => (int) $r->count,
                'total' => round((float) $r->total),
            ])->toArray();

        return [
            'trend_6bulan'       => $trend,
            'tagihan_per_status' => $tagihanPerStatus,
            'rekonsiliasi'       => $rekonsiliasi,
            'po_trend'           => $poTrend,
        ];
    }

    private function pengambilanTerbaruData(): array
    {
        return DB::table('pengambilan_obat')
            ->join('pasien', 'pasien.id', '=', 'pengambilan_obat.pasien_id')
            ->leftJoin('users', 'users.id', '=', 'pengambilan_obat.dicatat_oleh')
            ->whereNull('pengambilan_obat.deleted_at')
            ->select(
                'pengambilan_obat.id',
                'pasien.nama as nama_pasien',
                'pasien.kategori_diagnosis',
                'pengambilan_obat.tanggal_pengambilan',
                'pengambilan_obat.total_item',
                'pengambilan_obat.status',
                'pengambilan_obat.jadwal_berikutnya',
                'users.name as dicatat_oleh'
            )
            ->orderBy('pengambilan_obat.id', 'desc')->limit(10)->get()
            ->map(fn($r) => [
                'id'                 => $r->id,
                'nama_pasien'        => $r->nama_pasien,
                'kategori_diagnosis' => $r->kategori_diagnosis,
                'tanggal'            => $r->tanggal_pengambilan,
                'total_item'         => (int) $r->total_item,
                'status'             => $r->status,
                'jadwal_berikutnya'  => $r->jadwal_berikutnya,
                'dicatat_oleh'       => $r->dicatat_oleh,
            ])->toArray();
    }

    private function obatDefisitData(): array
    {
        $rows = DB::table('obat')
            ->where('is_active', true)
            ->where(function ($q) {
                $q->whereRaw('klaim_bpjs_per_unit > 0 AND harga_beli_per_unit > klaim_bpjs_per_unit * ' . \App\Models\Obat::jfSql('faktor_jasa_farmasi') . '')
                  ->orWhereRaw('klaim_bpjs_per_unit = 0 AND harga_beli_per_unit > 0');
            })
            ->selectRaw('
                id, nama_obat, kategori_diagnosis, satuan, sumber_harga,
                harga_beli_per_unit, klaim_bpjs_per_unit, faktor_jasa_farmasi,
                ROUND(klaim_bpjs_per_unit * ' . \App\Models\Obat::jfSql('faktor_jasa_farmasi') . ', 2)                          AS pendapatan_klaim,
                ROUND(harga_beli_per_unit - klaim_bpjs_per_unit * ' . \App\Models\Obat::jfSql('faktor_jasa_farmasi') . ', 2)    AS rugi_per_unit,
                unit_per_bulan,
                ROUND((harga_beli_per_unit - klaim_bpjs_per_unit * ' . \App\Models\Obat::jfSql('faktor_jasa_farmasi') . ') * unit_per_bulan, 0) AS estimasi_rugi_bulan
            ')
            ->orderByRaw('estimasi_rugi_bulan DESC')->limit(30)->get()
            ->map(fn($r) => [
                'id'                  => $r->id,
                'nama_obat'           => $r->nama_obat,
                'kategori_diagnosis'  => $r->kategori_diagnosis,
                'satuan'              => $r->satuan,
                'sumber_harga'        => $r->sumber_harga,
                'harga_beli'          => (float) $r->harga_beli_per_unit,
                'klaim_bpjs'          => (float) $r->klaim_bpjs_per_unit,
                'faktor_jf'           => (float) $r->faktor_jasa_farmasi,
                'pendapatan_klaim'    => (float) $r->pendapatan_klaim,
                'rugi_per_unit'       => (float) $r->rugi_per_unit,
                'unit_per_bulan'      => (float) $r->unit_per_bulan,
                'estimasi_rugi_bulan' => (float) $r->estimasi_rugi_bulan,
                'tipe'                => (float) $r->klaim_bpjs_per_unit === 0.0 ? 'tidak_terklaim' : 'rugi',
            ]);

        $totalEstimasiRugi = $rows->sum('estimasi_rugi_bulan');
        return [
            'data'                => $rows->toArray(),
            'total'               => $rows->count(),
            'total_estimasi_rugi' => round($totalEstimasiRugi),
        ];
    }

    private function prediksiStokData(): array
    {
        // Ambil rata-rata pengambilan per obat dalam 3 bulan terakhir
        $data = DB::table('item_pengambilan')
            ->join('pengambilan_obat', 'pengambilan_obat.id', '=', 'item_pengambilan.pengambilan_obat_id')
            ->join('obat', 'obat.id', '=', 'item_pengambilan.obat_id')
            ->whereNull('pengambilan_obat.deleted_at')
            ->where('obat.is_active', true)
            ->where('pengambilan_obat.tanggal_pengambilan', '>=', now()->subMonths(3)->toDateString())
            ->selectRaw("
                obat.id, obat.nama_obat, obat.stok_aktual, obat.satuan, obat.kategori_diagnosis,
                DATE_FORMAT(pengambilan_obat.tanggal_pengambilan, '%Y-%m') as ym,
                SUM(item_pengambilan.jumlah_unit) as total_unit
            ")
            ->groupBy('obat.id', 'obat.nama_obat', 'obat.stok_aktual', 'obat.satuan', 'obat.kategori_diagnosis', 'ym')
            ->get()
            ->groupBy('id');

        $prediksi = [];
        foreach ($data as $obatId => $rows) {
            $avgPerBulan = $rows->avg('total_unit');
            if ($avgPerBulan <= 0) continue;

            $stokAktual         = (float) ($rows->first()->stok_aktual ?? 0);
            $bulanSampaiHabis   = round($stokAktual / $avgPerBulan, 1);

            if ($bulanSampaiHabis > 2) continue; // hanya tampilkan yang perlu perhatian

            $prediksi[] = [
                'id'                   => $obatId,
                'nama_obat'            => $rows->first()->nama_obat,
                'kategori_diagnosis'   => $rows->first()->kategori_diagnosis,
                'satuan'               => $rows->first()->satuan,
                'stok_aktual'          => (int) $stokAktual,
                'avg_per_bulan'        => round($avgPerBulan, 0),
                'estimasi_habis_bulan' => $bulanSampaiHabis,
                'urgensi'              => $bulanSampaiHabis <= 0.5 ? 'kritis'
                    : ($bulanSampaiHabis <= 1 ? 'segera' : 'perhatian'),
            ];
        }

        usort($prediksi, fn($a, $b) => $a['estimasi_habis_bulan'] <=> $b['estimasi_habis_bulan']);
        return $prediksi;
    }

    // ──────────────────────────────────────────────────────────────
    // Bridge data methods
    // ──────────────────────────────────────────────────────────────

    private function katalogData(?string $since = null): array
    {
        $q = \App\Models\Obat::query();
        if ($since) {
            try { $q->where('updated_at', '>=', Carbon::parse($since)); } catch (\Throwable $e) {}
        }

        return $q->orderBy('nama_obat')->get()->map(fn ($o) => [
            'id'                  => (int) $o->id,
            'kode_obat'           => $o->kode_obat,
            'nama_obat'           => $o->nama_obat,
            'kategori_diagnosis'  => $o->kategori_diagnosis,
            'tipe_obat'           => $o->tipe_obat,
            'bentuk_sediaan'      => $o->bentuk_sediaan,
            'komposisi'           => $o->komposisi,
            'satuan'              => $o->satuan,
            'harga_beli_per_unit' => (float) $o->harga_beli_per_unit,
            'harga_jual_per_unit' => $o->harga_jual_per_unit !== null ? (float) $o->harga_jual_per_unit : null,
            'klaim_bpjs_per_unit' => (float) $o->klaim_bpjs_per_unit,
            'faktor_jasa_farmasi' => (float) $o->faktor_jasa_farmasi,
            'bayar_bpjs'          => (float) $o->bayar_bpjs,
            'stok_aktual'         => (int) $o->stok_aktual,
            'stok_minimum'        => (int) $o->stok_minimum,
            'stok_status'         => $o->stok_status,
            'tanggal_kadaluarsa'  => optional($o->tanggal_kadaluarsa)->toDateString(),
            'kadaluarsa_status'   => $o->kadaluarsa_status,
            'is_active'           => (bool) $o->is_active,
            'updated_at'          => optional($o->updated_at)->toIso8601String(),
        ])->values()->all();
    }

    private function katalogStokData(array $ids): array
    {
        if (empty($ids)) return [];

        $out = [];
        foreach (\App\Models\Obat::whereIn('id', $ids)->get() as $o) {
            $out[(string) $o->id] = [
                'stok_aktual'         => (int) $o->stok_aktual,
                'stok_minimum'        => (int) $o->stok_minimum,
                'stok_status'         => $o->stok_status,
                'harga_jual_per_unit' => $o->harga_jual_per_unit !== null ? (float) $o->harga_jual_per_unit : null,
                'bayar_bpjs'          => (float) $o->bayar_bpjs,
                'tipe_obat'           => $o->tipe_obat,
                'tanggal_kadaluarsa'  => optional($o->tanggal_kadaluarsa)->toDateString(),
                'kadaluarsa_status'   => $o->kadaluarsa_status,
                'is_active'           => (bool) $o->is_active,
            ];
        }
        return $out;
    }

    private function pasienCariData(?string $bpjs, ?string $nama): ?array
    {
        $q = DB::table('pasien')->whereNull('deleted_at');
        if ($bpjs) {
            $q->where('no_bpjs', $bpjs);
        } elseif ($nama) {
            $q->where('nama', 'like', '%' . $nama . '%');
        } else {
            return null;
        }

        $p = $q->first();
        if (!$p) return null;

        // Resep kronis aktif (standing prescription)
        $resep = DB::table('resep_pasien')
            ->join('obat', 'obat.id', '=', 'resep_pasien.obat_id')
            ->where('resep_pasien.pasien_id', $p->id)
            ->where('resep_pasien.is_aktif', true)
            ->orderBy('resep_pasien.urutan')
            ->get([
                'obat.id as obat_id', 'obat.nama_obat', 'obat.tipe_obat',
                'resep_pasien.jumlah_default', 'resep_pasien.satuan',
            ])->map(fn ($r) => [
                'obat_id'   => (int) $r->obat_id,
                'nama_obat' => $r->nama_obat,
                'tipe_obat' => $r->tipe_obat,
                'jumlah'    => (int) $r->jumlah_default,
                'satuan'    => $r->satuan,
            ])->values()->all();

        // Pengambilan terakhir + jadwal berikutnya
        $last = DB::table('pengambilan_obat')
            ->where('pasien_id', $p->id)->whereNull('deleted_at')
            ->orderByDesc('tanggal_pengambilan')->orderByDesc('id')
            ->first(['tanggal_pengambilan', 'jadwal_berikutnya', 'status', 'total_item']);

        $jadwal     = $last->jadwal_berikutnya ?? null;
        $hariTelat  = $jadwal ? Carbon::parse($jadwal)->diffInDays(now(), false) : null;

        return [
            'id'                 => (int) $p->id,
            'nama'               => $p->nama,
            'no_bpjs'            => $p->no_bpjs,
            'kategori_diagnosis' => $p->kategori_diagnosis,
            'telepon'            => $p->telepon,
            'tanggal_lahir'      => $p->tanggal_lahir,
            'jenis_kelamin'      => $p->jenis_kelamin,
            'is_aktif'           => (bool) $p->is_aktif,
            'resep_aktif'        => $resep,
            'pengambilan_terakhir' => $last ? [
                'tanggal'           => $last->tanggal_pengambilan,
                'jadwal_berikutnya' => $last->jadwal_berikutnya,
                'status'            => $last->status,
                'total_item'        => (int) $last->total_item,
            ] : null,
            'jadwal_berikutnya'  => $jadwal,
            'hari_terlambat'     => ($hariTelat !== null && $hariTelat > 0) ? (int) $hariTelat : 0,
            'overdue'            => $hariTelat !== null && $hariTelat > 0,
        ];
    }

    /**
     * STOK KELUAR generik dari SIM RME (umum & BPJS) — apotik = sumber tunggal stok.
     * Mengurangi Obat.stok_aktual + catat StokKeluar. Idempoten per `ref` (no_resep SIM).
     * Tidak butuh pasien terdaftar di PRB (beda dengan resep/daftar yang khusus PRB).
     */
    public function stokKeluar(Request $request): JsonResponse
    {
        $v = $request->validate([
            'ref'              => ['required', 'string', 'max:60'],
            'sumber'           => ['nullable', 'string', 'max:30'],
            'petugas'          => ['nullable', 'string', 'max:100'],
            'pasien'           => ['nullable', 'array'],
            'pasien.nama'      => ['nullable', 'string', 'max:120'],
            'pasien.no_rm'     => ['nullable', 'string', 'max:40'],
            'items'            => ['required', 'array', 'min:1'],
            'items.*.obat_id'  => ['required', 'integer'],
            'items.*.jumlah'   => ['required', 'numeric', 'min:0.01'],
            'items.*.satuan'   => ['nullable', 'string', 'max:20'],
        ]);

        $ref = $v['ref'];

        // Idempoten: ref ini sudah pernah diproses → kembalikan stok terkini (tak potong lagi).
        if (\App\Models\StokKeluar::where('ref', $ref)->exists()) {
            $ids = collect($v['items'])->pluck('obat_id')->map('intval')->unique()->all();
            return response()->json([
                'ok' => true, 'duplicate' => true, 'ref' => $ref,
                'message' => 'Stok keluar untuk ref ini sudah diproses (idempoten).',
                'items' => \App\Models\Obat::whereIn('id', $ids)->get()
                    ->map(fn ($o) => ['obat_id' => (int) $o->id, 'stok_aktual' => (int) $o->stok_aktual])->values(),
            ]);
        }

        $namaPasien = data_get($v, 'pasien.nama', '-');
        $noRm       = data_get($v, 'pasien.no_rm', '');
        $sumber     = $v['sumber'] ?? 'sim_resep';

        $out = [];
        $skipped = [];
        $lacking = [];
        DB::transaction(function () use ($v, $ref, $namaPasien, $noRm, $sumber, &$out, &$skipped, &$lacking) {
            foreach ($v['items'] as $it) {
                $obat = \App\Models\Obat::find((int) $it['obat_id']);
                if (! $obat) { $skipped[] = (int) $it['obat_id']; continue; }
                $jml = (int) round((float) $it['jumlah']);
                $stokSebelum = (int) $obat->stok_aktual;          // snapshot sebelum potong
                $stokSesudah = $stokSebelum - $jml;

                // GERBANG ANTI-MINUS: stok tak cukup → JANGAN catat & JANGAN potong.
                if ($stokSebelum < $jml) {
                    $lacking[] = [
                        'obat_id'  => (int) $obat->id,
                        'nama'     => $obat->nama_obat,
                        'tersedia' => $stokSebelum,
                        'diminta'  => $jml,
                    ];
                    continue;
                }

                \App\Models\StokKeluar::create([
                    'ref'                 => $ref,
                    'obat_id'             => $obat->id,
                    'tanggal_keluar'      => now()->toDateString(),
                    'jumlah_unit'         => $jml,
                    'stok_sebelum'        => $stokSebelum,
                    'stok_sesudah'        => $stokSesudah,
                    'satuan'              => $it['satuan'] ?? ($obat->satuan ?: 'item'),
                    'harga_beli_snapshot' => (float) ($obat->harga_beli_per_unit ?? 0),
                    'harga_jual_per_unit' => (float) ($obat->harga_jual_per_unit ?? 0),
                    'keterangan'          => 'Resep SIM (RME): ' . $namaPasien . ($noRm ? " [{$noRm}]" : '') . " ref:{$ref}",
                    'sumber'              => $sumber,
                    'pasien_id'           => null,
                ]);
                // Atomik & race-safe (sudah dipre-cek di atas).
                \App\Models\Obat::kurangiStok((int) $obat->id, $jml);
                $obat->refresh();
                $out[] = ['obat_id' => (int) $obat->id, 'stok_aktual' => (int) $obat->stok_aktual];
            }
        });

        return response()->json([
            'ok'      => count($lacking) === 0,
            'ref'     => $ref,
            'items'   => $out,
            'skipped' => $skipped,
            'lacking' => $lacking,
            'message' => count($lacking) === 0
                ? 'Stok dikurangi & dicatat.'
                : 'Sebagian obat stok apotik tidak cukup — catat stok masuk dulu: ' . collect($lacking)->map(fn ($l) => $l['nama'] . ' (ada ' . $l['tersedia'] . ', minta ' . $l['diminta'] . ')')->implode('; '),
        ]);
    }
}
