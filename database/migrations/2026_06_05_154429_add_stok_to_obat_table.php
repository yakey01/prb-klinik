<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::table('obat', function (Blueprint $table) {
            $table->integer('stok_aktual')->default(0)->after('is_active');
            $table->integer('stok_minimum')->default(10)->after('stok_aktual');
            $table->date('tanggal_kadaluarsa')->nullable()->after('stok_minimum');
        });
    }
    public function down(): void {
        Schema::table('obat', function (Blueprint $table) {
            $table->dropColumn(['stok_aktual','stok_minimum','tanggal_kadaluarsa']);
        });
    }
};
