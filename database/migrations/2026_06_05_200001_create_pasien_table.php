<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('pasien', function (Blueprint $table) {
            $table->id();
            $table->string('nama', 150);
            $table->string('no_bpjs', 20)->unique()->nullable();
            $table->string('kategori_diagnosis', 100)->nullable();
            $table->string('telepon', 20)->nullable();
            $table->string('alamat', 255)->nullable();
            $table->date('tanggal_lahir')->nullable();
            $table->enum('jenis_kelamin', ['L','P'])->nullable();
            $table->boolean('is_aktif')->default(true);
            $table->text('catatan')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('pasien'); }
};
