<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Indeks performa — kolom FK & filter panas yang sebelumnya TANPA indeks
 * (full table scan di produksi). Additive, idempoten (skip bila indeks sudah ada).
 */
return new class extends Migration
{
    /** Peta tabel → daftar [nama_indeks => [kolom...]]. */
    private array $map = [
        'obat'                   => ['obat_active_tipe_idx' => ['is_active', 'tipe_obat'], 'obat_tipe_idx' => ['tipe_obat'], 'obat_kategori_idx' => ['kategori_diagnosis']],
        'purchase_orders'        => ['po_tanggal_idx' => ['tanggal_po']],
        'purchase_order_items'   => ['poi_po_idx' => ['purchase_order_id'], 'poi_obat_idx' => ['obat_id']],
        'tagihan'                => ['tag_po_idx' => ['purchase_order_id'], 'tag_status_idx' => ['status'], 'tag_jatuh_tempo_idx' => ['tanggal_jatuh_tempo'], 'tag_periode_idx' => ['periode_bulan']],
        'resep_pasien'           => ['resep_aktif_idx' => ['is_aktif'], 'resep_obat_idx' => ['obat_id'], 'resep_pasien_idx' => ['pasien_id']],
        'stok_keluar'            => ['sk_tanggal_idx' => ['tanggal_keluar'], 'sk_sumber_idx' => ['sumber'], 'sk_obat_idx' => ['obat_id']],
        'item_pengambilan'       => ['ip_obat_idx' => ['obat_id'], 'ip_pengambilan_idx' => ['pengambilan_obat_id']],
        'pengambilan_obat'       => ['po_pasien_idx' => ['pasien_id'], 'po_tgl_idx' => ['tanggal_pengambilan'], 'po_status_idx' => ['status']],
        'pasien'                 => ['pasien_aktif_idx' => ['is_aktif']],
        'pengajuan_pengadaan'    => ['pr_status_idx' => ['status'], 'pr_po_idx' => ['purchase_order_id'], 'pr_sumber_idx' => ['approver_sumber']],
    ];

    private function indexExists(string $table, string $name): bool
    {
        $row = DB::selectOne(
            'SELECT COUNT(*) AS n FROM information_schema.statistics WHERE table_schema = DATABASE() AND table_name = ? AND index_name = ?',
            [$table, $name]
        );
        return (int) ($row->n ?? 0) > 0;
    }

    public function up(): void
    {
        foreach ($this->map as $table => $indexes) {
            if (! Schema::hasTable($table)) continue;
            Schema::table($table, function (Blueprint $t) use ($table, $indexes) {
                foreach ($indexes as $name => $cols) {
                    $cols = array_values(array_filter($cols, fn ($c) => Schema::hasColumn($table, $c)));
                    if ($cols && ! $this->indexExists($table, $name)) {
                        $t->index($cols, $name);
                    }
                }
            });
        }
    }

    public function down(): void
    {
        foreach ($this->map as $table => $indexes) {
            if (! Schema::hasTable($table)) continue;
            Schema::table($table, function (Blueprint $t) use ($table, $indexes) {
                foreach ($indexes as $name => $cols) {
                    if ($this->indexExists($table, $name)) {
                        $t->dropIndex($name);
                    }
                }
            });
        }
    }
};
