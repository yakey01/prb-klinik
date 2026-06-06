<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('obat', function (Blueprint $table) {
            $table->id();
            $table->string('nama_obat');
            $table->string('kode_obat')->nullable();
            $table->string('kategori_diagnosis');
            $table->integer('jumlah_pasien')->default(0);
            $table->decimal('unit_per_bulan', 10, 2)->default(0);
            $table->decimal('harga_beli_per_unit', 15, 2)->default(0);
            $table->enum('sumber_harga', ['PO', 'REAL', 'EST'])->default('EST');
            $table->decimal('klaim_bpjs_per_unit', 15, 2)->default(0);
            $table->decimal('faktor_jasa_farmasi', 8, 4)->default(1.0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('obat');
    }
};
