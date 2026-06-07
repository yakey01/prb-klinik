<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // pasien — frequent filter columns
        Schema::table('pasien', function (Blueprint $table) {
            if (!$this->hasIndex('pasien', 'pasien_is_aktif_index')) {
                $table->index('is_aktif', 'pasien_is_aktif_index');
            }
            if (!$this->hasIndex('pasien', 'pasien_kategori_diagnosis_index')) {
                $table->index('kategori_diagnosis', 'pasien_kategori_diagnosis_index');
            }
        });

        // item_pengambilan — FK indexes often missing
        Schema::table('item_pengambilan', function (Blueprint $table) {
            if (!$this->hasIndex('item_pengambilan', 'item_pengambilan_pengambilan_obat_id_index')) {
                $table->index('pengambilan_obat_id', 'item_pengambilan_pengambilan_obat_id_index');
            }
            if (!$this->hasIndex('item_pengambilan', 'item_pengambilan_obat_id_index')) {
                $table->index('obat_id', 'item_pengambilan_obat_id_index');
            }
        });

        // pengambilan_obat — pasien_id and tanggal_pengambilan for sorting/filtering
        Schema::table('pengambilan_obat', function (Blueprint $table) {
            if (!$this->hasIndex('pengambilan_obat', 'pengambilan_obat_pasien_id_index')) {
                $table->index('pasien_id', 'pengambilan_obat_pasien_id_index');
            }
            if (!$this->hasIndex('pengambilan_obat', 'pengambilan_obat_tanggal_index')) {
                $table->index('tanggal_pengambilan', 'pengambilan_obat_tanggal_index');
            }
        });

        // stok_keluar — tanggal for range queries in dashboard
        Schema::table('stok_keluar', function (Blueprint $table) {
            if (!$this->hasIndex('stok_keluar', 'stok_keluar_tanggal_keluar_index')) {
                $table->index('tanggal_keluar', 'stok_keluar_tanggal_keluar_index');
            }
            if (!$this->hasIndex('stok_keluar', 'stok_keluar_obat_id_index')) {
                $table->index('obat_id', 'stok_keluar_obat_id_index');
            }
        });

        // obat — is_active and tipe_obat for katalog/kebutuhan filtering
        Schema::table('obat', function (Blueprint $table) {
            if (!$this->hasIndex('obat', 'obat_is_active_tipe_index')) {
                $table->index(['is_active', 'tipe_obat'], 'obat_is_active_tipe_index');
            }
        });

        // purchase_order_items — FK indexes
        Schema::table('purchase_order_items', function (Blueprint $table) {
            if (!$this->hasIndex('purchase_order_items', 'poi_obat_id_index')) {
                $table->index('obat_id', 'poi_obat_id_index');
            }
        });
    }

    public function down(): void
    {
        Schema::table('pasien', function (Blueprint $table) {
            $table->dropIndex('pasien_is_aktif_index');
            $table->dropIndex('pasien_kategori_diagnosis_index');
        });
        Schema::table('item_pengambilan', function (Blueprint $table) {
            $table->dropIndex('item_pengambilan_pengambilan_obat_id_index');
            $table->dropIndex('item_pengambilan_obat_id_index');
        });
        Schema::table('pengambilan_obat', function (Blueprint $table) {
            $table->dropIndex('pengambilan_obat_pasien_id_index');
            $table->dropIndex('pengambilan_obat_tanggal_index');
        });
        Schema::table('stok_keluar', function (Blueprint $table) {
            $table->dropIndex('stok_keluar_tanggal_keluar_index');
            $table->dropIndex('stok_keluar_obat_id_index');
        });
        Schema::table('obat', function (Blueprint $table) {
            $table->dropIndex('obat_is_active_tipe_index');
        });
        Schema::table('purchase_order_items', function (Blueprint $table) {
            $table->dropIndex('poi_obat_id_index');
        });
    }

    private function hasIndex(string $table, string $index): bool
    {
        return collect(\DB::select("SHOW INDEX FROM `{$table}`"))
            ->pluck('Key_name')
            ->contains($index);
    }
};
