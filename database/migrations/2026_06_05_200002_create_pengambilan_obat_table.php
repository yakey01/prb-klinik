<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('pengambilan_obat', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pasien_id')->constrained('pasien')->onDelete('cascade');
            $table->date('tanggal_pengambilan');
            $table->date('jadwal_berikutnya')->nullable();
            $table->enum('status', ['selesai','dijadwalkan','lewat'])->default('selesai');
            $table->unsignedInteger('total_item')->default(0);
            $table->foreignId('dicatat_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->text('catatan')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('pengambilan_obat'); }
};
