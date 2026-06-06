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
        Schema::create('tagihan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_order_id')->constrained('purchase_orders')->cascadeOnDelete();
            $table->foreignId('distributor_id')->constrained('distributors');
            $table->string('nomor_tagihan', 30)->unique();
            $table->enum('tipe_obat', ['kronis', 'non_kronis']);
            $table->string('periode_bulan', 7);           // YYYY-MM
            $table->date('tanggal_tagihan');
            $table->date('tanggal_jatuh_tempo');
            $table->unsignedBigInteger('total_tagihan')->default(0);
            $table->enum('status', ['draft', 'belum_bayar', 'sebagian', 'lunas'])->default('draft');
            $table->date('tanggal_bayar')->nullable();
            $table->unsignedBigInteger('jumlah_dibayar')->default(0);
            $table->text('catatan_bayar')->nullable();
            $table->timestamps();

            $table->index(['distributor_id', 'status']);
            $table->index(['periode_bulan', 'tipe_obat']);
            $table->index('tanggal_jatuh_tempo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tagihan');
    }
};
