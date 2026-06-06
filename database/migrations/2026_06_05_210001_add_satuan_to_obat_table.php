<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('obat', function (Blueprint $table) {
            $table->string('satuan', 20)->default('tablet')->after('unit_per_bulan');
        });
        Schema::table('item_pengambilan', function (Blueprint $table) {
            $table->string('satuan', 20)->default('tablet')->after('jumlah_unit');
        });
    }
    public function down(): void {
        Schema::table('obat', fn($t) => $t->dropColumn('satuan'));
        Schema::table('item_pengambilan', fn($t) => $t->dropColumn('satuan'));
    }
};
