<?php
namespace App\Services;

use App\Models\NotifikasiLog;
use App\Models\NotifikasiSetting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class NotifikasiService
{
    private NotifikasiSetting $cfg;

    // URL default lokal whatsapp-web.js service (override via wa_endpoint_url di settings)
    public const LOCAL_WA_URL = 'http://localhost:3001';

    private function localWaUrl(): string
    {
        return rtrim($this->cfg->wa_endpoint_url ?: self::LOCAL_WA_URL, '/');
    }

    public function __construct()
    {
        $this->cfg = NotifikasiSetting::getSetting();
    }

    public function kirimWa(
        string $nomor,
        string $pesan,
        ?int $pasienId = null,
        ?int $pengambilanId = null,
        string $tipe = 'HARIAN'
    ): array {
        $nomor = $this->normalizeNomor($nomor);
        $log = NotifikasiLog::create([
            'pasien_id'      => $pasienId,
            'pengambilan_id' => $pengambilanId,
            'channel'        => 'wa',
            'nomor_tujuan'   => $nomor,
            'pesan'          => $pesan,
            'status'         => 'pending',
            'tipe'           => $tipe,
        ]);

        if (!$this->cfg->is_aktif_wa) {
            $log->update(['status' => 'skipped', 'error_message' => 'WA gateway tidak aktif']);
            return ['ok' => false, 'msg' => 'WA gateway tidak aktif'];
        }

        $provider = $this->cfg->wa_provider ?? 'fonnte';

        try {
            if ($provider === 'local') {
                return $this->kirimWaLocal($nomor, $pesan, $log);
            }
            return $this->kirimWaFonnte($nomor, $pesan, $log);
        } catch (\Exception $e) {
            $log->update(['status' => 'failed', 'error_message' => $e->getMessage()]);
            Log::error('NotifikasiService WA: ' . $e->getMessage());
            return ['ok' => false, 'msg' => $e->getMessage()];
        }
    }

    private function kirimWaLocal(string $nomor, string $pesan, NotifikasiLog $log): array
    {
        $secret = config('notifikasi.wa_local_secret', 'prb-klinik-secret-2024');

        $response = Http::timeout(15)
            ->withHeaders(['x-api-key' => $secret])
            ->post($this->localWaUrl() . '/send', [
                'to'      => $nomor,
                'message' => $pesan,
            ]);

        $body = $response->json();

        if ($response->successful() && ($body['success'] ?? false)) {
            $log->update(['status' => 'sent', 'sent_at' => now()]);
            return ['ok' => true, 'msg' => 'Terkirim via WA lokal'];
        }

        $errMsg = $body['error'] ?? $response->body();
        $log->update(['status' => 'failed', 'error_message' => $errMsg]);
        return ['ok' => false, 'msg' => $errMsg];
    }

    private function kirimWaFonnte(string $nomor, string $pesan, NotifikasiLog $log): array
    {
        if (!$this->cfg->wa_api_key) {
            $log->update(['status' => 'skipped', 'error_message' => 'Fonnte API key kosong']);
            return ['ok' => false, 'msg' => 'API key kosong'];
        }

        $response = Http::timeout(15)
            ->withHeaders(['Authorization' => $this->cfg->wa_api_key])
            ->post('https://api.fonnte.com/send', [
                'target'      => $nomor,
                'message'     => $pesan,
                'countryCode' => '62',
                'delay'       => '3',
            ]);

        $body = $response->json();

        if ($response->successful() && ($body['status'] ?? false)) {
            $log->update(['status' => 'sent', 'sent_at' => now()]);
            return ['ok' => true, 'msg' => 'Terkirim via Fonnte'];
        }

        $errMsg = $body['reason'] ?? $response->body();
        $log->update(['status' => 'failed', 'error_message' => $errMsg]);
        return ['ok' => false, 'msg' => $errMsg];
    }

    public function kirimTelegram(
        string $pesan,
        ?int $pasienId = null,
        ?int $pengambilanId = null,
        string $tipe = 'HARIAN'
    ): array {
        $chatId = $this->cfg->telegram_chat_id_staff ?? '';
        $log = NotifikasiLog::create([
            'pasien_id'      => $pasienId,
            'pengambilan_id' => $pengambilanId,
            'channel'        => 'telegram',
            'nomor_tujuan'   => $chatId,
            'pesan'          => $pesan,
            'status'         => 'pending',
            'tipe'           => $tipe,
        ]);

        if (!$this->cfg->is_aktif_telegram || !$this->cfg->telegram_bot_token || !$chatId) {
            $log->update(['status' => 'skipped', 'error_message' => 'Telegram tidak aktif atau token/chat_id kosong']);
            return ['ok' => false, 'msg' => 'Telegram tidak aktif'];
        }

        try {
            $token = $this->cfg->telegram_bot_token;
            $response = Http::timeout(10)
                ->post("https://api.telegram.org/bot{$token}/sendMessage", [
                    'chat_id'    => $chatId,
                    'text'       => $pesan,
                    'parse_mode' => 'HTML',
                ]);

            $body = $response->json();
            if ($response->successful() && ($body['ok'] ?? false)) {
                $log->update(['status' => 'sent', 'sent_at' => now()]);
                return ['ok' => true, 'msg' => 'Terkirim ke Telegram'];
            }

            $errMsg = $body['description'] ?? $response->body();
            $log->update(['status' => 'failed', 'error_message' => $errMsg]);
            return ['ok' => false, 'msg' => $errMsg];
        } catch (\Exception $e) {
            $log->update(['status' => 'failed', 'error_message' => $e->getMessage()]);
            return ['ok' => false, 'msg' => $e->getMessage()];
        }
    }

    // Cek status koneksi WA lokal (gunakan wa_endpoint_url jika dikonfigurasi)
    public function statusWaLokal(): array
    {
        try {
            $response = Http::timeout(5)->get($this->localWaUrl() . '/status');
            if ($response->successful()) {
                return array_merge(['ok' => true], $response->json());
            }
            return ['ok' => false, 'ready' => false, 'msg' => 'Service tidak merespon'];
        } catch (\Exception $e) {
            return ['ok' => false, 'ready' => false, 'msg' => 'Service tidak berjalan: ' . $e->getMessage()];
        }
    }

    // Test kirim WA — support semua provider
    public function testKoneksiWa(string $apiKeyOrSecret, string $nomorTest): array
    {
        $provider = $this->cfg->wa_provider ?? 'fonnte';

        if ($provider === 'local') {
            try {
                $response = Http::timeout(10)
                    ->withHeaders(['x-api-key' => $apiKeyOrSecret])
                    ->post($this->localWaUrl() . '/send', [
                        'to'      => $this->normalizeNomor($nomorTest),
                        'message' => '✅ Test koneksi WA dari Klinik Dokterku — berhasil!',
                    ]);
                $body = $response->json();
                return [
                    'ok'  => $response->successful() && ($body['success'] ?? false),
                    'msg' => $body['error'] ?? ($response->successful() ? 'OK' : $response->body()),
                ];
            } catch (\Exception $e) {
                return ['ok' => false, 'msg' => $e->getMessage()];
            }
        }

        // Fonnte
        try {
            $response = Http::timeout(10)
                ->withHeaders(['Authorization' => $apiKeyOrSecret])
                ->post('https://api.fonnte.com/send', [
                    'target'      => $this->normalizeNomor($nomorTest),
                    'message'     => '✅ Test koneksi WA dari Klinik Dokterku — berhasil!',
                    'countryCode' => '62',
                ]);
            $body = $response->json();
            return [
                'ok'  => $response->successful() && ($body['status'] ?? false),
                'msg' => $body['reason'] ?? ($response->successful() ? 'OK' : $response->body()),
            ];
        } catch (\Exception $e) {
            return ['ok' => false, 'msg' => $e->getMessage()];
        }
    }

    public function testKoneksiTelegram(string $token, string $chatId): array
    {
        try {
            $response = Http::timeout(10)
                ->post("https://api.telegram.org/bot{$token}/sendMessage", [
                    'chat_id'    => $chatId,
                    'text'       => '✅ Test koneksi Telegram dari Klinik Dokterku — berhasil!',
                    'parse_mode' => 'HTML',
                ]);
            $body = $response->json();
            return [
                'ok'  => $response->successful() && ($body['ok'] ?? false),
                'msg' => $body['description'] ?? ($response->successful() ? 'OK' : $response->body()),
            ];
        } catch (\Exception $e) {
            return ['ok' => false, 'msg' => $e->getMessage()];
        }
    }

    // Cek apakah pengambilan ini sudah dapat WA hari ini (anti-spam)
    public function sudahKirimHariIni(int $pengambilanId): bool
    {
        return NotifikasiLog::where('pengambilan_id', $pengambilanId)
            ->where('channel', 'wa')
            ->where('status', 'sent')
            ->whereDate('created_at', today())
            ->exists();
    }

    public function normalizeNomor(string $nomor): string
    {
        $n = preg_replace('/[^0-9]/', '', $nomor);
        if (str_starts_with($n, '0'))  $n = '62' . substr($n, 1);
        if (!str_starts_with($n, '62')) $n = '62' . $n;
        return $n;
    }

    // Build pesan dengan variabel dinamis: hari Indonesia, sapaan, diagnosa
    public function buildPesanUntuk(string $template, \App\Models\Pasien $pasien, \Carbon\Carbon $jadwal): string
    {
        static $bulanId = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
                           'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
        static $hariId  = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];

        $sapaanFormal = ($pasien->jenis_kelamin === 'L') ? 'Bapak' : 'Ibu';
        $hariNama     = $hariId[(int) $jadwal->format('w')];
        $bulanNama    = $bulanId[(int) $jadwal->format('n')];
        $hariTanggal  = $hariNama . ' ' . $jadwal->format('j') . ' ' . $bulanNama . ' ' . $jadwal->format('Y');

        return $this->buildPesan($template, [
            'nama'          => $pasien->nama,
            'sapaan_formal' => $sapaanFormal,
            'diagnosa'      => $pasien->kategori_diagnosis ?? '-',
            'hari_tanggal'  => $hariTanggal,
            'tanggal'       => $hariTanggal,
            'hari'          => $hariNama,
        ]);
    }

    // Cek apakah pengambilan overdue sudah dapat notif >= maxHari (anti-spam 5 hari)
    public function sudahKirimOverdueMaxHari(int $pengambilanId, int $maxHari = 5): bool
    {
        return NotifikasiLog::where('pengambilan_id', $pengambilanId)
            ->where('tipe', 'OVERDUE')
            ->where('status', 'sent')
            ->count() >= $maxHari;
    }

    public function buildPesan(string $template, array $vars): string
    {
        foreach ($vars as $k => $v) {
            $template = str_replace('{' . $k . '}', (string) $v, $template);
        }
        return $template;
    }
}
