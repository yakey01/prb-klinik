<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pengambilan_obat', function (Blueprint $table) {
            // Referensi resep dari RME (idempotency key) — cegah duplikat saat re-sync.
            $table->string('ref_rme', 40)->nullable()->unique()->after('catatan');
            $table->string('sumber_resep', 20)->nullable()->after('ref_rme'); // 'rme' | null
        });
    }

    public function down(): void
    {
        Schema::table('pengambilan_obat', function (Blueprint $table) {
            $table->dropUnique(['ref_rme']);
            $table->dropColumn(['ref_rme', 'sumber_resep']);
        });
    }
};
