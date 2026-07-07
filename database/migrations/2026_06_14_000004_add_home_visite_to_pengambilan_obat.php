<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pengambilan_obat', function (Blueprint $table) {
            $table->boolean('is_home_visite')->default(false)->after('persyaratan_ok');
            $table->foreignId('home_visite_id')->nullable()->after('is_home_visite')->constrained('home_visite')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('pengambilan_obat', function (Blueprint $table) {
            $table->dropForeign(['home_visite_id']);
            $table->dropColumn(['is_home_visite', 'home_visite_id']);
        });
    }
};
