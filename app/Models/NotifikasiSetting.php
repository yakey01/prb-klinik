<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotifikasiSetting extends Model
{
    protected $table = 'notifikasi_settings';
    protected $fillable = [
        'wa_provider', 'wa_api_key', 'wa_sender_number', 'wa_endpoint_url',
        'telegram_bot_token', 'telegram_chat_id_staff',
        'jam_kirim', 'is_aktif_wa', 'is_aktif_telegram',
        'template_h1', 'template_harian', 'template_overdue',
    ];
    protected $casts = [
        'is_aktif_wa'       => 'boolean',
        'is_aktif_telegram' => 'boolean',
    ];

    public static function getSetting(): self
    {
        return static::firstOrCreate([], [
            'wa_provider'    => 'fonnte',
            'jam_kirim'      => '08:00:00',
            'is_aktif_wa'    => false,
            'is_aktif_telegram' => false,
            'template_h1'     => "🏥 *Klinik Dokterku – Pengingat Pengambilan Obat PRB*\n\nAssalamu'alaikum warahmatullahi wabarakatuh.\n\nYth. {nama},\n\nKami dari Klinik Dokterku Mojo Kediri ingin mengingatkan bahwa besok, {hari_tanggal}, merupakan jadwal {sapaan_formal} untuk mengambil obat Program Rujuk Balik (PRB) untuk terapi {diagnosa}.\n\nMohon berkenan hadir sesuai jadwal agar ketersediaan obat dapat kami siapkan dengan baik dan pelayanan dapat berjalan lancar.\n\nApabila terdapat kendala atau berhalangan hadir, mohon menghubungi petugas Klinik Dokterku.\n\nTerima kasih atas perhatian dan kerja sama {sapaan_formal}.\n\nWassalamu'alaikum warahmatullahi wabarakatuh.\n\nHormat kami,\n\nApoteker Klinik Dokterku Mojo Kediri",
            'template_harian' => "🏥 *Klinik Dokterku – Jadwal Pengambilan Obat PRB Hari Ini*\n\nAssalamu'alaikum warahmatullahi wabarakatuh.\n\nYth. {nama},\n\nKami dari Klinik Dokterku Mojo Kediri mengingatkan bahwa hari ini, {hari_tanggal}, adalah jadwal {sapaan_formal} untuk mengambil obat Program Rujuk Balik (PRB) untuk terapi {diagnosa}.\n\nObat PRB {sapaan_formal} telah kami siapkan. Mohon berkenan hadir sesuai jadwal.\n\nApabila terdapat kendala, mohon segera menghubungi petugas Klinik Dokterku.\n\nTerima kasih atas perhatian dan kerja sama {sapaan_formal}.\n\nWassalamu'alaikum warahmatullahi wabarakatuh.\n\nHormat kami,\n\nApoteker Klinik Dokterku Mojo Kediri",
            'template_overdue' => "⚠️ *Klinik Dokterku – Pengingat Pengambilan Obat PRB*\n\nAssalamu'alaikum warahmatullahi wabarakatuh.\n\nYth. {nama},\n\nKami dari Klinik Dokterku Mojo Kediri ingin mengingatkan bahwa jadwal pengambilan obat Program Rujuk Balik (PRB) untuk terapi {diagnosa} yang dijadwalkan pada {hari_tanggal} *belum terkonfirmasi*.\n\nMohon segera menghubungi atau mengunjungi Klinik Dokterku Mojo Kediri untuk pengambilan obat {sapaan_formal}.\n\nTerima kasih atas perhatian dan kerja sama {sapaan_formal}.\n\nWassalamu'alaikum warahmatullahi wabarakatuh.\n\nHormat kami,\n\nApoteker Klinik Dokterku Mojo Kediri",
        ]);
    }

    public function formatTemplate(string $template, array $vars): string
    {
        foreach ($vars as $key => $val) {
            $template = str_replace('{' . $key . '}', $val, $template);
        }
        return $template;
    }
}
