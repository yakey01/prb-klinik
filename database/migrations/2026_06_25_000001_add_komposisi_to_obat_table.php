<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('obat', function (Blueprint $table) {
            // Komposisi zat aktif + kekuatan (mis. "Amoxicillin 500 mg").
            // Untuk tracking generik lintas merek dagang.
            $table->string('komposisi', 255)->nullable()->after('bentuk_sediaan');
        });
    }

    public function down(): void
    {
        Schema::table('obat', function (Blueprint $table) {
            $table->dropColumn('komposisi');
        });
    }
};
