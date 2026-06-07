<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchase_order_items', function (Blueprint $table) {
            // Drop cascade FK and replace with restrict to protect historical PO data
            $table->dropForeign(['obat_id']);
            $table->foreign('obat_id')->references('id')->on('obat')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('purchase_order_items', function (Blueprint $table) {
            $table->dropForeign(['obat_id']);
            $table->foreign('obat_id')->references('id')->on('obat')->cascadeOnDelete();
        });
    }
};
