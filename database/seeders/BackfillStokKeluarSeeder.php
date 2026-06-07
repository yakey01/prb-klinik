<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Backfills stok_keluar records for existing item_pengambilan rows
 * that were created before the auto-create logic was added.
 * Also decrements stok_aktual for each backfilled item.
 */
class BackfillStokKeluarSeeder extends Seeder
{
    public function run(): void
    {
        // Find item_pengambilan that don't yet have a matching stok_keluar
        $items = DB::table('item_pengambilan as ip')
            ->join('pengambilan_obat as po', 'ip.pengambilan_obat_id', '=', 'po.id')
            ->join('obat as o', 'ip.obat_id', '=', 'o.id')
            ->leftJoin('stok_keluar as sk', function ($join) {
                $join->on('sk.pengambilan_obat_id', '=', 'ip.pengambilan_obat_id')
                     ->on('sk.obat_id', '=', 'ip.obat_id')
                     ->where('sk.sumber', '=', 'pengambilan');
            })
            ->whereNull('sk.id')
            ->select([
                'ip.id as item_id',
                'ip.obat_id',
                'ip.jumlah_unit',
                'ip.satuan',
                'ip.harga_beli_snapshot',
                'ip.harga_klaim_bpjs_snapshot',
                'ip.faktor_jasa_farmasi_snapshot',
                'ip.pengambilan_obat_id',
                'po.pasien_id',
                'po.tanggal_pengambilan',
                'po.dicatat_oleh',
                'o.nama_obat',
            ])
            ->get();

        if ($items->isEmpty()) {
            $this->command->info('BackfillStokKeluar: nothing to backfill.');
            return;
        }

        $this->command->info("BackfillStokKeluar: backfilling {$items->count()} item_pengambilan records...");

        $now = now();

        foreach ($items as $item) {
            $hargaKlaim = (float) ($item->harga_klaim_bpjs_snapshot ?? 0);
            $faktor     = (float) ($item->faktor_jasa_farmasi_snapshot ?? 1.15);

            DB::table('stok_keluar')->insert([
                'obat_id'              => $item->obat_id,
                'tanggal_keluar'       => $item->tanggal_pengambilan,
                'jumlah_unit'          => $item->jumlah_unit,
                'satuan'               => $item->satuan ?? 'tablet',
                'harga_beli_snapshot'  => (float) ($item->harga_beli_snapshot ?? 0),
                'harga_jual_per_unit'  => round($hargaKlaim * $faktor, 2),
                'keterangan'           => 'Backfill otomatis dari item_pengambilan #' . $item->item_id,
                'sumber'               => 'pengambilan',
                'pengambilan_obat_id'  => $item->pengambilan_obat_id,
                'pasien_id'            => $item->pasien_id,
                'dicatat_oleh'         => $item->dicatat_oleh,
                'created_at'           => $now,
                'updated_at'           => $now,
            ]);

            // Decrement stok_aktual
            DB::table('obat')
                ->where('id', $item->obat_id)
                ->decrement('stok_aktual', $item->jumlah_unit);

            $this->command->line("  ✓ {$item->nama_obat} × {$item->jumlah_unit} (pengambilan #{$item->pengambilan_obat_id})");
        }

        $this->command->info("BackfillStokKeluar: done. {$items->count()} records created.");
    }
}
