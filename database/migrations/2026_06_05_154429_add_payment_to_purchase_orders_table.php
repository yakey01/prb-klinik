<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->enum('status_bayar', ['belum','sebagian','lunas'])->default('belum')->after('catatan');
            $table->date('tanggal_jatuh_tempo')->nullable()->after('status_bayar');
            $table->date('tanggal_bayar')->nullable()->after('tanggal_jatuh_tempo');
            $table->decimal('jumlah_bayar', 15, 2)->default(0)->after('tanggal_bayar');
        });
    }
    public function down(): void {
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->dropColumn(['status_bayar','tanggal_jatuh_tempo','tanggal_bayar','jumlah_bayar']);
        });
    }
};
