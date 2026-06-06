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
        Schema::create('biaya_operasional', function (Blueprint $table) {
            $table->id();
            $table->tinyInteger('bulan');
            $table->year('tahun');
            $table->decimal('biaya_sdm', 15, 2)->default(0);
            $table->decimal('biaya_utilitas', 15, 2)->default(0);
            $table->decimal('biaya_administrasi', 15, 2)->default(0);
            $table->decimal('biaya_sewa', 15, 2)->default(0);
            $table->decimal('biaya_lainnya', 15, 2)->default(0);
            $table->unique(['bulan', 'tahun']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('biaya_operasional');
    }
};
