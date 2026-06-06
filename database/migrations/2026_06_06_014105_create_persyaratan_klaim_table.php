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
        Schema::create('persyaratan_klaim', function (Blueprint $table) {
            $table->id();
            $table->string('diagnosis', 100);
            $table->string('nama_syarat', 200);
            $table->text('deskripsi')->nullable();
            $table->enum('tipe', ['lab', 'dokumen', 'pemeriksaan'])->default('dokumen');
            $table->unsignedTinyInteger('periode_bulan')->default(1);
            $table->boolean('is_wajib')->default(true);
            $table->unsignedSmallInteger('urutan')->default(0);
            $table->boolean('is_aktif')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('persyaratan_klaim');
    }
};
