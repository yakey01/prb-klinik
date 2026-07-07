<?php

namespace App\Console\Commands;

use App\Models\Obat;
use Illuminate\Console\Command;

/**
 * Seed Bahan Medis Habis Pakai (BMHP) standar klinik pratama.
 * Idempoten: upsert by (nama_obat, tipe_obat='bmhp'). Stok item-based (isi_per_box).
 */
class SeedBmhp extends Command
{
    protected $signature = 'bmhp:seed {--fresh : kosongkan stok awal jadi 0}';
    protected $description = 'Seed daftar BMHP standar (spuit, handscoon, kasa, dll) ke katalog apotik';

    /** [nama, kategori, satuan, isi_per_box, beli, jual, stok_min, stok_awal] */
    private array $items = [
        ['Spuit 1cc',               'Injeksi',         'pcs',     100, 700,   1300,  20, 300],
        ['Spuit 3cc',               'Injeksi',         'pcs',     100, 800,   1400,  20, 400],
        ['Spuit 5cc',               'Injeksi',         'pcs',     100, 1000,  1800,  20, 300],
        ['Spuit 10cc',              'Injeksi',         'pcs',     100, 1400,  2300,  20, 200],
        ['Wing Needle 23G',         'Injeksi',         'pcs',     1,   2500,  4500,  10, 50],
        ['Abocath No.18',           'Infus',           'pcs',     50,  3500,  6500,  10, 50],
        ['Abocath No.20',           'Infus',           'pcs',     50,  3500,  6500,  10, 100],
        ['Abocath No.22',           'Infus',           'pcs',     50,  3500,  6500,  10, 100],
        ['Abocath No.24',           'Infus',           'pcs',     50,  3800,  7000,  10, 50],
        ['Infus Set Makro',         'Infus',           'set',     1,   5500,  9500,  10, 40],
        ['Infus Set Mikro',         'Infus',           'set',     1,   6500,  11000, 10, 30],
        ['Handscoon Non-Steril M',  'Proteksi Diri',   'pasang',  100, 700,   1300,  30, 500],
        ['Handscoon Steril 7.5',    'Proteksi Diri',   'pasang',  1,   3500,  6000,  10, 50],
        ['Masker Medis 3ply',       'Proteksi Diri',   'pcs',     50,  400,   900,   50, 500],
        ['Kasa Steril 16x16',       'Perawatan Luka',  'pcs',     1,   1500,  3000,  20, 100],
        ['Kapas 250gr',             'Perawatan Luka',  'bungkus', 1,   12000, 18000, 5,  20],
        ['Alkohol Swab',            'Perawatan Luka',  'pcs',     100, 150,   400,   50, 600],
        ['Plester Hypafix 5cm',     'Perawatan Luka',  'roll',    1,   18000, 28000, 5,  15],
        ['Micropore 1/2 inch',      'Perawatan Luka',  'roll',    1,   8000,  13000, 10, 25],
        ['Folley Catheter No.16',   'Urologi',         'pcs',     1,   8000,  14000, 5,  20],
        ['Urine Bag',               'Urologi',         'pcs',     1,   5000,  9000,  5,  20],
        ['NGT No.16',               'Lain-lain',       'pcs',     1,   6000,  10000, 5,  15],
        ['Nasal Kanul Dewasa',      'Oksigenasi',      'pcs',     1,   5000,  9000,  5,  20],
        ['Underpad',                'Lain-lain',       'pcs',     1,   2500,  4500,  10, 40],
        ['Betadine Solution 60ml',  'Antiseptik',      'botol',   1,   12000, 18000, 10, 30],
    ];

    public function handle(): int
    {
        $fresh = (bool) $this->option('fresh');
        $n = 0;
        foreach ($this->items as [$nama, $kat, $satuan, $isi, $beli, $jual, $min, $stok]) {
            $margin = $beli > 0 ? round(($jual - $beli) / $beli, 4) : 0;
            Obat::updateOrCreate(
                ['nama_obat' => $nama, 'tipe_obat' => 'bmhp'],
                [
                    'kode_obat'           => 'BMHP' . str_pad((string) (++$n), 3, '0', STR_PAD_LEFT),
                    'kategori_diagnosis'  => $kat,
                    'satuan'              => $satuan,
                    'isi_per_box'         => $isi,
                    'harga_beli_per_unit' => $beli,
                    'harga_jual_per_unit' => $jual,
                    'margin_umum'         => $margin,
                    'klaim_bpjs_per_unit' => 0,
                    'faktor_jasa_farmasi' => 1,
                    'jumlah_pasien'       => 0,
                    'unit_per_bulan'      => 0,
                    'sumber_harga'        => 'EST',
                    'stok_minimum'        => $min,
                    'is_active'           => true,
                    'stok_aktual'         => $fresh ? 0 : $stok,
                ]
            );
        }
        $this->info("Selesai — {$n} BMHP di-seed (tipe_obat=bmhp).");
        return self::SUCCESS;
    }
}
