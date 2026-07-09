<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * K3: `purchase_order_items.tipe_obat` sebelumnya enum('kronis','non_kronis') —
 * tidak punya 'bmhp' padahal obat & pengajuan sudah mendukung BMHP. Akibatnya
 * realisasi pengajuan berisi item BMHP gagal/rollback atau truncate diam.
 * Migrasi menyamakan enum dengan obat/pengajuan. Idempoten.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('purchase_order_items')) return;
        $col = DB::selectOne("SELECT COLUMN_TYPE ct FROM information_schema.columns WHERE table_schema=DATABASE() AND table_name='purchase_order_items' AND column_name='tipe_obat'");
        if ($col && ! str_contains((string) $col->ct, 'bmhp')) {
            DB::statement("ALTER TABLE purchase_order_items MODIFY tipe_obat ENUM('kronis','non_kronis','bmhp') NOT NULL DEFAULT 'kronis'");
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('purchase_order_items')) return;
        // Turunkan bmhp → non_kronis sebelum menyempitkan enum (hindari data hilang).
        DB::table('purchase_order_items')->where('tipe_obat', 'bmhp')->update(['tipe_obat' => 'non_kronis']);
        DB::statement("ALTER TABLE purchase_order_items MODIFY tipe_obat ENUM('kronis','non_kronis') NOT NULL DEFAULT 'kronis'");
    }
};
