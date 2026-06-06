<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotifikasiSetting extends Model
{
    protected $table = 'notifikasi_settings';
    protected $fillable = [
        'wa_provider', 'wa_api_key', 'wa_sender_number',
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
            'template_h1'    => "🏥 *Klinik Dokterku - Pengingat Ambil Obat*\n\nYth. {nama},\n\nBesok ({tanggal}) adalah jadwal ambil obat PRB untuk *{diagnosa}*.\n\nMohon hadir tepat waktu.\n\n_Apoteker Klinik Dokterku_",
            'template_harian' => "🏥 *Klinik Dokterku*\n\nYth. {nama},\n\nHari ini jadwal ambil obat PRB ({diagnosa}). Obat sudah siap.\n\n_Apoteker Klinik Dokterku_",
            'template_overdue' => "⚠️ *Klinik Dokterku*\n\nYth. {nama},\n\nJadwal ambil obat ({diagnosa}) pada {tanggal} belum terkonfirmasi. Mohon segera datang.\n\n_Apoteker Klinik Dokterku_",
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
