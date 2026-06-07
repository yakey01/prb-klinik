<?php
namespace App\Livewire;

use App\Models\Obat;
use App\Models\PurchaseOrder;
use App\Models\BiayaOperasional;
use App\Models\StokKeluar;
use App\Models\RekonsiliasiiBpjs;
use Livewire\Component;
use Livewire\Attributes\Computed;
use Carbon\Carbon;

class LaporanBulanan extends Component
{
    public int    $bulan;
    public int    $tahun;
    public string $activeTab = 'ringkasan';

    public function mount(): void
    {
        $this->bulan = now()->month;
        $this->tahun = now()->year;
    }

    #[Computed]
    public function periode(): string
    {
        return Carbon::create($this->tahun, $this->bulan, 1)->locale('id')->translatedFormat('F Y');
    }

    #[Computed]
    public function ringkasan(): array
    {
        // ── Segmen A: Obat Kronis BPJS ────────────────────────────────────────────
        //
        // HPP (biaya): dari item_pengambilan aktual bulan ini
        //   = Σ jumlah_unit × harga_beli_snapshot (harga saat penyerahan)
        //
        // PENDAPATAN (revenue): dari rekonsiliasi_bpjs.tagihan_dibayar
        //   = jumlah yang benar-benar dibayar BPJS untuk periode ini
        //   NOTE: klaim bulan N dibayar di bulan N+1, sehingga bulan berjalan
        //         cenderung NEGATIF sampai klaim disetujui.
        //
        // PROYEKSI: dari item_pengambilan × klaim_bpjs_rate (estimasi klaim)

        // HPP aktual dari obat yang diserahkan bulan ini
        $hppBpjs = (float) \DB::table('item_pengambilan as ip')
            ->join('pengambilan_obat as po', 'ip.pengambilan_obat_id', '=', 'po.id')
            ->join('obat as o', 'ip.obat_id', '=', 'o.id')
            ->whereYear('po.tanggal_pengambilan', $this->tahun)
            ->whereMonth('po.tanggal_pengambilan', $this->bulan)
            ->where('po.status', 'selesai')
            ->where('o.tipe_obat', 'kronis')
            ->sum(\DB::raw('ip.jumlah_unit * ip.harga_beli_snapshot'));

        // Proyeksi klaim dari item diserahkan (sebelum rekonsiliasi)
        $proyeksiBpjs = (float) \DB::table('item_pengambilan as ip')
            ->join('pengambilan_obat as po', 'ip.pengambilan_obat_id', '=', 'po.id')
            ->join('obat as o', 'ip.obat_id', '=', 'o.id')
            ->whereYear('po.tanggal_pengambilan', $this->tahun)
            ->whereMonth('po.tanggal_pengambilan', $this->bulan)
            ->where('po.status', 'selesai')
            ->where('o.tipe_obat', 'kronis')
            ->sum(\DB::raw('ip.jumlah_unit * ip.harga_klaim_bpjs_snapshot * ip.faktor_jasa_farmasi_snapshot'));

        // Revenue aktual dari rekonsiliasi BPJS periode ini
        $rekon = RekonsiliasiiBpjs::where('bulan', $this->bulan)
            ->where('tahun', $this->tahun)->first();

        $pendBpjs        = $rekon ? (float) $rekon->tagihan_dibayar : 0.0;
        $diajukanBpjs    = $rekon ? (float) $rekon->tagihan_diajukan : 0.0;
        $statusRekon     = $rekon?->status ?? 'belum_diajukan';
        $selisihRekon    = $pendBpjs - $diajukanBpjs;

        // Jika belum ada rekonsiliasi (bulan berjalan), revenue = 0 (pending)
        // Tampilkan proyeksi sebagai estimasi
        $pendBpjsDisplay = $pendBpjs > 0 ? $pendBpjs : $proyeksiBpjs;
        $isPending       = ($pendBpjs === 0.0);

        $labaBpjs   = $pendBpjsDisplay - $hppBpjs;
        $marginBpjs = $pendBpjsDisplay > 0 ? round($labaBpjs / $pendBpjsDisplay * 100, 1) : 0;

        // ── Segmen B: Obat Non-Kronis — Revenue TUNAI dari Pasien Umum (stok keluar aktual) ──
        $skBulan   = StokKeluar::whereYear('tanggal_keluar', $this->tahun)
                                ->whereMonth('tanggal_keluar', $this->bulan)->get();
        $pendTunai  = (float) $skBulan->sum(fn ($sk) => $sk->total_pendapatan);
        $hppTunai   = (float) $skBulan->sum(fn ($sk) => $sk->total_biaya);
        $labaTunai  = (float) $skBulan->sum(fn ($sk) => $sk->laba);
        $marginTunai = $pendTunai > 0 ? round($labaTunai / $pendTunai * 100, 1) : 0;

        // ── Konsolidasi Laba Rugi ──────────────────────────────────────────────────────────
        $totalPend    = $pendBpjs + $pendTunai;
        $totalHpp     = $hppBpjs + $hppTunai;
        $labaKotor    = $totalPend - $totalHpp;
        $marginPersen = $totalPend > 0 ? round($labaKotor / $totalPend * 100, 1) : 0;

        $biayaOps      = BiayaOperasional::latest()->first();
        $totalBiayaOps = $biayaOps
            ? ($biayaOps->biaya_sdm + $biayaOps->biaya_utilitas + $biayaOps->biaya_administrasi +
               $biayaOps->biaya_sewa + $biayaOps->biaya_lainnya)
            : 0;

        $labaBersih = $labaKotor - $totalBiayaOps;

        // ── Pengadaan (Realisasi PO bulan ini) ────────────────────────────────────────────
        $pengeluaran = PurchaseOrder::whereYear('tanggal_po', $this->tahun)
                                    ->whereMonth('tanggal_po', $this->bulan)
                                    ->sum('total_nilai');

        $pengeluaranBpjs = PurchaseOrder::whereHas('items', fn ($q) => $q->where('tipe_obat', 'kronis'))
                                        ->whereYear('tanggal_po', $this->tahun)
                                        ->whereMonth('tanggal_po', $this->bulan)
                                        ->with('items')
                                        ->get()
                                        ->sum(fn ($po) => $po->items->where('tipe_obat', 'kronis')->sum('subtotal'));

        $pengeluaranUmum = (float) ($pengeluaran - $pengeluaranBpjs);

        return compact(
            // Segmen A: BPJS/Kronis
            'pendBpjs', 'hppBpjs', 'labaBpjs', 'marginBpjs',
            // Rekonsiliasi BPJS detail
            'proyeksiBpjs', 'diajukanBpjs', 'selisihRekon', 'statusRekon', 'isPending',
            // Segmen B: Tunai/Non-Kronis
            'pendTunai', 'hppTunai', 'labaTunai', 'marginTunai',
            // Konsolidasi
            'totalPend', 'totalHpp', 'labaKotor', 'marginPersen',
            'totalBiayaOps', 'labaBersih',
            // Pengadaan
            'pengeluaran', 'pengeluaranBpjs', 'pengeluaranUmum'
        );
    }

