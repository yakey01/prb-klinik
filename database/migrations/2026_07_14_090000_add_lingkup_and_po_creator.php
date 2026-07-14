<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Lingkup obat per user (kronis / non_kronis / keduanya) + jejak pembuat PO.
 * User hanya boleh mengajukan/mengadakan obat sesuai lingkupnya, dan setiap
 * PO merekam siapa pembuatnya agar tampil di Riwayat PO.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $t) {
            if (! Schema::hasColumn('users', 'lingkup_obat')) {
                $t->enum('lingkup_obat', ['kronis', 'non_kronis', 'keduanya'])
                    ->default('keduanya')->after('role');
            }
        });

        Schema::table('purchase_orders', function (Blueprint $t) {
            if (! Schema::hasColumn('purchase_orders', 'dibuat_oleh_id')) {
                $t->unsignedBigInteger('dibuat_oleh_id')->nullable()->after('catatan');
            }
            if (! Schema::hasColumn('purchase_orders', 'dibuat_oleh_nama')) {
                $t->string('dibuat_oleh_nama')->nullable()->after('dibuat_oleh_id');
            }
            if (! Schema::hasColumn('purchase_orders', 'sumber')) {
                $t->string('sumber', 20)->nullable()->after('dibuat_oleh_nama'); // pengajuan | langsung
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', fn (Blueprint $t) => $t->dropColumn('lingkup_obat'));
        Schema::table('purchase_orders', fn (Blueprint $t) => $t->dropColumn(['dibuat_oleh_id', 'dibuat_oleh_nama', 'sumber']));
    }
};
