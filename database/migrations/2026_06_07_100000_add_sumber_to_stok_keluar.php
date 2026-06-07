<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stok_keluar', function (Blueprint $table) {
            $table->enum('sumber', ['manual', 'pengambilan'])->default('manual')->after('keterangan');
            $table->unsignedBigInteger('pengambilan_obat_id')->nullable()->after('sumber');
            $table->unsignedBigInteger('pasien_id')->nullable()->after('pengambilan_obat_id');
            $table->foreign('pengambilan_obat_id')->references('id')->on('pengambilan_obat')->nullOnDelete();
            $table->foreign('pasien_id')->references('id')->on('pasien')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('stok_keluar', function (Blueprint $table) {
            $table->dropForeign(['pengambilan_obat_id']);
            $table->dropForeign(['pasien_id']);
            $table->dropColumn(['sumber', 'pengambilan_obat_id', 'pasien_id']);
        });
    }
};
