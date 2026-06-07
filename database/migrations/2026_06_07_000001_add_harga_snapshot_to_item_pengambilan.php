<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('item_pengambilan', function (Blueprint $table) {
            // Snapshot harga saat penyerahan — penting untuk historis P&L
            $table->decimal('harga_beli_snapshot', 12, 2)->default(0)->after('jumlah_unit');
            $table->decimal('harga_klaim_bpjs_snapshot', 12, 2)->default(0)->after('harga_beli_snapshot');
            $table->decimal('faktor_jasa_farmasi_snapshot', 5, 4)->default(1.15)->after('harga_klaim_bpjs_snapshot');
        });

        // Backfill dari tabel obat untuk data historis
        \DB::statement('
            UPDATE item_pengambilan ip
            JOIN obat o ON ip.obat_id = o.id
            SET ip.harga_beli_snapshot = o.harga_beli_per_unit,
                ip.harga_klaim_bpjs_snapshot = o.klaim_bpjs_per_unit,
                ip.faktor_jasa_farmasi_snapshot = o.faktor_jasa_farmasi
        ');
    }

    public function down(): void {
        Schema::table('item_pengambilan', function (Blueprint $table) {
            $table->dropColumn(['harga_beli_snapshot','harga_klaim_bpjs_snapshot','faktor_jasa_farmasi_snapshot']);
        });
    }
};
