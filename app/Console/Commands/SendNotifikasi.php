<?php
namespace App\Console\Commands;

use App\Models\NotifikasiSetting;
use App\Models\PengambilanObat;
use App\Services\NotifikasiService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SendNotifikasi extends Command
{
    protected $signature = 'notifikasi:kirim {--dry-run : Jalankan simulasi tanpa kirim notifikasi}';
    protected $description = 'Kirim notifikasi WA/Telegram untuk jadwal ambil obat PRB (1x per pasien per hari)';

    public function handle(NotifikasiService $svc): int
    {
        $besok   = Carbon::tomorrow()->toDateString();
        $hariIni = Carbon::today()->toDateString();
        $dry     = (bool) $this->option('dry-run');
        $cfg     = NotifikasiSetting::getSetting();

        $this->info('--- Notifikasi PRB Klinik Dokterku ---');
        $dry && $this->warn('MODE DRY-RUN: tidak ada pesan yang dikirim');

        $kirim = 0;
        $skip  = 0;

        // H-1: besok jadwal
        $besokList = PengambilanObat::with('pasien')
            ->where('jadwal_berikutnya', $besok)
            ->whereNotIn('status', ['selesai', 'batal'])
            ->get();

        $this->info("[H-1] {$besokList->count()} pasien jadwal besok");
        foreach ($besokList as $p) {
            if (!$p->pasien?->telepon) continue;
            if (!$dry && $svc->sudahKirimHariIni($p->id)) {
                $this->line("  ⏭️  {$p->pasien->nama} — sudah terkirim hari ini, skip");
                $skip++;
                continue;
            }
            $jadwal = Carbon::parse($p->jadwal_berikutnya);
            $pesan  = $svc->buildPesanUntuk($cfg->template_h1 ?? '', $p->pasien, $jadwal);
            if (!$dry) {
                $result = $svc->kirimWa($p->pasien->telepon, $pesan, $p->pasien_id, $p->id, 'H1');
                $result['ok'] ? $kirim++ : $this->warn("  ❌ {$p->pasien->nama}: {$result['msg']}");
            }
            $this->line("  📱 {$p->pasien->nama} → {$p->pasien->telepon}");
        }

        // Hari ini: belum selesai
        $hariIniList = PengambilanObat::with('pasien')
            ->where('jadwal_berikutnya', $hariIni)
            ->whereNotIn('status', ['selesai', 'batal'])
            ->get();

        $this->info("[Hari Ini] {$hariIniList->count()} pasien belum ambil");
        foreach ($hariIniList as $p) {
            if (!$p->pasien?->telepon) continue;
            if (!$dry && $svc->sudahKirimHariIni($p->id)) {
                $this->line("  ⏭️  {$p->pasien->nama} — sudah terkirim hari ini, skip");
                $skip++;
                continue;
            }
            $jadwal = Carbon::parse($p->jadwal_berikutnya);
            $pesan  = $svc->buildPesanUntuk($cfg->template_harian ?? '', $p->pasien, $jadwal);
            if (!$dry) {
                $result = $svc->kirimWa($p->pasien->telepon, $pesan, $p->pasien_id, $p->id, 'HARIAN');
                $result['ok'] ? $kirim++ : $this->warn("  ❌ {$p->pasien->nama}: {$result['msg']}");
            }
            $this->line("  📱 {$p->pasien->nama}");
        }

        // Overdue: lewat jadwal, belum selesai — maks 5 hari pengingat
        $overdueList = PengambilanObat::with('pasien')
            ->where('jadwal_berikutnya', '<', $hariIni)
            ->whereNotIn('status', ['selesai', 'batal'])
            ->whereNotNull('jadwal_berikutnya')
            ->get();

        $this->info("[Overdue] {$overdueList->count()} pasien lewat jadwal");
        foreach ($overdueList as $p) {
            if (!$p->pasien?->telepon) continue;
            if (!$dry && $svc->sudahKirimHariIni($p->id)) {
                $this->line("  ⏭️  {$p->pasien->nama} — sudah terkirim hari ini, skip");
                $skip++;
                continue;
            }
            if (!$dry && $svc->sudahKirimOverdueMaxHari($p->id)) {
                $this->line("  🛑 {$p->pasien->nama} — batas 5 hari overdue tercapai, hentikan");
                $skip++;
                continue;
            }
            $jadwal = Carbon::parse($p->jadwal_berikutnya);
            $pesan  = $svc->buildPesanUntuk($cfg->template_overdue ?? '', $p->pasien, $jadwal);
            if (!$dry) {
                $result = $svc->kirimWa($p->pasien->telepon, $pesan, $p->pasien_id, $p->id, 'OVERDUE');
                $result['ok'] ? $kirim++ : $this->warn("  ❌ {$p->pasien->nama}: {$result['msg']}");
            }
            $this->line("  ⚠️  {$p->pasien->nama} (jadwal: {$p->jadwal_berikutnya})");
        }

        // Telegram summary ke staff
        $totalAktif = $hariIniList->count() + $overdueList->count();
        if ($cfg->is_aktif_telegram && ($totalAktif > 0 || $besokList->count() > 0)) {
            $tgl = now()->format('d/m/Y H:i');
            $summary = "📊 <b>Klinik Dokterku — Ringkasan Notifikasi PRB</b>\n<i>{$tgl}</i>\n\n" .
                "📅 Jadwal besok: <b>{$besokList->count()}</b> pasien\n" .
                "🕐 Hari ini belum ambil: <b>{$hariIniList->count()}</b>\n" .
                "⚠️ Overdue: <b>{$overdueList->count()}</b> pasien\n" .
                "✅ WA terkirim: <b>{$kirim}</b> | Diskip: <b>{$skip}</b>\n\n" .
                "<a href='http://localhost:8181/notifikasi'>Buka Dashboard Notifikasi</a>";
            if (!$dry) $svc->kirimTelegram($summary, null, null, 'HARIAN');
            $this->info("Telegram summary terkirim ke staff");
        }

        $this->info("✅ Selesai — Terkirim: {$kirim}, Skip (sudah kirim): {$skip}");
        return self::SUCCESS;
    }
}