    #[Computed]
    public function detailBpjs()
    {
        // Detail obat yang diserahkan bulan ini — basis HPP dan proyeksi klaim
        $rows = \DB::table('item_pengambilan as ip')
            ->join('pengambilan_obat as po', 'ip.pengambilan_obat_id', '=', 'po.id')
            ->join('obat as o', 'ip.obat_id', '=', 'o.id')
            ->whereYear('po.tanggal_pengambilan', $this->tahun)
            ->whereMonth('po.tanggal_pengambilan', $this->bulan)
            ->where('po.status', 'selesai')
            ->where('o.tipe_obat', 'kronis')
            ->select(
                'o.id as obat_id',
                'o.nama_obat', 'o.kategori_diagnosis',
                \DB::raw('COUNT(DISTINCT po.pasien_id) AS pasien'),
                \DB::raw('SUM(ip.jumlah_unit) AS total_unit'),
                \DB::raw('AVG(ip.harga_klaim_bpjs_snapshot) AS klaim'),
                \DB::raw('AVG(ip.faktor_jasa_farmasi_snapshot) AS faktor'),
                \DB::raw('AVG(ip.harga_beli_snapshot) AS harga_beli'),
                \DB::raw('SUM(ip.jumlah_unit * ip.harga_klaim_bpjs_snapshot * ip.faktor_jasa_farmasi_snapshot) AS proyeksi'),
                \DB::raw('SUM(ip.jumlah_unit * ip.harga_beli_snapshot) AS biaya')
            )
            ->groupBy('o.id', 'o.nama_obat', 'o.kategori_diagnosis')
            ->orderByDesc('proyeksi')
            ->get();

        return $rows->map(fn ($r) => [
            'nama'       => $r->nama_obat,
            'kategori'   => $r->kategori_diagnosis,
            'pasien'     => (int) $r->pasien,
            'unit'       => (float) $r->total_unit,
            'klaim'      => round((float) $r->klaim, 2),
            'faktor'     => round((float) $r->faktor, 4),
            'bayar_bpjs' => round((float) $r->klaim * (float) $r->faktor, 2),
            'harga_beli' => round((float) $r->harga_beli, 2),
            'pendapatan' => round((float) $r->proyeksi, 2),  // proyeksi klaim
            'biaya'      => round((float) $r->biaya, 2),
            'laba'       => round((float) $r->proyeksi - (float) $r->biaya, 2),
        ])->values();
    }

