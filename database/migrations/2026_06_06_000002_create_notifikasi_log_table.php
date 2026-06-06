<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('notifikasi_log', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pasien_id')->nullable()->constrained('pasien')->nullOnDelete();
            $table->foreignId('pengambilan_id')->nullable()->constrained('pengambilan_obat')->nullOnDelete();
            $table->enum('channel', ['wa', 'telegram'])->default('wa');
            $table->string('nomor_tujuan', 50)->default('');
            $table->text('pesan');
            $table->enum('status', ['pending', 'sent', 'failed', 'skipped'])->default('pending');
            $table->enum('tipe', ['H1', 'HARIAN', 'KONFIRMASI', 'TEST', 'BROADCAST'])->default('HARIAN');
            $table->timestamp('sent_at')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamps();

            $table->index(['pasien_id', 'created_at']);
            $table->index(['status', 'channel']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifikasi_log');
    }
};
