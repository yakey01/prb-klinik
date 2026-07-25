<?php

namespace App\Livewire;

use App\Models\Obat;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Component;

/**
 * Rekap Pengadaan Bulanan — laporan untung/rugi obat KRONIS dari pengadaan
 * (nilai beli vs nilai klaim BPJS), dengan riwayat antar-bulan. Siap lapor CEO.
 *
 * Dasar angka = barang MASUK (purchase_order_items per po.tanggal_po), rumus
 * identik dengan kalender "Barang Masuk Harian" agar konsisten:
 *   klaim kronis = Σ unit × klaim_bpjs_per_unit × faktor_jasa_farmasi
 *   untung       = klaim − beli
 * Non-kronis dipisah & dilabeli "proyeksi retail" (harga jual, BUKAN klaim BPJS)
 * agar tidak mengaburkan angka bisnis PRB/BPJS.
 *
 * @property-read array<int, int>            $tahunTersedia
 * @property-read array<int, array<string, mixed>> $rekap
 * @property-read array<string, mixed>       $ringkasan
 * @property-read array<string, mixed>       $chartData
 */
class RekapPengadaan extends Component
{
    public int $tahun;

    public function mount(): void
    {
        $this->tahun = (int) now()->format('Y');
    }

    /** Tahun yang punya data PO (untuk dropdown). */
    #[Computed]
    public function tahunTersedia(): array
    {
        $rows = DB::table('purchase_orders')
            ->selectRaw('DISTINCT YEAR(tanggal_po) as th')
            ->whereNotNull('tanggal_po')
            ->orderByDesc('th')->pluck('th')->map(fn ($t) => (int) $t)->all();

        return $rows ?: [(int) now()->format('Y')];
    }

    /**
     * Rekap per bulan untuk tahun terpilih.
     *
     * @return array<int, array<string, mixed>>
     */
    #[Computed]
    public function rekap(): array
    {
        $jf = Obat::jfSql('o.faktor_jasa_farmasi');

        $rows = DB::table('purchase_order_items as poi')
            ->join('purchase_orders as po', 'po.id', '=', 'poi.purchase_order_id')
            ->leftJoin('obat as o', 'o.id', '=', 'poi.obat_id')
            ->whereYear('po.tanggal_po', $this->tahun)
            ->selectRaw("
                MONTH(po.tanggal_po) as bln,
                CASE WHEN COALESCE(poi.tipe_obat, o.tipe_obat) = 'kronis' THEN 'kronis' ELSE 'non' END as jenis,
                COUNT(DISTINCT po.id) as po_n,
                COALESCE(SUM(poi.subtotal),0) as beli,
                COALESCE(SUM(
                    CASE WHEN COALESCE(poi.tipe_obat, o.tipe_obat) = 'kronis'
                         THEN (poi.jumlah_box*poi.isi_per_box) * COALESCE(o.klaim_bpjs_per_unit,0) * {$jf}
                         ELSE (poi.jumlah_box*poi.isi_per_box) * COALESCE(o.harga_jual_per_unit,0) END
                ),0) as klaim
            ")
            ->groupBy('bln', 'jenis')
            ->get();

        $bulanNama = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
            'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];

        $map = [];
        foreach ($rows as $r) {
            $map[(int) $r->bln][$r->jenis] = $r;
        }

        $out = [];
        foreach (range(1, 12) as $bln) {
            $k = $map[$bln]['kronis'] ?? null;
            $n = $map[$bln]['non'] ?? null;
            if (! $k && ! $n) {
                continue; // hanya bulan yang ada pengadaan
            }
            $kb = (float) ($k->beli ?? 0);
            $kk = (float) ($k->klaim ?? 0);
            $ku = $kk - $kb;
            $nb = (float) ($n->beli ?? 0);
            $nk = (float) ($n->klaim ?? 0);
            $out[] = [
                'bln' => $bln,
                'label' => $bulanNama[$bln],
                'po' => (int) ($k->po_n ?? 0) + (int) ($n->po_n ?? 0),
                'kronis_beli' => $kb,
                'kronis_klaim' => $kk,
                'kronis_untung' => $ku,
                'kronis_margin' => $kk > 0 ? round($ku / $kk * 100, 1) : 0.0,
                'non_beli' => $nb,
                'non_untung' => $nk - $nb,
            ];
        }

        return $out;
    }

    /** Ringkasan tahun: total kronis beli/klaim/untung + rata2 margin. */
    #[Computed]
    public function ringkasan(): array
    {
        $r = $this->rekap;
        $beli = array_sum(array_column($r, 'kronis_beli'));
        $klaim = array_sum(array_column($r, 'kronis_klaim'));
        $untung = $klaim - $beli;

        return [
            'bulan_ada' => count($r),
            'beli' => $beli,
            'klaim' => $klaim,
            'untung' => $untung,
            'margin' => $klaim > 0 ? round($untung / $klaim * 100, 1) : 0.0,
            'untung_terbaik' => $r ? max(array_column($r, 'kronis_untung')) : 0.0,
            'rata_untung' => $r ? $untung / count($r) : 0.0,
        ];
    }

    /** Data untuk grafik tren untung kronis per bulan. */
    #[Computed]
    public function chartData(): array
    {
        $r = $this->rekap;

        return [
            'labels' => array_map(fn ($x) => mb_substr($x['label'], 0, 3), $r),
            'untung' => array_map(fn ($x) => round($x['kronis_untung']), $r),
        ];
    }

    public function render()
    {
        return view('livewire.rekap-pengadaan');
    }
}
