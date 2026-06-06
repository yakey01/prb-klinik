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
        Schema::table('purchase_orders', function (Blueprint $table) {
            // status_bayar sudah ada dari migration sebelumnya, skip jika sudah ada
            if (!Schema::hasColumn('purchase_orders', 'status_bayar')) {
                $table->enum('status_bayar', ['belum', 'sebagian', 'lunas'])->default('belum')->after('total_nilai');
                $table->unsignedBigInteger('sisa_tagihan')->default(0)->after('status_bayar');
            }
        });
    }

    public function down(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->dropColumn(['status_bayar', 'sisa_tagihan']);
        });
    }
};
