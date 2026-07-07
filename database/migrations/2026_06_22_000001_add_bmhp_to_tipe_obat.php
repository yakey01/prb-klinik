<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Tambah 'bmhp' (Bahan Medis Habis Pakai) ke enum tipe_obat — reuse infra stok & bridge SIM.
        DB::statement("ALTER TABLE obat MODIFY tipe_obat ENUM('kronis','non_kronis','bmhp') NOT NULL DEFAULT 'kronis'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE obat MODIFY tipe_obat ENUM('kronis','non_kronis') NOT NULL DEFAULT 'kronis'");
    }
};
