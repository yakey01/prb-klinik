<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Tambah tipe_obat + harga_jual ke tabel obat
        Schema::table('obat', function (Blueprint $table) {
            $table->enum('tipe_obat', ['kronis', 'non_kronis'])->default('kronis')->after('kategori_diagnosis');
            $table->decimal('harga_jual_per_unit', 12, 2)->nullable()->after('harga_beli_per_unit')
                  ->comment('Harga jual ke pasien — khusus obat non-kronis');
        });

        // 2. Snapshot tipe di purchase_order_items
        Schema::table('purchase_order_items', function (Blueprint $table) {
            $table->enum('tipe_obat', ['kronis', 'non_kronis'])->default('kronis')->after('obat_id');
        });

        // 3. Backfill snapshot dari master obat
        DB::statement('UPDATE purchase_order_items poi
            JOIN obat o ON poi.obat_id = o.id
            SET poi.tipe_obat = o.tipe_obat');

        // 4. Tabel stok_keluar: pencatatan obat non-kronis keluar
        Schema::create('stok_keluar', function (Blueprint $table) {
            $table->id();
            $table->foreignId('obat_id')->constrained('obat')->restrictOnDelete();
            $table->date('tanggal_keluar');
            $table->integer('jumlah_unit');
            $table->string('satuan', 50)->default('tablet');
            $table->decimal('harga_beli_snapshot', 12, 2)->default(0);
            $table->decimal('harga_jual_per_unit', 12, 2);
            $table->string('keterangan', 255)->nullable();
            $table->foreignId('dicatat_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['tanggal_keluar', 'obat_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stok_keluar');

        Schema::table('purchase_order_items', function (Blueprint $table) {
            $table->dropColumn('tipe_obat');
        });

        Schema::table('obat', function (Blueprint $table) {
            $table->dropColumn(['tipe_obat', 'harga_jual_per_unit']);
        });
    }
};
