<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Formula lama: bayar = klaim × faktor  (faktor=1.15 → 115% klaim)
     * Formula baru: bayar = klaim × (1 + faktor)  (faktor=0.15 → 115% klaim)
     *
     * Konversi: nilai >= 1  →  nilai - 1
     *   1.15 → 0.15  (15% jasa farmasi)
     *   1.25 → 0.25  (25% jasa farmasi)
     *   1.00 → 0.00  (tanpa jasa farmasi, bayar = klaim)
     */
    public function up(): void
    {
        DB::table('obat')
            ->where('faktor_jasa_farmasi', '>=', 1)
            ->update([
                'faktor_jasa_farmasi' => DB::raw('faktor_jasa_farmasi - 1'),
            ]);
    }

    public function down(): void
    {
        DB::table('obat')
            ->where('faktor_jasa_farmasi', '>=', 0)
            ->where('faktor_jasa_farmasi', '<', 1)
            ->update([
                'faktor_jasa_farmasi' => DB::raw('faktor_jasa_farmasi + 1'),
            ]);
    }
};
