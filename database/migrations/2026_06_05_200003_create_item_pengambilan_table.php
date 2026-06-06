<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('item_pengambilan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pengambilan_obat_id')->constrained('pengambilan_obat')->onDelete('cascade');
            $table->foreignId('obat_id')->constrained('obat')->onDelete('cascade');
            $table->unsignedInteger('jumlah_unit')->default(1);
            $table->string('catatan', 255)->nullable();
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('item_pengambilan'); }
};