    #[Computed]
    public function detailNonKronis()
    {
        return StokKeluar::with('obat')
            ->whereYear('tanggal_keluar', $this->tahun)
            ->whereMonth('tanggal_keluar', $this->bulan)
            ->orderByDesc('tanggal_keluar')
            ->get()
            ->map(fn ($sk) => [
                'tanggal'    => $sk->tanggal_keluar->format('d/m/Y'),
                'nama'       => $sk->obat->nama_obat ?? '—',
                'jumlah'     => $sk->jumlah_unit,
                'satuan'     => $sk->satuan,
                'harga_jual' => $sk->harga_jual_per_unit,
                'harga_beli' => $sk->harga_beli_snapshot,
                'pendapatan' => $sk->total_pendapatan,
                'biaya'      => $sk->total_biaya,
                'laba'       => $sk->laba,
                'keterangan' => $sk->keterangan,
            ]);
    }

    #[Computed]
    public function topLaba()
    {
        // Top obat berdasarkan laba aktual bulan ini (proyeksi klaim - HPP)
        return \DB::table('item_pengambilan as ip')
            ->join('pengambilan_obat as po', 'ip.pengambilan_obat_id', '=', 'po.id')
            ->join('obat as o', 'ip.obat_id', '=', 'o.id')
            ->whereYear('po.tanggal_pengambilan', $this->tahun)
            ->whereMonth('po.tanggal_pengambilan', $this->bulan)
            ->where('po.status', 'selesai')
            ->where('o.tipe_obat', 'kronis')
            ->select(
                'o.nama_obat',
                \DB::raw('SUM(ip.jumlah_unit * ip.harga_klaim_bpjs_snapshot * ip.faktor_jasa_farmasi_snapshot) AS proyeksi'),
                \DB::raw('SUM(ip.jumlah_unit * ip.harga_beli_snapshot) AS biaya'),
                \DB::raw('SUM(ip.jumlah_unit * ip.harga_klaim_bpjs_snapshot * ip.faktor_jasa_farmasi_snapshot)
                         - SUM(ip.jumlah_unit * ip.harga_beli_snapshot) AS laba')
            )
            ->groupBy('o.id', 'o.nama_obat')
            ->having('laba', '>', 0)
            ->orderByDesc('laba')
            ->limit(10)
            ->get();
    }

    #[Computed]
    public function topRugi()
    {
        return \DB::table('item_pengambilan as ip')
            ->join('pengambilan_obat as po', 'ip.pengambilan_obat_id', '=', 'po.id')
            ->join('obat as o', 'ip.obat_id', '=', 'o.id')
            ->whereYear('po.tanggal_pengambilan', $this->tahun)
            ->whereMonth('po.tanggal_pengambilan', $this->bulan)
            ->where('po.status', 'selesai')
            ->where('o.tipe_obat', 'kronis')
            ->select(
                'o.nama_obat',
                \DB::raw('SUM(ip.jumlah_unit * ip.harga_klaim_bpjs_snapshot * ip.faktor_jasa_farmasi_snapshot) AS proyeksi'),
                \DB::raw('SUM(ip.jumlah_unit * ip.harga_beli_snapshot) AS biaya'),
                \DB::raw('SUM(ip.jumlah_unit * ip.harga_klaim_bpjs_snapshot * ip.faktor_jasa_farmasi_snapshot)
                         - SUM(ip.jumlah_unit * ip.harga_beli_snapshot) AS laba')
            )
            ->groupBy('o.id', 'o.nama_obat')
            ->having('laba', '<', 0)
            ->orderBy('laba')
            ->limit(10)
            ->get();
    }

    #[Computed]
    public function tren(): array
    {
        $labels          = [];
        $pendKronisData  = [];
        $pendTunaiData   = [];
        $pengeluaranData = [];

        for ($i = 5; $i >= 0; $i--) {
            $d = Carbon::create($this->tahun, $this->bulan, 1)->subMonths($i);
            $labels[] = $d->locale('id')->translatedFormat('M Y');

            $pengeluaranData[] = (float) PurchaseOrder::whereYear('tanggal_po', $d->year)
                ->whereMonth('tanggal_po', $d->month)->sum('total_nilai');

            // Proyeksi BPJS kronis: dari item_pengambilan aktual per bulan
            $pendKronisData[] = (float) \DB::table('item_pengambilan as ip')
                ->join('pengambilan_obat as po', 'ip.pengambilan_obat_id', '=', 'po.id')
                ->join('obat as o', 'ip.obat_id', '=', 'o.id')
                ->whereYear('po.tanggal_pengambilan', $d->year)
                ->whereMonth('po.tanggal_pengambilan', $d->month)
                ->where('po.status', 'selesai')
                ->where('o.tipe_obat', 'kronis')
                ->sum(\DB::raw('ip.jumlah_unit * ip.harga_klaim_bpjs_snapshot * ip.faktor_jasa_farmasi_snapshot'));

            // Tunai non-kronis: aktual dari stok keluar per bulan
            $pendTunaiData[] = (float) StokKeluar::whereYear('tanggal_keluar', $d->year)
                ->whereMonth('tanggal_keluar', $d->month)
                ->get()->sum(fn ($sk) => $sk->total_pendapatan);
        }

        return compact('labels', 'pendKronisData', 'pendTunaiData', 'pengeluaranData');
    }

    public function exportCsv(): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $ringkasan  = $this->ringkasan;
        $topLaba    = $this->topLaba;
        $detailBpjs = $this->detailBpjs;
        $detailNK   = $this->detailNonKronis;
        $periode    = $this->periode;

        return response()->streamDownload(function () use ($ringkasan, $topLaba, $detailBpjs, $detailNK, $periode) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($handle, ['LAPORAN LABA RUGI BULANAN - '.$periode.' - Klinik Dokterku']);
            fputcsv($handle, ['Segmen: (A) BPJS/JKN — Obat Kronis | (B) Tunai — Obat Non-Kronis/Pasien Umum']);
            fputcsv($handle, []);
            fputcsv($handle, ['=== I. PENDAPATAN ===']);
            fputcsv($handle, ['(A) Pendapatan BPJS/JKN — Obat Kronis (Proyeksi)', number_format($ringkasan['pendBpjs'],0,',','.')]);
            fputcsv($handle, ['    Basis: Klaim BPJS/Unit × Faktor Jasa Farmasi (PMK 3/2023)', '']);
            fputcsv($handle, ['(B) Pendapatan Tunai — Pasien Umum (Aktual)',       number_format($ringkasan['pendTunai'],0,',','.')]);
            fputcsv($handle, ['    Basis: Stok keluar aktual bulan ini', '']);
            fputcsv($handle, ['TOTAL PENDAPATAN',                                  number_format($ringkasan['totalPend'],0,',','.')]);
            fputcsv($handle, []);
            fputcsv($handle, ['=== II. HARGA POKOK PENJUALAN (HPP) ===']);
            fputcsv($handle, ['HPP Obat Kronis',                                   number_format($ringkasan['hppBpjs'],0,',','.')]);
            fputcsv($handle, ['HPP Obat Non-Kronis',                               number_format($ringkasan['hppTunai'],0,',','.')]);
            fputcsv($handle, ['TOTAL HPP',                                         number_format($ringkasan['totalHpp'],0,',','.')]);
            fputcsv($handle, []);
            fputcsv($handle, ['=== III. LABA KOTOR ===']);
            fputcsv($handle, ['Laba Kotor',                                        number_format($ringkasan['labaKotor'],0,',','.')]);
            fputcsv($handle, ['Margin Laba Kotor (%)',                             $ringkasan['marginPersen'].'%']);
            fputcsv($handle, []);
            fputcsv($handle, ['=== IV. BIAYA OPERASIONAL ===']);
            fputcsv($handle, ['Biaya Operasional (SDM, Utilitas, Admin, Sewa)',    number_format($ringkasan['totalBiayaOps'],0,',','.')]);
            fputcsv($handle, []);
            fputcsv($handle, ['=== V. LABA BERSIH ===']);
            fputcsv($handle, ['Laba Bersih',                                       number_format($ringkasan['labaBersih'],0,',','.')]);
            fputcsv($handle, []);
            fputcsv($handle, ['=== ANALISIS KONTRIBUSI SEGMEN ===']);
            fputcsv($handle, ['Segmen','Pendapatan','HPP','Laba','Margin %']);
            fputcsv($handle, ['BPJS/Kronis', number_format($ringkasan['pendBpjs'],0,',','.'), number_format($ringkasan['hppBpjs'],0,',','.'), number_format($ringkasan['labaBpjs'],0,',','.'), $ringkasan['marginBpjs'].'%']);
            fputcsv($handle, ['Tunai/Non-Kronis', number_format($ringkasan['pendTunai'],0,',','.'), number_format($ringkasan['hppTunai'],0,',','.'), number_format($ringkasan['labaTunai'],0,',','.'), $ringkasan['marginTunai'].'%']);
            fputcsv($handle, []);
            fputcsv($handle, ['=== PENGADAAN (REALISASI PO) ===']);
            fputcsv($handle, ['Pengeluaran PO Kronis Bulan Ini',     number_format($ringkasan['pengeluaranBpjs'],0,',','.')]);
            fputcsv($handle, ['Pengeluaran PO Non-Kronis Bulan Ini', number_format($ringkasan['pengeluaranUmum'],0,',','.')]);
            fputcsv($handle, ['Total Pengeluaran PO Bulan Ini',      number_format($ringkasan['pengeluaran'],0,',','.')]);
            fputcsv($handle, []);
            fputcsv($handle, ['=== DETAIL OBAT KRONIS (FORMULA BPJS) ===']);
            fputcsv($handle, ['No','Nama Obat','Diagnosis','Pasien','Unit/Bln','Klaim BPJS/Unit','Faktor JF','Bayar BPJS/Unit','Harga Beli/Unit','Pendapatan/Bln','Biaya/Bln','Laba/Bln']);
            foreach ($detailBpjs as $i => $row) {
                fputcsv($handle, [
                    $i+1, $row['nama'], $row['kategori'], $row['pasien'],
                    $row['unit'], $row['klaim'], $row['faktor'], $row['bayar_bpjs'], $row['harga_beli'],
                    number_format($row['pendapatan'],0,',','.'),
                    number_format($row['biaya'],0,',','.'),
                    number_format($row['laba'],0,',','.'),
                ]);
            }
            fputcsv($handle, []);
            if ($detailNK->isNotEmpty()) {
                fputcsv($handle, ['=== DETAIL STOK KELUAR OBAT NON-KRONIS (AKTUAL) ===']);
                fputcsv($handle, ['No','Tanggal','Nama Obat','Jumlah','Satuan','Harga Jual','Harga Beli','Pendapatan','Biaya','Laba','Keterangan']);
                foreach ($detailNK as $i => $row) {
                    fputcsv($handle, [
                        $i+1, $row['tanggal'], $row['nama'], $row['jumlah'], $row['satuan'],
                        $row['harga_jual'], $row['harga_beli'],
                        number_format($row['pendapatan'],0,',','.'),
                        number_format($row['biaya'],0,',','.'),
                        number_format($row['laba'],0,',','.'),
                        $row['keterangan'],
                    ]);
                }
            }
            fclose($handle);
        }, 'laporan_'.$this->tahun.'_'.str_pad($this->bulan,2,'0',STR_PAD_LEFT).'.csv');
    }

    public function render() { return view('livewire.laporan-bulanan'); }
}
