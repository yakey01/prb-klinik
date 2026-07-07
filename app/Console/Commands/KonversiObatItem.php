<?php

namespace App\Console\Commands;

use App\Models\Obat;
use Illuminate\Console\Command;

/**
 * Konversi stok obat dari BOX → ITEM (satuan terkecil) agar decrement resep real-time akurat.
 * Idempoten: hanya memproses obat yang satuannya masih 'box'.
 *  - stok_aktual ×= isi_per_box, harga ÷= isi_per_box, satuan → unit item.
 * Default isi/box per bentuk: Tablet/Kaplet/Kapsul = 100 (10 strip × 10), Sirup/Salep/Tetes = 1.
 */
class KonversiObatItem extends Command
{
    protected $signature = 'obat:konversi-item {--dry : preview saja} {--isi-tablet=100 : isi per box utk tablet}';
    protected $description = 'Konversi stok obat box → item (satuan terkecil) untuk akurasi real-time';

    public function handle(): int
    {
        $isiTablet = (int) $this->option('isi-tablet') ?: 100;
        $dry = (bool) $this->option('dry');
        $konv = 0; $setOnly = 0;

        foreach (Obat::all() as $o) {
            $satuan = strtolower(trim($o->satuan ?? ''));

            if ($satuan === 'box') {
                // Tablet/kaplet/kapsul yang ter-input per box → konversi ke item.
                $isi  = $isiTablet;
                $newStok  = (int) $o->stok_aktual * $isi;
                $newBeli  = round((float) $o->harga_beli_per_unit / $isi, 2);
                $newJual  = round((float) ($o->harga_jual_per_unit ?? 0) / $isi, 2);

                $this->line(sprintf('%s %-26s %d box → %d item · beli %s→%s · jual %s→%s',
                    $dry ? 'DRY' : 'OK ', mb_substr($o->nama_obat, 0, 26),
                    $o->stok_aktual, $newStok, $o->harga_beli_per_unit, $newBeli,
                    $o->harga_jual_per_unit, $newJual));

                if (!$dry) {
                    $o->update([
                        'stok_aktual'         => $newStok,
                        'harga_beli_per_unit' => $newBeli,
                        'harga_jual_per_unit' => $newJual,
                        'satuan'              => 'tablet',
                        'isi_per_box'         => $isi,
                        'stok_minimum'        => max(1, (int) $o->stok_minimum * $isi),
                    ]);
                }
                $konv++;
            } else {
                // botol/tube/tablet (sudah per-item): set isi_per_box utk tampilan box.
                $isi = match (true) {
                    in_array($satuan, ['botol', 'tube', 'pcs', 'vial', 'ampul', 'sachet']) => 1,
                    in_array($satuan, ['tablet', 'kaplet', 'kapsul', 'strip']) => $isiTablet,
                    default => 1,
                };
                if (!$dry && (int) $o->isi_per_box !== $isi) {
                    $o->update(['isi_per_box' => $isi]);
                }
                $setOnly++;
            }
        }

        $this->info($dry
            ? "Dry-run: $konv obat akan dikonversi box→item, $setOnly obat hanya set isi_per_box."
            : "Selesai — $konv dikonversi box→item · $setOnly set isi_per_box.");
        return self::SUCCESS;
    }
}
