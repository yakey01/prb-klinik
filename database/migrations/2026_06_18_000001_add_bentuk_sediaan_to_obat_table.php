<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('obat', function (Blueprint $table) {
            if (! Schema::hasColumn('obat', 'bentuk_sediaan')) {
                $table->string('bentuk_sediaan', 60)->nullable()->after('satuan');
            }
        });
    }

    public function down(): void
    {
        Schema::table('obat', function (Blueprint $table) {
            if (Schema::hasColumn('obat', 'bentuk_sediaan')) {
                $table->dropColumn('bentuk_sediaan');
            }
        });
    }
};
