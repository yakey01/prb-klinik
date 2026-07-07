<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('obat', function (Blueprint $table) {
            // Isi per box (item per box) — konversi box ↔ item.
            // Stok & harga disimpan dalam ITEM (satuan terkecil) agar decrement resep real-time akurat.
            $table->unsignedInteger('isi_per_box')->default(1)->after('satuan');
        });
    }

    public function down(): void
    {
        Schema::table('obat', function (Blueprint $table) {
            $table->dropColumn('isi_per_box');
        });
    }
};
