<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('home_visite', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pasien_id')->constrained('pasien')->cascadeOnDelete();
            $table->foreignId('assigned_to')->constrained('users')->cascadeOnDelete();
            $table->foreignId('assigned_by')->constrained('users')->cascadeOnDelete();
            $table->foreignId('pengambilan_obat_id')->nullable()->constrained('pengambilan_obat')->nullOnDelete();
            $table->date('tanggal_visite');
            $table->string('alamat_tujuan', 500);
            $table->decimal('lat_tujuan', 10, 7)->nullable();
            $table->decimal('lng_tujuan', 10, 7)->nullable();
            $table->enum('status', ['ditugaskan', 'dalam_perjalanan', 'sampai', 'selesai', 'dibatalkan'])->default('ditugaskan');
            $table->text('catatan_admin')->nullable();
            $table->text('catatan_karyawan')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('arrived_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->string('foto_bukti')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('home_visite');
    }
};
