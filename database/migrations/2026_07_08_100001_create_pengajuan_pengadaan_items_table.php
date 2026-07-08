<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Item pengajuan pengadaan. Struktur harga selaras dengan purchase_order_items
 * agar realisasi pengajuan → PO memetakan 1:1. Menyimpan snapshot klaim BPJS +
 * jasa farmasi untuk obat kronis (data izin lengkap kelas RS).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pengajuan_pengadaan_items', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('pengajuan_pengadaan_id');
            $t->unsignedBigInteger('obat_id')->nullable();       // null = obat baru (belum di katalog)
            $t->string('nama_obat');                             // snapshot / obat baru
            $t->enum('tipe_obat', ['kronis', 'non_kronis', 'bmhp'])->default('kronis');

            $t->integer('jumlah_box')->default(1);
            $t->integer('isi_per_box')->default(1);
            $t->decimal('harga_per_box', 15, 2)->default(0);     // harga BELI per box
            $t->decimal('harga_per_unit', 15, 2)->default(0);    // = harga_per_box / isi_per_box
            $t->decimal('subtotal_beli', 15, 2)->default(0);     // = jumlah_box * harga_per_box

            // Snapshot klaim (untuk kronis) — data izin: berapa penggantian BPJS-nya
            $t->decimal('klaim_bpjs_per_unit', 15, 2)->default(0);
            $t->decimal('faktor_jasa_farmasi', 8, 4)->nullable();
            $t->decimal('estimasi_klaim', 15, 2)->default(0);    // potensi klaim/jual total item

            $t->date('tanggal_kadaluarsa')->nullable();
            $t->string('catatan')->nullable();
            $t->timestamps();

            $t->index('pengajuan_pengadaan_id');
            $t->foreign('pengajuan_pengadaan_id')->references('id')->on('pengajuan_pengadaan')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pengajuan_pengadaan_items');
    }
};
