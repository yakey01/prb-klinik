<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Jejak audit koreksi pembayaran (standar enterprise): pembayaran yang salah
 * TIDAK dihapus — dibatalkan (void) dengan alasan & pelaku, atau diedit dengan
 * catatan pengubah terakhir. Baris void dikecualikan dari Σ jumlah_dibayar.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pembayaran_tagihan', function (Blueprint $t) {
            $t->boolean('dibatalkan')->default(false)->after('dicatat_oleh');
            $t->timestamp('dibatalkan_at')->nullable()->after('dibatalkan');
            $t->string('dibatalkan_oleh', 120)->nullable()->after('dibatalkan_at');
            $t->string('alasan_batal', 300)->nullable()->after('dibatalkan_oleh');
            $t->timestamp('diubah_at')->nullable()->after('alasan_batal');
            $t->string('diubah_oleh', 120)->nullable()->after('diubah_at');
            $t->index(['tagihan_id', 'dibatalkan']);
        });
    }

    public function down(): void
    {
        Schema::table('pembayaran_tagihan', function (Blueprint $t) {
            $t->dropIndex(['tagihan_id', 'dibatalkan']);
            $t->dropColumn(['dibatalkan', 'dibatalkan_at', 'dibatalkan_oleh', 'alasan_batal', 'diubah_at', 'diubah_oleh']);
        });
    }
};
