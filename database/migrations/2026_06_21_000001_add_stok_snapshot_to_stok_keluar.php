<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stok_keluar', function (Blueprint $table) {
            // Snapshot stok saat obat keluar — untuk audit "sebelum → sesudah".
            $table->integer('stok_sebelum')->nullable()->after('jumlah_unit');
            $table->integer('stok_sesudah')->nullable()->after('stok_sebelum');
        });
    }

    public function down(): void
    {
        Schema::table('stok_keluar', function (Blueprint $table) {
            $table->dropColumn(['stok_sebelum', 'stok_sesudah']);
        });
    }
};
