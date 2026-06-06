<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('notifikasi_settings', function (Blueprint $table) {
            $table->id();
            $table->string('wa_provider', 50)->default('fonnte');
            $table->text('wa_api_key')->nullable();
            $table->string('wa_sender_number', 20)->nullable();
            $table->text('telegram_bot_token')->nullable();
            $table->string('telegram_chat_id_staff', 50)->nullable();
            $table->time('jam_kirim')->default('08:00:00');
            $table->boolean('is_aktif_wa')->default(false);
            $table->boolean('is_aktif_telegram')->default(false);
            $table->text('template_h1')->nullable();
            $table->text('template_harian')->nullable();
            $table->text('template_overdue')->nullable();
            $table->timestamps();
        });

        // Seed default row
        \DB::table('notifikasi_settings')->insert([
            'wa_provider'    => 'fonnte',
            'jam_kirim'      => '08:00:00',
            'is_aktif_wa'    => false,
            'is_aktif_telegram' => false,
            'template_h1'    => "🏥 *Klinik Dokterku - Pengingat Ambil Obat*\n\nYth. {nama},\n\nIni pengingat bahwa *besok ({tanggal})* adalah jadwal Anda mengambil obat PRB untuk *{diagnosa}*.\n\nMohon hadir tepat waktu agar ketersediaan obat terjamin.\n\nTerima kasih.\n_Apoteker Klinik Dokterku_",
            'template_harian' => "🏥 *Klinik Dokterku*\n\nYth. {nama},\n\nHari ini adalah jadwal pengambilan obat PRB Anda untuk *{diagnosa}*.\n\nSilakan datang ke apotek kami. Obat sudah siap.\n\n_Apoteker Klinik Dokterku_",
            'template_overdue' => "⚠️ *Klinik Dokterku - Pengingat Penting*\n\nYth. {nama},\n\nJadwal ambil obat PRB ({diagnosa}) Anda pada {tanggal} belum terkonfirmasi.\n\nMohon segera hubungi klinik atau datang untuk pengambilan obat.\n\n_Apoteker Klinik Dokterku_",
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('notifikasi_settings');
    }
};
