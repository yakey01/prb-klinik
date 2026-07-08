<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Pengajuan Pengadaan (Purchase Requisition) — standar RS: usulan belanja obat
 * yang WAJIB disetujui manajer (di SIM) sebelum direalisasikan jadi Purchase Order.
 * Tabel ini juga menjadi KONTRAK BRIDGE: manajer di SIM membaca/menulis status via
 * koneksi 'apotik' (sama seperti reconcile-sim-stock).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pengajuan_pengadaan', function (Blueprint $t) {
            $t->id();
            $t->string('no_pengajuan', 32)->unique();          // PR-YYYYMM-####
            $t->date('tanggal');
            $t->unsignedBigInteger('pemohon_id')->nullable();   // users.id apotek
            $t->string('pemohon_nama')->nullable();             // snapshot
            $t->unsignedBigInteger('distributor_id')->nullable(); // usulan PBF (opsional)
            $t->enum('prioritas', ['rutin', 'segera', 'urgent'])->default('rutin');
            $t->text('justifikasi')->nullable();                // alasan belanja (stok kritis, dll)

            // ── State machine ──
            // draft → diajukan → (manajer SIM) disetujui | ditolak | revisi → diajukan … → direalisasi
            $t->enum('status', ['draft', 'diajukan', 'disetujui', 'ditolak', 'revisi', 'direalisasi'])
              ->default('draft')->index();

            // ── Ringkasan finansial (snapshot dari item) ──
            $t->decimal('total_beli', 15, 2)->default(0);           // HPP
            $t->decimal('total_estimasi_klaim', 15, 2)->default(0); // potensi klaim/jual
            $t->decimal('estimasi_laba', 15, 2)->default(0);
            $t->text('catatan')->nullable();

            // ── Approval (ditulis oleh manajer SIM via koneksi apotik / aksi lokal) ──
            $t->unsignedBigInteger('approver_id')->nullable();
            $t->string('approver_nama')->nullable();
            $t->string('approver_sumber', 16)->nullable();      // 'SIM' | 'APOTIK'
            $t->timestamp('approved_at')->nullable();
            $t->text('catatan_approver')->nullable();
            $t->text('alasan_tolak')->nullable();

            // ── Jejak ──
            $t->timestamp('submitted_at')->nullable();          // saat diajukan
            $t->unsignedBigInteger('purchase_order_id')->nullable(); // saat direalisasi
            $t->unsignedBigInteger('created_by')->nullable();
            $t->timestamps();

            $t->index(['status', 'tanggal']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pengajuan_pengadaan');
    }
};
