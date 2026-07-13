<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Koreksi Purchase Order — usulan perbaikan qty/harga/faktur PO yang WAJIB disetujui
 * manajer (di SIM) sebelum diterapkan. Payload menyimpan detail perubahan (before/after)
 * agar manajer bisa menilai. Rekonsiliasi stok/tagihan dijalankan APOTIK saat status
 * 'disetujui' & belum applied (logika hanya di apotik, SIM cukup setujui/tolak).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('koreksi_po', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('purchase_order_id');
            $t->string('pemohon_nama', 120)->nullable();
            $t->unsignedBigInteger('pemohon_id')->nullable();
            $t->string('alasan', 300);
            $t->decimal('total_lama', 15, 2)->default(0);
            $t->decimal('total_baru', 15, 2)->default(0);
            $t->json('payload');                       // {faktur, tanggal_po, rows:[{item_id,obat_id,nama,tipe,ori_box,ori_isi,ori_harga,box,isi,harga,expiry,hapus}]}
            $t->enum('status', ['diajukan', 'disetujui', 'ditolak'])->default('diajukan');
            $t->string('approver_nama', 120)->nullable();
            $t->unsignedBigInteger('approver_id')->nullable();
            $t->timestamp('approved_at')->nullable();
            $t->string('alasan_tolak', 300)->nullable();
            $t->boolean('applied')->default(false);    // sudah direkonsiliasi ke stok/tagihan?
            $t->timestamp('applied_at')->nullable();
            $t->timestamps();

            $t->index(['status', 'applied']);
            $t->index('purchase_order_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('koreksi_po');
    }
};
