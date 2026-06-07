<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('notifikasi_settings', function (Blueprint $table) {
            $table->string('wa_endpoint_url', 255)->nullable()->after('wa_sender_number');
        });
    }

    public function down(): void
    {
        Schema::table('notifikasi_settings', function (Blueprint $table) {
            $table->dropColumn('wa_endpoint_url');
        });
    }
};
