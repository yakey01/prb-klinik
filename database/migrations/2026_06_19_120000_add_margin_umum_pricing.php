<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('obat', function (Blueprint $table) {
            // Profit faktor / margin untuk harga jual ke pasien UMUM (tunai).
            // harga_jual_per_unit = harga_beli_per_unit × (1 + margin_umum)
            $table->decimal('margin_umum', 6, 4)->default(0.2000)->after('faktor_jasa_farmasi');
        });

        // Pengaturan harga global (singleton).
        Schema::create('pengaturan_harga', function (Blueprint $table) {
            $table->id();
            $table->decimal('margin_umum_default', 6, 4)->default(0.2000); // default 20%
            $table->boolean('auto_hitung_jual')->default(true);            // auto harga jual dari margin
            $table->timestamps();
        });

        DB::table('pengaturan_harga')->insert([
            'margin_umum_default' => 0.2000,
            'auto_hitung_jual'    => true,
            'created_at'          => now(),
            'updated_at'          => now(),
        ]);
    }

    public function down(): void
    {
        Schema::table('obat', function (Blueprint $table) {
            $table->dropColumn('margin_umum');
        });
        Schema::dropIfExists('pengaturan_harga');
    }
};
