<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stok_keluar', function (Blueprint $table) {
            if (! Schema::hasColumn('stok_keluar', 'ref')) {
                // ref idempotensi dari sumber luar (mis. SIM RME no_resep) — cegah dobel potong.
                // TIDAK unique: 1 resep = banyak baris (per obat) ber-ref sama; dedupe per-batch di controller.
                $table->string('ref', 60)->nullable()->index()->after('id');
            }
        });

        // Tambah 'sim_resep' ke enum sumber (stok keluar dari SIM RME).
        DB::statement("ALTER TABLE stok_keluar MODIFY sumber ENUM('manual','pengambilan','sim_resep') NOT NULL DEFAULT 'manual'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE stok_keluar MODIFY sumber ENUM('manual','pengambilan') NOT NULL DEFAULT 'manual'");
        Schema::table('stok_keluar', function (Blueprint $table) {
            if (Schema::hasColumn('stok_keluar', 'ref')) {
                $table->dropColumn('ref');
            }
        });
    }
};
