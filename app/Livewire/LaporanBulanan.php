<?php
namespace App\Livewire;

use App\Models\Obat;
use App\Models\PurchaseOrder;
use App\Models\BiayaOperasional;
use App\Models\StokKeluar;
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
        // ── Segmen A: Obat Kronis — Revenue dari BPJS/JKN (non-tunai, formula PMK 3/2023) ──
        $kronisObat = Obat::where('is_active', true)->where('tipe_obat', 'kronis')->get();
        $pendBpjs   = (float) $kronisObat->sum('pendapatan_bulan');
        $hppBpjs    = (float) $kronisObat->sum('biaya_bulan');
        $labaBpjs   = $pendBpjs - $hppBpjs;
        $marginBpjs = $pendBpjs > 0 ? round($labaBpjs / $pendBpjs * 100, 1) : 0;

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
        return Obat::where('is_active', true)->where('tipe_obat', 'kronis')
            ->orderByDesc(\DB::raw('(klaim_bpjs_per_unit * faktor_jasa_farmasi - harga_beli_per_unit) * unit_per_bulan'))
            ->get()
            ->map(fn ($o) => [
                'nama'       => $o->nama_obat,
                'kategori'   => $o->kategori_diagnosis,
                'pasien'     => $o->jumlah_pasien,
                'unit'       => $o->unit_per_bulan,
                'klaim'      => $o->klaim_bpjs_per_unit,
                'faktor'     => $o->faktor_jasa_farmasi,
                'bayar_bpjs' => round($o->klaim_bpjs_per_unit * $o->faktor_jasa_farmasi, 2),
                'harga_beli' => $o->harga_beli_per_unit,
                'pendapatan' => $o->pendapatan_bulan,
                'biaya'      => $o->biaya_bulan,
                'laba'       => $o->laba,
            ]);
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
        return Obat::where('is_active', true)->get()
            ->sortByDesc('laba')->take(10)->values();
    }

    #[Computed]
    public function topRugi()
    {
        return Obat::where('is_active', true)->get()
            ->filter(fn ($o) => $o->laba < 0)
            ->sortBy('laba')->take(10)->values();
    }

    #[Computed]
    public function tren(): array
    {
        $labels          = [];
        $pendKronisData  = [];
        $pendTunaiData   = [];
        $pengeluaranData = [];

        // Proyeksi BPJS/kronis sama setiap bulan (pendapatan_bulan adalah computed accessor)
        $kronisTotal = (float) Obat::where('is_active', true)->where('tipe_obat', 'kronis')->get()->sum('pendapatan_bulan');

        for ($i = 5; $i >= 0; $i--) {
            $d = Carbon::create($this->tahun, $this->bulan, 1)->subMonths($i);
            $labels[] = $d->locale('id')->translatedFormat('M Y');

            $pengeluaranData[] = (float) PurchaseOrder::whereYear('tanggal_po', $d->year)
                ->whereMonth('tanggal_po', $d->month)->sum('total_nilai');

            $pendKronisData[] = $kronisTotal;

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
