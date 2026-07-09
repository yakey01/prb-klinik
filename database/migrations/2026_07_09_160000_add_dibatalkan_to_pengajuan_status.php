<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * K4: tambah status 'dibatalkan' pada pengajuan_pengadaan agar pengajuan
 * 'diajukan'/'disetujui' yang batal beli bisa ditarik/dibatalkan (bukan dead-end).
 * Idempoten.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('pengajuan_pengadaan')) return;
        $col = DB::selectOne("SELECT COLUMN_TYPE ct FROM information_schema.columns WHERE table_schema=DATABASE() AND table_name='pengajuan_pengadaan' AND column_name='status'");
        if ($col && ! str_contains((string) $col->ct, 'dibatalkan')) {
            DB::statement("ALTER TABLE pengajuan_pengadaan MODIFY status ENUM('draft','diajukan','disetujui','ditolak','revisi','direalisasi','dibatalkan') NOT NULL DEFAULT 'draft'");
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('pengajuan_pengadaan')) return;
        DB::table('pengajuan_pengadaan')->where('status', 'dibatalkan')->update(['status' => 'ditolak']);
        DB::statement("ALTER TABLE pengajuan_pengadaan MODIFY status ENUM('draft','diajukan','disetujui','ditolak','revisi','direalisasi') NOT NULL DEFAULT 'draft'");
    }
};
