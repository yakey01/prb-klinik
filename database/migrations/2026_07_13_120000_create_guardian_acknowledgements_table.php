<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Jejak konfirmasi manusia atas temuan Pharmacy Guardian AI.
 * Menyimpan keputusan staf ("aman"/"selesai"/"abaikan") beserta SIDIK JARI kondisi
 * saat dikonfirmasi. Bila data berubah (sidik jari beda), temuan muncul lagi.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('guardian_acknowledgements', function (Blueprint $t) {
            $t->id();
            $t->string('code', 40);                 // kode detektor, mis. DUP_FAKTUR
            $t->string('subject_type', 20);         // po | tagihan
            $t->unsignedBigInteger('subject_id');   // id po / tagihan
            $t->unsignedBigInteger('po_id')->nullable(); // faktur terkait (grouping)
            $t->string('fingerprint', 64);          // hash kondisi saat dikonfirmasi
            $t->string('status', 20)->default('confirmed_ok'); // confirmed_ok | resolved | ignored
            $t->string('catatan', 400)->nullable();
            $t->string('oleh')->nullable();
            $t->timestamps();

            $t->unique(['code', 'subject_type', 'subject_id'], 'guardian_ack_unik');
            $t->index('po_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('guardian_acknowledgements');
    }
};
