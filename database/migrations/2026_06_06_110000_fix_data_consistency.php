<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // FIX 1: tipe_obat di purchase_order_items harus sinkron dengan obat master
        // 23 rows mismatch: POI bilang non_kronis, obat master bilang kronis
        DB::statement("
            UPDATE purchase_order_items poi
            JOIN obat o ON poi.obat_id = o.id
            SET poi.tipe_obat = o.tipe_obat
            WHERE poi.tipe_obat != o.tipe_obat
        ");

        // FIX 2: tagihan.total_tagihan dan jumlah_dibayar pakai DECIMAL untuk konsistensi
        // bigint OK untuk Rupiah (tak butuh desimal), tapi inconsistent dengan tabel lain
        Schema::table('tagihan', function (Blueprint $table) {
            $table->decimal('total_tagihan', 15, 2)->default(0)->change();
            $table->decimal('jumlah_dibayar', 15, 2)->default(0)->change();
        });

        // FIX 3: pengambilan_obat yang status selesai tapi tanpa jadwal_berikutnya
        // set jadwal 30 hari dari tanggal pengambilan
        DB::statement("
            UPDATE pengambilan_obat
            SET jadwal_berikutnya = DATE_ADD(tanggal_pengambilan, INTERVAL 30 DAY)
            WHERE status = 'selesai' AND jadwal_berikutnya IS NULL
        ");

        // FIX 4: tambah composite index (bulan, tahun) di rekonsiliasi sudah ada (unique)
        // tambah index (tanggal_bayar) di tagihan untuk filter laporan
        Schema::table('tagihan', function (Blueprint $table) {
            if (!collect(DB::select("SHOW INDEX FROM tagihan"))->pluck('Key_name')->contains('tagihan_tanggal_bayar_index')) {
                $table->index('tanggal_bayar');
            }
        });

        // FIX 5: distributor.phone harusnya varchar(20) bukan varchar(255)
        Schema::table('distributors', function (Blueprint $table) {
            $table->string('phone', 20)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('tagihan', function (Blueprint $table) {
            $table->bigInteger('total_tagihan')->default(0)->change();
            $table->bigInteger('jumlah_dibayar')->default(0)->change();
        });
        Schema::table('distributors', function (Blueprint $table) {
            $table->string('phone', 255)->nullable()->change();
        });
    }
};
