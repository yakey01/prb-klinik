<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Arsip pembayaran tagihan (standar enterprise) — SATU baris per pembayaran
 * (mendukung cicilan/parsial + jejak audit). Menyimpan metode, bank, nomor
 * referensi, waktu, dan LINK BUKTI TRANSFER (wajib untuk non-tunai).
 * `tagihan.jumlah_dibayar` = Σ pembayaran (sumber kebenaran).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pembayaran_tagihan', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('tagihan_id');
            $t->date('tanggal');                                   // tanggal bayar
            $t->time('waktu')->nullable();                         // jam bayar
            $t->enum('metode', ['transfer_bank', 'tunai', 'qris', 'giro', 'cek', 'lainnya'])->default('transfer_bank');
            $t->string('bank_nama', 60)->nullable();               // bank pengirim/tujuan
            $t->string('nomor_referensi', 100)->nullable();        // no. transaksi/transfer/ref
            $t->string('rekening_tujuan', 60)->nullable();         // no. rekening PBF
            $t->string('atas_nama', 120)->nullable();              // nama pemilik rekening
            $t->decimal('jumlah', 15, 2)->default(0);
            $t->string('link_bukti', 600)->nullable();             // URL bukti transfer (Google Drive dll) — wajib non-tunai
            $t->string('link_faktur', 600)->nullable();            // URL faktur pembelian/pajak (scan) — wajib kecuali pemutihan
            $t->boolean('pemutihan')->default(false);              // centang: pembebasan faktur → tak wajib link faktur
            $t->text('catatan')->nullable();
            $t->string('dicatat_oleh', 120)->nullable();
            $t->timestamps();

            $t->index('tagihan_id');
            $t->index(['metode', 'tanggal']);
            $t->foreign('tagihan_id')->references('id')->on('tagihan')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pembayaran_tagihan');
    }
};
