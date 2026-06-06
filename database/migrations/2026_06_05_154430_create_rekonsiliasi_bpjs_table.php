<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('rekonsiliasi_bpjs', function (Blueprint $table) {
            $table->id();
            $table->tinyInteger('bulan');
            $table->smallInteger('tahun');
            $table->decimal('proyeksi_pendapatan', 15, 2)->default(0);
            $table->decimal('tagihan_diajukan', 15, 2)->default(0);
            $table->decimal('tagihan_dibayar', 15, 2)->default(0);
            $table->enum('status', ['draft','diajukan','dibayar','selisih'])->default('draft');
            $table->text('catatan')->nullable();
            $table->date('tanggal_pengajuan')->nullable();
            $table->date('tanggal_pembayaran')->nullable();
            $table->timestamps();
            $table->unique(['bulan','tahun']);
        });
    }
    public function down(): void { Schema::dropIfExists('rekonsiliasi_bpjs'); }
};
