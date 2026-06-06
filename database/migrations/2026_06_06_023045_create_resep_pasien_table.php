<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('resep_pasien', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pasien_id')->constrained('pasien')->cascadeOnDelete();
            $table->foreignId('obat_id')->constrained('obat');
            $table->unsignedSmallInteger('jumlah_default')->default(30);
            $table->string('satuan', 50)->default('tablet');
            $table->text('catatan')->nullable();
            $table->unsignedSmallInteger('urutan')->default(0);
            $table->boolean('is_aktif')->default(true);
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('resep_pasien'); }
};
