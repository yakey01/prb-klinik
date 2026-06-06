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
        Schema::table('pengambilan_obat', function (Blueprint $table) {
            $table->json('checklist_json')->nullable()->after('catatan');
            $table->boolean('persyaratan_ok')->nullable()->after('checklist_json');
        });
    }

    public function down(): void
    {
        Schema::table('pengambilan_obat', function (Blueprint $table) {
            $table->dropColumn(['checklist_json', 'persyaratan_ok']);
        });
    }
};
